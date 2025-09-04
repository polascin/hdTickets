<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ScrapedTicket;
use DB;
use Exception;
use Illuminate\Console\Command;
use Route;

use function count;

class EnhanceScrapingSystem extends Command
{
    protected $signature = 'scraping:enhance';

    protected $description = 'Enhance scraping system with improvements and fixes';

    public function handle()
    {
        $this->info('🚀 Enhancing Scraping System...');

        try {
            // 1. Create sample data for testing
            $this->info('1️⃣ Creating sample test data...');
            $this->createSampleData();

            // 2. Test API endpoints
            $this->info('2️⃣ Testing API endpoints...');
            $this->testApiEndpoints();

            // 3. Optimize database
            $this->info('3️⃣ Optimizing database...');
            $this->optimizeDatabase();

            // 4. Generate summary report
            $this->info('4️⃣ Generating summary report...');
            $this->generateSummaryReport();

            $this->info('✅ Scraping system enhancement completed!');
        } catch (Exception $e) {
            $this->error('❌ Enhancement failed: ' . $e->getMessage());

            return 1;
        }

        return 0;
    }

    private function createSampleData(): void
    {
        $sampleTickets = [
            [
                'platform'         => 'stubhub',
                'external_id'      => 'stubhub_sample_1',
                'title'            => 'Manchester United vs Liverpool',
                'venue'            => 'Old Trafford',
                'location'         => 'Manchester, UK',
                'sport'            => 'football',
                'team'             => 'Manchester United',
                'event_date'       => now()->addDays(30),
                'min_price'        => 85.00,
                'max_price'        => 450.00,
                'currency'         => 'GBP',
                'is_available'     => TRUE,
                'is_high_demand'   => TRUE,
                'status'           => ScrapedTicket::STATUS_ACTIVE,
                'search_keyword'   => 'manchester united',
                'scraped_at'       => now(),
                'popularity_score' => 95.5,
            ],
            [
                'platform'         => 'ticketmaster',
                'external_id'      => 'tm_sample_1',
                'title'            => 'Arsenal vs Chelsea',
                'venue'            => 'Emirates Stadium',
                'location'         => 'London, UK',
                'sport'            => 'football',
                'team'             => 'Arsenal',
                'event_date'       => now()->addDays(45),
                'min_price'        => 65.00,
                'max_price'        => 350.00,
                'currency'         => 'GBP',
                'is_available'     => TRUE,
                'is_high_demand'   => FALSE,
                'status'           => ScrapedTicket::STATUS_ACTIVE,
                'search_keyword'   => 'arsenal',
                'scraped_at'       => now(),
                'popularity_score' => 78.2,
            ],
            [
                'platform'         => 'viagogo',
                'external_id'      => 'viagogo_sample_1',
                'title'            => 'Wimbledon Finals',
                'venue'            => 'All England Club',
                'location'         => 'Wimbledon, London',
                'sport'            => 'tennis',
                'team'             => NULL,
                'event_date'       => now()->addDays(60),
                'min_price'        => 150.00,
                'max_price'        => 800.00,
                'currency'         => 'GBP',
                'is_available'     => TRUE,
                'is_high_demand'   => TRUE,
                'status'           => ScrapedTicket::STATUS_ACTIVE,
                'search_keyword'   => 'wimbledon',
                'scraped_at'       => now(),
                'popularity_score' => 88.7,
            ],
        ];

        foreach ($sampleTickets as $ticketData) {
            ScrapedTicket::updateOrCreate(
                ['external_id' => $ticketData['external_id']],
                $ticketData,
            );
        }

        $this->comment('✓ Created/updated ' . count($sampleTickets) . ' sample tickets');
    }

    private function testApiEndpoints(): void
    {
        $endpoints = [
            '/api/scraping/tickets',
            '/api/scraping/platforms',
        ];

        foreach ($endpoints as $endpoint) {
            try {
                // Test if route exists
                $route = Route::getRoutes()->getByName('api.' . str_replace(['/', '-'], ['.', '_'], trim($endpoint, '/')));
                if ($route) {
                    $this->comment("✓ API endpoint exists: {$endpoint}");
                } else {
                    $this->warn("⚠️ API endpoint missing: {$endpoint}");
                }
            } catch (Exception $e) {
                $this->warn("⚠️ Could not test endpoint {$endpoint}: " . $e->getMessage());
            }
        }
    }

    private function optimizeDatabase(): void
    {
        try {
            // Add indexes if they don't exist
            DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_platform ON scraped_tickets(platform)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_is_available ON scraped_tickets(is_available)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_scraped_at ON scraped_tickets(scraped_at)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_event_date ON scraped_tickets(event_date)');
            DB::statement('CREATE INDEX IF NOT EXISTS idx_scraped_tickets_is_high_demand ON scraped_tickets(is_high_demand)');

            $this->comment('✓ Database indexes optimized');

            // Clean up old test tickets
            $deletedCount = ScrapedTicket::where('platform', 'test')
                ->where('created_at', '<', now()->subDay())
                ->delete();

            if ($deletedCount > 0) {
                $this->comment("✓ Cleaned up {$deletedCount} old test tickets");
            }
        } catch (Exception $e) {
            $this->warn('⚠️ Database optimization issue: ' . $e->getMessage());
        }
    }

    private function generateSummaryReport(): void
    {
        $stats = [
            'total_tickets'       => ScrapedTicket::count(),
            'available_tickets'   => ScrapedTicket::where('is_available', TRUE)->count(),
            'high_demand_tickets' => ScrapedTicket::where('is_high_demand', TRUE)->count(),
            'platforms'           => ScrapedTicket::distinct('platform')->count(),
            'recent_tickets'      => ScrapedTicket::where('scraped_at', '>=', now()->subDays(7))->count(),
        ];

        $this->info("\n📊 SCRAPING SYSTEM SUMMARY");
        $this->info(str_repeat('=', 50));
        $this->comment("🎫 Total tickets: {$stats['total_tickets']}");
        $this->comment("✅ Available tickets: {$stats['available_tickets']}");
        $this->comment("🔥 High demand tickets: {$stats['high_demand_tickets']}");
        $this->comment("🏢 Active platforms: {$stats['platforms']}");
        $this->comment("📅 Recent tickets (7 days): {$stats['recent_tickets']}");

        // Platform breakdown
        $platformStats = ScrapedTicket::selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->get();

        $this->info("\n🏢 PLATFORM BREAKDOWN:");
        foreach ($platformStats as $platform) {
            $this->comment("  • {$platform->platform}: {$platform->count} tickets");
        }

        $this->info("\n🚀 NEXT STEPS:");
        $this->comment('1. Visit: https://hdtickets.local/tickets/scraping');
        $this->comment('2. Test search functionality');
        $this->comment('3. Create ticket alerts');
        $this->comment('4. Monitor system performance');

        $this->info("\n✨ System is ready for use!");
    }
}
