<?php declare(strict_types=1);

namespace App\Domain\Ticket\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\Price;
use App\Domain\Ticket\ValueObjects\TicketId;
use Override;

final class TicketPriceChanged extends AbstractDomainEvent
{
    public function __construct(
        public TicketId $ticketId,
        public Price $oldPrice,
        public Price $newPrice,
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

    /**
     * Get  price change
     */
    public function getPriceChange(): Price
    {
        if ($this->newPrice->isGreaterThan($this->oldPrice)) {
            return $this->newPrice->subtract($this->oldPrice);
        }

        return $this->oldPrice->subtract($this->newPrice);
    }

    /**
     * Check if  increase
     */
    public function isIncrease(): bool
    {
        return $this->newPrice->isGreaterThan($this->oldPrice);
    }

    /**
     * Check if  decrease
     */
    public function isDecrease(): bool
    {
        return $this->newPrice->isLessThan($this->oldPrice);
    }

    /**
     * @param array<string, mixed> $payload
     */
    /**
     * PopulateFromPayload
     */
    #[Override]
    protected function populateFromPayload(array $payload): void
    {
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->oldPrice = new Price($payload['old_price']['amount'], $payload['old_price']['currency']);
        $this->newPrice = new Price($payload['new_price']['amount'], $payload['new_price']['currency']);
    }
}
