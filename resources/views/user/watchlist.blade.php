<x-unified-layout title="My Watchlist" subtitle="Track prices and get alerts for your favorite events">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Quick Filters -->
      <div class="flex items-center space-x-2">
        <button @click="quickFilter('price-drops')" 
                :class="filters.quickFilter === 'price-drops' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Price Drops
        </button>
        <button @click="quickFilter('today')" 
                :class="filters.quickFilter === 'today' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          Today
        </button>
        <button @click="quickFilter('this-week')" 
                :class="filters.quickFilter === 'this-week' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="px-3 py-2 rounded-lg text-sm font-medium hover:opacity-80 transition">
          This Week
        </button>
      </div>

      <!-- View Toggle -->
      <div class="flex items-center bg-gray-100 rounded-lg p-1">
        <button @click="viewMode = 'grid'" 
                :class="viewMode === 'grid' ? 'bg-white shadow' : ''"
                class="p-2 rounded-md transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
          </svg>
        </button>
        <button @click="viewMode = 'list'" 
                :class="viewMode === 'list' ? 'bg-white shadow' : ''"
                class="p-2 rounded-md transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>
  </x-slot>

  <div x-data="watchlistManager()" x-init="init()" class="space-y-6">
    
    <!-- Watchlist Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Total Watching</p>
              <p class="text-2xl font-bold text-gray-900" x-text="stats.total">{{ $stats['total'] ?? 0 }}</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Price Drops</p>
              <p class="text-2xl font-bold text-green-600" x-text="stats.priceDrops">{{ $stats['price_drops'] ?? 0 }}</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.343 12.344l1.414-1.414L6.5 11.5"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Active Alerts</p>
              <p class="text-2xl font-bold text-yellow-600" x-text="stats.activeAlerts">{{ $stats['active_alerts'] ?? 0 }}</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      <x-ui.card>
        <x-ui.card-content class="p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm text-gray-600">Avg. Savings</p>
              <p class="text-2xl font-bold text-purple-600" x-text="formatCurrency(stats.averageSavings)">{{ $stats['average_savings'] ? '$' . number_format($stats['average_savings']) : '$0' }}</p>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Filters and Search -->
    <x-ui.card>
      <x-ui.card-content class="p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
          <!-- Search -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <input type="text" 
                     x-model="filters.search"
                     @input="applyFilters"
                     placeholder="Search events, teams, or venues..."
                     class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>

          <!-- Filter Controls -->
          <div class="flex items-center space-x-4">
            <!-- Sport Filter -->
            <div class="relative">
              <select x-model="filters.sport" 
                      @change="applyFilters"
                      class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Sports</option>
                <option value="football">Football</option>
                <option value="basketball">Basketball</option>
                <option value="baseball">Baseball</option>
                <option value="hockey">Hockey</option>
                <option value="soccer">Soccer</option>
                <option value="tennis">Tennis</option>
                <option value="other">Other</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>

            <!-- Price Range Filter -->
            <div class="relative">
              <select x-model="filters.priceRange" 
                      @change="applyFilters"
                      class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Prices</option>
                <option value="0-100">Under $100</option>
                <option value="100-250">$100 - $250</option>
                <option value="250-500">$250 - $500</option>
                <option value="500+">$500+</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>

            <!-- Sort -->
            <div class="relative">
              <select x-model="filters.sortBy" 
                      @change="applyFilters"
                      class="appearance-none bg-white border border-gray-300 rounded-lg px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="date">Event Date</option>
                <option value="price">Price</option>
                <option value="price-change">Price Change</option>
                <option value="added">Recently Added</option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
              </div>
            </div>

            <!-- Clear Filters -->
            <button @click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
              Clear All
            </button>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Empty State -->
    <div x-show="watchlistItems.length === 0 && !loading" x-cloak>
      <x-ui.card class="text-center py-12">
        <x-ui.card-content>
          <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Events in Watchlist</h3>
          <p class="text-gray-500 mb-6">Start watching events to track prices and get alerts when they drop.</p>
          <a href="{{ route('tickets.index') }}" class="inline-flex items-center bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            Browse Events
          </a>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Watchlist Items - Grid View -->
    <div x-show="viewMode === 'grid' && filteredItems.length > 0" x-cloak class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <template x-for="item in filteredItems" :key="item.id">
        <x-ui.card class="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer">
          <div class="relative">
            <img :src="item.image || '/images/default-event.jpg'" 
                 :alt="item.title"
                 class="w-full h-48 object-cover bg-gray-200">
            
            <!-- Price Badge -->
            <div class="absolute top-4 right-4 bg-white rounded-lg px-3 py-1 shadow-sm">
              <div class="text-lg font-bold text-gray-900" x-text="formatCurrency(item.current_price)"></div>
            </div>

            <!-- Price Change Indicator -->
            <div class="absolute bottom-4 left-4" 
                 :class="item.price_change >= 0 ? 'text-red-600' : 'text-green-600'"
                 x-show="Math.abs(item.price_change) > 0">
              <div class="bg-white/90 rounded-full px-2 py-1 text-xs font-medium flex items-center">
                <svg x-show="item.price_change >= 0" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 7H7v10"></path>
                </svg>
                <svg x-show="item.price_change < 0" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l-9.2-9.2M7 17h10V7"></path>
                </svg>
                <span x-text="Math.abs(item.price_change) + '%'"></span>
              </div>
            </div>
          </div>

          <x-ui.card-content class="p-4">
            <div class="flex justify-between items-start mb-2">
              <h3 class="font-semibold text-gray-900 text-sm truncate flex-1" x-text="item.title"></h3>
              <x-ui.badge :variant="getBadgeVariant(item.sport)" x-text="item.sport" class="ml-2 text-xs"></x-ui.badge>
            </div>

            <div class="space-y-2 text-sm text-gray-600">
              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span x-text="item.venue" class="truncate"></span>
              </div>

              <div class="flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span x-text="formatDate(item.event_date)"></span>
              </div>

              <div class="flex items-center" x-show="item.price_alert_target">
                <svg class="w-4 h-4 mr-2 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                </svg>
                <span class="text-yellow-700">Alert at <span x-text="formatCurrency(item.price_alert_target)"></span></span>
              </div>
            </div>

            <div class="mt-4 flex items-center justify-between">
              <button @click="viewTicket(item)" 
                      class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View Details
              </button>
              
              <div class="flex items-center space-x-2">
                <button @click="setPriceAlert(item)" 
                        :class="item.price_alert_target ? 'text-yellow-600' : 'text-gray-400'"
                        class="hover:text-yellow-600 transition"
                        :title="item.price_alert_target ? 'Update Price Alert' : 'Set Price Alert'">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                  </svg>
                </button>

                <button @click="removeFromWatchlist(item)" 
                        class="text-red-400 hover:text-red-600 transition"
                        title="Remove from Watchlist">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </template>
    </div>

    <!-- Watchlist Items - List View -->
    <div x-show="viewMode === 'list' && filteredItems.length > 0" x-cloak>
      <x-ui.card>
        <x-ui.card-content class="p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price Change</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alert</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <template x-for="item in filteredItems" :key="item.id">
                  <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <img :src="item.image || '/images/default-event.jpg'" 
                             :alt="item.title"
                             class="w-12 h-12 rounded-lg object-cover bg-gray-200">
                        <div class="ml-4">
                          <div class="font-medium text-gray-900" x-text="item.title"></div>
                          <div class="text-sm text-gray-500" x-text="item.venue"></div>
                        </div>
                      </div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDate(item.event_date)"></td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="font-medium text-gray-900" x-text="formatCurrency(item.current_price)"></div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center" x-show="Math.abs(item.price_change) > 0">
                        <svg x-show="item.price_change >= 0" class="w-4 h-4 mr-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 7H7v10"></path>
                        </svg>
                        <svg x-show="item.price_change < 0" class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l-9.2-9.2M7 17h10V7"></path>
                        </svg>
                        <span :class="item.price_change >= 0 ? 'text-red-600' : 'text-green-600'" 
                              class="font-medium" 
                              x-text="Math.abs(item.price_change) + '%'"></span>
                      </div>
                      <div x-show="Math.abs(item.price_change) === 0" class="text-gray-500">--</div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div x-show="item.price_alert_target" class="text-sm text-yellow-700" x-text="formatCurrency(item.price_alert_target)"></div>
                      <div x-show="!item.price_alert_target" class="text-gray-500">--</div>
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatTimeAgo(item.added_at)"></td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center justify-end space-x-2">
                        <button @click="viewTicket(item)" 
                                class="text-blue-600 hover:text-blue-900 transition">
                          View
                        </button>
                        <button @click="setPriceAlert(item)" 
                                :class="item.price_alert_target ? 'text-yellow-600' : 'text-gray-400'"
                                class="hover:text-yellow-600 transition">
                          Alert
                        </button>
                        <button @click="removeFromWatchlist(item)" 
                                class="text-red-400 hover:text-red-600 transition">
                          Remove
                        </button>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    </div>

    <!-- Pagination -->
    <div x-show="pagination.totalPages > 1" x-cloak class="flex items-center justify-between">
      <div class="text-sm text-gray-500">
        Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> events
      </div>
      
      <div class="flex items-center space-x-2">
        <button @click="goToPage(pagination.currentPage - 1)" 
                :disabled="pagination.currentPage === 1"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          Previous
        </button>
        
        <template x-for="page in getPaginationRange()" :key="page">
          <button @click="goToPage(page)" 
                  :class="page === pagination.currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                  class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium">
            <span x-text="page"></span>
          </button>
        </template>
        
        <button @click="goToPage(pagination.currentPage + 1)" 
                :disabled="pagination.currentPage === pagination.totalPages"
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
          Next
        </button>
      </div>
    </div>

    <!-- Price Alert Modal -->
    <div x-show="showPriceAlertModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showPriceAlertModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900" x-text="selectedItem?.price_alert_target ? 'Update Price Alert' : 'Set Price Alert'">Set Price Alert</h3>
          <p class="text-sm text-gray-600">Get notified when the price drops</p>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="font-medium text-gray-900" x-text="selectedItem?.title"></div>
              <div class="text-sm text-gray-500" x-text="selectedItem?.venue"></div>
              <div class="mt-2 text-lg font-bold text-gray-900" x-text="formatCurrency(selectedItem?.current_price)">Current Price</div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Alert When Price Drops To</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                <input type="number" 
                       x-model="alertPrice"
                       :max="selectedItem?.current_price"
                       step="0.01"
                       class="block w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="0.00">
              </div>
              <p class="text-xs text-gray-500 mt-1">Must be less than current price</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notification Method</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" x-model="alertMethods" value="email" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Email notification</span>
                </label>
                @if(Auth::user()->phone_verified_at)
                  <label class="flex items-center">
                    <input type="checkbox" x-model="alertMethods" value="sms" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">SMS notification</span>
                  </label>
                @endif
              </div>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showPriceAlertModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="savePriceAlert()" 
                  :disabled="!alertPrice || alertPrice >= selectedItem?.current_price || alertMethods.length === 0"
                  class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            <span x-text="selectedItem?.price_alert_target ? 'Update Alert' : 'Create Alert'">Create Alert</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      function watchlistManager() {
        return {
          // State
          loading: true,
          viewMode: 'grid',
          showPriceAlertModal: false,
          selectedItem: null,
          alertPrice: '',
          alertMethods: ['email'],

          // Data
          watchlistItems: [],
          filteredItems: [],
          stats: {
            total: 0,
            priceDrops: 0,
            activeAlerts: 0,
            averageSavings: 0
          },

          // Filters
          filters: {
            search: '',
            sport: '',
            priceRange: '',
            sortBy: 'date',
            quickFilter: ''
          },

          // Pagination
          pagination: {
            currentPage: 1,
            perPage: 12,
            totalPages: 1,
            total: 0,
            from: 0,
            to: 0
          },

          async init() {
            this.loadWatchlist();
            this.loadStats();
            this.setupRealTimeUpdates();
          },

          async loadWatchlist() {
            this.loading = true;
            try {
              const response = await fetch('/api/user/watchlist');
              const data = await response.json();
              
              if (data.success) {
                this.watchlistItems = data.watchlist || [];
                this.applyFilters();
              }
            } catch (error) {
              console.error('Failed to load watchlist:', error);
            } finally {
              this.loading = false;
            }
          },

          async loadStats() {
            try {
              const response = await fetch('/api/user/watchlist/stats');
              const data = await response.json();
              
              if (data.success) {
                this.stats = { ...this.stats, ...data.stats };
              }
            } catch (error) {
              console.error('Failed to load stats:', error);
            }
          },

          applyFilters() {
            let filtered = [...this.watchlistItems];

            // Text search
            if (this.filters.search) {
              const search = this.filters.search.toLowerCase();
              filtered = filtered.filter(item => 
                item.title.toLowerCase().includes(search) ||
                item.venue.toLowerCase().includes(search) ||
                (item.teams && item.teams.some(team => team.toLowerCase().includes(search)))
              );
            }

            // Sport filter
            if (this.filters.sport) {
              filtered = filtered.filter(item => item.sport === this.filters.sport);
            }

            // Price range filter
            if (this.filters.priceRange) {
              const [min, max] = this.filters.priceRange.split('-').map(p => p === '' ? Infinity : parseInt(p));
              filtered = filtered.filter(item => {
                const price = item.current_price;
                return price >= (min || 0) && (max === undefined || price <= max);
              });
            }

            // Quick filters
            if (this.filters.quickFilter) {
              const now = new Date();
              const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
              const weekFromNow = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);

              switch (this.filters.quickFilter) {
                case 'price-drops':
                  filtered = filtered.filter(item => item.price_change < 0);
                  break;
                case 'today':
                  filtered = filtered.filter(item => {
                    const eventDate = new Date(item.event_date);
                    return eventDate >= today && eventDate < new Date(today.getTime() + 24 * 60 * 60 * 1000);
                  });
                  break;
                case 'this-week':
                  filtered = filtered.filter(item => {
                    const eventDate = new Date(item.event_date);
                    return eventDate >= today && eventDate <= weekFromNow;
                  });
                  break;
              }
            }

            // Sort
            switch (this.filters.sortBy) {
              case 'date':
                filtered.sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
                break;
              case 'price':
                filtered.sort((a, b) => a.current_price - b.current_price);
                break;
              case 'price-change':
                filtered.sort((a, b) => a.price_change - b.price_change);
                break;
              case 'added':
                filtered.sort((a, b) => new Date(b.added_at) - new Date(a.added_at));
                break;
            }

            // Update pagination
            this.pagination.total = filtered.length;
            this.pagination.totalPages = Math.ceil(filtered.length / this.pagination.perPage);
            this.pagination.currentPage = Math.min(this.pagination.currentPage, this.pagination.totalPages || 1);

            // Apply pagination
            const start = (this.pagination.currentPage - 1) * this.pagination.perPage;
            const end = start + this.pagination.perPage;
            this.filteredItems = filtered.slice(start, end);

            this.pagination.from = start + 1;
            this.pagination.to = Math.min(end, filtered.length);
          },

          quickFilter(filter) {
            if (this.filters.quickFilter === filter) {
              this.filters.quickFilter = '';
            } else {
              this.filters.quickFilter = filter;
            }
            this.applyFilters();
          },

          clearFilters() {
            this.filters = {
              search: '',
              sport: '',
              priceRange: '',
              sortBy: 'date',
              quickFilter: ''
            };
            this.applyFilters();
          },

          goToPage(page) {
            if (page >= 1 && page <= this.pagination.totalPages) {
              this.pagination.currentPage = page;
              this.applyFilters();
            }
          },

          getPaginationRange() {
            const current = this.pagination.currentPage;
            const total = this.pagination.totalPages;
            const range = [];
            
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            
            for (let i = start; i <= end; i++) {
              range.push(i);
            }
            
            return range;
          },

          viewTicket(item) {
            window.location.href = `/tickets/${item.ticket_id}`;
          },

          setPriceAlert(item) {
            this.selectedItem = item;
            this.alertPrice = item.price_alert_target || '';
            this.alertMethods = item.alert_methods || ['email'];
            this.showPriceAlertModal = true;
          },

          async savePriceAlert() {
            try {
              const response = await fetch('/api/price-alerts', {
                method: this.selectedItem.price_alert_target ? 'PUT' : 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  ticket_id: this.selectedItem.ticket_id,
                  target_price: this.alertPrice,
                  notification_methods: this.alertMethods,
                  ...(this.selectedItem.price_alert_target && { alert_id: this.selectedItem.alert_id })
                })
              });

              const data = await response.json();

              if (data.success) {
                // Update the item in the watchlist
                const itemIndex = this.watchlistItems.findIndex(item => item.id === this.selectedItem.id);
                if (itemIndex !== -1) {
                  this.watchlistItems[itemIndex].price_alert_target = parseFloat(this.alertPrice);
                  this.watchlistItems[itemIndex].alert_methods = this.alertMethods;
                  this.watchlistItems[itemIndex].alert_id = data.alert_id;
                }

                this.showPriceAlertModal = false;
                this.applyFilters();
                this.loadStats();

                this.showNotification(
                  this.selectedItem.price_alert_target ? 'Price Alert Updated' : 'Price Alert Created',
                  `You'll be notified when the price drops to $${this.alertPrice}`,
                  'success'
                );
              } else {
                this.showNotification('Error', data.message || 'Failed to save price alert', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to save price alert', 'error');
            }
          },

          async removeFromWatchlist(item) {
            if (!confirm('Remove this event from your watchlist?')) return;

            try {
              const response = await fetch(`/api/tickets/${item.ticket_id}/watchlist`, {
                method: 'DELETE',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                this.watchlistItems = this.watchlistItems.filter(i => i.id !== item.id);
                this.applyFilters();
                this.loadStats();
                this.showNotification('Removed from Watchlist', 'Event removed from your watchlist', 'success');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to remove from watchlist', 'error');
            }
          },

          setupRealTimeUpdates() {
            if (window.Echo) {
              window.Echo.private(`user.${window.authUserId}.watchlist`)
                .listen('WatchlistPriceUpdate', (e) => {
                  const itemIndex = this.watchlistItems.findIndex(item => item.ticket_id === e.ticket_id);
                  if (itemIndex !== -1) {
                    this.watchlistItems[itemIndex].current_price = e.new_price;
                    this.watchlistItems[itemIndex].price_change = e.price_change;
                    this.applyFilters();
                  }
                });
            }
          },

          getBadgeVariant(sport) {
            const variants = {
              football: 'success',
              basketball: 'warning', 
              baseball: 'info',
              hockey: 'secondary',
              soccer: 'primary'
            };
            return variants[sport] || 'default';
          },

          formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
              style: 'currency',
              currency: 'USD',
              minimumFractionDigits: 0,
              maximumFractionDigits: 0
            }).format(value);
          },

          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
              month: 'short', 
              day: 'numeric',
              year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
            });
          },

          formatTimeAgo(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = Math.floor((now - date) / (1000 * 60 * 60));
            
            if (diffInHours < 1) return 'Just now';
            if (diffInHours < 24) return `${diffInHours}h ago`;
            if (diffInHours < 168) return `${Math.floor(diffInHours / 24)}d ago`;
            return this.formatDate(timestamp);
          },

          showNotification(title, message, type = 'info') {
            if (window.hdTicketsFeedback) {
              window.hdTicketsFeedback[type](title, message);
            }
          }
        };
      }
    </script>
  @endpush
</x-unified-layout>
