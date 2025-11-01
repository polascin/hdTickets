#!/bin/bash
# HD Tickets Simple Deployment Script
# For git-based deployments on local/development servers

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
PROJECT_DIR="/var/www/hdtickets"
PHP_FPM_SERVICE="php8.3-fpm"
HORIZON_SERVICE="horizon"

# Functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}==>${NC} $1"
}

check_requirements() {
    log_step "Checking requirements..."
    
    # Check if we're in the right directory
    if [ ! -f "$PROJECT_DIR/artisan" ]; then
        log_error "Not in Laravel project directory"
        exit 1
    fi
    
    # Check git
    if ! command -v git &> /dev/null; then
        log_error "git is not installed"
        exit 1
    fi
    
    # Check composer
    if ! command -v composer &> /dev/null; then
        log_error "composer is not installed"
        exit 1
    fi
    
    # Check npm
    if ! command -v npm &> /dev/null; then
        log_error "npm is not installed"
        exit 1
    fi
    
    log_info "All requirements met"
}

backup_database() {
    log_step "Creating database backup..."
    
    local timestamp=$(date +%Y%m%d-%H%M%S)
    local backup_dir="$PROJECT_DIR/backups"
    
    mkdir -p "$backup_dir"
    
    if mysqldump --single-transaction hdtickets 2>/dev/null | gzip > "$backup_dir/pre-deploy-$timestamp.sql.gz"; then
        log_info "Database backup created: pre-deploy-$timestamp.sql.gz"
        
        # Keep only last 10 backups
        cd "$backup_dir" && ls -t pre-deploy-*.sql.gz | tail -n +11 | xargs -r rm --
    else
        log_warn "Database backup failed (continuing anyway)"
    fi
}

pull_latest_code() {
    log_step "Pulling latest code from git..."
    
    cd "$PROJECT_DIR"
    
    # Show current branch and commit
    local current_branch=$(git branch --show-current)
    local current_commit=$(git rev-parse --short HEAD)
    log_info "Current: $current_branch @ $current_commit"
    
    # Pull latest changes
    if git pull origin "$current_branch"; then
        local new_commit=$(git rev-parse --short HEAD)
        log_info "Updated to: $current_branch @ $new_commit"
    else
        log_error "Git pull failed"
        exit 1
    fi
}

install_dependencies() {
    log_step "Installing/updating dependencies..."
    
    cd "$PROJECT_DIR"
    
    # Composer dependencies (without dev)
    log_info "Running composer install..."
    if composer install --no-dev --optimize-autoloader --no-interaction; then
        log_info "Composer dependencies installed"
    else
        log_error "Composer install failed"
        exit 1
    fi
}

build_frontend() {
    log_step "Building frontend assets..."
    
    cd "$PROJECT_DIR"
    
    # Install npm dependencies
    log_info "Running npm ci..."
    if npm ci --silent; then
        log_info "NPM dependencies installed"
    else
        log_error "npm ci failed"
        exit 1
    fi
    
    # Build assets
    log_info "Running npm run build..."
    if npm run build; then
        log_info "Frontend assets built successfully"
    else
        log_error "npm run build failed"
        exit 1
    fi
}

run_migrations() {
    log_step "Running database migrations..."
    
    cd "$PROJECT_DIR"
    
    # Check if there are pending migrations
    if php artisan migrate:status 2>/dev/null | grep -q "Pending"; then
        log_info "Pending migrations found, running..."
        if php artisan migrate --force; then
            log_info "Migrations completed"
        else
            log_error "Migration failed"
            exit 1
        fi
    else
        log_info "No pending migrations"
    fi
}

optimize_application() {
    log_step "Optimizing application..."
    
    cd "$PROJECT_DIR"
    
    # Clear all caches
    php artisan optimize:clear > /dev/null 2>&1
    
    # Rebuild caches
    php artisan config:cache
    php artisan event:cache
    php artisan view:cache
    
    # Note: route:cache skipped due to closure routes
    
    log_info "Application caches optimized"
}

restart_services() {
    log_step "Restarting services..."
    
    # Reload PHP-FPM
    if sudo systemctl reload "$PHP_FPM_SERVICE"; then
        log_info "PHP-FPM reloaded"
    else
        log_warn "Failed to reload PHP-FPM"
    fi
    
    # Restart Horizon
    if sudo systemctl restart "$HORIZON_SERVICE"; then
        log_info "Horizon restarted"
        sleep 2
        
        # Verify Horizon is running
        if sudo systemctl is-active "$HORIZON_SERVICE" > /dev/null; then
            log_info "Horizon is running"
        else
            log_warn "Horizon failed to start"
        fi
    else
        log_warn "Failed to restart Horizon"
    fi
}

verify_deployment() {
    log_step "Verifying deployment..."
    
    # Check if application is responding
    local status_code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 https://hdtickets.local 2>/dev/null || echo "000")
    
    if [ "$status_code" = "200" ] || [ "$status_code" = "302" ]; then
        log_info "Application is responding (HTTP $status_code)"
    else
        log_warn "Application returned HTTP $status_code"
    fi
    
    # Check for recent errors in log
    if [ -f "$PROJECT_DIR/storage/logs/laravel.log" ]; then
        local error_count=$(grep -c "ERROR" "$PROJECT_DIR/storage/logs/laravel.log" 2>/dev/null | tail -1 || echo "0")
        if [ "$error_count" -gt 0 ]; then
            log_warn "Found $error_count errors in log (check storage/logs/laravel.log)"
        fi
    fi
}

# Main deployment flow
main() {
    echo ""
    log_info "HD Tickets Deployment Started"
    log_info "Time: $(date '+%Y-%m-%d %H:%M:%S')"
    echo ""
    
    check_requirements
    
    # Ask for confirmation
    read -p "Continue with deployment? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_warn "Deployment cancelled"
        exit 0
    fi
    
    # Backup before changes
    backup_database
    
    # Deployment steps
    pull_latest_code
    install_dependencies
    build_frontend
    run_migrations
    optimize_application
    restart_services
    verify_deployment
    
    echo ""
    log_info "✅ Deployment completed successfully!"
    log_info "Time: $(date '+%Y-%m-%d %H:%M:%S')"
    echo ""
    log_info "Next steps:"
    echo "  - Test the application: https://hdtickets.local"
    echo "  - Check logs: tail -f storage/logs/laravel.log"
    echo "  - Monitor Horizon: https://hdtickets.local/horizon"
    echo ""
}

# Run main function
main "$@"
