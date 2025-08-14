@extends('layouts.modern')

@section('title', 'Admin Dashboard')
@section('description', 'Sports Events Tickets Admin - Complete platform overview and management')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}?t={{ filemtime(public_path('css/admin-dashboard.css')) ?? time() }}" />
<style>
/* Enhanced Admin Dashboard Styles */
.admin-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.admin-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    opacity: 0.1;
}

.stat-card-enhanced {
    background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card-enhanced:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.metric-ring {
    transform: rotate(-90deg);
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.pulse-dot {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.card-hover-effect {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover-effect:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.status-indicator {
    position: relative;
}

.status-indicator::after {
    content: '';
    position: absolute;
    top: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    border: 2px solid white;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.status-online::after {
    background-color: #10b981;
}

.status-warning::after {
    background-color: #f59e0b;
}

.status-error::after {
    background-color: #ef4444;
}
</style>
@endpush

@section('header')
    <div class="admin-header text-white py-8 px-6 rounded-2xl mb-6 relative z-10">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <div class="flex items-center space-x-4">
                <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm border border-white/30 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-bold leading-tight mb-2">
                        Welcome back, {{ Auth::user()->name }}!
                    </h1>
                    <p class="text-white/90 text-lg">Sports Events Tickets Admin Dashboard</p>
                    <div class="flex items-center space-x-6 mt-3 text-sm text-white/80">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4h3a1 1 0 011 1v9a1 1 0 01-1 1H5a1 1 0 01-1-1V8a1 1 0 011-1h3z"></path>
                            </svg>
                            {{ now()->format('l, F j, Y') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span id="currentTime">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="stat-card-enhanced rounded-2xl p-6 min-w-[200px] text-center">
                    <div class="text-white/80 text-sm mb-2">System Health</div>
                    <div class="relative w-20 h-20 mx-auto mb-3">
                        <svg class="metric-ring w-20 h-20" viewBox="0 0 36 36">
                            <path class="text-white/20" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                            <path class="text-green-400" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="98, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold text-white" id="systemHealth">98%</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-center">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 pulse-dot"></div>
                        <span class="text-xs text-white/90">All Systems Operational</span>
                    </div>
                </div>
                
                <button onclick="refreshDashboard()" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-6 py-3 text-white rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl" title="Refresh the dashboard">
                    <svg class="w-5 h-5 inline mr-2 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="refreshSpinner">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                    </svg>
                    <span class="hidden sm:inline">Refresh</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('content')

@php
    // Admin Dashboard Data
    $totalTickets = \App\Models\ScrapedTicket::count();
    $activeUsers = \App\Models\User::where('last_activity', '>=', now()->subHours(24))->count();
    $totalUsers = \App\Models\User::count();
    $scraperJobs = \App\Models\ScrapingJob::where('status', 'running')->count() ?? 0;
    $successfulPurchases = \App\Models\Purchase::where('status', 'completed')
        ->whereDate('created_at', today())->count() ?? 0;
    $platformHealth = 98; // This should come from your system monitoring
@endphp

<div class="py-6 space-y-6">
    <!-- Enhanced System Health Banner -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl p-8 text-white shadow-2xl relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-0 left-0 w-full h-full">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100" height="100" fill="url(#grid)"/>
                </svg>
            </div>
        </div>
        
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex-1">
                <div class="flex items-center mb-3">
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold mb-1">Sports Events Tickets Platform</h3>
                        <p class="text-blue-100 text-lg">Comprehensive Monitoring & Management System</p>
                    </div>
                </div>
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                        {{ number_format($activeUsers) }} Active Users
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                        {{ number_format($totalTickets) }} Sports Event Tickets
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        {{ $scraperJobs }} Active Jobs
                    </div>
                </div>
            </div>
            
            <div class="text-right">
                <div class="text-blue-100 text-sm mb-1">Platform Status</div>
                <div class="flex items-center justify-end">
                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                    <span class="text-lg font-bold">Operational</span>
                </div>
                <div class="text-blue-100 text-xs mt-1">Last updated: {{ now()->format('H:i:s') }}</div>
            </div>
        </div>
    </div>

    <!-- Admin Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Users -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Total Users</h3>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalUsers) }}</div>
                    <div class="text-xs text-green-600">{{ number_format($activeUsers) }} active today</div>
                </div>
            </div>
        </div>

        <!-- Total Tickets -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Sports Event Tickets</h3>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($totalTickets) }}</div>
                    <div class="text-xs text-blue-600">All monitored tickets</div>
                </div>
            </div>
        </div>

        <!-- Active Scraping Jobs -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Active Scraping Jobs</h3>
                    <div class="text-2xl font-bold text-gray-900">{{ $scraperJobs }}</div>
                    <div class="text-xs text-orange-600">Currently running</div>
                </div>
            </div>
        </div>

        <!-- Daily Purchases -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-600">Purchases Today</h3>
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($successfulPurchases) }}</div>
                    <div class="text-xs text-purple-600">Successful transactions</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Admin Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <!-- User Management -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect cursor-pointer" onclick="window.location.href='{{ route('admin.users.index') }}'">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">User Management</h4>
                <p class="text-sm text-gray-600">Manage users and permissions</p>
            </div>
        </div>

        <!-- Ticket Categories -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect cursor-pointer" onclick="window.location.href='{{ route('admin.categories.index') }}'">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Ticket Categories</h4>
                <p class="text-sm text-gray-600">Sports event categories</p>
            </div>
        </div>

        <!-- Scraping Management -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect cursor-pointer" onclick="window.location.href='{{ route('admin.scraping.index') }}'">
            <div class="text-center">
                <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Scraping Control</h4>
                <p class="text-sm text-gray-600">Monitor scraping jobs</p>
            </div>
        </div>

        <!-- Platform Analytics -->
        <div class="stat-card-enhanced p-6 rounded-2xl card-hover-effect cursor-pointer" onclick="window.location.href='{{ route('admin.reports.index') }}'">
            <div class="text-center">
                <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h4 class="text-lg font-semibold text-gray-900 mb-2">Analytics</h4>
                <p class="text-sm text-gray-600">Platform performance reports</p>
            </div>
        </div>
    </div>

    <!-- System Status Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Activity -->
        <div class="stat-card-enhanced p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Recent Admin Activity</h3>
                <a href="{{ route('admin.activity-logs.index') }}" class="text-blue-600 hover:text-blue-700 text-sm">View All</a>
            </div>
            <div class="space-y-4">
                @for($i = 1; $i <= 5; $i++)
                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">System Activity {{ $i }}</h4>
                        <p class="text-xs text-gray-500">Sports ticket monitoring update</p>
                        <p class="text-xs text-gray-400">{{ now()->subMinutes($i * 15)->diffForHumans() }}</p>
                    </div>
                </div>
                @endfor
            </div>
        </div>

        <!-- Platform Health -->
        <div class="stat-card-enhanced p-6 rounded-2xl">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Platform Health Status</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Healthy</span>
                </div>
            </div>
            <div class="space-y-4">
                <!-- Database -->
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                        <span class="text-sm font-medium">Database</span>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Online</span>
                </div>

                <!-- Scraping Services -->
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <span class="text-sm font-medium">Scraping Services</span>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>
                </div>

                <!-- API Services -->
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                        <span class="text-sm font-medium">API Services</span>
                    </div>
                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Online</span>
                </div>

                <!-- Queue System -->
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-medium">Queue System</span>
                    </div>
                    <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Processing</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Real-time clock update
function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', { hour12: false });
    document.getElementById('currentTime').textContent = timeString;
}

// Update time every second
setInterval(updateTime, 1000);

// Dashboard refresh function
function refreshDashboard() {
    const spinner = document.getElementById('refreshSpinner');
    spinner.classList.remove('hidden');
    
    // Simulate refresh (in real implementation, this would reload data)
    setTimeout(() => {
        spinner.classList.add('hidden');
        location.reload();
    }, 1000);
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    updateTime();
});
</script>
@endpush

@endsection
