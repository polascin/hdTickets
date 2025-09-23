@extends('layouts.app-v2')

@section('title', 'Sports Events Dashboard')

@section('meta')
  <meta name="dashboard-realtime-url" content="{{ route('api.dashboard.realtime') }}">
  <meta name="dashboard-api-base" content="{{ url('/api/v1/dashboard') }}">
  <meta name="dashboard-refresh-interval" content="120000">
  <meta name="user-role" content="{{ Auth::user()->role }}">
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('resources/css/dashboard-enhanced.css') }}">
  <style>
    :root {
      --dashboard-primary: #3b82f6;
      --dashboard-secondary: #8b5cf6;
      --dashboard-success: #10b981;
      --dashboard-warning: #f59e0b;
      --dashboard-error: #ef4444;
      --dashboard-glass: rgba(255, 255, 255, 0.1);
      --dashboard-glass-border: rgba(255, 255, 255, 0.2);
    }

    .dashboard-glass {
      background: var(--dashboard-glass);
      backdrop-filter: blur(10px);
      border: 1px solid var(--dashboard-glass-border);
    }

    .stats-card {
      background: var(--dashboard-glass);
      backdrop-filter: blur(10px);
      border: 1px solid var(--dashboard-glass-border);
      border-radius: 0.75rem;
      padding: 1.5rem;
      transition-property: all;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 300ms;
      cursor: default;
    }

    .stats-card:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .stats-card-icon {
      width: 3rem;
      height: 3rem;
      border-radius: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
    }

    .dashboard-ticket-card {
      background: var(--dashboard-glass);
      backdrop-filter: blur(10px);
      border: 1px solid var(--dashboard-glass-border);
      border-radius: 0.5rem;
      padding: 1rem;
      transition-property: all;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 200ms;
      cursor: pointer;
      border-left-width: 4px;
    }

    .dashboard-ticket-card:hover {
      background-color: rgba(255, 255, 255, 0.15);
    }

    .quick-action-btn {
      background: var(--dashboard-glass);
      backdrop-filter: blur(10px);
      border: 1px solid var(--dashboard-glass-border);
      border-radius: 0.5rem;
      padding: 1rem;
      text-align: center;
      transition-property: all;
      transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
      transition-duration: 200ms;
      cursor: pointer;
    }

    .quick-action-btn:hover {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .skeleton {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
      background-color: rgb(226 232 240);
      border-radius: 0.25rem;
    }

    .dark .skeleton {
      background-color: rgb(51 65 85);
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: .5;
      }
    }

    @media (prefers-reduced-motion: reduce) {

      .transition-all,
      .animate-pulse {
        transition: none;
        animation: none;
      }
    }
  </style>
@endpush

