#!/bin/bash

# HD Tickets Post-Deployment Validation Script
# Sports Events Entry Tickets Monitoring System
# 
# Comprehensive validation of deployed system

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
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "${LOG_DIR}/post_deploy_${DATE}.log"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "${LOG_DIR}/post_deploy_${DATE}.log"
}

log_warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "${LOG_DIR}/post_deploy_${DATE}.log"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "${LOG_DIR}/post_deploy_${DATE}.log"
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

log "HD Tickets Post-Deployment Validation Starting"
log "Timestamp: $(date)"
log "Environment: $(cat /etc/environment | grep APP_ENV || echo 'production')"

# Test 1: Basic Health Check
test_basic_health() {
    local response=$(curl -s -w "%{http_code}" -o /tmp/health_basic.json http://localhost/health)
    if [ "$response" = "200" ]; then
        local status=$(jq -r '.status' /tmp/health_basic.json 2>/dev/null || echo "error")
        [ "$status" = "healthy" ]
    else
        return 1
    fi
}

# Test 2: Detailed Health Check
test_detailed_health() {
    local response=$(curl -s -w "%{http_code}" -o /tmp/health_detailed.json http://localhost/health/detailed)
    if [ "$response" = "200" ]; then
        local status=$(jq -r '.status' /tmp/health_detailed.json 2>/dev/null || echo "error")
        [ "$status" = "healthy" ] || [ "$status" = "degraded" ]
    else
        return 1
    fi
}

# Test 3: Database Connectivity
test_database_connectivity() {
    cd "$APP_DIR"
    php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection successful';" >/dev/null 2>&1
}

# Test 4: Sports Events Data
test_sports_events_data() {
    local response=$(curl -s -w "%{http_code}" -o /tmp/sports_events.json http://localhost/metrics/sports-events)
    if [ "$response" = "200" ]; then
        local status=$(jq -r '.status' /tmp/sports_events.json 2>/dev/null || echo "error")
        [ "$status" = "success" ]
    else
        return 1
    fi
}

# Test 5: Performance Metrics
test_performance_metrics() {
    local response=$(curl -s -w "%{http_code}" -o /tmp/performance.json http://localhost/metrics/performance)
    if [ "$response" = "200" ]; then
        local status=$(jq -r '.status' /tmp/performance.json 2>/dev/null || echo "error")
        [ "$status" = "success" ]
    else
        return 1
    fi
}

# Test 6: Cache System
test_cache_system() {
    cd "$APP_DIR"
    php artisan tinker --execute="Cache::put('test', 'value', 60); echo Cache::get('test'); Cache::forget('test');" | grep -q "value"
}

# Test 7: Queue System
test_queue_system() {
    cd "$APP_DIR"
    local queue_size=$(php artisan queue:size 2>/dev/null || echo "error")
    [ "$queue_size" != "error" ]
}

# Test 8: Ticket Platform APIs (basic connectivity test)
test_ticket_platforms() {
    local platforms=("ticketmaster" "stubhub" "viagogo")
    local passed=0
    local total=0
    
    for platform in "${platforms[@]}"; do
        ((total++))
        case $platform in
            "ticketmaster")
                if nslookup app.ticketmaster.com >/dev/null 2>&1; then
                    ((passed++))
                fi
                ;;
            "stubhub")
                if nslookup api.stubhub.com >/dev/null 2>&1; then
                    ((passed++))
                fi
                ;;
            "viagogo")
                if nslookup api.viagogo.net >/dev/null 2>&1; then
                    ((passed++))
                fi
                ;;
        esac
    done
    
    # Pass if at least half of the platforms are reachable
    [ $passed -ge $((total / 2)) ]
}

# Test 9: File Permissions
test_file_permissions() {
    [ -w "$APP_DIR/storage" ] && [ -w "$APP_DIR/bootstrap/cache" ]
}

# Test 10: Service Status
test_service_status() {
    systemctl is-active --quiet apache2 && \
    systemctl is-active --quiet mysql && \
    systemctl is-active --quiet redis-server
}

# Test 11: SSL Certificate (if applicable)
test_ssl_certificate() {
    if [ -f "/etc/ssl/certs/hdtickets.crt" ]; then
        openssl x509 -in /etc/ssl/certs/hdtickets.crt -noout -dates >/dev/null 2>&1
    else
        log_warning "SSL certificate not found - skipping SSL test"
        return 0
    fi
}

# Test 12: Deployment Status
test_deployment_status() {
    local response=$(curl -s -w "%{http_code}" -o /tmp/deployment.json http://localhost/deployment/status 2>/dev/null)
    if [ "$response" = "200" ]; then
        local status=$(jq -r '.status' /tmp/deployment.json 2>/dev/null || echo "error")
        [ "$status" = "active" ]
    else
        return 1
    fi
}

# Test 13: Log Files
test_log_files() {
    [ -d "$APP_DIR/storage/logs" ] && \
    [ -w "$APP_DIR/storage/logs" ]
}

# Test 14: Configuration Cache
test_configuration_cache() {
    cd "$APP_DIR"
    php artisan config:cache >/dev/null 2>&1 && \
    php artisan route:cache >/dev/null 2>&1
}

# Test 15: Sports Events Scraping System
test_scraping_system() {
    local response=$(curl -s http://localhost/health/detailed)
    if [ $? -eq 0 ]; then
        local scraping_status=$(echo "$response" | jq -r '.checks.scraping.status' 2>/dev/null || echo "error")
        [ "$scraping_status" = "healthy" ] || [ "$scraping_status" = "degraded" ]
    else
        return 1
    fi
}

# Run all tests
log "Starting comprehensive system validation..."

run_test "Basic Health Check" "test_basic_health"
run_test "Detailed Health Check" "test_detailed_health"
run_test "Database Connectivity" "test_database_connectivity"
run_test "Sports Events Data" "test_sports_events_data"
run_test "Performance Metrics" "test_performance_metrics"
run_test "Cache System" "test_cache_system"
run_test "Queue System" "test_queue_system"
run_test "Ticket Platform APIs" "test_ticket_platforms"
run_test "File Permissions" "test_file_permissions"
run_test "Service Status" "test_service_status"
run_test "SSL Certificate" "test_ssl_certificate"
run_test "Deployment Status" "test_deployment_status"
run_test "Log Files" "test_log_files"
run_test "Configuration Cache" "test_configuration_cache"
run_test "Scraping System" "test_scraping_system"

# Performance benchmarking
log "Running performance benchmarks..."

# Response time test
RESPONSE_TIME=$(curl -o /dev/null -s -w "%{time_total}" http://localhost/health)
if (( $(echo "$RESPONSE_TIME < 1.0" | bc -l) )); then
    log_success "Response time: ${RESPONSE_TIME}s (Good)"
elif (( $(echo "$RESPONSE_TIME < 3.0" | bc -l) )); then
    log_warning "Response time: ${RESPONSE_TIME}s (Acceptable)"
    ((TESTS_WARNINGS++))
else
    log_error "Response time: ${RESPONSE_TIME}s (Poor)"
    ((TESTS_FAILED++))
fi

# Memory usage test
MEMORY_USAGE=$(php -r "echo round(memory_get_usage(true) / 1024 / 1024, 2);")
if (( $(echo "$MEMORY_USAGE < 100" | bc -l) )); then
    log_success "Memory usage: ${MEMORY_USAGE}MB (Good)"
elif (( $(echo "$MEMORY_USAGE < 200" | bc -l) )); then
    log_warning "Memory usage: ${MEMORY_USAGE}MB (Acceptable)"
    ((TESTS_WARNINGS++))
else
    log_error "Memory usage: ${MEMORY_USAGE}MB (High)"
    ((TESTS_FAILED++))
fi

# Disk space test
DISK_USAGE=$(df -h /var/www | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$DISK_USAGE" -lt 80 ]; then
    log_success "Disk usage: ${DISK_USAGE}% (Good)"
elif [ "$DISK_USAGE" -lt 90 ]; then
    log_warning "Disk usage: ${DISK_USAGE}% (Monitor closely)"
    ((TESTS_WARNINGS++))
else
    log_error "Disk usage: ${DISK_USAGE}% (Critical)"
    ((TESTS_FAILED++))
fi

# Generate system report
log "Generating system report..."

cat > "${LOG_DIR}/system_report_${DATE}.json" << EOF
{
    "timestamp": "$(date -Iseconds)",
    "deployment_validation": {
        "tests_passed": $TESTS_PASSED,
        "tests_failed": $TESTS_FAILED,
        "tests_warnings": $TESTS_WARNINGS,
        "total_tests": $((TESTS_PASSED + TESTS_FAILED)),
        "success_rate": $(echo "scale=2; $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)" | bc -l)
    },
    "performance_metrics": {
        "response_time_seconds": $RESPONSE_TIME,
        "memory_usage_mb": $MEMORY_USAGE,
        "disk_usage_percent": $DISK_USAGE
    },
    "system_info": {
        "php_version": "$(php -r 'echo PHP_VERSION;')",
        "laravel_version": "$(cd $APP_DIR && php artisan --version | cut -d' ' -f3)",
        "environment": "$(cd $APP_DIR && php artisan env 2>/dev/null || echo 'production')",
        "deployment_color": "$(cd $APP_DIR && php -r 'echo env("DEPLOYMENT_COLOR", "unknown");' 2>/dev/null || echo 'unknown')"
    }
}
EOF

# Summary
log "========================================="
log "POST-DEPLOYMENT VALIDATION COMPLETE"
log "========================================="
log "Tests Passed: $TESTS_PASSED"
log "Tests Failed: $TESTS_FAILED" 
log "Tests with Warnings: $TESTS_WARNINGS"
log "Success Rate: $(echo "scale=1; $TESTS_PASSED * 100 / ($TESTS_PASSED + $TESTS_FAILED)" | bc -l)%"
log "========================================="

# HD Tickets specific validations
log "HD Tickets Sports Events System Validation:"

# Check if sports events are being tracked
SPORTS_EVENTS_COUNT=$(curl -s http://localhost/metrics/sports-events | jq -r '.metrics.sports_events.total_events' 2>/dev/null || echo "0")
log "Total Sports Events: $SPORTS_EVENTS_COUNT"

# Check ticket listings
TICKET_LISTINGS_COUNT=$(curl -s http://localhost/metrics/sports-events | jq -r '.metrics.ticket_listings.total_listings' 2>/dev/null || echo "0")
log "Total Ticket Listings: $TICKET_LISTINGS_COUNT"

# Check active alerts
ACTIVE_ALERTS=$(curl -s http://localhost/metrics/sports-events | jq -r '.metrics.user_activity.active_alerts' 2>/dev/null || echo "0")
log "Active User Alerts: $ACTIVE_ALERTS"

# Cleanup temp files
rm -f /tmp/health_*.json /tmp/sports_events.json /tmp/performance.json /tmp/deployment.json

# Exit with appropriate code
if [ $TESTS_FAILED -gt 0 ]; then
    log_error "Some tests failed. Please review the issues before going live."
    exit 1
elif [ $TESTS_WARNINGS -gt 0 ]; then
    log_warning "All tests passed but there are warnings. Monitor the system closely."
    exit 0
else
    log_success "All tests passed successfully! HD Tickets Sports Events System is ready."
    exit 0
fi
