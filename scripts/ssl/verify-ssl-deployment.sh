#!/bin/bash

# HD Tickets SSL Deployment Verification Script
# Sports Events Entry Tickets Monitoring System - Complete SSL Verification
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration
DOMAIN="hdtickets.local"
APACHE_CONFIG_DIR="/etc/apache2"
SSL_CONFIG_FILE="$APACHE_CONFIG_DIR/sites-available/hdtickets-ssl-production.conf"
CERT_DIR="/etc/ssl/certs"
KEY_DIR="/etc/ssl/private"
LOG_FILE="/var/log/hdtickets-ssl-verification.log"
REPORT_DIR="/var/log/ssl-deployment-reports"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m'

# Create directories
mkdir -p "$REPORT_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Logging function
log_message() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo -e "${timestamp} [${level}] ${message}" | tee -a "$LOG_FILE"
}

print_header() {
    echo -e "\n${CYAN}========================================${NC}"
    echo -e "${CYAN} $1 ${NC}"
    echo -e "${CYAN}========================================${NC}\n"
    log_message "INFO" "=== $1 ==="
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
    log_message "PASS" "$1"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
    log_message "WARN" "$1"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
    log_message "FAIL" "$1"
}

print_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
    log_message "INFO" "$1"
}

print_detail() {
    echo -e "${PURPLE}   $1${NC}"
    log_message "DETAIL" "$1"
}

# Test counters
TESTS_TOTAL=0
TESTS_PASSED=0
TESTS_FAILED=0
TESTS_WARNINGS=0

run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_result="${3:-0}"
    
    TESTS_TOTAL=$((TESTS_TOTAL + 1))
    
    print_info "Testing: $test_name"
    
    if eval "$test_command" >/dev/null 2>&1; then
        local result=$?
        if [ $result -eq $expected_result ]; then
            print_success "$test_name"
            TESTS_PASSED=$((TESTS_PASSED + 1))
            return 0
        else
            print_error "$test_name (unexpected result: $result)"
            TESTS_FAILED=$((TESTS_FAILED + 1))
            return 1
        fi
    else
        print_error "$test_name"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

# Check Apache installation and modules
check_apache_setup() {
    print_header "APACHE SSL SETUP VERIFICATION"
    
    # Check if Apache is installed
    run_test "Apache2 installation" "command -v apache2"
    
    # Check if Apache is running
    run_test "Apache2 service status" "systemctl is-active apache2"
    
    # Check SSL module
    run_test "Apache SSL module enabled" "apache2ctl -M | grep -q ssl_module"
    
    # Check HTTP/2 module
    run_test "Apache HTTP/2 module enabled" "apache2ctl -M | grep -q http2_module"
    
    # Check headers module
    run_test "Apache Headers module enabled" "apache2ctl -M | grep -q headers_module"
    
    # Check rewrite module
    run_test "Apache Rewrite module enabled" "apache2ctl -M | grep -q rewrite_module"
    
    print_detail "Loaded Apache modules:"
    apache2ctl -M | grep -E "(ssl|http2|headers|rewrite)" | sed 's/^/   /'
}

