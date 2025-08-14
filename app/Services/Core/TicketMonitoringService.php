<?php declare(strict_types=1);

namespace App\Services\Core;

use App\Services\Interfaces\TicketMonitoringInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Redis;
use InvalidArgumentException;

use function array_slice;
use function count;

/**
 * Unified Ticket Monitoring Service
 *
 * Consolidates real-time monitoring and availability tracking
 * for sport events entry tickets across all platforms.
 */
class TicketMonitoringService extends BaseService implements TicketMonitoringInterface
{
    private const MONITORING_PREFIX = 'ticket_monitoring:';

    private const ALERT_PREFIX = 'ticket_alerts:';

    private const AVAILABILITY_PREFIX = 'ticket_availability:';

    private array $monitoredTickets = [];

    private array $alertRules = [];

    /**
     * Start monitoring a ticket for availability and price changes
     */
    /**
     * StartMonitoring
     */
    public function startMonitoring(int $ticketId, array $criteria = []): bool
    {
        $this->ensureInitialized();

        try {
            $monitoringData = [
                'ticket_id'            => $ticketId,
                'criteria'             => $criteria,
                'started_at'           => Carbon::now()->toISOString(),
                'status'               => 'active',
                'last_check'           => NULL,
                'alert_count'          => 0,
                'availability_history' => [],
                'price_history'        => [],
            ];

            $monitoringKey = self::MONITORING_PREFIX . $ticketId;
            Redis::hmset($monitoringKey, $monitoringData);
            Redis::expire($monitoringKey, 86400 * 30); // 30 days

            // Add to monitoring queue
            Redis::sadd(self::MONITORING_PREFIX . 'active_tickets', $ticketId);

            $this->monitoredTickets[$ticketId] = $monitoringData;

            $this->logOperation('startMonitoring', ['ticket_id' => $ticketId, 'criteria' => $criteria]);

            $this->getDependency('analyticsService')->trackEvent('ticket_monitoring_started', [
                'ticket_id' => $ticketId,
                'criteria'  => $criteria,
            ]);

            return TRUE;
        } catch (Exception $e) {
            $this->handleError($e, 'startMonitoring', ['ticket_id' => $ticketId]);

            return FALSE;
        }
    }

    /**
     * Stop monitoring a ticket
     */
    /**
     * StopMonitoring
     */
    public function stopMonitoring(int $ticketId): bool
    {
        $this->ensureInitialized();

        try {
            $monitoringKey = self::MONITORING_PREFIX . $ticketId;

            // Update status to stopped
            Redis::hset($monitoringKey, 'status', 'stopped');
            Redis::hset($monitoringKey, 'stopped_at', Carbon::now()->toISOString());

            // Remove from active monitoring
            Redis::srem(self::MONITORING_PREFIX . 'active_tickets', $ticketId);

            unset($this->monitoredTickets[$ticketId]);

            $this->logOperation('stopMonitoring', ['ticket_id' => $ticketId]);

            $this->getDependency('analyticsService')->trackEvent('ticket_monitoring_stopped', [
                'ticket_id' => $ticketId,
            ]);

            return TRUE;
        } catch (Exception $e) {
            $this->handleError($e, 'stopMonitoring', ['ticket_id' => $ticketId]);

            return FALSE;
        }
    }

    /**
     * Check availability for all monitored tickets
     */
    /**
     * CheckAllTickets
     */
    public function checkAllTickets(): array
    {
        $this->ensureInitialized();

        $results = [];
        $activeTickets = Redis::smembers(self::MONITORING_PREFIX . 'active_tickets');

        foreach ($activeTickets as $ticketId) {
            try {
                $result = $this->checkTicketAvailability((int) $ticketId);
                $results[$ticketId] = $result;
            } catch (Exception $e) {
                $this->handleError($e, 'checkAllTickets', ['ticket_id' => $ticketId]);
                $results[$ticketId] = [
                    'status' => 'error',
                    'error'  => $e->getMessage(),
                ];
            }
        }

        $this->getDependency('analyticsService')->trackEvent('monitoring_check_completed', [
            'total_tickets'     => count($activeTickets),
            'successful_checks' => count(array_filter($results, fn ($r) => $r['status'] !== 'error')),
        ]);

        return $results;
    }

