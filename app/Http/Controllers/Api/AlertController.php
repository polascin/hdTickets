<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketAlert;
use App\Models\ScrapedTicket;
use App\Services\EnhancedAlertSystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlertController extends Controller
{
    protected $alertSystem;

    public function __construct(EnhancedAlertSystem $alertSystem)
    {
        $this->alertSystem = $alertSystem;
    }

    /**
     * Get all alerts for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        $query = TicketAlert::forUser(auth()->id())
            ->with(['user'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('platform')) {
            $query->byPlatform($request->platform);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('keywords', 'LIKE', '%' . $search . '%');
            });
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $alerts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $alerts->items(),
            'meta' => [
                'current_page' => $alerts->currentPage(),
                'from' => $alerts->firstItem(),
                'last_page' => $alerts->lastPage(),
                'per_page' => $alerts->perPage(),
                'to' => $alerts->lastItem(),
                'total' => $alerts->total(),
            ],
            'links' => [
                'first' => $alerts->url(1),
                'last' => $alerts->url($alerts->lastPage()),
                'prev' => $alerts->previousPageUrl(),
                'next' => $alerts->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Create a new ticket alert
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'keywords' => 'required|string|max:500',
            'platform' => 'sometimes|string|in:stubhub,ticketmaster,viagogo,tickpick,seatgeek,axs,eventbrite,livenation',
            'max_price' => 'sometimes|numeric|min:0|max:99999',
            'currency' => 'sometimes|string|in:USD,EUR,GBP,CAD,AUD',
            'filters' => 'sometimes|array',
            'filters.venue' => 'sometimes|string|max:255',
            'filters.location' => 'sometimes|string|max:255',
            'filters.min_quantity' => 'sometimes|integer|min:1',
            'filters.section' => 'sometimes|string|max:100',
            'filters.event_date_from' => 'sometimes|date',
            'filters.event_date_to' => 'sometimes|date',
            'email_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->id();
        $data['uuid'] = (string) Str::uuid();
        $data['currency'] = $data['currency'] ?? 'USD';
        $data['email_notifications'] = $data['email_notifications'] ?? true;
        $data['sms_notifications'] = $data['sms_notifications'] ?? false;
        $data['is_active'] = $data['is_active'] ?? true;

        $alert = TicketAlert::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Alert created successfully',
            'data' => $alert->load('user')
        ], 201);
    }

    /**
     * Get a specific alert
     */
    public function show(string $uuid): JsonResponse
    {
        $alert = TicketAlert::with(['user'])
            ->where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $alert
        ]);
    }

    /**
     * Update an existing alert
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'keywords' => 'sometimes|string|max:500',
            'platform' => 'sometimes|string|in:stubhub,ticketmaster,viagogo,tickpick,seatgeek,axs,eventbrite,livenation',
            'max_price' => 'sometimes|numeric|min:0|max:99999',
            'currency' => 'sometimes|string|in:USD,EUR,GBP,CAD,AUD',
            'filters' => 'sometimes|array',
            'filters.venue' => 'sometimes|string|max:255',
            'filters.location' => 'sometimes|string|max:255',
            'filters.min_quantity' => 'sometimes|integer|min:1',
            'filters.section' => 'sometimes|string|max:100',
            'filters.event_date_from' => 'sometimes|date',
            'filters.event_date_to' => 'sometimes|date',
            'email_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $alert->update($validator->validated());
        $alert->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Alert updated successfully',
            'data' => $alert
        ]);
    }

    /**
     * Delete an alert
     */
    public function destroy(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }

        $alert->delete();

        return response()->json([
            'success' => true,
            'message' => 'Alert deleted successfully'
        ]);
    }

    /**
     * Toggle alert active status
     */
    public function toggle(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }

        $alert->update(['is_active' => !$alert->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Alert status updated successfully',
            'data' => [
                'uuid' => $alert->uuid,
                'is_active' => $alert->is_active
            ]
        ]);
    }

    /**
     * Test an alert against current tickets
     */
    public function test(string $uuid): JsonResponse
    {
        $alert = TicketAlert::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$alert) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }

        // Get recent tickets that match the alert criteria
        $query = ScrapedTicket::where('is_available', true)
            ->where('scraped_at', '>=', now()->subHours(24));

        // Apply alert filters
        if ($alert->platform) {
            $query->where('platform', $alert->platform);
        }

        if ($alert->max_price) {
            $query->where(function($q) use ($alert) {
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
            'success' => true,
            'message' => 'Alert test completed',
            'data' => [
                'alert' => [
                    'uuid' => $alert->uuid,
                    'name' => $alert->name,
                    'keywords' => $alert->keywords,
                    'platform' => $alert->platform,
                    'max_price' => $alert->max_price
                ],
                'matching_tickets' => $matchingTickets->count(),
                'sample_matches' => $matchingTickets->map(function($ticket) {
                    return [
                        'uuid' => $ticket->uuid,
                        'title' => $ticket->title,
                        'platform' => $ticket->platform,
                        'venue' => $ticket->venue,
                        'min_price' => $ticket->min_price,
                        'max_price' => $ticket->max_price,
                        'currency' => $ticket->currency,
                        'event_date' => $ticket->event_date,
                        'ticket_url' => $ticket->ticket_url
                    ];
                }),
                'tested_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get alert statistics for the user
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();
        
        $stats = [
            'total_alerts' => TicketAlert::forUser($userId)->count(),
            'active_alerts' => TicketAlert::forUser($userId)->active()->count(),
            'inactive_alerts' => TicketAlert::forUser($userId)->where('is_active', false)->count(),
            'total_matches_found' => TicketAlert::forUser($userId)->sum('matches_found'),
            'platform_breakdown' => TicketAlert::forUser($userId)
                ->selectRaw('platform, COUNT(*) as count')
                ->groupBy('platform')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->platform ?? 'all_platforms' => $item->count];
                }),
            'recent_activity' => TicketAlert::forUser($userId)
                ->whereNotNull('last_triggered_at')
                ->orderBy('last_triggered_at', 'desc')
                ->limit(5)
                ->get(['uuid', 'name', 'matches_found', 'last_triggered_at'])
                ->map(function($alert) {
                    return [
                        'uuid' => $alert->uuid,
                        'name' => $alert->name,
                        'matches_found' => $alert->matches_found,
                        'last_triggered' => $alert->last_triggered_at?->diffForHumans()
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Trigger manual check for all active alerts
     */
    public function checkAll(): JsonResponse
    {
        $activeAlerts = TicketAlert::forUser(auth()->id())
            ->active()
            ->get();

        if ($activeAlerts->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No active alerts to check',
                'data' => [
                    'alerts_checked' => 0,
                    'matches_found' => 0
                ]
            ]);
        }

        $totalMatches = 0;
        $checkedAlerts = 0;

        foreach ($activeAlerts as $alert) {
            try {
                $matches = $this->alertSystem->checkAlert($alert);
                $totalMatches += $matches;
                $checkedAlerts++;
            } catch (\Exception $e) {
                // Log error but continue with other alerts
                \Log::error('Alert check failed: ' . $e->getMessage(), [
                    'alert_uuid' => $alert->uuid,
                    'user_id' => auth()->id()
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alert check completed',
            'data' => [
                'total_alerts' => $activeAlerts->count(),
                'alerts_checked' => $checkedAlerts,
                'total_matches_found' => $totalMatches,
                'check_completed_at' => now()->toISOString()
            ]
        ]);
    }
}
