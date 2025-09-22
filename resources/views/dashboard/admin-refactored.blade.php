@extends('layouts.master')

@section('title', 'Admin Dashboard')

@section('meta_description', 'Sports ticket monitoring system administration dashboard with analytics, user management, and system monitoring')

@push('head')
  <meta name="robots" content="noindex, nofollow">
  <link rel="preload" href="{{ Vite::asset('resources/js/charts.js') }}" as="script">
@endpush

@section('page-header')
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
      <h1 class="text-2xl lg:text-3xl font-bold text-hd-gray-900 dark:text-white">
        Administration Dashboard
      </h1>
      <p class="mt-1 text-sm text-hd-gray-600 dark:text-hd-gray-400">
        System management, analytics, and monitoring overview
      </p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <!-- System Status -->
      <div class="flex items-center gap-2 text-sm">
        <x-hdt.badge variant="success" dot pulse>System Online</x-hdt.badge>
        <span class="text-hd-gray-500" x-data x-text="'Updated ' + new Date().toLocaleTimeString()"></span>
      </div>

      <!-- Quick Actions -->
      <x-hdt.button href="/admin/users" size="sm" variant="primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
        </svg>
        Manage Users
      </x-hdt.button>

      <x-hdt.button href="/admin/reports" size="sm" variant="secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        Reports
      </x-hdt.button>
    </div>
  </div>
@endsection

