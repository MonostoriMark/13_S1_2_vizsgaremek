#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

#define RST_PIN 9
#define SS_PIN 10
#define LED_OK 5
#define LED_DENY 4

MFRC522 rfid(SS_PIN, RST_PIN);

// Config
const char WIFI_SSID[] = "HotelFlow-wireless";
const char WIFI_PASS[] = "OptikArt2025";
const char MQTT_BROKER[] = "192.168.1.35";
const uint16_t MQTT_PORT = 1883;

// State machine
enum State { S_BOOT, S_WAIT_ESP, S_SEND_CONFIG, S_WAIT_READY, S_OPERATIONAL, S_ERROR };
State state = S_BOOT;

unsigned long nowMillis;
unsigned long stateTimer = 0;
int configStep = 0;
String lastCard = "";
unsigned long lastPublish = 0;
bool configSent = false;


// --- SERIAL BUFFERS ---
#define CMD_BUF_SZ 128
char cmdBuf[CMD_BUF_SZ];
int cmdIdx = 0;

#define MQTT_BUF_SZ 256
char mqttBuf[MQTT_BUF_SZ];
int mqttIdx = 0;

// --- SERIAL HELPERS ---
void sendRaw(const char *s) { Serial.print(s); Serial.print("|"); }
void sendKV(const char *k, const char *v) { Serial.print(k); Serial.print("="); Serial.print(v); Serial.print("|"); }
void sendNumberKV(const char *k, unsigned long n) { Serial.print(k); Serial.print("="); Serial.print(n); Serial.print("|"); }

void sendConfigStep(int step) {
  switch(step) {
    case 0: sendKV("SSID", WIFI_SSID); break;
    case 1: sendKV("PASS", WIFI_PASS); break;
    case 2: sendKV("BROKER", MQTT_BROKER); break;
    case 3: { char portStr[8]; sprintf(portStr, "%u", (unsigned)MQTT_PORT); sendKV("PORT", portStr); } break;
    case 4: sendRaw("CONNECT"); break;
  }
}

// --- HANDLE MQTT ---
void handleMQTTMessage(const char* msg) {
    const char* payload = msg + 8; // "MQTT_RX|" átugrás
    Serial.print("MQTT payload: "); Serial.println(payload);

    // csak JSON payload feldolgozása
    if(payload[0] != '{') return;

    StaticJsonDocument<128> doc;
    DeserializationError err = deserializeJson(doc, payload);
    if(err){
        Serial.print("JSON parse error: "); Serial.println(err.c_str());
        return;
    }

    const char* access = doc["accessResult"];
    if(!access) return;

    Serial.print("accessResult: "); Serial.println(access);

    if(strcmp(access,"OK") == 0){
        digitalWrite(LED_OK,HIGH);
        digitalWrite(LED_DENY,LOW);
    }
    else if(strcmp(access,"DENY") == 0){
        digitalWrite(LED_OK,LOW);
        digitalWrite(LED_DENY,HIGH);
    }
}

// --- HANDLE COMMAND ---
void handleCommandMessage(const char* msg) {
  if (strcmp(msg,"REQ_CONFIG")==0) { state=S_SEND_CONFIG; configStep=0; stateTimer=nowMillis; return; }
  if (strcmp(msg,"WIFI_OK")==0) { sendRaw("ARD_WIFI_SEEN"); return; }
  if (strcmp(msg,"WIFI_FAIL")==0) { state=S_ERROR; return; }
  if (strcmp(msg,"MQTT_OK")==0) return;
  if (strcmp(msg,"SYS_READY")==0) { state=S_OPERATIONAL; return; }
  if (strcmp(msg,"PUB_OK")==0) { digitalWrite(LED_OK,HIGH); delay(80); digitalWrite(LED_OK,LOW); return; }
  if (strcmp(msg,"")==0) { digitalWrite(LED_DENY,HIGH); delay(300); digitalWrite(LED_DENY,LOW); return; }
}

