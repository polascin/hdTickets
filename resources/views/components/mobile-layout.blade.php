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
    if (!mobileLayout || !window.isMobile()) return;
    
    // Search toggle functionality
    const searchToggle = document.querySelector('.mobile-search-toggle');
    const searchBar = document.querySelector('.mobile-search-bar');
    
    if (searchToggle && searchBar) {
        searchToggle.addEventListener('click', function() {
            searchBar.classList.toggle('hidden');
            searchBar.classList.toggle('show');
            
            if (!searchBar.classList.contains('hidden')) {
                const input = searchBar.querySelector('input');
                if (input) input.focus();
            }
        });
    }
    
    // User menu functionality
    const userMenuToggle = document.querySelector('.mobile-user-menu-toggle');
    const userMenu = document.querySelector('.mobile-user-menu');
    
    if (userMenuToggle && userMenu) {
        userMenuToggle.addEventListener('click', function() {
            userMenu.classList.toggle('hidden');
            userMenu.classList.toggle('show');
            document.body.classList.toggle('overflow-hidden');
        });
        
        // Close menu when clicking backdrop
        userMenu.addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('bg-black')) {
                userMenu.classList.add('hidden');
                userMenu.classList.remove('show');
                document.body.classList.remove('overflow-hidden');
            }
        });
    }
    
    // Pull-to-refresh functionality
    if (mobileLayout.hasAttribute('data-pull-to-refresh') && window.mobileUtils) {
        window.mobileUtils.enablePullToRefresh(mobileLayout, async function() {
            // Reload the page or perform custom refresh logic
            return new Promise(resolve => {
                setTimeout(() => {
                    window.location.reload();
                    resolve();
                }, 1000);
            });
        });
    }
    
    // Swipe gestures
    if (mobileLayout.hasAttribute('data-swipe-gestures') && window.mobileUtils) {
        window.mobileUtils.enableSwipeGestures(mobileLayout, {
            swipeLeft: function() {
                // Navigate forward or show next content
                console.log('Swiped left');
            },
            swipeRight: function() {
                // Navigate back or show previous content
                if (document.referrer) {
                    window.history.back();
                }
            }
        });
    }
    
    // Handle mobile keyboard
    document.addEventListener('mobile:keyboard:open', function(e) {
        mobileLayout.classList.add('keyboard-open');
        
        // Scroll active input into view
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            setTimeout(() => {
                activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 300);
        }
    });
    
    document.addEventListener('mobile:keyboard:close', function() {
        mobileLayout.classList.remove('keyboard-open');
    });
});
</script>
@endpush
