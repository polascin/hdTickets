<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class TicketOnePlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for TicketOne
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Calcio Serie A' => [
                'Juventus',
                'AC Milan',
                'Inter Milan',
                'AS Roma',
                'Napoli',
                'Lazio',
                'Fiorentina',
                'Atalanta',
            ],
            'Teatri Famosi' => [
                'Teatro alla Scala Milano',
                'Teatro La Fenice Venezia',
                'Teatro Regio Torino',
                'Teatro San Carlo Napoli',
                'Teatro Massimo Palermo',
            ],
            'Stadi' => [
                'Juventus Stadium',
                'San Siro Milano',
                'Stadio Olimpico Roma',
                'Stadio Maradona Napoli',
            ],
            'Concerti' => [
                'Concerti Rock',
                'Concerti Pop',
                'Concerti Classici',
                'Festival Musicali',
            ],
        ];
    }

    /**
     * Check if platform supports a specific venue
     */
    public function supportsVenue(string $venue): bool
    {
        $supportedVenues = [
            'san siro', 'stadio olimpico', 'juventus stadium',
            'teatro alla scala', 'teatro la fenice', 'teatro regio',
            'forum assago', 'mediolanum forum', 'palalottomatica',
        ];

        return in_array(strtolower($venue), $supportedVenues, TRUE);
    }

    /**
     * Get platform-specific filtering options
     */
    public function getFilterOptions(): array
    {
        return [
            'categories' => [
                'calcio'   => 'Calcio',
                'concerti' => 'Concerti',
                'teatro'   => 'Teatro',
                'opera'    => 'Opera',
                'festival' => 'Festival',
                'sport'    => 'Sport',
            ],
            'cities' => [
                'milano'  => 'Milano',
                'roma'    => 'Roma',
                'napoli'  => 'Napoli',
                'torino'  => 'Torino',
                'firenze' => 'Firenze',
                'bologna' => 'Bologna',
                'venezia' => 'Venezia',
                'palermo' => 'Palermo',
            ],
            'price_ranges' => [
                '0-25'   => 'Fino a €25',
                '25-50'  => '€25 - €50',
                '50-100' => '€50 - €100',
                '100+'   => 'Oltre €100',
            ],
        ];
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'TicketOne';
        $this->platform = 'ticketone';
        $this->description = 'TicketOne - Major Italian ticket platform for concerts, sports, theater and events';
        $this->baseUrl = 'https://www.ticketone.it';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'it-IT';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'serie_a',
            'champions_league',
            'europa_league',
            'football_tickets',
            'concerts',
            'opera',
            'theater',
            'festivals',
            'sports_events',
            'multi_venue',
            'multi_city',
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
        return $this->baseUrl . '/ricerca';
    }

    /**
     * Build search URL for TicketOne
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'testo'     => $query,
            'citta'     => $filters['city'] ?? '',
            'data'      => $filters['date'] ?? '',
            'categoria' => $filters['category'] ?? '',
            'genere'    => $filters['genre'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, function ($value) {
            return ! empty($value);
        });

        return $this->baseUrl . '/ricerca?' . http_build_query($params);
    }

    /**
     * Scrape tickets from TicketOne search results
     */
    protected function scrapeTickets(string $searchUrl): array
    {
        try {
            Log::info("TicketOne Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // TicketOne search results selectors
            $crawler->filter('.event-item, .item, .listing, .evento, .spettacolo')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('TicketOne Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('TicketOne Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('TicketOne Plugin: Scraping error: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $crawler = new Crawler($html);
        $tickets = [];

        $crawler->filter('.event-item, .item, .listing, .evento, .spettacolo')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('TicketOne Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.titolo, .title, .nome, h2 a, h3 a, .event-name';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.data, .date, .quando, .when, time';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.luogo, .venue, .location, .teatro, .stadio';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.prezzo, .price, .costo, .cost';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .disponibilita';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.titolo, .title, .nome, h2 a, h3 a, .event-name');
            if (empty($title)) {
                return NULL;
            }

            $venue = $this->extractText($node, '.luogo, .venue, .location, .teatro, .stadio');
            $date = $this->extractText($node, '.data, .date, .quando, .when, time');
            $priceText = $this->extractText($node, '.prezzo, .price, .costo, .cost');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && ! filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title and venue
            $category = $this->determineCategory($title, $venue);

            return [
                'title'        => $title,
                'price'        => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $category,
                'availability' => 'available',
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('TicketOne Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse price from text
     */
    private function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return NULL;
        }

        // Handle Italian price formats
        $cleanPrice = preg_replace('/da\s*€?|a partire da\s*€?/i', '', $priceText);
        $cleanPrice = preg_replace('/[^0-9.,€]/', '', $cleanPrice);
        $cleanPrice = str_replace(',', '.', $cleanPrice); // Italian decimal format

        if (preg_match('/(\d+(?:\.\d{2})?)/', $cleanPrice, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $combined = $title . ' ' . $venue;

        // Italian football terms
        if (preg_match('/juventus|milan|inter|roma|napoli|lazio|fiorentina|atalanta|serie\s*a|champions\s*league|europa\s*league|calcio/', $combined)) {
            return 'football';
        }

        // Sports venues
        if (preg_match('/stadio|palasport|olimpico|san siro|juventus stadium|stadio/', $venue)) {
            return 'sports';
        }

        // Music and concerts
        if (preg_match('/concerto|tour|live|musica|band|cantante|orchestra|sinfonica/', $combined)) {
            return 'concert';
        }

        // Opera and theater
        if (preg_match('/opera|teatro|musical|spettacolo|commedia|dramma/', $combined)) {
            return 'theater';
        }

        // Classical venues
        if (preg_match('/scala|fenice|regio|massimo|san carlo/', $venue)) {
            return 'opera';
        }

        // Festivals
        if (preg_match('/festival|fest|rassegna/', $combined)) {
            return 'festival';
        }

        return 'other';
    }
}
