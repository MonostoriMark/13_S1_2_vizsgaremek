#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Arduino.h>

int relayPins[] = {2,3};
const int relayCount = sizeof(relayPins)/sizeof(relayPins[0]);

LiquidCrystal_I2C lcd(0x27, 16, 2);

String input = "";

// Scroll beállítások
int scroll_delay_ms = 1000;   // lassabb scroll
int scroll_repeat = 5;       // hányszor ismételje
int scroll_chars = 5;        // mennyit lépjen karakterenként

String replaceAccents(String msg) {
  msg.replace("á","a");
  msg.replace("é","e");
  msg.replace("í","i");
  msg.replace("ó","o");
  msg.replace("ö","o");
  msg.replace("ő","o");
  msg.replace("ú","u");
  msg.replace("ü","u");
  msg.replace("ű","u");
  return msg;
}

void openLocker(int id) {
  if (id < 0 || id >= relayCount) {
    Serial.println("ERROR;INVALID_ID");
    return;
  }
  digitalWrite(relayPins[id], HIGH);
  delay(1000);
  digitalWrite(relayPins[id], LOW);
  Serial.print("OK;");
  Serial.println(id);
}

void displayText(String msg) {
  msg = replaceAccents(msg);
  lcd.clear();

  int row = 0;
  int col = 0;

  for (int i = 0; i < msg.length(); i++) {
    char c = msg[i];

    if (c == '\n') {
      row++;
      col = 0;
      if (row > 1) break;
      lcd.setCursor(col,row);
    } else {
      lcd.setCursor(col,row);
      lcd.write(c);
      col++;
      if (col >= 16) { // hosszú sor scroll
        int start = i - 15;
        for (int r = 0; r < scroll_repeat; r++) { // ismétlés
          for (int scroll = 0; scroll < msg.length()-start-16; scroll+=scroll_chars) {
            lcd.setCursor(0,row);
            lcd.print(msg.substring(start+scroll, start+scroll+16));
            delay(scroll_delay_ms);
          }
        }
        break;
      }
    }
  }

  def clear_lcd(ser):
    if not ser:
        print("⚠️ Arduino nem elérhető")
        return False

    ser.write("CLEAR\n".encode())
    response = ser.readline().decode().strip()
    print("Arduino válasz:", response)
    return response == "DISPLAY_OK"

  Serial.println("DISPLAY_OK");
}

void setup() {
  Serial.begin(9600);
  for (int i = 0; i < relayCount; i++) {
    pinMode(relayPins[i], OUTPUT);
    digitalWrite(relayPins[i], LOW);
  }
  lcd.init();
  lcd.backlight();
  lcd.clear();
  lcd.print("Ready...");
}

void loop() {
  while (Serial.available()) {
    char c = Serial.read();
    if (c == '\n') {
      if (input.startsWith("OPEN;")) {
        int id = input.substring(5).toInt();
        openLocker(id);
      } else if (input.startsWith("DISPLAY;")) {
        String msg = input.substring(8);
        displayText(msg);
      } else if (input == "CLEAR") {
        lcd.clear();          
        Serial.println("DISPLAY_OK");
      }
      input = "";
    } else {
      input += c;
    }
  }
}
