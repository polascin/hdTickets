#!/bin/bash

# HD Tickets - Maintenance Automation Script
# Description: Automates routine maintenance tasks for optimal performance
# Usage: ./scripts/maintenance.sh [task] [options]
# Author: HD Tickets DevOps Team
# Version: 1.0.0

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_DIR="$PROJECT_DIR/storage/logs"
MAINTENANCE_LOG="$LOG_DIR/maintenance.log"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

# Maintenance configuration
LOG_RETENTION_DAYS=30
CACHE_MAX_AGE_HOURS=24
SESSION_MAX_AGE_HOURS=48
TEMP_CLEANUP_DAYS=7
BACKUP_RETENTION_DAYS=30

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Logging functions
log_info() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] INFO: $message" >> "$MAINTENANCE_LOG"
    echo -e "${BLUE}[INFO]${NC} $message"
}

log_success() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] SUCCESS: $message" >> "$MAINTENANCE_LOG"
    echo -e "${GREEN}[SUCCESS]${NC} $message"
}

log_warning() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $message" >> "$MAINTENANCE_LOG"
    echo -e "${YELLOW}[WARNING]${NC} $message"
}

log_error() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $message" >> "$MAINTENANCE_LOG"
    echo -e "${RED}[ERROR]${NC} $message"
}

# Maintenance tasks
clear_laravel_caches() {
    log_info "Clearing Laravel caches..."
    cd "$PROJECT_DIR"
    
    local cleared_items=0
    
    # Clear application cache
    if php artisan cache:clear >/dev/null 2>&1; then
        log_success "Application cache cleared"
        cleared_items=$((cleared_items + 1))
    else
        log_warning "Failed to clear application cache"
    fi
    
    # Clear configuration cache
    if php artisan config:clear >/dev/null 2>&1; then
        log_success "Configuration cache cleared"
        cleared_items=$((cleared_items + 1))
    else
        log_warning "Failed to clear configuration cache"
    fi
    
    # Clear route cache
    if php artisan route:clear >/dev/null 2>&1; then
        log_success "Route cache cleared"
        cleared_items=$((cleared_items + 1))
    else
        log_warning "Failed to clear route cache"
    fi
    
    # Clear view cache
    if php artisan view:clear >/dev/null 2>&1; then
        log_success "View cache cleared"
        cleared_items=$((cleared_items + 1))
    else
        log_warning "Failed to clear view cache"
    fi
    
    # Clear compiled views
    if php artisan view:cache >/dev/null 2>&1; then
        log_success "Views recompiled"
        cleared_items=$((cleared_items + 1))
    else
        log_warning "Failed to recompile views"
    fi
    
    log_success "Cache clearing completed ($cleared_items operations)"
}

optimize_laravel_caches() {
    log_info "Optimizing Laravel caches for production..."
    cd "$PROJECT_DIR"
    
    local optimized_items=0
    
    # Cache configuration
    if php artisan config:cache >/dev/null 2>&1; then
        log_success "Configuration cached"
        optimized_items=$((optimized_items + 1))
    else
        log_warning "Failed to cache configuration"
    fi
    
    # Cache routes
    if php artisan route:cache >/dev/null 2>&1; then
        log_success "Routes cached"
        optimized_items=$((optimized_items + 1))
    else
        log_warning "Failed to cache routes"
    fi
    
    # Cache views
    if php artisan view:cache >/dev/null 2>&1; then
        log_success "Views cached"
        optimized_items=$((optimized_items + 1))
    else
        log_warning "Failed to cache views"
    fi
    
    # Optimize autoloader
    if composer dump-autoload --optimize --no-dev >/dev/null 2>&1; then
        log_success "Autoloader optimized"
        optimized_items=$((optimized_items + 1))
    else
        log_warning "Failed to optimize autoloader"
    fi
    
    log_success "Cache optimization completed ($optimized_items operations)"
}

