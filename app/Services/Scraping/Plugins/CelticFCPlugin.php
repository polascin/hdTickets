<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class CelticFCPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Celtic FC';
        $this->platform = 'celtic_fc';
        $this->description = 'Official Celtic FC tickets - Scottish Premiership, European competitions';
        $this->baseUrl = 'https://www.celticfc.com';
        $this->venue = 'Celtic Park';
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
            'scottish_premiership',
            'champions_league',
            'europa_league',
            'europa_conference_league',
            'scottish_cup',
            'league_cup',
            'old_firm_derby',
            'season_tickets',
            'hospitality_packages',
            'premium_experiences',
            'youth_matches',
            'womens_team',
            'friendly_matches',
            'testimonial_matches',
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
            'match_importance',
        ];
    }

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
                'url' => $searchUrl,
                'results_found' => count($filteredEvents),
            ]);

            return $filteredEvents;
        } catch (Exception $e) {
            Log::error("{$this->pluginName} scraping failed", [
                'criteria' => $criteria,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Build search URL based on criteria
     */
    protected function buildSearchUrl(array $criteria): string
    {
        $baseUrl = $this->baseUrl . '/tickets';
        
        $params = [];
        
        if (!empty($criteria['keyword'])) {
            $params['search'] = urlencode($criteria['keyword']);
        }
        
        if (!empty($criteria['competition'])) {
            $params['competition'] = urlencode($criteria['competition']);
        }
        
        if (!empty($criteria['opponent'])) {
            $params['opponent'] = urlencode($criteria['opponent']);
        }
        
        if (!empty($criteria['ticket_type'])) {
            $params['type'] = urlencode($criteria['ticket_type']);
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
            $crawler->filter('.fixture-item, .match-item, .event-item, [data-testid="fixture"]')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseMatchItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse Celtic FC match item", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse Celtic FC search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual match item
     */
    protected function parseMatchItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.fixture-title, .match-title, h2, h3');
            $opponent = $this->extractText($node, '.opponent, .vs, .against');
            $competition = $this->extractText($node, '.competition, .tournament, .league');
            $date = $this->extractText($node, '.date, .fixture-date, .match-date, time');
            $time = $this->extractText($node, '.time, .kick-off, .fixture-time');
            $venue = $this->extractText($node, '.venue, .stadium');
            $priceText = $this->extractText($node, '.price, .from-price, .ticket-price');
            $availability = $this->extractText($node, '.availability, .status, .sold-out');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title) && empty($opponent)) {
                return null;
            }

            // Build match title if needed
            if (empty($title) && !empty($opponent)) {
                $title = "Celtic FC vs {$opponent}";
            }

            // Parse price
            $price = $this->parsePrice($priceText);

            // Parse date and time
            $eventDate = $this->parseDate($date);
            $eventTime = $this->parseTime($time);

            // Determine match importance
            $importance = $this->determineMatchImportance($title, $opponent, $competition);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : null;

            return [
                'title' => trim($title),
                'opponent' => trim($opponent),
                'competition' => trim($competition),
                'venue' => $this->venue,
                'location' => 'Glasgow, Scotland, G40 3RE',
                'date' => $eventDate,
                'time' => $eventTime,
                'match_importance' => $importance,
                'price' => $price,
                'currency' => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url' => $fullUrl,
                'platform' => $this->platform,
                'description' => null,
                'category' => 'football',
                'team' => 'Celtic FC',
                'capacity' => '60411',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse Celtic FC match item", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Determine match importance
     */
    protected function determineMatchImportance(string $title, string $opponent, string $competition): string
    {
        $lowerTitle = strtolower($title);
        $lowerOpponent = strtolower($opponent);
        $lowerComp = strtolower($competition);

        // Old Firm Derby (highest importance)
        if (strpos($lowerOpponent, 'rangers') !== false || strpos($lowerTitle, 'old firm') !== false) {
            return 'old_firm_derby';
        }

        // European competitions
        if (strpos($lowerComp, 'champions league') !== false) {
            return 'champions_league';
        }
        if (strpos($lowerComp, 'europa league') !== false) {
            return 'europa_league';
        }
        if (strpos($lowerComp, 'europa conference') !== false || strpos($lowerComp, 'conference league') !== false) {
            return 'europa_conference_league';
        }

        // Domestic cups
        if (strpos($lowerComp, 'scottish cup') !== false) {
            return 'scottish_cup';
        }
        if (strpos($lowerComp, 'league cup') !== false || strpos($lowerComp, 'viaplay cup') !== false) {
            return 'league_cup';
        }

        // League matches
        if (strpos($lowerComp, 'premiership') !== false || strpos($lowerComp, 'spfl') !== false) {
            return 'scottish_premiership';
        }

        return 'general_match';
    }

    /**
     * Parse availability status
     */
    protected function parseAvailability(string $status): string
    {
        $lowerStatus = strtolower($status);
        
        if (strpos($lowerStatus, 'sold out') !== false || strpos($lowerStatus, 'unavailable') !== false) {
            return 'sold_out';
        }
        
        if (strpos($lowerStatus, 'limited') !== false || strpos($lowerStatus, 'few left') !== false) {
            return 'limited';
        }
        
        if (strpos($lowerStatus, 'available') !== false || strpos($lowerStatus, 'on sale') !== false) {
            return 'available';
        }
        
        if (strpos($lowerStatus, 'season ticket') !== false || strpos($lowerStatus, 'members only') !== false) {
            return 'members_only';
        }

        return 'check_website';
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return null;
        }

        // Handle "from £X" format
        if (preg_match('/from\s*£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float)$matches[1];
        }

        // Handle regular £X format
        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }

    /**
     * Parse time from text
     */
    protected function parseTime(string $timeText): ?string
    {
        if (empty($timeText)) {
            return null;
        }

        try {
            // Handle various time formats including kick-off times
            if (preg_match('/(\d{1,2}):(\d{2})\s*(am|pm)?/i', $timeText, $matches)) {
                $hour = (int)$matches[1];
                $minute = $matches[2];
                $ampm = strtolower($matches[3] ?? '');
                
                if ($ampm === 'pm' && $hour < 12) {
                    $hour += 12;
                } elseif ($ampm === 'am' && $hour === 12) {
                    $hour = 0;
                }
                
                return sprintf('%02d:%s', $hour, $minute);
            }
            
            return null;
        } catch (Exception $e) {
            Log::debug("Failed to parse Celtic FC time", ['time' => $timeText, 'error' => $e->getMessage()]);
            return null;
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
        return '.fixture-title, .match-title, h2, h3';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .fixture-date, .match-date, time';
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

    /**
     * Get Old Firm Derby tickets
     */
    public function getOldFirmTickets(array $criteria = []): array
    {
        $criteria['opponent'] = 'Rangers';
        return $this->scrape($criteria);
    }

    /**
     * Get Champions League matches
     */
    public function getChampionsLeagueTickets(array $criteria = []): array
    {
        $criteria['competition'] = 'Champions League';
        return $this->scrape($criteria);
    }

    /**
     * Get Europa League matches
     */
    public function getEuropaLeagueTickets(array $criteria = []): array
    {
        $criteria['competition'] = 'Europa League';
        return $this->scrape($criteria);
    }

    /**
     * Get Scottish Premiership matches
     */
    public function getPremiership

(array $criteria = []): array
    {
        $criteria['competition'] = 'Scottish Premiership';
        return $this->scrape($criteria);
    }

    /**
     * Get Scottish Cup matches
     */
    public function getScottishCupTickets(array $criteria = []): array
    {
        $criteria['competition'] = 'Scottish Cup';
        return $this->scrape($criteria);
    }
}
