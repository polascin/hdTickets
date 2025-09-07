#!/bin/bash

# HD Tickets - Comprehensive Health Check Script
# Description: Monitors application health, system resources, and service availability
# Usage: ./scripts/health-check.sh [--json] [--verbose] [--alerts]
# Author: HD Tickets DevOps Team
# Version: 1.0.0

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_DIR="$PROJECT_DIR/storage/logs"
HEALTH_LOG="$LOG_DIR/health-check.log"
TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')
JSON_OUTPUT=false
VERBOSE=false
ENABLE_ALERTS=false

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Health check results
HEALTH_STATUS=0
ISSUES=()
WARNINGS=()
SUCCESSES=()

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --json)
            JSON_OUTPUT=true
            shift
            ;;
        --verbose|-v)
            VERBOSE=true
            shift
            ;;
        --alerts|-a)
            ENABLE_ALERTS=true
            shift
            ;;
        --help|-h)
            echo "HD Tickets Health Check Script"
            echo "Usage: $0 [--json] [--verbose] [--alerts]"
            echo "  --json     Output results in JSON format"
            echo "  --verbose  Enable verbose output"
            echo "  --alerts   Enable alert notifications"
            exit 0
            ;;
        *)
            echo "Unknown option $1"
            exit 1
            ;;
    esac
done

# Utility functions
log_info() {
    local message="$1"
    echo "[$TIMESTAMP] INFO: $message" >> "$HEALTH_LOG"
    if [[ "$VERBOSE" == "true" ]] || [[ "$JSON_OUTPUT" == "false" ]]; then
        echo -e "${BLUE}[INFO]${NC} $message"
    fi
}

log_success() {
    local message="$1"
    echo "[$TIMESTAMP] SUCCESS: $message" >> "$HEALTH_LOG"
    SUCCESSES+=("$message")
    if [[ "$VERBOSE" == "true" ]] || [[ "$JSON_OUTPUT" == "false" ]]; then
        echo -e "${GREEN}[SUCCESS]${NC} $message"
    fi
}

log_warning() {
    local message="$1"
    echo "[$TIMESTAMP] WARNING: $message" >> "$HEALTH_LOG"
    WARNINGS+=("$message")
    if [[ "$VERBOSE" == "true" ]] || [[ "$JSON_OUTPUT" == "false" ]]; then
        echo -e "${YELLOW}[WARNING]${NC} $message"
    fi
}

log_error() {
    local message="$1"
    echo "[$TIMESTAMP] ERROR: $message" >> "$HEALTH_LOG"
    ISSUES+=("$message")
    HEALTH_STATUS=1
    if [[ "$VERBOSE" == "true" ]] || [[ "$JSON_OUTPUT" == "false" ]]; then
        echo -e "${RED}[ERROR]${NC} $message"
    fi
}

# Health check functions
check_php_version() {
    log_info "Checking PHP version..."
    local php_version=$(php -r "echo PHP_VERSION;")
    local required_version="8.3"
    
    if php -r "exit(version_compare(PHP_VERSION, '$required_version', '<') ? 1 : 0);"; then
        log_success "PHP version: $php_version (>= $required_version required)"
    else
        log_error "PHP version $php_version is below required version $required_version"
    fi
}

check_php_extensions() {
    log_info "Checking required PHP extensions..."
    local required_extensions=("intl" "pdo_mysql" "redis" "gd" "zip" "mbstring" "openssl" "tokenizer" "xml" "ctype" "json" "bcmath")
    
    for ext in "${required_extensions[@]}"; do
        if php -m | grep -qi "^$ext$"; then
            log_success "PHP extension '$ext' is installed"
        else
            log_error "Required PHP extension '$ext' is missing"
        fi
    done
}

check_composer_dependencies() {
    log_info "Checking Composer dependencies..."
    cd "$PROJECT_DIR"
    
    if composer check-platform-reqs --no-dev > /dev/null 2>&1; then
        log_success "Composer platform requirements satisfied"
    else
        log_warning "Some Composer platform requirements not met"
    fi
    
    if composer validate --strict > /dev/null 2>&1; then
        log_success "Composer.json is valid"
    else
        log_warning "Composer.json validation issues found"
    fi
}

check_database_connection() {
    log_info "Checking database connection..."
    cd "$PROJECT_DIR"
    
    if php artisan db:show --counts > /dev/null 2>&1; then
        local table_count=$(php artisan db:show --counts | grep "Tables" | awk '{print $2}')
        log_success "Database connection successful ($table_count tables)"
        
        # Check database size
        local db_size=$(php artisan tinker --execute="echo DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = database()')[0]->size_mb;")
        log_info "Database size: ${db_size}MB"
        
        # Warn if database is getting large
        if (( $(echo "$db_size > 1000" | bc -l) )); then
            log_warning "Database size is quite large (${db_size}MB) - consider optimization"
        fi
    else
        log_error "Database connection failed"
    fi
}

