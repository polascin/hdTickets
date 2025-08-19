<?php declare(strict_types=1);

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

use function strlen;

class TicketLazyLoadController extends Controller
{
    /**
     * Load tickets with lazy loading and AJAX pagination
     */
    /**
     * LoadTickets
     */
    public function loadTickets(Request $request): JsonResponse
    {
        $page = $request->get('page', 1);
        $perPage = min($request->get('per_page', 20), 50); // Max 50 items per page
        $cacheKey = 'tickets_lazy_' . md5(serialize($request->all()));

        // Cache for 2 minutes to improve performance
        $result = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($request, $perPage) {
            $query = ScrapedTicket::query()
                ->with(['source'])
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('platform')) {
                $query->where('platform', 'like', '%' . $request->platform . '%');
            }

            if ($request->filled('keywords')) {
                $query->where(function ($q) use ($request): void {
                    $q->where('title', 'like', '%' . $request->keywords . '%')
                        ->orWhere('description', 'like', '%' . $request->keywords . '%');
                });
            }

            if ($request->filled('max_price')) {
                $query->where(function ($q) use ($request): void {
                    $q->where('max_price', '<=', $request->max_price)
                        ->orWhere('min_price', '<=', $request->max_price);
                });
            }

            if ($request->filled('min_price')) {
                $query->where(function ($q) use ($request): void {
                    $q->where('min_price', '>=', $request->min_price)
                        ->orWhere('max_price', '>=', $request->min_price);
                });
            }

            if ($request->filled('availability')) {
                $query->where('is_available', $request->boolean('availability'));
            }

            if ($request->filled('high_demand')) {
                $query->where('is_high_demand', $request->boolean('high_demand'));
            }

            if ($request->filled('date_from')) {
                $query->where('event_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('event_date', '<=', $request->date_to);
            }

            return $query->paginate($perPage);
        });

        return response()->json([
            'success'    => TRUE,
            'data'       => $result->items(),
            'pagination' => [
                'current_page' => $result->currentPage(),
                'last_page'    => $result->lastPage(),
                'per_page'     => $result->perPage(),
                'total'        => $result->total(),
                'has_more'     => $result->hasMorePages(),
            ],
            'cache_hit' => Cache::has($cacheKey),
        ]);
    }

    /**
     * Load dashboard statistics with lazy loading
     */
    /**
     * LoadDashboardStats
     */
    public function loadDashboardStats(Request $request): JsonResponse
    {
        $cacheKey = 'dashboard_stats_lazy';

        $stats = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return [
                'scraped_tickets'     => ScrapedTicket::count(),
                'available_tickets'   => ScrapedTicket::where('is_available', TRUE)->count(),
                'high_demand_tickets' => ScrapedTicket::where('is_high_demand', TRUE)->count(),
                'platforms_monitored' => ScrapedTicket::distinct('platform')->count(),
                'today_tickets'       => ScrapedTicket::whereDate('created_at', today())->count(),
                'this_week_tickets'   => ScrapedTicket::whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ])->count(),
                'avg_price' => ScrapedTicket::whereNotNull('min_price')
                    ->avg('min_price'),
                'price_ranges' => [
                    'under_50'   => ScrapedTicket::where('min_price', '<', 50)->count(),
                    '50_to_100'  => ScrapedTicket::whereBetween('min_price', [50, 100])->count(),
                    '100_to_200' => ScrapedTicket::whereBetween('min_price', [100, 200])->count(),
                    'above_200'  => ScrapedTicket::where('min_price', '>', 200)->count(),
                ],
                'platform_breakdown' => ScrapedTicket::select('platform')
                    ->selectRaw('count(*) as count')
                    ->groupBy('platform')
                    ->pluck('count', 'platform')
                    ->toArray(),
            ];
        });

        return response()->json([
            'success'    => TRUE,
            'stats'      => $stats,
            'updated_at' => now()->toISOString(),
        ]);
    }

    /**
     * Search tickets with autocomplete
     */
    /**
     * SearchTickets
     */
    public function searchTickets(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 20);

        if (strlen($query) < 2) {
            return response()->json([
                'success' => TRUE,
                'results' => [],
            ]);
        }

        $cacheKey = 'ticket_search_' . md5($query . $limit);

        $results = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($query, $limit) {
            /** @phpstan-ignore-next-line */
            return ScrapedTicket::where('title', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->orWhere('venue', 'like', '%' . $query . '%')
                ->select('id', 'title', 'venue', 'event_date', 'min_price', 'max_price', 'currency', 'platform')
                ->limit($limit)
                ->get()
                ->map(function ($ticket) {
                    return [
                        'id'       => $ticket->id,
                        'title'    => $ticket->title,
                        'venue'    => $ticket->venue,
                        'date'     => $ticket->event_date ? $ticket->event_date->format('M d, Y') : 'TBD',
                        'price'    => $this->formatPrice($ticket),
                        'platform' => $ticket->platform,
                        'url'      => route('tickets.scraping.show', $ticket),
                    ];
                });
        });

        return response()->json([
            'success' => TRUE,
            'results' => $results,
        ]);
    }

    /**
     * Load more tickets for infinite scroll
     */
    /**
     * LoadMore
     */
    public function loadMore(Request $request): JsonResponse
    {
        $lastId = $request->get('last_id', 0);
        $perPage = min($request->get('per_page', 20), 50);

        $tickets = ScrapedTicket::where('id', '<', $lastId)
            ->orderBy('id', 'desc')
            ->limit($perPage)
            ->get();

        return response()->json([
            'success'  => TRUE,
            'data'     => $tickets,
            'has_more' => $tickets->count() === $perPage,
            'last_id'  => $tickets->last()?->id ?? 0,
        ]);
    }

    /**
     * Format ticket price for display
     *
     * @param mixed $ticket
     */
    /**
     * FormatPrice
     */
    private function formatPrice(App\Models\Ticket $ticket): string
    {
        if ($ticket->min_price && $ticket->max_price) {
            return $ticket->currency . ' ' . number_format($ticket->min_price, 2) . ' - ' . number_format($ticket->max_price, 2);
        }
        if ($ticket->max_price) {
            return $ticket->currency . ' ' . number_format($ticket->max_price, 2);
        }
        if ($ticket->min_price) {
            return $ticket->currency . ' ' . number_format($ticket->min_price, 2);
        }

        return 'Price on request';
    }
}
