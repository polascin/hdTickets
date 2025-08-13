<?php declare(strict_types=1);

namespace App\Domain\Event\Aggregates;

use App\Domain\Event\Entities\SportsEvent;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use DateTimeImmutable;
use DomainException;

use function count;

class EventSchedule
{
    /** @var SportsEvent[] */
    private array $events = [];

    /** @var array<int, object> */
    private array $domainEvents = [];

    public function __construct(
        private DateTimeImmutable $scheduleDate,
        private ?string $venue = NULL,
    ) {
    }

    /**
     * AddEvent
     */
    public function addEvent(SportsEvent $event): void
    {
        $eventId = $event->getId()->value();

        if (isset($this->events[$eventId])) {
            throw new DomainException('Event already exists in schedule');
        }

        $this->validateEventDate($event);
        $this->events[$eventId] = $event;

        $this->recordDomainEvent(
            new \App\Domain\Event\Events\EventAddedToSchedule($event->getId(), $this->scheduleDate),
        );
    }

    /**
     * RemoveEvent
     */
    public function removeEvent(EventId $eventId): void
    {
        $id = $eventId->value();

        if (! isset($this->events[$id])) {
            throw new DomainException('Event not found in schedule');
        }

        unset($this->events[$id]);

        $this->recordDomainEvent(
            new \App\Domain\Event\Events\EventRemovedFromSchedule($eventId, $this->scheduleDate),
        );
    }

    /**
     * Get  event
     */
    public function getEvent(EventId $eventId): ?SportsEvent
    {
        return $this->events[$eventId->value()] ?? NULL;
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * Get  all events
     */
    public function getAllEvents(): array
    {
        return array_values($this->events);
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * Get  events by category
     */
    public function getEventsByCategory(SportCategory $category): array
    {
        return array_filter(
            $this->events,
            fn (SportsEvent $event) => $event->getCategory()->equals($category),
        );
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * Get  upcoming events
     */
    public function getUpcomingEvents(): array
    {
        return array_filter(
            $this->events,
            fn (SportsEvent $event) => $event->isUpcoming(),
        );
    }

    /**
     * @return array<int, SportsEvent>
     */
    /**
     * Get  high demand events
     */
    public function getHighDemandEvents(): array
    {
        return array_filter(
            $this->events,
            fn (SportsEvent $event) => $event->isHighDemand(),
        );
    }

    /**
     * Check if has  conflicts
     */
    public function hasConflicts(): bool
    {
        $eventTimes = [];

        foreach ($this->events as $event) {
            $dateTime = $event->getEventDate()->value();
            $timeSlot = $dateTime->format('Y-m-d H:i');

            if (isset($eventTimes[$timeSlot])) {
                return TRUE;
            }

            $eventTimes[$timeSlot] = TRUE;
        }

        return FALSE;
    }

    /**
     * @return array<int, array{0: SportsEvent, 1: SportsEvent}>
     */
    /**
     * Get  conflicting events
     */
    public function getConflictingEvents(): array
    {
        $conflicts = [];
        $eventTimes = [];

        foreach ($this->events as $event) {
            $dateTime = $event->getEventDate()->value();
            $timeSlot = $dateTime->format('Y-m-d H:i');

            if (isset($eventTimes[$timeSlot])) {
                $conflicts[] = [$eventTimes[$timeSlot], $event];
            } else {
                $eventTimes[$timeSlot] = $event;
            }
        }

        return $conflicts;
    }

    /**
     * Get  schedule date
     */
    public function getScheduleDate(): DateTimeImmutable
    {
        return $this->scheduleDate;
    }

    /**
     * Get  venue
     */
    public function getVenue(): ?string
    {
        return $this->venue;
    }

    /**
     * Get  event count
     */
    public function getEventCount(): int
    {
        return count($this->events);
    }

    /**
     * Check if  empty
     */
    public function isEmpty(): bool
    {
        return empty($this->events);
    }

    /**
     * @return array<int, object>
     */
    /**
     * Get  domain events
     */
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    /**
     * ClearDomainEvents
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    /**
     * ValidateEventDate
     */
    private function validateEventDate(SportsEvent $event): void
    {
        $eventDate = $event->getEventDate()->value();
        $scheduleDate = $this->scheduleDate->format('Y-m-d');
        $eventScheduleDate = $eventDate->format('Y-m-d');

        if ($scheduleDate !== $eventScheduleDate) {
            throw new DomainException('Event date must match schedule date');
        }
    }

    /**
     * RecordDomainEvent
     */
    private function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }
}
