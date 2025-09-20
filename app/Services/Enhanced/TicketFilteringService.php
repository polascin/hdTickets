<?php declare(strict_types=1);

namespace App\Services\Enhanced;

use App\Models\ScrapedTicket;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function array_slice;
use function in_array;
use function strlen;

/**
 * Advanced Ticket Filtering Service
 *
 * Provides a fluent interface for building complex ticket search queries
 * with caching, aggregations, and performance optimizations.
 */
class TicketFilteringService
{
    protected Builder $query;

    protected array $appliedFilters = [];

    protected array $aggregations = [];

    protected int $cacheTimeMinutes = 15;

    public function __construct()
    {
        $this->query = ScrapedTicket::query();
        $this->applyDefaultConstraints();
    }

    /**
     * Create a new instance from HTTP request
     */
    public static function fromRequest(Request $request): self
    {
        $service = new self();

        // Apply search filters
        if ($request->filled('keywords')) {
            $service->search($request->keywords);
        }

        if ($request->filled('platform')) {
            $service->byPlatform($request->platform);
        }

        if ($request->filled('sport')) {
            $service->bySport($request->sport);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $service->priceRange(
                $request->filled('min_price') ? (float) $request->min_price : NULL,
                $request->filled('max_price') ? (float) $request->max_price : NULL,
            );
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $service->dateRange(
                $request->filled('date_from') ? Carbon::parse($request->date_from) : NULL,
                $request->filled('date_to') ? Carbon::parse($request->date_to) : NULL,
            );
        }

        if ($request->filled('venue')) {
            $service->byVenue($request->venue);
        }

        if ($request->filled('location')) {
            $service->byLocation($request->location);
        }

        if ($request->boolean('high_demand_only')) {
            $service->highDemandOnly();
        }

        if ($request->boolean('available_only')) {
            $service->availableOnly();
        }

        if ($request->filled('team')) {
            $service->byTeam($request->team);
        }

        if ($request->filled('league')) {
            $service->byLeague($request->league);
        }

        // Apply sorting
        if ($request->filled('sort_by')) {
            $service->sortBy($request->sort_by, $request->get('sort_dir', 'desc'));
        }

        return $service;
    }

    /**
     * Full-text search across multiple fields
     */
    public function search(string $keywords): self
    {
        $keywords = trim($keywords);
        if ($keywords === '' || $keywords === '0') {
            return $this;
        }

        $this->appliedFilters['search'] = $keywords;

        // Use full-text search if available, otherwise use LIKE queries
        $this->query->where(function ($q) use ($keywords): void {
            $searchTerms = explode(' ', $keywords);

            foreach ($searchTerms as $term) {
                $term = '%' . $term . '%';
                $q->where(function ($subQuery) use ($term): void {
                    $subQuery->where('title', 'LIKE', $term)
                        ->orWhere('venue', 'LIKE', $term)
                        ->orWhere('location', 'LIKE', $term)
                        ->orWhere('team', 'LIKE', $term)
                        ->orWhere('sport', 'LIKE', $term)
                        ->orWhere('search_keyword', 'LIKE', $term);
                });
            }
        });

        return $this;
    }

    /**
     * Filter by platform
     */
    public function byPlatform(string $platform): self
    {
        $allowedPlatforms = [
            'stubhub', 'ticketmaster', 'viagogo', 'seetickets', 'ticketek',
            'eventim', 'axs', 'gigantic', 'skiddle', 'ticketone', 'stargreen',
            'ticketswap', 'livenation',
        ];

        if (in_array($platform, $allowedPlatforms, TRUE)) {
            $this->query->where('platform', $platform);
            $this->appliedFilters['platform'] = $platform;
        }

        return $this;
    }

    /**
     * Filter by sport
     */
    public function bySport(string $sport): self
    {
        $this->query->where('sport', $sport);
        $this->appliedFilters['sport'] = $sport;

        return $this;
    }

