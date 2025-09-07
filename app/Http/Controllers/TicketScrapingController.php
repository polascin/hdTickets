<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Services\Enhanced\TicketFilteringService;
use App\Services\TicketScrapingService;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function count;
use function is_array;
use function is_string;

class TicketScrapingController extends Controller
{
    protected $scrapingService;

    public function __construct(TicketScrapingService $scrapingService)
    {
        $this->middleware('auth');
        $this->scrapingService = $scrapingService;
    }

    /**
     * Display enhanced scraped tickets dashboard
     */
    public function index(Request $request): View
    {
        try {
            // Use enhanced filtering service
            $filterService = TicketFilteringService::fromRequest($request);

            // Get paginated results
            $perPage = min($request->get('per_page', 20), 50); // Max 50 per page
            $tickets = $filterService->paginate($perPage);

            // Get statistics and facets for the current filter set
            $stats = $filterService->getStats();
            $facets = $filterService->getFacets();
            $activeFilters = $filterService->getAppliedFilters();

            // Get popular searches and suggestions
            $popularSearches = $this->getPopularSearches();
            $viewMode = $request->get('view', 'grid'); // grid or list

            return view('tickets.scraping.index', compact(
                'tickets',
                'stats',
                'facets',
                'activeFilters',
                'popularSearches',
                'viewMode'
            ));
        } catch (Exception $e) {
            Log::error('Error loading sports tickets page: ' . $e->getMessage(), [
                'user_id'      => Auth::id(),
                'request_data' => $request->all(),
                'error'        => $e->getTraceAsString(),
            ]);

            // Return error state with minimal data
            $tickets = collect()->paginate(0);
            $stats = $this->getDefaultStats();
            $facets = [];
            $activeFilters = [];
            $popularSearches = [];
            $viewMode = 'grid';

            return view('tickets.scraping.index', compact(
                'tickets',
                'stats',
                'facets',
                'activeFilters',
                'popularSearches',
                'viewMode'
            ))->with('error', 'Unable to load sports event tickets. Please try refreshing the page or contact support if the issue persists.');
        }
    }

