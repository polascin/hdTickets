<x-dashboard.layout title="Agent Dashboard" subtitle="Support Agent Control Panel">
    <x-slot name="headerActions">
        <div class="text-sm text-gray-600">
            Online: <span class="text-green-600 font-semibold">{{ now()->format('H:i:s') }}</span>
        </div>
        <button onclick="refreshAgentDashboard()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh
        </button>
    </x-slot>

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-orange-400 to-red-500 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h3>
                <p class="text-orange-100">Support Agent • Ready to help customers</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-orange-100 mb-1">Status</div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                    <span class="text-lg font-bold">Active</span>
                </div>
            </div>
        </div>
    </div>

            <!-- Stats Cards -->
            @php
                $myTickets = Auth::user()->assignedTickets()->count();
                $pendingTickets = Auth::user()->assignedTickets()->where('status', 'pending')->count();
                $todayResolved = Auth::user()->assignedTickets()->where('status', 'resolved')->whereDate('resolved_at', today())->count();
                $totalResolved = Auth::user()->assignedTickets()->where('status', 'resolved')->count();
            @endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-dashboard.stat-card title="My Tickets" value="{{ $myTickets }}" icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2' /></svg>" color="blue" />
                <!-- My Assigned Tickets -->
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
                            <div class="text-sm font-medium text-gray-500">My Tickets</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $myTickets }}</div>
                        </div>
                    </div>
                </div>

                <!-- Pending Response -->
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
                            <div class="text-sm font-medium text-gray-500">Pending Response</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $pendingTickets }}</div>
                        </div>
                    </div>
                </div>

                <!-- Resolved Today -->
<x-dashboard.stat-card title="Pending Response" value="{{ $pendingTickets }}" icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>" color="yellow" />
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Resolved Today</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $todayResolved }}</div>
                        </div>
                    </div>
                </div>

                <!-- Total Resolved -->
<x-dashboard.stat-card title="Total Resolved" value="{{ $totalResolved }}" icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 10V3L4 14h7v7l9-11h-7z' /></svg>" color="purple" />
 value="{{ $todayResolved }}" icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>" color="green" />
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-500">Total Resolved</div>
                            <div class="text-2xl font-bold text-gray-900">{{ $totalResolved }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer" onclick="window.location.href='{{ route('tickets.index') }}'">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-blue-900">My Ticket Queue</h4>
                            <p class="text-blue-700 text-sm">View and manage assigned tickets</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer" onclick="window.location.href='#'">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-green-900">Knowledge Base</h4>
                            <p class="text-green-700 text-sm">Access support articles and guides</p>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer" onclick="window.location.href='#'">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-purple-900">My Performance</h4>
                            <p class="text-purple-700 text-sm">View your response and resolution stats</p>
                        </div>
                    </div>
                </div>
            </div>

    <script>
        function refreshAgentDashboard() {
            location.reload();
        }

        // Auto-refresh every 5 minutes
        setInterval(refreshAgentDashboard, 300000);
    </script>
</x-app-layout>
