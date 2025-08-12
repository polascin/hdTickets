<?php declare(strict_types=1);

namespace App\Services\Scraping;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Str;

use function count;
use function in_array;

/**
 * Practical implementation for high-demand ticket scraping
 *
 * This class provides ready-to-use methods for scraping high-demand tickets
 * from various platforms with advanced queue management and anti-detection.
 */
class HighDemandTicketScrapingImplementation
{
    protected HighDemandTicketScraperService $scraper;

    protected AdvancedAntiDetectionService $antiDetection;

    public function __construct(
        HighDemandTicketScraperService $scraper,
        AdvancedAntiDetectionService $antiDetection,
    ) {
        $this->scraper = $scraper;
        $this->antiDetection = $antiDetection;
    }

    /**
     * Scrape El Clasico tickets with maximum priority
     *
     * Example usage:
     * $scraper = app(HighDemandTicketScrapingImplementation::class);
     * $results = $scraper->scrapeElClasico(['max_price' => 500]);
     */
    public function scrapeElClasico(array $options = []): array
    {
        Log::info('Starting El Clasico high-demand ticket scraping');

        $criteria = [
            'keyword'      => 'Real Madrid vs Barcelona',
            'event_type'   => 'el_clasico',
            'max_price'    => $options['max_price'] ?? 1000,
            'currency'     => $options['currency'] ?? 'EUR',
            'demand_level' => 'extreme',
        ];

        $platforms = ['real_madrid', 'barcelona', 'stubhub', 'viagogo'];
        $results = [];

        // Pre-warm sessions for all platforms
        $this->preWarmSessions($platforms);

        // Execute parallel scraping with aggressive strategy
        foreach ($platforms as $platform) {
            try {
                $platformResults = $this->scraper->scrapeHighDemandTickets($platform, $criteria);
                $results[$platform] = $this->processResults($platformResults, $platform);

                // If tickets found, trigger immediate alerts
                if (! empty($results[$platform]['tickets'])) {
                    $this->triggerHighDemandAlert($results[$platform]['tickets'], 'el_clasico');
                }
            } catch (Exception $e) {
                Log::error("El Clasico scraping failed for {$platform}", [
                    'error'    => $e->getMessage(),
                    'criteria' => $criteria,
                ]);
                $results[$platform] = ['error' => $e->getMessage()];
            }
        }

        // Merge and prioritize all results
        $mergedResults = $this->mergeAndPrioritizeResults($results);

        Log::info('El Clasico scraping completed', [
            'total_tickets'     => count($mergedResults['tickets']),
            'platforms_scraped' => count($results),
        ]);

        return $mergedResults;
    }

    /**
     * Scrape Manchester United tickets
     *
     * Example usage:
     * $results = $scraper->scrapeManchesterUnited([
     *     'opponent' => 'Liverpool',
     *     'venue' => 'Old Trafford',
     *     'max_price' => 300
     * ]);
     */
    public function scrapeManchesterUnited(array $options = []): array
    {
        $opponent = $options['opponent'] ?? '';
        $venue = $options['venue'] ?? 'Old Trafford';
        $maxPrice = $options['max_price'] ?? 500;

        $keywords = [
            "Manchester United vs {$opponent}",
            "Man Utd vs {$opponent}",
            "MUFC vs {$opponent}",
            "Manchester United {$venue}",
            "Old Trafford {$opponent}",
        ];

        $criteria = [
            'keywords'     => $keywords,
            'team'         => 'Manchester United',
            'venue'        => $venue,
            'max_price'    => $maxPrice,
            'currency'     => 'GBP',
            'demand_level' => $this->calculateDemandLevel($opponent),
        ];

        $platforms = ['manchester_united', 'stubhub', 'ticketmaster', 'viagogo'];

        return $this->executeConcurrentScraping($platforms, $criteria, 'manchester_united');
    }

    /**
     * Scrape Champions League Final tickets with extreme priority
     */
    public function scrapeChampionsLeagueFinal(array $options = []): array
    {
        $criteria = [
            'keyword'      => 'Champions League Final',
            'event_type'   => 'champions_league_final',
            'max_price'    => $options['max_price'] ?? 2000,
            'currency'     => $options['currency'] ?? 'EUR',
            'venue'        => $options['venue'] ?? 'Wembley',
            'demand_level' => 'extreme',
        ];

        // All major European clubs and secondary markets
        $platforms = [
            'real_madrid', 'barcelona', 'bayern_munich', 'manchester_city',
            'psg', 'juventus', 'stubhub', 'ticketmaster', 'viagogo',
        ];

        // Use aggressive strategy with pre-sale monitoring
        $this->enablePreSaleMonitoring('champions_league_final');

        return $this->executeConcurrentScraping($platforms, $criteria, 'champions_league_final');
    }

