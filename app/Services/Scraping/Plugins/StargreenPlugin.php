<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class StargreenPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Stargreen';
        $this->platform = 'stargreen';
        $this->description = 'Stargreen - Major German ticket platform for sports, concerts, theater and events';
        $this->baseUrl = 'https://www.stargreen.com';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'de-DE';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'bundesliga',
            'champions_league',
            'dfb_pokal',
            'football_tickets',
            'concerts',
            'theater',
            'opera',
            'festivals',
            'sports_events',
            'handball',
            'basketball',
            'ice_hockey',
            'multi_venue',
            'presales',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'city',
            'date',
            'category',
            'genre',
            'venue',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/suche';
    }

    /**
     * Build search URL for Stargreen
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];
        
        $params = [
            'search' => $query,
            'city' => $filters['city'] ?? '',
            'date' => $filters['date'] ?? '',
            'category' => $filters['category'] ?? '',
            'genre' => $filters['genre'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, function($value) {
            return !empty($value);
        });

        return $this->baseUrl . '/suche?' . http_build_query($params);
    }

    /**
     * Scrape tickets from Stargreen search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("Stargreen Plugin: Scraping tickets from: $searchUrl");
            
            $response = $this->makeHttpRequest($searchUrl);
            if (!$response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Stargreen search results selectors
            $crawler->filter('.event-item, .ticket-item, .listing, .event, .veranstaltung')->each(function (Crawler $node) use (&$tickets) {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning("Stargreen Plugin: Error extracting ticket: " . $e->getMessage());
                }
            });

            Log::info("Stargreen Plugin: Found " . count($tickets) . " tickets");
            return $tickets;

        } catch (Exception $e) {
            Log::error("Stargreen Plugin: Scraping error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.title, .event-title, .name, h2 a, h3 a, .veranstaltung-title');
            if (empty($title)) {
                return null;
            }

            $venue = $this->extractText($node, '.venue, .location, .ort, .stadion, .halle');
            $date = $this->extractText($node, '.date, .datum, .when, time, .termin');
            $priceText = $this->extractText($node, '.price, .preis, .cost, .kosten');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title and venue
            $category = $this->determineCategory($title, $venue);

            return [
                'title' => $title,
                'price' => $price,
                'currency' => $this->currency,
                'venue' => $venue,
                'event_date' => $eventDate,
                'link' => $link,
                'platform' => $this->platform,
                'category' => $category,
                'availability' => 'available',
                'scraped_at' => now(),
            ];

        } catch (Exception $e) {
            Log::warning("Stargreen Plugin: Error extracting ticket data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $tickets = [];

        $crawler->filter('.event-item, .ticket-item, .listing, .event, .veranstaltung')->each(function (Crawler $node) use (&$tickets) {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning("Stargreen Plugin: Error extracting ticket: " . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.title, .event-title, .name, h2 a, h3 a, .veranstaltung-title';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.date, .datum, .when, time, .termin';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .location, .ort, .stadion, .halle';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .preis, .cost, .kosten';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .verfuegbar';
    }

    /**
     * Parse price from text
     */
    private function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return null;
        }

        // Handle German price formats
        $cleanPrice = preg_replace('/ab\s*€?|von\s*€?/i', '', $priceText);
        $cleanPrice = preg_replace('/[^0-9.,€]/', '', $cleanPrice);
        $cleanPrice = str_replace(',', '.', $cleanPrice); // German decimal format
        
        if (preg_match('/(\d+(?:\.\d{2})?)/', $cleanPrice, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $combined = $title . ' ' . $venue;

        // German football terms
        if (preg_match('/bayern|dortmund|schalke|hamburg|köln|frankfurt|stuttgart|werder|bundesliga|dfb.*pokal|champions.*league|fußball|fc\s+/', $combined)) {
            return 'football';
        }
        
        // Sports venues and terms
        if (preg_match('/allianz.*arena|signal.*iduna|mercedes.*benz.*arena|handball|basketball|eishockey|sport/', $combined)) {
            return 'sports';
        }
        
        // Music and concerts
        if (preg_match('/konzert|tour|live|musik|band|sänger|orchester|philharmonie/', $combined)) {
            return 'concert';
        }
        
        // Opera and theater
        if (preg_match('/oper|theater|musical|schauspiel|komödie|drama/', $combined)) {
            return 'theater';
        }
        
        // Classical venues
        if (preg_match('/staatsoper|semperoper|nationaltheater|philharmonie|gewandhaus/', $venue)) {
            return 'opera';
        }
        
        // Festivals
        if (preg_match('/festival|fest|festspiele/', $combined)) {
            return 'festival';
        }

        return 'other';
    }

    /**
     * Get search suggestions for Stargreen
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Bundesliga Teams' => [
                'Bayern München',
                'Borussia Dortmund',
                'RB Leipzig',
                'Bayer Leverkusen',
                'Eintracht Frankfurt',
                'FC Schalke 04',
                'Werder Bremen'
            ],
            'Berühmte Venues' => [
                'Allianz Arena München',
                'Signal Iduna Park Dortmund',
                'Mercedes-Benz Arena Stuttgart',
                'Olympiastadion Berlin',
                'Staatsoper Berlin'
            ],
            'Event Kategorien' => [
                'Fußball Tickets',
                'Konzert Tickets',
                'Theater Shows',
                'Klassische Musik',
                'Musik Festivals'
            ]
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $supportedVenues = [
            'allianz arena', 'signal iduna park', 'mercedes-benz arena',
            'olympiastadion berlin', 'staatsoper', 'semperoper',
            'nationaltheater', 'philharmonie', 'o2 world'
        ];

        return in_array(strtolower($venue), $supportedVenues);
    }

    /**
     * Get platform-specific filtering options
     */
    public function getFilterOptions(): array
    {
        return [
            'categories' => [
                'fussball' => 'Fußball',
                'konzerte' => 'Konzerte',
                'theater' => 'Theater',
                'oper' => 'Oper',
                'festival' => 'Festivals',
                'sport' => 'Sport'
            ],
            'cities' => [
                'berlin' => 'Berlin',
                'münchen' => 'München',
                'hamburg' => 'Hamburg',
                'köln' => 'Köln',
                'frankfurt' => 'Frankfurt',
                'stuttgart' => 'Stuttgart',
                'dortmund' => 'Dortmund',
                'leipzig' => 'Leipzig'
            ],
            'price_ranges' => [
                '0-25' => 'Bis €25',
                '25-50' => '€25 - €50',
                '50-100' => '€50 - €100',
                '100+' => 'Über €100'
            ]
        ];
    }
}
