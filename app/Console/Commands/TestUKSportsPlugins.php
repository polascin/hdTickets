<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scraping\PluginBasedScraperManager;

class TestUKSportsPlugins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-uk-sports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all UK Sports scraper plugins registration and basic functionality';

    /**
     * Execute the console command.
     */
    public function handle(PluginBasedScraperManager $manager)
    {
        $this->info('Testing UK Sports Plugins Registration and Functionality');
        $this->info('==========================================================');
        
        // UK Sports platforms to test
        $ukPlatforms = [
            'wimbledon' => 'Wimbledon Championships',
            'liverpoolfc' => 'Liverpool FC',
            'wembleystadium' => 'Wembley Stadium', 
            'ticketekuk' => 'Ticketek UK',
            'arsenalfc' => 'Arsenal FC',
            'twickenham' => 'Twickenham Stadium',
            'lordscricket' => 'Lord\'s Cricket Ground'
        ];
        
        // List all registered plugins
        $plugins = $manager->getPlugins();
        $this->info('Total registered plugins: ' . count($plugins));
        $this->info('Registered plugins: ' . implode(', ', array_keys($plugins)));
        $this->newLine();
        
        $results = [];
        
        foreach ($ukPlatforms as $platformKey => $platformName) {
            $this->info("Testing {$platformName} ({$platformKey})...");
            
            $plugin = $manager->getPlugin($platformKey);
            
            if (!$plugin) {
                $this->error("❌ {$platformName} plugin is NOT registered!");
                $results[$platformKey] = 'not_registered';
                continue;
            }
            
            $this->info("✅ {$platformName} plugin is registered and loaded");
            
            // Display plugin info
            $info = $plugin->getInfo();
            $this->line("   Platform: {$info['platform']}");
            $this->line("   Description: {$info['description']}");
            $this->line("   Version: {$info['version']}");
            $this->line("   Enabled: " . ($plugin->isEnabled() ? 'YES' : 'NO'));
            
            // Test plugin functionality
            try {
                $testResult = $manager->testPlugin($platformKey);
                
                if ($testResult['status'] === 'success') {
                    $this->info("   ✅ Plugin test passed!");
                    if (isset($testResult['duration_ms'])) {
                        $this->line("   Duration: {$testResult['duration_ms']}ms");
                    }
                    $results[$platformKey] = 'success';
                } else {
                    $this->warn("   ⚠️  Plugin test had issues: {$testResult['message']}");
                    $results[$platformKey] = 'warning';
                }
            } catch (\Exception $e) {
                $this->error("   ❌ Plugin test failed: " . $e->getMessage());
                $results[$platformKey] = 'error';
            }
            
            $this->newLine();
        }
        
        // Summary
        $this->info('UK Sports Plugins Test Summary');
        $this->info('==================================');
        
        $successCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $notRegisteredCount = 0;
        
        foreach ($results as $platform => $status) {
            $platformName = $ukPlatforms[$platform];
            switch ($status) {
                case 'success':
                    $this->line("✅ {$platformName}: Working");
                    $successCount++;
                    break;
                case 'warning':
                    $this->line("⚠️  {$platformName}: Has issues");
                    $warningCount++;
                    break;
                case 'error':
                    $this->line("❌ {$platformName}: Failed");
                    $errorCount++;
                    break;
                case 'not_registered':
                    $this->line("🚫 {$platformName}: Not registered");
                    $notRegisteredCount++;
                    break;
            }
        }
        
        $this->newLine();
        $this->info("Total Platforms: " . count($ukPlatforms));
        $this->info("✅ Working: {$successCount}");
        $this->info("⚠️  With Issues: {$warningCount}");
        $this->info("❌ Failed: {$errorCount}");
        $this->info("🚫 Not Registered: {$notRegisteredCount}");
        
        if ($successCount + $warningCount === count($ukPlatforms)) {
            $this->info('\n🎉 All UK Sports plugins are registered and functional!');
            return 0;
        } else {
            $this->error('\n⚠️  Some UK Sports plugins have issues.');
            return 1;
        }
    }
}
