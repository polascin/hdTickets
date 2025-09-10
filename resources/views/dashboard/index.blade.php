@extends('layouts.app-v2')
@section('title', 'Unified Dashboard')
@section('content')
  <div class="min-h-screen bg-transparent" x-data="mainDashboard()">
    {{-- Header with Role-Specific Welcome --}}
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">
              @if (auth()->user()->role === 'customer')
                Welcome back, {{ auth()->user()->first_name ?? auth()->user()->name }}!
              @elseif(auth()->user()->role === 'agent')
                Professional Dashboard
              @elseif(auth()->user()->role === 'admin')
                System Administration
              @endif
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              @if (auth()->user()->role === 'customer')
                Find amazing deals on sports tickets and manage your alerts
              @elseif(auth()->user()->role === 'agent')
                Advanced ticket monitoring and unlimited access tools
              @elseif(auth()->user()->role === 'admin')
                Monitor system performance and manage platform operations
              @endif
            </p>
          </div>

          <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ auth()->user()->role === 'agent' ? 'purple' : (auth()->user()->role === 'admin' ? 'gray' : 'indigo') }}-100 text-{{ auth()->user()->role === 'agent' ? 'purple' : (auth()->user()->role === 'admin' ? 'gray' : 'indigo') }}-800">
                {{ ucfirst(auth()->user()->role) }}
              </span>
            </div>

            @if (auth()->user()->role === 'customer')
              <a href="{{ route('subscription.plans') }}"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                @if (auth()->user()->hasActiveSubscription())
                  Manage Subscription
                @else
                  Upgrade Now
                @endif
              </a>
            @endif
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      {{-- Quick Stats Cards --}}
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @if (auth()->user()->role === 'customer')
          {{-- Customer Stats --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Tickets This Month</dt>
                  <dd class="text-lg font-medium text-gray-900">{{ auth()->user()->getMonthlyTicketUsage() ?? 0 }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Saved</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="'$' + totalSaved.toFixed(2)">$0.00</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Active Alerts</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="activeAlerts">0</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Watchlist</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="watchlistItems">0</dd>
                </dl>
              </div>
            </div>
          </div>
        @elseif(auth()->user()->role === 'agent')
          {{-- Agent Stats --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Monitored Events</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="monitoredEvents">0</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Success Rate</dt>
                  <dd class="text-lg font-medium text-gray-900">98.5%</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Response Time</dt>
                  <dd class="text-lg font-medium text-gray-900">1.2s</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Tickets Acquired</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="totalTicketsAcquired">0</dd>
                </dl>
              </div>
            </div>
          </div>
        @elseif(auth()->user()->role === 'admin')
          {{-- Admin Stats --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="totalUsers">0</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Monthly Revenue</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="'$' + monthlyRevenue.toLocaleString()">$0</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">System Status</dt>
                  <dd class="text-lg font-medium text-green-600">Healthy</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Scraped Today</dt>
                  <dd class="text-lg font-medium text-gray-900" x-text="scrapedToday.toLocaleString()">0</dd>
                </dl>
              </div>
            </div>
          </div>
        @endif
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-8">
          {{-- Featured Tickets --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-semibold text-gray-900">
                @if (auth()->user()->role === 'customer')
                  Hot Deals & Recommendations
                @elseif(auth()->user()->role === 'agent')
                  High-Value Opportunities
                @else
                  Platform Overview
                @endif
              </h2>
              <a href="/tickets" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                View All →
              </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <template x-for="ticket in featuredTickets" :key="ticket.id">
                <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow cursor-pointer"
                  @click="viewTicket(ticket.id)">
                  <div class="flex items-start justify-between mb-3">
                    <div>
                      <h3 class="font-semibold text-gray-900" x-text="ticket.event_title"></h3>
                      <p class="text-sm text-gray-500" x-text="ticket.venue + ' • ' + ticket.date"></p>
                    </div>
                    <div class="text-right">
                      <div class="text-lg font-bold text-gray-900" x-text="'$' + ticket.price"></div>
                      <div x-show="ticket.original_price && ticket.original_price > ticket.price"
                        class="text-xs text-gray-400 line-through" x-text="'$' + ticket.original_price"></div>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <span
                      :class="`px-2 py-1 rounded-full text-xs font-medium ${ticket.status === 'available' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}`"
                      x-text="ticket.status === 'available' ? 'Available' : 'Limited'"></span>
                    <div class="flex items-center space-x-2">
                      <span class="text-xs text-gray-500" x-text="ticket.quantity + ' left'"></span>
                      @if (auth()->user()->role === 'customer' || auth()->user()->role === 'agent')
                        <button @click.stop="addToWatchlist(ticket.id)"
                          class="text-gray-400 hover:text-red-500 transition-colors">
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                          </svg>
                        </button>
                      @endif
                    </div>
                  </div>
                </div>
              </template>
            </div>

            <div x-show="featuredTickets.length === 0" class="text-center py-12">
              <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
              </svg>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets available</h3>
              <p class="text-gray-500">Check back soon for new opportunities!</p>
            </div>
          </div>

          {{-- Recent Activity --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Recent Activity</h2>
              <button @click="refreshActivity()" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">
                Refresh
              </button>
            </div>

            <div class="space-y-4">
              <template x-for="activity in recentActivity" :key="activity.id">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <div
                      :class="`w-8 h-8 rounded-full flex items-center justify-center ${activity.type === 'purchase' ? 'bg-green-100' : activity.type === 'alert' ? 'bg-yellow-100' : 'bg-blue-100'}`">
                      <svg class="w-4 h-4"
                        :class="`${activity.type === 'purchase' ? 'text-green-600' : activity.type === 'alert' ? 'text-yellow-600' : 'text-blue-600'}`"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="activity.type === 'purchase'" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        <path x-show="activity.type === 'alert'" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
                        <path x-show="activity.type === 'watchlist'" stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-900" x-text="activity.description"></p>
                    <p class="text-xs text-gray-500" x-text="activity.time_ago"></p>
                  </div>
                </div>
              </template>
            </div>

            <div x-show="recentActivity.length === 0" class="text-center py-8">
              <p class="text-gray-500">No recent activity</p>
            </div>
          </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1 space-y-6">
          @if (auth()->user()->role === 'customer')
            {{-- Subscription Status for Customers --}}
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Subscription</h3>
                @if (auth()->user()->hasActiveSubscription())
                  <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Active</span>
                @elseif(auth()->user()->isInFreeTrial())
                  <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Trial</span>
                @else
                  <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">Expired</span>
                @endif
              </div>

              @if (auth()->user()->hasActiveSubscription() || auth()->user()->isInFreeTrial())
                <div class="space-y-3">
                  @php
                    $usage = auth()->user()->getMonthlyTicketUsage();
                    $limit = auth()->user()->getMonthlyTicketLimit();
                    $percentage = $limit > 0 ? ($usage / $limit) * 100 : 0;
                  @endphp
                  <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                      <span>Monthly Usage</span>
                      <span>{{ $usage }}/{{ $limit }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                        style="width: {{ min($percentage, 100) }}%"></div>
                    </div>
                  </div>

                  @if (auth()->user()->isInFreeTrial())
                    <div class="text-sm text-gray-600">
                      Trial ends: {{ auth()->user()->created_at->addDays(7)->format('M j, Y') }}
                    </div>
                  @endif
                </div>
              @endif

              <div class="mt-4">
                <a href="{{ route('subscription.plans') }}"
                  class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors text-center block">
                  @if (auth()->user()->hasActiveSubscription())
                    Manage Subscription
                  @else
                    Subscribe Now
                  @endif
                </a>
              </div>
            </div>
          @endif

          {{-- Quick Actions --}}
          <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="space-y-3">
              @if (auth()->user()->role === 'customer' || auth()->user()->role === 'agent')
                <a href="/tickets/search"
                  class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors text-center block">
                  Find Tickets
                </a>
                <a href="/alerts/create"
                  class="w-full bg-yellow-100 text-yellow-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-yellow-200 transition-colors text-center block">
                  Create Alert
                </a>
                <a href="/watchlist"
                  class="w-full bg-purple-100 text-purple-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors text-center block">
                  View Watchlist
                </a>
              @endif

              @if (auth()->user()->role === 'admin')
                <a href="/admin/users"
                  class="w-full bg-blue-100 text-blue-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-200 transition-colors text-center block">
                  Manage Users
                </a>
                <a href="/admin/system"
                  class="w-full bg-green-100 text-green-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-200 transition-colors text-center block">
                  System Health
                </a>
                <a href="/admin/reports"
                  class="w-full bg-purple-100 text-purple-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-purple-200 transition-colors text-center block">
                  View Reports
                </a>
              @endif

              <a href="/settings"
                class="w-full bg-gray-100 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors text-center block">
                Account Settings
              </a>
            </div>
          </div>

          {{-- Price Alerts (for customers and agents) --}}
          @if (auth()->user()->role === 'customer' || auth()->user()->role === 'agent')
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Alerts</h3>
                <a href="/alerts" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium">View All</a>
              </div>

              <div class="space-y-3">
                <template x-for="alert in recentAlerts" :key="alert.id">
                  <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start justify-between">
                      <div class="flex-1">
                        <p class="text-sm font-medium text-yellow-800" x-text="alert.event_title"></p>
                        <p class="text-xs text-yellow-600 mt-1" x-text="alert.message"></p>
                      </div>
                      <span class="text-xs text-yellow-600" x-text="alert.time_ago"></span>
                    </div>
                  </div>
                </template>

                <div x-show="recentAlerts.length === 0" class="text-center py-6">
                  <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
                  </svg>
                  <p class="text-sm text-gray-500">No recent alerts</p>
                </div>
              </div>
            </div>
          @endif

          {{-- System Status (for admins) --}}
          @if (auth()->user()->role === 'admin')
            <div class="bg-white rounded-2xl shadow-lg p-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4">System Monitor</h3>

              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">API Status</span>
                  <span class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Online</span>
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Database</span>
                  <span class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Healthy</span>
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Scrapers</span>
                  <span class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></div>
                    <span class="text-sm text-yellow-600">3/4 Active</span>
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-600">Queue</span>
                  <span class="flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-green-600">Processing</span>
                  </span>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <script>
    function mainDashboard() {
      return {
        // Stats data
        totalSaved: 0,
        activeAlerts: 0,
        watchlistItems: 0,
        monitoredEvents: 0,
        totalTicketsAcquired: 0,
        totalUsers: 0,
        monthlyRevenue: 0,
        scrapedToday: 0,

        // Featured tickets
        featuredTickets: [{
            id: 1,
            event_title: 'Lakers vs Warriors',
            venue: 'Crypto.com Arena',
            date: 'Dec 15, 2024',
            price: 89,
            original_price: 120,
            status: 'available',
            quantity: 8
          },
          {
            id: 2,
            event_title: 'Cowboys vs Giants',
            venue: 'AT&T Stadium',
            date: 'Dec 18, 2024',
            price: 145,
            original_price: null,
            status: 'limited',
            quantity: 3
          }
        ],

        // Recent activity
        recentActivity: [{
            id: 1,
            type: 'purchase',
            description: 'Successfully purchased Lakers vs Warriors tickets',
            time_ago: '2 hours ago'
          },
          {
            id: 2,
            type: 'alert',
            description: 'Price drop alert: Cowboys vs Giants',
            time_ago: '4 hours ago'
          },
          {
            id: 3,
            type: 'watchlist',
            description: 'Added Chiefs vs Bills to watchlist',
            time_ago: '1 day ago'
          }
        ],

        // Recent alerts
        recentAlerts: [{
            id: 1,
            event_title: 'Lakers vs Warriors',
            message: 'Price dropped to $89 (-26%)',
            time_ago: '2h ago'
          },
          {
            id: 2,
            event_title: 'Cowboys vs Giants',
            message: 'New tickets available',
            time_ago: '4h ago'
          }
        ],

        init() {
          this.loadDashboardData();
          this.startRealTimeUpdates();
        },

        async loadDashboardData() {
          try {
            // Load dashboard statistics
            const response = await fetch('/api/v1/dashboard/stats', {
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              }
            });

            if (response.ok) {
              const data = await response.json();

              // Update stats based on user role
              @if (auth()->user()->role === 'customer')
                this.totalSaved = data.total_saved || 0;
                this.activeAlerts = data.active_alerts || 0;
                this.watchlistItems = data.watchlist_items || 0;
              @elseif (auth()->user()->role === 'agent')
                this.monitoredEvents = data.monitored_events || 0;
                this.totalTicketsAcquired = data.total_tickets_acquired || 0;
              @elseif (auth()->user()->role === 'admin')
                this.totalUsers = data.total_users || 0;
                this.monthlyRevenue = data.monthly_revenue || 0;
                this.scrapedToday = data.scraped_today || 0;
              @endif

              // Update featured tickets and activity
              if (data.featured_tickets) {
                this.featuredTickets = data.featured_tickets;
              }
              if (data.recent_activity) {
                this.recentActivity = data.recent_activity;
              }
              if (data.recent_alerts) {
                this.recentAlerts = data.recent_alerts;
              }
            }
          } catch (error) {
            console.error('Error loading dashboard data:', error);
          }
        },

        startRealTimeUpdates() {
          // Refresh dashboard data every 5 minutes
          setInterval(() => {
            this.loadDashboardData();
          }, 300000); // 5 minutes

          // Real-time updates via WebSocket (if available)
          if (typeof Echo !== 'undefined') {
            Echo.private(`user.{{ auth()->id() }}`)
              .listen('PriceDropAlert', (e) => {
                this.recentAlerts.unshift({
                  id: Date.now(),
                  event_title: e.event_title,
                  message: e.message,
                  time_ago: 'just now'
                });

                // Keep only latest 5 alerts
                if (this.recentAlerts.length > 5) {
                  this.recentAlerts = this.recentAlerts.slice(0, 5);
                }
              })
              .listen('TicketPurchased', (e) => {
                this.recentActivity.unshift({
                  id: Date.now(),
                  type: 'purchase',
                  description: `Successfully purchased ${e.event_title} tickets`,
                  time_ago: 'just now'
                });

                // Keep only latest 10 activities
                if (this.recentActivity.length > 10) {
                  this.recentActivity = this.recentActivity.slice(0, 10);
                }
              });
          }
        },

        viewTicket(ticketId) {
          window.location.href = `/tickets/${ticketId}`;
        },

        addToWatchlist(ticketId) {
          this.makeRequest('POST', '/api/v1/watchlist', {
              ticket_id: ticketId
            })
            .then(data => {
              if (data.success) {
                this.showToast('Added to watchlist!', 'success');
                this.watchlistItems++;
              } else {
                this.showToast(data.message || 'Failed to add to watchlist', 'error');
              }
            })
            .catch(() => {
              this.showToast('An error occurred', 'error');
            });
        },

        refreshActivity() {
          this.loadDashboardData();
          this.showToast('Activity refreshed', 'success');
        },

        async makeRequest(method, url, data = null) {
          const options = {
            method,
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          };

          if (data) {
            options.body = JSON.stringify(data);
          }

          const response = await fetch(url, options);
          return await response.json();
        },

        showToast(message, type = 'info') {
          const toast = document.createElement('div');
          const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
          toast.className =
            `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform`;
          toast.textContent = message;

          document.body.appendChild(toast);

          requestAnimationFrame(() => {
            toast.classList.remove('translate-x-full');
          });

          setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
          }, 5000);
        }
      }
    }
  </script>
@endsection
