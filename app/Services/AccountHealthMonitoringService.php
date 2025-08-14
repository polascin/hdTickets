<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function count;
use function in_array;
use function strlen;

/**
 * Account Health Monitoring Service
 *
 * Monitors account health, validates credentials, detects suspicious activities,
 * and manages account security for the sports events monitoring system.
 */
class AccountHealthMonitoringService
{
    protected $securityService;

    protected $encryptionService;

    public function __construct(SecurityService $securityService, EncryptionService $encryptionService)
    {
        $this->securityService = $securityService;
        $this->encryptionService = $encryptionService;
    }

    /**
     * Perform comprehensive account health check
     *
     * @return array Health status report
     */
    /**
     * PerformHealthCheck
     */
    public function performHealthCheck(User $user): array
    {
        $healthReport = [
            'user_id'         => $user->id,
            'username'        => $user->username,
            'overall_status'  => 'healthy',
            'risk_level'      => 'low',
            'checks'          => [],
            'issues'          => [],
            'recommendations' => [],
            'last_checked'    => now(),
        ];

        // Basic account validation
        $healthReport['checks']['basic_validation'] = $this->validateBasicAccount($user);

        // Login pattern analysis
        $healthReport['checks']['login_patterns'] = $this->analyzeLoginPatterns($user);

        // Failed authentication attempts
        $healthReport['checks']['failed_attempts'] = $this->checkFailedAttempts($user);

        // Account activity analysis
        $healthReport['checks']['activity_analysis'] = $this->analyzeAccountActivity($user);

        // Session validation
        $healthReport['checks']['session_validation'] = $this->validateUserSessions($user);

        // Permission consistency check
        $healthReport['checks']['permission_check'] = $this->checkPermissionConsistency($user);

        // Security settings validation
        $healthReport['checks']['security_settings'] = $this->validateSecuritySettings($user);

        // Data integrity check for encrypted fields
        $healthReport['checks']['data_integrity'] = $this->checkDataIntegrity($user);

        // Calculate overall health status
        $healthReport = $this->calculateOverallHealth($healthReport);

        // Cache the health report
        $this->cacheHealthReport($user->id, $healthReport);

        // Log health check
        $this->logHealthCheck($user, $healthReport);

        return $healthReport;
    }

    /**
     * Perform bulk health checks
     */
    /**
     * PerformBulkHealthChecks
     */
    public function performBulkHealthChecks(?Collection $users = NULL): array
    {
        if ($users === NULL) {
            $users = User::where('is_active', TRUE)->get();
        }

        $results = [
            'total_users'     => $users->count(),
            'healthy'         => 0,
            'at_risk'         => 0,
            'unhealthy'       => 0,
            'high_risk_users' => [],
            'summary'         => [],
        ];

        foreach ($users as $user) {
            $healthReport = $this->performHealthCheck($user);

            switch ($healthReport['overall_status']) {
                case 'healthy':
                    $results['healthy']++;

                    break;
                case 'at_risk':
                case 'cautionary':
                    $results['at_risk']++;

                    break;
                case 'unhealthy':
                    $results['unhealthy']++;
                    if ($healthReport['risk_level'] === 'high') {
                        $results['high_risk_users'][] = [
                            'user_id'  => $user->id,
                            'username' => $user->username,
                            'issues'   => $healthReport['failed_checks'] + $healthReport['warning_checks'],
                            'score'    => $healthReport['overall_score'],
                        ];
                    }

                    break;
            }
        }

        // Generate summary report
        $results['summary'] = [
            'health_percentage'            => ($results['healthy'] / $results['total_users']) * 100,
            'risk_percentage'              => (($results['at_risk'] + $results['unhealthy']) / $results['total_users']) * 100,
            'immediate_attention_required' => count($results['high_risk_users']),
        ];

        return $results;
    }

    /**
     * Get cached health report
     */
    /**
     * Get  cached health report
     */
    public function getCachedHealthReport(int $userId): ?array
    {
        $cacheKey = "account_health_{$userId}";

        return Cache::get($cacheKey);
    }

    /**
     * Validate basic account information
     */
    /**
     * ValidateBasicAccount
     */
    protected function validateBasicAccount(User $user): array
    {
        $checks = [
            'email_valid'      => filter_var($user->email, FILTER_VALIDATE_EMAIL) !== FALSE,
            'username_valid'   => ! empty($user->username) && strlen($user->username) >= 3,
            'profile_complete' => $this->isProfileComplete($user),
            'account_verified' => $user->email_verified_at !== NULL,
            'account_active'   => $user->is_active ?? TRUE,
            'role_valid'       => in_array($user->role, ['root_admin', 'admin', 'agent', 'customer', 'scraper'], TRUE),
        ];

        $status = array_sum($checks) === count($checks) ? 'passed' : 'warning';

        return [
            'status'  => $status,
            'details' => $checks,
            'score'   => (array_sum($checks) / count($checks)) * 100,
        ];
    }

