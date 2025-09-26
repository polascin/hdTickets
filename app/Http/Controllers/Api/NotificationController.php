<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateNotificationSettingsRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\NotificationSettings;
use App\Services\NotificationService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use function in_array;

class NotificationController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
    ) {
        $this->middleware('auth:sanctum');
        $this->middleware('verified');
    }

    /**
     * Get user's notifications with filtering and pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $page = $request->get('page', 1);
            $perPage = min($request->get('per_page', 20), 100);
            $type = $request->get('type'); // price_alert, availability_alert, system
            $read = $request->get('read'); // true, false, null for all

            $query = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            // Filter by type
            if ($type) {
                $query->where('type', $type);
            }

            // Filter by read status
            if ($read === 'true') {
                $query->whereNotNull('read_at');
            } elseif ($read === 'false') {
                $query->whereNull('read_at');
            }

            $notifications = $query->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'success'       => TRUE,
                'notifications' => NotificationResource::collection($notifications->items()),
                'total'         => $notifications->total(),
                'per_page'      => $perPage,
                'current_page'  => $page,
                'last_page'     => $notifications->lastPage(),
                'has_more'      => $notifications->hasMorePages(),
                'stats'         => $this->getNotificationStats($user->id),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to load notifications', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load notifications.',
            ], 500);
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(int $notificationId): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where([
                'id'      => $notificationId,
                'user_id' => $user->id,
            ])->firstOrFail();

            if (! $notification->read_at) {
                $notification->update(['read_at' => now()]);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Notification marked as read.',
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Notification not found.',
            ], 404);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to mark notification as read.',
            ], 500);
        }
    }

    /**
     * Mark a notification as unread
     */
    public function markAsUnread(int $notificationId): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where([
                'id'      => $notificationId,
                'user_id' => $user->id,
            ])->firstOrFail();

            $notification->update(['read_at' => NULL]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Notification marked as unread.',
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Notification not found.',
            ], 404);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to mark notification as unread.',
            ], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $user = Auth::user();

            $updatedCount = Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);

            return response()->json([
                'success'       => TRUE,
                'message'       => "Marked {$updatedCount} notifications as read.",
                'updated_count' => $updatedCount,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to mark all notifications as read.',
            ], 500);
        }
    }

    /**
     * Delete a specific notification
     */
    public function destroy(int $notificationId): JsonResponse
    {
        try {
            $user = Auth::user();

            $notification = Notification::where([
                'id'      => $notificationId,
                'user_id' => $user->id,
            ])->firstOrFail();

            $notification->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Notification deleted successfully.',
            ]);
        } catch (ModelNotFoundException) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Notification not found.',
            ], 404);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete notification.',
            ], 500);
        }
    }

    /**
     * Delete all read notifications
     */
    public function deleteRead(): JsonResponse
    {
        try {
            $user = Auth::user();

            $deletedCount = Notification::where('user_id', $user->id)
                ->whereNotNull('read_at')
                ->delete();

            return response()->json([
                'success'       => TRUE,
                'message'       => "Deleted {$deletedCount} read notifications.",
                'deleted_count' => $deletedCount,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete read notifications.',
            ], 500);
        }
    }

    /**
     * Get notification settings
     */
    public function getSettings(): JsonResponse
    {
        try {
            $user = Auth::user();

            $settings = NotificationSettings::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'email_notifications'         => TRUE,
                    'push_notifications'          => TRUE,
                    'price_drop_threshold'        => 'any',
                    'notification_frequency'      => 'instant',
                    'price_alerts_enabled'        => TRUE,
                    'availability_alerts_enabled' => TRUE,
                    'system_alerts_enabled'       => TRUE,
                    'marketing_notifications'     => FALSE,
                    'digest_time'                 => '09:00:00',
                ],
            );

            return response()->json([
                'success'  => TRUE,
                'settings' => [
                    'email_notifications'         => $settings->email_notifications,
                    'push_notifications'          => $settings->push_notifications,
                    'price_drop_threshold'        => $settings->price_drop_threshold,
                    'notification_frequency'      => $settings->notification_frequency,
                    'price_alerts_enabled'        => $settings->price_alerts_enabled,
                    'availability_alerts_enabled' => $settings->availability_alerts_enabled,
                    'system_alerts_enabled'       => $settings->system_alerts_enabled,
                    'marketing_notifications'     => $settings->marketing_notifications,
                    'digest_time'                 => $settings->digest_time,
                ],
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load notification settings.',
            ], 500);
        }
    }

    /**
     * Update notification settings
     */
    public function updateSettings(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $data = $request->validated();

            $settings = NotificationSettings::firstOrCreate(['user_id' => $user->id]);
            $settings->update($data);

            // Clear any cached notification preferences
            Cache::forget("notification_settings:{$user->id}");

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Notification settings updated successfully.',
                'settings' => $settings->only([
                    'email_notifications',
                    'push_notifications',
                    'price_drop_threshold',
                    'notification_frequency',
                    'price_alerts_enabled',
                    'availability_alerts_enabled',
                    'system_alerts_enabled',
                    'marketing_notifications',
                    'digest_time',
                ]),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update notification settings', [
                'user_id' => Auth::id(),
                'data'    => $request->all(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update notification settings.',
            ], 500);
        }
    }

    /**
     * Export notification history
     */
    public function export(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $format = $request->get('format', 'csv'); // csv, json, pdf

            if (! in_array($format, ['csv', 'json', 'pdf'], TRUE)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid export format. Use csv, json, or pdf.',
                ], 422);
            }

            $notifications = Notification::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'type', 'title', 'message', 'data', 'read_at', 'created_at']);

            $filename = $this->notificationService->exportNotifications($notifications, $format, $user);

            return response()->json([
                'success'             => TRUE,
                'message'             => 'Export completed successfully.',
                'download_url'        => Storage::url("exports/notifications/{$filename}"),
                'filename'            => $filename,
                'total_notifications' => $notifications->count(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to export notifications', [
                'user_id' => Auth::id(),
                'format'  => $request->get('format'),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export notifications.',
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $user = Auth::user();

            $cacheKey = "notification_stats:{$user->id}";

            $stats = Cache::remember($cacheKey, now()->addMinutes(10), fn (): array => $this->getNotificationStats($user->id));

            return response()->json([
                'success' => TRUE,
                'stats'   => $stats,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load notification statistics.',
            ], 500);
        }
    }

    /**
     * Test notification system
     */
    public function testNotification(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $type = $request->get('type', 'system');

            // Only allow in non-production environments or for admins
            if (app()->isProduction() && ! $user->isAdmin()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Test notifications not allowed in production.',
                ], 403);
            }

            $testNotification = $this->notificationService->createTestNotification($user, $type);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Test notification sent successfully.',
                'notification' => new NotificationResource($testNotification),
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to send test notification.',
            ], 500);
        }
    }

    /**
     * Get recent activity summary
     */
    public function recentActivity(): JsonResponse
    {
        try {
            $user = Auth::user();

            $cacheKey = "recent_activity:{$user->id}";

            $activity = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user): array {
                $recentNotifications = Notification::where('user_id', $user->id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get(['type', 'title', 'created_at', 'read_at']);

                return [
                    'total_this_week'               => $recentNotifications->count(),
                    'unread_this_week'              => $recentNotifications->whereNull('read_at')->count(),
                    'price_alerts_this_week'        => $recentNotifications->where('type', 'price_alert')->count(),
                    'availability_alerts_this_week' => $recentNotifications->where('type', 'availability_alert')->count(),
                    'recent_notifications'          => $recentNotifications->take(5),
                ];
            });

            return response()->json([
                'success'  => TRUE,
                'activity' => $activity,
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load recent activity.',
            ], 500);
        }
    }

    /**
     * Snooze notifications for a specified duration
     */
    public function snooze(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $duration = $request->get('duration', '1h'); // 15m, 1h, 4h, 1d

            $snoozeUntil = match ($duration) {
                '15m'   => now()->addMinutes(15),
                '1h'    => now()->addHour(),
                '4h'    => now()->addHours(4),
                '1d'    => now()->addDay(),
                default => now()->addHour(),
            };

            // Update user settings or create a snooze record
            $settings = NotificationSettings::firstOrCreate(['user_id' => $user->id]);
            $settings->update(['snoozed_until' => $snoozeUntil]);

            return response()->json([
                'success'       => TRUE,
                'message'       => "Notifications snoozed until {$snoozeUntil->format('M j, Y g:i A')}",
                'snoozed_until' => $snoozeUntil->toISOString(),
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to snooze notifications.',
            ], 500);
        }
    }

    /**
     * Unsnooze notifications
     */
    public function unsnooze(): JsonResponse
    {
        try {
            $user = Auth::user();

            $settings = NotificationSettings::where('user_id', $user->id)->first();

            if ($settings) {
                $settings->update(['snoozed_until' => NULL]);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Notifications unsnoozed successfully.',
            ]);
        } catch (Exception) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to unsnooze notifications.',
            ], 500);
        }
    }

    /**
     * Get notification statistics for a user
     */
    private function getNotificationStats(int $userId): array
    {
        $baseQuery = Notification::where('user_id', $userId);

        return [
            'total'               => $baseQuery->count(),
            'unread'              => $baseQuery->whereNull('read_at')->count(),
            'read'                => $baseQuery->whereNotNull('read_at')->count(),
            'price_alerts'        => $baseQuery->where('type', 'price_alert')->count(),
            'availability_alerts' => $baseQuery->where('type', 'availability_alert')->count(),
            'system_alerts'       => $baseQuery->where('type', 'system')->count(),
            'this_week'           => $baseQuery->where('created_at', '>=', now()->subWeek())->count(),
            'this_month'          => $baseQuery->where('created_at', '>=', now()->subMonth())->count(),
            'last_notification'   => $baseQuery->latest()->value('created_at'),
        ];
    }
}
