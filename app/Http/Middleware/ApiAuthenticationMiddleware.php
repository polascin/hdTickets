<?php declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function in_array;

class ApiAuthenticationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     * @param array                     $roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return $this->errorResponse('Authentication required.', 401);
        }

        $user = Auth::user();

        // Check if user account is active
        if (!$user->is_active) {
            Log::warning('Inactive user attempted API access', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);

            return $this->errorResponse('Account is inactive. Please contact support.', 403);
        }

        // Check if user is verified (email verification)
        if (!$user->hasVerifiedEmail()) {
            return $this->errorResponse(
                'Email verification required.',
                403,
                ['verification_required' => TRUE, 'verification_url' => route('verification.notice')],
            );
        }

        // Role-based access control
        if ($roles !== [] && !$this->hasRequiredRole($user, $roles)) {
            Log::warning('Insufficient permissions for API access', [
                'user_id'        => $user->id,
                'user_role'      => $user->role,
                'required_roles' => $roles,
                'endpoint'       => $request->path(),
            ]);

            return $this->errorResponse(
                'Insufficient permissions.',
                403,
                ['user_role' => $user->role, 'required_roles' => $roles],
            );
        }

        // Rate limiting based on user role and endpoint
        $rateLimitKey = $this->getRateLimitKey($user, $request);
        $maxAttempts = $this->getMaxAttemptsForUser($user, $request);
        $decayMinutes = $this->getDecayMinutesForUser($user);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            Log::warning('API rate limit exceeded', [
                'user_id'        => $user->id,
                'rate_limit_key' => $rateLimitKey,
                'max_attempts'   => $maxAttempts,
                'available_in'   => $seconds,
                'endpoint'       => $request->path(),
                'ip'             => $request->ip(),
            ]);

            return $this->errorResponse(
                'Rate limit exceeded. Please try again later.',
                429,
                [
                    'retry_after' => $seconds,
                    'limit'       => $maxAttempts,
                    'window'      => $decayMinutes * 60,
                ],
            );
        }

        // Increment rate limiter
        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        // Block scrapers from accessing non-scraping APIs
        if ($user->isScraper() && !$this->isScraperAllowedEndpoint($request)) {
            Log::warning('Scraper attempted to access restricted API', [
                'user_id'  => $user->id,
                'endpoint' => $request->path(),
                'ip'       => $request->ip(),
            ]);

            return $this->errorResponse(
                'Access denied for scraper accounts.',
                403,
                ['account_type' => 'scraper'],
            );
        }

        // Check for suspicious activity patterns
        if ($this->detectSuspiciousActivity($user, $request)) {
            Log::alert('Suspicious API activity detected', [
                'user_id'    => $user->id,
                'endpoint'   => $request->path(),
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return $this->errorResponse(
                'Suspicious activity detected. Account temporarily restricted.',
                403,
                ['temporary_restriction' => TRUE, 'contact_support' => TRUE],
            );
        }

        // Add user info to request for controllers
        $request->attributes->set('authenticated_user', $user);
        $request->attributes->set('user_permissions', $user->getPermissions());

        // Log API access for audit trail (only for sensitive endpoints)
        if ($this->shouldLogAccess($request)) {
            $this->logApiAccess($user, $request);
        }

        return $next($request);
    }

    /**
     * Check if user has required role
     *
     * @param mixed $user
     */
    private function hasRequiredRole($user, array $roles): bool
    {
        if ($roles === []) {
            return TRUE;
        }

        // Allow 'any' role requirement
        if (in_array('any', $roles, TRUE)) {
            return TRUE;
        }

        // Check specific roles
        return in_array($user->role, $roles, TRUE);
    }

    /**
     * Get rate limit key for user and request
     *
     * @param mixed $user
     */
    private function getRateLimitKey($user, Request $request): string
    {
        $endpoint = $this->getEndpointCategory($request);

        return "api_rate_limit:{$user->id}:{$endpoint}";
    }

    /**
     * Get maximum attempts based on user role and endpoint
     *
     * @param mixed $user
     */
    private function getMaxAttemptsForUser($user, Request $request): int
    {
        $endpoint = $this->getEndpointCategory($request);

        // Different limits based on user role and endpoint
        $limits = [
            'admin' => [
                'search'       => 1000,
                'purchase'     => 100,
                'notification' => 500,
                'general'      => 2000,
            ],
            'agent' => [
                'search'       => 500,
                'purchase'     => 50,
                'notification' => 300,
                'general'      => 1000,
            ],
            'customer' => [
                'search'       => 200,
                'purchase'     => 20,
                'notification' => 100,
                'general'      => 300,
            ],
            'scraper' => [
                'scraping' => 10000,
                'general'  => 50,
            ],
        ];

        return $limits[$user->role][$endpoint] ?? $limits[$user->role]['general'] ?? 100;
    }

    /**
     * Get decay minutes based on user role
     *
     * @param mixed $user
     */
    private function getDecayMinutesForUser($user): int
    {
        // Higher tier users get longer windows
        return match ($user->role) {
            'admin'    => 1,  // 1 minute window
            'agent'    => 1,  // 1 minute window
            'customer' => 1, // 1 minute window
            'scraper'  => 60, // 60 minute window for scrapers
            default    => 1,
        };
    }

    /**
     * Get endpoint category for rate limiting
     */
    private function getEndpointCategory(Request $request): string
    {
        $path = $request->path();

        if (str_contains($path, '/api/tickets/search') || str_contains($path, '/api/tickets/suggestions')) {
            return 'search';
        }

        if (str_contains($path, '/api/tickets/purchase') || str_contains($path, '/api/tickets/') && $request->isMethod('POST')) {
            return 'purchase';
        }

        if (str_contains($path, '/api/notifications')) {
            return 'notification';
        }

        if (str_contains($path, '/api/scraping') || str_contains($path, '/api/scraped-data')) {
            return 'scraping';
        }

        return 'general';
    }

    /**
     * Check if scraper is accessing allowed endpoint
     */
    private function isScraperAllowedEndpoint(Request $request): bool
    {
        $path = $request->path();

        // Allow only specific endpoints for scrapers
        $allowedPaths = [
            '/api/scraping/',
            '/api/scraped-data/',
            '/api/auth/logout',
            '/api/user/profile', // Basic profile info
        ];

        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($path, $allowedPath)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Detect suspicious activity patterns
     *
     * @param mixed $user
     */
    private function detectSuspiciousActivity($user, Request $request): bool
    {
        $cacheKey = "suspicious_activity:{$user->id}";
        $activity = Cache::get($cacheKey, []);

        // Track request patterns
        $now = time();
        $activity[] = [
            'timestamp'  => $now,
            'endpoint'   => $request->path(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
        ];

        // Keep only last hour of activity
        $activity = array_filter($activity, fn (array $entry): bool => $entry['timestamp'] > $now - 3600);

        // Check for suspicious patterns
        $recentRequests = count($activity);
        $uniqueEndpoints = count(array_unique(array_column($activity, 'endpoint')));
        $uniqueIPs = count(array_unique(array_column($activity, 'ip')));

        // Suspicious if: too many requests, too few unique endpoints (automation), multiple IPs
        $isSuspicious = (
            $recentRequests > 500 // More than 500 requests per hour
            || ($recentRequests > 100 && $uniqueEndpoints < 3) // High volume, low diversity
            || $uniqueIPs > 5 // Multiple IPs for same user
        );

        // Update cache
        Cache::put($cacheKey, $activity, 3600); // Store for 1 hour

        return $isSuspicious;
    }

    /**
     * Check if API access should be logged
     */
    private function shouldLogAccess(Request $request): bool
    {
        $sensitiveEndpoints = [
            '/api/tickets/purchase',
            '/api/notifications/settings',
            '/api/user/profile',
            '/api/subscription',
        ];

        foreach ($sensitiveEndpoints as $endpoint) {
            if (str_contains($request->path(), $endpoint)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Log API access for audit trail
     *
     * @param mixed $user
     */
    private function logApiAccess($user, Request $request): void
    {
        Log::info('API access', [
            'user_id'    => $user->id,
            'user_role'  => $user->role,
            'endpoint'   => $request->path(),
            'method'     => $request->method(),
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toISOString(),
        ]);
    }

    /**
     * Return standardized error response
     */
    private function errorResponse(string $message, int $status, array $data = []): JsonResponse
    {
        return response()->json([
            'success'    => FALSE,
            'message'    => $message,
            'error_code' => $status,
            'timestamp'  => now()->toISOString(),
            ...$data,
        ], $status);
    }
}
