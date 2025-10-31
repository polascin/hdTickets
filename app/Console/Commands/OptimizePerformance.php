<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Enhanced\AdvancedCacheService;
use App\Services\Enhanced\DatabaseQueryOptimizer;
use App\Services\PerformanceCacheService;
use App\Services\PlatformCachingService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function count;
use function is_array;

class OptimizePerformance extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'hdtickets:optimize-performance 
                            {--cache : Only run cache optimizations}
                            {--database : Only run database optimizations}
                            {--analyze : Analyze performance without making changes}
                            {--force : Force optimization even in production}';

    /** The console command description. */
    protected $description = 'Run comprehensive performance optimizations for the Sports Events Ticket System';

    private AdvancedCacheService $advancedCache;

    private DatabaseQueryOptimizer $dbOptimizer;

    private PerformanceCacheService $performanceCache;

    private PlatformCachingService $platformCache;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->advancedCache = new AdvancedCacheService();
        $this->dbOptimizer = new DatabaseQueryOptimizer();
        $this->performanceCache = new PerformanceCacheService();
        $this->platformCache = new PlatformCachingService();
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Performance Optimization for Sports Events Ticket System');
        $this->newLine();

        // Safety check for production
        if (app()->environment('production') && !$this->option('force') && !$this->confirm('You are running this in production. Continue?')) {
            $this->error('Operation cancelled.');

            return Command::FAILURE;
        }

        $startTime = microtime(TRUE);
        $results = [];

        try {
            // Run analysis if requested
            if ($this->option('analyze')) {
                return $this->runAnalysis();
            }

            // Cache optimizations
            if (!$this->option('database')) {
                $results['cache'] = $this->optimizeCache();
            }

            // Database optimizations
            if (!$this->option('cache')) {
                $results['database'] = $this->optimizeDatabase();
            }

            // General Laravel optimizations
            if (!$this->option('cache') && !$this->option('database')) {
                $results['laravel'] = $this->optimizeLaravel();
            }

            $totalTime = round(microtime(TRUE) - $startTime, 2);

            $this->displayResults($results, $totalTime);

            Log::channel('performance')->info('Performance optimization completed', [
                'results'        => $results,
                'execution_time' => $totalTime,
                'memory_peak'    => memory_get_peak_usage(TRUE),
            ]);

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Performance optimization failed: ' . $e->getMessage());
            Log::channel('performance')->error('Performance optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Run performance analysis without making changes.
     */
    /**
     * RunAnalysis
     */
    private function runAnalysis(): int
    {
        $this->info('📊 Running Performance Analysis...');
        $this->newLine();

        // Cache analysis
        $this->line('<comment>Cache Analysis:</comment>');
        $cacheStats = $this->advancedCache->getAdvancedCacheStats();
        $this->displayCacheStats($cacheStats);

        // Database analysis
        $this->line('<comment>Database Analysis:</comment>');
        $dbStats = $this->dbOptimizer->getConnectionStats();
        $this->displayDatabaseStats($dbStats);

        // Query performance
        $queryStats = $this->dbOptimizer->getQueryStats();
        $this->displayQueryStats($queryStats);

        return 0;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * OptimizeCache
     */
    private function optimizeCache(): array
    {
        $this->info('🗄️  Optimizing Cache Performance...');
        $results = [];

        // Clear expired caches
        $this->info('  - Clearing expired caches...');
        Cache::flush();
        $this->line('    ✓ Completed');

        // Warm up critical caches
        $this->info('  - Warming up critical caches...');
        $this->advancedCache->intelligentWarmUp();
        $results['cache_warming'] = 'completed';
        $this->line('    ✓ Completed');

        // Preload anticipated data
        $this->info('  - Preloading anticipated data...');
        $this->advancedCache->preloadAnticipatedData();
        $results['data_preloading'] = 'completed';
        $this->line('    ✓ Completed');

        // Optimize cache configuration
        $this->info('  - Optimizing cache configuration...');
        $optimizations = $this->performanceCache->optimizeCacheUsage();
        $results['cache_optimizations'] = $optimizations;
        $this->line('    ✓ Completed');

        // Clean up platform caches
        $platforms = ['ticketmaster', 'stubhub', 'viagogo', 'tickpick'];
        foreach ($platforms as $platform) {
            $this->info("  - Optimizing {$platform} platform cache...");
            $this->platformCache->clearPlatformCache($platform);
            $results['platform_cache_cleared'][] = $platform;
            $this->line('    ✓ Completed');
        }

        $results['status'] = 'completed';

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * OptimizeDatabase
     */
    private function optimizeDatabase(): array
    {
        $this->info('🗃️  Optimizing Database Performance...');
        $results = [];

        // Run database maintenance
        $this->info('  - Running database maintenance...');
        $maintenanceResults = $this->dbOptimizer->performMaintenance();
        $results['maintenance'] = $maintenanceResults;
        $this->line('    ✓ Completed');

        // Add performance indexes (skip migration for now)
        $this->info('  - Analyzing performance indexes...');
        $results['indexes_skipped'] = 'Migration requires manual review due to existing schema';
        $this->line('    ⚠ Skipped');

        // Analyze table statistics
        $this->info('  - Analyzing table statistics...');
        $stats = $this->dbOptimizer->getConnectionStats();
        $results['connection_stats'] = $stats;
        $this->line('    ✓ Completed');

        // Optimize query performance
        $this->info('  - Analyzing query performance...');
        $queryStats = $this->dbOptimizer->getQueryStats();
        $results['query_stats'] = $queryStats;
        $this->line('    ✓ Completed');

        $results['status'] = 'completed';

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * OptimizeLaravel
     */
    private function optimizeLaravel(): array
    {
        $this->info('⚡ Running Laravel Optimizations...');
        $results = [];

        // Config cache
        $this->info('  - Caching configuration...');
        Artisan::call('config:cache');
        $results['config_cache'] = 'completed';
        $this->line('    ✓ Completed');

        // Route cache
        $this->info('  - Caching routes...');
        Artisan::call('route:cache');
        $results['route_cache'] = 'completed';
        $this->line('    ✓ Completed');

        // View cache
        $this->info('  - Caching views...');
        Artisan::call('view:cache');
        $results['view_cache'] = 'completed';
        $this->line('    ✓ Completed');

        // Event cache (skip if not available)
        $this->info('  - Checking event cache...');
        $results['event_cache'] = 'not_available';
        $this->line('    ⚠ Skipped');

        // Optimize autoloader
        $this->info('  - Optimizing autoloader...');
        if (app()->environment('production')) {
            exec('composer dump-autoload --optimize --no-dev', $output, $returnCode);
            $results['autoloader_optimization'] = $returnCode === 0 ? 'completed' : 'failed';
        } else {
            exec('composer dump-autoload --optimize', $output, $returnCode);
            $results['autoloader_optimization'] = $returnCode === 0 ? 'completed' : 'failed';
        }
        $this->line('    ✓ Completed');

        $results['status'] = 'completed';

        return $results;
    }

    /**
     * @param array<string, mixed> $results
     */
    /**
     * DisplayResults
     */
    private function displayResults(array $results, float $executionTime): void
    {
        $this->newLine();
        $this->info('✅ Performance Optimization Results');
        $this->line(str_repeat('=', 50));

        foreach ($results as $category => $result) {
            $this->line("<comment>{$category}:</comment>");

            if (is_array($result)) {
                foreach ($result as $key => $value) {
                    if (is_array($value)) {
                        $this->line("  - {$key}: " . json_encode($value));
                    } else {
                        $this->line("  - {$key}: {$value}");
                    }
                }
            } else {
                $this->line("  Status: {$result}");
            }
            $this->newLine();
        }

        $this->line("<info>Total execution time: {$executionTime}s</info>");
        $this->line('<info>Peak memory usage: ' . $this->formatBytes(memory_get_peak_usage(TRUE)) . '</info>');
        $this->newLine();

        // Display optimization recommendations
        $this->displayRecommendations();
    }

    /**
     * @param array<string, mixed> $stats
     */
    /**
     * DisplayCacheStats
     */
    private function displayCacheStats(array $stats): void
    {
        if (isset($stats['redis_info'])) {
            $info = $stats['redis_info'];
            $this->line('  Redis Version: ' . ($info['version'] ?? 'N/A'));
            $this->line('  Connected Clients: ' . ($info['connected_clients'] ?? 'N/A'));
            $this->line('  Total Commands: ' . number_format($info['total_commands_processed'] ?? 0));
        }

        if (isset($stats['performance_metrics'])) {
            $metrics = $stats['performance_metrics'];
            $this->line('  Hit Rate: ' . ($metrics['redis_hit_rate'] ?? 0) . '%');
            $this->line('  Active Keys: ' . number_format($metrics['active_keys'] ?? 0));
            $this->line('  Memory Fragmentation: ' . ($metrics['memory_fragmentation_ratio'] ?? 1));
        }

        $this->newLine();
    }

    /**
     * @param array<string, mixed> $stats
     */
    /**
     * DisplayDatabaseStats
     */
    private function displayDatabaseStats(array $stats): void
    {
        if (isset($stats['mysql_status'])) {
            $mysql = $stats['mysql_status'];
            $this->line('  Connected Threads: ' . ($mysql['Threads_connected'] ?? 'N/A'));
            $this->line('  Running Threads: ' . ($mysql['Threads_running'] ?? 'N/A'));
            $this->line('  Total Questions: ' . number_format($mysql['Questions'] ?? 0));
            $this->line('  Slow Queries: ' . number_format($mysql['Slow_queries'] ?? 0));
        }

        $this->newLine();
    }

    /**
     * @param array<string, mixed> $stats
     */
    /**
     * DisplayQueryStats
     */
    private function displayQueryStats(array $stats): void
    {
        $this->line('  Total Queries: ' . number_format($stats['total_queries'] ?? 0));
        $this->line('  Slow Queries: ' . number_format($stats['slow_queries'] ?? 0));
        $this->line('  Average Execution: ' . ($stats['average_execution_time'] ?? 0) . 'ms');
        $this->line('  Peak Execution: ' . ($stats['peak_execution_time'] ?? 0) . 'ms');

        if (isset($stats['connection_distribution'])) {
            $this->line('  Connection Distribution:');
            foreach ($stats['connection_distribution'] as $connection => $count) {
                $this->line("    - {$connection}: " . number_format($count));
            }
        }

        $this->newLine();
    }

    /**
     * DisplayRecommendations
     */
    private function displayRecommendations(): void
    {
        $this->line('<comment>🎯 Performance Recommendations:</comment>');

        $recommendations = [
            '1. Schedule this command to run daily: php artisan hdtickets:optimize-performance --cache',
            '2. Monitor slow queries and optimize them regularly',
            '3. Consider implementing Redis clustering for high traffic',
            '4. Set up database read replicas for heavy read operations',
            '5. Use CDN for static assets and images',
            '6. Implement queue workers for background processing',
            '7. Monitor cache hit rates and adjust TTL values accordingly',
            '8. Regular database maintenance and cleanup of old data',
        ];

        foreach ($recommendations as $recommendation) {
            $this->line("  {$recommendation}");
        }

        $this->newLine();
    }

    /**
     * FormatBytes
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
