@extends('layouts.app-v2')

@section('title', 'Security Configuration - HD Tickets')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
    .config-section { transition: all 0.3s ease; }
    .config-section:hover { background-color: #f9fafb; }
    .status-enabled { color: #059669; }
    .status-disabled { color: #dc2626; }
    .status-warning { color: #d97706; }
    .config-card { border-left: 4px solid #3b82f6; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="securityConfig()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Security Configuration</h1>
                <p class="mt-2 text-lg text-gray-600">Manage system-wide security settings and policies</p>
            </div>
            <div class="flex space-x-3">
                <button @click="resetToDefaults()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium">
                    Reset to Defaults
                </button>
                <button @click="exportConfig()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                    Export Config
                </button>
                <button @click="saveConfiguration()" 
                        :disabled="saving"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium disabled:opacity-50">
                    <span x-show="!saving">Save Configuration</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>

        <!-- System Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Security Status</p>
                        <p class="text-2xl font-bold" :class="systemStatus.security_enabled ? 'text-green-600' : 'text-red-600'"
                           x-text="systemStatus.security_enabled ? 'ENABLED' : 'DISABLED'"></p>
                    </div>
                    <div :class="systemStatus.security_enabled ? 'bg-green-100' : 'bg-red-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="systemStatus.security_enabled ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">MFA Status</p>
                        <p class="text-2xl font-bold" :class="systemStatus.mfa_enforced ? 'text-green-600' : 'text-orange-600'"
                           x-text="systemStatus.mfa_enforced ? 'ENFORCED' : 'OPTIONAL'"></p>
                    </div>
                    <div :class="systemStatus.mfa_enforced ? 'bg-green-100' : 'bg-orange-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="systemStatus.mfa_enforced ? 'text-green-600' : 'text-orange-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Audit Logging</p>
                        <p class="text-2xl font-bold" :class="systemStatus.audit_enabled ? 'text-green-600' : 'text-red-600'"
                           x-text="systemStatus.audit_enabled ? 'ACTIVE' : 'INACTIVE'"></p>
                    </div>
                    <div :class="systemStatus.audit_enabled ? 'bg-green-100' : 'bg-red-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="systemStatus.audit_enabled ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Threat Detection</p>
                        <p class="text-2xl font-bold" :class="systemStatus.threat_detection ? 'text-green-600' : 'text-red-600'"
                           x-text="systemStatus.threat_detection ? 'ACTIVE' : 'INACTIVE'"></p>
                    </div>
                    <div :class="systemStatus.threat_detection ? 'bg-green-100' : 'bg-red-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="systemStatus.threat_detection ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Sections -->
        <div class="space-y-6">
            
            <!-- Authentication & Access Control -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Authentication & Access Control</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure user authentication and access control policies</p>
                </div>
                <div class="p-6 space-y-6">
                    
                    <!-- Multi-Factor Authentication -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Multi-Factor Authentication</h4>
                                <p class="text-sm text-gray-600">Enforce MFA for enhanced security</p>
                            </div>
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input x-model="config.mfa.enabled" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">Enable MFA</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="config.mfa.enabled" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">MFA Enforcement</label>
                                <select x-model="config.mfa.enforcement" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="optional">Optional</option>
                                    <option value="required_admins">Required for Admins</option>
                                    <option value="required_all">Required for All</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">TOTP Window (seconds)</label>
                                <input x-model="config.mfa.totp_window" type="number" min="15" max="120" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Backup Codes Count</label>
                                <input x-model="config.mfa.backup_codes_count" type="number" min="5" max="20" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Password Policies -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Password Policies</h4>
                                <p class="text-sm text-gray-600">Configure password strength requirements</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Length</label>
                                <input x-model="config.password.min_length" type="number" min="6" max="50" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Failed Attempts</label>
                                <input x-model="config.password.max_failed_attempts" type="number" min="3" max="10" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lockout Duration (mins)</label>
                                <input x-model="config.password.lockout_duration" type="number" min="5" max="1440" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password Expiry (days)</label>
                                <input x-model="config.password.expiry_days" type="number" min="0" max="365" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4 space-y-2">
                            <label class="flex items-center">
                                <input x-model="config.password.require_uppercase" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Require uppercase letters</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="config.password.require_lowercase" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Require lowercase letters</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="config.password.require_numbers" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Require numbers</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="config.password.require_symbols" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Require special characters</span>
                            </label>
                        </div>
                    </div>

                    <!-- Session Management -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Session Management</h4>
                                <p class="text-sm text-gray-600">Configure session timeout and security settings</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                                <input x-model="config.session.timeout_minutes" type="number" min="5" max="1440" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Concurrent Sessions</label>
                                <input x-model="config.session.max_concurrent" type="number" min="1" max="10" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Remember Me Duration (days)</label>
                                <input x-model="config.session.remember_duration" type="number" min="1" max="365" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-4 space-y-2">
                            <label class="flex items-center">
                                <input x-model="config.session.secure_cookies" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Require secure cookies (HTTPS)</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="config.session.httponly_cookies" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">HTTP-only cookies</span>
                            </label>
                            <label class="flex items-center">
                                <input x-model="config.session.regenerate_on_login" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Regenerate session ID on login</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Threat Detection & Monitoring -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Threat Detection & Monitoring</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure security monitoring and threat response</p>
                </div>
                <div class="p-6 space-y-6">
                    
                    <!-- Threat Detection -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Threat Detection</h4>
                                <p class="text-sm text-gray-600">Configure automated threat detection parameters</p>
                            </div>
                            <label class="flex items-center">
                                <input x-model="config.threat_detection.enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Enable Threat Detection</span>
                            </label>
                        </div>
                        
                        <div x-show="config.threat_detection.enabled" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Low Threshold</label>
                                    <input x-model="config.threat_detection.low_threshold" type="number" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Medium Threshold</label>
                                    <input x-model="config.threat_detection.medium_threshold" type="number" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">High Threshold</label>
                                    <input x-model="config.threat_detection.high_threshold" type="number" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Critical Threshold</label>
                                    <input x-model="config.threat_detection.critical_threshold" type="number" min="0" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Brute Force Window (minutes)</label>
                                    <input x-model="config.threat_detection.brute_force_window" type="number" min="1" max="60" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Failed Attempts</label>
                                    <input x-model="config.threat_detection.max_failed_attempts" type="number" min="3" max="20" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rate Limiting -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Rate Limiting</h4>
                                <p class="text-sm text-gray-600">Configure API and request rate limiting</p>
                            </div>
                            <label class="flex items-center">
                                <input x-model="config.rate_limiting.enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Enable Rate Limiting</span>
                            </label>
                        </div>
                        
                        <div x-show="config.rate_limiting.enabled" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">API Requests/Minute</label>
                                <input x-model="config.rate_limiting.api_requests_per_minute" type="number" min="10" max="1000" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Login Attempts/Hour</label>
                                <input x-model="config.rate_limiting.login_attempts_per_hour" type="number" min="5" max="100" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Requests/Hour</label>
                                <input x-model="config.rate_limiting.purchase_requests_per_hour" type="number" min="10" max="500" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- IP Blocking -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">IP Blocking & Geolocation</h4>
                                <p class="text-sm text-gray-600">Configure IP-based access controls</p>
                            </div>
                            <label class="flex items-center">
                                <input x-model="config.ip_blocking.enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Enable IP Blocking</span>
                            </label>
                        </div>
                        
                        <div x-show="config.ip_blocking.enabled" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Auto Block Duration (hours)</label>
                                    <input x-model="config.ip_blocking.auto_block_duration" type="number" min="1" max="168" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Max Block Duration (hours)</label>
                                    <input x-model="config.ip_blocking.max_block_duration" type="number" min="1" max="8760" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Blocked Countries (comma-separated country codes)</label>
                                <input x-model="config.ip_blocking.blocked_countries" type="text" placeholder="e.g., CN,RU,KP" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit & Compliance -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Audit & Compliance</h3>
                    <p class="text-sm text-gray-600 mt-1">Configure audit logging and compliance settings</p>
                </div>
                <div class="p-6 space-y-6">
                    
                    <!-- Audit Logging -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Audit Logging</h4>
                                <p class="text-sm text-gray-600">Configure comprehensive audit trail settings</p>
                            </div>
                            <label class="flex items-center">
                                <input x-model="config.audit.enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2 text-sm text-gray-700">Enable Audit Logging</span>
                            </label>
                        </div>
                        
                        <div x-show="config.audit.enabled" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Retention Period (days)</label>
                                    <input x-model="config.audit.retention_days" type="number" min="30" max="2555" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
                                    <select x-model="config.audit.log_level" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="info">Info</option>
                                        <option value="warning">Warning</option>
                                        <option value="error">Error</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Compression</label>
                                    <select x-model="config.audit.compression" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="none">None</option>
                                        <option value="gzip">Gzip</option>
                                        <option value="bzip2">Bzip2</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-2">
                                <h5 class="font-medium text-gray-900">Log Categories</h5>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_authentication" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Authentication</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_authorization" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Authorization</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_data_access" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Data Access</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_data_changes" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Data Changes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_admin_actions" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Admin Actions</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_security_events" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Security Events</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_purchases" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Ticket Purchases</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input x-model="config.audit.log_system_changes" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">System Changes</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Compliance Settings -->
                    <div class="config-section p-4 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-md font-semibold text-gray-900">Compliance Settings</h4>
                                <p class="text-sm text-gray-600">Configure regulatory compliance requirements</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg">
                                    <input x-model="config.compliance.gdpr_enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-700">GDPR Compliance</span>
                                        <p class="text-xs text-gray-500">Enable GDPR data protection features</p>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg">
                                    <input x-model="config.compliance.pci_dss_enabled" type="checkbox" class="rounded border-gray-300 text-blue-600">
                                    <div class="ml-3">
                                        <span class="text-sm font-medium text-gray-700">PCI DSS Compliance</span>
                                        <p class="text-xs text-gray-500">Payment card data security standards</p>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Retention Period (days)</label>
                                    <input x-model="config.compliance.data_retention_days" type="number" min="30" max="2555" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Backup Retention Period (days)</label>
                                    <input x-model="config.compliance.backup_retention_days" type="number" min="7" max="365" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button (Fixed at bottom) -->
        <div class="fixed bottom-0 right-0 left-0 bg-white border-t border-gray-200 px-6 py-4 z-10">
            <div class="max-w-7xl mx-auto flex justify-end space-x-3">
                <button @click="resetToDefaults()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md font-medium">
                    Reset
                </button>
                <button @click="saveConfiguration()" 
                        :disabled="saving"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium disabled:opacity-50">
                    <span x-show="!saving">Save All Changes</span>
                    <span x-show="saving">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function securityConfig() {
    return {
        loading: false,
        saving: false,
        systemStatus: {
            security_enabled: true,
            mfa_enforced: false,
            audit_enabled: true,
            threat_detection: true
        },
        config: {
            mfa: {
                enabled: true,
                enforcement: 'required_admins',
                totp_window: 30,
                backup_codes_count: 10
            },
            password: {
                min_length: 8,
                max_failed_attempts: 5,
                lockout_duration: 30,
                expiry_days: 90,
                require_uppercase: true,
                require_lowercase: true,
                require_numbers: true,
                require_symbols: false
            },
            session: {
                timeout_minutes: 120,
                max_concurrent: 3,
                remember_duration: 30,
                secure_cookies: true,
                httponly_cookies: true,
                regenerate_on_login: true
            },
            threat_detection: {
                enabled: true,
                low_threshold: 39,
                medium_threshold: 69,
                high_threshold: 89,
                critical_threshold: 90,
                brute_force_window: 5,
                max_failed_attempts: 5
            },
            rate_limiting: {
                enabled: true,
                api_requests_per_minute: 60,
                login_attempts_per_hour: 20,
                purchase_requests_per_hour: 100
            },
            ip_blocking: {
                enabled: true,
                auto_block_duration: 1,
                max_block_duration: 168,
                blocked_countries: ''
            },
            audit: {
                enabled: true,
                retention_days: 365,
                log_level: 'info',
                compression: 'gzip',
                log_authentication: true,
                log_authorization: true,
                log_data_access: true,
                log_data_changes: true,
                log_admin_actions: true,
                log_security_events: true,
                log_purchases: true,
                log_system_changes: true
            },
            compliance: {
                gdpr_enabled: true,
                pci_dss_enabled: false,
                data_retention_days: 365,
                backup_retention_days: 90
            }
        },

        init() {
            this.loadConfiguration();
            this.loadSystemStatus();
        },

        async loadConfiguration() {
            this.loading = true;
            try {
                const response = await fetch('/security/dashboard/config/api');
                const data = await response.json();
                
                if (data.success) {
                    this.config = { ...this.config, ...data.config };
                }
            } catch (error) {
                console.error('Error loading configuration:', error);
            } finally {
                this.loading = false;
            }
        },

        async loadSystemStatus() {
            try {
                const response = await fetch('/security/dashboard/status/api');
                const data = await response.json();
                
                if (data.success) {
                    this.systemStatus = { ...this.systemStatus, ...data.status };
                }
            } catch (error) {
                console.error('Error loading system status:', error);
            }
        },

        async saveConfiguration() {
            this.saving = true;
            try {
                const response = await fetch('/security/dashboard/config', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ config: this.config })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Configuration saved successfully!');
                    // Reload system status to reflect changes
                    this.loadSystemStatus();
                } else {
                    alert('Error saving configuration: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving configuration:', error);
                alert('Error saving configuration');
            } finally {
                this.saving = false;
            }
        },

        async resetToDefaults() {
            if (confirm('Are you sure you want to reset all settings to defaults? This action cannot be undone.')) {
                try {
                    const response = await fetch('/security/dashboard/config/reset', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const data = await response.json();
                    
                    if (data.success) {
                        this.config = data.config;
                        alert('Configuration reset to defaults successfully!');
                        this.loadSystemStatus();
                    } else {
                        alert('Error resetting configuration: ' + (data.message || 'Unknown error'));
                    }
                } catch (error) {
                    console.error('Error resetting configuration:', error);
                    alert('Error resetting configuration');
                }
            }
        },

        async exportConfig() {
            try {
                const response = await fetch('/security/dashboard/config/export');
                const blob = await response.blob();
                
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `security_config_${new Date().toISOString().split('T')[0]}.json`;
                
                document.body.appendChild(a);
                a.click();
                
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                alert('Configuration exported successfully!');
            } catch (error) {
                console.error('Error exporting configuration:', error);
                alert('Error exporting configuration');
            }
        }
    };
}
</script>
@endsection
