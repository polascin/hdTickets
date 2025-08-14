<?php declare(strict_types=1);

namespace App\Services;

use App\Exports\PriceFluctuationExport;
use App\Exports\TicketAvailabilityTrendsExport;
use App\Models\ScrapedTicket;
use App\Models\TicketPriceHistory;
use App\Models\User;
use Barryvdh\DomPDF\Facades\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

use function is_array;

class AdvancedReportingService
{
    protected $reportTypes = [
        'ticket_availability_trends',
        'price_fluctuation_analysis',
        'platform_performance_comparison',
        'user_engagement_metrics',
        'category_analysis',
        'response_time_analysis',
    ];

    protected $exportFormats = ['pdf', 'xlsx', 'csv'];

    /**
     * Generate comprehensive report with charts and analytics
     */
    /**
     * GenerateAdvancedReport
     */
    public function generateAdvancedReport(string $reportType, array $parameters = []): array
    {
        $startDate = $parameters['start_date'] ?? now()->subMonth();
        $endDate = $parameters['end_date'] ?? now();
        $format = $parameters['format'] ?? 'pdf';
        $includeCharts = $parameters['include_charts'] ?? TRUE;

        switch ($reportType) {
            case 'ticket_availability_trends':
                return $this->generateTicketAvailabilityTrends($startDate, $endDate, $format, $includeCharts);
            case 'price_fluctuation_analysis':
                return $this->generatePriceFluctuationAnalysis($startDate, $endDate, $format, $includeCharts);
            case 'platform_performance_comparison':
                return $this->generatePlatformPerformanceComparison($startDate, $endDate, $format, $includeCharts);
            case 'user_engagement_metrics':
                return $this->generateUserEngagementMetrics($startDate, $endDate, $format, $includeCharts);
            default:
                throw new InvalidArgumentException("Unsupported report type: {$reportType}");
        }
    }

    /**
     * Schedule automated report generation
     */
    /**
     * ScheduleReport
     */
    public function scheduleReport(array $config): bool
    {
        $reportConfig = [
            'type'       => $config['type'],
            'parameters' => $config['parameters'] ?? [],
            'recipients' => $config['recipients'] ?? [],
            'frequency'  => $config['frequency'] ?? 'weekly', // daily, weekly, monthly
            'format'     => $config['format'] ?? 'pdf',
            'next_run'   => $this->calculateNextRun($config['frequency']),
            'created_at' => now(),
            'is_active'  => TRUE,
        ];

        // Store in database or cache for scheduled execution
        return DB::table('scheduled_reports')->insert($reportConfig);
    }

