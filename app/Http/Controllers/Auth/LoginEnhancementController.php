<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class LoginEnhancementController extends Controller
{
    /**
     * Check if email exists in the system
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');
        
        // Rate limit email checking to prevent enumeration
        $key = 'email_check:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return response()->json([
                'error' => 'Too many requests'
            ], 429);
        }
        
        RateLimiter::hit($key, 300); // 5 minutes

        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'exists' => false,
                'message' => 'Email not found'
            ]);
        }

        // Return user preferences without sensitive data
        return response()->json([
            'exists' => true,
            'preferences' => [
                'theme' => $user->preferences['theme'] ?? 'light',
                'language' => $user->preferences['language'] ?? 'en',
                'timezone' => $user->preferences['timezone'] ?? 'UTC',
                'two_factor_enabled' => !empty($user->two_factor_secret),
                'last_login' => $user->last_login_at?->diffForHumans(),
            ]
        ]);
    }

    /**
     * Get session status for authenticated users
     */
    public function getSessionStatus(Request $request): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'authenticated' => false
            ], 401);
        }

        $sessionLifetime = config('session.lifetime') * 60; // Convert to seconds
        $lastActivity = $request->session()->get('last_activity', time());
        $timeRemaining = $sessionLifetime - (time() - $lastActivity);
        $expiresIn = max(0, $timeRemaining);

        return response()->json([
            'authenticated' => true,
            'session_lifetime' => $sessionLifetime,
            'time_remaining' => $expiresIn,
            'expires_soon' => $expiresIn < 300, // 5 minutes
            'expires_at' => now()->addSeconds($expiresIn)->toISOString(),
            'user' => [
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'role' => auth()->user()->role,
            ]
        ]);
    }

    /**
     * Validate password strength
     */
    public function validatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $password = $request->input('password');
        
        // Rate limit password validation
        $key = 'password_validate:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 20)) {
            return response()->json([
                'error' => 'Too many requests'
            ], 429);
        }
        
        RateLimiter::hit($key, 60);

        $score = $this->calculatePasswordStrength($password);

        return response()->json([
            'strength' => $score['strength'],
            'score' => $score['score'],
            'checks' => $score['checks'],
            'suggestions' => $score['suggestions']
        ]);
    }

    /**
     * Get security info for user education
     */
    public function getSecurityInfo(): JsonResponse
    {
        return response()->json([
            'tips' => [
                'Use a unique password for your HD Tickets account',
                'Enable two-factor authentication for added security',
                'Never share your login credentials with anyone',
                'Log out when using shared or public computers',
                'Keep your browser and operating system updated'
            ],
            'features' => [
                'Advanced encryption protects your data',
                'Automatic session timeout prevents unauthorized access',
                'Failed login attempt monitoring',
                'Suspicious activity detection',
                'Secure password reset process'
            ],
            'requirements' => [
                'Minimum 8 characters',
                'Mix of uppercase and lowercase letters',
                'At least one number',
                'At least one special character'
            ]
        ]);
    }

    /**
     * Log security event
     */
    public function logSecurityEvent(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|in:suspicious_activity,automation_detected,unusual_location,failed_biometric',
            'details' => 'sometimes|array'
        ]);

        $eventData = [
            'type' => $request->input('event_type'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'details' => $request->input('details', []),
            'timestamp' => now()->toISOString(),
        ];

        // Store in cache for security monitoring
        $key = 'security_events:' . $request->ip();
        $events = Cache::get($key, []);
        $events[] = $eventData;
        
        // Keep only last 50 events
        $events = array_slice($events, -50);
        Cache::put($key, $events, now()->addDay());

        \Log::warning('Security event logged', $eventData);

        return response()->json(['status' => 'logged']);
    }

    /**
     * Calculate password strength
     */
    private function calculatePasswordStrength(string $password): array
    {
        $score = 0;
        $suggestions = [];
        
        $checks = [
            'length' => strlen($password) >= 8,
            'uppercase' => preg_match('/[A-Z]/', $password),
            'lowercase' => preg_match('/[a-z]/', $password),
            'numbers' => preg_match('/\d/', $password),
            'symbols' => preg_match('/[^A-Za-z0-9]/', $password),
            'no_common_patterns' => !$this->hasCommonPatterns($password)
        ];

        $score = array_sum($checks);

        // Generate suggestions
        if (!$checks['length']) {
            $suggestions[] = 'Use at least 8 characters';
        }
        if (!$checks['uppercase']) {
            $suggestions[] = 'Add uppercase letters (A-Z)';
        }
        if (!$checks['lowercase']) {
            $suggestions[] = 'Add lowercase letters (a-z)';
        }
        if (!$checks['numbers']) {
            $suggestions[] = 'Add numbers (0-9)';
        }
        if (!$checks['symbols']) {
            $suggestions[] = 'Add special characters (!@#$%^&*)';
        }
        if (!$checks['no_common_patterns']) {
            $suggestions[] = 'Avoid common patterns like "123" or "abc"';
        }

        $strength = $score <= 2 ? 'weak' : ($score <= 4 ? 'medium' : 'strong');

        return [
            'score' => $score,
            'strength' => $strength,
            'checks' => $checks,
            'suggestions' => $suggestions
        ];
    }

    /**
     * Check for common password patterns
     */
    private function hasCommonPatterns(string $password): bool
    {
        $patterns = [
            '/123/', '/abc/', '/qwe/', '/password/', '/admin/',
            '/111/', '/000/', '/aaa/', '/zzz/', '/888/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, strtolower($password))) {
                return true;
            }
        }

        return false;
    }
}
