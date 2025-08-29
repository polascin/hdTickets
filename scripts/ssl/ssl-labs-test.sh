#!/bin/bash

# HD Tickets SSL Labs Testing Script
# Sports Events Entry Tickets Monitoring System - SSL Labs Security Assessment
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration
DOMAIN="hdtickets.local"  # Change for production
API_URL="https://api.ssllabs.com/api/v3"
LOG_FILE="/var/log/hdtickets-ssl-labs.log"
REPORT_DIR="/var/log/ssl-labs-reports"
MAX_WAIT_TIME=600  # 10 minutes maximum wait

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

# Create directories
mkdir -p "$REPORT_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Function to check if domain is accessible from internet
check_domain_accessibility() {
    local domain="$1"
    
    print_status "Checking domain accessibility: $domain"
    
    # Check if domain resolves
    if ! host "$domain" >/dev/null 2>&1; then
        print_error "Domain $domain does not resolve to an IP address"
        return 1
    fi
    
    # Get IP address
    local ip_address=$(host "$domain" | grep "has address" | head -1 | awk '{print $4}')
    print_info "Domain resolves to: $ip_address"
    
    # Check if it's a private IP
    if [[ "$ip_address" =~ ^(10\.|172\.1[6-9]\.|172\.2[0-9]\.|172\.3[0-1]\.|192\.168\.) ]] || [[ "$ip_address" =~ ^127\. ]]; then
        print_warning "Domain resolves to private/local IP address: $ip_address"
        print_warning "SSL Labs testing requires publicly accessible domains"
        return 1
    fi
    
    # Test HTTPS connection
    if ! curl -s --connect-timeout 10 "https://$domain/" >/dev/null 2>&1; then
        print_error "Cannot establish HTTPS connection to $domain"
        return 1
    fi
    
    print_status "Domain is accessible for SSL Labs testing"
    return 0
}

# Function to start SSL Labs scan
start_ssl_labs_scan() {
    local domain="$1"
    local publish="${2:-off}"  # Don't publish by default
    
    print_status "Starting SSL Labs scan for $domain"
    
    local start_url="${API_URL}/analyze?host=${domain}&publish=${publish}&startNew=on&all=done"
    
    # Start the scan
    local response=$(curl -s "$start_url")
    
    if [ $? -ne 0 ]; then
        print_error "Failed to start SSL Labs scan"
        return 1
    fi
    
    # Check if scan started successfully
    local status=$(echo "$response" | jq -r '.status // empty' 2>/dev/null)
    
    if [ -z "$status" ]; then
        print_error "Invalid response from SSL Labs API"
        print_error "Response: $response"
        return 1
    fi
    
    print_info "Scan status: $status"
    
    case "$status" in
        "DNS"|"IN_PROGRESS")
            print_status "Scan initiated successfully"
            return 0
            ;;
        "READY")
            print_status "Scan completed immediately (cached results)"
            return 0
            ;;
        "ERROR")
            local error_message=$(echo "$response" | jq -r '.statusMessage // "Unknown error"' 2>/dev/null)
            print_error "SSL Labs scan error: $error_message"
            return 1
            ;;
        *)
            print_warning "Unexpected status: $status"
            return 0
            ;;
    esac
}

# Function to wait for scan completion
wait_for_scan_completion() {
    local domain="$1"
    local wait_time=0
    local check_interval=30
    
    print_status "Waiting for SSL Labs scan to complete..."
    
    while [ $wait_time -lt $MAX_WAIT_TIME ]; do
        local check_url="${API_URL}/analyze?host=${domain}"
        local response=$(curl -s "$check_url")
        
        if [ $? -ne 0 ]; then
            print_error "Failed to check scan status"
            return 1
        fi
        
        local status=$(echo "$response" | jq -r '.status // empty' 2>/dev/null)
        
        print_info "Current scan status: $status"
        
        case "$status" in
            "READY")
                print_status "Scan completed successfully"
                echo "$response"
                return 0
                ;;
            "IN_PROGRESS"|"DNS")
                print_info "Scan in progress... waiting ${check_interval}s"
                sleep $check_interval
                wait_time=$((wait_time + check_interval))
                ;;
            "ERROR")
                local error_message=$(echo "$response" | jq -r '.statusMessage // "Unknown error"' 2>/dev/null)
                print_error "SSL Labs scan failed: $error_message"
                return 1
                ;;
            *)
                print_warning "Unexpected status: $status"
                sleep $check_interval
                wait_time=$((wait_time + check_interval))
                ;;
        esac
    done
    
    print_error "Scan timeout after ${MAX_WAIT_TIME} seconds"
    return 1
}

