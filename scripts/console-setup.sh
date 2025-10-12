#!/bin/bash
# HD Tickets Complete Server Setup - DigitalOcean Console Version
# Copy and paste this entire script into the DigitalOcean console
# Run as root user

set -euo pipefail

echo "🚀 Starting HD Tickets server setup..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

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

# Configuration
DOMAIN="hd-tickets.com"
WWW_DOMAIN="www.hd-tickets.com"
DEPLOY_USER="deploy"
DEPLOY_PATH="/var/www/hdtickets"
PHP_VERSION="8.3"

# Step 1: System Update
log_step "Updating system packages..."
apt-get update -qq
DEBIAN_FRONTEND=noninteractive apt-get upgrade -y -qq

# Step 2: Install essential packages
log_step "Installing essential packages..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
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
    tmux \
    openssh-server

# Step 3: Configure SSH first
log_step "Configuring SSH access..."
mkdir -p /root/.ssh
chmod 700 /root/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' >> /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

# Ensure SSH service is running
systemctl enable ssh
systemctl start ssh

log_info "SSH access configured for root user"

# Step 4: Create deploy user
log_step "Creating deploy user..."
useradd -m -s /bin/bash "$DEPLOY_USER"
usermod -aG sudo "$DEPLOY_USER"

# Create SSH directory for deploy user
sudo -u "$DEPLOY_USER" mkdir -p "/home/$DEPLOY_USER/.ssh"
sudo -u "$DEPLOY_USER" chmod 700 "/home/$DEPLOY_USER/.ssh"
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' | sudo -u "$DEPLOY_USER" tee "/home/$DEPLOY_USER/.ssh/authorized_keys" > /dev/null
sudo -u "$DEPLOY_USER" chmod 600 "/home/$DEPLOY_USER/.ssh/authorized_keys"

log_info "Deploy user '$DEPLOY_USER' created"

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
systemctl enable fail2ban
systemctl start fail2ban
log_info "Fail2Ban configured and started"

# Step 7: Install PHP 8.3
log_step "Installing PHP $PHP_VERSION..."
add-apt-repository ppa:ondrej/php -y
apt-get update -qq

DEBIAN_FRONTEND=noninteractive apt-get install -y -qq \
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
PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
sed -i 's/display_errors = On/display_errors = Off/' "$PHP_INI"
sed -i 's/expose_php = On/expose_php = Off/' "$PHP_INI"
sed -i 's/memory_limit = 128M/memory_limit = 512M/' "$PHP_INI"
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 64M/' "$PHP_INI"
sed -i 's/post_max_size = 8M/post_max_size = 64M/' "$PHP_INI"
sed -i 's/max_execution_time = 30/max_execution_time = 300/' "$PHP_INI"

# Enable OPcache
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

# Step 9: Install Node.js 20
log_step "Installing Node.js 20..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq nodejs
log_info "Node.js $(node --version) and npm $(npm --version) installed"

# Step 10: Install Apache
log_step "Installing and configuring Apache..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq apache2

# Enable required Apache modules
a2enmod rewrite
a2enmod headers
a2enmod ssl
a2enmod proxy_fcgi
a2enmod setenvif
a2enmod http2

# Enable PHP-FPM configuration
a2enconf "php${PHP_VERSION}-fpm"

# Disable default site
a2dissite 000-default

systemctl restart apache2
log_info "Apache installed and configured"

# Step 11: Install MySQL
log_step "Installing MySQL..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq mysql-server

# Generate passwords
DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
REDIS_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS hdtickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS 'hdtickets'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON hdtickets.* TO 'hdtickets'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

log_info "MySQL installed and configured"

# Step 12: Install and configure Redis
log_step "Installing Redis..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq redis-server

# Configure Redis
sed -i "s/# requirepass foobared/requirepass ${REDIS_PASSWORD}/" /etc/redis/redis.conf
sed -i 's/bind 127.0.0.1 ::1/bind 127.0.0.1/' /etc/redis/redis.conf
sed -i 's/# maxmemory <bytes>/maxmemory 1gb/' /etc/redis/redis.conf
sed -i 's/# maxmemory-policy noeviction/maxmemory-policy allkeys-lru/' /etc/redis/redis.conf
sed -i 's/appendonly no/appendonly yes/' /etc/redis/redis.conf

systemctl restart redis
log_info "Redis installed and configured"

# Step 13: Create application directories
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

# Step 14: Install Certbot
log_step "Installing Certbot..."
DEBIAN_FRONTEND=noninteractive apt-get install -y -qq certbot python3-certbot-apache
log_info "Certbot installed"

