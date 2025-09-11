@extends('layouts.modern')
@section('title', 'Customer Dashboard - HD Tickets Sports Events')

@push('styles')
    <link href="{{ asset('css/dashboard-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/customer-dashboard-v2.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Customer Dashboard Specific Styles */
        .customer-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Only apply backdrop-filter when dropdowns are closed */
        .customer-dashboard:not(.dropdown-open) .dashboard-header {
            backdrop-filter: blur(10px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        /* Only apply backdrop-filter when dropdowns are closed */
        .customer-dashboard:not(.dropdown-open) .stat-card {
            backdrop-filter: blur(10px);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .stat-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .stat-icon--success { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon--danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .stat-icon--info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .stat-icon--warning { background: linear-gradient(135deg, #f59e0b, #d97706); }
        
        .stat-icon svg {
            color: white;
            width: 24px;
            height: 24px;
        }
        
        .stat-content h3 {
            font-size: 16px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1f2937;
            line-height: 1;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            padding: 0;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        
        /* Only apply backdrop-filter when dropdowns are closed */
        .customer-dashboard:not(.dropdown-open) .action-card {
            backdrop-filter: blur(10px);
        }
        
        .action-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .action-link {
            display: block;
            padding: 25px;
            text-decoration: none;
            color: inherit;
        }
        
        .action-header {
            display: flex;
            align-items: center;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .action-icon--blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .action-icon--green { background: linear-gradient(135deg, #10b981, #059669); }
        .action-icon--purple { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .action-icon--red { background: linear-gradient(135deg, #ef4444, #dc2626); }
        
        .action-icon svg {
            color: white;
            width: 24px;
            height: 24px;
        }
        
        .action-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .action-description {
            font-size: 14px;
            color: #6b7280;
        }
        
        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }
        
        /* DISABLED: Remove all backdrop-filter to fix dropdown z-index issues */
        .customer-dashboard .dashboard-header,
        .customer-dashboard .stat-card,
        .customer-dashboard .action-card,
        .customer-dashboard .dashboard-card {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
        
        .card-header {
            padding: 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .view-all-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        
        .view-all-link:hover {
            color: #2563eb;
        }
        
        .view-all-link svg {
            margin-left: 5px;
            width: 16px;
            height: 16px;
        }
        
        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            padding: 25px;
        }
        
        .ticket-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }
        
        .ticket-item:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        
        .ticket-title {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .ticket-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #64748b;
            font-size: 14px;
        }
        
        .ticket-price {
            font-weight: 700;
            color: #059669;
            font-size: 16px;
        }
        
        .ticket-platform {
            background: #e2e8f0;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            color: #475569;
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Real-time indicators */
        .status-indicator {
            display: inline-flex;
            align-items: center;
            font-size: 12px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .status-online {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pulse::before {
            content: '';
            width: 6px;
            height: 6px;
            background: currentColor;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* GLOBAL DROPDOWN Z-INDEX FIX - HIGHEST PRIORITY */
        .nav-dropdown,
        [data-dropdown],
        [data-dropdown="admin"],
        [data-dropdown="profile"],
        .dropdown-menu {
            z-index: 999999 !important;
            position: absolute !important;
            background: white !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
            isolation: isolate !important;
        }

        /* Ensure navigation has proper z-index */
        #main-navigation,
        nav[role="banner"] {
            z-index: 100000 !important;
            position: sticky !important;
        }
    </style>
@endpush

@section('content')
<div class="customer-dashboard" x-data="customerDashboard()" x-init="init()">

    <!-- Main Dashboard Container -->
    <main class="dashboard-container">
        <!-- Dashboard Header -->
        <header class="dashboard-header">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
                <div>
                    <h1 class="page-title">HD Tickets Dashboard</h1>
                    <p class="page-subtitle">Your gateway to premium sports event tickets</p>
                    
                    <div class="flex items-center space-x-6 mt-4 text-sm text-gray-500">
                        <div class="flex items-center">
                            <span x-text="stats.availableTickets || '{{ $statistics['available-tickets'] ?? 'Loading...' }}'">{{ $statistics['available-tickets'] ?? 'Loading...' }}</span>
                            <span class="ml-1">Available</span>
                        </div>
                        <span class="text-gray-300">•</span>
                        <div class="flex items-center">
                            <span x-text="stats.alerts || '{{ $statistics['alerts'] ?? 'Loading...' }}'">{{ $statistics['alerts'] ?? 'Loading...' }}</span>
                            <span class="ml-1">Active Alerts</span>
                        </div>
                        <span class="text-gray-300">•</span>
                        <div class="flex items-center">
                            <span class="status-indicator status-online status-pulse">Live Data</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <button @click="refreshData()" 
                            :disabled="isLoading" 
                            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <svg x-show="!isLoading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <svg x-show="isLoading" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-show="!isLoading">Refresh</span>
                        <span x-show="isLoading">Updating...</span>
                    </button>
                    
                    <a href="{{ route('tickets.scraping.index') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span>Browse Tickets</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="dashboard-card mb-8">
            <div class="p-6 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-1">Welcome back, {{ $user->name }}! 🎟️</h2>
                        <p class="text-blue-100">Comprehensive Sports Events Entry Tickets Monitoring System</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics Grid -->
        <section class="stats-grid">
            <!-- Available Tickets -->
            <div class="stat-card" @click="navigateTo('{{ route('tickets.scraping.index') }}')">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--success">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Available Tickets</h3>
                        <div class="stat-value" :class="{'loading-skeleton': isLoading}" x-text="isLoading ? '' : formatNumber(stats.availableTickets || {{ $statistics['available-tickets'] ?? 0 }})">
                            {{ number_format($statistics['available-tickets'] ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- High Demand Tickets -->
            <div class="stat-card" @click="navigateTo('{{ route('tickets.scraping.trending') }}')">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>High Demand</h3>
                        <div class="stat-value" :class="{'loading-skeleton': isLoading}" x-text="isLoading ? '' : formatNumber(stats.highDemand || {{ $statistics['high-demand'] ?? 0 }})">
                            {{ number_format($statistics['high-demand'] ?? 0) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts -->
            <div class="stat-card" @click="navigateTo('{{ route('tickets.alerts.index') }}')">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Active Alerts</h3>
                        <div class="stat-value" :class="{'loading-skeleton': isLoading}" x-text="isLoading ? '' : (stats.alerts || {{ $statistics['alerts'] ?? 0 }})">
                            {{ $statistics['alerts'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Purchase Queue -->
            <div class="stat-card" @click="navigateTo('{{ route('purchase-decisions.index') }}')">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--warning">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>In Queue</h3>
                        <div class="stat-value" :class="{'loading-skeleton': isLoading}" x-text="isLoading ? '' : (stats.queue || {{ $statistics['queue'] ?? 0 }})">
                            {{ $statistics['queue'] ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <!-- Quick Actions Section -->
    <section class="actions-section">
        <div class="section-header">
            <h3>Quick Actions</h3>
            <p>Essential ticket management tools</p>
        </div>

        <div class="actions-grid">
            <!-- Browse Tickets -->
            <div class="action-card" data-action="browse">
                <a href="{{ route('tickets.scraping.index') }}" class="action-link">
                    <div class="action-header">
                        <div class="action-icon action-icon--blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="action-title">Browse Tickets</h4>
                            <p class="action-description">Find sports event tickets</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- My Alerts -->
            <div class="action-card" data-action="alerts">
                <a href="{{ route('tickets.alerts.index') }}" class="action-link">
                    <div class="action-header">
                        <div class="action-icon action-icon--green">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="action-title">My Alerts</h4>
                            <p class="action-description">Manage ticket alerts</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Purchase Queue -->
            <div class="action-card" data-action="queue">
                <a href="{{ route('purchase-decisions.index') }}" class="action-link">
                    <div class="action-header">
                        <div class="action-icon action-icon--purple">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="action-title">Purchase Queue</h4>
                            <p class="action-description">Manage ticket purchases</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Ticket Sources -->
            <div class="action-card" data-action="sources">
                <a href="{{ route('ticket-sources.index') }}" class="action-link">
                    <div class="action-header">
                        <div class="action-icon action-icon--red">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="action-title">Ticket Sources</h4>
                            <p class="action-description">Manage platform sources</p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- Recent Tickets Section -->
    <section class="recent-tickets-section">
        <div class="dashboard-card">
            <div class="card-header">
                <h4 class="card-title">Recent Sport Event Tickets</h4>
                <a href="{{ route('tickets.scraping.index') }}" class="view-all-link">
                    View all tickets
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            <div id="recent-tickets-container">
                @if($recentTickets->count() > 0)
                    <table class="tickets-table">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Venue</th>
                                <th class="hide-mobile">Price</th>
                                <th>Status</th>
                                <th class="hide-mobile">Scraped</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTickets as $ticket)
                                <tr class="ticket-row" data-ticket-id="{{ $ticket->id }}">
                                    <td>
                                        <div class="ticket-title">{{ $ticket->title ?? 'Sports Event' }}</div>
                                        @if($ticket->sport)
                                            <div class="ticket-meta">{{ $ticket->sport }}@if($ticket->team) - {{ $ticket->team }}@endif</div>
                                        @endif
                                    </td>
                                    <td class="ticket-venue">{{ $ticket->venue ?? 'TBD' }}</td>
                                    <td class="hide-mobile ticket-price">
                                        @if($ticket->min_price || $ticket->max_price)
                                            @if($ticket->min_price && $ticket->max_price && $ticket->min_price != $ticket->max_price)
                                                ${{ number_format($ticket->min_price, 2) }} - ${{ number_format($ticket->max_price, 2) }}
                                            @else
                                                ${{ number_format($ticket->max_price ?? $ticket->min_price, 2) }}
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </td>
                                    <td>
                                        @if($ticket->is_high_demand)
                                            <span class="status-badge status-badge--high-demand">High Demand</span>
                                        @endif
                                        <span class="status-badge {{ $ticket->is_available ? 'status-badge--available' : 'status-badge--unavailable' }}">
                                            {{ $ticket->is_available ? 'Available' : 'Sold Out' }}
                                        </span>
                                        @if($ticket->platform)
                                            <span class="status-badge status-badge--platform">{{ ucfirst($ticket->platform) }}</span>
                                        @endif
                                    </td>
                                    <td class="hide-mobile">{{ $ticket->scraped_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        <svg class="empty-state-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <h3>No sport event tickets available</h3>
                        <p>Check back soon for new sport event tickets or set up an alert.</p>
                        <a href="{{ route('tickets.alerts.create') }}" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                            </svg>
                            Create Ticket Alert
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
    </main>
</div>

@push('scripts')
<script>
// IMMEDIATE DROPDOWN FIX
document.addEventListener('DOMContentLoaded', function() {
  // Force fix dropdowns immediately
  function forceFixDropdowns() {
    const dropdowns = document.querySelectorAll('[data-dropdown], .nav-dropdown');
    dropdowns.forEach(dropdown => {
      dropdown.style.zIndex = '99999';
      dropdown.style.position = 'absolute';
      dropdown.style.isolation = 'isolate';
    });
    
    // Remove backdrop filters that interfere
    const dashboardElements = document.querySelectorAll('.dashboard-header, .stat-card, .action-card, .dashboard-card');
    dashboardElements.forEach(el => {
      el.style.backdropFilter = 'none';
    });
    
    console.log('🔧 Forced dropdown fixes applied');
  }
  
  // Apply fixes immediately and on mutations
  forceFixDropdowns();
  
  // Re-apply when dropdowns are shown
  document.addEventListener('click', function(e) {
    if (e.target.closest('[aria-haspopup="true"]')) {
      setTimeout(forceFixDropdowns, 10);
    }
  });
  
  // Observer for Alpine.js changes
  const observer = new MutationObserver(function() {
    forceFixDropdowns();
  });
  
  observer.observe(document.body, {
    attributes: true,
    subtree: true,
    attributeFilter: ['style', 'class']
  });
});

function customerDashboard() {
    return {
        stats: {
            availableTickets: null,
            highDemand: null,
            alerts: null,
            queue: null
        },
        isLoading: false,
        lastUpdate: null,
        
        init() {
            this.startRealTimeUpdates();
        },
        
        async refreshData() {
            if (this.isLoading) return;
            
            this.isLoading = true;
            
            try {
                const response = await fetch('/api/v1/dashboard/stats', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    // Handle both direct data and nested data structure from API
                    const data = result.data || result;
                    this.stats = {
                        availableTickets: data.availableTickets || data['available-tickets'] || 0,
                        highDemand: data.highDemand || data['high-demand'] || 0,
                        alerts: data.alerts || 0,
                        queue: data.queue || 0
                    };
                    this.lastUpdate = new Date();
                    console.log('Dashboard stats updated:', this.stats);
                } else {
                    console.error('Failed to fetch dashboard stats:', response.status, response.statusText);
                }
            } catch (error) {
                console.warn('Failed to fetch dashboard stats:', error);
            } finally {
                this.isLoading = false;
            }
        },
        
        startRealTimeUpdates() {
            // Update stats every 30 seconds
            setInterval(() => {
                this.refreshData();
            }, 30000);
        },
        
        navigateTo(url) {
            window.location.href = url;
        },
        
        formatNumber(num) {
            if (num === null || num === undefined) return '0';
            return new Intl.NumberFormat().format(num);
        }
    };
}
</script>
@endpush

@endsection
