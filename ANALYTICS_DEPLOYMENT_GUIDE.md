# HD Tickets Advanced Analytics System - Deployment & Usage Guide

## 🚀 System Overview

The HD Tickets Advanced Analytics System is a comprehensive sports event ticket analysis platform featuring:

- **Advanced Analytics Dashboard** with real-time visualization
- **Predictive Analytics Engine** powered by machine learning
- **Anomaly Detection System** with intelligent alerting
- **Competitive Intelligence Module** for market analysis
- **Business Intelligence API** for third-party integrations
- **Automated Reporting System** with scheduled exports

## 📋 System Requirements

### Environment
- Ubuntu 24.04 LTS
- Apache2 / Nginx
- PHP 8.3.25+
- MariaDB 10.4+ / MySQL 8.0+
- Redis 6.0+
- Node.js 22.19.0+
- Composer 2.0+

### Laravel Dependencies
- Laravel 11.45.2
- Laravel Horizon (queue management)
- Laravel Sanctum (API authentication)
- PhpSpreadsheet (Excel export)
- DomPDF (PDF generation)

## 🔧 Installation & Configuration

### 1. Dependencies Installation

```bash
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node.js dependencies  
npm install --production

# Build frontend assets
npm run build

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 2. Configuration Setup

The system uses comprehensive configuration in `config/analytics.php`:

```bash
# Publish analytics configuration (if needed)
php artisan vendor:publish --tag=analytics-config

# Update environment variables
cat >> .env << 'EOF'
# Analytics Configuration
ANALYTICS_CACHE_TTL=3600
ANALYTICS_EXPORT_DISK=local
ANALYTICS_MAX_EXPORT_ROWS=50000

# Predictive Analytics
PREDICTIVE_ANALYTICS_ENABLED=true
ANOMALY_DETECTION_ENABLED=true

# Business Intelligence API
BI_API_RATE_LIMIT=100
BI_API_HEAVY_LIMIT=20
BI_EXPORT_LIMIT=5

# Automated Reporting
AUTOMATED_REPORTS_ENABLED=true
REPORT_EMAIL_FROM=analytics@hdtickets.com
EOF
```

### 3. Database Setup

Ensure all required tables exist:

```bash
# Run database migrations
php artisan migrate

# Seed initial data (if applicable)
php artisan db:seed
```

### 4. Queue Configuration

The system uses Laravel Horizon for queue management:

```bash
# Start Horizon for background jobs
php artisan horizon

# Or use supervisor for production
sudo supervisorctl start laravel-horizon
```

### 5. Scheduled Tasks

Add to crontab for automated reporting:

```bash
# Edit crontab
crontab -e

# Add Laravel scheduler
* * * * * cd /var/www/hdtickets && php artisan schedule:run >> /dev/null 2>&1
```

## 📊 Dashboard Access

### Web Dashboard
- **URL:** `https://your-domain.com/dashboard/analytics`
- **Access:** Admin and Agent roles only
- **Features:**
  - Real-time data visualization
  - Interactive charts and filters
  - Export functionality
  - Anomaly alerts
  - Historical comparisons

### Dashboard Components
1. **Overview Metrics** - Key performance indicators
2. **Platform Performance** - Multi-platform analysis
3. **Pricing Trends** - Historical price analysis
4. **Event Popularity** - Trending events tracking
5. **Anomaly Alerts** - Real-time issue detection
6. **Predictive Insights** - ML-powered forecasting

## 🔌 API Documentation

### Base URL
```
https://your-domain.com/api/v1/bi/
```

### Authentication
All API endpoints require Bearer token authentication:

```bash
# Login to get token
curl -X POST https://your-domain.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@hdtickets.local","password":"your-password"}'

# Use token in subsequent requests
curl -H "Authorization: Bearer YOUR_TOKEN" \
  https://your-domain.com/api/v1/bi/health
```

### Core Endpoints

#### 1. Health Check
```http
GET /api/v1/bi/health
```
**Response:**
```json
{
  "success": true,
  "data": {
    "status": "healthy",
    "version": "1.0.0",
    "endpoints": {...},
    "rate_limits": {...}
  }
}
```

