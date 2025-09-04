<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;

class RealMadridPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Real Madrid
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Competiciones' => [
                'La Liga',
                'Champions League',
                'Copa del Rey',
                'Supercopa de España',
                'El Clásico',
            ],
            'Rivales Principales' => [
                'FC Barcelona',
                'Atlético Madrid',
                'Athletic Bilbao',
                'Sevilla FC',
                'Valencia CF',
            ],
            'Tipos de Entrada' => [
                'Entradas Generales',
                'Palcos VIP',
                'Paquetes Hospitalidad',
                'Abonos de Temporada',
            ],
        ];
    }

    /**
     * Check if plugin supports a specific competition
     */
    public function supportsCompetition(string $competition): bool
    {
        $supportedCompetitions = [
            'la_liga', 'laliga', 'liga', 'primera division',
            'champions league', 'champions', 'ucl',
            'copa del rey', 'copa', 'cdr',
            'supercopa', 'supercopa de españa',
            'clasico', 'el clasico',
        ];

        return in_array(strtolower($competition), $supportedCompetitions, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Real Madrid CF';
        $this->platform = 'real_madrid';
        $this->description = 'Official Real Madrid CF tickets - La Liga, Champions League, Copa del Rey';
        $this->baseUrl = 'https://www.realmadrid.com';
        $this->venue = 'Santiago Bernabéu Stadium';
        $this->currency = 'EUR';
        $this->language = 'es-ES';
        $this->rateLimitSeconds = 3;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'la_liga',
            'champions_league',
            'copa_del_rey',
            'supercopa_espana',
            'hospitality_packages',
            'season_tickets',
            'bernabeu_tours',
            'clasico_tickets',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'date_range',
            'competition',
            'match_type',
            'opponent',
            'section',
            'price_range',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/entradas';
    }

    /**
     * Build search URL from criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/entradas';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['buscar'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competicion'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['date_from'])) {
            $params['fecha_desde'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['fecha_hasta'] = $criteria['date_to'];
        }

        if (! empty($criteria['opponent'])) {
            $params['rival'] = $criteria['opponent'];
        }

        // Remove empty parameters
        $params = array_filter($params, function ($value) {
            return ! empty($value);
        });

        if (! empty($params)) {
            return $baseSearchUrl . '?' . http_build_query($params);
        }

        return $baseSearchUrl;
    }

    /**
     * Scrape tickets from Real Madrid
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("Real Madrid Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Real Madrid ticket selectors
            $crawler->filter('.match-card, .ticket-item, .partido, .entrada')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Real Madrid Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Real Madrid Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Real Madrid Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.match-card, .ticket-item, .partido, .entrada')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Real Madrid Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.titulo, .title, .partido, .match-title, h2, h3';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.fecha, .date, .match-date, .dia';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.estadio, .venue, .lugar';
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
        return '.disponibilidad, .availability, .agotado, .sold-out';
    }

    /**
     * Map competition names to Spanish terms
     */
    private function mapCompetition(string $competition): string
    {
        $competitions = [
            'la_liga'          => 'LaLiga',
            'champions_league' => 'Champions League',
            'copa_del_rey'     => 'Copa del Rey',
            'supercopa'        => 'Supercopa de España',
            'clasico'          => 'El Clásico',
        ];

        return $competitions[strtolower($competition)] ?? $competition;
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract match information
            $homeTeam = $this->extractText($node, '.equipo-local, .home-team, .local');
            $awayTeam = $this->extractText($node, '.equipo-visitante, .away-team, .visitante');
            $date = $this->extractText($node, '.fecha, .date, .match-date');
            $competition = $this->extractText($node, '.competicion, .competition, .torneo');
            $priceText = $this->extractText($node, '.precio, .price, .desde');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Create match title
            $title = trim($homeTeam . ' vs ' . $awayTeam);
            if (empty($title) || $title === 'vs') {
                $title = $this->extractText($node, '.titulo, .title, h3');
            }

            if (empty($title)) {
                return NULL;
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date
            $eventDate = $this->parseDate($date);

            // Build full URL if relative
            if ($link && ! filter_var($link, FILTER_VALIDATE_URL)) {
                $link = rtrim($this->baseUrl, '/') . '/' . ltrim($link, '/');
            }

            return [
                'title'        => $title,
                'price'        => $price['min'],
                'price_range'  => $price,
                'currency'     => $this->currency,
                'venue'        => $this->venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => 'football',
                'competition'  => $competition ?: 'La Liga',
                'home_team'    => $homeTeam,
                'away_team'    => $awayTeam,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('Real Madrid Plugin: Error extracting ticket data: ' . $e->getMessage());

            return NULL;
        }
    }

    /**
     * Parse price from Spanish text
     */
    private function parsePrice(string $priceText): array
    {
        if (empty($priceText)) {
            return ['min' => NULL, 'max' => NULL];
        }

        // Handle Spanish price formats
        $priceText = str_replace(['desde ', 'a partir de ', '€'], '', strtolower($priceText));

        // Extract numeric values
        preg_match_all('/[\d,]+\.?\d*/', $priceText, $matches);
        $prices = array_map(function ($price) {
            return (float) str_replace(',', '.', $price);
        }, $matches[0]);

        if (empty($prices)) {
            return ['min' => NULL, 'max' => NULL];
        }

        return [
            'min' => min($prices),
            'max' => count($prices) > 1 ? max($prices) : min($prices),
        ];
    }

    /**
     * Determine ticket availability
     */
    private function determineAvailability(Crawler $node): string
    {
        $availabilityText = $this->extractText($node, '.disponibilidad, .availability, .estado');

        if (preg_match('/agotad|sold.?out|no disponible/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/pocas entradas|few tickets|limitad/i', $availabilityText)) {
            return 'limited';
        }

        return 'available';
    }
}
