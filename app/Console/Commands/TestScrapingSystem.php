<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ScrapedTicket;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\TicketScrapingService;
use Exception;
use Illuminate\Console\Command;

use function count;
use function is_array;

class TestScrapingSystem extends Command
{
    protected $signature = 'scraping:test {--plugin= : Test specific plugin} {--quick : Run quick tests only}';

    protected $description = 'Test the scraping system functionality';

    public function handle(): int
    {
        $this->info('🧪 Starting Scraping System Tests...');

        $pluginName = $this->option('plugin');
        $quickTest = $this->option('quick');

        try {
            // Test 1: Basic service loading
            $this->info('1️⃣ Testing service loading...');
            $this->testServiceLoading();

            // Test 2: Plugin manager
            $this->info('2️⃣ Testing plugin manager...');
            $this->testPluginManager($pluginName);

            // Test 3: Database connectivity
            $this->info('3️⃣ Testing database...');
            $this->testDatabase();

            if (!$quickTest) {
                // Test 4: Search functionality
                $this->info('4️⃣ Testing search functionality...');
                $this->testSearchFunctionality();

                // Test 5: Individual plugins
                if ($pluginName) {
                    $this->info('5️⃣ Testing specific plugin...');
                    $this->testSpecificPlugin($pluginName);
                } else {
                    $this->info('5️⃣ Testing sample plugins...');
                    $this->testSamplePlugins();
                }
            }

            $this->info('✅ All tests completed successfully!');
        } catch (Exception $e) {
            $this->error('❌ Test failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    private function testServiceLoading(): void
    {
        try {
            $scrapingService = app(TicketScrapingService::class);
            $this->comment('✓ TicketScrapingService loaded');

            $pluginManager = app(PluginBasedScraperManager::class);
            $this->comment('✓ PluginBasedScraperManager loaded');
        } catch (Exception $e) {
            throw new Exception('Service loading failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function testPluginManager($pluginName = NULL): void
    {
        try {
            $manager = app(PluginBasedScraperManager::class);
            $plugins = $manager->getPlugins();

            $this->comment('✓ Found ' . count($plugins) . ' plugins');

            $enabled = 0;
            foreach ($plugins as $plugin) {
                if ($plugin->isEnabled()) {
                    $enabled++;
                }
            }

            $this->comment("✓ {$enabled} plugins enabled");

            if ($pluginName && !isset($plugins[$pluginName])) {
                throw new Exception("Plugin '{$pluginName}' not found");
            }
        } catch (Exception $e) {
            throw new Exception('Plugin manager test failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function testDatabase(): void
    {
        try {
            $count = ScrapedTicket::count();
            $this->comment("✓ Database connected - {$count} tickets found");

            // Test creating a sample ticket
            $testTicket = ScrapedTicket::firstOrCreate([
                'external_id' => 'test_ticket_' . time(),
                'platform'    => 'test',
            ], [
                'title'          => 'Test Event - ' . now()->format('Y-m-d H:i:s'),
                'venue'          => 'Test Venue',
                'min_price'      => 50.00,
                'max_price'      => 150.00,
                'currency'       => 'USD',
                'is_available'   => TRUE,
                'is_high_demand' => FALSE,
                'scraped_at'     => now(),
                'status'         => ScrapedTicket::STATUS_ACTIVE,
                'search_keyword' => 'test',
            ]);

            $this->comment('✓ Test ticket created/found: ' . $testTicket->title);
        } catch (Exception $e) {
            throw new Exception('Database test failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function testSearchFunctionality(): void
    {
        try {
            $service = app(TicketScrapingService::class);

            // Test search with mock data
            $this->comment('🔍 Testing search with "test" keyword...');

            $results = $service->searchTickets('test', [
                'platforms' => ['viagogo'], // Using viagogo as it has mock data
                'max_price' => 200,
                'filters'   => ['mock' => TRUE],
            ]);

            $totalResults = 0;
            foreach ($results as $platform => $tickets) {
                $count = is_array($tickets) ? count($tickets) : 0;
                $totalResults += $count;
                $this->comment("  📊 {$platform}: {$count} results");
            }

            $this->comment("✓ Search completed - {$totalResults} total results");
        } catch (Exception $e) {
            throw new Exception('Search functionality test failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function testSpecificPlugin(string $pluginName): void
    {
        try {
            $manager = app(PluginBasedScraperManager::class);

            $this->comment("🔌 Testing plugin: {$pluginName}");

            $testResult = $manager->testPlugin($pluginName);

            if ($testResult['status'] === 'success') {
                $this->comment('✓ Plugin test passed');
                $this->comment("  📊 Plugin: {$testResult['plugin_info']['name']}");
                $this->comment("  ⏱️ Duration: {$testResult['duration_ms']}ms");
                $this->comment("  📈 Results: {$testResult['test_results']}");
            } else {
                $this->warn("⚠️ Plugin test failed: {$testResult['message']}");
            }
        } catch (Exception $e) {
            throw new Exception("Plugin '{$pluginName}' test failed: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function testSamplePlugins(): void
    {
        try {
            $manager = app(PluginBasedScraperManager::class);
            $plugins = $manager->getPlugins();

            // Test a few sample plugins
            $samplePlugins = ['stubhub', 'ticketmaster', 'arsenalfc'];

            foreach ($samplePlugins as $pluginName) {
                if (isset($plugins[$pluginName])) {
                    $this->comment("🔌 Testing {$pluginName}...");

                    try {
                        $testResult = $manager->testPlugin($pluginName);

                        if ($testResult['status'] === 'success') {
                            $this->comment("  ✓ {$pluginName} test passed");
                        } else {
                            $this->warn("  ⚠️ {$pluginName} test failed: {$testResult['message']}");
                        }
                    } catch (Exception $e) {
                        $this->warn("  ⚠️ {$pluginName} error: " . $e->getMessage());
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception('Sample plugins test failed: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