rotate_logs() {
    log_info "Rotating log files..."
    
    local rotated_count=0
    local total_size_saved=0
    
    # Find old log files
    local log_files=($(find "$LOG_DIR" -name "*.log" -type f -mtime +$LOG_RETENTION_DAYS 2>/dev/null || true))
    
    for log_file in "${log_files[@]}"; do
        if [[ -f "$log_file" ]]; then
            local file_size=$(stat -c%s "$log_file" 2>/dev/null || echo "0")
            
            # Compress old log file
            gzip "$log_file" 2>/dev/null || continue
            
            rotated_count=$((rotated_count + 1))
            total_size_saved=$((total_size_saved + file_size))
            
            log_info "Compressed log: $(basename "$log_file")"
        fi
    done
    
    # Remove very old compressed logs
    local old_compressed_logs=($(find "$LOG_DIR" -name "*.log.gz" -type f -mtime +$((LOG_RETENTION_DAYS * 2)) 2>/dev/null || true))
    local deleted_count=0
    
    for compressed_log in "${old_compressed_logs[@]}"; do
        if [[ -f "$compressed_log" ]]; then
            rm "$compressed_log"
            deleted_count=$((deleted_count + 1))
            log_info "Deleted old compressed log: $(basename "$compressed_log")"
        fi
    done
    
    # Clean up Laravel daily logs older than retention period
    local laravel_logs=($(find "$LOG_DIR" -name "laravel-*.log" -type f -mtime +$LOG_RETENTION_DAYS 2>/dev/null || true))
    local laravel_deleted=0
    
    for laravel_log in "${laravel_logs[@]}"; do
        if [[ -f "$laravel_log" ]]; then
            rm "$laravel_log"
            laravel_deleted=$((laravel_deleted + 1))
            log_info "Deleted old Laravel log: $(basename "$laravel_log")"
        fi
    done
    
    local size_mb=$(echo "$total_size_saved / 1024 / 1024" | bc -l 2>/dev/null | xargs printf "%.2f" || echo "0")
    log_success "Log rotation completed: $rotated_count compressed, $deleted_count deleted, $laravel_deleted Laravel logs removed (${size_mb}MB saved)"
}

clean_temporary_files() {
    log_info "Cleaning temporary files..."
    
    local cleaned_count=0
    local total_size_cleaned=0
    
    # Clean Laravel temporary files
    local temp_dirs=(
        "$PROJECT_DIR/storage/framework/cache/data"
        "$PROJECT_DIR/storage/framework/sessions"
        "$PROJECT_DIR/storage/framework/testing"
        "$PROJECT_DIR/storage/app/temp"
        "/tmp/laravel*"
    )
    
    for temp_dir in "${temp_dirs[@]}"; do
        if [[ -d "$temp_dir" ]]; then
            local old_files=($(find "$temp_dir" -type f -mtime +$TEMP_CLEANUP_DAYS 2>/dev/null || true))
            
            for old_file in "${old_files[@]}"; do
                if [[ -f "$old_file" ]]; then
                    local file_size=$(stat -c%s "$old_file" 2>/dev/null || echo "0")
                    rm "$old_file" 2>/dev/null || continue
                    
                    cleaned_count=$((cleaned_count + 1))
                    total_size_cleaned=$((total_size_cleaned + file_size))
                fi
            done
        fi
    done
    
    # Clean system temporary files related to our application
    local app_temp_files=($(find /tmp -name "*hdtickets*" -o -name "*laravel*" -type f -mtime +1 2>/dev/null || true))
    
    for temp_file in "${app_temp_files[@]}"; do
        if [[ -f "$temp_file" ]]; then
            local file_size=$(stat -c%s "$temp_file" 2>/dev/null || echo "0")
            rm "$temp_file" 2>/dev/null || continue
            
            cleaned_count=$((cleaned_count + 1))
            total_size_cleaned=$((total_size_cleaned + file_size))
        fi
    done
    
    # Clean empty directories
    find "$PROJECT_DIR/storage/framework/cache" -type d -empty -delete 2>/dev/null || true
    find "$PROJECT_DIR/storage/logs" -type d -empty -delete 2>/dev/null || true
    
    local size_mb=$(echo "$total_size_cleaned / 1024 / 1024" | bc -l 2>/dev/null | xargs printf "%.2f" || echo "0")
    log_success "Temporary cleanup completed: $cleaned_count files removed (${size_mb}MB freed)"
}

