@props([
    'activeTab' => 'home',
    'showCartCount' => true,
    'showNotificationBadge' => true,
    'currentUser' => null,
    'cartCount' => 0,
    'notificationCount' => 0
])

@php
    $user = $currentUser ?? Auth::user();
    $navId = 'mobile-bottom-nav-' . uniqid();
    
    // Navigation items configuration
    $navItems = [
        [
            'id' => 'home',
            'label' => 'Tickets',
            'route' => 'dashboard',
            'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z',
            'emoji' => '🎫'
        ],
        [
            'id' => 'categories',
            'label' => 'Sports',
            'route' => 'tickets.scraping.index',
            'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z',
            'emoji' => '🏆'
        ],
        [
            'id' => 'favorites',
            'label' => 'Favorites',
            'route' => 'tickets.alerts.index',
            'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z',
            'emoji' => '❤️',
            'badge' => true
        ],
        [
            'id' => 'cart',
            'label' => 'Cart',
            'route' => 'purchase-decisions.index',
            'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z',
            'emoji' => '🛒',
            'badge' => true,
            'count' => $cartCount
        ],
        [
            'id' => 'profile',
            'label' => 'Profile',
            'route' => 'profile.show',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'emoji' => '👤',
            'badge' => $showNotificationBadge,
            'count' => $notificationCount
        ]
    ];
@endphp

<div 
    class="mobile-bottom-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 md:hidden"
    data-nav-id="{{ $navId }}"
    id="{{ $navId }}"
>
    <!-- Main Navigation -->
    <nav class="flex items-center justify-around px-2 py-2" role="tablist">
        @foreach($navItems as $item)
            @php
                $isActive = $activeTab === $item['id'];
                $routeExists = Route::has($item['route']);
                $href = $routeExists ? route($item['route']) : '#';
                $showBadge = isset($item['badge']) && $item['badge'] && isset($item['count']) && $item['count'] > 0;
                $badgeCount = $item['count'] ?? 0;
            @endphp

            <a 
                href="{{ $href }}"
                class="nav-item flex flex-col items-center justify-center p-2 min-w-0 flex-1 touch-target {{ $isActive ? 'active' : '' }}"
                role="tab"
                aria-selected="{{ $isActive ? 'true' : 'false' }}"
                aria-label="{{ $item['label'] }}"
                data-nav-item="{{ $item['id'] }}"
                @if(!$routeExists) onclick="return false;" @endif
            >
                <!-- Icon Container -->
                <div class="relative icon-container mb-1">
                    <!-- SVG Icon (Primary) -->
                    <svg 
                        class="nav-icon w-6 h-6 transition-all duration-300 {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="{{ $item['icon'] }}"
                        ></path>
                    </svg>

                    <!-- Emoji Icon (Fallback/Fun) -->
                    <span 
                        class="nav-emoji absolute inset-0 flex items-center justify-center text-lg opacity-0 transition-opacity duration-300"
                        aria-hidden="true"
                    >
                        {{ $item['emoji'] }}
                    </span>

                    <!-- Badge/Counter -->
                    @if($showBadge || ($item['id'] === 'favorites' && $showNotificationBadge && $notificationCount > 0))
                        <div class="absolute -top-2 -right-2 min-w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center animate-pulse">
                            @if($item['id'] === 'cart' && $cartCount > 0)
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            @elseif($item['id'] === 'favorites' && $notificationCount > 0)
                                {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                            @else
                                <span class="w-2 h-2 bg-current rounded-full"></span>
                            @endif
                        </div>
                    @endif

                    <!-- Active Indicator -->
                    <div class="active-indicator absolute inset-0 bg-blue-500 bg-opacity-10 rounded-full scale-0 transition-transform duration-300"></div>
                </div>

                <!-- Label -->
                <span class="nav-label text-xs font-medium transition-colors duration-300 {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}">
                    {{ $item['label'] }}
                </span>

                <!-- Haptic Feedback Element -->
                <div class="haptic-feedback absolute inset-0 rounded-lg"></div>
            </a>
        @endforeach
    </nav>

    <!-- Quick Action Floating Button (Optional) -->
    <div class="quick-action-fab absolute -top-6 left-1/2 transform -translate-x-1/2">
        <button 
            class="fab-button w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center"
            data-quick-action="search"
            aria-label="Quick search"
            title="Search tickets"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </button>
    </div>

    <!-- Safe Area Spacer for devices with home indicator -->
    <div class="safe-area-spacer h-safe-area-inset-bottom bg-white"></div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ now()->timestamp }}">
