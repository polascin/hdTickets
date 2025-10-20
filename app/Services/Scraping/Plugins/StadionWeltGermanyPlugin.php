<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class StadionWeltGermanyPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for StadionWelt Germany
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Bundesliga Teams' => [
                'FC Bayern München',
                'Borussia Dortmund',
                'RB Leipzig',
                'Bayer Leverkusen',
                'Borussia Mönchengladbach',
                'FC Schalke 04',
                'Eintracht Frankfurt',
                'VfB Stuttgart',
            ],
            'Famous Stadiums' => [
                'Allianz Arena',
                'Signal Iduna Park',
                'Red Bull Arena',
                'BayArena',
                'Borussia-Park',
                'Deutsche Bank Park',
            ],
            'Classic Matches' => [
                'Der Klassiker',
                'Revierderby',
                'Rheinderby',
                'Nordderby',
                'DFB-Pokal Finale',
            ],
        ];
    }

    /**
     * Check if platform supports a specific team
     */
    public function supportsTeam(string $team): bool
    {
        $supportedTeams = [
            'bayern münchen', 'bayern munich', 'borussia dortmund', 'rb leipzig',
            'bayer leverkusen', 'borussia mönchengladbach', 'schalke 04',
            'eintracht frankfurt', 'vfb stuttgart', 'werder bremen',
            'hamburger sv', 'fc köln', 'hertha bsc',
        ];

        return in_array(strtolower($team), $supportedTeams, TRUE);
    }

    /**
     * Check if platform supports a specific competition
     */
    public function supportsCompetition(string $competition): bool
    {
        $supportedCompetitions = [
            'bundesliga', '2. bundesliga', '3. liga', 'dfb-pokal',
            'champions league', 'europa league', 'conference league',
            'der klassiker', 'revierderby',
        ];

        return in_array(strtolower($competition), $supportedCompetitions, TRUE);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'StadionWelt Germany';
        $this->platform = 'stadionwelt_germany';
        $this->description = 'StadionWelt - German football ticket platform for Bundesliga and DFB-Pokal';
        $this->baseUrl = 'https://www.stadionwelt-business.de';
        $this->venue = 'Various';
        $this->currency = 'EUR';
        $this->language = 'de-DE';
        $this->rateLimitSeconds = 3;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'bundesliga',
            '2_bundesliga',
            '3_liga',
            'dfb_pokal',
            'champions_league',
            'europa_league',
            'regionalliga',
            'hospitality_packages',
            'season_tickets',
            'stadium_tours',
            'der_klassiker',
            'revierderby',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'team',
            'city',
            'date_range',
            'competition',
            'venue',
            'price_range',
            'opponent',
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/tickets';
    }

    /**
     * Build search URL for StadionWelt Germany
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $query = $criteria['keyword'] ?? '';
        $filters = $criteria['filters'] ?? [];

        $params = [
            'suche'  => $query,
            'verein' => $filters['team'] ?? '',
            'stadt'  => $filters['city'] ?? '',
            'liga'   => $filters['competition'] ?? '',
            'datum'  => $filters['date'] ?? '',
            'gegner' => $filters['opponent'] ?? '',
        ];

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => ! empty($value));

        return $this->baseUrl . '/tickets?' . http_build_query($params);
    }

    /**
     * Scrape tickets from StadionWelt Germany
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("StadionWelt Germany Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // StadionWelt search results selectors
            $crawler->filter('.spiel-card, .ticket-box, .match-item, .event-listing')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('StadionWelt Germany Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('StadionWelt Germany Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('StadionWelt Germany Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.spiel-card, .ticket-box, .match-item, .event-listing')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('StadionWelt Germany Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.spieltitel, .match-title, .begegnung, h2 a, h3';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.spieltag, .datum, .date, .match-date';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.stadion, .venue, .spielort, .arena';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.preis, .price, .ab, .kosten';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.verfügbarkeit, .availability, .ausverkauft, .sold-out';
    }

    /**
     * Extract ticket data from DOM node
     */
    private function extractTicketData(Crawler $node): ?array
    {
        try {
            // Extract match information
            $homeTeam = $this->extractText($node, '.heimverein, .home-team, .heim');
            $awayTeam = $this->extractText($node, '.gastverein, .away-team, .gast');
            $date = $this->extractText($node, '.spieltag, .datum, .date, .match-date');
            $time = $this->extractText($node, '.anstoß, .uhrzeit, .time');
            $venue = $this->extractText($node, '.stadion, .venue, .spielort');
            $competition = $this->extractText($node, '.wettbewerb, .liga, .competition');
            $priceText = $this->extractText($node, '.preis, .price, .ab, .kosten');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Create match title
            $title = trim($homeTeam . ' vs ' . $awayTeam);
            if ($title === '' || $title === '0' || $title === 'vs') {
                $title = $this->extractText($node, '.spieltitel, .match-title, h3');
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
                'venue'        => $venue,
                'event_date'   => $eventDate,
                'link'         => $link,
                'platform'     => $this->platform,
                'category'     => 'football',
                'competition'  => $competition,
                'home_team'    => $homeTeam,
                'away_team'    => $awayTeam,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('StadionWelt Germany Plugin: Error extracting ticket data: ' . $e->getMessage());

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
     * Parse time from German text
     */
    private function parseTime(string $time): ?string
    {
        // Handle German time formats like "15:30 Uhr", "15.30", etc.
        if (preg_match('/(\d{1,2})[:.h](\d{2})/', $time, $matches)) {
            return sprintf('%02d:%02d', $matches[1], $matches[2]);
        }

        return NULL;
    }

    /**
     * Parse price from German text
     */
    private function parsePrice(string $priceText): array
    {
        if ($priceText === '' || $priceText === '0') {
            return ['min' => NULL, 'max' => NULL];
        }

        // Handle German price formats
        $priceText = str_replace(['ab ', 'ab', '€'], '', strtolower($priceText));

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
        $availabilityText = $this->extractText($node, '.verfügbarkeit, .availability, .status');

        if (preg_match('/ausverkauft|sold.?out|nicht verfügbar/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/wenige tickets|few tickets|begrenzt|letzte/i', $availabilityText)) {
            return 'limited';
        }

        return 'available';
    }
}
