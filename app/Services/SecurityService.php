<?php declare(strict_types=1);

namespace App\Services;

use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Spatie\Activitylog\Models\Activity;

use function array_slice;
use function count;
use function in_array;

class SecurityService
{
    // ===== LOGIN HISTORY & SESSION MANAGEMENT =====

    protected $agent;

    /**
     * Log security-related activities with enhanced context
     *
     * @param mixed|null $subject
     */
    /**
     * LogSecurityActivity
     *
     * @param mixed $subject
     */
    public function logSecurityActivity(string $description, array $properties = [], ?Model $subject = NULL): void
    {
        $user = Auth::user();
        $request = request();

        $enhancedProperties = array_merge([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toISOString(),
            'session_id' => session()->getId(),
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'user_id'    => $user?->id,
            'user_role'  => $user?->role,
            'risk_level' => $this->calculateRiskLevel($description),
        ], $properties);

        activity('security')
            ->causedBy($user)
            ->performedOn($subject)
            ->withProperties($enhancedProperties)
            ->log($description);
    }

    /**
     * Log user actions with context
     *
     * @param mixed|null $subject
     */
    /**
     * LogUserActivity
     *
     * @param mixed $subject
     */
    public function logUserActivity(string $action, array $context = [], ?Model $subject = NULL): void
    {
        $user = Auth::user();
        $request = request();

        $properties = array_merge([
            'action'     => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toISOString(),
            'session_id' => session()->getId(),
            'user_role'  => $user?->role,
        ], $context);

        activity('user_actions')
            ->causedBy($user)
            ->performedOn($subject)
            ->withProperties($properties)
            ->log("User performed action: {$action}");
    }

    /**
     * Log bulk operations with detailed tracking
     */
    /**
     * LogBulkOperation
     */
    public function logBulkOperation(string $operation, array $items, array $results = []): void
    {
        $user = Auth::user();

        $properties = [
            'operation'      => $operation,
            'item_count'     => count($items),
            'item_ids'       => array_slice($items, 0, 100), // Limit to prevent too large logs
            'success_count'  => $results['success'] ?? 0,
            'failure_count'  => $results['failure'] ?? 0,
            'errors'         => $results['errors'] ?? [],
            'execution_time' => $results['execution_time'] ?? NULL,
            'user_role'      => $user?->role,
        ];

        activity('bulk_operations')
            ->causedBy($user)
            ->withProperties($properties)
            ->log("Bulk operation performed: {$operation} on " . count($items) . ' items');
    }

    /**
     * Log authentication events
     */
    /**
     * LogAuthEvent
     */
    public function logAuthEvent(string $event, array $context = []): void
    {
        $request = request();

        $properties = array_merge([
            'event'      => $event,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toISOString(),
            'session_id' => session()->getId(),
        ], $context);

        activity('authentication')
            ->withProperties($properties)
            ->log("Authentication event: {$event}");
    }

    /**
     * Check if user has permission for action with logging
     */
    /**
     * CheckPermission
     */
    public function checkPermission(User $user, string $permission, array $context = []): bool
    {
        $hasPermission = match ($permission) {
            'manage_users'            => $user->canManageUsers(),
            'manage_system'           => $user->canManageSystem(),
            'manage_platforms'        => $user->canManagePlatforms(),
            'access_financials'       => $user->canAccessFinancials(),
            'delete_any_data'         => $user->canDeleteAnyData(),
            'select_purchase_tickets' => $user->canSelectAndPurchaseTickets(),
            'make_purchase_decisions' => $user->canMakePurchaseDecisions(),
            'manage_monitoring'       => $user->canManageMonitoring(),
            'view_scraping_metrics'   => $user->canViewScrapingMetrics(),
            'access_system'           => $user->canAccessSystem(),
            'bulk_operations'         => $this->canPerformBulkOperations($user),
            default                   => FALSE,
        };

        if (!$hasPermission) {
            $this->logSecurityActivity(
                'Permission denied',
                array_merge([
                    'permission'       => $permission,
                    'user_role'        => $user->role,
                    'attempted_action' => $context['action'] ?? 'unknown',
                ], $context),
            );
        }

        return $hasPermission;
    }

    /**
     * Check if user can perform bulk operations
     */
    /**
     * Check if can  perform bulk operations
     */
    public function canPerformBulkOperations(User $user): bool
    {
        if ($user->isAdmin()) {
            return TRUE;
        }

        return $user->isAgent();
    }

