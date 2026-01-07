import requests
import json
import pymysql

DB_CONFIG = {
    'user': 'appuser',
    'password': '123',
    'host': '127.0.0.1',
    'database': 'hotelflowLocal'
}

def put_request_example(payload, booking_id):
    url_template = "http://172.16.50.41:8000/api/devices/update-booking/"
    headers = {
        "Content-Type": "application/json"
    }

    conn = None
    cursor = None
    try:
        conn = pymysql.connect(**DB_CONFIG, cursorclass=pymysql.cursors.DictCursor)
        cursor = conn.cursor()

        # --- 1. PENDING REQUESTS ÚJRAKÜLDÉSE ---
        cursor.execute("SELECT id, booking_id, payload FROM pending_requests ORDER BY id ASC")
        pending_items = cursor.fetchall()

        if pending_items:
            print(f"Függőben lévő kérések feldolgozása ({len(pending_items)} db)...")
            for item in pending_items:
                p_id = item['id']
                p_booking_id = item['booking_id']
                p_payload_raw = item['payload']

                # JSON dekódolás
                try:
                    if isinstance(p_payload_raw, (bytes, bytearray)):
                        p_payload_raw = p_payload_raw.decode('utf-8')
                    if isinstance(p_payload_raw, str):
                        p_payload = json.loads(p_payload_raw)
                    else:
                        p_payload = p_payload_raw
                except Exception as e:
                    print(f"Hiba a JSON dekódolásánál (ID: {p_id}): {e}")
                    continue

                try:
                    p_url = f"{url_template}{p_booking_id}"
                    p_res = requests.put(p_url, headers=headers, json=p_payload, timeout=3)
                    
                    if 200 <= p_res.status_code < 300:
                        cursor.execute("DELETE FROM pending_requests WHERE id = %s", (p_id,))
                        conn.commit()
                        print(f"Sikeres utólagos szinkronizáció: ID {p_id}")
                    else:
                        break
                except Exception:
                    break

        # --- 2. AZ AKTUÁLIS KÉRÉS KÜLDÉSE ---
        current_url = f"{url_template}{booking_id}"
        response = requests.put(current_url, headers=headers, json=payload, timeout=3)

        if 200 <= response.status_code < 300:
            print("Sikeres frissítés backend felé:", response.status_code)
            return True
        else:
            print("Backend hiba:", response.status_code)
            return False

    except Exception as e:
        print(f"Hálózati hiba vagy szerver nem elérhető: {e}")
        
        # --- 3. MENTÉS PENDINGBE ---
        if conn:
            try:
                payload_to_save = json.dumps(payload)
                cursor.execute(
                    "INSERT INTO pending_requests (booking_id, payload) VALUES (%s, %s)",
                    (booking_id, payload_to_save)
                )
                conn.commit()
                print("Az aktuális kérés elmentve a várólistába.")
            except Exception as db_err:
                print("Hiba a mentéskor:", db_err)
        
        return False

    finally:
        if cursor:
            cursor.close()
        if conn:
            conn.close()