#### 2. Analytics Overview
```http
GET /api/v1/bi/analytics/overview?sport=football&date_from=2024-01-01
```
**Parameters:**
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `sport` (optional): Sport filter
- `platform` (optional): Platform filter

#### 3. Ticket Metrics
```http
GET /api/v1/bi/tickets/metrics?include_historical=true
```
**Parameters:**
- `date_from`, `date_to`: Date range
- `sport`: Sport filter
- `price_min`, `price_max`: Price range
- `include_historical`: Include historical comparison

#### 4. Platform Performance
```http
GET /api/v1/bi/platforms/performance?include_metrics[]=pricing&include_metrics[]=volume
```
**Parameters:**
- `platform_id`: Specific platform ID
- `include_metrics[]`: Array of metrics (`pricing`, `volume`, `trends`, `reliability`)
- `date_range`: Time range (`7d`, `30d`, `90d`, `1y`)

#### 5. Competitive Intelligence
```http
GET /api/v1/bi/competitive/intelligence?analysis_type=pricing&include_recommendations=true
```
**Parameters:**
- `analysis_type`: Type (`overview`, `pricing`, `positioning`, `gaps`)
- `sport`: Sport filter
- `include_recommendations`: Business recommendations

#### 6. Predictive Insights
```http
GET /api/v1/bi/predictive/insights?prediction_type=price&horizon_days=30
```
**Parameters:**
- `prediction_type`: Type (`price`, `demand`, `success`, `market_trends`)
- `event_id`: Specific event ID
- `sport`: Sport filter
- `horizon_days`: Prediction horizon (1-365 days)

#### 7. Current Anomalies
```http
GET /api/v1/bi/anomalies/current?severity=high&category=price&limit=20
```
**Parameters:**
- `severity`: Severity level (`low`, `medium`, `high`, `critical`)
- `category`: Category (`price`, `volume`, `velocity`, `platform`, `temporal`)
- `limit`: Maximum results (1-100)

#### 8. Data Export
```http
POST /api/v1/bi/export/dataset
Content-Type: application/json

{
  "dataset": "tickets",
  "format": "csv",
  "date_from": "2024-01-01",
  "date_to": "2024-12-31",
  "fields": ["id", "event_name", "sport", "price", "venue"],
  "compress": true
}
```
**Supported datasets:** `tickets`, `platforms`, `analytics`, `competitive`
**Supported formats:** `json`, `csv`, `parquet`

### Rate Limits
- **Standard endpoints:** 100 requests/hour
- **Heavy endpoints:** 20 requests/hour  
- **Export endpoints:** 5 requests/hour

## 📈 Automated Reporting

### Report Types
1. **Daily Analytics** - Key metrics summary
2. **Weekly Performance** - Platform comparison
3. **Monthly Insights** - Competitive analysis
4. **Custom Reports** - Configurable metrics

### Configuration
Reports are configured via the database using the `ScheduledReport` model:

```php
use App\Models\ScheduledReport;

ScheduledReport::create([
    'name' => 'Weekly Platform Performance',
    'type' => 'platform_performance',
    'frequency' => 'weekly', // daily, weekly, monthly
    'format' => 'pdf',
    'recipients' => ['manager@hdtickets.com', 'analytics@hdtickets.com'],
    'filters' => [
        'date_range' => '7d',
        'include_charts' => true
    ],
    'is_active' => true
]);
```

### Manual Report Generation
```bash
# Generate specific report
php artisan reports:generate --report-id=1

# Generate all due reports
php artisan reports:generate

# Generate report by type
php artisan reports:generate --type=daily_analytics
```

## 🔍 Monitoring & Troubleshooting

### System Health
```bash
# Check analytics system health
curl -H "Authorization: Bearer TOKEN" \
  https://your-domain.com/api/v1/bi/health

# Check Laravel logs
tail -f storage/logs/laravel.log

# Check Horizon status
php artisan horizon:status
```

### Performance Monitoring
The system includes built-in performance metrics:

```bash
# View analytics performance
php artisan analytics:performance

# Clear analytics cache
php artisan cache:forget analytics*

# Check memory usage
php artisan analytics:memory-usage
```

