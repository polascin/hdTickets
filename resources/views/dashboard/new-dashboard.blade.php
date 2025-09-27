@extends('layouts.new-app')

@section('title', 'Dashboard')

@section('sidebar')
  <!-- Navigation Items -->
  <div class="space-y-1">
    <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z">
        </path>
      </svg>
      Dashboard
    </a>

    <a href="{{ route('tickets.index') }}" class="nav-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v-3a2 2 0 10-2-2m14-4a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2V7z">
        </path>
      </svg>
      Tickets
    </a>

    <a href="{{ route('tickets.alerts.index') }}"
      class="nav-item {{ request()->routeIs('tickets.alerts.*') ? 'active' : '' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
      </svg>
      Alerts
    </a>

    <a href="{{ route('profile.edit') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
      </svg>
      Profile
    </a>

    @if (auth()->user()->hasRole('admin'))
      <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
        <p class="px-4 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Admin</p>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
            </path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
            </path>
          </svg>
          Admin Panel
        </a>
      </div>
    @endif
  </div>
@endsection

@section('content')
  <div class="space-y-8" x-data="dashboard()" x-init="init()">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Welcome back! Here's what's happening with your tickets.</p>
      </div>

      <div class="flex items-center space-x-4">
        <button @click="refresh()" :disabled="loading" class="btn btn-secondary">
          <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
            </path>
          </svg>
          Refresh
        </button>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="dashboard-stats">
      <div class="stat-card">
        <div class="stat-icon bg-blue-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-title">New Today</div>
          <div class="stat-value" x-text="stats.new_today || '0'">0</div>
          <div x-show="loading" class="loading-skeleton h-4 w-16"></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-green-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z">
            </path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
            </path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-title">Events Monitored</div>
          <div class="stat-value" x-text="stats.monitored_events || '0'">0</div>
          <div x-show="loading" class="loading-skeleton h-4 w-16"></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-yellow-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-title">Active Alerts</div>
          <div class="stat-value" x-text="stats.active_alerts || '0'">0</div>
          <div x-show="loading" class="loading-skeleton h-4 w-16"></div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon bg-purple-500">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
            </path>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-title">Price Alerts</div>
          <div class="stat-value" x-text="stats.price_alerts || '0'">0</div>
          <div x-show="loading" class="loading-skeleton h-4 w-16"></div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Recent Tickets -->
      <div class="lg:col-span-2">
        <div class="card">
          <div class="card-header">
            <div class="flex items-center justify-between">
              <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Recent Tickets</h2>
              <a href="{{ route('tickets.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                View all →
              </a>
            </div>
          </div>

          <div class="card-body">
            <!-- Loading State -->
            <div x-show="loading" class="space-y-4">
              <template x-for="i in 3">
                <div class="loading-skeleton h-20 rounded-lg"></div>
              </template>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && tickets.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                </path>
              </svg>
              <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">No tickets available</h3>
              <p class="mt-2 text-gray-500 dark:text-gray-400">Check back later for new sports event tickets.</p>
              <div class="mt-6">
                <a href="{{ route('tickets.index') }}" class="btn btn-primary">Browse Tickets</a>
              </div>
            </div>

            <!-- Tickets List -->
            <div x-show="!loading && tickets.length > 0" class="space-y-4">
              <template x-for="ticket in tickets" :key="ticket.id">
                <div class="ticket-card">
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <h3 class="ticket-title" x-text="ticket.title || 'Sports Event'"></h3>
                      <div class="ticket-meta">
                        <span x-text="ticket.venue || 'TBD'"></span>
                        <span class="mx-2">•</span>
                        <span x-text="ticket.sport || 'Sports'"></span>
                        <span class="mx-2">•</span>
                        <span x-text="ticket.platform || 'Unknown'"></span>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="ticket-price">
                        <span x-show="ticket.min_price">$</span>
                        <span x-text="ticket.min_price || 'TBD'"></span>
                      </div>
                      <div class="mt-1">
                        <span x-show="ticket.event_date" class="badge badge-primary" x-text="ticket.event_date"></span>
                        <span x-show="ticket.is_high_demand" class="badge badge-error ml-2">High Demand</span>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Actions</h2>
          </div>
          <div class="card-body">
            <div class="space-y-3">
              <a href="{{ route('tickets.index') }}" class="btn btn-secondary w-full justify-start">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                Find Tickets
              </a>

              <a href="{{ route('tickets.alerts.index') }}" class="btn btn-secondary w-full justify-start">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-5-5V9c0-3.866-3.134-7-7-7s-7 3.134-7 7v3l-5 5h5m9 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                My Alerts
              </a>

              <a href="{{ route('profile.edit') }}" class="btn btn-secondary w-full justify-start">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Profile Settings
              </a>
            </div>
          </div>
        </div>

        <!-- System Status -->
        <div class="card">
          <div class="card-header">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">System Status</h2>
          </div>
          <div class="card-body">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Scraping</span>
                <div class="flex items-center">
                  <div class="w-2 h-2 rounded-full mr-2"
                    :class="systemStatus.scraping_active ? 'bg-green-500' : 'bg-red-500'"></div>
                  <span class="text-sm text-gray-900 dark:text-white"
                    x-text="systemStatus.scraping_active ? 'Active' : 'Inactive'">Active</span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                <div class="flex items-center">
                  <div class="w-2 h-2 rounded-full mr-2"
                    :class="systemStatus.database_healthy ? 'bg-green-500' : 'bg-red-500'"></div>
                  <span class="text-sm text-gray-900 dark:text-white"
                    x-text="systemStatus.database_healthy ? 'Healthy' : 'Issues'">Healthy</span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">Last Updated</span>
                <span class="text-sm text-gray-900 dark:text-white" x-text="lastUpdate">Just now</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function dashboard() {
      return {
        loading: false,
        stats: @json($statistics ?? []),
        tickets: @json($recent_tickets ?? []),
        systemStatus: @json($system_status ?? []),
        lastUpdate: 'Just now',

        init() {
          this.updateTime();
          setInterval(() => this.updateTime(), 60000); // Update every minute
        },

        updateTime() {
          this.lastUpdate = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
          });
        },

        async refresh() {
          if (this.loading) return;

          this.loading = true;
          try {
            const response = await fetch('/api/v1/dashboard/realtime');
            const data = await response.json();

            this.stats = data.statistics || {};
            this.tickets = data.recent_tickets || [];
            this.systemStatus = data.system_status || {};
            this.lastUpdate = 'Just now';
          } catch (error) {
            console.error('Failed to refresh dashboard:', error);
          } finally {
            this.loading = false;
          }
        }
      }
    }
  </script>
@endpush
@endsection
