<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;
use function in_array;
use function sprintf;

class LoginAnalyticsService
{
    /**
     * Track a login attempt (successful or failed)
     */
    public function trackLoginAttempt(
        string $email,
        bool $successful,
        string $ip,
        ?string $userAgent = NULL,
        array $metadata = [],
    ): void {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');
        $date = $timestamp->format('Y-m-d');

        // Track hourly metrics
        $hourlyKey = "login_metrics:hourly:{$hour}";
        $hourlyMetrics = Cache::get($hourlyKey, [
            'total_attempts'    => 0,
            'successful_logins' => 0,
            'failed_logins'     => 0,
            'unique_ips'        => [],
            'user_agents'       => [],
            'errors'            => [],
        ]);

        $hourlyMetrics['total_attempts']++;

        if ($successful) {
            $hourlyMetrics['successful_logins']++;
        } else {
            $hourlyMetrics['failed_logins']++;

            // Track error types
            $errorType = $metadata['error_type'] ?? 'unknown';
            $hourlyMetrics['errors'][$errorType] = ($hourlyMetrics['errors'][$errorType] ?? 0) + 1;
        }

        // Track unique IPs (limit to prevent memory issues)
        if (! in_array($ip, $hourlyMetrics['unique_ips'], TRUE) && count($hourlyMetrics['unique_ips']) < 1000) {
            $hourlyMetrics['unique_ips'][] = $ip;
        }

        // Track user agents (simplified)
        if ($userAgent) {
            $simplifiedUA = $this->simplifyUserAgent($userAgent);
            $hourlyMetrics['user_agents'][$simplifiedUA] = ($hourlyMetrics['user_agents'][$simplifiedUA] ?? 0) + 1;
        }

        Cache::put($hourlyKey, $hourlyMetrics, now()->addDays(7));

        // Track daily rollup
        $this->updateDailyMetrics($date, $successful, $metadata);

        // Track real-time metrics
        $this->updateRealTimeMetrics($successful, $ip, $metadata);
    }

    /**
     * Track security events
     */
    public function trackSecurityEvent(
        string $eventType,
        string $ip,
        ?string $userAgent = NULL,
        array $details = [],
    ): void {
        $timestamp = now();
        $hour = $timestamp->format('Y-m-d-H');

        $securityKey = "security_events:hourly:{$hour}";
        $securityMetrics = Cache::get($securityKey, [
            'events'       => [],
            'event_counts' => [],
            'affected_ips' => [],
        ]);

        // Add event
        $event = [
            'type'       => $eventType,
            'ip'         => $ip,
            'user_agent' => $userAgent,
            'details'    => $details,
            'timestamp'  => $timestamp->toISOString(),
        ];

        $securityMetrics['events'][] = $event;
        $securityMetrics['event_counts'][$eventType] = ($securityMetrics['event_counts'][$eventType] ?? 0) + 1;

        if (! in_array($ip, $securityMetrics['affected_ips'], TRUE)) {
            $securityMetrics['affected_ips'][] = $ip;
        }

        // Keep only last 100 events per hour
        $securityMetrics['events'] = array_slice($securityMetrics['events'], -100);

        Cache::put($securityKey, $securityMetrics, now()->addDays(7));

        // Log security event for external monitoring
        Log::channel('security')->warning('Login security event', [
            'event_type' => $eventType,
            'ip'         => $ip,
            'user_agent' => $userAgent,
            'details'    => $details,
        ]);
    }

    /**
     * Get comprehensive login analytics for dashboard
     */
    public function getAnalytics(int $days = 7): array
    {
        return [
            'overview'      => $this->getOverviewMetrics($days),
            'trends'        => $this->getTrendData($days),
            'security'      => $this->getSecurityMetrics($days),
            'performance'   => $this->getPerformanceMetrics(),
            'user_behavior' => $this->getUserBehaviorMetrics($days),
            'real_time'     => $this->getRealTimeMetrics(),
        ];
    }

