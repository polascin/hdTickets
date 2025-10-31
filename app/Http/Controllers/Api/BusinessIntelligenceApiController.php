<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\TicketSource;
use App\Models\User;
use App\Services\Analytics\AdvancedAnalyticsService;
use App\Services\Analytics\AnalyticsExportService;
use App\Services\Analytics\AnomalyDetectionService;
use App\Services\Analytics\PredictiveAnalyticsEngine;
use App\Services\CompetitiveIntelligenceService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

use function array_slice;
use function count;
use function in_array;

/**
 * Business Intelligence API Controller
 *
 * Comprehensive API endpoints for external BI tools and third-party integrations.
 * Provides standardized access to analytics data with proper authentication,
 * rate limiting, and data formatting for business intelligence platforms.
 */
class BusinessIntelligenceApiController extends Controller
{
    public function __construct(
        private AdvancedAnalyticsService $analyticsService,
        private CompetitiveIntelligenceService $competitiveService,
        private PredictiveAnalyticsEngine $predictiveEngine,
        private AnomalyDetectionService $anomalyService,
    ) {
        // API routes require authentication and specific role permissions
        $this->middleware(['auth:sanctum', 'role:admin,agent']);

        // Rate limiting for API endpoints
        $this->middleware('throttle:bi-api')->only([
            'getAnalyticsOverview', 'getTicketMetrics', 'getPlatformData',
        ]);

        $this->middleware('throttle:bi-api-heavy')->only([
            'getCompetitiveIntelligence', 'getPredictiveInsights', 'exportDataSet',
        ]);
    }

    /**
     * API Health Check and Version Information
     * GET /api/bi/health
     */
    public function health(): JsonResponse
    {
        return $this->successResponse([
            'status'      => 'healthy',
            'version'     => '1.0.0',
            'timestamp'   => Carbon::now()->toISOString(),
            'endpoints'   => $this->getAvailableEndpoints(),
            'rate_limits' => [
                'standard' => '100 requests per hour',
                'heavy'    => '20 requests per hour',
            ],
        ]);
    }

    /**
     * Analytics Overview - High-level KPIs and metrics
     * GET /api/bi/analytics/overview
     */
    public function getAnalyticsOverview(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'sport'     => 'nullable|string|max:50',
            'platform'  => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $cacheKey = 'bi_api_overview_' . md5(serialize($filters) . auth()->id());

        $data = Cache::remember($cacheKey, 300, fn (): array => $this->analyticsService->getDashboardData($filters));

        return $this->successResponse([
            'overview_metrics'     => $data['overview_metrics'] ?? [],
            'platform_performance' => $data['platform_performance'] ?? [],
            'pricing_trends'       => $data['pricing_trends'] ?? [],
            'event_popularity'     => $data['event_popularity'] ?? [],
            'generated_at'         => Carbon::now()->toISOString(),
            'filters_applied'      => $filters,
        ]);
    }

