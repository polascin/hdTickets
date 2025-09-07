#!/bin/bash

# HD Tickets - Automated Backup Manager
# Description: Comprehensive backup creation, verification, and recovery testing
# Usage: ./scripts/backup-manager.sh [backup|verify|restore|cleanup] [options]
# Author: HD Tickets DevOps Team
# Version: 1.0.0

set -euo pipefail

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" &> /dev/null && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_DIR/storage/backups"
LOG_DIR="$PROJECT_DIR/storage/logs"
BACKUP_LOG="$LOG_DIR/backup.log"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')

# Backup configuration
RETENTION_DAYS=30
MAX_BACKUPS=100
COMPRESSION_LEVEL=6
ENCRYPTION_KEY_FILE="$PROJECT_DIR/.backup_key"

# Database configuration (from .env)
if [[ -f "$PROJECT_DIR/.env" ]]; then
    set -a
    source "$PROJECT_DIR/.env"
    set +a
else
    echo "Error: .env file not found"
    exit 1
fi

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Logging functions
log_info() {
    local message="$1"
    echo "[$TIMESTAMP] INFO: $message" >> "$BACKUP_LOG"
    echo -e "${BLUE}[INFO]${NC} $message"
}

log_success() {
    local message="$1"
    echo "[$TIMESTAMP] SUCCESS: $message" >> "$BACKUP_LOG"
    echo -e "${GREEN}[SUCCESS]${NC} $message"
}

log_warning() {
    local message="$1"
    echo "[$TIMESTAMP] WARNING: $message" >> "$BACKUP_LOG"
    echo -e "${YELLOW}[WARNING]${NC} $message"
}

log_error() {
    local message="$1"
    echo "[$TIMESTAMP] ERROR: $message" >> "$BACKUP_LOG"
    echo -e "${RED}[ERROR]${NC} $message"
}

# Utility functions
check_dependencies() {
    local deps=("mysqldump" "mysql" "gzip" "openssl" "jq")
    
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            log_error "Required dependency '$dep' not found"
            exit 1
        fi
    done
    
    log_success "All dependencies are available"
}

generate_backup_key() {
    if [[ ! -f "$ENCRYPTION_KEY_FILE" ]]; then
        log_info "Generating new backup encryption key..."
        openssl rand -base64 32 > "$ENCRYPTION_KEY_FILE"
        chmod 600 "$ENCRYPTION_KEY_FILE"
        log_success "Backup encryption key generated"
    fi
}

get_database_size() {
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
            FROM information_schema.tables 
            WHERE table_schema = '$DB_DATABASE';" \
        --skip-column-names 2>/dev/null || echo "0"
}

create_database_backup() {
    local backup_name="$1"
    local backup_file="$BACKUP_DIR/db_${backup_name}.sql"
    local compressed_file="${backup_file}.gz"
    local encrypted_file="${compressed_file}.enc"
    
    log_info "Creating database backup: $backup_name"
    
    # Create database dump
    mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        --single-transaction \
        --routines \
        --triggers \
        --events \
        --hex-blob \
        --add-drop-database \
        --databases "$DB_DATABASE" > "$backup_file"
    
    if [[ $? -eq 0 ]]; then
        log_success "Database dump created: $(du -h "$backup_file" | cut -f1)"
        
        # Compress backup
        gzip -"$COMPRESSION_LEVEL" "$backup_file"
        log_success "Backup compressed: $(du -h "$compressed_file" | cut -f1)"
        
        # Encrypt backup
        openssl aes-256-cbc -salt -in "$compressed_file" -out "$encrypted_file" \
            -pass file:"$ENCRYPTION_KEY_FILE" 2>/dev/null
        
        if [[ $? -eq 0 ]]; then
            rm "$compressed_file"
            log_success "Backup encrypted: $(du -h "$encrypted_file" | cut -f1)"
            echo "$encrypted_file"
        else
            log_error "Failed to encrypt backup"
            return 1
        fi
    else
        log_error "Failed to create database dump"
        return 1
    fi
}

create_files_backup() {
    local backup_name="$1"
    local files_backup="$BACKUP_DIR/files_${backup_name}.tar.gz"
    
    log_info "Creating files backup: $backup_name"
    
    # Define important directories to backup
    local dirs_to_backup=(
        "app"
        "config"
        "database"
        "resources"
        "routes"
        "public"
        "storage/app"
        "storage/framework/views"
        ".env"
        "composer.json"
        "composer.lock"
        "package.json"
        "package-lock.json"
    )
    
    # Create tar archive with compression
    cd "$PROJECT_DIR"
    tar -czf "$files_backup" \
        --exclude="storage/logs/*" \
        --exclude="storage/framework/cache/*" \
        --exclude="storage/framework/sessions/*" \
        --exclude="node_modules" \
        --exclude="vendor" \
        --exclude=".git" \
        --exclude="storage/backups" \
        "${dirs_to_backup[@]}" 2>/dev/null
    
    if [[ $? -eq 0 ]]; then
        log_success "Files backup created: $(du -h "$files_backup" | cut -f1)"
        echo "$files_backup"
    else
        log_error "Failed to create files backup"
        return 1
    fi
}

