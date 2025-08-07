<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>WebSocket Testing Dashboard - {{ config('app.name', 'HD Tickets') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        // Provide Laravel config to JavaScript
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            user: @auth {
                id: {{ auth()->id() }},
                name: '{{ auth()->user()->name }}',
                email: '{{ auth()->user()->email }}'
            } @else null @endauth,
            pusher: {
                key: '{{ env('VITE_PUSHER_APP_KEY', 'hd-tickets-key') }}',
                cluster: '{{ env('VITE_PUSHER_APP_CLUSTER', '') }}',
                host: '{{ env('VITE_PUSHER_HOST', '127.0.0.1') }}',
                port: '{{ env('VITE_PUSHER_PORT', '6001') }}'
            }
        };
    </script>

    <style>
        .status-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .test-section {
            @apply bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6;
        }
        
        .btn {
            @apply px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200;
        }
        
        .btn-primary {
            @apply bg-blue-500 text-white hover:bg-blue-600;
        }
        
        .btn-success {
            @apply bg-green-500 text-white hover:bg-green-600;
        }
        
        .btn-warning {
            @apply bg-yellow-500 text-white hover:bg-yellow-600;
        }
        
        .btn-danger {
            @apply bg-red-500 text-white hover:bg-red-600;
        }
        
        .log-output {
            @apply bg-gray-100 dark:bg-gray-900 border rounded-md p-4 font-mono text-sm max-h-96 overflow-y-auto;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
    <!-- WebSocket Status Indicator -->
    <div class="status-indicator" id="websocket-status">
        <!-- Status indicator will be mounted here -->
    </div>

    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            WebSocket Testing Dashboard
                        </h1>
                    </div>
                    <nav class="flex space-x-4">
                        <a href="{{ url('/') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            ← Back to Dashboard
                        </a>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Connection Overview -->
            <div class="test-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Connection Status
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 border rounded-lg">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300">Connection Type</h3>
                        <p id="connection-type" class="text-2xl font-bold text-blue-600">Unknown</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300">Status</h3>
                        <p id="connection-status" class="text-2xl font-bold text-red-600">Disconnected</p>
                    </div>
                    <div class="p-4 border rounded-lg">
                        <h3 class="font-medium text-gray-700 dark:text-gray-300">Reconnect Attempts</h3>
                        <p id="reconnect-attempts" class="text-2xl font-bold text-gray-600">0</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="test-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Quick Actions
                </h2>
                <div class="flex flex-wrap gap-3">
                    <button onclick="runDiagnostics()" class="btn btn-primary">
                        🔬 Run Diagnostics
                    </button>
                    <button onclick="testBasicConnection()" class="btn btn-success">
                        🧪 Test Connection
                    </button>
                    <button onclick="testSubscriptions()" class="btn btn-warning">
                        📡 Test Subscriptions
                    </button>
                    <button onclick="reconnectWebSocket()" class="btn btn-danger">
                        🔄 Force Reconnect
                    </button>
                    <button onclick="clearLogs()" class="btn btn-secondary">
                        🧹 Clear Logs
                    </button>
                </div>
            </div>

            <!-- Subscription Tests -->
            <div class="test-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Subscription Tests
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button onclick="testTicketUpdates()" class="btn btn-primary">
                        🎟️ Ticket Updates
                    </button>
                    <button onclick="testAnalytics()" class="btn btn-primary">
                        📊 Analytics
                    </button>
                    <button onclick="testPlatformMonitoring()" class="btn btn-primary">
                        🖥️ Platform Status
                    </button>
                    <button onclick="testNotifications()" class="btn btn-primary">
                        🔔 Notifications
                    </button>
                </div>
            </div>

            <!-- Live Event Monitor -->
            <div class="test-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Live Event Monitor
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Recent Events</h3>
                        <div id="event-log" class="log-output">
                            <div class="text-gray-500">No events received yet...</div>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Connection Log</h3>
                        <div id="connection-log" class="log-output">
                            <div class="text-gray-500">Initializing...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Server Information -->
            <div class="test-section">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Server Configuration
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Soketi Configuration</h3>
                        <ul class="space-y-1 text-gray-600 dark:text-gray-400">
                            <li><strong>Host:</strong> {{ env('PUSHER_HOST', '127.0.0.1') }}</li>
                            <li><strong>Port:</strong> {{ env('PUSHER_PORT', '6001') }}</li>
                            <li><strong>App ID:</strong> {{ env('PUSHER_APP_ID', 'hd-tickets-app') }}</li>
                            <li><strong>Key:</strong> {{ env('PUSHER_APP_KEY', 'hd-tickets-key') }}</li>
                            <li><strong>Scheme:</strong> {{ env('PUSHER_SCHEME', 'http') }}</li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Broadcasting</h3>
                        <ul class="space-y-1 text-gray-600 dark:text-gray-400">
                            <li><strong>Driver:</strong> {{ config('broadcasting.default') }}</li>
                            <li><strong>Environment:</strong> {{ app()->environment() }}</li>
                            <li><strong>Debug:</strong> {{ config('app.debug') ? 'Enabled' : 'Disabled' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Global variables for testing
        let eventCount = 0;
        let connectionLogCount = 0;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializeStatusMonitoring();
            setupWebSocketEventListeners();
            logConnection('Page loaded, initializing WebSocket monitoring...');
        });

        // Status monitoring
        function initializeStatusMonitoring() {
            updateConnectionStatus();
            setInterval(updateConnectionStatus, 2000);
        }

        function updateConnectionStatus() {
            if (window.websocketManager) {
                const status = window.websocketManager.getConnectionStatus();
                
                document.getElementById('connection-type').textContent = status.connectionType || 'Unknown';
                document.getElementById('connection-status').textContent = status.isConnected ? 'Connected' : 'Disconnected';
                document.getElementById('connection-status').className = `text-2xl font-bold ${status.isConnected ? 'text-green-600' : 'text-red-600'}`;
                document.getElementById('reconnect-attempts').textContent = status.reconnectAttempts || '0';
            }
        }

        // WebSocket event listeners
        function setupWebSocketEventListeners() {
            if (window.websocketManager) {
                window.websocketManager.on('connected', () => {
                    logConnection('✅ WebSocket connected');
                    updateConnectionStatus();
                });
                
                window.websocketManager.on('disconnected', () => {
                    logConnection('❌ WebSocket disconnected');
                    updateConnectionStatus();
                });
                
                window.websocketManager.on('error', (error) => {
                    logConnection(`❌ WebSocket error: ${error}`);
                });
                
                // Listen for test events
                window.websocketManager.on('ticket-updated', (data) => {
                    logEvent('🎟️ Ticket Update', data);
                });
                
                window.websocketManager.on('analytics-updated', (data) => {
                    logEvent('📊 Analytics Update', data);
                });
                
                window.websocketManager.on('platform-status-updated', (data) => {
                    logEvent('🖥️ Platform Status', data);
                });
            }
        }

        // Test functions
        function runDiagnostics() {
            if (window.WebSocketTester) {
                window.WebSocketTester.diagnose();
                logConnection('🔬 Running diagnostics (check console for details)');
            } else {
                logConnection('❌ WebSocket tester not available');
            }
        }

        function testBasicConnection() {
            if (window.WebSocketTester) {
                window.WebSocketTester.testConnection();
                logConnection('🧪 Testing basic connection');
            } else if (window.websocketManager) {
                window.websocketManager.emit('test-event', {
                    message: 'Basic connection test',
                    timestamp: new Date().toISOString()
                });
                logConnection('🧪 Basic connection test sent');
            }
        }

        function testSubscriptions() {
            if (window.WebSocketTester) {
                window.WebSocketTester.runFullTest();
                logConnection('📡 Running full subscription test suite');
            }
        }

        function reconnectWebSocket() {
            if (window.websocketManager && typeof window.websocketManager.reconnect === 'function') {
                window.websocketManager.reconnect();
                logConnection('🔄 Forcing reconnection...');
            }
        }

        function testTicketUpdates() {
            if (window.websocketManager) {
                // Subscribe if not already subscribed
                window.websocketManager.subscribeToTicketUpdates((data) => {
                    logEvent('🎟️ Real Ticket Update', data);
                });
                
                // Simulate an update
                setTimeout(() => {
                    window.websocketManager.emit('ticket-updated', {
                        id: `test-${Date.now()}`,
                        event: 'Test Sports Event',
                        venue: 'Test Stadium',
                        availability: 'available',
                        price: Math.floor(Math.random() * 200) + 50,
                        timestamp: new Date().toISOString()
                    });
                }, 500);
                
                logConnection('🎟️ Testing ticket updates...');
            }
        }

        function testAnalytics() {
            if (window.websocketManager) {
                window.websocketManager.subscribeToAnalytics((data) => {
                    logEvent('📊 Real Analytics Update', data);
                });
                
                setTimeout(() => {
                    window.websocketManager.emit('analytics-updated', {
                        active_users: Math.floor(Math.random() * 100) + 10,
                        active_monitors: Math.floor(Math.random() * 50) + 5,
                        total_tickets_found: Math.floor(Math.random() * 1000) + 100,
                        timestamp: new Date().toISOString()
                    });
                }, 500);
                
                logConnection('📊 Testing analytics updates...');
            }
        }

        function testPlatformMonitoring() {
            if (window.websocketManager) {
                window.websocketManager.subscribeToPlatformMonitoring((data) => {
                    logEvent('🖥️ Real Platform Update', data);
                });
                
                setTimeout(() => {
                    window.websocketManager.emit('platform-status-updated', {
                        platform: 'ticketmaster',
                        status: Math.random() > 0.5 ? 'online' : 'degraded',
                        response_time: Math.floor(Math.random() * 500) + 100,
                        timestamp: new Date().toISOString()
                    });
                }, 500);
                
                logConnection('🖥️ Testing platform monitoring...');
            }
        }

        function testNotifications() {
            if (window.websocketManager) {
                window.websocketManager.on('user-notification', (data) => {
                    logEvent('🔔 User Notification', data);
                });
                
                setTimeout(() => {
                    window.websocketManager.emit('user-notification', {
                        title: 'Test Notification',
                        message: 'This is a test notification from the WebSocket system',
                        type: 'info',
                        timestamp: new Date().toISOString()
                    });
                }, 500);
                
                logConnection('🔔 Testing notifications...');
            }
        }

        // Logging functions
        function logEvent(title, data) {
            eventCount++;
            const log = document.getElementById('event-log');
            const time = new Date().toLocaleTimeString();
            const eventHtml = `
                <div class="border-b border-gray-200 pb-2 mb-2">
                    <div class="flex justify-between items-start">
                        <span class="font-medium text-blue-600">${title}</span>
                        <span class="text-xs text-gray-500">${time}</span>
                    </div>
                    <pre class="text-xs text-gray-600 mt-1 whitespace-pre-wrap">${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
            
            if (eventCount === 1) {
                log.innerHTML = eventHtml;
            } else {
                log.insertAdjacentHTML('afterbegin', eventHtml);
            }
            
            // Keep only last 10 events
            const events = log.querySelectorAll('div');
            if (events.length > 10) {
                events[events.length - 1].remove();
            }
        }

        function logConnection(message) {
            connectionLogCount++;
            const log = document.getElementById('connection-log');
            const time = new Date().toLocaleTimeString();
            const logHtml = `<div class="text-sm text-gray-700 dark:text-gray-300 mb-1">[${time}] ${message}</div>`;
            
            if (connectionLogCount === 1) {
                log.innerHTML = logHtml;
            } else {
                log.insertAdjacentHTML('afterbegin', logHtml);
            }
            
            // Keep only last 20 log entries
            const entries = log.querySelectorAll('div');
            if (entries.length > 20) {
                entries[entries.length - 1].remove();
            }
        }

        function clearLogs() {
            document.getElementById('event-log').innerHTML = '<div class="text-gray-500">No events received yet...</div>';
            document.getElementById('connection-log').innerHTML = '<div class="text-gray-500">Logs cleared...</div>';
            eventCount = 0;
            connectionLogCount = 0;
        }

        // Add some styles for better button appearance
        const style = document.createElement('style');
        style.textContent = `
            .btn-secondary {
                background-color: #6b7280;
                color: white;
            }
            .btn-secondary:hover {
                background-color: #4b5563;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
