# Football Club Stores Integration

This document describes the Football Club Official Stores integration for ticket scraping from major European football clubs.

## Overview

The `FootballClubStoresService` provides ticket extraction capabilities from official club stores across major European leagues including:

- **Premier League** (England): Arsenal, Chelsea, Liverpool, Manchester United, Manchester City, Tottenham
- **La Liga** (Spain): Real Madrid, Barcelona, Atlético Madrid  
- **Serie A** (Italy): Juventus, AC Milan, Inter Milan
- **Bundesliga** (Germany): Bayern Munich, Borussia Dortmund
- **Ligue 1** (France): Paris Saint-Germain

## Features

### Dual Extraction Method
- **API-First Approach**: Attempts to use official club APIs when available
- **Web Scraping Fallback**: Falls back to HTML parsing when APIs are unavailable
- **Caching**: Results are cached for 15 minutes to reduce server load

### Multi-Language Support
- Automatic language detection based on club country
- Appropriate currency mapping (GBP for England, EUR for continental Europe)
- Localized request headers for better compatibility

### Anti-Detection Measures
- Utilizes `AntiDetectionTrait` for sophisticated bot detection avoidance
- Randomized user agents and headers
- Human-like browsing patterns with delays
- European-specific detection patterns

## Installation & Setup

### 1. Service Registration
The service extends `BasePlatformService` and is automatically available for dependency injection.

### 2. Database Requirements
Ensure your `scraped_tickets` table supports the platform name `football_clubs`.

### 3. Command Usage
Use the dedicated Artisan command for ticket import:

```bash
# Import from specific clubs
php artisan football:import-tickets --clubs=arsenal,chelsea

# Import from all supported clubs
php artisan football:import-tickets --all

# Filter by league
php artisan football:import-tickets --league="Premier League"

# Filter by country  
php artisan football:import-tickets --country=Spain

# Dry run to preview results
php artisan football:import-tickets --clubs=real_madrid --dry-run

# Filter by date range
php artisan football:import-tickets --all --date-from=2024-03-01 --date-to=2024-04-30
```

## API Structure

### Search Tickets
```php
$service = new FootballClubStoresService();
$results = $service->searchTickets(['arsenal', 'chelsea'], [
    'date_from' => '2024-03-01',
    'date_to' => '2024-03-31',
    'competition' => 'Premier League'
]);
```

### Import Tickets
```php
$results = $service->importTickets(['real_madrid', 'barcelona'], $filters);
```

### Get Statistics
```php
$stats = $service->getStatistics();
// Returns: platform stats, availability rates, league breakdown, etc.
```

### Get Supported Clubs
```php
$clubs = $service->getSupportedClubs();
// Returns: array of all supported clubs with metadata
```

## Response Format

### Search Results
```json
{
    "success": true,
    "clubs_searched": 2,
    "successful_searches": 2,
    "results": {
        "arsenal": {
            "success": true,
            "club": "Arsenal FC",
            "league": "Premier League",
            "country": "England",
            "total_fixtures": 5,
            "fixtures": [
                {
                    "id": "arsenal_vs_chelsea_001",
                    "opponent": "Chelsea",
                    "competition": "Premier League",
                    "venue": "Emirates Stadium",
                    "date": "2024-03-15 15:00:00",
                    "ticket_categories": [
                        {
                            "category": "Lower Tier",
                            "price": 65.00,
                            "available": true,
                            "restrictions": [],
                            "seat_type": "standard"
                        }
                    ]
                }
            ]
        }
    },
    "errors": []
}
```

## Club-Specific Implementation

### API Parsers
Each major club has a dedicated API parser:
- `parseArsenalApi()` - Arsenal FC specific format
- `parseChelseaApi()` - Chelsea FC specific format  
- `parseRealMadridApi()` - Real Madrid specific format
- `parseBarcelonaApi()` - Barcelona specific format
- `parseGenericApi()` - Fallback for unknown formats

### Web Scraping Selectors
Club-specific CSS selectors are defined for web scraping fallback:
```php
$selectors = [
    'arsenal' => [
        'fixture_container' => '//div[contains(@class, "fixture-item")]',
        'title' => './/h3[@class="fixture-title"]',
        'date' => './/time[@class="fixture-date"]',
        'tickets' => './/div[@class="ticket-info"]'
    ]
];
```

## Data Extraction

### Fixture Information
- **Match Details**: Home team, opponent, venue, date/time
- **Competition**: League, cup competition identification
- **Ticket Categories**: Section names, pricing, availability

### Ticket Categorization
Automatic ticket type classification:
- **Standard**: General admission, regular seating
- **Premium**: VIP boxes, premium seating
- **Hospitality**: Corporate packages, hospitality suites
- **Season Ticket**: Member-only, season ticket holder access

### Price Extraction
- Multi-currency support (GBP, EUR)
- Price range detection (from £25, £25-£50)
- Handling of sold-out/unavailable tickets

## Error Handling

### Graceful Degradation
- API failures automatically fall back to web scraping
- Individual club failures don't affect other clubs
- Comprehensive error logging and reporting

### Rate Limiting
- Built-in delays between requests
- Respect for robots.txt and rate limits
- Automatic retry mechanisms for temporary failures

## Testing

### Unit Tests
Comprehensive test suite covering:
- Supported clubs verification
- API response parsing
- Error handling scenarios
- Currency and language mapping
- Ticket categorization logic

### Mock Data
HTTP facade mocking for testing API responses without hitting live endpoints.

## Monitoring & Statistics

### Platform Statistics
- Total tickets in database
- Availability rates by club/league
- Last update timestamps
- Success/failure rates

### Logging
- Detailed error logging with context
- Performance metrics tracking
- Bot detection event logging

## Compliance & Ethics

### Rate Limiting
Respectful scraping practices with appropriate delays and caching.

### Data Usage
Only publicly available ticket information is extracted.

### Terms of Service
Ensure compliance with individual club website terms of service.

## Extending the Service

### Adding New Clubs
1. Add club configuration to `$clubStores` array
2. Implement club-specific API parser if needed
3. Add web scraping selectors for fallback
4. Update tests and documentation

### Custom Filters
The service supports extensible filtering options through the `$filters` parameter.

### Integration Points
- Webhook notifications for new tickets
- Price change monitoring
- Availability alerts
- Integration with ticket alert system

## Command Line Examples

```bash
# Get help
php artisan football:import-tickets --help

# Import all Premier League clubs
php artisan football:import-tickets --league="Premier League" --dry-run

# Import specific clubs with date range
php artisan football:import-tickets --clubs=arsenal,chelsea --date-from=2024-03-01

# Verbose output with statistics
php artisan football:import-tickets --all --verbose

# Country-specific import  
php artisan football:import-tickets --country=Spain --competition="Champions League"
```

## Performance Considerations

### Caching Strategy
- 15-minute cache for search results
- Longer cache for club configuration
- Cache invalidation on data updates

### Resource Usage
- Memory-efficient DOM parsing
- Streaming for large datasets
- Configurable request timeouts

### Scalability
- Horizontal scaling support
- Queue-based processing for large imports
- Distributed caching options

## Troubleshooting

### Common Issues
1. **Club API Changes**: Monitor logs for parsing failures
2. **Rate Limiting**: Increase delays if getting blocked
3. **Locale Issues**: Verify language/currency mapping
4. **Date Parsing**: Check date format compatibility

### Debug Mode
Enable verbose logging with `--verbose` flag for detailed debugging information.
