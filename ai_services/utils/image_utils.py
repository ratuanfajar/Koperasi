import cv2
import numpy as np
import os
from config.settings import OUTPUT_DIR, TESSERACT_PATH
import pytesseract

pytesseract.pytesseract.tesseract_cmd = TESSERACT_PATH

def rotate_bound(image, angle):
    (h, w) = image.shape[:2]
    (cX, cY) = (w // 2, h // 2)
    M = cv2.getRotationMatrix2D((cX, cY), -angle, 1.0)
    cos = np.abs(M[0, 0])
    sin = np.abs(M[0, 1])
    nW = int((h * sin) + (w * cos))
    nH = int((h * cos) + (w * sin))
    M[0, 2] += (nW / 2) - cX
    M[1, 2] += (nH / 2) - cY
    return cv2.warpAffine(image, M, (nW, nH), borderMode=cv2.BORDER_REPLICATE)


def auto_orient_receipt_safe_strict(image_path, output_path=None):
    img = cv2.imread(image_path)
    if img is None:
        raise FileNotFoundError(f"Gambar '{image_path}' tidak ditemukan.")

    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

    text_base = pytesseract.image_to_string(gray, lang='eng+ind')
    score_base = sum(c.isalnum() for c in text_base)

    try:
        osd = pytesseract.image_to_osd(gray, output_type=pytesseract.Output.DICT)
        angle = osd.get('rotate', 0)
    except Exception:
        angle = 0

    if angle == 0:
        rotated = img
    else:
        rotated = rotate_bound(img, angle)
        gray_rot = cv2.cvtColor(rotated, cv2.COLOR_BGR2GRAY)
        text_rot = pytesseract.image_to_string(gray_rot, lang='eng+ind')
        score_rot = sum(c.isalnum() for c in text_rot)
        if score_rot < score_base * 0.8:
            rotated = img

    if output_path:
        cv2.imwrite(output_path, rotated)

    return rotated


def preprocess_for_trocr(img):
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    clahe = cv2.createCLAHE(clipLimit=1.5, tileGridSize=(8, 8))
    enhanced = clahe.apply(gray)
    denoised = cv2.bilateralFilter(enhanced, 7, 50, 50)
    norm = cv2.normalize(denoised, None, 0, 255, cv2.NORM_MINMAX)

    kernel = np.array([[0, -1, 0],
                       [-1, 5, -1],
                       [0, -1, 0]])
    sharpened = cv2.filter2D(norm, -1, kernel)
    return sharpened


def final_pipeline(image_path, out_dir=OUTPUT_DIR, filename='preprocessed.png', debug=True):
    os.makedirs(out_dir, exist_ok=True)
    oriented = auto_orient_receipt_safe_strict(image_path)
    preprocessed = preprocess_for_trocr(oriented)

    output_path = os.path.join(out_dir, filename)
    cv2.imwrite(output_path, preprocessed)

    if debug:
        print(f"[✅] Final saved → {output_path}")

    return output_path