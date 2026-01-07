import json
import pymysql
import paho.mqtt.client as mqtt

# -------- CONFIG --------
MQTT_BROKER = "192.168.1.35"
MQTT_PORT = 1883

DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "appuser",
    "password": "123",
    "database": "hotelflowLocal"
}

# -------- SIMPLE SIG (UGYANAZ, MINT ARDUINO) --------
def simple_sig(cardID: str, doorID: str, ts: int) -> int:
    s = 0xBEEF
    for c in cardID:
        s ^= ord(c)
        s = ((s << 5) | (s >> 11)) & 0xFFFF
    for c in doorID:
        s ^= ord(c)
        s = ((s << 5) | (s >> 11)) & 0xFFFF
    for i in range(4):
        s ^= (ts >> (i*8)) & 0xFF
        s = ((s << 3) | (s >> 13)) & 0xFFFF
    return s

# -------- DB CHECK --------
def is_allowed(card_id: str, room_name: str) -> bool:
    import pymysql

    conn = pymysql.connect(**DB_CONFIG)
    cur = conn.cursor(pymysql.cursors.DictCursor)

    try:
        # 1️⃣ Szoba lekérése
        cur.execute("SELECT * FROM rooms WHERE name = %s", (room_name,))
        room = cur.fetchone()
        if not room:
            return False  # Szoba nem létezik

        room_id = room["id"]

        # 2️⃣ Ellenőrzés: kulcs + foglalás + checkedIn
        cur.execute("""
            SELECT b.id
            FROM rfidConnections rc
            JOIN relations r ON r.rooms_id = rc.roomId
            JOIN bookings b ON b.id = r.booking_id
            WHERE rc.rfidKey = %s
              AND rc.roomId = %s
              AND b.checkInstatus = 'checkedIn'
            LIMIT 1
        """, (card_id, room_id))

        record = cur.fetchone()
        return record is not None

    finally:
        cur.close()
        conn.close()



# -------- MQTT CALLBACKS --------
def on_connect(client, userdata, flags, rc):
    print("MQTT connected:", rc)
    client.subscribe("hotel/+/auth")

def on_message(client, userdata, msg):
    try:
        payload = json.loads(msg.payload.decode())
    except Exception:
        print("Invalid JSON")
        return

    card_id = payload.get("cardID")
    room = payload.get("doorID")
    ts = payload.get("ts")
    sig = payload.get("sig")

    if not all([card_id, room, ts, sig]):
        print("Missing fields")
        return

    expected_sig = simple_sig(card_id, room, ts)

    if expected_sig != sig:
        print(f"BAD SIGNATURE: card={card_id}, ts={ts}, sig={sig}, expected={expected_sig}")
        result = "DENY"
    else:
        allowed = is_allowed(card_id, room)
        result = "OK" if allowed else "DENY"

    # --- Visszaküldött üzenet most már tartalmazza a sig-et is ---
    response = {
        "accessResult": result,
        "ts": ts,
        "sig": sig  # ez a kulcs, amit az Arduino ellenőrizni fog
    }

    response_topic = f"hotel/{room}/result"
    client.publish(response_topic, json.dumps(response), qos=1)
    print(f"{card_id} -> {room}: {result}")
    
# -------- MAIN --------
def main():
    client = mqtt.Client(client_id="hotel-auth-service-001")
    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(MQTT_BROKER, MQTT_PORT, keepalive=60)
    client.loop_forever()

if __name__ == "__main__":
    main()
