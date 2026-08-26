#!/bin/sh
set -e

echo '=== MHC Parish System Starting ==='

PORT=${PORT:-80}
echo "Using PORT=$PORT"
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/*:80/*:$PORT/" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo '=== Migrations ==='
php artisan migrate --force

echo '=== Seeding ==='
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || true
php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || true
php artisan db:seed --class=MassScheduleSeeder --force 2>/dev/null || true
php artisan db:seed --class=ServiceSeeder --force 2>/dev/null || true

php artisan storage:link 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Starting Apache on $PORT ==="
apache2-foreground
