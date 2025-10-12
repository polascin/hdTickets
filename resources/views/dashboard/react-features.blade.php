<x-app-layout title="Advanced Features Dashboard">
  {{-- Fixed syntax errors --}}
  <!-- Page Header -->
  <div class="bg-white shadow">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <div class="md:flex md:items-center md:justify-between">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Advanced Features Dashboard
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Explore smart monitoring, advanced search, team following, and more powerful features.
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <button type="button"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Settings
          </button>
          <button type="button"
            class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Refresh Data
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

      <!-- Feature Navigation Tabs -->
      <div class="mb-8">
        <div class="sm:hidden">
          <label for="tabs" class="sr-only">Select a feature</label>
          <select id="tabs" name="tabs"
            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
            <option selected>Smart Ticket Monitoring</option>
            <option>Advanced Search & Filtering</option>
            <option>Team & Venue Following</option>
            <option>Ticket Comparison Engine</option>
            <option>Interactive Event Calendar</option>
            <option>User Notification System</option>
            <option>Ticket History Tracking</option>
            <option>Social Proof Features</option>
          </select>
        </div>
        <div class="hidden sm:block">
          <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Features" x-data="{ activeTab: 'monitoring' }">
              <button @click="activeTab = 'monitoring'"
                :class="activeTab === 'monitoring' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm" aria-current="page">
                Smart Monitoring
              </button>
              <button @click="activeTab = 'search'"
                :class="activeTab === 'search' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Advanced Search
              </button>
              <button @click="activeTab = 'following'"
                :class="activeTab === 'following' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Team Following
              </button>
              <button @click="activeTab = 'comparison'"
                :class="activeTab === 'comparison' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Ticket Comparison
              </button>
              <button @click="activeTab = 'calendar'"
                :class="activeTab === 'calendar' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Event Calendar
              </button>
              <button @click="activeTab = 'notifications'"
                :class="activeTab === 'notifications' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Notifications
              </button>
              <button @click="activeTab = 'history'"
                :class="activeTab === 'history' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Price History
              </button>
              <button @click="activeTab = 'social'"
                :class="activeTab === 'social' ? 'border-blue-500 text-blue-600' :
                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                Social Proof
              </button>
            </nav>
          </div>
        </div>
      </div>

      <!-- React Component Container -->
      <div x-data="{ activeTab: 'monitoring' }">

        <!-- Smart Ticket Monitoring Dashboard -->
        <div x-show="activeTab === 'monitoring'" x-transition>
          <div id="smart-monitoring-dashboard" data-api-base="{{ url('/api/v1') }}"
            data-csrf-token="{{ csrf_token() }}" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Smart Ticket Monitoring Dashboard</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading monitoring dashboard...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Search & Filtering System -->
        <div x-show="activeTab === 'search'" x-transition>
          <div id="advanced-search-system" data-api-base="{{ url('/api/v1') }}" data-csrf-token="{{ csrf_token() }}"
            class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Advanced Search & Filtering</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading search interface...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Team & Venue Following System -->
        <div x-show="activeTab === 'following'" x-transition>
          <div id="following-system" data-api-base="{{ url('/api/v1') }}" data-csrf-token="{{ csrf_token() }}"
            class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Team & Venue Following</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading following system...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ticket Comparison Engine -->
        <div x-show="activeTab === 'comparison'" x-transition>
          <div id="ticket-comparison-engine" data-api-base="{{ url('/api/v1') }}" data-csrf-token="{{ csrf_token() }}"
            class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket Comparison Engine</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading comparison engine...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Event Calendar -->
        <div x-show="activeTab === 'calendar'" x-transition>
          <div id="interactive-event-calendar" data-api-base="{{ url('/api/v1') }}"
            data-csrf-token="{{ csrf_token() }}" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Interactive Event Calendar</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading event calendar...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- User Notification System -->
        <div x-show="activeTab === 'notifications'" x-transition>
          <div id="user-notification-system" data-api-base="{{ url('/api/v1') }}"
            data-csrf-token="{{ csrf_token() }}" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">User Notification System</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading notification system...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Ticket History Tracking -->
        <div x-show="activeTab === 'history'" x-transition>
          <div id="ticket-history-tracking" data-api-base="{{ url('/api/v1') }}"
            data-csrf-token="{{ csrf_token() }}" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Ticket History Tracking</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading history tracking...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Social Proof Features -->
        <div x-show="activeTab === 'social'" x-transition>
          <div id="social-proof-features" data-api-base="{{ url('/api/v1') }}"
            data-csrf-token="{{ csrf_token() }}" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Social Proof Features</h3>
              <div class="text-center py-8">
                <div class="text-gray-500">Loading social proof features...</div>
                <div class="mt-4">
                  <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Feature Information Cards -->
      <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Smart Monitoring</dt>
                  <dd class="text-lg font-medium text-gray-900">Real-time ticket tracking with intelligent alerts</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Advanced Search</dt>
                  <dd class="text-lg font-medium text-gray-900">Multi-faceted search with intelligent filtering</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                    </path>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Team Following</dt>
                  <dd class="text-lg font-medium text-gray-900">Follow favourite teams and venues</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">Social Proof</dt>
                  <dd class="text-lg font-medium text-gray-900">Market trends and demand indicators</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script type="module">
      document.addEventListener('DOMContentLoaded', function() {
        const config = {
          apiBase: '{{ url('/api/v1') }}',
          csrfToken: '{{ csrf_token() }}',
          user: @json(auth()->user()),
        };

        if (window.SmartTicketMonitoringDashboard) {
          window.SmartTicketMonitoringDashboard.init({
            containerId: 'smart-monitoring-dashboard',
            ...config
          });
        }

        if (window.AdvancedSearchSystem) {
          window.AdvancedSearchSystem.init({
            containerId: 'advanced-search-system',
            ...config
          });
        }

        if (window.FollowingSystem) {
          window.FollowingSystem.init({
            containerId: 'following-system',
            ...config
          });
        }

        if (window.TicketComparisonEngine) {
          window.TicketComparisonEngine.init({
            containerId: 'ticket-comparison-engine',
            ...config
          });
        }

        if (window.InteractiveEventCalendar) {
          window.InteractiveEventCalendar.init({
            containerId: 'interactive-event-calendar',
            ...config
          });
        }

        if (window.UserNotificationSystem) {
          window.UserNotificationSystem.init({
            containerId: 'user-notification-system',
            ...config
          });
        }

        if (window.TicketHistoryTracking) {
          window.TicketHistoryTracking.init({
            containerId: 'ticket-history-tracking',
            ...config
          });
        }

        if (window.SocialProofFeatures) {
          window.SocialProofFeatures.init({
            containerId: 'social-proof-features',
            ...config
          });
        }

        console.log('React components initialized with configuration:', config);
      });
    </script>
  @endpush

</x-app-layout>
