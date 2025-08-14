<?php declare(strict_types=1);

namespace App\Console\Commands\Events;

use App\Infrastructure\EventStore\EventStoreInterface;
use App\Infrastructure\Projections\ProjectionManagerInterface;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function count;
use function sprintf;
use function strlen;

class MonitorEventsCommand extends Command
{
    /** The name and signature of the console command. */
    protected $signature = 'events:monitor 
                           {--watch : Watch events in real-time}
                           {--stats : Show event statistics}
                           {--failures : Show failed event processing}
                           {--projections : Show projection status}';

    /** The console command description. */
    protected $description = 'Monitor event store and projection health';

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
    /**
     * Handle
     */
    public function handle(): int
    {
        $watch = $this->option('watch');
        $stats = $this->option('stats');
        $failures = $this->option('failures');
        $projections = $this->option('projections');

        if ($watch) {
            return $this->watchEvents();
        }

        if ($stats) {
            $this->showEventStatistics();
        }

        if ($failures) {
            $this->showProcessingFailures();
        }

        if ($projections) {
            $this->showProjectionStatus();
        }

        if (! $stats && ! $failures && ! $projections) {
            $this->showOverview();
        }

        return self::SUCCESS;
    }

    /**
     * Watch for new events in real-time.
     */
    /**
     * WatchEvents
     */
    private function watchEvents(): int
    {
        $this->info('Watching for new events (Press Ctrl+C to stop)...');
        $lastEventId = $this->getLastEventId();

        $maxIterations = 1000; // Prevent infinite loop in case of issues
        $iterations = 0;

        while ($iterations < $maxIterations) {
            $iterations++;
            $newEvents = DB::table('event_store')
                ->where('id', '>', $lastEventId)
                ->orderBy('id')
                ->limit(10)
                ->get();

            foreach ($newEvents as $event) {
                $this->line(sprintf(
                    '[%s] %s | %s | %s',
                    $event->recorded_at,
                    $this->getEventTypeName($event->event_type),
                    $event->aggregate_type,
                    $event->aggregate_root_id,
                ));
                $lastEventId = $event->id;
            }

            sleep(1);

            // Allow graceful exit on signals or interruption
            if ($this->shouldStop()) {
                break;
            }
        }

        return self::SUCCESS;
    }

