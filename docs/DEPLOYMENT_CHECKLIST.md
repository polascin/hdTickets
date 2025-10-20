# HD Tickets Production Deployment Checklist

## Current Status: SSH Connection Issue

The automated deployment is blocked by SSH connectivity. Here's the step-by-step manual process:

## Phase 1: Server Preparation (DigitalOcean Console)

### ✅ DNS Configuration 
- [x] hd-tickets.com → `************` (confirmed working)
- [x] www.hd-tickets.com → `************` (confirmed working)

### 🔧 Server Setup (via DigitalOcean Console)

**Access Console:**
1. Go to [DigitalOcean Control Panel](https://cloud.digitalocean.com/)
2. Droplets → hdtickets-production → Console
3. Login as root

**Run these commands in order:**

#### 1. Enable SSH Service
```bash
# Install and start SSH
apt-get update
apt-get install -y openssh-server
systemctl enable ssh
systemctl start ssh
systemctl status ssh

# Configure SSH keys
mkdir -p /root/.ssh
chmod 700 /root/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' >> /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

# Test SSH is listening
netstat -tlnp | grep :22
```

#### 2. Configure Firewall
```bash
# Install and configure UFW
apt-get install -y ufw
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw --force enable
ufw status
```

#### 3. Create Deploy User
```bash
# Create deploy user with sudo access
useradd -m -s /bin/bash deploy
usermod -aG sudo deploy
passwd deploy  # Set a temporary password

# Setup SSH for deploy user
mkdir -p /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' > /home/deploy/.ssh/authorized_keys
chmod 600 /home/deploy/.ssh/authorized_keys
chown -R deploy:deploy /home/deploy/.ssh
```

**After completing these steps, test SSH from your local machine:**

```bash
ssh deploy@$(doctl compute droplet get hdtickets-production --format PublicIPv4 --no-header)
```

---

## Phase 2: Complete Server Setup (Once SSH Works)

### Option A: Run Full Console Setup Script
Once SSH is working, you can run the complete setup script:

1. Copy `scripts/console-setup.sh` to the server
2. Run as root: `bash console-setup.sh`

### Option B: Manual Step-by-Step Setup

#### Install Core Packages
```bash
# Update system
apt-get update && apt-get upgrade -y

# Install essential packages
apt-get install -y curl wget git unzip software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release \
    ufw fail2ban acl htop tree vim tmux

# Install PHP 8.3
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-intl php8.3-gd php8.3-zip \
    php8.3-soap php8.3-imap php8.3-opcache

# Install Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# Install Apache
apt-get install -y apache2
a2enmod rewrite headers ssl proxy_fcgi setenvif http2
a2enconf php8.3-fpm
a2dissite 000-default

# Install MySQL
apt-get install -y mysql-server

# Install Redis
apt-get install -y redis-server
```

#### Configure Services
```bash
# Generate secure passwords
DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
REDIS_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)

echo "DB_PASSWORD: $DB_PASSWORD" | tee /home/deploy/credentials.txt
echo "REDIS_PASSWORD: $REDIS_PASSWORD" | tee -a /home/deploy/credentials.txt
chown deploy:deploy /home/deploy/credentials.txt
chmod 600 /home/deploy/credentials.txt

# Setup MySQL database
mysql -e "CREATE DATABASE hdtickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'hdtickets'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON hdtickets.* TO 'hdtickets'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Configure Redis
sed -i "s/# requirepass foobared/requirepass $REDIS_PASSWORD/" /etc/redis/redis.conf
systemctl restart redis
```

#### Create Application Structure
```bash
# Create deployment directories
mkdir -p /var/www/hdtickets/{releases,shared,shared/storage,shared/bootstrap/cache,backups}
chown -R deploy:www-data /var/www/hdtickets
chmod -R 755 /var/www/hdtickets
```

#### Setup Apache Virtual Host
```bash
cat > /etc/apache2/sites-available/hd-tickets.com.conf << 'EOF'
<VirtualHost *:80>
    ServerName hd-tickets.com
    ServerAlias www.hd-tickets.com
    DocumentRoot /var/www/hdtickets/current/public
    
    <Directory "/var/www/hdtickets/current/public">
        Options -Indexes
        AllowOverride All
        Require all granted
    </Directory>
    
    <FilesMatch \.php$>
        SetHandler "proxy:fcgi://127.0.0.1:9000"
    </FilesMatch>
    
    ErrorLog ${APACHE_LOG_DIR}/hd-tickets.com-error.log
    CustomLog ${APACHE_LOG_DIR}/hd-tickets.com-access.log combined
</VirtualHost>
EOF

a2ensite hd-tickets.com
systemctl restart apache2
```

---

## Phase 3: SSL Certificate Setup

```bash
# Install Certbot
apt-get install -y certbot python3-certbot-apache

# Get SSL certificates
certbot --apache -d hd-tickets.com -d www.hd-tickets.com \
    --non-interactive --agree-tos --email lubomir@polascin.net --redirect

# Verify SSL
systemctl status certbot.timer
certbot certificates
```

---

## Phase 4: Application Deployment

### Create Production Environment
```bash
# Create .env from template
cp /var/www/hdtickets/shared/.env.template /var/www/hdtickets/shared/.env
chown deploy:www-data /var/www/hdtickets/shared/.env
chmod 640 /var/www/hdtickets/shared/.env

# Edit .env with actual credentials (use nano or vim)
nano /var/www/hdtickets/shared/.env
```

### Deploy with Deployer (from local machine)
```bash
# Test Deployer configuration
~/.config/composer/vendor/bin/dep config:hosts

# Run first deployment
~/.config/composer/vendor/bin/dep deploy production

# If deployment succeeds, the site should be live at https://hd-tickets.com
```

### Manual Deployment (if Deployer fails)
```bash
# Clone repository
cd /var/www/hdtickets
git clone https://github.com/polascin/hdTickets.git current
cd current

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# Copy environment file
cp ../shared/.env .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Generate Passport keys
php artisan passport:keys --force

# Create storage link
php artisan storage:link

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Set permissions
chown -R deploy:www-data /var/www/hdtickets
chmod -R 755 /var/www/hdtickets/current/storage
chmod -R 755 /var/www/hdtickets/current/bootstrap/cache
```

---

## Phase 5: Setup Horizon & Scheduler

### Horizon Service
```bash
# Copy service file
cp /var/www/hdtickets/current/scripts/services/hdtickets-horizon.service /etc/systemd/system/
systemctl daemon-reload
systemctl enable hdtickets-horizon
systemctl start hdtickets-horizon
systemctl status hdtickets-horizon
```

### Laravel Scheduler
```bash
# Add to www-data crontab
sudo -u www-data crontab -e
# Add this line:
# * * * * * cd /var/www/hdtickets/current && php artisan schedule:run >> /dev/null 2>&1
```

---

## Phase 6: Verification

### Test Application
```bash
# Check website
curl -I https://hd-tickets.com

# Check health endpoint
curl https://hd-tickets.com/health

# Check services
systemctl status apache2
systemctl status mysql
systemctl status redis
systemctl status hdtickets-horizon
```

### Check Logs
```bash
tail -f /var/log/apache2/hd-tickets.com-error.log
tail -f /var/www/hdtickets/current/storage/logs/laravel.log
```

---

## Current Action Required

**Please complete Phase 1 (Server Preparation) first:**

1. Access DigitalOcean Console
2. Run the SSH setup commands
3. Test SSH connectivity from local machine
4. Once SSH works, continue with the remaining phases

**Let me know when SSH is working and I can assist with the automated deployment!**

## Quick Status Check Commands

After each phase, verify with:

```bash
# Check SSH
ssh deploy@DROPLET_IP "whoami && pwd"

# Check services
ssh deploy@DROPLET_IP "sudo systemctl status ssh apache2 mysql redis --no-pager"

# Check website
curl -I https://hd-tickets.com
```