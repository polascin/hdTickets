<?php declare(strict_types=1);

namespace App\Services;

use App\Exports\GenericArrayExport;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Models\TicketPriceHistory;
use App\Models\User;
use Barryvdh\DomPDF\Facades\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

use function in_array;

class DataExportService
{
    protected $supportedFormats = ['csv', 'xlsx', 'pdf', 'json'];

    protected $exportTypes = [
        'ticket_trends',
        'price_analysis',
        'platform_performance',
        'user_engagement',
        'demand_patterns',
        'comprehensive_analytics',
    ];

    /**
     * Export ticket trends data
     */
    /**
     * ExportTicketTrends
     */
    public function exportTicketTrends(array $filters = [], string $format = 'xlsx'): array
    {
        $this->validateFormat($format);

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();
        $platforms = $filters['platforms'] ?? [];

        $data = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->when(!empty($platforms), function ($query) use ($platforms): void {
                $query->whereIn('platform', $platforms);
            })
            ->select([
                'id',
                'title',
                'platform',
                'status',
                'min_price',
                'max_price',
                'is_available',
                'is_high_demand',
                'venue',
                'event_date',
                'created_at',
                'updated_at',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($ticket): array => [
                'ID'            => $ticket->id,
                'Event Title'   => $ticket->title,
                'Platform'      => ucfirst((string) $ticket->platform),
                'Status'        => ucfirst((string) $ticket->status),
                'Min Price ($)' => $ticket->min_price,
                'Max Price ($)' => $ticket->max_price,
                'Available'     => $ticket->is_available ? 'Yes' : 'No',
                'High Demand'   => $ticket->is_high_demand ? 'Yes' : 'No',
                'Venue'         => $ticket->venue,
                'Event Date'    => $ticket->event_date ? $ticket->event_date->format('Y-m-d H:i') : 'TBD',
                'Scraped At'    => $ticket->created_at->format('Y-m-d H:i:s'),
                'Last Updated'  => $ticket->updated_at->format('Y-m-d H:i:s'),
            ]);

        $metadata = [
            'title'  => 'Ticket Trends Report',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
            'filters'       => $filters,
            'total_records' => $data->count(),
            'generated_at'  => now()->format('Y-m-d H:i:s'),
        ];

        return $this->generateExport($data, $metadata, 'ticket-trends', $format);
    }

    /**
     * Export price analysis data
     */
    /**
     * ExportPriceAnalysis
     */
    public function exportPriceAnalysis(array $filters = [], string $format = 'xlsx'): array
    {
        $this->validateFormat($format);

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        $priceData = TicketPriceHistory::whereBetween('recorded_at', [$startDate, $endDate])
            ->with(['ticket:id,title,platform,venue'])
            ->select([
                'ticket_id',
                'price',
                'quantity',
                'source',
                'recorded_at',
                'price_change',
                'created_at',
            ])
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->map(fn ($record): array => [
                'Ticket ID'        => $record->ticket_id,
                'Event Title'      => $record->ticket->title ?? 'Unknown',
                'Platform'         => ucfirst($record->ticket->platform ?? 'Unknown'),
                'Venue'            => $record->ticket->venue ?? 'Unknown',
                'Price ($)'        => number_format($record->price, 2),
                'Quantity'         => $record->quantity,
                'Source'           => $record->source,
                'Price Change ($)' => $record->price_change ? number_format($record->price_change, 2) : '0.00',
                'Recorded At'      => $record->recorded_at->format('Y-m-d H:i:s'),
                'Created At'       => $record->created_at->format('Y-m-d H:i:s'),
            ]);

        // Add statistical analysis
        $statistics = $this->calculatePriceStatistics($priceData);

        $metadata = [
            'title'  => 'Price Analysis Report',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
            'statistics'    => $statistics,
            'total_records' => $priceData->count(),
            'generated_at'  => now()->format('Y-m-d H:i:s'),
        ];

        return $this->generateExport($priceData, $metadata, 'price-analysis', $format);
    }

    /**
     * Export platform performance data
     */
    /**
     * ExportPlatformPerformance
     */
    public function exportPlatformPerformance(array $filters = [], string $format = 'xlsx'): array
    {
        $this->validateFormat($format);

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        $platformData = ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'platform',
                DB::raw('COUNT(*) as total_tickets'),
                DB::raw('AVG(min_price) as avg_min_price'),
                DB::raw('AVG(max_price) as avg_max_price'),
                DB::raw('COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_tickets'),
                DB::raw('COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as high_demand_tickets'),
                DB::raw('COUNT(CASE WHEN status = "available" THEN 1 END) as active_tickets'),
                DB::raw('COUNT(CASE WHEN status = "sold_out" THEN 1 END) as sold_out_tickets'),
                DB::raw('COUNT(DISTINCT venue) as unique_venues'),
                DB::raw('COUNT(DISTINCT DATE(event_date)) as unique_event_dates'),
            ])
            ->whereNotNull('platform')
            ->groupBy('platform')
            ->orderBy('total_tickets', 'desc')
            ->get()
            ->map(function ($platform): array {
                $availabilityRate = $platform->total_tickets > 0
                    ? round(($platform->available_tickets / $platform->total_tickets) * 100, 2)
                    : 0;
                $demandRate = $platform->total_tickets > 0
                    ? round(($platform->high_demand_tickets / $platform->total_tickets) * 100, 2)
                    : 0;
                $successRate = $platform->total_tickets > 0
                    ? round(($platform->active_tickets / $platform->total_tickets) * 100, 2)
                    : 0;

                return [
                    'Platform'              => ucfirst(str_replace('_', ' ', $platform->platform)),
                    'Total Tickets'         => $platform->total_tickets,
                    'Available Tickets'     => $platform->available_tickets,
                    'High Demand Tickets'   => $platform->high_demand_tickets,
                    'Active Tickets'        => $platform->active_tickets,
                    'Sold Out Tickets'      => $platform->sold_out_tickets,
                    'Availability Rate (%)' => $availabilityRate,
                    'Demand Rate (%)'       => $demandRate,
                    'Success Rate (%)'      => $successRate,
                    'Avg Min Price ($)'     => number_format($platform->avg_min_price, 2),
                    'Avg Max Price ($)'     => number_format($platform->avg_max_price, 2),
                    'Unique Venues'         => $platform->unique_venues,
                    'Unique Event Dates'    => $platform->unique_event_dates,
                    'Performance Score'     => round(($availabilityRate * 0.3 + $demandRate * 0.3 + $successRate * 0.4), 2),
                ];
            });

