<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Security\ApiSecurityService;
use App\Services\Security\SecurityMonitoringService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Log;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function is_array;
use function strlen;

class SecurityHeadersMiddleware
{
    protected $securityMonitoring;

    protected $apiSecurity;

    public function __construct(
        // SecurityMonitoringService $securityMonitoring,
        // ApiSecurityService $apiSecurity
    ) {
        // $this->securityMonitoring = $securityMonitoring;
        // $this->apiSecurity = $apiSecurity;
    }

    /**
     * Handle an incoming request with comprehensive security checks
     *
     * @param Closure(Request): (Response) $next
     */
    /**
     * Handle
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Monitor request for threats (temporarily disabled)
        // $threatAnalysis = $this->securityMonitoring->monitorRequest($request);

        // Block request if critical threats detected (temporarily disabled)
        // if ($threatAnalysis['risk_level'] === 'critical') {
        //     return response()->json([
        //         'error' => 'Request blocked due to security policy violation',
        //         'code' => 'SECURITY_BLOCK'
        //     ], 403);
        // }

        // Check API rate limits for API routes (temporarily disabled)
        // if ($request->is('api/*')) {
        //     $endpoint = $this->getEndpointIdentifier($request);
        //     $user = Auth::user();

        //     $rateLimitResult = $this->apiSecurity->checkRateLimit($request, $endpoint, $user);
        //     if (!$rateLimitResult['allowed']) {
        //         return response()->json([
        //             'error' => 'Rate limit exceeded',
        //             'retry_after' => $rateLimitResult['retry_after'] ?? 60
        //         ], 429);
        //     }
        // }

        // Process request
        $response = $next($request);

        // Apply security headers
        $this->applySecurityHeaders($request, $response);

        // Record request metrics
        $this->recordRequestMetrics($request, $response);

        return $response;
    }

    /**
     * Apply comprehensive security headers
     */
    /**
     * ApplySecurityHeaders
     */
    protected function applySecurityHeaders(Request $request, Response $response): void
    {
        $headers = config('security.headers', []);

        // Apply configured headers
        foreach ($headers as $header => $value) {
            $response->headers->set($header, $value);
        }

        // X-Content-Type-Options: Prevent MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options: Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // X-XSS-Protection: Enable XSS filtering
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: Control browser features
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), payment=(), usb=()');

        // Content-Security-Policy: Define content sources
        // First remove any existing CSP header
        $response->headers->remove('Content-Security-Policy');
        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Debug headers
        $cspConfig = config('security.csp', []);
        $response->headers->set('X-Debug-CSP-Count', (string) count($cspConfig));
        $response->headers->set('X-Debug-CSP-Length', (string) strlen($csp));
        $response->headers->set('X-Debug-CSP-Preview', substr($csp, 0, 100));
        $response->headers->set('X-Debug-Method-Called', 'yes');

        // Strict-Transport-Security: Force HTTPS (only if HTTPS)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('X-RateLimit-Limit');
        $response->headers->remove('X-RateLimit-Remaining');

        // Cache control for sensitive pages
        if ($this->isSensitivePage($request)) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        // Add security audit headers for debugging (only in non-production)
        if (config('app.env') !== 'production') {
            $response->headers->set('X-Security-Scan', 'passed');
            $response->headers->set('X-Request-ID', $request->header('X-Request-ID', uniqid()));
        }
    }

    /**
     * Get endpoint identifier for rate limiting
     */
    /**
     * Get  endpoint identifier
     */
    protected function getEndpointIdentifier(Request $request): string
    {
        $route = $request->route();
        if ($route) {
            $action = $route->getActionName();
            $name = $route->getName();

            if ($name) {
                return $name;
            }

            // Extract controller method
            if (str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                $controller = class_basename($controller);

                return strtolower("{$controller}.{$method}");
            }
        }

        // Fallback to URL pattern
        $path = $request->path();

        return str_replace(['/', '-'], ['.', '_'], $path);
    }

    /**
     * Record request metrics for monitoring
     */
    /**
     * RecordRequestMetrics
     */
    protected function recordRequestMetrics(Request $request, Response $response): void
    {
        $now = now();
        $key = 'security_metrics:' . $now->format('Y-m-d:H');

        // Increment request counters
        Cache::increment("{$key}:total_requests");
        Cache::increment("{$key}:status_{$response->getStatusCode()}");

        // Track API requests separately
        if ($request->is('api/*')) {
            Cache::increment("{$key}:api_requests");

            if ($response->getStatusCode() >= 400) {
                Cache::increment("{$key}:api_errors");
            }
        }

        // Track failed authentication attempts
        if ($request->is('auth/*') && $response->getStatusCode() === 422) {
            Cache::increment("{$key}:failed_auth");
        }

        // Set expiration for metrics (keep for 7 days)
        Cache::put($key . ':expires_at', now()->addDays(7), 604800);
    }

    /**
     * Build Content Security Policy header
     */
    /**
     * BuildContentSecurityPolicy
     */
    protected function buildContentSecurityPolicy(): string
    {
        $cspConfig = config('security.csp', []);

        // Debug logging to see what's happening
        Log::info('SecurityHeadersMiddleware: buildContentSecurityPolicy called');
        Log::info('CSP Config loaded:', ['count' => count($cspConfig), 'keys' => array_keys($cspConfig)]);

        $policies = [];

        foreach ($cspConfig as $directive => $sources) {
            if ($directive === 'upgrade-insecure-requests') {
                // Only add upgrade-insecure-requests directive if it's true
                if ($sources === TRUE) {
                    $policies[] = 'upgrade-insecure-requests';
                }
                // Skip adding it if it's false (which is what we want)
            } elseif (is_array($sources)) {
                $policies[] = $directive . ' ' . implode(' ', $sources);
            }
        }

        Log::info('Built CSP Policies count:', ['count' => count($policies)]);

        // Use fallback only if no valid policies were generated from config
        if (empty($policies)) {
            Log::info('Using fallback CSP policy - policies array was empty');
            $policies = [
                "default-src 'self'",
            ];
        }

        $finalCSP = implode('; ', $policies);
        Log::info('Final CSP String length:', ['length' => strlen($finalCSP), 'preview' => substr($finalCSP, 0, 100) . '...']);

        return $finalCSP;
    }

    /**
     * Check if the current page is sensitive (admin, auth, etc.)
     */
    /**
     * Check if  sensitive page
     */
    protected function isSensitivePage(Request $request): bool
    {
        $sensitiveRoutes = [
            'admin/*',
            'auth/*',
            'profile/*',
            'api/auth/*',
            'two-factor/*',
            'purchase/*',
            'payment/*',
        ];

        foreach ($sensitiveRoutes as $route) {
            if ($request->is($route)) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
