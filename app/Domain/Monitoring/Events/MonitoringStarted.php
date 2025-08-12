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
        /** @var array<string, mixed> */
        public array $criteria,
        public DateTimeImmutable $startedAt,
        /** @var array<string, mixed> */
        array $metadata = [],
    ) {
        parent::__construct($metadata);
    }

    public function getAggregateRootId(): string
    {
        return $this->monitorId;
    }

    public function getAggregateType(): string
    {
        return 'monitoring';
    }

    /**
     * @return array<string, mixed>
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
    protected function populateFromPayload(array $payload): void
    {
        $this->monitorId = $payload['monitor_id'];
        $this->userId = $payload['user_id'];
        $this->platform = $payload['platform'];
        $this->criteria = $payload['criteria'];
        $this->startedAt = new DateTimeImmutable($payload['started_at']);
    }
}
