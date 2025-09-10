<?php declare(strict_types=1);

namespace App\Services;

use App\Events\TicketAvailabilityUpdated;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\User;
use App\Notifications\TicketStatusChanged;
use App\Services\Scraping\PluginBasedScraperManager;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function count;

class RealTimeMonitoringService
{
    protected $monitoringActive = FALSE;

    protected $monitoringInterval = 30; // seconds

    protected $watchedTickets = [];

    protected $alertThresholds = [];

    public function __construct(protected PluginBasedScraperManager $scraperManager)
    {
        $this->loadConfiguration();
    }

    /**
     * Start real-time monitoring
     */
    /**
     * StartMonitoring
     */
    public function startMonitoring(): void
    {
        if ($this->monitoringActive) {
            Log::info('Real-time monitoring is already active');

            return;
        }

        $this->monitoringActive = TRUE;
        Cache::put('monitoring.active', TRUE, 3600 * 24);

        Log::info('Started real-time ticket monitoring', [
            'interval'   => $this->monitoringInterval,
            'thresholds' => $this->alertThresholds,
        ]);

        $this->loadWatchedTickets();
        $this->runMonitoringLoop();
    }

    /**
     * Stop real-time monitoring
     */
    /**
     * StopMonitoring
     */
    public function stopMonitoring(): void
    {
        $this->monitoringActive = FALSE;
        Cache::put('monitoring.active', FALSE, 3600 * 24);

        Log::info('Stopped real-time ticket monitoring');
    }

    /**
     * Check if monitoring is active
     */
    /**
     * Check if  monitoring active
     */
    public function isMonitoringActive(): bool
    {
        return Cache::get('monitoring.active', FALSE);
    }

    /**
     * Add ticket to monitoring watch list
     */
    /**
     * AddToWatchList
     */
    public function addToWatchList(int $ticketId, array $criteria = []): void
    {
        $ticket = Ticket::find($ticketId);

        if (! $ticket) {
            throw new Exception("Ticket {$ticketId} not found");
        }

        $watchData = [
            'ticket_id'    => $ticketId,
            'criteria'     => $criteria,
            'added_at'     => now()->toISOString(),
            'last_checked' => NULL,
            'check_count'  => 0,
        ];

        $this->watchedTickets[$ticketId] = $watchData;

        // Store in cache for persistence
        Cache::put('monitoring.watched_tickets', $this->watchedTickets, 3600 * 24);

        Log::info("Added ticket {$ticketId} to monitoring watch list");
    }

    /**
     * Remove ticket from monitoring watch list
     */
    /**
     * RemoveFromWatchList
     */
    public function removeFromWatchList(int $ticketId): void
    {
        unset($this->watchedTickets[$ticketId]);

        Cache::put('monitoring.watched_tickets', $this->watchedTickets, 3600 * 24);

        Log::info("Removed ticket {$ticketId} from monitoring watch list");
    }

    /**
     * Get monitoring statistics
     */
    /**
     * Get  monitoring stats
     */
    public function getMonitoringStats(): array
    {
        $stats = Cache::get('monitoring.stats', []);

        return array_merge($stats, [
            'is_active'             => $this->isMonitoringActive(),
            'watched_tickets_count' => count($this->watchedTickets),
            'monitoring_interval'   => $this->monitoringInterval,
            'alert_thresholds'      => $this->alertThresholds,
        ]);
    }

    /**
     * Get real-time dashboard data
     */
    /**
     * Get  dashboard data
     */
    public function getDashboardData(): array
    {
        return [
            'monitoring_status' => [
                'active'          => $this->isMonitoringActive(),
                'watched_tickets' => count($this->watchedTickets),
                'last_cycle'      => Cache::get('monitoring.stats.last_cycle'),
                'next_cycle'      => $this->isMonitoringActive() ? now()->addSeconds($this->monitoringInterval) : NULL,
            ],
            'recent_alerts'       => $this->getRecentAlerts(),
            'performance_metrics' => $this->getPerformanceMetrics(),
            'health_status'       => $this->getHealthStatus(),
        ];
    }

