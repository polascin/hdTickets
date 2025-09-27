{{-- 
  OLD CUSTOMER DASHBOARD - BACKED UP AND REPLACED
  This file has been replaced with a new comprehensive dashboard
  Original content preserved for reference
--}}
<x-unified-layout title="Customer Dashboard - OLD" subtitle="Legacy Dashboard (Replaced)">

  <!-- Quick Search Button -->
  <x-ui.button variant="ghost" size="sm" onclick="openQuickSearch()" title="Quick Ticket Search">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
    </svg>
  </x-ui.button>

  <!-- Notifications -->
  <div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="p-2 text-gray-500 hover:text-gray-700 relative">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
      </svg>
      <span
        class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
        {{ Auth::user()->unreadAlerts()->count() }}
      </span>
    </button>

    <div x-show="open" @click.away="open = false"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border z-50">
      <div class="p-4">
        <h3 class="font-semibold text-gray-900 mb-3">Recent Alerts</h3>
        <div class="space-y-3">
          @forelse(Auth::user()->recentAlerts()->take(3)->get() as $alert)
            <div class="flex items-start space-x-3 p-2 hover:bg-gray-50 rounded">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
              </div>
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ $alert->title }}</p>
                <p class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</p>
              </div>
            </div>
          @empty
            <p class="text-sm text-gray-500 text-center py-4">No recent alerts</p>
          @endforelse
        </div>
        <div class="mt-3 pt-3 border-t">
          <a href="{{ route('tickets.alerts.index') }}" class="text-sm text-blue-600 hover:text-blue-700">View all
            alerts →</a>
        </div>
      </div>
    </div>
  </div>
  </div>
  </x-slot>

  <div x-data="customerDashboard()" x-init="init()">
    <!-- Welcome Banner with Subscription Info -->
    <x-ui.card class="mb-6" variant="flat">
      <x-ui.card-content
        class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white rounded-lg relative overflow-hidden">
        <div class="relative z-10 p-6">
          <div class="flex items-start justify-between">
            <div>
              <div class="flex items-center mb-3">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mr-4">
                  <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                    </path>
                  </svg>
                </div>
                <div>
                  <h2 class="hd-heading-2 !text-white !mb-1">Welcome back, {{ Auth::user()->name }}!</h2>
                  <p class="text-white/90 hd-text-base">Your Sports Ticket Discovery Dashboard</p>
                </div>
              </div>

              <!-- Subscription Status -->
              @if (Auth::user()->hasActiveSubscription())
                <div class="bg-white/20 rounded-lg p-3 mb-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-white font-semibold">Premium Subscription Active</p>
                      <p class="text-white/80 text-sm">{{ Auth::user()->subscription->plan_name ?? 'Premium Plan' }}</p>
                    </div>
                    <div class="text-right">
                      <p class="text-white/80 text-sm">Next billing</p>
                      <p class="text-white font-semibold text-sm">
                        {{ Auth::user()->subscription->next_billing_date?->format('M j, Y') ?? 'N/A' }}</p>
                    </div>
                  </div>
                </div>
              @else
                <div class="bg-yellow-500/20 rounded-lg p-3 mb-3">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-white font-semibold">Free Trial Active</p>
                      <p class="text-white/80 text-sm">{{ Auth::user()->getFreeTrialDaysRemaining() }} days remaining
                      </p>
                    </div>
                    <a href="{{ route('subscription.plans') }}"
                      class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-white text-sm font-medium transition">
                      Upgrade Now
                    </a>
                  </div>
                </div>
              @endif
            </div>

            <!-- Usage Overview -->
            <div class="text-right">
              <div class="bg-white/20 rounded-lg p-4 min-w-[200px]">
                <p class="text-white/80 text-sm mb-1">Monthly Usage</p>
                @php
                  $usage = Auth::user()->getMonthlyTicketUsage();
                  $limit = Auth::user()->getMonthlyTicketLimit();
                  $percentage = $limit > 0 ? ($usage / $limit) * 100 : 0;
                @endphp
                <div class="flex items-center justify-between mb-2">
                  <span class="text-2xl font-bold text-white">{{ $usage }}</span>
                  <span class="text-white/80 text-sm">/ {{ $limit }}</span>
                </div>
                <div class="w-full bg-white/20 rounded-full h-2">
                  <div class="bg-white rounded-full h-2 transition-all duration-300"
                    style="width: {{ min($percentage, 100) }}%"></div>
                </div>
                <p class="text-white/80 text-xs mt-1">Tickets this month</p>
              </div>
            </div>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Customer Stats Cards -->
    <div class="hd-grid hd-grid-1 hd-md-grid-2 hd-lg-grid-4 mb-8">
      <!-- My Alerts -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-blue-500 to-blue-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 hd-text-small font-medium">Active Alerts</p>
              <p class="hd-heading-2 !text-white !mb-0"
                x-text="stats.active_alerts || '{{ Auth::user()->activeAlerts()->count() }}'">
                {{ Auth::user()->activeAlerts()->count() }}
              </p>
              <div class="mt-1">
                @if (Auth::user()->recentAlerts()->count() > 0)
                  <x-ui.badge variant="warning" size="xs"
                    dot="true">{{ Auth::user()->recentAlerts()->count() }} new</x-ui.badge>
                @else
                  <span class="hd-text-small text-white/70">All caught up</span>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Available Deals -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-green-500 to-green-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 hd-text-small font-medium">Available Deals</p>
              <p class="hd-heading-2 !text-white !mb-0" x-text="stats.available_deals || '12'">12</p>
              <div class="mt-1">
                <x-ui.badge variant="success" size="xs" dot="true">New deals found</x-ui.badge>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Purchase History -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-orange-500 to-orange-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100 hd-text-small font-medium">My Purchases</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ Auth::user()->ticketPurchases()->count() }}</p>
              <div class="mt-1">
                @if (Auth::user()->ticketPurchases()->recent()->count() > 0)
                  <x-ui.badge variant="info" size="xs"
                    dot="true">{{ Auth::user()->ticketPurchases()->recent()->count() }} recent</x-ui.badge>
                @else
                  <span class="hd-text-small text-white/70">No recent purchases</span>
                @endif
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <!-- Watchlist -->
      <x-ui.card hover="true" class="bg-gradient-to-br from-purple-500 to-purple-600 text-white">
        <x-ui.card-content>
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 hd-text-small font-medium">Watchlist</p>
              <p class="hd-heading-2 !text-white !mb-0">{{ Auth::user()->watchlist()->count() }}</p>
              <div class="mt-1">
                <span class="hd-text-small text-white/70">Monitored events</span>
              </div>
            </div>
            <svg class="w-8 h-8 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
              </path>
            </svg>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
      <!-- Hot Deals & Trending -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Quick Actions for Customers -->
        <x-ui.card>
          <x-ui.card-header title="Quick Actions">
            <x-ui.button href="{{ route('tickets.scraping.index') }}" variant="outline" size="sm">Browse
              All</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <a href="{{ route('tickets.scraping.index', ['sport' => 'football']) }}"
                class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors group">
                <div
                  class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <span class="text-white font-bold text-lg">🏈</span>
                </div>
                <span class="text-sm font-medium text-gray-700">NFL</span>
                <span class="text-xs text-gray-500">Football</span>
              </a>

              <a href="{{ route('tickets.scraping.index', ['sport' => 'basketball']) }}"
                class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors group">
                <div
                  class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <span class="text-white font-bold text-lg">🏀</span>
                </div>
                <span class="text-sm font-medium text-gray-700">NBA</span>
                <span class="text-xs text-gray-500">Basketball</span>
              </a>

              <a href="{{ route('tickets.scraping.index', ['sport' => 'baseball']) }}"
                class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors group">
                <div
                  class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <span class="text-white font-bold text-lg">⚾</span>
                </div>
                <span class="text-sm font-medium text-gray-700">MLB</span>
                <span class="text-xs text-gray-500">Baseball</span>
              </a>

              <a href="{{ route('tickets.scraping.index', ['sport' => 'soccer']) }}"
                class="flex flex-col items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors group">
                <div
                  class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                  <span class="text-white font-bold text-lg">⚽</span>
                </div>
                <span class="text-sm font-medium text-gray-700">MLS</span>
                <span class="text-xs text-gray-500">Soccer</span>
              </a>
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Trending Events -->
        <x-ui.card>
          <x-ui.card-header title="Trending Events">
            <x-ui.button href="{{ route('tickets.scraping.trending') }}" variant="ghost" size="sm">View
              All</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              @forelse($trendingEvents ?? [] as $event)
                <div
                  class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                  <div class="flex items-center space-x-4">
                    <div
                      class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                        </path>
                      </svg>
                    </div>
                    <div>
                      <h4 class="font-semibold text-gray-900">
                        {{ $event['title'] ?? 'Kansas City Chiefs vs Patriots' }}</h4>
                      <p class="text-sm text-gray-500">{{ $event['venue'] ?? 'Arrowhead Stadium' }} •
                        {{ $event['date'] ?? 'Dec 15, 2024' }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="font-semibold text-green-600">From ${{ $event['price'] ?? '89' }}</p>
                    <p class="text-xs text-gray-500">{{ $event['available'] ?? '127' }} available</p>
                  </div>
                </div>
              @empty
                <div class="text-center py-8 text-gray-500">
                  <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                    </path>
                  </svg>
                  <p>No trending events at the moment</p>
                  <a href="{{ route('tickets.scraping.index') }}"
                    class="text-blue-600 hover:text-blue-700 text-sm">Browse all tickets →</a>
                </div>
              @endforelse
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Recent Alerts -->
        <x-ui.card>
          <x-ui.card-header title="Recent Alerts">
            <x-ui.button href="{{ route('tickets.alerts.index') }}" variant="ghost"
              size="sm">Manage</x-ui.button>
          </x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-3">
              @forelse(Auth::user()->recentAlerts()->take(4)->get() as $alert)
                <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                  <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></div>
                  <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-gray-900">{{ $alert->title }}</p>
                    <p class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</p>
                  </div>
                </div>
              @empty
                <div class="text-center py-6 text-gray-500">
                  <svg class="w-8 h-8 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                  </svg>
                  <p class="text-sm">No recent alerts</p>
                  <a href="{{ route('tickets.alerts.create') }}"
                    class="text-blue-600 hover:text-blue-700 text-sm">Create your first alert →</a>
                </div>
              @endforelse
            </div>
          </x-ui.card-content>
        </x-ui.card>

        <!-- Account Status -->
        <x-ui.card>
          <x-ui.card-header title="Account Overview"></x-ui.card-header>
          <x-ui.card-content>
            <div class="space-y-4">
              <!-- Subscription -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                  </svg>
                  <span class="text-sm font-medium">Subscription</span>
                </div>
                @if (Auth::user()->hasActiveSubscription())
                  <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Active</span>
                @else
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Trial</span>
                @endif
              </div>

              <!-- Profile Completion -->
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-purple-600 mr-3" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                  <span class="text-sm font-medium">Profile</span>
                </div>
                @php
                  $completion = Auth::user()->getProfileCompletion();
                @endphp
                <span
                  class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">{{ $completion['percentage'] }}%</span>
              </div>

              <!-- Actions -->
              <div class="pt-3 border-t">
                <div class="space-y-2">
                  @if (!Auth::user()->hasActiveSubscription())
                    <a href="{{ route('subscription.plans') }}"
                      class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition text-center block">
                      Upgrade to Premium
                    </a>
                  @endif
                  <a href="{{ route('profile.edit') }}"
                    class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition text-center block">
                    Manage Account
                  </a>
                </div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function customerDashboard() {
        return {
          stats: {
            active_alerts: 0,
            available_deals: 0,
            purchases: 0,
            watchlist: 0
          },

          init() {
            this.loadStats();
            this.setupRealTimeUpdates();
          },

          loadStats() {
            // In a real implementation, this would fetch from API
            fetch('/api/customer/dashboard-stats')
              .then(response => response.json())
              .then(data => {
                this.stats = data;
              })
              .catch(() => {
                // Fallback to static data
                this.stats = {
                  active_alerts: {{ Auth::user()->activeAlerts()->count() }},
                  available_deals: 12,
                  purchases: {{ Auth::user()->ticketPurchases()->count() }},
                  watchlist: {{ Auth::user()->watchlist()->count() }}
                };
              });
          },

          setupRealTimeUpdates() {
            // WebSocket connection for real-time updates
            if (window.Echo) {
              window.Echo.private('customer.{{ Auth::id() }}')
                .listen('TicketAlertTriggered', (e) => {
                  this.showNotification('New ticket alert!', e.message, 'success');
                  this.stats.active_alerts++;
                })
                .listen('PriceDropDetected', (e) => {
                  this.showNotification('Price drop alert!', e.message, 'info');
                });
            }
          },

          showNotification(title, message, type = 'info') {
            // Use your preferred notification system
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }

      function openQuickSearch() {
        // Open quick search modal or redirect
        window.location.href = '{{ route('tickets.scraping.index') }}';
      }
    </script>
  @endpush
</x-unified-layout>
