@extends('layouts.app-v2')

@section('title', 'Ticket Monitoring & Alerts')
@section('description', 'Monitor sports event ticket prices and set up intelligent alerts for your favorite events')

@push('styles')
<style>
    /* Monitoring Dashboard Styles */
    .monitoring-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .monitoring-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        opacity: 0.1;
    }

    .monitoring-card {
        @apply bg-white rounded-2xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300;
    }

    .price-chart {
        height: 200px;
        @apply bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg p-4;
    }

    .alert-item {
        @apply p-4 bg-white rounded-xl border border-gray-200 hover:shadow-md transition-shadow;
    }

    .alert-active {
        @apply border-l-4 border-green-500 bg-green-50;
    }

    .alert-triggered {
        @apply border-l-4 border-red-500 bg-red-50;
    }

    .alert-paused {
        @apply border-l-4 border-yellow-500 bg-yellow-50;
    }

    .price-trend {
        @apply flex items-center space-x-1 text-sm font-medium;
    }

    .price-up {
        @apply text-red-600;
    }

    .price-down {
        @apply text-green-600;
    }

    .price-stable {
        @apply text-gray-600;
    }

    .availability-indicator {
        @apply w-3 h-3 rounded-full;
    }

    .availability-high {
        @apply bg-green-400;
    }

    .availability-medium {
        @apply bg-yellow-400;
    }

    .availability-low {
        @apply bg-red-400;
    }

    .availability-sold-out {
        @apply bg-gray-400;
    }

    .filter-button {
        @apply px-4 py-2 text-sm font-medium rounded-lg transition-colors;
    }

    .filter-active {
        @apply bg-blue-600 text-white;
    }

    .filter-inactive {
        @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
    }

    .notification-badge {
        @apply absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="py-6">
    <!-- Hero Section -->
    <div class="monitoring-hero text-white py-8 px-6 rounded-2xl mb-8 relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">Ticket Monitoring & Alerts</h1>
                <p class="text-white/90 text-lg mb-4">Stay ahead of the game with intelligent price tracking and real-time notifications</p>
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 19.462A17.936 17.936 0 003 12c0-9.941 8.059-18 18-18s18 8.059 18 18-8.059 18-18 18c-2.508 0-4.885-.511-7.077-1.438L9 21l4.462-4.538z"></path>
                        </svg>
                        <span id="activeAlertsCount">12</span> Active Alerts
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                        </svg>
                        <span id="monitoredEventsCount">28</span> Monitored Events
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Last Update: <span id="lastUpdateTime">{{ now()->format('H:i') }}</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 mt-6 lg:mt-0">
                <button onclick="openCreateAlertModal()" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-6 py-3 text-white rounded-xl font-medium transition-colors shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create Alert
                </button>
                <button onclick="refreshMonitoring()" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-6 py-3 text-white rounded-xl font-medium transition-colors shadow-lg">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="refreshIcon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid mb-8">
        <div class="monitoring-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 19.462A17.936 17.936 0 003 12c0-9.941 8.059-18 18-18s18 8.059 18 18-8.059 18-18 18c-2.508 0-4.885-.511-7.077-1.438L9 21l4.462-4.538z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">12</h3>
                    <p class="text-sm text-gray-600">Active Alerts</p>
                    <div class="flex items-center mt-2">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-xs text-green-600">3 triggered today</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="monitoring-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">$847</h3>
                    <p class="text-sm text-gray-600">Total Savings</p>
                    <div class="price-trend price-down mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-4-4"></path>
                        </svg>
                        <span>Average 23% below list price</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="monitoring-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">28</h3>
                    <p class="text-sm text-gray-600">Events Monitored</p>
                    <div class="flex items-center mt-2">
                        <span class="w-2 h-2 bg-purple-400 rounded-full mr-2"></span>
                        <span class="text-xs text-purple-600">5 ending soon</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="monitoring-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-lg">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">97%</h3>
                    <p class="text-sm text-gray-600">Success Rate</p>
                    <div class="flex items-center mt-2">
                        <span class="w-2 h-2 bg-green-400 rounded-full mr-2"></span>
                        <span class="text-xs text-green-600">Real-time monitoring</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Tabs -->
    <div class="monitoring-card p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search -->
            <div class="relative max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" id="searchMonitoring" placeholder="Search events, teams, or venues..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-2">
                <button class="filter-button filter-active" data-filter="all">All Alerts</button>
                <button class="filter-button filter-inactive" data-filter="active">Active</button>
                <button class="filter-button filter-inactive" data-filter="triggered">Triggered</button>
                <button class="filter-button filter-inactive" data-filter="paused">Paused</button>
                <button class="filter-button filter-inactive" data-filter="price-drop">Price Drops</button>
                <button class="filter-button filter-inactive" data-filter="availability">Availability</button>
            </div>

            <!-- Sort Options -->
            <select id="sortMonitoring" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                <option value="created_desc">Newest First</option>
                <option value="created_asc">Oldest First</option>
                <option value="event_date">Event Date</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="triggered">Recently Triggered</option>
            </select>
        </div>
    </div>

    <!-- Monitoring Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8" id="monitoringGrid">
        @php
            // Sample monitoring data - replace with actual data
            $monitoringAlerts = [
                [
                    'id' => 1,
                    'event' => 'NBA Finals Game 7 - Lakers vs Celtics',
                    'venue' => 'Crypto.com Arena',
                    'date' => '2024-06-20',
                    'current_price' => 285.00,
                    'target_price' => 250.00,
                    'original_price' => 350.00,
                    'status' => 'active',
                    'type' => 'price_drop',
                    'availability' => 'medium',
                    'last_check' => now()->subMinutes(5),
                    'trend' => 'down',
                    'savings' => 65.00,
                    'section' => 'Lower Level 118',
                ],
                [
                    'id' => 2,
                    'event' => 'Super Bowl LVIII',
                    'venue' => 'Allegiant Stadium',
                    'date' => '2024-02-11',
                    'current_price' => 1250.00,
                    'target_price' => 1200.00,
                    'original_price' => 1350.00,
                    'status' => 'triggered',
                    'type' => 'price_drop',
                    'availability' => 'low',
                    'last_check' => now()->subMinutes(2),
                    'trend' => 'down',
                    'savings' => 100.00,
                    'section' => 'Club Level 230',
                ],
                [
                    'id' => 3,
                    'event' => 'World Series Game 1 - Dodgers vs Yankees',
                    'venue' => 'Dodger Stadium',
                    'date' => '2024-10-22',
                    'current_price' => 189.00,
                    'target_price' => 150.00,
                    'original_price' => 220.00,
                    'status' => 'active',
                    'type' => 'availability',
                    'availability' => 'high',
                    'last_check' => now()->subMinutes(1),
                    'trend' => 'stable',
                    'savings' => 31.00,
                    'section' => 'Field Level 38',
                ],
                [
                    'id' => 4,
                    'event' => 'NBA Playoffs - Warriors vs Nuggets',
                    'venue' => 'Chase Center',
                    'date' => '2024-05-15',
                    'current_price' => 425.00,
                    'target_price' => 400.00,
                    'original_price' => 475.00,
                    'status' => 'paused',
                    'type' => 'price_drop',
                    'availability' => 'sold_out',
                    'last_check' => now()->subHours(2),
                    'trend' => 'up',
                    'savings' => 50.00,
                    'section' => 'Upper Level 215',
                ],
                [
                    'id' => 5,
                    'event' => 'Stanley Cup Final Game 4',
                    'venue' => 'TD Garden',
                    'date' => '2024-06-10',
                    'current_price' => 320.00,
                    'target_price' => 300.00,
                    'original_price' => 380.00,
                    'status' => 'triggered',
                    'type' => 'availability',
                    'availability' => 'medium',
                    'last_check' => now()->subMinutes(8),
                    'trend' => 'down',
                    'savings' => 60.00,
                    'section' => 'Balcony 301',
                ],
                [
                    'id' => 6,
                    'event' => 'UFC 300 - Main Event',
                    'venue' => 'T-Mobile Arena',
                    'date' => '2024-04-13',
                    'current_price' => 550.00,
                    'target_price' => 500.00,
                    'original_price' => 650.00,
                    'status' => 'active',
                    'type' => 'price_drop',
                    'availability' => 'low',
                    'last_check' => now()->subMinutes(3),
                    'trend' => 'down',
                    'savings' => 100.00,
                    'section' => 'Floor 104',
                ]
            ];
        @endphp

        @foreach($monitoringAlerts as $alert)
        <div class="alert-item alert-{{ $alert['status'] }}" data-alert-id="{{ $alert['id'] }}">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1 mr-3">
                    <h3 class="font-semibold text-gray-900 text-lg leading-tight mb-1">{{ $alert['event'] }}</h3>
                    <div class="flex items-center text-sm text-gray-600 space-x-3">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $alert['venue'] }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012 0v4h4V3a1 1 0 012 0v4h2a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h2z"></path>
                            </svg>
                            {{ \Carbon\Carbon::parse($alert['date'])->format('M j, Y') }}
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">{{ $alert['section'] }}</p>
                </div>

                <!-- Status & Actions -->
                <div class="flex items-center space-x-2">
                    <div class="availability-indicator availability-{{ $alert['availability'] }}" title="Availability: {{ ucfirst($alert['availability']) }}"></div>
                    <div class="relative">
                        <button onclick="toggleAlertMenu({{ $alert['id'] }})" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div id="alertMenu{{ $alert['id'] }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                            <div class="py-1">
                                <button onclick="editAlert({{ $alert['id'] }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Alert</button>
                                <button onclick="pauseAlert({{ $alert['id'] }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    {{ $alert['status'] === 'paused' ? 'Resume' : 'Pause' }} Alert
                                </button>
                                <button onclick="viewHistory({{ $alert['id'] }})" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View History</button>
                                <button onclick="deleteAlert({{ $alert['id'] }})" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Delete Alert</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Information -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Current</p>
                        <p class="text-lg font-bold text-gray-900">${{ number_format($alert['current_price'], 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Target</p>
                        <p class="text-lg font-bold text-blue-600">${{ number_format($alert['target_price'], 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Savings</p>
                        <p class="text-lg font-bold text-green-600">${{ number_format($alert['savings'], 0) }}</p>
                    </div>
                </div>
            </div>

            <!-- Price Chart Placeholder -->
            <div class="price-chart mb-4">
                <div class="flex items-center justify-center h-full text-gray-600">
                    <div class="text-center">
                        <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                        </svg>
                        <p class="text-sm">Price Trend Chart</p>
                        <p class="text-xs text-gray-500">7-day history</p>
                    </div>
                </div>
            </div>

            <!-- Status & Details -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $alert['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                               ($alert['status'] === 'triggered' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($alert['status']) }}
                        </span>
                    </div>
                    <div class="price-trend price-{{ $alert['trend'] }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($alert['trend'] === 'up')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-4 4"></path>
                            @elseif($alert['trend'] === 'down')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-4-4"></path>
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            @endif
                        </svg>
                        <span class="capitalize">{{ $alert['trend'] }}</span>
                    </div>
                </div>
                
                <div class="text-right">
                    <p class="text-xs text-gray-500">Last checked</p>
                    <p class="text-sm font-medium">{{ $alert['last_check']->diffForHumans() }}</p>
                </div>
            </div>

            @if($alert['status'] === 'triggered')
                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-red-800">Alert Triggered!</p>
                            <p class="text-xs text-red-600">Price dropped to your target. Act fast!</p>
                        </div>
                    </div>
                    <div class="mt-2 flex space-x-2">
                        <button onclick="purchaseTicket({{ $alert['id'] }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium">
                            Buy Now
                        </button>
                        <button onclick="dismissAlert({{ $alert['id'] }})" class="bg-white border border-red-300 text-red-700 px-3 py-1 rounded text-sm font-medium hover:bg-red-50">
                            Dismiss
                        </button>
                    </div>
                </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Load More Button -->
    <div class="text-center mb-8">
        <button onclick="loadMoreAlerts()" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors">
            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
            Load More Alerts
        </button>
    </div>
</div>

<!-- Create Alert Modal -->
<div id="createAlertModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Create Price Alert</h3>
                    <button onclick="closeCreateAlertModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <form id="createAlertForm" class="p-6 space-y-6">
                <!-- Event Selection -->
                <div>
                    <label for="alert_event" class="block text-sm font-medium text-gray-700 mb-2">Select Event</label>
                    <select id="alert_event" name="event" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Choose an event...</option>
                        <option value="nba_finals_g7">NBA Finals Game 7 - Lakers vs Celtics</option>
                        <option value="super_bowl">Super Bowl LVIII</option>
                        <option value="world_series_g1">World Series Game 1 - Dodgers vs Yankees</option>
                        <option value="nba_playoffs">NBA Playoffs - Warriors vs Nuggets</option>
                        <option value="stanley_cup_g4">Stanley Cup Final Game 4</option>
                        <option value="ufc_300">UFC 300 - Main Event</option>
                    </select>
                </div>

                <!-- Alert Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="alert_type" value="price_drop" class="mr-3" checked>
                            <div>
                                <p class="font-medium text-gray-900">Price Drop</p>
                                <p class="text-sm text-gray-600">Alert when price drops below target</p>
                            </div>
                        </label>
                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="alert_type" value="availability" class="mr-3">
                            <div>
                                <p class="font-medium text-gray-900">Availability</p>
                                <p class="text-sm text-gray-600">Alert when tickets become available</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Price Target -->
                <div id="priceTarget">
                    <label for="target_price" class="block text-sm font-medium text-gray-700 mb-2">Target Price ($)</label>
                    <input type="number" id="target_price" name="target_price" min="1" step="0.01" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="Enter your maximum price">
                    <p class="text-sm text-gray-500 mt-1">You'll be notified when the price drops to or below this amount</p>
                </div>

                <!-- Section Preferences -->
                <div>
                    <label for="section_preferences" class="block text-sm font-medium text-gray-700 mb-2">Section Preferences (Optional)</label>
                    <input type="text" id="section_preferences" name="section_preferences" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., Lower Level, Club Seats, etc.">
                </div>

                <!-- Notification Methods -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Notification Methods</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="notifications[]" value="email" checked class="mr-2">
                            <span class="text-sm text-gray-700">Email notifications</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="notifications[]" value="sms" class="mr-2">
                            <span class="text-sm text-gray-700">SMS notifications (requires verified phone)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="notifications[]" value="browser" checked class="mr-2">
                            <span class="text-sm text-gray-700">Browser push notifications</span>
                        </label>
                    </div>
                </div>

                <!-- Alert Duration -->
                <div>
                    <label for="alert_duration" class="block text-sm font-medium text-gray-700 mb-2">Alert Duration</label>
                    <select id="alert_duration" name="duration" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="until_event">Until event date</option>
                        <option value="1_day">1 day</option>
                        <option value="3_days">3 days</option>
                        <option value="1_week" selected>1 week</option>
                        <option value="1_month">1 month</option>
                        <option value="custom">Custom duration</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <button type="button" onclick="closeCreateAlertModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        Create Alert
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Monitoring Dashboard JavaScript
let monitoringData = [];
let currentFilter = 'all';
let currentSort = 'created_desc';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    loadMonitoringData();
    startRealTimeUpdates();
});

function setupEventListeners() {
    // Search functionality
    document.getElementById('searchMonitoring').addEventListener('input', function() {
        filterMonitoringAlerts();
    });

    // Filter buttons
    document.querySelectorAll('.filter-button').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.filter-button').forEach(b => {
                b.classList.remove('filter-active');
                b.classList.add('filter-inactive');
            });
            this.classList.remove('filter-inactive');
            this.classList.add('filter-active');
            currentFilter = this.dataset.filter;
            filterMonitoringAlerts();
        });
    });

    // Sort dropdown
    document.getElementById('sortMonitoring').addEventListener('change', function() {
        currentSort = this.value;
        sortMonitoringAlerts();
    });

    // Create alert form
    document.getElementById('createAlertForm').addEventListener('submit', function(e) {
        e.preventDefault();
        handleCreateAlert();
    });

    // Alert type radio buttons
    document.querySelectorAll('input[name="alert_type"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const priceTarget = document.getElementById('priceTarget');
            if (this.value === 'availability') {
                priceTarget.style.display = 'none';
                document.getElementById('target_price').required = false;
            } else {
                priceTarget.style.display = 'block';
                document.getElementById('target_price').required = true;
            }
        });
    });
}

