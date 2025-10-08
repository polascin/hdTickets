#!/bin/bash
# HD Tickets Production Server Provisioning Script
# Sports Events Entry Tickets Monitoring System
# Author: Lubomir Polascin (Ľubomír Polaščín) aka Walter Csoelle
# 
# This script provisions a DigitalOcean Ubuntu 24.04 LTS droplet for production deployment

set -euo pipefail

# Configuration
DEPLOY_USER="deploy"
DB_NAME="hdtickets"
DB_USER="hdtickets"
DOMAIN="hd-tickets.com"
WWW_DOMAIN="www.hd-tickets.com"
DEPLOY_PATH="/var/www/hdtickets"
PHP_VERSION="8.3"
NODE_VERSION="20"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Helper functions
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

check_root() {
    if [[ $EUID -ne 0 ]]; then
        log_error "This script must be run as root"
        exit 1
    fi
}

generate_password() {
    openssl rand -base64 32 | tr -d "=+/" | cut -c1-25
}

# Main provisioning steps
main() {
    check_root
    
    log_info "Starting HD Tickets production server provisioning..."
    log_info "Domain: $DOMAIN"
    log_info "Deploy path: $DEPLOY_PATH"
    
    # Step 1: System Update
    log_step "Updating system packages..."
    apt-get update -qq
    apt-get upgrade -y -qq
    
    # Step 2: Install essential packages
    log_step "Installing essential packages..."
    apt-get install -y -qq \
        curl \
        wget \
        git \
        unzip \
        software-properties-common \
        apt-transport-https \
        ca-certificates \
        gnupg \
        lsb-release \
        ufw \
        fail2ban \
        acl \
        htop \
        tree \
        vim \
        tmux
    
    # Step 3: Create deploy user
    log_step "Creating deploy user..."
    if ! id -u "$DEPLOY_USER" >/dev/null 2>&1; then
        useradd -m -s /bin/bash "$DEPLOY_USER"
        usermod -aG sudo "$DEPLOY_USER"
        
        # Create SSH directory for deploy user
        sudo -u "$DEPLOY_USER" mkdir -p "/home/$DEPLOY_USER/.ssh"
        sudo -u "$DEPLOY_USER" chmod 700 "/home/$DEPLOY_USER/.ssh"
        
        log_info "Deploy user '$DEPLOY_USER' created"
        log_warn "Please add your SSH public key to /home/$DEPLOY_USER/.ssh/authorized_keys"
    else
        log_info "Deploy user '$DEPLOY_USER' already exists"
    fi
    
    # Step 4: Configure SSH
    log_step "Configuring SSH security..."
    cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup
    
    # Update SSH configuration
    sed -i 's/#PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
    sed -i 's/#PasswordAuthentication yes/PasswordAuthentication no/' /etc/ssh/sshd_config
    sed -i 's/#PubkeyAuthentication yes/PubkeyAuthentication yes/' /etc/ssh/sshd_config
    
    # Restart SSH service
    systemctl restart ssh
    log_info "SSH configuration updated and service restarted"
    
    # Step 5: Configure firewall
    log_step "Configuring UFW firewall..."
    ufw --force reset
    ufw default deny incoming
    ufw default allow outgoing
    ufw allow ssh
    ufw allow http
    ufw allow https
    ufw --force enable
    log_info "UFW firewall configured and enabled"
    
    # Step 6: Configure Fail2Ban
    log_step "Configuring Fail2Ban..."
    cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
    systemctl enable fail2ban
    systemctl start fail2ban
    log_info "Fail2Ban configured and started"
    
    # Step 7: Install PHP 8.3
    log_step "Installing PHP $PHP_VERSION..."
    add-apt-repository ppa:ondrej/php -y
    apt-get update -qq
    
    apt-get install -y -qq \
        "php${PHP_VERSION}" \
        "php${PHP_VERSION}-fpm" \
        "php${PHP_VERSION}-cli" \
        "php${PHP_VERSION}-common" \
        "php${PHP_VERSION}-mysql" \
        "php${PHP_VERSION}-redis" \
        "php${PHP_VERSION}-mbstring" \
        "php${PHP_VERSION}-xml" \
        "php${PHP_VERSION}-bcmath" \
        "php${PHP_VERSION}-curl" \
        "php${PHP_VERSION}-intl" \
        "php${PHP_VERSION}-gd" \
        "php${PHP_VERSION}-zip" \
        "php${PHP_VERSION}-soap" \
        "php${PHP_VERSION}-imap" \
        "php${PHP_VERSION}-opcache"
    
    # Configure PHP for production
    log_step "Configuring PHP for production..."
    PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
    
    sed -i 's/display_errors = On/display_errors = Off/' "$PHP_INI"
    sed -i 's/expose_php = On/expose_php = Off/' "$PHP_INI"
    sed -i 's/memory_limit = 128M/memory_limit = 512M/' "$PHP_INI"
    sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' "$PHP_INI"
    sed -i 's/post_max_size = 8M/post_max_size = 64M/' "$PHP_INI"
    sed -i 's/max_execution_time = 30/max_execution_time = 300/' "$PHP_INI"
    
    # Enable and configure OPcache
    sed -i 's/;opcache.enable=1/opcache.enable=1/' "$PHP_INI"
    sed -i 's/;opcache.memory_consumption=128/opcache.memory_consumption=256/' "$PHP_INI"
    sed -i 's/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=20000/' "$PHP_INI"
    sed -i 's/;opcache.validate_timestamps=1/opcache.validate_timestamps=0/' "$PHP_INI"
    
    systemctl restart "php${PHP_VERSION}-fpm"
    log_info "PHP $PHP_VERSION installed and configured"
    
    # Step 8: Install Composer
    log_step "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
    chmod +x /usr/local/bin/composer
    log_info "Composer installed"
    
    # Step 9: Install Node.js
    log_step "Installing Node.js $NODE_VERSION..."
    curl -fsSL https://deb.nodesource.com/setup_${NODE_VERSION}.x | bash -
    apt-get install -y -qq nodejs
    log_info "Node.js $(node --version) and npm $(npm --version) installed"
    
    # Step 10: Install Apache
    log_step "Installing and configuring Apache..."
    apt-get install -y -qq apache2
    
    # Enable required Apache modules
    a2enmod rewrite
    a2enmod headers
    a2enmod ssl
    a2enmod proxy_fcgi
    a2enmod setenvif
    a2enmod http2
    a2enmod security2
    
    # Enable PHP-FPM configuration
    a2enconf "php${PHP_VERSION}-fpm"
    
    # Disable default site
    a2dissite 000-default
    
    # Configure Apache security
    cat >> /etc/apache2/conf-available/security.conf << 'EOF'

# Additional security headers
ServerTokens Prod
ServerSignature Off

# Hide Apache version
Header always unset Server
Header always unset X-Powered-By

EOF
    
    a2enconf security
    systemctl restart apache2
    log_info "Apache installed and configured"
    
    # Step 11: Install MySQL
    log_step "Installing MySQL..."
    apt-get install -y -qq mysql-server
    
    # Secure MySQL installation
    mysql_secure_installation --use-default
    
    # Configure MySQL for production
    cat >> /etc/mysql/mysql.conf.d/production.cnf << 'EOF'
[mysqld]
# InnoDB settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 1
innodb_flush_method = O_DIRECT

# Connection settings
max_connections = 300
connect_timeout = 60
wait_timeout = 120

# Query cache (disabled in MySQL 8.0+)
# query_cache_type = 1
# query_cache_size = 256M

# Logging
slow_query_log = 1
long_query_time = 2
EOF
    
    systemctl restart mysql
    log_info "MySQL installed and configured"
    
    # Step 12: Create database and user
    log_step "Creating database and user..."
    DB_PASSWORD=$(generate_password)
    
    mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
    mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
    mysql -e "FLUSH PRIVILEGES;"
    
    # Save database credentials
    cat > "/home/$DEPLOY_USER/database_credentials.txt" << EOF
Database Name: $DB_NAME
Database User: $DB_USER
Database Password: $DB_PASSWORD
EOF
    chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/database_credentials.txt"
    chmod 600 "/home/$DEPLOY_USER/database_credentials.txt"
    
    log_info "Database '$DB_NAME' and user '$DB_USER' created"
    log_warn "Database password saved to /home/$DEPLOY_USER/database_credentials.txt"
    
    # Step 13: Install and configure Redis
    log_step "Installing Redis..."
    apt-get install -y -qq redis-server
    
    # Configure Redis for production
    REDIS_PASSWORD=$(generate_password)
    
    sed -i 's/# requirepass foobared/requirepass '"$REDIS_PASSWORD"'/' /etc/redis/redis.conf
    sed -i 's/bind 127.0.0.1 ::1/bind 127.0.0.1/' /etc/redis/redis.conf
    sed -i 's/# maxmemory <bytes>/maxmemory 1gb/' /etc/redis/redis.conf
    sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
    sed -i 's/appendonly no/appendonly yes/' /etc/redis/redis.conf
    
    systemctl restart redis
    
    # Save Redis credentials
    cat > "/home/$DEPLOY_USER/redis_credentials.txt" << EOF
Redis Password: $REDIS_PASSWORD
EOF
    chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/redis_credentials.txt"
    chmod 600 "/home/$DEPLOY_USER/redis_credentials.txt"
    
    log_info "Redis installed and configured"
    log_warn "Redis password saved to /home/$DEPLOY_USER/redis_credentials.txt"
    
    # Step 14: Create application directories
    log_step "Creating application directories..."
    mkdir -p "$DEPLOY_PATH"
    mkdir -p "$DEPLOY_PATH/releases"
    mkdir -p "$DEPLOY_PATH/shared"
    mkdir -p "$DEPLOY_PATH/shared/storage"
    mkdir -p "$DEPLOY_PATH/shared/bootstrap/cache"
    mkdir -p "$DEPLOY_PATH/backups"
    
    # Set proper ownership
    chown -R "$DEPLOY_USER:www-data" "$DEPLOY_PATH"
    chmod -R 755 "$DEPLOY_PATH"
    
    log_info "Application directories created"
    
    # Step 15: Install Certbot for Let's Encrypt
    log_step "Installing Certbot..."
    apt-get install -y -qq certbot python3-certbot-apache
    log_info "Certbot installed"
    
    # Step 16: Create Apache virtual host
    log_step "Creating Apache virtual host..."
    cat > "/etc/apache2/sites-available/${DOMAIN}.conf" << EOF
# HD Tickets Production Virtual Host
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias ${WWW_DOMAIN}
    DocumentRoot ${DEPLOY_PATH}/current/public
    
    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Let's Encrypt ACME Challenge
    Alias /.well-known/acme-challenge/ ${DEPLOY_PATH}/current/public/.well-known/acme-challenge/
    <Directory "${DEPLOY_PATH}/current/public/.well-known/acme-challenge/">
        Options None
        AllowOverride None
        Require all granted
    </Directory>
    
    # Laravel Document Root
    <Directory "${DEPLOY_PATH}/current/public">
        Options -Indexes
        AllowOverride All
        Require all granted
        
        # Laravel routing
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
        
        # Security: Block sensitive files
        <FilesMatch "^\\.">
            Require all denied
        </FilesMatch>
    </Directory>
    
    # PHP-FPM Configuration
    <FilesMatch \\.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
    
    # Logging
    ErrorLog \${APACHE_LOG_DIR}/${DOMAIN}-error.log
    CustomLog \${APACHE_LOG_DIR}/${DOMAIN}-access.log combined
</VirtualHost>
EOF
    
    # Enable the site
    a2ensite "$DOMAIN"
    systemctl reload apache2
    
    log_info "Apache virtual host created and enabled"
    
    # Step 17: Configure automatic security updates
    log_step "Configuring automatic security updates..."
    apt-get install -y -qq unattended-upgrades
    
    cat > /etc/apt/apt.conf.d/20auto-upgrades << 'EOF'
APT::Periodic::Update-Package-Lists "1";
APT::Periodic::Download-Upgradeable-Packages "1";
APT::Periodic::AutocleanInterval "7";
APT::Periodic::Unattended-Upgrade "1";
EOF
    
    systemctl enable unattended-upgrades
    systemctl start unattended-upgrades
    
    log_info "Automatic security updates configured"
    
    # Step 18: Install monitoring agent (DigitalOcean)
    log_step "Installing DigitalOcean monitoring agent..."
    curl -sSL https://repos.insights.digitalocean.com/install.sh | sudo bash
    log_info "DigitalOcean monitoring agent installed"
    
    # Step 19: Create initial environment file template
    log_step "Creating environment file template..."
    cat > "$DEPLOY_PATH/shared/.env.template" << EOF
# HD Tickets Production Environment Configuration
# Sports Events Entry Tickets Monitoring System

APP_NAME="HD Tickets"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://${DOMAIN}
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASSWORD}
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379
REDIS_DB=0