    /**
     * Validate bulk operation security
     */
    /**
     * ValidateBulkOperation
     */
    public function validateBulkOperation(array $items, string $operation, User $user): array
    {
        $validation = [
            'valid'    => TRUE,
            'errors'   => [],
            'warnings' => [],
        ];

        // Check item count limits
        $maxItems = $this->getMaxBulkItems($user, $operation);
        if (count($items) > $maxItems) {
            $validation['valid'] = FALSE;
            $validation['errors'][] = "Bulk operation exceeds maximum limit of {$maxItems} items";
        }

        // Check for destructive operations
        if (in_array($operation, ['delete', 'disable', 'remove'], TRUE) && (!$user->canDeleteAnyData() && count($items) > 10)) {
            $validation['valid'] = FALSE;
            $validation['errors'][] = 'Destructive bulk operations limited to 10 items for non-root users';
        }

        // Rate limiting check
        if ($this->isBulkOperationRateLimited($user)) {
            $validation['valid'] = FALSE;
            $validation['errors'][] = 'Bulk operation rate limit exceeded. Please wait before trying again.';
        }

        return $validation;
    }

    /**
     * Generate secure CSRF token for bulk operations
     */
    /**
     * GenerateBulkOperationToken
     */
    public function generateBulkOperationToken(string $operation, array $items): string
    {
        $payload = [
            'operation'  => $operation,
            'item_count' => count($items),
            'user_id'    => Auth::id(),
            'timestamp'  => now()->timestamp,
            'nonce'      => bin2hex(random_bytes(16)),
        ];

        return Hash::make(json_encode($payload));
    }

