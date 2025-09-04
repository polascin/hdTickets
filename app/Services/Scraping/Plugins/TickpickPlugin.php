<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;

class TickpickPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception("{$this->pluginName} plugin is disabled");
        }

        Log::info("Starting {$this->pluginName} scraping", $criteria);

        try {
            $this->applyRateLimit($this->platform);

            $searchUrl = $this->buildSearchUrl($criteria);
            $html = $this->makeHttpRequest($searchUrl);
            $events = $this->parseSearchResults($html);
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info("{$this->pluginName} scraping completed", [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error("{$this->pluginName} scraping failed", [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get last minute deals
     */
    public function getLastMinuteDeals(array $criteria = []): array
    {
        $criteria['sort_by'] = 'date';

        // Add logic for last minute deals
        return $this->scrape($criteria);
    }

    /**
     * Get events by sport
     */
    public function getEventsBySport(string $sport, array $criteria = []): array
    {
        $sportCategories = [
            'football'   => 'nfl',
            'basketball' => 'nba',
            'baseball'   => 'mlb',
            'hockey'     => 'nhl',
            'soccer'     => 'mls',
            'college'    => 'ncaa',
        ];

        $category = $sportCategories[strtolower($sport)] ?? strtolower($sport);
        $criteria['category'] = $category;

        return $this->scrape($criteria);
    }

    /**
     * Get events by city
     */
    public function getEventsByCity(string $city, array $criteria = []): array
    {
        $criteria['city'] = $city;

        return $this->scrape($criteria);
    }

    /**
     * Get concert events
     */
    public function getConcertEvents(array $criteria = []): array
    {
        $criteria['category'] = 'concerts';

        return $this->scrape($criteria);
    }

    /**
     * Get sports events
     */
    public function getSportsEvents(array $criteria = []): array
    {
        $criteria['category'] = 'sports';

        return $this->scrape($criteria);
    }

    /**
     * Get Broadway shows
     */
    public function getBroadwayShows(array $criteria = []): array
    {
        $criteria['category'] = 'theater';
        $criteria['city'] = 'New York';

        return $this->scrape($criteria);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'TickPick';
        $this->platform = 'tickpick';
        $this->description = 'TickPick - No-fee ticket marketplace for sports, concerts, and theater';
        $this->baseUrl = 'https://www.tickpick.com';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->language = 'en-US';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'sports_events',
            'concerts',
            'theater',
            'comedy',
            'no_fees',
            'nfl',
            'nba',
            'mlb',
            'nhl',
            'mls',
            'ncaa',
            'broadway',
            'festivals',
            'last_minute_deals',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'city',
            'date_range',
            'category',
            'venue',
            'team',
            'artist',
            'price_range',
            'sort_by',
        ];
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['search'] = urlencode($criteria['keyword']);
        }

        if (! empty($criteria['city'])) {
            $params['city'] = urlencode($criteria['city']);
        }

        if (! empty($criteria['category'])) {
            $params['type'] = urlencode($criteria['category']);
        }

        if (! empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['start_date'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['end_date'] = $criteria['date_range']['end'];
            }
        }

        if (! empty($criteria['sort_by'])) {
            $params['sort'] = $criteria['sort_by'];
        }

        $queryString = http_build_query($params);

        return $this->baseUrl . '/search?' . $queryString;
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.event-item, .EventItem, .ticket-listing, [data-testid="event-item"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse TickPick event item', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse TickPick search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event item
     */
    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-title, .EventItem-title, h3, .listing-title');
            $venue = $this->extractText($node, '.venue-name, .EventItem-venue, .venue, .listing-venue');
            $location = $this->extractText($node, '.location, .EventItem-location, .city-state');
            $date = $this->extractText($node, '.date, .EventItem-date, time, .listing-date');
            $time = $this->extractText($node, '.time, .EventItem-time, .event-time');
            $priceText = $this->extractText($node, '.price, .EventItem-price, .starting-price, .no-fee-price');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return NULL;
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Parse time
            $eventTime = $this->parseTime($time);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : NULL;

            return [
                'title'        => trim($title),
                'venue'        => trim($venue),
                'location'     => trim($location ?: $venue),
                'date'         => $eventDate,
                'time'         => $eventTime,
                'price'        => $price,
                'currency'     => $this->currency,
                'url'          => $fullUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => 'event',
                'availability' => 'available',
                'no_fees'      => TRUE, // TickPick's main selling point
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse TickPick event item', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse price from text (TickPick shows no-fee prices)
     */
    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return NULL;
        }

        // Extract numeric price
        if (preg_match('/\$(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * Parse time from text
     */
    protected function parseTime(string $timeText): ?string
    {
        if (empty($timeText)) {
            return NULL;
        }

        try {
            // Try to parse time
            $time = Carbon::parse($timeText);

            return $time->format('H:i');
        } catch (Exception $e) {
            Log::debug('Failed to parse TickPick time', ['time' => $timeText, 'error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate(string $dateText): ?string
    {
        if (empty($dateText)) {
            return NULL;
        }

        try {
            // Try common date formats
            $date = Carbon::parse($dateText);

            return $date->format('Y-m-d');
        } catch (Exception $e) {
            Log::debug('Failed to parse TickPick date', ['date' => $dateText, 'error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Build full URL
     */
    protected function buildFullUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Required abstract methods from BaseScraperPlugin

    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/search';
    }

    protected function getEventNameSelectors(): string
    {
        return '.event-title, .EventItem-title, h3, .listing-title';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .EventItem-date, time, .listing-date';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue-name, .EventItem-venue, .venue, .listing-venue';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .EventItem-price, .starting-price, .no-fee-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.status, .availability, .sold-out';
    }
}
