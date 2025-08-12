<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class EventbriteClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.eventbrite.com';
        $this->respectRateLimit('eventbrite');
    }

    public function searchEvents(array $criteria): array
    {
        return $this->scrapeSearchResults($criteria['q'] ?? '', $criteria['location'] ?? '', $criteria['per_page'] ?? 50);
    }

    public function getEvent(string $eventId): array
    {
        return $this->scrapeEventDetails($this->baseUrl . '/e/' . $eventId);
    }

    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    /**
     * Scrape Eventbrite search results
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        $searchUrl = $this->buildSearchUrl($keyword, $location);

        try {
            $html = $this->makeScrapingRequest($searchUrl);
            $crawler = new Crawler($html);

            return $this->extractSearchResults($crawler, $maxResults);
        } catch (Exception $e) {
            Log::error('Eventbrite scraping failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Scrape individual event details
     */
    public function scrapeEventDetails(string $url): array
    {
        try {
            $html = $this->makeScrapingRequest($url, ['referer' => $this->baseUrl]);
            $crawler = new Crawler($html);

            return $this->extractEventDetails($crawler, $url);
        } catch (Exception $e) {
            Log::error('Failed to scrape Eventbrite event details: ' . $e->getMessage());

            return [];
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    protected function getHeaders(): array
    {
        return [
            'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language'           => 'en-US,en;q=0.5',
            'Accept-Encoding'           => 'gzip, deflate, br',
            'DNT'                       => '1',
            'Connection'                => 'keep-alive',
            'Upgrade-Insecure-Requests' => '1',
            'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        ];
    }

    /**
     * Extract search results from HTML (BaseWebScrapingClient requirement)
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;

        // Look for different possible selectors for event listings
        $eventSelectors = [
            '[data-testid="search-result"]',
            '.search-event-card',
            '.event-card',
            '.structured-content-card',
            '[data-testid="event-card"]',
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
     * Extract prices from crawler (BaseWebScrapingClient requirement)
     */
    protected function extractPrices(Crawler $crawler): array
    {
        $prices = [];

        try {
            $ticketNodes = $crawler->filter('.ticket-card, [data-testid="ticket-card"], .ticket-option');
            $ticketNodes->each(function (Crawler $node) use (&$prices): void {
                $priceText = $node->filter('.ticket-price, [data-testid="ticket-price"], .price')->text('');

                // Extract price from text
                $price = 0;
                $currency = 'USD';
                if (preg_match('/\$([\d\.]+)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                } elseif (preg_match('/£([\d\.]+)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'GBP';
                } elseif (preg_match('/€([\d\.]+)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'EUR';
                }

                if ($price > 0) {
                    $prices[] = [
                        'price'    => $price,
                        'currency' => $currency,
                        'section'  => 'General',
                    ];
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract Eventbrite prices', ['error' => $e->getMessage()]);
        }

        return $prices;
    }

    /**
     * Extract event data from a single node
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        try {
            // Extract event name
            $name = $this->trySelectors($node, [
                'h3 a',
                'h2 a',
                '.event-title a',
                '[data-testid="event-title"] a',
                'a[href*="/e/"]',
                '.card-title a',
            ]);

            // Extract event URL
            $link = $node->filter('a[href*="/e/"]')->first();
            $url = $link->count() > 0 ? $this->resolveUrl($link->attr('href')) : '';

            // Extract date and time
            $dateTime = $this->trySelectors($node, [
                '.event-date',
                '[data-testid="event-date"]',
                '.date-display',
                'time',
                '.structured-content-date',
            ]);

            // Parse date
            $parsedDate = $this->parseEventDate($dateTime);

            // Extract venue/location
            $venue = $this->trySelectors($node, [
                '.event-venue',
                '[data-testid="event-venue"]',
                '.venue-name',
                '.location-display',
                '.event-location',
            ]);

            // Extract price information
            $priceData = $this->extractPriceWithFallbacks($node);
            $priceRange = ! empty($priceData) ? $this->formatPriceRange($priceData) : '';

            $price = $this->trySelectors($node, [
                '.event-price',
                '[data-testid="event-price"]',
                '.ticket-price',
                '.price-display',
            ]) ?: $priceRange;

            // Extract organizer
            $organizer = $this->trySelectors($node, [
                '.event-organizer',
                '[data-testid="organizer"]',
                '.organizer-name',
            ]);

            // Extract category
            $category = $this->trySelectors($node, [
                '.event-category',
                '[data-testid="category"]',
                '.category-display',
            ]);

            return [
                'name'        => trim($name),
                'url'         => $url,
                'date'        => trim($dateTime),
                'parsed_date' => $parsedDate,
                'venue'       => trim($venue),
                'price_range' => trim($price),
                'prices'      => $priceData,
                'organizer'   => trim($organizer),
                'category'    => trim($category),
                'source'      => 'eventbrite_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to extract event from node', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Extract ticket information from the event page
     */
    protected function extractTicketInfo(Crawler $crawler): array
    {
        $ticketInfo = [
            'available' => FALSE,
            'tickets'   => [],
        ];

        try {
            // Check ticket availability
            $ticketSection = $crawler->filter('[data-testid="ticket-section"], .ticket-selection, .tickets-widget');
            if ($ticketSection->count() > 0) {
                $ticketInfo['available'] = TRUE;
            }

            // Extract ticket types and prices
            $ticketNodes = $crawler->filter('.ticket-card, [data-testid="ticket-card"], .ticket-option');
            $ticketNodes->each(function (Crawler $node) use (&$ticketInfo): void {
                $ticketName = $node->filter('.ticket-name, [data-testid="ticket-name"], h3')->text('');
                $priceText = $node->filter('.ticket-price, [data-testid="ticket-price"], .price')->text('');
                $availability = $node->filter('.ticket-availability, [data-testid="availability"]')->text('');

                // Extract price from text
                $price = 0;
                $currency = 'USD';
                if (preg_match('/\$(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                } elseif (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'GBP';
                } elseif (preg_match('/€(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
                    $price = (float) ($matches[1]);
                    $currency = 'EUR';
                }

                if (! empty($ticketName)) {
                    $ticketInfo['tickets'][] = [
                        'name'         => trim($ticketName),
                        'price'        => $price,
                        'currency'     => $currency,
                        'availability' => trim($availability),
                        'is_free'      => stripos($priceText, 'free') !== FALSE || $price === 0,
                    ];
                }
            });
        } catch (Exception $e) {
            Log::debug('Failed to extract ticket info', ['error' => $e->getMessage()]);
        }

        return $ticketInfo;
    }

    protected function transformEventData(array $eventData): array
    {
        return [
            'id'        => $eventData['id'] ?? uniqid('eventbrite_'),
            'name'      => $eventData['name'] ?? 'Unnamed Event',
            'date'      => $eventData['date'] ?? NULL,
            'time'      => $eventData['time'] ?? NULL,
            'venue'     => $eventData['venue'] ?? 'TBD',
            'city'      => $eventData['city'] ?? '',
            'country'   => $eventData['country'] ?? '',
            'url'       => $eventData['url'] ?? '',
            'organizer' => $eventData['organizer'] ?? '',
            'category'  => $eventData['category'] ?? '',
        ];
    }

    /**
     * Build search URL for Eventbrite
     */
    private function buildSearchUrl(string $keyword, string $location = ''): string
    {
        $baseUrl = 'https://www.eventbrite.com/d';
        $params = [
            'q'    => $keyword,
            'page' => 1,
        ];

        if (! empty($location)) {
            $params['location'] = $location;
        }

        return $baseUrl . '/' . urlencode($location ?: 'online') . '/' . urlencode($keyword) . '/?' . http_build_query($params);
    }

    /**
     * Extract detailed event information from event page
     */
    private function extractEventDetails(Crawler $crawler, string $url): array
    {
        try {
            $name = $this->trySelectors($crawler, [
                'h1[data-testid="event-title"]',
                'h1.event-title',
                'h1',
                '.event-name',
            ]);

            $description = $this->trySelectors($crawler, [
                '[data-testid="event-description"]',
                '.event-description',
                '.structured-content-rich-text',
                '.description-content',
            ]);

            $dateTime = $this->trySelectors($crawler, [
                '[data-testid="event-date-time"]',
                '.event-datetime',
                '.date-time-display',
                'time',
            ]);

            $venue = $this->trySelectors($crawler, [
                '[data-testid="venue-name"]',
                '.venue-name',
                '.event-venue',
                '.location-info h2',
            ]);

            $address = $this->trySelectors($crawler, [
                '[data-testid="venue-address"]',
                '.venue-address',
                '.location-address',
                '.address-display',
            ]);

            $organizer = $this->trySelectors($crawler, [
                '[data-testid="organizer-name"]',
                '.organizer-name',
                '.event-organizer',
            ]);

            // Extract ticket information
            $ticketInfo = $this->extractTicketInfo($crawler);

            // Extract event image
            $image = '';
            $imgNode = $crawler->filter('img[src*="eventbrite"], .event-image img, [data-testid="event-hero-image"] img')->first();
            if ($imgNode->count() > 0) {
                $image = $imgNode->attr('src');
            }

            return [
                'name'        => trim($name),
                'description' => trim($description),
                'date_time'   => trim($dateTime),
                'venue'       => trim($venue),
                'address'     => trim($address),
                'organizer'   => trim($organizer),
                'ticket_info' => $ticketInfo,
                'image'       => $image,
                'url'         => $url,
                'source'      => 'eventbrite_scrape',
                'scraped_at'  => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::error('Error extracting Eventbrite event details: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Resolve relative URLs to absolute URLs
     */
    private function resolveUrl(string $url): string
    {
        if (strpos($url, 'http') === 0) {
            return $url;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($url, '/');
    }
}
