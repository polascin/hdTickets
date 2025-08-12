#!/bin/bash

# HDTickets Deployment Script
# This script prepares the application for production deployment  
# Updated for version 4.0.0 with Node.js v22.18.0 and Laravel 12.22.1
# Sports Events Entry Tickets Monitoring, Scraping and Purchase System

echo "=== HDTickets Deployment Script v4.0.0 ==="
echo "Preparing Sports Event Ticket Monitoring System for production..."
echo "Laravel Framework: $(php artisan --version)"
echo "PHP Version: $(php -v | head -n1)"

# Check Node.js version requirement
echo "Checking Node.js version..."
NODE_VERSION=$(node --version 2>/dev/null || echo "not_found")
if [ "$NODE_VERSION" = "not_found" ]; then
    echo "❌ Node.js not found. Please install Node.js v22.18.0"
    echo "   You can use: curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash"
    echo "   Then: nvm install 22.18.0 && nvm use 22.18.0"
    exit 1
fi

REQUIRED_VERSION="v22.18.0"
if [ "$NODE_VERSION" != "$REQUIRED_VERSION" ]; then
    echo "⚠️  Warning: Node.js version $NODE_VERSION detected, but v22.18.0 is required"
    echo "   Consider using: nvm use 22.18.0"
else
    echo "✅ Node.js version $NODE_VERSION is correct"
fi

# Clear any existing caches
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Install backend dependencies
echo "Installing production PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install frontend dependencies and build assets
echo "Installing frontend dependencies..."
npm ci --only=production

echo "Building production assets..."
npm run build

# Generate application key if not set
echo "Generating application key..."
php artisan key:generate --force

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Cache configuration for production
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

# Set proper permissions (Linux/Unix only)
if [[ "$OSTYPE" == "linux-gnu"* ]] || [[ "$OSTYPE" == "darwin"* ]]; then
    echo "Setting proper file permissions..."
    chmod -R 755 storage/
    chmod -R 755 bootstrap/cache/
    chown -R www-data:www-data storage/
    chown -R www-data:www-data bootstrap/cache/
fi

echo "=== Deployment preparation complete! ==="
echo ""
echo "Version Information:"
echo "- Laravel Framework: $(php artisan --version 2>/dev/null || echo 'Not available')"
echo "- PHP: $(php --version | head -n1)"
echo "- Node.js: $(node --version)"
echo ""
echo "Next steps:"
echo "1. Upload files to your web server (Ubuntu 24.04 LTS with Apache2)"
echo "2. Copy .env.production to .env and configure database settings"
echo "3. Run 'php artisan migrate' and 'php artisan passport:install' on server"
echo "4. Set proper file permissions if on Linux/Unix"
echo "5. Configure your web server to point to the 'public' directory"
echo "6. Clear all caches: config, route, view, and application cache"
echo "7. Monitor application logs for any runtime errors"
