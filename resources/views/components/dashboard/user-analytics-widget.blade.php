@props(['users', 'userStats', 'timeframe' => '30d'])

<div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
    <!-- Widget Header -->
    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    User Analytics
                </h3>
                <p class="text-sm text-gray-600 mt-1">User engagement and activity insights</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="text-xs text-gray-500">Last {{ $timeframe }}</div>
                <button onclick="refreshUserAnalytics()" class="p-2 text-gray-400 hover:text-gray-600 transition-colors duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Widget Content -->
    <div class="p-6">
        <!-- Key Metrics Row -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-700">{{ number_format($userStats['total_users'] ?? 0) }}</div>
                <div class="text-sm text-blue-600">Total Users</div>
                @if(isset($userStats['growth_rate']))
                    <div class="text-xs text-blue-500 mt-1">
                        @if($userStats['growth_rate'] > 0)
                            <span class="text-green-600">↗ +{{ $userStats['growth_rate'] }}%</span>
                        @else
                            <span class="text-red-600">↘ {{ $userStats['growth_rate'] }}%</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ number_format($userStats['active_users'] ?? 0) }}</div>
                <div class="text-sm text-green-600">Active Users</div>
                <div class="text-xs text-green-500 mt-1">
                    {{ $userStats['total_users'] > 0 ? round(($userStats['active_users'] / $userStats['total_users']) * 100, 1) : 0 }}% active
                </div>
            </div>

            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700">{{ number_format($userStats['new_users_this_month'] ?? 0) }}</div>
                <div class="text-sm text-yellow-600">New This Month</div>
                <div class="text-xs text-yellow-500 mt-1">{{ $userStats['avg_daily_signups'] ?? 0 }}/day avg</div>
            </div>

            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-700">{{ $userStats['engagement_score'] ?? 0 }}%</div>
                <div class="text-sm text-purple-600">Engagement</div>
                <div class="text-xs text-purple-500 mt-1">
                    @if(($userStats['engagement_score'] ?? 0) > 75)
                        <span class="text-green-600">Excellent</span>
                    @elseif(($userStats['engagement_score'] ?? 0) > 50)
                        <span class="text-yellow-600">Good</span>
                    @else
                        <span class="text-red-600">Needs Attention</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Role Distribution Chart -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Role Distribution</h4>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach(['admin', 'agent', 'customer', 'scraper'] as $role)
                    @php
                        $count = $userStats['by_role'][$role] ?? 0;
                        $percentage = $userStats['total_users'] > 0 ? round(($count / $userStats['total_users']) * 100, 1) : 0;
                        $colors = [
                            'admin' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'border' => 'border-red-200'],
                            'agent' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-200'],
                            'customer' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'border' => 'border-green-200'],
                            'scraper' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'border' => 'border-gray-200']
                        ];
                        $color = $colors[$role];
                    @endphp
                    <div class="border {{ $color['border'] }} rounded-lg p-3 {{ $color['bg'] }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium {{ $color['text'] }} capitalize">{{ $role }}s</div>
                                <div class="text-lg font-bold {{ $color['text'] }}">{{ $count }}</div>
                            </div>
                            <div class="text-xs {{ $color['text'] }}">{{ $percentage }}%</div>
                        </div>
                        <div class="mt-2 bg-white bg-opacity-50 rounded-full h-2">
                            <div class="h-2 rounded-full {{ str_replace('100', '500', $color['bg']) }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- User Activity Timeline -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Recent User Activity</h4>
            <div class="space-y-3 max-h-48 overflow-y-auto">
                @if(isset($userStats['recent_activities']) && count($userStats['recent_activities']) > 0)
                    @foreach($userStats['recent_activities'] as $activity)
                        <div class="flex items-center space-x-3 p-2 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($activity['user_name'] ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900">{{ $activity['user_name'] ?? 'Unknown User' }}</div>
                                <div class="text-xs text-gray-500">{{ $activity['action'] ?? 'Performed action' }}</div>
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ isset($activity['created_at']) ? \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() : 'Recent' }}
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-gray-500">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                        </svg>
                        <div class="text-sm">No recent activity</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- User Verification Status -->
        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Verification Status</h4>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $userStats['verified_users'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Verified</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $userStats['total_users'] > 0 ? round(($userStats['verified_users'] / $userStats['total_users']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $userStats['unverified_users'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Unverified</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $userStats['total_users'] > 0 ? round(($userStats['unverified_users'] / $userStats['total_users']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ $userStats['suspended_users'] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Suspended</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $userStats['total_users'] > 0 ? round(($userStats['suspended_users'] / $userStats['total_users']) * 100, 1) : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="border-t border-gray-100 pt-4">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                    </svg>
                    Manage Users
                </a>
                <a href="{{ route('admin.users.roles') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Role Management
                </a>
                <button onclick="openBulkImportModal()" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                    Bulk Import
                </button>
                <a href="{{ route('admin.reports.users.export') }}" class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Data
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="userAnalyticsLoading" class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center">
        <div class="text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
            <div class="text-sm text-gray-600 mt-2">Refreshing data...</div>
        </div>
    </div>
</div>

<script>
function refreshUserAnalytics() {
    const loadingOverlay = document.getElementById('userAnalyticsLoading');
    loadingOverlay.classList.remove('hidden');
    
    // Simulate API call and refresh
    setTimeout(() => {
        loadingOverlay.classList.add('hidden');
        // In real implementation, this would fetch fresh data
        window.location.reload();
    }, 1500);
}

function openBulkImportModal() {
    // This would open a modal for bulk user import
    window.location.href = '{{ route("admin.users.index") }}?action=bulk_import';
}
</script>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
