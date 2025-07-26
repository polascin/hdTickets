<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\AnalyticsDashboard;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\DB;

class MonitorAnalyticsSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'analytics:monitor {--refresh=5 : Refresh interval in seconds}';

    /**
     * The console command description.
     */
    protected $description = 'Monitor the Advanced Analytics Dashboard system performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $refreshInterval = (int) $this->option('refresh');
        
        $this->info('🔍 Advanced Analytics Dashboard - System Monitor');
        $this->info('=' . str_repeat('=', 50));
        $this->newLine();

        while (true) {
            // Clear the screen (Windows)
            if (PHP_OS_FAMILY === 'Windows') {
                system('cls');
            } else {
                system('clear');
            }

            $this->displayHeader();
            $this->displaySystemMetrics();
            $this->displayQueueStatus();
            $this->displayDatabaseMetrics();
            $this->displayCacheMetrics();
            $this->displayUserActivity();
            
            $this->newLine();
            $this->info("🔄 Refreshing in {$refreshInterval} seconds... (Press Ctrl+C to exit)");
            
            sleep($refreshInterval);
        }
    }

    private function displayHeader()
    {
        $this->info('🚀 HDTickets Analytics System Monitor');
        $this->info('Last Updated: ' . now()->format('Y-m-d H:i:s'));
        $this->info('=' . str_repeat('=', 60));
        $this->newLine();
    }

    private function displaySystemMetrics()
    {
        $totalUsers = User::count();
        $dashboardCount = AnalyticsDashboard::count();
        $coverage = $totalUsers > 0 ? round(($dashboardCount / $totalUsers) * 100, 2) : 0;

        $this->info('📊 SYSTEM METRICS');
        $this->line("   Users: {$totalUsers}");
        $this->line("   Dashboards: {$dashboardCount}");
        $this->line("   Coverage: {$coverage}%");
        $this->newLine();
    }

    private function displayQueueStatus()
    {
        $this->info('⚡ QUEUE STATUS');
        
        $queues = [
            'analytics-high' => 'High Priority Analytics',
            'analytics-medium' => 'Medium Priority Analytics', 
            'notifications' => 'Notifications',
            'default' => 'Default Queue'
        ];

        foreach ($queues as $queueName => $description) {
            try {
                $size = Queue::size($queueName);
                $status = $size == 0 ? '✅' : ($size < 10 ? '⚠️' : '🔴');
                $this->line("   {$status} {$description}: {$size} jobs");
            } catch (\Exception $e) {
                $this->line("   ❌ {$description}: Error");
            }
        }
        $this->newLine();
    }

    private function displayDatabaseMetrics()
    {
        $this->info('💾 DATABASE METRICS');
        
        try {
            // Get recent analytics dashboard activity
            $recentUpdates = AnalyticsDashboard::where('updated_at', '>=', now()->subHour())->count();
            
            // Database connection check
            $dbConnected = DB::connection()->getPdo() ? '✅ Connected' : '❌ Disconnected';
            
            $this->line("   Status: {$dbConnected}");
            $this->line("   Recent Updates (1h): {$recentUpdates}");
            
        } catch (\Exception $e) {
            $this->line("   ❌ Database Error: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function displayCacheMetrics()
    {
        $this->info('⚡ CACHE METRICS');
        
        try {
            // Test cache functionality
            $testKey = 'analytics_monitor_test_' . time();
            Cache::put($testKey, 'test', 10);
            $cacheWorking = Cache::get($testKey) === 'test' ? '✅ Working' : '❌ Failed';
            Cache::forget($testKey);
            
            $this->line("   Status: {$cacheWorking}");
            $this->line("   Driver: " . config('cache.default'));
            
        } catch (\Exception $e) {
            $this->line("   ❌ Cache Error: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    private function displayUserActivity()
    {
        $this->info('👥 USER ACTIVITY');
        
        try {
            // Recent user activity
            $activeUsers = User::where('updated_at', '>=', now()->subDay())->count();
            $totalUsers = User::count();
            $activityRate = $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100, 2) : 0;
            
            $this->line("   Active Today: {$activeUsers}");
            $this->line("   Activity Rate: {$activityRate}%");
            
        } catch (\Exception $e) {
            $this->line("   ❌ Activity Error: " . $e->getMessage());
        }
        
        $this->newLine();
    }
}
