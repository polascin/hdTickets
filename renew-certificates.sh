#!/bin/bash

# HD Tickets Certificate Renewal Script
# Sports Events Entry Tickets Monitoring System - Automated Certificate Renewal
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration
DOMAIN="hdtickets.local"  # Change for production
CERT_NAME="hdtickets"
NOTIFICATION_EMAIL="admin@hdtickets.com"
LOG_FILE="/var/log/hdtickets-cert-renewal.log"
BACKUP_DIR="/var/backups/ssl-renewal"
APACHE_SERVICE="apache2"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log_message() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${timestamp} [${level}] ${message}" | tee -a "$LOG_FILE"
}

print_status() {
    log_message "INFO" "${GREEN}$1${NC}"
}

print_warning() {
    log_message "WARN" "${YELLOW}$1${NC}"
}

print_error() {
    log_message "ERROR" "${RED}$1${NC}"
}

print_info() {
    log_message "INFO" "${BLUE}$1${NC}"
}

# Create log directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"
mkdir -p "$BACKUP_DIR"

print_status "Starting certificate renewal process for $DOMAIN"

# Check if running as root
if [[ $EUID -ne 0 ]]; then
   print_error "This script must be run as root or with sudo"
   exit 1
fi

# Function to send notification
send_notification() {
    local subject="$1"
    local message="$2"
    local priority="$3"  # high, normal, low
    
    # Email notification (if mail is configured)
    if command -v mail >/dev/null 2>&1; then
        echo "$message" | mail -s "$subject" "$NOTIFICATION_EMAIL"
        print_info "Email notification sent to $NOTIFICATION_EMAIL"
    fi
    
    # Slack notification (configure webhook URL)
    # SLACK_WEBHOOK="https://hooks.slack.com/services/YOUR/SLACK/WEBHOOK"
    # if [ ! -z "$SLACK_WEBHOOK" ]; then
    #     curl -X POST -H 'Content-type: application/json' \
    #         --data "{\"text\":\"$subject: $message\"}" \
    #         "$SLACK_WEBHOOK"
    # fi
    
    # Log notification
    print_info "Notification: $subject - $message"
}

# Function to check certificate expiry
check_certificate_expiry() {
    local cert_file="$1"
    
    if [ ! -f "$cert_file" ]; then
        print_error "Certificate file not found: $cert_file"
        return 1
    fi
    
    local expiry_date=$(openssl x509 -in "$cert_file" -noout -enddate | cut -d= -f2)
    local expiry_timestamp=$(date -d "$expiry_date" +%s)
    local current_timestamp=$(date +%s)
    local days_until_expiry=$(( ($expiry_timestamp - $current_timestamp) / 86400 ))
    
    print_info "Certificate expires in $days_until_expiry days ($expiry_date)"
    echo $days_until_expiry
}

# Function to backup current certificates
backup_certificates() {
    local backup_timestamp=$(date +%Y%m%d-%H%M%S)
    local backup_path="$BACKUP_DIR/backup-$backup_timestamp"
    
    print_status "Creating certificate backup..."
    mkdir -p "$backup_path"
    
    # Backup Let's Encrypt certificates
    if [ -d "/etc/letsencrypt/live/$DOMAIN" ]; then
        cp -r "/etc/letsencrypt/live/$DOMAIN" "$backup_path/"
        print_status "Certificates backed up to $backup_path"
    fi
    
    # Backup Apache configuration
    if [ -f "/etc/apache2/sites-available/hdtickets-ssl.conf" ]; then
        cp "/etc/apache2/sites-available/hdtickets-ssl.conf" "$backup_path/"
    fi
    
    echo "$backup_path"
}

# Function to test certificate
test_certificate() {
    local cert_file="$1"
    local key_file="$2"
    
    print_status "Testing certificate validity..."
    
    # Check certificate format
    if ! openssl x509 -in "$cert_file" -noout >/dev/null 2>&1; then
        print_error "Invalid certificate format"
        return 1
    fi
    
    # Check private key format
    if ! openssl rsa -in "$key_file" -noout >/dev/null 2>&1; then
        print_error "Invalid private key format"
        return 1
    fi
    
    # Check if certificate and key match
    local cert_modulus=$(openssl x509 -in "$cert_file" -noout -modulus | openssl md5)
    local key_modulus=$(openssl rsa -in "$key_file" -noout -modulus | openssl md5)
    
    if [ "$cert_modulus" != "$key_modulus" ]; then
        print_error "Certificate and private key do not match"
        return 1
    fi
    
    print_status "Certificate validation passed"
    return 0
}

