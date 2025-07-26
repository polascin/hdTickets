# HDTickets Web Scraper System

A comprehensive, plugin-based web scraping system for sports events ticket platforms.

## Features

- **Plugin-Based Architecture**: Easily extendable with new ticket platforms
- **Built-in Rate Limiting**: Respectful scraping with configurable delays
- **Proxy Support**: Rotate proxies to avoid IP blocks
- **Health Monitoring**: Track plugin performance and success rates
- **Anti-Detection**: User agent rotation, randomized delays, and more
- **Configuration Management**: Flexible configuration per plugin
- **Data Validation**: Ensure scraped data quality
- **Command Line Interface**: Easy testing and monitoring

## Available Platforms

### Currently Implemented:
- **Ticketmaster**: Official API + web scraper with comprehensive event data
- **SeatGeek**: Advanced web scraper with pricing and venue extraction
- **StubHub**: Event search scraper with marketplace data
- **Viagogo**: International marketplace scraper with multi-currency support
- **TickPick**: No-fee marketplace scraper with transparent pricing
- **Eventbrite**: Event discovery platform for local events and workshops
- **Bandsintown**: Concert discovery platform for live music events
- **Manchester United**: Official team site scraper for Old Trafford matches

### Platform Coverage:
- **🎵 Music**: Bandsintown, Ticketmaster, SeatGeek, StubHub, Viagogo, TickPick
- **⚽ Sports**: Ticketmaster, SeatGeek, StubHub, Viagogo, TickPick, Manchester United
- **🎭 Theater**: Ticketmaster, SeatGeek, StubHub, Viagogo, TickPick
- **📅 Local Events**: Eventbrite
- **🌍 Global Coverage**: Viagogo, Ticketmaster, SeatGeek

## Installation

The scraper system is already integrated into your HDTickets Laravel application.

### Dependencies
Ensure you have the following PHP packages:
```bash
composer require guzzlehttp/guzzle
```

## Usage

### Command Line Interface

#### List Available Plugins
```bash
php artisan tickets:scrape-v2 --list
```

#### Test All Plugins
```bash
php artisan tickets:scrape-v2 --test
```

#### Test Specific Plugin
```bash
php artisan tickets:scrape-v2 --test --plugin=stubhub
```

#### Check System Health
```bash
php artisan tickets:scrape-v2 --status
```

#### Run Scraping
```bash
# Scrape with all plugins
php artisan tickets:scrape-v2 --keyword="Manchester United"

# Scrape with specific plugin
php artisan tickets:scrape-v2 --keyword="concerts" --plugin=seatgeek --location="London"
```

## Creating New Plugins

### 1. Create Plugin Class
Create a new file in `app/Services/Scraping/Plugins/` that implements `ScraperPluginInterface`:

```php
<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\ScraperPluginInterface;
use App\Services\ProxyRotationService;

class MyPlatformPlugin implements ScraperPluginInterface
{
    private $enabled = true;
    private $proxyService;
    
    public function __construct(ProxyRotationService $proxyService = null)
    {
        $this->proxyService = $proxyService;
    }
    
    public function getInfo(): array
    {
        return [
            'name' => 'My Platform',
            'description' => 'Scraper for My Platform tickets',
            'version' => '1.0.0',
            'platform' => 'myplatform',
            'capabilities' => ['search_events', 'extract_pricing']
        ];
    }
    
    // Implement other interface methods...
}
```

### 2. Add to Configuration
Edit `config/scraping.php` to include your new plugin:

```php
'enabled_plugins' => [
    'myplatform',
    // ... other plugins
],

'plugins' => [
    'myplatform' => [
        'base_url' => 'https://myplatform.com',
        'rate_limit_seconds' => 2,
        'timeout' => 30,
    ],
    // ... other configurations
]
```

### 3. Test Your Plugin
```bash
php artisan tickets:scrape-v2 --test --plugin=myplatform
```

## Configuration

### Main Configuration File
Edit `config/scraping.php` to customize:

