<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Event;
use App\Models\PriceHistory;
use App\Models\PriceAlert;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Price Tracking Analytics Service
 * 
 * Comprehensive price tracking system with:
 * - Historical price data collection
 * - Price trend analysis and predictions
 * - Target price alerts and notifications
 * - Market insights and analytics
 * - Dynamic pricing intelligence
 */
class PriceTrackingAnalyticsService
{
    private array $analyticsCache = [];
    
    public function __construct(
        private EnhancedSmartAlertsService $alertsService
    ) {
    }

    /**
     * Record price data point for comprehensive tracking
     */
    public function recordPriceData(Event $event, array $priceData): void
    {
        try {
            $platform = $priceData['platform'];
            $timestamp = now();
            
            // Validate and normalize price data
            $normalizedData = $this->normalizePriceData($priceData);
            
            // Store in price history
            PriceHistory::create([
                'event_id' => $event->id,
                'platform' => $platform,
                'price_min' => $normalizedData['price_min'],
                'price_max' => $normalizedData['price_max'],
                'price_average' => $normalizedData['price_average'],
                'total_listings' => $normalizedData['total_listings'],
                'available_quantity' => $normalizedData['available_quantity'],
                'currency' => $normalizedData['currency'],
                'market_conditions' => $normalizedData['market_conditions'],
                'recorded_at' => $timestamp
            ]);
            
            // Update real-time price cache
            $this->updateRealTimePriceCache($event, $platform, $normalizedData);
            
            // Check for price alerts
            $this->checkPriceAlerts($event, $normalizedData);
            
            // Update price predictions
            $this->updatePricePredictions($event, $normalizedData);
            
            Log::debug('Price data recorded', [
                'event_id' => $event->id,
                'platform' => $platform,
                'price_range' => "{$normalizedData['price_min']}-{$normalizedData['price_max']}"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to record price data', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'price_data' => $priceData
            ]);
        }
    }

    /**
     * Normalize and validate price data
     */
    private function normalizePriceData(array $priceData): array
    {
        $prices = array_filter([
            $priceData['price_min'] ?? 0,
            $priceData['price_max'] ?? 0,
            $priceData['average_price'] ?? 0
        ], fn($price) => $price > 0);
        
        if (empty($prices)) {
            throw new \Exception('No valid prices in price data');
        }
        
        $priceMin = min($prices);
        $priceMax = max($prices);
        $priceAverage = $priceData['average_price'] ?? ($priceMin + $priceMax) / 2;
        
        return [
            'price_min' => round($priceMin, 2),
            'price_max' => round($priceMax, 2),
            'price_average' => round($priceAverage, 2),
            'total_listings' => max(0, $priceData['total_tickets'] ?? $priceData['total_listings'] ?? 0),
            'available_quantity' => max(0, $priceData['available_quantity'] ?? $priceData['total_tickets'] ?? 0),
            'currency' => $priceData['currency'] ?? 'GBP',
            'market_conditions' => $this->analyzeMarketConditions($priceData),
            'quality_score' => $this->calculateDataQuality($priceData)
        ];
    }

    /**
     * Analyze current market conditions
     */
    private function analyzeMarketConditions(array $priceData): array
    {
        $conditions = [
            'demand_level' => 'medium',
            'price_volatility' => 'stable',
            'inventory_status' => 'normal',
            'market_trend' => 'neutral'
        ];
        
        $totalTickets = $priceData['total_tickets'] ?? 100;
        $minPrice = $priceData['price_min'] ?? 0;
        $maxPrice = $priceData['price_max'] ?? 0;
        
        // Analyze demand based on inventory
        if ($totalTickets < 10) {
            $conditions['demand_level'] = 'very_high';
            $conditions['inventory_status'] = 'critical';
        } elseif ($totalTickets < 50) {
            $conditions['demand_level'] = 'high';
            $conditions['inventory_status'] = 'low';
        } elseif ($totalTickets > 500) {
            $conditions['demand_level'] = 'low';
            $conditions['inventory_status'] = 'abundant';
        }
        
        // Analyze price volatility
        if ($maxPrice > 0 && $minPrice > 0) {
            $priceSpread = ($maxPrice - $minPrice) / $minPrice;
            if ($priceSpread > 2.0) {
                $conditions['price_volatility'] = 'very_high';
            } elseif ($priceSpread > 1.0) {
                $conditions['price_volatility'] = 'high';
            } elseif ($priceSpread < 0.1) {
                $conditions['price_volatility'] = 'very_stable';
            }
        }
        
        return $conditions;
    }

