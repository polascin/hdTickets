<?php declare(strict_types=1);

namespace App\Domain\Monitoring\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;

final class MonitoringStarted extends AbstractDomainEvent
{
    public function __construct(
        public string $monitorId,
        public string $userId,
        public string $platform,
        /** @var array<string, mixed> Monitoring criteria including price thresholds, availability settings, etc. */
        public array $criteria,
        public DateTimeImmutable $startedAt,
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
            'monitor_id' => $this->monitorId,
            'user_id'    => $this->userId,
            'platform'   => $this->platform,
            'criteria'   => $this->criteria,
            'started_at' => $this->startedAt->format('Y-m-d H:i:s'),
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
        $this->monitorId = $payload['monitor_id'];
        $this->userId = $payload['user_id'];
        $this->platform = $payload['platform'];
        $this->criteria = $payload['criteria'];
        $this->startedAt = new DateTimeImmutable($payload['started_at']);
    }
}
