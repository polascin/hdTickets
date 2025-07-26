# Production Deployment Guide for HDTickets

## Issues Identified and Fixed

### 1. Storage Permissions (CRITICAL)
The storage directories must be writable by the web server:

```bash
# On the production server, run:
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Or if you need broader permissions:
chmod -R 777 storage
chmod -R 777 bootstrap/cache
```

### 2. View Compilation Issue
There's a malformed `@foreach` statement in one of your Blade files. While view caching fails, the application still works without it.

**For immediate deployment:** Skip view caching and fix the malformed Blade syntax later.

### 3. Environment Configuration
Ensure your production `.env` file is properly configured:

```bash
# Copy the production template
cp .env.production .env

# Edit the .env file with your production database credentials
```

## Deployment Steps

### 1. Pre-Deployment (Local)
```bash
# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan cache:clear

# Test routes (but skip view caching for now)
php artisan route:cache

# Install production dependencies
composer install --optimize-autoloader --no-dev

# Build frontend assets
npm run build
```

### 2. Upload to Production Server
Upload these files/folders to your WebSupport.sk hosting:
- All application files
- `.env.production` → rename to `.env` on server
- Compiled assets from `public/build/`

### 3. Server Configuration
1. **Set proper file permissions:**
   ```bash
   chmod -R 755 .
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

2. **Configure database:** Update `.env` with your MySQL credentials

3. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

4. **Generate application key if needed:**
   ```bash
   php artisan key:generate
   ```

### 4. Optimize for Production
```bash
# Cache configuration (only if .env is properly set)
php artisan config:cache

# Cache routes (should work now)
php artisan route:cache

# Skip view caching for now due to the malformed @foreach issue
# php artisan view:cache
```

## Troubleshooting Common 500 Errors

### Check These First:
1. **Storage permissions** - Most common cause
2. **Database connection** - Check `.env` credentials
3. **Missing APP_KEY** - Generate if empty
4. **PHP extensions** - Ensure all required extensions are installed

### Debugging Commands:
```bash
# Test basic functionality
php artisan about

# Check database connection
php artisan migrate:status

# Clear all caches if something seems stuck
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### Server Requirements:
- PHP 8.1 or higher (you have 8.4.8 ✓)
- MySQL 5.7+ or MySQL 8.0+
- Extensions: PDO, mbstring, OpenSSL, Tokenizer, XML, cURL
- Write permissions on storage/ and bootstrap/cache/

## Production Environment Variables

Your `.env.production` should contain:
```env
APP_NAME=HDTickets
APP_ENV=production
APP_KEY=base64:zHkg+Nx9m0hCdHSPJxmg613QdLMVqGLfc5HGZSinT54=
APP_DEBUG=false
APP_URL=https://hdtickets.polascin.net

# Database - Update with your actual credentials
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=your-db-name
DB_USERNAME=your-db-username
DB_PASSWORD=your-db-password

# Cache and Session
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail (configure as needed)
MAIL_MAILER=smtp
# ... other mail settings
```

## Known Issues to Fix Later

1. **Malformed @foreach statement** - Causing view caching to fail
   - Application works without view caching
   - Should be fixed for better performance

2. **Route listing syntax** - Minor: `--compact` option doesn't exist in Laravel 12
   - Use `php artisan route:list` instead

## Testing Your Deployment

After deployment, test these URLs:
- https://hdtickets.polascin.net/ (should show welcome page)
- https://hdtickets.polascin.net/login (should show login form)
- https://hdtickets.polascin.net/admin (should redirect to login)

If you get 500 errors, check:
1. Server error logs
2. Laravel logs in `storage/logs/laravel.log`
3. File permissions on storage directories

## Emergency Rollback

If deployment fails:
1. Restore previous files
2. Restore previous database (if migrations were run)
3. Clear all caches: `php artisan config:clear && php artisan route:clear && php artisan cache:clear`
