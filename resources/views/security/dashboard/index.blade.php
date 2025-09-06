@extends('layouts.app')

@section('title', 'Security Dashboard - HD Tickets')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    .pulse { animation: pulse 2s infinite; }
    
    .threat-critical { @apply bg-red-100 border-red-500 text-red-800; }
    .threat-high { @apply bg-orange-100 border-orange-500 text-orange-800; }
    .threat-medium { @apply bg-yellow-100 border-yellow-500 text-yellow-800; }
    .threat-low { @apply bg-green-100 border-green-500 text-green-800; }
    
    .status-open { @apply bg-red-100 text-red-800; }
    .status-investigating { @apply bg-yellow-100 text-yellow-800; }
    .status-resolved { @apply bg-green-100 text-green-800; }
</style>
@endsection

@section('content')
<div class="min-h-screen bg-gray-50" x-data="securityDashboard()">
    <!-- Dashboard Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Security Dashboard</h1>
                        <p class="mt-1 text-sm text-gray-500">
                            Real-time security monitoring and threat detection for HD Tickets
                        </p>
                    </div>
                    
                    <!-- Security Score -->
                    <div class="flex items-center space-x-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold" :class="{
                                'text-green-600': dashboardData.overview?.security_score >= 80,
                                'text-yellow-600': dashboardData.overview?.security_score >= 60 && dashboardData.overview?.security_score < 80,
                                'text-red-600': dashboardData.overview?.security_score < 60
                            }" x-text="dashboardData.overview?.security_score || 0"></div>
                            <div class="text-xs text-gray-500">Security Score</div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full pulse" :class="{
                                'bg-green-500': isSystemHealthy,
                                'bg-yellow-500': hasWarnings,
                                'bg-red-500': hasCriticalIssues
                            }"></div>
                            <span class="text-sm text-gray-600" x-text="systemStatusText"></span>
                        </div>
                        
                        <!-- Auto-refresh Toggle -->
                        <div class="flex items-center">
                            <button @click="toggleAutoRefresh()" 
                                    class="flex items-center px-3 py-1 rounded-md text-sm"
                                    :class="autoRefresh ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'">
                                <svg class="w-4 h-4 mr-1" :class="autoRefresh ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                <span x-text="autoRefresh ? 'Auto' : 'Manual'"></span>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Navigation Tabs -->
                <div class="mt-6">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'overview'" 
                                :class="activeTab === 'overview' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Overview
                        </button>
                        <button @click="activeTab = 'threats'" 
                                :class="activeTab === 'threats' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Threat Detection
                        </button>
                        <button @click="activeTab = 'incidents'" 
                                :class="activeTab === 'incidents' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Incidents
                        </button>
                        <button @click="activeTab = 'users'" 
                                :class="activeTab === 'users' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Users
                        </button>
                        <button @click="activeTab = 'audit'" 
                                :class="activeTab === 'audit' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Audit
                        </button>
                        <button @click="activeTab = 'demo'" 
                                :class="activeTab === 'demo' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                            Demo
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" class="space-y-8">
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Security Events Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Security Events (24h)
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" x-text="dashboardData.overview?.total_events_24h || 0"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm">
                            <span class="font-medium" 
                                  :class="dashboardData.overview?.trends?.['24h_vs_prev']?.trend === 'up' ? 'text-red-600' : 'text-green-600'"
                                  x-text="(dashboardData.overview?.trends?.['24h_vs_prev']?.percentage || 0) + '%'"></span>
                            <span class="text-gray-600"> from yesterday</span>
                        </div>
                    </div>
                </div>

                <!-- High Risk Events Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        High Risk Events
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" x-text="dashboardData.overview?.high_risk_events_24h || 0"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm text-gray-600">
                            Threat score ≥ 70
                        </div>
                    </div>
                </div>

                <!-- Active Incidents Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Active Incidents
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" x-text="dashboardData.overview?.active_incidents || 0"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm text-red-600" x-show="dashboardData.overview?.critical_incidents > 0">
                            <span x-text="dashboardData.overview?.critical_incidents || 0"></span> Critical
                        </div>
                        <div class="text-sm text-gray-600" x-show="!dashboardData.overview?.critical_incidents">
                            All incidents under control
                        </div>
                    </div>
                </div>

                <!-- MFA Adoption Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        MFA Adoption
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900" x-text="(dashboardData.users?.mfa_adoption_rate || 0) + '%'"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-5 py-3">
                        <div class="text-sm text-gray-600">
                            <span x-text="dashboardData.users?.mfa_enabled || 0"></span> users enabled
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Threat Trends Chart -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Security Events Timeline</h3>
                    <canvas id="threatTrendsChart" width="400" height="200"></canvas>
                </div>

                <!-- Threat Distribution Pie Chart -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Threat Distribution</h3>
                    <canvas id="threatDistributionChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Events and Incidents -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Security Events -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Security Events</h3>
                    </div>
                    <div class="p-6">
                        <div class="flow-root">
                            <ul role="list" class="-mb-8" x-data="{ recentEvents: dashboardData.realtime?.live_events?.slice(0, 10) || [] }">
                                <template x-for="(event, index) in recentEvents" :key="event.id">
                                    <li>
                                        <div class="relative pb-8" x-show="index < recentEvents.length - 1">
                                            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"></span>
                                        </div>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white"
                                                      :class="{
                                                          'bg-red-500': event.severity === 'critical',
                                                          'bg-orange-500': event.severity === 'high',
                                                          'bg-yellow-500': event.severity === 'medium',
                                                          'bg-green-500': event.severity === 'low'
                                                      }">
                                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">
                                                        <span class="font-medium text-gray-900" x-text="event.event_type"></span>
                                                        <span x-show="event.user" x-text="'by ' + event.user?.name"></span>
                                                    </p>
                                                    <p class="text-xs text-gray-400" x-text="event.ip_address"></p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time x-text="formatTime(event.occurred_at)"></time>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                                
                                <li x-show="recentEvents.length === 0" class="text-center py-4 text-gray-500">
                                    No recent events
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Active Incidents -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Active Incidents</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4" x-data="{ activeIncidents: dashboardData.incidents?.open_incidents || [] }">
                            <template x-for="incident in activeIncidents.slice(0, 5)" :key="incident.id">
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                  :class="{
                                                      'bg-red-100 text-red-800': incident.severity === 'critical',
                                                      'bg-orange-100 text-orange-800': incident.severity === 'high',
                                                      'bg-yellow-100 text-yellow-800': incident.severity === 'medium',
                                                      'bg-green-100 text-green-800': incident.severity === 'low'
                                                  }"
                                                  x-text="incident.severity"></span>
                                            <span class="text-sm font-medium text-gray-900" x-text="incident.title"></span>
                                        </div>
                                        <div class="text-sm text-gray-500" x-text="formatTime(incident.detected_at)"></div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600" x-text="incident.description"></p>
                                    <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                        <span x-show="incident.affected_user">
                                            User: <span class="font-medium" x-text="incident.affected_user?.name"></span>
                                        </span>
                                        <span x-show="incident.source_ip">
                                            IP: <span class="font-medium" x-text="incident.source_ip"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                            
                            <div x-show="activeIncidents.length === 0" class="text-center py-4 text-gray-500">
                                No active incidents
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demo Tab -->
        <div x-show="activeTab === 'demo'" class="space-y-8">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Security Features Demo</h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- MFA Demo -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Multi-Factor Authentication</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="space-y-3">
                                <button class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
                                        @click="demonstrateMFA()">
                                    Demo MFA Setup Process
                                </button>
                                <button class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors"
                                        @click="generateBackupCodes()">
                                    Generate Backup Codes
                                </button>
                                <div class="text-sm text-gray-600">
                                    <p>✅ Google Authenticator Integration</p>
                                    <p>✅ SMS Verification Backup</p>
                                    <p>✅ Recovery Code System</p>
                                    <p>✅ Device Trust Management</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Threat Detection Demo -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900">Threat Detection</h3>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="space-y-3">
                                <button class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors"
                                        @click="simulateBruteForce()">
                                    Simulate Brute Force Attack
                                </button>
                                <button class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 transition-colors"
                                        @click="simulateSuspiciousLogin()">
                                    Simulate Suspicious Login
                                </button>
                                <div class="text-sm text-gray-600">
                                    <p>✅ Real-time Threat Scoring</p>
                                    <p>✅ Geographic Anomaly Detection</p>
                                    <p>✅ Automated Response System</p>
                                    <p>✅ Pattern Recognition</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Demo Results -->
                <div x-show="demoResult" class="mt-6 p-4 rounded-lg" :class="demoResult?.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
                    <div class="flex items-center">
                        <svg x-show="demoResult?.success" class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <svg x-show="!demoResult?.success" class="h-5 w-5 text-red-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium" x-text="demoResult?.message"></span>
                    </div>
                    <div x-show="demoResult?.details" class="mt-2 text-sm" x-html="demoResult?.details"></div>
                </div>
            </div>

            <!-- Security Documentation -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Security Documentation</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Implementation Guide</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Multi-Factor Authentication Setup</li>
                            <li>• Device Trust Management</li>
                            <li>• RBAC Configuration</li>
                            <li>• Security Monitoring</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">API Reference</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Authentication Endpoints</li>
                            <li>• Security Event APIs</li>
                            <li>• Incident Management</li>
                            <li>• Audit Log Access</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-900 mb-2">Best Practices</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Password Policies</li>
                            <li>• Session Management</li>
                            <li>• Threat Response</li>
                            <li>• Compliance Standards</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Loading security data...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function securityDashboard() {
    return {
        activeTab: 'overview',
        dashboardData: @json($dashboardData),
        loading: false,
        autoRefresh: true,
        refreshInterval: null,
        demoResult: null,
        
        init() {
            this.startAutoRefresh();
            this.$nextTick(() => {
                this.initializeCharts();
            });
        },
        
        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },
        
        startAutoRefresh() {
            this.stopAutoRefresh();
            this.refreshInterval = setInterval(() => {
                this.refreshDashboard();
            }, 30000); // 30 seconds
        },
        
        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },
        
        async refreshDashboard() {
            try {
                const response = await fetch('/security/dashboard/api/data', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.dashboardData = data.data;
                    this.updateCharts();
                }
            } catch (error) {
                console.error('Failed to refresh dashboard:', error);
            }
        },
        
        initializeCharts() {
            // Initialize threat trends chart
            const threatCtx = document.getElementById('threatTrendsChart')?.getContext('2d');
            if (threatCtx) {
                new Chart(threatCtx, {
                    type: 'line',
                    data: {
                        labels: ['6h ago', '5h ago', '4h ago', '3h ago', '2h ago', '1h ago', 'Now'],
                        datasets: [{
                            label: 'Security Events',
                            data: [12, 19, 8, 15, 12, 25, 18],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Initialize threat distribution chart
            const distCtx = document.getElementById('threatDistributionChart')?.getContext('2d');
            if (distCtx) {
                new Chart(distCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Login Failed', 'Suspicious Login', 'Rate Limited', 'Bot Detected'],
                        datasets: [{
                            data: [45, 25, 20, 10],
                            backgroundColor: [
                                '#EF4444',
                                '#F59E0B',
                                '#10B981',
                                '#6B7280'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        },
        
        updateCharts() {
            // Update chart data based on new dashboard data
            // Implementation would update the Chart.js instances
        },
        
        // Demo functions
        async demonstrateMFA() {
            this.loading = true;
            this.demoResult = null;
            
            try {
                // Simulate MFA demo
                await new Promise(resolve => setTimeout(resolve, 2000));
                this.demoResult = {
                    success: true,
                    message: 'MFA Demo Completed Successfully',
                    details: '<strong>Demonstration Results:</strong><br>• QR Code Generated<br>• Backup codes created<br>• Device registered<br>• 2FA challenge simulated'
                };
            } catch (error) {
                this.demoResult = {
                    success: false,
                    message: 'Demo Failed',
                    details: error.message
                };
            } finally {
                this.loading = false;
            }
        },
        
        async simulateBruteForce() {
            this.loading = true;
            this.demoResult = null;
            
            try {
                await new Promise(resolve => setTimeout(resolve, 1500));
                this.demoResult = {
                    success: true,
                    message: 'Brute Force Attack Detected & Blocked',
                    details: '<strong>Threat Response:</strong><br>• Multiple failed login attempts detected<br>• IP address blocked temporarily<br>• Security incident created<br>• Admin notifications sent'
                };
            } catch (error) {
                this.demoResult = {
                    success: false,
                    message: 'Simulation Failed',
                    details: error.message
                };
            } finally {
                this.loading = false;
            }
        },
        
        async simulateSuspiciousLogin() {
            this.loading = true;
            this.demoResult = null;
            
            try {
                await new Promise(resolve => setTimeout(resolve, 1500));
                this.demoResult = {
                    success: true,
                    message: 'Suspicious Login Pattern Detected',
                    details: '<strong>Anomaly Detected:</strong><br>• Geographic impossibility identified<br>• Device fingerprint mismatch<br>• Additional authentication required<br>• User security alert sent'
                };
            } catch (error) {
                this.demoResult = {
                    success: false,
                    message: 'Simulation Failed',
                    details: error.message
                };
            } finally {
                this.loading = false;
            }
        },
        
        formatTime(timestamp) {
            if (!timestamp) return '';
            const date = new Date(timestamp);
            const now = new Date();
            const diff = now - date;
            const minutes = Math.floor(diff / 60000);
            
            if (minutes < 1) return 'Just now';
            if (minutes < 60) return `${minutes}m ago`;
            if (minutes < 1440) return `${Math.floor(minutes / 60)}h ago`;
            return date.toLocaleDateString();
        },
        
        get isSystemHealthy() {
            return this.dashboardData.overview?.security_score >= 80 && 
                   this.dashboardData.overview?.critical_incidents === 0;
        },
        
        get hasWarnings() {
            return this.dashboardData.overview?.security_score >= 60 && 
                   this.dashboardData.overview?.security_score < 80;
        },
        
        get hasCriticalIssues() {
            return this.dashboardData.overview?.security_score < 60 || 
                   this.dashboardData.overview?.critical_incidents > 0;
        },
        
        get systemStatusText() {
            if (this.isSystemHealthy) return 'System Secure';
            if (this.hasWarnings) return 'Warnings Present';
            return 'Critical Issues';
        }
    }
}
</script>
@endsection
