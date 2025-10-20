<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class InterMilanPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Inter Milan
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Competizioni' => [
                'Serie A TIM',
                'Champions League',
                'Europa League',
                'Coppa Italia',
                'Supercoppa Italiana',
                'Derby della Madonnina',
            ],
            'Rivali Principali' => [
                'AC Milan',
                'Juventus',
                'AS Roma',
                'Lazio',
                'Napoli',
                'Atalanta',
            ],
            'Tipi di Biglietto' => [
                'Biglietti Generali',
                'Hospitality',
                'Abbonamenti',
                'Prima Squadra',
                'Squadra Femminile',
                'Tour San Siro',
            ],
        ];
    }

    /**
     * Check if plugin supports a specific competition
     */
    public function supportsCompetition(string $competition): bool
    {
        $supportedCompetitions = [
            'serie_a', 'serie a', 'serie a tim',
            'champions league', 'champions', 'ucl',
            'europa league', 'europa', 'uel',
            'coppa italia', 'coppa', 'tim cup',
            'supercoppa', 'supercoppa italiana',
            'derby', 'derby della madonnina',
        ];

        return in_array(strtolower($competition), $supportedCompetitions, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'FC Internazionale Milano';
        $this->platform = 'inter_milan';
        $this->description = 'Official FC Internazionale Milano tickets - Serie A, Champions League, Coppa Italia';
        $this->baseUrl = 'https://www.inter.it';
        $this->venue = 'San Siro (Giuseppe Meazza)';
        $this->currency = 'EUR';
        $this->language = 'it-IT';
        $this->rateLimitSeconds = 3;
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
            'coppa_italia',
            'supercoppa_italiana',
            'hospitality_packages',
            'season_tickets',
            'san_siro_tours',
            'derby_della_madonnina', // vs AC Milan
            'womens_football',
            'youth_teams',
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
            'team', // prima squadra/femminile/primavera
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/it/biglietti';
    }

    /**
     * Build search URL for Inter Milan
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/it/biglietti';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['cerca'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competizione'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['team'])) {
            $params['squadra'] = $criteria['team'];
        }

        if (! empty($criteria['date_from'])) {
            $params['data_da'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['data_a'] = $criteria['date_to'];
        }

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => ! empty($value));

        if ($params !== []) {
            return $baseSearchUrl . '?' . http_build_query($params);
        }

        return $baseSearchUrl;
    }

    /**
     * Scrape tickets from Inter Milan
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("Inter Milan Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Inter Milan ticket selectors
            $crawler->filter('.partita-card, .ticket-item, .match-box, .biglietto')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Inter Milan Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Inter Milan Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Inter Milan Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.partita-card, .ticket-item, .match-box, .biglietto')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Inter Milan Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.titolo-partita, .match-title, .nome-evento, h2 a, h3';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.data-partita, .date, .match-date, .giorno';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.stadio, .venue, .san-siro, .giuseppe-meazza';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.prezzo, .price, .da, .costo';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.disponibilita, .availability, .esaurito, .sold-out';
    }

    /**
     * Map competition names to Italian terms
     */
    private function mapCompetition(string $competition): string
    {
        $competitions = [
            'serie_a'          => 'Serie A TIM',
            'champions_league' => 'Champions League',
            'europa_league'    => 'Europa League',
            'coppa_italia'     => 'Coppa Italia',
            'supercoppa'       => 'Supercoppa Italiana',
            'derby'            => 'Derby della Madonnina',
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
            $homeTeam = $this->extractText($node, '.squadra-casa, .home-team, .inter');
            $awayTeam = $this->extractText($node, '.squadra-ospite, .away-team, .avversario');
            $date = $this->extractText($node, '.data-partita, .date, .match-date');
            $time = $this->extractText($node, '.ora-partita, .time, .orario');
            $competition = $this->extractText($node, '.competizione, .competition, .campionato');
            $priceText = $this->extractText($node, '.prezzo, .price, .da, .costo');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Create match title
            $title = trim($homeTeam . ' vs ' . $awayTeam);
            if ($title === '' || $title === '0' || $title === 'vs') {
                $title = $this->extractText($node, '.titolo-partita, .match-title, h3');
            }

            if ($title === '' || $title === '0') {
                return NULL;
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDateTime($date, $time);

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
                'competition'  => $competition ?: 'Serie A',
                'home_team'    => $homeTeam,
                'away_team'    => $awayTeam,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('Inter Milan Plugin: Error extracting ticket data: ' . $e->getMessage());

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
     * Determine ticket availability
     */
    private function determineAvailability(Crawler $node): string
    {
        $availabilityText = $this->extractText($node, '.disponibilita, .availability, .stato');

        if (preg_match('/esaurit|sold.?out|non disponibili/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/pochi biglietti|few tickets|limitat|ultimi/i', $availabilityText)) {
            return 'limited';
        }

        return 'available';
    }
}
