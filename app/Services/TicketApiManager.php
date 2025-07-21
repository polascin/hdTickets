<?php

namespace App\Services;

use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\SeatGeekClient;
use App\Services\TicketApis\StubHubClient;
use App\Services\TicketApis\ViagogoClient;
use App\Services\TicketApis\TickPickClient;
use App\Services\TicketApis\FunZoneClient;
use App\Models\TicketSource;
use Illuminate\Support\Facades\Log;
use Exception;

class TicketApiManager
{
    protected $clients = [];
    
    public function __construct()
    {
        $this->initializeClients();
    }

    /**
     * Initialize all available API clients
     */
    protected function initializeClients(): void
    {
        $apiConfigs = config('ticket_apis');

        // Initialize Ticketmaster client
        if ($apiConfigs['ticketmaster']['enabled'] ?? false) {
            $this->clients['ticketmaster'] = new TicketmasterClient($apiConfigs['ticketmaster']);
        }

        // Initialize SeatGeek client
        if ($apiConfigs['seatgeek']['enabled'] ?? false) {
            $this->clients['seatgeek'] = new SeatGeekClient($apiConfigs['seatgeek']);
        }

        // Initialize StubHub client
        if ($apiConfigs['stubhub']['enabled'] ?? false) {
            $this->clients['stubhub'] = new StubHubClient($apiConfigs['stubhub']);
        }

        // Initialize Viagogo client
        if ($apiConfigs['viagogo']['enabled'] ?? false) {
            $this->clients['viagogo'] = new ViagogoClient($apiConfigs['viagogo']);
        }

        // Initialize TickPick client
        if ($apiConfigs['tickpick']['enabled'] ?? false) {
            $this->clients['tickpick'] = new TickPickClient($apiConfigs['tickpick']);
        }

        // Initialize FunZone client
        if ($apiConfigs['funzone']['enabled'] ?? false) {
            $this->clients['funzone'] = new FunZoneClient($apiConfigs['funzone']);
        }
    }

    /**
     * Search events across all enabled APIs
     */
    public function searchEvents(array $criteria, array $platforms = []): array
    {
        $results = [];
        $clientsToUse = empty($platforms) ? $this->clients : array_intersect_key($this->clients, array_flip($platforms));

        foreach ($clientsToUse as $platform => $client) {
            try {
                $apiResults = $client->searchEvents($criteria);
                $results[$platform] = $this->processApiResults($apiResults, $platform, $client);
                
                Log::info("Successfully fetched events from {$platform}", [
                    'count' => count($results[$platform])
                ]);
            } catch (Exception $e) {
                Log::error("Failed to fetch events from {$platform}", [
                    'error' => $e->getMessage()
                ]);
                $results[$platform] = [];
            }
        }

        return $results;
    }

