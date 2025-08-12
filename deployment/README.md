# HD Tickets Deployment & Migration Guide

**Sports Events Entry Tickets Monitoring System**  
**Comprehensive Deployment with Zero Downtime & Data Integrity**

## Overview

This document provides complete instructions for deploying the HD Tickets sports events entry tickets monitoring system using blue-green deployment strategy with comprehensive data migration, monitoring, and rollback capabilities.

## System Requirements

- **Operating System**: Ubuntu 24.04 LTS
- **Web Server**: Apache2 with PHP 8.4 support
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Cache/Sessions**: Redis 6.0+
- **Queue Backend**: Redis
- **Load Balancer**: Nginx (for blue-green deployment)
- **Minimum Resources**: 4GB RAM, 50GB disk space
- **Network**: Stable internet connection for ticket platform API access

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    Load Balancer (Nginx)                   │
│                     hdtickets.local                        │
└─────────────────────┬───────────────────────────────────────┘
                      │
        ┌─────────────┼─────────────┐
        │             │             │
   ┌────▼───┐    ┌────▼───┐    ┌────▼───┐
   │ Blue   │    │ Green  │    │ Maint  │
   │ :8080  │    │ :9080  │    │ :8090  │
   └────────┘    └────────┘    └────────┘
        │             │             │
        └─────────────┼─────────────┘
                      │
          ┌───────────▼───────────┐
          │     Shared Services   │
          │  MySQL + Redis + FS   │
          └───────────────────────┘
```

## Quick Start

### 1. Prepare Infrastructure

```bash
# Clone repository
cd /var/www
sudo git clone <repository-url> hdtickets
cd hdtickets

# Set permissions
sudo chown -R www-data:www-data /var/www/hdtickets
sudo chmod -R 755 /var/www/hdtickets
sudo chmod -R 775 storage bootstrap/cache

# Make deployment scripts executable
sudo chmod +x deployment/blue-green/deploy.sh
sudo chmod +x deployment/migration/migrate-data.php
```

### 2. Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
npm ci --only=production
npm run build

# Copy environment configuration
cp .env.example .env
cp .env.example .env.blue
cp .env.example .env.green

# Generate application key
php artisan key:generate
```

### 3. Configure Environment

Edit `.env`, `.env.blue`, and `.env.green` files with appropriate settings:

```bash
# Main configuration
APP_NAME="HD Tickets"
APP_ENV=production
APP_URL=https://hdtickets.local

# Database (shared between environments)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hdtickets
DB_USERNAME=hdtickets_user
DB_PASSWORD=your_secure_password

# Redis (shared between environments)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Sports Events Ticket Platform APIs
TICKETMASTER_API_KEY=your_ticketmaster_api_key
STUBHUB_API_KEY=your_stubhub_api_key
VIAGOGO_API_KEY=your_viagogo_api_key
TICKPICK_API_KEY=your_tickpick_api_key
```

### 4. Deploy Application

```bash
# Run initial deployment
sudo ./deployment/blue-green/deploy.sh deploy

# Check deployment status
sudo ./deployment/blue-green/deploy.sh status
```

## Deployment Process

### Blue-Green Deployment Strategy

The system uses blue-green deployment to achieve zero downtime:

1. **Blue Environment** (Port 8080): Currently active environment serving traffic
2. **Green Environment** (Port 9080): Standby environment for new deployments
3. **Load Balancer**: Routes traffic between blue/green based on health checks

### Deployment Steps

#### Step 1: Pre-Deployment Preparation

```bash
# Verify system health
curl -s http://localhost/health | jq .

# Check current environment status
sudo ./deployment/blue-green/deploy.sh status

# Verify backup storage
sudo ls -la /var/backups/hdtickets/
```

#### Step 2: Database Migration

The system includes comprehensive data migration with batch processing:

```bash
# Run data validation only (dry run)
php artisan hdtickets:migrate-data --validate-only

# Run actual migration with progress monitoring
php artisan hdtickets:migrate-data --batch-size=1000

# Continue from checkpoint if interrupted
php artisan hdtickets:migrate-data --continue
```

#### Step 3: Execute Deployment

```bash
# Standard deployment
sudo ./deployment/blue-green/deploy.sh deploy

# Deployment with test skipping (faster)
sudo ./deployment/blue-green/deploy.sh deploy skip_tests

# Check deployment progress
tail -f /var/log/hdtickets/deployment_*.log
```

#### Step 4: Verify Deployment

```bash
# Health check
curl -s http://localhost/health/detailed | jq .

# Sports events system check
curl -s http://localhost/metrics/sports-events | jq .

# Performance metrics
curl -s http://localhost/metrics/performance | jq .
```

## Data Migration

### Migration Features

