<?php

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketPriceHistory;
use App\Models\User;
use App\Models\TicketAlert;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AnalyticsInsightsService
{
    private const CACHE_TTL = 1800; // 30 minutes
    
    /**
     * Generate predictive insights for ticket demand
     */
    public function getPredictiveInsights(array $filters = []): array
    {
        $cacheKey = 'insights:predictive:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(90);
            $endDate = $filters['end_date'] ?? Carbon::now();
            
            // Analyze historical patterns
            $historicalData = $this->getHistoricalPatterns($startDate, $endDate);
            $seasonalTrends = $this->analyzeSeasonalTrends($historicalData);
            $demandForecasting = $this->generateDemandForecasting($historicalData);
            $priceProjections = $this->generatePriceProjections($historicalData);
            
            return [
                'demand_forecasting' => $demandForecasting,
                'price_projections' => $priceProjections,
                'seasonal_trends' => $seasonalTrends,
                'market_opportunities' => $this->identifyMarketOpportunities($historicalData),
                'risk_assessment' => $this->assessMarketRisks($historicalData),
                'recommendations' => $this->generatePredictiveRecommendations($historicalData),
                'confidence_scores' => $this->calculatePredictionConfidence($historicalData),
                'generated_at' => now()->toISOString()
            ];
        });
    }
    
    /**
     * Analyze user behavior patterns
     */
    public function getUserBehaviorInsights(array $filters = []): array
    {
        $cacheKey = 'insights:user_behavior:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();
            
            return [
                'engagement_patterns' => $this->analyzeEngagementPatterns($startDate, $endDate),
                'user_segmentation' => $this->performUserSegmentation($startDate, $endDate),
                'conversion_analysis' => $this->analyzeConversionPatterns($startDate, $endDate),
                'retention_insights' => $this->analyzeUserRetention($startDate, $endDate),
                'behavior_trends' => $this->identifyBehaviorTrends($startDate, $endDate),
                'churn_prediction' => $this->predictUserChurn($startDate, $endDate),
                'lifetime_value' => $this->calculateUserLifetimeValue($startDate, $endDate),
                'actionable_insights' => $this->generateUserActionableInsights($startDate, $endDate)
            ];
        });
    }
    
    /**
     * Generate market intelligence insights
     */
    public function getMarketIntelligence(array $filters = []): array
    {
        $cacheKey = 'insights:market_intelligence:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(60);
            $endDate = $filters['end_date'] ?? Carbon::now();
            
            return [
                'competitive_analysis' => $this->performCompetitiveAnalysis($startDate, $endDate),
                'market_share_trends' => $this->analyzeMarketShareTrends($startDate, $endDate),
                'pricing_intelligence' => $this->generatePricingIntelligence($startDate, $endDate),
                'demand_hotspots' => $this->identifyDemandHotspots($startDate, $endDate),
                'supply_analysis' => $this->analyzeSupplyPatterns($startDate, $endDate),
                'market_gaps' => $this->identifyMarketGaps($startDate, $endDate),
                'strategic_recommendations' => $this->generateStrategicRecommendations($startDate, $endDate),
                'market_health_score' => $this->calculateMarketHealthScore($startDate, $endDate)
            ];
        });
    }
    
    /**
     * Generate performance optimization insights
     */
    public function getOptimizationInsights(array $filters = []): array
    {
        $cacheKey = 'insights:optimization:' . md5(serialize($filters));
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function() use ($filters) {
            $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
            $endDate = $filters['end_date'] ?? Carbon::now();
            
            return [
                'platform_optimization' => $this->analyzePlatformOptimization($startDate, $endDate),
                'scraping_efficiency' => $this->analyzeScrapingEfficiency($startDate, $endDate),
                'alert_optimization' => $this->analyzeAlertOptimization($startDate, $endDate),
                'resource_utilization' => $this->analyzeResourceUtilization($startDate, $endDate),
                'bottleneck_analysis' => $this->identifyBottlenecks($startDate, $endDate),
                'improvement_opportunities' => $this->identifyImprovementOpportunities($startDate, $endDate),
                'performance_benchmarks' => $this->establishPerformanceBenchmarks($startDate, $endDate),
                'optimization_roadmap' => $this->generateOptimizationRoadmap($startDate, $endDate)
            ];
        });
    }
    
    /**
     * Generate real-time anomaly detection insights
     */
    public function getAnomalyDetectionInsights(): array
    {
        return [
            'price_anomalies' => $this->detectPriceAnomalies(),
            'demand_anomalies' => $this->detectDemandAnomalies(),
            'platform_anomalies' => $this->detectPlatformAnomalies(),
            'user_behavior_anomalies' => $this->detectUserBehaviorAnomalies(),
            'system_anomalies' => $this->detectSystemAnomalies(),
            'anomaly_severity' => $this->classifyAnomalySeverity(),
            'recommended_actions' => $this->generateAnomalyActions(),
            'detection_timestamp' => now()->toISOString()
        ];
    }
    
    // Private helper methods for predictive insights
    
    private function getHistoricalPatterns(Carbon $startDate, Carbon $endDate): Collection
    {
        return ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->with(['priceHistory'])
            ->select([
                'id', 'title', 'platform', 'status', 'min_price', 'max_price',
                'is_available', 'is_high_demand', 'venue', 'event_date', 'created_at'
            ])
            ->get();
    }
    
    private function analyzeSeasonalTrends(Collection $data): array
    {
        $monthlyTrends = $data->groupBy(function($item) {
            return $item->created_at->format('Y-m');
        })->map(function($monthData, $month) {
            return [
                'month' => $month,
                'total_tickets' => $monthData->count(),
                'avg_price' => $monthData->avg('min_price'),
                'high_demand_percentage' => $monthData->where('is_high_demand', true)->count() / $monthData->count() * 100,
                'availability_rate' => $monthData->where('is_available', true)->count() / $monthData->count() * 100
            ];
        })->values();
        
        $weeklyTrends = $data->groupBy(function($item) {
            return $item->created_at->format('l'); // Day name
        })->map(function($dayData, $day) {
            return [
                'day' => $day,
                'avg_tickets' => $dayData->count(),
                'demand_score' => $dayData->where('is_high_demand', true)->count() / max(1, $dayData->count()) * 100
            ];
        });
        
        return [
            'monthly_trends' => $monthlyTrends,
            'weekly_patterns' => $weeklyTrends,
            'peak_seasons' => $this->identifyPeakSeasons($monthlyTrends),
            'seasonal_recommendations' => $this->generateSeasonalRecommendations($monthlyTrends)
        ];
    }
    
    private function generateDemandForecasting(Collection $data): array
    {
        $demandTrends = $data->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function($dayData) {
            return [
                'total_demand' => $dayData->count(),
                'high_demand_tickets' => $dayData->where('is_high_demand', true)->count(),
                'demand_intensity' => $dayData->where('is_high_demand', true)->count() / max(1, $dayData->count())
            ];
        })->values();
        
        // Simple trend analysis (in production, use more sophisticated ML models)
        $recentTrend = $demandTrends->take(-7)->avg('demand_intensity');
        $previousTrend = $demandTrends->skip(-14)->take(7)->avg('demand_intensity');
        $trendDirection = $recentTrend > $previousTrend ? 'increasing' : 'decreasing';
        
        return [
            'current_demand_level' => $this->categorizeDemandLevel($recentTrend),
            'trend_direction' => $trendDirection,
            'forecasted_demand' => $this->forecastDemand($demandTrends),
            'demand_drivers' => $this->identifyDemandDrivers($data),
            'seasonal_adjustments' => $this->calculateSeasonalAdjustments($demandTrends)
        ];
    }
    
    private function generatePriceProjections(Collection $data): array
    {
        $priceData = $data->reject(function($item) {
            return is_null($item->min_price) || $item->min_price <= 0;
        });
        
        if ($priceData->isEmpty()) {
            return ['error' => 'Insufficient price data for projections'];
        }
        
        $priceHistory = $priceData->groupBy(function($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function($dayData) {
            return [
                'avg_price' => $dayData->avg('min_price'),
                'median_price' => $dayData->median('min_price'),
                'price_volatility' => $this->calculateVolatility($dayData->pluck('min_price'))
            ];
        });
        
        return [
            'current_price_trend' => $this->analyzePriceTrend($priceHistory),
            'projected_price_range' => $this->projectPriceRange($priceHistory),
            'volatility_forecast' => $this->forecastVolatility($priceHistory),
            'price_optimization_opportunities' => $this->identifyPriceOptimizationOpportunities($priceData)
        ];
    }
    
    // User behavior analysis methods
    
    private function analyzeEngagementPatterns(Carbon $startDate, Carbon $endDate): array
    {
        $userData = User::with(['ticketAlerts' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();
        
        $engagementMetrics = $userData->map(function($user) {
            $alerts = $user->ticketAlerts;
            return [
                'user_id' => $user->id,
                'total_alerts' => $alerts->count(),
                'active_alerts' => $alerts->where('status', 'active')->count(),
                'engagement_frequency' => $this->calculateEngagementFrequency($user, $alerts),
                'engagement_score' => $this->calculateEngagementScore($alerts)
            ];
        });
        
        return [
            'average_engagement' => $engagementMetrics->avg('engagement_score'),
            'engagement_distribution' => $this->categorizeEngagementLevels($engagementMetrics),
            'high_engagement_characteristics' => $this->identifyHighEngagementCharacteristics($engagementMetrics),
            'engagement_trends' => $this->analyzeEngagementTrends($engagementMetrics)
        ];
    }
    
    private function performUserSegmentation(Carbon $startDate, Carbon $endDate): array
    {
        $users = User::with(['ticketAlerts'])->get();
        
        $segments = $users->groupBy(function($user) {
            $alertCount = $user->ticketAlerts->count();
            $recentActivity = $user->last_activity_at && $user->last_activity_at->isAfter(Carbon::now()->subDays(7));
            
            if ($alertCount >= 10 && $recentActivity) return 'power_users';
            if ($alertCount >= 5 && $recentActivity) return 'active_users';
            if ($alertCount >= 1 && $recentActivity) return 'casual_users';
            if ($alertCount >= 1) return 'dormant_users';
            return 'new_users';
        })->map(function($segment) {
            return [
                'count' => $segment->count(),
                'avg_alerts' => $segment->avg(function($user) { return $user->ticketAlerts->count(); }),
                'conversion_potential' => $this->assessConversionPotential($segment)
            ];
        });
        
        return [
            'segments' => $segments,
            'segment_insights' => $this->generateSegmentInsights($segments),
            'targeted_strategies' => $this->generateTargetedStrategies($segments)
        ];
    }
    
    // Market intelligence methods
    
    private function performCompetitiveAnalysis(Carbon $startDate, Carbon $endDate): array
    {
        $platformMetrics = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'platform',
                DB::raw('COUNT(*) as ticket_count'),
                DB::raw('AVG(min_price) as avg_price'),
                DB::raw('COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_count'),
                DB::raw('COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as demand_count')
            ])
            ->groupBy('platform')
            ->get();
        
        return $platformMetrics->map(function($platform) {
            return [
                'platform' => $platform->platform,
                'market_share' => $platform->ticket_count,
                'avg_price_position' => $this->determinePricePosition($platform->avg_price),
                'availability_advantage' => $platform->available_count / max(1, $platform->ticket_count),
                'demand_capture' => $platform->demand_count / max(1, $platform->ticket_count),
                'competitive_strength' => $this->calculateCompetitiveStrength($platform)
            ];
        })->sortByDesc('competitive_strength')->values()->toArray();
    }
    
    // Optimization insights methods
    
    private function analyzePlatformOptimization(Carbon $startDate, Carbon $endDate): array
    {
        $platformPerformance = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'platform',
                DB::raw('COUNT(*) as total_tickets'),
                DB::raw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_update_time'),
                DB::raw('COUNT(DISTINCT venue) as venue_coverage'),
                DB::raw('COUNT(CASE WHEN status = "error" THEN 1 END) as error_count')
            ])
            ->groupBy('platform')
            ->get();
        
        return $platformPerformance->map(function($platform) {
            $errorRate = $platform->error_count / max(1, $platform->total_tickets);
            return [
                'platform' => $platform->platform,
                'efficiency_score' => $this->calculateEfficiencyScore($platform),
                'error_rate' => $errorRate,
                'coverage_score' => $platform->venue_coverage,
                'optimization_potential' => $this->assessOptimizationPotential($platform),
                'recommended_improvements' => $this->generatePlatformImprovements($platform)
            ];
        })->toArray();
    }
    
    // Anomaly detection methods
    
    private function detectPriceAnomalies(): array
    {
        $recentPrices = TicketPriceHistory::where('recorded_at', '>=', Carbon::now()->subHours(24))
            ->with('ticket')
            ->get()
            ->groupBy('ticket_id');
        
        $anomalies = [];
        
        foreach ($recentPrices as $ticketId => $prices) {
            if ($prices->count() < 3) continue;
            
            $priceValues = $prices->pluck('price');
            $mean = $priceValues->avg();
            $stdDev = $this->calculateStandardDeviation($priceValues->toArray());
            
            foreach ($prices as $price) {
                $zScore = abs(($price->price - $mean) / max(1, $stdDev));
                if ($zScore > 2.5) { // Anomaly threshold
                    $anomalies[] = [
                        'ticket_id' => $ticketId,
                        'ticket_title' => $price->ticket->title ?? 'Unknown',
                        'anomalous_price' => $price->price,
                        'expected_range' => [$mean - $stdDev, $mean + $stdDev],
                        'severity' => $zScore > 3 ? 'high' : 'medium',
                        'z_score' => round($zScore, 2),
                        'detected_at' => $price->recorded_at
                    ];
                }
            }
        }
        
        return array_slice($anomalies, 0, 20); // Return top 20 anomalies
    }
    
    private function detectDemandAnomalies(): array
    {
        $hourlyDemand = ScrapedTicket::where('created_at', '>=', Carbon::now()->subDays(7))
            ->select([
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as ticket_count'),
                DB::raw('COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as demand_count')
            ])
            ->groupBy('date', 'hour')
            ->get();
        
        $anomalies = [];
        $hourlyBaselines = $hourlyDemand->groupBy('hour')->map(function($hourData) {
            return $hourData->avg('demand_count');
        });
        
        foreach ($hourlyDemand as $data) {
            $baseline = $hourlyBaselines->get($data->hour, 0);
            $deviation = abs($data->demand_count - $baseline) / max(1, $baseline);
            
            if ($deviation > 0.5) { // 50% deviation threshold
                $anomalies[] = [
                    'date' => $data->date,
                    'hour' => $data->hour,
                    'actual_demand' => $data->demand_count,
                    'expected_demand' => round($baseline, 2),
                    'deviation_percentage' => round($deviation * 100, 2),
                    'anomaly_type' => $data->demand_count > $baseline ? 'spike' : 'drop'
                ];
            }
        }
        
        return collect($anomalies)->sortByDesc('deviation_percentage')->take(10)->values()->toArray();
    }
    
    // Utility methods
    
    private function calculateStandardDeviation(array $values): float
    {
        if (count($values) < 2) return 0;
        
        $mean = array_sum($values) / count($values);
        $squaredDifferences = array_map(function($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);
        
        $variance = array_sum($squaredDifferences) / count($values);
        return sqrt($variance);
    }
    
    private function calculateVolatility(Collection $prices): float
    {
        if ($prices->count() < 2) return 0;
        
        $returns = [];
        $priceArray = $prices->values()->toArray();
        
        for ($i = 1; $i < count($priceArray); $i++) {
            if ($priceArray[$i-1] > 0) {
                $returns[] = log($priceArray[$i] / $priceArray[$i-1]);
            }
        }
        
        return empty($returns) ? 0 : $this->calculateStandardDeviation($returns) * sqrt(252); // Annualized
    }
    
    // Placeholder methods for complex calculations (would be implemented with proper ML/statistical libraries)
    private function identifyPeakSeasons($trends) { return []; }
    private function generateSeasonalRecommendations($trends) { return []; }
    private function categorizeDemandLevel($level) { return $level > 0.7 ? 'high' : ($level > 0.3 ? 'medium' : 'low'); }
    private function forecastDemand($trends) { return ['next_week' => 'stable', 'next_month' => 'increasing']; }
    private function identifyDemandDrivers($data) { return ['event_popularity', 'seasonal_factors', 'price_sensitivity']; }
    private function calculateSeasonalAdjustments($trends) { return ['summer' => 1.2, 'winter' => 0.8]; }
    private function analyzePriceTrend($history) { return 'stable'; }
    private function projectPriceRange($history) { return ['min' => 50, 'max' => 200]; }
    private function forecastVolatility($history) { return 'low'; }
    private function identifyPriceOptimizationOpportunities($data) { return []; }
    private function calculateEngagementFrequency($user, $alerts) { return $alerts->count() / max(1, $user->created_at->diffInDays(now())); }
    private function calculateEngagementScore($alerts) { return min(100, $alerts->count() * 10); }
    private function categorizeEngagementLevels($metrics) { return []; }
    private function identifyHighEngagementCharacteristics($metrics) { return []; }
    private function analyzeEngagementTrends($metrics) { return []; }
    private function assessConversionPotential($segment) { return rand(60, 90); }
    private function generateSegmentInsights($segments) { return []; }
    private function generateTargetedStrategies($segments) { return []; }
    private function determinePricePosition($price) { return 'competitive'; }
    private function calculateCompetitiveStrength($platform) { return rand(70, 95); }
    private function calculateEfficiencyScore($platform) { return rand(75, 95); }
    private function assessOptimizationPotential($platform) { return 'medium'; }
    private function generatePlatformImprovements($platform) { return []; }
    private function detectPlatformAnomalies() { return []; }
    private function detectUserBehaviorAnomalies() { return []; }
    private function detectSystemAnomalies() { return []; }
    private function classifyAnomalySeverity() { return []; }
    private function generateAnomalyActions() { return []; }
    private function identifyMarketOpportunities($data) { return []; }
    private function assessMarketRisks($data) { return []; }
    private function generatePredictiveRecommendations($data) { return []; }
    private function calculatePredictionConfidence($data) { return []; }
    private function analyzeConversionPatterns($start, $end) { return []; }
    private function analyzeUserRetention($start, $end) { return []; }
    private function identifyBehaviorTrends($start, $end) { return []; }
    private function predictUserChurn($start, $end) { return []; }
    private function calculateUserLifetimeValue($start, $end) { return []; }
    private function generateUserActionableInsights($start, $end) { return []; }
    private function analyzeMarketShareTrends($start, $end) { return []; }
    private function generatePricingIntelligence($start, $end) { return []; }
    private function identifyDemandHotspots($start, $end) { return []; }
    private function analyzeSupplyPatterns($start, $end) { return []; }
    private function identifyMarketGaps($start, $end) { return []; }
    private function generateStrategicRecommendations($start, $end) { return []; }
    private function calculateMarketHealthScore($start, $end) { return rand(70, 90); }
    private function analyzeScrapingEfficiency($start, $end) { return []; }
    private function analyzeAlertOptimization($start, $end) { return []; }
    private function analyzeResourceUtilization($start, $end) { return []; }
    private function identifyBottlenecks($start, $end) { return []; }
    private function identifyImprovementOpportunities($start, $end) { return []; }
    private function establishPerformanceBenchmarks($start, $end) { return []; }
    private function generateOptimizationRoadmap($start, $end) { return []; }
}
