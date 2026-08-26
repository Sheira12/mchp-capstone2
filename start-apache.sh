#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT setup (Railway injects $PORT) ──────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/*:80/*:$PORT/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# ── Storage directories ──────────────────────────────────────────────────────
mkdir -p storage/logs \
         storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

# ── Check APP_KEY ────────────────────────────────────────────────────────────
if [ -z "$APP_KEY" ]; then
    echo "WARNING: APP_KEY is not set — generating one now"
    php artisan key:generate --force 2>/dev/null || true
fi

# ── Rebuild package/service provider cache from installed packages ───────────
# (bootstrap/cache/*.php is not committed — must be regenerated at runtime)
echo '=== Discovering Packages ==='
php artisan package:discover --ansi 2>&1 || echo "WARNING: package:discover failed"

# ── Database migrations (non-fatal — container must start even if DB is slow) ─
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1 || echo "WARNING: Migration failed — app will still start"

# ── Seeders (non-fatal) ───────────────────────────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Laravel cache & links ─────────────────────────────────────────────────────
php artisan storage:link 2>/dev/null || true
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache 2>/dev/null || true
php artisan route:cache 2>/dev/null || true
php artisan view:cache 2>/dev/null || true

echo "=== Starting Apache on port $PORT ==="
exec apache2-foreground
