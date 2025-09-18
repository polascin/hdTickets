@extends('layouts.app')

@section('title', 'Customer Dashboard')

@section('head')
    <meta name="description" content="Sports event ticket monitoring dashboard - view tickets, alerts, and subscription status">
    <link rel="preload" href="{{ asset('css/customer-dashboard-enhanced-v2.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset('css/customer-dashboard-enhanced-v2.css') }}">
    @if(config('broadcasting.default') !== 'null')
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    @endif
@endsection

@section('content')
<div 
    id="customerDashboard"
    class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-purple-50/20 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
    x-data="customerDashboard()"
    x-init="init()"
>

    {{-- Main Dashboard Header --}}
    <header role="banner" class="sticky top-0 z-40 bg-white/80 backdrop-blur-sm border-b border-slate-200/60 dark:bg-slate-900/80 dark:border-slate-700/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Greeting & User Info --}}
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-semibold text-sm">
                                {{ substr(auth()->user()->name, 0, 1) }}{{ substr(explode(' ', auth()->user()->name)[1] ?? '', 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            Welcome back, {{ auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0] }}
                        </h1>
                        <p class="text-sm text-slate-600 dark:text-slate-400" x-text="currentTime"></p>
                    </div>
                </div>

                {{-- Quick Actions --}}
                <nav role="navigation" aria-label="Quick actions" class="hidden md:flex items-center space-x-2">
                    <button 
                        type="button"
                        @click="refreshData()"
                        :disabled="loading"
                        class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
                        :class="{ 'animate-spin': loading }"
                        aria-label="Refresh dashboard data"
                        title="Refresh data"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <a 
                        href="{{ route('subscription.plans') }}"
                        class="px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-md transition-colors duration-200 dark:text-blue-400 dark:hover:text-blue-300 dark:hover:bg-blue-900/20"
                        aria-label="Manage subscription"
                    >
                        Subscription
                    </a>
                    <button 
                        type="button"
                        @click="showNotifications = !showNotifications"
                        class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
                        aria-label="View notifications"
                        :aria-expanded="showNotifications"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span x-show="notifications.length > 0" class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
                    </button>
                </nav>

                {{-- Mobile menu button --}}
                <button 
                    type="button"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 text-slate-500 hover:text-slate-700 rounded-md"
                    aria-label="Toggle mobile menu"
                    :aria-expanded="mobileMenuOpen"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Mobile menu --}}
            <div x-show="mobileMenuOpen" x-transition class="md:hidden py-4 border-t border-slate-200 dark:border-slate-700">
                <nav class="space-y-2">
                    <button @click="refreshData()" class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-md dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800">
                        Refresh Dashboard
                    </button>
                    <a href="{{ route('subscription.plans') }}" class="block px-3 py-2 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-md dark:text-slate-400 dark:hover:text-slate-100 dark:hover:bg-slate-800">
                        Manage Subscription
                    </a>
                </nav>
            </div>
        </div>
    </header>

    {{-- Notification Toast --}}
    <div 
        x-show="showToast" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed top-20 right-4 z-50 max-w-sm w-full"
        role="alert"
        aria-live="polite"
    >
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg x-show="toastType === 'success'" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg x-show="toastType === 'error'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100" x-text="toastMessage"></p>
                </div>
                <button @click="hideToast()" class="ml-4 flex-shrink-0 text-slate-400 hover:text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <main id="main-content" role="main" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Dashboard Stats Grid --}}
        <section aria-labelledby="stats-heading" class="mb-8">
            <h2 id="stats-heading" class="sr-only">Dashboard Statistics</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Available Tickets --}}
                <div class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400">Available Tickets</h3>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="stats.available_tickets || '0'">
                                {{ $stats['available_tickets'] ?? '0' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                                <span x-text="stats.new_today || '0'">{{ $stats['new_today'] ?? '0' }}</span> new today
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Monitored Events --}}
                <div class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400">Monitored Events</h3>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="stats.monitored_events || '0'">
                                {{ $stats['monitored_events'] ?? '0' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                                <span x-text="stats.active_alerts || '0'">{{ $stats['active_alerts'] ?? '0' }}</span> active alerts
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Price Alerts --}}
                <div class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400">Price Alerts</h3>
                            <p class="text-2xl font-bold text-slate-900 dark:text-slate-100" x-text="stats.price_alerts || '0'">
                                {{ $stats['price_alerts'] ?? '0' }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">
                                <span x-text="stats.triggered_today || '0'">{{ $stats['triggered_today'] ?? '0' }}</span> triggered today
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Subscription Status --}}
                <div class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6 hover:bg-white/80 dark:hover:bg-slate-800/80 transition-all duration-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-sm font-medium text-slate-600 dark:text-slate-400">Plan Status</h3>
                            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                @if(auth()->user()->hasActiveSubscription())
                                    Active
                                @else
                                    @php
                                        $daysRemaining = auth()->user()->getFreeTrialDaysRemaining();
                                    @endphp
                                    @if($daysRemaining > 0)
                                        Trial ({{ $daysRemaining }}d)
                                    @else
                                        Expired
                                    @endif
                                @endif
                            </p>
                            <div class="mt-2">
                                @php
                                    $usage = auth()->user()->getMonthlyTicketUsage();
                                    $limit = auth()->user()->getMonthlyTicketLimit();
                                    $percentage = $limit > 0 ? ($usage / $limit) * 100 : 0;
                                @endphp
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                    <div 
                                        class="h-2 rounded-full transition-all duration-300 {{ $percentage >= 90 ? 'bg-red-500' : ($percentage >= 70 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                        style="width: {{ min($percentage, 100) }}%"
                                    ></div>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-500 mt-1">{{ $usage }}/{{ $limit }} tickets used</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Left Column: Recent Tickets & Filters --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- Filters Section --}}
                <section aria-labelledby="filters-heading" class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 id="filters-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Filter Tickets</h2>
                        <button 
                            @click="resetFilters()"
                            class="text-sm text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                        >
                            Reset
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Sport Filter --}}
                        <div>
                            <label for="sport-filter" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Sport</label>
                            <select 
                                id="sport-filter"
                                x-model="filters.sport"
                                @change="applyFilters()"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            >
                                <option value="">All Sports</option>
                                <option value="football">Football</option>
                                <option value="basketball">Basketball</option>
                                <option value="baseball">Baseball</option>
                                <option value="hockey">Hockey</option>
                                <option value="soccer">Soccer</option>
                            </select>
                        </div>

                        {{-- Platform Filter --}}
                        <div>
                            <label for="platform-filter" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Platform</label>
                            <select 
                                id="platform-filter"
                                x-model="filters.platform"
                                @change="applyFilters()"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            >
                                <option value="">All Platforms</option>
                                <option value="ticketmaster">Ticketmaster</option>
                                <option value="stubhub">StubHub</option>
                                <option value="vivid_seats">Vivid Seats</option>
                                <option value="seatgeek">SeatGeek</option>
                            </select>
                        </div>

                        {{-- Price Range --}}
                        <div>
                            <label for="max-price" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Max Price</label>
                            <input 
                                type="number" 
                                id="max-price"
                                x-model="filters.maxPrice"
                                @input.debounce.500ms="applyFilters()"
                                placeholder="No limit"
                                min="0"
                                step="10"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            >
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label for="sort-filter" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Sort By</label>
                            <select 
                                id="sort-filter"
                                x-model="filters.sort"
                                @change="applyFilters()"
                                class="w-full px-3 py-2 border border-slate-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            >
                                <option value="created_at:desc">Newest First</option>
                                <option value="price:asc">Price: Low to High</option>
                                <option value="price:desc">Price: High to Low</option>
                                <option value="event_date:asc">Event Date: Nearest</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Recent Tickets Section --}}
                <section aria-labelledby="tickets-heading" class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 id="tickets-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recent Tickets</h2>
                        <span class="text-sm text-slate-500 dark:text-slate-400" x-text="`${tickets.length} tickets`"></span>
                    </div>

                    {{-- Loading State --}}
                    <div x-show="loading" class="flex items-center justify-center py-12">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-3 text-slate-600 dark:text-slate-400">Loading tickets...</span>
                    </div>

                    {{-- Empty State --}}
                    <div x-show="!loading && tickets.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">No tickets found</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try adjusting your filters to see more results.</p>
                    </div>

                    {{-- Tickets Grid --}}
                    <div x-show="!loading && tickets.length > 0" class="grid gap-4">
                        <template x-for="ticket in tickets" :key="ticket.id">
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 hover:border-blue-300 dark:hover:border-blue-700 transition-colors duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate" x-text="ticket.event_title"></h3>
                                        <p class="text-sm text-slate-600 dark:text-slate-400" x-text="ticket.venue_name"></p>
                                        <div class="flex items-center mt-2 space-x-4 text-xs text-slate-500 dark:text-slate-400">
                                            <span x-text="formatDate(ticket.event_date)"></span>
                                            <span x-text="ticket.sport_category"></span>
                                            <span x-text="ticket.platform_source"></span>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end ml-4">
                                        <div class="text-right">
                                            <p class="text-lg font-bold text-slate-900 dark:text-slate-100" x-text="formatCurrency(ticket.price)"></p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400" x-text="`${ticket.available_quantity} available`"></p>
                                        </div>
                                        <div class="flex items-center mt-2 space-x-2">
                                            {{-- Demand Indicator --}}
                                            <span 
                                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': ticket.demand_level === 'high',
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': ticket.demand_level === 'medium',
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': ticket.demand_level === 'low'
                                                }"
                                                x-text="ticket.demand_level"
                                            ></span>
                                            {{-- Price Trend --}}
                                            <span 
                                                class="inline-flex items-center text-xs"
                                                :class="{
                                                    'text-red-600 dark:text-red-400': ticket.price_trend === 'up',
                                                    'text-green-600 dark:text-green-400': ticket.price_trend === 'down',
                                                    'text-slate-500 dark:text-slate-400': ticket.price_trend === 'stable'
                                                }"
                                            >
                                                <svg x-show="ticket.price_trend === 'up'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg x-show="ticket.price_trend === 'down'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                <svg x-show="ticket.price_trend === 'stable'" class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
                                                </svg>
                                                <span x-text="ticket.price_trend"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4">
                                    <div class="flex space-x-2">
                                        <button 
                                            @click="toggleAlert(ticket)"
                                            :class="ticket.has_alert ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-400'"
                                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-md hover:bg-opacity-75 transition-colors duration-200"
                                        >
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <span x-text="ticket.has_alert ? 'Alert On' : 'Set Alert'"></span>
                                        </button>
                                    </div>
                                    <a 
                                        :href="`/tickets/${ticket.id}/purchase`"
                                        class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                    >
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </div>

            {{-- Right Column: Recommendations & Quick Actions --}}
            <div class="space-y-8">
                {{-- Recommendations Section --}}
                <section aria-labelledby="recommendations-heading" class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6">
                    <h2 id="recommendations-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Recommended for You</h2>
                    
                    <div x-show="recommendationsLoading" class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                        <span class="ml-2 text-sm text-slate-600 dark:text-slate-400">Loading...</span>
                    </div>
                    
                    <div x-show="!recommendationsLoading" class="space-y-4">
                        <template x-for="rec in recommendations" :key="rec.id">
                            <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 hover:border-blue-300 dark:hover:border-blue-700 transition-colors duration-200">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate" x-text="rec.event_title"></h3>
                                        <p class="text-xs text-slate-600 dark:text-slate-400" x-text="formatDate(rec.event_date)"></p>
                                        <div class="flex items-center mt-2">
                                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100" x-text="formatCurrency(rec.price)"></span>
                                            <span 
                                                class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                                                x-text="`${rec.match_score}% match`"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <a 
                                        :href="`/tickets/${rec.id}/purchase`"
                                        class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200"
                                    >
                                        View Ticket
                                    </a>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="recommendations.length === 0" class="text-center py-6">
                            <p class="text-sm text-slate-500 dark:text-slate-400">No recommendations available yet.</p>
                        </div>
                    </div>
                </section>

                {{-- Quick Actions Section --}}
                <section aria-labelledby="actions-heading" class="bg-white/60 backdrop-blur-sm dark:bg-slate-800/60 rounded-xl border border-slate-200/60 dark:border-slate-700/60 p-6">
                    <h2 id="actions-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">Quick Actions</h2>
                    <div class="space-y-3">
                        <a href="/tickets" class="flex items-center p-3 text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            Browse All Tickets
                        </a>
                        <a href="/alerts" class="flex items-center p-3 text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            Manage Alerts
                        </a>
                        <a href="{{ route('subscription.plans') }}" class="flex items-center p-3 text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                            View Subscription
                        </a>
                        <a href="/purchase-history" class="flex items-center p-3 text-sm text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200">
                            <svg class="w-5 h-5 mr-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Purchase History
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/customer-dashboard-enhanced-v2.js') }}"></script>
@endsection
