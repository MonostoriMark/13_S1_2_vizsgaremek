import paho.mqtt.client as mqtt
import json
import mysql.connector
from datetime import datetime

# --- KONFIGURÁCIÓ ---
MQTT_BROKER = "127.0.0.1"
AUTH_TOKEN_FIX = "ABCD"  # Ezt a tokent várjuk a kérésekben
TOPIC_SUB = "hotel/+/auth"

def check_db_access(room_name, rfid_key):
    """
    Csak az RFID és a szoba kapcsolatát, valamint az időpontot ellenőrzi.
    """
    try:
        # Cseréld ki a saját adataidra
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password="",
            database="hotelflowlocal"
        )
        cursor = conn.cursor(dictionary=True)

        query = """
            SELECT b.id FROM bookings b
            JOIN relations rel ON b.id = rel.booking_id
            JOIN rooms r ON rel.rooms_id = r.id
            JOIN rfidkeys rfk ON rfk.rfidKey = %s
            WHERE r.name = %s 
            AND b.status = 'active'
            AND CURDATE() BETWEEN b.startDate AND b.endDate
            AND rfk.isUsed = 1
        """
        cursor.execute(query, (rfid_key, room_name))
        result = cursor.fetchone()
        
        conn.close()
        return "OK" if result else "DENY"
    except Exception as e:
        print(f"Adatbázis hiba: {e}")
        return False

# --- MQTT ESEMÉNYEK ---

def on_message(client, userdata, msg):
    # Topik darabolása: hotel/room1/auth -> room1
    topic_parts = msg.topic.split('/')
    if len(topic_parts) < 3: return
    room_name = topic_parts[1]

    try:
        data = json.loads(msg.payload.decode())
        rfid = data.get("rfidKey")
        received_token = data.get("authToken")

        # 1. Lépés: Fix token ellenőrzése (gyorsabb, mint a DB)
        if received_token != AUTH_TOKEN_FIX:
            authorized = False
            print(f"LOG: HIBÁS TOKEN! Szoba: {room_name}")
        else:
            # 2. Lépés: Adatbázis ellenőrzés (RFID, Szoba, Időpont)
            authorized = check_db_access(room_name, rfid)

        # Válasz összeállítása
        response = {
            "rfidKey": rfid,
            "roomName": room_name,
            "authorized": authorized
        }

        # Válasz küldése a dinamikus topikra: hotel/room1/response
        res_topic = f"hotel/{room_name}/response"
        client.publish(res_topic, json.dumps(response))
        
        status_txt = "OK" if authorized else "DENY"
        print(f"Válasz elküldve: {res_topic} -> {status_txt}")

    except Exception as e:
        print(f"Hiba a feldolgozás során: {e}")

# --- INDÍTÁS ---
client = mqtt.Client()
client.on_message = on_message
client.on_connect = lambda c, u, f, rc: c.subscribe(TOPIC_SUB)

print(f"Szerver indul... Figyelt topik: {TOPIC_SUB}")
client.connect(MQTT_BROKER, 1883, 60)
client.loop_forever()