@section('content')
  <div id="customer-dashboard" x-data="customerDashboard()" x-init="init()"
    class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">

    <!-- Header Section -->
    <header
      class="sticky top-0 z-40 bg-white/80 backdrop-blur-sm border-b border-slate-200/60 dark:bg-slate-900/80 dark:border-slate-700/60"
      role="banner">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Welcome Section -->
          <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
              <div
                class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                <span class="text-white font-semibold text-sm">
                  {{ substr(Auth::user()->name, 0, 1) }}{{ substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1) }}
                </span>
              </div>
            </div>
            <div>
              <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                Welcome, {{ Auth::user()->first_name ?? explode(' ', Auth::user()->name)[0] }}
              </h1>
              <p class="text-sm text-slate-600 dark:text-slate-400" x-text="getCurrentTime()"></p>
            </div>
          </div>

          <!-- Action Bar -->
          <nav class="flex items-center space-x-3" aria-label="Dashboard actions">
            <!-- Refresh Button -->
            <button @click="refreshData()" :disabled="loading" :class="{ 'animate-spin': loading }"
              class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
              aria-label="Refresh dashboard data" title="Refresh data">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>

            <!-- Notifications -->
            <button @click="showNotifications = !showNotifications"
              class="relative p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
              aria-label="View notifications" :aria-expanded="showNotifications">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
              </svg>
              <span x-show="notifications && notifications.unread_count > 0"
                class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full flex items-center justify-center">
                <span class="text-xs text-white font-medium" x-text="notifications.unread_count"></span>
              </span>
            </button>

            <!-- Settings Link -->
            <a href="{{ route('profile.edit') }}"
              class="p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-md transition-colors duration-200 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800"
              aria-label="Account settings">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </a>
          </nav>
        </div>
      </div>
    </header>

    <!-- Error Notification -->
    <div x-show="errorMessage" x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 transform translate-y-2"
      x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100 transform translate-y-0"
      x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed top-20 right-4 z-50 max-w-md w-full"
      role="alert" aria-live="polite">
      <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 shadow-lg">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium text-red-800 dark:text-red-200" x-text="errorMessage"></p>
          </div>
          <button @click="errorMessage = ''" class="ml-4 inline-flex text-red-400 hover:text-red-500">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Main Dashboard Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" role="main">

      <!-- Statistics Cards Grid -->
      <section aria-labelledby="stats-heading" class="mb-8">
        <h2 id="stats-heading" class="sr-only">Dashboard Statistics</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">

          <!-- Available Tickets -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  Available Tickets
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.available_tickets) || '0'">
                  {{ $statistics['available_tickets'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-blue-500 to-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- New Today -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  New Today
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.new_today) || '0'">
                  {{ $statistics['new_today'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-emerald-500 to-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Monitored Events -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  Events
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.monitored_events) || '0'">
                  {{ $statistics['monitored_events'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-cyan-500 to-cyan-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Active Alerts -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  Alerts
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.active_alerts) || '0'">
                  {{ $statistics['active_alerts'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-amber-500 to-amber-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Price Alerts -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  Price Alerts
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.price_alerts) || '0'">
                  {{ $statistics['price_alerts'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-fuchsia-500 to-fuchsia-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Triggered Today -->
          <div class="stats-card">
            <div class="flex items-center justify-between">
              <div class="min-w-0 flex-1">
                <p class="text-xs uppercase font-medium tracking-wide text-slate-500 dark:text-slate-400">
                  Triggered
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-50" x-show="!loading"
                  x-text="formatNumber(statistics.triggered_today) || '0'">
                  {{ $statistics['triggered_today'] ?? '0' }}
                </p>
                <div x-show="loading" class="mt-1 h-8 w-16 skeleton"></div>
              </div>
              <div class="stats-card-icon bg-gradient-to-br from-rose-500 to-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>
          </div>

        </div>
      </section>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">

        <!-- Left Column: Recent Tickets -->
        <div class="xl:col-span-3">
          <section aria-labelledby="recent-tickets-heading">
            <div class="dashboard-glass rounded-xl p-6">
              <div class="flex items-center justify-between mb-6">
                <h2 id="recent-tickets-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                  Recent Sports Event Tickets
                </h2>
                <a href="{{ route('tickets.scraping.index') }}"
                  class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                  View all tickets →
                </a>
              </div>

              <!-- Loading State -->
              <div x-show="loading" class="space-y-4">
                <template x-for="i in 5" :key="i">
                  <div class="dashboard-ticket-card border-slate-300">
                    <div class="flex items-center justify-between">
                      <div class="flex-1 space-y-2">
                        <div class="h-5 skeleton w-64"></div>
                        <div class="h-4 skeleton w-48"></div>
                        <div class="h-3 skeleton w-32"></div>
                      </div>
                      <div class="text-right space-y-2">
                        <div class="h-6 skeleton w-16"></div>
                        <div class="h-4 skeleton w-20"></div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

              <!-- Empty State -->
              <div x-show="!loading && recent_tickets.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900 dark:text-slate-100">
                  No tickets available
                </h3>
                <p class="mt-2 text-slate-500 dark:text-slate-400">
                  Check back later for new sports event tickets.
                </p>
                <div class="mt-6">
                  <a href="{{ route('tickets.scraping.index') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Browse All Tickets
                  </a>
                </div>
              </div>

              <!-- Tickets List -->
              <div x-show="!loading && recent_tickets.length > 0" class="space-y-4">
                <template x-for="ticket in recent_tickets" :key="ticket.id">
                  <div class="dashboard-ticket-card border-blue-300"
                    :class="{
                        'border-red-300': ticket.demand_level === 'high',
                        'border-yellow-300': ticket.demand_level === 'medium',
                        'border-green-300': ticket.demand_level === 'low'
                    }">
                    <div class="flex items-center justify-between">
                      <div class="flex-1 min-w-0">
                        <h3 class="text-base font-medium text-slate-900 dark:text-slate-100 truncate"
                          x-text="ticket.title || 'Sports Event'">
                        </h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 truncate"
                          x-text="ticket.venue || 'TBD'">
                        </p>
                        <div class="mt-2 flex items-center space-x-4 text-xs text-slate-500 dark:text-slate-400">
                          <span x-text="ticket.sport || 'Sports'"></span>
                          <span>•</span>
                          <span x-text="ticket.platform || 'Unknown'"></span>
                          <span>•</span>
                          <span x-text="ticket.scraped_at || 'Recently'"></span>
                        </div>
                      </div>
                      <div class="text-right flex-shrink-0">
                        <div class="text-lg font-bold text-slate-900 dark:text-slate-50">
                          <span x-show="ticket.min_price" x-text="'$' + ticket.min_price"></span>
                          <span x-show="!ticket.min_price">TBD</span>
                        </div>
                        <div class="mt-1 flex items-center justify-end space-x-2">
                          <span x-show="ticket.event_date"
                            class="inline-flex items-center px-2 py-1 rounded bg-slate-100 dark:bg-slate-700 text-xs font-medium text-slate-600 dark:text-slate-300"
                            x-text="ticket.event_date">
                          </span>
                          <span x-show="ticket.is_high_demand"
                            class="inline-flex items-center px-2 py-1 rounded bg-red-100 dark:bg-red-900/20 text-xs font-medium text-red-600 dark:text-red-400">
                            High Demand
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </template>
              </div>

            </div>
          </section>
        </div>

        <!-- Right Column: Quick Actions & System Info -->
        <div class="xl:col-span-1 space-y-6">

          <!-- Quick Actions -->
          <section aria-labelledby="quick-actions-heading">
            <div class="dashboard-glass rounded-xl p-6">
              <h2 id="quick-actions-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                Quick Actions
              </h2>
              <div class="space-y-3">
                @if (isset($quick_actions))
                  @foreach ($quick_actions as $action)
                    <a href="{{ $action['url'] }}" class="quick-action-btn block">
                      <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                          <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            @switch($action['icon'])
                              @case('search')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                              @break

                              @case('bell')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                              @break

                              @case('history')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                              @break

                              @case('settings')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                              @break
                            @endswitch
                          </svg>
                        </div>
                        <div class="flex-1 text-left">
                          <p class="font-medium text-slate-900 dark:text-slate-100">{{ $action['label'] }}</p>
                          <p class="text-xs text-slate-500 dark:text-slate-400">{{ $action['description'] }}</p>
                        </div>
                      </div>
                    </a>
                  @endforeach
                @else
                  <!-- Default Quick Actions -->
                  <a href="{{ route('tickets.scraping.index') }}" class="quick-action-btn block">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                      </div>
                      <div class="flex-1 text-left">
                        <p class="font-medium text-slate-900 dark:text-slate-100">Find Tickets</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Browse available sports event tickets</p>
                      </div>
                    </div>
                  </a>

                  <a href="{{ route('tickets.alerts.index') }}" class="quick-action-btn block">
                    <div class="flex items-center space-x-3">
                      <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor"
                          viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                      </div>
                      <div class="flex-1 text-left">
                        <p class="font-medium text-slate-900 dark:text-slate-100">My Alerts</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Manage your price alerts</p>
                      </div>
                    </div>
                  </a>
                @endif
              </div>
            </div>
          </section>

          <!-- System Status -->
          <section aria-labelledby="system-status-heading">
            <div class="dashboard-glass rounded-xl p-6">
              <h2 id="system-status-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                System Status
              </h2>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Scraping</span>
                  <span class="flex items-center text-sm">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"
                      x-show="system_status && system_status.scraping_active"></span>
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"
                      x-show="system_status && !system_status.scraping_active"></span>
                    <span x-text="system_status && system_status.scraping_active ? 'Active' : 'Inactive'"
                      class="text-slate-900 dark:text-slate-100">Active</span>
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Database</span>
                  <span class="flex items-center text-sm">
                    <span class="w-2 h-2 bg-green-500 rounded-full mr-2"
                      x-show="system_status && system_status.database_healthy"></span>
                    <span class="w-2 h-2 bg-red-500 rounded-full mr-2"
                      x-show="system_status && !system_status.database_healthy"></span>
                    <span x-text="system_status && system_status.database_healthy ? 'Healthy' : 'Issues'"
                      class="text-slate-900 dark:text-slate-100">Healthy</span>
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-sm text-slate-600 dark:text-slate-400">Last Updated</span>
                  <span class="text-sm text-slate-900 dark:text-slate-100" x-text="formatTime(lastUpdate)">
                    {{ $generated_at ?? now()->format('H:i') }}
                  </span>
                </div>
              </div>
            </div>
          </section>

          <!-- Subscription Info -->
          @if (isset($subscription_data))
            <section aria-labelledby="subscription-heading">
              <div class="dashboard-glass rounded-xl p-6">
                <h2 id="subscription-heading" class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-4">
                  Subscription
                </h2>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Plan</span>
                    <span
                      class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $subscription_data['plan_name'] }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600 dark:text-slate-400">Usage</span>
                    <span class="text-sm text-slate-900 dark:text-slate-100">
                      {{ $subscription_data['current_usage'] }} / {{ $subscription_data['monthly_limit'] }}
                    </span>
                  </div>
                  @if ($subscription_data['usage_percentage'] > 0)
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                      <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        style="width: {{ min(100, $subscription_data['usage_percentage']) }}%">
                      </div>
                    </div>
                  @endif
                </div>
              </div>
            </section>
          @endif

        </div>
      </div>

    </main>
  </div>

  <script>
    window.__DASHBOARD_INITIAL__ = {
      statistics: {!! json_encode($statistics ?? []) !!},
      recent_tickets: {!! json_encode($recent_tickets ?? []) !!},
      system_status: {!! json_encode($system_status ?? null) !!},
      notifications: @json($notifications ?? (object) []),
    };
  </script>
  @vite(['resources/js/dashboard/index.js'])
@endsection