    /**
     * Analyze login patterns for anomalies
     */
    /**
     * AnalyzeLoginPatterns
     */
    protected function analyzeLoginPatterns(User $user): array
    {
        $recentLogins = DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->where('log_name', 'authentication')
            ->where('description', 'like', '%login%')
            ->where('created_at', '>=', now()->subDays(30))
            ->orderBy('created_at', 'desc')
            ->get();

        $patterns = [
            'total_logins'         => $recentLogins->count(),
            'unique_ips'           => $recentLogins->pluck('properties.ip_address')->unique()->count(),
            'unusual_hours'        => 0,
            'geographic_anomalies' => 0,
            'device_changes'       => 0,
        ];

        // Analyze login times
        $normalHours = collect(range(8, 18)); // 8 AM to 6 PM
        $patterns['unusual_hours'] = $recentLogins->filter(function ($login) use ($normalHours) {
            $hour = Carbon::parse($login->created_at)->hour;

            return ! $normalHours->contains($hour);
        })->count();

        // Check for multiple IPs (potential account sharing)
        $ipCount = $patterns['unique_ips'];
        $suspiciousIpActivity = $ipCount > 5 || ($ipCount > 2 && $patterns['total_logins'] < 10);

        $status = 'passed';
        if ($patterns['unusual_hours'] > 10 || $suspiciousIpActivity) {
            $status = 'warning';
        }
        if ($patterns['unusual_hours'] > 20 || $ipCount > 10) {
            $status = 'failed';
        }

        return [
            'status'  => $status,
            'details' => $patterns,
            'score'   => max(0, 100 - ($patterns['unusual_hours'] * 2) - ($ipCount > 5 ? 20 : 0)),
        ];
    }

    /**
     * Check failed authentication attempts
     */
    /**
     * CheckFailedAttempts
     */
    protected function checkFailedAttempts(User $user): array
    {
        $failedAttempts = DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->where('log_name', 'authentication')
            ->where('description', 'like', '%failed%')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $recentFailures = DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->where('log_name', 'authentication')
            ->where('description', 'like', '%failed%')
            ->where('created_at', '>=', now()->subHours(1))
            ->count();

        $threshold = config('security.failed_login_threshold', 5);

        $status = 'passed';
        if ($failedAttempts >= $threshold) {
            $status = 'warning';
        }
        if ($recentFailures >= 3 || $failedAttempts >= ($threshold * 2)) {
            $status = 'failed';
        }

        return [
            'status'  => $status,
            'details' => [
                'failed_attempts_week' => $failedAttempts,
                'failed_attempts_hour' => $recentFailures,
                'threshold'            => $threshold,
            ],
            'score' => max(0, 100 - ($failedAttempts * 10) - ($recentFailures * 20)),
        ];
    }

    /**
     * Analyze account activity patterns
     */
    /**
     * AnalyzeAccountActivity
     */
    protected function analyzeAccountActivity(User $user): array
    {
        $activities = DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $analysis = [
            'total_activities' => $activities->count(),
            'daily_average'    => $activities->count() / 30,
            'activity_types'   => $activities->groupBy('log_name')->map->count()->toArray(),
            'recent_activity'  => $activities->where('created_at', '>=', now()->subDays(7))->count(),
            'inactive_days'    => $this->calculateInactiveDays($user),
        ];

        // Determine if activity patterns are normal
        $expectedDailyActivity = $this->getExpectedActivityLevel($user);
        $activityScore = min(100, ($analysis['daily_average'] / $expectedDailyActivity) * 100);

        $status = 'passed';
        if ($analysis['inactive_days'] > 7 || $activityScore < 50) {
            $status = 'warning';
        }
        if ($analysis['inactive_days'] > 14 || $activityScore < 25) {
            $status = 'failed';
        }

        return [
            'status'  => $status,
            'details' => $analysis,
            'score'   => $activityScore,
        ];
    }

    /**
     * Validate user sessions
     */
    /**
     * ValidateUserSessions
     */
    protected function validateUserSessions(User $user): array
    {
        // Check for active sessions
        $activeSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subHours(24)->timestamp)
            ->count();

        $concurrentSessions = DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
            ->count();

        $status = 'passed';
        if ($concurrentSessions > 3) {
            $status = 'warning';
        }
        if ($concurrentSessions > 5) {
            $status = 'failed';
        }

