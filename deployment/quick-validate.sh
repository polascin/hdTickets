#!/bin/bash

# HD Tickets Quick Validation Script
set -e

APP_DIR="/var/www/hdtickets"
DATE=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/www/hdtickets/storage/logs/quick_validation_${DATE}.log"

echo "HD Tickets System Validation - $(date)" | tee "$LOG_FILE"
echo "========================================" | tee -a "$LOG_FILE"

cd "$APP_DIR"

TESTS_PASSED=0
TESTS_FAILED=0
TESTS_WARNINGS=0

# Function to run tests
run_test() {
    local test_name="$1"
    local test_result="$2"
    
    echo -n "Testing $test_name: " | tee -a "$LOG_FILE"
    
    if [ "$test_result" = "0" ]; then
        echo "PASS" | tee -a "$LOG_FILE"
        ((TESTS_PASSED++))
    elif [ "$test_result" = "1" ]; then
        echo "FAIL" | tee -a "$LOG_FILE"
        ((TESTS_FAILED++))
    else
        echo "WARNING" | tee -a "$LOG_FILE"
        ((TESTS_WARNINGS++))
    fi
}

# Test 1: Directory structure
[ -d "app" ] && [ -d "config" ] && [ -d "database" ] && [ -d "resources" ] && \
[ -d "routes" ] && [ -d "storage" ] && [ -d "tests" ]
run_test "Directory structure" "$?"

# Test 2: Essential files
[ -f "artisan" ] && [ -f "composer.json" ] && [ -f ".env" ]
run_test "Essential files" "$?"

# Test 3: Vendor dependencies  
[ -d "vendor" ] && [ -f "vendor/autoload.php" ]
run_test "Vendor dependencies" "$?"

# Test 4: File permissions
[ -w "storage/logs" ] && [ -w "bootstrap/cache" ]
run_test "File permissions" "$?"

# Test 5: PHP syntax check (simplified)
find app/ -name "*.php" -exec php -l {} \; > /tmp/php_check.log 2>&1
if ! grep -q "Parse error" /tmp/php_check.log; then
    run_test "PHP syntax" "0"
else
    run_test "PHP syntax" "1"
fi

# Test 6: Configuration loading
php -r "require 'vendor/autoload.php'; echo 'OK';" > /dev/null 2>&1
run_test "Configuration loading" "$?"

# HD Tickets specific checks
echo "" | tee -a "$LOG_FILE"
echo "HD Tickets Specific Features:" | tee -a "$LOG_FILE"
echo "=============================" | tee -a "$LOG_FILE"

# Check for key files
[ -f "app/Services/Core/ScrapingService.php" ] && echo "✓ Core ScrapingService found" || echo "✗ Core ScrapingService missing" | tee -a "$LOG_FILE"
[ -f "app/Services/Core/TicketMonitoringService.php" ] && echo "✓ Ticket Monitoring Service found" || echo "✗ Ticket Monitoring Service missing" | tee -a "$LOG_FILE"
[ -f "app/Services/Security/AuthenticationService.php" ] && echo "✓ Enhanced Authentication found" || echo "✗ Enhanced Authentication missing" | tee -a "$LOG_FILE"
[ -d "domain" ] && echo "✓ DDD Domain structure found" || echo "✗ DDD Domain structure missing" | tee -a "$LOG_FILE"
[ -f "app/EventSourcing/EventStoreInterface.php" ] && echo "✓ Event Sourcing infrastructure found" || echo "✗ Event Sourcing infrastructure missing" | tee -a "$LOG_FILE"

# System information
echo "" | tee -a "$LOG_FILE"
echo "System Information:" | tee -a "$LOG_FILE"
echo "==================" | tee -a "$LOG_FILE"
echo "PHP Version: $(php -r 'echo PHP_VERSION;')" | tee -a "$LOG_FILE"
echo "Laravel Version: $(php artisan --version 2>/dev/null | cut -d' ' -f3 || echo 'Unable to determine')" | tee -a "$LOG_FILE"
echo "Composer Version: $(composer --version 2>/dev/null | cut -d' ' -f3 || echo 'Not available')" | tee -a "$LOG_FILE"

# Code statistics
echo "" | tee -a "$LOG_FILE"
echo "Code Statistics:" | tee -a "$LOG_FILE"
echo "===============" | tee -a "$LOG_FILE"
echo "PHP Files: $(find app/ -name "*.php" | wc -l)" | tee -a "$LOG_FILE"
echo "Service Files: $(find app/Services/ -name "*.php" 2>/dev/null | wc -l || echo '0')" | tee -a "$LOG_FILE"
echo "Model Files: $(find app/Models/ -name "*.php" 2>/dev/null | wc -l || echo '0')" | tee -a "$LOG_FILE"
echo "Controller Files: $(find app/Http/Controllers/ -name "*.php" 2>/dev/null | wc -l || echo '0')" | tee -a "$LOG_FILE"
echo "Migration Files: $(find database/migrations/ -name "*.php" 2>/dev/null | wc -l || echo '0')" | tee -a "$LOG_FILE"

# Summary
echo "" | tee -a "$LOG_FILE"
echo "Validation Summary:" | tee -a "$LOG_FILE"
echo "==================" | tee -a "$LOG_FILE"
echo "Tests Passed: $TESTS_PASSED" | tee -a "$LOG_FILE"
echo "Tests Failed: $TESTS_FAILED" | tee -a "$LOG_FILE"
echo "Tests with Warnings: $TESTS_WARNINGS" | tee -a "$LOG_FILE"

if [ $TESTS_FAILED -eq 0 ]; then
    echo "SUCCESS: All tests passed! HD Tickets system is ready." | tee -a "$LOG_FILE"
    exit 0
else
    echo "WARNING: Some tests failed. Review issues before proceeding." | tee -a "$LOG_FILE"
    exit 1
fi
