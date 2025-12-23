#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <SoftwareSerial.h> 
// #include <ArduinoJson.h> <-- EZT KIKAPCSOLTUK, NEM KELL!

SoftwareSerial espSerial(2, 3); 

#define RST_PIN 9
#define SS_PIN 10
#define LED_OK 5
#define LED_DENY 4
#define LED_PROCESS 6

MFRC522 rfid(SS_PIN, RST_PIN);

// --- KONFIGURÁCIÓ ---
const char WIFI_SSID[] = "Xiaomi_FCD0";
const char WIFI_PASS[] = "Xiaomirouter3000";
const char MQTT_BROKER[] = "192.168.1.35";
const uint16_t MQTT_PORT = 1883;

enum State { S_BOOT, S_WAIT_ESP, S_SEND_CONFIG, S_WAIT_READY, S_OPERATIONAL, S_ERROR };
State state = S_BOOT;

unsigned long nowMillis;
unsigned long stateTimer = 0;
int configStep = 0;
bool configSent = false;
String lastCard = "";
unsigned long lastPublish = 0;

char room[] = "Room 868";

// --- ADATKÜLDÉS VEZÉRLÉS ---
bool waitingForPub = false;       
unsigned long pubRetryTimer = 0;  

#define CMD_BUF_SZ 256
char cmdBuf[CMD_BUF_SZ];
int cmdIdx = 0;

// FIX MEMÓRIA SOR
#define PUB_QUEUE_SZ 3        
#define MSG_LEN 100           
char pubQueue[PUB_QUEUE_SZ][MSG_LEN]; 
uint8_t pubHead=0, pubTail=0;

// --- SEGÉDEK ---
void sendToEsp(const char *s) { 
  if(strlen(s) == 0) return;
  espSerial.print(s); espSerial.print("|"); 
  Serial.print("TX >> "); Serial.println(s); 
}
void sendKV(const char *k, const char *v) { 
  espSerial.print(k); espSerial.print("="); espSerial.print(v); espSerial.print("|"); 
  Serial.print("TX >> CONFIG: "); Serial.println(k); 
}

void sendConfigStep(int step) {
  switch(step) {
    case 0: sendKV("SSID", WIFI_SSID); break;
    case 1: sendKV("PASS", WIFI_PASS); break;
    case 2: sendKV("BROKER", MQTT_BROKER); break;
    case 3: { char portStr[8]; sprintf(portStr, "%u", (unsigned)MQTT_PORT); sendKV("PORT", portStr); } break;
    case 4: sendToEsp("CONNECT"); break;
  }
}

// --- QUEUE ---
bool pubQueueEmpty(){ return pubHead==pubTail; }
bool pubQueueFull(){ return ((pubTail+1)%PUB_QUEUE_SZ)==pubHead; }
void enqueuePub(const char* msg){ 
    strncpy(pubQueue[pubTail], msg, MSG_LEN);
    pubQueue[pubTail][MSG_LEN-1] = 0; 
    pubTail=(pubTail+1)%PUB_QUEUE_SZ; 
    Serial.println("DEBUG: Sorba rakva.");
}
char* peekPub(){ return pubQueue[pubHead]; }
void dequeuePub(){ pubHead=(pubHead+1)%PUB_QUEUE_SZ; }

// --- KÖNNYŰSÚLYÚ FELDOLGOZÓ (No JSON Lib) ---
void handleMQTTMessage(char* msg){
  Serial.print("RX MQTT << "); Serial.println(msg);

  char* resultPtr = strstr(msg, "\"accessResult\"");
  if(!resultPtr){ Serial.println("HIBA: Nem találtam 'accessResult'-ot."); return; }

  // --- új mezők ellenőrzése ---
  char* tsPtr = strstr(msg,"\"ts\":");
  char* sigPtr = strstr(msg,"\"sig\":");
  unsigned long tsVal = tsPtr ? atol(tsPtr+5) : 0;
  uint16_t sigVal = sigPtr ? atoi(sigPtr+6) : 0;
  uint16_t expSig = simpleSig(lastCard.c_str(),room,tsVal);

  bool sigOk = (sigVal == expSig);

  if(strstr(resultPtr,"OK") != NULL && sigOk){
      Serial.println(">>> DÖNTÉS: NYITÁS (OK)");
      digitalWrite(LED_OK,HIGH); 
      digitalWrite(LED_DENY,LOW);
      stateTimer = nowMillis; 
  }
  else{
      Serial.println(">>> DÖNTÉS: ELUTASÍTVA (DENY)");
      digitalWrite(LED_OK,LOW); 
      digitalWrite(LED_DENY,HIGH);
      stateTimer = nowMillis; 
  }
}

// --- PARANCSOK KEZELÉSE ---
void handleCommandMessage(const char* msg){
  if(strncmp(msg, "BRK_FAIL", 8) != 0 && strncmp(msg, "PUB_", 4) != 0) { 
      Serial.print("RX CMD << "); Serial.println(msg); 
  }

  if(strncmp(msg, "REQ_CONFIG", 10) == 0 || strncmp(msg, "WIFI_FAIL", 9) == 0){ 
      state=S_SEND_CONFIG; configStep=0; stateTimer=nowMillis; configSent=false; 
      Serial.println("--- ÚJRAKONFIGURÁLÁS ---");
      return; 
  }
  if(strncmp(msg, "BRK_FAIL", 8) == 0){ 
      state=S_ERROR; Serial.println("!!! MQTT HIBA !!!"); return; 
  }
  if(strncmp(msg, "SYS_READY", 9) == 0){ 
     state=S_OPERATIONAL; waitingForPub = false; pubRetryTimer = 0;
     Serial.println("\n*** RENDSZER KÉSZ! ***\n");
     digitalWrite(LED_PROCESS, HIGH); delay(100); digitalWrite(LED_PROCESS, LOW);
     return; 
  }
  if(strncmp(msg, "PUB_OK", 6) == 0){ 
     if(!pubQueueEmpty()) dequeuePub(); 
     waitingForPub = false; 
     Serial.println("--- KÜLDÉS OK ---");
     return; 
  }
  if(strncmp(msg, "PUB_FAIL", 8) == 0){ 
      waitingForPub = false; Serial.println("ESP Queue Tele!");
  }
}

