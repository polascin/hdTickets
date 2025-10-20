<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class EntradiumSpainPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Entradium Spain
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Equipos La Liga' => [
                'Real Madrid',
                'FC Barcelona',
                'Atlético Madrid',
                'Sevilla FC',
                'Real Sociedad',
                'Athletic Bilbao',
                'Valencia CF',
                'Real Betis',
            ],
            'Estadios Principales' => [
                'Santiago Bernabéu',
                'Camp Nou',
                'Metropolitano',
                'Ramón Sánchez-Pizjuán',
                'San Mamés',
                'Mestalla',
            ],
            'Eventos Populares' => [
                'El Clásico',
                'Derby Madrileño',
                'Conciertos',
                'Teatro',
                'Flamenco',
                'Festivales',
            ],
        ];
    }

    /**
     * Check if platform supports a specific team
     */
    public function supportsTeam(string $team): bool
    {
        $supportedTeams = [
            'real madrid', 'barcelona', 'atletico madrid', 'sevilla',
            'real sociedad', 'athletic bilbao', 'valencia', 'real betis',
            'villarreal', 'celta vigo', 'espanyol', 'getafe',
        ];

        return in_array(strtolower($team), $supportedTeams, TRUE);
    }

    /**
     * Check if platform supports a specific city
     */
    public function supportsCity(string $city): bool
    {
        $supportedCities = [
            'madrid', 'barcelona', 'sevilla', 'valencia', 'bilbao',
            'san sebastian', 'villarreal', 'vigo', 'getafe', 'cornella',
        ];

        return in_array(strtolower($city), $supportedCities, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Entradium Spain';
        $this->platform = 'entradium_spain';
        $this->description = 'Entradium - Major Spanish ticket platform for La Liga, concerts, and events';
        $this->baseUrl = 'https://www.entradium.com';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'es-ES';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'la_liga',
            'segunda_division',
            'champions_league',
            'europa_league',
            'copa_del_rey',
            'concerts',
            'theater',
            'sports_events',
            'festivals',
            'multi_venue',
            'multi_city',
            'bullfighting',
            'flamenco',
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
            'artist',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/buscar';
    }

    /**
     * Build search URL for Entradium Spain
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'q'         => $query,
            'ciudad'    => $filters['city'] ?? '',
            'categoria' => $filters['category'] ?? '',
            'fecha'     => $filters['date'] ?? '',
            'equipo'    => $filters['team'] ?? '',
            'artista'   => $filters['artist'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => ! empty($value));

        return $this->baseUrl . '/buscar?' . http_build_query($params);
    }

    /**
     * Scrape tickets from Entradium Spain
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("Entradium Spain Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Entradium search results selectors
            $crawler->filter('.evento-card, .ticket-item, .entrada-box, .event-listing')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Entradium Spain Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Entradium Spain Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Entradium Spain Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.evento-card, .ticket-item, .entrada-box, .event-listing')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Entradium Spain Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.titulo-evento, .event-title, .nombre, h2 a, h3 a';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.fecha, .date, .event-date, .dia';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.estadio, .venue, .lugar, .location, .recinto';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.precio, .price, .desde, .coste';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.disponibilidad, .availability, .agotado, .sold-out, .estado';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract basic information
            $title = $this->extractText($node, '.titulo-evento, .event-title, .nombre, h3 a, .title');
            if ($title === '' || $title === '0') {
                return NULL;
            }

            $venue = $this->extractText($node, '.estadio, .venue, .lugar, .location, .recinto');
            $date = $this->extractText($node, '.fecha, .date, .event-date, .dia');
            $time = $this->extractText($node, '.hora, .time, .horario');
            $priceText = $this->extractText($node, '.precio, .price, .desde, .coste');
            $link = $this->extractAttribute($node, 'a', 'href');
            $category = $this->extractText($node, '.categoria, .category, .tipo');

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDateTime($date, $time);

            // Build full URL if relative
            if ($link && ! filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            // Determine category from title, venue, and extracted category
            $eventCategory = $this->determineCategory($title, $venue, $category);

            return [
                'title'        => $title,
                'price'        => $price['min'],
                'price_range'  => $price,
                'currency'     => $this->currency,
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => $eventCategory,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('Entradium Spain Plugin: Error extracting ticket data: ' . $e->getMessage());

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
            $timeFormatted = $this->parseTime($time);
            if ($timeFormatted) {
                return date('Y-m-d H:i:s', strtotime($eventDate . ' ' . $timeFormatted));
            }
        }

        return $eventDate;
    }

    /**
     * Parse time from Spanish text
     */
    private function parseTime(string $time): ?string
    {
        // Handle Spanish time formats like "20:30h", "20.30", etc.
        if (preg_match('/(\d{1,2})[:.h](\d{2})/', $time, $matches)) {
            return sprintf('%02d:%02d', $matches[1], $matches[2]);
        }

        return NULL;
    }

    /**
     * Parse price from Spanish text
     */
    private function parsePrice(string $priceText): array
    {
        if ($priceText === '' || $priceText === '0') {
            return ['min' => NULL, 'max' => NULL];
        }

        // Handle Spanish price formats
        $priceText = str_replace(['desde ', 'a partir de ', 'desde', '€'], '', strtolower($priceText));

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
    private function determineCategory(string $title, string $venue, string $category): string
    {
        $title = strtolower($title);
        $venue = strtolower($venue);
        $category = strtolower($category);
        $combined = $title . ' ' . $venue . ' ' . $category;

        if (preg_match('/fútbol|football|fc|real|barça|atletico|sevilla|valencia|liga/i', $combined)) {
            return 'football';
        }
        if (preg_match('/concierto|tour|música|banda|cantante|festival/i', $combined)) {
            return 'concert';
        }
        if (preg_match('/teatro|musical|espectáculo/i', $combined)) {
            return 'theater';
        }
        if (preg_match('/flamenco|baile|danza/i', $combined)) {
            return 'dance';
        }
        if (preg_match('/toros|tauromaquia|corrida/i', $combined)) {
            return 'bullfighting';
        }
        if (preg_match('/baloncesto|basket|acb/i', $combined)) {
            return 'basketball';
        }

        return 'other';
    }

    /**
     * Determine ticket availability
     */
    private function determineAvailability(Crawler $node): string
    {
        $availabilityText = $this->extractText($node, '.disponibilidad, .availability, .estado');

        if (preg_match('/agotad|sold.?out|sin entradas|no disponible/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/pocas entradas|few tickets|limitad|últimas/i', $availabilityText)) {
            return 'limited';
        }

        return 'available';
    }
}
