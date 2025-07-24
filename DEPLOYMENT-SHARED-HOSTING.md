# 🚀 HDTickets Shared Hosting Deployment Guide

## Step-by-Step Instructions for https://hdtickets.polascin.net/ (No sudo required)

---

## 📋 Prerequisites

Before starting, ensure you have:
- Shared hosting account with PHP 8.4+ support
- Domain pointing to your hosting (hdtickets.polascin.net)
- FTP/SFTP access or hosting control panel
- MySQL database access
- SSH access (if available)

---

## 🔥 STEP 1: Prepare Deployment Package

On your Windows machine (current location):

```powershell
# Ensure you're in the project directory
cd C:\Users\polas\OneDrive\www\hdtickets

# Create deployment package
git archive --format=tar.gz --output=hdtickets-production.tar.gz HEAD

# Verify the package was created
ls -la hdtickets-production.tar.gz
```

---

## 📤 STEP 2: Upload to Server

### Option A: Using Control Panel File Manager
1. Login to your hosting control panel (cPanel, Plesk, etc.)
2. Navigate to File Manager
3. Upload `hdtickets-production.tar.gz` to your account root
4. Extract the archive using the file manager

### Option B: Using FTP/SFTP
```bash
# Upload via FTP/SFTP to your hosting account
# Replace with your actual FTP credentials
sftp user@hdtickets.polascin.net
put hdtickets-production.tar.gz
```

### Option C: Using SSH (if available)
```bash
# Upload via SCP
scp hdtickets-production.tar.gz user@hdtickets.polascin.net:~/
```

---

## 🖥️ STEP 3: Connect to Server

### Option A: SSH (if available)
```bash
ssh user@hdtickets.polascin.net
```

### Option B: Use hosting control panel terminal/shell access

---

## 📁 STEP 4: Extract and Organize Files

```bash
# Navigate to your home directory
cd ~

# Create backup of existing installation (if any)
if [ -d "public_html/hdtickets" ]; then
    mv public_html/hdtickets public_html/hdtickets-backup-$(date +%Y%m%d-%H%M%S)
fi

# Create application directory
mkdir -p hdtickets
cd hdtickets

# Extract the deployment package
tar -xzf ../hdtickets-production.tar.gz

# Set proper permissions for writable directories
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

---

## 🌐 STEP 5: Configure Web Directory

### Option A: Move to public_html (if hdtickets.polascin.net is your main domain)
```bash
# Move the public folder contents to public_html
cp -r public/* ~/public_html/
cp public/.htaccess ~/public_html/

# Update the index.php to point to the correct location
```

### Option B: Create subdomain/subdirectory structure
```bash
# Create symbolic link or copy public folder
mkdir -p ~/public_html/hdtickets
cp -r public/* ~/public_html/hdtickets/
```

---

## ⚙️ STEP 6: Configure Environment

### 6.1 Set Up Environment File
```bash
# Copy production environment
cp .env.production .env

# Edit environment file with your specific settings
nano .env  # or use hosting control panel editor
```

**Important**: Update these values in `.env`:
```bash
APP_URL=https://hdtickets.polascin.net
APP_ENV=production
APP_DEBUG=false

# Database settings (get from hosting control panel)
DB_HOST=localhost  # or your hosting's DB server
DB_DATABASE=your_database_name
DB_USERNAME=your_db_username
DB_PASSWORD=your_db_password

# File paths (adjust based on your hosting structure)
SESSION_DRIVER=file  # Use file instead of redis if not available
CACHE_STORE=file     # Use file instead of redis if not available
QUEUE_CONNECTION=database  # Use database instead of redis
```

### 6.2 Update Bootstrap Path

Edit `~/public_html/index.php` (or wherever your public files are):

```php
<?php
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Update these paths to point to your Laravel installation
require __DIR__.'/../hdtickets/vendor/autoload.php';

$app = require_once __DIR__.'/../hdtickets/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
```

---

## 📦 STEP 7: Install Dependencies

### Option A: If Composer is available on your hosting
```bash
cd ~/hdtickets

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction
```

### Option B: If Composer is not available
1. Run `composer install --no-dev --optimize-autoloader` on your local machine
2. Upload the entire `vendor` folder via FTP
3. Make sure to include the `composer.lock` file

---

## 🗄️ STEP 8: Database Setup

### 8.1 Create Database (via Control Panel)
1. Login to your hosting control panel
2. Navigate to MySQL Databases or Database section
3. Create a new database: `hdtickets_production`
4. Create a database user and assign it to the database
5. Note down the database credentials

### 8.2 Import Database Structure
```bash
# If you have SSH access
cd ~/hdtickets
php artisan migrate --force

# If you need to import manually, export your local database:
# mysqldump -u root -p hdtickets > hdtickets_schema.sql
# Then import via phpMyAdmin or hosting control panel
```

---

## 🔑 STEP 9: Generate Application Key

```bash
cd ~/hdtickets

# Generate application key
php artisan key:generate --force
```

---

## 🎨 STEP 10: Build Frontend Assets

### Option A: If Node.js is available on hosting
```bash
cd ~/hdtickets

# Install NPM dependencies
npm ci

# Build production assets
npm run build

# Copy built assets to public directory
cp -r public/build ~/public_html/build/
```

### Option B: Build locally and upload
1. On your local machine, run:
   ```bash
   npm ci
   npm run build
   ```
2. Upload the `public/build` folder to your hosting's public directory

---

## 🚀 STEP 11: Optimize for Production

```bash
cd ~/hdtickets

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views  
php artisan view:cache

# Create storage link (if supported)
php artisan storage:link
```

---

## 📝 STEP 12: Configure .htaccess

Create/update `~/public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "no-referrer-when-downgrade"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</IfModule>

# Hide sensitive files
<Files .env>
    Order allow,deny
    Deny from all
</Files>

<Files composer.json>
    Order allow,deny  
    Deny from all
</Files>

<Files composer.lock>
    Order allow,deny
    Deny from all
</Files>

# Optimize static files
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/svg+xml "access plus 1 year"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
</IfModule>
```

---

## ⏰ STEP 13: Set Up Cron Jobs

### Via Control Panel:
1. Login to your hosting control panel
2. Navigate to Cron Jobs section
3. Add a new cron job:
   - **Command**: `cd /home/yourusername/hdtickets && /usr/bin/php artisan schedule:run`
   - **Frequency**: Every minute `* * * * *`

### Manual Setup (if SSH available):
```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /home/yourusername/hdtickets && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 STEP 14: Configure Queue Processing

Since you can't use systemd services, use one of these alternatives:

### Option A: Database Queue (Recommended for shared hosting)
Update your `.env`:
```bash
QUEUE_CONNECTION=database
```

Create queue jobs table:
```bash
cd ~/hdtickets
php artisan queue:table
php artisan migrate --force
```

### Option B: Cron-based Queue Processing
Add to your cron jobs:
```bash
# Process queue every 5 minutes
*/5 * * * * cd /home/yourusername/hdtickets && /usr/bin/php artisan queue:work --stop-when-empty
```

---

## 🔧 STEP 15: PHP Configuration

### Via .htaccess (if allowed):
Add to your `~/public_html/.htaccess`:

```apache
# PHP Configuration
php_value memory_limit 256M
php_value max_execution_time 300
php_value upload_max_filesize 64M
php_value post_max_size 64M
```

### Via php.ini (if supported):
Create `~/public_html/php.ini` or `~/hdtickets/php.ini`:

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
expose_php = Off
```