update_dependencies() {
    log_info "Checking and updating dependencies..."
    cd "$PROJECT_DIR"
    
    local updates_available=false
    
    # Check for Composer updates
    log_info "Checking Composer dependencies..."
    if composer outdated --direct --strict 2>/dev/null | grep -q "^"; then
        log_warning "Composer dependencies are outdated"
        updates_available=true
        
        # Show what needs updating
        composer outdated --direct --strict 2>/dev/null | head -10 | while read -r line; do
            log_info "Outdated: $line"
        done
    else
        log_success "Composer dependencies are up to date"
    fi
    
    # Check for npm updates
    if [[ -f "package.json" ]]; then
        log_info "Checking npm dependencies..."
        if npm outdated 2>/dev/null | grep -q "^"; then
            log_warning "npm dependencies are outdated"
            updates_available=true
        else
            log_success "npm dependencies are up to date"
        fi
    fi
    
    # Security audit
    log_info "Running security audit..."
    if composer audit --no-dev 2>/dev/null | grep -q "No security vulnerability advisories found"; then
        log_success "No security vulnerabilities found in Composer dependencies"
    else
        log_warning "Security vulnerabilities detected - review recommended"
        updates_available=true
    fi
    
    if [[ -f "package.json" ]]; then
        if npm audit --audit-level=moderate 2>/dev/null | grep -q "found 0 vulnerabilities"; then
            log_success "No security vulnerabilities found in npm dependencies"
        else
            log_warning "npm security vulnerabilities detected - review recommended"
            updates_available=true
        fi
    fi
    
    if $updates_available; then
        log_warning "Updates are available - manual review recommended"
    else
        log_success "All dependencies are current and secure"
    fi
}

database_maintenance() {
    log_info "Running database maintenance..."
    cd "$PROJECT_DIR"
    
    # Run database optimization
    if php artisan db:show >/dev/null 2>&1; then
        log_success "Database connection verified"
        
        # Get database size before optimization
        local db_size_before=$(php artisan tinker --execute="echo DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = database()')[0]->size_mb;" 2>/dev/null || echo "0")
        
        # Clean up expired sessions
        if php artisan session:gc >/dev/null 2>&1; then
            log_success "Session garbage collection completed"
        else
            log_info "Session garbage collection not available or failed"
        fi
        
        # Optimize database tables (MySQL/MariaDB)
        local optimized_tables=0
        while IFS= read -r table; do
            if [[ -n "$table" ]]; then
                if php artisan tinker --execute="DB::statement('OPTIMIZE TABLE \`$table\`');" >/dev/null 2>&1; then
                    optimized_tables=$((optimized_tables + 1))
                fi
            fi
        done < <(php artisan tinker --execute="DB::select('SHOW TABLES')->each(function(\$t) { echo array_values((array)\$t)[0] . PHP_EOL; });" 2>/dev/null || true)
        
        if [[ $optimized_tables -gt 0 ]]; then
            log_success "Optimized $optimized_tables database tables"
        fi
        
        # Get database size after optimization
        local db_size_after=$(php artisan tinker --execute="echo DB::select('SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = database()')[0]->size_mb;" 2>/dev/null || echo "0")
        
        log_info "Database size: ${db_size_before}MB → ${db_size_after}MB"
    else
        log_error "Database connection failed"
    fi
}

