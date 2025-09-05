<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestDashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test customer dashboard functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing HD Tickets Customer Dashboard...');
        
        // Create or get test admin user
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@hdtickets.local'],
            [
                'name' => 'Admin',
                'surname' => 'User',
                'password' => \Hash::make('password123'),
                'role' => \App\Models\User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
                'registration_source' => 'console',
                'password_changed_at' => now(),
                'require_2fa' => false,
            ]
        );
        
        $this->info("Created/found admin user: {$user->email} (ID: {$user->id})");
        
        // Test the dashboard controller directly
        try {
            $analytics = app(\App\Services\AnalyticsService::class);
            $recommendations = app(\App\Services\RecommendationService::class);
            
            $controller = new \App\Http\Controllers\EnhancedDashboardController($analytics, $recommendations);
            
            // Simulate authentication
            \Auth::login($user);
            
            $this->info('Testing dashboard data generation...');
            
            // Test the controller method
            $dashboardData = $this->callPrivateMethod($controller, 'getComprehensiveDashboardData', [$user]);
            
            $this->info('Dashboard data generated successfully!');
            $this->info('Statistics: ' . json_encode($dashboardData['statistics'] ?? [], JSON_PRETTY_PRINT));
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Dashboard test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
    
    private function callPrivateMethod($object, $method, $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);
        $method->setAccessible(true);
        
        return $method->invokeArgs($object, $parameters);
    }
}
