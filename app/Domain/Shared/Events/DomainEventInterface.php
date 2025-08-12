<?php declare(strict_types=1);

namespace App\Domain\Shared\Events;

use DateTimeImmutable;

interface DomainEventInterface
{
    public function getEventId(): string;

    public function getEventType(): string;

    public function getAggregateRootId(): string;

    public function getAggregateType(): string;

    public function getOccurredAt(): DateTimeImmutable;

    public function getVersion(): string;

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array;

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array;

    public function withMetadata(array $metadata): static;
}