check_redis_connection() {
    log_info "Checking Redis connection..."
    cd "$PROJECT_DIR"
    
    if php artisan tinker --execute="Redis::ping(); echo 'Redis connection successful';" > /dev/null 2>&1; then
        log_success "Redis connection successful"
        
        # Check Redis memory usage
        local redis_info=$(redis-cli info memory 2>/dev/null || echo "")
        if [[ -n "$redis_info" ]]; then
            local used_memory=$(echo "$redis_info" | grep "used_memory_human:" | cut -d: -f2 | tr -d '\r')
            local max_memory=$(echo "$redis_info" | grep "maxmemory_human:" | cut -d: -f2 | tr -d '\r')
            if [[ -n "$used_memory" ]]; then
                log_info "Redis memory usage: $used_memory"
            fi
        fi
    else
        log_error "Redis connection failed"
    fi
}

check_disk_space() {
    log_info "Checking disk space..."
    local disk_usage=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
    local available_space=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $4}')
    
    log_info "Disk usage: ${disk_usage}% (${available_space} available)"
    
    if [[ $disk_usage -gt 90 ]]; then
        log_error "Disk usage is critical (${disk_usage}%)"
    elif [[ $disk_usage -gt 80 ]]; then
        log_warning "Disk usage is high (${disk_usage}%)"
    else
        log_success "Disk usage is normal (${disk_usage}%)"
    fi
}

check_memory_usage() {
    log_info "Checking memory usage..."
    local memory_info=$(free -m)
    local used_memory=$(echo "$memory_info" | awk 'NR==2{printf "%.0f", $3}')
    local total_memory=$(echo "$memory_info" | awk 'NR==2{printf "%.0f", $2}')
    local memory_percentage=$(( (used_memory * 100) / total_memory ))
    
    log_info "Memory usage: ${memory_percentage}% (${used_memory}MB/${total_memory}MB)"
    
    if [[ $memory_percentage -gt 90 ]]; then
        log_error "Memory usage is critical (${memory_percentage}%)"
    elif [[ $memory_percentage -gt 80 ]]; then
        log_warning "Memory usage is high (${memory_percentage}%)"
    else
        log_success "Memory usage is normal (${memory_percentage}%)"
    fi
}

check_laravel_caches() {
    log_info "Checking Laravel cache status..."
    cd "$PROJECT_DIR"
    
    # Check if caches are enabled
    if php artisan about --only=cache | grep -q "CACHED"; then
        local cached_items=$(php artisan about --only=cache | grep "CACHED" | wc -l)
        log_success "Laravel caches are optimized ($cached_items items cached)"
    else
        log_warning "Laravel caches are not optimized - consider running optimization commands"
    fi
    
    # Check cache directory permissions
    local cache_dirs=("storage/framework/cache" "storage/framework/sessions" "storage/framework/views")
    for cache_dir in "${cache_dirs[@]}"; do
        if [[ -w "$PROJECT_DIR/$cache_dir" ]]; then
            log_success "Cache directory '$cache_dir' is writable"
        else
            log_error "Cache directory '$cache_dir' is not writable"
        fi
    done
}

check_log_files() {
    log_info "Checking log files..."
    local log_dir="$PROJECT_DIR/storage/logs"
    
    if [[ -d "$log_dir" ]]; then
        local log_size=$(du -sh "$log_dir" | cut -f1)
        log_info "Log directory size: $log_size"
        
        # Check for large log files
        local large_logs=$(find "$log_dir" -name "*.log" -size +100M 2>/dev/null || true)
        if [[ -n "$large_logs" ]]; then
            log_warning "Large log files detected (>100MB) - consider log rotation"
        else
            log_success "Log file sizes are reasonable"
        fi
        
        # Check recent error logs
        local recent_errors=$(find "$log_dir" -name "*.log" -mtime -1 -exec grep -l "ERROR\|CRITICAL\|EMERGENCY" {} \; 2>/dev/null || true)
        if [[ -n "$recent_errors" ]]; then
            log_warning "Recent error logs detected - review recommended"
        else
            log_success "No recent critical errors in logs"
        fi
    else
        log_error "Log directory not found or not accessible"
    fi
}

check_application_response() {
    log_info "Checking application response..."
    cd "$PROJECT_DIR"
    
    # Start temporary server for testing
    local test_port=8999
    php artisan serve --host=localhost --port=$test_port --no-reload &
    local server_pid=$!
    
    # Wait for server to start
    sleep 3
    
    # Test application response
    local response_time=$(curl -s -o /dev/null -w "%{time_total}" "http://localhost:$test_port" 2>/dev/null || echo "0")
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$test_port" 2>/dev/null || echo "000")
    
    # Kill test server
    kill $server_pid 2>/dev/null || true
    wait $server_pid 2>/dev/null || true
    
    if [[ "$http_code" == "200" ]]; then
        if (( $(echo "$response_time < 1.0" | bc -l) )); then
            log_success "Application response: ${response_time}s (HTTP $http_code)"
        elif (( $(echo "$response_time < 3.0" | bc -l) )); then
            log_warning "Application response slow: ${response_time}s (HTTP $http_code)"
        else
            log_error "Application response very slow: ${response_time}s (HTTP $http_code)"
        fi
    else
        log_error "Application not responding properly (HTTP $http_code)"
    fi
}