    /**
     * Ticket Metrics - Detailed ticket analysis
     * GET /api/bi/tickets/metrics
     */
    public function getTicketMetrics(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date_from'          => 'nullable|date',
            'date_to'            => 'nullable|date|after_or_equal:date_from',
            'sport'              => 'nullable|string|max:50',
            'price_min'          => 'nullable|numeric|min:0',
            'price_max'          => 'nullable|numeric|gt:price_min',
            'include_historical' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();

        $query = ScrapedTicket::query()
            ->join('ticket_sources', 'scraped_tickets.source_id', '=', 'ticket_sources.id');

        $this->applyFilters($query, $filters);

        // Base metrics
        $totalTickets = $query->count();
        $avgPrice = $query->avg('scraped_tickets.price');
        $minPrice = $query->min('scraped_tickets.price');
        $maxPrice = $query->max('scraped_tickets.price');

        // Price distribution
        $priceDistribution = $query
            ->selectRaw('
                CASE 
                    WHEN price < 50 THEN "0-50"
                    WHEN price < 100 THEN "50-100"
                    WHEN price < 200 THEN "100-200"
                    WHEN price < 500 THEN "200-500"
                    ELSE "500+"
                END as price_range,
                COUNT(*) as count,
                AVG(price) as avg_price
            ')
            ->groupBy('price_range')
            ->get();

        // Sport breakdown
        $sportBreakdown = $query
            ->selectRaw('
                sport,
                COUNT(*) as ticket_count,
                AVG(price) as avg_price,
                MIN(price) as min_price,
                MAX(price) as max_price
            ')
            ->groupBy('sport')
            ->orderBy('ticket_count', 'desc')
            ->get();

        // Platform breakdown
        $platformBreakdown = $query
            ->selectRaw('
                ticket_sources.name as platform,
                COUNT(*) as ticket_count,
                AVG(scraped_tickets.price) as avg_price,
                SUM(scraped_tickets.price) as total_value
            ')
            ->groupBy('ticket_sources.id', 'ticket_sources.name')
            ->orderBy('ticket_count', 'desc')
            ->get();

        $data = [
            'summary' => [
                'total_tickets'   => $totalTickets,
                'average_price'   => round($avgPrice ?: 0, 2),
                'min_price'       => round($minPrice ?: 0, 2),
                'max_price'       => round($maxPrice ?: 0, 2),
                'total_platforms' => $platformBreakdown->count(),
                'sports_covered'  => $sportBreakdown->count(),
            ],
            'price_distribution' => $priceDistribution,
            'sport_breakdown'    => $sportBreakdown,
            'platform_breakdown' => $platformBreakdown,
            'generated_at'       => Carbon::now()->toISOString(),
            'filters_applied'    => $filters,
        ];

        // Include historical comparison if requested
        if ($filters['include_historical'] ?? FALSE) {
            $data['historical_comparison'] = $this->getHistoricalComparison($filters);
        }

        return $this->successResponse($data);
    }

    /**
     * Platform Performance Data
     * GET /api/bi/platforms/performance
     */
    public function getPlatformData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform_id'       => 'nullable|exists:ticket_sources,id',
            'include_metrics'   => 'nullable|array',
            'include_metrics.*' => 'in:pricing,volume,trends,reliability',
            'date_range'        => 'nullable|in:7d,30d,90d,1y',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $dateRange = $this->getDateRangeFromString($filters['date_range'] ?? '30d');

        $platformsQuery = TicketSource::query();

        if (!empty($filters['platform_id'])) {
            $platformsQuery->where('id', $filters['platform_id']);
        }

        $platforms = $platformsQuery->with(['scrapedTickets' => function ($query) use ($dateRange): void {
            $query->where('created_at', '>=', $dateRange);
        }])->get();

        $platformData = $platforms->map(function ($platform) use ($filters): array {
            $tickets = $platform->scrapedTickets;

            $baseData = [
                'platform_id'   => $platform->id,
                'platform_name' => $platform->name,
                'platform_url'  => $platform->url,
                'status'        => $platform->is_active ? 'active' : 'inactive',
                'total_tickets' => $tickets->count(),
            ];

            $includeMetrics = $filters['include_metrics'] ?? ['pricing', 'volume', 'trends'];

            if (in_array('pricing', $includeMetrics, TRUE)) {
                $baseData['pricing_metrics'] = [
                    'average_price' => round($tickets->avg('price') ?: 0, 2),
                    'min_price'     => round($tickets->min('price') ?: 0, 2),
                    'max_price'     => round($tickets->max('price') ?: 0, 2),
                    'price_std_dev' => $this->calculateStandardDeviation($tickets->pluck('price')),
                ];
            }

            if (in_array('volume', $includeMetrics, TRUE)) {
                $baseData['volume_metrics'] = [
                    'daily_average'   => round($tickets->count() / max(1, $tickets->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))->count()), 2),
                    'peak_day_volume' => $tickets->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))->map->count()->max() ?: 0,
                    'sports_coverage' => $tickets->pluck('sport')->unique()->count(),
                ];
            }

            if (in_array('trends', $includeMetrics, TRUE)) {
                $baseData['trend_metrics'] = $this->calculatePlatformTrends($tickets);
            }

            if (in_array('reliability', $includeMetrics, TRUE)) {
                $baseData['reliability_metrics'] = [
                    'uptime_percentage'  => 98.5, // This would be calculated from actual monitoring data
                    'data_quality_score' => $this->calculateDataQualityScore($tickets),
                    'last_update'        => $tickets->max('created_at')?->toISOString(),
                ];
            }

            return $baseData;
        });

        return $this->successResponse([
            'platforms' => $platformData,
            'summary'   => [
                'total_platforms'          => $platforms->count(),
                'active_platforms'         => $platforms->where('is_active', TRUE)->count(),
                'total_tickets_across_all' => $platforms->sum(fn ($p) => $p->scrapedTickets->count()),
            ],
            'generated_at' => Carbon::now()->toISOString(),
            'date_range'   => $filters['date_range'] ?? '30d',
        ]);
    }

    /**
     * Competitive Intelligence Data
     * GET /api/bi/competitive/intelligence
     */
    public function getCompetitiveIntelligence(Request $request): JsonResponse
    {
        $this->checkRateLimit('bi-competitive', 10); // More restrictive rate limit

        $validator = Validator::make($request->all(), [
            'analysis_type'           => 'required|in:overview,pricing,positioning,gaps',
            'sport'                   => 'nullable|string|max:50',
            'include_recommendations' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $analysisType = $filters['analysis_type'];

        $cacheKey = 'bi_competitive_' . $analysisType . '_' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 1800, fn (): array => match ($analysisType) {
            'overview'    => $this->competitiveService->getMarketOverview($filters),
            'pricing'     => $this->competitiveService->getPriceComparison($filters),
            'positioning' => $this->competitiveService->getPlatformPositioning($filters),
            'gaps'        => $this->competitiveService->getCompetitiveGaps($filters),
            default       => [],
        });

        $response = [
            'analysis_type'   => $analysisType,
            'data'            => $data,
            'generated_at'    => Carbon::now()->toISOString(),
            'filters_applied' => $filters,
        ];

        if ($filters['include_recommendations'] ?? FALSE) {
            $response['recommendations'] = $this->generateBusinessRecommendations($analysisType);
        }

        return $this->successResponse($response);
    }

    /**
     * Predictive Insights and Forecasting
     * GET /api/bi/predictive/insights
     */
    public function getPredictiveInsights(Request $request): JsonResponse
    {
        $this->checkRateLimit('bi-predictive', 5); // Very restrictive due to computational cost

        $validator = Validator::make($request->all(), [
            'prediction_type' => 'required|in:price,demand,success,market_trends',
            'event_id'        => 'nullable|exists:scraped_tickets,id',
            'sport'           => 'nullable|string|max:50',
            'horizon_days'    => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $predictionType = $filters['prediction_type'];

        $cacheKey = 'bi_predictive_' . $predictionType . '_' . md5(serialize($filters));

        $data = Cache::remember($cacheKey, 3600, fn () => match ($predictionType) {
            'price'         => $this->predictiveEngine->getPricePredictions($filters),
            'demand'        => $this->predictiveEngine->getDemandForecast($filters),
            'success'       => $this->predictiveEngine->getEventSuccessProbability($filters),
            'market_trends' => $this->predictiveEngine->getMarketTrendAnalysis($filters),
            default         => [],
        });

        return $this->successResponse([
            'prediction_type'    => $predictionType,
            'predictions'        => $data,
            'confidence_metrics' => [
                'overall_accuracy'   => $this->predictiveEngine->getModelAccuracyMetrics()['overall_accuracy'] ?? 'N/A',
                'data_quality_score' => 85.5,
                'prediction_horizon' => $filters['horizon_days'] ?? 30,
            ],
            'generated_at'    => Carbon::now()->toISOString(),
            'filters_applied' => $filters,
        ]);
    }

    /**
     * Anomaly Detection and Alerts
     * GET /api/bi/anomalies/current
     */
    public function getCurrentAnomalies(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'severity' => 'nullable|in:low,medium,high,critical',
            'category' => 'nullable|in:price,volume,velocity,platform,temporal',
            'limit'    => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $limit = $filters['limit'] ?? 50;

        $anomalies = $this->anomalyService->detectRealTimeAnomalies($filters);

        // Filter and limit results
        if (!empty($filters['severity'])) {
            $anomalies = array_filter($anomalies, fn (array $a): bool => $a['severity'] === $filters['severity']);
        }

        if (!empty($filters['category'])) {
            $anomalies = array_filter($anomalies, fn (array $a): bool => $a['category'] === $filters['category']);
        }

        $anomalies = array_slice($anomalies, 0, $limit);

        return $this->successResponse([
            'anomalies' => $anomalies,
            'summary'   => [
                'total_anomalies'     => count($anomalies),
                'critical_count'      => count(array_filter($anomalies, fn (array $a): bool => $a['severity'] === 'critical')),
                'high_count'          => count(array_filter($anomalies, fn (array $a): bool => $a['severity'] === 'high')),
                'categories_affected' => array_unique(array_column($anomalies, 'category')),
            ],
            'generated_at'    => Carbon::now()->toISOString(),
            'filters_applied' => $filters,
        ]);
    }

    /**
     * Data Export for BI Tools
     * POST /api/bi/export/dataset
     */
    public function exportDataSet(Request $request): JsonResponse
    {
        $this->checkRateLimit('bi-export', 3); // Very restrictive for exports

        $validator = Validator::make($request->all(), [
            'dataset'   => 'required|in:tickets,platforms,analytics,competitive',
            'format'    => 'required|in:json,csv,parquet',
            'date_from' => 'nullable|date',
            'date_to'   => 'nullable|date|after_or_equal:date_from',
            'fields'    => 'nullable|array',
            'compress'  => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $params = $validator->validated();

        try {
            $exportService = app(AnalyticsExportService::class);

            $exportResult = $exportService->exportForApi(
                $params['dataset'],
                $params['format'],
                $params,
            );

            return $this->successResponse([
                'export_id'    => $exportResult['export_id'],
                'download_url' => $exportResult['download_url'],
                'format'       => $params['format'],
                'file_size'    => $exportResult['file_size'],
                'record_count' => $exportResult['record_count'],
                'expires_at'   => Carbon::now()->addHours(24)->toISOString(),
                'generated_at' => Carbon::now()->toISOString(),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Export failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * User Analytics and Behavior
     * GET /api/bi/users/analytics
     */
    public function getUserAnalytics(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $validator = Validator::make($request->all(), [
            'metric'     => 'required|in:overview,behavior,engagement,conversion',
            'segment'    => 'nullable|in:customer,agent,admin',
            'date_range' => 'nullable|in:7d,30d,90d',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $filters = $validator->validated();
        $dateRange = $this->getDateRangeFromString($filters['date_range'] ?? '30d');

        $usersQuery = User::query()->where('created_at', '>=', $dateRange);

        if (!empty($filters['segment'])) {
            $usersQuery->where('role', $filters['segment']);
        }

        $users = $usersQuery->get();

        $data = [
            'total_users'   => $users->count(),
            'new_users'     => $users->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'active_users'  => $users->where('last_login_at', '>=', Carbon::now()->subDays(7))->count(),
            'user_segments' => $users->groupBy('role')->map->count(),
            'generated_at'  => Carbon::now()->toISOString(),
        ];

        return $this->successResponse($data);
    }

    // Helper Methods

    private function getAvailableEndpoints(): array
    {
        return [
            'health'                   => 'GET /api/bi/health',
            'analytics_overview'       => 'GET /api/bi/analytics/overview',
            'ticket_metrics'           => 'GET /api/bi/tickets/metrics',
            'platform_data'            => 'GET /api/bi/platforms/performance',
            'competitive_intelligence' => 'GET /api/bi/competitive/intelligence',
            'predictive_insights'      => 'GET /api/bi/predictive/insights',
            'current_anomalies'        => 'GET /api/bi/anomalies/current',
            'export_dataset'           => 'POST /api/bi/export/dataset',
            'user_analytics'           => 'GET /api/bi/users/analytics',
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['date_from'])) {
            $query->where('scraped_tickets.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('scraped_tickets.created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['sport'])) {
            $query->where('scraped_tickets.sport', $filters['sport']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('scraped_tickets.price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('scraped_tickets.price', '<=', $filters['price_max']);
        }
    }

    private function getDateRangeFromString(string $range): Carbon
    {
        return match ($range) {
            '7d'    => Carbon::now()->subDays(7),
            '30d'   => Carbon::now()->subDays(30),
            '90d'   => Carbon::now()->subDays(90),
            '1y'    => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };
    }

    private function getHistoricalComparison(array $filters): array
    {
        // Compare current period with previous period
        $currentPeriod = ScrapedTicket::query()
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->when(!empty($filters['sport']), fn ($q) => $q->where('sport', $filters['sport']))
            ->get();

        $previousPeriod = ScrapedTicket::query()
            ->whereBetween('created_at', [Carbon::now()->subDays(60), Carbon::now()->subDays(30)])
            ->when(!empty($filters['sport']), fn ($q) => $q->where('sport', $filters['sport']))
            ->get();

        return [
            'current_period' => [
                'ticket_count' => $currentPeriod->count(),
                'avg_price'    => round($currentPeriod->avg('price') ?: 0, 2),
            ],
            'previous_period' => [
                'ticket_count' => $previousPeriod->count(),
                'avg_price'    => round($previousPeriod->avg('price') ?: 0, 2),
            ],
            'growth_rates' => [
                'ticket_count_growth' => $this->calculateGrowthRate(
                    $previousPeriod->count(),
                    $currentPeriod->count(),
                ),
                'avg_price_growth' => $this->calculateGrowthRate(
                    $previousPeriod->avg('price') ?: 0,
                    $currentPeriod->avg('price') ?: 0,
                ),
            ],
        ];
    }

    private function calculateStandardDeviation($collection): float
    {
        if ($collection->isEmpty()) {
            return 0;
        }

        $mean = $collection->avg();
        $variance = $collection->map(fn ($value): int|float => ($value - $mean) ** 2)->avg();

        return round(sqrt($variance), 2);
    }

    private function calculatePlatformTrends($tickets): array
    {
        $dailyData = $tickets->groupBy(fn ($ticket) => $ticket->created_at->format('Y-m-d'));

        $trend = [];
        foreach ($dailyData as $date => $dayTickets) {
            $trend[] = [
                'date'      => $date,
                'count'     => $dayTickets->count(),
                'avg_price' => round($dayTickets->avg('price') ?: 0, 2),
            ];
        }

        return $trend;
    }

    private function calculateDataQualityScore($tickets): float
    {
        if ($tickets->isEmpty()) {
            return 0;
        }

        $totalFields = 0;
        $validFields = 0;

        foreach ($tickets as $ticket) {
            $totalFields += 6; // Assuming 6 key fields per ticket

            if (!empty($ticket->event_name)) {
                $validFields++;
            }
            if (!empty($ticket->sport)) {
                $validFields++;
            }
            if ($ticket->price > 0) {
                $validFields++;
            }
            if (!empty($ticket->event_date)) {
                $validFields++;
            }
            if (!empty($ticket->venue)) {
                $validFields++;
            }
            if (!empty($ticket->url)) {
                $validFields++;
            }
        }

        return $totalFields > 0 ? round(($validFields / $totalFields) * 100, 1) : 0;
    }

    private function calculateGrowthRate($previous, $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function generateBusinessRecommendations(string $analysisType): array
    {
        // Generate contextual business recommendations based on analysis type
        return match ($analysisType) {
            'overview' => [
                'market_expansion'     => 'Consider expanding into underrepresented sports categories',
                'pricing_optimization' => 'Implement dynamic pricing in premium segments',
            ],
            'pricing' => [
                'competitive_pricing' => 'Adjust pricing strategy for events with >20% price gaps',
                'value_positioning'   => 'Emphasize unique value propositions in premium segments',
            ],
            'positioning' => [
                'differentiation' => 'Focus on unique sports categories or geographic markets',
                'partnership'     => 'Consider strategic partnerships with market leaders',
            ],
            'gaps' => [
                'opportunity_focus'    => 'Prioritize high-opportunity market segments',
                'competitive_response' => 'Develop rapid response capabilities for competitive threats',
            ],
            default => [],
        };
    }

    private function checkRateLimit(string $key, int $maxAttempts): void
    {
        $user = auth()->user();
        $rateLimitKey = $key . ':' . $user->id;

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            throw new ThrottleRequestsException(
                "Too many requests. Try again in {$seconds} seconds.",
            );
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour window
    }

    private function successResponse(array $data): JsonResponse
    {
        return response()->json([
            'success' => TRUE,
            'data'    => $data,
            'meta'    => [
                'api_version' => '1.0.0',
                'request_id'  => uniqid('bi_'),
                'timestamp'   => Carbon::now()->toISOString(),
            ],
        ]);
    }

    private function errorResponse(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => FALSE,
            'message' => $message,
            'errors'  => $errors,
            'meta'    => [
                'api_version' => '1.0.0',
                'request_id'  => uniqid('bi_'),
                'timestamp'   => Carbon::now()->toISOString(),
            ],
        ], $code);
    }
}
