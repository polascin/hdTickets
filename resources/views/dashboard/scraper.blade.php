<x-dashboard.layout title="Scraper Dashboard - Sports Events Tickets" subtitle="Ticket Scraping & Monitoring Control Panel">
    <x-slot name="headerActions">
        <div class="flex items-center space-x-4">
            <div class="text-sm text-gray-600">
                Last Sync: <span class="text-green-600 font-semibold">{{ now()->format('H:i:s') }}</span>
            </div>
            <button onclick="refreshScraperDashboard()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh
            </button>
        </div>
    </x-slot>

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-green-500 to-teal-600 rounded-xl p-6 text-white mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h3>
                <p class="text-green-100">Sports Events Ticket Scraper • Data Collection & Monitoring</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-green-100 mb-1">Scraper Status</div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-300 rounded-full mr-2 animate-pulse"></div>
                    <span class="text-lg font-bold">Active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Scraper Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <x-dashboard.stat-card 
            title="Tickets Scraped Today" 
            value="{{ number_format($scraperMetrics['tickets_scraped_today']) }}" 
            icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z' /></svg>" 
            color="green" 
        />
        
        <x-dashboard.stat-card 
            title="Active Scraping Jobs" 
            value="{{ $scraperMetrics['active_scraping_jobs'] }}" 
            icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 10V3L4 14h7v7l9-11h-7z' /></svg>" 
            color="orange" 
        />
        
        <x-dashboard.stat-card 
            title="Success Rate" 
            value="{{ $scraperMetrics['success_rate'] }}" 
            icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>" 
            color="blue" 
        />
        
        <x-dashboard.stat-card 
            title="Data Quality Score" 
            value="{{ $scraperMetrics['data_quality_score'] }}" 
            icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' /></svg>" 
            color="purple" 
        />
    </div>

    <!-- Active Scraping Jobs and Platform Status -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Active Scraping Jobs
                </h4>
                <p class="text-sm text-gray-500">{{ count($scrapingJobs['active_jobs']) }} jobs currently running</p>
            </div>
            <div class="p-6">
                @if(count($scrapingJobs['active_jobs']) > 0)
                    <div class="space-y-4">
                        @foreach($scrapingJobs['active_jobs'] as $job)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <h5 class="text-sm font-medium text-gray-900">{{ $job['platform'] }} - {{ $job['event_type'] }}</h5>
                                    <p class="text-xs text-gray-500">Progress: {{ $job['progress'] }}%</p>
                                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $job['progress'] }}%"></div>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">{{ ucfirst($job['status']) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No active scraping jobs</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"></path>
                    </svg>
                    Platform Status
                </h4>
                <p class="text-sm text-gray-500">Real-time monitoring of ticket platforms</p>
            </div>
            <div class="p-6">
                @if(isset($platformData['platform_status']))
                    <div class="space-y-3">
                        @foreach($platformData['platform_status'] as $platform => $status)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <h5 class="text-sm font-medium text-gray-900 capitalize">{{ ucfirst(str_replace('_', ' ', $platform)) }}</h5>
                                    <p class="text-xs text-gray-500">Response: {{ $status['response_time'] }} • Success: {{ $status['success_rate'] }}</p>
                                </div>
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full {{ $status['status'] === 'online' ? 'bg-green-400' : ($status['status'] === 'slow' ? 'bg-yellow-400' : 'bg-red-400') }}"></div>
                                    <span class="ml-2 text-xs font-medium {{ $status['status'] === 'online' ? 'text-green-600' : ($status['status'] === 'slow' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ ucfirst($status['status']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p class="text-sm">Platform status information not available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-green-50 to-emerald-100 border border-green-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer" onclick="window.location.href='{{ route('tickets.scraping.index') }}'">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-green-900">Scraped Tickets</h4>
                    <p class="text-green-700 text-sm">View collected sports event tickets</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer" onclick="window.location.href='{{ route('admin.scraping.index') }}'">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-blue-900">Scraping Settings</h4>
                    <p class="text-blue-700 text-sm">Configure scraping parameters</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-orange-900">Performance Analytics</h4>
                    <p class="text-orange-700 text-sm">View scraping performance data</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h4 class="font-semibold text-purple-900">Job Scheduler</h4>
                    <p class="text-purple-700 text-sm">Manage scraping schedules</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Scraping Activity and Error Analysis -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Recent Activity
                </h4>
                <p class="text-sm text-gray-500">Latest scraping operations and events</p>
            </div>
            <div class="p-6">
                @if(count($recentActivity) > 0)
                    <div class="space-y-4">
                        @foreach(array_slice($recentActivity, 0, 8) as $activity)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ $activity['color'] === 'green' ? 'bg-green-100' : ($activity['color'] === 'red' ? 'bg-red-100' : 'bg-blue-100') }}">
                                        <svg class="w-4 h-4 {{ $activity['color'] === 'green' ? 'text-green-600' : ($activity['color'] === 'red' ? 'text-red-600' : 'text-blue-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if($activity['icon'] === 'ticket')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                            @elseif($activity['icon'] === 'alert')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L5.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"></path>
                                            @endif
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</h5>
                                    <p class="text-xs text-gray-500">{{ $activity['description'] }}</p>
                                    <p class="text-xs text-gray-400">{{ $activity['timestamp']->diffForHumans() }}</p>
                                </div>
                                @if(isset($activity['platform']))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($activity['platform']) }}
                                    </span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L5.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Error Analysis
                </h4>
                <p class="text-sm text-gray-500">Scraping errors and troubleshooting</p>
            </div>
            <div class="p-6">
                @if(isset($performanceData['error_analysis']))
                    <div class="space-y-3">
                        @foreach($performanceData['error_analysis'] as $errorType => $count)
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $errorType) }}</span>
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-red-600">{{ $count }}</span>
                                    <div class="ml-3 w-20 bg-gray-200 rounded-full h-2">
                                        <div class="bg-red-400 h-2 rounded-full" style="width: {{ min(($count / 20) * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-sm">No errors detected today</p>
                        <p class="text-xs text-green-600">Scraping system running smoothly</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Proxy Rotation Health -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
                Anti-Detection Status
            </h4>
            <p class="text-sm text-gray-500">Proxy rotation and stealth techniques</p>
        </div>
        <div class="p-6">
            @if(isset($platformData['anti_detection_status']))
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($platformData['anti_detection_status'] as $technique => $data)
                        <div class="bg-gray-50 rounded-lg p-4 text-center">
                            <div class="w-3 h-3 {{ $data['status'] === 'active' ? 'bg-green-400' : ($data['status'] === 'monitoring' ? 'bg-yellow-400' : 'bg-red-400') }} rounded-full mx-auto mb-2"></div>
                            <h5 class="text-sm font-medium text-gray-900 capitalize mb-1">{{ str_replace('_', ' ', $technique) }}</h5>
                            <p class="text-xs text-gray-500">{{ ucfirst($data['status']) }}</p>
                            @if(isset($data['pool_size']))
                                <p class="text-xs text-gray-400">Pool: {{ $data['pool_size'] }}</p>
                            @elseif(isset($data['active_proxies']))
                                <p class="text-xs text-gray-400">Proxies: {{ $data['active_proxies'] }}</p>
                            @elseif(isset($data['average_delay']))
                                <p class="text-xs text-gray-400">Delay: {{ $data['average_delay'] }}</p>
                            @elseif(isset($data['encounters_today']))
                                <p class="text-xs text-gray-400">Today: {{ $data['encounters_today'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <p class="text-sm">Anti-detection status information not available</p>
                </div>
            @endif
        </div>
    </div>

    <script>
        function refreshScraperDashboard() {
            location.reload();
        }

        // Auto-refresh every 2 minutes for scraper dashboard
        setInterval(refreshScraperDashboard, 120000);
        
        // Add CSS timestamp to prevent caching
        document.addEventListener('DOMContentLoaded', function() {
            const timestamp = new Date().getTime();
            const links = document.querySelectorAll('link[rel="stylesheet"]');
            links.forEach(link => {
                if (!link.href.includes('timestamp=')) {
                    link.href += (link.href.includes('?') ? '&' : '?') + 'timestamp=' + timestamp;
                }
            });
        });

        // Real-time metrics update function
        function updateScrapingMetrics() {
            fetch('/scraper/api/realtime-metrics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update platform status indicators
                        document.querySelectorAll('[data-platform-status]').forEach(element => {
                            const platform = element.dataset.platformStatus;
                            if (data.data.platform_status[platform]) {
                                const status = data.data.platform_status[platform].status;
                                const indicator = element.querySelector('.status-indicator');
                                if (indicator) {
                                    indicator.className = `w-3 h-3 rounded-full ${status === 'online' ? 'bg-green-400' : status === 'slow' ? 'bg-yellow-400' : 'bg-red-400'}`;
                                }
                            }
                        });
                        
                        console.log('Scraper metrics updated:', data.timestamp);
                    }
                })
                .catch(error => {
                    console.error('Error updating scraper metrics:', error);
                });
        }

        // Update metrics every 30 seconds
        setInterval(updateScrapingMetrics, 30000);
    </script>
</x-dashboard.layout>
