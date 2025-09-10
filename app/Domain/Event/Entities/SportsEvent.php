<?php declare(strict_types=1);

namespace App\Domain\Event\Entities;

use App\Domain\Event\Events\SportEventMarkedAsHighDemand;
use App\Domain\Event\Events\SportEventUnmarkedAsHighDemand;
use App\Domain\Event\Events\SportEventUpdated;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Event\ValueObjects\Venue;
use DateTimeImmutable;
use InvalidArgumentException;

use function in_array;
use function sprintf;
use function strlen;

class SportsEvent
{
    /** @var array<int, object> */
    private array $domainEvents = [];

    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

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
        ?DateTimeImmutable $createdAt = NULL,
        ?DateTimeImmutable $updatedAt = NULL,
    ) {
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
        $this->validate();
    }

    /**
     * Get  id
     */
    public function getId(): EventId
    {
        return $this->id;
    }

    /**
     * Get  name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get  category
     */
    public function getCategory(): SportCategory
    {
        return $this->category;
    }

    /**
     * Get  event date
     */
    public function getEventDate(): EventDate
    {
        return $this->eventDate;
    }

    /**
     * Get  venue
     */
    public function getVenue(): Venue
    {
        return $this->venue;
    }

    /**
     * Get  home team
     */
    public function getHomeTeam(): ?string
    {
        return $this->homeTeam;
    }

    /**
     * Get  away team
     */
    public function getAwayTeam(): ?string
    {
        return $this->awayTeam;
    }

    /**
     * Get  competition
     */
    public function getCompetition(): ?string
    {
        return $this->competition;
    }

    /**
     * Check if  high demand
     */
    public function isHighDemand(): bool
    {
        return $this->isHighDemand;
    }

    /**
     * Get  created at
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get  updated at
     */
    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * UpdateDetails
     */
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

        $this->recordDomainEvent(new SportEventUpdated($this->id));
    }

    /**
     * MarkAsHighDemand
     */
    public function markAsHighDemand(): void
    {
        if (! $this->isHighDemand) {
            $this->isHighDemand = TRUE;
            $this->updatedAt = new DateTimeImmutable();
            $this->recordDomainEvent(new SportEventMarkedAsHighDemand($this->id));
        }
    }

    /**
     * UnmarkAsHighDemand
     */
    public function unmarkAsHighDemand(): void
    {
        if ($this->isHighDemand) {
            $this->isHighDemand = FALSE;
            $this->updatedAt = new DateTimeImmutable();
            $this->recordDomainEvent(new SportEventUnmarkedAsHighDemand($this->id));
        }
    }

    /**
     * Check if  upcoming
     */
    public function isUpcoming(): bool
    {
        return $this->eventDate->isUpcoming();
    }

    /**
     * Check if  past
     */
    public function isPast(): bool
    {
        return $this->eventDate->isPast();
    }

    /**
     * Get  display name
     */
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
    /**
     * Get  domain events
     */
    public function getDomainEvents(): array
    {
        return $this->domainEvents;
    }

    /**
     * ClearDomainEvents
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    /**
     * RecordDomainEvent
     */
    public function recordDomainEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Equals
     */
    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    /**
     * Validate
     */
    private function validate(): void
    {
        if (in_array(trim($this->name), ['', '0'], TRUE)) {
            throw new InvalidArgumentException('Event name cannot be empty');
        }

        if (strlen($this->name) > 255) {
            throw new InvalidArgumentException('Event name cannot exceed 255 characters');
        }

        if ($this->category->isTeamSport() && ($this->homeTeam === NULL || $this->homeTeam === '' || $this->homeTeam === '0' || ($this->awayTeam === NULL || $this->awayTeam === '' || $this->awayTeam === '0'))) {
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
