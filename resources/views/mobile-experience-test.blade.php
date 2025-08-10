@extends('layouts.app')

@section('title', 'Mobile Experience Test')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">📱 Mobile Experience Test</h1>
        <p class="text-gray-600">Test all mobile optimizations and PWA features</p>
    </div>

    <!-- Device Info -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">📊 Device Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="device-info">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600" id="screen-width">-</div>
                <div class="text-sm text-gray-600">Screen Width</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600" id="screen-height">-</div>
                <div class="text-sm text-gray-600">Screen Height</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600" id="device-type">-</div>
                <div class="text-sm text-gray-600">Device Type</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-2xl font-bold text-orange-600" id="orientation">-</div>
                <div class="text-sm text-gray-600">Orientation</div>
            </div>
        </div>
    </div>

    <!-- Touch Controls Test -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">👆 Touch Controls Test</h2>
        <div class="space-y-4">
            <!-- Touch Buttons -->
            <div class="grid grid-cols-2 gap-4">
                <button class="touch-target bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors" 
                        data-touch-feedback
                        onclick="showToast('Standard button tapped!', 'info')">
                    Standard Button
                </button>
                
                <button class="touch-target-lg bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors touch-ripple" 
                        data-touch-ripple
                        onclick="showToast('Large button tapped!', 'success')">
                    Large Touch Target
                </button>
            </div>
            
            <!-- Long Press Test -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="long-press-indicator bg-purple-600 text-white rounded-lg p-4 text-center font-medium cursor-pointer"
                     data-long-press>
                    Long Press Me (Hold for 500ms)
                </div>
            </div>
            
            <!-- Swipe Area -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg p-6 text-center"
                 id="swipe-area"
                 data-swipe-enabled>
                <p class="font-medium mb-2">Swipe Area</p>
                <p class="text-sm opacity-90">Try swiping left, right, up, or down</p>
                <div id="swipe-feedback" class="mt-2 text-sm font-mono"></div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Test -->
    @auth
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">📱 Bottom Navigation</h2>
            <p class="text-gray-600 mb-4">The bottom navigation should be visible on mobile devices with touch-friendly buttons.</p>
            
            <x-mobile.bottom-navigation 
                :activeTab="'test'"
                :currentUser="auth()->user()"
                :cartCount="3"
                :notificationCount="5"
            />
        </div>
    @endauth

    <!-- Swipeable Cards Test -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">🃏 Swipeable Cards</h2>
        
        @php
            $mockTickets = [
                (object)[
                    'id' => 1,
                    'event_name' => 'NFL Championship Game',
                    'venue' => 'Mercedes-Benz Stadium',
                    'event_date' => '2024-02-15 19:30:00',
                    'price' => 450.00,
                    'platform' => 'StubHub',
                    'available_quantity' => 12
                ],
                (object)[
                    'id' => 2,
                    'event_name' => 'NBA Finals Game 7',
                    'venue' => 'Chase Center',
                    'event_date' => '2024-03-20 20:00:00',
                    'price' => 850.00,
                    'platform' => 'Ticketmaster',
                    'available_quantity' => 3
                ],
                (object)[
                    'id' => 3,
                    'event_name' => 'Premier League Derby',
                    'venue' => 'Old Trafford',
                    'event_date' => '2024-04-10 15:00:00',
                    'price' => 275.00,
                    'platform' => 'Official Club Store',
                    'available_quantity' => 0
                ]
            ];
        @endphp
        
        <x-mobile.swipeable-ticket-cards 
            :tickets="$mockTickets"
            :enableSwipe="true"
            :showActions="true"
        />
    </div>

    <!-- Form Controls Test -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">📝 Form Controls Test</h2>
        <form class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Text Input (16px to prevent zoom on iOS)</label>
                <input type="text" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Enter text here...">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email Input</label>
                <input type="email" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="your@email.com">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Select Dropdown</label>
                <select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option>Choose an option...</option>
                    <option>Option 1</option>
                    <option>Option 2</option>
                    <option>Option 3</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Textarea</label>
                <textarea class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          rows="3" placeholder="Enter your message..."></textarea>
            </div>
            
            <div class="flex items-center space-x-3">
                <input type="checkbox" id="test-checkbox" class="w-5 h-5 text-blue-600">
                <label for="test-checkbox" class="text-sm font-medium text-gray-700">I agree to the terms</label>
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors touch-target">
                Submit Form
            </button>
        </form>
    </div>

    <!-- Pull to Refresh Test -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6" data-pull-to-refresh>
        <h2 class="text-xl font-semibold mb-4">↕️ Pull to Refresh Test</h2>
        <p class="text-gray-600 mb-4">On mobile, pull down from the top of this section to test pull-to-refresh functionality.</p>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-blue-800 font-medium">Last refreshed: <span id="last-refresh">-</span></p>
        </div>
    </div>

    <!-- PWA Features Test -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">⚡ PWA Features Test</h2>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button class="btn btn-primary" onclick="testPWAInstall()">
                    📱 Test PWA Install
                </button>
                
                <button class="btn btn-secondary" onclick="testNotifications()">
                    🔔 Test Notifications
                </button>
                
                <button class="btn btn-outline" onclick="testOfflineMode()">
                    📶 Test Offline Mode
                </button>
            </div>
            
            <div id="pwa-status" class="bg-gray-50 rounded-lg p-4">
                <p class="font-medium text-gray-700 mb-2">PWA Status:</p>
                <ul class="text-sm text-gray-600 space-y-1" id="pwa-capabilities">
                    <!-- Will be populated by JavaScript -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Performance Tests -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">⚡ Performance Tests</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-gray-900 mb-2">Image Lazy Loading</div>
                <div class="space-y-2">
                    <!-- Test lazy loading with placeholder images -->
                    <img data-lazy-src="https://picsum.photos/300/200?random=1" 
                         class="w-full h-32 bg-gray-300 rounded-lg lazy-image"
                         alt="Test image 1">
                    <img data-lazy-src="https://picsum.photos/300/200?random=2" 
                         class="w-full h-32 bg-gray-300 rounded-lg lazy-image"
                         alt="Test image 2">
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-lg font-semibold text-gray-900 mb-2">Connection Status</div>
                <div class="flex items-center space-x-2 mb-2">
                    <div class="connection-indicator"></div>
                    <span id="connection-status">-</span>
                </div>
                <div class="text-sm text-gray-600">
                    Network: <span id="network-info">-</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Results -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">📋 Test Results</h2>
        <div class="space-y-2" id="test-results">
            <!-- Will be populated by test results -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize device info
    updateDeviceInfo();
    
    // Initialize test results
    const testResults = document.getElementById('test-results');
    let testCount = 0;
    let passedTests = 0;
    
    function addTestResult(testName, passed, details = '') {
        testCount++;
        if (passed) passedTests++;
        
        const result = document.createElement('div');
        result.className = `flex items-center justify-between p-3 rounded-lg ${passed ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'}`;
        result.innerHTML = `
            <div class="flex items-center">
                <span class="text-xl mr-2">${passed ? '✅' : '❌'}</span>
                <span class="font-medium">${testName}</span>
            </div>
            ${details ? `<span class="text-sm">${details}</span>` : ''}
        `;
        testResults.appendChild(result);
        
        // Update summary
        updateTestSummary();
    }
    
    function updateTestSummary() {
        const existingSummary = document.querySelector('#test-summary');
        if (existingSummary) existingSummary.remove();
        
        const summary = document.createElement('div');
        summary.id = 'test-summary';
        summary.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4';
        summary.innerHTML = `
            <p class="font-medium text-blue-800">
                Tests Passed: ${passedTests}/${testCount} (${Math.round((passedTests/testCount)*100)}%)
            </p>
        `;
        testResults.insertBefore(summary, testResults.firstChild);
    }
    
    // Test 1: Touch capabilities
    const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
    addTestResult('Touch Support', hasTouch, hasTouch ? 'Device supports touch' : 'No touch support detected');
    
    // Test 2: PWA manifest
    const hasManifest = document.querySelector('link[rel="manifest"]') !== null;
    addTestResult('PWA Manifest', hasManifest, hasManifest ? 'Manifest linked' : 'No manifest found');
    
    // Test 3: Service Worker
    const hasServiceWorker = 'serviceWorker' in navigator;
    addTestResult('Service Worker Support', hasServiceWorker, hasServiceWorker ? 'SW supported' : 'SW not supported');
    
    // Test 4: Responsive utilities
    const hasResponsiveUtils = typeof window.responsiveUtils !== 'undefined';
    addTestResult('Responsive Utilities', hasResponsiveUtils, hasResponsiveUtils ? 'Loaded' : 'Not loaded');
    
    // Test 5: Mobile touch utils
    const hasMobileTouchUtils = typeof window.mobileTouchUtils !== 'undefined';
    addTestResult('Mobile Touch Utils', hasMobileTouchUtils, hasMobileTouchUtils ? 'Loaded' : 'Not loaded');
    
    // Test 6: Lazy loading
    const hasLazyLoader = typeof window.lazyImageLoader !== 'undefined';
    addTestResult('Image Lazy Loading', hasLazyLoader, hasLazyLoader ? 'Initialized' : 'Not initialized');
    
    // Test 7: Mobile CSS
    const hasMobileCSS = document.querySelector('link[href*="mobile-enhancements.css"]') !== null;
    addTestResult('Mobile Enhancement CSS', hasMobileCSS, hasMobileCSS ? 'Loaded' : 'Not loaded');
    
    // Setup touch event listeners
    if (window.mobileTouchUtils) {
        // Long press test
        window.mobileTouchUtils.on('longpress', (e) => {
            if (e.detail.element.dataset.longPress !== undefined) {
                showToast('Long press detected!', 'success');
                addTestResult('Long Press Gesture', true, 'Successfully detected');
            }
        });
        
        // Swipe test
        const swipeArea = document.getElementById('swipe-area');
        const swipeFeedback = document.getElementById('swipe-feedback');
        
        window.mobileTouchUtils.enableSwipe(swipeArea, {
            swipeLeft: () => {
                swipeFeedback.textContent = 'Swiped Left ←';
                showToast('Swiped Left!', 'info');
                addTestResult('Swipe Left Gesture', true, 'Detected swipe left');
            },
            swipeRight: () => {
                swipeFeedback.textContent = 'Swiped Right →';
                showToast('Swiped Right!', 'info');
                addTestResult('Swipe Right Gesture', true, 'Detected swipe right');
            },
            swipeUp: () => {
                swipeFeedback.textContent = 'Swiped Up ↑';
                showToast('Swiped Up!', 'info');
                addTestResult('Swipe Up Gesture', true, 'Detected swipe up');
            },
            swipeDown: () => {
                swipeFeedback.textContent = 'Swiped Down ↓';
                showToast('Swiped Down!', 'info');
                addTestResult('Swipe Down Gesture', true, 'Detected swipe down');
            }
        });
    }
    
    // Setup pull to refresh test
    const pullRefreshArea = document.querySelector('[data-pull-to-refresh]');
    if (pullRefreshArea && window.mobileOptimization) {
        window.mobileOptimization.enablePullToRefresh(pullRefreshArea, async () => {
            document.getElementById('last-refresh').textContent = new Date().toLocaleTimeString();
            showToast('Pull to refresh triggered!', 'success');
            addTestResult('Pull to Refresh', true, 'Successfully triggered');
            return Promise.resolve();
        });
    }
    
    // Update device info periodically
    window.addEventListener('resize', updateDeviceInfo);
    window.addEventListener('orientationchange', () => {
        setTimeout(updateDeviceInfo, 100);
    });
    
    // Update connection status
    updateConnectionStatus();
    window.addEventListener('online', updateConnectionStatus);
    window.addEventListener('offline', updateConnectionStatus);
    
    // Test network information API
    if ('connection' in navigator) {
        updateNetworkInfo();
        navigator.connection.addEventListener('change', updateNetworkInfo);
    }
    
    function updateDeviceInfo() {
        document.getElementById('screen-width').textContent = window.innerWidth + 'px';
        document.getElementById('screen-height').textContent = window.innerHeight + 'px';
        
        const isMobile = window.innerWidth < 768;
        const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
        const deviceType = isMobile ? 'Mobile' : isTablet ? 'Tablet' : 'Desktop';
        document.getElementById('device-type').textContent = deviceType;
        
        const orientation = window.innerHeight > window.innerWidth ? 'Portrait' : 'Landscape';
        document.getElementById('orientation').textContent = orientation;
    }
    
    function updateConnectionStatus() {
        const status = navigator.onLine ? 'Online' : 'Offline';
        document.getElementById('connection-status').textContent = status;
        
        if (!navigator.onLine) {
            showToast('Device is offline', 'warning');
        }
    }
    
    function updateNetworkInfo() {
        if ('connection' in navigator) {
            const connection = navigator.connection;
            const info = `${connection.effectiveType || 'Unknown'} (${connection.downlink || '?'}Mbps)`;
            document.getElementById('network-info').textContent = info;
        } else {
            document.getElementById('network-info').textContent = 'Not available';
        }
    }
});