    /**
     * Validate bulk operation token
     */
    /**
     * ValidateBulkOperationToken
     */
    public function validateBulkOperationToken(string $token, string $operation, array $items): bool
    {
        $payload = [
            'operation'  => $operation,
            'item_count' => count($items),
            'user_id'    => Auth::id(),
            'timestamp'  => now()->timestamp,
        ];

        // Allow 5 minute window for token validity
        for ($i = 0; $i < 5; $i++) {
            $testPayload = $payload;
            $testPayload['timestamp'] -= ($i * 60);

            if (Hash::check(json_encode($testPayload), $token)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Get recent security activities
     */
    /**
     * Get  recent security activities
     */
    public function getRecentSecurityActivities(int $limit = 50): Collection
    {
        return Activity::where('log_name', 'security')
            ->with('causer')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity summary
     */
    /**
     * Get  user activity summary
     */
    public function getUserActivitySummary(User $user, int $days = 30): array
    {
        $activities = Activity::where('causer_id', $user->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        return [
            'total_activities'      => $activities->count(),
            'security_events'       => $activities->where('log_name', 'security')->count(),
            'user_actions'          => $activities->where('log_name', 'user_actions')->count(),
            'bulk_operations'       => $activities->where('log_name', 'bulk_operations')->count(),
            'authentication_events' => $activities->where('log_name', 'authentication')->count(),
            'recent_activities'     => $activities->sortByDesc('created_at')->take(10)->values(),
        ];
    }

    /**
     * InitAgent
     */
    public function initAgent(): void
    {
        if (!$this->agent) {
            $this->agent = new Agent();
        }
    }

    /**
     * Log login attempt and track security information
     */
    /**
     * LogLoginAttempt
     */
    public function logLoginAttempt(User $user, Request $request, bool $success, ?string $failureReason = NULL): LoginHistory
    {
        $this->initAgent();
        $deviceInfo = $this->getDeviceInfo($request);
        $locationInfo = $this->getLocationInfo($request->ip());
        $suspiciousFlags = $this->analyzeSuspiciousActivity($user, $request, $deviceInfo, $locationInfo);

        return LoginHistory::create([
            'user_id'          => $user->id,
            'ip_address'       => $request->ip(),
            'user_agent'       => $request->userAgent(),
            'device_type'      => $deviceInfo['device_type'],
            'browser'          => $deviceInfo['browser'],
            'operating_system' => $deviceInfo['os'],
            'country'          => $locationInfo['country'],
            'city'             => $locationInfo['city'],
            'latitude'         => $locationInfo['latitude'],
            'longitude'        => $locationInfo['longitude'],
            'success'          => $success,
            'failure_reason'   => $failureReason,
            'is_suspicious'    => count($suspiciousFlags) > 0,
            'suspicious_flags' => $suspiciousFlags,
            'session_id'       => Session::getId(),
            'attempted_at'     => now(),
        ]);
    }

    /**
     * Create or update user session
     */
    /**
     * CreateOrUpdateSession
     */
    public function createOrUpdateSession(User $user, Request $request): UserSession
    {
        $this->initAgent();
        $sessionId = Session::getId();
        $deviceInfo = $this->getDeviceInfo($request);
        $locationInfo = $this->getLocationInfo($request->ip());

        // Mark all previous sessions as non-current for this user
        UserSession::where('user_id', $user->id)->update(['is_current' => FALSE]);

        return UserSession::updateOrCreate(
            ['id' => $sessionId],
            [
                'user_id'          => $user->id,
                'ip_address'       => $request->ip(),
                'user_agent'       => $request->userAgent(),
                'device_type'      => $deviceInfo['device_type'],
                'browser'          => $deviceInfo['browser'],
                'operating_system' => $deviceInfo['os'],
                'country'          => $locationInfo['country'],
                'city'             => $locationInfo['city'],
                'is_current'       => TRUE,
                'is_trusted'       => $this->isDeviceTrusted($user, $deviceInfo, $request->ip()),
                'last_activity'    => now(),
                'expires_at'       => now()->addMinutes(config('session.lifetime')),
            ],
        );
    }

    /**
     * Update session activity
     */
    /**
     * UpdateSessionActivity
     */
    public function updateSessionActivity(string $sessionId): void
    {
        UserSession::where('id', $sessionId)->update([
            'last_activity' => now(),
            'expires_at'    => now()->addMinutes(config('session.lifetime')),
        ]);
    }

    /**
     * Revoke session
     */
    /**
     * RevokeSession
     */
    public function revokeSession(string $sessionId): bool
    {
        return UserSession::where('id', $sessionId)->delete() > 0;
    }

    /**
     * Revoke all sessions for user except current
     */
    /**
     * RevokeAllOtherSessions
     */
    public function revokeAllOtherSessions(User $user, ?string $exceptSessionId = NULL): int
    {
        $query = UserSession::where('user_id', $user->id);

        if ($exceptSessionId) {
            $query->where('id', '!=', $exceptSessionId);
        }

        return $query->delete();
    }

    /**
     * Add device to trusted devices
     */
    /**
     * TrustDevice
     */
    public function trustDevice(User $user, Request $request): void
    {
        $this->initAgent();
        $deviceInfo = $this->getDeviceInfo($request);
        $trustedDevices = $user->trusted_devices ?? [];

        $newDevice = [
            'browser'     => $deviceInfo['browser'],
            'os'          => $deviceInfo['os'],
            'ip_address'  => $request->ip(),
            'device_type' => $deviceInfo['device_type'],
            'trusted_at'  => now()->toISOString(),
            'name'        => $this->generateDeviceName($deviceInfo),
        ];

        // Check if device is already trusted
        $exists = collect($trustedDevices)->contains(fn ($device): bool => $device['browser'] === $newDevice['browser']
               && $device['os'] === $newDevice['os']
               && $device['ip_address'] === $newDevice['ip_address']);

        if (!$exists) {
            $trustedDevices[] = $newDevice;
            $user->update(['trusted_devices' => $trustedDevices]);
        }
    }

    /**
     * Remove device from trusted devices
     */
    /**
     * UntrustDevice
     */
    public function untrustDevice(User $user, int $deviceIndex): bool
    {
        $trustedDevices = $user->trusted_devices ?? [];

        if (isset($trustedDevices[$deviceIndex])) {
            unset($trustedDevices[$deviceIndex]);
            $user->update(['trusted_devices' => array_values($trustedDevices)]);

            return TRUE;
        }

        return FALSE;
    }

    /**
     * Get user's login statistics
     */
    /**
     * Get  login statistics
     */
    public function getLoginStatistics(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $totalAttempts = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->count();

        $successfulLogins = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->where('success', TRUE)
            ->count();

        $failedAttempts = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->where('success', FALSE)
            ->count();

        $suspiciousAttempts = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->where('is_suspicious', TRUE)
            ->count();

        $uniqueLocations = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->whereNotNull('country')
            ->distinct('country')
            ->count('country');

        $uniqueDevices = LoginHistory::where('user_id', $user->id)
            ->where('attempted_at', '>=', $startDate)
            ->selectRaw('DISTINCT CONCAT(browser, "-", operating_system, "-", device_type)')
            ->count();

        return [
            'total_attempts'      => $totalAttempts,
            'successful_logins'   => $successfulLogins,
            'failed_attempts'     => $failedAttempts,
            'suspicious_attempts' => $suspiciousAttempts,
            'unique_locations'    => $uniqueLocations,
            'unique_devices'      => $uniqueDevices,
            'success_rate'        => $totalAttempts > 0 ? round(($successfulLogins / $totalAttempts) * 100, 2) : 0,
            'period_days'         => $days,
        ];
    }

    /**
     * Get recent login history for user
     */
    /**
     * Get  recent login history
     */
    public function getRecentLoginHistory(User $user, int $limit = 20): Collection
    {
        return LoginHistory::where('user_id', $user->id)
            ->orderBy('attempted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active sessions for user
     */
    /**
     * Get  active sessions
     */
    public function getActiveSessions(User $user): Collection
    {
        return UserSession::where('user_id', $user->id)
            ->active()
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Perform security checkup for user
     */
    /**
     * PerformSecurityCheckup
     */
    public function performSecurityCheckup(User $user): array
    {
        $issues = [];
        $recommendations = [];

        // Check 2FA status
        if (!$user->two_factor_enabled) {
            $issues[] = [
                'type'        => 'critical',
                'title'       => 'Two-Factor Authentication Disabled',
                'description' => 'Your account is not protected with two-factor authentication.',
                'action'      => 'Enable 2FA',
                'url'         => route('2fa.setup'),
            ];
        }

        // Check password age
        if ($user->password_changed_at && $user->password_changed_at < now()->subMonths(6)) {
            $issues[] = [
                'type'        => 'warning',
                'title'       => 'Password is Old',
                'description' => 'Your password hasn\'t been changed in over 6 months.',
                'action'      => 'Change Password',
                'url'         => route('profile.edit'),
            ];
        }

        // Check for suspicious activity
        $suspiciousCount = LoginHistory::where('user_id', $user->id)
            ->where('is_suspicious', TRUE)
            ->where('attempted_at', '>=', now()->subDays(7))
            ->count();

        if ($suspiciousCount > 0) {
            $issues[] = [
                'type'        => 'warning',
                'title'       => 'Suspicious Activity Detected',
                'description' => "{$suspiciousCount} suspicious login attempts in the last 7 days.",
                'action'      => 'Review Activity',
                'url'         => route('profile.security'),
            ];
        }

        // Check active sessions
        $activeSessions = $this->getActiveSessions($user)->count();
        if ($activeSessions > 5) {
            $recommendations[] = [
                'type'        => 'info',
                'title'       => 'Multiple Active Sessions',
                'description' => "You have {$activeSessions} active sessions. Consider logging out unused sessions.",
                'action'      => 'Manage Sessions',
                'url'         => route('profile.security'),
            ];
        }

        // Check trusted devices
        $trustedDeviceCount = count($user->trusted_devices ?? []);
        if ($trustedDeviceCount === 0) {
            $recommendations[] = [
                'type'        => 'info',
                'title'       => 'No Trusted Devices',
                'description' => 'Consider marking frequently used devices as trusted for convenience.',
                'action'      => 'Manage Devices',
                'url'         => route('profile.security'),
            ];
        }

        $score = $this->calculateSecurityScore($user, $issues);

        return [
            'score'           => $score,
            'issues'          => $issues,
            'recommendations' => $recommendations,
            'total_issues'    => count($issues),
            'critical_issues' => count(array_filter($issues, fn (array $i): bool => $i['type'] === 'critical')),
        ];
    }

    /**
     * Get device information from request
     */
    /**
     * Get  device info
     */
    protected function getDeviceInfo(Request $request): array
    {
        $this->initAgent();
        $this->agent->setUserAgent($request->userAgent());

        $deviceType = 'desktop';
        if ($this->agent->isMobile()) {
            $deviceType = 'mobile';
        } elseif ($this->agent->isTablet()) {
            $deviceType = 'tablet';
        }

        return [
            'device_type' => $deviceType,
            'browser'     => $this->agent->browser(),
            'os'          => $this->agent->platform(),
        ];
    }

    /**
     * Get location information from IP address
     */
    /**
     * Get  location info
     */
    protected function getLocationInfo(string $ipAddress): array
    {
        // For localhost/development, return default values
        if (in_array($ipAddress, ['127.0.0.1', '::1', 'localhost'], TRUE)) {
            return [
                'country'   => 'Local',
                'city'      => 'Development',
                'latitude'  => NULL,
                'longitude' => NULL,
            ];
        }

        // In production, you would integrate with a GeoIP service
        // For now, return default values
        return [
            'country'   => NULL,
            'city'      => NULL,
            'latitude'  => NULL,
            'longitude' => NULL,
        ];
    }

    /**
     * Analyze suspicious activity patterns
     */
    /**
     * AnalyzeSuspiciousActivity
     *
     * @return string[]
     */
    protected function analyzeSuspiciousActivity(User $user, Request $request, array $deviceInfo, array $locationInfo): array
    {
        $flags = [];

        // Check for rapid login attempts
        $recentFailedAttempts = LoginHistory::where('user_id', $user->id)
            ->where('success', FALSE)
            ->where('attempted_at', '>=', now()->subHour())
            ->count();

        if ($recentFailedAttempts >= 3) {
            $flags[] = 'rapid_failed_attempts';
        }

        // Check for new location
        $hasLoginFromLocation = LoginHistory::where('user_id', $user->id)
            ->where('success', TRUE)
            ->where('country', $locationInfo['country'])
            ->exists();

        if ($locationInfo['country'] && !$hasLoginFromLocation) {
            $flags[] = 'new_location';
        }

        // Check for new device
        $hasLoginFromDevice = LoginHistory::where('user_id', $user->id)
            ->where('success', TRUE)
            ->where('browser', $deviceInfo['browser'])
            ->where('operating_system', $deviceInfo['os'])
            ->exists();

        if (!$hasLoginFromDevice) {
            $flags[] = 'new_device';
        }

        // Check for unusual time
        $currentHour = now()->hour;
        $usualLoginTimes = LoginHistory::where('user_id', $user->id)
            ->where('success', TRUE)
            ->get()
            ->map(fn ($record) => $record->attempted_at->hour)
            ->unique();

        if ($usualLoginTimes->isNotEmpty() && !$usualLoginTimes->contains($currentHour)) {
            $timeDifference = $usualLoginTimes->map(fn ($hour): float|int => abs($hour - $currentHour))->min();
            if ($timeDifference >= 6) {
                $flags[] = 'unusual_time';
            }
        }

        return $flags;
    }

    /**
     * Check if device is trusted
     */
    /**
     * Check if  device trusted
     */
    protected function isDeviceTrusted(User $user, array $deviceInfo, string $ipAddress): bool
    {
        $trustedDevices = $user->trusted_devices ?? [];

        foreach ($trustedDevices as $device) {
            if ($device['browser'] === $deviceInfo['browser']
                && $device['os'] === $deviceInfo['os']
                && $device['ip_address'] === $ipAddress) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * Generate a friendly name for the device
     */
    /**
     * GenerateDeviceName
     */
    protected function generateDeviceName(array $deviceInfo): string
    {
        $deviceType = ucfirst((string) $deviceInfo['device_type']);
        $browser = $deviceInfo['browser'];
        $os = $deviceInfo['os'];

        return "{$deviceType} - {$browser} on {$os}";
    }

    /**
     * Calculate security score for user
     */
    /**
     * CalculateSecurityScore
     */
    protected function calculateSecurityScore(User $user, array $issues): int
    {
        $score = 100;

        // Deduct points for issues
        foreach ($issues as $issue) {
            $score -= match ($issue['type']) {
                'critical' => 30,
                'warning'  => 15,
                'info'     => 5,
                default    => 0,
            };
        }

        // Add points for good practices
        if ($user->two_factor_enabled) {
            // Already included in base score
        }

        if ($user->email_verified_at) {
            // Already included in base score
        }

        if (count($user->trusted_devices ?? []) > 0) {
            $score += 5;
        }

        if ($user->password_changed_at && $user->password_changed_at > now()->subMonths(3)) {
            $score += 10;
        }

        return max(0, min(100, $score));
    }

    /**
     * Calculate risk level based on activity
     */
    /**
     * CalculateRiskLevel
     */
    private function calculateRiskLevel(string $description): string
    {
        $highRiskKeywords = ['delete', 'disable', 'permission', 'admin', 'bulk', 'failed'];
        $mediumRiskKeywords = ['update', 'modify', 'change', 'access'];

        $description = strtolower($description);

        foreach ($highRiskKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'high';
            }
        }

        foreach ($mediumRiskKeywords as $keyword) {
            if (str_contains($description, $keyword)) {
                return 'medium';
            }
        }

        return 'low';
    }

    /**
     * Get maximum bulk items based on user role and operation
     */
    /**
     * Get  max bulk items
     */
    private function getMaxBulkItems(User $user, string $operation): int
    {
        if ($user->isRootAdmin()) {
            return 1000;
        }

        if ($user->isAdmin()) {
            return in_array($operation, ['delete', 'disable'], TRUE) ? 100 : 500;
        }

        if ($user->isAgent()) {
            return in_array($operation, ['delete', 'disable'], TRUE) ? 10 : 100;
        }

        return 10;
    }

    /**
     * Check if user is rate limited for bulk operations
     */
    /**
     * Check if  bulk operation rate limited
     */
    private function isBulkOperationRateLimited(User $user): bool
    {
        $recentBulkOps = Activity::where('causer_id', $user->id)
            ->where('log_name', 'bulk_operations')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        $limit = $user->isAdmin() ? 10 : 3;

        return $recentBulkOps >= $limit;
    }
}
