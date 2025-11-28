# Koperasi Web

A Laravel-based web application for managing cooperative financial transactions, ledger entries, and financial reporting. The system provides intelligent account code recommendations and comprehensive financial analysis tools.

## Overview

**Koperasi Web** is a modern financial management system built with Laravel 12, designed to streamline accounting operations for cooperative organizations. It features an AI-powered account code recommender, digital ledger management, posting functionality, trial balance generation, and comprehensive financial reports.

## Key Features

- **Account Code Recommender** - AI-powered intelligent system that recommends appropriate account codes based on transaction details and receipt images using OCR and LLM technology
- **Ledger Management** - Complete digital ledger with support for debit/credit entries, transaction tracking, and receipt image attachments
- **Posting System** - Efficient posting and transaction consolidation
- **Trial Balance** - Automated trial balance generation and verification
- **Financial Reports** - Comprehensive financial reporting and analysis
- **CSV Export** - Export ledger, posting, trial balance, and financial data to CSV format
- **Multi-item Transactions** - Support for complex transactions with multiple line items
- **Receipt Management** - Digital receipt image storage and tracking with OCR text extraction

## Technology Stack

### Backend
- **Framework**: Laravel 12.0
- **Language**: PHP 8.2+
- **Database**: SQLite (default) or configurable via `.env`
- **ORM**: Eloquent

### Frontend
- **Build Tool**: Vite 7.0+
- **CSS Framework**: Tailwind CSS 4.1+
- **Templating**: Blade (Laravel)
- **Post-processing**: PostCSS with Autoprefixer

### AI & Machine Learning
- **OCR** - Optical Character Recognition for receipt and document text extraction
- **LLM** - Large Language Model integration for intelligent account code recommendations

### Development Tools
- **Testing**: PHPUnit 11.5+
- **Code Quality**: Laravel Pint
- **API Client**: Axios
- **Debugger**: Laravel Pail
- **REPL**: PsySH

## Project Structure

```
koperasi_web/
├── app/
│   ├── Http/Controllers/       # Application controllers
│   ├── Models/
│   │   ├── User.php           # User model for authentication
│   │   ├── LedgerEntry.php    # Ledger entry model
│   │   └── TransactionItem.php # Transaction items model
│   └── Providers/              # Service providers
├── database/
│   ├── migrations/             # Database schema migrations
│   ├── factories/              # Model factories for testing
│   └── seeders/               # Database seeders
├── resources/
│   ├── js/                    # JavaScript assets
│   ├── css/                   # CSS assets (Tailwind)
│   └── views/                 # Blade templates
├── routes/
│   ├── web.php               # Web application routes
│   └── console.php           # Console commands
├── storage/                   # File storage (logs, cache, sessions)
├── tests/                     # Application tests
├── vendor/                    # Composer dependencies
├── config/                    # Configuration files
└── public/                    # Web server root
```

## Database Schema

### Tables

#### `users`
- Authentication and user management
- Fields: name, email, password

#### `ledger_entries`
- Core financial transactions
- Fields: date, account_code, account_name, debit, credit, description, receipt_image_path, transaction_group_id, transaction_code
- Timestamps: created_at, updated_at

#### `transaction_items`
- Line items for transactions
- Fields: item_name, price, quantity
- Relationship: Belongs to LedgerEntry

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js and npm
- Laragon (recommended for Windows development)

### Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd koperasi_web
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Set up database**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   # or for development with hot reload
   npm run dev
   ```

7. **Start the application**
   - Using Laragon: Start via Laragon control panel
   - Using Artisan: `php artisan serve`

## Available Routes

### Account Code Recommender
**Powered by OCR and LLM Technology**

This intelligent system automates account code assignment by:
1. Extracting text from receipt images using OCR technology
2. Analyzing transaction details with Large Language Model (LLM)
3. Recommending appropriate account codes based on business logic and historical patterns
4. Allowing manual refinement and saving of recommendations

Routes:
- `GET /` - Redirect to account code recommender
- `GET /account-code-recommender/{step?}` - Account code recommendation interface
- `POST /account-code-recommender` - Submit account code recommendation
- `POST /process-image` - Process receipt image for recommendations
- `POST /save-recommendation` - Save selected recommendation

### Financial Management
- `GET /ledger` - View ledger entries
- `GET /ledger/export-csv` - Export ledger to CSV
- `GET /posting` - View posting summary
- `GET /posting/export-csv` - Export posting to CSV
- `GET /trial-balance` - View trial balance
- `GET /trial-balance/export-csv` - Export trial balance to CSV
- `GET /finance-report` - View financial reports
- `GET /finance-report/export-csv` - Export financial report to CSV

## Development

### Running Tests
```bash
php artisan test
```

### Code Quality & Formatting
```bash
./vendor/bin/pint
```

### Database Migrations
```bash
# Create new migration
php artisan make:migration create_table_name

