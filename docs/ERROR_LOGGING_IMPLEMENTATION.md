# HD Tickets - Comprehensive Error Logging and Monitoring Implementation

## Overview

This implementation provides a complete error logging and monitoring system for the HD Tickets sports events platform with detailed tracking across all critical paths.

## Features Implemented

### 1. Enhanced ActivityLogger Service
- **Location**: `app/Services/ActivityLogger.php`
- **Features**:
  - Performance timing with memory usage tracking
  - API endpoint access logging with detailed context
  - Database query performance monitoring
  - JavaScript event and error logging
  - WebSocket connection tracking
  - Critical error notifications with admin alerts
  - Request ID tracking for correlation

### 2. Comprehensive Logging Middleware
- **Location**: `app/Http/Middleware/ComprehensiveLoggingMiddleware.php`
- **Features**:
  - Automatic API endpoint access logging
  - Database query capture and performance analysis
  - Response timing and memory usage
  - Error response logging
  - Performance metrics collection

### 3. Health Monitoring Endpoints
- **Location**: `app/Http/Controllers/HealthController.php` (enhanced)
- **Endpoints**:
  - `/api/v1/dashboard/health` - Complete system health
  - `/api/v1/dashboard/health/database` - Database specific
  - `/api/v1/dashboard/health/redis` - Redis specific  
  - `/api/v1/dashboard/health/websockets` - WebSocket health
  - `/api/v1/dashboard/health/services` - External services

### 4. JavaScript Error Reporting
- **Location**: `resources/js/utils/errorReporting.js`
- **Features**:
  - Console error capture and reporting
  - Unhandled JavaScript errors
  - Promise rejection handling
  - Performance monitoring (long tasks, page load metrics)
  - Browser session tracking
  - Batch error reporting to backend

### 5. Frontend Error Logging API
- **Location**: `app/Http/Controllers/Api/DashboardController.php` (logError method)
- **Endpoint**: `POST /api/v1/dashboard/log-error`
- **Features**:
  - Receives JavaScript error batches
  - Categorizes error severity
  - Routes to appropriate log channels
  - Critical error admin notification

## Log Channels Configuration

Enhanced logging configuration in `config/logging.php`:

### Channels Added/Enhanced:
1. **ticket_apis** - API access and ticket operations
2. **monitoring** - System monitoring events
3. **performance** - Performance metrics and slow operations
4. **security** - Security-related events
5. **critical_alerts** - Critical errors with Slack notifications
6. **audit** - Administrative activity tracking

## Usage Examples

### Backend Logging

```php
// Performance timing
$logger = app(ActivityLogger::class);
$logger->startTiming('ticket_search');
// ... perform operation
$logger->endTiming('ticket_search', 2.0); // Warn if > 2 seconds

// API access logging (automatic via middleware)
// Database query logging (automatic via middleware)

// Critical error logging
try {
    // risky operation
} catch (Exception $e) {
    $logger->logCriticalError($e, ['operation' => 'ticket_purchase'], true);
}
```

### Frontend Error Reporting

```javascript
// Automatic setup
import errorReporter from '@utils/errorReporting';

// Manual error logging
errorReporter.logEvent('info', 'User action completed', {
    action: 'ticket_search',
    filters: filters
});

// Performance timing
errorReporter.startTiming('api_request');
// ... make request
const duration = errorReporter.endTiming('api_request', 1000);
```

## Log File Locations

```
storage/logs/
├── laravel.log              # General application logs
├── ticket_apis.log          # API access and operations
├── monitoring.log           # System monitoring events  
├── performance.log          # Performance metrics
├── security.log            # Security events
├── audit.log               # Admin activity tracking
└── critical_alerts.log     # Critical errors
```

## Monitoring and Alerting

### Critical Error Thresholds
- **5 errors per minute** triggers admin notification
- JavaScript errors automatically categorized by severity
- Performance issues logged when thresholds exceeded:
  - API responses > 2 seconds
  - Database queries > 1 second
  - JavaScript operations > 50ms

### Health Check Monitoring
- **Database**: Connection time, query performance
- **Redis**: Ping response, memory usage
- **Cache**: Operation timing, hit rates
- **WebSocket**: Connection status, configuration
- **System**: Memory, disk usage, queue status
- **Scraping**: Recent activity, platform availability

## Admin Notifications

Critical errors trigger notifications through:
1. **Slack channel** (configured via LOG_SLACK_WEBHOOK_URL)
2. **Emergency log channel** for immediate attention
3. **Email alerts** (can be configured)

## Performance Metrics

Tracked automatically:
- Request duration and memory usage
- Database query count and timing
- Response size and status codes
- JavaScript performance (page load, long tasks)
- WebSocket connection health

## Security Considerations

- Sensitive headers excluded from logs (authorization, cookies)
- Request parameters sanitized
- User identification without PII exposure
- Secure session tracking
- Rate limiting on error reporting endpoints

## Integration Points

### With Existing Systems:
- **New Relic**: Performance monitoring integration
- **Sentry**: Error tracking configuration  
- **Pusher**: WebSocket health monitoring
- **Laravel Telescope**: Development debugging

### Browser Integration:
- Automatic initialization on page load
- Error batching to prevent spam
- Session correlation across page loads
- Performance timing integration

## Next Steps

1. **Configure Slack Webhook** for critical alerts
2. **Set up log rotation** for production
3. **Implement log analysis dashboard**
4. **Add custom metrics collection**
5. **Set up automated alerts based on log patterns**

## Troubleshooting

### Common Issues:
1. **Missing logs**: Check file permissions in `storage/logs/`
2. **High log volume**: Adjust log levels in `.env` 
3. **JavaScript errors not reporting**: Check network connectivity
4. **Health checks failing**: Verify service configurations

### Debug Mode:
Set `LOG_LEVEL=debug` in `.env` for verbose logging during development.

## Citations

This implementation was created following Laravel best practices and incorporates sports events ticket monitoring specific requirements as per the user rules.
