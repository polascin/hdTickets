# HD Tickets Production Monitoring & Logging

## Overview

This document outlines the comprehensive production monitoring and logging system configured for the HD Tickets sports events entry tickets monitoring application.

## System Architecture

- **Environment**: Ubuntu 24.04 LTS, Apache2, PHP8.4, MySQL/MariaDB 10.4
- **Application**: Laravel 12.0 with HD Tickets sports events monitoring system
- **Queue System**: Laravel Horizon with Redis
- **Monitoring**: Custom health checks with performance metrics
- **Logging**: Multi-channel structured logging with rotation

## 1. Laravel Horizon Configuration

### Features Configured
- **Multi-Queue Support**: high, default, scraping, notifications, low priority queues
- **Auto-scaling**: Time-based scaling strategy for optimal performance
- **Memory Management**: 128MB limit for master supervisor, 512MB for workers
- **Job Retention**: Extended retention periods for better analysis
  - Recent jobs: 24 hours
  - Completed jobs: 48 hours
  - Failed jobs: 2 weeks
- **Authentication**: Protected with web, auth, verified middleware
- **Performance Monitoring**: 48-hour metrics retention

### Queue Configuration
```bash
# Production environment queues
- high-priority-supervisor: 8 processes, 512MB memory, 5 retries
- default-supervisor: 6 processes, 256MB memory, 3 retries  
- scraping-supervisor: 4 processes, 512MB memory, 10min timeout
- notifications-supervisor: 3 processes, 128MB memory, 5 retries
- low-priority-supervisor: 2 processes, 256MB memory, 15min timeout
```

### Access
- **URL**: `/horizon` (requires authentication)
- **Environment Variables**:
  - `HORIZON_DOMAIN`: Custom domain (optional)
  - `HORIZON_REDIS_CONNECTION`: Redis connection
  - `HORIZON_MEMORY_LIMIT`: Memory limit in MB

## 2. Log Rotation Configuration

### Location
`/etc/logrotate.d/hdtickets`

### Configured Logs
- **Main Application Logs**: 30 days retention, daily rotation
- **Ticket APIs**: 60 days retention (business critical)
- **Monitoring Logs**: 90 days retention
- **Security Logs**: 180 days retention
- **Audit Logs**: 365 days retention
- **Performance Logs**: 30 days retention
- **Auth Debug**: 4 weeks retention

### Features
- Automatic compression with `gzip`
- Safe rotation with `delaycompress`
- PHP-FPM reload on rotation
- Proper permissions (www-data:www-data)

## 3. Error Tracking & Production Safety

### Ignition Configuration
- **Production Status**: ✅ DISABLED for security
- **Debug Mode**: ✅ DISABLED in production
- **Share Button**: ✅ DISABLED
- **Solution Providers**: Limited to safe providers only

### Error Tracking Features
- Structured error logging with context
- Sensitive data filtering (passwords, tokens, keys)
- Performance threshold monitoring
- Rate limiting to prevent error spam
- Multiple notification channels (Slack, Email, Database)

### Environment Variables
```bash
ERROR_TRACKING_ENABLED=true
IGNITION_ENABLED_PRODUCTION=false
SLACK_ERROR_NOTIFICATIONS=false
EMAIL_ERROR_NOTIFICATIONS=true
SLOW_QUERY_THRESHOLD=1000
```

## 4. Database Query Logging

### Features
- Slow query detection and logging
- Query pattern analysis
- Performance categorization (fast, acceptable, slow, very_slow)
- JSON-formatted logs for analysis
- Table extraction from queries
- Connection pool monitoring

### Configuration
```bash
DB_SLOW_QUERY_THRESHOLD=1.0  # seconds
DB_LOG_QUERIES=false         # Enable only when needed
DB_LOG_SLOW_QUERIES=true     # Always monitor slow queries
```

## 5. Performance Monitoring

### Custom Loggers
- **PerformanceLogger**: Memory, CPU, response time tracking
- **QueryLogger**: Database performance analysis
- **ErrorTrackingLogger**: Structured error analysis

### Metrics Tracked
- Response times (with thresholds)
- Memory usage and limits
- CPU load average
- Database connection counts
- Queue sizes and processing times
- Error rates and patterns

## 6. Health Check Endpoints

### Available Endpoints

#### Basic Health Checks
- `GET /health` - General system health
- `GET /health/database` - Database connectivity
- `GET /health/redis` - Redis connectivity

#### Production Health Checks
- `GET /health/production` - Comprehensive production monitoring
- `GET /health/comprehensive` - Full system analysis

### Health Check Features
- **Application Status**: Debug mode, maintenance mode checks
- **Database Health**: Connection time, slow queries, pool usage
- **Performance Metrics**: Response times, memory, CPU, disk usage
- **Horizon Status**: Queue monitoring, failed jobs tracking
- **Scraping System**: Activity monitoring, error rates
- **System Resources**: Load average, disk space, connections

### Response Format
```json
{
  "status": "healthy|warning|degraded|critical",
  "timestamp": "2025-01-13T10:00:00.000Z",
  "service": "HD Tickets Sports Events Monitoring",
  "version": "2025.07.v4.0",
  "environment": "production",
  "response_time_ms": 150.25,
  "checks": {
    "application": {...},
    "database": {...},
    "performance": {...}
  }
}
```

