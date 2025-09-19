              </div>
            </a>
          </div>

          <div class="action-card alerts" data-action="alerts" data-hook="action-alerts">
            <a href="{{ route('tickets.alerts.index') }}" class="action-link">
              <div
                class="group bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-xl border border-green-200 hover:shadow-lg transition-all duration-200">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div
                      class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-semibold text-green-900">My Alerts</h4>
                    <p class="text-green-700 text-sm">Manage ticket alerts</p>
                  </div>
                </div>
              </div>
            </a>
          </div>

          <div class="action-card queue" data-action="queue" data-hook="action-queue">
            <a href="{{ route('purchase-decisions.index') }}" class="action-link">
              <div
                class="group bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-xl border border-purple-200 hover:shadow-lg transition-all duration-200">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div
                      class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-semibold text-purple-900">Purchase Queue</h4>
                    <p class="text-purple-700 text-sm">Manage ticket purchases</p>
                  </div>
                </div>
              </div>
            </a>
          </div>

          <div class="action-card sources" data-action="sources" data-hook="action-sources">
            <a href="{{ route('ticket-sources.index') }}" class="action-link">
              <div
                class="group bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-xl border border-red-200 hover:shadow-lg transition-all duration-200">
                <div class="flex items-center">
                  <div class="flex-shrink-0">
                    <div
                      class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1">
                        </path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <h4 class="text-lg font-semibold text-red-900">Ticket Sources</h4>
                    <p class="text-red-700 text-sm">Manage platform sources</p>
                  </div>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Recent Sport Event Tickets Section -->
    <section class="recent-tickets-section" data-section="recent-tickets" data-realtime="ticket-updates">
      <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl">
        <div class="px-6 py-6 sm:px-8">
          <!-- Skeleton loader for recent tickets -->
          <div class="recent-tickets-skeleton hidden" data-skeleton="recent-tickets">
            <div class="skeleton-header">
              <div class="skeleton-title"></div>
              <div class="skeleton-link"></div>
            </div>
            <div class="skeleton-tickets-list">
              <div class="skeleton-ticket-item"></div>
              <div class="skeleton-ticket-item"></div>
              <div class="skeleton-ticket-item"></div>
            </div>
          </div>

          <div class="section-header flex items-center justify-between mb-6">
            <h4 class="text-xl font-bold text-gray-900">Recent Sport Event Tickets</h4>
            <a href="{{ route('tickets.scraping.index') }}"
              class="text-brand-600 hover:text-brand-700 font-medium text-sm flex items-center"
              data-hook="view-all-tickets">
              View all tickets
              <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
              </svg>
            </a>
          </div>

          @php
            $recentSportTickets = \App\Models\ScrapedTicket::where('is_available', true)
                ->latest('scraped_at')
                ->limit(5)
                ->get();
          @endphp

          <div class="recent-tickets-list" data-refresh="true" data-live-updates="tickets">
            @if ($recentSportTickets->count() > 0)
              <div class="space-y-4">
                @foreach ($recentSportTickets as $ticket)
                  <div
                    class="ticket-item flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200"
                    data-ticket-id="{{ $ticket->id }}" data-realtime="ticket-{{ $ticket->id }}">
                    <div class="flex-shrink-0">
                      <div
                        class="availability-indicator w-3 h-3 rounded-full {{ $ticket->is_available ? 'bg-green-400' : 'bg-red-400' }} {{ $ticket->is_high_demand ? 'animate-pulse' : '' }}"
                        data-status="{{ $ticket->is_available ? 'available' : 'unavailable' }}"></div>
                    </div>
                    <div class="ml-4 flex-1 min-w-0">
                      <div class="flex items-center justify-between">
                        <div>
                          <p class="text-sm font-medium text-gray-900 truncate">
                            <span class="ticket-title">{{ $ticket->event_name ?? 'Sport Event Ticket' }}</span>
                            @if ($ticket->venue)
                              <span class="text-gray-500">at {{ $ticket->venue }}</span>
                            @endif
                          </p>
                          <p class="text-sm text-gray-500">Scraped {{ $ticket->scraped_at->diffForHumans() }}</p>
                          @if ($ticket->price)
                            <p class="text-sm font-semibold text-green-600">${{ number_format($ticket->price, 2) }}</p>
                          @endif
                        </div>
                        <div class="flex items-center space-x-2">
                          @if ($ticket->is_high_demand)
                            <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                              High Demand
                            </span>
                          @endif
                          <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->is_available ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $ticket->is_available ? 'Available' : 'Sold Out' }}
                          </span>
                          @if ($ticket->source_platform)
                            <span
                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                              {{ ucfirst($ticket->source_platform) }}
                            </span>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <div class="text-center py-12" data-empty-state="no-tickets">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
                  </path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No sport event tickets available</h3>
                <p class="mt-1 text-sm text-gray-500">Check back soon for new sport event tickets or set up an alert.</p>
                <div class="mt-6">
                  <a href="{{ route('tickets.alerts.create') }}"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500"
                    data-hook="create-alert">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                    </svg>
                    Create Ticket Alert
                  </a>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>

    <!-- WebSocket and Real-time Update Scripts with Cache Busting -->
    <script src="{{ asset('js/websocket-client.js') }}"></script>
    <script src="{{ asset('js/dashboard-realtime.js') }}"></script>
    <script src="{{ asset('js/skeleton-loaders.js') }}"></script>

    <script>
      // Initialize real-time dashboard
      document.addEventListener('DOMContentLoaded', function() {
        if (typeof DashboardRealtime !== 'undefined') {
          window.dashboardInstance = new DashboardRealtime({
            userId: {{ Auth::id() }},
            websocket: window.websocketConfig,
            refreshInterval: 120000, // 2 minutes instead of 30 seconds
            enableSkeletonLoaders: true
          });

          window.dashboardInstance.init();
        }
      });
    </script>
  </main>
  </div>

  <!-- Add CSS and JS directly in the content section -->
  <link href="{{ css_with_timestamp('css/customer-dashboard-v2.css') }}" rel="stylesheet">

  <!-- WebSocket Connection Config -->
  <script>
    window.websocketConfig = {
      url: '{{ config('websocket.url', 'ws://localhost:6001') }}',
      key: '{{ config('websocket.key') }}',
      auth: {
        userId: {{ Auth::id() }},
        token: '{{ csrf_token() }}'
      }
    };
  </script>
@endsection
