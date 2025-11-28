# AI-Cooperative: Intelligent Financial Transaction Management System

An integrated system for managing cooperative financial transactions with AI-powered account code recommendations. This project combines Python-based OCR and LLM analysis with a Laravel web application for comprehensive financial management.

## 🎯 Project Overview

**AI-Cooperative** is a complete financial management solution designed for cooperative organizations. It automates receipt analysis through OCR technology, leverages AI to recommend appropriate accounting codes, and provides a web-based interface for managing ledgers, postings, and financial reports.

The system processes financial documents (receipts, invoices, payment slips) by extracting text through OCR, analyzing content with LLM (via Groq API), and recommending appropriate Chart of Accounts (COA) codes.

## 📁 Project Structure

```
koperasi/
├── ai_services/                    # Python Flask backend for receipt analysis
│   ├── app.py                      # Flask application with REST API endpoints
│   ├── main.py                     # Entry point script
│   ├── koperasi.ipynb              # Jupyter notebook for development/testing
│   ├── requirements.txt            # Python dependencies
│   ├── pyproject.toml              # Project configuration
│   ├── config/
│   │   └── settings.py             # Configuration settings and constants
│   ├── utils/
│   │   ├── image_utils.py          # Image processing and enhancement
│   │   ├── ocr_utils.py            # OCR extraction using PaddleOCR
│   │   ├── llm_utils.py            # LLM analysis via Groq API
│   │   └── enhance_utils.py        # Image enhancement with Real-ESRGAN
│   ├── static/                     # Receipt image uploads directory
│   ├── output/                     # Processed results directory
│   ├── weights/                    # Pre-trained model weights
│   └── README.md                   # AI Services documentation
│
└── koperasi_web/                   # Laravel web application
    ├── app/                        # Laravel application code
    │   ├── Http/
    │   │   └── Controllers/        # API controllers
    │   ├── Models/
    │   │   ├── User.php
    │   │   ├── LedgerEntry.php
    │   │   └── TransactionItem.php
    │   └── Providers/
    ├── config/                     # Configuration files
    ├── database/
    │   ├── migrations/             # Database schema migrations
    │   ├── factories/              # Factory classes for testing
    │   └── seeders/                # Database seeders
    ├── resources/
    │   ├── views/                  # Blade templates
    │   ├── js/                     # JavaScript files
    │   └── css/                    # Stylesheets
    ├── routes/                     # Web and API routes
    ├── tests/                      # Unit and feature tests
    ├── public/                     # Publicly accessible files
    ├── storage/                    # Application storage
    ├── bootstrap/                  # Bootstrap files
    ├── vendor/                     # Composer dependencies
    ├── composer.json               # PHP dependencies
    ├── package.json                # Node.js dependencies
    ├── vite.config.js              # Vite configuration
    ├── phpunit.xml                 # PHPUnit configuration
    └── README.md                   # Web application documentation
```

## 🚀 Key Features

### AI Services (Python Backend)
- **REST API Endpoints**: Health checks and receipt analysis endpoints
- **Multi-format Image Support**: PNG, JPG, JPEG, TIFF, BMP
- **Image Enhancement**: Real-ESRGAN 4x upscaling for improved OCR accuracy
- **OCR Processing**: PaddleOCR with Indonesian language support
- **AI Analysis**: LLM-powered transaction classification and account code recommendations
- **CORS Support**: Cross-origin resource sharing for frontend integration
- **Efficient Model Loading**: Lazy initialization of heavy models

### Web Application (Laravel)
- **Account Code Recommender**: AI-powered intelligent account code suggestions
- **Ledger Management**: Digital ledger with debit/credit entries
- **Posting System**: Transaction consolidation and posting
- **Trial Balance**: Automated generation and verification
- **Financial Reports**: Comprehensive financial analysis tools
- **CSV Export**: Export data in multiple formats
- **Multi-item Transactions**: Support for complex transaction structures
- **Receipt Management**: Digital storage and OCR text extraction

## 🛠 Technology Stack

### Backend (AI Services)
- **Framework**: Flask 3.1.2+
- **Language**: Python 3.11+
- **OCR**: PaddleOCR 3.3.2+
- **LLM**: Groq API
- **Image Processing**: OpenCV, Real-ESRGAN, PIL
- **Deep Learning**: PyTorch 2.9.1+, BasicSR

### Web Application
- **Framework**: Laravel 12.0+
- **Language**: PHP 8.2+
- **Frontend Build**: Vite 7.0+
- **Styling**: Tailwind CSS 4.1+
- **Database**: SQLite (default) or configurable
- **ORM**: Eloquent

## 📋 Prerequisites

### AI Services
- Python 3.11 or higher
- pip package manager
- CUDA-capable GPU (optional, for faster processing)
- Groq API key for LLM access

### Web Application
- PHP 8.2 or higher
- Composer package manager
- Node.js and npm (for frontend build tools)
- SQLite or MySQL

## 🔧 Installation & Setup

### AI Services Setup

1. **Navigate to the AI services directory**:
   ```powershell
   cd ai_services
   ```

2. **Create a virtual environment** (recommended):
   ```powershell
   python -m venv venv
   .\venv\Scripts\Activate.ps1
   ```

3. **Install dependencies**:
   > **Note for Windows users**: Install paddlepaddle first using the Windows wheel
   ```powershell
   pip install paddlepaddle -f https://www.paddlepaddle.org.cn/whl/windows/mkl/avx/stable.html
   pip install -r requirements.txt
   ```

