<?php

namespace App\Console\Commands;

use App\Services\TicketScrapingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TicketScrapingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tickets:scrape 
                            {--platform= : Platform to scrape (stubhub, ticketmaster, viagogo)}
                            {--keywords= : Specific keywords to search for}
                            {--manchester-united : Search for Manchester United tickets}
                            {--high-demand : Search for high-demand sports tickets}
                            {--check-alerts : Check and process ticket alerts}
                            {--max-price= : Maximum price filter}
                            {--limit=50 : Maximum number of tickets to process}';

    /**
     * The console command description.
     */
    protected $description = 'Scrape tickets from various platforms and check alerts';

    protected $scrapingService;

    public function __construct(TicketScrapingService $scrapingService)
    {
        parent::__construct();
        $this->scrapingService = $scrapingService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎫 Starting Ticket Scraping Process...');
        $startTime = microtime(true);
        
        try {
            // Check alerts if requested
            if ($this->option('check-alerts')) {
                $this->checkAlerts();
            }

            // Manchester United specific search
            if ($this->option('manchester-united')) {
                $this->scrapeManchesterUnited();
            }

            // High-demand sports tickets
            if ($this->option('high-demand')) {
                $this->scrapeHighDemandSports();
            }

            // Custom keyword search
            if ($this->option('keywords')) {
                $this->scrapeByKeywords();
            }

            // Default: Run both Manchester United and high-demand searches
            if (!$this->option('check-alerts') && 
                !$this->option('manchester-united') && 
                !$this->option('high-demand') && 
                !$this->option('keywords')) {
                $this->info('Running default scraping: Manchester United + High-demand sports');
                $this->scrapeManchesterUnited();
                $this->scrapeHighDemandSports();
            }

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->info("✅ Ticket scraping completed in {$duration} seconds");
            Log::info('Ticket scraping command completed', [
                'duration' => $duration,
                'options' => $this->options()
            ]);

        } catch (\Exception $e) {
            $this->error('❌ Ticket scraping failed: ' . $e->getMessage());
            Log::error('Ticket scraping command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Check ticket alerts
     */
    protected function checkAlerts()
    {
        $this->info('🔍 Checking ticket alerts...');
        
        $alertsChecked = $this->scrapingService->checkAlerts();
        
        $this->info("📧 Checked {$alertsChecked} alerts");
        Log::info('Alert checking completed', ['alerts_checked' => $alertsChecked]);
    }

    /**
     * Scrape Manchester United tickets
     */
    protected function scrapeManchesterUnited()
    {
        $this->info('🔴 Scraping Manchester United tickets...');
        
        $maxPrice = $this->option('max-price');
        $results = $this->scrapingService->searchManchesterUnitedTickets($maxPrice);
        
        $this->displayResults('Manchester United', $results);
    }

    /**
     * Scrape high-demand sports tickets
     */
    protected function scrapeHighDemandSports()
    {
        $this->info('🔥 Scraping high-demand sports tickets...');
        
        $filters = [];
        if ($this->option('max-price')) {
            $filters['max_price'] = $this->option('max-price');
        }
        
        $results = $this->scrapingService->searchHighDemandSportsTickets($filters);
        
        $this->displayResults('High-demand Sports', $results);
    }

    /**
     * Scrape by custom keywords
     */
    protected function scrapeByKeywords()
    {
        $keywords = $this->option('keywords');
        $this->info("🔍 Scraping tickets for: {$keywords}");
        
        $platforms = $this->option('platform') ? [$this->option('platform')] : ['stubhub', 'ticketmaster', 'viagogo'];
        $options = [
            'platforms' => $platforms,
            'max_price' => $this->option('max-price'),
            'limit' => $this->option('limit')
        ];
        
        $results = $this->scrapingService->searchTickets($keywords, $options);
        
        $totalFound = array_sum(array_map('count', $results));
        
        $this->info("📊 Found {$totalFound} tickets for '{$keywords}'");
        
        foreach ($results as $platform => $tickets) {
            if (!empty($tickets)) {
                $this->line("  📍 {$platform}: " . count($tickets) . ' tickets');
            }
        }
    }

    /**
     * Display scraping results
     */
    protected function displayResults($category, $results)
    {
        $totalFound = $results['total_found'] ?? 0;
        $saved = $results['saved'] ?? 0;
        $highDemand = $results['high_demand'] ?? 0;
        
        $this->info("📊 {$category} Results:");
        $this->line("  🎯 Total Found: {$totalFound}");
        $this->line("  💾 Saved: {$saved}");
        $this->line("  🔥 High Demand: {$highDemand}");
        
        if ($highDemand > 0) {
            $this->warn("⚠️  {$highDemand} high-demand tickets detected - alerts may have been triggered");
        }
    }
}
