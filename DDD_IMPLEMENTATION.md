# Domain-Driven Design Implementation

## Overview
This document describes the Domain-Driven Design (DDD) implementation for the HD Tickets sports events entry tickets monitoring, scraping and purchase system.

## Architecture Structure

```
app/
├── Domain/                          # Domain Layer (Business Logic)
│   ├── Event/                       # Event Management Bounded Context
│   │   ├── Entities/                # Domain Entities
│   │   │   └── SportsEvent.php
│   │   ├── ValueObjects/            # Value Objects
│   │   │   ├── EventId.php
│   │   │   ├── EventDate.php
│   │   │   ├── Venue.php
│   │   │   └── SportCategory.php
│   │   ├── Aggregates/              # Aggregates
│   │   │   └── EventSchedule.php
│   │   ├── Events/                  # Domain Events
│   │   │   ├── SportEventCreated.php
│   │   │   ├── SportEventUpdated.php
│   │   │   └── SportEventMarkedAsHighDemand.php
│   │   ├── Repositories/            # Repository Interfaces
│   │   │   ├── SportsEventRepositoryInterface.php
│   │   │   └── EventScheduleRepositoryInterface.php
│   │   └── Services/                # Domain Services
│   │       └── EventManagementService.php
│   ├── Ticket/                      # Ticket Monitoring Bounded Context
│   ├── Purchase/                    # Purchase Management Bounded Context
│   ├── User/                        # User Management Bounded Context
│   ├── Notification/                # Notification Bounded Context
│   └── Analytics/                   # Analytics Bounded Context
├── Application/                     # Application Layer (Use Cases)
│   ├── Commands/                    # Commands and Command Handlers
│   │   ├── CreateSportsEventCommand.php
│   │   └── CreateSportsEventCommandHandler.php
│   ├── Queries/                     # Queries and Query Handlers
│   │   ├── GetUpcomingEventsQuery.php
│   │   └── GetUpcomingEventsQueryHandler.php
│   └── Services/                    # Application Services
├── Infrastructure/                  # Infrastructure Layer (Technical Concerns)
│   ├── Persistence/                 # Data Persistence
│   │   └── EloquentSportsEventRepository.php
│   ├── External/                    # External Service Integration
│   │   └── TicketmasterAntiCorruptionLayer.php
│   └── Security/                    # Security Infrastructure
└── Presentation/                    # Presentation Layer (User Interface)
    ├── Http/                        # HTTP Controllers
    ├── Console/                     # Console Commands
    └── Api/                         # API Controllers
```

## Bounded Contexts

### 1. Event Management Context
**Responsibility**: Managing sports events, venues, and schedules.

**Key Components**:
- `SportsEvent` Entity: Core event information with business rules
- `EventSchedule` Aggregate: Manages collections of events for specific dates
- `EventManagementService`: Handles complex event-related business logic

**Domain Events**:
- `SportEventCreated`
- `SportEventUpdated` 
- `SportEventMarkedAsHighDemand`

### 2. Ticket Monitoring Context
**Responsibility**: Tracking ticket availability, prices, and platform sources.

**Key Components**:
- `MonitoredTicket` Entity: Represents a ticket being tracked
- `Price` Value Object: Encapsulates price with currency validation
- `AvailabilityStatus` Value Object: Ticket availability states
- `PlatformSource` Value Object: Platform information with validation

**Domain Events**:
- `TicketPriceChanged`
- `TicketAvailabilityChanged`

### 3. Purchase Management Context
**Responsibility**: Managing purchase decisions, queues, and automated buying.

**Key Components**:
- `PurchaseId` Value Object
- `PurchaseStatus` Value Object with business rules for status transitions

### 4. User Management Context
**Responsibility**: User authentication, roles, permissions, and preferences.

### 5. Notification Context
**Responsibility**: Multi-channel notifications (email, SMS, webhooks, etc.).

### 6. Analytics Context
**Responsibility**: Reporting, insights, and performance metrics.

### 7. Platform Integration Context
**Responsibility**: External API integrations with anti-corruption layers.

## Key DDD Patterns Implemented

### Value Objects
Value objects encapsulate business rules and ensure data integrity:

```php
// Price value object with currency validation
$price = new Price(50.00, 'GBP');
echo $price->formatted(); // £50.00

// Availability status with business rules
$status = new AvailabilityStatus('AVAILABLE');
if ($status->canPurchase()) {
    // Handle purchase logic
}
```

### Entities
Entities represent business objects with identity:

```php
$event = new SportsEvent(
    new EventId('event_123'),
    'Arsenal vs Chelsea',
    new SportCategory('FOOTBALL'),
    new EventDate(new DateTimeImmutable('2024-03-15 15:00:00')),
    new Venue('Emirates Stadium', 'London', 'UK')
);

$event->markAsHighDemand(); // Business logic with domain events
```

