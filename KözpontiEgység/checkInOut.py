import mysql.connector
import json
#from qrReader import read_qr_from_image
from post import put_request_example

# MySQL KONFIGURÁCIÓ
db_config = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'hotelflowlocal'
}

# 1. QR kód olvasása
auth_token = "a5Fld5GHJVoIzNiF"
#print("Auth Token:", auth_token)

try:
    conn = mysql.connector.connect(**db_config)
    cursor = conn.cursor()

    # Biztosítjuk, hogy a pending_requests tábla létezik MySQL-ben
    cursor.execute("""
        CREATE TABLE IF NOT EXISTS pending_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT,
            payload JSON,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    """)

    # -----------------------------
    # PENDING REQUESTS újraküldése
    # -----------------------------
    cursor.execute("SELECT id, booking_id, payload FROM pending_requests ORDER BY id ASC")
    pending = cursor.fetchall()

    if pending:
        print(f"{len(pending)} korábbi sikertelen kérés újraküldése...")
        for row in pending:
            req_id, b_id, payload_json = row
            # MySQL-nél a JSON mező lehet string vagy diktátum a konnektortól függően
            payload_data = json.loads(payload_json) if isinstance(payload_json, str) else payload_json

            if put_request_example(payload_data, b_id):
                cursor.execute("DELETE FROM pending_requests WHERE id = %s", (req_id,))
                print(f"Kérés sikeresen szinkronizálva: {req_id}")
            else:
                print("A szerver továbbra sem elérhető, az újraküldés megszakítva.")
                break

    # -----------------------------
    # Foglalás lekérése
    # -----------------------------
    query = "SELECT id, checkInstatus, checkInTime, checkOutTime FROM bookings WHERE checkInToken = %s"
    cursor.execute(query, (auth_token,))
    booking = cursor.fetchone()

    if not booking:
        print("Nincs ilyen foglalás.")
        exit()

    booking_id, status, db_checkInTime, db_checkOutTime = booking
    print("Aktuális státusz:", status)

    # -----------------------------
    # CHECK-IN / CHECK-OUT logika (MySQL szintaxis)
    # -----------------------------
    if status is None or status == "" or status == "confirmed":
        print("\nVendég bejelentkeztetése...")
        # MySQL-ben a NOW() használatos, az időzónát a szerver kezeli
        cursor.execute("""
            UPDATE bookings
            SET checkInstatus='checkedIn',
                checkInTime=NOW(),
                checkOutTime=NULL
            WHERE id=%s
        """, (booking_id,))
    else:
        print("\nVendég kijelentkeztetése...")
        cursor.execute("""
            UPDATE bookings
            SET checkInstatus='CheckedOut',
                checkOutTime=NOW()
            WHERE id=%s
        """, (booking_id,))

    conn.commit()

    # -----------------------------
    # ÚJRA LEKÉRDEZZÜK A FRISS ADATOKAT
    # -----------------------------
    cursor.execute("SELECT checkInstatus, checkInTime, checkOutTime FROM bookings WHERE id = %s", (booking_id,))
    new_status, new_checkInTime, new_checkOutTime = cursor.fetchone()

    # MySQL DATETIME objektumot JSON-kompatibilis stringgé kell alakítani
    payload = {
        "checkInstatus": new_status,
        "checkInTime": new_checkInTime.strftime('%Y-%m-%d %H:%M:%S') if new_checkInTime else None,
        "checkOutTime": new_checkOutTime.strftime('%Y-%m-%d %H:%M:%S') if new_checkOutTime else None
    }

    print("\nPayload backend felé:", payload)

    # -----------------------------
    # Backend frissítése
    # -----------------------------
    success = put_request_example(payload, booking_id)

    if not success:
        print("A szerver nem érhető el. Mentés a pending_requests táblába...")
        cursor.execute(
            "INSERT INTO pending_requests (booking_id, payload) VALUES (%s, %s)",
            (booking_id, json.dumps(payload))
        )
        conn.commit()

    print("\nMűvelet kész.")

except mysql.connector.Error as err:
    print(f"Adatbázis hiba: {err}")
finally:
    if 'conn' in locals() and conn.is_connected():
        cursor.close()
        conn.close()