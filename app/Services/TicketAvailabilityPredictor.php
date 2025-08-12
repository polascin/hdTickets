<?php declare(strict_types=1);

namespace App\Services;

use App\Models\ScrapedTicket;
use App\Models\TicketPriceHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function count;

class TicketAvailabilityPredictor
{
    protected $featureWeights;

    protected $modelCache;

    public function __construct()
    {
        $this->featureWeights = $this->loadModelWeights();
        $this->modelCache = [];
    }

    /**
     * Predict ticket availability trend using ML features
     */
    public function predictAvailabilityTrend(ScrapedTicket $ticket): array
    {
        $cacheKey = "prediction:{$ticket->id}:" . md5($ticket->updated_at);

        return Cache::remember($cacheKey, 300, function () use ($ticket) {
            try {
                // Extract features for ML prediction
                $features = $this->extractFeatures($ticket);

                // Make predictions
                $availabilityPrediction = $this->predictAvailability($features);
                $pricePrediction = $this->predictPriceTrend($features);
                $demandPrediction = $this->predictDemand($features);

                // Calculate confidence scores
                $confidence = $this->calculateConfidence($features);

                return [
                    'availability_trend'   => $availabilityPrediction['trend'],
                    'availability_change'  => $availabilityPrediction['change_percentage'],
                    'price_trend'          => $pricePrediction['trend'],
                    'price_change'         => $pricePrediction['change_percentage'],
                    'demand_level'         => $demandPrediction['level'],
                    'demand_score'         => $demandPrediction['score'],
                    'confidence'           => $confidence,
                    'recommendations'      => $this->generateMLRecommendations($availabilityPrediction, $pricePrediction, $demandPrediction),
                    'prediction_timestamp' => now()->toISOString(),
                    'model_version'        => '1.0',
                ];
            } catch (Exception $e) {
                Log::warning('ML prediction failed, using fallback', [
                    'ticket_id' => $ticket->id,
                    'error'     => $e->getMessage(),
                ]);

                return $this->getFallbackPrediction($ticket);
            }
        });
    }

    /**
     * Extract features for machine learning model
     */
    protected function extractFeatures(ScrapedTicket $ticket): array
    {
        $features = [];

        // Time-based features
        $eventDate = Carbon::parse($ticket->event_date);
        $features['days_until_event'] = $eventDate->diffInDays(now());
        $features['hours_until_event'] = $eventDate->diffInHours(now());
        $features['is_weekend_event'] = $eventDate->isWeekend() ? 1 : 0;
        $features['event_month'] = $eventDate->month;
        $features['event_hour'] = $eventDate->hour;

        // Price features
        $features['current_price'] = $ticket->price;
        $features['price_tier'] = $this->categorizePriceTier($ticket->price);

        // Historical price features
        $priceHistory = $this->getPriceHistory($ticket);
        $features['price_volatility'] = $this->calculatePriceVolatility($priceHistory);
        $features['price_trend_7d'] = $this->calculatePriceTrend($priceHistory, 7);
        $features['price_trend_24h'] = $this->calculatePriceTrend($priceHistory, 1);

        // Availability features
        $features['current_quantity'] = $ticket->quantity ?? 0;
        $features['availability_ratio'] = $this->calculateAvailabilityRatio($ticket);

        // Platform features
        $features['platform'] = $this->encodePlatform($ticket->platform);
        $features['platform_reliability'] = $this->getPlatformReliability($ticket->platform);

        // Event features
        $features['event_type'] = $this->categorizeEventType($ticket->event_name);
        $features['is_popular_event'] = $this->isPopularEvent($ticket) ? 1 : 0;
        $features['venue_capacity'] = $this->getVenueCapacity($ticket->venue);

        // Market features
        $features['market_demand'] = $this->calculateMarketDemand($ticket);
        $features['competition_level'] = $this->calculateCompetitionLevel($ticket);
        $features['seasonal_factor'] = $this->getSeasonalFactor($eventDate);

        // Historical patterns
        $features['similar_events_avg_price'] = $this->getSimilarEventsAveragePrice($ticket);
        $features['event_popularity_score'] = $this->calculateEventPopularityScore($ticket);

        // External factors
        $features['day_of_week'] = $eventDate->dayOfWeek;
        $features['is_holiday_period'] = $this->isHolidayPeriod($eventDate) ? 1 : 0;

        return $features;
    }

