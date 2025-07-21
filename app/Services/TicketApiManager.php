<?php

namespace App\Services;

use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\SeatGeekClient;
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

        // Add more clients as needed...
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
                return $response['_embedded']['events'] ?? [];
            case 'seatgeek':
                return $response['events'] ?? [];
            default:
                return $response['events'] ?? $response['data'] ?? [];
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
                'availability_status' => $this->mapStatus($eventData['status'] ?? 'unknown'),
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
     * Map API status to our internal status
     */
    protected function mapStatus(string $apiStatus): string
    {
        $statusMap = [
            'onsale' => TicketSource::STATUS_AVAILABLE,
            'offsale' => TicketSource::STATUS_NOT_ON_SALE,
            'soldout' => TicketSource::STATUS_SOLD_OUT,
            'cancelled' => TicketSource::STATUS_NOT_ON_SALE,
            'postponed' => TicketSource::STATUS_NOT_ON_SALE,
        ];

        return $statusMap[strtolower($apiStatus)] ?? TicketSource::STATUS_UNKNOWN;
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
