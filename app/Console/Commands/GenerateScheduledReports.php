<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Analytics\AutomatedReportingService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function count;

class GenerateScheduledReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hdtickets:generate-reports 
                            {type=all : Type of reports to generate (daily, weekly, monthly, all)}
                            {--force : Force generation even if reports are not due}
                            {--dry-run : Show what would be generated without actually generating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and distribute scheduled analytics reports for HD Tickets';

    /**
     * Create a new command instance.
     */
    public function __construct(private AutomatedReportingService $reportingService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $this->info('🎯 HD Tickets Scheduled Reports Generator');
        $this->info('=====================================');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No reports will be generated');
        }

        $startTime = microtime(TRUE);
        $allResults = [];
        $reportTypes = $this->getReportTypes($type);

        foreach ($reportTypes as $reportType) {
            $this->info("\n📊 Processing {$reportType} reports...");

            if ($dryRun) {
                $this->showDryRunInfo($reportType);

                continue;
            }

            try {
                $results = $this->reportingService->generateScheduledReports(
                    $reportType,
                    ['force' => $force],
                );

                $allResults = array_merge($allResults, $results);
                $this->displayResults($reportType, $results);
            } catch (Exception $e) {
                $this->error("❌ Failed to generate {$reportType} reports: {$e->getMessage()}");
                Log::error('Scheduled report generation command failed', [
                    'type'  => $reportType,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->displaySummary($allResults, microtime(TRUE) - $startTime);

        return Command::SUCCESS;
    }

    /**
     * Get report types to process
     */
    private function getReportTypes(string $type): array
    {
        if ($type === 'all') {
            return ['daily', 'weekly', 'monthly'];
        }

        return [$type];
    }

    /**
     * Show what would be generated in dry run mode
     */
    private function showDryRunInfo(string $type): void
    {
        // This would show what reports would be generated
        $this->line("  • Would check for active {$type} reports");
        $this->line('  • Would generate report files');
        $this->line('  • Would send emails to recipients');
        $this->line('  • Would update report statistics');
    }

    /**
     * Display results for a specific report type
     */
    private function displayResults(string $type, array $results): void
    {
        if ($results === []) {
            $this->comment("  ℹ️  No {$type} reports were due for generation");

            return;
        }

        $successful = array_filter($results, fn (array $r) => $r['success']);
        $failed = array_filter($results, fn (array $r): bool => !$r['success']);

        $this->info('  ✅ Successfully generated: ' . count($successful));

        if ($failed !== []) {
            $this->error('  ❌ Failed to generate: ' . count($failed));
        }

        // Show details for each report
        foreach ($results as $result) {
            if ($result['success']) {
                $size = $this->formatBytes($result['size'] ?? 0);
                $time = round($result['generation_time'] ?? 0, 2);
                $this->line("    • {$result['filename']} ({$size}, {$time}s)");

                // Show delivery status
                if (isset($result['delivery'])) {
                    $deliverySuccess = count(array_filter($result['delivery'], fn (array $d) => $d['success']));
                    $deliveryTotal = count($result['delivery']);
                    $this->line("      📧 Delivered to {$deliverySuccess}/{$deliveryTotal} recipients");
                }
            } else {
                $this->error("    • Report {$result['report_id']} failed: {$result['error']}");
            }
        }
    }

    /**
     * Display overall summary
     */
    private function displaySummary(array $allResults, float $totalTime): void
    {
        $this->info("\n📈 SUMMARY");
        $this->info('===========');

        $totalReports = count($allResults);
        $successful = count(array_filter($allResults, fn (array $r) => $r['success']));
        $failed = $totalReports - $successful;

        $this->info("Total reports processed: {$totalReports}");
        $this->info("✅ Successful: {$successful}");

        if ($failed > 0) {
            $this->error("❌ Failed: {$failed}");
        }

        $this->info('⏱️  Total execution time: ' . round($totalTime, 2) . 's');

        // Calculate total file sizes
        $totalSize = array_sum(array_column($allResults, 'size'));
        if ($totalSize > 0) {
            $this->info('💾 Total file size: ' . $this->formatBytes($totalSize));
        }

        if ($successful > 0) {
            $this->info("\n🎉 Report generation completed successfully!");
        } else {
            $this->warn("\n⚠️  No reports were generated");
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        if ($size === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($size !== 0 ? log($size) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $size /= (1 << (10 * $pow));

        return round($size, $precision) . ' ' . $units[$pow];
    }
}
