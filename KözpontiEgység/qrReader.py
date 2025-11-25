import cv2
from pyzbar.pyzbar import decode

def read_qr_from_image(path):
    img = cv2.imread(path)
    qr_codes = decode(img)

    for qr in qr_codes:
        data = qr.data.decode('utf-8')
        print("QR-kód tartalma:", data)

# Példa
read_qr_from_image("QR.png")
