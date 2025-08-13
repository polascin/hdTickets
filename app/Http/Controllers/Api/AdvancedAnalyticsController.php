<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsDashboard;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function count;
use function sprintf;

class AdvancedAnalyticsController extends Controller
{
    private $analyticsDashboard;

    public function __construct(AdvancedAnalyticsDashboard $analyticsDashboard)
    {
        $this->analyticsDashboard = $analyticsDashboard;
    }

    /**
     * Get comprehensive price trend analysis
     */
    /**
     * Get  price trend analysis
     */
    public function getPriceTrendAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'   => 'sometimes|date',
            'end_date'     => 'sometimes|date|after_or_equal:start_date',
            'platforms'    => 'sometimes|array',
            'platforms.*'  => 'string',
            'categories'   => 'sometimes|array',
            'categories.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $this->buildFilters($request);
            $analysis = $this->analyticsDashboard->getPriceTrendAnalysis($filters);

            return response()->json([
                'success'  => TRUE,
                'data'     => $analysis,
                'metadata' => [
                    'generated_at'    => now()->toISOString(),
                    'filters_applied' => $filters,
                    'cache_status'    => 'hit', // This would be determined by cache implementation
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate price trend analysis',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get demand pattern analysis with ML insights
     */
    /**
     * Get  demand pattern analysis
     */
    public function getDemandPatternAnalysis(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'          => 'sometimes|date',
            'end_date'            => 'sometimes|date|after_or_equal:start_date',
            'event_types'         => 'sometimes|array',
            'geographic_filters'  => 'sometimes|array',
            'include_predictions' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $this->buildFilters($request);
            $analysis = $this->analyticsDashboard->getDemandPatternAnalysis($filters);

            return response()->json([
                'success'  => TRUE,
                'data'     => $analysis,
                'insights' => [
                    'key_trends'       => $this->extractKeyDemandTrends($analysis),
                    'recommendations'  => $analysis['recommendations'] ?? [],
                    'confidence_score' => $this->calculateAnalysisConfidenceScore($analysis),
                ],
                'metadata' => [
                    'generated_at'         => now()->toISOString(),
                    'analysis_period'      => $this->getAnalysisPeriod($filters),
                    'data_points_analyzed' => $this->countDataPoints($analysis),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate demand pattern analysis',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get success rate optimization recommendations
     */
    /**
     * Get  success rate optimization
     */
    public function getSuccessRateOptimization(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'         => 'sometimes|date',
            'end_date'           => 'sometimes|date|after_or_equal:start_date',
            'channels'           => 'sometimes|array',
            'user_segments'      => 'sometimes|array',
            'optimization_goals' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $this->buildFilters($request);
            $optimization = $this->analyticsDashboard->getSuccessRateOptimization($filters);

            // Calculate potential improvements
            $improvementProjections = $this->calculateImprovementProjections($optimization);

            return response()->json([
                'success'      => TRUE,
                'data'         => $optimization,
                'projections'  => $improvementProjections,
                'action_items' => $this->prioritizeActionItems($optimization),
                'metadata'     => [
                    'generated_at'          => now()->toISOString(),
                    'current_baseline'      => $optimization['current_performance'] ?? [],
                    'improvement_potential' => $this->calculateImprovementPotential($optimization),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate success rate optimization analysis',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get comprehensive platform performance comparison
     */
    /**
     * Get  platform performance comparison
     */
    public function getPlatformPerformanceComparison(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'      => 'sometimes|date',
            'end_date'        => 'sometimes|date|after_or_equal:start_date',
            'platforms'       => 'sometimes|array',
            'metrics'         => 'sometimes|array',
            'comparison_type' => 'sometimes|in:detailed,summary,rankings',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $this->buildFilters($request);
            $comparison = $this->analyticsDashboard->getPlatformPerformanceComparison($filters);

            $comparisonType = $request->get('comparison_type', 'detailed');
            $formattedData = $this->formatPlatformComparison($comparison, $comparisonType);

            return response()->json([
                'success' => TRUE,
                'data'    => $formattedData,
                'summary' => [
                    'top_performer'       => $this->identifyTopPerformer($comparison),
                    'biggest_opportunity' => $this->identifyBiggestOpportunity($comparison),
                    'market_trends'       => $this->extractMarketTrends($comparison),
                ],
                'metadata' => [
                    'generated_at'       => now()->toISOString(),
                    'platforms_analyzed' => count($comparison['performance_metrics'] ?? []),
                    'comparison_type'    => $comparisonType,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate platform performance comparison',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get real-time dashboard metrics
     */
    /**
     * Get  real time dashboard metrics
     */
    public function getRealTimeDashboardMetrics(): JsonResponse
    {
        try {
            $metrics = $this->analyticsDashboard->getRealTimeDashboardMetrics();

            return response()->json([
                'success'  => TRUE,
                'data'     => $metrics,
                'status'   => $this->determineSystemStatus($metrics),
                'alerts'   => $this->generateSystemAlerts($metrics),
                'metadata' => [
                    'generated_at'     => now()->toISOString(),
                    'refresh_interval' => 30, // seconds
                    'data_freshness'   => 'real-time',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve real-time metrics',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Export analytics data in various formats
     */
    /**
     * ExportAnalyticsData
     */
    public function exportAnalyticsData(Request $request, string $type): JsonResponse
    {
        $validator = Validator::make(array_merge($request->all(), ['type' => $type]), [
            'type'             => 'required|in:price_trends,demand_patterns,success_metrics,platform_comparison',
            'format'           => 'sometimes|in:json,csv,xlsx',
            'start_date'       => 'sometimes|date',
            'end_date'         => 'sometimes|date|after_or_equal:start_date',
            'include_raw_data' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $filters = $this->buildFilters($request);
            $format = $request->get('format', 'json');

            $data = $this->analyticsDashboard->exportAnalyticsData($type, $filters);

            if ($format === 'json') {
                return response()->json([
                    'success'     => TRUE,
                    'data'        => $data,
                    'export_info' => [
                        'type'         => $type,
                        'format'       => $format,
                        'generated_at' => now()->toISOString(),
                        'record_count' => $this->countExportRecords($data),
                    ],
                ]);
            }

            // For CSV/XLSX formats, return download URL or direct file response
            $exportFile = $this->generateExportFile($data, $type, $format);

            return response()->json([
                'success'      => TRUE,
                'download_url' => $exportFile['url'],
                'file_info'    => [
                    'filename'   => $exportFile['filename'],
                    'size'       => $exportFile['size'],
                    'expires_at' => $exportFile['expires_at'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export analytics data',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get custom analytics dashboard configuration
     */
    /**
     * Get  custom dashboard
     */
    public function getCustomDashboard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'widgets'      => 'required|array',
            'widgets.*'    => 'in:price_trends,demand_patterns,success_rates,platform_comparison,real_time_metrics',
            'time_range'   => 'sometimes|in:1h,24h,7d,30d,90d,1y',
            'auto_refresh' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $widgets = $request->get('widgets', []);
            $filters = $this->buildFiltersFromTimeRange($request->get('time_range', '30d'));

            $dashboardData = [];

            foreach ($widgets as $widget) {
                $dashboardData[$widget] = $this->getWidgetData($widget, $filters);
            }

            return response()->json([
                'success'       => TRUE,
                'data'          => $dashboardData,
                'configuration' => [
                    'widgets'      => $widgets,
                    'time_range'   => $request->get('time_range', '30d'),
                    'auto_refresh' => $request->get('auto_refresh', FALSE),
                    'last_updated' => now()->toISOString(),
                ],
                'metadata' => [
                    'total_widgets'  => count($widgets),
                    'data_freshness' => $this->calculateDataFreshness($dashboardData),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate custom dashboard',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    // Private helper methods

    /**
     * BuildFilters
     */
    private function buildFilters(Request $request): array
    {
        $filters = [];

        if ($request->has('start_date')) {
            $filters['start_date'] = Carbon::parse($request->get('start_date'));
        }

        if ($request->has('end_date')) {
            $filters['end_date'] = Carbon::parse($request->get('end_date'));
        }

        if ($request->has('platforms')) {
            $filters['platforms'] = $request->get('platforms');
        }

        if ($request->has('categories')) {
            $filters['categories'] = $request->get('categories');
        }

        if ($request->has('channels')) {
            $filters['channels'] = $request->get('channels');
        }

        return $filters;
    }

    /**
     * BuildFiltersFromTimeRange
     */
    private function buildFiltersFromTimeRange(string $timeRange): array
    {
        $endDate = Carbon::now();

        switch ($timeRange) {
            case '1h':
                $startDate = $endDate->copy()->subHour();

                break;
            case '24h':
                $startDate = $endDate->copy()->subDay();

                break;
            case '7d':
                $startDate = $endDate->copy()->subWeek();

                break;
            case '30d':
                $startDate = $endDate->copy()->subDays(30);

                break;
            case '90d':
                $startDate = $endDate->copy()->subDays(90);

                break;
            case '1y':
                $startDate = $endDate->copy()->subYear();

                break;
            default:
                $startDate = $endDate->copy()->subDays(30);
        }

        return [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ];
    }

    /**
     * Get  widget data
     */
    private function getWidgetData(string $widget, array $filters): array
    {
        switch ($widget) {
            case 'price_trends':
                return $this->analyticsDashboard->getPriceTrendAnalysis($filters);
            case 'demand_patterns':
                return $this->analyticsDashboard->getDemandPatternAnalysis($filters);
            case 'success_rates':
                return $this->analyticsDashboard->getSuccessRateOptimization($filters);
            case 'platform_comparison':
                return $this->analyticsDashboard->getPlatformPerformanceComparison($filters);
            case 'real_time_metrics':
                return $this->analyticsDashboard->getRealTimeDashboardMetrics();
            default:
                return [];
        }
    }

    /**
     * ExtractKeyDemandTrends
     */
    private function extractKeyDemandTrends(array $analysis): array
    {
        $trends = [];

        if (isset($analysis['temporal_patterns'])) {
            $trends['peak_hours'] = $this->identifyPeakHours($analysis['temporal_patterns']);
            $trends['seasonal_peaks'] = $this->identifySeasonalPeaks($analysis['temporal_patterns']);
        }

        if (isset($analysis['demand_overview'])) {
            $trends['growth_rate'] = $analysis['demand_overview']['demand_growth_rate'] ?? 0;
            $trends['demand_score'] = $analysis['demand_overview']['total_demand_score'] ?? 0;
        }

        return $trends;
    }

    /**
     * CalculateAnalysisConfidenceScore
     */
    private function calculateAnalysisConfidenceScore(array $analysis): float
    {
        // Calculate confidence based on data volume, pattern consistency, etc.
        $dataVolume = $this->countDataPoints($analysis);
        $patternConsistency = $this->assessPatternConsistency($analysis);

        return min(100, ($dataVolume * 0.6 + $patternConsistency * 0.4));
    }

    /**
     * CalculateImprovementProjections
     */
    private function calculateImprovementProjections(array $optimization): array
    {
        $current = $optimization['current_performance'] ?? [];
        $recommendations = $optimization['improvement_roadmap'] ?? [];

        $projections = [];

        if (! empty($current) && ! empty($recommendations)) {
            foreach ($recommendations as $recommendation) {
                $projections[] = [
                    'metric'                => $recommendation['metric'] ?? 'overall',
                    'current_value'         => $current[$recommendation['metric']] ?? 0,
                    'projected_improvement' => $recommendation['expected_improvement'] ?? 0,
                    'confidence'            => $recommendation['confidence'] ?? 0.5,
                    'timeline'              => $recommendation['timeline'] ?? '30 days',
                ];
            }
        }

        return $projections;
    }

    /**
     * PrioritizeActionItems
     */
    private function prioritizeActionItems(array $optimization): array
    {
        $actionItems = [];

        if (isset($optimization['improvement_roadmap'])) {
            $items = collect($optimization['improvement_roadmap']);

            $actionItems = $items->sortByDesc(function ($item) {
                $impact = $item['expected_improvement'] ?? 0;
                $effort = $item['implementation_effort'] ?? 5;

                return $impact / $effort; // Impact/Effort ratio
            })->take(5)->values()->toArray();
        }

        return $actionItems;
    }

    /**
     * FormatPlatformComparison
     */
    private function formatPlatformComparison(array $comparison, string $type): array
    {
        switch ($type) {
            case 'summary':
                return $this->createPlatformSummary($comparison);
            case 'rankings':
                return $comparison['platform_rankings'] ?? [];
            case 'detailed':
            default:
                return $comparison;
        }
    }

    /**
     * CreatePlatformSummary
     */
    private function createPlatformSummary(array $comparison): array
    {
        $summary = [];

        if (isset($comparison['platform_rankings'])) {
            foreach ($comparison['platform_rankings'] as $platform) {
                $summary[] = [
                    'platform'      => $platform['platform'],
                    'overall_score' => $platform['overall_score'],
                    'rank'          => $platform['rank'] ?? 0,
                    'key_strength'  => $platform['strengths'][0] ?? 'N/A',
                    'key_weakness'  => $platform['weaknesses'][0] ?? 'N/A',
                ];
            }
        }

        return $summary;
    }

    /**
     * IdentifyTopPerformer
     */
    private function identifyTopPerformer(array $comparison): array
    {
        $rankings = $comparison['platform_rankings'] ?? [];

        return ! empty($rankings) ? $rankings[0] : [];
    }

    /**
     * IdentifyBiggestOpportunity
     */
    private function identifyBiggestOpportunity(array $comparison): array
    {
        $rankings = $comparison['platform_rankings'] ?? [];

        // Find platform with biggest gap between potential and current performance
        $opportunity = [];
        $maxGap = 0;

        foreach ($rankings as $platform) {
            $potential = $platform['potential_score'] ?? $platform['overall_score'];
            $current = $platform['overall_score'];
            $gap = $potential - $current;

            if ($gap > $maxGap) {
                $maxGap = $gap;
                $opportunity = [
                    'platform'            => $platform['platform'],
                    'current_score'       => $current,
                    'potential_score'     => $potential,
                    'improvement_gap'     => $gap,
                    'recommended_actions' => $platform['recommendations'] ?? [],
                ];
            }
        }

        return $opportunity;
    }

    /**
     * ExtractMarketTrends
     */
    private function extractMarketTrends(array $comparison): array
    {
        return [
            'market_leader'   => $this->identifyTopPerformer($comparison)['platform'] ?? 'N/A',
            'fastest_growing' => $this->identifyFastestGrowingPlatform($comparison),
            'most_reliable'   => $this->identifyMostReliablePlatform($comparison),
            'best_value'      => $this->identifyBestValuePlatform($comparison),
        ];
    }

    /**
     * DetermineSystemStatus
     */
    private function determineSystemStatus(array $metrics): string
    {
        $healthMetrics = $metrics['system_health'] ?? [];

        if (empty($healthMetrics)) {
            return 'unknown';
        }

        $errorRate = $healthMetrics['error_rate'] ?? 0;
        $responseTime = $healthMetrics['api_response_time'] ?? 0;

        if ($errorRate > 5 || $responseTime > 2000) {
            return 'warning';
        }
        if ($errorRate > 10 || $responseTime > 5000) {
            return 'critical';
        }

        return 'healthy';
    }

    /**
     * GenerateSystemAlerts
     */
    private function generateSystemAlerts(array $metrics): array
    {
        $alerts = [];
        $healthMetrics = $metrics['system_health'] ?? [];

        if (($healthMetrics['error_rate'] ?? 0) > 5) {
            $alerts[] = [
                'type'      => 'warning',
                'message'   => 'High error rate detected',
                'value'     => $healthMetrics['error_rate'],
                'threshold' => 5,
            ];
        }

        if (($healthMetrics['api_response_time'] ?? 0) > 2000) {
            $alerts[] = [
                'type'      => 'warning',
                'message'   => 'Slow API response time',
                'value'     => $healthMetrics['api_response_time'],
                'threshold' => 2000,
            ];
        }

        return $alerts;
    }

    /**
     * GenerateExportFile
     */
    private function generateExportFile(array $data, string $type, string $format): array
    {
        // This would implement actual file generation logic
        $filename = sprintf('%s_%s_%s.%s', $type, date('Y-m-d_H-i-s'), uniqid(), $format);

        return [
            'url'        => url("exports/{$filename}"),
            'filename'   => $filename,
            'size'       => 0, // Would be calculated from actual file
            'expires_at' => now()->addHours(24)->toISOString(),
        ];
    }

    /**
     * CountExportRecords
     */
    private function countExportRecords(array $data): int
    {
        // Count total records in the export data
        return collect($data)->flatten()->count();
    }

    /**
     * CountDataPoints
     */
    private function countDataPoints(array $analysis): int
    {
        // Count data points across all analysis sections
        return collect($analysis)->flatten()->count();
    }

    /**
     * Get  analysis period
     */
    private function getAnalysisPeriod(array $filters): array
    {
        return [
            'start'         => $filters['start_date']->toISOString() ?? NULL,
            'end'           => $filters['end_date']->toISOString() ?? NULL,
            'duration_days' => isset($filters['start_date'], $filters['end_date'])
                ? $filters['start_date']->diffInDays($filters['end_date'])
                : NULL,
        ];
    }

    /**
     * CalculateDataFreshness
     */
    private function calculateDataFreshness(array $dashboardData): string
    {
        // Determine overall data freshness across all widgets
        return 'current'; // Simplified implementation
    }

    /**
     * CalculateImprovementPotential
     */
    private function calculateImprovementPotential(array $optimization): float
    {
        // Calculate overall improvement potential percentage
        $current = $optimization['current_performance']['overall_success_rate'] ?? 0;
        $potential = min(100, $current * 1.3); // Example: 30% improvement potential

        return round($potential - $current, 2);
    }

    // Additional helper methods would be implemented here
    /**
     * IdentifyPeakHours
     */
    private function identifyPeakHours(array $temporalPatterns): array
    {
        return [];
    }

    /**
     * IdentifySeasonalPeaks
     */
    private function identifySeasonalPeaks(array $temporalPatterns): array
    {
        return [];
    }

    /**
     * AssessPatternConsistency
     */
    private function assessPatternConsistency(array $analysis): float
    {
        return 75.0;
    }

    /**
     * IdentifyFastestGrowingPlatform
     */
    private function identifyFastestGrowingPlatform(array $comparison): string
    {
        return 'N/A';
    }

    /**
     * IdentifyMostReliablePlatform
     */
    private function identifyMostReliablePlatform(array $comparison): string
    {
        return 'N/A';
    }

    /**
     * IdentifyBestValuePlatform
     */
    private function identifyBestValuePlatform(array $comparison): string
    {
        return 'N/A';
    }
}
