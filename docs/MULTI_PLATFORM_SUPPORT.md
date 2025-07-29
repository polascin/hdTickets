# Multi-Platform Support Documentation

## Overview

The HD Tickets system now supports enhanced multi-platform scraping with data normalization and consistency across different ticket platforms. This implementation provides a unified interface for searching, scraping, and importing ticket data from multiple sources.

## Supported Platforms

- **Ticketmaster** - US-based primary ticket platform
- **StubHub** - Major resale marketplace
- **FunZone** - Slovak ticket platform (specializes in entertainment events)
- **Viagogo** - Global secondary ticket marketplace
- **TickPick** - No-fee ticket marketplace

## Architecture

### Core Components

1. **DataNormalizationService** - Normalizes event data across platforms
2. **MultiPlatformManager** - Coordinates scraping across multiple platforms
3. **BaseWebScrapingClient** - Enhanced base class for web scraping
4. **Platform-specific Clients** - Individual clients for each platform

### Key Features

- **Data Normalization**: Consistent data structure across platforms
- **Deduplication**: Automatic removal of duplicate events
- **Error Handling**: Robust error handling with retry logic
- **Anti-Bot Detection**: Advanced bot detection evasion
- **Rate Limiting**: Platform-specific rate limiting
- **Health Checking**: Monitor platform availability

## Usage

### Command Line Interface

```bash
# Search across all platforms
php artisan search:multi-platform "concert" --location="New York" --limit=20

# With deduplication
php artisan search:multi-platform "sports" --deduplicate

# With health check
php artisan search:multi-platform "theater" --health-check
```

### Programmatic Usage

```php
use App\Services\MultiPlatformManager;
use App\Services\Normalization\DataNormalizationService;

$normalizationService = new DataNormalizationService();
$multiPlatformManager = new MultiPlatformManager($normalizationService);

// Search across all platforms
$results = $multiPlatformManager->searchEventsAcrossPlatforms(
    'concert',
    'Los Angeles',
    25
);

// Deduplicate results
$deduplicated = $multiPlatformManager->deduplicateEvents(
    $results['normalized_events']
);

// Get platform status
$status = $multiPlatformManager->getPlatformsStatus();
```

## Data Normalization

The normalization service ensures consistent data structure:

```php
[
    'id' => 'platform_eventid',
    'platform' => 'ticketmaster',
    'external_id' => 'original_event_id',
    'name' => 'Event Name',
    'description' => 'Event description',
    'date' => '2025-08-15',
    'time' => '20:00:00',
    'timezone' => 'America/New_York',
    'venue' => 'Venue Name',
    'city' => 'City Name',
    'country' => 'Country',
    'price_min' => 25.00,
    'price_max' => 150.00,
    'currency' => 'USD',
    'availability_status' => 'available',
    'platform_specific' => [...],
    'raw_data' => [...]
]
```

## Platform-Specific Features

### FunZone (Slovak Platform)
- **Currency**: EUR
- **Language**: Slovak
- **Specialties**: Entertainment events, concerts, theater
- **Regional Support**: Slovak regions and cities

### StubHub
- **Currency**: USD (primary)
- **Specialties**: Sports, concerts, theater
- **Features**: Ticket classes, zones, section mappings

### Ticketmaster
- **Currency**: USD (primary)
- **Specialties**: All event types
- **Features**: Presale info, verified resale

## Configuration

### Platform Configuration

```php
// Configure individual platforms
$multiPlatformManager->configurePlatform('ticketmaster', [
    'enabled' => true,
    'timeout' => 45,
    'rate_limit' => ['requests' => 5, 'window' => 60]
]);
```

### Rate Limiting

Each platform has specific rate limits:

- **Ticketmaster**: 5 requests/minute
- **StubHub**: 10 requests/minute
- **FunZone**: 10 requests/minute
- **Viagogo**: 5 requests/minute
- **TickPick**: 15 requests/minute

## Health Monitoring

```php
$healthCheck = $multiPlatformManager->performHealthCheck();
```

Returns:
- Overall system health
- Individual platform status
- Response times
- Error messages

## Error Handling

### Exception Types

- `ScrapingDetectedException` - Bot detection triggered
- `RateLimitException` - Rate limit exceeded
- `TimeoutException` - Request timeout
- `TicketPlatformException` - General platform error

### Error Recovery

- Automatic retries with exponential backoff
- Fallback mechanisms
- Graceful degradation
- Comprehensive logging

## Best Practices

### Scraping Guidelines

1. **Respect Rate Limits**: Always use appropriate delays
2. **Use Random User-Agents**: Rotate user agents for anti-detection
3. **Handle Bot Detection**: Implement proper recovery strategies
4. **Monitor Success Rates**: Track selector effectiveness
5. **Log Everything**: Maintain detailed logs for debugging

### Performance Optimization

1. **Use Caching**: Cache search results when appropriate
2. **Parallel Processing**: Consider async processing for multiple platforms
3. **Database Indexing**: Proper indexing for normalized data
4. **Memory Management**: Handle large result sets efficiently

## Extending Support

### Adding New Platforms

1. Create new client extending `BaseWebScrapingClient`
2. Implement required abstract methods
3. Add platform-specific selectors and logic
4. Update `MultiPlatformManager` to include new platform
5. Add normalization rules for the platform

### Example Platform Implementation

```php
class NewPlatformClient extends BaseWebScrapingClient
{
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        // Implementation
    }
    
    public function scrapeEventDetails(string $url): array
    {
        // Implementation
    }
    
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        // Implementation
    }
    
    protected function extractEventFromNode(Crawler $node): array
    {
        // Implementation
    }
    
    protected function extractPrices(Crawler $crawler): array
    {
        // Implementation
    }
}
```

## Troubleshooting

### Common Issues

1. **Bot Detection**
   - Solution: Implement proper delays and user-agent rotation
   - Use proxy rotation if necessary

2. **Rate Limiting**
   - Solution: Respect platform-specific rate limits
   - Implement proper backoff strategies

3. **Selector Changes**
   - Solution: Use multiple selectors with fallbacks
   - Monitor selector effectiveness

4. **Data Inconsistency**
   - Solution: Enhance normalization rules
   - Implement validation checks

### Debugging

```php
// Enable detailed logging
Log::channel('ticket_apis')->debug('Platform search', [
    'platform' => 'ticketmaster',
    'keyword' => 'concert',
    'results' => count($results)
]);
```

## Security Considerations

1. **Rate Limiting**: Prevent overwhelming external platforms
2. **User Agent Rotation**: Avoid detection patterns
3. **Error Handling**: Don't expose internal errors
4. **Data Validation**: Validate all scraped data
5. **Access Control**: Limit scraping capabilities to authorized users

## Performance Metrics

Monitor these key metrics:

- **Success Rate**: Percentage of successful scrapes
- **Response Time**: Average response times per platform
- **Error Rate**: Frequency of errors by type
- **Data Quality**: Completeness of normalized data
- **Deduplication Effectiveness**: Percentage of duplicates found

## Future Enhancements

1. **Machine Learning**: Improve deduplication with ML algorithms
2. **Real-time Updates**: Implement real-time price monitoring
3. **Enhanced Anti-Detection**: More sophisticated bot evasion
4. **API Integrations**: Official API support where available
5. **Mobile App Support**: Extend to mobile scraping capabilities
