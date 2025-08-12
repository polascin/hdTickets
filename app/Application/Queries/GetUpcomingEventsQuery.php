<?php declare(strict_types=1);

namespace App\Application\Queries;

use DateTimeImmutable;

final readonly class GetUpcomingEventsQuery
{
    public function __construct(
        public int $limit = 50,
        public ?string $category = NULL,
        public ?string $venue = NULL,
        public ?bool $highDemandOnly = NULL,
        public ?DateTimeImmutable $fromDate = NULL,
        public ?DateTimeImmutable $toDate = NULL,
        public int $page = 1,
        public int $perPage = 20,
    ) {
    }
}
