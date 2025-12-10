import requests
import sqlite3
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)


API_URL = "http://172.16.13.18:8000/api/devices/bookings/37"


# ---- 1. Lekérés a backendtől ----
response = requests.get(API_URL, verify=False)
data = response.json()

# --- Kulcsok szerinti szétbontás ---
bookings = data.get("bookings", [])
rooms = data.get("rooms", [])
relations = data.get("relations", [])
rfid_keys = data.get("rfidKeys", [])


# ---- 2. Csatlakozás a SQLite-hoz ----
conn = sqlite3.connect("hotelflowLocal.db")
cursor = conn.cursor()



# ---- 3. Tábla létrehozása (ha nem létezik) ----

# --- BOOKINGS: Insert vagy Update ---
for booking in bookings:
    cursor.execute("""
        INSERT INTO bookings (id, users_id, startDate, endDate, checkInToken, checkInTime, checkOutTime, checkInstatus, status)
        VALUES (:id, :users_id, :startDate, :endDate, :checkInToken, :checkInTime, :checkOutTime, :checkInstatus, :status)
        ON CONFLICT(id) DO UPDATE SET
            users_id=excluded.users_id,
            startDate=excluded.startDate,
            endDate=excluded.endDate,
            checkInToken=excluded.checkInToken,
            checkInstatus=excluded.checkInstatus,
            checkInTime=excluded.checkInTime,
            checkOutTime=excluded.checkOutTime,
            status=excluded.status
    """, booking)

# --- ROOMS: Insert vagy Update ---
for room in rooms:
    cursor.execute("""
        INSERT INTO rooms (id, name)
        VALUES (:id, :name)
        ON CONFLICT(id) DO UPDATE SET
            name=excluded.name
    """, room)

# --- RELATIONS: Insert vagy Update ---
for rel in relations:
    cursor.execute(
        "SELECT 1 FROM relations WHERE booking_id=? AND rooms_id=?",
        (rel["booking_id"], rel["rooms_id"])
    )
    if not cursor.fetchone():
        cursor.execute(
            "INSERT INTO relations (booking_id, rooms_id) VALUES (?, ?)",
            (rel["booking_id"], rel["rooms_id"])
        )

# --- RFID KEYS: Insert vagy Update ---
for rfid in rfid_keys:
    cursor.execute("""
        INSERT INTO rfidkeys (id, hotels_id, isUsed, rfidKey)
        VALUES (:id, :hotels_id, :isUsed, :rfidKey)
        ON CONFLICT(id) DO UPDATE SET
            hotels_id=excluded.hotels_id,
            isUsed=excluded.isUsed,
            rfidKey=excluded.rfidKey
    """, rfid)


# --- Törlés a helyi DB-ből, ha már nem szerepel a backupban ---
# Példa bookings
backend_booking_ids = [b["id"] for b in bookings]
cursor.execute(f"DELETE FROM bookings WHERE id NOT IN ({','.join(['?']*len(backend_booking_ids))})", backend_booking_ids)

# --- Törlés a helyi DB-ből, ha már nem szerepel a backendben ---

# Bookings törlése, ha már nincs a backendben
backend_booking_ids = [b["id"] for b in bookings]
if backend_booking_ids:  # elkerüli a hibát üres listánál
    cursor.execute(
        f"DELETE FROM bookings WHERE id NOT IN ({','.join(['?']*len(backend_booking_ids))})",
        backend_booking_ids
    )

# Rooms törlése, ha már nincs a backendben
backend_room_ids = [r["id"] for r in rooms]
if backend_room_ids:
    cursor.execute(
        f"DELETE FROM rooms WHERE id NOT IN ({','.join(['?']*len(backend_room_ids))})",
        backend_room_ids
    )

# Relations törlése, ha már nincs a backendben
backend_relations_set = set((rel["booking_id"], rel["rooms_id"]) for rel in relations)
cursor.execute("SELECT booking_id, rooms_id FROM relations")
for booking_id, room_id in cursor.fetchall():
    if (booking_id, room_id) not in backend_relations_set:
        cursor.execute("DELETE FROM relations WHERE booking_id=? AND rooms_id=?", (booking_id, room_id))

# RFID KEYS törlése, ha más nincs a backendben
backend_rfid_ids = [rfid["id"] for rfid in data.get("rfidKeys", [])]
if backend_rfid_ids:
    cursor.execute(
        f"DELETE FROM rfidKeys WHERE id NOT IN ({','.join(['?']*len(backend_rfid_ids))})",
        backend_rfid_ids
    )

# ---- 5. Mentés és lezárás ----
conn.commit()
conn.close()

print("Sikeresen elmentve a helyi SQLite adatbázisba.")
