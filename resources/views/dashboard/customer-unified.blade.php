@extends('layouts.app-v2')@extends('layouts.app-v2')



@section('title', 'Customer Dashboard')@section('title', 'Customer Dashboard')



@push('styles')@section('meta')

<style>
  <meta name="dashboard-realtime-url" content="{{ route('api.dashboard.realtime') }}">.dashboard-card {
    <meta name="dashboard-api-base" content="{{ url('/api/v1/dashboard') }}">@apply bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-shadow duration-200;
    <meta name="dashboard-refresh-interval" content="120000">
  }

  <meta name="user-role" content="{{ Auth::user()->role }}">
  @endsection

  .metric-card {

    @apply dashboard-card p-6;

    @push('styles')

  }

  <style>.customer-dashboard {

    .metric-value {
      @apply space-y-6;

      @apply text-3xl font-bold text-slate-900 dark:text-slate-100;
    }

  }

  .glass-card {

    .metric-label {
      @apply bg-white/95 backdrop-blur-sm border border-slate-200/60 shadow-sm dark:bg-slate-800/95 dark:border-slate-700/60;

      @apply text-sm font-medium text-slate-600 dark:text-slate-400;
    }

  }

  .metric-card {

    .metric-trend {
      @apply glass-card rounded-lg p-6 hover:shadow-md transition-all duration-200;

      @apply text-sm font-medium;
    }

  }

  .metric-value {

    .metric-trend.up {
      @apply text-2xl font-bold text-slate-900 dark:text-slate-100;

      @apply text-emerald-600 dark:text-emerald-400;
    }

  }

  .metric-gradient {

    .metric-trend.down {
      @apply bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent;

      @apply text-red-600 dark:text-red-400;
    }

  }

  .trend-indicator {

    .dashboard-header {
      @apply inline-flex items-center text-sm font-medium;

      @apply mb-8 pb-6 border-b border-slate-200 dark:border-slate-700;
    }

  }

  .trend-up {

    .action-button {
      @apply text-green-600 dark:text-green-400;

      @apply inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg shadow-sm hover:shadow transition-all duration-200;
    }

  }

  .trend-down {

    .section-title {
      @apply text-red-600 dark:text-red-400;

      @apply text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4;
    }

  }

  .dashboard-section {

    .ticket-item {
      @apply bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm;

      @apply flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200;
    }

  }
</style>

</style>@endpush

@endpush

@section('content')

@section('content') <div id="enhanced-customer-dashboard" x-data="enhancedCustomerDashboard()" x-init="init()"
  class="customer-dashboard">

  <div class="max-w-7xl mx-auto" x-data="customerDashboard()" x-init="init()">

    <!-- Welcome Banner -->

    <!-- Dashboard Header -->
    <div class="glass-card p-6 mb-6">

      <div class="dashboard-header">
        <div class="flex items-center justify-between">

          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">

              <div>
                <div
                  class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">

                  <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100"> <svg class="w-6 h-6 text-white"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">

                      Welcome back, {{ Auth::user()->first_name ?? Auth::user()->name }}! <path stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" </h1> d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0
                        110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">

                        <p class="text-slate-600 dark:text-slate-400 mt-1">
                      </path>

                      Here's what's happening with your ticket monitoring </svg>

                    </p>
                </div>

              </div>
              <div>

                <div class="flex space-x-3">
                  <h2 class="text-xl font-semibold text-slate-900 dark:text-white">

                    <button @click="refreshData()" :disabled="loading" class="action-button"> Welcome back,
                      {{ Auth::user()->first_name ?? Auth::user()->name }}!

                      <svg x-show="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                  </h2>

                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                  </path>
                  <p class="text-sm text-slate-600 dark:text-slate-400">

                    </svg> {{ now()->format('l, F j, Y') }}

                    <svg x-show="loading" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                  </p>

                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                </div>

                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </div>

              </svg>

              <span x-text="loading ? 'Refreshing...' : 'Refresh'"></span> <!-- Action Buttons -->

              </button>
              <div class="flex items-center space-x-3">

              </div> <button @click="refreshDashboard()" :disabled="loading" class="btn-primary">

            </div> <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">

          </div>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">

            <!-- Key Metrics -->
          </path>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8"> </svg>

            <svg x-show="loading" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">

              <!-- Available Tickets -->
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">

                <div class="metric-card">
              </circle>

              <div class="flex items-center justify-between">
                <path class="opacity-75" fill="currentColor" <div> d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2
                  5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">

                  <p class="metric-label">Available Tickets</p>
                </path>

                <p class="metric-value" x-text="formatNumber(metrics.available_tickets)">0</p>
            </svg>

            <div class="flex items-center mt-2" x-show="metrics.tickets_trend"> <span
                x-text="loading ? 'Refreshing...' : 'Refresh'"></span>

              <span class="metric-trend" :class="metrics.tickets_trend > 0 ? 'up' : 'down'"> </button>

                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">

                  <path x-show="metrics.tickets_trend > 0" fill-rule="evenodd"
                    d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L6.707 7.707a1 1 0 01-1.414 0z"
                    clip-rule="evenodd"></path> <button @click="showNotifications = !showNotifications"
                    class="uiv2-icon-btn relative" <path x-show="metrics.tickets_trend <= 0" fill-rule="evenodd"
                    d="M14.707 12.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 14.586V3a1 1 0 112 0v11.586l2.293-2.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"></path> :aria-expanded="showNotifications">

                </svg> <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                  <span x-text="Math.abs(metrics.tickets_trend) + '%'"></span>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" </span> d="M15
                    17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>

            </div> </svg>

          </div> <span x-show="notifications && notifications.unread_count > 0" <div
            class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">
            class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full flex items-center justify-center">

            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
              viewBox="0 0 24 24"> <span class="text-xs text-white font-medium"
                x-text="notifications.unread_count"></span>

              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
              </path> </span>

          </svg> </button>

        </div>
      </div>

    </div>
  </div>

