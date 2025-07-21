# Platform Scraping Guide

This document provides detailed information about scraping each ticket platform, including selectors, platform-specific quirks, limitations, and best practices.

## Table of Contents

1. [General Scraping Guidelines](#general-scraping-guidelines)
2. [FunZone Platform](#funzone-platform)
3. [StubHub Platform](#stubhub-platform)
4. [Viagogo Platform](#viagogo-platform)
5. [TickPick Platform](#tickpick-platform)
6. [Ticketmaster Platform](#ticketmaster-platform)
7. [Anti-Bot Detection](#anti-bot-detection)
8. [Performance Optimization](#performance-optimization)

## General Scraping Guidelines

### Rate Limiting
Each platform has different rate limiting requirements:

- **Minimum delay between requests**: 1-3 seconds
- **User-Agent rotation**: Required for all platforms
- **Session management**: Maintain cookies across requests
- **Proxy rotation**: Recommended for high-volume scraping

### Error Handling
- Graceful degradation when selectors fail
- Retry logic with exponential backoff
- Fallback to alternative selectors
- Bot detection recovery strategies

## FunZone Platform

### Overview
- **Country**: Slovakia
- **Language**: Slovak (sk)
- **Currency**: EUR (€)
- **Character encoding**: UTF-8
- **Base URL**: https://www.funzone.sk

### CSS Selectors

#### Search Results Page
```css
/* Primary event containers */
.event-card                    /* Main event card container */
.event-item                    /* Alternative event container */
.listing                       /* Generic listing container */

/* Event details within cards */
.event-title                   /* Event name/title */
.event-name                    /* Alternative title selector */
h3, h4                        /* Fallback title selectors */

/* Date and time */
.date                         /* Event date */
.event-date                   /* Alternative date selector */
time                          /* HTML5 time element */
.datum                        /* Slovak date selector */

/* Venue information */
.venue                        /* Venue name */
.miesto                       /* Slovak venue selector */
.location                     /* Location/address */
.mesto                        /* Slovak city selector */

/* Pricing */
.price                        /* Price elements */
.cena                         /* Slovak price selector */
.price-info                   /* Price container */

/* Links and URLs */
a[href*="/event/"]            /* Event detail links */
a[href*="/show/"]             /* Alternative event links */
```

#### Event Details Page
```css
/* Main content */
.event-detail                 /* Main event detail container */
.event-info                   /* Event information section */

/* Structured data */
script[type="application/ld+json"]  /* JSON-LD data */

/* Ticket listings */
.ticket-listing               /* Individual ticket listing */
.ticket-row                   /* Alternative ticket row */
.cenova-kategoria            /* Slovak price category */

/* Venue details */
.venue-details               /* Venue information section */
.capacity                    /* Venue capacity */
.kapacita                    /* Slovak capacity selector */
```

### Platform-Specific Quirks

#### Language Considerations
- Content is primarily in Slovak
- Date formats: `dd.mm.yyyy` or `d. m. yyyy`
- Time formats: 24-hour format (e.g., `20:00`)
- Slovak month names: január, február, marec, etc.

#### Price Handling
- Currency: Euro (€) symbol placement can vary
- Formats: `€35`, `35€`, `35,50 €`
- Decimal separator: comma (,) instead of period (.)

#### Date Parsing Patterns
```php
// Common Slovak date formats
'd.m.Y H:i'     // 15.1.2025 20:00
'd. m. Y H:i'   // 15. 1. 2025 20:00  
'j.n.Y'         // 5.3.2025
```

#### Regional Information
- Bratislavský kraj (Bratislava Region)
- Košický kraj (Košice Region)
- Venue types: divadlo, štadión, aréna, hala, klub

### Limitations
- No official API available
- Limited search filters
- Captcha protection on high traffic
- IP blocking from outside Slovakia (use Slovak proxies)
- SSL certificate issues with some domains

### Error Indicators
```html
<!-- Bot detection patterns -->
"Prístup zamietnutý"          <!-- Access denied -->
"Podozrivý prenos"           <!-- Suspicious traffic -->
"Overenie identity"          <!-- Identity verification -->
```

## StubHub Platform

### Overview
- **Country**: United States (Global presence)
- **Language**: English
- **Currency**: USD ($) primary, multiple currencies supported
- **Base URL**: https://www.stubhub.com

### CSS Selectors

#### Search Results Page
```css
/* Event containers */
.EventCard                    /* Primary event card */
.SearchResultCard            /* Alternative result card */
.event-card                  /* Generic event card */

/* Event information */
.event-name                  /* Event title */
.title                       /* Alternative title */
h3, h4                      /* Fallback titles */

/* Date and venue */
.date                        /* Event date */
.event-date                  /* Alternative date */
.venue                       /* Venue name */
.venue-name                  /* Alternative venue */
.location                    /* Location/city */

/* Pricing */
.price                       /* Price display */
.pricing                     /* Price container */
.price-range                 /* Price range display */
.price-label                 /* Price label (e.g., "starting at") */

/* Ticket information */
.ticket-count               /* Number of tickets available */
.availability               /* Availability text */
```

#### Event Details Page
```css
/* Main content */
.event-page                  /* Main event container */
.event-header               /* Event header section */
.event-title                /* Main event title */

/* Ticket listings */
.listing                    /* Individual ticket listing */
.ticket-listing            /* Alternative listing */
.listing-info              /* Listing details */

/* Venue and location */
.venue-address             /* Venue address */
.venue-info                /* Venue information section */

/* Pricing details */
.section                   /* Seating section */
.row                       /* Seating row */
.seats                     /* Seat numbers */
.total                     /* Total price with fees */
```

### Platform-Specific Quirks

#### Anti-Bot Measures
- Cloudflare protection (very aggressive)
- CAPTCHA challenges frequent
- IP-based rate limiting
- JavaScript-heavy pages requiring rendering

#### Price Formatting
- Multiple currencies: USD, CAD, GBP, EUR
- Fee structure: base price + service fees
- Dynamic pricing based on demand

#### Search Behavior
- Fuzzy matching for event names
- Location auto-suggestion
- Date range filtering available

### Limitations
- Aggressive bot detection (Cloudflare)
- JavaScript-rendered content requires browser automation
- API access requires partnership agreement
- Geo-blocking for certain events
- Price changes rapidly (every few minutes)

### Error Indicators
```html
<!-- Cloudflare bot detection -->
<div class="cf-error-container">
<h1>Sorry, you have been blocked</h1>

<!-- Rate limiting -->
<h1>429 - Too Many Requests</h1>

<!-- JavaScript required -->
"Please enable JavaScript to continue"
```

## Viagogo Platform

### Overview
- **Country**: Global (UK-based)
- **Languages**: Multiple (English primary)
- **Currencies**: Multiple (GBP, USD, EUR, etc.)
- **Base URL**: https://www.viagogo.com

### CSS Selectors

#### Search Results
```css
/* Event containers */
.event-card                  /* Main event card */
article[class*="event"]     /* Article-based events */

/* Event information */
.event-title                /* Event title link */
.event-name                 /* Alternative title */

/* Date and location */
.event-date                 /* Event date/time */
time                        /* HTML5 time element */
.venue                      /* Venue name */
.city                       /* City name */

/* Pricing */
.price-from                 /* Starting price */
.currency                   /* Currency indicator */
.price-range               /* Price range display */
```

### Platform-Specific Quirks

#### Multi-Currency Support
- Dynamic currency conversion
- Geo-based currency selection
- Exchange rate fluctuations affect pricing

#### Geographic Restrictions
- Content varies by location
- VPN detection and blocking
- Region-specific inventory

#### Event Categories
- Strong categorization system
- Sports vs entertainment vs theater
- Subcategory filtering available

### Limitations
- Sophisticated bot detection
- Geographic content restrictions  
- Currency conversion complexity
- Legal restrictions in some countries

## TickPick Platform

### Overview
- **Country**: United States
- **Language**: English
- **Currency**: USD ($)
- **Unique Feature**: No-fee marketplace
- **Base URL**: https://www.tickpick.com

### CSS Selectors

#### Search Results
```css
/* Event containers */
.event-listing              /* Main event listing */
.listing-card              /* Alternative container */

/* Event details */
.listing-title             /* Event title */
.event-name                /* Alternative title */

/* Date and venue */
.event-date                /* Event date/time */
.venue-name                /* Venue name */
.event-location            /* Location/city */

/* Pricing */
.lowest-price              /* Lowest available price */
.price-range               /* Price range */
.no-fees                   /* No-fee indicator */

/* Availability */
.listing-count             /* Number of available listings */
```

### Platform-Specific Quirks

#### No-Fee Model
- Prices displayed include all costs
- No service fees or processing fees
- Competitive pricing due to fee structure

#### Inventory Management
- Real-time inventory updates
- Seller-based availability
- Quality scoring for listings

### Limitations
- Smaller inventory compared to major platforms
- Limited international events
- Fewer filtering options
- Basic search functionality

## Ticketmaster Platform

### Overview
- **Country**: United States (Global presence)  
- **Language**: English (multi-language sites)
- **Currency**: Multiple (USD primary)
- **Base URL**: https://www.ticketmaster.com

### CSS Selectors

#### Search Results
```css
/* Event containers */
.event-tile                 /* Main event tile */
.search-result-item        /* Alternative container */

/* Event information */
.event-name                /* Event title */
.event-title               /* Alternative title */

/* Date and venue */
.event-date                /* Event date */
.event-time                /* Event time */
.venue-name                /* Venue name */
.venue-location           /* Venue location */

/* Pricing and availability */
.price-range               /* Price range display */
.event-status              /* Event status (on sale, sold out) */
.presale-info             /* Presale information */
```

### Platform-Specific Quirks

#### Complex Event States
- Presale vs public sale
- Multiple sale phases
- Verified resale marketplace
- Official vs resale tickets

#### Anti-Bot Measures
- Queue-it system for popular events
- Verified fan requirements
- Mobile app preference
- Geographic restrictions

#### API Integration
- Discovery API available
- Authentication required
- Rate limiting enforced
- Webhook support

### Limitations
- Strict bot detection
- Queue system delays
- Limited resale inventory access
- Complex authentication requirements

## Anti-Bot Detection

### Common Detection Methods

#### Cloudflare Protection
```html
<!-- Detection indicators -->
<div class="cf-error-container">
<script>window._cf_chl_opt</script>
<!-- Cloudflare challenge page -->
```

#### JavaScript Challenges
- Browser fingerprinting
- Mouse movement tracking
- Typing pattern analysis
- Canvas fingerprinting

#### Rate Limiting Indicators
```http
HTTP/1.1 429 Too Many Requests
Retry-After: 300
```

### Evasion Strategies

#### Header Rotation
```php
$headers = [
    'User-Agent' => $this->getRandomUserAgent(),
    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language' => 'en-US,en;q=0.5',
    'Accept-Encoding' => 'gzip, deflate',
    'DNT' => '1',
    'Connection' => 'keep-alive',
    'Sec-Fetch-Dest' => 'document',
    'Sec-Fetch-Mode' => 'navigate',
    'Sec-Fetch-Site' => 'none',
];
```

#### Session Management
- Maintain cookies across requests
- Respect session timeouts
- Handle session invalidation gracefully

#### Request Patterns
- Random delays between requests (1-5 seconds)
- Vary request timing patterns
- Avoid predictable sequences

## Performance Optimization

### Caching Strategies

#### Response Caching
```php
// Cache search results for 5 minutes
Cache::remember("search_{$platform}_{$hash}", 300, function() {
    return $this->performSearch($criteria);
});
```

#### Database Indexes
```sql
-- Platform-specific indexes
CREATE INDEX idx_tickets_platform_date ON tickets(platform, event_date);
CREATE INDEX idx_tickets_platform_location ON tickets(platform, location);
CREATE INDEX idx_tickets_external_id ON tickets(external_id);
```

#### Selector Optimization
- Test selectors regularly for effectiveness
- Maintain fallback selector arrays
- Monitor selector success rates
- Update selectors when sites change

### Monitoring and Alerting

#### Success Rate Tracking
```php
// Track selector effectiveness
$this->trackSelectorEffectiveness($selector, $successful, $platform);
```

#### Performance Metrics
- Response times per platform
- Success rates over time
- Error frequency analysis
- Bot detection incidents

#### Automated Testing
- Daily selector validation
- Platform availability checks
- Performance regression detection
- Error pattern recognition

### Scalability Considerations

#### Horizontal Scaling
- Multiple scraping workers
- Platform-specific queues
- Load distribution strategies
- Failover mechanisms

#### Resource Management
- Memory usage optimization
- Connection pooling
- Request timeout handling
- Graceful shutdown procedures
