#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT ─────────────────────────────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Storage dirs ─────────────────────────────────────────────────────────────
mkdir -p storage/logs storage/framework/cache \
         storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# ── Write .env from Railway environment variables ────────────────────────────
# Must happen BEFORE any artisan command
echo "APP_NAME=\"${APP_NAME:-MHC Parish System}\"" > .env
echo "APP_ENV=${APP_ENV:-production}" >> .env
echo "APP_KEY=${APP_KEY}" >> .env
echo "APP_DEBUG=${APP_DEBUG:-false}" >> .env
echo "APP_URL=${APP_URL:-http://localhost}" >> .env
echo "APP_TIMEZONE=${APP_TIMEZONE:-Asia/Manila}" >> .env
echo "LOG_CHANNEL=${LOG_CHANNEL:-stderr}" >> .env
echo "LOG_LEVEL=${LOG_LEVEL:-error}" >> .env
echo "DB_CONNECTION=${DB_CONNECTION:-pgsql}" >> .env
echo "DB_HOST=${DB_HOST}" >> .env
echo "DB_PORT=${DB_PORT:-5432}" >> .env
echo "DB_DATABASE=${DB_DATABASE:-postgres}" >> .env
echo "DB_USERNAME=${DB_USERNAME:-postgres}" >> .env
echo "DB_PASSWORD=${DB_PASSWORD}" >> .env
echo "BROADCAST_DRIVER=${BROADCAST_DRIVER:-log}" >> .env
echo "CACHE_DRIVER=${CACHE_DRIVER:-file}" >> .env
echo "FILESYSTEM_DISK=${FILESYSTEM_DISK:-local}" >> .env
echo "QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}" >> .env
echo "SESSION_DRIVER=${SESSION_DRIVER:-cookie}" >> .env
echo "SESSION_LIFETIME=${SESSION_LIFETIME:-120}" >> .env
echo "SESSION_SECURE_COOKIE=${SESSION_SECURE_COOKIE:-true}" >> .env
echo "MAIL_MAILER=${MAIL_MAILER:-smtp}" >> .env
echo "MAIL_HOST=${MAIL_HOST:-smtp.gmail.com}" >> .env
echo "MAIL_PORT=${MAIL_PORT:-587}" >> .env
echo "MAIL_USERNAME=${MAIL_USERNAME}" >> .env
echo "MAIL_PASSWORD=${MAIL_PASSWORD}" >> .env
echo "MAIL_ENCRYPTION=${MAIL_ENCRYPTION:-tls}" >> .env
echo "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}" >> .env
echo "MAIL_FROM_NAME=\"${MAIL_FROM_NAME:-MHC Parish System}\"" >> .env
echo "PAYMONGO_PUBLIC_KEY=${PAYMONGO_PUBLIC_KEY}" >> .env
echo "PAYMONGO_SECRET_KEY=${PAYMONGO_SECRET_KEY}" >> .env
echo "PAYMONGO_WEBHOOK_SECRET=${PAYMONGO_WEBHOOK_SECRET}" >> .env
echo "PAYMONGO_SUCCESS_URL=${PAYMONGO_SUCCESS_URL}" >> .env
echo "PAYMONGO_CANCEL_URL=${PAYMONGO_CANCEL_URL}" >> .env
echo "GCASH_NUMBER=\"${GCASH_NUMBER}\"" >> .env
echo "GCASH_NAME=\"${GCASH_NAME}\"" >> .env
echo "MAYA_NUMBER=\"${MAYA_NUMBER}\"" >> .env
echo "MAYA_NAME=\"${MAYA_NAME}\"" >> .env
echo "PARISH_NAME=\"${PARISH_NAME}\"" >> .env
echo "PARISH_ADDRESS=\"${PARISH_ADDRESS}\"" >> .env
echo "PARISH_PHONE=\"${PARISH_PHONE}\"" >> .env
echo "PARISH_EMAIL=\"${PARISH_EMAIL}\"" >> .env
echo "PARISH_PRIEST=\"${PARISH_PRIEST}\"" >> .env
echo "QR_VERIFICATION_BASE_URL=\"${QR_VERIFICATION_BASE_URL}\"" >> .env

echo '--- .env written ---'
echo "APP_KEY present: $(grep -c 'APP_KEY=base64' .env || echo 0)"
echo "DB_CONNECTION: $(grep DB_CONNECTION .env)"
echo "DB_HOST: $(grep DB_HOST= .env)"

# ── Clear stale config cache (was built with wrong DB settings) ───────────────
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true

# ── Package discovery ─────────────────────────────────────────────────────────
echo '=== Discovering Packages ==='
php artisan package:discover --ansi 2>&1 || true

# ── Migrations ────────────────────────────────────────────────────────────────
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1 || echo 'WARNING: migrate failed (non-fatal)'

# ── Seeders ───────────────────────────────────────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Cache (after .env is correct) ─────────────────────────────────────────────
php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

echo "=== Starting Apache on port $PORT ==="
exec apache2-foreground