</div>
</div>



<!-- Active Alerts -->

<div class="metric-card"> <!-- Error Message -->

  <div class="flex items-center justify-between">
    <div x-show="errorMessage" x-transition <div>
      class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"
      role="alert">

      <p class="metric-label">Active Alerts</p>
      <div class="flex">

        <p class="metric-value" x-text="formatNumber(metrics.active_alerts)">0</p>
        <div class="flex-shrink-0">

          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Monitoring prices</p> <svg
            class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">

        </div>
        <path fill-rule="evenodd" <div
          class="w-12 h-12 bg-amber-100 dark:bg-amber-900/20 rounded-lg flex items-center justify-center"> d="M10 18a8
          8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10
          11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"

          <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
            viewBox="0 0 24 24"> clip-rule="evenodd">
        </path>

        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path> </svg>

        </svg>
      </div>

    </div>
    <div class="ml-3">

    </div>
    <p class="text-sm font-medium text-red-800 dark:text-red-200" x-text="errorMessage"></p>

  </div>
</div>

<div class="ml-auto pl-3">

  <!-- Events Monitored --> <button @click="errorMessage = ''" <div class="metric-card">
    class="text-red-800 dark:text-red-200 hover:text-red-600 dark:hover:text-red-400">

    <div class="flex items-center justify-between"> <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">

        <div>
          <path fill-rule="evenodd" <p class="metric-label">Events Monitored</p> d="M4.293 4.293a1 1 0 011.414 0L10
            8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1
            0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"

            <p class="metric-value" x-text="formatNumber(metrics.events_monitored)">0</p> clip-rule="evenodd">
          </path>

          <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Unique events</p>
      </svg>

    </div>
  </button>

  <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center"> </div>

  <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
    viewBox="0 0 24 24">
</div>

<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
</path>
</div>

</svg>

</div> <!-- Key Metrics Grid -->

</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">

</div> <!-- Available Tickets -->

<div class="metric-card">

  <!-- Total Savings -->
  <div class="flex items-center justify-between">

    <div class="metric-card">
      <div>

        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Available Tickets</p>

          <div>
            <div class="flex items-baseline mt-2">

              <p class="metric-label">Total Savings</p>
              <p class="metric-value metric-gradient" x-text="formatNumber(statistics?.available_tickets || 0)">0</p>

              <p class="metric-value text-emerald-600 dark:text-emerald-400"
                x-text="formatCurrency(metrics.total_savings)">$0</p> <span class="trend-indicator trend-up ml-2"
                x-show="statistics?.trend_indicators?.tickets_trend > 0">

                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">This month</p> <svg class="w-3 h-3 mr-1"
                  fill="currentColor" viewBox="0 0 20 20">

            </div>
            <path fill-rule="evenodd" <div
              class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/20 rounded-lg flex items-center justify-center">
              d="M5.293 7.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2
              0V5.414L6.707 7.707a1 1 0 01-1.414 0z"

              <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24"> clip-rule="evenodd">
            </path>

            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
            </path> </svg>

            </svg> <span x-text="statistics?.trend_indicators?.tickets_change + '%'"></span>

          </div> </span>

        </div>
      </div>

    </div>
  </div>

