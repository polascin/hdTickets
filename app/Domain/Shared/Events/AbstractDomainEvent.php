<?php declare(strict_types=1);

namespace App\Domain\Shared\Events;

use DateTimeImmutable;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use ReflectionClass;

abstract class AbstractDomainEvent implements DomainEventInterface
{
    protected string $domainEventId;

    protected DateTimeImmutable $occurredAt;

    protected string $version;

    /** @var array<string, mixed> */
    protected array $metadata;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(array $metadata = [])
    {
        $this->domainEventId = Uuid::uuid4()->toString();
        $this->occurredAt = new DateTimeImmutable();
        $this->version = '1.0';
        $this->metadata = $metadata;
    }

    /**
     * Get  event id
     */
    public function getEventId(): string
    {
        return $this->domainEventId;
    }

    /**
     * Get  event type
     */
    public function getEventType(): string
    {
        return static::class;
    }

    /**
     * Get  occurred at
     */
    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Get  version
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array<string, mixed>
     */
    /**
     * Get  metadata
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    /**
     * WithMetadata
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
    /**
     * Get  event name
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
    /**
     * ToArray
     */
    public function toArray(): array
    {
        return [
            'event_id'          => $this->domainEventId,
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
    /**
     * FromArray
     */
    public static function fromArray(array $data): static
    {
        // Use reflection to create instance safely
        $reflection = new ReflectionClass(static::class);
        /** @var static $event */
        $event = $reflection->newInstanceArgs([$data['metadata'] ?? []]);

        // Override the auto-generated values with the provided data
        $event->domainEventId = $data['event_id'];
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
    /**
     * PopulateFromPayload
     */
    protected function populateFromPayload(array $payload): void
    {
        // Override in child classes
    }
}