# Cache Configuration
CACHE_DRIVER=redis
CACHE_PREFIX=hdtickets_cache

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.${DOMAIN}

# Queue Configuration
QUEUE_CONNECTION=redis
QUEUE_DEFAULT=default

# Broadcasting Configuration
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

# Pusher Configuration (or Soketi)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

# Vite Configuration
VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

# Laravel Horizon
HORIZON_DOMAIN=${DOMAIN}
HORIZON_PREFIX=horizon:
HORIZON_REDIS_CONNECTION=horizon
HORIZON_DARKMODE=true

# Logging
LOG_CHANNEL=daily
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

# Laravel Telescope (disabled in production)
TELESCOPE_ENABLED=false

# Security
BCRYPT_ROUNDS=12

# Third-party APIs (configure as needed)
TICKET_PLATFORM_API_KEY=
TICKETMASTER_API_KEY=
STUBHUB_API_KEY=
VIAGOGO_API_KEY=
TICKPICK_API_KEY=

# Google OAuth (optional)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URL=\${APP_URL}/auth/google/callback

# Payment Processing
STRIPE_KEY=
STRIPE_SECRET=
STRIPE_WEBHOOK_SECRET=

PAYPAL_MODE=live
PAYPAL_LIVE_CLIENT_ID=
PAYPAL_LIVE_CLIENT_SECRET=
PAYPAL_WEBHOOK_ID=
PAYPAL_RECEIVER_EMAIL=

