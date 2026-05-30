# Mary Help of Christians Parish — Management System

Web-based Administrative Transaction and Services System for **Mary Help of Christians Parish – Southville 1, Niugan, Cabuyao, Laguna**.

Built with **Laravel 10**, **Vue 3**, **Tailwind CSS**, and **PostgreSQL** (via Supabase).

---

## Features

| Module | Description |
|---|---|
| **QR Booking & Verification** | Walk-in and online booking with QR code generation and scan-to-verify |
| **Parishioner Profiling** | Full CRUD with family grouping, photo, sacramental history, and change logs |
| **Sacramental Records** | Baptism, Confirmation, Marriage, First Communion, Death/Burial |
| **Certificate Generation** | PDF certificates with embedded QR codes for authenticity verification |
| **Payment Gateway** | GCash and Maya via PayMongo, plus cash recording; webhook-driven status updates |
| **2FA Authentication** | OTP via email (and SMS via Semaphore) for parishioner accounts |
| **Admin Dashboard** | Charts, revenue trends, sacrament breakdown, and PDF/Excel report export |
| **CMS / Announcements** | Create, publish, and schedule parish announcements with image support |
| **Mass Schedule Manager** | Manage regular and special Mass schedules displayed on the public website |
| **Chatbot** | Keyword-based FAQ chatbot with staff escalation via email |
| **Audit Logging** | Every create/update/delete action is logged with user, IP, and diff |
| **Role-Based Access** | `super_admin`, `parish_secretary`, `finance_officer`, `parishioner` |
| **Booking Reminders** | Automated email reminders sent 1 day before confirmed bookings |
| **Database Backup** | Scheduled daily backup supporting both MySQL and PostgreSQL |

---

## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+ and npm
- PostgreSQL 14+ (or MySQL 8+)
- A Gmail account (or SMTP provider) for email
- [Optional] Semaphore account for SMS OTP
- [Optional] PayMongo account for GCash/Maya payments

---

## Installation

### 1. Clone and install dependencies

```bash
git clone <repository-url> "CAPSTONE 2"
cd "CAPSTONE 2"

composer install
npm install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and fill in:

```dotenv
# Database (PostgreSQL via Supabase)
DB_CONNECTION=pgsql
DB_HOST=db.xxxxxxxxxxxx.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

# Or local PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=mhc_parish
DB_USERNAME=postgres
DB_PASSWORD=

# Mail (Gmail example)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-gmail@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-gmail@gmail.com"
MAIL_FROM_NAME="MHC Parish System"

# PayMongo (get keys from dashboard.paymongo.com)
PAYMONGO_PUBLIC_KEY=pk_test_xxxxxxxxxxxx
PAYMONGO_SECRET_KEY=sk_test_xxxxxxxxxxxx
PAYMONGO_WEBHOOK_SECRET=whsec_xxxxxxxxxxxx

# Semaphore SMS (optional — get key from semaphore.co)
SEMAPHORE_API_KEY=your-semaphore-api-key
SEMAPHORE_SENDER_NAME=MHCParish

# Parish info
PARISH_NAME="Mary Help of Christians Parish"
PARISH_ADDRESS="Southville 1, Niugan, Cabuyao, Laguna"
PARISH_PHONE="+63 49 XXX XXXX"
PARISH_EMAIL="mhcparish@gmail.com"
PARISH_PRIEST="Rev. Fr. [Parish Priest Name]"
```

### 3. Run migrations and seed

```bash
php artisan migrate
php artisan db:seed
```

This creates:
- All 19 database tables
- Roles and permissions (super_admin, parish_secretary, finance_officer, parishioner)
- Default admin accounts (see credentials below)
- Mass schedules (weekday and Sunday)
- All bookable parish services

### 4. Build frontend assets

```bash
npm run build
```

For development with hot reload:
```bash
npm run dev
```

### 5. Create storage symlink

```bash
php artisan storage:link
```

### 6. Start the application

```bash
php artisan serve
```

Visit `http://localhost:8000`

---

## Default Admin Accounts

> **Change these passwords immediately after first login.**

| Role | Email | Password |
|---|---|---|
| Super Admin | admin@mhcparish.ph | Admin@1234 |
| Parish Secretary | secretary@mhcparish.ph | Secretary@1234 |
| Finance Officer | finance@mhcparish.ph | Finance@1234 |

---

## URL Structure

| URL | Description |
|---|---|
| `/` | Public parish website |
| `/services` | Parish services listing |
| `/announcements` | Public announcements |
| `/contact` | Contact form |
| `/verify/{token}` | QR code verification (public) |
| `/login` | Login page |
| `/register` | Parishioner self-registration |
| `/verify-otp` | 2FA OTP entry |
| `/portal/dashboard` | Parishioner portal |
| `/admin/dashboard` | Admin panel |

---

## Scheduled Tasks

