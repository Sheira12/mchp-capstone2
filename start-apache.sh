#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT ─────────────────────────────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Override .env values from platform environment variables ─────────────────
sed -i "s|DATABASE_URL=.*|DATABASE_URL=|" .env 2>/dev/null || true
[ -n "$APP_KEY" ]       && sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_URL" ]       && sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$APP_ENV" ]       && sed -i "s|APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_DEBUG" ]     && sed -i "s|APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" .env
[ -n "$DB_CONNECTION" ] && sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$DB_CONNECTION|" .env
[ -n "$DB_HOST" ]       && sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" .env
[ -n "$DB_PORT" ]       && sed -i "s|DB_PORT=.*|DB_PORT=$DB_PORT|" .env
[ -n "$DB_DATABASE" ]   && sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ]   && sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ]   && sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$DB_SSLMODE" ]    && sed -i "s|DB_SSLMODE=.*|DB_SSLMODE=$DB_SSLMODE|" .env
[ -n "$PAYMONGO_SECRET_KEY" ] && sed -i "s|PAYMONGO_SECRET_KEY=.*|PAYMONGO_SECRET_KEY=$PAYMONGO_SECRET_KEY|" .env
[ -n "$MAIL_PASSWORD" ] && sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASSWORD|" .env
[ -n "$RAILWAY_PUBLIC_DOMAIN" ] && sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_PUBLIC_DOMAIN|" .env

echo "--- ENV CHECK ---"
grep "^DB_CONNECTION" .env
grep "^DB_HOST=" .env
grep "^APP_URL=" .env
echo "-----------------"

# ── Create ALL storage directories Laravel needs ─────────────────────────────
echo "=== Creating storage directories ==="
mkdir -p \
    storage/app/public \
    storage/app/private \
    storage/framework/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public/storage

# Create the hashed subdirectory structure for file cache (00-ff)
for i in $(seq 0 255); do
    hex=$(printf '%02x' $i)
    mkdir -p "storage/framework/cache/data/$hex"
done

chmod -R 775 storage bootstrap/cache public/storage || true
chown -R www-data:www-data storage bootstrap/cache public/storage || true
echo "Storage directories created."

# ── Clear stale caches ────────────────────────────────────────────────────────
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true
php artisan cache:clear 2>&1 || true

# ── Migrations ────────────────────────────────────────────────────────────────
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1
echo "Migration done."

# ── Seeders ───────────────────────────────────────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Storage link ──────────────────────────────────────────────────────────────
php artisan storage:link 2>&1 || true

echo "=== Starting Apache on port $PORT ==="
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
exec apache2-foreground
