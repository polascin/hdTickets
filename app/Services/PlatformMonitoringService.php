<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapingStats;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function get_class;
use function is_array;

class PlatformMonitoringService
{
    private const CRITICAL_SUCCESS_RATE_THRESHOLD = 50.0; // Below 50% is critical

    private const WARNING_SUCCESS_RATE_THRESHOLD = 80.0;  // Below 80% is warning

    private const HIGH_RESPONSE_TIME_THRESHOLD = 5000;    // Above 5 seconds is high

    private const MONITORING_INTERVAL_HOURS = 1;          // Check every hour

    /**
     * Monitor all platforms and generate alerts if needed
     */
    /**
     * MonitorAllPlatforms
     */
    public function monitorAllPlatforms(): array
    {
        $platforms = $this->getActivePlatforms();
        $alerts = [];

        foreach ($platforms as $platform) {
            $alerts = array_merge($alerts, $this->monitorPlatform($platform));
        }

        if (! empty($alerts)) {
            $this->processAlerts($alerts);
        }

        return $alerts;
    }

    /**
     * Monitor a specific platform
     */
    /**
     * MonitorPlatform
     */
    public function monitorPlatform(string $platform): array
    {
        $alerts = [];
        $stats = $this->getPlatformStats($platform);

        // Check success rate
        $successRateAlert = $this->checkSuccessRate($platform, $stats['success_rate']);
        if ($successRateAlert) {
            $alerts[] = $successRateAlert;
        }

        // Check response times
        $responseTimeAlert = $this->checkResponseTime($platform, $stats['avg_response_time']);
        if ($responseTimeAlert) {
            $alerts[] = $responseTimeAlert;
        }

        // Check for bot detection
        $botDetectionAlert = $this->checkBotDetection($platform, $stats['error_stats']);
        if ($botDetectionAlert) {
            $alerts[] = $botDetectionAlert;
        }

        // Check for frequent rate limiting
        $rateLimitAlert = $this->checkRateLimit($platform, $stats['error_stats']);
        if ($rateLimitAlert) {
            $alerts[] = $rateLimitAlert;
        }

        // Check selector effectiveness
        $selectorAlert = $this->checkSelectorEffectiveness($platform, $stats['selector_stats']);
        if ($selectorAlert) {
            $alerts[] = $selectorAlert;
        }

        return $alerts;
    }

    /**
     * Get comprehensive stats for a platform
     */
    /**
     * Get  platform stats
     */
    public function getPlatformStats(string $platform, int $hours = 24): array
    {
        $cacheKey = "platform_stats_{$platform}_{$hours}";

        return Cache::remember($cacheKey, 600, function () use ($platform, $hours) {
            return [
                'platform'            => $platform,
                'time_period_hours'   => $hours,
                'success_rate'        => ScrapingStats::getSuccessRate($platform, $hours),
                'avg_response_time'   => ScrapingStats::getAverageResponseTime($platform, $hours),
                'availability'        => ScrapingStats::getPlatformAvailability($platform, 1),
                'error_stats'         => ScrapingStats::getErrorStats($platform, $hours),
                'selector_stats'      => ScrapingStats::getSelectorStats($platform, $hours),
                'total_requests'      => ScrapingStats::platform($platform)->recent($hours)->count(),
                'successful_requests' => ScrapingStats::platform($platform)->recent($hours)->successful()->count(),
                'failed_requests'     => ScrapingStats::platform($platform)->recent($hours)->failed()->count(),
                'last_success'        => ScrapingStats::platform($platform)->successful()->latest('created_at')->value('created_at'),
                'last_failure'        => ScrapingStats::platform($platform)->failed()->latest('created_at')->value('created_at'),
            ];
        });
    }

    /**
     * Get stats for all platforms
     */
    /**
     * Get  all platform stats
     */
    public function getAllPlatformStats(int $hours = 24): Collection
    {
        $platforms = $this->getActivePlatforms();

        return collect($platforms)->map(function ($platform) use ($hours) {
            return $this->getPlatformStats($platform, $hours);
        });
    }

