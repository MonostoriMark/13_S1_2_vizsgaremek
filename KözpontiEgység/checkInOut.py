from qrReader import read_qr_from_image
import sqlite3
import json
from post import put_request_example

auth_token = read_qr_from_image("QR.png")
print("Auth Token:", auth_token)

conn = sqlite3.connect("hotelflowLocal.db")
cursor = conn.cursor()

# -----------------------------
# PENDING REQUESTS újraküldése
# -----------------------------
def resend_pending(cursor):
    cursor.execute("SELECT id, booking_id, payload FROM pending_requests ORDER BY id ASC")
    pending = cursor.fetchall()

    if not pending:
        return

    print(f"{len(pending)} korábbi sikertelen kérés újraküldése...")

    for row in pending:
        req_id, booking_id, payload_json = row
        payload = json.loads(payload_json)

        if put_request_example(payload, booking_id):
            cursor.execute("DELETE FROM pending_requests WHERE id = ?", (req_id,))
            print("Kérés sikeresen szinkronizálva:", req_id)
        else:
            print("Még mindig nem elérhető a szerver.")
            break


resend_pending(cursor)

# -----------------------------
# Foglalás lekérése
# -----------------------------
query = """
SELECT id, checkInstatus, checkInTime, checkOutTime 
FROM bookings
WHERE checkInToken = ?
"""
cursor.execute(query, (auth_token,))
booking = cursor.fetchone()

if not booking:
    print("Nincs ilyen foglalás.")
    conn.close()
    exit()

booking_id, status, checkInTime, checkOutTime = booking
print("Aktuális státusz:", status)

# -----------------------------
# CHECK-IN / CHECK-OUT logika
# -----------------------------
if status is None:
    print("\nVendég bejelentkeztetése...")

    cursor.execute("""
        UPDATE bookings
        SET checkInstatus='checkedIn',
            checkInTime=DATETIME(CURRENT_TIMESTAMP, '+1 hour'),
            checkOutTime=NULL
        WHERE id=?
    """, (booking_id,))

else:
    print("\nVendég kijelentkeztetése...")

    cursor.execute("""
        UPDATE bookings
        SET checkInstatus='CheckedOut',
            checkOutTime=DATETIME(CURRENT_TIMESTAMP, '+1 hour')
        WHERE id=?
    """, (booking_id,))

conn.commit()

# -----------------------------
# ÚJRA LEKÉRDEZZÜK A FRISS ADATOKAT !!!
# -----------------------------
cursor.execute("""
SELECT checkInstatus, checkInTime, checkOutTime
FROM bookings
WHERE id = ?
""", (booking_id,))

new_status, new_checkInTime, new_checkOutTime = cursor.fetchone()

# -----------------------------
# Payload backend felé
# -----------------------------
payload = {
    "checkInstatus": new_status,
    "checkInTime": new_checkInTime,
    "checkOutTime": new_checkOutTime
}

print("\nPayload backend felé:")
print(payload)

# -----------------------------
# Backend frissítése
# -----------------------------
success = put_request_example(payload, booking_id)

if not success:
    print("Kérés mentése pending_requests táblába...")

    cursor.execute(
        "INSERT INTO pending_requests (booking_id, payload) VALUES (?, ?)",
        (booking_id, json.dumps(payload))
    )

conn.commit()
conn.close()

print("\nMűvelet kész.")
