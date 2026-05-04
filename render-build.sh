#!/bin/bash
set -e

echo "=== Installing Composer Dependencies ==="
composer install --no-dev --prefer-dist --no-interaction

echo "=== Running Database Migrations ==="
php artisan migrate --force

echo "=== Caching Configuration ==="
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Creating Storage Symlink ==="
php artisan storage:link || true

echo "=== Clearing Caches ==="
php artisan cache:clear
php artisan view:clear

echo "=== Build Complete! ==="