# Check SSL configuration files
check_ssl_configuration() {
    print_header "SSL CONFIGURATION FILES"
    
    # Check if SSL site configuration exists
    if [ -f "$SSL_CONFIG_FILE" ]; then
        print_success "SSL site configuration found"
        
        # Check configuration syntax
        run_test "SSL configuration syntax" "apache2ctl -t"
        
        # Check if site is enabled
        if [ -L "/etc/apache2/sites-enabled/$(basename "$SSL_CONFIG_FILE")" ]; then
            print_success "SSL site is enabled"
        else
            print_warning "SSL site is not enabled"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Analyze configuration content
        print_detail "Configuration analysis:"
        
        if grep -q "SSLEngine on" "$SSL_CONFIG_FILE"; then
            print_detail "✅ SSL Engine enabled"
        else
            print_detail "❌ SSL Engine not found"
        fi
        
        if grep -q "SSLProtocol" "$SSL_CONFIG_FILE"; then
            local ssl_protocols=$(grep "SSLProtocol" "$SSL_CONFIG_FILE" | head -1)
            print_detail "🔒 $ssl_protocols"
        fi
        
        if grep -q "SSLCipherSuite" "$SSL_CONFIG_FILE"; then
            print_detail "🔐 Custom cipher suite configured"
        fi
        
        if grep -q "Header always set Strict-Transport-Security" "$SSL_CONFIG_FILE"; then
            print_detail "🛡️  HSTS enabled"
        else
            print_detail "⚠️  HSTS not found"
        fi
        
    else
        print_error "SSL site configuration not found: $SSL_CONFIG_FILE"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

# Check SSL certificates
check_ssl_certificates() {
    print_header "SSL CERTIFICATES VERIFICATION"
    
    # Extract certificate paths from configuration
    local cert_file=$(grep "SSLCertificateFile" "$SSL_CONFIG_FILE" | awk '{print $2}' | head -1)
    local key_file=$(grep "SSLCertificateKeyFile" "$SSL_CONFIG_FILE" | awk '{print $2}' | head -1)
    local chain_file=$(grep "SSLCertificateChainFile" "$SSL_CONFIG_FILE" | awk '{print $2}' | head -1)
    
    print_detail "Certificate file: $cert_file"
    print_detail "Key file: $key_file"
    print_detail "Chain file: $chain_file"
    
    # Check certificate file exists
    if [ -f "$cert_file" ]; then
        print_success "Certificate file exists"
        
        # Check certificate validity
        if openssl x509 -in "$cert_file" -noout -checkend 86400 >/dev/null 2>&1; then
            print_success "Certificate is valid (expires in >24h)"
            
            # Get certificate details
            local cert_subject=$(openssl x509 -in "$cert_file" -noout -subject | sed 's/subject=//')
            local cert_issuer=$(openssl x509 -in "$cert_file" -noout -issuer | sed 's/issuer=//')
            local cert_dates=$(openssl x509 -in "$cert_file" -noout -dates)
            
            print_detail "Subject: $cert_subject"
            print_detail "Issuer: $cert_issuer"
            print_detail "$cert_dates"
        else
            print_error "Certificate is expired or invalid"
            TESTS_FAILED=$((TESTS_FAILED + 1))
        fi
    else
        print_error "Certificate file not found: $cert_file"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    
    # Check private key file
    if [ -f "$key_file" ]; then
        print_success "Private key file exists"
        
        # Check key permissions
        local key_perms=$(stat -c "%a" "$key_file")
        if [ "$key_perms" = "600" ] || [ "$key_perms" = "400" ]; then
            print_success "Private key has secure permissions ($key_perms)"
        else
            print_warning "Private key permissions may be too open ($key_perms)"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Verify key matches certificate
        if [ -f "$cert_file" ]; then
            local cert_hash=$(openssl x509 -in "$cert_file" -noout -modulus | openssl md5)
            local key_hash=$(openssl rsa -in "$key_file" -noout -modulus 2>/dev/null | openssl md5)
            
            if [ "$cert_hash" = "$key_hash" ]; then
                print_success "Certificate and private key match"
            else
                print_error "Certificate and private key do not match"
                TESTS_FAILED=$((TESTS_FAILED + 1))
            fi
        fi
    else
        print_error "Private key file not found: $key_file"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
    
    # Check chain file if specified
    if [ ! -z "$chain_file" ] && [ "$chain_file" != "/dev/null" ]; then
        if [ -f "$chain_file" ]; then
            print_success "Certificate chain file exists"
        else
            print_warning "Certificate chain file not found: $chain_file"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
    fi
}

# Test SSL connectivity
check_ssl_connectivity() {
    print_header "SSL CONNECTIVITY TESTING"
    
    # Test HTTPS connection
    print_info "Testing HTTPS connection to $DOMAIN"
    
    if curl -k -s --connect-timeout 10 "https://$DOMAIN/" >/dev/null 2>&1; then
        print_success "HTTPS connection successful"
        
        # Test SSL handshake
        local ssl_info=$(echo | openssl s_client -connect "$DOMAIN:443" -servername "$DOMAIN" 2>/dev/null | head -20)
        
        if echo "$ssl_info" | grep -q "Verification: OK"; then
            print_success "SSL handshake completed successfully"
        else
            print_warning "SSL handshake completed with warnings"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Check supported protocols
        print_detail "Protocol support:"
        for protocol in tls1 tls1_1 tls1_2 tls1_3; do
            if echo | openssl s_client -connect "$DOMAIN:443" -"$protocol" 2>/dev/null | grep -q "Protocol"; then
                print_detail "✅ $protocol: Supported"
            else
                print_detail "❌ $protocol: Not supported"
            fi
        done
        
    else
        print_error "HTTPS connection failed"
        TESTS_FAILED=$((TESTS_FAILED + 1))
        return 1
    fi
}

# Check security headers
check_security_headers() {
    print_header "SECURITY HEADERS VERIFICATION"
    
    print_info "Testing security headers for $DOMAIN"
    
    # Get headers
    local headers_output=$(curl -k -s -I "https://$DOMAIN/" 2>/dev/null)
    
    if [ $? -eq 0 ]; then
        print_success "Successfully retrieved headers"
        
        # Check HSTS
        if echo "$headers_output" | grep -qi "Strict-Transport-Security"; then
            local hsts_header=$(echo "$headers_output" | grep -i "Strict-Transport-Security" | head -1)
            print_success "HSTS header present"
            print_detail "$hsts_header"
        else
            print_warning "HSTS header missing"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Check X-Frame-Options
        if echo "$headers_output" | grep -qi "X-Frame-Options"; then
            local xfo_header=$(echo "$headers_output" | grep -i "X-Frame-Options" | head -1)
            print_success "X-Frame-Options header present"
            print_detail "$xfo_header"
        else
            print_warning "X-Frame-Options header missing"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Check X-Content-Type-Options
        if echo "$headers_output" | grep -qi "X-Content-Type-Options"; then
            print_success "X-Content-Type-Options header present"
        else
            print_warning "X-Content-Type-Options header missing"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
        # Check Content-Security-Policy
        if echo "$headers_output" | grep -qi "Content-Security-Policy"; then
            print_success "Content-Security-Policy header present"
        else
            print_warning "Content-Security-Policy header missing"
            TESTS_WARNINGS=$((TESTS_WARNINGS + 1))
        fi
        
    else
        print_error "Failed to retrieve headers"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
}

# Check Laravel application
check_laravel_app() {
    print_header "LARAVEL APPLICATION VERIFICATION"
    
    # Check if Laravel is accessible via HTTPS
    print_info "Testing Laravel application access"
    
    local app_response=$(curl -k -s "https://$DOMAIN/" 2>/dev/null)
    
    if [ $? -eq 0 ]; then
        print_success "Laravel application accessible via HTTPS"
        
        # Check if it looks like a Laravel response
        if echo "$app_response" | grep -qi "laravel\|blade\|csrf"; then
            print_success "Laravel framework detected in response"
        else
            print_info "Response received (Laravel detection inconclusive)"
        fi
        
        # Check for common Laravel routes
        local routes_to_test=("/" "/login" "/register" "/dashboard")
        
        for route in "${routes_to_test[@]}"; do
            local status_code=$(curl -k -s -o /dev/null -w "%{http_code}" "https://$DOMAIN$route")
            
            case "$status_code" in
                200|302|404)
                    print_detail "Route $route: HTTP $status_code (OK)"
                    ;;
                *)
                    print_detail "Route $route: HTTP $status_code (Unexpected)"
                    ;;
            esac
        done
        
    else
        print_error "Laravel application not accessible"
        TESTS_FAILED=$((TESTS_FAILED + 1))
    fi
}

