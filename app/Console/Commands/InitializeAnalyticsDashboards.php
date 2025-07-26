<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\AnalyticsDashboard;
use App\Services\AdvancedAnalyticsDashboard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InitializeAnalyticsDashboards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:init-dashboards 
                            {--force : Force initialization even if dashboards exist}
                            {--user= : Initialize for specific user ID}
                            {--clear-cache : Clear analytics cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize default analytics dashboards for users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Initializing Analytics Dashboards...');

        if ($this->option('clear-cache')) {
            $this->clearAnalyticsCache();
        }

        $userId = $this->option('user');
        $force = $this->option('force');

        if ($userId) {
            $this->initializeForUser($userId, $force);
        } else {
            $this->initializeForAllUsers($force);
        }

        $this->info('✅ Analytics Dashboard initialization completed!');
    }

    /**
     * Initialize dashboards for all users
     */
    private function initializeForAllUsers(bool $force = false)
    {
        $users = User::all();
        $progressBar = $this->output->createProgressBar($users->count());
        
        $this->info("Initializing dashboards for {$users->count()} users...");
        $progressBar->start();

        $created = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($this->initializeUserDashboard($user, $force)) {
                $created++;
            } else {
                $skipped++;
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
        $this->info("✅ Created: {$created} dashboards");
        $this->info("⏭️  Skipped: {$skipped} dashboards");
    }

    /**
     * Initialize dashboard for specific user
     */
    private function initializeForUser(int $userId, bool $force = false)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found!");
            return;
        }

        $this->info("Initializing dashboard for user: {$user->name} (ID: {$userId})");
        
        if ($this->initializeUserDashboard($user, $force)) {
            $this->info("✅ Dashboard created successfully for {$user->name}");
        } else {
            $this->warn("⏭️  Dashboard already exists for {$user->name}");
        }
    }

    /**
     * Initialize dashboard for a user
     */
    private function initializeUserDashboard(User $user, bool $force = false): bool
    {
        try {
            // Check if user already has a default dashboard
            $existingDashboard = AnalyticsDashboard::getDefaultForUser($user->id);
            
            if ($existingDashboard && !$force) {
                return false; // Skip if dashboard exists and not forcing
            }

            if ($existingDashboard && $force) {
                $this->warn("🔄 Removing existing dashboard for user {$user->name}");
                $existingDashboard->delete();
            }

            // Create default dashboard
            $dashboard = AnalyticsDashboard::createDefaultForUser($user->id);
            
            // Customize dashboard based on user role or preferences
            $this->customizeDashboardForUser($dashboard, $user);
            
            return true;

        } catch (\Exception $e) {
            $this->error("❌ Failed to create dashboard for user {$user->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Customize dashboard based on user characteristics
     */
    private function customizeDashboardForUser(AnalyticsDashboard $dashboard, User $user)
    {
        $configuration = $dashboard->configuration;
        $widgets = $dashboard->widgets;

        // Admin users get additional widgets
        if ($user->hasRole('admin')) {
            $widgets = array_merge($widgets, [
                'system_overview',
                'user_analytics',
                'platform_health'
            ]);
        }

        // Premium users get enhanced features
        if ($user->hasRole('premium')) {
            $configuration['features'] = array_merge($configuration['features'] ?? [], [
                'advanced_exports',
                'custom_alerts',
                'ml_insights'
            ]);
        }

        // Update dashboard with customizations
        $dashboard->update([
            'configuration' => $configuration,
            'widgets' => $widgets
        ]);
    }

    /**
     * Clear analytics cache
     */
    private function clearAnalyticsCache()
    {
        $this->info('🧹 Clearing analytics cache...');
        
        $cacheKeys = [
            'analytics:*',
            'dashboard:*',
            'price_trends:*',
            'demand_patterns:*',
            'platform_performance:*'
        ];

        foreach ($cacheKeys as $pattern) {
            Cache::flush(); // For simplicity, we'll flush all cache
        }

        $this->info('✅ Analytics cache cleared');
    }

    /**
     * Validate analytics system health
     */
    private function validateSystemHealth()
    {
        $this->info('🔍 Validating analytics system health...');

        $checks = [
            'Database Connection' => $this->checkDatabaseConnection(),
            'Cache System' => $this->checkCacheSystem(),
            'Queue System' => $this->checkQueueSystem(),
            'Required Tables' => $this->checkRequiredTables(),
            'Analytics Service' => $this->checkAnalyticsService()
        ];

        $allPassed = true;

        foreach ($checks as $check => $result) {
            if ($result) {
                $this->info("✅ {$check}: OK");
            } else {
                $this->error("❌ {$check}: FAILED");
                $allPassed = false;
            }
        }

        if ($allPassed) {
            $this->info('🎉 All system checks passed!');
        } else {
            $this->error('⚠️  Some system checks failed. Please review the issues above.');
        }

        return $allPassed;
    }

    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkCacheSystem(): bool
    {
        try {
            Cache::put('analytics_test', 'test_value', 60);
            $value = Cache::get('analytics_test');
            Cache::forget('analytics_test');
            return $value === 'test_value';
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkQueueSystem(): bool
    {
        try {
            // Simple check to see if queue configuration exists
            return config('queue.default') !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkRequiredTables(): bool
    {
        $requiredTables = [
            'analytics_dashboards',
            'ticket_price_histories',
            'user_preferences',
            'alert_escalations',
            'user_notification_settings'
        ];

        try {
            foreach ($requiredTables as $table) {
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    return false;
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkAnalyticsService(): bool
    {
        try {
            $service = app(AdvancedAnalyticsDashboard::class);
            return $service instanceof AdvancedAnalyticsDashboard;
        } catch (\Exception $e) {
            return false;
        }
    }
}
