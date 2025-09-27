@extends('layouts.master')

@section('title', 'Agent Dashboard')

@section('meta_description', 'Professional sports ticket monitoring and purchase decision management dashboard')

@push('head')
  <meta name="robots" content="noindex, nofollow">
@endpush

@section('page-header')
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
    <div>
      <h1 class="text-2xl lg:text-3xl font-bold text-hd-gray-900 dark:text-white">
        Professional Dashboard
      </h1>
      <p class="mt-1 text-sm text-hd-gray-600 dark:text-hd-gray-400">
        Advanced ticket monitoring and unlimited access tools
      </p>
    </div>

    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <!-- Live status indicator -->
      <div class="flex items-center gap-2 text-sm">
        <x-hdt.badge variant="success" dot pulse>Live Monitoring</x-hdt.badge>
      </div>

      <!-- Quick Actions -->
      <x-hdt.button href="/alerts/create" size="sm" variant="primary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
        </svg>
        Create Alert
      </x-hdt.button>

      <x-hdt.button href="/tickets/search" size="sm" variant="secondary">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        Browse Tickets
      </x-hdt.button>
    </div>
  </div>
@endsection

@section('content')
  <div class="space-y-8" x-data="agentDashboard()" x-init="init()">

    <!-- Performance Stats Grid -->
    <div class="dashboard-stats-grid">
      <x-hdt.stat-card label="Monitored Events" x-text="monitoredEvents" variant="primary" trend="up" trendValue="+12"
        trendLabel="this week">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card label="Success Rate" value="94.2%" variant="success" trend="up" trendValue="+2.1%"
        trendLabel="this week">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card label="Response Time" value="1.2s" description="Average processing time">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>

      <x-hdt.stat-card label="Queue Items" x-text="queueItems" variant="warning" description="Pending decisions">
        <x-slot:icon>
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
        </x-slot:icon>
      </x-hdt.stat-card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">

      <!-- Left Column: Main Monitoring Feed -->
      <div class="xl:col-span-3 space-y-6">

        <!-- Real-time Monitoring Feed -->
        <x-hdt.card>
          <x-slot:title>Real-time Ticket Monitoring</x-slot:title>
          <x-slot:subtitle>
            <div class="flex items-center gap-4">
              <span x-text="'Last updated: ' + lastUpdated"></span>
              <x-hdt.badge variant="success" dot pulse size="sm">Live</x-hdt.badge>
            </div>
          </x-slot:subtitle>

          <!-- Advanced Filters Bar -->
          <div
            class="bg-hd-gray-50 dark:bg-hd-gray-800 -mx-6 -mt-6 mb-6 p-4 border-b border-hd-gray-200 dark:border-hd-gray-700">
            <div class="flex flex-wrap items-center gap-3">
              <x-hdt.input size="sm" placeholder="Search events..." x-model="searchQuery" class="w-64">
                <x-slot:icon>
                  <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </x-slot:icon>
              </x-hdt.input>

              <select class="hdt-input__field hdt-input--sm" x-model="selectedSport">
                <option value="">All Sports</option>
                <option value="basketball">Basketball</option>
                <option value="football">Football</option>
                <option value="baseball">Baseball</option>
                <option value="hockey">Hockey</option>
              </select>

              <select class="hdt-input__field hdt-input--sm" x-model="priceRange">
                <option value="">All Prices</option>
                <option value="0-50">$0-50</option>
                <option value="50-100">$50-100</option>
                <option value="100-200">$100-200</option>
                <option value="200+">$200+</option>
              </select>

              <x-hdt.button size="sm" variant="ghost" @click="clearFilters()">
                Clear Filters
              </x-hdt.button>
            </div>
          </div>

          <!-- Monitoring Feed -->
          <div class="space-y-4" x-show="filteredTickets.length > 0">
            <template x-for="ticket in filteredTickets.slice(0, 10)" :key="ticket.id">
              <div
                class="flex items-center gap-4 p-4 border border-hd-gray-200 dark:border-hd-gray-700 rounded-lg hover:border-hd-secondary-300 dark:hover:border-hd-secondary-600 transition-colors cursor-pointer"
                @click="openTicketDetails(ticket)">

                <!-- Priority Indicator -->
                <div class="flex flex-col items-center gap-1">
                  <div class="w-3 h-3 rounded-full"
                    :class="ticket.priority === 'high' ? 'bg-red-500' : ticket.priority === 'medium' ? 'bg-yellow-500' :
                        'bg-green-500'">
                  </div>
                  <span class="text-xs text-hd-gray-500 capitalize" x-text="ticket.priority"></span>
                </div>

                <!-- Event Info -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <h4 class="font-semibold text-hd-gray-900 dark:text-hd-gray-100 truncate"
                      x-text="ticket.event_title"></h4>
                    <x-hdt.badge size="xs" variant="info" x-text="ticket.sport" class="capitalize"></x-hdt.badge>
                  </div>
                  <p class="text-sm text-hd-gray-600 dark:text-hd-gray-400 mb-2"
                    x-text="ticket.venue + ' • ' + ticket.date"></p>

                  <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                      <span class="text-lg font-bold"
                        :class="ticket.price_trend === 'down' ? 'text-green-600' : ticket.price_trend === 'up' ?
                            'text-red-600' : 'text-hd-gray-900 dark:text-hd-gray-100'"
                        x-text="'$' + ticket.current_price"></span>
                      <span x-show="ticket.price_change" class="text-xs px-2 py-1 rounded-full"
                        :class="ticket.price_trend === 'down' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                        x-text="ticket.price_change"></span>
                    </div>

                    <div class="text-sm text-hd-gray-600 dark:text-hd-gray-400">
                      <span x-text="ticket.available_tickets"></span> available
                    </div>
                  </div>
                </div>

                <!-- Quick Actions -->
                <div class="flex flex-col gap-2">
                  <x-hdt.button size="xs" variant="primary" @click.stop="addToPurchaseQueue(ticket)">
                    Add to Queue
                  </x-hdt.button>
                  <x-hdt.button size="xs" variant="ghost" @click.stop="createAlert(ticket)">
                    Alert
                  </x-hdt.button>
                </div>

                <!-- Real-time indicator -->
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse" title="Live data"></div>
                </div>
              </div>
            </template>
          </div>

          <!-- Empty State -->
          <div x-show="filteredTickets.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-hd-gray-300 dark:text-hd-gray-600 mb-4" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <h3 class="text-lg font-medium text-hd-gray-900 dark:text-hd-gray-100 mb-2">No tickets found</h3>
            <p class="text-hd-gray-600 dark:text-hd-gray-400 mb-4">Try adjusting your filters or check back later</p>
            <x-hdt.button size="sm" @click="clearFilters()">Clear Filters</x-hdt.button>
          </div>
        </x-hdt.card>

        <!-- Purchase Decision Queue -->
        <x-hdt.card>
          <x-slot:title>Purchase Decision Queue</x-slot:title>
          <x-slot:subtitle>
            <span x-text="purchaseQueue.length + ' items pending'"></span>
          </x-slot:subtitle>

          <div class="space-y-3" x-show="purchaseQueue.length > 0">
            <template x-for="item in purchaseQueue" :key="item.id">
              <div
                class="flex items-center gap-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <div class="flex-1 min-w-0">
                  <h4 class="font-semibold text-hd-gray-900 dark:text-hd-gray-100" x-text="item.event_title"></h4>
                  <p class="text-sm text-hd-gray-600 dark:text-hd-gray-400"
                    x-text="'$' + item.price + ' • ' + item.quantity + ' tickets'"></p>
                  <p class="text-xs text-hd-gray-500" x-text="'Added ' + item.time_ago"></p>
                </div>
                <div class="flex gap-2">
                  <x-hdt.button size="xs" variant="success" @click="approvepurchase(item)">
                    Approve
                  </x-hdt.button>
                  <x-hdt.button size="xs" variant="danger" @click="rejectPurchase(item)">
                    Reject
                  </x-hdt.button>
                </div>
              </div>
            </template>
          </div>

          <div x-show="purchaseQueue.length === 0" class="text-center py-8">
            <svg class="w-12 h-12 mx-auto text-hd-gray-300 dark:text-hd-gray-600 mb-3" fill="none"
              stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">All caught up! No pending purchase decisions.</p>
          </div>
        </x-hdt.card>
      </div>

      <!-- Right Column: Sidebar Widgets -->
      <div class="space-y-6">

        <!-- Performance Metrics -->
        <x-hdt.card>
          <x-slot:title>This Week</x-slot:title>

          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Tickets Acquired</span>
              <span class="font-bold text-hd-gray-900 dark:text-hd-gray-100" x-text="weeklyStats.acquired"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Success Rate</span>
              <span class="font-bold text-green-600" x-text="weeklyStats.successRate + '%'"></span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-sm text-hd-gray-600 dark:text-hd-gray-400">Avg Response</span>
              <span class="font-bold text-hd-gray-900 dark:text-hd-gray-100"
                x-text="weeklyStats.avgResponse + 's'"></span>
            </div>
          </div>
        </x-hdt.card>

        <!-- Quick Actions Panel -->
        <x-hdt.card>
          <x-slot:title>Quick Actions</x-slot:title>

          <div class="space-y-3">
            <x-hdt.button variant="secondary" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
              </svg>
              Create Bulk Alert
            </x-hdt.button>

            <x-hdt.button variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Export Reports
            </x-hdt.button>

            <x-hdt.button variant="ghost" class="w-full justify-start">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Agent Settings
            </x-hdt.button>
          </div>
        </x-hdt.card>

        <!-- Active Alerts -->
        <x-hdt.card>
          <x-slot:title>Active Alerts</x-slot:title>
          <x-slot:subtitle>
            <a href="/alerts" class="text-hd-secondary-600 hover:text-hd-secondary-700 text-sm font-medium">Manage
              All</a>
          </x-slot:subtitle>

          <div class="space-y-3" x-show="activeAlerts.length > 0">
            <template x-for="alert in activeAlerts.slice(0, 5)" :key="alert.id">
              <div class="p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200" x-text="alert.event_title"></p>
                    <p class="text-xs text-blue-600 dark:text-blue-300 mt-1" x-text="alert.condition"></p>
                  </div>
                  <x-hdt.badge size="xs" :variant="alert.status === 'active' ? 'success' : 'warning'" x-text="alert.status"></x-hdt.badge>
                </div>
              </div>
            </template>
          </div>

          <div x-show="activeAlerts.length === 0" class="text-center py-6">
            <svg class="w-8 h-8 text-hd-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 17h5l-5 5v-5zM4 19l1-7H3l1 7zM15 3H4l11 4v12z" />
            </svg>
            <p class="text-sm text-hd-gray-500 dark:text-hd-gray-400">No active alerts</p>
          </div>
        </x-hdt.card>
      </div>
    </div>

    <!-- Keyboard Shortcuts Help -->
    <div x-show="showShortcuts" x-transition @keydown.window.escape="showShortcuts = false"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="showShortcuts = false">
      <x-hdt.card class="max-w-md">
        <x-slot:title>Keyboard Shortcuts</x-slot:title>
        <div class="space-y-2 text-sm">
          <div class="flex justify-between"><span>Search</span><kbd class="px-2 py-1 bg-hd-gray-200 rounded">⌘K</kbd>
          </div>
          <div class="flex justify-between"><span>Create Alert</span><kbd
              class="px-2 py-1 bg-hd-gray-200 rounded">⌘A</kbd></div>
          <div class="flex justify-between"><span>Refresh Data</span><kbd
              class="px-2 py-1 bg-hd-gray-200 rounded">⌘R</kbd></div>
          <div class="flex justify-between"><span>Toggle Shortcuts</span><kbd
              class="px-2 py-1 bg-hd-gray-200 rounded">?</kbd></div>
        </div>
        <x-slot:actions>
          <x-hdt.button size="sm" @click="showShortcuts = false">Close</x-hdt.button>
        </x-slot:actions>
      </x-hdt.card>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    function agentDashboard() {
      return {
        // Stats data
        monitoredEvents: 89,
        queueItems: 15,
        lastUpdated: new Date().toLocaleTimeString(),

        // Filters
        searchQuery: '',
        selectedSport: '',
        priceRange: '',
        showShortcuts: false,

        // Data
        tickets: [{
            id: 1,
            event_title: 'Lakers vs Warriors',
            sport: 'basketball',
            venue: 'Crypto.com Arena',
            date: 'Dec 15, 2024',
            current_price: 89,
            price_change: '-$31 (26%)',
            price_trend: 'down',
            available_tickets: 8,
            priority: 'high'
          },
          {
            id: 2,
            event_title: 'Cowboys vs Giants',
            sport: 'football',
            venue: 'AT&T Stadium',
            date: 'Dec 18, 2024',
            current_price: 145,
            price_change: null,
            price_trend: 'neutral',
            available_tickets: 3,
            priority: 'medium'
          },
          {
            id: 3,
            event_title: 'Rangers vs Bruins',
            sport: 'hockey',
            venue: 'Madison Square Garden',
            date: 'Dec 20, 2024',
            current_price: 67,
            price_change: '+$12 (18%)',
            price_trend: 'up',
            available_tickets: 15,
            priority: 'low'
          }
        ],

        purchaseQueue: [{
          id: 1,
          event_title: 'Lakers vs Warriors',
          price: 89,
          quantity: 2,
          time_ago: '5 min ago'
        }],

        activeAlerts: [{
            id: 1,
            event_title: 'Cowboys vs Giants',
            condition: 'Price < $140',
            status: 'active'
          },
          {
            id: 2,
            event_title: 'Rangers vs Bruins',
            condition: 'Availability > 10',
            status: 'triggered'
          }
        ],

        weeklyStats: {
          acquired: 47,
          successRate: 96.2,
          avgResponse: 1.1
        },

        get filteredTickets() {
          return this.tickets.filter(ticket => {
            const matchesSearch = !this.searchQuery ||
              ticket.event_title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
              ticket.venue.toLowerCase().includes(this.searchQuery.toLowerCase());

            const matchesSport = !this.selectedSport || ticket.sport === this.selectedSport;

            const matchesPrice = !this.priceRange || this.matchesPriceRange(ticket.current_price);

            return matchesSearch && matchesSport && matchesPrice;
          });
        },

        async init() {
          await this.loadDashboardData();
          this.startRealTimeUpdates();
          this.setupKeyboardShortcuts();
        },

        matchesPriceRange(price) {
          switch (this.priceRange) {
            case '0-50':
              return price <= 50;
            case '50-100':
              return price > 50 && price <= 100;
            case '100-200':
              return price > 100 && price <= 200;
            case '200+':
              return price > 200;
            default:
              return true;
          }
        },

        clearFilters() {
          this.searchQuery = '';
          this.selectedSport = '';
          this.priceRange = '';
        },

        async loadDashboardData() {
          try {
            const response = await fetch('/api/v1/agent/dashboard/stats', {
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
              }
            });

            if (response.ok) {
              const data = await response.json();

              this.monitoredEvents = data.monitored_events || this.monitoredEvents;
              this.queueItems = data.queue_items || this.queueItems;

              if (data.tickets) {
                this.tickets = data.tickets;
              }
              if (data.purchase_queue) {
                this.purchaseQueue = data.purchase_queue;
              }
              if (data.active_alerts) {
                this.activeAlerts = data.active_alerts;
              }
              if (data.weekly_stats) {
                this.weeklyStats = data.weekly_stats;
              }
            }
          } catch (error) {
            console.error('Error loading dashboard data:', error);
          }
        },

        startRealTimeUpdates() {
          // Update timestamp every minute
          setInterval(() => {
            this.lastUpdated = new Date().toLocaleTimeString();
          }, 60000);

          // Refresh data every 30 seconds for real-time monitoring
          setInterval(() => {
            this.loadDashboardData();
          }, 30000);

          // Real-time updates via WebSocket if available
          if (typeof Echo !== 'undefined') {
            Echo.private(`agent.{{ auth()->id() }}`)
              .listen('TicketPriceUpdate', (e) => {
                const ticket = this.tickets.find(t => t.id === e.ticket_id);
                if (ticket) {
                  ticket.current_price = e.new_price;
                  ticket.price_change = e.price_change;
                  ticket.price_trend = e.price_trend;
                }
              })
              .listen('PurchaseDecisionRequired', (e) => {
                this.purchaseQueue.unshift(e.decision);
                this.queueItems = this.purchaseQueue.length;
              });
          }
        },

        setupKeyboardShortcuts() {
          document.addEventListener('keydown', (e) => {
            if (e.key === '?') {
              e.preventDefault();
              this.showShortcuts = !this.showShortcuts;
            }

            if ((e.metaKey || e.ctrlKey)) {
              switch (e.key) {
                case 'k':
                  e.preventDefault();
                  document.querySelector('input[placeholder="Search events..."]')?.focus();
                  break;
                case 'a':
                  e.preventDefault();
                  window.location.href = '/alerts/create';
                  break;
                case 'r':
                  e.preventDefault();
                  this.loadDashboardData();
                  break;
              }
            }
          });
        },

        openTicketDetails(ticket) {
          window.location.href = `/tickets/${ticket.id}`;
        },

        addToPurchaseQueue(ticket) {
          const decision = {
            id: Date.now(),
            event_title: ticket.event_title,
            price: ticket.current_price,
            quantity: 2,
            time_ago: 'just now'
          };

          this.purchaseQueue.unshift(decision);
          this.queueItems = this.purchaseQueue.length;
        },

        approvePublic(item) {
          this.purchaseQueue = this.purchaseQueue.filter(i => i.id !== item.id);
          this.queueItems = this.purchaseQueue.length;

          // Here you would make an API call to approve the purchase
          console.log('Approved purchase:', item);
        },

        rejectPurchase(item) {
          this.purchaseQueue = this.purchaseQueue.filter(i => i.id !== item.id);
          this.queueItems = this.purchaseQueue.length;

          // Here you would make an API call to reject the purchase
          console.log('Rejected purchase:', item);
        },

        createAlert(ticket) {
          window.location.href = `/alerts/create?event=${encodeURIComponent(ticket.event_title)}`;
        }
      }
    }
  </script>
@endpush

@push('styles')
  <style>
    /* Agent dashboard specific styles */
    .dashboard-stats-grid {
      display: grid;
      gap: 1.5rem;
      grid-template-columns: 1fr;
    }

    @media (min-width: 640px) {
      .dashboard-stats-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (min-width: 1024px) {
      .dashboard-stats-grid {
        grid-template-columns: repeat(4, 1fr);
      }
    }

    /* Keyboard shortcut styling */
    kbd {
      font-family: ui-monospace, monospace;
      font-size: 0.75rem;
      font-weight: 600;
    }
  </style>
@endpush
@endsection
