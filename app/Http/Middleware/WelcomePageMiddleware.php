<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\UserAgentHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

use function in_array;
use function is_array;

class WelcomePageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Geographic restriction check
        if ($this->isGeographicallyRestricted($request)) {
            return response()->view('errors.geo-restricted', [], 451);
        }

        // Add security headers
        $this->addSecurityHeaders($request);

        // Track visitor analytics
        $this->trackVisitorAnalytics($request);

        $response = $next($request);

        // Add cache headers for public content
        if (!Auth::check()) {
            $this->addPublicCacheHeaders($response);
        }

        // Add performance headers
        $this->addPerformanceHeaders($response);

        return $response;
    }

    /**
     * Check if request is from geographically restricted location
     *
     * @return bool
     */
    protected function isGeographicallyRestricted(Request $request)
    {
        // Skip geo-restriction in development
        if (app()->environment('local', 'testing')) {
            return FALSE;
        }

        $restrictedCountries = config('welcome.geo_restrictions.blocked_countries', []);

        if (empty($restrictedCountries)) {
            return FALSE;
        }

        // Get country from IP (this would require a GeoIP service)
        $country = $this->getCountryFromIp($request->ip());

        return in_array($country, $restrictedCountries, TRUE);
    }

    /**
     * Add security headers to the response
     */
    protected function addSecurityHeaders(Request $request): void
    {
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; " .
               "font-src 'self' https://fonts.bunny.net; " .
               "img-src 'self' data: https:; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none';";

        header('Content-Security-Policy: ' . $csp);

        // Other security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

        // HSTS for HTTPS
        if ($request->secure()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Track visitor analytics
     */
    protected function trackVisitorAnalytics(Request $request): void
    {
        try {
            $deviceInfo = UserAgentHelper::getDeviceInfo($request);

            $visitorData = [
                'ip'               => $request->ip(),
                'user_agent'       => UserAgentHelper::sanitise($deviceInfo['user_agent'] ?? NULL),
                'device_type'      => $deviceInfo['device_type'] ?? 'unknown',
                'is_ios'           => $deviceInfo['is_ios'] ?? FALSE,
                'referer'          => $request->header('referer'),
                'timestamp'        => now(),
                'session_id'       => $request->session()->getId(),
                'is_authenticated' => Auth::check(),
                'user_id'          => Auth::id(),
            ];

            // Log iOS access specifically
            if ($deviceInfo['is_ios']) {
                UserAgentHelper::logIOSRequest($request, 'welcome_page_visit');
            }

            // Store visitor data in cache for batch processing
            $cacheKey = 'visitor_analytics_' . date('Y-m-d-H');
            $visitors = Cache::get($cacheKey, []);
            $visitors[] = $visitorData;

            // Store for 2 hours to batch process
            Cache::put($cacheKey, $visitors, 7200);

            // Track unique visitors
            $this->trackUniqueVisitor($request);
        } catch (Throwable $e) {
            Log::warning('Analytics tracking error in middleware', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);
        }
    }

    /**
     * Track unique visitors
     */
    protected function trackUniqueVisitor(Request $request): void
    {
        $visitorHash = hash('sha256', $request->ip() . $request->userAgent());
        $cacheKey = 'unique_visitor_' . date('Y-m-d') . '_' . $visitorHash;

        if (!Cache::has($cacheKey)) {
            // Mark as seen for today
            Cache::put($cacheKey, TRUE, 86400); // 24 hours

            // Increment daily unique visitor count
            $dailyCountKey = 'daily_unique_visitors_' . date('Y-m-d');
            Cache::increment($dailyCountKey, 1);
            Cache::put($dailyCountKey . '_expires', TRUE, 172800); // Keep for 2 days
        }
    }

    /**
     * Add cache headers for public content
     *
     * @param mixed $response
     */
    protected function addPublicCacheHeaders($response): void
    {
        // Cache for 5 minutes for anonymous users
        $response->header('Cache-Control', 'public, max-age=300, s-maxage=300');
        $response->header('Vary', 'Accept-Encoding, User-Agent');

        // ETag for cache validation
        $etag = md5((string) $response->getContent());
        $response->header('ETag', $etag);

        // Last modified
        $response->header('Last-Modified', now()->format('D, d M Y H:i:s \G\M\T'));
    }

    /**
     * Add performance headers
     *
     * @param mixed $response
     */
    protected function addPerformanceHeaders($response): void
    {
        // Resource hints for critical resources
        $response->header('Link', '<https://fonts.bunny.net>; rel=preconnect');

        // Server timing header for performance monitoring
        $serverTiming = 'total;dur=' . round((microtime(TRUE) - LARAVEL_START) * 1000, 2);
        $response->header('Server-Timing', $serverTiming);
    }

    /**
     * Get country code from IP address
     * This is a placeholder - in production you'd use a service like MaxMind
     *
     * @param string $ip
     *
     * @return string|null
     */
    protected function getCountryFromIp($ip)
    {
        // Skip for local/private IPs
        if ($ip === '*********' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return; // Local IP
        }

        // Cache country lookups to avoid repeated API calls
        $cacheKey = 'country_lookup_' . hash('sha256', $ip);

        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            // Use free GeoIP service with comprehensive error handling
            try {
                $context = stream_context_create([
                    'http' => [
                        'timeout'       => 2, // 2 second timeout
                        'ignore_errors' => TRUE,
                        'method'        => 'GET',
                        'header'        => [
                            'User-Agent: HDTickets/1.0',
                            'Accept: application/json',
                        ],
                    ],
                ]);

                $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode", FALSE, $context);

                if ($response === FALSE || $response === '') {
                    Log::debug('GeoIP country lookup failed for IP', [
                        'ip'    => $ip,
                        'error' => error_get_last()['message'] ?? 'Unknown error',
                    ]);

                    return;
                }

                $data = json_decode($response, TRUE);

                if (!is_array($data)) {
                    Log::debug('Invalid GeoIP response', [
                        'ip'       => $ip,
                        'response' => substr($response, 0, 200),
                    ]);

                    return;
                }

                return $data['countryCode'] ?? NULL;
            } catch (Throwable $e) {
                Log::debug('GeoIP lookup failed', [
                    'ip'    => $ip,
                    'error' => $e->getMessage(),
                ]);

                return;
            }
        });
    }

    /**
     * Check if visitor uses automated tools (bot detection)
     */
    protected function isAutomatedTool(Request $request): bool
    {
        try {
            // Use UserAgentHelper to safely detect automated tools
            $deviceInfo = UserAgentHelper::getDeviceInfo($request);

            // Don't flag iOS devices as bots
            if ($deviceInfo['is_ios']) {
                return FALSE;
            }

            return UserAgentHelper::isAutomatedTool($request);
        } catch (Throwable $e) {
            Log::debug('Error detecting automated tool', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
            ]);

            // Assume not a bot on error - don't block legitimate users
            return FALSE;
        }
    }

    /**
     * Log suspicious activity
     *
     * @param string $reason
     */
    protected function logSuspiciousActivity(Request $request, $reason): void
    {
        Log::warning('Suspicious activity detected on welcome page', [
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason'     => $reason,
            'timestamp'  => now(),
            'url'        => $request->fullUrl(),
            'referer'    => $request->header('referer'),
        ]);
    }
}
