{{--
    Example Dashboard - Demonstrates Unified Layout System
    Shows how to use the master layout with role-based variants
--}}
@extends('layouts.master')

@section('title', 'Dashboard')

@section('meta_description', 'HD Tickets unified dashboard demonstrating responsive layout system')

@push('head')
    {{-- Page-specific meta tags --}}
    <meta property="og:title" content="HD Tickets Dashboard">
    <meta property="og:description" content="Sports event tickets monitoring dashboard">
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                @if(auth()->user()->isAdmin())
                    Admin Dashboard
                @elseif(auth()->user()->isAgent())
                    Agent Dashboard  
                @else
                    Customer Dashboard
                @endif
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Welcome back, {{ auth()->user()->getProfileDisplay()['display_name'] }}
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-2 sm:space-y-0 sm:space-x-4">
            {{-- Quick Actions based on role --}}
            @if(auth()->user()->isAdmin())
                <button class="btn-primary">
                    <i class="fas fa-plus mr-2"></i>
                    Add User
                </button>
                <button class="btn-secondary">
                    <i class="fas fa-download mr-2"></i>
                    Export Data
                </button>
            @elseif(auth()->user()->isAgent())
                <button class="btn-primary">
                    <i class="fas fa-bell mr-2"></i>
                    Create Alert
                </button>
                <button class="btn-secondary">
                    <i class="fas fa-search mr-2"></i>
                    Browse Tickets
                </button>
            @else
                <button class="btn-primary">
                    <i class="fas fa-ticket-alt mr-2"></i>
                    Find Tickets
                </button>
            @endif
        </div>
    </div>
@endsection

@section('content')
    {{-- Stats Grid - Responsive across all device types --}}
    <div class="dashboard-stats-grid mb-6">
        @if(auth()->user()->isAdmin())
            {{-- Admin Stats --}}
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">2,847</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-green-600">+12%</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">from last month</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Scraping</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">156</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-robot text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <div class="real-time-indicator">Live</div>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">System Health</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">98.5%</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-heartbeat text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-green-600">Excellent</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">$45.2K</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-dollar-sign text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-green-600">+8.2%</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">from last month</span>
                </div>
            </div>
        @elseif(auth()->user()->isAgent())
            {{-- Agent Stats --}}
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Alerts</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">24</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-yellow-600">3 pending</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Events Monitored</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">89</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <div class="real-time-indicator">Live Updates</div>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Success Rate</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">94.2%</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-target text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-green-600">+2.1%</span>
                    <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">this week</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Queue Items</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">15</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-list text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-blue-600">Processing</span>
                </div>
            </div>
        @else
            {{-- Customer Stats --}}
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">My Watchlist</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">8</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-eye text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-green-600">2 available</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Price Alerts</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">5</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <div class="real-time-indicator">Active</div>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Saved Searches</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">12</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-search text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-blue-600">Ready to use</span>
                </div>
            </div>
            
            <div class="stat-card-enhanced">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Notifications</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">3</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-inbox text-white text-xl"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-sm text-yellow-600">New</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Content Grid - Adapts to screen size --}}
    <div class="card-grid">
        {{-- Left Column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Recent Activity Card --}}
            <div class="main-section">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h3>
                    <button class="text-sm text-blue-600 hover:text-blue-800">View All</button>
                </div>
                
                <div class="space-y-4">
                    @for($i = 1; $i <= 5; $i++)
                        <div class="flex items-center space-x-4 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-ticket-alt text-white text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    New tickets found for Lakers vs Warriors
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ now()->subMinutes($i * 15)->diffForHumans() }}
                                </p>
                            </div>
                            <div class="badge badge-success">New</div>
                        </div>
                    @endfor
                </div>
            </div>

            {{-- Chart/Analytics Section (Desktop and Tablet) --}}
            <div class="main-section hidden md:block">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Overview</h3>
                <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                    <p class="text-gray-500 dark:text-gray-400">Chart visualization placeholder</p>
                </div>
            </div>
        </div>

        {{-- Right Column (Desktop) / Bottom Section (Mobile) --}}
        <div class="space-y-6">
            {{-- Quick Actions --}}
            <div class="main-section">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                
                <div class="grid grid-cols-1 gap-3">
                    @if(auth()->user()->isAdmin())
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-users mr-3"></i>
                            Manage Users
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-cogs mr-3"></i>
                            System Settings
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-chart-bar mr-3"></i>
                            View Reports
                        </button>
                    @elseif(auth()->user()->isAgent())
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-plus mr-3"></i>
                            Create Alert
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-search mr-3"></i>
                            Browse Tickets
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-list mr-3"></i>
                            Manage Queue
                        </button>
                    @else
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-search mr-3"></i>
                            Find Events
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-heart mr-3"></i>
                            My Wishlist
                        </button>
                        <button class="btn-secondary w-full justify-start">
                            <i class="fas fa-bell mr-3"></i>
                            Price Alerts
                        </button>
                    @endif
                </div>
            </div>

            {{-- System Status --}}
            <div class="main-section">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">System Status</h3>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Ticket Scraping</span>
                        <div class="status-indicator status-online">Online</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Price Monitoring</span>
                        <div class="status-indicator status-online">Online</div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Alert System</span>
                        <div class="status-indicator status-online">Online</div>
                    </div>
                    @if(auth()->user()->isAdmin())
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                            <div class="status-indicator status-pending">Maintenance</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Example dashboard-specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize any dashboard-specific functionality
        console.log('Dashboard loaded for:', '{{ auth()->user()->getRoleNames()->first() }}');
        
        // Simulate real-time updates
        setInterval(function() {
            // Update real-time indicators
            const indicators = document.querySelectorAll('.real-time-indicator');
            indicators.forEach(indicator => {
                indicator.style.opacity = '0.5';
                setTimeout(() => {
                    indicator.style.opacity = '1';
                }, 500);
            });
        }, 30000); // Every 30 seconds
    });
</script>
@endpush

@section('javascript')
<script>
    // Page-specific JavaScript can go here
    console.log('Example dashboard JavaScript loaded');
</script>
@endsection
