<!DOCTYPE html>
<html lang="en" data-realtime="true">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="HD Tickets - Modern Sports Event Ticket Monitoring Dashboard">
    <title>HD Tickets - Dashboard</title>
    
    <!-- Modern CSS Framework -->
    <link href="{{ asset('css/customer-dashboard-v2.css') }}?v={{ time() }}" rel="stylesheet">
    
    <!-- Preload Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- WebSocket Connection Configuration -->
    <script>
        window.websocketConfig = {
            url: '{{ config("websocket.url", "ws://localhost:6001") }}',
            key: '{{ config("websocket.key") }}',
            auth: {
                userId: {{ Auth::id() }},
                token: '{{ csrf_token() }}'
            }
        };
    </script>
</head>
<body>
@php
    $totalTickets = \App\Models\ScrapedTicket::where('is_available', true)->count();
    $userAlerts = \App\Models\TicketAlert::forUser(Auth::id())->where('status', 'active')->count();
    $availableTickets = \App\Models\ScrapedTicket::where('is_available', true)->count();
    $highDemandTickets = \App\Models\ScrapedTicket::where('is_high_demand', true)->where('is_available', true)->count();
    $userPurchaseQueue = \App\Models\PurchaseQueue::where('selected_by_user_id', Auth::id())->where('status', 'queued')->count();
@endphp