## 7. Logging Channels

### Configured Channels
- **stack**: Multi-channel logging (single + performance)
- **single**: Basic application logs with placeholders
- **daily**: 30-day retention with placeholders
- **performance**: Custom performance logger with 14-day retention
- **query**: Database query analysis (7 days)
- **error_tracking**: Structured error tracking (30 days)
- **security**: Security events (90 days)
- **audit**: Administrative actions (180 days)
- **ticket_apis**: API operations (30 days)
- **monitoring**: System monitoring (60 days)
- **metrics**: System metrics (30 days)
- **requests**: HTTP request/response logging (7 days)

### Environment Variables
```bash
LOG_CHANNEL=stack
LOG_STACK_CHANNELS=single,performance
LOG_LEVEL=error  # production
LOG_SLACK_WEBHOOK_URL=your_webhook_url
```

## 8. System Monitoring Integration

### Load Balancer Integration
- Health check endpoints suitable for HAProxy, Nginx, AWS ALB
- Appropriate HTTP status codes (200, 503)
- Fast response times for frequent checks

### External Monitoring
- Structured JSON responses for Prometheus/Grafana
- Slack notifications for critical alerts
- Email notifications for administrators
- Database storage for historical analysis

## 9. Security Considerations

### Production Safety
- ✅ Debug mode disabled
- ✅ Ignition disabled
- ✅ Sensitive data filtering
- ✅ Authentication required for admin endpoints
- ✅ Rate limiting on health checks
- ✅ Proper log permissions

### Access Control
- Horizon requires authentication and verification
- Health endpoints are public but rate-limited
- Admin-specific metrics require role-based access
- Log files protected with proper permissions

## 10. Maintenance & Operations

### Daily Operations
1. Monitor Horizon dashboard for queue health
2. Check error rates in `/health/production`
3. Review slow query logs for optimization opportunities
4. Monitor disk space usage

### Weekly Operations
1. Review log rotation effectiveness
2. Analyze performance trends
3. Check for failed job accumulation
4. Review security logs for anomalies

### Monthly Operations
1. Optimize database based on query logs
2. Review and adjust monitoring thresholds
3. Clean up old compressed logs if needed
4. Update monitoring documentation

## 11. Troubleshooting

### Common Issues

#### Horizon Not Running
```bash
# Check status
php artisan horizon:status

# Start Horizon
php artisan horizon

# Restart if needed
php artisan horizon:terminate
php artisan horizon
```

#### High Error Rates
1. Check `/health/production` for specific issues
2. Review error_tracking.log for patterns
3. Monitor database connection pool
4. Check disk space and memory usage

#### Slow Performance
1. Review performance.log for trends
2. Check slow queries in queries.log
3. Monitor queue sizes in Horizon
4. Analyze system resource usage

### Log Analysis
```bash
# Recent errors
tail -f storage/logs/error_tracking.log

# Performance issues
grep "very_slow" storage/logs/queries.log

# System metrics
tail -f storage/logs/metrics.log

# Security events
grep "FAILED_LOGIN" storage/logs/security.log
```

## 12. Environment Variables Summary

```bash
# Horizon Configuration
HORIZON_DOMAIN=
HORIZON_REDIS_CONNECTION=default
HORIZON_MEMORY_LIMIT=128
HORIZON_FAST_TERMINATION=false

# Logging Configuration
LOG_CHANNEL=stack
LOG_STACK_CHANNELS=single,performance
LOG_LEVEL=error
LOG_SLACK_WEBHOOK_URL=

# Database Monitoring
DB_SLOW_QUERY_THRESHOLD=1.0
DB_LOG_QUERIES=false
DB_LOG_SLOW_QUERIES=true

# Error Tracking
ERROR_TRACKING_ENABLED=true
IGNITION_ENABLED_PRODUCTION=false
SLACK_ERROR_NOTIFICATIONS=false
EMAIL_ERROR_NOTIFICATIONS=true

# Performance Monitoring
SLOW_QUERY_THRESHOLD=1000
SLOW_REQUEST_THRESHOLD=5000
MEMORY_THRESHOLD=128
CPU_THRESHOLD=80
```

## 13. Monitoring Checklist

### Pre-Production
- [ ] Horizon configuration tested
- [ ] Log rotation configured and tested
- [ ] Health check endpoints responding correctly
- [ ] Error tracking properly filtering sensitive data
- [ ] Performance monitoring thresholds set appropriately
- [ ] Database query logging enabled
- [ ] Security logging configured
- [ ] Notification channels tested

### Production
- [ ] Horizon queues processing normally
- [ ] Log files rotating correctly
- [ ] Health checks returning expected status
- [ ] Error rates within acceptable limits
- [ ] Performance metrics being collected
- [ ] Database performance optimized
- [ ] Security monitoring active
- [ ] Alerts functioning properly

This comprehensive monitoring setup ensures the HD Tickets application maintains high availability and performance while providing detailed insights for optimization and troubleshooting.
