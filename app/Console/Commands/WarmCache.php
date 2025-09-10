<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PerformanceOptimizationService;
use Illuminate\Console\Command;

class WarmCache extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'cache:warm';

    /** The console command description. */
    protected $description = 'Warm application cache for improved performance';

    /**
     * Create a new command instance.
     */
    public function __construct(private PerformanceOptimizationService $performanceService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('Starting cache warming...');

        $results = $this->performanceService->warmCache();

        if (isset($results['error'])) {
            $this->error('Cache warming encountered issues: ' . $results['error']);

            return Command::FAILURE;
        }
        $this->info('Cache warming completed successfully.');
        $this->info('Details: ' . json_encode($results));

        return Command::SUCCESS;
    }
}
