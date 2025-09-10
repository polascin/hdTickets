<?php declare(strict_types=1);

namespace App\Domain\Purchase\Events;

use App\Domain\Purchase\ValueObjects\PurchaseId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use App\Domain\Ticket\ValueObjects\TicketId;
use Override;

final class PurchaseInitiated extends AbstractDomainEvent
{
    public function __construct(
        public PurchaseId $purchaseId,
        public string $userId,
        public TicketId $ticketId,
        public float $amount,
        public string $currency,
        /** @var array<string, mixed> Array containing purchase metadata like payment method, billing info, etc. */
        public array $purchaseDetails = [],
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    /**
     * Get  aggregate root id
     */
    public function getAggregateRootId(): string
    {
        return $this->purchaseId->value();
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'purchase';
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
            'purchase_id'      => $this->purchaseId->value(),
            'user_id'          => $this->userId,
            'ticket_id'        => $this->ticketId->value(),
            'amount'           => $this->amount,
            'currency'         => $this->currency,
            'purchase_details' => $this->purchaseDetails,
        ];
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
        $this->purchaseId = new PurchaseId($payload['purchase_id']);
        $this->userId = $payload['user_id'];
        $this->ticketId = new TicketId($payload['ticket_id']);
        $this->amount = $payload['amount'];
        $this->currency = $payload['currency'];
        $this->purchaseDetails = $payload['purchase_details'];
    }
}
