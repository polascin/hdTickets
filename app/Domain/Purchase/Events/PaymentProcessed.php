<?php declare(strict_types=1);

namespace App\Domain\Purchase\Events;

use App\Domain\Purchase\ValueObjects\PurchaseId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class PaymentProcessed extends AbstractDomainEvent
{
    public function __construct(
        public PurchaseId $purchaseId,
        public string $paymentMethod,
        public string $paymentReference,
        public float $amount,
        public string $currency,
        public string $status,
        public DateTimeImmutable $processedAt,
        /** @var array<string, mixed> */
        public array $paymentDetails = [],
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->purchaseId->value();
    }

    public function getAggregateType(): string
    {
        return 'purchase';
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return [
            'purchase_id'       => $this->purchaseId->value(),
            'payment_method'    => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'amount'            => $this->amount,
            'currency'          => $this->currency,
            'status'            => $this->status,
            'processed_at'      => $this->processedAt->format('Y-m-d H:i:s'),
            'payment_details'   => $this->paymentDetails,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->purchaseId = new PurchaseId($payload['purchase_id']);
        $this->paymentMethod = $payload['payment_method'];
        $this->paymentReference = $payload['payment_reference'];
        $this->amount = $payload['amount'];
        $this->currency = $payload['currency'];
        $this->status = $payload['status'];
        $this->processedAt = new DateTimeImmutable($payload['processed_at']);
        $this->paymentDetails = $payload['payment_details'];
    }
}
