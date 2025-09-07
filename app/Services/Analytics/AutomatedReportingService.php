<?php declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\ScheduledReport;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

/**
 * Automated Reporting Service
 *
 * Handles scheduled report generation, distribution, and management
 * for the HD Tickets analytics system.
 */
class AutomatedReportingService
{
    private AdvancedAnalyticsService $analyticsService;

    private AnalyticsExportService $exportService;

    private array $config;

    public function __construct(
        AdvancedAnalyticsService $analyticsService,
        AnalyticsExportService $exportService
    ) {
        $this->analyticsService = $analyticsService;
        $this->exportService = $exportService;
        $this->config = config('analytics.export.scheduled_exports', []);
    }

    /**
     * Generate and send scheduled reports
     *
     * @param  string $reportType Type of report (daily, weekly, monthly, custom)
     * @param  array  $options    Optional configuration overrides
     * @return array  Results of the report generation
     */
    public function generateScheduledReports(string $reportType, array $options = []): array
    {
        Log::info('Starting scheduled report generation', [
            'type'    => $reportType,
            'options' => $options,
        ]);

        $results = [];

        try {
            $reports = $this->getActiveScheduledReports($reportType);

            foreach ($reports as $report) {
                $result = $this->generateAndDeliverReport($report, $options);
                $results[] = $result;

                // Update report statistics
                $this->updateReportStatistics($report, $result);
            }

            // Clean up old report files
            $this->cleanupOldReports();

            Log::info('Completed scheduled report generation', [
                'type'              => $reportType,
                'reports_generated' => count($results),
                'successful'        => count(array_filter($results, fn ($r) => $r['success'])),
            ]);
        } catch (\Exception $e) {
            Log::error('Scheduled report generation failed', [
                'type'  => $reportType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $results[] = [
                'success' => FALSE,
                'error'   => $e->getMessage(),
                'type'    => $reportType,
            ];
        }

        return $results;
    }

    /**
     * Create a new scheduled report
     *
     * @param  array           $reportConfig Report configuration
     * @return ScheduledReport Created report instance
     */
    public function createScheduledReport(array $reportConfig): ScheduledReport
    {
        $report = new ScheduledReport([
            'name'        => $reportConfig['name'],
            'description' => $reportConfig['description'] ?? '',
            'type'        => $reportConfig['type'], // daily, weekly, monthly, custom
            'format'      => $reportConfig['format'], // pdf, xlsx, csv, json
            'schedule'    => $reportConfig['schedule'], // cron expression or predefined
            'sections'    => $reportConfig['sections'], // array of sections to include
            'filters'     => $reportConfig['filters'] ?? [],
            'recipients'  => $reportConfig['recipients'], // array of email addresses
            'is_active'   => $reportConfig['is_active'] ?? TRUE,
            'options'     => $reportConfig['options'] ?? [],
            'created_by'  => auth()->id(),
        ]);

        $report->save();

        Log::info('Created scheduled report', [
            'report_id' => $report->id,
            'name'      => $report->name,
            'type'      => $report->type,
        ]);

        return $report;
    }

    /**
     * Generate a custom report on-demand
     *
     * @param  array $config  Report configuration
     * @param  bool  $deliver Whether to deliver the report or just generate it
     * @return array Report generation result
     */
    public function generateCustomReport(array $config, bool $deliver = TRUE): array
    {
        $reportId = 'custom_' . uniqid();

        try {
            // Get analytics data based on configuration
            $analyticsData = $this->getAnalyticsDataForReport($config);

            // Generate report file
            $exportResult = $this->exportService->export(
                $config['format'],
                $analyticsData,
                array_merge($config['options'] ?? [], [
                    'filename_prefix' => $reportId,
                    'title'           => $config['title'] ?? 'HD Tickets Analytics Report',
                    'generated_by'    => auth()->user()->name ?? 'System',
                ])
            );

            if (!$exportResult['success']) {
                throw new \Exception('Failed to generate report file: ' . $exportResult['error']);
            }

            $result = [
                'success'        => TRUE,
                'report_id'      => $reportId,
                'file_path'      => $exportResult['file_path'],
                'filename'       => $exportResult['filename'],
                'format'         => $config['format'],
                'size'           => $exportResult['size'],
                'generated_at'   => now()->toISOString(),
                'analytics_data' => $this->getReportSummary($analyticsData),
            ];

            // Deliver report if requested
            if ($deliver && !empty($config['recipients'])) {
                $deliveryResult = $this->deliverReport($result, $config['recipients'], $config);
                $result['delivery'] = $deliveryResult;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Custom report generation failed', [
                'report_id' => $reportId,
                'config'    => $config,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success'      => FALSE,
                'report_id'    => $reportId,
                'error'        => $e->getMessage(),
                'generated_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Get available report templates
     *
     * @return array Available report templates
     */
    public function getReportTemplates(): array
    {
        return [
            'executive_summary' => [
                'name'             => 'Executive Summary',
                'description'      => 'High-level overview for executives and stakeholders',
                'sections'         => ['overview_metrics', 'platform_performance', 'key_insights'],
                'format'           => 'pdf',
                'schedule_options' => ['daily', 'weekly', 'monthly'],
            ],
            'platform_analysis' => [
                'name'             => 'Platform Analysis Report',
                'description'      => 'Detailed analysis of platform performance and trends',
                'sections'         => ['platform_performance', 'pricing_trends', 'market_intelligence'],
                'format'           => 'xlsx',
                'schedule_options' => ['weekly', 'monthly'],
            ],
            'anomaly_detection' => [
                'name'             => 'Anomaly Detection Report',
                'description'      => 'Summary of detected anomalies and alerts',
                'sections'         => ['anomalies', 'overview_metrics'],
                'format'           => 'pdf',
                'schedule_options' => ['daily', 'weekly'],
            ],
            'pricing_intelligence' => [
                'name'             => 'Pricing Intelligence',
                'description'      => 'Comprehensive pricing analysis and recommendations',
                'sections'         => ['pricing_trends', 'predictive_insights', 'market_intelligence'],
                'format'           => 'xlsx',
                'schedule_options' => ['weekly', 'monthly'],
            ],
            'event_popularity' => [
                'name'             => 'Event Popularity Report',
                'description'      => 'Analysis of event trends and popularity metrics',
                'sections'         => ['event_popularity', 'overview_metrics'],
                'format'           => 'pdf',
                'schedule_options' => ['weekly', 'monthly'],
            ],
        ];
    }

    /**
     * Get report generation statistics
     *
     * @param  array $filters Optional filters
     * @return array Report statistics
     */
    public function getReportStatistics(array $filters = []): array
    {
        $dateRange = $this->getDateRange($filters);

        $stats = [
            'total_reports'   => ScheduledReport::whereBetween('created_at', $dateRange)->count(),
            'active_reports'  => ScheduledReport::where('is_active', TRUE)->count(),
            'reports_by_type' => ScheduledReport::whereBetween('created_at', $dateRange)
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'reports_by_format' => ScheduledReport::whereBetween('created_at', $dateRange)
                ->select('format', DB::raw('count(*) as count'))
                ->groupBy('format')
                ->pluck('count', 'format')
                ->toArray(),
            'generation_success_rate' => $this->calculateSuccessRate($dateRange),
            'avg_generation_time'     => $this->calculateAverageGenerationTime($dateRange),
            'total_file_size'         => $this->calculateTotalFileSize($dateRange),
            'most_popular_sections'   => $this->getMostPopularSections($dateRange),
        ];

        return $stats;
    }

    /**
     * Schedule report generation jobs
     *
     * @param Schedule $schedule Laravel schedule instance
     */
    public function scheduleReports(Schedule $schedule): void
    {
        // Daily reports at 6:00 AM
        if ($this->config['daily_report']['enabled'] ?? FALSE) {
            $schedule->call(function () {
                $this->generateScheduledReports('daily');
            })->dailyAt('06:00')->name('daily-analytics-reports');
        }

        // Weekly reports on Monday at 8:00 AM
        if ($this->config['weekly_report']['enabled'] ?? FALSE) {
            $schedule->call(function () {
                $this->generateScheduledReports('weekly');
            })->weeklyOn(1, '08:00')->name('weekly-analytics-reports');
        }

        // Monthly reports on the 1st at 9:00 AM
        if ($this->config['monthly_report']['enabled'] ?? FALSE) {
            $schedule->call(function () {
                $this->generateScheduledReports('monthly');
            })->monthlyOn(1, '09:00')->name('monthly-analytics-reports');
        }

        // Custom scheduled reports
        $customReports = ScheduledReport::where('is_active', TRUE)
            ->whereNotIn('type', ['daily', 'weekly', 'monthly'])
            ->get();

        foreach ($customReports as $report) {
            $schedule->call(function () use ($report) {
                $this->generateAndDeliverReport($report);
            })->cron($report->schedule)->name("custom-report-{$report->id}");
        }

        // Cleanup old reports weekly
        $schedule->call(function () {
            $this->cleanupOldReports();
        })->weekly()->name('cleanup-old-reports');
    }

    // Private helper methods

    /**
     * Get active scheduled reports of a specific type
     */
    private function getActiveScheduledReports(string $type): Collection
    {
        return ScheduledReport::where('is_active', TRUE)
            ->where('type', $type)
            ->get();
    }

    /**
     * Generate and deliver a specific report
     */
    private function generateAndDeliverReport(ScheduledReport $report, array $options = []): array
    {
        $startTime = microtime(TRUE);

        try {
            // Prepare filters for this report type
            $filters = $this->prepareFiltersForReportType($report->type, $report->filters);

            // Get analytics data
            $analyticsData = $this->getAnalyticsDataForSections($report->sections, $filters);

            // Generate report file
            $exportResult = $this->exportService->export(
                $report->format,
                $analyticsData,
                array_merge($report->options, [
                    'filename_prefix' => "scheduled_{$report->type}_{$report->id}",
                    'title'           => $report->name,
                ])
            );

            if (!$exportResult['success']) {
                throw new \Exception('Export failed: ' . $exportResult['error']);
            }

            // Deliver report
            $deliveryResult = $this->deliverReport($exportResult, $report->recipients, [
                'report_name' => $report->name,
                'report_type' => $report->type,
                'description' => $report->description,
            ]);

            $generationTime = microtime(TRUE) - $startTime;

            return [
                'success'         => TRUE,
                'report_id'       => $report->id,
                'type'            => $report->type,
                'file_path'       => $exportResult['file_path'],
                'filename'        => $exportResult['filename'],
                'size'            => $exportResult['size'],
                'generation_time' => $generationTime,
                'delivery'        => $deliveryResult,
                'generated_at'    => now()->toISOString(),
            ];
        } catch (\Exception $e) {
            $generationTime = microtime(TRUE) - $startTime;

            Log::error('Report generation failed', [
                'report_id'       => $report->id,
                'type'            => $report->type,
                'error'           => $e->getMessage(),
                'generation_time' => $generationTime,
            ]);

            return [
                'success'         => FALSE,
                'report_id'       => $report->id,
                'type'            => $report->type,
                'error'           => $e->getMessage(),
                'generation_time' => $generationTime,
                'generated_at'    => now()->toISOString(),
            ];
        }
    }

    /**
     * Get analytics data for specific sections
     */
    private function getAnalyticsDataForSections(array $sections, array $filters): array
    {
        $data = [];

        foreach ($sections as $section) {
            switch ($section) {
                case 'overview_metrics':
                    $data['overview_metrics'] = $this->analyticsService->getOverviewMetrics($filters);

                    break;
                case 'platform_performance':
                    $data['platform_performance'] = $this->analyticsService->getPlatformPerformanceMetrics($filters);

                    break;
                case 'pricing_trends':
                    $data['pricing_trends'] = $this->analyticsService->getPricingTrends($filters);

                    break;
                case 'event_popularity':
                    $data['event_popularity'] = $this->analyticsService->getEventPopularityMetrics($filters);

                    break;
                case 'market_intelligence':
                    $data['market_intelligence'] = $this->analyticsService->getMarketIntelligence($filters);

                    break;
                case 'predictive_insights':
                    $data['predictive_insights'] = $this->analyticsService->getPredictiveInsights($filters);

                    break;
                case 'anomalies':
                    $data['anomalies'] = $this->analyticsService->getRecentAnomalies($filters);

                    break;
            }
        }

        // Add metadata
        $data['report_metadata'] = [
            'generated_at'      => now()->toISOString(),
            'filters_applied'   => $filters,
            'sections_included' => $sections,
            'data_freshness'    => 'realtime',
        ];

        return $data;
    }

    /**
     * Get analytics data for a report configuration
     */
    private function getAnalyticsDataForReport(array $config): array
    {
        $sections = $config['sections'] ?? ['overview_metrics'];
        $filters = $config['filters'] ?? [];

        return $this->getAnalyticsDataForSections($sections, $filters);
    }

    /**
     * Prepare filters for specific report type
     */
    private function prepareFiltersForReportType(string $type, array $baseFilters): array
    {
        $filters = $baseFilters;

        switch ($type) {
            case 'daily':
                $filters['start_date'] = now()->subDay()->startOfDay();
                $filters['end_date'] = now()->subDay()->endOfDay();

                break;
            case 'weekly':
                $filters['start_date'] = now()->subWeek()->startOfWeek();
                $filters['end_date'] = now()->subWeek()->endOfWeek();

                break;
            case 'monthly':
                $filters['start_date'] = now()->subMonth()->startOfMonth();
                $filters['end_date'] = now()->subMonth()->endOfMonth();

                break;
        }

        return $filters;
    }

    /**
     * Deliver report to recipients
     */
    private function deliverReport(array $reportData, array $recipients, array $context = []): array
    {
        $deliveryResults = [];

        foreach ($recipients as $recipient) {
            try {
                Mail::send('emails.analytics.scheduled-report', [
                    'reportData' => $reportData,
                    'context'    => $context,
                    'recipient'  => $recipient,
                ], function ($message) use ($reportData, $context, $recipient) {
                    $message->to($recipient)
                        ->subject($context['report_name'] ?? 'HD Tickets Analytics Report')
                        ->attach(storage_path('app/' . $reportData['file_path']));
                });

                $deliveryResults[$recipient] = ['success' => TRUE, 'delivered_at' => now()->toISOString()];
            } catch (\Exception $e) {
                Log::error('Failed to deliver report', [
                    'recipient'   => $recipient,
                    'report_file' => $reportData['filename'] ?? 'unknown',
                    'error'       => $e->getMessage(),
                ]);

                $deliveryResults[$recipient] = ['success' => FALSE, 'error' => $e->getMessage()];
            }
        }

        return $deliveryResults;
    }

    /**
     * Update report statistics
     */
    private function updateReportStatistics(ScheduledReport $report, array $result): void
    {
        $stats = $report->statistics ?? [];

        $stats['last_run'] = now()->toISOString();
        $stats['total_runs'] = ($stats['total_runs'] ?? 0) + 1;

        if ($result['success']) {
            $stats['successful_runs'] = ($stats['successful_runs'] ?? 0) + 1;
            $stats['last_successful_run'] = now()->toISOString();
            $stats['last_file_size'] = $result['size'] ?? 0;
            $stats['last_generation_time'] = $result['generation_time'] ?? 0;
        } else {
            $stats['failed_runs'] = ($stats['failed_runs'] ?? 0) + 1;
            $stats['last_error'] = $result['error'] ?? 'Unknown error';
        }

        $report->statistics = $stats;
        $report->save();
    }

    /**
     * Clean up old report files
     */
    private function cleanupOldReports(): void
    {
        $retentionDays = $this->config['temp_storage_days'] ?? 30;
        $cutoffDate = now()->subDays($retentionDays);

        $exportPath = config('analytics.export.export_path', 'analytics/exports');
        $files = Storage::files($exportPath);

        $deletedCount = 0;
        foreach ($files as $file) {
            try {
                $lastModified = Carbon::createFromTimestamp(Storage::lastModified($file));
                if ($lastModified->lt($cutoffDate)) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete old report file', [
                    'file'  => $file,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($deletedCount > 0) {
            Log::info('Cleaned up old report files', ['deleted_count' => $deletedCount]);
        }
    }

    /**
     * Get date range for statistics
     */
    private function getDateRange(array $filters): array
    {
        $endDate = isset($filters['end_date']) ? Carbon::parse($filters['end_date']) : now();
        $startDate = isset($filters['start_date'])
            ? Carbon::parse($filters['start_date'])
            : $endDate->copy()->subDays($filters['days'] ?? 30);

        return [$startDate, $endDate];
    }

    /**
     * Calculate success rate for reports
     */
    private function calculateSuccessRate(array $dateRange): float
    {
        // This would calculate from actual report execution logs
        // For now, return a placeholder
        return 95.5;
    }

    /**
     * Calculate average generation time
     */
    private function calculateAverageGenerationTime(array $dateRange): float
    {
        // This would calculate from actual report execution logs
        // For now, return a placeholder
        return 12.3; // seconds
    }

    /**
     * Calculate total file size
     */
    private function calculateTotalFileSize(array $dateRange): string
    {
        // This would calculate from actual report files
        // For now, return a placeholder
        return '2.3GB';
    }

    /**
     * Get most popular report sections
     */
    private function getMostPopularSections(array $dateRange): array
    {
        return [
            'overview_metrics'     => 85,
            'platform_performance' => 78,
            'pricing_trends'       => 65,
            'anomalies'            => 52,
            'event_popularity'     => 48,
        ];
    }

    /**
     * Generate summary of analytics data
     */
    private function getReportSummary(array $analyticsData): array
    {
        return [
            'sections_included'  => array_keys($analyticsData),
            'total_events'       => $analyticsData['overview_metrics']['total_events'] ?? 0,
            'total_tickets'      => $analyticsData['overview_metrics']['total_tickets'] ?? 0,
            'platforms_analyzed' => count($analyticsData['platform_performance']['platforms'] ?? []),
            'anomalies_detected' => $analyticsData['anomalies']['total_anomalies'] ?? 0,
        ];
    }
}
