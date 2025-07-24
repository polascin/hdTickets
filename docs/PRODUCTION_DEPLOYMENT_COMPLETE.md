# Complete Production Deployment Guide
## Sports Event Ticket Monitoring System

### Overview

This guide provides comprehensive instructions for deploying the Sports Event Ticket Monitoring, Scraping and Purchase System to production with all monitoring, error tracking, and optimization features enabled.

### Prerequisites

Before deploying to production, ensure you have:

- [ ] Server infrastructure with PHP 8.4+, MySQL 8.0+, Redis, and Nginx
- [ ] SSL certificates configured for HTTPS
- [ ] Domain names configured (hdtickets.polascin.net)
- [ ] Database master-slave replication setup
- [ ] New Relic account and license key
- [ ] Datadog account and API keys (optional)
- [ ] Sentry account and DSN
- [ ] AWS S3 bucket for file storage
- [ ] Pusher account for real-time features
- [ ] SMTP service for email notifications

---

## 1. Environment Configuration

### Production Environment Variables

Update your `.env.production` file with the following configurations:

```bash
# Core Application Settings
APP_NAME="HD Tickets"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hdtickets.polascin.net

# Database Master-Slave Configuration
DB_CONNECTION=mysql
DB_HOST=prod-db-master.hdtickets.internal
DB_READ_HOST=prod-db-slave-1.hdtickets.internal,prod-db-slave-2.hdtickets.internal

# Redis Cluster Configuration
CACHE_STORE=redis
REDIS_HOST=prod-redis-cluster.hdtickets.internal

# Production Monitoring
NEW_RELIC_ENABLED=true
NEW_RELIC_LICENSE_KEY=your_license_key
DATADOG_ENABLED=true
SENTRY_ENABLED=true
```

### Staging Environment

For staging deployment, use `.env.staging` with reduced monitoring and sampling rates.

---

## 2. Database Optimization

### Master-Slave Replication Setup

1. **Configure Master Database:**
   ```sql
   -- On master server
   CREATE USER 'replication_user'@'%' IDENTIFIED BY 'secure_password';
   GRANT REPLICATION SLAVE ON *.* TO 'replication_user'@'%';
   FLUSH PRIVILEGES;
   ```

2. **Configure Slave Databases:**
   ```sql
   -- On each slave server
   CHANGE MASTER TO
   MASTER_HOST='prod-db-master.hdtickets.internal',
   MASTER_USER='replication_user',
   MASTER_PASSWORD='secure_password',
   MASTER_LOG_FILE='mysql-bin.000001',
   MASTER_LOG_POS=4;
   START SLAVE;
   ```

3. **Laravel Configuration:**
   - Use `config/database-production.php` for production database setup
   - Automatic read/write splitting configured
   - Connection pooling enabled for better performance

### Database Indexes

Ensure these performance-critical indexes are created:

```sql
-- Ticket searching performance
CREATE INDEX idx_scraped_tickets_event_date ON scraped_tickets(event_date);
CREATE INDEX idx_scraped_tickets_platform_status ON scraped_tickets(platform, status);
CREATE INDEX idx_scraped_tickets_price_range ON scraped_tickets(min_price, max_price);

-- User activity tracking
CREATE INDEX idx_users_last_activity ON users(last_activity_at);
CREATE INDEX idx_activity_log_created_at ON activity_log(created_at);

-- Queue performance
CREATE INDEX idx_jobs_queue_status ON jobs(queue, status);
```

---

## 3. Monitoring Setup

### New Relic APM Integration

1. **Install New Relic PHP Agent:**
   ```bash
   curl -L https://download.newrelic.com/php_agent/scripts/newrelic-install | sudo bash
   sudo systemctl restart php8.4-fpm
   ```

2. **Configure Application:**
   - Application name: `HDTickets-Production`
   - Custom instrumentation for ticket scraping services
   - Database query monitoring enabled
   - Browser monitoring for real user metrics

3. **Key Metrics to Monitor:**
   - Application response time (target: <500ms)
   - Database query performance
   - Ticket scraping success rates
   - User session duration
   - API endpoint performance

### Datadog Integration (Optional)

1. **Install Datadog Agent:**
   ```bash
   DD_API_KEY=your_api_key DD_SITE="datadoghq.eu" bash -c "$(curl -L https://s3.amazonaws.com/dd-agent/scripts/install_script.sh)"
   ```

2. **Configure Integrations:**
   - MySQL monitoring
   - Redis monitoring
   - Nginx monitoring
   - PHP-FPM monitoring

3. **Custom Dashboards:**
   - Ticket scraping performance
   - User activity metrics
   - System resource utilization
   - Alert response times

---

## 4. Error Tracking - Sentry

### Setup Instructions

1. **Install Sentry SDK:**
   ```bash
   composer require sentry/sentry-laravel
   ```

2. **Configuration:**
   - DSN configured in `.env.production`
   - Custom error filtering for ticket system
   - Performance monitoring enabled
   - Release tracking integrated with CI/CD

3. **Custom Context:**
   - Active scrapers status
   - User subscription information
   - Platform health metrics
   - Queue depths and processing times

### Error Filtering

Sentry is configured to ignore common, non-critical errors:
- 404 Not Found exceptions
- Authentication failures
- Validation errors
- Rate limiting errors

---

