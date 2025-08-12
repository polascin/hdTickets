#!/bin/bash

# HD Tickets SSL Status and Monitoring Script
# Sports Events Entry Tickets Monitoring System - SSL Health Check
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle

echo "=================================="
echo "HD Tickets SSL Configuration Status"
echo "=================================="
echo ""

# Check if SSL certificate exists
if [ -f "/etc/ssl/hdtickets/hdtickets.local.crt" ]; then
    echo "✓ SSL Certificate Found"
    
    # Certificate details
    echo ""
    echo "Certificate Details:"
    echo "-------------------"
    openssl x509 -in /etc/ssl/hdtickets/hdtickets.local.crt -noout -subject -dates -issuer
    
    # Certificate expiry check
    echo ""
    expiry_date=$(openssl x509 -in /etc/ssl/hdtickets/hdtickets.local.crt -noout -enddate | cut -d= -f2)
    expiry_timestamp=$(date -d "$expiry_date" +%s)
    current_timestamp=$(date +%s)
    days_until_expiry=$(( ($expiry_timestamp - $current_timestamp) / 86400 ))
    
    if [ $days_until_expiry -lt 30 ]; then
        echo "⚠️  Certificate expires in $days_until_expiry days - Consider renewal"
    else
        echo "✓ Certificate valid for $days_until_expiry days"
    fi
else
    echo "❌ SSL Certificate Not Found"
fi

echo ""
echo "Apache SSL Configuration:"
echo "------------------------"

# Check if SSL module is enabled
if apache2ctl -M | grep -q ssl_module; then
    echo "✓ SSL Module Enabled"
else
    echo "❌ SSL Module Not Enabled"
fi

# Check if site is enabled
if [ -f "/etc/apache2/sites-enabled/hdtickets-ssl.conf" ]; then
    echo "✓ SSL Site Configuration Enabled"
else
    echo "❌ SSL Site Configuration Not Enabled"
fi

echo ""
echo "SSL Connection Test:"
echo "-------------------"

# Test HTTPS connection
if curl -k -s -I https://hdtickets.local/ > /dev/null 2>&1; then
    echo "✓ HTTPS Connection Successful"
    
    # Check security headers
    echo ""
    echo "Security Headers:"
    echo "----------------"
    
    headers=$(curl -k -s -I https://hdtickets.local/)
    
    if echo "$headers" | grep -q "Strict-Transport-Security:"; then
        hsts_header=$(echo "$headers" | grep "Strict-Transport-Security:" | cut -d: -f2-)
        echo "✓ HSTS Header: $hsts_header"
    else
        echo "❌ HSTS Header Missing"
    fi
    
    if echo "$headers" | grep -q "Content-Security-Policy:"; then
        csp_preview=$(echo "$headers" | grep "Content-Security-Policy:" | cut -d: -f2- | head -c 50)
        echo "✓ CSP Header: ${csp_preview}..."
    else
        echo "❌ CSP Header Missing"
    fi
    
    if echo "$headers" | grep -q "X-Frame-Options:"; then
        frame_options=$(echo "$headers" | grep "X-Frame-Options:" | cut -d: -f2-)
        echo "✓ X-Frame-Options: $frame_options"
    else
        echo "❌ X-Frame-Options Missing"
    fi
    
else
    echo "❌ HTTPS Connection Failed"
fi

echo ""
echo "HTTP to HTTPS Redirect Test:"
echo "---------------------------"

# Test HTTP redirect
redirect_response=$(curl -s -I http://hdtickets.local/ | head -1)
if echo "$redirect_response" | grep -q "301\|302"; then
    echo "✓ HTTP to HTTPS Redirect Working"
    redirect_location=$(curl -s -I http://hdtickets.local/ | grep "Location:" | cut -d: -f2-)
    echo "  Redirect Location: $redirect_location"
else
    echo "❌ HTTP to HTTPS Redirect Not Working"
fi

echo ""
echo "SSL/TLS Protocol Analysis:"
echo "-------------------------"

# Test SSL protocols and ciphers
ssl_info=$(openssl s_client -connect hdtickets.local:443 -servername hdtickets.local < /dev/null 2>/dev/null)

if echo "$ssl_info" | grep -q "Protocol.*TLSv1.3"; then
    echo "✓ TLS 1.3 Supported"
elif echo "$ssl_info" | grep -q "Protocol.*TLSv1.2"; then
    echo "✓ TLS 1.2 Supported"
else
    echo "⚠️  Older TLS Protocol in Use"
fi

cipher=$(echo "$ssl_info" | grep "Cipher.*:" | cut -d: -f2-)
if [ ! -z "$cipher" ]; then
    echo "✓ Cipher Suite: $cipher"
fi

echo ""
echo "Laravel Application Status:"
echo "--------------------------"

# Check if Laravel app responds over HTTPS
if curl -k -s https://hdtickets.local/ | grep -q "HD Tickets"; then
    echo "✓ Laravel Application Responding over HTTPS"
else
    echo "❌ Laravel Application Not Responding Properly over HTTPS"
fi

# Check Laravel configuration
if grep -q "APP_URL=https://" /var/www/hdtickets/.env; then
    echo "✓ Laravel APP_URL configured for HTTPS"
else
    echo "⚠️  Laravel APP_URL not configured for HTTPS"
fi

echo ""
echo "Recommendations:"
echo "---------------"

# Provide recommendations based on findings
if [ $days_until_expiry -lt 30 ]; then
    echo "• Renew SSL certificate (expires in $days_until_expiry days)"
fi

echo "• For production, consider using Let's Encrypt or a commercial certificate"
echo "• Monitor certificate expiry regularly"
echo "• Test SSL configuration with tools like SSL Labs"
echo "• Consider implementing Certificate Pinning for enhanced security"
echo "• Review and update cipher suites regularly"

echo ""
echo "Log Files to Monitor:"
echo "--------------------"
echo "• SSL Error Log: /var/log/apache2/hdtickets-ssl-error.log"
echo "• SSL Access Log: /var/log/apache2/hdtickets-ssl-access.log"
echo "• SSL Security Log: /var/log/apache2/hdtickets-ssl-security.log"
echo "• Laravel Log: /var/www/hdtickets/storage/logs/laravel.log"

echo ""
echo "=================================="
echo "SSL Status Check Complete"
echo "=================================="