# Generate final report
generate_final_report() {
    print_header "DEPLOYMENT VERIFICATION SUMMARY"
    
    local report_file="$REPORT_DIR/ssl-deployment-$(date +%Y%m%d-%H%M%S).txt"
    
    cat > "$report_file" << EOF
HD Tickets SSL Deployment Verification Report
=============================================

Test Date: $(date)
Domain: $DOMAIN
Total Tests: $TESTS_TOTAL
Passed: $TESTS_PASSED
Failed: $TESTS_FAILED
Warnings: $TESTS_WARNINGS

Overall Status: $(
    if [ $TESTS_FAILED -eq 0 ]; then
        if [ $TESTS_WARNINGS -eq 0 ]; then
            echo "✅ EXCELLENT - All tests passed"
        else
            echo "⚠️  GOOD - All tests passed with warnings"
        fi
    else
        echo "❌ NEEDS ATTENTION - Some tests failed"
    fi
)

Test Categories:
- Apache SSL Setup: $([ $TESTS_FAILED -eq 0 ] && echo "✅ PASS" || echo "❌ ISSUES DETECTED")
- SSL Configuration: $([ -f "$SSL_CONFIG_FILE" ] && echo "✅ CONFIGURED" || echo "❌ MISSING")
- SSL Certificates: $([ $TESTS_FAILED -eq 0 ] && echo "✅ VALID" || echo "⚠️ CHECK REQUIRED")
- SSL Connectivity: $([ $TESTS_FAILED -eq 0 ] && echo "✅ WORKING" || echo "❌ ISSUES")
- Security Headers: $([ $TESTS_WARNINGS -eq 0 ] && echo "✅ COMPLETE" || echo "⚠️ INCOMPLETE")
- Laravel Application: $([ $TESTS_FAILED -eq 0 ] && echo "✅ ACCESSIBLE" || echo "❌ ISSUES")

Next Steps:
$(
    if [ $TESTS_FAILED -eq 0 ] && [ $TESTS_WARNINGS -eq 0 ]; then
        echo "🎉 SSL deployment is ready for production!"
        echo "📋 Consider scheduling regular SSL monitoring"
        echo "🔄 Set up automated certificate renewal"
    elif [ $TESTS_FAILED -eq 0 ]; then
        echo "✅ SSL deployment is functional"
        echo "⚠️  Address warnings for optimal security"
        echo "📋 Review security headers configuration"
    else
        echo "🔧 Address failed tests before production deployment"
        echo "📞 Check Apache error logs for details"
        echo "🛠️  Verify certificate and key files"
    fi
)

Full log available at: $LOG_FILE
EOF
    
    echo ""
    echo "📊 FINAL RESULTS:"
    echo "================="
    echo "✅ Passed: $TESTS_PASSED/$TESTS_TOTAL"
    echo "❌ Failed: $TESTS_FAILED/$TESTS_TOTAL"
    echo "⚠️  Warnings: $TESTS_WARNINGS"
    echo ""
    
    if [ $TESTS_FAILED -eq 0 ]; then
        if [ $TESTS_WARNINGS -eq 0 ]; then
            echo -e "${GREEN}🎉 SSL DEPLOYMENT READY FOR PRODUCTION! 🎉${NC}"
        else
            echo -e "${YELLOW}⚠️  SSL DEPLOYMENT FUNCTIONAL WITH WARNINGS${NC}"
        fi
    else
        echo -e "${RED}❌ SSL DEPLOYMENT NEEDS ATTENTION${NC}"
    fi
    
    echo ""
    echo "📄 Full report saved to: $report_file"
    
    return $TESTS_FAILED
}

# Main execution
main() {
    print_header "HD TICKETS SSL DEPLOYMENT VERIFICATION"
    
    print_info "Starting comprehensive SSL deployment verification"
    print_info "Domain: $DOMAIN"
    print_info "Timestamp: $(date)"
    
    # Run all verification checks
    check_apache_setup
    check_ssl_configuration
    check_ssl_certificates
    check_ssl_connectivity
    check_security_headers
    check_laravel_app
    
    # Generate final report
    generate_final_report
}

# Handle command line arguments
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    echo "Usage: $0 [DOMAIN]"
    echo ""
    echo "HD Tickets SSL Deployment Verification"
    echo "Comprehensive testing of SSL/TLS configuration and security"
    echo ""
    echo "Options:"
    echo "  DOMAIN    Domain to test (defaults to hdtickets.local)"
    echo ""
    echo "Examples:"
    echo "  $0                          # Test hdtickets.local"
    echo "  $0 example.com              # Test example.com"
    echo ""
    exit 0
fi

if [ ! -z "$1" ]; then
    DOMAIN="$1"
fi

# Run main function
main "$@"
