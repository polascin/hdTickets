#!/bin/bash

# HD Tickets Certificate Expiry Monitoring Script
# Sports Events Entry Tickets Monitoring System - Certificate Expiry Alerts
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration
DOMAIN="hdtickets.local"  # Change for production
CERT_PATH="/etc/letsencrypt/live/$DOMAIN/fullchain.pem"
NOTIFICATION_EMAIL="admin@hdtickets.com"
LOG_FILE="/var/log/hdtickets-cert-expiry.log"
SLACK_WEBHOOK=""  # Configure for Slack notifications

# Alert thresholds (days)
CRITICAL_DAYS=7
WARNING_DAYS=30
INFO_DAYS=60

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Create log directory
mkdir -p "$(dirname "$LOG_FILE")"

# Function to send alert
send_alert() {
    local subject="$1"
    local message="$2"
    local priority="$3"  # CRITICAL, WARNING, INFO
    local days_remaining="$4"
    
    local full_message="Certificate Expiry Alert for $DOMAIN

Priority: $priority
Days Remaining: $days_remaining
Domain: $DOMAIN
Certificate Path: $CERT_PATH

$message

Actions Required:
- For CRITICAL alerts: Immediate certificate renewal required
- For WARNING alerts: Schedule certificate renewal within 7 days
- For INFO alerts: Plan certificate renewal within 30 days

HD Tickets SSL Monitoring System
Generated: $(date)
Server: $(hostname)"
    
    # Email notification
    if command -v mail >/dev/null 2>&1; then
        echo "$full_message" | mail -s "$subject" "$NOTIFICATION_EMAIL"
        print_info "Email alert sent to $NOTIFICATION_EMAIL"
    fi
    
    # Slack notification
    if [ ! -z "$SLACK_WEBHOOK" ]; then
        local color=""
        case $priority in
            CRITICAL) color="danger" ;;
            WARNING) color="warning" ;;
            INFO) color="good" ;;
        esac
        
        local slack_payload=$(cat <<EOF
{
    "attachments": [
        {
            "color": "$color",
            "title": "$subject",
            "text": "$message",
            "fields": [
                {
                    "title": "Domain",
                    "value": "$DOMAIN",
                    "short": true
                },
                {
                    "title": "Days Remaining",
                    "value": "$days_remaining",
                    "short": true
                },
                {
                    "title": "Priority",
                    "value": "$priority",
                    "short": true
                }
            ],
            "footer": "HD Tickets SSL Monitor",
            "ts": $(date +%s)
        }
    ]
}
EOF
        )
        
        curl -X POST -H 'Content-type: application/json' \
             --data "$slack_payload" \
             "$SLACK_WEBHOOK" >/dev/null 2>&1
        
        print_info "Slack alert sent"
    fi
    
    # System log
    logger -t hdtickets-ssl "[$priority] Certificate for $DOMAIN expires in $days_remaining days"
}

# Function to check certificate expiry
check_certificate() {
    local cert_file="$1"
    local domain="$2"
    
    print_status "Checking certificate expiry for $domain"
    
    if [ ! -f "$cert_file" ]; then
        print_error "Certificate file not found: $cert_file"
        send_alert "HD Tickets SSL - Certificate Missing" \
                  "Certificate file not found: $cert_file" \
                  "CRITICAL" \
                  "0"
        return 1
    fi
    
    # Get certificate expiry date
    local expiry_date=$(openssl x509 -in "$cert_file" -noout -enddate | cut -d= -f2)
    local expiry_timestamp=$(date -d "$expiry_date" +%s)
    local current_timestamp=$(date +%s)
    local days_until_expiry=$(( ($expiry_timestamp - $current_timestamp) / 86400 ))
    
    print_info "Certificate expires on: $expiry_date"
    print_info "Days until expiry: $days_until_expiry"
    
    # Check certificate validity
    if ! openssl x509 -in "$cert_file" -noout -checkend 0 >/dev/null 2>&1; then
        print_error "Certificate has already expired!"
        send_alert "HD Tickets SSL - Certificate Expired" \
                  "Certificate for $domain has expired! Immediate action required." \
                  "CRITICAL" \
                  "0"
        return 1
    fi
    
    # Determine alert level and send notifications
    if [ "$days_until_expiry" -le "$CRITICAL_DAYS" ]; then
        print_error "CRITICAL: Certificate expires in $days_until_expiry days"
        send_alert "HD Tickets SSL - CRITICAL Expiry Warning" \
                  "Certificate for $domain expires in $days_until_expiry days. Immediate renewal required!" \
                  "CRITICAL" \
                  "$days_until_expiry"
    elif [ "$days_until_expiry" -le "$WARNING_DAYS" ]; then
        print_warning "WARNING: Certificate expires in $days_until_expiry days"
        send_alert "HD Tickets SSL - Expiry Warning" \
                  "Certificate for $domain expires in $days_until_expiry days. Renewal should be scheduled soon." \
                  "WARNING" \
                  "$days_until_expiry"
    elif [ "$days_until_expiry" -le "$INFO_DAYS" ]; then
        print_info "INFO: Certificate expires in $days_until_expiry days"
        send_alert "HD Tickets SSL - Expiry Notice" \
                  "Certificate for $domain expires in $days_until_expiry days. Consider planning renewal." \
                  "INFO" \
                  "$days_until_expiry"
    else
        print_status "Certificate is valid for $days_until_expiry days - no action needed"
    fi
    
    return 0
}

