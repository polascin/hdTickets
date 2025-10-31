<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventMonitor;
use App\Models\PriceAlert;
use App\Services\AutomatedPurchasingService;
use App\Services\EnhancedEventMonitoringService;
use App\Services\MultiEventManagementService;
use App\Services\PriceTrackingAnalyticsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

/**
 * Events API Controller
 *
 * Provides comprehensive RESTful API endpoints for:
 * - Event discovery and search
 * - Real-time monitoring management
 * - Price tracking and alerts
 * - Automated purchasing configuration
 * - Multi-event operations
 */
class EventsController extends Controller
{
    public function __construct(
        private EnhancedEventMonitoringService $monitoringService,
        private PriceTrackingAnalyticsService $priceAnalyticsService,
        private AutomatedPurchasingService $purchasingService,
        private MultiEventManagementService $multiEventService,
    ) {
        $this->middleware('auth:api');
        $this->middleware('throttle:api-general')->except(['webhook']);
        $this->middleware('throttle:api-intensive')->only(['search', 'monitor', 'analyze']);
    }

    /**
     * Get paginated list of events with filtering
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'page'       => 'integer|min:1',
            'per_page'   => 'integer|min:1|max:100',
            'category'   => 'string|max:100',
            'venue'      => 'string|max:255',
            'date_from'  => 'date',
            'date_to'    => 'date|after_or_equal:date_from',
            'location'   => 'string|max:255',
            'min_price'  => 'numeric|min:0',
            'max_price'  => 'numeric|min:0|gte:min_price',
            'sort_by'    => 'string|in:name,date,price,popularity,created_at',
            'sort_order' => 'string|in:asc,desc',
            'search'     => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $query = Event::query()->where('is_active', TRUE);

            // Apply filters
            if ($request->filled('category')) {
                $query->where('category', $request->input('category'));
            }

            if ($request->filled('venue')) {
                $query->where('venue_name', 'LIKE', '%' . $request->input('venue') . '%');
            }

            if ($request->filled('date_from')) {
                $query->where('event_date', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->where('event_date', '<=', $request->input('date_to'));
            }

            if ($request->filled('location')) {
                $query->where(function ($q) use ($request): void {
                    $location = $request->input('location');
                    $q->where('city', 'LIKE', "%{$location}%")
                        ->orWhere('state', 'LIKE', "%{$location}%")
                        ->orWhere('country', 'LIKE', "%{$location}%");
                });
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhere('artist_name', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'event_date');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = min($request->input('per_page', 20), 100);
            $events = $query->with(['monitors' => function ($q): void {
                $q->where('user_id', Auth::id());
            }])->paginate($perPage);

            return $this->successResponse([
                'events'     => $events->items(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'total_pages'  => $events->lastPage(),
                    'total_items'  => $events->total(),
                    'per_page'     => $events->perPage(),
                    'has_more'     => $events->hasMorePages(),
                ],
                'applied_filters' => $request->only([
                    'category', 'venue', 'date_from', 'date_to',
                    'location', 'min_price', 'max_price', 'search',
                ]),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve events', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get detailed event information
     */
    public function show(Event $event): JsonResponse
    {
        try {
            $user = Auth::user();

            // Load relationships
            $event->load([
                'monitors' => function ($q) use ($user): void {
                    $q->where('user_id', $user->id);
                },
                'priceHistories' => function ($q): void {
                    $q->orderByDesc('recorded_at')->limit(50);
                },
            ]);

            // Get additional analytics
            $priceAnalytics = $this->priceAnalyticsService->getEventPriceAnalytics($event, $user);
            $monitoringStatus = $this->getEventMonitoringStatus($event, $user);
            $purchaseConfig = $this->purchasingService->getUserPurchaseConfig($user, $event);

            return $this->successResponse([
                'event'                 => $event,
                'price_analytics'       => $priceAnalytics,
                'monitoring_status'     => $monitoringStatus,
                'purchase_config'       => $purchaseConfig,
                'current_opportunities' => $this->identifyCurrentOpportunities($event),
                'recommendations'       => $this->generateEventRecommendations($event, $user),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve event details', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Start monitoring an event
     */
    public function startMonitoring(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platforms'                  => 'array|min:1',
            'platforms.*'                => 'string|in:ticketmaster,seatgeek,stubhub,vivid_seats',
            'check_interval'             => 'integer|min:60|max:3600',
            'priority'                   => 'integer|min:1|max:10',
            'notification_preferences'   => 'array',
            'notification_preferences.*' => 'string|in:email,sms,push,webhook',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();

            // Check rate limiting for monitoring operations
            $rateLimitKey = "monitor_event:{$user->id}";
            if (RateLimiter::tooManyAttempts($rateLimitKey, 10)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);

                return $this->errorResponse('Too many monitoring requests', [
                    'retry_after' => $seconds,
                ], 429);
            }

            RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

            $config = [
                'platforms'                => $request->input('platforms', ['ticketmaster']),
                'check_interval'           => $request->input('check_interval', 300),
                'priority'                 => $request->input('priority', 5),
                'notification_preferences' => $request->input('notification_preferences', ['email']),
            ];

            $monitor = $this->monitoringService->startMonitoring($user, $event, $config);

            return $this->successResponse([
                'message'               => 'Event monitoring started successfully',
                'monitor'               => $monitor,
                'estimated_first_check' => now()->addSeconds($config['check_interval']),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to start monitoring', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Stop monitoring an event
     */
    public function stopMonitoring(Event $event): JsonResponse
    {
        try {
            $user = Auth::user();
            $result = $this->monitoringService->stopMonitoring($user, $event);

            if ($result) {
                return $this->successResponse([
                    'message' => 'Event monitoring stopped successfully',
                ]);
            }

            return $this->errorResponse('No active monitoring found for this event', [], 404);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to stop monitoring', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get event monitoring status and statistics
     */
    public function monitoringStatus(Event $event): JsonResponse
    {
        try {
            $user = Auth::user();
            $monitor = EventMonitor::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if (!$monitor) {
                return $this->errorResponse('No monitoring configured for this event', [], 404);
            }

            $status = [
                'is_active'           => $monitor->is_active,
                'priority'            => $monitor->priority,
                'platforms'           => $monitor->platforms,
                'check_interval'      => $monitor->check_interval,
                'last_check_at'       => $monitor->last_check_at,
                'next_check_at'       => $monitor->getNextCheckTime(),
                'performance_metrics' => [
                    'success_rate'      => $monitor->getSuccessRate(),
                    'avg_response_time' => $monitor->getAverageResponseTime(),
                    'uptime'            => $monitor->getUptime(),
                    'total_checks'      => $monitor->total_checks,
                ],
                'health_status'   => $monitor->getHealthStatus(),
                'recent_activity' => $monitor->monitoringLogs()
                    ->orderByDesc('checked_at')
                    ->limit(10)
                    ->get(),
            ];

            return $this->successResponse($status);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve monitoring status', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Update monitoring configuration
     */
    public function updateMonitoring(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'platforms'                  => 'array',
            'platforms.*'                => 'string|in:ticketmaster,seatgeek,stubhub,vivid_seats',
            'check_interval'             => 'integer|min:60|max:3600',
            'priority'                   => 'integer|min:1|max:10',
            'notification_preferences'   => 'array',
            'notification_preferences.*' => 'string|in:email,sms,push,webhook',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();
            $monitor = EventMonitor::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->first();

            if (!$monitor) {
                return $this->errorResponse('No monitoring configured for this event', [], 404);
            }

            $updateData = [];
            if ($request->has('platforms')) {
                $updateData['platforms'] = $request->input('platforms');
            }
            if ($request->has('check_interval')) {
                $updateData['check_interval'] = $request->input('check_interval');
            }
            if ($request->has('priority')) {
                $updateData['priority'] = $request->input('priority');
            }
            if ($request->has('notification_preferences')) {
                $updateData['notification_preferences'] = $request->input('notification_preferences');
            }

            $monitor->update($updateData);

            return $this->successResponse([
                'message' => 'Monitoring configuration updated successfully',
                'monitor' => $monitor->fresh(),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update monitoring configuration', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get price analytics for an event
     */
    public function priceAnalytics(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period'              => 'string|in:24h,7d,30d,90d,all',
            'include_predictions' => 'boolean',
            'include_comparisons' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();
            $period = $request->input('period', '7d');
            $includePredictions = $request->boolean('include_predictions', TRUE);
            $includeComparisons = $request->boolean('include_comparisons', TRUE);

            $analytics = $this->priceAnalyticsService->getComprehensiveAnalytics(
                $event,
                $user,
                $period,
                $includePredictions,
                $includeComparisons,
            );

            return $this->successResponse($analytics);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve price analytics', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Create or update price alert for an event
     */
    public function createPriceAlert(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'target_price'            => 'required|numeric|min:1',
            'alert_type'              => 'required|string|in:below,above,percentage_drop,significant_change',
            'percentage_threshold'    => 'nullable|numeric|min:1|max:100',
            'notification_channels'   => 'required|array|min:1',
            'notification_channels.*' => 'string|in:email,sms,push,webhook',
            'is_active'               => 'boolean',
            'expiry_date'             => 'nullable|date|after:now',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();

            // Check for existing alert
            $existingAlert = PriceAlert::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('alert_type', $request->input('alert_type'))
                ->first();

            if ($existingAlert) {
                // Update existing alert
                $existingAlert->update($request->only([
                    'target_price', 'percentage_threshold', 'notification_channels',
                    'is_active', 'expiry_date',
                ]));
                $alert = $existingAlert;
                $message = 'Price alert updated successfully';
            } else {
                // Create new alert
                $alert = PriceAlert::create([
                    'user_id'               => $user->id,
                    'event_id'              => $event->id,
                    'target_price'          => $request->input('target_price'),
                    'alert_type'            => $request->input('alert_type'),
                    'percentage_threshold'  => $request->input('percentage_threshold'),
                    'notification_channels' => $request->input('notification_channels'),
                    'is_active'             => $request->input('is_active', TRUE),
                    'expiry_date'           => $request->input('expiry_date'),
                ]);
                $message = 'Price alert created successfully';
            }

            return $this->successResponse([
                'message'                       => $message,
                'alert'                         => $alert,
                'estimated_trigger_probability' => $this->calculateTriggerProbability($alert, $event),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to create price alert', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Configure automated purchasing for an event
     */
    public function configureAutoPurchase(Request $request, Event $event): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'max_price'             => 'required|numeric|min:1',
            'quantity'              => 'required|integer|min:1|max:10',
            'section_preferences'   => 'array',
            'section_preferences.*' => 'string',
            'payment_method_id'     => 'required|string',
            'is_active'             => 'boolean',
            'purchase_window'       => 'array',
            'purchase_window.start' => 'nullable|date',
            'purchase_window.end'   => 'nullable|date|after:purchase_window.start',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();

            $config = $this->purchasingService->configureAutoPurchase($user, $event, [
                'max_price'           => $request->input('max_price'),
                'quantity'            => $request->input('quantity'),
                'section_preferences' => $request->input('section_preferences', []),
                'payment_method_id'   => $request->input('payment_method_id'),
                'is_active'           => $request->input('is_active', TRUE),
                'purchase_window'     => $request->input('purchase_window', []),
            ]);

            return $this->successResponse([
                'message'                       => 'Auto-purchase configuration saved successfully',
                'config'                        => $config,
                'estimated_success_probability' => $this->calculatePurchaseSuccessProbability($config, $event),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to configure auto-purchase', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Search events with advanced filtering
     */
    public function search(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query'            => 'required|string|min:2|max:255',
            'filters'          => 'array',
            'location'         => 'string|max:255',
            'date_range'       => 'array',
            'date_range.start' => 'date',
            'date_range.end'   => 'date|after_or_equal:date_range.start',
            'price_range'      => 'array',
            'price_range.min'  => 'numeric|min:0',
            'price_range.max'  => 'numeric|min:0',
            'categories'       => 'array',
            'categories.*'     => 'string',
            'sort_by'          => 'string|in:relevance,date,price,popularity',
            'limit'            => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            // Rate limiting for search operations
            $user = Auth::user();
            $rateLimitKey = "search_events:{$user->id}";
            if (RateLimiter::tooManyAttempts($rateLimitKey, 20)) {
                $seconds = RateLimiter::availableIn($rateLimitKey);

                return $this->errorResponse('Search rate limit exceeded', [
                    'retry_after' => $seconds,
                ], 429);
            }

            RateLimiter::hit($rateLimitKey, 3600);

            $searchResults = $this->performAdvancedSearch($request);

            return $this->successResponse([
                'query'       => $request->input('query'),
                'results'     => $searchResults['events'],
                'total_found' => $searchResults['total'],
                'search_time' => $searchResults['search_time'],
                'suggestions' => $searchResults['suggestions'],
                'facets'      => $searchResults['facets'],
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Search failed', ['error' => $e->getMessage()]);
        }
    }

    // Protected helper methods

    protected function successResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success'   => TRUE,
            'data'      => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    protected function errorResponse(string $message, array $errors = [], int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success'   => FALSE,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    private function getEventMonitoringStatus(Event $event, $user): array
    {
        $monitor = EventMonitor::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$monitor) {
            return ['status' => 'not_monitored'];
        }

        return [
            'status'       => 'monitored',
            'is_active'    => $monitor->is_active,
            'priority'     => $monitor->priority,
            'platforms'    => $monitor->platforms,
            'last_check'   => $monitor->last_check_at,
            'success_rate' => $monitor->getSuccessRate(),
        ];
    }

    private function identifyCurrentOpportunities(Event $event): array
    {
        // Implementation would analyze current market conditions
        return [];
    }

    private function generateEventRecommendations(Event $event, $user): array
    {
        // Implementation would generate personalized recommendations
        return [];
    }

    private function calculateTriggerProbability(PriceAlert $alert, Event $event): float
    {
        // Implementation would calculate probability based on historical data
        return 0.75;
    }

    private function calculatePurchaseSuccessProbability($config, Event $event): float
    {
        // Implementation would calculate success probability
        return 0.85;
    }

    private function performAdvancedSearch(Request $request): array
    {
        // Implementation would perform sophisticated search with ML ranking
        return [
            'events'      => [],
            'total'       => 0,
            'search_time' => 0.25,
            'suggestions' => [],
            'facets'      => [],
        ];
    }
}
