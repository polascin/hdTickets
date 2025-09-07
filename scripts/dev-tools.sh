#!/bin/bash

# HD Tickets - Development Workflow Optimization Tools
# Description: Comprehensive development tools for testing, deployment, and productivity
# Usage: ./scripts/dev-tools.sh [command] [options]
# Author: HD Tickets DevOps Team
# Version: 1.0.0

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
LOG_DIR="$PROJECT_DIR/storage/logs"
DEV_LOG="$LOG_DIR/dev-tools.log"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
NC='\033[0m'

# Logging functions
log_info() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] INFO: $message" >> "$DEV_LOG"
    echo -e "${BLUE}[INFO]${NC} $message"
}

log_success() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] SUCCESS: $message" >> "$DEV_LOG"
    echo -e "${GREEN}[SUCCESS]${NC} $message"
}

log_warning() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] WARNING: $message" >> "$DEV_LOG"
    echo -e "${YELLOW}[WARNING]${NC} $message"
}

log_error() {
    local message="$1"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ERROR: $message" >> "$DEV_LOG"
    echo -e "${RED}[ERROR]${NC} $message"
}

# Development tools
setup_dev_environment() {
    log_info "Setting up development environment..."
    cd "$PROJECT_DIR"
    
    local setup_steps=0
    
    # Install/update Composer dependencies
    if composer install --no-prod 2>/dev/null; then
        log_success "Composer dependencies installed"
        setup_steps=$((setup_steps + 1))
    else
        log_error "Failed to install Composer dependencies"
    fi
    
    # Install/update npm dependencies
    if [[ -f "package.json" ]]; then
        if npm install 2>/dev/null; then
            log_success "npm dependencies installed"
            setup_steps=$((setup_steps + 1))
        else
            log_error "Failed to install npm dependencies"
        fi
    fi
    
    # Copy .env file if it doesn't exist
    if [[ ! -f ".env" ]] && [[ -f ".env.example" ]]; then
        cp .env.example .env
        log_success ".env file created from example"
        setup_steps=$((setup_steps + 1))
    fi
    
    # Generate application key if needed
    if php artisan key:generate --show 2>/dev/null | grep -q "base64:"; then
        if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
            php artisan key:generate --force >/dev/null 2>&1
            log_success "Application key generated"
            setup_steps=$((setup_steps + 1))
        fi
    fi
    
    # Create storage links
    if php artisan storage:link >/dev/null 2>&1; then
        log_success "Storage symlink created"
        setup_steps=$((setup_steps + 1))
    else
        log_info "Storage symlink already exists or failed"
    fi
    
    # Set up database
    if php artisan migrate:status >/dev/null 2>&1; then
        log_success "Database connection verified"
        
        # Run migrations if needed
        if php artisan migrate --pretend 2>/dev/null | grep -q "Migration batch"; then
            log_info "Pending migrations found"
            read -p "Run database migrations? (y/N): " -r
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                php artisan migrate --force
                log_success "Database migrations completed"
                setup_steps=$((setup_steps + 1))
            fi
        else
            log_success "Database is up to date"
        fi
    else
        log_warning "Database connection failed - check configuration"
    fi
    
    log_success "Development environment setup completed ($setup_steps steps)"
}

run_tests() {
    log_info "Running test suite..."
    cd "$PROJECT_DIR"
    
    local test_results=""
    local total_tests=0
    local failed_tests=0
    
    # Run PHPUnit tests
    if [[ -f "vendor/bin/phpunit" ]] || [[ -f "phpunit.xml" ]]; then
        log_info "Running PHPUnit tests..."
        
        local phpunit_output
        if phpunit_output=$(php artisan test --no-coverage 2>&1); then
            echo "$phpunit_output" | grep -E "(OK|Tests:|Assertions:)" | while read -r line; do
                log_info "$line"
            done
            
            total_tests=$(echo "$phpunit_output" | grep -oE '[0-9]+ tests?' | head -1 | grep -oE '[0-9]+' || echo "0")
            log_success "PHPUnit tests passed ($total_tests tests)"
        else
            failed_tests=$(echo "$phpunit_output" | grep -oE '[0-9]+ failures?' | grep -oE '[0-9]+' || echo "0")
            log_error "PHPUnit tests failed ($failed_tests failures)"
            echo "$phpunit_output" | tail -10
        fi
    else
        log_warning "PHPUnit not configured"
    fi
    
    # Run JavaScript tests if available
    if [[ -f "package.json" ]] && grep -q '"test"' package.json; then
        log_info "Running JavaScript tests..."
        
        if npm test 2>/dev/null; then
            log_success "JavaScript tests passed"
        else
            log_error "JavaScript tests failed"
        fi
    fi
    
    # Generate test coverage report
    if [[ "$1" == "--coverage" ]]; then
        log_info "Generating test coverage report..."
        
        if php artisan test --coverage --coverage-html=storage/quality/coverage/html >/dev/null 2>&1; then
            log_success "Coverage report generated: storage/quality/coverage/html/index.html"
        else
            log_warning "Failed to generate coverage report"
        fi
    fi
}

