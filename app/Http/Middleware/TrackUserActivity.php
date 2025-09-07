<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

use function in_array;
use function is_string;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    /**
     * Handle
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track authenticated users
        if (Auth::check() && $this->shouldTrackActivity($request)) {
            $this->trackUserActivity(Auth::user());
        }

        return $response;
    }

    /**
     * Get user activity status
     */
    /**
     * Get  user activity status
     */
    public static function getUserActivityStatus(int $userId): array
    {
        $lastActivity = Cache::get("user_last_activity:{$userId}");
        $activityData = Cache::get("user_activity:{$userId}", []);

        if (!$lastActivity) {
            return [
                'is_active'              => FALSE,
                'last_seen'              => NULL,
                'minutes_since_activity' => NULL,
                'activity_level'         => 'inactive',
            ];
        }

        $lastSeen = is_string($lastActivity) ? $lastActivity : $lastActivity->toISOString();
        $minutesSinceActivity = now()->diffInMinutes($lastActivity);

        return [
            'is_active'              => $minutesSinceActivity <= 15,
            'last_seen'              => $lastSeen,
            'minutes_since_activity' => $minutesSinceActivity,
            'activity_level'         => self::getActivityLevel($minutesSinceActivity),
            'details'                => $activityData,
        ];
    }

    /**
     * Check if user was recently active on alert-related pages
     */
    /**
     * Check if  user active on alerts
     */
    public static function isUserActiveOnAlerts(int $userId): bool
    {
        return Cache::has("user_alert_activity:{$userId}");
    }

    /**
     * Check if user was recently viewing tickets
     */
    /**
     * Check if  user active on tickets
     */
    public static function isUserActiveOnTickets(int $userId): bool
    {
        return Cache::has("user_ticket_activity:{$userId}");
    }

    /**
     * Manually mark user as active (for API usage)
     */
    /**
     * MarkUserActive
     */
    public static function markUserActive(int $userId): void
    {
        Cache::put("user_last_activity:{$userId}", now(), 86400);
        Cache::put("user_activity:{$userId}", [
            'last_seen' => now()->toISOString(),
            'method'    => 'manual',
            'source'    => 'api',
        ], 86400);
    }

    /**
     * Get activity statistics for analytics
     */
    /**
     * Get  activity statistics
     */
    public static function getActivityStatistics(): array
    {
        // This would be implemented to gather analytics
        // For now, return basic structure
        return [
            'active_users_last_5min'  => 0,
            'active_users_last_15min' => 0,
            'active_users_last_hour'  => 0,
            'total_tracked_users'     => 0,
        ];
    }

    /**
     * Track user activity for alert escalation system
     *
     * @param mixed $user
     */
    /**
     * TrackUserActivity
     */
    protected function trackUserActivity(App\Models\User $user): void
    {
        $cacheKey = "user_activity:{$user->id}";
        $activityData = [
            'last_seen'  => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'route'      => request()->route() ? request()->route()->getName() : NULL,
            'method'     => request()->method(),
        ];

        // Store activity for 24 hours
        Cache::put($cacheKey, $activityData, 86400);

        // Also store a simple timestamp for quick escalation checks
        Cache::put("user_last_activity:{$user->id}", now(), 86400);

        // Track page-specific activity for better insights
        $this->trackPageActivity($user);

        // Update user's last_active_at timestamp periodically
        $this->updateUserLastActive($user);
    }

    /**
     * Track specific page/feature activity
     *
     * @param mixed $user
     */
    /**
     * TrackPageActivity
     */
    protected function trackPageActivity(App\Models\User $user): void
    {
        $route = request()->route();
        if (!$route) {
            return;
        }

        $routeName = $route->getName();
        if (!$routeName) {
            return;
        }

        // Track activity on alert-related pages more frequently
        $alertRoutes = [
            'alerts.index',
            'alerts.create',
            'alerts.show',
            'tickets.scraping.index',
            'tickets.scraping.show',
            'notifications.preferences',
            'notifications.channels',
        ];

        if (in_array($routeName, $alertRoutes, TRUE)) {
            Cache::put("user_alert_activity:{$user->id}", now(), 3600);
        }

        // Track ticket viewing activity
        if (str_contains($routeName, 'tickets')) {
            Cache::put("user_ticket_activity:{$user->id}", now(), 1800);
        }
    }

    /**
     * Update user's last_active_at timestamp in database (throttled)
     *
     * @param mixed $user
     */
    /**
     * UpdateUserLastActive
     */
    protected function updateUserLastActive(App\Models\User $user): void
    {
        $cacheKey = "user_db_update:{$user->id}";

        // Only update database every 5 minutes to avoid excessive writes
        if (!Cache::has($cacheKey)) {
            $user->update(['last_active_at' => now()]);
            Cache::put($cacheKey, TRUE, 300); // 5 minutes
        }
    }

    /**
     * Determine if we should track activity for this request
     */
    /**
     * ShouldTrackActivity
     */
    protected function shouldTrackActivity(Request $request): bool
    {
        // Skip tracking for certain routes
        $skipRoutes = [
            'api.health',
            'api.ping',
            'telescope.*',
            'horizon.*',
            '_debugbar.*',
        ];

        $routeName = $request->route() ? $request->route()->getName() : '';

        foreach ($skipRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return FALSE;
            }
        }

        // Skip tracking for AJAX requests that are not user-initiated
        if ($request->ajax() && $this->isAutomatedRequest($request)) {
            return FALSE;
        }

        // Skip tracking for API calls that are not user-initiated
        return !($request->is('api/*') && $this->isAutomatedApiCall($request));
    }

    /**
     * Check if request is automated (polling, etc.)
     */
    /**
     * Check if  automated request
     */
    protected function isAutomatedRequest(Request $request): bool
    {
        $automatedPaths = [
            'api/notifications/poll',
            'api/alerts/check',
            'api/tickets/refresh',
            'api/heartbeat',
        ];

        foreach ($automatedPaths as $path) {
            if ($request->is($path)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if API call is automated
     */
    /**
     * Check if  automated api call
     */
    protected function isAutomatedApiCall(Request $request): bool
    {
        // Check for automated API calls by user agent or specific headers
        $userAgent = $request->userAgent();

        $automatedAgents = [
            'bot',
            'crawler',
            'spider',
            'monitor',
            'check',
        ];

        foreach ($automatedAgents as $agent) {
            if (stripos($userAgent, $agent) !== FALSE) {
                return TRUE;
            }
        }

        // Check for automated API headers
        return (bool) ($request->hasHeader('X-Automated-Request'));
    }

    /**
     * Get activity level based on time since last activity
     */
    /**
     * Get  activity level
     */
    protected static function getActivityLevel(int $minutesSinceActivity): string
    {
        if ($minutesSinceActivity <= 5) {
            return 'very_active';
        }
        if ($minutesSinceActivity <= 15) {
            return 'active';
        }
        if ($minutesSinceActivity <= 60) {
            return 'recently_active';
        }
        if ($minutesSinceActivity <= 240) { // 4 hours
            return 'inactive';
        }

        return 'very_inactive';
    }
}
