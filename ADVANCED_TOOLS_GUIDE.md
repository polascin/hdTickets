# HD Tickets - Advanced Tools & Monitoring Guide

## Overview
This document provides comprehensive documentation for all advanced monitoring, automation, and development tools created for the HD Tickets application. These tools ensure optimal performance, reliability, and developer productivity.

## 🛠️ Tool Suite Overview

### 1. **Health Check System** (`scripts/health-check.sh`)
Comprehensive application health monitoring and proactive maintenance alerts.

**Key Features:**
- ✅ Real-time system resource monitoring
- ✅ Database connectivity and performance checks
- ✅ Redis cache system verification
- ✅ Application response time testing
- ✅ Security vulnerability scanning
- ✅ Dependency update notifications
- ✅ JSON output for automation integration

**Usage Examples:**
```bash
# Basic health check
./scripts/health-check.sh

# Verbose output with detailed information
./scripts/health-check.sh --verbose

# JSON output for monitoring systems
./scripts/health-check.sh --json

# Enable alert notifications
./scripts/health-check.sh --alerts
```

**What It Monitors:**
- PHP version and extensions (intl, redis, bcmath, etc.)
- Database connectivity and size
- Redis performance and memory usage
- Disk space and memory utilization
- Application response times
- Laravel cache optimization status
- Log file sizes and rotation needs
- System security updates

### 2. **Performance Monitoring Dashboard** (`scripts/performance-monitor.sh`)
Real-time performance metrics collection with interactive HTML dashboards.

**Key Features:**
- 📊 Real-time CPU, memory, and disk monitoring
- 📈 Application response time tracking
- 📋 Database and Redis performance metrics
- 🎯 Interactive charts with Chart.js
- 📱 Mobile-responsive dashboard design
- 💾 JSON data export for analysis
- ⚠️ Configurable alerting thresholds

**Usage Examples:**
```bash
# Monitor for 60 seconds with 5-second intervals
./scripts/performance-monitor.sh --duration=60 --interval=5

# Continuous monitoring (Ctrl+C to stop)
./scripts/performance-monitor.sh --continuous

# Custom output file
./scripts/performance-monitor.sh --output=my-dashboard.html

# Disable alerts
./scripts/performance-monitor.sh --no-alerts
```

**Dashboard Features:**
- Live CPU usage graphs
- Memory consumption trends
- Application response time charts
- Disk usage monitoring
- System information summary
- Performance threshold alerts

### 3. **Backup Management System** (`scripts/backup-manager.sh`)
Enterprise-grade backup creation, verification, and recovery testing.

**Key Features:**
- 🔐 Encrypted database backups with AES-256
- 📦 Compressed file system backups
- ✅ Backup integrity verification
- 🧪 Automated restore testing
- 📋 Detailed backup manifests with metadata
- 🔄 Automated cleanup and retention policies
- 📊 Backup statistics and reporting

**Usage Examples:**
```bash
# Create full backup
./scripts/backup-manager.sh backup

# Create backup with custom name
./scripts/backup-manager.sh backup pre_update_v2.1

# Verify backup integrity
./scripts/backup-manager.sh verify hdtickets_20250109_143022

# Test restore procedure
./scripts/backup-manager.sh test-restore latest

# List all backups
./scripts/backup-manager.sh list

# Clean old backups
./scripts/backup-manager.sh cleanup
```

**Backup Features:**
- Database: mysqldump with all triggers, routines, events
- Files: tar.gz of application code and configuration
- Encryption: OpenSSL AES-256-CBC with unique keys
- Verification: SHA-256 checksums and integrity tests
- Metadata: JSON manifests with system information
- Retention: Configurable policies (30 days default)

### 4. **Maintenance Automation** (`scripts/maintenance.sh`)
Comprehensive routine maintenance automation for optimal performance.

**Key Features:**
- 🧹 Automated cache clearing and optimization
- 📜 Log rotation and compression
- 🗂️ Temporary file cleanup
- 🗃️ Database optimization and maintenance
- 🔒 Security checks and fixes
- 📊 Dependency update notifications
- 📋 Automated maintenance reporting

**Usage Examples:**
```bash
# Full maintenance routine
./scripts/maintenance.sh full

# Individual tasks
./scripts/maintenance.sh clear-cache
./scripts/maintenance.sh rotate-logs
./scripts/maintenance.sh clean-temp
./scripts/maintenance.sh database-maintenance
./scripts/maintenance.sh security-check

# Custom retention periods
./scripts/maintenance.sh rotate-logs --log-retention=14
```

