#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Arduino.h>

int relayPins[] = {12, 13};
const int relayCount = sizeof(relayPins) / sizeof(relayPins[0]);
int sensorPins[] = {8, 9};

bool lockerOpened = false;
bool waitingForClose = false;
int activeLockerId = -1;
int lastState = HIGH;

LiquidCrystal_I2C lcd(0x27, 16, 2);

String input = "";

// ---- Scroll állapot ----
String scrollMsg = "";
int scrollRow = 0;
int scrollStart = 0;
int scrollLength = 0;
int scrollRepeatCount = 0;
int scrollRepeatMax = 2;
unsigned long lastScrollTime = 0;
int scrollDelay = 700;   // ms
int scrollStep = 1;      // hány karakterrel lép

String replaceAccents(String msg) {
  msg.replace("á", "a"); msg.replace("é", "e"); msg.replace("í", "i");
  msg.replace("ó", "o"); msg.replace("ö", "o"); msg.replace("ő", "o");
  msg.replace("ú", "u"); msg.replace("ü", "u"); msg.replace("ű", "u");
  return msg;
}

void openLocker(int id) {
  if (id < 0 || id >= relayCount) {
    Serial.println("ERROR;INVALID_ID");
    return;
  }
  activeLockerId = id;
  digitalWrite(relayPins[id], HIGH);
  waitingForClose = true;
  lockerOpened = false;
  Serial.print("OPENED;");  // <---- AZ INSTANT VISSZAJELZÉS
  Serial.println(id);
}

// ---- Scroll inicializálása ----
void startScroll(String msg) {
  scrollMsg = replaceAccents(msg);
  scrollRow = 0;
  scrollStart = 0;
  scrollLength = scrollMsg.length();
  scrollRepeatCount = 0;
  lcd.clear();
  lastScrollTime = millis();
}

// ---- Scroll feldolgozás (nem blokkoló) ----
void handleScroll() {
  if (scrollMsg == "") return; // nincs scroll

  unsigned long now = millis();
  if (now - lastScrollTime >= scrollDelay) {
    lastScrollTime = now;

    // Kiírjuk az aktuális ablakot
    int end = scrollStart + 16;
    if (end > scrollLength) end = scrollLength;
    lcd.setCursor(0, scrollRow);
    lcd.print("                "); // törlés
    lcd.setCursor(0, scrollRow);
    lcd.print(scrollMsg.substring(scrollStart, end));

    scrollStart += scrollStep;
    if (scrollStart + 16 > scrollLength) {
      scrollStart = 0;
      scrollRepeatCount++;
      if (scrollRepeatCount >= scrollRepeatMax) {
        scrollMsg = ""; // scroll kész
      }
    }
  }
}

void sendLockState() {
  // FIX: sensorPin is not defined - should use sensorPins array
  if (activeLockerId != -1) {
    int state = digitalRead(sensorPins[activeLockerId]);
    if (state == LOW) Serial.println("STATE;CLOSED");
    else Serial.println("STATE;OPEN");
  } else {
    Serial.println("STATE;UNKNOWN");
  }
}

void setup() {
  Serial.begin(9600);
  for (int i = 0; i < relayCount; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], LOW);
  }
  // FIX: sensorPin is not defined - initialize all sensor pins
  for (int i = 0; i < relayCount; i++) {
    pinMode(sensorPins[i], INPUT_PULLUP);
  }
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.print("Ready...");
}

void loop() {
  // ---- Soros feldolgozás ----
  while (Serial.available()) {
    char c = Serial.read();
    if (c == '\n') {
      input.trim();
      if (input.startsWith("OPEN;")) {
        int id = input.substring(5).toInt();
        openLocker(id);
      } else if (input.startsWith("DISPLAY;")) {
        startScroll(input.substring(8));
        Serial.println("DISPLAY_OK");
      } else if (input == "CLEAR") {
        lcd.clear();
        scrollMsg = "";
        Serial.println("DISPLAY_OK");
      } else if (input == "STATE") {
        sendLockState();
      }
      input = "";
    } else {
      input += c;
    }
  }

  // ---- Scroll feldolgozás ----
  handleScroll();

  // ---- Automatikus állapotfigyelés ----
  if (waitingForClose && activeLockerId != -1) {
    int currentState = digitalRead(sensorPins[activeLockerId]);

    if (!lockerOpened && currentState == HIGH) {
      lockerOpened = true;  // egyszer rögzítjük a nyitást
    }

    // Visszazárás érzékelése
    if (lockerOpened && currentState == LOW) {
      digitalWrite(relayPins[activeLockerId], LOW); // relé vissza
      Serial.print("STATE;CLOSED;");
      Serial.println(activeLockerId);

      waitingForClose = false;
      lockerOpened = false;
      activeLockerId = -1; // visszaállítjuk nullára
    }
  }
}