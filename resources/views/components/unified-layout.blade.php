@props([
    'title' => 'HD Tickets',
    'subtitle' => '',
    'showSidebar' => true,
    'sidebarCollapsed' => false,
    'breadcrumbs' => [],
    'meta' => [],
    'headerActions' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="robots" content="noindex, nofollow">

    <!-- SEO Meta Tags -->
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @if(!empty($subtitle))
        <meta name="description" content="{{ $subtitle }}">
    @else
        <meta name="description" content="Professional sports ticket monitoring and alerting platform">
    @endif

    <!-- Favicon and PWA Icons -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/images/hdTicketsLogo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">

    <!-- Preload Critical Resources -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preload" href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" as="style">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Design System CSS -->
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}?v={{ time() }}">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Additional Styles Stack -->
    @stack('styles')

    <style>
        /* Unified Sports Theme Application Layout */
        .hd-unified-layout {
            display: flex;
            height: 100vh;
            background-color: #f8fafc;
            font-family: var(--hd-font-family, 'Inter', system-ui, sans-serif);
        }

        .hd-unified-sidebar {
            width: 280px;
            background-color: white;
            border-right: 1px solid #e5e7eb;
            transition: transform 0.3s ease;
            position: relative;
            z-index: 30;
            overflow-y: auto;
        }

        .hd-unified-sidebar--collapsed {
            transform: translateX(-100%);
        }

        .hd-unified-sidebar__header {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: white;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .hd-unified-sidebar__nav {
            padding: 1rem;
        }

        .hd-unified-nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #6b7280;
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            font-weight: 500;
            min-height: 44px;
            margin-bottom: 0.25rem;
        }

        .hd-unified-nav-item:hover {
            background-color: #f9fafb;
            color: #111827;
            transform: translateX(4px);
        }

        .hd-unified-nav-item--active {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
            border-left: 3px solid #2563eb;
            padding-left: calc(1rem - 3px);
        }

        .hd-unified-nav-item__icon {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        .hd-unified-main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: #f8fafc;
        }

        .hd-unified-header {
            background-color: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 64px;
            position: sticky;
            top: 0;
            z-index: 20;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .hd-unified-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
            background-color: #f8fafc;
        }

        .hd-mobile-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 20;
        }

        /* Sports Theme Enhancements */
        .hd-logo-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .hd-logo-container img {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .hd-app-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
        }

        .hd-app-subtitle {
            font-size: 0.75rem;
            color: #6b7280;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .hd-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .hd-breadcrumb-item {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .hd-breadcrumb-item:hover {
            color: #2563eb;
        }

        .hd-breadcrumb-separator {
            color: #d1d5db;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .hd-unified-layout {
                flex-direction: column;
            }
            
            .hd-unified-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 40;
                transform: translateX(-100%);
            }

            .hd-unified-sidebar--mobile-open {
                transform: translateX(0);
            }

            .hd-mobile-overlay--visible {
                display: block;
            }

            .hd-unified-main-content {
                width: 100%;
            }

            .hd-unified-header {
                padding: 1rem;
            }

            .hd-unified-content {
                padding: 1rem;
                padding-bottom: 5rem; /* Space for mobile navigation */
            }
        }

        /* Loading states */
        .hd-loading-skeleton {
            background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        .hd-loading-skeleton--spinner {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Utility classes */
        .hd-text-small { font-size: 0.875rem; }
        .hd-text-base { font-size: 1rem; }
        .hd-heading-2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
        .hd-grid { display: grid; gap: 1rem; }
        .hd-grid-1 { grid-template-columns: repeat(1, 1fr); }
        .hd-md-grid-2 { @media (min-width: 768px) { grid-template-columns: repeat(2, 1fr); } }
        .hd-lg-grid-4 { @media (min-width: 1024px) { grid-template-columns: repeat(4, 1fr); } }
    </style>
</head>

<body class="font-sans antialiased h-full bg-gray-50">
    <div class="hd-unified-layout" x-data="{ sidebarOpen: false, sidebarCollapsed: {{ $sidebarCollapsed ? 'true' : 'false' }} }">
        
        <!-- Mobile Overlay -->
        <div class="hd-mobile-overlay" 
             :class="{ 'hd-mobile-overlay--visible': sidebarOpen }"
             @click="sidebarOpen = false"
             x-show="sidebarOpen"
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
        </div>

        @if($showSidebar)
        <!-- Sidebar -->
        <aside class="hd-unified-sidebar"
               :class="{ 
                   'hd-unified-sidebar--collapsed': !sidebarOpen && window.innerWidth >= 768,
                   'hd-unified-sidebar--mobile-open': sidebarOpen && window.innerWidth < 768 
               }">
            
            <!-- Sidebar Header -->
            <div class="hd-unified-sidebar__header">
                <div class="hd-logo-container">
                    <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets Logo" class="w-10 h-10">
                    <div>
                        <h1 class="hd-app-title">HD Tickets</h1>
                        <p class="hd-app-subtitle">Sports Monitoring</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="hd-unified-sidebar__nav">
                <a href="{{ route('dashboard') }}" 
                   class="hd-unified-nav-item {{ request()->routeIs('dashboard') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('tickets.scraping.index') }}" 
                   class="hd-unified-nav-item {{ request()->routeIs('tickets.scraping.*') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Discover Tickets
                </a>

                <a href="{{ route('tickets.alerts.index') }}" 
                   class="hd-unified-nav-item {{ request()->routeIs('tickets.alerts.*') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                    </svg>
                    Price Alerts
                </a>

                <a href="{{ route('tickets.scraping.trending') }}" 
                   class="hd-unified-nav-item {{ request()->routeIs('tickets.scraping.trending') ? 'hd-unified-nav-item--active' : '' }}">
                    <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Trending
                </a>

                @if(Auth::user()?->isAdmin() || Auth::user()?->isAgent())
                <div class="border-t border-gray-200 my-4 pt-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3 px-4">Administration</p>
                    
                    <a href="{{ route('purchase-decisions.index') }}" 
                       class="hd-unified-nav-item {{ request()->routeIs('purchase-decisions.*') ? 'hd-unified-nav-item--active' : '' }}">
                        <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Purchase Queue
                    </a>

                    <a href="{{ route('ticket-sources.index') }}" 
                       class="hd-unified-nav-item {{ request()->routeIs('ticket-sources.*') ? 'hd-unified-nav-item--active' : '' }}">
                        <svg class="hd-unified-nav-item__icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Ticket Sources
                    </a>
                </div>
                @endif
            </nav>
        </aside>
        @endif

        <!-- Main Content Area -->
        <main class="hd-unified-main-content">
            <!-- Header -->
            <header class="hd-unified-header">
                <div class="flex items-center space-x-4">
                    @if($showSidebar)
                    <!-- Mobile Menu Toggle -->
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="md:hidden p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <!-- Desktop Sidebar Toggle -->
                    <button @click="sidebarCollapsed = !sidebarCollapsed" 
                            class="hidden md:block p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    @endif

                    <!-- Page Title -->
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                        @if($subtitle)
                            <p class="text-sm text-gray-600">{{ $subtitle }}</p>
                        @endif
                    </div>
                </div>

                <!-- Header Actions -->
                <div class="flex items-center space-x-3">
                    {{ $headerActions ?? '' }}
                    
                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" 
                                class="flex items-center space-x-2 p-2 rounded-md text-gray-500 hover:text-gray-900 hover:bg-gray-100">
                            <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-white">
                                    {{ substr(Auth::user()?->name ?? 'U', 0, 2) }}
                                </span>
                            </div>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="transform opacity-100 scale-100"
                             x-transition:leave-end="transform opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border">
                            
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()?->name }}</p>
                                <p class="text-sm text-gray-500">{{ Auth::user()?->email }}</p>
                            </div>
                            
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Sign Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Breadcrumbs -->
            @if(!empty($breadcrumbs))
            <div class="px-6 py-3 bg-white border-b border-gray-200">
                <nav class="hd-breadcrumb">
                    @foreach($breadcrumbs as $index => $breadcrumb)
                        @if($index > 0)
                            <span class="hd-breadcrumb-separator">/</span>
                        @endif
                        @if(isset($breadcrumb['url']) && !$loop->last)
                            <a href="{{ $breadcrumb['url'] }}" class="hd-breadcrumb-item">{{ $breadcrumb['name'] }}</a>
                        @else
                            <span class="hd-breadcrumb-item text-gray-900">{{ $breadcrumb['name'] }}</span>
                        @endif
                    @endforeach
                </nav>
            </div>
            @endif

            <!-- Main Content -->
            <div class="hd-unified-content">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Scripts Stack -->
    @stack('scripts')

    <!-- Real-time updates and WebSocket -->
    <script>
        // Dashboard manager for Alpine.js
        function dashboardManager() {
            return {
                loading: true,
                stats: {
                    active_monitors: 0,
                    alerts_today: 0,
                    price_drops: 0,
                    available_now: 0
                },
                
                init() {
                    this.loadData();
                    // Simulate loading
                    setTimeout(() => {
                        this.loading = false;
                    }, 1000);
                },
                
                loadData() {
                    // This would typically fetch from API
                    this.stats = {
                        active_monitors: Math.floor(Math.random() * 1000),
                        alerts_today: Math.floor(Math.random() * 50),
                        price_drops: Math.floor(Math.random() * 20),
                        available_now: Math.floor(Math.random() * 100)
                    };
                }
            };
        }
    </script>
</body>
</html>
