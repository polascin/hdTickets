# Dependency Update Guidelines

**HD Tickets - Sport Events Entry Tickets Monitoring System**  
**Environment**: Ubuntu 24.04 LTS, Apache2, PHP 8.4, MySQL/MariaDB 10.4

## 📋 Overview

This document provides comprehensive guidelines for managing dependencies in the HD Tickets application. Following these guidelines ensures consistent, secure, and stable dependency management across all environments.

## 🎯 Core Principles

### 1. **Security First**
- Always prioritize security updates over feature updates
- Regularly audit dependencies for known vulnerabilities
- Use only trusted and well-maintained packages

### 2. **Stability Priority**
- Prefer stable releases over cutting-edge versions
- Test thoroughly in staging before production deployment
- Maintain version compatibility across the entire stack

### 3. **Sports Events Focus**
- Ensure all dependencies support our sports events monitoring requirements
- Prioritize performance for real-time ticket data processing
- Maintain compatibility with ticket platform APIs

## 🔧 Version Requirements

### Critical Version Constraints

#### PHP Backend
```json
{
  "php": "^8.4.11",
  "laravel/framework": "^12.22.1",
  "laravel/passport": "^13.0",
  "laravel/sanctum": "^4.0",
  "laravel/horizon": "^5.33"
}
```

#### Node.js Frontend
```json
{
  "node": "22.18.0",
  "npm": ">=10.9.3",
  "vue": "^3.5.18",
  "vite": "^7.1.2",
  "alpine": "^3.14.9"
}
```

### Environment-Specific Requirements

#### Production Environment (Ubuntu 24.04 LTS)
- **PHP**: 8.4.11+ (compiled with Apache2 module)
- **Node.js**: v22.18.0 (exact version - use NVM)
- **Apache2**: 2.4+ with PHP 8.4 module
- **MySQL**: 8.4+ or MariaDB 10.4+
- **Redis**: 6.0+ for caching and queues
- **Composer**: 2.x latest stable

## 📦 Dependency Categories

### 1. **Core Framework Dependencies**
These are critical to system operation and require careful handling:

#### Laravel Framework
- **Current**: Laravel 12.22.1
- **Update Policy**: Minor updates allowed, major updates require testing
- **Testing Required**: Full regression testing
- **Rollback Plan**: Database migration rollback prepared

#### Vue.js Frontend
- **Current**: Vue.js 3.5.18
- **Update Policy**: Patch updates automatic, minor updates with testing
- **Testing Required**: Component testing, E2E testing
- **Browser Support**: Must maintain current browser support matrix

### 2. **Sports Events Platform Integrations**
Critical for sports ticket monitoring functionality:

#### API Client Libraries
- **Guzzle HTTP**: ^7.0 (for API communications)
- **Roach PHP**: ^3.0 (for web scraping)
- **Browsershot**: ^5.0.5 (for JavaScript-heavy sites)

#### Payment Processing
- **PayPal Server SDK**: ^1.1 (migrated from REST SDK)
- **Stripe PHP**: ^17.4
- **Laravel Cashier**: When required for subscriptions

### 3. **Development Tools**
Quality assurance and development efficiency:

#### Code Quality
- **PHPStan**: ^2.0 (static analysis level 8)
- **PHP CS Fixer**: ^3.85 (PSR-12 compliance)
- **PHPUnit**: ^12.0 (testing framework)
- **Larastan**: ^3.0 (Laravel-specific PHPStan rules)

#### Frontend Development
- **ESLint**: ^9.33.0 (flat config format)
- **TypeScript**: ^5.9.2
- **Vitest**: ^3.2.4 (testing framework)
- **Vite**: ^7.1.2 (build tool)

## 🔄 Update Procedures

### 1. **Security Updates (High Priority)**

#### Immediate Action Required
```bash
# 1. Check for security advisories
composer audit
npm audit

# 2. Apply security patches
composer update --with-dependencies package/name
npm update package-name

# 3. Run tests immediately
composer run full-quality-check
npm run test

# 4. Deploy to staging for verification
./deployment/blue-green/deploy.sh deploy
```

