<?php declare(strict_types=1);

namespace App\Domain\Ticket\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\AvailabilityStatus;
use App\Domain\Ticket\ValueObjects\TicketId;

final class TicketAvailabilityChanged extends AbstractDomainEvent
{
    public function __construct(
        public TicketId $ticketId,
        public AvailabilityStatus $oldStatus,
        public AvailabilityStatus $newStatus,
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
        return $this->ticketId->value();
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'ticket';
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
            'ticket_id'  => $this->ticketId->value(),
            'old_status' => $this->oldStatus->value(),
            'new_status' => $this->newStatus->value(),
        ];
    }

    /**
     * BecameAvailable
     */
    public function becameAvailable(): bool
    {
        return ! $this->oldStatus->canPurchase() && $this->newStatus->canPurchase();
    }

    /**
     * BecameUnavailable
     */
    public function becameUnavailable(): bool
    {
        return $this->oldStatus->canPurchase() && ! $this->newStatus->canPurchase();
    }

    /**
     * SoldOut
     */
    public function soldOut(): bool
    {
        return $this->newStatus->isSoldOut() && ! $this->oldStatus->isSoldOut();
    }

    /**
     * @param array<string, mixed> $payload
     */
    /**
     * PopulateFromPayload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->oldStatus = new AvailabilityStatus($payload['old_status']);
        $this->newStatus = new AvailabilityStatus($payload['new_status']);
    }
}