function loadMonitoringData() {
    // This would typically load from an API
    // For now, we'll use the PHP-generated data
    console.log('Loading monitoring data...');
}

function filterMonitoringAlerts() {
    const searchTerm = document.getElementById('searchMonitoring').value.toLowerCase();
    const alertItems = document.querySelectorAll('.alert-item');

    alertItems.forEach(item => {
        const title = item.querySelector('h3').textContent.toLowerCase();
        const venue = item.querySelector('.text-gray-600').textContent.toLowerCase();
        
        let showItem = true;

        // Search filter
        if (searchTerm && !title.includes(searchTerm) && !venue.includes(searchTerm)) {
            showItem = false;
        }

        // Status filter
        if (currentFilter !== 'all') {
            const hasClass = item.classList.contains(`alert-${currentFilter}`) || 
                           (currentFilter === 'price-drop' && item.textContent.includes('Price Drop')) ||
                           (currentFilter === 'availability' && item.textContent.includes('Availability'));
            if (!hasClass) {
                showItem = false;
            }
        }

        item.style.display = showItem ? 'block' : 'none';
    });
}

function sortMonitoringAlerts() {
    // Implementation for sorting alerts
    console.log('Sorting alerts by:', currentSort);
}

function refreshMonitoring() {
    const refreshIcon = document.getElementById('refreshIcon');
    refreshIcon.classList.add('animate-spin');
    
    // Simulate refresh
    setTimeout(() => {
        refreshIcon.classList.remove('animate-spin');
        document.getElementById('lastUpdateTime').textContent = new Date().toLocaleTimeString('en-US', {hour12: false});
        showMessage('Monitoring data refreshed successfully!');
    }, 1000);
}

