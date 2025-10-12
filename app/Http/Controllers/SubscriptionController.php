<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\PaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class SubscriptionController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Show subscription payment page
     */
    public function showPayment(): View
    {
        $user = Auth::user();

        // Get available payment plans
        $plans = PaymentPlan::where('is_active', TRUE)
            ->orderBy('price')
            ->get();

        // Get current subscription if exists
        $currentSubscription = $user->activeSubscription();

        // Calculate remaining free access days
        $freeAccessDays = (int) config('subscription.free_access_days', 7);
        $accountAge = $user->created_at->diffInDays(now());
        $remainingFreeDays = max(0, $freeAccessDays - $accountAge);

        return view('subscription.payment', ['user' => $user, 'plans' => $plans, 'currentSubscription' => $currentSubscription, 'remainingFreeDays' => $remainingFreeDays]);
    }

    /**
     * Process subscription payment
     */
    public function processPayment(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id'        => ['required', 'exists:payment_plans,id'],
            'payment_method' => ['required', 'in:stripe,paypal'],
        ]);

        $user = Auth::user();
        $plan = PaymentPlan::findOrFail($request->plan_id);

        try {
            DB::beginTransaction();

            // Create subscription based on payment method
            $subscription = match ($request->payment_method) {
                'stripe' => $this->handleStripeSubscription($user, $plan),
                'paypal' => $this->handlePayPalSubscription($user, $plan),
                default  => throw new Exception('Invalid payment method'),
            };

            DB::commit();

            // Handle different redirect flows based on payment method
            return $this->handleSubscriptionRedirect($subscription, $request->payment_method);
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Subscription creation failed', [
                'user_id'        => $user->id,
                'plan_id'        => $plan->id,
                'payment_method' => $request->payment_method,
                'error'          => $e->getMessage(),
            ]);

            return back()
                ->withErrors(['error' => 'Payment failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show subscription success page
     */
    public function success(): View
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->route('subscription.payment')
                ->withErrors(['error' => 'No active subscription found.']);
        }

        return view('subscription.success', ['user' => $user, 'subscription' => $subscription]);
    }

    /**
     * Show subscription management page
     */
    public function manage(): View
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();
        $subscriptionHistory = $user->subscriptions()
            ->with('paymentPlan')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get usage statistics
        $currentMonth = now()->startOfMonth();
        $ticketUsage = $this->getTicketUsage($user, $currentMonth);

        return view('subscription.manage', ['user' => $user, 'subscription' => $subscription, 'subscriptionHistory' => $subscriptionHistory, 'ticketUsage' => $ticketUsage]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return redirect()->route('subscription.manage')
                ->withErrors(['error' => 'No active subscription found.']);
        }

        try {
            // Cancel subscription with appropriate payment provider
            $cancelled = FALSE;

            if ($subscription->stripe_subscription_id) {
                $cancelled = $this->paymentService->cancelStripeSubscription($subscription->stripe_subscription_id);
            } elseif ($subscription->paypal_subscription_id) {
                $cancelled = $this->paymentService->cancelPayPalSubscription($subscription);
            }

            if (!$cancelled && ($subscription->stripe_subscription_id || $subscription->paypal_subscription_id)) {
                throw new Exception('Failed to cancel subscription with payment provider.');
            }

            // Update local subscription status
            $subscription->update([
                'status'  => 'cancelled',
                'ends_at' => now()->endOfMonth(), // Allow access until end of current billing period
            ]);

            Log::info('Subscription cancelled successfully', [
                'subscription_id' => $subscription->id,
                'user_id'         => $subscription->user_id,
                'payment_method'  => $subscription->payment_method,
            ]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Subscription cancelled. You will have access until the end of your billing period.');
        } catch (Exception $e) {
            Log::error('Subscription cancellation failed', [
                'subscription_id' => $subscription->id,
                'user_id'         => $subscription->user_id,
                'error'           => $e->getMessage(),
            ]);

            return redirect()->route('subscription.manage')
                ->withErrors(['error' => 'Failed to cancel subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle Stripe webhooks
     */
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpoint_secret);
        } catch (UnexpectedValueException) {
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException) {
            return response('Invalid signature', 400);
        }

        // Handle the event
        match ($event->type) {
            'customer.subscription.created', 'customer.subscription.updated' => $this->handleSubscriptionUpdated($event->data->object),
            'customer.subscription.deleted' => $this->handleSubscriptionCancelled($event->data->object),
            'invoice.payment_succeeded'     => $this->handlePaymentSucceeded($event->data->object),
            'invoice.payment_failed'        => $this->handlePaymentFailed($event->data->object),
            default                         => Log::info('Unhandled Stripe webhook event: ' . $event->type),
        };

        return response('Webhook handled', 200);
    }

    /**
     * Handle subscription updated webhook
     *
     * @param mixed $stripeSubscription
     */
    private function handleSubscriptionUpdated($stripeSubscription): void
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status'    => $stripeSubscription->status,
                'starts_at' => Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'ends_at'   => Carbon::createFromTimestamp($stripeSubscription->current_period_end),
            ]);
        }
    }

    /**
     * Handle subscription cancelled webhook
     *
     * @param mixed $stripeSubscription
     */
    private function handleSubscriptionCancelled($stripeSubscription): void
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status'  => 'cancelled',
                'ends_at' => now(),
            ]);
        }
    }

    /**
     * Handle payment succeeded webhook
     *
     * @param mixed $invoice
     */
    private function handlePaymentSucceeded($invoice): void
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            $subscription->update([
                'status'      => 'active',
                'amount_paid' => $invoice->amount_paid / 100, // Convert from cents
            ]);
        }
    }

    /**
     * Handle payment failed webhook
     *
     * @param mixed $invoice
     */
    private function handlePaymentFailed($invoice): void
    {
        $subscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription) {
            // Implement logic for handling failed payments
            // e.g., send notification email, update status, etc.
            Log::warning('Payment failed for subscription', [
                'subscription_id'        => $subscription->id,
                'stripe_subscription_id' => $invoice->subscription,
                'amount'                 => $invoice->amount_due / 100,
            ]);
        }
    }

    /**
     * Handle Stripe subscription creation
     */
    private function handleStripeSubscription(User $user, PaymentPlan $plan): UserSubscription
    {
        $stripeCustomer = $this->paymentService->createOrUpdateCustomer($user);

        return $this->paymentService->createStripeSubscription($user, $plan, $stripeCustomer->id);
    }

    /**
     * Handle PayPal subscription creation
     */
    private function handlePayPalSubscription(User $user, PaymentPlan $plan): UserSubscription
    {
        return $this->paymentService->createPayPalSubscription($user, $plan);
    }

    /**
     * Handle subscription redirect based on payment method
     */
    private function handleSubscriptionRedirect(UserSubscription $subscription, string $paymentMethod): RedirectResponse
    {
        if ($paymentMethod === 'paypal') {
            // For PayPal, we need to redirect to PayPal for approval
            $approveUrl = $subscription->metadata['paypal_approve_link'] ?? NULL;

            if ($approveUrl) {
                Log::info('Redirecting to PayPal for subscription approval', [
                    'subscription_id' => $subscription->id,
                    'approve_url'     => $approveUrl,
                ]);

                return redirect($approveUrl);
            }
        }

        // For Stripe or if no approval URL is needed
        return redirect()->route('subscription.success')
            ->with('success', 'Subscription created successfully!');
    }

    /**
     * Handle PayPal subscription approval return
     */
    public function paypalReturn(Request $request): RedirectResponse
    {
        $subscriptionId = $request->get('subscription_id');
        $token = $request->get('token');

        if (!$subscriptionId || !$token) {
            return redirect()->route('subscription.payment')
                ->withErrors(['error' => 'Invalid PayPal response.']);
        }

        try {
            $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();

            if (!$subscription) {
                throw new Exception('Subscription not found.');
            }

            // Activate the subscription
            $activatedSubscription = app(\App\Services\PayPal\PayPalSubscriptionService::class)
                ->activateSubscription($subscriptionId);

            if ($activatedSubscription) {
                Log::info('PayPal subscription approved and activated', [
                    'subscription_id'        => $subscription->id,
                    'paypal_subscription_id' => $subscriptionId,
                ]);

                return redirect()->route('subscription.success')
                    ->with('success', 'Subscription activated successfully!');
            }

            throw new Exception('Failed to activate subscription.');
        } catch (Exception $e) {
            Log::error('PayPal subscription approval failed', [
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);

            return redirect()->route('subscription.payment')
                ->withErrors(['error' => 'Failed to activate subscription: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle PayPal subscription approval cancellation
     */
    public function paypalCancel(Request $request): RedirectResponse
    {
        $subscriptionId = $request->get('subscription_id');

        if ($subscriptionId) {
            // Clean up the cancelled subscription
            $subscription = UserSubscription::where('paypal_subscription_id', $subscriptionId)->first();
            if ($subscription) {
                $subscription->update([
                    'status'   => 'cancelled',
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'cancelled_at_approval' => now()->toISOString(),
                    ]),
                ]);
            }
        }

        Log::info('PayPal subscription approval cancelled', [
            'subscription_id' => $subscriptionId,
        ]);

        return redirect()->route('subscription.payment')
            ->with('message', 'Subscription setup was cancelled.');
    }

    /**
     * API: Handle PayPal subscription approval
     */
    public function paypalApprove(Request $request)
    {
        $request->validate([
            'subscription_id' => ['required', 'string'],
            'plan_type'       => ['required', 'in:monthly,annual'],
            'billing_info'    => ['required', 'array'],
        ]);

        $user = Auth::user();
        $subscriptionId = $request->input('subscription_id');

        try {
            // Find the corresponding plan
            $planType = $request->input('plan_type');
            $plan = PaymentPlan::where('interval', $planType)
                ->where('is_active', TRUE)
                ->first();

            if (!$plan) {
                throw new Exception('Invalid plan type.');
            }

            // Create or update subscription record
            $subscription = UserSubscription::updateOrCreate(
                [
                    'user_id'                => $user->id,
                    'paypal_subscription_id' => $subscriptionId,
                ],
                [
                    'payment_plan_id' => $plan->id,
                    'status'          => 'pending',
                    'payment_method'  => 'paypal',
                    'amount_paid'     => $plan->price,
                    'starts_at'       => now(),
                    'ends_at'         => now()->addDays($plan->interval_days ?? 30),
                    'metadata'        => [
                        'billing_info' => $request->input('billing_info'),
                        'approved_at'  => now()->toISOString(),
                    ],
                ]
            );

            // Activate the subscription through PayPal service
            $paypalService = app(\App\Services\PayPal\PayPalSubscriptionService::class);
            $activatedSubscription = $paypalService->activateSubscription($subscriptionId);

            if (!$activatedSubscription) {
                throw new Exception('Failed to activate PayPal subscription.');
            }

            Log::info('PayPal subscription approved via API', [
                'user_id'                => $user->id,
                'subscription_id'        => $subscription->id,
                'paypal_subscription_id' => $subscriptionId,
            ]);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Subscription approved successfully.',
                'subscription' => [
                    'id'        => $subscription->id,
                    'status'    => $subscription->status,
                    'plan_name' => $plan->name,
                    'amount'    => $plan->price,
                    'interval'  => $plan->interval,
                ],
                'redirect_url' => route('subscriptions.success'),
            ]);
        } catch (Exception $e) {
            Log::error('PayPal subscription approval failed via API', [
                'user_id'         => $user->id,
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Activate PayPal subscription after approval
     */
    public function paypalActivate(Request $request)
    {
        $request->validate([
            'subscription_id' => ['required', 'string'],
            'billing_info'    => ['required', 'array'],
        ]);

        $user = Auth::user();
        $subscriptionId = $request->input('subscription_id');

        try {
            // Find the subscription
            $subscription = UserSubscription::where('user_id', $user->id)
                ->where('paypal_subscription_id', $subscriptionId)
                ->first();

            if (!$subscription) {
                throw new Exception('Subscription not found.');
            }

            // Update subscription to active
            $subscription->update([
                'status'   => 'active',
                'metadata' => array_merge($subscription->metadata ?? [], [
                    'activated_at' => now()->toISOString(),
                    'billing_info' => $request->input('billing_info'),
                ]),
            ]);

            Log::info('PayPal subscription activated via API', [
                'user_id'                => $user->id,
                'subscription_id'        => $subscription->id,
                'paypal_subscription_id' => $subscriptionId,
            ]);

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Subscription activated successfully.',
                'subscription' => [
                    'id'        => $subscription->id,
                    'status'    => $subscription->status,
                    'starts_at' => $subscription->starts_at,
                    'ends_at'   => $subscription->ends_at,
                ],
                'redirect_url' => route('subscriptions.success'),
            ]);
        } catch (Exception $e) {
            Log::error('PayPal subscription activation failed via API', [
                'user_id'         => $user->id,
                'subscription_id' => $subscriptionId,
                'error'           => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * API: Get current subscription
     */
    public function getCurrent(Request $request)
    {
        $user = Auth::user();
        $subscription = $user->activeSubscription();

        if (!$subscription) {
            return response()->json([
                'success'      => TRUE,
                'subscription' => NULL,
                'message'      => 'No active subscription found.',
            ]);
        }

        return response()->json([
            'success'      => TRUE,
            'subscription' => [
                'id'                => $subscription->id,
                'status'            => $subscription->status,
                'payment_method'    => $subscription->payment_method,
                'plan_name'         => $subscription->paymentPlan->name,
                'amount'            => $subscription->amount_paid,
                'starts_at'         => $subscription->starts_at,
                'ends_at'           => $subscription->ends_at,
                'next_billing_date' => $subscription->ends_at,
            ],
        ]);
    }

    /**
     * API: Get subscription history
     */
    public function getHistory(Request $request)
    {
        $user = Auth::user();
        $subscriptions = $user->subscriptions()
            ->with('paymentPlan')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success'       => TRUE,
            'subscriptions' => $subscriptions->map(function ($subscription) {
                return [
                    'id'             => $subscription->id,
                    'status'         => $subscription->status,
                    'payment_method' => $subscription->payment_method,
                    'plan_name'      => $subscription->paymentPlan->name,
                    'amount'         => $subscription->amount_paid,
                    'starts_at'      => $subscription->starts_at,
                    'ends_at'        => $subscription->ends_at,
                    'created_at'     => $subscription->created_at,
                ];
            }),
        ]);
    }

    /**
     * Get ticket usage for a user in a given period
     */
    private function getTicketUsage(User $user, Carbon $period): array
    {
        // This would need to be implemented based on your ticket tracking system
        // For now, return mock data
        return [
            'used'         => 0,
            'limit'        => $user->getCurrentPlan()?->max_tickets_per_month ?? 0,
            'remaining'    => $user->getRemainingTicketAllowance(),
            'period_start' => $period,
            'period_end'   => $period->copy()->endOfMonth(),
        ];
    }
}
