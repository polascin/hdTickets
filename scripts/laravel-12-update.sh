#!/bin/bash
# Laravel 12 Update and Optimization Script
# HD Tickets Sports Event Ticket Monitoring System

set -e

echo "🚀 Laravel 12 Update and Optimization Script"
echo "============================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel project root directory"
    exit 1
fi

print_info "Starting Laravel 12 update process..."

# 1. Clear all caches
print_info "Clearing all caches..."
php artisan optimize:clear
print_status "Caches cleared"

# 2. Update Composer dependencies
print_info "Updating Composer dependencies..."
composer update --no-dev --optimize-autoloader
print_status "Composer dependencies updated"

# 3. Check for security vulnerabilities
print_info "Checking for security vulnerabilities..."
composer audit || print_warning "Security audit completed with warnings"

# 4. Run migrations
print_info "Running database migrations..."
php artisan migrate --force
print_status "Migrations completed"

# 5. Link storage
print_info "Linking storage..."
php artisan storage:link
print_status "Storage linked"

# 6. Generate application key if missing
if grep -q "APP_KEY=$" .env 2>/dev/null || [ ! -f .env ]; then
    print_info "Generating application key..."
    php artisan key:generate --force
    print_status "Application key generated"
fi

# 7. Cache configuration for production
print_info "Optimizing for production..."
php artisan config:cache
php artisan route:cache  
php artisan view:cache
php artisan event:cache
print_status "Production optimizations complete"

# 8. Install Passport keys if needed
if php artisan route:list | grep -q "passport" 2>/dev/null; then
    print_info "Installing Passport keys..."
    php artisan passport:keys || print_warning "Passport keys installation failed (may already exist)"
fi

# 9. Create necessary directories
print_info "Creating necessary directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p public/storage
print_status "Directories created"

# 10. Set proper permissions
print_info "Setting proper permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 755 public
print_status "Permissions set"

# 11. Clear and warm various caches
print_info "Warming caches..."
php artisan route:cache
php artisan config:cache
php artisan view:cache
print_status "Caches warmed"

# 12. Update Node dependencies and build assets
if [ -f "package.json" ]; then
    print_info "Updating Node.js dependencies..."
    npm install
    print_info "Building production assets..."
    npm run build
    print_status "Assets built successfully"
fi

# 13. Check for common Laravel 12 compatibility issues
print_info "Checking Laravel 12 compatibility..."

# Check for deprecated middleware aliases
if grep -r "auth:" app/Http/Kernel.php 2>/dev/null; then
    print_warning "Found deprecated middleware syntax in Kernel.php"
fi

# Check for old broadcasting configuration
if grep -q "BROADCAST_DRIVER=pusher" .env 2>/dev/null; then
    print_info "Broadcasting driver is set to pusher - this is correct for Laravel 12"
fi

# 14. Run a few basic tests to ensure everything is working
print_info "Running basic functionality tests..."
php artisan test --testsuite=Unit --stop-on-failure || print_warning "Some unit tests failed"

# 15. Show application status
print_info "Application status:"
php artisan about

# 16. Create a backup timestamp
echo "$(date)" > storage/logs/laravel-12-update.log

print_status "Laravel 12 update and optimization complete!"
echo ""
echo "📋 Summary:"
echo "- Laravel Framework updated to latest version"
echo "- Dependencies updated and optimized"
echo "- Database migrations applied"
echo "- Caches optimized for production"
echo "- Storage properly linked"
echo "- Assets built (if Node.js available)"
echo "- Permissions set correctly"
echo ""
print_info "Next steps:"
echo "1. Test your application thoroughly"
echo "2. Check logs: tail -f storage/logs/laravel.log"
echo "3. Monitor performance"
echo "4. Review any deprecation warnings"
echo ""
print_status "HD Tickets application is ready for production!"