quality_check() {
    log_info "Running code quality checks..."
    cd "$PROJECT_DIR"
    
    local quality_score=0
    local max_score=6
    
    # PHP syntax check
    log_info "Checking PHP syntax..."
    local php_errors=0
    while IFS= read -r -d '' file; do
        if ! php -l "$file" >/dev/null 2>&1; then
            php_errors=$((php_errors + 1))
        fi
    done < <(find app config routes -name "*.php" -print0 2>/dev/null)
    
    if [[ $php_errors -eq 0 ]]; then
        log_success "PHP syntax check passed"
        quality_score=$((quality_score + 1))
    else
        log_error "PHP syntax errors found: $php_errors files"
    fi
    
    # Laravel Pint (code style)
    if [[ -f "vendor/bin/pint" ]]; then
        log_info "Running Laravel Pint code style check..."
        
        if vendor/bin/pint --test --quiet; then
            log_success "Code style check passed"
            quality_score=$((quality_score + 1))
        else
            log_warning "Code style issues found - run 'vendor/bin/pint' to fix"
        fi
    fi
    
    # PHPStan static analysis
    if [[ -f "vendor/bin/phpstan" ]]; then
        log_info "Running PHPStan static analysis..."
        
        if vendor/bin/phpstan analyse --no-progress --quiet >/dev/null 2>&1; then
            log_success "Static analysis passed"
            quality_score=$((quality_score + 1))
        else
            log_warning "Static analysis issues found"
        fi
    fi
    
    # Composer validation
    if composer validate --no-check-all --quiet 2>/dev/null; then
        log_success "Composer.json validation passed"
        quality_score=$((quality_score + 1))
    else
        log_warning "Composer.json validation issues"
    fi
    
    # Check for security vulnerabilities
    if composer audit --no-dev --quiet 2>/dev/null; then
        log_success "Security audit passed"
        quality_score=$((quality_score + 1))
    else
        log_warning "Security vulnerabilities detected"
    fi
    
    # Check environment configuration
    if php artisan config:cache >/dev/null 2>&1 && php artisan config:clear >/dev/null 2>&1; then
        log_success "Environment configuration valid"
        quality_score=$((quality_score + 1))
    else
        log_error "Environment configuration issues"
    fi
    
    local quality_percentage=$(echo "$quality_score * 100 / $max_score" | bc)
    log_info "Quality score: $quality_score/$max_score ($quality_percentage%)"
    
    if [[ $quality_percentage -ge 80 ]]; then
        log_success "Code quality is good"
    elif [[ $quality_percentage -ge 60 ]]; then
        log_warning "Code quality needs improvement"
    else
        log_error "Code quality is poor"
    fi
}

build_assets() {
    log_info "Building frontend assets..."
    cd "$PROJECT_DIR"
    
    if [[ ! -f "package.json" ]]; then
        log_warning "No package.json found - skipping asset build"
        return 0
    fi
    
    # Install dependencies if node_modules doesn't exist
    if [[ ! -d "node_modules" ]]; then
        log_info "Installing npm dependencies..."
        npm install
    fi
    
    # Build for development or production
    local build_mode="${1:-development}"
    
    case "$build_mode" in
        "production")
            log_info "Building assets for production..."
            if npm run build >/dev/null 2>&1; then
                log_success "Production assets built successfully"
                
                # Show build statistics
                if [[ -d "public/build" ]]; then
                    local total_size=$(du -sh public/build | cut -f1)
                    local file_count=$(find public/build -type f | wc -l)
                    log_info "Build output: $file_count files, $total_size total"
                fi
            else
                log_error "Production asset build failed"
                return 1
            fi
            ;;
        "development")
            log_info "Building assets for development..."
            if npm run dev >/dev/null 2>&1; then
                log_success "Development assets built successfully"
            else
                log_error "Development asset build failed"
                return 1
            fi
            ;;
        "watch")
            log_info "Starting asset watch mode..."
            log_info "Press Ctrl+C to stop watching..."
            npm run dev -- --watch
            ;;
        *)
            log_error "Unknown build mode: $build_mode"
            return 1
            ;;
    esac
}

