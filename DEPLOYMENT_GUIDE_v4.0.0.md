# HD Tickets v4.0.0 Deployment Guide

## 🚀 Major Dependency Updates & Breaking Changes

This guide documents the comprehensive dependency updates implemented in HD Tickets v4.0.0 and provides deployment instructions for team members.

### 🎯 System Overview
HD Tickets is a **Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System** - NOT a helpdesk ticket system. This application monitors ticket availability across multiple sports event platforms and provides automated purchase functionality.

## 📋 Prerequisites

### Required Versions
- **Node.js**: v22.18.0 (REQUIRED - specified in `.nvmrc`)
- **PHP**: ^8.4
- **Laravel**: ^12.0
- **Ubuntu**: 24.04 LTS with Apache2

### Environment Setup
```bash
# Install Node.js v22.18.0 using nvm
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
nvm install 22.18.0
nvm use 22.18.0

# Verify versions
node --version  # Should return v22.18.0
php --version   # Should return PHP 8.4.x
```

## 🔄 Major Dependency Changes

### Backend Dependencies (composer.json)

#### ⬆️ Major Upgrades
- **Laravel Framework**: ^11.x → ^12.0
- **PHP**: ^8.3 → ^8.4
- **PayPal SDK**: Deprecated REST SDK → PayPal Server SDK ^1.1
- **Predis**: ^2.x → ^3.1

#### 🆕 New Dependencies
- **Laravel Telescope**: ^5.10 (Application monitoring)
- **Laravel Passport**: ^12.0 (OAuth authentication)
- **Stripe SDK**: ^17.4 (Enhanced payment processing)
- **Spatie Browsershot**: ^5.0.5 (Screenshot functionality)

### Frontend Dependencies (package.json)

#### ⬆️ Major Upgrades
- **Vite**: ^5.x → ^6.3.5
- **Vue**: ^3.2.x → ^3.3.11
- **Alpine.js**: ^3.13.x → ^3.14.9
- **Axios**: ^1.5.x → ^1.6.2

#### 🆕 New Dependencies
- **@soketi/soketi**: ^1.6.1 (WebSocket server)
- **Chart.js**: ^4.4.1 (Data visualization)
- **Vue Router**: ^4.2.5 (Client-side routing)

## 🛠️ Deployment Process

### 1. Pre-Deployment Checklist

```bash
# Clone or pull latest changes
git pull origin main

# Check Node.js version
node --version  # Must be v22.18.0

# Verify .nvmrc file exists
cat .nvmrc      # Should contain: 22.18.0
```

### 2. Backend Setup

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Clear existing caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Run migrations (includes new OAuth and Telescope tables)
php artisan migrate --force

# Cache for production
php artisan config:cache
php artisan route:cache
```

### 3. Frontend Setup

```bash
# Install Node.js dependencies
npm ci --only=production

# Build production assets
npm run build

# Verify build output
ls -la public/build/
```

### 4. Configuration Updates

#### Environment Variables (.env)
Add the following new environment variables:

```env
# PayPal Server SDK Configuration
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_MODE=sandbox  # or 'live' for production

# Telescope Configuration (optional for production)
TELESCOPE_ENABLED=false

# OAuth Configuration
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=client_id
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=client_secret

# WebSocket Configuration
SOKETI_APP_ID=your_app_id
SOKETI_APP_KEY=your_app_key
SOKETI_APP_SECRET=your_app_secret
```

### 5. Database Updates

New tables added in this version:
- `telescope_*` tables (application monitoring)
- `oauth_*` tables (authentication)
- `personal_access_tokens` table
- Performance indexes on existing tables

### 6. Security Considerations

⚠️ **BREAKING CHANGES**:
- PayPal integration completely rewritten
- OAuth authentication system added
- Personal access tokens for API authentication

## 🔧 Configuration File Updates

### Files Modified:
- `composer.json` - Major dependency upgrades
- `package.json` - Frontend dependency updates
- `config/telescope.php` - New monitoring configuration
- `config/services.php` - Updated PayPal configuration
- `config/database.php` - MySQL compatibility improvements
- `tailwind.config.js` - Updated build configuration

### New Files Added:
- `.nvmrc` - Node.js version specification
- `DEPLOYMENT_GUIDE_v4.0.0.md` - This file
- Various migration files for new features

## 🚨 Team Awareness & Migration Steps

### For Development Teams:

1. **Update Local Environment**:
   ```bash
   # Update Node.js to v22.18.0
   nvm install 22.18.0
   nvm use 22.18.0
   
   # Update PHP to 8.4 (if needed)
   # Update dependencies
   composer update
   npm update
   ```

2. **Run Local Migrations**:
   ```bash
   php artisan migrate
   php artisan passport:install --force
   ```

3. **Update IDE/Editor**:
   - Update PHP language server to support PHP 8.4
   - Update Vue.js plugins for Vue 3.3.11
   - Update Tailwind CSS IntelliSense

### For DevOps/Deployment Teams:

1. **Server Requirements**:
   - Ubuntu 24.04 LTS with Apache2
   - PHP 8.4 with required extensions
   - Node.js v22.18.0
   - Updated MySQL/PostgreSQL drivers

2. **Deployment Script**:
   Use the updated `deploy.sh` script which includes:
   - Node.js version verification
   - Frontend asset building
   - Database migration execution

3. **Monitoring Setup**:
   - Optional: Enable Laravel Telescope for debugging
   - Configure OAuth clients if using API authentication
   - Set up WebSocket server (Soketi) for real-time features

## 📊 Performance Improvements

### Database Optimizations:
- Added performance indexes to high-traffic tables
- Optimized MySQL configuration for modern compatibility
- Enhanced Redis integration with Predis v3.1

### Frontend Optimizations:
- Vite 6.3.5 provides faster build times
- Vue 3.3.11 includes performance improvements
- Optimized WebSocket implementation with Soketi

## 🐛 Common Issues & Solutions

### Issue: Node.js Version Mismatch
**Solution**: Use `.nvmrc` file with nvm:
```bash
nvm use  # Automatically uses version from .nvmrc
```

### Issue: PayPal Integration Errors
**Solution**: Update environment variables to use new PayPal Server SDK format

### Issue: CSS Cache Issues
**Solution**: CSS files are linked with timestamps to prevent caching (as per project rules)

### Issue: MySQL Authentication Plugin Error
**Solution**: Updated database configuration handles modern MySQL compatibility

## 📞 Support & Documentation

- **Technical Documentation**: `/docs/` directory
- **API Documentation**: `/docs/api/`
- **Architecture**: `/docs/ad_interim/ARCHITECTURE.md`
- **Changelog**: `/docs/ad_interim/CHANGELOG.md`

## ✅ Post-Deployment Verification

1. **System Health Check**:
   ```bash
   php artisan route:list  # Verify routes
   php artisan config:show app.version  # Should show 4.0.0
   npm run dev  # Test frontend build
   ```

2. **Feature Verification**:
   - Test ticket monitoring functionality
   - Verify payment processing (PayPal/Stripe)
   - Check real-time WebSocket connections
   - Validate API authentication

3. **Performance Monitoring**:
   - Enable Telescope (if desired) for debugging
   - Monitor database query performance
   - Check frontend asset loading times

---

**Version**: 4.0.0  
**Deployment Date**: 2025-08-04  
**System**: Sports Event Ticket Monitoring System  
**Platform**: Ubuntu 24.04 LTS with Apache2  

⚠️ **Remember**: This is a sports events ticket monitoring system, NOT a helpdesk system!
