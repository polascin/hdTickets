@extends('layouts.app')

@section('title', 'Enhanced Customer Dashboard - HD Tickets Sports Events')

@push('styles')
    <link href="{{ asset('css/dashboard-common.css') }}" rel="stylesheet">
    <link href="{{ asset('css/customer-dashboard-enhanced.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="dashboard-api" content="{{ route('api.dashboard.realtime') }}">
    <meta name="analytics-api" content="{{ route('api.dashboard.analytics.data') }}">
@endpush

@section('content')
<div class="enhanced-customer-dashboard" 
     x-data="enhancedCustomerDashboard()" 
     x-init="init()"
     data-user-id="{{ $user->id }}">

    <!-- Dashboard Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <div class="header-info">
                <div class="welcome-section">
                    <h1 class="dashboard-title">
                        <span class="gradient-text">HD Tickets</span>
                        <span class="live-indicator" :class="{'pulse': isLiveData}">
                            <svg class="live-icon" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="4"/>
                            </svg>
                            LIVE
                        </span>
                    </h1>
                    <p class="dashboard-subtitle">Welcome back, {{ $user->name }}! 🎟️</p>
                </div>
                
                <div class="stats-summary">
                    <div class="stat-item">
                        <span class="stat-value" x-text="formatNumber(dashboardData.statistics?.available_tickets?.current || 0)">
                            {{ number_format($statistics['available_tickets']['current'] ?? 0) }}
                        </span>
                        <span class="stat-label">Available</span>
                        <div class="trend-indicator" :class="getTrendClass(dashboardData.statistics?.available_tickets?.trend)">
                            <svg class="trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="stat-divider">•</div>
                    
                    <div class="stat-item">
                        <span class="stat-value" x-text="dashboardData.statistics?.active_alerts?.current || 0">
                            {{ $statistics['active_alerts']['current'] ?? 0 }}
                        </span>
                        <span class="stat-label">Alerts</span>
                    </div>
                    
                    <div class="stat-divider">•</div>
                    
                    <div class="stat-item">
                        <span class="stat-value" x-text="dashboardData.statistics?.high_demand?.current || 0">
                            {{ $statistics['high_demand']['current'] ?? 0 }}
                        </span>
                        <span class="stat-label">Hot</span>
                    </div>
                </div>
            </div>
            
            <div class="header-actions">
                <div class="refresh-controls">
                    <button @click="toggleAutoRefresh()" 
                            :class="{'active': autoRefresh}" 
                            class="auto-refresh-btn"
                            :title="autoRefresh ? 'Auto-refresh enabled' : 'Auto-refresh disabled'">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span x-text="autoRefresh ? 'Auto' : 'Manual'"></span>
                    </button>
                    
                    <button @click="refreshDashboard()" 
                            :disabled="isLoading" 
                            class="refresh-btn">
                        <svg :class="{'animate-spin': isLoading}" class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span x-show="!isLoading">Refresh</span>
                        <span x-show="isLoading">Updating...</span>
                    </button>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('tickets.scraping.index') }}" class="action-btn primary">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Browse Tickets
                    </a>
                    
                    <button @click="openNotifications()" class="notification-btn" :class="{'has-notifications': hasNotifications}">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"/>
                        </svg>
                        <span class="notification-badge" x-show="notificationCount > 0" x-text="notificationCount"></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Data Freshness Indicator -->
        <div class="freshness-indicator" :class="getDataFreshnessClass()">
            <span x-text="getDataFreshnessText()"></span>
            <span class="separator">•</span>
            <span x-text="'Updated ' + getLastUpdateTime()"></span>
        </div>
    </header>

    <!-- Main Dashboard Content -->
    <main class="dashboard-main">
        
        <!-- Key Metrics Grid -->
        <section class="metrics-grid">
            <!-- Available Tickets Card -->
            <div class="metric-card available-tickets" @click="navigateTo('{{ route('tickets.scraping.index') }}')">
                <div class="metric-header">
                    <div class="metric-icon success">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <div class="metric-trend" :class="getTrendClass(dashboardData.statistics?.available_tickets?.trend)">
                        <svg class="trend-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                
                <div class="metric-content">
                    <h3 class="metric-title">Available Tickets</h3>
                    <div class="metric-value-container">
                        <span class="metric-value" x-text="formatNumber(dashboardData.statistics?.available_tickets?.current || {{ $statistics['available_tickets']['current'] ?? 0 }})">
                            {{ number_format($statistics['available_tickets']['current'] ?? 0) }}
                        </span>
                        <span class="metric-change" x-show="dashboardData.statistics?.available_tickets?.change_24h" 
                              x-text="formatChange(dashboardData.statistics?.available_tickets?.change_24h)">
                        </span>
                    </div>
                    <p class="metric-description">Active ticket listings</p>
                </div>
                
                <div class="metric-footer">
                    <span class="metric-action">View All →</span>
                </div>
            </div>

            <!-- High Demand Card -->
            <div class="metric-card high-demand" @click="navigateTo('{{ route('tickets.scraping.trending') }}')">
                <div class="metric-header">
                    <div class="metric-icon danger">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="popularity-indicator">
                        <div class="fire-icon">🔥</div>
                    </div>
                </div>
                
                <div class="metric-content">
                    <h3 class="metric-title">High Demand</h3>
                    <div class="metric-value-container">
                        <span class="metric-value" x-text="formatNumber(dashboardData.statistics?.high_demand?.current || {{ $statistics['high_demand']['current'] ?? 0 }})">
                            {{ number_format($statistics['high_demand']['current'] ?? 0) }}
                        </span>
                    </div>
                    <p class="metric-description">Hot tickets right now</p>
                </div>
                
                <div class="metric-footer">
                    <span class="metric-action">View Trending →</span>
                </div>
            </div>

            <!-- Active Alerts Card -->
            <div class="metric-card alerts" @click="navigateTo('{{ route('tickets.alerts.index') }}')">
                <div class="metric-header">
                    <div class="metric-icon info">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"/>
                        </svg>
                    </div>
                    <div class="alert-indicator" x-show="dashboardData.statistics?.active_alerts?.triggered_today > 0">
                        <span x-text="dashboardData.statistics?.active_alerts?.triggered_today"></span>
                    </div>
                </div>
                
                <div class="metric-content">
                    <h3 class="metric-title">My Alerts</h3>
                    <div class="metric-value-container">
                        <span class="metric-value" x-text="dashboardData.statistics?.active_alerts?.current || {{ $statistics['active_alerts']['current'] ?? 0 }}">
                            {{ $statistics['active_alerts']['current'] ?? 0 }}
                        </span>
                    </div>
                    <p class="metric-description">
                        Active monitoring • 
                        <span x-text="(dashboardData.statistics?.active_alerts?.success_rate || {{ $statistics['active_alerts']['success_rate'] ?? 0 }}) + '%'">{{ $statistics['active_alerts']['success_rate'] ?? 0 }}%</span> success rate
                    </p>
                </div>
                
                <div class="metric-footer">
                    <span class="metric-action">Manage Alerts →</span>
                </div>
            </div>

            <!-- User Activity Card -->
            <div class="metric-card user-activity">
                <div class="metric-header">
                    <div class="metric-icon warning">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div class="engagement-score">
                        <div class="score-circle">
                            <span x-text="Math.round(dashboardData.statistics?.user_activity?.engagement_score || {{ $statistics['user_activity']['engagement_score'] ?? 75 }})">
                                {{ round($statistics['user_activity']['engagement_score'] ?? 75) }}
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="metric-content">
                    <h3 class="metric-title">Your Activity</h3>
                    <div class="metric-value-container">
                        <span class="metric-value" x-text="dashboardData.statistics?.user_activity?.views_today || {{ $statistics['user_activity']['views_today'] ?? 0 }}">
                            {{ $statistics['user_activity']['views_today'] ?? 0 }}
                        </span>
                    </div>
                    <p class="metric-description">Views today • Engagement score</p>
                </div>
                
                <div class="metric-footer">
                    <span class="metric-action">View Stats →</span>
                </div>
            </div>
        </section>

        <!-- Quick Actions Section -->
        <section class="quick-actions">
            <div class="section-header">
                <h2 class="section-title">Quick Actions</h2>
                <p class="section-subtitle">Essential ticket management tools</p>
            </div>
            
            <div class="actions-grid">
                <a href="{{ route('tickets.scraping.index') }}" class="action-card browse-tickets">
                    <div class="action-icon blue">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Browse Tickets</h3>
                        <p class="action-description">Find and compare sports event tickets</p>
                        <span class="action-badge">{{ number_format($statistics['available_tickets']['current'] ?? 0) }} available</span>
                    </div>
                </a>
                
                <a href="{{ route('tickets.alerts.create') }}" class="action-card create-alert">
                    <div class="action-icon green">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Create Alert</h3>
                        <p class="action-description">Set up automated ticket monitoring</p>
                        <span class="action-badge">Smart notifications</span>
                    </div>
                </a>
                
                <a href="{{ route('tickets.alerts.index') }}" class="action-card manage-alerts">
                    <div class="action-icon purple">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Manage Alerts</h3>
                        <p class="action-description">View and edit your ticket alerts</p>
                        <span class="action-badge">{{ $statistics['active_alerts']['current'] ?? 0 }} active</span>
                    </div>
                </a>
                
                <div class="action-card settings" @click="openSettings()">
                    <div class="action-icon gray">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100-4m0 4v2m0-6V4"/>
                        </svg>
                    </div>
                    <div class="action-content">
                        <h3 class="action-title">Preferences</h3>
                        <p class="action-description">Customize your dashboard experience</p>
                        <span class="action-badge">Personalize</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Grid -->
        <div class="content-grid">
            
            <!-- Recent Tickets Widget -->
            <section class="dashboard-widget recent-tickets">
                <div class="widget-header">
                    <h3 class="widget-title">Latest Tickets</h3>
                    <div class="widget-actions">
                        <button @click="refreshRecentTickets()" class="widget-refresh">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        <a href="{{ route('tickets.scraping.index') }}" class="widget-view-all">View All</a>
                    </div>
                </div>
                
                <div class="widget-content">
                    <div x-show="isLoading" class="loading-state">
                        <div class="skeleton-loader"></div>
                    </div>
                    
                    <template x-for="ticket in recentTickets" :key="ticket.id">
                        <div class="ticket-item" @click="viewTicket(ticket)">
                            <div class="ticket-header">
                                <div class="ticket-title" x-text="ticket.title"></div>
                                <div class="ticket-price" x-text="formatPrice(ticket.price)"></div>
                            </div>
                            <div class="ticket-details">
                                <span class="ticket-platform" x-text="ticket.platform"></span>
                                <span class="ticket-venue" x-text="ticket.venue"></span>
                                <span class="ticket-date" x-text="formatDate(ticket.event_date)"></span>
                            </div>
                            <div class="ticket-indicators">
                                <span class="demand-indicator" :class="'demand-' + ticket.demand_indicator" x-text="ticket.demand_indicator"></span>
                                <span class="urgency-indicator" :class="'urgency-' + ticket.urgency_level" x-text="ticket.urgency_level"></span>
                            </div>
                        </div>
                    </template>
                    
                    @if(isset($recentTickets) && $recentTickets->isNotEmpty())
                        @foreach($recentTickets as $ticket)
                        <div class="ticket-item" onclick="window.open('{{ route('tickets.scraping.show', $ticket->id) }}', '_blank')">
                            <div class="ticket-header">
                                <div class="ticket-title">{{ $ticket->title }}</div>
                                <div class="ticket-price">${{ number_format($ticket->price, 2) }}</div>
                            </div>
                            <div class="ticket-details">
                                <span class="ticket-platform">{{ $ticket->platform }}</span>
                                <span class="ticket-venue">{{ $ticket->venue }}</span>
                                <span class="ticket-date">{{ $ticket->event_date ? \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y') : 'TBD' }}</span>
                            </div>
                            <div class="ticket-indicators">
                                <span class="demand-indicator demand-{{ strtolower($ticket->demand_indicator ?? 'medium') }}">
                                    {{ ucfirst($ticket->demand_indicator ?? 'medium') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"/>
                            </svg>
                            <p class="empty-text">No recent tickets found</p>
                            <a href="{{ route('tickets.scraping.index') }}" class="empty-action">Browse Available Tickets</a>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Personalized Recommendations Widget -->
            <section class="dashboard-widget recommendations">
                <div class="widget-header">
                    <h3 class="widget-title">
                        <svg class="title-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        For You
                    </h3>
                    <span class="widget-badge">Personalized</span>
                </div>
                
                <div class="widget-content">
                    @if(isset($personalizedRecommendations) && !empty($personalizedRecommendations))
                        @foreach($personalizedRecommendations as $recommendation)
                        <div class="recommendation-item">
                            <div class="recommendation-header">
                                <div class="recommendation-title">{{ $recommendation['ticket']->title }}</div>
                                <div class="confidence-score">{{ round($recommendation['confidence_score']) }}%</div>
                            </div>
                            <div class="recommendation-details">
                                <span class="recommendation-price">${{ number_format($recommendation['ticket']->price, 2) }}</span>
                                <span class="recommendation-reason">{{ $recommendation['match_reason'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <p class="empty-text">Set up your preferences to get personalized recommendations</p>
                            <button @click="openSettings()" class="empty-action">Configure Preferences</button>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Trends Chart Widget -->
            <section class="dashboard-widget trends-chart">
                <div class="widget-header">
                    <h3 class="widget-title">Market Trends</h3>
                    <div class="trend-period">Last 7 Days</div>
                </div>
                
                <div class="widget-content">
                    <div class="chart-container">
                        <canvas id="trendsChart" width="400" height="200"></canvas>
                    </div>
                    
                    <div class="trend-summary">
                        <div class="trend-item">
                            <span class="trend-label">Avg. Price</span>
                            <span class="trend-value">$245</span>
                            <span class="trend-change positive">+5.2%</span>
                        </div>
                        <div class="trend-item">
                            <span class="trend-label">Availability</span>
                            <span class="trend-value">{{ number_format($statistics['available_tickets']['current'] ?? 0) }}</span>
                            <span class="trend-change neutral">-</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Upcoming Events Widget -->
            <section class="dashboard-widget upcoming-events">
                <div class="widget-header">
                    <h3 class="widget-title">Upcoming Events</h3>
                    <span class="widget-subtitle">Based on your preferences</span>
                </div>
                
                <div class="widget-content">
                    @if(isset($upcomingEvents) && $upcomingEvents->isNotEmpty())
                        @foreach($upcomingEvents->take(4) as $event)
                        <div class="event-item">
                            <div class="event-date">
                                <span class="month">{{ \Carbon\Carbon::parse($event->event_date)->format('M') }}</span>
                                <span class="day">{{ \Carbon\Carbon::parse($event->event_date)->format('j') }}</span>
                            </div>
                            <div class="event-details">
                                <div class="event-title">{{ $event->title }}</div>
                                <div class="event-venue">{{ $event->venue }}</div>
                                <div class="event-price">From ${{ number_format($event->price, 2) }}</div>
                            </div>
                            <div class="event-actions">
                                <button class="alert-btn" @click="createEventAlert('{{ $event->id }}')">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <p class="empty-text">No upcoming events match your preferences</p>
                            <button @click="openSettings()" class="empty-action">Update Preferences</button>
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </main>

    <!-- Notification Panel -->
    <aside class="notification-panel" x-show="showNotifications" @click.away="showNotifications = false">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button @click="showNotifications = false" class="close-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <div class="notification-content">
            <template x-for="notification in notifications" :key="notification.id">
                <div class="notification-item" :class="notification.type">
                    <div class="notification-icon">
                        <svg fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2L3 7v11c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V7l-7-5z"/>
                        </svg>
                    </div>
                    <div class="notification-details">
                        <div class="notification-title" x-text="notification.title"></div>
                        <div class="notification-message" x-text="notification.message"></div>
                        <div class="notification-time" x-text="notification.time"></div>
                    </div>
                </div>
            </template>
            
            <div x-show="notifications.length === 0" class="empty-notifications">
                <p>No new notifications</p>
            </div>
        </div>
    </aside>

    <!-- Settings Modal -->
    <div class="modal-overlay" x-show="showSettings" @click.self="showSettings = false">
        <div class="modal-content settings-modal">
            <div class="modal-header">
                <h3>Dashboard Settings</h3>
                <button @click="showSettings = false" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body">
                <div class="settings-section">
                    <h4>Data Refresh</h4>
                    <div class="setting-item">
                        <label class="setting-label">
                            <input type="checkbox" x-model="settings.autoRefresh"> 
                            Auto-refresh dashboard data
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-label">Refresh interval (minutes):</label>
                        <select x-model="settings.refreshInterval">
                            <option value="1">1 minute</option>
                            <option value="5">5 minutes</option>
                            <option value="10">10 minutes</option>
                            <option value="30">30 minutes</option>
                        </select>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h4>Notifications</h4>
                    <div class="setting-item">
                        <label class="setting-label">
                            <input type="checkbox" x-model="settings.notifications"> 
                            Enable notifications
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-label">
                            <input type="checkbox" x-model="settings.emailAlerts"> 
                            Email alerts
                        </label>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h4>Display</h4>
                    <div class="setting-item">
                        <label class="setting-label">Theme:</label>
                        <select x-model="settings.theme">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="auto">Auto</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button @click="saveSettings()" class="btn-primary">Save Settings</button>
                <button @click="showSettings = false" class="btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/customer-dashboard-enhanced.js') }}"></script>
<script>
// Initialize dashboard data
window.dashboardInitialData = {!! json_encode([
    'statistics' => $statistics ?? [],
    'recentTickets' => $recentTickets ?? [],
    'userPreferences' => $userPreferences ?? [],
]) !!};
</script>
@endpush