// --- HANDLE SERIAL ---
void pollSerial() {
  while (Serial.available()) {
    char c = Serial.read();

    // --- MQTT ÜZENETEK ---
    // Ha M az első karakter → MQTT mód
    if (mqttIdx == 0 && c == 'M') {
      mqttBuf[mqttIdx++] = c;
      continue;
    }

    // Ha MQTT mód aktív: addig olvas, amíg \n-t nem kap
    if (mqttIdx > 0) {
      mqttBuf[mqttIdx++] = c;

      if (c == '\n') {
        mqttBuf[mqttIdx - 1] = '\0';   // a \n eltávolítása

        handleMQTTMessage(mqttBuf);    // MQTT feldolgozása

        mqttIdx = 0;                   // visszaáll normál módba
      }
      continue;
    }

    // --- PARANCSOK ---
    if (cmdIdx < CMD_BUF_SZ - 1) {
      if (c == '\n') {
        mqttBuf[mqttIdx - 1] = '\0';  // \n eltávolítása
        // extra whitespace vagy CR karakterek levágása
          while(mqttBuf[mqttIdx-2]=='\r' || mqttBuf[mqttIdx-2]==' '){
              mqttBuf[--mqttIdx-1] = '\0';
          }
        handleMQTTMessage(mqttBuf);
        mqttIdx = 0;
      }
    }

  } // while
}





// --- SETUP ---
void setup() {
  pinMode(LED_OK, OUTPUT); pinMode(LED_DENY, OUTPUT);
  digitalWrite(LED_OK, LOW); digitalWrite(LED_DENY, LOW);

  Serial.begin(9600);
  SPI.begin();
  rfid.PCD_Init();

  state=S_WAIT_ESP; stateTimer=millis();
  sendRaw("ARDUINO_BOOT");
  delay(2000);
}

// --- LOOP ---
void loop() {
  nowMillis = millis();

  pollSerial(); // ESP üzenetek olvasása és feldolgozása

  // --- STATE MACHINE ---
  switch (state) {
    case S_WAIT_ESP:
      if (nowMillis - stateTimer > 800) { state=S_SEND_CONFIG; configStep=0; stateTimer=nowMillis; }
      break;

    case S_SEND_CONFIG:
    if (!configSent && nowMillis - stateTimer > 40) {
        sendConfigStep(configStep);
        configStep++;
        stateTimer = nowMillis;

        if (configStep > 4) { 
            state = S_WAIT_READY; 
            stateTimer = nowMillis;
            configSent = true;  // már nem küldjük újra
        }
    }
    break;

    case S_WAIT_READY:
      if (nowMillis - stateTimer > 30000) { state=S_ERROR; digitalWrite(LED_DENY,HIGH); }
      break;

    case S_OPERATIONAL:
      static unsigned long lastRFID=0;
      if (nowMillis - lastRFID > 300) {
        lastRFID = nowMillis;
        if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
          char cardID[32]; cardID[0]=0;
          for (byte i=0;i<rfid.uid.size;i++) { char b[3]; sprintf(b,"%02X",rfid.uid.uidByte[i]); strcat(cardID,b); }
          String sCard = String(cardID); sCard.toUpperCase();
          if (sCard != lastCard && nowMillis - lastPublish > 2000) {
            lastCard = sCard; lastPublish=nowMillis;
            char json[128];
            snprintf(json,sizeof(json),"{\"cardID\":\"%s\",\"doorID\":\"room1\",\"token\":\"ABC123DEF456\"}",cardID);
            Serial.print("MQTT_TX|"); Serial.print(json); Serial.print("|");
            digitalWrite(LED_OK,HIGH); delay(60); digitalWrite(LED_OK,LOW);
          }
          rfid.PICC_HaltA(); rfid.PCD_StopCrypto1();
        }
      }
      break;

    case S_ERROR:
    if (nowMillis - stateTimer > 10000) {
        digitalWrite(LED_DENY, LOW);
        state = S_SEND_CONFIG;
        configStep = 0;
        stateTimer = nowMillis;
        configSent = false; // lehetővé tesszük a konfiguráció újraküldését
    }
    break;

    default: break;
  }
}
