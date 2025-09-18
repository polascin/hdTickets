@extends('layouts.app-v2')

@section('title', 'Security Documentation - HD Tickets')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
<style>
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    .code-block { background-color: #f8f9fa; border-radius: 0.375rem; padding: 1rem; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="{ activeSection: 'overview' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">HD Tickets Security Documentation</h1>
            <p class="mt-2 text-lg text-gray-600">
                Comprehensive guide to the advanced security features and authentication system
            </p>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200 mb-8">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeSection = 'overview'"
                        :class="activeSection === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Overview
                </button>
                <button @click="activeSection = 'mfa'"
                        :class="activeSection === 'mfa' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Multi-Factor Auth
                </button>
                <button @click="activeSection = 'rbac'"
                        :class="activeSection === 'rbac' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    RBAC System
                </button>
                <button @click="activeSection = 'monitoring'"
                        :class="activeSection === 'monitoring' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Security Monitoring
                </button>
                <button @click="activeSection = 'api'"
                        :class="activeSection === 'api' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    API Reference
                </button>
                <button @click="activeSection = 'deployment'"
                        :class="activeSection === 'deployment' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                    Deployment
                </button>
            </nav>
        </div>

        <!-- Overview Section -->
        <div x-show="activeSection === 'overview'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Security System Overview</h2>
                
                <div class="prose max-w-none">
                    <p class="text-lg text-gray-700 mb-6">
                        HD Tickets implements an enterprise-grade security system designed specifically for sports event ticket monitoring and purchasing. 
                        The system provides comprehensive protection against modern security threats while maintaining excellent user experience.
                    </p>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Core Security Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900">Multi-Factor Authentication</h4>
                            <ul class="mt-2 text-sm text-blue-800 space-y-1">
                                <li>• Google Authenticator integration</li>
                                <li>• SMS verification backup</li>
                                <li>• Recovery codes system</li>
                                <li>• Device trust management</li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900">Enhanced Login Security</h4>
                            <ul class="mt-2 text-sm text-green-800 space-y-1">
                                <li>• Device fingerprinting</li>
                                <li>• Geolocation validation</li>
                                <li>• Behavioral analysis</li>
                                <li>• Account lockout protection</li>
                            </ul>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-900">Advanced RBAC</h4>
                            <ul class="mt-2 text-sm text-purple-800 space-y-1">
                                <li>• Hierarchical role system</li>
                                <li>• Granular permissions</li>
                                <li>• Resource-based access</li>
                                <li>• Dynamic permission management</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900">Threat Detection</h4>
                            <ul class="mt-2 text-sm text-red-800 space-y-1">
                                <li>• Real-time threat scoring</li>
                                <li>• Pattern recognition</li>
                                <li>• Automated incident response</li>
                                <li>• Comprehensive audit logging</li>
                            </ul>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Security Architecture</h3>
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <pre class="text-sm text-gray-800"><code>
┌─────────────────────┐
│   Frontend Layer    │
│ - Alpine.js         │
│ - CSRF Protection   │
│ - Input Validation  │
└─────────┬───────────┘
          │
┌─────────▼───────────┐
│  Application Layer  │
│ - Controllers       │
│ - Middleware        │
│ - Form Requests     │
└─────────┬───────────┘
          │
┌─────────▼───────────┐
│   Security Services │
│ - MFA Service       │
│ - RBAC Service      │
│ - Monitor Service   │
│ - Login Security    │
└─────────┬───────────┘
          │
┌─────────▼───────────┐
│    Data Layer       │
│ - Encrypted Storage │
│ - Audit Logs        │
│ - Security Events   │
│ - User Sessions     │
└─────────────────────┘
                        </code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Multi-Factor Authentication Section -->
        <div x-show="activeSection === 'mfa'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Multi-Factor Authentication</h2>
                
                <div class="prose max-w-none">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Setup Process</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">1. Enable MFA for User</h4>
                        <pre><code class="language-php">
use App\Services\MultiFactorAuthService;

$mfaService = app(MultiFactorAuthService::class);

// Generate QR code and secret
$setupData = $mfaService->generateSetup($user);

// Returns:
// - qr_code_url: Data URL for QR code image
// - secret_key: Base32 encoded secret
// - backup_codes: Array of recovery codes
                        </code></pre>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">2. Verify MFA Setup</h4>
                        <pre><code class="language-php">
// Verify the code from user's authenticator app
$isValid = $mfaService->verifyCode($user, $userProvidedCode);

if ($isValid) {
    $mfaService->confirmSetup($user);
    // MFA is now enabled for the user
}
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Authentication Flow</h3>
                    
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-blue-900 mb-2">Login with MFA</h4>
                        <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                            <li>User provides email and password</li>
                            <li>System validates credentials</li>
                            <li>If MFA enabled, prompt for verification code</li>
                            <li>Validate TOTP code or backup code</li>
                            <li>Create authenticated session</li>
                            <li>Log security event</li>
                        </ol>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Backup Codes</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre><code class="language-php">
// Generate new backup codes
$backupCodes = $mfaService->generateBackupCodes($user, 10);

// Use backup code for authentication
$isValid = $mfaService->verifyBackupCode($user, $backupCode);

// Backup codes are single-use and automatically invalidated
                        </code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- RBAC Section -->
        <div x-show="activeSection === 'rbac'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Role-Based Access Control</h2>
                
                <div class="prose max-w-none">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Role Hierarchy</h3>
                    
                    <div class="bg-gray-50 p-6 rounded-lg mb-6">
                        <pre class="text-sm text-gray-800"><code>
Admin (Full Access)
├── Agent (Ticket Operations)
│   └── Customer (Basic Access)
└── Scraper (System Only)
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Permission Management</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">Check User Permission</h4>
                        <pre><code class="language-php">
use App\Services\AdvancedRBACService;

$rbacService = app(AdvancedRBACService::class);

// Check basic permission
$hasPermission = $rbacService->hasPermission($user, 'tickets.purchase');

// Check resource-specific permission
$canAccess = $rbacService->canAccessResource(
    $user, 
    'ticket', 
    $ticketId, 
    'update'
);
                        </code></pre>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">Role Assignment</h4>
                        <pre><code class="language-php">
// Assign role to user
$rbacService->assignRole($user, 'agent');

// Assign role with expiration
$rbacService->assignRole(
    $user, 
    'agent', 
    now()->addMonths(6)
);

// Grant specific permission
$rbacService->grantPermission(
    $user, 
    'tickets.purchase', 
    'ticket', 
    now()->addDays(30)
);
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Permission Categories</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900">System</h4>
                            <ul class="mt-2 text-sm text-blue-800 space-y-1">
                                <li>• system.manage</li>
                                <li>• system.config</li>
                                <li>• system.logs</li>
                                <li>• system.maintenance</li>
                            </ul>
                        </div>
                        
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900">Users</h4>
                            <ul class="mt-2 text-sm text-green-800 space-y-1">
                                <li>• users.create</li>
                                <li>• users.read</li>
                                <li>• users.update</li>
                                <li>• users.delete</li>
                            </ul>
                        </div>
                        
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-900">Tickets</h4>
                            <ul class="mt-2 text-sm text-purple-800 space-y-1">
                                <li>• tickets.create</li>
                                <li>• tickets.purchase</li>
                                <li>• tickets.assign</li>
                                <li>• tickets.delete</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900">Security</h4>
                            <ul class="mt-2 text-sm text-red-800 space-y-1">
                                <li>• security.audit</li>
                                <li>• security.manage</li>
                                <li>• security.logs</li>
                                <li>• security.incidents</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Security Monitoring Section -->
        <div x-show="activeSection === 'monitoring'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Security Monitoring & Threat Detection</h2>
                
                <div class="prose max-w-none">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Threat Detection</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">Log Security Event</h4>
                        <pre><code class="language-php">
use App\Services\SecurityMonitoringService;

$securityService = app(SecurityMonitoringService::class);

// Log security event with automatic threat analysis
$event = $securityService->logSecurityEvent(
    'login_failed',
    $user,
    $request,
    ['ip' => $request->ip(), 'user_agent' => $request->userAgent()]
);

// System automatically:
// 1. Calculates threat score
// 2. Detects patterns (brute force, enumeration)
// 3. Triggers automated responses if needed
// 4. Creates incidents for high-risk events
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Threat Scoring System</h3>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-yellow-900 mb-2">Score Calculation Factors</h4>
                        <ul class="text-sm text-yellow-800 space-y-1">
                            <li>• <strong>Base Event Score:</strong> login_failed (20), brute_force (80), data_breach (95)</li>
                            <li>• <strong>IP Reputation:</strong> Known malicious IPs add 10-50 points</li>
                            <li>• <strong>Geographic Anomaly:</strong> Impossible travel adds 25 points</li>
                            <li>• <strong>Time Anomaly:</strong> Unusual login time adds 15 points</li>
                            <li>• <strong>Device Unknown:</strong> Untrusted device adds 20 points</li>
                            <li>• <strong>Recent Failures:</strong> 5 points per recent failure from same IP</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Automated Response Levels</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900">Low Risk (0-39)</h4>
                            <ul class="mt-2 text-sm text-green-800 space-y-1">
                                <li>• Normal logging</li>
                                <li>• No automated action</li>
                            </ul>
                        </div>
                        
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-yellow-900">Medium Risk (40-69)</h4>
                            <ul class="mt-2 text-sm text-yellow-800 space-y-1">
                                <li>• Increase monitoring</li>
                                <li>• Additional logging</li>
                            </ul>
                        </div>
                        
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-orange-900">High Risk (70-89)</h4>
                            <ul class="mt-2 text-sm text-orange-800 space-y-1">
                                <li>• Temporary IP blocking</li>
                                <li>• Require additional auth</li>
                                <li>• Send security alerts</li>
                            </ul>
                        </div>
                        
                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900">Critical Risk (90-100)</h4>
                            <ul class="mt-2 text-sm text-red-800 space-y-1">
                                <li>• Extended IP blocking</li>
                                <li>• Account temporary lock</li>
                                <li>• Create security incident</li>
                                <li>• Immediate admin notification</li>
                            </ul>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Audit Logging</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <pre><code class="language-php">
// Log audit event
$securityService->logAuditEvent(
    'user_role_changed',
    $currentUser,        // Who performed the action
    'user',              // Resource type
    $targetUser->id,     // Resource ID
    [                    // Changes made
        'old_role' => 'customer',
        'new_role' => 'agent',
        'changed_by' => $currentUser->id
    ]
);
                        </code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Reference Section -->
        <div x-show="activeSection === 'api'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">API Reference</h2>
                
                <div class="prose max-w-none">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Authentication Endpoints</h3>
                    
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-900">POST /api/auth/mfa/setup</h4>
                            <p class="text-sm text-blue-800 mb-2">Generate MFA setup data for user</p>
                            <pre class="text-xs"><code class="language-json">
{
  "qr_code_url": "data:image/png;base64,...",
  "secret_key": "JBSWY3DPEHPK3PXP",
  "backup_codes": ["12345678", "87654321", ...]
}
                            </code></pre>
                        </div>

                        <div class="bg-green-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-green-900">POST /api/auth/mfa/verify</h4>
                            <p class="text-sm text-green-800 mb-2">Verify MFA code during login</p>
                            <pre class="text-xs"><code class="language-json">
// Request
{
  "code": "123456",
  "backup_code": null
}

// Response
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {...}
}
                            </code></pre>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3 mt-6">Security Dashboard API</h3>
                    
                    <div class="space-y-4">
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-purple-900">GET /security/dashboard/api/data</h4>
                            <p class="text-sm text-purple-800 mb-2">Get real-time dashboard data</p>
                            <pre class="text-xs"><code class="language-json">
{
  "success": true,
  "data": {
    "overview": {
      "security_score": 85,
      "total_events_24h": 1247,
      "high_risk_events_24h": 3,
      "active_incidents": 1,
      "critical_incidents": 0
    },
    "threats": {...},
    "incidents": {...}
  }
}
                            </code></pre>
                        </div>

                        <div class="bg-red-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-red-900">GET /security/dashboard/api/live-events</h4>
                            <p class="text-sm text-red-800 mb-2">Get recent security events (last 5 minutes)</p>
                            <pre class="text-xs"><code class="language-json">
{
  "success": true,
  "events": [
    {
      "id": 1234,
      "event_type": "login_failed",
      "severity": "medium",
      "threat_score": 45,
      "ip_address": "192.168.1.100",
      "user": {"name": "John Doe"},
      "occurred_at": "2024-01-15T10:30:00Z"
    }
  ]
}
                            </code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deployment Section -->
        <div x-show="activeSection === 'deployment'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Deployment Guide</h2>
                
                <div class="prose max-w-none">
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Environment Configuration</h3>
                    
                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold mb-2">Required Environment Variables</h4>
                        <pre><code class="language-bash">
