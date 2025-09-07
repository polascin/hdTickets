<?php declare(strict_types=1);

namespace App\Infrastructure\EventBus;

use App\Domain\Shared\Events\DomainEventInterface;
use App\Infrastructure\EventStore\EventStoreInterface;
use Exception;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

use function count;
use function get_class;
use function is_string;

class LaravelEventBus implements EventBusInterface
{
    private array $handlers = [];

    public function __construct(
        private readonly Dispatcher $dispatcher,
        private readonly EventStoreInterface $eventStore,
    ) {
    }

    /**
     * Dispatch
     */
    public function dispatch(DomainEventInterface $event): void
    {
        try {
            // Store the event first
            $this->eventStore->store($event);

            // Then dispatch to Laravel's event system
            $this->dispatcher->dispatch($event->getEventType(), $event);

            // Also trigger any manually registered handlers
            $this->triggerHandlers($event);

            Log::debug('Domain event dispatched', [
                'event_type'   => $event->getEventType(),
                'event_id'     => $event->getEventId(),
                'aggregate_id' => $event->getAggregateRootId(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to dispatch domain event', [
                'event_type' => $event->getEventType(),
                'event_id'   => $event->getEventId(),
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * DispatchMany
     */
    public function dispatchMany(array $events): void
    {
        if (empty($events)) {
            return;
        }

        try {
            // Store all events first
            $aggregateId = $events[0]->getAggregateRootId();
            $this->eventStore->storeMany($events, $aggregateId);

            // Then dispatch each event
            foreach ($events as $event) {
                $this->dispatcher->dispatch($event->getEventType(), $event);
                $this->triggerHandlers($event);
            }

            Log::debug('Multiple domain events dispatched', [
                'event_count'  => count($events),
                'aggregate_id' => $aggregateId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to dispatch multiple domain events', [
                'event_count' => count($events),
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Subscribe
     */
    public function subscribe(string $eventType, callable $handler): void
    {
        if (!isset($this->handlers[$eventType])) {
            $this->handlers[$eventType] = [];
        }

        $this->handlers[$eventType][] = $handler;
    }

    /**
     * Unsubscribe
     */
    public function unsubscribe(string $eventType, callable $handler): void
    {
        if (!isset($this->handlers[$eventType])) {
            return;
        }

        $this->handlers[$eventType] = array_filter(
            $this->handlers[$eventType],
            fn ($h) => $h !== $handler,
        );

        if (empty($this->handlers[$eventType])) {
            unset($this->handlers[$eventType]);
        }
    }

    /**
     * Get  handlers
     */
    public function getHandlers(string $eventType): array
    {
        return $this->handlers[$eventType] ?? [];
    }

    /**
     * Check if has  handlers
     */
    public function hasHandlers(string $eventType): bool
    {
        return isset($this->handlers[$eventType]) && !empty($this->handlers[$eventType]);
    }

    /**
     * Queue event for async processing
     */
    /**
     * DispatchAsync
     */
    public function dispatchAsync(DomainEventInterface $event): void
    {
        // Store the event first
        $this->eventStore->store($event);

        // Queue for async processing
        Queue::push(function () use ($event): void {
            $this->dispatcher->dispatch($event->getEventType(), $event);
            $this->triggerHandlers($event);
        });
    }

    /**
     * Dispatch event with retry mechanism
     */
    /**
     * DispatchWithRetry
     */
    public function dispatchWithRetry(DomainEventInterface $event, int $maxRetries = 3): void
    {
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $this->dispatch($event);

                return;
            } catch (Exception $e) {
                $attempt++;

                if ($attempt >= $maxRetries) {
                    Log::error('Failed to dispatch event after retries', [
                        'event_id' => $event->getEventId(),
                        'attempts' => $attempt,
                        'error'    => $e->getMessage(),
                    ]);

                    throw $e;
                }

                // Exponential backoff
                usleep(pow(2, $attempt) * 100000); // 0.1s, 0.2s, 0.4s
            }
        }
    }

    /**
     * TriggerHandlers
     */
    private function triggerHandlers(DomainEventInterface $event): void
    {
        $handlers = $this->getHandlers($event->getEventType());

        foreach ($handlers as $handler) {
            try {
                $handler($event);
            } catch (Exception $e) {
                Log::error('Event handler failed', [
                    'event_type' => $event->getEventType(),
                    'event_id'   => $event->getEventId(),
                    'handler'    => is_string($handler) ? $handler : 'closure',
                    'error'      => $e->getMessage(),
                ]);

                // Don't re-throw to prevent one failed handler from affecting others
                // but record the failure for monitoring
                $this->recordHandlerFailure($event, $handler, $e);
            }
        }
    }

    /**
     * RecordHandlerFailure
     */
    private function recordHandlerFailure(DomainEventInterface $event, callable $handler, Exception $exception): void
    {
        // Record handler failure for monitoring and potential retry
        // This could be extended to support dead letter queues or compensation actions

        Log::warning('Recording handler failure for potential retry', [
            'event_id'   => $event->getEventId(),
            'event_type' => $event->getEventType(),
            'handler'    => is_string($handler) ? $handler : get_class($handler),
            'error'      => $exception->getMessage(),
            'trace'      => $exception->getTraceAsString(),
        ]);
    }
}
