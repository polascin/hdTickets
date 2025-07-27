<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebPushSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PushSubscriptionController extends Controller
{
    /**
     * Subscribe to push notifications
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'subscription.endpoint' => 'required|string|url',
                'subscription.keys.p256dh' => 'required|string',
                'subscription.keys.auth' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid subscription data',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $subscriptionData = $request->input('subscription');

            // Check if subscription already exists
            $existingSubscription = WebPushSubscription::where('user_id', $user->id)
                ->where('endpoint', $subscriptionData['endpoint'])
                ->first();

            if ($existingSubscription) {
                // Update existing subscription
                $existingSubscription->update([
                    'p256dh_key' => $subscriptionData['keys']['p256dh'],
                    'auth_token' => $subscriptionData['keys']['auth'],
                    'user_agent' => $request->header('User-Agent'),
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                $subscription = $existingSubscription;
            } else {
                // Create new subscription
                $subscription = WebPushSubscription::create([
                    'user_id' => $user->id,
                    'endpoint' => $subscriptionData['endpoint'],
                    'p256dh_key' => $subscriptionData['keys']['p256dh'],
                    'auth_token' => $subscriptionData['keys']['auth'],
                    'user_agent' => $request->header('User-Agent'),
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);
            }

            Log::info('Push subscription created/updated', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'endpoint' => $subscription->endpoint,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Push subscription saved successfully',
                'data' => [
                    'id' => $subscription->id,
                    'is_active' => $subscription->is_active,
                    'created_at' => $subscription->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save push subscription', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save push subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'endpoint' => 'required|string|url',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid endpoint',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            $endpoint = $request->input('endpoint');

            $subscription = WebPushSubscription::where('user_id', $user->id)
                ->where('endpoint', $endpoint)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            $subscription->update(['is_active' => false]);

            Log::info('Push subscription deactivated', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'endpoint' => $endpoint,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully unsubscribed from push notifications'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to unsubscribe from push notifications', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's push subscriptions
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $subscriptions = WebPushSubscription::where('user_id', $user->id)
                ->where('is_active', true)
                ->get()
                ->map(function ($subscription) {
                    return [
                        'id' => $subscription->id,
                        'endpoint' => $subscription->endpoint,
                        'user_agent' => $subscription->user_agent,
                        'is_active' => $subscription->is_active,
                        'last_used_at' => $subscription->last_used_at,
                        'created_at' => $subscription->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'subscriptions' => $subscriptions,
                    'total' => $subscriptions->count(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve push subscriptions', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve subscriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test push notification
     */
    public function test(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $subscriptions = WebPushSubscription::where('user_id', $user->id)
                ->where('is_active', true)
                ->get();

            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active push subscriptions found'
                ], 404);
            }

            // Use the notification system to send a test notification
            $notificationManager = app(\App\Services\NotificationSystem\NotificationManager::class);
            
            $testNotification = [
                'type' => 'test_notification',
                'title' => 'Test Notification 🔔',
                'message' => 'Your push notifications are working correctly!',
                'data' => [
                    'test' => true,
                    'timestamp' => now()->toISOString(),
                    'css_timestamp' => now()->timestamp,
                ],
                'priority' => 3,
                'channels' => ['web_push'],
                'expires_at' => now()->addMinutes(5),
            ];

            $success = $this->sendTestPushNotification($user, $testNotification);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Test notification sent successfully' : 'Failed to send test notification',
                'data' => [
                    'subscriptions_count' => $subscriptions->count(),
                    'sent_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send test push notification', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subscription settings
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_active' => 'sometimes|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            $subscription = WebPushSubscription::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            $subscription->update($request->only(['is_active']));

            return response()->json([
                'success' => true,
                'message' => 'Subscription updated successfully',
                'data' => [
                    'id' => $subscription->id,
                    'is_active' => $subscription->is_active,
                    'updated_at' => $subscription->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update push subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a push subscription
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = Auth::user();

            $subscription = WebPushSubscription::where('user_id', $user->id)
                ->where('id', $id)
                ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }

            $subscription->delete();

            Log::info('Push subscription deleted', [
                'user_id' => $user->id,
                'subscription_id' => $id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete push subscription', [
                'user_id' => Auth::id(),
                'subscription_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send test push notification directly through WebPush channel
     */
    private function sendTestPushNotification($user, array $notification): bool
    {
        try {
            $webPushChannel = app(\App\Services\NotificationSystem\Channels\WebPushChannel::class);
            return $webPushChannel->send($user, $notification);
        } catch (\Exception $e) {
            Log::error('Failed to send test push notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