# Function to check certificate chain
check_certificate_chain() {
    local domain="$1"
    local port="${2:-443}"
    
    print_status "Checking certificate chain for $domain:$port"
    
    # Test SSL connection and certificate chain
    local chain_output=$(echo | openssl s_client -connect "$domain:$port" -servername "$domain" 2>/dev/null)
    
    if echo "$chain_output" | grep -q "Verify return code: 0 (ok)"; then
        print_status "Certificate chain validation: OK"
        
        # Get certificate details from connection
        local cert_info=$(echo "$chain_output" | openssl x509 -noout -subject -issuer -dates 2>/dev/null)
        print_info "Certificate chain details:"
        echo "$cert_info" | while read line; do
            print_info "  $line"
        done
        
    else
        local verify_code=$(echo "$chain_output" | grep "Verify return code:" | cut -d: -f2-)
        print_warning "Certificate chain validation issue:$verify_code"
        
        # Send alert for chain issues
        send_alert "HD Tickets SSL - Certificate Chain Issue" \
                  "Certificate chain validation failed for $domain. Verify code:$verify_code" \
                  "WARNING" \
                  "N/A"
    fi
}

# Function to check OCSP stapling
check_ocsp_stapling() {
    local domain="$1"
    local port="${2:-443}"
    
    print_status "Checking OCSP stapling for $domain:$port"
    
    # Test OCSP stapling
    local ocsp_output=$(echo | openssl s_client -connect "$domain:$port" -servername "$domain" -status 2>/dev/null)
    
    if echo "$ocsp_output" | grep -q "OCSP response: no response sent"; then
        print_warning "OCSP stapling is not working properly"
    elif echo "$ocsp_output" | grep -q "OCSP Response Status: successful"; then
        print_status "OCSP stapling is working correctly"
    else
        print_info "OCSP stapling status unclear - check configuration"
    fi
}

# Function to check multiple certificate sources
check_all_certificates() {
    print_status "Starting comprehensive certificate check"
    
    # Check Let's Encrypt certificate
    if [ -f "$CERT_PATH" ]; then
        print_info "Checking Let's Encrypt certificate"
        check_certificate "$CERT_PATH" "$DOMAIN"
    fi
    
    # Check self-signed certificate (fallback)
    local self_signed_cert="/etc/ssl/hdtickets/hdtickets.local.crt"
    if [ -f "$self_signed_cert" ]; then
        print_info "Checking self-signed certificate (backup)"
        check_certificate "$self_signed_cert" "$DOMAIN"
    fi
    
    # Check live certificate via SSL connection
    if command -v openssl >/dev/null 2>&1; then
        print_info "Checking live certificate via SSL connection"
        check_certificate_chain "$DOMAIN"
        
        # Check OCSP stapling if enabled
        check_ocsp_stapling "$DOMAIN"
    fi
}

# Function to generate monitoring report
generate_report() {
    local report_file="/var/log/hdtickets-cert-report-$(date +%Y%m%d).log"
    
    print_status "Generating certificate monitoring report"
    
    cat > "$report_file" << EOF
HD Tickets Certificate Monitoring Report
Generated: $(date)
Domain: $DOMAIN
Server: $(hostname)

==================================================

Certificate File Locations:
- Let's Encrypt: $CERT_PATH
- Self-signed: /etc/ssl/hdtickets/hdtickets.local.crt

Certificate Status Summary:
$(if [ -f "$CERT_PATH" ]; then
    expiry_date=$(openssl x509 -in "$CERT_PATH" -noout -enddate | cut -d= -f2)
    days_remaining=$(( ($(date -d "$expiry_date" +%s) - $(date +%s)) / 86400 ))
    echo "- Primary certificate expires in $days_remaining days ($expiry_date)"
else
    echo "- Primary certificate not found"
fi)

SSL Configuration:
- HTTPS Port: 443
- HTTP/2 Enabled: $(apache2ctl -M | grep -q http2 && echo "Yes" || echo "No")
- OCSP Stapling: $(grep -q "SSLUseStapling" /etc/apache2/sites-enabled/hdtickets-ssl.conf && echo "Enabled" || echo "Disabled")

Recent Certificate Activities:
$(tail -10 /var/log/hdtickets-cert-renewal.log 2>/dev/null || echo "No renewal log found")

Next Scheduled Tasks:
- Renewal check: Twice daily (3:30 AM & 3:30 PM)
- Status monitoring: Daily at 8:00 AM
- Expiry alerts: Weekly on Monday at 10:00 AM

==================================================
End of Report
EOF

    print_status "Report saved to: $report_file"
    
    # Keep only last 30 days of reports
    find /var/log -name "hdtickets-cert-report-*.log" -mtime +30 -delete 2>/dev/null || true
}

# Main execution
main() {
    print_status "HD Tickets Certificate Expiry Check Started"
    
    # Run certificate checks
    check_all_certificates
    
    # Generate monitoring report
    generate_report
    
    print_status "Certificate expiry check completed"
}

# Run main function
main "$@"
