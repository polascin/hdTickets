<x-unified-layout title="Dashboard" subtitle="Your Sports Ticket Monitoring Hub">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <x-ui.badge variant="success" dot="true">Live</x-ui.badge>
    </div>
  </x-slot>

  <div x-data="{ loading: false }">
    <!-- Welcome Banner -->
    <x-ui.card class="mb-6" variant="flat">
      <x-ui.card-content class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white rounded-lg relative overflow-hidden">
        <div class="relative z-10 p-6">
          <div class="flex items-center mb-3">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                </path>
              </svg>
            </div>
            <div>
              <h2 class="hd-heading-2 !text-white !mb-1">Welcome back, {{ $user->name }}!</h2>
              <p class="text-white/90 hd-text-base">Your Sports Ticket Monitoring Dashboard</p>
            </div>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Stats Cards -->
    <div class="hd-grid hd-grid-1 hd-md-grid-2 hd-lg-grid-4 mb-8">
      <x-ui.card hover="true" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 hd-text-small font-medium">Active Monitors</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ $stats['active_monitors'] }}</p>
              <div class="mt-1">
                <x-ui.badge variant="success" size="xs" dot="true">Live</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card hover="true" class="bg-gradient-to-br from-green-500 to-green-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 hd-text-small font-medium">Alerts Today</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ $stats['alerts_today'] }}</p>
              <div class="mt-1">
                @if ($stats['alerts_today'] > 0)
                  <x-ui.badge variant="warning" size="xs" dot="true">New alerts</x-ui.badge>
                @else
                  <span class="hd-text-small text-white/70">All caught up</span>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5 5v-5z"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card hover="true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100 hd-text-small font-medium">Price Drops</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ $stats['price_drops'] }}</p>
              <div class="mt-1">
                <span class="hd-text-small text-white/70">Monitoring prices</span>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card hover="true" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 hd-text-small font-medium">Available Now</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ $stats['available_now'] }}</p>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Quick Actions & Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- Quick Actions -->
      <x-ui.card>
        <x-ui.card-header title="Quick Actions"></x-ui.card-header>
        <x-ui.card-content>
          <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('tickets.scraping.index') }}"
              class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
              <svg class="w-8 h-8 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                </path>
              </svg>
              <span class="text-sm font-medium text-blue-700">Browse Tickets</span>
            </a>
            <a href="{{ route('tickets.alerts.index') }}"
              class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
              <svg class="w-8 h-8 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5 5v-5z"></path>
              </svg>
              <span class="text-sm font-medium text-green-700">Manage Alerts</span>
            </a>
            <a href="{{ route('purchase-decisions.index') }}"
              class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
              <svg class="w-8 h-8 text-purple-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                </path>
              </svg>
              <span class="text-sm font-medium text-purple-700">Purchase Queue</span>
            </a>
            <a href="{{ route('ticket-sources.index') }}"
              class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
              <svg class="w-8 h-8 text-orange-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                </path>
              </svg>
              <span class="text-sm font-medium text-orange-700">Ticket Sources</span>
            </a>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Recent Tickets -->
      <x-ui.card>
        <x-ui.card-header title="Recent Tickets">
          <x-ui.button href="{{ route('tickets.scraping.index') }}" variant="ghost" size="sm">View All</x-ui.button>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-3">
            @forelse($recentTickets as $ticket)
              <div class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ $ticket->title }}</p>
                  <p class="text-xs text-gray-500">{{ $ticket->venue ?? 'N/A' }} • {{ $ticket->platform }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-semibold text-gray-900">${{ number_format($ticket->min_price, 2) }}</p>
                  <span class="text-xs text-gray-500">{{ $ticket->scraped_at->diffForHumans() }}</span>
                </div>
              </div>
            @empty
              <p class="text-center text-gray-500 py-4">No recent tickets available</p>
            @endforelse
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>
  </div>
</x-unified-layout>
