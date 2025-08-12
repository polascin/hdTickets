<?php declare(strict_types=1);

namespace App\Domain\Ticket\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\TicketId;

final class TicketPriceChanged extends AbstractDomainEvent
{
    public function __construct(
        public TicketId $ticketId,
        public Price $oldPrice,
        public Price $newPrice,
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
            'ticket_id' => $this->ticketId->value(),
            'old_price' => [
                'amount'   => $this->oldPrice->amount(),
                'currency' => $this->oldPrice->currency(),
            ],
            'new_price' => [
                'amount'   => $this->newPrice->amount(),
                'currency' => $this->newPrice->currency(),
            ],
        ];
    }

    public function getPriceChange(): Price
    {
        if ($this->newPrice->isGreaterThan($this->oldPrice)) {
            return $this->newPrice->subtract($this->oldPrice);
        }

        return $this->oldPrice->subtract($this->newPrice);
    }

    public function isIncrease(): bool
    {
        return $this->newPrice->isGreaterThan($this->oldPrice);
    }

    public function isDecrease(): bool
    {
        return $this->newPrice->isLessThan($this->oldPrice);
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->oldPrice = new Price($payload['old_price']['amount'], $payload['old_price']['currency']);
        $this->newPrice = new Price($payload['new_price']['amount'], $payload['new_price']['currency']);
    }
}