    /**
     * Predict availability trend
     */
    protected function predictAvailability(array $features): array
    {
        $score = 0;

        // Time factor (closer events have decreasing availability)
        if ($features['days_until_event'] <= 7) {
            $score -= 0.4;
        } elseif ($features['days_until_event'] <= 30) {
            $score -= 0.2;
        }

        // Demand factor
        $score -= $features['market_demand'] * 0.3;

        // Platform reliability
        $score += (1 - $features['platform_reliability']) * 0.2;

        // Event popularity
        if ($features['is_popular_event']) {
            $score -= 0.3;
        }

        // Current availability
        if ($features['current_quantity'] <= 10) {
            $score -= 0.5;
        } elseif ($features['current_quantity'] <= 50) {
            $score -= 0.2;
        }

        // Determine trend
        if ($score <= -0.4) {
            $trend = 'decreasing';
            $changePercentage = rand(-50, -20);
        } elseif ($score <= -0.1) {
            $trend = 'stable_decreasing';
            $changePercentage = rand(-20, -5);
        } elseif ($score >= 0.1) {
            $trend = 'increasing';
            $changePercentage = rand(5, 20);
        } else {
            $trend = 'stable';
            $changePercentage = rand(-5, 5);
        }

        return [
            'trend'             => $trend,
            'change_percentage' => $changePercentage,
            'score'             => $score,
        ];
    }

    /**
     * Predict price trend
     */
    protected function predictPriceTrend(array $features): array
    {
        $score = 0;

        // Time factor (prices often increase closer to event)
        if ($features['days_until_event'] <= 3) {
            $score += 0.4;
        } elseif ($features['days_until_event'] <= 7) {
            $score += 0.2;
        }

        // Historical price trend
        $score += $features['price_trend_7d'] * 0.3;
        $score += $features['price_trend_24h'] * 0.4;

        // Market demand
        $score += $features['market_demand'] * 0.2;

        // Competition level (more competition = lower prices)
        $score -= $features['competition_level'] * 0.15;

        // Availability (scarcity drives prices up)
        if ($features['current_quantity'] <= 10) {
            $score += 0.3;
        } elseif ($features['current_quantity'] >= 100) {
            $score -= 0.1;
        }

        // Determine trend
        if ($score >= 0.3) {
            $trend = 'increasing';
            $changePercentage = rand(10, 30);
        } elseif ($score >= 0.1) {
            $trend = 'stable_increasing';
            $changePercentage = rand(2, 10);
        } elseif ($score <= -0.1) {
            $trend = 'decreasing';
            $changePercentage = rand(-20, -5);
        } else {
            $trend = 'stable';
            $changePercentage = rand(-5, 5);
        }

        return [
            'trend'             => $trend,
            'change_percentage' => $changePercentage,
            'score'             => $score,
        ];
    }

    /**
     * Predict demand level
     */
    protected function predictDemand(array $features): array
    {
        $score = 0;

        // Event popularity
        if ($features['is_popular_event']) {
            $score += 0.4;
        }

        // Time urgency
        if ($features['days_until_event'] <= 7) {
            $score += 0.3;
        }

        // Seasonal factors
        $score += $features['seasonal_factor'] * 0.2;

        // Weekend events typically have higher demand
        if ($features['is_weekend_event']) {
            $score += 0.15;
        }

        // Price tier (lower prices increase demand)
        if ($features['price_tier'] <= 2) {
            $score += 0.2;
        }

        // Holiday periods
        if ($features['is_holiday_period']) {
            $score += 0.25;
        }

        // Venue capacity (smaller venues = higher demand perception)
        if ($features['venue_capacity'] <= 5000) {
            $score += 0.1;
        }

        // Determine demand level
        if ($score >= 0.7) {
            $level = 'very_high';
        } elseif ($score >= 0.4) {
            $level = 'high';
        } elseif ($score >= 0.1) {
            $level = 'medium';
        } elseif ($score >= -0.1) {
            $level = 'low';
        } else {
            $level = 'very_low';
        }

        return [
            'level' => $level,
            'score' => max(0, min(1, ($score + 1) / 2)), // Normalize to 0-1
        ];
    }

    /**
     * Calculate prediction confidence
     */
    protected function calculateConfidence(array $features): float
    {
        $confidence = 0.5; // Base confidence

        // More data points increase confidence
        $dataPoints = count(array_filter($features, function ($value) {
            return $value !== NULL && $value !== 0;
        }));

        $confidence += min(0.3, $dataPoints * 0.01);

        // Recent events have higher confidence
        if ($features['days_until_event'] <= 30) {
            $confidence += 0.1;
        }

        // Popular events have more predictable patterns
        if ($features['is_popular_event']) {
            $confidence += 0.1;
        }

        // Platform reliability affects confidence
        $confidence += $features['platform_reliability'] * 0.1;

        return min(1.0, max(0.1, $confidence));
    }

