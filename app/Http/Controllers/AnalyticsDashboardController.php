<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Analytics\AdvancedAnalyticsService;
use App\Services\Analytics\AnomalyDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Analytics Dashboard Controller
 *
 * Handles the analytics dashboard interface and data visualization
 * endpoints for the HD Tickets analytics system.
 */
class AnalyticsDashboardController extends Controller
{
    private AdvancedAnalyticsService $analyticsService;

    private AnomalyDetectionService $anomalyService;

    public function __construct(
        AdvancedAnalyticsService $analyticsService,
        AnomalyDetectionService $anomalyService
    ) {
        $this->analyticsService = $analyticsService;
        $this->anomalyService = $anomalyService;

        // Ensure only admin and agent roles can access analytics
        $this->middleware(['auth', 'role:admin,agent']);
    }

    /**
     * Display the main analytics dashboard
     *
     * @param  Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $filters = $this->getFiltersFromRequest($request);

        // Get initial dashboard data for server-side rendering
        $dashboardData = $this->analyticsService->getDashboardData($filters);

        // Get recent anomalies for alerts widget
        $recentAnomalies = $this->anomalyService->getRecentAnomalies($filters);

        return view('analytics.dashboard', [
            'initialData' => $dashboardData,
            'anomalies'   => $recentAnomalies,
            'filters'     => $filters,
            'config'      => config('analytics.dashboard'),
        ]);
    }

    /**
     * Get dashboard data for AJAX requests
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $data = $this->analyticsService->getDashboardData($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $data,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics dashboard data fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch dashboard data',
                'message' => 'Please try again later',
            ], 500);
        }
    }

    /**
     * Get overview metrics for the dashboard
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getOverviewMetrics(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $metrics = $this->analyticsService->getOverviewMetrics($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $metrics,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics overview metrics fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch overview metrics',
            ], 500);
        }
    }

    /**
     * Get platform performance data for charts
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getPlatformPerformance(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $performance = $this->analyticsService->getPlatformPerformanceMetrics($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $performance,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics platform performance fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch platform performance data',
            ], 500);
        }
    }

    /**
     * Get pricing trends data for charts
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getPricingTrends(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $trends = $this->analyticsService->getPricingTrends($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $trends,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics pricing trends fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch pricing trends',
            ], 500);
        }
    }

    /**
     * Get event popularity data
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getEventPopularity(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $popularity = $this->analyticsService->getEventPopularityMetrics($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $popularity,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics event popularity fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch event popularity data',
            ], 500);
        }
    }

    /**
     * Get anomalies data for alerts widget
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getAnomalies(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $anomalies = $this->anomalyService->getRecentAnomalies($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $anomalies,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics anomalies fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch anomalies data',
            ], 500);
        }
    }

    /**
     * Get real-time analytics data
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $realtimeData = $this->analyticsService->getRealtimeAnalytics($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $realtimeData,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics realtime data fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch real-time data',
            ], 500);
        }
    }

    /**
     * Get predictive insights data
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getPredictiveInsights(Request $request): JsonResponse
    {
        try {
            $filters = $this->getFiltersFromRequest($request);
            $insights = $this->analyticsService->getPredictiveInsights($filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $insights,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics predictive insights fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch predictive insights',
            ], 500);
        }
    }

    /**
     * Get historical comparison data
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function getHistoricalComparison(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'periods'              => 'required|array|min:2|max:5',
                'periods.*.label'      => 'required|string|max:50',
                'periods.*.start_date' => 'required|date',
                'periods.*.end_date'   => 'required|date|after:periods.*.start_date',
            ]);

            $filters = $this->getFiltersFromRequest($request);
            $periods = $request->get('periods');

            $comparison = $this->analyticsService->getHistoricalComparison($periods, $filters);

            return response()->json([
                'success'   => TRUE,
                'data'      => $comparison,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics historical comparison fetch failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters ?? [],
                'periods' => $request->get('periods', []),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch historical comparison data',
            ], 500);
        }
    }

    /**
     * Export analytics data
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function exportData(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'format'     => 'required|in:csv,pdf,json,xlsx',
                'sections'   => 'array',
                'sections.*' => 'in:overview,platform_performance,pricing_trends,event_popularity,anomalies',
                'options'    => 'array',
            ]);

            $format = $request->get('format');
            $sections = $request->get('sections', ['overview', 'platform_performance']);
            $options = $request->get('options', []);
            $filters = $this->getFiltersFromRequest($request);

            // Add user context to options
            $options['user_id'] = auth()->id();
            $options['user_role'] = auth()->user()->role;
            $options['export_requested_at'] = now()->toISOString();

            // Get data for selected sections
            $exportData = [];
            foreach ($sections as $section) {
                switch ($section) {
                    case 'overview':
                        $exportData['overview_metrics'] = $this->analyticsService->getOverviewMetrics($filters);

                        break;
                    case 'platform_performance':
                        $exportData['platform_performance'] = $this->analyticsService->getPlatformPerformanceMetrics($filters);

                        break;
                    case 'pricing_trends':
                        $exportData['pricing_trends'] = $this->analyticsService->getPricingTrends($filters);

                        break;
                    case 'event_popularity':
                        $exportData['event_popularity'] = $this->analyticsService->getEventPopularityMetrics($filters);

                        break;
                    case 'anomalies':
                        $exportData['anomalies'] = $this->anomalyService->getRecentAnomalies($filters);

                        break;
                }
            }

            $result = $this->analyticsService->exportAnalyticsData($format, $exportData, $options);

            Log::info('Analytics data export initiated', [
                'format'   => $format,
                'sections' => $sections,
                'user_id'  => auth()->id(),
                'success'  => $result['success'],
            ]);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Analytics data export failed', [
                'error'   => $e->getMessage(),
                'format'  => $request->get('format'),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Export failed',
                'message' => 'Please try again later',
            ], 500);
        }
    }

    /**
     * Clear analytics cache
     *
     * @param  Request      $request
     * @return JsonResponse
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            // Require admin role for cache clearing
            $this->authorize('admin-only');

            $tags = ['analytics', 'dashboard', 'predictive', 'anomalies'];

            foreach ($tags as $tag) {
                Cache::tags($tag)->flush();
            }

            Log::info('Analytics cache cleared', [
                'user_id' => auth()->id(),
                'tags'    => $tags,
            ]);

            return response()->json([
                'success'   => TRUE,
                'message'   => 'Analytics cache cleared successfully',
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Analytics cache clear failed', [
                'error'   => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to clear cache',
            ], 500);
        }
    }

    /**
     * Get available filters and their options
     *
     * @return JsonResponse
     */
    public function getFilterOptions(): JsonResponse
    {
        try {
            $options = [
                'platforms'   => $this->getAvailablePlatforms(),
                'categories'  => $this->getAvailableCategories(),
                'time_ranges' => $this->getTimeRangeOptions(),
                'date_ranges' => $this->getDateRangePresets(),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $options,
            ]);
        } catch (\Exception $e) {
            Log::error('Filter options fetch failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'error'   => 'Failed to fetch filter options',
            ], 500);
        }
    }

    // Private helper methods

    /**
     * Extract filters from request
     *
     * @param  Request $request
     * @return array
     */
    private function getFiltersFromRequest(Request $request): array
    {
        $filters = [];

        if ($request->has('start_date')) {
            $filters['start_date'] = $request->get('start_date');
        }

        if ($request->has('end_date')) {
            $filters['end_date'] = $request->get('end_date');
        }

        if ($request->has('days')) {
            $filters['days'] = (int) $request->get('days', 30);
        }

        if ($request->has('platform')) {
            $filters['platform'] = $request->get('platform');
        }

        if ($request->has('sport_category')) {
            $filters['sport_category'] = $request->get('sport_category');
        }

        if ($request->has('period')) {
            $filters['period'] = $request->get('period');
        }

        return $filters;
    }

    /**
     * Get available platforms from database
     *
     * @return array
     */
    private function getAvailablePlatforms(): array
    {
        return Cache::remember('analytics_platforms', 3600, function () {
            return \DB::table('tickets')
                ->select('source_platform')
                ->distinct()
                ->whereNotNull('source_platform')
                ->pluck('source_platform')
                ->map(function ($platform) {
                    return [
                        'value' => $platform,
                        'label' => ucfirst($platform),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get available sport categories
     *
     * @return array
     */
    private function getAvailableCategories(): array
    {
        return Cache::remember('analytics_categories', 3600, function () {
            return \DB::table('sports_events')
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category')
                ->map(function ($category) {
                    return [
                        'value' => $category,
                        'label' => ucwords(str_replace('_', ' ', $category)),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Get time range options
     *
     * @return array
     */
    private function getTimeRangeOptions(): array
    {
        return [
            ['value' => 7, 'label' => 'Last 7 Days'],
            ['value' => 14, 'label' => 'Last 2 Weeks'],
            ['value' => 30, 'label' => 'Last 30 Days'],
            ['value' => 60, 'label' => 'Last 2 Months'],
            ['value' => 90, 'label' => 'Last 3 Months'],
            ['value' => 180, 'label' => 'Last 6 Months'],
            ['value' => 365, 'label' => 'Last Year'],
        ];
    }

    /**
     * Get date range presets
     *
     * @return array
     */
    private function getDateRangePresets(): array
    {
        return [
            [
                'label'      => 'Today',
                'start_date' => now()->startOfDay()->toISOString(),
                'end_date'   => now()->endOfDay()->toISOString(),
            ],
            [
                'label'      => 'Yesterday',
                'start_date' => now()->subDay()->startOfDay()->toISOString(),
                'end_date'   => now()->subDay()->endOfDay()->toISOString(),
            ],
            [
                'label'      => 'This Week',
                'start_date' => now()->startOfWeek()->toISOString(),
                'end_date'   => now()->endOfWeek()->toISOString(),
            ],
            [
                'label'      => 'Last Week',
                'start_date' => now()->subWeek()->startOfWeek()->toISOString(),
                'end_date'   => now()->subWeek()->endOfWeek()->toISOString(),
            ],
            [
                'label'      => 'This Month',
                'start_date' => now()->startOfMonth()->toISOString(),
                'end_date'   => now()->endOfMonth()->toISOString(),
            ],
            [
                'label'      => 'Last Month',
                'start_date' => now()->subMonth()->startOfMonth()->toISOString(),
                'end_date'   => now()->subMonth()->endOfMonth()->toISOString(),
            ],
        ];
    }
}
