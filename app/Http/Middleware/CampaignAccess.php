<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use function in_array;

/**
 * Campaign Management Access Middleware
 *
 * Controls access to marketing campaign features based on:
 * - User roles and permissions
 * - Subscription plan limits
 * - Feature availability
 */
class CampaignAccess
{
    /**
     * Handle an incoming request
     *
     * @param array $permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check user role permissions
        if (! $this->hasRequiredPermissions($user, $permissions)) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Insufficient permissions for campaign management',
            ], 403);
        }

        // Check subscription plan limits
        if (! $this->hasSubscriptionAccess($user)) {
            return response()->json([
                'success'     => FALSE,
                'message'     => 'Upgrade your subscription to access marketing campaigns',
                'upgrade_url' => route('subscription.plans'),
            ], 402);
        }

        // Check campaign limits based on plan
        if (! $this->withinCampaignLimits($user, $request)) {
            return response()->json([
                'success'     => FALSE,
                'message'     => 'Campaign limit reached for your subscription plan',
                'upgrade_url' => route('subscription.plans'),
            ], 429);
        }

        return $next($request);
    }

    /**
     * Check if user has required permissions
     *
     * @param mixed $user
     */
    private function hasRequiredPermissions($user, array $permissions): bool
    {
        // Admin users have full access
        if ($user->role === 'admin') {
            return TRUE;
        }

        // Agent users have limited access
        if ($user->role === 'agent') {
            $allowedPermissions = ['view-campaigns', 'create-campaigns', 'manage-campaigns'];

            return empty($permissions) || ! empty(array_intersect($permissions, $allowedPermissions));
        }

        // Customer users have very limited access
        if ($user->role === 'customer') {
            $allowedPermissions = ['view-campaigns'];

            return empty($permissions) || ! empty(array_intersect($permissions, $allowedPermissions));
        }

        return FALSE;
    }

    /**
     * Check if user's subscription plan allows campaign access
     *
     * @param mixed $user
     */
    private function hasSubscriptionAccess($user): bool
    {
        $plan = $user->subscription_plan ?? 'free';

        // Free plan users cannot access marketing campaigns
        if ($plan === 'free') {
            return FALSE;
        }

        // All paid plans have campaign access
        return in_array($plan, ['starter', 'pro', 'enterprise'], TRUE);
    }

    /**
     * Check if user is within campaign limits for their plan
     *
     * @param mixed $user
     */
    private function withinCampaignLimits($user, Request $request): bool
    {
        // Skip limit check for read operations
        if ($request->isMethod('GET')) {
            return TRUE;
        }

        $plan = $user->subscription_plan ?? 'free';
        $currentCampaigns = $user->marketingCampaigns()->count();

        $limits = [
            'starter'    => 5,      // 5 campaigns per month
            'pro'        => 25,         // 25 campaigns per month
            'enterprise' => -1,   // Unlimited campaigns
        ];

        $limit = $limits[$plan] ?? 0;

        // Unlimited campaigns
        if ($limit === -1) {
            return TRUE;
        }

        // Check if within limit
        return $currentCampaigns < $limit;
    }
}
