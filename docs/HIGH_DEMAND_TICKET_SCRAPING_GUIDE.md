# High-Demand Ticket Scraping Guide

This comprehensive guide explains how to effectively scrape high-demand tickets using the HDTickets system.

## Table of Contents
1. [Overview](#overview)
2. [High-Demand Events](#high-demand-events)
3. [Scraping Strategies](#scraping-strategies)
4. [Commands & Usage](#commands--usage)
5. [API Integration](#api-integration)
6. [Best Practices](#best-practices)
7. [Monitoring & Alerts](#monitoring--alerts)
8. [Queue Management](#queue-management)
9. [Anti-Detection](#anti-detection)
10. [Troubleshooting](#troubleshooting)

## Overview

High-demand ticket scraping focuses on obtaining tickets for popular sports events where:
- Tickets sell out quickly
- High competition from other buyers
- Platforms implement queue systems
- Anti-bot measures are strict
- Price fluctuations are significant

### Supported Platforms
- **Official Club Sites**: Real Madrid, Barcelona, Bayern Munich, Manchester City, PSG, Juventus
- **Major Vendors**: StubHub, Ticketmaster, Viagogo
- **Secondary Markets**: TickPick, SeatGeek, AXS

## High-Demand Events

### Extreme Demand (Priority Level: Maximum)
```php
$extremeDemandEvents = [
    'El Clásico' => [
        'teams' => ['Real Madrid', 'Barcelona'],
        'monitoring_interval' => 15, // seconds
        'pre_sale_monitoring' => true,
        'queue_strategy' => 'aggressive'
    ],
    'Champions League Final' => [
        'monitoring_interval' => 10,
        'pre_sale_monitoring' => true,
        'queue_strategy' => 'aggressive'
    ]
];
```

### Very High Demand
- Manchester Derby (City vs United)
- Der Klassiker (Bayern vs Dortmund)
- Champions League Knockout Rounds
- Liverpool vs Manchester United

### High Demand
- Premier League Top 6 matches
- La Liga Clásicos
- Bundesliga top matches
- Champions League Group Stage

## Scraping Strategies

### 1. Aggressive Strategy
**Use for**: El Clásico, Champions League Final
```php
$aggressiveConfig = [
    'concurrent_sessions' => 5,
    'retry_attempts' => 10,
    'retry_delay_base' => 1000, // ms
    'session_rotation_frequency' => 3,
    'bypass_queue_attempts' => true,
    'pre_queue_monitoring' => true
];
```

**Features**:
- Multiple concurrent sessions
- Rapid retry attempts
- Queue bypass techniques
- Continuous pre-sale monitoring

### 2. Moderate Strategy
**Use for**: Major derby matches, semi-finals
```php
$moderateConfig = [
    'concurrent_sessions' => 3,
    'retry_attempts' => 6,
    'retry_delay_base' => 2000,
    'session_rotation_frequency' => 5,
    'pre_queue_monitoring' => true
];
```

### 3. Conservative Strategy
**Use for**: Regular high-demand matches
```php
$conservativeConfig = [
    'concurrent_sessions' => 1,
    'retry_attempts' => 3,
    'retry_delay_base' => 3000,
    'session_rotation_frequency' => 10
];
```

## Commands & Usage

### 1. Basic High-Demand Scraping
```bash
# Scrape all high-demand sports tickets
php artisan tickets:scrape --high-demand

# Manchester United specific scraping
php artisan tickets:scrape --manchester-united --max-price=500

# Custom keyword scraping with high priority
php artisan tickets:scrape --keywords="Champions League Final" --max-price=1000
```

### 2. Platform-Specific Scraping
```bash
# Scrape specific platform
php artisan tickets:scrape --platform=real_madrid --keywords="El Clasico"

# Multiple platforms
php artisan tickets:scrape --platform=stubhub,ticketmaster --high-demand
```

### 3. Advanced Options
```bash
# With alerts checking
php artisan tickets:scrape --high-demand --check-alerts

# Limit results
php artisan tickets:scrape --manchester-united --limit=20

# Show high-demand sports overview
php artisan tickets:show-high-demand-sports --max-price=800 --limit=50
```

## API Integration

### 1. Start High-Demand Scraping
```php
POST /api/scraping/start-scraping

{
    "platforms": ["real_madrid", "barcelona", "stubhub"],
    "keywords": "El Clasico",
    "priority": "high",
    "max_price": 800,
    "limit": 100
}
```

### 2. Get High-Demand Tickets
```php
GET /api/scraping/tickets?is_high_demand=true&sort=scraped_at&direction=desc
```

### 3. Monitor Specific Event
```javascript
// WebSocket subscription for real-time updates
const ws = new WebSocket('ws://hdtickets.local:6001');
ws.send(JSON.stringify({
    event: 'monitor-high-demand',
    data: {
        keywords: 'El Clasico',
        platforms: ['real_madrid', 'barcelona']
    }
}));
```

## Best Practices

### 1. Timing Strategy
```php
// Pre-sale monitoring
$preSaleEvents = [
    'El Clasico' => [
        'announcement_date' => '2025-02-15',
        'pre_sale_date' => '2025-02-20 10:00:00',
        'general_sale_date' => '2025-02-22 10:00:00'
    ]
];

// Start monitoring 24-48 hours before sale
$monitoringStart = Carbon::parse($preSaleDate)->subHours(48);
```

### 2. Session Management
```php
// Pre-warm sessions
$highDemandScraper->preWarmSessions([
    'real_madrid' => 5, // 5 concurrent sessions
    'barcelona' => 3,
    'stubhub' => 4
]);

// Rotate sessions every N requests
$sessionRotationFrequency = 3; // requests per session
```

### 3. Queue Handling
```php
// Detect queue systems
if ($scraper->detectQueuePage($html)) {
    // Switch to queue monitoring mode
    return $scraper->handleQueueScraping($platform, $criteria, 'aggressive');
}

// Queue bypass attempts
$bypassTechniques = [
    'direct_url_access',
    'cached_session_tokens',
    'alternative_entry_points',
    'mobile_vs_desktop_switching'
];
```

### 4. Price Monitoring
```php
// Track price changes for high-demand events
$priceTracking = [
    'interval' => 60, // seconds
    'thresholds' => [
        'price_drop' => 10, // %
        'availability_increase' => 5 // tickets
    ],
    'alerts' => [
        'email' => true,
        'webhook' => true,
        'push_notification' => true
    ]
];
```

## Monitoring & Alerts

### 1. Set Up High-Demand Alerts
```php
// Create alert for El Clasico
$alert = TicketAlert::create([
    'name' => 'El Clasico 2025',
    'keywords' => 'Real Madrid vs Barcelona, El Clasico',
    'platforms' => ['real_madrid', 'barcelona', 'stubhub'],
    'max_price' => 500,
    'currency' => 'EUR',
    'status' => 'active',
    'priority' => 'extreme',
    'filters' => [
        'availability' => ['available', 'limited'],
        'demand_level' => ['high', 'very_high', 'extreme']
    ]
]);
```

### 2. Real-Time Monitoring Dashboard
```javascript
// Subscribe to high-demand events
Echo.channel('high-demand-tickets')
    .listen('HighDemandTicketFound', (e) => {
        console.log('High-demand ticket found:', e.ticket);
        
        if (e.ticket.demand_level === 'extreme') {
            // Trigger immediate alert
            showUrgentNotification(e.ticket);
            
            // Auto-purchase if configured
            if (shouldAutoPurchase(e.ticket)) {
                attemptPurchase(e.ticket);
            }
        }
    });
```

### 3. Queue Position Monitoring
```php
// Monitor queue position and estimated wait time
$queueStatus = $scraper->monitorQueuePosition('real_madrid');

if ($queueStatus['position'] < 100) {
    Log::info('Near front of queue', $queueStatus);
    
    // Prepare for ticket availability
    $scraper->prepareForTicketRelease();
}
```

## Queue Management

### 1. Queue Detection
```php
$queueIndicators = [
    'queue-it.net',           // Queue-it service
    'waiting room',           // Generic waiting room
    'virtual queue',          // Virtual queue system
    'you are in line',        // User-facing message
    'estimated wait time',    // Wait time display
    'queue position'          // Position indicator
];

foreach ($queueIndicators as $indicator) {
    if (str_contains(strtolower($html), $indicator)) {
        return $scraper->handleQueueScraping($platform, $criteria);
    }
}
```

### 2. Queue Bypass Techniques
```php
// Technique 1: Direct URL access
$directUrls = [
    'real_madrid' => 'https://www.realmadrid.com/entradas/partido/{match_id}',
    'barcelona' => 'https://www.fcbarcelona.com/es/entradas/{event_id}'
];

// Technique 2: Mobile vs Desktop switching
$mobileUserAgents = [
    'iPhone Safari',
    'Android Chrome',
    'iPad Safari'
];

// Technique 3: Cached session tokens
$cachedSessions = Cache::get("valid_sessions_{$platform}", []);
```

### 3. Intelligent Queue Waiting
```php
class QueueWaitStrategy
{
    public function waitIntelligently($platform, $queueInfo)
    {
        $waitTime = $queueInfo['estimated_wait'];
        $position = $queueInfo['position'];
        
        // Dynamic refresh intervals based on position
        if ($position < 50) {
            $refreshInterval = 5; // seconds - very frequent
        } elseif ($position < 500) {
            $refreshInterval = 15; // seconds - frequent
        } else {
            $refreshInterval = 60; // seconds - normal
        }
        
        // Keep session alive with minimal requests
        $this->maintainSession($platform, $refreshInterval);
        
        return $this->checkQueueProgress($platform);
    }
}
```

## Anti-Detection

### 1. Advanced Session Management
```php
$antiDetectionConfig = [
    'rotate_user_agents' => true,
    'randomize_delays' => [500, 3000], // ms range
    'browser_fingerprinting' => 'randomize',
    'proxy_rotation' => true,
    'javascript_execution' => true,
    'cookie_management' => 'advanced',
    'request_headers' => 'realistic'
];
```

### 2. Human-Like Behavior
```php
// Simulate human browsing patterns
$humanBehavior = [
    'page_dwell_time' => [2000, 8000], // ms
    'scroll_simulation' => true,
    'mouse_movement' => true,
    'click_patterns' => 'natural',
    'typing_speed' => [50, 150], // chars per minute
    'break_intervals' => [300, 600] // seconds
];
```

### 3. Captcha Handling
```php
// Captcha solving services integration
$captchaSolvers = [
    '2captcha' => [
        'api_key' => env('2CAPTCHA_API_KEY'),
        'timeout' => 120
    ],
    'anticaptcha' => [
        'api_key' => env('ANTICAPTCHA_API_KEY'),
        'timeout' => 180
    ]
];
```

## Troubleshooting

### Common Issues

#### 1. Rate Limiting
```php
// Symptoms: HTTP 429, request throttling
// Solution: Implement exponential backoff
$retryDelay = 1000 * pow(2, $attemptNumber - 1); // exponential backoff
usleep($retryDelay * 1000);
```

#### 2. Session Expiration
```php
// Symptoms: Redirects to login, session cookies invalid
// Solution: Session refresh mechanism
if ($this->isSessionExpired($response)) {
    $newSession = $this->refreshSession($platform);
    return $this->retryWithNewSession($newSession, $request);
}
```

#### 3. Queue Detection False Positives
```php
// Symptoms: Detecting queues when there aren't any
// Solution: Multi-factor queue detection
$queueConfidence = $this->calculateQueueConfidence([
    'html_indicators' => $htmlScore,
    'response_time' => $responseTimeScore,
    'redirect_patterns' => $redirectScore,
    'javascript_checks' => $jsScore
]);

if ($queueConfidence > 0.7) {
    return $this->handleQueueScraping($platform, $criteria);
}
```

#### 4. JavaScript Challenges
```php
// Symptoms: Cloudflare challenges, bot detection
// Solution: Headless browser with JavaScript execution
$browser = $this->antiDetection->createHeadlessBrowser();
$page = $browser->newPage();
$page->goto($url);
$page->waitForLoadState('networkidle');
$content = $page->content();
```

### Performance Optimization

#### 1. Parallel Processing
```php
// Process multiple platforms simultaneously
$promises = [];
foreach ($platforms as $platform) {
    $promises[] = $this->scrapeAsync($platform, $criteria);
}

$results = Promise::settle($promises)->wait();
```

#### 2. Caching Strategy
```php
// Cache successful session data
Cache::put("session_data_{$platform}", [
    'cookies' => $cookies,
    'headers' => $headers,
    'tokens' => $tokens
], now()->addHours(2));

// Cache ticket data with short TTL for high-demand events
Cache::put("tickets_{$eventId}", $tickets, now()->addMinutes(5));
```

#### 3. Database Optimization
```sql
-- Index for high-demand ticket queries
CREATE INDEX idx_scraped_tickets_high_demand ON scraped_tickets 
(is_high_demand, availability_status, event_date, scraped_at);

-- Index for real-time monitoring
CREATE INDEX idx_scraped_tickets_monitoring ON scraped_tickets 
(platform, search_keyword, scraped_at DESC);
```

## Conclusion

High-demand ticket scraping requires:
1. **Strategic Planning**: Know when tickets go on sale
2. **Technical Excellence**: Robust scraping infrastructure
3. **Real-Time Monitoring**: Instant alerts and notifications
4. **Queue Management**: Intelligent queue handling
5. **Anti-Detection**: Advanced evasion techniques
6. **Rapid Response**: Fast purchase capabilities

The HDTickets system provides all necessary tools for successful high-demand ticket acquisition. Use this guide to implement effective scraping strategies for the most competitive events.

## Support

For additional support:
- Check the logs in `storage/logs/laravel.log`
- Monitor Redis queues: `redis-cli monitor`
- Use the debugging commands: `php artisan tickets:debug-high-demand`
- Review the performance metrics: `GET /api/scraping/statistics`
