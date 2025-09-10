<?php declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

use function array_slice;

class AnalyticsService
{
    private const ANALYTICS_PREFIX = 'analytics:';

    private const EVENTS_KEY = 'events';

    private const METRICS_KEY = 'metrics';

    private const USER_BEHAVIOR_KEY = 'user_behavior';

    private const TICKET_PERFORMANCE_KEY = 'ticket_performance';

    /**
     * Track user events and behavior
     *
     * @param string               $event  Event name to track
     * @param array<string, mixed> $data   Event data and context
     * @param int|null             $userId User ID if authenticated
     */
    /**
     * TrackEvent
     */
    public function trackEvent(string $event, array $data = [], ?int $userId = NULL): void
    {
        try {
            $timestamp = Carbon::now();
            $eventData = [
                'event'      => $event,
                'data'       => $data,
                'user_id'    => $userId,
                'timestamp'  => $timestamp->toISOString(),
                'date'       => $timestamp->format('Y-m-d'),
                'hour'       => $timestamp->format('H'),
                'session_id' => session()->getId(),
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
            ];

            // Store detailed event
            $eventKey = self::ANALYTICS_PREFIX . self::EVENTS_KEY . ':' . $timestamp->format('Y-m-d');
            Redis::lpush($eventKey, json_encode($eventData));
            Redis::expire($eventKey, 2592000); // 30 days retention

            // Update real-time counters
            $this->updateRealTimeCounters($event, $timestamp);

            // Update hourly metrics
            $this->updateHourlyMetrics($event, $timestamp);

            // Track user behavior patterns
            if ($userId) {
                $this->trackUserBehavior($userId, $event, $data);
            }
        } catch (Exception $e) {
            Log::error('Analytics tracking failed', [
                'event' => $event,
                'error' => $e->getMessage(),
                'data'  => $data,
            ]);
        }
    }

