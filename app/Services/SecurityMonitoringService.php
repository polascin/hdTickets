<?php

namespace App\Services;

use App\Models\User;
use App\Models\SecurityEvent;
use App\Models\LoginAttempt;
use App\Models\TrustedDevice;
use App\Models\SecurityIncident;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Security Monitoring & Audit Service
 * 
 * Provides comprehensive security monitoring including:
 * - Real-time threat detection and analysis
 * - Security event logging and correlation
 * - Automated incident response
 * - Audit trail management
 * - Security metrics and reporting
 * - Anomaly detection and alerting
 */
class SecurityMonitoringService
{
    protected $threatLevels = [
        'low' => 1,
        'medium' => 2,
        'high' => 3,
        'critical' => 4
    ];

    protected $securityRules = [
        'failed_login_threshold' => 5,
        'login_rate_limit' => 10, // per minute
        'suspicious_ip_threshold' => 3,
        'account_lockout_duration' => 30, // minutes
        'incident_escalation_threshold' => 3,
        'anomaly_detection_window' => 24, // hours
    ];

    public function __construct()
    {
        $this->securityRules = array_merge(
            $this->securityRules,
            config('security.monitoring', [])
        );
    }

    /**
     * Log security event and perform threat analysis
     *
     * @param string $eventType
     * @param User|null $user
     * @param Request $request
     * @param array $data
     * @return SecurityEvent
     */
    public function logSecurityEvent(
        string $eventType, 
        ?User $user, 
        Request $request, 
        array $data = []
    ): SecurityEvent {
        $ipAddress = $this->getClientIp($request);
        $userAgent = $request->header('User-Agent');
        $location = $this->getLocationFromIp($ipAddress);

        // Create security event
        $event = SecurityEvent::create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'severity' => $this->calculateEventSeverity($eventType, $data),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'location' => $location,
            'event_data' => $data,
            'request_data' => $this->sanitizeRequestData($request),
            'session_id' => session()->getId(),
            'occurred_at' => now()
        ]);

        // Perform real-time threat analysis
        $this->analyzeThreat($event, $user, $request);

        // Update security metrics
        $this->updateSecurityMetrics($eventType, $event);

        // Log to system log for backup
        Log::channel('security')->info("Security Event: {$eventType}", [
            'event_id' => $event->id,
            'user_id' => $user?->id,
            'ip' => $ipAddress,
            'severity' => $event->severity
        ]);

        return $event;
    }

    /**
     * Analyze threat level and trigger automated responses
     *
     * @param SecurityEvent $event
     * @param User|null $user
     * @param Request $request
     */
    protected function analyzeThreat(SecurityEvent $event, ?User $user, Request $request): void
    {
        $threatScore = $this->calculateThreatScore($event, $user, $request);
        $event->update(['threat_score' => $threatScore]);

        // Check for suspicious patterns
        $this->detectSuspiciousPatterns($event, $user);

        // Automated threat response
        if ($threatScore >= 70) {
            $this->triggerAutomatedResponse($event, $user, $request);
        }

        // Check for incident escalation
        if ($threatScore >= 80) {
            $this->createSecurityIncident($event, $user);
        }
    }

    /**
     * Calculate threat score based on multiple factors
     *
     * @param SecurityEvent $event
     * @param User|null $user
     * @param Request $request
     * @return int
     */
    protected function calculateThreatScore(SecurityEvent $event, ?User $user, Request $request): int
    {
        $score = 0;

        // Base score by event type
        $baseScores = [
            'login_failed' => 20,
            'multiple_failed_logins' => 40,
            'suspicious_login' => 60,
            'brute_force_detected' => 80,
            'account_takeover_attempt' => 90,
            'unauthorized_access_attempt' => 70,
            'privilege_escalation_attempt' => 85,
            'data_breach_attempt' => 95,
            'malicious_request' => 75,
            'bot_detected' => 50,
            'rate_limit_exceeded' => 30,
        ];

        $score += $baseScores[$event->event_type] ?? 10;

        // IP reputation score
        $score += $this->getIpReputationScore($event->ip_address);

        // Geographic anomaly
        if ($user && $this->isGeographicAnomaly($user, $event->location)) {
            $score += 25;
        }

        // Time-based anomaly
        if ($user && $this->isTimeBasedAnomaly($user, $event->occurred_at)) {
            $score += 15;
        }

        // Device fingerprint anomaly
        if ($user && !$this->isTrustedDevice($user, $request)) {
            $score += 20;
        }

        // Recent failed attempts from same IP
        $recentFailures = $this->getRecentFailuresFromIp($event->ip_address);
        $score += min($recentFailures * 5, 30);

        // User risk factors
        if ($user) {
            $score += $this->calculateUserRiskScore($user);
        }

        return min($score, 100); // Cap at 100
    }

    /**
     * Detect suspicious patterns and anomalies
     *
     * @param SecurityEvent $event
     * @param User|null $user
     */
    protected function detectSuspiciousPatterns(SecurityEvent $event, ?User $user): void
    {
        // Pattern 1: Multiple failed logins from same IP
        if ($this->detectBruteForcePattern($event->ip_address)) {
            $this->logSecurityEvent('brute_force_detected', $user, request(), [
                'source_ip' => $event->ip_address,
                'failed_attempts' => $this->getRecentFailuresFromIp($event->ip_address)
            ]);
        }

        // Pattern 2: Login attempts from multiple countries
        if ($user && $this->detectDistributedLoginPattern($user)) {
            $this->logSecurityEvent('distributed_login_attempt', $user, request(), [
                'locations' => $this->getRecentLoginLocations($user)
            ]);
        }

        // Pattern 3: Rapid succession of different attack types
        if ($this->detectCoordinatedAttackPattern($event->ip_address)) {
            $this->logSecurityEvent('coordinated_attack_detected', $user, request(), [
                'attack_vectors' => $this->getRecentAttackVectors($event->ip_address)
            ]);
        }

        // Pattern 4: Account enumeration attempts
        if ($this->detectAccountEnumerationPattern($event->ip_address)) {
            $this->logSecurityEvent('account_enumeration_detected', null, request(), [
                'source_ip' => $event->ip_address,
                'attempted_accounts' => $this->getAccountEnumerationAttempts($event->ip_address)
            ]);
        }
    }

    /**
     * Trigger automated security responses
     *
     * @param SecurityEvent $event
     * @param User|null $user
     * @param Request $request
     */
    protected function triggerAutomatedResponse(SecurityEvent $event, ?User $user, Request $request): void
    {
        $responses = [];

        // High threat score responses
        if ($event->threat_score >= 80) {
            // Temporarily block IP
            $this->blockIpAddress($event->ip_address, 60); // 60 minutes
            $responses[] = 'ip_blocked';

            // Lock user account if applicable
            if ($user && $event->threat_score >= 85) {
                $this->lockUserAccount($user, 'Security threat detected');
                $responses[] = 'account_locked';
            }

            // Send security alert
            $this->sendSecurityAlert($event, $user);
            $responses[] = 'alert_sent';
        }

        // Medium threat score responses
        if ($event->threat_score >= 60) {
            // Increase monitoring for IP
            $this->increaseIpMonitoring($event->ip_address);
            $responses[] = 'monitoring_increased';

            // Require additional authentication
            if ($user) {
                $this->requireAdditionalAuth($user);
                $responses[] = 'additional_auth_required';
            }
        }

        // Log automated responses
        if (!empty($responses)) {
            $this->logSecurityEvent('automated_response_triggered', $user, $request, [
                'responses' => $responses,
                'threat_score' => $event->threat_score
            ]);
        }
    }

    /**
     * Create security incident for high-risk events
     *
     * @param SecurityEvent $event
     * @param User|null $user
     */
    protected function createSecurityIncident(SecurityEvent $event, ?User $user): void
    {
        $incident = SecurityIncident::create([
            'title' => $this->generateIncidentTitle($event),
            'description' => $this->generateIncidentDescription($event),
            'severity' => $this->mapThreatScoreToSeverity($event->threat_score),
            'status' => 'open',
            'priority' => $event->threat_score >= 90 ? 'critical' : 'high',
            'affected_user_id' => $user?->id,
            'source_ip' => $event->ip_address,
            'detection_method' => 'automated',
            'incident_data' => [
                'security_event_id' => $event->id,
                'threat_score' => $event->threat_score,
                'event_type' => $event->event_type,
                'automated_responses' => $this->getAutomatedResponses($event)
            ],
            'detected_at' => now(),
            'assigned_to' => $this->getIncidentAssignee($event->threat_score)
        ]);

        // Link event to incident
        $event->update(['incident_id' => $incident->id]);

        // Send incident notification
        $this->sendIncidentNotification($incident);
    }

    /**
     * Log audit event for compliance and tracking
     *
     * @param string $action
     * @param User|null $user
     * @param string|null $resource
     * @param mixed $resourceId
     * @param array $changes
     * @param Request|null $request
     */
    public function logAuditEvent(
        string $action,
        ?User $user,
        ?string $resource = null,
        $resourceId = null,
        array $changes = [],
        ?Request $request = null
    ): AuditLog {
        $request = $request ?? request();
        
        return AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'resource_type' => $resource,
            'resource_id' => $resourceId,
            'changes' => $changes,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $request->header('User-Agent'),
            'session_id' => session()->getId(),
            'performed_at' => now()
        ]);
    }

    /**
     * Generate comprehensive security report
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public function generateSecurityReport(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
                'days' => $startDate->diffInDays($endDate)
            ],
            'overview' => $this->getSecurityOverview($startDate, $endDate),
            'threat_analysis' => $this->getThreatAnalysis($startDate, $endDate),
            'incidents' => $this->getIncidentSummary($startDate, $endDate),
            'user_security' => $this->getUserSecurityMetrics($startDate, $endDate),
            'ip_analysis' => $this->getIpAnalysis($startDate, $endDate),
            'compliance' => $this->getComplianceMetrics($startDate, $endDate),
            'recommendations' => $this->getSecurityRecommendations($startDate, $endDate)
        ];
    }

    /**
     * Get real-time security dashboard data
     *
     * @return array
     */
    public function getSecurityDashboard(): array
    {
        return [
            'current_threats' => $this->getCurrentThreats(),
            'active_incidents' => $this->getActiveIncidents(),
            'recent_events' => $this->getRecentSecurityEvents(),
            'blocked_ips' => $this->getBlockedIps(),
            'locked_accounts' => $this->getLockedAccounts(),
            'system_health' => $this->getSecuritySystemHealth(),
            'threat_map' => $this->getThreatMap(),
            'security_score' => $this->calculateSystemSecurityScore()
        ];
    }

    // Protected helper methods for internal functionality

    protected function calculateEventSeverity(string $eventType, array $data): string
    {
        $severityMap = [
            'login_success' => 'low',
            'login_failed' => 'medium',
            'multiple_failed_logins' => 'high',
            'brute_force_detected' => 'critical',
            'suspicious_login' => 'high',
            'account_takeover_attempt' => 'critical',
            'unauthorized_access_attempt' => 'high',
            'privilege_escalation_attempt' => 'critical',
            'data_breach_attempt' => 'critical'
        ];

        return $severityMap[$eventType] ?? 'medium';
    }

    protected function getClientIp(Request $request): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }

        return $request->ip();
    }

    protected function getLocationFromIp(string $ip): ?array
    {
        // In production, integrate with GeoIP service
        // For now, return mock data
        return [
            'country' => 'Unknown',
            'city' => 'Unknown',
            'latitude' => null,
            'longitude' => null
        ];
    }

    protected function sanitizeRequestData(Request $request): array
    {
        $data = $request->all();
        
        // Remove sensitive data
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'api_key'];
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    protected function getIpReputationScore(string $ip): int
    {
        // Check against known bad IP lists
        $cacheKey = "ip_reputation:{$ip}";
        
        return Cache::remember($cacheKey, 3600, function() use ($ip) {
            // In production, integrate with threat intelligence feeds
            return 0; // Default: no reputation issue
        });
    }

    protected function isGeographicAnomaly(User $user, ?array $location): bool
    {
        if (!$location || !$location['country'] || $location['country'] === 'Unknown') {
            return false;
        }

        // Get user's typical login locations
        $recentLocations = SecurityEvent::where('user_id', $user->id)
            ->where('event_type', 'login_success')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->whereNotNull('location')
            ->pluck('location')
            ->map(function($loc) {
                return is_string($loc) ? json_decode($loc, true) : $loc;
            })
            ->filter()
            ->pluck('country')
            ->unique();

        return $recentLocations->count() > 0 && !$recentLocations->contains($location['country']);
    }

    protected function isTimeBasedAnomaly(User $user, Carbon $timestamp): bool
    {
        $hour = $timestamp->hour;
        
        // Get user's typical login hours
        $recentHours = SecurityEvent::where('user_id', $user->id)
            ->where('event_type', 'login_success')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->get()
            ->pluck('occurred_at')
            ->map(function($time) {
                return $time->hour;
            })
            ->countBy()
            ->sortByDesc();

        // If user has no recent history, not an anomaly
        if ($recentHours->isEmpty()) {
            return false;
        }

        // Check if current hour is significantly different from usual pattern
        $totalLogins = $recentHours->sum();
        $currentHourPercentage = ($recentHours->get($hour, 0) / $totalLogins) * 100;

        return $currentHourPercentage < 5; // Less than 5% of usual login times
    }

    protected function isTrustedDevice(User $user, Request $request): bool
    {
        $deviceFingerprint = $this->generateDeviceFingerprint($request);
        
        return TrustedDevice::where('user_id', $user->id)
            ->where('device_fingerprint', $deviceFingerprint)
            ->where('trusted_until', '>', now())
            ->exists();
    }

    protected function generateDeviceFingerprint(Request $request): string
    {
        return hash('sha256', $request->header('User-Agent') . $request->ip());
    }

    protected function getRecentFailuresFromIp(string $ip): int
    {
        return SecurityEvent::where('ip_address', $ip)
            ->where('event_type', 'login_failed')
            ->where('occurred_at', '>=', now()->subHour())
            ->count();
    }

    protected function calculateUserRiskScore(User $user): int
    {
        $score = 0;

        // Check recent failed logins
        $recentFailures = SecurityEvent::where('user_id', $user->id)
            ->where('event_type', 'login_failed')
            ->where('occurred_at', '>=', now()->subDay())
            ->count();

        $score += min($recentFailures * 5, 20);

        // Check if account is newly created
        if ($user->created_at->isAfter(now()->subWeek())) {
            $score += 10;
        }

        // Check for privilege escalation attempts
        $escalationAttempts = SecurityEvent::where('user_id', $user->id)
            ->where('event_type', 'privilege_escalation_attempt')
            ->where('occurred_at', '>=', now()->subWeek())
            ->count();

        $score += $escalationAttempts * 15;

        return min($score, 30);
    }

    protected function detectBruteForcePattern(string $ip): bool
    {
        $failures = $this->getRecentFailuresFromIp($ip);
        return $failures >= $this->securityRules['failed_login_threshold'];
    }

    protected function detectDistributedLoginPattern(User $user): bool
    {
        $recentLocations = SecurityEvent::where('user_id', $user->id)
            ->whereIn('event_type', ['login_success', 'login_failed'])
            ->where('occurred_at', '>=', now()->subHours(6))
            ->whereNotNull('location')
            ->pluck('location')
            ->map(function($loc) {
                return is_string($loc) ? json_decode($loc, true) : $loc;
            })
            ->filter()
            ->pluck('country')
            ->unique();

        return $recentLocations->count() >= 3;
    }

    protected function detectCoordinatedAttackPattern(string $ip): bool
    {
        $recentEventTypes = SecurityEvent::where('ip_address', $ip)
            ->where('occurred_at', '>=', now()->subHour())
            ->pluck('event_type')
            ->unique();

        return $recentEventTypes->count() >= 3;
    }

    protected function detectAccountEnumerationPattern(string $ip): bool
    {
        $uniqueAttempts = SecurityEvent::where('ip_address', $ip)
            ->where('event_type', 'login_failed')
            ->where('occurred_at', '>=', now()->subHour())
            ->distinct('user_id')
            ->count();

        return $uniqueAttempts >= 10;
    }

    protected function updateSecurityMetrics(string $eventType, SecurityEvent $event): void
    {
        $cacheKey = "security_metrics:" . now()->format('Y-m-d');
        $metrics = Cache::get($cacheKey, []);
        
        $metrics['events'][$eventType] = ($metrics['events'][$eventType] ?? 0) + 1;
        $metrics['total_events'] = ($metrics['total_events'] ?? 0) + 1;
        
        if ($event->threat_score >= 70) {
            $metrics['high_threat_events'] = ($metrics['high_threat_events'] ?? 0) + 1;
        }

        Cache::put($cacheKey, $metrics, now()->addDay());
    }

    // Additional helper methods would be implemented here for:
    // - blockIpAddress()
    // - lockUserAccount() 
    // - sendSecurityAlert()
    // - increaseIpMonitoring()
    // - requireAdditionalAuth()
    // - generateIncidentTitle()
    // - getSecurityOverview()
    // - getCurrentThreats()
    // etc.
}