    /**
     * Calculate data quality score
     */
    private function calculateDataQuality(array $priceData): float
    {
        $score = 1.0;
        
        // Deduct for missing data
        $requiredFields = ['price_min', 'price_max', 'total_tickets', 'platform'];
        $missingFields = array_diff($requiredFields, array_keys($priceData));
        $score -= count($missingFields) * 0.1;
        
        // Deduct for inconsistent data
        $minPrice = $priceData['price_min'] ?? 0;
        $maxPrice = $priceData['price_max'] ?? 0;
        if ($minPrice > $maxPrice) {
            $score -= 0.3;
        }
        
        // Boost for additional data fields
        $bonusFields = ['average_price', 'listing_count', 'section_breakdown'];
        $presentBonusFields = array_intersect($bonusFields, array_keys($priceData));
        $score += count($presentBonusFields) * 0.05;
        
        return max(0.1, min(1.0, $score));
    }

    /**
     * Update real-time price cache for instant access
     */
    private function updateRealTimePriceCache(Event $event, string $platform, array $priceData): void
    {
        $cacheKey = "realtime_prices_{$event->id}";
        $realtimePrices = Cache::get($cacheKey, []);
        
        $realtimePrices[$platform] = [
            'price_min' => $priceData['price_min'],
            'price_max' => $priceData['price_max'],
            'price_average' => $priceData['price_average'],
            'total_listings' => $priceData['total_listings'],
            'market_conditions' => $priceData['market_conditions'],
            'last_updated' => now()->toISOString(),
            'quality_score' => $priceData['quality_score']
        ];
        
        // Calculate cross-platform summary
        $realtimePrices['summary'] = $this->calculateCrossPlatformSummary($realtimePrices);
        
        Cache::put($cacheKey, $realtimePrices, 300); // Cache for 5 minutes
    }

    /**
     * Calculate summary across all platforms
     */
    private function calculateCrossPlatformSummary(array $platformPrices): array
    {
        $platforms = array_filter($platformPrices, fn($key) => $key !== 'summary', ARRAY_FILTER_USE_KEY);
        
        if (empty($platforms)) {
            return [];
        }
        
        $allMinPrices = array_column($platforms, 'price_min');
        $allMaxPrices = array_column($platforms, 'price_max');
        $allAvgPrices = array_column($platforms, 'price_average');
        $allListings = array_column($platforms, 'total_listings');
        
        return [
            'global_min_price' => min($allMinPrices),
            'global_max_price' => max($allMaxPrices),
            'global_avg_price' => round(array_sum($allAvgPrices) / count($allAvgPrices), 2),
            'total_listings' => array_sum($allListings),
            'platform_count' => count($platforms),
            'best_value_platform' => $this->findBestValuePlatform($platforms),
            'price_variance' => $this->calculatePriceVariance($allMinPrices),
            'last_updated' => now()->toISOString()
        ];
    }

    /**
     * Find platform with best value
     */
    private function findBestValuePlatform(array $platforms): ?string
    {
        $bestPlatform = null;
        $bestPrice = PHP_FLOAT_MAX;
        
        foreach ($platforms as $platform => $data) {
            if ($data['price_min'] < $bestPrice && $data['total_listings'] > 0) {
                $bestPrice = $data['price_min'];
                $bestPlatform = $platform;
            }
        }
        
        return $bestPlatform;
    }

