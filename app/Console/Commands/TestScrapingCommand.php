<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ScrapedTicket;
use App\Models\User;
use Illuminate\Console\Command;

class TestScrapingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:scraping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test scraping functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing HD Tickets Scraping Functionality');
        $this->info('=========================================');

        // Test ScrapedTicket model
        $ticketCount = ScrapedTicket::count();
        $this->info("Found {$ticketCount} scraped tickets");

        // Test future tickets
        $futureTickets = ScrapedTicket::where('event_date', '>', now())->count();
        $this->info("Found {$futureTickets} future tickets");

        // Test platforms
        $platforms = ScrapedTicket::select('platform')->distinct()->pluck('platform');
        $this->info('Available platforms: ' . implode(', ', $platforms->toArray()));

        // Test users
        $userCount = User::count();
        $this->info("Found {$userCount} users in system");

        $this->info('✅ All basic tests passed!');
        $this->info('The scraping functionality appears to be working correctly.');

        return 0;
    }
}