    /**
     * Filter by price range with currency support
     */
    public function priceRange(?float $minPrice = NULL, ?float $maxPrice = NULL, string $currency = 'USD'): self
    {
        if ($minPrice !== NULL || $maxPrice !== NULL) {
            $this->query->where('currency', $currency);

            if ($minPrice !== NULL) {
                $this->query->where(function ($q) use ($minPrice): void {
                    $q->where('min_price', '>=', $minPrice)
                        ->orWhere('max_price', '>=', $minPrice);
                });
                $this->appliedFilters['min_price'] = $minPrice;
            }

            if ($maxPrice !== NULL) {
                $this->query->where('min_price', '<=', $maxPrice);
                $this->appliedFilters['max_price'] = $maxPrice;
            }
        }

        return $this;
    }

    /**
     * Filter by date range
     */
    public function dateRange(?Carbon $dateFrom = NULL, ?Carbon $dateTo = NULL): self
    {
        if ($dateFrom instanceof Carbon || $dateTo instanceof Carbon) {
            if ($dateFrom instanceof Carbon) {
                $this->query->where('event_date', '>=', $dateFrom->startOfDay());
                $this->appliedFilters['date_from'] = $dateFrom->toDateString();
            }

            if ($dateTo instanceof Carbon) {
                $this->query->where('event_date', '<=', $dateTo->endOfDay());
                $this->appliedFilters['date_to'] = $dateTo->toDateString();
            }
        }

        return $this;
    }

    /**
     * Filter by venue
     */
    public function byVenue(string $venue): self
    {
        $this->query->where('venue', 'LIKE', '%' . $venue . '%');
        $this->appliedFilters['venue'] = $venue;

        return $this;
    }

    /**
     * Filter by location (city/region)
     */
    public function byLocation(string $location): self
    {
        $this->query->where('location', 'LIKE', '%' . $location . '%');
        $this->appliedFilters['location'] = $location;

        return $this;
    }

    /**
     * Filter by team (home or away)
     */
    public function byTeam(string $team): self
    {
        $this->query->where(function ($q) use ($team): void {
            $q->where('team', 'LIKE', '%' . $team . '%')
                ->orWhere('title', 'LIKE', '%' . $team . '%')
                ->orWhere('search_keyword', 'LIKE', '%' . $team . '%');
        });

        $this->appliedFilters['team'] = $team;

        return $this;
    }

    /**
     * Filter by league
     */
    public function byLeague(string $league): self
    {
        $this->query->where(function ($q) use ($league): void {
            $q->where('title', 'LIKE', '%' . $league . '%')
                ->orWhere('search_keyword', 'LIKE', '%' . $league . '%')
                ->orWhere('sport', 'LIKE', '%' . $league . '%');
        });

        $this->appliedFilters['league'] = $league;

        return $this;
    }

    /**
     * Filter for high demand tickets only
     */
    public function highDemandOnly(): self
    {
        $this->query->where('is_high_demand', TRUE);
        $this->appliedFilters['high_demand_only'] = TRUE;

        return $this;
    }

    /**
     * Filter for available tickets only
     */
    public function availableOnly(): self
    {
        $this->query->where('is_available', TRUE);
        $this->appliedFilters['available_only'] = TRUE;

        return $this;
    }

    /**
     * Filter by freshness (recently scraped)
     */
    public function recentlyScraped(int $hoursAgo = 24): self
    {
        $this->query->where('scraped_at', '>=', now()->subHours($hoursAgo));
        $this->appliedFilters['freshness_hours'] = $hoursAgo;

        return $this;
    }

    /**
     * Apply sorting
     */
    public function sortBy(string $sortField, string $direction = 'desc'): self
    {
        $allowedSorts = [
            'scraped_at', 'event_date', 'min_price', 'max_price',
            'title', 'platform', 'availability', 'venue', 'predicted_demand',
        ];

        $direction = in_array(strtolower($direction), ['asc', 'desc'], TRUE)
            ? strtolower($direction)
            : 'desc';

        if (in_array($sortField, $allowedSorts, TRUE)) {
            match ($sortField) {
                'min_price', 'max_price' => $this->query->orderByRaw("COALESCE({$sortField}, 999999) {$direction}"),
                'availability' => $this->query->orderByRaw('CASE WHEN is_available = 1 THEN 0 ELSE 1 END')
                    ->orderBy('scraped_at', 'desc'),
                'platform' => $this->query->orderBy('platform', 'asc')
                    ->orderBy('scraped_at', 'desc'),
                'predicted_demand' => $this->query->orderByRaw("COALESCE(predicted_demand, 0) {$direction}"),
                default            => $this->query->orderBy($sortField, $direction),
            };
            $this->appliedFilters['sort_by'] = $sortField;
            $this->appliedFilters['sort_dir'] = $direction;
        }

        return $this;
    }

