<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\SecurityEvent;
use App\Models\SecurityIncident;
use App\Models\AuditLog;
use App\Models\LoginAttempt;
use App\Models\TrustedDevice;
use App\Models\TwoFactorBackupCode;
use App\Services\SecurityMonitoringService;
use App\Services\EnhancedLoginSecurityService;
use App\Services\MultiFactorAuthService;
use App\Services\AdvancedRBACService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Security Dashboard Controller
 * 
 * Provides comprehensive security management dashboard with:
 * - Real-time security metrics and monitoring
 * - Interactive threat detection demonstration
 * - Security incident management
 * - User security management tools
 * - Security system configuration
 * - Live security event streaming
 */
class SecurityDashboardController extends Controller
{
    protected SecurityMonitoringService $securityMonitoring;
    protected EnhancedLoginSecurityService $loginSecurity;
    protected MultiFactorAuthService $mfaService;
    protected AdvancedRBACService $rbacService;

    public function __construct(
        SecurityMonitoringService $securityMonitoring,
        EnhancedLoginSecurityService $loginSecurity,
        MultiFactorAuthService $mfaService,
        AdvancedRBACService $rbacService
    ) {
        $this->securityMonitoring = $securityMonitoring;
        $this->loginSecurity = $loginSecurity;
        $this->mfaService = $mfaService;
        $this->rbacService = $rbacService;
        
        $this->middleware('auth');
        $this->middleware('role:admin'); // Only admins can access security dashboard
    }

    /**
     * Display main security dashboard
     */
    public function index()
    {
        $dashboardData = $this->getDashboardData();
        
        return view('security.dashboard.index', compact('dashboardData'));
    }

    /**
     * Get comprehensive dashboard data
     */
    protected function getDashboardData(): array
    {
        return [
            'overview' => $this->getSecurityOverview(),
            'threats' => $this->getThreatMetrics(),
            'incidents' => $this->getIncidentMetrics(),
            'users' => $this->getUserSecurityMetrics(),
            'authentication' => $this->getAuthenticationMetrics(),
            'audit' => $this->getAuditMetrics(),
            'system' => $this->getSystemHealthMetrics(),
            'realtime' => $this->getRealtimeData()
        ];
    }

    /**
     * Security overview metrics
     */
    protected function getSecurityOverview(): array
    {
        $now = now();
        $last24h = $now->copy()->subDay();
        $last7d = $now->copy()->subWeek();
        $last30d = $now->copy()->subMonth();

        return [
            'security_score' => $this->calculateSecurityScore(),
            'total_events_24h' => SecurityEvent::where('occurred_at', '>=', $last24h)->count(),
            'high_risk_events_24h' => SecurityEvent::where('occurred_at', '>=', $last24h)
                ->where('threat_score', '>=', 70)->count(),
            'active_incidents' => SecurityIncident::open()->count(),
            'critical_incidents' => SecurityIncident::critical()->open()->count(),
            'blocked_ips_24h' => $this->getBlockedIpsCount($last24h),
            'locked_accounts' => User::where('locked_until', '>', $now)->count(),
            'failed_logins_24h' => SecurityEvent::where('event_type', 'login_failed')
                ->where('occurred_at', '>=', $last24h)->count(),
            'trends' => [
                '24h_vs_prev' => $this->calculateTrend($last24h, $last24h->copy()->subDay()),
                '7d_vs_prev' => $this->calculateTrend($last7d, $last7d->copy()->subWeek()),
                '30d_vs_prev' => $this->calculateTrend($last30d, $last30d->copy()->subMonth())
            ]
        ];
    }

