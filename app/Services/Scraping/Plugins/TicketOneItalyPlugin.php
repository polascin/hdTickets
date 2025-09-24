<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class TicketOneItalyPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for TicketOne Italy
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Serie A Teams' => [
                'AC Milan',
                'Inter Milano',
                'Juventus',
                'AS Roma',
                'Lazio',
                'Napoli',
                'Atalanta',
                'Fiorentina',
            ],
            'Stadi Principali' => [
                'San Siro',
                'Allianz Stadium',
                'Stadio Olimpico',
                'Diego Armando Maradona',
                'Artemio Franchi',
            ],
            'Competizioni' => [
                'Serie A TIM',
                'Champions League',
                'Europa League',
                'Coppa Italia',
                'Derby della Madonnina',
                'Derby di Roma',
            ],
        ];
    }

    /**
     * Check if platform supports a specific team
     */
    public function supportsTeam(string $team): bool
    {
        $supportedTeams = [
            'milan', 'inter', 'juventus', 'roma', 'lazio', 'napoli',
            'atalanta', 'fiorentina', 'torino', 'genoa', 'sampdoria',
            'bologna', 'sassuolo', 'udinese', 'hellas verona',
        ];

        return in_array(strtolower($team), $supportedTeams, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'TicketOne Italy';
        $this->platform = 'ticketone_italy';
        $this->description = 'TicketOne - Major Italian ticket platform for Serie A, concerts, and events';
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
            'serie_b',
            'champions_league',
            'europa_league',
            'coppa_italia',
            'concerts',
            'theater',
            'sports_events',
            'festivals',
            'multi_venue',
            'multi_city',
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
            'date_range',
            'category',
            'venue',
            'team',
            'competition',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/search';
    }

    /**
     * Build search URL for TicketOne Italy
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'q'         => $query,
            'city'      => $filters['city'] ?? '',
            'categoria' => $filters['category'] ?? '',
            'data'      => $filters['date'] ?? '',
            'team'      => $filters['team'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => !empty($value));

        return $this->baseUrl . '/search?' . http_build_query($params);
    }

    /**
     * Scrape tickets from TicketOne Italy
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("TicketOne Italy Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (!$response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // TicketOne search results selector
            $crawler->filter('.event-item, .ticket-card, .evento, .biglietto')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('TicketOne Italy Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('TicketOne Italy Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('TicketOne Italy Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.event-item, .ticket-card, .evento, .biglietto')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('TicketOne Italy Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.titolo, .event-title, .nome-evento, h2 a, h3 a';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.data, .date, .evento-data, .giorno';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.stadio, .venue, .luogo, .location';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.prezzo, .price, .costo, .da';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.disponibilita, .availability, .esaurito, .sold-out';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.titolo, .event-title, .nome-evento, h3 a, .title');
            if ($title === '' || $title === '0') {
                return NULL;
            }

            $venue = $this->extractText($node, '.stadio, .venue, .luogo, .location');
            $date = $this->extractText($node, '.data, .date, .evento-data');
            $time = $this->extractText($node, '.ora, .time, .orario');
            $priceText = $this->extractText($node, '.prezzo, .price, .costo, .da');
            $link = $this->extractAttribute($node, 'a', 'href');
            $competition = $this->extractText($node, '.campionato, .competition, .serie');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDateTime($date, $time);

            // Build full URL if relative
            if ($link && !filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title and venue
            $category = $this->determineCategory($title, $venue, $competition);

            return [
                'title'        => $title,
                'price'        => $price['min'],
                'price_range'  => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $category,
                'competition'  => $competition,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('TicketOne Italy Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse date and time together
     */
    private function parseDateTime(string $date, string $time): ?string
    {
        $eventDate = $this->parseDate($date);

        if ($eventDate && ($time !== '' && $time !== '0')) {
            // Try to combine date and time
            $timeFormatted = $this->parseTime($time);
            if ($timeFormatted) {
                return date('Y-m-d H:i:s', strtotime($eventDate . ' ' . $timeFormatted));
            }
        }

        return $eventDate;
    }

    /**
     * Parse time from Italian text
     */
    private function parseTime(string $time): ?string
    {
        // Handle Italian time formats like "20:30", "20.30", etc.
        if (preg_match('/(\d{1,2})[:.h](\d{2})/', $time, $matches)) {
            return sprintf('%02d:%02d', $matches[1], $matches[2]);
        }

        return NULL;
    }

    /**
     * Parse price from Italian text
     */
    private function parsePrice(string $priceText): array
    {
        if ($priceText === '' || $priceText === '0') {
            return ['min' => NULL, 'max' => NULL];
        }

        // Handle Italian price formats
        $priceText = str_replace(['da ', 'a partire da ', '€'], '', strtolower($priceText));

        // Extract numeric values from price text
        preg_match_all('/[\d,]+\.?\d*/', $priceText, $matches);
        $prices = array_map(fn (string $price): float => (float) str_replace(',', '.', $price), $matches[0]);

        if ($prices === []) {
            return ['min' => NULL, 'max' => NULL];
        }

        return [
            'min' => min($prices),
            'max' => count($prices) > 1 ? max($prices) : min($prices),
        ];
    }

    /**
     * Determine event category
     */
    private function determineCategory(string $title, string $venue, string $competition): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $competition = strtolower($competition);
        $combined = $title . ' ' . $venue . ' ' . $competition;

        if (preg_match('/calcio|football|fc|inter|milan|juve|napoli|roma|lazio|serie/i', $combined)) {
            return 'football';
        }
        if (preg_match('/concerto|tour|musica|band|cantante/', $combined)) {
            return 'concert';
        }
        if (preg_match('/teatro|musical|spettacolo/', $combined)) {
            return 'theater';
        }
        if (preg_match('/festival|fest/', $combined)) {
            return 'festival';
        }

        return 'other';
    }

    /**
     * Determine ticket availability
     */
    private function determineAvailability(Crawler $node): string
    {
        $availabilityText = $this->extractText($node, '.disponibilita, .availability, .stato');

        if (preg_match('/esaurit|sold.?out|non disponibile/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/pochi biglietti|few tickets|limitat|ultimi/i', $availabilityText)) {
            return 'limited';
        }

        return 'available';
    }
}
