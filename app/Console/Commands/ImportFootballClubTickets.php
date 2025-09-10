<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Platforms\FootballClubStoresService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function count;
use function is_array;
use function is_string;
use function sprintf;

class ImportFootballClubTickets extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'football:import-tickets
                            {--clubs=* : Specific club keys to import (e.g., arsenal,chelsea)}
                            {--all : Import from all supported clubs}
                            {--league= : Filter by league (e.g., "Premier League", "La Liga")}
                            {--country= : Filter by country (e.g., England, Spain)}
                            {--date-from= : Start date for fixtures (Y-m-d format)}
                            {--date-to= : End date for fixtures (Y-m-d format)}
                            {--competition= : Filter by competition name}
                            {--dry-run : Show what would be imported without saving}';

    /** The console command description. */
    protected $description = 'Import football match tickets from European club official stores';

    /**
     * Create a new command instance.
     */
    public function __construct(protected FootballClubStoresService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    /**
     * Handle
     */
    public function handle(): int
    {
        $this->info('🏈 Football Club Ticket Import Starting...');
        $this->newLine();

        try {
            // Determine which clubs to process
            $clubsToProcess = $this->determineClubsToProcess();

            if ($clubsToProcess === []) {
                $this->error('❌ No clubs selected for processing.');

                return Command::FAILURE;
            }

            // Build filters from options
            $filters = $this->buildFilters();

            $this->info('📋 Processing ' . count($clubsToProcess) . ' club(s): ' . implode(', ', $clubsToProcess));

            if ($filters !== []) {
                $this->info('🔍 Filters applied: ' . json_encode($filters, JSON_PRETTY_PRINT));
            }
            $this->newLine();

            // Show supported clubs info
            if ($this->option('verbose')) {
                $this->displaySupportedClubs();
            }

            // Search for tickets
            $this->info('🔎 Searching for tickets...');
            $searchResults = $this->service->searchTickets($clubsToProcess, $filters);

            if (! $searchResults['success']) {
                $this->error('❌ Search failed:');
                foreach ($searchResults['errors'] as $error) {
                    $this->line("  • {$error}");
                }

                return Command::FAILURE;
            }

            // Display search results
            $this->displaySearchResults($searchResults);

            // Import tickets (unless dry-run)
            if (! $this->option('dry-run')) {
                if ($this->confirm('Proceed with importing tickets to database?', TRUE)) {
                    $this->info('💾 Importing tickets to database...');
                    $importResults = $this->service->importTickets($clubsToProcess, $filters);
                    $this->displayImportResults($importResults);
                } else {
                    $this->info('❌ Import cancelled by user.');
                }
            } else {
                $this->warn('🔍 Dry-run mode: No tickets were saved to database.');
            }

            // Show platform statistics
            if ($this->option('verbose')) {
                $this->displayStatistics();
            }

            $this->newLine();
            $this->info('✅ Football club ticket import completed!');

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error('❌ An error occurred: ' . $e->getMessage());
            Log::error('Football club ticket import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }

    /**
     * Determine which clubs to process based on options.
     *
     * @return array<int, string>
     */
    /**
     * DetermineClubsToProcess
     */
    private function determineClubsToProcess(): array
    {
        $supportedClubs = $this->service->getSupportedClubs();
        $clubKeys = array_keys($supportedClubs);

        // If specific clubs requested
        $clubs = $this->option('clubs');
        if (! empty($clubs)) {
            $validClubs = array_intersect($clubs, $clubKeys);

            if ($validClubs === []) {
                $this->error('❌ None of the requested clubs are supported.');
                $this->line('Available clubs: ' . implode(', ', $clubKeys));

                return [];
            }

            return array_values(array_filter($validClubs, fn ($club): bool => is_string($club)));
        }

        // If all clubs requested
        if ((bool) $this->option('all')) {
            return $clubKeys;
        }

        // Filter by league
        $league = $this->option('league');
        if ($league) {
            return array_values(array_filter($clubKeys, fn (int|string $key): bool => strcasecmp((string) $supportedClubs[$key]['league'], $league) === 0));
        }

        // Filter by country
        $country = $this->option('country');
        if ($country) {
            return array_values(array_filter($clubKeys, fn (int|string $key): bool => strcasecmp((string) $supportedClubs[$key]['country'], $country) === 0));
        }

        // Interactive selection
        return $this->interactiveClubSelection($clubKeys);
    }

    /**
     * Interactive club selection.
     *
     * @param array<int, string> $clubKeys
     *
     * @return array<int, string>
     */
    /**
     * InteractiveClubSelection
     */
    private function interactiveClubSelection(array $clubKeys): array
    {
        $this->info('🏟️ Available Football Clubs:');
        $supportedClubs = $this->service->getSupportedClubs();

        foreach ($clubKeys as $index => $key) {
            $club = $supportedClubs[$key];
            $this->line(sprintf(
                '  %d. %s (%s - %s)',
                $index + 1,
                $club['name'],
                $club['league'],
                $club['country'],
            ));
        }
        $this->newLine();

        $choice = $this->choice(
            'Select clubs to process (comma-separated numbers or "all")',
            array_merge(['all'], array_map(fn (int|string $i): string => (string) ($i + 1), array_keys($clubKeys))),
            'all',
        );

        if ($choice === 'all') {
            return $clubKeys;
        }

        $choiceStr = is_array($choice) ? implode(',', $choice) : (string) $choice;
        $selectedNumbers = array_map('trim', explode(',', $choiceStr));
        $selectedClubs = [];

        foreach ($selectedNumbers as $number) {
            if (is_numeric($number) && isset($clubKeys[$number - 1])) {
                $selectedClubs[] = $clubKeys[$number - 1];
            }
        }

        return $selectedClubs;
    }

    /**
     * Build filters from command options.
     *
     * @return array<string, mixed>
     */
    /**
     * BuildFilters
     */
    private function buildFilters(): array
    {
        $filters = [];

        $dateFrom = $this->option('date-from');
        if ($dateFrom) {
            $filters['date_from'] = $dateFrom;
        }

        $dateTo = $this->option('date-to');
        if ($dateTo) {
            $filters['date_to'] = $dateTo;
        }

        $competition = $this->option('competition');
        if ($competition) {
            $filters['competition'] = $competition;
        }

        return $filters;
    }

    /**
     * Display supported clubs information.
     */
    /**
     * DisplaySupportedClubs
     */
    private function displaySupportedClubs(): void
    {
        $supportedClubs = $this->service->getSupportedClubs();

        $this->info('🏟️ Supported Football Clubs:');
        $this->newLine();

        $clubsByLeague = [];
        foreach ($supportedClubs as $club) {
            $clubsByLeague[$club['league']][] = $club;
        }

        foreach ($clubsByLeague as $league => $clubs) {
            $this->line("<fg=yellow>{$league}:</>");
            foreach ($clubs as $club) {
                $this->line("  • {$club['name']} ({$club['country']})");
            }
            $this->newLine();
        }
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
        $this->info('📊 Search Results:');
        $this->line("  • Clubs searched: {$results['clubs_searched']}");
        $this->line("  • Successful searches: {$results['successful_searches']}");
        $this->newLine();

        if (! empty($results['results'])) {
            $totalFixtures = 0;
            $totalTickets = 0;

            foreach ($results['results'] as $clubData) {
                $fixtureCount = count($clubData['fixtures']);
                $ticketCount = 0;

                foreach ($clubData['fixtures'] as $fixture) {
                    $ticketCount += count($fixture['ticket_categories']);
                }

                $totalFixtures += $fixtureCount;
                $totalTickets += $ticketCount;

                $this->line("  🏆 {$clubData['club']} ({$clubData['league']})");
                $this->line("     • Fixtures found: {$fixtureCount}");
                $this->line("     • Ticket categories: {$ticketCount}");

                if ($this->option('verbose') && $fixtureCount > 0) {
                    foreach (array_slice($clubData['fixtures'], 0, 3) as $fixture) {
                        $this->line("       - {$clubData['club']} vs {$fixture['opponent']} (" .
                                   ($fixture['date'] ? date('M j, Y', strtotime((string) $fixture['date'])) : 'TBD') . ')');
                    }
                    if ($fixtureCount > 3) {
                        $this->line('       ... and ' . ($fixtureCount - 3) . ' more fixtures');
                    }
                }
                $this->newLine();
            }

            $this->info('📈 Total Summary:');
            $this->line("  • Total fixtures: {$totalFixtures}");
            $this->line("  • Total ticket categories: {$totalTickets}");
        }

        if (! empty($results['errors'])) {
            $this->warn('⚠️ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }
        $this->newLine();
    }

    /**
     * Display import results.
     *
     * @param array<string, mixed> $results
     */
    /**
     * DisplayImportResults
     */
    private function displayImportResults(array $results): void
    {
        if ($results['success']) {
            $this->info('✅ Import completed successfully!');
            $this->line("  • Tickets imported: {$results['imported_count']}");
            $this->line("  • Clubs processed: {$results['clubs_processed']}");
        } else {
            $this->error('❌ Import failed.');
        }

        if (! empty($results['errors'])) {
            $this->warn('⚠️ Import errors:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }
        $this->newLine();
    }

    /**
     * Display platform statistics.
     */
    /**
     * DisplayStatistics
     */
    private function displayStatistics(): void
    {
        $this->info('📊 Platform Statistics:');

        $stats = $this->service->getStatistics();

        $this->line("  • Total tickets in database: {$stats['total_tickets']}");
        $this->line("  • Available tickets: {$stats['available_tickets']}");
        $this->line("  • Availability rate: {$stats['availability_rate']}%");
        $this->line("  • Supported clubs: {$stats['supported_clubs']}");

        if (! empty($stats['leagues'])) {
            $this->line('  • Leagues covered:');
            foreach ($stats['leagues'] as $league => $count) {
                $this->line("    - {$league}: {$count} tickets");
            }
        }

        if ($stats['last_updated']) {
            $this->line("  • Last updated: {$stats['last_updated']}");
        }

        $this->newLine();
    }
}