    /**
     * Record a successful scraping operation
     */
    /**
     * RecordSuccess
     */
    public function recordSuccess(
        string $platform,
        string $method,
        string $operation,
        array $searchCriteria = [],
        ?int $responseTime = NULL,
        int $resultsCount = 0,
        array $selectorsUsed = [],
        ?string $url = NULL,
    ): void {
        ScrapingStats::create([
            'platform'         => $platform,
            'method'           => $method,
            'operation'        => $operation,
            'url'              => $url,
            'search_criteria'  => $searchCriteria,
            'status'           => 'success',
            'response_time_ms' => $responseTime,
            'results_count'    => $resultsCount,
            'selectors_used'   => $selectorsUsed,
            'started_at'       => now(),
            'completed_at'     => now(),
        ]);

        // Clear platform stats cache
        $this->clearPlatformStatsCache($platform);
    }

    /**
     * Record a failed scraping operation
     */
    /**
     * RecordFailure
     */
    public function recordFailure(
        string $platform,
        string $method,
        string $operation,
        Exception $exception,
        array $searchCriteria = [],
        ?int $responseTime = NULL,
        array $selectorsUsed = [],
        ?string $url = NULL,
    ): void {
        $status = $this->determineFailureStatus($exception);

        ScrapingStats::create([
            'platform'         => $platform,
            'method'           => $method,
            'operation'        => $operation,
            'url'              => $url,
            'search_criteria'  => $searchCriteria,
            'status'           => $status,
            'response_time_ms' => $responseTime,
            'results_count'    => 0,
            'error_type'       => get_class($exception),
            'error_message'    => $exception->getMessage(),
            'selectors_used'   => $selectorsUsed,
            'started_at'       => now(),
            'completed_at'     => now(),
        ]);

        // Clear platform stats cache
        $this->clearPlatformStatsCache($platform);
    }

    /**
     * Check success rate and generate alert if needed
     */
    /**
     * CheckSuccessRate
     */
    private function checkSuccessRate(string $platform, float $successRate): ?array
    {
        if ($successRate < self::CRITICAL_SUCCESS_RATE_THRESHOLD) {
            return [
                'type'      => 'critical',
                'platform'  => $platform,
                'metric'    => 'success_rate',
                'value'     => $successRate,
                'threshold' => self::CRITICAL_SUCCESS_RATE_THRESHOLD,
                'message'   => "Critical: {$platform} success rate is {$successRate}% (below {self::CRITICAL_SUCCESS_RATE_THRESHOLD}%)",
                'timestamp' => now(),
            ];
        }
        if ($successRate < self::WARNING_SUCCESS_RATE_THRESHOLD) {
            return [
                'type'      => 'warning',
                'platform'  => $platform,
                'metric'    => 'success_rate',
                'value'     => $successRate,
                'threshold' => self::WARNING_SUCCESS_RATE_THRESHOLD,
                'message'   => "Warning: {$platform} success rate is {$successRate}% (below {self::WARNING_SUCCESS_RATE_THRESHOLD}%)",
                'timestamp' => now(),
            ];
        }

        return NULL;
    }

    /**
     * Check response time and generate alert if needed
     */
    /**
     * CheckResponseTime
     */
    private function checkResponseTime(string $platform, float $avgResponseTime): ?array
    {
        if ($avgResponseTime > self::HIGH_RESPONSE_TIME_THRESHOLD) {
            return [
                'type'      => 'warning',
                'platform'  => $platform,
                'metric'    => 'response_time',
                'value'     => $avgResponseTime,
                'threshold' => self::HIGH_RESPONSE_TIME_THRESHOLD,
                'message'   => "Warning: {$platform} average response time is {$avgResponseTime}ms (above {self::HIGH_RESPONSE_TIME_THRESHOLD}ms)",
                'timestamp' => now(),
            ];
        }

        return NULL;
    }

    /**
     * Check for bot detection issues
     */
    /**
     * CheckBotDetection
     */
    private function checkBotDetection(string $platform, array $errorStats): ?array
    {
        $botDetectionErrors = [
            'App\Exceptions\ScrapingDetectedException',
            'bot_detected',
        ];

        $botDetectionCount = 0;
        foreach ($botDetectionErrors as $errorType) {
            $botDetectionCount += $errorStats[$errorType] ?? 0;
        }

        if ($botDetectionCount > 5) { // More than 5 bot detection errors in the time period
            return [
                'type'      => 'critical',
                'platform'  => $platform,
                'metric'    => 'bot_detection',
                'value'     => $botDetectionCount,
                'threshold' => 5,
                'message'   => "Critical: {$platform} has {$botDetectionCount} bot detection instances (threshold: 5)",
                'timestamp' => now(),
            ];
        }

        return NULL;
    }

