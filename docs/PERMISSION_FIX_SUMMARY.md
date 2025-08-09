# Laravel Permission Fix Summary

## Issue
The Laravel application was encountering permission errors when trying to write to the `storage/framework/views` directory:

```
ErrorException: file_put_contents(/var/www/hdtickets/storage/framework/views/953ceb46c39911b4e096ebc3f78380a1.php): Failed to open stream: Permission denied
```

## Root Cause
The compiled Blade view files in `storage/framework/views/` were owned by user `lubomir` instead of the web server user `www-data`, causing permission conflicts when Laravel tried to create or update compiled view files.

## Solution Applied

### 1. Fixed Storage Directory Ownership
```bash
sudo chown -R www-data:www-data /var/www/hdtickets/storage/
sudo chmod -R 775 /var/www/hdtickets/storage/
```

### 2. Fixed Bootstrap Cache Directory
```bash
sudo chown -R www-data:www-data /var/www/hdtickets/bootstrap/cache/
sudo chmod -R 775 /var/www/hdtickets/bootstrap/cache/
```

### 3. Cleared Laravel Caches
```bash
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:clear
```

### 4. Added User to www-data Group
```bash
sudo usermod -a -G www-data lubomir
```

## Verification
All critical directories now have the correct permissions:

- ✅ `storage/framework/views/` - www-data:www-data (0775)
- ✅ `bootstrap/cache/` - www-data:www-data (0775)  
- ✅ `storage/logs/` - www-data:www-data (0775)

## Best Practices for Future

### Recommended Permission Structure
```
/var/www/hdtickets/
├── storage/           (www-data:www-data, 775)
├── bootstrap/cache/   (www-data:www-data, 775)
├── public/           (www-data:www-data, 755)
└── [other files]     (lubomir:www-data, 644/755)
```

### Commands to Run After Deployment
Always run these commands after deploying code changes:

```bash
# Fix ownership
sudo chown -R www-data:www-data storage/ bootstrap/cache/

# Set permissions
sudo chmod -R 775 storage/ bootstrap/cache/

# Clear caches as web server user
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
```

### Development Workflow
1. Work on files as your user (`lubomir`)
2. After changes, ensure web server has proper permissions
3. Always clear caches as the web server user
4. Test the application functionality

## Status
✅ **RESOLVED** - The permission issue has been fixed and the HD Tickets dashboard should now load without permission errors.

---
*Fixed on: August 9, 2025*
*Applied by: System Administrator*
