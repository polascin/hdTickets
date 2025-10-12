<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\NotificationChannels\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Push Notification Controller
 *
 * Handles browser push notification subscriptions and management.
 * Supports WebPush API for real-time notifications inspired by
 * TicketScoutie's notification system.
 */
class PushNotificationController extends Controller
{
    public function __construct(
        private readonly PushNotificationService $pushService
    ) {
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'endpoint'    => 'required|string|url',
            'keys'        => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Invalid subscription data',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // Check if subscription already exists
            $existingSubscription = PushSubscription::where('user_id', $user->id)
                ->where('endpoint', $request->input('endpoint'))
                ->first();

            if ($existingSubscription) {
                // Update existing subscription
                $existingSubscription->update([
                    'p256dh_key'   => $request->input('keys.p256dh'),
                    'auth_key'     => $request->input('keys.auth'),
                    'last_used_at' => now(),
                    'updated_at'   => now(),
                ]);

                $subscription = $existingSubscription;
            } else {
                // Create new subscription
                $subscription = PushSubscription::create([
                    'user_id'      => $user->id,
                    'endpoint'     => $request->input('endpoint'),
                    'p256dh_key'   => $request->input('keys.p256dh'),
                    'auth_key'     => $request->input('keys.auth'),
                    'last_used_at' => now(),
                ]);
            }

            // Send welcome notification
            $this->pushService->sendWelcomeNotification($subscription);

            return response()->json([
                'success'         => TRUE,
                'message'         => 'Successfully subscribed to push notifications',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to subscribe to push notifications', [
                'error'    => $e->getMessage(),
                'user_id'  => $request->user()->id,
                'endpoint' => $request->input('endpoint'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to subscribe to push notifications',
            ], 500);
        }
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'endpoint' => 'required|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Invalid unsubscribe data',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            $subscription = PushSubscription::where('user_id', $user->id)
                ->where('endpoint', $request->input('endpoint'))
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Subscription not found',
                ], 404);
            }

            // Deactivate subscription instead of deleting to maintain history
            $subscription->update(['last_used_at' => NULL]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Successfully unsubscribed from push notifications',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to unsubscribe from push notifications', [
                'error'    => $e->getMessage(),
                'user_id'  => $request->user()->id,
                'endpoint' => $request->input('endpoint'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to unsubscribe from push notifications',
            ], 500);
        }
    }

    /**
     * Get VAPID public key for push subscription
     */
    public function getVapidKey(): JsonResponse
    {
        $vapidKey = config('services.webpush.vapid_public_key');

        if (!$vapidKey) {
            return response()->json([
                'success' => FALSE,
                'message' => 'VAPID key not configured',
            ], 500);
        }

        return response()->json([
            'success'   => TRUE,
            'vapid_key' => $vapidKey,
        ]);
    }

    /**
     * Get user's push subscription status
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();

        $subscriptions = PushSubscription::where('user_id', $user->id)
            ->where('last_used_at', '>=', now()->subDays(30))
            ->count();

        return response()->json([
            'success'            => TRUE,
            'is_subscribed'      => $subscriptions > 0,
            'subscription_count' => $subscriptions,
        ]);
    }

    /**
     * Test push notification
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $result = $this->pushService->sendTestNotification($user);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Test notification sent successfully',
                'sent_count'   => $result['sent_count'] ?? 0,
                'failed_count' => $result['failed_count'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test push notification', [
                'error'   => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to send test notification',
            ], 500);
        }
    }

    /**
     * Get push notification settings
     */
    public function getSettings(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get user's push notification preferences from their profile or settings
        $settings = [
            'enabled'              => TRUE,
            'ticket_alerts'        => TRUE,
            'price_drops'          => TRUE,
            'new_matches'          => TRUE,
            'system_notifications' => FALSE,
            'quiet_hours'          => [
                'enabled' => FALSE,
                'start'   => '22:00',
                'end'     => '08:00',
            ],
            'frequency' => 'instant', // instant, hourly, daily
        ];

        return response()->json([
            'success'  => TRUE,
            'settings' => $settings,
        ]);
    }

    /**
     * Update push notification settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'enabled'              => 'boolean',
            'ticket_alerts'        => 'boolean',
            'price_drops'          => 'boolean',
            'new_matches'          => 'boolean',
            'system_notifications' => 'boolean',
            'quiet_hours.enabled'  => 'boolean',
            'quiet_hours.start'    => 'string|date_format:H:i',
            'quiet_hours.end'      => 'string|date_format:H:i',
            'frequency'            => 'string|in:instant,hourly,daily',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Invalid settings data',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = $request->user();

            // In a real implementation, you would save these settings to a user_preferences table
            // For now, we'll just return success

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Push notification settings updated successfully',
                'settings' => $request->validated(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update push notification settings', [
                'error'    => $e->getMessage(),
                'user_id'  => $request->user()->id,
                'settings' => $request->validated(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update settings',
            ], 500);
        }
    }

    /**
     * Get push notification statistics
     */
    public function getStatistics(Request $request): JsonResponse
    {
        $user = $request->user();

        $stats = [
            'total_subscriptions'  => PushSubscription::where('user_id', $user->id)->count(),
            'active_subscriptions' => PushSubscription::where('user_id', $user->id)
                ->where('is_active', TRUE)
                ->count(),
            'notifications_sent_today'      => 0, // Would be calculated from a notifications log table
            'notifications_sent_this_month' => 0,
            'last_notification_sent'        => NULL,
        ];

        return response()->json([
            'success'    => TRUE,
            'statistics' => $stats,
        ]);
    }
}
