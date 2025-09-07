<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\TicketApiManager;
use App\Models\ScrapedTicket;
use App\Events\TicketPriceChanged;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function count;

class TicketApiController extends Controller
{
    protected $apiManager;

    public function __construct(TicketApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    /**
     * Show API integration dashboard
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $availablePlatforms = $this->apiManager->getAvailablePlatforms();

        return view('ticket-api.index', compact('availablePlatforms'));
    }

    /**
     * Search events via API
     */
    /**
     * Search
     */
    public function search(Request $request): Illuminate\Http\JsonResponse
    {
        $request->validate([
            'query'       => 'required|string|max:255',
            'city'        => 'nullable|string|max:100',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date|after_or_equal:date_from',
            'platforms'   => 'nullable|array',
            'platforms.*' => 'string|in:' . implode(',', $this->apiManager->getAvailablePlatforms()),
        ]);

        $criteria = $this->buildSearchCriteria($request);
        $platforms = $request->input('platforms', []);

        try {
            $results = $this->apiManager->searchEvents($criteria, $platforms);

            return response()->json([
                'success' => TRUE,
                'data'    => $results,
                'summary' => $this->generateSearchSummary($results),
            ]);
        } catch (Exception $e) {
            Log::error('API search failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Search failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event details from specific platform
     */
    /**
     * Get  event
     */
    public function getEvent(Request $request, string $platform, string $eventId): Illuminate\Http\RedirectResponse
    {
        try {
            $event = $this->apiManager->getEvent($platform, $eventId);

            if (!$event) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Event not found',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => $event,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get event details', [
                'platform' => $platform,
                'event_id' => $eventId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get event details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import events from APIs to database
     */
    /**
     * ImportEvents
     */
    public function importEvents(Request $request): Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'query'       => 'required|string|max:255',
            'platforms'   => 'required|array|min:1',
            'platforms.*' => 'string|in:' . implode(',', $this->apiManager->getAvailablePlatforms()),
            'save_to_db'  => 'boolean',
        ]);

        $criteria = $this->buildSearchCriteria($request);
        $platforms = $request->input('platforms');

        try {
            // Enable auto-save temporarily if requested
            $originalAutoSave = config('ticket_apis.auto_save', FALSE);
            if ($request->boolean('save_to_db')) {
                config(['ticket_apis.auto_save' => TRUE]);
            }

            $results = $this->apiManager->searchEvents($criteria, $platforms);

            // Restore original auto-save setting
            config(['ticket_apis.auto_save' => $originalAutoSave]);

            $importedCount = 0;
            foreach ($results as $platformResults) {
                $importedCount += count($platformResults);
            }

            return response()->json([
                'success' => TRUE,
                'message' => "Successfully imported {$importedCount} events",
                'data'    => $results,
                'summary' => $this->generateSearchSummary($results),
            ]);
        } catch (Exception $e) {
            Log::error('Event import failed', [
                'criteria' => $criteria,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test API connections
     */
    public function testConnections()
    {
        $platforms = $this->apiManager->getAvailablePlatforms();
        $results = [];

        foreach ($platforms as $platform) {
            try {
                // Try a simple search to test the connection
                $testResults = $this->apiManager->searchEvents(['q' => 'test', 'per_page' => 1], [$platform]);
                $results[$platform] = [
                    'status'  => 'connected',
                    'message' => 'API connection successful',
                ];
            } catch (Exception $e) {
                $results[$platform] = [
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $results,
        ]);
    }

    /**
     * Build search criteria from request
     */
    /**
     * BuildSearchCriteria
     */
    protected function buildSearchCriteria(Request $request): array
    {
        $criteria = [
            'q' => $request->input('query'),
        ];

        if ($request->filled('city')) {
            $criteria['venue.city'] = $request->input('city');
        }

        if ($request->filled('date_from')) {
            $criteria['datetime_utc.gte'] = $request->input('date_from') . 'T00:00:00Z';
        }

        if ($request->filled('date_to')) {
            $criteria['datetime_utc.lte'] = $request->input('date_to') . 'T23:59:59Z';
        }

        // Add Ticketmaster specific parameters
        if ($request->filled('query')) {
            $criteria['apikey'] = config('ticket_apis.ticketmaster.api_key');
            $criteria['keyword'] = $request->input('query');
        }

        return array_filter($criteria);
    }

    /**
     * Generate search summary
     */
    /**
     * GenerateSearchSummary
     */
    protected function generateSearchSummary(array $results): array
    {
        $summary = [
            'total_events' => 0,
            'platforms'    => [],
            'price_range'  => ['min' => NULL, 'max' => NULL],
        ];

        foreach ($results as $platform => $events) {
            $platformCount = count($events);
            $summary['total_events'] += $platformCount;
            $summary['platforms'][$platform] = $platformCount;

            // Calculate price range
            foreach ($events as $event) {
                if (isset($event['price_min']) && $event['price_min'] !== NULL) {
                    $summary['price_range']['min'] = $summary['price_range']['min'] === NULL
                        ? $event['price_min']
                        : min($summary['price_range']['min'], $event['price_min']);
                }

                if (isset($event['price_max']) && $event['price_max'] !== NULL) {
                    $summary['price_range']['max'] = $summary['price_range']['max'] === NULL
                        ? $event['price_max']
                        : max($summary['price_range']['max'], $event['price_max']);
                }
            }
        }

        return $summary;
    }

    /**
     * Filter tickets for frontend AJAX requests
     */
    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:255',
            'sport_type' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0|gte:price_min',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'platforms' => 'nullable|array',
            'platforms.*' => 'string|max:50',
            'availability' => 'nullable|string|in:available,limited,sold_out',
            'sort' => 'nullable|string|in:relevance,price_asc,price_desc,date_asc,date_desc,updated_desc,popular',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Build cache key
            $cacheKey = 'tickets_filter_' . md5(serialize($request->all()));
            
            // Try to get from cache first
            $result = Cache::remember($cacheKey, 300, function () use ($request) { // 5 minutes cache
                $query = ScrapedTicket::query()
                    ->with(['category'])
                    ->where('status', 'active');

                // Apply search filter
                if ($request->filled('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'LIKE', "%{$search}%")
                          ->orWhere('venue', 'LIKE', "%{$search}%")
                          ->orWhere('location', 'LIKE', "%{$search}%")
                          ->orWhere('sport', 'LIKE', "%{$search}%")
                          ->orWhere('team', 'LIKE', "%{$search}%");
                    });
                }

                // Apply sport type filter
                if ($request->filled('sport_type')) {
                    $query->where('sport', $request->input('sport_type'));
                }

                // Apply city filter
                if ($request->filled('city')) {
                    $query->where('location', 'LIKE', '%' . $request->input('city') . '%');
                }

                // Apply price range filter
                if ($request->filled('price_min')) {
                    $query->where('min_price', '>=', $request->input('price_min'));
                }
                if ($request->filled('price_max')) {
                    $query->where('max_price', '<=', $request->input('price_max'));
                }

                // Apply date range filter
                if ($request->filled('date_from')) {
                    $query->whereDate('event_date', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('event_date', '<=', $request->input('date_to'));
                }

                // Apply platform filter
                if ($request->filled('platforms')) {
                    $platforms = $request->input('platforms');
                    $query->whereIn('platform', $platforms);
                }

                // Apply availability filter
                if ($request->filled('availability')) {
                    $availability = $request->input('availability');
                    if ($availability === 'available') {
                        $query->where('is_available', true);
                    } elseif ($availability === 'limited') {
                        $query->where('is_high_demand', true);
                    } elseif ($availability === 'sold_out') {
                        $query->where('is_available', false);
                    }
                }

                // Apply sorting
                $sort = $request->input('sort', 'relevance');
                switch ($sort) {
                    case 'price_asc':
                        $query->orderBy('min_price', 'asc');
                        break;
                    case 'price_desc':
                        $query->orderBy('max_price', 'desc');
                        break;
                    case 'date_asc':
                        $query->orderBy('event_date', 'asc');
                        break;
                    case 'date_desc':
                        $query->orderBy('event_date', 'desc');
                        break;
                    case 'updated_desc':
                        $query->orderBy('updated_at', 'desc');
                        break;
                    case 'popular':
                        $query->orderBy('popularity_score', 'desc');
                        break;
                    case 'relevance':
                    default:
                        if ($request->filled('search')) {
                            // Add relevance scoring based on search term
                            $search = $request->input('search');
                            $query->selectRaw('scraped_tickets.*, 
                                (CASE 
                                    WHEN title LIKE ? THEN 3
                                    WHEN venue LIKE ? THEN 2
                                    WHEN location LIKE ? THEN 1
                                    ELSE 0
                                END) as relevance_score', [
                                "%{$search}%", "%{$search}%", "%{$search}%"
                            ])
                            ->orderBy('relevance_score', 'desc')
                            ->orderBy('updated_at', 'desc');
                        } else {
                            $query->orderBy('updated_at', 'desc');
                        }
                        break;
                }

                // Pagination
                $perPage = min($request->input('per_page', 12), 100);
                return $query->paginate($perPage);
            });

            // Add bookmark status to each ticket (placeholder for future implementation)
            if (Auth::check()) {
                $result->getCollection()->transform(function ($ticket) {
                    $ticket->is_bookmarked = false; // TODO: Implement bookmark functionality
                    return $ticket;
                });
            }

            return response()->json([
                'success' => true,
                'data' => $result->items(),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'last_page' => $result->lastPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                    'from' => $result->firstItem(),
                    'to' => $result->lastItem(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Ticket filtering failed', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to filter tickets: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'q' => 'required|string|min:2|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Query parameter is required and must be at least 2 characters'
            ], 422);
        }

        $query = $request->input('q');
        $cacheKey = 'suggestions_' . md5($query);

        try {
            $suggestions = Cache::remember($cacheKey, 1800, function () use ($query) { // 30 minutes cache
                $suggestions = [];
                
                // Get event name suggestions
                $eventNames = DB::table('scraped_tickets')
                    ->select('title as text', DB::raw('"Event" as category'), DB::raw('COUNT(*) as count'))
                    ->where('title', 'LIKE', "%{$query}%")
                    ->where('status', 'active')
                    ->groupBy('title')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get();
                    
                $suggestions = array_merge($suggestions, $eventNames->toArray());
                
                // Get venue suggestions
                $venues = DB::table('scraped_tickets')
                    ->select('venue as text', DB::raw('"Venue" as category'), DB::raw('COUNT(*) as count'))
                    ->where('venue', 'LIKE', "%{$query}%")
                    ->where('venue', '!=', '')
                    ->where('status', 'active')
                    ->groupBy('venue')
                    ->orderBy('count', 'desc')
                    ->limit(3)
                    ->get();
                    
                $suggestions = array_merge($suggestions, $venues->toArray());
                
                // Get location suggestions
                $locations = DB::table('scraped_tickets')
                    ->select('location as text', DB::raw('"Location" as category'), DB::raw('COUNT(*) as count'))
                    ->where('location', 'LIKE', "%{$query}%")
                    ->where('location', '!=', '')
                    ->where('status', 'active')
                    ->groupBy('location')
                    ->orderBy('count', 'desc')
                    ->limit(3)
                    ->get();
                    
                $suggestions = array_merge($suggestions, $locations->toArray());
                
                // Get sport type suggestions
                $sports = DB::table('scraped_tickets')
                    ->select('sport as text', DB::raw('"Sport" as category'), DB::raw('COUNT(*) as count'))
                    ->where('sport', 'LIKE', "%{$query}%")
                    ->where('sport', '!=', '')
                    ->where('status', 'active')
                    ->groupBy('sport')
                    ->orderBy('count', 'desc')
                    ->limit(2)
                    ->get();
                    
                $suggestions = array_merge($suggestions, $sports->toArray());
                
                // Sort by relevance (exact matches first, then by count)
                usort($suggestions, function ($a, $b) use ($query) {
                    $aExact = stripos($a->text, $query) === 0 ? 1 : 0;
                    $bExact = stripos($b->text, $query) === 0 ? 1 : 0;
                    
                    if ($aExact !== $bExact) {
                        return $bExact - $aExact;
                    }
                    
                    return $b->count - $a->count;
                });
                
                return array_slice($suggestions, 0, 10);
            });

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);

        } catch (Exception $e) {
            Log::error('Suggestions failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get suggestions'
            ], 500);
        }
    }

    /**
     * Toggle bookmark status for a ticket
     */
    public function toggleBookmark(Request $request, $ticketId)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }

        try {
            $ticket = ScrapedTicket::findOrFail($ticketId);
            $user = Auth::user();
            
            // TODO: Implement bookmark functionality
            $bookmark = null; // $user->bookmarks()->where('ticket_id', $ticketId)->first();
            
            if ($bookmark) {
                // $bookmark->delete();
                $bookmarked = false;
                $message = 'Bookmark removed (placeholder)';
            } else {
                // $user->bookmarks()->create(['ticket_id' => $ticketId]);
                $bookmarked = true;
                $message = 'Bookmark added (placeholder)';
            }

            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
                'message' => $message
            ]);

        } catch (Exception $e) {
            Log::error('Bookmark toggle failed', [
                'ticket_id' => $ticketId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update bookmark'
            ], 500);
        }
    }

    /**
     * Get detailed ticket information
     */
    public function getTicketDetails($ticketId)
    {
        try {
            $ticket = ScrapedTicket::with(['category'])
                ->findOrFail($ticketId);
            
            // TODO: Add view count tracking if needed
            // $ticket->increment('view_count');
            
            // Add bookmark status if user is authenticated
            if (Auth::check()) {
                $ticket->is_bookmarked = false; // TODO: Implement bookmark functionality
            } else {
                $ticket->is_bookmarked = false;
            }

            return response()->json([
                'success' => true,
                'data' => $ticket
            ]);

        } catch (Exception $e) {
            Log::error('Get ticket details failed', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }
    }

    /**
     * Test endpoint to trigger price change event (for development)
     */
    public function testPriceChange(Request $request, $ticketId)
    {
        if (app()->environment('production')) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is only available in development'
            ], 403);
        }

        try {
            $ticket = ScrapedTicket::findOrFail($ticketId);
            $oldPrice = $ticket->min_price ?? 0;
            $newPrice = $request->input('new_price', $oldPrice * 1.1); // 10% increase by default
            
            // Update both min_price and max_price for consistency
            $ticket->update([
                'min_price' => $newPrice,
                'max_price' => $newPrice
            ]);
            
            $percentageChange = (($newPrice - $oldPrice) / $oldPrice) * 100;
            
            // Broadcast price change event
            event(new TicketPriceChanged(
                $ticket->id,
                $ticket->title ?? 'Unknown Event',
                $ticket->platform ?? 'unknown',
                (float) $oldPrice,
                (float) $newPrice,
                $ticket->ticket_url
            ));

            return response()->json([
                'success' => true,
                'message' => 'Price change event triggered',
                'data' => [
                    'ticket_id' => $ticket->id,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'percentage_change' => round($percentageChange, 2)
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Test price change failed', [
                'ticket_id' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger price change'
            ], 500);
        }
    }
}