- **Batch Processing**: Handles large datasets efficiently
- **Progress Monitoring**: Real-time progress tracking via cache
- **Validation**: Pre and post-migration data integrity checks  
- **Checkpointing**: Resume from last successful point
- **Rollback Capability**: Restore to previous state if needed
- **Audit Trail**: Complete logging of all operations

### Migration Commands

```bash
# Full migration with validation
php artisan hdtickets:migrate-data

# Dry run (validation only)
php artisan hdtickets:migrate-data --dry-run

# Custom batch size for large datasets
php artisan hdtickets:migrate-data --batch-size=2000

# Rollback to previous version
php artisan hdtickets:migrate-data --rollback

# Force migration without confirmation
php artisan hdtickets:migrate-data --force
```

### Monitored Data Types

- **Sports Events**: Event details, teams, venues, schedules
- **Ticket Listings**: Prices, availability, seating information
- **Price History**: Historical pricing data for trend analysis
- **User Alerts**: Price drop alerts and availability notifications
- **Scraping Logs**: API call logs and scraping activity
- **Audit Trails**: User actions and system changes

## Configuration Management

### Environment-Specific Configuration

The system supports multiple environments with centralized configuration:

```php
// config/deployment.php
'environments' => [
    'development' => [...],
    'staging' => [...],
    'production' => [...],
    'blue' => ['extends' => 'production'],
    'green' => ['extends' => 'production']
]
```

### Hot Configuration Reload

```bash
# Enable hot reload (development only)
CONFIG_HOT_RELOAD=true

# Reload configuration without restart
php artisan config:reload
```

### Feature Flags

Control feature availability across environments:

```bash
# Enable new scraping engine
FEATURE_NEW_SCRAPING_ENGINE=true

# Enable advanced analytics
FEATURE_ADVANCED_ANALYTICS=true

# Enable mobile app API
FEATURE_MOBILE_APP_API=true
```

## Monitoring Setup

### Health Check Endpoints

| Endpoint | Purpose | Authentication |
|----------|---------|----------------|
| `/health` | Load balancer checks | None |
| `/health/detailed` | Comprehensive status | None |
| `/deployment/status` | Deployment info | IP-restricted |
| `/metrics/sports-events` | Business metrics | None |
| `/metrics/performance` | System metrics | None |
| `/ready` | Readiness probe | None |
| `/live` | Liveness probe | None |

### Monitoring Sports Events System

```bash
# Check sports events activity
curl -s http://localhost/metrics/sports-events | jq '.metrics.sports_events'

# Monitor ticket scraping
curl -s http://localhost/metrics/sports-events | jq '.metrics.system_activity'

# Check user engagement
curl -s http://localhost/metrics/sports-events | jq '.metrics.user_activity'
```

### Performance Monitoring

```bash
# System performance
curl -s http://localhost/metrics/performance | jq '.metrics.system_resources'

# Database performance  
curl -s http://localhost/metrics/performance | jq '.metrics.response_times.database'

# Queue status
curl -s http://localhost/metrics/performance | jq '.metrics.queue_stats'
```

### Alert Configuration

Configure monitoring alerts for:

- **Critical System Errors**: Database connectivity issues
- **Performance Degradation**: Response time > 2s
- **High Error Rates**: Error rate > 10%
- **Service Downtime**: Health check failures
- **Sports Events Issues**: No recent ticket updates
- **Scraping Problems**: High scraping failure rate
- **Queue Backlog**: Queue length > 1000 jobs

## Rollback Procedures

### Automatic Rollback Triggers

The deployment script automatically rolls back on:
- Health check failures
- Smoke test failures
- Database migration errors
- Critical system errors

### Manual Rollback

```bash
# Immediate rollback to previous environment
sudo ./deployment/blue-green/deploy.sh rollback

# Check rollback status
curl -s http://localhost/deployment/status | jq .
```

### Database Rollback

```bash
# Rollback database changes
php artisan hdtickets:migrate-data --rollback

# Restore from backup
sudo gunzip /var/backups/hdtickets/hdtickets_YYYYMMDD_HHMMSS.sql.gz
mysql -u hdtickets_user -p hdtickets < /var/backups/hdtickets/hdtickets_YYYYMMDD_HHMMSS.sql
```

## Troubleshooting

### Common Issues

#### 1. Deployment Hangs During Health Check

```bash
# Check Apache error logs
sudo tail -f /var/log/apache2/hdtickets_*_error.log

# Check application logs
sudo tail -f storage/logs/laravel.log

# Verify database connectivity
php artisan tinker
>>> DB::connection()->getPdo();
```

#### 2. Sports Events Data Not Updating

```bash
# Check scraping system
curl -s http://localhost/health/detailed | jq '.checks.scraping'

# Verify API credentials
php artisan tinker
>>> config('deployment.environments.production.ticket_platforms')

# Check scraping logs
mysql -u hdtickets_user -p -e "SELECT * FROM scraping_logs ORDER BY created_at DESC LIMIT 10;" hdtickets
```

