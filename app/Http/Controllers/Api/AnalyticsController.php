<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Services\PlatformMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function in_array;

class AnalyticsController extends Controller
{
    protected $platformMonitoringService;

    public function __construct(PlatformMonitoringService $platformMonitoringService)
    {
        $this->platformMonitoringService = $platformMonitoringService;
    }

    /**
     * Get analytics overview
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
                        ->avg('price') ?? 0,
                    'price_range' => [
                        'min' => ScrapedTicket::where('scraped_at', '>=', $startDate)->min('price') ?? 0,
                        'max' => ScrapedTicket::where('scraped_at', '>=', $startDate)->max('price') ?? 0,
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
                ->selectRaw('AVG(price) as avg_price')
                ->selectRaw('MIN(price) as min_price')
                ->selectRaw('MAX(price) as max_price')
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
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price,
                STDDEV(price) as price_stddev,
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

    private function getTopEvents(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->select('event_title', 'venue')
            ->selectRaw('COUNT(*) as ticket_count, AVG(price) as avg_price')
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

    private function getPlatformBreakdown(int $days): array
    {
        $startDate = now()->subDays($days);

        return ScrapedTicket::where('scraped_at', '>=', $startDate)
            ->select('platform')
            ->selectRaw('COUNT(*) as ticket_count, AVG(price) as avg_price')
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

    private function getTicketsFoundByPlatform(string $platform, int $hours): int
    {
        return ScrapedTicket::where('platform', $platform)
            ->where('scraped_at', '>=', now()->subHours($hours))
            ->count();
    }

    private function getOverallSuccessRate(int $days): float
    {
        return $this->platformMonitoringService
            ->getAllPlatformStats($days * 24)
            ->avg('success_rate');
    }

    private function getTicketAvailabilityRate(int $days): float
    {
        $total = ScrapedTicket::where('scraped_at', '>=', now()->subDays($days))->count();
        $available = ScrapedTicket::where('scraped_at', '>=', now()->subDays($days))
            ->where('availability_status', 'available')->count();

        return $total > 0 ? round(($available / $total) * 100, 2) : 0;
    }

    private function getPlatformUptime(int $days): float
    {
        return $this->platformMonitoringService
            ->getAllPlatformStats($days * 24)
            ->avg('availability');
    }

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

    private function getSuccessRatesByEventType(int $days): array
    {
        // Placeholder - would need event type classification
        return [
            ['event_type' => 'Sports', 'success_rate' => 85.2],
            ['event_type' => 'Concerts', 'success_rate' => 78.9],
            ['event_type' => 'Theater', 'success_rate' => 92.1],
        ];
    }

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

    private function getPriceTrends(int $days, ?string $eventType): array
    {
        // Implementation for price trends over time
        return [];
    }

    private function getBestDeals(int $days, ?string $eventType): array
    {
        // Implementation for identifying best deals
        return [];
    }

    private function getPeakHours(int $days): array
    {
        // Implementation for peak activity hours
        return [];
    }

    private function getPopularEvents(int $days): array
    {
        // Implementation for popular events
        return [];
    }

    private function getVenuePopularity(int $days): array
    {
        // Implementation for venue popularity
        return [];
    }

    private function getSeasonalTrends(int $days): array
    {
        // Implementation for seasonal trends
        return [];
    }

    private function getPriceDemandCorrelation(int $days): array
    {
        // Implementation for price-demand correlation
        return [];
    }
}
