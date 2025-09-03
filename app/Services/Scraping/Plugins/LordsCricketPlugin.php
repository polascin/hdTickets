<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class LordsCricketPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Lord\'s Cricket Ground';
        $this->platform = 'lords_cricket';
        $this->description = 'Official Lord\'s Cricket Ground tickets - Home of Cricket';
        $this->baseUrl = 'https://www.lords.org';
        $this->venue = 'Lord\'s Cricket Ground';
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
            'test_matches',
            'odi_matches',
            't20_matches',
            'county_championship',
            'vitality_blast',
            'the_hundred',
            'world_cup_matches',
            'ashes_series',
            'lords_final',
            'mcc_matches',
            'hospitality_packages',
            'ground_tours',
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
            'match_format',
            'teams',
            'price_range',
            'seating_area',
            'ticket_type',
            'competition',
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

    protected function buildSearchUrl(array $criteria): string
    {
        return $this->baseUrl . '/tickets';
    }

    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.match-item, .fixture-item, .event-item')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseMatchItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse Lords match item", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse Lords search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    protected function parseMatchItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.match-title, .event-title, h2, h3');
            $teams = $this->extractText($node, '.teams, .vs, .opponents');
            $date = $this->extractText($node, '.date, .match-date, time');
            $format = $this->extractText($node, '.format, .match-type');
            $priceText = $this->extractText($node, '.price, .from-price');
            $availability = $this->extractText($node, '.availability, .status');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return null;
            }

            return [
                'title' => trim($title),
                'teams' => trim($teams),
                'venue' => $this->venue,
                'location' => 'St John\'s Wood, London, NW8 8QN',
                'date' => $this->parseDate($date),
                'match_format' => trim($format),
                'price' => $this->parsePrice($priceText),
                'currency' => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url' => $link ? $this->buildFullUrl($link) : null,
                'platform' => $this->platform,
                'category' => 'cricket',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse Lords match item", ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function parseAvailability(string $status): string
    {
        $lowerStatus = strtolower($status);
        
        if (strpos($lowerStatus, 'sold out') !== false) {
            return 'sold_out';
        }
        if (strpos($lowerStatus, 'available') !== false) {
            return 'available';
        }
        
        return 'check_website';
    }

    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText)) {
            return null;
        }

        if (preg_match('/£(\d+(?:\.\d{2})?)/', $priceText, $matches)) {
            return (float)$matches[1];
        }

        return null;
    }

    protected function buildFullUrl(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    // Required abstract methods
    protected function getTestUrl(): string { return $this->baseUrl . '/tickets'; }
    protected function getEventNameSelectors(): string { return '.match-title, .event-title, h2, h3'; }
    protected function getDateSelectors(): string { return '.date, .match-date, time'; }
    protected function getVenueSelectors(): string { return '.venue'; }
    protected function getPriceSelectors(): string { return '.price, .from-price'; }
    protected function getAvailabilitySelectors(): string { return '.availability, .status'; }
}