# Run migrations
php artisan migrate

# Rollback
php artisan migrate:rollback
```

### Asset Compilation
```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

## OCR & LLM Integration

The Account Code Recommender leverages advanced AI technologies to automate account code assignment:

### OCR (Optical Character Recognition) - Metadata & Value Extraction

OCR processes receipt and document images to extract:

**Metadata Extraction:**
- **Vendor/Merchant**: Company or business name
- **Date**: Transaction date
- **Receipt Number**: Transaction reference ID
- **Payment Method**: Cash, card, transfer, etc.
- **Category Tags**: Type of transaction (supplies, utilities, etc.)

**Value Extraction:**
- **Total Amount**: Transaction total or individual line item amounts
- **Subtotal/Breakdown**: Individual item prices and quantities
- **Tax Amount**: If applicable
- **Currency**: Transaction currency
- **Line Items**: Product/service names and their costs

**Additional Details:**
- Invoice numbers and PO references
- Account or cost center codes
- Customer/vendor contact information
- Payment terms and conditions

### LLM (Large Language Model) - Intelligent Analysis & Recommendation

Once OCR extracts metadata and values, LLM performs deep analysis:

**Analysis Process:**
1. **Context Understanding** - Evaluates vendor type, product/service category, and business nature
2. **Amount Analysis** - Examines transaction value to determine expense classification
3. **Pattern Recognition** - Identifies transaction patterns from historical data
4. **Accounting Rules** - Applies accounting principles and business rules
5. **Compliance Check** - Ensures recommendations align with financial policies

**Recommendation Output:**
- Primary account code suggestion with confidence score
- Alternative account codes ranked by relevance
- Reasoning explanation for the recommendation
- Risk flags for unusual transactions
- Related transaction suggestions

### Workflow

1. **User uploads receipt image**
   - Image sent to `/process-image` endpoint
   
2. **OCR extracts metadata & values**
   - Text recognition from image
   - Structured data extraction (vendor, amount, date, items)
   - Metadata organization and validation
   
3. **LLM analyzes extracted data**
   - Processes extracted metadata and values
   - Applies business logic and accounting rules
   - Generates account code recommendations
   - Calculates confidence scores
   
4. **System presents recommendations**
   - Displays primary suggestion with reasoning
   - Shows alternative account codes
   - Highlights transaction details
   
5. **User reviews and selects**
   - Reviews OCR extracted data
   - Confirms or modifies recommendation
   - Adds additional notes if needed
   
6. **Recommendation saved**
   - Stores account code mapping
   - Saves for future pattern learning
   - Records confidence and accuracy metrics

### Key Benefits

- **Accuracy**: Reduces human error in account code assignment
- **Efficiency**: Processes receipts in seconds vs. minutes manually
- **Learning**: Improves over time from saved recommendations
- **Consistency**: Ensures uniform account coding across organization
- **Auditability**: Maintains complete audit trail of recommendations

## Key Models & Controllers

### Models
- **User** - User authentication and management
- **LedgerEntry** - Financial transaction records with OCR text storage
- **TransactionItem** - Line items within transactions

### Controllers
- **AccountCodeController** - Handles OCR processing and LLM-based account code recommendations
- **LedgerController** - Manages ledger operations
- **PostingController** - Manages posting functionality
- **TrialBalanceController** - Generates trial balance reports
- **FinanceReportController** - Generates financial reports

## Configuration

Key configuration files:
- `.env` - Environment variables (database, mail, etc.)
- `config/app.php` - Application settings
- `config/database.php` - Database configuration
- `config/auth.php` - Authentication configuration
- `vite.config.js` - Frontend build configuration

## File Storage

- **Private**: `storage/app/private/` - Secure file storage
- **Public**: `storage/app/public/` - Public accessible files
- **Logs**: `storage/logs/` - Application logs
- **Cache**: `storage/framework/cache/` - Cache storage

## Contributing

1. Create a feature branch (`git checkout -b feature/AmazingFeature`)
2. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
3. Push to the branch (`git push origin feature/AmazingFeature`)
4. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues, feature requests, or questions, please open an issue on the repository.

## Project Status

This is an active development project. The system provides core accounting and financial management functionality for cooperative organizations.

---

**Last Updated**: November 28, 2025
