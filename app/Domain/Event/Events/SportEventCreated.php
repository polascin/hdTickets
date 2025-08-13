<?php declare(strict_types=1);

namespace App\Domain\Event\Events;

use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class SportEventCreated extends AbstractDomainEvent
{
    public function __construct(
        public EventId $eventId,
        public string $name,
        public SportCategory $category,
        public DateTimeImmutable $eventDate,
        public string $venue,
        /** @var array<string, mixed> Event metadata including additional context or debugging information */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    /**
     * Create
     */
    public static function create(
        EventId $eventId,
        string $name,
        SportCategory $category,
        DateTimeImmutable $eventDate,
        string $venue,
    ): self {
        return new self(
            $eventId,
            $name,
            $category,
            $eventDate,
            $venue,
        );
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
        return 'sport_event';
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
            'event_id'   => $this->eventId->value(),
            'name'       => $this->name,
            'category'   => $this->category->value(),
            'event_date' => $this->eventDate->format('Y-m-d H:i:s'),
            'venue'      => $this->venue,
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
        $this->name = $payload['name'];
        $this->category = new SportCategory($payload['category']);
        $this->eventDate = new DateTimeImmutable($payload['event_date']);
        $this->venue = $payload['venue'];
    }
}