**Maintenance Tasks:**
- **Cache Management:** Clear/optimize Laravel caches
- **Log Management:** Rotate, compress, and clean log files
- **File Cleanup:** Remove temporary and unnecessary files
- **Database:** Optimize tables and cleanup expired data
- **Security:** File permission fixes and vulnerability checks
- **Dependencies:** Update notifications and security audits

### 5. **Development Tools Suite** (`scripts/dev-tools.sh`)
Complete development workflow optimization and automation tools.

**Key Features:**
- 🚀 Automated development environment setup
- 🧪 Comprehensive testing with coverage reports
- 🔍 Code quality analysis and enforcement
- 🏗️ Frontend asset building and optimization
- 📦 Deployment preparation automation
- 🖥️ Development server management
- ✨ Linting and code formatting

**Usage Examples:**
```bash
# Setup development environment
./scripts/dev-tools.sh setup

# Run tests with coverage
./scripts/dev-tools.sh test --coverage

# Code quality checks
./scripts/dev-tools.sh quality

# Build assets
./scripts/dev-tools.sh build production

# Prepare for deployment
./scripts/dev-tools.sh deploy-prep

# Start development server
./scripts/dev-tools.sh serve 3000 0.0.0.0

# Linting with auto-fix
./scripts/dev-tools.sh lint --fix

# Database tools
./scripts/dev-tools.sh db fresh
```

**Development Features:**
- **Environment Setup:** Dependencies, keys, storage links
- **Testing:** PHPUnit, JavaScript tests, coverage reports
- **Quality:** Syntax, style, static analysis, security audits
- **Building:** Development, production, watch modes
- **Database:** Migrations, seeding, backup creation
- **Server:** Development server with port/host configuration

## 📊 Integration & Automation

### Cron Job Examples
Set up automated maintenance and monitoring:

```bash
# Daily health check at 2 AM
0 2 * * * /var/www/hdtickets/scripts/health-check.sh --json > /var/www/hdtickets/storage/logs/health-$(date +\%Y\%m\%d).json

# Weekly full maintenance on Sunday at 3 AM
0 3 * * 0 /var/www/hdtickets/scripts/maintenance.sh full

# Daily backup at 1 AM
0 1 * * * /var/www/hdtickets/scripts/backup-manager.sh backup daily_$(date +\%Y\%m\%d)

# Hourly performance monitoring during business hours
0 9-17 * * 1-5 /var/www/hdtickets/scripts/performance-monitor.sh --duration=60 --output=/var/www/hdtickets/storage/logs/performance-$(date +\%H).html
```

### CI/CD Integration
Use development tools in your CI/CD pipeline:

```yaml
# Example GitHub Actions workflow
- name: Quality Checks
  run: ./scripts/dev-tools.sh quality

- name: Run Tests
  run: ./scripts/dev-tools.sh test --coverage

- name: Build Assets
  run: ./scripts/dev-tools.sh build production

- name: Deployment Preparation
  run: ./scripts/dev-tools.sh deploy-prep
```

### Monitoring Integration
Connect tools with monitoring systems:

```bash
# Send health check results to monitoring system
./scripts/health-check.sh --json | curl -X POST -H "Content-Type: application/json" -d @- https://monitoring-system/api/health

# Performance metrics to time-series database
./scripts/performance-monitor.sh --duration=300 --json | jq '.metrics[]' | curl -X POST -d @- https://metrics-db/api/ingest
```

## 📁 File Structure

```
scripts/
├── health-check.sh          # System health monitoring
├── performance-monitor.sh   # Real-time performance dashboard
├── backup-manager.sh        # Backup creation and verification
├── maintenance.sh           # Automated maintenance tasks
└── dev-tools.sh            # Development workflow tools

storage/
├── logs/
│   ├── health-check.log     # Health check history
│   ├── maintenance.log      # Maintenance task logs
│   ├── backup.log          # Backup operation logs
│   ├── dev-tools.log       # Development tool logs
│   └── metrics/            # Performance monitoring data
└── backups/                # Encrypted backup storage
```

## 🔧 Configuration

### Environment Variables
Add these to your `.env` file for enhanced functionality:

```env
# Health Check Configuration
HEALTH_CHECK_ALERTS=true
HEALTH_CHECK_WEBHOOK_URL=https://your-webhook-url

# Performance Monitoring
PERF_MONITOR_RETENTION_DAYS=30
PERF_MONITOR_ALERT_THRESHOLD_CPU=80
PERF_MONITOR_ALERT_THRESHOLD_MEMORY=80

# Backup Configuration
BACKUP_ENCRYPTION_ENABLED=true
BACKUP_RETENTION_DAYS=30
BACKUP_MAX_COUNT=100

# Maintenance Settings
MAINTENANCE_LOG_RETENTION=30
MAINTENANCE_AUTO_OPTIMIZE=true
```