<main class="dashboard-container" data-user-id="{{ Auth::id() }}" data-realtime-enabled="true">
    <!-- Dashboard Header -->
    <header class="dashboard-header" data-section="header">
        <div class="header-content">
            <div>
                <h1 class="page-title">HD Tickets Dashboard</h1>
                <p class="page-subtitle">Your gateway to premium sports event tickets</p>
            </div>
            
            <div class="header-actions" data-realtime="header-stats">
                <div class="stats-summary" data-refresh="true">
                    <span class="stat-item" data-stat="total-tickets">{{ number_format($totalTickets) }} Available</span>
                    <span class="stat-separator">•</span>
                    <span class="stat-item" data-stat="user-alerts">{{ $userAlerts }} Active Alerts</span>
                </div>
                
                <a href="{{ route('tickets.scraping.index') }}" class="btn btn-primary" data-hook="browse-tickets">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    Browse Tickets
                </a>
            </div>
        </div>
    </header>

    <!-- Welcome Banner -->
    <section class="welcome-section" data-section="welcome" data-realtime="welcome-banner">
        <div class="welcome-banner">
            <div class="flex items-center justify-between">
                <div>
                    <h2>Welcome back, {{ Auth::user()->name }}! 🎟️</h2>
                    <p>Stay ahead with real-time sports ticket monitoring and smart alerts</p>
                </div>
                <div class="live-status" data-realtime="connection-status">
                    <div class="text-sm">Live Updates</div>
                    <div class="connection-indicator" data-connection-indicator="true">
                        <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse" data-status="active"></div>
                        <span class="text-lg font-bold" data-connection-text="Active">Connected</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Grid -->
    <section class="stats-section" data-section="stats" data-realtime="statistics">
        <div class="stats-grid" data-refresh="true">
            <!-- Available Tickets -->
            <div class="stat-card stat-available-tickets" data-stat-type="available-tickets" data-value="{{ $availableTickets }}">
                <div class="stat-content">
                    <div class="stat-icon bg-green-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Available Tickets</h3>
                        <div class="stat-value" data-live-value="available-tickets">{{ number_format($availableTickets) }}</div>
                    </div>
                </div>
            </div>

            <!-- High Demand Tickets -->
            <div class="stat-card stat-high-demand" data-stat-type="high-demand" data-value="{{ $highDemandTickets }}">
                <div class="stat-content">
                    <div class="stat-icon bg-red-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">High Demand</h3>
                        <div class="stat-value" data-live-value="high-demand">{{ number_format($highDemandTickets) }}</div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts -->
            <div class="stat-card stat-alerts" data-stat-type="alerts" data-value="{{ $userAlerts }}">
                <div class="stat-content">
                    <div class="stat-icon bg-blue-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">Active Alerts</h3>
                        <div class="stat-value" data-live-value="alerts">{{ $userAlerts }}</div>
                    </div>
                </div>
            </div>

            <!-- Purchase Queue -->
            <div class="stat-card stat-queue" data-stat-type="queue" data-value="{{ $userPurchaseQueue }}">
                <div class="stat-content">
                    <div class="stat-icon bg-purple-500">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="stat-info">
                        <h3 class="stat-title">In Queue</h3>
                        <div class="stat-value" data-live-value="queue">{{ $userPurchaseQueue }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions Section -->
    <section class="actions-section" data-section="actions">
        <div class="dashboard-card">
            <div class="section-header">
                <div>
                    <h3>Quick Actions</h3>
                    <p>Essential tools for ticket management</p>
                </div>
            </div>
            
            <div class="actions-grid" data-actions="quick-nav">
                <div class="action-card browse-tickets" data-action="browse" data-hook="action-browse">
                    <a href="{{ route('tickets.scraping.index') }}" class="action-link">
                        <div class="group">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-blue-900">Browse Tickets</h4>
                                    <p class="text-blue-700">Discover available sports tickets</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="action-card alerts" data-action="alerts" data-hook="action-alerts">
                    <a href="{{ route('tickets.alerts.index') }}" class="action-link">
                        <div class="group">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-green-900">My Alerts</h4>
                                    <p class="text-green-700">Manage ticket notifications</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="action-card queue" data-action="queue" data-hook="action-queue">
                    <a href="{{ route('purchase-decisions.index') }}" class="action-link">
                        <div class="group">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-purple-900">Purchase Queue</h4>
                                    <p class="text-purple-700">Review pending purchases</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <div class="action-card sources" data-action="sources" data-hook="action-sources">
                    <a href="{{ route('ticket-sources.index') }}" class="action-link">
                        <div class="group">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-red-900">Ticket Sources</h4>
                                    <p class="text-red-700">Manage platform sources</p>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Tickets Section -->
    <section class="recent-tickets-section" data-section="recent-tickets" data-realtime="ticket-updates">
        <div class="bg-white overflow-hidden shadow-lg rounded-xl">
            <div class="px-6 py-6">
                <div class="section-header">
                    <h4>Recent Sports Tickets</h4>
                    <a href="{{ route('tickets.scraping.index') }}" class="text-brand-600 hover:text-brand-700" data-hook="view-all-tickets">
                        View all tickets
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                @php
                    $recentSportTickets = \App\Models\ScrapedTicket::where('is_available', true)
                        ->latest('scraped_at')
                        ->limit(5)
                        ->get();
                @endphp

                <div class="recent-tickets-list" data-refresh="true" data-live-updates="tickets">
                    @if($recentSportTickets->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentSportTickets as $ticket)
                                <div class="ticket-item" data-ticket-id="{{ $ticket->id }}" data-realtime="ticket-{{ $ticket->id }}">
                                    <div class="availability-indicator {{ $ticket->is_available ? 'bg-green-400' : 'bg-red-400' }} {{ $ticket->is_high_demand ? 'animate-pulse' : '' }}" data-status="{{ $ticket->is_available ? 'available' : 'unavailable' }}"></div>
                                    
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <span class="ticket-title">{{ $ticket->event_name ?? 'Sports Event Ticket' }}</span>
                                                    @if($ticket->venue)
                                                        <span class="text-gray-500">at {{ $ticket->venue }}</span>
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-500">Scraped {{ $ticket->scraped_at->diffForHumans() }}</p>
                                                @if($ticket->price)
                                                    <p class="text-sm font-semibold text-green-600">${{ number_format($ticket->price, 2) }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if($ticket->is_high_demand)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        High Demand
                                                    </span>
                                                @endif
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $ticket->is_available ? 'Available' : 'Sold Out' }}
                                                </span>
                                                @if($ticket->source_platform)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ ucfirst($ticket->source_platform) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12" data-empty-state="no-tickets">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets available yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Check back soon or create an alert to get notified.</p>
                            <div class="mt-6">
                                <a href="{{ route('tickets.alerts.create') }}" class="btn btn-primary" data-hook="create-alert">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                                    </svg>
                                    Create Alert
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript -->
    <script src="{{ asset('js/websocket-client.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/customer-dashboard.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/skeleton-loaders.js') }}?v={{ time() }}"></script>

    <script>
        // Initialize real-time dashboard
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof CustomerDashboard !== 'undefined') {
                window.dashboardInstance = new CustomerDashboard();
                console.log('Modern HD Tickets Dashboard initialized successfully');
            }
        });
    </script>
</main>
</body>
</html>
