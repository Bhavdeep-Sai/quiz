#!/bin/bash

# Docker Setup Script for Quiz System
# This script initializes the Laravel application inside Docker

echo "🚀 Starting Quiz System Docker Setup..."

# Build and start containers
echo "📦 Building Docker containers..."
docker-compose up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install Laravel dependencies
echo "📥 Installing Composer dependencies..."
docker-compose exec -T php composer install

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec -T php php artisan key:generate

# Run migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T php php artisan migrate

# Create storage symlink
echo "🔗 Creating storage symlink..."
docker-compose exec -T php php artisan storage:link

# Set permissions
echo "🔐 Setting permissions..."
docker-compose exec -T php chown -R appuser:appuser /var/www/storage
docker-compose exec -T php chown -R appuser:appuser /var/www/bootstrap/cache

echo ""
echo "✅ Setup complete!"
echo "🌐 Access the application at: http://localhost:8000"
echo ""
echo "📋 Useful commands:"
echo "  docker-compose logs -f php          # View PHP logs"
echo "  docker-compose logs -f nginx        # View Nginx logs"
echo "  docker-compose exec php artisan    # Run artisan commands"
echo "  docker-compose down                # Stop containers"
