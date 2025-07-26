# hdtickets Development Environment Setup - Laragon

This document provides comprehensive step-by-step instructions for setting up the hdtickets sneaker monitoring web application using Laragon on Windows.

## Project Overview

hdtickets is a Laravel-based sneaker monitoring and purchasing automation system that includes:
- Real-time product monitoring
- Automated purchasing workflows
- Account management for multiple platforms
- Queue-based job processing
- Web scraping with anti-detection

## Prerequisites

- Laragon installed at `C:\laragon`
- Document root configured at `G:\"Môj disk"\www`
- Composer installed globally
- Node.js 18+ and npm available
- Redis server (for caching and queues)
- Git for version control

## hdtickets Project Setup

### Step 1: Navigate to Document Root
```bash
cd G:\"Môj disk"\www
```

### Step 2: Clone or Initialize hdtickets Project

If cloning from repository:
```bash
git clone <repository-url> hdtickets
cd hdtickets/sneaker-bot
```

If starting fresh:
```bash
mkdir hdtickets
cd hdtickets
composer create-project laravel/laravel sneaker-bot
cd sneaker-bot
```

### Step 3: Install Composer Dependencies
```bash
composer install
```

This will install:
- Laravel 11.x framework
- All required PHP packages
- Development dependencies

## Database Configuration

### Step 1: Create Local MySQL Database in Laragon

1. **Open Laragon Control Panel**
2. **Start MySQL Service** (if not already running)
3. **Access phpMyAdmin** or use MySQL command line
4. **Create a new database** for your project:
   ```sql
   CREATE DATABASE ticket_monitor_db;
   ```

### Step 2: Configure .env File

Navigate to your project directory:

```bash
cd G:\"Môj disk"\www\hdtickets\sneaker-bot
```

Copy the environment file and update configuration:

```bash
cp .env.example .env
```

Update the following configuration in `.env`:

```ini
# Application
APP_NAME=hdtickets
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://hdtickets.test

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hdtickets_db
DB_USERNAME=root
DB_PASSWORD=

# Queue Configuration
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache Configuration
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Scraping Configuration
SCRAPE_DELAY_MIN=1000
SCRAPE_DELAY_MAX=3000
USER_AGENT_ROTATION=true
PROXY_ENABLED=false
```

**Note**: Laragon's default MySQL configuration uses `root` with no password for local development.

### Step 3: Test Database Connection
```bash
php artisan migrate
```

## Required PHP Extensions

### Step 1: Access PHP Extensions in Laragon

1. **Open Laragon**
2. **Right-click on Laragon tray icon**
3. **Go to PHP → Extensions**

### Step 2: Enable Required Extensions

Ensure the following extensions are enabled:

- ✅ `php_openssl`
- ✅ `php_mbstring` 
- ✅ `php_tokenizer`
- ✅ `php_xml`
- ✅ `php_ctype`
- ✅ `php_json`
- ✅ `php_pdo_mysql`
- ✅ `php_curl`
- ✅ `php_fileinfo`
- ✅ `php_zip`

### Step 3: Restart Apache/Nginx
After enabling extensions, restart the web server through Laragon.

## Node.js Dependencies

### Step 1: Navigate to Project Directory
```bash
cd G:\"Môj disk"\www\ticket-monitor
```

### Step 2: Install Base Node.js Dependencies
```bash
npm install
```

### Step 3: Install Puppeteer and Additional Packages
```bash
npm install puppeteer
npm install puppeteer-extra puppeteer-extra-plugin-stealth
```

### Step 4: Install Development Dependencies
```bash
npm install --save-dev @vitejs/plugin-laravel laravel-vite-plugin
```

### Step 5: Build Assets
```bash
npm run build
```

## Queue Worker Setup

### Option 1: Laravel Horizon (Recommended)

#### Step 1: Install Horizon
```bash
composer require laravel/horizon
```

#### Step 2: Publish Horizon Assets
```bash
php artisan horizon:install
```

#### Step 3: Configure Horizon
Update `config/horizon.php` as needed for your environment.

#### Step 4: Start Horizon
```bash
php artisan horizon
```

#### Step 5: Access Horizon Dashboard
Visit: `http://ticket-monitor.test/horizon`

### Option 2: Supervisor (Alternative)

#### Step 1: Install Supervisor
For Windows, you can use alternatives like:
- Windows Task Scheduler
- NSSM (Non-Sucking Service Manager)
- Manual queue worker management

#### Step 2: Create Queue Worker Command
```bash
php artisan queue:work --sleep=3 --tries=3
```

#### Step 3: Configure as Windows Service (using NSSM)
```bash
nssm install LaravelQueue
nssm set LaravelQueue Application "C:\laragon\bin\php\php-8.2.4-Win32-vs16-x64\php.exe"
nssm set LaravelQueue AppParameters "artisan queue:work --sleep=3 --tries=3"
nssm set LaravelQueue AppDirectory "G:\Môj disk\www\ticket-monitor"
nssm start LaravelQueue
```

## Environment Verification

### Step 1: Check Laravel Installation
```bash
php artisan --version
```

### Step 2: Test Database Connection
```bash
php artisan tinker
# In tinker:
DB::connection()->getPdo();
```

### Step 3: Test Queue System
```bash
# Create a test job
php artisan make:job TestJob

# Dispatch the job
php artisan tinker
# In tinker:
App\Jobs\TestJob::dispatch();
```

### Step 4: Verify Node.js Setup
```bash
node --version
npm --version
puppeteer --version
```

## Local Development Server

### Option 1: Laravel Artisan Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

### Option 2: Laragon Auto-Host
Laragon automatically creates a virtual host at:
`http://ticket-monitor.test`

## Project Structure Overview

```
ticket-monitor/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── tests/
├── vendor/
├── .env
├── artisan
├── composer.json
├── package.json
└── vite.config.js
```

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Verify MySQL is running in Laragon
   - Check database credentials in `.env`

2. **Composer Install Fails**
   - Ensure PHP extensions are enabled
   - Check internet connectivity

3. **NPM Install Issues**
   - Clear npm cache: `npm cache clean --force`
   - Delete `node_modules` and reinstall

4. **Queue Workers Not Processing**
   - Check queue driver in `.env`: `QUEUE_CONNECTION=database`
   - Run migrations: `php artisan migrate`

### Useful Commands

```bash
# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create symbolic link for storage
php artisan storage:link
```

## Next Steps

1. Configure your application-specific settings
2. Set up version control (Git)
3. Configure your IDE/editor
4. Set up testing environment
5. Configure deployment pipeline

---

**Created**: 2025-07-20  
**Environment**: Laragon on Windows  
**Laravel Version**: Latest  
**PHP Version**: 8.x  
**Document Root**: `G:\"Môj disk"\www`