### Common Issues

#### 1. Memory Exhaustion
If large dataset exports fail:
```php
// In config/analytics.php
'max_rows_per_export' => 10000, // Reduce from 50000
'chunk_size' => 500,           // Process in smaller chunks
```

#### 2. API Rate Limit Exceeded
Adjust rate limits in `config/analytics.php`:
```php
'api_rate_limits' => [
    'standard' => 200,  // Increase from 100
    'heavy' => 50,      // Increase from 20
],
```

#### 3. Export File Not Found
Ensure proper file permissions:
```bash
chmod -R 755 storage/app/analytics/exports/
chown -R www-data:www-data storage/app/analytics/exports/
```

## 🛡️ Security Considerations

### Access Control
- All analytics endpoints require authentication
- Role-based access (Admin and Agent only)
- API tokens with expiration
- Rate limiting per user

### Data Protection
- Sensitive data is not exposed in exports
- File downloads are secured with signed URLs
- Export files are automatically cleaned up
- Audit logging for all API access

### Security Headers
The system automatically applies security headers:
- CSRF protection
- XSS protection
- Content Security Policy
- Secure cookie settings

## 📚 Usage Examples

### JavaScript Frontend Integration
```javascript
// Fetch analytics data
const response = await fetch('/api/v1/bi/analytics/overview', {
    headers: {
        'Authorization': `Bearer ${authToken}`,
        'Content-Type': 'application/json'
    }
});

const data = await response.json();
console.log(data.data.overview_metrics);
```

### Python Integration
```python
import requests

# API client setup
headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

# Fetch competitive intelligence
response = requests.get(
    'https://hdtickets.com/api/v1/bi/competitive/intelligence',
    headers=headers,
    params={
        'analysis_type': 'pricing',
        'include_recommendations': True
    }
)

data = response.json()
```

### Power BI Integration
```powerquery
let
    Source = Json.Document(Web.Contents(
        "https://hdtickets.com/api/v1/bi/tickets/metrics",
        [
            Headers = [
                #"Authorization" = "Bearer " & TokenValue,
                #"Content-Type" = "application/json"
            ]
        ]
    )),
    data = Source[data],
    summary = data[summary]
in
    summary
```

## 🔄 Maintenance

### Regular Tasks
```bash
# Weekly maintenance
php artisan analytics:cleanup-old-exports
php artisan analytics:update-models
php artisan analytics:optimize-cache

# Monthly maintenance  
php artisan analytics:recompute-baselines
php artisan analytics:update-predictions
```

### Database Optimization
```sql
-- Optimize analytics tables
OPTIMIZE TABLE scraped_tickets;
OPTIMIZE TABLE ticket_sources;
OPTIMIZE TABLE scheduled_reports;
```

### Backup Strategy
Include analytics data in your backup routine:
```bash
# Backup analytics exports
rsync -av storage/app/analytics/exports/ /backup/analytics/

# Backup configuration
cp config/analytics.php /backup/config/
```

## 📞 Support

### Documentation
- **API Docs:** Available at `/api/v1/bi/health` (includes endpoint list)
- **Dashboard Help:** Built-in help tooltips and guides
- **Configuration:** Comprehensive comments in `config/analytics.php`

### Logging
All system activities are logged:
- **Application logs:** `storage/logs/laravel.log`
- **Analytics logs:** `storage/logs/analytics.log`
- **API access logs:** `storage/logs/api.log`

### Performance Metrics
Monitor system performance:
- Response times tracked automatically
- Memory usage monitoring
- Cache hit/miss ratios
- Background job success rates

---

## 🎉 System Ready

The HD Tickets Advanced Analytics System is now fully deployed and operational. The system provides:

✅ **Real-time Analytics Dashboard**  
✅ **Predictive Insights & ML Models**  
✅ **Anomaly Detection & Alerts**  
✅ **Competitive Intelligence**  
✅ **Business Intelligence API**  
✅ **Automated Reporting**  
✅ **Data Export Capabilities**  
✅ **Role-based Security**  

Access the dashboard at `/dashboard/analytics` or use the API endpoints for programmatic access.