// --- INTELLIGENS PARSER ---
void pollSerial(){
  static char buf[256];
  static int idx = 0;
  
  while(espSerial.available()){
    char c = espSerial.read();
    Serial.write(c); 

    if(idx < 255) buf[idx++] = c;

    if(c == '\n') {
        buf[idx-1] = 0; 
        if(strncmp(buf, "MQTT_RX|", 8) == 0) handleMQTTMessage(buf);
        idx = 0; 
    } 
    else if (c == '|') {
        bool isMqttHeader = (idx >= 8 && strncmp(buf, "MQTT_RX|", 8) == 0);
        if(!isMqttHeader) {
            buf[idx-1] = 0; 
            handleCommandMessage(buf);
            idx = 0; 
        }
    } 
  }
}

// --- EGYSZERŰ HMAC / SIG KÉSZÍTÉS ---
uint16_t simpleSig(const char* cardID, const char* doorID, unsigned long ts){
  uint16_t s=0xBEEF;
  for(int i=0; cardID[i]; i++){ s ^= cardID[i]; s = (s<<5)|(s>>11); }
  for(int i=0; doorID[i]; i++){ s ^= doorID[i]; s = (s<<5)|(s>>11); }
  for(int i=0;i<4;i++){ s ^= (ts>>(i*8))&0xFF; s = (s<<3)|(s>>13); }
  return s;
}

void setup(){
  delay(1000);

  pinMode(LED_OK,OUTPUT); pinMode(LED_DENY,OUTPUT); pinMode(LED_PROCESS, OUTPUT);
  digitalWrite(LED_OK,LOW); digitalWrite(LED_DENY,LOW); digitalWrite(LED_PROCESS,LOW);

  Serial.begin(9600); 
  espSerial.begin(9600); 

  Serial.println("--- ARDUINO INDUL (LIGHTWEIGHT) ---");
  SPI.begin();
  rfid.PCD_Init();
  rfid.PCD_SetAntennaGain(rfid.RxGain_max);

  Serial.print("RFID Verzió: "); rfid.PCD_DumpVersionToSerial();
  state=S_WAIT_ESP; stateTimer=millis();
}

void loop(){
  nowMillis=millis();
  pollSerial();

  if(state!=S_OPERATIONAL || (digitalRead(LED_OK) || digitalRead(LED_DENY))){
    if(nowMillis - stateTimer > 2000){ digitalWrite(LED_OK,LOW); digitalWrite(LED_DENY,LOW); }
  }

  switch(state){
    case S_WAIT_ESP:
      if(nowMillis-stateTimer>3000){ state=S_SEND_CONFIG; configStep=0; stateTimer=nowMillis; }
      break;
    case S_SEND_CONFIG:
      if(!configSent && nowMillis - stateTimer > 200){ 
          sendConfigStep(configStep); 
          configStep++; 
          stateTimer = nowMillis; // frissítjük a timert
          if(configStep > 4){ 
              state = S_WAIT_READY; 
              stateTimer = nowMillis; 
              configSent = true; 
          }
      }
      break;
    case S_WAIT_READY:
      if(nowMillis-stateTimer>60000){ state=S_ERROR; digitalWrite(LED_DENY,HIGH); }
      break;

    case S_OPERATIONAL:
      if(rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()){
          char cardID[32]; cardID[0]=0;
          for(byte i=0;i<rfid.uid.size;i++){ char b[3]; sprintf(b,"%02X",rfid.uid.uidByte[i]); strcat(cardID,b); }
          String sCard=String(cardID); sCard.toUpperCase();
          
          if(sCard!=lastCard || nowMillis-lastPublish>2000){
            lastCard=sCard; lastPublish=nowMillis;
            Serial.print("!!! KÁRTYA !!! ID: "); Serial.println(sCard);
            
            // --- új biztonsági adatok ---
            unsigned long ts = millis();              // timestamp
            uint16_t sig = simpleSig(cardID,room,ts);

            char json[150];
            snprintf(json,sizeof(json),
              "{\"cardID\":\"%s\",\"doorID\":\"%s\",\"token\":\"ABC123DEF456\",\"ts\":%lu,\"sig\":%u}",
                cardID, room, ts, sig);

            if(!pubQueueFull()){
                enqueuePub(json); 
                digitalWrite(LED_PROCESS,HIGH); delay(100); digitalWrite(LED_PROCESS,LOW); 
            }
          }
          rfid.PICC_HaltA(); 
          rfid.PCD_StopCrypto1();
      }
      break;
      
    case S_ERROR:
      if(nowMillis-stateTimer>10000){
         digitalWrite(LED_DENY,LOW); state=S_SEND_CONFIG; configStep=0; stateTimer=nowMillis; configSent=false;
      }
      break;
  }

  if(!pubQueueEmpty()){ 
    if(!waitingForPub || millis() - pubRetryTimer > 3000){
        char* msg = peekPub();
        if(strlen(msg) > 0) {
           Serial.println("KÜLDÉS...");
           sendToEsp(msg);
           waitingForPub = true;     
           pubRetryTimer = millis(); 
        } else { dequeuePub(); }
    }
  }
}