# Step 15: Create Apache virtual host
log_step "Creating Apache virtual host..."
cat > "/etc/apache2/sites-available/${DOMAIN}.conf" << EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    ServerAlias ${WWW_DOMAIN}
    DocumentRoot ${DEPLOY_PATH}/current/public
    
    # Security Headers
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    
    # Laravel Document Root
    <Directory "${DEPLOY_PATH}/current/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    
    # PHP-FPM Configuration
    <FilesMatch \.php$>
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

# Step 16: Create environment file template
log_step "Creating environment file template..."
cat > "$DEPLOY_PATH/shared/.env.template" << EOF
APP_NAME="HD Tickets"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://${DOMAIN}
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hdtickets
DB_USERNAME=hdtickets
DB_PASSWORD=${DB_PASSWORD}
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379
REDIS_DB=0

CACHE_DRIVER=redis
CACHE_PREFIX=hdtickets_cache

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.${DOMAIN}

QUEUE_CONNECTION=redis
QUEUE_DEFAULT=default

BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@${DOMAIN}"
MAIL_FROM_NAME="\${APP_NAME}"

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="\${APP_NAME}"
VITE_PUSHER_APP_KEY="\${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="\${PUSHER_HOST}"
VITE_PUSHER_PORT="\${PUSHER_PORT}"
VITE_PUSHER_SCHEME="\${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="\${PUSHER_APP_CLUSTER}"

HORIZON_DOMAIN=${DOMAIN}
HORIZON_PREFIX=horizon:
HORIZON_REDIS_CONNECTION=horizon
HORIZON_DARKMODE=true

LOG_CHANNEL=daily
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

TELESCOPE_ENABLED=false

BCRYPT_ROUNDS=12

# Third-party APIs (configure as needed)
TICKET_PLATFORM_API_KEY=
TICKETMASTER_ENABLED=true
TICKETMASTER_API_KEY=
SEATGEEK_ENABLED=false
SEATGEEK_CLIENT_ID=
SEATGEEK_CLIENT_SECRET=
EVENTBRITE_ENABLED=false
EVENTBRITE_API_KEY=
STUBHUB_ENABLED=false
STUBHUB_API_KEY=
BANDSINTOWN_ENABLED=false
BANDSINTOWN_APP_ID=
VIAGOGO_ENABLED=false
VIAGOGO_API_KEY=
TICKPICK_ENABLED=false
TICKPICK_API_KEY=
MANCHESTER_UNITED_ENABLED=false

OPCACHE_ENABLE=1
EOF

chown "$DEPLOY_USER:www-data" "$DEPLOY_PATH/shared/.env.template"
chmod 640 "$DEPLOY_PATH/shared/.env.template"

log_info "Environment template created"

# Step 17: Save credentials
cat > "/home/$DEPLOY_USER/deployment_info.txt" << EOF
HD Tickets Deployment Information
=================================

Server Details:
- Domain: ${DOMAIN}
- Deploy Path: ${DEPLOY_PATH}
- Deploy User: ${DEPLOY_USER}
- PHP Version: ${PHP_VERSION}

Database Credentials:
- Database: hdtickets
- Username: hdtickets
- Password: ${DB_PASSWORD}

Redis Credentials:
- Password: ${REDIS_PASSWORD}

Next Steps:
1. Test SSH connection: ssh ${DEPLOY_USER}@DROPLET_IP
2. Set up SSL: certbot --apache -d ${DOMAIN} -d ${WWW_DOMAIN} --email lubomir@polascin.net
3. Deploy application using Deployer from local machine

Files Created:
- Environment template: ${DEPLOY_PATH}/shared/.env.template
- Apache virtual host: /etc/apache2/sites-available/${DOMAIN}.conf
- Deployment info: /home/${DEPLOY_USER}/deployment_info.txt
EOF

chown "$DEPLOY_USER:$DEPLOY_USER" "/home/$DEPLOY_USER/deployment_info.txt"

# Step 18: Final cleanup
log_step "Final system cleanup..."
apt-get autoremove -y -qq
apt-get autoclean -qq

# Step 19: Test SSH setup
log_step "Testing SSH configuration..."
systemctl status ssh --no-pager

log_info "✅ HD Tickets server setup completed successfully!"
log_info ""
log_info "🔐 IMPORTANT CREDENTIALS (save these securely!):"
log_info "Database Password: ${DB_PASSWORD}"
log_info "Redis Password: ${REDIS_PASSWORD}"
log_info ""
log_info "📋 Next Steps:"
log_info "1. Test SSH from your local machine"
log_info "2. Set up SSL certificates"
log_info "3. Deploy the application"
log_info ""
log_info "🔗 Full deployment info saved to: /home/$DEPLOY_USER/deployment_info.txt"