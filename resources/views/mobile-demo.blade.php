<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <x-mobile.mobile-meta :enableZoom="false" />
    <title>HD Tickets - Mobile Experience Demo</title>
    
    <!-- Include mobile enhancement CSS -->
    <link rel="stylesheet" href="{{ asset('css/mobile-enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    
    <!-- Tailwind CSS for quick styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Demo-specific styles */
        .demo-section {
            margin-bottom: 2rem;
            padding: 1rem;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: white;
        }
        
        .demo-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0.5rem;
        }
        
        .enhancement-indicator {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            background: #dbeafe;
            color: #1e40af;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Layout Component -->
    <x-mobile-layout 
        title="Mobile Demo" 
        :showBackButton="false" 
        :showSearch="true" 
        :showUserMenu="true"
        :pullToRefresh="true"
        :swipeGestures="true"
        searchPlaceholder="Search sports tickets..."
    >
        <div class="space-y-6">
            <!-- Header -->
            <div class="text-center py-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">🎫 HD Tickets</h1>
                <p class="text-gray-600 mb-4">Mobile Experience Enhancements Demo</p>
                <div class="text-sm text-blue-600 bg-blue-50 p-3 rounded-lg">
                    📱 This page demonstrates all mobile experience enhancements for the sports tickets application
                </div>
            </div>

            <!-- Touch Targets Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Touch Targets (44px minimum) 
                    <span class="enhancement-indicator">✅ WCAG AAA</span>
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <button class="touch-target bg-blue-500 text-white rounded-lg" data-haptic="light">
                        Regular Button
                    </button>
                    <button class="touch-target-lg bg-green-500 text-white rounded-lg" data-haptic="medium">
                        Large Target
                    </button>
                    <a href="#" class="touch-target bg-purple-500 text-white rounded-lg text-center" data-haptic="light">
                        Link Target
                    </a>
                    <button class="touch-target-enhanced bg-orange-500 text-white rounded-lg" data-haptic="heavy">
                        Enhanced Feedback
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    All interactive elements meet 44px minimum touch target size with haptic feedback
                </p>
            </div>

            <!-- Form Optimizations Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Mobile Form Optimizations 
                    <span class="enhancement-indicator">📱 Keyboard Optimized</span>
                </h2>
                <form class="mobile-form space-y-4">
                    <div class="mobile-form-group">
                        <label class="mobile-form-label">Email (optimized keyboard)</label>
                        <input type="email" class="mobile-form-input" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="mobile-form-group">
                        <label class="mobile-form-label">Phone Number (numeric pad)</label>
                        <input type="tel" class="mobile-form-input" placeholder="Enter phone number" required>
                    </div>
                    
                    <div class="mobile-form-floating">
                        <input type="text" class="mobile-form-input" placeholder="Floating label example" required>
                        <label class="mobile-form-label">Floating Label</label>
                    </div>
                    
                    <div class="mobile-form-group">
                        <label class="mobile-form-label">Message (auto-resize)</label>
                        <textarea class="mobile-form-textarea" rows="3" maxlength="200" 
                                  placeholder="Type your message here..."></textarea>
                    </div>
                    
                    <button type="submit" class="touch-target-lg w-full bg-blue-600 text-white rounded-lg font-semibold" 
                            data-haptic="success">
                        Submit Form
                    </button>
                </form>
                <p class="text-sm text-gray-600 mt-2">
                    16px font size prevents zoom, optimized keyboards, auto-validation, character counters
                </p>
            </div>

            <!-- Swipe Gestures Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Swipe Gestures 
                    <span class="enhancement-indicator">👆 Interactive</span>
                </h2>
                <div class="swipe-container bg-gradient-to-r from-blue-500 to-purple-600 text-white p-6 rounded-lg text-center" 
                     data-swipe-enabled="true" id="swipeDemo">
                    <h3 class="text-xl font-bold mb-2">Try Swiping!</h3>
                    <p class="mb-4">Swipe in any direction to test gesture recognition</p>
                    <div id="swipeResult" class="text-sm bg-white bg-opacity-20 p-2 rounded">
                        👆 Swipe to see results
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Swipe left/right for navigation, up/down for actions
                </p>
            </div>

            <!-- Progressive Disclosure Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Progressive Disclosure 
                    <span class="enhancement-indicator">📖 Content Organization</span>
                </h2>
                
                <!-- Expandable Section -->
                <div class="mobile-expandable collapsed" data-expandable>
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Ticket Details</h3>
                        <button class="mobile-expand-button" data-expand-trigger>
                            Show More
                        </button>
                    </div>
                    <div class="mobile-expandable-content" data-expand-content>
                        <div class="space-y-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-semibold mb-2">Game Information</h4>
                                <p>Lakers vs Warriors</p>
                                <p>Staples Center, Los Angeles</p>
                                <p>March 15, 2024 at 7:30 PM</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-semibold mb-2">Seat Details</h4>
                                <p>Section 101, Row 5, Seats 7-8</p>
                                <p>Lower Bowl, Premium Access</p>
                                <p>Includes parking pass</p>
                            </div>
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-semibold mb-2">Price Breakdown</h4>
                                <p>Base Price: $150 x 2 = $300</p>
                                <p>Service Fee: $25</p>
                                <p>Processing Fee: $10</p>
                                <p><strong>Total: $335</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="mobile-expandable-fade"></div>
                </div>
                
                <!-- Accordion Demo -->
                <div class="mobile-accordion mt-6" data-exclusive="true">
                    <div class="mobile-accordion-item">
                        <button class="mobile-accordion-header" aria-expanded="false">
                            <span>Frequently Asked Questions</span>
                            <span class="mobile-accordion-icon">▼</span>
                        </button>
                        <div class="mobile-accordion-content">
                            <div class="mobile-accordion-body">
                                <p>Find answers to common questions about ticket purchasing, refunds, and event policies.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mobile-accordion-item">
                        <button class="mobile-accordion-header" aria-expanded="false">
                            <span>Ticket Transfer</span>
                            <span class="mobile-accordion-icon">▼</span>
                        </button>
                        <div class="mobile-accordion-content">
                            <div class="mobile-accordion-body">
                                <p>Learn how to transfer tickets to friends or family members through our mobile app.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mobile-accordion-item">
                        <button class="mobile-accordion-header" aria-expanded="false">
                            <span>Refund Policy</span>
                            <span class="mobile-accordion-icon">▼</span>
                        </button>
                        <div class="mobile-accordion-content">
                            <div class="mobile-accordion-body">
                                <p>Our refund policy varies by event. Most tickets are non-refundable unless the event is cancelled.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="text-sm text-gray-600 mt-2">
                    Expandable content and accordion patterns for better mobile information architecture
                </p>
            </div>

            <!-- Mobile Table Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Mobile Table Optimization 
                    <span class="enhancement-indicator">📊 Responsive Tables</span>
                </h2>
                
                <div class="mobile-table-wrapper">
                    <table class="mobile-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Event">Lakers vs Warriors</td>
                                <td data-label="Date">Mar 15, 2024</td>
                                <td data-label="Venue">Staples Center</td>
                                <td data-label="Price">$150</td>
                                <td data-label="Status"><span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">Available</span></td>
                            </tr>
                            <tr>
                                <td data-label="Event">Bulls vs Heat</td>
                                <td data-label="Date">Mar 18, 2024</td>
                                <td data-label="Venue">United Center</td>
                                <td data-label="Price">$89</td>
                                <td data-label="Status"><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm">Limited</span></td>
                            </tr>
                            <tr>
                                <td data-label="Event">Celtics vs Knicks</td>
                                <td data-label="Date">Mar 22, 2024</td>
                                <td data-label="Venue">TD Garden</td>
                                <td data-label="Price">$200</td>
                                <td data-label="Status"><span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">Sold Out</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <p class="text-sm text-gray-600 mt-2">
                    Tables automatically convert to card layout on small screens with data labels
                </p>
            </div>

            <!-- Haptic Feedback Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Haptic Feedback 
                    <span class="enhancement-indicator">📳 Tactile Response</span>
                </h2>
                <div class="grid grid-cols-2 gap-4">
                    <button class="touch-target bg-gray-500 text-white rounded-lg" data-haptic="light">
                        Light Haptic
                    </button>
                    <button class="touch-target bg-blue-500 text-white rounded-lg" data-haptic="medium">
                        Medium Haptic
                    </button>
                    <button class="touch-target bg-purple-500 text-white rounded-lg" data-haptic="heavy">
                        Heavy Haptic
                    </button>
                    <button class="touch-target bg-green-500 text-white rounded-lg" data-haptic="success">
                        Success Haptic
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Contextual haptic feedback with visual fallback for non-supported devices
                </p>
            </div>

            <!-- Pull-to-Refresh Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Pull-to-Refresh 
                    <span class="enhancement-indicator">🔄 Native Feel</span>
                </h2>
                <div class="pull-to-refresh-container bg-blue-50 p-4 rounded-lg" style="height: 200px; overflow-y: auto;" 
                     data-pull-to-refresh="true">
                    <div class="text-center mb-4">
                        <p class="text-blue-600 font-semibold">Pull down to refresh</p>
                        <p class="text-sm text-gray-600">This simulates refreshing ticket data</p>
                    </div>
                    <div class="space-y-3">
                        <div class="bg-white p-3 rounded shadow-sm">
                            <h4 class="font-semibold">Recent Tickets</h4>
                            <p class="text-sm text-gray-600">Last updated: 2 minutes ago</p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <h4 class="font-semibold">Price Alerts</h4>
                            <p class="text-sm text-gray-600">3 new alerts available</p>
                        </div>
                        <div class="bg-white p-3 rounded shadow-sm">
                            <h4 class="font-semibold">Upcoming Events</h4>
                            <p class="text-sm text-gray-600">5 events this week</p>
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Pull down from the top to trigger refresh with visual and haptic feedback
                </p>
            </div>

            <!-- Connection Status Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Connection Status 
                    <span class="enhancement-indicator">📡 Offline Support</span>
                </h2>
                <div class="text-center">
                    <div id="connectionStatus" class="mb-4">
                        <span class="connection-indicator inline-block mr-2"></span>
                        <span id="connectionText">Connected</span>
                    </div>
                    <button id="toggleConnection" class="touch-target bg-orange-500 text-white rounded-lg">
                        Simulate Offline Mode
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Automatic detection of connection status with user feedback
                </p>
            </div>

            <!-- Bottom Navigation Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Navigation Patterns 
                    <span class="enhancement-indicator">🧭 Mobile-First</span>
                </h2>
                <div class="bg-white border rounded-lg p-4">
                    <div class="mobile-bottom-nav-content">
                        <a href="#" class="mobile-bottom-nav-item active" data-haptic="selection">
                            <div class="mobile-bottom-nav-icon">🏠</div>
                            <span class="mobile-bottom-nav-label">Home</span>
                        </a>
                        <a href="#" class="mobile-bottom-nav-item" data-haptic="selection">
                            <div class="mobile-bottom-nav-icon">🔍</div>
                            <span class="mobile-bottom-nav-label">Search</span>
                        </a>
                        <a href="#" class="mobile-bottom-nav-item" data-haptic="selection">
                            <div class="mobile-bottom-nav-icon">❤️</div>
                            <span class="mobile-bottom-nav-label">Favorites</span>
                        </a>
                        <a href="#" class="mobile-bottom-nav-item" data-haptic="selection">
                            <div class="mobile-bottom-nav-icon">🛒</div>
                            <span class="mobile-bottom-nav-label">Cart</span>
                            <div class="mobile-nav-badge">3</div>
                        </a>
                        <a href="#" class="mobile-bottom-nav-item" data-haptic="selection">
                            <div class="mobile-bottom-nav-icon">👤</div>
                            <span class="mobile-bottom-nav-label">Profile</span>
                        </a>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Touch-optimized navigation with badges and active states
                </p>
            </div>

            <!-- Accessibility Features Demo -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Accessibility Enhancements 
                    <span class="enhancement-indicator">♿ WCAG Compliant</span>
                </h2>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <label class="mobile-form-label">Enable Screen Reader Mode</label>
                        <input type="checkbox" class="w-6 h-6" id="screenReader">
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="mobile-form-label">High Contrast Mode</label>
                        <input type="checkbox" class="w-6 h-6" id="highContrast">
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <label class="mobile-form-label">Reduce Motion</label>
                        <input type="checkbox" class="w-6 h-6" id="reduceMotion">
                    </div>
                    
                    <button class="touch-target bg-purple-600 text-white rounded-lg" tabindex="0">
                        Focus Visible Button (try Tab navigation)
                    </button>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    ARIA labels, skip links, keyboard navigation, focus management, and preference respect
                </p>
            </div>

            <!-- Performance Indicators -->
            <div class="demo-section">
                <h2 class="demo-title">
                    Performance Status 
                    <span class="enhancement-indicator">⚡ Optimized</span>
                </h2>
                <div id="performanceStats" class="grid grid-cols-2 gap-4 text-center">
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-green-600" id="loadTime">--ms</div>
                        <div class="text-sm text-gray-600">Load Time</div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600" id="touchLatency">--ms</div>
                        <div class="text-sm text-gray-600">Touch Latency</div>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600" id="memoryUsage">--MB</div>
                        <div class="text-sm text-gray-600">Memory Usage</div>
                    </div>
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <div class="text-2xl font-bold text-orange-600" id="batteryLevel">--%</div>
                        <div class="text-sm text-gray-600">Battery Level</div>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Real-time performance monitoring for optimal mobile experience
                </p>
            </div>
        </div>
    </x-mobile-layout>

    <!-- Floating Action Button -->
    <button class="mobile-fab" data-haptic="medium" title="Quick Actions">
        ✨
    </button>

    <!-- Include mobile enhancement scripts -->
    <script src="{{ asset('js/utils/responsiveUtils.js') }}"></script>
    <script src="{{ asset('js/utils/mobileTouchUtils.js') }}"></script>
    <script src="{{ asset('js/utils/mobileFormOptimizer.js') }}"></script>
    <script src="{{ asset('js/utils/mobileOptimization.js') }}"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🎯 Mobile Demo Page Loaded');
        
        // Demo-specific enhancements
        setupSwipeDemo();
        setupConnectionDemo();
        setupAccessibilityDemo();
        setupPerformanceMonitoring();
        
        function setupSwipeDemo() {
            const swipeDemo = document.getElementById('swipeDemo');
            const swipeResult = document.getElementById('swipeResult');
            
            if (window.mobileTouchUtils) {
                window.mobileTouchUtils.enableSwipe(swipeDemo, {
                    swipeLeft: () => {
                        swipeResult.textContent = '⬅️ Swiped Left!';
                        swipeResult.style.background = 'rgba(59, 130, 246, 0.3)';
                    },
                    swipeRight: () => {
                        swipeResult.textContent = '➡️ Swiped Right!';
                        swipeResult.style.background = 'rgba(16, 185, 129, 0.3)';
                    },
                    swipeUp: () => {
                        swipeResult.textContent = '⬆️ Swiped Up!';
                        swipeResult.style.background = 'rgba(245, 158, 11, 0.3)';
                    },
                    swipeDown: () => {
                        swipeResult.textContent = '⬇️ Swiped Down!';
                        swipeResult.style.background = 'rgba(239, 68, 68, 0.3)';
                    }
                });
            }
        }
        
        function setupConnectionDemo() {
            const toggleButton = document.getElementById('toggleConnection');
            const statusText = document.getElementById('connectionText');
            const indicator = document.querySelector('#connectionStatus .connection-indicator');
            
            toggleButton.addEventListener('click', function() {
                if (statusText.textContent === 'Connected') {
                    statusText.textContent = 'Offline (Simulated)';
                    indicator.classList.add('offline');
                    this.textContent = 'Simulate Online Mode';
                } else {
                    statusText.textContent = 'Connected';
                    indicator.classList.remove('offline');
                    this.textContent = 'Simulate Offline Mode';
                }
            });
        }
        
        function setupAccessibilityDemo() {
            const screenReader = document.getElementById('screenReader');
            const highContrast = document.getElementById('highContrast');
            const reduceMotion = document.getElementById('reduceMotion');
            
            screenReader.addEventListener('change', function() {
                document.body.classList.toggle('screen-reader-mode', this.checked);
            });
            
            highContrast.addEventListener('change', function() {
                document.body.classList.toggle('high-contrast', this.checked);
            });
            
            reduceMotion.addEventListener('change', function() {
                document.body.classList.toggle('reduce-motion', this.checked);
            });
        }
        
        function setupPerformanceMonitoring() {
            // Simulate performance stats
            const loadTime = document.getElementById('loadTime');
            const touchLatency = document.getElementById('touchLatency');
            const memoryUsage = document.getElementById('memoryUsage');
            const batteryLevel = document.getElementById('batteryLevel');
            
            // Load time (actual)
            const startTime = performance.now();
            window.addEventListener('load', function() {
                const endTime = performance.now();
                loadTime.textContent = Math.round(endTime - startTime) + 'ms';
            });
            
            // Touch latency simulation
            let touchStart = 0;
            document.addEventListener('touchstart', function() {
                touchStart = performance.now();
            });
            
            document.addEventListener('touchend', function() {
                if (touchStart) {
                    const latency = performance.now() - touchStart;
                    touchLatency.textContent = Math.round(latency) + 'ms';
                }
            });
            
            // Memory usage (if available)
            if ('memory' in performance) {
                const updateMemory = () => {
                    const mem = performance.memory.usedJSHeapSize / (1024 * 1024);
                    memoryUsage.textContent = Math.round(mem) + 'MB';
                };
                updateMemory();
                setInterval(updateMemory, 5000);
            }
            
            // Battery level (if available)
            if ('getBattery' in navigator) {
                navigator.getBattery().then(function(battery) {
                    const updateBattery = () => {
                        batteryLevel.textContent = Math.round(battery.level * 100) + '%';
                    };
                    updateBattery();
                    battery.addEventListener('levelchange', updateBattery);
                });
            }
        }
        
        // Custom pull-to-refresh handler
        window.customRefreshHandler = async function() {
            console.log('🔄 Custom refresh triggered');
            
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Update some demo content
            const timestamp = new Date().toLocaleTimeString();
            document.querySelector('[data-label="Status"] span').textContent = `Updated at ${timestamp}`;
            
            return Promise.resolve();
        };
        
        console.log('✅ All mobile enhancements initialized successfully');
        
        // Log mobile capabilities
        if (window.mobileTouchUtils) {
            console.log('📱 Touch capabilities:', window.mobileTouchUtils.getTouchCapabilities());
        }
        
        if (window.mobileFormOptimizer) {
            console.log('📝 Form optimizer status:', window.mobileFormOptimizer.getStatus());
        }
    });
    </script>
</body>
</html>
