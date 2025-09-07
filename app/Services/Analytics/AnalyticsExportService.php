<?php declare(strict_types=1);

namespace App\Services\Analytics;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Analytics Export Service
 *
 * Handles exporting analytics data in various formats including
 * CSV, PDF, JSON, and Excel (XLSX) for reporting and analysis.
 */
class AnalyticsExportService
{
    private array $config;

    private array $supportedFormats;

    public function __construct()
    {
        $this->config = config('analytics.export', [
            'storage_disk'        => 'local',
            'export_path'         => 'analytics/exports',
            'retention_days'      => 30,
            'max_rows_per_export' => 50000,
            'chunk_size'          => 1000,
        ]);

        $this->supportedFormats = ['csv', 'pdf', 'json', 'xlsx'];
    }

    /**
     * Export analytics data in the specified format
     *
     * @param  string $format  Export format (csv, pdf, json, xlsx)
     * @param  array  $data    Analytics data to export
     * @param  array  $options Export options
     * @return array  Export result with file path and metadata
     */
    public function export(string $format, array $data, array $options = []): array
    {
        if (!in_array(strtolower($format), $this->supportedFormats)) {
            throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        $format = strtolower($format);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = $this->generateFilename($format, $timestamp, $options);

        try {
            $filePath = match ($format) {
                'csv'  => $this->exportToCsv($data, $filename, $options),
                'pdf'  => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                'xlsx' => $this->exportToXlsx($data, $filename, $options),
            };

            $this->cleanupOldExports();

            return [
                'success'       => TRUE,
                'file_path'     => $filePath,
                'filename'      => $filename,
                'format'        => $format,
                'size'          => Storage::disk($this->config['storage_disk'])->size($filePath),
                'records_count' => $this->countRecords($data),
                'generated_at'  => now()->toISOString(),
                'download_url'  => $this->generateDownloadUrl($filePath),
                'expires_at'    => now()->addDays($this->config['retention_days'])->toISOString(),
            ];
        } catch (\Exception $e) {
            Log::error('Analytics export failed', [
                'format'   => $format,
                'filename' => $filename,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return [
                'success'      => FALSE,
                'error'        => $e->getMessage(),
                'format'       => $format,
                'attempted_at' => now()->toISOString(),
            ];
        }
    }

    /**
     * Export data to CSV format
     *
     * @param  array  $data
     * @param  string $filename
     * @param  array  $options
     * @return string File path
     */
    private function exportToCsv(array $data, string $filename, array $options): string
    {
        $filePath = $this->config['export_path'] . '/' . $filename;
        $csvData = $this->prepareCsvData($data, $options);

        $content = '';
        $delimiter = $options['delimiter'] ?? ',';
        $enclosure = $options['enclosure'] ?? '"';

        // Add headers
        if (!empty($csvData) && isset($csvData[0])) {
            $headers = array_keys($csvData[0]);
            $content .= $this->formatCsvRow($headers, $delimiter, $enclosure) . "\n";
        }

        // Add data rows
        foreach ($csvData as $row) {
            $content .= $this->formatCsvRow(array_values($row), $delimiter, $enclosure) . "\n";
        }

        Storage::disk($this->config['storage_disk'])->put($filePath, $content);

        return $filePath;
    }

    /**
     * Export data to PDF format
     *
     * @param  array  $data
     * @param  string $filename
     * @param  array  $options
     * @return string File path
     */
    private function exportToPdf(array $data, string $filename, array $options): string
    {
        $filePath = $this->config['export_path'] . '/' . $filename;

        // Generate HTML content
        $html = $this->generatePdfHtml($data, $options);

        // Configure Dompdf
        $options_pdf = new Options();
        $options_pdf->set('defaultFont', 'Arial');
        $options_pdf->set('isRemoteEnabled', TRUE);

        $dompdf = new Dompdf($options_pdf);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($options['page_size'] ?? 'A4', $options['orientation'] ?? 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        Storage::disk($this->config['storage_disk'])->put($filePath, $pdfContent);

        return $filePath;
    }

    /**
     * Export data to JSON format
     *
     * @param  array  $data
     * @param  string $filename
     * @param  array  $options
     * @return string File path
     */
    private function exportToJson(array $data, string $filename, array $options): string
    {
        $filePath = $this->config['export_path'] . '/' . $filename;

        $exportData = [
            'metadata' => [
                'generated_at'   => now()->toISOString(),
                'format'         => 'json',
                'version'        => '1.0',
                'export_options' => $options,
            ],
            'data' => $data,
        ];

        $jsonFlags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;
        if ($options['compact'] ?? FALSE) {
            $jsonFlags = JSON_UNESCAPED_UNICODE;
        }

        $content = json_encode($exportData, $jsonFlags);

        Storage::disk($this->config['storage_disk'])->put($filePath, $content);

        return $filePath;
    }

    /**
     * Export data to Excel (XLSX) format
     *
     * @param  array  $data
     * @param  string $filename
     * @param  array  $options
     * @return string File path
     */
    private function exportToXlsx(array $data, string $filename, array $options): string
    {
        $filePath = $this->config['export_path'] . '/' . $filename;

        $spreadsheet = new Spreadsheet();
        $this->buildExcelWorkbook($spreadsheet, $data, $options);

        $writer = new Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'analytics_export_');

        $writer->save($tempFile);

        $content = file_get_contents($tempFile);
        unlink($tempFile);

        Storage::disk($this->config['storage_disk'])->put($filePath, $content);

        return $filePath;
    }

    /**
     * Build Excel workbook with multiple sheets
     *
     * @param Spreadsheet $spreadsheet
     * @param array       $data
     * @param array       $options
     */
    private function buildExcelWorkbook(Spreadsheet $spreadsheet, array $data, array $options): void
    {
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        // Overview Metrics Sheet
        if (isset($data['overview_metrics'])) {
            $this->addOverviewSheet($spreadsheet, $data['overview_metrics']);
        }

        // Platform Performance Sheet
        if (isset($data['platform_performance'])) {
            $this->addPlatformPerformanceSheet($spreadsheet, $data['platform_performance']);
        }

        // Pricing Trends Sheet
        if (isset($data['pricing_trends'])) {
            $this->addPricingTrendsSheet($spreadsheet, $data['pricing_trends']);
        }

        // Event Popularity Sheet
        if (isset($data['event_popularity'])) {
            $this->addEventPopularitySheet($spreadsheet, $data['event_popularity']);
        }

        // Anomalies Sheet
        if (isset($data['anomalies'])) {
            $this->addAnomaliesSheet($spreadsheet, $data['anomalies']);
        }

        // Ensure at least one sheet exists
        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('Data');
            $sheet->setCellValue('A1', 'No data available for export');
        }

        $spreadsheet->setActiveSheetIndex(0);
    }

    /**
     * Add overview metrics sheet to Excel workbook
     */
    private function addOverviewSheet(Spreadsheet $spreadsheet, array $overviewData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Overview');

        $row = 1;

        // Summary metrics
        $sheet->setCellValue("A{$row}", 'Overview Metrics');
        $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
        $row += 2;

        $sheet->setCellValue("A{$row}", 'Total Events:');
        $sheet->setCellValue("B{$row}", $overviewData['total_events'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Total Tickets:');
        $sheet->setCellValue("B{$row}", $overviewData['total_tickets'] ?? 0);
        $row++;

        $sheet->setCellValue("A{$row}", 'Average Ticket Price:');
        $sheet->setCellValue("B{$row}", '$' . number_format($overviewData['avg_ticket_price'] ?? 0, 2));
        $row++;

        // Growth metrics
        if (isset($overviewData['growth_metrics'])) {
            $row++;
            $sheet->setCellValue("A{$row}", 'Growth Metrics');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $row++;

            foreach ($overviewData['growth_metrics'] as $metric => $value) {
                $sheet->setCellValue("A{$row}", ucfirst(str_replace('_', ' ', $metric)) . ':');
                $sheet->setCellValue("B{$row}", $value . '%');
                $row++;
            }
        }

        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(TRUE);
        $sheet->getColumnDimension('B')->setAutoSize(TRUE);
    }

    /**
     * Add platform performance sheet to Excel workbook
     */
    private function addPlatformPerformanceSheet(Spreadsheet $spreadsheet, array $platformData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Platform Performance');

        $headers = ['Platform', 'Total Tickets', 'Unique Events', 'Avg Price', 'Price Range Min', 'Price Range Max', 'Market Share'];

        // Add headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(TRUE);
            $col++;
        }

        // Add data
        $row = 2;
        if (isset($platformData['platforms'])) {
            foreach ($platformData['platforms'] as $platform) {
                $sheet->setCellValue("A{$row}", $platform['platform']);
                $sheet->setCellValue("B{$row}", $platform['performance']['total_tickets'] ?? 0);
                $sheet->setCellValue("C{$row}", $platform['performance']['unique_events'] ?? 0);
                $sheet->setCellValue("D{$row}", '$' . number_format($platform['performance']['avg_price'] ?? 0, 2));
                $sheet->setCellValue("E{$row}", '$' . number_format($platform['performance']['price_range']['min'] ?? 0, 2));
                $sheet->setCellValue("F{$row}", '$' . number_format($platform['performance']['price_range']['max'] ?? 0, 2));

                // Find market share for this platform
                $marketShare = 0;
                if (isset($platformData['market_share'])) {
                    foreach ($platformData['market_share'] as $share) {
                        if ($share['platform'] === $platform['platform']) {
                            $marketShare = $share['market_share'];

                            break;
                        }
                    }
                }
                $sheet->setCellValue("G{$row}", $marketShare . '%');

                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(TRUE);
        }
    }

    /**
     * Add pricing trends sheet to Excel workbook
     */
    private function addPricingTrendsSheet(Spreadsheet $spreadsheet, array $pricingData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Pricing Trends');

        $row = 1;

        // Sport-based pricing analysis
        if (isset($pricingData['sport_analysis']) && !empty($pricingData['sport_analysis'])) {
            $sheet->setCellValue("A{$row}", 'Sport Category Pricing Analysis');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $row += 2;

            $headers = ['Category', 'Avg Price', 'Ticket Count', 'Min Price', 'Max Price'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(TRUE);
                $col++;
            }
            $row++;

            foreach ($pricingData['sport_analysis'] as $sport) {
                $sheet->setCellValue("A{$row}", $sport['category']);
                $sheet->setCellValue("B{$row}", '$' . number_format($sport['avg_price'] ?? 0, 2));
                $sheet->setCellValue("C{$row}", $sport['ticket_count'] ?? 0);
                $sheet->setCellValue("D{$row}", '$' . number_format($sport['min_price'] ?? 0, 2));
                $sheet->setCellValue("E{$row}", '$' . number_format($sport['max_price'] ?? 0, 2));
                $row++;
            }
        }

        // Price distribution
        if (isset($pricingData['price_distribution']) && !empty($pricingData['price_distribution'])) {
            $row += 2;
            $sheet->setCellValue("A{$row}", 'Price Distribution');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $row += 2;

            $sheet->setCellValue("A{$row}", 'Price Range');
            $sheet->setCellValue("B{$row}", 'Ticket Count');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $sheet->getStyle("B{$row}")->getFont()->setBold(TRUE);
            $row++;

            foreach ($pricingData['price_distribution'] as $range => $count) {
                $sheet->setCellValue("A{$row}", '$' . $range);
                $sheet->setCellValue("B{$row}", $count);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(TRUE);
        }
    }

    /**
     * Add event popularity sheet to Excel workbook
     */
    private function addEventPopularitySheet(Spreadsheet $spreadsheet, array $eventData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Event Popularity');

        $row = 1;

        // Trending events
        if (isset($eventData['trending_events']) && !empty($eventData['trending_events'])) {
            $sheet->setCellValue("A{$row}", 'Trending Events');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $row += 2;

            $headers = ['Event Name', 'Category', 'Venue', 'Event Date', 'Ticket Count', 'Avg Price'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(TRUE);
                $col++;
            }
            $row++;

            foreach ($eventData['trending_events'] as $event) {
                $sheet->setCellValue("A{$row}", $event['name']);
                $sheet->setCellValue("B{$row}", $event['category']);
                $sheet->setCellValue("C{$row}", $event['venue']);
                $sheet->setCellValue("D{$row}", $event['event_date']);
                $sheet->setCellValue("E{$row}", $event['ticket_count']);
                $sheet->setCellValue("F{$row}", '$' . number_format($event['avg_price'] ?? 0, 2));
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(TRUE);
        }
    }

    /**
     * Add anomalies sheet to Excel workbook
     */
    private function addAnomaliesSheet(Spreadsheet $spreadsheet, array $anomaliesData): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Anomalies');

        $row = 1;

        // Price anomalies
        if (isset($anomaliesData['price_anomalies']['anomalies']) && !empty($anomaliesData['price_anomalies']['anomalies'])) {
            $sheet->setCellValue("A{$row}", 'Price Anomalies');
            $sheet->getStyle("A{$row}")->getFont()->setBold(TRUE);
            $row += 2;

            $headers = ['Type', 'Price', 'Platform', 'Z-Score', 'Severity', 'Detected At'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $sheet->getStyle($col . $row)->getFont()->setBold(TRUE);
                $col++;
            }
            $row++;

            foreach ($anomaliesData['price_anomalies']['anomalies'] as $anomaly) {
                $sheet->setCellValue("A{$row}", $anomaly['type']);
                $sheet->setCellValue("B{$row}", '$' . number_format($anomaly['price'] ?? 0, 2));
                $sheet->setCellValue("C{$row}", $anomaly['platform']);
                $sheet->setCellValue("D{$row}", number_format($anomaly['z_score'] ?? 0, 2));
                $sheet->setCellValue("E{$row}", ucfirst($anomaly['severity']));
                $sheet->setCellValue("F{$row}", $anomaly['detected_at']);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(TRUE);
        }
    }

    /**
     * Prepare data for CSV export
     */
    private function prepareCsvData(array $data, array $options): array
    {
        $flatData = [];

        // Flatten nested data structure for CSV
        if (isset($data['overview_metrics'])) {
            $overview = $data['overview_metrics'];
            $flatData[] = [
                'metric'   => 'total_events',
                'value'    => $overview['total_events'] ?? 0,
                'category' => 'overview',
            ];
            $flatData[] = [
                'metric'   => 'total_tickets',
                'value'    => $overview['total_tickets'] ?? 0,
                'category' => 'overview',
            ];
            $flatData[] = [
                'metric'   => 'avg_ticket_price',
                'value'    => $overview['avg_ticket_price'] ?? 0,
                'category' => 'overview',
            ];
        }

        // Add platform performance data
        if (isset($data['platform_performance']['platforms'])) {
            foreach ($data['platform_performance']['platforms'] as $platform) {
                $flatData[] = [
                    'metric'        => 'platform_performance',
                    'platform'      => $platform['platform'],
                    'total_tickets' => $platform['performance']['total_tickets'] ?? 0,
                    'unique_events' => $platform['performance']['unique_events'] ?? 0,
                    'avg_price'     => $platform['performance']['avg_price'] ?? 0,
                    'category'      => 'platform',
                ];
            }
        }

        return array_slice($flatData, 0, $this->config['max_rows_per_export']);
    }

    /**
     * Generate HTML content for PDF export
     */
    private function generatePdfHtml(array $data, array $options): string
    {
        $title = $options['title'] ?? 'HD Tickets Analytics Report';
        $generatedAt = now()->format('F j, Y \a\t g:i A');

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>{$title}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
                h2 { color: #34495e; margin-top: 30px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f4f4f4; font-weight: bold; }
                .metric { margin: 10px 0; }
                .header { text-align: center; margin-bottom: 30px; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>{$title}</h1>
                <p>Generated on {$generatedAt}</p>
            </div>
        ";

        // Overview Metrics
        if (isset($data['overview_metrics'])) {
            $overview = $data['overview_metrics'];
            $html .= "
                <h2>Overview Metrics</h2>
                <div class='metric'>Total Events: " . number_format($overview['total_events'] ?? 0) . "</div>
                <div class='metric'>Total Tickets: " . number_format($overview['total_tickets'] ?? 0) . "</div>
                <div class='metric'>Average Ticket Price: $" . number_format($overview['avg_ticket_price'] ?? 0, 2) . '</div>
            ';

            if (isset($overview['growth_metrics'])) {
                $html .= '<h3>Growth Metrics</h3>';
                foreach ($overview['growth_metrics'] as $metric => $value) {
                    $html .= "<div class='metric'>" . ucfirst(str_replace('_', ' ', $metric)) . ": {$value}%</div>";
                }
            }
        }

        // Platform Performance Table
        if (isset($data['platform_performance']['platforms'])) {
            $html .= '
                <h2>Platform Performance</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Total Tickets</th>
                            <th>Unique Events</th>
                            <th>Avg Price</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            foreach ($data['platform_performance']['platforms'] as $platform) {
                $html .= '
                    <tr>
                        <td>' . ($platform['platform']) . '</td>
                        <td>' . number_format($platform['performance']['total_tickets'] ?? 0) . '</td>
                        <td>' . number_format($platform['performance']['unique_events'] ?? 0) . '</td>
                        <td>$' . number_format($platform['performance']['avg_price'] ?? 0, 2) . '</td>
                    </tr>
                ';
            }

            $html .= '
                    </tbody>
                </table>
            ';
        }

        $html .= "
            <div class='footer'>
                <p>HD Tickets Analytics - Sports Event Ticket Monitoring System</p>
            </div>
        </body>
        </html>
        ";

        return $html;
    }

    /**
     * Generate filename for export
     */
    private function generateFilename(string $format, string $timestamp, array $options): string
    {
        $prefix = $options['filename_prefix'] ?? 'analytics_export';
        $suffix = $options['filename_suffix'] ?? '';

        return $prefix . '_' . $timestamp . $suffix . '.' . $format;
    }

    /**
     * Format CSV row
     */
    private function formatCsvRow(array $fields, string $delimiter, string $enclosure): string
    {
        $formatted = array_map(function ($field) use ($enclosure) {
            // Escape enclosure characters
            $field = str_replace($enclosure, $enclosure . $enclosure, $field);
            // Wrap in enclosures if contains delimiter, enclosure, or newline
            if (strpos($field, $enclosure) !== FALSE ||
                strpos($field, "\n") !== FALSE ||
                strpos($field, "\r") !== FALSE) {
                return $enclosure . $field . $enclosure;
            }

            return $field;
        }, $fields);

        return implode($delimiter, $formatted);
    }

    /**
     * Count total records in data
     */
    private function countRecords(array $data): int
    {
        $count = 0;

        foreach ($data as $section => $sectionData) {
            if (is_array($sectionData)) {
                $count += count($sectionData);
            }
        }

        return $count;
    }

    /**
     * Generate download URL for exported file
     */
    private function generateDownloadUrl(string $filePath): string
    {
        // In a real implementation, this would generate a signed URL or temporary download link
        return route('analytics.download', ['file' => basename($filePath)]);
    }

    /**
     * Export data specifically for API consumption
     * Used by Business Intelligence API endpoints
     */
    public function exportForApi(string $dataset, string $format, array $params): array
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $exportId = uniqid('api_export_');
        $filename = "api_export_{$dataset}_{$format}_{$timestamp}.{$format}";
        $filePath = storage_path("app/analytics/exports/api/{$filename}");

        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, TRUE);
        }

        // Generate data based on dataset type
        $data = $this->generateApiDataset($dataset, $params);

        // Export in requested format
        $recordCount = 0;
        switch ($format) {
            case 'json':
                file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
                $recordCount = is_array($data) ? count($data) : 1;

                break;
            case 'csv':
                $recordCount = $this->exportApiToCsv($data, $filePath);

                break;
            case 'parquet':
                // For parquet, we'll export as JSON for now (requires additional library for true parquet)
                file_put_contents($filePath, json_encode($data));
                $recordCount = is_array($data) ? count($data) : 1;

                break;
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }

        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        return [
            'export_id'    => $exportId,
            'download_url' => $this->generateApiDownloadUrl($filename),
            'file_size'    => $fileSize,
            'record_count' => $recordCount,
            'filename'     => $filename,
        ];
    }

    /**
     * Generate dataset for API export
     */
    private function generateApiDataset(string $dataset, array $params): array
    {
        $dateFrom = $params['date_from'] ?? Carbon::now()->subDays(30)->toDateString();
        $dateTo = $params['date_to'] ?? Carbon::now()->toDateString();

        switch ($dataset) {
            case 'tickets':
                return $this->generateTicketsDataset($dateFrom, $dateTo, $params);
            case 'platforms':
                return $this->generatePlatformsDataset($dateFrom, $dateTo, $params);
            case 'analytics':
                return $this->generateAnalyticsDataset($dateFrom, $dateTo, $params);
            case 'competitive':
                return $this->generateCompetitiveDataset($dateFrom, $dateTo, $params);
            default:
                throw new \InvalidArgumentException("Unsupported dataset: {$dataset}");
        }
    }

    /**
     * Generate tickets dataset for API
     */
    private function generateTicketsDataset(string $dateFrom, string $dateTo, array $params): array
    {
        $query = \App\Models\ScrapedTicket::query()
            ->with('source')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if (!empty($params['sport'])) {
            $query->where('sport', $params['sport']);
        }

        $fields = $params['fields'] ?? [
            'id', 'event_name', 'sport', 'price', 'venue', 'event_date', 'source_name', 'created_at',
        ];

        return $query->get()->map(function ($ticket) use ($fields) {
            $data = [];
            foreach ($fields as $field) {
                switch ($field) {
                    case 'source_name':
                        $data[$field] = $ticket->source->name ?? NULL;

                        break;
                    default:
                        $data[$field] = $ticket->{$field} ?? NULL;
                }
            }

            return $data;
        })->toArray();
    }

    /**
     * Generate platforms dataset for API
     */
    private function generatePlatformsDataset(string $dateFrom, string $dateTo, array $params): array
    {
        return \App\Models\TicketSource::with(['scrapedTickets' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
        }])->get()->map(function ($platform) {
            return [
                'platform_id'    => $platform->id,
                'platform_name'  => $platform->name,
                'platform_url'   => $platform->url,
                'is_active'      => $platform->is_active,
                'total_tickets'  => $platform->scrapedTickets->count(),
                'avg_price'      => round($platform->scrapedTickets->avg('price') ?: 0, 2),
                'min_price'      => round($platform->scrapedTickets->min('price') ?: 0, 2),
                'max_price'      => round($platform->scrapedTickets->max('price') ?: 0, 2),
                'sports_covered' => $platform->scrapedTickets->pluck('sport')->unique()->count(),
                'last_update'    => $platform->scrapedTickets->max('created_at'),
            ];
        })->toArray();
    }

    /**
     * Generate analytics dataset for API
     */
    private function generateAnalyticsDataset(string $dateFrom, string $dateTo, array $params): array
    {
        $analyticsService = app(\App\Services\AdvancedAnalyticsService::class);

        return $analyticsService->getDashboardData([
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'sport'     => $params['sport'] ?? NULL,
        ]);
    }

    /**
     * Generate competitive dataset for API
     */
    private function generateCompetitiveDataset(string $dateFrom, string $dateTo, array $params): array
    {
        $competitiveService = app(\App\Services\CompetitiveIntelligenceService::class);

        return $competitiveService->getCompetitiveDashboard([
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'sport'     => $params['sport'] ?? NULL,
        ]);
    }

    /**
     * Export API data to CSV format
     */
    private function exportApiToCsv(array $data, string $filePath): int
    {
        if (empty($data)) {
            file_put_contents($filePath, '');

            return 0;
        }

        $handle = fopen($filePath, 'w');

        // Write headers (use keys from first row)
        $headers = array_keys($data[0]);
        fputcsv($handle, $headers);

        // Write data rows
        $recordCount = 0;
        foreach ($data as $row) {
            fputcsv($handle, array_values($row));
            $recordCount++;
        }

        fclose($handle);

        return $recordCount;
    }

    /**
     * Generate API download URL for export file
     */
    private function generateApiDownloadUrl(string $filename): string
    {
        return url('/api/v1/bi/download/' . basename($filename));
    }

    /**
     * Clean up old export files
     */
    private function cleanupOldExports(): void
    {
        $disk = Storage::disk($this->config['storage_disk']);
        $exportPath = $this->config['export_path'];
        $retentionDays = $this->config['retention_days'];

        $files = $disk->files($exportPath);
        $cutoffDate = now()->subDays($retentionDays);

        foreach ($files as $file) {
            try {
                $lastModified = Carbon::createFromTimestamp($disk->lastModified($file));
                if ($lastModified->lt($cutoffDate)) {
                    $disk->delete($file);
                    Log::info('Deleted old analytics export file', ['file' => $file]);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to delete old export file', [
                    'file'  => $file,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
