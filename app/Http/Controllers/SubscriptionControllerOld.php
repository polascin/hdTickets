<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\UsageRecord;
use App\Models\User;
use App\Services\SubscriptionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Subscription Controller
 *
 * Handles subscription management including:
 * - Plan subscription and upgrades
 * - Payment processing and billing
 * - Usage tracking and limits
 * - Subscription cancellation and resumption
 * - Billing history and invoices
 */
class SubscriptionController extends Controller
{
    private SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->middleware('auth');
    }

    /**
     * Get available subscription plans
     */
    public function getPlans(): JsonResponse
    {
        try {
            $plans = $this->subscriptionService->getAvailablePlans();
            $user = Auth::user();
            $currentPlan = $user->subscription_plan ?? 'free';

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'plans'               => $plans,
                    'current_plan'        => $currentPlan,
                    'subscription_status' => $user->subscription_status ?? 'free',
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get subscription plans', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load subscription plans',
            ], 500);
        }
    }

    /**
     * Create new subscription
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan'              => 'required|string|in:starter,pro,enterprise',
                'payment_method_id' => 'required|string',
                'trial_days'        => 'nullable|integer|min:0|max:30',
            ]);

            $user = Auth::user();

            // Check if user already has active subscription
            if ($user->activeSubscription()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'You already have an active subscription. Use upgrade instead.',
                ], 400);
            }

            $options = [];
            if (isset($validated['trial_days'])) {
                $options['trial_days'] = $validated['trial_days'];
            }

            $result = $this->subscriptionService->createSubscription(
                $user,
                $validated['plan'],
                $validated['payment_method_id'],
                $options,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'subscription'    => $result['subscription'],
                    'billing_summary' => $result['subscription']->getBillingSummary(),
                ],
                'message' => $result['message'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to create subscription', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
                'plan'    => $request->input('plan'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to create subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upgrade/downgrade subscription
     */
    public function upgrade(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan'    => 'required|string|in:starter,pro,enterprise',
                'prorate' => 'nullable|boolean',
            ]);

            $user = Auth::user();
            $currentSubscription = $user->activeSubscription();

            if (!$currentSubscription) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No active subscription found. Create a subscription first.',
                ], 400);
            }

            if ($currentSubscription->plan_name === $validated['plan']) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'You are already on this plan.',
                ], 400);
            }

            $options = [
                'prorate' => $validated['prorate'] ?? TRUE,
            ];

            $result = $this->subscriptionService->updateSubscription(
                $user,
                $validated['plan'],
                $options,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'subscription'    => $result['subscription'],
                    'billing_summary' => $result['subscription']->getBillingSummary(),
                ],
                'message' => $result['message'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to upgrade subscription', [
                'error'    => $e->getMessage(),
                'user_id'  => Auth::id(),
                'new_plan' => $request->input('plan'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to upgrade subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'immediately' => 'nullable|boolean',
                'reason'      => 'nullable|string|max:500',
            ]);

            $user = Auth::user();

            $result = $this->subscriptionService->cancelSubscription(
                $user,
                $validated['immediately'] ?? FALSE,
                $validated['reason'] ?? NULL,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'subscription'    => $result['subscription'],
                    'billing_summary' => $result['subscription']->getBillingSummary(),
                ],
                'message' => $result['message'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to cancel subscription', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resume(): JsonResponse
    {
        try {
            $user = Auth::user();

            $result = $this->subscriptionService->resumeSubscription($user);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'subscription'    => $result['subscription'],
                    'billing_summary' => $result['subscription']->getBillingSummary(),
                ],
                'message' => $result['message'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to resume subscription', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to resume subscription: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current subscription details
     */
    public function current(): JsonResponse
    {
        try {
            $user = Auth::user();
            $subscription = $user->activeSubscription();

            if (!$subscription) {
                return response()->json([
                    'success' => TRUE,
                    'data'    => [
                        'subscription'    => NULL,
                        'billing_summary' => [
                            'plan'     => 'Free',
                            'status'   => 'free',
                            'features' => $this->subscriptionService->getUserLimits($user),
                        ],
                    ],
                ]);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'subscription'    => $subscription,
                    'billing_summary' => $subscription->getBillingSummary(),
                    'usage_summary'   => UsageRecord::getCurrentPeriodSummary($user),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get current subscription', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load subscription details',
            ], 500);
        }
    }

    /**
     * Get billing summary
     */
    public function billing(): JsonResponse
    {
        try {
            $user = Auth::user();
            $summary = $this->subscriptionService->getBillingSummary($user);

            return response()->json([
                'success' => TRUE,
                'data'    => $summary,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get billing summary', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load billing summary',
            ], 500);
        }
    }

    /**
     * Get payment history
     */
    public function payments(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'page'       => 'nullable|integer|min:1',
                'per_page'   => 'nullable|integer|min:1|max:100',
                'status'     => 'nullable|string|in:pending,succeeded,failed,cancelled,refunded',
                'start_date' => 'nullable|date',
                'end_date'   => 'nullable|date|after_or_equal:start_date',
            ]);

            $user = Auth::user();
            $query = Payment::where('user_id', $user->id)
                ->with('subscription')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if (isset($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (isset($validated['start_date'], $validated['end_date'])) {
                $query->dateRange($validated['start_date'], $validated['end_date']);
            }

            $payments = $query->paginate(
                $validated['per_page'] ?? 15,
                ['*'],
                'page',
                $validated['page'] ?? 1,
            );

            return response()->json([
                'success' => TRUE,
                'data'    => $payments,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get payment history', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load payment history',
            ], 500);
        }
    }

    /**
     * Get usage analytics
     */
    public function usage(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'resource_type' => 'nullable|string',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date|after_or_equal:start_date',
                'granularity'   => 'nullable|string|in:day,week,month',
            ]);

            $user = Auth::user();
            $query = UsageRecord::where('user_id', $user->id);

            // Apply filters
            if (isset($validated['resource_type'])) {
                $query->forResource($validated['resource_type']);
            }

            if (isset($validated['start_date'], $validated['end_date'])) {
                $query->dateRange(
                    \Carbon\Carbon::parse($validated['start_date']),
                    \Carbon\Carbon::parse($validated['end_date']),
                );
            } else {
                // Default to current billing period
                $subscription = $user->activeSubscription();
                if ($subscription) {
                    $query->currentBillingPeriod($subscription);
                }
            }

            $records = $query->orderBy('recorded_at', 'desc')->get();

            // Group by granularity if specified
            $granularity = $validated['granularity'] ?? 'day';
            $grouped = $this->groupUsageByGranularity($records, $granularity);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'records' => $records,
                    'grouped' => $grouped,
                    'summary' => UsageRecord::getCurrentPeriodSummary($user),
                    'limits'  => $this->subscriptionService->getUserLimits($user),
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to get usage analytics', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load usage analytics',
            ], 500);
        }
    }

    /**
     * Check feature access
     */
    public function checkFeatureAccess(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'feature' => 'required|string',
            ]);

            $user = Auth::user();
            $hasAccess = $this->subscriptionService->hasFeatureAccess($user, $validated['feature']);
            $limits = $this->subscriptionService->getUserLimits($user);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'has_access' => $hasAccess,
                    'feature'    => $validated['feature'],
                    'plan'       => $user->subscription_plan ?? 'free',
                    'limits'     => $limits,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Failed to check feature access', [
                'error'   => $e->getMessage(),
                'user_id' => Auth::id(),
                'feature' => $request->input('feature'),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to check feature access',
            ], 500);
        }
    }

    // Private helper methods

    private function groupUsageByGranularity($records, string $granularity): array
    {
        $grouped = [];

        foreach ($records as $record) {
            $key = match ($granularity) {
                'day'   => $record->recorded_at->format('Y-m-d'),
                'week'  => $record->recorded_at->format('Y-W'),
                'month' => $record->recorded_at->format('Y-m'),
                default => $record->recorded_at->format('Y-m-d'),
            };

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'period'    => $key,
                    'resources' => [],
                ];
            }

            if (!isset($grouped[$key]['resources'][$record->resource_type])) {
                $grouped[$key]['resources'][$record->resource_type] = [
                    'quantity' => 0,
                    'cost'     => 0,
                ];
            }

            $grouped[$key]['resources'][$record->resource_type]['quantity'] += $record->quantity;
            $grouped[$key]['resources'][$record->resource_type]['cost'] += $record->total_amount;
        }

        return array_values($grouped);
    }
}
