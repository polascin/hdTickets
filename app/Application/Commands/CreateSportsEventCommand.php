<?php declare(strict_types=1);

namespace App\Application\Commands;

use DateTimeImmutable;

final readonly class CreateSportsEventCommand
{
    public function __construct(
        public string $name,
        public string $category,
        public DateTimeImmutable $eventDate,
        public string $venueName,
        public string $venueCity,
        public string $venueCountry,
        public ?string $venueAddress = NULL,
        public ?int $venueCapacity = NULL,
        public ?string $homeTeam = NULL,
        public ?string $awayTeam = NULL,
        public ?string $competition = NULL,
    ) {
    }
}
