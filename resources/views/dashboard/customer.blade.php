@extends('layouts.app')

@section('title', 'Sports Ticket Hub - Customer Dashboard')

@push('styles')
    <link href="{{ asset('css/customer-dashboard-simple.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<body class="customer-dashboard">

<!-- Main Dashboard Container -->
<main class="dashboard-container">
    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <h1 class="page-title">Sports Ticket Hub</h1>
        <p class="page-subtitle">Find, track, and purchase the best sports event tickets</p>
        
        <div class="header-actions">
            <div class="stats-summary">
                <span class="stat-item">{{ $statistics['available-tickets'] }} Available</span>
                <span class="stat-separator">•</span>
                <span class="stat-item">{{ $statistics['alerts'] }} Active Alerts</span>
                <span class="stat-separator">•</span>
                <span class="stat-item" id="last-refresh-time">{{ now()->format('H:i') }}</span>
            </div>
            
            <a href="{{ route('tickets.scraping.index') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Browse Tickets
            </a>
        </div>
    </header>

    <!-- Welcome Section -->
    <section class="welcome-section">
        <div class="welcome-banner">
            <h2>Welcome to Sports Ticket Hub, {{ $user->name }}! 🎟️</h2>
            <p>Comprehensive Sport Events Entry Tickets Monitoring, Scraping and Purchase System</p>
        </div>
    </section>

    <!-- Statistics Grid -->
    <section class="stats-section">
        <div class="stats-grid">
            <!-- Available Tickets -->
            <div class="stat-card" data-stat="available-tickets">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Available Tickets</h3>
                        <div class="stat-value">{{ number_format($statistics['available-tickets']) }}</div>
                    </div>
                </div>
            </div>

            <!-- High Demand Tickets -->
            <div class="stat-card" data-stat="high-demand">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>High Demand</h3>
                        <div class="stat-value">{{ number_format($statistics['high-demand']) }}</div>
                    </div>
                </div>
            </div>

            <!-- Active Alerts -->
            <div class="stat-card" data-stat="alerts">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--info">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>Active Alerts</h3>
                        <div class="stat-value">{{ $statistics['alerts'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Purchase Queue -->
            <div class="stat-card" data-stat="queue">
                <div class="stat-header">
                    <div class="stat-icon stat-icon--warning">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <h3>In Queue</h3>
                        <div class="stat-value">{{ $statistics['queue'] }}</div>
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

@push('scripts')
    <script src="{{ asset('js/customer-dashboard-simple.js') }}"></script>
@endpush

</body>
@endsection
