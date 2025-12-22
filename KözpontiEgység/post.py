import requests
import json
import mysql.connector

DB_CONFIG = {
    'user': 'root',
    'password': '',
    'host': '127.0.0.1',
    'database': 'hotelflowlocal'
}

def put_request_example(payload, booking_id):
    url_template = "http://26.34.221.136:8000/api/devices/update-booking/"
    headers = {
        "Authorization": "Bearer TOKEN",
        "Content-Type": "application/json"
    }

    conn = None
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)

        # --- 1. PENDING REQUESTS ÚJRAKÜLDÉSE ---
        cursor.execute("SELECT id, booking_id, payload FROM pending_requests ORDER BY id ASC")
        pending_items = cursor.fetchall()

        if pending_items:
            print(f"Függőben lévő kérések feldolgozása ({len(pending_items)} db)...")
            for item in pending_items:
                p_id = item['id']
                p_booking_id = item['booking_id']
                p_payload_raw = item['payload']

                # HIBAJAVÍTÁS: Bytes kezelése
                try:
                    # Ha bytes, dekódoljuk stringgé
                    if isinstance(p_payload_raw, (bytes, bytearray)):
                        p_payload_raw = p_payload_raw.decode('utf-8')
                    
                    # Ha string, alakítsuk dict-é a küldéshez
                    if isinstance(p_payload_raw, str):
                        p_payload = json.loads(p_payload_raw)
                    else:
                        p_payload = p_payload_raw # Már dict volt
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
        # Itt jelentkezett a "bytes is not JSON serializable" hiba
        print(f"Hálózati hiba vagy szerver nem elérhető: {e}")
        
        # --- 3. MENTÉS PENDINGBE ---
        if conn and conn.is_connected():
            try:
                # A biztonság kedvéért itt is stringgé alakítjuk a mentés előtt
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
        if conn and conn.is_connected():
            cursor.close()
            conn.close()