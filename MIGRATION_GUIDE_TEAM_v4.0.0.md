# HD Tickets v4.0.0 - Team Migration Guide

**Updated:** August 12, 2025  
**Version:** 4.0.0  
**Target System:** Sports Events Entry Tickets Monitoring, Scraping and Purchase System  

## 🎯 System Overview

HD Tickets is a **Comprehensive Sports Events Entry Tickets Monitoring, Scraping and Purchase System** designed for monitoring ticket availability across multiple sports platforms. This is **NOT** a helpdesk ticket system - all development should focus on sports events functionality.

## 🚨 CRITICAL: Breaking Changes Summary

### Mandatory Requirements
1. **Node.js v22.18.0** - Exact version required (no newer, no older)
2. **PHP 8.4.11** - Laravel 12.x dependency
3. **PayPal Integration Rewrite** - Complete code changes needed
4. **Database Schema Updates** - New OAuth and monitoring tables

## 📋 Pre-Migration Checklist

### Environment Verification
```bash
# Check current versions
node --version      # Target: v22.18.0
php --version       # Target: PHP 8.4.11
composer --version  # Should be 2.x

# Check Git status
git status          # Should be clean
git pull origin main # Get latest changes
```

### Backup Requirements
```bash
# Backup your current work
git stash push -m "Pre-v4.0.0-migration-backup"

# Backup local database (if needed)
php artisan db:backup # or mysqldump equivalent
```

## 🔄 Step-by-Step Migration Process

### Step 1: Node.js Version Update
```bash
# Install and use exact Node.js version
nvm install 22.18.0
nvm use 22.18.0

# Verify the version (CRITICAL)
node --version  # Must show: v22.18.0

# Set as default (optional)
nvm alias default 22.18.0

# Verify .nvmrc file exists
cat .nvmrc      # Should contain: 22.18.0
```

### Step 2: Update Dependencies
```bash
# Clear NPM cache
npm cache clean --force

# Remove old node_modules
rm -rf node_modules package-lock.json

# Install PHP dependencies
composer install --optimize-autoloader

# Install Node.js dependencies (uses exact versions)
npm ci

# Verify key packages
npm list vue @vitejs/plugin-vue laravel-vite-plugin
```

### Step 3: Database Migration
```bash
# Clear application caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run new migrations
php artisan migrate

# Install OAuth components
php artisan passport:install --force

# Verify new tables exist
php artisan tinker --execute="
echo 'OAuth tables: ' . collect(['oauth_clients', 'oauth_access_tokens'])->map(fn(\$t) => Schema::hasTable(\$t) ? '✓' : '✗')->join(' ') . PHP_EOL;
echo 'Telescope: ' . (Schema::hasTable('telescope_entries') ? '✓' : '✗') . PHP_EOL;
"
```

### Step 4: Environment Configuration
Update your `.env` file with new required variables:

```bash
# Add these new environment variables:
cat >> .env << 'EOF'

# OAuth Configuration (New in v4.0.0)
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=

# PayPal Server SDK (Replaces old REST SDK)
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret  
PAYPAL_MODE=sandbox

# Stripe Integration (Enhanced)
STRIPE_KEY=your_stripe_key
STRIPE_SECRET=your_stripe_secret

# WebSocket Server (Soketi)
SOKETI_APP_ID=your_app_id
SOKETI_APP_KEY=your_app_key
SOKETI_APP_SECRET=your_app_secret

# Monitoring (Optional)
TELESCOPE_ENABLED=false
EOF
```

### Step 5: Frontend Asset Building
```bash
# Build production assets
npm run build

# Verify build output
ls -la public/build/

# Test development server
npm run dev # Should start without errors
```

### Step 6: Verification Tests
```bash
# Test application startup
php artisan serve --port=8001 &
curl -I http://localhost:8001

# Test key routes
php artisan route:list --name=api | head -20

# Test database connections
php artisan tinker --execute="
echo 'DB Status: ' . (DB::connection()->getPdo() ? 'Connected' : 'Failed') . PHP_EOL;
echo 'Users count: ' . App\Models\User::count() . PHP_EOL;
"

# Kill test server
pkill -f "php artisan serve"
```

## ⚠️ Code Migration Requirements

### PayPal Integration Changes

#### Old Code (DEPRECATED - Will Break):
```php
// This will cause errors in v4.0.0
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;

$apiContext = new ApiContext(
    new OAuthTokenCredential($clientId, $clientSecret)
);
```

#### New Code (REQUIRED for v4.0.0):
```php
// Use PayPal Server SDK
use PayPal\v1\Payments\PaymentsCreateRequest;
use PayPal\Core\PayPalHttpClient;
use PayPal\Core\SandboxEnvironment; // or LiveEnvironment

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);
```

### Configuration File Updates

