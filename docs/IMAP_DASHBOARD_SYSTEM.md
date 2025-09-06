# IMAP Email Monitoring Dashboard System

## Overview

The IMAP Email Monitoring Dashboard System is a comprehensive solution for monitoring, managing, and analyzing email-based sports event ticket discovery in the HD Tickets platform. This system provides real-time monitoring, analytics, notifications, and management tools for email processing operations.

## 🏗️ Architecture Components

### Backend Services

#### 1. Core Services
- **`ImapConnectionService`** - Manages IMAP connections with retry logic and health monitoring
- **`EmailMonitoringService`** - Orchestrates email monitoring across multiple platforms
- **`EmailParsingService`** - Extracts sports event data from emails with platform-specific parsers
- **`ImapNotificationService`** - Handles alerts and notifications for monitoring events

#### 2. Controllers
- **`ImapDashboardController`** - Web dashboard interface with full monitoring capabilities
- **`Api\ImapMonitoringController`** - RESTful API endpoints for management and statistics

#### 3. Jobs & Events
- **`ProcessSportsEventEmailJob`** - Asynchronous email processing queue job
- **`ImapMonitoringEvent`** - WebSocket events for real-time dashboard updates

### Frontend Interface

#### 1. Dashboard Views
- **Main Dashboard** (`resources/views/admin/imap/dashboard.blade.php`) - Central monitoring interface
- **Connection Health** - Real-time connection status monitoring
- **Platform Analytics** - Performance metrics and statistics
- **System Logs** - Monitoring activity and error logs

#### 2. Real-time Features
- **WebSocket Integration** - Live updates for connection status and processing metrics
- **Auto-refresh Dashboard** - 30-second interval updates for critical data
- **Interactive Controls** - Manual monitoring triggers and cache management

## 🚀 Key Features

### 1. Email Platform Support
- **Ticketmaster** - Event extraction, pricing, availability tracking
- **StubHub** - Price drop alerts, section/row details, event information
- **Viagogo** - Multi-currency support, availability notifications
- **SeatGeek** - Deal alerts, percentage discounts
- **TickPick** - No-fee pricing, final cost calculations
- **Generic Parsing** - Flexible parsing for unknown platforms

### 2. Sports Event Recognition
- **10 Sport Categories** - Football, Basketball, Baseball, Hockey, Soccer, Tennis, Golf, Racing, Boxing, MMA
- **Smart Content Detection** - Team vs team matching, venue recognition, date parsing
- **Price Validation** - Currency extraction with range validation ($1-$50,000)

### 3. Real-time Monitoring
- **Connection Health** - Live status of all configured email connections
- **Processing Statistics** - Emails processed, events discovered, tickets found
- **System Health** - IMAP extension, Redis connection, queue workers, disk space
- **Performance Metrics** - Response times, success rates, error tracking

### 4. Notification System
- **Multi-channel Alerts** - Email, Slack, Discord, in-app notifications
- **Severity Levels** - High, Medium, Low, Info with appropriate routing
- **Smart Thresholds** - Configurable alert conditions for different scenarios
- **High-value Event Detection** - Special alerts for championship/playoff games

## 📊 Dashboard Interface

### Main Dashboard Components

#### 1. Statistics Cards
```
┌─────────────────┬─────────────────┬─────────────────┬─────────────────┐
│ Total           │ Active          │ Platform        │ Mailboxes       │
│ Connections: 4  │ Connections: 3  │ Patterns: 6     │ Monitored: 5    │
└─────────────────┴─────────────────┴─────────────────┴─────────────────┘
```

#### 2. Connection Status Panel
- Real-time health status for each configured connection
- Response times and message counts
- Visual indicators (✅/❌) for quick status assessment
- Direct links to detailed connection management

#### 3. Quick Actions Panel
- **Run Email Monitoring** - Manual trigger for immediate processing
- **Test All Connections** - Health check for all configured connections  
- **Clear Processed Cache** - Reset processing cache for fresh monitoring
- **Quick Links** - Analytics, logs, platform configuration

