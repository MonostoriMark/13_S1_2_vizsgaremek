import requests
import json

def put_request_example(payload, booking_id):
    url = f"http://172.16.6.12:8000/api/devices/update-bookingg/{booking_id}"

    headers = {
        "Authorization": "Bearer TOKEN",
        "Content-Type": "application/json"
    }

    try:
        response = requests.put(url, headers=headers, json=payload, timeout=3)

        if response.status_code >= 200 and response.status_code < 300:
            print("Sikeres frissítés backend felé:", response.status_code)
            return True
        else:
            print("Backend hiba:", response.status_code, response.text)
            return False

    except Exception as e:
        print("Szerver nem elérhető:", e)
        return False
