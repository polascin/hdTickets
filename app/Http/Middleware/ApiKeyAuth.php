<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * API Key Authentication Middleware
 * 
 * Handles API key-based authentication with:
 * - Secure API key validation
 * - Permission-based access control
 * - Rate limiting per API key
 * - Usage tracking and analytics
 */
class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        $apiKey = $this->extractApiKey($request);
        
        if (!$apiKey) {
            return $this->unauthorizedResponse('API key required');
        }

        $keyModel = $this->validateApiKey($apiKey);
        
        if (!$keyModel) {
            return $this->unauthorizedResponse('Invalid API key');
        }

        if (!$keyModel->isValid()) {
            return $this->unauthorizedResponse('API key is expired, revoked, or inactive');
        }

        // Check permissions if specified
        if (!empty($permissions) && !$keyModel->hasAnyPermission($permissions)) {
            return $this->forbiddenResponse('Insufficient permissions');
        }

        // Check rate limiting
        $rateLimitResult = $this->checkRateLimit($keyModel, $request);
        if ($rateLimitResult !== true) {
            return $rateLimitResult;
        }

        // Set the authenticated user
        Auth::setUser($keyModel->user);
        
        // Add API key info to request for tracking
        $request->attributes->set('api_key', $keyModel);
        $request->attributes->set('auth_method', 'api_key');

        // Record usage
        $this->recordUsage($keyModel, $request);

        return $next($request);
    }

    /**
     * Extract API key from request
     */
    private function extractApiKey(Request $request): ?string
    {
        // Check Authorization header (Bearer token format)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check X-API-Key header
        $apiKeyHeader = $request->header('X-API-Key');
        if ($apiKeyHeader) {
            return $apiKeyHeader;
        }

        // Check query parameter (less secure, only for GET requests)
        if ($request->isMethod('GET')) {
            return $request->query('api_key');
        }

        return null;
    }

    /**
     * Validate API key and return model
     */
    private function validateApiKey(string $providedKey): ?ApiKey
    {
        // Cache the validation result for 5 minutes to reduce database queries
        $cacheKey = 'api_key_validation:' . hash('sha256', $providedKey);
        
        return Cache::remember($cacheKey, 300, function () use ($providedKey) {
            $keyHash = hash('sha256', $providedKey);
            
            return ApiKey::where('key_hash', $keyHash)
                ->with('user')
                ->first();
        });
    }

    /**
     * Check rate limiting for API key
     */
    private function checkRateLimit(ApiKey $apiKey, Request $request): true|JsonResponse
    {
        // Use API key specific rate limiting
        $rateLimitKey = "api_key_rate_limit:{$apiKey->id}";
        $maxAttempts = $apiKey->rate_limit ?: $this->getDefaultRateLimit($apiKey->user);
        
        // Check if rate limited
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded',
                'errors' => [
                    'rate_limit' => [
                        'max_requests' => $maxAttempts,
                        'window' => 'per hour',
                        'retry_after' => $retryAfter
                    ]
                ],
                'timestamp' => now()->toISOString()
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $maxAttempts,
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
                'Retry-After' => $retryAfter
            ]);
        }

        // Increment rate limiter
        RateLimiter::hit($rateLimitKey, 3600); // 1 hour window
        
        // Add rate limit headers
        $remaining = $maxAttempts - RateLimiter::attempts($rateLimitKey);
        $request->attributes->set('rate_limit_headers', [
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addHour()->timestamp
        ]);

        return true;
    }

    /**
     * Record API key usage
     */
    private function recordUsage(ApiKey $apiKey, Request $request): void
    {
        // Update API key usage in background
        dispatch(function () use ($apiKey, $request) {
            $apiKey->recordUsage($request->ip());
            
            // Log detailed usage if needed
            $this->logApiUsage($apiKey, $request);
        })->afterResponse();
    }

    /**
     * Log detailed API usage for analytics
     */
    private function logApiUsage(ApiKey $apiKey, Request $request): void
    {
        try {
            // This would create a record in api_usage_logs table
            // For now, we'll use cache to store basic metrics
            $dateKey = now()->format('Y-m-d-H');
            $usageKey = "api_usage:{$apiKey->id}:{$dateKey}";
            
            Cache::increment($usageKey, 1);
            Cache::expire($usageKey, 86400 * 7); // Keep for 7 days
            
            // Store request details for analytics
            $requestData = [
                'method' => $request->method(),
                'endpoint' => $request->path(),
                'timestamp' => now()->timestamp,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ];
            
            $detailKey = "api_requests:{$apiKey->id}:" . now()->format('Y-m-d');
            $existing = Cache::get($detailKey, []);
            $existing[] = $requestData;
            
            // Keep only last 100 requests per day
            if (count($existing) > 100) {
                $existing = array_slice($existing, -100);
            }
            
            Cache::put($detailKey, $existing, 86400);
            
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::warning('Failed to log API usage', [
                'api_key_id' => $apiKey->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get default rate limit based on user subscription
     */
    private function getDefaultRateLimit(User $user): int
    {
        return match ($user->subscription_plan) {
            'starter' => 100,
            'pro' => 1000,
            'enterprise' => 10000,
            default => 50
        };
    }

    /**
     * Return unauthorized response
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], 401);
    }

    /**
     * Return forbidden response
     */
    private function forbiddenResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], 403);
    }
}

