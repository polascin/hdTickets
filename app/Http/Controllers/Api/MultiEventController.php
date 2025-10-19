<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventGroup;
use App\Services\MultiEventManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Multi-Event Management API Controller
 *
 * Provides RESTful API endpoints for:
 * - Event group management
 * - Bulk operations
 * - Unified dashboard data
 * - Portfolio analytics
 * - Automation rules
 */
class MultiEventController extends Controller
{
    public function __construct(
        private MultiEventManagementService $multiEventService
    ) {
    }

    /**
     * Get user's event portfolio overview
     */
    public function portfolio(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $portfolio = $this->multiEventService->getEventPortfolio($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $portfolio,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve portfolio',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get unified dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $filters = [];
            if ($request->has('group_id')) {
                $filters['group_id'] = $request->input('group_id');
            }
            if ($request->has('category')) {
                $filters['category'] = $request->input('category');
            }

            $dashboardData = $this->multiEventService->getUnifiedDashboard($user, $filters);

            return response()->json([
                'success' => TRUE,
                'data'    => $dashboardData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve dashboard data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new event group
     */
    public function createGroup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'category'          => 'nullable|string|max:100',
            'color_code'        => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'settings'          => 'nullable|array',
            'monitoring_config' => 'nullable|array',
            'is_active'         => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $group = $this->multiEventService->createEventGroup($user, $request->all());

            return response()->json([
                'success' => TRUE,
                'message' => 'Event group created successfully',
                'data'    => $group,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create event group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add events to a group
     */
    public function addEventsToGroup(Request $request, EventGroup $group): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_ids'   => 'required|array|min:1',
            'event_ids.*' => 'required|integer|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Check if user owns the group
        if ($group->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized access to event group',
            ], 403);
        }

        try {
            $result = $this->multiEventService->addEventsToGroup($group, $request->input('event_ids'));

            return response()->json([
                'success' => TRUE,
                'message' => "Added {$result['success_count']} events to group",
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to add events to group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Execute bulk operation on multiple events
     */
    public function bulkOperation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'operation'   => 'required|string|in:start_monitoring,stop_monitoring,update_price_alerts,setup_auto_purchase,update_priority,export_data',
            'event_ids'   => 'required|array|min:1',
            'event_ids.*' => 'required|integer|exists:events,id',
            'parameters'  => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $result = $this->multiEventService->executeBulkOperation(
                $user,
                $request->input('operation'),
                $request->input('event_ids'),
                $request->input('parameters', [])
            );

            return response()->json([
                'success' => TRUE,
                'message' => "Bulk operation completed: {$result['success_count']} successful, {$result['error_count']} failed",
                'data'    => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to execute bulk operation',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Categorize events using smart analysis
     */
    public function categorizeEvents(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'event_ids'   => 'required|array|min:1',
            'event_ids.*' => 'required|integer|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $categorized = $this->multiEventService->categorizeEvents($request->input('event_ids'));

            return response()->json([
                'success' => TRUE,
                'message' => 'Events categorized successfully',
                'data'    => $categorized,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to categorize events',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create automation rule
     */
    public function createAutomationRule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'triggers'    => 'required|array',
            'actions'     => 'required|array',
            'is_active'   => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $rule = $this->multiEventService->createAutomationRule($user, $request->all());

            return response()->json([
                'success' => TRUE,
                'message' => 'Automation rule created successfully',
                'data'    => $rule,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create automation rule',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get event group details
     */
    public function getGroup(EventGroup $group): JsonResponse
    {
        // Check if user owns the group
        if ($group->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized access to event group',
            ], 403);
        }

        try {
            $group->load(['events', 'eventMonitors']);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'group'              => $group,
                    'performance_report' => $group->getWeeklyPerformanceReport(),
                    'health_status'      => $this->getGroupHealthStatus($group),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve group details',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update event group
     */
    public function updateGroup(Request $request, EventGroup $group): JsonResponse
    {
        // Check if user owns the group
        if ($group->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized access to event group',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name'              => 'sometimes|required|string|max:255',
            'description'       => 'nullable|string|max:1000',
            'category'          => 'nullable|string|max:100',
            'color_code'        => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'settings'          => 'nullable|array',
            'monitoring_config' => 'nullable|array',
            'is_active'         => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $group->update($request->only([
                'name', 'description', 'category', 'color_code',
                'settings', 'monitoring_config', 'is_active',
            ]));

            return response()->json([
                'success' => TRUE,
                'message' => 'Event group updated successfully',
                'data'    => $group->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update event group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete event group
     */
    public function deleteGroup(EventGroup $group): JsonResponse
    {
        // Check if user owns the group
        if ($group->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized access to event group',
            ], 403);
        }

        try {
            // Detach all events first
            $group->events()->detach();

            // Delete the group
            $group->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Event group deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete event group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove events from group
     */
    public function removeEventsFromGroup(Request $request, EventGroup $group): JsonResponse
    {
        // Check if user owns the group
        if ($group->user_id !== Auth::id()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Unauthorized access to event group',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'event_ids'   => 'required|array|min:1',
            'event_ids.*' => 'required|integer|exists:events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $eventIds = $request->input('event_ids');
            $group->events()->detach($eventIds);

            // Update group statistics
            $group->update([
                'total_events'     => $group->events()->count(),
                'last_modified_at' => now(),
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Events removed from group successfully',
                'data'    => [
                    'removed_count'    => count($eventIds),
                    'remaining_events' => $group->events()->count(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to remove events from group',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get portfolio recommendations
     */
    public function recommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $recommendations = $this->multiEventService->generatePortfolioRecommendations($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $recommendations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to generate recommendations',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Private helper methods

    private function getGroupHealthStatus(EventGroup $group): array
    {
        $monitors = $group->eventMonitors()->where('is_active', TRUE)->get();

        if ($monitors->isEmpty()) {
            return [
                'status'  => 'inactive',
                'message' => 'No active monitors in this group',
            ];
        }

        $healthStatuses = $monitors->map(fn ($monitor) => $monitor->getHealthStatus());

        $criticalCount = $healthStatuses->where('status', 'critical')->count();
        $warningCount = $healthStatuses->where('status', 'warning')->count();
        $healthyCount = $healthStatuses->where('status', 'healthy')->count();

        if ($criticalCount > 0) {
            $status = 'critical';
            $message = "{$criticalCount} monitors in critical state";
        } elseif ($warningCount > $healthyCount) {
            $status = 'warning';
            $message = "{$warningCount} monitors have warnings";
        } else {
            $status = 'healthy';
            $message = 'All monitors operating normally';
        }

        return [
            'status'    => $status,
            'message'   => $message,
            'breakdown' => [
                'healthy'  => $healthyCount,
                'warning'  => $warningCount,
                'critical' => $criticalCount,
            ],
            'total_monitors' => $monitors->count(),
        ];
    }
}
