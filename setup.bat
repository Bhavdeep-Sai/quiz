@echo off
REM Docker Setup Script for Quiz System (Windows)
REM This script initializes the Laravel application inside Docker

echo.
echo 🚀 Starting Quiz System Docker Setup...
echo.

REM Build and start containers
echo 📦 Building Docker containers...
docker-compose up -d --build

REM Wait for MySQL to be ready
echo ⏳ Waiting for MySQL to be ready...
timeout /t 10

REM Install Laravel dependencies
echo 📥 Installing Composer dependencies...
docker-compose exec -T php composer install

REM Generate application key
echo 🔑 Generating application key...
docker-compose exec -T php php artisan key:generate

REM Run migrations
echo 🗄️ Running database migrations...
docker-compose exec -T php php artisan migrate

REM Create storage symlink
echo 🔗 Creating storage symlink...
docker-compose exec -T php php artisan storage:link

REM Set permissions
echo 🔐 Setting permissions...
docker-compose exec -T php chown -R appuser:appuser /var/www/storage
docker-compose exec -T php chown -R appuser:appuser /var/www/bootstrap/cache

echo.
echo ✅ Setup complete!
echo 🌐 Access the application at: http://localhost:8000
echo.
echo 📋 Useful commands:
echo   docker-compose logs -f php          # View PHP logs
echo   docker-compose logs -f nginx        # View Nginx logs
echo   docker-compose exec php artisan    # Run artisan commands
echo   docker-compose down                # Stop containers
echo.
pause
