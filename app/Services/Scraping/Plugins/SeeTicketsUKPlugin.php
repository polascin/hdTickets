<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class SeeTicketsUKPlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'See Tickets UK';
        $this->platform = 'seetickets_uk';
        $this->description = 'See Tickets UK - Music, comedy, theatre and entertainment events';
        $this->baseUrl = 'https://www.seetickets.com';
        $this->venue = 'Various UK Venues';
        $this->currency = 'GBP';
        $this->language = 'en-GB';
        $this->rateLimitSeconds = 1;
    }

    protected function getCapabilities(): array
    {
        return ['music_events', 'comedy_shows', 'theatre', 'festivals', 'club_events'];
    }

    protected function getSupportedCriteria(): array
    {
        return ['keyword', 'date_range', 'genre', 'venue', 'price_range'];
    }

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
            $crawler->filter('.event-listing, .show-item')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseEventItem($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse See Tickets UK event item", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse See Tickets UK search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    protected function parseEventItem(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '.event-name, .show-title, h2, h3');
            $venue = $this->extractText($node, '.venue-name, .location');
            $date = $this->extractText($node, '.event-date, .date');
            $priceText = $this->extractText($node, '.price, .ticket-price');
            $link = $this->extractAttribute($node, 'a', 'href');

            if (empty($title)) {
                return null;
            }

            return [
                'title' => trim($title),
                'venue' => trim($venue) ?: $this->venue,
                'date' => $this->parseDate($date),
                'price' => $this->parsePrice($priceText),
                'currency' => $this->currency,
                'url' => $link ? $this->buildFullUrl($link) : null,
                'platform' => $this->platform,
                'category' => 'entertainment',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse See Tickets UK event item", ['error' => $e->getMessage()]);
            return null;
        }
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
    protected function getEventNameSelectors(): string { return '.event-name, .show-title, h2, h3'; }
    protected function getDateSelectors(): string { return '.event-date, .date'; }
    protected function getVenueSelectors(): string { return '.venue-name, .location'; }
    protected function getPriceSelectors(): string { return '.price, .ticket-price'; }
    protected function getAvailabilitySelectors(): string { return '.availability, .status'; }
}