    /**
     * Get real-time login metrics for monitoring
     */
    public function getRealTimeMetrics(): array
    {
        $realTimeKey = 'login_metrics:realtime';
        $metrics = Cache::get($realTimeKey, [
            'active_sessions' => 0,
            'recent_logins'   => [],
            'active_ips'      => [],
            'alerts'          => [],
        ]);

        // Get active sessions count from database
        $metrics['active_sessions'] = $this->getActiveSessionsCount();

        return $metrics;
    }

    /**
     * Generate alerts based on metrics
     */
    public function generateAlerts(): array
    {
        $alerts = [];
        $realTimeMetrics = $this->getRealTimeMetrics();

        // Check for high failure rate
        $recentFailures = collect($realTimeMetrics['recent_logins'])
            ->where('successful', FALSE)
            ->count();

        if ($recentFailures > 10) {
            $alerts[] = [
                'type'      => 'high_failure_rate',
                'severity'  => 'warning',
                'message'   => "High login failure rate detected: {$recentFailures} failed attempts in the last few minutes",
                'timestamp' => now()->toISOString(),
            ];
        }

        // Check for suspicious IP activity
        $uniqueActiveIPs = count($realTimeMetrics['active_ips']);
        if ($uniqueActiveIPs > 50) {
            $alerts[] = [
                'type'      => 'high_ip_activity',
                'severity'  => 'warning',
                'message'   => "Unusually high number of active IPs: {$uniqueActiveIPs}",
                'timestamp' => now()->toISOString(),
            ];
        }

        return $alerts;
    }

    /**
     * Update real-time metrics
     */
    private function updateRealTimeMetrics(bool $successful, string $ip, array $metadata): void
    {
        $realTimeKey = 'login_metrics:realtime';
        $metrics = Cache::get($realTimeKey, [
            'active_sessions' => 0,
            'recent_logins'   => [],
            'active_ips'      => [],
            'alerts'          => [],
        ]);

        // Add recent login (keep last 20)
        $metrics['recent_logins'][] = [
            'successful' => $successful,
            'ip'         => $ip,
            'timestamp'  => now()->toISOString(),
            'error_type' => $metadata['error_type'] ?? NULL,
        ];
        $metrics['recent_logins'] = array_slice($metrics['recent_logins'], -20);

        // Track active IPs (last 5 minutes)
        $cutoff = now()->subMinutes(5);
        $metrics['active_ips'] = array_filter($metrics['active_ips'], fn (array $entry) => carbon($entry['timestamp'])->gt($cutoff));

        if (! collect($metrics['active_ips'])->contains('ip', $ip)) {
            $metrics['active_ips'][] = [
                'ip'        => $ip,
                'timestamp' => now()->toISOString(),
            ];
        }

        Cache::put($realTimeKey, $metrics, now()->addMinutes(30));
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics(int $days): array
    {
        $totalAttempts = 0;
        $successfulLogins = 0;
        $failedLogins = 0;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');

            for ($hour = 0; $hour < 24; $hour++) {
                $hourlyKey = "login_metrics:hourly:{$date}-{$hour}";
                $hourlyMetrics = Cache::get($hourlyKey, []);

                $totalAttempts += $hourlyMetrics['total_attempts'] ?? 0;
                $successfulLogins += $hourlyMetrics['successful_logins'] ?? 0;
                $failedLogins += $hourlyMetrics['failed_logins'] ?? 0;
            }
        }

        $successRate = $totalAttempts > 0 ? ($successfulLogins / $totalAttempts) * 100 : 0;

        return [
            'total_attempts'    => $totalAttempts,
            'successful_logins' => $successfulLogins,
            'failed_logins'     => $failedLogins,
            'success_rate'      => round($successRate, 2),
            'active_sessions'   => $this->getActiveSessionsCount(),
        ];
    }