#### 3. High Memory Usage

```bash
# Check memory usage
curl -s http://localhost/metrics/performance | jq '.metrics.system_resources.memory_usage'

# Clear application caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Restart services
sudo systemctl restart apache2
sudo systemctl restart redis-server
```

#### 4. Database Migration Fails

```bash
# Check migration logs
sudo tail -f storage/logs/migration_*.log

# Verify database permissions
mysql -u hdtickets_user -p -e "SHOW GRANTS;"

# Resume from checkpoint
php artisan hdtickets:migrate-data --continue
```

### Log Locations

- **Deployment Logs**: `/var/log/hdtickets/deployment_*.log`
- **Application Logs**: `storage/logs/laravel.log`
- **Apache Logs**: `/var/log/apache2/hdtickets_*_error.log`
- **Nginx Logs**: `/var/log/nginx/hdtickets_*.log`
- **Migration Logs**: `storage/logs/migration_*.log`

### Performance Optimization

#### Database Optimization

```bash
# Optimize MySQL for sports events data
mysql -u root -p << EOF
SET GLOBAL innodb_buffer_pool_size = 2G;
SET GLOBAL query_cache_size = 256M;
SET GLOBAL tmp_table_size = 256M;
SET GLOBAL max_heap_table_size = 256M;
EOF

# Add indexes for ticket monitoring queries
php artisan migrate
```

#### Redis Optimization

```bash
# Optimize Redis configuration
sudo nano /etc/redis/redis.conf
# maxmemory 1gb
# maxmemory-policy allkeys-lru

sudo systemctl restart redis-server
```

#### Apache/PHP Optimization

```bash
# Enable OPCache
sudo nano /etc/php/8.4/apache2/php.ini
# opcache.enable=1
# opcache.memory_consumption=256
# opcache.max_accelerated_files=20000

sudo systemctl restart apache2
```

## Security Considerations

### SSL/TLS Configuration

```bash
# Generate SSL certificate
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/ssl/private/hdtickets.key \
  -out /etc/ssl/certs/hdtickets.crt

# Set proper permissions
sudo chmod 600 /etc/ssl/private/hdtickets.key
sudo chmod 644 /etc/ssl/certs/hdtickets.crt
```

### API Security

```bash
# Rotate API keys regularly
TICKETMASTER_API_KEY=new_key_here
STUBHUB_API_KEY=new_key_here

# Update .env files
php artisan config:cache
```

### Access Control

```bash
# Restrict deployment endpoint access
# Edit nginx configuration to allow only specific IPs
allow 127.0.0.1;
allow 10.0.0.0/8;
deny all;
```

## Maintenance

### Regular Maintenance Tasks

#### Daily
- Monitor sports events data updates
- Check scraping success rates
- Verify user alert delivery
- Review system performance metrics

#### Weekly  
- Update ticket platform API credentials if needed
- Clean up old log files
- Optimize database tables
- Review and clear failed job queues

#### Monthly
- Update application dependencies
- Rotate SSL certificates if needed
- Archive old sports events data
- Performance testing and optimization

### Maintenance Commands

```bash
# Clear expired data
php artisan hdtickets:cleanup-expired

# Optimize database
php artisan hdtickets:optimize-database

# Generate reports
php artisan hdtickets:generate-reports

# Health check
php artisan hdtickets:health-check
```

## Support & Contact

For deployment issues, system monitoring, or sports events data problems:

- **Primary Contact**: System Administrator
- **Email**: admin@hdtickets.local  
- **Emergency**: Check system logs and monitoring dashboards
- **Documentation**: `/var/www/hdtickets/deployment/README.md`

## Appendix

### Environment Variables Reference

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `APP_ENV` | Application environment | Yes | production |
| `APP_URL` | Application URL | Yes | - |
| `DB_HOST` | Database host | Yes | 127.0.0.1 |
| `DB_DATABASE` | Database name | Yes | hdtickets |
| `REDIS_HOST` | Redis host | Yes | 127.0.0.1 |
| `TICKETMASTER_API_KEY` | Ticketmaster API key | Yes | - |
| `STUBHUB_API_KEY` | StubHub API key | Yes | - |
| `MONITORING_ENABLED` | Enable monitoring | No | true |

### Port Usage

| Port | Service | Purpose |
|------|---------|---------|
| 80/443 | Nginx | Load balancer |
| 8080 | Apache | Blue environment |
| 9080 | Apache | Green environment |
| 8090 | Apache | Maintenance page |
| 3306 | MySQL | Database |
| 6379 | Redis | Cache/Sessions/Queue |

---

*HD Tickets Sports Events Entry Tickets Monitoring System*  
*Deployment Guide v1.0 - Created for Ubuntu 24.04 LTS with Apache2, PHP8.4, and MySQL/MariaDB*