    /**
     * Set monitoring interval
     */
    /**
     * Set  monitoring interval
     */
    public function setMonitoringInterval(int $seconds): void
    {
        $this->monitoringInterval = max(10, $seconds); // Minimum 10 seconds
        Log::info("Monitoring interval updated to {$this->monitoringInterval} seconds");
    }

    /**
     * Set alert thresholds
     */
    /**
     * Set  alert thresholds
     */
    public function setAlertThresholds(array $thresholds): void
    {
        $this->alertThresholds = array_merge($this->alertThresholds, $thresholds);
        Log::info('Alert thresholds updated', $this->alertThresholds);
    }

    /**
     * Load monitoring configuration
     */
    /**
     * LoadConfiguration
     */
    protected function loadConfiguration(): void
    {
        $this->monitoringInterval = config('monitoring.interval', 15); // Reduced interval for more frequent updates
        $this->alertThresholds = config('monitoring.alert_thresholds', [
            'price_change_percentage'        => 5, // More sensitive to price changes
            'availability_change'            => TRUE,
            'new_tickets'                    => TRUE,
            'alert_on_price_drop'            => TRUE, // Alert when price drops significantly
            'alert_on_availability_increase' => TRUE, // Alert when availability improves significantly
        ]);
    }

    /**
     * Load tickets to be watched
     */
    /**
     * LoadWatchedTickets
     */
    protected function loadWatchedTickets(): void
    {
        // Load tickets with active alerts
        $this->watchedTickets = TicketAlert::with(['ticket', 'user'])
            ->where('is_active', TRUE)
            ->get()
            ->groupBy('ticket_id')
            ->toArray();

        Log::info('Loaded watched tickets for monitoring', [
            'count' => count($this->watchedTickets),
        ]);
    }

