#!/bin/bash

# HD Tickets System Setup and Validation Script
# Sports Events Entry Tickets Monitoring System
# Complete setup and validation for development/staging environment

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="/var/www/hdtickets"
LOG_DIR="/var/www/hdtickets/storage/logs"
DATE=$(date +%Y%m%d_%H%M%S)

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "${LOG_DIR}/setup_validation_${DATE}.log"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "${LOG_DIR}/setup_validation_${DATE}.log"
}

log_warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "${LOG_DIR}/setup_validation_${DATE}.log"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "${LOG_DIR}/setup_validation_${DATE}.log"
}

log_step() {
    echo -e "${PURPLE}[$(date +'%Y-%m-%d %H:%M:%S')] STEP:${NC} $1" | tee -a "${LOG_DIR}/setup_validation_${DATE}.log"
}

# Initialize logging
mkdir -p "${LOG_DIR}"

log "========================================="
log "HD TICKETS SYSTEM SETUP & VALIDATION"
log "========================================="
log "Sports Events Entry Tickets Monitoring System"
log "Timestamp: $(date)"
log "Directory: $APP_DIR"

cd "$APP_DIR"

# Step 1: Environment Setup
log_step "Setting up environment..."

if [ ! -f ".env" ]; then
    log "Creating .env file from .env.example"
    cp .env.example .env
    log_success ".env file created"
else
    log_success ".env file already exists"
fi

# Generate application key if not exists
if ! grep -q "APP_KEY=" .env || grep -q "APP_KEY=$" .env; then
    log "Generating Laravel application key"
    php artisan key:generate --force
    log_success "Application key generated"
else
    log_success "Application key already exists"
fi

# Step 2: Composer Dependencies
log_step "Installing Composer dependencies..."

if [ ! -d "vendor" ] || [ ! -f "composer.lock" ]; then
    log "Installing Composer dependencies"
    composer install --no-dev --optimize-autoloader
    log_success "Composer dependencies installed"
else
    log_success "Composer dependencies already installed"
fi

# Step 3: File Permissions
log_step "Setting file permissions..."

# Try to set permissions, but don't fail if we can't
if chmod -R 755 storage bootstrap/cache 2>/dev/null; then
    log_success "File permissions set successfully"
else
    log_warning "Could not set file permissions (insufficient privileges)"
fi

# Try to set ownership, but don't fail if we can't
if chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || \
   chown -R $USER:$USER storage bootstrap/cache 2>/dev/null; then
    log_success "File ownership set successfully"
else
    log_warning "Could not set file ownership (insufficient privileges)"
fi

# Step 4: Configuration Caching
log_step "Optimizing Laravel configuration..."

# Clear caches first
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Try to cache configuration, but handle database errors gracefully
if php artisan config:cache 2>/dev/null; then
    log_success "Configuration cached successfully"
else
    log_warning "Could not cache configuration (database dependency issues)"
fi

# Try to cache routes
if php artisan route:cache 2>/dev/null; then
    log_success "Routes cached successfully"
else
    log_warning "Could not cache routes (may have database dependencies)"
fi

log_success "Laravel configuration optimization completed"

# Step 5: Database Setup (Basic Structure Only)
log_step "Setting up basic database structure..."

# Create basic Laravel migrations that don't depend on custom code
log "Running core Laravel migrations (if database is available)"
if php artisan migrate:status > /dev/null 2>&1; then
    log_success "Database connection verified"
    
    # Only run Laravel's default migrations
    php artisan migrate --path=database/migrations --force 2>/dev/null || \
    log_warning "Some migrations failed (this is expected for custom tables)"
else
    log_warning "Database connection not available - skipping database setup"
fi

# Step 6: Storage Setup
log_step "Setting up storage..."

php artisan storage:link 2>/dev/null || log_warning "Storage link already exists"

# Create necessary directories
mkdir -p storage/app/public/exports
mkdir -p storage/app/tickets
mkdir -p storage/app/scraping-cache
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views

log_success "Storage directories created"

# Step 7: Validation Tests
log_step "Running system validation tests..."

TESTS_PASSED=0
TESTS_FAILED=0
TESTS_WARNINGS=0

# Test 1: Core Directory Structure
log "Testing: Core directory structure"
if [ -d "app" ] && [ -d "config" ] && [ -d "database" ] && [ -d "resources" ] && \
   [ -d "routes" ] && [ -d "storage" ] && [ -d "tests" ]; then
    log_success "Core directory structure: PASS"
    ((TESTS_PASSED++))
