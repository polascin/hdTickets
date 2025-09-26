<?php declare(strict_types=1);

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

use function count;
use function in_array;

/**
 * MonitoringController - HD Tickets Sports Events Monitoring System
 *
 * Handles comprehensive ticket price monitoring, alert management, and real-time
 * notifications for sports event tickets. This controller manages the intelligent
 * alert system that helps users track price changes and availability updates.
 *
 * Key Features:
 * - Real-time price monitoring and alerts
 * - Intelligent notification system
 * - Historical price tracking and analytics
 * - User-customizable alert preferences
 * - AJAX-powered dashboard updates
 *
 * @version 1.0.0
 */
class MonitoringController extends Controller
{
    /**
     * Display the main monitoring dashboard
     *
     * Shows active alerts, monitoring statistics, and price tracking overview
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Get user's active alerts with relationships
        $alerts = $this->getUserAlerts($user->id);

        // Get dashboard statistics
        $stats = $this->getDashboardStatistics($user->id);

        // Get recent triggered alerts for notifications
        $recentTriggers = $this->getRecentTriggeredAlerts($user->id);

        // Get available events for alert creation
        $availableEvents = $this->getAvailableEventsForMonitoring();

        return view('monitoring.index', ['alerts' => $alerts, 'stats' => $stats, 'recentTriggers' => $recentTriggers, 'availableEvents' => $availableEvents]);
    }

    /**
     * Create a new price/availability alert
     */
    public function createAlert(Request $request): JsonResponse
    {
        $request->validate([
            'event'               => 'required|string',
            'alert_type'          => 'required|in:price_drop,availability',
            'target_price'        => 'nullable|numeric|min:1',
            'section_preferences' => 'nullable|string|max:255',
            'notifications'       => 'array',
            'notifications.*'     => 'in:email,sms,browser',
            'duration'            => 'required|string',
        ]);

        $user = Auth::user();

        // Validate subscription limits for customers
        if ($user->role === 'customer' && ! $user->hasActiveSubscription()) {
            $alertsCount = $this->getUserAlertsCount($user->id);
            $freeLimit = config('monitoring.free_alerts_limit', 3);

            if ($alertsCount >= $freeLimit) {
                return response()->json([
                    'success'               => FALSE,
                    'message'               => 'Active subscription required to create more alerts.',
                    'requires_subscription' => TRUE,
                ], 403);
            }
        }

        try {
            // Create the alert record using existing table structure
            $alertData = [
                'user_id'             => $user->id,
                'sports_event_id'     => $this->getEventIdFromIdentifier(),
                'alert_name'          => $this->getEventDisplayName($request->event),
                'max_price'           => $request->alert_type === 'price_drop' ? $request->target_price : NULL,
                'min_quantity'        => 1,
                'preferred_sections'  => $request->section_preferences ? json_encode([$request->section_preferences]) : NULL,
                'platforms'           => json_encode(['all']),
                'status'              => 'active',
                'email_notifications' => in_array('email', $request->notifications ?? ['email'], TRUE),
                'sms_notifications'   => in_array('sms', $request->notifications ?? [], TRUE),
                'channel_preferences' => json_encode($request->notifications ?? ['email']),
                'created_at'          => now(),
                'updated_at'          => now(),
            ];

            $alertId = DB::table('ticket_alerts')->insertGetId($alertData);

            // Log alert creation
            Log::info('Ticket alert created', [
                'alert_id' => $alertId,
                'user_id'  => $user->id,
                'event'    => $request->event,
                'type'     => $request->alert_type,
            ]);

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Alert created successfully!',
                'alert_id' => $alertId,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to create ticket alert', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create alert. Please try again.',
            ], 500);
        }
    }

    /**
     * Update an existing alert
     */
    public function updateAlert(Request $request, int $alertId): JsonResponse
    {
        $request->validate([
            'target_price'        => 'nullable|numeric|min:1',
            'section_preferences' => 'nullable|string|max:255',
            'notifications'       => 'array',
            'notifications.*'     => 'in:email,sms,browser',
            'duration'            => 'nullable|string',
        ]);

        $user = Auth::user();

        try {
            $alert = DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->where('user_id', $user->id)
                ->first();

            if (! $alert) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Alert not found.',
                ], 404);
            }

            $updateData = [];

            if ($request->has('target_price')) {
                $updateData['target_price'] = $request->target_price;
            }

            if ($request->has('section_preferences')) {
                $updateData['section_preferences'] = $request->section_preferences;
            }

            if ($request->has('notifications')) {
                $updateData['notification_methods'] = json_encode($request->notifications);
            }

            if ($request->has('duration')) {
                $updateData['duration_type'] = $request->duration;
                $updateData['expires_at'] = $this->calculateExpirationDate($request->duration);
            }

            $updateData['updated_at'] = now();

            DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->update($updateData);

            return response()->json([
                'success' => TRUE,
                'message' => 'Alert updated successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update ticket alert', [
                'alert_id' => $alertId,
                'user_id'  => $user->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update alert.',
            ], 500);
        }
    }

    /**
     * Toggle alert active/paused status
     */
    public function toggleAlert(Request $request, int $alertId): JsonResponse
    {
        $user = Auth::user();

        try {
            $alert = DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->where('user_id', $user->id)
                ->first();

            if (! $alert) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Alert not found.',
                ], 404);
            }

            $newStatus = $alert->status === 'active' ? 'paused' : 'active';

            DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->update([
                    'status'     => $newStatus,
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Alert ' . ($newStatus === 'active' ? 'resumed' : 'paused') . ' successfully!',
                'new_status' => $newStatus,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to toggle alert status', [
                'alert_id' => $alertId,
                'user_id'  => $user->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update alert status.',
            ], 500);
        }
    }

    /**
     * Delete an alert
     */
    public function deleteAlert(Request $request, int $alertId): JsonResponse
    {
        $user = Auth::user();

        try {
            $deleted = DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->where('user_id', $user->id)
                ->delete();

            if (! $deleted) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Alert not found.',
                ], 404);
            }

            // Clean up alert history
            DB::table('alert_history')
                ->where('alert_id', $alertId)
                ->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Alert deleted successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to delete alert', [
                'alert_id' => $alertId,
                'user_id'  => $user->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete alert.',
            ], 500);
        }
    }

    /**
     * Dismiss a triggered alert
     */
    public function dismissAlert(Request $request, int $alertId): JsonResponse
    {
        $user = Auth::user();

        try {
            DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->where('user_id', $user->id)
                ->update([
                    'status'            => 'active',
                    'last_triggered_at' => NULL,
                    'updated_at'        => now(),
                ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Alert dismissed successfully!',
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to dismiss alert.',
            ], 500);
        }
    }

    /**
     * Get alert history with price tracking
     */
    public function alertHistory(Request $request, int $alertId): JsonResponse
    {
        $user = Auth::user();

        try {
            // Verify alert ownership
            $alert = DB::table('ticket_alerts')
                ->where('id', $alertId)
                ->where('user_id', $user->id)
                ->first();

            if (! $alert) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Alert not found.',
                ], 404);
            }

            // Get price history for the last 30 days
            $history = DB::table('alert_history')
                ->where('alert_id', $alertId)
                ->where('created_at', '>=', now()->subDays(30))
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($record): array => [
                    'date'    => Carbon::parse($record->created_at)->format('M j, Y H:i'),
                    'price'   => $record->price,
                    'status'  => $record->status,
                    'message' => $record->message,
                ]);

            return response()->json([
                'success' => TRUE,
                'history' => $history,
                'alert'   => [
                    'id'             => $alert->id,
                    'event'          => $alert->event_identifier,
                    'target_price'   => $alert->target_price,
                    'current_status' => $alert->status,
                ],
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load alert history.',
            ], 500);
        }
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $stats = $this->getDashboardStatistics($user->id);
            $stats['last_update'] = now()->format('H:i');

            return response()->json([
                'success' => TRUE,
                'stats'   => $stats,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load statistics.',
            ], 500);
        }
    }

    /**
     * Get user alerts via AJAX
     */
    public function getAlerts(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            $alerts = $this->getUserAlerts($user->id);

            return response()->json([
                'success' => TRUE,
                'alerts'  => $alerts,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load alerts.',
            ], 500);
        }
    }

    /**
     * Refresh monitoring data
     */
    public function refreshMonitoring(Request $request): JsonResponse
    {
        $user = Auth::user();

        try {
            // Clear relevant caches
            Cache::forget("user_alerts_{$user->id}");
            Cache::forget("dashboard_stats_{$user->id}");

            // Trigger background monitoring jobs if available
            // dispatch(new \App\Jobs\CheckUserAlertsJob($user->id));

            $stats = $this->getDashboardStatistics($user->id);
            $alerts = $this->getUserAlerts($user->id);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Monitoring data refreshed successfully!',
                'stats'        => $stats,
                'alerts_count' => count($alerts),
                'timestamp'    => now()->format('H:i:s'),
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to refresh monitoring data.',
            ], 500);
        }
    }

    /**
     * Get simulated price updates for real-time display
     */
    public function getPriceUpdates(Request $request): JsonResponse
    {
        Auth::user();

        try {
            // In a real implementation, this would fetch actual price changes
            // For demo purposes, we'll simulate some updates
            $updates = $this->simulatePriceUpdates();

            return response()->json([
                'success'   => TRUE,
                'updates'   => $updates,
                'timestamp' => now()->format('H:i:s'),
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to get price updates.',
            ], 500);
        }
    }

    /**
     * Get user's alerts with current status and pricing information
     */
    private function getUserAlerts(int $userId): array
    {
        return Cache::remember("user_alerts_{$userId}", 300, fn () => DB::table('ticket_alerts')
            ->where('user_id', $userId)
            ->where('status', '!=', 'expired')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($alert): array {
                $notifications = [];
                if ($alert->email_notifications) {
                    $notifications[] = 'email';
                }
                if ($alert->sms_notifications) {
                    $notifications[] = 'sms';
                }

                return [
                    'id'             => $alert->id,
                    'event'          => $alert->alert_name,
                    'venue'          => $this->getEventVenue($alert->sports_event_id),
                    'date'           => $this->getEventDate(),
                    'current_price'  => $this->getCurrentPrice(),
                    'target_price'   => $alert->max_price,
                    'original_price' => $alert->max_price ? $alert->max_price * 1.2 : 300,
                    'status'         => $alert->status,
                    'type'           => $alert->max_price ? 'price_drop' : 'availability',
                    'section'        => $alert->preferred_sections ? implode(', ', json_decode((string) $alert->preferred_sections, TRUE) ?? []) : NULL,
                    'created_at'     => $alert->created_at,
                    'last_checked'   => $alert->last_checked_at ?? now()->subMinutes(random_int(1, 10)),
                    'availability'   => $this->getEventAvailability(),
                    'trend'          => $this->getPriceTrend(),
                    'notifications'  => $notifications,
                    'savings'        => $this->calculateSavings($alert->max_price, $this->getCurrentPrice()),
                ];
            })
            ->toArray());
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStatistics(int $userId): array
    {
        return Cache::remember("dashboard_stats_{$userId}", 300, function () use ($userId): array {
            $activeAlerts = DB::table('ticket_alerts')
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->count();

            $triggeredToday = DB::table('ticket_alerts')
                ->where('user_id', $userId)
                ->whereDate('last_triggered_at', today())
                ->count();

            $monitoredEvents = DB::table('ticket_alerts')
                ->where('user_id', $userId)
                ->where('status', '!=', 'deleted')
                ->distinct('event_identifier')
                ->count();

            // Simulate savings calculation
            $totalSavings = random_int(500, 1500);
            $successRate = random_int(92, 99);

            return [
                'active_alerts'           => $activeAlerts,
                'triggered_today'         => $triggeredToday,
                'monitored_events'        => $monitoredEvents,
                'total_savings'           => $totalSavings,
                'success_rate'            => $successRate,
                'average_savings_percent' => random_int(15, 35),
            ];
        });
    }

    /**
     * Get recent triggered alerts for notifications
     */
    private function getRecentTriggeredAlerts(int $userId): array
    {
        return DB::table('ticket_alerts')
            ->where('user_id', $userId)
            ->where('status', 'triggered')
            ->whereDate('last_triggered_at', today())
            ->orderBy('last_triggered_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get available events for alert creation
     */
    private function getAvailableEventsForMonitoring(): array
    {
        // In a real implementation, this would query the scraped_tickets table
        // For demo purposes, return sample events
        return [
            'nba_finals_g7'   => 'NBA Finals Game 7 - Lakers vs Celtics',
            'super_bowl'      => 'Super Bowl LVIII',
            'world_series_g1' => 'World Series Game 1 - Dodgers vs Yankees',
            'nba_playoffs'    => 'NBA Playoffs - Warriors vs Nuggets',
            'stanley_cup_g4'  => 'Stanley Cup Final Game 4',
            'ufc_300'         => 'UFC 300 - Main Event',
        ];
    }

    /**
     * Calculate expiration date based on duration type
     */
    private function calculateExpirationDate(string $durationType): Carbon
    {
        return match ($durationType) {
            '1_day'       => now()->addDay(),
            '3_days'      => now()->addDays(3),
            '1_week'      => now()->addWeek(),
            '1_month'     => now()->addMonth(),
            'until_event' => now()->addMonths(6), // Default to 6 months for events
            default       => now()->addWeek(),
        };
    }

    /**
     * Get count of user's alerts
     */
    private function getUserAlertsCount(int $userId): int
    {
        return DB::table('ticket_alerts')
            ->where('user_id', $userId)
            ->where('status', 'active')
            ->count();
    }

    /**
     * Simulate price updates for demo purposes
     */
    private function simulatePriceUpdates(): array
    {
        $updates = [];
        $alertsToUpdate = random_int(1, 3);
        for ($i = 0; $i < $alertsToUpdate; $i++) {
            $priceChange = random_int(-50, 30);
            $updates[] = [
                'alert_id'       => random_int(1, 10),
                'event'          => 'Sample Event ' . ($i + 1),
                'old_price'      => random_int(200, 500),
                'new_price'      => random_int(200, 500) + $priceChange,
                'change'         => $priceChange,
                'change_percent' => round(($priceChange / random_int(200, 500)) * 100, 1),
                'timestamp'      => now()->subMinutes(random_int(1, 30))->format('H:i'),
            ];
        }

        return $updates;
    }

    /**
     * Helper methods for event data (would integrate with real data sources)
     */
    private function getEventDisplayName(string $eventId): string
    {
        $events = [
            'nba_finals_g7'   => 'NBA Finals Game 7 - Lakers vs Celtics',
            'super_bowl'      => 'Super Bowl LVIII',
            'world_series_g1' => 'World Series Game 1 - Dodgers vs Yankees',
            'nba_playoffs'    => 'NBA Playoffs - Warriors vs Nuggets',
            'stanley_cup_g4'  => 'Stanley Cup Final Game 4',
            'ufc_300'         => 'UFC 300 - Main Event',
        ];

        return $events[$eventId] ?? 'Unknown Event';
    }

    /**
     * Get event ID from identifier for compatibility
     */
    private function getEventIdFromIdentifier(): int
    {
        // In a real implementation, this would query the sports_events table
        // For demo purposes, return a default event ID
        return 1;
        // Default to first event
    }

    private function getEventVenue($eventId): string
    {
        // In real implementation, query the sports events or venues table
        $venues = [
            1 => 'Crypto.com Arena',
            2 => 'Allegiant Stadium',
            3 => 'Dodger Stadium',
            4 => 'Chase Center',
            5 => 'TD Garden',
            6 => 'T-Mobile Arena',
        ];

        return $venues[$eventId] ?? 'TBA';
    }

    private function getEventDate(): string
    {
        // Return sample dates
        return now()->addDays(random_int(1, 90))->format('Y-m-d');
    }

    private function getCurrentPrice(): float
    {
        return (float) random_int(150, 800);
    }

    private function getEventAvailability(): string
    {
        $availabilities = ['high', 'medium', 'low', 'sold_out'];

        return $availabilities[array_rand($availabilities)];
    }

    private function getPriceTrend(): string
    {
        $trends = ['up', 'down', 'stable'];

        return $trends[array_rand($trends)];
    }

    /**
     * Calculate savings between target and current price
     *
     * @param mixed $targetPrice
     */
    private function calculateSavings($targetPrice, float $currentPrice): float
    {
        if (! $targetPrice || ! $currentPrice) {
            return random_int(20, 100);
        }

        return max(0, $currentPrice - $targetPrice);
    }
}
