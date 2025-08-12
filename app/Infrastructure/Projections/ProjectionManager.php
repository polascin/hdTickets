<?php declare(strict_types=1);

namespace App\Infrastructure\Projections;

use App\Domain\Shared\Events\DomainEventInterface;
use App\Infrastructure\EventStore\EventStoreInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function get_class;

class ProjectionManager implements ProjectionManagerInterface
{
    private array $projections = [];

    public function __construct(
        private readonly EventStoreInterface $eventStore,
    ) {
    }

    public function register(ProjectionInterface $projection): void
    {
        $this->projections[$projection->getName()] = $projection;

        // Initialize projection tracking
        $this->initializeProjectionTracking($projection->getName());
    }

    public function project(DomainEventInterface $event): void
    {
        foreach ($this->projections as $projection) {
            if ($projection->handles($event->getEventType())) {
                try {
                    $this->projectEvent($projection, $event);
                } catch (Exception $e) {
                    Log::error('Projection failed', [
                        'projection' => $projection->getName(),
                        'event_type' => $event->getEventType(),
                        'event_id'   => $event->getEventId(),
                        'error'      => $e->getMessage(),
                    ]);

                    // Record the failure for potential retry
                    $this->recordProjectionFailure($projection->getName(), $event, $e);
                }
            }
        }
    }

    public function rebuildAll(int $fromPosition = 0): void
    {
        Log::info('Starting rebuild of all projections', ['from_position' => $fromPosition]);

        foreach ($this->projections as $projection) {
            $this->rebuild($projection->getName(), $fromPosition);
        }

        Log::info('Completed rebuild of all projections');
    }

    public function rebuild(string $projectionName, int $fromPosition = 0): void
    {
        if (! isset($this->projections[$projectionName])) {
            throw new Exception("Projection '{$projectionName}' not found");
        }

        $projection = $this->projections[$projectionName];

        Log::info("Starting rebuild of projection '{$projectionName}'", ['from_position' => $fromPosition]);

        try {
            // Lock the projection
            if (! $this->lockProjection($projectionName, 'rebuild-' . getmypid())) {
                throw new Exception("Could not acquire lock for projection '{$projectionName}'");
            }

            // Reset the projection
            $projection->reset();

            // Process all events
            $batchSize = 1000;
            $position = $fromPosition;
            $processedCount = 0;

            do {
                $events = $this->eventStore->loadAllEvents($position, $batchSize);

                foreach ($events as $event) {
                    if ($projection->handles($event->getEventType())) {
                        $projection->project($event);
                        $processedCount++;
                    }
                    $position++;
                }

                // Update projection position
                $this->updateProjectionPosition($projectionName, $position - 1);

                Log::debug("Processed batch for projection '{$projectionName}'", [
                    'batch_size'       => $events->count(),
                    'total_processed'  => $processedCount,
                    'current_position' => $position,
                ]);
            } while ($events->count() === $batchSize);

            Log::info("Completed rebuild of projection '{$projectionName}'", [
                'events_processed' => $processedCount,
                'final_position'   => $position,
            ]);
        } finally {
            // Always unlock the projection
            $this->unlockProjection($projectionName);
        }
    }

    public function getProjectionStatus(string $projectionName): array
    {
        if (! isset($this->projections[$projectionName])) {
            throw new Exception("Projection '{$projectionName}' not found");
        }

        $projection = $this->projections[$projectionName];

        $dbStatus = DB::table('event_projections')
            ->where('projection_name', $projectionName)
            ->first();

        return [
            'name'                    => $projectionName,
            'position'                => $dbStatus->position ?? 0,
            'last_processed_event_id' => $dbStatus->last_processed_event_id ?? NULL,
            'last_updated_at'         => $dbStatus->last_updated_at ?? NULL,
            'is_locked'               => $dbStatus->is_locked ?? FALSE,
            'locked_by'               => $dbStatus->locked_by ?? NULL,
            'locked_at'               => $dbStatus->locked_at ?? NULL,
            'handled_event_types'     => $projection->getHandledEventTypes(),
            'state'                   => $projection->getState(),
        ];
    }

    public function lockProjection(string $projectionName, string $lockedBy): bool
    {
        $updated = DB::table('event_projections')
            ->where('projection_name', $projectionName)
            ->where('is_locked', FALSE)
            ->update([
                'is_locked' => TRUE,
                'locked_by' => $lockedBy,
                'locked_at' => now(),
            ]);

        return $updated > 0;
    }

    public function unlockProjection(string $projectionName): void
    {
        DB::table('event_projections')
            ->where('projection_name', $projectionName)
            ->update([
                'is_locked' => FALSE,
                'locked_by' => NULL,
                'locked_at' => NULL,
            ]);
    }

    public function getProjections(): array
    {
        return array_keys($this->projections);
    }

    private function initializeProjectionTracking(string $projectionName): void
    {
        try {
            DB::table('event_projections')->updateOrInsert(
                ['projection_name' => $projectionName],
                [
                    'position'                => 0,
                    'last_processed_event_id' => NULL,
                    'last_updated_at'         => NULL,
                    'is_locked'               => FALSE,
                    'locked_by'               => NULL,
                    'locked_at'               => NULL,
                    'state'                   => json_encode([]),
                ],
            );
        } catch (Exception $e) {
            // Log the error but don't fail during service registration
            Log::warning("Could not initialize projection tracking for '{$projectionName}': " . $e->getMessage());
        }
    }

    private function projectEvent(ProjectionInterface $projection, DomainEventInterface $event): void
    {
        DB::transaction(function () use ($projection, $event): void {
            // Apply the projection
            $projection->project($event);

            // Update projection tracking
            $this->updateProjectionTracking($projection->getName(), $event);
        });
    }

    private function updateProjectionTracking(string $projectionName, DomainEventInterface $event): void
    {
        DB::table('event_projections')
            ->where('projection_name', $projectionName)
            ->update([
                'last_processed_event_id' => $event->getEventId(),
                'position'                => DB::raw('position + 1'),
                'last_updated_at'         => now(),
            ]);
    }

    private function updateProjectionPosition(string $projectionName, int $position): void
    {
        DB::table('event_projections')
            ->where('projection_name', $projectionName)
            ->update([
                'position'        => $position,
                'last_updated_at' => now(),
            ]);
    }

    private function recordProjectionFailure(string $projectionName, DomainEventInterface $event, Exception $exception): void
    {
        DB::table('event_processing_failures')->insert([
            'event_id'          => $event->getEventId(),
            'subscription_name' => $projectionName,
            'handler_class'     => get_class($this->projections[$projectionName]),
            'error_type'        => get_class($exception),
            'error_message'     => $exception->getMessage(),
            'error_context'     => json_encode([
                'event_type'   => $event->getEventType(),
                'aggregate_id' => $event->getAggregateRootId(),
                'trace'        => $exception->getTraceAsString(),
            ]),
            'event_payload' => json_encode($event->getPayload()),
            'retry_count'   => 0,
            'failed_at'     => now(),
            'retry_after'   => now()->addMinutes(5), // Retry after 5 minutes
            'is_resolved'   => FALSE,
        ]);
    }
}