---

## ✅ STEP 16: Test Deployment

### 16.1 Basic Tests
```bash
# Test if PHP can run Laravel
cd ~/hdtickets
php artisan --version

# Test database connection
php artisan migrate:status

# Check if routes are working
php artisan route:list | head -10
```

### 16.2 Web Tests
1. Visit your domain: https://hdtickets.polascin.net
2. Check for any errors in browser
3. Test basic navigation

---

## 🎉 STEP 17: Post-Deployment Tasks

### 17.1 Create Admin User
```bash
cd ~/hdtickets

# If you have a seeder for admin user
php artisan db:seed --class=AdminUserSeeder

# Or create manually via tinker
php artisan tinker
```

In tinker:
```php
use App\Models\User;
User::create([
    'name' => 'Admin User',
    'email' => 'admin@hdtickets.polascin.net',
    'password' => bcrypt('your_secure_password'),
    'role' => 'admin',
    'email_verified_at' => now()
]);
```

### 17.2 Test Admin Features
1. Visit: https://hdtickets.polascin.net/admin/login
2. Login with admin credentials
3. Test the scraping dashboard: https://hdtickets.polascin.net/admin/scraping
4. Verify all features are working

---

## 📊 STEP 18: Set Up Monitoring

### 18.1 Error Logging
Ensure your `.env` has proper logging:
```bash
LOG_CHANNEL=daily
LOG_LEVEL=error
LOG_STACK=single
```

### 18.2 Monitor Logs
```bash
# Check Laravel logs
tail -f ~/hdtickets/storage/logs/laravel.log

# Check error logs (location varies by hosting)
tail -f ~/logs/error.log  # or ~/public_html/error_log
```

---

## 🔐 STEP 19: Security Hardening

### 19.1 File Permissions
```bash
# Set secure permissions
find ~/hdtickets -type f -exec chmod 644 {} \;
find ~/hdtickets -type d -exec chmod 755 {} \;
chmod -R 755 ~/hdtickets/storage
chmod -R 755 ~/hdtickets/bootstrap/cache
```

