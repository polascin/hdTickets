<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;

class SeatgeekPlugin extends BaseScraperPlugin
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
     * Get events by category
     */
    public function getEventsByCategory(string $category, array $criteria = []): array
    {
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
     * Get sports events
     */
    public function getSportsEvents(array $criteria = []): array
    {
        $criteria['category'] = 'sports';

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
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'SeatGeek';
        $this->platform = 'seatgeek';
        $this->description = 'SeatGeek - Major ticket platform for sports, concerts, and theater';
        $this->baseUrl = 'https://seatgeek.com';
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
            'nfl',
            'nba',
            'mlb',
            'nhl',
            'mls',
            'ncaa',
            'broadway',
            'festivals',
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
            $params['q'] = urlencode($criteria['keyword']);
        }

        if (! empty($criteria['city'])) {
            $params['metro'] = urlencode($criteria['city']);
        }

        if (! empty($criteria['category'])) {
            $params['taxonomies.name'] = urlencode($criteria['category']);
        }

        if (! empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['datetime_local.gte'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['datetime_local.lte'] = $criteria['date_range']['end'];
            }
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
            $crawler->filter('.event-tile, .EventTile, [data-testid="event-tile"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventTile($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse event tile', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse SeatGeek search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event tile
     */
    protected function parseEventTile(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-title, .EventTile-title, h3');
            $venue = $this->extractText($node, '.venue-name, .EventTile-venue, .venue');
            $date = $this->extractText($node, '.date, .EventTile-date, time');
            $priceText = $this->extractText($node, '.price, .EventTile-price, .lowest-price');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return NULL;
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : NULL;

            return [
                'title'        => trim($title),
                'venue'        => trim($venue),
                'location'     => trim($venue), // SeatGeek uses venue as location
                'date'         => $eventDate,
                'time'         => NULL, // Extract if available
                'price'        => $price,
                'currency'     => $this->currency,
                'url'          => $fullUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => 'event',
                'availability' => 'available',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse SeatGeek event tile', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse price from text
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
            Log::debug('Failed to parse SeatGeek date', ['date' => $dateText, 'error' => $e->getMessage()]);

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
        return '.event-title, .EventTile-title, h3';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .EventTile-date, time';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue-name, .EventTile-venue, .venue';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .EventTile-price, .lowest-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.status, .availability, .sold-out';
    }
}
