<?php

namespace App\Console\Commands;

use App\Services\TicketApiManager;
use Illuminate\Console\Command;

class TestTicketApis extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ticket-apis:test {--platform=* : Specific platform to test}';

    /**
     * The console command description.
     */
    protected $description = 'Test connections to ticket APIs';

    /**
     * Execute the console command.
     */
    public function handle(TicketApiManager $apiManager)
    {
        $platforms = $this->option('platform') ?: $apiManager->getAvailablePlatforms();
        
        if (empty($platforms)) {
            $this->error('No ticket APIs are configured or enabled.');
            return 1;
        }

        $this->info('Testing ticket API connections...');
        $this->line('');

        foreach ($platforms as $platform) {
            if (!$apiManager->isPlatformAvailable($platform)) {
                $this->warn("Platform '{$platform}' is not available or not configured.");
                continue;
            }

            $this->info("Testing {$platform}...");
            
            try {
                // Test with a simple search
                $results = $apiManager->searchEvents(['q' => 'test', 'per_page' => 1], [$platform]);
                
                if (!empty($results[$platform])) {
                    $this->info("✓ {$platform}: Connected successfully (" . count($results[$platform]) . " test results)");
                } else {
                    $this->warn("⚠ {$platform}: Connected but no test results returned");
                }
            } catch (\Exception $e) {
                $this->error("✗ {$platform}: Failed - " . $e->getMessage());
            }
        }

        $this->line('');
        $this->info('API connection testing completed.');
        
        return 0;
    }
}
