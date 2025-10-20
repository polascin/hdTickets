<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\SmartAlert\CreateSmartAlertRequest;
use App\Http\Requests\SmartAlert\UpdateSmartAlertRequest;
use App\Models\SmartAlert;
use App\Services\SmartAlertsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Log;

/**
 * Smart Alerts Controller
 *
 * Handles the management of intelligent ticket alerts inspired by TicketScoutie's
 * smart alert system. Provides functionality for creating, managing, and triggering
 * multi-channel notifications based on ticket availability and price changes.
 */
class SmartAlertsController extends Controller
{
    public function __construct(
        private readonly SmartAlertsService $smartAlertsService,
    ) {
    }

    /**
     * Display smart alerts dashboard
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $alerts = SmartAlert::where('user_id', $user->id)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_alerts'    => SmartAlert::where('user_id', $user->id)->count(),
            'active_alerts'   => SmartAlert::where('user_id', $user->id)->where('is_active', TRUE)->count(),
            'triggered_today' => SmartAlert::where('user_id', $user->id)
                ->where('last_triggered_at', '>=', now()->startOfDay())
                ->count(),
            'alerts_this_month' => SmartAlert::where('user_id', $user->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('smart-alerts.index', compact('alerts', 'stats'));
    }

    /**
     * Store a new smart alert
     */
    public function store(CreateSmartAlertRequest $request): JsonResponse
    {
        try {
            $alert = SmartAlert::create([
                'user_id'               => $request->user()->id,
                'name'                  => $request->input('name'),
                'description'           => $request->input('description'),
                'alert_type'            => $request->input('alert_type'),
                'trigger_conditions'    => $request->input('trigger_conditions'),
                'notification_channels' => $request->input('notification_channels'),
                'notification_settings' => $request->input('notification_settings', []),
                'is_active'             => $request->input('is_active', TRUE),
                'priority'              => $request->input('priority', 'medium'),
                'cooldown_minutes'      => $request->input('cooldown_minutes', 30),
                'max_triggers_per_day'  => $request->input('max_triggers_per_day', 10),
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Smart alert created successfully',
                'alert'   => $alert->load('user'),
            ], 201);
        } catch (Exception $e) {
            Log::error('Failed to create smart alert', [
                'error'        => $e->getMessage(),
                'user_id'      => $request->user()->id,
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create smart alert',
            ], 500);
        }
    }

    /**
     * Display a specific smart alert
     */
    public function show(Request $request, SmartAlert $alert): JsonResponse
    {
        // Ensure user can only access their own alerts
        if ($alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        return response()->json([
            'success'    => TRUE,
            'alert'      => $alert->load('user'),
            'statistics' => [
                'total_triggers'       => $alert->trigger_count,
                'last_triggered'       => $alert->last_triggered_at?->format('Y-m-d H:i:s'),
                'triggers_today'       => $alert->triggers_today,
                'avg_triggers_per_day' => $alert->getAverageTriggersPerDay(),
            ],
        ]);
    }

    /**
     * Update a smart alert
     */
    public function update(UpdateSmartAlertRequest $request, SmartAlert $alert): JsonResponse
    {
        // Ensure user can only update their own alerts
        if ($alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        try {
            $alert->update($request->validated());

            return response()->json([
                'success' => TRUE,
                'message' => 'Smart alert updated successfully',
                'alert'   => $alert->fresh()->load('user'),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update smart alert', [
                'error'        => $e->getMessage(),
                'alert_id'     => $alert->id,
                'user_id'      => $request->user()->id,
                'request_data' => $request->validated(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update smart alert',
            ], 500);
        }
    }

    /**
     * Delete a smart alert
     */
    public function destroy(Request $request, SmartAlert $alert): JsonResponse
    {
        // Ensure user can only delete their own alerts
        if ($alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        try {
            $alert->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Smart alert deleted successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete smart alert', [
                'error'    => $e->getMessage(),
                'alert_id' => $alert->id,
                'user_id'  => $request->user()->id,
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete smart alert',
            ], 500);
        }
    }

    /**
     * Toggle alert active status
     */
    public function toggle(Request $request, SmartAlert $alert): JsonResponse
    {
        // Ensure user can only toggle their own alerts
        if ($alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        try {
            $alert->update(['is_active' => ! $alert->is_active]);

            return response()->json([
                'success'   => TRUE,
                'message'   => 'Alert status updated successfully',
                'is_active' => $alert->is_active,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to toggle smart alert', [
                'error'    => $e->getMessage(),
                'alert_id' => $alert->id,
                'user_id'  => $request->user()->id,
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to toggle alert status',
            ], 500);
        }
    }

    /**
     * Test a smart alert (manually trigger for testing)
     */
    public function test(Request $request, SmartAlert $alert): JsonResponse
    {
        // Ensure user can only test their own alerts
        if ($alert->user_id !== $request->user()->id) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Alert not found',
            ], 404);
        }

        try {
            $result = $this->smartAlertsService->testAlert($alert);

            return response()->json([
                'success' => TRUE,
                'message' => 'Test alert sent successfully',
                'result'  => $result,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to test smart alert', [
                'error'    => $e->getMessage(),
                'alert_id' => $alert->id,
                'user_id'  => $request->user()->id,
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to send test alert',
            ], 500);
        }
    }

    /**
     * Get alert templates for easy setup
     */
    public function getTemplates(): JsonResponse
    {
        $templates = [
            [
                'name'               => 'Price Drop Alert',
                'description'        => 'Get notified when ticket prices drop below your target',
                'alert_type'         => 'price_drop',
                'trigger_conditions' => [
                    'price_threshold'   => 100,
                    'percentage_drop'   => 10,
                    'comparison_period' => '24h',
                ],
                'notification_channels' => ['email', 'push'],
                'priority'              => 'medium',
            ],
            [
                'name'               => 'New Tickets Available',
                'description'        => 'Alert when new tickets become available for your events',
                'alert_type'         => 'availability',
                'trigger_conditions' => [
                    'event_keywords' => [],
                    'venue_keywords' => [],
                    'date_range'     => ['start' => NULL, 'end' => NULL],
                ],
                'notification_channels' => ['email', 'push', 'sms'],
                'priority'              => 'high',
            ],
            [
                'name'               => 'Instant Deal Alert',
                'description'        => 'Immediate notification for hot deals and limited offers',
                'alert_type'         => 'instant_deal',
                'trigger_conditions' => [
                    'discount_percentage' => 25,
                    'limited_quantity'    => TRUE,
                    'time_sensitive'      => TRUE,
                ],
                'notification_channels' => ['push', 'sms'],
                'priority'              => 'urgent',
            ],
            [
                'name'               => 'Platform Comparison',
                'description'        => 'Compare prices across multiple platforms',
                'alert_type'         => 'price_comparison',
                'trigger_conditions' => [
                    'platforms'                  => ['ticketmaster', 'stubhub', 'viagogo'],
                    'price_difference_threshold' => 20,
                ],
                'notification_channels' => ['email'],
                'priority'              => 'low',
            ],
        ];

        return response()->json([
            'success'   => TRUE,
            'templates' => $templates,
        ]);
    }
}
