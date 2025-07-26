# 🚀 HDTickets Production Deployment Guide

## Step-by-Step Instructions for https://hdtickets.polascin.net/

---

## 📋 Prerequisites

Before starting, ensure you have:
- Ubuntu server with root/sudo access
- Domain pointing to your server (hdtickets.polascin.net)
- SSH access to the server
- SSL certificate for HTTPS

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

Upload `hdtickets-production.tar.gz` to your Ubuntu server using your preferred method:

**Option A: Using SCP**
```bash
scp hdtickets-production.tar.gz user@hdtickets.polascin.net:/tmp/
```

**Option B: Using FileZilla/WinSCP**
- Upload to `/tmp/hdtickets-production.tar.gz`

---

## 🖥️ STEP 3: Connect to Server

SSH into your Ubuntu server:

```bash
ssh user@hdtickets.polascin.net
```

---

## 🏗️ STEP 4: Server Environment Setup

### 4.1 Update System Packages
```bash
sudo apt update && sudo apt upgrade -y
```

### 4.2 Install Required Software
```bash
# Install PHP 8.4 and extensions
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
    php8.4 \
    php8.4-fpm \
    php8.4-mysql \
    php8.4-redis \
    php8.4-zip \
    php8.4-gd \
    php8.4-mbstring \
    php8.4-curl \
    php8.4-xml \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-dom \
    php8.4-fileinfo

# Install Nginx
sudo apt install -y nginx

# Install MySQL (if not already installed)
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Install Node.js and NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 📁 STEP 5: Deploy Application

### 5.1 Prepare Directory Structure
```bash
# Navigate to web root
cd /var/www

# Backup existing installation (if any)
if [ -d "hdtickets" ]; then
    sudo mv hdtickets hdtickets-backup-$(date +%Y%m%d-%H%M%S)
fi

# Create new directory
sudo mkdir -p hdtickets
cd hdtickets
```

### 5.2 Extract Application
```bash
# Extract the deployment package
sudo tar -xzf /tmp/hdtickets-production.tar.gz

# Set proper ownership
sudo chown -R www-data:www-data /var/www/hdtickets

# Set proper permissions
sudo chmod -R 755 /var/www/hdtickets
sudo chmod -R 775 /var/www/hdtickets/storage
sudo chmod -R 775 /var/www/hdtickets/bootstrap/cache
```

---

## ⚙️ STEP 6: Configure Environment

### 6.1 Set Up Environment File
```bash
# Copy production environment
sudo cp .env.production .env

# Edit environment file with your specific settings
sudo nano .env
```

**Important**: Update these values in `.env`:
- Database credentials
- Redis password (if set)
- Mail settings
- API keys
- Domain settings

### 6.2 Install Dependencies
```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-req=ext-zip

# Generate application key (if not set)
php artisan key:generate --force
```

---

## 🗄️ STEP 7: Database Setup

### 7.1 Create Database
```bash
# Login to MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE hdtickets_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hdtickets_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON hdtickets_production.* TO 'hdtickets_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 7.2 Run Migrations
```bash
# Run database migrations
php artisan migrate --force

# Seed initial data (optional)
php artisan db:seed --force
```

---

## 🎨 STEP 8: Build Frontend Assets

```bash
# Install NPM dependencies
npm ci

# Build production assets
npm run build
```

---

## 🚀 STEP 9: Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Create symbolic link for storage
php artisan storage:link
```

---

## 🌐 STEP 10: Configure Nginx

### 10.1 Create Nginx Configuration
```bash
sudo nano /etc/nginx/sites-available/hdtickets.polascin.net
```

**Add this configuration:**
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name hdtickets.polascin.net www.hdtickets.polascin.net;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name hdtickets.polascin.net www.hdtickets.polascin.net;
    root /var/www/hdtickets/public;

    index index.php;

    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/hdtickets.polascin.net/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hdtickets.polascin.net/privkey.pem;
    
    # SSL Security
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;

    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # Logging
    access_log /var/log/nginx/hdtickets.access.log;
    error_log /var/log/nginx/hdtickets.error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known) {
        deny all;
    }

    # Static files optimization
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

### 10.2 Enable Site
```bash
# Enable the site
sudo ln -s /etc/nginx/sites-available/hdtickets.polascin.net /etc/nginx/sites-enabled/

# Remove default site (if exists)
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
sudo nginx -t

# If test passes, reload Nginx
sudo systemctl reload nginx
```

---

## 🔐 STEP 11: SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d hdtickets.polascin.net -d www.hdtickets.polascin.net

# Test automatic renewal
sudo certbot renew --dry-run
```

---

## 🔄 STEP 12: Set Up Queue Workers

### 12.1 Create Systemd Service
```bash
sudo nano /etc/systemd/system/hdtickets-worker.service
```

**Add this content:**
```ini
[Unit]
Description=HDTickets Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=10
ExecStart=/usr/bin/php /var/www/hdtickets/artisan queue:work --sleep=3 --tries=3 --max-time=3600
StandardOutput=journal
StandardError=journal

[Install]
WantedBy=multi-user.target
```

### 12.2 Enable and Start Service
```bash
sudo systemctl daemon-reload
sudo systemctl enable hdtickets-worker
sudo systemctl start hdtickets-worker

# Check status
sudo systemctl status hdtickets-worker
```

---

## ⏰ STEP 13: Set Up Cron Jobs

```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e

# Add this line:
* * * * * cd /var/www/hdtickets && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔧 STEP 14: Configure PHP-FPM

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.4/fpm/pool.d/www.conf
```

