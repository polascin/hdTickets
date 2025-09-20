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

            // Create or update Stripe customer
            $stripeCustomer = $this->paymentService->createOrUpdateCustomer($user);

            // Create subscription based on payment method
            $subscription = match ($request->payment_method) {
                'stripe' => $this->paymentService->createStripeSubscription(
                    $user,
                    $plan,
                    $stripeCustomer->id,
                ),
                'paypal' => $this->paymentService->createPayPalSubscription(
                    $user,
                    $plan,
                ),
                default => throw new Exception('Invalid payment method'),
            };

            DB::commit();

            return redirect()->route('subscription.success')
                ->with('success', 'Subscription activated successfully!');
        } catch (Exception $e) {
            DB::rollBack();

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
            // Cancel subscription with payment provider
            if ($subscription->stripe_subscription_id) {
                $this->paymentService->cancelStripeSubscription($subscription->stripe_subscription_id);
            }

            // Update local subscription status
            $subscription->update([
                'status'  => 'cancelled',
                'ends_at' => now()->endOfMonth(), // Allow access until end of current billing period
            ]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Subscription cancelled. You will have access until the end of your billing period.');
        } catch (Exception $e) {
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
