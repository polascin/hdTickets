<?php

namespace App\Services;

use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\StubHubClient;
use App\Services\TicketApis\FunZoneClient;
use App\Services\TicketApis\ViagogoClient;
use App\Services\TicketApis\TickPickClient;
use App\Services\TicketApis\SeatGeekClient;
use App\Services\TicketApis\ManchesterUnitedClient;
use App\Services\TicketApis\EventbriteClient;
use App\Services\TicketApis\LiveNationClient;
use App\Services\TicketApis\AxsClient;
use App\Services\Normalization\DataNormalizationService;
use Illuminate\Support\Facades\Log;
use Exception;

class MultiPlatformManager
{
    protected array $platformClients = [];
    protected DataNormalizationService $normalizationService;

    public function __construct(DataNormalizationService $normalizationService)
    {
        $this->normalizationService = $normalizationService;
        $this->initializePlatformClients();
    }

    /**
     * Initialize platform clients with default configurations.
     */
    protected function initializePlatformClients(): void
    {
        $defaultConfig = [
            'enabled' => true,
            'timeout' => 30,
            'sandbox' => false,
            'api_key' => env('STUBHUB_API_KEY'),
            'app_token' => env('STUBHUB_APP_TOKEN'),
            'retry_attempts' => 3,
            'retry_delay' => 1,
        ];

        // Initialize clients for each platform
        $this->platformClients = [
            'ticketmaster' => new TicketmasterClient($defaultConfig),
            'stubhub' => new StubHubClient($defaultConfig),
            'funzone' => new FunZoneClient($defaultConfig),
            'viagogo' => new ViagogoClient($defaultConfig),
            'tickpick' => new TickPickClient($defaultConfig),
            'seatgeek' => new SeatGeekClient($defaultConfig),
            'manchester_united' => new ManchesterUnitedClient($defaultConfig),
            'eventbrite' => new EventbriteClient($defaultConfig),
            'livenation' => new LiveNationClient($defaultConfig),
            'axs' => new AxsClient($defaultConfig),
        ];
    }

    /**
     * Search events across all enabled platforms.
     */
    public function searchEventsAcrossPlatforms(string $keyword, string $location = '', int $maxResults = 50): array
    {
        $allResults = [];
        $platformResults = [];

        foreach ($this->platformClients as $platformName => $client) {
            try {
                Log::info("Searching events on platform: {$platformName}", [
                    'keyword' => $keyword,
                    'location' => $location,
                    'max_results' => $maxResults
                ]);

                $results = $this->searchOnPlatform($platformName, $client, $keyword, $location, $maxResults);
                
                if (!empty($results)) {
                    $platformResults[$platformName] = [
                        'count' => count($results),
                        'results' => $results
                    ];
                    
                    $allResults = array_merge($allResults, $results);
                    
                    Log::info("Found events on {$platformName}", ['count' => count($results)]);
                }
            } catch (Exception $e) {
                Log::error("Search failed on platform {$platformName}", [
                    'error' => $e->getMessage(),
                    'keyword' => $keyword,
                    'location' => $location
                ]);
                
                $platformResults[$platformName] = [
                    'count' => 0,
                    'error' => $e->getMessage(),
                    'results' => []
                ];
            }
        }

        return [
            'total_results' => count($allResults),
            'platforms' => $platformResults,
            'normalized_events' => $this->normalizationService->normalizeMultiple($allResults),
            'search_metadata' => [
                'keyword' => $keyword,
                'location' => $location,
                'max_results' => $maxResults,
                'searched_at' => now()->toISOString()
            ]
        ];
    }

    /**
     * Search events on a specific platform.
     */
    protected function searchOnPlatform(string $platformName, $client, string $keyword, string $location, int $maxResults): array
    {
        // Use the scraping method if available
        if (method_exists($client, 'scrapeSearchResults')) {
            return $client->scrapeSearchResults($keyword, $location, $maxResults);
        }
        
        // Fallback to API search if available
        if (method_exists($client, 'searchEvents')) {
            $criteria = [
                'q' => $keyword,
                'city' => $location,
                'per_page' => $maxResults
            ];
            return $client->searchEvents($criteria);
        }

        throw new Exception("Platform {$platformName} doesn't support search operations");
    }

