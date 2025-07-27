@extends('layouts.app')

@section('title', 'Real-time Monitoring Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><strong>Real-time Monitoring</strong> Dashboard</h1>
        <div class="btn-toolbar" role="toolbar">
            <div class="btn-group me-2" role="group">
                <button type="button" class="btn btn-success" id="start-monitoring">
                    <i class="fas fa-play"></i> Start Monitoring
                </button>
                <button type="button" class="btn btn-danger" id="stop-monitoring">
                    <i class="fas fa-stop"></i> Stop Monitoring
                </button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-info" id="refresh-data">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Connection Status -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info" id="connection-status">
                <i class="fas fa-wifi"></i> Connecting to real-time updates...
            </div>
        </div>
    </div>

    <!-- Monitoring Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="watched-tickets-count">-</h4>
                            <p class="mb-0">Watched Tickets</p>
                        </div>
                        <div>
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="active-scrapers">-</h4>
                            <p class="mb-0">Active Scrapers</p>
                        </div>
                        <div>
                            <i class="fas fa-spider fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="alerts-sent">-</h4>
                            <p class="mb-0">Alerts Sent</p>
                        </div>
                        <div>
                            <i class="fas fa-bell fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0" id="monitoring-status">Inactive</h4>
                            <p class="mb-0">Status</p>
                        </div>
                        <div>
                            <i class="fas fa-heartbeat fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Updates and Analytics -->
    <div class="row">
        <!-- Live Ticket Updates -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Live Ticket Updates</h5>
                    <button class="btn btn-sm btn-outline-secondary" id="clear-updates">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                </div>
                <div class="card-body">
                    <div class="update-feed" id="update-feed" style="height: 400px; overflow-y: auto;">
                        <div class="text-muted text-center py-4">
                            <i class="fas fa-clock fa-2x mb-2"></i>
                            <p>Waiting for real-time updates...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health & Settings -->
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">System Health</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Connection Status:</span>
                            <span class="badge badge-secondary" id="ws-status">Disconnected</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Last Update:</span>
                            <small class="text-muted" id="last-update">Never</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Updates Count:</span>
                            <span id="updates-count">0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="test-websocket">
                            <i class="fas fa-plug"></i> Test WebSocket
                        </button>
                        <button class="btn btn-outline-info btn-sm" id="fetch-stats">
                            <i class="fas fa-chart-bar"></i> Fetch Statistics
                        </button>
                        <button class="btn btn-outline-warning btn-sm" id="test-notification">
                            <i class="fas fa-bell"></i> Test Notification
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('resources/css/app.css') }}?{{ cssTimestamp() }}" rel="stylesheet">

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
        connectionStatus.className = `alert alert-${type}`;
        connectionStatus.innerHTML = `<i class="fas fa-wifi"></i> ${status}`;
        
        const statusBadge = wsStatus;
        statusBadge.className = 'badge ';
        
        switch(type) {
            case 'success':
                statusBadge.classList.add('badge-connected');
                statusBadge.textContent = 'Connected';
                break;
            case 'danger':
                statusBadge.classList.add('badge-disconnected');
                statusBadge.textContent = 'Disconnected';
                break;
            case 'warning':
                statusBadge.classList.add('badge-connecting');
                statusBadge.textContent = 'Connecting';
                break;
            default:
                statusBadge.classList.add('badge-secondary');
                statusBadge.textContent = 'Unknown';
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

    // WebSocket event listeners
    websocketManager.on('connected', function() {
        updateConnectionStatus('Connected to real-time updates', 'success');
    });

    websocketManager.on('disconnected', function() {
        updateConnectionStatus('Disconnected from real-time updates', 'danger');
    });

    websocketManager.on('error', function(error) {
        updateConnectionStatus('Connection error: ' + error, 'danger');
        addUpdateToFeed({ status: 'Connection error occurred' }, 'error');
    });

    websocketManager.on('reconnected', function() {
        updateConnectionStatus('Reconnected to real-time updates', 'success');
        addUpdateToFeed({ status: 'Connection restored' }, 'info');
    });

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