    /**
     * Calculate price variance across platforms
     */
    private function calculatePriceVariance(array $prices): float
    {
        if (count($prices) < 2) {
            return 0.0;
        }
        
        $mean = array_sum($prices) / count($prices);
        $squaredDiffs = array_map(fn($price) => pow($price - $mean, 2), $prices);
        $variance = array_sum($squaredDiffs) / count($prices);
        
        return round(sqrt($variance), 2);
    }

    /**
     * Check and trigger price alerts
     */
    private function checkPriceAlerts(Event $event, array $priceData): void
    {
        $activeAlerts = PriceAlert::where('event_id', $event->id)
            ->where('is_active', true)
            ->with('user')
            ->get();
        
        foreach ($activeAlerts as $alert) {
            if ($this->shouldTriggerAlert($alert, $priceData)) {
                $this->triggerPriceAlert($alert, $priceData);
            }
        }
    }

    /**
     * Determine if price alert should be triggered
     */
    private function shouldTriggerAlert(PriceAlert $alert, array $priceData): bool
    {
        $targetPrice = $alert->target_price;
        $currentPrice = $priceData['price_min'];
        $alertType = $alert->alert_type;
        
        // Check if enough time has passed since last alert
        if ($alert->last_triggered_at && 
            $alert->last_triggered_at->diffInMinutes(now()) < ($alert->min_interval_minutes ?? 15)) {
            return false;
        }
        
        return match ($alertType) {
            'price_drop' => $currentPrice <= $targetPrice,
            'price_drop_percentage' => $this->checkPercentageDrop($alert, $currentPrice),
            'absolute_price' => $currentPrice <= $targetPrice,
            'best_deal' => $this->isBestDeal($alert, $priceData),
            default => false
        };
    }

    /**
     * Check for percentage-based price drop
     */
    private function checkPercentageDrop(PriceAlert $alert, float $currentPrice): bool
    {
        $baselinePrice = $alert->baseline_price ?? $alert->target_price;
        $targetDropPercentage = $alert->target_percentage ?? 10;
        
        if ($baselinePrice <= 0) {
            return false;
        }
        
        $actualDropPercentage = (($baselinePrice - $currentPrice) / $baselinePrice) * 100;
        return $actualDropPercentage >= $targetDropPercentage;
    }

    /**
     * Check if current price is the best deal
     */
    private function isBestDeal(PriceAlert $alert, array $priceData): bool
    {
        $historicalLow = $this->getHistoricalLowPrice($alert->event_id, 30); // 30 days
        return $priceData['price_min'] <= $historicalLow * 1.05; // Within 5% of historical low
    }

    /**
     * Trigger price alert and send notification
     */
    private function triggerPriceAlert(PriceAlert $alert, array $priceData): void
    {
        $alert->update([
            'last_triggered_at' => now(),
            'trigger_count' => $alert->trigger_count + 1
        ]);
        
        $alertData = [
            'type' => 'price_alert',
            'urgency' => $this->calculateAlertUrgency($alert, $priceData),
            'event_name' => $alert->event->name,
            'event_id' => $alert->event_id,
            'alert_type' => $alert->alert_type,
            'target_price' => $alert->target_price,
            'current_price' => $priceData['price_min'],
            'savings' => $alert->target_price - $priceData['price_min'],
            'platform' => $priceData['platform'] ?? 'Multiple',
            'market_conditions' => $priceData['market_conditions'],
            'message' => $this->buildAlertMessage($alert, $priceData)
        ];
        
        $this->alertsService->sendEnhancedAlert($alert->user, $alertData);
        
        Log::info('Price alert triggered', [
            'alert_id' => $alert->id,
            'user_id' => $alert->user_id,
            'event_id' => $alert->event_id,
            'target_price' => $alert->target_price,
            'current_price' => $priceData['price_min']
        ]);
    }

