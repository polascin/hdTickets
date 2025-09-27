@extends('layouts.app-v2')

@section('title', 'Customer Dashboard')
@section('description', 'Modern sports ticket monitoring dashboard with real-time updates')

@push('styles')
  @vite(['resources/css/dashboard-modern.css'])
  <style>
    /* Custom dashboard styles */
    .glass-card {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .trend-up {
      color: #dc2626;
      background-color: #dcfce7;
    }

    .trend-down {
      color: #dc2626;
      background-color: #fee2e2;
    }

    .trend-stable {
      color: #4b5563;
      background-color: #f3f4f6;
    }

    .sidebar {
      transition: transform 0.3s ease-in-out;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.open {
        transform: translateX(0);
      }
    }
  </style>
@endpush

@section('content')
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100" x-data="modernCustomerDashboard()"
    x-init="init()" x-cloak>

    <!-- Mobile Menu Button -->
    <div class="md:hidden fixed top-4 left-4 z-50">
      <button @click="sidebarOpen = !sidebarOpen"
        class="p-2 rounded-lg bg-white shadow-md text-gray-600 hover:text-gray-900">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Sidebar Navigation -->
    <div class="sidebar fixed left-0 top-0 h-full w-64 bg-white shadow-xl z-40 md:transform-none"
      :class="{ 'open': sidebarOpen }">
      <div class="p-6">
        <!-- Logo -->
        <div class="flex items-center mb-8">
          <div
            class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-lg">HD</span>
          </div>
          <span class="ml-3 text-xl font-bold text-gray-900">Tickets</span>
        </div>

        <!-- User Info -->
        <div class="mb-8 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
          <div class="flex items-center">
            <div
              class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold">
              {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div class="ml-3">
              <p class="font-semibold text-gray-900">{{ $user->name }}</p>
              <p class="text-sm text-gray-600">
                @if ($subscription_status['is_active'])
                  {{ $subscription_status['plan_name'] }}
                @else
                  Free Trial
                @endif
              </p>
            </div>
          </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
          <button type="button" @click="activeTab = 'dashboard'"
            class="w-full flex items-center px-4 py-3 text-gray-700 rounded-lg transition-colors text-left"
            :class="activeTab === 'dashboard' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            </svg>
            Dashboard
          </button>

          <button type="button" @click="activeTab = 'tickets'"
            class="w-full flex items-center px-4 py-3 text-gray-700 rounded-lg transition-colors text-left"
            :class="activeTab === 'tickets' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
              </path>
            </svg>
            My Tickets
          </button>

          <button type="button" @click="activeTab = 'alerts'"
            class="w-full flex items-center px-4 py-3 text-gray-700 rounded-lg transition-colors text-left"
            :class="activeTab === 'alerts' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            Alerts
            <span x-show="stats.active_alerts > 0" x-text="stats.active_alerts"
              class="ml-auto bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full"></span>
          </button>

          <button type="button" @click="activeTab = 'recommendations'"
            class="w-full flex items-center px-4 py-3 text-gray-700 rounded-lg transition-colors text-left"
            :class="activeTab === 'recommendations' ? 'bg-blue-50 text-blue-700' : 'hover:bg-gray-50'">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
              </path>
            </svg>
            Insights
          </button>
        </nav>

        <!-- Quick Actions -->
        <div class="mt-8">
          <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Quick Actions</h3>
          <div class="space-y-2">
            @foreach ($quick_actions as $action)
              <a href="{{ $action['url'] }}"
                class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors">
                <div
                  class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 bg-{{ $action['color'] }}-100 text-{{ $action['color'] }}-600">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    @if ($action['icon'] === 'search')
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    @elseif($action['icon'] === 'bell')
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    @elseif($action['icon'] === 'list')
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    @elseif($action['icon'] === 'settings')
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                      </path>
                    @endif
                  </svg>
                </div>
                {{ $action['title'] }}
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="md:ml-64 min-h-screen">
      <!-- Header -->
      <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Sports Ticket Dashboard</h1>
            <p class="text-gray-600 mt-1">Monitor and discover the best ticket deals</p>
          </div>

          <!-- Notifications & Profile -->
          <div class="flex items-center space-x-4">
            <!-- Real-time Status -->
            <div class="flex items-center space-x-2">
              <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse" x-show="isConnected"></div>
              <div class="w-2 h-2 bg-red-400 rounded-full" x-show="!isConnected"></div>
              <span class="text-sm text-gray-500" x-text="isConnected ? 'Connected' : 'Offline'"></span>
            </div>

            <!-- Last Updated -->
            <span class="text-sm text-gray-500" x-text="'Updated ' + lastUpdated"></span>
          </div>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div class="p-6">

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Available Tickets -->
          <div class="stat-card glass-card rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Available Tickets</p>
                <p class="text-3xl font-bold text-gray-900" x-text="formatNumber(stats.available_tickets)">
                  {{ number_format($statistics['available_tickets']) }}
                </p>
                <div class="flex items-center mt-2">
                  <span class="text-sm text-green-600" x-text="'+' + stats.new_today + ' today'">
                    +{{ $statistics['new_today'] }} today
                  </span>
                </div>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
                  </path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Active Alerts -->
          <div class="stat-card glass-card rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Active Alerts</p>
                <p class="text-3xl font-bold text-gray-900" x-text="stats.active_alerts">
                  {{ $statistics['active_alerts'] }}
                </p>
                <div class="flex items-center mt-2">
                  <span class="text-sm text-amber-600" x-text="stats.price_alerts_triggered + ' triggered today'">
                    {{ $statistics['price_alerts_triggered'] ?? 0 }} triggered today
                  </span>
                </div>
              </div>
              <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Events Monitored -->
          <div class="stat-card glass-card rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Events Monitored</p>
                <p class="text-3xl font-bold text-gray-900" x-text="stats.monitored_events">
                  {{ $statistics['monitored_events'] }}
                </p>
                <div class="flex items-center mt-2">
                  <span class="text-sm text-purple-600">
                    {{ number_format($statistics['unique_events']) }} unique events
                  </span>
                </div>
              </div>
              <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Total Savings -->
          <div class="stat-card glass-card rounded-xl p-6 shadow-lg">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Savings</p>
                <p class="text-3xl font-bold text-emerald-600" x-text="'$' + formatNumber(stats.total_savings)">
                  ${{ number_format($statistics['total_savings'], 2) }}
                </p>
                <div class="flex items-center mt-2" x-show="stats.price_trend">
                  <span :class="'text-sm ' + getTrendClass(stats.price_trend?.direction)"
                    x-text="stats.price_trend?.direction + ' ' + stats.price_trend?.percentage + '%'">
                    @if (isset($statistics['price_trend']))
                      {{ $statistics['price_trend']['direction'] }} {{ $statistics['price_trend']['percentage'] }}%
                    @endif
                  </span>
                </div>
              </div>
              <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                  </path>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="space-y-6">

          <!-- Dashboard Tab -->
          <div x-show="activeTab === 'dashboard'" x-transition>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

              <!-- Recent Tickets -->
              <div class="lg:col-span-2">
                <div class="glass-card rounded-xl p-6 shadow-lg">
                  <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Tickets</h2>
                    <button @click="loadTickets()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                      Refresh
                    </button>
                  </div>

                  <div class="space-y-4" x-show="tickets.length > 0">
                    <template x-for="ticket in tickets" :key="ticket.id">
                      <div
                        class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                          <h3 class="font-semibold text-gray-900" x-text="ticket.event_name"></h3>
                          <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                            <span x-text="ticket.venue_name"></span>
                            <span x-text="ticket.event_date"></span>
                            <span x-text="ticket.platform"
                              class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs"></span>
                          </div>
                        </div>
                        <div class="text-right">
                          <div class="text-lg font-bold text-gray-900" x-text="'$' + ticket.price"></div>
                          <div x-show="ticket.discount" class="text-sm text-green-600"
                            x-text="ticket.discount + '% off'"></div>
                          <div class="text-xs text-gray-500" x-text="ticket.time_ago"></div>
                        </div>
                      </div>
                    </template>
                  </div>

                  <div x-show="tickets.length === 0" class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                      viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                      </path>
                    </svg>
                    <p>No tickets found. Check back later!</p>
                  </div>
                </div>
              </div>

              <!-- Sidebar Content -->
              <div class="space-y-6">

                <!-- Active Alerts -->
                <div class="glass-card rounded-xl p-6 shadow-lg">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Alerts</h3>
                  <div class="space-y-3">
                    @foreach ($active_alerts->take(3) as $alert)
                      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex-1">
                          <p class="text-sm font-medium text-gray-900">{{ $alert['title'] }}</p>
                          <p class="text-xs text-gray-600">{{ $alert['created_at'] }}</p>
                        </div>
                        <div
                          class="w-2 h-2 rounded-full {{ $alert['status'] === 'triggered' ? 'bg-red-400' : 'bg-green-400' }}">
                        </div>
                      </div>
                    @endforeach

                    @if ($active_alerts->isEmpty())
                      <p class="text-sm text-gray-500 text-center py-4">No alerts set up yet</p>
                    @endif
                  </div>
                </div>

                <!-- Subscription Status -->
                <div class="glass-card rounded-xl p-6 shadow-lg">
                  <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h3>
                  <div class="space-y-3">
                    <div class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">Plan</span>
                      <span class="text-sm font-medium text-gray-900">{{ $subscription_status['plan_name'] }}</span>
                    </div>

                    @if ($subscription_status['is_active'])
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Next billing</span>
                        <span class="text-sm text-gray-900">{{ $subscription_status['next_billing'] }}</span>
                      </div>
                    @else
                      <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Trial days left</span>
                        <span
                          class="text-sm font-medium text-amber-600">{{ $subscription_status['days_remaining'] ?? 'N/A' }}</span>
                      </div>
                    @endif

                    <div class="pt-3 border-t">
                      <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-600">Alerts used</span>
                        <span class="text-sm text-gray-900">
                          {{ $subscription_status['usage_stats']['alerts_used'] }}/{{ $subscription_status['usage_stats']['alerts_limit'] }}
                        </span>
                      </div>

                      @if ($subscription_status['usage_stats']['alerts_limit'] !== 'unlimited')
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div class="bg-blue-600 h-2 rounded-full"
                            style="width: {{ ($subscription_status['usage_stats']['alerts_used'] / $subscription_status['usage_stats']['alerts_limit']) * 100 }}%">
                          </div>
                        </div>
                      @endif
                    </div>

                    @if (!$subscription_status['is_active'])
                      <a href="{{ route('subscription.plans') }}"
                        class="w-full mt-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:from-blue-700 hover:to-indigo-700 transition-colors text-center block">
                        Upgrade Now
                      </a>
                    @endif
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- Tickets Tab -->
          <div x-show="activeTab === 'tickets'" x-transition>
            <div class="glass-card rounded-xl p-6 shadow-lg">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">My Ticket Monitoring</h2>
                <a href="{{ route('tickets.main') }}"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                  Browse Tickets
                </a>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                  <h3 class="font-semibold text-blue-900">Active Monitors</h3>
                  <p class="text-2xl font-bold text-blue-600" x-text="stats.monitored_events">
                    {{ $statistics['monitored_events'] }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                  <h3 class="font-semibold text-green-900">Available Now</h3>
                  <p class="text-2xl font-bold text-green-600" x-text="stats.available_tickets">
                    {{ $statistics['available_tickets'] }}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                  <h3 class="font-semibold text-purple-900">New Today</h3>
                  <p class="text-2xl font-bold text-purple-600" x-text="stats.new_today">{{ $statistics['new_today'] }}
                  </p>
                </div>
              </div>

              <p class="text-gray-600 text-center py-4">Visit the main tickets page to start monitoring events and set up
                price alerts.</p>
            </div>
          </div>

          <!-- Alerts Tab -->
          <div x-show="activeTab === 'alerts'" x-transition>
            <div class="glass-card rounded-xl p-6 shadow-lg">
              <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Price Alerts & Notifications</h2>
                <a href="{{ route('tickets.alerts.create') }}"
                  class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition-colors">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                  </svg>
                  Create Alert
                </a>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-amber-50 p-4 rounded-lg">
                  <h3 class="font-semibold text-amber-900">Active Alerts</h3>
                  <p class="text-2xl font-bold text-amber-600" x-text="stats.active_alerts">
                    {{ $statistics['active_alerts'] }}</p>
                  <p class="text-sm text-amber-700">Monitoring price changes</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                  <h3 class="font-semibold text-red-900">Triggered Today</h3>
                  <p class="text-2xl font-bold text-red-600" x-text="stats.price_alerts_triggered">
                    {{ $statistics['price_alerts_triggered'] ?? 0 }}</p>
                  <p class="text-sm text-red-700">Price targets reached</p>
                </div>
              </div>

              @if ($active_alerts->isNotEmpty())
                <div class="space-y-3">
                  <h3 class="font-semibold text-gray-900">Recent Alerts</h3>
                  @foreach ($active_alerts->take(5) as $alert)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                      <div>
                        <p class="font-medium text-gray-900">{{ $alert['title'] }}</p>
                        <p class="text-sm text-gray-600">{{ $alert['created_at'] }}</p>
                      </div>
                      <div class="flex items-center">
                        <span
                          class="w-2 h-2 rounded-full {{ $alert['status'] === 'triggered' ? 'bg-red-400' : 'bg-green-400' }} mr-2"></span>
                        <span
                          class="text-sm {{ $alert['status'] === 'triggered' ? 'text-red-600' : 'text-green-600' }}">{{ ucfirst($alert['status']) }}</span>
                      </div>
                    </div>
                  @endforeach
                </div>
              @else
                <div class="text-center py-8">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                  </svg>
                  <p class="text-gray-500">No price alerts set up yet. Create your first alert to get notified when
                    ticket prices drop!</p>
                </div>
              @endif
            </div>
          </div>

          <!-- Insights Tab -->
          <div x-show="activeTab === 'recommendations'" x-transition>
            <div class="glass-card rounded-xl p-6 shadow-lg">
              <h2 class="text-lg font-semibold text-gray-900 mb-6">Market Insights & Recommendations</h2>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                  <h3 class="font-semibold text-gray-900 mb-4">Price Trends</h3>
                  <div class="bg-gradient-to-r from-emerald-50 to-blue-50 p-4 rounded-lg">
                    <div class="flex items-center justify-between">
                      <span class="text-sm font-medium text-gray-600">Market Average</span>
                      @if (isset($statistics['price_trend']))
                        <span
                          class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium
                          {{ $statistics['price_trend']['direction'] === 'up'
                              ? 'bg-red-100 text-red-800'
                              : ($statistics['price_trend']['direction'] === 'down'
                                  ? 'bg-green-100 text-green-800'
                                  : 'bg-gray-100 text-gray-800') }}">
                          {{ $statistics['price_trend']['direction'] === 'up' ? '↗' : ($statistics['price_trend']['direction'] === 'down' ? '↘' : '→') }}
                          {{ $statistics['price_trend']['percentage'] }}%
                        </span>
                      @endif
                    </div>
                    <p class="text-lg font-bold text-gray-900 mt-2">
                      ${{ number_format($statistics['total_savings'], 2) }}</p>
                    <p class="text-sm text-gray-600">Your total potential savings</p>
                  </div>
                </div>

                <div>
                  <h3 class="font-semibold text-gray-900 mb-4">Popular Events</h3>
                  <div class="space-y-2">
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                      <span class="text-sm font-medium text-gray-900">{{ $statistics['unique_events'] }} unique events
                        available</span>
                      <span class="text-xs text-gray-500">Updated recently</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                      <span class="text-sm font-medium text-blue-900">{{ $statistics['monitored_events'] }} events
                        monitored</span>
                      <span class="text-xs text-blue-600">Active tracking</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg">
                <h3 class="font-semibold text-blue-900 mb-2">💡 Smart Recommendations</h3>
                <p class="text-blue-800 text-sm">Based on your activity, consider setting up alerts for events with high
                  price volatility to maximize your savings potential.</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div x-show="isLoading" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 shadow-xl">
        <div class="flex items-center space-x-3">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          <span class="text-gray-900">Loading...</span>
        </div>
      </div>
    </div>

  </div>
@endsection

@push('scripts')
  @vite(['resources/js/dashboard/modern-customer-dashboard.js'])
@endpush
