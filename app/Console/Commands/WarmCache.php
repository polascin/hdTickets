<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PerformanceOptimizationService;

class WarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm application cache for improved performance';

    private $performanceService;

    /**
     * Create a new command instance.
     *
     * @param PerformanceOptimizationService $performanceService
     */
    public function __construct(PerformanceOptimizationService $performanceService)
    {
        parent::__construct();
        $this->performanceService = $performanceService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cache warming...');

        $results = $this->performanceService->warmCache();

        if (isset($results['error'])) {
            $this->error('Cache warming encountered issues: ' . $results['error']);
        } else {
            $this->info('Cache warming completed successfully.');
            $this->info('Details: ' . json_encode($results));
        }
    }
}
