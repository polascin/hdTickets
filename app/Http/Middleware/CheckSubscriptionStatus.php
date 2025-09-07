<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function in_array;

class CheckSubscriptionStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Skip check for non-authenticated users
        if (!$user) {
            return $next($request);
        }

        // Skip check for admin and scraper users
        if ($user->isAdmin() || $user->isScraper()) {
            return $next($request);
        }

        // Skip check for agent users (they have unlimited access)
        if ($user->isAgent()) {
            return $next($request);
        }

        // Skip check if already on payment/subscription related routes
        if ($this->isPaymentRoute($request)) {
            return $next($request);
        }

        // Skip check if user email is not verified
        if (!$user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Check subscription status for customers
        if ($user->isCustomer() && !$this->hasValidSubscription($user)) {
            return redirect()->route('subscription.payment')
                ->with('warning', 'Please complete your monthly subscription payment to access the platform.');
        }

        return $next($request);
    }

    /**
     * Check if current route is payment-related
     */
    private function isPaymentRoute(Request $request): bool
    {
        $paymentRoutes = [
            'subscription.payment',
            'subscription.process',
            'subscription.success',
            'subscription.cancel',
            'subscription.webhook',
            'legal.show',
            'legal.index',
            'profile.edit',
            'logout',
            'verification.notice',
            'verification.verify',
            'verification.send',
        ];

        $currentRoute = $request->route()?->getName();

        return in_array($currentRoute, $paymentRoutes, TRUE)
               || str_starts_with($request->path(), 'api/')
               || str_starts_with($request->path(), 'legal/');
    }

    /**
     * Check if user has valid subscription
     */
    private function hasValidSubscription(User $user): bool
    {
        // Check if user has active subscription
        if ($user->hasActiveSubscription()) {
            return TRUE;
        }

        // Check if user is still on trial
        if ($user->isOnTrial()) {
            return TRUE;
        }

        // Check configuration for free access period
        $freeAccessDays = (int) config('subscription.free_access_days', 0);
        if ($freeAccessDays > 0) {
            $accountAge = $user->created_at->diffInDays(now());
            if ($accountAge <= $freeAccessDays) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
