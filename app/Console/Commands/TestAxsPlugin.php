<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Scraping\PluginBasedScraperManager;

class TestAxsPlugin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-axs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test AXS scraper plugin registration and basic functionality';

    /**
     * Execute the console command.
     */
    public function handle(PluginBasedScraperManager $manager)
    {
        $this->info('Testing AXS Plugin Registration and Functionality');
        $this->info('================================================');
        
        // List all registered plugins
        $plugins = $manager->getPlugins();
        $this->info('Registered plugins: ' . implode(', ', array_keys($plugins)));
        
        // Test AXS plugin specifically
        $axsPlugin = $manager->getPlugin('axs');
        
        if (!$axsPlugin) {
            $this->error('❌ AXS plugin is NOT registered!');
            return 1;
        }
        
        $this->info('✅ AXS plugin is registered and loaded');
        
        // Display plugin info
        $info = $axsPlugin->getInfo();
        $this->info('Plugin Info:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $info['name']],
                ['Description', $info['description']],
                ['Version', $info['version']],
                ['Platform', $info['platform']],
                ['Enabled', $axsPlugin->isEnabled() ? 'YES' : 'NO'],
                ['Capabilities', implode(', ', $info['capabilities'])],
                ['Supported Criteria', implode(', ', $info['supported_criteria'])],
            ]
        );
        
        // Test plugin functionality
        $this->info('\nTesting plugin functionality...');
        try {
            $testResult = $manager->testPlugin('axs');
            
            if ($testResult['status'] === 'success') {
                $this->info('✅ Plugin test passed!');
                $this->info('Duration: ' . $testResult['duration_ms'] . 'ms');
            } else {
                $this->warn('⚠️  Plugin test had issues: ' . $testResult['message']);
            }
        } catch (\Exception $e) {
            $this->error('❌ Plugin test failed: ' . $e->getMessage());
        }
        
        return 0;
    }
}
