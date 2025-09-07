<?php declare(strict_types=1);

namespace App\Domain\Event\Services;

use App\Domain\Event\Entities\SportsEvent;
use App\Domain\Event\Events\SportEventCreated;
use App\Domain\Event\Repositories\SportsEventRepositoryInterface;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Event\ValueObjects\Venue;
use DateTimeImmutable;
use DomainException;

use function in_array;

class EventManagementService
{
    public function __construct(
        private SportsEventRepositoryInterface $eventRepository,
    ) {
    }

    /**
     * CreateSportsEvent
     */
    public function createSportsEvent(
        string $name,
        SportCategory $category,
        EventDate $eventDate,
        Venue $venue,
        ?string $homeTeam = NULL,
        ?string $awayTeam = NULL,
        ?string $competition = NULL,
    ): SportsEvent {
        $eventId = new EventId(uniqid('event_'));

        // Check for duplicate events
        $existingEvent = $this->eventRepository->findByName($name);
        if ($existingEvent !== NULL) {
            throw new DomainException('Event with this name already exists');
        }

        // Check for venue conflicts
        $conflictingEvents = $this->eventRepository->findConflictingEvents(
            $eventDate->value(),
            $venue->fullName(),
        );

        if (!empty($conflictingEvents)) {
            throw new DomainException('Venue conflict detected at the specified time');
        }

        $event = new SportsEvent(
            $eventId,
            $name,
            $category,
            $eventDate,
            $venue,
            $homeTeam,
            $awayTeam,
            $competition,
        );

        // Domain event will be recorded internally
        $event->recordDomainEvent(
            SportEventCreated::create(
                $eventId,
                $name,
                $category,
                $eventDate->value(),
                $venue->fullName(),
            ),
        );

        return $event;
    }

    /**
     * UpdateEventDetails
     */
    public function updateEventDetails(
        EventId $eventId,
        string $name,
        EventDate $eventDate,
        ?string $homeTeam = NULL,
        ?string $awayTeam = NULL,
        ?string $competition = NULL,
    ): void {
        $event = $this->eventRepository->findById($eventId);
        if ($event === NULL) {
            throw new DomainException('Event not found');
        }

        $event->updateDetails($name, $eventDate, $homeTeam, $awayTeam, $competition);
        $this->eventRepository->save($event);
    }

    /**
     * MarkEventAsHighDemand
     */
    public function markEventAsHighDemand(EventId $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);
        if ($event === NULL) {
            throw new DomainException('Event not found');
        }

        $event->markAsHighDemand();
        $this->eventRepository->save($event);
    }

    /**
     * UnmarkEventAsHighDemand
     */
    public function unmarkEventAsHighDemand(EventId $eventId): void
    {
        $event = $this->eventRepository->findById($eventId);
        if ($event === NULL) {
            throw new DomainException('Event not found');
        }

        $event->unmarkAsHighDemand();
        $this->eventRepository->save($event);
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * DetectHighDemandEvents
     */
    public function detectHighDemandEvents(): array
    {
        // This could include business logic to automatically detect high-demand events
        // based on factors like ticket sales, venue capacity, historical data, etc.
        $upcomingEvents = $this->eventRepository->findUpcoming();
        $highDemandEvents = [];

        foreach ($upcomingEvents as $event) {
            if ($this->isLikelyHighDemand($event)) {
                $event->markAsHighDemand();
                $this->eventRepository->save($event);
                $highDemandEvents[] = $event;
            }
        }

        return $highDemandEvents;
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * FindEventsByFilters
     */
    public function findEventsByFilters(
        ?SportCategory $category = NULL,
        ?string $venue = NULL,
        ?bool $highDemand = NULL,
        ?DateTimeImmutable $fromDate = NULL,
        ?DateTimeImmutable $toDate = NULL,
        int $page = 1,
        int $perPage = 20,
    ): array {
        return $this->eventRepository->findWithFilters(
            $category,
            $venue,
            $highDemand,
            $fromDate,
            $toDate,
            $page,
            $perPage,
        );
    }

    /**
     * Check if  likely high demand
     */
    private function isLikelyHighDemand(SportsEvent $event): bool
    {
        // Business logic to determine if an event is likely high demand
        // This could be based on:
        // - Popular teams/venues
        // - Championship/final games
        // - Venue capacity vs typical demand
        // - Historical ticket sales data

        $venue = $event->getVenue();
        $category = $event->getCategory();

        // Example logic - this would be more sophisticated in reality
        $highCapacityVenues = ['Wembley Stadium', 'Old Trafford', 'Emirates Stadium'];
        $popularCategories = ['FOOTBALL', 'TENNIS', 'CRICKET'];

        return in_array($venue->name(), $highCapacityVenues, TRUE)
               || in_array($category->value(), $popularCategories, TRUE)
               || str_contains(strtolower($event->getName()), 'final')
               || str_contains(strtolower($event->getName()), 'championship');
    }
}
