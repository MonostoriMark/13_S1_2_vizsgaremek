#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// WiFi adatok
const char* ssid = "HotelFlow-wireless";
const char* password = "OptikArt2025";

// MQTT adatok
const char* mqtt_server = "192.168.1.35";
const int mqtt_port = 1883;

WiFiClient espClient;
PubSubClient client(espClient);

// Üzenet buffer
#define BUF_SIZE 128
char buffer[BUF_SIZE];
int indexPos = 0;

// MQTT callback
void callback(char* topic, byte* payload, unsigned int length) {
  String msg = "";
  for (unsigned int i = 0; i < length; i++) {
    msg += (char)payload[i];
  }
  msg.trim();

  Serial.println(msg);   // visszaküldjük az Arduinonak
}

// MQTT reconnect
void reconnect() {
  while (!client.connected()) {
    if (client.connect("ESP01Client")) {
      client.subscribe("hotel/room1/result");
    } else {
      delay(2000);
    }
  }
}

void setup() {
  Serial.begin(9600);  // UART Arduino felé

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) delay(500);

  client.setServer(mqtt_server, mqtt_port);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) reconnect();
  client.loop();

  // --- Arduino -> ESP üzenet ---
  while (Serial.available()) {
    char c = Serial.read();

    if (c == '|') {
      buffer[indexPos] = '\0';

      if (indexPos > 0) {
        client.publish("hotel/room1/card", buffer);
      }

      indexPos = 0;
    } 
    else {
      if (indexPos < BUF_SIZE - 1) {
        buffer[indexPos++] = c;
      }
    }
  }
}
