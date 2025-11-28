import os
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parents[1]
OUTPUT_DIR = str(BASE_DIR / 'output')
STATIC_DIR = str(BASE_DIR / 'static')
WEIGHTS_DIR = str(BASE_DIR / 'weights')

os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(STATIC_DIR, exist_ok=True)
os.makedirs(WEIGHTS_DIR, exist_ok=True)

# GROQ key taken from environment
GROQ_API_KEY = os.getenv('GROQ_API_KEY')

# Optional settings
REAL_ESRGAN_MODEL = str(Path(WEIGHTS_DIR) / 'realesr-general-x4v3.pth')