# HD Tickets Manual Deployment Guide

## SSH Troubleshooting & Manual Setup

If SSH access is not working, here are the steps to manually set up your HD Tickets deployment.

### Step 1: Access the Droplet Console

1. Go to [DigitalOcean Control Panel](https://cloud.digitalocean.com/)
2. Navigate to Droplets → hdtickets-production
3. Click "Console" to access the web terminal
4. Log in as root

### Step 2: Enable SSH Access

Run these commands in the console:

```bash
# Create SSH directory
mkdir -p /root/.ssh
chmod 700 /root/.ssh

# Add your SSH key
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' >> /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

# Ensure SSH service is running
systemctl status ssh
systemctl start ssh
systemctl enable ssh

# Check if firewall is blocking SSH
ufw status
ufw allow ssh
ufw --force enable
```

### Step 3: Alternative - Reset Droplet (if needed)

If SSH still doesn't work, you may need to:

```bash
# On your local machine
doctl compute droplet-action reboot 522849266
```

Wait 2-3 minutes after reboot, then try SSH again.

### Step 4: Manual Server Provisioning

If you prefer to set up manually through the console, here's the complete setup:

#### System Update and Basic Packages
```bash
apt-get update -y
apt-get upgrade -y
apt-get install -y curl wget git unzip software-properties-common \
    apt-transport-https ca-certificates gnupg lsb-release ufw fail2ban \
    acl htop tree vim tmux
```

#### Create Deploy User
```bash
useradd -m -s /bin/bash deploy
usermod -aG sudo deploy
sudo -u deploy mkdir -p /home/deploy/.ssh
sudo -u deploy chmod 700 /home/deploy/.ssh
echo 'ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIKPInr2Qy1Z+3JAF+Irn2KNHccQCpi015Juqf34EL8Qq lubomir@polascin.net' | sudo -u deploy tee /home/deploy/.ssh/authorized_keys
sudo -u deploy chmod 600 /home/deploy/.ssh/authorized_keys
```

#### Install PHP 8.3
```bash
add-apt-repository ppa:ondrej/php -y
apt-get update
apt-get install -y php8.3 php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-redis php8.3-mbstring php8.3-xml \
    php8.3-bcmath php8.3-curl php8.3-intl php8.3-gd php8.3-zip \
    php8.3-soap php8.3-imap php8.3-opcache
```

#### Install Composer
```bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer
```

#### Install Node.js 20
```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
```

#### Install Apache
```bash
apt-get install -y apache2
a2enmod rewrite headers ssl proxy_fcgi setenvif http2
a2enconf php8.3-fpm
a2dissite 000-default
systemctl restart apache2
```

#### Install MySQL
```bash
apt-get install -y mysql-server
mysql_secure_installation
```

#### Install Redis
```bash
apt-get install -y redis-server
systemctl restart redis
systemctl enable redis
```

#### Create Database and User
```bash
# Generate passwords (save these securely!)
DB_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)
REDIS_PASSWORD=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)

echo "DB_PASSWORD: $DB_PASSWORD"
echo "REDIS_PASSWORD: $REDIS_PASSWORD"

# Create database
mysql -e "CREATE DATABASE hdtickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER 'hdtickets'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
mysql -e "GRANT ALL PRIVILEGES ON hdtickets.* TO 'hdtickets'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
```

#### Configure Redis
```bash
sed -i "s/# requirepass foobared/requirepass $REDIS_PASSWORD/" /etc/redis/redis.conf
sed -i 's/bind 127.0.0.1 ::1/bind 127.0.0.1/' /etc/redis/redis.conf
systemctl restart redis
```

#### Create Apache Virtual Host
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
systemctl reload apache2
```

#### Create Application Directories
```bash
mkdir -p /var/www/hdtickets/{releases,shared,shared/storage,shared/bootstrap/cache,backups}
chown -R deploy:www-data /var/www/hdtickets
chmod -R 755 /var/www/hdtickets
```

#### Install Certbot
```bash
apt-get install -y certbot python3-certbot-apache
```

### Step 5: Test SSH from Local Machine

After completing the manual setup:

```bash
# Test SSH connection
ssh deploy@$(doctl compute droplet get hdtickets-production --format PublicIPv4 --no-header) "echo 'SSH working!'"
```

### Step 6: SSL Certificate Setup

Once SSH is working, from the droplet:

```bash
certbot --apache -d hd-tickets.com -d www.hd-tickets.com --non-interactive --agree-tos --email lubomir@polascin.net --redirect
```

### Step 7: Run Automated Deployment

Once SSH is working and basic setup is complete:

```bash
# From your local machine
./scripts/deploy-setup.sh
```

Or run individual deployment steps:

```bash
# Deploy with Deployer
~/.config/composer/vendor/bin/dep deploy production

# If that fails, manual deployment:
ssh deploy@DROPLET_IP "
    cd /var/www/hdtickets
    git clone https://github.com/polascin/hdTickets.git current
    cd current
    composer install --no-dev --optimize-autoloader
    npm ci
    npm run build
    cp .env.example .env
    # Edit .env with database credentials
    php artisan key:generate
    php artisan migrate --force
"
```

## Troubleshooting Common Issues

### SSH Connection Reset
- Try rebooting the droplet: `doctl compute droplet-action reboot 522849266`
- Check firewall: `ufw allow ssh`
- Restart SSH service: `systemctl restart ssh`

### Permission Denied
- Check key permissions: `chmod 600 ~/.ssh/authorized_keys`
- Verify key format is correct
- Try password authentication temporarily (not recommended for production)

### SSL Certificate Issues
- Ensure DNS is pointing to droplet IP
- Check Apache virtual host configuration
- Verify domain ownership with IONOS

Let me know which approach you'd like to take!