    /**
     * Show event store statistics.
     */
    /**
     * ShowEventStatistics
     */
    private function showEventStatistics(): void
    {
        $this->info('Event Store Statistics');
        $this->newLine();

        // Total events
        $totalEvents = DB::table('event_store')->count();
        $this->line("Total Events: {$totalEvents}");

        // Events by type
        $eventsByType = DB::table('event_store')
            ->select('event_type', DB::raw('count(*) as count'))
            ->groupBy('event_type')
            ->orderBy('count', 'desc')
            ->get();

        $this->newLine();
        $this->line('Events by Type:');
        foreach ($eventsByType as $row) {
            $eventTypeName = $this->getEventTypeName($row->event_type);
            $this->line("  {$eventTypeName}: {$row->count}");
        }

        // Events by aggregate type
        $eventsByAggregate = DB::table('event_store')
            ->select('aggregate_type', DB::raw('count(*) as count'))
            ->groupBy('aggregate_type')
            ->orderBy('count', 'desc')
            ->get();

        $this->newLine();
        $this->line('Events by Aggregate:');
        foreach ($eventsByAggregate as $row) {
            $this->line("  {$row->aggregate_type}: {$row->count}");
        }

        // Recent activity
        $recentEvents = DB::table('event_store')
            ->select(DB::raw('DATE(recorded_at) as date'), DB::raw('count(*) as count'))
            ->where('recorded_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $this->newLine();
        $this->line('Recent Activity (Last 7 days):');
        foreach ($recentEvents as $row) {
            $this->line("  {$row->date}: {$row->count} events");
        }
    }

    /**
     * Show event processing failures.
     */
    /**
     * ShowProcessingFailures
     */
    private function showProcessingFailures(): void
    {
        $this->info('Event Processing Failures');
        $this->newLine();

        $failures = DB::table('event_processing_failures')
            ->where('is_resolved', FALSE)
            ->orderBy('failed_at', 'desc')
            ->limit(20)
            ->get();

        if ($failures->isEmpty()) {
            $this->info('No unresolved processing failures found.');

            return;
        }

        $headers = ['Event ID', 'Handler', 'Error', 'Failed At', 'Retry Count'];
        $rows = [];

        foreach ($failures as $failure) {
            $eventIdStr = (string) $failure->event_id;
            $errorMessageStr = (string) $failure->error_message;
            $rows[] = [
                substr($eventIdStr, 0, 8) . '...',
                $failure->subscription_name,
                substr($errorMessageStr, 0, 50) . '...',
                $failure->failed_at,
                $failure->retry_count,
            ];
        }

        $this->table($headers, $rows);

        // Summary
        $totalFailures = DB::table('event_processing_failures')
            ->where('is_resolved', FALSE)
            ->count();

        $failuresByType = DB::table('event_processing_failures')
            ->select('error_type', DB::raw('count(*) as count'))
            ->where('is_resolved', FALSE)
            ->groupBy('error_type')
            ->orderBy('count', 'desc')
            ->get();

        $this->newLine();
        $this->line("Total Unresolved Failures: {$totalFailures}");

        if (! $failuresByType->isEmpty()) {
            $this->newLine();
            $this->line('Failures by Type:');
            foreach ($failuresByType as $row) {
                $errorType = class_basename($row->error_type);
                $this->line("  {$errorType}: {$row->count}");
            }
        }
    }

    /**
     * Show projection status information.
     */
    /**
     * ShowProjectionStatus
     */
    private function showProjectionStatus(): void
    {
        $this->info('Projection Status');
        $this->newLine();

        $projections = $this->projectionManager->getProjections();

        if (empty($projections)) {
            $this->info('No projections registered.');

            return;
        }

        $headers = ['Projection', 'Position', 'Last Updated', 'Locked', 'State'];
        $rows = [];

        foreach ($projections as $projectionName) {
            $status = $this->projectionManager->getProjectionStatus($projectionName);
            $state = $status['state'];
            $stateInfo = isset($state['total_tickets']) ?
                "Tickets: {$state['total_tickets']}" :
                json_encode($state);

            $stateInfoStr = (string) $stateInfo;
            $maxLength = min(strlen($stateInfoStr), 30);

            $rows[] = [
                $projectionName,
                $status['position'],
                $status['last_updated_at'] ?? 'Never',
                $status['is_locked'] ? 'Yes' : 'No',
                substr($stateInfoStr, 0, $maxLength) . ($maxLength < strlen($stateInfoStr) ? '...' : ''),
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Show event store overview.
     */
    /**
     * ShowOverview
     */
    private function showOverview(): void
    {
        $this->info('Event Store Overview');
        $this->newLine();

        // Basic metrics
        $totalEvents = DB::table('event_store')->count();
        $totalProjections = count($this->projectionManager->getProjections());
        $totalFailures = DB::table('event_processing_failures')
            ->where('is_resolved', FALSE)
            ->count();

        $this->line("Total Events: {$totalEvents}");
        $this->line("Active Projections: {$totalProjections}");
        $this->line("Unresolved Failures: {$totalFailures}");

        // Last event
        $lastEvent = DB::table('event_store')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEvent) {
            $this->newLine();
            $this->line('Last Event:');
            $eventType = $this->getEventTypeName($lastEvent->event_type);
            $this->line("  Type: {$eventType}");
            $this->line("  Aggregate: {$lastEvent->aggregate_type}");
            $this->line("  Recorded: {$lastEvent->recorded_at}");
        }

        $this->newLine();
        $this->line('Use --stats, --failures, or --projections for detailed information.');
        $this->line('Use --watch to monitor events in real-time.');
    }

    /**
     * Get the last event ID from the event store.
     */
    /**
     * Get  last event id
     */
    private function getLastEventId(): int
    {
        // Use event store service to ensure it's not marked as unused
        try {
            return $this->eventStore->getLastEventId();
        } catch (Exception) {
            // Fallback to direct DB query if event store service fails
            return DB::table('event_store')->max('id') ?? 0;
        }
    }

    /**
     * Get simplified event type name.
     */
    /**
     * Get  event type name
     */
    private function getEventTypeName(string $eventType): string
    {
        return class_basename($eventType);
    }

    /**
     * Check if monitoring should stop.
     */
    /**
     * ShouldStop
     */
    private function shouldStop(): bool
    {
        // Simple check for keyboard interruption or other stop conditions
        // In a real implementation, this could check for signals or other conditions
        return FALSE;
    }
}
