#!/bin/bash

# HD Tickets Complete SSL Testing Suite
# Sports Events Entry Tickets Monitoring System - Production Readiness Test
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

set -e

# Configuration
DEFAULT_DOMAIN="hdtickets.local"
DOMAIN="${1:-$DEFAULT_DOMAIN}"
LOG_FILE="/var/log/hdtickets-ssl-complete.log"
REPORT_DIR="/var/log/ssl-complete-reports"
TIMESTAMP=$(date +%Y%m%d-%H%M%S)

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# Create directories
mkdir -p "$REPORT_DIR"
mkdir -p "$(dirname "$LOG_FILE")"

# Initialize report
REPORT_FILE="$REPORT_DIR/ssl-complete-test-$TIMESTAMP.txt"

# Logging functions
log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

header() {
    echo ""
    echo -e "${CYAN}${BOLD}===============================================${NC}"
    echo -e "${CYAN}${BOLD} $1 ${NC}"
    echo -e "${CYAN}${BOLD}===============================================${NC}"
    echo ""
    log "=== $1 ==="
}

success() {
    echo -e "${GREEN}✅ $1${NC}"
    log "SUCCESS: $1"
}

warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
    log "WARNING: $1"
}

error() {
    echo -e "${RED}❌ $1${NC}"
    log "ERROR: $1"
}

info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
    log "INFO: $1"
}

detail() {
    echo -e "${PURPLE}   $1${NC}"
    log "DETAIL: $1"
}

# Test results tracking
declare -a test_results=()
declare -a warnings_list=()
declare -a errors_list=()

add_result() {
    local status="$1"
    local message="$2"
    test_results+=("$status: $message")
    
    case "$status" in
        "PASS") ;;
        "WARN") warnings_list+=("$message") ;;
        "FAIL") errors_list+=("$message") ;;
    esac
}

# SSL Labs Test (if domain is public)
test_ssl_labs() {
    header "SSL LABS SECURITY ASSESSMENT"
    
    info "Testing with SSL Labs API for domain: $DOMAIN"
    
    # Check if domain is local/private
    if [[ "$DOMAIN" == *".local" ]] || [[ "$DOMAIN" == "localhost" ]] || [[ "$DOMAIN" == "hdtickets.local" ]]; then
        warning "Domain $DOMAIN is local/private - SSL Labs testing not available"
        info "Running local SSL test instead..."
        
        /var/www/hdtickets/ssl-labs-test.sh "$DOMAIN" > /tmp/ssl-labs-local.log 2>&1
        if [ $? -eq 0 ]; then
            success "Local SSL test completed successfully"
            add_result "PASS" "Local SSL test completed"
        else
            warning "Local SSL test completed with warnings"
            add_result "WARN" "Local SSL test warnings"
        fi
        
        cat /tmp/ssl-labs-local.log | tail -10
        return 0
    fi
    
    # For public domains, attempt SSL Labs test
    if command -v jq >/dev/null 2>&1; then
        info "Running SSL Labs test for public domain..."
        /var/www/hdtickets/ssl-labs-test.sh "$DOMAIN" > /tmp/ssl-labs-full.log 2>&1
        local ssl_labs_result=$?
        
        case $ssl_labs_result in
            0) 
                success "SSL Labs test: Grade A+ or A"
                add_result "PASS" "SSL Labs Grade A/A+"
                ;;
            1) 
                warning "SSL Labs test: Grade A- or B"
                add_result "WARN" "SSL Labs Grade A-/B"
                ;;
            *) 
                error "SSL Labs test failed or Grade C/D/F"
                add_result "FAIL" "SSL Labs test failed"
                ;;
        esac
        
        cat /tmp/ssl-labs-full.log | tail -15
    else
        warning "jq not installed - SSL Labs API testing unavailable"
        add_result "WARN" "SSL Labs testing unavailable"
    fi
}

# Deployment verification
test_deployment() {
    header "DEPLOYMENT VERIFICATION"
    
    info "Running comprehensive deployment verification..."
    
    /var/www/hdtickets/verify-ssl-deployment.sh "$DOMAIN" > /tmp/deployment.log 2>&1
    local deploy_result=$?
    
    if [ $deploy_result -eq 0 ]; then
        success "Deployment verification passed"
        add_result "PASS" "Deployment verification"
    else
        error "Deployment verification failed"
        add_result "FAIL" "Deployment verification"
    fi
    
    # Extract key metrics from deployment log
    local tests_passed=$(grep "✅ Passed:" /tmp/deployment.log | head -1 | awk '{print $3}')
    local tests_failed=$(grep "❌ Failed:" /tmp/deployment.log | head -1 | awk '{print $3}')
    local tests_warnings=$(grep "⚠️  Warnings:" /tmp/deployment.log | head -1 | awk '{print $3}')
    
    detail "Tests passed: $tests_passed"
    detail "Tests failed: $tests_failed"  
    detail "Warnings: $tests_warnings"
    
    if [ "$tests_failed" != "0/7" ] && [ ! -z "$tests_failed" ]; then
        add_result "FAIL" "Deployment tests failed: $tests_failed"
    fi
    
    if [ "$tests_warnings" != "0" ] && [ ! -z "$tests_warnings" ]; then
        add_result "WARN" "Deployment warnings: $tests_warnings"
    fi
}

