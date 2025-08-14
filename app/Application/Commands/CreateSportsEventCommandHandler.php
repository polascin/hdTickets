<?php declare(strict_types=1);

namespace App\Application\Commands;

use App\Domain\Event\Repositories\SportsEventRepositoryInterface;
use App\Domain\Event\Services\EventManagementService;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Event\ValueObjects\Venue;

class CreateSportsEventCommandHandler
{
    public function __construct(
        private EventManagementService $eventManagementService,
        private SportsEventRepositoryInterface $eventRepository,
    ) {
    }

    /**
     * Handle
     */
    public function handle(CreateSportsEventCommand $command): void
    {
        $eventDate = new EventDate($command->eventDate);
        $venue = new Venue(
            $command->venueName,
            $command->venueCity,
            $command->venueCountry,
            $command->venueAddress,
            $command->venueCapacity,
        );
        $category = new SportCategory($command->category);

        $sportsEvent = $this->eventManagementService->createSportsEvent(
            $command->name,
            $category,
            $eventDate,
            $venue,
            $command->homeTeam,
            $command->awayTeam,
            $command->competition,
        );

        $this->eventRepository->save($sportsEvent);
    }
}
