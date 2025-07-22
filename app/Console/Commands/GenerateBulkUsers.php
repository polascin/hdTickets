<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class GenerateBulkUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hdtickets:generate-bulk-users
                            {--count=1200 : Number of users to generate}
                            {--fresh : Drop all existing users and start fresh}
                            {--no-specialized : Skip creating specialized scraping accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate bulk fake users for high-demand ticket scraping operations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->option('count');
        $fresh = $this->option('fresh');
        $noSpecialized = $this->option('no-specialized');
        
        $this->info('🎫 HDTickets Bulk User Generator');
        $this->info('==================================');
        
        if ($fresh) {
            if ($this->confirm('⚠️  This will delete ALL existing users. Continue?')) {
                $this->info('Dropping all users...');
                \App\Models\User::truncate();
                $this->warn('All users have been deleted.');
            } else {
                $this->info('Operation cancelled.');
                return;
            }
        }
        
        $this->info("Generating {$count} bulk users for scraping operations...");
        
        // Run the bulk users seeder
        $exitCode = Artisan::call('db:seed', [
            '--class' => 'BulkUsersSeeder'
        ]);
        
        if ($exitCode === 0) {
            $this->info('✅ Bulk user generation completed successfully!');
            
            // Display statistics
            $totalUsers = \App\Models\User::count();
            $activeUsers = \App\Models\User::where('is_active', true)->count();
            $verifiedUsers = \App\Models\User::whereNotNull('email_verified_at')->count();
            
            $this->table([
                'Metric', 'Count'
            ], [
                ['Total Users', $totalUsers],
                ['Active Users', $activeUsers],
                ['Verified Users', $verifiedUsers],
                ['Rotation Ready', $verifiedUsers],
            ]);
            
            // Test user rotation service
            $this->info('Testing user rotation service...');
            $this->testUserRotation();
            
        } else {
            $this->error('❌ Failed to generate bulk users.');
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Test the user rotation service with generated users
     */
    private function testUserRotation()
    {
        try {
            $rotationService = new \App\Services\UserRotationService();
            
            // Test general rotation
            $user1 = $rotationService->getRotatedUser();
            $user2 = $rotationService->getRotatedUser();
            
            if ($user1 && $user2) {
                $this->info("✅ General rotation test passed (User IDs: {$user1->id}, {$user2->id})");
            }
            
            // Test platform-specific rotation
            $stubhubUser = $rotationService->getRotatedUser('stubhub', 'search');
            if ($stubhubUser) {
                $this->info("✅ StubHub rotation test passed (User: {$stubhubUser->email})");
            }
            
            // Test batch rotation
            $batchUsers = $rotationService->getMultipleRotatedUsers(5, 'viagogo', 'details');
            if ($batchUsers->count() >= 3) {
                $this->info("✅ Batch rotation test passed ({$batchUsers->count()} users retrieved)");
            }
            
            // Display rotation statistics
            $stats = $rotationService->getRotationStatistics();
            $this->info('📊 Rotation Statistics:');
            $this->line("  • Total Active Users: {$stats['total_active_users']}");
            $this->line("  • Premium Users: {$stats['premium_users']}");
            $this->line("  • Rotation Pool Users: {$stats['rotation_pool_users']}");
            
            foreach ($stats['platform_specific'] as $platform => $count) {
                $this->line("  • {$platform}: {$count} users");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ User rotation test failed: " . $e->getMessage());
        }
    }
}