# Security Configuration
SECURITY_FAILED_LOGIN_THRESHOLD=5
SECURITY_LOGIN_RATE_LIMIT=10
SECURITY_ACCOUNT_LOCKOUT_DURATION=30
SECURITY_BRUTE_FORCE_WINDOW=60

# MFA Configuration  
GOOGLE_AUTHENTICATOR_ISSUER="HD Tickets"
SMS_SERVICE_PROVIDER=twilio
TWILIO_SID=your_twilio_sid
TWILIO_TOKEN=your_twilio_token

# RBAC Configuration
RBAC_CACHE_TTL=3600
SECURITY_AUTO_ASSIGNMENT_ENABLED=true

# Monitoring
SECURITY_AUDIT_ENABLED=true
SECURITY_AUDIT_RETENTION_DAYS=365
SECURITY_METRICS_ENABLED=true
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Database Setup</h3>
                    
                    <div class="bg-blue-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-blue-900 mb-2">Run Migrations</h4>
                        <pre><code class="language-bash">
# Run security-related migrations
php artisan migrate

# Seed default roles and permissions
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=SecurityConfigSeeder
                        </code></pre>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Production Considerations</h3>
                    
                    <div class="bg-yellow-50 p-4 rounded-lg mb-6">
                        <h4 class="font-semibold text-yellow-900 mb-2">Security Checklist</h4>
                        <ul class="text-sm text-yellow-800 space-y-1">
                            <li>✓ Enable HTTPS/SSL certificates</li>
                            <li>✓ Configure secure session cookies</li>
                            <li>✓ Set up Redis for session and cache storage</li>
                            <li>✓ Configure proper file permissions</li>
                            <li>✓ Enable Laravel Horizon for queue management</li>
                            <li>✓ Set up log rotation and monitoring</li>
                            <li>✓ Configure firewall and intrusion detection</li>
                            <li>✓ Enable database encryption at rest</li>
                        </ul>
                    </div>

                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Monitoring Setup</h3>
                    
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h4 class="font-semibold text-green-900 mb-2">Queue Workers</h4>
                        <pre><code class="language-bash">
# Start Horizon for queue management
php artisan horizon

# Or use supervisor for queue workers
php artisan queue:work --queue=security,default --timeout=300
                        </code></pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
