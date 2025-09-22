<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\EnhancedDashboardController;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\Dashboard\DashboardCacheService;
use App\Services\RecommendationService;
use Auth;
use Exception;
use Hash;
use Illuminate\Console\Command;
use ReflectionClass;

use function get_class;

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
    public function handle(): int
    {
        $this->info('Testing HD Tickets Customer Dashboard...');

        // Create or get test admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@hdtickets.local'],
            [
                'name'                => 'Admin',
                'surname'             => 'User',
                'password'            => Hash::make('password123'),
                'role'                => User::ROLE_ADMIN,
                'is_active'           => TRUE,
                'email_verified_at'   => now(),
                'registration_source' => 'console',
                'password_changed_at' => now(),
                'require_2fa'         => FALSE,
            ],
        );

        $this->info("Created/found admin user: {$user->email} (ID: {$user->id})");

        // Test the dashboard controller directly
        try {
            $analytics = app(AnalyticsService::class);
            $recommendations = app(RecommendationService::class);
            $cacheService = app(DashboardCacheService::class);

            $controller = new EnhancedDashboardController($analytics, $recommendations, $cacheService);

            // Simulate authentication
            Auth::login($user);

            $this->info('Testing dashboard data generation...');

            // Test the controller method
            $dashboardData = $this->callPrivateMethod($controller, 'getComprehensiveDashboardData', [$user]);

            $this->info('Dashboard data generated successfully!');
            $this->info('Statistics: ' . json_encode($dashboardData['statistics'] ?? [], JSON_PRETTY_PRINT));

            return 0;
        } catch (Exception $e) {
            $this->error('Dashboard test failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            return 1;
        }
    }

    private function callPrivateMethod(EnhancedDashboardController $object, string $method, array $parameters = []): mixed
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);

        return $method->invokeArgs($object, $parameters);
    }
}
