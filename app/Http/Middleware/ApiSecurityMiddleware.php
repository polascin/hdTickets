<?php declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\InputValidationService;
use App\Services\SecurityService;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

use function in_array;
use function strlen;

class ApiSecurityMiddleware
{
    protected SecurityService $securityService;

    protected InputValidationService $validationService;

    public function __construct(SecurityService $securityService, InputValidationService $validationService)
    {
        $this->securityService = $securityService;
        $this->validationService = $validationService;
    }

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
        // Perform comprehensive security checks
        $this->performSecurityChecks($request);

        // Validate API authentication
        $this->validateApiAuthentication($request);

        // Check rate limiting
        $this->checkRateLimit($request);

        // Sanitize input data
        $this->sanitizeRequestData($request);

        // Process request
        $response = $next($request);

        // Add security headers to API responses
        $this->addApiSecurityHeaders($response);

        return $response;
    }

    /**
     * Perform comprehensive security checks
     */
    /**
     * PerformSecurityChecks
     */
    protected function performSecurityChecks(Request $request): void
    {
        // Check for suspicious IP addresses
        if ($this->isSuspiciousIp($request->ip())) {
            $this->logSecurityViolation($request, 'Suspicious IP address detected');
            abort(403, 'Access denied from this IP address');
        }

        // Check user agent for known bad bots
        if ($this->isMaliciousUserAgent($request->userAgent())) {
            $this->logSecurityViolation($request, 'Malicious user agent detected');
            abort(403, 'Invalid user agent');
        }

        // Check request size limits
        if ($this->exceedsRequestSizeLimit($request)) {
            $this->logSecurityViolation($request, 'Request size limit exceeded');
            abort(413, 'Request entity too large');
        }

        // Validate request structure
        if ($this->hasInvalidRequestStructure($request)) {
            $this->logSecurityViolation($request, 'Invalid request structure');
            abort(400, 'Invalid request format');
        }
    }

    /**
     * Validate API authentication
     */
    /**
     * ValidateApiAuthentication
     */
    protected function validateApiAuthentication(Request $request): void
    {
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');

        if (! $apiKey) {
            $this->logSecurityViolation($request, 'Missing API key');
            abort(401, 'API key is required');
        }

        if (! $this->isValidApiKey($apiKey)) {
            $this->logSecurityViolation($request, 'Invalid API key');
            abort(401, 'Invalid API key');
        }

        // Verify API key signature if present
        $signature = $request->header('X-API-Signature');
        if ($signature && ! $this->verifyApiSignature($request, $apiKey, $signature)) {
            $this->logSecurityViolation($request, 'Invalid API signature');
            abort(401, 'Invalid API signature');
        }

        // Store validated API key info in request
        $request->attributes->set('api_key_info', $this->getApiKeyInfo($apiKey));
    }

    /**
     * Check rate limiting for API requests
     */
    /**
     * CheckRateLimit
     */
    protected function checkRateLimit(Request $request): void
    {
        $apiKeyInfo = $request->attributes->get('api_key_info');
        $rateLimit = $apiKeyInfo['rate_limit'] ?? 1000;
        $window = $apiKeyInfo['rate_window'] ?? 3600; // 1 hour

        $key = 'api_rate_limit:' . $apiKeyInfo['id'];
        $current = Cache::get($key, 0);

        if ($current >= $rateLimit) {
            $this->logSecurityViolation($request, 'API rate limit exceeded');
            abort(429, 'Rate limit exceeded. Try again later.');
        }

        Cache::put($key, $current + 1, $window);

        // Add rate limit headers
        $request->attributes->set('rate_limit_remaining', $rateLimit - $current - 1);
        $request->attributes->set('rate_limit_total', $rateLimit);
    }

    /**
     * Sanitize request data
     */
    /**
     * SanitizeRequestData
     */
    protected function sanitizeRequestData(Request $request): void
    {
        // Get all input data
        $data = $request->all();

        try {
            // Sanitize input using validation service
            $sanitized = $this->validationService->sanitizeInput($data);

            // Replace request data with sanitized version
            $request->replace($sanitized);
        } catch (Exception $e) {
            $this->logSecurityViolation($request, 'Input sanitization failed: ' . $e->getMessage());
            abort(400, 'Invalid input data');
        }
    }

    /**
     * Check if IP address is suspicious
     */
    /**
     * Check if  suspicious ip
     */
    protected function isSuspiciousIp(string $ip): bool
    {
        // Check against known bad IP lists
        $suspiciousIps = Cache::remember('suspicious_ips', 3600, function () {
            // In production, this would fetch from a threat intelligence service
            return [
                '127.0.0.1', // Example - in production would be real threat IPs
            ];
        });

        return in_array($ip, $suspiciousIps, TRUE);
    }

    /**
     * Check if user agent is malicious
     */
    /**
     * Check if  malicious user agent
     */
    protected function isMaliciousUserAgent(?string $userAgent): bool
    {
        if (! $userAgent) {
            return TRUE; // Require user agent
        }

        $maliciousPatterns = [
            '/sqlmap/i',
            '/nikto/i',
            '/nmap/i',
            '/masscan/i',
            '/nessus/i',
            '/openvas/i',
            '/acunetix/i',
            '/w3af/i',
            '/skipfish/i',
            '/havij/i',
        ];

        foreach ($maliciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if request exceeds size limits
     */
    /**
     * ExceedsRequestSizeLimit
     */
    protected function exceedsRequestSizeLimit(Request $request): bool
    {
        $maxSize = 10 * 1024 * 1024; // 10MB limit
        $contentLength = $request->header('Content-Length', 0);
        
        // Ensure we have a numeric value for comparison
        $contentLengthInt = is_array($contentLength) ? (int) (reset($contentLength) ?: 0) : (int) $contentLength;

        return $contentLengthInt > $maxSize;
    }

    /**
     * Check for invalid request structure
     */
    /**
     * Check if has  invalid request structure
     */
    protected function hasInvalidRequestStructure(Request $request): bool
    {
        // Check for suspicious headers
        $suspiciousHeaders = [
            'X-Forwarded-For' => '/[^0-9.,\s:a-fA-F]/',
            'X-Real-IP'       => '/[^0-9.:]/',
        ];

        foreach ($suspiciousHeaders as $header => $pattern) {
            $value = $request->header($header);
            if ($value && preg_match($pattern, $value)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Validate API key
     */
    /**
     * Check if  valid api key
     */
    protected function isValidApiKey(string $apiKey): bool
    {
        // Check API key format (should be 40 character hash)
        if (strlen($apiKey) !== 40 || ! ctype_alnum($apiKey)) {
            return FALSE;
        }

        // Check against database of valid API keys
        $validKeys = Cache::remember('valid_api_keys', 300, function () {
            // In production, fetch from database
            return [
                'valid_key_hash_1' => [
                    'id'          => 1,
                    'name'        => 'Sports Events Monitor',
                    'rate_limit'  => 2000,
                    'rate_window' => 3600,
                    'permissions' => ['scrape', 'purchase', 'analytics'],
                ],
                // Add more API keys as needed
            ];
        });

        return isset($validKeys[$apiKey]);
    }

    /**
     * Get API key information
     */
    /**
     * Get  api key info
     */
    protected function getApiKeyInfo(string $apiKey): array
    {
        $validKeys = Cache::get('valid_api_keys', []);

        return $validKeys[$apiKey] ?? [];
    }

    /**
     * Verify API signature
     */
    /**
     * VerifyApiSignature
     */
    protected function verifyApiSignature(Request $request, string $apiKey, string $signature): bool
    {
        // Create expected signature
        $payload = $request->method() . '|' . $request->getPathInfo() . '|' . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $apiKey);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Add security headers to API responses
     */
    /**
     * AddApiSecurityHeaders
     */
    protected function addApiSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
    }

    /**
     * Log security violation
     */
    /**
     * LogSecurityViolation
     */
    protected function logSecurityViolation(Request $request, string $reason): void
    {
        $this->securityService->logSecurityActivity(
            'API security violation: ' . $reason,
            [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url'        => $request->fullUrl(),
                'method'     => $request->method(),
                'headers'    => $request->headers->all(),
                'risk_level' => 'high',
            ],
        );
    }
}
