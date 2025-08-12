# Event-Driven Architecture Documentation

## Overview

The HD Tickets application now implements a comprehensive event-driven architecture for loose coupling and scalability. This document describes the implementation, usage patterns, and management tools for the event system.

## Architecture Components

### 1. Event Store

The event store is the heart of the system, providing durable storage for all domain events.

**Key Features:**
- PostgreSQL-based storage for reliability and ACID compliance
- Event versioning and metadata support
- Optimistic concurrency control
- Event replay capabilities
- Snapshot support for performance optimization

**Database Tables:**
- `event_store` - Main event storage
- `event_streams` - Event stream management
- `event_snapshots` - Performance snapshots
- `event_projections` - Projection tracking
- `event_subscriptions` - Event subscriptions
- `event_processing_failures` - Failure tracking

### 2. Domain Events

All domain events implement the `DomainEventInterface` and extend `AbstractDomainEvent`.

**Implemented Events:**

#### Ticket Events
- `TicketDiscovered` - When a new ticket is found during scraping
- `TicketPriceChanged` - When ticket prices are updated
- `TicketAvailabilityChanged` - When ticket availability changes
- `TicketSoldOut` - When tickets are completely sold out

#### Purchase Events
- `PurchaseInitiated` - When a purchase process starts
- `PurchaseCompleted` - When a purchase is successfully completed
- `PurchaseFailed` - When a purchase fails
- `PaymentProcessed` - When payment processing occurs

#### Monitoring Events
- `MonitoringStarted` - When monitoring begins
- `MonitoringStopped` - When monitoring stops
- `AlertTriggered` - When alert conditions are met

#### System Events
- `ScrapingJobStarted` - When scraping jobs begin
- `ScrapingJobCompleted` - When scraping jobs finish
- `PlatformStatusChanged` - When platform status changes
- `ErrorOccurred` - When system errors occur

### 3. Event Bus

The event bus manages event dispatching and subscription.

**Features:**
- Synchronous and asynchronous processing
- Retry mechanisms with exponential backoff
- Handler failure recording
- Integration with Laravel's event system

**Usage:**
```php
use App\Infrastructure\EventBus\EventBusInterface;

// Dispatch single event
$eventBus->dispatch($event);

// Dispatch multiple events atomically
$eventBus->dispatchMany([$event1, $event2, $event3]);

// Async dispatch
$eventBus->dispatchAsync($event);

// Dispatch with retry
$eventBus->dispatchWithRetry($event, 3);
```

### 4. Projections (CQRS Read Models)

Projections build read-optimized views from events.

**Available Projections:**
- `TicketReadModelProjection` - Optimized ticket data for queries
- `PurchaseReadModelProjection` - Purchase analytics and reporting
- `MonitoringReadModelProjection` - Monitoring metrics and status

**Read Model Tables:**
- `ticket_read_models` - Denormalized ticket data
- `purchase_read_models` - Purchase transaction data
- `monitoring_read_models` - Monitoring statistics

### 5. Event Handlers

Event handlers process domain events to trigger side effects.

**Example Handler:**
```php
class TicketDiscoveredHandler
{
    public function handle(TicketDiscovered $event): void
    {
        // Update read model
        $this->updateTicketReadModel($event);
        
        // Update cache
        $this->updateTicketCache($event);
        
        // Check alert conditions
        $this->checkAlertConditions($event);
        
        // Update statistics
        $this->updatePlatformStats($event);
    }
}
```

## Usage Examples

### Dispatching Events

```php
use App\Domain\Ticket\Events\TicketDiscovered;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\PlatformSource;
use App\Infrastructure\EventBus\EventBusInterface;

// Create and dispatch a ticket discovered event
$event = new TicketDiscovered(
    ticketId: new TicketId('ticket-123'),
    eventName: 'Manchester United vs Liverpool',
    eventCategory: 'Football',
    venue: 'Old Trafford',
    eventDate: new DateTimeImmutable('2025-03-15 15:00:00'),
    price: new Price(85.00, 'GBP'),
    platformSource: new PlatformSource('ticketmaster'),
    availableQuantity: 2500,
    ticketDetails: ['section' => 'North Stand', 'row' => 'K']
);

$eventBus->dispatch($event);
```

### Creating Custom Events

```php
use App\Domain\Shared\Events\AbstractDomainEvent;

class CustomEvent extends AbstractDomainEvent
{
    public function __construct(
        public string $aggregateId,
        public array $data,
        array $metadata = []
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->aggregateId;
    }

    public function getAggregateType(): string
    {
        return 'custom';
    }

    public function getPayload(): array
    {
        return [
            'aggregate_id' => $this->aggregateId,
            'data' => $this->data
        ];
    }

    protected function populateFromPayload(array $payload): void
    {
        $this->aggregateId = $payload['aggregate_id'];
        $this->data = $payload['data'];
    }
}
```

### Creating Custom Projections

