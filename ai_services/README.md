# Cooperative Receipt Analysis System

A comprehensive Flask-based application for analyzing financial transaction receipts using OCR and an AI-powered Account Code Recommender (LLM).

This project uses prompt engineering (Chain-of-Thought) to provide context and base knowledge for the LLM, enabling it to analyze receipts and recommend relevant account codes accurately.
## Overview

This project automates the processing of financial transaction documents (receipts, invoices, payment slips, etc.) by:

1. **Image Enhancement**: Enhancing receipt image quality using advanced upscaling techniques
2. **Orientation Detection**: Automatically detecting and correcting receipt orientation
3. **OCR Processing**: Extracting text from receipts using PaddleOCR (supporting Indonesian language)
4. **AI Analysis**: Leveraging LLM (via Groq API) to intelligently classify transactions and recommend accounting codes

## Features

- **REST API Endpoints** for receipt analysis and health checks
- **Multi-format Image Support**: PNG, JPG, JPEG, TIFF, BMP
- **Indonesian Language Support**: Optimized OCR for Indonesian receipts
- **Real-ESRGAN Integration**: 4x image upscaling for improved OCR accuracy
- **Automatic Accounting Classification**: AI-powered recommendations for Chart of Accounts (COA) codes
- **CORS Support**: Cross-origin resource sharing for integration with frontend applications
- **Lazy Model Loading**: Efficient resource management with on-demand model initialization

## Project Structure

```
project_koperasi/
├── app.py                    # Main Flask application
├── main.py                   # Entry point script
├── koperasi.ipynb            # Jupyter notebook for development/testing
├── pyproject.toml            # Project configuration and dependencies
├── config/
│   └── settings.py           # Configuration settings
├── utils/
│   ├── image_utils.py        # Image processing utilities
│   ├── ocr_utils.py          # OCR processing utilities
│   ├── llm_utils.py          # LLM analysis utilities
│   └── enhance_utils.py      # Image enhancement utilities
├── static/                   # Upload directory for receipts
├── output/                   # Output directory for processed results
└── weights/                  # Pre-trained model weights
    └── realesr-general-x4v3.pth  # Real-ESRGAN model weights
```

## Requirements

- Python >= 3.11
- Flask >= 3.1.2
- PaddleOCR >= 3.3.2
- Real-ESRGAN for image upscaling
- Groq API for LLM analysis
- PyTorch for deep learning models

## Installation

### Option 1: Using UV (Recommended)

UV is a fast Python package installer and resolver written in Rust.

#### Prerequisites

First, ensure UV is installed in your environment:

```bash
uv --version
```

If UV is not installed, install it first:

```bash
pip install uv
```

#### Setup Steps

1. Navigate to the project directory:

```bash
cd d:\document\coding\koperasi\project_koperasi
```

2. Initialize the virtual environment (if not already done):

```bash
uv init
```

3. Sync dependencies from `pyproject.toml`:

```bash
uv sync
```

4. Activate the virtual environment:

```bash
# On Windows:
.\.venv\Scripts\Activate.ps1
# On Linux/macOS:
source .venv/bin/activate
```

#### Important: PaddleOCR Installation with UV

PaddleOCR requires special handling on Windows. After activating the virtual environment, run these commands:

```bash
uv add paddlepaddle -f https://www.paddlepaddle.org.cn/whl/windows/mkl/avx/stable.html
uv add paddleocr
```

**Note**: The `-f` flag specifies the Windows-optimized wheel URL for PaddleOCR to work properly on Windows systems.

### Option 2: Using Pip and Virtual Environment

If you don't have UV installed:

#### 1. Create Virtual Environment

```bash
python -m venv .venv
# On Windows:
.\.venv\Scripts\Activate.ps1
# On Linux/macOS:
source .venv/bin/activate
```

#### 2. Install Dependencies

```bash
pip install -r requirements.txt
```

#### 3. Install PaddleOCR (Windows)

```bash
pip install paddlepaddle -f https://www.paddlepaddle.org.cn/whl/windows/mkl/avx/stable.html
pip install paddleocr
```

### Main Dependencies

- `flask>=3.1.2` - Web framework
- `flask-cors>=6.0.1` - CORS support
- `paddlepaddle>=3.2.2` - PaddleOCR backend
- `paddleocr>=3.3.2` - OCR engine
- `realesrgan>=0.3.0` - Image enhancement
- `groq>=0.36.0` - LLM API client
- `opencv-python>=4.11.0.86` - Computer vision
- `pillow>=12.0.0` - Image processing
- `torch>=2.9.1` - Deep learning framework
- `pytesseract>=0.3.13` - Tesseract OCR interface

### 4. Environment Variables

Create a `.env` file in the project root or export environment variables:

**On Windows PowerShell:**
```powershell
$env:GROQ_API_KEY="your-groq-api-key-here"
```

**On Linux/macOS:**
```bash
export GROQ_API_KEY="your-groq-api-key-here"
```

The Groq API key is required for LLM-powered receipt analysis.

### 5. Prepare Model Weights

Download the Real-ESRGAN model weights and place in the `weights/` directory:
- `realesr-general-x4v3.pth` - General upscaling model

## Usage

### Running the Flask Application

```bash
python app.py
```

The application will start on `http://localhost:5000`

### API Endpoints

#### 1. Health Check

