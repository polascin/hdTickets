@extends('layouts.modern-app')

@section('title', 'Admin Dashboard')

@section('meta_description', 'Comprehensive admin dashboard for sports ticket monitoring system with analytics and management tools')

@push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Admin Dashboard 🏟️
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                System health, analytics, and management overview
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- Quick Actions -->
            <div class="flex items-center gap-2">
                <button class="hdt-button hdt-button--primary hdt-button--sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add User
                </button>
                <button class="hdt-button hdt-button--secondary hdt-button--sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </button>
            </div>
            
            <!-- Time Range Selector -->
            <select class="hdt-input hdt-input--sm" x-data x-model="$store.dashboard.timeRange" @change="$store.dashboard.updateData()">
                <option value="24h">Last 24 Hours</option>
                <option value="7d">Last 7 Days</option>
                <option value="30d">Last 30 Days</option>
                <option value="90d">Last 90 Days</option>
            </select>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="adminDashboard()" x-init="init()" class="space-y-6">
        
        <!-- System Health Status -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- System Status -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">System Health</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Healthy</p>
                                <span class="ml-2 text-xs text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/50 px-2 py-1 rounded-full">
                                    99.9% uptime
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Scrapers -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Scrapers</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="systemStats.activeScrapers"></p>
                                <span class="ml-2 text-xs" :class="systemStats.scraperTrend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <template x-if="systemStats.scraperTrend === 'up'">
                                        <span>↑ +2 from yesterday</span>
                                    </template>
                                    <template x-if="systemStats.scraperTrend === 'down'">
                                        <span>↓ -1 from yesterday</span>
                                    </template>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Users -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100" x-text="systemStats.totalUsers.toLocaleString()"></p>
                                <span class="ml-2 text-xs text-green-600 dark:text-green-400">
                                    +<span x-text="systemStats.newUsersToday"></span> today
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Today -->
            <div class="hdt-card">
                <div class="hdt-card__body">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 dark:bg-green-900/50 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Revenue Today</p>
                            <div class="flex items-center mt-1">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">$<span x-text="systemStats.revenueToday.toLocaleString()"></span></p>
                                <span class="ml-2 text-xs" :class="systemStats.revenueTrend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                                    <span x-text="systemStats.revenueChange"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Revenue Chart -->
            <div class="hdt-card">
                <div class="hdt-card__header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Revenue Overview</h3>
                    <div class="flex items-center gap-2">
                        <select class="hdt-input hdt-input--xs" x-model="revenueChartPeriod" @change="updateRevenueChart()">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="hdt-card__body">
                    <canvas id="revenueChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <!-- User Activity Chart -->
            <div class="hdt-card">
                <div class="hdt-card__header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">User Activity</h3>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            <span>Active Users</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            <span>New Signups</span>
                        </div>
                    </div>
                </div>
                <div class="hdt-card__body">
                    <canvas id="userActivityChart" class="w-full h-64"></canvas>
                </div>
            </div>
        </div>

        <!-- Platform Performance & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Platform Performance -->
            <div class="lg:col-span-2">
                <div class="hdt-card">
                    <div class="hdt-card__header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Platform Performance</h3>
                        <button @click="refreshPlatformData()" class="hdt-button hdt-button--outline hdt-button--xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                    </div>
                    <div class="hdt-card__body">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Platform</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Success Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tickets Today</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Last Updated</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    <template x-for="platform in platformData" :key="platform.name">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <img :src="platform.logo" :alt="platform.name" class="w-8 h-8 rounded-full">
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="platform.name"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                      :class="platform.status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'">
                                                    <span x-text="platform.status"></span>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="text-sm text-gray-900 dark:text-gray-100" x-text="platform.successRate + '%'"></div>
                                                    <div class="ml-2 w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                        <div class="bg-blue-600 h-2 rounded-full" :style="`width: ${platform.successRate}%`"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100" x-text="platform.ticketsToday.toLocaleString()"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" x-text="platform.lastUpdated"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="hdt-card">
                <div class="hdt-card__header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Recent Activity</h3>
                </div>
                <div class="hdt-card__body">
                    <div class="space-y-4">
                        <template x-for="activity in recentActivity" :key="activity.id">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="getActivityIconClass(activity.type)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getActivityIconPath(activity.type)"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-gray-900 dark:text-gray-100" x-text="activity.message"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="activity.timestamp"></p>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- System Alerts -->
            <div class="hdt-card">
                <div class="hdt-card__header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">System Alerts</h3>
                    <span class="hdt-badge hdt-badge--warning hdt-badge--sm" x-text="systemAlerts.length + ' active'"></span>
                </div>
                <div class="hdt-card__body">
                    <div class="space-y-3">
                        <template x-for="alert in systemAlerts" :key="alert.id">
                            <div class="flex items-start justify-between p-3 rounded-lg"
                                 :class="getAlertClass(alert.severity)">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getAlertIcon(alert.severity)"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium" x-text="alert.title"></p>
                                        <p class="text-xs opacity-75" x-text="alert.message"></p>
                                        <p class="text-xs opacity-60 mt-1" x-text="alert.timestamp"></p>
                                    </div>
                                </div>
                                <button @click="dismissAlert(alert.id)" class="opacity-50 hover:opacity-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Quick Management Actions -->
            <div class="hdt-card">
                <div class="hdt-card__header">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h3>
                </div>
                <div class="hdt-card__body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <button class="hdt-button hdt-button--outline hdt-button--md justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add User
                        </button>
                        <button class="hdt-button hdt-button--outline hdt-button--md justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                            Manage Scrapers
                        </button>
                        <button class="hdt-button hdt-button--outline hdt-button--md justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            View Analytics
                        </button>
                        <button class="hdt-button hdt-button--outline hdt-button--md justify-start">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            System Config
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminDashboard', () => ({
                loading: false,
                revenueChartPeriod: 'daily',
                revenueChart: null,
                userActivityChart: null,
                
                systemStats: {
                    activeScrapers: 12,
                    scraperTrend: 'up',
                    totalUsers: 15847,
                    newUsersToday: 23,
                    revenueToday: 12547,
                    revenueChange: '+8.2%',
                    revenueTrend: 'up'
                },
                
                platformData: [
                    {
                        name: 'Ticketmaster',
                        logo: '/images/platforms/ticketmaster.png',
                        status: 'active',
                        successRate: 98,
                        ticketsToday: 1247,
                        lastUpdated: '2 mins ago'
                    },
                    {
                        name: 'StubHub',
                        logo: '/images/platforms/stubhub.png',
                        status: 'active',
                        successRate: 95,
                        ticketsToday: 892,
                        lastUpdated: '5 mins ago'
                    },
                    {
                        name: 'SeatGeek',
                        logo: '/images/platforms/seatgeek.png',
                        status: 'active',
                        successRate: 89,
                        ticketsToday: 674,
                        lastUpdated: '3 mins ago'
                    },
                    {
                        name: 'Vivid Seats',
                        logo: '/images/platforms/vivid.png',
                        status: 'warning',
                        successRate: 76,
                        ticketsToday: 423,
                        lastUpdated: '12 mins ago'
                    }
                ],
                
                recentActivity: [
                    {
                        id: 1,
                        type: 'user',
                        message: 'New user registration: john.doe@email.com',
                        timestamp: '2 minutes ago'
                    },
                    {
                        id: 2,
                        type: 'purchase',
                        message: 'Ticket purchase completed: Lakers vs Warriors - $175',
                        timestamp: '5 minutes ago'
                    },
                    {
                        id: 3,
                        type: 'scraper',
                        message: 'Scraper job completed for Ticketmaster - 1,247 tickets updated',
                        timestamp: '8 minutes ago'
                    },
                    {
                        id: 4,
                        type: 'alert',
                        message: 'Price alert triggered for NBA Finals tickets',
                        timestamp: '12 minutes ago'
                    },
                    {
                        id: 5,
                        type: 'system',
                        message: 'Database maintenance completed successfully',
                        timestamp: '1 hour ago'
                    }
                ],
                
                systemAlerts: [
                    {
                        id: 1,
                        severity: 'warning',
                        title: 'High Memory Usage',
                        message: 'Server memory usage at 85%. Consider scaling.',
                        timestamp: '10 minutes ago'
                    },
                    {
                        id: 2,
                        severity: 'info',
                        title: 'Scheduled Maintenance',
                        message: 'Database maintenance scheduled for tonight at 2 AM EST.',
                        timestamp: '2 hours ago'
                    },
                    {
                        id: 3,
                        severity: 'error',
                        title: 'API Rate Limit',
                        message: 'Vivid Seats API approaching rate limit threshold.',
                        timestamp: '1 hour ago'
                    }
                ],
                
                async init() {
                    await this.loadData();
                    this.initCharts();
                    this.startRealTimeUpdates();
                },
                
                async loadData() {
                    this.loading = true;
                    // Simulate API calls
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    this.loading = false;
                },
                
                initCharts() {
                    // Revenue Chart
                    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
                    this.revenueChart = new Chart(revenueCtx, {
                        type: 'line',
                        data: {
                            labels: this.getRevenueLabels(),
                            datasets: [{
                                label: 'Revenue',
                                data: this.getRevenueData(),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                    
                    // User Activity Chart
                    const userCtx = document.getElementById('userActivityChart').getContext('2d');
                    this.userActivityChart = new Chart(userCtx, {
                        type: 'bar',
                        data: {
                            labels: this.getUserActivityLabels(),
                            datasets: [{
                                label: 'Active Users',
                                data: this.getActiveUsersData(),
                                backgroundColor: 'rgba(59, 130, 246, 0.8)'
                            }, {
                                label: 'New Signups',
                                data: this.getNewSignupsData(),
                                backgroundColor: 'rgba(34, 197, 94, 0.8)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                },
                
                getRevenueLabels() {
                    return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
                },
                
                getRevenueData() {
                    return [12000, 15000, 18000, 14000, 22000, 25000, 20000];
                },
                
                getUserActivityLabels() {
                    return ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                },
                
                getActiveUsersData() {
                    return [420, 380, 450, 520, 480, 380, 420];
                },
                
                getNewSignupsData() {
                    return [12, 15, 8, 23, 18, 25, 19];
                },
                
                updateRevenueChart() {
                    // Update chart based on selected period
                    this.revenueChart.data.labels = this.getRevenueLabels();
                    this.revenueChart.data.datasets[0].data = this.getRevenueData();
                    this.revenueChart.update();
                },
                
                refreshPlatformData() {
                    // Refresh platform performance data
                    this.loadData();
                },
                
                startRealTimeUpdates() {
                    // Update data every 30 seconds
                    setInterval(() => {
                        this.updateRealTimeData();
                    }, 30000);
                },
                
                updateRealTimeData() {
                    // Update system stats with real-time data
                    this.systemStats.activeScrapers = Math.floor(Math.random() * 3) + 11;
                    this.systemStats.newUsersToday = Math.floor(Math.random() * 10) + 20;
                },
                
                getActivityIconClass(type) {
                    const classes = {
                        user: 'bg-blue-100 text-blue-600 dark:bg-blue-900/50 dark:text-blue-400',
                        purchase: 'bg-green-100 text-green-600 dark:bg-green-900/50 dark:text-green-400',
                        scraper: 'bg-purple-100 text-purple-600 dark:bg-purple-900/50 dark:text-purple-400',
                        alert: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/50 dark:text-yellow-400',
                        system: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400'
                    };
                    return classes[type] || classes.system;
                },
                
                getActivityIconPath(type) {
                    const paths = {
                        user: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                        purchase: 'M3 3h2l.4 2M7 13h10l4-8H5.4m1.6 8L6 5H2m5 8v6a2 2 0 002 2h6a2 2 0 002-2v-6m-6 0v6m-6-6h12',
                        scraper: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z',
                        alert: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        system: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
                    };
                    return paths[type] || paths.system;
                },
                
                getAlertClass(severity) {
                    const classes = {
                        error: 'bg-red-50 text-red-800 dark:bg-red-900/20 dark:text-red-300',
                        warning: 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300',
                        info: 'bg-blue-50 text-blue-800 dark:bg-blue-900/20 dark:text-blue-300',
                        success: 'bg-green-50 text-green-800 dark:bg-green-900/20 dark:text-green-300'
                    };
                    return classes[severity] || classes.info;
                },
                
                getAlertIcon(severity) {
                    const icons = {
                        error: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                        warning: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
                        info: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                        success: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
                    };
                    return icons[severity] || icons.info;
                },
                
                dismissAlert(alertId) {
                    this.systemAlerts = this.systemAlerts.filter(alert => alert.id !== alertId);
                }
            }));
        });
    </script>
@endsection