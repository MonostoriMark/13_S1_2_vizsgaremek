import pymysql
import json
from post import put_request_example
import serial
import time
import qrReader

# MySQL KONFIGURÁCIÓ
db_config = {
    'user': 'appuser',
    'password': '123',
    'host': '127.0.0.1',
    'database': 'hotelflowlocal',
    'cursorclass': pymysql.cursors.DictCursor  # DictCursor a könnyebb kezeléshez
}


RFID_LOCKER_MAP = {
    "F4E4C928": (0, 0),
    "HUOHDSPHI": (0, 1)
}

#SERIAL_PORT = "/dev/ttyACM0"   # Linux
SERIAL_PORT = "COM4"   # Windows
BAUDRATE = 9600

def get_arduino():
    try:
        ser = serial.Serial(SERIAL_PORT, BAUDRATE, timeout=2)
        time.sleep(2)  # Arduino reset
        return ser
    except serial.SerialException as e:
        print("⚠️ Arduino nem elérhető:", e)
        return None

def open_locker(ser, locker_id: int, timeout=30):
    """
    Nyitja a szekrényt az Arduino-n.
    Vár az OPENED visszajelzésre, de nem blokkolja a soros port figyelést hosszú ideig.
    """
    if not ser:
        print("⚠️ Nincs soros kapcsolat, szekrény nem nyitható.")
        return False

    ser.write(f"OPEN;{locker_id}\n".encode())
    start_time = time.time()

    while True:
        if ser.in_waiting:
            line = ser.readline().decode().strip()
            print("Arduino:", line)
            if line == f"OPENED;{locker_id}":
                print(f"✅ Szekrény {locker_id} nyitva (relé húzva)")
                return True  # visszatér, Python folytatja
        if time.time() - start_time > timeout:
            print(f"⏰ Időtúllépés – nem kaptunk nyitás visszajelzést a {locker_id}-hez")
            return False
        time.sleep(0.05)  # kicsit lazítunk a CPU-n



def locker_id_from_matrix(row, col, cols=1):
    return row * cols + col

def display_lcd(ser, msg, scroll_delay=0.7, scroll_repeat=2):
    """
    Kiírja az msg szöveget az Arduino LCD-re.
    Dinamikusan 16 karakteres ablakokkal scrolloz, ha hosszabb a szöveg.
    """
    if not ser:
        print("⚠️ Arduino nem elérhető.")
        return False

    # Ellenőrizzük, hogy a soros port üres-e, majd küldjük a DISPLAY parancsot
    # Scroll és repeat paramétereket az Arduino oldalon kell beállítani fixen
    cmd = f"DISPLAY;{msg}\n"
    ser.write(cmd.encode())

    # Várunk visszajelzést
    start_time = time.time()
    while True:
        if time.time() - start_time > 5:  # max 5 másodperc várakozás
            print("⏰ Nincs válasz az Arduino-tól")
            return False

        if ser.in_waiting:
            response = ser.readline().decode().strip()
            if response == "DISPLAY_OK":
                return True
            else:
                print("Arduino válasz:", response)