function startRealTimeUpdates() {
    // Update time every minute
    setInterval(() => {
        document.getElementById('lastUpdateTime').textContent = new Date().toLocaleTimeString('en-US', {hour12: false});
    }, 60000);

    // Simulate real-time price updates every 30 seconds
    setInterval(() => {
        simulatePriceUpdates();
    }, 30000);
}

function simulatePriceUpdates() {
    // Randomly update some prices to simulate real-time changes
    const alertItems = document.querySelectorAll('.alert-item');
    const randomIndex = Math.floor(Math.random() * alertItems.length);
    const alertItem = alertItems[randomIndex];
    
    if (alertItem) {
        const priceElement = alertItem.querySelector('.text-lg.font-bold.text-gray-900');
        if (priceElement) {
            const currentPrice = parseInt(priceElement.textContent.replace('$', '').replace(',', ''));
            const change = Math.floor(Math.random() * 20) - 10; // Random change between -10 and +10
            const newPrice = Math.max(50, currentPrice + change); // Minimum price of $50
            priceElement.textContent = `$${newPrice.toLocaleString()}`;
        }
    }
}

// Alert management functions
function toggleAlertMenu(alertId) {
    const menu = document.getElementById(`alertMenu${alertId}`);
    // Close all other menus first
    document.querySelectorAll('[id^="alertMenu"]').forEach(m => {
        if (m.id !== `alertMenu${alertId}`) {
            m.classList.add('hidden');
        }
    });
    menu.classList.toggle('hidden');
}