    /**
     * Threat detection metrics
     */
    protected function getThreatMetrics(): array
    {
        $last24h = now()->subDay();
        
        return [
            'threat_distribution' => SecurityEvent::where('occurred_at', '>=', $last24h)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->orderByDesc('count')
                ->get(),
            'threat_scores' => SecurityEvent::where('occurred_at', '>=', $last24h)
                ->whereNotNull('threat_score')
                ->selectRaw('
                    CASE 
                        WHEN threat_score >= 90 THEN "Critical"
                        WHEN threat_score >= 70 THEN "High" 
                        WHEN threat_score >= 40 THEN "Medium"
                        ELSE "Low"
                    END as level,
                    COUNT(*) as count
                ')
                ->groupBy('level')
                ->get(),
            'geographic_threats' => $this->getGeographicThreatData(),
            'attack_patterns' => $this->getAttackPatterns(),
            'ip_reputation' => $this->getIpReputationData()
        ];
    }

    /**
     * Security incident metrics
     */
    protected function getIncidentMetrics(): array
    {
        return [
            'open_incidents' => SecurityIncident::open()->with(['affectedUser', 'assignee'])->get(),
            'recent_incidents' => SecurityIncident::recent(48)->with(['affectedUser', 'assignee'])->get(),
            'incident_distribution' => SecurityIncident::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')->get(),
            'severity_breakdown' => SecurityIncident::selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')->get(),
            'resolution_times' => $this->getIncidentResolutionTimes(),
            'escalation_trends' => $this->getIncidentEscalationTrends()
        ];
    }

    /**
     * User security metrics
     */
    protected function getUserSecurityMetrics(): array
    {
        $totalUsers = User::count();
        
        return [
            'total_users' => $totalUsers,
            'active_users_24h' => User::where('last_login_at', '>=', now()->subDay())->count(),
            'mfa_enabled' => User::where('two_factor_enabled', true)->count(),
            'mfa_adoption_rate' => round((User::where('two_factor_enabled', true)->count() / max($totalUsers, 1)) * 100, 2),
            'trusted_devices' => TrustedDevice::where('trusted_until', '>', now())->count(),
            'locked_accounts' => User::where('locked_until', '>', now())->count(),
            'role_distribution' => User::selectRaw('role, COUNT(*) as count')->groupBy('role')->get(),
            'recent_registrations' => User::where('created_at', '>=', now()->subWeek())->count(),
            'security_alerts' => $this->getUserSecurityAlerts()
        ];
    }

    /**
     * Authentication metrics
     */
    protected function getAuthenticationMetrics(): array
    {
        $last24h = now()->subDay();
        
        return [
            'successful_logins_24h' => SecurityEvent::where('event_type', 'login_successful')
                ->where('occurred_at', '>=', $last24h)->count(),
            'failed_logins_24h' => SecurityEvent::where('event_type', 'login_failed')
                ->where('occurred_at', '>=', $last24h)->count(),
            'mfa_challenges_24h' => SecurityEvent::where('event_type', '2fa_challenged')
                ->where('occurred_at', '>=', $last24h)->count(),
            'device_registrations_24h' => TrustedDevice::where('created_at', '>=', $last24h)->count(),
            'login_trends' => $this->getLoginTrends(),
            'authentication_methods' => $this->getAuthenticationMethods(),
            'geographic_logins' => $this->getGeographicLoginData()
        ];
    }

    /**
     * Audit metrics
     */
    protected function getAuditMetrics(): array
    {
        $last24h = now()->subDay();
        
        return [
            'total_audit_entries_24h' => AuditLog::where('performed_at', '>=', $last24h)->count(),
            'sensitive_actions_24h' => AuditLog::sensitive()
                ->where('performed_at', '>=', $last24h)->count(),
            'most_active_users' => AuditLog::where('performed_at', '>=', $last24h)
                ->with('user')
                ->selectRaw('user_id, COUNT(*) as action_count')
                ->groupBy('user_id')
                ->orderByDesc('action_count')
                ->limit(10)
                ->get(),
            'action_distribution' => AuditLog::where('performed_at', '>=', $last24h)
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderByDesc('count')
                ->get(),
            'compliance_summary' => $this->getComplianceSummary()
        ];
    }

    /**
     * System health metrics
     */
    protected function getSystemHealthMetrics(): array
    {
        return [
            'security_services' => [
                'monitoring' => $this->checkServiceHealth('monitoring'),
                'mfa' => $this->checkServiceHealth('mfa'),
                'login_security' => $this->checkServiceHealth('login_security'),
                'rbac' => $this->checkServiceHealth('rbac'),
                'audit' => $this->checkServiceHealth('audit')
            ],
            'database_health' => $this->getDatabaseHealthMetrics(),
            'cache_health' => $this->getCacheHealthMetrics(),
            'performance_metrics' => $this->getPerformanceMetrics()
        ];
    }

    /**
     * Real-time data for live updates
     */
    protected function getRealtimeData(): array
    {
        return [
            'active_sessions' => $this->getActiveSessionsCount(),
            'current_threats' => $this->getCurrentThreats(),
            'live_events' => $this->getRecentSecurityEvents(50),
            'system_alerts' => $this->getSystemAlerts(),
            'refresh_interval' => config('security.metrics.dashboard_refresh_interval', 30)
        ];
    }

    /**
     * Security incidents management page
     */
    public function incidents()
    {
        $incidents = SecurityIncident::with(['affectedUser', 'assignee', 'securityEvents'])
            ->orderByDesc('detected_at')
            ->paginate(20);

        $stats = [
            'open' => SecurityIncident::open()->count(),
            'critical' => SecurityIncident::critical()->count(),
            'unassigned' => SecurityIncident::unassigned()->count(),
            'resolved_today' => SecurityIncident::where('resolved_at', '>=', now()->startOfDay())->count()
        ];

        return view('security.dashboard.incidents', compact('incidents', 'stats'));
    }

    /**
     * Security events timeline
     */
    public function events(Request $request)
    {
        $query = SecurityEvent::with('user')->orderByDesc('occurred_at');

        // Apply filters
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }
        
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('occurred_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('occurred_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $events = $query->paginate(50);
        
        $eventTypes = SecurityEvent::distinct('event_type')
            ->orderBy('event_type')
            ->pluck('event_type');

        return view('security.dashboard.events', compact('events', 'eventTypes'));
    }

    /**
     * User security management
     */
    public function users()
    {
        $users = User::with(['roles', 'loginAttempts' => function($query) {
            $query->recent(24);
        }])->paginate(20);

        $stats = [
            'total' => User::count(),
            'active_24h' => User::where('last_login_at', '>=', now()->subDay())->count(),
            'mfa_enabled' => User::where('two_factor_enabled', true)->count(),
            'locked' => User::where('locked_until', '>', now())->count()
        ];

        return view('security.dashboard.users', compact('users', 'stats'));
    }

    /**
     * Audit log viewer
     */
    public function audit(Request $request)
    {
        $query = AuditLog::with('user')->orderByDesc('performed_at');

        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('resource_type')) {
            $query->where('resource_type', $request->resource_type);
        }

        if ($request->filled('date_from')) {
            $query->where('performed_at', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->where('performed_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $logs = $query->paginate(50);
        
        $actions = AuditLog::distinct('action')
            ->orderBy('action')
            ->pluck('action');

        $resourceTypes = AuditLog::whereNotNull('resource_type')
            ->distinct('resource_type')
            ->orderBy('resource_type')
            ->pluck('resource_type');

        return view('security.dashboard.audit', compact('logs', 'actions', 'resourceTypes'));
    }

    /**
     * Security configuration management
     */
    public function configuration()
    {
        $config = [
            'monitoring' => config('security.monitoring'),
            'authentication' => config('security.authentication'),
            'rbac' => config('security.rbac'),
            'audit' => config('security.audit'),
            'compliance' => config('security.compliance')
        ];

        return view('security.dashboard.configuration', compact('config'));
    }

    /**
     * Security demo and testing interface
     */
    public function demo()
    {
        $demoData = [
            'mfa_demo' => $this->getMfaDemoData(),
            'threat_simulation' => $this->getThreatSimulationData(),
            'rbac_demo' => $this->getRbacDemoData(),
            'audit_demo' => $this->getAuditDemoData()
        ];

        return view('security.dashboard.demo', compact('demoData'));
    }

    /**
     * API endpoint for real-time dashboard updates
     */
    public function apiDashboardData()
    {
        return response()->json([
            'success' => true,
            'data' => $this->getDashboardData(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * API endpoint for live security events
     */
    public function apiLiveEvents()
    {
        $events = SecurityEvent::with('user')
            ->where('occurred_at', '>=', now()->subMinutes(5))
            ->orderByDesc('occurred_at')
            ->get();

        return response()->json([
            'success' => true,
            'events' => $events,
            'timestamp' => now()->toISOString()
        ]);
    }

    // Protected helper methods for data processing

    protected function calculateSecurityScore(): int
    {
        // Implement comprehensive security score calculation
        $score = 100;
        
        // Deduct for active incidents
        $activeIncidents = SecurityIncident::open()->count();
        $score -= min($activeIncidents * 5, 30);
        
        // Deduct for recent high-risk events
        $highRiskEvents = SecurityEvent::where('occurred_at', '>=', now()->subDay())
            ->where('threat_score', '>=', 70)->count();
        $score -= min($highRiskEvents * 2, 20);
        
        // Deduct for locked accounts
        $lockedAccounts = User::where('locked_until', '>', now())->count();
        $score -= min($lockedAccounts * 3, 15);
        
        // Add for MFA adoption
        $totalUsers = User::count();
        $mfaUsers = User::where('two_factor_enabled', true)->count();
        $mfaRate = $totalUsers > 0 ? ($mfaUsers / $totalUsers) * 100 : 0;
        $score += ($mfaRate - 50) * 0.2; // Bonus/penalty based on 50% baseline
        
        return max(0, min(100, round($score)));
    }

    protected function calculateTrend(Carbon $current, Carbon $previous): array
    {
        $currentCount = SecurityEvent::where('occurred_at', '>=', $current)->count();
        $previousCount = SecurityEvent::whereBetween('occurred_at', [$previous, $current])->count();
        
        $change = $currentCount - $previousCount;
        $percentage = $previousCount > 0 ? round(($change / $previousCount) * 100, 2) : 0;
        
        return [
            'current' => $currentCount,
            'previous' => $previousCount,
            'change' => $change,
            'percentage' => $percentage,
            'trend' => $change >= 0 ? 'up' : 'down'
        ];
    }

    // Additional helper methods would be implemented here...
    protected function getGeographicThreatData(): array { return []; }
    protected function getAttackPatterns(): array { return []; }
    protected function getIpReputationData(): array { return []; }
    protected function getIncidentResolutionTimes(): array { return []; }
    protected function getIncidentEscalationTrends(): array { return []; }
    protected function getUserSecurityAlerts(): array { return []; }
    protected function getLoginTrends(): array { return []; }
    protected function getAuthenticationMethods(): array { return []; }
    protected function getGeographicLoginData(): array { return []; }
    protected function getComplianceSummary(): array { return []; }
    protected function checkServiceHealth(string $service): array { return ['status' => 'healthy']; }
    protected function getDatabaseHealthMetrics(): array { return []; }
    protected function getCacheHealthMetrics(): array { return []; }
    protected function getPerformanceMetrics(): array { return []; }
    protected function getActiveSessionsCount(): int { return 0; }
    protected function getCurrentThreats(): array { return []; }
    protected function getRecentSecurityEvents(int $limit): array { return []; }
    protected function getSystemAlerts(): array { return []; }
    protected function getBlockedIpsCount(Carbon $since): int { return 0; }
    protected function getMfaDemoData(): array { return []; }
    protected function getThreatSimulationData(): array { return []; }
    protected function getRbacDemoData(): array { return []; }
    protected function getAuditDemoData(): array { return []; }
}
