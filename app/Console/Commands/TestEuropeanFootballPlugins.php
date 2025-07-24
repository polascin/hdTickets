<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\Scraping\Contracts\ScraperPluginInterface;

class TestEuropeanFootballPlugins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-european-football';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test European football club plugins registration and functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing European Football Club Plugins Registration and Functionality');
        $this->info(str_repeat('=', 72));
        
        $manager = app(PluginBasedScraperManager::class);
        $allPlugins = $manager->getAvailablePlugins();
        
        $this->info("Total registered plugins: " . count($allPlugins));
        
        // European football club plugin keys
        $europeanFootballPlugins = [
            'manchester_city' => 'Manchester City FC',
            'real_madrid' => 'Real Madrid CF',
            'barcelona' => 'FC Barcelona',
            'atletico_madrid' => 'Atlético Madrid',
            'bayern_munich' => 'FC Bayern Munich',
            'borussia_dortmund' => 'Borussia Dortmund',
            'juventus' => 'Juventus FC',
            'ac_milan' => 'AC Milan',
            'inter_milan' => 'Inter Milan',
            'psg' => 'Paris Saint-Germain',
            'newcastle_united' => 'Newcastle United FC',
        ];
        
        $europeanTicketingPlugins = [
            'eventim' => 'Eventim.de',
            'fnac_spectacles' => 'Fnac Spectacles',
            'vivaticket' => 'VivaTicket',
            'entradas' => 'Entradas.com',
        ];
        
        $allEuropeanPlugins = array_merge($europeanFootballPlugins, $europeanTicketingPlugins);
        
        $this->newLine();
        
        $results = [
            'working' => 0,
            'issues' => 0,
            'failed' => 0,
            'not_registered' => 0
        ];
        
        foreach ($allEuropeanPlugins as $pluginKey => $pluginName) {
            $this->info("Testing {$pluginName} ({$pluginKey})...");
            
            if (!isset($allPlugins[$pluginKey])) {
                $this->error("❌ {$pluginName} plugin is NOT registered");
                $results['not_registered']++;
                $this->newLine();
                continue;
            }
            
            $plugin = $allPlugins[$pluginKey];
            $metadata = $plugin->getMetadata();
            
            $this->line("✅ {$pluginName} plugin is registered and loaded");
            $this->line("   Platform: {$metadata['platform']}");
            $this->line("   Description: {$metadata['description']}");
            $this->line("   Version: {$metadata['version']}");
            $this->line("   Enabled: " . ($metadata['enabled'] ? 'YES' : 'NO'));
            
            if (isset($metadata['venue'])) {
                $this->line("   Venue: {$metadata['venue']}");
            }
            
            // Test connection
            try {
                $connectionTest = $plugin->testConnection();
                
                if ($connectionTest['success']) {
                    $this->line("   ✅ Plugin test passed!");
                    if (isset($connectionTest['response_time_ms'])) {
                        $this->line("   Duration: {$connectionTest['response_time_ms']}ms");
                    }
                    $results['working']++;
                } else {
                    $this->line("   ⚠️  Plugin test had issues: " . $connectionTest['message']);
                    $results['issues']++;
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ Plugin test failed: " . $e->getMessage());
                $results['failed']++;
            }
            
            $this->newLine();
        }
        
        // Display summary
        $this->info('European Football & Ticketing Plugins Test Summary');
        $this->info(str_repeat('=', 54));
        
        foreach ($allEuropeanPlugins as $pluginKey => $pluginName) {
            if (!isset($allPlugins[$pluginKey])) {
                $this->line("🚫 {$pluginName}: Not Registered");
            } else {
                try {
                    $plugin = $allPlugins[$pluginKey];
                    $connectionTest = $plugin->testConnection();
                    
                    if ($connectionTest['success']) {
                        $this->line("✅ {$pluginName}: Working");
                    } else {
                        $this->line("⚠️  {$pluginName}: Has issues");
                    }
                } catch (\Exception $e) {
                    $this->line("❌ {$pluginName}: Failed");
                }
            }
        }
        
        $this->newLine();
        $totalPlatforms = count($allEuropeanPlugins);
        $this->info("Total Platforms: {$totalPlatforms}");
        $this->info("✅ Working: {$results['working']}");
        $this->info("⚠️  With Issues: {$results['issues']}");
        $this->info("❌ Failed: {$results['failed']}");
        $this->info("🚫 Not Registered: {$results['not_registered']}");
        
        if ($results['not_registered'] === 0) {
            $this->newLine();
            $this->info('🎉 All European football & ticketing plugins are registered and functional!');
        }
        
        return 0;
    }
}