    /**
     * Calculate alert urgency based on conditions
     */
    private function calculateAlertUrgency(PriceAlert $alert, array $priceData): string
    {
        $savings = $alert->target_price - $priceData['price_min'];
        $savingsPercentage = ($savings / $alert->target_price) * 100;
        $inventory = $priceData['total_listings'];
        
        // Critical: High savings + low inventory
        if ($savingsPercentage >= 30 || $inventory < 10) {
            return 'critical';
        }
        
        // High: Good savings or low inventory
        if ($savingsPercentage >= 20 || $inventory < 25) {
            return 'high';
        }
        
        // Medium: Moderate savings
        if ($savingsPercentage >= 10) {
            return 'medium';
        }
        
        return 'low';
    }

    /**
     * Build alert message content
     */
    private function buildAlertMessage(PriceAlert $alert, array $priceData): string
    {
        $eventName = $alert->event->name;
        $currentPrice = $priceData['price_min'];
        $savings = $alert->target_price - $currentPrice;
        $platform = $priceData['platform'] ?? 'Multiple platforms';
        
        return "🎯 PRICE ALERT: {$eventName} tickets now £{$currentPrice} on {$platform}! You save £{$savings}!";
    }

    /**
     * Get comprehensive price analytics for event
     */
    public function getEventPriceAnalytics(Event $event, int $days = 30): array
    {
        $cacheKey = "event_analytics_{$event->id}_{$days}";
        
        return Cache::remember($cacheKey, 900, function () use ($event, $days) {
            $startDate = now()->subDays($days);
            
            $priceHistory = PriceHistory::where('event_id', $event->id)
                ->where('recorded_at', '>=', $startDate)
                ->orderBy('recorded_at')
                ->get();
            
            if ($priceHistory->isEmpty()) {
                return $this->getEmptyAnalytics();
            }
            
            return [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'analysis_period' => $days,
                'price_trends' => $this->analyzePriceTrends($priceHistory),
                'platform_comparison' => $this->comparePlatformPrices($priceHistory),
                'market_insights' => $this->generateMarketInsights($priceHistory),
                'price_predictions' => $this->generatePricePredictions($event, $priceHistory),
                'optimal_purchase_timing' => $this->calculateOptimalTiming($priceHistory),
                'volatility_analysis' => $this->analyzeVolatility($priceHistory),
                'demand_patterns' => $this->analyzeDemandPatterns($priceHistory),
                'last_updated' => now()->toISOString()
            ];
        });
    }

    /**
     * Analyze price trends over time
     */
    private function analyzePriceTrends(object $priceHistory): array
    {
        $groupedByDay = $priceHistory->groupBy(fn($item) => $item->recorded_at->format('Y-m-d'));
        $dailyTrends = [];
        
        foreach ($groupedByDay as $date => $dayRecords) {
            $dailyTrends[] = [
                'date' => $date,
                'min_price' => $dayRecords->min('price_min'),
                'max_price' => $dayRecords->max('price_max'),
                'avg_price' => round($dayRecords->avg('price_average'), 2),
                'total_listings' => $dayRecords->sum('total_listings'),
                'platform_count' => $dayRecords->unique('platform')->count()
            ];
        }
        
        // Calculate trend direction
        $recent = array_slice($dailyTrends, -7); // Last 7 days
        $older = array_slice($dailyTrends, -14, 7); // Previous 7 days
        
        $recentAvg = collect($recent)->avg('avg_price');
        $olderAvg = collect($older)->avg('avg_price');
        
        $trendDirection = $recentAvg > $olderAvg ? 'increasing' : 
                         ($recentAvg < $olderAvg ? 'decreasing' : 'stable');
        
        $trendStrength = abs($recentAvg - $olderAvg) / $olderAvg * 100;
        
        return [
            'daily_trends' => $dailyTrends,
            'trend_direction' => $trendDirection,
            'trend_strength' => round($trendStrength, 2),
            'price_range' => [
                'lowest' => $priceHistory->min('price_min'),
                'highest' => $priceHistory->max('price_max'),
                'current' => $priceHistory->last()->price_min ?? 0
            ]
        ];
    }

