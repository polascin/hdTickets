<?php declare(strict_types=1);

namespace App\Application\Queries;

use App\Domain\Event\Repositories\SportsEventRepositoryInterface;
use App\Domain\Event\ValueObjects\SportCategory;

class GetUpcomingEventsQueryHandler
{
    public function __construct(
        private SportsEventRepositoryInterface $eventRepository,
    ) {
    }

    /**
     * @return array<int, \App\Domain\Event\Entities\SportsEvent>
     */
    /**
     * Handle
     */
    public function handle(GetUpcomingEventsQuery $query): array
    {
        $category = $query->category ? new SportCategory($query->category) : NULL;

        return $this->eventRepository->findWithFilters(
            $category,
            $query->venue,
            $query->highDemandOnly,
            $query->fromDate,
            $query->toDate,
            $query->page,
            $query->perPage,
        );
    }
}
