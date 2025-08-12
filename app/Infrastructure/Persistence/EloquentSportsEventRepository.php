<?php declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Event\Entities\SportsEvent;
use App\Domain\Event\Repositories\SportsEventRepositoryInterface;
use App\Domain\Event\ValueObjects\EventDate;
use App\Domain\Event\ValueObjects\EventId;
use App\Domain\Event\ValueObjects\SportCategory;
use App\Domain\Event\ValueObjects\Venue;
use App\Models\ScrapedTicket; // Existing model - we'll adapt
use DateTimeImmutable;

class EloquentSportsEventRepository implements SportsEventRepositoryInterface
{
    public function save(SportsEvent $event): void
    {
        $model = ScrapedTicket::firstOrNew(['external_id' => $event->getId()->value()]);

        $model->fill([
            'external_id'    => $event->getId()->value(),
            'event_name'     => $event->getName(),
            'sport_category' => $event->getCategory()->value(),
            'event_date'     => $event->getEventDate()->value(),
            'venue_name'     => $event->getVenue()->name(),
            'venue_city'     => $event->getVenue()->city(),
            'venue_country'  => $event->getVenue()->country(),
            'venue_address'  => $event->getVenue()->address(),
            'venue_capacity' => $event->getVenue()->capacity(),
            'home_team'      => $event->getHomeTeam(),
            'away_team'      => $event->getAwayTeam(),
            'competition'    => $event->getCompetition(),
            'is_high_demand' => $event->isHighDemand(),
            'created_at'     => $event->getCreatedAt(),
            'updated_at'     => $event->getUpdatedAt(),
        ]);

        $model->save();
    }

    public function findById(EventId $id): ?SportsEvent
    {
        $model = ScrapedTicket::where('external_id', $id->value())->first();

        if (! $model) {
            return NULL;
        }

        return $this->toDomainEntity($model);
    }

    public function findByName(string $name): ?SportsEvent
    {
        $model = ScrapedTicket::where('event_name', $name)->first();

        if (! $model) {
            return NULL;
        }

        return $this->toDomainEntity($model);
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findByCategory(SportCategory $category): array
    {
        $models = ScrapedTicket::where('sport_category', $category->value())->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findUpcoming(int $limit = 50): array
    {
        $models = ScrapedTicket::where('event_date', '>', now())
            ->orderBy('event_date')
            ->limit($limit)
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findByDateRange(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
    ): array {
        $models = ScrapedTicket::whereBetween('event_date', [
            $startDate->format('Y-m-d H:i:s'),
            $endDate->format('Y-m-d H:i:s'),
        ])->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findHighDemandEvents(): array
    {
        $models = ScrapedTicket::where('is_high_demand', TRUE)->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findByVenue(string $venue): array
    {
        $models = ScrapedTicket::where('venue_name', $venue)->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findByTeam(string $team): array
    {
        $models = ScrapedTicket::where('home_team', $team)
            ->orWhere('away_team', $team)
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findByCompetition(string $competition): array
    {
        $models = ScrapedTicket::where('competition', $competition)->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    /**
     * @return array<int, SportsEvent>
     */
    public function findConflictingEvents(
        DateTimeImmutable $eventDate,
        string $venue,
    ): array {
        $models = ScrapedTicket::where('venue_name', $venue)
            ->where('event_date', $eventDate->format('Y-m-d H:i:s'))
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    public function delete(EventId $id): void
    {
        ScrapedTicket::where('external_id', $id->value())->delete();
    }

    public function exists(EventId $id): bool
    {
        return ScrapedTicket::where('external_id', $id->value())->exists();
    }

    /**
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
    ): array {
        $query = ScrapedTicket::query();

        if ($category) {
            $query->where('sport_category', $category->value());
        }

        if ($venue) {
            $query->where('venue_name', 'LIKE', "%{$venue}%");
        }

        if ($highDemand !== NULL) {
            $query->where('is_high_demand', $highDemand);
        }

        if ($fromDate) {
            $query->where('event_date', '>=', $fromDate->format('Y-m-d H:i:s'));
        }

        if ($toDate) {
            $query->where('event_date', '<=', $toDate->format('Y-m-d H:i:s'));
        }

        $models = $query->orderBy('event_date')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return $models->map(fn ($model) => $this->toDomainEntity($model))->all();
    }

    public function countWithFilters(
        ?SportCategory $category = NULL,
        ?string $venue = NULL,
        ?bool $highDemand = NULL,
        ?DateTimeImmutable $fromDate = NULL,
        ?DateTimeImmutable $toDate = NULL,
    ): int {
        $query = ScrapedTicket::query();

        if ($category) {
            $query->where('sport_category', $category->value());
        }

        if ($venue) {
            $query->where('venue_name', 'LIKE', "%{$venue}%");
        }

        if ($highDemand !== NULL) {
            $query->where('is_high_demand', $highDemand);
        }

        if ($fromDate) {
            $query->where('event_date', '>=', $fromDate->format('Y-m-d H:i:s'));
        }

        if ($toDate) {
            $query->where('event_date', '<=', $toDate->format('Y-m-d H:i:s'));
        }

        return $query->count();
    }

    private function toDomainEntity(ScrapedTicket $model): SportsEvent
    {
        return new SportsEvent(
            new EventId($model->external_id),
            $model->event_name,
            new SportCategory($model->sport_category),
            new EventDate(new DateTimeImmutable($model->event_date)),
            new Venue(
                $model->venue_name,
                $model->venue_city ?? 'Unknown',
                $model->venue_country ?? 'Unknown',
                $model->venue_address,
                $model->venue_capacity,
            ),
            $model->home_team,
            $model->away_team,
            $model->competition,
            $model->is_high_demand ?? FALSE,
            $model->created_at ? new DateTimeImmutable($model->created_at) : NULL,
            $model->updated_at ? new DateTimeImmutable($model->updated_at) : NULL,
        );
    }
}
