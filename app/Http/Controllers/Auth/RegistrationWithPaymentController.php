<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PaymentPlan;
use App\Models\UserSubscription;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Controllers\PaymentsController;

class RegistrationWithPaymentController extends Controller
{
    /**
     * Display the registration view with payment plans.
     */
    public function create(): View|Response
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied. User registration is restricted to administrators only.');
        }

        $paymentPlans = PaymentPlan::active()->ordered()->get();
        
        return view('auth.register-with-payment', compact('paymentPlans'));
    }

    /**
     * Handle registration request with payment plan selection.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user is authenticated and is an admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied. User registration is restricted to administrators only.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['sometimes', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['sometimes', 'string', 'in:admin,agent,customer,scraper'],
            'is_active' => ['sometimes', 'boolean'],
            'require_2fa' => ['sometimes', 'boolean'],
            'payment_plan_id' => ['required', 'exists:payment_plans,id'],
            'subscription_type' => ['required', 'in:trial,paid,admin_granted'],
            'trial_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
            'payment_method' => ['required_if:subscription_type,paid', 'in:stripe,paypal'],
            // Billing address fields
            'billing_address.street' => ['required_if:subscription_type,paid', 'string', 'max:255'],
            'billing_address.city' => ['required_if:subscription_type,paid', 'string', 'max:255'],
            'billing_address.state' => ['required_if:subscription_type,paid', 'string', 'max:255'],
            'billing_address.postal_code' => ['required_if:subscription_type,paid', 'string', 'max:20'],
            'billing_address.country' => ['required_if:subscription_type,paid', 'string', 'max:255'],
        ]);

        $paymentPlan = PaymentPlan::findOrFail($request->payment_plan_id);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'surname' => $request->surname,
            'username' => $request->username ?? strtolower(str_replace(' ', '.', $request->name)),
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? User::ROLE_CUSTOMER,
            'is_active' => $request->is_active ?? true,
            'require_2fa' => $request->require_2fa ?? false,
            'registration_source' => 'admin_with_payment',
            'created_by_type' => 'admin',
            'created_by_id' => Auth::id(),
            'password_changed_at' => now(),
            'billing_address' => $request->subscription_type === 'paid' ? $request->billing_address : null,
        ]);

        $subscriptionData = [
            'payment_plan_id' => $paymentPlan->id,
            'starts_at' => now(),
        ];

        // Payment handling for paid subscriptions
        if ($request->subscription_type === 'paid') {
            if ($request->payment_method === 'stripe') {
                // Stripe payment
                try {
                    Stripe::setApiKey(config('services.stripe.secret'));
                    $paymentIntent = PaymentIntent::create([
                        'amount' => (int)($paymentPlan->price * 100), // cents
                        'currency' => 'usd',
                        'payment_method' => $request->stripe_payment_method_id,
                        'confirmation_method' => 'manual',
                        'confirm' => true,
                    ]);
                    if ($paymentIntent->status !== 'succeeded') {
                        return back()->withErrors(['payment' => 'Stripe payment failed.']);
                    }
                    $subscriptionData['stripe_payment_intent_id'] = $paymentIntent->id;
                    $subscriptionData['payment_method'] = 'stripe';
                    $subscriptionData['amount_paid'] = $paymentPlan->price;
                } catch (\Exception $e) {
                    return back()->withErrors(['payment' => 'Stripe error: ' . $e->getMessage()]);
                }
            } elseif ($request->payment_method === 'paypal') {
                // PayPal payment with new SDK
                try {
                    $client = PaypalServerSdkClientBuilder::init()
                        ->clientCredentialsAuthCredentials(
                            ClientCredentialsAuthCredentialsBuilder::init(
                                config('services.paypal.client_id'),
                                config('services.paypal.secret')
                            )
                        )
                        ->environment(
                            config('services.paypal.environment') === 'production' 
                                ? Environment::PRODUCTION 
                                : Environment::SANDBOX
                        )
                        ->build();

                    $paymentsController = new PaymentsController($client);
                    $payment = $paymentsController->get($request->paypal_payment_id);
                    if ($payment->getStatus() !== 'COMPLETED') {
                        return back()->withErrors(['payment' => 'PayPal payment failed.']);
                    }
                    $subscriptionData['paypal_transaction_id'] = $payment->getId();
                    $subscriptionData['payment_method'] = 'paypal';
                    $subscriptionData['amount_paid'] = $paymentPlan->price;
                } catch (\Exception $e) {
                    return back()->withErrors(['payment' => 'PayPal error: ' . $e->getMessage()]);
                }
            }
            // Calculate end date based on billing cycle
            switch ($paymentPlan->billing_cycle) {
                case 'monthly':
                    $subscriptionData['ends_at'] = now()->addMonth();
                    break;
                case 'yearly':
                    $subscriptionData['ends_at'] = now()->addYear();
                    break;
                case 'lifetime':
                    $subscriptionData['ends_at'] = null;
                    break;
            }
            $subscriptionData['status'] = 'active';
        } elseif ($request->subscription_type === 'trial') {
            $trialDays = $request->trial_days ?? 14;
            $subscriptionData['status'] = 'trial';
            $subscriptionData['trial_ends_at'] = now()->addDays($trialDays);
        } elseif ($request->subscription_type === 'admin_granted') {
            $subscriptionData['status'] = 'active';
            $subscriptionData['ends_at'] = null; // No expiration for admin granted
            $subscriptionData['amount_paid'] = 0;
            $subscriptionData['metadata'] = ['granted_by_admin' => Auth::id()];
        }

        // Create the subscription
        $subscription = $user->subscriptions()->create($subscriptionData);
        $user->update(['current_subscription_id' => $subscription->id]);

        event(new Registered($user));

        // Redirect with success message
        return redirect()->route('admin.users.index')
            ->with('success', "User '{$user->name}' has been successfully created with {$paymentPlan->name} plan ({$subscription->formatted_status}).");
    }

    /**
     * Show payment plan selection page for existing user
     */
    public function selectPlan(User $user): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $paymentPlans = PaymentPlan::active()->ordered()->get();
        $currentSubscription = $user->activeSubscription();
        
        return view('auth.select-payment-plan', compact('user', 'paymentPlans', 'currentSubscription'));
    }

    /**
     * Assign payment plan to existing user
     */
    public function assignPlan(Request $request, User $user): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $request->validate([
            'payment_plan_id' => ['required', 'exists:payment_plans,id'],
            'subscription_type' => ['required', 'in:trial,paid,admin_granted'],
            'trial_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ]);

        $paymentPlan = PaymentPlan::findOrFail($request->payment_plan_id);

        // Subscribe user to the new plan
        $options = [
            'status' => $request->subscription_type === 'trial' ? 'trial' : 'active',
            'starts_at' => now(),
        ];

        if ($request->subscription_type === 'trial') {
            $options['trial_ends_at'] = now()->addDays($request->trial_days ?? 14);
        } elseif ($request->subscription_type === 'paid') {
            $options['amount_paid'] = $paymentPlan->price;
        } elseif ($request->subscription_type === 'admin_granted') {
            $options['amount_paid'] = 0;
            $options['metadata'] = ['granted_by_admin' => Auth::id()];
        }

        $subscription = $user->subscribeToPlan($paymentPlan, $options);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "User has been subscribed to {$paymentPlan->name} plan ({$subscription->formatted_status}).");
    }
}