4. **Configure settings**:
   - Edit `config/settings.py` and set your Groq API key
   - Adjust paths and model parameters as needed

5. **Start the Flask server**:
   ```powershell
   python app.py
   ```
   The API will be available at `http://localhost:5000`

### Web Application Setup

1. **Navigate to the web application directory**:
   ```powershell
   cd koperasi_web
   ```

2. **Install PHP dependencies**:
   ```powershell
   composer install
   ```

3. **Install Node.js dependencies**:
   ```powershell
   npm install
   ```

4. **Configure environment**:
   ```powershell
   copy .env.example .env
   ```

5. **Generate application key**:
   ```powershell
   php artisan key:generate
   ```

6. **Run database migrations**:
   ```powershell
   php artisan migrate
   ```

7. **Build frontend assets**:
   ```powershell
   npm run build
   ```

8. **Start the development server**:
   ```powershell
   php artisan serve
   ```
   The web application will be available at `http://localhost:8000`

## 📡 API Documentation

### AI Services API

#### Health Check
```
GET /health
Response: { "status": "ok" }
```

#### Receipt Analysis
```
POST /analyze
Content-Type: multipart/form-data

Parameters:
  file: Receipt image file (PNG, JPG, JPEG, TIFF, BMP)

Response: {
  "ocr": {
    "full_text": "...",
    "lines": [...]
  },
  "llm": {
    "account_code": "...",
    "confidence": 0.95,
    "reasoning": "..."
  }
}
```

## 🔐 Configuration

### AI Services (`config/settings.py`)
- `GROQ_API_KEY`: Your Groq API key
- `UPLOAD_DIR`: Directory for receipt uploads
- `OUTPUT_DIR`: Directory for processed outputs
- `MODEL_NAME`: LLM model identifier
- `OCR_LANG`: Language for OCR (default: 'id' for Indonesian)

### Web Application (`.env`)
- `APP_URL`: Application URL
- `DB_CONNECTION`: Database type (sqlite, mysql, etc.)
- `DB_DATABASE`: Database name/path
- `GROQ_API_KEY`: Groq API key for backend communication

## 📊 Usage Examples

### Processing a Receipt via API
```powershell
$file = Get-Item "path/to/receipt.jpg"
$form = @{
    file = $file
}
Invoke-RestMethod -Uri "http://localhost:5000/analyze" -Method POST -Form $form
```

### Accessing the Web Dashboard
1. Navigate to `http://localhost:8000`
2. Create or import a receipt
3. View AI-recommended account codes
4. Manage ledger entries and generate reports

## 🧪 Testing

### Web Application
```powershell
cd koperasi_web
php artisan test
```

### AI Services
```powershell
cd ai_services
python -m pytest
```

## 📝 Database Schema

### LedgerEntry
- id
- user_id
- transaction_code
- account_code
- debit/credit amount
- description
- receipt_image_path
- timestamp

### TransactionItem
- id
- ledger_entry_id
- item_description
- quantity
- unit_price
- amount

### User
- id
- name
- email
- password
- created_at/updated_at

## 🤝 Integration

The two components communicate through:
- **REST API**: Web application calls the Flask API for receipt analysis
- **Shared Database**: Both systems can access transaction data
- **File Storage**: Receipt images are stored in shared storage

## 📦 Model Weights

The AI services require pre-trained model weights:
- `realesr-general-x4v3.pth`: Real-ESRGAN model for image upscaling (4x)

Download and place in `ai_services/weights/` directory or configure auto-download in `enhance_utils.py`.

## 🚨 Troubleshooting

### AI Services Issues

**PaddleOCR Model Download Issues**:
- Models are downloaded on first use; ensure internet connection
- Models are cached in `~/.paddleocr/` directory

**GPU Memory Issues**:
- Set `use_gpu=False` in `ocr_utils.py` if GPU memory is insufficient
- Reduce image size before processing

**Groq API Errors**:
- Verify API key is correct in `config/settings.py`
- Check API rate limits and quota

### Web Application Issues

**Database Lock**:
- If using SQLite, ensure file permissions are correct
- Close any other connections to the database

**Vite Build Issues**:
- Clear `node_modules` and reinstall: `rm -r node_modules; npm install`
- Clear Vite cache: `npm run dev -- --force`

## 📚 Documentation

- **AI Services**: See `ai_services/README.md` for detailed backend documentation
- **Web Application**: See `koperasi_web/README.md` for detailed frontend documentation

## 🎓 Development

### Adding New Features

1. **Backend Features**: Add endpoints in `ai_services/app.py` and utility functions
2. **Frontend Features**: Create Blade views and controllers in `koperasi_web/`
3. **Database Changes**: Create migrations in `database/migrations/`

### Code Structure

- **Models**: Core data structures
- **Controllers**: Request handling and business logic
- **Routes**: Endpoint definitions
- **Migrations**: Database schema management
- **Utils**: Reusable utility functions

## 📄 License

This project is licensed under the MIT License. See LICENSE file for details.

## 👥 Support

For issues, questions, or contributions:
1. Check the troubleshooting section
2. Review component-specific READMEs
3. Open an issue on the repository

## 🔄 Version History

- **v0.1.0** (Current): Initial release with OCR, LLM analysis, and web management

---

**Last Updated**: November 28, 2025
