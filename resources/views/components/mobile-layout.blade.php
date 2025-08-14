@props([
    'title' => '',
    'showBackButton' => false,
    'backUrl' => null,
    'showSearch' => false,
    'searchPlaceholder' => 'Search...',
    'showUserMenu' => true,
    'fixedHeader' => true,
    'pullToRefresh' => false,
    'swipeGestures' => false
])

<div class="mobile-layout min-h-screen bg-gray-50 relative" 
     data-mobile-layout
     @if($pullToRefresh) data-pull-to-refresh @endif
     @if($swipeGestures) data-swipe-gestures @endif>
     
    <!-- Mobile Header -->
    <header class="mobile-header bg-white shadow-sm border-b border-gray-200 {{ $fixedHeader ? 'fixed top-0 left-0 right-0 z-50' : '' }}">
        <div class="flex items-center justify-between px-4 py-3 h-16">
            <!-- Left side -->
            <div class="flex items-center space-x-3">
                @if($showBackButton)
                    <button onclick="window.history.back()" 
                            class="touch-target p-2 -ml-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <span class="sr-only">Go back</span>
                    </button>
                @endif
                
                <!-- App Logo/Title -->
                <div class="flex items-center space-x-2">
                    <x-application-logo class="h-8 w-auto fill-current text-blue-600" />
                    @if($title)
                        <h1 class="text-lg font-semibold text-gray-900 truncate">{{ $title }}</h1>
                    @endif
                </div>
            </div>
            
            <!-- Right side -->
            <div class="flex items-center space-x-2">
                @if($showSearch)
                    <button class="mobile-search-toggle touch-target p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="sr-only">Search</span>
                    </button>
                @endif
                
                @if($showUserMenu)
                    <!-- User Menu Button -->
                    <button class="mobile-user-menu-toggle touch-target p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        @php
                            $profileDisplay = Auth::user()->getProfileDisplay();
                        @endphp
                        @if($profileDisplay['has_picture'])
                            <img class="w-6 h-6 rounded-full object-cover" 
                                 src="{{ $profileDisplay['picture_url'] }}" 
                                 alt="{{ $profileDisplay['display_name'] }}">
                        @else
                            <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $profileDisplay['initials'] }}
                                </span>
                            </div>
                        @endif
                    </button>
                @endif
            </div>
        </div>
        
        <!-- Mobile Search Bar (Hidden by default) -->
        @if($showSearch)
            <div class="mobile-search-bar hidden border-t border-gray-200 px-4 py-3">
                <div class="relative">
                    <input type="text" 
                           class="form-input w-full pl-10 pr-4 py-2 text-base border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20" 
                           placeholder="{{ $searchPlaceholder }}">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        @endif
    </header>
    
    <!-- Pull-to-refresh indicator -->
    @if($pullToRefresh)
        <div class="pull-to-refresh-indicator fixed top-16 left-1/2 transform -translate-x-1/2 -translate-y-full transition-transform duration-200 z-40">
            <div class="bg-white rounded-full shadow-lg p-3">
                <svg class="w-5 h-5 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
        </div>
    @endif
    
    <!-- Main Content -->
    <main class="mobile-content {{ $fixedHeader ? 'pt-16' : '' }} pb-6">
        <div class="content-wrapper">
            {{ $slot }}
        </div>
    </main>
    
    <!-- Mobile User Menu Overlay -->
    @if($showUserMenu)
        <div class="mobile-user-menu fixed inset-0 z-50 hidden">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            
            <!-- Menu Panel -->
            <div class="fixed bottom-0 left-0 right-0 bg-white rounded-t-xl shadow-xl transform transition-transform translate-y-full">
                <div class="p-6">
                    <!-- User Info -->
                    <div class="flex items-center space-x-4 mb-6">
                        @if($profileDisplay['has_picture'])
                            <img class="w-12 h-12 rounded-full object-cover" 
                                 src="{{ $profileDisplay['picture_url'] }}" 
                                 alt="{{ $profileDisplay['display_name'] }}">
                        @else
                            <div class="w-12 h-12 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-lg font-medium text-gray-700">
                                    {{ $profileDisplay['initials'] }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $profileDisplay['display_name'] }}</h3>
                            <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    
                    <!-- Menu Items -->
                    <div class="space-y-2">
                        <a href="{{ route('profile.show') }}" 
                           class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                            </svg>
                            Dashboard
                        </a>
                        
                        @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
                            <hr class="my-2">
                            <a href="{{ route('tickets.scraping.index') }}" 
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                </svg>
                                Sports Tickets
                            </a>
                            
                            <a href="{{ route('tickets.alerts.index') }}" 
                               class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                                </svg>
                                My Alerts
                            </a>
                        @endif
                        
                        <hr class="my-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full flex items-center px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors text-left">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Close Handle -->
                <div class="flex justify-center py-2">
                    <div class="w-12 h-1 bg-gray-300 rounded-full"></div>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/mobile-enhancements.css') }}{{ css_with_timestamp('') }}">
