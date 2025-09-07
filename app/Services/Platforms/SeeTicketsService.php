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
use function in_array;

class SeeTicketsService extends BasePlatformService
{
    protected string $platformName = 'seetickets';

    protected string $baseUrl = 'https://www.seetickets.com';

    protected array $sportEventTypes = [
        'football', 'rugby', 'cricket', 'tennis', 'motorsport',
        'athletics', 'boxing', 'horse-racing', 'golf',
    ];

    /**
     * Search for events on SeeTickets
     */
    /**
     * SearchEvents
     */
    public function searchEvents(string $query, array $filters = []): array
    {
        try {
            $searchUrl = $this->buildSearchUrl($query, $filters);

            $response = Http::withHeaders([
                'User-Agent'      => $this->getRandomUserAgent(),
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-GB,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate',
                'Connection'      => 'keep-alive',
            ])->timeout(30)->get($searchUrl);

            if (!$response->successful()) {
                throw new Exception('SeeTickets search failed: ' . $response->status());
            }

            return $this->parseSearchResults($response->body(), $query);
        } catch (Exception $e) {
            Log::error('SeeTickets search error', [
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
    public function getEventDetails(string $eventUrl): array
    {
        $cacheKey = 'seetickets_event_' . md5($eventUrl);

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($eventUrl) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => $this->getRandomUserAgent(),
                    'Referer'    => $this->baseUrl,
                ])->timeout(30)->get($eventUrl);

                if (!$response->successful()) {
                    throw new Exception('Failed to fetch event details');
                }

                return $this->parseEventDetails($response->body(), $eventUrl);
            } catch (Exception $e) {
                Log::error('SeeTickets event details error', [
                    'url'   => $eventUrl,
                    'error' => $e->getMessage(),
                ]);

                return [
                    'success' => FALSE,
                    'error'   => $e->getMessage(),
                ];
            }
        });
    }

    /**
     * Import tickets from SeeTickets
     */
    /**
     * ImportTickets
     */
    public function importTickets(array $eventUrls): array
    {
        $imported = [];
        $errors = [];

        foreach ($eventUrls as $url) {
            try {
                $eventDetails = $this->getEventDetails($url);

                if (!$eventDetails['success']) {
                    $errors[] = "Failed to get details for: {$url}";

                    continue;
                }

                foreach ($eventDetails['tickets'] as $ticketData) {
                    $ticket = $this->createTicketRecord($ticketData, $eventDetails);
                    if ($ticket) {
                        $imported[] = $ticket;
                    }
                }
            } catch (Exception $e) {
                $errors[] = "Error importing {$url}: " . $e->getMessage();
                Log::error('SeeTickets import error', [
                    'url'   => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'          => count($imported) > 0,
            'imported_count'   => count($imported),
            'imported_tickets' => $imported,
            'errors'           => $errors,
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

        $categories = ScrapedTicket::where('platform', $this->platformName)
            ->selectRaw('JSON_EXTRACT(metadata, "$.category") as category, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        return [
            'platform'          => $this->platformName,
            'total_tickets'     => $totalTickets,
            'available_tickets' => $availableTickets,
            'availability_rate' => $totalTickets > 0 ? round(($availableTickets / $totalTickets) * 100, 2) : 0,
            'categories'        => $categories->pluck('count', 'category')->toArray(),
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
    private function buildSearchUrl(string $query, array $filters): string
    {
        $params = [
            'q'    => $query,
            'sort' => 'date',
            'view' => 'list',
        ];

        // Add category filter for sports
        if (isset($filters['category']) && in_array($filters['category'], $this->sportEventTypes, TRUE)) {
            $params['category'] = $filters['category'];
        }

        // Add date filters
        if (isset($filters['date_from'])) {
            $params['date_from'] = Carbon::parse($filters['date_from'])->format('Y-m-d');
        }

        if (isset($filters['date_to'])) {
            $params['date_to'] = Carbon::parse($filters['date_to'])->format('Y-m-d');
        }

        // Add location filter
        if (isset($filters['location'])) {
            $params['location'] = $filters['location'];
        }

        return $this->baseUrl . '/search?' . http_build_query($params);
    }

    /**
     * Parse search results from HTML
     */
    /**
     * ParseSearchResults
     */
    private function parseSearchResults(string $html, string $query): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        $events = [];

        // Find event listings (adjust selectors based on actual SeeTickets HTML structure)
        $eventNodes = $xpath->query('//div[contains(@class, "event-item") or contains(@class, "search-result")]');

        foreach ($eventNodes as $node) {
            try {
                $event = $this->extractEventData($node, $xpath);
                if ($event) {
                    $events[] = $event;
                }
            } catch (Exception $e) {
                Log::warning('Failed to parse SeeTickets event', ['error' => $e->getMessage()]);
            }
        }

        return [
            'success'     => TRUE,
            'query'       => $query,
            'total_found' => count($events),
            'events'      => $events,
        ];
    }

    /**
     * Extract event data from DOM node
     */
    /**
     * ExtractEventData
     */
    private function extractEventData(DOMNode $node, DOMXPath $xpath): ?array
    {
        // Extract event title
        $titleNode = $xpath->query('.//h3[@class="event-title"] | .//h2[@class="event-name"]', $node)->item(0);
        if (!$titleNode) {
            return NULL;
        }

        $title = trim($titleNode->textContent);

        // Extract event URL
        $linkNode = $xpath->query('.//a[contains(@href, "/event/")]', $node)->item(0);
        $eventUrl = $linkNode ? $this->baseUrl . $linkNode->getAttribute('href') : NULL;

        // Extract venue
        $venueNode = $xpath->query('.//span[@class="venue"] | .//div[@class="venue-name"]', $node)->item(0);
        $venue = $venueNode ? trim($venueNode->textContent) : NULL;

        // Extract date
        $dateNode = $xpath->query('.//time | .//span[@class="date"]', $node)->item(0);
        $eventDate = NULL;
        if ($dateNode) {
            $dateStr = $dateNode->getAttribute('datetime') ?: $dateNode->textContent;
            $eventDate = $this->parseEventDate($dateStr);
        }

        // Extract price range
        $priceNode = $xpath->query('.//span[@class="price"] | .//div[@class="price-range"]', $node)->item(0);
        $priceRange = $priceNode ? $this->parsePriceRange(trim($priceNode->textContent)) : NULL;

        // Extract image
        $imageNode = $xpath->query('.//img', $node)->item(0);
        $imageUrl = $imageNode ? $imageNode->getAttribute('src') : NULL;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->baseUrl . $imageUrl;
        }

        return [
            'title'       => $title,
            'url'         => $eventUrl,
            'venue'       => $venue,
            'date'        => $eventDate,
            'price_range' => $priceRange,
            'image_url'   => $imageUrl,
            'platform'    => $this->platformName,
            'category'    => $this->categorizeEvent($title),
            'source_page' => 'search',
        ];
    }

    /**
     * Parse detailed event information
     */
    /**
     * ParseEventDetails
     */
    private function parseEventDetails(string $html, string $eventUrl): array
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        // Extract basic event information
        $titleNode = $xpath->query('//h1[@class="event-title"] | //h1[contains(@class, "event-name")]')->item(0);
        $title = $titleNode ? trim($titleNode->textContent) : 'Unknown Event';

        $venueNode = $xpath->query('//span[@class="venue-name"] | //div[@class="venue-details"]')->item(0);
        $venue = $venueNode ? trim($venueNode->textContent) : NULL;

        $dateNode = $xpath->query('//time[@class="event-date"] | //span[@class="date"]')->item(0);
        $eventDate = NULL;
        if ($dateNode) {
            $dateStr = $dateNode->getAttribute('datetime') ?: $dateNode->textContent;
            $eventDate = $this->parseEventDate($dateStr);
        }

        // Extract ticket options
        $tickets = [];
        $ticketNodes = $xpath->query('//div[@class="ticket-option"] | //tr[@class="ticket-row"]');

        foreach ($ticketNodes as $ticketNode) {
            $ticket = $this->extractTicketData($ticketNode, $xpath);
            if ($ticket) {
                $tickets[] = $ticket;
            }
        }

        // Extract event description
        $descNode = $xpath->query('//div[@class="event-description"] | //div[@class="event-info"]')->item(0);
        $description = $descNode ? trim($descNode->textContent) : NULL;

        // Extract event image
        $imageNode = $xpath->query('//img[@class="event-image"] | //img[contains(@class, "hero-image")]')->item(0);
        $imageUrl = $imageNode ? $imageNode->getAttribute('src') : NULL;
        if ($imageUrl && !str_starts_with($imageUrl, 'http')) {
            $imageUrl = $this->baseUrl . $imageUrl;
        }

        return [
            'success'      => TRUE,
            'title'        => $title,
            'venue'        => $venue,
            'date'         => $eventDate,
            'description'  => $description,
            'image_url'    => $imageUrl,
            'url'          => $eventUrl,
            'platform'     => $this->platformName,
            'tickets'      => $tickets,
            'extracted_at' => now()->toISOString(),
        ];
    }

    /**
     * Extract individual ticket data
     */
    /**
     * ExtractTicketData
     */
    private function extractTicketData(DOMNode $ticketNode, DOMXPath $xpath): ?array
    {
        // Extract section/category
        $sectionNode = $xpath->query('.//span[@class="section"] | .//td[@class="section"]', $ticketNode)->item(0);
        $section = $sectionNode ? trim($sectionNode->textContent) : 'General Admission';

        // Extract price
        $priceNode = $xpath->query('.//span[@class="price"] | .//td[@class="price"]', $ticketNode)->item(0);
        if (!$priceNode) {
            return NULL;
        }

        $priceText = trim($priceNode->textContent);
        $price = $this->extractPrice($priceText);
        if (!$price) {
            return NULL;
        }

        // Extract availability
        $availNode = $xpath->query('.//span[@class="availability"] | .//td[@class="stock"]', $ticketNode)->item(0);
        $availability = $availNode ? trim($availNode->textContent) : 'Available';

        // Check if sold out
        $isSoldOut = stripos($availability, 'sold out') !== FALSE
                    || stripos($availability, 'unavailable') !== FALSE;

        return [
            'section'           => $section,
            'price'             => $price,
            'currency'          => 'GBP',
            'availability'      => $availability,
            'is_available'      => !$isSoldOut,
            'platform_specific' => [
                'ticket_type'  => $this->extractTicketType($section),
                'restrictions' => $this->extractRestrictions($ticketNode, $xpath),
            ],
        ];
    }

    /**
     * Create ticket record in database
     */
    /**
     * CreateTicketRecord
     */
    private function createTicketRecord(array $ticketData, array $eventData): ?ScrapedTicket
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
                    'extraction_method' => 'api_scraping',
                    'last_updated'      => now()->toISOString(),
                ]),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create SeeTickets ticket record', [
                'error'       => $e->getMessage(),
                'ticket_data' => $ticketData,
            ]);

            return NULL;
        }
    }

    /**
     * Parse event date from various formats
     */
    /**
     * ParseEventDate
     */
    private function parseEventDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);

        // Common UK date formats
        $formats = [
            'Y-m-d H:i:s',      // ISO format
            'd/m/Y H:i',        // UK format with time
            'd/m/Y',            // UK format
            'D d M Y H:i',      // "Sat 25 Dec 2024 19:30"
            'l d F Y H:i',      // "Saturday 25 December 2024 19:30"
            'M d, Y H:i',       // US format with time
            'M d, Y',           // US format
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateStr);

                return $date->toDateTimeString();
            } catch (Exception $e) {
                continue;
            }
        }

        // Try natural language parsing
        try {
            return Carbon::parse($dateStr)->toDateTimeString();
        } catch (Exception $e) {
            Log::warning('Could not parse SeeTickets date', ['date_string' => $dateStr]);

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
        $priceText = preg_replace('/[^\d.,£\-\s]/', '', $priceText);

        // Look for price ranges like "£25 - £50" or "From £25"
        if (preg_match('/£(\d+(?:\.\d{2})?)\s*-\s*£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return [
                'min'      => (float) $matches[1],
                'max'      => (float) $matches[2],
                'currency' => 'GBP',
            ];
        }

        // Look for "From £X" format
        if (preg_match('/from\s*£(\d+(?:\.\d{2})?)/i', $priceText, $matches)) {
            return [
                'min'      => (float) $matches[1],
                'max'      => NULL,
                'currency' => 'GBP',
            ];
        }

        // Single price
        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return [
                'min'      => (float) $matches[1],
                'max'      => (float) $matches[1],
                'currency' => 'GBP',
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
        $priceText = preg_replace('/[^\d.]/', '', $priceText);

        return is_numeric($priceText) ? (float) $priceText : NULL;
    }

    /**
     * Categorize event based on title and context
     */
    /**
     * CategorizeEvent
     */
    private function categorizeEvent(string $title): string
    {
        $title = strtolower($title);

        $categories = [
            'football'     => ['football', 'fc', 'united', 'city', 'arsenal', 'chelsea', 'liverpool', 'premier league'],
            'rugby'        => ['rugby', 'union', 'league', 'six nations'],
            'cricket'      => ['cricket', 'test', 'odi', 't20', 'county'],
            'tennis'       => ['tennis', 'wimbledon', 'atp', 'wta'],
            'motorsport'   => ['f1', 'formula', 'grand prix', 'motogp', 'btcc'],
            'athletics'    => ['athletics', 'marathon', 'diamond league'],
            'boxing'       => ['boxing', 'fight', 'championship'],
            'golf'         => ['golf', 'open', 'masters', 'pga'],
            'horse-racing' => ['racing', 'derby', 'ascot', 'cheltenham'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($title, $keyword)) {
                    return $category;
                }
            }
        }

        return 'sports';
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

        if (str_contains($section, 'vip') || str_contains($section, 'premium')) {
            return 'premium';
        }

        if (str_contains($section, 'hospitality') || str_contains($section, 'corporate')) {
            return 'hospitality';
        }

        if (str_contains($section, 'season') || str_contains($section, 'membership')) {
            return 'season_ticket';
        }

        return 'standard';
    }

    /**
     * Extract ticket restrictions
     */
    /**
     * ExtractRestrictions
     */
    private function extractRestrictions(DOMNode $ticketNode, DOMXPath $xpath): array
    {
        $restrictions = [];

        $restrictionNode = $xpath->query('.//span[@class="restrictions"] | .//div[@class="ticket-info"]', $ticketNode)->item(0);
        if ($restrictionNode) {
            $text = strtolower(trim($restrictionNode->textContent));

            if (str_contains($text, 'over 18') || str_contains($text, 'adult only')) {
                $restrictions[] = 'adult_only';
            }

            if (str_contains($text, 'id required') || str_contains($text, 'identification')) {
                $restrictions[] = 'id_required';
            }

            if (str_contains($text, 'no transfer') || str_contains($text, 'non-transferable')) {
                $restrictions[] = 'non_transferable';
            }
        }

        return $restrictions;
    }
}
