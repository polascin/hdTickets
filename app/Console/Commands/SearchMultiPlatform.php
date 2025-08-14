<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MultiPlatformManager;
use App\Services\Normalization\DataNormalizationService;
use Exception;
use Illuminate\Console\Command;

use function array_slice;
use function count;

class SearchMultiPlatform extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'search:multi-platform
                            {keyword : Search keyword for events}
                            {--location= : Location to search for events}
                            {--limit=20 : Maximum number of events per platform}
                            {--deduplicate : Remove duplicate events}
                            {--platforms= : Comma-separated list of platforms to search}
                            {--health-check : Perform health check before search}';

    /** The console command description. */
    protected $description = 'Search events across multiple platforms with data normalization';

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
        $deduplicate = (bool) $this->option('deduplicate');
        $healthCheck = (bool) $this->option('health-check');

        $this->info('Starting multi-platform search...');
        $this->info("Keyword: {$keyword}");

        if ($location) {
            $this->info("Location: {$location}");
        }

        $this->info("Limit per platform: {$limit}");

        // Initialize services
        $normalizationService = new DataNormalizationService();
        $multiPlatformManager = new MultiPlatformManager($normalizationService);

        // Perform health check if requested
        if ($healthCheck) {
            $this->info("\nPerforming health check...");
            $healthStatus = $multiPlatformManager->performHealthCheck();
            $this->displayHealthCheck($healthStatus);

            if ($healthStatus['overall_status'] === 'unhealthy') {
                $this->error('All platforms are unhealthy. Aborting search.');

                return Command::FAILURE;
            }
        }

        try {
            // Search across all platforms
            $results = $multiPlatformManager->searchEventsAcrossPlatforms(
                $keyword,
                $location,
                $limit,
            );

            if ($results['total_results'] === 0) {
                $this->warn("No events found for keyword: {$keyword}");

                return Command::SUCCESS;
            }

            $this->info("\nSearch completed!");
            $this->displaySearchResults($results);

            // Handle deduplication if requested
            if ($deduplicate && ! empty($results['normalized_events'])) {
                $this->info("\nPerforming deduplication...");
                $dedupResults = $multiPlatformManager->deduplicateEvents($results['normalized_events']);
                $this->displayDeduplicationResults($dedupResults);
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('Multi-platform search failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Display health check results.
     *
     * @param array<string, mixed> $healthStatus
     */
    /**
     * DisplayHealthCheck
     */
    private function displayHealthCheck(array $healthStatus): void
    {
        $this->info('Overall Status: ' . strtoupper($healthStatus['overall_status']));
        $this->info("Healthy Platforms: {$healthStatus['healthy_count']}/{$healthStatus['total_count']}");

        $headers = ['Platform', 'Status', 'Response Time (ms)', 'Errors'];
        $rows = [];

        foreach ($healthStatus['platforms'] as $platform => $health) {
            $rows[] = [
                $health['name'],
                $health['status'],
                $health['response_time'] ?? 'N/A',
                ! empty($health['errors']) ? implode(', ', $health['errors']) : 'None',
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Display search results.
     *
     * @param array<string, mixed> $results
     */
    /**
     * DisplaySearchResults
     */
    private function displaySearchResults(array $results): void
    {
        $this->info("Total events found: {$results['total_results']}");

        // Display platform breakdown
        $platformHeaders = ['Platform', 'Events Found', 'Status'];
        $platformRows = [];

        foreach ($results['platforms'] as $platformName => $platformData) {
            $platformRows[] = [
                ucfirst($platformName),
                $platformData['count'],
                isset($platformData['error']) ? 'Error: ' . substr($platformData['error'], 0, 50) : 'Success',
            ];
        }

        $this->table($platformHeaders, $platformRows);

        // Display sample events
        if (! empty($results['normalized_events'])) {
            $this->info("\nSample Events (showing first 5):");

            $eventHeaders = ['Name', 'Date', 'Venue', 'City', 'Platform', 'Price Range'];
            $eventRows = [];

            $sampleEvents = array_slice($results['normalized_events'], 0, 5);

            foreach ($sampleEvents as $event) {
                $priceRange = 'N/A';
                if ($event['price_min'] && $event['price_max']) {
                    $priceRange = $event['currency'] . ' ' . $event['price_min'] . ' - ' . $event['price_max'];
                } elseif ($event['price_min']) {
                    $priceRange = $event['currency'] . ' ' . $event['price_min'];
                }

                $eventRows[] = [
                    substr($event['name'], 0, 30),
                    $event['date'] ?? 'N/A',
                    substr($event['venue'], 0, 20),
                    substr($event['city'], 0, 15),
                    $event['platform'],
                    $priceRange,
                ];
            }

            $this->table($eventHeaders, $eventRows);
        }
    }

    /**
     * Display deduplication results.
     *
     * @param array<string, mixed> $dedupResults
     */
    /**
     * DisplayDeduplicationResults
     */
    private function displayDeduplicationResults(array $dedupResults): void
    {
        $this->info('Deduplication Results:');
        $this->info("Original events: {$dedupResults['original_count']}");
        $this->info("After deduplication: {$dedupResults['deduplicated_count']}");
        $this->info("Duplicates removed: {$dedupResults['duplicates_removed']}");

        if (! empty($dedupResults['duplicate_groups'])) {
            $this->info('Found ' . count($dedupResults['duplicate_groups']) . ' duplicate groups:');

            foreach ($dedupResults['duplicate_groups'] as $groupIndex => $group) {
                $this->line('Group ' . ($groupIndex + 1) . ':');
                foreach ($group as $event) {
                    $this->line("  - {$event['name']} ({$event['platform']})");
                }
            }
        }
    }
}