</div>
<div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/20 rounded-lg flex items-center justify-center">

  <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" <!-- Main Content Grid
    --> viewBox="0 0 24 24">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">

        <!-- Left Column - Recent Activity -->
      </path>

      <div class="lg:col-span-2 space-y-6">
  </svg>

</div>

<!-- Recent Tickets --> </div>

<div class="dashboard-card p-6"> </div>

<div class="flex items-center justify-between mb-4">

  <h2 class="section-title mb-0">Recent Tickets</h2> <!-- New Today -->

  <a href="{{ route('tickets.main') }}" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
    <div class="metric-card">

      View All → <div class="flex items-center justify-between">

  </a>
  <div>

  </div>
  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">New Today</p>

  <div class="flex items-baseline mt-2">

    <div class="space-y-3" x-show="recentTickets.length > 0">
      <p class="metric-value metric-gradient" x-text="formatNumber(statistics?.new_today || 0)">0</p>

      <template x-for="ticket in recentTickets.slice(0, 5)" :key="ticket.id">
    </div>

    <div class="ticket-item"> </div>

    <div class="flex items-center space-x-3">
      <div class="w-12 h-12 bg-green-100 dark:bg-green-900/20 rounded-lg flex items-center justify-center">

        <div
          class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" <span
            class="text-white text-sm font-semibold" x-text="ticket.event_type?.charAt(0) || 'T'"></span>
            viewBox="0 0 24 24">

        </div>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <div> d="M12 8v4l3 3m6-3a9 9 0 11-18 0
          9 9 0 0118 0z"></path>

        <h4 class="font-medium text-slate-900 dark:text-slate-100" x-text="ticket.title"></h4> </svg>

        <p class="text-sm text-slate-600 dark:text-slate-400"
          x-text="ticket.venue + ' • ' + formatDate(ticket.event_date)"></p>
      </div>

    </div>
  </div>

</div>
</div>

<div class="text-right">

  <p class="font-semibold text-slate-900 dark:text-slate-100" x-text="formatCurrency(ticket.price)"></p>
  <!-- Active Alerts -->

  <p class="text-xs text-slate-500 dark:text-slate-400" x-text="ticket.platform"></p>
  <div class="metric-card">

  </div>
  <div class="flex items-center justify-between">

  </div>
  <div>

    </template>
    <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Active Alerts</p>

  </div>
  <div class="flex items-baseline mt-2">

    <p class="metric-value metric-gradient" x-text="formatNumber(alerts_data?.total_active || 0)">0</p>

    <div x-show="recentTickets.length === 0" class="text-center py-12"> </div>

    <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor"
      viewBox="0 0 24 24">
  </div>

  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
  </path>
  <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg flex items-center justify-center">

    </svg> <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" <h3
      class="mt-4 text-sm font-medium text-slate-900 dark:text-slate-100">No tickets found</h3> viewBox="0 0 24 24">

      <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Start monitoring events to see tickets here.</p>
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <div class="mt-6"> d="M15
        17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>

      <a href="{{ route('tickets.main') }}" class="action-button">
    </svg>

    Browse Tickets </div>

  </a>
</div>

</div>
</div>

</div>

</div> <!-- Events Monitored -->

<div class="metric-card">

  <!-- Price Alerts -->
  <div class="flex items-center justify-between">

    <div class="dashboard-card p-6" x-show="alerts.length > 0">
      <div>

        <h2 class="section-title">Recent Price Alerts</h2>
        <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Events Monitored</p>

        <div class="space-y-3">
          <div class="flex items-baseline mt-2">

            <template x-for="alert in alerts.slice(0, 3)" :key="alert.id">
              <p class="metric-value metric-gradient" x-text="formatNumber(statistics?.unique_events || 0)">0</p>

              <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"> </div>

              <div> </div>

              <h4 class="font-medium text-slate-900 dark:text-slate-100" x-text="alert.event_name"></h4>
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/20 rounded-lg flex items-center justify-center">

                <p class="text-sm text-slate-600 dark:text-slate-400" x-text="alert.message"></p> <svg
                  class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" </div>
                  viewBox="0 0 24 24">

                  <div class="text-right">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <span
                      class="text-sm font-semibold text-blue-600 dark:text-blue-400"
                      x-text="formatCurrency(alert.new_price)"></span> d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458
                      12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477
                      0-8.268-2.943-9.542-7z">

                      <p class="text-xs text-slate-500 dark:text-slate-400" x-text="formatTime(alert.triggered_at)">
                      </p>
                    </path>

                  </div>
                </svg>

              </div>
          </div>

          </template>
        </div>

      </div>
    </div>

  </div>
</div>

</div>

<!-- Main Content Grid -->