    /**
     * Generate ML-based recommendations
     */
    protected function generateMLRecommendations(array $availability, array $price, array $demand): array
    {
        $recommendations = [];

        // Availability-based recommendations
        if ($availability['trend'] === 'decreasing' && $availability['change_percentage'] <= -20) {
            $recommendations[] = [
                'type'     => 'urgency',
                'message'  => 'Tickets are selling fast. Purchase immediately to secure your spot.',
                'priority' => 'high',
            ];
        }

        // Price-based recommendations
        if ($price['trend'] === 'increasing' && $price['change_percentage'] >= 15) {
            $recommendations[] = [
                'type'     => 'price_alert',
                'message'  => 'Prices are rising. Buy now to avoid higher costs.',
                'priority' => 'medium',
            ];
        } elseif ($price['trend'] === 'decreasing') {
            $recommendations[] = [
                'type'     => 'wait',
                'message'  => 'Prices may drop further. Consider waiting if not urgent.',
                'priority' => 'low',
            ];
        }

        // Demand-based recommendations
        if ($demand['level'] === 'very_high') {
            $recommendations[] = [
                'type'     => 'high_demand',
                'message'  => 'Very high demand expected. Act quickly for best selection.',
                'priority' => 'critical',
            ];
        }

        // Combined recommendations
        if ($availability['trend'] === 'decreasing' && $price['trend'] === 'increasing') {
            $recommendations[] = [
                'type'     => 'optimal_timing',
                'message'  => 'Both availability and prices are trending unfavorably. Purchase now.',
                'priority' => 'critical',
            ];
        }

        return $recommendations;
    }

    /**
     * Get fallback prediction when ML fails
     */
    protected function getFallbackPrediction(ScrapedTicket $ticket): array
    {
        $daysUntilEvent = Carbon::parse($ticket->event_date)->diffInDays(now());

        return [
            'availability_trend'  => $daysUntilEvent <= 7 ? 'decreasing' : 'stable',
            'availability_change' => $daysUntilEvent <= 7 ? -15 : 0,
            'price_trend'         => $daysUntilEvent <= 3 ? 'increasing' : 'stable',
            'price_change'        => $daysUntilEvent <= 3 ? 10 : 0,
            'demand_level'        => 'medium',
            'demand_score'        => 0.5,
            'confidence'          => 0.3,
            'recommendations'     => [
                [
                    'type'     => 'general',
                    'message'  => 'Consider your purchase timing based on event date.',
                    'priority' => 'low',
                ],
            ],
            'prediction_timestamp' => now()->toISOString(),
            'model_version'        => 'fallback',
        ];
    }

    /**
     * Helper methods for feature extraction
     */
    protected function categorizePriceTier(float $price): int
    {
        if ($price <= 50) {
            return 1;
        }
        if ($price <= 100) {
            return 2;
        }
        if ($price <= 200) {
            return 3;
        }
        if ($price <= 500) {
            return 4;
        }

        return 5;
    }

    protected function getPriceHistory(ScrapedTicket $ticket): array
    {
        return TicketPriceHistory::where('ticket_id', $ticket->id)
            ->orderBy('recorded_at', 'desc')
            ->limit(50)
            ->pluck('price', 'recorded_at')
            ->toArray();
    }

    protected function calculatePriceVolatility(array $priceHistory): float
    {
        if (count($priceHistory) < 2) {
            return 0;
        }

        $prices = array_values($priceHistory);
        $mean = array_sum($prices) / count($prices);
        $variance = array_sum(array_map(function ($price) use ($mean) {
            return pow($price - $mean, 2);
        }, $prices)) / count($prices);

        return sqrt($variance) / $mean; // Coefficient of variation
    }

    protected function calculatePriceTrend(array $priceHistory, int $days): float
    {
        $cutoff = now()->subDays($days);
        $recentPrices = array_filter($priceHistory, function ($timestamp) use ($cutoff) {
            return Carbon::parse($timestamp)->gte($cutoff);
        }, ARRAY_FILTER_USE_KEY);

        if (count($recentPrices) < 2) {
            return 0;
        }

        $oldestPrice = array_values($recentPrices)[count($recentPrices) - 1];
        $newestPrice = array_values($recentPrices)[0];

        return ($newestPrice - $oldestPrice) / $oldestPrice;
    }

    protected function calculateAvailabilityRatio(ScrapedTicket $ticket): float
    {
        $totalForEvent = ScrapedTicket::where('event_name', 'LIKE', "%{$ticket->event_name}%")
            ->where('event_date', $ticket->event_date)
            ->sum('quantity');

        return $totalForEvent > 0 ? ($ticket->quantity ?? 0) / $totalForEvent : 0;
    }

