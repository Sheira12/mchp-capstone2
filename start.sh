#!/bin/sh
set -e

echo "=== Starting MHC Parish System ==="
echo "PORT=${PORT:-8080}"

# Create storage directories if missing
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views

# Set permissions
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "=== Running database migrations ==="
php artisan migrate --force

echo "=== Seeding initial data ==="
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || echo "Roles already exist"
php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || echo "Admin already exists"
php artisan db:seed --class=MassScheduleSeeder --force 2>/dev/null || echo "Schedules already exist"
php artisan db:seed --class=ServiceSeeder --force 2>/dev/null || echo "Services already exist"

echo "=== Creating storage symlink ==="
php artisan storage:link 2>/dev/null || true

echo "=== Caching configuration ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Starting server on port ${PORT:-8080} ==="
php -S 0.0.0.0:${PORT:-8080} -t public