        $metadata = [
            'title'  => 'Platform Performance Report',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
            'total_platforms' => $platformData->count(),
            'generated_at'    => now()->format('Y-m-d H:i:s'),
        ];

        return $this->generateExport($platformData, $metadata, 'platform-performance', $format);
    }

    /**
     * Export user engagement data
     */
    /**
     * ExportUserEngagement
     */
    public function exportUserEngagement(array $filters = [], string $format = 'xlsx'): array
    {
        $this->validateFormat($format);

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        $userData = User::select([
            'id',
            'name',
            'email',
            'role',
            'created_at',
            'last_activity_at',
            'email_verified_at',
        ])
            ->withCount([
                'ticketAlerts as total_alerts' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                },
                'ticketAlerts as active_alerts' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'active');
                },
                'ticketAlerts as triggered_alerts' => function ($query) use ($startDate, $endDate): void {
                    $query->whereBetween('created_at', [$startDate, $endDate])
                        ->where('status', 'triggered');
                },
            ])
            ->get()
            ->map(function ($user): array {
                $engagementScore = 0;
                if ($user->total_alerts > 0) {
                    $engagementScore = (($user->active_alerts / $user->total_alerts) * 50) +
                                     (($user->triggered_alerts / $user->total_alerts) * 50);
                }

                return [
                    'User ID'                  => $user->id,
                    'Name'                     => $user->name,
                    'Email'                    => $user->email,
                    'Role'                     => ucfirst((string) $user->role),
                    'Total Alerts'             => $user->total_alerts,
                    'Active Alerts'            => $user->active_alerts,
                    'Triggered Alerts'         => $user->triggered_alerts,
                    'Engagement Score'         => round($engagementScore, 2),
                    'Email Verified'           => $user->email_verified_at ? 'Yes' : 'No',
                    'Last Activity'            => $user->last_activity_at ? $user->last_activity_at->format('Y-m-d H:i:s') : 'Never',
                    'Account Created'          => $user->created_at->format('Y-m-d H:i:s'),
                    'Days Since Last Activity' => $user->last_activity_at ? $user->last_activity_at->diffInDays(now()) : 'Never',
                ];
            });

        $metadata = [
            'title'  => 'User Engagement Report',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
            'total_users'  => $userData->count(),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        return $this->generateExport($userData, $metadata, 'user-engagement', $format);
    }

    /**
     * Export comprehensive analytics data
     */
    /**
     * ExportComprehensiveAnalytics
     */
    public function exportComprehensiveAnalytics(array $filters = [], string $format = 'xlsx'): array
    {
        $this->validateFormat($format);

        $startDate = $filters['start_date'] ?? Carbon::now()->subDays(30);
        $endDate = $filters['end_date'] ?? Carbon::now();

        // Collect comprehensive analytics data
        $analytics = [
            'summary'          => $this->getAnalyticsSummary($startDate, $endDate),
            'ticket_trends'    => $this->getTicketTrendsAnalytics($startDate, $endDate),
            'price_insights'   => $this->getPriceInsightsAnalytics($startDate, $endDate),
            'platform_metrics' => $this->getPlatformMetricsAnalytics($startDate, $endDate),
            'user_behavior'    => $this->getUserBehaviorAnalytics($startDate, $endDate),
            'demand_patterns'  => $this->getDemandPatternsAnalytics($startDate, $endDate),
        ];

        $metadata = [
            'title'  => 'Comprehensive Analytics Report',
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date'   => $endDate->format('Y-m-d'),
            ],
            'sections'     => array_keys($analytics),
            'generated_at' => now()->format('Y-m-d H:i:s'),
        ];

        return $this->generateExport(collect($analytics), $metadata, 'comprehensive-analytics', $format);
    }

    /**
     * Generate export file in specified format
     */
    /**
     * GenerateExport
     */
    protected function generateExport(Collection $data, array $metadata, string $filename, string $format): array
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $fullFilename = "{$filename}_{$timestamp}";

        return match ($format) {
            'csv'   => $this->exportToCsv($data, $metadata, $fullFilename),
            'xlsx'  => $this->exportToExcel($data, $metadata, $fullFilename),
            'pdf'   => $this->exportToPdf($data, $metadata, $fullFilename),
            'json'  => $this->exportToJson($data, $metadata, $fullFilename),
            default => throw new InvalidArgumentException("Unsupported export format: {$format}"),
        };
    }

    /**
     * Export to CSV format
     */
    /**
     * ExportToCsv
     */
    protected function exportToCsv(Collection $data, array $metadata, string $filename): array
    {
        $headers = $data->isNotEmpty() ? array_keys($data->first()) : [];
        $csvData = collect([$headers])->merge($data->map(fn ($row) => array_values($row)));

        $path = "exports/csv/{$filename}.csv";
        $csvContent = $csvData->map(fn ($row): string => implode(',', array_map(fn ($cell): string => '"' . str_replace('"', '""', $cell) . '"', $row)))->implode("\n");

        // Add metadata as comments at the top
        $metadataComments = '# ' . $metadata['title'] . "\n";
        $metadataComments .= '# Generated: ' . $metadata['generated_at'] . "\n";
        $metadataComments .= '# Total Records: ' . $data->count() . "\n\n";

        Storage::put($path, $metadataComments . $csvContent);

        return [
            'success'      => TRUE,
            'format'       => 'csv',
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'metadata'     => $metadata,
        ];
    }

    /**
     * Export to Excel format
     */
    /**
     * ExportToExcel
     */
    protected function exportToExcel(Collection $data, array $metadata, string $filename): array
    {
        $path = "exports/excel/{$filename}.xlsx";
        $headers = $data->isNotEmpty() ? array_keys($data->first()) : [];

        Excel::store(new GenericArrayExport($data->toArray(), $headers), $path);

        return [
            'success'      => TRUE,
            'format'       => 'xlsx',
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'metadata'     => $metadata,
        ];
    }

    /**
     * Export to PDF format
     */
    /**
     * ExportToPdf
     */
    protected function exportToPdf(Collection $data, array $metadata, string $filename): array
    {
        $pdf = Pdf::loadView('exports.pdf.analytics_report', [
            'data'         => $data,
            'metadata'     => $metadata,
            'generated_at' => now()->format('F d, Y H:i:s'),
        ]);

        $path = "exports/pdf/{$filename}.pdf";
        Storage::put($path, $pdf->output());

        return [
            'success'      => TRUE,
            'format'       => 'pdf',
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'metadata'     => $metadata,
        ];
    }

    /**
     * Export to JSON format
     */
    /**
     * ExportToJson
     */
    protected function exportToJson(Collection $data, array $metadata, string $filename): array
    {
        $jsonData = [
            'metadata' => $metadata,
            'data'     => $data->toArray(),
        ];

        $path = "exports/json/{$filename}.json";
        Storage::put($path, json_encode($jsonData, JSON_PRETTY_PRINT));

        return [
            'success'      => TRUE,
            'format'       => 'json',
            'file_path'    => $path,
            'download_url' => Storage::url($path),
            'file_size'    => Storage::size($path),
            'metadata'     => $metadata,
        ];
    }

    /**
     * Validate export format
     */
    /**
     * ValidateFormat
     */
    protected function validateFormat(string $format): void
    {
        if (!in_array($format, $this->supportedFormats, TRUE)) {
            throw new InvalidArgumentException(
                "Unsupported format '{$format}'. Supported formats: " . implode(', ', $this->supportedFormats),
            );
        }
    }

    /**
     * Calculate price statistics
     */
    /**
     * CalculatePriceStatistics
     */
    protected function calculatePriceStatistics(Collection $priceData): array
    {
        if ($priceData->isEmpty()) {
            return [];
        }

        $prices = $priceData->pluck('Price ($)');

        return [
            'average_price' => number_format($prices->avg(), 2),
            'median_price'  => number_format($prices->median(), 2),
            'min_price'     => number_format($prices->min(), 2),
            'max_price'     => number_format($prices->max(), 2),
            'price_range'   => number_format($prices->max() - $prices->min(), 2),
            'total_records' => $priceData->count(),
        ];
    }

    // Analytics helper methods
    /**
     * Get  analytics summary
     */
    protected function getAnalyticsSummary(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_tickets'    => ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_users'     => User::where('last_activity_at', '>=', $startDate)->count(),
            'total_alerts'     => TicketAlert::whereBetween('created_at', [$startDate, $endDate])->count(),
            'unique_platforms' => ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])->distinct('platform')->count(),
            'average_price'    => TicketPriceHistory::whereBetween('recorded_at', [$startDate, $endDate])->avg('price') ?? 0,
        ];
    }

    /**
     * Get  ticket trends analytics
     */
    protected function getTicketTrendsAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get  price insights analytics
     */
    protected function getPriceInsightsAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        $priceData = TicketPriceHistory::whereBetween('recorded_at', [$startDate, $endDate])->get();

        return [
            'average_price'       => $priceData->avg('price'),
            'median_price'        => $priceData->median('price'),
            'price_volatility'    => $priceData->isNotEmpty() ? $priceData->pluck('price')->std() : 0,
            'total_price_records' => $priceData->count(),
        ];
    }

    /**
     * Get  platform metrics analytics
     */
    protected function getPlatformMetricsAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])
            ->select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->pluck('count', 'platform')
            ->toArray();
    }

    /**
     * Get  user behavior analytics
     */
    protected function getUserBehaviorAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'new_users'         => User::whereBetween('created_at', [$startDate, $endDate])->count(),
            'active_users'      => User::where('last_activity_at', '>=', $startDate)->count(),
            'users_with_alerts' => TicketAlert::whereBetween('created_at', [$startDate, $endDate])->distinct('user_id')->count(),
        ];
    }

    /**
     * Get  demand patterns analytics
     */
    protected function getDemandPatternsAnalytics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'high_demand_tickets' => ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])->where('is_high_demand', TRUE)->count(),
            'sold_out_tickets'    => ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])->where('status', 'sold_out')->count(),
            'available_tickets'   => ScrapedTicket::whereBetween('created_at', [$startDate, $endDate])->where('is_available', TRUE)->count(),
        ];
    }
}
