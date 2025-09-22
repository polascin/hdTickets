<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class TicketmasterClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.ticketmaster.com';
        $this->respectRateLimit('ticketmaster');
    }

    /**
     * SearchEvents
     */
    public function searchEvents(array $criteria): array
    {
        return $this->makeRequest('GET', 'events', $criteria);
    }

    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        return $this->makeRequest('GET', "events/{$eventId}");
    }

    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    /**
     * Scrape Ticketmaster search results
     */
    /**
     * ScrapeSearchResults
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        $searchUrl = $this->buildSearchUrl($keyword, $location);

        try {
            $html = $this->makeScrapingRequest($searchUrl);
            $crawler = new Crawler($html);

            return $this->extractSearchResults($crawler, $maxResults);
        } catch (Exception $e) {
            Log::error('Ticketmaster scraping failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Scrape individual event details
     */
    /**
     * ScrapeEventDetails
     */
    public function scrapeEventDetails(string $url): array
    {
        try {
            $html = $this->makeScrapingRequest($url, ['referer' => $this->baseUrl]);
            $crawler = new Crawler($html);

            return $this->extractEventDetails($crawler, $url);
        } catch (Exception $e) {
            Log::error('Failed to scrape event details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        return [
            'Accept'     => 'application/json',
            'User-Agent' => 'Laravel Ticker Manager/1.0',
        ];
    }

    /**
     * Extract search results from HTML
     */
    /**
     * ExtractSearchResults
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;

        // Look for different possible selectors for event listings
        $eventSelectors = [
            '[data-testid="event-tile"]',
            '.event-tile',
            '.search-result-item',
            '.event-card',
        ];

        foreach ($eventSelectors as $selector) {
            if ($crawler->filter($selector)->count() > 0) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$events, &$count, $maxResults) {
                    if ($count >= $maxResults) {
                        return FALSE;
                    }

                    $event = $this->extractEventFromNode($node);
                    if (! empty($event['name'])) {
                        $events[] = $event;
                        $count++;
                    }
                });

                break; // Use first selector that works
            }
        }

        return $events;
    }

    /**
     * Extract event data from a single node
     */
    /**
     * ExtractEventFromNode
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        try {
            // Try multiple selectors for different page layouts + JSON-LD fallback
            $name = $this->trySelectors($node, [
                'h3 a',
                '.event-name a',
                '[data-testid="event-name"] a',
                'h2 a',
                'a[href*="/event/"]',
                '.EventDetails-eventName',
                '.eds-text-bm',
            ]);

            $link = $node->filter('a[href*="/event/"]')->first();
            $url = $link->count() > 0 ? 'https://www.ticketmaster.com' . $link->attr('href') : '';

            $date = $this->trySelectors($node, [
                '.event-date',
                '[data-testid="event-date"]',
                '.date',
                'time',
                '.EventDetails-eventDate',
                '.eds-text-bs',
            ]);

            // Parse date with enhanced parsing
            $parsedDate = $this->parseEventDate($date);

            $venue = $this->trySelectors($node, [
                '.venue-name',
                '[data-testid="venue-name"]',
                '.event-venue',
                '.venue',
                '.EventDetails-venueName',
                '.eds-text-bm',
            ]);

            // Extract prices using enhanced methods
            $priceData = $this->extractPriceWithFallbacks($node);
            $priceRange = $priceData === [] ? '' : $this->formatPriceRange($priceData);

            $price = $this->trySelectors($node, [
                '.price-range',
                '[data-testid="price-range"]',
                '.event-price',
                '.price',
                '.PriceRange-value',
            ]) ?: $priceRange;

            return [
                'name'        => trim($name),
                'url'         => $url,
                'date'        => trim($date),
                'parsed_date' => $parsedDate,
                'venue'       => trim($venue),
                'price_range' => trim($price),
                'prices'      => $priceData,
                'source'      => 'ticketmaster_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to extract event from node', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Extract ticket prices from the page
     */
    /**
     * ExtractPrices
     */
    protected function extractPrices(Crawler $crawler): array
    {
        $prices = [];

        try {
            $priceNodes = $crawler->filter('.ticket-price, .price-level, [data-testid="price-level"]');

            $priceNodes->each(function (Crawler $node) use (&$prices): void {
                $priceText = $node->text();
                $sectionText = $node->closest('tr, .section')->filter('.section-name, .seat-type')->text('');

                if (preg_match('/\$([\d,]+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $prices[] = [
                        'section'  => trim($sectionText) ?: 'General',
                        'price'    => (float) (str_replace(',', '', $matches[1])),
                        'currency' => 'USD',
                    ];
                }
            });
        } catch (Exception) {
            // Ignore price extraction errors
        }

        return $prices;
    }

    /**
     * Format price range from price data array
     */
    /**
     * FormatPriceRange
     */
    protected function formatPriceRange(array $prices): string
    {
        if ($prices === []) {
            return '';
        }

        $numericPrices = [];
        foreach ($prices as $price) {
            if (isset($price['price']) && is_numeric($price['price'])) {
                $numericPrices[] = $price['price'];
            }
        }

        if ($numericPrices === []) {
            return '';
        }

        $min = min($numericPrices);
        $max = max($numericPrices);

        if ($min === $max) {
            return '$' . number_format($min, 2);
        }

        return '$' . number_format($min, 2) . ' - $' . number_format($max, 2);
    }

    /**
     * TransformEventData
     */
    protected function transformEventData(array $eventData): array
    {
        return [
            'id'      => $eventData['id'] ?? NULL,
            'name'    => $eventData['name'] ?? 'Unnamed Event',
            'date'    => $eventData['dates']['start']['localDate'] ?? NULL,
            'time'    => $eventData['dates']['start']['localTime'] ?? NULL,
            'status'  => $eventData['dates']['status']['code'] ?? 'unknown',
            'venue'   => $eventData['_embedded']['venues'][0]['name'] ?? 'Unknown Venue',
            'city'    => $eventData['_embedded']['venues'][0]['city']['name'] ?? 'Unknown City',
            'country' => $eventData['_embedded']['venues'][0]['country']['name'] ?? 'Unknown Country',
            'url'     => $eventData['url'] ?? '',
        ];
    }

    /**
     * Build search URL for Ticketmaster
     */
    /**
     * BuildSearchUrl
     */
    private function buildSearchUrl(string $keyword, string $location = ''): string
    {
        $baseUrl = 'https://www.ticketmaster.com/search';
        $params = [
            'q'    => $keyword,
            'sort' => 'date,asc',
        ];

        if ($location !== '' && $location !== '0') {
            $params['city'] = $location;
        }

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Extract detailed event information from event page
     */
    /**
     * ExtractEventDetails
     */
    private function extractEventDetails(Crawler $crawler, string $url): array
    {
        try {
            $name = $this->trySelectors($crawler, [
                'h1[data-testid="event-name"]',
                'h1.event-name',
                'h1.event-title',
                'h1',
            ]);

            $description = $this->trySelectors($crawler, [
                '[data-testid="event-description"]',
                '.event-description',
                '.event-info p',
                '.description',
            ]);

            $dateTime = $this->trySelectors($crawler, [
                '[data-testid="event-date-time"]',
                '.event-datetime',
                '.date-time',
                'time',
            ]);

            $venue = $this->trySelectors($crawler, [
                '[data-testid="venue-name"]',
                '.venue-name',
                '.event-venue h2',
                '.venue h2',
            ]);

            $address = $this->trySelectors($crawler, [
                '[data-testid="venue-address"]',
                '.venue-address',
                '.address',
                '.event-venue .address',
            ]);

            $priceRange = $this->trySelectors($crawler, [
                '[data-testid="price-range"]',
                '.price-range',
                '.ticket-prices',
                '.price-info',
            ]);

            // Extract ticket prices
            $prices = $this->extractPrices($crawler);

            // Extract image
            $image = '';
            $imgNode = $crawler->filter('img[src*="ticketmaster"], .event-image img, [data-testid="event-image"] img')->first();
            if ($imgNode->count() > 0) {
                $image = $imgNode->attr('src');
            }

            return [
                'name'        => trim($name),
                'description' => trim($description),
                'date_time'   => trim($dateTime),
                'venue'       => trim($venue),
                'address'     => trim($address),
                'price_range' => trim($priceRange),
                'prices'      => $prices,
                'image'       => $image,
                'url'         => $url,
                'source'      => 'ticketmaster_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error extracting event details: ' . $e->getMessage());

            return [];
        }
    }
}