# Function to parse and display results
parse_ssl_labs_results() {
    local results="$1"
    local domain="$2"
    
    print_status "Parsing SSL Labs results for $domain"
    
    # Check if jq is available
    if ! command -v jq >/dev/null 2>&1; then
        print_warning "jq not available, displaying raw results"
        echo "$results" > "${REPORT_DIR}/ssl-labs-${domain}-$(date +%Y%m%d-%H%M%S)-raw.json"
        return 1
    fi
    
    # Extract key information
    local overall_grade=$(echo "$results" | jq -r '.endpoints[0].grade // "N/A"')
    local ip_address=$(echo "$results" | jq -r '.endpoints[0].ipAddress // "N/A"')
    local server_name=$(echo "$results" | jq -r '.endpoints[0].serverName // "N/A"')
    
    # Certificate information
    local cert_subject=$(echo "$results" | jq -r '.endpoints[0].details.cert.subject // "N/A"')
    local cert_issuer=$(echo "$results" | jq -r '.endpoints[0].details.cert.issuerSubject // "N/A"')
    local cert_not_after=$(echo "$results" | jq -r '.endpoints[0].details.cert.notAfter // "N/A"')
    
    # Convert timestamp to readable date
    local cert_expiry="N/A"
    if [ "$cert_not_after" != "N/A" ] && [ "$cert_not_after" != "null" ]; then
        cert_expiry=$(date -d "@$((cert_not_after / 1000))" '+%Y-%m-%d %H:%M:%S UTC')
    fi
    
    # Protocol information
    local protocols=$(echo "$results" | jq -r '.endpoints[0].details.protocols[] | "\(.name) \(.version)"' 2>/dev/null | tr '\n' ', ' | sed 's/, $//')
    
    # Cipher suites (top 3)
    local cipher_suites=$(echo "$results" | jq -r '.endpoints[0].details.suites.list[0:3][] | .name' 2>/dev/null | tr '\n' ', ' | sed 's/, $//')
    
    # Security features
    local hsts=$(echo "$results" | jq -r '.endpoints[0].details.hstsPolicy.status // "Not Set"')
    local forward_secrecy=$(echo "$results" | jq -r '.endpoints[0].details.forwardSecrecy // "N/A"')
    
    # Create detailed report
    local report_file="${REPORT_DIR}/ssl-labs-${domain}-$(date +%Y%m%d-%H%M%S).txt"
    
    cat > "$report_file" << EOF
HD Tickets SSL Labs Security Assessment Report
===============================================

Domain: $domain
Test Date: $(date)
Overall Grade: $overall_grade
IP Address: $ip_address
Server Name: $server_name

Certificate Information:
-----------------------
Subject: $cert_subject
Issuer: $cert_issuer
Expires: $cert_expiry

Protocol Support:
----------------
$protocols

Top Cipher Suites:
-----------------
$cipher_suites

Security Features:
-----------------
HSTS: $hsts
Forward Secrecy: $forward_secrecy

Key Findings:
------------
EOF

    # Add grade-specific findings
    case "$overall_grade" in
        "A+"|"A"|"A-")
            echo "✅ Excellent SSL/TLS configuration" >> "$report_file"
            echo "✅ Strong security posture detected" >> "$report_file"
            ;;
        "B")
            echo "⚠️  Good configuration with minor issues" >> "$report_file"
            echo "⚠️  Consider security improvements" >> "$report_file"
            ;;
        "C"|"D"|"F")
            echo "❌ Significant security issues detected" >> "$report_file"
            echo "❌ Immediate attention required" >> "$report_file"
            ;;
        *)
            echo "ℹ️  Grade: $overall_grade - Review detailed results" >> "$report_file"
            ;;
    esac
    
    # Add recommendations
    cat >> "$report_file" << EOF

Recommendations:
---------------
1. Ensure all protocols are TLS 1.2 or higher
2. Disable weak cipher suites
3. Enable HSTS with appropriate max-age
4. Implement Certificate Transparency monitoring
5. Regular security testing and monitoring

Technical Details:
-----------------
EOF
    
    # Add raw JSON results
    echo "$results" | jq '.' >> "${report_file}.json" 2>/dev/null || echo "$results" >> "${report_file}.json"
    
    print_status "Report saved to: $report_file"
    
    # Display summary
    echo ""
    echo "🔒 SSL Labs Test Results Summary"
    echo "================================"
    echo "Domain: $domain"
    echo "Grade: $overall_grade"
    echo "Certificate Expires: $cert_expiry"
    echo "HSTS: $hsts"
    echo "Full Report: $report_file"
    echo ""
    
    # Return grade as exit code for automation
    case "$overall_grade" in
        "A+"|"A") return 0 ;;
        "A-"|"B") return 1 ;;
        *) return 2 ;;
    esac
}

