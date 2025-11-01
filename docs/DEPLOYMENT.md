# Deployment Setup Guide

This guide explains how to set up automatic deployment from GitHub to Digital Ocean.

## Prerequisites

1. GitHub repository: `polascin/hdtickets`
2. Digital Ocean droplet with SSH access
3. PHP 8.3+, Composer, Node.js 20+ installed on the droplet
4. Git installed and configured on the droplet

## Setup Steps

### 1. Generate SSH Deploy Key

On your **local machine** or the **droplet**, generate a dedicated SSH key for deployments:

```bash
ssh-keygen -t ed25519 -C "github-deploy-hdtickets" -f ~/.ssh/github_deploy_hdtickets
```

**Important**: Do NOT set a passphrase (press Enter when prompted).

### 2. Add Public Key to Droplet

Copy the public key to your droplet's authorised keys:

```bash
# Display the public key
cat ~/.ssh/github_deploy_hdtickets.pub

# On the droplet, add it to authorized_keys
# SSH into your droplet first
mkdir -p ~/.ssh
echo "YOUR_PUBLIC_KEY_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 700 ~/.ssh
```

### 3. Configure GitHub Secrets

Go to your GitHub repository: `https://github.com/polascin/hdtickets/settings/secrets/actions`

Add the following secrets:

#### Required Secrets:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `DEPLOY_SSH_KEY` | Private SSH key content | Entire content of `github_deploy_hdtickets` file |
| `DEPLOY_USER` | SSH user on droplet | `lubomir` or `root` |
| `DEPLOY_HOST` | Droplet IP or hostname | `123.45.67.89` or `hdtickets.example.com` |
| `DEPLOY_PATH` | Absolute path to application | `/var/www/hdtickets` |
| `MAINTENANCE_SECRET` | Secret for bypass during maintenance | Generate random string |

#### Optional (doctl) Secrets for auto-resolving droplet IP:

| Secret Name | Description | Example |
|-------------|-------------|---------|
| `DO_API_TOKEN` | DigitalOcean API token (read-only is enough) | `************` |
| `DO_DROPLET_NAME` | Droplet name to resolve IP from | `hdtickets-prod` |
| `DO_DROPLET_ID` | Droplet ID (overrides name/tag) | `123456789` |
| `DO_DROPLET_TAG` | Droplet tag to pick first matching droplet | `production` |

#### To get the private key content:
```bash
cat ~/.ssh/github_deploy_hdtickets
```

Copy the **entire output** including `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----`.

#### To generate maintenance secret:
```bash
openssl rand -base64 32
```

### 4. Prepare Droplet for Deployment

On your **droplet**, ensure the repository is cloned and configured:

```bash
# Navigate to web root
cd /var/www

# If not already cloned, clone the repository
git clone https://github.com/polascin/hdtickets.git
cd hdtickets

# Set correct ownership (adjust user as needed)
sudo chown -R lubomir:lubomir /var/www/hdtickets

# Configure Git
git config pull.rebase false
git config --global --add safe.directory /var/www/hdtickets

# Ensure storage and cache directories are writable
chmod -R 775 storage bootstrap/cache
```

### 5. Test SSH Connection

From your local machine, test the SSH connection:

```bash
ssh -i ~/.ssh/github_deploy_hdtickets lubomir@YOUR_DROPLET_IP "cd /var/www/hdtickets && pwd"
```

This should output: `/var/www/hdtickets`

### 6. Configure Web Server

Ensure your web server (Nginx/Apache) serves from `/var/www/hdtickets/public` and has proper permissions.

**Nginx example** (`/etc/nginx/sites-available/hdtickets`):
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/hdtickets/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Deployment Workflow

### Automatic Deployment

When you push to the `main` branch, GitHub Actions will automatically:

1. ✅ Put the application in maintenance mode
2. ✅ Pull latest changes from GitHub
3. ✅ Install/update Composer dependencies
4. ✅ Install/update NPM dependencies  
5. ✅ Build frontend assets
6. ✅ Clear all caches
7. ✅ Run database migrations
8. ✅ Optimise application (cache routes, config, views)
9. ✅ Restart queue workers (Horizon)
10. ✅ Bring application back online

### Manual Deployment

You can also trigger deployment manually from GitHub Actions:
1. Go to: `https://github.com/polascin/hdtickets/actions/workflows/deploy.yml`
2. Click "Run workflow"
3. Select branch: `main`
4. Click "Run workflow"

### Bypass Maintenance Mode

During maintenance, access the site using:
```
https://your-domain.com?secret=YOUR_MAINTENANCE_SECRET
```

## Monitoring Deployments

### View Deployment Logs

1. Go to: `https://github.com/polascin/hdtickets/actions`
2. Click on the latest "Deploy to Digital Ocean" workflow
3. Click on "Deploy to Production" job to view detailed logs

### SSH into Droplet

```bash
ssh lubomir@YOUR_DROPLET_IP
cd /var/www/hdtickets
git log -1  # View latest commit
php artisan --version  # Verify Laravel is working
```

### Check Application Logs

```bash
tail -f /var/www/hdtickets/storage/logs/laravel.log
```

## Troubleshooting

### Deployment Fails at "Pull latest changes"

**Issue**: Git permission errors or conflicts

**Solution**:
```bash
# SSH into droplet
cd /var/www/hdtickets
git config --global --add safe.directory /var/www/hdtickets
git reset --hard origin/main
```

### Deployment Fails at "Composer install"

**Issue**: Missing dependencies or memory limits

**Solution**:
```bash
# Increase PHP memory limit temporarily
php -d memory_limit=512M /usr/local/bin/composer install --no-dev --optimize-autoloader
```

### Site Stuck in Maintenance Mode

**Solution**:
```bash
# SSH into droplet
cd /var/www/hdtickets
php artisan up
```

### Queue Workers Not Restarting

**Solution**:
```bash
# SSH into droplet
cd /var/www/hdtickets
php artisan horizon:terminate
# Or if not using Horizon:
php artisan queue:restart
```

### Permission Issues After Deployment

**Solution**:
```bash
# SSH into droplet
cd /var/www/hdtickets
sudo chown -R lubomir:www-data .
chmod -R 775 storage bootstrap/cache
```

## Security Best Practices

1. ✅ Use dedicated SSH key for deployments (not your personal key)
2. ✅ Store all secrets in GitHub Secrets (never commit them)
3. ✅ Use `--no-dev` for Composer in production
4. ✅ Keep maintenance secret secure and rotate periodically
5. ✅ Regularly update dependencies and apply security patches
6. ✅ Monitor deployment logs for suspicious activity
7. ✅ Use HTTPS with valid SSL certificate in production

## Rollback Procedure

If a deployment introduces issues:

```bash
# SSH into droplet
cd /var/www/hdtickets

# Put site in maintenance mode
php artisan down

# View recent commits
git log --oneline -10

# Rollback to previous commit
git reset --hard COMMIT_HASH

# Reinstall dependencies
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Run migrations (if needed)
php artisan migrate

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart workers
php artisan horizon:terminate

# Bring site back online
php artisan up
```

## Additional Resources

- [Laravel Deployment Documentation](https://laravel.com/docs/11.x/deployment)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Digital Ocean Droplet Setup](https://docs.digitalocean.com/products/droplets/)
