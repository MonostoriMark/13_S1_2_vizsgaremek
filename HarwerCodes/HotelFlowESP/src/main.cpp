#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// --- ADATPUFFEREK ---
#define RXBUF 256
char buf[RXBUF];
uint16_t bi = 0;

// --- KONFIGURÁCIÓ (Arduinótól jön) ---
char ssid[64] = "";
char pass[64] = "";
char broker[64] = "";
uint16_t port = 1883;

// --- MQTT BEÁLLÍTÁSOK ---
const char* CLIENT_ID = "ESP01-Gateway"; 
const char* PUBLISH_TOPIC = "hotel/room1/auth";
const char* SUBSCRIBE_TOPIC = "hotel/room1/result";
const char* LWT_TOPIC = "hotel/status/ESP01";

bool gotSSID = false, gotPASS = false, gotBRK = false, gotPORT = false;
bool sysReady = false;

WiFiClient wc;
PubSubClient mqtt(wc);

// --- PUBLIKÁLÁSI SOR ---
#define PUB_QUEUE_SZ 3
String pubQueue[PUB_QUEUE_SZ];
uint8_t pubHead = 0, pubTail = 0;

// --- SOROS KÜLDÉS ---
void send(const char* s){
  Serial.print(s); 
  Serial.print("|"); 
  Serial.flush();    
}

// --- QUEUE KEZELÉS ---
bool pubQueueEmpty(){ return pubHead == pubTail; }
bool pubQueueFull(){ return ((pubTail + 1) % PUB_QUEUE_SZ) == pubHead; }

void enqueuePub(const char* msg){
  pubQueue[pubTail] = String(msg);
  pubTail = (pubTail + 1) % PUB_QUEUE_SZ;
  send("PUB_QUEUED");
}

// --- MQTT CALLBACK ---
void mqttCB(char* topic, byte* payload, unsigned int len){
  Serial.print("MQTT_RX|"); 
  for (unsigned int i = 0; i < len; i++) Serial.print((char)payload[i]);
  Serial.print("\n"); 
}

// --- QUEUE KÜLDÉS MQTT-re ---
void processPubQueue(){
  if(pubQueueEmpty() || !mqtt.connected()) return;
  
  String msg = pubQueue[pubHead];
  if(mqtt.publish(PUBLISH_TOPIC, msg.c_str())){
    send("PUB_OK");
    pubHead = (pubHead + 1) % PUB_QUEUE_SZ;
  }
}

// --- FŐ FELDOLGOZÓ FÜGGVÉNY (SZEMÉTTŰRŐ VERZIÓ) ---
void processMsg(char* m){
  // 1. ELŐFELDOLGOZÁS: Szóközök és soremelések átugrása az elején
  int start = 0;
  while(m[start] == ' ' || m[start] == '\r' || m[start] == '\n') {
    start++;
  }
  char* cmd = m + start; // Ez a megtisztított parancs

  // Ha üres a parancs, kilépünk
  if(strlen(cmd) == 0) return;

  // 2. Konfiguráció
  if(strncmp(cmd,"SSID=",5)==0){ strncpy(ssid,cmd+5,63); gotSSID=true; send("SSID_OK"); return; }
  if(strncmp(cmd,"PASS=",5)==0){ strncpy(pass,cmd+5,63); gotPASS=true; send("PASS_OK"); return; }
  if(strncmp(cmd,"BROKER=",7)==0){ strncpy(broker,cmd+7,63); gotBRK=true; send("BROKER_OK"); return; }
  if(strncmp(cmd,"PORT=",5)==0){ port = atoi(cmd+5); gotPORT=true; send("PORT_OK"); return; }

  // 3. Csatlakozás (CONNECT)
  if(strcmp(cmd,"CONNECT")==0){
    if(!gotSSID || !gotPASS || !gotBRK || !gotPORT){ send("CONFIG_INCOMPLETE"); return; }

    while(Serial.available()) Serial.read(); // Puffer ürítés
    
    send("STEP_1_RESET");
    WiFi.disconnect(); WiFi.mode(WIFI_OFF); delay(1000); 

    WiFi.mode(WIFI_STA); WiFi.setPhyMode(WIFI_PHY_MODE_11B); WiFi.setOutputPower(0); WiFi.setAutoConnect(false);
    delay(500);

    send("STEP_2_WIFI");
    WiFi.begin(ssid, pass);

    unsigned long t = millis();
    bool connected = false;
    while(millis() - t < 20000){
        if(WiFi.status() == WL_CONNECTED){ connected = true; break; }
        delay(500); yield();
    }

    if(!connected){ WiFi.mode(WIFI_OFF); send("WIFI_FAIL"); return; }
    send("WIFI_OK"); delay(1000); 

    send("STEP_3_BRK");
    mqtt.setServer(broker, port); mqtt.setCallback(mqttCB); mqtt.setSocketTimeout(10); mqtt.setKeepAlive(60); 

    if(mqtt.connect(CLIENT_ID, LWT_TOPIC, 1, true, "OFFLINE")) {
      delay(200); mqtt.subscribe(SUBSCRIBE_TOPIC);
      send("BRK_OK"); delay(500);
      sysReady=true; send("SYS_READY");
    } else {
      Serial.print("BRK_FAIL_RC="); Serial.print(mqtt.state()); Serial.print("|");
    }
    return;
  }

  // 4. JSON Publikálás (Kártyaadat)
  if(cmd[0] == '{' && sysReady){
    if(pubQueueFull()) send("PUB_FAIL");
    else enqueuePub(cmd);
    return;
  }

  // 5. HIBAKERESÉS: Visszaküldjük a nem értett parancsot
  Serial.print("UNKNOWN_CMD=");
  Serial.print(cmd);
  Serial.print("|");
}

// --- KAPCSOLAT FENNTARTÁSA ---
void ensureConnection(){
  if(!gotSSID) return; 

  static unsigned long lastTry=0;
  if(!mqtt.connected() && millis()-lastTry > 10000){
      lastTry = millis();
      if(WiFi.status() != WL_CONNECTED){
         WiFi.disconnect(); WiFi.begin(ssid, pass);
      } else {
         if(mqtt.connect(CLIENT_ID, LWT_TOPIC, 1, true, "OFFLINE")){
            mqtt.subscribe(SUBSCRIBE_TOPIC);
            send("BRK_RECONN");
         }
      }
  }
}

// --- SETUP ---
void setup(){
  Serial.begin(9600); 
  delay(500);
  WiFi.mode(WIFI_OFF);
  
  Serial.println();
  send("BOOT_DONE");
  send("REQ_CONFIG");
}

// --- LOOP ---
void loop(){
  if(sysReady) {
      ensureConnection();
      mqtt.loop();
      processPubQueue();
  }

  while(Serial.available()){
    char c = Serial.read();
    if(c=='|'){ 
      buf[bi]=0; 
      processMsg(buf); 
      bi=0; 
    } else { 
      if(bi < RXBUF-1) buf[bi++]=c; 
      else bi=0; 
    }
  }
}