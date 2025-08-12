<?php declare(strict_types=1);

namespace App\Infrastructure\EventBus;

use App\Domain\Shared\Events\DomainEventInterface;

interface EventBusInterface
{
    /**
     * Dispatch a single domain event
     */
    public function dispatch(DomainEventInterface $event): void;

    /**
     * Dispatch multiple domain events
     */
    public function dispatchMany(array $events): void;

    /**
     * Register an event handler
     */
    public function subscribe(string $eventType, callable $handler): void;

    /**
     * Remove an event handler
     */
    public function unsubscribe(string $eventType, callable $handler): void;

    /**
     * Get all registered handlers for an event type
     */
    public function getHandlers(string $eventType): array;

    /**
     * Check if there are handlers for an event type
     */
    public function hasHandlers(string $eventType): bool;
}
