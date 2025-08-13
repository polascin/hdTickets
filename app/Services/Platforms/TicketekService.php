<?php declare(strict_types=1);

namespace App\Services\Platforms;

use App\Models\ScrapedTicket;
use Carbon\Carbon;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use function count;
use function is_array;

class TicketekService extends BasePlatformService
{
    protected string $platformName = 'ticketek';

    protected string $baseUrl = 'https://www.ticketek.co.uk';

    protected array $regions = [
        'uk' => 'https://www.ticketek.co.uk',
        'au' => 'https://www.ticketek.com.au',
        'nz' => 'https://www.ticketek.co.nz',
    ];

    /**
     * Search for events on Ticketek
     */
    /**
     * SearchEvents
     */
    public function searchEvents(string $query, array $filters = []): array
    {
        try {
            $region = $filters['region'] ?? 'uk';
            $baseUrl = $this->regions[$region] ?? $this->baseUrl;

            $searchUrl = $this->buildSearchUrl($query, $filters, $baseUrl);

            $response = Http::withHeaders([
                'User-Agent'       => $this->getRandomUserAgent(),
                'Accept'           => 'application/json, text/plain, */*',
                'Accept-Language'  => 'en-GB,en;q=0.9',
                'Referer'          => $baseUrl,
                'X-Requested-With' => 'XMLHttpRequest',
            ])->timeout(30)->get($searchUrl);

            if (! $response->successful()) {
                throw new Exception('Ticketek search failed: ' . $response->status());
            }

            return $this->parseSearchResults($response->json(), $query, $region);
        } catch (Exception $e) {
            Log::error('Ticketek search error', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => FALSE,
                'error'   => $e->getMessage(),
                'events'  => [],
            ];
        }
    }

