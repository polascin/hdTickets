<?php declare(strict_types=1);

namespace App\Domain\Event\Events;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Shared\Events\AbstractDomainEvent;

final class SportEventUnmarkedAsHighDemand extends AbstractDomainEvent
{
    public function __construct(
        public EventId $eventId,
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
        return 'sport_event';
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'event_id' => $this->eventId->value(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->eventId = new EventId($payload['event_id']);
    }
}
