@extends('layouts.admin')

@section('title', $page_title)

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    {{-- Header --}}
    <div class="bg-white dark:bg-gray-800 shadow">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between items-center">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white flex items-center">
                        <svg class="w-6 h-6 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        {{ $page_title }}
                    </h1>
                </div>
                <div class="flex items-center space-x-4">
                    {{-- Real-time status indicator --}}
                    <div class="flex items-center">
                        <div class="animate-pulse h-2 w-2 bg-green-400 rounded-full mr-2"></div>
                        <span class="text-sm text-gray-600 dark:text-gray-300">Live Monitoring</span>
                    </div>
                    
                    {{-- Quick actions --}}
                    <div class="flex space-x-2">
                        <button onclick="triggerMonitoring()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Run Now
                        </button>
                        <button onclick="clearCache()" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                            Clear Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Dashboard Content --}}
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Alert Messages --}}
        @if(session('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
        @endif

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Connections --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Connections</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $connection_stats['configured_connections'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Active Connections --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Connections</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $connection_stats['active_connections'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Platform Patterns --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Platform Patterns</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $monitoring_stats['platform_patterns'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Mailboxes Monitored --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0a2 2 0 002-2h6l2 2h6a2 2 0 012 2v1"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Mailboxes Monitored</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $monitoring_stats['total_mailboxes'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Connection Status Panel --}}
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Connection Status</h3>
                        <a href="{{ route('admin.imap.connections') }}" 
                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            View All →
                        </a>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4" id="connection-status">
                            {{-- Connection status will be loaded dynamically --}}
                            <div class="animate-pulse">
                                <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Actions Panel --}}
            <div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">Quick Actions</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <button onclick="triggerMonitoring()" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m6-10V7a2 2 0 00-2-2H5a2 2 0 00-2 2v3m14 0V8a2 2 0 00-2-2"/>
                            </svg>
                            Run Email Monitoring
                        </button>
                        
                        <button onclick="testAllConnections()" 
                                class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Test All Connections
                        </button>
                        
                        <button onclick="clearCache()" 
                                class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-3 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Clear Processed Cache
                        </button>

                        <div class="border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Quick Links</h4>
                            <div class="space-y-2">
                                <a href="{{ route('admin.imap.analytics') }}" 
                                   class="block text-blue-600 hover:text-blue-800 text-sm">
                                    📊 Analytics & Statistics
                                </a>
                                <a href="{{ route('admin.imap.logs') }}" 
                                   class="block text-blue-600 hover:text-blue-800 text-sm">
                                    📋 View Logs
                                </a>
                                <a href="{{ route('admin.imap.platforms') }}" 
                                   class="block text-blue-600 hover:text-blue-800 text-sm">
                                    ⚙️ Platform Configuration
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- System Health Panel --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow mt-6">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">System Health</h3>
                    </div>
                    <div class="p-6 space-y-4" id="system-health">
                        {{-- System health will be loaded dynamically --}}
                        <div class="animate-pulse">
                            <div class="h-3 bg-gray-200 rounded w-full mb-2"></div>
                            <div class="h-3 bg-gray-200 rounded w-3/4"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity Panel --}}
        <div class="mt-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Activity</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400" id="last-updated">
                        Last updated: {{ now()->format('H:i:s') }}
                    </span>
                </div>
                <div class="p-6" id="recent-activity">
                    {{-- Recent activity will be loaded dynamically --}}
                    <div class="animate-pulse space-y-3">
                        <div class="h-4 bg-gray-200 rounded w-full"></div>
                        <div class="h-4 bg-gray-200 rounded w-5/6"></div>
                        <div class="h-4 bg-gray-200 rounded w-4/6"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Command Output Modal --}}
@if(session('command_output'))
<div id="commandOutputModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh]">
        <div class="flex justify-between items-center p-6 border-b">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Command Output</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-auto max-h-96">
            <pre class="bg-gray-100 dark:bg-gray-700 p-4 rounded text-sm">{{ session('command_output') }}</pre>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
// Auto-refresh dashboard data
let refreshInterval;

document.addEventListener('DOMContentLoaded', function() {
    loadDashboardData();
    startAutoRefresh();
});

function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        loadDashboardData();
        updateLastUpdated();
    }, 30000); // Refresh every 30 seconds
}

function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
}

function loadDashboardData() {
    // Load connection status
    fetch('/api/imap/connection-health')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateConnectionStatus(data.data);
            }
        })
        .catch(error => console.error('Error loading connection status:', error));

    // Load recent activity
    fetch('/api/imap/dashboard')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateRecentActivity(data.data.recent_activity);
                updateSystemHealth(data.data.system_health);
            }
        })
        .catch(error => console.error('Error loading dashboard data:', error));
}

function updateConnectionStatus(data) {
    const container = document.getElementById('connection-status');
    let html = '';

    Object.values(data.connections).forEach(connection => {
        const statusClass = connection.status === 'healthy' ? 'text-green-600' : 'text-red-600';
        const statusIcon = connection.status === 'healthy' ? '✅' : '❌';
        
        html += `
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">${connection.name}</p>
                    <p class="text-sm ${statusClass}">${statusIcon} ${connection.status}</p>
                </div>
                <div class="text-right">
                    ${connection.response_time ? `<p class="text-sm text-gray-600 dark:text-gray-300">${connection.response_time}s</p>` : ''}
                    ${connection.messages_count ? `<p class="text-xs text-gray-500">${connection.messages_count} messages</p>` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

function updateRecentActivity(data) {
    const container = document.getElementById('recent-activity');
    let html = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">${data.total_emails_processed_today}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Emails Processed Today</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">${data.sports_events_discovered_today}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Sports Events Discovered</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-600">${data.tickets_found_today}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Tickets Found</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-yellow-600">${data.recent_platforms.length}</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">Active Platforms</p>
            </div>
        </div>
    `;
    container.innerHTML = html;
}

function updateSystemHealth(data) {
    const container = document.getElementById('system-health');
    let html = `
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-300">IMAP Extension</span>
                <span class="${data.imap_extension ? 'text-green-600' : 'text-red-600'}">${data.imap_extension ? '✅' : '❌'}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-300">Redis Connection</span>
                <span class="${data.redis_connection ? 'text-green-600' : 'text-red-600'}">${data.redis_connection ? '✅' : '❌'}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600 dark:text-gray-300">Queue Workers</span>
                <span class="text-blue-600">${data.queue_workers}</span>
            </div>
        </div>
    `;
    container.innerHTML = html;
}

function updateLastUpdated() {
    const element = document.getElementById('last-updated');
    if (element) {
        element.textContent = 'Last updated: ' + new Date().toLocaleTimeString();
    }
}

// Action functions
function triggerMonitoring() {
    if (confirm('Start email monitoring now?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.imap.trigger-monitoring") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

function clearCache() {
    if (confirm('Clear all processed email cache?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.imap.clear-cache") }}';
        form.innerHTML = '@csrf';
        document.body.appendChild(form);
        form.submit();
    }
}

function testAllConnections() {
    alert('Testing all connections... Check the Connections page for results.');
    window.location.href = '{{ route("admin.imap.connections") }}';
}

function closeModal() {
    const modal = document.getElementById('commandOutputModal');
    if (modal) {
        modal.remove();
    }
}

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});
</script>
@endpush
