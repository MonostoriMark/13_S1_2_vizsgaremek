// ESP.ino (for ESP8266 / ESP-01)
#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

#define RXBUF_SZ 256
#define MSG_MAX 4
#define MSG_SZ 256

// Config containers
char wifi_ssid[64] = {0};
char wifi_pass[64] = {0};
char mqtt_server[64] = {0};
uint16_t mqtt_port = 1883;
bool haveSSID=false, havePass=false, haveBroker=false, havePort=false;

// Serial RX
char rxbuf[RXBUF_SZ];
size_t rxidx = 0;

// MQTT client
WiFiClient netClient;
PubSubClient client(netClient);

// Publish queue (ring)
char msgQueue[MSG_MAX][MSG_SZ];
uint8_t qHead=0, qTail=0, qCount=0;

// state
enum State { ST_BOOT, ST_WAIT_CONFIG, ST_CONNECT_WIFI, ST_CONNECT_MQTT, ST_READY, ST_ERROR };
State st = ST_BOOT;
unsigned long stTimer = 0;
unsigned long nowMs = 0;

// Helpers
void sendResp(const char* s) {
  Serial.print(s);
  Serial.print("|");
}

bool queuePush(const char* s) {
  if (qCount >= MSG_MAX) return false;
  strncpy(msgQueue[qTail], s, MSG_SZ-1);
  msgQueue[qTail][MSG_SZ-1] = '\0';
  qTail = (qTail + 1) % MSG_MAX;
  qCount++;
  return true;
}
bool queuePop(char* outBuf, size_t outSz) {
  if (qCount == 0) return false;
  strncpy(outBuf, msgQueue[qHead], outSz-1);
  outBuf[outSz-1] = '\0';
  qHead = (qHead + 1) % MSG_MAX;
  qCount--;
  return true;
}

void mqttCallback(char* topic, byte* payload, unsigned int length) {
  // forward to Arduino as MQTT_RX|<json>|
  Serial.print("MQTT_RX|");
  for (unsigned int i=0;i<length;i++) Serial.write(payload[i]);
  Serial.print("|");
}

bool mqttEnsureConnected() {
  if (client.connected()) return true;
  String cid = "ESP01-";
  cid += String(ESP.getChipId(), HEX);
  if (client.connect(cid.c_str())) {
    client.subscribe("hotel/room1/result");
    sendResp("MQTT_OK");
    return true;
  } else {
    // send state for debug
    int stCode = client.state();
    Serial.print("MQTT_STATE|");
    Serial.print(stCode);
    Serial.print("|");
    return false;
  }
}

void processRxBuffer() {
  if (rxidx==0) return;
  rxbuf[rxidx] = '\0';
  // trim
  char *s = rxbuf;
  while (*s && (*s==' '||*s=='\r'||*s=='\n')) s++;
  // handle commands
  if (strncmp(s, "SSID=",5)==0) {
    strncpy(wifi_ssid, s+5, sizeof(wifi_ssid)-1);
    haveSSID = true;
    sendResp("SSID_OK");
  } else if (strncmp(s, "PASS=",5)==0) {
    strncpy(wifi_pass, s+5, sizeof(wifi_pass)-1);
    havePass = true;
    sendResp("PASS_OK");
  } else if (strncmp(s, "BROKER=",7)==0) {
    strncpy(mqtt_server, s+7, sizeof(mqtt_server)-1);
    haveBroker = true;
    sendResp("BROKER_OK");
  } else if (strncmp(s, "PORT=",5)==0) {
    mqtt_port = atoi(s+5);
    havePort = true;
    sendResp("PORT_OK");
  } else if (strcmp(s, "CONNECT")==0) {
    // start connect sequence
    st = ST_CONNECT_WIFI;
    stTimer = nowMs;
    sendResp("CONNECTING");
  } else {
    // if ready and message starts with { treat as JSON to publish
    if (st == ST_READY && s[0]=='{') {
      // enqueue
      if (strlen(s) < MSG_SZ && queuePush(s)) {
        sendResp("ENQUEUED");
      } else {
        // queue full, notify Arduino
        sendResp("QUEUE_FULL");
      }
    } else if (strcmp(s, "REQ_CONFIG")==0) {
      // optional: Arduino may have asked
      sendResp("REQ_ACK");
    } else {
      // debug
      Serial.print("UNKNOWN|");
      Serial.print(s);
      Serial.print("|");
    }
  }
  rxidx = 0; // reset
}

// Non-blocking serial read: collect until '|' then process
void pollSerial() {
  while (Serial.available()) {
    char c = Serial.read();
    if (c == '|') {
      processRxBuffer();
    } else {
      if (rxidx < RXBUF_SZ-1) rxbuf[rxidx++] = c;
    }
  }
}

void setup() {
  Serial.begin(9600);
  delay(200);
  sendResp("BOOT_DONE");
  delay(50);
  sendResp("REQ_CONFIG");
  st = ST_WAIT_CONFIG;
  stTimer = millis();
}

void loop() {
  nowMs = millis();
  pollSerial();

  switch (st) {
    case ST_WAIT_CONFIG:
      // wait until we have essential config
      if (haveSSID && havePass && haveBroker) {
        st = ST_CONNECT_WIFI;
        stTimer = nowMs;
      }
      break;

    case ST_CONNECT_WIFI:
      // connect non-blocking style: attempt then check
      if (WiFi.status() != WL_CONNECTED) {
        WiFi.mode(WIFI_STA);
        WiFi.begin(wifi_ssid, wifi_pass);
        unsigned long start = nowMs;
        while (millis() - start < 15000) { // block short while waiting for connection attempts
          pollSerial(); // keep processing serial while waiting
          if (WiFi.status() == WL_CONNECTED) break;
          delay(50);
        }
      }
      if (WiFi.status() == WL_CONNECTED) {
        sendResp("WIFI_OK");
        st = ST_CONNECT_MQTT;
        stTimer = nowMs;
      } else {
        sendResp("WIFI_FAIL");
        st = ST_ERROR;
      }
      break;

    case ST_CONNECT_MQTT:
      client.setServer(mqtt_server, mqtt_port);
      client.setCallback(mqttCallback);
      if (mqttEnsureConnected()) {
        st = ST_READY;
        sendResp("SYS_READY");
      } else {
        sendResp("MQTT_FAIL");
        st = ST_ERROR;
      }
      break;

    case ST_READY:
      // maintain mqtt loop
      client.loop();
      // if disconnected try reconnect periodic
      if (!client.connected()) {
        unsigned long t = millis();
        if (t - stTimer > 2000) {
          stTimer = t;
          mqttEnsureConnected();
        }
      }
      // if messages queued -> publish (one per loop)
      if (qCount > 0 && client.connected()) {
        char outgoing[MSG_SZ];
        if (queuePop(outgoing, sizeof(outgoing))) {
          bool ok = client.publish("hotel/room1/auth", outgoing);
          if (ok) sendResp("PUB_OK");
          else {
            sendResp("PUB_FAIL");
            // push back? attempt retry
            queuePush(outgoing);
          }
        }
      }
      break;

    case ST_ERROR:
      // send error and offer retry
      sendResp("STATE_ERROR");
      // try to recover after a pause
      if (nowMs - stTimer > 5000) {
        // reset flags to allow config again
        haveSSID = havePass = haveBroker = havePort = false;
        qHead = qTail = qCount = 0;
        st = ST_WAIT_CONFIG;
        sendResp("RETRY_CONFIG");
      }
      break;

    default:
      break;
  }
}
