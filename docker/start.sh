#!/bin/sh
set -e

cd /var/www/html

echo "=== Running database migrations ==="
php artisan migrate --force

echo "=== Seeding demo data ==="
php artisan db:seed --force

echo "=== Starting Laravel server ==="
exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}