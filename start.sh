#!/bin/sh
set -e

echo "=== Running migrations ==="
php artisan migrate --force

echo "=== Seeding roles ==="
php artisan db:seed --class=RolesAndPermissionsSeeder --force 2>/dev/null || true

echo "=== Seeding admin users ==="
php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || true

echo "=== Seeding mass schedules ==="
php artisan db:seed --class=MassScheduleSeeder --force 2>/dev/null || true

echo "=== Seeding services ==="
php artisan db:seed --class=ServiceSeeder --force 2>/dev/null || true

echo "=== Creating storage link ==="
php artisan storage:link 2>/dev/null || true

echo "=== Caching config, routes, views ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Starting PHP server on port $PORT ==="
php -S 0.0.0.0:$PORT -t public