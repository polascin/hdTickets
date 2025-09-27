@extends('layouts.app-v2')

@section('title', 'Customer Dashboard - HD Tickets')

@section('meta')
  <meta name="dashboard-realtime-url" content="{{ route('api.dashboard.realtime') }}">
  <meta name="dashboard-api-base" content="{{ url('/api/v1/dashboard') }}">
  <meta name="dashboard-refresh-interval" content="120000">
  <meta name="user-role" content="{{ Auth::user()->role }}">
@endsection

@section('styles')
  <style>
    .glass-card {
      @apply bg-white/70 backdrop-blur-sm border border-white/20 shadow-lg;
    }

    .dark .glass-card {
      @apply bg-slate-800/70 border-slate-700/20;
    }

    .metric-card {
      @apply glass-card rounded-xl p-6 hover:shadow-xl transition-all duration-300;
    }

    .metric-value {
      @apply text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent;
    }

    .trend-indicator {
      @apply inline-flex items-center text-sm font-medium;
    }

    .trend-up {
      @apply text-green-600 dark:text-green-400;
    }

    .trend-down {
      @apply text-red-600 dark:text-red-400;
    }
  </style>
@endsection

@section('content')
  <div id="enhanced-customer-dashboard" x-data="enhancedCustomerDashboard()" x-init="init()"
    class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">

    <!-- Header Section -->
    <header
      class="sticky top-0 z-40 bg-white/80 backdrop-blur-sm border-b border-slate-200/60 dark:bg-slate-900/80 dark:border-slate-700/60"
      role="banner">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Welcome Section -->
          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-3">
              <div
                class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
                  </path>
                </svg>
              </div>
              <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                  Welcome back, {{ Auth::user()->first_name ?? Auth::user()->name }}!
                </h1>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                  {{ now()->format('l, F j, Y') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Action Bar -->
          <div class="flex items-center space-x-3">
            <!-- Refresh Button -->
            <button @click="refreshDashboard()" :disabled="loading"
              class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-colors duration-200">
              <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
              </svg>
              <svg x-show="loading" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              <span x-text="loading ? 'Refreshing...' : 'Refresh'"></span>
            </button>

            <!-- Notifications -->
            <button @click="showNotifications = !showNotifications"
              class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
              :aria-expanded="showNotifications">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
              <span x-show="notifications && notifications.unread_count > 0"
                class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full flex items-center justify-center">
                <span class="text-xs text-white font-medium" x-text="notifications.unread_count"></span>
              </span>
            </button>

            <!-- Settings -->
            <a href="{{ route('profile.edit') }}"
              class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                </path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </a>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" role="main">

      <!-- Error Message -->
      <div x-show="errorMessage" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4" role="alert">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"></path>
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-red-800 dark:text-red-200" x-text="errorMessage"></p>
          </div>
          <div class="ml-auto pl-3">
            <button @click="errorMessage = ''"
              class="text-red-800 dark:text-red-200 hover:text-red-600 dark:hover:text-red-400">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"></path>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Key Metrics Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Available Tickets -->
        <div class="metric-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Available Tickets</p>
              <div class="flex items-baseline mt-2">
                <p class="metric-value" x-text="formatNumber(statistics?.available_tickets || 0)">0</p>
                <span class="trend-indicator trend-up ml-2" x-show="statistics?.trend_indicators?.tickets_trend > 0">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z"
                      clip-rule="evenodd"></path>
                  </svg>
                  <span x-text="statistics?.trend_indicators?.tickets_change + '%'"></span>
                </span>
              </div>
            </div>
            <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
                </path>
              </svg>
            </div>
          </div>
        </div>

        <!-- New Today -->
        <div class="metric-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">New Today</p>
              <div class="flex items-baseline mt-2">
                <p class="metric-value" x-text="formatNumber(statistics?.new_today || 0)">0</p>
              </div>
            </div>
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Active Alerts -->
        <div class="metric-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Active Alerts</p>
              <div class="flex items-baseline mt-2">
                <p class="metric-value" x-text="formatNumber(alerts_data?.total_active || 0)">0</p>
              </div>
            </div>
            <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Events Monitored -->
        <div class="metric-card">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Events Monitored</p>
              <div class="flex items-baseline mt-2">
                <p class="metric-value" x-text="formatNumber(statistics?.unique_events || 0)">0</p>
              </div>
            </div>
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                </path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Recent Tickets & Recommendations -->
        <div class="lg:col-span-2 space-y-6">

          <!-- Recent Tickets -->
          <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Tickets</h2>
              <a href="{{ route('tickets.main') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                View all
              </a>
            </div>

            <div class="space-y-4" x-show="recent_tickets && recent_tickets.length > 0">
              <template x-for="ticket in recent_tickets.slice(0, 5)" :key="ticket.id">
                <div
                  class="flex items-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors duration-200">
                  <div
                    class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                    <span class="text-white text-sm font-bold"
                      x-text="ticket.platform?.charAt(0).toUpperCase() || 'T'"></span>
                  </div>
                  <div class="ml-4 flex-1">
                    <h4 class="text-sm font-medium text-slate-900 dark:text-white" x-text="ticket.title"></h4>
                    <p class="text-sm text-slate-600 dark:text-slate-400"
                      x-text="ticket.venue + ' • ' + formatDate(ticket.event_date)"></p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white" x-text="formatPrice(ticket.price)">
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="ticket.platform"></p>
                  </div>
                </div>
              </template>
            </div>

            <div x-show="!recent_tickets || recent_tickets.length === 0" class="text-center py-8">
              <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-4" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
                </path>
              </svg>
              <p class="text-slate-500 dark:text-slate-400">No tickets available at the moment</p>
            </div>
          </div>

          <!-- Personalized Recommendations -->
          <div class="glass-card rounded-xl p-6" x-show="recommendations && recommendations.length > 0">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Recommended for You</h2>
            <div class="space-y-3">
              <template x-for="rec in recommendations.slice(0, 3)" :key="rec.id">
                <div
                  class="flex items-center p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                  <div class="flex-1">
                    <h4 class="text-sm font-medium text-slate-900 dark:text-white" x-text="rec.title"></h4>
                    <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="rec.reason"></p>
                  </div>
                  <div class="text-right">
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200"
                      x-text="formatPrice(rec.price)"></span>
                  </div>
                </div>
              </template>
            </div>
          </div>

        </div>

        <!-- Right Column: Quick Actions & Alerts -->
        <div class="space-y-6">

          <!-- Quick Actions -->
          <div class="glass-card rounded-xl p-6">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="space-y-3">
              <!-- For now, use the existing working routes -->
              <div href="#"
                class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200 cursor-pointer"
                onclick="window.location.href='{{ route('tickets.scraping.index') }}'">
                <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">Create Alert</p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">Set up price monitoring</p>
                </div>
              </div>

              <a href="{{ route('tickets.main') }}"
                class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors duration-200">
                <div class="flex-shrink-0 w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
                <div class="ml-3">
                  <p class="text-sm font-medium text-slate-900 dark:text-white">Browse Tickets</p>
                  <p class="text-xs text-slate-600 dark:text-slate-400">Find sports events</p>
                </div>
              </a>
            </div>
          </div>

          <!-- Alert Summary -->
          <div class="glass-card rounded-xl p-6" x-show="alerts_data">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Alert Summary</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Total Active</span>
                <span class="text-sm font-medium text-slate-900 dark:text-white"
                  x-text="alerts_data?.total_active || 0"></span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Triggered Today</span>
                <span class="text-sm font-medium text-green-600 dark:text-green-400"
                  x-text="alerts_data?.triggered_today || 0"></span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Price Drops</span>
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400"
                  x-text="alerts_data?.price_drops || 0"></span>
              </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
              <a href="{{ route('tickets.alerts.index') }}"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                Manage alerts →
              </a>
            </div>
          </div>

          <!-- System Status -->
          <div class="glass-card rounded-xl p-6" x-show="system_status">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">System Status</h2>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Scraping Status</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="system_status?.scraping_active ?
                      'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' :
                      'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'">
                  <span x-text="system_status?.scraping_active ? 'Active' : 'Inactive'"></span>
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-slate-600 dark:text-slate-400">Last Update</span>
                <span class="text-xs text-slate-500 dark:text-slate-400"
                  x-text="formatTime(system_status?.last_update)"></span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </main>

    <!-- Notification Panel -->
    <div x-show="showNotifications" x-transition:enter="transition ease-out duration-200"
      x-transition:enter-start="opacity-0 transform translate-x-full"
      x-transition:enter-end="opacity-100 transform translate-x-0" x-transition:leave="transition ease-in duration-150"
      x-transition:leave-start="opacity-100 transform translate-x-0"
      x-transition:leave-end="opacity-0 transform translate-x-full"
      class="fixed inset-y-0 right-0 w-96 bg-white dark:bg-slate-800 shadow-xl z-50 overflow-y-auto"
      @click.away="showNotifications = false">
      <div class="p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Notifications</h3>
          <button @click="showNotifications = false"
            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"></path>
            </svg>
          </button>
        </div>

        <div x-show="notifications && notifications.items && notifications.items.length > 0" class="space-y-3">
          <template x-for="notification in notifications.items" :key="notification.id">
            <div class="p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
              <h4 class="text-sm font-medium text-slate-900 dark:text-white" x-text="notification.title"></h4>
              <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="notification.message"></p>
              <p class="text-xs text-slate-500 dark:text-slate-500 mt-2" x-text="formatTime(notification.created_at)">
              </p>
            </div>
          </template>
        </div>

        <div x-show="!notifications || !notifications.items || notifications.items.length === 0"
          class="text-center py-8">
          <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-4" fill="none" stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
          </svg>
          <p class="text-slate-500 dark:text-slate-400">No new notifications</p>
        </div>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  <script>
    function enhancedCustomerDashboard() {
      return {
        loading: false,
        showNotifications: false,
        errorMessage: '',

        // Data properties
        statistics: @json($statistics ?? []),
        recent_tickets: @json($recent_tickets ?? []),
        recommendations: @json($recommendations ?? []),
        alerts_data: @json($alerts_data ?? []),
        system_status: @json($system_status ?? []),
        notifications: @json($notifications ?? []),

        init() {
          console.log('Enhanced Customer Dashboard initialized');
          this.setupAutoRefresh();
        },

        setupAutoRefresh() {
          setInterval(() => {
            if (!this.loading) {
              this.refreshDashboard();
            }
          }, 120000); // 2 minutes
        },

        async refreshDashboard() {
          this.loading = true;
          this.errorMessage = '';

          try {
            const response = await fetch('/api/v1/dashboard/realtime', {
              method: 'GET',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
              },
              credentials: 'same-origin'
            });

            if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
              // Update dashboard data
              Object.assign(this, data.data);
              console.log('Dashboard refreshed successfully');
            } else {
              this.errorMessage = data.message || 'Failed to refresh dashboard';
            }
          } catch (error) {
            console.error('Dashboard refresh failed:', error);
            this.errorMessage = 'Unable to refresh dashboard. Please try again.';
          } finally {
            this.loading = false;
          }
        },

        formatNumber(number) {
          if (typeof number !== 'number') return '0';
          return new Intl.NumberFormat().format(number);
        },

        formatPrice(price) {
          if (typeof price !== 'number') return '$0';
          return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
          }).format(price);
        },

        formatDate(dateString) {
          if (!dateString) return '';
          const date = new Date(dateString);
          return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
          });
        },

        formatTime(dateString) {
          if (!dateString) return '';
          const date = new Date(dateString);
          const now = new Date();
          const diffMs = now - date;
          const diffMins = Math.floor(diffMs / 60000);

          if (diffMins < 1) return 'Just now';
          if (diffMins < 60) return `${diffMins}m ago`;
          if (diffMins < 1440) return `${Math.floor(diffMins / 60)}h ago`;
          return date.toLocaleDateString();
        }
      };
    }
  </script>
@endpush
