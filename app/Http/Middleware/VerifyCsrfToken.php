<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use App\Services\SecurityService;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // API endpoints use different authentication
        'api/*',
        // Webhook endpoints (with signature verification)
        'webhooks/*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Illuminate\Session\TokenMismatchException
     */
    public function handle($request, \Closure $next)
    {
        // Enhanced CSRF protection for sports events system
        if ($this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        // Log CSRF token mismatch for security monitoring
        $this->logCsrfViolation($request);

        throw new TokenMismatchException('CSRF token mismatch.');
    }

    /**
     * Determine if the HTTP request uses a 'read' verb.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Enhanced token matching with additional security checks
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);
        
        // Check if session token exists
        if (!$sessionToken = $request->session()->token()) {
            return false;
        }

        // Standard CSRF token validation
        if (!hash_equals($sessionToken, $token)) {
            return false;
        }

        // Additional security checks for sports events platform
        return $this->performAdditionalSecurityChecks($request);
    }

    /**
     * Perform additional security checks specific to sports events monitoring
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function performAdditionalSecurityChecks($request)
    {
        // Check for suspicious request patterns
        if ($this->detectSuspiciousPatterns($request)) {
            return false;
        }

        // Verify user session integrity
        if (!$this->verifySessionIntegrity($request)) {
            return false;
        }

        // Check request frequency for automated attacks
        if ($this->isRequestTooFrequent($request)) {
            return false;
        }

        return true;
    }

    /**
     * Detect suspicious request patterns
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function detectSuspiciousPatterns($request)
    {
        $suspiciousPatterns = [
            // Rapid automated requests
            'user-agent' => '/bot|crawler|spider|scraper/i',
            // Unusual referrers
            'referer' => '/\.onion|localhost:[0-9]{4}/',
        ];

        foreach ($suspiciousPatterns as $header => $pattern) {
            $headerValue = $request->header($header, '');
            if (preg_match($pattern, $headerValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify session integrity
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function verifySessionIntegrity($request)
    {
        $session = $request->session();
        
        // Check if session has required security markers
        if (!$session->has('_token') || !$session->has('login_web_')) {
            return true; // Not logged in, standard CSRF is enough
        }

        // For logged-in users, verify session consistency
        $expectedFingerprint = hash('sha256', 
            $request->ip() . '|' . 
            $request->userAgent() . '|' . 
            config('app.key')
        );

        $storedFingerprint = $session->get('security_fingerprint');
        
        if (!$storedFingerprint) {
            // Create fingerprint if it doesn't exist
            $session->put('security_fingerprint', $expectedFingerprint);
            return true;
        }

        return hash_equals($storedFingerprint, $expectedFingerprint);
    }

    /**
     * Check if requests are coming too frequently (rate limiting)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isRequestTooFrequent($request)
    {
        $key = 'csrf_rate_limit:' . $request->ip();
        $cache = app('cache');
        
        $attempts = $cache->get($key, 0);
        
        if ($attempts > 60) { // More than 60 requests per minute
            return true;
        }
        
        $cache->put($key, $attempts + 1, 60); // Store for 1 minute
        
        return false;
    }

    /**
     * Log CSRF violation for security monitoring
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logCsrfViolation($request)
    {
        $securityService = app(SecurityService::class);
        
        $securityService->logSecurityActivity(
            'CSRF token mismatch detected',
            [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'timestamp' => now()->toISOString(),
                'risk_level' => 'high'
            ]
        );
    }
}