#### 4. System Health Panel
- IMAP extension status
- Redis connection health
- Queue worker status
- System resource utilization

#### 5. Recent Activity Panel
- Emails processed today
- Sports events discovered
- Tickets found
- Active platforms

### Real-time Updates

The dashboard automatically refreshes every 30 seconds with:
- Connection health status updates
- Processing statistics
- System health metrics
- Recent activity summaries

Updates pause when the browser tab is not visible to conserve resources.

## 🔧 Configuration

### Environment Variables

```bash
# IMAP Default Settings
IMAP_DEFAULT_CONNECTION=gmail
IMAP_BATCH_SIZE=50
IMAP_MAX_AGE_DAYS=7
IMAP_MARK_AS_READ=true

# Gmail Configuration
IMAP_GMAIL_HOST=imap.gmail.com
IMAP_GMAIL_PORT=993
IMAP_GMAIL_USERNAME=your_email@gmail.com
IMAP_GMAIL_PASSWORD=your_app_password

# Outlook Configuration  
IMAP_OUTLOOK_HOST=outlook.office365.com
IMAP_OUTLOOK_PORT=993
IMAP_OUTLOOK_USERNAME=your_email@outlook.com
IMAP_OUTLOOK_PASSWORD=your_password

# Monitoring Settings
IMAP_QUEUE_ENABLED=true
IMAP_CACHE_ENABLED=true
IMAP_LOGGING_ENABLED=true
IMAP_RATE_LIMITING=true
```

### Platform Patterns Configuration

The system includes pre-configured patterns for major ticket platforms:

```php
'ticketmaster' => [
    'from_patterns' => ['*@ticketmaster.com', '*@tm.e.ticketmaster.com'],
    'subject_keywords' => ['tickets available', 'on sale now', 'presale'],
    'body_keywords' => ['sports event', 'tickets', 'stadium', 'arena'],
],

'stubhub' => [
    'from_patterns' => ['*@stubhub.com', '*@email.stubhub.com'],
    'subject_keywords' => ['price drop', 'new listings', 'favorites'],
    'body_keywords' => ['sports', 'event', 'tickets', 'listing'],
],
```

## 🛠️ API Endpoints

### Dashboard & Statistics
```http
GET /api/v1/imap/dashboard
GET /api/v1/imap/statistics
GET /api/v1/imap/connection-health
```

### Monitoring Operations
```http
POST /api/v1/imap/start-monitoring
POST /api/v1/imap/test-connection
POST /api/v1/imap/clear-cache
```

### Configuration Management
```http
GET /api/v1/imap/platform-config
```

### Example API Response
```json
{
  "success": true,
  "data": {
    "monitoring_stats": {
      "total_connections": 4,
      "active_connections": 3,
      "platform_patterns": 6
    },
    "system_health": {
      "imap_extension": true,
      "redis_connection": true,
      "queue_workers": "active"
    },
    "recent_activity": {
      "total_emails_processed_today": 127,
      "sports_events_discovered_today": 18,
      "tickets_found_today": 63
    }
  },
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## 🔄 Command Line Interface

### Primary Commands

#### Test IMAP Setup
```bash
php artisan hdtickets:test-imap
```
Comprehensive system test including IMAP extension, configuration, services, and parsing.

#### Monitor Emails
```bash
# Monitor all connections
php artisan hdtickets:monitor-emails

# Monitor specific connection
php artisan hdtickets:monitor-emails --connection=gmail

# Dry run (no processing)
php artisan hdtickets:monitor-emails --dry-run

# Test connections only
php artisan hdtickets:monitor-emails --test-connection

# View statistics
php artisan hdtickets:monitor-emails --stats

# Clear cache
php artisan hdtickets:monitor-emails --clear-cache

# Verbose output
php artisan hdtickets:monitor-emails --verbose
```

### Cron Job Setup

For automated monitoring, add to crontab:
```bash
# Monitor emails every 15 minutes
*/15 * * * * cd /var/www/hdtickets && php artisan hdtickets:monitor-emails