    /**
     * Compare prices across platforms
     */
    private function comparePlatformPrices(object $priceHistory): array
    {
        $platformStats = [];
        $groupedByPlatform = $priceHistory->groupBy('platform');
        
        foreach ($groupedByPlatform as $platform => $records) {
            $platformStats[$platform] = [
                'avg_price' => round($records->avg('price_average'), 2),
                'min_price' => $records->min('price_min'),
                'max_price' => $records->max('price_max'),
                'total_listings' => $records->sum('total_listings'),
                'data_points' => $records->count(),
                'last_updated' => $records->max('recorded_at'),
                'reliability_score' => $this->calculatePlatformReliability($records)
            ];
        }
        
        // Find best value platform
        $bestValue = collect($platformStats)->sortBy('avg_price')->first();
        $bestValuePlatform = collect($platformStats)->search($bestValue);
        
        return [
            'platform_stats' => $platformStats,
            'best_value_platform' => $bestValuePlatform,
            'platform_count' => count($platformStats),
            'price_spread' => $this->calculatePlatformPriceSpread($platformStats)
        ];
    }

    /**
     * Calculate platform reliability score
     */
    private function calculatePlatformReliability(object $records): float
    {
        $dataPoints = $records->count();
        $avgQuality = $records->avg('quality_score') ?? 0.5;
        $consistency = 1 - ($records->std('price_average') / $records->avg('price_average'));
        
        // Weighted score
        $score = ($dataPoints / 100 * 0.3) + ($avgQuality * 0.4) + ($consistency * 0.3);
        return round(min(1.0, max(0.0, $score)), 2);
    }

    /**
     * Calculate price spread across platforms
     */
    private function calculatePlatformPriceSpread(array $platformStats): array
    {
        $avgPrices = array_column($platformStats, 'avg_price');
        
        if (empty($avgPrices)) {
            return ['spread' => 0, 'variance' => 0];
        }
        
        $minPrice = min($avgPrices);
        $maxPrice = max($avgPrices);
        $spread = $maxPrice - $minPrice;
        $variance = $this->calculatePriceVariance($avgPrices);
        
        return [
            'spread' => round($spread, 2),
            'variance' => round($variance, 2),
            'spread_percentage' => $minPrice > 0 ? round(($spread / $minPrice) * 100, 2) : 0
        ];
    }

    /**
     * Generate market insights
     */
    private function generateMarketInsights(object $priceHistory): array
    {
        $insights = [];
        
        // Demand analysis
        $avgListings = $priceHistory->avg('total_listings');
        if ($avgListings < 50) {
            $insights[] = "High demand event - limited inventory across platforms";
        } elseif ($avgListings > 200) {
            $insights[] = "Good availability - multiple options to choose from";
        }
        
        // Price volatility insight
        $priceVariance = $this->calculatePriceVariance($priceHistory->pluck('price_average')->toArray());
        if ($priceVariance > 50) {
            $insights[] = "High price volatility - prices fluctuating significantly";
        } elseif ($priceVariance < 10) {
            $insights[] = "Stable pricing - minimal price fluctuations";
        }
        
        // Platform diversity insight
        $platformCount = $priceHistory->unique('platform')->count();
        if ($platformCount >= 4) {
            $insights[] = "Available on multiple platforms - good for price comparison";
        } elseif ($platformCount <= 2) {
            $insights[] = "Limited platform availability - fewer options to compare";
        }
        
        return $insights;
    }

