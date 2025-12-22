import requests
import mysql.connector
import urllib3
import asyncio
import aiohttp

# Figyelmeztetések kikapcsolása
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

API_URL = "http://26.34.221.136:8000/api/devices/bookings/37"

db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'hotelflowlocal'
}

def setup_database(conn):
    cursor = conn.cursor()
    tables = [
        """CREATE TABLE IF NOT EXISTS bookings (
            id INT PRIMARY KEY,
            users_id INT,
            startDate DATE,
            endDate DATE,
            checkInToken VARCHAR(255),
            checkInstatus VARCHAR(50),
            checkInTime DATETIME,
            checkOutTime DATETIME,
            status VARCHAR(50)
        )""",
        """CREATE TABLE IF NOT EXISTS rooms (
            id INT PRIMARY KEY,
            name VARCHAR(255)
        )""",
        """CREATE TABLE IF NOT EXISTS relations (
            booking_id INT,
            rooms_id INT,
            PRIMARY KEY (booking_id, rooms_id)
        )""",
        """CREATE TABLE IF NOT EXISTS rfidkeys (
            id INT PRIMARY KEY,
            hotels_id INT,
            isUsed TINYINT(1),
            rfidKey VARCHAR(100)
        )""",
        """CREATE TABLE IF NOT EXISTS rfidConnections (
            `key` VARCHAR(100),
            roomId INT,
            roomName VARCHAR(255)
        )"""
    ]
    for table_sql in tables:
        cursor.execute(table_sql)
    conn.commit()
    cursor.close()

async def fetch_data_to_mysql():
    conn = None
    cursor = None # Előre definiáljuk, hogy a finally ágban ne legyen hiba
    try:
        # 1. Csatlakozás az adatbázishoz
        conn = mysql.connector.connect(**db_config)
        setup_database(conn)
        cursor = conn.cursor()

        # --- ASZINKRON LEKÉRÉS (aiohttp) ---
        # A 'verify_ssl=False' kiváltja a verify=False-t
        connector = aiohttp.TCPConnector(ssl=False)
        async with aiohttp.ClientSession(connector=connector) as session:
            print("Lekérés indítása (nem blokkoló)...")
            async with session.get(API_URL, timeout=10) as response:
                if response.status != 200:
                    print(f"Szerver hiba: {response.status}")
                    return False
                
                data = await response.json()
                print("Adatok megérkeztek.")

        bookings = data.get("bookings", [])
        rooms = data.get("rooms", [])
        relations = data.get("relations", [])
        rfid_keys = data.get("rfidKeys", [])
        rfid_connections = data.get("rfidConnections", [])

        # ---- ADATOK FRISSÍTÉSE ----
        if bookings:
            sql = """INSERT INTO bookings (id, users_id, startDate, endDate, checkInToken, checkInTime, checkOutTime, checkInstatus, status)
                     VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
                     ON DUPLICATE KEY UPDATE users_id=VALUES(users_id), startDate=VALUES(startDate), endDate=VALUES(endDate), 
                     checkInToken=VALUES(checkInToken), checkInstatus=VALUES(checkInstatus), status=VALUES(status)"""
            for b in bookings:
                cursor.execute(sql, (b['id'], b['users_id'], b['startDate'], b['endDate'], b['checkInToken'], b['checkInTime'], b['checkOutTime'], b['checkInstatus'], b['status']))

        if rooms:
            sql = "INSERT INTO rooms (id, name) VALUES (%s, %s) ON DUPLICATE KEY UPDATE name=VALUES(name)"
            for r in rooms:
                cursor.execute(sql, (r['id'], r['name']))

        for rel in relations:
            cursor.execute("INSERT IGNORE INTO relations (booking_id, rooms_id) VALUES (%s, %s)", (rel["booking_id"], rel["rooms_id"]))

        if rfid_keys:
            sql = "INSERT INTO rfidkeys (id, hotels_id, isUsed, rfidKey) VALUES (%s, %s, %s, %s) ON DUPLICATE KEY UPDATE isUsed=VALUES(isUsed), rfidKey=VALUES(rfidKey)"
            for rfid in rfid_keys:
                cursor.execute(sql, (rfid['id'], rfid['hotels_id'], rfid['isUsed'], rfid['rfidKey']))

        cursor.execute("TRUNCATE TABLE rfidConnections")
        if rfid_connections:
            sql = "INSERT INTO rfidConnections (`key`, roomId, roomName) VALUES (%s, %s, %s)"
            for c in rfid_connections:
                cursor.execute(sql, (c['key'], c['roomId'], c['roomName']))

        # ---- TÖRLÉS (Sync) ----
        if bookings:
            ids = [b["id"] for b in bookings]
            cursor.execute(f"DELETE FROM bookings WHERE id NOT IN ({','.join(['%s']*len(ids))})", tuple(ids))
        
        if rooms:
            ids = [r["id"] for r in rooms]
            cursor.execute(f"DELETE FROM rooms WHERE id NOT IN ({','.join(['%s']*len(ids))})", tuple(ids))

        conn.commit()
        print("Sikeres szinkronizáció!")
        return True

    except (requests.exceptions.RequestException, mysql.connector.Error) as e:
        print(f"Kapcsolódási hiba: {e}")
        return False
    except Exception as e:
        print(f"Váratlan hiba: {e}")
        return False
    finally:
        # Csak akkor zárjuk le, ha valóban létrejöttek
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()

async def main():
    while True:
        print("\nSzinkronizáció indítása...")
        success = await fetch_data_to_mysql()
        
        if success:
            print(f"Minden adat friss.")
            break
        else:
            # Ha hiba volt, próbáljuk újra sűrűbben (pl. 10 másodperc)
            wait_time = 60
            print(f"Újrapróbálkozás {wait_time} másodperc múlva...")
        
        await asyncio.sleep(wait_time)

if __name__ == "__main__":
    try:
        asyncio.run(main())
    except KeyboardInterrupt:
        print("\nProgram leállítva a felhasználó által.")