- **Enabled Plugins**: Which scrapers to load
- **Rate Limiting**: Delays between requests
- **Proxy Settings**: Enable/disable proxy rotation
- **Anti-Detection**: User agent rotation, randomized delays
- **Data Processing**: Validation and normalization settings
- **Health Monitoring**: Success rate thresholds

### Environment Variables
Add to your `.env` file:

```bash
# Scraper Settings
SCRAPER_USE_PROXIES=false
SCRAPER_CACHE_RESULTS=true
SCRAPER_CACHE_TTL=60
SCRAPER_DEBUG=false

# Platform-specific settings
SEATGEEK_BASE_URL=https://seatgeek.com
STUBHUB_BASE_URL=https://www.stubhub.com
TICKETMASTER_API_KEY=your_api_key_here
```

## Best Practices

### Legal & Ethical Considerations
- Always check `robots.txt` and terms of service
- Respect rate limits and server resources
- Use official APIs where available
- Be mindful of copyright and data usage rights

### Technical Best Practices
- Implement proper error handling
- Use appropriate delays between requests
- Rotate user agents and headers
- Monitor for IP blocks and CAPTCHAs
- Validate and sanitize scraped data

### Platform-Specific Tips

#### SeatGeek
- Uses modern JavaScript rendering
- Implements bot detection
- Consider using headless browser for complex interactions

#### StubHub
- Has rate limiting
- May require session management
- Price information is dynamically loaded

#### Official Venue Sites
- Often have better structured data
- May offer RSS feeds or APIs
- Usually more reliable than reseller sites

## Architecture

### Core Components

1. **PluginBasedScraperManager**: Main orchestrator
2. **ScraperPluginInterface**: Contract for all plugins
3. **ProxyRotationService**: Handles proxy management
4. **ScrapeTickets Command**: CLI interface

### Data Flow

1. **Initialize**: Load enabled plugins from configuration
2. **Configure**: Apply plugin-specific settings
3. **Execute**: Run scraping based on criteria
4. **Process**: Validate and normalize data
5. **Store**: Save results to database
6. **Monitor**: Track performance and health

### Plugin Lifecycle

1. **Discovery**: Auto-discover plugins in Plugins directory
2. **Registration**: Register with manager
3. **Configuration**: Load settings from config/cache
4. **Execution**: Run scraping logic
5. **Monitoring**: Track success rates and performance

## Monitoring & Health Checks

### Health Status Indicators
- **Healthy (🟢)**: Success rate > 80%
- **Warning (🟡)**: Success rate 50-80%
- **Critical (🔴)**: Success rate < 50%

### Metrics Tracked
- Success rate percentage
- Average response time
- Total runs and results
- Recent errors
- Last execution time

### Troubleshooting Common Issues

#### HTTP 403 Forbidden
- Site blocking automated requests
- Try different user agents
- Implement delays or use proxies
- Consider using headless browser

#### No Results Found
- Check selectors for HTML structure changes
- Verify search parameters
- Test with simple queries first

#### Rate Limiting
- Increase delays between requests
- Implement exponential backoff
- Use proxy rotation

## Future Enhancements

### Planned Features
- Browser automation (Selenium/Playwright)
- Machine learning for price prediction
- Real-time monitoring dashboard
- Webhook notifications
- Distributed scraping across multiple servers

### Plugin Ideas
- Social media platforms (Twitter, Facebook events)
- Venue-specific scrapers
- Secondary market aggregators
- International platforms

## Support

For issues or feature requests related to the scraper system:

1. Check the logs: `storage/logs/laravel.log`
2. Test individual plugins: `php artisan tickets:scrape-v2 --test --plugin=<name>`
3. Review configuration in `config/scraping.php`
4. Monitor health status: `php artisan tickets:scrape-v2 --status`

---

**⚠️ Important**: Always ensure compliance with website terms of service and applicable laws when scraping data. This system is designed for educational and legitimate business purposes only.