```php
use App\Infrastructure\Projections\ProjectionInterface;

class CustomProjection implements ProjectionInterface
{
    public function getName(): string
    {
        return 'custom_projection';
    }

    public function getHandledEventTypes(): array
    {
        return [CustomEvent::class];
    }

    public function handles(string $eventType): bool
    {
        return $eventType === CustomEvent::class;
    }

    public function project(DomainEventInterface $event): void
    {
        // Update your read model based on the event
        DB::table('custom_read_model')->insert([
            'aggregate_id' => $event->getAggregateRootId(),
            'data' => json_encode($event->getPayload()),
            'processed_at' => now()
        ]);
    }

    public function reset(): void
    {
        DB::table('custom_read_model')->truncate();
    }

    public function getState(): array
    {
        return [
            'total_records' => DB::table('custom_read_model')->count(),
            'last_updated' => DB::table('custom_read_model')->max('processed_at')
        ];
    }
}
```

## Console Commands

### Event Replay
Rebuild projections from stored events:

```bash
# Replay all events to rebuild all projections
php artisan events:replay

# Replay from a specific position
php artisan events:replay --from=1000

# Rebuild a specific projection
php artisan events:replay --projection=ticket_read_model

# Dry run to see what would be rebuilt
php artisan events:replay --dry-run
```

### Event Monitoring
Monitor event store health and statistics:

```bash
# Show overview
php artisan events:monitor

# Show detailed statistics
php artisan events:monitor --stats

# Show processing failures
php artisan events:monitor --failures

# Show projection status
php artisan events:monitor --projections

# Watch events in real-time
php artisan events:monitor --watch
```

## API Endpoints

### Event Monitoring Dashboard

The system provides REST API endpoints for monitoring:

- `GET /api/events/overview` - System overview
- `GET /api/events/statistics` - Detailed statistics
- `GET /api/events/recent` - Recent events
- `GET /api/events/projections` - Projection status
- `GET /api/events/failures` - Processing failures
- `POST /api/events/projections/{name}/rebuild` - Rebuild projection
- `POST /api/events/failures/{id}/resolve` - Resolve failure

### Example API Response

```json
{
    "success": true,
    "data": {
        "total_events": 15420,
        "events_today": 342,
        "active_projections": 3,
        "failed_processing": 2,
        "event_types": [
            {"type": "TicketDiscovered", "count": 8901},
            {"type": "TicketPriceChanged", "count": 4523},
            {"type": "PurchaseCompleted", "count": 1896}
        ]
    }
}
```

## Database Migration

Run the migration to set up event store infrastructure:

```bash
php artisan migrate
```

This creates all necessary tables for the event-driven architecture.

## Performance Considerations

### Event Store Optimization
- Events are stored with proper indexing for fast retrieval
- Snapshots can be created for performance-critical aggregates
- Event replay can be done in batches to manage memory usage

### Projection Performance
- Projections are updated asynchronously where possible
- Failed projections are tracked and can be retried
- Projections can be rebuilt from any point in the event stream

### Caching Strategy
- Event processing results are cached where appropriate
- Read models include versioning for cache invalidation
- Platform-specific caches are maintained for quick access

## Error Handling

### Processing Failures
- All event processing failures are recorded in `event_processing_failures`
- Automatic retry mechanisms with exponential backoff
- Failed events can be manually retried or marked as resolved

### Debugging Tools
- Comprehensive logging of all event processing
- Event payload inspection through monitoring API
- Projection state tracking and diagnostics

## Best Practices

### Event Design
1. Events should be immutable and contain all necessary data
2. Include sufficient context for event replay
3. Use meaningful event names that describe what happened
4. Include metadata for debugging and tracing

### Handler Implementation
1. Handlers should be idempotent (safe to run multiple times)
2. Handle failures gracefully with proper logging
3. Keep handlers focused on single responsibilities
4. Use dependency injection for testability

### Projection Design
1. Design read models for specific query patterns
2. Include denormalized data to avoid complex joins
3. Version your projections for schema evolution
4. Keep projection logic simple and focused

## Integration with Existing Code

The event-driven architecture integrates seamlessly with existing HD Tickets functionality:

1. **Scraping Services** - Now emit events when tickets are discovered
2. **Purchase System** - Events track the entire purchase lifecycle
3. **Monitoring** - Real-time events for alerts and notifications
4. **Analytics** - Events provide audit trail and business intelligence

## Monitoring and Maintenance

### Health Checks
- Monitor event processing lag
- Track projection rebuild times
- Alert on processing failures
- Monitor event store growth

### Regular Maintenance
- Archive old events based on retention policies
- Rebuild projections periodically for consistency
- Review and resolve processing failures
- Monitor disk usage for event storage

## Troubleshooting

### Common Issues

1. **Projection Lag**: Projections falling behind event stream
   - Check for processing failures
   - Consider rebuilding affected projections
   - Review handler performance

2. **Processing Failures**: Events failing to process
   - Check error logs and failure records
   - Verify handler dependencies
   - Consider manual retry or resolution

3. **Performance Issues**: Slow event processing
   - Review database indexes
   - Check for blocking operations in handlers
   - Consider async processing where appropriate

### Debug Commands

```bash
# Check event store statistics
php artisan events:monitor --stats

# View recent processing failures
php artisan events:monitor --failures

# Check projection status
php artisan events:monitor --projections

# Watch events in real-time
php artisan events:monitor --watch
```

This event-driven architecture provides a robust foundation for scalable, maintainable, and observable sports ticket monitoring system.