# Function to run local SSL test as alternative
run_local_ssl_test() {
    local domain="$1"
    
    print_status "Running local SSL test for $domain"
    
    local report_file="${REPORT_DIR}/local-ssl-test-${domain}-$(date +%Y%m%d-%H%M%S).txt"
    
    cat > "$report_file" << EOF
HD Tickets Local SSL Test Report
===============================

Domain: $domain
Test Date: $(date)
Test Type: Local OpenSSL Analysis

Certificate Information:
-----------------------
EOF
    
    # Get certificate information
    local cert_info=$(echo | openssl s_client -connect "$domain:443" -servername "$domain" 2>/dev/null | openssl x509 -noout -text 2>/dev/null)
    
    if [ $? -eq 0 ]; then
        echo "✅ Certificate retrieved successfully" >> "$report_file"
        
        # Extract key information
        local subject=$(echo "$cert_info" | grep "Subject:" | head -1)
        local issuer=$(echo "$cert_info" | grep "Issuer:" | head -1)
        local validity=$(echo "$cert_info" | grep -A2 "Validity")
        
        cat >> "$report_file" << EOF
$subject
$issuer
$validity

Protocol Testing:
----------------
EOF
        
        # Test different TLS versions
        for version in ssl3 tls1 tls1_1 tls1_2 tls1_3; do
            if echo | openssl s_client -connect "$domain:443" -"$version" 2>/dev/null | grep -q "Verification: OK"; then
                echo "✅ $version: Supported" >> "$report_file"
            else
                echo "❌ $version: Not supported" >> "$report_file"
            fi
        done
        
    else
        echo "❌ Failed to retrieve certificate" >> "$report_file"
    fi
    
    print_status "Local SSL test completed: $report_file"
}

# Main function
main() {
    print_status "HD Tickets SSL Labs Security Assessment Started"
    
    # Check if this is a local/private domain
    if [[ "$DOMAIN" == "hdtickets.local" ]] || [[ "$DOMAIN" == *".local" ]]; then
        print_warning "Domain appears to be local/private: $DOMAIN"
        print_info "SSL Labs requires publicly accessible domains"
        print_info "Running local SSL test instead..."
        
        run_local_ssl_test "$DOMAIN"
        
        print_status "Local SSL testing completed"
        return 0
    fi
    
    # For public domains, run SSL Labs test
    print_info "Testing public domain: $DOMAIN"
    
    # Check if jq is installed
    if ! command -v jq >/dev/null 2>&1; then
        print_error "jq is required for SSL Labs API integration"
        print_info "Install with: sudo apt install jq"
        print_info "Falling back to local SSL test..."
        
        run_local_ssl_test "$DOMAIN"
        return 1
    fi
    
    # Check domain accessibility
    if ! check_domain_accessibility "$DOMAIN"; then
        print_warning "Domain not accessible for SSL Labs testing"
        print_info "Running local SSL test instead..."
        
        run_local_ssl_test "$DOMAIN"
        return 1
    fi
    
    # Start SSL Labs scan
    if ! start_ssl_labs_scan "$DOMAIN"; then
        print_error "Failed to start SSL Labs scan"
        return 1
    fi
    
    # Wait for completion and get results
    local results=$(wait_for_scan_completion "$DOMAIN")
    local scan_exit_code=$?
    
    if [ $scan_exit_code -ne 0 ]; then
        print_error "SSL Labs scan failed"
        return 1
    fi
    
    # Parse and display results
    parse_ssl_labs_results "$results" "$DOMAIN"
    local parse_exit_code=$?
    
    print_status "SSL Labs security assessment completed"
    
    return $parse_exit_code
}

# Display usage information
usage() {
    echo "Usage: $0 [DOMAIN]"
    echo ""
    echo "HD Tickets SSL Labs Security Assessment"
    echo "Tests SSL/TLS configuration using SSL Labs API or local OpenSSL"
    echo ""
    echo "Options:"
    echo "  DOMAIN    Domain to test (defaults to hdtickets.local)"
    echo ""
    echo "Examples:"
    echo "  $0                          # Test hdtickets.local"
    echo "  $0 example.com              # Test example.com"
    echo ""
    echo "Reports are saved to: $REPORT_DIR"
    echo "Logs are saved to: $LOG_FILE"
}

# Handle command line arguments
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    usage
    exit 0
fi

if [ ! -z "$1" ]; then
    DOMAIN="$1"
fi

# Run main function
main "$@"
