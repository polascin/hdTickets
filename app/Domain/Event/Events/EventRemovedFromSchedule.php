<?php declare(strict_types=1);

namespace App\Domain\Event\Events;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class EventRemovedFromSchedule extends AbstractDomainEvent
{
    public function __construct(
        public EventId $eventId,
        public DateTimeImmutable $scheduleDate,
        /** @var array<string, mixed> Event metadata including additional context or debugging information */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    /**
     * Get  aggregate root id
     */
    public function getAggregateRootId(): string
    {
        return $this->eventId->value();
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'event_schedule';
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  payload
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
    /**
     * PopulateFromPayload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->eventId = new EventId($payload['event_id']);
        $this->scheduleDate = new DateTimeImmutable($payload['schedule_date']);
    }
}
