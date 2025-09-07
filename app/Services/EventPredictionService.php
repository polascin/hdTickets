<?php declare(strict_types=1);

namespace App\Services;

use App\Models\TicketSource;

use function array_slice;
use function count;

class EventPredictionService
{
    /**
     * Predict upcoming events based on historical patterns
     */
    /**
     * PredictUpcomingEvents
     */
    public function predictUpcomingEvents(array $criteria = []): array
    {
        $predictions = [];

        // Analyze seasonal patterns
        $seasonalPredictions = $this->analyzeSeasonalPatterns($criteria);
        $predictions['seasonal'] = $seasonalPredictions;

        // Analyze venue patterns
        $venuePredictions = $this->analyzeVenuePatterns($criteria);
        $predictions['venue_based'] = $venuePredictions;

        // Analyze artist/event type patterns
        $artistPredictions = $this->analyzeArtistPatterns($criteria);
        $predictions['artist_based'] = $artistPredictions;

        // Price trend analysis
        $priceTrends = $this->analyzePriceTrends($criteria);
        $predictions['price_trends'] = $priceTrends;

        return $predictions;
    }

    /**
     * Analyze seasonal event patterns
     */
    /**
     * AnalyzeSeasonalPatterns
     */
    protected function analyzeSeasonalPatterns(array $criteria): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Get historical data for same month in previous years
        $historicalData = TicketSource::whereMonth('event_date', $currentMonth)
            ->whereYear('event_date', '<', $currentYear)
            ->where('is_active', TRUE)
            ->get()
            ->groupBy(function ($event) {
                return $event->event_date->year;
            });

        $patterns = [];
        foreach ($historicalData as $year => $events) {
            $monthlyStats = [
                'year'           => $year,
                'total_events'   => $events->count(),
                'average_price'  => $events->avg('price_min'),
                'popular_venues' => $events->groupBy('venue')->map->count()->sortDesc()->take(5),
                'event_types'    => $this->categorizeEvents($events->toArray()),
            ];

            $patterns[] = $monthlyStats;
        }