    protected function encodePlatform(string $platform): int
    {
        $platformMap = [
            'stubhub'      => 1,
            'ticketmaster' => 2,
            'seatgeek'     => 3,
            'vivid_seats'  => 4,
            'gametime'     => 5,
        ];

        return $platformMap[strtolower($platform)] ?? 0;
    }

    protected function categorizeEventType(string $eventName): int
    {
        $eventName = strtolower($eventName);

        if (strpos($eventName, 'concert') !== FALSE) {
            return 1;
        }
        if (strpos($eventName, 'sports') !== FALSE) {
            return 2;
        }
        if (strpos($eventName, 'theater') !== FALSE) {
            return 3;
        }
        if (strpos($eventName, 'comedy') !== FALSE) {
            return 4;
        }

        return 0; // Other
    }

    protected function isPopularEvent(ScrapedTicket $ticket): bool
    {
        // Check if event appears across multiple platforms with high demand
        $crossPlatformCount = ScrapedTicket::where('event_name', 'LIKE', "%{$ticket->event_name}%")
            ->where('event_date', $ticket->event_date)
            ->distinct('platform')
            ->count();

        return $crossPlatformCount >= 3;
    }

    protected function getVenueCapacity(string $venue): int
    {
        // Mock venue capacity data - in real implementation, this would come from a venues database
        $venueCapacities = [
            'madison square garden' => 20000,
            'yankee stadium'        => 47000,
            'staples center'        => 20000,
            'wembley stadium'       => 90000,
        ];

        return $venueCapacities[strtolower($venue)] ?? 10000; // Default capacity
    }

    protected function calculateMarketDemand(ScrapedTicket $ticket): float
    {
        // Calculate based on search volume, social mentions, etc.
        // Mock implementation
        return rand(10, 90) / 100;
    }

    protected function calculateCompetitionLevel(ScrapedTicket $ticket): float
    {
        $competitorCount = ScrapedTicket::where('event_name', 'LIKE', "%{$ticket->event_name}%")
            ->where('event_date', $ticket->event_date)
            ->count();

        return min(1.0, $competitorCount / 10); // Normalize to 0-1
    }

    protected function getSeasonalFactor(Carbon $eventDate): float
    {
        $month = $eventDate->month;

        // Higher demand in certain months
        $seasonalMultipliers = [
            12 => 0.8, // December (holidays)
            1  => 0.6,  // January
            2  => 0.4,  // February
            3  => 0.5,  // March
            4  => 0.6,  // April
            5  => 0.7,  // May
            6  => 0.8,  // June
            7  => 0.9,  // July
            8  => 0.8,  // August
            9  => 0.7,  // September
            10 => 0.8, // October
            11 => 0.9,  // November
        ];

        return $seasonalMultipliers[$month] ?? 0.5;
    }

    protected function getSimilarEventsAveragePrice(ScrapedTicket $ticket): float
    {
        return ScrapedTicket::where('event_name', 'LIKE', "%{$ticket->event_name}%")
            ->where('id', '!=', $ticket->id)
            ->avg('price') ?? $ticket->price;
    }

    protected function calculateEventPopularityScore(ScrapedTicket $ticket): float
    {
        // Mock calculation based on social media mentions, search trends, etc.
        return rand(10, 95) / 100;
    }

    protected function isHolidayPeriod(Carbon $date): bool
    {
        $holidays = [
            ['start' => '12-20', 'end' => '01-05'], // Christmas/New Year
            ['start' => '07-01', 'end' => '07-07'], // July 4th week
            ['start' => '11-20', 'end' => '11-30'], // Thanksgiving
        ];

        foreach ($holidays as $holiday) {
            $start = Carbon::createFromFormat('m-d', $holiday['start'])->year($date->year);
            $end = Carbon::createFromFormat('m-d', $holiday['end'])->year($date->year);

            if ($end->lt($start)) {
                $end->addYear();
            }

            if ($date->between($start, $end)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function getPlatformReliability(string $platform): float
    {
        // Mock reliability scores - would come from actual metrics
        $reliabilityScores = [
            'stubhub'      => 0.95,
            'ticketmaster' => 0.98,
            'seatgeek'     => 0.92,
            'vivid_seats'  => 0.88,
            'gametime'     => 0.85,
        ];

        return $reliabilityScores[strtolower($platform)] ?? 0.80;
    }

    protected function loadModelWeights(): array
    {
        // In a real implementation, these would be loaded from a trained ML model
        return [
            'time_factor'         => 0.25,
            'price_factor'        => 0.20,
            'availability_factor' => 0.20,
            'demand_factor'       => 0.15,
            'platform_factor'     => 0.10,
            'seasonal_factor'     => 0.10,
        ];
    }
}
