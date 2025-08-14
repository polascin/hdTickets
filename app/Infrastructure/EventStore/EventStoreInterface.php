<?php declare(strict_types=1);

namespace App\Infrastructure\EventStore;

use App\Domain\Shared\Events\DomainEventInterface;
use Illuminate\Support\Collection;

interface EventStoreInterface
{
    /**
     * Store a single domain event
     */
    public function store(DomainEventInterface $event, ?int $expectedVersion = NULL): void;

    /**
     * Store multiple domain events atomically
     */
    public function storeMany(array $events, string $aggregateId, ?int $expectedVersion = NULL): void;

    /**
     * Load events for a specific aggregate
     */
    public function loadEvents(string $aggregateId, string $aggregateType, int $fromVersion = 0): Collection;

    /**
     * Load events by type
     */
    public function loadEventsByType(string $eventType, int $limit = 1000, ?string $fromEventId = NULL): Collection;

    /**
     * Load all events from a position
     */
    public function loadAllEvents(int $fromPosition = 0, int $limit = 1000): Collection;

    /**
     * Get the current version for an aggregate
     */
    public function getCurrentVersion(string $aggregateId, string $aggregateType): int;

    /**
     * Create a snapshot for an aggregate
     */
    public function createSnapshot(string $aggregateId, string $aggregateType, int $version, array $data): void;

    /**
     * Load the latest snapshot for an aggregate
     */
    public function loadSnapshot(string $aggregateId, string $aggregateType): ?array;

    /**
     * Check if an event exists
     */
    public function eventExists(string $eventId): bool;

    /**
     * Get event by ID
     */
    public function getEventById(string $eventId): ?DomainEventInterface;

    /**
     * Get events count for an aggregate
     */
    public function getEventsCount(string $aggregateId, string $aggregateType): int;

    /**
     * Replay events from a specific point
     */
    public function replayEvents(int $fromPosition = 0, ?callable $handler = NULL): void;
}
