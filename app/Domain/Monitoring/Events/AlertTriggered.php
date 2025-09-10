<?php declare(strict_types=1);

namespace App\Domain\Monitoring\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;
use Override;

final class AlertTriggered extends AbstractDomainEvent
{
    public function __construct(
        public string $alertId,
        public string $monitorId,
        public string $userId,
        public string $alertType,
        public string $severity,
        /** @var array<string, mixed> Alert-specific data including trigger conditions, thresholds, current values, etc. */
        public array $alertData,
        public DateTimeImmutable $triggeredAt,
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
        return $this->alertId;
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'alert';
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
            'alert_id'     => $this->alertId,
            'monitor_id'   => $this->monitorId,
            'user_id'      => $this->userId,
            'alert_type'   => $this->alertType,
            'severity'     => $this->severity,
            'alert_data'   => $this->alertData,
            'triggered_at' => $this->triggeredAt->format('Y-m-d H:i:s'),
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
        $this->alertId = $payload['alert_id'];
        $this->monitorId = $payload['monitor_id'];
        $this->userId = $payload['user_id'];
        $this->alertType = $payload['alert_type'];
        $this->severity = $payload['severity'];
        $this->alertData = $payload['alert_data'];
        $this->triggeredAt = new DateTimeImmutable($payload['triggered_at']);
    }
}
