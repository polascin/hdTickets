#!/bin/bash

# HD Tickets Blue-Green Deployment Script
# Sports Events Entry Tickets Monitoring System
# Ubuntu 24.04 LTS with Apache2, PHP8.4, and MySQL/MariaDB 10.4

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="/var/www/hdtickets"
BACKUP_DIR="/var/backups/hdtickets"
LOG_DIR="/var/log/hdtickets"
DATE=$(date +%Y%m%d_%H%M%S)

# Environment settings
BLUE_PORT=8080
GREEN_PORT=9080
NGINX_CONF="/etc/nginx/sites-available/hdtickets-load-balancer"
MAINTENANCE_PAGE_PORT=8090

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging functions
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "${LOG_DIR}/deployment_${DATE}.log"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] SUCCESS:${NC} $1" | tee -a "${LOG_DIR}/deployment_${DATE}.log"
}

log_warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')] WARNING:${NC} $1" | tee -a "${LOG_DIR}/deployment_${DATE}.log"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')] ERROR:${NC} $1" | tee -a "${LOG_DIR}/deployment_${DATE}.log"
}

# Initialize directories
initialize_deployment_structure() {
    log "Initializing deployment structure..."
    
    mkdir -p "${LOG_DIR}"
    mkdir -p "${BACKUP_DIR}"
    mkdir -p "${APP_DIR}/deployment/maintenance"
    mkdir -p "${APP_DIR}/deployment/environments/blue"
    mkdir -p "${APP_DIR}/deployment/environments/green"
    
    log_success "Deployment structure initialized"
}

# Health check function
check_health() {
    local environment=$1
    local port=$2
    local max_attempts=30
    local attempt=1
    
    log "Checking health for ${environment} environment on port ${port}..."
    
    while [ $attempt -le $max_attempts ]; do
        if curl -s -f "http://127.0.0.1:${port}/health" > /dev/null 2>&1; then
            log_success "${environment} environment is healthy (attempt ${attempt}/${max_attempts})"
            return 0
        fi
        
        log "Health check attempt ${attempt}/${max_attempts} failed for ${environment}..."
        sleep 2
        ((attempt++))
    done
    
    log_error "${environment} environment failed health checks after ${max_attempts} attempts"
    return 1
}

# Get current active environment
get_active_environment() {
    if grep -q "127.0.0.1:${BLUE_PORT}" /etc/nginx/sites-available/hdtickets-load-balancer 2>/dev/null; then
        echo "blue"
    elif grep -q "127.0.0.1:${GREEN_PORT}" /etc/nginx/sites-available/hdtickets-load-balancer 2>/dev/null; then
        echo "green"
    else
        echo "blue" # Default to blue
    fi
}

# Get inactive environment
get_inactive_environment() {
    local active=$(get_active_environment)
    if [ "$active" = "blue" ]; then
        echo "green"
    else
        echo "blue"
    fi
}

# Database backup
backup_database() {
    log "Creating database backup..."
    
    local backup_file="${BACKUP_DIR}/hdtickets_${DATE}.sql"
    
    # Create backup with compression
    mysqldump \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --add-drop-table \
        --add-locks \
        --create-options \
        --disable-keys \
        --extended-insert \
        --quick \
        --set-charset \
        hdtickets | gzip > "${backup_file}.gz"
    
    log_success "Database backup created: ${backup_file}.gz"
    
    # Keep only last 10 backups
    cd "${BACKUP_DIR}"
    ls -t hdtickets_*.sql.gz 2>/dev/null | tail -n +11 | xargs -r rm -f
}

# Application backup
backup_application() {
    log "Creating application backup..."
    
    local backup_file="${BACKUP_DIR}/hdtickets_app_${DATE}.tar.gz"
    
    cd "${APP_DIR}"
    tar --exclude='node_modules' \
        --exclude='vendor' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        -czf "${backup_file}" . 2>/dev/null || {
        log_error "Failed to create application backup"
        return 1
    }
    
    log_success "Application backup created: ${backup_file}"
    
    # Keep only last 5 application backups
    cd "${BACKUP_DIR}"
    ls -t hdtickets_app_*.tar.gz 2>/dev/null | tail -n +6 | xargs -r rm -f
}

