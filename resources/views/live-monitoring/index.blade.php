@extends('layouts.app')

@section('title', 'Live Match Monitoring - HD Tickets')

@section('content')
  <div class="live-monitoring-dashboard" x-data="liveMonitoring()" x-init="initializeMonitoring()">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200 px-4 py-6 sm:px-6">
      <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
              <span class="animate-pulse text-red-500 mr-3">●</span>
              Live Match Monitoring
            </h1>
            <p class="mt-1 text-sm text-gray-500">
              Real-time ticket availability across {{ count($platforms) }} verified platforms
            </p>
          </div>

          <div class="flex items-center space-x-4">
            <!-- Auto-refresh toggle -->
            <div class="flex items-center">
              <input type="checkbox" id="auto-refresh" x-model="preferences.auto_refresh" @change="updatePreferences()"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <label for="auto-refresh" class="ml-2 text-sm text-gray-700">Auto-refresh</label>
            </div>

            <!-- Refresh interval -->
            <select x-model="preferences.refresh_interval" @change="updatePreferences()"
              class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
              <option value="10">10s</option>
              <option value="30">30s</option>
              <option value="60">1m</option>
              <option value="120">2m</option>
              <option value="300">5m</option>
            </select>

            <!-- Manual refresh button -->
            <button @click="refreshData()" :disabled="loading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
              <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                </path>
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="bg-gray-50 px-4 py-6 sm:px-6">
      <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
          <!-- Total Matches -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Total Matches</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_matches']) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- High Demand -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">High Demand</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['high_demand_matches']) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Platforms -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Platforms</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ $stats['platforms_monitored'] }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Price Drops -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">Price Drops Today</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['price_drops_today']) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- New Today -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                        clip-rule="evenodd"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-5 w-0 flex-1">
                  <dl>
                    <dt class="text-sm font-medium text-gray-500 truncate">New Today</dt>
                    <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['new_matches_today']) }}</dd>
                  </dl>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <!-- Sidebar with filters and platform status -->
        <div class="lg:col-span-1">
          <!-- Filters -->
          <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>

            <!-- Category filter -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select x-model="currentCategory" @change="loadMatches()"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="upcoming">Upcoming Matches</option>
                <option value="high_demand">High Demand</option>
                <option value="recent">Recently Added</option>
                <option value="all">All Matches</option>
              </select>
            </div>

            <!-- League filter -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">League</label>
              <select x-model="selectedLeague" @change="loadMatches()"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Leagues</option>
                @foreach ($leagues as $league)
                  <option value="{{ $league['slug'] }}">{{ $league['name'] }} ({{ $league['ticket_count'] }})</option>
                @endforeach
              </select>
            </div>

            <!-- Platform filter -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
              <select x-model="selectedPlatform" @change="loadMatches()"
                class="w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Platforms</option>
                @foreach ($platforms as $platform)
                  <option value="{{ $platform['slug'] }}">{{ $platform['name'] }}</option>
                @endforeach
              </select>
            </div>

            <!-- Hide sold out -->
            <div class="flex items-center">
              <input type="checkbox" id="hide-sold-out" x-model="preferences.hide_sold_out"
                @change="updatePreferences(); loadMatches()"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
              <label for="hide-sold-out" class="ml-2 text-sm text-gray-700">Hide sold out matches</label>
            </div>
          </div>

          <!-- Platform Status -->
          <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Platform Status</h3>
            <div class="space-y-3">
              @foreach ($platforms as $platform)
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full mr-3 platform-status-{{ $platform['slug'] }}"
                      :class="{
                          'bg-green-500': platformStatus['{{ $platform['slug'] }}']?.status === 'online',
                          'bg-red-500': platformStatus['{{ $platform['slug'] }}']?.status === 'offline',
                          'bg-yellow-500': platformStatus['{{ $platform['slug'] }}']?.status === 'error',
                          'bg-gray-400': !platformStatus['{{ $platform['slug'] }}']
                      }">
                    </div>
                    <span class="text-sm text-gray-900">{{ $platform['name'] }}</span>
                  </div>
                  <span class="text-xs text-gray-500"
                    x-text="platformStatus['{{ $platform['slug'] }}']?.response_time ? platformStatus['{{ $platform['slug'] }}'].response_time + 'ms' : '-'"></span>
                </div>
              @endforeach
            </div>
          </div>
        </div>

        <!-- Main content area -->
        <div class="lg:col-span-3">
          <!-- Loading state -->
          <div x-show="loading" class="text-center py-12">
            <div
              class="inline-flex items-center px-4 py-2 font-semibold leading-6 text-sm shadow rounded-md text-gray-500 bg-white">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg"
                fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                  stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
              </svg>
              Loading matches...
            </div>
          </div>

          <!-- Matches grid -->
          <div x-show="!loading" class="space-y-4">
            <template x-for="match in matches" :key="match.id">
              <div class="bg-white shadow rounded-lg p-6 hover:shadow-lg transition-shadow duration-200">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="match.title"></h3>
                    <div class="flex items-center text-sm text-gray-500 space-x-4 mb-3">
                      <span x-text="match.venue"></span>
                      <span x-text="formatDate(match.event_date)"></span>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                        :class="{
                            'bg-green-100 text-green-800': match.status_indicator.type === 'success',
                            'bg-yellow-100 text-yellow-800': match.status_indicator.type === 'warning',
                            'bg-red-100 text-red-800': match.status_indicator.type === 'danger',
                            'bg-blue-100 text-blue-800': match.status_indicator.type === 'info',
                            'bg-gray-100 text-gray-800': match.status_indicator.type === 'secondary'
                        }"
                        x-text="match.status_indicator.text"></span>
                    </div>

                    <!-- Price and platform info -->
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-4">
                        <span class="text-lg font-bold text-gray-900">
                          £<span x-text="match.price"></span>
                        </span>
                        <span class="text-sm text-gray-500" x-text="match.platform"></span>
                        <span x-show="match.price_changed_recently"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                          💰 Price Changed
                        </span>
                        <span x-show="match.is_new"
                          class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          🆕 New
                        </span>
                      </div>

                      <div class="flex items-center space-x-2">
                        <button @click="viewTicket(match)"
                          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                          View Details
                        </button>
                        <a :href="match.external_url" target="_blank" rel="noopener"
                          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                          Buy Tickets
                          <svg class="ml-2 -mr-0.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-2M14 4h6m0 0v6m0-6L10 14"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Empty state -->
            <div x-show="!loading && matches.length === 0" class="text-center py-12">
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">No matches found</h3>
              <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or check back later.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function liveMonitoring() {
      return {
        loading: false,
        matches: [],
        platformStatus: {},
        currentCategory: 'upcoming',
        selectedLeague: '',
        selectedPlatform: '',
        preferences: {
          auto_refresh: true,
          refresh_interval: 30,
          show_price_changes: true,
          show_availability_changes: true,
          hide_sold_out: false
        },
        refreshInterval: null,

        async initializeMonitoring() {
          await this.loadPreferences();
          await this.loadMatches();
          await this.loadPlatformStatus();
          this.startAutoRefresh();
        },

        async loadPreferences() {
          try {
            const response = await fetch('/live-monitoring/preferences');
            if (response.ok) {
              const data = await response.json();
              this.preferences = {
                ...this.preferences,
                ...data.preferences
              };
            }
          } catch (error) {
            console.error('Failed to load preferences:', error);
          }
        },

        async updatePreferences() {
          try {
            await fetch('/live-monitoring/preferences', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify(this.preferences)
            });
            this.restartAutoRefresh();
          } catch (error) {
            console.error('Failed to update preferences:', error);
          }
        },

        async loadMatches() {
          this.loading = true;
          try {
            const params = new URLSearchParams({
              category: this.currentCategory,
              league: this.selectedLeague,
              platform: this.selectedPlatform,
              limit: 20
            });

            const response = await fetch(`/live-monitoring/data?${params}`);
            const data = await response.json();

            if (data.success) {
              this.matches = data.data.matches || data.data;
            }
          } catch (error) {
            console.error('Failed to load matches:', error);
          } finally {
            this.loading = false;
          }
        },

        async loadPlatformStatus() {
          try {
            const response = await fetch('/live-monitoring/platform-status');
            const data = await response.json();

            if (data.success) {
              this.platformStatus = data.platforms.reduce((acc, platform) => {
                acc[platform.slug] = platform.status;
                return acc;
              }, {});
            }
          } catch (error) {
            console.error('Failed to load platform status:', error);
          }
        },

        async refreshData() {
          await Promise.all([
            this.loadMatches(),
            this.loadPlatformStatus()
          ]);
        },

        startAutoRefresh() {
          if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
          }

          if (this.preferences.auto_refresh) {
            this.refreshInterval = setInterval(() => {
              this.refreshData();
            }, this.preferences.refresh_interval * 1000);
          }
        },

        restartAutoRefresh() {
          this.startAutoRefresh();
        },

        viewTicket(match) {
          window.location.href = match.url;
        },

        formatDate(dateString) {
          const date = new Date(dateString);
          return date.toLocaleDateString('en-GB', {
            weekday: 'short',
            day: 'numeric',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit'
          });
        }
      }
    }
  </script>
@endsection