        // Generate predictions based on patterns
        return $this->generateSeasonalPredictions($patterns);
    }

    /**
     * Analyze venue-specific patterns
     */
    /**
     * AnalyzeVenuePatterns
     */
    protected function analyzeVenuePatterns(array $criteria): array
    {
        $venueStats = TicketSource::where('is_active', TRUE)
            ->where('event_date', '>', now()->subYear())
            ->get()
            ->groupBy('venue')
            ->map(function ($events) {
                $avgInterval = $this->calculateAverageEventInterval($events);
                $lastEvent = $events->sortByDesc('event_date')->first();

                return [
                    'venue'                => $events->first()->venue,
                    'total_events'         => $events->count(),
                    'avg_interval_days'    => $avgInterval,
                    'last_event_date'      => $lastEvent->event_date,
                    'predicted_next_event' => $lastEvent->event_date->addDays($avgInterval),
                    'confidence'           => $this->calculateVenueConfidence($events),
                    'typical_price_range'  => [
                        'min' => $events->min('price_min'),
                        'max' => $events->max('price_max'),
                    ],
                ];
            })
            ->sortByDesc('confidence')
            ->values();

        return $venueStats->toArray();
    }

    /**
     * Analyze artist/performer patterns
     */
    /**
     * AnalyzeArtistPatterns
     */
    protected function analyzeArtistPatterns(array $criteria): array
    {
        // Extract artist names from event names (simplified approach)
        $events = TicketSource::where('is_active', TRUE)
            ->where('event_date', '>', now()->subYears(2))
            ->get();

        $artistPatterns = [];

        foreach ($events as $event) {
            $artistName = $this->extractArtistName($event->event_name);

            if (!isset($artistPatterns[$artistName])) {
                $artistPatterns[$artistName] = [
                    'events'       => [],
                    'venues'       => [],
                    'avg_interval' => 0,
                ];
            }

            $artistPatterns[$artistName]['events'][] = $event;
            $artistPatterns[$artistName]['venues'][] = $event->venue;
        }

        // Generate predictions for artists with enough data
        $predictions = [];
        foreach ($artistPatterns as $artist => $data) {
            if (count($data['events']) >= 3) { // Need at least 3 events for prediction
                $lastEvent = collect($data['events'])->sortByDesc('event_date')->first();
                $avgInterval = $this->calculateAverageEventInterval(collect($data['events']));

                $predictions[] = [
                    'artist'              => $artist,
                    'last_performance'    => $lastEvent->event_date,
                    'predicted_next_date' => $lastEvent->event_date->addDays($avgInterval),
                    'likely_venues'       => array_count_values($data['venues']),
                    'confidence'          => min(count($data['events']) / 10, 0.9), // Max 90% confidence
                ];
            }
        }

        return array_slice($predictions, 0, 20); // Return top 20 predictions
    }

    /**
     * Analyze price trends
     */
    /**
     * AnalyzePriceTrends
     */
    protected function analyzePriceTrends(array $criteria): array
    {
        $priceData = TicketSource::where('is_active', TRUE)
            ->where('price_min', '>', 0)
            ->where('event_date', '>', now()->subYear())
            ->selectRaw('
                DATE_FORMAT(event_date, "%Y-%m") as month,
                AVG(price_min) as avg_min_price,
                AVG(price_max) as avg_max_price,
                COUNT(*) as event_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $trends = [];
        $previousMonth = NULL;

        foreach ($priceData as $monthData) {
            $trend = [
                'month'         => $monthData->month,
                'avg_min_price' => round($monthData->avg_min_price, 2),
                'avg_max_price' => round($monthData->avg_max_price, 2),
                'event_count'   => $monthData->event_count,
                'price_change'  => NULL,
            ];

            if ($previousMonth) {
                $priceChange = (($monthData->avg_min_price - $previousMonth->avg_min_price) / $previousMonth->avg_min_price) * 100;
                $trend['price_change'] = round($priceChange, 2);
            }

            $trends[] = $trend;
            $previousMonth = $monthData;
        }

        // Predict next month's prices
        if (count($trends) >= 3) {
            $recentTrends = array_slice($trends, -3);
            $avgChange = collect($recentTrends)->avg('price_change');
            $lastTrend = end($trends);

            $predictions = [
                'next_month_prediction' => [
                    'month'                   => now()->addMonth()->format('Y-m'),
                    'predicted_avg_min_price' => round($lastTrend['avg_min_price'] * (1 + $avgChange / 100), 2),
                    'predicted_avg_max_price' => round($lastTrend['avg_max_price'] * (1 + $avgChange / 100), 2),
                    'confidence'              => min(abs($avgChange) < 5 ? 0.8 : 0.6, 0.9),
                ],
                'trends' => $trends,
            ];
        } else {
            $predictions = ['trends' => $trends];
        }

        return $predictions;
    }

    /**
     * Calculate average interval between events
     *
     * @param mixed $events
     */
    /**
     * CalculateAverageEventInterval
     *
     * @param mixed $events
     */
    protected function calculateAverageEventInterval($events): int
    {
        $events = collect($events)->sortBy('event_date');
        $intervals = [];

        for ($i = 1; $i < $events->count(); $i++) {
            $diff = $events[$i]->event_date->diffInDays($events[$i - 1]->event_date);
            $intervals[] = $diff;
        }

        return empty($intervals) ? 365 : round(array_sum($intervals) / count($intervals));
    }

    /**
     * Extract artist name from event title (simplified)
     */
    /**
     * ExtractArtistName
     */
    protected function extractArtistName(string $eventName): string
    {
        // Remove common words and extract likely artist name
        $cleanName = preg_replace('/\b(concert|live|tour|show|presents|featuring)\b/i', '', $eventName);
        $cleanName = preg_replace('/\s+/', ' ', trim($cleanName));

        // Take first part as artist name (simplified approach)
        $parts = explode(' - ', $cleanName);

        return trim($parts[0]);
    }

    /**
     * Categorize events by type
     */
    /**
     * CategorizeEvents
     */
    protected function categorizeEvents(array $events): array
    {
        $categories = [
            'music'   => 0,
            'sports'  => 0,
            'theater' => 0,
            'comedy'  => 0,
            'other'   => 0,
        ];

        foreach ($events as $event) {
            $eventName = strtolower($event['event_name'] ?? '');

            if (preg_match('/\b(concert|music|band|singer|acoustic|rock|pop|jazz|classical)\b/', $eventName)) {
                $categories['music']++;
            } elseif (preg_match('/\b(game|match|championship|league|tournament|vs|football|basketball|soccer)\b/', $eventName)) {
                $categories['sports']++;
            } elseif (preg_match('/\b(play|theater|theatre|musical|opera|ballet|drama)\b/', $eventName)) {
                $categories['theater']++;
            } elseif (preg_match('/\b(comedy|comedian|stand.?up|humor)\b/', $eventName)) {
                $categories['comedy']++;
            } else {
                $categories['other']++;
            }
        }

        return $categories;
    }

    /**
     * Calculate venue prediction confidence
     *
     * @param mixed $events
     */
    /**
     * CalculateVenueConfidence
     *
     * @param mixed $events
     */
    protected function calculateVenueConfidence($events): float
    {
        $eventCount = $events->count();
        $timeSpan = $events->max('event_date')->diffInDays($events->min('event_date'));

        // More events and longer time span = higher confidence
        $confidence = min(($eventCount / 10) * (min($timeSpan, 365) / 365), 0.95);

        return round($confidence, 2);
    }

    /**
     * Generate seasonal predictions based on historical patterns
     */
    /**
     * GenerateSeasonalPredictions
     */
    protected function generateSeasonalPredictions(array $patterns): array
    {
        if (empty($patterns)) {
            return [];
        }

        $avgEvents = collect($patterns)->avg('total_events');
        $avgPrice = collect($patterns)->avg('average_price');

        return [
            'predicted_events_this_month' => round($avgEvents),
            'predicted_avg_price'         => round($avgPrice, 2),
            'confidence'                  => count($patterns) >= 3 ? 0.75 : 0.5,
            'based_on_years'              => count($patterns),
            'historical_patterns'         => $patterns,
        ];
    }
}