        return [
            'status'  => $status,
            'details' => [
                'active_sessions_24h' => $activeSessions,
                'concurrent_sessions' => $concurrentSessions,
            ],
            'score' => max(0, 100 - ($concurrentSessions > 3 ? ($concurrentSessions - 3) * 15 : 0)),
        ];
    }

    /**
     * Check permission consistency
     */
    /**
     * CheckPermissionConsistency
     */
    protected function checkPermissionConsistency(User $user): array
    {
        $permissionChecks = [
            'role_permissions_match' => $this->validateRolePermissions($user),
            'elevated_permissions'   => $this->checkElevatedPermissions($user),
            'permission_changes'     => $this->checkRecentPermissionChanges($user),
        ];

        $allPassed = array_sum($permissionChecks) === count($permissionChecks);

        return [
            'status'  => $allPassed ? 'passed' : 'warning',
            'details' => $permissionChecks,
            'score'   => (array_sum($permissionChecks) / count($permissionChecks)) * 100,
        ];
    }

    /**
     * Validate security settings
     */
    /**
     * ValidateSecuritySettings
     */
    protected function validateSecuritySettings(User $user): array
    {
        $securityChecks = [
            'strong_password'         => $this->hasStrongPassword($user),
            'two_factor_enabled'      => $user->two_factor_secret !== NULL,
            'recent_password_change'  => $this->hasRecentPasswordChange($user),
            'secure_session_settings' => TRUE, // Based on app configuration
        ];

        $score = (array_sum($securityChecks) / count($securityChecks)) * 100;
        $status = $score >= 75 ? 'passed' : ($score >= 50 ? 'warning' : 'failed');

        return [
            'status'  => $status,
            'details' => $securityChecks,
            'score'   => $score,
        ];
    }

    /**
     * Check data integrity for encrypted fields
     */
    /**
     * CheckDataIntegrity
     */
    protected function checkDataIntegrity(User $user): array
    {
        $integrityChecks = [
            'encrypted_fields_valid' => TRUE,
            'data_consistency'       => TRUE,
            'backup_codes_valid'     => TRUE,
        ];

        // Try to decrypt encrypted fields to check integrity
        try {
            if ($user->hasEncryptedAttributes()) {
                $decrypted = $user->getDecryptedAttributes();
                $integrityChecks['encrypted_fields_valid'] = $decrypted !== NULL;
            }
        } catch (Exception $e) {
            $integrityChecks['encrypted_fields_valid'] = FALSE;
            Log::warning('Data integrity check failed for user', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        $score = (array_sum($integrityChecks) / count($integrityChecks)) * 100;

        return [
            'status'  => $score === 100 ? 'passed' : 'warning',
            'details' => $integrityChecks,
            'score'   => $score,
        ];
    }

    /**
     * Calculate overall health status
     */
    /**
     * CalculateOverallHealth
     */
    protected function calculateOverallHealth(array $healthReport): array
    {
        $scores = array_column($healthReport['checks'], 'score');
        $averageScore = collect($scores)->average();

        $failedChecks = collect($healthReport['checks'])->where('status', 'failed')->count();
        $warningChecks = collect($healthReport['checks'])->where('status', 'warning')->count();

        // Determine overall status
        if ($failedChecks > 0) {
            $healthReport['overall_status'] = 'unhealthy';
            $healthReport['risk_level'] = 'high';
        } elseif ($warningChecks > 2) {
            $healthReport['overall_status'] = 'at_risk';
            $healthReport['risk_level'] = 'medium';
        } elseif ($warningChecks > 0) {
            $healthReport['overall_status'] = 'cautionary';
            $healthReport['risk_level'] = 'low';
        }

        $healthReport['overall_score'] = $averageScore;
        $healthReport['failed_checks'] = $failedChecks;
        $healthReport['warning_checks'] = $warningChecks;

        // Generate recommendations
        $healthReport['recommendations'] = $this->generateRecommendations($healthReport);

        return $healthReport;
    }

    /**
     * Generate health recommendations
     */
    /**
     * GenerateRecommendations
     */
    protected function generateRecommendations(array $healthReport): array
    {
        $recommendations = [];

        foreach ($healthReport['checks'] as $checkName => $checkResult) {
            if ($checkResult['status'] === 'failed' || $checkResult['status'] === 'warning') {
                $recommendations = array_merge($recommendations, $this->getCheckRecommendations($checkName, $checkResult));
            }
        }

        return array_unique($recommendations);
    }

    /**
     * Get recommendations for specific check
     */
    /**
     * Get  check recommendations
     */
    protected function getCheckRecommendations(string $checkName, array $checkResult): array
    {
        $recommendations = [];

        switch ($checkName) {
            case 'basic_validation':
                if (! $checkResult['details']['email_valid']) {
                    $recommendations[] = 'Update email address to a valid format';
                }
                if (! $checkResult['details']['account_verified']) {
                    $recommendations[] = 'Verify email address';
                }
                if (! $checkResult['details']['profile_complete']) {
                    $recommendations[] = 'Complete user profile information';
                }

                break;
            case 'login_patterns':
                if ($checkResult['details']['unique_ips'] > 5) {
                    $recommendations[] = 'Review recent login locations for unauthorized access';
                }
                if ($checkResult['details']['unusual_hours'] > 10) {
                    $recommendations[] = 'Consider enabling login notifications for unusual access times';
                }

                break;
            case 'failed_attempts':
                if ($checkResult['details']['failed_attempts_week'] > 5) {
                    $recommendations[] = 'Consider enabling account lockout after failed attempts';
                    $recommendations[] = 'Review and strengthen password';
                }

                break;
            case 'security_settings':
                if (! $checkResult['details']['two_factor_enabled']) {
                    $recommendations[] = 'Enable two-factor authentication for additional security';
                }
                if (! $checkResult['details']['strong_password']) {
                    $recommendations[] = 'Update to a stronger password';
                }
                if (! $checkResult['details']['recent_password_change']) {
                    $recommendations[] = 'Consider changing password periodically';
                }

                break;
            case 'session_validation':
                if ($checkResult['details']['concurrent_sessions'] > 3) {
                    $recommendations[] = 'Review active sessions and log out unused sessions';
                }

                break;
        }

        return $recommendations;
    }

    /**
     * Cache health report
     */
    /**
     * CacheHealthReport
     */
    protected function cacheHealthReport(int $userId, array $healthReport): void
    {
        $cacheKey = "account_health_{$userId}";
        Cache::put($cacheKey, $healthReport, now()->addHours(6));
    }

    /**
     * Log health check
     */
    /**
     * LogHealthCheck
     */
    protected function logHealthCheck(User $user, array $healthReport): void
    {
        $this->securityService->logSecurityActivity(
            'Account health check performed',
            [
                'overall_status' => $healthReport['overall_status'],
                'risk_level'     => $healthReport['risk_level'],
                'overall_score'  => $healthReport['overall_score'],
                'failed_checks'  => $healthReport['failed_checks'],
                'warning_checks' => $healthReport['warning_checks'],
            ],
            $user,
        );
    }

    // Helper methods
    /**
     * Check if  profile complete
     */
    protected function isProfileComplete(User $user): bool
    {
        return ! empty($user->email) && ! empty($user->username) && ! empty($user->role);
    }

    /**
     * CalculateInactiveDays
     */
    protected function calculateInactiveDays(User $user): int
    {
        $lastActivity = DB::table('activity_log')
            ->where('causer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastActivity) {
            return 999; // Never active
        }

        return Carbon::parse($lastActivity->created_at)->diffInDays(now());
    }

    /**
     * Get  expected activity level
     */
    protected function getExpectedActivityLevel(User $user): float
    {
        return match ($user->role) {
            'root_admin', 'admin' => 15.0,
            'agent'    => 10.0,
            'customer' => 5.0,
            'scraper'  => 20.0,
            default    => 5.0,
        };
    }

    /**
     * ValidateRolePermissions
     */
    protected function validateRolePermissions(User $user): bool
    {
        // Check if user's permissions match their role
        return TRUE; // Implement role-specific permission validation
    }

    /**
     * CheckElevatedPermissions
     */
    protected function checkElevatedPermissions(User $user): bool
    {
        // Check for any elevated permissions that shouldn't be there
        return TRUE; // Implement elevated permission check
    }

    /**
     * CheckRecentPermissionChanges
     */
    protected function checkRecentPermissionChanges(User $user): bool
    {
        $recentChanges = DB::table('activity_log')
            ->where('subject_id', $user->id)
            ->where('description', 'like', '%permission%')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return $recentChanges <= 2; // No more than 2 permission changes per month
    }

    /**
     * Check if has  strong password
     */
    protected function hasStrongPassword(User $user): bool
    {
        // This would need access to password history or strength validation
        return TRUE; // Implement password strength validation
    }

    /**
     * Check if has  recent password change
     */
    protected function hasRecentPasswordChange(User $user): bool
    {
        return $user->password_changed_at === NULL
               || Carbon::parse($user->password_changed_at)->diffInDays(now()) <= 90;
    }
}
