#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT setup ───────────────────────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Storage dirs ─────────────────────────────────────────────────────────────
mkdir -p storage/logs \
         storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# ── Write .env from Railway environment variables ───────────────────────────
# Laravel reads .env file; Railway injects env vars but no .env file exists
cat > .env <<EOF
APP_NAME="${APP_NAME:-MHC Parish System}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
APP_TIMEZONE=${APP_TIMEZONE:-Asia/Manila}

LOG_CHANNEL=${LOG_CHANNEL:-stderr}
LOG_LEVEL=${LOG_LEVEL:-error}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE:-postgres}
DB_USERNAME=${DB_USERNAME:-postgres}
DB_PASSWORD=${DB_PASSWORD}

BROADCAST_DRIVER=${BROADCAST_DRIVER:-log}
CACHE_DRIVER=${CACHE_DRIVER:-file}
FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
SESSION_DRIVER=${SESSION_DRIVER:-cookie}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}

MAIL_MAILER=${MAIL_MAILER:-smtp}
MAIL_HOST=${MAIL_HOST:-smtp.gmail.com}
MAIL_PORT=${MAIL_PORT:-587}
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}
MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}
MAIL_FROM_NAME="${MAIL_FROM_NAME:-MHC Parish System}"

PAYMONGO_PUBLIC_KEY=${PAYMONGO_PUBLIC_KEY}
PAYMONGO_SECRET_KEY=${PAYMONGO_SECRET_KEY}
PAYMONGO_WEBHOOK_SECRET=${PAYMONGO_WEBHOOK_SECRET}
PAYMONGO_SUCCESS_URL=${PAYMONGO_SUCCESS_URL}
PAYMONGO_CANCEL_URL=${PAYMONGO_CANCEL_URL}

GCASH_NUMBER="${GCASH_NUMBER}"
GCASH_NAME="${GCASH_NAME}"
MAYA_NUMBER="${MAYA_NUMBER}"
MAYA_NAME="${MAYA_NAME}"

PARISH_NAME="${PARISH_NAME}"
PARISH_ADDRESS="${PARISH_ADDRESS}"
PARISH_PHONE="${PARISH_PHONE}"
PARISH_EMAIL="${PARISH_EMAIL}"
PARISH_PRIEST="${PARISH_PRIEST}"

QR_VERIFICATION_BASE_URL="${QR_VERIFICATION_BASE_URL}"
EOF

echo '=== .env written ==='
echo "APP_KEY set: $([ -n "$APP_KEY" ] && echo YES || echo NO)"
echo "DB_HOST set: $([ -n "$DB_HOST" ] && echo YES || echo NO)"

# ── Package discovery ─────────────────────────────────────────────────────────
echo '=== Discovering Packages ==='
php artisan package:discover --ansi 2>&1 || echo "package:discover failed (non-fatal)"

# ── Migrations ────────────────────────────────────────────────────────────────
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed (non-fatal)"

# ── Seeders ───────────────────────────────────────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Cache ─────────────────────────────────────────────────────────────────────
php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || echo "config:cache failed (non-fatal)"
php artisan route:cache 2>&1 || echo "route:cache failed (non-fatal)"
php artisan view:cache 2>&1 || echo "view:cache failed (non-fatal)"

echo "=== Starting Apache on port $PORT ==="
exec apache2-foreground
