{{--
    Header Partial - Unified Layout System
    Responsive header with role-based navigation
--}}
<div class="header-content" x-data="headerManager()">
    {{-- Left Section: Logo and Brand --}}
    <div class="header-left flex items-center space-x-4">
        {{-- Mobile Sidebar Toggle --}}
        @auth
            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                <button @click="$dispatch('toggle-sidebar')" 
                        class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                        aria-label="Toggle Menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            @endif
        @endauth

        {{-- Logo and Brand --}}
        <div class="flex items-center space-x-3">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                     alt="HD Tickets" 
                     class="h-8 w-auto">
                <div class="hidden sm:block">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">HD Tickets</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sports Events Platform</p>
                </div>
            </a>
        </div>

        {{-- Desktop Navigation Links --}}
        <nav class="hidden lg:flex items-center space-x-6 ml-8">
            @auth
                {{-- Dashboard Link --}}
                <a href="{{ route('dashboard') }}" 
                   class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : 'nav-link-inactive' }}">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </a>

                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    {{-- Sports Tickets --}}
                    <a href="{{ route('tickets.scraping.index') }}" 
                       class="nav-link {{ request()->routeIs('tickets.scraping.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        Tickets
                    </a>

                    {{-- Alerts --}}
                    <a href="{{ route('tickets.alerts.index') }}" 
                       class="nav-link {{ request()->routeIs('tickets.alerts.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-bell mr-2"></i>
                        Alerts
                    </a>

                    {{-- Sources --}}
                    <a href="{{ route('ticket-sources.index') }}" 
                       class="nav-link {{ request()->routeIs('ticket-sources.*') ? 'nav-link-active' : 'nav-link-inactive' }}">
                        <i class="fas fa-link mr-2"></i>
                        Sources
                    </a>
                @endif
            @endauth
        </nav>
    </div>

    {{-- Right Section: Actions and User Menu --}}
    <div class="header-right flex items-center space-x-4">
        {{-- Search Bar (Desktop) --}}
        @auth
            <div class="hidden lg:block relative">
                <div class="relative">
                    <input type="search" 
                           placeholder="Search tickets, events..." 
                           class="form-input pl-10 pr-4 py-2 w-64 bg-gray-50 dark:bg-gray-700 border-0 focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                </div>
            </div>
        @endauth

        {{-- Notification Bell --}}
        @auth
            <div class="relative" x-data="{ notificationsOpen: false }">
                <button @click="notificationsOpen = !notificationsOpen"
                        class="relative p-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <i class="fas fa-bell w-5 h-5"></i>
                    {{-- Notification badge --}}
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                        3
                    </span>
                </button>

                {{-- Notifications Dropdown --}}
                <div x-show="notificationsOpen"
                     x-cloak
                     @click.outside="notificationsOpen = false"
                     x-transition:enter="transform ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transform ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                    
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Notifications</h3>
                    </div>
                    
                    <div class="max-h-96 overflow-y-auto">
                        {{-- Sample notification items --}}
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-100 dark:border-gray-600">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-ticket-alt text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900 dark:text-white">New tickets available for Lakers vs Warriors</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">2 minutes ago</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 text-center">
                            <a href="#" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endauth

        {{-- Theme Toggle --}}
        <button @click="$dispatch('toggle-theme')" 
                class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                title="Toggle Dark Mode">
            <i class="fas fa-moon dark:hidden w-5 h-5"></i>
            <i class="fas fa-sun hidden dark:block w-5 h-5"></i>
        </button>

        {{-- User Menu --}}
        @auth
            <div class="relative" x-data="{ userMenuOpen: false }">
                <button @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center space-x-2 p-2 rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    @php
                        $user = auth()->user();
                        $profileDisplay = $user->getProfileDisplay();
                    @endphp
                    
                    <div class="flex items-center space-x-3">
                        {{-- User Avatar --}}
                        <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0">
                            @if($profileDisplay['has_picture'])
                                <img src="{{ $profileDisplay['picture_url'] }}" 
                                     alt="{{ $profileDisplay['display_name'] }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-200">
                                        {{ $profileDisplay['initials'] }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        {{-- User Info (Hidden on mobile) --}}
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ $profileDisplay['display_name'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ ucfirst($user->getRoleNames()->first()) }}
                            </p>
                        </div>
                    </div>
                    
                    <i class="fas fa-chevron-down w-3 h-3"></i>
                </button>

                {{-- User Dropdown --}}
                <div x-show="userMenuOpen"
                     x-cloak
                     @click.outside="userMenuOpen = false"
                     x-transition:enter="transform ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transform ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-50">
                    
                    {{-- User Profile Section --}}
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
                                @if($profileDisplay['has_picture'])
                                    <img src="{{ $profileDisplay['picture_url'] }}" 
                                         alt="{{ $profileDisplay['display_name'] }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                        <span class="text-lg font-medium text-gray-700 dark:text-gray-200">
                                            {{ $profileDisplay['initials'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $profileDisplay['display_name'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 mt-1">
                                    {{ ucfirst($user->getRoleNames()->first()) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Menu Items --}}
                    <div class="py-1">
                        <a href="{{ route('profile.show') }}" 
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-user mr-3 w-4"></i>
                            Profile Settings
                        </a>
                        
                        <a href="{{ route('dashboard') }}" 
                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-tachometer-alt mr-3 w-4"></i>
                            Dashboard
                        </a>

                        @if($user->isAdmin())
                            <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                            <a href="{{ route('admin.dashboard') }}" 
                               class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-cogs mr-3 w-4"></i>
                                Admin Panel
                            </a>
                        @endif

                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="flex items-center w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 w-4"></i>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- Guest Actions --}}
            <div class="flex items-center space-x-3">
                <a href="{{ route('login') }}" 
                   class="btn-secondary">
                    Sign In
                </a>
                <a href="{{ route('register') }}" 
                   class="btn-primary">
                    Get Started
                </a>
            </div>
        @endauth
    </div>
</div>

{{-- Alpine.js Header Manager --}}
<script>
    function headerManager() {
        return {
            init() {
                // Listen for theme toggle events
                this.$el.addEventListener('toggle-theme', () => {
                    const themeManager = Alpine.store('theme');
                    if (themeManager) {
                        themeManager.toggle();
                    }
                });

                // Listen for sidebar toggle events
                this.$el.addEventListener('toggle-sidebar', () => {
                    this.$dispatch('sidebar-toggle');
                });
            }
        }
    }
</script>