### System Requirements

**Minimum Requirements:**
- Ubuntu 20.04+ / CentOS 8+ / Debian 11+
- PHP 8.3+ with required extensions (intl, redis, bcmath, etc.)
- MySQL/MariaDB 8.0+
- Redis 6.0+
- Node.js 18+ (for frontend tools)
- 2GB RAM minimum, 4GB recommended
- 20GB disk space for backups and logs

**Required System Tools:**
- `curl` - HTTP requests and API testing
- `jq` - JSON processing
- `bc` - Mathematical calculations
- `gzip` - File compression
- `openssl` - Encryption operations
- `mysqldump` / `mysql` - Database operations
- `redis-cli` - Redis operations

### Installation & Setup

1. **Make scripts executable:**
```bash
chmod +x scripts/*.sh
```

2. **Install missing PHP extensions:**
```bash
sudo apt-get install php8.3-intl php8.3-redis php8.3-bcmath
```

3. **Install system dependencies:**
```bash
sudo apt-get install jq bc curl openssl mysql-client redis-tools
```

4. **Test all tools:**
```bash
# Test health check
./scripts/health-check.sh --verbose

# Test development environment
./scripts/dev-tools.sh setup

# Create test backup
./scripts/backup-manager.sh backup test_backup

# Run maintenance check
./scripts/maintenance.sh health-check
```

## 🚨 Troubleshooting

### Common Issues

**"Permission denied" errors:**
```bash
# Fix script permissions
chmod +x scripts/*.sh

# Fix storage permissions
chmod -R 755 storage/
```

**"Command not found" errors:**
```bash
# Install missing system tools
sudo apt-get update
sudo apt-get install jq bc curl openssl mysql-client redis-tools

# Install missing PHP extensions
sudo apt-get install php8.3-intl php8.3-redis php8.3-bcmath
```

**Database connection failures:**
```bash
# Check database service
sudo systemctl status mysql

# Test connection manually
mysql -h127.0.0.1 -uhdtickets -p hdtickets

# Verify .env configuration
grep -E "DB_|REDIS_" .env
```

**Backup encryption issues:**
```bash
# Regenerate encryption key
rm .backup_key
./scripts/backup-manager.sh backup test_backup

# Test OpenSSL
openssl version
```

### Log Files
Monitor these log files for issues:

- `storage/logs/health-check.log` - Health monitoring history
- `storage/logs/maintenance.log` - Maintenance task results
- `storage/logs/backup.log` - Backup operation details
- `storage/logs/dev-tools.log` - Development tool usage
- `storage/logs/laravel.log` - Application errors

## 📈 Performance Metrics

### Benchmarks
Expected performance on recommended hardware:

- **Health Check:** Complete scan in <30 seconds
- **Performance Monitor:** <5% CPU overhead during monitoring
- **Backup Creation:** ~1MB/second for database, ~10MB/second for files
- **Maintenance Tasks:** Complete routine in <5 minutes
- **Development Tools:** Test suite completion in <2 minutes

### Optimization Tips

1. **Schedule maintenance during low-traffic periods**
2. **Use SSD storage for better backup performance**
3. **Configure Redis memory limits appropriately**
4. **Monitor disk space regularly (80% threshold)**
5. **Set up log rotation to prevent disk space issues**
6. **Use production caching for better performance**

## 🔄 Updates & Maintenance

### Tool Updates
To update the tool suite:

```bash
# Backup current scripts
cp -r scripts/ scripts.backup/

# Update scripts (manual process)
# Test new scripts
./scripts/health-check.sh --verbose
./scripts/dev-tools.sh quality

# Update documentation
# Notify team of changes
```

### Version History
- **v1.0.0** - Initial release with all core tools
- Feature additions and improvements will be documented here

---

## 📞 Support & Contributing

For issues, improvements, or questions:

1. Check the troubleshooting section above
2. Review log files for error details
3. Test individual components in isolation
4. Verify system requirements and dependencies
5. Create detailed issue reports with logs

**Contribution Guidelines:**
- Follow existing code style and patterns
- Add comprehensive logging and error handling
- Include usage examples and documentation
- Test thoroughly on different environments
- Maintain backward compatibility when possible

---

**Last Updated:** 2025-01-09  
**Version:** 1.0.0  
**Compatibility:** Laravel 11.45.2, PHP 8.3.25, Ubuntu 24.04 LTS