    /**
     * Get detailed event information across platforms.
     */
    public function getEventDetailsAcrossPlatforms(array $eventUrls): array
    {
        $eventDetails = [];

        foreach ($eventUrls as $url) {
            $platform = $this->detectPlatformFromUrl($url);
            
            if (!$platform || !isset($this->platformClients[$platform])) {
                continue;
            }

            try {
                $client = $this->platformClients[$platform];
                
                if (method_exists($client, 'scrapeEventDetails')) {
                    $details = $client->scrapeEventDetails($url);
                    
                    if (!empty($details)) {
                        $normalizedEvent = $this->normalizationService->normalize($details);
                        if ($this->normalizationService->validate($normalizedEvent)) {
                            $eventDetails[] = $normalizedEvent;
                        }
                    }
                }
            } catch (Exception $e) {
                Log::error("Failed to get event details from {$platform}", [
                    'url' => $url,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $eventDetails;
    }

    /**
     * Detect platform from URL.
     */
    protected function detectPlatformFromUrl(string $url): ?string
    {
        $platformPatterns = [
            'ticketmaster' => '/ticketmaster\.com/',
            'stubhub' => '/stubhub\.com/',
            'funzone' => '/funzone\.sk/',
            'viagogo' => '/viagogo\.com/',
            'tickpick' => '/tickpick\.com/',
            'seatgeek' => '/seatgeek\.com/',
            'manchester_united' => '/manutd\.com/',
            'eventbrite' => '/eventbrite\.com/',
            'livenation' => '/livenation\.com/',
            'axs' => '/axs\.com/',
        ];

        foreach ($platformPatterns as $platform => $pattern) {
            if (preg_match($pattern, $url)) {
                return $platform;
            }
        }

        return null;
    }

    /**
     * Get available platforms and their status.
     */
    public function getPlatformsStatus(): array
    {
        $status = [];

        foreach ($this->platformClients as $platformName => $client) {
            $status[$platformName] = [
                'enabled' => true,
                'has_search' => method_exists($client, 'scrapeSearchResults') || method_exists($client, 'searchEvents'),
                'has_event_details' => method_exists($client, 'scrapeEventDetails') || method_exists($client, 'getEvent'),
                'base_url' => $client->getBaseUrl() ?? null,
                'platform_name' => $this->getPlatformDisplayName($platformName)
            ];
        }

        return $status;
    }

    /**
     * Get display name for platform.
     */
    protected function getPlatformDisplayName(string $platform): string
    {
        $displayNames = [
            'ticketmaster' => 'Ticketmaster',
            'stubhub' => 'StubHub',
            'funzone' => 'FunZone',
            'viagogo' => 'Viagogo',
            'tickpick' => 'TickPick',
            'seatgeek' => 'SeatGeek',
            'manchester_united' => 'Manchester United FC',
            'eventbrite' => 'Eventbrite',
            'livenation' => 'Live Nation',
            'axs' => 'AXS',
        ];

        return $displayNames[$platform] ?? ucfirst($platform);
    }

    /**
     * Deduplicate events across platforms.
     */
    public function deduplicateEvents(array $events, float $similarityThreshold = 0.8): array
    {
        $deduplicatedEvents = [];
        $duplicateGroups = [];

        foreach ($events as $i => $event1) {
            $isDuplicate = false;
            
            foreach ($deduplicatedEvents as $j => $event2) {
                $similarity = $this->normalizationService->compareEvents($event1, $event2);
                
                if ($similarity >= $similarityThreshold) {
                    $isDuplicate = true;
                    
                    // Group duplicates
                    if (!isset($duplicateGroups[$j])) {
                        $duplicateGroups[$j] = [$event2];
                    }
                    $duplicateGroups[$j][] = $event1;
                    break;
                }
            }
            
            if (!$isDuplicate) {
                $deduplicatedEvents[] = $event1;
            }
        }

        return [
            'deduplicated_events' => $deduplicatedEvents,
            'duplicate_groups' => $duplicateGroups,
            'original_count' => count($events),
            'deduplicated_count' => count($deduplicatedEvents),
            'duplicates_removed' => count($events) - count($deduplicatedEvents)
        ];
    }

    /**
     * Get aggregated statistics from all platforms.
     */
    public function getAggregatedStatistics(): array
    {
        $stats = [
            'platforms_count' => count($this->platformClients),
            'enabled_platforms' => [],
            'total_capabilities' => 0
        ];

        foreach ($this->platformClients as $platformName => $client) {
            $platformStats = [
                'name' => $this->getPlatformDisplayName($platformName),
                'capabilities' => []
            ];

            if (method_exists($client, 'scrapeSearchResults') || method_exists($client, 'searchEvents')) {
                $platformStats['capabilities'][] = 'search';
                $stats['total_capabilities']++;
            }

            if (method_exists($client, 'scrapeEventDetails') || method_exists($client, 'getEvent')) {
                $platformStats['capabilities'][] = 'event_details';
                $stats['total_capabilities']++;
            }

            if (method_exists($client, 'getEventTickets')) {
                $platformStats['capabilities'][] = 'ticket_details';
                $stats['total_capabilities']++;
            }

            $stats['enabled_platforms'][$platformName] = $platformStats;
        }

        return $stats;
    }

    /**
     * Perform health check on all platforms.
     */
    public function performHealthCheck(): array
    {
        $healthCheck = [
            'overall_status' => 'healthy',
            'platforms' => [],
            'healthy_count' => 0,
            'total_count' => count($this->platformClients)
        ];

        foreach ($this->platformClients as $platformName => $client) {
            $platformHealth = [
                'name' => $this->getPlatformDisplayName($platformName),
                'status' => 'unknown',
                'response_time' => null,
                'last_check' => now()->toISOString(),
                'errors' => []
            ];

            try {
                $startTime = microtime(true);
                
                // Simple connectivity test - try to make a basic request
                if (method_exists($client, 'scrapeSearchResults')) {
                    $testResults = $client->scrapeSearchResults('test', '', 1);
                    $platformHealth['status'] = 'healthy';
                } else {
                    $platformHealth['status'] = 'no_search_capability';
                }
                
                $platformHealth['response_time'] = round((microtime(true) - $startTime) * 1000, 2);
                $healthCheck['healthy_count']++;
                
            } catch (Exception $e) {
                $platformHealth['status'] = 'unhealthy';
                $platformHealth['errors'][] = $e->getMessage();
                $healthCheck['overall_status'] = 'partial';
            }

            $healthCheck['platforms'][$platformName] = $platformHealth;
        }

        if ($healthCheck['healthy_count'] === 0) {
            $healthCheck['overall_status'] = 'unhealthy';
        }

        return $healthCheck;
    }

    /**
     * Configure platform-specific settings.
     */
    public function configurePlatform(string $platformName, array $config): bool
    {
        if (!isset($this->platformClients[$platformName])) {
            return false;
        }

        try {
            // Apply configuration to client
            $clientClass = get_class($this->platformClients[$platformName]);
            $this->platformClients[$platformName] = new $clientClass($config);
            
            return true;
        } catch (Exception $e) {
            Log::error("Failed to configure platform {$platformName}", [
                'config' => $config,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
