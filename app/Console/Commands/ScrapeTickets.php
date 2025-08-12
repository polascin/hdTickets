<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ProxyRotationService;
use App\Services\Scraping\PluginBasedScraperManager;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;

class ScrapeTickets extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'tickets:scrape-v2
                            {--plugin= : Specific plugin to run (optional)}
                            {--keyword= : Keyword to search for}
                            {--location= : Location to search in}
                            {--test : Test mode - run plugin tests}
                            {--list : List available plugins}
                            {--status : Show plugin status and health}';

    /** The console command description. */
    protected $description = 'Scrape tickets from various platforms using plugin-based system';

    private PluginBasedScraperManager $scraperManager;

    public function __construct()
    {
        parent::__construct();

        // Initialize scraper manager in constructor
        $proxyService = app(ProxyRotationService::class);
        $this->scraperManager = new PluginBasedScraperManager($proxyService);
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎫 HDTickets Scraper System');
        $this->info('================================');

        // Handle different command options
        if ($this->option('list')) {
            return $this->listPlugins();
        }

        if ($this->option('status')) {
            return $this->showStatus();
        }

        if ($this->option('test')) {
            return $this->testPlugins();
        }

        // Run scraping
        return $this->runScraping();
    }

    /**
     * List all available plugins
     */
    private function listPlugins(): int
    {
        $plugins = $this->scraperManager->getPlugins();

        if (empty($plugins)) {
            $this->warn('No plugins found. Make sure plugins are properly configured.');

            return Command::FAILURE;
        }

        $this->info('Available Plugins (' . count($plugins) . ' total):');
        $this->line('');

        foreach ($plugins as $name => $plugin) {
            $info = $plugin->getInfo();
            $status = $plugin->isEnabled() ? '✅ Enabled' : '❌ Disabled';

            $this->line("🔌 <comment>{$info['name']}</comment> ({$name})");
            $this->line("   Status: {$status}");
            $this->line("   Version: {$info['version']}");
            $this->line("   Platform: {$info['platform']}");
            $this->line("   Description: {$info['description']}");

            if (! empty($info['capabilities'])) {
                $this->line('   Capabilities: ' . implode(', ', $info['capabilities']));
            }

            $this->line('');
        }

        return Command::SUCCESS;
    }

    /**
     * Show plugin status and health
     */
    private function showStatus(): int
    {
        $health = $this->scraperManager->getHealthStatus();
        $stats = $this->scraperManager->getPluginStats();

        $this->info('System Health Status');
        $this->line('==================');

        $healthIcon = match ($health['status']) {
            'healthy'  => '🟢',
            'warning'  => '🟡',
            'critical' => '🔴',
            default    => '⚪',
        };

        $this->line("{$healthIcon} Overall Health: {$health['overall_health']}% ({$health['status']})");
        $this->line("📊 Total Plugins: {$health['total_plugins']}");
        $this->line("✅ Enabled: {$health['enabled_plugins']}");
        $this->line("💚 Healthy: {$health['healthy_plugins']}");
        $this->line("❗ Recent Errors: {$health['recent_errors']}");
        $this->line('');

        $this->info('Plugin Statistics');
        $this->line('=================');

        foreach ($stats as $name => $stat) {
            $status = $stat['enabled'] ? '✅' : '❌';
            $this->line("{$status} <comment>{$stat['info']['name']}</comment>");
            $this->line('   Last Run: ' . ($stat['last_run'] ?? 'Never'));
            $this->line("   Success Rate: {$stat['success_rate']}%");
            $this->line("   Total Runs: {$stat['total_runs']}");
            $this->line("   Avg Results: {$stat['avg_results']}");
            $this->line('');
        }

        return Command::SUCCESS;
    }

    /**
     * Test all or specific plugins
     */
    private function testPlugins(): int
    {
        $pluginName = $this->option('plugin');

        if ($pluginName) {
            return $this->testSinglePlugin($pluginName);
        }

        $this->info('Testing All Plugins');
        $this->line('==================');

        $plugins = $this->scraperManager->getPlugins();
        $totalTests = 0;
        $passedTests = 0;

        foreach ($plugins as $name => $plugin) {
            $totalTests++;
            $this->line("Testing {$name}...");

            try {
                $result = $this->scraperManager->testPlugin($name);

                if ($result['status'] === 'success') {
                    $this->line("  ✅ <info>PASSED</info> - {$result['message']}");
                    if (isset($result['duration_ms'])) {
                        $this->line("     Duration: {$result['duration_ms']}ms");
                    }
                    if (isset($result['test_results'])) {
                        $this->line("     Results: {$result['test_results']} items");
                    }
                    $passedTests++;
                } else {
                    $this->line("  ❌ <error>FAILED</error> - {$result['message']}");
                }
            } catch (Exception $e) {
                $this->line("  ❌ <error>ERROR</error> - {$e->getMessage()}");
            }

            $this->line('');
        }

        $this->info("Test Summary: {$passedTests}/{$totalTests} plugins passed");

        return $passedTests === $totalTests ? 0 : 1;
    }

    /**
     * Test a single plugin
     */
    private function testSinglePlugin(string $pluginName): int
    {
        $this->info("Testing Plugin: {$pluginName}");
        $this->line('========================');

        try {
            $result = $this->scraperManager->testPlugin($pluginName);

            if ($result['status'] === 'success') {
                $this->line('✅ <info>TEST PASSED</info>');
                $this->line('Message: ' . ($result['message'] ?? 'Test completed successfully'));

                if (isset($result['duration_ms'])) {
                    $this->line("Duration: {$result['duration_ms']}ms");
                }

                if (isset($result['test_results'])) {
                    $this->line("Test Results: {$result['test_results']} items found");
                }

                if (isset($result['sample_data']) && $result['sample_data']) {
                    $this->line('');
                    $this->info('Sample Data:');
                    $jsonData = json_encode($result['sample_data'], JSON_PRETTY_PRINT);
                    $this->line($jsonData ?: 'Failed to encode sample data');
                }

                return 0;
            }
            $this->line('❌ <error>TEST FAILED</error>');
            $this->line("Message: {$result['message']}");

            return 1;
        } catch (Exception $e) {
            $this->error("Test failed with exception: {$e->getMessage()}");

            return 1;
        }
    }

    /**
     * Run actual scraping
     */
    private function runScraping(): int
    {
        $pluginName = $this->option('plugin');
        $keyword = $this->option('keyword') ?? $this->ask('Enter search keyword');
        $location = $this->option('location') ?? $this->ask('Enter location (optional)', '');

        if (empty($keyword)) {
            $this->error('Keyword is required for scraping');

            return 1;
        }

        $criteria = [
            'keyword'     => $keyword,
            'location'    => $location,
            'max_results' => 20,
        ];

        $this->info('Starting scraping with criteria:');
        $this->line("Keyword: {$keyword}");
        if ($location) {
            $this->line("Location: {$location}");
        }
        $this->line('');

        try {
            if ($pluginName) {
                $this->info("Running specific plugin: {$pluginName}");
                $results = $this->scraperManager->scrapeWithPlugin($pluginName, $criteria);
                $this->displaySinglePluginResults($pluginName, $results);
            } else {
                $this->info('Running all enabled plugins...');
                $results = $this->scraperManager->scrapeAll($criteria);
                $this->displayAllPluginResults($results);
            }

            return 0;
        } catch (Exception $e) {
            $this->error("Scraping failed: {$e->getMessage()}");
            Log::error('Scraping command failed', [
                'criteria' => $criteria,
                'plugin'   => $pluginName,
                'error'    => $e->getMessage(),
            ]);

            return 1;
        }
    }

    /**
     * Display results from a single plugin
     *
     * @param array<int, array<string, mixed>> $results
     */
    private function displaySinglePluginResults(string $pluginName, array $results): void
    {
        $this->info("Results from {$pluginName}:");
        $this->line('========================');

        if (empty($results)) {
            $this->warn('No results found');

            return;
        }

        $this->line('Found ' . count($results) . ' results:');
        $this->line('');

        foreach ($results as $index => $result) {
            $lineNumber = $index + 1;
            $this->line("#{$lineNumber}:");
            $this->line('  Event: ' . ($result['event_name'] ?? $result['name'] ?? 'Unknown'));

            if (! empty($result['venue'])) {
                $this->line("  Venue: {$result['venue']}");
            }

            if (! empty($result['date'])) {
                $this->line("  Date: {$result['date']}");
            }

            if (! empty($result['price_min']) || ! empty($result['price_max'])) {
                $priceInfo = '';
                if (! empty($result['price_min'])) {
                    $priceInfo .= "from £{$result['price_min']}";
                }
                if (! empty($result['price_max'])) {
                    $priceInfo .= " to £{$result['price_max']}";
                }
                $this->line("  Price: {$priceInfo}");
            }

            if (! empty($result['url'])) {
                $this->line("  URL: {$result['url']}");
            }

            $this->line('');
        }
    }

    /**
     * Display results from all plugins
     *
     * @param array<string, mixed> $results
     */
    private function displayAllPluginResults(array $results): void
    {
        $summary = $results['summary'];

        $this->info('Scraping Summary');
        $this->line('================');
        $this->line("Total Plugins: {$summary['total_plugins']}");
        $this->line("Successful: {$summary['successful_plugins']}");
        $this->line("Failed: {$summary['failed_plugins']}");
        $this->line("Total Results: {$summary['total_results']}");
        $this->line('');

        if (! empty($results['errors'])) {
            $this->warn('Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line("  {$error['plugin']}: {$error['error']}");
            }
            $this->line('');
        }

        foreach ($results['results'] as $pluginName => $pluginResult) {
            if ($pluginResult['status'] === 'success' && ! empty($pluginResult['results'])) {
                $this->info("Results from {$pluginName} ({$pluginResult['count']} found):");
                $this->displaySinglePluginResults($pluginName, array_slice($pluginResult['results'], 0, 3));
            }
        }
    }
}
