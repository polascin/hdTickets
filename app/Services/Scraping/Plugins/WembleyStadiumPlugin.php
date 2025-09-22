<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function in_array;
use function sprintf;

class WembleyStadiumPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
    #[Override]
    public function scrape(array $criteria): array
    {
        if (! $this->enabled) {
            throw new Exception("{$this->pluginName} plugin is disabled");
        }

        Log::info("Starting {$this->pluginName} scraping", $criteria);

        try {
            $this->applyRateLimit($this->platform);

            $searchUrl = $this->buildSearchUrl($criteria);
            $html = $this->makeHttpRequest($searchUrl);
            $events = $this->parseSearchResults($html);
            $filteredEvents = $this->filterResults($events, $criteria);

            Log::info("{$this->pluginName} scraping completed", [
                'url'           => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error("{$this->pluginName} scraping failed", [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get England national team matches
     */
    public function getEnglandMatches(array $criteria = []): array
    {
        $criteria['event_type'] = 'england_national_team';

        return $this->scrape($criteria);
    }

    /**
     * Get FA Cup matches
     */
    public function getFACupMatches(array $criteria = []): array
    {
        $criteria['competition'] = 'FA Cup';

        return $this->scrape($criteria);
    }

    /**
     * Get EFL Cup matches
     */
    public function getEFLCupMatches(array $criteria = []): array
    {
        $criteria['competition'] = 'EFL Cup';

        return $this->scrape($criteria);
    }

    /**
     * Get playoff finals
     */
    public function getPlayoffFinals(array $criteria = []): array
    {
        $criteria['event_type'] = 'playoff_final';

        return $this->scrape($criteria);
    }

    /**
     * Get concerts
     */
    public function getConcerts(array $criteria = []): array
    {
        $criteria['event_type'] = 'concert';

        return $this->scrape($criteria);
    }

    /**
     * Get NFL games
     */
    public function getNFLGames(array $criteria = []): array
    {
        $criteria['event_type'] = 'nfl';

        return $this->scrape($criteria);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Wembley Stadium';
        $this->platform = 'wembley_stadium';
        $this->description = 'Official Wembley Stadium tickets - England national team, FA Cup Final, major events';
        $this->baseUrl = 'https://www.wembleystadium.com';
        $this->venue = 'Wembley Stadium';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'england_national_team',
            'fa_cup_final',
            'efl_cup_final',
            'playoff_finals',
            'major_football_events',
            'concerts',
            'community_shield',
            'international_matches',
            'nfl_games',
            'boxing_events',
            'rugby_league_challenge_cup',
            'olympic_events',
            'premium_experiences',
            'hospitality_packages',
            'club_wembley',
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
            'event_type',
            'competition',
            'price_range',
            'seating_area',
            'ticket_type',
            'team',
        ];
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseUrl = $this->baseUrl . '/events';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['search'] = urlencode((string) $criteria['keyword']);
        }

        if (! empty($criteria['event_type'])) {
            $params['type'] = urlencode((string) $criteria['event_type']);
        }

        if (! empty($criteria['competition'])) {
            $params['competition'] = urlencode((string) $criteria['competition']);
        }

        if (! empty($criteria['team'])) {
            $params['team'] = urlencode((string) $criteria['team']);
        }

        if (! empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['date_from'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['date_to'] = $criteria['date_range']['end'];
            }
        }

        $queryString = http_build_query($params);

        return $baseUrl . ($queryString !== '' && $queryString !== '0' ? '?' . $queryString : '');
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.event-item, .fixture-item, .match-item, [data-testid="event-item"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse Wembley event item', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse Wembley search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event item
     */
    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-title, .fixture-title, h2, h3');
            $competition = $this->extractText($node, '.competition, .tournament, .league');
            $date = $this->extractText($node, '.date, .event-date, .match-date, time');
            $time = $this->extractText($node, '.time, .event-time, .kick-off');
            $teams = $this->extractText($node, '.teams, .vs, .opponents');
            $priceText = $this->extractText($node, '.price, .from-price, .ticket-price');
            $availability = $this->extractText($node, '.availability, .status, .sold-out');
            $link = $this->extractAttribute($node, 'a', 'href');

            if ($title === '' || $title === '0') {
                return NULL;
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDate($date);
            $eventTime = $this->parseTime($time);

            // Determine event type
            $eventType = $this->determineEventType($title, $competition);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : NULL;

            return [
                'title'        => trim($title),
                'competition'  => trim($competition),
                'venue'        => $this->venue,
                'location'     => 'Wembley, London, HA9',
                'date'         => $eventDate,
                'time'         => $eventTime,
                'teams'        => trim($teams),
                'event_type'   => $eventType,
                'price'        => $price,
                'currency'     => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url'          => $fullUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => $this->determineCategory($eventType),
                'stadium'      => 'Wembley Stadium',
                'capacity'     => '90000',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse Wembley event item', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Determine event type from title and competition
     */
    protected function determineEventType(string $title, string $competition): string
    {
        $lowerTitle = strtolower($title);
        $lowerComp = strtolower($competition);

        // Football events
        if (str_contains($lowerComp, 'fa cup')) {
            return 'fa_cup';
        }
        if (str_contains($lowerComp, 'efl cup') || str_contains($lowerComp, 'league cup')) {
            return 'efl_cup';
        }
        if (str_contains($lowerComp, 'playoff')) {
            return 'playoff_final';
        }
        if (str_contains($lowerComp, 'community shield')) {
            return 'community_shield';
        }
        if (str_contains($lowerTitle, 'england') || str_contains($lowerComp, 'international')) {
            return 'england_national_team';
        }
        if (str_contains($lowerTitle, 'challenge cup')) {
            return 'rugby_league_challenge_cup';
        }

        // Non-football events
        if (str_contains($lowerTitle, 'concert') || str_contains($lowerTitle, 'tour')) {
            return 'concert';
        }
        if (str_contains($lowerTitle, 'nfl')) {
            return 'nfl';
        }
        if (str_contains($lowerTitle, 'boxing') || str_contains($lowerTitle, 'fight')) {
            return 'boxing';
        }

        return 'general_event';
    }

    /**
     * Determine category from event type
     */
    protected function determineCategory(string $eventType): string
    {
        if (in_array($eventType, ['fa_cup', 'efl_cup', 'playoff_final', 'community_shield', 'england_national_team'], TRUE)) {
            return 'football';
        }
        if ($eventType === 'rugby_league_challenge_cup') {
            return 'rugby';
        }
        if ($eventType === 'concert') {
            return 'music';
        }
        if ($eventType === 'nfl') {
            return 'american_football';
        }
        if ($eventType === 'boxing') {
            return 'boxing';
        }

        return 'sports';
    }

    /**
     * Parse availability status
     */
    protected function parseAvailability(string $status): string
    {
        $lowerStatus = strtolower($status);

        if (str_contains($lowerStatus, 'sold out') || str_contains($lowerStatus, 'unavailable')) {
            return 'sold_out';
        }

        if (str_contains($lowerStatus, 'limited') || str_contains($lowerStatus, 'few left')) {
            return 'limited';
        }

        if (str_contains($lowerStatus, 'available') || str_contains($lowerStatus, 'on sale')) {
            return 'available';
        }

        return 'check_website';
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if ($priceText === '' || $priceText === '0') {
            return NULL;
        }

        // Handle "from £X" format
        if (preg_match('/from\s*£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        // Handle regular £X format
        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float) $matches[1];
        }

        return NULL;
    }

    /**
     * Parse time from text
     */
    protected function parseTime(string $timeText): ?string
    {
        if ($timeText === '' || $timeText === '0') {
            return NULL;
        }

        try {
            // Handle various time formats including kick-off times
            if (preg_match('/(\d{1,2}):(\d{2})\s*(am|pm)?/i', $timeText, $matches)) {
                $hour = (int) $matches[1];
                $minute = $matches[2];
                $ampm = strtolower($matches[3] ?? '');

                if ($ampm === 'pm' && $hour < 12) {
                    $hour += 12;
                } elseif ($ampm === 'am' && $hour === 12) {
                    $hour = 0;
                }

                return sprintf('%02d:%s', $hour, $minute);
            }

            return NULL;
        } catch (Exception $e) {
            Log::debug('Failed to parse Wembley time', ['time' => $timeText, 'error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Build full URL
     */
    protected function buildFullUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Required abstract methods from BaseScraperPlugin

    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/events';
    }

    protected function getEventNameSelectors(): string
    {
        return '.event-title, .fixture-title, h2, h3';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .event-date, .match-date, time';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue, .stadium';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .from-price, .ticket-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out';
    }
}