# Daily cache cleanup at midnight
0 0 * * * cd /var/www/hdtickets && php artisan hdtickets:monitor-emails --clear-cache
```

## 📈 Analytics & Metrics

### Performance Metrics
- **Connection Response Times** - Average time to establish IMAP connections
- **Email Processing Speed** - Emails processed per minute
- **Success Rates** - Percentage of successful operations by platform
- **Discovery Efficiency** - Sports events found per email processed

### Platform Performance
- **Ticketmaster** - Events discovered, processing success rate
- **StubHub** - Price alerts, listing detection accuracy
- **SeatGeek** - Deal identification, response times
- **Generic Parsing** - Fallback parser effectiveness

### System Health Metrics
- **Resource Utilization** - Memory usage, disk space, CPU load
- **Queue Performance** - Job processing times, failure rates
- **Error Analysis** - Common error types, frequency patterns
- **Uptime Tracking** - System availability and connection stability

## 🔔 Notification System

### Alert Types

#### 1. Connection Failures
- **Threshold** - 3 consecutive failures
- **Recipients** - Admins and agents (high severity)
- **Channels** - Email, Slack, Discord, dashboard alert

#### 2. Processing Errors
- **Threshold** - 5 errors in 10 minutes
- **Recipients** - Admins (medium severity)
- **Channels** - Email, Slack

#### 3. Low Discovery Rate
- **Threshold** - No events discovered for 2+ hours
- **Recipients** - Admins (medium severity)
- **Channels** - Email notification

#### 4. High-Value Events
- **Trigger** - Championship, playoff, finals keywords
- **Recipients** - All agents and admins
- **Channels** - All channels including dashboard broadcast

### Notification Channels

#### Email Notifications
- HTML formatted alerts with detailed context
- Severity-based color coding
- Direct links to dashboard for immediate action

#### Slack Integration
```json
{
  "text": "🎟️ HD Tickets IMAP Alert",
  "attachments": [{
    "color": "danger",
    "title": "IMAP Monitoring Alert", 
    "text": "Connection failure detected",
    "fields": [
      {"title": "Severity", "value": "HIGH", "short": true},
      {"title": "Connection", "value": "gmail", "short": true}
    ]
  }]
}
```

#### Discord Integration
- Rich embed formatting with color-coded severity
- Structured data presentation
- Direct notification to monitoring channels

## 🔐 Security & Access Control

### Role-Based Access
- **Admin** - Full access to all dashboard features and settings
- **Agent** - Monitoring dashboard, statistics, manual triggers
- **Customer** - No access to IMAP monitoring features
- **Scraper** - No web interface access (API-only role)

### API Authentication
- Laravel Sanctum token-based authentication
- Role verification middleware on all endpoints
- Rate limiting to prevent abuse

### Data Security
- SSL/TLS encryption for all IMAP connections
- App-specific passwords recommended for Gmail/Yahoo
- Sensitive credentials stored in encrypted environment files
- Connection timeouts and retry limits to prevent resource exhaustion

## 🚨 Troubleshooting

### Common Issues

#### 1. IMAP Extension Missing
```bash
# Install IMAP extension
sudo apt install php8.3-imap
sudo systemctl restart apache2

# Verify installation
php -m | grep imap
```

#### 2. Connection Failures
- Verify email credentials in `.env` file
- Check firewall settings for outbound IMAP ports (993/143)
- Ensure app-specific passwords for Gmail/Yahoo
- Test connection manually: `php artisan hdtickets:monitor-emails --test-connection`

#### 3. Parsing Issues
- Check platform patterns in `config/imap.php`
- Review logs in `storage/logs/imap.log`
- Test with known good email samples
- Verify sport category detection logic

#### 4. Dashboard Not Loading
- Check Laravel logs in `storage/logs/laravel.log`
- Verify database connections
- Ensure proper route registration
- Check middleware authentication

### Debug Commands

#### Connection Testing
```bash
# Test all connections
php artisan hdtickets:monitor-emails --test-connection