    /**
     * Main monitoring loop
     */
    /**
     * RunMonitoringLoop
     */
    protected function runMonitoringLoop(): void
    {
        while ($this->monitoringActive) {
            try {
                $this->performMonitoringCycle();

                // Sleep for the configured interval
                sleep($this->monitoringInterval);
            } catch (Exception $e) {
                Log::error('Error in monitoring loop', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Continue monitoring despite errors
                sleep(5);
            }
        }
    }

    /**
     * Perform a single monitoring cycle
     */
    /**
     * PerformMonitoringCycle
     */
    protected function performMonitoringCycle(): void
    {
        $startTime = microtime(TRUE);
        $checkedTickets = 0;
        $updatedTickets = 0;
        $alerts = 0;

        foreach ($this->watchedTickets as $ticketId => $watchData) {
            try {
                $ticket = Ticket::find($ticketId);

                if (! $ticket) {
                    $this->removeFromWatchList($ticketId);

                    continue;
                }

                $changes = $this->checkTicketChanges($ticket, $watchData);

                if ($changes !== []) {
                    $this->processTicketChanges($ticket, $changes);
                    $updatedTickets++;
                    $alerts += count($changes);
                }

                $checkedTickets++;

                // Update watch data
                $this->watchedTickets[$ticketId]['last_checked'] = now()->toISOString();
                $this->watchedTickets[$ticketId]['check_count']++;
            } catch (Exception $e) {
                Log::error("Error checking ticket {$ticketId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $duration = (microtime(TRUE) - $startTime) * 1000;

        Log::info('Monitoring cycle completed', [
            'duration_ms'     => round($duration, 2),
            'checked_tickets' => $checkedTickets,
            'updated_tickets' => $updatedTickets,
            'alerts_sent'     => $alerts,
        ]);

        // Update monitoring statistics
        $this->updateMonitoringStats($checkedTickets, $updatedTickets, $alerts, $duration);
    }

    /**
     * Check for changes in a ticket
     */
    /**
     * CheckTicketChanges
     */
    protected function checkTicketChanges(Ticket $ticket, array $watchData): array
    {
        $changes = [];

        try {
            // Get fresh ticket data from scraping
            $freshData = $this->getFreshTicketData($ticket);

            if ($freshData === []) {
                return [];
            }

            // Check for availability changes
            $currentAvailability = $ticket->metadata['availability'] ?? 'unknown';
            $newAvailability = $freshData['availability'] ?? 'unknown';

            if ($currentAvailability !== $newAvailability) {
                $changes[] = [
                    'type'      => 'availability',
                    'old_value' => $currentAvailability,
                    'new_value' => $newAvailability,
                    'timestamp' => now()->toISOString(),
                ];
            }

            // Check for price changes
            $currentPrice = $ticket->metadata['price'] ?? NULL;
            $newPrice = $freshData['price'] ?? NULL;

            if ($currentPrice && $newPrice && $currentPrice !== $newPrice) {
                $priceChangePercent = abs(($newPrice - $currentPrice) / $currentPrice) * 100;

                if ($priceChangePercent > $this->alertThresholds['price_change_percentage']) {
                    $changes[] = [
                        'type'              => 'price',
                        'old_value'         => $currentPrice,
                        'new_value'         => $newPrice,
                        'change_percentage' => $priceChangePercent,
                        'timestamp'         => now()->toISOString(),
                    ];
                }
            }

            // Check for new ticket details or updates
            if (isset($freshData['updated_at']) && $freshData['updated_at'] > $ticket->updated_at) {
                $changes[] = [
                    'type'      => 'update',
                    'details'   => 'Ticket information has been updated',
                    'timestamp' => now()->toISOString(),
                ];
            }
        } catch (Exception $e) {
            Log::error("Error checking changes for ticket {$ticket->id}", [
                'error' => $e->getMessage(),
            ]);
        }

        return $changes;
    }

    /**
     * Get fresh ticket data from scraping
     */
    /**
     * Get  fresh ticket data
     */
    protected function getFreshTicketData(Ticket $ticket): array
    {
        $source = $ticket->metadata['source'] ?? NULL;
        $url = $ticket->metadata['url'] ?? NULL;

        if (! $source || ! $url) {
            return [];
        }

        try {
            // Use appropriate scraper plugin
            $plugin = $this->scraperManager->getPlugin($source);

            if (! $plugin) {
                return [];
            }

            $results = $plugin->scrape([
                'url'         => $url,
                'keyword'     => $ticket->title,
                'max_results' => 1,
            ]);

            return empty($results) ? [] : $results[0];
        } catch (Exception $e) {
            Log::debug("Failed to get fresh data for ticket {$ticket->id}", [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Process ticket changes and send notifications
     */
    /**
     * ProcessTicketChanges
     */
    protected function processTicketChanges(Ticket $ticket, array $changes): void
    {
        foreach ($changes as $change) {
            // Broadcast real-time update
            broadcast(new TicketAvailabilityUpdated(
                $ticket->id,
                $change['type'] . '_changed',
            ));

            // Send notifications to users with alerts for this ticket
            $this->sendChangeNotifications($ticket, $change);

            // Log the change
            Log::info('Ticket change detected', [
                'ticket_id'   => $ticket->id,
                'change_type' => $change['type'],
                'change'      => $change,
            ]);
        }

        // Update ticket metadata with changes
        $this->updateTicketMetadata($ticket, $changes);
    }

    /**
     * Send notifications for ticket changes
     */
    /**
     * SendChangeNotifications
     */
    protected function sendChangeNotifications(Ticket $ticket, array $change): void
    {
        $alerts = TicketAlert::where('ticket_id', $ticket->id)
            ->where('is_active', TRUE)
            ->with('user')
            ->get();

        foreach ($alerts as $alert) {
            if ($this->shouldSendAlert($alert, $change)) {
                try {
                    $alert->user->notify(new TicketStatusChanged($ticket, $change));

                    Log::info('Sent notification for ticket change', [
                        'ticket_id'   => $ticket->id,
                        'user_id'     => $alert->user->id,
                        'change_type' => $change['type'],
                    ]);
                } catch (Exception $e) {
                    Log::error('Failed to send notification', [
                        'ticket_id' => $ticket->id,
                        'user_id'   => $alert->user->id,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * Determine if alert should be sent based on user preferences
     */
    /**
     * ShouldSendAlert
     */
    protected function shouldSendAlert(TicketAlert $alert, array $change): bool
    {
        $preferences = $alert->preferences ?? [];
        $changeType = $change['type'];

        // Check if user wants notifications for this type of change
        return match ($changeType) {
            'availability' => $preferences['availability_changes'] ?? TRUE,
            'price'        => $preferences['price_changes'] ?? TRUE,
            default        => $preferences['all_changes'] ?? TRUE,
        };
    }

    /**
     * Update ticket metadata with latest changes
     */
    /**
     * UpdateTicketMetadata
     */
    protected function updateTicketMetadata(Ticket $ticket, array $changes): void
    {
        $metadata = $ticket->metadata ?? [];

        foreach ($changes as $change) {
            switch ($change['type']) {
                case 'availability':
                    $metadata['availability'] = $change['new_value'];
                    $metadata['availability_last_updated'] = $change['timestamp'];

                    break;
                case 'price':
                    $metadata['price'] = $change['new_value'];
                    $metadata['price_last_updated'] = $change['timestamp'];

                    break;
            }
        }

        $metadata['last_monitored'] = now()->toISOString();
        $metadata['monitoring_changes'] = ($metadata['monitoring_changes'] ?? []) + $changes;

        $ticket->update(['metadata' => $metadata]);
    }

    /**
     * Update monitoring statistics
     */
    /**
     * UpdateMonitoringStats
     */
    protected function updateMonitoringStats(int $checked, int $updated, int $alerts, float $duration): void
    {
        $stats = Cache::get('monitoring.stats', [
            'total_cycles'          => 0,
            'total_tickets_checked' => 0,
            'total_updates_found'   => 0,
            'total_alerts_sent'     => 0,
            'avg_cycle_duration'    => 0,
            'last_cycle'            => NULL,
        ]);

        $stats['total_cycles']++;
        $stats['total_tickets_checked'] += $checked;
        $stats['total_updates_found'] += $updated;
        $stats['total_alerts_sent'] += $alerts;
        $stats['avg_cycle_duration'] = (($stats['avg_cycle_duration'] * ($stats['total_cycles'] - 1)) + $duration) / $stats['total_cycles'];
        $stats['last_cycle'] = now()->toISOString();

        Cache::put('monitoring.stats', $stats, 3600 * 24 * 7);
    }

    /**
     * Get recent alerts
     */
    /**
     * Get  recent alerts
     */
    protected function getRecentAlerts(int $limit = 10): array
    {
        return Cache::get('monitoring.recent_alerts', []);
    }

    /**
     * Get performance metrics
     */
    /**
     * Get  performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        $stats = Cache::get('monitoring.stats', []);

        return [
            'avg_cycle_duration' => $stats['avg_cycle_duration'] ?? 0,
            'success_rate'       => $this->calculateSuccessRate(),
            'throughput'         => $this->calculateThroughput(),
            'error_rate'         => $this->calculateErrorRate(),
        ];
    }

    /**
     * Get health status
     */
    /**
     * Get  health status
     */
    protected function getHealthStatus(): array
    {
        $isActive = $this->isMonitoringActive();
        $errorRate = $this->calculateErrorRate();

        $status = 'healthy';
        if (! $isActive) {
            $status = 'inactive';
        } elseif ($errorRate > 20) {
            $status = 'critical';
        } elseif ($errorRate > 10) {
            $status = 'warning';
        }

        return [
            'status'            => $status,
            'is_active'         => $isActive,
            'error_rate'        => $errorRate,
            'last_health_check' => now()->toISOString(),
        ];
    }

    /**
     * Calculate success rate
     */
    /**
     * CalculateSuccessRate
     */
    protected function calculateSuccessRate(): float
    {
        // Implementation would calculate based on successful vs failed monitoring cycles
        return 95.0; // Placeholder
    }

    /**
     * Calculate throughput (tickets checked per minute)
     */
    /**
     * CalculateThroughput
     */
    protected function calculateThroughput(): float
    {
        $stats = Cache::get('monitoring.stats', []);
        $totalCycles = $stats['total_cycles'] ?? 0;
        $totalTickets = $stats['total_tickets_checked'] ?? 0;

        if ($totalCycles === 0) {
            return 0;
        }

        return ($totalTickets / $totalCycles) * (60 / $this->monitoringInterval);
    }

    /**
     * Calculate error rate
     */
    /**
     * CalculateErrorRate
     */
    protected function calculateErrorRate(): float
    {
        // Implementation would calculate based on monitoring errors
        return 2.5; // Placeholder
    }
}
