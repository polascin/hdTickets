<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\Venue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use function array_slice;

/**
 * Advanced Search & Filtering System API Controller
 *
 * Handles complex ticket search with multiple filters, sorting, and faceted search
 * Supports sports, teams, venues, leagues, platforms, pricing, and date filters
 */
class AdvancedSearchController extends Controller
{
    /**
     * Search tickets with advanced filters
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query'        => 'nullable|string|max:255',
            'sports'       => 'nullable|array',
            'sports.*'     => 'string',
            'teams'        => 'nullable|array',
            'teams.*'      => 'string',
            'venues'       => 'nullable|array',
            'venues.*'     => 'string',
            'leagues'      => 'nullable|array',
            'leagues.*'    => 'string',
            'platforms'    => 'nullable|array',
            'platforms.*'  => 'string',
            'availability' => 'nullable|string|in:all,available,limited,sold_out',
            'price_min'    => 'nullable|numeric|min:0',
            'price_max'    => 'nullable|numeric|min:0',
            'date_from'    => 'nullable|date',
            'date_to'      => 'nullable|date|after_or_equal:date_from',
            'sort_by'      => 'nullable|string|in:price_low,price_high,date_asc,date_desc,relevance,popularity',
            'page'         => 'nullable|integer|min:1',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

        $query = $this->buildSearchQuery($request);

        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

        $facets = $this->getFacets($request);

        return response()->json([
            'success' => TRUE,
            'data'    => [
                'tickets'    => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page'    => $tickets->lastPage(),
                    'per_page'     => $tickets->perPage(),
                    'total'        => $tickets->total(),
                ],
                'facets'          => $facets,
                'applied_filters' => $this->getAppliedFilters($request),
            ],
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2|max:100',
            'type'  => 'nullable|string|in:all,events,teams,venues,leagues',
        ]);

        $query = $request->get('query');
        $type = $request->get('type', 'all');

        $suggestions = [];

        if ($type === 'all' || $type === 'events') {
            $events = Event::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['name', 'sport', 'venue', 'date'])
                ->map(function ($event) {
                    return [
                        'type'     => 'event',
                        'title'    => $event->name,
                        'subtitle' => "{$event->venue} • {$event->date->format('M j, Y')}",
                        'category' => $event->sport,
                        'value'    => $event->name,
                    ];
                });

            $suggestions = array_merge($suggestions, $events->toArray());
        }

        if ($type === 'all' || $type === 'teams') {
            $teams = Team::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['name', 'sport', 'league'])
                ->map(function ($team) {
                    return [
                        'type'     => 'team',
                        'title'    => $team->name,
                        'subtitle' => "{$team->league} • {$team->sport}",
                        'category' => $team->sport,
                        'value'    => $team->name,
                    ];
                });

            $suggestions = array_merge($suggestions, $teams->toArray());
        }

        if ($type === 'all' || $type === 'venues') {
            $venues = Venue::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['name', 'city', 'capacity'])
                ->map(function ($venue) {
                    return [
                        'type'     => 'venue',
                        'title'    => $venue->name,
                        'subtitle' => "{$venue->city} • Capacity: {$venue->capacity}",
                        'category' => 'venue',
                        'value'    => $venue->name,
                    ];
                });

            $suggestions = array_merge($suggestions, $venues->toArray());
        }

        return response()->json([
            'success'     => TRUE,
            'suggestions' => array_slice($suggestions, 0, 10),
        ]);
    }

    /**
     * Get popular searches
     */
    public function popularSearches(): JsonResponse
    {
        $cacheKey = 'popular_searches';

        $data = Cache::remember($cacheKey, 3600, function () {
            return [
                'trending_searches' => [
                    'Manchester United tickets',
                    'Liverpool vs Arsenal',
                    'Wembley Stadium events',
                    'Premier League finals',
                    'Champions League tickets',
                ],
                'popular_events' => Event::where('date', '>=', now())
                    ->orderBy('popularity_score', 'desc')
                    ->limit(5)
                    ->get(['name', 'sport', 'venue', 'date'])
                    ->map(function ($event) {
                        return [
                            'name'  => $event->name,
                            'sport' => $event->sport,
                            'venue' => $event->venue,
                            'date'  => $event->date->format('M j'),
                        ];
                    }),
                'popular_teams' => Team::orderBy('followers_count', 'desc')
                    ->limit(8)
                    ->get(['name', 'sport', 'logo_url'])
                    ->map(function ($team) {
                        return [
                            'name'  => $team->name,
                            'sport' => $team->sport,
                            'logo'  => $team->logo_url,
                        ];
                    }),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $data,
        ]);
    }

    /**
     * Build search query with filters
     */
    private function buildSearchQuery(Request $request)
    {
        $query = Ticket::query()->with(['event', 'venue']);

        // Text search
        if ($searchTerm = $request->get('query')) {
            $query->where(function ($q) use ($searchTerm): void {
                $q->where('event_name', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('venue', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('event', function ($eventQuery) use ($searchTerm): void {
                        $eventQuery->where('description', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Sport filters
        if ($sports = $request->get('sports')) {
            $query->whereIn('sport', $sports);
        }

        // Team filters
        if ($teams = $request->get('teams')) {
            $query->where(function ($q) use ($teams): void {
                foreach ($teams as $team) {
                    $q->orWhere('event_name', 'LIKE', "%{$team}%");
                }
            });
        }

        // Venue filters
        if ($venues = $request->get('venues')) {
            $query->whereIn('venue', $venues);
        }

        // League filters
        if ($leagues = $request->get('leagues')) {
            $query->whereIn('league', $leagues);
        }

        // Platform filters
        if ($platforms = $request->get('platforms')) {
            $query->whereIn('platform', $platforms);
        }

        // Availability filter
        $availability = $request->get('availability');
        if ($availability && $availability !== 'all') {
            switch ($availability) {
                case 'available':
                    $query->where('is_available', TRUE)->where('quantity', '>', 0);

                    break;
                case 'limited':
                    $query->where('is_available', TRUE)->where('quantity', '<=', 5);

                    break;
                case 'sold_out':
                    $query->where('is_available', FALSE);

                    break;
            }
        }

        // Price range filter
        if ($priceMin = $request->get('price_min')) {
            $query->where('current_price', '>=', $priceMin);
        }
        if ($priceMax = $request->get('price_max')) {
            $query->where('current_price', '<=', $priceMax);
        }

        // Date range filter
        if ($dateFrom = $request->get('date_from')) {
            $query->where('event_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->where('event_date', '<=', $dateTo);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'relevance');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('current_price', 'asc');

                break;
            case 'price_high':
                $query->orderBy('current_price', 'desc');

                break;
            case 'date_asc':
                $query->orderBy('event_date', 'asc');

                break;
            case 'date_desc':
                $query->orderBy('event_date', 'desc');

                break;
            case 'popularity':
                $query->orderBy('views_count', 'desc');

                break;
            case 'relevance':
            default:
                // Default relevance sorting
                if ($request->get('query')) {
                    $query->orderByRaw('CASE WHEN event_name LIKE ? THEN 1 ELSE 2 END', ["%{$request->get('query')}%"]);
                }
                $query->orderBy('created_at', 'desc');

                break;
        }

        return $query;
    }

    /**
     * Get search facets for filtering
     */
    private function getFacets(Request $request): array
    {
        $baseQuery = $this->buildSearchQuery($request);

        return [
            'sports'       => $this->getSportFacets($baseQuery),
            'venues'       => $this->getVenueFacets($baseQuery),
            'platforms'    => $this->getPlatformFacets($baseQuery),
            'leagues'      => $this->getLeagueFacets($baseQuery),
            'price_range'  => $this->getPriceRangeFacets($baseQuery),
            'date_range'   => $this->getDateRangeFacets($baseQuery),
            'availability' => $this->getAvailabilityFacets($baseQuery),
        ];
    }

    /**
     * Get sport facets
     *
     * @param mixed $query
     */
    private function getSportFacets($query): array
    {
        return $query->select('sport', DB::raw('COUNT(*) as count'))
            ->groupBy('sport')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->sport,
                    'label' => ucfirst($item->sport),
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get venue facets
     *
     * @param mixed $query
     */
    private function getVenueFacets($query): array
    {
        return $query->select('venue', DB::raw('COUNT(*) as count'))
            ->groupBy('venue')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->venue,
                    'label' => $item->venue,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get platform facets
     *
     * @param mixed $query
     */
    private function getPlatformFacets($query): array
    {
        return $query->select('platform', DB::raw('COUNT(*) as count'))
            ->groupBy('platform')
            ->orderBy('count', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->platform,
                    'label' => $item->platform,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get league facets
     *
     * @param mixed $query
     */
    private function getLeagueFacets($query): array
    {
        return $query->select('league', DB::raw('COUNT(*) as count'))
            ->whereNotNull('league')
            ->groupBy('league')
            ->orderBy('count', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->league,
                    'label' => $item->league,
                    'count' => $item->count,
                ];
            })
            ->toArray();
    }

    /**
     * Get price range facets
     *
     * @param mixed $query
     */
    private function getPriceRangeFacets($query): array
    {
        $stats = $query->selectRaw('MIN(current_price) as min_price, MAX(current_price) as max_price, AVG(current_price) as avg_price')
            ->first();

        return [
            'min'     => (float) $stats->min_price,
            'max'     => (float) $stats->max_price,
            'average' => (float) $stats->avg_price,
            'ranges'  => [
                ['label' => 'Under £50', 'min' => 0, 'max' => 50, 'count' => $query->where('current_price', '<', 50)->count()],
                ['label' => '£50 - £100', 'min' => 50, 'max' => 100, 'count' => $query->whereBetween('current_price', [50, 100])->count()],
                ['label' => '£100 - £200', 'min' => 100, 'max' => 200, 'count' => $query->whereBetween('current_price', [100, 200])->count()],
                ['label' => 'Over £200', 'min' => 200, 'max' => NULL, 'count' => $query->where('current_price', '>', 200)->count()],
            ],
        ];
    }

    /**
     * Get date range facets
     *
     * @param mixed $query
     */
    private function getDateRangeFacets($query): array
    {
        return [
            'today'      => $query->whereDate('event_date', today())->count(),
            'this_week'  => $query->whereBetween('event_date', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => $query->whereBetween('event_date', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'next_month' => $query->whereBetween('event_date', [now()->addMonth()->startOfMonth(), now()->addMonth()->endOfMonth()])->count(),
        ];
    }

    /**
     * Get availability facets
     *
     * @param mixed $query
     */
    private function getAvailabilityFacets($query): array
    {
        return [
            'available' => $query->where('is_available', TRUE)->where('quantity', '>', 0)->count(),
            'limited'   => $query->where('is_available', TRUE)->where('quantity', '<=', 5)->count(),
            'sold_out'  => $query->where('is_available', FALSE)->count(),
        ];
    }

    /**
     * Get applied filters summary
     */
    private function getAppliedFilters(Request $request): array
    {
        $filters = [];

        if ($query = $request->get('query')) {
            $filters[] = ['type' => 'search', 'label' => "Search: {$query}", 'value' => $query];
        }

        if ($sports = $request->get('sports')) {
            foreach ($sports as $sport) {
                $filters[] = ['type' => 'sport', 'label' => "Sport: {$sport}", 'value' => $sport];
            }
        }

        if ($teams = $request->get('teams')) {
            foreach ($teams as $team) {
                $filters[] = ['type' => 'team', 'label' => "Team: {$team}", 'value' => $team];
            }
        }

        if ($venues = $request->get('venues')) {
            foreach ($venues as $venue) {
                $filters[] = ['type' => 'venue', 'label' => "Venue: {$venue}", 'value' => $venue];
            }
        }

        if ($priceMin = $request->get('price_min')) {
            $filters[] = ['type' => 'price_min', 'label' => "Min Price: £{$priceMin}", 'value' => $priceMin];
        }

        if ($priceMax = $request->get('price_max')) {
            $filters[] = ['type' => 'price_max', 'label' => "Max Price: £{$priceMax}", 'value' => $priceMax];
        }

        return $filters;
    }
}
