<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Services\Scraping\PluginBasedScraperManager;
use App\Services\TicketScrapingService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use function count;

class ScrapingController extends Controller
{
    protected $scrapingService;

    protected $scraperManager;

    public function __construct(TicketScrapingService $scrapingService, PluginBasedScraperManager $scraperManager)
    {
        $this->scrapingService = $scrapingService;
        $this->scraperManager = $scraperManager;
    }

    /**
     * Get all scraped tickets with filtering and pagination
     */
    /**
     * Tickets
     */
    public function tickets(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platform'          => 'sometimes|string',
            'status'            => 'sometimes|string|in:active,sold_out,expired,cancelled,pending_verification,invalid',
            'sport'             => 'sometimes|string',
            'team'              => 'sometimes|string',
            'venue'             => 'sometimes|string',
            'location'          => 'sometimes|string',
            'min_price'         => 'sometimes|numeric|min:0',
            'max_price'         => 'sometimes|numeric|min:0',
            'is_available'      => 'sometimes|boolean',
            'is_high_demand'    => 'sometimes|boolean',
            'event_date_from'   => 'sometimes|date',
            'event_date_to'     => 'sometimes|date',
            'scraped_date_from' => 'sometimes|date',
            'scraped_date_to'   => 'sometimes|date',
            'search'            => 'sometimes|string|max:255',
            'category_id'       => 'sometimes|integer|exists:categories,id',
            'sort'              => 'sometimes|string|in:event_date,min_price,max_price,scraped_at,title,platform',
            'direction'         => 'sometimes|string|in:asc,desc',
            'per_page'          => 'sometimes|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $query = ScrapedTicket::with(['category']);

        // Apply filters
        if ($request->has('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('sport')) {
            $query->where('sport', 'LIKE', '%' . $request->sport . '%');
        }

        if ($request->has('team')) {
            $query->where('team', 'LIKE', '%' . $request->team . '%');
        }

        if ($request->has('venue')) {
            $query->where('venue', 'LIKE', '%' . $request->venue . '%');
        }

        if ($request->has('location')) {
            $query->where('location', 'LIKE', '%' . $request->location . '%');
        }

        if ($request->has('min_price') && $request->has('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        } elseif ($request->has('min_price')) {
            $query->priceRange($request->min_price);
        } elseif ($request->has('max_price')) {
            $query->priceRange(NULL, $request->max_price);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($request->has('is_high_demand')) {
            $query->where('is_high_demand', $request->boolean('is_high_demand'));
        }

        if ($request->has('event_date_from')) {
            $query->where('event_date', '>=', $request->event_date_from);
        }

        if ($request->has('event_date_to')) {
            $query->where('event_date', '<=', $request->event_date_to);
        }

        if ($request->has('scraped_date_from')) {
            $query->where('scraped_at', '>=', $request->scraped_date_from);
        }

        if ($request->has('scraped_date_to')) {
            $query->where('scraped_at', '<=', $request->scraped_date_to);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'LIKE', '%' . $search . '%')
                    ->orWhere('search_keyword', 'LIKE', '%' . $search . '%')
                    ->orWhere('team', 'LIKE', '%' . $search . '%')
                    ->orWhere('venue', 'LIKE', '%' . $search . '%');
            });
        }

        // Apply sorting
        $sortField = $request->get('sort', 'scraped_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $tickets = $query->paginate($perPage);

        return response()->json([
            'success' => TRUE,
            'data'    => $tickets->items(),
            'meta'    => [
                'current_page' => $tickets->currentPage(),
                'from'         => $tickets->firstItem(),
                'last_page'    => $tickets->lastPage(),
                'per_page'     => $tickets->perPage(),
                'to'           => $tickets->lastItem(),
                'total'        => $tickets->total(),
            ],
            'links' => [
                'first' => $tickets->url(1),
                'last'  => $tickets->url($tickets->lastPage()),
                'prev'  => $tickets->previousPageUrl(),
                'next'  => $tickets->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get scraped ticket by UUID
     */
    /**
     * Show
     */
    public function show(string $uuid): JsonResponse
    {
        $ticket = ScrapedTicket::with(['category'])
            ->where('uuid', $uuid)
            ->first();

        if (! $ticket) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Ticket not found',
            ], 404);
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $ticket,
        ]);
    }

    /**
     * Start scraping for specific platforms and criteria
     */
    /**
     * StartScraping
     */
    public function startScraping(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platforms'   => 'required|array|min:1',
            'platforms.*' => 'string|in:stubhub,ticketmaster,viagogo,tickpick,seatgeek,axs,eventbrite,livenation',
            'keywords'    => 'required|string|max:255',
            'location'    => 'sometimes|string|max:255',
            'max_price'   => 'sometimes|numeric|min:0',
            'priority'    => 'sometimes|string|in:low,normal,high',
            'limit'       => 'sometimes|integer|min:1|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $results = [];
            foreach ($request->platforms as $platform) {
                $searchParams = [
                    'keyword'   => $request->keywords,
                    'location'  => $request->get('location'),
                    'max_price' => $request->get('max_price'),
                    'limit'     => $request->get('limit', 50),
                ];

                $platformResults = $this->scraperManager->scrapeByPlatform($platform, $searchParams);
                $results[$platform] = [
                    'success'       => $platformResults['success'] ?? FALSE,
                    'tickets_found' => count($platformResults['tickets'] ?? []),
                    'message'       => $platformResults['message'] ?? 'Scraping completed',
                ];
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Scraping initiated successfully',
                'data'    => [
                    'job_id'          => uniqid('scrape_'),
                    'platforms'       => $results,
                    'total_platforms' => count($request->platforms),
                    'started_at'      => now()->toISOString(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to start scraping: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get scraping statistics and metrics
     */
    /**
     * Statistics
     */
    public function statistics(): JsonResponse
    {
        $cacheKey = 'scraping_statistics_' . now()->format('Y-m-d-H');

        $stats = Cache::remember($cacheKey, 3600, function () {
            $totalTickets = ScrapedTicket::count();
            $availableTickets = ScrapedTicket::where('is_available', TRUE)->count();
            $highDemandTickets = ScrapedTicket::where('is_high_demand', TRUE)->count();

            $platformStats = ScrapedTicket::selectRaw('platform, COUNT(*) as total, 
                COUNT(CASE WHEN is_available = 1 THEN 1 END) as available,
                AVG(min_price) as avg_min_price,
                AVG(max_price) as avg_max_price')
                ->groupBy('platform')
                ->get();

            $todayStats = ScrapedTicket::whereDate('scraped_at', today())
                ->selectRaw('COUNT(*) as today_total,
                    COUNT(CASE WHEN is_available = 1 THEN 1 END) as today_available,
                    COUNT(CASE WHEN is_high_demand = 1 THEN 1 END) as today_high_demand')
                ->first();

            $recentActivity = ScrapedTicket::where('scraped_at', '>=', now()->subHours(24))
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->orderBy('count', 'desc')
                ->get();

            return [
                'overview' => [
                    'total_tickets'       => $totalTickets,
                    'available_tickets'   => $availableTickets,
                    'high_demand_tickets' => $highDemandTickets,
                    'availability_rate'   => $totalTickets > 0 ? round(($availableTickets / $totalTickets) * 100, 2) : 0,
                ],
                'today' => [
                    'total_scraped'     => $todayStats->today_total ?? 0,
                    'available_found'   => $todayStats->today_available ?? 0,
                    'high_demand_found' => $todayStats->today_high_demand ?? 0,
                ],
                'platforms' => $platformStats->map(function ($platform) {
                    return [
                        'name'              => $platform->platform,
                        'total_tickets'     => $platform->total,
                        'available_tickets' => $platform->available,
                        'avg_min_price'     => round($platform->avg_min_price ?? 0, 2),
                        'avg_max_price'     => round($platform->avg_max_price ?? 0, 2),
                    ];
                }),
                'recent_activity' => $recentActivity->map(function ($activity) {
                    return [
                        'platform'            => $activity->platform,
                        'tickets_scraped_24h' => $activity->count,
                    ];
                }),
                'last_updated' => now()->toISOString(),
            ];
        });

        return response()->json([
            'success' => TRUE,
            'data'    => $stats,
        ]);
    }

    /**
     * Get available scraping platforms and their status
     */
    /**
     * Platforms
     */
    public function platforms(): JsonResponse
    {
        $platforms = [
            'stubhub' => [
                'name'              => 'StubHub',
                'status'            => 'active',
                'last_scrape'       => ScrapedTicket::where('platform', 'stubhub')->latest('scraped_at')->value('scraped_at'),
                'total_tickets'     => ScrapedTicket::where('platform', 'stubhub')->count(),
                'available_tickets' => ScrapedTicket::where('platform', 'stubhub')->where('is_available', TRUE)->count(),
            ],
            'ticketmaster' => [
                'name'              => 'Ticketmaster',
                'status'            => 'active',
                'last_scrape'       => ScrapedTicket::where('platform', 'ticketmaster')->latest('scraped_at')->value('scraped_at'),
                'total_tickets'     => ScrapedTicket::where('platform', 'ticketmaster')->count(),
                'available_tickets' => ScrapedTicket::where('platform', 'ticketmaster')->where('is_available', TRUE)->count(),
            ],
            'viagogo' => [
                'name'              => 'Viagogo',
                'status'            => 'active',
                'last_scrape'       => ScrapedTicket::where('platform', 'viagogo')->latest('scraped_at')->value('scraped_at'),
                'total_tickets'     => ScrapedTicket::where('platform', 'viagogo')->count(),
                'available_tickets' => ScrapedTicket::where('platform', 'viagogo')->where('is_available', TRUE)->count(),
            ],
            'tickpick' => [
                'name'              => 'TickPick',
                'status'            => 'active',
                'last_scrape'       => ScrapedTicket::where('platform', 'tickpick')->latest('scraped_at')->value('scraped_at'),
                'total_tickets'     => ScrapedTicket::where('platform', 'tickpick')->count(),
                'available_tickets' => ScrapedTicket::where('platform', 'tickpick')->where('is_available', TRUE)->count(),
            ],
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $platforms,
        ]);
    }

    /**
     * Delete old scraped tickets based on criteria
     */
    /**
     * Cleanup
     */
    public function cleanup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'older_than_days' => 'required|integer|min:1|max:365',
            'status'          => 'sometimes|array',
            'status.*'        => 'string|in:sold_out,expired,cancelled,invalid',
            'platform'        => 'sometimes|string',
            'dry_run'         => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $query = ScrapedTicket::where('scraped_at', '<', now()->subDays($request->older_than_days));

        if ($request->has('status')) {
            $query->whereIn('status', $request->status);
        }

        if ($request->has('platform')) {
            $query->where('platform', $request->platform);
        }

        $count = $query->count();

        if ($request->boolean('dry_run', FALSE)) {
            return response()->json([
                'success' => TRUE,
                'message' => 'Dry run completed',
                'data'    => [
                    'tickets_to_delete' => $count,
                    'criteria'          => $request->only(['older_than_days', 'status', 'platform']),
                ],
            ]);
        }

        $deleted = $query->delete();

        return response()->json([
            'success' => TRUE,
            'message' => 'Cleanup completed successfully',
            'data'    => [
                'tickets_deleted'  => $deleted,
                'cleanup_criteria' => $request->only(['older_than_days', 'status', 'platform']),
                'completed_at'     => now()->toISOString(),
            ],
        ]);
    }
}
