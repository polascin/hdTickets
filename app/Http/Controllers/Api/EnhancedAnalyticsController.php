<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsInsightsService;
use App\Services\ChartDataService;
use App\Services\DataExportService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

use function array_slice;
use function count;
use function is_array;

class EnhancedAnalyticsController extends Controller
{
    protected $chartDataService;

    protected $exportService;

    protected $insightsService;

    public function __construct(
        ChartDataService $chartDataService,
        DataExportService $exportService,
        AnalyticsInsightsService $insightsService,
    ) {
        $this->chartDataService = $chartDataService;
        $this->exportService = $exportService;
        $this->insightsService = $insightsService;
    }

    /**
     * Get comprehensive chart data for analytics dashboard
     */
    /**
     * Get  chart data
     */
    public function getChartData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'    => 'sometimes|date',
            'end_date'      => 'sometimes|date|after_or_equal:start_date',
            'platforms'     => 'sometimes|array',
            'platforms.*'   => 'string',
            'chart_types'   => 'sometimes|array',
            'chart_types.*' => 'in:ticket_trends,price_volatility,platform_market_share,user_engagement_funnel,sports_category_radar,hourly_activity_heatmap',
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
            $chartTypes = $request->get('chart_types', []);

            if (empty($chartTypes)) {
                // Return all chart types if none specified
                $chartData = $this->chartDataService->getDashboardChartsData($filters);
            } else {
                // Return only requested chart types
                $chartData = [];
                foreach ($chartTypes as $chartType) {
                    $chartData[$chartType] = $this->getSpecificChartData($chartType, $filters);
                }
            }