    /**
     * Check availability for specific ticket
     */
    /**
     * CheckTicketAvailability
     */
    public function checkTicketAvailability(int $ticketId): array
    {
        $this->ensureInitialized();

        $monitoringKey = self::MONITORING_PREFIX . $ticketId;
        $monitoringData = Redis::hgetall($monitoringKey);

        if (empty($monitoringData)) {
            throw new InvalidArgumentException("Ticket {$ticketId} is not being monitored");
        }

        $criteria = json_decode($monitoringData['criteria'] ?? '[]', TRUE);

        // Use scraping service to check current availability
        $scrapingResults = $this->getDependency('scrapingService')->scrapeAllPlatforms($criteria);

        $availabilityData = $this->processAvailabilityResults($ticketId, $scrapingResults);

        // Update monitoring data
        Redis::hset($monitoringKey, 'last_check', Carbon::now()->toISOString());
        $this->updateAvailabilityHistory($ticketId, $availabilityData);

        // Check for alerts
        $this->checkAlertConditions($ticketId, $availabilityData);

        return $availabilityData;
    }

    /**
     * Set alert rule for ticket monitoring
     */
    /**
     * Set  alert rule
     */
    public function setAlertRule(int $ticketId, string $condition, mixed $value, array $notificationChannels = ['email']): bool
    {
        $this->ensureInitialized();

        try {
            $alertRule = [
                'ticket_id'       => $ticketId,
                'condition'       => $condition, // 'available', 'price_below', 'price_above', 'price_change'
                'value'           => $value,
                'channels'        => $notificationChannels,
                'created_at'      => Carbon::now()->toISOString(),
                'triggered_count' => 0,
                'last_triggered'  => NULL,
            ];

            $ruleKey = self::ALERT_PREFIX . 'rule:' . $ticketId . ':' . $condition;
            Redis::hmset($ruleKey, $alertRule);
            Redis::expire($ruleKey, 86400 * 30); // 30 days

            // Add to ticket's alert rules
            Redis::sadd(self::ALERT_PREFIX . 'ticket:' . $ticketId, $ruleKey);

            $this->alertRules[$ticketId][$condition] = $alertRule;

            $this->logOperation('setAlertRule', [
                'ticket_id' => $ticketId,
                'condition' => $condition,
                'value'     => $value,
            ]);

            return TRUE;
        } catch (Exception $e) {
            $this->handleError($e, 'setAlertRule', [
                'ticket_id' => $ticketId,
                'condition' => $condition,
            ]);

            return FALSE;
        }
    }

    /**
     * Remove alert rule
     */
    /**
     * RemoveAlertRule
     */
    public function removeAlertRule(int $ticketId, string $condition): bool
    {
        $this->ensureInitialized();

        try {
            $ruleKey = self::ALERT_PREFIX . 'rule:' . $ticketId . ':' . $condition;

            Redis::del($ruleKey);
            Redis::srem(self::ALERT_PREFIX . 'ticket:' . $ticketId, $ruleKey);

            unset($this->alertRules[$ticketId][$condition]);

            return TRUE;
        } catch (Exception $e) {
            $this->handleError($e, 'removeAlertRule', [
                'ticket_id' => $ticketId,
                'condition' => $condition,
            ]);

            return FALSE;
        }
    }

    /**
     * Get monitoring status for ticket
     */
    /**
     * Get  monitoring status
     */
    public function getMonitoringStatus(int $ticketId): array
    {
        $this->ensureInitialized();

        $monitoringKey = self::MONITORING_PREFIX . $ticketId;
        $data = Redis::hgetall($monitoringKey);

        if (empty($data)) {
            return ['status' => 'not_monitored'];
        }

        return [
            'ticket_id'            => $ticketId,
            'status'               => $data['status'] ?? 'unknown',
            'started_at'           => $data['started_at'] ?? NULL,
            'last_check'           => $data['last_check'] ?? NULL,
            'alert_count'          => (int) ($data['alert_count'] ?? 0),
            'availability_history' => json_decode($data['availability_history'] ?? '[]', TRUE),
            'price_history'        => json_decode($data['price_history'] ?? '[]', TRUE),
            'alert_rules'          => $this->getTicketAlertRules($ticketId),
        ];
    }

    /**
     * Get all monitored tickets
     */
    /**
     * Get  monitored tickets
     */
    public function getMonitoredTickets(): array
    {
        $this->ensureInitialized();

        $activeTickets = Redis::smembers(self::MONITORING_PREFIX . 'active_tickets');
        $monitoredTickets = [];

        foreach ($activeTickets as $ticketId) {
            $monitoredTickets[] = $this->getMonitoringStatus((int) $ticketId);
        }

        return $monitoredTickets;
    }