    /**
     * Track ticket performance metrics
     *
     * @param int                  $ticketId ID of the ticket
     * @param string               $metric   Metric name to track
     * @param mixed                $value    Metric value (number, string, boolean)
     * @param array<string, mixed> $context  Additional context data
     */
    /**
     * TrackTicketPerformance
     */
    public function trackTicketPerformance(int $ticketId, string $metric, mixed $value, array $context = []): void
    {
        try {
            $timestamp = Carbon::now();
            $performanceData = [
                'ticket_id' => $ticketId,
                'metric'    => $metric,
                'value'     => $value,
                'context'   => $context,
                'timestamp' => $timestamp->toISOString(),
            ];

            // Store ticket performance data
            $performanceKey = self::ANALYTICS_PREFIX . self::TICKET_PERFORMANCE_KEY . ':' . $ticketId;
            Redis::lpush($performanceKey, json_encode($performanceData));
            Redis::expire($performanceKey, 7776000); // 90 days retention

            // Update aggregated metrics
            $this->updateTicketAggregates($ticketId, $metric, $value, $timestamp);
        } catch (Exception $e) {
            Log::error('Ticket performance tracking failed', [
                'ticket_id' => $ticketId,
                'metric'    => $metric,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get real-time dashboard metrics
     *
     * @return array<string, mixed> Real-time metrics data
     */
    /**
     * Get  real time metrics
     */
    public function getRealTimeMetrics(): array
    {
        try {
            $now = Carbon::now();
            $metricsKey = self::ANALYTICS_PREFIX . self::METRICS_KEY . ':realtime';

            return [
                'active_users'            => $this->getActiveUsers(),
                'events_last_hour'        => $this->getEventsLastHour(),
                'popular_events'          => $this->getPopularEvents(),
                'conversion_rate'         => $this->getConversionRate(),
                'avg_response_time'       => $this->getAverageResponseTime(),
                'ticket_views'            => $this->getTicketViews(),
                'search_trends'           => $this->getSearchTrends(),
                'geographic_distribution' => $this->getGeographicDistribution(),
                'device_breakdown'        => $this->getDeviceBreakdown(),
                'revenue_metrics'         => $this->getRevenueMetrics(),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get real-time metrics', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Get user behavior analytics
     *
     * @param int $userId User ID to analyze
     * @param int $days   Number of days to analyze
     *
     * @return array<string, mixed> User behavior analytics data
     */
    /**
     * Get  user behavior analytics
     */
    public function getUserBehaviorAnalytics(int $userId, int $days = 30): array
    {
        try {
            $behaviorKey = self::ANALYTICS_PREFIX . self::USER_BEHAVIOR_KEY . ':' . $userId;
            $behaviorData = Redis::hgetall($behaviorKey);

            return [
                'total_sessions'       => $behaviorData['total_sessions'] ?? 0,
                'total_page_views'     => $behaviorData['total_page_views'] ?? 0,
                'avg_session_duration' => $behaviorData['avg_session_duration'] ?? 0,
                'favorite_sports'      => json_decode($behaviorData['favorite_sports'] ?? '[]', TRUE),
                'search_history'       => json_decode($behaviorData['search_history'] ?? '[]', TRUE),
                'conversion_funnel'    => json_decode($behaviorData['conversion_funnel'] ?? '[]', TRUE),
                'price_sensitivity'    => $behaviorData['price_sensitivity'] ?? 'medium',
                'preferred_times'      => json_decode($behaviorData['preferred_times'] ?? '[]', TRUE),
                'device_preferences'   => json_decode($behaviorData['device_preferences'] ?? '[]', TRUE),
            ];
        } catch (Exception $e) {
            Log::error('Failed to get user behavior analytics', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Get ticket performance report
     *
     * @param int $ticketId Ticket ID to analyze
     *
     * @return array<string, mixed> Performance metrics and report data
     */
    /**
     * Get  ticket performance report
     */
    public function getTicketPerformanceReport(int $ticketId): array
    {
        try {
            $performanceKey = self::ANALYTICS_PREFIX . self::TICKET_PERFORMANCE_KEY . ':' . $ticketId;
            $performanceData = Redis::lrange($performanceKey, 0, 1000);

            $metrics = [
                'views'            => 0,
                'clicks'           => 0,
                'favorites'        => 0,
                'shares'           => 0,
                'purchases'        => 0,
                'avg_time_on_page' => 0,
                'bounce_rate'      => 0,
                'conversion_rate'  => 0,
                'price_changes'    => [],
                'popularity_score' => 0,
            ];

            foreach ($performanceData as $dataJson) {
                $data = json_decode($dataJson, TRUE);
                $metric = $data['metric'] ?? '';
                $value = $data['value'] ?? 0;

                switch ($metric) {
                    case 'view':
                        $metrics['views']++;

                        break;
                    case 'click':
                        $metrics['clicks']++;

                        break;
                    case 'favorite':
                        $metrics['favorites']++;

                        break;
                    case 'share':
                        $metrics['shares']++;

                        break;
                    case 'purchase':
                        $metrics['purchases']++;

                        break;
                    case 'time_on_page':
                        $metrics['avg_time_on_page'] += $value;

                        break;
                    case 'price_change':
                        $metrics['price_changes'][] = [
                            'timestamp' => $data['timestamp'],
                            'old_price' => $data['context']['old_price'] ?? 0,
                            'new_price' => $data['context']['new_price'] ?? 0,
                        ];

                        break;
                }
            }

            // Calculate derived metrics
            if ($metrics['views'] > 0) {
                $metrics['conversion_rate'] = ($metrics['purchases'] / $metrics['views']) * 100;
                $metrics['avg_time_on_page'] /= $metrics['views'];
            }

            $metrics['popularity_score'] = $this->calculatePopularityScore($metrics);

            return $metrics;
        } catch (Exception $e) {
            Log::error('Failed to get ticket performance report', [
                'ticket_id' => $ticketId,
                'error'     => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Generate comprehensive analytics report
     *
     * @param Carbon $startDate Report period start date
     * @param Carbon $endDate   Report period end date
     *
     * @return array<string, mixed> Comprehensive analytics report
     */
    /**
     * GenerateAnalyticsReport
     */
    public function generateAnalyticsReport(Carbon $startDate, Carbon $endDate): array
    {
        try {
            $report = [
                'period' => [
                    'start' => $startDate->toISOString(),
                    'end'   => $endDate->toISOString(),
                    'days'  => $startDate->diffInDays($endDate) + 1,
                ],
                'overview'           => $this->getOverviewMetrics(),
                'user_engagement'    => $this->getUserEngagementMetrics(),
                'ticket_performance' => $this->getTopTicketPerformance(),
                'search_analytics'   => $this->getSearchAnalytics(),
                'conversion_funnel'  => $this->getConversionFunnel(),
                'revenue_analysis'   => $this->getRevenueAnalysis(),
                'trends'             => $this->getTrendAnalysis(),
                'recommendations'    => $this->generateRecommendations(),
            ];

            // Cache the report for 1 hour
            $reportKey = 'analytics_report:' . $startDate->format('Y-m-d') . ':' . $endDate->format('Y-m-d');
            Cache::put($reportKey, $report, 3600);

            return $report;
        } catch (Exception $e) {
            Log::error('Failed to generate analytics report', [
                'start_date' => $startDate->toISOString(),
                'end_date'   => $endDate->toISOString(),
                'error'      => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Private helper methods
     */
    /**
     * UpdateRealTimeCounters
     */
    private function updateRealTimeCounters(string $event, Carbon $timestamp): void
    {
        $counterKey = self::ANALYTICS_PREFIX . 'counters:' . $timestamp->format('Y-m-d:H');
        Redis::hincrby($counterKey, $event, 1);
        Redis::expire($counterKey, 86400); // 24 hours retention
    }

    /**
     * UpdateHourlyMetrics
     */
    private function updateHourlyMetrics(string $event, Carbon $timestamp): void
    {
        $metricsKey = self::ANALYTICS_PREFIX . 'hourly:' . $timestamp->format('Y-m-d:H');
        Redis::hincrby($metricsKey, $event, 1);
        Redis::expire($metricsKey, 2592000); // 30 days retention
    }

    /**
     * TrackUserBehavior
     */
    private function trackUserBehavior(int $userId, string $event, array $data): void
    {
        $behaviorKey = self::ANALYTICS_PREFIX . self::USER_BEHAVIOR_KEY . ':' . $userId;

        // Update behavior counters
        switch ($event) {
            case 'page_view':
                Redis::hincrby($behaviorKey, 'total_page_views', 1);

                break;
            case 'session_start':
                Redis::hincrby($behaviorKey, 'total_sessions', 1);

                break;
            case 'search':
                $this->updateSearchHistory($behaviorKey, $data['query'] ?? '');

                break;
            case 'ticket_view':
                $this->updateSportPreferences($behaviorKey, $data['sport'] ?? '');

                break;
        }

        Redis::expire($behaviorKey, 7776000); // 90 days retention
    }

    /**
     * Update aggregated ticket metrics
     *
     * @param int    $ticketId  Ticket ID
     * @param string $metric    Metric name
     * @param mixed  $value     Metric value
     * @param Carbon $timestamp Timestamp of the metric
     */
    /**
     * UpdateTicketAggregates
     */
    private function updateTicketAggregates(int $ticketId, string $metric, mixed $value, Carbon $timestamp): void
    {
        $aggregateKey = self::ANALYTICS_PREFIX . 'aggregates:tickets:' . $timestamp->format('Y-m-d');
        $ticketMetricKey = $ticketId . ':' . $metric;

        Redis::hincrby($aggregateKey, $ticketMetricKey, is_numeric($value) ? $value : 1);
        Redis::expire($aggregateKey, 2592000); // 30 days retention
    }

    /**
     * Get  active users
     */
    private function getActiveUsers(): int
    {
        $activeKey = self::ANALYTICS_PREFIX . 'active_users';

        return Redis::scard($activeKey);
    }

    /**
     * Get  events last hour
     */
    private function getEventsLastHour(): int
    {
        $hour = Carbon::now()->format('Y-m-d:H');
        $counterKey = self::ANALYTICS_PREFIX . 'counters:' . $hour;
        $events = Redis::hgetall($counterKey);

        return array_sum($events);
    }

    /**
     * Get  popular events
     */
    private function getPopularEvents(): array
    {
        // Implementation for popular events
        return [];
    }

    /**
     * Get  conversion rate
     */
    private function getConversionRate(): float
    {
        // Implementation for conversion rate
        return 0.0;
    }

    /**
     * Get  average response time
     */
    private function getAverageResponseTime(): float
    {
        // Implementation for average response time
        return 0.0;
    }

    /**
     * Get  ticket views
     */
    private function getTicketViews(): int
    {
        // Implementation for ticket views
        return 0;
    }

    /**
     * Get  search trends
     */
    private function getSearchTrends(): array
    {
        // Implementation for search trends
        return [];
    }

    /**
     * Get  geographic distribution
     */
    private function getGeographicDistribution(): array
    {
        // Implementation for geographic distribution
        return [];
    }

    /**
     * Get  device breakdown
     */
    private function getDeviceBreakdown(): array
    {
        // Implementation for device breakdown
        return [];
    }

    /**
     * Get  revenue metrics
     */
    private function getRevenueMetrics(): array
    {
        // Implementation for revenue metrics
        return [];
    }

    /**
     * Update user search history in behavior tracking
     *
     * @param string $behaviorKey Redis key for user behavior data
     * @param string $query       Search query to record
     */
    /**
     * UpdateSearchHistory
     */
    private function updateSearchHistory(string $behaviorKey, string $query): void
    {
        if ($query === '' || $query === '0') {
            return;
        }

        $searchHistory = json_decode(Redis::hget($behaviorKey, 'search_history') ?? '[]', TRUE);
        $searchHistory[] = [
            'query'     => $query,
            'timestamp' => Carbon::now()->toISOString(),
        ];

        // Keep only last 50 searches
        $searchHistory = array_slice($searchHistory, -50);
        Redis::hset($behaviorKey, 'search_history', json_encode($searchHistory));
    }

    /**
     * Update user sport preferences based on viewing behavior
     *
     * @param string $behaviorKey Redis key for user behavior data
     * @param string $sport       Sport name to increment preference for
     */
    /**
     * UpdateSportPreferences
     */
    private function updateSportPreferences(string $behaviorKey, string $sport): void
    {
        if ($sport === '' || $sport === '0') {
            return;
        }

        $favorites = json_decode(Redis::hget($behaviorKey, 'favorite_sports') ?? '{}', TRUE);
        $favorites[$sport] = ($favorites[$sport] ?? 0) + 1;

        // Sort by preference
        arsort($favorites);
        Redis::hset($behaviorKey, 'favorite_sports', json_encode($favorites));
    }

    /**
     * Calculate popularity score based on engagement metrics
     *
     * @param array<string, mixed> $metrics Engagement metrics array
     *
     * @return float Calculated popularity score
     */
    /**
     * CalculatePopularityScore
     */
    private function calculatePopularityScore(array $metrics): float
    {
        $score = 0;
        $score += $metrics['views'] * 1;
        $score += $metrics['clicks'] * 2;
        $score += $metrics['favorites'] * 3;
        $score += $metrics['shares'] * 4;
        $score += $metrics['purchases'] * 10;

        return round($score / 100, 2);
    }

    /**
     * Get  overview metrics
     */
    private function getOverviewMetrics(): array
    {
        // Implementation for overview metrics
        return [
            'total_events'         => 0,
            'unique_users'         => 0,
            'page_views'           => 0,
            'sessions'             => 0,
            'bounce_rate'          => 0,
            'avg_session_duration' => 0,
        ];
    }

    /**
     * Get  user engagement metrics
     */
    private function getUserEngagementMetrics(): array
    {
        // Implementation for user engagement metrics
        return [];
    }

    /**
     * Get  top ticket performance
     */
    private function getTopTicketPerformance(): array
    {
        // Implementation for top ticket performance
        return [];
    }

    /**
     * Get  search analytics
     */
    private function getSearchAnalytics(): array
    {
        // Implementation for search analytics
        return [];
    }

    /**
     * Get  conversion funnel
     */
    private function getConversionFunnel(): array
    {
        // Implementation for conversion funnel
        return [];
    }

    /**
     * Get  revenue analysis
     */
    private function getRevenueAnalysis(): array
    {
        // Implementation for revenue analysis
        return [];
    }

    /**
     * Get  trend analysis
     */
    private function getTrendAnalysis(): array
    {
        // Implementation for trend analysis
        return [];
    }

    /**
     * GenerateRecommendations
     */
    private function generateRecommendations(): array
    {
        // Implementation for generating recommendations
        return [];
    }
}