create_full_backup() {
    local backup_name="${1:-hdtickets_$TIMESTAMP}"
    
    log_info "Starting full backup: $backup_name"
    
    # Ensure backup directory exists
    mkdir -p "$BACKUP_DIR"
    
    # Get database size before backup
    local db_size=$(get_database_size)
    log_info "Database size: ${db_size}MB"
    
    # Create database backup
    local db_backup
    if db_backup=$(create_database_backup "$backup_name"); then
        log_success "Database backup completed: $(basename "$db_backup")"
    else
        log_error "Database backup failed"
        return 1
    fi
    
    # Create files backup
    local files_backup
    if files_backup=$(create_files_backup "$backup_name"); then
        log_success "Files backup completed: $(basename "$files_backup")"
    else
        log_error "Files backup failed"
        return 1
    fi
    
    # Create backup manifest
    local manifest_file="$BACKUP_DIR/manifest_${backup_name}.json"
    cat > "$manifest_file" << EOF
{
    "backup_name": "$backup_name",
    "timestamp": "$TIMESTAMP",
    "date": "$(date '+%Y-%m-%d %H:%M:%S')",
    "hostname": "$(hostname)",
    "database": {
        "file": "$(basename "$db_backup")",
        "size_mb": "$db_size",
        "tables": $(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
                    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$DB_DATABASE';" \
                    --skip-column-names 2>/dev/null || echo "0")
    },
    "files": {
        "file": "$(basename "$files_backup")",
        "size_bytes": $(stat -c%s "$files_backup" 2>/dev/null || echo "0")
    },
    "checksums": {
        "database": "$(sha256sum "$db_backup" | cut -d' ' -f1)",
        "files": "$(sha256sum "$files_backup" | cut -d' ' -f1)"
    },
    "environment": {
        "php_version": "$(php -r 'echo PHP_VERSION;')",
        "laravel_version": "$(cd "$PROJECT_DIR" && php artisan --version | cut -d' ' -f3)",
        "app_env": "$APP_ENV"
    }
}
EOF
    
    log_success "Backup manifest created: $(basename "$manifest_file")"
    log_success "Full backup completed: $backup_name"
    
    # Return paths for verification
    echo "$db_backup:$files_backup:$manifest_file"
}

verify_backup() {
    local backup_identifier="$1"
    local success=true
    
    log_info "Verifying backup: $backup_identifier"
    
    # Find backup files
    local manifest_file=""
    if [[ -f "$BACKUP_DIR/manifest_${backup_identifier}.json" ]]; then
        manifest_file="$BACKUP_DIR/manifest_${backup_identifier}.json"
    else
        # Search for manifest by pattern
        manifest_file=$(find "$BACKUP_DIR" -name "manifest_*${backup_identifier}*.json" | head -1)
    fi
    
    if [[ -z "$manifest_file" || ! -f "$manifest_file" ]]; then
        log_error "Backup manifest not found for: $backup_identifier"
        return 1
    fi
    
    log_info "Using manifest: $(basename "$manifest_file")"
    
    # Parse manifest
    local db_file=$(jq -r '.database.file' "$manifest_file")
    local files_file=$(jq -r '.files.file' "$manifest_file")
    local db_checksum=$(jq -r '.checksums.database' "$manifest_file")
    local files_checksum=$(jq -r '.checksums.files' "$manifest_file")
    
    # Verify database backup exists and checksum
    local db_backup_path="$BACKUP_DIR/$db_file"
    if [[ -f "$db_backup_path" ]]; then
        local current_db_checksum=$(sha256sum "$db_backup_path" | cut -d' ' -f1)
        if [[ "$current_db_checksum" == "$db_checksum" ]]; then
            log_success "Database backup checksum verified"
        else
            log_error "Database backup checksum mismatch"
            success=false
        fi
        
        # Test decryption
        local temp_file=$(mktemp)
        if openssl aes-256-cbc -d -in "$db_backup_path" -out "$temp_file" \
           -pass file:"$ENCRYPTION_KEY_FILE" 2>/dev/null; then
            log_success "Database backup decryption test passed"
            rm "$temp_file"
        else
            log_error "Database backup decryption test failed"
            success=false
        fi
    else
        log_error "Database backup file not found: $db_file"
        success=false
    fi
    
    # Verify files backup exists and checksum
    local files_backup_path="$BACKUP_DIR/$files_file"
    if [[ -f "$files_backup_path" ]]; then
        local current_files_checksum=$(sha256sum "$files_backup_path" | cut -d' ' -f1)
        if [[ "$current_files_checksum" == "$files_checksum" ]]; then
            log_success "Files backup checksum verified"
        else
            log_error "Files backup checksum mismatch"
            success=false
        fi
        
        # Test tar archive integrity
        if tar -tzf "$files_backup_path" >/dev/null 2>&1; then
            log_success "Files backup archive integrity verified"
        else
            log_error "Files backup archive integrity check failed"
            success=false
        fi
    else
        log_error "Files backup file not found: $files_file"
        success=false
    fi
    
    if $success; then
        log_success "Backup verification completed successfully"
        return 0
    else
        log_error "Backup verification failed"
        return 1
    fi
}

test_restore() {
    local backup_identifier="$1"
    local test_db_name="hdtickets_restore_test_$(date +%s)"
    
    log_info "Testing restore procedure for backup: $backup_identifier"
    
    # Find and verify backup first
    if ! verify_backup "$backup_identifier"; then
        log_error "Cannot test restore - backup verification failed"
        return 1
    fi
    
    # Find backup files
    local manifest_file=$(find "$BACKUP_DIR" -name "manifest_*${backup_identifier}*.json" | head -1)
    local db_file=$(jq -r '.database.file' "$manifest_file")
    local db_backup_path="$BACKUP_DIR/$db_file"
    
    log_info "Testing database restore to temporary database: $test_db_name"
    
    # Create temporary database
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        -e "CREATE DATABASE \`$test_db_name\`;" 2>/dev/null
    
    if [[ $? -ne 0 ]]; then
        log_error "Failed to create temporary database"
        return 1
    fi
    
    # Decrypt and decompress backup
    local temp_sql=$(mktemp)
    if openssl aes-256-cbc -d -in "$db_backup_path" -pass file:"$ENCRYPTION_KEY_FILE" 2>/dev/null | \
       gunzip > "$temp_sql"; then
        log_success "Backup decrypted and decompressed"
    else
        log_error "Failed to decrypt/decompress backup"
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
            -e "DROP DATABASE \`$test_db_name\`;" 2>/dev/null
        rm -f "$temp_sql"
        return 1
    fi
    
    # Replace database name in SQL dump
    sed -i "s/\`$DB_DATABASE\`/\`$test_db_name\`/g" "$temp_sql"
    
    # Restore to temporary database
    if mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
       "$test_db_name" < "$temp_sql" 2>/dev/null; then
        log_success "Database restore test completed successfully"
        
        # Verify table count
        local table_count=$(mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
                           -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '$test_db_name';" \
                           --skip-column-names 2>/dev/null)
        log_info "Restored database contains $table_count tables"
        
    else
        log_error "Database restore test failed"
        mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
            -e "DROP DATABASE \`$test_db_name\`;" 2>/dev/null
        rm -f "$temp_sql"
        return 1
    fi
    
    # Cleanup
    mysql -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" -p"$DB_PASSWORD" \
        -e "DROP DATABASE \`$test_db_name\`;" 2>/dev/null
    rm -f "$temp_sql"
    
    log_success "Restore test completed and cleanup finished"
    return 0
}

list_backups() {
    log_info "Listing available backups..."
    
    if [[ ! -d "$BACKUP_DIR" ]]; then
        log_warning "Backup directory does not exist"
        return 0
    fi
    
    local manifests=($(find "$BACKUP_DIR" -name "manifest_*.json" | sort -r))
    
    if [[ ${#manifests[@]} -eq 0 ]]; then
        log_warning "No backups found"
        return 0
    fi
    
    echo ""
    printf "%-25s %-20s %-15s %-15s %-10s\n" "BACKUP NAME" "DATE" "DB SIZE" "FILES SIZE" "STATUS"
    echo "===================================================================================="
    
    for manifest in "${manifests[@]}"; do
        local backup_name=$(jq -r '.backup_name' "$manifest")
        local date=$(jq -r '.date' "$manifest")
        local db_size=$(jq -r '.database.size_mb' "$manifest")
        local files_size_bytes=$(jq -r '.files.size_bytes' "$manifest")
        local files_size=$(echo "$files_size_bytes" | awk '{printf "%.2f MB", $1/1024/1024}')
        
        # Check if backup files exist
        local db_file=$(jq -r '.database.file' "$manifest")
        local files_file=$(jq -r '.files.file' "$manifest")
        local status="✅ OK"
        
        if [[ ! -f "$BACKUP_DIR/$db_file" ]] || [[ ! -f "$BACKUP_DIR/$files_file" ]]; then
            status="❌ MISSING"
        fi
        
        printf "%-25s %-20s %-15s %-15s %-10s\n" \
            "$backup_name" "$date" "${db_size}MB" "$files_size" "$status"
    done
    
    echo ""
    log_success "Listed ${#manifests[@]} backups"
}

cleanup_old_backups() {
    log_info "Cleaning up old backups (retention: $RETENTION_DAYS days, max: $MAX_BACKUPS)"
    
    if [[ ! -d "$BACKUP_DIR" ]]; then
        log_info "No backup directory to clean"
        return 0
    fi
    
    local deleted_count=0
    
    # Delete backups older than retention period
    while IFS= read -r -d '' file; do
        rm "$file"
        deleted_count=$((deleted_count + 1))
        log_info "Deleted old backup: $(basename "$file")"
    done < <(find "$BACKUP_DIR" -name "*.enc" -o -name "*.tar.gz" -o -name "manifest_*.json" \
             -type f -mtime +$RETENTION_DAYS -print0 2>/dev/null)
    
    # If we still have too many backups, delete oldest ones
    local manifests=($(find "$BACKUP_DIR" -name "manifest_*.json" | sort -r))
    if [[ ${#manifests[@]} -gt $MAX_BACKUPS ]]; then
        local excess=$((${#manifests[@]} - MAX_BACKUPS))
        local oldest_manifests=("${manifests[@]: -$excess}")
        
        for manifest in "${oldest_manifests[@]}"; do
            local backup_name=$(jq -r '.backup_name' "$manifest")
            local db_file=$(jq -r '.database.file' "$manifest")
            local files_file=$(jq -r '.files.file' "$manifest")
            
            rm -f "$BACKUP_DIR/$db_file" "$BACKUP_DIR/$files_file" "$manifest"
            deleted_count=$((deleted_count + 3))
            log_info "Deleted excess backup: $backup_name"
        done
    fi
    
    if [[ $deleted_count -gt 0 ]]; then
        log_success "Cleanup completed: $deleted_count files deleted"
    else
        log_info "No cleanup required"
    fi
}

show_usage() {
    cat << EOF
HD Tickets Backup Manager

Usage: $0 <command> [options]

Commands:
    backup [name]           Create a full backup (database + files)
    verify <backup_name>    Verify backup integrity and checksums
    test-restore <backup>   Test restore procedure with temporary database
    list                    List all available backups
    cleanup                 Remove old backups according to retention policy
    
Options:
    --retention-days N      Set retention period (default: $RETENTION_DAYS)
    --max-backups N         Set maximum number of backups (default: $MAX_BACKUPS)
    --compression N         Set compression level 1-9 (default: $COMPRESSION_LEVEL)
    
Examples:
    $0 backup                           # Create backup with auto-generated name
    $0 backup pre_update_v2.1          # Create backup with custom name
    $0 verify hdtickets_20250109_143022 # Verify specific backup
    $0 test-restore latest             # Test restore of latest backup
    $0 list                            # List all backups
    $0 cleanup                         # Clean old backups

EOF
}

# Main execution
main() {
    # Ensure required directories exist
    mkdir -p "$BACKUP_DIR" "$LOG_DIR"
    
    # Parse command line arguments
    local command="${1:-}"
    shift || true
    
    # Parse options
    while [[ $# -gt 0 ]]; do
        case $1 in
            --retention-days=*)
                RETENTION_DAYS="${1#*=}"
                shift
                ;;
            --max-backups=*)
                MAX_BACKUPS="${1#*=}"
                shift
                ;;
            --compression=*)
                COMPRESSION_LEVEL="${1#*=}"
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
    
    # Check dependencies
    check_dependencies
    
    # Generate encryption key if needed
    generate_backup_key
    
    case "$command" in
        "backup")
            local backup_name="${1:-hdtickets_$TIMESTAMP}"
            create_full_backup "$backup_name"
            ;;
        "verify")
            local backup_identifier="${1:-}"
            if [[ -z "$backup_identifier" ]]; then
                log_error "Backup identifier required for verification"
                exit 1
            fi
            verify_backup "$backup_identifier"
            ;;
        "test-restore")
            local backup_identifier="${1:-}"
            if [[ -z "$backup_identifier" ]]; then
                log_error "Backup identifier required for restore test"
                exit 1
            fi
            test_restore "$backup_identifier"
            ;;
        "list")
            list_backups
            ;;
        "cleanup")
            cleanup_old_backups
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