# Monitoring & Analytics
GOOGLE_ANALYTICS_ID=
GOOGLE_SEARCH_CONSOLE_VERIFICATION=
SENTRY_LARAVEL_DSN=

# Performance Settings
OPCACHE_ENABLE=1
EOF
    
    chown "$DEPLOY_USER:www-data" "$DEPLOY_PATH/shared/.env.template"
    chmod 640 "$DEPLOY_PATH/shared/.env.template"
    
    log_info "Environment template created at $DEPLOY_PATH/shared/.env.template"
    
    # Step 20: Create deployment summary
    log_step "Creating deployment summary..."
    cat > "/home/$DEPLOY_USER/deployment_summary.txt" << EOF
HD Tickets Production Server Provisioning Complete
==================================================

Server Details:
- Domain: ${DOMAIN}
- Deploy Path: ${DEPLOY_PATH}
- Deploy User: ${DEPLOY_USER}
- PHP Version: ${PHP_VERSION}
- Node.js Version: $(node --version)

Services Installed:
- Apache 2.4 with HTTP/2 support
- PHP ${PHP_VERSION}-FPM with OPcache
- MySQL 8.0
- Redis 7.0
- Composer
- Node.js ${NODE_VERSION} & npm
- Certbot for Let's Encrypt SSL

