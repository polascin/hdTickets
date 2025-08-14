{{-- Enhanced Mobile Navigation for Admin Dashboard --}}
<div x-data="{ mobileMenuOpen: false }" class="lg:hidden">
    <!-- Mobile menu button -->
    <button 
        @click="mobileMenuOpen = !mobileMenuOpen" 
        class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors duration-200"
        aria-expanded="false"
    >
        <span class="sr-only">Open main menu</span>
        <!-- Hamburger icon -->
        <svg 
            x-show="!mobileMenuOpen" 
            class="block h-6 w-6" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        <!-- Close icon -->
        <svg 
            x-show="mobileMenuOpen" 
            class="block h-6 w-6" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>

    <!-- Mobile menu overlay -->
    <div 
        x-show="mobileMenuOpen" 
        x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileMenuOpen = false"
        class="fixed inset-0 z-20 bg-black bg-opacity-25 lg:hidden"
    ></div>

    <!-- Mobile menu panel -->
    <div 
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-30 w-64 bg-white shadow-xl lg:hidden"
    >
        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <div class="flex items-center space-x-2">
                    <x-application-logo class="h-8 w-auto fill-current text-gray-800" />
                    <span class="text-lg font-semibold text-gray-900">HD Tickets</span>
                </div>
                <button 
                    @click="mobileMenuOpen = false"
                    class="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-2">
                <!-- Dashboard -->
                <a 
                    href="{{ route('dashboard') }}" 
                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                >
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                    </svg>
                    Dashboard
                </a>

                @if(Auth::user()->isAdmin())
                    <!-- Admin Section -->
                    <div class="pt-4">
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</h3>
                        <div class="mt-2 space-y-1">
                            <a 
                                href="{{ route('admin.dashboard') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Admin Panel
                            </a>

                            @if(Auth::user()->canManageUsers())
                                <a 
                                    href="{{ route('admin.users.index') }}" 
                                    class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                                >
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                    Users
                                </a>
                            @endif

                            <a 
                                href="{{ route('admin.categories.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.categories.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Categories
                            </a>

                            <a 
                                href="{{ route('admin.system.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.system.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                </svg>
                                System
                            </a>

                            <a 
                                href="{{ route('admin.scraping.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.scraping.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Scraping
                            </a>

                            <a 
                                href="{{ route('admin.reports.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('admin.reports.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Reports
                            </a>
                        </div>
                    </div>
                @endif

                @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
                    <!-- Sports Tickets Section -->
                    <div class="pt-4">
                        <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sports Tickets</h3>
                        <div class="mt-2 space-y-1">
                            <a 
                                href="{{ route('tickets.scraping.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('tickets.scraping.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                </svg>
                                Sports Tickets
                            </a>

                            <a 
                                href="{{ route('tickets.alerts.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('tickets.alerts.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                                </svg>
                                My Alerts
                                <span class="ml-auto bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded-full">3</span>
                            </a>

                            <a 
                                href="{{ route('purchase-decisions.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('purchase-decisions.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Purchase Queue
                            </a>

                            <a 
                                href="{{ route('ticket-sources.index') }}" 
                                class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('ticket-sources.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                            >
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                Sources
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Profile Section -->
                <div class="pt-4">
                    <h3 class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Profile</h3>
                    <div class="mt-2 space-y-1">
                        <a 
                            href="{{ route('profile.show') }}" 
                            class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }} transition-colors duration-200"
                        >
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Footer -->
            <div class="p-4 border-t border-gray-200">
                <div class="flex items-center space-x-3 mb-3">
                    @php
                        $profileDisplay = Auth::user()->getProfileDisplay();
                    @endphp
                    <div class="w-10 h-10 rounded-full flex items-center justify-center overflow-hidden">
                        @if($profileDisplay['has_picture'])
                            <img class="w-10 h-10 rounded-full object-cover" src="{{ $profileDisplay['picture_url'] }}" alt="{{ $profileDisplay['display_name'] }}">
                        @else
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-gray-700">
                                    {{ $profileDisplay['initials'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-900 truncate">{{ $profileDisplay['display_name'] }}</div>
                        <div class="text-xs text-gray-500 capitalize">{{ Auth::user()->role }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button 
                        type="submit" 
                        class="w-full flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 rounded-lg transition-colors duration-200"
                    >
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
