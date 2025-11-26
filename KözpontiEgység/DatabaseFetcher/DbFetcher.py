import requests
import sqlite3
import urllib3
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)


API_URL = "http://172.16.27.2:8000/api/devices/bookings/37"


# ---- 1. Lekérés a backendtől ----
response = requests.get(API_URL, verify=False)
data = response.json()

# --- Kulcsok szerinti szétbontás ---
bookings = data.get("bookings", [])
rooms = data.get("rooms", [])
relations = data.get("relations", [])


# ---- 2. Csatlakozás a SQLite-hoz ----
conn = sqlite3.connect("hotelflowLocal.db")
cursor = conn.cursor()



# ---- 3. Tábla létrehozása (ha nem létezik) ----

# Foglalások tábla
cursor.execute("""
CREATE TABLE IF NOT EXISTS bookings (
    id INTEGER PRIMARY KEY,
	"users_id"	INTEGER,
	"startDate"	DATE,
	"endDate"	DATE,
	"checkInToken"	TEXT,
	"checkInstatus"	TEXT,
	"checkInTime"	DATE,
	"checkOutTime"	DATE,
	"status"	TEXT
    )
""")

# Szobák tábla
cursor.execute("""
CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY,
    name TEXT
)
""")  

# Foglalások szobák kapcsoló tabla
cursor.execute("""
CREATE TABLE IF NOT EXISTS relations (
    "booking_id"	INTEGER,
    "rooms_id"	INTEGER
)
""")



# ---- 4. Adatok beszúrása ----
for b in bookings:
    cursor.execute("""
        INSERT OR REPLACE INTO bookings VALUES (
            :id,
            :users_id,
            :startDate,
            :endDate,
            :checkInToken,
            :checkInstatus,
            :checkInTime,
            :checkOutTime,       
            :status
        )
    """, b)

for r in rooms:
    cursor.execute("""
        INSERT OR REPLACE INTO rooms VALUES (
            :id,
            :name
        )
    """, r)

for rel in relations:
    cursor.execute("""
        INSERT OR REPLACE INTO relations VALUES (
            :booking_id,
            :rooms_id
        )
    """, rel)



# ---- 5. Mentés és lezárás ----
conn.commit()
conn.close()

print("Sikeresen elmentve a helyi SQLite adatbázisba.")
