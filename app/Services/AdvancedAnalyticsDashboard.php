<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PriceVolatilityAnalytics;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketPriceHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

use function count;

class AdvancedAnalyticsDashboard
{
    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        // Constructor can be empty since we're using static methods
    }

    /**
     * Get comprehensive price trend analysis
     */
    /**
     * Get  price trend analysis
     */
    public function getPriceTrendAnalysis(): float
    {
        $cacheKey = 'analytics:price_trends:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $query = TicketPriceHistory::query()
                ->with(['ticket'])
                ->whereBetween('recorded_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30),
                    $filters['end_date'] ?? Carbon::now(),
                ]);

            if (! empty($filters['platforms'])) {
                $query->whereHas('ticket', function ($q) use ($filters): void {
                    $q->whereIn('platform', $filters['platforms']);
                });
            }

            if (! empty($filters['categories'])) {
                $query->whereHas('ticket', function ($q) use ($filters): void {
                    $q->whereIn('category', $filters['categories']);
                });
            }

            $priceData = $query->get();

            return [
                'overview'            => $this->calculatePriceTrendOverview($priceData),
                'daily_trends'        => $this->calculateDailyPriceTrends($priceData),
                'platform_comparison' => $this->calculatePlatformPriceComparison($priceData),
                'volatility_analysis' => $this->calculatePriceVolatility($priceData),
                'prediction_insights' => $this->generatePricePredictionInsights($priceData),
                'anomaly_detection'   => $this->detectPriceAnomalies($priceData),
                'recommendations'     => $this->generatePriceTrendRecommendations($priceData),
            ];
        });
    }

    /**
     * Analyze demand patterns with ML insights
     *
     * @param mixed $filters
     */
    public function getDemandPatternAnalysis(array $filters = [])
    {
        $cacheKey = 'analytics:demand_patterns:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            // Aggregate ticket viewing, alert creation, and purchase data
            $demandData = $this->aggregateDemandMetrics($filters);

            return [
                'demand_overview'     => $this->calculateDemandOverview($demandData),
                'temporal_patterns'   => $this->analyzTemporalDemandPatterns($demandData),
                'event_type_analysis' => $this->analyzeEventTypeDemand($demandData),
                'geographic_patterns' => $this->analyzeGeographicDemand($demandData),
                'seasonal_trends'     => $this->analyzeSeasonalDemandTrends($demandData),
                'prediction_model'    => $this->buildDemandPredictionModel($demandData),
                'market_saturation'   => $this->analyzeMarketSaturation($demandData),
                'recommendations'     => $this->generateDemandRecommendations($demandData),
            ];
        });
    }

    /**
     * Generate success rate optimization recommendations
     *
     * @param mixed $filters
     */
    /**
     * Get  success rate optimization
     */
    public function getSuccessRateOptimization(array $filters = []): float
    {
        $cacheKey = 'analytics:success_optimization:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $alertData = $this->gatherAlertSuccessData($filters);
            $userEngagementData = $this->gatherUserEngagementData($filters);
            $channelPerformanceData = $this->gatherChannelPerformanceData($filters);

            return [
                'current_performance'  => $this->calculateCurrentSuccessRates($alertData),
                'channel_optimization' => $this->analyzeChannelOptimization($channelPerformanceData),
                'timing_optimization'  => $this->analyzeOptimalTiming($alertData),
                'content_optimization' => $this->analyzeContentOptimization($alertData),
                'user_segmentation'    => $this->analyzeUserSegmentPerformance($userEngagementData),
                'a_b_test_suggestions' => $this->generateABTestSuggestions($alertData),
                'predictive_scoring'   => $this->calculatePredictiveSuccessScoring($alertData),
                'improvement_roadmap'  => $this->generateImprovementRoadmap($alertData),
                'roi_analysis'         => $this->calculateROIAnalysis($alertData),
            ];
        });
    }

    /**
     * Comprehensive platform performance comparison
     *
     * @param mixed $filters
     */
    public function getPlatformPerformanceComparison(array $filters = [])
    {
        $cacheKey = 'analytics:platform_performance:' . md5(serialize($filters));

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($filters): array {
            $platforms = $this->getAllActivePlatforms();
            $performanceData = [];

            foreach ($platforms as $platform) {
                $performanceData[$platform] = $this->analyzePlatformPerformance($platform, $filters);
            }

            return [
                'platform_rankings'        => $this->calculatePlatformRankings($performanceData),
                'performance_metrics'      => $performanceData,
                'reliability_analysis'     => $this->analyzePlatformReliability($performanceData),
                'user_preference_analysis' => $this->analyzePlatformUserPreferences($performanceData),
                'market_share_analysis'    => $this->analyzePlatformMarketShare($performanceData),
                'competitive_analysis'     => $this->generateCompetitiveAnalysis($performanceData),
                'trend_analysis'           => $this->analyzePlatformTrends($performanceData),
                'recommendations'          => $this->generatePlatformRecommendations($performanceData),
            ];
        });
    }

    /**
     * Calculate price volatility metrics
     */
    /**
     * Get  price volatility metrics
     */
    public function getPriceVolatilityMetrics(int $ticketId, string $date): ?array
    {
        $analytics = PriceVolatilityAnalytics::calculateForTicket($ticketId, $date);

        return $analytics instanceof PriceVolatilityAnalytics ? $analytics->toArray() : NULL;
    }

    /**
     * Real-time dashboard metrics
     */
    public function getRealTimeDashboardMetrics(): array
    {
        return [
            'live_metrics'           => $this->getLiveMetrics(),
            'system_health'          => $this->getSystemHealthMetrics(),
            'active_alerts'          => $this->getActiveAlertsMetrics(),
            'user_activity'          => $this->getUserActivityMetrics(),
            'performance_indicators' => $this->getKeyPerformanceIndicators(),
            'alerts_summary'         => $this->getAlertsSummary(),
        ];
    }

    /**
     * Export analytics data for external analysis
     *
     * @param mixed $type
     * @param mixed $filters
     */
    public function exportAnalyticsData(string $type, array $filters = [])
    {
        return match ($type) {
            'price_trends'        => $this->exportPriceTrendData(),
            'demand_patterns'     => $this->exportDemandPatternData($filters),
            'success_metrics'     => $this->exportSuccessMetricsData($filters),
            'platform_comparison' => $this->exportPlatformComparisonData($filters),
            default               => throw new InvalidArgumentException("Invalid export type: {$type}"),
        };
    }

    // Private helper methods for price trend analysis

    private function calculatePriceTrendOverview($priceData): array
    {
        $totalRecords = $priceData->count();
        if ($totalRecords === 0) {
            return [];
        }

        $prices = $priceData->pluck('price');
        $avgPrice = $prices->avg();
        $medianPrice = $prices->median();
        $priceStdDev = $this->calculateStandardDeviation($prices->toArray());

        $priceChanges = $priceData->groupBy('ticket_id')->map(function ($ticketPrices): int|float {
            $sorted = $ticketPrices->sortBy('recorded_at');
            if ($sorted->count() < 2) {
                return 0;
            }

            $first = $sorted->first()->price;
            $last = $sorted->last()->price;

            return (($last - $first) / $first) * 100;
        })->filter();

        return [
            'total_tickets_tracked'    => $totalRecords,
            'average_price'            => round($avgPrice, 2),
            'median_price'             => round($medianPrice, 2),
            'price_volatility'         => round($priceStdDev / $avgPrice * 100, 2),
            'average_price_change'     => round($priceChanges->avg(), 2),
            'price_increase_frequency' => round($priceChanges->filter(fn ($change): bool => $change > 0)->count() / $priceChanges->count() * 100, 2),
            'significant_drops'        => $priceChanges->filter(fn ($change): bool => $change < -20)->count(),
            'significant_increases'    => $priceChanges->filter(fn ($change): bool => $change > 20)->count(),
        ];
    }

    private function calculateDailyPriceTrends($priceData)
    {
        return $priceData->groupBy(fn ($record) => $record->recorded_at->format('Y-m-d'))->map(function ($dayData, $date): array {
            $prices = $dayData->pluck('price');

            return [
                'date'         => $date,
                'avg_price'    => round($prices->avg(), 2),
                'min_price'    => $prices->min(),
                'max_price'    => $prices->max(),
                'price_range'  => $prices->max() - $prices->min(),
                'ticket_count' => $dayData->count(),
                'volatility'   => $this->calculateDayVolatility($prices),
            ];
        })->values();
    }

    private function calculatePlatformPriceComparison($priceData)
    {
        return $priceData->groupBy('ticket.platform')->map(function ($platformData, $platform): array {
            $prices = $platformData->pluck('price');
            $priceChanges = $this->calculatePriceChangesForPlatform($platformData);

            return [
                'platform'          => $platform,
                'avg_price'         => round($prices->avg(), 2),
                'median_price'      => round($prices->median(), 2),
                'price_range'       => $prices->max() - $prices->min(),
                'avg_price_change'  => round($priceChanges->avg(), 2),
                'reliability_score' => $this->calculatePlatformReliabilityScore($platformData),
                'update_frequency'  => $this->calculateUpdateFrequency($platformData),
            ];
        });
    }

    private function calculatePriceVolatility($priceData)
    {
        return $priceData->groupBy('ticket_id')->map(function ($ticketPrices, $ticketId) {
            $prices = $ticketPrices->sortBy('recorded_at')->pluck('price');
            if ($prices->count() < 2) {
                return;
            }

            $returns = [];
            for ($i = 1; $i < $prices->count(); $i++) {
                $returns[] = log($prices[$i] / $prices[$i - 1]);
            }

            return [
                'ticket_id'         => $ticketId,
                'volatility'        => $this->calculateStandardDeviation($returns),
                'max_single_change' => max(array_map('abs', $returns)) * 100,
                'price_stability'   => $this->calculatePriceStability($prices),
            ];
        })->filter()->values();
    }

    private function generatePricePredictionInsights($priceData): array
    {
        $insights = [];

        // Trending patterns
        $trendingUp = $priceData->filter(fn ($record) => $this->isPriceTrendingUp($record->ticket_id))->count();

        $trendingDown = $priceData->filter(fn ($record) => $this->isPriceTrendingDown($record->ticket_id))->count();

        $insights['trending_patterns'] = [
            'trending_up'   => $trendingUp,
            'trending_down' => $trendingDown,
            'stable'        => $priceData->count() - $trendingUp - $trendingDown,
        ];

        // Seasonal patterns
        $insights['seasonal_patterns'] = $this->analyzeSeasonalPricePatterns($priceData);

        // Event-based patterns
        $insights['event_patterns'] = $this->analyzeEventBasedPricePatterns($priceData);

        return $insights;
    }

    private function detectPriceAnomalies($priceData)
    {
        $anomalies = [];

        foreach ($priceData->groupBy('ticket_id') as $ticketId => $ticketPrices) {
            $prices = $ticketPrices->sortBy('recorded_at')->pluck('price');
            if ($prices->count() < 3) {
                continue;
            }

            $mean = $prices->avg();
            $stdDev = $this->calculateStandardDeviation($prices->toArray());
            $threshold = 2; // 2 standard deviations

            foreach ($ticketPrices as $record) {
                $zScore = abs(($record->price - $mean) / $stdDev);
                if ($zScore > $threshold) {
                    $anomalies[] = [
                        'ticket_id'      => $ticketId,
                        'price'          => $record->price,
                        'expected_range' => [$mean - $stdDev, $mean + $stdDev],
                        'severity'       => $zScore > 3 ? 'high' : 'medium',
                        'recorded_at'    => $record->recorded_at,
                        'z_score'        => round($zScore, 2),
                    ];
                }
            }
        }

        return collect($anomalies)->sortByDesc('z_score')->take(20)->values();
    }

    /**
     * @param mixed $priceData
     *
     * @return list<(array{type: 'monitoring', priority: 'high', title: 'Monitor High Volatility Tickets', description: non-falsy-string, action: 'Increase monitoring frequency for these tickets', expected_impact: 'Better alert timing and user satisfaction'} | array{type: 'platform_optimization', priority: 'medium', title: 'Improve Platform Reliability', description: 'Some platforms show inconsistent price updates', action: 'Implement additional validation for price data', expected_impact: 'More accurate price alerts'})>
     */
    private function generatePriceTrendRecommendations($priceData): array
    {
        $recommendations = [];

        // High volatility tickets
        $highVolatilityTickets = $this->identifyHighVolatilityTickets($priceData);
        if ($highVolatilityTickets->count() > 0) {
            $recommendations[] = [
                'type'            => 'monitoring',
                'priority'        => 'high',
                'title'           => 'Monitor High Volatility Tickets',
                'description'     => "Found {$highVolatilityTickets->count()} tickets with high price volatility",
                'action'          => 'Increase monitoring frequency for these tickets',
                'expected_impact' => 'Better alert timing and user satisfaction',
            ];
        }

        // Platform-specific recommendations
        $platformAnalysis = $this->calculatePlatformPriceComparison($priceData);
        $unreliablePlatforms = $platformAnalysis->filter(fn ($platform): bool => $platform['reliability_score'] < 0.8);

        if ($unreliablePlatforms->count() > 0) {
            $recommendations[] = [
                'type'            => 'platform_optimization',
                'priority'        => 'medium',
                'title'           => 'Improve Platform Reliability',
                'description'     => 'Some platforms show inconsistent price updates',
                'action'          => 'Implement additional validation for price data',
                'expected_impact' => 'More accurate price alerts',
            ];
        }

        return $recommendations;
    }

    // Helper methods for demand pattern analysis

    private function aggregateDemandMetrics(array $filters): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        return [
            'ticket_views'    => $this->getTicketViewMetrics($startDate, $endDate),
            'alert_creations' => $this->getAlertCreationMetrics($startDate, $endDate),
            'user_engagement' => $this->getUserEngagementMetrics($startDate, $endDate),
            'conversion_data' => $this->getConversionMetrics($startDate, $endDate),
        ];
    }

    private function calculateDemandOverview($demandData): array
    {
        return [
            'total_demand_score'  => $this->calculateOverallDemandScore($demandData),
            'demand_growth_rate'  => $this->calculateDemandGrowthRate($demandData),
            'peak_demand_periods' => $this->identifyPeakDemandPeriods($demandData),
            'demand_distribution' => $this->analyzeDemandDistribution($demandData),
        ];
    }

    private function analyzTemporalDemandPatterns($demandData): array
    {
        return [
            'hourly_patterns'  => $this->analyzeHourlyDemandPatterns($demandData),
            'daily_patterns'   => $this->analyzeDailyDemandPatterns($demandData),
            'weekly_patterns'  => $this->analyzeWeeklyDemandPatterns($demandData),
            'monthly_patterns' => $this->analyzeMonthlyDemandPatterns($demandData),
        ];
    }

    // Success Rate Optimization Methods

    private function calculateCurrentSuccessRates($alertData): array
    {
        $totalAlerts = $alertData->count();
        if ($totalAlerts === 0) {
            return [];
        }

        $acknowledgedAlerts = $alertData->where('acknowledged', TRUE)->count();
        $convertedAlerts = $alertData->where('converted', TRUE)->count();
        $deliveredAlerts = $alertData->where('delivered', TRUE)->count();

        return [
            'delivery_rate'        => round(($deliveredAlerts / $totalAlerts) * 100, 2),
            'acknowledgment_rate'  => round(($acknowledgedAlerts / $totalAlerts) * 100, 2),
            'conversion_rate'      => round(($convertedAlerts / $totalAlerts) * 100, 2),
            'overall_success_rate' => round(($convertedAlerts / $deliveredAlerts) * 100, 2),
            'engagement_score'     => $this->calculateEngagementScore($alertData),
        ];
    }

    private function analyzeChannelOptimization($channelData)
    {
        return $channelData->map(fn ($channel, $channelName): array => [
            'channel'                => $channelName,
            'performance_score'      => $this->calculateChannelPerformanceScore($channel),
            'optimization_potential' => $this->identifyChannelOptimizationPotential($channel),
            'recommended_actions'    => $this->generateChannelRecommendations($channel),
        ]);
    }

    private function analyzeOptimalTiming($alertData): array
    {
        $timingAnalysis = $alertData->groupBy(fn ($alert) => $alert->created_at->hour)->map(fn ($hourData, $hour): array => [
            'hour'            => $hour,
            'total_alerts'    => $hourData->count(),
            'success_rate'    => $this->calculateHourlySuccessRate($hourData),
            'engagement_rate' => $this->calculateHourlyEngagementRate($hourData),
        ]);

        $optimalHours = $timingAnalysis->sortByDesc('success_rate')->take(3);

        return [
            'hourly_performance'     => $timingAnalysis->values(),
            'optimal_hours'          => $optimalHours->values(),
            'timing_recommendations' => $this->generateTimingRecommendations($timingAnalysis),
        ];
    }

    // Platform Performance Comparison Methods

    private function getAllActivePlatforms()
    {
        return ScrapedTicket::distinct()->pluck('platform')->filter()->values();
    }

    private function analyzePlatformPerformance($platform, array $filters): array
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        $tickets = ScrapedTicket::where('platform', $platform)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $alerts = TicketAlert::whereHas('scrapedTicket', function ($q) use ($platform): void {
            $q->where('platform', $platform);
        })->whereBetween('created_at', [$startDate, $endDate])->get();

        return [
            'ticket_count'          => $tickets->count(),
            'alert_count'           => $alerts->count(),
            'avg_price'             => $tickets->avg('price'),
            'price_range'           => $tickets->max('price') - $tickets->min('price'),
            'availability_rate'     => $this->calculateAvailabilityRate($tickets),
            'data_quality_score'    => $this->calculateDataQualityScore($tickets),
            'user_preference_score' => $this->calculateUserPreferenceScore($platform),
            'conversion_rate'       => $this->calculatePlatformConversionRate($alerts),
            'reliability_metrics'   => $this->calculatePlatformReliabilityMetrics($platform),
        ];
    }

    private function calculatePlatformRankings(array $performanceData)
    {
        return collect($performanceData)->map(fn ($data, $platform): array => [
            'platform'      => $platform,
            'overall_score' => $this->calculateOverallPlatformScore($data),
            'strengths'     => $this->identifyPlatformStrengths($data),
            'weaknesses'    => $this->identifyPlatformWeaknesses($data),
        ])->sortByDesc('overall_score')->values();
    }

    // Real-time metrics methods

    private function getLiveMetrics(): array
    {
        return [
            'active_users'       => $this->getActiveUsersCount(),
            'alerts_today'       => $this->getAlertsToday(),
            'success_rate_today' => $this->getTodaySuccessRate(),
            'system_load'        => $this->getSystemLoadMetrics(),
        ];
    }

    private function getSystemHealthMetrics(): array
    {
        return [
            'api_response_time'    => $this->getAverageApiResponseTime(),
            'error_rate'           => $this->getCurrentErrorRate(),
            'queue_health'         => $this->getQueueHealthStatus(),
            'database_performance' => $this->getDatabasePerformanceMetrics(),
        ];
    }

    // Utility helper methods

    private function calculateStandardDeviation($values): int|float
    {
        if (count($values) < 2) {
            return 0;
        }

        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(fn ($value): float|int => ($value - $mean) ** 2, $values);

        $variance = array_sum($squaredDifferences) / count($values);

        return sqrt($variance);
    }

    private function calculateEngagementScore($alertData): int|float
    {
        if ($alertData->count() === 0) {
            return 0;
        }

        $weights = [
            'acknowledged' => 0.3,
            'clicked'      => 0.4,
            'converted'    => 0.3,
        ];

        $acknowledgedRate = $alertData->where('acknowledged', TRUE)->count() / $alertData->count();
        $clickedRate = $alertData->where('clicked', TRUE)->count() / $alertData->count();
        $convertedRate = $alertData->where('converted', TRUE)->count() / $alertData->count();

        return round(
            ($acknowledgedRate * $weights['acknowledged'] +
             $clickedRate * $weights['clicked'] +
             $convertedRate * $weights['converted']) * 100,
            2,
        );
    }

    private function exportPriceTrendData(): float
    {
        // Implementation for exporting price trend data
        return $this->getPriceTrendAnalysis();
    }

    private function exportDemandPatternData(array $filters)
    {
        // Implementation for exporting demand pattern data
        return $this->getDemandPatternAnalysis($filters);
    }
}