    /**
     * Get trend data for charts
     */
    private function getTrendData(int $days): array
    {
        $trends = [
            'daily'        => [],
            'hourly'       => [],
            'success_rate' => [],
        ];

        // Daily trends
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            $dailyKey = "login_metrics:daily:{$dateStr}";
            $dailyMetrics = Cache::get($dailyKey, [
                'total_attempts'    => 0,
                'successful_logins' => 0,
                'failed_logins'     => 0,
            ]);

            $trends['daily'][] = [
                'date'         => $dateStr,
                'total'        => $dailyMetrics['total_attempts'],
                'successful'   => $dailyMetrics['successful_logins'],
                'failed'       => $dailyMetrics['failed_logins'],
                'success_rate' => $dailyMetrics['total_attempts'] > 0
                    ? ($dailyMetrics['successful_logins'] / $dailyMetrics['total_attempts']) * 100
                    : 0,
            ];
        }

        // Hourly trends for last 24 hours
        for ($i = 23; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $hourStr = $hour->format('Y-m-d-H');

            $hourlyKey = "login_metrics:hourly:{$hourStr}";
            $hourlyMetrics = Cache::get($hourlyKey, []);

            $trends['hourly'][] = [
                'hour'       => $hour->format('H:00'),
                'total'      => $hourlyMetrics['total_attempts'] ?? 0,
                'successful' => $hourlyMetrics['successful_logins'] ?? 0,
                'failed'     => $hourlyMetrics['failed_logins'] ?? 0,
            ];
        }

