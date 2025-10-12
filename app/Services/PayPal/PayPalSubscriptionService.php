<?php declare(strict_types=1);

namespace App\Services\PayPal;

use App\Models\PaymentPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Exception;
use Illuminate\Support\Facades\Log;

class PayPalSubscriptionService
{
    public function __construct(
        private PayPalService $paypalService
    ) {
    }

    /**
     * Create or retrieve PayPal product for subscription plans
     */
    public function ensureProductExists(string $productName = 'HD Tickets Subscription Plans'): string
    {
        // In a production system, you'd want to cache this product ID
        // For now, we'll create a new product each time or retrieve from config
        $cachedProductId = cache()->remember('paypal_product_id', 3600, function () use ($productName) {
            return $this->paypalService->createProduct(
                $productName,
                'Access to HD Tickets sports event monitoring platform',
                'SOFTWARE'
            );
        });

        return $cachedProductId;
    }

    /**
     * Create or retrieve PayPal plan for a payment plan
     */
    public function ensurePlanExists(PaymentPlan $paymentPlan): string
    {
        // Check if we already have a PayPal plan ID for this payment plan
        $cacheKey = "paypal_plan_id_{$paymentPlan->id}";

        return cache()->remember($cacheKey, 3600, function () use ($paymentPlan) {
            $productId = $this->ensureProductExists();

            return $this->paypalService->createSubscriptionPlan($paymentPlan, $productId);
        });
    }

    /**
     * Create a PayPal subscription and local subscription record
     */
    public function createSubscription(User $user, PaymentPlan $paymentPlan): UserSubscription
    {
        try {
            // Ensure PayPal plan exists
            $paypalPlanId = $this->ensurePlanExists($paymentPlan);

            // Create PayPal subscription
            $paypalSubscription = $this->paypalService->createSubscription($paypalPlanId, $user);

            // Create local subscription record
            $subscription = $user->subscriptions()->create([
                'payment_plan_id'        => $paymentPlan->id,
                'status'                 => 'pending_approval',
                'payment_method'         => 'paypal',
                'paypal_subscription_id' => $paypalSubscription['id'],
                'paypal_plan_id'         => $paypalPlanId,
                'starts_at'              => now(),
                'amount_paid'            => 0, // Will be updated after approval
                'metadata'               => [
                    'paypal_status'       => $paypalSubscription['status'],
                    'paypal_approve_link' => $paypalSubscription['approve_link'],
                ],
            ]);

            Log::info('PayPal subscription created successfully', [
                'user_id'                => $user->id,
                'subscription_id'        => $subscription->id,
                'paypal_subscription_id' => $paypalSubscription['id'],
                'payment_plan_id'        => $paymentPlan->id,
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to create PayPal subscription', [
                'user_id'         => $user->id,
                'payment_plan_id' => $paymentPlan->id,
                'error'           => $e->getMessage(),
            ]);

            throw new Exception('Failed to create subscription: ' . $e->getMessage());
        }
    }

