<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Enhanced\AdvancedTicketCachingService;
use App\Services\Enhanced\ViewFragmentCachingService;
use App\Services\PerformanceCacheService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function count;

class WarmPerformanceCaches extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'cache:warm-performance 
                            {--force : Force cache warming even if caches exist}
                            {--type=all : Type of cache to warm (all|tickets|views|fragments)}';

    /** The console command description. */
    protected $description = 'Warm up all performance-related caches for optimal application speed';

    protected AdvancedTicketCachingService $ticketCache;

    protected ViewFragmentCachingService $fragmentCache;

    protected PerformanceCacheService $performanceCache;

    /**
     * Create a new command instance.
     */
    public function __construct(
        AdvancedTicketCachingService $ticketCache,
        ViewFragmentCachingService $fragmentCache,
        PerformanceCacheService $performanceCache,
    ) {
        parent::__construct();

        $this->ticketCache = $ticketCache;
        $this->fragmentCache = $fragmentCache;
        $this->performanceCache = $performanceCache;
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $startTime = microtime(TRUE);
        $type = (string) ($this->option('type') ?: 'all');
        $force = (bool) $this->option('force');

        $this->info('🚀 Starting performance cache warming...');

        if ($force) {
            $this->info('🔄 Force mode enabled - clearing existing caches first');
            $this->call('cache:clear');
        }

        try {
            switch ($type) {
                case 'tickets':
                    $this->warmTicketCaches();

                    break;
                case 'views':
                    $this->warmViewCaches();

                    break;
                case 'fragments':
                    $this->warmViewFragments();

                    break;
                case 'all':
                default:
                    $this->warmAllCaches();

                    break;
            }

            $duration = round(microtime(TRUE) - $startTime, 2);

            $this->info("✅ Performance cache warming completed in {$duration} seconds");
            $this->displayCacheStats();

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ Cache warming failed: ' . $e->getMessage());
            Log::error('Cache warming failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Warm all performance caches.
     */
    /**
     * WarmAllCaches
     */
    protected function warmAllCaches(): void
    {
        $this->info('🎯 Warming all performance caches...');

        $this->warmTicketCaches();
        $this->warmViewCaches();
        $this->warmViewFragments();

        $this->info('✅ All caches warmed successfully');
    }

    /**
     * Warm ticket-related caches.
     */
    /**
     * WarmTicketCaches
     */
    protected function warmTicketCaches(): void
    {
        $this->info('🎫 Warming ticket caches...');

        $this->executeWithProgress('Ticket cache warming', function (): void {
            // Warm critical ticket data
            $result = $this->ticketCache->warmCriticalCaches();

            if (! $result['success']) {
                throw new Exception('Failed to warm ticket caches: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->line("\n📊 Ticket caches warmed:");
            foreach ($result['caches_warmed'] as $cache) {
                $this->line("   • {$cache}");
            }

            $this->line('⏱️  Duration: ' . round($result['duration'], 3) . 's');
            $this->line('💾 Memory used: ' . $this->formatBytes($result['memory_used']));
        });
    }

    /**
     * Warm view-related caches.
     */
    /**
     * WarmViewCaches
     */
    protected function warmViewCaches(): void
    {
        $this->info('👁️  Warming view caches...');

        $this->executeWithProgress('View cache warming', function (): void {
            // Warm performance cache service
            $this->performanceCache->warmUpCaches();

            $this->line("\n📋 View caches warmed:");
            $this->line('   • Ticket statistics');
            $this->line('   • Platform breakdown');
            $this->line('   • Trending events');
            $this->line('   • User activity stats');
        });
    }

    /**
     * Warm view fragment caches.
     */
    /**
     * WarmViewFragments
     */
    protected function warmViewFragments(): void
    {
        $this->info('🧩 Warming view fragments...');

        $this->executeWithProgress('Fragment cache warming', function (): void {
            // Get common user roles for warming
            $userRoles = ['admin', 'agent', 'customer', 'basic'];

            $result = $this->fragmentCache->warmupFragments($userRoles);

            if (! $result['success']) {
                throw new Exception('Failed to warm view fragments: ' . ($result['error'] ?? 'Unknown error'));
            }

            $this->line("\n🧩 Fragment caches warmed:");
            foreach ($result['fragments_warmed'] as $fragment) {
                $this->line("   • {$fragment}");
            }

            $this->line('⏱️  Duration: ' . round($result['duration'], 3) . 's');
        });
    }

    /**
     * Display cache statistics.
     */
    /**
     * DisplayCacheStats
     */
    protected function displayCacheStats(): void
    {
        $this->info("\n📊 Cache Statistics:");

        try {
            // Get ticket cache metrics
            $ticketMetrics = $this->ticketCache->getCacheMetrics();

            if (! isset($ticketMetrics['error'])) {
                $this->line('🎫 Ticket Cache:');
                $this->line('   • Hit Rate: ' . $ticketMetrics['hit_rate'] . '%');
                $this->line('   • Memory Usage: ' . $ticketMetrics['memory_usage']);
                $this->line('   • Total Keys: ' . number_format($ticketMetrics['total_keys']));
                $this->line('   • Operations/sec: ' . number_format($ticketMetrics['operations_per_second']));
            }

            // Get performance cache status
            $cacheStatus = $this->performanceCache->getCacheStatus();
            $totalCached = count(array_filter($cacheStatus, fn ($item) => $item['exists']));

            $this->line('📋 Performance Cache:');
            $this->line("   • Active Caches: {$totalCached}/" . count($cacheStatus));

            foreach ($cacheStatus as $cache => $status) {
                $statusIcon = $status['exists'] ? '✅' : '❌';
                $size = $this->formatBytes($status['size']);
                $this->line("   {$statusIcon} {$cache}: {$size}");
            }

            // Get fragment cache stats
            $fragmentStats = $this->fragmentCache->getCacheStats();

            $this->line('🧩 Fragment Cache:');
            $this->line('   • Total Fragments: ' . $fragmentStats['total_fragments']);
            $this->line('   • Hit Rate: ' . $fragmentStats['cache_hit_rate'] . '%');
            $this->line('   • Memory Usage: ' . $fragmentStats['memory_usage']);
        } catch (Exception $e) {
            $this->warn('Unable to retrieve cache statistics: ' . $e->getMessage());
        }
    }

    /**
     * Execute a task with progress indicator.
     */
    /**
     * ExecuteWithProgress
     */
    protected function executeWithProgress(string $description, callable $task): void
    {
        $this->output->write("   {$description}... ");

        $startTime = microtime(TRUE);
        $task();
        $duration = microtime(TRUE) - $startTime;

        $this->line('✅ (' . round($duration, 2) . 's)');
    }

    /**
     * Format bytes to human readable format.
     */
    /**
     * FormatBytes
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