        return $trends;
    }

    /**
     * Get security metrics
     */
    private function getSecurityMetrics(int $days): array
    {
        $securityEvents = [];
        $threatLevels = ['low' => 0, 'medium' => 0, 'high' => 0];
        $blockedIPs = [];

        for ($i = 0; $i < $days * 24; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $securityKey = "security_events:hourly:{$hour}";
            $hourlyEvents = Cache::get($securityKey, []);

            if (! empty($hourlyEvents['event_counts'])) {
                foreach ($hourlyEvents['event_counts'] as $eventType => $count) {
                    $securityEvents[$eventType] = ($securityEvents[$eventType] ?? 0) + $count;

                    // Classify threat level
                    $threatLevel = $this->classifyThreatLevel($eventType);
                    $threatLevels[$threatLevel] += $count;
                }
            }
        }

        return [
            'events_by_type' => $securityEvents,
            'threat_levels'  => $threatLevels,
            'blocked_ips'    => count($blockedIPs),
            'security_score' => $this->calculateSecurityScore($threatLevels),
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        // This would typically come from APM tools or logs
        return [
            'average_response_time' => 245, // milliseconds
            'p95_response_time'     => 450,
            'p99_response_time'     => 850,
            'error_rate'            => 0.02, // 2%
            'uptime_percentage'     => 99.95,
        ];
    }

    /**
     * Get user behavior metrics
     */
    private function getUserBehaviorMetrics(int $days): array
    {
        $userAgents = [];
        $deviceTypes = ['desktop' => 0, 'mobile' => 0, 'tablet' => 0];
        $browsers = [];

        for ($i = 0; $i < $days * 24; $i++) {
            $hour = now()->subHours($i)->format('Y-m-d-H');
            $hourlyKey = "login_metrics:hourly:{$hour}";
            $hourlyMetrics = Cache::get($hourlyKey, []);

            if (! empty($hourlyMetrics['user_agents'])) {
                foreach ($hourlyMetrics['user_agents'] as $ua => $count) {
                    $userAgents[$ua] = ($userAgents[$ua] ?? 0) + $count;

                    // Classify device type
                    $deviceType = $this->classifyDeviceType($ua);
                    $deviceTypes[$deviceType] += $count;

                    // Extract browser
                    $browser = $this->extractBrowser($ua);
                    $browsers[$browser] = ($browsers[$browser] ?? 0) + $count;
                }
            }
        }

        return [
            'device_types' => $deviceTypes,
            'top_browsers' => array_slice(arsort($browsers) ? $browsers : [], 0, 10, TRUE),
            'peak_hours'   => $this->calculatePeakHours($days),
        ];
    }

    /**
     * Update daily metrics rollup
     */
    private function updateDailyMetrics(string $date, bool $successful, array $metadata): void
    {
        $dailyKey = "login_metrics:daily:{$date}";
        $dailyMetrics = Cache::get($dailyKey, [
            'total_attempts'    => 0,
            'successful_logins' => 0,
            'failed_logins'     => 0,
            'error_types'       => [],
        ]);

        $dailyMetrics['total_attempts']++;

        if ($successful) {
            $dailyMetrics['successful_logins']++;
        } else {
            $dailyMetrics['failed_logins']++;

            $errorType = $metadata['error_type'] ?? 'unknown';
            $dailyMetrics['error_types'][$errorType] = ($dailyMetrics['error_types'][$errorType] ?? 0) + 1;
        }

        Cache::put($dailyKey, $dailyMetrics, now()->addDays(30));
    }

    /**
     * Get active sessions count from database
     */
    private function getActiveSessionsCount(): int
    {
        try {
            return DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                ->count();
        } catch (Exception $e) {
            Log::warning('Failed to get active sessions count', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    /**
     * Simplify user agent for tracking
     */
    private function simplifyUserAgent(string $userAgent): string
    {
        if (stripos($userAgent, 'Chrome') !== FALSE) {
            return 'Chrome';
        }
        if (stripos($userAgent, 'Firefox') !== FALSE) {
            return 'Firefox';
        }
        if (stripos($userAgent, 'Safari') !== FALSE && stripos($userAgent, 'Chrome') === FALSE) {
            return 'Safari';
        }
        if (stripos($userAgent, 'Edge') !== FALSE) {
            return 'Edge';
        }
        if (stripos($userAgent, 'Opera') !== FALSE) {
            return 'Opera';
        }
        if (stripos($userAgent, 'bot') !== FALSE || stripos($userAgent, 'crawler') !== FALSE) {
            return 'Bot';
        }

        return 'Other';
    }

    /**
     * Classify threat level
     */
    private function classifyThreatLevel(string $eventType): string
    {
        $highThreatEvents = ['brute_force_detected', 'suspicious_automation', 'multiple_account_lockouts'];
        $mediumThreatEvents = ['rapid_failed_attempts', 'unusual_location', 'suspicious_user_agent'];

        if (in_array($eventType, $highThreatEvents, TRUE)) {
            return 'high';
        }
        if (in_array($eventType, $mediumThreatEvents, TRUE)) {
            return 'medium';
        }

        return 'low';
    }

    /**
     * Calculate security score (0-100)
     */
    private function calculateSecurityScore(array $threatLevels): int
    {
        $totalEvents = array_sum($threatLevels);

        if ($totalEvents === 0) {
            return 100;
        }

        $score = 100;
        $score -= ($threatLevels['high'] * 10);    // High threats: -10 points each
        $score -= ($threatLevels['medium'] * 3);   // Medium threats: -3 points each
        $score -= ($threatLevels['low'] * 1);      // Low threats: -1 point each

        return max(0, $score);
    }

    /**
     * Classify device type from user agent
     */
    private function classifyDeviceType(string $userAgent): string
    {
        if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
            if (preg_match('/iPad|Tablet/', $userAgent)) {
                return 'tablet';
            }

            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Extract browser from user agent
     */
    private function extractBrowser(string $userAgent): string
    {
        return $this->simplifyUserAgent($userAgent);
    }

    /**
     * Calculate peak login hours
     */
    private function calculatePeakHours(int $days): array
    {
        $hourlyTotals = array_fill(0, 24, 0);

        for ($i = 0; $i < $days * 24; $i++) {
            $hour = now()->subHours($i);
            $hourOfDay = (int) $hour->format('H');
            $hourStr = $hour->format('Y-m-d-H');

            $hourlyKey = "login_metrics:hourly:{$hourStr}";
            $hourlyMetrics = Cache::get($hourlyKey, []);

            $hourlyTotals[$hourOfDay] += $hourlyMetrics['total_attempts'] ?? 0;
        }

        $peakHours = [];
        foreach ($hourlyTotals as $hour => $total) {
            $peakHours[] = [
                'hour'         => sprintf('%02d:00', $hour),
                'total_logins' => $total,
            ];
        }

        // Sort by total logins and get top 5
        usort($peakHours, fn (array $a, array $b): int => $b['total_logins'] <=> $a['total_logins']);

        return array_slice($peakHours, 0, 5);
    }
}
