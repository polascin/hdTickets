#!/bin/bash

# HD Tickets Development Environment Startup Script
# This script sets up and starts all necessary services for development

echo "=== HD Tickets Development Environment Setup ==="
echo "Starting development environment for Sports Events Entry Tickets System..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "Error: Please run this script from the HD Tickets root directory"
    exit 1
fi

# Clear all caches for development
echo "🧹 Clearing Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate application key if needed
if ! grep -q "APP_KEY=base64:" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Check database connection
echo "🗄️  Checking database connection..."
if php artisan migrate:status > /dev/null 2>&1; then
    echo "✅ Database connection successful"
else
    echo "❌ Database connection failed. Please check your .env configuration"
    exit 1
fi

# Check Redis connection
echo "📦 Checking Redis connection..."
if redis-cli ping > /dev/null 2>&1; then
    echo "✅ Redis connection successful"
else
    echo "❌ Redis connection failed. Starting Redis..."
    sudo systemctl start redis-server
fi

# Install/update dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-interaction --prefer-dist

echo "📦 Installing Node.js dependencies..."
npm install

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Start queue workers in background (development mode)
echo "🔄 Starting queue workers..."
php artisan queue:restart
nohup php artisan queue:work --sleep=3 --tries=3 --max-time=3600 > storage/logs/queue.log 2>&1 &

# Start Laravel scheduler (for development)
echo "⏰ Starting Laravel scheduler..."
nohup php artisan schedule:work > storage/logs/scheduler.log 2>&1 &

# Build frontend assets for development
echo "🎨 Building frontend assets..."
npm run dev &

echo ""
echo "🚀 Development environment is ready!"
echo ""
echo "Available commands:"
echo "  - Access application: http://localhost"
echo "  - Stop queue workers: php artisan queue:restart"
echo "  - Monitor logs: tail -f storage/logs/laravel.log"
echo "  - Run tests: php artisan test"
echo "  - Admin panel: http://localhost/admin"
echo ""
echo "Development features enabled:"
echo "  - Debug mode: ON"
echo "  - Error reporting: FULL"
echo "  - Queue processing: ACTIVE"
echo "  - Asset compilation: ACTIVE"
echo ""
echo "Happy coding! 🎫"
