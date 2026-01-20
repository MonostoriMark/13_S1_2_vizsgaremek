import pymysql
import requests
import asyncio

DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "appuser",
    "password": "123",
    "database": "hotelflowLocal"
}

API_URL = "http://172.16.50.41:8000/api/devices/bookings/38"
API_HEADERS = {
    "Accept": "application/json"
}

async def fetchDb():
    conn = pymysql.connect(**DB_CONFIG, cursorclass=pymysql.cursors.DictCursor)
    cursor = conn.cursor()

    # --- Tábla létrehozása, ha nincs ---
    tables = {
        "bookings": """
            CREATE TABLE IF NOT EXISTS bookings (
                id INT PRIMARY KEY,
                users_id INT,
                startDate DATE,
                endDate DATE,
                checkInToken VARCHAR(255),
                checkInstatus VARCHAR(255),
                checkInTime DATETIME,
                checkOutTime DATETIME,
                status VARCHAR(50)
            )
        """,
        "rooms": """
            CREATE TABLE IF NOT EXISTS rooms (
                id INT PRIMARY KEY,
                name VARCHAR(255)
            )
        """,
        "relations": """
            CREATE TABLE IF NOT EXISTS relations (
                booking_id INT,
                rooms_id INT,
                PRIMARY KEY (booking_id, rooms_id)
            )
        """,
        "rfidKeys": """
            CREATE TABLE IF NOT EXISTS rfidKeys (
                id INT PRIMARY KEY,
                hotels_id INT,
                isUsed TINYINT(1),
                rfidKey VARCHAR(50)
            )
        """,
        "rfidConnections": """
            CREATE TABLE IF NOT EXISTS rfidConnections (
                rfidKey VARCHAR(50) PRIMARY KEY,
                roomId INT,
                roomName VARCHAR(255)
            )
        """
    }

    for sql in tables.values():
        cursor.execute(sql)
    conn.commit()

    # --- API hívás ---
    response = requests.get(API_URL, headers=API_HEADERS)
    data = response.json()

    # --- Upsert függvény ---
    def upsert(cursor, table, data):
        placeholders = ", ".join("%s" for _ in data)
        columns = ", ".join(f"`{c}`" for c in data.keys())
        updates = ", ".join(f"`{k}`=VALUES(`{k}`)" for k in data.keys())
        sql = f"INSERT INTO {table} ({columns}) VALUES ({placeholders}) ON DUPLICATE KEY UPDATE {updates}"
        cursor.execute(sql, list(data.values()))

    # --- Rekordok ID listák az API alapján ---
    api_ids = {
        "bookings": [],
        "rooms": [],
        "relations": [],
        "rfidKeys": [],
        "rfidConnections": []
    }

    # --- Upsert + ID gyűjtés ---
    for booking in data.get("bookings", []):
        upsert(cursor, "bookings", booking)
        api_ids["bookings"].append(booking["id"])

    for room in data.get("rooms", []):
        upsert(cursor, "rooms", room)
        api_ids["rooms"].append(room["id"])

    for rel in data.get("relations", []):
        upsert(cursor, "relations", rel)
        api_ids["relations"].append((rel["booking_id"], rel["rooms_id"]))

    for key in data.get("rfidKeys", []):
        upsert(cursor, "rfidKeys", key)
        api_ids["rfidKeys"].append(key["id"])

    for connData in data.get("rfidConnections", []):
        connData['rfidKey'] = connData.pop('key')
        upsert(cursor, "rfidConnections", connData)
        api_ids["rfidConnections"].append(connData["rfidKey"])

    # --- Lokális törlés, ami nincs az API-ban ---
    if api_ids["bookings"]:
        placeholders = ','.join(['%s'] * len(api_ids["bookings"]))
        cursor.execute(f"DELETE FROM bookings WHERE id NOT IN ({placeholders})", api_ids["bookings"])

    if api_ids["rooms"]:
        placeholders = ','.join(['%s'] * len(api_ids["rooms"]))
        cursor.execute(f"DELETE FROM rooms WHERE id NOT IN ({placeholders})", api_ids["rooms"])

    if api_ids["relations"]:
        cursor.execute("SELECT booking_id, rooms_id FROM relations")
        for row in cursor.fetchall():
            if (row["booking_id"], row["rooms_id"]) not in api_ids["relations"]:
                cursor.execute(
                    "DELETE FROM relations WHERE booking_id=%s AND rooms_id=%s",
                    (row["booking_id"], row["rooms_id"])
                )

    if api_ids["rfidKeys"]:
        placeholders = ','.join(['%s'] * len(api_ids["rfidKeys"]))
        cursor.execute(f"DELETE FROM rfidKeys WHERE id NOT IN ({placeholders})", api_ids["rfidKeys"])

    if api_ids["rfidConnections"]:
        placeholders = ','.join(['%s'] * len(api_ids["rfidConnections"]))
        cursor.execute(
            f"DELETE FROM rfidConnections WHERE rfidKey NOT IN ({placeholders})",
            api_ids["rfidConnections"]
        )

    conn.commit()
    cursor.close()
    conn.close()
