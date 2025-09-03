<?php declare(strict_types=1);

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class EventbritePlugin extends BaseScraperPlugin
{
    /**
     * Initialize plugin-specific settings
     */
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Eventbrite';
        $this->platform = 'eventbrite';
        $this->description = 'Eventbrite - Global event discovery and ticketing platform';
        $this->baseUrl = 'https://www.eventbrite.com';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->language = 'en-US';
        $this->rateLimitSeconds = 2;
    }

    /**
     * Get plugin capabilities
     */
    protected function getCapabilities(): array
    {
        return [
            'business_events',
            'entertainment',
            'food_drink',
            'health_wellness',
            'music',
            'arts_culture',
            'sports_fitness',
            'travel_outdoor',
            'charity_causes',
            'education',
            'family_fun',
            'fashion_beauty',
            'film_media',
            'government_politics',
            'hobbies_lifestyle',
            'home_garden',
            'performing_visual_arts',
            'religion_spirituality',
            'school_activities',
            'science_technology',
            'seasonal_holiday',
        ];
    }

    /**
     * Get supported search criteria
     */
    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'location',
            'date_range',
            'category',
            'format',
            'price',
            'sort_by',
            'distance',
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
        $params = [];
        
        if (!empty($criteria['keyword'])) {
            $params['q'] = urlencode($criteria['keyword']);
        }
        
        if (!empty($criteria['location'])) {
            $params['location'] = urlencode($criteria['location']);
        }
        
        if (!empty($criteria['category'])) {
            $params['categories'] = urlencode($criteria['category']);
        }
        
        if (!empty($criteria['date_range'])) {
            if (isset($criteria['date_range']['start'])) {
                $params['start_date'] = $criteria['date_range']['start'];
            }
            if (isset($criteria['date_range']['end'])) {
                $params['end_date'] = $criteria['date_range']['end'];
            }
        }

        if (!empty($criteria['format'])) {
            $params['event_type'] = $criteria['format']; // online, in_person
        }

        if (!empty($criteria['price'])) {
            $params['price'] = $criteria['price']; // free, paid
        }

        if (!empty($criteria['sort_by'])) {
            $params['sort_by'] = $criteria['sort_by']; // date, distance, best
        }

        $queryString = http_build_query($params);
        return $this->baseUrl . '/d/events/?' . $queryString;
    }

    /**
     * Parse search results from HTML
     */
    protected function parseSearchResults(string $html): array
    {
        $events = [];
        $crawler = new Crawler($html);

        try {
            $crawler->filter('[data-testid="event-card"], .event-card, .SearchResultPanelContentEventCard')->each(function (Crawler $node) use (&$events) {
                try {
                    $event = $this->parseEventCard($node);
                    if ($event) {
                        $events[] = $event;
                    }
                } catch (Exception $e) {
                    Log::debug("Failed to parse Eventbrite event card", ['error' => $e->getMessage()]);
                }
            });
        } catch (Exception $e) {
            Log::warning("Failed to parse Eventbrite search results", ['error' => $e->getMessage()]);
        }

        return $events;
    }

    /**
     * Parse individual event card
     */
    protected function parseEventCard(Crawler $node): ?array
    {
        try {
            $title = $this->extractText($node, '[data-testid="event-title"], .event-card__title, h3 a');
            $venue = $this->extractText($node, '[data-testid="event-venue"], .event-card__venue, .location-info__venue');
            $location = $this->extractText($node, '[data-testid="event-location"], .event-card__location, .location-info__address');
            $date = $this->extractText($node, '[data-testid="event-date"], .event-card__date, .event-date');
            $time = $this->extractText($node, '[data-testid="event-time"], .event-card__time, .event-time');
            $priceText = $this->extractText($node, '[data-testid="event-price"], .event-card__price, .event-pricing');
            $link = $this->extractAttribute($node, 'a', 'href');
            $imageUrl = $this->extractAttribute($node, '[data-testid="event-image"] img, .event-card__image img', 'src');

            if (empty($title)) {
                return null;
            }

            // Parse price
            $price = $this->parsePrice($priceText);
            $isFree = $this->isFreeEvent($priceText);

            // Parse date and time
            $eventDate = $this->parseDate($date);
            $eventTime = $this->parseTime($time);

            // Build full URL
            $fullUrl = $link ? $this->buildFullUrl($link) : null;

            // Build full image URL
            $fullImageUrl = $imageUrl ? $this->buildFullUrl($imageUrl) : null;

            return [
                'title' => trim($title),
                'venue' => trim($venue),
                'location' => trim($location ?: $venue),
                'date' => $eventDate,
                'time' => $eventTime,
                'price' => $price,
                'is_free' => $isFree,
                'currency' => $this->currency,
                'url' => $fullUrl,
                'image_url' => $fullImageUrl,
                'platform' => $this->platform,
                'description' => null,
                'category' => 'event',
                'availability' => 'available',
                'scraped_at' => now()->toISOString(),
            ];
        } catch (Exception $e) {
            Log::debug("Failed to parse Eventbrite event card", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if event is free
     */
    protected function isFreeEvent(string $priceText): bool
    {
        $freeIndicators = ['free', 'gratis', 'gratuit', 'kostenlos', 'gratuito'];
        $lowerPrice = strtolower($priceText);
        
        foreach ($freeIndicators as $indicator) {
            if (strpos($lowerPrice, $indicator) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Parse price from text
     */
    protected function parsePrice(string $priceText): ?float
    {
        if (empty($priceText) || $this->isFreeEvent($priceText)) {
            return 0.0;
        }

        // Extract numeric price
        if (preg_match('/(?:[\$£€¥]|USD|GBP|EUR|JPY)\s*(\d+(?:[\.,]\d{2})?)/', $priceText, $matches)) {
            $price = str_replace(',', '', $matches[1]);
            return (float)$price;
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
            // Try to parse time
            $time = Carbon::parse($timeText);
            return $time->format('H:i');
        } catch (Exception $e) {
            Log::debug("Failed to parse Eventbrite time", ['time' => $timeText, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Parse date from various formats
     */
    protected function parseDate(string $dateText): ?string
    {
        if (empty($dateText)) {
            return null;
        }

        try {
            // Try common date formats
            $date = Carbon::parse($dateText);
            return $date->format('Y-m-d');
        } catch (Exception $e) {
            Log::debug("Failed to parse Eventbrite date", ['date' => $dateText, 'error' => $e->getMessage()]);
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

    /**
     * Get events by category
     */
    public function getEventsByCategory(string $category, array $criteria = []): array
    {
        $criteria['category'] = $category;
        return $this->scrape($criteria);
    }

    /**
     * Get free events
     */
    public function getFreeEvents(array $criteria = []): array
    {
        $criteria['price'] = 'free';
        return $this->scrape($criteria);
    }

    /**
     * Get online events
     */
    public function getOnlineEvents(array $criteria = []): array
    {
        $criteria['format'] = 'online';
        return $this->scrape($criteria);
    }

    /**
     * Get events by location
     */
    public function getEventsByLocation(string $location, array $criteria = []): array
    {
        $criteria['location'] = $location;
        return $this->scrape($criteria);
    }

    /**
     * Get business events
     */
    public function getBusinessEvents(array $criteria = []): array
    {
        $criteria['category'] = 'business';
        return $this->scrape($criteria);
    }

    /**
     * Get music events
     */
    public function getMusicEvents(array $criteria = []): array
    {
        $criteria['category'] = 'music';
        return $this->scrape($criteria);
    }

    /**
     * Get food and drink events
     */
    public function getFoodDrinkEvents(array $criteria = []): array
    {
        $criteria['category'] = 'food-and-drink';
        return $this->scrape($criteria);
    }

    /**
     * Get arts and culture events
     */
    public function getArtsCultureEvents(array $criteria = []): array
    {
        $criteria['category'] = 'arts';
        return $this->scrape($criteria);
    }

    // Required abstract methods from BaseScraperPlugin

    protected function getTestUrl(): string
    {
        return $this->baseUrl . '/d/events/';
    }

    protected function getEventNameSelectors(): string
    {
        return '[data-testid="event-title"], .event-card__title, h3 a';
    }

    protected function getDateSelectors(): string
    {
        return '[data-testid="event-date"], .event-card__date, .event-date';
    }

    protected function getVenueSelectors(): string
    {
        return '[data-testid="event-venue"], .event-card__venue, .location-info__venue';
    }

    protected function getPriceSelectors(): string
    {
        return '[data-testid="event-price"], .event-card__price, .event-pricing';
    }

    protected function getAvailabilitySelectors(): string
    {
        return '.ticket-status, .sold-out, .available';
    }
}
