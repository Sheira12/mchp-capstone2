# Mary Help of Christians Parish Management System
**Southville 1, Niugan, Cabuyao, Laguna**

A comprehensive web-based administrative transaction and services system for parish operations.

## Tech Stack
- **Backend:** PHP 8.2 + Laravel 10
- **Database:** MySQL / MariaDB
- **Frontend:** Blade + Vue.js 3 + Tailwind CSS
- **PDF:** DomPDF + DOMPDF
- **QR Codes:** SimpleSoftwareIO/simple-qrcode
- **Payments:** GCash / Maya (PayMongo gateway)
- **Email:** Laravel Mail (SMTP / Mailgun)
- **Charts:** Chart.js

## Modules
1. Parishioner Profiling
2. Sacramental & Administrative Record Keeping
3. Booking Module
4. QR Code Authentication & Verification
5. Digital Certificate Generation
6. E-Wallet Payment Integration
7. Dynamic Website with Chatbot
8. Dashboard & Reporting
9. Search & Filter Functionality
10. Email Notification System
11. Input Validation & Data Integrity

## User Roles
- Super Admin
- Parish Secretary
- Finance Officer
- Parishioner (self-service)
- Public Visitor

## Setup Instructions

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8+
- Apache/Nginx with SSL

### Installation

```bash
# Clone repository
git clone <repo-url>
cd parish-system

# Install PHP dependencies
composer install

# Install JS dependencies
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure .env with your database, mail, and payment credentials

# Run migrations and seeders
php artisan migrate --seed

# Build frontend assets
npm run build

# Start server (development)
php artisan serve
```

### Default Admin Credentials
- Email: admin@mhcparish.ph
- Password: Admin@1234 (change immediately after first login)

## API Documentation
See `/docs/api.md` for full API endpoint documentation.

## License
Proprietary — Mary Help of Christians Parish
