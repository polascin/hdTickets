<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scraping\PluginBasedScraperManager;

class TestEuropeanPlatforms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-european';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test European football and ticketing platforms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🌍 Testing European Football & Ticketing Platforms');
        $this->info(str_repeat('=', 60));
        
        $manager = app(PluginBasedScraperManager::class);
        $allPlugins = $manager->getPlugins();
        
        $this->info("Total registered plugins: " . count($allPlugins));
        
        // European football clubs
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
        
        // European ticketing platforms
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
        
        // Test Football Clubs
        $this->info('🏈 Testing European Football Clubs');
        $this->line(str_repeat('-', 40));
        
        foreach ($europeanFootballPlugins as $pluginKey => $pluginName) {
            $this->testPlugin($pluginKey, $pluginName, $allPlugins, $results);
        }
        
        $this->newLine();
        
        // Test Ticketing Platforms
        $this->info('🎫 Testing European Ticketing Platforms');
        $this->line(str_repeat('-', 40));
        
        foreach ($europeanTicketingPlugins as $pluginKey => $pluginName) {
            $this->testPlugin($pluginKey, $pluginName, $allPlugins, $results);
        }
        
        // Display summary
        $this->newLine();
        $this->info('📊 European Platforms Test Summary');
        $this->info(str_repeat('=', 40));
        
        $totalPlatforms = count($allEuropeanPlugins);
        $this->info("Total Platforms: {$totalPlatforms}");
        $this->info("✅ Working: {$results['working']}");
        $this->info("⚠️  With Issues: {$results['issues']}");
        $this->info("❌ Failed: {$results['failed']}");
        $this->info("🚫 Not Registered: {$results['not_registered']}");
        
        $successRate = $totalPlatforms > 0 ? round(($results['working'] / $totalPlatforms) * 100, 1) : 0;
        $this->newLine();
        $this->info("🎯 Success Rate: {$successRate}%");
        
        if ($results['not_registered'] === 0 && $results['failed'] === 0) {
            $this->info('🎉 European platform architecture successfully implemented!');
            return 0;
        } else {
            $this->warn('⚠️  Some platforms need attention');
            return 1;
        }
    }
    
    private function testPlugin(string $pluginKey, string $pluginName, array $allPlugins, array &$results): void
    {
        $this->line("Testing {$pluginName} ({$pluginKey})...");
        
        if (!isset($allPlugins[$pluginKey])) {
            $this->error("  ❌ Plugin is NOT registered");
            $results['not_registered']++;
            return;
        }
        
        $plugin = $allPlugins[$pluginKey];
        $metadata = $plugin->getInfo();
        
        $this->line("  ✅ Plugin registered and loaded");
        $this->line("     Platform: {$metadata['platform']}");
        $this->line("     Description: {$metadata['description']}");
        $this->line("     Currency: {$metadata['currency']}");
        $this->line("     Language: {$metadata['language']}");
        
        // Test connection
        try {
            $connectionTest = $plugin->test();
            
            if ($connectionTest['success']) {
                $this->line("  ✅ Connection test passed!");
                if (isset($connectionTest['response_time_ms'])) {
                    $this->line("     Response time: {$connectionTest['response_time_ms']}ms");
                }
                $results['working']++;
            } else {
                $this->line("  ⚠️  Connection issues: " . $connectionTest['message']);
                $results['issues']++;
            }
            
        } catch (\Exception $e) {
            $this->error("  ❌ Connection test failed: " . $e->getMessage());
            $results['failed']++;
        }
        
        $this->newLine();
    }
}
