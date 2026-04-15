import pymysql
import json
from post import put_request_example
import serial
import time
import qrReader
import threading
import queue

# MySQL KONFIGURÁCIÓ
db_config = {
    'user': 'appuser',
    'password': '123',
    'host': '127.0.0.1',
    'database': 'hotelflowLocal',
    'cursorclass': pymysql.cursors.DictCursor
}

RFID_LOCKER_MAP = {
    "B7E5C37A": (0, 0),
    "59EDC9B0s": (0, 1)
}

SERIAL_PORT = "/dev/ttyACM0"  # Linux
BAUDRATE = 9600

# --- Arduino kapcsolat ---
def get_arduino():
    try:
        ser = serial.Serial(SERIAL_PORT, BAUDRATE, timeout=2)
        time.sleep(2)
        return ser
    except serial.SerialException as e:
        print("⚠️ Arduino nem elérhető:", e)
        return None

# --- Szekrény nyitás ---
def open_locker(ser, locker_id: int, timeout=30):
    if not ser:
        print("⚠️ Nincs soros kapcsolat")
        return False

    ser.write(f"OPEN;{locker_id}\n".encode())
    start_time = time.time()

    while True:
        while ser.in_waiting:
            line = ser.readline().decode().strip()
            if line == f"OPENED;{locker_id}":
                print(f"✅ Szekrény {locker_id} nyitva")
                return True
        if time.time() - start_time > timeout:
            print(f"⏰ Időtúllépés – szekrény {locker_id} nem nyílt")
            return False
        time.sleep(0.05)

# --- Szekrény lezárására várás ---
def wait_for_locker_closed(ser, locker_id: int, timeout=60):
    start_time = time.time()
    while True:
        while ser.in_waiting:
            line = ser.readline().decode().strip()
            if line == f"STATE;CLOSED;{locker_id}":
                print(f"🔒 Szekrény {locker_id} visszazárva")
                return True
        if time.time() - start_time > timeout:
            print(f"⏰ Időtúllépés – szekrény {locker_id} nem záródott vissza")
            return False
        time.sleep(0.05)

def locker_id_from_matrix(row, col, cols=1):
    return row * cols + col

def display_lcd(ser, msg):
    if not ser:
        return
    ser.write(f"DISPLAY;{msg}\n".encode())
    start_time = time.time()
    while True:
        if time.time() - start_time > 5:
            return False
        while ser.in_waiting:
            response = ser.readline().decode().strip()
            if response == "DISPLAY_OK":
                return True

def process_serial(ser):
    while ser.in_waiting:
        line = ser.readline().decode().strip()
        if line.startswith("STATE;CLOSED;"):
            locker_id = int(line.split(";")[2])
            print(f"🔒 Szekrény {locker_id} visszazárva.")
        elif line == "DISPLAY_OK":
            pass
        else:
            print("Arduino üzenet:", line)

# --- Fő QR feldolgozó függvény ---
def check_in_out(auth_token: str, ser):
    conn = None
    cursor = None
    try:
        conn = pymysql.connect(**db_config)
        cursor = conn.cursor()

        cursor.execute("""
            CREATE TABLE IF NOT EXISTS pending_requests (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT,
                payload JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)

        # Korábbi sikertelen requestek újraküldése
        cursor.execute("SELECT id, booking_id, payload FROM pending_requests ORDER BY id ASC")
        pending = cursor.fetchall()
        for row in pending:
            req_id = row['id']
            b_id = row['booking_id']
            payload_data = json.loads(row['payload']) if isinstance(row['payload'], str) else row['payload']
            if put_request_example(payload_data, b_id):
                cursor.execute("DELETE FROM pending_requests WHERE id = %s", (req_id,))
        conn.commit()

        # Foglalás lekérése
        cursor.execute("SELECT id, checkInstatus FROM bookings WHERE checkInToken = %s", (auth_token,))
        booking = cursor.fetchone()
        if not booking:
            print("Nincs ilyen foglalás.")
            return

        booking_id = booking['id']
        status = booking['checkInstatus']

        if status is None or status.lower() in ["", "confirmed"]:
            # CHECK-IN
            cursor.execute("UPDATE bookings SET checkInstatus='checkedIn', checkInTime=NOW(), checkOutTime=NULL WHERE id=%s", (booking_id,))
            action_msg = "Vegye el a kártyát a szekrényből!"
        else:
            # CHECK-OUT
            cursor.execute("UPDATE bookings SET checkInstatus='CheckedOut', checkOutTime=NOW() WHERE id=%s", (booking_id,))
            action_msg = "Tegye vissza a kártyát a szekrénybe!"

        # Szobák feldolgozása
        cursor.execute("SELECT rooms_id FROM relations WHERE booking_id=%s", (booking_id,))
        rooms = cursor.fetchall()
        for room in rooms:
            room_id = room["rooms_id"]
            cursor.execute("SELECT rfidKey FROM rfidConnections WHERE roomId=%s", (room_id,))
            rfid = cursor.fetchone()
            if not rfid:
                continue
            rfid_key = rfid["rfidKey"]
            if rfid_key not in RFID_LOCKER_MAP:
                continue
            row, col = RFID_LOCKER_MAP[rfid_key]
            locker_id = locker_id_from_matrix(row, col)

            if open_locker(ser, locker_id):
                display_lcd(ser, action_msg)
                wait_for_locker_closed(ser, locker_id)
            else:
                display_lcd(ser, "Valami nem működik!")

        # Backend frissítése
        cursor.execute("SELECT checkInstatus, checkInTime, checkOutTime FROM bookings WHERE id=%s", (booking_id,))
        booking = cursor.fetchone()
        payload = {
            "checkInstatus": booking['checkInstatus'],
            "checkInTime": booking['checkInTime'].strftime('%Y-%m-%d %H:%M:%S') if booking['checkInTime'] else None,
            "checkOutTime": booking['checkOutTime'].strftime('%Y-%m-%d %H:%M:%S') if booking['checkOutTime'] else None
        }
        if not put_request_example(payload, booking_id):
            cursor.execute("INSERT INTO pending_requests (booking_id, payload) VALUES (%s, %s)", (booking_id, json.dumps(payload)))
        conn.commit()
        display_lcd(ser, "Kellemes időtöltést! :)")

    except pymysql.MySQLError as e:
        print("Adatbázis hiba:", e)
    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()

# --- Inicializálás ---
arduino = get_arduino()
if arduino:
    display_lcd(arduino, "Kérjük olvassa le a QR kódot!")

# --- QR feldolgozó loop ---
while True:
    try:
        qr_code = qrReader.scan_frame()  # visszaadja a **csak egy** QR kódot vagy None
        if qr_code:
            print(f"[MAIN] Feldolgozás: {qr_code}")
            check_in_out(qr_code, arduino)
        if arduino:
            process_serial(arduino)
        time.sleep(0.05)
    except KeyboardInterrupt:
        print("Leállítás")
        break
