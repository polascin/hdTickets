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
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->ticketId->value();
    }

    public function getAggregateType(): string
    {
        return 'ticket';
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'ticket_id'  => $this->ticketId->value(),
            'old_status' => $this->oldStatus->value(),
            'new_status' => $this->newStatus->value(),
        ];
    }

    public function becameAvailable(): bool
    {
        return ! $this->oldStatus->canPurchase() && $this->newStatus->canPurchase();
    }

    public function becameUnavailable(): bool
    {
        return $this->oldStatus->canPurchase() && ! $this->newStatus->canPurchase();
    }

    public function soldOut(): bool
    {
        return $this->newStatus->isSoldOut() && ! $this->oldStatus->isSoldOut();
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->oldStatus = new AvailabilityStatus($payload['old_status']);
        $this->newStatus = new AvailabilityStatus($payload['new_status']);
    }
}
