<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Dashboard\DashboardCacheService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class WarmDashboardCache extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'dashboard:cache-warm 
                            {--user-id= : Specific user ID to warm cache for}
                            {--all-users : Warm cache for all active users}
                            {--chunk-size=50 : Number of users to process in each chunk}';

    /** The console command description. */
    protected $description = 'Warm dashboard cache to improve performance';

    /** Dashboard cache service */
    protected DashboardCacheService $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(DashboardCacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔥 Starting dashboard cache warming...');

        try {
            if ($userId = $this->option('user-id')) {
                return $this->warmSingleUser((int) $userId);
            }

            if ($this->option('all-users')) {
                return $this->warmAllUsers();
            }

            // Default: warm cache for recently active users
            return $this->warmRecentlyActiveUsers();
        } catch (Exception $e) {
            $this->error('Cache warming failed: ' . $e->getMessage());
            Log::error('Dashboard cache warming failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Warm cache for a single user
     */
    private function warmSingleUser(int $userId): int
    {
        $user = User::find($userId);

        if (! $user) {
            $this->error("User with ID {$userId} not found.");

            return self::FAILURE;
        }

        $this->info("Warming cache for user: {$user->name} (ID: {$userId})");

        $this->cacheService->warmCache($user);

        $this->info('✅ Cache warmed successfully for single user.');

        return self::SUCCESS;
    }

    /**
     * Warm cache for all users
     */
    private function warmAllUsers(): int
    {
        $chunkSize = (int) $this->option('chunk-size');
        $totalUsers = User::where('is_active', TRUE)->count();

        $this->info("Warming cache for all {$totalUsers} active users...");

        $progressBar = $this->output->createProgressBar($totalUsers);
        $processedUsers = 0;
        $failedUsers = 0;

        User::where('is_active', TRUE)
            ->orderBy('last_activity_at', 'desc')
            ->chunk($chunkSize, function ($users) use ($progressBar, &$processedUsers, &$failedUsers): void {
                foreach ($users as $user) {
                    try {
                        $this->cacheService->warmCache($user);
                        $processedUsers++;
                    } catch (Exception $e) {
                        $failedUsers++;
                        Log::warning('Failed to warm cache for user', [
                            'user_id' => $user->id,
                            'error'   => $e->getMessage(),
                        ]);
                    }

                    $progressBar->advance();
                }
            });

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Users', $totalUsers],
                ['Successfully Processed', $processedUsers],
                ['Failed', $failedUsers],
                ['Success Rate', round(($processedUsers / $totalUsers) * 100, 2) . '%'],
            ],
        );

        $this->info('✅ Cache warming completed for all users.');

        return self::SUCCESS;
    }

    /**
     * Warm cache for recently active users
     */
    private function warmRecentlyActiveUsers(): int
    {
        $users = User::where('is_active', TRUE)
            ->whereNotNull('last_activity_at')
            ->where('last_activity_at', '>=', now()->subDays(7))
            ->orderBy('last_activity_at', 'desc')
            ->limit(100)
            ->get();

        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            $this->warn('No recently active users found.');

            return self::SUCCESS;
        }

        $this->info("Warming cache for {$totalUsers} recently active users...");

        $progressBar = $this->output->createProgressBar($totalUsers);
        $processedUsers = 0;
        $failedUsers = 0;

        foreach ($users as $user) {
            try {
                $this->cacheService->warmCache($user);
                $processedUsers++;
            } catch (Exception $e) {
                $failedUsers++;
                Log::warning('Failed to warm cache for user', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Recently Active Users', $totalUsers],
                ['Successfully Processed', $processedUsers],
                ['Failed', $failedUsers],
                ['Success Rate', round(($processedUsers / max($totalUsers, 1)) * 100, 2) . '%'],
            ],
        );

        $this->info('✅ Cache warming completed for recently active users.');

        return self::SUCCESS;
    }
}
