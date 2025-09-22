@extends('layouts.modern-app')

@section('title', 'Customer Dashboard')

@section('meta_description', 'Your personalized sports event ticket monitoring dashboard')

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Welcome back, {{ Auth::user()->name ?? 'Customer' }}! 🎟️
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Your personal sports ticket monitoring dashboard
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- Quick Actions -->
            <a href="{{ route('tickets.alerts.create') }}" 
               class="hdt-button hdt-button--primary hdt-button--md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create Alert
            </a>
            
            <a href="{{ route('tickets.scraping.index') }}" 
               class="hdt-button hdt-button--secondary hdt-button--md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Browse Tickets
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="space-y-8" x-data="customerDashboard()" x-init="init()">
        
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Active Alerts -->
            <div class="hdt-stats-card">
                <div class="hdt-stats-card__header">
                    <div class="hdt-stats-card__icon hdt-stats-card__icon--primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                        </svg>
                    </div>
                </div>
                <div class="hdt-stats-card__value" x-text="stats.activeAlerts">5</div>
                <div class="hdt-stats-card__label">Active Alerts</div>
                <div class="hdt-stats-card__change hdt-stats-card__change--positive">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    +2 this week
                </div>
            </div>

            <!-- Available Tickets -->
            <div class="hdt-stats-card">
                <div class="hdt-stats-card__header">
                    <div class="hdt-stats-card__icon hdt-stats-card__icon--success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                </div>
                <div class="hdt-stats-card__value" x-text="stats.availableTickets">1,234</div>
                <div class="hdt-stats-card__label">Available Tickets</div>
                <div class="hdt-stats-card__change hdt-stats-card__change--positive">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    +42 new today
                </div>
            </div>

            <!-- This Month Purchases -->
            <div class="hdt-stats-card">
                <div class="hdt-stats-card__header">
                    <div class="hdt-stats-card__icon hdt-stats-card__icon--warning">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="hdt-stats-card__value" x-text="stats.monthlyPurchases">3</div>
                <div class="hdt-stats-card__label">This Month</div>
                <div class="hdt-stats-card__change hdt-stats-card__change--positive">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                    +1 from last month
                </div>
            </div>

            <!-- Subscription Status -->
            <div class="hdt-stats-card">
                <div class="hdt-stats-card__header">
                    <div class="hdt-stats-card__icon hdt-stats-card__icon--success">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                </div>
                <div class="hdt-stats-card__value">Pro</div>
                <div class="hdt-stats-card__label">Plan Status</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    65/100 tickets used
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <!-- Left Column - Recent Tickets & Alerts -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- Recent Tickets -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest Tickets</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">New tickets matching your interests</p>
                            </div>
                            <a href="{{ route('tickets.scraping.index') }}" 
                               class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                View All →
                            </a>
                        </div>
                    </div>
                    
                    <div class="hdt-card__body">
                        <div class="space-y-4" x-show="recentTickets.length > 0">
                            <template x-for="ticket in recentTickets" :key="ticket.id">
                                <div class="hdt-ticket-card">
                                    <div class="hdt-ticket-card__header">
                                        <div class="hdt-ticket-card__sport">
                                            <span class="hdt-badge hdt-badge--primary hdt-badge--xs" 
                                                  x-text="ticket.sport"></span>
                                        </div>
                                        <h4 class="hdt-ticket-card__title" x-text="ticket.title"></h4>
                                        <p class="hdt-ticket-card__venue" x-text="ticket.venue + ' • ' + ticket.date"></p>
                                        <div class="hdt-ticket-card__meta">
                                            <span x-text="ticket.time"></span>
                                            <span x-text="ticket.platform"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="hdt-ticket-card__body">
                                        <div class="hdt-ticket-card__price">
                                            <div class="hdt-ticket-card__price-current" x-text="'$' + ticket.price"></div>
                                            <div class="hdt-ticket-card__price-change"
                                                 :class="ticket.price_trend === 'down' ? 'hdt-ticket-card__price-change--down' : 'hdt-ticket-card__price-change--up'"
                                                 x-show="ticket.price_change">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                          :d="ticket.price_trend === 'down' ? 'M19 14l-7 7m0 0l-7-7m7 7V3' : 'M5 10l7-7m0 0l7 7m-7-7v18'"/>
                                                </svg>
                                                <span x-text="ticket.price_change"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="hdt-ticket-card__availability">
                                            <span x-text="ticket.available + ' available'"></span>
                                            <span class="text-xs px-2 py-1 rounded-full"
                                                  :class="ticket.demand === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : 
                                                          ticket.demand === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 
                                                          'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'"
                                                  x-text="ticket.demand + ' demand'"></span>
                                        </div>
                                        
                                        <div class="hdt-ticket-card__actions">
                                            <button @click="createAlert(ticket)" 
                                                    class="hdt-button hdt-button--outline hdt-button--sm">
                                                Set Alert
                                            </button>
                                            <button @click="viewTicket(ticket)" 
                                                    class="hdt-button hdt-button--primary hdt-button--sm">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Empty State -->
                        <div x-show="recentTickets.length === 0" class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No tickets yet</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">Start browsing to find tickets that match your interests</p>
                            <a href="{{ route('tickets.scraping.index') }}" 
                               class="hdt-button hdt-button--primary hdt-button--md">
                                Browse Tickets
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Alerts -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Price Alerts</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Monitor ticket prices automatically</p>
                            </div>
                            <a href="{{ route('customer.alerts.index') }}" 
                               class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                                Manage Alerts →
                            </a>
                        </div>
                    </div>
                    
                    <div class="hdt-card__body">
                        <div class="space-y-3">
                            <!-- Alert Item -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Lakers vs Warriors</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Alert when price drops below $150</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">$175</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Current price</div>
                                </div>
                            </div>

                            <!-- Alert Item -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">Chiefs vs Bills</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Alert when price drops below $200</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-red-600 dark:text-red-400">$185 🔥</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ALERT TRIGGERED!</div>
                                </div>
                            </div>

                            <!-- Alert Item -->
                            <div class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">NBA Finals Game 7</h4>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Alert when tickets become available</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Not available</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Monitoring</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Sidebar -->
            <div class="space-y-6">
                
                <!-- Subscription Status -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Subscription</h3>
                    </div>
                    <div class="hdt-card__body">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">Pro Plan</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Active until Jan 2025</div>
                            </div>
                            <div class="hdt-badge hdt-badge--success hdt-badge--md">Active</div>
                        </div>
                        
                        <!-- Usage Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-2">
                                <span class="text-gray-600 dark:text-gray-400">Ticket Alerts</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">65/100</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 65%"></div>
                            </div>
                        </div>
                        
                        <a href="{{ route('customer.subscription.index') }}" 
                           class="hdt-button hdt-button--secondary hdt-button--md w-full">
                            Manage Subscription
                        </a>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h3>
                    </div>
                    <div class="hdt-card__body">
                        <div class="space-y-3">
                            <a href="{{ route('tickets.alerts.create') }}" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-blue-300 dark:hover:border-blue-600 transition-colors group">
                                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-blue-600 dark:group-hover:text-blue-400">Create Alert</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Set up price monitoring</div>
                                </div>
                            </a>

                            <a href="{{ route('customer.purchases.index') }}" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-green-300 dark:hover:border-green-600 transition-colors group">
                                <div class="w-10 h-10 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-green-600 dark:group-hover:text-green-400">Purchase History</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">View past purchases</div>
                                </div>
                            </a>

                            <a href="{{ route('support.index') }}" 
                               class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-purple-300 dark:hover:border-purple-600 transition-colors group">
                                <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 group-hover:text-purple-600 dark:group-hover:text-purple-400">Get Help</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Support & FAQ</div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Activity</h3>
                    </div>
                    <div class="hdt-card__body">
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        Price alert triggered for Chiefs vs Bills
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">2 minutes ago</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        New Lakers tickets available
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">15 minutes ago</p>
                                </div>
                            </div>

                            <div class="flex items-center space-x-3">
                                <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        Created alert for NBA Finals
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">1 hour ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('customerDashboard', () => ({
                stats: {
                    activeAlerts: 5,
                    availableTickets: 1234,
                    monthlyPurchases: 3,
                    subscriptionStatus: 'Pro'
                },
                
                recentTickets: [
                    {
                        id: 1,
                        title: 'Lakers vs Warriors',
                        venue: 'Crypto.com Arena',
                        date: 'Dec 25, 2024',
                        time: '8:00 PM',
                        sport: 'NBA',
                        platform: 'Ticketmaster',
                        price: 175,
                        price_change: '+$15',
                        price_trend: 'up',
                        available: 8,
                        demand: 'high'
                    },
                    {
                        id: 2,
                        title: 'Chiefs vs Bills',
                        venue: 'Arrowhead Stadium',
                        date: 'Jan 15, 2025',
                        time: '3:00 PM',
                        sport: 'NFL',
                        platform: 'StubHub',
                        price: 185,
                        price_change: '-$20',
                        price_trend: 'down',
                        available: 12,
                        demand: 'high'
                    },
                    {
                        id: 3,
                        title: 'Celtics vs Heat',
                        venue: 'TD Garden',
                        date: 'Dec 30, 2024',
                        time: '7:30 PM',
                        sport: 'NBA',
                        platform: 'SeatGeek',
                        price: 95,
                        price_change: null,
                        price_trend: 'stable',
                        available: 25,
                        demand: 'medium'
                    }
                ],
                
                init() {
                    // Initialize dashboard
                    this.loadDashboardData();
                    
                    // Set up real-time updates
                    this.startRealTimeUpdates();
                },
                
                loadDashboardData() {
                    // Simulate loading dashboard data
                    console.log('Loading customer dashboard data...');
                },
                
                startRealTimeUpdates() {
                    // Set up WebSocket or polling for real-time updates
                    setInterval(() => {
                        this.updateStats();
                    }, 30000); // Update every 30 seconds
                },
                
                updateStats() {
                    // Simulate real-time stats updates
                    // In real implementation, this would fetch from API
                },
                
                createAlert(ticket) {
                    // Navigate to create alert page with ticket pre-filled
                    window.location.href = `/alerts/create?ticket=${ticket.id}`;
                },
                
                viewTicket(ticket) {
                    // Navigate to ticket details
                    window.location.href = `/tickets/${ticket.id}`;
                }
            }));
        });
    </script>
@endsection

@push('scripts')
    <!-- Additional scripts for dashboard functionality -->
@endpush