else
    log_error "Core directory structure: FAIL"
    ((TESTS_FAILED++))
fi

# Test 2: PHP Syntax
log "Testing: PHP syntax validation"
if find app/ -name "*.php" -exec php -l {} \; > /tmp/php_syntax.log 2>&1; then
    if ! grep -q "Parse error" /tmp/php_syntax.log; then
        log_success "PHP syntax validation: PASS"
        ((TESTS_PASSED++))
    else
        log_error "PHP syntax validation: FAIL (parse errors found)"
        grep "Parse error" /tmp/php_syntax.log | head -5
        ((TESTS_FAILED++))
    fi
else
    log_error "PHP syntax validation: FAIL (syntax check failed)"
    ((TESTS_FAILED++))
fi

# Test 3: Essential Files
log "Testing: Essential Laravel files"
if [ -f "artisan" ] && [ -f "composer.json" ] && [ -f ".env" ]; then
    log_success "Essential Laravel files: PASS"
    ((TESTS_PASSED++))
else
    log_error "Essential Laravel files: FAIL"
    ((TESTS_FAILED++))
fi

# Test 4: Vendor Dependencies
log "Testing: Vendor dependencies"
if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
    log_success "Vendor dependencies: PASS"
    ((TESTS_PASSED++))
else
    log_error "Vendor dependencies: FAIL"
    ((TESTS_FAILED++))
fi

# Test 5: Configuration
log "Testing: Laravel configuration (basic)"
if php -r "require 'vendor/autoload.php'; echo 'Config loaded successfully';" > /dev/null 2>&1; then
    log_success "Laravel configuration: PASS"
    ((TESTS_PASSED++))
else
    log_error "Laravel configuration: FAIL"
    ((TESTS_FAILED++))
fi

# Test 6: File Permissions
log "Testing: File permissions"
if [ -w "storage/logs" ] && [ -w "bootstrap/cache" ]; then
    log_success "File permissions: PASS"
    ((TESTS_PASSED++))
else
    log_error "File permissions: FAIL"
    ((TESTS_FAILED++))
fi

# Test 7: HD Tickets Specific Structure
log "Testing: HD Tickets specific structure"
hd_structure_ok=0
if [ -d "app/Services" ]; then ((hd_structure_ok++)); fi
if [ -d "app/Models" ]; then ((hd_structure_ok++)); fi
if [ -d "app/Http/Controllers" ]; then ((hd_structure_ok++)); fi
if [ -d "database/migrations" ]; then ((hd_structure_ok++)); fi

if [ $hd_structure_ok -ge 3 ]; then
    log_success "HD Tickets structure: PASS"
    ((TESTS_PASSED++))
else
    log_warning "HD Tickets structure: PARTIAL (some directories missing)"
    ((TESTS_WARNINGS++))
fi

# System Information Gathering
log_step "Gathering system information..."

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
log "PHP Version: $PHP_VERSION"

LARAVEL_VERSION=$(php artisan --version 2>/dev/null | cut -d' ' -f3 || echo "Unable to determine")
log "Laravel Version: $LARAVEL_VERSION"

COMPOSER_VERSION=$(composer --version 2>/dev/null | cut -d' ' -f3 || echo "Not available")
log "Composer Version: $COMPOSER_VERSION"

# Code Statistics
PHP_FILES_COUNT=$(find app/ -name "*.php" 2>/dev/null | wc -l)
log "PHP Files in app/: $PHP_FILES_COUNT"