system_health_check() {
    log_info "Running system health check..."
    
    # Check disk space
    local disk_usage=$(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}' | sed 's/%//')
    if [[ $disk_usage -gt 90 ]]; then
        log_error "Disk usage critical: ${disk_usage}%"
    elif [[ $disk_usage -gt 80 ]]; then
        log_warning "Disk usage high: ${disk_usage}%"
    else
        log_success "Disk usage normal: ${disk_usage}%"
    fi
    
    # Check memory usage
    local memory_usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2 }')
    if [[ $memory_usage -gt 90 ]]; then
        log_error "Memory usage critical: ${memory_usage}%"
    elif [[ $memory_usage -gt 80 ]]; then
        log_warning "Memory usage high: ${memory_usage}%"
    else
        log_success "Memory usage normal: ${memory_usage}%"
    fi
    
    # Check load average
    local load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
    local cpu_cores=$(nproc)
    local load_threshold=$(echo "$cpu_cores * 2" | bc)
    
    if (( $(echo "$load_avg > $load_threshold" | bc -l) )); then
        log_warning "System load high: $load_avg (${cpu_cores} cores)"
    else
        log_success "System load normal: $load_avg"
    fi
    
    # Check application response
    cd "$PROJECT_DIR"
    local test_port=9002
    php artisan serve --host=localhost --port=$test_port --no-reload &
    local server_pid=$!
    
    sleep 2
    
    local response_time=$(curl -s -o /dev/null -w "%{time_total}" "http://localhost:$test_port" 2>/dev/null || echo "999")
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$test_port" 2>/dev/null || echo "000")
    
    kill $server_pid 2>/dev/null || true
    wait $server_pid 2>/dev/null || true
    
    if [[ "$http_code" == "200" ]] && (( $(echo "$response_time < 2.0" | bc -l) )); then
        log_success "Application response: ${response_time}s (HTTP $http_code)"
    else
        log_warning "Application response slow or failed: ${response_time}s (HTTP $http_code)"
    fi
}