check_security_updates() {
    log_info "Checking for security updates..."
    
    # Check PHP security advisories (if available)
    if command -v composer &> /dev/null; then
        cd "$PROJECT_DIR"
        if composer audit --no-dev 2>/dev/null | grep -q "No security vulnerability advisories found"; then
            log_success "No known security vulnerabilities in dependencies"
        else
            log_warning "Potential security vulnerabilities detected - run 'composer audit' for details"
        fi
    fi
    
    # Check system security updates
    if command -v apt-get &> /dev/null; then
        local security_updates=$(apt list --upgradable 2>/dev/null | grep -c "security" || echo "0")
        if [[ $security_updates -gt 0 ]]; then
            log_warning "$security_updates security updates available"
        else
            log_success "System security updates are current"
        fi
    fi
}

generate_json_report() {
    local end_time=$(date '+%Y-%m-%d %H:%M:%S')
    local issues_json=$(printf '%s\n' "${ISSUES[@]}" | jq -R . | jq -s .)
    local warnings_json=$(printf '%s\n' "${WARNINGS[@]}" | jq -R . | jq -s .)
    local successes_json=$(printf '%s\n' "${SUCCESSES[@]}" | jq -R . | jq -s .)
    
    cat << EOF
{
    "timestamp": "$TIMESTAMP",
    "end_time": "$end_time",
    "status": $([ $HEALTH_STATUS -eq 0 ] && echo '"healthy"' || echo '"unhealthy"'),
    "exit_code": $HEALTH_STATUS,
    "summary": {
        "total_checks": $((${#ISSUES[@]} + ${#WARNINGS[@]} + ${#SUCCESSES[@]})),
        "errors": ${#ISSUES[@]},
        "warnings": ${#WARNINGS[@]},
        "successes": ${#SUCCESSES[@]}
    },
    "issues": $issues_json,
    "warnings": $warnings_json,
    "successes": $successes_json,
    "system_info": {
        "hostname": "$(hostname)",
        "php_version": "$(php -r 'echo PHP_VERSION;')",
        "laravel_version": "$(cd "$PROJECT_DIR" && php artisan --version | cut -d' ' -f3)",
        "disk_usage": "$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}')",
        "memory_usage": "$(free | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')"
    }
}
EOF
}

send_alert() {
    if [[ "$ENABLE_ALERTS" == "true" ]] && [[ $HEALTH_STATUS -ne 0 ]]; then
        local alert_message="HD Tickets Health Check Alert: ${#ISSUES[@]} errors and ${#WARNINGS[@]} warnings detected"
        
        # Log alert
        echo "[$TIMESTAMP] ALERT: $alert_message" >> "$HEALTH_LOG"
        
        # Here you could add integrations with:
        # - Slack webhook
        # - Discord webhook
        # - Email notification
        # - PagerDuty
        # - SMS service
        
        echo -e "${RED}[ALERT]${NC} $alert_message"
    fi
}

# Main execution
main() {
    if [[ "$JSON_OUTPUT" == "false" ]]; then
        echo -e "${BLUE}HD Tickets Health Check - $TIMESTAMP${NC}"
        echo "=================================================="
    fi
    
    # Ensure log directory exists
    mkdir -p "$LOG_DIR"
    
    # Run health checks
    check_php_version
    check_php_extensions
    check_composer_dependencies
    check_database_connection
    check_redis_connection
    check_disk_space
    check_memory_usage
    check_laravel_caches
    check_log_files
    check_application_response
    check_security_updates
    
    # Generate output
    if [[ "$JSON_OUTPUT" == "true" ]]; then
        generate_json_report
    else
        echo "=================================================="
        if [[ $HEALTH_STATUS -eq 0 ]]; then
            echo -e "${GREEN}✅ Overall Health Status: HEALTHY${NC}"
        else
            echo -e "${RED}❌ Overall Health Status: UNHEALTHY${NC}"
        fi
        
        echo "Summary: ${#SUCCESSES[@]} successes, ${#WARNINGS[@]} warnings, ${#ISSUES[@]} errors"
        
        if [[ ${#ISSUES[@]} -gt 0 ]]; then
            echo -e "\n${RED}Issues to resolve:${NC}"
            for issue in "${ISSUES[@]}"; do
                echo "  ❌ $issue"
            done
        fi
        
        if [[ ${#WARNINGS[@]} -gt 0 ]]; then
            echo -e "\n${YELLOW}Warnings to review:${NC}"
            for warning in "${WARNINGS[@]}"; do
                echo "  ⚠️  $warning"
            done
        fi
    fi
    
    # Send alerts if configured
    send_alert
    
    exit $HEALTH_STATUS
}

# Execute main function
main "$@"
