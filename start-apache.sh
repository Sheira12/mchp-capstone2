#!/bin/sh

echo '=== MHC Parish System Starting ==='

# ── PORT ─────────────────────────────────────────────────────────────────────
PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/g" /etc/apache2/ports.conf || true
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf || true

# ── Override .env values from Render environment variables ───────────────────
# These are the only values that differ between the baked .env.railway and
# what Render injects at runtime (secrets, real API keys, etc.)
sed -i "s|DATABASE_URL=.*|DATABASE_URL=|" .env 2>/dev/null || true
[ -n "$APP_KEY" ]             && sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
[ -n "$APP_URL" ]             && sed -i "s|APP_URL=.*|APP_URL=$APP_URL|" .env
[ -n "$APP_ENV" ]             && sed -i "s|APP_ENV=.*|APP_ENV=$APP_ENV|" .env
[ -n "$APP_DEBUG" ]           && sed -i "s|APP_DEBUG=.*|APP_DEBUG=$APP_DEBUG|" .env
[ -n "$DB_CONNECTION" ]       && sed -i "s|DB_CONNECTION=.*|DB_CONNECTION=$DB_CONNECTION|" .env
[ -n "$DB_HOST" ]             && sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" .env
[ -n "$DB_PORT" ]             && sed -i "s|DB_PORT=.*|DB_PORT=$DB_PORT|" .env
[ -n "$DB_DATABASE" ]         && sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_DATABASE|" .env
[ -n "$DB_USERNAME" ]         && sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USERNAME|" .env
[ -n "$DB_PASSWORD" ]         && sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
[ -n "$DB_SSLMODE" ]          && sed -i "s|DB_SSLMODE=.*|DB_SSLMODE=$DB_SSLMODE|" .env
[ -n "$DB_CONNECT_TIMEOUT" ] && (grep -q "DB_CONNECT_TIMEOUT=" .env && sed -i "s|DB_CONNECT_TIMEOUT=.*|DB_CONNECT_TIMEOUT=$DB_CONNECT_TIMEOUT|" .env || echo "DB_CONNECT_TIMEOUT=$DB_CONNECT_TIMEOUT" >> .env)
[ -n "$DB_PERSISTENT" ]      && (grep -q "DB_PERSISTENT=" .env && sed -i "s|DB_PERSISTENT=.*|DB_PERSISTENT=$DB_PERSISTENT|" .env || echo "DB_PERSISTENT=$DB_PERSISTENT" >> .env)
[ -n "$MAIL_MAILER" ]         && sed -i "s|MAIL_MAILER=.*|MAIL_MAILER=$MAIL_MAILER|" .env
[ -n "$MAIL_HOST" ]           && sed -i "s|MAIL_HOST=.*|MAIL_HOST=$MAIL_HOST|" .env
[ -n "$MAIL_PORT" ]           && sed -i "s|MAIL_PORT=.*|MAIL_PORT=$MAIL_PORT|" .env
[ -n "$MAIL_USERNAME" ]       && sed -i "s|MAIL_USERNAME=.*|MAIL_USERNAME=$MAIL_USERNAME|" .env
[ -n "$MAIL_PASSWORD" ]       && sed -i "s|MAIL_PASSWORD=.*|MAIL_PASSWORD=$MAIL_PASSWORD|" .env
[ -n "$MAIL_ENCRYPTION" ]     && sed -i "s|MAIL_ENCRYPTION=.*|MAIL_ENCRYPTION=$MAIL_ENCRYPTION|" .env
[ -n "$MAIL_FROM_ADDRESS" ]   && sed -i "s|MAIL_FROM_ADDRESS=.*|MAIL_FROM_ADDRESS=$MAIL_FROM_ADDRESS|" .env
[ -n "$RESEND_API_KEY" ]      && sed -i "s|RESEND_API_KEY=.*|RESEND_API_KEY=$RESEND_API_KEY|" .env
[ -n "$BREVO_API_KEY" ]       && (grep -q "BREVO_API_KEY=" .env && sed -i "s|BREVO_API_KEY=.*|BREVO_API_KEY=$BREVO_API_KEY|" .env || echo "BREVO_API_KEY=$BREVO_API_KEY" >> .env)
[ -n "$PAYMONGO_SECRET_KEY" ] && sed -i "s|PAYMONGO_SECRET_KEY=.*|PAYMONGO_SECRET_KEY=$PAYMONGO_SECRET_KEY|" .env
[ -n "$PAYMONGO_PUBLIC_KEY" ] && sed -i "s|PAYMONGO_PUBLIC_KEY=.*|PAYMONGO_PUBLIC_KEY=$PAYMONGO_PUBLIC_KEY|" .env
[ -n "$PAYMONGO_WEBHOOK_SECRET" ] && (grep -q "PAYMONGO_WEBHOOK_SECRET=" .env && sed -i "s|PAYMONGO_WEBHOOK_SECRET=.*|PAYMONGO_WEBHOOK_SECRET=$PAYMONGO_WEBHOOK_SECRET|" .env || echo "PAYMONGO_WEBHOOK_SECRET=$PAYMONGO_WEBHOOK_SECRET" >> .env)
[ -n "$RAILWAY_PUBLIC_DOMAIN" ] && sed -i "s|APP_URL=.*|APP_URL=https://$RAILWAY_PUBLIC_DOMAIN|" .env
[ -n "$APP_URL" ] && sed -i "s|QR_VERIFICATION_BASE_URL=.*|QR_VERIFICATION_BASE_URL=$APP_URL/verify|" .env
[ -n "$RAILWAY_PUBLIC_DOMAIN" ] && sed -i "s|QR_VERIFICATION_BASE_URL=.*|QR_VERIFICATION_BASE_URL=https://$RAILWAY_PUBLIC_DOMAIN/verify|" .env

