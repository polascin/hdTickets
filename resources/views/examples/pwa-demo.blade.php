@extends('layouts.app-v2')

@section('title', 'PWA Features Demo')

@section('content')
{{-- Include PWA Manager --}}
<script src="{{ asset('js/pwa-manager.js') }}" defer></script>

<div class="container container--xl" id="main-content">
    {{-- Header Section --}}
    <header class="text-center space-y-lg mb-2xl">
        <h1 class="text-4xl">Progressive Web App Features</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Experience HD Tickets as a Progressive Web App with offline functionality, 
            push notifications, background sync, and installable app features.
        </p>
        
        {{-- PWA Status Panel --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-lg mb-xl" role="region" aria-labelledby="pwa-status">
            <h2 id="pwa-status" class="text-lg font-semibold text-blue-900 mb-md">PWA Status</h2>
            <div class="grid grid--4 gap-md text-sm">
                <div class="bg-white rounded p-md">
                    <strong>Service Worker:</strong>
                    <span id="sw-status" class="font-mono">Checking...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Installation:</strong>
                    <span id="install-status" class="font-mono">Checking...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Notifications:</strong>
                    <span id="notification-status" class="font-mono">Checking...</span>
                </div>
                <div class="bg-white rounded p-md">
                    <strong>Connection:</strong>
                    <span id="connection-status" class="font-mono">Online</span>
                </div>
            </div>
        </div>
    </header>

    {{-- App Installation Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="installation-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="installation-demo" class="text-2xl">App Installation</h2>
                <p class="text-sm text-gray-600 mt-sm">Install HD Tickets as a standalone app on your device</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div class="space-y-md">
                        <h3 class="text-lg font-semibold">Installation Benefits</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🚀</span>
                                Faster loading times
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">📱</span>
                                Home screen access
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">📶</span>
                                Works offline
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🔔</span>
                                Push notifications
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-green-600">🎯</span>
                                Native app experience
                            </li>
                        </ul>
                    </div>
                    
                    <div class="space-y-md">
                        <h3 class="text-lg font-semibold">Installation Controls</h3>
                        <div class="space-y-md">
                            <button id="trigger-install" class="btn bg-blue-600 text-white w-full">
                                📱 Install HD Tickets App
                            </button>
                            <button id="check-install-status" class="btn border border-gray-300 w-full">
                                🔍 Check Installation Status
                            </button>
                            <div id="install-info" class="p-md bg-gray-50 rounded text-sm text-gray-600">
                                Installation information will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Installation Instructions --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Manual Installation Instructions</h3>
                    <div class="grid grid--3 gap-md">
                        <div class="p-md border rounded-lg">
                            <h4 class="font-semibold text-blue-600 mb-sm">Chrome Desktop</h4>
                            <ol class="text-sm text-gray-600 space-y-xs">
                                <li>1. Click the install icon in the address bar</li>
                                <li>2. Or use the menu → "Install HD Tickets"</li>
                                <li>3. Click "Install" in the popup</li>
                            </ol>
                        </div>
                        <div class="p-md border rounded-lg">
                            <h4 class="font-semibold text-green-600 mb-sm">Safari iOS</h4>
                            <ol class="text-sm text-gray-600 space-y-xs">
                                <li>1. Tap the Share button</li>
                                <li>2. Scroll down and tap "Add to Home Screen"</li>
                                <li>3. Tap "Add" to confirm</li>
                            </ol>
                        </div>
                        <div class="p-md border rounded-lg">
                            <h4 class="font-semibold text-purple-600 mb-sm">Chrome Android</h4>
                            <ol class="text-sm text-gray-600 space-y-xs">
                                <li>1. Tap the menu (three dots)</li>
                                <li>2. Select "Add to Home screen"</li>
                                <li>3. Confirm the installation</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Push Notifications Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="notifications-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="notifications-demo" class="text-2xl">Push Notifications</h2>
                <p class="text-sm text-gray-600 mt-sm">Get real-time notifications for ticket alerts and price changes</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Notification Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">🎫</span>
                                Ticket availability alerts
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">💰</span>
                                Price drop notifications
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">⚡</span>
                                Flash sale announcements
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">📊</span>
                                Market trend updates
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-blue-600">🏆</span>
                                Event reminders
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Notification Controls</h3>
                        <div class="space-y-md">
                            <button id="request-notifications" class="btn bg-green-600 text-white w-full">
                                🔔 Enable Push Notifications
                            </button>
                            <button id="test-notification" class="btn border border-gray-300 w-full" disabled>
                                🧪 Send Test Notification
                            </button>
                            <button id="check-notification-status" class="btn border border-gray-300 w-full">
                                📊 Check Notification Status
                            </button>
                            <div id="notification-info" class="p-md bg-gray-50 rounded text-sm text-gray-600">
                                Notification status will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Sample Notification Preview --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Notification Preview</h3>
                    <div class="max-w-sm bg-white border rounded-lg shadow-lg p-md">
                        <div class="flex items-start gap-md">
                            <img src="/assets/images/hdTicketsLogo.png" alt="HD Tickets" class="w-8 h-8 rounded">
                            <div class="flex-1">
                                <div class="font-semibold text-sm">HD Tickets</div>
                                <div class="text-sm text-gray-600 mb-sm">Lakers vs Warriors - Price Drop!</div>
                                <div class="text-xs text-gray-500">Tickets now starting at $89 (was $125)</div>
                                <div class="flex gap-sm mt-sm">
                                    <button class="text-xs text-blue-600 font-semibold">View Details</button>
                                    <button class="text-xs text-gray-500">Dismiss</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Offline Functionality Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="offline-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="offline-demo" class="text-2xl">Offline Functionality</h2>
                <p class="text-sm text-gray-600 mt-sm">Access cached content and queue actions when offline</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Offline Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">💾</span>
                                Cached ticket data
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">📝</span>
                                Draft form submissions
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">🔄</span>
                                Background sync when online
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">📄</span>
                                Static page caching
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-purple-600">🎨</span>
                                Offline-first UI
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Offline Tools</h3>
                        <div class="space-y-md">
                            <button id="simulate-offline" class="btn bg-orange-600 text-white w-full">
                                📶 Simulate Offline Mode
                            </button>
                            <button id="check-cache-size" class="btn border border-gray-300 w-full">
                                💾 Check Cache Size
                            </button>
                            <button id="clear-cache" class="btn border border-red-300 text-red-600 w-full">
                                🗑️ Clear Cache
                            </button>
                            <div id="offline-info" class="p-md bg-gray-50 rounded text-sm text-gray-600">
                                Offline status information will appear here...
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Cached Content Demo --}}
                <div class="border-t pt-lg">
                    <h3 class="text-lg font-semibold mb-md">Available Offline Content</h3>
                    <div class="grid grid--3 gap-md">
                        <a href="/" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">🏠 Home Page</div>
                            <div class="text-sm text-gray-600">Main landing page</div>
                        </a>
                        <a href="/dashboard" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">📊 Dashboard</div>
                            <div class="text-sm text-gray-600">Cached dashboard data</div>
                        </a>
                        <a href="/profile" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">👤 Profile</div>
                            <div class="text-sm text-gray-600">User profile settings</div>
                        </a>
                        <a href="/tickets/alerts" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">🔔 Alerts</div>
                            <div class="text-sm text-gray-600">Price alerts page</div>
                        </a>
                        <a href="/examples/accessibility" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">♿ Accessibility</div>
                            <div class="text-sm text-gray-600">Accessibility demo</div>
                        </a>
                        <a href="/examples/responsive" class="p-md border rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="font-semibold">📱 Responsive</div>
                            <div class="text-sm text-gray-600">Responsive design demo</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Background Sync Demo --}}
    <section class="mb-2xl" role="region" aria-labelledby="background-sync-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="background-sync-demo" class="text-2xl">Background Sync</h2>
                <p class="text-sm text-gray-600 mt-sm">Queue form submissions and sync when connection is restored</p>
            </div>
            <div class="card__body space-y-lg">
                <div class="grid grid--2 gap-lg">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Background Sync Features</h3>
                        <ul class="space-y-sm text-gray-600">
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">💳</span>
                                Purchase form submissions
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">🔔</span>
                                Alert creation requests
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">👤</span>
                                Profile updates
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">⚙️</span>
                                Preference changes
                            </li>
                            <li class="flex items-center gap-sm">
                                <span class="text-indigo-600">📧</span>
                                Contact form submissions
                            </li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Test Background Sync</h3>
                        <form id="background-sync-form" class="space-y-md">
                            <div>
                                <label for="test-message" class="block text-sm font-medium text-gray-700 mb-sm">Test Message</label>
                                <input type="text" id="test-message" name="message" 
                                       class="w-full border border-gray-300 rounded-md px-md py-sm"
                                       placeholder="Enter a test message">
                            </div>
                            <div>
                                <label for="test-email" class="block text-sm font-medium text-gray-700 mb-sm">Email (optional)</label>
                                <input type="email" id="test-email" name="email" 
                                       class="w-full border border-gray-300 rounded-md px-md py-sm"
                                       placeholder="test@example.com">
                            </div>
                            <button type="submit" class="btn bg-indigo-600 text-white w-full">
                                📤 Submit (Will sync when online)
                            </button>
                        </form>
                        <div id="sync-status" class="p-md bg-gray-50 rounded text-sm text-gray-600 mt-md">
                            Background sync status will appear here...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PWA Capabilities Overview --}}
    <section class="mb-2xl" role="region" aria-labelledby="capabilities-demo">
        <div class="card">
            <div class="card__header">
                <h2 id="capabilities-demo" class="text-2xl">PWA Capabilities Overview</h2>
                <p class="text-sm text-gray-600 mt-sm">Complete overview of Progressive Web App features</p>
            </div>
            <div class="card__body">
                <div class="grid grid--2 gap-xl">
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Core PWA Features</h3>
                        <div class="space-y-md">
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Service Worker</span>
                                    <span id="sw-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Enables offline functionality and caching</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Web App Manifest</span>
                                    <span id="manifest-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Provides app metadata for installation</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Push Notifications</span>
                                    <span id="push-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Real-time notifications support</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Background Sync</span>
                                    <span id="bg-sync-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Queue actions for offline-to-online sync</div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-semibold mb-md">Device Integration</h3>
                        <div class="space-y-md">
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Home Screen Install</span>
                                    <span id="install-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Add to home screen functionality</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Standalone Mode</span>
                                    <span id="standalone-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">App-like full screen experience</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Offline Storage</span>
                                    <span id="storage-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Local storage and IndexedDB support</div>
                            </div>
                            
                            <div class="p-md border rounded-lg">
                                <div class="flex items-center justify-between mb-sm">
                                    <span class="font-medium">Network Detection</span>
                                    <span id="network-capability" class="text-sm bg-gray-100 px-sm py-xs rounded">Checking...</span>
                                </div>
                                <div class="text-sm text-gray-600">Online/offline status detection</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-50 rounded-lg p-lg mt-2xl text-center" id="footer" role="contentinfo">
        <h2 class="text-lg font-semibold mb-md">Progressive Web App Implementation</h2>
        <div class="grid grid--3 gap-lg text-left">
            <div>
                <h3 class="font-semibold mb-sm">Core Technologies</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Service Worker v2.0.0</li>
                    <li>Web App Manifest</li>
                    <li>Cache API</li>
                    <li>IndexedDB Storage</li>
                    <li>Push API</li>
                    <li>Background Sync</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Features</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Installable application</li>
                    <li>Offline functionality</li>
                    <li>Push notifications</li>
                    <li>Background synchronization</li>
                    <li>Automatic updates</li>
                    <li>Native app experience</li>
                </ul>
            </div>
            <div>
                <h3 class="font-semibold mb-sm">Browser Support</h3>
                <ul class="text-sm text-gray-600 space-y-xs">
                    <li>Chrome 67+ (Full support)</li>
                    <li>Firefox 62+ (Most features)</li>
                    <li>Safari 11.1+ (Basic PWA)</li>
                    <li>Edge 79+ (Full support)</li>
                    <li>Opera 54+ (Full support)</li>
                    <li>Samsung Internet 8.2+</li>
                </ul>
            </div>
        </div>
    </footer>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for PWA Manager to initialize
    setTimeout(initPWADemo, 1000);
    
    function initPWADemo() {
        updatePWAStatus();
        setupEventHandlers();
        checkCapabilities();
    }
    
    function updatePWAStatus() {
        // Service Worker status
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistration().then(registration => {
                const swStatus = document.getElementById('sw-status');
                if (registration) {
                    swStatus.textContent = 'Active';
                    swStatus.className = 'font-mono text-green-600';
                } else {
                    swStatus.textContent = 'Not registered';
                    swStatus.className = 'font-mono text-red-600';
                }
            });
        }
        
        // Installation status
        const installStatus = document.getElementById('install-status');
        if (window.matchMedia('(display-mode: standalone)').matches) {
            installStatus.textContent = 'Installed';
            installStatus.className = 'font-mono text-green-600';
        } else {
            installStatus.textContent = 'Not installed';
            installStatus.className = 'font-mono text-orange-600';
        }
        
        // Notification status
        const notificationStatus = document.getElementById('notification-status');
        if ('Notification' in window) {
            notificationStatus.textContent = Notification.permission;
            notificationStatus.className = `font-mono ${
                Notification.permission === 'granted' ? 'text-green-600' : 
                Notification.permission === 'denied' ? 'text-red-600' : 'text-orange-600'
            }`;
        } else {
            notificationStatus.textContent = 'Not supported';
            notificationStatus.className = 'font-mono text-red-600';
        }
        
        // Connection status
        const connectionStatus = document.getElementById('connection-status');
        connectionStatus.textContent = navigator.onLine ? 'Online' : 'Offline';
        connectionStatus.className = `font-mono ${navigator.onLine ? 'text-green-600' : 'text-red-600'}`;
    }
    
    function setupEventHandlers() {
        // Install app
        document.getElementById('trigger-install').addEventListener('click', async () => {
            if (window.pwaManager) {
                await window.pwaManager.promptInstall();
            } else {
                showInfo('install-info', 'PWA Manager not available. Try refreshing the page.');
            }
        });
        
        // Check install status
        document.getElementById('check-install-status').addEventListener('click', () => {
            const info = [];
            
            if (window.matchMedia('(display-mode: standalone)').matches) {
                info.push('✅ App is running in standalone mode (installed)');
            } else {
                info.push('❌ App is running in browser mode');
            }
            
            if ('beforeinstallprompt' in window) {
                info.push('✅ Install prompt available');
            } else {
                info.push('❌ Install prompt not available');
            }
            
            showInfo('install-info', info.join('<br>'));
        });
        
        // Request notifications
        document.getElementById('request-notifications').addEventListener('click', async () => {
            if (window.pwaManager) {
                const granted = await window.pwaManager.requestNotificationPermission();
                if (granted) {
                    document.getElementById('test-notification').disabled = false;
                    showInfo('notification-info', '✅ Notification permission granted!');
                } else {
                    showInfo('notification-info', '❌ Notification permission denied.');
                }
                updatePWAStatus();
            }
        });
        
        // Test notification
        document.getElementById('test-notification').addEventListener('click', () => {
            if (Notification.permission === 'granted') {
                new Notification('HD Tickets Test', {
                    body: 'This is a test notification from HD Tickets PWA demo!',
                    icon: '/assets/images/pwa/icon-192x192.png',
                    badge: '/assets/images/pwa/icon-96x96.png',
                    vibrate: [200, 100, 200]
                });
                showInfo('notification-info', '✅ Test notification sent!');
            }
        });
        
        // Check notification status
        document.getElementById('check-notification-status').addEventListener('click', () => {
            const info = [];
            
            if ('Notification' in window) {
                info.push(`✅ Notifications supported`);
                info.push(`Permission: ${Notification.permission}`);
                
                if ('serviceWorker' in navigator && 'PushManager' in window) {
                    info.push('✅ Push notifications supported');
                } else {
                    info.push('❌ Push notifications not supported');
                }
            } else {
                info.push('❌ Notifications not supported');
            }
            
            showInfo('notification-info', info.join('<br>'));
        });
        
        // Simulate offline
        document.getElementById('simulate-offline').addEventListener('click', () => {
            // This would typically use Service Worker to simulate offline
            showInfo('offline-info', '🔧 Offline simulation would be implemented with Service Worker testing tools.');
        });
        
        // Check cache size
        document.getElementById('check-cache-size').addEventListener('click', async () => {
            if ('caches' in window) {
                try {
                    const cacheNames = await caches.keys();
                    let totalSize = 0;
                    
                    for (const cacheName of cacheNames) {
                        const cache = await caches.open(cacheName);
                        const requests = await cache.keys();
                        totalSize += requests.length;
                    }
                    
                    showInfo('offline-info', 
                        `📊 Found ${cacheNames.length} caches with ${totalSize} cached resources.<br>` +
                        `Cache names: ${cacheNames.join(', ')}`
                    );
                } catch (error) {
                    showInfo('offline-info', `❌ Error checking cache: ${error.message}`);
                }
            } else {
                showInfo('offline-info', '❌ Cache API not supported');
            }
        });
        
        // Clear cache
        document.getElementById('clear-cache').addEventListener('click', async () => {
            if ('caches' in window) {
                try {
                    const cacheNames = await caches.keys();
                    await Promise.all(cacheNames.map(cacheName => caches.delete(cacheName)));
                    showInfo('offline-info', `✅ Cleared ${cacheNames.length} caches`);
                } catch (error) {
                    showInfo('offline-info', `❌ Error clearing cache: ${error.message}`);
                }
            }
        });
        
        // Background sync form
        document.getElementById('background-sync-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            if (window.pwaManager && window.pwaManager.backgroundSync) {
                // Store data for background sync
                try {
                    // In a real implementation, this would store in IndexedDB
                    localStorage.setItem('pendingBackgroundSync', JSON.stringify({
                        ...data,
                        timestamp: Date.now()
                    }));
                    
                    await window.pwaManager.scheduleBackgroundSync('test-form-sync');
                    showInfo('sync-status', '✅ Form data queued for background sync!');
                } catch (error) {
                    showInfo('sync-status', `❌ Error scheduling sync: ${error.message}`);
                }
            } else {
                showInfo('sync-status', '❌ Background sync not supported');
            }
        });
        
        // Online/offline event listeners
        window.addEventListener('online', () => {
            updatePWAStatus();
            showInfo('offline-info', '🌐 Connection restored!');
        });
        
        window.addEventListener('offline', () => {
            updatePWAStatus();
            showInfo('offline-info', '📶 You are now offline');
        });
    }
    
    function checkCapabilities() {
        // Service Worker
        updateCapability('sw-capability', 'serviceWorker' in navigator);
        
        // Web App Manifest
        updateCapability('manifest-capability', 'onbeforeinstallprompt' in window || 
            window.matchMedia('(display-mode: standalone)').matches);
        
        // Push Notifications
        updateCapability('push-capability', 'Notification' in window && 
            'serviceWorker' in navigator && 'PushManager' in window);
        
        // Background Sync
        updateCapability('bg-sync-capability', 'serviceWorker' in navigator && 
            'sync' in window.ServiceWorkerRegistration.prototype);
        
        // Install capability
        updateCapability('install-capability', 'onbeforeinstallprompt' in window || 
            window.matchMedia('(display-mode: standalone)').matches);
        
        // Standalone mode
        updateCapability('standalone-capability', window.matchMedia('(display-mode: standalone)').matches);
        
        // Offline storage
        updateCapability('storage-capability', 'localStorage' in window && 'indexedDB' in window);
        
        // Network detection
        updateCapability('network-capability', 'onLine' in navigator);
    }
    
    function updateCapability(elementId, supported) {
        const element = document.getElementById(elementId);
        if (supported) {
            element.textContent = 'Supported';
            element.className = 'text-sm bg-green-100 text-green-800 px-sm py-xs rounded';
        } else {
            element.textContent = 'Not Supported';
            element.className = 'text-sm bg-red-100 text-red-800 px-sm py-xs rounded';
        }
    }
    
    function showInfo(elementId, message) {
        const element = document.getElementById(elementId);
        element.innerHTML = message;
    }
});
</script>
@endsection