# Deploy to inactive environment
deploy_to_inactive() {
    local inactive_env=$(get_inactive_environment)
    local inactive_port
    
    if [ "$inactive_env" = "blue" ]; then
        inactive_port=$BLUE_PORT
    else
        inactive_port=$GREEN_PORT
    fi
    
    log "Deploying to inactive environment: ${inactive_env} (port ${inactive_port})"
    
    # Create environment-specific directory
    local env_dir="${APP_DIR}/deployment/environments/${inactive_env}"
    
    # Copy application files
    log "Copying application files to ${inactive_env} environment..."
    rsync -av --delete \
        --exclude='storage/logs/' \
        --exclude='storage/framework/cache/' \
        --exclude='storage/framework/sessions/' \
        --exclude='storage/framework/views/' \
        --exclude='node_modules/' \
        --exclude='deployment/' \
        "${APP_DIR}/" "${env_dir}/"
    
    # Install/update dependencies
    log "Installing dependencies in ${inactive_env} environment..."
    cd "${env_dir}"
    
    # Composer install
    composer install --no-dev --optimize-autoloader --no-interaction
    
    # Verify Node.js version requirement
    if ! node --version | grep -q "v22.18.0"; then
        log_error "Node.js v22.18.0 required. Current: $(node --version)"
        log "Use 'nvm use 22.18.0' to switch to required version"
        return 1
    fi
    
    # NPM install and build
    if [ -f "package.json" ]; then
        log "Installing NPM dependencies (Node.js v22.18.0)..."
        npm ci --only=production
        
        log "Building production assets with Vite 7..."
        npm run build:production
    fi
    
    # Set permissions
    chown -R www-data:www-data "${env_dir}"
    chmod -R 755 "${env_dir}"
    chmod -R 775 "${env_dir}/storage"
    chmod -R 775 "${env_dir}/bootstrap/cache"
    
    # Configure environment
    cp "${APP_DIR}/.env.${inactive_env}" "${env_dir}/.env" 2>/dev/null || {
        cp "${APP_DIR}/.env" "${env_dir}/.env"
    }
    
    # Clear caches
    cd "${env_dir}"
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    
    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    log_success "Deployment to ${inactive_env} environment completed"
}

# Run database migrations
run_migrations() {
    local inactive_env=$(get_inactive_environment)
    local env_dir="${APP_DIR}/deployment/environments/${inactive_env}"
    
    log "Running database migrations in ${inactive_env} environment..."
    
    cd "${env_dir}"
    php artisan migrate --force
    
    log_success "Database migrations completed"
}

# Start inactive environment
start_inactive_environment() {
    local inactive_env=$(get_inactive_environment)
    local inactive_port
    
    if [ "$inactive_env" = "blue" ]; then
        inactive_port=$BLUE_PORT
    else
        inactive_port=$GREEN_PORT
    fi
    
    log "Starting ${inactive_env} environment on port ${inactive_port}..."
    
    # Kill existing processes on the port
    pkill -f ":${inactive_port}" || true
    sleep 2
    
    # Start PHP built-in server (for testing) or configure Apache virtual host
    local env_dir="${APP_DIR}/deployment/environments/${inactive_env}"
    cd "${env_dir}"
    
    # Create Apache virtual host configuration
    cat > "/etc/apache2/sites-available/hdtickets-${inactive_env}.conf" << EOF
<VirtualHost *:${inactive_port}>
    DocumentRoot ${env_dir}/public
    ServerName hdtickets-${inactive_env}.local
    
    <Directory ${env_dir}/public>
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
        
        # Enable PHP 8.4
        <FilesMatch \.php$>
            SetHandler application/x-httpd-php
        </FilesMatch>
    </Directory>
    
    # Logging
    ErrorLog \${APACHE_LOG_DIR}/hdtickets_${inactive_env}_error.log
    CustomLog \${APACHE_LOG_DIR}/hdtickets_${inactive_env}_access.log combined
</VirtualHost>

Listen ${inactive_port}
EOF
    
    # Enable site and restart Apache
    a2ensite "hdtickets-${inactive_env}"
    systemctl reload apache2
    
    # Wait for service to start
    sleep 5
    
    log_success "${inactive_env} environment started on port ${inactive_port}"
}

# Switch traffic
switch_traffic() {
    local inactive_env=$(get_inactive_environment)
    local active_env=$(get_active_environment)
    local new_port
    
    if [ "$inactive_env" = "blue" ]; then
        new_port=$BLUE_PORT
    else
        new_port=$GREEN_PORT
    fi
    
    log "Switching traffic from ${active_env} to ${inactive_env} (port ${new_port})..."
    
    # Update nginx configuration
    sed -i "s/server 127.0.0.1:[0-9]*;/server 127.0.0.1:${new_port};/" "${NGINX_CONF}"
    
    # Test nginx configuration
    if ! nginx -t; then
        log_error "Nginx configuration test failed"
        return 1
    fi
    
    # Reload nginx
    systemctl reload nginx
    
    log_success "Traffic switched to ${inactive_env} environment"
}

