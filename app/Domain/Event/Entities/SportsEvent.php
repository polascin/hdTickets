<?php declare(strict_types=1);

namespace App\Domain\Event\Entities;

use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Event\ValueObjects\Venue;
use DateTimeImmutable;
use InvalidArgumentException;

use function sprintf;
use function strlen;

class SportsEvent
{
    /** @var array<int, object> */
    private array $domainEvents = [];

    public function __construct(
        private EventId $id,
        private string $name,
        private SportCategory $category,
        private EventDate $eventDate,
        private Venue $venue,
        private ?string $homeTeam = NULL,
        private ?string $awayTeam = NULL,
        private ?string $competition = NULL,
        private bool $isHighDemand = FALSE,
        private ?DateTimeImmutable $createdAt = NULL,
        private ?DateTimeImmutable $updatedAt = NULL,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->validate();
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCategory(): SportCategory
    {
        return $this->category;
    }

    public function getEventDate(): EventDate
    {
        return $this->eventDate;
    }

    public function getVenue(): Venue
    {
        return $this->venue;
    }

    public function getHomeTeam(): ?string
    {
        return $this->homeTeam;
    }

    public function getAwayTeam(): ?string
    {
        return $this->awayTeam;
    }

    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    public function isHighDemand(): bool
    {
        return $this->isHighDemand;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateDetails(
        string $name,
        EventDate $eventDate,
        ?string $homeTeam = NULL,
        ?string $awayTeam = NULL,
        ?string $competition = NULL,
    ): void {
        $this->name = $name;
        $this->eventDate = $eventDate;
        $this->homeTeam = $homeTeam;
        $this->awayTeam = $awayTeam;
        $this->competition = $competition;
        $this->updatedAt = new DateTimeImmutable();
        $this->validate();

        $this->recordDomainEvent(new \App\Domain\Event\Events\SportEventUpdated($this->id));
    }

    public function markAsHighDemand(): void
    {
        if (! $this->isHighDemand) {
            $this->isHighDemand = TRUE;
            $this->updatedAt = new DateTimeImmutable();
            $this->recordDomainEvent(new \App\Domain\Event\Events\SportEventMarkedAsHighDemand($this->id));
        }
    }

    public function unmarkAsHighDemand(): void
    {
        if ($this->isHighDemand) {
            $this->isHighDemand = FALSE;
            $this->updatedAt = new DateTimeImmutable();
            $this->recordDomainEvent(new \App\Domain\Event\Events\SportEventUnmarkedAsHighDemand($this->id));
        }
    }

    public function isUpcoming(): bool
    {
        return $this->eventDate->isUpcoming();
    }

    public function isPast(): bool
    {
        return $this->eventDate->isPast();
    }

    public function getDisplayName(): string
    {
        if ($this->category->isTeamSport() && $this->homeTeam && $this->awayTeam) {
            return sprintf('%s vs %s', $this->homeTeam, $this->awayTeam);
        }

        return $this->name;
    }

    /**
     * @return array<int, object>
     */
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    public function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    private function validate(): void
    {
        if (empty(trim($this->name))) {
            throw new InvalidArgumentException('Event name cannot be empty');
        }

        if (strlen($this->name) > 255) {
            throw new InvalidArgumentException('Event name cannot exceed 255 characters');
        }

        if ($this->category->isTeamSport() && (empty($this->homeTeam) || empty($this->awayTeam))) {
            throw new InvalidArgumentException('Team sports must have both home and away teams');
        }

        if ($this->homeTeam !== NULL && strlen($this->homeTeam) > 100) {
            throw new InvalidArgumentException('Home team name cannot exceed 100 characters');
        }

        if ($this->awayTeam !== NULL && strlen($this->awayTeam) > 100) {
            throw new InvalidArgumentException('Away team name cannot exceed 100 characters');
        }

        if ($this->competition !== NULL && strlen($this->competition) > 100) {
            throw new InvalidArgumentException('Competition name cannot exceed 100 characters');
        }
    }
}
