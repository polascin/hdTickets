<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\SubscriptionCancelled;
use App\Events\SubscriptionCreated;
use App\Events\SubscriptionUpdated;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

/**
 * Subscription Management Service
 *
 * Comprehensive subscription system with:
 * - Tiered plan management (Starter, Pro, Enterprise)
 * - Stripe payment processing integration
 * - Feature access control and billing cycles
 * - Subscription lifecycle management
 * - Usage tracking and billing analytics
 */
class SubscriptionService
{
    private const PLANS = [
        'starter' => [
            'name'     => 'Starter',
            'price'    => 19.00,
            'interval' => 'month',
            'features' => [
                'events_limit'          => 5,
                'monitors_limit'        => 10,
                'api_requests_per_hour' => 100,
                'price_alerts_limit'    => 20,
                'webhook_endpoints'     => 1,
                'auto_purchase_configs' => 1,
                'data_retention_days'   => 30,
                'support_level'         => 'email',
                'features'              => [
                    'real_time_monitoring',
                    'basic_price_alerts',
                    'email_notifications',
                    'basic_analytics',
                ],
            ],
        ],
        'pro' => [
            'name'     => 'Pro',
            'price'    => 49.00,
            'interval' => 'month',
            'features' => [
                'events_limit'          => 25,
                'monitors_limit'        => 50,
                'api_requests_per_hour' => 1000,
                'price_alerts_limit'    => 100,
                'webhook_endpoints'     => 5,
                'auto_purchase_configs' => 5,
                'data_retention_days'   => 90,
                'support_level'         => 'priority_email',
                'features'              => [
                    'real_time_monitoring',
                    'advanced_price_alerts',
                    'smart_notifications',
                    'comprehensive_analytics',
                    'automated_purchasing',
                    'multi_event_management',
                    'api_access',
                    'bulk_operations',
                    'price_predictions',
                ],
            ],
        ],
        'enterprise' => [
            'name'     => 'Enterprise',
            'price'    => 199.00,
            'interval' => 'month',
            'features' => [
                'events_limit'          => 100,
                'monitors_limit'        => 250,
                'api_requests_per_hour' => 10000,
                'price_alerts_limit'    => 500,
                'webhook_endpoints'     => 25,
                'auto_purchase_configs' => 25,
                'data_retention_days'   => 365,
                'support_level'         => 'phone_and_email',
                'features'              => [
                    'real_time_monitoring',
                    'advanced_price_alerts',
                    'intelligent_notifications',
                    'comprehensive_analytics',
                    'automated_purchasing',
                    'multi_event_management',
                    'full_api_access',
                    'bulk_operations',
                    'price_predictions',
                    'custom_integrations',
                    'priority_support',
                    'dedicated_account_manager',
                    'custom_reporting',
                    'white_label_options',
                ],
            ],
        ],
    ];

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Get all available subscription plans
     */
    public function getAvailablePlans(): array
    {
        return self::PLANS;
    }

    /**
     * Get specific plan details
     */
    public function getPlan(string $planName): ?array
    {
        return self::PLANS[$planName] ?? NULL;
    }