#### CSS Cache Prevention (Project Rule)
Ensure all CSS includes use timestamps:
```php
// In Blade templates
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">

// Or use the helper
<link rel="stylesheet" href="{{ mix('css/app.css') }}">
```

## 🧪 Testing Your Migration

### Functional Tests
```bash
# Test core functionality
php artisan test --filter=CoreFunctionalityTest

# Test sports events monitoring (our primary function)
php artisan tinker --execute="
echo 'Testing sports events monitoring...' . PHP_EOL;
// Add your specific tests here
"

# Test payment integrations (if you work with payments)
php artisan test --filter=PaymentTest
```

### Manual Testing Checklist
- [ ] Application loads without errors
- [ ] User registration/login works
- [ ] Sports events can be monitored (core functionality)
- [ ] Alerts system functions
- [ ] Dashboard displays correctly
- [ ] CSS files load with timestamps (no cache issues)

## 🔧 Development Environment Setup

### IDE/Editor Updates
```bash
# Update PHP language server for PHP 8.4
# Update Vue.js extensions for Vue 3.3.11
# Install Tailwind CSS IntelliSense (updated version)

# VS Code recommended extensions:
code --install-extension bradlc.vscode-tailwindcss
code --install-extension vue.volar
code --install-extension bmewburn.vscode-intelephense-client
```

### Debug Tools (Optional)
```bash
# Enable Telescope for debugging (development only)
echo "TELESCOPE_ENABLED=true" >> .env.local

# Install Telescope if not installed
php artisan telescope:install
php artisan migrate
```

## 🚨 Common Issues & Solutions

### Issue 1: Node.js Version Mismatch
**Symptoms**: Build failures, "node version not supported" errors
**Solution**:
```bash
nvm install 22.18.0
nvm use 22.18.0
rm -rf node_modules package-lock.json
npm ci
```

### Issue 2: PayPal Integration Errors
**Symptoms**: PayPal API calls failing, "class not found" errors
**Solution**: Update all PayPal code to use Server SDK (see code examples above)

### Issue 3: CSS Not Loading
**Symptoms**: Styling broken, CSS cache issues
**Solution**: Verify timestamp linking is implemented (project rule compliance)

### Issue 4: OAuth Errors
**Symptoms**: API authentication failing, "client not found" errors
**Solution**:
```bash
php artisan passport:install --force
php artisan config:cache
```

### Issue 5: Database Migration Errors
**Symptoms**: Migration failures, "table already exists" errors
**Solution**:
```bash
php artisan migrate:status
php artisan migrate --force
# If needed: php artisan migrate:rollback && php artisan migrate
```

## 📊 Performance Optimizations

### Production Optimizations
```bash
# Cache everything for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build optimized frontend
npm run build
```

## 🏁 Completion Checklist

### Before Committing New Work:
- [ ] Node.js version is exactly v22.18.0
- [ ] PHP version is 8.4.x
- [ ] All migrations run successfully
- [ ] Frontend builds without errors (`npm run build`)
- [ ] Application starts without errors
- [ ] Core sports events functionality works
- [ ] PayPal integration updated (if applicable)
- [ ] CSS includes timestamps (project rule)
- [ ] No console errors in browser
- [ ] Tests pass (if applicable)

### Documentation Updates:
- [ ] Update any project documentation you maintain
- [ ] Update API documentation if you made API changes
- [ ] Update deployment scripts if needed

## 📞 Support Resources

### Documentation:
- `README.md` - Updated with v4.0.0 dependencies
- `DEPLOYMENT_GUIDE_v4.0.0.md` - Technical deployment guide  
- `/docs/api/` - API documentation
- `/docs/ad_interim/CHANGELOG.md` - Detailed change log

### Getting Help:
1. Check this migration guide first
2. Review the deployment guide for technical details
3. Check existing documentation in `/docs/`
4. Test changes in development environment first
5. Contact team lead for critical migration issues

## 🎯 Post-Migration Development

### Best Practices:
- Always use `nvm use` before development work
- Run `npm ci` instead of `npm install` for consistent builds
- Test sports events functionality (our core purpose)
- Ensure CSS cache prevention is maintained
- Use new PayPal Server SDK for payment features

### Continuous Integration:
- CI/CD pipelines updated for Node.js v22.18.0
- Database migrations run automatically
- Frontend assets built with new Vite configuration

---

**Remember**: HD Tickets is a **Sports Events Entry Tickets Monitoring System** - focus all development on sports events, ticket monitoring, and purchase functionality. This is NOT a helpdesk ticket system.

**Migration Support**: If you encounter issues not covered in this guide, document them and share with the team to improve this guide for future migrations.

**Version**: 4.0.0  
**Migration Date**: August 12, 2025  
**Environment**: Ubuntu 24.04 LTS with Apache2