<style>
/* Mobile Bottom Navigation Styles */
.mobile-bottom-nav {
    /* Safe area support for devices with home indicator */
    padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
    backdrop-filter: blur(20px);
    background: rgba(255, 255, 255, 0.95);
    border-top: 1px solid rgba(229, 231, 235, 0.8);
}

.safe-area-spacer {
    height: env(safe-area-inset-bottom, 0);
}

/* Enhanced touch targets for navigation items */
.nav-item {
    position: relative;
    min-height: 56px;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}

.nav-item.touch-target {
    min-height: 56px;
    min-width: 56px;
}

/* Active state styling */
.nav-item.active {
    background: rgba(59, 130, 246, 0.1);
    border-radius: 16px;
}

.nav-item.active .active-indicator {
    transform: scale(1);
    background: rgba(59, 130, 246, 0.15);
}

.nav-item.active .nav-icon {
    color: #3b82f6;
    transform: translateY(-2px) scale(1.1);
}

.nav-item.active .nav-label {
    color: #3b82f6;
    font-weight: 600;
}

/* Touch interaction effects */
.nav-item:active {
    transform: scale(0.95);
}

.nav-item:active .haptic-feedback {
    background: rgba(59, 130, 246, 0.2);
    animation: hapticRipple 0.6s ease-out;
}

@keyframes hapticRipple {
    0% {
        transform: scale(0);
        opacity: 0.6;
    }
    100% {
        transform: scale(1);
        opacity: 0;
    }
}

/* Hover effects for devices that support hover */
@media (hover: hover) {
    .nav-item:hover:not(.active) {
        background: rgba(107, 114, 128, 0.1);
        border-radius: 12px;
    }
    
    .nav-item:hover .nav-icon {
        color: #4b5563;
        transform: translateY(-1px) scale(1.05);
    }
    
    .nav-item:hover .nav-label {
        color: #4b5563;
    }
}

/* Icon animations */
.nav-icon {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.nav-item:not(.active):active .nav-icon {
    transform: scale(1.2);
}

/* Emoji toggle functionality */
.nav-item.show-emoji .nav-icon {
    opacity: 0;
}

.nav-item.show-emoji .nav-emoji {
    opacity: 1;
}

/* Badge styling */
.nav-item [class*="bg-red-500"] {
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    animation: badgePulse 2s infinite;
}

@keyframes badgePulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Floating Action Button */
.quick-action-fab {
    z-index: 10;
}

.fab-button {
    transform: translateY(-50%);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
}

.fab-button:hover {
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.6);
}

.fab-button:active {
    transform: translateY(-50%) scale(0.95);
}

/* Loading states */
.nav-item.loading .nav-icon {
    animation: spin 1s linear infinite;
}

