<?php declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Events\TicketPriceChanged;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Ticket API Controller
 *
 * Handles AJAX requests for the enhanced ticket system including
 * filtering, search suggestions, bookmarks, and real-time updates.
 */
class TicketApiController extends Controller
{
    /**
     * AJAX filter tickets with advanced search and caching
     */
    public function ajaxFilter(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'keywords'          => 'nullable|string|max:255',
                'sport_type'        => 'nullable|string',
                'venue'             => 'nullable|string',
                'city'              => 'nullable|string',
                'date_from'         => 'nullable|date',
                'date_to'           => 'nullable|date',
                'price_min'         => 'nullable|numeric|min:0',
                'price_max'         => 'nullable|numeric',
                'availability_only' => 'nullable|boolean',
                'platform'          => 'nullable|string',
                'sort_by'           => 'nullable|in:price_asc,price_desc,date_asc,date_desc,popularity',
                'per_page'          => 'nullable|integer|min:1|max:100',
                'page'              => 'nullable|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid filter parameters',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $filters = $validator->validated();
            $perPage = $filters['per_page'] ?? 20;
            $page = $filters['page'] ?? 1;

            // Generate cache key based on filters
            $cacheKey = 'ticket_filter_' . md5(serialize($filters));

            // Try to get from cache first (5 minute cache)
            $result = Cache::remember($cacheKey, 300, function () use ($filters, $perPage, $page) {
                return $this->executeTicketFilter($filters, $perPage, $page);
            });

            return response()->json([
                'success'         => TRUE,
                'html'            => $result['html'],
                'stats'           => $result['stats'],
                'applied_filters' => $result['applied_filters'],
                'pagination'      => $result['pagination'],
                'cache_key'       => $cacheKey,
                'timestamp'       => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            \Log::error('AJAX Filter Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success'    => FALSE,
                'message'    => 'An error occurred while filtering tickets. Please try again.',
                'error_code' => 'FILTER_ERROR',
            ], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        try {
            $term = $request->get('term', '');

            if (strlen($term) < 2) {
                return response()->json([
                    'success'     => TRUE,
                    'suggestions' => [],
                ]);
            }

            $cacheKey = 'search_suggestions_' . md5($term);

            $suggestions = Cache::remember($cacheKey, 3600, function () use ($term) {
                $suggestions = [];

                // Get event name suggestions
                $events = DB::table('scraped_tickets')
                    ->select('event_name')
                    ->where('event_name', 'LIKE', "%{$term}%")
                    ->groupBy('event_name')
                    ->limit(5)
                    ->get();

                foreach ($events as $event) {
                    $suggestions[] = [
                        'value' => $event->event_name,
                        'type'  => 'event',
                    ];
                }

                // Get venue suggestions
                $venues = DB::table('scraped_tickets')
                    ->select('venue')
                    ->where('venue', 'LIKE', "%{$term}%")
                    ->whereNotNull('venue')
                    ->groupBy('venue')
                    ->limit(3)
                    ->get();

                foreach ($venues as $venue) {
                    $suggestions[] = [
                        'value' => $venue->venue,
                        'type'  => 'venue',
                    ];
                }

                // Get city suggestions
                $cities = DB::table('scraped_tickets')
                    ->select('city')
                    ->where('city', 'LIKE', "%{$term}%")
                    ->whereNotNull('city')
                    ->groupBy('city')
                    ->limit(3)
                    ->get();

                foreach ($cities as $city) {
                    $suggestions[] = [
                        'value' => $city->city,
                        'type'  => 'city',
                    ];
                }

                return $suggestions;
            });

            return response()->json([
                'success'     => TRUE,
                'suggestions' => $suggestions,
                'term'        => $term,
            ]);
        } catch (\Exception $e) {
            \Log::error('Search Suggestions Error: ' . $e->getMessage());

            return response()->json([
                'success'     => FALSE,
                'suggestions' => [],
                'message'     => 'Failed to load suggestions',
            ]);
        }
    }

    /**
     * Toggle bookmark status for a ticket
     */
    public function bookmarkToggle(Request $request): JsonResponse
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success'  => FALSE,
                    'message'  => 'Authentication required',
                    'redirect' => route('login'),
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'ticket_id' => 'required|integer|exists:scraped_tickets,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid ticket ID',
                ], 422);
            }

            $ticketId = $request->get('ticket_id');
            $userId = Auth::id();

            // Check if bookmark exists
            $bookmark = DB::table('ticket_bookmarks')
                ->where('user_id', $userId)
                ->where('ticket_id', $ticketId)
                ->first();

            if ($bookmark) {
                // Remove bookmark
                DB::table('ticket_bookmarks')
                    ->where('user_id', $userId)
                    ->where('ticket_id', $ticketId)
                    ->delete();

                // Decrement bookmark count
                DB::table('scraped_tickets')
                    ->where('id', $ticketId)
                    ->decrement('bookmark_count');

                $isBookmarked = FALSE;
                $message = 'Bookmark removed';
            } else {
                // Add bookmark
                DB::table('ticket_bookmarks')->insert([
                    'user_id'    => $userId,
                    'ticket_id'  => $ticketId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Increment bookmark count
                DB::table('scraped_tickets')
                    ->where('id', $ticketId)
                    ->increment('bookmark_count');

                $isBookmarked = TRUE;
                $message = 'Ticket bookmarked';
            }

            // Clear user-specific cache
            Cache::forget("user_bookmarks_{$userId}");

            return response()->json([
                'success'       => TRUE,
                'is_bookmarked' => $isBookmarked,
                'message'       => $message,
                'ticket_id'     => $ticketId,
            ]);
        } catch (\Exception $e) {
            \Log::error('Bookmark Toggle Error: ' . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'ticket_id' => $request->get('ticket_id'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update bookmark. Please try again.',
            ], 500);
        }
    }

    /**
     * Get ticket details for API/AJAX requests
     */
    public function ticketDetails(Request $request, int $ticketId): JsonResponse
    {
        try {
            $ticket = DB::table('scraped_tickets')
                ->where('id', $ticketId)
                ->where('status', 'active')
                ->first();

            if (!$ticket) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Ticket not found or inactive',
                ], 404);
            }

            // Increment view count
            DB::table('scraped_tickets')
                ->where('id', $ticketId)
                ->increment('view_count');

            // Record view if user is logged in
            if (Auth::check()) {
                DB::table('ticket_views')->insert([
                    'user_id'   => Auth::id(),
                    'ticket_id' => $ticketId,
                    'viewed_at' => now(),
                ]);
            }

            // Get price history
            $priceHistory = DB::table('ticket_price_history')
                ->where('ticket_id', $ticketId)
                ->orderBy('recorded_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => TRUE,
                'ticket'  => [
                    'id'                  => $ticket->id,
                    'event_name'          => $ticket->event_name,
                    'venue'               => $ticket->venue,
                    'city'                => $ticket->city,
                    'event_date'          => $ticket->event_date,
                    'price'               => $ticket->price,
                    'currency'            => $ticket->currency ?? 'USD',
                    'availability_status' => $ticket->availability_status,
                    'platform_name'       => $ticket->platform_name,
                    'external_url'        => $ticket->external_url,
                    'view_count'          => $ticket->view_count,
                    'bookmark_count'      => $ticket->bookmark_count,
                    'last_updated'        => $ticket->updated_at,
                ],
                'price_history' => $priceHistory,
                'is_bookmarked' => Auth::check() ? $this->isTicketBookmarked(Auth::id(), $ticketId) : FALSE,
            ]);
        } catch (\Exception $e) {
            \Log::error('Ticket Details Error: ' . $e->getMessage(), [
                'ticket_id' => $ticketId,
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load ticket details',
            ], 500);
        }
    }

    /**
     * Trigger a test price change event (for development/testing)
     */
    public function testPriceChange(Request $request, int $ticketId): JsonResponse
    {
        if (!app()->environment(['local', 'staging'])) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Test endpoints only available in development',
            ], 403);
        }

        try {
            $ticket = DB::table('scraped_tickets')->where('id', $ticketId)->first();

            if (!$ticket) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Ticket not found',
                ], 404);
            }

            $oldPrice = $ticket->price;
            $newPrice = $oldPrice + (rand(-20, 50) / 10); // Random price change
            $newPrice = max($newPrice, 10); // Minimum price of $10

            // Update ticket price
            DB::table('scraped_tickets')
                ->where('id', $ticketId)
                ->update([
                    'price'             => $newPrice,
                    'last_price_change' => now(),
                    'updated_at'        => now(),
                ]);

            // Record price history
            DB::table('ticket_price_history')->insert([
                'ticket_id'   => $ticketId,
                'old_price'   => $oldPrice,
                'new_price'   => $newPrice,
                'recorded_at' => now(),
            ]);

            // Broadcast price change event
            event(new TicketPriceChanged(
                ticketId: $ticketId,
                oldPrice: $oldPrice,
                newPrice: $newPrice,
                currency: $ticket->currency ?? 'USD',
                platform: $ticket->platform_name,
                eventTitle: $ticket->event_name
            ));

            return response()->json([
                'success'           => TRUE,
                'message'           => 'Test price change event triggered',
                'ticket_id'         => $ticketId,
                'old_price'         => $oldPrice,
                'new_price'         => $newPrice,
                'change_amount'     => $newPrice - $oldPrice,
                'percentage_change' => $oldPrice > 0 ? (($newPrice - $oldPrice) / $oldPrice) * 100 : 0,
            ]);
        } catch (\Exception $e) {
            \Log::error('Test Price Change Error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to trigger test event',
            ], 500);
        }
    }

    /**
     * Execute the ticket filtering logic
     */
    private function executeTicketFilter(array $filters, int $perPage, int $page): array
    {
        $query = DB::table('scraped_tickets')
            ->where('status', 'active');

        // Apply filters
        if (!empty($filters['keywords'])) {
            $keywords = $filters['keywords'];
            $query->where(function ($q) use ($keywords) {
                $q->where('event_name', 'LIKE', "%{$keywords}%")
                  ->orWhere('venue', 'LIKE', "%{$keywords}%")
                  ->orWhere('city', 'LIKE', "%{$keywords}%")
                  ->orWhere('description', 'LIKE', "%{$keywords}%");
            });
        }

        if (!empty($filters['sport_type'])) {
            $query->where('sport_type', $filters['sport_type']);
        }

        if (!empty($filters['venue'])) {
            $query->where('venue', 'LIKE', "%{$filters['venue']}%");
        }

        if (!empty($filters['city'])) {
            $query->where('city', 'LIKE', "%{$filters['city']}%");
        }

        if (!empty($filters['date_from'])) {
            $query->where('event_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('event_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['price_min'])) {
            $query->where('price', '>=', $filters['price_min']);
        }

        if (!empty($filters['price_max'])) {
            $query->where('price', '<=', $filters['price_max']);
        }

        if (!empty($filters['availability_only'])) {
            $query->where('availability_status', 'available');
        }

        if (!empty($filters['platform'])) {
            $query->where('platform_name', $filters['platform']);
        }

        // Apply sorting
        switch ($filters['sort_by'] ?? 'date_desc') {
            case 'price_asc':
                $query->orderBy('price', 'asc');

                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');

                break;
            case 'date_asc':
                $query->orderBy('event_date', 'asc');

                break;
            case 'popularity':
                $query->orderBy('view_count', 'desc');

                break;
            default:
                $query->orderBy('event_date', 'desc');

                break;
        }

        // Get total count for stats
        $totalCount = $query->count();

        // Paginate results
        $offset = ($page - 1) * $perPage;
        $tickets = $query->offset($offset)->limit($perPage)->get();

        // Calculate statistics
        $stats = [
            'total_count'     => $totalCount,
            'available_count' => DB::table('scraped_tickets')
                ->where('status', 'active')
                ->where('availability_status', 'available')
                ->count(),
            'avg_price' => DB::table('scraped_tickets')
                ->where('status', 'active')
                ->avg('price'),
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($totalCount / $perPage),
        ];

        // Generate HTML (you would create a partial view for this)
        $html = view('tickets.partials.ticket-grid', compact('tickets'))->render();

        return [
            'html'            => $html,
            'stats'           => $stats,
            'applied_filters' => array_filter($filters),
            'pagination'      => [
                'current_page' => $page,
                'total_pages'  => ceil($totalCount / $perPage),
                'total_items'  => $totalCount,
                'per_page'     => $perPage,
            ],
        ];
    }

    /**
     * Check if a ticket is bookmarked by a user
     */
    private function isTicketBookmarked(int $userId, int $ticketId): bool
    {
        return DB::table('ticket_bookmarks')
            ->where('user_id', $userId)
            ->where('ticket_id', $ticketId)
            ->exists();
    }
}