# Test specific connection
php artisan hdtickets:monitor-emails --test-connection --connection=gmail

# Verbose output for debugging
php artisan hdtickets:monitor-emails --test-connection --verbose
```

#### Log Analysis
```bash
# View IMAP logs
tail -f storage/logs/imap.log

# Search for specific errors
grep -i "error" storage/logs/imap.log

# Check Laravel application logs
tail -f storage/logs/laravel.log
```

## 📚 Development Notes

### Extending Platform Support

To add support for a new email platform:

1. **Add Platform Patterns** in `config/imap.php`:
```php
'new_platform' => [
    'from_patterns' => ['*@newplatform.com'],
    'subject_keywords' => ['tickets', 'events'],
    'body_keywords' => ['sports', 'venue'],
],
```

2. **Create Parser Method** in `EmailParsingService`:
```php
private function parseNewPlatformEmail(array $headers, string $body): array
{
    // Platform-specific parsing logic
    return [
        'sports_events' => [...],
        'tickets' => [...],
    ];
}
```

3. **Update Tests** to include new platform validation

### Custom Alert Thresholds

Alert thresholds can be customized in `ImapNotificationService`:

```php
private array $alertThresholds = [
    'connection_failures' => 3,    // Failures before alert
    'processing_errors' => 5,      // Errors in 10 minutes
    'low_discovery_rate' => 2,     // Hours without discoveries
    'high_response_time' => 10,    // Seconds threshold
];
```

### Performance Optimization

For high-volume email processing:

1. **Increase Batch Size** - `IMAP_BATCH_SIZE=100`
2. **Enable Queue Workers** - `IMAP_QUEUE_ENABLED=true`
3. **Use Redis Clustering** - For cache and session management
4. **Optimize Database** - Add indexes for frequent queries
5. **Monitor Memory Usage** - Adjust PHP memory limits as needed

## 📋 Maintenance

### Regular Maintenance Tasks

#### Daily
- Review connection health dashboard
- Check for new error patterns in logs
- Monitor disk space usage
- Verify queue worker status

#### Weekly  
- Clear old processed email cache
- Review platform performance metrics
- Update platform patterns if needed
- Check notification delivery rates

#### Monthly
- Analyze sports event discovery trends
- Review and optimize parsing accuracy
- Update email connection credentials
- Performance optimization review

### Backup Considerations

Important data to backup:
- Configuration files (`config/imap.php`, `.env`)
- Processed email metadata (if stored)
- Analytics and metrics data
- Platform pattern customizations
- User notification preferences

## 🎯 Future Enhancements

### Planned Features
- **AI-powered Email Classification** - Machine learning for better parsing
- **Advanced Analytics Dashboard** - Detailed platform performance metrics
- **Custom Alert Rules** - User-defined notification conditions
- **Email Content Preview** - Dashboard view of processed emails
- **Platform Pattern Management** - Web interface for pattern editing
- **Multi-language Support** - International sports event detection
- **Mobile Dashboard App** - Native mobile monitoring interface

### Integration Opportunities
- **CRM Systems** - Customer notification integration
- **Business Intelligence** - Advanced reporting and analytics
- **External APIs** - Third-party ticket platform integration
- **Machine Learning** - Predictive analytics for event popularity

---

## 📞 Support

For technical support or questions about the IMAP Dashboard System:

1. **Check Documentation** - Review this guide and inline code comments
2. **Review Logs** - Check `storage/logs/imap.log` for specific errors
3. **Test Components** - Use `php artisan hdtickets:test-imap` for diagnostics
4. **Monitor Dashboard** - Real-time system health in the web interface

The IMAP Email Monitoring Dashboard System provides comprehensive visibility and control over sports event ticket discovery operations, ensuring reliable monitoring and immediate notification of important events and system issues.
