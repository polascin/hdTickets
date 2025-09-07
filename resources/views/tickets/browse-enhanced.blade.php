<x-unified-layout title="Browse Sports Tickets" subtitle="Find and monitor tickets for your favorite events">
  <x-slot name="headerActions">
    <div class="flex items-center space-x-3">
      <!-- Search Stats -->
      <div class="flex items-center bg-blue-100 text-blue-800 px-3 py-2 rounded-lg text-sm font-medium">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
        <span x-text="`${filteredTicketsCount} tickets found`">Loading...</span>
      </div>
      
      <button @click="showFilters = !showFilters" 
              :class="showFilters ? 'bg-gray-200 text-gray-800' : 'bg-gray-100 text-gray-600'"
              class="px-3 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
        </svg>
        Filters
      </button>
    </div>
  </x-slot>

  <div x-data="ticketBrowser()" x-init="init()" class="space-y-6">
    <!-- Search Header -->
    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 text-white rounded-lg p-6">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Find Your Perfect Tickets</h1>
        <p class="text-blue-100 mb-6">Search through thousands of sports events and get real-time price alerts</p>
        
        <!-- Search Bar -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <svg class="w-5 h-5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <input type="text" 
                 x-model="searchQuery"
                 @input.debounced.500ms="performSearch()"
                 placeholder="Search teams, events, venues... (e.g. 'Lakers vs Warriors')"
                 class="w-full pl-12 pr-4 py-4 text-lg bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-white/50">
          
          <!-- Search Suggestions -->
          <div x-show="searchSuggestions.length > 0 && searchQuery.length > 2" 
               x-transition
               class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-60 overflow-y-auto">
            <template x-for="suggestion in searchSuggestions.slice(0, 8)" :key="suggestion.id">
              <div @click="selectSuggestion(suggestion)" 
                   class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-gray-900" x-text="suggestion.title"></div>
                    <div class="text-sm text-gray-600" x-text="suggestion.subtitle"></div>
                  </div>
                  <div class="text-sm text-blue-600" x-text="suggestion.category"></div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Panel -->
    <div x-show="showFilters" x-transition class="bg-white rounded-lg shadow-md border border-gray-200">
      <div class="p-6">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-lg font-semibold text-gray-900">Advanced Filters</h3>
          <button @click="clearFilters()" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
            Clear All
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Sport Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sport</label>
            <select x-model="filters.sport" @change="applyFilters()" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">All Sports</option>
              <option value="basketball">🏀 Basketball</option>
              <option value="football">🏈 Football</option>
              <option value="baseball">⚾ Baseball</option>
              <option value="hockey">🏒 Hockey</option>
              <option value="soccer">⚽ Soccer</option>
              <option value="tennis">🎾 Tennis</option>
              <option value="golf">⛳ Golf</option>
              <option value="racing">🏁 Racing</option>
            </select>
          </div>

          <!-- Date Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select x-model="filters.dateRange" @change="applyFilters()" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">Any Time</option>
              <option value="today">Today</option>
              <option value="tomorrow">Tomorrow</option>
              <option value="this-week">This Week</option>
              <option value="this-month">This Month</option>
              <option value="next-month">Next Month</option>
              <option value="custom">Custom Range</option>
            </select>
            
            <!-- Custom Date Inputs -->
            <div x-show="filters.dateRange === 'custom'" x-transition class="mt-2 space-y-2">
              <input type="date" 
                     x-model="filters.startDate" 
                     @change="applyFilters()"
                     class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
              <input type="date" 
                     x-model="filters.endDate" 
                     @change="applyFilters()"
                     class="w-full border border-gray-300 rounded px-2 py-1 text-sm">
            </div>
          </div>

          <!-- Price Range -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Price Range: $<span x-text="filters.minPrice"></span> - $<span x-text="filters.maxPrice"></span>
            </label>
            <div class="space-y-2">
              <input type="range" 
                     x-model="filters.minPrice" 
                     min="0" max="1000" step="10"
                     @input="applyFilters()"
                     class="w-full">
              <input type="range" 
                     x-model="filters.maxPrice" 
                     min="0" max="2000" step="10"
                     @input="applyFilters()"
                     class="w-full">
            </div>
          </div>

          <!-- Location -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
            <select x-model="filters.location" @change="applyFilters()" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="">All Locations</option>
              <option value="new-york">New York</option>
              <option value="los-angeles">Los Angeles</option>
              <option value="chicago">Chicago</option>
              <option value="dallas">Dallas</option>
              <option value="philadelphia">Philadelphia</option>
              <option value="houston">Houston</option>
              <option value="washington">Washington DC</option>
              <option value="miami">Miami</option>
              <option value="atlanta">Atlanta</option>
              <option value="boston">Boston</option>
            </select>
          </div>
        </div>

        <!-- Quick Filters -->
        <div class="mt-6 pt-6 border-t border-gray-200">
          <label class="block text-sm font-medium text-gray-700 mb-3">Quick Filters</label>
          <div class="flex flex-wrap gap-2">
            <button @click="toggleQuickFilter('trending')" 
                    :class="filters.quickFilters.includes('trending') ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-600 hover:text-white transition">
              🔥 Trending
            </button>
            <button @click="toggleQuickFilter('price-drop')" 
                    :class="filters.quickFilters.includes('price-drop') ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm font-medium hover:bg-green-600 hover:text-white transition">
              📉 Price Drops
            </button>
            <button @click="toggleQuickFilter('last-minute')" 
                    :class="filters.quickFilters.includes('last-minute') ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm font-medium hover:bg-red-600 hover:text-white transition">
              ⏰ Last Minute
            </button>
            <button @click="toggleQuickFilter('premium')" 
                    :class="filters.quickFilters.includes('premium') ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm font-medium hover:bg-purple-600 hover:text-white transition">
              ⭐ Premium
            </button>
            <button @click="toggleQuickFilter('available')" 
                    :class="filters.quickFilters.includes('available') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'"
                    class="px-3 py-1 rounded-full text-sm font-medium hover:bg-indigo-600 hover:text-white transition">
              ✅ Available Now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sorting and View Options -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <div class="flex items-center">
          <label class="text-sm font-medium text-gray-700 mr-2">Sort by:</label>
          <select x-model="sortBy" @change="applySorting()" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="relevance">Relevance</option>
            <option value="date-asc">Date (Earliest First)</option>
            <option value="date-desc">Date (Latest First)</option>
            <option value="price-asc">Price (Low to High)</option>
            <option value="price-desc">Price (High to Low)</option>
            <option value="popularity">Popularity</option>
            <option value="distance">Distance</option>
          </select>
        </div>
        
        <div class="flex items-center">
          <label class="text-sm font-medium text-gray-700 mr-2">Show:</label>
          <select x-model="perPage" @change="updatePagination()" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="12">12 per page</option>
            <option value="24">24 per page</option>
            <option value="48">48 per page</option>
          </select>
        </div>
      </div>
      
      <div class="flex items-center space-x-2">
        <span class="text-sm text-gray-600">View:</span>
        <button @click="viewMode = 'grid'" 
                :class="viewMode === 'grid' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="p-2 rounded hover:bg-blue-600 hover:text-white transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
          </svg>
        </button>
        <button @click="viewMode = 'list'" 
                :class="viewMode === 'list' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600'"
                class="p-2 rounded hover:bg-blue-600 hover:text-white transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="text-center py-12">
      <div class="inline-flex items-center">
        <svg class="animate-spin w-6 h-6 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-gray-600">Loading tickets...</span>
      </div>
    </div>

    <!-- Tickets Grid/List -->
    <div x-show="!loading">
      <!-- Grid View -->
      <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <template x-for="ticket in displayedTickets" :key="ticket.id">
          <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow overflow-hidden">
            <!-- Event Image -->
            <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600">
              <img x-show="ticket.image" 
                   :src="ticket.image" 
                   :alt="ticket.title"
                   class="w-full h-full object-cover">
              
              <!-- Price Badge -->
              <div class="absolute top-3 right-3 bg-white/90 backdrop-blur-sm px-2 py-1 rounded-full">
                <div class="text-sm font-bold text-gray-900" x-text="`$${ticket.price}`"></div>
              </div>
              
              <!-- Status Badge -->
              <div x-show="ticket.status" 
                   class="absolute top-3 left-3 px-2 py-1 rounded-full text-xs font-medium"
                   :class="{
                     'bg-green-500 text-white': ticket.status === 'available',
                     'bg-red-500 text-white': ticket.status === 'limited',
                     'bg-orange-500 text-white': ticket.status === 'selling-fast'
                   }">
                <span x-text="ticket.status.replace('-', ' ')"></span>
              </div>
            </div>

            <!-- Event Details -->
            <div class="p-4">
              <div class="mb-2">
                <h3 class="font-semibold text-gray-900 line-clamp-2" x-text="ticket.title"></h3>
                <p class="text-sm text-gray-600" x-text="ticket.venue"></p>
              </div>
              
              <div class="flex items-center justify-between mb-3">
                <div class="text-sm text-gray-500">
                  <div x-text="formatDate(ticket.date)"></div>
                  <div x-text="ticket.time"></div>
                </div>
                <div class="text-right">
                  <div class="text-sm text-gray-500">Starting at</div>
                  <div class="font-bold text-lg text-gray-900" x-text="`$${ticket.price}`"></div>
                </div>
              </div>

              <!-- Price History -->
              <div x-show="ticket.priceHistory" class="mb-3">
                <div class="flex items-center text-sm">
                  <span class="text-gray-500 mr-2">24h change:</span>
                  <span :class="ticket.priceChange >= 0 ? 'text-red-600' : 'text-green-600'" 
                        x-text="`${ticket.priceChange >= 0 ? '+' : ''}${ticket.priceChange}%`"></span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex space-x-2">
                <button @click="viewTicketDetails(ticket)" 
                        class="flex-1 bg-blue-600 text-white py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition">
                  View Details
                </button>
                <button @click="toggleWatchlist(ticket)" 
                        :class="ticket.isWatched ? 'bg-red-100 text-red-600 border-red-200' : 'bg-gray-100 text-gray-600 border-gray-200'"
                        class="p-2 rounded border hover:bg-gray-200 transition">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- List View -->
      <div x-show="viewMode === 'list'" class="space-y-4">
        <template x-for="ticket in displayedTickets" :key="ticket.id">
          <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-shadow">
            <div class="p-6">
              <div class="flex items-center justify-between">
                <div class="flex-1">
                  <div class="flex items-start justify-between">
                    <div>
                      <h3 class="font-semibold text-lg text-gray-900" x-text="ticket.title"></h3>
                      <p class="text-gray-600" x-text="ticket.venue"></p>
                      <div class="flex items-center mt-2 space-x-4 text-sm text-gray-500">
                        <span x-text="formatDate(ticket.date)"></span>
                        <span x-text="ticket.time"></span>
                        <span x-text="ticket.sport" class="capitalize"></span>
                      </div>
                    </div>
                    
                    <div class="text-right ml-4">
                      <div class="text-sm text-gray-500">Starting at</div>
                      <div class="text-2xl font-bold text-gray-900" x-text="`$${ticket.price}`"></div>
                      <div x-show="ticket.priceChange" 
                           :class="ticket.priceChange >= 0 ? 'text-red-600' : 'text-green-600'" 
                           class="text-sm"
                           x-text="`${ticket.priceChange >= 0 ? '+' : ''}${ticket.priceChange}% (24h)`"></div>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center space-x-2">
                      <span x-show="ticket.status" 
                            class="px-2 py-1 rounded-full text-xs font-medium"
                            :class="{
                              'bg-green-100 text-green-800': ticket.status === 'available',
                              'bg-red-100 text-red-800': ticket.status === 'limited',
                              'bg-orange-100 text-orange-800': ticket.status === 'selling-fast'
                            }">
                        <span x-text="ticket.status.replace('-', ' ')"></span>
                      </span>
                      
                      <span x-show="ticket.trending" class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                        🔥 Trending
                      </span>
                    </div>
                    
                    <div class="flex space-x-2">
                      <button @click="toggleWatchlist(ticket)" 
                              :class="ticket.isWatched ? 'bg-red-100 text-red-600 border-red-200' : 'bg-gray-100 text-gray-600 border-gray-200'"
                              class="px-3 py-2 rounded border hover:bg-gray-200 transition text-sm">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                        <span x-text="ticket.isWatched ? 'Watching' : 'Watch'"></span>
                      </button>
                      <button @click="viewTicketDetails(ticket)" 
                              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition text-sm">
                        View Details
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- No Results -->
    <div x-show="!loading && displayedTickets.length === 0" class="text-center py-12">
      <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets found</h3>
      <p class="text-gray-600 mb-6">Try adjusting your search criteria or filters to find more results.</p>
      <button @click="clearFilters(); performSearch()" 
              class="bg-blue-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-700 transition">
        Clear Filters & Search Again
      </button>
    </div>

    <!-- Pagination -->
    <div x-show="!loading && totalPages > 1" class="flex items-center justify-center space-x-2 mt-8">
      <button @click="goToPage(currentPage - 1)" 
              :disabled="currentPage <= 1"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
        Previous
      </button>
      
      <template x-for="page in paginationPages" :key="page">
        <button @click="goToPage(page)" 
                :class="page === currentPage ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'"
                class="px-3 py-2 border rounded-lg text-sm font-medium"
                x-text="page"></button>
      </template>
      
      <button @click="goToPage(currentPage + 1)" 
              :disabled="currentPage >= totalPages"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
        Next
      </button>
    </div>
  </div>

  @push('scripts')
    <script>
      function ticketBrowser() {
        return {
          // State
          loading: false,
          showFilters: false,
          searchQuery: '',
          searchSuggestions: [],
          tickets: [],
          displayedTickets: [],
          viewMode: 'grid',
          sortBy: 'relevance',
          perPage: 24,
          currentPage: 1,
          totalPages: 1,

          // Filters
          filters: {
            sport: '',
            dateRange: '',
            startDate: '',
            endDate: '',
            minPrice: 0,
            maxPrice: 500,
            location: '',
            quickFilters: []
          },

          init() {
            this.loadTickets();
            this.setupRealTimeUpdates();
            
            // URL params handling
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('q')) {
              this.searchQuery = urlParams.get('q');
              this.performSearch();
            }
          },

          async loadTickets() {
            this.loading = true;
            try {
              const response = await fetch('/api/tickets/search', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                  query: this.searchQuery,
                  filters: this.filters,
                  sort: this.sortBy,
                  page: this.currentPage,
                  per_page: this.perPage
                })
              });

              const data = await response.json();
              
              this.tickets = data.tickets || [];
              this.displayedTickets = this.tickets;
              this.totalPages = data.total_pages || 1;
              this.updateURL();
              
            } catch (error) {
              console.error('Failed to load tickets:', error);
              this.showNotification('Error', 'Failed to load tickets. Please try again.', 'error');
            } finally {
              this.loading = false;
            }
          },

          async performSearch() {
            if (this.searchQuery.length > 2) {
              await this.loadSuggestions();
            } else {
              this.searchSuggestions = [];
            }
            
            this.currentPage = 1;
            await this.loadTickets();
          },

          async loadSuggestions() {
            try {
              const response = await fetch(`/api/tickets/suggestions?q=${encodeURIComponent(this.searchQuery)}`);
              const data = await response.json();
              this.searchSuggestions = data.suggestions || [];
            } catch (error) {
              console.error('Failed to load suggestions:', error);
            }
          },

          selectSuggestion(suggestion) {
            this.searchQuery = suggestion.title;
            this.searchSuggestions = [];
            this.performSearch();
          },

          applyFilters() {
            this.currentPage = 1;
            this.loadTickets();
          },

          toggleQuickFilter(filter) {
            const index = this.filters.quickFilters.indexOf(filter);
            if (index > -1) {
              this.filters.quickFilters.splice(index, 1);
            } else {
              this.filters.quickFilters.push(filter);
            }
            this.applyFilters();
          },

          clearFilters() {
            this.filters = {
              sport: '',
              dateRange: '',
              startDate: '',
              endDate: '',
              minPrice: 0,
              maxPrice: 500,
              location: '',
              quickFilters: []
            };
            this.searchQuery = '';
            this.applyFilters();
          },

          applySorting() {
            this.currentPage = 1;
            this.loadTickets();
          },

          updatePagination() {
            this.currentPage = 1;
            this.loadTickets();
          },

          goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
              this.currentPage = page;
              this.loadTickets();
              window.scrollTo({ top: 0, behavior: 'smooth' });
            }
          },

          get paginationPages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
              pages.push(i);
            }
            
            return pages;
          },

          get filteredTicketsCount() {
            return this.displayedTickets.length;
          },

          async toggleWatchlist(ticket) {
            try {
              const response = await fetch(`/api/tickets/${ticket.id}/watchlist`, {
                method: ticket.isWatched ? 'DELETE' : 'POST',
                headers: {
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
              });

              if (response.ok) {
                ticket.isWatched = !ticket.isWatched;
                this.showNotification(
                  ticket.isWatched ? 'Added to Watchlist' : 'Removed from Watchlist',
                  ticket.isWatched ? 'You\'ll receive price alerts for this event' : 'You\'ll no longer receive alerts',
                  'success'
                );
              }
            } catch (error) {
              this.showNotification('Error', 'Failed to update watchlist', 'error');
            }
          },

          viewTicketDetails(ticket) {
            window.location.href = `/tickets/${ticket.id}`;
          },

          formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
              weekday: 'short', 
              month: 'short', 
              day: 'numeric' 
            });
          },

          updateURL() {
            const params = new URLSearchParams();
            if (this.searchQuery) params.set('q', this.searchQuery);
            if (this.filters.sport) params.set('sport', this.filters.sport);
            if (this.filters.location) params.set('location', this.filters.location);
            if (this.currentPage > 1) params.set('page', this.currentPage);

            const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
            window.history.replaceState({}, '', newURL);
          },

          setupRealTimeUpdates() {
            // WebSocket connection for real-time price updates
            if (window.Echo) {
              window.Echo.channel('ticket-updates')
                .listen('TicketPriceUpdated', (e) => {
                  const ticket = this.displayedTickets.find(t => t.id === e.ticket.id);
                  if (ticket) {
                    ticket.price = e.ticket.price;
                    ticket.priceChange = e.ticket.priceChange;
                  }
                })
                .listen('TicketAvailabilityUpdated', (e) => {
                  const ticket = this.displayedTickets.find(t => t.id === e.ticket.id);
                  if (ticket) {
                    ticket.status = e.ticket.status;
                  }
                });
            }

            // Periodic updates
            setInterval(() => {
              if (!this.loading) {
                this.loadTickets();
              }
            }, 60000); // Update every minute
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
