#!/bin/bash

# HDTickets Deployment Script
# This script prepares the application for production deployment

echo "=== HDTickets Deployment Script ==="
echo "Preparing application for production..."

# Clear any existing caches
echo "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Install dependencies
echo "Installing production dependencies..."
composer install --optimize-autoloader --no-dev

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
echo "Next steps:"
echo "1. Upload files to your web server"
echo "2. Copy .env.production to .env and configure database settings"
echo "3. Run 'php artisan migrate' on the server"
echo "4. Set proper file permissions if on Linux/Unix"
echo "5. Configure your web server to point to the 'public' directory"
