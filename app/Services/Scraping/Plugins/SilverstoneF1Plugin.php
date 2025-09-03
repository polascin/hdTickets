<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class SilverstoneF1Plugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Silverstone F1 Circuit';
        $this->platform = 'silverstone_f1';
        $this->description = 'Official Silverstone F1 Circuit tickets - British Grand Prix and motorsport events';
        $this->baseUrl = 'https://www.silverstone.co.uk';
        $this->venue = 'Silverstone Circuit';
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
            'formula_1_british_gp',
            'motogp',
            'british_touring_cars',
            'silverstone_classic',
            'f1_experiences',
            'grandstand_tickets',
            'general_admission',
            'hospitality_packages',
            'paddock_club',
            'driving_experiences',
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
            'seating_area',
            'price_range',
            'ticket_type',
            'experience_type',
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
        return $this->baseUrl . '/events';
    }

    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('.event-item, .race-item, .ticket-item')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse Silverstone event item", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse Silverstone search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-title, .race-title, h2, h3');
            $date = $this->extractText($node, '.date, .event-date');
            $eventType = $this->extractText($node, '.event-type, .category');
            $priceText = $this->extractText($node, '.price, .from-price');
            $availability = $this->extractText($node, '.availability, .status');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return null;
            }

            return [
                'title' => trim($title),
                'venue' => $this->venue,
                'location' => 'Silverstone, Northamptonshire, MK18 5SI',
                'date' => $this->parseDate($date),
                'event_type' => $this->determineEventType($title, $eventType),
                'price' => $this->parsePrice($priceText),
                'currency' => $this->currency,
                'availability' => $this->parseAvailability($availability),
                'url' => $link ? $this->buildFullUrl($link) : null,
                'platform' => $this->platform,
                'category' => 'motorsport',
                'circuit' => 'Silverstone Circuit',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse Silverstone event item", ['error' => $e->getMessage()]);
            return null;
        }
    }

    protected function determineEventType(string $title, string $eventType): string
    {
        $lowerTitle = strtolower($title);
        $lowerType = strtolower($eventType);

        if (strpos($lowerTitle, 'british grand prix') !== false || strpos($lowerTitle, 'f1') !== false) {
            return 'formula_1_british_gp';
        }
        if (strpos($lowerTitle, 'motogp') !== false) {
            return 'motogp';
        }
        if (strpos($lowerTitle, 'touring car') !== false || strpos($lowerTitle, 'btcc') !== false) {
            return 'british_touring_cars';
        }
        if (strpos($lowerTitle, 'classic') !== false) {
            return 'silverstone_classic';
        }

        return 'motorsport_event';
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
    protected function getTestUrl(): string { return $this->baseUrl . '/events'; }
    protected function getEventNameSelectors(): string { return '.event-title, .race-title, h2, h3'; }
    protected function getDateSelectors(): string { return '.date, .event-date'; }
    protected function getVenueSelectors(): string { return '.venue'; }
    protected function getPriceSelectors(): string { return '.price, .from-price'; }
    protected function getAvailabilitySelectors(): string { return '.availability, .status'; }
}
