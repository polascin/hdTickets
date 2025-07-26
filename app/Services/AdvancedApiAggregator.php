<?php

namespace App\Services;

use App\Services\TicketApis\TicketmasterClient;
use App\Services\TicketApis\SeatGeekClient;
use App\Models\TicketSource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Exception;

class AdvancedApiAggregator
{
    protected $rateLimiter;
    protected $clients = [];

    public function __construct(ApiRateLimiter $rateLimiter)
    {
        $this->rateLimiter = $rateLimiter;
        $this->initializeClients();
    }

    /**
     * Advanced multi-source data aggregation
     */
    public function aggregateTicketData(array $searchCriteria): array
    {
        $results = [];
        $tasks = [];

        // Create async tasks for each API
        foreach ($this->clients as $platform => $client) {
            if ($this->rateLimiter->canMakeRequest($platform)) {
                $tasks[] = [
                    'platform' => $platform,
                    'client' => $client,
                    'criteria' => $this->adaptCriteriaForPlatform($searchCriteria, $platform)
                ];
            }
        }

        // Execute tasks with intelligent batching
        $batchSize = 3; // Concurrent API calls
        $batches = array_chunk($tasks, $batchSize);

        foreach ($batches as $batch) {
            $batchResults = $this->executeBatch($batch);
            $results = array_merge($results, $batchResults);
        }

        return $this->deduplicateAndEnrich($results);
    }

    /**
     * Execute batch of API calls
     */
    protected function executeBatch(array $batch): array
    {
        $results = [];
        $promises = [];

        foreach ($batch as $task) {
            try {
                $data = $task['client']->searchEvents($task['criteria']);
                $results[$task['platform']] = $this->processApiResponse($data, $task['platform']);
                
                $this->rateLimiter->recordRequest($task['platform']);
                
            } catch (Exception $e) {
                Log::warning("API call failed", [
                    'platform' => $task['platform'],
                    'error' => $e->getMessage()
                ]);
                $results[$task['platform']] = [];
            }
        }

        return $results;
    }

    /**
     * Intelligent data deduplication and enrichment
     */
    protected function deduplicateAndEnrich(array $results): array
    {
        $allEvents = [];
        
        // Flatten all results
        foreach ($results as $platform => $events) {
            foreach ($events as $event) {
                $event['source_platform'] = $platform;
                $event['confidence_score'] = $this->calculateConfidenceScore($event);
                $allEvents[] = $event;
            }
        }

        // Group similar events
        $groupedEvents = $this->groupSimilarEvents($allEvents);
        
        // Merge and enrich grouped events
        $enrichedEvents = [];
        foreach ($groupedEvents as $group) {
            $enrichedEvents[] = $this->mergeEventGroup($group);
        }

        return $enrichedEvents;
    }

