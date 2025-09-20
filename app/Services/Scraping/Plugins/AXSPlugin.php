<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class AXSPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for AXS
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Popular Sports' => [
                'Premier League Football',
                'Championship Football',
                'England Rugby',
                'England Cricket',
                'Six Nations Rugby',
                'The Ashes Cricket',
            ],
            'Popular Venues' => [
                'Wembley Stadium',
                'Emirates Stadium',
                'Old Trafford',
                'Anfield',
                'Stamford Bridge',
                'O2 Arena',
            ],
            'Event Types' => [
                'Football Tickets',
                'Concert Tickets',
                'Theater Shows',
                'Comedy Shows',
                'Music Festivals',
            ],
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $supportedVenues = [
            'wembley', 'emirates', 'old trafford', 'anfield',
            'stamford bridge', 'o2 arena', 'manchester arena',
            'first direct arena', 'motorpoint arena',
        ];

        return in_array(strtolower($venue), $supportedVenues, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'AXS';
        $this->platform = 'axs';
        $this->description = 'AXS - Major UK and international ticket platform for sports, concerts, and events';
        $this->baseUrl = 'https://www.axs.com';
        $this->venue = 'Various';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'football_tickets',
            'premier_league',
            'championship',
            'rugby',
            'cricket',
            'concerts',
            'theater',
            'sports_events',
            'festivals',
            'comedy_shows',
            'multi_venue',
            'multi_city',
            'resale_platform',
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
            'date',
            'category',
            'genre',
            'venue',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/uk/search';
    }

    /**
     * Build search URL for AXS
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'q'      => $query,
            'city'   => $filters['city'] ?? '',
            'date'   => $filters['date'] ?? '',
            'genre'  => $filters['category'] ?? '',
            'radius' => $filters['radius'] ?? '50',
        ];

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => !empty($value));

        return $this->baseUrl . '/uk/search?' . http_build_query($params);
    }

    /**
     * Scrape tickets from AXS search results
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("AXS Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (!$response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // AXS search results selector
            $crawler->filter('.search-result-item, .event-card, .listing-item')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('AXS Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('AXS Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('AXS Plugin: Scraping error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $tickets = [];

        $crawler->filter('.search-result-item, .event-card, .listing-item')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('AXS Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.event-title, .title, .name, h2 a, h3 a';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.event-date, .date, .event-time';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue-name, .location, .venue';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .price-range';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.event-title, .listing-title, h3 a, .title');
            if ($title === '' || $title === '0') {
                return NULL;
            }

            $venue = $this->extractText($node, '.venue-name, .location, .venue');
            $date = $this->extractText($node, '.event-date, .date, .event-time');
            $priceText = $this->extractText($node, '.price, .cost, .price-range');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title and venue
            $category = $this->determineCategory($title, $venue);

            return [
                'title'        => $title,
                'price'        => $price['min'],
                'price_range'  => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $category,
                'availability' => 'available',
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('AXS Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse price to get range information
     */
    private function parsePrice(string $priceText): array
    {
        if ($priceText === '' || $priceText === '0') {
            return ['min' => NULL, 'max' => NULL];
        }

        // Extract numeric values from price text
        preg_match_all('/[\d,]+\.?\d*/', $priceText, $matches);
        $prices = array_map(fn (string $price): float => (float) str_replace(',', '', $price), $matches[0]);

        if ($prices === []) {
            return ['min' => NULL, 'max' => NULL];
        }

        return [
            'min' => min($prices),
            'max' => count($prices) > 1 ? max($prices) : min($prices),
        ];
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $combined = $title . ' ' . $venue;

        if (preg_match('/football|fc|united|city|arsenal|chelsea|liverpool|tottenham|premier|league/', $combined)) {
            return 'football';
        }
        if (preg_match('/rugby|rfl|union/', $combined)) {
            return 'rugby';
        }
        if (preg_match('/cricket|test|odi|t20/', $combined)) {
            return 'cricket';
        }
        if (preg_match('/concert|tour|music|band|singer/', $combined)) {
            return 'concert';
        }
        if (preg_match('/theatre|theater|musical|show/', $combined)) {
            return 'theater';
        }
        if (preg_match('/comedy|stand.?up|comedian/', $combined)) {
            return 'comedy';
        }
        if (preg_match('/festival|fest/', $combined)) {
            return 'festival';
        }

        return 'other';
    }
}
