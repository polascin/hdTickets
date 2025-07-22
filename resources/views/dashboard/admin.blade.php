<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Sports Ticket Management Dashboard') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Complete sports ticket platform overview and control</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="text-sm text-gray-600">
                    Last updated: <span id="lastUpdated">{{ now()->format('H:i:s') }}</span>
                </div>
                <button onclick="refreshDashboard()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
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
                                <div class="text-sm font-medium text-gray-600 mb-1">Scraped Tickets</div>
                                <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $scrapedTickets ?? 0 }}">0</div>
                                <div class="text-xs text-green-500 font-medium flex items-center">
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
                                <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $activeMonitors ?? 0 }}">0</div>
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
                                <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $premiumTickets ?? 0 }}">0</div>
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
                                <div class="text-3xl font-bold text-gray-900 mb-1" data-counter="{{ $totalUsers ?? 0 }}">0</div>
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
                            <div class="text-2xl font-bold text-blue-900">{{ $totalUsers }}</div>
                            <div class="text-xs text-blue-600">Total Users</div>
                        </div>
                    </div>
                    <h4 class="font-bold text-xl text-blue-900 mb-2">User Management</h4>
                    <p class="text-blue-700 text-sm mb-4">Manage users, roles, and permissions across the platform</p>
                    
                    <!-- User Statistics -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 text-center">
                            <div class="text-lg font-bold text-green-600">{{ $totalAgents }}</div>
                            <div class="text-xs text-gray-600">Agents</div>
                        </div>
                        <div class="bg-white/60 backdrop-blur-sm rounded-lg p-3 text-center">
                            <div class="text-lg font-bold text-purple-600">{{ $totalCustomers }}</div>
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
                        Active Categories: {{ $totalCategories }}
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

            <!-- Recent Activity -->
            @if($recentTickets->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Tickets</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentTickets as $ticket)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $ticket->id }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($ticket->title, 30) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $ticket->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $ticket->category->name ?? 'Uncategorized' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $ticket->status_color }}-100 text-{{ $ticket->status_color }}-800">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-{{ $ticket->priority_color }}-100 text-{{ $ticket->priority_color }}-800">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $ticket->assignedTo->name ?? 'Unassigned' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->diffForHumans() }}
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
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C6.477 0 0 6.477 0 12h4z"></path></svg>Refreshing...';
            
            // Simulate loading delay
            setTimeout(() => {
                location.reload();
            }, 500);
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
</x-app-layout>