    /**
     * Get monitoring statistics
     */
    /**
     * Get  monitoring statistics
     */
    public function getMonitoringStatistics(): array
    {
        $this->ensureInitialized();

        $activeTickets = Redis::smembers(self::MONITORING_PREFIX . 'active_tickets');
        $totalAlerts = 0;
        $recentActivity = 0;

        foreach ($activeTickets as $ticketId) {
            $monitoringKey = self::MONITORING_PREFIX . $ticketId;
            $data = Redis::hgetall($monitoringKey);
            $totalAlerts += (int) ($data['alert_count'] ?? 0);

            $lastCheck = $data['last_check'] ?? NULL;
            if ($lastCheck && Carbon::parse($lastCheck)->isAfter(Carbon::now()->subHour())) {
                $recentActivity++;
            }
        }

        return [
            'total_monitored'   => count($activeTickets),
            'recent_activity'   => $recentActivity,
            'total_alerts_sent' => $totalAlerts,
            'monitoring_health' => $this->getMonitoringHealth(),
            'timestamp'         => Carbon::now()->toISOString(),
        ];
    }

    /**
     * OnInitialize
     */
    protected function onInitialize(): void
    {
        $this->validateDependencies(['scrapingService', 'notificationService', 'analyticsService']);
        $this->loadMonitoredTickets();
        $this->loadAlertRules();
    }

    /**
     * Private helper methods
     */
    /**
     * LoadMonitoredTickets
     */
    private function loadMonitoredTickets(): void
    {
        $activeTickets = Redis::smembers(self::MONITORING_PREFIX . 'active_tickets');

        foreach ($activeTickets as $ticketId) {
            $monitoringKey = self::MONITORING_PREFIX . $ticketId;
            $data = Redis::hgetall($monitoringKey);

            if (! empty($data)) {
                $this->monitoredTickets[$ticketId] = $data;
            }
        }
    }

    /**
     * LoadAlertRules
     */
    private function loadAlertRules(): void
    {
        foreach ($this->monitoredTickets as $ticketId => $data) {
            $this->alertRules[$ticketId] = $this->getTicketAlertRules($ticketId);
        }
    }

    /**
     * ProcessAvailabilityResults
     */
    private function processAvailabilityResults(int $ticketId, array $scrapingResults): array
    {
        $totalResults = 0;
        $availablePlatforms = [];
        $priceInfo = [];
        $bestPrice = NULL;

        foreach ($scrapingResults['results'] as $platform => $result) {
            if ($result['status'] === 'success' && $result['count'] > 0) {
                $totalResults += $result['count'];
                $availablePlatforms[] = $platform;

                // Extract price information
                foreach ($result['results'] as $ticket) {
                    if (isset($ticket['price']) && is_numeric($ticket['price'])) {
                        $priceInfo[] = [
                            'platform' => $platform,
                            'price'    => (float) $ticket['price'],
                            'currency' => $ticket['currency'] ?? 'USD',
                        ];

                        if ($bestPrice === NULL || $ticket['price'] < $bestPrice) {
                            $bestPrice = (float) $ticket['price'];
                        }
                    }
                }
            }
        }

        return [
            'ticket_id'           => $ticketId,
            'is_available'        => $totalResults > 0,
            'total_available'     => $totalResults,
            'available_platforms' => $availablePlatforms,
            'platform_count'      => count($availablePlatforms),
            'price_info'          => $priceInfo,
            'best_price'          => $bestPrice,
            'checked_at'          => Carbon::now()->toISOString(),
            'scraping_summary'    => $scrapingResults['summary'],
        ];
    }

    /**
     * UpdateAvailabilityHistory
     */
    private function updateAvailabilityHistory(int $ticketId, array $availabilityData): void
    {
        $monitoringKey = self::MONITORING_PREFIX . $ticketId;

        // Get current history
        $historyData = Redis::hget($monitoringKey, 'availability_history');
        $history = json_decode($historyData ?? '[]', TRUE);

        // Add new data point
        $history[] = [
            'timestamp'  => $availabilityData['checked_at'],
            'available'  => $availabilityData['is_available'],
            'count'      => $availabilityData['total_available'],
            'best_price' => $availabilityData['best_price'],
            'platforms'  => count($availabilityData['available_platforms']),
        ];

        // Keep only last 100 history points
        $history = array_slice($history, -100);

        // Update Redis
        Redis::hset($monitoringKey, 'availability_history', json_encode($history));

        // Update price history if we have price data
        if ($availabilityData['best_price']) {
            $this->updatePriceHistory($ticketId, $availabilityData['best_price']);
        }
    }

