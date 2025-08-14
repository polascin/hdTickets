<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PurchaseAttempt;
use App\Models\PurchaseQueue;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PurchaseAnalyticsService
{
    /**
     * Get comprehensive purchase analytics for a given time period
     */
    /**
     * Get  purchase analytics
     */
    public function getPurchaseAnalytics(string $period = '24h', ?string $platform = NULL): array
    {
        $cacheKey = "purchase_analytics_{$period}_" . ($platform ?? 'all');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($period, $platform) {
            $dateRange = $this->getDateRange($period);

            return [
                'summary'              => $this->getSummaryStats($dateRange, $platform),
                'success_rates'        => $this->getSuccessRates($dateRange, $platform),
                'platform_performance' => $this->getPlatformPerformance($dateRange),
                'time_series'          => $this->getTimeSeriesData($dateRange, $platform),
                'failure_analysis'     => $this->getFailureAnalysis($dateRange, $platform),
                'performance_metrics'  => $this->getPerformanceMetrics($dateRange, $platform),
            ];
        });
    }

    /**
     * Get real-time purchase monitoring dashboard data
     */
    /**
     * Get  real time data
     */
    public function getRealTimeData(): array
    {
        return [
            'current_processing'     => $this->getCurrentProcessingCount(),
            'queue_status'           => $this->getQueueStatus(),
            'recent_attempts'        => $this->getRecentAttempts(),
            'success_rate_last_hour' => $this->getRecentSuccessRate('1h'),
            'alerts'                 => $this->getActiveAlerts(),
            'system_health'          => $this->getSystemHealthMetrics(),
        ];
    }

    /**
     * Get purchase success rate trends
     */
    /**
     * Get  success rate trends
     */
    public function getSuccessRateTrends(string $period = '7d'): array
    {
        $dateRange = $this->getDateRange($period);

        $query = PurchaseAttempt::selectRaw('
                DATE(created_at) as date,
                COUNT(*) as total_attempts,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts,
                ROUND((SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
            ')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $query->map(function ($item) {
            return [
                'date'                => $item->date,
                'total_attempts'      => $item->total_attempts,
                'successful_attempts' => $item->successful_attempts,
                'success_rate'        => $item->success_rate ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get platform-specific performance metrics
     */
    /**
     * Get  platform metrics
     */
    public function getPlatformMetrics(): array
    {
        $platforms = PurchaseAttempt::select('platform')
            ->distinct()
            ->pluck('platform')
            ->toArray();

        $metrics = [];
        foreach ($platforms as $platform) {
            $metrics[$platform] = $this->getPlatformSpecificMetrics($platform);
        }

        return $metrics;
    }

    /**
     * Get purchase attempt failure reasons analysis
     */
    /**
     * Get  failure analysis
     */
    public function getFailureAnalysis(array $dateRange, ?string $platform = NULL): array
    {
        $query = PurchaseAttempt::select([
            'failure_reason',
            DB::raw('COUNT(*) as count'),
            DB::raw('ROUND((COUNT(*) / (SELECT COUNT(*) FROM purchase_attempts WHERE status = "failed")) * 100, 2) as percentage'),
        ])
            ->where('status', 'failed')
            ->whereBetween('created_at', $dateRange);

        if ($platform) {
            $query->where('platform', $platform);
        }

        return $query->whereNotNull('failure_reason')
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get purchase queue optimization recommendations
     */
    /**
     * Get  optimization recommendations
     */
    public function getOptimizationRecommendations(): array
    {
        $recommendations = [];

        // Analyze queue processing efficiency
        $avgProcessingTime = $this->getAverageProcessingTime();
        if ($avgProcessingTime > 300) { // More than 5 minutes
            $recommendations[] = [
                'type'             => 'performance',
                'priority'         => 'high',
                'title'            => 'High Queue Processing Time',
                'description'      => "Average processing time is {$avgProcessingTime} seconds. Consider optimizing purchase flow.",
                'suggested_action' => 'Review and optimize purchase automation workflow',
            ];
        }

        // Analyze success rates by platform
        $platformMetrics = $this->getPlatformMetrics();
        foreach ($platformMetrics as $platform => $metrics) {
            if ($metrics['success_rate'] < 70) {
                $recommendations[] = [
                    'type'             => 'platform_performance',
                    'priority'         => 'medium',
                    'title'            => "Low Success Rate for {$platform}",
                    'description'      => "Success rate for {$platform} is {$metrics['success_rate']}%",
                    'suggested_action' => "Review and improve {$platform} purchase implementation",
                ];
            }
        }

        // Check for high retry rates
        $highRetryAttempts = PurchaseAttempt::where('retry_count', '>', 2)
            ->whereDate('created_at', today())
            ->count();

        if ($highRetryAttempts > 10) {
            $recommendations[] = [
                'type'             => 'reliability',
                'priority'         => 'medium',
                'title'            => 'High Retry Rate',
                'description'      => "{$highRetryAttempts} attempts required multiple retries today",
                'suggested_action' => 'Investigate common failure points and improve error handling',
            ];
        }

        return $recommendations;
    }

    /**
     * Generate performance report
     */
    /**
     * GeneratePerformanceReport
     */
    public function generatePerformanceReport(string $period = '7d'): array
    {
        $analytics = $this->getPurchaseAnalytics($period);
        $trends = $this->getSuccessRateTrends($period);
        $recommendations = $this->getOptimizationRecommendations();

        return [
            'report_period'      => $period,
            'generated_at'       => now()->toISOString(),
            'summary'            => $analytics['summary'],
            'performance_trends' => $trends,
            'platform_breakdown' => $analytics['platform_performance'],
            'failure_analysis'   => $analytics['failure_analysis'],
            'recommendations'    => $recommendations,
            'kpis'               => [
                'overall_success_rate'    => $analytics['summary']['success_rate'],
                'average_processing_time' => $this->getAverageProcessingTime(),
                'total_revenue'           => $this->getTotalRevenue($period),
                'cost_per_success'        => $this->getCostPerSuccess($period),
            ],
        ];
    }

    // Helper methods

    /**
     * Get  date range
     */
    private function getDateRange(string $period): array
    {
        $end = Carbon::now();

        $start = match ($period) {
            '1h' => $end->copy()->subHour(),
            '24h', '1d' => $end->copy()->subDay(),
            '7d'    => $end->copy()->subDays(7),
            '30d'   => $end->copy()->subDays(30),
            '90d'   => $end->copy()->subDays(90),
            default => $end->copy()->subDay(),
        };

        return [$start, $end];
    }

    /**
     * Get  summary stats
     */
    private function getSummaryStats(array $dateRange, ?string $platform = NULL): array
    {
        $query = PurchaseAttempt::whereBetween('created_at', $dateRange);

        if ($platform) {
            $query->where('platform', $platform);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_attempts,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_attempts,
            SUM(CASE WHEN status = "in_progress" THEN 1 ELSE 0 END) as in_progress_attempts,
            AVG(CASE WHEN status = "success" THEN total_paid ELSE NULL END) as avg_purchase_amount,
            SUM(CASE WHEN status = "success" THEN total_paid ELSE 0 END) as total_revenue
        ')->first();

        $successRate = $stats->total_attempts > 0
            ? round(($stats->successful_attempts / $stats->total_attempts) * 100, 2)
            : 0;

        return [
            'total_attempts'       => $stats->total_attempts,
            'successful_attempts'  => $stats->successful_attempts,
            'failed_attempts'      => $stats->failed_attempts,
            'in_progress_attempts' => $stats->in_progress_attempts,
            'success_rate'         => $successRate,
            'avg_purchase_amount'  => round($stats->avg_purchase_amount ?? 0, 2),
            'total_revenue'        => round($stats->total_revenue ?? 0, 2),
        ];
    }

    /**
     * Get  success rates
     */
    private function getSuccessRates(array $dateRange, ?string $platform = NULL): array
    {
        $query = PurchaseAttempt::whereBetween('created_at', $dateRange);

        if ($platform) {
            $query->where('platform', $platform);
        }

        return $query->selectRaw('
            platform,
            COUNT(*) as total_attempts,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts,
            ROUND((SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate
        ')
            ->groupBy('platform')
            ->orderByDesc('success_rate')
            ->get()
            ->toArray();
    }

    /**
     * Get  platform performance
     */
    private function getPlatformPerformance(array $dateRange): array
    {
        return PurchaseAttempt::selectRaw('
            platform,
            COUNT(*) as total_attempts,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts,
            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_attempts,
            ROUND((SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
            AVG(CASE WHEN status = "success" THEN total_paid ELSE NULL END) as avg_revenue_per_success,
            AVG(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) 
                ELSE NULL END) as avg_processing_time_seconds
        ')
            ->whereBetween('created_at', $dateRange)
            ->groupBy('platform')
            ->orderByDesc('success_rate')
            ->get()
            ->toArray();
    }

    /**
     * Get  time series data
     */
    private function getTimeSeriesData(array $dateRange, ?string $platform = NULL): array
    {
        $query = PurchaseAttempt::selectRaw('
            DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour,
            COUNT(*) as total_attempts,
            SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts
        ')
            ->whereBetween('created_at', $dateRange);

        if ($platform) {
            $query->where('platform', $platform);
        }

        return $query->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                return [
                    'timestamp'           => $item->hour,
                    'total_attempts'      => $item->total_attempts,
                    'successful_attempts' => $item->successful_attempts,
                    'success_rate'        => $item->total_attempts > 0
                        ? round(($item->successful_attempts / $item->total_attempts) * 100, 2)
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Get  performance metrics
     */
    private function getPerformanceMetrics(array $dateRange, ?string $platform = NULL): array
    {
        $query = PurchaseAttempt::whereBetween('created_at', $dateRange);

        if ($platform) {
            $query->where('platform', $platform);
        }

        $metrics = $query->selectRaw('
            AVG(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) 
                ELSE NULL END) as avg_processing_time,
            MIN(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) 
                ELSE NULL END) as min_processing_time,
            MAX(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) 
                ELSE NULL END) as max_processing_time,
            AVG(retry_count) as avg_retry_count
        ')->first();

        return [
            'avg_processing_time_seconds' => round($metrics->avg_processing_time ?? 0, 2),
            'min_processing_time_seconds' => $metrics->min_processing_time ?? 0,
            'max_processing_time_seconds' => $metrics->max_processing_time ?? 0,
            'avg_retry_count'             => round($metrics->avg_retry_count ?? 0, 2),
        ];
    }

    /**
     * Get  current processing count
     */
    private function getCurrentProcessingCount(): int
    {
        return PurchaseAttempt::where('status', 'in_progress')->count();
    }

    /**
     * Get  queue status
     */
    private function getQueueStatus(): array
    {
        return [
            'queued'        => PurchaseQueue::where('status', 'queued')->count(),
            'processing'    => PurchaseQueue::where('status', 'processing')->count(),
            'high_priority' => PurchaseQueue::whereIn('priority', ['high', 'urgent', 'critical'])
                ->whereIn('status', ['queued', 'processing'])
                ->count(),
            'expired' => PurchaseQueue::where('expires_at', '<', now())->count(),
        ];
    }

    /**
     * Get  recent attempts
     */
    private function getRecentAttempts(int $limit = 10): array
    {
        return PurchaseAttempt::with(['purchaseQueue.scrapedTicket'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id'              => $attempt->id,
                    'uuid'            => $attempt->uuid,
                    'platform'        => $attempt->platform,
                    'status'          => $attempt->status,
                    'attempted_price' => $attempt->attempted_price,
                    'total_paid'      => $attempt->total_paid,
                    'created_at'      => $attempt->created_at->toISOString(),
                    'processing_time' => $attempt->completed_at && $attempt->started_at
                        ? $attempt->started_at->diffInSeconds($attempt->completed_at)
                        : NULL,
                ];
            })
            ->toArray();
    }

    /**
     * Get  recent success rate
     */
    private function getRecentSuccessRate(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        $stats = $this->getSummaryStats($dateRange);

        return $stats['success_rate'];
    }

    /**
     * Get  active alerts
     */
    private function getActiveAlerts(): array
    {
        $alerts = [];

        // Check for high failure rates
        $recentFailureRate = 100 - $this->getRecentSuccessRate('1h');
        if ($recentFailureRate > 50) {
            $alerts[] = [
                'type'      => 'high_failure_rate',
                'severity'  => 'warning',
                'message'   => "High failure rate detected: {$recentFailureRate}% in the last hour",
                'timestamp' => now()->toISOString(),
            ];
        }

        // Check for stuck processes
        $stuckProcesses = PurchaseAttempt::where('status', 'in_progress')
            ->where('started_at', '<', now()->subMinutes(30))
            ->count();

        if ($stuckProcesses > 0) {
            $alerts[] = [
                'type'      => 'stuck_processes',
                'severity'  => 'error',
                'message'   => "{$stuckProcesses} purchase attempts have been processing for over 30 minutes",
                'timestamp' => now()->toISOString(),
            ];
        }

        return $alerts;
    }

    /**
     * Get  system health metrics
     */
    private function getSystemHealthMetrics(): array
    {
        return [
            'database_connection' => $this->checkDatabaseConnection(),
            'queue_worker_status' => $this->checkQueueWorkerStatus(),
            'memory_usage'        => $this->getMemoryUsage(),
            'response_time'       => $this->getAverageResponseTime(),
        ];
    }

    /**
     * Get  platform specific metrics
     */
    private function getPlatformSpecificMetrics(string $platform): array
    {
        $dateRange = $this->getDateRange('7d');

        $stats = PurchaseAttempt::where('platform', $platform)
            ->whereBetween('created_at', $dateRange)
            ->selectRaw('
                COUNT(*) as total_attempts,
                SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) as successful_attempts,
                ROUND((SUM(CASE WHEN status = "success" THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as success_rate,
                AVG(CASE WHEN completed_at IS NOT NULL AND started_at IS NOT NULL 
                    THEN TIMESTAMPDIFF(SECOND, started_at, completed_at) 
                    ELSE NULL END) as avg_processing_time
            ')
            ->first();

        return [
            'platform'            => $platform,
            'total_attempts'      => $stats->total_attempts ?? 0,
            'successful_attempts' => $stats->successful_attempts ?? 0,
            'success_rate'        => $stats->success_rate ?? 0,
            'avg_processing_time' => round($stats->avg_processing_time ?? 0, 2),
        ];
    }

    /**
     * Get  average processing time
     */
    private function getAverageProcessingTime(): float
    {
        $avgTime = PurchaseAttempt::whereNotNull('started_at')
            ->whereNotNull('completed_at')
            ->whereDate('created_at', today())
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_time')
            ->value('avg_time');

        return round($avgTime ?? 0, 2);
    }

    /**
     * Get  total revenue
     */
    private function getTotalRevenue(string $period): float
    {
        $dateRange = $this->getDateRange($period);

        return PurchaseAttempt::where('status', 'success')
            ->whereBetween('created_at', $dateRange)
            ->sum('total_paid') ?? 0;
    }

    /**
     * Get  cost per success
     */
    private function getCostPerSuccess(string $period): float
    {
        $dateRange = $this->getDateRange($period);
        $totalAttempts = PurchaseAttempt::whereBetween('created_at', $dateRange)->count();
        $successfulAttempts = PurchaseAttempt::where('status', 'success')
            ->whereBetween('created_at', $dateRange)
            ->count();

        // Assuming a base cost per attempt (customize based on your infrastructure costs)
        $costPerAttempt = 0.10; // $0.10 per attempt
        $totalCost = $totalAttempts * $costPerAttempt;

        return $successfulAttempts > 0 ? round($totalCost / $successfulAttempts, 2) : 0;
    }

    /**
     * CheckDatabaseConnection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * CheckQueueWorkerStatus
     */
    private function checkQueueWorkerStatus(): string
    {
        // This would need to be implemented based on your queue setup
        // For now, return a placeholder
        return 'active';
    }

    /**
     * Get  memory usage
     */
    private function getMemoryUsage(): array
    {
        return [
            'used_mb' => round(memory_get_usage(TRUE) / 1024 / 1024, 2),
            'peak_mb' => round(memory_get_peak_usage(TRUE) / 1024 / 1024, 2),
        ];
    }

    /**
     * Get  average response time
     */
    private function getAverageResponseTime(): float
    {
        // This would typically be measured by application performance monitoring
        // For now, return a placeholder
        return 0.15; // 150ms
    }
}
