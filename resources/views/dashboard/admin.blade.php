@extends('layouts.modern')

@section('title', 'Admin Dashboard')
@section('description', 'Sports Ticket Management - Complete platform overview and control')

@push('styles')
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
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
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

.loading-shimmer {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
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
                    <p class="text-white/90 text-lg">Sports Ticket Management Dashboard</p>
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-3xl font-bold mb-1">Welcome back, {{ Auth::user()->name }}!</h3>
                                <p class="text-blue-100 text-lg">Sports Ticket Platform Administrator</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-6 text-sm">
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
                    
                    <div class="text-right">
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-4 min-w-[200px]">
                            <div class="text-sm text-blue-100 mb-2">Platform Health</div>
                            <div class="flex items-center justify-center mb-3">
                                <div class="relative w-16 h-16">
                                    <svg class="w-16 h-16 transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-blue-200" stroke="currentColor" stroke-width="3" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                        <path class="text-green-400" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="98, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-xl font-bold" id="systemHealth">98%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-center">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                <span class="text-xs text-green-200">All Systems Operational</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Scraped Tickets -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-blue-400 to-blue-600 rounded-bl-full opacity-10"></div>
                        <div class="flex items-center relative z-10">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-600 mb-1" title="Total tickets collected from all platforms">Scraped Tickets</div>
                            <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $scrapedTickets ?? 0 }}" title="{{ number_format($scrapedTickets ?? 0) }} tickets collected">{{ number_format($scrapedTickets ?? 0) }}</div>
                                <div class="text-xs text-green-500 font-medium flex items-center" title="Growth compared to last week">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"></path>
                                    </svg>
                                    +12% from last week
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Monitoring -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-bl-full opacity-10"></div>
                        <div class="flex items-center relative z-10">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-600 mb-1">Active Monitors</div>
                            <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $activeMonitors ?? 0 }}">{{ number_format($activeMonitors ?? 0) }}</div>
                                <div class="text-xs text-blue-500 font-medium flex items-center">
                                    <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                    Real-time monitoring
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Premium Tickets Found -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-red-400 to-pink-500 rounded-bl-full opacity-10"></div>
                        <div class="flex items-center relative z-10">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-600 mb-1">Premium Tickets</div>
                            <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $premiumTickets ?? 0 }}">{{ number_format($premiumTickets ?? 0) }}</div>
                                <div class="text-xs text-orange-500 font-medium flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    High-value events
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Users -->
                <div class="bg-white overflow-hidden shadow-lg rounded-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100">
                    <div class="p-6 relative">
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-br from-green-400 to-emerald-500 rounded-bl-full opacity-10"></div>
                        <div class="flex items-center relative z-10">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-sm font-medium text-gray-600 mb-1">Platform Users</div>
                            <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $totalUsers ?? 0 }}">{{ number_format($totalUsers ?? 0) }}</div>
                                <div class="text-xs text-green-500 font-medium flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    +5% this month
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Management Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @if(Auth::user()->canManageUsers())
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 p-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                        <div class="text-2xl font-bold text-blue-900">{{ number_format($totalUsers ?? 0) }}</div>
                            <div class="text-xs text-blue-600">Total Users</div>
                        </div>
                    </div>
                    <h4 class="font-bold text-xl text-blue-900 mb-2">User Management</h4>
                    <p class="text-blue-700 text-sm mb-4">Manage users, roles, and permissions across the platform</p>
                    
                    <!-- User Statistics -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 text-center">
                            <div class="text-lg font-bold text-green-600">{{ number_format($totalAgents ?? 0) }}</div>
                            <div class="text-xs text-gray-600">Agents</div>
                        </div>
                        <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 text-center">
                            <div class="text-lg font-bold text-purple-600">{{ number_format($totalCustomers ?? 0) }}</div>
                            <div class="text-xs text-gray-600">Customers</div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="space-y-2">
                        <a href="{{ route('admin.users.index') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 text-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Manage All Users
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.users.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition-colors duration-200 text-center">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add User
                            </a>
                            <a href="{{ route('admin.users.roles') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-md text-xs font-medium transition-colors duration-200 text-center">
                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Roles
                            </a>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="bg-green-50 border border-green-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-green-900 mb-2">Category Management</h4>
                    <p class="text-green-700 text-sm mb-4">Organize tickets with categories</p>
                    <div class="text-xs text-green-600 mb-3">
                        Active Categories: {{ number_format($totalCategories ?? 0) }}
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-green-700">Manage Categories</a>
                </div>
                
                <div class="bg-purple-50 border border-purple-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-purple-900 mb-2">System Settings</h4>
                    <p class="text-purple-700 text-sm mb-4">Configure system preferences</p>
                    <div class="text-xs text-purple-600 mb-3">
                        Email, notifications, and more
                    </div>
                    <a href="#" class="inline-block bg-purple-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-purple-700">System Settings</a>
                </div>

                <div class="bg-indigo-50 border border-indigo-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-indigo-900 mb-2">Scraping Management</h4>
                    <p class="text-indigo-700 text-sm mb-4">Monitor and control ticket scraping</p>
                    <div class="text-xs text-indigo-600 mb-3">
                        Platform monitoring, performance metrics
                    </div>
                    <a href="{{ route('admin.scraping.index') }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700">Manage Scraping</a>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-yellow-900 mb-2">System Management</h4>
                    <p class="text-yellow-700 text-sm mb-4">System health and configuration</p>
                    <div class="text-xs text-yellow-600 mb-3">
                        Health monitoring, logs, cache management
                    </div>
                    <a href="{{ route('admin.system.index') }}" class="inline-block bg-yellow-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-yellow-700">System Settings</a>
                </div>

                <div class="bg-red-50 border border-red-200 p-6 rounded-lg">
                    <h4 class="font-semibold text-red-900 mb-2">API Integration</h4>
                    <p class="text-red-700 text-sm mb-4">Connect to ticket platforms</p>
                    <div class="text-xs text-red-600 mb-3">
                        Ticketmaster, SeatGeek, and more
                    </div>
                    <a href="{{ route('ticket-api.index') }}" class="inline-block bg-red-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-red-700">Manage APIs</a>
                </div>
            </div>

            <!-- Role Distribution Chart -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">User Role Distribution</h3>
                            <p class="text-sm text-gray-600 mt-1">Breakdown of users by role</p>
                        </div>
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if(isset($userStats['by_role']))
                            @foreach($userStats['by_role'] as $role => $count)
                                @php
                                    $roleColors = [
                                        'admin' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'text-red-600'],
                                        'agent' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'text-blue-600'],
                                        'customer' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'text-green-600'],
                                        'scraper' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'text-yellow-600']
                                    ];
                                    $colors = $roleColors[$role] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'text-gray-600'];
                                @endphp
                                <div class="{{ $colors['bg'] }} rounded-lg p-4 text-center">
                                    <div class="w-8 h-8 mx-auto mb-2 {{ $colors['icon'] }}">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="text-2xl font-bold {{ $colors['text'] }}">{{ number_format($count) }}</div>
                                    <div class="text-sm {{ $colors['text'] }} capitalize">{{ $role }}s</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <x-dashboard.quick-actions :actions="$quickActions" />

            <!-- Enhanced Analytics Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Real-time Scraping Statistics -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Real-time Scraping Statistics</h3>
                                <p class="text-sm text-gray-600 mt-1">Live ticket scraping performance</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-700" id="scrapedToday">0</div>
                                <div class="text-sm text-green-600">Tickets Scraped Today</div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-700" id="activeScrapers">0</div>
                                <div class="text-sm text-blue-600">Active Scrapers</div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Scraping Rate</span>
                                <span class="text-sm font-medium text-gray-900" id="scrapingRate">0 tickets/min</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Success Rate</span>
                                <span class="text-sm font-medium text-green-600" id="successRate">0%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Error Rate</span>
                                <span class="text-sm font-medium text-red-600" id="errorRate">0%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Platform Performance Metrics -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-violet-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Platform Performance</h3>
                                <p class="text-sm text-gray-600 mt-1">Ticketmaster, StubHub, SeatGeek metrics</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4" id="platformMetrics">
                            <!-- Platform metrics will be loaded via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics and User Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Analytics -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-green-50 to-emerald-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Revenue Analytics</h3>
                                <p class="text-sm text-gray-600 mt-1">Pricing and revenue insights</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-yellow-700" id="totalRevenue">$0</div>
                                <div class="text-sm text-yellow-600">Total Revenue</div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-700" id="avgTicketPrice">$0</div>
                                <div class="text-sm text-green-600">Avg Ticket Price</div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Premium Events</span>
                                <span class="text-sm font-medium text-gray-900" id="premiumEvents">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Price Range</span>
                                <span class="text-sm font-medium text-gray-900" id="priceRange">$0 - $0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Activity Heatmap -->
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-orange-50 to-red-50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">User Activity Heatmap</h3>
                                <p class="text-sm text-gray-600 mt-1">Peak usage times and patterns</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-3 gap-2 mb-6" id="activityHeatmap">
                            <!-- Heatmap will be generated via JavaScript -->
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Peak Hours</span>
                                <span class="text-sm font-medium text-gray-900" id="peakHours">9 AM - 11 AM</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Active Users Now</span>
                                <span class="text-sm font-medium text-green-600" id="activeUsersNow">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alert Management System -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-red-50 to-pink-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Alert Management System</h3>
                            <p class="text-sm text-gray-600 mt-1">Monitor system health and triggers</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="refreshAlerts()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh Alerts
                            </button>
                            <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.996-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-green-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-green-700" id="activeAlerts">0</div>
                            <div class="text-sm text-green-600">Active Alerts</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-yellow-700" id="warningAlerts">0</div>
                            <div class="text-sm text-yellow-600">Warnings</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-red-700" id="criticalAlerts">0</div>
                            <div class="text-sm text-red-600">Critical</div>
                        </div>
                    </div>
                    <div class="space-y-3" id="alertsList">
                        <!-- Alert list will be populated via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            @if($recentActivity->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Activity</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentActivity as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $activity['user'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $activity['description'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($activity['timestamp'])->diffForHumans() }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <script>
        function refreshDashboard() {
            // Show loading state
            const refreshBtn = document.querySelector('button[onclick="refreshDashboard()"]');
            const refreshSpinner = document.getElementById('refreshSpinner');
            const originalContent = refreshBtn.innerHTML;
            
            // Show spinner and update button text
            refreshSpinner.classList.remove('hidden');
            refreshBtn.innerHTML = '<svg class="w-5 h-5 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="refreshSpinner"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><span class="hidden sm:inline">Refreshing...</span>';
            refreshBtn.disabled = true;
            
            // Add shimmer effect to stats cards
            const statCards = document.querySelectorAll('[data-counter]');
            statCards.forEach(card => {
                card.classList.add('loading-shimmer');
            });
            
            // Simulate API call and reload
            setTimeout(() => {
                location.reload();
            }, 800);
        }
        
        // Function to refresh alerts
        function refreshAlerts() {
            const alertsContainer = document.getElementById('alertsList');
            alertsContainer.innerHTML = '<div class="text-center py-3"><i class="fas fa-spinner fa-spin text-gray-400"></i> Loading alerts...</div>';
            
            // Simulate API call
            setTimeout(() => {
                alertsContainer.innerHTML = '<div class="text-center py-3 text-gray-500">No active alerts</div>';
            }, 1000);
        }

        // Counter animation function
        function animateCounter(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16); // 60 FPS
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current).toLocaleString();
            }, 16);
        }

        // Update time displays
        function updateTimeDisplays() {
            const now = new Date();
            const lastUpdatedElement = document.getElementById('lastUpdated');
            const currentTimeElement = document.getElementById('currentTime');
            
            if (lastUpdatedElement) {
                lastUpdatedElement.textContent = now.toLocaleTimeString();
            }
            if (currentTimeElement) {
                currentTimeElement.textContent = now.toLocaleTimeString();
            }
        }

        // Update system health with smooth animation
        function updateSystemHealth() {
            const healthElement = document.getElementById('systemHealth');
            if (healthElement) {
                const currentHealth = parseInt(healthElement.textContent);
                const change = Math.random() > 0.5 ? 1 : -1;
                const newHealth = Math.max(85, Math.min(100, currentHealth + change));
                
                // Animate the change
                healthElement.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    healthElement.textContent = newHealth + '%';
                    healthElement.style.transform = 'scale(1)';
                }, 150);
                
                // Update the circle stroke-dasharray
                const circle = document.querySelector('path[stroke-dasharray]');
                if (circle) {
                    circle.setAttribute('stroke-dasharray', newHealth + ', 100');
                }
            }
        }

        // Add hover effects to cards
        function addCardHoverEffects() {
            const cards = document.querySelectorAll('.transform.hover\\:-translate-y-1');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Animate counters on page load
            const counterElements = document.querySelectorAll('[data-counter]');
            counterElements.forEach(element => {
                const target = parseInt(element.getAttribute('data-counter')) || 0;
                animateCounter(element, target);
            });

            // Add card hover effects
            addCardHoverEffects();

            // Add loading states for buttons
            const buttons = document.querySelectorAll('button, a[href]');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (this.tagName === 'A' && !this.onclick) {
                        // Add loading state to navigation links
                        this.style.opacity = '0.7';
                        this.style.pointerEvents = 'none';
                        
                        setTimeout(() => {
                            this.style.opacity = '1';
                            this.style.pointerEvents = 'auto';
                        }, 1000);
                    }
                });
            });

            // Add click animation to management cards
            const managementCards = document.querySelectorAll('.bg-blue-50, .bg-green-50, .bg-purple-50, .bg-indigo-50, .bg-yellow-50, .bg-red-50');
            managementCards.forEach(card => {
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
        });

        // Auto-refresh time displays every second
        setInterval(updateTimeDisplays, 1000);

        // Update system health every 5 seconds
        setInterval(updateSystemHealth, 5000);

        // Add page visibility API to pause animations when tab is not active
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pause animations when tab is hidden
                document.body.style.animationPlayState = 'paused';
            } else {
                // Resume animations when tab becomes visible
                document.body.style.animationPlayState = 'running';
                // Refresh counters
                const counterElements = document.querySelectorAll('[data-counter]');
                counterElements.forEach(element => {
                    const target = parseInt(element.getAttribute('data-counter')) || 0;
                    element.textContent = target.toLocaleString();
                });
            }
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + R for refresh
            if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                e.preventDefault();
                refreshDashboard();
            }
            
            // F5 for refresh
            if (e.key === 'F5') {
                e.preventDefault();
                refreshDashboard();
            }
        });

        // Add smooth scrolling for better UX
        document.documentElement.style.scrollBehavior = 'smooth';
        
        // Performance monitoring
        window.addEventListener('load', function() {
            const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
            console.log('Dashboard loaded in:', loadTime + 'ms');
        });
    </script>
@endsection