# Certificate expiry monitoring
test_certificate_expiry() {
    header "CERTIFICATE EXPIRY MONITORING"
    
    info "Running certificate expiry checks..."
    
    if [ -f "/var/www/hdtickets/check-cert-expiry.sh" ]; then
        /var/www/hdtickets/check-cert-expiry.sh --test > /tmp/cert-expiry.log 2>&1
        local expiry_result=$?
        
        if [ $expiry_result -eq 0 ]; then
            success "Certificate expiry check passed"
            add_result "PASS" "Certificate expiry monitoring"
        else
            warning "Certificate expiry check has warnings"
            add_result "WARN" "Certificate expiry warnings"
        fi
        
        # Extract expiry information
        local days_until_expiry=$(grep "days until expiry" /tmp/cert-expiry.log | head -1 | awk '{print $1}')
        if [ ! -z "$days_until_expiry" ]; then
            detail "Certificate expires in $days_until_expiry days"
            
            if [ "$days_until_expiry" -lt 30 ]; then
                add_result "WARN" "Certificate expires soon ($days_until_expiry days)"
            fi
        fi
    else
        warning "Certificate expiry monitoring script not found"
        add_result "WARN" "Expiry monitoring unavailable"
    fi
}

# Performance testing
test_performance() {
    header "SSL PERFORMANCE TESTING"
    
    info "Testing SSL/HTTPS performance..."
    
    # Test connection speed
    local start_time=$(date +%s.%3N)
    if curl -k -s --connect-timeout 10 "https://$DOMAIN/" >/dev/null 2>&1; then
        local end_time=$(date +%s.%3N)
        local connection_time=$(echo "$end_time - $start_time" | bc 2>/dev/null || echo "N/A")
        
        success "HTTPS connection successful"
        detail "Connection time: ${connection_time}s"
        add_result "PASS" "HTTPS connection performance"
        
        if command -v bc >/dev/null 2>&1 && [ "$connection_time" != "N/A" ]; then
            if (( $(echo "$connection_time > 2.0" | bc -l) )); then
                add_result "WARN" "Slow HTTPS connection (${connection_time}s)"
            fi
        fi
    else
        error "HTTPS connection failed"
        add_result "FAIL" "HTTPS connection failed"
    fi
    
    # Test cipher negotiation speed
    info "Testing cipher negotiation..."
    local cipher_test_start=$(date +%s.%3N)
    echo | openssl s_client -connect "$DOMAIN:443" -cipher ECDHE-RSA-AES128-GCM-SHA256 2>/dev/null | grep -q "Cipher"
    local cipher_result=$?
    local cipher_test_end=$(date +%s.%3N)
    
    if [ $cipher_result -eq 0 ]; then
        local cipher_time=$(echo "$cipher_test_end - $cipher_test_start" | bc 2>/dev/null || echo "N/A")
        success "Cipher negotiation successful"
        detail "Cipher negotiation time: ${cipher_time}s"
        add_result "PASS" "Cipher negotiation"
    else
        warning "Cipher negotiation test failed"
        add_result "WARN" "Cipher negotiation issues"
    fi
}

# Security compliance testing
test_security_compliance() {
    header "SECURITY COMPLIANCE TESTING"
    
    info "Testing security compliance..."
    
    # Test for weak protocols
    local weak_protocols=("ssl2" "ssl3" "tls1")
    local weak_found=false
    
    for protocol in "${weak_protocols[@]}"; do
        if echo | openssl s_client -connect "$DOMAIN:443" -"$protocol" 2>/dev/null | grep -q "Protocol"; then
            warning "Weak protocol $protocol is supported"
            add_result "WARN" "Weak protocol $protocol supported"
            weak_found=true
        fi
    done
    
    if [ "$weak_found" = false ]; then
        success "No weak protocols detected"
        add_result "PASS" "Weak protocol check"
    fi
    
    # Test for strong protocols
    local strong_protocols=("tls1_2" "tls1_3")
    local strong_found=false
    
    for protocol in "${strong_protocols[@]}"; do
        if echo | openssl s_client -connect "$DOMAIN:443" -"$protocol" 2>/dev/null | grep -q "Protocol"; then
            success "Strong protocol $protocol is supported"
            strong_found=true
        fi
    done
    
    if [ "$strong_found" = true ]; then
        add_result "PASS" "Strong protocol support"
    else
        error "No strong protocols (TLS 1.2/1.3) supported"
        add_result "FAIL" "No strong protocols"
    fi
    
    # Test cipher strength
    info "Testing cipher strength..."
    local cipher_info=$(echo | openssl s_client -connect "$DOMAIN:443" 2>/dev/null | grep "Cipher is")
    
    if echo "$cipher_info" | grep -qi "ECDHE\|DHE"; then
        success "Perfect Forward Secrecy (PFS) enabled"
        add_result "PASS" "Perfect Forward Secrecy"
    else
        warning "Perfect Forward Secrecy (PFS) not detected"
        add_result "WARN" "PFS not detected"
    fi
    
    detail "$cipher_info"
}