**Update these settings:**
```ini
user = www-data
group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

```bash
# Restart PHP-FPM
sudo systemctl restart php8.4-fpm
```

---

## 🔥 STEP 15: Configure Firewall

```bash
# Enable UFW
sudo ufw enable

# Allow SSH
sudo ufw allow ssh

# Allow HTTP and HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Check status
sudo ufw status
```

---

## 🎯 STEP 16: Final Services Restart

```bash
# Restart all services
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
sudo systemctl restart redis-server
sudo systemctl restart hdtickets-worker

# Enable services to start on boot
sudo systemctl enable nginx
sudo systemctl enable php8.4-fpm
sudo systemctl enable redis-server
sudo systemctl enable mysql
```

---

## ✅ STEP 17: Verification & Testing

### 17.1 Check Services
```bash
# Check all services are running
sudo systemctl status nginx
sudo systemctl status php8.4-fpm
sudo systemctl status mysql
sudo systemctl status redis-server
sudo systemctl status hdtickets-worker
```

### 17.2 Test Website
```bash
# Test HTTP to HTTPS redirect
curl -I http://hdtickets.polascin.net

# Test HTTPS response
curl -I https://hdtickets.polascin.net

# Test specific endpoints
curl https://hdtickets.polascin.net/health
```

### 17.3 Check Logs
```bash
# Check application logs
tail -f /var/www/hdtickets/storage/logs/laravel.log

# Check Nginx logs
tail -f /var/log/nginx/hdtickets.error.log

# Check PHP-FPM logs
tail -f /var/log/php8.4-fpm.log
```

---

## 🎉 STEP 18: Post-Deployment Tasks

### 18.1 Create Admin User
```bash
# Access the application via SSH
cd /var/www/hdtickets

# Create admin user
php artisan make:admin-user
```

### 18.2 Test Advanced Features
1. Visit https://hdtickets.polascin.net/admin/login
2. Test the enhanced scraping dashboard
3. Verify anti-detection capabilities
4. Test user registration restrictions

---

## 📊 STEP 19: Monitoring Setup

### 19.1 Set Up Log Rotation
```bash
sudo nano /etc/logrotate.d/hdtickets
```

**Add this content:**
```
/var/www/hdtickets/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 644 www-data www-data
}
```

### 19.2 Monitor Resource Usage
```bash
# Install htop for monitoring
sudo apt install -y htop

# Check current resource usage
htop
```

---

## 🔒 STEP 20: Security Hardening

```bash
# Disable server tokens in Nginx
echo 'server_tokens off;' | sudo tee -a /etc/nginx/nginx.conf

# Hide PHP version
echo 'expose_php = Off' | sudo tee -a /etc/php/8.4/fpm/php.ini

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.4-fpm
```

---

## 🎯 Deployment Complete!

Your HDTickets application is now successfully deployed at:
**https://hdtickets.polascin.net/**

### 🚀 Features Deployed:
- ✅ Advanced Anti-Detection Scraping Systems
- ✅ High-Demand Ticket Monitoring
- ✅ Enhanced Admin Dashboard
- ✅ User Registration Restrictions (Admin-only)
- ✅ Real-time Monitoring & Testing
- ✅ Professional UI with Interactive Features
- ✅ SSL Security & Performance Optimization

### 📱 Admin Access:
- Admin Panel: https://hdtickets.polascin.net/admin/login
- Scraping Dashboard: https://hdtickets.polascin.net/admin/scraping
- User Management: https://hdtickets.polascin.net/admin/users

**The deployment is complete and ready for production use!** 🎉

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### Issue 1: Permission Denied Errors
```bash
sudo chown -R www-data:www-data /var/www/hdtickets
sudo chmod -R 755 /var/www/hdtickets
sudo chmod -R 775 /var/www/hdtickets/storage
sudo chmod -R 775 /var/www/hdtickets/bootstrap/cache
```

#### Issue 2: Database Connection Errors
- Check MySQL service: `sudo systemctl status mysql`
- Verify database credentials in `.env`
- Test connection: `php artisan migrate:status`

#### Issue 3: 502 Bad Gateway
- Check PHP-FPM: `sudo systemctl status php8.4-fpm`
- Check socket path in Nginx config
- Review error logs: `tail -f /var/log/nginx/hdtickets.error.log`

#### Issue 4: Queue Jobs Not Processing
- Check worker status: `sudo systemctl status hdtickets-worker`
- Restart worker: `sudo systemctl restart hdtickets-worker`
- Check queue table: `php artisan queue:work --once`

#### Issue 5: SSL Certificate Issues
- Renew certificate: `sudo certbot renew`
- Check certificate status: `sudo certbot certificates`
- Test SSL: `openssl s_client -connect hdtickets.polascin.net:443`

### Log Locations
- Application logs: `/var/www/hdtickets/storage/logs/laravel.log`
- Nginx access logs: `/var/log/nginx/hdtickets.access.log`
- Nginx error logs: `/var/log/nginx/hdtickets.error.log`
- PHP-FPM logs: `/var/log/php8.4-fpm.log`
- System logs: `/var/log/syslog`

### Performance Monitoring
```bash
# Check system resources
htop

# Monitor disk usage
df -h

# Check memory usage
free -h

# Monitor active connections
netstat -an | grep :443 | wc -l

# Check queue status
php artisan queue:work --once --verbose
```

### Maintenance Commands
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Update application
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache

# Restart services
sudo systemctl restart nginx php8.4-fpm hdtickets-worker
```

---

## 📞 Support

If you encounter any issues during deployment:

1. Check the troubleshooting section above
2. Review the application logs
3. Verify all services are running
4. Test individual components

**Remember to always backup your database and files before making changes!**