## 5. CI/CD Pipeline

### GitHub Actions Workflow

The automated deployment pipeline includes:

1. **Security & Quality Checks:**
   - Composer security audit
   - PHPStan static analysis
   - Code style validation

2. **Automated Testing:**
   - Unit tests with coverage
   - Feature tests
   - Integration tests
   - Load testing

3. **Deployment Process:**
   - Staging deployment first
   - Health checks before production
   - Zero-downtime production deployment
   - Automatic rollback on failure

### Required GitHub Secrets

```bash
# Server Access
PRODUCTION_HOST=your.production.server
PRODUCTION_USERNAME=deploy_user
PRODUCTION_SSH_KEY=your_private_key

STAGING_HOST=staging.server
STAGING_USERNAME=deploy_user
STAGING_SSH_KEY=staging_private_key

# Monitoring Services
NEW_RELIC_API_KEY=your_api_key
NEW_RELIC_APP_ID=your_app_id
SENTRY_AUTH_TOKEN=your_token
SENTRY_ORG=your_organization

# Notifications
SLACK_WEBHOOK_URL=your_webhook_url
```

---

## 6. Performance Optimization

### Caching Strategy

1. **Application Cache:**
   - Redis for session storage
   - Database query caching
   - View compilation caching
   - Route caching

2. **Content Delivery:**
   - AWS S3 for static assets
   - CloudFront CDN integration
   - Image optimization

3. **Database Optimization:**
   - Query optimization
   - Index tuning
   - Connection pooling
   - Read replica utilization

### Queue Management

```bash
# Start queue workers
php artisan queue:work redis --sleep=3 --tries=3 --max-time=3600

# Monitor queue health
php artisan queue:monitor redis:default,redis:scraping --max=100
```

---

## 7. Security Configuration

### SSL/TLS Setup

1. **Certificate Management:**
   - Let's Encrypt certificates
   - Automatic renewal configured
   - HSTS headers enabled

2. **Security Headers:**
   ```nginx
   add_header X-Frame-Options "SAMEORIGIN" always;
   add_header X-XSS-Protection "1; mode=block" always;
   add_header X-Content-Type-Options "nosniff" always;
   add_header Referrer-Policy "no-referrer-when-downgrade" always;
   add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
   ```

### Rate Limiting

Production rate limits configured:
- General API: 300 requests/minute
- Scraping API: 50 requests/minute
- Authentication: 3 attempts/minute

---

## 8. Backup Strategy

### Automated Backups

1. **Database Backups:**
   - Daily full backups to S3
   - Point-in-time recovery enabled
   - 30-day retention policy

2. **Application Backups:**
   - Code deployments archived
   - Configuration backups
   - Log file rotation and archiving

### Disaster Recovery

1. **Recovery Time Objective (RTO):** 4 hours
2. **Recovery Point Objective (RPO):** 1 hour
3. **Backup verification:** Weekly restore tests

---

## 9. Health Monitoring

### Health Check Endpoints

```bash
# Application health
GET /health

# Database connectivity
GET /health/database

# Redis connectivity
GET /health/redis

# External services
GET /health/external
```

### Alerting Thresholds

- **Response Time:** >2 seconds (95th percentile)
- **Error Rate:** >5%
- **Memory Usage:** >80%
- **CPU Usage:** >85%
- **Disk Usage:** >90%

---

## 10. Deployment Checklist

### Pre-Deployment

- [ ] All tests passing
- [ ] Security scan completed
- [ ] Database migration tested
- [ ] Backup verification completed
- [ ] Monitoring dashboards configured

### Deployment

- [ ] Staging deployment successful
- [ ] Health checks passing
- [ ] Performance metrics within thresholds
- [ ] Error rates acceptable

### Post-Deployment

- [ ] Production health check passed
- [ ] User acceptance testing completed
- [ ] Monitoring alerts configured
- [ ] Documentation updated
- [ ] Team notifications sent

---

## 11. Troubleshooting

### Common Issues

1. **High Memory Usage:**
   - Check queue worker processes
   - Review PHP memory limits
   - Analyze Redis memory usage

2. **Slow Database Queries:**
   - Check slow query log
   - Verify index usage
   - Monitor replication lag

3. **Scraping Failures:**
   - Check proxy rotation
   - Verify CAPTCHA solving
   - Review rate limiting

### Emergency Contacts

- **DevOps Team:** devops@hdtickets.polascin.net
- **On-Call Engineer:** +421-XXX-XXX-XXX
- **Emergency Slack:** #incidents

---

## 12. Maintenance

### Regular Maintenance Tasks

**Daily:**
- Monitor error rates and performance metrics
- Check backup completion
- Review security logs

**Weekly:**
- Update dependencies
- Review and optimize slow queries
- Clean up old log files

**Monthly:**
- Security updates
- Performance optimization review
- Disaster recovery testing

### Scaling Considerations

As traffic grows, consider:
- Load balancer configuration
- Additional database replicas
- Redis cluster expansion
- CDN optimization
- Microservices architecture

---

## Support

For deployment support or issues:
- **Documentation:** `/docs` directory
- **API Reference:** `api_documentation.md`
- **GitHub Issues:** Repository issue tracker
- **Team Chat:** #hdtickets-support

---

*Last updated: January 2025*
*Version: 2025.7.3*
