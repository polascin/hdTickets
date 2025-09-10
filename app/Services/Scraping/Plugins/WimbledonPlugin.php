<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Override;
use Symfony\Component\DomCrawler\Crawler;

use function count;
use function sprintf;

class WimbledonPlugin extends BaseScraperPlugin
{
    protected string $pluginName = 'Wimbledon Championships';

    protected string $platform = 'wimbledon';

    protected string $description = 'Official Wimbledon Championships tickets - The most prestigious tennis tournament';

    protected string $baseUrl = 'https://www.wimbledon.com';

    protected string $venue = 'All England Lawn Tennis Club';

    protected string $currency = 'GBP';

    protected string $language = 'en-GB';

    protected int $rateLimitSeconds = 2;

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
     * Get Centre Court tickets
     */
    public function getCentreCourtTickets(array $criteria = []): array
    {
        $criteria['court'] = 'Centre Court';

        return $this->scrape($criteria);
    }

    /**
     * Get Court No. 1 tickets
     */
    public function getCourt1Tickets(array $criteria = []): array
    {
        $criteria['court'] = 'Court No. 1';

        return $this->scrape($criteria);
    }

    /**
     * Get Ground Pass tickets
     */
    public function getGroundPasses(array $criteria = []): array
    {
        $criteria['ticket_type'] = 'ground_pass';

        return $this->scrape($criteria);
    }

    /**
     * Get Hospitality packages
     */
    public function getHospitalityPackages(array $criteria = []): array
    {
        $criteria['ticket_type'] = 'hospitality';

        return $this->scrape($criteria);
    }

    /**
     * Get tickets by round
     */
    public function getTicketsByRound(string $round, array $criteria = []): array
    {
        $criteria['round'] = $round;

        return $this->scrape($criteria);
    }

    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        // Properties are now initialized at the class level.
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'championship_tickets',
            'centre_court',
            'court_no_1',
            'ground_passes',
            'hospitality_packages',
            'debenture_seats',
            'premium_experiences',
            'practice_courts',
            'qualifying_rounds',
            'championships',
            'junior_championships',
            'wheelchair_tennis',
            'legends_doubles',
            'invitation_doubles',
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
            'court',
            'ticket_type',
            'session',
            'round',
            'price_range',
            'hospitality_level',
        ];
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseUrl = $this->baseUrl . '/tickets';

        $params = [];

        if (! empty($criteria['keyword'])) {
            $params['search'] = urlencode((string) $criteria['keyword']);
        }

        if (! empty($criteria['court'])) {
            $params['court'] = urlencode((string) $criteria['court']);
        }

        if (! empty($criteria['ticket_type'])) {
            $params['type'] = urlencode((string) $criteria['ticket_type']);
        }

        if (! empty($criteria['session'])) {
            $params['session'] = urlencode((string) $criteria['session']);
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
            $crawler->filter('.ticket-item, .event-item, .session-item, [data-testid="ticket-item"]')->each(function (Crawler $node) use (&$events): void {
                try {
                    $event = $this->parseTicketItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug('Failed to parse Wimbledon ticket item', ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning('Failed to parse Wimbledon search results', ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual ticket item
     */
    protected function parseTicketItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.session-title, .event-title, h3, .ticket-name');
            $court = $this->extractText($node, '.court-name, .venue-name');
            $date = $this->extractText($node, '.date, .session-date, time');
            $time = $this->extractText($node, '.time, .session-time');
            $round = $this->extractText($node, '.round, .stage');
            $priceText = $this->extractText($node, '.price, .ticket-price, .from-price');
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

            // Determine ticket type
            $ticketType = $this->determineTicketType($title, $court);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : NULL;

            return [
                'title'        => trim($title),
                'court'        => trim($court),
                'venue'        => $this->venue,
                'location'     => 'Wimbledon, London, SW19',
                'date'         => $eventDate,
                'time'         => $eventTime,
                'round'        => trim($round),
                'ticket_type'  => $ticketType,
                'price'        => $price,
                'currency'     => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url'          => $fullUrl,
                'platform'     => $this->platform,
                'description'  => NULL,
                'category'     => 'tennis',
                'tournament'   => 'Wimbledon Championships',
                'scraped_at'   => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug('Failed to parse Wimbledon ticket item', ['error' => $e->getMessage()]);

            return NULL;
        }
    }

    /**
     * Determine ticket type from title and court
     */
    protected function determineTicketType(string $title, string $court): string
    {
        $lowerTitle = strtolower($title);
        $lowerCourt = strtolower($court);

        if (str_contains($lowerTitle, 'hospitality') || str_contains($lowerTitle, 'vip')) {
            return 'hospitality';
        }

        if (str_contains($lowerTitle, 'debenture')) {
            return 'debenture';
        }

        if (str_contains($lowerCourt, 'centre court')) {
            return 'centre_court';
        }

        if (str_contains($lowerCourt, 'court no. 1') || str_contains($lowerCourt, 'court 1')) {
            return 'court_1';
        }

        if (str_contains($lowerTitle, 'ground pass') || str_contains($lowerTitle, 'grounds')) {
            return 'ground_pass';
        }

        return 'general';
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
            // Handle various time formats
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
            Log::debug('Failed to parse Wimbledon time', ['time' => $timeText, 'error' => $e->getMessage()]);

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
        return $this->baseUrl . '/tickets';
    }

    protected function getEventNameSelectors(): string
    {
        return '.session-title, .event-title, h3, .ticket-name';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .session-date, time';
    }

    protected function getVenueSelectors(): string
    {
        return '.court-name, .venue-name';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .ticket-price, .from-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.availability, .status, .sold-out';
    }
}
