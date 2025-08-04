<?php

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketPriceHistory;
use App\Models\PriceVolatilityAnalytics;
use App\Models\UserPreference;
use App\Models\AlertEscalation;
use App\Models\UserNotificationSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

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
    public function getPriceTrendAnalysis($filters = [])
    {
        $cacheKey = 'analytics:price_trends:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $query = TicketPriceHistory::query()
                ->with(['ticket'])
                ->whereBetween('recorded_at', [
                    $filters['start_date'] ?? Carbon::now()->subDays(30),
                    $filters['end_date'] ?? Carbon::now()
                ]);
            
            if (!empty($filters['platforms'])) {
                $query->whereHas('ticket', function($q) use ($filters) {
                    $q->whereIn('platform', $filters['platforms']);
                });
            }
            
            if (!empty($filters['categories'])) {
                $query->whereHas('ticket', function($q) use ($filters) {
                    $q->whereIn('category', $filters['categories']);
                });
            }
            
            $priceData = $query->get();
            
            return [
                'overview' => $this->calculatePriceTrendOverview($priceData),
                'daily_trends' => $this->calculateDailyPriceTrends($priceData),
                'platform_comparison' => $this->calculatePlatformPriceComparison($priceData),
                'volatility_analysis' => $this->calculatePriceVolatility($priceData),
                'prediction_insights' => $this->generatePricePredictionInsights($priceData),
                'anomaly_detection' => $this->detectPriceAnomalies($priceData),
                'recommendations' => $this->generatePriceTrendRecommendations($priceData)
            ];
        });
    }
    
    /**
     * Analyze demand patterns with ML insights
     */
    public function getDemandPatternAnalysis($filters = [])
    {
        $cacheKey = 'analytics:demand_patterns:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            // Aggregate ticket viewing, alert creation, and purchase data
            $demandData = $this->aggregateDemandMetrics($filters);
            
            return [
                'demand_overview' => $this->calculateDemandOverview($demandData),
                'temporal_patterns' => $this->analyzTemporalDemandPatterns($demandData),
                'event_type_analysis' => $this->analyzeEventTypeDemand($demandData),
                'geographic_patterns' => $this->analyzeGeographicDemand($demandData),
                'seasonal_trends' => $this->analyzeSeasonalDemandTrends($demandData),
                'prediction_model' => $this->buildDemandPredictionModel($demandData),
                'market_saturation' => $this->analyzeMarketSaturation($demandData),
                'recommendations' => $this->generateDemandRecommendations($demandData)
            ];
        });
    }
    
    /**
     * Generate success rate optimization recommendations
     */
    public function getSuccessRateOptimization($filters = [])
    {
        $cacheKey = 'analytics:success_optimization:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $alertData = $this->gatherAlertSuccessData($filters);
            $userEngagementData = $this->gatherUserEngagementData($filters);
            $channelPerformanceData = $this->gatherChannelPerformanceData($filters);
            
            return [
                'current_performance' => $this->calculateCurrentSuccessRates($alertData),
                'channel_optimization' => $this->analyzeChannelOptimization($channelPerformanceData),
                'timing_optimization' => $this->analyzeOptimalTiming($alertData),
                'content_optimization' => $this->analyzeContentOptimization($alertData),
                'user_segmentation' => $this->analyzeUserSegmentPerformance($userEngagementData),
                'a_b_test_suggestions' => $this->generateABTestSuggestions($alertData),
                'predictive_scoring' => $this->calculatePredictiveSuccessScoring($alertData),
                'improvement_roadmap' => $this->generateImprovementRoadmap($alertData),
                'roi_analysis' => $this->calculateROIAnalysis($alertData)
            ];
        });
    }
    
    /**
     * Comprehensive platform performance comparison
     */
    public function getPlatformPerformanceComparison($filters = [])
    {
        $cacheKey = 'analytics:platform_performance:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $platforms = $this->getAllActivePlatforms();
            $performanceData = [];
            
            foreach ($platforms as $platform) {
                $performanceData[$platform] = $this->analyzePlatformPerformance($platform, $filters);
            }
            
            return [
                'platform_rankings' => $this->calculatePlatformRankings($performanceData),
                'performance_metrics' => $performanceData,
                'reliability_analysis' => $this->analyzePlatformReliability($performanceData),
                'user_preference_analysis' => $this->analyzePlatformUserPreferences($performanceData),
                'market_share_analysis' => $this->analyzePlatformMarketShare($performanceData),
                'competitive_analysis' => $this->generateCompetitiveAnalysis($performanceData),
                'trend_analysis' => $this->analyzePlatformTrends($performanceData),
                'recommendations' => $this->generatePlatformRecommendations($performanceData)
            ];
        });
    }
    
    /**
     * Calculate price volatility metrics
     */
    public function getPriceVolatilityMetrics(int $ticketId, string $date): ?array
    {
        $analytics = PriceVolatilityAnalytics::calculateForTicket($ticketId, $date);
        return $analytics ? $analytics->toArray() : null;
    }

    /**
     * Real-time dashboard metrics
     */
    public function getRealTimeDashboardMetrics()
    {
        return [
            'live_metrics' => $this->getLiveMetrics(),
            'system_health' => $this->getSystemHealthMetrics(),
            'active_alerts' => $this->getActiveAlertsMetrics(),
            'user_activity' => $this->getUserActivityMetrics(),
            'performance_indicators' => $this->getKeyPerformanceIndicators(),
            'alerts_summary' => $this->getAlertsSummary()
        ];
    }
    
    // Private helper methods for price trend analysis
    
    private function calculatePriceTrendOverview($priceData)
    {
        $totalRecords = $priceData->count();
        if ($totalRecords === 0) return [];
        
        $prices = $priceData->pluck('price');
        $avgPrice = $prices->avg();
        $medianPrice = $prices->median();
        $priceStdDev = $this->calculateStandardDeviation($prices->toArray());
        
        $priceChanges = $priceData->groupBy('ticket_id')->map(function($ticketPrices) {
            $sorted = $ticketPrices->sortBy('recorded_at');
            if ($sorted->count() < 2) return 0;
            
            $first = $sorted->first()->price;
            $last = $sorted->last()->price;
            return (($last - $first) / $first) * 100;
        })->filter();
        
        return [
            'total_tickets_tracked' => $totalRecords,
            'average_price' => round($avgPrice, 2),
            'median_price' => round($medianPrice, 2),
            'price_volatility' => round($priceStdDev / $avgPrice * 100, 2),
            'average_price_change' => round($priceChanges->avg(), 2),
            'price_increase_frequency' => round($priceChanges->filter(fn($change) => $change > 0)->count() / $priceChanges->count() * 100, 2),
            'significant_drops' => $priceChanges->filter(fn($change) => $change < -20)->count(),
            'significant_increases' => $priceChanges->filter(fn($change) => $change > 20)->count()
        ];
    }
    
    private function calculateDailyPriceTrends($priceData)
    {
        return $priceData->groupBy(function($record) {
            return $record->recorded_at->format('Y-m-d');
        })->map(function($dayData, $date) {
            $prices = $dayData->pluck('price');
            return [
                'date' => $date,
                'avg_price' => round($prices->avg(), 2),
                'min_price' => $prices->min(),
                'max_price' => $prices->max(),
                'price_range' => $prices->max() - $prices->min(),
                'ticket_count' => $dayData->count(),
                'volatility' => $this->calculateDayVolatility($prices)
            ];
        })->values();
    }
    
    private function calculatePlatformPriceComparison($priceData)
    {
        return $priceData->groupBy('ticket.platform')->map(function($platformData, $platform) {
            $prices = $platformData->pluck('price');
            $priceChanges = $this->calculatePriceChangesForPlatform($platformData);
            
            return [
                'platform' => $platform,
                'avg_price' => round($prices->avg(), 2),
                'median_price' => round($prices->median(), 2),
                'price_range' => $prices->max() - $prices->min(),
                'avg_price_change' => round($priceChanges->avg(), 2),
                'reliability_score' => $this->calculatePlatformReliabilityScore($platformData),
                'update_frequency' => $this->calculateUpdateFrequency($platformData)
            ];
        });
    }
    
    private function calculatePriceVolatility($priceData)
    {
        return $priceData->groupBy('ticket_id')->map(function($ticketPrices, $ticketId) {
            $prices = $ticketPrices->sortBy('recorded_at')->pluck('price');
            if ($prices->count() < 2) return null;
            
            $returns = [];
            for ($i = 1; $i < $prices->count(); $i++) {
                $returns[] = log($prices[$i] / $prices[$i-1]);
            }
            
            return [
                'ticket_id' => $ticketId,
                'volatility' => $this->calculateStandardDeviation($returns),
                'max_single_change' => max(array_map('abs', $returns)) * 100,
                'price_stability' => $this->calculatePriceStability($prices)
            ];
        })->filter()->values();
    }
    
    private function generatePricePredictionInsights($priceData)
    {
        $insights = [];
        
        // Trending patterns
        $trendingUp = $priceData->filter(function($record) {
            return $this->isPriceTrendingUp($record->ticket_id);
        })->count();
        
        $trendingDown = $priceData->filter(function($record) {
            return $this->isPriceTrendingDown($record->ticket_id);
        })->count();
        
        $insights['trending_patterns'] = [
            'trending_up' => $trendingUp,
            'trending_down' => $trendingDown,
            'stable' => $priceData->count() - $trendingUp - $trendingDown
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
            if ($prices->count() < 3) continue;
            
            $mean = $prices->avg();
            $stdDev = $this->calculateStandardDeviation($prices->toArray());
            $threshold = 2; // 2 standard deviations
            
            foreach ($ticketPrices as $record) {
                $zScore = abs(($record->price - $mean) / $stdDev);
                if ($zScore > $threshold) {
                    $anomalies[] = [
                        'ticket_id' => $ticketId,
                        'price' => $record->price,
                        'expected_range' => [$mean - $stdDev, $mean + $stdDev],
                        'severity' => $zScore > 3 ? 'high' : 'medium',
                        'recorded_at' => $record->recorded_at,
                        'z_score' => round($zScore, 2)
                    ];
                }
            }
        }
        
        return collect($anomalies)->sortByDesc('z_score')->take(20)->values();
    }
    
    private function generatePriceTrendRecommendations($priceData)
    {
        $recommendations = [];
        
        // High volatility tickets
        $highVolatilityTickets = $this->identifyHighVolatilityTickets($priceData);
        if ($highVolatilityTickets->count() > 0) {
            $recommendations[] = [
                'type' => 'monitoring',
                'priority' => 'high',
                'title' => 'Monitor High Volatility Tickets',
                'description' => "Found {$highVolatilityTickets->count()} tickets with high price volatility",
                'action' => 'Increase monitoring frequency for these tickets',
                'expected_impact' => 'Better alert timing and user satisfaction'
            ];
        }
        
        // Platform-specific recommendations
        $platformAnalysis = $this->calculatePlatformPriceComparison($priceData);
        $unreliablePlatforms = $platformAnalysis->filter(function($platform) {
            return $platform['reliability_score'] < 0.8;
        });
        
        if ($unreliablePlatforms->count() > 0) {
            $recommendations[] = [
                'type' => 'platform_optimization',
                'priority' => 'medium',
                'title' => 'Improve Platform Reliability',
                'description' => "Some platforms show inconsistent price updates",
                'action' => 'Implement additional validation for price data',
                'expected_impact' => 'More accurate price alerts'
            ];
        }
        
        return $recommendations;
    }
    
    // Helper methods for demand pattern analysis
    
    private function aggregateDemandMetrics($filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();
        
        return [
            'ticket_views' => $this->getTicketViewMetrics($startDate, $endDate),
            'alert_creations' => $this->getAlertCreationMetrics($startDate, $endDate),
            'user_engagement' => $this->getUserEngagementMetrics($startDate, $endDate),
            'conversion_data' => $this->getConversionMetrics($startDate, $endDate)
        ];
    }
    
    private function calculateDemandOverview($demandData)
    {
        return [
            'total_demand_score' => $this->calculateOverallDemandScore($demandData),
            'demand_growth_rate' => $this->calculateDemandGrowthRate($demandData),
            'peak_demand_periods' => $this->identifyPeakDemandPeriods($demandData),
            'demand_distribution' => $this->analyzeDemandDistribution($demandData)
        ];
    }
    
    private function analyzTemporalDemandPatterns($demandData)
    {
        return [
            'hourly_patterns' => $this->analyzeHourlyDemandPatterns($demandData),
            'daily_patterns' => $this->analyzeDailyDemandPatterns($demandData),
            'weekly_patterns' => $this->analyzeWeeklyDemandPatterns($demandData),
            'monthly_patterns' => $this->analyzeMonthlyDemandPatterns($demandData)
        ];
    }
    
    // Success Rate Optimization Methods
    
    private function calculateCurrentSuccessRates($alertData)
    {
        $totalAlerts = $alertData->count();
        if ($totalAlerts === 0) return [];
        
        $acknowledgedAlerts = $alertData->where('acknowledged', true)->count();
        $convertedAlerts = $alertData->where('converted', true)->count();
        $deliveredAlerts = $alertData->where('delivered', true)->count();
        
        return [
            'delivery_rate' => round(($deliveredAlerts / $totalAlerts) * 100, 2),
            'acknowledgment_rate' => round(($acknowledgedAlerts / $totalAlerts) * 100, 2),
            'conversion_rate' => round(($convertedAlerts / $totalAlerts) * 100, 2),
            'overall_success_rate' => round(($convertedAlerts / $deliveredAlerts) * 100, 2),
            'engagement_score' => $this->calculateEngagementScore($alertData)
        ];
    }
    
    private function analyzeChannelOptimization($channelData)
    {
        return $channelData->map(function($channel, $channelName) {
            return [
                'channel' => $channelName,
                'performance_score' => $this->calculateChannelPerformanceScore($channel),
                'optimization_potential' => $this->identifyChannelOptimizationPotential($channel),
                'recommended_actions' => $this->generateChannelRecommendations($channel)
            ];
        });
    }
    
    private function analyzeOptimalTiming($alertData)
    {
        $timingAnalysis = $alertData->groupBy(function($alert) {
            return $alert->created_at->hour;
        })->map(function($hourData, $hour) {
            return [
                'hour' => $hour,
                'total_alerts' => $hourData->count(),
                'success_rate' => $this->calculateHourlySuccessRate($hourData),
                'engagement_rate' => $this->calculateHourlyEngagementRate($hourData)
            ];
        });
        
        $optimalHours = $timingAnalysis->sortByDesc('success_rate')->take(3);
        
        return [
            'hourly_performance' => $timingAnalysis->values(),
            'optimal_hours' => $optimalHours->values(),
            'timing_recommendations' => $this->generateTimingRecommendations($timingAnalysis)
        ];
    }
    
    // Platform Performance Comparison Methods
    
    private function getAllActivePlatforms()
    {
        return ScrapedTicket::distinct()->pluck('platform')->filter()->values();
    }
    
    private function analyzePlatformPerformance($platform, $filters)
    {
        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();
        
        $tickets = ScrapedTicket::where('platform', $platform)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();
        
        $alerts = TicketAlert::whereHas('scrapedTicket', function($q) use ($platform) {
            $q->where('platform', $platform);
        })->whereBetween('created_at', [$startDate, $endDate])->get();
        
        return [
            'ticket_count' => $tickets->count(),
            'alert_count' => $alerts->count(),
            'avg_price' => $tickets->avg('price'),
            'price_range' => $tickets->max('price') - $tickets->min('price'),
            'availability_rate' => $this->calculateAvailabilityRate($tickets),
            'data_quality_score' => $this->calculateDataQualityScore($tickets),
            'user_preference_score' => $this->calculateUserPreferenceScore($platform),
            'conversion_rate' => $this->calculatePlatformConversionRate($alerts),
            'reliability_metrics' => $this->calculatePlatformReliabilityMetrics($platform)
        ];
    }
    
    private function calculatePlatformRankings($performanceData)
    {
        $rankings = collect($performanceData)->map(function($data, $platform) {
            return [
                'platform' => $platform,
                'overall_score' => $this->calculateOverallPlatformScore($data),
                'strengths' => $this->identifyPlatformStrengths($data),
                'weaknesses' => $this->identifyPlatformWeaknesses($data)
            ];
        })->sortByDesc('overall_score')->values();
        
        return $rankings;
    }
    
    // Real-time metrics methods
    
    private function getLiveMetrics()
    {
        return [
            'active_users' => $this->getActiveUsersCount(),
            'alerts_today' => $this->getAlertsToday(),
            'success_rate_today' => $this->getTodaySuccessRate(),
            'system_load' => $this->getSystemLoadMetrics()
        ];
    }
    
    private function getSystemHealthMetrics()
    {
        return [
            'api_response_time' => $this->getAverageApiResponseTime(),
            'error_rate' => $this->getCurrentErrorRate(),
            'queue_health' => $this->getQueueHealthStatus(),
            'database_performance' => $this->getDatabasePerformanceMetrics()
        ];
    }
    
    // Utility helper methods
    
    private function calculateStandardDeviation($values)
    {
        if (count($values) < 2) return 0;
        
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        $variance = array_sum($squaredDifferences) / count($values);
        return sqrt($variance);
    }
    
    private function calculateEngagementScore($alertData)
    {
        if ($alertData->count() === 0) return 0;
        
        $weights = [
            'acknowledged' => 0.3,
            'clicked' => 0.4,
            'converted' => 0.3
        ];
        
        $acknowledgedRate = $alertData->where('acknowledged', true)->count() / $alertData->count();
        $clickedRate = $alertData->where('clicked', true)->count() / $alertData->count();
        $convertedRate = $alertData->where('converted', true)->count() / $alertData->count();
        
        return round(
            ($acknowledgedRate * $weights['acknowledged'] +
             $clickedRate * $weights['clicked'] +
             $convertedRate * $weights['converted']) * 100,
            2
        );
    }
    
    /**
     * Export analytics data for external analysis
     */
    public function exportAnalyticsData($type, $filters = [])
    {
        switch ($type) {
            case 'price_trends':
                return $this->exportPriceTrendData($filters);
            case 'demand_patterns':
                return $this->exportDemandPatternData($filters);
            case 'success_metrics':
                return $this->exportSuccessMetricsData($filters);
            case 'platform_comparison':
                return $this->exportPlatformComparisonData($filters);
            default:
                throw new \InvalidArgumentException("Invalid export type: {$type}");
        }
    }
    
    private function exportPriceTrendData($filters)
    {
        // Implementation for exporting price trend data
        return $this->getPriceTrendAnalysis($filters);
    }
    
    private function exportDemandPatternData($filters)
    {
        // Implementation for exporting demand pattern data
        return $this->getDemandPatternAnalysis($filters);
    }
}
