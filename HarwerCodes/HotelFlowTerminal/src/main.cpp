#include <Arduino.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>

#define RST_PIN 9
#define SS_PIN 10
#define LED_OK 5
#define LED_DENY 4

MFRC522 rfid(SS_PIN, RST_PIN);

String lastCard = "";
#define BUF_SIZE 256
char msgBuffer[BUF_SIZE];
int bufIndex = 0;

// --- Küldés csak új kártyára ---
void sendJsonToESP(const String &cardID) {
    String json = "{";
    json += "\"cardID\":\"" + cardID + "\",";
    json += "\"timestamp\":" + String(millis()) + ",";
    json += "\"doorID\":\"room1\",";
    json += "\"authToken\":\"ABC123XYZ\"";
    json += "}";

    Serial.print(json);
    Serial.print("|"); // üzenet vége
    //Serial.println();   // debug
   //Serial.println("JSON elküldve ESP-nek");
}

// --- Backend JSON feldolgozás (LED vezérlés) ---
void processBackendJSON(char* jsonStr) {
    // Debug
    //Serial.print("Processing backend JSON: ");
    //Serial.println(jsonStr);

    StaticJsonDocument<256> doc;
    DeserializationError err = deserializeJson(doc, jsonStr);
    if (err) {
        Serial.print("JSON parse hiba: ");
        Serial.println(err.c_str());
        return;
    }

    const char* result = doc["accessResult"];
    if (!result) return;

    if (strcmp(result, "OK") == 0) {
        digitalWrite(LED_OK, HIGH);
        digitalWrite(LED_DENY, LOW);
    } else if (strcmp(result, "DENY") == 0) {
        digitalWrite(LED_OK, LOW);
        digitalWrite(LED_DENY, HIGH);
    } else {
        digitalWrite(LED_OK, LOW);
        digitalWrite(LED_DENY, LOW);
    }
}

void setup() {
    Serial.begin(9600);       // UART az ESP felé
    SPI.begin();
    rfid.PCD_Init();

    pinMode(LED_OK, OUTPUT);
    pinMode(LED_DENY, OUTPUT);
    digitalWrite(LED_OK, LOW);
    digitalWrite(LED_DENY, LOW);

    Serial.println("Arduino READY");
}

void loop() {
    // --- RFID olvasás ---
    if (rfid.PICC_IsNewCardPresent() && rfid.PICC_ReadCardSerial()) {
        String cardID = "";
        for (byte i = 0; i < rfid.uid.size; i++) {
            cardID += String(rfid.uid.uidByte[i], HEX);
        }
        cardID.toUpperCase();

        if (cardID != lastCard) {
            lastCard = cardID;
            sendJsonToESP(cardID);
        }

        rfid.PICC_HaltA();
        rfid.PCD_StopCrypto1();
    }

    // --- ESP-től érkező JSON feldolgozása (NE küld vissza!) ---
    while (Serial.available()) {
        char c = Serial.read();
        if (c == '|') {
            msgBuffer[bufIndex] = '\0';

            // Csak feldolgozás LED-hez
            processBackendJSON(msgBuffer);

            // BUFFER TISZTÍTÁS
            bufIndex = 0;
            msgBuffer[0] = '\0';
        } else {
            if (bufIndex < BUF_SIZE - 1) msgBuffer[bufIndex++] = c;
        }
    }
}