    /**
     * Get results with pagination
     */
    public function paginate(int $perPage = 20)
    {
        // Add relationships for better performance
        $this->query->with(['category']);

        return $this->query->paginate($perPage);
    }

    /**
     * Get all results (use carefully)
     */
    public function get()
    {
        $this->query->with(['category']);

        return $this->query->get();
    }

    /**
     * Get count of matching tickets
     */
    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * Get applied filters
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Generate statistics for current filter set
     */
    public function getStats(): array
    {
        $cacheKey = 'ticket_stats_' . md5(serialize($this->appliedFilters));

        return Cache::remember($cacheKey, $this->cacheTimeMinutes, function (): array {
            $baseQuery = clone $this->query;

            $stats = [
                'total_count'        => $baseQuery->count(),
                'available_count'    => (clone $baseQuery)->where('is_available', TRUE)->count(),
                'high_demand_count'  => (clone $baseQuery)->where('is_high_demand', TRUE)->count(),
                'avg_price'          => 0,
                'min_price'          => 0,
                'max_price'          => 0,
                'platform_breakdown' => [],
                'sport_breakdown'    => [],
                'upcoming_events'    => 0,
            ];

            // Price statistics
            $priceStats = (clone $baseQuery)
                ->selectRaw('
                    AVG(COALESCE((min_price + max_price) / 2, min_price, max_price)) as avg_price,
                    MIN(COALESCE(min_price, max_price)) as min_price,
                    MAX(COALESCE(max_price, min_price)) as max_price
                ')
                ->where(function ($q): void {
                    $q->whereNotNull('min_price')->orWhereNotNull('max_price');
                })
                ->first();

            if ($priceStats) {
                $stats['avg_price'] = round((float) $priceStats->avg_price, 2);
                $stats['min_price'] = round((float) $priceStats->min_price, 2);
                $stats['max_price'] = round((float) $priceStats->max_price, 2);
            }

            // Platform breakdown
            $stats['platform_breakdown'] = (clone $baseQuery)
                ->select('platform', DB::raw('COUNT(*) as count'))
                ->groupBy('platform')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'platform')
                ->toArray();

            // Sport breakdown
            $stats['sport_breakdown'] = (clone $baseQuery)
                ->select('sport', DB::raw('COUNT(*) as count'))
                ->groupBy('sport')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'sport')
                ->toArray();

            // Upcoming events count
            $stats['upcoming_events'] = (clone $baseQuery)
                ->where('event_date', '>=', now())
                ->where('event_date', '<=', now()->addMonths(3))
                ->count();

            return $stats;
        });
    }

    /**
     * Get suggestions for autocomplete
     */
    public function getSearchSuggestions(string $term = '', int $limit = 10): array
    {
        $cacheKey = 'search_suggestions_' . md5($term);

        return Cache::remember($cacheKey, 60, function () use ($term, $limit): array {
            $suggestions = [];

            if (strlen($term) >= 2) {
                $term = '%' . $term . '%';

                // Team suggestions
                $teams = ScrapedTicket::select('team')
                    ->where('team', 'LIKE', $term)
                    ->whereNotNull('team')
                    ->distinct()
                    ->limit($limit)
                    ->pluck('team')
                    ->filter()
                    ->map(fn ($team): array => ['type' => 'team', 'value' => $team])
                    ->toArray();

                // Venue suggestions
                $venues = ScrapedTicket::select('venue')
                    ->where('venue', 'LIKE', $term)
                    ->whereNotNull('venue')
                    ->distinct()
                    ->limit($limit)
                    ->pluck('venue')
                    ->filter()
                    ->map(fn ($venue): array => ['type' => 'venue', 'value' => $venue])
                    ->toArray();

                // Sport suggestions
                $sports = ScrapedTicket::select('sport')
                    ->where('sport', 'LIKE', $term)
                    ->distinct()
                    ->limit($limit)
                    ->pluck('sport')
                    ->filter()
                    ->map(fn ($sport): array => ['type' => 'sport', 'value' => ucfirst($sport)])
                    ->toArray();

                $suggestions = array_merge($teams, $venues, $sports);
                $suggestions = array_slice($suggestions, 0, $limit);
            }

            return $suggestions;
        });
    }

    /**
     * Get faceted search data
     */
    public function getFacets(): array
    {
        $cacheKey = 'ticket_facets_' . md5(serialize($this->appliedFilters));

        return Cache::remember($cacheKey, $this->cacheTimeMinutes, function (): array {
            $baseQuery = clone $this->query;

            return [
                'platforms' => (clone $baseQuery)
                    ->select('platform', DB::raw('COUNT(*) as count'))
                    ->groupBy('platform')
                    ->orderByDesc('count')
                    ->get()
                    ->mapWithKeys(fn ($item): array => [$item->platform => $item->count])
                    ->toArray(),

                'sports' => (clone $baseQuery)
                    ->select('sport', DB::raw('COUNT(*) as count'))
                    ->groupBy('sport')
                    ->orderByDesc('count')
                    ->get()
                    ->mapWithKeys(fn ($item): array => [$item->sport => $item->count])
                    ->toArray(),

                'price_ranges' => [
                    'under_50' => (clone $baseQuery)->where('min_price', '<', 50)->count(),
                    '50_100'   => (clone $baseQuery)->whereBetween('min_price', [50, 100])->count(),
                    '100_250'  => (clone $baseQuery)->whereBetween('min_price', [100, 250])->count(),
                    '250_500'  => (clone $baseQuery)->whereBetween('min_price', [250, 500])->count(),
                    'over_500' => (clone $baseQuery)->where('min_price', '>', 500)->count(),
                ],

                'availability' => [
                    'available' => (clone $baseQuery)->where('is_available', TRUE)->count(),
                    'sold_out'  => (clone $baseQuery)->where('is_available', FALSE)->count(),
                ],

                'demand' => [
                    'high_demand' => (clone $baseQuery)->where('is_high_demand', TRUE)->count(),
                    'normal'      => (clone $baseQuery)->where('is_high_demand', FALSE)->count(),
                ],
            ];
        });
    }

    /**
     * Export results to various formats
     */
    public function export(string $format = 'csv', int $limit = 1000): Collection
    {
        $tickets = $this->query
            ->with(['homeTeam', 'awayTeam', 'venue', 'league'])
            ->limit($limit)
            ->get();

        return $tickets->map(fn ($ticket): array => [
            'id'             => $ticket->id,
            'title'          => $ticket->title,
            'platform'       => $ticket->platform,
            'sport'          => $ticket->sport,
            'venue'          => $ticket->venue,
            'location'       => $ticket->location,
            'event_date'     => $ticket->event_date?->toDateTimeString(),
            'min_price'      => $ticket->min_price,
            'max_price'      => $ticket->max_price,
            'currency'       => $ticket->currency,
            'is_available'   => $ticket->is_available ? 'Yes' : 'No',
            'is_high_demand' => $ticket->is_high_demand ? 'Yes' : 'No',
            'scraped_at'     => $ticket->scraped_at?->toDateTimeString(),
            'ticket_url'     => $ticket->ticket_url,
        ]);
    }

    /**
     * Clear filter cache
     */
    public function clearCache(): void
    {
        Cache::tags(['ticket_filters', 'ticket_stats'])->flush();
    }

    /**
     * Apply default constraints (active tickets, future events)
     */
    protected function applyDefaultConstraints(): self
    {
        $this->query->where('status', 'active')
            ->where(function ($q): void {
                $q->whereNull('event_date')
                    ->orWhere('event_date', '>', now());
            });

        return $this;
    }
}