// PWA test functions
function testPWAInstall() {
    if ('serviceWorker' in navigator) {
        // Check if app is already installed
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            showToast('App is already installed!', 'success');
        } else {
            showToast('PWA install prompt would appear here', 'info');
        }
    } else {
        showToast('PWA not supported on this browser', 'error');
    }
}

function testNotifications() {
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            new Notification('HD Tickets', {
                body: 'Test notification from mobile app!',
                icon: '/assets/images/pwa/icon-192x192.png'
            });
            showToast('Test notification sent!', 'success');
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    testNotifications();
                } else {
                    showToast('Notification permission denied', 'warning');
                }
            });
        } else {
            showToast('Notifications are blocked', 'error');
        }
    } else {
        showToast('Notifications not supported', 'error');
    }
}

function testOfflineMode() {
    if ('serviceWorker' in navigator) {
        // Test offline functionality
        fetch('/api/test-offline', { method: 'HEAD' })
            .then(() => {
                showToast('Online - Service Worker active', 'success');
            })
            .catch(() => {
                showToast('Offline mode detected', 'warning');
            });
    } else {
        showToast('Offline mode not supported', 'error');
    }
}

// Toast notification function
function showToast(message, type = 'info') {
    const colors = {
        info: 'bg-blue-500',
        success: 'bg-green-500', 
        warning: 'bg-yellow-500',
        error: 'bg-red-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-4 py-2 rounded-lg font-medium z-50 transform translate-x-full transition-transform duration-300`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.transform = 'translateX(0)';
    });
    
    setTimeout(() => {
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
@endpush

@push('styles')
<style>
    /* Additional test page styles */
    .btn {
        @apply px-4 py-2 rounded-lg font-medium transition-colors touch-target;
    }
    
    .btn-primary {
        @apply bg-blue-600 text-white hover:bg-blue-700;
    }
    
    .btn-secondary {
        @apply bg-gray-600 text-white hover:bg-gray-700;
    }
    
    .btn-outline {
        @apply border-2 border-gray-600 text-gray-600 hover:bg-gray-600 hover:text-white;
    }
    
    /* Test-specific mobile optimizations */
    @media (max-width: 768px) {
        .grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .md\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .md\:grid-cols-3 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
