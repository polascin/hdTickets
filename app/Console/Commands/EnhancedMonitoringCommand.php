<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\EnhancedEventMonitoringService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced Event Monitoring Command
 *
 * Runs sub-second monitoring for all active events
 * Usage: php artisan monitor:enhanced
 */
class EnhancedMonitoringCommand extends Command
{
    protected $signature = 'monitor:enhanced 
                          {--interval=0.5 : Monitoring interval in seconds}
                          {--max-duration=3600 : Maximum duration in seconds}
                          {--verbose : Enable verbose output}';

    protected $description = 'Run enhanced event monitoring with sub-second updates';

    public function __construct(
        private EnhancedEventMonitoringService $monitoringService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $interval = (float) $this->option('interval');
        $maxDuration = (int) $this->option('max-duration');
        $verbose = $this->option('verbose');

        $this->info('🚀 Starting Enhanced Event Monitoring');
        $this->info("⏱️  Interval: {$interval} seconds");
        $this->info("⏰ Max Duration: {$maxDuration} seconds");

        if ($verbose) {
            $this->info('📊 Verbose mode enabled');
        }

        $startTime = time();
        $cycles = 0;
        $totalResponseTime = 0;

        Log::info('Enhanced monitoring started', [
            'interval'     => $interval,
            'max_duration' => $maxDuration,
            'start_time'   => now(),
        ]);

        while ((time() - $startTime) < $maxDuration) {
            $cycleStart = microtime(TRUE);

            try {
                $this->monitoringService->startSubSecondMonitoring();
                $cycles++;

                $cycleTime = (microtime(TRUE) - $cycleStart) * 1000; // Convert to ms
                $totalResponseTime += $cycleTime;

                if ($verbose) {
                    $this->line(sprintf(
                        '✅ Cycle %d completed in %.2fms | Avg: %.2fms',
                        $cycles,
                        $cycleTime,
                        $totalResponseTime / $cycles
                    ));
                }

                // Update progress bar every 10 cycles
                if ($cycles % 10 === 0) {
                    $elapsed = time() - $startTime;
                    $progress = ($elapsed / $maxDuration) * 100;
                    $this->info(sprintf(
                        '📈 Progress: %.1f%% | Cycles: %d | Elapsed: %ds | Avg Response: %.2fms',
                        $progress,
                        $cycles,
                        $elapsed,
                        $totalResponseTime / $cycles
                    ));
                }
            } catch (\Exception $e) {
                $this->error('❌ Monitoring cycle failed: ' . $e->getMessage());
                Log::error('Enhanced monitoring cycle failed', [
                    'error' => $e->getMessage(),
                    'cycle' => $cycles,
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // Sleep for the specified interval
            if ($interval > 0) {
                usleep((int) ($interval * 1000000)); // Convert to microseconds
            }
        }

        $totalTime = time() - $startTime;
        $avgResponseTime = $cycles > 0 ? $totalResponseTime / $cycles : 0;

        $this->info('🏁 Enhanced monitoring completed!');
        $this->info('📊 Final Statistics:');
        $this->info("   • Total Cycles: {$cycles}");
        $this->info("   • Total Time: {$totalTime}s");
        $this->info('   • Average Response Time: ' . round($avgResponseTime, 2) . 'ms');
        $this->info('   • Cycles per Second: ' . round($cycles / $totalTime, 2));

        Log::info('Enhanced monitoring completed', [
            'total_cycles'          => $cycles,
            'total_time'            => $totalTime,
            'average_response_time' => $avgResponseTime,
            'cycles_per_second'     => $cycles / $totalTime,
            'end_time'              => now(),
        ]);

        return self::SUCCESS;
    }
}
