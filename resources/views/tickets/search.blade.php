<x-unified-layout title="Find Sports Tickets" subtitle="Discover and track tickets for your favorite sports events">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- View Mode Toggle -->
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
        <button @click="viewMode = 'map'" 
                :class="viewMode === 'map' ? 'bg-white shadow' : ''"
                class="p-2 rounded-md transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
          </svg>
        </button>
      </div>

      <!-- Save Search -->
      <button @click="saveCurrentSearch()" 
              :disabled="!hasActiveFilters"
              class="flex items-center bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>
        Save Search
      </button>
    </div>
  </x-slot>

  <div x-data="ticketSearcher()" x-init="init()" class="space-y-6">
    
    <!-- Search Header -->
    <x-ui.card>
      <x-ui.card-content class="p-6">
        <div class="space-y-4">
          <!-- Main Search Bar -->
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <input type="text" 
                   x-model="filters.query"
                   @input="debounceSearch"
                   placeholder="Search events, teams, venues, or locations..."
                   class="block w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
          </div>

          <!-- Quick Filters -->
          <div class="flex flex-wrap items-center gap-3">
            <span class="text-sm font-medium text-gray-700">Popular:</span>
            
            <button @click="applyQuickFilter('football')" 
                    :class="filters.sport === 'football' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm hover:opacity-80 transition">
              🏈 Football
            </button>
            
            <button @click="applyQuickFilter('basketball')" 
                    :class="filters.sport === 'basketball' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm hover:opacity-80 transition">
              🏀 Basketball
            </button>
            
            <button @click="applyQuickFilter('baseball')" 
                    :class="filters.sport === 'baseball' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm hover:opacity-80 transition">
              ⚾ Baseball
            </button>
            
            <button @click="applyQuickFilter('this-weekend')" 
                    :class="filters.dateRange === 'this-weekend' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm hover:opacity-80 transition">
              📅 This Weekend
            </button>
            
            <button @click="applyQuickFilter('under-100')" 
                    :class="filters.priceMax === 100 ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm hover:opacity-80 transition">
              💰 Under $100
            </button>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Filters Sidebar and Results -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      
      <!-- Filters Sidebar -->
      <div class="lg:col-span-1">
        <x-ui.card>
          <x-ui.card-header title="Filters">
            <button @click="clearAllFilters()" 
                    x-show="hasActiveFilters"
                    class="text-sm text-gray-500 hover:text-gray-700">
              Clear All
            </button>
          </x-ui.card-header>
          <x-ui.card-content class="p-0">
            <div class="space-y-6 p-6">
              
              <!-- Sport Filter -->
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Sport</h3>
                <div class="space-y-2">
                  <template x-for="sport in availableSports" :key="sport.slug">
                    <label class="flex items-center">
                      <input type="radio" 
                             name="sport" 
                             :value="sport.slug"
                             x-model="filters.sport"
                             @change="applyFilters"
                             class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                      <span class="ml-2 text-sm text-gray-700 flex items-center">
                        <span x-text="sport.icon" class="mr-2"></span>
                        <span x-text="sport.name"></span>
                        <span x-text="'(' + sport.count + ')'" class="ml-1 text-gray-500"></span>
                      </span>
                    </label>
                  </template>
                </div>
              </div>

              <!-- Date Range Filter -->
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Date Range</h3>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input type="radio" name="dateRange" value="today" x-model="filters.dateRange" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Today</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="dateRange" value="this-week" x-model="filters.dateRange" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">This Week</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="dateRange" value="this-month" x-model="filters.dateRange" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">This Month</span>
                  </label>
                  <label class="flex items-center">
                    <input type="radio" name="dateRange" value="custom" x-model="filters.dateRange" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Custom Range</span>
                  </label>
                  
                  <!-- Custom Date Inputs -->
                  <div x-show="filters.dateRange === 'custom'" x-cloak class="ml-6 space-y-2">
                    <div>
                      <label class="block text-xs font-medium text-gray-700">From</label>
                      <input type="date" x-model="filters.dateFrom" @change="applyFilters" class="mt-1 block w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                    <div>
                      <label class="block text-xs font-medium text-gray-700">To</label>
                      <input type="date" x-model="filters.dateTo" @change="applyFilters" class="mt-1 block w-full text-sm border border-gray-300 rounded px-3 py-1.5">
                    </div>
                  </div>
                </div>
              </div>

              <!-- Price Range Filter -->
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Price Range</h3>
                <div class="space-y-3">
                  <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                      <span x-text="'$' + filters.priceMin"></span>
                      <span x-text="'$' + filters.priceMax"></span>
                    </div>
                    <div class="relative">
                      <input type="range" 
                             x-model="filters.priceMin"
                             min="0" 
                             :max="filters.priceMax - 10"
                             step="10"
                             @input="applyFilters"
                             class="absolute w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                      <input type="range" 
                             x-model="filters.priceMax"
                             :min="parseInt(filters.priceMin) + 10" 
                             max="2000"
                             step="10"
                             @input="applyFilters"
                             class="absolute w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>
                    <div class="h-2"></div>
                  </div>
                  
                  <!-- Quick Price Options -->
                  <div class="grid grid-cols-2 gap-2 text-xs">
                    <button @click="setPriceRange(0, 100)" 
                            :class="filters.priceMin == 0 && filters.priceMax == 100 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded text-center hover:opacity-80 transition">
                      Under $100
                    </button>
                    <button @click="setPriceRange(100, 250)" 
                            :class="filters.priceMin == 100 && filters.priceMax == 250 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded text-center hover:opacity-80 transition">
                      $100-$250
                    </button>
                    <button @click="setPriceRange(250, 500)" 
                            :class="filters.priceMin == 250 && filters.priceMax == 500 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded text-center hover:opacity-80 transition">
                      $250-$500
                    </button>
                    <button @click="setPriceRange(500, 2000)" 
                            :class="filters.priceMin == 500 && filters.priceMax == 2000 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                            class="p-2 rounded text-center hover:opacity-80 transition">
                      $500+
                    </button>
                  </div>
                </div>
              </div>

              <!-- Location Filter -->
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Location</h3>
                <div class="space-y-2">
                  <input type="text" 
                         x-model="filters.location"
                         @input="searchLocations"
                         placeholder="City, State, or Venue"
                         class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2">
                  
                  <div class="space-y-1">
                    <label class="flex items-center">
                      <input type="checkbox" x-model="filters.nearMe" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                      <span class="ml-2 text-sm text-gray-700">Near me</span>
                    </label>
                    
                    <div x-show="filters.nearMe" x-cloak class="ml-6">
                      <select x-model="filters.radius" @change="applyFilters" class="text-sm border border-gray-300 rounded px-2 py-1">
                        <option value="25">25 miles</option>
                        <option value="50">50 miles</option>
                        <option value="100">100 miles</option>
                        <option value="250">250 miles</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Availability Filter -->
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Availability</h3>
                <div class="space-y-2">
                  <label class="flex items-center">
                    <input type="checkbox" x-model="filters.availableOnly" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Available tickets only</span>
                  </label>
                  <label class="flex items-center">
                    <input type="checkbox" x-model="filters.priceDropsOnly" @change="applyFilters" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">Price drops only</span>
                  </label>
                </div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      </div>

      <!-- Results Area -->
      <div class="lg:col-span-3">
        
        <!-- Results Header -->
        <div class="flex items-center justify-between mb-6">
          <div>
            <div class="text-sm text-gray-600">
              <span x-text="loading ? 'Searching...' : `${totalResults} events found`"></span>
              <span x-show="hasActiveFilters" class="ml-2">
                • <button @click="clearAllFilters()" class="text-blue-600 hover:text-blue-800">Clear filters</button>
              </span>
            </div>
            <div x-show="filters.query" class="text-xs text-gray-500 mt-1">
              Results for "<span x-text="filters.query"></span>"
            </div>
          </div>
          
          <!-- Sort Options -->
          <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-700">Sort by:</span>
            <select x-model="filters.sortBy" @change="applyFilters" class="text-sm border border-gray-300 rounded-lg px-3 py-1.5">
              <option value="relevance">Relevance</option>
              <option value="date">Date</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="popularity">Popularity</option>
              <option value="distance">Distance</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div x-show="loading" x-cloak class="space-y-4">
          <div class="animate-pulse">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="viewMode === 'grid'">
              <template x-for="i in 6" :key="i">
                <div class="bg-gray-200 h-64 rounded-lg"></div>
              </template>
            </div>
            <div class="space-y-4" x-show="viewMode === 'list'">
              <template x-for="i in 8" :key="i">
                <div class="bg-gray-200 h-20 rounded-lg"></div>
              </template>
            </div>
          </div>
        </div>

        <!-- No Results -->
        <div x-show="!loading && tickets.length === 0" x-cloak>
          <x-ui.card class="text-center py-12">
            <x-ui.card-content>
              <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
              <h3 class="text-lg font-medium text-gray-900 mb-2">No events found</h3>
              <p class="text-gray-500 mb-4">Try adjusting your search criteria or filters</p>
              <div class="space-x-2">
                <button @click="clearAllFilters()" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  Clear all filters
                </button>
                <span class="text-gray-300">•</span>
                <button @click="showSuggestions = true" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                  Browse popular events
                </button>
              </div>
            </x-ui.card-content>
          </x-ui.card>
        </div>

        <!-- Grid View -->
        <div x-show="viewMode === 'grid' && !loading && tickets.length > 0" x-cloak>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="ticket in tickets" :key="ticket.id">
              <x-ui.card class="overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" @click="viewTicket(ticket)">
                <div class="relative">
                  <img :src="ticket.image || '/images/default-event.jpg'" 
                       :alt="ticket.title"
                       class="w-full h-48 object-cover bg-gray-200">
                  
                  <!-- Price Badge -->
                  <div class="absolute top-3 right-3 bg-white rounded-lg px-2 py-1 shadow-sm">
                    <div class="text-lg font-bold text-gray-900" x-text="formatCurrency(ticket.price)"></div>
                  </div>

                  <!-- Watchlist Button -->
                  <button @click="toggleWatchlist(ticket, $event)" 
                          :class="ticket.is_watched ? 'text-red-600 bg-white' : 'text-gray-400 bg-white/80'"
                          class="absolute top-3 left-3 p-2 rounded-full hover:bg-white transition"
                          :title="ticket.is_watched ? 'Remove from watchlist' : 'Add to watchlist'">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                    </svg>
                  </button>

                  <!-- Status Badge -->
                  <div class="absolute bottom-3 left-3" x-show="ticket.status && ticket.status !== 'available'">
                    <x-ui.badge :variant="getStatusVariant(ticket.status)" x-text="ticket.status.replace('-', ' ')" class="text-xs"></x-ui.badge>
                  </div>
                </div>

                <x-ui.card-content class="p-4">
                  <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-gray-900 text-sm leading-tight flex-1 mr-2" x-text="ticket.title"></h3>
                    <x-ui.badge :variant="getSportVariant(ticket.sport)" x-text="ticket.sport" class="text-xs flex-shrink-0"></x-ui.badge>
                  </div>

                  <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex items-center">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                      </svg>
                      <span x-text="ticket.venue" class="truncate"></span>
                    </div>

                    <div class="flex items-center">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                      <span x-text="formatDate(ticket.event_date)"></span>
                    </div>

                    <div x-show="ticket.distance" class="flex items-center">
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <span x-text="ticket.distance + ' miles away'"></span>
                    </div>
                  </div>

                  <div class="mt-3 flex items-center justify-between">
                    <div x-show="ticket.price_change" class="flex items-center">
                      <svg x-show="ticket.price_change > 0" class="w-4 h-4 mr-1 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 7H7v10"></path>
                      </svg>
                      <svg x-show="ticket.price_change < 0" class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17l-9.2-9.2M7 17h10V7"></path>
                      </svg>
                      <span :class="ticket.price_change > 0 ? 'text-red-600' : 'text-green-600'" 
                            class="text-xs font-medium" 
                            x-text="Math.abs(ticket.price_change) + '%'"></span>
                    </div>

                    <button @click="viewTicket(ticket, $event)" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                      View Details
                    </button>
                  </div>
                </x-ui.card-content>
              </x-ui.card>
            </template>
          </div>
        </div>

        <!-- List View -->
        <div x-show="viewMode === 'list' && !loading && tickets.length > 0" x-cloak>
          <x-ui.card>
            <x-ui.card-content class="p-0">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="ticket in tickets" :key="ticket.id">
                      <tr class="hover:bg-gray-50 cursor-pointer" @click="viewTicket(ticket)">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <img :src="ticket.image || '/images/default-event.jpg'" 
                                 :alt="ticket.title"
                                 class="w-12 h-12 rounded-lg object-cover bg-gray-200">
                            <div class="ml-4">
                              <div class="font-medium text-gray-900" x-text="ticket.title"></div>
                              <x-ui.badge :variant="getSportVariant(ticket.sport)" x-text="ticket.sport" class="text-xs"></x-ui.badge>
                            </div>
                          </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDate(ticket.event_date)"></td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="text-sm text-gray-900" x-text="ticket.venue"></div>
                          <div class="text-sm text-gray-500" x-text="ticket.city + ', ' + ticket.state"></div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="font-medium text-gray-900" x-text="formatCurrency(ticket.price)"></div>
                          <div x-show="ticket.price_change" class="flex items-center">
                            <span :class="ticket.price_change > 0 ? 'text-red-600' : 'text-green-600'" 
                                  class="text-xs" 
                                  x-text="(ticket.price_change > 0 ? '+' : '') + ticket.price_change + '%'"></span>
                          </div>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap">
                          <x-ui.badge :variant="getStatusVariant(ticket.status)" x-text="ticket.status.replace('-', ' ')" class="text-xs"></x-ui.badge>
                        </td>
                        
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                          <div class="flex items-center justify-end space-x-2">
                            <button @click="toggleWatchlist(ticket, $event)" 
                                    :class="ticket.is_watched ? 'text-red-600' : 'text-gray-400'"
                                    class="hover:opacity-75 transition">
                              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                              </svg>
                            </button>
                            <button @click="viewTicket(ticket, $event)" class="text-blue-600 hover:text-blue-900 transition">
                              View
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

        <!-- Map View -->
        <div x-show="viewMode === 'map' && !loading && tickets.length > 0" x-cloak>
          <x-ui.card>
            <x-ui.card-content class="p-0">
              <div class="grid grid-cols-1 lg:grid-cols-2">
                <!-- Map Container -->
                <div class="h-96 lg:h-[600px] bg-gray-200 relative">
                  <div id="ticketsMap" class="w-full h-full"></div>
                  <div class="absolute bottom-4 left-4 bg-white rounded-lg p-2 shadow-lg">
                    <div class="text-xs text-gray-600">
                      <span x-text="tickets.length"></span> events shown
                    </div>
                  </div>
                </div>
                
                <!-- Map Results List -->
                <div class="h-96 lg:h-[600px] overflow-y-auto">
                  <div class="space-y-2 p-4">
                    <template x-for="ticket in tickets" :key="ticket.id">
                      <div class="border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer transition"
                           @click="viewTicket(ticket)"
                           @mouseenter="highlightMapMarker(ticket.id)"
                           @mouseleave="unhighlightMapMarker(ticket.id)">
                        <div class="flex items-center space-x-3">
                          <img :src="ticket.image || '/images/default-event.jpg'" 
                               :alt="ticket.title"
                               class="w-12 h-12 rounded object-cover bg-gray-200">
                          <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm text-gray-900 truncate" x-text="ticket.title"></div>
                            <div class="text-xs text-gray-500" x-text="ticket.venue"></div>
                            <div class="text-xs text-gray-500" x-text="formatDate(ticket.event_date)"></div>
                          </div>
                          <div class="text-right">
                            <div class="font-bold text-sm text-gray-900" x-text="formatCurrency(ticket.price)"></div>
                            <div x-show="ticket.distance" class="text-xs text-gray-500" x-text="ticket.distance + ' mi'"></div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </x-ui.card-content>
          </x-ui.card>
        </div>

        <!-- Pagination -->
        <div x-show="totalPages > 1 && !loading" x-cloak class="flex items-center justify-between mt-8">
          <div class="text-sm text-gray-500">
            Showing <span x-text="(currentPage - 1) * perPage + 1"></span> to 
            <span x-text="Math.min(currentPage * perPage, totalResults)"></span> of 
            <span x-text="totalResults"></span> events
          </div>
          
          <div class="flex items-center space-x-2">
            <button @click="goToPage(currentPage - 1)" 
                    :disabled="currentPage === 1"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Previous
            </button>
            
            <template x-for="page in getPaginationRange()" :key="page">
              <button @click="goToPage(page)" 
                      :class="page === currentPage ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
                      class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium">
                <span x-text="page"></span>
              </button>
            </template>
            
            <button @click="goToPage(currentPage + 1)" 
                    :disabled="currentPage === totalPages"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Save Search Modal -->
    <div x-show="showSaveSearchModal" x-cloak class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50" @click.self="showSaveSearchModal = false">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Save Search</h3>
          <p class="text-sm text-gray-600">Get notified when new events match your criteria</p>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Search Name</label>
              <input type="text" 
                     x-model="searchName"
                     placeholder="e.g., Local Football Games"
                     class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
              <label class="flex items-center">
                <input type="checkbox" x-model="notifyNewMatches" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Email me when new events match</span>
              </label>
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
          <button @click="showSaveSearchModal = false" class="text-gray-600 hover:text-gray-800 px-4 py-2 text-sm font-medium">
            Cancel
          </button>
          <button @click="saveSearch()" 
                  :disabled="!searchName.trim()"
                  class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition">
            Save Search
          </button>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_key') }}&libraries=places"></script>
    <script>
      function ticketSearcher() {
        return {
          // State
          loading: false,
          viewMode: 'grid',
          showSaveSearchModal: false,
          searchTimeout: null,
          
          // Data
          tickets: [],
          availableSports: [
            { slug: 'football', name: 'Football', icon: '🏈', count: 0 },
            { slug: 'basketball', name: 'Basketball', icon: '🏀', count: 0 },
            { slug: 'baseball', name: 'Baseball', icon: '⚾', count: 0 },
            { slug: 'hockey', name: 'Hockey', icon: '🏒', count: 0 },
            { slug: 'soccer', name: 'Soccer', icon: '⚽', count: 0 },
            { slug: 'tennis', name: 'Tennis', icon: '🎾', count: 0 }
          ],
          
          // Filters
          filters: {
            query: '',
            sport: '',
            dateRange: '',
            dateFrom: '',
            dateTo: '',
            priceMin: 0,
            priceMax: 1000,
            location: '',
            nearMe: false,
            radius: 50,
            availableOnly: false,
            priceDropsOnly: false,
            sortBy: 'relevance'
          },
          
          // Pagination
          currentPage: 1,
          perPage: 24,
          totalPages: 1,
          totalResults: 0,
          
          // Save search
          searchName: '',
          notifyNewMatches: true,
          
          // Map
          map: null,
          markers: [],

          async init() {
            this.loadUrlParams();
            this.loadSportCounts();
            await this.searchTickets();
            this.initializeMap();
            this.setupGeolocation();
          },

          loadUrlParams() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.query = urlParams.get('q') || '';
            this.filters.sport = urlParams.get('sport') || '';
            this.filters.location = urlParams.get('location') || '';
          },

          debounceSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
              this.currentPage = 1;
              this.applyFilters();
            }, 500);
          },

          async applyFilters() {
            this.updateUrl();
            await this.searchTickets();
            this.updateMap();
          },

          async searchTickets() {
            this.loading = true;
            try {
              const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage,
                ...this.filters
              });

              const response = await fetch(`/api/tickets/search?${params}`);
              const data = await response.json();
              
              if (data.success) {
                this.tickets = data.tickets || [];
                this.totalResults = data.total || 0;
                this.totalPages = Math.ceil(this.totalResults / this.perPage);
              }
            } catch (error) {
              console.error('Failed to search tickets:', error);
            } finally {
              this.loading = false;
            }
          },

          async loadSportCounts() {
            try {
              const response = await fetch('/api/tickets/sport-counts');
              const data = await response.json();
              
              if (data.success) {
                this.availableSports.forEach(sport => {
                  sport.count = data.counts[sport.slug] || 0;
                });
              }
            } catch (error) {
              console.error('Failed to load sport counts:', error);
            }
          },

          applyQuickFilter(type) {
            switch (type) {
              case 'football':
              case 'basketball':
              case 'baseball':
                this.filters.sport = this.filters.sport === type ? '' : type;
                break;
              case 'this-weekend':
                this.filters.dateRange = this.filters.dateRange === 'this-weekend' ? '' : 'this-weekend';
                break;
              case 'under-100':
                if (this.filters.priceMax === 100) {
                  this.filters.priceMax = 1000;
                } else {
                  this.filters.priceMax = 100;
                }
                break;
            }
            this.applyFilters();
          },

          setPriceRange(min, max) {
            this.filters.priceMin = min;
            this.filters.priceMax = max;
            this.applyFilters();
          },

          clearAllFilters() {
            this.filters = {
              query: this.filters.query, // Keep the main search query
              sport: '',
              dateRange: '',
              dateFrom: '',
              dateTo: '',
              priceMin: 0,
              priceMax: 1000,
              location: '',
              nearMe: false,
              radius: 50,
              availableOnly: false,
              priceDropsOnly: false,
              sortBy: 'relevance'
            };
            this.currentPage = 1;
            this.applyFilters();
          },

          async toggleWatchlist(ticket, event) {
            event.stopPropagation();
            
            try {
              const response = await fetch(`/api/tickets/${ticket.id}/watchlist`, {
                method: ticket.is_watched ? 'DELETE' : 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                ticket.is_watched = !ticket.is_watched;
                this.showNotification(
                  ticket.is_watched ? 'Added to Watchlist' : 'Removed from Watchlist',
                  ticket.is_watched ? 'You\'ll receive price alerts' : 'No longer watching',
                  'success'
                );
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update watchlist', 'error');
            }
          },

          viewTicket(ticket, event = null) {
            if (event) event.stopPropagation();
            window.location.href = `/tickets/${ticket.id}`;
          },

          goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
              this.currentPage = page;
              this.applyFilters();
            }
          },

          getPaginationRange() {
            const current = this.currentPage;
            const total = this.totalPages;
            const range = [];
            
            let start = Math.max(1, current - 2);
            let end = Math.min(total, current + 2);
            
            for (let i = start; i <= end; i++) {
              range.push(i);
            }
            
            return range;
          },

          initializeMap() {
            if (this.viewMode === 'map' && this.tickets.length > 0) {
              const mapElement = document.getElementById('ticketsMap');
              if (mapElement) {
                this.map = new google.maps.Map(mapElement, {
                  zoom: 10,
                  center: { lat: 40.7128, lng: -74.0060 }
                });
                
                this.updateMap();
              }
            }
          },

          updateMap() {
            if (this.map && this.tickets.length > 0) {
              // Clear existing markers
              this.markers.forEach(marker => marker.setMap(null));
              this.markers = [];
              
              // Add new markers
              const bounds = new google.maps.LatLngBounds();
              
              this.tickets.forEach(ticket => {
                if (ticket.latitude && ticket.longitude) {
                  const marker = new google.maps.Marker({
                    position: { lat: ticket.latitude, lng: ticket.longitude },
                    map: this.map,
                    title: ticket.title,
                    icon: {
                      url: '/images/marker-icon.png',
                      scaledSize: new google.maps.Size(30, 30)
                    }
                  });
                  
                  const infoWindow = new google.maps.InfoWindow({
                    content: `
                      <div class="p-2">
                        <h3 class="font-semibold">${ticket.title}</h3>
                        <p class="text-sm text-gray-600">${ticket.venue}</p>
                        <p class="text-sm text-gray-600">${this.formatDate(ticket.event_date)}</p>
                        <p class="font-bold text-lg text-blue-600">${this.formatCurrency(ticket.price)}</p>
                        <a href="/tickets/${ticket.id}" class="text-blue-600 hover:text-blue-800 text-sm">View Details</a>
                      </div>
                    `
                  });
                  
                  marker.addListener('click', () => {
                    infoWindow.open(this.map, marker);
                  });
                  
                  this.markers.push(marker);
                  bounds.extend(marker.getPosition());
                }
              });
              
              if (this.tickets.length > 0) {
                this.map.fitBounds(bounds);
              }
            }
          },

          highlightMapMarker(ticketId) {
            // Implementation for highlighting map marker on hover
          },

          unhighlightMapMarker(ticketId) {
            // Implementation for unhighlighting map marker
          },

          setupGeolocation() {
            if (this.filters.nearMe && navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(
                (position) => {
                  this.userLocation = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                  };
                  this.applyFilters();
                },
                (error) => {
                  console.warn('Geolocation error:', error);
                  this.filters.nearMe = false;
                }
              );
            }
          },

          saveCurrentSearch() {
            this.showSaveSearchModal = true;
            this.searchName = this.generateSearchName();
          },

          generateSearchName() {
            let name = '';
            if (this.filters.sport) name += this.availableSports.find(s => s.slug === this.filters.sport)?.name || '';
            if (this.filters.location) name += (name ? ' in ' : '') + this.filters.location;
            if (this.filters.query) name += (name ? ': ' : '') + this.filters.query;
            return name || 'My Search';
          },

          async saveSearch() {
            try {
              const response = await fetch('/api/saved-searches', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  name: this.searchName,
                  filters: this.filters,
                  notify_new_matches: this.notifyNewMatches
                })
              });

              const data = await response.json();

              if (data.success) {
                this.showSaveSearchModal = false;
                this.showNotification('Search Saved', 'You\'ll be notified of matching events', 'success');
              } else {
                this.showNotification('Error', data.message || 'Failed to save search', 'error');
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to save search', 'error');
            }
          },

          updateUrl() {
            const params = new URLSearchParams();
            if (this.filters.query) params.set('q', this.filters.query);
            if (this.filters.sport) params.set('sport', this.filters.sport);
            if (this.filters.location) params.set('location', this.filters.location);
            
            const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', newUrl);
          },

          get hasActiveFilters() {
            return this.filters.sport || this.filters.dateRange || this.filters.location || 
                   this.filters.priceMin > 0 || this.filters.priceMax < 1000 ||
                   this.filters.availableOnly || this.filters.priceDropsOnly;
          },

          getSportVariant(sport) {
            const variants = {
              football: 'success',
              basketball: 'warning',
              baseball: 'info',
              hockey: 'secondary',
              soccer: 'primary'
            };
            return variants[sport] || 'default';
          },

          getStatusVariant(status) {
            const variants = {
              available: 'success',
              limited: 'warning',
              'selling-fast': 'danger',
              'sold-out': 'secondary'
            };
            return variants[status] || 'default';
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
              weekday: 'short',
              month: 'short', 
              day: 'numeric',
              year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
            });
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
