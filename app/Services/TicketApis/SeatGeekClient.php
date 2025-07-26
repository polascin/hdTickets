<?php

namespace App\Services\TicketApis;

use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;
use Exception;

class SeatGeekClient extends BaseWebScrapingClient
{
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->baseUrl = 'https://seatgeek.com';
        $this->respectRateLimit('seatgeek');
    }

    protected function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->config['client_id'] . ':' . $this->config['client_secret']),
        ];
    }

    public function searchEvents(array $criteria): array
    {
        // Try API first
        if (!empty($this->config['client_id']) && !empty($this->config['client_secret'])) {
            try {
                $params = $this->buildSearchParams($criteria);
                return $this->makeRequest('GET', 'events', $params);
            } catch (Exception $e) {
                Log::warning('SeatGeek API search failed, falling back to scraping', [
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fallback to web scraping
        return $this->scrapeSearchResults($criteria['q'] ?? '', $criteria['city'] ?? '', $criteria['per_page'] ?? 50);
    }

    public function getEvent(string $eventId): array
    {
        return $this->makeRequest('GET', "events/{$eventId}");
    }

    public function getVenue(string $venueId): array
    {
        return $this->makeRequest('GET', "venues/{$venueId}");
    }

    protected function buildSearchParams(array $criteria): array
    {
        $params = [];

        if (isset($criteria['q'])) {
            $params['q'] = $criteria['q'];
        }

        if (isset($criteria['datetime_utc.gte'])) {
            $params['datetime_utc.gte'] = $criteria['datetime_utc.gte'];
        }

        if (isset($criteria['datetime_utc.lte'])) {
            $params['datetime_utc.lte'] = $criteria['datetime_utc.lte'];
        }

        if (isset($criteria['venue.city'])) {
            $params['venue.city'] = $criteria['venue.city'];
        }

        if (isset($criteria['venue.state'])) {
            $params['venue.state'] = $criteria['venue.state'];
        }

        if (isset($criteria['taxonomies.name'])) {
            $params['taxonomies.name'] = $criteria['taxonomies.name'];
        }

        if (isset($criteria['per_page'])) {
            $params['per_page'] = min(100, $criteria['per_page']);
        }

        return $params;
    }

    protected function transformEventData(array $eventData): array
    {
        $lowestPrice = null;
        $highestPrice = null;

        if (isset($eventData['stats']['lowest_price'])) {
            $lowestPrice = $eventData['stats']['lowest_price'];
        }

        if (isset($eventData['stats']['highest_price'])) {
            $highestPrice = $eventData['stats']['highest_price'];
        }

        return [
            'id' => $eventData['id'] ?? null,
            'name' => $eventData['title'] ?? 'Unnamed Event',
            'date' => isset($eventData['datetime_local']) ? date('Y-m-d', strtotime($eventData['datetime_local'])) : null,
            'time' => isset($eventData['datetime_local']) ? date('H:i:s', strtotime($eventData['datetime_local'])) : null,
            'status' => $eventData['announce_date'] ? 'onsale' : 'unknown',
            'venue' => $eventData['venue']['name'] ?? 'Unknown Venue',
            'city' => $eventData['venue']['city'] ?? 'Unknown City',
            'country' => $eventData['venue']['country'] ?? 'Unknown Country',
            'url' => $eventData['url'] ?? '',
            'price_min' => $lowestPrice,
            'price_max' => $highestPrice,
            'ticket_count' => $eventData['stats']['listing_count'] ?? null,
        ];
    }

    /**
     * Get available tickets for an event
     */
    public function getEventTickets(string $eventId, array $filters = []): array
    {
        // Try API first
        if (!empty($this->config['client_id']) && !empty($this->config['client_secret'])) {
            try {
                $params = array_merge(['event_id' => $eventId], $filters);
                return $this->makeRequest('GET', 'listings', $params);
            } catch (Exception $e) {
                Log::warning('SeatGeek API tickets fetch failed, falling back to scraping', [
                    'event_id' => $eventId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fallback to scraping event details for ticket information
        return $this->getEventTicketsViaScraping($eventId, $filters);
    }

    /**
     * Scrape SeatGeek search results
     */
    public function scrapeSearchResults(string $keyword, string $location = '', int $maxResults = 50): array
    {
        try {
            $criteria = ['q' => $keyword, 'city' => $location, 'per_page' => $maxResults];
            $searchUrl = $this->buildScrapingSearchUrl($criteria);
            
            $html = $this->makeScrapingRequest($searchUrl);
            return $this->parseSearchResultsHtml($html);
        } catch (Exception $e) {
            Log::error('SeatGeek scraping search failed', [
                'keyword' => $keyword,
                'location' => $location,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function buildScrapingSearchUrl(array $criteria): string
    {
        $baseUrl = 'https://seatgeek.com/search';
        $params = [];

        if (isset($criteria['q'])) {
            $params['q'] = urlencode($criteria['q']);
        }

        if (isset($criteria['venue.city']) || isset($criteria['city'])) {
            $params['city'] = urlencode($criteria['venue.city'] ?? $criteria['city']);
        }

        if (isset($criteria['venue.state']) || isset($criteria['state'])) {
            $params['state'] = $criteria['venue.state'] ?? $criteria['state'];
        }

        if (isset($criteria['date_start'])) {
            $params['date'] = $criteria['date_start'];
        }

        return $baseUrl . (!empty($params) ? '?' . http_build_query($params) : '');
    }

    /**
     * Extract search results from HTML using Crawler
     */
    protected function extractSearchResults(Crawler $crawler, int $maxResults): array
    {
        $events = [];
        $count = 0;
        
        try {
            // SeatGeek event selectors
            $eventSelectors = [
                '.event-card',
                '.event-tile', 
                '.event-link',
                '.search-result'
            ];
            
            foreach ($eventSelectors as $selector) {
                if ($crawler->filter($selector)->count() > 0) {
                    $crawler->filter($selector)->each(function (Crawler $node) use (&$events, &$count, $maxResults) {
                        if ($count >= $maxResults) {
                            return false;
                        }
                        
                        $event = $this->extractEventFromNode($node);
                        if (!empty($event['name'])) {
                            $events[] = $event;
                            $count++;
                        }
                    });
                    break;
                }
            }
        } catch (Exception $e) {
            Log::error('Failed to extract SeatGeek search results', [
                'error' => $e->getMessage()
            ]);
        }

        return $events;
    }
    
    protected function parseSearchResultsHtml(string $html): array
    {
        $crawler = new Crawler($html);
        return $this->extractSearchResults($crawler, 50);
    }

    /**
     * Extract event data from node using Crawler
     */
    protected function extractEventFromNode(Crawler $node): array
    {
        $event = [
            'platform' => 'seatgeek',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name
            $name = $this->trySelectors($node, [
                'h3',
                'h4', 
                '.title',
                '.event-title',
                'a'
            ]);
            $event['name'] = $name;

            // Event URL
            $url = $this->trySelectors($node, [
                'a'
            ], 'href');
            if ($url) {
                $event['url'] = $this->normalizeUrl($url);
                $event['id'] = $this->extractEventIdFromUrl($event['url']);
            }

            // Date and time
            $date = $this->trySelectors($node, [
                '.date',
                'time',
                '.event-date'
            ]);
            if ($date) {
                $event['date'] = $date;
                $event['parsed_date'] = $this->parseEventDate($date);
            }

            // Venue
            $venue = $this->trySelectors($node, [
                '.venue',
                '.venue-name',
                '.location'
            ]);
            $event['venue'] = $venue;

            // Price information using enhanced extraction
            $prices = $this->extractPriceWithFallbacks($node);
            $event['prices'] = $prices;
            
            if (!empty($prices)) {
                $numericPrices = array_column($prices, 'price');
                $event['price_min'] = min($numericPrices);
                $event['price_max'] = max($numericPrices);
            }

        } catch (Exception $e) {
            Log::warning('Failed to extract SeatGeek event from node', [
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }
    
    protected function parseEventCard(DOMXPath $xpath, $eventNode): array
    {
        $event = [
            'platform' => 'seatgeek',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Event name
            $nameNode = $xpath->query('.//h3 | .//h4 | .//span[contains(@class, "title")] | .//a[contains(@class, "event-title")]', $eventNode)->item(0);
            if (!$nameNode) {
                // If this is a link node, get text content
                $nameNode = $eventNode->nodeName === 'a' ? $eventNode : null;
            }
            $event['name'] = $nameNode ? trim($nameNode->textContent) : '';

            // Event URL
            $linkNode = $xpath->query('.//a[contains(@href, "/")]', $eventNode)->item(0);
            if (!$linkNode && $eventNode->nodeName === 'a') {
                $linkNode = $eventNode;
            }
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
            $priceNodes = $xpath->query('.//span[contains(@class, "price")] | .//div[contains(@class, "price")] | .//*[contains(text(), "$")]', $eventNode);
            $prices = [];
            foreach ($priceNodes as $priceNode) {
                $priceText = trim($priceNode->textContent);
                if (preg_match('/\$[\d,]+/', $priceText)) {
                    $prices[] = $priceText;
                }
            }
            $event['prices'] = array_unique($prices);
            $this->extractPriceRange($event, $prices);

        } catch (Exception $e) {
            Log::warning('Failed to parse SeatGeek event card', [
                'error' => $e->getMessage()
            ]);
        }

        return $event;
    }

    protected function getEventTicketsViaScraping(string $eventId, array $filters = []): array
    {
        try {
            // Try to get event URL from the ID
            $eventUrl = "https://seatgeek.com/events/{$eventId}";
            
            $response = Http::withHeaders($this->scrapingHeaders)
                ->timeout($this->timeout)
                ->get($eventUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch event details from SeatGeek');
            }

            return $this->parseEventTicketsHtml($response->body(), $eventId);
        } catch (Exception $e) {
            Log::error('SeatGeek event tickets scraping failed', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function parseEventTicketsHtml(string $html, string $eventId): array
    {
        try {
            $doc = new DOMDocument();
            @$doc->loadHTML($html);
            $xpath = new DOMXPath($doc);

            // Look for ticket listings
            $listingNodes = $xpath->query('//div[contains(@class, "listing")] | //div[contains(@class, "ticket-row")] | //tr[contains(@class, "ticket")]');
            $prices = [];
            $sections = [];

            foreach ($listingNodes as $listingNode) {
                $priceNode = $xpath->query('.//*[contains(@class, "price")]', $listingNode)->item(0);
                $sectionNode = $xpath->query('.//*[contains(@class, "section")]', $listingNode)->item(0);

                if ($priceNode && preg_match('/\$[\d,]+/', $priceNode->textContent)) {
                    $price = trim($priceNode->textContent);
                    $prices[] = $price;
                    
                    if ($sectionNode) {
                        $sections[] = [
                            'section' => trim($sectionNode->textContent),
                            'price' => $price
                        ];
                    }
                }
            }

            $priceRange = [];
            if (!empty($prices)) {
                $numericPrices = [];
                foreach ($prices as $price) {
                    if (preg_match('/\$([,\d]+)/', $price, $matches)) {
                        $numericPrices[] = floatval(str_replace(',', '', $matches[1]));
                    }
                }
                if (!empty($numericPrices)) {
                    $priceRange = [
                        'min' => min($numericPrices),
                        'max' => max($numericPrices)
                    ];
                }
            }

            return [
                'event_id' => $eventId,
                'total_listings' => count($listingNodes),
                'price_range' => $priceRange,
                'prices' => $prices,
                'sections' => $sections,
            ];

        } catch (Exception $e) {
            Log::error('Failed to parse SeatGeek event tickets HTML', [
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    protected function normalizeUrl(string $url, ?string $baseUrl = null): string
    {
        if (strpos($url, 'http') !== 0) {
            return ($baseUrl ?: 'https://seatgeek.com') . $url;
        }
        return $url;
    }

    protected function extractEventIdFromUrl(string $url): ?string
    {
        if (preg_match('/\/events\/([^\/?]+)/', $url, $matches)) {
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
            if (preg_match('/\$([,\d]+)/', $price, $matches)) {
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

        $dateString = trim(preg_replace('/\s+/', ' ', $dateString));
        
        $formats = [
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
            return [];
        }
    }
    
    /**
     * Scrape event details from URL
     */
    public function scrapeEventDetails(string $url): array
    {
        try {
            $html = $this->makeScrapingRequest($url, ['referer' => $this->baseUrl]);
            $crawler = new Crawler($html);
            
            return $this->extractEventDetails($crawler, $url);
        } catch (Exception $e) {
            Log::error('Failed to scrape SeatGeek event details', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Extract detailed event information
     */
    protected function extractEventDetails(Crawler $crawler, string $url): array
    {
        $event = [
            'url' => $url,
            'platform' => 'seatgeek',
            'scraped_at' => now()->toISOString(),
        ];

        try {
            // Extract using JSON-LD first
            $jsonLdData = $this->extractJsonLdData($crawler, 'Event');
            if (!empty($jsonLdData)) {
                $eventData = $jsonLdData[0];
                $event['name'] = $eventData['name'] ?? '';
                $event['description'] = $eventData['description'] ?? '';
                if (isset($eventData['startDate'])) {
                    $event['parsed_date'] = new \DateTime($eventData['startDate']);
                }
                if (isset($eventData['location']['name'])) {
                    $event['venue'] = $eventData['location']['name'];
                }
            }
            
            // Fallback to selectors if JSON-LD not available
            if (empty($event['name'])) {
                $event['name'] = $this->trySelectors($crawler, [
                    'h1',
                    '.event-title',
                    '.event-name'
                ]);
            }
            
            if (empty($event['venue'])) {
                $event['venue'] = $this->trySelectors($crawler, [
                    '.venue-name',
                    '.venue',
                    '.location'
                ]);
            }
            
            // Extract prices
            $event['prices'] = $this->extractPrices($crawler);
            
            return $event;
            
        } catch (Exception $e) {
            Log::error('Failed to extract SeatGeek event details', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return $event;
        }
    }
    
    /**
     * Extract prices from page
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
                '[data-price]'
            ];
            
            foreach ($priceSelectors as $selector) {
                $crawler->filter($selector)->each(function (Crawler $node) use (&$prices) {
                    $text = $node->text();
                    if (preg_match('/\$([0-9,]+(?:\.[0-9]{2})?)/', $text, $matches)) {
                        $prices[] = [
                            'price' => floatval(str_replace(',', '', $matches[1])),
                            'currency' => 'USD',
                            'section' => 'General'
                        ];
                    }
                });
                
                if (!empty($prices)) {
                    break;
                }
            }
        } catch (Exception $e) {
            Log::debug('Failed to extract SeatGeek prices', ['error' => $e->getMessage()]);
        }
        
        return $prices;
    }
}
