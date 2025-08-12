#!/bin/bash

# HD Tickets Basic System Validation Script
# Sports Events Entry Tickets Monitoring System
# Basic validation for development/staging environment

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
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "${LOG_DIR}/basic_validation_${DATE}.log"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "${LOG_DIR}/basic_validation_${DATE}.log"
}

log_warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "${LOG_DIR}/basic_validation_${DATE}.log"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "${LOG_DIR}/basic_validation_${DATE}.log"
}

# Test results tracking
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_WARNINGS=0

# Test tracking function
run_test() {
    local test_name="$1"
    local test_command="$2"
    
    log "Running test: $test_name"
    
    if eval "$test_command"; then
        log_success "$test_name passed"
        ((TESTS_PASSED++))
        return 0
    else
        log_error "$test_name failed"
        ((TESTS_FAILED++))
        return 1
    fi
}

# Initialize logging
mkdir -p "${LOG_DIR}"

log "HD Tickets Basic System Validation Starting"
log "Timestamp: $(date)"
log "Directory: $APP_DIR"

# Test 1: Directory Structure
test_directory_structure() {
    [ -d "$APP_DIR/app" ] && \
    [ -d "$APP_DIR/config" ] && \
    [ -d "$APP_DIR/database" ] && \
    [ -d "$APP_DIR/resources" ] && \
    [ -d "$APP_DIR/routes" ] && \
    [ -d "$APP_DIR/storage" ] && \
    [ -d "$APP_DIR/tests" ]
}

# Test 2: PHP Syntax Check
test_php_syntax() {
    cd "$APP_DIR"
    find app/ -name "*.php" -exec php -l {} \; > /tmp/php_syntax.log 2>&1
    ! grep -q "Parse error" /tmp/php_syntax.log
}

# Test 3: Composer Dependencies
test_composer_dependencies() {
    cd "$APP_DIR"
    [ -f "composer.json" ] && [ -f "composer.lock" ] && [ -d "vendor" ]
}

# Test 4: Laravel Configuration
test_laravel_config() {
    cd "$APP_DIR"
    [ -f ".env" ] && php artisan config:show app.name > /dev/null 2>&1
}

# Test 5: Database Configuration
test_database_config() {
    cd "$APP_DIR"
    php artisan config:show database.default > /dev/null 2>&1
}

# Test 6: File Permissions
test_file_permissions() {
    [ -w "$APP_DIR/storage" ] && \
    [ -w "$APP_DIR/storage/logs" ] && \
    [ -w "$APP_DIR/bootstrap/cache" ]
}

# Test 7: Migration Files
test_migration_files() {
    cd "$APP_DIR"
    [ -d "database/migrations" ] && \
    [ "$(find database/migrations -name "*.php" | wc -l)" -gt 0 ]
}

# Test 8: Service Classes
test_service_classes() {
    cd "$APP_DIR"
    [ -d "app/Services" ] && \
    [ "$(find app/Services -name "*.php" | wc -l)" -gt 0 ]
}

# Test 9: Model Classes
test_model_classes() {
    cd "$APP_DIR"
    [ -d "app/Models" ] && \
    [ "$(find app/Models -name "*.php" | wc -l)" -gt 0 ]
}

# Test 10: Controllers
test_controllers() {
    cd "$APP_DIR"
    [ -d "app/Http/Controllers" ] && \
    [ "$(find app/Http/Controllers -name "*.php" | wc -l)" -gt 0 ]
}

# Test 11: Routes
test_routes() {
    cd "$APP_DIR"
    [ -f "routes/web.php" ] && [ -f "routes/api.php" ]
}

# Test 12: Test Suite Structure
test_test_structure() {
    cd "$APP_DIR"
    [ -f "phpunit.xml" ] && \
    [ -d "tests" ] && \
    [ -d "tests/Unit" ] && \
    [ -d "tests/Feature" ]
}

# Test 13: Frontend Assets
test_frontend_assets() {
    cd "$APP_DIR"
    [ -f "package.json" ] && \
    [ -f "vite.config.js" ] && \
    [ -d "resources/js" ] && \
    [ -d "resources/css" ]
}

# Test 14: Cache Directories
test_cache_directories() {
    cd "$APP_DIR"
    [ -d "storage/framework/cache" ] && \
    [ -d "storage/framework/sessions" ] && \
    [ -d "storage/framework/views" ]
}

# Test 15: Configuration Cache Capability
test_config_cache_capability() {
    cd "$APP_DIR"
    php artisan config:cache > /dev/null 2>&1 && \
    php artisan config:clear > /dev/null 2>&1
}

# Run all tests
log "Starting comprehensive system validation..."