function editAlert(alertId) {
    console.log('Editing alert:', alertId);
    // Implementation for editing alert
    closeAllMenus();
}

function pauseAlert(alertId) {
    if (confirm('Are you sure you want to pause this alert?')) {
        console.log('Pausing alert:', alertId);
        // Implementation for pausing alert
        showMessage('Alert paused successfully!');
    }
    closeAllMenus();
}

function viewHistory(alertId) {
    console.log('Viewing history for alert:', alertId);
    // Implementation for viewing alert history
    closeAllMenus();
}

function deleteAlert(alertId) {
    if (confirm('Are you sure you want to delete this alert? This action cannot be undone.')) {
        console.log('Deleting alert:', alertId);
        const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
        if (alertElement) {
            alertElement.remove();
            showMessage('Alert deleted successfully!');
        }
    }
    closeAllMenus();
}

function closeAllMenus() {
    document.querySelectorAll('[id^="alertMenu"]').forEach(menu => {
        menu.classList.add('hidden');
    });
}

function purchaseTicket(alertId) {
    window.location.href = `/tickets/purchase?alert=${alertId}`;
}

function dismissAlert(alertId) {
    const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
    const triggeredBanner = alertElement.querySelector('.bg-red-50');
    if (triggeredBanner) {
        triggeredBanner.remove();
        alertElement.classList.remove('alert-triggered');
        alertElement.classList.add('alert-active');
        showMessage('Alert dismissed');
    }
}

