# Setup & Deployment Guide

## System Requirements
- PHP 8.2+
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ or MariaDB 10.6+
- Apache 2.4+ or Nginx 1.20+
- SSL Certificate (required for production)

## Local Development Setup

### 1. Clone and Install
```bash
git clone <repository-url> parish-system
cd parish-system
composer install
npm install
```

### 2. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your settings:
- Database credentials
- Mail settings (SMTP)
- PayMongo API keys
- Parish information

### 3. Database Setup
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE mhc_parish CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations and seed
php artisan migrate --seed
```

### 4. Storage Setup
```bash
php artisan storage:link
mkdir -p storage/app/public/{parishioners/photos,certificates/pdf,certificates/qr,qrcodes,announcements,backups}
```

### 5. Build Assets
```bash
npm run build
# or for development:
npm run dev
```

### 6. Start Development Server
```bash
php artisan serve
```

Visit: http://localhost:8000

---

## Production Deployment

### Apache Configuration
```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/www/parish-system/public

    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem

    <Directory /var/www/parish-system/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/parish-error.log
    CustomLog ${APACHE_LOG_DIR}/parish-access.log combined
</VirtualHost>
```

### Nginx Configuration
```nginx
server {
    listen 443 ssl;
    server_name your-domain.com;
    root /var/www/parish-system/public;

    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Production Commands
```bash
# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Queue Worker (Supervisor)
```ini
[program:parish-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/parish-system/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/parish-queue.log
```

### Cron Job (Scheduled Tasks)
```cron
* * * * * www-data cd /var/www/parish-system && php artisan schedule:run >> /dev/null 2>&1
```

---

## PayMongo Integration

1. Create a PayMongo account at https://paymongo.com
2. Get your API keys from the dashboard
3. Set up webhook endpoint: `https://your-domain.com/webhooks/paymongo`
4. Subscribe to events: `payment.paid`, `payment.failed`
5. Copy the webhook secret to `.env`

---

## Email Configuration

### Gmail (Development)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
```

### Mailgun (Production)
```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.your-domain.com
MAILGUN_SECRET=your-mailgun-key
```

---

## Backup Procedures

### Automated Daily Backup
The system runs `php artisan parish:backup-db` daily at 2:00 AM.
Backups are stored in `storage/app/backups/`.

### Manual Backup
```bash
php artisan parish:backup-db
```

### Restore from Backup
```bash
mysql -u root -p mhc_parish < storage/app/backups/db-YYYY-MM-DD-HHMMSS.sql
```

---

## Default Credentials
After seeding, use these to log in (change immediately!):

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@mhcparish.ph | Admin@1234 |
| Parish Secretary | secretary@mhcparish.ph | Secretary@1234 |
| Finance Officer | finance@mhcparish.ph | Finance@1234 |