#### Security Update Checklist
- [ ] Review security advisory details
- [ ] Check for breaking changes
- [ ] Update in development environment first
- [ ] Run comprehensive test suite
- [ ] Deploy to staging environment
- [ ] Verify sports events functionality
- [ ] Schedule production deployment
- [ ] Monitor system after deployment

### 2. **Feature Updates (Medium Priority)**

#### Monthly Update Process
```bash
# 1. Review available updates
composer outdated
npm outdated

# 2. Plan update batch
# Group compatible updates together
# Separate major version updates

# 3. Update development environment
composer update --with-dependencies
npm update

# 4. Test thoroughly
composer run test
npm run test
npm run build
```

#### Feature Update Checklist
- [ ] Review changelog for breaking changes
- [ ] Update in development environment
- [ ] Test sports events monitoring functionality
- [ ] Test ticket platform integrations
- [ ] Run performance benchmarks
- [ ] Update documentation if needed
- [ ] Deploy to staging for team testing
- [ ] Schedule production deployment

### 3. **Major Version Updates (Low Priority)**

#### Planned Update Process
Major version updates require careful planning and extensive testing.

##### Pre-Update Planning
1. **Impact Assessment**
   - Review breaking changes documentation
   - Identify affected code areas
   - Estimate development time required
   - Plan rollback strategy

2. **Environment Preparation**
   - Create dedicated update branch
   - Set up isolated testing environment
   - Backup current stable version
   - Document current configuration

3. **Update Execution**
   - Update one major dependency at a time
   - Fix breaking changes incrementally
   - Test after each major change
   - Document all modifications

##### Example: Laravel Major Version Update
```bash
# 1. Create update branch
git checkout -b feature/laravel-13-upgrade

# 2. Update composer.json
# Edit version constraints manually

# 3. Update dependencies
composer update laravel/framework --with-dependencies

# 4. Run Laravel upgrade assistant
php artisan upgrade:check

# 5. Fix breaking changes
# Update code according to upgrade guide

# 6. Test extensively
composer run full-quality-check
php artisan test --coverage

# 7. Test sports events functionality
./scripts/test-sports-platforms.sh
```

## 🧪 Testing Requirements

### 1. **Automated Testing**

#### PHP Backend Testing
```bash
# Unit tests
./vendor/bin/phpunit --testsuite=Unit

# Feature tests
./vendor/bin/phpunit --testsuite=Feature

# Sports events integration tests
./vendor/bin/phpunit --testsuite=Integration

# Full test suite with coverage
composer run test-coverage
```

#### Frontend Testing
```bash
# Unit tests for Vue components
npm run test:unit

# Integration tests
npm run test:integration

# End-to-end tests
npm run test:e2e

# Build verification
npm run build
```

### 2. **Manual Testing**

#### Sports Events Functionality
- [ ] Ticket platform connections working
- [ ] Real-time price monitoring active
- [ ] Alert system functioning
- [ ] Dashboard displaying correctly
- [ ] User authentication working
- [ ] Payment processing functional

#### Performance Testing
- [ ] Page load times \< 3 seconds
- [ ] API response times \< 1 second
- [ ] Database query performance acceptable
- [ ] Memory usage within limits
- [ ] CPU usage within acceptable range

## 🚀 Deployment Guidelines

### 1. **Staging Deployment**
All dependency updates must be tested in staging first:

```bash
# Deploy to staging
./deployment/blue-green/deploy.sh deploy

# Run staging tests
./deployment/post-deployment-check.sh staging

# Monitor for 24 hours minimum
tail -f /var/log/hdtickets/application.log
```

### 2. **Production Deployment**
Production deployments require additional safeguards:

#### Pre-Deployment
- [ ] All tests passing in staging
- [ ] Team approval obtained
- [ ] Rollback plan prepared
- [ ] Maintenance window scheduled
- [ ] Monitoring alerts configured

#### During Deployment
```bash
# Use blue-green deployment
sudo ./deployment/blue-green/deploy.sh deploy

# Monitor health checks
watch -n 5 'curl -s http://localhost/health | jq .'

# Verify sports events data
curl -s http://localhost/api/v1/health/sports-events
```

#### Post-Deployment
- [ ] Health checks passing
- [ ] Sports events monitoring active
- [ ] Error rates within normal range
- [ ] Performance metrics acceptable
- [ ] User feedback positive

## 🔍 Monitoring and Maintenance

### 1. **Regular Maintenance Tasks**

#### Weekly
```bash
# Check for security updates
composer audit
npm audit

# Review system logs
sudo tail -100 /var/log/hdtickets/application.log

# Check sports events system health
./scripts/health-check-sports-events.sh
```

#### Monthly
```bash
# Review dependency status
composer outdated
npm outdated

# Performance analysis
./scripts/performance-analysis.sh

# Security audit
./scripts/security-audit.sh

# Update documentation
git log --since="1 month ago" --pretty=format:"%h %s" > monthly-changes.txt
```

### 2. **Monitoring Alerts**

#### Dependency-Related Alerts
- New security vulnerabilities detected
- Failed dependency downloads
- Version constraint conflicts
- Build failures due to dependency issues

#### Sports Events System Alerts
- Ticket platform API failures
- Price monitoring interruptions
- Alert delivery failures
- Database connection issues

## 🚨 Emergency Procedures

### 1. **Critical Security Vulnerability**

#### Immediate Response (Within 2 Hours)
1. **Assess Impact**
   - Review vulnerability details
   - Identify affected components
   - Determine exploitation risk

2. **Apply Emergency Fix**
   ```bash
   # Create hotfix branch
   git checkout -b hotfix/security-YYYY-MM-DD
   
   # Apply security update
   composer update vulnerable/package
   
   # Run critical tests only
   ./vendor/bin/phpunit --testsuite=Critical
   
   # Deploy immediately
   sudo ./deployment/blue-green/deploy.sh deploy skip_tests
   ```

3. **Verify Fix**
   - Test vulnerable functionality
   - Monitor error logs
   - Verify security scanners

### 2. **Dependency Conflict Resolution**

#### Common Conflicts
```bash
# Version constraint conflicts
composer why-not package/name version

# Dependency resolution
composer update --with-dependencies package/name

# Force resolution (last resort)
composer update package/name --ignore-platform-requirements
```

#### Node.js Conflicts
```bash
# Clear NPM cache
npm cache clean --force

# Delete node_modules and reinstall
rm -rf node_modules package-lock.json
npm install

# Use exact versions for critical packages
npm install --save-exact package-name@version
```

### 3. **Rollback Procedures**

#### Application Rollback
```bash
# Immediate rollback
sudo ./deployment/blue-green/deploy.sh rollback

# Database rollback (if needed)
php artisan hdtickets:migrate-data --rollback

# Verify rollback
curl -s http://localhost/health | jq '.status'
```

## 📚 Documentation Requirements

### 1. **Update Documentation**
Every dependency update must include:
- CHANGELOG.md entry
- Updated README.md if dependencies change
- Migration guide for breaking changes
- Team notification of changes

### 2. **Required Documentation Files**
- `CHANGELOG.md` - All changes with versions
- `DEPENDENCY_AUDIT_REPORT.md` - Monthly audit results
- `BREAKING_CHANGES.md` - Major version migration guides
- `TEAM_NOTIFICATIONS.md` - Communication log

## 👥 Team Responsibilities

### 1. **Roles and Responsibilities**

#### Lead Developer
- Approve major version updates
- Review security updates
- Maintain update schedule
- Coordinate team communications

#### Senior Developers
- Perform dependency updates
- Review breaking changes
- Test sports events functionality
- Document migration procedures

