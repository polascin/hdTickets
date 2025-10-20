<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * API Authentication Controller
 *
 * Handles API authentication including:
 * - API key generation and management
 * - Token-based authentication
 * - Rate limiting and security
 * - Developer account management
 */
class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['login', 'register', 'refresh']);
        $this->middleware('throttle:auth')->only(['login', 'register']);
    }

    /**
     * API user login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'       => 'required|email',
            'password'    => 'required|string|min:6',
            'remember_me' => 'boolean',
            'device_name' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember_me', FALSE);

        // Rate limiting
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return $this->errorResponse('Too many login attempts', [
                'retry_after' => $seconds,
            ], 429);
        }

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            // Create token with appropriate expiration
            $tokenResult = $user->createToken(
                $request->input('device_name', 'API Access'),
                ['*'],
                $remember ? now()->addDays(30) : now()->addHours(24),
            );

            // Clear rate limiting on successful login
            RateLimiter::clear($throttleKey);

            // Log successful API login
            $user->update(['last_login_at' => now()]);

            return $this->successResponse([
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => $tokenResult->token->expires_at,
                'user'         => $this->formatUserProfile($user),
                'api_limits'   => $this->getUserApiLimits($user),
            ]);
        }

        // Increment rate limiter on failed attempt
        RateLimiter::hit($throttleKey, 300); // 5 minutes

        return $this->errorResponse('Invalid credentials', [], 401);
    }

    /**
     * Register new API user
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:255',
            'email'          => 'required|string|email|max:255|unique:users',
            'password'       => 'required|string|min:8|confirmed',
            'company'        => 'nullable|string|max:255',
            'website'        => 'nullable|url',
            'intended_use'   => 'required|string|max:1000',
            'agree_to_terms' => 'required|boolean|accepted',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = User::create([
                'name'               => $request->input('name'),
                'email'              => $request->input('email'),
                'password'           => Hash::make($request->input('password')),
                'company'            => $request->input('company'),
                'website'            => $request->input('website'),
                'intended_use'       => $request->input('intended_use'),
                'api_access_enabled' => TRUE,
                'subscription_plan'  => 'starter', // Default plan
                'terms_accepted_at'  => now(),
            ]);

            // Create initial API key
            $apiKey = $this->createApiKeyForUser($user, 'Default API Key');

            // Create access token
            $tokenResult = $user->createToken('Registration Token', ['*'], now()->addDays(30));

            return $this->successResponse([
                'message'      => 'Registration successful',
                'access_token' => $tokenResult->accessToken,
                'token_type'   => 'Bearer',
                'expires_at'   => $tokenResult->token->expires_at,
                'user'         => $this->formatUserProfile($user),
                'api_key'      => $apiKey,
                'api_limits'   => $this->getUserApiLimits($user),
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Registration failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Logout and revoke tokens
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Revoke current token
            $request->user()->token()->revoke();

            // Optionally revoke all tokens
            if ($request->boolean('revoke_all_tokens', FALSE)) {
                $tokens = $user->tokens;
                foreach ($tokens as $token) {
                    $token->revoke();
                }
            }

            return $this->successResponse([
                'message' => 'Successfully logged out',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Logout failed', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function profile(): JsonResponse
    {
        try {
            $user = Auth::user();

            return $this->successResponse([
                'user'          => $this->formatUserProfile($user),
                'api_limits'    => $this->getUserApiLimits($user),
                'current_usage' => $this->getCurrentUsage($user),
                'api_keys'      => $user->apiKeys()->select(['id', 'name', 'last_used_at', 'is_active'])->get(),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve profile', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'                     => 'string|max:255',
            'company'                  => 'nullable|string|max:255',
            'website'                  => 'nullable|url',
            'notification_preferences' => 'array',
            'timezone'                 => 'string|timezone',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();
            $user->update($request->only([
                'name', 'company', 'website', 'notification_preferences', 'timezone',
            ]));

            return $this->successResponse([
                'message' => 'Profile updated successfully',
                'user'    => $this->formatUserProfile($user->fresh()),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to update profile', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate new API key
     */
    public function createApiKey(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'permissions'   => 'array',
            'permissions.*' => 'string|in:read,write,admin',
            'expires_at'    => 'nullable|date|after:now',
            'rate_limit'    => 'nullable|integer|min:1|max:10000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();

            // Check API key limit based on subscription
            $maxKeys = $this->getMaxApiKeys($user);
            if ($user->apiKeys()->count() >= $maxKeys) {
                return $this->errorResponse('API key limit reached', [
                    'max_keys'     => $maxKeys,
                    'current_plan' => $user->subscription_plan,
                ], 403);
            }

            $apiKey = $this->createApiKeyForUser($user, $request->input('name'), [
                'permissions' => $request->input('permissions', ['read']),
                'expires_at'  => $request->input('expires_at'),
                'rate_limit'  => $request->input('rate_limit'),
            ]);

            return $this->successResponse([
                'message' => 'API key generated successfully',
                'api_key' => $apiKey,
                'warning' => 'Store this key securely. It will not be shown again.',
            ], 201);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to generate API key', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List user's API keys
     */
    public function listApiKeys(): JsonResponse
    {
        try {
            $user = Auth::user();
            $apiKeys = $user->apiKeys()->select([
                'id', 'name', 'permissions', 'last_used_at',
                'expires_at', 'is_active', 'rate_limit', 'created_at',
            ])->get();

            return $this->successResponse([
                'api_keys'   => $apiKeys,
                'total_keys' => $apiKeys->count(),
                'max_keys'   => $this->getMaxApiKeys($user),
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve API keys', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Revoke API key
     */
    public function revokeApiKey(Request $request, string $keyId): JsonResponse
    {
        try {
            $user = Auth::user();
            $apiKey = $user->apiKeys()->findOrFail($keyId);

            $apiKey->update(['is_active' => FALSE, 'revoked_at' => now()]);

            return $this->successResponse([
                'message' => 'API key revoked successfully',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to revoke API key', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get API usage statistics
     */
    public function usageStats(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'period'    => 'string|in:24h,7d,30d,90d',
            'breakdown' => 'string|in:hourly,daily,weekly',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', $validator->errors(), 422);
        }

        try {
            $user = Auth::user();
            $period = $request->input('period', '7d');
            $breakdown = $request->input('breakdown', 'daily');

            $stats = $this->calculateUsageStats($user, $period, $breakdown);

            return $this->successResponse($stats);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to retrieve usage stats', ['error' => $e->getMessage()], 500);
        }
    }

    // Protected helper methods

    protected function successResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success'   => TRUE,
            'data'      => $data,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    protected function errorResponse(string $message, array $errors = [], int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'success'   => FALSE,
            'message'   => $message,
            'errors'    => $errors,
            'timestamp' => now()->toISOString(),
        ], $statusCode);
    }

    private function formatUserProfile(User $user): array
    {
        return [
            'id'                 => $user->id,
            'name'               => $user->name,
            'email'              => $user->email,
            'company'            => $user->company,
            'website'            => $user->website,
            'subscription_plan'  => $user->subscription_plan,
            'api_access_enabled' => $user->api_access_enabled,
            'timezone'           => $user->timezone ?? 'UTC',
            'created_at'         => $user->created_at,
            'last_login_at'      => $user->last_login_at,
        ];
    }

    private function getUserApiLimits(User $user): array
    {
        return match ($user->subscription_plan) {
            'starter' => [
                'requests_per_hour' => 100,
                'requests_per_day'  => 1000,
                'max_api_keys'      => 2,
                'webhook_endpoints' => 1,
                'rate_limit_reset'  => 'hourly',
            ],
            'pro' => [
                'requests_per_hour' => 1000,
                'requests_per_day'  => 10000,
                'max_api_keys'      => 10,
                'webhook_endpoints' => 5,
                'rate_limit_reset'  => 'hourly',
            ],
            'enterprise' => [
                'requests_per_hour' => 10000,
                'requests_per_day'  => 100000,
                'max_api_keys'      => 50,
                'webhook_endpoints' => 25,
                'rate_limit_reset'  => 'per_minute',
            ],
            default => [
                'requests_per_hour' => 50,
                'requests_per_day'  => 500,
                'max_api_keys'      => 1,
                'webhook_endpoints' => 0,
                'rate_limit_reset'  => 'hourly',
            ],
        };
    }

    private function getCurrentUsage(User $user): array
    {
        // Implementation would calculate current API usage
        return [
            'requests_this_hour'  => 0,
            'requests_today'      => 0,
            'requests_this_month' => 0,
            'bandwidth_used_mb'   => 0,
            'last_request_at'     => NULL,
        ];
    }

    private function getMaxApiKeys(User $user): int
    {
        return $this->getUserApiLimits($user)['max_api_keys'];
    }

    private function createApiKeyForUser(User $user, string $name, array $options = []): array
    {
        $key = 'hdt_' . Str::random(32);
        $hashedKey = hash('sha256', $key);

        $apiKey = ApiKey::create([
            'user_id'     => $user->id,
            'name'        => $name,
            'key_hash'    => $hashedKey,
            'permissions' => $options['permissions'] ?? ['read'],
            'expires_at'  => $options['expires_at'],
            'rate_limit'  => $options['rate_limit'],
            'is_active'   => TRUE,
        ]);

        return [
            'id'          => $apiKey->id,
            'name'        => $apiKey->name,
            'key'         => $key, // Only returned once
            'permissions' => $apiKey->permissions,
            'expires_at'  => $apiKey->expires_at,
            'rate_limit'  => $apiKey->rate_limit,
            'created_at'  => $apiKey->created_at,
        ];
    }

    private function calculateUsageStats(User $user, string $period, string $breakdown): array
    {
        // Implementation would calculate detailed usage statistics
        return [
            'period'              => $period,
            'breakdown'           => $breakdown,
            'total_requests'      => 0,
            'successful_requests' => 0,
            'failed_requests'     => 0,
            'avg_response_time'   => 0,
            'data_points'         => [],
            'top_endpoints'       => [],
            'error_breakdown'     => [],
        ];
    }
}
