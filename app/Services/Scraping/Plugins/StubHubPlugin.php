<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class StubHubPlugin extends BaseScraperPlugin
{
    /**
     * Get events by specific sport category
     */
    /**
     * Get  events by sport
     */
    public function getEventsBySport(string $sport, array $criteria = []): array
    {
        $sportUrls = [
            'football'   => '/nfl-tickets',
            'basketball' => '/nba-tickets',
            'baseball'   => '/mlb-tickets',
            'hockey'     => '/nhl-tickets',
            'soccer'     => '/mls-tickets',
            'tennis'     => '/tennis-tickets',
            'golf'       => '/golf-tickets',
        ];

        $sport = strtolower($sport);
        if (isset($sportUrls[$sport])) {
            $criteria['custom_url'] = $this->baseUrl . $sportUrls[$sport];
        } else {
            $criteria['keyword'] = $sport;
        }

        return $this->scrape($criteria);
    }

    /**
     * Get popular sports events
     */
    /**
     * Get  popular sports events
     */
    public function getPopularSportsEvents(array $criteria = []): array
    {
        $criteria['sort'] = 'popularity';
        $criteria['custom_url'] = $this->baseUrl . '/sports';

        return $this->scrape($criteria);
    }

    /**
     * Get events by city
     */
    /**
     * Get  events by city
     */
    public function getEventsByCity(string $city, array $criteria = []): array
    {
        $criteria['keyword'] = $city;

        return $this->scrape($criteria);
    }

    /**
     * Get events within price range
     */
    /**
     * Get  events by price range
     */
    public function getEventsByPriceRange(int $minPrice, int $maxPrice, array $criteria = []): array
    {
        $criteria['price_min'] = $minPrice;
        $criteria['price_max'] = $maxPrice;

        return $this->scrape($criteria);
    }

    /**
     * InitializePlugin
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'StubHub';
        $this->platform = 'stubhub';
        $this->description = 'StubHub marketplace scraper for sports events and tickets';
        $this->baseUrl = 'https://www.stubhub.com';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->rateLimitSeconds = 2;
        $this->version = '2.0.0';
    }

    /**
     * Get  capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'sports_events',
            'music_events',
            'theater_events',
            'secondary_market',
            'price_comparison',
            'seat_selection',
            'instant_download',
            'mobile_delivery',
        ];
    }

    /**
     * Get  supported criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'sport',
            'city',
            'venue',
            'date_from',
            'date_to',
            'price_min',
            'price_max',
            'sort',
        ];
    }

    /**
     * Get  test url
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/sports';
    }

    /**
     * BuildSearchUrl
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $searchUrl = $this->baseUrl . '/sports';
        $params = [];

        // Add keyword search
        if (!empty($criteria['keyword'])) {
            $searchUrl = $this->baseUrl . '/find/s/' . urlencode($criteria['keyword']);
        }

        // Add price range
        if (!empty($criteria['price_min'])) {
            $params['priceMin'] = $criteria['price_min'];
        }

        if (!empty($criteria['price_max'])) {
            $params['priceMax'] = $criteria['price_max'];
        }

        // Add sort parameter
        if (!empty($criteria['sort'])) {
            $sortMap = [
                'price_low'  => 'price_asc',
                'price_high' => 'price_desc',
                'date'       => 'event_date_asc',
                'popularity' => 'popularity_desc',
            ];
            $params['sort'] = $sortMap[$criteria['sort']] ?? 'event_date_asc';
        }

        if (!empty($params)) {
            $searchUrl .= '?' . http_build_query($params);
        }

        return $searchUrl;
    }

    /**
     * ParseSearchResults
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $events = [];

        // Multiple selectors for different StubHub page layouts
        $eventSelectors = [
            '.SearchResultsGrid-container .SearchResultsGrid-item',
            '.events-list .event-item',
            '.search-results .result-item',
            '[data-testid="event-card"]',
        ];

        foreach ($eventSelectors as $selector) {
            $eventNodes = $crawler->filter($selector);

            if ($eventNodes->count() > 0) {
                Log::info("Found {$eventNodes->count()} events using selector: {$selector}");

                $eventNodes->each(function (Crawler $node) use (&$events): void {
                    $event = $this->parseEventNode($node);
                    if ($event) {
                        $events[] = $event;
                    }
                });

                break; // Use the first selector that finds results
            }
        }

        return $events;
    }

    /**
     * ParseEventNode
     */
    protected function parseEventNode(Crawler $node): ?array
    {
        try {
            // Extract event name
            $eventName = $this->extractText($node, $this->getEventNameSelectors());
            if (empty($eventName)) {
                return NULL;
            }

            // Extract venue
            $venue = $this->extractText($node, $this->getVenueSelectors());

            // Extract date
            $dateText = $this->extractText($node, $this->getDateSelectors());
            $date = $this->parseStubHubDate($dateText);

            // Extract price information
            $priceText = $this->extractText($node, $this->getPriceSelectors());
            $priceInfo = $this->parseStubHubPrice($priceText);

            // Extract URL
            $url = $this->extractUrl($node);
            if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
                $url = $this->baseUrl . $url;
            }

            // Extract availability
            $availabilityText = $this->extractText($node, $this->getAvailabilitySelectors());
            $availability = $this->normalizeStubHubAvailability($availabilityText);

            // Determine category and subcategory
            $category = $this->getEventCategory($eventName);
            $subcategory = $this->getEventSubcategory($eventName);

            return [
                'event_name'          => trim($eventName),
                'venue'               => $venue ?: 'Various',
                'date'                => $date,
                'price_min'           => $priceInfo['min'],
                'price_max'           => $priceInfo['max'],
                'currency'            => $this->currency,
                'url'                 => $url,
                'platform'            => $this->platform,
                'availability_status' => $availability,
                'category'            => $category,
                'subcategory'         => $subcategory,
                'ticket_type'         => 'Secondary Market',
                'scraped_at'          => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::warning('Failed to parse StubHub event node', [
                'error' => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Get  event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return 'h3 a, .event-title, .event-name, [data-testid="event-title"], .search-result-title a, .event-listing-title';
    }

    /**
     * Get  date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.event-date, .date-time, [data-testid="event-date"], .event-datetime, .listing-date';
    }

    /**
     * Get  venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue-name, .event-venue, [data-testid="venue-name"], .venue-info, .listing-venue';
    }

    /**
     * Get  price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .ticket-price, [data-testid="price"], .price-range, .starting-price, .min-price';
    }

    /**
     * Get  availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .ticket-count, [data-testid="availability"], .tickets-available, .inventory-status';
    }

    /**
     * Enhanced filtering for StubHub-specific criteria
     */
    /**
     * FilterResults
     */
    protected function filterResults(array $events, array $criteria): array
    {
        $filteredEvents = parent::filterResults($events, $criteria);

        // Additional StubHub-specific filtering
        return array_filter($filteredEvents, function ($event) use ($criteria) {
            // Price range filtering
            if (!empty($criteria['price_min'])
                && !empty($event['price_min'])
                && $event['price_min'] < $criteria['price_min']) {
                return FALSE;
            }

            if (!empty($criteria['price_max'])
                && !empty($event['price_max'])
                && $event['price_max'] > $criteria['price_max']) {
                return FALSE;
            }

            // Venue filtering
            if (!empty($criteria['venue'])) {
                $venueKeyword = strtolower($criteria['venue']);
                $eventVenue = strtolower($event['venue'] ?? '');
                if (strpos($eventVenue, $venueKeyword) === FALSE) {
                    return FALSE;
                }
            }

            return TRUE;
        });
    }

    /**
     * ParseStubHubDate
     */
    private function parseStubHubDate(string $dateText): ?string
    {
        if (empty($dateText)) {
            return NULL;
        }

        try {
            // Clean up common StubHub date formats
            $dateText = trim($dateText);
            $dateText = preg_replace('/\s+/', ' ', $dateText);

            // Handle relative dates
            if (stripos($dateText, 'today') !== FALSE) {
                return now()->toISOString();
            }

            if (stripos($dateText, 'tomorrow') !== FALSE) {
                return now()->addDay()->toISOString();
            }

            // Handle various StubHub date formats
            $dateFormats = [
                'M j, Y g:i A',      // Jan 15, 2024 7:30 PM
                'F j, Y g:i A',      // January 15, 2024 7:30 PM
                'M j g:i A',         // Jan 15 7:30 PM
                'M j',               // Jan 15
                'n/j/Y g:i A',       // 1/15/2024 7:30 PM
                'Y-m-d H:i:s',       // 2024-01-15 19:30:00
                'M j, Y',            // Jan 15, 2024
            ];

            foreach ($dateFormats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $dateText);
                    if ($parsed) {
                        // If year is not specified, assume current year
                        if (!preg_match('/\d{4}/', $dateText)) {
                            $parsed->year(now()->year);
                        }

                        return $parsed->toISOString();
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            // Fallback to Carbon's natural parsing
            return Carbon::parse($dateText)->toISOString();
        } catch (Exception $e) {
            Log::warning('Failed to parse StubHub date', [
                'date_text' => $dateText,
                'error'     => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * ParseStubHubPrice
     */
    private function parseStubHubPrice(string $priceText): array
    {
        $priceInfo = ['min' => NULL, 'max' => NULL];

        if (empty($priceText)) {
            return $priceInfo;
        }

        // Clean price text
        $priceText = trim($priceText);

        // Handle "from $X" format
        if (preg_match('/from\s*\$(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $priceText, $matches)) {
            $priceInfo['min'] = (float) str_replace(',', '', $matches[1]);

            return $priceInfo;
        }

        // Handle "$X - $Y" range format
        if (preg_match('/\$(\d+(?:,\d{3})*(?:\.\d{2})?)\s*-\s*\$(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $priceText, $matches)) {
            $priceInfo['min'] = (float) str_replace(',', '', $matches[1]);
            $priceInfo['max'] = (float) str_replace(',', '', $matches[2]);

            return $priceInfo;
        }

        // Handle single price "$X"
        if (preg_match('/\$(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $priceText, $matches)) {
            $price = (float) str_replace(',', '', $matches[1]);
            $priceInfo['min'] = $price;
            $priceInfo['max'] = $price;

            return $priceInfo;
        }

        // Handle "starting at $X"
        if (preg_match('/starting\s+at\s*\$(\d+(?:,\d{3})*(?:\.\d{2})?)/i', $priceText, $matches)) {
            $priceInfo['min'] = (float) str_replace(',', '', $matches[1]);

            return $priceInfo;
        }

        return $priceInfo;
    }

    /**
     * NormalizeStubHubAvailability
     */
    private function normalizeStubHubAvailability(string $availability): string
    {
        $availability = strtolower(trim($availability));

        if (empty($availability)) {
            return 'unknown';
        }

        if (strpos($availability, 'sold out') !== FALSE
            || strpos($availability, 'no tickets') !== FALSE) {
            return 'sold_out';
        }

        if (strpos($availability, 'low inventory') !== FALSE
            || strpos($availability, 'few left') !== FALSE
            || preg_match('/\d+\s*left/i', $availability)) {
            return 'low_inventory';
        }

        if (strpos($availability, 'available') !== FALSE
            || strpos($availability, 'in stock') !== FALSE
            || preg_match('/\d+\s*tickets?/i', $availability)) {
            return 'available';
        }

        return 'unknown';
    }
}