# Rollback function
rollback() {
    local active_env=$(get_active_environment)
    local inactive_env=$(get_inactive_environment)
    
    log_warning "Rolling back deployment..."
    
    # Switch back to previous environment
    switch_traffic
    
    # Stop the failed environment
    local failed_port
    if [ "$active_env" = "blue" ]; then
        failed_port=$BLUE_PORT
    else
        failed_port=$GREEN_PORT
    fi
    
    pkill -f ":${failed_port}" || true
    a2dissite "hdtickets-${active_env}" || true
    systemctl reload apache2
    
    log_success "Rollback completed"
}

# Cleanup old environment
cleanup_old_environment() {
    local old_env=$(get_inactive_environment)
    local old_port
    
    if [ "$old_env" = "blue" ]; then
        old_port=$BLUE_PORT
    else
        old_port=$GREEN_PORT
    fi
    
    log "Cleaning up old ${old_env} environment..."
    
    # Stop old environment
    pkill -f ":${old_port}" || true
    a2dissite "hdtickets-${old_env}" || true
    systemctl reload apache2
    
    # Clean up old environment directory
    rm -rf "${APP_DIR}/deployment/environments/${old_env:?}"/*
    
    log_success "Old ${old_env} environment cleaned up"
}

# Main deployment process
main() {
    local skip_tests=${1:-false}
    
    log "Starting blue-green deployment for HD Tickets Sports Events Monitoring System"
    log "Active environment: $(get_active_environment)"
    log "Target environment: $(get_inactive_environment)"
    
    # Initialize
    initialize_deployment_structure
    
    # Pre-deployment checks
    log "Running pre-deployment checks..."
    if ! systemctl is-active --quiet nginx; then
        log_error "Nginx is not running"
        exit 1
    fi
    
    if ! systemctl is-active --quiet apache2; then
        log_error "Apache2 is not running"
        exit 1
    fi
    
    if ! systemctl is-active --quiet mysql; then
        log_error "MySQL/MariaDB is not running"
        exit 1
    fi
    
    # Create backups
    backup_database
    backup_application
    
    # Deploy to inactive environment
    deploy_to_inactive
    
    # Run database migrations
    run_migrations
    
    # Start inactive environment
    start_inactive_environment
    
    # Health check
    local inactive_env=$(get_inactive_environment)
    local inactive_port
    if [ "$inactive_env" = "blue" ]; then
        inactive_port=$BLUE_PORT
    else
        inactive_port=$GREEN_PORT
    fi
    
    if ! check_health "$inactive_env" "$inactive_port"; then
        log_error "Health check failed for ${inactive_env} environment"
        rollback
        exit 1
    fi
    
    # Run smoke tests
    if [ "$skip_tests" != "true" ]; then
        log "Running smoke tests..."
        cd "${APP_DIR}/deployment/environments/${inactive_env}"
        if ! php artisan test --testsuite=Smoke --stop-on-failure; then
            log_error "Smoke tests failed"
            rollback
            exit 1
        fi
        log_success "Smoke tests passed"
    fi
    
    # Switch traffic
    switch_traffic
    
    # Final health check
    sleep 10
    if ! check_health "$inactive_env" "$inactive_port"; then
        log_error "Final health check failed"
        rollback
        exit 1
    fi
    
    # Cleanup old environment
    cleanup_old_environment
    
    log_success "Blue-green deployment completed successfully!"
    log "New active environment: $(get_active_environment)"
}

# Script execution
case "${1:-deploy}" in
    "deploy")
        main "${2:-false}"
        ;;
    "rollback")
        rollback
        ;;
    "status")
        echo "Active environment: $(get_active_environment)"
        echo "Inactive environment: $(get_inactive_environment)"
        ;;
    "health")
        active_env=$(get_active_environment)
        if [ "$active_env" = "blue" ]; then
            check_health "blue" $BLUE_PORT
        else
            check_health "green" $GREEN_PORT
        fi
        ;;
    *)
        echo "Usage: $0 {deploy|rollback|status|health} [skip_tests]"
        echo "  deploy [skip_tests] - Deploy to inactive environment and switch"
        echo "  rollback           - Rollback to previous environment"  
        echo "  status             - Show current environment status"
        echo "  health             - Check active environment health"
        exit 1
        ;;
esac
