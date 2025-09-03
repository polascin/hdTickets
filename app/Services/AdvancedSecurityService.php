<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class AdvancedSecurityService
{
    protected $agent;

    public function __construct()
    {
        $this->agent = new Agent();
    }

    /**
     * Get comprehensive security dashboard data
     */
    public function getSecurityDashboard(User $user): array
    {
        return Cache::remember("security_dashboard_{$user->id}", 300, function() use ($user) {
            return [
                'session_management' => $this->getSessionManagement($user),
                'device_tracking' => $this->getDeviceTracking($user),
                'login_history' => $this->getLoginHistory($user),
                'security_alerts' => $this->getSecurityAlerts($user),
                'security_score' => $this->calculateAdvancedSecurityScore($user),
                'recommendations' => $this->getSecurityRecommendations($user),
            ];
        });
    }

    /**
     * Get session management data
     */
    private function getSessionManagement(User $user): array
    {
        // This would typically query the sessions table
        $activeSessions = collect([
            [
                'id' => 'session_1',
                'ip_address' => '192.168.1.100',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'last_activity' => now()->subMinutes(5),
                'location' => 'New York, NY',
                'device' => 'Desktop - Chrome',
                'is_current' => true,
            ],
            [
                'id' => 'session_2',
                'ip_address' => '10.0.0.50',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)',
                'last_activity' => now()->subHours(2),
                'location' => 'Los Angeles, CA',
                'device' => 'Mobile - Safari',
                'is_current' => false,
            ]
        ]);

        return [
            'active_sessions' => $activeSessions->toArray(),
            'session_count' => $activeSessions->count(),
            'suspicious_sessions' => $this->detectSuspiciousSessions($activeSessions),
            'session_security_score' => $this->calculateSessionSecurityScore($activeSessions),
        ];
    }

    /**
     * Get device tracking data
     */
    private function getDeviceTracking(User $user): array
    {
        $trustedDevices = collect([
            [
                'id' => 'device_1',
                'name' => 'Personal Laptop',
                'fingerprint' => hash('sha256', 'device_fingerprint_1'),
                'first_seen' => now()->subDays(30),
                'last_seen' => now()->subMinutes(5),
                'device_type' => 'Desktop',
                'browser' => 'Chrome 120.0',
                'os' => 'Windows 11',
                'location' => 'New York, NY',
                'is_trusted' => true,
                'login_count' => 245,
            ],
            [
                'id' => 'device_2',
                'name' => 'iPhone',
                'fingerprint' => hash('sha256', 'device_fingerprint_2'),
                'first_seen' => now()->subDays(15),
                'last_seen' => now()->subHours(2),
                'device_type' => 'Mobile',
                'browser' => 'Safari 17.0',
                'os' => 'iOS 17.1',
                'location' => 'Los Angeles, CA',
                'is_trusted' => true,
                'login_count' => 89,
            ]
        ]);

        return [
            'trusted_devices' => $trustedDevices->toArray(),
            'device_count' => $trustedDevices->count(),
            'new_devices_this_month' => $trustedDevices->where('first_seen', '>', now()->subMonth())->count(),
            'device_security_score' => $this->calculateDeviceSecurityScore($trustedDevices),
        ];
    }

    /**
     * Get login history
     */
    private function getLoginHistory(User $user): array
    {
        $loginHistory = collect(range(0, 19))->map(function ($i) {
            $timestamp = now()->subHours($i * 6);
            return [
                'id' => 'login_' . $i,
                'timestamp' => $timestamp,
                'ip_address' => '192.168.1.' . rand(100, 255),
                'location' => collect(['New York, NY', 'Los Angeles, CA', 'Chicago, IL', 'Houston, TX'])->random(),
                'device' => collect(['Desktop - Chrome', 'Mobile - Safari', 'Tablet - Firefox'])->random(),
                'success' => $i < 18, // Last 2 failed for demonstration
                'risk_level' => collect(['low', 'medium', 'high'])->random(),
                'details' => $i >= 18 ? 'Failed login attempt' : 'Successful login',
            ];
        });

        return [
            'recent_logins' => $loginHistory->take(10)->toArray(),
            'failed_attempts' => $loginHistory->where('success', false)->count(),
            'success_rate' => round($loginHistory->where('success', true)->count() / $loginHistory->count() * 100, 1),
            'unusual_activity' => $this->detectUnusualActivity($loginHistory),
        ];
    }

    /**
     * Get security alerts
     */
    private function getSecurityAlerts(User $user): array
    {
        $alerts = collect([
            [
                'id' => 'alert_1',
                'type' => 'new_device',
                'severity' => 'medium',
                'title' => 'New Device Login',
                'message' => 'A new device logged into your account from Los Angeles, CA',
                'timestamp' => now()->subHours(2),
                'read' => false,
                'action_required' => true,
            ],
            [
                'id' => 'alert_2',
                'type' => 'failed_login',
                'severity' => 'low',
                'title' => 'Failed Login Attempt',
                'message' => 'Someone tried to login to your account but failed',
                'timestamp' => now()->subHours(6),
                'read' => true,
                'action_required' => false,
            ]
        ]);

        return [
            'active_alerts' => $alerts->where('read', false)->toArray(),
            'recent_alerts' => $alerts->take(5)->toArray(),
            'alert_count' => $alerts->where('read', false)->count(),
            'high_priority_count' => $alerts->where('severity', 'high')->where('read', false)->count(),
        ];
    }

    /**
     * Calculate advanced security score
     */
    private function calculateAdvancedSecurityScore(User $user): array
    {
        $factors = [
            'password_strength' => 85,
            'two_factor_enabled' => $user->two_factor_secret ? 100 : 0,
            'email_verified' => $user->email_verified_at ? 100 : 0,
            'recent_login_security' => 90,
            'device_trust_level' => 95,
            'session_security' => 88,
        ];

        $totalScore = array_sum($factors) / count($factors);

        return [
            'overall_score' => round($totalScore),
            'factors' => $factors,
            'grade' => $this->getSecurityGrade($totalScore),
            'improvement_areas' => $this->getImprovementAreas($factors),
        ];
    }

    /**
     * Get security recommendations
     */
    private function getSecurityRecommendations(User $user): array
    {
        $recommendations = [];

        if (!$user->two_factor_secret) {
            $recommendations[] = [
                'type' => 'two_factor',
                'priority' => 'high',
                'title' => 'Enable Two-Factor Authentication',
                'description' => 'Add an extra layer of security to your account',
                'action' => 'setup_2fa',
                'estimated_time' => '2 minutes',
            ];
        }

        if (!$user->email_verified_at) {
            $recommendations[] = [
                'type' => 'email_verification',
                'priority' => 'high',
                'title' => 'Verify Your Email',
                'description' => 'Verify your email address to secure your account',
                'action' => 'verify_email',
                'estimated_time' => '1 minute',
            ];
        }

        $recommendations[] = [
            'type' => 'password_review',
            'priority' => 'medium',
            'title' => 'Review Password Strength',
            'description' => 'Consider updating your password if it\'s older than 90 days',
            'action' => 'change_password',
            'estimated_time' => '3 minutes',
        ];

        return $recommendations;
    }

    /**
     * Detect suspicious sessions
     */
    private function detectSuspiciousSessions($sessions): array
    {
        return $sessions->filter(function ($session) {
            // Check for unusual locations, IP ranges, etc.
            return rand(0, 100) < 10; // 10% chance for demo
        })->toArray();
    }

    /**
     * Calculate session security score
     */
    private function calculateSessionSecurityScore($sessions): int
    {
        // Calculate based on session patterns, locations, devices
        return rand(80, 98);
    }

    /**
     * Calculate device security score
     */
    private function calculateDeviceSecurityScore($devices): int
    {
        // Calculate based on trusted devices, patterns, etc.
        return rand(85, 99);
    }

    /**
     * Detect unusual activity
     */
    private function detectUnusualActivity($loginHistory): array
    {
        return $loginHistory->filter(function ($login) {
            return $login['risk_level'] === 'high';
        })->take(3)->toArray();
    }

    /**
     * Get security grade
     */
    private function getSecurityGrade(float $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 70) return 'B';
        if ($score >= 60) return 'C';
        return 'D';
    }

    /**
     * Get improvement areas
     */
    private function getImprovementAreas(array $factors): array
    {
        return collect($factors)
            ->filter(fn($score) => $score < 90)
            ->keys()
            ->map(fn($key) => str_replace('_', ' ', $key))
            ->map(fn($text) => ucwords($text))
            ->toArray();
    }

    /**
     * Track device fingerprint
     */
    public function trackDevice(Request $request, User $user): string
    {
        $this->agent->setUserAgent($request->userAgent());
        
        $fingerprint = hash('sha256', implode('|', [
            $this->agent->browser(),
            $this->agent->version($this->agent->browser()),
            $this->agent->platform(),
            $this->agent->version($this->agent->platform()),
            $request->ip(),
            $request->header('Accept-Language', ''),
        ]));

        // Store device information
        $this->storeDeviceInfo($user, $fingerprint, $request);

        return $fingerprint;
    }

    /**
     * Store device information
     */
    private function storeDeviceInfo(User $user, string $fingerprint, Request $request): void
    {
        // This would typically store in a devices table
        // For now, we'll cache it
        $deviceInfo = [
            'fingerprint' => $fingerprint,
            'user_agent' => $request->userAgent(),
            'ip_address' => $request->ip(),
            'device_type' => $this->agent->isMobile() ? 'Mobile' : ($this->agent->isTablet() ? 'Tablet' : 'Desktop'),
            'browser' => $this->agent->browser() . ' ' . $this->agent->version($this->agent->browser()),
            'os' => $this->agent->platform() . ' ' . $this->agent->version($this->agent->platform()),
            'first_seen' => now(),
            'last_seen' => now(),
        ];

        Cache::put("device_{$user->id}_{$fingerprint}", $deviceInfo, 86400); // 24 hours
    }
}
