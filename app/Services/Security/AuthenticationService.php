<?php declare(strict_types=1);

namespace App\Services\Security;

use App\Models\LoginHistory;
use App\Models\User;
use App\Services\SecurityService;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

use function count;
use function in_array;

class AuthenticationService
{
    protected $securityService;

    protected $agent;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
        $this->agent = new Agent();
    }

    /**
     * Enhanced login with security features
     */
    /**
     * AuthenticateUser
     */
    public function authenticateUser(Request $request, array $credentials, bool $remember = FALSE): array
    {
        $email = $credentials['email'] ?? '';
        $password = $credentials['password'] ?? '';

        // Find user
        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->logFailedAttempt($request, $email, 'user_not_found');

            return ['success' => FALSE, 'reason' => 'invalid_credentials'];
        }

        // Check if account is locked
        if ($this->isAccountLocked($user)) {
            return ['success' => FALSE, 'reason' => 'account_locked'];
        }

        // Verify password
        if (! Hash::check($password, $user->password)) {
            $this->handleFailedLogin($user, $request, 'invalid_password');

            return ['success' => FALSE, 'reason' => 'invalid_credentials'];
        }

        // Check for suspicious login patterns
        $suspiciousFlags = $this->detectSuspiciousActivity($user, $request);
        if (! empty($suspiciousFlags) && in_array('high_risk', $suspiciousFlags, TRUE)) {
            $this->handleSuspiciousLogin($user, $request, $suspiciousFlags);

            return ['success' => FALSE, 'reason' => 'suspicious_activity'];
        }

        // Generate device fingerprint
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        // Check if 2FA is required
        $requires2FA = $user->two_factor_enabled || $this->requiresTwoFactorAuthentication($user, $deviceFingerprint);

        if ($requires2FA) {
            // Store user ID in session for 2FA challenge
            Session::put('2fa_user_id', $user->id);
            Session::put('2fa_remember', $remember);
            Session::put('2fa_device_fingerprint', $deviceFingerprint);

            return [
                'success'            => FALSE,
                'reason'             => 'requires_2fa',
                'user_id'            => $user->id,
                'has_backup_methods' => $this->hasBackupMethods($user),
            ];
        }

        // Complete login
        $this->completeLogin($user, $request, $remember, $deviceFingerprint);

        return ['success' => TRUE, 'user' => $user];
    }

    /**
     * Generate JWT token for API authentication
     */
    /**
     * GenerateJWTToken
     */
    public function generateJWTToken(User $user, array $scopes = []): array
    {
        $payload = [
            'iss'                => config('app.url'),
            'sub'                => $user->id,
            'iat'                => time(),
            'exp'                => time() + (60 * 60 * 24), // 24 hours
            'scopes'             => $scopes,
            'role'               => $user->role,
            'jti'                => Str::uuid()->toString(),
            'device_fingerprint' => Session::get('2fa_device_fingerprint'),
        ];

        $token = JWT::encode($payload, config('app.key'), 'HS256');

        // Store token in cache for validation
        Cache::put("jwt_token:{$payload['jti']}", [
            'user_id'    => $user->id,
            'expires_at' => $payload['exp'],
            'scopes'     => $scopes,
        ], now()->addDay());

        // Log token generation
        $this->securityService->logSecurityActivity('JWT token generated', [
            'token_id'   => $payload['jti'],
            'scopes'     => $scopes,
            'expires_at' => date('Y-m-d H:i:s', $payload['exp']),
        ], $user);

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => 86400,
            'scope'        => implode(' ', $scopes),
            'jti'          => $payload['jti'],
        ];
    }

    /**
     * Validate JWT token
     */
    /**
     * ValidateJWTToken
     */
    public function validateJWTToken(string $token): ?User
    {
        try {
            $decoded = JWT::decode($token, new Key(config('app.key'), 'HS256'));

            // Check if token is revoked
            $tokenData = Cache::get("jwt_token:{$decoded->jti}");
            if (! $tokenData) {
                return NULL;
            }

            // Get user
            $user = User::find($decoded->sub);
            if (! $user || ! $user->is_active) {
                return NULL;
            }

            // Update last activity
            Cache::put("jwt_token:{$decoded->jti}", array_merge($tokenData, [
                'last_used' => time(),
            ]), now()->addDay());

            return $user;
        } catch (Exception $e) {
            return NULL;
        }
    }

    /**
     * Revoke JWT token
     */
    /**
     * RevokeJWTToken
     */
    public function revokeJWTToken(string $jti): bool
    {
        return Cache::forget("jwt_token:{$jti}");
    }

    /**
     * Generate OAuth 2.0 authorization code
     */
    /**
     * GenerateAuthorizationCode
     */
    public function generateAuthorizationCode(User $user, string $clientId, array $scopes, string $redirectUri): string
    {
        $code = Str::random(40);

        Cache::put("oauth_code:{$code}", [
            'user_id'      => $user->id,
            'client_id'    => $clientId,
            'scopes'       => $scopes,
            'redirect_uri' => $redirectUri,
            'expires_at'   => time() + 600, // 10 minutes
        ], now()->addMinutes(10));

        return $code;
    }

    /**
     * Exchange authorization code for access token
     */
    /**
     * ExchangeAuthorizationCode
     */
    public function exchangeAuthorizationCode(string $code, string $clientId, string $redirectUri): ?array
    {
        $codeData = Cache::get("oauth_code:{$code}");

        if (! $codeData
            || $codeData['client_id'] !== $clientId
            || $codeData['redirect_uri'] !== $redirectUri
            || $codeData['expires_at'] < time()) {
            return NULL;
        }

        // Remove used code
        Cache::forget("oauth_code:{$code}");

        $user = User::find($codeData['user_id']);
        if (! $user) {
            return NULL;
        }

        return $this->generateJWTToken($user, $codeData['scopes']);
    }

    /**
     * Generate device fingerprint
     */
    /**
     * GenerateDeviceFingerprint
     */
    public function generateDeviceFingerprint(Request $request): string
    {
        $this->agent->setUserAgent($request->userAgent());

        $components = [
            $request->ip(),
            $this->agent->browser(),
            $this->agent->platform(),
            $this->agent->device(),
            $request->header('Accept-Language', ''),
            $request->header('Accept-Encoding', ''),
        ];

        return hash('sha256', implode('|', $components));
    }

    /**
     * Biometric authentication support
     */
    /**
     * InitiateBiometricAuth
     */
    public function initiateBiometricAuth(User $user, Request $request): array
    {
        $challenge = Str::random(64);

        Cache::put("biometric_challenge:{$user->id}", [
            'challenge'          => $challenge,
            'device_fingerprint' => $this->generateDeviceFingerprint($request),
            'expires_at'         => time() + 300, // 5 minutes
        ], now()->addMinutes(5));

        return [
            'challenge'  => $challenge,
            'expires_in' => 300,
        ];
    }

    /**
     * Verify biometric authentication
     */
    /**
     * VerifyBiometricAuth
     */
    public function verifyBiometricAuth(User $user, string $challenge, string $signature, Request $request): bool
    {
        $challengeData = Cache::get("biometric_challenge:{$user->id}");

        if (! $challengeData
            || $challengeData['challenge'] !== $challenge
            || $challengeData['expires_at'] < time()
            || $challengeData['device_fingerprint'] !== $this->generateDeviceFingerprint($request)) {
            return FALSE;
        }

        // In a real implementation, verify the biometric signature
        // For now, we'll simulate verification
        $isValid = $this->verifyBiometricSignature($user, $challenge, $signature);

        if ($isValid) {
            Cache::forget("biometric_challenge:{$user->id}");

            // Log successful biometric auth
            $this->securityService->logSecurityActivity('Biometric authentication successful', [
                'method' => 'fingerprint', // or 'face_id', etc.
            ], $user);
        }

        return $isValid;
    }

    /**
     * Detect anomalous login patterns
     */
    /**
     * DetectLoginAnomaly
     */
    public function detectLoginAnomaly(User $user, Request $request): array
    {
        $anomalies = [];
        $currentTime = now();

        // Check for unusual time patterns
        $recentLogins = LoginHistory::where('user_id', $user->id)
            ->where('success', TRUE)
            ->where('attempted_at', '>=', now()->subDays(30))
            ->get();

        if ($recentLogins->isNotEmpty()) {
            $usualHours = $recentLogins->pluck('attempted_at')
                ->map(fn ($date) => $date->hour)
                ->unique()
                ->values();

            $currentHour = $currentTime->hour;
            $timeDifference = $usualHours->map(fn ($hour) => abs($hour - $currentHour))->min();

            if ($timeDifference >= 6) {
                $anomalies[] = 'unusual_time';
            }
        }

        // Check for rapid successive logins
        $rapidLogins = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', now()->subMinutes(5))
            ->count();

        if ($rapidLogins >= 3) {
            $anomalies[] = 'rapid_logins';
        }

        // Check for new geolocation
        $locationInfo = $this->getLocationInfo($request->ip());
        if ($locationInfo['country']) {
            $hasLoginFromCountry = LoginHistory::where('user_id', $user->id)
                ->where('country', $locationInfo['country'])
                ->where('success', TRUE)
                ->exists();

            if (! $hasLoginFromCountry) {
                $anomalies[] = 'new_location';
            }
        }

        // Velocity checks - multiple locations in short time
        $recentLocations = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', now()->subHours(6))
            ->whereNotNull('country')
            ->distinct('country')
            ->count();

        if ($recentLocations >= 3) {
            $anomalies[] = 'impossible_travel';
        }

        return $anomalies;
    }

    /**
     * Session management with Redis
     */
    /**
     * CreateSecureSession
     */
    public function createSecureSession(User $user, Request $request, array $options = []): string
    {
        $sessionId = Str::uuid()->toString();
        $deviceFingerprint = $this->generateDeviceFingerprint($request);

        $sessionData = [
            'user_id'            => $user->id,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'device_fingerprint' => $deviceFingerprint,
            'created_at'         => time(),
            'last_activity'      => time(),
            'expires_at'         => time() + (config('session.lifetime') * 60),
            'is_trusted'         => $options['trusted'] ?? FALSE,
            'security_level'     => $options['security_level'] ?? 'standard',
        ];

        // Store in Redis
        Cache::put("session:{$sessionId}", $sessionData, now()->addMinutes(config('session.lifetime')));

        // Track active sessions
        $userSessions = Cache::get("user_sessions:{$user->id}", []);
        $userSessions[] = $sessionId;

        // Limit concurrent sessions
        $maxSessions = config('security.session.max_concurrent_sessions', 3);
        if (count($userSessions) > $maxSessions) {
            // Remove oldest session
            $oldestSession = array_shift($userSessions);
            Cache::forget("session:{$oldestSession}");
        }

        Cache::put("user_sessions:{$user->id}", $userSessions, now()->addDay());

        return $sessionId;
    }

    /**
     * Validate session with security checks
     */
    /**
     * ValidateSession
     */
    public function validateSession(string $sessionId, Request $request): ?User
    {
        $sessionData = Cache::get("session:{$sessionId}");

        if (! $sessionData || $sessionData['expires_at'] < time()) {
            return NULL;
        }

        // Validate device fingerprint
        if (config('security.session.fingerprint_validation')) {
            $currentFingerprint = $this->generateDeviceFingerprint($request);
            if ($sessionData['device_fingerprint'] !== $currentFingerprint) {
                Cache::forget("session:{$sessionId}");

                return NULL;
            }
        }

        // Validate IP if required
        if (config('security.session.ip_validation')) {
            if ($sessionData['ip_address'] !== $request->ip()) {
                Cache::forget("session:{$sessionId}");

                return NULL;
            }
        }

        // Update last activity
        $sessionData['last_activity'] = time();
        $sessionData['expires_at'] = time() + (config('session.lifetime') * 60);
        Cache::put("session:{$sessionId}", $sessionData, now()->addMinutes(config('session.lifetime')));

        return User::find($sessionData['user_id']);
    }

    /**
     * Check if user requires 2FA based on risk assessment
     */
    /**
     * RequiresTwoFactorAuthentication
     */
    protected function requiresTwoFactorAuthentication(User $user, string $deviceFingerprint): bool
    {
        // Always require for admin users
        if ($user->isAdmin()) {
            return TRUE;
        }

        // Check if device is trusted
        $trustedDevices = $user->trusted_devices ?? [];
        foreach ($trustedDevices as $device) {
            if (isset($device['fingerprint']) && $device['fingerprint'] === $deviceFingerprint) {
                return FALSE;
            }
        }

        // Check security configuration
        return config('security.two_factor.required_for_purchase', FALSE);
    }

    /**
     * Complete login process
     */
    /**
     * CompleteLogin
     */
    protected function completeLogin(User $user, Request $request, bool $remember, string $deviceFingerprint): void
    {
        // Update user login information
        $user->update([
            'last_login_at'         => now(),
            'last_login_ip'         => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
            'failed_login_attempts' => 0,
            'locked_until'          => NULL,
        ]);
        $user->increment('login_count');

        // Log successful login
        $this->securityService->logLoginAttempt($user, $request, TRUE);

        // Create session
        $this->securityService->createOrUpdateSession($user, $request);

        // Authenticate user
        Auth::login($user, $remember);

        // Regenerate session ID for security
        if (config('security.session.regenerate_on_login')) {
            Session::regenerate();
        }
    }

    /**
     * Handle failed login attempt
     */
    /**
     * HandleFailedLogin
     */
    protected function handleFailedLogin(User $user, Request $request, string $reason): void
    {
        $user->increment('failed_login_attempts');

        // Lock account after threshold
        $maxAttempts = config('security.logging.alert_thresholds.failed_logins', 5);
        if ($user->failed_login_attempts >= $maxAttempts) {
            $lockDuration = config('security.account_lockout.duration_minutes', 15);
            $user->update(['locked_until' => now()->addMinutes($lockDuration)]);

            $this->securityService->logSecurityActivity('Account locked due to failed login attempts', [
                'attempts' => $user->failed_login_attempts,
                'reason'   => $reason,
            ], $user);
        }

        // Log failed attempt
        $this->securityService->logLoginAttempt($user, $request, FALSE, $reason);
    }

    /**
     * Check if account is locked
     */
    /**
     * Check if  account locked
     */
    protected function isAccountLocked(User $user): bool
    {
        return $user->locked_until && $user->locked_until->isFuture();
    }

    /**
     * Get location information from IP
     */
    /**
     * Get  location info
     */
    protected function getLocationInfo(string $ipAddress): array
    {
        // For localhost/development
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'], TRUE)) {
            return [
                'country'   => 'Local',
                'city'      => 'Development',
                'latitude'  => NULL,
                'longitude' => NULL,
            ];
        }

        // In production, integrate with GeoIP service
        return [
            'country'   => NULL,
            'city'      => NULL,
            'latitude'  => NULL,
            'longitude' => NULL,
        ];
    }

    /**
     * Detect suspicious activity patterns
     */
    /**
     * DetectSuspiciousActivity
     */
    protected function detectSuspiciousActivity(User $user, Request $request): array
    {
        $flags = [];

        // Check for brute force patterns
        $recentFailedAttempts = LoginHistory::where('user_id', $user->id)
            ->where('success', FALSE)
            ->where('attempted_at', '>=', now()->subHour())
            ->count();

        if ($recentFailedAttempts >= 3) {
            $flags[] = 'brute_force';
        }

        // Check for bot-like behavior
        $userAgent = $request->userAgent();
        if (empty($userAgent) || $this->isSuspiciousUserAgent($userAgent)) {
            $flags[] = 'suspicious_user_agent';
        }

        // Check for proxy/VPN usage
        if ($this->isProxyOrVPN($request->ip())) {
            $flags[] = 'proxy_usage';
        }

        return $flags;
    }

    /**
     * Check if user agent is suspicious
     */
    /**
     * Check if  suspicious user agent
     */
    protected function isSuspiciousUserAgent(string $userAgent): bool
    {
        $suspiciousPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/python/i',
            '/curl/i',
            '/wget/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Check if IP is proxy or VPN
     */
    /**
     * Check if  proxy or v p n
     */
    protected function isProxyOrVPN(string $ipAddress): bool
    {
        // In production, integrate with proxy detection service
        return FALSE;
    }

    /**
     * Handle suspicious login
     */
    /**
     * HandleSuspiciousLogin
     */
    protected function handleSuspiciousLogin(User $user, Request $request, array $flags): void
    {
        $this->securityService->logSecurityActivity('Suspicious login attempt blocked', [
            'flags'      => $flags,
            'risk_level' => 'high',
        ], $user);

        // Optionally lock account or require additional verification
        if (in_array('brute_force', $flags, TRUE)) {
            $user->update(['locked_until' => now()->addMinutes(30)]);
        }
    }

    /**
     * Log failed attempt without user
     */
    /**
     * LogFailedAttempt
     */
    protected function logFailedAttempt(Request $request, string $email, string $reason): void
    {
        $this->securityService->logSecurityActivity('Failed login attempt', [
            'email'      => $email,
            'reason'     => $reason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Check if user has backup authentication methods
     */
    /**
     * Check if has  backup methods
     */
    protected function hasBackupMethods(User $user): array
    {
        return [
            'sms'            => ! empty($user->phone),
            'email'          => ! empty($user->email),
            'recovery_codes' => $user->two_factor_recovery_codes !== NULL,
        ];
    }

    /**
     * Verify biometric signature (placeholder)
     */
    /**
     * VerifyBiometricSignature
     */
    protected function verifyBiometricSignature(User $user, string $challenge, string $signature): bool
    {
        // In a real implementation, verify the biometric signature
        // This would involve cryptographic verification of the biometric data
        return TRUE; // Placeholder
    }
}
