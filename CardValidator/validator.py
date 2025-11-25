import json
import sqlite3
import time
import paho.mqtt.client as mqtt

MQTT_BROKER = "192.168.1.35"
MQTT_PORT = 1883
MQTT_TOPIC_SUB = "hotel/room1/auth"
MQTT_TOPIC_PUB = "hotel/room1/result"

DB_FILE = "cards.db"

# --- SQLite kapcsolat és tábla létrehozás ---
def init_db():
    conn = sqlite3.connect(DB_FILE, check_same_thread=False)
    c = conn.cursor()
    # Tábla: cardID és authToken
    c.execute('''
        CREATE TABLE IF NOT EXISTS cards (
            cardID TEXT PRIMARY KEY,
            authToken TEXT NOT NULL
        )
    ''')
    conn.commit()
    return conn

# --- Ellenőrzés az adatbázisban ---
def check_card(conn, cardID, token):
    c = conn.cursor()
    c.execute("SELECT 1 FROM cards WHERE cardID=? AND authToken=?", (cardID, token))
    return c.fetchone() is not None

# --- MQTT callback ---
def on_message(client, userdata, msg):
    payload = msg.payload.decode()
    print(f"Üzenet érkezett: {payload}")

    try:
        data = json.loads(payload)
    except json.JSONDecodeError:
        print("Hibás JSON")
        return

    # Ellenőrzés mezők szerint
    cardID = data.get("cardID")
    token = data.get("authToken")
    deviceID = data.get("doorID") or data.get("deviceID") or "unknown"

    if not cardID or not token:
        print("Hiányzó cardID vagy authToken")
        return

    # --- Ellenőrzés az adatbázisban ---
    if check_card(userdata['conn'], cardID, token):
        result = "OK"
    else:
        result = "DENY"

    response = json.dumps({
        "cardID": cardID,
        "deviceID": deviceID,
        "accessResult": result
    })

    client.publish(MQTT_TOPIC_PUB, response)
    print(f"Válasz elküldve: {response}")

# --- Inicializálás ---
conn = init_db()

# Példa adatok beszúrása (csak egyszer, ha üres az adatbázis)
c = conn.cursor()
c.execute("SELECT COUNT(*) FROM cards")
if c.fetchone()[0] == 0:
    c.execute("INSERT INTO cards(cardID, authToken) VALUES (?, ?)", ("AB12CD34", "ABC123XYZ"))
    c.execute("INSERT INTO cards(cardID, authToken) VALUES (?, ?)", ("F4E4C928", "ABC123XYZ"))
    conn.commit()

client = mqtt.Client(userdata={'conn': conn})
client.on_message = on_message
client.connect(MQTT_BROKER, MQTT_PORT, 60)
client.subscribe(MQTT_TOPIC_SUB)
client.loop_start()

try:
    print("Backend fut. Ctrl+C a kilépéshez.")
    while True:
        time.sleep(1)
except KeyboardInterrupt:
    client.loop_stop()
    client.disconnect()
    conn.close()
    print("Backend leállítva.")
