@extends('layouts.app')

@section('title', 'PWA Features Test')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <span class="connection-indicator"></span>
            PWA Features Test
        </h2>
        <div class="flex space-x-2">
            <button onclick="pwaManager.showNotificationPreferences()" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                Notification Settings
            </button>
        </div>
    </div>
@endsection

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- PWA Status Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">PWA Status</h3>
        <div id="pwa-status" class="space-y-2">
            <div class="flex justify-between">
                <span>Service Worker:</span>
                <span id="sw-status" class="text-red-600">Loading...</span>
            </div>
            <div class="flex justify-between">
                <span>Installed:</span>
                <span id="install-status" class="text-red-600">Loading...</span>
            </div>
            <div class="flex justify-between">
                <span>Online:</span>
                <span id="online-status" class="text-red-600">Loading...</span>
            </div>
            <div class="flex justify-between">
                <span>Notifications:</span>
                <span id="notification-status" class="text-red-600">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Install PWA Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Install App</h3>
        <p class="text-gray-600 mb-4">Install HD Tickets as a native app for the best experience.</p>
        <div class="space-y-2">
            <button onclick="installPWA()" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                📱 Install App
            </button>
            <button onclick="pwaManager.showInstallBanner()" class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                Show Install Banner
            </button>
        </div>
    </div>

    <!-- Push Notifications Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Push Notifications</h3>
        <p class="text-gray-600 mb-4">Test push notification features.</p>
        <div class="space-y-2">
            <button onclick="requestNotifications()" class="w-full bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                🔔 Enable Notifications
            </button>
            <button onclick="testNotification()" class="w-full bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600 transition">
                ⚡ Test Notification
            </button>
            <button onclick="pwaManager.setupAdvancedPushNotifications()" class="w-full bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition">
                ⚙️ Advanced Setup
            </button>
        </div>
    </div>

    <!-- Cache Management Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cache Management</h3>
        <p class="text-gray-600 mb-4">Manage offline data and cache.</p>
        <div class="space-y-2">
            <button onclick="getCacheInfo()" class="w-full bg-indigo-500 text-white px-4 py-2 rounded-lg hover:bg-indigo-600 transition">
                📊 Cache Info
            </button>
            <button onclick="clearCache()" class="w-full bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">
                🗑️ Clear Cache
            </button>
            <button onclick="triggerSync()" class="w-full bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition">
                🔄 Trigger Sync
            </button>
        </div>
    </div>

    <!-- Offline Test Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Offline Features</h3>
        <p class="text-gray-600 mb-4">Test offline functionality.</p>
        <div class="space-y-2">
            <button onclick="testOfflineStorage()" class="w-full bg-teal-500 text-white px-4 py-2 rounded-lg hover:bg-teal-600 transition">
                💾 Test Offline Storage
            </button>
            <button onclick="simulateOffline()" class="w-full bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
                📵 Simulate Offline
            </button>
        </div>
    </div>

    <!-- Mobile Features Card -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Mobile Features</h3>
        <p class="text-gray-600 mb-4">Test mobile-specific PWA features.</p>
        <div class="space-y-2">
            <button onclick="testTouchGestures()" class="w-full bg-pink-500 text-white px-4 py-2 rounded-lg hover:bg-pink-600 transition">
                👆 Touch Gestures
            </button>
            <button onclick="testShare()" class="w-full bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                📤 Web Share API
            </button>
        </div>
    </div>
</div>

<!-- Test Results -->
<div class="mt-8">
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Test Results</h3>
        <div id="test-results" class="bg-gray-50 p-4 rounded-lg min-h-32 font-mono text-sm overflow-auto">
            <p class="text-gray-500">Test results will appear here...</p>
        </div>
        <button onclick="clearResults()" class="mt-2 bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition">
            Clear Results
        </button>
    </div>
</div>

<script>
// PWA Test Functions
function updatePWAStatus() {
    if (window.pwaManager) {
        const status = window.pwaManager.getStatus();
        
        document.getElementById('sw-status').textContent = status.hasServiceWorker ? '✅ Active' : '❌ Not Active';
        document.getElementById('sw-status').className = status.hasServiceWorker ? 'text-green-600' : 'text-red-600';
        
        document.getElementById('install-status').textContent = status.isInstalled ? '✅ Installed' : '❌ Not Installed';
        document.getElementById('install-status').className = status.isInstalled ? 'text-green-600' : 'text-red-600';
        
        document.getElementById('online-status').textContent = status.isOnline ? '✅ Online' : '❌ Offline';
        document.getElementById('online-status').className = status.isOnline ? 'text-green-600' : 'text-red-600';
        
        document.getElementById('notification-status').textContent = status.notificationPermission === 'granted' ? '✅ Granted' : '❌ ' + status.notificationPermission;
        document.getElementById('notification-status').className = status.notificationPermission === 'granted' ? 'text-green-600' : 'text-red-600';
    }
}