    /**
     * UpdatePriceHistory
     */
    private function updatePriceHistory(int $ticketId, float $price): void
    {
        $monitoringKey = self::MONITORING_PREFIX . $ticketId;

        $priceHistoryData = Redis::hget($monitoringKey, 'price_history');
        $priceHistory = json_decode($priceHistoryData ?? '[]', TRUE);

        $priceHistory[] = [
            'timestamp' => Carbon::now()->toISOString(),
            'price'     => $price,
        ];

        // Keep only last 100 price points
        $priceHistory = array_slice($priceHistory, -100);

        Redis::hset($monitoringKey, 'price_history', json_encode($priceHistory));
    }

    /**
     * CheckAlertConditions
     */
    private function checkAlertConditions(int $ticketId, array $availabilityData): void
    {
        $alertRules = $this->getTicketAlertRules($ticketId);

        foreach ($alertRules as $condition => $rule) {
            $shouldTrigger = FALSE;
            $message = '';

            switch ($condition) {
                case 'available':
                    if ($availabilityData['is_available'] && $availabilityData['total_available'] >= ($rule['value'] ?? 1)) {
                        $shouldTrigger = TRUE;
                        $message = "Tickets now available! Found {$availabilityData['total_available']} tickets.";
                    }

                    break;
                case 'price_below':
                    if ($availabilityData['best_price'] && $availabilityData['best_price'] <= (float) $rule['value']) {
                        $shouldTrigger = TRUE;
                        $message = 'Price alert! Best price is now $' . number_format($availabilityData['best_price'], 2) .
                                 ' (below your target of $' . number_format((float) $rule['value'], 2) . ')';
                    }

                    break;
                case 'price_above':
                    if ($availabilityData['best_price'] && $availabilityData['best_price'] >= (float) $rule['value']) {
                        $shouldTrigger = TRUE;
                        $message = 'Price alert! Best price is now $' . number_format($availabilityData['best_price'], 2) .
                                 ' (above your threshold of $' . number_format((float) $rule['value'], 2) . ')';
                    }

                    break;
            }

            if ($shouldTrigger) {
                $this->triggerAlert($ticketId, $condition, $message, $rule['channels'] ?? ['email']);
            }
        }
    }

    /**
     * TriggerAlert
     */
    private function triggerAlert(int $ticketId, string $condition, string $message, array $channels): void
    {
        try {
            // Send notification
            $this->getDependency('notificationService')->sendTicketAlert([
                'ticket_id' => $ticketId,
                'condition' => $condition,
                'message'   => $message,
            ], [], 'high');

            // Update alert rule stats
            $ruleKey = self::ALERT_PREFIX . 'rule:' . $ticketId . ':' . $condition;
            Redis::hincrby($ruleKey, 'triggered_count', 1);
            Redis::hset($ruleKey, 'last_triggered', Carbon::now()->toISOString());

            // Update monitoring stats
            $monitoringKey = self::MONITORING_PREFIX . $ticketId;
            Redis::hincrby($monitoringKey, 'alert_count', 1);

            $this->logOperation('triggerAlert', [
                'ticket_id' => $ticketId,
                'condition' => $condition,
                'message'   => $message,
            ]);
        } catch (Exception $e) {
            $this->handleError($e, 'triggerAlert', [
                'ticket_id' => $ticketId,
                'condition' => $condition,
            ]);
        }
    }

    /**
     * Get  ticket alert rules
     */
    private function getTicketAlertRules(int $ticketId): array
    {
        $ruleKeys = Redis::smembers(self::ALERT_PREFIX . 'ticket:' . $ticketId);
        $rules = [];

        foreach ($ruleKeys as $ruleKey) {
            $ruleData = Redis::hgetall($ruleKey);
            if (! empty($ruleData)) {
                $condition = $ruleData['condition'] ?? '';
                $rules[$condition] = $ruleData;
            }
        }

        return $rules;
    }

    /**
     * Get  monitoring health
     */
    private function getMonitoringHealth(): string
    {
        $activeTickets = Redis::smembers(self::MONITORING_PREFIX . 'active_tickets');
        $healthyCount = 0;

        foreach ($activeTickets as $ticketId) {
            $monitoringKey = self::MONITORING_PREFIX . $ticketId;
            $lastCheck = Redis::hget($monitoringKey, 'last_check');

            if ($lastCheck && Carbon::parse($lastCheck)->isAfter(Carbon::now()->subHours(2))) {
                $healthyCount++;
            }
        }

        if (count($activeTickets) === 0) {
            return 'no_monitoring';
        }

        $healthPercentage = ($healthyCount / count($activeTickets)) * 100;

        if ($healthPercentage > 80) {
            return 'healthy';
        }
        if ($healthPercentage > 50) {
            return 'warning';
        }

        return 'critical';
    }
}
