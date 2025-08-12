<x-modern-app-layout title="Dashboard">
    <x-slot name="headerActions">
        <div class="flex items-center space-x-3">
            <x-ui.badge variant="success" dot="true">Live</x-ui.badge>
            <x-ui.button 
                variant="ghost" 
                size="sm"
                x-data="{}" 
                @click="AppCore.getModule('websocket')?.reconnect()"
                title="Reconnect WebSocket">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </x-ui.button>
        </div>
    </x-slot>

    <div x-data="dashboardManager()" x-init="init()">
        <!-- Performance Timer -->
        @startTimer('dashboard_render')
        
        <!-- Loading State -->
        <div x-show="loading" x-transition class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
            <div class="flex flex-col items-center space-y-4">
                <div class="hd-loading-skeleton hd-loading-skeleton--spinner"></div>
                <p class="hd-text-small">Loading dashboard...</p>
            </div>
        </div>
        
        <!-- Welcome Banner -->
        @if(!empty($userStats) && !empty($stats))
            @include('components.dashboard.welcome-banner', [
                'user' => auth()->user(),
                'stats' => $stats ?? []
            ])
        @else
            <x-ui.card class="mb-6" variant="flat">
                <x-ui.card-content class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white rounded-lg relative overflow-hidden">
                    <div class="relative z-10 p-6">
                        <div class="flex items-center mb-3">
                            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="hd-heading-2 !text-white !mb-1">Welcome back, {{ auth()->user()->name ?? 'User' }}!</h2>
                                <p class="text-white/90 hd-text-base">Your Sports Ticket Monitoring Dashboard</p>
                            </div>
                        </div>
                        <p class="hd-text-small text-white/80">Dashboard data will appear once loaded.</p>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        @endif
    
        <!-- Stats Cards -->
        <div class="hd-grid-4 mb-8">
            <x-ui.card hover="true" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 hd-text-small font-medium">Active Monitors</p>
                            <p class="hd-heading-2 !text-white !mb-0" x-text="stats.active_monitors || '{{ $stats['active_monitors'] ?? 0 }}'">{{ $stats['active_monitors'] ?? 0 }}</p>
                            <div class="mt-1">
                                <x-ui.badge variant="success" size="xs" dot="true">Live</x-ui.badge>
                            </div>
                        </div>
                        <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card hover="true" class="bg-gradient-to-br from-green-500 to-green-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 hd-text-small font-medium">Alerts Today</p>
                            <p class="hd-heading-2 !text-white !mb-0" x-text="stats.alerts_today || '{{ $stats['alerts_today'] ?? 0 }}'">{{ $stats['alerts_today'] ?? 0 }}</p>
                            <div class="mt-1">
                                @if(($stats['alerts_today'] ?? 0) > 0)
                                    <x-ui.badge variant="warning" size="xs" dot="true">New alerts</x-ui.badge>
                                @else
                                    <span class="hd-text-small text-white/70">All caught up</span>
                                @endif
                            </div>
                        </div>
                        <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card hover="true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 hd-text-small font-medium">Price Drops</p>
                            <p class="hd-heading-2 !text-white !mb-0" x-text="stats.price_drops || '{{ $stats['price_drops'] ?? 0 }}'">{{ $stats['price_drops'] ?? 0 }}</p>
                            <div class="mt-1">
                                @if(($stats['price_drops'] ?? 0) > 0)
                                    <x-ui.badge variant="error" size="xs" dot="true">Deals found</x-ui.badge>
                                @else
                                    <span class="hd-text-small text-white/70">Monitoring prices</span>
                                @endif
                            </div>
                        </div>
                        <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card hover="true" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 hd-text-small font-medium">Available Now</p>
                            <p class="hd-heading-2 !text-white !mb-0">{{ $stats['available_now'] ?? 0 }}</p>
                        </div>
                        <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    
        <!-- Quick Actions & Recent Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Alerts -->
            <x-ui.card>
                <x-ui.card-header title="Recent Alerts">
                    <x-ui.button href="{{ route('tickets.alerts.index') }}" variant="ghost" size="sm">View All</x-ui.button>
                </x-ui.card-header>
                <x-ui.card-content>
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
                </x-ui.card-content>
            </x-ui.card>
    
            <!-- Quick Actions -->
            <x-ui.card>
                <x-ui.card-header title="Quick Actions"></x-ui.card-header>
                <x-ui.card-content>
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
                                <x-ui.button href="{{ route('profile.edit') }}">Upgrade Account</x-ui.button>
                            </div>
                        @endif
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    
        <!-- Platform Status -->
        <x-ui.card class="mb-8">
            <x-ui.card-header title="Platform Status"></x-ui.card-header>
            <x-ui.card-content>
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
            </x-ui.card-content>
        </x-ui.card>
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
</x-modern-app-layout>