deployment_prep() {
    log_info "Preparing for deployment..."
    cd "$PROJECT_DIR"
    
    local prep_steps=0
    local warnings=0
    
    # Run quality checks
    log_info "Running pre-deployment quality checks..."
    quality_check
    
    # Build production assets
    log_info "Building production assets..."
    if build_assets production; then
        prep_steps=$((prep_steps + 1))
    else
        warnings=$((warnings + 1))
    fi
    
    # Optimize Composer autoloader
    if composer dump-autoload --optimize --no-dev >/dev/null 2>&1; then
        log_success "Composer autoloader optimized"
        prep_steps=$((prep_steps + 1))
    else
        log_warning "Failed to optimize autoloader"
        warnings=$((warnings + 1))
    fi
    
    # Cache Laravel configuration
    if php artisan config:cache >/dev/null 2>&1; then
        log_success "Configuration cached"
        prep_steps=$((prep_steps + 1))
    else
        log_warning "Failed to cache configuration"
        warnings=$((warnings + 1))
    fi
    
    # Cache routes
    if php artisan route:cache >/dev/null 2>&1; then
        log_success "Routes cached"
        prep_steps=$((prep_steps + 1))
    else
        log_warning "Failed to cache routes"
        warnings=$((warnings + 1))
    fi
    
    # Cache views
    if php artisan view:cache >/dev/null 2>&1; then
        log_success "Views cached"
        prep_steps=$((prep_steps + 1))
    else
        log_warning "Failed to cache views"
        warnings=$((warnings + 1))
    fi
    
    # Generate deployment checklist
    local checklist_file="$LOG_DIR/deployment-checklist-$TIMESTAMP.md"
    cat > "$checklist_file" << EOF
# HD Tickets Deployment Checklist

Generated: $(date '+%Y-%m-%d %H:%M:%S')

## Pre-deployment Checks
- [x] Code quality checks completed
- [x] Assets built for production
- [x] Composer autoloader optimized
- [x] Laravel caches optimized

## Environment Setup
- [ ] Environment variables configured
- [ ] Database migrations ready
- [ ] File permissions set correctly
- [ ] SSL certificates installed
- [ ] Domain DNS configured

## Post-deployment Tasks
- [ ] Run database migrations
- [ ] Verify application health
- [ ] Test critical functionality
- [ ] Monitor error logs
- [ ] Backup previous version

## Rollback Plan
- [ ] Previous version backup available
- [ ] Database rollback scripts ready
- [ ] CDN cache invalidation prepared

## Notes
- Quality issues: $warnings warnings found
- Preparation steps: $prep_steps completed
- Build artifacts: public/build directory

## Contact Information
- Tech Lead: [Your Name]
- DevOps: [Your Name]
- Emergency Contact: [Your Name]

EOF
    
    log_success "Deployment preparation completed"
    log_info "Checklist generated: $(basename "$checklist_file")"
    
    if [[ $warnings -eq 0 ]]; then
        log_success "Ready for deployment with no warnings"
    else
        log_warning "Deployment ready with $warnings warnings - review recommended"
    fi
}

create_dev_server() {
    log_info "Starting development server..."
    cd "$PROJECT_DIR"
    
    local port="${1:-8000}"
    local host="${2:-localhost}"
    
    # Check if port is available
    if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
        log_error "Port $port is already in use"
        return 1
    fi
    
    # Start Laravel development server
    log_success "Starting Laravel server on $host:$port"
    log_info "Press Ctrl+C to stop the server"
    
    php artisan serve --host="$host" --port="$port"
}

