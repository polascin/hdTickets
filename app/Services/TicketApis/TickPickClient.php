<?php declare(strict_types=1);

namespace App\Services\TicketApis;

use DateTime;
use DOMDocument;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;

class TickPickClient extends BaseApiClient
{
    /**
     * Headers for scraping requests
     *
     * @var array<string,string>
     */
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
     * Constructor
     *
     * @param array<string,mixed> $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.tickpick.com';
    }

    /**
     * Search for events
     *
     * @param array<string,mixed> $criteria
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
     * Get event details
     *
     * @return array<string,mixed>
     */
    /**
     * Get  event
     */
    public function getEvent(string $eventId): array
    {
        return $this->getEventViaScraping($eventId);
    }

    /**
     * Get venue details
     *
     * @return array<string,mixed>
     */
    /**
     * Get  venue
     */
    public function getVenue(string $venueId): array
    {
        // TickPick doesn't have a direct venue endpoint, return basic info
        return [
            'id'      => $venueId,
            'name'    => 'Unknown Venue',
            'city'    => 'Unknown City',
            'country' => 'Unknown Country',
        ];
    }

    /**
     * Get available tickets for an event with no-fee pricing information
     *
     * @param array<string,mixed> $filters
     *
     * @return array<int,array<string,mixed>>
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
                'no_fee_price_range' => [
                    'min' => $eventDetails['no_fee_price_min'] ?? NULL,
                    'max' => $eventDetails['no_fee_price_max'] ?? NULL,
                ],
                'prices'        => $eventDetails['prices'] ?? [],
                'no_fee_prices' => $eventDetails['no_fee_prices'] ?? [],
                'is_no_fee'     => $eventDetails['is_no_fee'] ?? FALSE,
                'availability'  => $this->determineStatus($eventDetails),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get TickPick event tickets', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get scraping headers
     *
     * @return array<string,string>
     */
    /**
     * Get  headers
     */
    protected function getHeaders(): array
    {
        return $this->scrapingHeaders;
    }

    /**
     * Search events via scraping
     *
     * @param array<string,mixed> $criteria
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

            if (!$response->successful()) {
                throw new Exception('Failed to fetch search results from TickPick');
            }

            return $this->parseSearchResultsHtml($response->body());
        } catch (Exception $e) {
            Log::error('TickPick scraping search failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Build scraping search URL
     *
     * @param array<string,mixed> $criteria
     */
    /**
     * BuildScrapingSearchUrl
     */
    protected function buildScrapingSearchUrl(array $criteria): string
    {
        $baseUrl = 'https://www.tickpick.com/buy-tickets';
        $params = [];

        if (isset($criteria['q'])) {
            $params['search'] = urlencode($criteria['q']);
        }

        if (isset($criteria['city'])) {
            $params['location'] = urlencode($criteria['city']);
        }

        if (isset($criteria['date_start'])) {
            $params['date_from'] = $criteria['date_start'];
        }

        if (isset($criteria['date_end'])) {
            $params['date_to'] = $criteria['date_end'];
        }

        if (isset($criteria['category'])) {
            $params['category'] = $criteria['category'];
        }

        // TickPick sorting options
        $params['sort'] = 'date';
        $params['limit'] = min(50, $criteria['per_page'] ?? 25);

        return $baseUrl . '?' . http_build_query($params);
    }

    /**
     * Parse search results from HTML
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

            // TickPick event cards - they use various CSS classes
            $eventNodes = $xpath->query('//div[contains(@class, "event-card")] | //div[contains(@class, "search-item")] | //article[contains(@class, "event")] | //div[contains(@class, "ticket-listing")]');

            if ($eventNodes !== FALSE) {
                foreach ($eventNodes as $eventNode) {
                    $event = $this->parseEventCard($xpath, $eventNode);
                    if (!empty($event['name'])) {
                        $events[] = $event;
                    }
                }
            }

            // Alternative approach if no events found
            if (empty($events)) {
                $linkNodes = $xpath->query('//a[contains(@href, "/buy-") and contains(@href, "-tickets")]');
                foreach ($linkNodes as $linkNode) {
                    $event = $this->parseEventFromLink($xpath, $linkNode);
                    if (!empty($event['name'])) {
                        $events[] = $event;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to parse TickPick search results HTML', [
                'error' => $e->getMessage(),
            ]);
        }

        return $events;
    }

    /**
     * ParseEventCard
     *
     * @param mixed $eventNode
     */
    protected function parseEventCard(DOMXPath $xpath, $eventNode): array
    {
        $event = [
            'platform'   => 'tickpick',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name - try multiple selectors
            $nameNode = $xpath->query('.//h2 | .//h3 | .//h4 | .//span[contains(@class, "title")] | .//a[contains(@class, "event-title")] | .//div[contains(@class, "event-name")]', $eventNode)->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNode = $xpath->query('.//a[contains(@href, "/buy-")] | .//a[contains(@href, "-tickets")]', $eventNode)->item(0);
            if ($linkNode && $linkNode->hasAttribute('href')) {
                $event['url'] = $this->normalizeUrl($linkNode->getAttribute('href'));
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $dateNode = $xpath->query('.//span[contains(@class, "date")] | .//div[contains(@class, "date")] | .//time | .//div[contains(@class, "event-date")]', $eventNode)->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue
            $venueNode = $xpath->query('.//span[contains(@class, "venue")] | .//div[contains(@class, "venue")] | .//p[contains(@class, "venue")]', $eventNode)->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location/City
            $locationNode = $xpath->query('.//span[contains(@class, "location")] | .//div[contains(@class, "city")] | .//span[contains(@class, "city")]', $eventNode)->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price information - TickPick emphasizes no-fee pricing
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")] | .//*[contains(text(), "$")]', $eventNode);
            $prices = [];
            $noFeePrices = [];

            foreach ($priceNodes as $priceNode) {
                $priceText = trim($priceNode->textContent);
                if (preg_match('/\$[\d,]+/', $priceText)) {
                    $prices[] = $priceText;

                    // Check if this is marked as no-fee price
                    $parentText = $priceNode->parentNode ? trim($priceNode->parentNode->textContent) : '';
                    if (stripos($parentText, 'no fee') !== FALSE || stripos($parentText, 'all-in') !== FALSE) {
                        $noFeePrices[] = $priceText;
                    }
                }
            }

            $event['prices'] = array_unique($prices);
            $event['no_fee_prices'] = array_unique($noFeePrices);
            $event['price_range'] = !empty($prices) ? implode(' - ', $prices) : '';

            // Extract min/max prices (both regular and no-fee)
            $this->extractPriceRange($event, $prices);
            $this->extractNoFeePriceRange($event, $noFeePrices);

            // Number of tickets available
            $ticketCountNode = $xpath->query('.//span[contains(@class, "available")] | .//span[contains(text(), "ticket")] | .//div[contains(@class, "quantity")]', $eventNode)->item(0);
            if ($ticketCountNode) {
                $ticketText = trim($ticketCountNode->textContent);
                if (preg_match('/(\d+)\s*(?:ticket|listing)s?\s*(?:available|from)?/i', $ticketText, $matches)) {
                    $event['ticket_count'] = (int) ($matches[1]);
                }
            }

            // Check for "No Fees" badge or indicator
            $noFeeNode = $xpath->query('.//span[contains(text(), "No Fee")] | .//div[contains(text(), "All-In")] | .//span[contains(@class, "no-fee")]', $eventNode)->item(0);
            $event['is_no_fee'] = $noFeeNode !== NULL;
        } catch (Exception $e) {
            Log::warning('Failed to parse TickPick event card', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * ParseEventFromLink
     *
     * @param mixed $linkNode
     */
    protected function parseEventFromLink(DOMXPath $xpath, $linkNode): array
    {
        $event = [
            'platform'   => 'tickpick',
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

            // Extract event name from URL if text is empty
            if (empty($event['name']) && !empty($event['url'])) {
                $event['name'] = $this->extractEventNameFromUrl($event['url']);
            }

            // Look for date/venue info in parent/sibling nodes
            $parentNode = $linkNode->parentNode;
            if ($parentNode) {
                $dateNode = $xpath->query('.//span[contains(@class, "date")] | .//time', $parentNode)->item(0);
                if ($dateNode) {
                    $event['date'] = trim($dateNode->textContent);
                    $event['parsed_date'] = $this->parseEventDate($event['date']);
                }

                $venueNode = $xpath->query('.//span[contains(@class, "venue")]', $parentNode)->item(0);
                if ($venueNode) {
                    $event['venue'] = trim($venueNode->textContent);
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed to parse TickPick event from link', [
                'error' => $e->getMessage(),
            ]);
        }

        return $event;
    }

    /**
     * Get  event via scraping
     */
    protected function getEventViaScraping(string $eventId): array
    {
        try {
            // TickPick URLs are typically /buy-{event-name}-tickets/{eventId}
            $eventUrl = $this->buildEventUrl($eventId);

            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($eventUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch event details from TickPick');
            }

            return $this->parseEventDetailsHtml($response->body(), $eventId);
        } catch (Exception $e) {
            Log::error('TickPick event scraping failed', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * BuildEventUrl
     */
    protected function buildEventUrl(string $eventId): string
    {
        // If eventId contains a URL fragment, use it directly
        if (strpos($eventId, '/') !== FALSE) {
            return $this->normalizeUrl($eventId);
        }

        // Otherwise construct a generic event URL
        return "https://www.tickpick.com/buy-tickets/{$eventId}";
    }

    /**
     * ParseEventDetailsHtml
     */
    protected function parseEventDetailsHtml(string $html, string $eventId): array
    {
        $event = [
            'id'         => $eventId,
            'platform'   => 'tickpick',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Event name
            $nameNode = $xpath->query('//h1 | //title')->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Clean up the title if it contains site name
            if (strpos($event['name'], '|') !== FALSE) {
                $event['name'] = trim(explode('|', $event['name'])[0]);
            }

            // Remove "Tickets" suffix if present
            $event['name'] = preg_replace('/\s+Tickets?\s*$/i', '', $event['name']);

            // Event date and time
            $dateNode = $xpath->query('//span[contains(@class, "event-date")] | //div[contains(@class, "date")] | //time')->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue information
            $venueNode = $xpath->query('//span[contains(@class, "venue")] | //div[contains(@class, "venue")] | //h2[contains(@class, "venue")]')->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location
            $locationNode = $xpath->query('//span[contains(@class, "location")] | //address | //div[contains(@class, "city")]')->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price data from ticket listings
            $listingNodes = $xpath->query('//div[contains(@class, "listing")] | //div[contains(@class, "ticket-row")] | //tr[contains(@class, "ticket")] | //div[contains(@class, "price-row")]');
            $prices = [];
            $noFeePrices = [];

            foreach ($listingNodes as $listingNode) {
                $priceNode = $xpath->query('.//*[contains(@class, "price")] | .//*[contains(text(), "$")]', $listingNode)->item(0);
                if ($priceNode && preg_match('/\$[\d,]+/', $priceNode->textContent)) {
                    $price = trim($priceNode->textContent);
                    $prices[] = $price;

                    // TickPick specializes in no-fee pricing - check if this listing is marked as such
                    $listingText = strtolower($listingNode->textContent);
                    if (strpos($listingText, 'no fee') !== FALSE || strpos($listingText, 'all-in') !== FALSE || strpos($listingText, 'final price') !== FALSE) {
                        $noFeePrices[] = $price;
                    }
                }
            }

            $event['available_listings'] = count($listingNodes);
            $event['prices'] = array_unique($prices);
            $event['no_fee_prices'] = array_unique($noFeePrices);

            $this->extractPriceRange($event, $prices);
            $this->extractNoFeePriceRange($event, $noFeePrices);

            // Event description
            $descNode = $xpath->query('//div[contains(@class, "description")] | //div[contains(@class, "event-info")] | //section[contains(@class, "about")]')->item(0);
            $event['description'] = $descNode ? trim($descNode->textContent) : '';

            // Category/genre
            $categoryNode = $xpath->query('//span[contains(@class, "category")] | //div[contains(@class, "genre")] | //span[contains(@class, "sport")]')->item(0);
            $event['category'] = $categoryNode ? trim($categoryNode->textContent) : '';

            // Check if this is a no-fee event (TickPick's main selling point)
            $noFeeIndicators = $xpath->query('//*[contains(text(), "No Fee") or contains(text(), "All-In") or contains(text(), "Final Price")]');
            $event['is_no_fee'] = $noFeeIndicators->length > 0;
        } catch (Exception $e) {
            Log::error('Failed to parse TickPick event details HTML', [
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);
        }

        return $event;
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
            'country'             => 'United States', // TickPick is primarily US-based
            'url'                 => $eventData['url'] ?? '',
            'price_min'           => $eventData['price_min'] ?? NULL,
            'price_max'           => $eventData['price_max'] ?? NULL,
            'currency'            => 'USD', // TickPick uses USD
            'availability_status' => $this->mapAvailabilityStatus($this->determineStatus($eventData)),
            'ticket_count'        => $eventData['ticket_count'] ?? $eventData['available_listings'] ?? NULL,
            'image_url'           => $eventData['image_url'] ?? NULL,
            'description'         => $eventData['description'] ?? '',

            // TickPick Platform-Specific Mappings (No-Fee Pricing)
            'no_fee_pricing'        => $this->extractNoFeePricing($eventData),
            'is_no_fee_available'   => $eventData['is_no_fee'] ?? FALSE,
            'final_price_guarantee' => $eventData['is_no_fee'] ?? FALSE,
            'fees_included'         => $eventData['is_no_fee'] ?? FALSE,
            'transparent_pricing'   => TRUE, // TickPick's key differentiator

            // Additional metadata
            'category' => $eventData['category'] ?? '',
            'platform' => 'tickpick',
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

        if (!empty($eventData['ticket_count']) && $eventData['ticket_count'] > 0) {
            return 'onsale';
        }

        if (!empty($eventData['available_listings']) && $eventData['available_listings'] > 0) {
            return 'onsale';
        }

        return 'unknown';
    }

    /**
     * ExtractCity
     */
    protected function extractCity(string $location): string
    {
        if (empty($location)) {
            return 'Unknown City';
        }

        // Extract city from location string like "New York, NY" or "Los Angeles, CA"
        if (preg_match('/^([^,]+),?\s*[A-Z]{2}?/', $location, $matches)) {
            return trim($matches[1]);
        }

        return $location ?: 'Unknown City';
    }

    /**
     * NormalizeUrl
     */
    protected function normalizeUrl(string $url): string
    {
        if (strpos($url, 'http') !== 0) {
            return 'https://www.tickpick.com' . $url;
        }

        return $url;
    }

    /**
     * ExtractEventIdFromUrl
     */
    protected function extractEventIdFromUrl(string $url): ?string
    {
        // TickPick URLs are like /buy-{event-name}-tickets/{id} or contain numeric IDs
        if (preg_match('/\/(\d+)$/', $url, $matches)) {
            return $matches[1];
        }

        // If no numeric ID, use the URL path as ID
        if (preg_match('/\/buy-([^\/]+)/', $url, $matches)) {
            return $matches[1];
        }

        return NULL;
    }

    /**
     * ExtractEventNameFromUrl
     */
    protected function extractEventNameFromUrl(string $url): string
    {
        // Extract event name from TickPick URL format: /buy-{event-name}-tickets
        if (preg_match('/\/buy-(.+?)-tickets/', $url, $matches)) {
            return ucwords(str_replace('-', ' ', $matches[1]));
        }

        return '';
    }

    /**
     * ExtractPriceRange
     *
     * @param mixed $event
     */
    protected function extractPriceRange(array &$event, array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $numericPrices = [];
        foreach ($prices as $price) {
            if (preg_match('/\$?([,\d]+(?:\.\d{2})?)/', $price, $matches)) {
                $numericPrices[] = (float) (str_replace(',', '', $matches[1]));
            }
        }

        if (!empty($numericPrices)) {
            $event['price_min'] = min($numericPrices);
            $event['price_max'] = max($numericPrices);
        }
    }

    /**
     * ExtractNoFeePriceRange
     *
     * @param mixed $event
     */
    protected function extractNoFeePriceRange(array &$event, array $noFeePrices): void
    {
        if (empty($noFeePrices)) {
            return;
        }

        $numericPrices = [];
        foreach ($noFeePrices as $price) {
            if (preg_match('/\$?([,\d]+(?:\.\d{2})?)/', $price, $matches)) {
                $numericPrices[] = (float) (str_replace(',', '', $matches[1]));
            }
        }

        if (!empty($numericPrices)) {
            $event['no_fee_price_min'] = min($numericPrices);
            $event['no_fee_price_max'] = max($numericPrices);
        }
    }

    /**
     * ParseEventDate
     */
    protected function parseEventDate(string $dateString): ?DateTime
    {
        if (empty($dateString)) {
            return NULL;
        }

        // Clean up the date string
        $dateString = trim(preg_replace('/\s+/', ' ', $dateString));

        // Remove common prefixes
        $dateString = preg_replace('/^(Event\s+date:?\s*|Date:?\s*)/i', '', $dateString);

        $formats = [
            'M j, Y \a\t g:i A',
            'F j, Y \a\t g:i A',
            'M j, Y g:i A',
            'F j, Y g:i A',
            'M j, Y',
            'F j, Y',
            'j M Y H:i',
            'j F Y H:i',
            'j M Y',
            'j F Y',
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
            } catch (Exception $e) {
                continue;
            }
        }

        try {
            return new DateTime($dateString);
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Extract no-fee pricing information specific to TickPick
     */
    /**
     * ExtractNoFeePricing
     */
    protected function extractNoFeePricing(array $eventData): array
    {
        return [
            'has_no_fee_tickets' => $eventData['is_no_fee'] ?? FALSE,
            'no_fee_price_min'   => $eventData['no_fee_price_min'] ?? NULL,
            'no_fee_price_max'   => $eventData['no_fee_price_max'] ?? NULL,
            'no_fee_prices'      => $eventData['no_fee_prices'] ?? [],
            'price_transparency' => [
                'all_fees_included'       => $eventData['is_no_fee'] ?? FALSE,
                'final_price_display'     => TRUE,
                'no_hidden_fees'          => TRUE,
                'fee_breakdown_available' => FALSE, // TickPick doesn't break down fees since there are none
            ],
            'savings_vs_competitors' => $this->calculateFeeSavings($eventData),
        ];
    }

    /**
     * Calculate potential fee savings compared to competitors
     */
    /**
     * CalculateFeeSavings
     */
    protected function calculateFeeSavings(array $eventData): array
    {
        $savings = [];

        if (isset($eventData['price_min']) && $eventData['price_min'] > 0) {
            // Estimate typical competitor fees (10-20% + $5-15 service fees)
            $estimatedFees = max(
                $eventData['price_min'] * 0.15, // 15% fee
                $eventData['price_min'] * 0.10 + 10, // 10% + $10 service fee
            );

            $savings = [
                'estimated_fee_savings' => round($estimatedFees, 2),
                'percentage_savings'    => round(($estimatedFees / ($eventData['price_min'] + $estimatedFees)) * 100, 1),
                'comparison_note'       => 'Estimated savings compared to typical competitor fees',
            ];
        }

        return $savings;
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
