<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use DateTime;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class ViagogoClient extends BaseApiClient
{
    /** @var array<string, string> */
    protected $scrapingHeaders = [
        'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language'           => 'en-US,en;q=0.9',
        'Accept-Encoding'           => 'gzip, deflate, br',
        'DNT'                       => '1',
        'Connection'                => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1',
        'Sec-Fetch-Dest'            => 'document',
        'Sec-Fetch-Mode'            => 'navigate',
        'Sec-Fetch-Site'            => 'none',
        'Cache-Control'             => 'max-age=0',
    ];

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.viagogo.com';
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<int,array<string,mixed>>
     */
    /**
     * SearchEvents
     */
    public function searchEvents(array $criteria): array
    {
        return $this->searchEventsViaScraping($criteria);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        return $this->getEventViaScraping($eventId);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        // Viagogo doesn't have a direct venue endpoint, return basic info
        return [
            'id'      => $venueId,
            'name'    => 'Unknown Venue',
            'city'    => 'Unknown City',
            'country' => 'Unknown Country',
        ];
    }

    /**
     * Get available tickets for an event
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
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
                'prices'       => $eventDetails['prices'] ?? [],
                'availability' => $this->determineStatus($eventDetails),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get Viagogo event tickets', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array<string, string>
     */
    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        return $this->scrapingHeaders;
    }

    /**
     * @param array<string, mixed> $criteria
     *
     * @return array<int,array<string,mixed>>
     */
    /**
     * SearchEventsViaScraping
     */
    protected function searchEventsViaScraping(array $criteria): array
    {
        try {
            $searchUrl = $this->buildScrapingSearchUrl($criteria);

            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($searchUrl);

            if (! $response->successful()) {
                throw new Exception('Failed to fetch search results from Viagogo');
            }

            return $this->parseSearchResultsHtml($response->body());
        } catch (Exception $e) {
            Log::error('Viagogo scraping search failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param array<string, mixed> $criteria
     */
    /**
     * BuildScrapingSearchUrl
     */
    protected function buildScrapingSearchUrl(array $criteria): string
    {
        $baseUrl = 'https://www.viagogo.com/secure/search';
        $params = [];

        if (isset($criteria['q'])) {
            $params['SearchTerm'] = urlencode($criteria['q']);
        }

        if (isset($criteria['city'])) {
            $params['Location'] = urlencode($criteria['city']);
        }

        if (isset($criteria['country'])) {
            $params['Country'] = $criteria['country'];
        }

        if (isset($criteria['date_start'])) {
            $params['FromDate'] = $criteria['date_start'];
        }

        if (isset($criteria['date_end'])) {
            $params['ToDate'] = $criteria['date_end'];
        }

        // Viagogo sorting options
        $params['Sort'] = 'EventDate';
        $params['PageSize'] = min(50, $criteria['per_page'] ?? 25);

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Parse search results from HTML content.
     *
     * @return array<int,array<string,mixed>>
     */
    /**
     * ParseSearchResultsHtml
     */
    protected function parseSearchResultsHtml(string $html): array
    {
        $events = [];

        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Viagogo event cards - they use various CSS classes
            $eventNodes = $xpath->query('//div[contains(@class, "event-card")] | //div[contains(@class, "search-result")] | //article[contains(@class, "event")] | //div[contains(@class, "listing-item")]');

            if ($eventNodes !== FALSE) {
                foreach ($eventNodes as $eventNode) {
                    $event = $this->parseEventCard($xpath, $eventNode);
                    if (! empty($event['name'])) {
                        $events[] = $event;
                    }
                }
            }

            // If no events found with above selectors, try alternative approach
            if ($events === []) {
                $linkNodes = $xpath->query('//a[contains(@href, "/event/")]');
                if ($linkNodes !== FALSE) {
                    foreach ($linkNodes as $linkNode) {
                        $event = $this->parseEventFromLink($xpath, $linkNode);
                        if (! empty($event['name'])) {
                            $events[] = $event;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to parse Viagogo search results HTML', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * Parse individual event card/node from search results.
     *
     * @return array<string,mixed>
     */
    /**
     * ParseEventCard
     */
    protected function parseEventCard(DOMXPath $xpath, DOMNode $eventNode): array
    {
        $event = [
            'platform'   => 'viagogo',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name - try multiple selectors
            $nameNodes = $xpath->query('.//h2 | .//h3 | .//h4 | .//span[contains(@class, "title")] | .//a[contains(@class, "event-title")]', $eventNode);
            $nameNode = ($nameNodes !== FALSE) ? $nameNodes->item(0) : NULL;
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNodes = $xpath->query('.//a[contains(@href, "/event/")] | .//a[contains(@href, "/tickets/")]', $eventNode);
            $linkNode = ($linkNodes !== FALSE) ? $linkNodes->item(0) : NULL;
            if ($linkNode && $linkNode->hasAttribute('href')) {
                $event['url'] = $this->normalizeUrl($linkNode->getAttribute('href'));
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $dateNodes = $xpath->query('.//span[contains(@class, "date")] | .//div[contains(@class, "date")] | .//time', $eventNode);
            $dateNode = ($dateNodes !== FALSE) ? $dateNodes->item(0) : NULL;
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue
            $venueNodes = $xpath->query('.//span[contains(@class, "venue")] | .//div[contains(@class, "venue")] | .//p[contains(@class, "venue")]', $eventNode);
            $venueNode = ($venueNodes !== FALSE) ? $venueNodes->item(0) : NULL;
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location/City
            $locationNodes = $xpath->query('.//span[contains(@class, "location")] | .//div[contains(@class, "city")] | .//span[contains(@class, "city")]', $eventNode);
            $locationNode = ($locationNodes !== FALSE) ? $locationNodes->item(0) : NULL;
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price information
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")] | .//*[contains(text(), "€")] | .//*[contains(text(), "$")] | .//*[contains(text(), "£")]', $eventNode);
            $prices = [];
            if ($priceNodes !== FALSE) {
                foreach ($priceNodes as $priceNode) {
                    $priceText = trim($priceNode->textContent);
                    if (preg_match('/[€$£][\d,]+/', $priceText)) {
                        $prices[] = $priceText;
                    }
                }
            }
            $event['prices'] = array_unique($prices);
            $event['price_range'] = implode(' - ', $prices);

            // Extract min/max prices
            $this->extractPriceRange($event, $prices);

            // Number of tickets available
            $ticketCountNodes = $xpath->query('.//span[contains(@class, "available")] | .//span[contains(text(), "ticket")] | .//span[contains(text(), "listing")]', $eventNode);
            $ticketCountNode = ($ticketCountNodes !== FALSE) ? $ticketCountNodes->item(0) : NULL;
            if ($ticketCountNode) {
                $ticketText = trim($ticketCountNode->textContent);
                if (preg_match('/(\d+)\s*(?:ticket|listing)s?\s*(?:available|from)?/i', $ticketText, $matches)) {
                    $event['ticket_count'] = (int) ($matches[1]);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to parse Viagogo event card', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * Parse event data from a link node.
     *
     * @return array<string,mixed>
     */
    /**
     * ParseEventFromLink
     */
    protected function parseEventFromLink(DOMXPath $xpath, DOMNode $linkNode): array
    {
        $event = [
            'platform'   => 'viagogo',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event URL
            if ($linkNode->hasAttribute('href')) {
                $event['url'] = $this->normalizeUrl($linkNode->getAttribute('href'));
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Event name from link text or title
            $event['name'] = trim($linkNode->textContent);
            if (empty($event['name']) && $linkNode->hasAttribute('title')) {
                $event['name'] = trim($linkNode->getAttribute('title'));
            }

            // Look for date/venue info in parent/sibling nodes
            $parentNode = $linkNode->parentNode;
            if ($parentNode instanceof DOMNode) {
                $dateNodes = $xpath->query('.//span[contains(@class, "date")] | .//time', $parentNode);
                $dateNode = ($dateNodes !== FALSE) ? $dateNodes->item(0) : NULL;
                if ($dateNode) {
                    $event['date'] = trim($dateNode->textContent);
                    $event['parsed_date'] = $this->parseEventDate($event['date']);
                }

                $venueNodes = $xpath->query('.//span[contains(@class, "venue")]', $parentNode);
                $venueNode = ($venueNodes !== FALSE) ? $venueNodes->item(0) : NULL;
                if ($venueNode) {
                    $event['venue'] = trim($venueNode->textContent);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to parse Viagogo event from link', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * Get event details via scraping.
     *
     * @return array<string,mixed>
     */
    /**
     * Get  event via scraping
     */
    protected function getEventViaScraping(string $eventId): array
    {
        try {
            $eventUrl = "https://www.viagogo.com/event/{$eventId}";

            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($eventUrl);

            if (! $response->successful()) {
                throw new Exception('Failed to fetch event details from Viagogo');
            }

            return $this->parseEventDetailsHtml($response->body(), $eventId);
        } catch (Exception $e) {
            Log::error('Viagogo event scraping failed', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Parse event details from HTML.
     *
     * @return array<string,mixed>
     */
    /**
     * ParseEventDetailsHtml
     */
    protected function parseEventDetailsHtml(string $html, string $eventId): array
    {
        $event = [
            'id'         => $eventId,
            'platform'   => 'viagogo',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Event name
            $nameNodes = $xpath->query('//h1 | //title');
            $nameNode = ($nameNodes !== FALSE) ? $nameNodes->item(0) : NULL;
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Clean up the title if it contains site name
            if (str_contains($event['name'], '|')) {
                $event['name'] = trim(explode('|', $event['name'])[0]);
            }

            // Event date and time
            $dateNodes = $xpath->query('//span[contains(@class, "event-date")] | //div[contains(@class, "date")] | //time');
            $dateNode = ($dateNodes !== FALSE) ? $dateNodes->item(0) : NULL;
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue information
            $venueNodes = $xpath->query('//span[contains(@class, "venue")] | //div[contains(@class, "venue")] | //h2[contains(@class, "venue")]');
            $venueNode = ($venueNodes !== FALSE) ? $venueNodes->item(0) : NULL;
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location
            $locationNodes = $xpath->query('//span[contains(@class, "location")] | //address | //div[contains(@class, "city")]');
            $locationNode = ($locationNodes !== FALSE) ? $locationNodes->item(0) : NULL;
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price data from ticket listings
            $listingNodes = $xpath->query('//div[contains(@class, "listing")] | //div[contains(@class, "ticket-row")] | //tr[contains(@class, "ticket")]');
            $prices = [];
            if ($listingNodes !== FALSE) {
                foreach ($listingNodes as $listingNode) {
                    $priceNodeList = $xpath->query('.//*[contains(@class, "price")] | .//*[contains(text(), "€")] | .//*[contains(text(), "$")] | .//*[contains(text(), "£")]', $listingNode);
                    $priceNode = ($priceNodeList !== FALSE) ? $priceNodeList->item(0) : NULL;
                    if ($priceNode && preg_match('/[€$£][\d,]+/', $priceNode->textContent)) {
                        $prices[] = trim($priceNode->textContent);
                    }
                }
            }

            $event['available_listings'] = ($listingNodes !== FALSE) ? count($listingNodes) : 0;
            $event['prices'] = array_unique($prices);
            $this->extractPriceRange($event, $prices);

            // Event description
            $descNodes = $xpath->query('//div[contains(@class, "description")] | //div[contains(@class, "event-info")] | //section[contains(@class, "about")]');
            $descNode = ($descNodes !== FALSE) ? $descNodes->item(0) : NULL;
            $event['description'] = $descNode ? trim($descNode->textContent) : '';

            // Category/genre
            $categoryNodes = $xpath->query('//span[contains(@class, "category")] | //div[contains(@class, "genre")]');
            $categoryNode = ($categoryNodes !== FALSE) ? $categoryNodes->item(0) : NULL;
            $event['category'] = $categoryNode ? trim($categoryNode->textContent) : '';
        } catch (Exception $e) {
            Log::error('Failed to parse Viagogo event details HTML', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * Transform event data to standardized format.
     *
     * @param array<string,mixed> $eventData
     *
     * @return array<string,mixed>
     */
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
            'country'             => $this->extractCountry($eventData['location'] ?? ''),
            'url'                 => $eventData['url'] ?? '',
            'price_min'           => $eventData['price_min'] ?? NULL,
            'price_max'           => $eventData['price_max'] ?? NULL,
            'currency'            => $this->determineCurrency($eventData),
            'availability_status' => $this->mapAvailabilityStatus($this->determineStatus($eventData)),
            'ticket_count'        => $eventData['ticket_count'] ?? $eventData['available_listings'] ?? NULL,
            'image_url'           => $eventData['image_url'] ?? NULL,
            'description'         => $eventData['description'] ?? '',

            // Viagogo Platform-Specific Mappings
            'guarantee_info'     => $this->extractGuaranteeInfo($eventData),
            'seller_type'        => $eventData['seller_type'] ?? NULL,
            'delivery_method'    => $eventData['delivery_method'] ?? NULL,
            'guarantee_coverage' => $eventData['guarantee_coverage'] ?? NULL,
            'restrictions'       => $eventData['restrictions'] ?? [],

            // Additional metadata
            'category' => $eventData['category'] ?? '',
            'platform' => 'viagogo',
            'raw_data' => $eventData, // Store original data for debugging
        ];
    }

    /**
     * Determine event status based on event data.
     *
     * @param array<string,mixed> $eventData
     */
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

        if (! empty($eventData['available_listings']) && $eventData['available_listings'] > 0) {
            return 'onsale';
        }

        return 'unknown';
    }

    /**
     * ExtractCity
     */
    protected function extractCity(string $location): string
    {
        if ($location === '' || $location === '0') {
            return 'Unknown City';
        }

        // Try to extract city from various formats
        // "Paris, France" -> "Paris"
        // "London, UK" -> "London"
        // "New York, NY, USA" -> "New York"
        $parts = explode(',', $location);

        return trim($parts[0]) ?: 'Unknown City';
    }

    /**
     * ExtractCountry
     */
    protected function extractCountry(string $location): string
    {
        if ($location === '' || $location === '0') {
            return 'Unknown Country';
        }

        // Extract country from location string
        $parts = array_map('trim', explode(',', $location));

        // If we have multiple parts, the last one is usually the country
        if (count($parts) >= 2) {
            $lastPart = end($parts);
            // If it's a 2-letter code, try to map it to country name
            $countryCodes = [
                'US' => 'United States',
                'UK' => 'United Kingdom',
                'CA' => 'Canada',
                'AU' => 'Australia',
                'DE' => 'Germany',
                'FR' => 'France',
                'IT' => 'Italy',
                'ES' => 'Spain',
                'NL' => 'Netherlands',
                'BE' => 'Belgium',
            ];

            return $countryCodes[$lastPart] ?? $lastPart;
        }

        return 'Unknown Country';
    }

    /**
     * NormalizeUrl
     */
    protected function normalizeUrl(string $url): string
    {
        if (! str_starts_with($url, 'http')) {
            return 'https://www.viagogo.com' . $url;
        }

        return $url;
    }

    /**
     * ExtractEventIdFromUrl
     */
    protected function extractEventIdFromUrl(string $url): ?string
    {
        // Viagogo URLs can be like /event/12345 or /tickets/some-event-name/e-12345
        if (preg_match('/\/(?:event|e)-?(\d+)/', $url, $matches)) {
            return $matches[1];
        }

        return NULL;
    }

    /**
     * Extract price range from prices array.
     *
     * @param array<string,mixed> $event
     * @param array<int,string>   $prices
     */
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
            // Support multiple currencies
            if (preg_match('/[€$£]?([,\d]+(?:\.\d{2})?)/', (string) $price, $matches)) {
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
    protected function parseEventDate(string $dateString): ?DateTime
    {
        if ($dateString === '' || $dateString === '0') {
            return NULL;
        }

        // Clean up the date string
        $cleanedString = preg_replace('/\s+/', ' ', $dateString);
        $dateString = trim($cleanedString ?? $dateString);

        // Remove common prefixes
        $prefixCleaned = preg_replace('/^(Event\s+date:?\s*|Date:?\s*)/i', '', $dateString);
        $dateString = $prefixCleaned ?? $dateString;

        $formats = [
            'j M Y, H:i',
            'j F Y, H:i',
            'M j, Y H:i',
            'F j, Y H:i',
            'j M Y',
            'j F Y',
            'M j, Y',
            'F j, Y',
            'd/m/Y H:i',
            'd/m/Y',
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
     * Extract guarantee information specific to Viagogo
     */
    /**
     * Extract guarantee information from event data.
     *
     * @param array<string,mixed> $eventData
     *
     * @return array<string,mixed>
     */
    /**
     * ExtractGuaranteeInfo
     */
    protected function extractGuaranteeInfo(array $eventData): array
    {
        $guaranteeInfo = [
            'has_guarantee'  => TRUE, // Viagogo always provides guarantees
            'guarantee_type' => 'full_guarantee',
            'coverage'       => [],
        ];

        // Extract specific guarantee coverage from event data
        if (isset($eventData['guarantee_coverage'])) {
            $guaranteeInfo['coverage'] = $eventData['guarantee_coverage'];
        } else {
            // Default Viagogo guarantees
            $guaranteeInfo['coverage'] = [
                'authenticity'       => 'Guaranteed authentic tickets',
                'delivery'           => 'Guaranteed delivery or full refund',
                'event_cancellation' => 'Full refund if event is cancelled',
                'replacement'        => 'Replacement tickets if there are issues',
            ];
        }

        // Check for specific guarantee indicators
        if (isset($eventData['description']) && ! empty($eventData['description'])) {
            $description = strtolower((string) $eventData['description']);

            if (str_contains($description, '100% guarantee')) {
                $guaranteeInfo['guarantee_type'] = '100_percent_guarantee';
            }

            if (str_contains($description, 'instant download')) {
                $guaranteeInfo['coverage']['instant_download'] = 'Instant download available';
            }
        }

        return $guaranteeInfo;
    }

    /**
     * Determine currency from price data
     */
    /**
     * Determine currency from event data.
     *
     * @param array<string,mixed> $eventData
     */
    /**
     * DetermineCurrency
     */
    protected function determineCurrency(array $eventData): string
    {
        // Check if currency is already specified
        if (isset($eventData['currency'])) {
            return $eventData['currency'];
        }

        // Extract currency from prices
        if (isset($eventData['prices']) && ! empty($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (str_contains((string) $price, '€')) {
                    return 'EUR';
                }
                if (str_contains((string) $price, '$')) {
                    return 'USD';
                }
                if (str_contains((string) $price, '£')) {
                    return 'GBP';
                }
                if (str_contains((string) $price, 'Kč')) {
                    return 'CZK';
                }
            }
        }

        // Default based on location
        $location = $eventData['location'] ?? '';
        if (! empty($location)) {
            $location = strtolower((string) $location);

            if (str_contains($location, 'uk') || str_contains($location, 'united kingdom')) {
                return 'GBP';
            }
            if (str_contains($location, 'us') || str_contains($location, 'united states')) {
                return 'USD';
            }
            if (str_contains($location, 'canada')) {
                return 'CAD';
            }
        }

        return 'EUR'; // Viagogo default
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
}
