<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class Manchester_unitedPlugin extends BaseScraperPlugin
{
    /**
     * Get search suggestions for Manchester United
     */
    public function getSearchSuggestions(): array
    {
        return [
            'Competitions' => [
                'Premier League',
                'Champions League',
                'Europa League',
                'FA Cup',
                'Carabao Cup',
                'Manchester Derby',
                'Women\'s Super League',
            ],
            'Major Opponents' => [
                'Manchester City',
                'Liverpool',
                'Arsenal',
                'Chelsea',
                'Tottenham',
                'Real Madrid',
                'Barcelona',
                'Bayern Munich',
            ],
            'Ticket Types' => [
                'General Admission',
                'Season Tickets',
                'Hospitality Packages',
                'VIP Experiences',
                'Family Tickets',
                'Disabled Access',
                'Away Tickets',
            ],
            'Teams' => [
                'First Team',
                'Women\'s Team',
                'Academy',
                'Legends',
            ],
        ];
    }

    /**
     * Check if plugin supports a specific competition
     */
    public function supportsCompetition(string $competition): bool
    {
        $supportedCompetitions = [
            'premier league', 'premier', 'epl',
            'champions league', 'champions', 'ucl',
            'europa league', 'europa', 'uel',
            'fa cup', 'facup', 'the fa cup',
            'carabao cup', 'league cup', 'efl cup',
            'manchester derby', 'derby',
            'womens super league', 'wsl',
        ];

        return in_array(strtolower($competition), $supportedCompetitions, TRUE);
    }

    /**
     * Check if plugin supports a specific opponent
     */
    public function supportsOpponent(string $opponent): bool
    {
        // Manchester United plays against all Premier League teams and various European teams
        $majorOpponents = [
            'manchester city', 'city', 'liverpool', 'arsenal', 'chelsea',
            'tottenham', 'spurs', 'everton', 'leeds', 'newcastle',
            'real madrid', 'barcelona', 'bayern munich', 'juventus',
        ];

        return in_array(strtolower($opponent), $majorOpponents, TRUE);
    }

    /**
     * Get venue capacity information
     */
    public function getVenueInfo(): array
    {
        return [
            'name'     => 'Old Trafford',
            'capacity' => 74310,
            'location' => 'Manchester, England',
            'nickname' => 'The Theatre of Dreams',
            'opened'   => 1910,
            'surface'  => 'Grass',
        ];
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Manchester United FC';
        $this->platform = 'manchester_united';
        $this->description = 'Official Manchester United FC tickets - Premier League, Champions League, FA Cup, Carabao Cup';
        $this->baseUrl = 'https://www.manutd.com';
        $this->venue = 'Old Trafford';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 3;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'premier_league',
            'champions_league',
            'europa_league',
            'fa_cup',
            'carabao_cup',
            'hospitality_packages',
            'season_tickets',
            'old_trafford_tours',
            'manchester_derby',
            'womens_football',
            'youth_teams',
            'legends_matches',
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
            'ticket_type',
            'team', // first-team/women/academy
        ];
    }

    /**
     * Get test URL for connectivity check
     */
    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/tickets-and-hospitality';
    }

    /**
     * Build search URL from criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseSearchUrl = $this->baseUrl . '/tickets-and-hospitality/tickets';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['search'] = $criteria['keyword'];
        }

        if (! empty($criteria['competition'])) {
            $params['competition'] = $this->mapCompetition($criteria['competition']);
        }

        if (! empty($criteria['team'])) {
            $params['team'] = $criteria['team'];
        }

        if (! empty($criteria['date_from'])) {
            $params['from'] = $criteria['date_from'];
        }

        if (! empty($criteria['date_to'])) {
            $params['to'] = $criteria['date_to'];
        }

        if (! empty($criteria['ticket_type'])) {
            $params['type'] = $criteria['ticket_type'];
        }

        // Remove empty parameters
        $params = array_filter($params, fn ($value): bool => ! empty($value));

        if ($params !== []) {
            return $baseSearchUrl . '?' . http_build_query($params);
        }

        return $baseSearchUrl;
    }

    /**
     * Scrape tickets from Manchester United
     */
    protected function scrapeTickets(array $criteria): array
    {
        $searchUrl = $this->buildSearchUrl($criteria);

        try {
            Log::info("Manchester United Plugin: Scraping tickets from: {$searchUrl}");

            $response = $this->makeHttpRequest($searchUrl);
            if (! $response) {
                return [];
            }

            $crawler = new Crawler($response);
            $tickets = [];

            // Manchester United ticket selectors
            $crawler->filter('.fixture-card, .match-card, .ticket-item, .event-card')->each(function (Crawler $node) use (&$tickets): void {
                try {
                    $ticket = $this->extractTicketData($node);
                    if ($ticket && $this->validateTicketData($ticket)) {
                        $tickets[] = $ticket;
                    }
                } catch (Exception $e) {
                    Log::warning('Manchester United Plugin: Error extracting ticket: ' . $e->getMessage());
                }
            });

            Log::info('Manchester United Plugin: Found ' . count($tickets) . ' tickets');

            return $tickets;
        } catch (Exception $e) {
            Log::error('Manchester United Plugin: Scraping error: ' . $e->getMessage());

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

        $crawler->filter('.fixture-card, .match-card, .ticket-item, .event-card')->each(function (Crawler $node) use (&$tickets): void {
            try {
                $ticket = $this->extractTicketData($node);
                if ($ticket && $this->validateTicketData($ticket)) {
                    $tickets[] = $ticket;
                }
            } catch (Exception $e) {
                Log::warning('Manchester United Plugin: Error extracting ticket: ' . $e->getMessage());
            }
        });

        return $tickets;
    }

    /**
     * Get event name selectors
     */
    protected function getEventNameSelectors(): string
    {
        return '.match-title, .fixture-title, .event-title, h2, h3';
    }

    /**
     * Get date selectors
     */
    protected function getDateSelectors(): string
    {
        return '.match-date, .fixture-date, .kickoff-date, .ko-date';
    }

    /**
     * Get venue selectors
     */
    protected function getVenueSelectors(): string
    {
        return '.venue, .stadium, .ground';
    }

    /**
     * Get price selectors
     */
    protected function getPriceSelectors(): string
    {
        return '.price, .cost, .from, .ticket-price';
    }

    /**
     * Get availability selectors
     */
    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out, .on-sale';
    }

    /**
     * Map competition names to Manchester United terms
     */
    private function mapCompetition(string $competition): string
    {
        $competitions = [
            'premier_league'      => 'Premier League',
            'champions_league'    => 'Champions League',
            'europa_league'       => 'Europa League',
            'fa_cup'              => 'FA Cup',
            'carabao_cup'         => 'Carabao Cup',
            'league_cup'          => 'Carabao Cup',
            'manchester_derby'    => 'Manchester Derby',
            'womens_super_league' => 'Women\'s Super League',
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
            $homeTeam = $this->extractText($node, '.home-team, .manchester-united, .united');
            $awayTeam = $this->extractText($node, '.away-team, .opponent, .visiting-team');
            $date = $this->extractText($node, '.match-date, .fixture-date, .kickoff-date');
            $time = $this->extractText($node, '.match-time, .kickoff-time, .ko-time');
            $competition = $this->extractText($node, '.competition, .tournament, .comp-name');
            $priceText = $this->extractText($node, '.price, .cost, .from, .ticket-price');
            $link = $this->extractAttribute($node, 'a', 'href');

            // Create match title - Manchester United is always home at Old Trafford
            $title = 'Manchester United';
            if ($awayTeam !== '' && $awayTeam !== '0') {
                $title .= ' vs ' . $awayTeam;
            } else {
                // Fallback to extract from general title
                $generalTitle = $this->extractText($node, '.match-title, .fixture-title, h3, h2');
                if ($generalTitle !== '' && $generalTitle !== '0') {
                    $title = $generalTitle;
                }
            }

            if ($title === 'Manchester United') {
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
                'competition'  => $competition ?: 'Premier League',
                'home_team'    => 'Manchester United',
                'away_team'    => $awayTeam,
                'availability' => $this->determineAvailability($node),
                'scraped_at'   => now(),
            ];
        } catch (Exception $e) {
            Log::warning('Manchester United Plugin: Error extracting ticket data: ' . $e->getMessage());

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
     * Parse time from British text
     */
    private function parseTime(string $time): ?string
    {
        // Handle British time formats like "15:00", "3:00 PM", "3pm"
        if (preg_match('/(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $time, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];
            $period = $matches[3] ?? '';

            if (strtoupper($period) === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif (strtoupper($period) === 'AM' && $hour === 12) {
                $hour = 0;
            }

            return sprintf('%02d:%02d', $hour, $minute);
        }

        // Handle "3pm" format
        if (preg_match('/(\d{1,2})\s*(AM|PM)/i', $time, $matches)) {
            $hour = (int) $matches[1];
            $period = strtoupper($matches[2]);

            if ($period === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'AM' && $hour === 12) {
                $hour = 0;
            }

            return sprintf('%02d:00', $hour);
        }

        return NULL;
    }

    /**
     * Parse price from British text
     */
    private function parsePrice(string $priceText): array
    {
        if ($priceText === '' || $priceText === '0') {
            return ['min' => NULL, 'max' => NULL];
        }

        // Handle British price formats
        $priceText = str_replace(['from ', 'From £', 'from £', '£'], '', strtolower($priceText));

        // Extract numeric values from price text
        preg_match_all('/[\d,]+\.?\d*/', $priceText, $matches);
        $prices = array_map(fn (string $price): float => (float) str_replace(',', '', $price), $matches[0]);

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
        $availabilityText = $this->extractText($node, '.availability, .status, .ticket-status');

        if (preg_match('/sold.?out|unavailable|not available/i', $availabilityText)) {
            return 'sold_out';
        }
        if (preg_match('/limited|few left|selling fast/i', $availabilityText)) {
            return 'limited';
        }
        if (preg_match('/on sale|available|buy now/i', $availabilityText)) {
            return 'available';
        }
        if (preg_match('/coming soon|not on sale yet/i', $availabilityText)) {
            return 'not_on_sale';
        }

        return 'available'; // Default for Manchester United
    }
}