    /**
     * Create new subscription for user
     */
    public function createSubscription(
        User $user,
        string $planName,
        string $paymentMethodId,
        array $options = []
    ): array {
        $plan = $this->getPlan($planName);
        if (!$plan) {
            throw new \InvalidArgumentException("Invalid plan: {$planName}");
        }

        DB::beginTransaction();

        try {
            // Create or get Stripe customer
            $stripeCustomer = $this->getOrCreateStripeCustomer($user);

            // Attach payment method to customer
            $this->attachPaymentMethod($stripeCustomer->id, $paymentMethodId);

            // Create Stripe subscription
            $stripeSubscription = $this->createStripeSubscription(
                $stripeCustomer->id,
                $planName,
                $paymentMethodId,
                $options
            );

            // Create local subscription record
            $subscription = $this->createLocalSubscription(
                $user,
                $planName,
                $stripeSubscription,
                $options
            );

            // Update user subscription status
            $user->update([
                'subscription_plan'   => $planName,
                'subscription_status' => 'active',
                'stripe_customer_id'  => $stripeCustomer->id,
            ]);

            DB::commit();

            // Fire subscription created event
            event(new SubscriptionCreated($subscription));

            Log::info('Subscription created successfully', [
                'user_id'                => $user->id,
                'plan'                   => $planName,
                'subscription_id'        => $subscription->id,
                'stripe_subscription_id' => $stripeSubscription->id,
            ]);

            return [
                'success'             => TRUE,
                'subscription'        => $subscription,
                'stripe_subscription' => $stripeSubscription,
                'message'             => "Successfully subscribed to {$plan['name']} plan",
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create subscription', [
                'user_id' => $user->id,
                'plan'    => $planName,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update existing subscription
     */
    public function updateSubscription(
        User $user,
        string $newPlanName,
        array $options = []
    ): array {
        $currentSubscription = $user->activeSubscription();
        if (!$currentSubscription) {
            throw new \Exception('No active subscription found');
        }

        $newPlan = $this->getPlan($newPlanName);
        if (!$newPlan) {
            throw new \InvalidArgumentException("Invalid plan: {$newPlanName}");
        }

        DB::beginTransaction();

        try {
            // Update Stripe subscription
            $stripeSubscription = StripeSubscription::update(
                $currentSubscription->stripe_subscription_id,
                [
                    'items' => [
                        [
                            'id'    => $currentSubscription->stripe_subscription_item_id,
                            'price' => $this->getStripePriceId($newPlanName),
                        ],
                    ],
                    'proration_behavior' => $options['prorate'] ?? 'create_prorations',
                ]
            );

            // Update local subscription
            $currentSubscription->update([
                'plan_name'  => $newPlanName,
                'price'      => $newPlan['price'],
                'updated_at' => now(),
            ]);

            // Update user plan
            $user->update(['subscription_plan' => $newPlanName]);

            DB::commit();

            // Fire subscription updated event
            event(new SubscriptionUpdated($currentSubscription, $newPlanName));

            Log::info('Subscription updated successfully', [
                'user_id'         => $user->id,
                'old_plan'        => $currentSubscription->plan_name,
                'new_plan'        => $newPlanName,
                'subscription_id' => $currentSubscription->id,
            ]);

            return [
                'success'      => TRUE,
                'subscription' => $currentSubscription->fresh(),
                'message'      => "Successfully upgraded to {$newPlan['name']} plan",
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update subscription', [
                'user_id'  => $user->id,
                'new_plan' => $newPlanName,
                'error'    => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(
        User $user,
        bool $immediately = FALSE,
        string $reason = NULL
    ): array {
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            throw new \Exception('No active subscription found');
        }

        DB::beginTransaction();

        try {
            if ($immediately) {
                // Cancel immediately
                $stripeSubscription = StripeSubscription::update(
                    $subscription->stripe_subscription_id,
                    ['cancel_at_period_end' => FALSE]
                );

                StripeSubscription::retrieve($subscription->stripe_subscription_id)->cancel();

                $subscription->update([
                    'status'              => 'cancelled',
                    'cancelled_at'        => now(),
                    'cancellation_reason' => $reason,
                ]);

                $user->update([
                    'subscription_plan'   => 'free',
                    'subscription_status' => 'cancelled',
                ]);

                $message = 'Subscription cancelled immediately';
            } else {
                // Cancel at period end
                StripeSubscription::update(
                    $subscription->stripe_subscription_id,
                    ['cancel_at_period_end' => TRUE]
                );

                $subscription->update([
                    'status'              => 'cancel_at_period_end',
                    'cancel_at'           => $subscription->current_period_end,
                    'cancellation_reason' => $reason,
                ]);

                $message = 'Subscription will cancel at the end of the current billing period';
            }

            DB::commit();

            // Fire subscription cancelled event
            event(new SubscriptionCancelled($subscription, $immediately));

            Log::info('Subscription cancelled', [
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
                'immediately'     => $immediately,
                'reason'          => $reason,
            ]);

            return [
                'success'      => TRUE,
                'subscription' => $subscription->fresh(),
                'message'      => $message,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to cancel subscription', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resumeSubscription(User $user): array
    {
        $subscription = $user->activeSubscription();
        if (!$subscription || $subscription->status !== 'cancel_at_period_end') {
            throw new \Exception('No subscription pending cancellation found');
        }

        try {
            // Resume in Stripe
            StripeSubscription::update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => FALSE]
            );

            // Update local subscription
            $subscription->update([
                'status'              => 'active',
                'cancel_at'           => NULL,
                'cancellation_reason' => NULL,
            ]);

            Log::info('Subscription resumed', [
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
            ]);

            return [
                'success'      => TRUE,
                'subscription' => $subscription->fresh(),
                'message'      => 'Subscription resumed successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to resume subscription', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if user has access to specific feature
     */
    public function hasFeatureAccess(User $user, string $feature): bool
    {
        $plan = $this->getPlan($user->subscription_plan ?? 'free');

        if (!$plan) {
            return FALSE;
        }

        return in_array($feature, $plan['features']['features'] ?? []);
    }

    /**
     * Get user's plan limits
     */
    public function getUserLimits(User $user): array
    {
        $plan = $this->getPlan($user->subscription_plan ?? 'free');

        if (!$plan) {
            return $this->getFreePlanLimits();
        }

        return $plan['features'];
    }

    /**
     * Check if user has reached limit for specific resource
     */
    public function hasReachedLimit(User $user, string $resource): bool
    {
        $limits = $this->getUserLimits($user);
        $limitKey = $resource . '_limit';

        if (!isset($limits[$limitKey])) {
            return FALSE;
        }

        $currentUsage = $this->getCurrentUsage($user, $resource);

        return $currentUsage >= $limits[$limitKey];
    }

    /**
     * Get subscription billing summary
     */
    public function getBillingSummary(User $user): array
    {
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return [
                'plan'              => 'Free',
                'status'            => 'free',
                'next_billing_date' => NULL,
                'amount'            => 0,
                'features'          => $this->getFreePlanLimits(),
            ];
        }

        $plan = $this->getPlan($subscription->plan_name);

        return [
            'plan'              => $plan['name'],
            'status'            => $subscription->status,
            'next_billing_date' => $subscription->current_period_end,
            'amount'            => $subscription->price,
            'currency'          => $subscription->currency,
            'features'          => $plan['features'],
            'usage'             => $this->getUsageSummary($user),
            'payment_method'    => $this->getCurrentPaymentMethod($user),
        ];
    }

    /**
     * Process failed payment
     */
    public function handleFailedPayment(string $stripeSubscriptionId, array $data): void
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if (!$subscription) {
            Log::warning('Failed payment for unknown subscription', ['stripe_id' => $stripeSubscriptionId]);

            return;
        }

        $subscription->update([
            'status'                 => 'past_due',
            'last_payment_failed_at' => now(),
        ]);

        // Notify user about failed payment
        $this->notifyFailedPayment($subscription->user, $data);

        Log::info('Processed failed payment', [
            'subscription_id' => $subscription->id,
            'user_id'         => $subscription->user_id,
        ]);
    }

    // Private helper methods

    private function getOrCreateStripeCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            try {
                return Customer::retrieve($user->stripe_customer_id);
            } catch (\Exception $e) {
                // Customer not found, create new one
            }
        }

        $customer = Customer::create([
            'email'    => $user->email,
            'name'     => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    private function attachPaymentMethod(string $customerId, string $paymentMethodId): void
    {
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);
    }

    private function createStripeSubscription(
        string $customerId,
        string $planName,
        string $paymentMethodId,
        array $options
    ): StripeSubscription {
        $priceId = $this->getStripePriceId($planName);

        return StripeSubscription::create([
            'customer'               => $customerId,
            'items'                  => [['price' => $priceId]],
            'default_payment_method' => $paymentMethodId,
            'expand'                 => ['latest_invoice.payment_intent'],
            'trial_period_days'      => $options['trial_days'] ?? NULL,
            'metadata'               => [
                'plan_name' => $planName,
            ],
        ]);
    }

    private function createLocalSubscription(
        User $user,
        string $planName,
        StripeSubscription $stripeSubscription,
        array $options
    ): Subscription {
        $plan = $this->getPlan($planName);

        return Subscription::create([
            'user_id'                     => $user->id,
            'plan_name'                   => $planName,
            'stripe_subscription_id'      => $stripeSubscription->id,
            'stripe_subscription_item_id' => $stripeSubscription->items->data[0]->id,
            'status'                      => $stripeSubscription->status,
            'price'                       => $plan['price'],
            'currency'                    => 'usd',
            'current_period_start'        => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
            'current_period_end'          => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            'trial_ends_at'               => $stripeSubscription->trial_end ?
                Carbon::createFromTimestamp($stripeSubscription->trial_end) : NULL,
        ]);
    }

    private function getStripePriceId(string $planName): string
    {
        // In production, these would be actual Stripe price IDs
        return match ($planName) {
            'starter'    => config('stripe.prices.starter'),
            'pro'        => config('stripe.prices.pro'),
            'enterprise' => config('stripe.prices.enterprise'),
            default      => throw new \InvalidArgumentException("No Stripe price ID for plan: {$planName}")
        };
    }

    private function getFreePlanLimits(): array
    {
        return [
            'events_limit'          => 1,
            'monitors_limit'        => 2,
            'api_requests_per_hour' => 20,
            'price_alerts_limit'    => 5,
            'webhook_endpoints'     => 0,
            'auto_purchase_configs' => 0,
            'data_retention_days'   => 7,
            'support_level'         => 'community',
            'features'              => ['basic_monitoring'],
        ];
    }

    private function getCurrentUsage(User $user, string $resource): int
    {
        return match ($resource) {
            'events'                => $user->events()->count(),
            'monitors'              => $user->eventMonitors()->count(),
            'price_alerts'          => $user->priceAlerts()->count(),
            'webhook_endpoints'     => $user->webhooks()->count(),
            'auto_purchase_configs' => $user->autoPurchaseConfigs()->count(),
            default                 => 0
        };
    }

    private function getUsageSummary(User $user): array
    {
        $limits = $this->getUserLimits($user);

        return [
            'events' => [
                'used'  => $this->getCurrentUsage($user, 'events'),
                'limit' => $limits['events_limit'] ?? 0,
            ],
            'monitors' => [
                'used'  => $this->getCurrentUsage($user, 'monitors'),
                'limit' => $limits['monitors_limit'] ?? 0,
            ],
            'price_alerts' => [
                'used'  => $this->getCurrentUsage($user, 'price_alerts'),
                'limit' => $limits['price_alerts_limit'] ?? 0,
            ],
            'api_requests_this_hour' => [
                'used'  => $this->getApiUsageThisHour($user),
                'limit' => $limits['api_requests_per_hour'] ?? 0,
            ],
        ];
    }

    private function getCurrentPaymentMethod(User $user): ?array
    {
        // Implementation would fetch current payment method from Stripe
        return NULL;
    }

    private function notifyFailedPayment(User $user, array $data): void
    {
        // Implementation would send notification about failed payment
    }

    private function getApiUsageThisHour(User $user): int
    {
        // Implementation would calculate API usage for current hour
        return 0;
    }
}