    /**
     * Generate price predictions using trend analysis
     */
    private function generatePricePredictions(Event $event, object $priceHistory): array
    {
        $predictions = [];
        
        // Simple trend-based prediction
        $recentPrices = $priceHistory->sortByDesc('recorded_at')->take(10)->pluck('price_average');
        $slope = $this->calculateTrendSlope($recentPrices->toArray());
        
        for ($days = 1; $days <= 7; $days++) {
            $predictedPrice = $recentPrices->first() + ($slope * $days);
            $predictions[] = [
                'date' => now()->addDays($days)->format('Y-m-d'),
                'predicted_price' => round(max(0, $predictedPrice), 2),
                'confidence' => $this->calculatePredictionConfidence($recentPrices->toArray(), $days)
            ];
        }
        
        return [
            'predictions' => $predictions,
            'trend_slope' => round($slope, 2),
            'methodology' => 'Linear trend analysis',
            'disclaimer' => 'Predictions are estimates based on historical data and may not reflect actual future prices'
        ];
    }

    /**
     * Calculate trend slope for predictions
     */
    private function calculateTrendSlope(array $prices): float
    {
        $n = count($prices);
        if ($n < 2) return 0;
        
        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($prices);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $prices[$i];
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        return $slope;
    }

    /**
     * Calculate prediction confidence
     */
    private function calculatePredictionConfidence(array $prices, int $daysAhead): float
    {
        $baseConfidence = 0.8;
        $volatility = $this->calculatePriceVariance($prices) / (array_sum($prices) / count($prices));
        $timeDecay = 1 / (1 + $daysAhead * 0.1);
        
        return round(max(0.1, min(1.0, $baseConfidence * (1 - $volatility) * $timeDecay)), 2);
    }

    /**
     * Calculate optimal purchase timing
     */
    private function calculateOptimalTiming(object $priceHistory): array
    {
        // Analyze historical patterns to suggest best times to buy
        $hourlyPatterns = $priceHistory->groupBy(fn($item) => $item->recorded_at->format('H'));
        $dailyPatterns = $priceHistory->groupBy(fn($item) => $item->recorded_at->format('w')); // Day of week
        
        $bestHours = [];
        foreach ($hourlyPatterns as $hour => $records) {
            $bestHours[] = [
                'hour' => $hour,
                'avg_price' => round($records->avg('price_average'), 2),
                'sample_size' => $records->count()
            ];
        }
        
        $bestDays = [];
        foreach ($dailyPatterns as $day => $records) {
            $bestDays[] = [
                'day_of_week' => $day,
                'day_name' => now()->dayOfWeek($day)->format('l'),
                'avg_price' => round($records->avg('price_average'), 2),
                'sample_size' => $records->count()
            ];
        }
        
        // Sort by price to find optimal times
        $optimalHour = collect($bestHours)->where('sample_size', '>=', 3)->sortBy('avg_price')->first();
        $optimalDay = collect($bestDays)->where('sample_size', '>=', 5)->sortBy('avg_price')->first();
        
        return [
            'optimal_hour' => $optimalHour['hour'] ?? null,
            'optimal_day' => $optimalDay['day_name'] ?? null,
            'hourly_patterns' => $bestHours,
            'daily_patterns' => $bestDays,
            'recommendation' => $this->generateTimingRecommendation($optimalHour, $optimalDay)
        ];
    }

    /**
     * Generate timing recommendation
     */
    private function generateTimingRecommendation(?array $optimalHour, ?array $optimalDay): string
    {
        if ($optimalHour && $optimalDay) {
            return "Best time to buy: {$optimalDay['day_name']} at {$optimalHour['hour']}:00 (historically lowest prices)";
        } elseif ($optimalDay) {
            return "Best day to buy: {$optimalDay['day_name']} (historically lower prices)";
        } elseif ($optimalHour) {
            return "Best time to buy: {$optimalHour['hour']}:00 (historically lower prices)";
        }
        
        return "Insufficient data for timing recommendations";
    }

