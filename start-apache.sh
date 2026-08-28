#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT ─────────────────────────────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Override .env values from platform environment variables ─────────────────
# Explicitly blank out DATABASE_URL so Laravel uses individual DB_* vars
sed -i "s|DATABASE_URL=.*|DATABASE_URL=|" .env 2>/dev/null || true
# This handles both Render and Railway injected env vars
[ -n "$APP_KEY" ]         && sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_URL" ]         && sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$APP_ENV" ]         && sed -i "s|APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_DEBUG" ]       && sed -i "s|APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" .env
[ -n "$DB_CONNECTION" ]   && sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$DB_CONNECTION|" .env
[ -n "$DB_HOST" ]         && sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" .env
[ -n "$DB_PORT" ]         && sed -i "s|DB_PORT=.*|DB_PORT=$DB_PORT|" .env
[ -n "$DB_DATABASE" ]     && sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ]     && sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ]     && sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$DB_SSLMODE" ]      && sed -i "s|DB_SSLMODE=.*|DB_SSLMODE=$DB_SSLMODE|" .env
[ -n "$PAYMONGO_SECRET_KEY" ] && sed -i "s|PAYMONGO_SECRET_KEY=.*|PAYMONGO_SECRET_KEY=$PAYMONGO_SECRET_KEY|" .env
[ -n "$MAIL_PASSWORD" ]   && sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASSWORD|" .env
[ -n "$RAILWAY_PUBLIC_DOMAIN" ] && sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_PUBLIC_DOMAIN|" .env

# ── Show what DB we are connecting to ────────────────────────────────────────
echo "--- DB CONFIG ---"
grep "^DB_CONNECTION" .env || echo "DB_CONNECTION not found in .env"
grep "^DB_HOST" .env       || echo "DB_HOST not found in .env"
grep "^DB_USERNAME" .env   || echo "DB_USERNAME not found in .env"
echo "-----------------"

# ── Storage dirs ─────────────────────────────────────────────────────────────
mkdir -p storage/logs \
         storage/framework/cache \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         bootstrap/cache \
         public/storage
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# ── Clear ALL caches (never use stale config) ─────────────────────────────────
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true
php artisan cache:clear 2>&1 || true
# Remove any compiled blade files that may be stale
rm -rf storage/framework/views/*.php 2>/dev/null || true

# ── Migrations ────────────────────────────────────────────────────────────────
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1
MIGRATE_EXIT=$?
echo "Migration exit code: $MIGRATE_EXIT"
if [ $MIGRATE_EXIT -ne 0 ]; then
    echo "ERROR: Migrations failed! DB connection details:"
    grep "^DB_" .env
fi

# ── Seeders (only run if migrations succeeded) ────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Storage link (no config:cache — let Laravel read .env fresh) ──────────────
php artisan storage:link 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

echo "=== Starting Apache on port $PORT ==="
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
exec apache2-foreground
