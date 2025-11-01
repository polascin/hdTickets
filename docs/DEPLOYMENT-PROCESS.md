# HD Tickets Deployment Process

**Current Setup:** Direct Git-based deployment on local development server  
**Server:** Ubuntu with Nginx + PHP 8.3 + Redis + MySQL  
**Location:** `/var/www/hdtickets`  
**Domain:** hdtickets.local (local dev)

## Current Deployment Method

### Manual Git Deployment (In Use)

The application is deployed directly via git:

```bash
# Navigate to application directory
cd /var/www/hdtickets

# Pull latest changes from main branch
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Build frontend assets
npm ci
npm run build

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan event:cache
php artisan view:cache

# Run migrations (if needed)
php artisan migrate --force

# Restart services
sudo systemctl reload php8.3-fpm
sudo systemctl restart horizon
```

### Deployment Script

A simplified deployment script is available:

```bash
# Make executable
chmod +x scripts/simple-deploy.sh

# Run deployment
./scripts/simple-deploy.sh
```

## Service Management

### Horizon Queue Worker

**Service:** `horizon.service`  
**User:** `www-data`  
**Working Directory:** `/var/www/hdtickets`

```bash
# Check status
sudo systemctl status horizon

# Restart
sudo systemctl restart horizon

# View logs
sudo journalctl -u horizon -f
```

### Web Server

**Nginx Configuration:** `/etc/nginx/sites-available/hdtickets.local`  
**PHP-FPM:** `php8.3-fpm.service`

```bash
# Reload Nginx (after config changes)
sudo nginx -t && sudo systemctl reload nginx

# Reload PHP-FPM (after code changes)
sudo systemctl reload php8.3-fpm

# Check status
sudo systemctl status nginx
sudo systemctl status php8.3-fpm
```

## Pre-Deployment Checklist

Before deploying changes:

- [ ] ✅ All tests passing locally
  ```bash
  vendor/bin/pest
  ```

- [ ] ✅ Code style checked
  ```bash
  vendor/bin/pint --test
  ```

- [ ] ✅ Static analysis passed (optional, has pre-existing errors)
  ```bash
  vendor/bin/phpstan analyse --memory-limit=512M
  ```

- [ ] ✅ Frontend builds successfully
  ```bash
  npm run build
  ```

- [ ] ✅ Changes committed and pushed to GitHub
  ```bash
  git push origin main
  ```

- [ ] ✅ Database migrations reviewed (if any)
  ```bash
  git diff HEAD~1 database/migrations/
  ```

## Post-Deployment Verification

After deployment:

1. **Check Application**
   ```bash
   curl -I https://hdtickets.local
   # Should return: HTTP/2 200
   ```

2. **Check Horizon**
   ```bash
   sudo systemctl status horizon
   # Should be: active (running)
   ```

3. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   # Look for errors
   ```

4. **Test Key Features**
   - Login functionality
   - Dashboard loads
   - Ticket browsing works
   - Queue jobs processing

## Rollback Process

If deployment fails:

```bash
# Quick rollback via git
git log --oneline -10  # Find previous commit
git reset --hard <commit-hash>

# Rebuild assets
composer install --no-dev
npm ci && npm run build

# Clear caches
php artisan optimize:clear
php artisan config:cache

# Restart services
sudo systemctl reload php8.3-fpm
sudo systemctl restart horizon
```

## Database Backups

### Manual Backup

```bash
# Create backup
php artisan db:backup

# Or via mysqldump
mysqldump --single-transaction hdtickets > backup-$(date +%Y%m%d-%H%M%S).sql
```

### Restore from Backup

```bash
# List available backups
ls -lh storage/backups/

# Restore from backup
mysql hdtickets < backup-YYYYMMDD-HHMMSS.sql

# Run migrations to ensure schema is current
php artisan migrate --force
```

## Automated Deployment (Deployer - NOT IN USE)

The repository contains a `deploy.php` file for Deployer-based deployment, but it's **not currently configured or in use**. 

If you want to use Deployer in the future:

1. Install Deployer:
   ```bash
   composer require deployer/deployer --dev
   ```

2. Configure server hosts in `deploy.php`

3. Run deployment:
   ```bash
   vendor/bin/dep deploy production
   ```

**Current Status:** ❌ Not configured (would require server restructuring)

## CI/CD Status

**GitHub Actions:** ✅ Basic CI enabled (frontend build only)

The CI workflow (`.github/workflows/ci.yml`) runs on every push to:
- Build frontend assets
- Verify compilation succeeds

**Note:** PHP quality checks and tests are disabled in CI due to Redis infrastructure requirements. Run these locally before deploying.

## Environment Configuration

### Required Environment Variables

Key variables in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hdtickets.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=hdtickets

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Updating Environment

After changing `.env`:

```bash
php artisan config:cache
sudo systemctl reload php8.3-fpm
sudo systemctl restart horizon
```

## Troubleshooting

### Application Not Loading

1. Check PHP-FPM status:
   ```bash
   sudo systemctl status php8.3-fpm
   ```

2. Check Nginx error log:
   ```bash
   sudo tail -50 /var/log/nginx/error.log
   ```

3. Check Laravel log:
   ```bash
   tail -50 storage/logs/laravel.log
   ```

### Horizon Not Processing Jobs

1. Check Horizon status:
   ```bash
   sudo systemctl status horizon
   ```

2. Check Horizon logs:
   ```bash
   sudo journalctl -u horizon -n 50
   ```

3. Restart Horizon:
   ```bash
   sudo systemctl restart horizon
   ```

### Database Connection Issues

1. Check MySQL status:
   ```bash
   sudo systemctl status mysql
   ```

2. Test connection:
   ```bash
   mysql -u root -p hdtickets -e "SELECT 1"
   ```

3. Check Redis:
   ```bash
   redis-cli ping
   # Should return: PONG
   ```

### Permission Issues

Fix storage/cache permissions:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

## Security Notes

- **Never commit `.env` file** to git
- **Always use HTTPS** in production
- **Keep dependencies updated** regularly
- **Review security advisories** for PHP packages
- **Rotate application keys** periodically
- **Backup database** before major changes

## Monitoring

### Key Metrics to Watch

1. **Application Performance**
   - Response times
   - Error rates
   - Memory usage

2. **Queue Health**
   - Horizon dashboard: `/horizon`
   - Failed jobs count
   - Queue wait times

3. **Server Resources**
   - CPU usage
   - Memory usage
   - Disk space

4. **Database**
   - Query performance
   - Connection pool
   - Slow query log

## Recommendations for Production

### Future Improvements

1. **Automated Deployment**
   - Set up Deployer or GitHub Actions deployment
   - Create staging environment
   - Implement zero-downtime deployments

2. **Monitoring**
   - Add application performance monitoring (APM)
   - Set up error tracking (Sentry, Bugsnag)
   - Configure server monitoring (Datadog, New Relic)

3. **Backup Strategy**
   - Automated daily database backups
   - Off-site backup storage
   - Regular backup restoration tests

4. **Security**
   - Implement fail2ban
   - Set up firewall rules
   - Enable automatic security updates
   - Add intrusion detection

5. **High Availability**
   - Load balancer
   - Database replication
   - Redis Sentinel/Cluster
   - Multi-server deployment

## Support

For deployment issues:
- Check logs first: `storage/logs/laravel.log`
- Review this documentation
- Check GitHub issues
- Contact: lubomir@polascin.net

---

**Last Updated:** 2025-11-01  
**Version:** 1.0  
**Deployment Type:** Manual Git-based (local development)
