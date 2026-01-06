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
    conn = pymysql.connect(**DB_CONFIG)
    cur = conn.cursor()
    cur.execute("""
        SELECT 1
        FROM rfidConnections
        WHERE rfidKey = %s AND roomName = %s
        LIMIT 1
    """, (card_id, room_name))
    result = cur.fetchone()
    cur.close()
    conn.close()
    return result is not None

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
    client = mqtt.Client(client_id="hotel-auth-service")
    client.on_connect = on_connect
    client.on_message = on_message
    client.connect(MQTT_BROKER, MQTT_PORT, 60)
    client.loop_forever()

if __name__ == "__main__":
    main()
