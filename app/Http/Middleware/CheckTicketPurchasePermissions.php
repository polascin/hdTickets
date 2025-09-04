<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

use function in_array;

class CheckTicketPurchasePermissions
{
    /**
     * Handle an incoming request for ticket purchase operations
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $this->unauthorizedResponse('Authentication required for ticket purchases');
        }

        // Check if user role allows ticket purchases
        if (! $this->userCanPurchaseTickets($user)) {
            return $this->forbiddenResponse('Your user role does not allow ticket purchases');
        }

        // Check subscription status for customers
        if ($user->isCustomer()) {
            $subscriptionCheck = $this->checkCustomerSubscription($user);
            if ($subscriptionCheck !== TRUE) {
                return $this->subscriptionRequiredResponse($subscriptionCheck);
            }
        }

        // Check ticket purchase limits
        if ($this->hasExceededTicketLimits($user, $request)) {
            return $this->limitExceededResponse($user);
        }

        return $next($request);
    }

    /**
     * Check if user can purchase tickets based on role
     */
    private function userCanPurchaseTickets(User $user): bool
    {
        // Scraper role cannot purchase tickets
        if ($user->role === User::ROLE_SCRAPER) {
            return FALSE;
        }

        // All other roles can purchase tickets
        return in_array($user->role, [
            User::ROLE_CUSTOMER,
            User::ROLE_AGENT,
            User::ROLE_ADMIN,
        ], TRUE);
    }

    /**
     * Check customer subscription status and validity
     */
    private function checkCustomerSubscription(User $user): bool|string
    {
        $subscription = $user->subscription;

        // Check if user has a subscription
        if (! $subscription) {
            return 'No active subscription found';
        }

        // Check if still in free trial period
        if ($subscription->isInFreeTrial()) {
            return TRUE;
        }

        // Check if subscription is active
        if (! $subscription->isActive()) {
            return 'Subscription is not active';
        }

        // Check if subscription is expired
        if ($subscription->isExpired()) {
            return 'Subscription has expired';
        }

        // Check if payment is up to date
        if ($subscription->hasFailedPayments()) {
            return 'Subscription payment failed - please update payment method';
        }

        return TRUE;
    }

    /**
     * Check if user has exceeded their ticket purchase limits
     */
    private function hasExceededTicketLimits(User $user, Request $request): bool
    {
        // Agents and admins have unlimited access
        if ($user->isAgent() || $user->isAdmin()) {
            return FALSE;
        }

        // For customers, check monthly limits
        if ($user->isCustomer()) {
            $subscription = $user->subscription;
            if (! $subscription) {
                return TRUE;
            }

            $monthlyLimit = $subscription->plan->ticket_limit ?? config('subscription.default_ticket_limit', 100);
            $currentMonthUsage = $user->getMonthlyTicketUsage();

            // Check if requesting quantity would exceed limit
            $requestedQuantity = $request->input('quantity', 1);

            return ($currentMonthUsage + $requestedQuantity) > $monthlyLimit;
        }

        return FALSE;
    }

    /**
     * Return unauthorized response
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => FALSE,
            'error'   => 'unauthorized',
            'message' => $message,
            'data'    => [
                'action_required' => 'login',
                'redirect_url'    => route('login'),
            ],
        ], 401);
    }

    /**
     * Return forbidden response
     */
    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => FALSE,
            'error'   => 'forbidden',
            'message' => $message,
            'data'    => [
                'user_role'     => Auth::user()?->role,
                'allowed_roles' => [User::ROLE_CUSTOMER, User::ROLE_AGENT, User::ROLE_ADMIN],
            ],
        ], 403);
    }

    /**
     * Return subscription required response
     */
    private function subscriptionRequiredResponse(string $reason): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => FALSE,
            'error'   => 'subscription_required',
            'message' => "Subscription required: {$reason}",
            'data'    => [
                'subscription_status' => $user->subscription?->status,
                'free_trial_expired'  => $user->subscription?->isFreeTrial() === FALSE,
                'action_required'     => 'subscription',
                'subscription_url'    => route('subscription.plans'),
                'pricing'             => [
                    'monthly_fee'     => config('subscription.default_monthly_fee', 29.99),
                    'currency'        => 'USD',
                    'free_trial_days' => config('subscription.free_access_days', 7),
                ],
            ],
        ], 402); // Payment required
    }

    /**
     * Return limit exceeded response
     */
    private function limitExceededResponse(User $user): JsonResponse
    {
        $subscription = $user->subscription;
        $monthlyLimit = $subscription?->plan?->ticket_limit ?? config('subscription.default_ticket_limit', 100);
        $currentUsage = $user->getMonthlyTicketUsage();

        return response()->json([
            'success' => FALSE,
            'error'   => 'limit_exceeded',
            'message' => 'Monthly ticket purchase limit exceeded',
            'data'    => [
                'monthly_limit'   => $monthlyLimit,
                'current_usage'   => $currentUsage,
                'remaining'       => max(0, $monthlyLimit - $currentUsage),
                'reset_date'      => now()->endOfMonth()->format('Y-m-d'),
                'upgrade_options' => [
                    'agent_role'       => 'Contact admin for agent role (unlimited tickets)',
                    'subscription_url' => route('subscription.plans'),
                ],
            ],
        ], 429); // Too many requests
    }
}
