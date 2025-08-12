<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SecurityService;
use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;

use function in_array;

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
     * @param Request $request
     *
     * @throws TokenMismatchException
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Enhanced CSRF protection for sports events system
        if ($this->isReading($request)
            || $this->runningUnitTests()
            || $this->inExceptArray($request)
            || $this->tokensMatch($request)) {
            return $this->addCookieToResponse($request, $next($request));
        }

        // Log CSRF token mismatch for security monitoring
        $this->logCsrfViolation($request);

        throw new TokenMismatchException('CSRF token mismatch.');
    }

    /**
     * Determine if the HTTP request uses a 'read' verb.
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->method(), ['HEAD', 'GET', 'OPTIONS'], TRUE);
    }

    /**
     * Enhanced token matching with additional security checks
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);

        // Check if session token exists
        if (! $sessionToken = $request->session()->token()) {
            return FALSE;
        }

        // Standard CSRF token validation
        if (! hash_equals($sessionToken, $token)) {
            return FALSE;
        }

        // Additional security checks for sports events platform
        return $this->performAdditionalSecurityChecks($request);
    }

    /**
     * Perform additional security checks specific to sports events monitoring
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function performAdditionalSecurityChecks($request)
    {
        // Check for suspicious request patterns
        if ($this->detectSuspiciousPatterns($request)) {
            return FALSE;
        }

        // Verify user session integrity
        if (! $this->verifySessionIntegrity($request)) {
            return FALSE;
        }

        // Check request frequency for automated attacks
        return ! ($this->isRequestTooFrequent($request));
    }

    /**
     * Detect suspicious request patterns
     *
     * @param Request $request
     *
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
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Verify session integrity
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function verifySessionIntegrity($request)
    {
        $session = $request->session();

        // Check if session has required security markers
        if (! $session->has('_token') || ! $session->has('login_web_')) {
            return TRUE; // Not logged in, standard CSRF is enough
        }

        // For logged-in users, verify session consistency
        $expectedFingerprint = hash(
            'sha256',
            $request->ip() . '|' .
            $request->userAgent() . '|' .
            config('app.key'),
        );

        $storedFingerprint = $session->get('security_fingerprint');

        if (! $storedFingerprint) {
            // Create fingerprint if it doesn't exist
            $session->put('security_fingerprint', $expectedFingerprint);

            return TRUE;
        }

        return hash_equals($storedFingerprint, $expectedFingerprint);
    }

    /**
     * Check if requests are coming too frequently (rate limiting)
     *
     * @param Request $request
     *
     * @return bool
     */
    protected function isRequestTooFrequent($request)
    {
        $key = 'csrf_rate_limit:' . $request->ip();
        $cache = app('cache');

        $attempts = $cache->get($key, 0);

        if ($attempts > 60) { // More than 60 requests per minute
            return TRUE;
        }

        $cache->put($key, $attempts + 1, 60); // Store for 1 minute

        return FALSE;
    }

    /**
     * Log CSRF violation for security monitoring
     *
     * @param Request $request
     */
    protected function logCsrfViolation($request): void
    {
        $securityService = app(SecurityService::class);

        $securityService->logSecurityActivity(
            'CSRF token mismatch detected',
            [
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer'    => $request->header('referer'),
                'timestamp'  => now()->toISOString(),
                'risk_level' => 'high',
            ],
        );
    }
}