```http
GET /health
```

**Response:**
```json
{
  "status": "ok"
}
```

#### 2. Analyze Receipt

```http
POST /analyze
Content-Type: multipart/form-data

file: <image-file>
```

**Supported file types**: PNG, JPG, JPEG, TIFF, BMP

**Response:**
```json
{
  "status": "success",
  "filename": "receipt.jpg",
  "ocr_result": {
    "rec_texts": ["text extracted from receipt"],
    "rec_scores": [0.95],
    "rec_polys": [[[x, y], ...]]
  },
  "analysis": {
    "tanggal_transaksi": "2025-11-28",
    "pihak_terlibat": "Supplier ABC",
    "deskripsi_transaksi": "Purchase of office supplies",
    "nominal_total": 150000,
    "mata_uang": "IDR",
    "tipe_transaksi": "debit",
    "items": [...],
    "rekomendasi_akun": [
      {
        "kode_akun": "511",
        "nama_akun": "Beban Perlengkapan Kantor",
        "confidence": 0.92,
        "alasan": "Daftar barang adalah perlengkapan kantor"
      },
      ...
    ]
  }
}
```

#### 3. Home

```http
GET /
```

**Response:**
```
Hello World!
```

## Core Modules

### `app.py`

Main Flask application with REST API endpoints for:
- Receipt file upload and validation
- Image preprocessing and enhancement
- OCR execution
- LLM-powered financial analysis
- File serving and response formatting

### `config/settings.py`

Configuration management:
- Output, static, and weights directory paths
- Groq API key management
- Real-ESRGAN model path configuration

### `utils/image_utils.py`

Image processing utilities:
- `rotate_bound()`: Rotate image while maintaining bounds
- `auto_orient_receipt_safe_strict()`: Automatically detect and correct receipt orientation
- `enhance_image()`: Apply enhancement filters
- `final_pipeline()`: Complete image preprocessing pipeline

### `utils/ocr_utils.py`

OCR functionality:
- `init_ocr()`: Initialize PaddleOCR with Indonesian language support
- `run_ocr()`: Execute OCR on image file
- `convert_paddleocr_to_json()`: Convert OCR results to JSON format

### `utils/llm_utils.py`

LLM-powered analysis:
- `analyze_receipt_with_llm()`: Send OCR results to Groq LLM for analysis
- Intelligent extraction of transaction details
- Recommendation of accounting codes from Chart of Accounts

### `utils/enhance_utils.py`

Image enhancement:
- Real-ESRGAN integration for 4x upscaling
- Pre/post-processing for optimal OCR results

## Workflow

```
Receipt Image
     ↓
[Auto-Orientation Detection]
     ↓
[Image Enhancement (4x Upscaling)]
     ↓
[OCR Processing (PaddleOCR)]
     ↓
[Text Extraction]
     ↓
[LLM Analysis (Groq API)]
     ↓
[Accounting Code Recommendation]
     ↓
JSON Response
```

## Configuration

### Output Directories

- `static/` - Uploaded receipt images
- `output/` - Processed results and outputs
- `weights/` - Pre-trained model weights

These directories are automatically created if they don't exist.

## Development

### Jupyter Notebook

For development and testing, use the included `koperasi.ipynb` notebook:

```bash
jupyter notebook koperasi.ipynb
```

This notebook provides an interactive environment for testing individual components.

## API Integration Example

### Python

```python
import requests

url = "http://localhost:5000/analyze"
files = {'file': open('receipt.jpg', 'rb')}

response = requests.post(url, files=files)
result = response.json()

print(result['analysis']['rekomendasi_akun'])
```

### JavaScript/Fetch

```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);

fetch('http://localhost:5000/analyze', {
  method: 'POST',
  body: formData
})
.then(response => response.json())
.then(data => console.log(data.analysis))
.catch(error => console.error('Error:', error));
```

## Supported Languages

- **OCR**: Indonesian (id), English (eng)
- **Analysis**: Indonesian (LLM prompts and responses)

## Performance Considerations

- **Model Initialization**: Models are loaded on first use (lazy loading) to reduce startup time
- **Image Upscaling**: 4x upscaling significantly improves OCR accuracy but increases processing time
- **API Rate Limiting**: Consider implementing rate limiting for production use
- **Caching**: Consider caching OCR results for identical images

## Troubleshooting

### Issue: GROQ_API_KEY not found

**Solution**: Ensure the environment variable is set:
```bash
$env:GROQ_API_KEY="your-api-key"
```

### Issue: PaddleOCR model download fails

**Solution**: Check internet connection and manually download models:
```bash
from paddleocr import PaddleOCR
ocr = PaddleOCR(lang='id')
```

### Issue: CUDA out of memory

**Solution**: Use CPU-only mode by setting environment variables or modify model loading in `ocr_utils.py`.

## License

This project is part of the Cooperative financial management suite.

## Future Enhancements

- [ ] Multi-page receipt support
- [ ] Batch processing API
- [ ] Database integration for result persistence
- [ ] Web UI for receipt upload and analysis
- [ ] Receipt template recognition
- [ ] Real-time processing status updates
- [ ] Export to common accounting formats (CSV, JSON-LD)

## Support

For issues, questions, or contributions, please contact the development team.

---

**Project Version**: 0.1.0  
**Last Updated**: November 2025