run_test "Directory Structure" "test_directory_structure"
run_test "PHP Syntax Check" "test_php_syntax"
run_test "Composer Dependencies" "test_composer_dependencies"
run_test "Laravel Configuration" "test_laravel_config"
run_test "Database Configuration" "test_database_config"
run_test "File Permissions" "test_file_permissions"
run_test "Migration Files" "test_migration_files"
run_test "Service Classes" "test_service_classes"
run_test "Model Classes" "test_model_classes"
run_test "Controllers" "test_controllers"
run_test "Routes" "test_routes"
run_test "Test Suite Structure" "test_test_structure"
run_test "Frontend Assets" "test_frontend_assets"
run_test "Cache Directories" "test_cache_directories"
run_test "Config Cache Capability" "test_config_cache_capability"

# System Information
log "Gathering system information..."

PHP_VERSION=$(php -r 'echo PHP_VERSION;')
log "PHP Version: $PHP_VERSION"

if cd "$APP_DIR" && php artisan --version > /dev/null 2>&1; then
    LARAVEL_VERSION=$(cd "$APP_DIR" && php artisan --version | cut -d' ' -f3)
    log "Laravel Version: $LARAVEL_VERSION"
fi

COMPOSER_VERSION=$(composer --version 2>/dev/null | cut -d' ' -f3 || echo "Not installed")
log "Composer Version: $COMPOSER_VERSION"

# Code Statistics
PHP_FILES_COUNT=$(find "$APP_DIR/app" -name "*.php" | wc -l)
log "PHP Files in app/: $PHP_FILES_COUNT"

SERVICE_FILES_COUNT=$(find "$APP_DIR/app/Services" -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Service Files: $SERVICE_FILES_COUNT"

MODEL_FILES_COUNT=$(find "$APP_DIR/app/Models" -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Model Files: $MODEL_FILES_COUNT"

MIGRATION_FILES_COUNT=$(find "$APP_DIR/database/migrations" -name "*.php" 2>/dev/null | wc -l || echo "0")
log "Migration Files: $MIGRATION_FILES_COUNT"

# HD Tickets Specific Validations
log "HD Tickets Sports Events System Specific Checks:"

# Check for key domain files
if [ -d "$APP_DIR/domain" ]; then
    DOMAIN_CONTEXTS=$(find "$APP_DIR/domain" -maxdepth 1 -type d | wc -l)
    log "Domain Contexts Found: $((DOMAIN_CONTEXTS - 1))"
fi

# Check for consolidated services
if [ -f "$APP_DIR/app/Services/Core/ScrapingService.php" ]; then
    log_success "Core ScrapingService found"
else
    log_warning "Core ScrapingService not found"
fi

if [ -f "$APP_DIR/app/Services/Core/TicketMonitoringService.php" ]; then
    log_success "Core TicketMonitoringService found"
else
    log_warning "Core TicketMonitoringService not found"
fi

# Check for security implementations
if [ -f "$APP_DIR/app/Services/Security/AuthenticationService.php" ]; then
    log_success "Enhanced AuthenticationService found"
else
    log_warning "Enhanced AuthenticationService not found"
fi

# Check for event sourcing
if [ -f "$APP_DIR/app/EventSourcing/EventStoreInterface.php" ]; then
    log_success "Event Sourcing infrastructure found"
else
    log_warning "Event Sourcing infrastructure not found"
fi

# Generate basic system report
cat > "${LOG_DIR}/basic_system_report_${DATE}.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "validation_results": {
        "tests_passed": $TESTS_PASSED,
        "tests_failed": $TESTS_FAILED,
        "tests_warnings": $TESTS_WARNINGS,
        "total_tests": $((TESTS_PASSED + TESTS_FAILED)),
        "success_rate": $(echo "scale=2; $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)" | bc -l)
    },
    "system_info": {
        "php_version": "$PHP_VERSION",
        "composer_version": "$COMPOSER_VERSION",
        "laravel_version": "${LARAVEL_VERSION:-unknown}"
    },
    "code_statistics": {
        "php_files": $PHP_FILES_COUNT,
        "service_files": $SERVICE_FILES_COUNT,
        "model_files": $MODEL_FILES_COUNT,
        "migration_files": $MIGRATION_FILES_COUNT
    }
}
EOF

# Summary
log "========================================="
log "BASIC SYSTEM VALIDATION COMPLETE"
log "========================================="
log "Tests Passed: $TESTS_PASSED"
log "Tests Failed: $TESTS_FAILED" 
log "Tests with Warnings: $TESTS_WARNINGS"
log "Success Rate: $(echo "scale=1; $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)" | bc -l)%"
log "========================================="

# Cleanup temp files
rm -f /tmp/php_syntax.log

# Exit with appropriate code
if [ $TESTS_FAILED -gt 0 ]; then
    log_error "Some basic tests failed. Please review the issues."
    exit 1
elif [ $TESTS_WARNINGS -gt 0 ]; then
    log_warning "All tests passed but there are warnings."
    exit 0
else
    log_success "All basic tests passed successfully! HD Tickets system structure is valid."
    exit 0
fi