# Application-specific tests
test_laravel_ssl() {
    header "LARAVEL SSL INTEGRATION TESTS"
    
    info "Testing Laravel-specific SSL functionality..."
    
    # Test Laravel routes over HTTPS
    local routes=("/" "/login" "/dashboard")
    local route_success=true
    
    for route in "${routes[@]}"; do
        local status_code=$(curl -k -s -o /dev/null -w "%{http_code}" "https://$DOMAIN$route" 2>/dev/null)
        
        case "$status_code" in
            200|302|404)
                detail "Route $route: HTTP $status_code (OK)"
                ;;
            *)
                warning "Route $route: HTTP $status_code (Unexpected)"
                route_success=false
                ;;
        esac
    done
    
    if [ "$route_success" = true ]; then
        success "Laravel route testing successful"
        add_result "PASS" "Laravel routes over HTTPS"
    else
        warning "Some Laravel routes have issues"
        add_result "WARN" "Laravel route issues"
    fi
    
    # Test for Laravel-specific headers
    local headers=$(curl -k -s -I "https://$DOMAIN/" 2>/dev/null)
    
    if echo "$headers" | grep -qi "laravel\|x-powered-by.*php"; then
        info "Laravel/PHP headers detected"
        detail "Application framework confirmed"
    fi
    
    # Test CSRF token functionality (basic check)
    local csrf_present=$(curl -k -s "https://$DOMAIN/login" 2>/dev/null | grep -c "csrf\|_token" || echo "0")
    
    if [ "$csrf_present" -gt 0 ]; then
        success "CSRF protection appears to be active"
        add_result "PASS" "CSRF protection"
    else
        info "CSRF protection check inconclusive"
    fi
}