Add this to your server's crontab to enable automated tasks:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled commands:
- `parish:send-reminders` — runs daily at 8:00 AM, sends booking reminder emails
- `parish:backup-db` — runs daily at 2:00 AM, creates a database dump in `storage/app/backups/`

---

## Queue Worker

Notifications (email) are queued. Run the queue worker:

```bash
php artisan queue:work --sleep=3 --tries=3
```

For production, use Supervisor to keep the worker running.

---

## PayMongo Webhook Setup

1. Log in to [dashboard.paymongo.com](https://dashboard.paymongo.com)
2. Go to **Developers → Webhooks**
3. Add webhook URL: `https://yourdomain.com/webhooks/paymongo`
4. Select events: `payment.paid`, `payment.failed`
5. Copy the webhook secret into `PAYMONGO_WEBHOOK_SECRET` in `.env`

---

## QR Code Verification

Every booking and certificate gets a unique QR code. Scanning it opens:

```
https://yourdomain.com/verify/{token}
```

The verification page shows the document details and confirms authenticity. The API endpoint `/api/verify/{token}` returns JSON for mobile app integration.

---

## Role Permissions Summary

| Permission | super_admin | parish_secretary | finance_officer | parishioner |
|---|:---:|:---:|:---:|:---:|
| Manage parishioners | ✓ | ✓ | — | — |
| Manage sacramental records | ✓ | ✓ | — | — |
| Manage bookings | ✓ | ✓ | — | View own |
| Issue certificates | ✓ | ✓ | — | Request |
| View payments | ✓ | ✓ | ✓ | View own |
| Record/refund payments | ✓ | — | ✓ | — |
| Manage users | ✓ | — | — | — |
| Manage announcements | ✓ | ✓ | — | — |
| View audit logs | ✓ | — | — | — |
| System settings | ✓ | — | — | — |

---

## Project Structure

```
app/
├── Console/Commands/       # DatabaseBackup, SendBookingReminders
├── Exports/                # ParishReportExport (Excel)
├── Http/Controllers/
│   ├── Admin/              # 12 admin controllers
│   ├── Auth/               # AuthController (login, 2FA, password reset)
│   └── Parishioner/        # 5 parishioner portal controllers
├── Mail/                   # TwoFactorCodeMail, InquiryMail, ChatEscalationMail
├── Models/                 # 15 Eloquent models
├── Notifications/          # BookingStatusNotification, PaymentReceiptNotification
├── Policies/               # BookingPolicy
├── Providers/              # AppServiceProvider
└── Services/               # CertificateService, PaymentService, QrCodeService,
                            # ReportService, SmsService

database/
├── migrations/             # 19 migrations
└── seeders/                # Roles, AdminUsers, MassSchedules, Services

resources/
├── css/app.css             # Tailwind + component styles
├── js/
│   ├── app.js              # Vue 3 entry point
│   ├── bootstrap.js        # Axios setup
│   └── components/         # BookingCalendar, QrScanner, PaymentForm,
│                           # ParishionerSearch
└── views/
    ├── admin/              # Admin panel views
    ├── auth/               # Login, register, 2FA, password reset
    ├── certificates/       # 9 PDF certificate templates
    ├── emails/             # Email templates
    ├── layouts/            # app.blade.php, portal.blade.php, public.blade.php
    ├── parishioner/        # Parishioner portal views
    ├── public/             # Public website pages
    └── reports/            # Report PDF template

routes/
├── web.php                 # All web routes
└── api.php                 # API routes (QR verify, calendar events)
```

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2, Laravel 10 |
| Frontend | Vue 3, Tailwind CSS 3, Vite |
| Database | PostgreSQL (Supabase) |
| Authentication | Laravel Auth + Spatie Permissions + 2FA OTP |
| PDF Generation | barryvdh/laravel-dompdf |
| QR Codes | simplesoftwareio/simple-qrcode |
| Excel Export | maatwebsite/excel |
| Payment Gateway | PayMongo (GCash, Maya) |
| SMS | Semaphore (Philippine SMS) |
| Image Processing | intervention/image |
| HTTP Client | GuzzleHTTP |

---

## Development Notes

- Admin roles (`super_admin`, `parish_secretary`, `finance_officer`) bypass 2FA and log in directly.
- Parishioner accounts always go through 2FA OTP via email.
- In `APP_DEBUG=true` mode, the OTP code is flashed to the session and shown on the 2FA page for easy testing.
- Certificate PDFs are stored in `storage/app/public/certificates/pdf/`.
- QR code SVGs are stored in `storage/app/public/qrcodes/`.
- All file uploads go to `storage/app/public/` and are served via the storage symlink.

---

## License

Proprietary — Mary Help of Christians Parish, Southville 1, Niugan, Cabuyao, Laguna.  
Developed as a Capstone Project, College of Computing Studies, AY 2026–2027.