def process_serial(ser):
    """
    Ellenőrzi a soros portot: LCD visszajelzés, zár visszazárás, stb.
    Ezt a fő ciklusban kell hívni, nem threadben.
    """
    while ser.in_waiting:
        line = ser.readline().decode().strip()
        if line.startswith("STATE;CLOSED;"):
            locker_id = int(line.split(";")[2])
            print(f"🔒 Szekrény {locker_id} visszazárva.")
        elif line == "DISPLAY_OK":
            # LCD visszajelzés logolása, ha kell
            pass
        else:
            print("Arduino üzenet:", line)


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


           # -----------------------------
            # FOGLALÁSHOZ TARTOZÓ SZOBÁK
            # -----------------------------
            cursor.execute("""
                SELECT rooms_id
                FROM relations
                WHERE booking_id = %s
            """, (booking_id,))
            rooms = cursor.fetchall()

            if not rooms:
                print("⚠️ Nincs szoba a foglaláshoz rendelve!")
            else:
                print("\nRFID kulcsok és szekrény koordináták:")

                for room in rooms:
                    room_id = room["rooms_id"]

                    # -----------------------------
                    # SZOBÁHOZ TARTOZÓ RFID KULCS
                    # -----------------------------
                    cursor.execute("""
                        SELECT rfidKey
                        FROM rfidConnections
                        WHERE roomId = %s
                    """, (room_id,))
                    rfid = cursor.fetchone()

                    if not rfid:
                        print(f"⚠️ Nincs RFID kulcs a(z) {room_id} szobához!")
                        continue

                    rfid_key = rfid["rfidKey"]

                    # -----------------------------
                    # RFID → SZEKRÉNY MÁTRIX
                    # -----------------------------
                    if rfid_key in RFID_LOCKER_MAP:
                        row, col = RFID_LOCKER_MAP[rfid_key]

                        locker_id = locker_id_from_matrix(row, col)

                        print(
                            f"Szoba {room_id} | "
                            f"RFID {rfid_key} → Szekrény [{row}][{col}] (ID={locker_id})"
                        )

                        if open_locker(arduino, locker_id):
                            print(f"✅ Szekrény {locker_id} nyitva")
                            display_lcd(arduino, f"Vegye el a kártyát a szekrényből!")
                        else:
                            print(f"❌ Szekrény {locker_id} nem nyílt")
                            display_lcd(arduino, f"Valami nem működik!")


        else:
            print("\nVendég kijelentkeztetése...")
            cursor.execute("""
                UPDATE bookings
                SET checkInstatus='CheckedOut',
                    checkOutTime=NOW()
                WHERE id=%s
            """, (booking_id,))

            # -----------------------------
            # SZEKRÉNY(ek) NYITÁSA A KÁRTYA VISSZAVÉTELÉHEZ
            # -----------------------------
            cursor.execute("""
                SELECT rooms_id
                FROM relations
                WHERE booking_id = %s
            """, (booking_id,))
            rooms = cursor.fetchall()

            if rooms:
                print("\nSzekrények nyitása a kártya visszaadásához:")
                for room in rooms:
                    room_id = room["rooms_id"]

                    cursor.execute("""
                        SELECT rfidKey
                        FROM rfidConnections
                        WHERE roomId = %s
                    """, (room_id,))
                    rfid = cursor.fetchone()

                    if not rfid:
                        print(f"⚠️ Nincs RFID kulcs a(z) {room_id} szobához!")
                        continue

                    rfid_key = rfid["rfidKey"]

                    if rfid_key in RFID_LOCKER_MAP:
                        row, col = RFID_LOCKER_MAP[rfid_key]
                        locker_id = locker_id_from_matrix(row, col)

                        print(f"Szoba {room_id} | RFID {rfid_key} → Szekrény ID={locker_id}")

                        if open_locker(arduino, locker_id):
                            print(f"✅ Szekrény {locker_id} nyitva")
                            display_lcd(arduino, f"Tegye vissza a kártyát a szekrénybe!")
                        else:
                            print(f"❌ Szekrény {locker_id} nem nyílt")
                            display_lcd(arduino, f"Valami nem működik!")
                    else:
                        print(f"⚠️ RFID {rfid_key} nincs a szekrény mátrixban!")

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
        display_lcd(arduino, "Kellemes időtöltést! :)")

    except pymysql.MySQLError as err:
        print(f"Adatbázis hiba: {err}")
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()
        if 'arduino' in locals() and arduino:
            arduino.close()

arduino = get_arduino()
display_lcd(arduino, "Kérjük olvassa le a QR kódot a bejelentkezéshez!")

# Fő ciklus, ami folyamatosan feldolgozza a soros portot
while True:
    if arduino:
        process_serial(arduino)  # olvassa az Arduino üzeneteit
        try:
            while not found:
                found = qrReader.scan_frame()  # csak lekéri a QR-eket
                if found:
                    print("Talált QR-ek:", found)
                    check_in_out(found)  # csak az első QR-t használjuk, ha több van
                time.sleep(0.1)
        except KeyboardInterrupt:
            print("Leállítás")
            break
        
    time.sleep(0.05)