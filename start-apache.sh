#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT (Railway injects $PORT) ─────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Override secrets from Railway environment variables ──────────────────────
if [ -n "$PAYMONGO_SECRET_KEY" ] && [ "$PAYMONGO_SECRET_KEY" != "RAILWAY_VAR_OVERRIDE" ]; then
    sed -i "s|PAYMONGO_SECRET_KEY=.*|PAYMONGO_SECRET_KEY=$PAYMONGO_SECRET_KEY|" .env
fi
if [ -n "$PAYMONGO_WEBHOOK_SECRET" ] && [ "$PAYMONGO_WEBHOOK_SECRET" != "RAILWAY_VAR_OVERRIDE" ]; then
    sed -i "s|PAYMONGO_WEBHOOK_SECRET=.*|PAYMONGO_WEBHOOK_SECRET=$PAYMONGO_WEBHOOK_SECRET|" .env
fi
if [ -n "$MAIL_PASSWORD" ] && [ "$MAIL_PASSWORD" != "RAILWAY_VAR_OVERRIDE" ]; then
    sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASSWORD|" .env
fi

# ── Override APP_URL with Railway-provided URL if set ────────────────────────
if [ -n "$RAILWAY_PUBLIC_DOMAIN" ]; then
    sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_PUBLIC_DOMAIN|" .env
    sed -i "s|PAYMONGO_SUCCESS_URL=.*|PAYMONGO_SUCCESS_URL=https://$RAILWAY_PUBLIC_DOMAIN/payment/success|" .env
    sed -i "s|PAYMONGO_CANCEL_URL=.*|PAYMONGO_CANCEL_URL=https://$RAILWAY_PUBLIC_DOMAIN/payment/cancel|" .env
    sed -i "s|QR_VERIFICATION_BASE_URL=.*|QR_VERIFICATION_BASE_URL=https://$RAILWAY_PUBLIC_DOMAIN/verify|" .env
    echo "APP_URL set to: https://$RAILWAY_PUBLIC_DOMAIN"
fi

# ── Storage dirs ─────────────────────────────────────────────────────────────
mkdir -p storage/logs storage/framework/cache \
         storage/framework/sessions storage/framework/views bootstrap/cache
chmod -R 775 storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache || true

# ── Clear any stale cache ─────────────────────────────────────────────────────
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true

# ── Migrations ────────────────────────────────────────────────────────────────
echo '=== Running Migrations ==='
php artisan migrate --force 2>&1 || echo 'WARNING: migrate failed (non-fatal)'

# ── Seeders ───────────────────────────────────────────────────────────────────
echo '=== Running Seeders ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>&1 || true
php artisan db:seed --class=AdminUserSeeder --force 2>&1 || true
php artisan db:seed --class=MassScheduleSeeder --force 2>&1 || true
php artisan db:seed --class=ServiceSeeder --force 2>&1 || true

# ── Cache ─────────────────────────────────────────────────────────────────────
php artisan storage:link 2>&1 || true
php artisan config:cache 2>&1 || true
php artisan route:cache 2>&1 || true
php artisan view:cache 2>&1 || true

echo "=== Starting Apache on port $PORT ==="
# Disable conflicting MPM modules, keep only mpm_event
a2dismod mpm_prefork mpm_worker 2>/dev/null || true
a2enmod mpm_event 2>/dev/null || true
exec apache2-foreground
