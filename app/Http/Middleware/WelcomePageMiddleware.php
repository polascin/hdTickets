<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WelcomePageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
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
     * @param Request $request
     * @return bool
     */
    protected function isGeographicallyRestricted(Request $request)
    {
        // Skip geo-restriction in development
        if (app()->environment('local', 'testing')) {
            return false;
        }
        
        $restrictedCountries = config('welcome.geo_restrictions.blocked_countries', []);
        
        if (empty($restrictedCountries)) {
            return false;
        }
        
        // Get country from IP (this would require a GeoIP service)
        $country = $this->getCountryFromIp($request->ip());
        
        return in_array($country, $restrictedCountries);
    }

    /**
     * Add security headers to the response
     *
     * @param Request $request
     * @return void
     */
    protected function addSecurityHeaders(Request $request)
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
     *
     * @param Request $request
     * @return void
     */
    protected function trackVisitorAnalytics(Request $request)
    {
        try {
            $visitorData = [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'timestamp' => now(),
                'session_id' => $request->session()->getId(),
                'is_authenticated' => Auth::check(),
                'user_id' => Auth::id()
            ];
            
            // Store visitor data in cache for batch processing
            $cacheKey = 'visitor_analytics_' . date('Y-m-d-H');
            $visitors = Cache::get($cacheKey, []);
            $visitors[] = $visitorData;
            
            // Store for 2 hours to batch process
            Cache::put($cacheKey, $visitors, 7200);
            
            // Track unique visitors
            $this->trackUniqueVisitor($request);
            
        } catch (\Exception $e) {
            Log::warning('Analytics tracking error in middleware: ' . $e->getMessage());
        }
    }

    /**
     * Track unique visitors
     *
     * @param Request $request
     * @return void
     */
    protected function trackUniqueVisitor(Request $request)
    {
        $visitorHash = hash('sha256', $request->ip() . $request->userAgent());
        $cacheKey = 'unique_visitor_' . date('Y-m-d') . '_' . $visitorHash;
        
        if (!Cache::has($cacheKey)) {
            // Mark as seen for today
            Cache::put($cacheKey, true, 86400); // 24 hours
            
            // Increment daily unique visitor count
            $dailyCountKey = 'daily_unique_visitors_' . date('Y-m-d');
            Cache::increment($dailyCountKey, 1);
            Cache::put($dailyCountKey . '_expires', true, 172800); // Keep for 2 days
        }
    }

    /**
     * Add cache headers for public content
     *
     * @param $response
     * @return void
     */
    protected function addPublicCacheHeaders($response)
    {
        // Cache for 5 minutes for anonymous users
        $response->header('Cache-Control', 'public, max-age=300, s-maxage=300');
        $response->header('Vary', 'Accept-Encoding, User-Agent');
        
        // ETag for cache validation
        $etag = md5($response->getContent());
        $response->header('ETag', $etag);
        
        // Last modified
        $response->header('Last-Modified', now()->format('D, d M Y H:i:s \G\M\T'));
    }

    /**
     * Add performance headers
     *
     * @param $response
     * @return void
     */
    protected function addPerformanceHeaders($response)
    {
        // Resource hints for critical resources
        $response->header('Link', '<https://fonts.bunny.net>; rel=preconnect');
        
        // Server timing header for performance monitoring
        $serverTiming = 'total;dur=' . round((microtime(true) - LARAVEL_START) * 1000, 2);
        $response->header('Server-Timing', $serverTiming);
    }

    /**
     * Get country code from IP address
     * This is a placeholder - in production you'd use a service like MaxMind
     *
     * @param string $ip
     * @return string|null
     */
    protected function getCountryFromIp($ip)
    {
        // Placeholder implementation
        // In production, use MaxMind GeoIP2 or similar service
        if ($ip === '127.0.0.1' || str_starts_with($ip, '192.168.')) {
            return null; // Local IP
        }
        
        // Cache country lookups to avoid repeated API calls
        $cacheKey = 'country_lookup_' . hash('sha256', $ip);
        
        return Cache::remember($cacheKey, 86400, function () use ($ip) {
            // Example: Use a free GeoIP service
            try {
                $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
                $data = json_decode($response, true);
                return $data['countryCode'] ?? null;
            } catch (\Exception $e) {
                Log::warning('GeoIP lookup failed: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Check if visitor uses automated tools (bot detection)
     *
     * @param Request $request
     * @return bool
     */
    protected function isAutomatedTool(Request $request)
    {
        $userAgent = strtolower($request->userAgent() ?? '');
        
        $botIndicators = [
            'bot', 'spider', 'crawler', 'scraper', 'curl', 'wget', 'python',
            'java', 'go-http-client', 'okhttp', 'apache-httpclient'
        ];
        
        foreach ($botIndicators as $indicator) {
            if (str_contains($userAgent, $indicator)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log suspicious activity
     *
     * @param Request $request
     * @param string $reason
     * @return void
     */
    protected function logSuspiciousActivity(Request $request, $reason)
    {
        Log::warning('Suspicious activity detected on welcome page', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'reason' => $reason,
            'timestamp' => now(),
            'url' => $request->fullUrl(),
            'referer' => $request->header('referer')
        ]);
    }
}
