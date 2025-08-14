<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScrapedTicket;
use App\Models\TicketAlert;
use App\Services\EnhancedAlertSystem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;

class AlertController extends Controller
{
    protected EnhancedAlertSystem $alertSystem;

    public function __construct(EnhancedAlertSystem $alertSystem)
    {
        $this->alertSystem = $alertSystem;
    }

    /**
     * Get all alerts for the authenticated user
     */
    /**
     * Index
     */
    public function index(Request $request): JsonResponse
    {
        $query = TicketAlert::forUser(auth()->id())
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('is_active')) {
            // Convert is_active boolean to status enum
            $status = $request->boolean('is_active') ? 'active' : 'paused';
            $query->where('status', $status);
        }

        if ($request->has('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('keywords', 'LIKE', '%' . $search . '%');
            });
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $alerts = $query->paginate($perPage);

        return response()->json([
            'success' => TRUE,
            'data'    => $alerts->items(),
            'meta'    => [
                'current_page' => $alerts->currentPage(),
                'from'         => $alerts->firstItem(),
                'last_page'    => $alerts->lastPage(),
                'per_page'     => $alerts->perPage(),
                'to'           => $alerts->lastItem(),
                'total'        => $alerts->total(),
            ],
            'links' => [
                'first' => $alerts->url(1),
                'last'  => $alerts->url($alerts->lastPage()),
                'prev'  => $alerts->previousPageUrl(),
                'next'  => $alerts->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Create a new ticket alert
     */
    /**
     * Store
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                    => 'required|string|max:255',
            'keywords'                => 'required|string|max:500',
            'platform'                => 'sometimes|string|in:stubhub,ticketmaster,viagogo,tickpick,seatgeek,axs,eventbrite,livenation',
            'max_price'               => 'sometimes|numeric|min:0|max:99999',
            'currency'                => 'sometimes|string|in:USD,EUR,GBP,CAD,AUD',
            'filters'                 => 'sometimes|array',
            'filters.venue'           => 'sometimes|string|max:255',
            'filters.location'        => 'sometimes|string|max:255',
            'filters.min_quantity'    => 'sometimes|integer|min:1',
            'filters.section'         => 'sometimes|string|max:100',
            'filters.event_date_from' => 'sometimes|date',
            'filters.event_date_to'   => 'sometimes|date',
            'email_notifications'     => 'sometimes|boolean',
            'sms_notifications'       => 'sometimes|boolean',
            'is_active'               => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        $data['uuid'] = (string) Str::uuid();
        $data['currency'] ??= 'USD';
        $data['email_notifications'] ??= TRUE;
        $data['sms_notifications'] ??= FALSE;

        // Convert is_active boolean to status enum
        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'] ? 'active' : 'paused';
            unset($data['is_active']); // Remove is_active as it doesn't exist in the table
        } else {
            $data['status'] = 'active'; // Default to active
        }

        $alert = TicketAlert::create($data);

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert created successfully',
            'data'    => $alert->load('user'),
        ], 201);
    }

    /**
     * Get a specific alert
     */
    /**
     * Show
     */
    public function show(string $uuid): JsonResponse
    {
        $alert = TicketAlert::with(['user'])
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (! $alert) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        return response()->json([
            'success' => TRUE,
            'data'    => $alert,
        ]);
    }

    /**
     * Update an existing alert
     */
    /**
     * Update
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (! $alert) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'                    => 'sometimes|string|max:255',
            'keywords'                => 'sometimes|string|max:500',
            'platform'                => 'sometimes|string|in:stubhub,ticketmaster,viagogo,tickpick,seatgeek,axs,eventbrite,livenation',
            'max_price'               => 'sometimes|numeric|min:0|max:99999',
            'currency'                => 'sometimes|string|in:USD,EUR,GBP,CAD,AUD',
            'filters'                 => 'sometimes|array',
            'filters.venue'           => 'sometimes|string|max:255',
            'filters.location'        => 'sometimes|string|max:255',
            'filters.min_quantity'    => 'sometimes|integer|min:1',
            'filters.section'         => 'sometimes|string|max:100',
            'filters.event_date_from' => 'sometimes|date',
            'filters.event_date_to'   => 'sometimes|date',
            'email_notifications'     => 'sometimes|boolean',
            'sms_notifications'       => 'sometimes|boolean',
            'is_active'               => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Convert is_active boolean to status enum if present
        if (isset($data['is_active'])) {
            $data['status'] = $data['is_active'] ? 'active' : 'paused';
            unset($data['is_active']); // Remove is_active as it doesn't exist in the table
        }

        $alert->update($data);
        $alert->load('user');

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert updated successfully',
            'data'    => $alert,
        ]);
    }

    /**
     * Delete an alert
     */
    /**
     * Destroy
     */
    public function destroy(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (! $alert) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        $alert->delete();

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert deleted successfully',
        ]);
    }

    /**
     * Toggle alert active status
     */
    /**
     * Toggle
     */
    public function toggle(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (! $alert) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        $newStatus = $alert->status === 'active' ? 'paused' : 'active';
        $alert->update(['status' => $newStatus]);

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert status updated successfully',
            'data'    => [
                'uuid'      => $alert->uuid,
                'status'    => $alert->status,
                'is_active' => $alert->status === 'active', // Backward compatibility
            ],
        ]);
    }

    /**
     * Test an alert against current tickets
     */
    /**
     * Test
     */
    public function test(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (! $alert) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        // Get recent tickets that match the alert criteria
        $query = ScrapedTicket::where('is_available', TRUE)
            ->where('scraped_at', '>=', now()->subHours(24));

        // Apply alert filters
        if ($alert->platform) {
            $query->where('platform', $alert->platform);
        }

        if ($alert->max_price) {
            $query->where(function ($q) use ($alert): void {
                $q->where('min_price', '<=', $alert->max_price)
                    ->orWhere('max_price', '<=', $alert->max_price);
            });
        }

        // Search for keywords in title
        $keywords = strtolower($alert->keywords);
        $query->where('title', 'LIKE', '%' . $keywords . '%');

        // Apply additional filters
        if ($alert->filters) {
            foreach ($alert->filters as $key => $value) {
                switch ($key) {
                    case 'venue':
                        $query->where('venue', 'LIKE', '%' . $value . '%');

                        break;
                    case 'location':
                        $query->where('location', 'LIKE', '%' . $value . '%');

                        break;
                    case 'event_date_from':
                        $query->where('event_date', '>=', $value);

                        break;
                    case 'event_date_to':
                        $query->where('event_date', '<=', $value);

                        break;
                }
            }
        }

        $matchingTickets = $query->limit(10)->get();

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert test completed',
            'data'    => [
                'alert' => [
                    'uuid'      => $alert->uuid,
                    'name'      => $alert->name,
                    'keywords'  => $alert->keywords,
                    'platform'  => $alert->platform,
                    'max_price' => $alert->max_price,
                ],
                'matching_tickets' => $matchingTickets->count(),
                'sample_matches'   => $matchingTickets->map(function ($ticket) {
                    return [
                        'uuid'       => $ticket->uuid,
                        'title'      => $ticket->title,
                        'platform'   => $ticket->platform,
                        'venue'      => $ticket->venue,
                        'min_price'  => $ticket->min_price,
                        'max_price'  => $ticket->max_price,
                        'currency'   => $ticket->currency,
                        'event_date' => $ticket->event_date,
                        'ticket_url' => $ticket->ticket_url,
                    ];
                }),
                'tested_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Get alert statistics for the user
     */
    /**
     * Statistics
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'total_alerts'        => TicketAlert::forUser($userId)->count(),
            'active_alerts'       => TicketAlert::forUser($userId)->active()->count(),
            'inactive_alerts'     => TicketAlert::forUser($userId)->where('status', '!=', 'active')->count(),
            'total_matches_found' => TicketAlert::forUser($userId)->sum('matches_found'),
            'platform_breakdown'  => TicketAlert::forUser($userId)
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->platform ?? 'all_platforms' => $item->count];
                }),
            'recent_activity' => TicketAlert::forUser($userId)
                ->whereNotNull('triggered_at')
                ->orderBy('triggered_at', 'desc')
                ->limit(5)
                ->get(['uuid', 'name', 'triggered_at'])
                ->map(function ($alert) {
                    return [
                        'uuid'           => $alert->uuid,
                        'name'           => $alert->name,
                        'triggered'      => $alert->triggered_at ? TRUE : FALSE,
                        'last_triggered' => $alert->triggered_at?->diffForHumans(),
                    ];
                }),
        ];

        return response()->json([
            'success' => TRUE,
            'data'    => $stats,
        ]);
    }

    /**
     * Trigger manual check for all active alerts
     */
    /**
     * CheckAll
     */
    public function checkAll(): JsonResponse
    {
        $activeAlerts = TicketAlert::forUser(auth()->id())
            ->active()
            ->get();

        if ($activeAlerts->isEmpty()) {
            return response()->json([
                'success' => TRUE,
                'message' => 'No active alerts to check',
                'data'    => [
                    'alerts_checked' => 0,
                    'matches_found'  => 0,
                ],
            ]);
        }

        $totalMatches = 0;
        $checkedAlerts = 0;

        foreach ($activeAlerts as $alert) {
            try {
                $matches = $this->alertSystem->checkAlert($alert);
                $totalMatches += $matches;
                $checkedAlerts++;
            } catch (Exception $e) {
                // Log error but continue with other alerts
                Log::error('Alert check failed: ' . $e->getMessage(), [
                    'alert_uuid' => $alert->uuid,
                    'user_id'    => auth()->id(),
                ]);
            }
        }

        return response()->json([
            'success' => TRUE,
            'message' => 'Alert check completed',
            'data'    => [
                'total_alerts'        => $activeAlerts->count(),
                'alerts_checked'      => $checkedAlerts,
                'total_matches_found' => $totalMatches,
                'check_completed_at'  => now()->toISOString(),
            ],
        ]);
    }
}
