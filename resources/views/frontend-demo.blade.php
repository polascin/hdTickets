@extends('layouts.app')

@section('title', 'Frontend Development Environment Demo')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <h1 class="text-3xl font-bold text-gray-900">Modern Frontend Development Environment</h1>
                <p class="mt-2 text-sm text-gray-600">
                    Showcasing Vue.js 3 with Composition API, Vite optimization, Tailwind CSS custom theme, 
                    Chart.js visualizations, WebSocket real-time updates, and mobile-first responsive design.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        {{-- Responsive Utilities Demo --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4z"/>
                </svg>
                Responsive Design System
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900">Current Viewport</h3>
                    <div id="viewport-info" class="mt-2 text-sm text-gray-600">
                        <div>Width: <span id="viewport-width">--</span>px</div>
                        <div>Breakpoint: <span id="viewport-breakpoint" class="font-medium">--</span></div>
                        <div>Device: <span id="device-type" class="font-medium">--</span></div>
                    </div>
                </div>
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900">Touch Support</h3>
                    <div class="mt-2">
                        <span id="touch-support" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">--</span>
                    </div>
                </div>
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900">Orientation</h3>
                    <div class="mt-2">
                        <span id="orientation" class="inline-flex px-2 py-1 text-xs font-medium rounded-full">--</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sports Theme Demo --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-football-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Sports Ticket Theme
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="sport-badge-football px-3 py-2 rounded-md text-center border">
                    <div class="font-medium">Football</div>
                    <div class="text-xs">Primary Sport</div>
                </div>
                <div class="sport-badge-basketball px-3 py-2 rounded-md text-center border">
                    <div class="font-medium">Basketball</div>
                    <div class="text-xs">Orange Theme</div>
                </div>
                <div class="sport-badge-baseball px-3 py-2 rounded-md text-center border">
                    <div class="font-medium">Baseball</div>
                    <div class="text-xs">Gray Theme</div>
                </div>
                <div class="sport-badge-tennis px-3 py-2 rounded-md text-center border">
                    <div class="font-medium">Tennis</div>
                    <div class="text-xs">Green Theme</div>
                </div>
            </div>
            
            <div class="mt-6">
                <h3 class="font-medium text-gray-900 mb-3">Ticket Status Examples</h3>
                <div class="flex flex-wrap gap-2">
                    <span class="ticket-status-available px-3 py-1 rounded-full text-sm font-medium border">Available</span>
                    <span class="ticket-status-pending px-3 py-1 rounded-full text-sm font-medium border">Pending</span>
                    <span class="ticket-status-sold px-3 py-1 rounded-full text-sm font-medium border">Sold Out</span>
                </div>
            </div>
        </div>

        {{-- Chart.js Demo --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M16 8v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2m8 4V6a2 2 0 00-2-2h-2m4 8V8"/>
                </svg>
                Interactive Charts & Analytics
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900 mb-3">Ticket Availability Status</h3>
                    <div class="relative h-64">
                        <canvas id="availability-chart"></canvas>
                    </div>
                </div>
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900 mb-3">Price Trends</h3>
                    <div class="relative h-64">
                        <canvas id="price-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- WebSocket Demo --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                </svg>
                Real-time WebSocket Connection
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="ticket-card text-center">
                    <div class="text-sm text-gray-600">Connection Status</div>
                    <div id="ws-status" class="mt-1 font-medium">Checking...</div>
                    <div id="ws-status-dot" class="mx-auto mt-2 h-3 w-3 rounded-full bg-gray-400"></div>
                </div>
                <div class="ticket-card text-center">
                    <div class="text-sm text-gray-600">Connection Type</div>
                    <div id="ws-type" class="mt-1 font-medium">--</div>
                </div>
                <div class="ticket-card text-center">
                    <div class="text-sm text-gray-600">Reconnect Attempts</div>
                    <div id="ws-attempts" class="mt-1 font-medium">0</div>
                </div>
            </div>
            <div class="mt-4">
                <button id="ws-test-btn" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Test WebSocket Connection
                </button>
                <button id="ws-reconnect-btn" class="ml-2 bg-secondary-500 hover:bg-secondary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                    Force Reconnection
                </button>
            </div>
        </div>

        {{-- CSS Timestamp Demo --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"/>
                </svg>
                CSS Cache Prevention System
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900 mb-2">Cache Information</h3>
                    <div class="text-sm space-y-1">
                        <div>Cache Size: <span id="css-cache-size" class="font-medium">--</span></div>
                        <div>Last Updated: <span id="css-last-update" class="font-medium">--</span></div>
                    </div>
                </div>
                <div class="ticket-card">
                    <h3 class="font-medium text-gray-900 mb-2">Actions</h3>
                    <div class="space-x-2">
                        <button id="css-reload-btn" class="bg-primary-500 hover:bg-primary-600 text-white px-3 py-1 rounded text-sm transition-colors">
                            Reload CSS
                        </button>
                        <button id="css-clear-btn" class="bg-secondary-500 hover:bg-secondary-600 text-white px-3 py-1 rounded text-sm transition-colors">
                            Clear Cache
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Features --}}
        <div class="mb-8 bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-4l-3 3-3-3H5a2 2 0 01-2-2V5z"/>
                </svg>
                Mobile-First Features
            </h2>
            <div class="space-y-4">
                <div class="text-sm text-gray-600">
                    This system automatically adapts to different screen sizes and device capabilities:
                </div>
                <ul class="text-sm space-y-2">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-available-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                        Responsive breakpoints: mobile (< 768px), tablet (768px-1024px), desktop (> 1024px)
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-available-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                        Touch gesture support and hover state management
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-available-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                        High-DPI display support and optimized images
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-available-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/>
                        </svg>
                        Performance optimized with code splitting and lazy loading
                    </li>
                </ul>
            </div>
        </div>

        {{-- Technical Implementation --}}
        <div class="bg-white rounded-ticket shadow-ticket p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <svg class="inline w-5 h-5 mr-2 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Technical Implementation Details
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Frontend Technologies</h3>
                    <ul class="text-sm space-y-2">
                        <li>✅ Vue.js 3 with Composition API</li>
                        <li>✅ Vite 6.x for optimal build performance</li>
                        <li>✅ Tailwind CSS 3.x with custom sports theme</li>
                        <li>✅ Chart.js 4.x with date-fns adapter</li>
                        <li>✅ Laravel Echo & Pusher.js for WebSockets</li>
                        <li>✅ Socket.IO client as fallback</li>
                        <li>✅ VueUse utilities for enhanced reactivity</li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-medium text-gray-900 mb-3">Build Optimizations</h3>
                    <ul class="text-sm space-y-2">
                        <li>✅ Code splitting with manual chunks</li>
                        <li>✅ CSS code splitting enabled</li>
                        <li>✅ Terser minification for production</li>
                        <li>✅ Tree shaking for smaller bundles</li>
                        <li>✅ Asset optimization and caching</li>
                        <li>✅ Hot module replacement in development</li>
                        <li>✅ Source maps for debugging</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update viewport information
    function updateViewportInfo() {
        if (typeof window.responsiveUtils !== 'undefined') {
            const viewport = window.responsiveUtils.getViewport();
            document.getElementById('viewport-width').textContent = viewport.width;
            document.getElementById('viewport-breakpoint').textContent = viewport.breakpoint;
            document.getElementById('device-type').textContent = 
                viewport.isMobile ? 'Mobile' : viewport.isTablet ? 'Tablet' : 'Desktop';
            document.getElementById('orientation').textContent = viewport.orientation;
            document.getElementById('orientation').className = 
                `inline-flex px-2 py-1 text-xs font-medium rounded-full ${viewport.orientation === 'portrait' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'}`;
            
            const touchSupport = document.getElementById('touch-support');
            touchSupport.textContent = viewport.supportsTouch ? 'Supported' : 'Not Supported';
            touchSupport.className = `inline-flex px-2 py-1 text-xs font-medium rounded-full ${viewport.supportsTouch ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}`;
        }
    }

    // Update WebSocket status
    function updateWebSocketStatus() {
        if (typeof window.websocketManager !== 'undefined') {
            const status = window.websocketManager.getConnectionStatus();
            const statusEl = document.getElementById('ws-status');
            const dotEl = document.getElementById('ws-status-dot');
            const typeEl = document.getElementById('ws-type');
            const attemptsEl = document.getElementById('ws-attempts');

            statusEl.textContent = status.isConnected ? 'Connected' : 'Disconnected';
            dotEl.className = `mx-auto mt-2 h-3 w-3 rounded-full ${status.isConnected ? 'bg-green-400' : 'bg-red-400'}`;
            typeEl.textContent = status.connectionType || 'None';
            attemptsEl.textContent = status.reconnectAttempts;
        }
    }

    // Update CSS cache info
    function updateCSSCacheInfo() {
        if (typeof window.cssTimestamp !== 'undefined') {
            const info = window.cssTimestamp.getCacheInfo();
            document.getElementById('css-cache-size').textContent = info.size;
            document.getElementById('css-last-update').textContent = new Date().toLocaleTimeString();
        }
    }

    // Initialize charts
    function initializeCharts() {
        // Availability Chart
        const availabilityCtx = document.getElementById('availability-chart');
        if (availabilityCtx && typeof Chart !== 'undefined') {
            new Chart(availabilityCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Available', 'Sold', 'Pending'],
                    datasets: [{
                        data: [245, 89, 12],
                        backgroundColor: ['#22c55e', '#ef4444', '#eab308'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }

        // Price Chart
        const priceCtx = document.getElementById('price-chart');
        if (priceCtx && typeof Chart !== 'undefined') {
            new Chart(priceCtx, {
                type: 'line',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [{
                        label: 'Average Price',
                        data: [120, 135, 110, 145, 160, 155, 140],
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // Event listeners
    document.getElementById('ws-test-btn')?.addEventListener('click', function() {
        if (typeof window.websocketManager !== 'undefined') {
            window.websocketManager.send('test', { message: 'Test connection' });
            alert('Test message sent via WebSocket');
        }
    });

    document.getElementById('ws-reconnect-btn')?.addEventListener('click', function() {
        if (typeof window.websocketManager !== 'undefined') {
            window.websocketManager.reconnect();
            setTimeout(updateWebSocketStatus, 1000);
        }
    });

    document.getElementById('css-reload-btn')?.addEventListener('click', function() {
        if (typeof window.updateAllCSS !== 'undefined') {
            window.updateAllCSS();
            setTimeout(updateCSSCacheInfo, 500);
        }
    });

    document.getElementById('css-clear-btn')?.addEventListener('click', function() {
        if (typeof window.cssTimestamp !== 'undefined') {
            window.cssTimestamp.clearCache();
            updateCSSCacheInfo();
        }
    });

    // Listen for viewport changes
    window.addEventListener('viewport:change', updateViewportInfo);

    // Initialize all components
    updateViewportInfo();
    updateWebSocketStatus();
    updateCSSCacheInfo();
    initializeCharts();

    // Update WebSocket status periodically
    setInterval(updateWebSocketStatus, 5000);
});
</script>
@endsection
