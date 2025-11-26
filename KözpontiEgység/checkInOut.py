from qrReader import read_qr_from_image
import sqlite3

auth_token = read_qr_from_image("QR.png")


conn = sqlite3.connect("hotelflowLocal.db")
cursor = conn.cursor()

# --- Lekérdezés: melyik szobák tartoznak ehhez a tokenhez ---
query = """
SELECT rooms.id, rooms.name, bookings.id AS booking_id
FROM bookings
JOIN relations ON relations.bookings_id = bookings.id
JOIN rooms ON rooms.id = relations.rooms_id
WHERE bookings.checkInToken = ?
"""
cursor.execute(query, (auth_token,))
rooms_to_checkin = cursor.fetchall()

print("Szobák check-inhez:")
for room in rooms_to_checkin:
    print(f"Room ID: {room[0]}, Name: {room[1]}, Booking ID: {room[2]}")

# --- Update checkInstatus ---
update_query = """
UPDATE bookings
SET checkInstatus = 'checkIn'
WHERE checkInToken = ?
"""
cursor.execute(update_query, (auth_token,))

conn.commit()
conn.close()

