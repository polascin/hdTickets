<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseAttempt;
use App\Models\PurchaseQueue;
use App\Models\ScrapedTicket;
use App\Services\AutomatedPurchaseEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    protected $purchaseEngine;

    public function __construct(AutomatedPurchaseEngine $purchaseEngine)
    {
        $this->purchaseEngine = $purchaseEngine;
    }

    /**
     * Get purchase queue for the authenticated user
     */
    public function queue(Request $request): JsonResponse
    {
        $query = PurchaseQueue::where('user_id', auth()->id())
            ->with(['ticket'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('platform')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $queueItems = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $queueItems->items(),
            'meta' => [
                'current_page' => $queueItems->currentPage(),
                'from' => $queueItems->firstItem(),
                'last_page' => $queueItems->lastPage(),
                'per_page' => $queueItems->perPage(),
                'to' => $queueItems->lastItem(),
                'total' => $queueItems->total(),
            ],
            'links' => [
                'first' => $queueItems->url(1),
                'last' => $queueItems->url($queueItems->lastPage()),
                'prev' => $queueItems->previousPageUrl(),
                'next' => $queueItems->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Add ticket to purchase queue
     */
    public function addToQueue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ticket_uuid' => 'required|string|exists:scraped_tickets,uuid',
            'max_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1|max:10',
            'priority' => 'sometimes|integer|min:1|max:10',
            'auto_purchase' => 'sometimes|boolean',
            'notes' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = ScrapedTicket::where('uuid', $request->ticket_uuid)->first();

        if (!$ticket->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is no longer available'
            ], 400);
        }

        // Check if ticket is already in queue
        $existingQueueItem = PurchaseQueue::where('user_id', auth()->id())
            ->where('ticket_uuid', $request->ticket_uuid)
            ->where('status', 'pending')
            ->first();

        if ($existingQueueItem) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is already in your purchase queue'
            ], 400);
        }

        $queueItem = PurchaseQueue::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'ticket_uuid' => $request->ticket_uuid,
            'max_price' => $request->max_price,
            'quantity' => $request->quantity,
            'priority' => $request->get('priority', 5),
            'auto_purchase' => $request->get('auto_purchase', false),
            'notes' => $request->get('notes'),
            'status' => 'pending'
        ]);

        $queueItem->load('ticket');

        return response()->json([
            'success' => true,
            'message' => 'Ticket added to purchase queue successfully',
            'data' => $queueItem
        ], 201);
    }

    /**
     * Update queue item
     */
    public function updateQueue(Request $request, string $uuid): JsonResponse
    {
        $queueItem = PurchaseQueue::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$queueItem) {
            return response()->json([
                'success' => false,
                'message' => 'Queue item not found'
            ], 404);
        }

        if ($queueItem->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update queue item with status: ' . $queueItem->status
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'max_price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:1|max:10',
            'priority' => 'sometimes|integer|min:1|max:10',
            'auto_purchase' => 'sometimes|boolean',
            'notes' => 'sometimes|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $queueItem->update($validator->validated());
        $queueItem->load('ticket');

        return response()->json([
            'success' => true,
            'message' => 'Queue item updated successfully',
            'data' => $queueItem
        ]);
    }

    /**
     * Remove item from queue
     */
    public function removeFromQueue(string $uuid): JsonResponse
    {
        $queueItem = PurchaseQueue::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$queueItem) {
            return response()->json([
                'success' => false,
                'message' => 'Queue item not found'
            ], 404);
        }

        if ($queueItem->status === 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove item that is currently being processed'
            ], 400);
        }

        $queueItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from queue successfully'
        ]);
    }

    /**
     * Get purchase attempts for the user
     */
    public function attempts(Request $request): JsonResponse
    {
        $query = PurchaseAttempt::where('user_id', auth()->id())
            ->with(['ticket'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('platform')) {
            $query->whereHas('ticket', function($q) use ($request) {
                $q->where('platform', $request->platform);
            });
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        // Pagination
        $perPage = min($request->get('per_page', 20), 100);
        $attempts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attempts->items(),
            'meta' => [
                'current_page' => $attempts->currentPage(),
                'from' => $attempts->firstItem(),
                'last_page' => $attempts->lastPage(),
                'per_page' => $attempts->perPage(),
                'to' => $attempts->lastItem(),
                'total' => $attempts->total(),
            ],
            'links' => [
                'first' => $attempts->url(1),
                'last' => $attempts->url($attempts->lastPage()),
                'prev' => $attempts->previousPageUrl(),
                'next' => $attempts->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Initiate manual purchase attempt
     */
    public function initiatePurchase(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ticket_uuid' => 'required|string|exists:scraped_tickets,uuid',
            'quantity' => 'required|integer|min:1|max:10',
            'max_price' => 'required|numeric|min:0',
            'payment_method' => 'sometimes|string|max:255',
            'delivery_method' => 'sometimes|string|in:email,mobile,pickup,mail',
            'priority' => 'sometimes|string|in:low,normal,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $ticket = ScrapedTicket::where('uuid', $request->ticket_uuid)->first();

        if (!$ticket->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket is no longer available'
            ], 400);
        }

        // Check if user has sufficient credits/permissions for automated purchase
        $user = auth()->user();
        if (!$this->purchaseEngine->canInitiatePurchase($user, $ticket)) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient permissions or credits for automated purchase'
            ], 403);
        }

        try {
            $attempt = $this->purchaseEngine->initiatePurchase([
                'user_id' => $user->id,
                'ticket_uuid' => $request->ticket_uuid,
                'quantity' => $request->quantity,
                'max_price' => $request->max_price,
                'payment_method' => $request->get('payment_method'),
                'delivery_method' => $request->get('delivery_method', 'email'),
                'priority' => $request->get('priority', 'normal')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Purchase attempt initiated successfully',
                'data' => [
                    'attempt_uuid' => $attempt->uuid,
                    'status' => $attempt->status,
                    'ticket' => [
                        'uuid' => $ticket->uuid,
                        'title' => $ticket->title,
                        'platform' => $ticket->platform,
                        'venue' => $ticket->venue,
                        'event_date' => $ticket->event_date
                    ],
                    'initiated_at' => $attempt->created_at->toISOString()
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate purchase: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase attempt details
     */
    public function attemptDetails(string $uuid): JsonResponse
    {
        $attempt = PurchaseAttempt::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->with(['ticket'])
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase attempt not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'uuid' => $attempt->uuid,
                'status' => $attempt->status,
                'quantity' => $attempt->quantity,
                'max_price' => $attempt->max_price,
                'final_price' => $attempt->final_price,
                'platform_fee' => $attempt->platform_fee,
                'total_paid' => $attempt->total_paid,
                'error_message' => $attempt->error_message,
                'metadata' => $attempt->metadata,
                'ticket' => $attempt->ticket,
                'started_at' => $attempt->started_at,
                'completed_at' => $attempt->completed_at,
                'created_at' => $attempt->created_at,
                'updated_at' => $attempt->updated_at
            ]
        ]);
    }

    /**
     * Cancel pending purchase attempt
     */
    public function cancelAttempt(string $uuid): JsonResponse
    {
        $attempt = PurchaseAttempt::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$attempt) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase attempt not found'
            ], 404);
        }

        if ($attempt->status !== 'pending' && $attempt->status !== 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel attempt with status: ' . $attempt->status
            ], 400);
        }

        try {
            $this->purchaseEngine->cancelAttempt($attempt);

            return response()->json([
                'success' => true,
                'message' => 'Purchase attempt cancelled successfully',
                'data' => [
                    'uuid' => $attempt->uuid,
                    'status' => $attempt->fresh()->status,
                    'cancelled_at' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel purchase attempt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase statistics for the user
     */
    public function statistics(): JsonResponse
    {
        $userId = auth()->id();

        $stats = [
            'queue' => [
                'total_items' => PurchaseQueue::where('user_id', $userId)->count(),
                'pending_items' => PurchaseQueue::where('user_id', $userId)->where('status', 'pending')->count(),
                'processing_items' => PurchaseQueue::where('user_id', $userId)->where('status', 'processing')->count()
            ],
            'attempts' => [
                'total_attempts' => PurchaseAttempt::where('user_id', $userId)->count(),
                'successful_purchases' => PurchaseAttempt::where('user_id', $userId)->where('status', 'success')->count(),
                'failed_attempts' => PurchaseAttempt::where('user_id', $userId)->where('status', 'failed')->count(),
                'pending_attempts' => PurchaseAttempt::where('user_id', $userId)->whereIn('status', ['pending', 'processing'])->count(),
                'cancelled_attempts' => PurchaseAttempt::where('user_id', $userId)->where('status', 'cancelled')->count()
            ],
            'financial' => [
                'total_spent' => PurchaseAttempt::where('user_id', $userId)
                    ->where('status', 'success')
                    ->sum('total_paid') ?? 0,
                'average_purchase_price' => PurchaseAttempt::where('user_id', $userId)
                    ->where('status', 'success')
                    ->avg('final_price') ?? 0,
                'total_fees_paid' => PurchaseAttempt::where('user_id', $userId)
                    ->where('status', 'success')
                    ->sum('platform_fee') ?? 0
            ],
            'platform_breakdown' => PurchaseAttempt::where('user_id', $userId)
                ->join('scraped_tickets', 'purchase_attempts.ticket_uuid', '=', 'scraped_tickets.uuid')
                ->selectRaw('scraped_tickets.platform, 
                    COUNT(*) as total_attempts,
                    COUNT(CASE WHEN purchase_attempts.status = "success" THEN 1 END) as successful,
                    AVG(CASE WHEN purchase_attempts.status = "success" THEN purchase_attempts.final_price END) as avg_price')
                ->groupBy('scraped_tickets.platform')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->platform => [
                        'total_attempts' => $item->total_attempts,
                        'successful_purchases' => $item->successful,
                        'success_rate' => $item->total_attempts > 0 ? round(($item->successful / $item->total_attempts) * 100, 2) : 0,
                        'average_price' => round($item->avg_price ?? 0, 2)
                    ]];
                }),
            'recent_activity' => PurchaseAttempt::where('user_id', $userId)
                ->with(['ticket:uuid,title,platform,venue'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($attempt) {
                    return [
                        'uuid' => $attempt->uuid,
                        'status' => $attempt->status,
                        'ticket_title' => $attempt->ticket->title ?? 'Unknown',
                        'platform' => $attempt->ticket->platform ?? 'Unknown',
                        'final_price' => $attempt->final_price,
                        'created_at' => $attempt->created_at->toISOString()
                    ];
                })
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get user's purchase configuration
     */
    public function configuration(): JsonResponse
    {
        $user = auth()->user();
        
        // Get user preferences or default configuration
        $config = [
            'auto_purchase_enabled' => $user->auto_purchase_enabled ?? false,
            'max_daily_spend' => $user->max_daily_spend ?? 1000,
            'default_quantity' => $user->default_ticket_quantity ?? 2,
            'preferred_delivery_method' => $user->preferred_delivery_method ?? 'email',
            'notification_preferences' => [
                'purchase_success' => $user->notify_purchase_success ?? true,
                'purchase_failure' => $user->notify_purchase_failure ?? true,
                'queue_updates' => $user->notify_queue_updates ?? true,
                'price_drops' => $user->notify_price_drops ?? false
            ],
            'risk_settings' => [
                'max_price_increase_percent' => $user->max_price_increase_percent ?? 10,
                'require_manual_approval_above' => $user->manual_approval_threshold ?? 500,
                'max_attempts_per_ticket' => $user->max_attempts_per_ticket ?? 3
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $config
        ]);
    }

    /**
     * Update user's purchase configuration
     */
    public function updateConfiguration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'auto_purchase_enabled' => 'sometimes|boolean',
            'max_daily_spend' => 'sometimes|numeric|min:0|max:10000',
            'default_quantity' => 'sometimes|integer|min:1|max:10',
            'preferred_delivery_method' => 'sometimes|string|in:email,mobile,pickup,mail',
            'notification_preferences' => 'sometimes|array',
            'notification_preferences.purchase_success' => 'sometimes|boolean',
            'notification_preferences.purchase_failure' => 'sometimes|boolean',
            'notification_preferences.queue_updates' => 'sometimes|boolean',
            'notification_preferences.price_drops' => 'sometimes|boolean',
            'risk_settings' => 'sometimes|array',
            'risk_settings.max_price_increase_percent' => 'sometimes|numeric|min:0|max:100',
            'risk_settings.require_manual_approval_above' => 'sometimes|numeric|min:0',
            'risk_settings.max_attempts_per_ticket' => 'sometimes|integer|min:1|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        $data = $validator->validated();

        // Update user preferences
        if (isset($data['auto_purchase_enabled'])) {
            $user->auto_purchase_enabled = $data['auto_purchase_enabled'];
        }
        if (isset($data['max_daily_spend'])) {
            $user->max_daily_spend = $data['max_daily_spend'];
        }
        if (isset($data['default_quantity'])) {
            $user->default_ticket_quantity = $data['default_quantity'];
        }
        if (isset($data['preferred_delivery_method'])) {
            $user->preferred_delivery_method = $data['preferred_delivery_method'];
        }

        // Update notification preferences
        if (isset($data['notification_preferences'])) {
            foreach ($data['notification_preferences'] as $key => $value) {
                $field = 'notify_' . $key;
                if (in_array($field, $user->getFillable())) {
                    $user->$field = $value;
                }
            }
        }

        // Update risk settings
        if (isset($data['risk_settings'])) {
            foreach ($data['risk_settings'] as $key => $value) {
                if (in_array($key, $user->getFillable())) {
                    $user->$key = $value;
                }
            }
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuration updated successfully',
            'data' => [
                'updated_at' => $user->updated_at->toISOString()
            ]
        ]);
    }
}
