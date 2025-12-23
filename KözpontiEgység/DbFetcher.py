import json
import pymysql
import paho.mqtt.client as mqtt

MQTT_BROKER = "127.0.0.1"
MQTT_PORT = 1883

MQTT_AUTH_TOPIC = "hotel/+/auth"
MQTT_RESULT_TOPIC = "hotel/{room}/result"

DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",
    "database": "hotelflowlocal",
    "cursorclass": pymysql.cursors.DictCursor
}

def check_access(card_id: str, room_name: str) -> bool:
    try:
        conn = pymysql.connect(**DB_CONFIG)
        with conn.cursor() as cur:
            cur.execute(
                """
                SELECT 1
                FROM rfidConnections
                WHERE `key` = %s
                  AND roomName = %s
                LIMIT 1
                """,
                (card_id, room_name)
            )
            return cur.fetchone() is not None
    except Exception as e:
        print("DB ERROR:", e)
        return False
    finally:
        try:
            conn.close()
        except:
            pass

def on_message(client, userdata, msg):
    payload = json.loads(msg.payload.decode())

    card_id = payload.get("cardID")
    room_name = payload.get("doorID")   # pl. "Room 868"

    if not card_id or not room_name:
        return

    allowed = check_access(card_id, room_name)

    result = {
        "accessResult": "OK" if allowed else "DENY"
    }

    # topic továbbra is technikai (szóköz NINCS!)
    topic_room = msg.topic.split("/")[1]  # room868
    result_topic = MQTT_RESULT_TOPIC.format(room=topic_room)

    client.publish(result_topic, json.dumps(result), qos=1)

    print("RX:", payload)
    print("TX:", result_topic, result)

def main():
    client = mqtt.Client("hotel-auth-service")
    client.on_message = on_message

    client.connect(MQTT_BROKER, MQTT_PORT, 60)
    client.subscribe(MQTT_AUTH_TOPIC)

    print("Auth service running...")
    client.loop_forever()

if __name__ == "__main__":
    main()
