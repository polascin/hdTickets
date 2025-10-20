<?php declare(strict_types=1);

namespace App\Services;

use App\Models\PaymentPlan;
use App\Models\ScrapedTicket;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\PayPal\PayPalService;
use App\Services\PayPal\PayPalSubscriptionService;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\Customer;
use Stripe\Stripe;
use Stripe\Subscription;

class PaymentService
{
    public function __construct(
        private PayPalService $paypalService,
        private PayPalSubscriptionService $paypalSubscriptionService,
    ) {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create PayPal subscription for user
     */
    public function createPayPalSubscription(User $user, PaymentPlan $plan): UserSubscription
    {
        try {
            $subscription = $this->paypalSubscriptionService->createSubscription($user, $plan);

            Log::info('PayPal subscription created via PaymentService', [
                'user_id'         => $user->id,
                'subscription_id' => $subscription->id,
                'plan_id'         => $plan->id,
            ]);

            return $subscription;
        } catch (Exception $e) {
            Log::error('Failed to create PayPal subscription in PaymentService', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process PayPal payment for ticket purchases
     */
    public function processPayPalPayment(User $user, ScrapedTicket $ticket, int $quantity, array $metadata = []): array
    {
        try {
            $amount = $ticket->price * $quantity;
            $currency = $ticket->currency ?? 'USD';

            $order = $this->paypalService->createOrder($amount, $currency, [
                'user_id'   => $user->id,
                'ticket_id' => $ticket->id,
                'quantity'  => $quantity,
                ...$metadata,
            ]);

            Log::info('PayPal order created for ticket purchase', [
                'user_id'   => $user->id,
                'ticket_id' => $ticket->id,
                'order_id'  => $order['id'],
                'amount'    => $amount,
                'currency'  => $currency,
            ]);

            return [
                'success'        => TRUE,
                'order_id'       => $order['id'],
                'amount'         => $amount,
                'currency'       => $currency,
                'approve_url'    => $order['approve_link'],
                'payment_method' => 'paypal',
            ];
        } catch (Exception $e) {
            Log::error('Failed to create PayPal order for ticket purchase', [
                'user_id'   => $user->id,
                'ticket_id' => $ticket->id,
                'error'     => $e->getMessage(),
            ]);

            return [
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Capture PayPal payment after user approval
     */
    public function capturePayPalPayment(string $orderId): array
    {
        try {
            $capture = $this->paypalService->captureOrder($orderId);

            Log::info('PayPal payment captured successfully', [
                'order_id'   => $orderId,
                'capture_id' => $capture['capture_id'],
                'amount'     => $capture['amount'],
            ]);

            return [
                'success'    => TRUE,
                'capture_id' => $capture['capture_id'],
                'order_id'   => $orderId,
                'amount'     => $capture['amount'],
                'currency'   => $capture['currency'],
                'status'     => $capture['status'],
            ];
        } catch (Exception $e) {
            Log::error('Failed to capture PayPal payment', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Refund PayPal transaction
     */
    public function refundPayPalTransaction(string $captureId, float $amount, string $currency = 'USD'): array
    {
        try {
            $refund = $this->paypalService->refundCapture($captureId, $amount, $currency);

            Log::info('PayPal refund processed successfully', [
                'capture_id' => $captureId,
                'refund_id'  => $refund['refund_id'],
                'amount'     => $amount,
            ]);

            return [
                'success'   => TRUE,
                'refund_id' => $refund['refund_id'],
                'amount'    => $amount,
                'currency'  => $currency,
                'status'    => $refund['status'],
            ];
        } catch (Exception $e) {
            Log::error('Failed to process PayPal refund', [
                'capture_id' => $captureId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => FALSE,
                'error'   => $e->getMessage(),
            ];
        }
    }

    /**
     * Cancel PayPal subscription
     */
    public function cancelPayPalSubscription(UserSubscription $subscription, string $reason = 'User requested cancellation'): bool
    {
        try {
            return $this->paypalSubscriptionService->cancelSubscription($subscription, $reason);
        } catch (Exception $e) {
            Log::error('Failed to cancel PayPal subscription in PaymentService', [
                'subscription_id' => $subscription->id,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Create or update Stripe customer (existing method for backward compatibility)
     */
    public function createOrUpdateCustomer(User $user): Customer
    {
        try {
            // Check if user already has a Stripe customer ID
            if ($user->stripe_customer_id) {
                return Customer::retrieve($user->stripe_customer_id);
            }

            // Create new Stripe customer
            $customer = Customer::create([
                'email'    => $user->email,
                'name'     => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            // Save customer ID to user
            $user->update(['stripe_customer_id' => $customer->id]);

            Log::info('Stripe customer created', [
                'user_id'     => $user->id,
                'customer_id' => $customer->id,
            ]);

            return $customer;
        } catch (Exception $e) {
            Log::error('Failed to create Stripe customer', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create Stripe subscription (existing method for backward compatibility)
     */
    public function createStripeSubscription(User $user, PaymentPlan $plan, string $customerId): UserSubscription
    {
        try {
            $subscription = Subscription::create([
                'customer' => $customerId,
                'items'    => [[
                    'price_data' => [
                        'currency'     => 'usd',
                        'product_data' => [
                            'name' => $plan->name,
                        ],
                        'unit_amount' => (int) ($plan->price * 100),
                        'recurring'   => [
                            'interval' => $plan->billing_cycle === 'yearly' ? 'year' : 'month',
                        ],
                    ],
                ]],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                ],
            ]);

            $userSubscription = $user->subscriptions()->create([
                'payment_plan_id'        => $plan->id,
                'status'                 => $subscription->status === 'active' ? 'active' : 'pending',
                'payment_method'         => 'stripe',
                'stripe_subscription_id' => $subscription->id,
                'starts_at'              => now(),
                'ends_at'                => $plan->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'amount_paid'            => $plan->price,
            ]);

            Log::info('Stripe subscription created', [
                'user_id'                => $user->id,
                'subscription_id'        => $userSubscription->id,
                'stripe_subscription_id' => $subscription->id,
            ]);

            return $userSubscription;
        } catch (Exception $e) {
            Log::error('Failed to create Stripe subscription', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel Stripe subscription (existing method for backward compatibility)
     */
    public function cancelStripeSubscription(string $subscriptionId): bool
    {
        try {
            $subscription = Subscription::retrieve($subscriptionId);
            $subscription->cancel();

            Log::info('Stripe subscription cancelled', [
                'subscription_id' => $subscriptionId,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to cancel Stripe subscription', [
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Calculate user ticket limits based on subscription
     */
    public function calculateTicketLimits(User $user): array
    {
        $subscription = $user->activeSubscription();

        if (! $subscription) {
            return [
                'monthly_limit' => 5, // Free tier limit
                'unlimited'     => FALSE,
                'plan_name'     => 'Free',
            ];
        }

        $plan = $subscription->paymentPlan;

        return [
            'monthly_limit' => $plan->ticket_limit ?? -1, // -1 means unlimited
            'unlimited'     => ($plan->ticket_limit ?? 0) === -1,
            'plan_name'     => $plan->name,
        ];
    }

    /**
     * Get remaining ticket allowance for current month
     */
    public function getRemainingTicketAllowance(User $user): int
    {
        $limits = $this->calculateTicketLimits($user);

        if ($limits['unlimited']) {
            return -1; // Unlimited
        }

        // Count tickets purchased this month
        $currentMonth = now()->startOfMonth();
        $ticketsUsed = $user->purchaseAttempts()
            ->where('created_at', '>=', $currentMonth)
            ->where('status', 'successful')
            ->sum('quantity');

        return max(0, $limits['monthly_limit'] - $ticketsUsed);
    }

    /**
     * Check if user can purchase tickets
     */
    public function canPurchaseTickets(User $user): bool
    {
        // Admins and agents can always purchase
        if ($user->isAdmin() || $user->isAgent()) {
            return TRUE;
        }

        // Scrapers cannot purchase tickets
        if ($user->isScraper()) {
            return FALSE;
        }

        // Customers need active subscription or trial
        $subscription = $user->activeSubscription();
        if ($subscription && $subscription->status === 'active') {
            return TRUE;
        }

        // Check if user is on trial
        return $user->isOnTrial();
    }

    /**
     * Legacy method for backward compatibility
     */
    public function processPayment(array $paymentData): array
    {
        Log::warning('Using deprecated processPayment method', [
            'payment_data' => $paymentData,
        ]);

        return ['status' => 'success', 'transaction_id' => uniqid()];
    }

    /**
     * Legacy refund method for backward compatibility
     */
    public function refund(string $transactionId): bool
    {
        Log::warning('Using deprecated refund method', [
            'transaction_id' => $transactionId,
        ]);

        return TRUE;
    }
}
