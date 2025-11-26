import sqlite3

conn = sqlite3.connect("hotelflowLocal.db")
cursor = conn.cursor()

cursor.execute("""
        CREATE TABLE relations (
    booking_id INTEGER,
    room_id INTEGER,
    UNIQUE(booking_id, room_id)
);
""")

conn.commit()
conn.close()