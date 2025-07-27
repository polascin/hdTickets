<?php

namespace App\Services\Scraping\Plugins;

use App\Services\Scraping\BaseScraperPlugin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TicketmasterPlugin extends BaseScraperPlugin
{
    protected function initializePlugin(): void
    {
        $this->pluginName = 'Ticketmaster';
        $this->platform = 'ticketmaster';
        $this->description = 'Official Ticketmaster API scraper for sports events and tickets';
        $this->baseUrl = 'https://app.ticketmaster.com/discovery/v2/';
        $this->venue = 'Various';
        $this->currency = 'USD';
        $this->rateLimitSeconds = 1;
        $this->version = '2.0.0';
    }

    protected function getCapabilities(): array
    {
        return [
            'sports_events',
            'music_events',
            'theater_events',
            'family_events',
            'api_based',
            'real_time_availability',
            'pricing_tiers',
            'venue_mapping',
            'multi_country_support'
        ];
    }

    protected function getSupportedCriteria(): array
    {
        return [
            'keyword',
            'sport',
            'city',
            'venue',
            'date_from',
            'date_to',
            'price_range',
            'classification',
            'country_code',
            'dma_id',
            'sort'
        ];
    }

    protected function getTestUrl(): string
    {
        return $this->baseUrl . 'events.json?apikey=' . config('scraping.plugins.ticketmaster.api_key');
    }

    protected function buildSearchUrl(array $criteria): string
    {
        $apiKey = config('scraping.plugins.ticketmaster.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Ticketmaster API key not configured');
        }

        $params = [
            'apikey' => $apiKey,
            'classificationName' => 'Sports',
            'size' => min($criteria['limit'] ?? 50, 200), // Ticketmaster max is 200
            'sort' => $criteria['sort'] ?? 'date,asc'
        ];

        // Add keyword search
        if (!empty($criteria['keyword'])) {
            $params['keyword'] = $criteria['keyword'];
        }

        // Add city filter
        if (!empty($criteria['city'])) {
            $params['city'] = $criteria['city'];
        }

        // Add venue filter
        if (!empty($criteria['venue'])) {
            $params['venue'] = $criteria['venue'];
        }

        // Add date range
        if (!empty($criteria['date_from'])) {
            $params['startDateTime'] = Carbon::parse($criteria['date_from'])->toISOString();
        } else {
            $params['startDateTime'] = now()->toISOString(); // Default to today
        }

        if (!empty($criteria['date_to'])) {
            $params['endDateTime'] = Carbon::parse($criteria['date_to'])->toISOString();
        }

        // Add country code
        if (!empty($criteria['country_code'])) {
            $params['countryCode'] = strtoupper($criteria['country_code']);
        } else {
            $params['countryCode'] = 'US'; // Default to US
        }

        // Add specific sport classification
        if (!empty($criteria['sport'])) {
            $params['subGenre'] = $criteria['sport'];
        }

        return $this->baseUrl . 'events.json?' . http_build_query($params);
    }

    protected function parseSearchResults(string $response): array
    {
        $data = json_decode($response, true);
        
        if (!isset($data['_embedded']['events'])) {
            Log::info('No events found in Ticketmaster response');
            return [];
        }

        $events = [];
        
        foreach ($data['_embedded']['events'] as $event) {
            $parsedEvent = $this->parseTicketmasterEvent($event);
            if ($parsedEvent) {
                $events[] = $parsedEvent;
            }
        }

        return $events;
    }

    private function parseTicketmasterEvent(array $event): ?array
    {
        try {
            // Extract basic event information
            $eventName = $event['name'] ?? '';
            if (empty($eventName)) {
                return null;
            }

            // Extract venue information
            $venue = 'Various';
            if (isset($event['_embedded']['venues'][0])) {
                $venueData = $event['_embedded']['venues'][0];
                $venue = $venueData['name'] ?? 'Various';
            }

            // Extract date information
            $date = null;
            if (isset($event['dates']['start']['dateTime'])) {
                $date = Carbon::parse($event['dates']['start']['dateTime'])->toISOString();
            } elseif (isset($event['dates']['start']['localDate'])) {
                $date = Carbon::parse($event['dates']['start']['localDate'])->toISOString();
            }

            // Extract price information
            $priceInfo = $this->extractPriceInfo($event);

            // Extract URL
            $url = $event['url'] ?? '';

            // Extract classification information
            $category = $this->extractCategory($event);
            $subcategory = $this->extractSubcategory($event);

            // Determine availability status
            $availabilityStatus = $this->determineAvailabilityStatus($event);

            return [
                'event_name' => trim($eventName),
                'venue' => $venue,
                'date' => $date,
                'price_min' => $priceInfo['min'],
                'price_max' => $priceInfo['max'],
                'currency' => $this->currency,
                'url' => $url,
                'platform' => $this->platform,
                'availability_status' => $availabilityStatus,
                'category' => $category,
                'subcategory' => $subcategory,
                'external_id' => $event['id'] ?? null,
                'event_type' => $event['type'] ?? 'event',
                'onsale_start' => isset($event['sales']['public']['startDateTime']) 
                    ? Carbon::parse($event['sales']['public']['startDateTime'])->toISOString() 
                    : null,
                'onsale_end' => isset($event['sales']['public']['endDateTime']) 
                    ? Carbon::parse($event['sales']['public']['endDateTime'])->toISOString() 
                    : null,
                'scraped_at' => now()->toISOString(),
            ];

        } catch (\Exception $e) {
            Log::warning("Failed to parse Ticketmaster event", [
                'error' => $e->getMessage(),
                'event_id' => $event['id'] ?? 'unknown'
            ]);
            return null;
        }
    }

    private function extractPriceInfo(array $event): array
    {
        $priceInfo = ['min' => null, 'max' => null];

        if (isset($event['priceRanges'][0])) {
            $priceRange = $event['priceRanges'][0];
            $priceInfo['min'] = $priceRange['min'] ?? null;
            $priceInfo['max'] = $priceRange['max'] ?? null;
            
            // Update currency if different
            if (isset($priceRange['currency'])) {
                $this->currency = $priceRange['currency'];
            }
        }

        return $priceInfo;
    }

    private function extractCategory(array $event): string
    {
        if (isset($event['classifications'][0]['segment']['name'])) {
            return $event['classifications'][0]['segment']['name'];
        }
        
        return 'Entertainment';
    }

    private function extractSubcategory(array $event): string
    {
        if (isset($event['classifications'][0]['genre']['name'])) {
            return $event['classifications'][0]['genre']['name'];
        }
        
        if (isset($event['classifications'][0]['subGenre']['name'])) {
            return $event['classifications'][0]['subGenre']['name'];
        }
        
        return 'General';
    }

    private function determineAvailabilityStatus(array $event): string
    {
        // Check sales status
        if (isset($event['dates']['status']['code'])) {
            $statusCode = strtolower($event['dates']['status']['code']);
            
            switch ($statusCode) {
                case 'onsale':
                    return 'available';
                case 'offsale':
                case 'cancelled':
                case 'postponed':
                    return 'unavailable';
                case 'presale':
                    return 'presale';
                default:
                    return 'unknown';
            }
        }

        // Check if currently on sale
        $now = now();
        if (isset($event['sales']['public'])) {
            $saleStart = isset($event['sales']['public']['startDateTime']) 
                ? Carbon::parse($event['sales']['public']['startDateTime']) 
                : null;
            $saleEnd = isset($event['sales']['public']['endDateTime']) 
                ? Carbon::parse($event['sales']['public']['endDateTime']) 
                : null;

            if ($saleStart && $now->lt($saleStart)) {
                return 'not_on_sale';
            }

            if ($saleEnd && $now->gt($saleEnd)) {
                return 'sale_ended';
            }

            return 'available';
        }

        return 'unknown';
    }

    protected function getEventNameSelectors(): string
    {
        return 'name'; // JSON field, not CSS selector
    }

    protected function getDateSelectors(): string
    {
        return 'dates.start.dateTime,dates.start.localDate';
    }

    protected function getVenueSelectors(): string
    {
        return '_embedded.venues.0.name';
    }

    protected function getPriceSelectors(): string
    {
        return 'priceRanges.0';
    }

    protected function getAvailabilitySelectors(): string
    {
        return 'dates.status.code';
    }

    /**
     * Enhanced scraping method that handles API-specific logic
     */
    public function scrape(array $criteria): array
    {
        if (!$this->enabled) {
            throw new \Exception("{$this->pluginName} plugin is disabled");
        }

        Log::info("Starting {$this->pluginName} API scraping", $criteria);
        
        try {
            // Apply rate limiting
            $this->applyRateLimit($this->platform);
            
            // Build API URL
            $apiUrl = $this->buildSearchUrl($criteria);
            
            // Make API request
            $response = $this->makeApiRequest($apiUrl);
            
            // Parse results
            $events = $this->parseSearchResults($response);
            
            // Filter and format results
            $filteredEvents = $this->filterResults($events, $criteria);
            
            Log::info("{$this->pluginName} API scraping completed", [
                'url' => $apiUrl,
                'results_found' => count($filteredEvents)
            ]);
            
            return $filteredEvents;
            
        } catch (\Exception $e) {
            Log::error("{$this->pluginName} API scraping failed", [
                'criteria' => $criteria,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Make API request with proper headers and error handling
     */
    private function makeApiRequest(string $url): string
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => $this->getRandomUserAgent(),
            ])
            ->get($url);

        if (!$response->successful()) {
            throw new \Exception("Ticketmaster API request failed: HTTP {$response->status()}");
        }

        return $response->body();
    }

    /**
     * Get specific sports events for better categorization
     */
    public function getSportsEvents(array $criteria = []): array
    {
        $criteria['classification'] = 'Sports';
        return $this->scrape($criteria);
    }

    /**
     * Get events by specific sport
     */
    public function getEventsBySport(string $sport, array $criteria = []): array
    {
        $criteria['sport'] = $sport;
        $criteria['classification'] = 'Sports';
        return $this->scrape($criteria);
    }

    /**
     * Get events by venue
     */
    public function getEventsByVenue(string $venue, array $criteria = []): array
    {
        $criteria['venue'] = $venue;
        return $this->scrape($criteria);
    }

    /**
     * Get events by city
     */
    public function getEventsByCity(string $city, array $criteria = []): array
    {
        $criteria['city'] = $city;
        return $this->scrape($criteria);
    }
}
