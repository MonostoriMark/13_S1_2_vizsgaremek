# qr_reader.py
from picamera2 import Picamera2
from pyzbar import pyzbar
import time
import logging

# --- Logging beállítás ---
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s [%(levelname)s] %(message)s'
)

logging.info("QR olvasó modul betöltve")

# --- Kamera inicializálás (csak egyszer) ---
picam2 = Picamera2()
picam2.configure(
    picam2.create_preview_configuration(
        main={"format": "RGB888", "size": (640, 480)}
    )
)
picam2.start()
time.sleep(2)  # kamera stabilizálása
logging.info("Kamera inicializálva")

# --- Callback függvény ---
def qr_callback(qr_data: str, qr_type: str):
    logging.info(f"[CALLBACK] QR találat: {qr_type} | Tartalom: {qr_data}")
    # A projekt fő logikája

# --- QR olvasó fő függvény ---
def scan_frame():
    """
    Egyetlen képkocka beolvasása és QR dekódolása.
    Meghívható ciklusban anélkül, hogy újraindítaná a kamerát.
    """
    frame = picam2.capture_array()
    barcodes = pyzbar.decode(frame)
    results = []

    for barcode in barcodes:
        qr_data = barcode.data.decode("utf-8")
        qr_type = barcode.type
        results.append((qr_data))
        qr_callback(qr_data, qr_type)
    
    return results