    /**
     * Group similar events using fuzzy matching
     */
    protected function groupSimilarEvents(array $events): array
    {
        $groups = [];
        $processed = [];

        foreach ($events as $index => $event) {
            if (in_array($index, $processed)) {
                continue;
            }

            $group = [$event];
            $processed[] = $index;

            // Find similar events
            foreach ($events as $compareIndex => $compareEvent) {
                if (in_array($compareIndex, $processed)) {
                    continue;
                }

                if ($this->eventsAreSimilar($event, $compareEvent)) {
                    $group[] = $compareEvent;
                    $processed[] = $compareIndex;
                }
            }

            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Check if two events are similar
     */
    protected function eventsAreSimilar(array $event1, array $event2): bool
    {
        // Name similarity (using Levenshtein distance)
        $nameDistance = levenshtein(
            strtolower($event1['name'] ?? ''),
            strtolower($event2['name'] ?? '')
        );
        $maxNameLength = max(strlen($event1['name'] ?? ''), strlen($event2['name'] ?? ''));
        $nameSimilarity = 1 - ($nameDistance / max($maxNameLength, 1));

        // Date similarity (same day or within 1 day)
        $date1 = strtotime($event1['date'] ?? '');
        $date2 = strtotime($event2['date'] ?? '');
        $dateDiff = abs($date1 - $date2) / (24 * 60 * 60); // Days difference
        $dateSimilarity = $dateDiff <= 1 ? 1 : 0;

        // Venue similarity
        $venueSimilarity = 0;
        if (isset($event1['venue']) && isset($event2['venue'])) {
            $venueDistance = levenshtein(
                strtolower($event1['venue']),
                strtolower($event2['venue'])
            );
            $maxVenueLength = max(strlen($event1['venue']), strlen($event2['venue']));
            $venueSimilarity = 1 - ($venueDistance / max($maxVenueLength, 1));
        }

        // Combined similarity score
        $overallSimilarity = ($nameSimilarity * 0.5) + ($dateSimilarity * 0.3) + ($venueSimilarity * 0.2);

        return $overallSimilarity >= 0.7; // 70% similarity threshold
    }

    /**
     * Merge group of similar events
     */
    protected function mergeEventGroup(array $eventGroup): array
    {
        if (count($eventGroup) === 1) {
            return $eventGroup[0];
        }

        // Find the event with highest confidence score as base
        $baseEvent = array_reduce($eventGroup, function($carry, $event) {
            return ($event['confidence_score'] ?? 0) > ($carry['confidence_score'] ?? 0) ? $event : $carry;
        }, $eventGroup[0]);

        // Merge additional data from other events
        $mergedEvent = $baseEvent;
        $mergedEvent['sources'] = [];
        $mergedEvent['price_variations'] = [];

        foreach ($eventGroup as $event) {
            $mergedEvent['sources'][] = $event['source_platform'];

            // Collect price variations
            if (isset($event['price_min']) || isset($event['price_max'])) {
                $mergedEvent['price_variations'][] = [
                    'platform' => $event['source_platform'],
                    'min' => $event['price_min'] ?? null,
                    'max' => $event['price_max'] ?? null,
                ];
            }

            // Take the best available URL
            if (empty($mergedEvent['url']) && !empty($event['url'])) {
                $mergedEvent['url'] = $event['url'];
            }
        }

        // Calculate aggregated price range
        $allPrices = [];
        foreach ($mergedEvent['price_variations'] as $priceInfo) {
            if ($priceInfo['min']) $allPrices[] = $priceInfo['min'];
            if ($priceInfo['max']) $allPrices[] = $priceInfo['max'];
        }

        if ($allPrices) {
            $mergedEvent['price_min'] = min($allPrices);
            $mergedEvent['price_max'] = max($allPrices);
        }

        $mergedEvent['data_quality'] = 'merged';
        $mergedEvent['source_count'] = count($eventGroup);

        return $mergedEvent;
    }

    /**
     * Calculate confidence score for event data
     */
    protected function calculateConfidenceScore(array $event): float
    {
        $score = 0;

        // Base score for having essential fields
        if (!empty($event['name'])) $score += 20;
        if (!empty($event['date'])) $score += 20;
        if (!empty($event['venue'])) $score += 15;

        // Additional points for extra data
        if (!empty($event['url'])) $score += 10;
        if (!empty($event['price_min'])) $score += 10;
        if (!empty($event['description'])) $score += 5;

        // Platform reliability bonus
        $platformBonus = [
            'ticketmaster' => 20,
            'seatgeek' => 15,
            'eventbrite' => 10,
        ];
        $score += $platformBonus[$event['source_platform']] ?? 5;

        return min($score / 100, 1.0); // Normalize to 0-1
    }

    /**
     * Adapt search criteria for specific platform
     */
    protected function adaptCriteriaForPlatform(array $criteria, string $platform): array
    {
        $adapted = $criteria;

        switch ($platform) {
            case 'ticketmaster':
                if (isset($criteria['q'])) {
                    $adapted['keyword'] = $criteria['q'];
                    $adapted['apikey'] = config('ticket_apis.ticketmaster.api_key');
                }
                break;

            case 'seatgeek':
                // SeatGeek specific adaptations
                if (isset($criteria['date_from'])) {
                    $adapted['datetime_utc.gte'] = $criteria['date_from'] . 'T00:00:00Z';
                }
                break;
        }

        return $adapted;
    }

    /**
     * Initialize API clients
     */
    protected function initializeClients(): void
    {
        $configs = config('ticket_apis');

        if ($configs['ticketmaster']['enabled'] ?? false) {
            $this->clients['ticketmaster'] = new TicketmasterClient($configs['ticketmaster']);
        }

        if ($configs['seatgeek']['enabled'] ?? false) {
            $this->clients['seatgeek'] = new SeatGeekClient($configs['seatgeek']);
        }
    }

    protected function processApiResponse(array $data, string $platform): array
    {
        $client = $this->clients[$platform];
        $events = [];

        // Extract events based on platform response structure
        switch ($platform) {
            case 'ticketmaster':
                $eventData = $data['_embedded']['events'] ?? [];
                break;
            case 'seatgeek':
                $eventData = $data['events'] ?? [];
                break;
            default:
                $eventData = $data['events'] ?? $data['data'] ?? [];
        }

        foreach ($eventData as $event) {
            $events[] = $client->transformEventData($event);
        }

        return $events;
    }
}
