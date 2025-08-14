<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Services\TicketScrapingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function count;

class TicketScrapingController extends Controller
{
    protected $scrapingService;

    public function __construct(TicketScrapingService $scrapingService)
    {
        $this->middleware('auth');
        $this->scrapingService = $scrapingService;
    }

    /**
     * Display scraped tickets dashboard
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $query = ScrapedTicket::query()
            ->where('event_date', '>', now())
            ->orderBy('scraped_at', 'desc');

        // Apply filters
        if ($request->filled('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->filled('keywords')) {
            $query->forEvent($request->keywords);
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->boolean('high_demand_only')) {
            $query->highDemand();
        }

        $tickets = $query->available()->paginate(20);

        // Get recent stats
        $stats = [
            'total_tickets'       => ScrapedTicket::count(),
            'high_demand_tickets' => ScrapedTicket::highDemand()->count(),
            'active_alerts'       => TicketAlert::active()->forUser(Auth::id())->count(),
            'recent_matches'      => TicketAlert::forUser(Auth::id())->sum('matches_found'),
        ];

        return view('tickets.scraping.index', compact('tickets', 'stats'));
    }

    /**
     * Search for tickets
     */
    /**
     * Search
     */
    public function search(Request $request): Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'keywords'    => 'required|string|max:255',
            'platforms'   => 'array',
            'platforms.*' => 'in:stubhub,ticketmaster,viagogo',
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
                    'platforms' => $request->get('platforms', ['stubhub', 'ticketmaster', 'viagogo']),
                    'max_price' => $request->max_price,
                    'currency'  => $request->get('currency', 'USD'),
                    'filters'   => $request->get('filters', []),
                ],
            );

            $totalFound = array_sum(array_map('count', $results));

            return response()->json([
                'success' => TRUE,
                'results' => $results,
                'summary' => [
                    'total_found'        => $totalFound,
                    'platforms_searched' => count($results),
                    'search_keywords'    => $request->keywords,
                    'timestamp'          => now(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Ticket search error: ' . $e->getMessage(), [
                'keywords' => $request->keywords,
                'user_id'  => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Search failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Get Manchester United tickets
     */
    /**
     * ManchesterUnited
     */
    public function manchesterUnited(Request $request): Illuminate\Http\RedirectResponse
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
    /**
     * HighDemandSports
     */
    public function highDemandSports(Request $request): Illuminate\Http\RedirectResponse
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
    /**
     * Show
     */
    public function show($ticket): Illuminate\Contracts\View\View
    {
        $ticket->load(['metadata']);

        return view('tickets.scraping.show', compact('ticket'));
    }

    /**
     * Purchase ticket (redirect to platform)
     */
    /**
     * Purchase
     */
    public function purchase(Request $request, ScrapedTicket $ticket): Illuminate\Http\RedirectResponse
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
    /**
     * Alerts
     */
    public function alerts(Request $request): Illuminate\Http\RedirectResponse
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
    /**
     * CreateAlert
     */
    public function createAlert(): Illuminate\Contracts\View\View
    {
        $validator = Validator::make($request->all(), [
            'name'                => 'required|string|max:255',
            'keywords'            => 'required|string|max:500',
            'platform'            => 'nullable|in:stubhub,ticketmaster,viagogo',
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
    /**
     * UpdateAlert
     */
    public function updateAlert(): Illuminate\Http\RedirectResponse
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
            'platform'            => 'nullable|in:stubhub,ticketmaster,viagogo',
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
    /**
     * DeleteAlert
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
     * Get trending Manchester United tickets
     */
    /**
     * Trending
     */
    public function trending(Request $request): Illuminate\Http\RedirectResponse
    {
        $limit = $request->get('limit', 20);
        $tickets = $this->scrapingService->getTrendingManchesterUnitedTickets($limit);

        return response()->json([
            'success' => TRUE,
            'tickets' => $tickets,
            'count'   => $tickets->count(),
        ]);
    }

    /**
     * Get best sports deals
     */
    /**
     * BestDeals
     */
    public function bestDeals(Request $request): Illuminate\Http\RedirectResponse
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
    /**
     * CheckAlerts
     */
    public function checkAlerts(Request $request): Illuminate\Http\RedirectResponse
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
    /**
     * Stats
     */
    public function stats(Request $request): Illuminate\Http\RedirectResponse
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
}
