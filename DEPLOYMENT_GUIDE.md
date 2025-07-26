# HDTickets Deployment Guide

## Deployment to https://hdtickets.polascin.net/

This guide will help you deploy the HDTickets Laravel application to your WebSupport.sk hosting.

### Prerequisites

1. Access to your WebSupport.sk hosting account
2. Database access (MySQL/MariaDB)
3. FTP/SFTP access or file manager
4. PHP 8.1+ with required extensions

### Step 1: Prepare Files for Upload

#### Files to upload:
- All files and folders EXCEPT:
  - `.env` (create new one on server)
  - `node_modules/` (not needed)
  - `tests/` (optional, for production)
  - `.git/` (not needed)

#### Files to modify before upload:
1. **Delete or ignore these development files:**
   ```
   .env
   .env.example
   .gitignore
   node_modules/
   tests/
   .git/
   ```

### Step 2: Database Setup

1. **Create MySQL database in WebSupport admin panel:**
   - Database name: `your_db_name`
   - Database user: `your_db_user`
   - Database password: `your_db_password`
   - Database host: `your_db_host` (usually localhost or specific IP)

### Step 3: Upload Files

1. **Upload all files to your hosting directory**
   - Usually to `/www/` or `/public_html/` directory
   - Make sure the `public/` folder content goes to the web root

2. **Important:** Your web server should point to the `public/` directory as document root
   - If you can't change document root, move contents of `public/` to root and adjust paths

### Step 4: Environment Configuration

1. **Create `.env` file on server** (copy from `.env.production` template):
   ```
   APP_NAME=HDTickets
   APP_ENV=production
   APP_KEY=base64:YOUR_32_CHAR_KEY_HERE
   APP_DEBUG=false
   APP_TIMEZONE=Europe/Bratislava
   APP_URL=https://hdtickets.polascin.net

   DB_CONNECTION=mysql
   DB_HOST=YOUR_DB_HOST
   DB_PORT=3306
   DB_DATABASE=YOUR_DB_NAME
   DB_USERNAME=YOUR_DB_USER
   DB_PASSWORD=YOUR_DB_PASSWORD

   SESSION_DOMAIN=.polascin.net
   MAIL_FROM_ADDRESS="noreply@polascin.net"
   
   # ... other settings
   ```

### Step 5: Server Setup Commands

If you have SSH access, run these commands:

```bash
# Generate application key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache

# Set permissions (if on Linux/Unix)
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### Step 6: File Permissions

Ensure these directories are writable:
- `storage/` (recursively)
- `bootstrap/cache/`

### Step 7: Web Server Configuration

#### Apache (.htaccess)
The `.htaccess` file in `public/` directory should handle URL rewriting.

#### Nginx
If using Nginx, add this location block:
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Step 8: SSL Certificate

Ensure your domain has SSL certificate configured in WebSupport panel.

### Step 9: Testing

1. Visit https://hdtickets.polascin.net/
2. Test user registration/login
3. Check database connections
4. Verify all functionality works

### Troubleshooting

#### Common Issues:

1. **500 Internal Server Error**
   - Check file permissions on `storage/` and `bootstrap/cache/`
   - Verify `.env` file configuration
   - Check PHP error logs

2. **Database Connection Error**
   - Verify database credentials in `.env`
   - Ensure database exists and user has permissions

3. **Session Issues**
   - Check `SESSION_DOMAIN` in `.env`
   - Ensure `storage/framework/sessions/` is writable

4. **Missing Extensions**
   - Ensure PHP has required extensions: mbstring, openssl, PDO, tokenizer, XML, zip

### Security Considerations

1. **Never expose `.env` file** - ensure it's not web-accessible
2. **Set APP_DEBUG=false** in production
3. **Use HTTPS** for all traffic
4. **Keep dependencies updated** regularly

### Maintenance

#### Regular Tasks:
- Monitor error logs
- Update dependencies: `composer update`
- Clear caches: `php artisan cache:clear`
- Backup database regularly

### Contact

For deployment issues, check:
1. Laravel logs in `storage/logs/`
2. Web server error logs
3. WebSupport.sk documentation

---

**Note:** This guide assumes standard Laravel hosting requirements. Adjust based on your specific WebSupport.sk hosting environment.