<!-- Right Column - Quick Actions & Summary -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  <div class="space-y-6"> <!-- Left Column: Recent Tickets & Recommendations -->

    <div class="lg:col-span-2 space-y-6">

      <!-- Quick Actions --> <!-- Recent Tickets -->

      <div class="dashboard-card p-6">
        <div class="dashboard-section p-6">

          <h2 class="section-title">Quick Actions</h2>
          <div class="flex items-center justify-between mb-4">

            <div class="space-y-3">
              <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Recent Tickets</h2>

              <a href="{{ route('tickets.main') }}"
                class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200 group">
                <a href="{{ route('tickets.main') }}"
                  class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View

                  <div
                    class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center group-hover:bg-blue-600 transition-colors">
                    all
                </a>

                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            </div>

            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>

            </svg>
            <div class="space-y-4" x-show="recent_tickets && recent_tickets.length > 0">

            </div> <template x-for="ticket in recent_tickets.slice(0, 5)" :key="ticket.id">

              <div class="ml-3">
                <div <p class="font-medium text-slate-900 dark:text-slate-100">Browse Tickets</p>
                  class="flex items-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors duration-200">

                  <p class="text-sm text-slate-600 dark:text-slate-400">Find events to monitor</p>
                  <div </div>
                    class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">

                    </a> <span class="text-white text-sm font-bold"
                      x-text="ticket.platform?.charAt(0).toUpperCase() || 'T'"></span>

                    <a href="{{ route('tickets.scraping.index') }}"
                      class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200 group">
                  </div>

                  <div
                    class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center group-hover:bg-amber-600 transition-colors">
                    <div class="ml-4 flex-1">

                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <h4 class="text-sm font-medium text-slate-900 dark:text-white" x-text="ticket.title"></h4>

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9">
                        </path>
                        <p class="text-sm text-slate-600 dark:text-slate-400" </svg>
                          x-text="ticket.venue + ' • ' + formatDate(ticket.event_date)"></p>

                    </div>
                  </div>

                  <div class="ml-3">
                    <div class="text-right">

                      <p class="font-medium text-slate-900 dark:text-slate-100">Create Alert</p>
                      <p class="text-sm font-semibold text-slate-900 dark:text-white"
                        x-text="formatPrice(ticket.price)">

                      <p class="text-sm text-slate-600 dark:text-slate-400">Set up price monitoring</p>
                      </p>

                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400" x-text="ticket.platform"></p>

                    </a>
                  </div>

                </div>

                <a href="{{ route('profile.show') }}"
                  class="flex items-center p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors duration-200 group">
            </template>

            <div
              class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center group-hover:bg-emerald-600 transition-colors">
            </div>

            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              <div x-show="!recent_tickets || recent_tickets.length === 0" class="text-center py-8">

            </svg> <svg class="w-12 h-12 mx-auto text-slate-400 dark:text-slate-600 mb-4" fill="none"
              stroke="currentColor" </div> viewBox="0 0 24 24">

              <div class="ml-3">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" <p
                  class="font-medium text-slate-900 dark:text-slate-100">Profile Settings</p> d="M15 5v2m0 4v2m0 4v2M5
                  5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0
                  002-2V7z">

                  <p class="text-sm text-slate-600 dark:text-slate-400">Manage your account</p>
                </path>

              </div>
            </svg>

            </a>
            <p class="text-slate-500 dark:text-slate-400">No tickets available at the moment</p>

          </div>
        </div>

      </div>
    </div>



    <!-- System Status --> <!-- Personalized Recommendations -->

    <div class="dashboard-card p-6">
      <div class="dashboard-section p-6" x-show="recommendations && recommendations.length > 0">

        <h2 class="section-title">System Status</h2>
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Recommended for You</h2>

        <div class="space-y-4">
          <div class="space-y-3">

            <div class="flex items-center justify-between"> <template x-for="rec in recommendations.slice(0, 3)"
                :key="rec.id">

                <span class="text-sm text-slate-600 dark:text-slate-400">Monitoring Status</span>
                <div <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="systemStatus.monitoring_active ?
                      'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200' :
                      'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'">
                  class="flex items-center p-3 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">

                  <span x-text="systemStatus.monitoring_active ? 'Active' : 'Inactive'"></span>
                  <div class="flex-1">

                    </span>
                    <h4 class="text-sm font-medium text-slate-900 dark:text-white" x-text="rec.title"></h4>

                  </div>
                  <p class="text-xs text-slate-600 dark:text-slate-400 mt-1" x-text="rec.reason"></p>

                  <div class="flex items-center justify-between"> </div>

                  <span class="text-sm text-slate-600 dark:text-slate-400">Last Update</span>
                  <div class="text-right">

                    <span class="text-xs text-slate-500 dark:text-slate-400"
                      x-text="formatTime(systemStatus.last_update)">Just now</span> <span </div>
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-200"

                      <div class="flex items-center justify-between"> x-text="formatPrice(rec.price)">
                    </span>

                    <span class="text-sm text-slate-600 dark:text-slate-400">Active Sources</span>
                  </div>

                  <span class="text-sm font-medium text-slate-900 dark:text-slate-100"
                    x-text="systemStatus.active_sources || 0">0</span>
                </div>

            </div> </template>

          </div>
        </div>

      </div>
    </div>

  </div>