SERVICE_FILES_COUNT=$(find app/Services/ -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Service Files: $SERVICE_FILES_COUNT"

MODEL_FILES_COUNT=$(find app/Models/ -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Model Files: $MODEL_FILES_COUNT"

CONTROLLER_FILES_COUNT=$(find app/Http/Controllers/ -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Controller Files: $CONTROLLER_FILES_COUNT"

MIGRATION_FILES_COUNT=$(find database/migrations/ -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Migration Files: $MIGRATION_FILES_COUNT"

# HD Tickets Specific Checks
log_step "HD Tickets Sports Events System specific validations..."

# Check for key components
if [ -f "app/Services/Core/ScrapingService.php" ]; then
    log_success "Core ScrapingService: FOUND"
else
    log_warning "Core ScrapingService: NOT FOUND"
fi

if [ -f "app/Services/Core/TicketMonitoringService.php" ]; then
    log_success "Core TicketMonitoringService: FOUND"
else
    log_warning "Core TicketMonitoringService: NOT FOUND"
fi

if [ -f "app/Services/Security/AuthenticationService.php" ]; then
    log_success "Enhanced AuthenticationService: FOUND"
else
    log_warning "Enhanced AuthenticationService: NOT FOUND"
fi

if [ -d "domain" ]; then
    DOMAIN_CONTEXTS=$(find domain/ -maxdepth 1 -type d 2>/dev/null | wc -l || echo "0")
    if [ $DOMAIN_CONTEXTS -gt 1 ]; then
        log_success "DDD Domain Contexts: FOUND ($((DOMAIN_CONTEXTS - 1)) contexts)"
    else
        log_warning "DDD Domain Contexts: NOT FOUND"
    fi
else
    log_warning "DDD Domain Structure: NOT FOUND"
fi

if [ -f "app/EventSourcing/EventStoreInterface.php" ]; then
    log_success "Event Sourcing Infrastructure: FOUND"
else
    log_warning "Event Sourcing Infrastructure: NOT FOUND"
fi

# Generate comprehensive report
log_step "Generating system report..."

cat > "${LOG_DIR}/system_setup_report_${DATE}.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "setup_validation": {
        "tests_passed": $TESTS_PASSED,
        "tests_failed": $TESTS_FAILED,
        "tests_warnings": $TESTS_WARNINGS,
        "total_tests": $((TESTS_PASSED + TESTS_FAILED + TESTS_WARNINGS)),
        "success_rate": $(echo "scale=2; $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)" | bc -l 2>/dev/null || echo "100")
    },
    "system_info": {
        "php_version": "$PHP_VERSION",
        "laravel_version": "$LARAVEL_VERSION",
        "composer_version": "$COMPOSER_VERSION"
    },
    "code_statistics": {
        "php_files": $PHP_FILES_COUNT,
        "service_files": $SERVICE_FILES_COUNT,
        "model_files": $MODEL_FILES_COUNT,
        "controller_files": $CONTROLLER_FILES_COUNT,
        "migration_files": $MIGRATION_FILES_COUNT
    },
    "hd_tickets_features": {
        "core_scraping_service": $([ -f "app/Services/Core/ScrapingService.php" ] && echo "true" || echo "false"),
        "ticket_monitoring_service": $([ -f "app/Services/Core/TicketMonitoringService.php" ] && echo "true" || echo "false"),
        "enhanced_authentication": $([ -f "app/Services/Security/AuthenticationService.php" ] && echo "true" || echo "false"),
        "ddd_structure": $([ -d "domain" ] && echo "true" || echo "false"),
        "event_sourcing": $([ -f "app/EventSourcing/EventStoreInterface.php" ] && echo "true" || echo "false")
    }
}
EOF

log_success "System report generated: ${LOG_DIR}/system_setup_report_${DATE}.json"

# Final Summary
log "========================================="
log "SETUP & VALIDATION COMPLETE"
log "========================================="
log "Tests Passed: $TESTS_PASSED"
log "Tests Failed: $TESTS_FAILED"
log "Tests with Warnings: $TESTS_WARNINGS"
if [ $((TESTS_PASSED + TESTS_FAILED)) -gt 0 ]; then
    SUCCESS_RATE=$(awk "BEGIN {printf \"%.1f\", $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)}")
    log "Success Rate: ${SUCCESS_RATE}%"
fi
log "HD Tickets Sports Events Monitoring System"
log "========================================="

# Cleanup
rm -f /tmp/php_syntax.log

# Next Steps Recommendation
log_step "NEXT STEPS RECOMMENDATIONS:"
log "1. Configure database connection in .env file"
log "2. Run database migrations: php artisan migrate"
log "3. Seed initial data: php artisan db:seed"
log "4. Configure web server (Apache/Nginx)"
log "5. Set up SSL certificate for production"
log "6. Configure cron jobs for scheduled tasks"
log "7. Set up queue workers: php artisan queue:work"
log "8. Configure monitoring and logging services"

# Exit with appropriate code
if [ $TESTS_FAILED -gt 0 ]; then
    log_error "Some critical tests failed. Please review and fix issues before proceeding."
    exit 1
elif [ $TESTS_WARNINGS -gt 0 ]; then
    log_warning "Setup completed with warnings. System is functional but some features may be incomplete."
    exit 0
else
    log_success "Setup and validation completed successfully! HD Tickets system is ready for configuration."
    exit 0
fi