    /**
     * Process API results and optionally save to database
     */
    protected function processApiResults(array $apiResults, string $platform, $client): array
    {
        $processedResults = [];

        // Handle different API response structures
        $events = $this->extractEventsFromResponse($apiResults, $platform);

        foreach ($events as $eventData) {
            try {
                $transformedData = $client->transformEventData($eventData);
                $processedResults[] = $transformedData;

                // Optionally save to database
                if (config('ticket_apis.auto_save', false)) {
                    $this->saveEventToDatabase($transformedData, $platform);
                }
            } catch (Exception $e) {
                Log::warning("Failed to process event data", [
                    'platform' => $platform,
                    'event_id' => $eventData['id'] ?? 'unknown',
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $processedResults;
    }

    /**
     * Extract events array from different API response structures
     */
    protected function extractEventsFromResponse(array $response, string $platform): array
    {
        switch ($platform) {
            case 'ticketmaster':
                // Ticketmaster has events nested under _embedded
                return $response['_embedded']['events'] ?? [];
                
            case 'seatgeek':
                // SeatGeek returns events directly in events array
                return $response['events'] ?? [];
                
            case 'stubhub':
                // StubHub may have different response structures
                if (isset($response['events'])) {
                    return $response['events'];
                }
                // If it's a direct array of events or single event
                return is_array($response) && (isset($response[0]) || empty($response)) ? $response : [$response];
                
            case 'viagogo':
                // Viagogo may return results or items array
                if (isset($response['results'])) {
                    return $response['results'];
                }
                if (isset($response['items'])) {
                    return $response['items'];
                }
                return is_array($response) && (isset($response[0]) || empty($response)) ? $response : [$response];
                
            case 'tickpick':
                // TickPick may return data array or direct events
                if (isset($response['data'])) {
                    return $response['data'];
                }
                if (isset($response['events'])) {
                    return $response['events'];
                }
                return is_array($response) && (isset($response[0]) || empty($response)) ? $response : [$response];
                
            case 'funzone':
                // FunZone may have various response structures
                if (isset($response['listings'])) {
                    return $response['listings'];
                }
                if (isset($response['events'])) {
                    return $response['events'];
                }
                return is_array($response) && (isset($response[0]) || empty($response)) ? $response : [$response];
                
            default:
                // Generic fallback for unknown platforms
                return $response['events'] ?? $response['data'] ?? $response['results'] ?? $response ?? [];
        }
    }

    /**
     * Save event data to database
     */
    protected function saveEventToDatabase(array $eventData, string $platform): void
    {
        try {
            TicketSource::updateOrCreate([
                'platform' => $platform,
                'external_id' => $eventData['id'],
            ], [
                'name' => $eventData['name'],
                'event_name' => $eventData['name'],
                'event_date' => $eventData['date'] . ' ' . ($eventData['time'] ?? '00:00:00'),
                'venue' => $eventData['venue'],
                'price_min' => $eventData['price_min'] ?? null,
                'price_max' => $eventData['price_max'] ?? null,
                'availability_status' => $this->mapStatus($eventData['status'] ?? 'unknown', $platform),
                'url' => $eventData['url'] ?? '',
                'description' => $eventData['description'] ?? '',
                'last_checked' => now(),
                'is_active' => true,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to save event to database", [
                'event' => $eventData,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Map API status to our internal status with platform-specific handling
     */
    protected function mapStatus(string $apiStatus, string $platform = null): string
    {
        $normalizedStatus = strtolower($apiStatus);
        
        // Platform-specific status mappings
        $platformMappings = [
            'ticketmaster' => [
                'onsale' => TicketSource::STATUS_AVAILABLE,
                'offsale' => TicketSource::STATUS_NOT_ON_SALE,
                'soldout' => TicketSource::STATUS_SOLD_OUT,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'postponed' => TicketSource::STATUS_NOT_ON_SALE,
                'presale' => TicketSource::STATUS_AVAILABLE,
                'rescheduled' => TicketSource::STATUS_NOT_ON_SALE,
            ],
            'seatgeek' => [
                'normal' => TicketSource::STATUS_AVAILABLE,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'postponed' => TicketSource::STATUS_NOT_ON_SALE,
                'rescheduled' => TicketSource::STATUS_NOT_ON_SALE,
            ],
            'stubhub' => [
                'active' => TicketSource::STATUS_AVAILABLE,
                'inactive' => TicketSource::STATUS_NOT_ON_SALE,
                'sold out' => TicketSource::STATUS_SOLD_OUT,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'available' => TicketSource::STATUS_AVAILABLE,
                'unavailable' => TicketSource::STATUS_NOT_ON_SALE,
            ],
            'viagogo' => [
                'available' => TicketSource::STATUS_AVAILABLE,
                'sold out' => TicketSource::STATUS_SOLD_OUT,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'postponed' => TicketSource::STATUS_NOT_ON_SALE,
                'active' => TicketSource::STATUS_AVAILABLE,
                'inactive' => TicketSource::STATUS_NOT_ON_SALE,
            ],
            'tickpick' => [
                'available' => TicketSource::STATUS_AVAILABLE,
                'sold out' => TicketSource::STATUS_SOLD_OUT,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'postponed' => TicketSource::STATUS_NOT_ON_SALE,
                'on sale' => TicketSource::STATUS_AVAILABLE,
                'off sale' => TicketSource::STATUS_NOT_ON_SALE,
            ],
            'funzone' => [
                'available' => TicketSource::STATUS_AVAILABLE,
                'sold out' => TicketSource::STATUS_SOLD_OUT,
                'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
                'postponed' => TicketSource::STATUS_NOT_ON_SALE,
                'active' => TicketSource::STATUS_AVAILABLE,
                'inactive' => TicketSource::STATUS_NOT_ON_SALE,
                'listed' => TicketSource::STATUS_AVAILABLE,
                'unlisted' => TicketSource::STATUS_NOT_ON_SALE,
            ],
        ];
        
        // Use platform-specific mapping if platform is provided
        if ($platform && isset($platformMappings[$platform])) {
            if (isset($platformMappings[$platform][$normalizedStatus])) {
                return $platformMappings[$platform][$normalizedStatus];
            }
        }
        
        // Fallback to generic mapping
        $genericStatusMap = [
            'onsale' => TicketSource::STATUS_AVAILABLE,
            'offsale' => TicketSource::STATUS_NOT_ON_SALE,
            'soldout' => TicketSource::STATUS_SOLD_OUT,
            'sold out' => TicketSource::STATUS_SOLD_OUT,
            'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
            'postponed' => TicketSource::STATUS_NOT_ON_SALE,
            'available' => TicketSource::STATUS_AVAILABLE,
            'unavailable' => TicketSource::STATUS_NOT_ON_SALE,
            'active' => TicketSource::STATUS_AVAILABLE,
            'inactive' => TicketSource::STATUS_NOT_ON_SALE,
            'on sale' => TicketSource::STATUS_AVAILABLE,
            'off sale' => TicketSource::STATUS_NOT_ON_SALE,
        ];

        return $genericStatusMap[$normalizedStatus] ?? TicketSource::STATUS_UNKNOWN;
    }

    /**
     * Get specific event details
     */
    public function getEvent(string $platform, string $eventId): ?array
    {
        if (!isset($this->clients[$platform])) {
            throw new Exception("API client for platform '{$platform}' not available");
        }

        try {
            $eventData = $this->clients[$platform]->getEvent($eventId);
            return $this->clients[$platform]->transformEventData($eventData);
        } catch (Exception $e) {
            Log::error("Failed to get event details", [
                'platform' => $platform,
                'event_id' => $eventId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get available platforms
     */
    public function getAvailablePlatforms(): array
    {
        return array_keys($this->clients);
    }

    /**
     * Check if platform is available
     */
    public function isPlatformAvailable(string $platform): bool
    {
        return isset($this->clients[$platform]);
    }

    /**
     * Search events with automatic fallback
     */
    public function searchEventsWithFallback(array $criteria): array
    {
        $results = [];
        
        foreach ($this->clients as $platform => $client) {
            try {
                $apiResults = $client->searchEvents($criteria);
                $processedResults = $this->processApiResults($apiResults, $platform, $client);
                
                if (!empty($processedResults)) {
                    $results = array_merge($results, $processedResults);
                    break; // Use first successful result
                }
            } catch (Exception $e) {
                Log::warning("Platform {$platform} failed, trying next", [
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return $results;
    }
}
