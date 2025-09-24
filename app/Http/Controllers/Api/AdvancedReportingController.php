<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdvancedReportingService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdvancedReportingController extends Controller
{
    public function __construct(protected AdvancedReportingService $reportingService)
    {
    }

    /**
     * Generate advanced analytics report
     */
    /**
     * GenerateReport
     */
    public function generateReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'           => 'required|in:ticket_availability_trends,price_fluctuation_analysis,platform_performance_comparison,user_engagement_metrics',
            'format'         => 'sometimes|in:pdf,xlsx,csv',
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date|after_or_equal:start_date',
            'include_charts' => 'sometimes|boolean',
            'parameters'     => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $parameters = array_merge($request->get('parameters', []), [
                'start_date'     => $request->get('start_date') ? Carbon::parse($request->get('start_date')) : NULL,
                'end_date'       => $request->get('end_date') ? Carbon::parse($request->get('end_date')) : NULL,
                'format'         => $request->get('format', 'pdf'),
                'include_charts' => $request->get('include_charts', TRUE),
            ]);

            $result = $this->reportingService->generateAdvancedReport(
                $request->get('type'),
                array_filter($parameters),
            );

            return response()->json([
                'success' => TRUE,
                'data'    => $result,
                'message' => 'Report generated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get available report types and their configurations
     */
    /**
     * Get  report types
     */
    public function getReportTypes(): JsonResponse
    {
        $reportTypes = [
            [
                'type'        => 'ticket_availability_trends',
                'name'        => 'Ticket Availability Trends',
                'description' => 'Analyze ticket availability patterns across platforms and time periods',
                'parameters'  => [
                    'start_date' => 'optional',
                    'end_date'   => 'optional',
                    'platforms'  => 'optional array',
                    'categories' => 'optional array',
                ],
                'formats'        => ['pdf', 'xlsx', 'csv'],
                'estimated_time' => '2-5 minutes',
            ],
            [
                'type'        => 'price_fluctuation_analysis',
                'name'        => 'Price Fluctuation Analysis',
                'description' => 'Track price changes and volatility patterns for events',
                'parameters'  => [
                    'start_date'     => 'optional',
                    'end_date'       => 'optional',
                    'event_types'    => 'optional array',
                    'min_volatility' => 'optional number',
                ],
                'formats'        => ['pdf', 'xlsx', 'csv'],
                'estimated_time' => '3-7 minutes',
            ],
            [
                'type'        => 'platform_performance_comparison',
                'name'        => 'Platform Performance Comparison',
                'description' => 'Compare performance metrics across ticket platforms',
                'parameters'  => [
                    'start_date' => 'optional',
                    'end_date'   => 'optional',
                    'platforms'  => 'optional array',
                    'metrics'    => 'optional array',
                ],
                'formats'        => ['pdf', 'xlsx', 'csv'],
                'estimated_time' => '1-3 minutes',
            ],
            [
                'type'        => 'user_engagement_metrics',
                'name'        => 'User Engagement Metrics',
                'description' => 'Analyze user behavior and engagement patterns',
                'parameters'  => [
                    'start_date'    => 'optional',
                    'end_date'      => 'optional',
                    'user_segments' => 'optional array',
                ],
                'formats'        => ['pdf', 'xlsx', 'csv'],
                'estimated_time' => '2-4 minutes',
            ],
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $reportTypes,
        ]);
    }

    /**
     * Schedule a recurring report
     */
    /**
     * ScheduleReport
     */
    public function scheduleReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'type'         => 'required|in:ticket_availability_trends,price_fluctuation_analysis,platform_performance_comparison,user_engagement_metrics',
            'frequency'    => 'required|in:daily,weekly,monthly',
            'format'       => 'sometimes|in:pdf,xlsx,csv',
            'recipients'   => 'required|array|min:1',
            'recipients.*' => 'email',
            'parameters'   => 'sometimes|array',
            'description'  => 'sometimes|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $config = [
                'name'        => $request->get('name'),
                'type'        => $request->get('type'),
                'frequency'   => $request->get('frequency'),
                'format'      => $request->get('format', 'pdf'),
                'recipients'  => $request->get('recipients'),
                'parameters'  => $request->get('parameters', []),
                'description' => $request->get('description'),
                'created_by'  => auth()->id(),
            ];

            $success = $this->reportingService->scheduleReport($config);

            if ($success) {
                return response()->json([
                    'success' => TRUE,
                    'message' => 'Report scheduled successfully',
                ]);
            }

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to schedule report',
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to schedule report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get scheduled reports
     */
    /**
     * Get  scheduled reports
     */
    public function getScheduledReports(): JsonResponse
    {
        try {
            $reports = DB::table('scheduled_reports')
                ->select([
                    'id',
                    'name',
                    'type',
                    'frequency',
                    'format',
                    'next_run',
                    'last_run',
                    'is_active',
                    'description',
                    'created_at',
                ])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => TRUE,
                'data'    => $reports,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve scheduled reports',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Update scheduled report
     *
     * @param mixed $id
     */
    /**
     * UpdateScheduledReport
     *
     * @param mixed $id
     */
    public function updateScheduledReport(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'sometimes|string|max:255',
            'frequency'    => 'sometimes|in:daily,weekly,monthly',
            'format'       => 'sometimes|in:pdf,xlsx,csv',
            'recipients'   => 'sometimes|array|min:1',
            'recipients.*' => 'email',
            'parameters'   => 'sometimes|array',
            'description'  => 'sometimes|string|max:1000',
            'is_active'    => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only([
                'name', 'frequency', 'format', 'recipients',
                'parameters', 'description', 'is_active',
            ]);
            $updateData['updated_at'] = now();

            $updated = DB::table('scheduled_reports')
                ->where('id', $id)
                ->update($updateData);

            if ($updated) {
                return response()->json([
                    'success' => TRUE,
                    'message' => 'Scheduled report updated successfully',
                ]);
            }

            return response()->json([
                'success' => FALSE,
                'message' => 'Scheduled report not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update scheduled report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Delete scheduled report
     *
     * @param mixed $id
     */
    /**
     * DeleteScheduledReport
     */
    public function deleteScheduledReport(int $id): JsonResponse
    {
        try {
            $deleted = DB::table('scheduled_reports')
                ->where('id', $id)
                ->delete();

            if ($deleted) {
                return response()->json([
                    'success' => TRUE,
                    'message' => 'Scheduled report deleted successfully',
                ]);
            }

            return response()->json([
                'success' => FALSE,
                'message' => 'Scheduled report not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete scheduled report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get report generation status
     */
    /**
     * Get  report status
     */
    public function getReportStatus(Request $request): JsonResponse
    {
        $reportId = $request->get('report_id');

        // This would typically check a job queue or cache for status
        // For now, we'll return a mock response
        return response()->json([
            'success' => TRUE,
            'data'    => [
                'report_id'            => $reportId,
                'status'               => 'completed', // pending, processing, completed, failed
                'progress'             => 100,
                'estimated_completion' => NULL,
                'download_url'         => $reportId ? "/api/reports/download/{$reportId}" : NULL,
            ],
        ]);
    }

    /**
     * Download generated report
     *
     * @param mixed $reportId
     */
    public function downloadReport($reportId)
    {
        // This would typically retrieve the report file from storage
        // and return it as a download response
        try {
            // Mock implementation - in reality, you'd retrieve from storage
            $filePath = storage_path("app/reports/pdf/report_{$reportId}.pdf");

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Report file not found',
                ], 404);
            }

            return response()->download($filePath);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to download report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get report builder configuration
     */
    /**
     * Get  report builder config
     */
    public function getReportBuilderConfig(): JsonResponse
    {
        $config = [
            'data_sources' => [
                'scraped_tickets' => [
                    'name'         => 'Scraped Tickets',
                    'fields'       => ['platform', 'status', 'price', 'event_date', 'venue', 'category'],
                    'aggregations' => ['count', 'sum', 'avg', 'min', 'max'],
                ],
                'price_history' => [
                    'name'         => 'Price History',
                    'fields'       => ['price', 'quantity', 'recorded_at', 'source'],
                    'aggregations' => ['count', 'sum', 'avg', 'min', 'max', 'stddev'],
                ],
                'users' => [
                    'name'         => 'Users',
                    'fields'       => ['role', 'created_at', 'last_activity_at', 'email_verified_at'],
                    'aggregations' => ['count', 'distinct_count'],
                ],
                'activities' => [
                    'name'         => 'User Activities',
                    'fields'       => ['event', 'created_at', 'properties'],
                    'aggregations' => ['count', 'distinct_count'],
                ],
            ],
            'chart_types' => [
                'line'    => 'Line Chart',
                'bar'     => 'Bar Chart',
                'pie'     => 'Pie Chart',
                'area'    => 'Area Chart',
                'scatter' => 'Scatter Plot',
            ],
            'time_ranges' => [
                '7d'     => 'Last 7 days',
                '30d'    => 'Last 30 days',
                '90d'    => 'Last 90 days',
                '1y'     => 'Last year',
                'custom' => 'Custom range',
            ],
            'export_formats' => ['pdf', 'xlsx', 'csv', 'png', 'jpg'],
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $config,
        ]);
    }

    /**
     * Build custom report
     */
    /**
     * BuildCustomReport
     */
    public function buildCustomReport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'data_source'  => 'required|string',
            'fields'       => 'required|array|min:1',
            'aggregations' => 'sometimes|array',
            'filters'      => 'sometimes|array',
            'chart_type'   => 'sometimes|string',
            'time_range'   => 'sometimes|string',
            'format'       => 'sometimes|in:pdf,xlsx,csv',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            // This would implement the custom report building logic
            // For now, we'll return a success response
            return response()->json([
                'success' => TRUE,
                'message' => 'Custom report built successfully',
                'data'    => [
                    'report_id' => uniqid('custom_'),
                    'name'      => $request->get('name'),
                    'status'    => 'processing',
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to build custom report',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
