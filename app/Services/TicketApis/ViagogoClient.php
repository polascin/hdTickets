<?php

namespace App\Services\TicketApis;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;
use Exception;

class ViagogoClient extends BaseApiClient
{
    protected $scrapingHeaders = [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Accept-Encoding' => 'gzip, deflate, br',
        'DNT' => '1',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
        'Cache-Control' => 'max-age=0',
    ];

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://www.viagogo.com';
    }

    protected function getHeaders(): array
    {
        return $this->scrapingHeaders;
    }

    public function searchEvents(array $criteria): array
    {
        return $this->searchEventsViaScraping($criteria);
    }

    protected function searchEventsViaScraping(array $criteria): array
    {
        try {
            $searchUrl = $this->buildScrapingSearchUrl($criteria);
            
            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($searchUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch search results from Viagogo');
            }

            return $this->parseSearchResultsHtml($response->body());
        } catch (Exception $e) {
            Log::error('Viagogo scraping search failed', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

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

    protected function parseSearchResultsHtml(string $html): array
    {
        $events = [];
        
        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Viagogo event cards - they use various CSS classes
            $eventNodes = $xpath->query('//div[contains(@class, "event-card")] | //div[contains(@class, "search-result")] | //article[contains(@class, "event")] | //div[contains(@class, "listing-item")]');

            foreach ($eventNodes as $eventNode) {
                $event = $this->parseEventCard($xpath, $eventNode);
                if (!empty($event['name'])) {
                    $events[] = $event;
                }
            }

            // If no events found with above selectors, try alternative approach
            if (empty($events)) {
                $linkNodes = $xpath->query('//a[contains(@href, "/event/")]');
                foreach ($linkNodes as $linkNode) {
                    $event = $this->parseEventFromLink($xpath, $linkNode);
                    if (!empty($event['name'])) {
                        $events[] = $event;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to parse Viagogo search results HTML', [
                'error' => $e->getMessage()
            ]);
        }

        return $events;
    }

    protected function parseEventCard(DOMXPath $xpath, $eventNode): array
    {
        $event = [
            'platform' => 'viagogo',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name - try multiple selectors
            $nameNode = $xpath->query('.//h2 | .//h3 | .//h4 | .//span[contains(@class, "title")] | .//a[contains(@class, "event-title")]', $eventNode)->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNode = $xpath->query('.//a[contains(@href, "/event/")] | .//a[contains(@href, "/tickets/")]', $eventNode)->item(0);
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
            $venueNode = $xpath->query('.//span[contains(@class, "venue")] | .//div[contains(@class, "venue")] | .//p[contains(@class, "venue")]', $eventNode)->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location/City
            $locationNode = $xpath->query('.//span[contains(@class, "location")] | .//div[contains(@class, "city")] | .//span[contains(@class, "city")]', $eventNode)->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price information
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")] | .//*[contains(text(), "€")] | .//*[contains(text(), "$")] | .//*[contains(text(), "£")]', $eventNode);
            $prices = [];
            foreach ($priceNodes as $priceNode) {
                $priceText = trim($priceNode->textContent);
                if (preg_match('/[€$£][\d,]+/', $priceText)) {
                    $prices[] = $priceText;
                }
            }
            $event['prices'] = array_unique($prices);
            $event['price_range'] = !empty($prices) ? implode(' - ', $prices) : '';

            // Extract min/max prices
            $this->extractPriceRange($event, $prices);

            // Number of tickets available
            $ticketCountNode = $xpath->query('.//span[contains(@class, "available")] | .//span[contains(text(), "ticket")] | .//span[contains(text(), "listing")]', $eventNode)->item(0);
            if ($ticketCountNode) {
                $ticketText = trim($ticketCountNode->textContent);
                if (preg_match('/(\d+)\s*(?:ticket|listing)s?\s*(?:available|from)?/i', $ticketText, $matches)) {
                    $event['ticket_count'] = intval($matches[1]);
                }
            }

        } catch (Exception $e) {
            Log::warning('Failed to parse Viagogo event card', [
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    protected function parseEventFromLink(DOMXPath $xpath, $linkNode): array
    {
        $event = [
            'platform' => 'viagogo',
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
            Log::warning('Failed to parse Viagogo event from link', [
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    public function getEvent(string $eventId): array
    {
        return $this->getEventViaScraping($eventId);
    }

    protected function getEventViaScraping(string $eventId): array
    {
        try {
            $eventUrl = "https://www.viagogo.com/event/{$eventId}";
            
            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($eventUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch event details from Viagogo');
            }

            return $this->parseEventDetailsHtml($response->body(), $eventId);
        } catch (Exception $e) {
            Log::error('Viagogo event scraping failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function parseEventDetailsHtml(string $html, string $eventId): array
    {
        $event = [
            'id' => $eventId,
            'platform' => 'viagogo',
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
            if (strpos($event['name'], '|') !== false) {
                $event['name'] = trim(explode('|', $event['name'])[0]);
            }

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
            $listingNodes = $xpath->query('//div[contains(@class, "listing")] | //div[contains(@class, "ticket-row")] | //tr[contains(@class, "ticket")]');
            $prices = [];
            foreach ($listingNodes as $listingNode) {
                $priceNode = $xpath->query('.//*[contains(@class, "price")] | .//*[contains(text(), "€")] | .//*[contains(text(), "$")] | .//*[contains(text(), "£")]', $listingNode)->item(0);
                if ($priceNode && preg_match('/[€$£][\d,]+/', $priceNode->textContent)) {
                    $prices[] = trim($priceNode->textContent);
                }
            }

            $event['available_listings'] = count($listingNodes);
            $event['prices'] = array_unique($prices);
            $this->extractPriceRange($event, $prices);

            // Event description
            $descNode = $xpath->query('//div[contains(@class, "description")] | //div[contains(@class, "event-info")] | //section[contains(@class, "about")]')->item(0);
            $event['description'] = $descNode ? trim($descNode->textContent) : '';

            // Category/genre
            $categoryNode = $xpath->query('//span[contains(@class, "category")] | //div[contains(@class, "genre")]')->item(0);
            $event['category'] = $categoryNode ? trim($categoryNode->textContent) : '';

        } catch (Exception $e) {
            Log::error('Failed to parse Viagogo event details HTML', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    public function getVenue(string $venueId): array
    {
        // Viagogo doesn't have a direct venue endpoint, return basic info
        return [
            'id' => $venueId,
            'name' => 'Unknown Venue',
            'city' => 'Unknown City',
            'country' => 'Unknown Country',
        ];
    }

    protected function transformEventData(array $eventData): array
    {
        return [
            // Standard Fields
            'id' => $eventData['id'] ?? null,
            'name' => $eventData['name'] ?? 'Unnamed Event',
            'date' => $eventData['parsed_date'] ? $eventData['parsed_date']->format('Y-m-d') : null,
            'time' => $eventData['parsed_date'] ? $eventData['parsed_date']->format('H:i:s') : null,
            'venue' => $eventData['venue'] ?? 'Unknown Venue',
            'city' => $this->extractCity($eventData['location'] ?? ''),
            'country' => $this->extractCountry($eventData['location'] ?? ''),
            'url' => $eventData['url'] ?? '',
            'price_min' => $eventData['price_min'] ?? null,
            'price_max' => $eventData['price_max'] ?? null,
            'currency' => $this->determineCurrency($eventData),
            'availability_status' => $this->mapAvailabilityStatus($this->determineStatus($eventData)),
            'ticket_count' => $eventData['ticket_count'] ?? $eventData['available_listings'] ?? null,
            'image_url' => $eventData['image_url'] ?? null,
            'description' => $eventData['description'] ?? '',
            
            // Viagogo Platform-Specific Mappings
            'guarantee_info' => $this->extractGuaranteeInfo($eventData),
            'seller_type' => $eventData['seller_type'] ?? null,
            'delivery_method' => $eventData['delivery_method'] ?? null,
            'guarantee_coverage' => $eventData['guarantee_coverage'] ?? null,
            'restrictions' => $eventData['restrictions'] ?? [],
            
            // Additional metadata
            'category' => $eventData['category'] ?? '',
            'platform' => 'viagogo',
            'raw_data' => $eventData, // Store original data for debugging
        ];
    }

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

    protected function extractCity(string $location): string
    {
        if (empty($location)) {
            return 'Unknown City';
        }

        // Try to extract city from various formats
        // "Paris, France" -> "Paris"
        // "London, UK" -> "London"
        // "New York, NY, USA" -> "New York"
        $parts = explode(',', $location);
        return trim($parts[0]) ?: 'Unknown City';
    }

    protected function extractCountry(string $location): string
    {
        if (empty($location)) {
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

    protected function normalizeUrl(string $url): string
    {
        if (strpos($url, 'http') !== 0) {
            return 'https://www.viagogo.com' . $url;
        }
        return $url;
    }

    protected function extractEventIdFromUrl(string $url): ?string
    {
        // Viagogo URLs can be like /event/12345 or /tickets/some-event-name/e-12345
        if (preg_match('/\/(?:event|e)-?(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function extractPriceRange(array &$event, array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $numericPrices = [];
        foreach ($prices as $price) {
            // Support multiple currencies
            if (preg_match('/[€$£]?([,\d]+(?:\.\d{2})?)/', $price, $matches)) {
                $numericPrices[] = floatval(str_replace(',', '', $matches[1]));
            }
        }

        if (!empty($numericPrices)) {
            $event['price_min'] = min($numericPrices);
            $event['price_max'] = max($numericPrices);
        }
    }

    protected function parseEventDate(string $dateString): ?\DateTime
    {
        if (empty($dateString)) {
            return null;
        }

        // Clean up the date string
        $dateString = trim(preg_replace('/\s+/', ' ', $dateString));
        
        // Remove common prefixes
        $dateString = preg_replace('/^(Event\s+date:?\s*|Date:?\s*)/i', '', $dateString);
        
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
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date) {
                    return $date;
                }
            } catch (Exception $e) {
                continue;
            }
        }

        try {
            return new \DateTime($dateString);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Get available tickets for an event
     */
    public function getEventTickets(string $eventId, array $filters = []): array
    {
        try {
            $eventDetails = $this->getEvent($eventId);
            
            return [
                'event_id' => $eventId,
                'total_listings' => $eventDetails['available_listings'] ?? 0,
                'price_range' => [
                    'min' => $eventDetails['price_min'] ?? null,
                    'max' => $eventDetails['price_max'] ?? null,
                ],
                'prices' => $eventDetails['prices'] ?? [],
                'availability' => $this->determineStatus($eventDetails),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get Viagogo event tickets', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Extract guarantee information specific to Viagogo
     */
    protected function extractGuaranteeInfo(array $eventData): array
    {
        $guaranteeInfo = [
            'has_guarantee' => true, // Viagogo always provides guarantees
            'guarantee_type' => 'full_guarantee',
            'coverage' => []
        ];
        
        // Extract specific guarantee coverage from event data
        if (isset($eventData['guarantee_coverage'])) {
            $guaranteeInfo['coverage'] = $eventData['guarantee_coverage'];
        } else {
            // Default Viagogo guarantees
            $guaranteeInfo['coverage'] = [
                'authenticity' => 'Guaranteed authentic tickets',
                'delivery' => 'Guaranteed delivery or full refund',
                'event_cancellation' => 'Full refund if event is cancelled',
                'replacement' => 'Replacement tickets if there are issues'
            ];
        }
        
        // Check for specific guarantee indicators
        if (isset($eventData['description']) && !empty($eventData['description'])) {
            $description = strtolower($eventData['description']);
            
            if (strpos($description, '100% guarantee') !== false) {
                $guaranteeInfo['guarantee_type'] = '100_percent_guarantee';
            }
            
            if (strpos($description, 'instant download') !== false) {
                $guaranteeInfo['coverage']['instant_download'] = 'Instant download available';
            }
        }
        
        return $guaranteeInfo;
    }
    
    /**
     * Determine currency from price data
     */
    protected function determineCurrency(array $eventData): string
    {
        // Check if currency is already specified
        if (isset($eventData['currency'])) {
            return $eventData['currency'];
        }
        
        // Extract currency from prices
        if (isset($eventData['prices']) && !empty($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (strpos($price, '€') !== false) {
                    return 'EUR';
                }
                if (strpos($price, '$') !== false) {
                    return 'USD';
                }
                if (strpos($price, '£') !== false) {
                    return 'GBP';
                }
                if (strpos($price, 'Kč') !== false) {
                    return 'CZK';
                }
            }
        }
        
        // Default based on location
        $location = $eventData['location'] ?? '';
        if (!empty($location)) {
            $location = strtolower($location);
            
            if (strpos($location, 'uk') !== false || strpos($location, 'united kingdom') !== false) {
                return 'GBP';
            }
            if (strpos($location, 'us') !== false || strpos($location, 'united states') !== false) {
                return 'USD';
            }
            if (strpos($location, 'canada') !== false) {
                return 'CAD';
            }
        }
        
        return 'EUR'; // Viagogo default
    }
    
    /**
     * Map internal status to standardized availability status
     */
    protected function mapAvailabilityStatus(string $internalStatus): string
    {
        $statusMap = [
            'onsale' => 'available',
            'soldout' => 'sold_out',
            'presale' => 'presale',
            'offsale' => 'not_available',
            'cancelled' => 'cancelled',
            'postponed' => 'postponed',
            'unknown' => 'unknown'
        ];
        
        return $statusMap[$internalStatus] ?? 'unknown';
    }
}