/**
 * Rate Limiting Middleware for API endpoints
 */
class ApiRateLimit
{
    /**
     * Handle an incoming request with dynamic rate limiting
     */
    public function handle(Request $request, Closure $next, string $limitType = 'general')
    {
        $user = Auth::user();
        $apiKey = $request->attributes->get('api_key');
        
        // Determine rate limits based on subscription and endpoint type
        $limits = $this->getRateLimits($user, $limitType);
        
        // Create rate limit key
        $identifier = $apiKey ? "api_key:{$apiKey->id}" : "user:{$user->id}";
        $rateLimitKey = "api_rate_limit:{$limitType}:{$identifier}";
        
        // Check rate limiting
        if (RateLimiter::tooManyAttempts($rateLimitKey, $limits['max_attempts'])) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            return response()->json([
                'success' => false,
                'message' => "Rate limit exceeded for {$limitType} operations",
                'errors' => [
                    'rate_limit' => [
                        'type' => $limitType,
                        'max_requests' => $limits['max_attempts'],
                        'window' => $limits['window'],
                        'retry_after' => $retryAfter
                    ]
                ],
                'timestamp' => now()->toISOString()
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $limits['max_attempts'],
                'X-RateLimit-Remaining' => 0,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
                'Retry-After' => $retryAfter
            ]);
        }

        // Increment rate limiter
        RateLimiter::hit($rateLimitKey, $limits['decay_seconds']);
        
        // Add rate limit headers to response
        $response = $next($request);
        
        $remaining = $limits['max_attempts'] - RateLimiter::attempts($rateLimitKey);
        $response->headers->add([
            'X-RateLimit-Limit' => $limits['max_attempts'],
            'X-RateLimit-Remaining' => max(0, $remaining),
            'X-RateLimit-Reset' => now()->addSeconds($limits['decay_seconds'])->timestamp
        ]);

        return $response;
    }

    /**
     * Get rate limits based on user subscription and operation type
     */
    private function getRateLimits(User $user, string $limitType): array
    {
        $baseLimits = match ($user->subscription_plan) {
            'starter' => ['general' => 100, 'search' => 20, 'intensive' => 10],
            'pro' => ['general' => 1000, 'search' => 200, 'intensive' => 50],
            'enterprise' => ['general' => 10000, 'search' => 2000, 'intensive' => 500],
            default => ['general' => 50, 'search' => 10, 'intensive' => 5]
        };

        $maxAttempts = $baseLimits[$limitType] ?? $baseLimits['general'];
        
        // Different time windows based on operation type
        $decaySeconds = match ($limitType) {
            'intensive' => 3600, // 1 hour for intensive operations
            'search' => 900,     // 15 minutes for search operations
            default => 3600      // 1 hour for general operations
        };

        return [
            'max_attempts' => $maxAttempts,
            'decay_seconds' => $decaySeconds,
            'window' => $this->formatWindow($decaySeconds)
        ];
    }

    /**
     * Format time window for display
     */
    private function formatWindow(int $seconds): string
    {
        if ($seconds >= 3600) {
            return (int)($seconds / 3600) . ' hour(s)';
        } elseif ($seconds >= 60) {
            return (int)($seconds / 60) . ' minute(s)';
        } else {
            return $seconds . ' second(s)';
        }
    }
}