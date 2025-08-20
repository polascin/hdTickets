<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Services\PlatformMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function in_array;

class AnalyticsController extends Controller
{
    protected PlatformMonitoringService $platformMonitoringService;

    public function __construct(PlatformMonitoringService $platformMonitoringService)
    {
        $this->platformMonitoringService = $platformMonitoringService;
    }

    /**
     * Get analytics overview
     */
    /**
     * Overview
     */
    public function overview(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '7d');
        $cacheKey = "analytics_overview_{$timeframe}";

        $data = Cache::remember($cacheKey, 600, function () use ($timeframe) {
            $days = $this->getTimeframeDays($timeframe);
            $startDate = now()->subDays($days);

            return [
                'summary' => [
                    'total_tickets_found' => ScrapedTicket::where('scraped_at', '>=', $startDate)->count(),
                    'unique_events'       => ScrapedTicket::where('scraped_at', '>=', $startDate)
                        ->distinct('event_title', 'venue')->count(),
                    'platforms_monitored' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                        ->distinct('platform')->count(),
                    'avg_price' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                        ->selectRaw('AVG((min_price + max_price) / 2) as avg_price')
                        ->value('avg_price') ?? 0,
                    'price_range' => [
                        'min' => ScrapedTicket::where('scraped_at', '>=', $startDate)->min('min_price') ?? 0,
                        'max' => ScrapedTicket::where('scraped_at', '>=', $startDate)->max('max_price') ?? 0,
                    ],
                ],
                'trends'             => $this->getTicketTrends($days),
                'top_events'         => $this->getTopEvents($days),
                'platform_breakdown' => $this->getPlatformBreakdown($days),
            ];
        });

        return response()->json([
            'data'         => $data,
            'timeframe'    => $timeframe,
            'generated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get ticket trends over time
     */
    /**
     * TicketTrends
     */
    public function ticketTrends(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '30d');
        $groupBy = $request->get('group_by', 'day');

        $cacheKey = "ticket_trends_{$timeframe}_{$groupBy}";

        $data = Cache::remember($cacheKey, 900, function () use ($timeframe, $groupBy) {
            $days = $this->getTimeframeDays($timeframe);
            $startDate = now()->subDays($days);

            $dateFormat = $groupBy === 'hour' ? '%Y-%m-%d %H:00:00' : '%Y-%m-%d';

            $trends = ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->selectRaw("DATE_FORMAT(scraped_at, '{$dateFormat}') AS period")
                ->selectRaw('COUNT(*) as tickets_found')
                ->selectRaw('COUNT(DISTINCT event_title) as unique_events')
                ->selectRaw('AVG((min_price + max_price) / 2) as avg_price')
                ->selectRaw('MIN(min_price) as min_price')
                ->selectRaw('MAX(max_price) as max_price')
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return $trends->map(function ($trend) {
                return [
                    'period'        => $trend->period,
                    'tickets_found' => (int) $trend->tickets_found,
                    'unique_events' => (int) $trend->unique_events,
                    'avg_price'     => round($trend->avg_price, 2),
                    'min_price'     => round($trend->min_price, 2),
                    'max_price'     => round($trend->max_price, 2),
                ];
            });
        });

        return response()->json([
            'data'      => $data,
            'timeframe' => $timeframe,
            'group_by'  => $groupBy,
        ]);
    }

    /**
     * Get platform performance metrics
     */
    /**
     * PlatformPerformance
     */
    public function platformPerformance(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '24h');
        $hours = $timeframe === '24h' ? 24 : ($timeframe === '7d' ? 168 : 720);

        $cacheKey = "platform_performance_{$timeframe}";

        $data = Cache::remember($cacheKey, 300, function () use ($hours) {
            $platformStats = $this->platformMonitoringService->getAllPlatformStats($hours);

            return $platformStats->map(function ($stats) {
                return [
                    'platform'            => $stats['platform'],
                    'success_rate'        => round($stats['success_rate'], 2),
                    'avg_response_time'   => round($stats['avg_response_time'], 2),
                    'total_requests'      => $stats['total_requests'],
                    'successful_requests' => $stats['successful_requests'],
                    'failed_requests'     => $stats['failed_requests'],
                    'uptime_percentage'   => round($stats['availability'], 2),
                    'last_success'        => $stats['last_success'],
                    'status'              => $this->determinePlatformStatus($stats),
                    'tickets_found'       => $this->getTicketsFoundByPlatform($stats['platform'], $hours),
                ];
            });
        });

        return response()->json([
            'data'      => $data,
            'timeframe' => $timeframe,
        ]);
    }