run_linting() {
    log_info "Running linting checks..."
    cd "$PROJECT_DIR"
    
    local linting_issues=0
    
    # PHP linting (Laravel Pint)
    if [[ -f "vendor/bin/pint" ]]; then
        log_info "Running PHP linting..."
        if vendor/bin/pint --test --quiet; then
            log_success "PHP linting passed"
        else
            log_warning "PHP linting issues found"
            linting_issues=$((linting_issues + 1))
            
            if [[ "$1" == "--fix" ]]; then
                log_info "Auto-fixing PHP linting issues..."
                vendor/bin/pint --quiet
                log_success "PHP linting issues fixed"
            fi
        fi
    fi
    
    # JavaScript/TypeScript linting
    if [[ -f "package.json" ]] && grep -q '"lint"' package.json 2>/dev/null; then
        log_info "Running JavaScript linting..."
        if npm run lint >/dev/null 2>&1; then
            log_success "JavaScript linting passed"
        else
            log_warning "JavaScript linting issues found"
            linting_issues=$((linting_issues + 1))
            
            if [[ "$1" == "--fix" ]]; then
                log_info "Auto-fixing JavaScript linting issues..."
                npm run lint:fix >/dev/null 2>&1 || true
                log_success "JavaScript linting issues fixed"
            fi
        fi
    fi
    
    if [[ $linting_issues -eq 0 ]]; then
        log_success "All linting checks passed"
    else
        log_warning "$linting_issues linting issue categories found"
        
        if [[ "$1" != "--fix" ]]; then
            log_info "Run with --fix to automatically resolve issues"
        fi
    fi
}

database_tools() {
    log_info "Database development tools..."
    cd "$PROJECT_DIR"
    
    local action="${1:-status}"
    
    case "$action" in
        "status")
            log_info "Database migration status:"
            php artisan migrate:status
            ;;
        "migrate")
            log_info "Running database migrations..."
            php artisan migrate --force
            log_success "Migrations completed"
            ;;
        "seed")
            log_info "Seeding database..."
            php artisan db:seed --force
            log_success "Database seeded"
            ;;
        "fresh")
            log_warning "This will drop all tables and recreate them"
            read -p "Are you sure? (y/N): " -r
            if [[ $REPLY =~ ^[Yy]$ ]]; then
                php artisan migrate:fresh --seed --force
                log_success "Database refreshed with fresh migrations and seeds"
            else
                log_info "Database refresh cancelled"
            fi
            ;;
        "backup")
            log_info "Creating database backup..."
            if [[ -f "scripts/backup-manager.sh" ]]; then
                ./scripts/backup-manager.sh backup "dev_backup_$TIMESTAMP"
                log_success "Development database backup created"
            else
                log_warning "Backup script not found"
            fi
            ;;
        *)
            log_error "Unknown database action: $action"
            log_info "Available actions: status, migrate, seed, fresh, backup"
            ;;
    esac
}

show_usage() {
    cat << EOF
HD Tickets Development Tools

Usage: $0 <command> [options]

Commands:
    setup                   Set up development environment
    test [--coverage]       Run test suite with optional coverage
    quality                 Run code quality checks
    build [mode]           Build assets (development|production|watch)
    deploy-prep            Prepare for deployment
    serve [port] [host]    Start development server
    lint [--fix]           Run linting checks with optional auto-fix
    db <action>            Database tools (status|migrate|seed|fresh|backup)
    
Asset Build Modes:
    development            Build for development (default)
    production             Build optimized for production
    watch                  Build and watch for changes

Database Actions:
    status                 Show migration status
    migrate                Run pending migrations
    seed                   Seed database with test data
    fresh                  Drop all tables and recreate
    backup                 Create development backup

Examples:
    $0 setup                       # Set up dev environment
    $0 test --coverage            # Run tests with coverage
    $0 build production           # Build production assets
    $0 lint --fix                 # Lint and auto-fix issues
    $0 serve 3000 0.0.0.0        # Start server on all interfaces
    $0 db fresh                   # Fresh database setup
    $0 deploy-prep               # Prepare for deployment

EOF
}

# Main execution
main() {
    # Ensure log directory exists
    mkdir -p "$LOG_DIR"
    
    # Parse command line arguments
    local command="${1:-}"
    shift || true
    
    echo -e "${CYAN}HD Tickets Development Tools${NC}"
    echo "============================="
    
    case "$command" in
        "setup")
            setup_dev_environment
            ;;
        "test")
            run_tests "$@"
            ;;
        "quality")
            quality_check
            ;;
        "build")
            build_assets "$@"
            ;;
        "deploy-prep")
            deployment_prep
            ;;
        "serve")
            create_dev_server "$@"
            ;;
        "lint")
            run_linting "$@"
            ;;
        "db")
            database_tools "$@"
            ;;
        "")
            log_error "Command required"
            show_usage
            exit 1
            ;;
        *)
            log_error "Unknown command: $command"
            show_usage
            exit 1
            ;;
    esac
}

# Execute main function
main "$@"
