<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ darkMode: $persist(false) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Theme Color -->
    <meta name="theme-color" content="#2563eb">
    <meta name="msapplication-navbutton-color" content="#2563eb">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    
    <!-- PWA Meta -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    
    <title>{{ isset($title) ? $title . ' - ' : '' }}{{ config('app.name', 'HD Tickets') }}</title>
    <meta name="description" content="@yield('meta_description', 'Professional sports event ticket monitoring and purchase platform')">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Preconnect to external resources -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    
    <!-- Critical Fonts -->
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet">
    
    <!-- Design System -->
    @vite(['resources/css/design-system-v2.css', 'resources/css/components-v2.css', 'resources/css/app.css', 'resources/js/app.js'])
    
    @stack('head')
</head>

<body class="h-full font-sans antialiased" 
      x-data="appShell()" 
      x-init="init()" 
      :class="{ 
        'overflow-hidden': sidebarOpen && $screen('md') < window.innerWidth,
        'dark': darkMode 
      }"
      style="font-family: var(--hdt-font-sans);">
    
    <!-- Skip Navigation -->
    <a href="#main-content" class="hdt-sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 hdt-button hdt-button--primary hdt-button--sm z-50">
        Skip to main content
    </a>

    <div class="flex h-full" 
         style="background-color: var(--hdt-surface-primary);">

        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
             style="display: none;"></div>

        <!-- Sidebar -->
        <div class="relative z-40 lg:z-auto"
             :class="{
               'fixed inset-y-0 left-0 lg:static lg:inset-auto': true,
               'translate-x-0': sidebarOpen,
               '-translate-x-full lg:translate-x-0': !sidebarOpen
             }"
             x-transition:enter="transition ease-in-out duration-300 transform"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in-out duration-300 transform"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full">
            
            <div class="flex flex-col h-full border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                 :class="{ 
                   'w-64': !sidebarCollapsed,
                   'w-16': sidebarCollapsed && $screen('lg') <= window.innerWidth,
                   'w-72': !sidebarCollapsed && $screen('lg') > window.innerWidth
                 }"
                 style="width: var(--hdt-sidebar-width); background-color: var(--hdt-surface-secondary);">
                
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700"
                     style="height: var(--hdt-header-height); border-color: var(--hdt-border-primary);">
                    <div class="flex items-center gap-3">
                        <!-- Logo -->
                        <div class="flex-shrink-0">
                            <img class="w-8 h-8" 
                                 src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                                 alt="HD Tickets Logo">
                        </div>
                        
                        <!-- Brand Text -->
                        <div x-show="!sidebarCollapsed" 
                             x-transition:enter="transition ease-in-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in-out duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">HD Tickets</h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Sports Monitoring</p>
                        </div>
                    </div>

                    <!-- Sidebar Toggle (Desktop) -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed"
                            class="hidden lg:block p-1.5 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
                            x-show="!sidebarCollapsed || sidebarCollapsed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  :d="sidebarCollapsed ? 'M13 7l5 5-5 5M6 12h12' : 'M11 17l-5-5 5-5m6 5H6'"/>
                        </svg>
                    </button>

                    <!-- Close Button (Mobile) -->
                    <button @click="sidebarOpen = false"
                            class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    @include('components.navigation.sidebar-nav-v2')
                </nav>

                <!-- Sidebar Footer -->
                <div class="flex-shrink-0 p-4 border-t border-gray-200 dark:border-gray-700"
                     style="border-color: var(--hdt-border-primary);">
                    
                    <!-- Theme Toggle -->
                    <div class="flex items-center justify-between mb-4" x-show="!sidebarCollapsed">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Dark Mode</span>
                        <button @click="darkMode = !darkMode" 
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                                :class="darkMode ? 'bg-blue-600' : 'bg-gray-200'">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"
                                  :class="darkMode ? 'translate-x-6' : 'translate-x-1'"></span>
                        </button>
                    </div>

                    <!-- User Profile -->
                    <div class="flex items-center" 
                         :class="sidebarCollapsed ? 'justify-center' : 'space-x-3'">
                        <img class="w-8 h-8 rounded-full" 
                             src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'User') . '&color=2563eb&background=dbeafe' }}" 
                             alt="{{ Auth::user()->name ?? 'User' }} Avatar">
                        
                        <div x-show="!sidebarCollapsed" class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ Auth::user()->name ?? 'User' }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate capitalize">
                                {{ Auth::user()->role ?? 'customer' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 h-16 flex-shrink-0"
                    style="height: var(--hdt-header-height); background-color: var(--hdt-surface-secondary); border-color: var(--hdt-border-primary);">
                <div class="flex items-center justify-between h-full px-6">
                    
                    <!-- Left Side - Mobile Menu & Breadcrumb -->
                    <div class="flex items-center gap-4">
                        <!-- Mobile Menu Button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <!-- Breadcrumb -->
                        @hasSection('breadcrumb')
                            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                                @yield('breadcrumb')
                            </nav>
                        @endif

                        <!-- Page Title -->
                        @hasSection('page-header')
                            <div class="lg:hidden">
                                @yield('page-header')
                            </div>
                        @endif
                    </div>

                    <!-- Right Side - Actions & Profile -->
                    <div class="flex items-center gap-4">
                        
                        <!-- Search -->
                        <div class="hidden md:block">
                            <div class="relative">
                                <input type="search" 
                                       placeholder="Search tickets..."
                                       class="w-64 hdt-input hdt-input--sm pl-10"
                                       x-data="{ focused: false }"
                                       @focus="focused = true"
                                       @blur="focused = false">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700 rounded-full relative transition-colors duration-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                                </svg>
                                <!-- Notification Badge -->
                                <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 rounded-full flex items-center justify-center">
                                    <span class="text-xs font-medium text-white">3</span>
                                </span>
                            </button>

                            <!-- Notifications Dropdown -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                 style="display: none;">
                                @include('components.navigation.notifications-dropdown-v2')
                            </div>
                        </div>

                        <!-- User Menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center gap-2 p-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200">
                                <img class="w-8 h-8 rounded-full" 
                                     src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name ?? 'User') . '&color=2563eb&background=dbeafe' }}" 
                                     alt="{{ Auth::user()->name ?? 'User' }} Avatar">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- User Dropdown -->
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
                                 style="display: none;">
                                @include('components.navigation.user-dropdown-v2')
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Header Section -->
            @hasSection('page-header')
                <div class="hidden lg:block bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700"
                     style="background-color: var(--hdt-surface-secondary); border-color: var(--hdt-border-primary);">
                    <div class="px-6 py-6">
                        @yield('page-header')
                    </div>
                </div>
            @endif

            <!-- Main Content -->
            <main id="main-content" 
                  role="main" 
                  class="flex-1 overflow-y-auto"
                  style="background-color: var(--hdt-surface-primary);">
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="m-6 p-4 rounded-md bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="m-6 p-4 rounded-md bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Page Content -->
                <div class="hdt-container py-6">
                    @yield('content')
                    {{ $slot ?? '' }}
                </div>
            </main>
        </div>
    </div>

    <!-- Alpine.js App Shell Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appShell', () => ({
                sidebarOpen: false,
                sidebarCollapsed: Alpine.$persist(false),
                
                init() {
                    // Handle responsive sidebar behavior
                    this.handleResize();
                    window.addEventListener('resize', () => {
                        this.handleResize();
                    });

                    // Close mobile sidebar on route changes
                    document.addEventListener('turbo:load', () => {
                        this.sidebarOpen = false;
                    });
                },

                handleResize() {
                    if (window.innerWidth >= 1024) { // lg breakpoint
                        this.sidebarOpen = false;
                    }
                },

                toggleSidebar() {
                    this.sidebarOpen = !this.sidebarOpen;
                },

                closeSidebar() {
                    this.sidebarOpen = false;
                }
            }));
        });
    </script>

    @stack('scripts')
</body>
</html>