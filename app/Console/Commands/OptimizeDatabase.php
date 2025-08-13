<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\PerformanceOptimizationService;
use Illuminate\Console\Command;

class OptimizeDatabase extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'db:optimize';

    /** The console command description. */
    protected $description = 'Optimize database performance by analyzing tables and cleaning up old data';

    private PerformanceOptimizationService $performanceService;

    /**
     * Create a new command instance.
     */
    public function __construct(PerformanceOptimizationService $performanceService)
    {
        parent::__construct();
        $this->performanceService = $performanceService;
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('Starting database optimization...');

        $results = $this->performanceService->optimizeDatabase();

        if (isset($results['error'])) {
            $this->error('Database optimization encountered issues: ' . $results['error']);

            return Command::FAILURE;
        }

        $this->info('Database optimization completed successfully.');

        // Display results
        if (isset($results['analyzed'])) {
            $this->info('Tables analyzed: ' . implode(', ', $results['analyzed']));
        }

        if (isset($results['cleanup'])) {
            $cleanup = $results['cleanup'];
            $this->info('Cleanup results:');
            $this->info('- Old tickets removed: ' . ($cleanup['old_tickets_removed'] ?? 0));
            $this->info('- Expired attempts removed: ' . ($cleanup['expired_attempts_removed'] ?? 0));
            $this->info('- Old logs removed: ' . ($cleanup['old_logs_removed'] ?? 0));
        }

        if (isset($results['optimization']['indexes_created'])) {
            $this->info('New indexes created: ' . implode(', ', $results['optimization']['indexes_created']));
        }

        return Command::SUCCESS;
    }
}