    /**
     * Activate subscription after PayPal approval
     */
    public function activateSubscription(string $paypalSubscriptionId): ?UserSubscription
    {
        try {
            // Get subscription details from PayPal
            $paypalDetails = $this->paypalService->getSubscriptionDetails($paypalSubscriptionId);

            // Find local subscription
            $subscription = UserSubscription::where('paypal_subscription_id', $paypalSubscriptionId)->first();

            if (!$subscription) {
                Log::error('Local subscription not found for PayPal subscription', [
                    'paypal_subscription_id' => $paypalSubscriptionId,
                ]);

                return NULL;
            }

            // Update subscription status based on PayPal status
            $status = $this->mapPayPalStatusToLocal($paypalDetails['status']);

            $subscription->update([
                'status'          => $status,
                'paypal_payer_id' => $paypalDetails['payer_id'] ?? NULL,
                'starts_at'       => $paypalDetails['start_time'] ? new \DateTime($paypalDetails['start_time']) : now(),
                'next_billing_at' => $paypalDetails['next_billing_time'] ? new \DateTime($paypalDetails['next_billing_time']) : NULL,
                'amount_paid'     => $subscription->paymentPlan->price,
                'metadata'        => array_merge($subscription->metadata ?? [], [
                    'paypal_status' => $paypalDetails['status'],
                    'activated_at'  => now()->toISOString(),
                ]),
            ]);

            // Update user's current subscription
            if ($status === 'active') {
                $subscription->user->update(['current_subscription_id' => $subscription->id]);

                // Calculate end date based on billing cycle
                $endsAt = $this->calculateEndDate($subscription->paymentPlan->billing_cycle);
                $subscription->update(['ends_at' => $endsAt]);
            }

            Log::info('PayPal subscription activated', [
                'subscription_id'        => $subscription->id,
                'paypal_subscription_id' => $paypalSubscriptionId,
                'status'                 => $status,
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to activate PayPal subscription', [
                'paypal_subscription_id' => $paypalSubscriptionId,
                'error'                  => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Cancel PayPal subscription
     */
    public function cancelSubscription(UserSubscription $subscription, string $reason = 'User requested cancellation'): bool
    {
        try {
            if (!$subscription->paypal_subscription_id) {
                throw new Exception('Not a PayPal subscription');
            }

            // Cancel with PayPal
            $cancelled = $this->paypalService->cancelSubscription(
                $subscription->paypal_subscription_id,
                $reason
            );

            if ($cancelled) {
                // Update local subscription
                $subscription->update([
                    'status'   => 'cancelled',
                    'ends_at'  => now()->endOfMonth(), // Allow access until end of billing period
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'cancelled_at'        => now()->toISOString(),
                        'cancellation_reason' => $reason,
                    ]),
                ]);

                Log::info('PayPal subscription cancelled', [
                    'subscription_id'        => $subscription->id,
                    'paypal_subscription_id' => $subscription->paypal_subscription_id,
                    'reason'                 => $reason,
                ]);
            }

            return $cancelled;
        } catch (Exception $e) {
            Log::error('Failed to cancel PayPal subscription', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Synchronise subscription status with PayPal
     */
    public function synchroniseSubscription(UserSubscription $subscription): bool
    {
        try {
            if (!$subscription->paypal_subscription_id) {
                return FALSE;
            }

            $paypalDetails = $this->paypalService->getSubscriptionDetails($subscription->paypal_subscription_id);
            $localStatus = $this->mapPayPalStatusToLocal($paypalDetails['status']);

            if ($subscription->status !== $localStatus) {
                $subscription->update([
                    'status'   => $localStatus,
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'last_sync_at'  => now()->toISOString(),
                        'paypal_status' => $paypalDetails['status'],
                    ]),
                ]);

                Log::info('Subscription status synchronised', [
                    'subscription_id' => $subscription->id,
                    'old_status'      => $subscription->status,
                    'new_status'      => $localStatus,
                    'paypal_status'   => $paypalDetails['status'],
                ]);
            }

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to synchronise subscription', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Process subscription renewal webhook
     */
    public function processRenewal(string $paypalSubscriptionId, array $paymentDetails): ?UserSubscription
    {
        try {
            $subscription = UserSubscription::where('paypal_subscription_id', $paypalSubscriptionId)->first();

            if (!$subscription) {
                Log::warning('Renewal webhook for unknown subscription', [
                    'paypal_subscription_id' => $paypalSubscriptionId,
                ]);

                return NULL;
            }

            // Extend subscription period
            $currentEndsAt = $subscription->ends_at ?: now();
            $newEndsAt = $this->calculateEndDate($subscription->paymentPlan->billing_cycle, $currentEndsAt);

            $subscription->update([
                'ends_at'     => $newEndsAt,
                'amount_paid' => $subscription->amount_paid + ($paymentDetails['amount'] ?? $subscription->paymentPlan->price),
                'metadata'    => array_merge($subscription->metadata ?? [], [
                    'last_renewal_at'     => now()->toISOString(),
                    'last_payment_amount' => $paymentDetails['amount'] ?? $subscription->paymentPlan->price,
                    'total_renewals'      => ($subscription->metadata['total_renewals'] ?? 0) + 1,
                ]),
            ]);

            Log::info('Subscription renewed', [
                'subscription_id'        => $subscription->id,
                'paypal_subscription_id' => $paypalSubscriptionId,
                'new_ends_at'            => $newEndsAt,
                'payment_amount'         => $paymentDetails['amount'] ?? $subscription->paymentPlan->price,
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to process subscription renewal', [
                'paypal_subscription_id' => $paypalSubscriptionId,
                'error'                  => $e->getMessage(),
            ]);

            return NULL;
        }
    }

    /**
     * Map PayPal subscription status to local status
     */
    private function mapPayPalStatusToLocal(string $paypalStatus): string
    {
        return match (strtoupper($paypalStatus)) {
            'APPROVAL_PENDING' => 'pending_approval',
            'APPROVED'         => 'pending_activation',
            'ACTIVE'           => 'active',
            'SUSPENDED'        => 'suspended',
            'CANCELLED'        => 'cancelled',
            'EXPIRED'          => 'expired',
            default            => 'pending',
        };
    }

    /**
     * Calculate subscription end date based on billing cycle
     */
    private function calculateEndDate(string $billingCycle, ?\DateTime $fromDate = NULL): \DateTime
    {
        $baseDate = $fromDate ?: now();

        return match (strtolower($billingCycle)) {
            'monthly' => (clone $baseDate)->addMonth(),
            'yearly', 'annual' => (clone $baseDate)->addYear(),
            'weekly'   => (clone $baseDate)->addWeek(),
            'daily'    => (clone $baseDate)->addDay(),
            'lifetime' => (clone $baseDate)->addYears(100), // Far future for lifetime
            default    => (clone $baseDate)->addMonth(),
        };
    }
}
