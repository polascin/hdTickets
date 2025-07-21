<?php

namespace App\Services\TicketApis;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;
use Exception;

class FunZoneClient extends BaseApiClient
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
        $this->baseUrl = 'https://www.funzone.sk'; // Assuming it's a Slovak ticket platform
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
                throw new Exception('Failed to fetch search results from FunZone');
            }

            return $this->parseSearchResultsHtml($response->body());
        } catch (Exception $e) {
            Log::error('FunZone scraping search failed', [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function buildScrapingSearchUrl(array $criteria): string
    {
        $baseUrl = 'https://www.funzone.sk/events';
        $params = [];

        if (isset($criteria['q'])) {
            $params['search'] = urlencode($criteria['q']);
        }

        if (isset($criteria['city'])) {
            $params['city'] = urlencode($criteria['city']);
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

        if (isset($criteria['venue'])) {
            $params['venue'] = urlencode($criteria['venue']);
        }

        // FunZone sorting options
        $params['sort'] = 'date';
        $params['limit'] = min(50, $criteria['per_page'] ?? 25);

        $url = $baseUrl;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    protected function parseSearchResultsHtml(string $html): array
    {
        $events = [];
        
        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // FunZone event cards - try various selectors
            $eventNodes = $xpath->query('//div[contains(@class, "event-card")] | //div[contains(@class, "event-item")] | //article[contains(@class, "event")] | //div[contains(@class, "listing")] | //div[contains(@class, "product-item")]');

            foreach ($eventNodes as $eventNode) {
                $event = $this->parseEventCard($xpath, $eventNode);
                if (!empty($event['name'])) {
                    $events[] = $event;
                }
            }

            // Alternative approach if no events found with main selectors
            if (empty($events)) {
                $linkNodes = $xpath->query('//a[contains(@href, "/event/") or contains(@href, "/events/") or contains(@href, "/show/")]');
                foreach ($linkNodes as $linkNode) {
                    $event = $this->parseEventFromLink($xpath, $linkNode);
                    if (!empty($event['name'])) {
                        $events[] = $event;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to parse FunZone search results HTML', [
                'error' => $e->getMessage()
            ]);
        }

        return $events;
    }

    protected function parseEventCard(DOMXPath $xpath, $eventNode): array
    {
        $event = [
            'platform' => 'funzone',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name - try multiple selectors
            $nameNode = $xpath->query('.//h1 | .//h2 | .//h3 | .//h4 | .//span[contains(@class, "title")] | .//a[contains(@class, "event-title")] | .//div[contains(@class, "event-name")] | .//span[contains(@class, "name")]', $eventNode)->item(0);
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNode = $xpath->query('.//a[contains(@href, "/event/") or contains(@href, "/show/") or contains(@href, "/events/")]', $eventNode)->item(0);
            if ($linkNode && $linkNode->hasAttribute('href')) {
                $event['url'] = $this->normalizeUrl($linkNode->getAttribute('href'));
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $dateNode = $xpath->query('.//span[contains(@class, "date")] | .//div[contains(@class, "date")] | .//time | .//div[contains(@class, "event-date")] | .//span[contains(@class, "datum")]', $eventNode)->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue
            $venueNode = $xpath->query('.//span[contains(@class, "venue")] | .//div[contains(@class, "venue")] | .//p[contains(@class, "venue")] | .//span[contains(@class, "miesto")]', $eventNode)->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Location/City
            $locationNode = $xpath->query('.//span[contains(@class, "location")] | .//div[contains(@class, "city")] | .//span[contains(@class, "city")] | .//span[contains(@class, "mesto")]', $eventNode)->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price information - support Slovak currency (EUR) and common formats
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")] | .//span[contains(@class, "cena")] | .//*[contains(text(), "€")] | .//*[contains(text(), "EUR")] | .//*[contains(text(), "Kč")]', $eventNode);
            $prices = [];
            
            foreach ($priceNodes as $priceNode) {
                $priceText = trim($priceNode->textContent);
                if (preg_match('/[€][\d,\s]+|[\d,\s]+\s*€|[\d,\s]+\s*EUR|[\d,\s]+\s*Kč/i', $priceText)) {
                    $prices[] = $priceText;
                }
            }
            
            $event['prices'] = array_unique($prices);
            $event['price_range'] = !empty($prices) ? implode(' - ', $prices) : '';

            // Extract min/max prices
            $this->extractPriceRange($event, $prices);

            // Number of tickets available
            $ticketCountNode = $xpath->query('.//span[contains(@class, "available")] | .//span[contains(text(), "ticket")] | .//span[contains(text(), "lístok")] | .//div[contains(@class, "quantity")]', $eventNode)->item(0);
            if ($ticketCountNode) {
                $ticketText = trim($ticketCountNode->textContent);
                if (preg_match('/(\d+)\s*(?:ticket|listing|lístok|voľný)s?\s*(?:available|from)?/i', $ticketText, $matches)) {
                    $event['ticket_count'] = intval($matches[1]);
                }
            }

            // Event category/genre
            $categoryNode = $xpath->query('.//span[contains(@class, "category")] | .//div[contains(@class, "genre")] | .//span[contains(@class, "typ")]', $eventNode)->item(0);
            $event['category'] = $categoryNode ? trim($categoryNode->textContent) : '';

            // Event description snippet
            $descNode = $xpath->query('.//div[contains(@class, "description")] | .//p[contains(@class, "desc")] | .//div[contains(@class, "summary")]', $eventNode)->item(0);
            $event['description_snippet'] = $descNode ? trim($descNode->textContent) : '';

        } catch (Exception $e) {
            Log::warning('Failed to parse FunZone event card', [
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    protected function parseEventFromLink(DOMXPath $xpath, $linkNode): array
    {
        $event = [
            'platform' => 'funzone',
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

                $venueNode = $xpath->query('.//span[contains(@class, "venue")] | .//span[contains(@class, "miesto")]', $parentNode)->item(0);
                if ($venueNode) {
                    $event['venue'] = trim($venueNode->textContent);
                }
            }

        } catch (Exception $e) {
            Log::warning('Failed to parse FunZone event from link', [
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
            $eventUrl = $this->buildEventUrl($eventId);
            
            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($eventUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch event details from FunZone');
            }

            return $this->parseEventDetailsHtml($response->body(), $eventId);
        } catch (Exception $e) {
            Log::error('FunZone event scraping failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function buildEventUrl(string $eventId): string
    {
        // If eventId contains a URL fragment, use it directly
        if (strpos($eventId, '/') !== false) {
            return $this->normalizeUrl($eventId);
        }
        
        // Try different URL patterns for FunZone
        return "https://www.funzone.sk/event/{$eventId}";
    }

    protected function parseEventDetailsHtml(string $html, string $eventId): array
    {
        $event = [
            'id' => $eventId,
            'platform' => 'funzone',
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
            if (strpos($event['name'], '-') !== false) {
                $parts = explode('-', $event['name']);
                $event['name'] = trim($parts[0]);
            }

            // Event date and time
            $dateNode = $xpath->query('//span[contains(@class, "event-date")] | //div[contains(@class, "date")] | //time | //span[contains(@class, "datum")]')->item(0);
            if ($dateNode) {
                $event['date'] = trim($dateNode->textContent);
                $event['parsed_date'] = $this->parseEventDate($event['date']);
            }

            // Venue information
            $venueNode = $xpath->query('//span[contains(@class, "venue")] | //div[contains(@class, "venue")] | //h2[contains(@class, "venue")] | //span[contains(@class, "miesto")]')->item(0);
            $event['venue'] = $venueNode ? trim($venueNode->textContent) : '';

            // Venue details - try to get more detailed venue info
            $venueDetailsNode = $xpath->query('//div[contains(@class, "venue-details")] | //section[contains(@class, "venue-info")]')->item(0);
            if ($venueDetailsNode) {
                $event['venue_details'] = trim($venueDetailsNode->textContent);
            }

            // Location/Address
            $locationNode = $xpath->query('//span[contains(@class, "location")] | //address | //div[contains(@class, "city")] | //span[contains(@class, "adresa")]')->item(0);
            $event['location'] = $locationNode ? trim($locationNode->textContent) : '';

            // Price data from ticket listings
            $listingNodes = $xpath->query('//div[contains(@class, "ticket-listing")] | //div[contains(@class, "price-row")] | //tr[contains(@class, "ticket")] | //div[contains(@class, "cenova-kategoria")]');
            $prices = [];
            $priceCategories = [];
            
            foreach ($listingNodes as $listingNode) {
                $priceNode = $xpath->query('.//*[contains(@class, "price")] | .//*[contains(@class, "cena")] | .//*[contains(text(), "€")]', $listingNode)->item(0);
                if ($priceNode && preg_match('/[€][\d,\s]+|[\d,\s]+\s*€/i', $priceNode->textContent)) {
                    $price = trim($priceNode->textContent);
                    $prices[] = $price;
                    
                    // Try to get category/section name
                    $categoryNode = $xpath->query('.//*[contains(@class, "category")] | .//*[contains(@class, "section")]', $listingNode)->item(0);
                    if ($categoryNode) {
                        $priceCategories[] = [
                            'category' => trim($categoryNode->textContent),
                            'price' => $price
                        ];
                    }
                }
            }

            $event['available_listings'] = count($listingNodes);
            $event['prices'] = array_unique($prices);
            $event['price_categories'] = $priceCategories;
            
            $this->extractPriceRange($event, $prices);

            // Event description
            $descNode = $xpath->query('//div[contains(@class, "description")] | //div[contains(@class, "event-info")] | //section[contains(@class, "about")] | //div[contains(@class, "popis")]')->item(0);
            $event['description'] = $descNode ? trim($descNode->textContent) : '';

            // Event organizer
            $organizerNode = $xpath->query('//span[contains(@class, "organizer")] | //div[contains(@class, "organizer")] | //span[contains(@class, "organizator")]')->item(0);
            $event['organizer'] = $organizerNode ? trim($organizerNode->textContent) : '';

            // Category/genre
            $categoryNode = $xpath->query('//span[contains(@class, "category")] | //div[contains(@class, "genre")] | //span[contains(@class, "typ")]')->item(0);
            $event['category'] = $categoryNode ? trim($categoryNode->textContent) : '';

            // Event status
            $statusNode = $xpath->query('//span[contains(@class, "status")] | //div[contains(@class, "availability")]')->item(0);
            if ($statusNode) {
                $statusText = strtolower(trim($statusNode->textContent));
                if (strpos($statusText, 'vypredané') !== false || strpos($statusText, 'sold out') !== false) {
                    $event['status'] = 'soldout';
                } elseif (strpos($statusText, 'dostupné') !== false || strpos($statusText, 'available') !== false) {
                    $event['status'] = 'onsale';
                } else {
                    $event['status'] = 'unknown';
                }
            }

            // Event duration if available
            $durationNode = $xpath->query('//span[contains(@class, "duration")] | //div[contains(@class, "trvanie")]')->item(0);
            $event['duration'] = $durationNode ? trim($durationNode->textContent) : '';

        } catch (Exception $e) {
            Log::error('Failed to parse FunZone event details HTML', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    public function getVenue(string $venueId): array
    {
        try {
            $venueUrl = "https://www.funzone.sk/venue/{$venueId}";
            
            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($venueUrl);

            if (!$response->successful()) {
                return $this->getBasicVenueInfo($venueId);
            }

            return $this->parseVenueDetailsHtml($response->body(), $venueId);
        } catch (Exception $e) {
            Log::error('Failed to get FunZone venue details', [
                'venue_id' => $venueId,
                'error' => $e->getMessage()
            ]);
            return $this->getBasicVenueInfo($venueId);
        }
    }

    protected function parseVenueDetailsHtml(string $html, string $venueId): array
    {
        $venue = [
            'id' => $venueId,
            'platform' => 'funzone',
        ];

        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Venue name
            $nameNode = $xpath->query('//h1 | //title')->item(0);
            $venue['name'] = $nameNode ? trim($nameNode->textContent) : 'Unknown Venue';

            // Venue address
            $addressNode = $xpath->query('//address | //div[contains(@class, "address")] | //span[contains(@class, "adresa")]')->item(0);
            $venue['address'] = $addressNode ? trim($addressNode->textContent) : '';

            // City
            $cityNode = $xpath->query('//span[contains(@class, "city")] | //div[contains(@class, "city")] | //span[contains(@class, "mesto")]')->item(0);
            $venue['city'] = $cityNode ? trim($cityNode->textContent) : $this->extractCity($venue['address']);

            // Country - assume Slovakia for FunZone
            $venue['country'] = 'Slovakia';

            // Venue capacity
            $capacityNode = $xpath->query('//span[contains(@class, "capacity")] | //div[contains(@class, "kapacita")]')->item(0);
            if ($capacityNode && preg_match('/(\d+)/', $capacityNode->textContent, $matches)) {
                $venue['capacity'] = intval($matches[1]);
            }

            // Venue description
            $descNode = $xpath->query('//div[contains(@class, "description")] | //section[contains(@class, "about")]')->item(0);
            $venue['description'] = $descNode ? trim($descNode->textContent) : '';

            // Venue amenities
            $amenitiesNodes = $xpath->query('//ul[contains(@class, "amenities")] | //div[contains(@class, "facilities")]');
            $amenities = [];
            foreach ($amenitiesNodes as $amenitiesNode) {
                $items = $xpath->query('.//li', $amenitiesNode);
                foreach ($items as $item) {
                    $amenities[] = trim($item->textContent);
                }
            }
            $venue['amenities'] = $amenities;

        } catch (Exception $e) {
            Log::error('Failed to parse FunZone venue details HTML', [
                'venue_id' => $venueId,
                'error' => $e->getMessage()
            ]);
        }

        return $venue;
    }

    protected function getBasicVenueInfo(string $venueId): array
    {
        return [
            'id' => $venueId,
            'name' => 'Unknown Venue',
            'city' => 'Unknown City',
            'country' => 'Slovakia',
            'platform' => 'funzone',
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
            'country' => 'Slovakia', // FunZone is Slovak platform
            'url' => $eventData['url'] ?? '',
            'price_min' => $eventData['price_min'] ?? null,
            'price_max' => $eventData['price_max'] ?? null,
            'currency' => $this->determineCurrency($eventData),
            'availability_status' => $this->mapAvailabilityStatus($eventData['status'] ?? $this->determineStatus($eventData)),
            'ticket_count' => $eventData['ticket_count'] ?? $eventData['available_listings'] ?? null,
            'image_url' => $eventData['image_url'] ?? null,
            'description' => $eventData['description'] ?? $eventData['description_snippet'] ?? '',
            
            // FunZone Platform-Specific Mappings (Entertainment Categories)
            'entertainment_category' => $this->mapEntertainmentCategory($eventData['category'] ?? ''),
            'event_type' => $this->determineEventType($eventData),
            'age_restrictions' => $this->extractAgeRestrictions($eventData),
            'venue_details' => $eventData['venue_details'] ?? null,
            'organizer_info' => $this->extractOrganizerInfo($eventData),
            'duration_info' => $eventData['duration'] ?? null,
            'price_categories' => $eventData['price_categories'] ?? [],
            'slovak_specific' => [
                'region' => $this->extractSlovakRegion($eventData['location'] ?? ''),
                'venue_type' => $this->categorizeVenueType($eventData['venue'] ?? ''),
                'cultural_category' => $this->categorizeCulturalEvent($eventData)
            ],
            
            // Additional metadata
            'platform' => 'funzone',
            'raw_data' => $eventData, // Store original data for debugging
        ];
    }

    protected function determineStatus(array $eventData): string
    {
        if (isset($eventData['status'])) {
            return $eventData['status'];
        }

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

        // Slovak city patterns
        $location = trim($location);
        
        // Remove postal codes (Slovak format: 123 45 City)
        $location = preg_replace('/^\d{3}\s*\d{2}\s*/', '', $location);
        
        // Extract city from various formats
        if (preg_match('/^([^,]+),?/', $location, $matches)) {
            return trim($matches[1]);
        }
        
        return $location ?: 'Unknown City';
    }

    protected function normalizeUrl(string $url): string
    {
        if (strpos($url, 'http') !== 0) {
            return 'https://www.funzone.sk' . $url;
        }
        return $url;
    }

    protected function extractEventIdFromUrl(string $url): ?string
    {
        // FunZone URL patterns: /event/{id} or /show/{id}
        if (preg_match('/\/(?:event|show)\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // If URL contains slug, use the entire path segment as ID
        if (preg_match('/\/(?:event|show)\/([^\/\?]+)/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    protected function extractEventNameFromUrl(string $url): string
    {
        // Extract event name from URL slug
        if (preg_match('/\/(?:event|show)\/([^\/\?]+)/', $url, $matches)) {
            return ucwords(str_replace(['-', '_'], ' ', $matches[1]));
        }
        
        return '';
    }

    protected function extractPriceRange(array &$event, array $prices): void
    {
        if (empty($prices)) {
            return;
        }

        $numericPrices = [];
        foreach ($prices as $price) {
            // Support EUR currency
            if (preg_match('/([,\d]+(?:[.,]\d{2})?)\s*€|€\s*([,\d]+(?:[.,]\d{2})?)/', $price, $matches)) {
                $numericValue = $matches[1] ?? $matches[2] ?? '';
                $numericValue = str_replace([',', ' '], ['', ''], $numericValue);
                $numericValue = str_replace(',', '.', $numericValue); // Handle Slovak decimal format
                $numericPrices[] = floatval($numericValue);
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
        
        // Remove common Slovak prefixes
        $dateString = preg_replace('/^(Dátum:?\s*|Termín:?\s*)/i', '', $dateString);
        
        // Slovak date formats
        $formats = [
            'd.m.Y H:i',
            'd.m.Y',
            'd. m. Y H:i',
            'd. m. Y',
            'j.n.Y H:i',
            'j.n.Y',
            'j. n. Y H:i',
            'j. n. Y',
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
     * Get available tickets for an event with detailed pricing
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
                'price_categories' => $eventDetails['price_categories'] ?? [],
                'availability' => $this->determineStatus($eventDetails),
                'venue' => $eventDetails['venue'] ?? '',
                'venue_details' => $eventDetails['venue_details'] ?? '',
            ];
        } catch (Exception $e) {
            Log::error('Failed to get FunZone event tickets', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Map entertainment categories for Slovak events
     */
    protected function mapEntertainmentCategory(string $category): string
    {
        $categoryMap = [
            // Slovak to English mappings
            'koncert' => 'concert',
            'divadlo' => 'theater',
            'tanec' => 'dance',
            'opera' => 'opera',
            'balet' => 'ballet',
            'muzikál' => 'musical',
            'komedia' => 'comedy',
            'festival' => 'festival',
            'výstava' => 'exhibition',
            'šport' => 'sports',
            'futbal' => 'football',
            'hokej' => 'hockey',
            'basketbal' => 'basketball',
            'konferencia' => 'conference',
            'prednáška' => 'lecture',
            'workshop' => 'workshop',
            'stand-up' => 'comedy',
            'rock' => 'rock_concert',
            'pop' => 'pop_concert',
            'jazz' => 'jazz_concert',
            'klasická hudba' => 'classical_music',
            'detské' => 'children_show',
            'kino' => 'cinema'
        ];
        
        $category = strtolower(trim($category));
        return $categoryMap[$category] ?? 'other';
    }
    
    /**
     * Determine event type based on various factors
     */
    protected function determineEventType(array $eventData): string
    {
        $name = strtolower($eventData['name'] ?? '');
        $category = strtolower($eventData['category'] ?? '');
        $venue = strtolower($eventData['venue'] ?? '');
        
        // Check for keywords in name and category
        if (strpos($name, 'koncert') !== false || strpos($category, 'koncert') !== false) {
            return 'concert';
        }
        if (strpos($name, 'divadlo') !== false || strpos($venue, 'divadlo') !== false) {
            return 'theater';
        }
        if (strpos($name, 'festival') !== false || strpos($category, 'festival') !== false) {
            return 'festival';
        }
        if (strpos($venue, 'štadión') !== false || strpos($venue, 'aréna') !== false) {
            return 'sports';
        }
        if (strpos($name, 'výstava') !== false || strpos($category, 'výstava') !== false) {
            return 'exhibition';
        }
        
        return 'entertainment';
    }
    
    /**
     * Extract age restrictions from event data
     */
    protected function extractAgeRestrictions(array $eventData): ?array
    {
        $description = strtolower($eventData['description'] ?? '');
        $name = strtolower($eventData['name'] ?? '');
        
        $restrictions = [];
        
        // Check for age restrictions
        if (preg_match('/(\d+)\+/', $description . ' ' . $name, $matches)) {
            $restrictions['minimum_age'] = (int)$matches[1];
        }
        
        if (strpos($description, 'detské') !== false || strpos($name, 'detské') !== false) {
            $restrictions['target_audience'] = 'children';
            $restrictions['family_friendly'] = true;
        }
        
        if (strpos($description, '18+') !== false || strpos($name, '18+') !== false) {
            $restrictions['minimum_age'] = 18;
            $restrictions['adult_only'] = true;
        }
        
        return !empty($restrictions) ? $restrictions : null;
    }
    
    /**
     * Extract organizer information
     */
    protected function extractOrganizerInfo(array $eventData): ?array
    {
        $organizer = $eventData['organizer'] ?? null;
        
        if (empty($organizer)) {
            return null;
        }
        
        return [
            'name' => $organizer,
            'type' => $this->categorizeOrganizer($organizer),
            'contact' => null // Would need to extract from description if available
        ];
    }
    
    /**
     * Categorize organizer type
     */
    protected function categorizeOrganizer(string $organizer): string
    {
        $organizer = strtolower($organizer);
        
        if (strpos($organizer, 'divadlo') !== false) {
            return 'theater_company';
        }
        if (strpos($organizer, 'filharmónia') !== false || strpos($organizer, 'orchester') !== false) {
            return 'orchestra';
        }
        if (strpos($organizer, 'klub') !== false) {
            return 'club';
        }
        if (strpos($organizer, 'asociácia') !== false || strpos($organizer, 'spoločnosť') !== false) {
            return 'organization';
        }
        
        return 'other';
    }
    
    /**
     * Extract Slovak region from location
     */
    protected function extractSlovakRegion(string $location): string
    {
        $location = strtolower($location);
        
        $regions = [
            'bratislava' => 'Bratislavský kraj',
            'trnava' => 'Trnavský kraj',
            'trenčín' => 'Trenčiansky kraj',
            'nitra' => 'Nitriansky kraj',
            'žilina' => 'Žilinský kraj',
            'banská bystrica' => 'Banskobystrický kraj',
            'prešov' => 'Prešovský kraj',
            'košice' => 'Košický kraj'
        ];
        
        foreach ($regions as $city => $region) {
            if (strpos($location, $city) !== false) {
                return $region;
            }
        }
        
        return 'Unknown Region';
    }
    
    /**
     * Categorize venue type based on name
     */
    protected function categorizeVenueType(string $venue): string
    {
        $venue = strtolower($venue);
        
        if (strpos($venue, 'divadlo') !== false) {
            return 'theater';
        }
        if (strpos($venue, 'štadión') !== false) {
            return 'stadium';
        }
        if (strpos($venue, 'aréna') !== false) {
            return 'arena';
        }
        if (strpos($venue, 'hala') !== false) {
            return 'hall';
        }
        if (strpos($venue, 'club') !== false || strpos($venue, 'klub') !== false) {
            return 'club';
        }
        if (strpos($venue, 'kultúrny dom') !== false || strpos($venue, 'kd') !== false) {
            return 'cultural_center';
        }
        if (strpos($venue, 'park') !== false) {
            return 'outdoor';
        }
        
        return 'venue';
    }
    
    /**
     * Categorize cultural events specific to Slovakia
     */
    protected function categorizeCulturalEvent(array $eventData): string
    {
        $name = strtolower($eventData['name'] ?? '');
        $category = strtolower($eventData['category'] ?? '');
        $description = strtolower($eventData['description'] ?? '');
        
        $text = $name . ' ' . $category . ' ' . $description;
        
        if (strpos($text, 'folklore') !== false || strpos($text, 'ľudová') !== false) {
            return 'folklore';
        }
        if (strpos($text, 'klasická hudba') !== false) {
            return 'classical';
        }
        if (strpos($text, 'moderný tanec') !== false) {
            return 'contemporary_dance';
        }
        if (strpos($text, 'tradičný') !== false) {
            return 'traditional';
        }
        if (strpos($text, 'experimentálny') !== false) {
            return 'experimental';
        }
        
        return 'general';
    }
    
    /**
     * Determine currency from event data
     */
    protected function determineCurrency(array $eventData): string
    {
        // Check prices for currency indicators
        if (isset($eventData['prices']) && !empty($eventData['prices'])) {
            foreach ($eventData['prices'] as $price) {
                if (strpos($price, '€') !== false) {
                    return 'EUR';
                }
                if (strpos($price, 'Kč') !== false) {
                    return 'CZK';
                }
            }
        }
        
        return 'EUR'; // Slovakia uses Euro
    }
    
    /**
     * Map internal status to standardized availability status
     */
    protected function mapAvailabilityStatus(string $internalStatus): string
    {
        $statusMap = [
            'onsale' => 'available',
            'dostupné' => 'available',
            'soldout' => 'sold_out',
            'vypredané' => 'sold_out',
            'presale' => 'presale',
            'predpredaj' => 'presale',
            'cancelled' => 'cancelled',
            'zrušené' => 'cancelled',
            'postponed' => 'postponed',
            'odložené' => 'postponed',
            'unknown' => 'unknown'
        ];
        
        return $statusMap[strtolower($internalStatus)] ?? 'unknown';
    }
}
