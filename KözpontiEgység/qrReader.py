from picamera2 import Picamera2
from pyzbar import pyzbar
import time
import logging
import checkInOut

# Logging beállítás
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s'
)

logging.info("QR olvasó debug mód indul...")

# Kamera inicializálás
picam2 = Picamera2()
picam2.configure(
    picam2.create_preview_configuration(
        main={"format": "RGB888", "size": (640, 480)}
    )
)
picam2.start()
time.sleep(2)  # kamera stabilizálása

# --- Ide definiáljuk a callback függvényt ---
def qr_callback(qr_data: str, qr_type: str):
    """
    Itt kell majd meghívni a projekt logikát:
    - ellenőrzés backendben / DB
    - MQTT küldés ESP felé
    - LED / ajtó vezérlés
    - snapshot mentés, logolás
    """
    logging.info(f"[CALLBACK] QR találat: {qr_type} | Tartalom: {qr_data}")
# --- Vége callback függvénynek ---

try:
    while True:
        frame = picam2.capture_array()

        barcodes = pyzbar.decode(frame)
        if barcodes:
            for barcode in barcodes:
                qr_data = barcode.data.decode("utf-8")
                qr_type = barcode.type

                logging.info(f"[QR] Típus: {qr_type} | Tartalom: {qr_data}")

                checkInOut.check_in_out(qr_data)
                
        else:
            logging.debug("Nincs QR a képkockában")

        time.sleep(1)  # CPU spórolás

except KeyboardInterrupt:
    logging.info("QR olvasó debug leállítva kézzel")

finally:
    picam2.stop()
    logging.info("Kamera leállítva")

