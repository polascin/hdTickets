<?php declare(strict_types=1);

namespace App\Infrastructure\Projections;

use App\Domain\Shared\Events\DomainEventInterface;

interface ProjectionManagerInterface
{
    /**
     * Register a projection
     */
    public function register(ProjectionInterface $projection): void;

    /**
     * Process a single event against all projections
     */
    public function project(DomainEventInterface $event): void;

    /**
     * Rebuild all projections from the event store
     */
    public function rebuildAll(int $fromPosition = 0): void;

    /**
     * Rebuild a specific projection
     */
    public function rebuild(string $projectionName, int $fromPosition = 0): void;

    /**
     * Get projection status
     */
    public function getProjectionStatus(string $projectionName): array;

    /**
     * Lock a projection for exclusive processing
     */
    public function lockProjection(string $projectionName, string $lockedBy): bool;

    /**
     * Unlock a projection
     */
    public function unlockProjection(string $projectionName): void;

    /**
     * Get all registered projections
     */
    public function getProjections(): array;
}