echo "--- ENV CHECK ---"
grep "^DB_CONNECTION" .env
grep "^DB_HOST=" .env
grep "^APP_URL=" .env
grep "^APP_DEBUG=" .env
echo "PAYMONGO_SECRET_KEY length: $(grep '^PAYMONGO_SECRET_KEY=' .env | sed 's/PAYMONGO_SECRET_KEY=//' | wc -c)"
echo "-----------------"

# ── Remove any invalid .env lines (keys with spaces) ─────────────────────────
sed -i '/^[A-Z_][A-Z0-9_]* [A-Z0-9_].*=/d' .env 2>/dev/null || true
sed -i '/^[^A-Z_#]/d' .env 2>/dev/null || true
echo ".env sanitized."

# ── Storage directories ───────────────────────────────────────────────────────
# Must run on every start because Render's filesystem is ephemeral —
# the container's writable layer is reset between deployments.
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
    bootstrap/cache

# Create hashed cache subdirectory structure (file cache driver)
for i in $(seq 0 255); do
    hex=$(printf '%02x' $i)
    mkdir -p "storage/framework/cache/data/$hex"
done

chmod -R 775 storage bootstrap/cache public/storage || true
chown -R www-data:www-data storage bootstrap/cache public/storage || true
echo "Storage directories ready."

# ── Database migrations ───────────────────────────────────────────────────────
# Run migrations on EVERY start so schema stays current after a new deployment.
# Seeders are NOT run here — they are run once manually or in the build phase.
echo "=== Running Migrations ==="
php artisan migrate --force 2>&1
echo "Migration done."

# ── Rebuild Laravel production caches ────────────────────────────────────────
# These are rebuilt AFTER .env has been patched with real Render secrets,
# so the cached config reflects the actual runtime values.
# This replaces the old "config:clear / route:clear / view:clear" block which
# was leaving the app uncached and forcing Laravel to re-read every file on
# every request.
echo "=== Building Laravel caches ==="
php artisan config:cache 2>&1
php artisan route:cache  2>&1
php artisan view:cache   2>&1
php artisan event:cache  2>&1
echo "Laravel caches built."

# ── Storage symlink ───────────────────────────────────────────────────────────
rm -rf /var/www/html/public/storage 2>/dev/null || true
ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
echo "Storage symlink: public/storage -> storage/app/public"
ls -la /var/www/html/public/storage 2>/dev/null || echo "WARNING: symlink creation failed"

# ── Final permission fix ──────────────────────────────────────────────────────
# Artisan cache commands above may create files as root.
# Reset to www-data so Apache/PHP can write on subsequent requests.
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
echo "Permissions set."

# ── NOTE: Seeders removed from startup ───────────────────────────────────────
# RolesAndPermissionsSeeder, AdminUserSeeder, MassScheduleSeeder, ServiceSeeder
# and parish:fix-qrcodes were previously run on every startup.
# This added 7-15 seconds to every cold start (each hits the remote Supabase DB).
# They are now run once during the Docker build phase in Dockerfile.
# If you need to re-run a seeder manually, use: php artisan db:seed --class=X

echo "=== Starting Apache on port $PORT ==="
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
exec apache2-foreground
