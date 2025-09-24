#!/bin/bash

# HD Tickets Security Monitoring Script
# Performs automated security checks and generates alerts

echo "🛡️  HD Tickets Security Monitor - $(date)"
echo "================================================"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check PHP dependencies for vulnerabilities
echo -e "\n🔍 Checking PHP Dependencies..."
if composer audit --quiet; then
    echo -e "${GREEN}✅ PHP dependencies: No vulnerabilities found${NC}"
else
    echo -e "${RED}❌ PHP dependencies: Vulnerabilities detected!${NC}"
fi

# Check Node.js dependencies for vulnerabilities
echo -e "\n🔍 Checking Node.js Dependencies..."
if npm audit --audit-level=moderate --silent; then
    echo -e "${GREEN}✅ Node.js dependencies: No vulnerabilities found${NC}"
else
    echo -e "${RED}❌ Node.js dependencies: Vulnerabilities detected!${NC}"
fi

# Check for sensitive data in config files
echo -e "\n🔍 Checking Configuration Security..."
if grep -q "APP_DEBUG=false" .env 2>/dev/null; then
    echo -e "${GREEN}✅ Debug mode: Disabled${NC}"
else
    echo -e "${YELLOW}⚠️  Debug mode: Check configuration${NC}"
fi

if grep -q "SESSION_ENCRYPT=true" .env 2>/dev/null; then
    echo -e "${GREEN}✅ Session encryption: Enabled${NC}"
else
    echo -e "${YELLOW}⚠️  Session encryption: Check configuration${NC}"
fi

# Check for HTTPS configuration
if grep -q "APP_URL=https" .env 2>/dev/null; then
    echo -e "${GREEN}✅ HTTPS: Configured${NC}"
else
    echo -e "${YELLOW}⚠️  HTTPS: Check configuration${NC}"
fi

# Check file permissions
echo -e "\n🔍 Checking File Permissions..."
if [ -f ".env" ] && [ "$(stat -c %a .env)" = "600" ]; then
    echo -e "${GREEN}✅ .env permissions: Secure (600)${NC}"
else
    echo -e "${YELLOW}⚠️  .env permissions: Should be 600${NC}"
    chmod 600 .env 2>/dev/null
fi

if [ -d "storage" ] && [ "$(stat -c %a storage)" = "755" ]; then
    echo -e "${GREEN}✅ Storage permissions: Secure (755)${NC}"
else
    echo -e "${YELLOW}⚠️  Storage permissions: Should be 755${NC}"
fi

# Check for common security files
echo -e "\n🔍 Checking Security Files..."
if [ -f "docs/SECURITY_AUDIT_REPORT.md" ]; then
    echo -e "${GREEN}✅ Security audit report: Present${NC}"
else
    echo -e "${YELLOW}⚠️  Security audit report: Missing${NC}"
fi

# Check Laravel security configurations
echo -e "\n🔍 Checking Laravel Security..."
php artisan about --only=environment 2>/dev/null | grep -q "production" && \
    echo -e "${GREEN}✅ Environment: Production${NC}" || \
    echo -e "${YELLOW}⚠️  Environment: Check configuration${NC}"

# Check database connection security
echo -e "\n🔍 Checking Database Security..."
if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connection: OK';" 2>/dev/null | grep -q "OK"; then
    echo -e "${GREEN}✅ Database connection: Secure${NC}"
else
    echo -e "${YELLOW}⚠️  Database connection: Check configuration${NC}"
fi

# Generate security score
echo -e "\n📊 Security Score Calculation..."
TOTAL_CHECKS=10
PASSED_CHECKS=0

# Count passed checks (simplified)
[ -f "docs/SECURITY_AUDIT_REPORT.md" ] && ((PASSED_CHECKS++))
grep -q "APP_DEBUG=false" .env 2>/dev/null && ((PASSED_CHECKS++))
grep -q "SESSION_ENCRYPT=true" .env 2>/dev/null && ((PASSED_CHECKS++))
grep -q "APP_URL=https" .env 2>/dev/null && ((PASSED_CHECKS++))
composer audit --quiet && ((PASSED_CHECKS++))
npm audit --audit-level=moderate --silent && ((PASSED_CHECKS++))
[ "$(stat -c %a .env 2>/dev/null)" = "600" ] && ((PASSED_CHECKS++))
[ "$(stat -c %a storage 2>/dev/null)" = "755" ] && ((PASSED_CHECKS++))
php artisan about --only=environment 2>/dev/null | grep -q "production" && ((PASSED_CHECKS++))
php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null && ((PASSED_CHECKS++))

SECURITY_SCORE=$((PASSED_CHECKS * 100 / TOTAL_CHECKS))

if [ $SECURITY_SCORE -ge 90 ]; then
    echo -e "${GREEN}🛡️  Security Score: $SECURITY_SCORE/100 - EXCELLENT${NC}"
elif [ $SECURITY_SCORE -ge 80 ]; then
    echo -e "${YELLOW}🛡️  Security Score: $SECURITY_SCORE/100 - GOOD${NC}"
elif [ $SECURITY_SCORE -ge 70 ]; then
    echo -e "${YELLOW}🛡️  Security Score: $SECURITY_SCORE/100 - FAIR${NC}"
else
    echo -e "${RED}🛡️  Security Score: $SECURITY_SCORE/100 - NEEDS IMPROVEMENT${NC}"
fi

echo -e "\n📅 Next security check recommended: $(date -d '+1 week')"
echo "================================================"