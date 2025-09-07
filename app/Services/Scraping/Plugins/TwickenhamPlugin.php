<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function sprintf;

class TwickenhamPlugin extends BaseScraperPlugin
{
    /**
     * Main scraping method
     */
    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
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
     * Get Six Nations matches
     */
    public function getSixNationsMatches(array $criteria = []): array
    {
        $criteria['competition'] = 'Six Nations';

        return $this->scrape($criteria);
    }

    /**
     * Get Autumn Internationals
     */
    public function getAutumnInternationalsMatches(array $criteria = []): array
    {
        $criteria['event_type'] = 'autumn_internationals';

        return $this->scrape($criteria);
    }

    /**
     * Get England rugby matches
     */
    public function getEnglandRugbyMatches(array $criteria = []): array
    {
        $criteria['event_type'] = 'england_rugby';

        return $this->scrape($criteria);
    }

    /**
     * Get Premiership Final
     */
    public function getPremiership(array $criteria = []): array
    {
        $criteria['event_type'] = 'premiership_final';

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
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Twickenham Stadium';
        $this->platform = 'twickenham';
        $this->description = 'Official Twickenham Stadium tickets - Home of Rugby';
        $this->baseUrl = 'https://www.twickenhamstadium.com';
        $this->venue = 'Twickenham Stadium';
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
            'england_rugby',
            'six_nations',
            'autumn_internationals',
            'rugby_world_cup',
            'premiership_final',
            'challenge_cup_final',
            'championship_final',
            'concerts',
            'hospitality_packages',
            'premium_experiences',
            'corporate_events',
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
            'opponent',
            'price_range',
            'seating_area',
            'ticket_type',
            'event_type',
        ];
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseUrl = $this->baseUrl . '/events';

        $params = [];

        if (!empty($criteria['keyword'])) {
            $params['search'] = urlencode($criteria['keyword']);
        }

        if (!empty($criteria['event_type'])) {
            $params['type'] = urlencode($criteria['event_type']);
        }

        if (!empty($criteria['competition'])) {
            $params['competition'] = urlencode($criteria['competition']);
        }

        if (!empty($criteria['opponent'])) {
            $params['opponent'] = urlencode($criteria['opponent']);
        }

        if (!empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['date_from'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['date_to'] = $criteria['date_range']['end'];
            }
        }

        $queryString = http_build_query($params);

        return $baseUrl . ($queryString ? '?' . $queryString : '');
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.fixture-item, .match-item, .event-item, [data-testid="event"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse Twickenham event item', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse Twickenham search results', ['error' => $e->getMessage()]);
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
            $competition = $this->extractText($node, '.competition, .tournament, .championship');
            $date = $this->extractText($node, '.date, .event-date, .match-date, time');
            $time = $this->extractText($node, '.time, .kick-off, .start-time');
            $teams = $this->extractText($node, '.teams, .vs, .opponents');
            $priceText = $this->extractText($node, '.price, .from-price, .ticket-price');
            $availability = $this->extractText($node, '.availability, .status, .sold-out');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
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
                'location'     => 'Twickenham, London, TW1 1DZ',
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
                'stadium'      => 'Twickenham Stadium',
                'capacity'     => '82000',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse Twickenham event item', ['error' => $e->getMessage()]);

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

        if (strpos($lowerComp, 'six nations') !== FALSE) {
            return 'six_nations';
        }
        if (strpos($lowerComp, 'autumn') !== FALSE || strpos($lowerTitle, 'autumn') !== FALSE) {
            return 'autumn_internationals';
        }
        if (strpos($lowerComp, 'world cup') !== FALSE) {
            return 'rugby_world_cup';
        }
        if (strpos($lowerComp, 'premiership') !== FALSE && strpos($lowerComp, 'final') !== FALSE) {
            return 'premiership_final';
        }
        if (strpos($lowerComp, 'challenge cup') !== FALSE) {
            return 'challenge_cup_final';
        }
        if (strpos($lowerComp, 'championship') !== FALSE && strpos($lowerComp, 'final') !== FALSE) {
            return 'championship_final';
        }
        if (strpos($lowerTitle, 'england') !== FALSE) {
            return 'england_rugby';
        }
        if (strpos($lowerTitle, 'concert') !== FALSE || strpos($lowerTitle, 'tour') !== FALSE) {
            return 'concert';
        }

        return 'rugby_match';
    }

    /**
     * Determine category from event type
     */
    protected function determineCategory(string $eventType): string
    {
        if ($eventType === 'concert') {
            return 'music';
        }

        return 'rugby';
    }

    /**
     * Parse availability status
     */
    protected function parseAvailability(string $status): string
    {
        $lowerStatus = strtolower($status);

        if (strpos($lowerStatus, 'sold out') !== FALSE || strpos($lowerStatus, 'unavailable') !== FALSE) {
            return 'sold_out';
        }

        if (strpos($lowerStatus, 'limited') !== FALSE || strpos($lowerStatus, 'few left') !== FALSE) {
            return 'limited';
        }

        if (strpos($lowerStatus, 'available') !== FALSE || strpos($lowerStatus, 'on sale') !== FALSE) {
            return 'available';
        }

        return 'check_website';
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
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
        if (empty($timeText)) {
            return NULL;
        }

        try {
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
            Log::debug('Failed to parse Twickenham time', ['time' => $timeText, 'error' => $e->getMessage()]);

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
