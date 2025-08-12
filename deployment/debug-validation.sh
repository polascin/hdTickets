#!/bin/bash

# Debug version of validation script
set -x  # Enable debug mode

APP_DIR="/var/www/hdtickets"
LOG_DIR="/var/www/hdtickets/storage/logs"

echo "Starting validation debug..."
echo "App dir: $APP_DIR"

# Test directory structure
if [ -d "$APP_DIR/app" ] && \
   [ -d "$APP_DIR/config" ] && \
   [ -d "$APP_DIR/database" ] && \
   [ -d "$APP_DIR/resources" ] && \
   [ -d "$APP_DIR/routes" ] && \
   [ -d "$APP_DIR/storage" ] && \
   [ -d "$APP_DIR/tests" ]; then
    echo "Directory structure: PASS"
else
    echo "Directory structure: FAIL"
fi

# Test PHP syntax
echo "Testing PHP syntax..."
cd "$APP_DIR"
if find app/ -name "*.php" -exec php -l {} \; > /tmp/php_syntax_debug.log 2>&1; then
    if ! grep -q "Parse error" /tmp/php_syntax_debug.log; then
        echo "PHP syntax: PASS"
    else
        echo "PHP syntax: FAIL (parse errors found)"
        grep "Parse error" /tmp/php_syntax_debug.log
    fi
else
    echo "PHP syntax: FAIL (find command failed)"
fi

# Test Composer
echo "Testing Composer dependencies..."
if [ -f "composer.json" ] && [ -f "composer.lock" ] && [ -d "vendor" ]; then
    echo "Composer dependencies: PASS"
else
    echo "Composer dependencies: FAIL"
    ls -la composer* vendor 2>/dev/null || echo "Files missing"
fi

# Test Laravel config
echo "Testing Laravel configuration..."
if [ -f ".env" ]; then
    echo ".env file exists"
    if php artisan config:show app.name > /dev/null 2>&1; then
        echo "Laravel configuration: PASS"
    else
        echo "Laravel configuration: FAIL (config:show failed)"
    fi
else
    echo "Laravel configuration: FAIL (.env missing)"
fi

echo "Debug validation complete."