# Function to reload Apache gracefully
reload_apache() {
    print_status "Reloading Apache configuration..."
    
    # Test configuration first
    if ! apache2ctl configtest; then
        print_error "Apache configuration test failed"
        return 1
    fi
    
    # Graceful reload
    systemctl reload "$APACHE_SERVICE"
    
    if [ $? -eq 0 ]; then
        print_status "Apache reloaded successfully"
        return 0
    else
        print_error "Failed to reload Apache"
        return 1
    fi
}

# Function to verify SSL is working
verify_ssl_connection() {
    local domain="$1"
    local port="${2:-443}"
    
    print_status "Verifying SSL connection to $domain:$port"
    
    # Test SSL connection
    if echo | openssl s_client -connect "$domain:$port" -servername "$domain" >/dev/null 2>&1; then
        print_status "SSL connection test passed"
        
        # Check certificate chain
        local chain_output=$(echo | openssl s_client -connect "$domain:$port" -servername "$domain" 2>/dev/null)
        if echo "$chain_output" | grep -q "Verify return code: 0 (ok)"; then
            print_status "Certificate chain verification passed"
        else
            print_warning "Certificate chain verification issues detected"
        fi
        
        return 0
    else
        print_error "SSL connection test failed"
        return 1
    fi
}

# Main renewal logic
main() {
    print_status "HD Tickets Certificate Renewal Started"
    print_info "Domain: $DOMAIN"
    print_info "Certificate Name: $CERT_NAME"
    
    # Check if this is simulation mode
    if [[ "$DOMAIN" == "hdtickets.local" ]]; then
        print_warning "Running in simulation mode for local development"
        
        # Simulate renewal process
        print_info "SIMULATION: Would check certificate expiry"
        print_info "SIMULATION: Would backup certificates"
        print_info "SIMULATION: Would run: certbot renew --cert-name $CERT_NAME"
        print_info "SIMULATION: Would reload Apache"
        print_info "SIMULATION: Would verify SSL connection"
        
        send_notification "HD Tickets SSL - Simulation Renewal" \
                         "Certificate renewal simulation completed successfully" \
                         "normal"
        
        print_status "Simulation completed successfully"
        return 0
    fi
    
    # Production renewal process
    local cert_file="/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
    local key_file="/etc/letsencrypt/live/$DOMAIN/privkey.pem"
    
    # Check current certificate expiry
    if [ -f "$cert_file" ]; then
        local days_remaining=$(check_certificate_expiry "$cert_file")
        
        # Only renew if certificate expires within 30 days
        if [ "$days_remaining" -gt 30 ]; then
            print_info "Certificate is still valid for $days_remaining days, skipping renewal"
            exit 0
        fi
        
        print_warning "Certificate expires in $days_remaining days, proceeding with renewal"
    else
        print_warning "Certificate file not found, proceeding with initial certificate generation"
    fi
    
    # Create backup
    backup_path=$(backup_certificates)
    
    # Run certbot renewal
    print_status "Running certificate renewal..."
    
    if certbot renew --cert-name "$CERT_NAME" --quiet --no-self-upgrade; then
        print_status "Certificate renewal completed successfully"
        
        # Test the new certificate
        if test_certificate "$cert_file" "$key_file"; then
            print_status "New certificate validation passed"
            
            # Reload Apache
            if reload_apache; then
                print_status "Apache configuration reloaded"
                
                # Wait a moment for Apache to fully restart
                sleep 2
                
                # Verify SSL connection
                if verify_ssl_connection "$DOMAIN"; then
                    print_status "SSL verification passed"
                    
                    send_notification "HD Tickets SSL - Renewal Success" \
                                     "Certificate for $DOMAIN renewed successfully. Valid for 90 days." \
                                     "normal"
                    
                    print_status "Certificate renewal process completed successfully"
                else
                    print_error "SSL verification failed after renewal"
                    send_notification "HD Tickets SSL - Verification Failed" \
                                     "Certificate renewed but SSL verification failed for $DOMAIN" \
                                     "high"
                    exit 1
                fi
            else
                print_error "Failed to reload Apache after renewal"
                send_notification "HD Tickets SSL - Apache Reload Failed" \
                                 "Certificate renewed but Apache reload failed for $DOMAIN" \
                                 "high"
                exit 1
            fi
        else
            print_error "New certificate validation failed"
            send_notification "HD Tickets SSL - Certificate Validation Failed" \
                             "Certificate renewal failed validation for $DOMAIN" \
                             "high"
            exit 1
        fi
    else
        print_error "Certificate renewal failed"
        send_notification "HD Tickets SSL - Renewal Failed" \
                         "Certificate renewal failed for $DOMAIN. Manual intervention required." \
                         "high"
        exit 1
    fi
}

# Cleanup function
cleanup() {
    print_info "Cleaning up temporary files..."
    # Add any cleanup logic here
}

# Set trap for cleanup on exit
trap cleanup EXIT

# Run main function
main "$@"
