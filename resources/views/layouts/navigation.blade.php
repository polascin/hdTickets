{{-- 
    Enhanced Navigation Component
    - Improved mobile responsiveness
    - Consistent desktop/mobile links
    - Better organization and readability
    - Role-based access control
    - Uses registered Alpine.js navigationData component
--}}
@php
    use Illuminate\Support\Facades\Request;
@endphp
<nav x-data="navigationData()" x-init="console.log('🔧 Navigation initialized:', $data)" class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40" role="navigation"
    aria-label="Main navigation" id="main-navigation">

    <!-- Primary Navigation Menu -->
    <div class="hd-container">
        <div class="flex justify-between items-center" style="height: var(--hd-header-height-desktop);">
            @media (max-width: 767px) {
                <style>
                    #main-navigation .hd-container > div {
                        height: var(--hd-header-height-mobile) !important;
                    }
                </style>
            @endmedia
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2"
                        aria-label="HD Tickets Home - Navigate to dashboard">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" aria-hidden="true" />
                        <span class="hidden lg:block text-lg font-semibold text-gray-900">HD Tickets</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    {{-- Dashboard Link - Available to all users --}}
                    <x-nav-link :href="route('dashboard')" :active="Request::routeIs('dashboard')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0M8 5a2 2 0 012-2h4a2 2 0 012 2v0"></path>
                        </svg>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    @if (Auth::user()->isAdmin() || Auth::user()->isAgent())
                        {{-- Agent-specific check for debugging --}}
                        @if (Auth::user()->isAgent())
                            {{-- Agent has access to these features --}}
                        @endif
                        {{-- Sports Tickets --}}
                        <x-nav-link :href="route('tickets.scraping.index')" :active="Request::routeIs('tickets.scraping.*')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                                </path>
                            </svg>
                            {{ __('Sports Tickets') }}
                        </x-nav-link>

                        {{-- Ticket Alerts --}}
                        <x-nav-link :href="route('tickets.alerts.index')" :active="Request::routeIs('tickets.alerts.*')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                            </svg>
                            {{ __('My Alerts') }}
                        </x-nav-link>

                        {{-- Purchase Queue --}}
                        <x-nav-link :href="route('purchase-decisions.index')" :active="Request::routeIs('purchase-decisions.*')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            {{ __('Purchase Queue') }}
                        </x-nav-link>

                        {{-- Ticket Sources --}}
                        <x-nav-link :href="route('ticket-sources.index')" :active="Request::routeIs('ticket-sources.*')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                                </path>
                            </svg>
                            {{ __('Sources') }}
                        </x-nav-link>
                    @endif

                    {{-- Profile Link with Completion Indicator - Available to all users --}}
                    <x-nav-link :href="route('profile.show')" :active="Request::routeIs('profile.*')" class="relative">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        {{ __('Profile') }}

                        {{-- Profile completion indicator --}}
                        @php
                            $profileCompletion = Auth::user()->getProfileCompletion();
                        @endphp
                        @if ($profileCompletion['percentage'] < 90)
                            <span
                                class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-xs font-bold text-white bg-yellow-500 rounded-full">
                                !
                            </span>
                        @endif
                    </x-nav-link>

                    @if (Auth::user()->isAdmin())
                        {{-- Admin Dropdown --}}
                        <div class="relative" @click.outside="adminDropdownOpen = false">
                            <button @click="toggleAdminDropdown();"
                                class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                :aria-expanded="adminDropdownOpen" aria-haspopup="true">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ __('Admin') }}
                                <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="adminDropdownOpen" x-cloak x-transition:enter="transform ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transform ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute z-50 mt-1 w-64 bg-white rounded-md shadow-lg py-1 border border-gray-200"
                                @click="adminDropdownOpen = false">
                                {{-- Admin Dashboard --}}
                                <a href="{{ route('admin.dashboard') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out {{ Request::routeIs('admin.dashboard') ? 'bg-gray-50 text-blue-600' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                        </path>
                                    </svg>
                                    {{ __('Admin Dashboard') }}
                                </a>

                                {{-- Reports --}}
                                <a href="{{ route('admin.reports.index') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out {{ Request::routeIs('admin.reports.*') ? 'bg-gray-50 text-blue-600' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    {{ __('Reports') }}
                                </a>

                                {{-- Separator --}}
                                <div class="border-t border-gray-100 my-1"></div>

                                @if (Auth::user()->canManageUsers())
                                    <a href="{{ route('admin.users.index') }}"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out {{ Request::routeIs('admin.users.*') ? 'bg-gray-50 text-blue-600' : '' }}">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                            </path>
                                        </svg>
                                        {{ __('User Management') }}
                                    </a>
                                @endif
                                <a href="{{ route('admin.categories.index') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                        </path>
                                    </svg>
                                    {{ __('Categories') }}
                                </a>
                                <a href="{{ route('admin.system.index') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                                        </path>
                                    </svg>
                                    {{ __('System') }}
                                </a>
                                <a href="{{ route('admin.scraping.index') }}"
                                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                    {{ __('Scraping') }}
                                </a>

                                {{-- Logout Link in Admin Dropdown --}}
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit"
                                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out text-left">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                            </path>
                                        </svg>
                                        {{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Theme Toggle -->
            <div class="hidden sm:flex sm:items-center sm:ms-4" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="document.documentElement.classList.toggle('dark', darkMode)">
                <button type="button"
                    @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode); document.documentElement.classList.toggle('dark', darkMode)"
                    class="inline-flex items-center p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200"
                    title="Toggle theme" id="theme-toggle">
                    <i class="fas w-5 h-5" :class="darkMode ? 'fa-sun' : 'fa-moon'" id="theme-icon"></i>
                </button>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-2">
                <div class="relative" @click.outside="profileDropdownOpen = false">
                    <button @click="toggleProfileDropdown()"
                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                        :aria-expanded="profileDropdownOpen" aria-haspopup="true">
                        @php
                            $profileDisplay = Auth::user()->getProfileDisplay();
                        @endphp
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-2 overflow-hidden">
                                @if ($profileDisplay['has_picture'])
                                    <img class="w-8 h-8 rounded-full object-cover"
                                        src="{{ $profileDisplay['picture_url'] }}"
                                        alt="{{ $profileDisplay['display_name'] }}">
                                @else
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center">
                                        <span class="text-xs font-medium text-gray-700">
                                            {{ $profileDisplay['initials'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div>{{ $profileDisplay['display_name'] }}</div>
                        </div>

                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <div x-show="profileDropdownOpen" x-cloak x-transition:enter="transform ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transform ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute z-50 mt-2 w-80 bg-white rounded-md shadow-lg border border-gray-200 right-0"
                        @click="profileDropdownOpen = false">

                        {{-- Enhanced Profile Quick Access --}}
                        <x-profile-quick-access :user="Auth::user()" position="right" />

                        {{-- Separator --}}
                        <div class="border-t border-gray-200"></div>

                        {{-- Logout Link --}}
                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit"
                                class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out text-left">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                    </path>
                                </svg>
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Enhanced Mobile Navigation with Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="toggleMobileMenu()" class="hd-mobile-hamburger"
                    :class="{ 'hd-mobile-hamburger--open': mobileMenuOpen }" :aria-expanded="mobileMenuOpen"
                    aria-label="Toggle mobile menu" aria-controls="mobile-menu" type="button">
                    <div class="hd-mobile-hamburger__icon">
                        <span class="hd-mobile-hamburger__line"></span>
                        <span class="hd-mobile-hamburger__line"></span>
                        <span class="hd-mobile-hamburger__line"></span>
                    </div>
                    <span class="sr-only">Toggle navigation menu</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': mobileMenuOpen, 'hidden': !mobileMenuOpen }"
        class="hidden sm:hidden bg-white border-t border-gray-200" id="mobile-menu" :aria-hidden="!mobileMenuOpen">
        <div class="container mx-auto px-4">
            <div class="pt-2 pb-3 space-y-1">
                {{-- Dashboard Link --}}
                <x-responsive-nav-link :href="route('dashboard')" :active="Request::routeIs('dashboard')">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 5a2 2 0 012-2h4a2 2 0 012 2v0M8 5a2 2 0 012-2h4a2 2 0 012 2v0"></path>
                    </svg>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                @if (Auth::user()->isAdmin() || Auth::user()->isAgent())
                    {{-- Sports Tickets --}}
                    <x-responsive-nav-link :href="route('tickets.scraping.index')" :active="Request::routeIs('tickets.scraping.*')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                            </path>
                        </svg>
                        {{ __('Sports Tickets') }}
                    </x-responsive-nav-link>

                    {{-- Ticket Alerts --}}
                    <x-responsive-nav-link :href="route('tickets.alerts.index')" :active="Request::routeIs('tickets.alerts.*')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                        {{ __('My Alerts') }}
                    </x-responsive-nav-link>

                    {{-- Purchase Queue --}}
                    <x-responsive-nav-link :href="route('purchase-decisions.index')" :active="Request::routeIs('purchase-decisions.*')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                        {{ __('Purchase Queue') }}
                    </x-responsive-nav-link>

                    {{-- Ticket Sources --}}
                    <x-responsive-nav-link :href="route('ticket-sources.index')" :active="Request::routeIs('ticket-sources.*')">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                            </path>
                        </svg>
                        {{ __('Sources') }}
                    </x-responsive-nav-link>
                @endif

                {{-- Profile Link --}}
                <x-responsive-nav-link :href="route('profile.show')" :active="Request::routeIs('profile.*')">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Auth::user()->isAdmin())
                    {{-- Admin Section --}}
                    <div class="border-t border-gray-200 mt-3 pt-3">
                        <div class="px-4 py-2">
                            <div class="font-medium text-sm text-gray-800 uppercase tracking-wide">
                                {{ __('Administration') }}</div>
                        </div>

                        {{-- Admin Dashboard --}}
                        <x-responsive-nav-link :href="route('admin.dashboard')" :active="Request::routeIs('admin.dashboard')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            {{ __('Admin Dashboard') }}
                        </x-responsive-nav-link>

                        {{-- Reports --}}
                        <x-responsive-nav-link :href="route('admin.reports.index')" :active="Request::routeIs('admin.reports.*')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            {{ __('Reports') }}
                        </x-responsive-nav-link>

                        @if (Auth::user()->canManageUsers())
                            <x-responsive-nav-link :href="route('admin.users.index')" :active="Request::routeIs('admin.users.*')">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                                {{ __('User Management') }}
                            </x-responsive-nav-link>
                        @endif

                        <x-responsive-nav-link :href="route('admin.categories.index')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            {{ __('Categories') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.system.index')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z">
                                </path>
                            </svg>
                            {{ __('System') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('admin.scraping.index')">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            {{ __('Scraping') }}
                        </x-responsive-nav-link>
                    </div>
                @endif
            </div>

            <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4 flex items-center justify-between">
                    <div class="flex items-center">
                        @php
                            $mobileProfileDisplay = Auth::user()->getProfileDisplay();
                        @endphp
                        <div
                            class="relative w-10 h-10 rounded-full flex items-center justify-center mr-3 overflow-hidden">
                            @if ($mobileProfileDisplay['has_picture'])
                                <img class="w-10 h-10 rounded-full object-cover"
                                    src="{{ $mobileProfileDisplay['picture_url'] }}"
                                    alt="{{ $mobileProfileDisplay['display_name'] }}">
                            @else
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ $mobileProfileDisplay['initials'] }}
                                    </span>
                                </div>
                            @endif

                            {{-- Profile completion indicator for mobile --}}
                            @php
                                $mobileProfileCompletion = Auth::user()->getProfileCompletion();
                            @endphp
                            @if ($mobileProfileCompletion['percentage'] < 90)
                                <div class="absolute -bottom-0.5 -right-0.5">
                                    <x-profile-completion-indicator :user="Auth::user()" position="sidebar"
                                        :showLabel="false" size="xs" />
                                </div>
                            @endif
                        </div>
                        <div>
                            <div class="font-medium text-base text-gray-800">
                                {{ $mobileProfileDisplay['display_name'] }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                    </div>

                    {{-- Profile completion status for mobile --}}
                    @if ($mobileProfileCompletion['percentage'] < 90)
                        <div class="text-right">
                            <div class="text-xs font-medium text-yellow-600">
                                {{ $mobileProfileCompletion['percentage'] }}%</div>
                            <div class="text-xs text-gray-500">Complete</div>
                        </div>
                    @endif
                </div>

                <div class="mt-3 space-y-1">
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