Security Features:
- UFW firewall configured (SSH, HTTP, HTTPS)
- Fail2Ban protection
- SSH key authentication only
- Root login disabled
- Automatic security updates

Next Steps:
1. Add your SSH public key to /home/${DEPLOY_USER}/.ssh/authorized_keys
2. Copy the .env.template to .env and configure it
3. Set up SSL certificate with: certbot --apache -d ${DOMAIN} -d ${WWW_DOMAIN}
4. Configure DNS A records for ${DOMAIN} and ${WWW_DOMAIN}
5. Install Deployer locally and run deployment

Credentials saved to:
- Database: /home/${DEPLOY_USER}/database_credentials.txt
- Redis: /home/${DEPLOY_USER}/redis_credentials.txt

Configuration files:
- Environment template: ${DEPLOY_PATH}/shared/.env.template
- Apache virtual host: /etc/apache2/sites-available/${DOMAIN}.conf

Deployment commands (run locally):
- composer global require deployer/deployer
- dep deploy production
- dep rollback production (if needed)

EOF
    
    chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/deployment_summary.txt"
    
    log_info "Deployment summary created at /home/$DEPLOY_USER/deployment_summary.txt"
    
    # Final steps
    log_step "Final system cleanup..."
    apt-get autoremove -y -qq
    apt-get autoclean -qq
    
    log_info "✅ HD Tickets production server provisioning completed successfully!"
    log_info ""
    log_info "🔑 Important: Add your SSH public key to /home/$DEPLOY_USER/.ssh/authorized_keys"
    log_info "🌐 Configure DNS A records for $DOMAIN and $WWW_DOMAIN to point to this server"
    log_info "🔒 Run SSL certificate setup: certbot --apache -d $DOMAIN -d $WWW_DOMAIN"
    log_info "📋 Check deployment summary: /home/$DEPLOY_USER/deployment_summary.txt"
    log_info ""
    log_warn "⚠️  Database password: $DB_PASSWORD"
    log_warn "⚠️  Redis password: $REDIS_PASSWORD"
    log_warn "⚠️  Save these credentials securely!"
}

# Run main function
main "$@"