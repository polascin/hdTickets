<?php declare(strict_types=1);

namespace App\Services\Core;

use Illuminate\Queue\QueueManager;

class QueueService
{
    public function __construct(
        private QueueManager $queueManager,
    ) {
    }

    public function push(string $job, array $data = [], string $queue = 'default'): void
    {
        $this->queueManager->push($job, $data, $queue);
    }

    public function later(int $delay, string $job, array $data = [], string $queue = 'default'): void
    {
        $this->queueManager->later($delay, $job, $data, $queue);
    }

    public function getQueueSize(string $queue = 'default'): int
    {
        return $this->queueManager->size($queue);
    }
}
