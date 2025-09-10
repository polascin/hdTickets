@extends('layouts.app-v2')
@section('title', 'Agent Dashboard')
@section('content')
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Agent Dashboard</h1>
      <p class="text-sm text-slate-500 dark:text-slate-400">Ticket Monitoring & Purchase Management</p>
    </div>
    <div class="flex items-center space-x-4">
      <div class="text-sm text-slate-600 dark:text-slate-300">
        Online: <span class="text-green-600 font-semibold">{{ now()->format('H:i:s') }}</span>
      </div>
      <button onclick="refreshAgentDashboard()" class="uiv2-action-btn bg-blue-600 hover:bg-blue-700 text-white">
        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
          </path>
        </svg>
        Refresh
      </button>
    </div>
  </div>

  <!-- Welcome Banner -->
  <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 text-white mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-2xl font-bold mb-2">Welcome, {{ Auth::user()->name }}!</h3>
        <p class="text-indigo-100">Sports Events Ticket Agent • Monitoring & Purchase Management</p>
      </div>
      <div class="text-right">
        <div class="text-sm text-indigo-100 mb-1">Agent Status</div>
        <div class="flex items-center">
          <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
          <span class="text-lg font-bold">Active</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Agent Performance Metrics -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <x-dashboard.stat-card title="Tickets Monitored" value="{{ $agentMetrics['tickets_monitored'] }}"
      icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z' /></svg>"
      color="blue" />

    <x-dashboard.stat-card title="Active Alerts" value="{{ $agentMetrics['active_alerts'] }}"
      icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z' /></svg>"
      color="yellow" />

    <x-dashboard.stat-card title="Purchases Today" value="{{ $agentMetrics['successful_purchases_today'] }}"
      icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' /></svg>"
      color="green" />

    <x-dashboard.stat-card title="Success Rate" value="{{ $agentMetrics['success_rate'] }}"
      icon="<svg class='w-6 h-6 text-white' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M13 10V3L4 14h7v7l9-11h-7z' /></svg>"
      color="purple" />
  </div>

  <!-- Purchase Queue Management -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Pending Purchase Decisions
        </h4>
        <p class="text-sm text-gray-500">{{ $agentMetrics['pending_purchase_decisions'] }} items in queue</p>
      </div>
      <div class="p-6">
        @if (isset($purchaseData['pending_purchases']) && count($purchaseData['pending_purchases']) > 0)
          <div class="space-y-3">
            @foreach (array_slice($purchaseData['pending_purchases'], 0, 5) as $purchase)
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex-1">
                  <h5 class="text-sm font-medium text-gray-900">{{ $purchase['event'] ?? 'Sports Event' }}</h5>
                  <p class="text-xs text-gray-500">{{ $purchase['platform'] ?? 'Platform' }} •
                    ${{ $purchase['price'] ?? '0.00' }}</p>
                </div>
                <div class="flex items-center space-x-2">
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Pending</span>
                </div>
              </div>
            @endforeach
          </div>
          <div class="mt-4">
            <a href="{{ route('purchase-decisions.index') }}"
              class="text-blue-600 hover:text-blue-700 text-sm font-medium">
              View All Purchase Decisions →
            </a>
          </div>
        @else
          <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm">No pending purchase decisions</p>
          </div>
        @endif
      </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h4 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          Recent Activity
        </h4>
        <p class="text-sm text-gray-500">Latest ticket monitoring activities</p>
      </div>
      <div class="p-6">
        @if (isset($recentActivity) && count($recentActivity) > 0)
          <div class="space-y-4">
            @foreach (array_slice($recentActivity, 0, 5) as $activity)
              <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center {{ $activity['color'] === 'green' ? 'bg-green-100' : ($activity['color'] === 'yellow' ? 'bg-yellow-100' : 'bg-blue-100') }}">
                    <svg
                      class="w-4 h-4 {{ $activity['color'] === 'green' ? 'text-green-600' : ($activity['color'] === 'yellow' ? 'text-yellow-600' : 'text-blue-600') }}"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      @if ($activity['icon'] === 'shopping-cart')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                        </path>
                      @elseif($activity['icon'] === 'bell')
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2z"></path>
                      @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z"></path>
                      @endif
                    </svg>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <h5 class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</h5>
                  <p class="text-xs text-gray-500">{{ $activity['description'] }}</p>
                  <p class="text-xs text-gray-400">{{ $activity['timestamp']->diffForHumans() }}</p>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm">No recent activity</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div
      class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer"
      onclick="window.location.href='{{ route('tickets.scraping.index') }}'">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z">
            </path>
          </svg>
        </div>
        <div class="ml-4">
          <h4 class="font-semibold text-blue-900">Sports Event Tickets</h4>
          <p class="text-blue-700 text-sm">Browse and monitor available tickets</p>
        </div>
      </div>
    </div>

    <div
      class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer"
      onclick="window.location.href='{{ route('purchase-decisions.index') }}'">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h4 class="font-semibold text-green-900">Purchase Decisions</h4>
          <p class="text-green-700 text-sm">Manage ticket purchase queue</p>
        </div>
      </div>
    </div>

    <div
      class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 p-6 rounded-xl hover:shadow-lg transition cursor-pointer"
      onclick="window.location.href='{{ route('tickets.alerts.index') }}'">
      <div class="flex items-center">
        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"></path>
          </svg>
        </div>
        <div class="ml-4">
          <h4 class="font-semibold text-purple-900">Price Alerts</h4>
          <p class="text-purple-700 text-sm">Monitor price drops and availability</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Platform Status -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-8">
    <div class="px-6 py-4 border-b border-gray-200">
      <h4 class="text-lg font-semibold text-gray-900 flex items-center">
        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M9 12l2 2 4-4m5.25-2.25L21 7.5l-2.25-2.25L16.5 7.5 21 12l-2.25 2.25L16.5 16.5 21 12"></path>
        </svg>
        Platform Status
      </h4>
      <p class="text-sm text-gray-500">Real-time monitoring of ticket platforms</p>
    </div>
    <div class="p-6">
      @if (isset($ticketData['platform_status']))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          @foreach ($ticketData['platform_status'] as $platform => $status)
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
              <div>
                <h5 class="text-sm font-medium text-gray-900 capitalize">{{ ucfirst($platform) }}</h5>
                <p class="text-xs text-gray-500">{{ $status['response_time'] ?? 'N/A' }}</p>
              </div>
              <div class="flex items-center">
                <div
                  class="w-3 h-3 rounded-full {{ $status['status'] === 'online' ? 'bg-green-400' : ($status['status'] === 'slow' ? 'bg-yellow-400' : 'bg-red-400') }}">
                </div>
                <span
                  class="ml-2 text-xs font-medium {{ $status['status'] === 'online' ? 'text-green-600' : ($status['status'] === 'slow' ? 'text-yellow-600' : 'text-red-600') }}">
                  {{ ucfirst($status['status'] ?? 'Unknown') }}
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

  <script>
    function refreshAgentDashboard() {
      location.reload();
    }

    // Auto-refresh every 5 minutes
    setInterval(refreshAgentDashboard, 300000);

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
  </script>
@endsection
