<?php declare(strict_types=1);

namespace App\Infrastructure\Projections;

use App\Domain\Shared\Events\DomainEventInterface;

interface ProjectionInterface
{
    /**
     * Get the projection name
     */
    public function getName(): string;

    /**
     * Get the event types this projection handles
     */
    public function getHandledEventTypes(): array;

    /**
     * Check if this projection handles the given event type
     */
    public function handles(string $eventType): bool;

    /**
     * Project an event onto the read model
     */
    public function project(DomainEventInterface $event): void;

    /**
     * Reset the projection (clear all data)
     */
    public function reset(): void;

    /**
     * Get the current projection state
     */
    public function getState(): array;
}