function logResult(message) {
    const results = document.getElementById('test-results');
    const timestamp = new Date().toLocaleTimeString();
    results.innerHTML += `<div>[${timestamp}] ${message}</div>`;
    results.scrollTop = results.scrollHeight;
}

function clearResults() {
    document.getElementById('test-results').innerHTML = '<p class="text-gray-500">Test results will appear here...</p>';
}

async function requestNotifications() {
    try {
        if (window.pwaManager) {
            const permission = await window.pwaManager.requestNotificationPermission();
            logResult(`Notification permission: ${permission}`);
            updatePWAStatus();
        }
    } catch (error) {
        logResult(`Error requesting notifications: ${error.message}`);
    }
}

function testNotification() {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('HD Tickets Test', {
            body: 'This is a test notification from HD Tickets PWA!',
            icon: '/assets/images/pwa/icon-192x192.png',
            badge: '/assets/images/pwa/icon-72x72.png',
            tag: 'test-notification'
        });
        logResult('Test notification sent');
    } else {
        logResult('Notifications not enabled');
    }
}

async function getCacheInfo() {
    try {
        if ('caches' in window) {
            const cacheNames = await caches.keys();
            logResult(`Found ${cacheNames.length} caches: ${cacheNames.join(', ')}`);
            
            let totalEntries = 0;
            for (const cacheName of cacheNames) {
                const cache = await caches.open(cacheName);
                const keys = await cache.keys();
                totalEntries += keys.length;
                logResult(`${cacheName}: ${keys.length} entries`);
            }
            logResult(`Total cached entries: ${totalEntries}`);
        }
    } catch (error) {
        logResult(`Error getting cache info: ${error.message}`);
    }
}

async function clearCache() {
    try {
        if (window.pwaManager && window.pwaManager.swRegistration) {
            const sw = window.pwaManager.swRegistration;
            sw.postMessage({ type: 'CLEAR_CACHE' });
            logResult('Cache clear requested');
        }
    } catch (error) {
        logResult(`Error clearing cache: ${error.message}`);
    }
}

function triggerSync() {
    try {
        if (window.pwaManager) {
            window.pwaManager.triggerBackgroundSync();
            logResult('Background sync triggered');
        }
    } catch (error) {
        logResult(`Error triggering sync: ${error.message}`);
    }
}

function testOfflineStorage() {
    try {
        // Test localStorage
        localStorage.setItem('pwa-test', JSON.stringify({
            timestamp: Date.now(),
            message: 'PWA test data'
        }));
        
        const data = JSON.parse(localStorage.getItem('pwa-test'));
        logResult(`Offline storage test successful: ${data.message}`);
        
        // Add to sync queue
        if (window.pwaManager) {
            window.pwaManager.addToSyncQueue('test', { action: 'pwa-test', data: 'test data' });
            logResult('Added test data to sync queue');
        }
    } catch (error) {
        logResult(`Error testing offline storage: ${error.message}`);
    }
}

function simulateOffline() {
    // Simulate offline event
    window.dispatchEvent(new Event('offline'));
    logResult('Simulated offline event');
    
    setTimeout(() => {
        window.dispatchEvent(new Event('online'));
        logResult('Simulated back online');
    }, 3000);
}

function testTouchGestures() {
    logResult('Touch gesture test: Try swiping left or right on the page');
    
    // Listen for custom swipe events
    window.addEventListener('swipe:left', () => {
        logResult('Left swipe detected!');
    }, { once: true });
    
    window.addEventListener('swipe:right', () => {
        logResult('Right swipe detected!');
    }, { once: true });
}

async function testShare() {
    if ('share' in navigator) {
        try {
            await navigator.share({
                title: 'HD Tickets PWA',
                text: 'Check out this amazing sports ticket monitoring system!',
                url: window.location.href
            });
            logResult('Share successful');
        } catch (error) {
            logResult(`Share failed: ${error.message}`);
        }
    } else {
        logResult('Web Share API not supported');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePWAStatus();
    
    // Update status every 5 seconds
    setInterval(updatePWAStatus, 5000);
    
    logResult('PWA Test page loaded');
});

// Listen for PWA events
window.addEventListener('online', () => logResult('Connection restored'));
window.addEventListener('offline', () => logResult('Connection lost'));

// Service worker messages
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data && event.data.type === 'SW_PERFORMANCE') {
            logResult(`SW Performance - Install: ${event.data.installTime}ms, Activate: ${event.data.activateTime}ms`);
        }
    });
}
</script>
@endsection
