<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center header-mobile">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl md:text-2xl text-gray-800 leading-tight">
                        {{ __('Advanced Scraping Management') }}
                    </h2>
                    <p class="text-sm text-gray-600">Monitor anti-detection systems and high-demand ticket scraping</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <button id="testAntiDetectionBtn" class="button-mobile inline-flex items-center px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 tap-target">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Test Anti-Detection
                </button>
                <button id="testHighDemandBtn" class="button-mobile inline-flex items-center px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 tap-target">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Test High-Demand
                </button>
                <button id="refreshBtn" class="button-mobile inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 tap-target">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh Data
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">

        <!-- Scraping Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Total Operations (24h)</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="totalOperations">{{ $stats['total_operations'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Success Rate</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="successRate">{{ $stats['success_rate'] ?? 0 }}%</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Avg Response Time</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="avgResponseTime">{{ $stats['avg_response_time'] ?? 0 }}ms</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-sm font-medium text-gray-500">Active Users</h3>
                        <p class="text-2xl font-semibold text-gray-900" id="activeUsers">{{ $userRotationStats['active_users'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Anti-Detection Capabilities -->
        <div class="mb-8">
            <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg shadow-lg border border-purple-200">
                <div class="px-6 py-4 border-b border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-purple-900 flex items-center">
                                <svg class="w-6 h-6 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Advanced Anti-Detection Systems
                            </h3>
                            <p class="text-sm text-purple-700">Real-time monitoring of advanced scraping protection systems</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                                Systems Active
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <!-- Anti-Detection Operations -->
                        <div class="bg-white rounded-lg p-4 shadow-md border border-red-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="p-2 bg-red-100 rounded-lg">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 bg-red-100 text-red-700 rounded-full">
                                    24H
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Anti-Detection Operations</h4>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $advancedStats['anti_detection_operations'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">{{ number_format((($advancedStats['anti_detection_operations'] ?? 0) / max($stats['total_operations'] ?? 1, 1)) * 100, 1) }}% of total</p>
                        </div>

                        <!-- High-Demand Scraping -->
                        <div class="bg-white rounded-lg p-4 shadow-md border border-orange-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="p-2 bg-orange-100 rounded-lg">
                                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 bg-orange-100 text-orange-700 rounded-full">
                                    PRIORITY
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">High-Demand Sessions</h4>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $advancedStats['high_demand_sessions'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Avg: {{ $advancedStats['high_demand_avg_time'] ?? 0 }}ms</p>
                        </div>

                        <!-- Success Rate with Protection -->
                        <div class="bg-white rounded-lg p-4 shadow-md border border-green-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="p-2 bg-green-100 rounded-lg">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                    PROTECTED
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Protected Success Rate</h4>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $advancedStats['protected_success_rate'] ?? 0 }}%</p>
                            <p class="text-xs text-gray-500">vs {{ $stats['success_rate'] ?? 0 }}% standard</p>
                        </div>

                        <!-- Threat Detection -->
                        <div class="bg-white rounded-lg p-4 shadow-md border border-yellow-200">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center">
                                    <div class="p-2 bg-yellow-100 rounded-lg">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <span class="text-xs font-semibold px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full">
                                    ALERTS
                                </span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700 mb-1">Threats Detected</h4>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ $advancedStats['threats_detected'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">{{ $advancedStats['threats_blocked'] ?? 0 }} blocked</p>
                        </div>
                    </div>

                    <!-- Advanced Features Status -->
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-white rounded-lg p-4 shadow-md">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Anti-Detection Features
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">User-Agent Rotation</span>
                                    <span class="flex items-center text-sm text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Active
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">IP Rotation</span>
                                    <span class="flex items-center text-sm text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Active
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Browser Fingerprinting</span>
                                    <span class="flex items-center text-sm text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Protected
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Request Timing</span>
                                    <span class="flex items-center text-sm text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Randomized
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white rounded-lg p-4 shadow-md">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                High-Demand Optimizations
                            </h4>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Priority Queue</span>
                                    <span class="flex items-center text-sm text-blue-600">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                        {{ $advancedStats['priority_queue_size'] ?? 0 }} items
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Dedicated Pools</span>
                                    <span class="flex items-center text-sm text-blue-600">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                                        {{ $advancedStats['dedicated_pools'] ?? 0 }} active
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Load Balancing</span>
                                    <span class="flex items-center text-sm text-green-600">
                                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                        Optimized
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600">Cache Hit Rate</span>
                                    <span class="flex items-center text-sm text-purple-600">
                                        <div class="w-2 h-2 bg-purple-500 rounded-full mr-2"></div>
                                        {{ $advancedStats['cache_hit_rate'] ?? 0 }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions for Advanced Features -->
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button onclick="viewAdvancedLogs()" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Advanced Logs
                        </button>
                        <button onclick="configureAntiDetection()" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            </svg>
                            Configure Anti-Detection
                        </button>
                        <button onclick="optimizeHighDemand()" class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Optimize High-Demand
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Platform Performance -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold">Platform Performance</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="platformsGrid">
                        @foreach($platforms as $platform => $data)
                        <div class="p-4 border border-gray-200 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-semibold text-lg">{{ $data['name'] }}</h4>
                                <span class="px-2 py-1 text-xs rounded-full {{ $data['success_rate'] > 80 ? 'bg-green-100 text-green-800' : ($data['success_rate'] > 60 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $data['success_rate'] }}%
                                </span>
                            </div>
                            <div class="space-y-1 text-sm text-gray-600">
                                <div class="flex justify-between">
                                    <span>Operations:</span>
                                    <span>{{ $data['total_operations'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Successful:</span>
                                    <span>{{ $data['successful_operations'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Avg Response:</span>
                                    <span>{{ $data['avg_response_time'] }}ms</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Results:</span>
                                    <span>{{ number_format($data['total_results']) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Dedicated Users:</span>
                                    <span>{{ $data['dedicated_users'] }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Management Tools -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- User Rotation Management -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">User Rotation</h3>
                <p class="text-gray-600 mb-4">Manage scraping user rotation and testing</p>
                <div class="space-y-2">
                    <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700" onclick="openRotationModal()">
                        View Rotation Stats
                    </button>
                    <button class="w-full bg-green-600 text-white py-2 px-4 rounded hover:bg-green-700" onclick="testUserRotation()">
                        Test Rotation
                    </button>
                </div>
            </div>

            <!-- Scraping Configuration -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Configuration</h3>
                <p class="text-gray-600 mb-4">Adjust scraping parameters and settings</p>
                <button class="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700" onclick="openConfigModal()">
                    Manage Configuration
                </button>
            </div>

            <!-- Performance Monitoring -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Performance</h3>
                <p class="text-gray-600 mb-4">Monitor scraping performance metrics</p>
                <button class="w-full bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700" onclick="openPerformanceModal()">
                    View Metrics
                </button>
            </div>
        </div>

        <!-- Recent Operations -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recent Operations</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platform</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Response Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Results</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="recentOperations">
                            @foreach($recentOperations as $operation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ ucfirst($operation['platform']) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $operation['operation'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $operation['status'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($operation['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $operation['response_time'] }}ms
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $operation['results_count'] ?? 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $operation['formatted_time'] }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals will be added via JavaScript -->
<div id="modalContainer"></div>

<script>
let scrapingData = {
    stats: @json($stats ?? []),
    platforms: @json($platforms ?? []),
    recentOperations: @json($recentOperations ?? []),
    userRotationStats: @json($userRotationStats ?? [])
};

// Refresh data
document.getElementById('refreshBtn').addEventListener('click', function() {
    location.reload();
});

// Test Anti-Detection button
document.getElementById('testAntiDetectionBtn').addEventListener('click', function() {
    testAntiDetection();
});

// Test High-Demand button
document.getElementById('testHighDemandBtn').addEventListener('click', function() {
    testHighDemandScraping();
});

// Scraping management functions
function openRotationModal() {
    showModal('User Rotation Statistics', createRotationView());
}

function openConfigModal() {
    showModal('Scraping Configuration', createConfigForm());
}

function openPerformanceModal() {
    showModal('Performance Metrics', createPerformanceView());
}

function testUserRotation() {
    if (confirm('Test user rotation system?')) {
        fetch('{{ route('admin.scraping.rotation-test') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                count: 5
            })
        })
        .then(response => response.json())
        .then(data => {
            showModal('Rotation Test Results', createRotationTestResults(data));
        })
        .catch(error => {
            alert('Error testing rotation: ' + error.message);
        });
    }
}

function createRotationView() {
    return `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded">
                    <h4 class="font-semibold">Total Users</h4>
                    <p class="text-2xl">${scrapingData.userRotationStats.total_users || 0}</p>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <h4 class="font-semibold">Active Users</h4>
                    <p class="text-2xl">${scrapingData.userRotationStats.active_users || 0}</p>
                </div>
            </div>
            <div class="mt-4">
                <h4 class="font-semibold mb-2">User Distribution</h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span>Premium Customers:</span>
                        <span>${scrapingData.userRotationStats.premium_customers || 0}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Platform Agents:</span>
                        <span>${scrapingData.userRotationStats.platform_agents || 0}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Rotation Pool:</span>
                        <span>${scrapingData.userRotationStats.rotation_pool || 0}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createConfigForm() {
    return `
        <form id="configForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Max Concurrent Requests</label>
                <input type="number" name="max_concurrent_requests" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="100" value="10">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Request Delay (ms)</label>
                <input type="number" name="request_delay_ms" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="0" max="10000" value="1000">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Retry Attempts</label>
                <input type="number" name="retry_attempts" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="10" value="3">
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="user_rotation_enabled" class="rounded border-gray-300" checked>
                    <span class="ml-2 text-sm text-gray-700">Enable User Rotation</span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Platform Rotation Interval (seconds)</label>
                <input type="number" name="platform_rotation_interval" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" min="1" max="3600" value="300">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Configuration</button>
            </div>
        </form>
    `;
}

function createPerformanceView() {
    return `
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                ${Object.entries(scrapingData.platforms).map(([platform, data]) => `
                    <div class="bg-gray-50 p-4 rounded">
                        <h4 class="font-semibold">${data.name}</h4>
                        <div class="text-sm space-y-1">
                            <div>Success Rate: ${data.success_rate}%</div>
                            <div>Operations: ${data.total_operations}</div>
                            <div>Avg Time: ${data.avg_response_time}ms</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
}

function createRotationTestResults(data) {
    return `
        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded">
                <h4 class="font-semibold">Test Summary</h4>
                <div class="text-sm space-y-1">
                    <div>Total Attempts: ${data.summary.total_attempts}</div>
                    <div>Successful Rotations: ${data.summary.successful_rotations}</div>
                    <div>Success Rate: ${data.summary.success_rate.toFixed(2)}%</div>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-2">Test Results</h4>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    ${data.test_results.map(result => `
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span>Attempt ${result.attempt}</span>
                            <span class="text-sm">${result.user_email || 'No user'}</span>
                            <span class="px-2 py-1 text-xs rounded ${result.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${result.success ? 'Success' : 'Failed'}
                            </span>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

// Modal functions
function showModal(title, content) {
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = `
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold">${title}</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div>${content}</div>
            </div>
        </div>
    `;
}

function closeModal() {
    document.getElementById('modalContainer').innerHTML = '';
}

// Advanced Anti-Detection Functions
function testAntiDetection() {
    if (confirm('Test anti-detection systems? This will run a controlled test using advanced protection mechanisms.')) {
        showModal('Testing Anti-Detection...', '<div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto"></div><p class="mt-4">Running anti-detection test...</p></div>');
        
        fetch('/admin/scraping/test-anti-detection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                platforms: ['stubhub', 'ticketmaster', 'seatgeek'],
                test_count: 3
            })
        })
        .then(response => response.json())
        .then(data => {
            showModal('Anti-Detection Test Results', createAntiDetectionTestResults(data));
        })
        .catch(error => {
            showModal('Test Error', `<div class="text-red-600">Error testing anti-detection: ${error.message}</div>`);
        });
    }
}

function testHighDemandScraping() {
    if (confirm('Test high-demand scraping optimizations? This will simulate priority ticket scraping.')) {
        showModal('Testing High-Demand...', '<div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-600 mx-auto"></div><p class="mt-4">Running high-demand test...</p></div>');
        
        fetch('/admin/scraping/test-high-demand', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                concurrent_requests: 10,
                priority_events: ['concert', 'sports']
            })
        })
        .then(response => response.json())
        .then(data => {
            showModal('High-Demand Test Results', createHighDemandTestResults(data));
        })
        .catch(error => {
            showModal('Test Error', `<div class="text-red-600">Error testing high-demand: ${error.message}</div>`);
        });
    }
}

function viewAdvancedLogs() {
    showModal('Loading Advanced Logs...', '<div class="text-center"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600 mx-auto"></div><p class="mt-4">Loading logs...</p></div>');
    
    fetch('/admin/scraping/advanced-logs', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        showModal('Advanced Scraping Logs', createAdvancedLogsView(data));
    })
    .catch(error => {
        showModal('Error', `<div class="text-red-600">Error loading logs: ${error.message}</div>`);
    });
}

function configureAntiDetection() {
    showModal('Anti-Detection Configuration', createAntiDetectionConfigForm());
}

function optimizeHighDemand() {
    showModal('High-Demand Optimization', createHighDemandOptimizationForm());
}

// Create result views for tests
function createAntiDetectionTestResults(data) {
    return `
        <div class="space-y-4">
            <div class="bg-red-50 p-4 rounded border border-red-200">
                <h4 class="font-semibold text-red-800 mb-2">Anti-Detection Test Summary</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium">Total Tests:</span> ${data.summary.total_tests}</div>
                    <div><span class="font-medium">Successful:</span> ${data.summary.successful_tests}</div>
                    <div><span class="font-medium">Detection Rate:</span> ${data.summary.detection_rate}%</div>
                    <div><span class="font-medium">Avg Response:</span> ${data.summary.avg_response_time}ms</div>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-2">Platform Results</h4>
                <div class="space-y-2 max-h-60 overflow-y-auto">
                    ${data.platform_results.map(result => `
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <div>
                                <span class="font-medium">${result.platform}</span>
                                <div class="text-sm text-gray-600">
                                    Methods: ${result.protection_methods.join(', ')}
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded ${result.bypassed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${result.bypassed ? 'Bypassed' : 'Detected'}
                                </span>
                                <div class="text-sm text-gray-600">${result.response_time}ms</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

function createHighDemandTestResults(data) {
    return `
        <div class="space-y-4">
            <div class="bg-orange-50 p-4 rounded border border-orange-200">
                <h4 class="font-semibold text-orange-800 mb-2">High-Demand Test Summary</h4>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><span class="font-medium">Concurrent Requests:</span> ${data.summary.concurrent_requests}</div>
                    <div><span class="font-medium">Total Processed:</span> ${data.summary.total_processed}</div>
                    <div><span class="font-medium">Success Rate:</span> ${data.summary.success_rate}%</div>
                    <div><span class="font-medium">Avg Processing:</span> ${data.summary.avg_processing_time}ms</div>
                </div>
            </div>
            <div>
                <h4 class="font-semibold mb-2">Queue Performance</h4>
                <div class="space-y-2">
                    ${data.queue_stats.map(stat => `
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <span class="font-medium">${stat.queue_type}</span>
                            <div class="text-right text-sm">
                                <div>Processed: ${stat.processed}</div>
                                <div>Avg Wait: ${stat.avg_wait_time}ms</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

function createAdvancedLogsView(data) {
    return `
        <div class="space-y-4">
            <div class="flex justify-between items-center">
                <h4 class="font-semibold">Recent Advanced Operations</h4>
                <select id="logFilter" class="text-sm border rounded px-2 py-1">
                    <option value="all">All Logs</option>
                    <option value="anti-detection">Anti-Detection</option>
                    <option value="high-demand">High-Demand</option>
                    <option value="errors">Errors Only</option>
                </select>
            </div>
            <div class="max-h-96 overflow-y-auto">
                <div class="space-y-2">
                    ${data.logs.map(log => `
                        <div class="p-3 border rounded text-sm ${log.type === 'error' ? 'border-red-200 bg-red-50' : 'border-gray-200 bg-gray-50'}">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-mono text-xs text-gray-500">${log.timestamp}</span>
                                <span class="px-2 py-1 text-xs rounded ${
                                    log.type === 'anti-detection' ? 'bg-red-100 text-red-700' :
                                    log.type === 'high-demand' ? 'bg-orange-100 text-orange-700' :
                                    log.type === 'error' ? 'bg-red-100 text-red-700' :
                                    'bg-blue-100 text-blue-700'
                                }">
                                    ${log.type.toUpperCase()}
                                </span>
                            </div>
                            <div class="font-medium">${log.message}</div>
                            ${log.details ? `<div class="text-gray-600 mt-1">${log.details}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
            </div>
        </div>
    `;
}

function createAntiDetectionConfigForm() {
    return `
        <form id="antiDetectionConfigForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">User-Agent Rotation</label>
                    <select name="user_agent_rotation" class="w-full border rounded px-3 py-2">
                        <option value="aggressive">Aggressive (Every Request)</option>
                        <option value="moderate" selected>Moderate (Every 10 Requests)</option>
                        <option value="conservative">Conservative (Every 100 Requests)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">IP Rotation Frequency</label>
                    <select name="ip_rotation" class="w-full border rounded px-3 py-2">
                        <option value="high">High (Every 5 Requests)</option>
                        <option value="medium" selected>Medium (Every 20 Requests)</option>
                        <option value="low">Low (Every 50 Requests)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Request Timing</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Min Delay (ms)</label>
                        <input type="number" name="min_delay" class="w-full border rounded px-3 py-2" value="500" min="0">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Max Delay (ms)</label>
                        <input type="number" name="max_delay" class="w-full border rounded px-3 py-2" value="2000" min="0">
                    </div>
                </div>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="fingerprint_protection" class="rounded" checked>
                    <span class="ml-2 text-sm">Enable Browser Fingerprint Protection</span>
                </label>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="captcha_solver" class="rounded" checked>
                    <span class="ml-2 text-sm">Enable Automatic CAPTCHA Solving</span>
                </label>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded">Save Anti-Detection Config</button>
            </div>
        </form>
    `;
}

function createHighDemandOptimizationForm() {
    return `
        <form id="highDemandConfigForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority Queue Size</label>
                    <input type="number" name="priority_queue_size" class="w-full border rounded px-3 py-2" value="100" min="10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dedicated Pools</label>
                    <input type="number" name="dedicated_pools" class="w-full border rounded px-3 py-2" value="5" min="1">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Load Balancing Strategy</label>
                <select name="load_balancing" class="w-full border rounded px-3 py-2">
                    <option value="round_robin">Round Robin</option>
                    <option value="least_connections" selected>Least Connections</option>
                    <option value="weighted">Weighted</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cache Settings</label>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Cache TTL (minutes)</label>
                        <input type="number" name="cache_ttl" class="w-full border rounded px-3 py-2" value="30" min="1">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Max Cache Size (MB)</label>
                        <input type="number" name="max_cache_size" class="w-full border rounded px-3 py-2" value="500" min="50">
                    </div>
                </div>
            </div>
            <div>
                <label class="flex items-center">
                    <input type="checkbox" name="auto_scaling" class="rounded" checked>
                    <span class="ml-2 text-sm">Enable Auto-Scaling</span>
                </label>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded">Save Optimization Config</button>
            </div>
        </form>
    `;
}

// Handle configuration form submissions
document.addEventListener('submit', function(e) {
    if (e.target.id === 'configForm') {
        e.preventDefault();
        const formData = new FormData(e.target);
        const config = Object.fromEntries(formData.entries());
        
        fetch('{{ route('admin.scraping.configuration.update') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(config)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Configuration updated successfully!');
                closeModal();
            } else {
                alert('Error updating configuration');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    if (e.target.id === 'antiDetectionConfigForm') {
        e.preventDefault();
        const formData = new FormData(e.target);
        const config = Object.fromEntries(formData.entries());
        
        fetch('/admin/scraping/configure-anti-detection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(config)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Anti-detection configuration saved successfully!');
                closeModal();
                location.reload();
            } else {
                alert('Error saving configuration');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    if (e.target.id === 'highDemandConfigForm') {
        e.preventDefault();
        const formData = new FormData(e.target);
        const config = Object.fromEntries(formData.entries());
        
        fetch('/admin/scraping/configure-high-demand', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(config)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('High-demand optimization saved successfully!');
                closeModal();
                location.reload();
            } else {
                alert('Error saving optimization');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
});
</script>
@endsection
