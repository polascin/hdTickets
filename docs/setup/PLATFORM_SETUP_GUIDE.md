# Platform Setup Guide

This guide provides step-by-step instructions for setting up each ticket platform integration in the HDTickets system.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Configuration](#environment-configuration)
3. [Platform-Specific Setup](#platform-specific-setup)
   - [FunZone Setup](#funzone-setup)
   - [StubHub Setup](#stubhub-setup)
   - [Viagogo Setup](#viagogo-setup)
   - [TickPick Setup](#tickpick-setup)
   - [Ticketmaster Setup](#ticketmaster-setup)
4. [Testing Setup](#testing-setup)
5. [Monitoring Configuration](#monitoring-configuration)
6. [Troubleshooting](#troubleshooting)

## Prerequisites

### System Requirements
- PHP 8.1 or higher
- Laravel 12.x
- MySQL 8.0 or higher
- Redis (for caching and queuing)
- Supervisord (for background workers)

### Required PHP Extensions
- `curl` - For HTTP requests
- `dom` - For HTML parsing
- `mbstring` - For multi-byte string handling
- `openssl` - For secure connections
- `json` - For JSON handling
- `xml` - For XML parsing

### Composer Dependencies
```bash
composer require symfony/dom-crawler
composer require symfony/css-selector
composer require guzzlehttp/guzzle
```

## Environment Configuration

### Basic Configuration

Add the following environment variables to your `.env` file:

```env
# General Scraping Configuration
SCRAPING_ENABLED=true
SCRAPING_USER_AGENT_ROTATION=true
SCRAPING_DEFAULT_TIMEOUT=30
SCRAPING_DEFAULT_DELAY=2
SCRAPING_MAX_RETRIES=3

# Rate Limiting
SCRAPING_RATE_LIMIT_ENABLED=true
SCRAPING_GLOBAL_RATE_LIMIT=100

# Proxy Configuration (Optional)
SCRAPING_PROXY_ENABLED=false
SCRAPING_PROXY_HOST=
SCRAPING_PROXY_PORT=
SCRAPING_PROXY_USERNAME=
SCRAPING_PROXY_PASSWORD=

# Caching
SCRAPING_CACHE_ENABLED=true
SCRAPING_CACHE_TTL=300

# Logging
SCRAPING_LOG_LEVEL=info
SCRAPING_LOG_CHANNEL=ticket_apis
```

### Database Configuration

Run the migrations to set up the required tables:

```bash
php artisan migrate
```

Create indexes for optimal performance:

```sql
-- Platform-specific indexes
CREATE INDEX idx_tickets_platform_date ON tickets(platform, event_date);
CREATE INDEX idx_tickets_platform_location ON tickets(platform, location);
CREATE INDEX idx_tickets_external_id ON tickets(external_id);
CREATE INDEX idx_scraping_stats_platform_date ON scraping_stats(platform, created_at);
```

## Platform-Specific Setup

### FunZone Setup

FunZone is a Slovak entertainment platform that requires web scraping.

#### Configuration

Add FunZone-specific settings to your `.env`:

```env
# FunZone Configuration
FUNZONE_ENABLED=true
FUNZONE_BASE_URL=https://www.funzone.sk
FUNZONE_RATE_LIMIT=10
FUNZONE_RATE_WINDOW=60
FUNZONE_TIMEOUT=30
FUNZONE_MAX_RESULTS=100

# FunZone Proxy (Recommended for non-Slovak IPs)
FUNZONE_PROXY_ENABLED=false
FUNZONE_PROXY_COUNTRY=SK
```

#### Required Setup Steps

1. **Verify Access**:
   ```bash
   php artisan funzone:test-connection
   ```

2. **Configure Slovak Locale**:
   ```php
   // config/app.php
   'locales' => [
       'sk' => 'Slovak',
       'en' => 'English',
   ],
   
   'fallback_locale' => 'sk',
   ```

3. **Test Slovak Date Parsing**:
   ```bash
   php artisan funzone:test-date-parsing
   ```

#### Proxy Setup (Recommended)

For optimal results, use Slovak proxies:

```env
# Slovak Proxy Configuration
FUNZONE_PROXY_ENABLED=true
FUNZONE_PROXY_HOST=slovakproxy.example.com
FUNZONE_PROXY_PORT=8080
FUNZONE_PROXY_COUNTRY=SK
```

#### Regional Configuration

Configure Slovak regions and cities:

```php
// config/ticket_apis.php
'funzone' => [
    'regions' => [
        'bratislava' => 'Bratislavský kraj',
        'trnava' => 'Trnavský kraj',
        'trencin' => 'Trenčiansky kraj',
        // ... other regions
    ],
    'major_cities' => [
        'Bratislava', 'Košice', 'Prešov', 'Žilina', 'Banská Bystrica'
    ],
],
```

### StubHub Setup

StubHub supports both API and web scraping modes.

#### API Configuration (Preferred)

Register for StubHub Partner API access:

1. Visit [StubHub Partner API](https://developer.stubhub.com)
2. Register for an account
3. Create an application
4. Obtain API credentials

Add credentials to `.env`:

```env
# StubHub API Configuration
STUBHUB_ENABLED=true
STUBHUB_API_MODE=true
STUBHUB_API_KEY=your_api_key_here
STUBHUB_APP_TOKEN=your_app_token_here
STUBHUB_API_BASE_URL=https://api.stubhub.com/sellers/search/events/v3

# Sandbox Configuration
STUBHUB_SANDBOX=true
STUBHUB_SANDBOX_URL=https://api.stubhub-sandbox.com

# Rate Limiting
STUBHUB_RATE_LIMIT=100
STUBHUB_RATE_WINDOW=60
```

#### Scraping Configuration (Fallback)

If API access is unavailable, configure scraping:

```env
# StubHub Scraping Configuration
STUBHUB_SCRAPING_ENABLED=true
STUBHUB_CLOUDFLARE_BYPASS=true
STUBHUB_USE_HEADLESS_BROWSER=false

# Anti-Bot Evasion
STUBHUB_USER_AGENT_ROTATION=true
STUBHUB_SESSION_ROTATION=true
STUBHUB_DELAY_MIN=3
STUBHUB_DELAY_MAX=7
```

#### Testing StubHub Setup

```bash
# Test API connection
php artisan stubhub:test-api

# Test scraping fallback
php artisan stubhub:test-scraping

# Verify rate limiting
php artisan stubhub:test-rate-limit
```

### Viagogo Setup

Viagogo requires careful configuration due to geographic restrictions.

#### Basic Configuration

```env
# Viagogo Configuration
VIAGOGO_ENABLED=true
VIAGOGO_BASE_URL=https://www.viagogo.com
VIAGOGO_RATE_LIMIT=20
VIAGOGO_RATE_WINDOW=60

# Geographic Configuration
VIAGOGO_DEFAULT_COUNTRY=US
VIAGOGO_DEFAULT_CURRENCY=USD
VIAGOGO_ACCEPT_LANGUAGE=en-US,en;q=0.9

# VPN/Proxy for Geographic Restrictions
VIAGOGO_PROXY_ENABLED=true
VIAGOGO_PROXY_ROTATE_COUNTRIES=true
```

#### Multi-Currency Support

Configure currency handling:

```php
// config/ticket_apis.php
'viagogo' => [
    'currencies' => [
        'USD' => ['symbol' => '$', 'decimal_places' => 2],
        'EUR' => ['symbol' => '€', 'decimal_places' => 2],
        'GBP' => ['symbol' => '£', 'decimal_places' => 2],
        'CAD' => ['symbol' => 'C$', 'decimal_places' => 2],
    ],
    'default_currency' => 'USD',
],
```

#### Geographic Configuration

Set up location-based configurations:

```env
# Geographic Targeting
VIAGOGO_TARGET_COUNTRIES=US,UK,CA,AU
VIAGOGO_PROXY_POOL=multi-country
VIAGOGO_GEO_DETECTION_BYPASS=true
```

### TickPick Setup

TickPick is a US-based no-fee platform.

#### Configuration

```env
# TickPick Configuration
TICKPICK_ENABLED=true
TICKPICK_BASE_URL=https://www.tickpick.com
TICKPICK_RATE_LIMIT=30
TICKPICK_RATE_WINDOW=60

# No-Fee Pricing
TICKPICK_INCLUDE_FEES=false
TICKPICK_PRICE_TRANSPARENCY=true

# US Market Focus
TICKPICK_DEFAULT_COUNTRY=US
TICKPICK_DEFAULT_CURRENCY=USD
```

#### Special Features Configuration

```php
// config/ticket_apis.php
'tickpick' => [
    'features' => [
        'no_fees' => true,
        'price_transparency' => true,
        'seller_ratings' => true,
    ],
    'markets' => [
        'primary' => 'US',
        'supported' => ['US', 'CA'],
    ],
],
```

### Ticketmaster Setup

Ticketmaster offers official API access.

#### API Registration

1. Visit [Ticketmaster Developer Portal](https://developer.ticketmaster.com)
2. Create an account
3. Register your application
4. Obtain API key

#### Configuration

```env
# Ticketmaster API Configuration
TICKETMASTER_ENABLED=true
TICKETMASTER_API_KEY=your_api_key_here
TICKETMASTER_API_SECRET=your_api_secret_here
TICKETMASTER_API_BASE_URL=https://app.ticketmaster.com/discovery/v2

# Rate Limiting (Ticketmaster enforced)
TICKETMASTER_RATE_LIMIT=5000
TICKETMASTER_RATE_WINDOW=3600
TICKETMASTER_QUOTA_DAILY=50000

# Market Configuration
TICKETMASTER_DEFAULT_COUNTRY_CODE=US
TICKETMASTER_SUPPORTED_COUNTRIES=US,CA,MX,AU,NZ,UK,IE
```

#### Webhook Configuration (Optional)

For real-time updates:

```env
# Ticketmaster Webhooks
TICKETMASTER_WEBHOOK_ENABLED=false
TICKETMASTER_WEBHOOK_SECRET=your_webhook_secret
TICKETMASTER_WEBHOOK_URL=https://yourdomain.com/webhooks/ticketmaster
```

#### Advanced Features

Enable advanced Ticketmaster features:

```php
// config/ticket_apis.php
'ticketmaster' => [
    'features' => [
        'presales' => true,
        'verified_resale' => true,
        'mobile_only_tickets' => true,
        'fan_club_presales' => true,
    ],
    'event_types' => [
        'music', 'sports', 'arts', 'miscellaneous'
    ],
],
```

## Testing Setup

### Running Tests

Execute the full test suite:

```bash
# Run all platform tests
php artisan test --testsuite=Unit --testsuite=Integration

# Test specific platforms
php artisan test tests/Unit/Services/TicketApis/FunZoneClientTest.php
php artisan test tests/Integration/Api/FunZoneControllerTest.php

# Run with coverage
php artisan test --coverage-html=coverage
```

### Manual Testing

Test each platform individually:

```bash
# FunZone
php artisan funzone:search "concert" --location="Bratislava" --limit=5

# StubHub  
php artisan stubhub:search "yankees" --city="New York" --limit=5

# Viagogo
php artisan viagogo:search "ed sheeran" --city="London" --limit=5

# TickPick
php artisan tickpick:search "lakers" --city="Los Angeles" --limit=5

# Ticketmaster
php artisan ticketmaster:search "taylor swift" --city="Los Angeles" --limit=5
```

### Load Testing

Test rate limiting and performance:

```bash
# Simulate concurrent requests
php artisan platform:load-test funzone --concurrent=10 --requests=100

# Test rate limiting
php artisan platform:test-rate-limits --all
```

## Monitoring Configuration

### Logging Setup

Configure comprehensive logging:

```php
// config/logging.php
'channels' => [
    'ticket_apis' => [
        'driver' => 'daily',
        'path' => storage_path('logs/ticket-apis.log'),
        'level' => 'info',
        'days' => 30,
        'formatter' => App\Logging\TicketApiFormatter::class,
    ],
],
```

### Performance Monitoring

Set up performance tracking:

```env
# Performance Monitoring
MONITORING_ENABLED=true
MONITORING_RESPONSE_TIME_THRESHOLD=5000
MONITORING_ERROR_RATE_THRESHOLD=5
MONITORING_ALERT_EMAIL=admin@hdtickets.com

# Metrics Collection
METRICS_ENABLED=true
METRICS_RETENTION_DAYS=90
```

### Health Checks

Configure automated health checks:

```bash
# Add to crontab
* * * * * php artisan schedule:run

# Health check commands
php artisan platform:health-check --all
php artisan platform:selector-validation --all
```

### Alert Configuration

Set up alerts for critical issues:

```php
// config/ticket_apis.php
'alerts' => [
    'error_rate_threshold' => 10, // %
    'response_time_threshold' => 5000, // ms
    'bot_detection_threshold' => 5, // incidents per hour
    'success_rate_threshold' => 90, // %
],
```

## Troubleshooting

### Common Issues

#### Bot Detection

**Symptoms**: 403 errors, CAPTCHA challenges, blocked requests

**Solutions**:
```bash
# Rotate User-Agents
php artisan platform:rotate-user-agents

# Reset sessions
php artisan platform:reset-sessions

# Check proxy health
php artisan platform:check-proxies
```

#### Rate Limiting

**Symptoms**: 429 errors, slow responses

**Solutions**:
```bash
# Check current rates
php artisan platform:rate-status

# Adjust delays
php artisan platform:adjust-delays --platform=stubhub --increase=50%

# Clear rate limit cache
php artisan cache:forget rate_limit_*
```

#### Selector Failures

**Symptoms**: Empty results, parsing errors

**Solutions**:
```bash
# Test current selectors
php artisan platform:test-selectors --platform=funzone

# Update selectors
php artisan platform:update-selectors --platform=funzone

# Validate HTML structure
php artisan platform:validate-html --url="https://example.com"
```

### Debug Mode

Enable detailed debugging:

```env
# Debug Configuration
APP_DEBUG=true
SCRAPING_DEBUG=true
SCRAPING_SAVE_HTML=true
SCRAPING_VERBOSE_LOGGING=true
```

Debug commands:

```bash
# Save HTML responses for analysis
php artisan platform:save-html --platform=funzone --search="concert"

# Test specific selectors
php artisan platform:test-selector ".event-card" --url="https://example.com"

# Analyze response times
php artisan platform:analyze-performance --days=7
```

### Performance Optimization

#### Caching Optimization

```bash
# Optimize caching
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear specific caches
php artisan cache:forget scraping_*
php artisan cache:forget selector_stats_*
```

#### Database Optimization

```bash
# Analyze slow queries
php artisan db:analyze-slow-queries

# Optimize tables
php artisan db:optimize-tables

# Update statistics
php artisan db:update-stats
```

### Getting Help

#### Log Analysis

```bash
# Analyze error patterns
php artisan logs:analyze --platform=stubhub --hours=24

# Generate error report
php artisan platform:error-report --all --format=json

# Monitor real-time logs
tail -f storage/logs/ticket-apis.log
```

#### Support Commands

```bash
# Generate support report
php artisan platform:support-report

# Test all configurations
php artisan platform:test-all-configs

# Validate environment setup
php artisan platform:validate-environment
```

For additional support, check the [API Documentation](../api/TICKET_PLATFORMS_API.md) and [Scraping Guide](../scraping/PLATFORM_SCRAPING_GUIDE.md).