    /**
     * Get success rates by various metrics
     */
    /**
     * SuccessRates
     */
    public function successRates(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '7d');
        $cacheKey = "success_rates_{$timeframe}";

        $data = Cache::remember($cacheKey, 600, function () use ($timeframe) {
            $days = $this->getTimeframeDays($timeframe);
            $startDate = now()->subDays($days);

            return [
                'overall' => [
                    'scraping_success_rate'    => $this->getOverallSuccessRate($days),
                    'ticket_availability_rate' => $this->getTicketAvailabilityRate($days),
                    'platform_uptime'          => $this->getPlatformUptime($days),
                ],
                'by_platform'    => $this->getSuccessRatesByPlatform($days),
                'by_event_type'  => $this->getSuccessRatesByEventType($days),
                'by_time_of_day' => $this->getSuccessRatesByTimeOfDay($days),
            ];
        });

        return response()->json([
            'data'      => $data,
            'timeframe' => $timeframe,
        ]);
    }

    /**
     * Get price analysis data
     */
    /**
     * PriceAnalysis
     */
    public function priceAnalysis(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '30d');
        $eventType = $request->get('event_type');

        $cacheKey = "price_analysis_{$timeframe}" . ($eventType ? "_{$eventType}" : '');

        $data = Cache::remember($cacheKey, 900, function () use ($timeframe, $eventType) {
            $days = $this->getTimeframeDays($timeframe);
            $startDate = now()->subDays($days);

            $query = ScrapedTicket::where('scraped_at', '>=', $startDate);

            if ($eventType) {
                $query->where('event_category', $eventType);
            }

            $priceStats = $query->selectRaw('
                AVG((min_price + max_price) / 2) as avg_price,
                MIN(min_price) as min_price,
                MAX(max_price) as max_price,
                STDDEV((min_price + max_price) / 2) as price_stddev,
                COUNT(*) as total_tickets
            ')->first();

            return [
                'summary' => [
                    'average_price'    => round($priceStats->avg_price ?? 0, 2),
                    'minimum_price'    => round($priceStats->min_price ?? 0, 2),
                    'maximum_price'    => round($priceStats->max_price ?? 0, 2),
                    'price_volatility' => round($priceStats->price_stddev ?? 0, 2),
                    'total_tickets'    => $priceStats->total_tickets ?? 0,
                ],
                'price_ranges' => $this->getPriceRangeDistribution($days, $eventType),
                'price_trends' => $this->getPriceTrends($days, $eventType),
                'best_deals'   => $this->getBestDeals($days, $eventType),
            ];
        });

        return response()->json([
            'data'       => $data,
            'timeframe'  => $timeframe,
            'event_type' => $eventType,
        ]);
    }

    /**
     * Get demand patterns analysis
     */
    /**
     * DemandPatterns
     */
    public function demandPatterns(Request $request): JsonResponse
    {
        $timeframe = $request->get('timeframe', '30d');
        $cacheKey = "demand_patterns_{$timeframe}";

        $data = Cache::remember($cacheKey, 1800, function () use ($timeframe) {
            $days = $this->getTimeframeDays($timeframe);

            return [
                'peak_hours'               => $this->getPeakHours($days),
                'popular_events'           => $this->getPopularEvents($days),
                'venue_popularity'         => $this->getVenuePopularity($days),
                'seasonal_trends'          => $this->getSeasonalTrends($days),
                'price_demand_correlation' => $this->getPriceDemandCorrelation($days),
            ];
        });

        return response()->json([
            'data'      => $data,
            'timeframe' => $timeframe,
        ]);
    }

    /**
     * Export analytics data
     */
    /**
     * Export
     */
    public function export(Request $request, string $type): JsonResponse
    {
        $allowedTypes = ['overview', 'trends', 'platforms', 'prices', 'demand'];

        if (! in_array($type, $allowedTypes, TRUE)) {
            return response()->json(['error' => 'Invalid export type'], 400);
        }

        $timeframe = $request->get('timeframe', '30d');
        $format = $request->get('format', 'json');

        // Generate export data based on type
        $data = match ($type) {
            'overview'  => $this->overview($request)->getData()->data,
            'trends'    => $this->ticketTrends($request)->getData()->data,
            'platforms' => $this->platformPerformance($request)->getData()->data,
            'prices'    => $this->priceAnalysis($request)->getData()->data,
            'demand'    => $this->demandPatterns($request)->getData()->data,
        };

        return response()->json([
            'export_type'  => $type,
            'format'       => $format,
            'timeframe'    => $timeframe,
            'generated_at' => now()->toISOString(),
            'data'         => $data,
        ]);
    }

    // Private helper methods

    /**
     * Get  timeframe days
     */
    private function getTimeframeDays(string $timeframe): int
    {
        return match ($timeframe) {
            '24h'   => 1,
            '7d'    => 7,
            '30d'   => 30,
            '90d'   => 90,
            default => 7,
        };
    }

    /**
     * Get  ticket trends
     */
    private function getTicketTrends(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->selectRaw('DATE(scraped_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date'          => $item->date,
                'tickets_found' => (int) $item->count,
            ])
            ->toArray();
    }

    /**
     * Get  top events
     */
    private function getTopEvents(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->select('event_title', 'venue')
            ->selectRaw('COUNT(*) as ticket_count, AVG((min_price + max_price) / 2) as avg_price')
            ->groupBy('event_title', 'venue')
            ->orderBy('ticket_count', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'event_title'  => $item->event_title,
                'venue'        => $item->venue,
                'ticket_count' => (int) $item->ticket_count,
                'avg_price'    => round($item->avg_price, 2),
            ])
            ->toArray();
    }

    /**
     * Get  platform breakdown
     */
    private function getPlatformBreakdown(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->select('platform')
            ->selectRaw('COUNT(*) as ticket_count, AVG((min_price + max_price) / 2) as avg_price')
            ->groupBy('platform')
            ->orderBy('ticket_count', 'desc')
            ->get()
            ->map(fn ($item) => [
                'platform'     => $item->platform,
                'ticket_count' => (int) $item->ticket_count,
                'avg_price'    => round($item->avg_price, 2),
            ])
            ->toArray();
    }

    /**
     * DeterminePlatformStatus
     */
    private function determinePlatformStatus(array $stats): string
    {
        if ($stats['success_rate'] >= 80) {
            return 'healthy';
        }
        if ($stats['success_rate'] >= 50) {
            return 'warning';
        }

        return 'critical';
    }

    /**
     * Get  tickets found by platform
     */
    private function getTicketsFoundByPlatform(string $platform, int $hours): int
    {
        return ScrapedTicket::where('platform', $platform)
            ->where('scraped_at', '>=', now()->subHours($hours))
            ->count();
    }

    /**
     * Get  overall success rate
     */
    private function getOverallSuccessRate(int $days): float
    {
        return $this->platformMonitoringService
            ->getAllPlatformStats($days * 24)
            ->avg('success_rate');
    }

    /**
     * Get  ticket availability rate
     */
    private function getTicketAvailabilityRate(int $days): float
    {
        $total = ScrapedTicket::where('scraped_at', '>=', now()->subDays($days))->count();
        $available = ScrapedTicket::where('scraped_at', '>=', now()->subDays($days))
            ->where('availability_status', 'available')->count();

        return $total > 0 ? round(($available / $total) * 100, 2) : 0;
    }

    /**
     * Get  platform uptime
     */
    private function getPlatformUptime(int $days): float
    {
        return $this->platformMonitoringService
            ->getAllPlatformStats($days * 24)
            ->avg('availability');
    }

    /**
     * Get  success rates by platform
     */
    private function getSuccessRatesByPlatform(int $days): array
    {
        return $this->platformMonitoringService
            ->getAllPlatformStats($days * 24)
            ->map(fn ($stats) => [
                'platform'     => $stats['platform'],
                'success_rate' => round($stats['success_rate'], 2),
            ])
            ->toArray();
    }

    /**
     * Get  success rates by event type
     */
    private function getSuccessRatesByEventType(int $days): array
    {
        // Placeholder - would need event type classification
        return [
            ['event_type' => 'Sports', 'success_rate' => 85.2],
            ['event_type' => 'Concerts', 'success_rate' => 78.9],
            ['event_type' => 'Theater', 'success_rate' => 92.1],
        ];
    }

    /**
     * Get  success rates by time of day
     */
    private function getSuccessRatesByTimeOfDay(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->selectRaw('HOUR(scraped_at) as hour, COUNT(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(fn ($item) => [
                'hour'           => (int) $item->hour,
                'activity_level' => (int) $item->total,
            ])
            ->toArray();
    }

    // Additional helper methods for price analysis and demand patterns
    /**
     * Get  price range distribution
     */
    private function getPriceRangeDistribution(int $days, ?string $eventType): array
    {
        // Implementation for price range distribution
        return [
            ['range' => '$0-50', 'count' => 1250],
            ['range' => '$51-100', 'count' => 2340],
            ['range' => '$101-250', 'count' => 1890],
            ['range' => '$251-500', 'count' => 980],
            ['range' => '$500+', 'count' => 540],
        ];
    }

    /**
     * Get  price trends
     */
    private function getPriceTrends(int $days, ?string $eventType): array
    {
        // Implementation for price trends over time
        return [];
    }

    /**
     * Get  best deals
     */
    private function getBestDeals(int $days, ?string $eventType): array
    {
        // Implementation for identifying best deals
        return [];
    }

    /**
     * Get  peak hours
     */
    private function getPeakHours(int $days): array
    {
        // Implementation for peak activity hours
        return [];
    }

    /**
     * Get  popular events
     */
    private function getPopularEvents(int $days): array
    {
        // Implementation for popular events
        return [];
    }

    /**
     * Get  venue popularity
     */
    private function getVenuePopularity(int $days): array
    {
        // Implementation for venue popularity
        return [];
    }

    /**
     * Get  seasonal trends
     */
    private function getSeasonalTrends(int $days): array
    {
        // Implementation for seasonal trends
        return [];
    }

    /**
     * Get  price demand correlation
     */
    private function getPriceDemandCorrelation(int $days): array
    {
        // Implementation for price-demand correlation
        return [];
    }

    /**
     * Receive analytics events from the frontend
     */
    public function receiveEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event' => 'required|string|max:100',
            'data' => 'required|array',
            'timestamp' => 'required|date_format:Y-m-d\TH:i:s.v\Z'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid event data',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $this->processAnalyticsEvent($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Event recorded'
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics event processing failed', [
                'error' => $e->getMessage(),
                'event_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Event processing failed'
            ], 500);
        }
    }

    /**
     * Process analytics event
     */
    private function processAnalyticsEvent(array $eventData): void
    {
        $event = $eventData['event'];
        $data = $eventData['data'];
        $timestamp = $eventData['timestamp'];

        // Log to application logs for debugging
        Log::info('Analytics Event', [
            'event' => $event,
            'category' => $data['event_category'] ?? 'unknown',
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'timestamp' => $timestamp
        ]);

        // Store in cache for real-time analytics
        $this->updateRealTimeStats($event, $data);

        // Process specific event types
        switch ($data['event_category']) {
            case 'sports_interaction':
                $this->processSportsEvent($event, $data);
                break;
            case 'performance':
                $this->processPerformanceEvent($event, $data);
                break;
            case 'error_tracking':
                $this->processErrorEvent($event, $data);
                break;
            case 'conversion':
                $this->processConversionEvent($event, $data);
                break;
        }
    }

    /**
     * Update real-time statistics
     */
    private function updateRealTimeStats(string $event, array $data): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('H');
        
        // Increment counters
        Cache::increment("analytics:events:$today", 1, 86400);
        Cache::increment("analytics:events:$today:$hour", 1, 3600);
        Cache::increment("analytics:event_types:$event:$today", 1, 86400);
        
        // Track unique sessions
        if (isset($data['session_id'])) {
            $sessionsKey = "analytics:sessions:$today";
            $sessions = Cache::get($sessionsKey, []);
            if (!in_array($data['session_id'], $sessions)) {
                $sessions[] = $data['session_id'];
                Cache::put($sessionsKey, $sessions, 86400);
            }
        }
    }

    /**
     * Process sports-specific events
     */
    private function processSportsEvent(string $event, array $data): void
    {
        $sport = $data['sport_type'] ?? null;
        $team = $data['team_preference'] ?? null;
        
        if ($sport) {
            Cache::increment("analytics:sports:$sport:" . now()->format('Y-m-d'), 1, 86400);
        }
        
        if ($team) {
            Cache::increment("analytics:teams:$team:" . now()->format('Y-m-d'), 1, 86400);
        }
    }

    /**
     * Process performance events
     */
    private function processPerformanceEvent(string $event, array $data): void
    {
        $value = $data['value'] ?? 0;
        $today = now()->format('Y-m-d');
        
        // Store performance metrics
        $metricsKey = "analytics:performance:$event:$today";
        $metrics = Cache::get($metricsKey, []);
        $metrics[] = $value;
        
        // Keep only last 100 measurements
        if (count($metrics) > 100) {
            $metrics = array_slice($metrics, -100);
        }
        
        Cache::put($metricsKey, $metrics, 86400);
    }

    /**
     * Process error events
     */
    private function processErrorEvent(string $event, array $data): void
    {
        $errorType = $data['error_type'] ?? 'unknown';
        $today = now()->format('Y-m-d');
        
        // Log error for monitoring
        Log::warning('Frontend Error Tracked', [
            'error_type' => $errorType,
            'error_message' => $data['error_message'] ?? 'No message',
            'user_agent' => request()->userAgent(),
            'url' => $data['page_location'] ?? 'unknown'
        ]);
        
        // Increment error counters
        Cache::increment("analytics:errors:$errorType:$today", 1, 86400);
        Cache::increment("analytics:errors:total:$today", 1, 86400);
    }

    /**
     * Process conversion events
     */
    private function processConversionEvent(string $event, array $data): void
    {
        $funnelName = $data['funnel_name'] ?? 'unknown';
        $stepNumber = $data['step_number'] ?? 0;
        $today = now()->format('Y-m-d');
        
        // Track funnel progression
        Cache::increment("analytics:funnel:$funnelName:step_$stepNumber:$today", 1, 86400);
    }

    /**
     * Get analytics dashboard data for frontend
     */
    public function getDashboardData(): JsonResponse
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        
        return response()->json([
            'events' => [
                'today' => Cache::get("analytics:events:$today", 0),
                'yesterday' => Cache::get("analytics:events:$yesterday", 0)
            ],
            'sessions' => [
                'today' => count(Cache::get("analytics:sessions:$today", [])),
                'yesterday' => count(Cache::get("analytics:sessions:$yesterday", []))
            ],
            'errors' => [
                'today' => Cache::get("analytics:errors:total:$today", 0),
                'yesterday' => Cache::get("analytics:errors:total:$yesterday", 0)
            ],
            'top_sports' => $this->getTopSportsAnalytics($today),
            'performance_metrics' => $this->getPerformanceMetricsAnalytics($today)
        ]);
    }

    /**
     * Get top sports by interaction
     */
    private function getTopSportsAnalytics(string $date): array
    {
        $sports = ['football', 'basketball', 'baseball', 'hockey', 'soccer', 'tennis'];
        $sportData = [];
        
        foreach ($sports as $sport) {
            $count = Cache::get("analytics:sports:$sport:$date", 0);
            if ($count > 0) {
                $sportData[] = [
                    'sport' => $sport,
                    'interactions' => $count
                ];
            }
        }
        
        // Sort by interactions
        usort($sportData, function($a, $b) {
            return $b['interactions'] - $a['interactions'];
        });
        
        return array_slice($sportData, 0, 5);
    }

    /**
     * Get performance metrics summary
     */
    private function getPerformanceMetricsAnalytics(string $date): array
    {
        $metrics = ['web_vitals_lcp', 'web_vitals_fid', 'web_vitals_cls'];
        $performanceData = [];
        
        foreach ($metrics as $metric) {
            $values = Cache::get("analytics:performance:$metric:$date", []);
            if (!empty($values)) {
                $performanceData[$metric] = [
                    'average' => round(array_sum($values) / count($values), 2),
                    'min' => min($values),
                    'max' => max($values),
                    'count' => count($values)
                ];
            }
        }
        
        return $performanceData;
    }
}
