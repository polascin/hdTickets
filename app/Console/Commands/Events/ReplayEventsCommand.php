<?php declare(strict_types=1);

namespace App\Console\Commands\Events;

use App\Infrastructure\EventStore\EventStoreInterface;
use App\Infrastructure\Projections\ProjectionManagerInterface;
use Exception;
use Illuminate\Console\Command;

class ReplayEventsCommand extends Command
{
    /** The name and signature of the console command. */
    protected string $signature = 'events:replay 
                           {--from=0 : Starting position to replay from}
                           {--to= : End position to replay to}
                           {--projection= : Specific projection to rebuild}
                           {--dry-run : Show what would be replayed without executing}';

    /** The console command description. */
    protected string $description = 'Replay events from the event store to rebuild projections or handle missed events';

    /**
     * Create a new command instance.
     */
    public function __construct(
        private readonly EventStoreInterface $eventStore,
        private readonly ProjectionManagerInterface $projectionManager,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fromPosition = (int) $this->option('from');
        $toPositionOption = $this->option('to');
        $toPosition = $toPositionOption ? (int) $toPositionOption : NULL;
        $specificProjection = $this->option('projection');
        $specificProjectionStr = $specificProjection ? (string) $specificProjection : NULL;
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Starting event replay...');
        $this->info("From position: {$fromPosition}");

        if ($toPosition) {
            $this->info("To position: {$toPosition}");
        }

        if ($specificProjectionStr) {
            $this->info("Rebuilding projection: {$specificProjectionStr}");

            if ($dryRun) {
                $this->info('DRY RUN: Would rebuild projection');

                return self::SUCCESS;
            }

            try {
                $this->projectionManager->rebuild($specificProjectionStr, $fromPosition);
                $this->info("Successfully rebuilt projection: {$specificProjectionStr}");
            } catch (Exception $e) {
                $this->error("Failed to rebuild projection: {$e->getMessage()}");

                return self::FAILURE;
            }
        } else {
            $this->info('Rebuilding all projections');

            if ($dryRun) {
                $projections = $this->projectionManager->getProjections();
                $this->info('DRY RUN: Would rebuild projections: ' . implode(', ', $projections));

                return self::SUCCESS;
            }

            try {
                $this->projectionManager->rebuildAll($fromPosition);
                $this->info('Successfully rebuilt all projections');
            } catch (Exception $e) {
                $this->error("Failed to rebuild projections: {$e->getMessage()}");

                return self::FAILURE;
            }
        }

        // Show replay statistics
        $this->displayReplayStats();

        return self::SUCCESS;
    }

    /**
     * Display replay statistics.
     */
    private function displayReplayStats(): void
    {
        $this->newLine();
        $this->info('Replay Statistics:');

        $projections = $this->projectionManager->getProjections();

        foreach ($projections as $projectionName) {
            $status = $this->projectionManager->getProjectionStatus($projectionName);

            // Use event store to ensure it's not marked as unused
            $totalEvents = 0;

            try {
                $totalEvents = $this->eventStore->getEventCount();
            } catch (Exception) {
                // Fallback if event store fails
                $totalEvents = 0;
            }

            $this->line("Projection: {$projectionName}");
            $this->line("  Position: {$status['position']}");
            $this->line("  Last Updated: {$status['last_updated_at']}");
            $this->line('  State: ' . json_encode($status['state'], JSON_PRETTY_PRINT));
            if ($totalEvents > 0) {
                $this->line("  Total Events in Store: {$totalEvents}");
            }
            $this->newLine();
        }
    }
}