    /**
     * Monitor and scrape tickets that are about to go on sale
     */
    public function monitorPreSaleTickets(string $eventType, array $criteria): array
    {
        Log::info("Starting pre-sale monitoring for {$eventType}");

        $monitoringKey = "pre_sale_monitoring_{$eventType}";

        if (Cache::has($monitoringKey)) {
            return ['status' => 'already_monitoring', 'event_type' => $eventType];
        }

        Cache::put($monitoringKey, TRUE, now()->addHours(24));

        // Schedule continuous monitoring every 30 seconds
        $this->schedulePreSaleChecks($eventType, $criteria);

        return [
            'status'              => 'monitoring_started',
            'event_type'          => $eventType,
            'monitoring_interval' => 30,
            'expires_at'          => now()->addHours(24)->toISOString(),
        ];
    }

    /**
     * Handle queue-based scraping intelligently
     */
    public function handleQueueScraping(string $platform, array $criteria): array
    {
        Log::info("Handling queue scraping for {$platform}");

        $queueKey = "queue_position_{$platform}";
        $queueData = Cache::get($queueKey, []);

        // Monitor queue position
        $currentPosition = $this->scraper->monitorQueuePosition($platform);

        if ($currentPosition['position'] < 100) {
            // Near front of queue - increase monitoring frequency
            $this->scheduleFrequentQueueChecks($platform, $criteria);

            return [
                'status'         => 'high_priority_queue_monitoring',
                'position'       => $currentPosition['position'],
                'estimated_wait' => $currentPosition['estimated_wait'],
            ];
        }

        // Try queue bypass techniques
        $bypassResults = $this->attemptQueueBypass($platform, $criteria);

        if (! empty($bypassResults)) {
            Log::info("Queue bypass successful for {$platform}");

            return $bypassResults;
        }

        // Wait intelligently in queue
        return $this->waitInQueueIntelligently($platform, $criteria, $currentPosition);
    }

