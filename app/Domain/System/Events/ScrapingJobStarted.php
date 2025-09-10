<?php declare(strict_types=1);

namespace App\Domain\System\Events;

use App\Domain\Shared\Events\AbstractDomainEvent;
use DateTimeImmutable;
use Override;

final class ScrapingJobStarted extends AbstractDomainEvent
{
    public function __construct(
        public string $jobId,
        public string $platform,
        /** @var array<string, mixed> Scraping configuration including target URLs, intervals, retry settings, etc. */
        public array $configuration,
        public DateTimeImmutable $startedAt,
        public int $expectedTargets = 0,
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
        return $this->jobId;
    }

    /**
     * Get  aggregate type
     */
    public function getAggregateType(): string
    {
        return 'scraping_job';
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
            'job_id'           => $this->jobId,
            'platform'         => $this->platform,
            'configuration'    => $this->configuration,
            'started_at'       => $this->startedAt->format('Y-m-d H:i:s'),
            'expected_targets' => $this->expectedTargets,
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
        $this->jobId = $payload['job_id'];
        $this->platform = $payload['platform'];
        $this->configuration = $payload['configuration'];
        $this->startedAt = new DateTimeImmutable($payload['started_at']);
        $this->expectedTargets = $payload['expected_targets'];
    }
}
