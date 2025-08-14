<?php declare(strict_types=1);

namespace App\Infrastructure\EventStore;

use App\Domain\Shared\Events\DomainEventInterface;
use App\Infrastructure\EventStore\Exceptions\EventStoreException;
use App\Infrastructure\EventStore\Exceptions\OptimisticLockException;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PostgreSqlEventStore implements EventStoreInterface
{
    private array $eventMap = [];

    public function __construct()
    {
        $this->buildEventMap();
    }

    /**
     * Store
     */
    public function store(DomainEventInterface $event, ?int $expectedVersion = NULL): void
    {
        DB::transaction(function () use ($event, $expectedVersion): void {
            $this->storeEvent($event, $expectedVersion);
            $this->updateStreamVersion($event);
        });
    }

    /**
     * StoreMany
     */
    public function storeMany(array $events, string $aggregateId, ?int $expectedVersion = NULL): void
    {
        if (empty($events)) {
            return;
        }

        DB::transaction(function () use ($events, $aggregateId, $expectedVersion): void {
            $currentVersion = $this->getCurrentVersion($aggregateId, $events[0]->getAggregateType());

            if ($expectedVersion !== NULL && $currentVersion !== $expectedVersion) {
                throw new OptimisticLockException(
                    "Expected version {$expectedVersion} but current version is {$currentVersion}",
                );
            }

            foreach ($events as $index => $event) {
                $this->storeEvent($event, $currentVersion + $index + 1);
                $this->updateStreamVersion($event);
            }
        });
    }

    /**
     * LoadEvents
     */
    public function loadEvents(string $aggregateId, string $aggregateType, int $fromVersion = 0): Collection
    {
        $rows = DB::table('event_store')
            ->where('aggregate_root_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->where('aggregate_version', '>', $fromVersion)
            ->orderBy('aggregate_version')
            ->get();

        return $rows->map(function ($row) {
            return $this->deserializeEvent($row);
        });
    }

    /**
     * LoadEventsByType
     */
    public function loadEventsByType(string $eventType, int $limit = 1000, ?string $fromEventId = NULL): Collection
    {
        $query = DB::table('event_store')
            ->where('event_type', $eventType)
            ->orderBy('recorded_at')
            ->limit($limit);

        if ($fromEventId) {
            $query->where('event_id', '>', $fromEventId);
        }

        $rows = $query->get();

        return $rows->map(function ($row) {
            return $this->deserializeEvent($row);
        });
    }

    /**
     * LoadAllEvents
     */
    public function loadAllEvents(int $fromPosition = 0, int $limit = 1000): Collection
    {
        $rows = DB::table('event_store')
            ->where('id', '>', $fromPosition)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        return $rows->map(function ($row) {
            return $this->deserializeEvent($row);
        });
    }

    /**
     * Get  current version
     */
    public function getCurrentVersion(string $aggregateId, string $aggregateType): int
    {
        return DB::table('event_store')
            ->where('aggregate_root_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->max('aggregate_version') ?? 0;
    }

    /**
     * CreateSnapshot
     */
    public function createSnapshot(string $aggregateId, string $aggregateType, int $version, array $data): void
    {
        DB::table('event_snapshots')->updateOrInsert(
            [
                'aggregate_root_id' => $aggregateId,
                'aggregate_type'    => $aggregateType,
                'aggregate_version' => $version,
            ],
            [
                'aggregate_data' => json_encode($data),
                'created_at'     => now(),
            ],
        );
    }

    /**
     * LoadSnapshot
     */
    public function loadSnapshot(string $aggregateId, string $aggregateType): ?array
    {
        $snapshot = DB::table('event_snapshots')
            ->where('aggregate_root_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->orderBy('aggregate_version', 'desc')
            ->first();

        if (! $snapshot) {
            return NULL;
        }

        return [
            'version'    => $snapshot->aggregate_version,
            'data'       => json_decode($snapshot->aggregate_data, TRUE),
            'created_at' => $snapshot->created_at,
        ];
    }

    /**
     * EventExists
     */
    public function eventExists(string $eventId): bool
    {
        return DB::table('event_store')
            ->where('event_id', $eventId)
            ->exists();
    }

    /**
     * Get  event by id
     */
    public function getEventById(string $eventId): ?DomainEventInterface
    {
        $row = DB::table('event_store')
            ->where('event_id', $eventId)
            ->first();

        return $row ? $this->deserializeEvent($row) : NULL;
    }

    /**
     * Get  events count
     */
    public function getEventsCount(string $aggregateId, string $aggregateType): int
    {
        return DB::table('event_store')
            ->where('aggregate_root_id', $aggregateId)
            ->where('aggregate_type', $aggregateType)
            ->count();
    }

    /**
     * ReplayEvents
     */
    public function replayEvents(int $fromPosition = 0, ?callable $handler = NULL): void
    {
        $limit = 1000;
        $offset = $fromPosition;

        do {
            $events = $this->loadAllEvents($offset, $limit);

            foreach ($events as $event) {
                try {
                    if ($handler) {
                        $handler($event);
                    } else {
                        // Default replay behavior - re-dispatch the event
                        event($event);
                    }
                    $offset++;
                } catch (Throwable $e) {
                    Log::error('Event replay failed', [
                        'event_id' => $event->getEventId(),
                        'error'    => $e->getMessage(),
                    ]);

                    throw $e;
                }
            }
        } while ($events->count() === $limit);
    }

    /**
     * BuildEventMap
     */
    private function buildEventMap(): void
    {
        // Map event class names to their full class paths for deserialization
        $this->eventMap = [
            'App\\Domain\\Ticket\\Events\\TicketDiscovered'          => \App\Domain\Ticket\Events\TicketDiscovered::class,
            'App\\Domain\\Ticket\\Events\\TicketPriceChanged'        => \App\Domain\Ticket\Events\TicketPriceChanged::class,
            'App\\Domain\\Ticket\\Events\\TicketAvailabilityChanged' => \App\Domain\Ticket\Events\TicketAvailabilityChanged::class,
            'App\\Domain\\Ticket\\Events\\TicketSoldOut'             => \App\Domain\Ticket\Events\TicketSoldOut::class,
            'App\\Domain\\Purchase\\Events\\PurchaseInitiated'       => \App\Domain\Purchase\Events\PurchaseInitiated::class,
            'App\\Domain\\Purchase\\Events\\PurchaseCompleted'       => \App\Domain\Purchase\Events\PurchaseCompleted::class,
            'App\\Domain\\Purchase\\Events\\PurchaseFailed'          => \App\Domain\Purchase\Events\PurchaseFailed::class,
            'App\\Domain\\Purchase\\Events\\PaymentProcessed'        => \App\Domain\Purchase\Events\PaymentProcessed::class,
            'App\\Domain\\Monitoring\\Events\\MonitoringStarted'     => \App\Domain\Monitoring\Events\MonitoringStarted::class,
            'App\\Domain\\Monitoring\\Events\\MonitoringStopped'     => \App\Domain\Monitoring\Events\MonitoringStopped::class,
            'App\\Domain\\Monitoring\\Events\\AlertTriggered'        => \App\Domain\Monitoring\Events\AlertTriggered::class,
            'App\\Domain\\System\\Events\\ScrapingJobStarted'        => \App\Domain\System\Events\ScrapingJobStarted::class,
        ];
    }

    /**
     * StoreEvent
     */
    private function storeEvent(DomainEventInterface $event, ?int $version = NULL): void
    {
        $version ??= $this->getCurrentVersion(
            $event->getAggregateRootId(),
            $event->getAggregateType(),
        ) + 1;

        try {
            DB::table('event_store')->insert([
                'event_id'          => $event->getEventId(),
                'event_type'        => $event->getEventType(),
                'aggregate_root_id' => $event->getAggregateRootId(),
                'aggregate_type'    => $event->getAggregateType(),
                'aggregate_version' => $version,
                'payload'           => json_encode($event->getPayload()),
                'metadata'          => json_encode($event->getMetadata()),
                'recorded_at'       => $event->getOccurredAt()->format('Y-m-d H:i:s.u'),
                'event_version'     => $event->getVersion(),
            ]);
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'duplicate')) {
                throw new OptimisticLockException('Concurrent modification detected');
            }

            throw new EventStoreException('Failed to store event: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * UpdateStreamVersion
     */
    private function updateStreamVersion(DomainEventInterface $event): void
    {
        $streamName = $event->getAggregateType() . '-' . $event->getAggregateRootId();

        DB::table('event_streams')->updateOrInsert(
            ['stream_name' => $streamName],
            [
                'stream_type'   => $event->getAggregateType(),
                'last_event_at' => now(),
                'version'       => DB::raw('version + 1'),
                'metadata'      => json_encode(['last_event_id' => $event->getEventId()]),
                'created_at'    => DB::raw('COALESCE(created_at, NOW())'),
            ],
        );
    }

    /**
     * DeserializeEvent
     *
     * @param mixed $row
     */
    private function deserializeEvent($row): DomainEventInterface
    {
        $eventClass = $this->eventMap[$row->event_type] ?? NULL;

        if (! $eventClass) {
            throw new EventStoreException("Unknown event type: {$row->event_type}");
        }

        if (! class_exists($eventClass)) {
            throw new EventStoreException("Event class does not exist: {$eventClass}");
        }

        $data = [
            'event_id'          => $row->event_id,
            'event_type'        => $row->event_type,
            'aggregate_root_id' => $row->aggregate_root_id,
            'aggregate_type'    => $row->aggregate_type,
            'occurred_at'       => $row->recorded_at,
            'version'           => $row->event_version,
            'payload'           => json_decode($row->payload, TRUE),
            'metadata'          => json_decode($row->metadata, TRUE) ?? [],
        ];

        return $eventClass::fromArray($data);
    }
}
