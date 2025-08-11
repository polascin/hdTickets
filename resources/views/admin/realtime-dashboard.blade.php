@extends('layouts.modern')

@section('title', 'Real-time Monitoring Dashboard')
@section('description', 'Live sports ticket monitoring with real-time updates and comprehensive analytics')

@push('styles')
<style>
/* Real-time Dashboard Specific Styles */
.realtime-card {
    @apply bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-100 dark:border-slate-700;
}

.status-pulse {
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.metric-animate {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.update-slide {
    animation: slideInFromRight 0.3s ease-out;
}

@keyframes slideInFromRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}
</style>
@endpush

@section('content')
<div x-data="realtimeDashboard()" x-init="init()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Real-time Monitoring <span class="text-blue-600 dark:text-blue-400">Dashboard</span>
            </h1>
            <p class="text-gray-600 dark:text-gray-300 mt-1">Live sports ticket monitoring with comprehensive analytics</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="modern-button bg-green-600 hover:bg-green-700" id="start-monitoring">
                <i class="fas fa-play mr-2"></i> Start Monitoring
            </button>
            <button type="button" class="modern-button bg-red-600 hover:bg-red-700" id="stop-monitoring">
                <i class="fas fa-stop mr-2"></i> Stop Monitoring
            </button>
            <button type="button" class="modern-button bg-gray-600 hover:bg-gray-700" id="refresh-data">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="modern-card mb-6" id="connection-status">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="status-pulse">
                    <i class="fas fa-wifi text-2xl" id="connection-icon"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-900 dark:text-white" id="connection-text">
                        Connecting to real-time updates...
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">WebSocket connection status</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span class="status-indicator status-inactive" id="update-frequency">Every 15s</span>
                <small class="text-gray-500 dark:text-gray-400" id="last-ping">Last ping: --</small>
            </div>
        </div>
    </div>

    <!-- Monitoring Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Watched Tickets -->
        <div class="modern-card bg-gradient-to-r from-blue-500 to-blue-600 text-white relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-bold metric-animate" id="watched-tickets-count">-</h3>
                    <p class="text-blue-100 text-sm font-medium">Watched Tickets</p>
                </div>
                <div class="text-blue-200">
                    <i class="fas fa-eye text-3xl"></i>
                </div>
            </div>
            <div class="absolute -top-2 -right-2 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
        </div>

        <!-- Active Scrapers -->
        <div class="modern-card bg-gradient-to-r from-green-500 to-green-600 text-white relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-bold metric-animate" id="active-scrapers">-</h3>
                    <p class="text-green-100 text-sm font-medium">Active Scrapers</p>
                </div>
                <div class="text-green-200">
                    <i class="fas fa-spider text-3xl"></i>
                </div>
            </div>
            <div class="absolute -top-2 -right-2 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
        </div>

        <!-- Alerts Sent -->
        <div class="modern-card bg-gradient-to-r from-yellow-500 to-orange-500 text-white relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-3xl font-bold metric-animate" id="alerts-sent">-</h3>
                    <p class="text-yellow-100 text-sm font-medium">Alerts Sent</p>
                </div>
                <div class="text-yellow-200">
                    <i class="fas fa-bell text-3xl"></i>
                </div>
            </div>
            <div class="absolute -top-2 -right-2 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
        </div>

        <!-- Monitoring Status -->
        <div class="modern-card bg-gradient-to-r from-purple-500 to-indigo-600 text-white relative overflow-hidden">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold metric-animate" id="monitoring-status">Inactive</h3>
                    <p class="text-purple-100 text-sm font-medium">Status</p>
                </div>
                <div class="text-purple-200">
                    <i class="fas fa-heartbeat text-3xl status-pulse"></i>
                </div>
            </div>
            <div class="absolute -top-2 -right-2 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
        </div>
    </div>

    <!-- Live Updates and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Live Ticket Updates -->
        <div class="lg:col-span-2">
            <div class="modern-card h-full">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        <i class="fas fa-stream text-blue-600 mr-2"></i>
                        Live Ticket Updates
                    </h2>
                    <button class="modern-button bg-gray-500 hover:bg-gray-600 text-sm px-3 py-1" id="clear-updates">
                        <i class="fas fa-trash mr-1"></i> Clear
                    </button>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="update-feed overflow-y-auto" id="update-feed" style="height: 400px;">
                        <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <i class="fas fa-clock text-4xl mb-4 text-gray-300 dark:text-gray-600"></i>
                            <p class="text-lg font-medium">Waiting for real-time updates...</p>
                            <p class="text-sm">Updates will appear here when monitoring is active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health & Quick Actions -->
        <div class="space-y-6">
            <!-- System Health -->
            <div class="modern-card">
                <div class="flex items-center mb-4">
                    <i class="fas fa-heartbeat text-red-500 text-xl mr-2"></i>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">System Health</h2>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-300 font-medium">Connection Status:</span>
                        <span class="status-indicator status-error" id="ws-status">Disconnected</span>
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-700">
                        <span class="text-gray-600 dark:text-gray-300 font-medium">Last Update:</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400" id="last-update">Never</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-gray-600 dark:text-gray-300 font-medium">Updates Count:</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400" id="updates-count">0</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="modern-card">
                <div class="flex items-center mb-4">
                    <i class="fas fa-bolt text-yellow-500 text-xl mr-2"></i>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Quick Actions</h2>
                </div>
                <div class="space-y-3">
                    <button class="w-full modern-button bg-blue-600 hover:bg-blue-700 text-sm" id="test-websocket">
                        <i class="fas fa-plug mr-2"></i> Test WebSocket
                    </button>
                    <button class="w-full modern-button bg-indigo-600 hover:bg-indigo-700 text-sm" id="fetch-stats">
                        <i class="fas fa-chart-bar mr-2"></i> Fetch Statistics
                    </button>
                    <button class="w-full modern-button bg-yellow-600 hover:bg-yellow-700 text-sm" id="test-notification">
                        <i class="fas fa-bell mr-2"></i> Test Notification
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="{{ css_with_timestamp('resources/css/app.css') }}" rel="stylesheet">

<style>
.update-item {
    padding: 10px;
    border-left: 4px solid #007bff;
    margin-bottom: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    animation: slideIn 0.3s ease-in;
}

.update-item.availability {
    border-left-color: #28a745;
}

.update-item.price {
    border-left-color: #ffc107;
}

.update-item.error {
    border-left-color: #dc3545;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.badge-connected {
    background-color: #28a745;
}

.badge-disconnected {
    background-color: #dc3545;
}

.badge-connecting {
    background-color: #ffc107;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const websocketManager = window.websocketManager;
    let updatesCount = 0;
    let isMonitoring = false;

    // DOM elements
    const connectionStatus = document.getElementById('connection-status');
    const wsStatus = document.getElementById('ws-status');
    const updateFeed = document.getElementById('update-feed');
    const lastUpdate = document.getElementById('last-update');
    const updatesCountEl = document.getElementById('updates-count');
    const monitoringStatusEl = document.getElementById('monitoring-status');

    // Initialize WebSocket connection status
    function updateConnectionStatus(status, type = 'info') {
        const connectionIcon = document.getElementById('connection-icon');
        const connectionText = document.getElementById('connection-text');
        const lastPing = document.getElementById('last-ping');
        
        // Update connection status card styling
        connectionStatus.className = `modern-card mb-6 border-l-4 ${getConnectionBorderColor(type)}`;
        connectionText.textContent = status;
        
        // Update icon based on connection status
        switch(type) {
            case 'success':
                connectionIcon.className = 'fas fa-wifi text-2xl text-green-500 status-pulse';
                wsStatus.className = 'status-indicator status-active';
                wsStatus.textContent = 'Connected';
                lastPing.textContent = `Last ping: ${new Date().toLocaleTimeString()}`;
                break;
            case 'danger':
                connectionIcon.className = 'fas fa-wifi-slash text-2xl text-red-500';
                wsStatus.className = 'status-indicator status-error';
                wsStatus.textContent = 'Disconnected';
                lastPing.textContent = 'Last ping: Connection lost';
                break;
            case 'warning':
                connectionIcon.className = 'fas fa-spinner fa-spin text-2xl text-yellow-500 status-pulse';
                wsStatus.className = 'status-indicator status-warning';
                wsStatus.textContent = 'Connecting';
                lastPing.textContent = 'Last ping: Connecting...';
                break;
            default:
                connectionIcon.className = 'fas fa-question-circle text-2xl text-gray-500';
                wsStatus.className = 'status-indicator status-inactive';
                wsStatus.textContent = 'Unknown';
        }
    }
    
    function getConnectionBorderColor(type) {
        switch(type) {
            case 'success': return 'border-green-500';
            case 'danger': return 'border-red-500';
            case 'warning': return 'border-yellow-500';
            default: return 'border-gray-300';
        }
    }

    // Add update to feed
    function addUpdateToFeed(update, type = 'info') {
        const updateDiv = document.createElement('div');
        updateDiv.className = `update-item ${type}`;
        
        const timestamp = new Date().toLocaleTimeString();
        const ticketId = update.ticket_uuid || update.ticket_id || 'Unknown';
        const status = update.status || update.type || 'Update';
        
        updateDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>Ticket ${ticketId}</strong>
                    <p class="mb-1">${status}</p>
                    <small class="text-muted">${timestamp}</small>
                </div>
                <div>
                    <i class="fas fa-${getUpdateIcon(type)}"></i>
                </div>
            </div>
        `;
        
        // Clear placeholder if exists
        const placeholder = updateFeed.querySelector('.text-muted.text-center');
        if (placeholder) {
            placeholder.remove();
        }
        
        updateFeed.insertBefore(updateDiv, updateFeed.firstChild);
        
        // Keep only last 50 updates
        const updates = updateFeed.querySelectorAll('.update-item');
        if (updates.length > 50) {
            updateFeed.removeChild(updates[updates.length - 1]);
        }
        
        // Update counters
        updatesCount++;
        updatesCountEl.textContent = updatesCount;
        lastUpdate.textContent = timestamp;
    }

    function getUpdateIcon(type) {
        switch(type) {
            case 'availability': return 'check-circle';
            case 'price': return 'dollar-sign';
            case 'error': return 'exclamation-triangle';
            default: return 'info-circle';
        }
    }

    // WebSocket event listeners with enhanced error handling
    if (typeof websocketManager !== 'undefined') {
        websocketManager.on('connected', function() {
            updateConnectionStatus('Connected to real-time updates', 'success');
            // Show success notification
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.success('WebSocket connected', 'Real-time updates are now active');
            }
        });

        websocketManager.on('disconnected', function() {
            updateConnectionStatus('Disconnected from real-time updates', 'danger');
            // Show warning notification
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.warning('Connection lost', 'Attempting to reconnect...');
            }
        });

        websocketManager.on('error', function(error) {
            const errorMessage = error?.message || error || 'Unknown error';
            updateConnectionStatus('Connection error: ' + errorMessage, 'danger');
            addUpdateToFeed({ status: 'Connection error: ' + errorMessage }, 'error');
            
            // Show error notification
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.error('Connection Error', errorMessage);
            }
        });

        websocketManager.on('reconnected', function() {
            updateConnectionStatus('Reconnected to real-time updates', 'success');
            addUpdateToFeed({ status: 'Connection restored' }, 'info');
            
            // Show success notification
            if (window.hdTicketsFeedback) {
                window.hdTicketsFeedback.success('Reconnected', 'Real-time updates restored');
            }
        });
    } else {
        console.warn('WebSocket manager not available');
        updateConnectionStatus('WebSocket manager not available', 'danger');
    }

    // Subscribe to ticket updates
    websocketManager.subscribeToTicketUpdates(function(update) {
        console.log('Received ticket update:', update);
        addUpdateToFeed(update, 'availability');
    });

    // Subscribe to price changes
    websocketManager.subscribeToPriceChanges([], function(update) {
        console.log('Received price update:', update);
        addUpdateToFeed(update, 'price');
    });

    // Subscribe to analytics updates
    websocketManager.subscribeToAnalytics(function(update) {
        console.log('Received analytics update:', update);
        updateDashboardStats(update);
    });

    // Button event listeners
    document.getElementById('start-monitoring').addEventListener('click', function() {
        fetch('{{ route("admin.monitoring.start") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isMonitoring = true;
                monitoringStatusEl.textContent = 'Active';
                addUpdateToFeed({ status: 'Monitoring started' }, 'info');
            }
        })
        .catch(error => {
            console.error('Error starting monitoring:', error);
            addUpdateToFeed({ status: 'Failed to start monitoring' }, 'error');
        });
    });

    document.getElementById('stop-monitoring').addEventListener('click', function() {
        fetch('{{ route("admin.monitoring.stop") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                isMonitoring = false;
                monitoringStatusEl.textContent = 'Inactive';
                addUpdateToFeed({ status: 'Monitoring stopped' }, 'info');
            }
        })
        .catch(error => {
            console.error('Error stopping monitoring:', error);
            addUpdateToFeed({ status: 'Failed to stop monitoring' }, 'error');
        });
    });

    document.getElementById('clear-updates').addEventListener('click', function() {
        updateFeed.innerHTML = `
            <div class="text-muted text-center py-4">
                <i class="fas fa-clock fa-2x mb-2"></i>
                <p>Waiting for real-time updates...</p>
            </div>
        `;
        updatesCount = 0;
        updatesCountEl.textContent = updatesCount;
    });

    document.getElementById('refresh-data').addEventListener('click', function() {
        fetchDashboardData();
    });

    document.getElementById('test-websocket').addEventListener('click', function() {
        addUpdateToFeed({ status: 'WebSocket test message', ticket_uuid: 'TEST' }, 'info');
    });

    document.getElementById('test-notification').addEventListener('click', function() {
        fetch('{{ route("admin.monitoring.test-notification") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                type: 'test',
                title: 'Test Notification',
                message: 'This is a test notification from the monitoring dashboard',
                priority: 'normal'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                addUpdateToFeed({ status: 'Test notification sent' }, 'info');
            }
        })
        .catch(error => {
            console.error('Error sending test notification:', error);
        });
    });

    // Fetch dashboard data
    function fetchDashboardData() {
        fetch('{{ route("admin.monitoring.data") }}')
            .then(response => response.json())
            .then(data => {
                updateDashboardStats(data);
            })
            .catch(error => {
                console.error('Error fetching dashboard data:', error);
            });
    }

    function updateDashboardStats(data) {
        if (data.monitoring) {
            document.getElementById('watched-tickets-count').textContent = 
                data.monitoring.monitoring_status?.watched_tickets || 0;
            
            const status = data.monitoring.monitoring_status?.active ? 'Active' : 'Inactive';
            monitoringStatusEl.textContent = status;
        }
        
        if (data.scrapers) {
            // Update scraper stats
            document.getElementById('active-scrapers').textContent = 
                Object.keys(data.scrapers).length || 0;
        }
        
        if (data.alerts_sent !== undefined) {
            document.getElementById('alerts-sent').textContent = data.alerts_sent;
        }
    }

    // Initial data fetch
    fetchDashboardData();

    // Periodic data refresh
    setInterval(fetchDashboardData, 30000); // Every 30 seconds

    // Initial connection status
    updateConnectionStatus('Initializing WebSocket connection...', 'warning');
});
</script>
@endsection

