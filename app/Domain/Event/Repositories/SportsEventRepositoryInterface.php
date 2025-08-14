<?php declare(strict_types=1);

namespace App\Domain\Event\Repositories;

use App\Domain\Event\Entities\SportsEvent;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use DateTimeImmutable;

interface SportsEventRepositoryInterface
{
    public function save(SportsEvent $event): void;

    public function findById(EventId $id): ?SportsEvent;

    public function findByName(string $name): ?SportsEvent;

    /**
     * @return array<int, SportsEvent>
     */
    public function findByCategory(SportCategory $category): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findUpcoming(int $limit = 50): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findHighDemandEvents(): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findByVenue(string $venue): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findByTeam(string $team): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findByCompetition(string $competition): array;

    /**
     * @return array<int, SportsEvent>
     */
    public function findConflictingEvents(
        DateTimeImmutable $eventDate,
        string $venue,
    ): array;

    public function delete(EventId $id): void;

    public function exists(EventId $id): bool;

    /**
     * Get paginated events with filtering options
     *
     * @return array<int, SportsEvent>
     */
    public function findWithFilters(
        ?SportCategory $category = NULL,
        ?string $venue = NULL,
        ?bool $highDemand = NULL,
        ?DateTimeImmutable $fromDate = NULL,
        ?DateTimeImmutable $toDate = NULL,
        int $page = 1,
        int $perPage = 20,
    ): array;

    /**
     * Count events with filtering options
     */
    public function countWithFilters(
        ?SportCategory $category = NULL,
        ?string $venue = NULL,
        ?bool $highDemand = NULL,
        ?DateTimeImmutable $fromDate = NULL,
        ?DateTimeImmutable $toDate = NULL,
    ): int;
}