### 19.2 Hide Sensitive Directories
Add to `~/public_html/.htaccess`:
```apache
# Block access to sensitive directories
RedirectMatch 403 ^/(.*)/(storage|vendor|bootstrap|config|database|resources|routes|tests)/.*$

# Block access to Laravel files  
<FilesMatch "(artisan|composer\.json|composer\.lock|package\.json|\.env\.*)">
    Order Allow,Deny
    Deny from all
</FilesMatch>
```

---

## 🎯 STEP 20: Performance Optimization

### 20.1 Enable OPcache (if available)
Check if OPcache is enabled:
```bash
php -m | grep -i opcache
```

### 20.2 Optimize Autoloader
```bash
cd ~/hdtickets
composer dump-autoload --optimize --no-dev
```

### 20.3 Clear Unnecessary Files
```bash
# Remove development files
rm -rf ~/hdtickets/tests
rm -rf ~/hdtickets/.git
rm ~/hdtickets/.gitignore
rm ~/hdtickets/README.md
```

---

## 🎯 Deployment Complete!

Your HDTickets application is now deployed at:
**https://hdtickets.polascin.net/**

### 🚀 Features Deployed:
- ✅ Advanced Anti-Detection Scraping Systems
- ✅ High-Demand Ticket Monitoring  
- ✅ Enhanced Admin Dashboard
- ✅ User Registration Restrictions (Admin-only)
- ✅ Real-time Monitoring & Testing
- ✅ Professional UI with Interactive Features
- ✅ Optimized for Shared Hosting Environment

### 📱 Admin Access:
- Admin Panel: https://hdtickets.polascin.net/admin/login
- Scraping Dashboard: https://hdtickets.polascin.net/admin/scraping
- User Management: https://hdtickets.polascin.net/admin/users

---

## 🔧 Shared Hosting Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Class not found" errors
```bash
# Regenerate autoloader
cd ~/hdtickets
composer dump-autoload --optimize
```

#### Issue 2: Permission denied on storage
```bash
chmod -R 755 ~/hdtickets/storage
chmod -R 755 ~/hdtickets/bootstrap/cache
```

#### Issue 3: 500 Internal Server Error
1. Check error logs in hosting control panel
2. Verify `.htaccess` syntax
3. Check PHP version compatibility
4. Ensure all required PHP extensions are enabled

#### Issue 4: Database connection failed
- Verify database credentials in `.env`
- Check if database server allows connections from web server
- Test connection via hosting control panel

#### Issue 5: Routes not working
- Verify `.htaccess` file is present and correct
- Check if mod_rewrite is enabled (contact hosting support)
- Clear route cache: `php artisan route:clear`

#### Issue 6: CSS/JS files not loading
- Check if `public/build` directory exists and has files
- Verify asset paths in generated HTML
- Check for CORS issues

### Performance Tips for Shared Hosting

1. **Use File-based Caching**: 
   ```bash
   CACHE_STORE=file
   SESSION_DRIVER=file
   ```

2. **Optimize Database Queries**:
   - Use eager loading
   - Index frequently queried columns
   - Limit result sets

3. **Minimize HTTP Requests**:
   - Combine CSS/JS files
   - Use asset versioning
   - Enable browser caching

4. **Monitor Resource Usage**:
   - Check hosting control panel for resource usage
   - Optimize heavy operations
   - Use queues for background tasks

### Maintenance Commands

```bash
# Update application (after uploading new files)
cd ~/hdtickets
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches when needed
php artisan cache:clear
php artisan config:clear  
php artisan route:clear
php artisan view:clear

# Check system status
php artisan --version
php artisan route:list
php artisan config:show database
```

---

## 📞 Support for Shared Hosting

If you encounter issues:

1. **Check hosting documentation** for PHP/Laravel requirements
2. **Contact hosting support** for:
   - PHP extension availability
   - Cron job setup
   - File permission issues
   - Database connectivity

3. **Common hosting requirements**:
   - PHP 8.4+ with required extensions
   - MySQL 5.7+ or 8.0+
   - Adequate memory limit (256MB+)
   - Cron job support

**Remember**: Shared hosting has limitations compared to VPS/dedicated servers, but HDTickets is designed to work within these constraints!

---

## 🔄 Updates and Maintenance

### Updating the Application

1. **Prepare new version locally**:
   ```bash
   git pull origin main
   composer install --no-dev --optimize-autoloader
   npm run build
   git archive --format=tar.gz --output=hdtickets-update.tar.gz HEAD
   ```

2. **Backup current version**:
   ```bash
   cp -r ~/hdtickets ~/hdtickets-backup-$(date +%Y%m%d)
   ```

3. **Deploy update**:
   ```bash
   cd ~/hdtickets
   tar -xzf ../hdtickets-update.tar.gz
   php artisan migrate --force
   php artisan config:cache
   ```

### Regular Maintenance

- **Weekly**: Check error logs and clear old log files
- **Monthly**: Update dependencies (if possible)
- **Quarterly**: Review and optimize database
- **Annually**: Review hosting plan and requirements

**Your HDTickets application is now successfully deployed on shared hosting!** 🎉