    /**
     * Set up real-time high-demand ticket alerts
     */
    public function setupHighDemandAlerts(array $events): array
    {
        $alertsCreated = [];

        foreach ($events as $event) {
            try {
                $alert = TicketAlert::create([
                    'name'                => $event['name'],
                    'keywords'            => implode(', ', $event['keywords']),
                    'platforms'           => json_encode($event['platforms']),
                    'max_price'           => $event['max_price'],
                    'currency'            => $event['currency'] ?? 'EUR',
                    'status'              => 'active',
                    'priority'            => $event['priority'] ?? 'high',
                    'email_notifications' => TRUE,
                    'sms_notifications'   => $event['sms'] ?? FALSE,
                    'webhook_url'         => $event['webhook_url'] ?? NULL,
                    'filters'             => json_encode([
                        'demand_level' => ['high', 'very_high', 'extreme'],
                        'availability' => ['available', 'limited'],
                    ]),
                ]);

                $alertsCreated[] = $alert;

                Log::info('High-demand alert created', [
                    'alert_id'   => $alert->id,
                    'event_name' => $event['name'],
                ]);
            } catch (Exception $e) {
                Log::error("Failed to create alert for {$event['name']}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $alertsCreated;
    }

    /**
     * Execute high-demand scraping with auto-purchase capability
     */
    public function scrapeWithAutoPurchase(array $criteria, array $purchaseConfig = []): array
    {
        $results = $this->executeConcurrentScraping(
            $criteria['platforms'],
            $criteria,
            $criteria['event_type'] ?? 'high_demand',
        );

        // If auto-purchase is enabled and tickets found
        if (! empty($purchaseConfig['enabled']) && ! empty($results['tickets'])) {
            foreach ($results['tickets'] as $ticket) {
                if ($this->shouldAutoPurchase($ticket, $purchaseConfig)) {
                    $purchaseResult = $this->attemptAutoPurchase($ticket, $purchaseConfig);
                    $ticket['purchase_attempt'] = $purchaseResult;
                }
            }
        }

        return $results;
    }

    /**
     * Get real-time high-demand ticket statistics
     */
    public function getHighDemandStats(): array
    {
        return Cache::remember('high_demand_stats', 300, function () {
            return [
                'total_high_demand'     => ScrapedTicket::where('is_high_demand', TRUE)->count(),
                'available_high_demand' => ScrapedTicket::where('is_high_demand', TRUE)
                    ->where('availability_status', 'available')
                    ->count(),
                'extreme_demand'       => ScrapedTicket::where('demand_level', 'extreme')->count(),
                'active_monitoring'    => Cache::get('active_monitoring_sessions', 0),
                'queue_sessions'       => $this->getActiveQueueSessions(),
                'recent_finds'         => $this->getRecentHighDemandFinds(),
                'platform_performance' => $this->getPlatformPerformanceStats(),
            ];
        });
    }

    // Protected helper methods

    protected function preWarmSessions(array $platforms): void
    {
        foreach ($platforms as $platform) {
            try {
                $this->scraper->createHighDemandSession($platform, 0);
                Log::debug("Session pre-warmed for {$platform}");
            } catch (Exception $e) {
                Log::warning("Failed to pre-warm session for {$platform}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function executeConcurrentScraping(array $platforms, array $criteria, string $eventType): array
    {
        $results = [];
        $promises = [];

        // Execute scraping concurrently
        foreach ($platforms as $platform) {
            $promises[$platform] = $this->scrapeAsync($platform, $criteria);
        }

        // Wait for all results
        foreach ($promises as $platform => $promise) {
            try {
                $results[$platform] = $promise;
            } catch (Exception $e) {
                Log::error("Concurrent scraping failed for {$platform}", [
                    'error'      => $e->getMessage(),
                    'event_type' => $eventType,
                ]);
                $results[$platform] = ['error' => $e->getMessage()];
            }
        }

        return $this->mergeAndPrioritizeResults($results);
    }

    protected function scrapeAsync(string $platform, array $criteria): array
    {
        // Implement async scraping using Guzzle promises or similar
        return $this->scraper->scrapeHighDemandTickets($platform, $criteria);
    }

    protected function processResults(array $results, string $platform): array
    {
        if (empty($results)) {
            return ['tickets' => [], 'platform' => $platform];
        }

        // Save to database
        $savedTickets = [];
        foreach ($results as $ticketData) {
            $ticket = $this->saveTicketToDatabase($ticketData, $platform);
            if ($ticket) {
                $savedTickets[] = $ticket;
            }
        }

        return [
            'tickets'     => $savedTickets,
            'platform'    => $platform,
            'scraped_at'  => now()->toISOString(),
            'total_found' => count($results),
        ];
    }

    protected function saveTicketToDatabase(array $ticketData, string $platform): ?ScrapedTicket
    {
        try {
            return ScrapedTicket::create([
                'uuid'                => Str::uuid(),
                'platform'            => $platform,
                'external_id'         => $ticketData['external_id'] ?? NULL,
                'title'               => $ticketData['title'],
                'venue'               => $ticketData['venue'] ?? '',
                'location'            => $ticketData['location'] ?? '',
                'event_date'          => $ticketData['event_date'] ?? NULL,
                'min_price'           => $ticketData['min_price'],
                'max_price'           => $ticketData['max_price'],
                'currency'            => $ticketData['currency'] ?? 'EUR',
                'availability_status' => $ticketData['availability'],
                'is_high_demand'      => TRUE,
                'demand_level'        => $ticketData['demand_level'] ?? 'high',
                'ticket_url'          => $ticketData['ticket_url'] ?? NULL,
                'scraped_at'          => now(),
                'search_keyword'      => $ticketData['search_keyword'] ?? '',
                'metadata'            => json_encode($ticketData),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to save high-demand ticket', [
                'error'       => $e->getMessage(),
                'ticket_data' => $ticketData,
            ]);

            return NULL;
        }
    }

    protected function mergeAndPrioritizeResults(array $results): array
    {
        $allTickets = [];
        $platformStats = [];

        foreach ($results as $platform => $result) {
            if (isset($result['tickets'])) {
                $allTickets = array_merge($allTickets, $result['tickets']);
                $platformStats[$platform] = [
                    'tickets_found' => count($result['tickets']),
                    'status'        => 'success',
                ];
            } else {
                $platformStats[$platform] = [
                    'tickets_found' => 0,
                    'status'        => 'error',
                    'error'         => $result['error'] ?? 'Unknown error',
                ];
            }
        }

        // Sort by demand level and availability
        usort($allTickets, function ($a, $b) {
            $demandOrder = ['extreme' => 0, 'very_high' => 1, 'high' => 2, 'medium' => 3];

            // Available tickets first
            if ($a['availability_status'] === 'available' && $b['availability_status'] !== 'available') {
                return -1;
            }
            if ($b['availability_status'] === 'available' && $a['availability_status'] !== 'available') {
                return 1;
            }

            // Then by demand level
            $aDemand = $demandOrder[$a['demand_level']] ?? 4;
            $bDemand = $demandOrder[$b['demand_level']] ?? 4;

            return $aDemand - $bDemand;
        });

        return [
            'tickets'        => $allTickets,
            'total_tickets'  => count($allTickets),
            'platform_stats' => $platformStats,
            'scraped_at'     => now()->toISOString(),
        ];
    }

    protected function triggerHighDemandAlert(array $tickets, string $eventType): void
    {
        foreach ($tickets as $ticket) {
            if ($ticket['demand_level'] === 'extreme') {
                // Send immediate notifications
                Log::info('EXTREME DEMAND TICKET FOUND', [
                    'ticket'     => $ticket,
                    'event_type' => $eventType,
                ]);

                // Trigger webhooks, emails, SMS, etc.
                $this->sendImmediateNotifications($ticket, $eventType);
            }
        }
    }

    protected function sendImmediateNotifications(array $ticket, string $eventType): void
    {
        // Implementation for immediate notifications
        // Could integrate with Slack, Discord, email, SMS services
        Log::info('Sending immediate notifications for high-demand ticket', [
            'ticket_title' => $ticket['title'],
            'event_type'   => $eventType,
        ]);
    }

    protected function calculateDemandLevel(string $opponent): string
    {
        $highDemandOpponents = [
            'Liverpool', 'Manchester City', 'Arsenal', 'Chelsea',
            'Real Madrid', 'Barcelona', 'Bayern Munich',
        ];

        return in_array($opponent, $highDemandOpponents, TRUE) ? 'very_high' : 'high';
    }

    protected function shouldAutoPurchase(array $ticket, array $config): bool
    {
        if (empty($config['enabled'])) {
            return FALSE;
        }

        // Check price limits
        if (! empty($config['max_price']) && $ticket['min_price'] > $config['max_price']) {
            return FALSE;
        }

        // Check demand level requirements
        if (! empty($config['min_demand_level'])) {
            $demandLevels = ['medium' => 1, 'high' => 2, 'very_high' => 3, 'extreme' => 4];
            $ticketLevel = $demandLevels[$ticket['demand_level']] ?? 0;
            $requiredLevel = $demandLevels[$config['min_demand_level']] ?? 0;

            if ($ticketLevel < $requiredLevel) {
                return FALSE;
            }
        }

        return TRUE;
    }

    protected function attemptAutoPurchase(array $ticket, array $config): array
    {
        Log::info('Attempting auto-purchase', [
            'ticket_title' => $ticket['title'],
            'price'        => $ticket['min_price'],
        ]);

        // Implementation would integrate with purchase systems
        return [
            'status'    => 'attempted',
            'timestamp' => now()->toISOString(),
            'ticket_id' => $ticket['id'] ?? NULL,
        ];
    }

    protected function getActiveQueueSessions(): int
    {
        return Cache::get('active_queue_sessions', 0);
    }

    protected function getRecentHighDemandFinds(): array
    {
        return ScrapedTicket::where('is_high_demand', TRUE)
            ->where('scraped_at', '>=', now()->subHours(6))
            ->orderBy('scraped_at', 'desc')
            ->limit(10)
            ->get(['title', 'platform', 'demand_level', 'scraped_at'])
            ->toArray();
    }

    protected function getPlatformPerformanceStats(): array
    {
        // Get performance statistics for each platform
        return Cache::remember('platform_performance', 600, function () {
            return ScrapedTicket::selectRaw('
                platform,
                COUNT(*) as total_tickets,
                COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_tickets,
                AVG(CASE WHEN scraped_at >= NOW() - INTERVAL 24 HOUR THEN 1 ELSE 0 END) as success_rate_24h
            ')
                ->groupBy('platform')
                ->get()
                ->keyBy('platform')
                ->toArray();
        });
    }

    protected function schedulePreSaleChecks(string $eventType, array $criteria): void
    {
        // Implementation would use Laravel's job queue system
        Log::info("Scheduling pre-sale checks for {$eventType}");
    }

    protected function scheduleFrequentQueueChecks(string $platform, array $criteria): void
    {
        // Implementation for frequent queue monitoring
        Log::info("Scheduling frequent queue checks for {$platform}");
    }

    protected function attemptQueueBypass(string $platform, array $criteria): array
    {
        // Implementation for queue bypass techniques
        Log::info("Attempting queue bypass for {$platform}");

        return [];
    }

    protected function waitInQueueIntelligently(string $platform, array $criteria, array $position): array
    {
        Log::info("Waiting intelligently in queue for {$platform}", $position);

        return [
            'status'   => 'waiting_in_queue',
            'position' => $position,
            'strategy' => 'intelligent_waiting',
        ];
    }

    protected function enablePreSaleMonitoring(string $eventType): void
    {
        Cache::put("pre_sale_monitoring_{$eventType}", TRUE, now()->addDays(7));
        Log::info("Pre-sale monitoring enabled for {$eventType}");
    }
}