security_check() {
    log_info "Running security checks..."
    cd "$PROJECT_DIR"
    
    # Check file permissions
    local permission_issues=0
    
    # Check .env file permissions
    if [[ -f ".env" ]]; then
        local env_perms=$(stat -c "%a" .env)
        if [[ "$env_perms" != "600" ]]; then
            chmod 600 .env
            log_warning "Fixed .env file permissions ($env_perms → 600)"
            permission_issues=$((permission_issues + 1))
        else
            log_success ".env file permissions are secure (600)"
        fi
    fi
    
    # Check storage directory permissions
    if [[ -d "storage" ]]; then
        local storage_dirs=("storage/logs" "storage/framework" "storage/app")
        for dir in "${storage_dirs[@]}"; do
            if [[ -d "$dir" ]] && [[ ! -w "$dir" ]]; then
                chmod -R 755 "$dir"
                log_warning "Fixed permissions for $dir"
                permission_issues=$((permission_issues + 1))
            fi
        done
        
        if [[ $permission_issues -eq 0 ]]; then
            log_success "Storage directory permissions are correct"
        fi
    fi
    
    # Check for suspicious files
    local suspicious_files=($(find "$PROJECT_DIR" -name "*.php" -type f -exec grep -l "eval\|base64_decode\|shell_exec" {} \; 2>/dev/null | head -5 || true))
    
    if [[ ${#suspicious_files[@]} -gt 0 ]]; then
        log_warning "Found ${#suspicious_files[@]} files with potentially suspicious code - review recommended"
        for file in "${suspicious_files[@]}"; do
            log_info "Suspicious file: $(basename "$file")"
        done
    else
        log_success "No suspicious PHP files detected"
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

generate_maintenance_report() {
    log_info "Generating maintenance report..."
    
    local report_file="$LOG_DIR/maintenance-report-$TIMESTAMP.txt"
    
    cat > "$report_file" << EOF
HD Tickets - Maintenance Report
Generated: $(date '+%Y-%m-%d %H:%M:%S')
========================================

System Information:
- Hostname: $(hostname)
- PHP Version: $(php -r 'echo PHP_VERSION;')
- Laravel Version: $(cd "$PROJECT_DIR" && php artisan --version | cut -d' ' -f3)
- Disk Usage: $(df -h "$PROJECT_DIR" | awk 'NR==2 {print $5}')
- Memory Usage: $(free | awk 'NR==2{printf "%.1f%%", $3*100/$2 }')
- Load Average: $(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')

Maintenance Tasks Completed:
$(tail -20 "$MAINTENANCE_LOG" | grep "SUCCESS\|ERROR\|WARNING")

Next Recommended Actions:
- Review security updates if any were reported
- Monitor system resources over the next 24 hours
- Check application logs for any issues
- Verify backup integrity

Report Location: $report_file
Log Location: $MAINTENANCE_LOG
========================================
EOF
    
    log_success "Maintenance report generated: $(basename "$report_file")"
    echo "$report_file"
}

run_full_maintenance() {
    log_info "Starting full maintenance routine..."
    
    echo -e "${CYAN}HD Tickets - Full Maintenance Mode${NC}"
    echo "======================================"
    echo "Starting comprehensive maintenance..."
    echo ""
    
    # Run all maintenance tasks
    clear_laravel_caches
    echo ""
    
    rotate_logs
    echo ""
    
    clean_temporary_files
    echo ""
    
    database_maintenance
    echo ""
    
    system_health_check
    echo ""
    
    security_check
    echo ""
    
    update_dependencies
    echo ""
    
    optimize_laravel_caches
    echo ""
    
    # Generate report
    local report_file=$(generate_maintenance_report)
    
    echo ""
    log_success "Full maintenance completed!"
    echo -e "${GREEN}Maintenance report: $(basename "$report_file")${NC}"
    echo ""
}

show_usage() {
    cat << EOF
HD Tickets Maintenance Script

Usage: $0 <task> [options]

Tasks:
    full                    Run all maintenance tasks
    clear-cache            Clear Laravel application caches
    optimize-cache         Optimize Laravel caches for production
    rotate-logs            Rotate and compress old log files
    clean-temp             Clean temporary files and directories
    database-maintenance   Optimize database tables and cleanup
    security-check         Run security checks and fixes
    health-check           System health and performance check
    update-check           Check for dependency updates
    report                 Generate maintenance report only

Options:
    --log-retention=N      Set log retention days (default: $LOG_RETENTION_DAYS)
    --temp-cleanup=N       Set temp file cleanup days (default: $TEMP_CLEANUP_DAYS)
    --quiet               Suppress non-essential output
    
Examples:
    $0 full                        # Run complete maintenance
    $0 clear-cache                 # Clear only caches
    $0 rotate-logs --log-retention=14  # Rotate logs with 14-day retention
    $0 security-check             # Run only security checks

EOF
}

# Main execution
main() {
    # Ensure log directory exists
    mkdir -p "$LOG_DIR"
    
    # Parse command line arguments
    local task="${1:-}"
    shift || true
    
    # Parse options
    while [[ $# -gt 0 ]]; do
        case $1 in
            --log-retention=*)
                LOG_RETENTION_DAYS="${1#*=}"
                shift
                ;;
            --temp-cleanup=*)
                TEMP_CLEANUP_DAYS="${1#*=}"
                shift
                ;;
            --quiet)
                # Implement quiet mode if needed
                shift
                ;;
            --help|-h)
                show_usage
                exit 0
                ;;
            -*)
                log_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
            *)
                break
                ;;
        esac
    done
    
    case "$task" in
        "full")
            run_full_maintenance
            ;;
        "clear-cache")
            clear_laravel_caches
            ;;
        "optimize-cache")
            optimize_laravel_caches
            ;;
        "rotate-logs")
            rotate_logs
            ;;
        "clean-temp")
            clean_temporary_files
            ;;
        "database-maintenance")
            database_maintenance
            ;;
        "security-check")
            security_check
            ;;
        "health-check")
            system_health_check
            ;;
        "update-check")
            update_dependencies
            ;;
        "report")
            generate_maintenance_report
            ;;
        "")
            log_error "Task required"
            show_usage
            exit 1
            ;;
        *)
            log_error "Unknown task: $task"
            show_usage
            exit 1
            ;;
    esac
}

# Execute main function
main "$@"
