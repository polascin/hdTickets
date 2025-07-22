<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BulkUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates 1000+ fake users specifically for high-demand ticket scraping rotation
     */
    public function run(): void
    {
        $this->command->info('Starting bulk user creation for ticket scraping...');
        
        // Disable query log to save memory
        DB::connection()->disableQueryLog();
        
        // Create users in batches for better performance
        $batchSize = 100;
        $totalUsers = 1200; // Create 1200 users total
        
        for ($i = 0; $i < $totalUsers; $i += $batchSize) {
            $remainingUsers = min($batchSize, $totalUsers - $i);
            
            $this->command->info("Creating batch " . (($i / $batchSize) + 1) . " - " . $remainingUsers . " users...");
            
            // Create users with specific configurations for scraping
            $users = User::factory()
                ->count($remainingUsers)
->state(function (array $attributes) {
                    // Override some attributes for scraping users
                    $roles = ['customer', 'customer', 'customer', 'agent', 'agent', 'customer'];
                    return [
                        'email_verified_at' => now(), // All scraping users should be verified
                        'is_active' => true, // All scraping users should be active
                        'role' => $roles[array_rand($roles)],
                    ];
                })
                ->create();
                
            // Add some delay to prevent overwhelming the database
            usleep(50000); // 50ms delay
        }
        
        // Create specialized scraping user accounts
        $this->createSpecializedScrapingUsers();
        
        $this->command->info("Successfully created {$totalUsers} bulk users for ticket scraping!");
        $this->command->info("Total users in database: " . User::count());
        
        // Display user distribution
        $this->displayUserDistribution();
    }
    
    /**
     * Get role for scraping users with weighted distribution
     */
    private function getScrapingRole(): string
    {
        // For scraping, we want more customers and agents, fewer admins
        $roles = ['customer', 'customer', 'customer', 'agent', 'agent', 'customer'];
        return $roles[array_rand($roles)];
    }
    
    /**
     * Create specialized user accounts for different scraping scenarios
     */
    private function createSpecializedScrapingUsers(): void
    {
        $this->command->info('Creating specialized scraping user accounts...');
        
        // Create premium customer accounts (for high-value ticket scraping)
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'name' => "Premium Customer {$i}",
                'email' => "premium.customer{$i}@scrapingtest.com",
                'email_verified_at' => now(),
                'password' => Hash::make('scraping123'),
                'role' => User::ROLE_CUSTOMER,
                'is_active' => true,
            ]);
        }
        
        // Create agent accounts (for platform-specific scraping)
        $platforms = ['stubhub', 'viagogo', 'seatgeek', 'tickpick', 'fanzone'];
        foreach ($platforms as $platform) {
            for ($i = 1; $i <= 20; $i++) {
                User::create([
                    'name' => ucfirst($platform) . " Agent {$i}",
                    'email' => "{$platform}.agent{$i}@scrapingtest.com",
                    'email_verified_at' => now(),
                    'password' => Hash::make('scraping123'),
                    'role' => User::ROLE_AGENT,
                    'is_active' => true,
                ]);
            }
        }
        
        // Create bot detection evasion accounts
        for ($i = 1; $i <= 100; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            
            User::create([
                'name' => "{$firstName} {$lastName}",
                'email' => strtolower("{$firstName}.{$lastName}.{$i}@rotationpool.com"),
                'email_verified_at' => now(),
                'password' => Hash::make('rotation123'),
                'role' => fake()->randomElement([User::ROLE_CUSTOMER, User::ROLE_AGENT]),
                'is_active' => true,
            ]);
        }
        
        $this->command->info('Specialized scraping accounts created successfully!');
    }
    
    /**
     * Display user distribution statistics
     */
    private function displayUserDistribution(): void
    {
        $this->command->info("\n=== USER DISTRIBUTION ===");
        
        $totalUsers = User::count();
        $admins = User::where('role', User::ROLE_ADMIN)->count();
        $agents = User::where('role', User::ROLE_AGENT)->count();
        $customers = User::where('role', User::ROLE_CUSTOMER)->count();
        $activeUsers = User::where('is_active', true)->count();
        $verifiedUsers = User::whereNotNull('email_verified_at')->count();
        
        $this->command->table([
            'Metric', 'Count', 'Percentage'
        ], [
            ['Total Users', $totalUsers, '100%'],
            ['Admins', $admins, round(($admins / $totalUsers) * 100, 2) . '%'],
            ['Agents', $agents, round(($agents / $totalUsers) * 100, 2) . '%'],
            ['Customers', $customers, round(($customers / $totalUsers) * 100, 2) . '%'],
            ['Active Users', $activeUsers, round(($activeUsers / $totalUsers) * 100, 2) . '%'],
            ['Verified Users', $verifiedUsers, round(($verifiedUsers / $totalUsers) * 100, 2) . '%'],
        ]);
        
        $this->command->info("\n=== SCRAPING-READY ACCOUNTS ===");
        $this->command->info("Premium Customers: 50");
        $this->command->info("Platform Agents: 100 (20 per platform)");
        $this->command->info("Rotation Pool: 100");
        $this->command->info("Total Specialized: 250");
        $this->command->info("Bulk Generated: " . ($totalUsers - 250));
    }
}
