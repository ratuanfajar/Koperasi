import os
from pathlib import Path
from dotenv import load_dotenv
load_dotenv()

BASE_DIR = Path(__file__).resolve().parents[1]
OUTPUT_DIR = str(BASE_DIR / 'output')
STATIC_DIR = str(BASE_DIR / 'static')
WEIGHTS_DIR = str(BASE_DIR / 'weights')

os.makedirs(OUTPUT_DIR, exist_ok=True)
os.makedirs(STATIC_DIR, exist_ok=True)
os.makedirs(WEIGHTS_DIR, exist_ok=True)

# Load env variables
OPENAI_API_KEY = os.getenv("OPENAI_API_KEY")
OPENAI_API_BASE = os.getenv("OPENAI_API_BASE")
TESSERACT_PATH = os.getenv("TESSERACT_PATH")

# Also export them to os.environ (optional)
os.environ["OPENAI_API_KEY"] = OPENAI_API_KEY
os.environ["OPENAI_API_BASE"] = OPENAI_API_BASE
os.environ["TESSERACT_PATH"] = TESSERACT_PATH

# Optional settings
REAL_ESRGAN_MODEL = str(Path(WEIGHTS_DIR) / 'realesr-general-x4v3.pth')