### Aggregates
Aggregates maintain consistency boundaries:

```php
$schedule = new EventSchedule(new DateTimeImmutable('2024-03-15'));
$schedule->addEvent($event); // Validates business rules
$conflicts = $schedule->getConflictingEvents();
```

### Domain Events
Domain events capture important business occurrences:

```php
// Events are automatically recorded and can be processed
$events = $sportsEvent->getDomainEvents();
foreach ($events as $event) {
    // Process domain event (e.g., send notifications, update analytics)
}
```

### Repository Pattern
Repositories provide domain-oriented data access:

```php
interface SportsEventRepositoryInterface
{
    public function save(SportsEvent $event): void;
    public function findById(EventId $id): ?SportsEvent;
    public function findByCategory(SportCategory $category): array;
    public function findHighDemandEvents(): array;
}
```

### Anti-Corruption Layer
Protects domain model from external system complexity:

```php
class TicketmasterAntiCorruptionLayer
{
    public function adaptEventData(array $ticketmasterData): array
    {
        // Convert external format to domain objects
        return $this->toDomainFormat($ticketmasterData);
    }
}
```

## CQRS Implementation

### Commands
Commands represent write operations:

```php
$command = new CreateSportsEventCommand(
    name: 'Arsenal vs Chelsea',
    category: 'FOOTBALL',
    eventDate: new DateTimeImmutable('2024-03-15 15:00:00'),
    venueName: 'Emirates Stadium',
    venueCity: 'London',
    venueCountry: 'UK'
);

$handler->handle($command);
```

### Queries
Queries represent read operations:

```php
$query = new GetUpcomingEventsQuery(
    limit: 50,
    category: 'FOOTBALL',
    highDemandOnly: true
);

$events = $queryHandler->handle($query);
```

## Event Sourcing (Planned)

Event sourcing will be implemented for critical operations:

- Purchase decisions and transactions
- Price change history
- Availability tracking over time
- User preference changes

## Service Registration

The `DomainDrivenDesignServiceProvider` wires up all components:

```php
// In config/app.php
'providers' => [
    // ... other providers
    App\Providers\DomainDrivenDesignServiceProvider::class,
],
```

## Usage Examples

### Creating a Sports Event

```php
// Through Application Service
$commandHandler = app(CreateSportsEventCommandHandler::class);
$commandHandler->handle(new CreateSportsEventCommand(
    'Liverpool vs Manchester City',
    'FOOTBALL',
    new DateTimeImmutable('2024-04-20 16:30:00'),
    'Anfield',
    'Liverpool',
    'UK'
));
```

### Querying Events

```php
$queryHandler = app(GetUpcomingEventsQueryHandler::class);
$events = $queryHandler->handle(new GetUpcomingEventsQuery(
    category: 'FOOTBALL',
    highDemandOnly: true,
    limit: 10
));
```

### Monitoring Tickets

```php
$ticket = new MonitoredTicket(
    new TicketId('ticket_123'),
    new EventId('event_456'),
    'Lower Tier',
    'Row 5',
    'Seat 12',
    new Price(75.00, 'GBP'),
    new AvailabilityStatus('AVAILABLE'),
    new PlatformSource('TICKETMASTER', 'https://...')
);

// Price changes trigger domain events
$ticket->updatePrice(new Price(85.00, 'GBP'));
```

## Benefits of This DDD Implementation

1. **Clear Business Logic**: Domain logic is explicitly modeled and testable
2. **Separation of Concerns**: Each layer has distinct responsibilities
3. **Flexibility**: Easy to adapt to changing business requirements
4. **Maintainability**: Well-organized code with clear boundaries
5. **Testability**: Domain logic can be tested in isolation
6. **Integration Safety**: Anti-corruption layers protect from external changes
7. **Event-Driven Architecture**: Domain events enable loose coupling
8. **Scalability**: Clear boundaries support microservices evolution

## Future Enhancements

1. **Complete Event Sourcing**: Implement full event store for audit trails
2. **Additional Bounded Contexts**: Expand User, Notification, and Analytics contexts
3. **Saga Pattern**: For complex cross-context transactions
4. **Domain Event Bus**: More sophisticated event handling
5. **Read Models**: Optimized query models for reporting
6. **Integration Events**: For inter-service communication

## Testing Strategy

- **Unit Tests**: For value objects, entities, and domain services
- **Integration Tests**: For repositories and external service adapters
- **Domain Event Tests**: Verify events are raised correctly
- **Command/Query Handler Tests**: End-to-end use case testing

This DDD implementation provides a solid foundation for the sports events ticket monitoring system while maintaining flexibility for future growth and changes.
