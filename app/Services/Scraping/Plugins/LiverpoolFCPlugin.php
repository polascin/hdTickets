<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Log;
use Symfony\Component\DomCrawler\Crawler;

class LiverpoolFCPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Liverpool FC';
        $this->platform = 'liverpoolfc';
        $this->description = 'Official Liverpool FC tickets - Premier League, Champions League, FA Cup, Carabao Cup';
        $this->baseUrl = 'https://www.liverpoolfc.com';
        $this->venue = 'Anfield';
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
            'anfield_tours',
            'merseyside_derby',
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
            'team',
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
        // Liverpool FC specific URL structure
        $baseSearchUrl = $this->baseUrl . '/tickets';
        
        if (!empty($criteria['keyword'])) {
            $baseSearchUrl .= '?search=' . urlencode($criteria['keyword']);
        }
        
        return $baseSearchUrl;
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.fixture-item, .match-item, .event-item')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse Liverpool FC event item", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse Liverpool FC search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event item
     */
    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.match-title, .fixture-title, h3');
            $opponent = $this->extractText($node, '.opponent, .away-team, .vs');
            $venue = $this->extractText($node, '.venue, .stadium') ?: 'Anfield';
            $date = $this->extractText($node, '.date, .fixture-date, time');
            $time = $this->extractText($node, '.time, .kick-off');
            $competition = $this->extractText($node, '.competition, .league');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title) && empty($opponent)) {
                return null;
            }

            // Build title if not available
            if (empty($title) && !empty($opponent)) {
                $title = 'Liverpool vs ' . $opponent;
            }

            // Parse date
            $eventDate = $this->parseDate($date);
            $eventTime = $this->parseTime($time);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : null;

            return [
                'title' => trim($title),
                'opponent' => trim($opponent),
                'venue' => trim($venue),
                'location' => 'Liverpool, England',
                'date' => $eventDate,
                'time' => $eventTime,
                'competition' => trim($competition),
                'price' => null, // Usually requires login
                'currency' => $this->currency,
                'url' => $fullUrl,
                'platform' => $this->platform,
                'description' => null,
                'category' => 'football',
                'availability' => 'check_website',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse Liverpool FC event item", ['error' => $e->getMessage()]);
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
        return '.match-title, .fixture-title, h3';
    }

    protected function getDateSelectors(): string
    {
        return '.date, .fixture-date, time';
    }

    protected function getVenueSelectors(): string
    {
        return '.venue, .stadium';
    }

    protected function getPriceSelectors(): string
    {
        return '.price, .ticket-price';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.status, .availability, .sold-out';
    }
}
