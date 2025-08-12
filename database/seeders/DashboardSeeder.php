<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ScrapedTicket;
use App\Models\Ticket;
use App\Models\User;
use Exception;
use Illuminate\Database\Seeder;

class DashboardSeeder extends Seeder
{
    /**
     * Run the database seeds to populate dashboard test data.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting HD Tickets Dashboard Seeder...');

        // 1. Seed users first (required for other seeders)
        $userCount = User::count();
        if ($userCount < 5) {
            $this->command->info('👥 Creating users...');

            try {
                $this->call(UserSeeder::class);
            } catch (Exception $e) {
                $this->command->warn('⚠️  Some users may already exist - continuing...');
            }
        } else {
            $this->command->info('👥 Users already exist (' . $userCount . ' found) - skipping user creation');
        }

        // 2. Seed categories (for organizing tickets)
        $categoryCount = Category::count();
        if ($categoryCount === 0) {
            $this->command->info('📂 Creating categories...');
            $this->call(CategorySeeder::class);
        } else {
            $this->command->info('📂 Categories already exist (' . $categoryCount . ' found) - skipping category creation');
        }

        // 3. Seed scraped tickets (from web scraping)
        $scrapedTicketCount = ScrapedTicket::count();
        if ($scrapedTicketCount === 0) {
            $this->command->info('🎫 Creating scraped tickets...');
            $this->call(ScrapedTicketsSeeder::class);
        } else {
            $this->command->info('🎫 Scraped tickets already exist (' . $scrapedTicketCount . ' found) - skipping scraped ticket creation');
        }

        // 4. Seed regular tickets (sports events entry tickets)
        $ticketCount = Ticket::count();
        if ($ticketCount === 0) {
            $this->command->info('🏟️ Creating sports event tickets...');
            $this->call(TicketSeeder::class);
        } else {
            $this->command->info('🏟️ Tickets already exist (' . $ticketCount . ' found) - skipping ticket creation');
        }

        // 5. Seed sports events if available
        if (class_exists('Database\\Seeders\\SportsEventsSeeder')) {
            $this->command->info('⚽ Creating sports events...');

            try {
                $this->call('Database\\Seeders\\SportsEventsSeeder');
            } catch (Exception $e) {
                $this->command->warn('⚠️  Could not seed sports events: ' . $e->getMessage());
            }
        }

        // 6. Seed payment plans if available
        if (class_exists('Database\\Seeders\\PaymentPlansSeeder')) {
            $this->command->info('💳 Creating payment plans...');

            try {
                $this->call('Database\\Seeders\\PaymentPlansSeeder');
            } catch (Exception $e) {
                $this->command->warn('⚠️  Could not seed payment plans: ' . $e->getMessage());
            }
        }

        $this->command->info('✅ HD Tickets Dashboard seeding completed successfully!');
        $this->command->newLine();
        $this->showDashboardSummary();
    }

    /**
     * Show summary of seeded data and test accounts
     */
    private function showDashboardSummary(): void
    {
        $userCount = User::count();
        $categoryCount = Category::count();
        $ticketCount = Ticket::count();
        $scrapedTicketCount = ScrapedTicket::count();

        $this->command->info('📊 Database Summary:');
        $this->command->info('   👥 Users: ' . $userCount);
        $this->command->info('   📂 Categories: ' . $categoryCount);
        $this->command->info('   🏟️ Event Tickets: ' . $ticketCount);
        $this->command->info('   🎫 Scraped Tickets: ' . $scrapedTicketCount);

        $this->command->newLine();
        $this->command->info('🔑 Test Accounts:');
        $this->command->info('   📧 Admin: admin@hdtickets.com (password: password)');
        $this->command->info('   📧 Agent: agent@hdtickets.com (password: password)');
        $this->command->info('   📧 Customer: customer@hdtickets.com (password: password)');
        $this->command->info('   📧 Super Admin: ticketmaster@hdtickets.admin (password: SecureAdminPass123!)');
    }
}
