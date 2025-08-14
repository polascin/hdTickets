<?php declare(strict_types=1);

namespace App\Domain\Event\Repositories;

use App\Domain\Event\Aggregates\EventSchedule;
use DateTimeImmutable;

interface EventScheduleRepositoryInterface
{
    public function save(EventSchedule $schedule): void;

    public function findByDate(DateTimeImmutable $date): ?EventSchedule;

    /**
     * @return array<int, EventSchedule>
     */
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array;

    /**
     * @return array<int, EventSchedule>
     */
    public function findByVenue(string $venue): array;

    /**
     * @return array<int, EventSchedule>
     */
    public function findConflictingSchedules(DateTimeImmutable $date): array;

    public function delete(DateTimeImmutable $date): void;

    public function exists(DateTimeImmutable $date): bool;
}
