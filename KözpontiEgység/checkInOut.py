import pymysql
import json
from post import put_request_example

# MySQL KONFIGURÁCIÓ
db_config = {
    'user': 'appuser',
    'password': '123',
    'host': '127.0.0.1',
    'database': 'hotelflowLocal',
    'cursorclass': pymysql.cursors.DictCursor  # DictCursor a könnyebb kezeléshez
}

def check_in_out(auth_token: str):
    conn = None
    cursor = None
    try:
        conn = pymysql.connect(**db_config)
        cursor = conn.cursor()

        # Biztosítjuk, hogy a pending_requests tábla létezik
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
                req_id = row['id']
                b_id = row['booking_id']
                payload_data = row['payload']
                if isinstance(payload_data, str):
                    payload_data = json.loads(payload_data)

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
            return

        booking_id = booking['id']
        status = booking['checkInstatus']
        db_checkInTime = booking['checkInTime']
        db_checkOutTime = booking['checkOutTime']

        print("Aktuális státusz:", status)

        # -----------------------------
        # CHECK-IN / CHECK-OUT logika
        # -----------------------------
        if status is None or status == "" or status.lower() == "confirmed":
            print("\nVendég bejelentkeztetése...")
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
        booking = cursor.fetchone()
        new_status = booking['checkInstatus']
        new_checkInTime = booking['checkInTime']
        new_checkOutTime = booking['checkOutTime']

        # Payload a backend felé
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

    except pymysql.MySQLError as err:
        print(f"Adatbázis hiba: {err}")
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()