            return response()->json([
                'success'  => TRUE,
                'data'     => $chartData,
                'metadata' => [
                    'generated_at'    => now()->toISOString(),
                    'filters_applied' => $filters,
                    'chart_count'     => count($chartData),
                    'cache_status'    => 'dynamic',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate chart data',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Export analytics data in various formats
     */
    /**
     * ExportData
     */
    public function exportData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'export_type'    => 'required|in:ticket_trends,price_analysis,platform_performance,user_engagement,comprehensive_analytics',
            'format'         => 'sometimes|in:csv,xlsx,pdf,json',
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date|after_or_equal:start_date',
            'platforms'      => 'sometimes|array',
            'include_charts' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $exportType = $request->get('export_type');
            $format = $request->get('format', 'xlsx');
            $filters = $this->buildFilters($request);

            $result = match ($exportType) {
                'ticket_trends'           => $this->exportService->exportTicketTrends($filters, $format),
                'price_analysis'          => $this->exportService->exportPriceAnalysis($filters, $format),
                'platform_performance'    => $this->exportService->exportPlatformPerformance($filters, $format),
                'user_engagement'         => $this->exportService->exportUserEngagement($filters, $format),
                'comprehensive_analytics' => $this->exportService->exportComprehensiveAnalytics($filters, $format),
                default                   => throw new InvalidArgumentException("Unsupported export type: {$exportType}"),
            };

            return response()->json([
                'success'     => $result['success'],
                'export_info' => [
                    'type'         => $exportType,
                    'format'       => $result['format'],
                    'file_path'    => $result['file_path'],
                    'download_url' => $result['download_url'],
                    'file_size'    => $result['file_size'],
                    'generated_at' => now()->toISOString(),
                ],
                'metadata' => $result['metadata'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export data',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get predictive analytics insights
     */
    /**
     * Get  predictive insights
     */
    public function getPredictiveInsights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'         => 'sometimes|date',
            'end_date'           => 'sometimes|date|after_or_equal:start_date',
            'prediction_horizon' => 'sometimes|in:1_week,2_weeks,1_month,3_months',
            'include_confidence' => 'sometimes|boolean',
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
            $insights = $this->insightsService->getPredictiveInsights($filters);

            return response()->json([
                'success'          => TRUE,
                'data'             => $insights,
                'insights_summary' => [
                    'demand_trend'               => $insights['demand_forecasting']['trend_direction'] ?? 'stable',
                    'market_opportunities_count' => count($insights['market_opportunities'] ?? []),
                    'risk_level'                 => $this->assessOverallRiskLevel($insights['risk_assessment'] ?? []),
                    'confidence_average'         => $this->calculateAverageConfidence($insights['confidence_scores'] ?? []),
                ],
                'metadata' => [
                    'analysis_period'    => $this->getAnalysisPeriod($filters),
                    'prediction_horizon' => $request->get('prediction_horizon', '1_month'),
                    'generated_at'       => $insights['generated_at'],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate predictive insights',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get user behavior insights
     */
    /**
     * Get  user behavior insights
     */
    public function getUserBehaviorInsights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'          => 'sometimes|date',
            'end_date'            => 'sometimes|date|after_or_equal:start_date',
            'segment_analysis'    => 'sometimes|boolean',
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
            $insights = $this->insightsService->getUserBehaviorInsights($filters);

            return response()->json([
                'success'          => TRUE,
                'data'             => $insights,
                'behavior_summary' => [
                    'total_segments'          => count($insights['user_segmentation']['segments'] ?? []),
                    'average_engagement'      => $insights['engagement_patterns']['average_engagement'] ?? 0,
                    'churn_risk_level'        => $this->assessChurnRiskLevel($insights['churn_prediction'] ?? []),
                    'key_actionable_insights' => array_slice($insights['actionable_insights'] ?? [], 0, 5),
                ],
                'metadata' => [
                    'analysis_period' => $this->getAnalysisPeriod($filters),
                    'generated_at'    => now()->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate user behavior insights',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get market intelligence insights
     */
    /**
     * Get  market intelligence
     */
    public function getMarketIntelligence(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'              => 'sometimes|date',
            'end_date'                => 'sometimes|date|after_or_equal:start_date',
            'competitive_analysis'    => 'sometimes|boolean',
            'include_recommendations' => 'sometimes|boolean',
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
            $intelligence = $this->insightsService->getMarketIntelligence($filters);

            return response()->json([
                'success'        => TRUE,
                'data'           => $intelligence,
                'market_summary' => [
                    'market_health_score'       => $intelligence['market_health_score'] ?? 0,
                    'competitive_platforms'     => count($intelligence['competitive_analysis'] ?? []),
                    'market_gaps_identified'    => count($intelligence['market_gaps'] ?? []),
                    'strategic_recommendations' => array_slice($intelligence['strategic_recommendations'] ?? [], 0, 3),
                ],
                'metadata' => [
                    'analysis_period'        => $this->getAnalysisPeriod($filters),
                    'intelligence_freshness' => $this->calculateIntelligenceFreshness(),
                    'generated_at'           => now()->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate market intelligence',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get performance optimization insights
     */
    /**
     * Get  optimization insights
     */
    public function getOptimizationInsights(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_date'      => 'sometimes|date',
            'end_date'        => 'sometimes|date|after_or_equal:start_date',
            'focus_areas'     => 'sometimes|array',
            'focus_areas.*'   => 'in:platform,scraping,alerts,resources',
            'include_roadmap' => 'sometimes|boolean',
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
            $insights = $this->insightsService->getOptimizationInsights($filters);

            return response()->json([
                'success'              => TRUE,
                'data'                 => $insights,
                'optimization_summary' => [
                    'total_opportunities'    => count($insights['improvement_opportunities'] ?? []),
                    'bottlenecks_identified' => count($insights['bottleneck_analysis'] ?? []),
                    'optimization_score'     => $this->calculateOptimizationScore($insights),
                    'priority_actions'       => $this->extractPriorityActions($insights),
                ],
                'metadata' => [
                    'analysis_period' => $this->getAnalysisPeriod($filters),
                    'focus_areas'     => $request->get('focus_areas', ['platform', 'scraping', 'alerts', 'resources']),
                    'generated_at'    => now()->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate optimization insights',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get real-time anomaly detection results
     */
    /**
     * Get  anomaly detection
     */
    public function getAnomalyDetection(Request $request): JsonResponse
    {
        try {
            $anomalies = $this->insightsService->getAnomalyDetectionInsights();

            $criticalAnomalies = $this->filterCriticalAnomalies($anomalies);
            $anomalySummary = $this->summarizeAnomalies($anomalies);

            return response()->json([
                'success'          => TRUE,
                'data'             => $anomalies,
                'critical_alerts'  => $criticalAnomalies,
                'summary'          => $anomalySummary,
                'real_time_status' => [
                    'system_health' => $this->determineSystemHealth($anomalies),
                    'alert_level'   => $this->determineAlertLevel($criticalAnomalies),
                    'last_scan'     => $anomalies['detection_timestamp'],
                    'next_scan'     => now()->addMinutes(5)->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to perform anomaly detection',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get analytics dashboard configuration
     */
    /**
     * Get  dashboard config
     */
    public function getDashboardConfig(Request $request): JsonResponse
    {
        try {
            $config = [
                'available_charts' => [
                    'ticket_trends' => [
                        'name'             => 'Ticket Availability Trends',
                        'description'      => 'Track ticket availability over time',
                        'type'             => 'line',
                        'refresh_interval' => 300, // 5 minutes
                    ],
                    'price_volatility' => [
                        'name'             => 'Price Volatility Analysis',
                        'description'      => 'Identify price fluctuation patterns',
                        'type'             => 'bar',
                        'refresh_interval' => 600, // 10 minutes
                    ],
                    'platform_market_share' => [
                        'name'             => 'Platform Market Share',
                        'description'      => 'Distribution of tickets across platforms',
                        'type'             => 'doughnut',
                        'refresh_interval' => 1800, // 30 minutes
                    ],
                    'user_engagement_funnel' => [
                        'name'             => 'User Engagement Funnel',
                        'description'      => 'User journey and conversion analysis',
                        'type'             => 'bar',
                        'refresh_interval' => 3600, // 1 hour
                    ],
                    'sports_category_radar' => [
                        'name'             => 'Sports Category Performance',
                        'description'      => 'Multi-dimensional category analysis',
                        'type'             => 'radar',
                        'refresh_interval' => 3600, // 1 hour
                    ],
                    'hourly_activity_heatmap' => [
                        'name'             => 'Activity Heatmap',
                        'description'      => 'User activity patterns by time',
                        'type'             => 'scatter',
                        'refresh_interval' => 1800, // 30 minutes
                    ],
                ],
                'export_formats' => ['csv', 'xlsx', 'pdf', 'json'],
                'export_types'   => [
                    'ticket_trends'           => 'Ticket Trends Report',
                    'price_analysis'          => 'Price Analysis Report',
                    'platform_performance'    => 'Platform Performance Report',
                    'user_engagement'         => 'User Engagement Report',
                    'comprehensive_analytics' => 'Comprehensive Analytics Report',
                ],
                'insight_types' => [
                    'predictive'          => 'Predictive Analytics',
                    'user_behavior'       => 'User Behavior Analysis',
                    'market_intelligence' => 'Market Intelligence',
                    'optimization'        => 'Performance Optimization',
                    'anomaly_detection'   => 'Anomaly Detection',
                ],
                'default_filters' => [
                    'start_date' => now()->subDays(30)->format('Y-m-d'),
                    'end_date'   => now()->format('Y-m-d'),
                    'platforms'  => [],
                    'categories' => [],
                ],
                'system_capabilities' => [
                    'real_time_updates'  => TRUE,
                    'scheduled_exports'  => TRUE,
                    'custom_dashboards'  => TRUE,
                    'api_access'         => TRUE,
                    'advanced_filtering' => TRUE,
                ],
            ];

            return response()->json([
                'success'  => TRUE,
                'data'     => $config,
                'metadata' => [
                    'config_version'     => '2.0',
                    'last_updated'       => now()->toISOString(),
                    'supported_features' => array_keys($config),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve dashboard configuration',
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

        return $filters;
    }

    /**
     * Get  specific chart data
     */
    private function getSpecificChartData(string $chartType, array $filters): Illuminate\Http\JsonResponse
    {
        return match ($chartType) {
            'ticket_trends'           => $this->chartDataService->getTicketTrendsChartData($filters),
            'price_volatility'        => $this->chartDataService->getPriceVolatilityHeatmapData($filters),
            'platform_market_share'   => $this->chartDataService->getPlatformMarketShareData($filters),
            'user_engagement_funnel'  => $this->chartDataService->getUserEngagementFunnelData($filters),
            'sports_category_radar'   => $this->chartDataService->getSportsCategoryRadarData($filters),
            'hourly_activity_heatmap' => $this->chartDataService->getHourlyActivityHeatmapData($filters),
            default                   => NULL,
        };
    }

    /**
     * Get  analysis period
     */
    private function getAnalysisPeriod(array $filters): array
    {
        return [
            'start_date'    => isset($filters['start_date']) ? $filters['start_date']->format('Y-m-d') : NULL,
            'end_date'      => isset($filters['end_date']) ? $filters['end_date']->format('Y-m-d') : NULL,
            'duration_days' => isset($filters['start_date'], $filters['end_date'])
                ? $filters['start_date']->diffInDays($filters['end_date'])
                : NULL,
        ];
    }

    /**
     * AssessOverallRiskLevel
     */
    private function assessOverallRiskLevel(array $riskAssessment): string
    {
        // Simplified risk assessment logic
        return empty($riskAssessment) ? 'low' : (count($riskAssessment) > 3 ? 'high' : 'medium');
    }

    /**
     * CalculateAverageConfidence
     */
    private function calculateAverageConfidence(array $confidenceScores): float
    {
        if (empty($confidenceScores)) {
            return 0.0;
        }

        return round(array_sum($confidenceScores) / count($confidenceScores), 2);
    }

    /**
     * AssessChurnRiskLevel
     */
    private function assessChurnRiskLevel(array $churnPrediction): string
    {
        // Simplified churn risk assessment
        return 'medium'; // Placeholder
    }

    /**
     * CalculateIntelligenceFreshness
     */
    private function calculateIntelligenceFreshness(): string
    {
        return 'current'; // Placeholder
    }

    /**
     * CalculateOptimizationScore
     */
    private function calculateOptimizationScore(array $insights): float
    {
        // Calculate overall optimization score based on insights
        return rand(70, 95); // Placeholder
    }

    /**
     * ExtractPriorityActions
     */
    private function extractPriorityActions(array $insights): array
    {
        // Extract high-priority optimization actions
        return array_slice($insights['improvement_opportunities'] ?? [], 0, 3);
    }

    /**
     * FilterCriticalAnomalies
     */
    private function filterCriticalAnomalies(array $anomalies): array
    {
        $critical = [];
        foreach ($anomalies as $type => $anomalyList) {
            if (is_array($anomalyList)) {
                $criticalItems = array_filter($anomalyList, function ($item) {
                    return isset($item['severity']) && $item['severity'] === 'high';
                });
                if (!empty($criticalItems)) {
                    $critical[$type] = $criticalItems;
                }
            }
        }

        return $critical;
    }

    /**
     * SummarizeAnomalies
     */
    private function summarizeAnomalies(array $anomalies): array
    {
        $summary = [
            'total_anomalies'       => 0,
            'by_type'               => [],
            'severity_distribution' => ['low' => 0, 'medium' => 0, 'high' => 0],
        ];

        foreach ($anomalies as $type => $anomalyList) {
            if (is_array($anomalyList)) {
                $count = count($anomalyList);
                $summary['total_anomalies'] += $count;
                $summary['by_type'][$type] = $count;

                foreach ($anomalyList as $anomaly) {
                    if (isset($anomaly['severity'])) {
                        $summary['severity_distribution'][$anomaly['severity']]++;
                    }
                }
            }
        }

        return $summary;
    }

    /**
     * DetermineSystemHealth
     */
    private function determineSystemHealth(array $anomalies): string
    {
        $summary = $this->summarizeAnomalies($anomalies);
        $criticalCount = $summary['severity_distribution']['high'] ?? 0;

        if ($criticalCount > 5) {
            return 'critical';
        }
        if ($criticalCount > 2) {
            return 'warning';
        }

        return 'healthy';
    }

    /**
     * DetermineAlertLevel
     */
    private function determineAlertLevel(array $criticalAnomalies): string
    {
        $totalCritical = array_sum(array_map('count', $criticalAnomalies));

        if ($totalCritical > 10) {
            return 'critical';
        }
        if ($totalCritical > 5) {
            return 'high';
        }
        if ($totalCritical > 0) {
            return 'medium';
        }

        return 'low';
    }
}