    /**
     * Generate ticket availability trends report
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    /**
     * GenerateTicketAvailabilityTrends
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    protected function generateTicketAvailabilityTrends($startDate, $endDate, $format, $includeCharts): array
    {
        // Collect data
        $trends = ScrapedTicket::whereBetween('scraped_at', [$startDate, $endDate])
            ->select(['status', DB::raw('count(*) as total')])
            ->groupBy('status')
            ->orderBy('total', 'desc')
            ->get();

        // Time-based trends
        $dailyTrends = ScrapedTicket::whereBetween('scraped_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(scraped_at) as date'),
                'status',
                DB::raw('count(*) as count'),
            ])
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Platform breakdown
        $platformBreakdown = ScrapedTicket::whereBetween('scraped_at', [$startDate, $endDate])
            ->select(['platform', 'status', DB::raw('count(*) as total')])
            ->groupBy('platform', 'status')
            ->get()
            ->groupBy('platform');

        $data = [
            'trends'             => $trends,
            'daily_trends'       => $dailyTrends,
            'platform_breakdown' => $platformBreakdown,
            'summary'            => [
                'total_tickets'       => $trends->sum('total'),
                'active_percentage'   => $this->calculatePercentage($trends, 'active'),
                'sold_out_percentage' => $this->calculatePercentage($trends, 'sold_out'),
                'expired_percentage'  => $this->calculatePercentage($trends, 'expired'),
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
                'days'       => $startDate->diffInDays($endDate),
            ],
        ];

        return $this->exportReport($data, 'ticket-availability-trends', $format, $includeCharts);
    }

    /**
     * Generate price fluctuation analysis report
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    /**
     * GeneratePriceFluctuationAnalysis
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    protected function generatePriceFluctuationAnalysis($startDate, $endDate, $format, $includeCharts): array
    {
        // Get price trends
        $priceData = TicketPriceHistory::betweenDates($startDate, $endDate)
            ->with('ticket')
            ->select([
                'ticket_id',
                DB::raw('AVG(price) as avg_price'),
                DB::raw('MIN(price) as min_price'),
                DB::raw('MAX(price) as max_price'),
                DB::raw('STDDEV(price) as price_volatility'),
                DB::raw('COUNT(*) as data_points'),
            ])
            ->groupBy('ticket_id')
            ->orderBy('price_volatility', 'desc')
            ->get();

        // Platform price comparison
        $platformPrices = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'platform',
                DB::raw('AVG(min_price) as avg_min_price'),
                DB::raw('AVG(max_price) as avg_max_price'),
                DB::raw('COUNT(*) as ticket_count'),
            ])
            ->groupBy('platform')
            ->get();

        // High volatility events
        $highVolatilityEvents = $priceData
            ->where('price_volatility', '>', 50)
            ->sortByDesc('price_volatility')
            ->take(10);

        $data = [
            'price_trends'           => $priceData,
            'platform_prices'        => $platformPrices,
            'high_volatility_events' => $highVolatilityEvents,
            'summary'                => [
                'total_events_analyzed' => $priceData->count(),
                'avg_volatility'        => $priceData->avg('price_volatility'),
                'highest_avg_price'     => $priceData->max('avg_price'),
                'lowest_avg_price'      => $priceData->min('avg_price'),
            ],
            'insights' => $this->generatePriceInsights($priceData, $platformPrices),
        ];

        return $this->exportReport($data, 'price-fluctuation-analysis', $format, $includeCharts);
    }

    /**
     * Generate platform performance comparison report
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    /**
     * GeneratePlatformPerformanceComparison
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    protected function generatePlatformPerformanceComparison($startDate, $endDate, $format, $includeCharts): array
    {
        $platformMetrics = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'platform',
                DB::raw('COUNT(*) as total_tickets'),
                DB::raw('AVG(min_price) as avg_price'),
                DB::raw('COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_tickets'),
                DB::raw('COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_tickets'),
                DB::raw('COUNT(CASE WHEN status = "active" THEN 1 END) as active_tickets'),
                DB::raw('COUNT(CASE WHEN status = "sold_out" THEN 1 END) as sold_out_tickets'),
            ])
            ->groupBy('platform')
            ->get()
            ->map(function ($platform) {
                $platform->availability_rate = $platform->total_tickets > 0
                    ? round(($platform->available_tickets / $platform->total_tickets) * 100, 2)
                    : 0;
                $platform->demand_rate = $platform->total_tickets > 0
                    ? round(($platform->high_demand_tickets / $platform->total_tickets) * 100, 2)
                    : 0;
                $platform->performance_score = $this->calculatePlatformScore($platform);

                return $platform;
            })
            ->sortByDesc('performance_score');

        // Response time comparison (if available)
        $responseTimeData = $this->getPlatformResponseTimes($startDate, $endDate);

        $data = [
            'platform_metrics' => $platformMetrics,
            'response_times'   => $responseTimeData,
            'rankings'         => $this->generatePlatformRankings($platformMetrics),
            'recommendations'  => $this->generatePlatformRecommendations($platformMetrics),
        ];

        return $this->exportReport($data, 'platform-performance-comparison', $format, $includeCharts);
    }

    /**
     * Generate user engagement metrics report
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    /**
     * GenerateUserEngagementMetrics
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $format
     * @param mixed $includeCharts
     */
    protected function generateUserEngagementMetrics($startDate, $endDate, $format, $includeCharts): array
    {
        $userMetrics = [
            'total_users'      => User::count(),
            'new_users'        => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_users'     => User::where('last_activity_at', '>=', $startDate)->count(),
            'user_growth_rate' => $this->calculateUserGrowthRate($startDate, $endDate),
        ];

        // Alert engagement
        $alertMetrics = DB::table('ticket_alerts')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('COUNT(*) as total_alerts'),
                DB::raw('COUNT(DISTINCT user_id) as users_with_alerts'),
                DB::raw('AVG(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_alert_rate'),
            ])
            ->first();

        // Activity patterns
        $dailyActivity = DB::table('activity_log')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as activity_count'),
                DB::raw('COUNT(DISTINCT causer_id) as unique_users'),
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $data = [
            'user_metrics'      => $userMetrics,
            'alert_metrics'     => $alertMetrics,
            'daily_activity'    => $dailyActivity,
            'engagement_trends' => $this->analyzeEngagementTrends($dailyActivity),
            'user_segmentation' => $this->getUserSegmentation(),
        ];

        return $this->exportReport($data, 'user-engagement-metrics', $format, $includeCharts);
    }

    /**
     * Export report in specified format
     */
    /**
     * ExportReport
     */
    protected function exportReport(array $data, string $reportName, string $format, bool $includeCharts): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "{$reportName}_{$timestamp}";

        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($data, $filename, $includeCharts);
            case 'xlsx':
                return $this->exportToExcel($data, $filename, $includeCharts);
            case 'csv':
                return $this->exportToCsv($data, $filename);
            default:
                throw new InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Export report to PDF with charts
     */
    /**
     * ExportToPdf
     */
    protected function exportToPdf(array $data, string $filename, bool $includeCharts): array
    {
        $pdf = Pdf::loadView('admin.reports.pdf.advanced_report', [
            'data'           => $data,
            'include_charts' => $includeCharts,
            'generated_at'   => now()->format('F d, Y H:i:s'),
        ]);

        $path = "reports/pdf/{$filename}.pdf";
        Storage::put($path, $pdf->output());

        return [
            'success'      => TRUE,
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'format'       => 'pdf',
        ];
    }

    /**
     * Export report to Excel with charts
     */
    /**
     * ExportToExcel
     */
    protected function exportToExcel(array $data, string $filename, bool $includeCharts): array
    {
        $path = "reports/excel/{$filename}.xlsx";

        // Use appropriate export class based on data type
        if (isset($data['trends'])) {
            Excel::store(new TicketAvailabilityTrendsExport($data['trends'], $data['period']['start_date'], $data['period']['end_date']), $path);
        } elseif (isset($data['price_trends'])) {
            Excel::store(new PriceFluctuationExport($data['price_trends'], $data['period']['start_date'], $data['period']['end_date']), $path);
        }

        return [
            'success'      => TRUE,
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'format'       => 'xlsx',
        ];
    }

    /**
     * Export report to CSV
     */
    /**
     * ExportToCsv
     */
    protected function exportToCsv(array $data, string $filename): array
    {
        $path = "reports/csv/{$filename}.csv";

        // Convert data to CSV format
        $csvData = $this->convertDataToCsv($data);
        Storage::put($path, $csvData);

        return [
            'success'      => TRUE,
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'format'       => 'csv',
        ];
    }

    /**
     * Helper methods
     */
    /**
     * CalculatePercentage
     */
    protected function calculatePercentage(Collection $collection, string $status): float
    {
        $total = $collection->sum('total');
        $statusCount = $collection->where('status', $status)->first()->total ?? 0;

        return $total > 0 ? round(($statusCount / $total) * 100, 2) : 0;
    }

    /**
     * CalculatePlatformScore
     *
     * @param mixed $platform
     */
    protected function calculatePlatformScore($platform): float
    {
        // Weighted scoring: availability (40%), demand (30%), total tickets (30%)
        $availabilityScore = $platform->availability_rate * 0.4;
        $demandScore = $platform->demand_rate * 0.3;
        $volumeScore = min(100, ($platform->total_tickets / 1000) * 100) * 0.3;

        return round($availabilityScore + $demandScore + $volumeScore, 2);
    }

    /**
     * GeneratePriceInsights
     */
    protected function generatePriceInsights(Collection $priceData, Collection $platformPrices): array
    {
        return [
            'most_volatile_platform' => $platformPrices->sortByDesc('avg_max_price')->first()->platform ?? 'N/A',
            'most_stable_pricing'    => $priceData->sortBy('price_volatility')->first()->ticket_id ?? 'N/A',
            'price_trend_direction'  => $this->determinePriceTrend($priceData),
            'recommendation'         => 'Monitor high volatility events for potential arbitrage opportunities',
        ];
    }

    /**
     * GeneratePlatformRankings
     */
    protected function generatePlatformRankings(Collection $platformMetrics): array
    {
        return $platformMetrics->map(function ($platform, $index) {
            return [
                'rank'       => $index + 1,
                'platform'   => $platform->platform,
                'score'      => $platform->performance_score,
                'strengths'  => $this->identifyPlatformStrengths($platform),
                'weaknesses' => $this->identifyPlatformWeaknesses($platform),
            ];
        })->values()->toArray();
    }

    /**
     * GeneratePlatformRecommendations
     */
    protected function generatePlatformRecommendations(Collection $platformMetrics): array
    {
        return [
            'focus_platforms'           => $platformMetrics->take(3)->pluck('platform')->toArray(),
            'improvement_opportunities' => $platformMetrics->where('availability_rate', '<', 50)->pluck('platform')->toArray(),
            'high_demand_platforms'     => $platformMetrics->where('demand_rate', '>', 70)->pluck('platform')->toArray(),
        ];
    }

    /**
     * CalculateUserGrowthRate
     *
     * @param mixed $startDate
     * @param mixed $endDate
     */
    protected function calculateUserGrowthRate($startDate, $endDate): float
    {
        $previousPeriodStart = $startDate->copy()->subDays($startDate->diffInDays($endDate));
        $currentUsers = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $previousUsers = User::whereBetween('created_at', [$previousPeriodStart, $startDate])->count();

        return $previousUsers > 0 ? round((($currentUsers - $previousUsers) / $previousUsers) * 100, 2) : 0;
    }

    /**
     * CalculateNextRun
     */
    protected function calculateNextRun(string $frequency): Carbon
    {
        switch ($frequency) {
            case 'daily':
                return now()->addDay();
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            default:
                return now()->addWeek();
        }
    }

    /**
     * ConvertDataToCsv
     */
    protected function convertDataToCsv(array $data): string
    {
        $csv = '';
        foreach ($data as $key => $value) {
            if (is_array($value) || $value instanceof Collection) {
                $csv .= "{$key}\n";
                if ($value instanceof Collection) {
                    $value = $value->toArray();
                }
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $csv .= implode(',', $item) . "\n";
                    } else {
                        $csv .= $item . "\n";
                    }
                }
                $csv .= "\n";
            }
        }

        return $csv;
    }

    // Additional helper methods would be implemented here...
    /**
     * Get  platform response times
     *
     * @param mixed $startDate
     * @param mixed $endDate
     */
    protected function getPlatformResponseTimes($startDate, $endDate): Collection
    {
        return collect();
    }

    /**
     * AnalyzeEngagementTrends
     *
     * @param mixed $dailyActivity
     */
    protected function analyzeEngagementTrends($dailyActivity): array
    {
        return [];
    }

    /**
     * Get  user segmentation
     */
    protected function getUserSegmentation(): array
    {
        return [];
    }

    /**
     * DeterminePriceTrend
     *
     * @param mixed $priceData
     */
    protected function determinePriceTrend($priceData): string
    {
        return 'stable';
    }

    /**
     * IdentifyPlatformStrengths
     *
     * @param mixed $platform
     */
    protected function identifyPlatformStrengths($platform): array
    {
        return [];
    }

    /**
     * IdentifyPlatformWeaknesses
     *
     * @param mixed $platform
     */
    protected function identifyPlatformWeaknesses($platform): array
    {
        return [];
    }
}
