<?php declare(strict_types=1);

namespace App\Domain\Purchase\Events;

use App\Domain\Purchase\ValueObjects\PurchaseId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;
use Override;

final class PurchaseCompleted extends AbstractDomainEvent
{
    public function __construct(
        public PurchaseId $purchaseId,
        public string $userId,
        public string $paymentReference,
        public DateTimeImmutable $completedAt,
        /** @var array<string, mixed> Purchase confirmation details like ticket details, confirmation numbers, etc. */
        public array $confirmationDetails = [],
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
            'purchase_id'          => $this->purchaseId->value(),
            'user_id'              => $this->userId,
            'payment_reference'    => $this->paymentReference,
            'completed_at'         => $this->completedAt->format('Y-m-d H:i:s'),
            'confirmation_details' => $this->confirmationDetails,
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
        $this->paymentReference = $payload['payment_reference'];
        $this->completedAt = new DateTimeImmutable($payload['completed_at']);
        $this->confirmationDetails = $payload['confirmation_details'];
    }
}