function loadMoreAlerts() {
    // Implementation for loading more alerts
    console.log('Loading more alerts...');
    showMessage('Loading more alerts...');
}

// Modal functions
function openCreateAlertModal() {
    document.getElementById('createAlertModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeCreateAlertModal() {
    document.getElementById('createAlertModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    document.getElementById('createAlertForm').reset();
}

function handleCreateAlert() {
    const formData = new FormData(document.getElementById('createAlertForm'));
    
    // Validate form
    const event = formData.get('event');
    const alertType = formData.get('alert_type');
    const targetPrice = formData.get('target_price');
    
    if (!event) {
        showMessage('Please select an event', 'error');
        return;
    }

    if (alertType === 'price_drop' && !targetPrice) {
        showMessage('Please enter a target price', 'error');
        return;
    }

    // Simulate API call
    console.log('Creating alert with data:', Object.fromEntries(formData));
    
    setTimeout(() => {
        showMessage('Alert created successfully!');
        closeCreateAlertModal();
        // Refresh the alerts list
        location.reload();
    }, 1000);
}

// Utility functions
function showMessage(message, type = 'success') {
    const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
    const alert = document.createElement('div');
    alert.className = `fixed top-4 right-4 p-4 rounded-lg border z-50 ${alertClass}`;
    alert.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">×</button>
        </div>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.relative')) {
        closeAllMenus();
    }
});

// Close modal on outside click
document.getElementById('createAlertModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateAlertModal();
    }
});
</script>
@endpush

@endsection