    /**
     * Analyze price volatility
     */
    private function analyzeVolatility(object $priceHistory): array
    {
        $prices = $priceHistory->pluck('price_average')->toArray();
        $variance = $this->calculatePriceVariance($prices);
        $mean = array_sum($prices) / count($prices);
        $coefficientOfVariation = $variance / $mean;
        
        $volatilityLevel = $coefficientOfVariation < 0.1 ? 'low' : 
                          ($coefficientOfVariation < 0.3 ? 'medium' : 'high');
        
        return [
            'variance' => round($variance, 2),
            'coefficient_of_variation' => round($coefficientOfVariation, 2),
            'volatility_level' => $volatilityLevel,
            'price_stability' => $volatilityLevel === 'low' ? 'stable' : 
                               ($volatilityLevel === 'medium' ? 'moderate' : 'unstable')
        ];
    }

    /**
     * Analyze demand patterns
     */
    private function analyzeDemandPatterns(object $priceHistory): array
    {
        $patterns = [
            'inventory_trend' => $this->analyzeInventoryTrend($priceHistory),
            'price_demand_correlation' => $this->analyzePriceDemandCorrelation($priceHistory),
            'peak_demand_times' => $this->identifyPeakDemandTimes($priceHistory)
        ];
        
        return $patterns;
    }

    // Helper methods for demand analysis
    private function analyzeInventoryTrend(object $priceHistory): string
    {
        $recent = $priceHistory->sortByDesc('recorded_at')->take(5)->avg('total_listings');
        $older = $priceHistory->sortBy('recorded_at')->take(5)->avg('total_listings');
        
        if ($recent < $older * 0.8) {
            return 'decreasing_rapidly';
        } elseif ($recent < $older * 0.95) {
            return 'decreasing';
        } elseif ($recent > $older * 1.2) {
            return 'increasing';
        }
        
        return 'stable';
    }

    private function analyzePriceDemandCorrelation(object $priceHistory): float
    {
        // Calculate correlation between price and inverse of inventory (demand indicator)
        $prices = $priceHistory->pluck('price_average')->toArray();
        $inventories = $priceHistory->pluck('total_listings')->toArray();
        
        // Convert inventory to demand indicator (inverse relationship)
        $demandIndicators = array_map(fn($inv) => $inv > 0 ? 1 / $inv : 0, $inventories);
        
        return $this->calculateCorrelation($prices, $demandIndicators);
    }

    private function identifyPeakDemandTimes(object $priceHistory): array
    {
        // Group by hour and find times with lowest inventory (highest demand)
        $hourlyDemand = $priceHistory->groupBy(fn($item) => $item->recorded_at->format('H'))
            ->map(fn($records) => [
                'avg_inventory' => $records->avg('total_listings'),
                'avg_price' => $records->avg('price_average')
            ])
            ->sortBy('avg_inventory')
            ->take(3);
        
        return $hourlyDemand->keys()->toArray();
    }

    /**
     * Calculate correlation coefficient
     */
    private function calculateCorrelation(array $x, array $y): float
    {
        $n = count($x);
        if ($n < 2 || count($y) !== $n) return 0;
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }
        
        $numerator = $n * $sumXY - $sumX * $sumY;
        $denominator = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));
        
        return $denominator != 0 ? round($numerator / $denominator, 3) : 0;
    }

    /**
     * Get historical low price for event
     */
    private function getHistoricalLowPrice(int $eventId, int $days): float
    {
        return PriceHistory::where('event_id', $eventId)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->min('price_min') ?? 0;
    }

    /**
     * Get empty analytics structure
     */
    private function getEmptyAnalytics(): array
    {
        return [
            'price_trends' => [],
            'platform_comparison' => [],
            'market_insights' => ['No price data available yet'],
            'price_predictions' => [],
            'optimal_purchase_timing' => [],
            'volatility_analysis' => [],
            'demand_patterns' => []
        ];
    }

    /**
     * Update price predictions for event
     */
    private function updatePricePredictions(Event $event, array $priceData): void
    {
        // This would implement machine learning predictions
        // For now, we'll just cache the prediction data
        $cacheKey = "price_predictions_{$event->id}";
        Cache::put($cacheKey, [
            'last_price' => $priceData['price_min'],
            'trend_direction' => $priceData['market_conditions']['market_trend'],
            'updated_at' => now()
        ], 3600);
    }
}