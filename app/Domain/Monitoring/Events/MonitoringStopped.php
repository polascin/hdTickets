<?php declare(strict_types=1);

namespace App\Domain\Monitoring\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;
use Override;

final class MonitoringStopped extends AbstractDomainEvent
{
    public function __construct(
        public string $monitorId,
        public string $userId,
        public DateTimeImmutable $stoppedAt,
        public string $reason,
        /** @var array<string, mixed> Final monitoring metrics including total scans, alerts triggered, success rate, etc. */
        public array $finalMetrics = [],
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
        return $this->monitorId;
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'monitoring';
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
            'monitor_id'    => $this->monitorId,
            'user_id'       => $this->userId,
            'stopped_at'    => $this->stoppedAt->format('Y-m-d H:i:s'),
            'reason'        => $this->reason,
            'final_metrics' => $this->finalMetrics,
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
        $this->monitorId = $payload['monitor_id'];
        $this->userId = $payload['user_id'];
        $this->stoppedAt = new DateTimeImmutable($payload['stopped_at']);
        $this->reason = $payload['reason'];
        $this->finalMetrics = $payload['final_metrics'];
    }
}
