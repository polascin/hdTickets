<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;

class ViagogoPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
    #[Override]
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
     * Get events by sport
     */
    public function getEventsBySport(string $sport, array $criteria = []): array
    {
        $sportCategories = [
            'football'   => 'american-football',
            'soccer'     => 'football',
            'basketball' => 'basketball',
            'baseball'   => 'baseball',
            'hockey'     => 'ice-hockey',
            'tennis'     => 'tennis',
            'golf'       => 'golf',
            'rugby'      => 'rugby',
            'cricket'    => 'cricket',
            'formula1'   => 'motorsports',
        ];

        $category = $sportCategories[strtolower($sport)] ?? strtolower($sport);
        $criteria['category'] = $category;

        return $this->scrape($criteria);
    }

    /**
     * Get events by country
     */
    public function getEventsByCountry(string $country, array $criteria = []): array
    {
        $criteria['country'] = $country;

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
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Viagogo';
        $this->platform = 'viagogo';
        $this->description = 'Viagogo - Global ticket marketplace for sports, concerts, and theater';
        $this->baseUrl = 'https://www.viagogo.com';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->language = 'en-US';
        $this->rateLimitSeconds = 3; // Viagogo is stricter
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
            'festivals',
            'football',
            'basketball',
            'tennis',
            'formula1',
            'soccer',
            'rugby',
            'cricket',
            'golf',
            'international_events',
            'olympics',
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
            'country',
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
            $params['q'] = urlencode((string) $criteria['keyword']);
        }

        if (! empty($criteria['city'])) {
            $params['city'] = urlencode((string) $criteria['city']);
        }

        if (! empty($criteria['country'])) {
            $params['country'] = urlencode((string) $criteria['country']);
        }

        if (! empty($criteria['category'])) {
            $params['category'] = urlencode((string) $criteria['category']);
        }

        if (! empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['fromDate'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['toDate'] = $criteria['date_range']['end'];
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
            $crawler->filter('.event-card, .EventCard, .listing-item, [data-testid="event-card"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventCard($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse Viagogo event card', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse Viagogo search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event card
     */
    protected function parseEventCard(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-title, .EventCard-title, h3, .listing-title');
            $venue = $this->extractText($node, '.venue-name, .EventCard-venue, .venue, .listing-venue');
            $location = $this->extractText($node, '.location, .EventCard-location, .city-country, .listing-location');
            $date = $this->extractText($node, '.date, .EventCard-date, time, .listing-date');
            $priceText = $this->extractText($node, '.price, .EventCard-price, .from-price, .listing-price');
            $link = $this->extractAttribute($node, 'a', 'href');

            if ($title === '' || $title === '0') {
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
                'location'     => trim($location ?: $venue),
                'date'         => $eventDate,
                'time'         => NULL, // Extract if available
                'price'        => $price,
                'currency'     => $this->determineCurrency($priceText),
                'url'          => $fullUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => 'event',
                'availability' => 'available',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse Viagogo event card', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if ($priceText === '' || $priceText === '0') {
            return NULL;
        }

        // Handle multiple currency symbols
        if (preg_match('/(?:[\$£€¥]|USD|GBP|EUR|JPY)\s*(\d+(?:[\.,]\d{2})?)/', $priceText, $matches)) {
            $price = str_replace(',', '', $matches[1]);

            return (float) $price;
        }

        return NULL;
    }

    /**
     * Determine currency from price text
     */
    protected function determineCurrency(string $priceText): string
    {
        if (str_contains($priceText, '£') || str_contains($priceText, 'GBP')) {
            return 'GBP';
        }
        if (str_contains($priceText, '€') || str_contains($priceText, 'EUR')) {
            return 'EUR';
        }
        if (str_contains($priceText, '¥') || str_contains($priceText, 'JPY')) {
            return 'JPY';
        }
        if (str_contains($priceText, '$') || str_contains($priceText, 'USD')) {
            return 'USD';
        }

        return $this->currency; // Default to plugin currency
    }

    /**
     * Parse date from various formats
     */
    #[Override]
    protected function parseDate(string $dateText): ?string
    {
        if ($dateText === '' || $dateText === '0') {
            return NULL;
        }

        try {
            // Try common date formats
            $date = Carbon::parse($dateText);

            return $date->format('Y-m-d');
        } catch (Exception $e) {
            Log::debug('Failed to parse Viagogo date', ['date' => $dateText, 'error' => $e->getMessage()]);

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
        return '.event-title, .EventCard-title, h3, .listing-title';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .EventCard-date, time, .listing-date';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue-name, .EventCard-venue, .venue, .listing-venue';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .EventCard-price, .from-price, .listing-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.status, .availability, .sold-out, .listing-status';
    }
}
