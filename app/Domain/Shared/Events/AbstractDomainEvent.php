<?php declare(strict_types=1);

namespace App\Domain\Shared\Events;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    protected string $eventId;

    protected DateTimeImmutable $occurredAt;

    protected string $version;

    /** @var array<string, mixed> */
    protected array $metadata;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredAt = new DateTimeImmutable();
        $this->version = '1.0';
        $this->metadata = $metadata;
    }

    public function getEventId(): string
    {
        return $this->eventId;
    }

    public function getEventType(): string
    {
        return static::class;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function withMetadata(array $metadata): static
    {
        $clone = clone $this;
        $clone->metadata = array_merge($this->metadata, $metadata);

        return $clone;
    }

    /**
     * Get the event name for serialization
     */
    public function getEventName(): string
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * Convert event to array for serialization
     */
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id'          => $this->eventId,
            'event_type'        => $this->getEventType(),
            'aggregate_root_id' => $this->getAggregateRootId(),
            'aggregate_type'    => $this->getAggregateType(),
            'occurred_at'       => $this->occurredAt->format('Y-m-d H:i:s.u'),
            'version'           => $this->version,
            'payload'           => $this->getPayload(),
            'metadata'          => $this->metadata,
        ];
    }

    /**
     * Create event from array (deserialization)
     */
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        // Initialize with empty metadata first to ensure proper constructor behavior
        $event = new static($data['metadata'] ?? []);

        // Override the auto-generated values with the provided data
        $event->eventId = $data['event_id'];
        $event->occurredAt = new DateTimeImmutable($data['occurred_at']);
        $event->version = $data['version'];
        $event->metadata = $data['metadata'] ?? [];

        // Populate event-specific properties from payload
        $event->populateFromPayload($data['payload']);

        return $event;
    }

    /**
     * Override in child classes to populate specific properties
     *
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        // Override in child classes
    }
}