.nav-item.loading .nav-label {
    opacity: 0.5;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Connection status indicator */
.mobile-bottom-nav.offline {
    background: rgba(239, 68, 68, 0.1);
    border-top-color: rgba(239, 68, 68, 0.3);
}

.mobile-bottom-nav.offline::before {
    content: 'Offline Mode';
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    background: #ef4444;
    color: white;
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 500;
}

/* Responsive adjustments */
@media (max-width: 320px) {
    .nav-item .nav-icon {
        width: 20px;
        height: 20px;
    }
    
    .nav-item .nav-label {
        font-size: 10px;
    }
    
    .fab-button {
        width: 44px;
        height: 44px;
    }
    
    .fab-button svg {
        width: 20px;
        height: 20px;
    }
}

/* Large screen adjustments (tablets in portrait) */
@media (min-width: 480px) and (max-width: 768px) {
    .nav-item {
        min-height: 64px;
    }
    
    .nav-item .nav-icon {
        width: 28px;
        height: 28px;
    }
    
    .nav-item .nav-label {
        font-size: 14px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .mobile-bottom-nav {
        background: rgba(17, 24, 39, 0.95);
        border-top-color: rgba(75, 85, 99, 0.8);
    }
    
    .nav-item:not(.active) .nav-icon,
    .nav-item:not(.active) .nav-label {
        color: #9ca3af;
    }
    
    .nav-item.active {
        background: rgba(59, 130, 246, 0.2);
    }
    
    @media (hover: hover) {
        .nav-item:hover:not(.active) {
            background: rgba(75, 85, 99, 0.3);
        }
        
        .nav-item:hover .nav-icon,
        .nav-item:hover .nav-label {
            color: #d1d5db;
        }
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .mobile-bottom-nav {
        border-top-width: 2px;
        border-top-color: #000;
    }
    
    .nav-item.active {
        border: 2px solid #3b82f6;
    }
    
    .nav-item:not(.active) {
        border: 1px solid transparent;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .nav-item,
    .nav-icon,
    .nav-label,
    .active-indicator,
    .fab-button {
        transition: none;
        animation: none;
    }
    
    .nav-item:active {
        transform: none;
    }
    
    .nav-item.active .nav-icon {
        transform: none;
    }
}

/* Print styles */
@media print {
    .mobile-bottom-nav {
        display: none;
    }
}

/* Landscape orientation adjustments */
@media screen and (orientation: landscape) and (max-height: 500px) {
    .mobile-bottom-nav nav {
        padding: 4px 8px;
    }
    
    .nav-item {
        min-height: 48px;
    }
    
    .nav-item .nav-icon {
        width: 20px;
        height: 20px;
    }
    
    .nav-item .nav-label {
        font-size: 10px;
    }
    
    .quick-action-fab {
        display: none; /* Hide FAB in landscape to save space */
    }
}

/* iOS Safari specific fixes */
@supports (-webkit-touch-callout: none) {
    .mobile-bottom-nav {
        /* Ensure proper safe area handling on iOS */
        padding-bottom: max(0.5rem, constant(safe-area-inset-bottom));
        padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
    }
}

/* Animation for page transitions */
.nav-item.navigating {
    opacity: 0.6;
}

.nav-item.navigating .nav-icon {
    animation: navigationPulse 0.5s ease-in-out;
}

@keyframes navigationPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navId = '{{ $navId }}';
    const bottomNav = document.getElementById(navId);
    const navItems = bottomNav.querySelectorAll('.nav-item');
    const fabButton = bottomNav.querySelector('.fab-button');
    
    if (!bottomNav) return;

    // State management
    let currentActiveTab = '{{ $activeTab }}';
    let isOnline = navigator.onLine;
    let touchStartTime = 0;
    
    // Initialize navigation
    initializeNavigation();
    setupNetworkDetection();
    setupFAB();
    setupSwipeGestures();
    setupHapticFeedback();

    function initializeNavigation() {
        navItems.forEach((item, index) => {
            const itemId = item.getAttribute('data-nav-item');
            
            // Add click handler
            item.addEventListener('click', handleNavItemClick);
            
            // Add touch handlers for enhanced feedback
            item.addEventListener('touchstart', handleTouchStart, { passive: true });
            item.addEventListener('touchend', handleTouchEnd, { passive: true });
            
            // Add keyboard navigation
            item.addEventListener('keydown', handleKeyDown);
            
            // Set initial state
            if (itemId === currentActiveTab) {
                item.classList.add('active');
                item.setAttribute('aria-selected', 'true');
            }
            
            // Add animation delay for initial load
            item.style.animationDelay = `${index * 100}ms`;
            item.classList.add('animate-fade-in-up');
        });
    }

    function handleNavItemClick(e) {
        const item = e.currentTarget;
        const itemId = item.getAttribute('data-nav-item');
        const href = item.getAttribute('href');
        
        // Prevent default if no valid route
        if (href === '#') {
            e.preventDefault();
            showComingSoon(itemId);
            return;
        }
        
        // Add navigation loading state
        item.classList.add('navigating', 'loading');
        
        // Update active state immediately for better UX
        updateActiveState(itemId);
        
        // Haptic feedback on supported devices
        triggerHapticFeedback('light');
        
        // Allow natural navigation to proceed
        // The loading state will be reset on page load
    }

    function handleTouchStart(e) {
        touchStartTime = Date.now();
        const item = e.currentTarget;
        
        // Add visual feedback immediately
        item.style.transform = 'scale(0.95)';
        
        // Trigger haptic feedback
        triggerHapticFeedback('selection');
    }

    function handleTouchEnd(e) {
        const item = e.currentTarget;
        const touchDuration = Date.now() - touchStartTime;
        
        // Reset transform
        setTimeout(() => {
            item.style.transform = '';
        }, 150);
        
        // Long press detection (500ms+)
        if (touchDuration > 500) {
            handleLongPress(item);
        }
    }

    function handleKeyDown(e) {
        const item = e.currentTarget;
        
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            item.click();
        }
        
        // Arrow key navigation
        if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            e.preventDefault();
            navigateWithKeyboard(e.key === 'ArrowLeft' ? -1 : 1);
        }
    }

    function handleLongPress(item) {
        const itemId = item.getAttribute('data-nav-item');
        
        // Show contextual menu or additional options
        showContextMenu(item, itemId);
        
        // Strong haptic feedback
        triggerHapticFeedback('heavy');
    }

    function updateActiveState(newActiveTab) {
        navItems.forEach(item => {
            const itemId = item.getAttribute('data-nav-item');
            const isActive = itemId === newActiveTab;
            
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
        
        currentActiveTab = newActiveTab;
    }

    function navigateWithKeyboard(direction) {
        const currentIndex = Array.from(navItems).findIndex(item => 
            item.classList.contains('active')
        );
        
        if (currentIndex === -1) return;
        
        const nextIndex = (currentIndex + direction + navItems.length) % navItems.length;
        const nextItem = navItems[nextIndex];
        
        if (nextItem) {
            nextItem.focus();
            nextItem.click();
        }
    }

    function setupNetworkDetection() {
        function updateNetworkStatus() {
            isOnline = navigator.onLine;
            bottomNav.classList.toggle('offline', !isOnline);
            
            // Update nav items accessibility
            navItems.forEach(item => {
                if (!isOnline) {
                    item.setAttribute('aria-disabled', 'true');
                    item.style.opacity = '0.6';
                } else {
                    item.removeAttribute('aria-disabled');
                    item.style.opacity = '';
                }
            });
            
            // Show toast notification
            if (!isOnline) {
                showToast('You are offline. Some features may not work.', 'warning');
            } else {
                showToast('Back online!', 'success');
            }
        }
        
        window.addEventListener('online', updateNetworkStatus);
        window.addEventListener('offline', updateNetworkStatus);
        
        // Initial check
        updateNetworkStatus();
    }

    function setupFAB() {
        if (!fabButton) return;
        
        fabButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const action = this.getAttribute('data-quick-action');
            
            // Add click animation
            this.style.transform = 'translateY(-50%) scale(0.9)';
            setTimeout(() => {
                this.style.transform = 'translateY(-50%) scale(1)';
            }, 150);
            
            // Handle different actions
            switch(action) {
                case 'search':
                    openQuickSearch();
                    break;
                case 'scan':
                    openTicketScanner();
                    break;
                default:
                    console.log('FAB action:', action);
            }
            
            triggerHapticFeedback('medium');
        });
    }

    function setupSwipeGestures() {
        if (!window.mobileUtils?.enableSwipeGestures) return;
        
        window.mobileUtils.enableSwipeGestures(bottomNav, {
            swipeUp: function(e) {
                // Swipe up to show search or quick actions
                openQuickSearch();
            },
            swipeDown: function(e) {
                // Swipe down to minimize (if applicable)
                console.log('Swipe down on bottom nav');
            },
            swipeLeft: function(e) {
                // Navigate to next tab
                navigateWithKeyboard(1);
            },
            swipeRight: function(e) {
                // Navigate to previous tab
                navigateWithKeyboard(-1);
            }
        });
    }

    function setupHapticFeedback() {
        // Feature detection for haptic feedback
        if ('vibrate' in navigator) {
            window.triggerHapticFeedback = function(type) {
                const patterns = {
                    light: [10],
                    medium: [20],
                    heavy: [30],
                    selection: [5],
                    success: [10, 100, 10],
                    error: [100, 50, 100]
                };
                
                if (patterns[type]) {
                    navigator.vibrate(patterns[type]);
                }
            };
        } else {
            // Fallback for devices without vibration
            window.triggerHapticFeedback = function(type) {
                // Visual feedback as fallback
                bottomNav.classList.add('haptic-visual-feedback');
                setTimeout(() => {
                    bottomNav.classList.remove('haptic-visual-feedback');
                }, 200);
            };
        }
    }

    function openQuickSearch() {
        // Create quick search overlay
        const searchOverlay = document.createElement('div');
        searchOverlay.className = 'quick-search-overlay fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end md:items-center md:justify-center';
        
        const searchContainer = document.createElement('div');
        searchContainer.className = 'bg-white w-full max-w-md mx-auto rounded-t-xl md:rounded-xl p-6 transform transition-transform duration-300 translate-y-full';
        
        searchContainer.innerHTML = `
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Quick Search</h3>
                <button class="close-search text-gray-500 hover:text-gray-700 p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="relative mb-4">
                <input type="text" placeholder="Search for tickets, events, venues..." 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <button class="search-filter-btn bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-sm">🏈 NFL</button>
                <button class="search-filter-btn bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-sm">🏀 NBA</button>
                <button class="search-filter-btn bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-sm">⚾ MLB</button>
                <button class="search-filter-btn bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-full text-sm">⚽ Soccer</button>
            </div>
        `;
        
        searchOverlay.appendChild(searchContainer);
        document.body.appendChild(searchOverlay);
        
        // Animate in
        requestAnimationFrame(() => {
            searchContainer.style.transform = 'translateY(0)';
        });
        
        // Focus input
        setTimeout(() => {
            const input = searchContainer.querySelector('input');
            input?.focus();
        }, 300);
        
        // Close handlers
        function closeSearch() {
            searchContainer.style.transform = 'translateY(100%)';
            setTimeout(() => {
                searchOverlay.remove();
            }, 300);
        }
        
        searchOverlay.addEventListener('click', (e) => {
            if (e.target === searchOverlay) closeSearch();
        });
        
        searchContainer.querySelector('.close-search')?.addEventListener('click', closeSearch);
        
        // Escape key to close
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                closeSearch();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    function showContextMenu(item, itemId) {
        // Create context menu for long press
        const menu = document.createElement('div');
        menu.className = 'context-menu absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 bg-white rounded-lg shadow-xl border p-2 z-50 min-w-32';
        
        const actions = getContextActions(itemId);
        
        menu.innerHTML = actions.map(action => 
            `<button class="context-action w-full text-left px-3 py-2 text-sm hover:bg-gray-100 rounded" data-action="${action.id}">
                ${action.icon} ${action.label}
            </button>`
        ).join('');
        
        item.appendChild(menu);
        
        // Add click handlers
        menu.addEventListener('click', (e) => {
            const actionBtn = e.target.closest('.context-action');
            if (actionBtn) {
                const actionId = actionBtn.getAttribute('data-action');
                handleContextAction(itemId, actionId);
                menu.remove();
            }
        });
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (menu.parentNode) {
                menu.remove();
            }
        }, 3000);
    }

    function getContextActions(itemId) {
        const baseActions = [
            { id: 'share', label: 'Share', icon: '📤' }
        ];
        
        switch(itemId) {
            case 'home':
                return [...baseActions, 
                    { id: 'refresh', label: 'Refresh', icon: '🔄' }
                ];
            case 'favorites':
                return [...baseActions,
                    { id: 'clear', label: 'Clear All', icon: '🗑️' }
                ];
            case 'cart':
                return [...baseActions,
                    { id: 'checkout', label: 'Quick Checkout', icon: '💳' }
                ];
            default:
                return baseActions;
        }
    }

    function handleContextAction(itemId, actionId) {
        console.log(`Context action: ${actionId} on ${itemId}`);
        
        switch(actionId) {
            case 'refresh':
                window.location.reload();
                break;
            case 'share':
                shareCurrentPage();
                break;
            case 'clear':
                // Handle clearing favorites/cart
                break;
            case 'checkout':
                // Navigate to quick checkout
                break;
        }
    }

    function shareCurrentPage() {
        if (navigator.share) {
            navigator.share({
                title: document.title,
                text: 'Check out these sports tickets!',
                url: window.location.href
            });
        } else {
            // Fallback
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast('Link copied to clipboard!', 'success');
            });
        }
    }

    function showComingSoon(feature) {
        showToast(`${feature.charAt(0).toUpperCase() + feature.slice(1)} feature coming soon!`, 'info');
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg text-white font-medium ${getToastColor(type)} animate-fade-in-scale`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    function getToastColor(type) {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };
        return colors[type] || colors.info;
    }

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') {
            // Refresh badge counts when app becomes visible
            updateBadgeCounts();
        }
    });

    function updateBadgeCounts() {
        // This would typically fetch updated counts from an API
        // For now, just trigger a visual refresh
        navItems.forEach(item => {
            const badge = item.querySelector('[class*="bg-red-500"]');
            if (badge) {
                badge.classList.add('animate-pulse');
                setTimeout(() => {
                    badge.classList.remove('animate-pulse');
                }, 1000);
            }
        });
    }

    // Export utilities for external use
    window.mobileBottomNav = {
        updateActiveTab: updateActiveState,
        updateBadgeCount: function(itemId, count) {
            const item = bottomNav.querySelector(`[data-nav-item="${itemId}"]`);
            const badge = item?.querySelector('[class*="bg-red-500"]');
            if (badge && badge.textContent) {
                badge.textContent = count > 99 ? '99+' : count.toString();
            }
        },
        triggerHaptic: triggerHapticFeedback,
        showToast: showToast
    };
});
</script>
@endpush