    /**
     * Search for tickets
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keywords'    => 'required|string|max:255',
            'platforms'   => 'array',
            'platforms.*' => 'in:stubhub,ticketmaster,viagogo,seetickets,ticketek,eventim,axs,gigantic,skiddle,ticketone,stargreen,ticketswap,livenation',
            'max_price'   => 'nullable|numeric|min:0',
            'currency'    => 'string|size:3',
            'filters'     => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $results = $this->scrapingService->searchTickets(
                $request->keywords,
                [
                    'platforms' => $request->get('platforms', ['stubhub', 'ticketmaster', 'viagogo', 'seetickets', 'eventim', 'axs', 'gigantic', 'skiddle', 'ticketone', 'stargreen', 'ticketswap', 'livenation']),
                    'max_price' => $request->max_price,
                    'currency'  => $request->get('currency', 'USD'),
                    'filters'   => $request->get('filters', []),
                ],
            );

            $totalFound = 0;
            foreach ($results as $tickets) {
                $totalFound += is_array($tickets) ? count($tickets) : 0;
            }

            return response()->json([
                'success' => TRUE,
                'results' => $results,
                'summary' => [
                    'total_found'        => $totalFound,
                    'platforms_searched' => count($results),
                    'search_keywords'    => $request->keywords,
                    'timestamp'          => now()->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Ticket search error: ' . $e->getMessage(), [
                'keywords'    => $request->keywords,
                'user_id'     => Auth::id(),
                'stack_trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'       => FALSE,
                'message'       => 'Search failed. Please try again.',
                'error_details' => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Get Manchester United tickets
     */
    public function manchesterUnited(Request $request): JsonResponse
    {
        try {
            $maxPrice = $request->get('max_price');
            $dateRange = $request->get('date_range');

            $results = $this->scrapingService->searchManchesterUnitedTickets($maxPrice, $dateRange);

            return response()->json([
                'success' => TRUE,
                'results' => $results,
                'message' => "Found {$results['total_found']} Manchester United tickets",
            ]);
        } catch (Exception $e) {
            Log::error('Manchester United ticket search error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to search for Manchester United tickets',
            ], 500);
        }
    }

    /**
     * Get high-demand sports tickets
     */
    public function highDemandSports(Request $request)
    {
        // If this is an AJAX request, return JSON data
        if ($request->expectsJson() || $request->ajax()) {
            try {
                $filters = $request->only(['max_price', 'currency', 'venue', 'date_range']);
                $results = $this->scrapingService->searchHighDemandSportsTickets($filters);

                return response()->json([
                    'success' => TRUE,
                    'results' => $results,
                    'message' => "Found {$results['total_found']} high-demand sports tickets",
                ]);
            } catch (Exception $e) {
                Log::error('High-demand sports ticket search error: ' . $e->getMessage());

                return response()->json([
                    'success' => FALSE,
                    'message' => 'Failed to search for high-demand sports tickets',
                ], 500);
            }
        }

        // For web requests, return the view
        return view('tickets.scraping.high-demand-sports');
    }

    /**
     * Show specific scraped ticket
     *
     * @param mixed $ticket
     */
    public function show($ticket): \Illuminate\Contracts\View\View
    {
        // If $ticket is a string/ID, find the model
        if (is_string($ticket) || is_numeric($ticket)) {
            $ticket = ScrapedTicket::findOrFail($ticket);
        }

        $ticket->load(['metadata']);

        return view('tickets.scraping.show', compact('ticket'));
    }

    /**
     * Purchase ticket (redirect to platform)
     */
    public function purchase(Request $request, ScrapedTicket $ticket): \Illuminate\Http\RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'max_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->scrapingService->attemptAutoPurchase(
                $ticket->id,
                Auth::id(),
                $request->max_price,
            );

            return response()->json($result);
        } catch (Exception $e) {
            Log::error('Auto-purchase error: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'user_id'   => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Purchase attempt failed',
            ], 500);
        }
    }

    /**
     * List user's ticket alerts
     */
    public function alerts(): \Illuminate\View\View
    {
        $alerts = TicketAlert::forUser(Auth::id())
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('tickets.alerts.index', compact('alerts'));
    }

    /**
     * Create new ticket alert
     */
    public function createAlert(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'keywords'            => 'required|string|max:500',
            'platform'            => 'nullable|in:stubhub,ticketmaster,viagogo,funzone,test,seetickets,ticketek,eventim,axs,gigantic,skiddle,ticketone,stargreen,ticketswap,livenation',
            'max_price'           => 'nullable|numeric|min:0',
            'currency'            => 'string|size:3',
            'filters'             => 'array',
            'email_notifications' => 'boolean',
            'sms_notifications'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $alert = TicketAlert::create([
                'user_id'             => Auth::id(),
                'name'                => $request->name,
                'keywords'            => $request->keywords,
                'platform'            => $request->platform,
                'max_price'           => $request->max_price,
                'currency'            => $request->get('currency', 'USD'),
                'filters'             => $request->get('filters', []),
                'status'              => 'active',
                'email_notifications' => $request->boolean('email_notifications', TRUE),
                'sms_notifications'   => $request->boolean('sms_notifications', FALSE),
            ]);

            return response()->json([
                'success' => TRUE,
                'alert'   => $alert,
                'message' => 'Ticket alert created successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Alert creation error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create alert',
            ], 500);
        }
    }

    /**
     * Update ticket alert
     */
    public function updateAlert(Request $request, TicketAlert $alert): JsonResponse
    {
        // Ensure user owns the alert
        if ($alert->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'                => 'sometimes|string|max:255',
            'keywords'            => 'sometimes|string|max:500',
            'platform'            => 'nullable|in:stubhub,ticketmaster,viagogo,seetickets,ticketek,eventim,axs,gigantic,skiddle,ticketone,stargreen,ticketswap,livenation',
            'max_price'           => 'nullable|numeric|min:0',
            'currency'            => 'sometimes|string|size:3',
            'filters'             => 'array',
            'status'              => 'sometimes|string|in:active,paused,triggered,expired',
            'email_notifications' => 'boolean',
            'sms_notifications'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $alert->update($request->only([
                'name', 'keywords', 'platform', 'max_price', 'currency',
                'filters', 'status', 'email_notifications', 'sms_notifications',
            ]));

            return response()->json([
                'success' => TRUE,
                'alert'   => $alert->fresh(),
                'message' => 'Alert updated successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Alert update error: ' . $e->getMessage(), [
                'alert_id' => $alert->id,
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update alert',
            ], 500);
        }
    }

    /**
     * Delete ticket alert
     */
    public function deleteAlert(TicketAlert $alert): JsonResponse
    {
        // Ensure user owns the alert
        if ($alert->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $alert->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Alert deleted successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Alert deletion error: ' . $e->getMessage(), [
                'alert_id' => $alert->id,
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete alert',
            ], 500);
        }
    }

    /**
     * Get trending sports event tickets
     */
    public function trending(Request $request)
    {
        try {
            $limit = $request->get('limit', 20);

            // Get trending tickets from the service
            $tickets = $this->scrapingService->getTrendingManchesterUnitedTickets($limit);

            // If this is an API request, return JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => TRUE,
                    'tickets' => $tickets,
                    'count'   => $tickets->count(),
                ]);
            }

            // For web requests, return the view with trending tickets data
            $stats = [
                'total_trending' => $tickets->count(),
                'platforms'      => $tickets->groupBy('platform')->map->count(),
                'avg_price'      => round((float) ($tickets->avg('min_price') ?? 0), 2),
                'date_range'     => [
                    'from' => $tickets->min('event_date'),
                    'to'   => $tickets->max('event_date'),
                ],
            ];

            return view('tickets.scraping.trending', compact('tickets', 'stats'));
        } catch (Exception $e) {
            Log::error('Error loading trending tickets: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Failed to load trending tickets',
                ], 500);
            }

            // For web requests, return view with error state
            $tickets = collect();
            $stats = [
                'total_trending' => 0,
                'platforms'      => [],
                'avg_price'      => 0,
                'date_range'     => ['from' => NULL, 'to' => NULL],
            ];

            return view('tickets.scraping.trending', compact('tickets', 'stats'))
                ->with('error', 'Unable to load trending tickets at this time.');
        }
    }

    /**
     * Get best sports deals
     */
    public function bestDeals(Request $request): JsonResponse
    {
        $sport = $request->get('sport', 'football');
        $limit = $request->get('limit', 50);

        $deals = $this->scrapingService->getBestSportsDeals($sport, $limit);

        return response()->json([
            'success' => TRUE,
            'deals'   => $deals,
            'count'   => $deals->count(),
            'sport'   => $sport,
        ]);
    }

    /**
     * Manual check alerts
     */
    public function checkAlerts(): JsonResponse
    {
        try {
            $alertsChecked = $this->scrapingService->checkAlerts();

            return response()->json([
                'success'        => TRUE,
                'alerts_checked' => $alertsChecked,
                'message'        => "Checked {$alertsChecked} alerts",
            ]);
        } catch (Exception $e) {
            Log::error('Manual alert check error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to check alerts',
            ], 500);
        }
    }

    /**
     * Get scraping statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $period = $request->get('period', '24h'); // 24h, 7d, 30d

        $startDate = match ($period) {
            '7d'    => now()->subDays(7),
            '30d'   => now()->subDays(30),
            default => now()->subDay(),
        };

        $stats = [
            'total_tickets' => ScrapedTicket::where('scraped_at', '>=', $startDate)->count(),
            'by_platform'   => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->groupBy('platform')
                ->selectRaw('platform, count(*) as count')
                ->pluck('count', 'platform'),
            'high_demand_count' => ScrapedTicket::highDemand()
                ->where('scraped_at', '>=', $startDate)
                ->count(),
            'avg_price' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                ->whereNotNull('total_price')
                ->avg('total_price'),
            'price_ranges' => [
                'under_100' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                    ->where('total_price', '<', 100)->count(),
                '100_300' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                    ->whereBetween('total_price', [100, 300])->count(),
                'over_300' => ScrapedTicket::where('scraped_at', '>=', $startDate)
                    ->where('total_price', '>', 300)->count(),
            ],
        ];

        return response()->json([
            'success' => TRUE,
            'period'  => $period,
            'stats'   => $stats,
        ]);
    }

    /**
     * Generate statistics with error handling
     */
    private function generateStats(): array
    {
        try {
            return [
                'total_tickets'       => ScrapedTicket::count(),
                'available_tickets'   => ScrapedTicket::available()->count(),
                'high_demand_tickets' => ScrapedTicket::highDemand()->count(),
                'active_alerts'       => TicketAlert::active()->forUser(Auth::id())->count(),
                'recent_matches'      => TicketAlert::forUser(Auth::id())->sum('matches_found'),
                'platforms'           => ScrapedTicket::distinct('platform')->count('platform'),
                'avg_price'           => round((float) (ScrapedTicket::available()->avg('min_price') ?? 0), 2),
                'price_range'         => [
                    'min' => ScrapedTicket::available()->min('min_price') ?? 0,
                    'max' => ScrapedTicket::available()->max('max_price') ?? 0,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error generating stats: ' . $e->getMessage());

            return $this->getDefaultStats();
        }
    }

    /**
     * Get default stats when database error occurs
     */
    private function getDefaultStats(): array
    {
        return [
            'total_tickets'       => 0,
            'available_tickets'   => 0,
            'high_demand_tickets' => 0,
            'active_alerts'       => 0,
            'recent_matches'      => 0,
            'platforms'           => 0,
            'avg_price'           => 0,
            'price_range'         => ['min' => 0, 'max' => 0],
        ];
    }

    /**
     * AJAX endpoint for real-time filtering and sorting
     */
    public function ajaxFilter(Request $request): JsonResponse
    {
        try {
            $filterService = TicketFilteringService::fromRequest($request);
            $perPage = min($request->get('per_page', 20), 50);

            $tickets = $filterService->paginate($perPage);
            $stats = $filterService->getStats();
            $facets = $filterService->getFacets();

            return response()->json([
                'success'    => TRUE,
                'tickets'    => $tickets->items(),
                'pagination' => [
                    'current_page' => $tickets->currentPage(),
                    'last_page'    => $tickets->lastPage(),
                    'per_page'     => $tickets->perPage(),
                    'total'        => $tickets->total(),
                    'has_more'     => $tickets->hasMorePages(),
                ],
                'stats'           => $stats,
                'facets'          => $facets,
                'applied_filters' => $filterService->getAppliedFilters(),
            ]);
        } catch (Exception $e) {
            Log::error('AJAX filter error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Filter temporarily unavailable.',
                'error'   => config('app.debug') ? $e->getMessage() : NULL,
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        $request->validate([
            'term' => 'required|string|min:2|max:100',
        ]);

        try {
            $filterService = new TicketFilteringService();
            $suggestions = $filterService->getSearchSuggestions(
                $request->term,
                $request->get('limit', 10)
            );

            return response()->json([
                'success'     => TRUE,
                'suggestions' => $suggestions,
            ]);
        } catch (Exception $e) {
            Log::error('Search suggestions error: ' . $e->getMessage());

            return response()->json([
                'success'     => FALSE,
                'suggestions' => [],
            ]);
        }
    }

    /**
     * API endpoint for ticket details (mobile/API)
     */
    public function apiShow(ScrapedTicket $ticket): JsonResponse
    {
        try {
            $ticket->load(['homeTeam', 'awayTeam', 'venue', 'league', 'category']);

            $data = [
                'ticket'          => $ticket->toArray(),
                'price_history'   => $this->getTicketPriceHistory($ticket),
                'similar_tickets' => $this->getSimilarTickets($ticket, 3),
                'is_bookmarked'   => $this->isTicketBookmarked($ticket),
                'view_count'      => $this->getTicketViewCount($ticket),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            Log::error('API ticket details error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load ticket details',
            ], 500);
        }
    }

    /**
     * Export filtered tickets to various formats
     */
    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'format' => 'required|in:csv,xlsx,pdf',
            'limit'  => 'sometimes|integer|min:1|max:10000',
        ]);

        try {
            $filterService = TicketFilteringService::fromRequest($request);
            $format = $request->get('format', 'csv');
            $limit = $request->get('limit', 1000);

            $data = $filterService->export($format, $limit);
            $filename = 'sports-tickets-' . now()->format('Y-m-d-H-i-s') . '.' . $format;

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data, $filename);
                case 'xlsx':
                    return $this->exportToExcel($data, $filename);
                case 'pdf':
                    return $this->exportToPdf($data, $filename);
                default:
                    abort(400, 'Unsupported export format');
            }
        } catch (Exception $e) {
            Log::error('Export error: ' . $e->getMessage());
            abort(500, 'Export failed');
        }
    }

    /**
     * Bookmark/unbookmark a ticket
     */
    public function toggleBookmark(Request $request, ScrapedTicket $ticket): JsonResponse
    {
        try {
            $user = Auth::user();
            $isBookmarked = $user->bookmarkedTickets()->toggle($ticket->id);

            return response()->json([
                'success'       => TRUE,
                'is_bookmarked' => !empty($isBookmarked['attached']),
                'message'       => !empty($isBookmarked['attached'])
                    ? 'Ticket bookmarked successfully'
                    : 'Bookmark removed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Bookmark toggle error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to update bookmark',
            ], 500);
        }
    }

    /**
     * Get user's bookmarked tickets
     */
    public function bookmarked(Request $request): View
    {
        try {
            $user = Auth::user();
            $tickets = $user->bookmarkedTickets()
                ->with(['homeTeam', 'awayTeam', 'venue', 'league'])
                ->where('status', 'active')
                ->orderBy('pivot.created_at', 'desc')
                ->paginate(20);

            $stats = [
                'total_bookmarks'     => $user->bookmarkedTickets()->count(),
                'available_bookmarks' => $user->bookmarkedTickets()->where('is_available', TRUE)->count(),
            ];

            return view('tickets.scraping.bookmarked', compact('tickets', 'stats'));
        } catch (Exception $e) {
            Log::error('Bookmarked tickets error: ' . $e->getMessage());

            $tickets = collect()->paginate(0);
            $stats = ['total_bookmarks' => 0, 'available_bookmarks' => 0];

            return view('tickets.scraping.bookmarked', compact('tickets', 'stats'))
                ->with('error', 'Unable to load bookmarked tickets');
        }
    }

    // Helper methods

    /**
     * Get popular searches from cache
     */
    protected function getPopularSearches(int $limit = 10): array
    {
        return Cache::remember('popular_searches', 3600, function () use ($limit) {
            // This would be populated by tracking user searches
            return [
                'Manchester United',
                'Liverpool FC',
                'Wembley Stadium',
                'Champions League',
                'Premier League',
                'Arsenal',
                'Chelsea',
                'Tottenham',
                'Manchester City',
                'Leeds United',
            ];
        });
    }

    /**
     * Get ticket price history
     */
    protected function getTicketPriceHistory(ScrapedTicket $ticket): array
    {
        // This would require a price_history table to track changes over time
        // For now, return empty array or mock data
        return [
            ['date' => now()->subDays(7)->toDateString(), 'min_price' => $ticket->min_price * 1.1, 'max_price' => $ticket->max_price * 1.1],
            ['date' => now()->subDays(5)->toDateString(), 'min_price' => $ticket->min_price * 1.05, 'max_price' => $ticket->max_price * 1.05],
            ['date' => now()->subDays(3)->toDateString(), 'min_price' => $ticket->min_price, 'max_price' => $ticket->max_price],
            ['date' => now()->toDateString(), 'min_price' => $ticket->min_price, 'max_price' => $ticket->max_price],
        ];
    }

    /**
     * Get similar tickets
     */
    protected function getSimilarTickets(ScrapedTicket $ticket, int $limit = 6)
    {
        return ScrapedTicket::where('id', '!=', $ticket->id)
            ->where('sport', $ticket->sport)
            ->where(function ($q) use ($ticket) {
                $q->where('venue', $ticket->venue)
                  ->orWhere('team', $ticket->team)
                  ->orWhere('location', $ticket->location);
            })
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->limit($limit)
            ->get();
    }

    /**
     * Get related events at same venue
     */
    protected function getRelatedEvents(ScrapedTicket $ticket, int $limit = 4)
    {
        return ScrapedTicket::where('id', '!=', $ticket->id)
            ->where('venue', $ticket->venue)
            ->where('status', 'active')
            ->where('event_date', '>', now())
            ->limit($limit)
            ->get();
    }

    /**
     * Track ticket view for analytics
     */
    protected function trackTicketView(ScrapedTicket $ticket): void
    {
        try {
            // Increment view count (would need a views table or counter)
            Cache::increment('ticket_views_' . $ticket->id);

            // Log for analytics
            Log::info('Ticket viewed', [
                'ticket_id'  => $ticket->id,
                'user_id'    => Auth::id(),
                'user_agent' => request()->userAgent(),
                'ip'         => request()->ip(),
            ]);
        } catch (Exception $e) {
            Log::error('Error tracking ticket view: ' . $e->getMessage());
        }
    }

    /**
     * Check if ticket is bookmarked by user
     */
    protected function isTicketBookmarked(ScrapedTicket $ticket): bool
    {
        if (!Auth::check()) {
            return FALSE;
        }

        return Auth::user()->bookmarkedTickets()->where('scraped_ticket_id', $ticket->id)->exists();
    }

    /**
     * Get ticket view count
     */
    protected function getTicketViewCount(ScrapedTicket $ticket): int
    {
        return (int) Cache::get('ticket_views_' . $ticket->id, 0);
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv($data, string $filename): BinaryFileResponse
    {
        $path = storage_path('app/exports/' . $filename);

        $file = fopen($path, 'w');

        // Add headers
        if ($data->isNotEmpty()) {
            fputcsv($file, array_keys($data->first()));
        }

        // Add data
        foreach ($data as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        return response()->download($path)->deleteFileAfterSend();
    }

    /**
     * Export to Excel (placeholder - would require PhpSpreadsheet)
     */
    protected function exportToExcel($data, string $filename): BinaryFileResponse
    {
        // This would require PhpSpreadsheet package
        // For now, fallback to CSV
        return $this->exportToCsv($data, str_replace('.xlsx', '.csv', $filename));
    }

    /**
     * Export to PDF (placeholder - would require DomPDF or similar)
     */
    protected function exportToPdf($data, string $filename): BinaryFileResponse
    {
        // This would require PDF generation library
        // For now, fallback to CSV
        return $this->exportToCsv($data, str_replace('.pdf', '.csv', $filename));
    }
}