<style>
    /* Mobile layout specific styles */
    .mobile-layout {
        /* Ensure proper mobile viewport handling */
        min-height: 100vh;
        min-height: calc(var(--vh, 1vh) * 100);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    
    .mobile-header {
        /* Handle safe area insets for devices with notches */
        padding-top: env(safe-area-inset-top);
        padding-left: max(1rem, env(safe-area-inset-left));
        padding-right: max(1rem, env(safe-area-inset-right));
    }
    
    .mobile-content {
        /* Account for header height and safe areas */
        padding-left: max(1rem, env(safe-area-inset-left));
        padding-right: max(1rem, env(safe-area-inset-right));
        padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
    }
    
    /* Pull-to-refresh styles */
    .pull-to-refresh-active .pull-to-refresh-indicator {
        transform: translateX(-50%) translateY(0);
    }
    
    /* User menu animation */
    .mobile-user-menu.show {
        display: block;
    }
    
    .mobile-user-menu.show .bg-black {
        opacity: 0.5;
    }
    
    .mobile-user-menu.show > div:last-child {
        transform: translateY(0);
    }
    
    /* Search bar animation */
    .mobile-search-bar.show {
        display: block;
        animation: slideDown 0.2s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Touch feedback */
    .touch-target:active {
        transform: scale(0.95);
    }
    
    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .mobile-header {
            border-bottom-width: 2px;
        }
        
        .touch-target {
            border: 1px solid currentColor;
        }
    }
    
    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        .mobile-user-menu > div:last-child,
        .pull-to-refresh-indicator,
        .mobile-search-bar {
            transition: none;
        }
        
        .touch-target:active {
            transform: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileLayout = document.querySelector('[data-mobile-layout]');
    if (!mobileLayout) return;
    
    // Initialize mobile layout enhancements
    initializeMobileLayout(mobileLayout);
    
    function initializeMobileLayout(layout) {
        // Check if this is a mobile device
        const isMobile = window.responsiveUtils ? window.responsiveUtils.isMobile() : 
                        /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        
        if (!isMobile) {
            console.log('Not a mobile device, skipping mobile layout enhancements');
            return;
        }
        
        // Add mobile device class
        document.body.classList.add('mobile-device');
        
        // Setup search functionality
        setupMobileSearch();
        
        // Setup user menu
        setupMobileUserMenu();
        
        // Setup pull-to-refresh
        setupPullToRefresh(layout);
        
        // Setup swipe gestures
        setupSwipeGestures(layout);
        
        // Setup keyboard handling
        setupMobileKeyboard(layout);
        
        // Setup haptic feedback
        setupHapticFeedback();
        
        // Setup progressive disclosure
        setupProgressiveDisclosure();
        
        // Setup mobile tables
        setupMobileTables();
        
        // Setup connection status
        setupConnectionStatus();
        
        // Setup accessibility enhancements
        setupAccessibilityEnhancements();
        
        console.log('🚀 Mobile layout enhancements initialized');
    }
    
    function setupMobileSearch() {
        const searchToggle = document.querySelector('.mobile-search-toggle');
        const searchBar = document.querySelector('.mobile-search-bar');
        
        if (searchToggle && searchBar) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                searchBar.classList.toggle('hidden');
                searchBar.classList.toggle('show');
                
                // Trigger haptic feedback
                if (window.mobileTouchUtils) {
                    window.mobileTouchUtils.triggerHapticFeedback('light');
                }
                
                if (!searchBar.classList.contains('hidden')) {
                    const input = searchBar.querySelector('input');
                    if (input) {
                        setTimeout(() => input.focus(), 100);
                    }
                }
            });
            
            // Close search on escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !searchBar.classList.contains('hidden')) {
                    searchBar.classList.add('hidden');
                    searchBar.classList.remove('show');
                    searchToggle.focus();
                }
            });
        }
    }
    
    function setupMobileUserMenu() {
        const userMenuToggle = document.querySelector('.mobile-user-menu-toggle');
        const userMenu = document.querySelector('.mobile-user-menu');
        
        if (userMenuToggle && userMenu) {
            userMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                const isOpen = !userMenu.classList.contains('hidden');
                
                userMenu.classList.toggle('hidden');
                userMenu.classList.toggle('show');
                document.body.classList.toggle('overflow-hidden');
                
                // Update ARIA attributes
                userMenuToggle.setAttribute('aria-expanded', !isOpen);
                
                // Trigger haptic feedback
                if (window.mobileTouchUtils) {
                    window.mobileTouchUtils.triggerHapticFeedback('medium');
                }
                
                // Focus first menu item when opening
                if (!isOpen) {
                    setTimeout(() => {
                        const firstMenuItem = userMenu.querySelector('a, button');
                        if (firstMenuItem) firstMenuItem.focus();
                    }, 100);
                }
            });
            
            // Close menu when clicking backdrop
            userMenu.addEventListener('click', function(e) {
                if (e.target === this || e.target.classList.contains('bg-black')) {
                    closeUserMenu();
                }
            });
            
            // Close menu on escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !userMenu.classList.contains('hidden')) {
                    closeUserMenu();
                }
            });
            
            // Swipe down to close menu
            if (window.mobileTouchUtils) {
                window.mobileTouchUtils.enableSwipe(userMenu, {
                    swipeDown: function() {
                        closeUserMenu();
                    }
                });
            }
            
            function closeUserMenu() {
                userMenu.classList.add('hidden');
                userMenu.classList.remove('show');
                document.body.classList.remove('overflow-hidden');
                userMenuToggle.setAttribute('aria-expanded', 'false');
                userMenuToggle.focus();
            }
        }
    }
    
    function setupPullToRefresh(layout) {
        if (!layout.hasAttribute('data-pull-to-refresh')) return;
        
        if (window.mobileOptimization) {
            window.mobileOptimization.enablePullToRefresh(layout, async function() {
                // Show loading indicator
                const indicator = layout.querySelector('.pull-to-refresh-indicator');
                if (indicator) {
                    indicator.classList.add('refreshing');
                }
                
                // Trigger haptic feedback
                if (window.mobileTouchUtils) {
                    window.mobileTouchUtils.triggerHapticFeedback('success');
                }
                
                try {
                    // Custom refresh logic or page reload
                    if (window.customRefreshHandler) {
                        await window.customRefreshHandler();
                    } else {
                        await new Promise(resolve => setTimeout(resolve, 1000));
                        window.location.reload();
                    }
                } finally {
                    if (indicator) {
                        indicator.classList.remove('refreshing');
                    }
                }
            });
        }
    }
    
    function setupSwipeGestures(layout) {
        if (!layout.hasAttribute('data-swipe-gestures')) return;
        
        if (window.mobileOptimization) {
            window.mobileOptimization.enableSwipeForElement(layout, {
                swipeLeft: function() {
                    // Navigate forward or show next content
                    console.log('Swiped left - navigate forward');
                    
                    // Trigger haptic feedback
                    if (window.mobileTouchUtils) {
                        window.mobileTouchUtils.triggerHapticFeedback('light');
                    }
                    
                    // Custom navigation logic
                    const nextButton = document.querySelector('[data-next-page]');
                    if (nextButton) {
                        nextButton.click();
                    }
                },
                swipeRight: function() {
                    // Navigate back or show previous content
                    console.log('Swiped right - navigate back');
                    
                    // Trigger haptic feedback
                    if (window.mobileTouchUtils) {
                        window.mobileTouchUtils.triggerHapticFeedback('light');
                    }
                    
                    // Navigate back if possible
                    if (document.referrer && window.history.length > 1) {
                        window.history.back();
                    } else {
                        const backButton = document.querySelector('[data-back-button]');
                        if (backButton) {
                            backButton.click();
                        }
                    }
                },
                swipeUp: function() {
                    // Scroll to top or show additional content
                    console.log('Swiped up');
                    
                    const scrollToTop = document.querySelector('[data-scroll-to-top]');
                    if (scrollToTop) {
                        scrollToTop.click();
                    } else {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                swipeDown: function() {
                    // Show additional options or refresh
                    console.log('Swiped down');
                    
                    if (window.scrollY === 0) {
                        // Trigger pull-to-refresh if at top
                        const refreshEvent = new CustomEvent('mobile:pull-to-refresh');
                        layout.dispatchEvent(refreshEvent);
                    }
                }
            });
        }
    }
    
    function setupMobileKeyboard(layout) {
        // Handle keyboard show/hide events
        document.addEventListener('mobile:keyboard:show', function(e) {
            layout.classList.add('keyboard-visible');
            
            // Scroll active input into view
            const activeElement = document.activeElement;
            if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
                setTimeout(() => {
                    activeElement.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center',
                        inline: 'nearest'
                    });
                }, 300);
            }
        });
        
        document.addEventListener('mobile:keyboard:hide', function() {
            layout.classList.remove('keyboard-visible');
        });
        
        // Handle input focus for better UX
        document.addEventListener('focusin', function(e) {
            if (e.target.matches('input, textarea, select')) {
                e.target.classList.add('input-focused');
                
                // Add focus class to parent form group
                const formGroup = e.target.closest('.form-group, .mobile-form-group');
                if (formGroup) {
                    formGroup.classList.add('has-focus');
                }
            }
        });
        
        document.addEventListener('focusout', function(e) {
            if (e.target.matches('input, textarea, select')) {
                e.target.classList.remove('input-focused');
                
                // Remove focus class from parent form group
                const formGroup = e.target.closest('.form-group, .mobile-form-group');
                if (formGroup) {
                    formGroup.classList.remove('has-focus');
                }
            }
        });
    }
    
    function setupHapticFeedback() {
        // Add haptic feedback to interactive elements
        document.addEventListener('click', function(e) {
            const target = e.target.closest('button, a, [data-haptic]');
            if (target && window.mobileTouchUtils) {
                const hapticType = target.dataset.haptic || 'light';
                window.mobileTouchUtils.triggerHapticFeedback(hapticType);
            }
        });
        
        // Add haptic feedback to form submissions
        document.addEventListener('submit', function(e) {
            if (window.mobileTouchUtils) {
                window.mobileTouchUtils.triggerHapticFeedback('medium');
            }
        });
        
        // Add haptic feedback to invalid form inputs
        document.addEventListener('invalid', function(e) {
            if (window.mobileTouchUtils) {
                window.mobileTouchUtils.triggerHapticFeedback('error');
            }
        }, true);
    }
    
    function setupProgressiveDisclosure() {
        // Setup expandable content sections
        document.querySelectorAll('[data-expandable]').forEach(function(element) {
            const trigger = element.querySelector('[data-expand-trigger]');
            const content = element.querySelector('[data-expand-content]');
            const icon = trigger ? trigger.querySelector('[data-expand-icon]') : null;
            
            if (trigger && content) {
                trigger.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const isExpanded = element.classList.contains('expanded');
                    element.classList.toggle('expanded');
                    
                    // Update ARIA attributes
                    trigger.setAttribute('aria-expanded', !isExpanded);
                    
                    // Rotate icon if present
                    if (icon) {
                        icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
                    }
                    
                    // Trigger haptic feedback
                    if (window.mobileTouchUtils) {
                        window.mobileTouchUtils.triggerHapticFeedback('light');
                    }
                    
                    // Scroll into view if expanding
                    if (!isExpanded) {
                        setTimeout(() => {
                            element.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'nearest'
                            });
                        }, 300);
                    }
                });
            }
        });
        
        // Setup accordion components
        document.querySelectorAll('.mobile-accordion').forEach(function(accordion) {
            const items = accordion.querySelectorAll('.mobile-accordion-item');
            
            items.forEach(function(item) {
                const header = item.querySelector('.mobile-accordion-header');
                const content = item.querySelector('.mobile-accordion-content');
                
                if (header && content) {
                    header.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        const isActive = item.classList.contains('active');
                        
                        // Close all other items if this is an exclusive accordion
                        if (accordion.dataset.exclusive === 'true' && !isActive) {
                            items.forEach(function(otherItem) {
                                if (otherItem !== item) {
                                    otherItem.classList.remove('active');
                                    const otherHeader = otherItem.querySelector('.mobile-accordion-header');
                                    if (otherHeader) {
                                        otherHeader.setAttribute('aria-expanded', 'false');
                                    }
                                }
                            });
                        }
                        
                        // Toggle current item
                        item.classList.toggle('active');
                        header.setAttribute('aria-expanded', !isActive);
                        
                        // Trigger haptic feedback
                        if (window.mobileTouchUtils) {
                            window.mobileTouchUtils.triggerHapticFeedback('medium');
                        }
                    });
                }
            });
        });
    }
    
    function setupMobileTables() {
        // Convert tables to mobile-friendly format
        document.querySelectorAll('table:not(.mobile-optimized)').forEach(function(table) {
            if (window.mobileOptimization) {
                // Use the mobile optimization utility
                window.mobileOptimization.setupMobileTables();
            } else {
                // Fallback implementation
                table.classList.add('mobile-optimized');
                
                // Add horizontal scroll wrapper
                const wrapper = document.createElement('div');
                wrapper.className = 'mobile-table-wrapper';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
                
                // Add data attributes for mobile card view
                const headers = Array.from(table.querySelectorAll('thead th'))
                    .map(th => th.textContent.trim());
                
                table.querySelectorAll('tbody tr').forEach(function(row) {
                    Array.from(row.children).forEach(function(cell, index) {
                        if (headers[index]) {
                            cell.setAttribute('data-label', headers[index]);
                        }
                    });
                });
            }
        });
    }
    
    function setupConnectionStatus() {
        // Create offline indicator
        const offlineIndicator = document.createElement('div');
        offlineIndicator.className = 'mobile-offline-indicator';
        offlineIndicator.innerHTML = `
            <div class="flex items-center justify-center gap-2 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-12.728 12.728m0-12.728l12.728 12.728"></path>
                </svg>
                You're offline. Some features may not be available.
            </div>
        `;
        document.body.appendChild(offlineIndicator);
        
        // Update connection status
        function updateConnectionStatus() {
            if (navigator.onLine) {
                offlineIndicator.classList.remove('visible');
                document.body.classList.remove('offline');
            } else {
                offlineIndicator.classList.add('visible');
                document.body.classList.add('offline');
                
                // Trigger haptic feedback
                if (window.mobileTouchUtils) {
                    window.mobileTouchUtils.triggerHapticFeedback('warning');
                }
            }
        }
        
        // Listen for connection changes
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Initial status check
        updateConnectionStatus();
    }
    
    function setupAccessibilityEnhancements() {
        // Accessibility enhancements without skip link
        
        // Add keyboard navigation support
        document.addEventListener('keydown', function(e) {
            // Handle escape key to close modals/overlays
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal.show, .mobile-user-menu.show');
                if (openModal) {
                    const closeButton = openModal.querySelector('[data-dismiss], .close, .mobile-user-menu-toggle');
                    if (closeButton) {
                        closeButton.click();
                    }
                }
            }
        });
        
        // Detect keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('keyboard-navigation');
            }
        });
        
        document.addEventListener('mousedown', function() {
            document.body.classList.remove('keyboard-navigation');
        });
        
        // Add focus visible support for custom elements
        document.addEventListener('focusin', function(e) {
            if (e.target.matches('.touch-target, button, a')) {
                e.target.classList.add('focus-visible');
            }
        });
        
        document.addEventListener('focusout', function(e) {
            if (e.target.matches('.touch-target, button, a')) {
                e.target.classList.remove('focus-visible');
            }
        });
    }
});
</script>
@endpush