# Generate comprehensive report
generate_final_report() {
    header "COMPREHENSIVE SSL TESTING REPORT"
    
    local total_tests=${#test_results[@]}
    local passed_tests=$(printf '%s\n' "${test_results[@]}" | grep -c "PASS:" || echo "0")
    local warning_tests=$(printf '%s\n' "${test_results[@]}" | grep -c "WARN:" || echo "0")
    local failed_tests=$(printf '%s\n' "${test_results[@]}" | grep -c "FAIL:" || echo "0")
    
    # Calculate overall grade
    local overall_status
    local overall_color
    
    if [ "$failed_tests" -eq 0 ]; then
        if [ "$warning_tests" -eq 0 ]; then
            overall_status="🎉 PRODUCTION READY"
            overall_color="$GREEN"
        else
            overall_status="⚠️  FUNCTIONAL WITH WARNINGS"
            overall_color="$YELLOW"
        fi
    else
        overall_status="❌ REQUIRES ATTENTION"
        overall_color="$RED"
    fi
    
    # Display results
    echo ""
    echo "📊 FINAL SSL TESTING RESULTS:"
    echo "=============================="
    echo "Domain: $DOMAIN"
    echo "Test Date: $(date)"
    echo "Total Tests: $total_tests"
    echo "✅ Passed: $passed_tests"
    echo "⚠️  Warnings: $warning_tests"
    echo "❌ Failed: $failed_tests"
    echo ""
    echo -e "Overall Status: ${overall_color}${overall_status}${NC}"
    echo ""
    
    # Create detailed report file
    cat > "$REPORT_FILE" << EOF
HD Tickets Complete SSL Testing Report
=====================================

Test Summary:
------------
Domain: $DOMAIN
Test Date: $(date)
Test Duration: N/A
Overall Status: $overall_status

Results Breakdown:
-----------------
Total Tests: $total_tests
Passed: $passed_tests
Warnings: $warning_tests
Failed: $failed_tests

Test Categories:
---------------
EOF

    # Add test results to report
    printf '%s\n' "${test_results[@]}" >> "$REPORT_FILE"
    
    cat >> "$REPORT_FILE" << EOF

Warnings Summary:
----------------
EOF
    
    if [ ${#warnings_list[@]} -eq 0 ]; then
        echo "No warnings detected." >> "$REPORT_FILE"
    else
        printf '- %s\n' "${warnings_list[@]}" >> "$REPORT_FILE"
    fi
    
    cat >> "$REPORT_FILE" << EOF

Errors Summary:
--------------
EOF
    
    if [ ${#errors_list[@]} -eq 0 ]; then
        echo "No errors detected." >> "$REPORT_FILE"
    else
        printf '- %s\n' "${errors_list[@]}" >> "$REPORT_FILE"
    fi
    
    cat >> "$REPORT_FILE" << EOF

Production Readiness Assessment:
-------------------------------
EOF

    if [ "$failed_tests" -eq 0 ] && [ "$warning_tests" -eq 0 ]; then
        cat >> "$REPORT_FILE" << EOF
✅ READY FOR PRODUCTION DEPLOYMENT

Your HD Tickets SSL configuration is fully ready for production:
- All security tests passed
- No warnings or issues detected
- SSL/TLS implementation follows best practices
- Certificate management is properly configured
- Performance is acceptable

Next Steps:
1. 🚀 Deploy to production environment
2. 📋 Schedule regular SSL monitoring
3. 🔄 Verify automated certificate renewal
4. 📊 Set up SSL monitoring alerts
EOF
    elif [ "$failed_tests" -eq 0 ]; then
        cat >> "$REPORT_FILE" << EOF
⚠️  FUNCTIONAL BUT NEEDS ATTENTION

Your HD Tickets SSL configuration is functional but has warnings:
- Core SSL functionality works correctly
- Some best practices could be improved
- Review and address warnings before production

Recommended Actions:
1. 🔍 Review and address all warnings
2. 🛡️  Enhance security headers configuration
3. 📋 Test with public SSL testing tools
4. 🎯 Optimize performance if needed
EOF
    else
        cat >> "$REPORT_FILE" << EOF
❌ NOT READY FOR PRODUCTION

Your HD Tickets SSL configuration has critical issues:
- Some tests failed and need immediate attention
- Security or functionality issues detected
- Do not deploy to production until resolved

Required Actions:
1. 🚨 Address all failed tests immediately
2. 🔧 Fix SSL configuration issues
3. 🛠️  Verify certificate installation
4. 🔄 Re-run tests after fixes
EOF
    fi
    
    cat >> "$REPORT_FILE" << EOF

Technical Details:
-----------------
Full test logs available at: $LOG_FILE
SSL Labs reports: /var/log/ssl-labs-reports/
Deployment reports: /var/log/ssl-deployment-reports/
Monitoring reports: /var/log/ssl-monitoring-reports/

For support or questions, contact the HD Tickets development team.

Report generated by HD Tickets SSL Testing Suite
Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
EOF

    success "Complete test report saved to: $REPORT_FILE"
    
    return $failed_tests
}

# Main execution
main() {
    header "HD TICKETS COMPLETE SSL TESTING SUITE"
    
    info "Starting comprehensive SSL testing for domain: $DOMAIN"
    info "Timestamp: $(date)"
    info "Report will be saved to: $REPORT_FILE"
    
    # Run all test suites
    test_ssl_labs
    test_deployment
    test_certificate_expiry
    test_performance
    test_security_compliance
    test_laravel_ssl
    
    # Generate final report
    generate_final_report
    
    local final_result=$?
    
    echo ""
    if [ $final_result -eq 0 ]; then
        success "All SSL tests completed successfully! 🎉"
    else
        warning "SSL tests completed with issues that need attention."
    fi
    
    echo ""
    echo "📄 Complete report: $REPORT_FILE"
    echo "📋 Full logs: $LOG_FILE"
    
    return $final_result
}

# Help function
show_help() {
    echo "HD Tickets Complete SSL Testing Suite"
    echo ""
    echo "Usage: $0 [DOMAIN]"
    echo ""
    echo "This script performs comprehensive SSL/TLS testing including:"
    echo "  - SSL Labs security assessment (for public domains)"
    echo "  - Deployment verification"
    echo "  - Certificate expiry monitoring"
    echo "  - Performance testing"
    echo "  - Security compliance testing"
    echo "  - Laravel-specific SSL integration tests"
    echo ""
    echo "Options:"
    echo "  DOMAIN    Domain to test (default: hdtickets.local)"
    echo "  -h, --help    Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0                    # Test hdtickets.local"
    echo "  $0 example.com        # Test example.com"
    echo ""
    echo "Reports saved to: $REPORT_DIR"
    echo "Logs saved to: $LOG_FILE"
}

# Handle arguments
case "${1:-}" in
    -h|--help)
        show_help
        exit 0
        ;;
    *)
        main "$@"
        ;;
esac
