<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Customer Portal') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Manage your support requests and get help</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Quick Stats -->
                @php
                    $userTicketsCount = Auth::user()->tickets()->count();
                    $openTicketsCount = Auth::user()->tickets()->open()->count();
                @endphp
                <div class="text-sm text-gray-600">
                    {{ $userTicketsCount }} Total • {{ $openTicketsCount }} Open
                </div>
                <!-- New Ticket Button -->
                <a href="{{ route('tickets.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Ticket
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Welcome Banner -->
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-2">Hello, {{ Auth::user()->name }}! 👋</h3>
                        <p class="text-blue-100">Customer Portal • We're here to help you succeed</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-blue-100 mb-1">Account Status</div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                            <span class="text-lg font-bold">{{ Auth::user()->email_verified_at ? 'Verified' : 'Pending' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            @php
                $totalTickets = Auth::user()->tickets()->count();
                $openTickets = Auth::user()->tickets()->open()->count();
                $resolvedTickets = Auth::user()->tickets()->where('status', 'resolved')->count();
                $recentTickets = Auth::user()->tickets()->orderBy('created_at', 'desc')->limit(3)->get();
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Tickets -->
                <div class="dashboard-card">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Tickets</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalTickets }}</div>
                        </div>
                    </div>
                </div>

                <!-- Open Tickets -->
                <div class="dashboard-card">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Open Tickets</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $openTickets }}</div>
                        </div>
                    </div>
                </div>

                <!-- Resolved Tickets -->
                <div class="dashboard-card">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Resolved</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $resolvedTickets }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="dashboard-card mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Quick Actions</h3>
                        <p class="text-gray-600 mt-1">Common tasks to help you get started</p>
                    </div>
                </div>
                    
                    <!-- Quick Actions Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="group bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-xl border border-blue-200 hover:shadow-lg transition-all duration-200 cursor-pointer" onclick="window.location.href='{{ route('tickets.index') }}'">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-blue-900">My Tickets</h4>
                                    <p class="text-blue-700 text-sm">View and track your support tickets</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-200 cursor-pointer" onclick="window.location.href='{{ route('tickets.create') }}'">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-green-900">New Ticket</h4>
                                    <p class="text-green-700 text-sm">Submit a new support request</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="group bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-200 cursor-pointer" onclick="window.location.href='/knowledge-base'">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-purple-900">Knowledge Base</h4>
                                    <p class="text-purple-700 text-sm">Browse help articles and guides</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Tickets Section -->
            <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
                <div class="px-6 py-6 sm:px-8">
                    <div class="flex items-center justify-between mb-6">
                        <h4 class="text-xl font-bold text-gray-900">Recent Tickets</h4>
                        <a href="{{ route('tickets.index') }}" class="text-brand-600 hover:text-brand-700 font-medium text-sm flex items-center">
                            View all tickets
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                    
                    @php
                        $recentTickets = Auth::user()->tickets()->latest('last_activity_at')->limit(5)->get();
                    @endphp
                    
                    @if($recentTickets->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentTickets as $ticket)
                                <div class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                    <div class="flex-shrink-0">
                                        <div class="w-3 h-3 rounded-full {{ $ticket->status === 'open' ? 'bg-blue-400' : ($ticket->status === 'in_progress' ? 'bg-yellow-400' : ($ticket->status === 'resolved' ? 'bg-green-400' : 'bg-gray-400')) }}"></div>
                                    </div>
                                    <div class="ml-4 flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-brand-600">
                                                        #{{ $ticket->id }} - {{ $ticket->title }}
                                                    </a>
                                                </p>
                                                <p class="text-sm text-gray-500">{{ $ticket->created_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority === 'urgent' ? 'bg-red-100 text-red-800' : ($ticket->priority === 'high' ? 'bg-orange-100 text-orange-800' : ($ticket->priority === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucfirst($ticket->priority) }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status === 'open' ? 'bg-blue-100 text-blue-800' : ($ticket->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : ($ticket->status === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')) }}">
                                                    {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No tickets yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating your first support ticket.</p>
                            <div class="mt-6">
                                <a href="{{ route('tickets.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Create New Ticket
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