</div>

</div>

</div> <!-- Right Column: Quick Actions & Alerts -->

@endsection <div class="space-y-6">
<!-- Quick Actions -->
<div class="dashboard-section p-6">
  <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Quick Actions</h2>
  <div class="space-y-3">
    <div
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
<div class="dashboard-section p-6" x-show="alerts_data">
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
      class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">Manage alerts →</a>
  </div>
</div>

<!-- System Status -->
<div class="dashboard-section p-6" x-show="system_status">
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

<!-- Notification Panel -->
<div x-show="showNotifications" x-transition
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

  <div x-show="!notifications || !notifications.items || notifications.items.length === 0" class="text-center py-8">
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
  function customerDashboard() {
    return {
      loading: false,

      // Dashboard data
      metrics: {
        available_tickets: @json($statistics['available_tickets'] ?? 0),
        active_alerts: @json($alerts_data['total_active'] ?? 0),
        events_monitored: @json($statistics['unique_events'] ?? 0),
        total_savings: @json($statistics['total_savings'] ?? 0),
        tickets_trend: @json($statistics['trend_indicators']['tickets_change'] ?? 0)
      },

      recentTickets: @json($recent_tickets ?? []),
      alerts: @json($alerts_data['recent'] ?? []),

      systemStatus: {
        monitoring_active: @json($system_status['scraping_active'] ?? true),
        last_update: @json($system_status['last_update'] ?? now()->toISOString()),
        active_sources: @json($system_status['active_sources'] ?? 3)
      },

      init() {
        console.log('Customer Dashboard initialized');
        this.setupAutoRefresh();
      },

      setupAutoRefresh() {
        // Auto-refresh every 2 minutes
        setInterval(() => {
          if (!this.loading) {
            this.refreshData();
          }
        }, 120000);
      },

      async refreshData() {
        this.loading = true;

        try {
          const response = await fetch('/api/dashboard/stats', {
            method: 'GET',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            }
          });

          if (response.ok) {
            const data = await response.json();

            // Update metrics
            if (data.statistics) {
              this.metrics.available_tickets = data.statistics.available_tickets || 0;
              this.metrics.events_monitored = data.statistics.unique_events || 0;
              this.metrics.tickets_trend = data.statistics.trend_indicators?.tickets_change || 0;
            }

            if (data.alerts_data) {
              this.metrics.active_alerts = data.alerts_data.total_active || 0;
              this.alerts = data.alerts_data.recent || [];
            }

            if (data.recent_tickets) {
              this.recentTickets = data.recent_tickets;
            }

            if (data.system_status) {
              this.systemStatus = {
                ...this.systemStatus,
                ...data.system_status
              };
            }

            console.log('Dashboard data refreshed successfully');
          }
        } catch (error) {
          console.error('Failed to refresh dashboard data:', error);
        } finally {
          this.loading = false;
        }
      },

      formatNumber(number) {
        if (typeof number !== 'number') return '0';
        return new Intl.NumberFormat().format(number);
      },

      formatCurrency(amount) {
        if (typeof amount !== 'number') return '$0.00';
        return new Intl.NumberFormat('en-US', {
          style: 'currency',
          currency: 'USD'
        }).format(amount);
      },

      formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const now = new Date();

        if (date.toDateString() === now.toDateString()) {
          return 'Today';
        }

        const tomorrow = new Date(now);
        tomorrow.setDate(tomorrow.getDate() + 1);
        if (date.toDateString() === tomorrow.toDateString()) {
          return 'Tomorrow';
        }

        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
        });
      },

      formatTime(dateString) {
        if (!dateString) return 'Never';

        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);

        if (diffMins < 1) return 'Just now';
        if (diffMins < 60) return `${diffMins}m ago`;
        if (diffHours < 24) return `${diffHours}h ago`;
        if (diffDays < 7) return `${diffDays}d ago`;

        return date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric'
        });
      }
    };
  }
</script>
@endpush@push('scripts')
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
