<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TicketmasterScraper;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function count;
use function strlen;

class ScrapeTicketmaster extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'ticketmaster:scrape 
                            {keyword : Search keyword for events}
                            {--location= : Location to search for events}
                            {--limit=20 : Maximum number of events to scrape}
                            {--dry-run : Show what would be scraped without importing}';

    /** The console command description. */
    protected $description = 'Scrape events from Ticketmaster and import them as tickets';

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $keyword = (string) $this->argument('keyword');
        $location = $this->option('location') ? (string) $this->option('location') : '';
        $limit = (int) $this->option('limit');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Starting Ticketmaster scraping...');
        $this->info("Keyword: {$keyword}");
        if ($location) {
            $this->info("Location: {$location}");
        }
        $this->info("Limit: {$limit}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No tickets will be imported');
        }

        try {
            $scraper = new TicketmasterScraper();

            if ($dryRun) {
                // For dry run, just get search results without importing
                $client = new \App\Services\TicketApis\TicketmasterClient([
                    'enabled'  => TRUE,
                    'base_url' => 'https://app.ticketmaster.com/discovery/v2/',
                    'timeout'  => 30,
                ]);

                $results = $client->scrapeSearchResults($keyword, $location, $limit);

                if (empty($results)) {
                    $this->error("No events found for keyword: {$keyword}");

                    return Command::FAILURE;
                }

                $this->info('Found ' . count($results) . ' events:');
                $this->table(
                    ['Name', 'Date', 'Venue', 'Price Range', 'URL'],
                    collect($results)->map(function ($event) {
                        return [
                            substr($event['name'], 0, 50) . (strlen($event['name']) > 50 ? '...' : ''),
                            $event['date'] ?? 'N/A',
                            substr($event['venue'] ?? 'N/A', 0, 30),
                            $event['price_range'] ?? 'N/A',
                            $event['url'] ? 'Yes' : 'No',
                        ];
                    })->toArray(),
                );

                return Command::SUCCESS;
            }

            // Actual scraping and import
            $result = $scraper->searchAndImportTickets($keyword, $location, $limit);

            if ($result['success']) {
                $this->info($result['message']);
                $this->info("Total found: {$result['total_found']}");
                $this->info("Successfully imported: {$result['imported']}");

                if (! empty($result['errors'])) {
                    $this->warn('Errors encountered: ' . count($result['errors']));
                    foreach ($result['errors'] as $error) {
                        $this->error("- {$error['event']}: {$error['error']}");
                    }
                }

                // Show statistics
                $this->showStats($scraper);

                return Command::SUCCESS;
            }
            $this->error($result['message']);

            return Command::FAILURE;
        } catch (Exception $e) {
            $this->error('Scraping failed: ' . $e->getMessage());
            Log::error('Ticketmaster scraping command failed', [
                'keyword' => $keyword,
                'error'   => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Show scraping statistics.
     */
    /**
     * ShowStats
     */
    private function showStats(TicketmasterScraper $scraper): void
    {
        $this->info("\n--- Scraping Statistics ---");

        try {
            $stats = $scraper->getScrapingStats();

            $this->info("Total scraped tickets: {$stats['total_scraped']}");
            $this->info("Recently scraped (last 7 days): {$stats['recently_scraped']}");

            if ($stats['last_scrape']) {
                $this->info("Last scrape: {$stats['last_scrape']->format('Y-m-d H:i:s')}");
            }

            if (! empty($stats['category_breakdown']) && $stats['category_breakdown']->isNotEmpty()) {
                $this->info("\nCategory breakdown:");
                foreach ($stats['category_breakdown'] as $categoryStats) {
                    $categoryName = $categoryStats->category->name ?? 'Unknown';
                    $this->info("  {$categoryName}: {$categoryStats->count}");
                }
            }
        } catch (Exception $e) {
            $this->warn('Could not retrieve statistics: ' . $e->getMessage());
        }
    }
}
