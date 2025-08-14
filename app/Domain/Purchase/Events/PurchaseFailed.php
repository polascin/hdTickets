<?php declare(strict_types=1);

namespace App\Domain\Purchase\Events;

use App\Domain\Purchase\ValueObjects\PurchaseId;
use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class PurchaseFailed extends AbstractDomainEvent
{
    public function __construct(
        public PurchaseId $purchaseId,
        public string $userId,
        public string $failureReason,
        public string $errorCode,
        public DateTimeImmutable $failedAt,
        /** @var array<string, mixed> Detailed error information including stack traces, validation errors, etc. */
        public array $errorDetails = [],
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
            'purchase_id'    => $this->purchaseId->value(),
            'user_id'        => $this->userId,
            'failure_reason' => $this->failureReason,
            'error_code'     => $this->errorCode,
            'failed_at'      => $this->failedAt->format('Y-m-d H:i:s'),
            'error_details'  => $this->errorDetails,
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    /**
     * PopulateFromPayload
     */
    protected function populateFromPayload(array $payload): void
    {
        $this->purchaseId = new PurchaseId($payload['purchase_id']);
        $this->userId = $payload['user_id'];
        $this->failureReason = $payload['failure_reason'];
        $this->errorCode = $payload['error_code'];
        $this->failedAt = new DateTimeImmutable($payload['failed_at']);
        $this->errorDetails = $payload['error_details'];
    }
}