    /**
     * Get detailed event information
     */
    /**
     * Get  event details
     */
    public function getEventDetails(string $eventUrl, string $region = 'uk'): array
    {
        $cacheKey = 'ticketek_event_' . md5($eventUrl . $region);

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($eventUrl, $region) {
            try {
                $baseUrl = $this->regions[$region] ?? $this->baseUrl;

                $response = Http::withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Accept'     => 'application/json, text/plain, */*',
                    'Referer'    => $baseUrl,
                ])->timeout(30)->get($eventUrl);

                if (! $response->successful()) {
                    throw new Exception('Failed to fetch event details');
                }

                // Try JSON response first, fallback to HTML parsing
                if ($response->header('content-type') && str_contains($response->header('content-type'), 'json')) {
                    return $this->parseEventDetailsJson($response->json(), $eventUrl, $region);
                }

                return $this->parseEventDetailsHtml($response->body(), $eventUrl, $region);
            } catch (Exception $e) {
                Log::error('Ticketek event details error', [
                    'url'    => $eventUrl,
                    'region' => $region,
                    'error'  => $e->getMessage(),
                ]);

                return [
                    'success' => FALSE,
                    'error'   => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Import tickets from Ticketek
     */
    /**
     * ImportTickets
     */
    public function importTickets(array $eventUrls, string $region = 'uk'): array
    {
        $imported = [];
        $errors = [];

        foreach ($eventUrls as $url) {
            try {
                $eventDetails = $this->getEventDetails($url, $region);

                if (! $eventDetails['success']) {
                    $errors[] = "Failed to get details for: {$url}";

                    continue;
                }

                foreach ($eventDetails['tickets'] as $ticketData) {
                    $ticket = $this->createTicketRecord($ticketData, $eventDetails, $region);
                    if ($ticket) {
                        $imported[] = $ticket;
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error importing {$url}: " . $e->getMessage();
                Log::error('Ticketek import error', [
                    'url'    => $url,
                    'region' => $region,
                    'error'  => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'          => count($imported) > 0,
            'imported_count'   => count($imported),
            'imported_tickets' => $imported,
            'errors'           => $errors,
            'region'           => $region,
        ];
    }

    /**
     * Get platform statistics
     */
    /**
     * Get  statistics
     */
    public function getStatistics(): array
    {
        $totalTickets = ScrapedTicket::where('platform', $this->platformName)->count();
        $availableTickets = ScrapedTicket::where('platform', $this->platformName)
            ->where('availability_status', 'available')->count();

        // Get region breakdown
        $regionStats = ScrapedTicket::where('platform', $this->platformName)
            ->selectRaw('JSON_EXTRACT(metadata, "$.region") as region, COUNT(*) as count')
            ->groupBy('region')
            ->get();

        return [
            'platform'          => $this->platformName,
            'total_tickets'     => $totalTickets,
            'available_tickets' => $availableTickets,
            'availability_rate' => $totalTickets > 0 ? round(($availableTickets / $totalTickets) * 100, 2) : 0,
            'regions'           => $regionStats->pluck('count', 'region')->toArray(),
            'supported_regions' => array_keys($this->regions),
            'last_updated'      => ScrapedTicket::where('platform', $this->platformName)
                ->max('updated_at'),
        ];
    }

    /**
     * Build search URL with parameters
     */
    /**
     * BuildSearchUrl
     */
    private function buildSearchUrl(string $query, array $filters, string $baseUrl): string
    {
        $params = [
            'search' => $query,
            'format' => 'json',
            'limit'  => 50,
        ];

        // Add category filters
        if (isset($filters['category'])) {
            $params['category'] = $this->mapCategoryToTicketek($filters['category']);
        }

        // Add date filters
        if (isset($filters['date_from'])) {
            $params['dateFrom'] = Carbon::parse($filters['date_from'])->format('Y-m-d');
        }

        if (isset($filters['date_to'])) {
            $params['dateTo'] = Carbon::parse($filters['date_to'])->format('Y-m-d');
        }

        // Add venue/location filter
        if (isset($filters['venue'])) {
            $params['venue'] = $filters['venue'];
        }

        // Add state/region filter for AU/NZ
        if (isset($filters['state'])) {
            $params['state'] = $filters['state'];
        }

        return $baseUrl . '/api/search?' . http_build_query($params);
    }

    /**
     * Parse search results from JSON response
     */
    /**
     * ParseSearchResults
     */
    private function parseSearchResults(array $jsonData, string $query, string $region): array
    {
        $events = [];

        if (! isset($jsonData['events']) || ! is_array($jsonData['events'])) {
            return [
                'success'     => TRUE,
                'query'       => $query,
                'region'      => $region,
                'total_found' => 0,
                'events'      => [],
            ];
        }

        foreach ($jsonData['events'] as $eventData) {
            try {
                $event = $this->extractEventFromJson($eventData, $region);
                if ($event) {
                    $events[] = $event;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse Ticketek event', ['error' => $e->getMessage()]);
            }
        }

        return [
            'success'     => TRUE,
            'query'       => $query,
            'region'      => $region,
            'total_found' => $jsonData['totalResults'] ?? count($events),
            'events'      => $events,
        ];
    }

    /**
     * Extract event data from JSON
     */
    /**
     * ExtractEventFromJson
     */
    private function extractEventFromJson(array $eventData, string $region): ?array
    {
        if (! isset($eventData['name']) || ! isset($eventData['id'])) {
            return NULL;
        }

        $baseUrl = $this->regions[$region] ?? $this->baseUrl;
        $eventUrl = $baseUrl . '/event/' . $eventData['id'];

        // Parse event date
        $eventDate = NULL;
        if (isset($eventData['eventDate'])) {
            $eventDate = $this->parseEventDate($eventData['eventDate']);
        } elseif (isset($eventData['performances']) && is_array($eventData['performances']) && ! empty($eventData['performances'])) {
            $eventDate = $this->parseEventDate($eventData['performances'][0]['startDateTime'] ?? '');
        }

        // Extract price information
        $priceRange = NULL;
        if (isset($eventData['priceRange'])) {
            $priceRange = $this->parsePriceRange($eventData['priceRange']);
        } elseif (isset($eventData['minPrice'], $eventData['maxPrice'])) {
            $priceRange = [
                'min'      => (float) $eventData['minPrice'],
                'max'      => (float) $eventData['maxPrice'],
                'currency' => $this->getCurrencyForRegion($region),
            ];
        }

        return [
            'id'          => $eventData['id'],
            'title'       => $eventData['name'],
            'url'         => $eventUrl,
            'venue'       => $eventData['venue']['name'] ?? $eventData['venueName'] ?? NULL,
            'date'        => $eventDate,
            'price_range' => $priceRange,
            'image_url'   => $eventData['imageUrl'] ?? $eventData['heroImage'] ?? NULL,
            'platform'    => $this->platformName,
            'region'      => $region,
            'category'    => $this->categorizeEvent($eventData['name'], $eventData),
            'source_page' => 'search',
            'metadata'    => [
                'venue_address'  => $eventData['venue']['address'] ?? NULL,
                'venue_capacity' => $eventData['venue']['capacity'] ?? NULL,
                'event_type'     => $eventData['type'] ?? NULL,
                'genre'          => $eventData['genre'] ?? NULL,
            ],
        ];
    }

    /**
     * Parse event details from JSON response
     */
    /**
     * ParseEventDetailsJson
     */
    private function parseEventDetailsJson(array $jsonData, string $eventUrl, string $region): array
    {
        $title = $jsonData['name'] ?? 'Unknown Event';
        $venue = $jsonData['venue']['name'] ?? $jsonData['venueName'] ?? NULL;

        // Parse event date from performances
        $eventDate = NULL;
        $performances = [];
        if (isset($jsonData['performances']) && is_array($jsonData['performances'])) {
            foreach ($jsonData['performances'] as $performance) {
                $perfDate = $this->parseEventDate($performance['startDateTime'] ?? '');
                if ($perfDate && ! $eventDate) {
                    $eventDate = $perfDate; // Use first performance as main date
                }

                $performances[] = [
                    'date'           => $perfDate,
                    'time'           => $performance['startTime'] ?? NULL,
                    'performance_id' => $performance['id'] ?? NULL,
                    'availability'   => $performance['availability'] ?? 'unknown',
                ];
            }
        }

        // Extract ticket categories
        $tickets = [];
        if (isset($jsonData['ticketCategories']) && is_array($jsonData['ticketCategories'])) {
            foreach ($jsonData['ticketCategories'] as $category) {
                $ticket = $this->extractTicketFromCategory($category, $region);
                if ($ticket) {
                    $tickets[] = $ticket;
                }
            }
        }

        return [
            'success'      => TRUE,
            'title'        => $title,
            'venue'        => $venue,
            'date'         => $eventDate,
            'description'  => $jsonData['description'] ?? $jsonData['shortDescription'] ?? NULL,
            'image_url'    => $jsonData['imageUrl'] ?? $jsonData['heroImage'] ?? NULL,
            'url'          => $eventUrl,
            'platform'     => $this->platformName,
            'region'       => $region,
            'performances' => $performances,
            'tickets'      => $tickets,
            'metadata'     => [
                'event_id'        => $jsonData['id'] ?? NULL,
                'duration'        => $jsonData['duration'] ?? NULL,
                'age_restriction' => $jsonData['ageRestriction'] ?? NULL,
                'dress_code'      => $jsonData['dressCode'] ?? NULL,
                'genre'           => $jsonData['genre'] ?? NULL,
                'artist'          => $jsonData['artist'] ?? NULL,
            ],
            'extracted_at' => now()->toISOString(),
        ];
    }

    /**
     * Parse event details from HTML response (fallback)
     */
    /**
     * ParseEventDetailsHtml
     */
    private function parseEventDetailsHtml(string $html, string $eventUrl, string $region): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Extract basic event information
        $titleNode = $xpath->query('//h1[contains(@class, "event-title")] | //h1[@data-testid="event-title"]')->item(0);
        $title = $titleNode ? trim($titleNode->textContent) : 'Unknown Event';

        $venueNode = $xpath->query('//span[contains(@class, "venue")] | //*[@data-testid="venue-name"]')->item(0);
        $venue = $venueNode ? trim($venueNode->textContent) : NULL;

        // Extract performances/dates
        $dateNodes = $xpath->query('//*[contains(@class, "performance-date")] | //*[@data-testid="performance-date"]');
        $performances = [];
        $eventDate = NULL;

        foreach ($dateNodes as $node) {
            $dateStr = $node->getAttribute('datetime') ?: $node->textContent;
            $perfDate = $this->parseEventDate($dateStr);
            if ($perfDate) {
                if (! $eventDate) {
                    $eventDate = $perfDate;
                }
                $performances[] = ['date' => $perfDate];
            }
        }

        // Extract ticket categories
        $tickets = [];
        $ticketNodes = $xpath->query('//*[contains(@class, "ticket-category")] | //*[@data-testid="ticket-option"]');

        foreach ($ticketNodes as $ticketNode) {
            $ticket = $this->extractTicketFromHtml($ticketNode, $xpath, $region);
            if ($ticket) {
                $tickets[] = $ticket;
            }
        }

        // Extract description
        $descNode = $xpath->query('//*[contains(@class, "event-description")] | //*[@data-testid="event-description"]')->item(0);
        $description = $descNode ? trim($descNode->textContent) : NULL;

        // Extract image
        $imageNode = $xpath->query('//img[contains(@class, "event-image")] | //img[@data-testid="event-image"]')->item(0);
        $imageUrl = $imageNode ? $imageNode->getAttribute('src') : NULL;

        return [
            'success'      => TRUE,
            'title'        => $title,
            'venue'        => $venue,
            'date'         => $eventDate,
            'description'  => $description,
            'image_url'    => $imageUrl,
            'url'          => $eventUrl,
            'platform'     => $this->platformName,
            'region'       => $region,
            'performances' => $performances,
            'tickets'      => $tickets,
            'extracted_at' => now()->toISOString(),
        ];
    }

    /**
     * Extract ticket data from category JSON
     */
    /**
     * ExtractTicketFromCategory
     */
    private function extractTicketFromCategory(array $category, string $region): ?array
    {
        if (! isset($category['name']) || ! isset($category['price'])) {
            return NULL;
        }

        $price = is_numeric($category['price']) ? (float) $category['price'] : NULL;
        if (! $price) {
            return NULL;
        }

        $isAvailable = $category['available'] ?? TRUE;
        if (isset($category['availability'])) {
            $isAvailable = strtolower($category['availability']) !== 'sold out';
        }

        return [
            'section'           => $category['name'],
            'price'             => $price,
            'currency'          => $this->getCurrencyForRegion($region),
            'availability'      => $category['availability'] ?? ($isAvailable ? 'Available' : 'Sold Out'),
            'is_available'      => $isAvailable,
            'platform_specific' => [
                'category_id'        => $category['id'] ?? NULL,
                'ticket_type'        => $this->extractTicketType($category['name']),
                'seat_map_available' => $category['seatMapAvailable'] ?? FALSE,
                'restrictions'       => $category['restrictions'] ?? [],
                'fees_included'      => $category['feesIncluded'] ?? FALSE,
            ],
        ];
    }

    /**
     * Extract ticket data from HTML
     */
    /**
     * ExtractTicketFromHtml
     */
    private function extractTicketFromHtml(DOMNode $ticketNode, DOMXPath $xpath, string $region): ?array
    {
        // Extract section name
        $sectionNode = $xpath->query('.//*[contains(@class, "section-name")] | .//*[@data-testid="section-name"]', $ticketNode)->item(0);
        $section = $sectionNode ? trim($sectionNode->textContent) : 'General Admission';

        // Extract price
        $priceNode = $xpath->query('.//*[contains(@class, "price")] | .//*[@data-testid="price"]', $ticketNode)->item(0);
        if (! $priceNode) {
            return NULL;
        }

        $priceText = trim($priceNode->textContent);
        $price = $this->extractPrice($priceText);
        if (! $price) {
            return NULL;
        }

        // Extract availability
        $availNode = $xpath->query('.//*[contains(@class, "availability")] | .//*[@data-testid="availability"]', $ticketNode)->item(0);
        $availability = $availNode ? trim($availNode->textContent) : 'Available';
        $isAvailable = ! str_contains(strtolower($availability), 'sold out');

        return [
            'section'           => $section,
            'price'             => $price,
            'currency'          => $this->getCurrencyForRegion($region),
            'availability'      => $availability,
            'is_available'      => $isAvailable,
            'platform_specific' => [
                'ticket_type'  => $this->extractTicketType($section),
                'restrictions' => $this->extractRestrictionsFromHtml($ticketNode, $xpath),
            ],
        ];
    }

    /**
     * Create ticket record in database
     */
    /**
     * CreateTicketRecord
     */
    private function createTicketRecord(array $ticketData, array $eventData, string $region): ?ScrapedTicket
    {
        try {
            return ScrapedTicket::updateOrCreate([
                'platform'    => $this->platformName,
                'event_title' => $eventData['title'],
                'section'     => $ticketData['section'],
                'price'       => $ticketData['price'],
            ], [
                'venue'               => $eventData['venue'],
                'event_date'          => $eventData['date'],
                'total_price'         => $ticketData['price'],
                'currency'            => $ticketData['currency'],
                'availability_status' => $ticketData['is_available'] ? 'available' : 'sold_out',
                'last_seen'           => now(),
                'source_url'          => $eventData['url'],
                'image_url'           => $eventData['image_url'],
                'description'         => $eventData['description'],
                'metadata'            => json_encode([
                    'platform_specific' => $ticketData['platform_specific'],
                    'region'            => $region,
                    'performances'      => $eventData['performances'] ?? [],
                    'extraction_method' => 'api_scraping',
                    'last_updated'      => now()->toISOString(),
                ]),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create Ticketek ticket record', [
                'error'       => $e->getMessage(),
                'ticket_data' => $ticketData,
                'region'      => $region,
            ]);

            return NULL;
        }
    }

    /**
     * Map category to Ticketek format
     */
    /**
     * MapCategoryToTicketek
     */
    private function mapCategoryToTicketek(string $category): string
    {
        $categoryMap = [
            'football'   => 'sport',
            'rugby'      => 'sport',
            'cricket'    => 'sport',
            'tennis'     => 'sport',
            'motorsport' => 'sport',
            'athletics'  => 'sport',
            'boxing'     => 'sport',
            'golf'       => 'sport',
            'concert'    => 'music',
            'theatre'    => 'theatre',
            'comedy'     => 'comedy',
            'family'     => 'family',
        ];

        return $categoryMap[$category] ?? $category;
    }

    /**
     * Get currency for region
     */
    /**
     * Get  currency for region
     */
    private function getCurrencyForRegion(string $region): string
    {
        return match ($region) {
            'uk'    => 'GBP',
            'au'    => 'AUD',
            'nz'    => 'NZD',
            default => 'GBP',
        };
    }

    /**
     * Parse event date from various formats
     */
    /**
     * ParseEventDate
     */
    private function parseEventDate(string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return NULL;
        }

        $dateStr = trim($dateStr);

        // ISO 8601 format with timezone
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $dateStr)) {
            try {
                return Carbon::parse($dateStr)->toDateTimeString();
            } catch (Exception $e) {
                // Continue to other formats
            }
        }

        // Common formats
        $formats = [
            'Y-m-d H:i:s',
            'd/m/Y H:i',
            'd/m/Y',
            'D, d M Y H:i',
            'l, d F Y H:i',
            'M d, Y H:i',
            'M d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateStr);

                return $date->toDateTimeString();
            } catch (Exception $e) {
                continue;
            }
        }

        // Try natural parsing
        try {
            return Carbon::parse($dateStr)->toDateTimeString();
        } catch (Exception $e) {
            Log::warning('Could not parse Ticketek date', ['date_string' => $dateStr]);

            return NULL;
        }
    }

    /**
     * Parse price range text
     */
    /**
     * ParsePriceRange
     */
    private function parsePriceRange(string $priceText): ?array
    {
        $currency = 'GBP';

        // Detect currency symbol
        if (str_contains($priceText, '$')) {
            $currency = str_contains($priceText, 'AU') ? 'AUD' : 'USD';
        } elseif (str_contains($priceText, '£')) {
            $currency = 'GBP';
        } elseif (str_contains($priceText, 'NZ')) {
            $currency = 'NZD';
        }

        // Extract numeric values
        preg_match_all('/[\d,.]+/', $priceText, $matches);
        $prices = array_map(function ($price) {
            return (float) str_replace(',', '', $price);
        }, $matches[0]);

        if (count($prices) >= 2) {
            return [
                'min'      => min($prices),
                'max'      => max($prices),
                'currency' => $currency,
            ];
        }
        if (count($prices) === 1) {
            return [
                'min'      => $prices[0],
                'max'      => $prices[0],
                'currency' => $currency,
            ];
        }

        return NULL;
    }

    /**
     * Extract price from text
     */
    /**
     * ExtractPrice
     */
    private function extractPrice(string $priceText): ?float
    {
        $priceText = preg_replace('/[^\d.,]/', '', $priceText);
        $priceText = str_replace(',', '', $priceText);

        return is_numeric($priceText) ? (float) $priceText : NULL;
    }

    /**
     * Categorize event
     */
    /**
     * CategorizeEvent
     */
    private function categorizeEvent(string $title, array $eventData = []): string
    {
        $title = strtolower($title);

        // Check event data first
        if (isset($eventData['type'])) {
            $type = strtolower($eventData['type']);
            if (str_contains($type, 'sport')) {
                return 'sports';
            }
            if (str_contains($type, 'music') || str_contains($type, 'concert')) {
                return 'music';
            }
            if (str_contains($type, 'theatre') || str_contains($type, 'play')) {
                return 'theatre';
            }
        }

        // Check genre
        if (isset($eventData['genre'])) {
            $genre = strtolower($eventData['genre']);
            if (str_contains($genre, 'rock') || str_contains($genre, 'pop') || str_contains($genre, 'jazz')) {
                return 'music';
            }
            if (str_contains($genre, 'comedy')) {
                return 'comedy';
            }
        }

        // Fallback to title analysis
        $categories = [
            'sports'  => ['football', 'rugby', 'cricket', 'tennis', 'f1', 'formula', 'athletics', 'boxing', 'golf'],
            'music'   => ['concert', 'tour', 'live', 'band', 'singer', 'festival'],
            'theatre' => ['theatre', 'play', 'musical', 'opera', 'ballet'],
            'comedy'  => ['comedy', 'comedian', 'stand-up'],
            'family'  => ['family', 'kids', 'children'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($title, $keyword)) {
                    return $category;
                }
            }
        }

        return 'entertainment';
    }

    /**
     * Extract ticket type from section name
     */
    /**
     * ExtractTicketType
     */
    private function extractTicketType(string $section): string
    {
        $section = strtolower($section);

        if (str_contains($section, 'vip') || str_contains($section, 'premium') || str_contains($section, 'platinum')) {
            return 'premium';
        }

        if (str_contains($section, 'hospitality') || str_contains($section, 'corporate') || str_contains($section, 'club')) {
            return 'hospitality';
        }

        if (str_contains($section, 'season') || str_contains($section, 'membership') || str_contains($section, 'package')) {
            return 'package';
        }

        return 'standard';
    }

    /**
     * Extract restrictions from HTML
     */
    /**
     * ExtractRestrictionsFromHtml
     */
    private function extractRestrictionsFromHtml(DOMNode $ticketNode, DOMXPath $xpath): array
    {
        $restrictions = [];

        $restrictionNodes = $xpath->query('.//*[contains(@class, "restriction")] | .//*[@data-testid="restriction"]', $ticketNode);

        foreach ($restrictionNodes as $node) {
            $text = strtolower(trim($node->textContent));

            if (str_contains($text, 'age') && (str_contains($text, '18') || str_contains($text, 'adult'))) {
                $restrictions[] = 'age_restriction';
            }

            if (str_contains($text, 'id') || str_contains($text, 'identification')) {
                $restrictions[] = 'id_required';
            }

            if (str_contains($text, 'non-transferable') || str_contains($text, 'no transfer')) {
                $restrictions[] = 'non_transferable';
            }

            if (str_contains($text, 'print') || str_contains($text, 'mobile only')) {
                $restrictions[] = 'delivery_restriction';
            }
        }

        return array_unique($restrictions);
    }
}