@section('content')
  <div class="space-y-8" x-data="adminDashboard()" x-init="init()">
    
    <!-- System Health Stats -->
    <div class="dashboard-stats-grid">
      <x-hdt.stat-card 
        label="Total Users" 
        x-text="systemStats.totalUsers.toLocaleString()"
        variant="primary"
        trend="up" 
        trendValue="+23" 
        trendLabel="this month">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="System Load" 
        x-text="systemStats.cpuUsage + '%'"
        :variant="systemStats.cpuUsage > 80 ? 'danger' : systemStats.cpuUsage > 60 ? 'warning' : 'success'"
        description="CPU utilization">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="Revenue Today" 
        x-text="'$' + systemStats.dailyRevenue.toLocaleString()"
        variant="success"
        trend="up" 
        trendValue="+12.5%" 
        trendLabel="vs yesterday">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card 
        label="Active Scrapers" 
        x-text="systemStats.activeScrapers + ' / ' + systemStats.totalScrapers"
        :variant="systemStats.scraperHealth === 'healthy' ? 'success' : systemStats.scraperHealth === 'warning' ? 'warning' : 'danger'"
        description="Scraping operations">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
      
      <!-- Left Column: Analytics and Charts -->
      <div class="xl:col-span-2 space-y-6">
        
        <!-- Revenue Analytics -->
        <x-hdt.card>
          <x-slot:title>Revenue Analytics</x-slot:title>
          <x-slot:subtitle>
            <div class="flex items-center gap-4">
              <select class="hdt-input__field hdt-input--sm" x-model="revenueTimeframe" @change="updateRevenueChart()">
                <option value="7d">Last 7 days</option>
                <option value="30d">Last 30 days</option>
                <option value="90d">Last 90 days</option>
                <option value="1y">Last year</option>
              </select>
            </div>
          </x-slot:subtitle>
          
          <!-- Chart Container -->
          <div class="h-80 relative">
            <div x-show="!chartsLoaded" class="absolute inset-0 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-hd-primary-600"></div>
            </div>
            <canvas id="revenueChart" 
                    x-show="chartsLoaded" 
                    x-transition:enter="transition-opacity duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="w-full h-full"></canvas>
          </div>
        </x-hdt.card>

        <!-- User Growth -->
        <x-hdt.card>
          <x-slot:title>User Growth</x-slot:title>
          <x-slot:subtitle>User registrations and activity trends</x-slot:subtitle>
          
          <div class="h-64 relative">
            <div x-show="!chartsLoaded" class="absolute inset-0 flex items-center justify-center">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-hd-primary-600"></div>
            </div>
            <canvas id="userGrowthChart" 
                    x-show="chartsLoaded" 
                    x-transition
                    class="w-full h-full"></canvas>
          </div>
        </x-hdt.card>

        <!-- System Performance -->
        <x-hdt.card>
          <x-slot:title>System Performance</x-slot:title>
          <x-slot:subtitle>Real-time system metrics and alerts</x-slot:subtitle>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Performance Metrics -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-hd-gray-600 dark:text-hd-gray-400">CPU Usage</span>
                <span class="text-sm font-bold" x-text="systemStats.cpuUsage + '%'"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + systemStats.cpuUsage + '%'"></div>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-hd-gray-600 dark:text-hd-gray-400">Memory</span>
                <span class="text-sm font-bold" x-text="systemStats.memoryUsage + '%'"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + systemStats.memoryUsage + '%'"></div>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-hd-gray-600 dark:text-hd-gray-400">Storage</span>
                <span class="text-sm font-bold" x-text="systemStats.storageUsage + '%'"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-yellow-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + systemStats.storageUsage + '%'"></div>
              </div>
            </div>

            <!-- Server Health -->
            <div class="space-y-4">
              <h4 class="font-semibold text-hd-gray-900 dark:text-hd-gray-100">Server Health</h4>
              <template x-for="server in serverStatus" :key="server.name">
                <div class="flex items-center justify-between p-3 bg-hd-gray-50 dark:bg-hd-gray-800 rounded-lg">
                  <div class="flex items-center gap-3">
                    <div class="w-3 h-3 rounded-full"
                         :class="server.status === 'online' ? 'bg-green-500' : server.status === 'warning' ? 'bg-yellow-500' : 'bg-red-500'"></div>
                    <span class="text-sm font-medium" x-text="server.name"></span>
                  </div>
                  <x-hdt.badge size="xs" 
                               :variant="server.status === 'online' ? 'success' : server.status === 'warning' ? 'warning' : 'danger'"
                               x-text="server.status" 
                               class="capitalize"></x-hdt.badge>
                </div>
              </template>
            </div>
          </div>
        </x-hdt.card>

        <!-- Recent Events and Logs -->
        <x-hdt.card>
          <x-slot:title>Recent Events</x-slot:title>
          <x-slot:subtitle>
            <div class="flex items-center gap-2">
              <span>System activity and alerts</span>
              <x-hdt.badge variant="info" size="sm" x-text="recentEvents.length + ' events'"></x-hdt.badge>
            </div>
          </x-slot:subtitle>
          
          <div class="space-y-3" x-show="recentEvents.length > 0">
            <template x-for="event in recentEvents.slice(0, 8)" :key="event.id">
              <div class="flex items-start gap-4 p-4 border border-hd-gray-200 dark:border-hd-gray-700 rounded-lg">
                <div class="flex-shrink-0 w-2 h-2 mt-2 rounded-full"
                     :class="{
                       'bg-green-500': event.type === 'success',
                       'bg-blue-500': event.type === 'info',
                       'bg-yellow-500': event.type === 'warning',
                       'bg-red-500': event.type === 'error'
                     }"></div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <p class="text-sm font-medium text-hd-gray-900 dark:text-hd-gray-100" x-text="event.title"></p>
                    <x-hdt.badge size="xs" 
                                 :variant="event.type === 'success' ? 'success' : event.type === 'warning' ? 'warning' : event.type === 'error' ? 'danger' : 'info'"
                                 x-text="event.type" 
                                 class="capitalize"></x-hdt.badge>
                  </div>
                  <p class="text-xs text-hd-gray-600 dark:text-hd-gray-400" x-text="event.description"></p>
                  <p class="text-xs text-hd-gray-500 mt-1" x-text="event.timestamp"></p>
                </div>
                <x-hdt.button size="xs" variant="ghost" x-show="event.actionable" @click="handleEventAction(event)">
                  View
                </x-hdt.button>
              </div>
            </template>
          </div>

          <div x-show="recentEvents.length === 0" class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-hd-gray-300 dark:text-hd-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">No recent events</p>
          </div>
        </x-hdt.card>
      </div>

      <!-- Right Column: Management Panels -->
      <div class="space-y-6">
        
        <!-- Quick Actions Panel -->
        <x-hdt.card>
          <x-slot:title>Quick Actions</x-slot:title>
          
          <div class="space-y-3">
            <x-hdt.button href="/admin/users/create" variant="secondary" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
              Create User
            </x-hdt.button>
            
            <x-hdt.button href="/admin/system/backup" variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
              </svg>
              System Backup
            </x-hdt.button>
            
            <x-hdt.button href="/admin/scrapers" variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Scraper Management
            </x-hdt.button>

            <x-hdt.button href="/admin/maintenance" variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
              </svg>
              Maintenance Mode
            </x-hdt.button>
          </div>
        </x-hdt.card>

        <!-- User Management Summary -->
        <x-hdt.card>
          <x-slot:title>Users Overview</x-slot:title>
          <x-slot:subtitle>
            <a href="/admin/users" class="text-hd-secondary-600 hover:text-hd-secondary-700 text-sm font-medium">Manage All</a>
          </x-slot:subtitle>
          
          <div class="space-y-4">
            <!-- Role Distribution -->
            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Customers</span>
                <span class="text-sm font-bold" x-text="userStats.customers.toLocaleString()"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + (userStats.customers / userStats.total * 100) + '%'"></div>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Agents</span>
                <span class="text-sm font-bold" x-text="userStats.agents.toLocaleString()"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-blue-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + (userStats.agents / userStats.total * 100) + '%'"></div>
              </div>
            </div>

            <div>
              <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Admins</span>
                <span class="text-sm font-bold" x-text="userStats.admins"></span>
              </div>
              <div class="w-full bg-hd-gray-200 dark:bg-hd-gray-700 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full transition-all duration-500" 
                     :style="'width: ' + (userStats.admins / userStats.total * 100) + '%'"></div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="mt-6 pt-6 border-t border-hd-gray-200 dark:border-hd-gray-700">
            <h4 class="text-sm font-semibold text-hd-gray-900 dark:text-hd-gray-100 mb-3">Recent Registrations</h4>
            <div class="space-y-2" x-show="recentUsers.length > 0">
              <template x-for="user in recentUsers.slice(0, 3)" :key="user.id">
                <div class="flex items-center gap-3 p-2 bg-hd-gray-50 dark:bg-hd-gray-800 rounded">
                  <div class="w-8 h-8 bg-hd-primary-600 text-white rounded-full flex items-center justify-center text-xs font-bold"
                       x-text="user.name.charAt(0).toUpperCase()"></div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate" x-text="user.name"></p>
                    <p class="text-xs text-hd-gray-500" x-text="user.registered_ago"></p>
                  </div>
                  <x-hdt.badge size="xs" 
                               :variant="user.role === 'customer' ? 'success' : user.role === 'agent' ? 'info' : 'warning'"
                               x-text="user.role" 
                               class="capitalize"></x-hdt.badge>
                </div>
              </template>
            </div>
          </div>
        </x-hdt.card>

        <!-- System Alerts -->
        <x-hdt.card>
          <x-slot:title>System Alerts</x-slot:title>
          <x-slot:subtitle>
            <x-hdt.badge size="sm" 
                         :variant="systemAlerts.length > 0 ? 'warning' : 'success'"
                         x-text="systemAlerts.length + ' alerts'"></x-hdt.badge>
          </x-slot:subtitle>
          
          <div class="space-y-3" x-show="systemAlerts.length > 0">
            <template x-for="alert in systemAlerts" :key="alert.id">
              <div class="p-3 border rounded-lg"
                   :class="{
                     'bg-red-50 border-red-200 dark:bg-red-900/20 dark:border-red-800': alert.severity === 'critical',
                     'bg-yellow-50 border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800': alert.severity === 'warning',
                     'bg-blue-50 border-blue-200 dark:bg-blue-900/20 dark:border-blue-800': alert.severity === 'info'
                   }">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium" 
                       :class="{
                         'text-red-800 dark:text-red-200': alert.severity === 'critical',
                         'text-yellow-800 dark:text-yellow-200': alert.severity === 'warning',
                         'text-blue-800 dark:text-blue-200': alert.severity === 'info'
                       }"
                       x-text="alert.title"></p>
                    <p class="text-xs mt-1" 
                       :class="{
                         'text-red-600 dark:text-red-300': alert.severity === 'critical',
                         'text-yellow-600 dark:text-yellow-300': alert.severity === 'warning',
                         'text-blue-600 dark:text-blue-300': alert.severity === 'info'
                       }"
                       x-text="alert.message"></p>
                    <p class="text-xs text-hd-gray-500 mt-1" x-text="alert.time_ago"></p>
                  </div>
                  <x-hdt.button size="xs" variant="ghost" @click="dismissAlert(alert.id)">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </x-hdt.button>
                </div>
              </div>
            </template>
          </div>

          <div x-show="systemAlerts.length === 0" class="text-center py-6">
            <svg class="w-8 h-8 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">All systems operational</p>
          </div>
        </x-hdt.card>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  function adminDashboard() {
    return {
      // System metrics
      systemStats: {
        totalUsers: 2847,
        cpuUsage: 45,
        memoryUsage: 62,
        storageUsage: 78,
        dailyRevenue: 15420,
        activeScrapers: 8,
        totalScrapers: 12,
        scraperHealth: 'healthy'
      },

      userStats: {
        total: 2847,
        customers: 2650,
        agents: 185,
        admins: 12
      },

      serverStatus: [
        { name: 'Web Server', status: 'online' },
        { name: 'Database', status: 'online' },
        { name: 'Redis Cache', status: 'online' },
        { name: 'Queue Worker', status: 'warning' },
        { name: 'Scraper Cluster', status: 'online' }
      ],

      recentEvents: [
        {
          id: 1,
          type: 'success',
          title: 'New user registration',
          description: 'John Doe registered as customer',
          timestamp: '2 minutes ago',
          actionable: true
        },
        {
          id: 2,
          type: 'warning',
          title: 'High CPU usage detected',
          description: 'Server load exceeded 90% for 5 minutes',
          timestamp: '15 minutes ago',
          actionable: true
        },
        {
          id: 3,
          type: 'info',
          title: 'Backup completed',
          description: 'Daily backup completed successfully',
          timestamp: '1 hour ago',
          actionable: false
        },
        {
          id: 4,
          type: 'error',
          title: 'Scraper failure',
          description: 'StubHub scraper encountered connection timeout',
          timestamp: '2 hours ago',
          actionable: true
        }
      ],

      recentUsers: [
        {
          id: 1,
          name: 'Sarah Johnson',
          role: 'customer',
          registered_ago: '5 minutes ago'
        },
        {
          id: 2,
          name: 'Mike Chen',
          role: 'agent',
          registered_ago: '1 hour ago'
        },
        {
          id: 3,
          name: 'Emily Davis',
          role: 'customer',
          registered_ago: '3 hours ago'
        }
      ],

      systemAlerts: [
        {
          id: 1,
          severity: 'warning',
          title: 'Queue Worker Lagging',
          message: 'Background job queue has 150+ pending jobs',
          time_ago: '10 minutes ago'
        },
        {
          id: 2,
          severity: 'info',
          title: 'Maintenance Window',
          message: 'Scheduled maintenance in 2 hours',
          time_ago: '30 minutes ago'
        }
      ],

      // Chart data
      revenueTimeframe: '30d',
      chartsLoaded: false,
      charts: {},

      async init() {
        await this.loadDashboardData();
        this.startRealTimeUpdates();
        this.loadCharts();
      },

      async loadDashboardData() {
        try {
          const response = await fetch('/api/v1/admin/dashboard/stats', {
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json',
            }
          });

          if (response.ok) {
            const data = await response.json();
            
            if (data.system_stats) {
              Object.assign(this.systemStats, data.system_stats);
            }
            if (data.user_stats) {
              Object.assign(this.userStats, data.user_stats);
            }
            if (data.server_status) {
              this.serverStatus = data.server_status;
            }
            if (data.recent_events) {
              this.recentEvents = data.recent_events;
            }
            if (data.recent_users) {
              this.recentUsers = data.recent_users;
            }
            if (data.system_alerts) {
              this.systemAlerts = data.system_alerts;
            }
          }
        } catch (error) {
          console.error('Error loading admin dashboard data:', error);
        }
      },

      async loadCharts() {
        try {
          // Check if Chart.js is available (would be loaded via dynamic import in real implementation)
          if (typeof Chart === 'undefined') {
            console.log('Charts would be loaded dynamically here');
            this.chartsLoaded = true;
            return;
          }
          
          await this.initRevenueChart();
          await this.initUserGrowthChart();
          
          this.chartsLoaded = true;
        } catch (error) {
          console.error('Error loading charts:', error);
          this.chartsLoaded = true; // Set to true to hide loading spinner
        }
      },

      async initRevenueChart() {
        const ctx = document.getElementById('revenueChart')?.getContext('2d');
        if (!ctx || typeof Chart === 'undefined') return;

        const data = await this.fetchRevenueData();
        
        this.charts.revenue = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Daily Revenue',
              data: data.values,
              borderColor: 'rgb(59, 130, 246)',
              backgroundColor: 'rgba(59, 130, 246, 0.1)',
              fill: true,
              tension: 0.4
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
            },
            plugins: {
              tooltip: {
                callbacks: {
                  label: function(context) {
                    return 'Revenue: $' + context.raw.toLocaleString();
                  }
                }
              }
            }
          }
        });
      },

      async initUserGrowthChart() {
        const ctx = document.getElementById('userGrowthChart')?.getContext('2d');
        if (!ctx || typeof Chart === 'undefined') return;

        const data = await this.fetchUserGrowthData();
        
        this.charts.userGrowth = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: data.labels,
            datasets: [
              {
                label: 'New Customers',
                data: data.customers,
                backgroundColor: 'rgba(34, 197, 94, 0.8)'
              },
              {
                label: 'New Agents',
                data: data.agents,
                backgroundColor: 'rgba(59, 130, 246, 0.8)'
              }
            ]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              x: { stacked: true },
              y: { stacked: true, beginAtZero: true }
            }
          }
        });
      },

      async updateRevenueChart() {
        if (!this.charts.revenue) return;
        
        const data = await this.fetchRevenueData();
        this.charts.revenue.data.labels = data.labels;
        this.charts.revenue.data.datasets[0].data = data.values;
        this.charts.revenue.update();
      },

      async fetchRevenueData() {
        // Mock data - replace with actual API call
        const labels = [];
        const values = [];
        const days = this.revenueTimeframe === '7d' ? 7 : this.revenueTimeframe === '30d' ? 30 : this.revenueTimeframe === '90d' ? 90 : 365;
        
        for (let i = days; i >= 0; i--) {
          const date = new Date();
          date.setDate(date.getDate() - i);
          labels.push(date.toLocaleDateString());
          values.push(Math.random() * 20000 + 5000);
        }
        
        return { labels, values };
      },

      async fetchUserGrowthData() {
        // Mock data - replace with actual API call
        const labels = [];
        const customers = [];
        const agents = [];
        
        for (let i = 29; i >= 0; i--) {
          const date = new Date();
          date.setDate(date.getDate() - i);
          labels.push(date.toLocaleDateString());
          customers.push(Math.floor(Math.random() * 50) + 10);
          agents.push(Math.floor(Math.random() * 5) + 1);
        }
        
        return { labels, customers, agents };
      },

      startRealTimeUpdates() {
        // Refresh system stats every 30 seconds
        setInterval(() => {
          this.updateSystemMetrics();
        }, 30000);

        // Real-time updates via WebSocket
        if (typeof Echo !== 'undefined') {
          Echo.private('admin.system')
            .listen('SystemMetricsUpdated', (e) => {
              Object.assign(this.systemStats, e.metrics);
            })
            .listen('SystemAlertCreated', (e) => {
              this.systemAlerts.unshift(e.alert);
            })
            .listen('UserRegistered', (e) => {
              this.systemStats.totalUsers++;
              this.userStats.total++;
              
              // Update role counts
              if (e.user.role === 'customer') this.userStats.customers++;
              else if (e.user.role === 'agent') this.userStats.agents++;
              else if (e.user.role === 'admin') this.userStats.admins++;
              
              this.recentUsers.unshift(e.user);
              if (this.recentUsers.length > 5) {
                this.recentUsers.pop();
              }
            });
        }
      },

      updateSystemMetrics() {
        // Simulate live metric updates
        this.systemStats.cpuUsage = Math.max(20, Math.min(95, this.systemStats.cpuUsage + (Math.random() - 0.5) * 10));
        this.systemStats.memoryUsage = Math.max(30, Math.min(90, this.systemStats.memoryUsage + (Math.random() - 0.5) * 5));
        this.systemStats.storageUsage = Math.max(60, Math.min(85, this.systemStats.storageUsage + (Math.random() - 0.5) * 2));
      },

      handleEventAction(event) {
        // Handle event actions based on type
        switch(event.type) {
          case 'error':
            window.location.href = '/admin/logs?filter=error';
            break;
          case 'warning':
            window.location.href = '/admin/system/monitoring';
            break;
          case 'success':
            window.location.href = '/admin/users';
            break;
          default:
            console.log('View event:', event);
        }
      },

      dismissAlert(alertId) {
        this.systemAlerts = this.systemAlerts.filter(alert => alert.id !== alertId);
        
        // API call to dismiss alert
        fetch(`/api/v1/admin/alerts/${alertId}/dismiss`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          }
        }).catch(error => console.error('Error dismissing alert:', error));
      }
    }
  }
</script>
@endpush

@push('styles')
<style>
  /* Admin dashboard specific styles */
  .dashboard-stats-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: 1fr;
  }

  @media (min-width: 640px) {
    .dashboard-stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (min-width: 1024px) {
    .dashboard-stats-grid {
      grid-template-columns: repeat(4, 1fr);
    }
  }
</style>
@endpush