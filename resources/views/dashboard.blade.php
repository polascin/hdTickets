@extends('layouts.modern')

@section('title', 'Dashboard')
@section('description', 'Your Sports Ticket Monitoring Dashboard')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                Live
            </span>
            <button x-data="{}" @click="AppCore.getModule('websocket')?.reconnect()" 
                    class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="dashboardManager()" x-init="init()">
    <!-- Performance Timer -->
    @startTimer('dashboard_render')
    
    <!-- Loading State -->
    <div x-show="loading" x-transition class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
        <div class="flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-600 text-sm">Loading dashboard...</p>
        </div>
    </div>
    
    <!-- Welcome Banner -->
    @if(!empty($userStats) && !empty($stats))
        @include('components.dashboard.welcome-banner', [
            'user' => auth()->user(),
            'stats' => $stats ?? []
        ])
    @else
        <!-- Fallback Welcome Banner -->
        <div class="dashboard-card mb-6 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white relative overflow-hidden">
            <div class="relative z-10 p-6">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl sm:text-3xl font-bold mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }}!</h2>
                        <p class="text-white/90 text-sm sm:text-base">Your Sports Ticket Monitoring Dashboard is loading...</p>
                    </div>
                </div>
                <div class="text-xs text-white/80">Dashboard data will appear once loaded.</div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
        <div class="dashboard-card stat-card transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Active Monitors</p>
                    <p class="stat-value" x-text="stats.active_monitors || '{{ $stats['active_monitors'] ?? 0 }}'">{{ $stats['active_monitors'] ?? 0 }}</p>
                    <div class="text-xs text-white/70 mt-1">
                        <span class="inline-flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></span>
                            Live
                        </span>
                    </div>
                </div>
                <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
        </div>

        <div class="dashboard-card stat-card transform hover:scale-105 transition-transform duration-200" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Alerts Today</p>
                    <p class="stat-value" x-text="stats.alerts_today || '{{ $stats['alerts_today'] ?? 0 }}'">{{ $stats['alerts_today'] ?? 0 }}</p>
                    <div class="text-xs text-white/70 mt-1">
                        @if(($stats['alerts_today'] ?? 0) > 0)
                            <span class="inline-flex items-center">
                                <span class="w-2 h-2 bg-yellow-400 rounded-full mr-1 animate-pulse"></span>
                                New alerts
                            </span>
                        @else
                            All caught up
                        @endif
                    </div>
                </div>
                <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                </svg>
            </div>
        </div>

        <div class="dashboard-card stat-card transform hover:scale-105 transition-transform duration-200" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Price Drops</p>
                    <p class="stat-value" x-text="stats.price_drops || '{{ $stats['price_drops'] ?? 0 }}'">{{ $stats['price_drops'] ?? 0 }}</p>
                    <div class="text-xs text-white/70 mt-1">
                        @if(($stats['price_drops'] ?? 0) > 0)
                            <span class="inline-flex items-center">
                                <span class="w-2 h-2 bg-red-400 rounded-full mr-1 animate-pulse"></span>
                                Deals found
                            </span>
                        @else
                            Monitoring prices
                        @endif
                    </div>
                </div>
                <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>

        <div class="dashboard-card stat-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Available Now</p>
                    <p class="stat-value">{{ $stats['available_now'] ?? 0 }}</p>
                </div>
                <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Recent Alerts -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Alerts</h3>
                <a href="{{ route('tickets.alerts.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
            </div>
            <div class="space-y-3">
                <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Kansas City Chiefs vs Patriots</p>
                        <p class="text-xs text-gray-500">Tickets available from $89</p>
                    </div>
                    <span class="text-xs text-gray-400">2m ago</span>
                </div>
                <div class="flex items-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full"></div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Manchester United vs Liverpool</p>
                        <p class="text-xs text-gray-500">Price dropped to £125</p>
                    </div>
                    <span class="text-xs text-gray-400">15m ago</span>
                </div>
                <div class="flex items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">Lakers vs Warriors</p>
                        <p class="text-xs text-gray-500">New tickets available</p>
                    </div>
                    <span class="text-xs text-gray-400">1h ago</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
                    <a href="{{ route('tickets.scraping.index') }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group">
                        <svg class="w-8 h-8 text-blue-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <span class="text-sm font-medium text-blue-700">Browse Tickets</span>
                    </a>
                    <a href="{{ route('tickets.alerts.index') }}" class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group">
                        <svg class="w-8 h-8 text-green-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                        <span class="text-sm font-medium text-green-700">Manage Alerts</span>
                    </a>
                    <a href="{{ route('purchase-decisions.index') }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors group">
                        <svg class="w-8 h-8 text-purple-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="text-sm font-medium text-purple-700">Purchase Queue</span>
                    </a>
                    <a href="{{ route('ticket-sources.index') }}" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors group">
                        <svg class="w-8 h-8 text-orange-600 mb-2 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        <span class="text-sm font-medium text-orange-700">Ticket Sources</span>
                    </a>
                @else
                    <div class="col-span-2 text-center py-8">
                        <p class="text-gray-500 mb-4">Upgrade your account to access more features</p>
                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Upgrade Account
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Platform Status -->
    <div class="dashboard-card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Status</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                </div>
                <p class="text-sm font-medium text-gray-900">Ticketmaster</p>
                <p class="text-xs text-green-600">Online</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                </div>
                <p class="text-sm font-medium text-gray-900">StubHub</p>
                <p class="text-xs text-green-600">Online</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                </div>
                <p class="text-sm font-medium text-gray-900">Vivid Seats</p>
                <p class="text-xs text-green-600">Online</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <div class="w-3 h-3 bg-yellow-500 rounded-full animate-pulse-slow"></div>
                </div>
                <p class="text-sm font-medium text-gray-900">TickPick</p>
                <p class="text-xs text-yellow-600">Slow</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                </div>
                <p class="text-sm font-medium text-gray-900">ViagoGo</p>
                <p class="text-xs text-green-600">Online</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/dashboard-enhancements.js') }}?v={{ time() }}" defer></script>
<script>
// Enhanced dashboard functionality
document.addEventListener('DOMContentLoaded', function() {
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeElement = document.getElementById('currentTime');
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString();
        }
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    
    // Add loading states to action buttons
    const actionButtons = document.querySelectorAll('a[href]');
    actionButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add loading state
            this.style.opacity = '0.7';
            this.style.pointerEvents = 'none';
            
            // Restore after navigation
            setTimeout(() => {
                this.style.opacity = '1';
                this.style.pointerEvents = 'auto';
            }, 1000);
        });
    });
    
    // Animate stat cards on page load
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
    
    // Add hover effects to quick action cards
    const quickActionCards = document.querySelectorAll('.group');
    quickActionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
    
    // Show notifications if available
    if (window.hdTicketsFeedback) {
        // Check for any important updates or alerts
        setTimeout(() => {
            window.hdTicketsFeedback.info('Dashboard loaded', 'Welcome to your ticket monitoring dashboard');
        }, 1000);
    }
    
    console.log('Dashboard enhanced features loaded');
});
</script>
@endpush

@endsection