    /**
     * Check for rate limiting issues
     */
    /**
     * CheckRateLimit
     */
    private function checkRateLimit(string $platform, array $errorStats): ?array
    {
        $rateLimitErrors = [
            'App\Exceptions\RateLimitException',
            'rate_limited',
        ];

        $rateLimitCount = 0;
        foreach ($rateLimitErrors as $errorType) {
            $rateLimitCount += $errorStats[$errorType] ?? 0;
        }

        if ($rateLimitCount > 10) { // More than 10 rate limit errors in the time period
            return [
                'type'      => 'warning',
                'platform'  => $platform,
                'metric'    => 'rate_limit',
                'value'     => $rateLimitCount,
                'threshold' => 10,
                'message'   => "Warning: {$platform} has {$rateLimitCount} rate limit instances (threshold: 10)",
                'timestamp' => now(),
            ];
        }

        return NULL;
    }

    /**
     * Check selector effectiveness
     */
    /**
     * CheckSelectorEffectiveness
     */
    private function checkSelectorEffectiveness(string $platform, array $selectorStats): ?array
    {
        $lowEffectivenessSelectors = [];

        foreach ($selectorStats as $selector => $stats) {
            if ($stats['total_uses'] >= 10 && $stats['success_rate'] < 70) {
                $lowEffectivenessSelectors[$selector] = $stats['success_rate'];
            }
        }

        if (! empty($lowEffectivenessSelectors)) {
            $selectorList = implode(', ', array_keys($lowEffectivenessSelectors));

            return [
                'type'      => 'warning',
                'platform'  => $platform,
                'metric'    => 'selector_effectiveness',
                'value'     => $lowEffectivenessSelectors,
                'threshold' => 70,
                'message'   => "Warning: {$platform} has low-effectiveness selectors: {$selectorList}",
                'timestamp' => now(),
            ];
        }

        return NULL;
    }

    /**
     * Process alerts by logging and potentially sending notifications
     */
    /**
     * ProcessAlerts
     */
    private function processAlerts(array $alerts): void
    {
        foreach ($alerts as $alert) {
            $logLevel = $alert['type'] === 'critical' ? 'error' : 'warning';

            Log::channel('ticket_apis')->{$logLevel}('Platform monitoring alert', $alert);

            // Store alert in cache for dashboard display
            $alertKey = "platform_alert_{$alert['platform']}_{$alert['metric']}";
            Cache::put($alertKey, $alert, 3600); // Store for 1 hour

            // Send notification if critical
            if ($alert['type'] === 'critical') {
                $this->sendCriticalAlert($alert);
            }
        }
    }

    /**
     * Send critical alert notification
     */
    /**
     * SendCriticalAlert
     */
    private function sendCriticalAlert(array $alert): void
    {
        // Here you could send email, Slack notification, SMS, etc.
        // For now, we'll just log it as a critical error

        Log::channel('ticket_apis')->critical('CRITICAL PLATFORM ALERT', [
            'alert'           => $alert,
            'action_required' => TRUE,
        ]);

        // You could also trigger events here for notification systems
        // event(new CriticalPlatformAlert($alert));
    }

    /**
     * Get list of active platforms from config
     */
    /**
     * Get  active platforms
     */
    private function getActivePlatforms(): array
    {
        $platforms = config('ticket_apis', []);
        $activePlatforms = [];

        foreach ($platforms as $platform => $config) {
            if (is_array($config) && ($config['enabled'] ?? FALSE)) {
                $activePlatforms[] = $platform;
            }
        }

        return $activePlatforms;
    }

    /**
     * Determine failure status based on exception type
     */
    /**
     * DetermineFailureStatus
     */
    private function determineFailureStatus(Exception $exception): string
    {
        $className = get_class($exception);

        if (str_contains($className, 'ScrapingDetected')) {
            return 'bot_detected';
        }
        if (str_contains($className, 'RateLimit')) {
            return 'rate_limited';
        }
        if (str_contains($className, 'Timeout')) {
            return 'timeout';
        }

        return 'failed';
    }

    /**
     * Clear platform stats cache
     */
    /**
     * ClearPlatformStatsCache
     */
    private function clearPlatformStatsCache(string $platform): void
    {
        $keys = [
            "platform_stats_{$platform}_1",
            "platform_stats_{$platform}_24",
            "platform_stats_{$platform}_168", // 7 days
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
