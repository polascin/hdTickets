<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scraping\PluginBasedScraperManager;

class TestTier3Plugins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-tier3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test all Tier 3 UK Sports scraper plugins registration and functionality';

    /**
     * Execute the console command.
     */
    public function handle(PluginBasedScraperManager $manager)
    {
        $this->info('Testing Tier 3 UK Sports Plugins Registration and Functionality');
        $this->info('============================================================');
        
        // Tier 3 UK Sports platforms to test
        $tier3Platforms = [
            'seeticketsuk' => 'See Tickets UK',
            'chelseafc' => 'Chelsea FC',
            'tottenham' => 'Tottenham Hotspur',
            'englandcricket' => 'England Cricket',
            'silverstonef1' => 'Silverstone F1',
            'celticfc' => 'Celtic FC'
        ];
        
        // List all registered plugins
        $plugins = $manager->getPlugins();
        $this->info('Total registered plugins: ' . count($plugins));
        $this->info('All registered plugins: ' . implode(', ', array_keys($plugins)));
        $this->newLine();
        
        $results = [];
        
        foreach ($tier3Platforms as $platformKey => $platformName) {
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
            if (isset($info['venue'])) {
                $this->line("   Venue: {$info['venue']}");
            }
            
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
        $this->info('Tier 3 UK Sports Plugins Test Summary');
        $this->info('=====================================');
        
        $successCount = 0;
        $warningCount = 0;
        $errorCount = 0;
        $notRegisteredCount = 0;
        
        foreach ($results as $platform => $status) {
            $platformName = $tier3Platforms[$platform];
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
        $this->info("Total Platforms: " . count($tier3Platforms));
        $this->info("✅ Working: {$successCount}");
        $this->info("⚠️  With Issues: {$warningCount}");
        $this->info("❌ Failed: {$errorCount}");
        $this->info("🚫 Not Registered: {$notRegisteredCount}");
        
        // Overall results
        $totalFunctional = $successCount + $warningCount;
        $totalPlatforms = count($tier3Platforms);
        
        if ($totalFunctional === $totalPlatforms) {
            $this->info('\n🎉 All Tier 3 UK Sports plugins are registered and functional!');
            return 0;
        } else {
            $completion = round(($totalFunctional / $totalPlatforms) * 100);
            $this->warn("\n⚠️  Tier 3 implementation: {$completion}% complete ({$totalFunctional}/{$totalPlatforms})");
            return $notRegisteredCount > 0 ? 1 : 0;
        }
    }
}
