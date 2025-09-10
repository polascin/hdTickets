<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function is_array;

class StubHubClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $sandbox = $config['sandbox'] ?? FALSE;
        $this->baseUrl = $sandbox ? 'https://api.stubhub.com/sellers/search/events/v3' : 'https://api.stubhub.com/sellers/search/events/v3';
        $this->respectRateLimit('stubhub');
    }

    /**
     * SearchEvents
     */
    public function searchEvents(array $criteria): array
    {
        // Try API first if credentials are available
        if (! empty($this->config['api_key']) && ! empty($this->config['app_token'])) {
            try {
                return $this->searchEventsViaApi($criteria);
            } catch (Exception $e) {
                Log::warning('StubHub API search failed, falling back to scraping', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Fallback to web scraping
        return $this->scrapeSearchResults($criteria['q'] ?? '', $criteria['city'] ?? '', $criteria['per_page'] ?? 50);
    }

    /**
     * Scrape StubHub search results
     */
    /**
     * ScrapeSearchResults
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        try {
            $criteria = ['q' => $keyword, 'city' => $location, 'per_page' => $maxResults];
            $searchUrl = $this->buildScrapingSearchUrl($criteria);

            $html = $this->makeScrapingRequest($searchUrl);

            return $this->parseSearchResultsHtml($html);
        } catch (Exception $e) {
            Log::error('StubHub scraping search failed', [
                'keyword'  => $keyword,
                'location' => $location,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        // Try API first if available
        if (! empty($this->config['api_key'])) {
            try {
                return $this->getEventViaApi($eventId);
            } catch (Exception $e) {
                Log::warning('StubHub API event fetch failed, falling back to scraping', [
                    'event_id' => $eventId,
                    'error'    => $e->getMessage(),
                ]);
            }
        }

        // Fallback to scraping
        return $this->getEventViaScraping($eventId);
    }

    /**
     * Scrape event details from URL
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
            Log::error('Failed to scrape StubHub event details', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        // StubHub doesn't have a direct venue API, we'll return basic info
        return [
            'id'      => $venueId,
            'name'    => 'Unknown Venue',
            'city'    => 'Unknown City',
            'country' => 'Unknown Country',
        ];
    }

    /**
     * Get available tickets with detailed pricing
     */
    /**
     * Get  event tickets
     */
    public function getEventTickets(string $eventId, array $filters = []): array
    {
        try {
            $eventDetails = $this->getEvent($eventId);

            return [
                'event_id'       => $eventId,
                'total_listings' => $eventDetails['available_listings'] ?? 0,
                'price_range'    => [
                    'min' => $eventDetails['price_min'] ?? NULL,
                    'max' => $eventDetails['price_max'] ?? NULL,
                ],
                'prices' => $eventDetails['prices'] ?? [],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get StubHub event tickets', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (! empty($this->config['api_key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api_key'];
        }

        if (! empty($this->config['app_token'])) {
            $headers['X-SH-Application-Token'] = $this->config['app_token'];
        }

        return $headers;
    }

    /**
     * SearchEventsViaApi
     */
    protected function searchEventsViaApi(array $criteria): array
    {
        $params = $this->buildApiSearchParams($criteria);
        $response = $this->makeRequest('GET', '', $params);

        return $response['events'] ?? [];
    }

    /**
     * BuildScrapingSearchUrl
     */
    protected function buildScrapingSearchUrl(array $criteria): string
    {
        $baseUrl = 'https://www.stubhub.com/secure/search';
        $params = [];

        if (isset($criteria['q'])) {
            $params['q'] = urlencode($criteria['q']);
        }

        if (isset($criteria['city'])) {
            $params['city'] = urlencode($criteria['city']);
        }

        if (isset($criteria['date_start'])) {
            $params['start_date'] = $criteria['date_start'];
        }

        if (isset($criteria['date_end'])) {
            $params['end_date'] = $criteria['date_end'];
        }

        $params['sort'] = 'event_date_asc';
        $params['rows'] = min(100, $criteria['per_page'] ?? 50);

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Extract search results from HTML using Crawler
     */
    /**
     * ExtractSearchResults
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;

        try {
            // StubHub event selectors
            $eventSelectors = [
                '.EventCard',
                '.event-card',
                '.SearchResultCard',
                '.search-result',
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

                    break;
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to extract StubHub search results', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * ParseSearchResultsHtml
     */
    protected function parseSearchResultsHtml(string $html): array
    {
        $crawler = new Crawler($html);

        return $this->extractSearchResults($crawler, 50);
    }

    /**
     * Extract event data from node using Crawler
     */
    /**
     * ExtractEventFromNode
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        $event = [
            'platform'   => 'stubhub',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name
            $name = $this->trySelectors($node, [
                '.event-name',
                'h3',
                'h4',
                '.title',
                'a',
            ]);
            $event['name'] = $name;

            // Event URL
            $url = $this->trySelectors($node, [
                'a[href*="/event/"]',
            ], 'href');
            if ($url !== '' && $url !== '0') {
                $event['url'] = $this->normalizeUrl($url);
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $date = $this->trySelectors($node, [
                '.date',
                'time',
                '.event-date',
            ]);
            if ($date !== '' && $date !== '0') {
                $event['date'] = $date;
                $event['parsed_date'] = $this->parseEventDate($date);
            }

            // Venue
            $venue = $this->trySelectors($node, [
                '.venue',
                '.venue-name',
                '.location',
            ]);
            $event['venue'] = $venue;

            // Price information using enhanced extraction
            $prices = $this->extractPriceWithFallbacks($node);
            $event['prices'] = $prices;

            if ($prices !== []) {
                $numericPrices = array_column($prices, 'price');
                $event['price_min'] = min($numericPrices);
                $event['price_max'] = max($numericPrices);
            }
        } catch (Exception $e) {
            Log::warning('Failed to extract StubHub event from node', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * ParseEventCard
     *
     * @param mixed $eventNode
     */
    protected function parseEventCard(DOMXPath $xpath, $eventNode): array
    {
        $event = [
            'platform'   => 'stubhub',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name
            $nameNode = $xpath->query('.//a[contains(@class, "event-name")] | .//h3 | .//h4 | .//span[contains(@class, "title")]', $eventNode)->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNode = $xpath->query('.//a[contains(@href, "/event/")]', $eventNode)->item(0);
            if ($linkNode && $linkNode->hasAttribute('href')) {
                $event['url'] = $this->normalizeUrl($linkNode->getAttribute('href'));
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $dateNode = $xpath->query('.//span[contains(@class, "date")] | .//div[contains(@class, "date")] | .//time', $eventNode)->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue
            $venueNode = $xpath->query('.//span[contains(@class, "venue")] | .//div[contains(@class, "venue")]', $eventNode)->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location
            $locationNode = $xpath->query('.//span[contains(@class, "location")] | .//div[contains(@class, "city")]', $eventNode)->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price information
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")]', $eventNode);
            $prices = [];
            foreach ($priceNodes as $priceNode) {
                $priceText = trim($priceNode->textContent);
                if (preg_match('/\$[\d,]+/', $priceText)) {
                    $prices[] = $priceText;
                }
            }
            $event['prices'] = $prices;
            $event['price_range'] = implode(' - ', $prices);

            // Extract min/max prices
            $this->extractPriceRange($event, $prices);

            // Ticket count/availability
            $availabilityNode = $xpath->query('.//span[contains(@class, "available")] | .//div[contains(@class, "tickets")]', $eventNode)->item(0);
            if ($availabilityNode) {
                $availText = trim($availabilityNode->textContent);
                if (preg_match('/(\d+)\s*(?:ticket|listing)s?\s*available/i', $availText, $matches)) {
                    $event['ticket_count'] = (int) ($matches[1]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to parse StubHub event card', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * Get  event via api
     */
    protected function getEventViaApi(string $eventId): array
    {
        $endpoint = "events/{$eventId}";

        return $this->makeRequest('GET', $endpoint);
    }

    /**
     * Get  event via scraping
     */
    protected function getEventViaScraping(string $eventId): array
    {
        try {
            $eventUrl = "https://www.stubhub.com/event/{$eventId}";

            $html = $this->makeScrapingRequest($eventUrl, ['referer' => 'https://www.stubhub.com']);

            return $this->parseEventDetailsHtml($html, $eventId);
        } catch (Exception $e) {
            Log::error('StubHub event scraping failed', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * ParseEventDetailsHtml
     */
    protected function parseEventDetailsHtml(string $html, string $eventId): array
    {
        $event = [
            'id'         => $eventId,
            'platform'   => 'stubhub',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Event name
            $nameNode = $xpath->query('//h1[@class*="event-title"] | //h1 | //title')->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event date and time
            $dateNode = $xpath->query('//span[contains(@class, "event-date")] | //div[contains(@class, "date")]')->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue information
            $venueNode = $xpath->query('//span[contains(@class, "venue")] | //div[contains(@class, "venue")]')->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location
            $locationNode = $xpath->query('//span[contains(@class, "location")] | //address')->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price data from listings
            $listingNodes = $xpath->query('//div[contains(@class, "listing")] | //div[contains(@class, "ticket-listing")]');
            $prices = [];
            foreach ($listingNodes as $listingNode) {
                $priceNode = $xpath->query('.//span[contains(@class, "price")]', $listingNode)->item(0);
                if ($priceNode && preg_match('/\$[\d,]+/', $priceNode->textContent)) {
                    $prices[] = trim($priceNode->textContent);
                }
            }

            $event['available_listings'] = count($listingNodes);
            $event['prices'] = array_unique($prices);
            $this->extractPriceRange($event, $prices);

            // Event description
            $descNode = $xpath->query('//div[contains(@class, "description")] | //div[contains(@class, "event-info")]')->item(0);
            $event['description'] = $descNode ? trim($descNode->textContent) : '';
        } catch (Exception $e) {
            Log::error('Failed to parse StubHub event details HTML', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * BuildApiSearchParams
     */
    protected function buildApiSearchParams(array $criteria): array
    {
        $params = [];

        if (isset($criteria['q'])) {
            $params['name'] = $criteria['q'];
        }

        if (isset($criteria['city'])) {
            $params['city'] = $criteria['city'];
        }

        if (isset($criteria['date_start'])) {
            $params['minDate'] = $criteria['date_start'];
        }

        if (isset($criteria['date_end'])) {
            $params['maxDate'] = $criteria['date_end'];
        }

        $params['rows'] = min(100, $criteria['per_page'] ?? 50);
        $params['start'] = $criteria['page'] ?? 0;

        return $params;
    }

    /**
     * TransformEventData
     */
    protected function transformEventData(array $eventData): array
    {
        return [
            // Standard Fields
            'id'                  => $eventData['id'] ?? NULL,
            'name'                => $eventData['name'] ?? 'Unnamed Event',
            'date'                => $eventData['parsed_date'] ? $eventData['parsed_date']->format('Y-m-d') : NULL,
            'time'                => $eventData['parsed_date'] ? $eventData['parsed_date']->format('H:i:s') : NULL,
            'venue'               => $eventData['venue'] ?? 'Unknown Venue',
            'city'                => $this->extractCity($eventData['location'] ?? ''),
            'country'             => $this->determineCountry($eventData['location'] ?? ''),
            'url'                 => $eventData['url'] ?? '',
            'price_min'           => $eventData['price_min'] ?? NULL,
            'price_max'           => $eventData['price_max'] ?? NULL,
            'currency'            => 'USD', // StubHub primarily uses USD
            'availability_status' => $this->mapAvailabilityStatus($this->determineStatus($eventData)),
            'ticket_count'        => $eventData['ticket_count'] ?? $eventData['available_listings'] ?? NULL,
            'image_url'           => $eventData['image_url'] ?? NULL,
            'description'         => $eventData['description'] ?? '',

            // StubHub Platform-Specific Mappings
            'ticket_classes'   => $this->extractTicketClasses($eventData),
            'zones'            => $this->extractZones($eventData),
            'section_mappings' => $this->mapSections($eventData),
            'listing_count'    => $eventData['available_listings'] ?? NULL,

            // Metadata
            'platform' => 'stubhub',
            'raw_data' => $eventData, // Store original data for debugging
        ];
    }

    /**
     * DetermineStatus
     */
    protected function determineStatus(array $eventData): string
    {
        if (empty($eventData['prices']) && empty($eventData['ticket_count'])) {
            return 'soldout';
        }

        if (! empty($eventData['ticket_count']) && $eventData['ticket_count'] > 0) {
            return 'onsale';
        }

        return 'unknown';
    }

    /**
     * ExtractCity
     */
    protected function extractCity(string $location): string
    {
        // Extract city from location string like "New York, NY" or "Los Angeles, CA"
        if (preg_match('/^([^,]+),?\s*[A-Z]{2}?/', $location, $matches)) {
            return trim($matches[1]);
        }

        return $location ?: 'Unknown City';
    }

    /**
     * NormalizeUrl
     */
    #[Override]
    protected function normalizeUrl(string $url, ?string $baseUrl = NULL): string
    {
        if (! str_starts_with($url, 'http')) {
            return 'https://www.stubhub.com' . $url;
        }

        return $url;
    }

    /**
     * ExtractEventIdFromUrl
     */
    protected function extractEventIdFromUrl(string $url): ?string
    {
        if (preg_match('/\/event\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return NULL;
    }

    /**
     * ExtractPriceRange
     *
     * @param mixed $event
     */
    protected function extractPriceRange(array &$event, array $prices): void
    {
        if ($prices === []) {
            return;
        }

        $numericPrices = [];
        foreach ($prices as $price) {
            if (preg_match('/\$?([\d,]+(?:\.\d{2})?)/', (string) $price, $matches)) {
                $numericPrices[] = (float) (str_replace(',', '', $matches[1]));
            }
        }

        if ($numericPrices !== []) {
            $event['price_min'] = min($numericPrices);
            $event['price_max'] = max($numericPrices);
        }
    }

    /**
     * ParseEventDate
     */
    #[Override]
    protected function parseEventDate(string $dateString): ?DateTime
    {
        if ($dateString === '' || $dateString === '0') {
            return NULL;
        }

        // Clean up the date string
        $dateString = trim((string) preg_replace('/\s+/', ' ', $dateString));

        $formats = [
            'M j, Y \a\t g:i A',
            'F j, Y \a\t g:i A',
            'M j, Y g:i A',
            'F j, Y g:i A',
            'M j, Y',
            'F j, Y',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
            'Y-m-d',
        ];

        foreach ($formats as $format) {
            try {
                $date = DateTime::createFromFormat($format, $dateString);
                if ($date) {
                    return $date;
                }
            } catch (Exception) {
                continue;
            }
        }

        try {
            return new DateTime($dateString);
        } catch (Exception) {
            return NULL;
        }
    }

    /**
     * Extract detailed event information
     */
    /**
     * ExtractEventDetails
     */
    protected function extractEventDetails(Crawler $crawler, string $url): array
    {
        $event = [
            'url'        => $url,
            'platform'   => 'stubhub',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Extract using JSON-LD first
            $jsonLdData = $this->extractJsonLdData($crawler, 'Event');
            if ($jsonLdData !== []) {
                $eventData = $jsonLdData[0];
                $event['name'] = $eventData['name'] ?? '';
                $event['description'] = $eventData['description'] ?? '';
                if (isset($eventData['startDate'])) {
                    $event['parsed_date'] = new DateTime($eventData['startDate']);
                }
                if (isset($eventData['location']['name'])) {
                    $event['venue'] = $eventData['location']['name'];
                }
            }

            // Fallback to selectors if JSON-LD not available
            if (empty($event['name'])) {
                $event['name'] = $this->trySelectors($crawler, [
                    'h1[class*="event-title"]',
                    'h1',
                    '.event-title',
                ]);
            }

            if (empty($event['venue'])) {
                $event['venue'] = $this->trySelectors($crawler, [
                    '.venue-name',
                    '.venue',
                    '.location',
                ]);
            }

            // Extract prices
            $event['prices'] = $this->extractPrices($crawler);

            return $event;
        } catch (Exception $e) {
            Log::error('Failed to extract StubHub event details', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return $event;
        }
    }

    /**
     * Extract prices from page
     */
    /**
     * ExtractPrices
     */
    protected function extractPrices(Crawler $crawler): array
    {
        $prices = [];

        try {
            // Try different price selectors
            $priceSelectors = [
                '.price',
                '.ticket-price',
                '.listing-price',
                '[data-price]',
            ];

            foreach ($priceSelectors as $selector) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$prices): void {
                    $text = $node->text();
                    if (preg_match('/\$([0-9,]+(?:\.\d{2})?)/', $text, $matches)) {
                        $prices[] = [
                            'price'    => (float) (str_replace(',', '', $matches[1])),
                            'currency' => 'USD',
                            'section'  => 'General',
                        ];
                    }
                });

                if ($prices !== []) {
                    break;
                }
            }
        } catch (Exception $e) {
            Log::debug('Failed to extract StubHub prices', ['error' => $e->getMessage()]);
        }

        return $prices;
    }

    /**
     * Extract ticket classes from StubHub data
     */
    /**
     * ExtractTicketClasses
     */
    protected function extractTicketClasses(array $eventData): array
    {
        $classes = [];

        if (isset($eventData['prices']) && is_array($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (is_array($price) && isset($price['section'])) {
                    $classes[] = [
                        'class'    => $price['section'],
                        'price'    => $price['price'] ?? NULL,
                        'currency' => $price['currency'] ?? 'USD',
                    ];
                }
            }
        }

        return array_unique($classes, SORT_REGULAR);
    }

    /**
     * Extract zone information from StubHub data
     */
    /**
     * ExtractZones
     */
    protected function extractZones(array $eventData): array
    {
        $zones = [];

        if (isset($eventData['venue_zones'])) {
            return $eventData['venue_zones'];
        }

        // Try to extract from ticket data
        if (isset($eventData['prices']) && is_array($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (is_array($price) && isset($price['zone'])) {
                    $zones[] = $price['zone'];
                }
            }
        }

        return array_unique($zones);
    }

    /**
     * Map sections for StubHub venue seating
     */
    /**
     * MapSections
     */
    protected function mapSections(array $eventData): array
    {
        $sections = [];

        if (isset($eventData['prices']) && is_array($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (is_array($price) && isset($price['section'])) {
                    $sections[$price['section']] = [
                        'name'        => $price['section'],
                        'type'        => $this->determineSectionType($price['section']),
                        'price_range' => [
                            'min' => $price['price'] ?? NULL,
                            'max' => $price['price'] ?? NULL,
                        ],
                    ];
                }
            }
        }

        return $sections;
    }

    /**
     * Determine section type based on section name
     */
    /**
     * DetermineSectionType
     */
    protected function determineSectionType(string $sectionName): string
    {
        $sectionName = strtolower($sectionName);

        if (str_contains($sectionName, 'vip') || str_contains($sectionName, 'premium')) {
            return 'premium';
        }

        if (str_contains($sectionName, 'floor') || str_contains($sectionName, 'pit')) {
            return 'floor';
        }

        if (str_contains($sectionName, 'upper') || str_contains($sectionName, 'balcony')) {
            return 'upper';
        }

        if (str_contains($sectionName, 'lower') || str_contains($sectionName, 'orchestra')) {
            return 'lower';
        }

        return 'general';
    }

    /**
     * Map internal status to standardized availability status
     */
    /**
     * MapAvailabilityStatus
     */
    protected function mapAvailabilityStatus(string $internalStatus): string
    {
        $statusMap = [
            'onsale'    => 'available',
            'soldout'   => 'sold_out',
            'presale'   => 'presale',
            'offsale'   => 'not_available',
            'cancelled' => 'cancelled',
            'postponed' => 'postponed',
            'unknown'   => 'unknown',
        ];

        return $statusMap[$internalStatus] ?? 'unknown';
    }

    /**
     * Determine country from location string
     */
    /**
     * DetermineCountry
     */
    protected function determineCountry(string $location): string
    {
        if ($location === '' || $location === '0') {
            return 'United States'; // StubHub default
        }

        // Extract country from location string
        $parts = array_map('trim', explode(',', $location));

        if (count($parts) >= 2) {
            $lastPart = end($parts);

            // State codes indicate US
            if (preg_match('/^[A-Z]{2}$/', $lastPart)) {
                return 'United States';
            }

            // Country code mappings
            $countryCodes = [
                'CA' => 'Canada',
                'UK' => 'United Kingdom',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
                'IT' => 'Italy',
                'ES' => 'Spain',
            ];

            return $countryCodes[$lastPart] ?? $lastPart;
        }

        return 'United States';
    }
}