#### DevOps Team
- Deploy updates to staging/production
- Monitor system performance
- Maintain deployment scripts
- Handle emergency rollbacks

### 2. **Communication Protocol**

#### Before Updates
```markdown
Subject: [HD Tickets] Scheduled Dependency Updates - [Date]

Dependencies to update:
- Laravel: 12.22.0 → 12.22.1 (security)
- Vue.js: 3.5.17 → 3.5.18 (feature)
- Chart.js: 4.4.0 → 4.5.0 (feature)

Testing completed: [Date]
Staging deployment: [Date]
Production deployment: [Date]

Breaking changes: None expected
Rollback plan: Blue-green deployment rollback

Team leads please confirm receipt.
```

#### After Updates
```markdown
Subject: [HD Tickets] Dependency Updates Completed - [Date]

Updates completed successfully:
✅ Laravel 12.22.1 deployed
✅ Vue.js 3.5.18 deployed  
✅ Chart.js 4.5.0 deployed

System status: All systems operational
Performance: Within normal parameters
Sports events monitoring: Active and functioning

Next update scheduled: [Date]
```

## 🔧 Tools and Scripts

### 1. **Dependency Management Scripts**

#### Update Check Script
```bash
#!/bin/bash
# scripts/check-dependencies.sh

echo "Checking PHP dependencies..."
composer outdated

echo "Checking Node.js dependencies..."
npm outdated

echo "Security audit..."
composer audit
npm audit

echo "Checking for sports events API compatibility..."
./scripts/test-api-compatibility.sh
```

#### Sports Events Testing Script
```bash
#!/bin/bash
# scripts/test-sports-platforms.sh

echo "Testing Ticketmaster integration..."
php artisan test --filter=TicketmasterTest

echo "Testing StubHub integration..."
php artisan test --filter=StubHubTest

echo "Testing Viagogo integration..."
php artisan test --filter=ViagogTest

echo "Testing SeatGeek integration..."
php artisan test --filter=SeatGeekTest
```

### 2. **Automation Tools**

#### GitHub Actions Workflow
```yaml
# .github/workflows/dependency-update.yml
name: Dependency Security Check

on:
  schedule:
    - cron: '0 9 * * MON'  # Weekly Monday 9 AM
  workflow_dispatch:

jobs:
  security-audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          
      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: '22.18.0'
          
      - name: Security Audit
        run: |
          composer audit
          npm audit
          
      - name: Create Issue on Vulnerabilities
        if: failure()
        uses: actions/github-script@v6
        with:
          script: |
            github.rest.issues.create({
              owner: context.repo.owner,
              repo: context.repo.repo,
              title: 'Security vulnerabilities detected',
              body: 'Automated security audit found vulnerabilities. Please review and update dependencies.'
            })
```

## 📊 Success Metrics

### 1. **Dependency Health Metrics**
- **Security**: Zero high/critical vulnerabilities
- **Freshness**: \<30 days behind latest versions
- **Stability**: \<5% dependency-related bugs
- **Performance**: No degradation after updates

### 2. **Sports Events System Metrics**
- **Uptime**: 99.9% sports events monitoring availability
- **API Response**: \<1s average response time
- **Data Accuracy**: 99.5% accurate ticket prices
- **Alert Delivery**: \<10s alert delivery time

## 🎯 Conclusion

Following these dependency update guidelines ensures:

✅ **Security**: Proactive vulnerability management  
✅ **Stability**: Reliable sports events monitoring  
✅ **Performance**: Optimized system operation  
✅ **Team Efficiency**: Clear processes and responsibilities  
✅ **Business Continuity**: Minimal disruption to ticket monitoring services  

For questions or clarifications, contact the development team lead or refer to the technical documentation in the `docs/` directory.

---

**Document Version**: 1.0  
**Last Updated**: July 26, 2025  
**Next Review**: August 26, 2025  
**Maintained by**: HD Tickets Development Team
