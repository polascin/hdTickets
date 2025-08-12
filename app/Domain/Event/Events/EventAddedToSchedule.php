<?php declare(strict_types=1);

namespace App\Domain\Event\Events;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class EventAddedToSchedule extends AbstractDomainEvent
{
    public function __construct(
        public EventId $eventId,
        public DateTimeImmutable $scheduleDate,
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->eventId->value();
    }

    public function getAggregateType(): string
    {
        return 'event_schedule';
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'event_id'      => $this->eventId->value(),
            'schedule_date' => $this->scheduleDate->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->eventId = new EventId($payload['event_id']);
        $this->scheduleDate = new DateTimeImmutable($payload['schedule_date']);
    }
}
