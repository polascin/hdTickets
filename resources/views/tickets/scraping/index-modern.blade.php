{{-- Enhanced Sports Tickets Scraping Interface --}}
<x-unified-layout title="Sports Event Tickets" subtitle="Browse and search for sports event tickets across multiple platforms">
  
  {{-- Enhanced Sports Color Scheme CSS --}}
  <link rel="stylesheet" href="{{ asset('css/sports-tickets-colors.css') }}">
  
  <x-slot name="headerActions">
    <div class="flex flex-col sm:flex-row gap-3">
      <button id="refresh-tickets" type="button"
        class="hd-button hd-button--outline hd-button--md inline-flex items-center gap-2" 
        aria-label="Refresh tickets"
        title="Refresh ticket data">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
          </path>
        </svg>
        <span class="hd-button__text">Refresh</span>
      </button>
      
      <button type="button" data-clear-filters
        class="hd-button hd-button--ghost hd-button--md inline-flex items-center gap-2" 
        aria-label="Clear all filters">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
          </path>
        </svg>
        <span class="hd-button__text">Clear Filters</span>
      </button>
      
      <button id="create-alert" type="button"
        class="hd-button hd-button--primary hd-button--md inline-flex items-center gap-2" 
        aria-label="Create new price alert">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5"></path>
        </svg>
        <span class="hd-button__text">Create Alert</span>
      </button>
      
      <div class="relative">
        <button type="button" id="export-dropdown-button"
          class="hd-button hd-button--secondary hd-button--md inline-flex items-center gap-2"
          aria-label="Export options">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-4-4m4 4l4-4m6-8V4a2 2 0 00-2-2H4a2 2 0 00-2 2v16c0 1.1.9 2 2 2h16a2 2 0 002-2V6"></path>
          </svg>
          <span>Export</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        
        <div id="export-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
          <div class="py-1">
            <a href="#" class="export-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-format="csv">
              Export as CSV
            </a>
            <a href="#" class="export-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-format="xlsx">
              Export as Excel
            </a>
            <a href="#" class="export-option block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-format="pdf">
              Export as PDF
            </a>
          </div>
        </div>
      </div>
    </div>
  </x-slot>

  {{-- Error/Success Messages --}}
  @if (session('error') || isset($error))
    <x-ui.alert variant="error" class="mb-6" role="alert" id="error-message">
      <x-slot name="title">Error</x-slot>
      {{ session('error') ?? $error }}
    </x-ui.alert>
  @endif

  @if (session('success'))
    <x-ui.alert variant="success" class="mb-6" role="alert" id="success-message">
      <x-slot name="title">Success</x-slot>
      {{ session('success') }}
    </x-ui.alert>
  @endif

  {{-- Active Filters Summary --}}
  @if (isset($activeFilters) && array_filter($activeFilters))
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg" role="region" aria-label="Active filters">
      <div class="flex items-start justify-between">
        <div>
          <h4 class="font-medium text-blue-900 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
            </svg>
            Active Filters:
          </h4>
          <div class="flex flex-wrap gap-2" id="active-filters-list">
            @foreach ($activeFilters as $key => $value)
              @if ($value && $value !== '' && $value !== false)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                      role="button" tabindex="0" data-filter="{{ $key }}"
                      onkeydown="if(event.key==='Enter') removeFilter('{{ $key }}')">
                  {{ ucfirst(str_replace(['_', 'only'], [' ', ''], $key)) }}:
                  @if (is_bool($value))
                    {{ $value ? 'Yes' : 'No' }}
                  @else
                    {{ $value }}
                  @endif
                  <button type="button"
                    class="ml-1 text-blue-400 hover:text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 rounded"
                    onclick="removeFilter('{{ $key }}')" aria-label="Remove {{ $key }} filter">
                    ×
                  </button>
                </span>
              @endif
            @endforeach
          </div>
        </div>
        <button data-clear-filters
          class="text-blue-600 hover:text-blue-800 text-sm font-medium focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1">
          Clear All
        </button>
      </div>
    </div>
  @endif

  {{-- Enhanced Search & Filters Panel --}}
  <x-ui.card class="mb-6">
    <x-ui.card-header class="border-b">
      <div class="flex items-center justify-between">
        <h3 class="flex items-center text-lg font-medium text-gray-900">
          <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
          </svg>
          Search & Filters
        </h3>
        <button type="button" id="collapse-filters" 
          class="p-2 text-gray-400 hover:text-gray-600 rounded-lg focus:ring-2 focus:ring-gray-500"
          aria-label="Toggle filter panel">
          <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
      </div>
    </x-ui.card-header>
    
    <x-ui.card-content id="filter-panel">
      <form id="filters-form" class="space-y-6" role="search" aria-label="Ticket search and filtering">
        {{-- Enhanced Search Input with Autocomplete --}}
        <div class="relative">
          <label for="keywords" class="block text-sm font-medium text-gray-700 mb-2">Search Tickets</label>
          <div class="relative">
            <input type="text" name="keywords" id="keywords" 
              placeholder="Search by event, team, venue, or keyword..."
              value="{{ request('keywords') }}"
              class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
              autocomplete="off" 
              aria-describedby="keywords-help" 
              data-search-input />
              
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2" aria-hidden="true">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            
            <button type="button" id="clear-search"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"
              aria-label="Clear search">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div id="keywords-help" class="text-xs text-gray-500 mt-1">
            Enter keywords to search for sports event tickets
          </div>
          
          {{-- Search Suggestions Dropdown --}}
          <div id="search-suggestions"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg hidden max-h-60 overflow-y-auto">
            <div class="p-2 text-xs text-gray-500 border-b">
              <div class="flex items-center justify-between">
                <span>Search suggestions:</span>
                <button type="button" id="close-suggestions" class="text-gray-400 hover:text-gray-600">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
            <div id="suggestions-list" class="py-1"></div>
          </div>
          
          {{-- Popular Searches --}}
          @if(isset($popularSearches) && !empty($popularSearches))
          <div class="mt-2">
            <span class="text-xs text-gray-500 mr-2">Popular:</span>
            <div class="inline-flex flex-wrap gap-1">
              @foreach(array_slice($popularSearches, 0, 5) as $popular)
                <button type="button" class="popular-search text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors"
                  data-search="{{ $popular }}">{{ $popular }}</button>
              @endforeach
            </div>
          </div>
          @endif
        </div>

        {{-- Main Filter Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          {{-- Platform Filter --}}
          <div>
            <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
            <select name="platform" id="platform"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Platforms</option>
              <optgroup label="Major International">
                <option value="stubhub" {{ request('platform') == 'stubhub' ? 'selected' : '' }}>StubHub</option>
                <option value="ticketmaster" {{ request('platform') == 'ticketmaster' ? 'selected' : '' }}>Ticketmaster</option>
                <option value="viagogo" {{ request('platform') == 'viagogo' ? 'selected' : '' }}>Viagogo</option>
              </optgroup>
              <optgroup label="UK Platforms">
                <option value="seetickets" {{ request('platform') == 'seetickets' ? 'selected' : '' }}>See Tickets UK</option>
                <option value="ticketek" {{ request('platform') == 'ticketek' ? 'selected' : '' }}>Ticketek UK</option>
                <option value="axs" {{ request('platform') == 'axs' ? 'selected' : '' }}>AXS</option>
                <option value="gigantic" {{ request('platform') == 'gigantic' ? 'selected' : '' }}>Gigantic</option>
                <option value="skiddle" {{ request('platform') == 'skiddle' ? 'selected' : '' }}>Skiddle</option>
                <option value="livenation" {{ request('platform') == 'livenation' ? 'selected' : '' }}>LiveNation UK</option>
              </optgroup>
              <optgroup label="European Platforms">
                <option value="eventim" {{ request('platform') == 'eventim' ? 'selected' : '' }}>Eventim (Germany)</option>
                <option value="ticketone" {{ request('platform') == 'ticketone' ? 'selected' : '' }}>TicketOne (Italy)</option>
                <option value="stargreen" {{ request('platform') == 'stargreen' ? 'selected' : '' }}>Stargreen (Germany)</option>
                <option value="ticketswap" {{ request('platform') == 'ticketswap' ? 'selected' : '' }}>TicketSwap (Resale)</option>
              </optgroup>
            </select>
          </div>

          {{-- Sport Filter --}}
          <div>
            <label for="sport" class="block text-sm font-medium text-gray-700 mb-2">Sport</label>
            <select name="sport" id="sport"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Sports</option>
              <option value="football" {{ request('sport') == 'football' ? 'selected' : '' }}>Football ⚽</option>
              <option value="rugby" {{ request('sport') == 'rugby' ? 'selected' : '' }}>Rugby 🏉</option>
              <option value="cricket" {{ request('sport') == 'cricket' ? 'selected' : '' }}>Cricket 🏏</option>
              <option value="tennis" {{ request('sport') == 'tennis' ? 'selected' : '' }}>Tennis 🎾</option>
              <option value="basketball" {{ request('sport') == 'basketball' ? 'selected' : '' }}>Basketball 🏀</option>
              <option value="motorsport" {{ request('sport') == 'motorsport' ? 'selected' : '' }}>Motorsport 🏎️</option>
              <option value="golf" {{ request('sport') == 'golf' ? 'selected' : '' }}>Golf ⛳</option>
              <option value="boxing" {{ request('sport') == 'boxing' ? 'selected' : '' }}>Boxing 🥊</option>
            </select>
          </div>

          {{-- Price Range --}}
          <div class="col-span-1 md:col-span-2 lg:col-span-1">
            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
            <div class="grid grid-cols-2 gap-2">
              <input type="number" name="min_price" id="min_price" placeholder="Min £" min="0" step="0.01"
                value="{{ request('min_price') }}"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <input type="number" name="max_price" id="max_price" placeholder="Max £" min="0" step="0.01"
                value="{{ request('max_price') }}"
                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
            </div>
          </div>

          {{-- Sort Options --}}
          <div>
            <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort_by" id="sort_by"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="scraped_at" {{ request('sort_by', 'scraped_at') == 'scraped_at' ? 'selected' : '' }}>Latest Updates</option>
              <option value="event_date" {{ request('sort_by') == 'event_date' ? 'selected' : '' }}>Event Date (Soonest)</option>
              <option value="min_price" {{ request('sort_by') == 'min_price' ? 'selected' : '' }}>Price (Low to High)</option>
              <option value="max_price" {{ request('sort_by') == 'max_price' ? 'selected' : '' }}>Price (High to Low)</option>
              <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Event Name (A-Z)</option>
              <option value="availability" {{ request('sort_by') == 'availability' ? 'selected' : '' }}>Available First</option>
              <option value="predicted_demand" {{ request('sort_by') == 'predicted_demand' ? 'selected' : '' }}>Popularity</option>
            </select>
          </div>
        </div>

        {{-- Advanced Filter Options --}}
        <fieldset class="border-t pt-4">
          <legend class="sr-only">Additional filter options</legend>
          <div class="flex flex-wrap gap-6">
            <label class="flex items-center cursor-pointer group">
              <input type="checkbox" name="high_demand_only" value="1"
                {{ request('high_demand_only') ? 'checked' : '' }}
                class="rounded border-gray-300 text-red-600 focus:ring-red-500 transition-colors">
              <span class="ml-2 text-sm text-gray-700 flex items-center group-hover:text-red-600 transition-colors">
                <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                    clip-rule="evenodd"></path>
                </svg>
                High Demand Only
              </span>
            </label>

            <label class="flex items-center cursor-pointer group">
              <input type="checkbox" name="available_only" value="1"
                {{ request('available_only') ? 'checked' : '' }}
                class="rounded border-gray-300 text-green-600 focus:ring-green-500 transition-colors">
              <span class="ml-2 text-sm text-gray-700 group-hover:text-green-600 transition-colors flex items-center">
                <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Available Only
              </span>
            </label>

            <button type="button" id="advanced-filters-toggle"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1"
              aria-expanded="false" aria-controls="advanced-filters">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
                </path>
              </svg>
              Advanced Filters
              <svg class="w-3 h-3 ml-1 transition-transform" id="advanced-icon" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
              </svg>
            </button>
          </div>
        </fieldset>

        {{-- Advanced Filters Panel (Hidden by default) --}}
        <div id="advanced-filters" class="hidden border-t pt-6 bg-gray-50 -mx-6 px-6 pb-6 mt-6">
          <h4 class="text-sm font-medium text-gray-900 mb-4">Advanced Search Options</h4>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Date Range --}}
            <div>
              <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Event Date From</label>
              <input type="date" name="date_from" id="date_from"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                value="{{ request('date_from') }}" min="{{ date('Y-m-d') }}">
            </div>
            <div>
              <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Event Date To</label>
              <input type="date" name="date_to" id="date_to"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                value="{{ request('date_to') }}" min="{{ date('Y-m-d') }}">
            </div>

            {{-- Venue/Location --}}
            <div>
              <label for="venue" class="block text-sm font-medium text-gray-700 mb-2">Venue/Location</label>
              <input type="text" name="venue" id="venue" placeholder="Enter venue name or location"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                value="{{ request('venue') }}" />
            </div>
          </div>
        </div>
      </form>
    </x-ui.card-content>
  </x-ui.card>

  {{-- Results Header with Stats and View Toggle --}}
  <div class="flex flex-col lg:flex-row lg:items-center justify-between mb-8 gap-4">
    <div class="flex items-center gap-4">
      <h2 class="text-2xl font-semibold text-gray-900">Sports Event Tickets</h2>
      <div class="flex items-center text-sm text-gray-600 gap-4">
        <span class="bg-gray-100 px-2 py-1 rounded">{{ $tickets->total() ?? 0 }} found</span>
        @if (isset($stats['avg_price']) && $stats['avg_price'] > 0)
          <span>Avg: ${{ number_format((float) $stats['avg_price'], 2) }}</span>
        @endif
        @if (isset($stats['available_count']) && $stats['available_count'] > 0)
          <span class="text-green-600">{{ $stats['available_count'] }} available</span>
        @endif
      </div>
    </div>
    
    <div class="flex items-center gap-2">
      {{-- View Toggle --}}
      <div class="bg-white border border-gray-300 rounded-lg p-1 flex">
        <button id="grid-view-toggle" 
          class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors focus:ring-2 focus:ring-gray-500 {{ ($viewMode ?? 'grid') == 'grid' ? 'bg-gray-100 text-gray-900' : '' }}"
          title="Grid View" aria-label="Switch to grid view" data-view="grid">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
        </button>
        <button id="list-view-toggle"
          class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors focus:ring-2 focus:ring-gray-500 {{ ($viewMode ?? 'grid') == 'list' ? 'bg-gray-100 text-gray-900' : '' }}"
          title="List View" aria-label="Switch to list view" data-view="list">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 6h16M4 10h16M4 14h16M4 18h16" />
          </svg>
        </button>
      </div>
      
      {{-- Per Page Selector --}}
      <select id="per-page-select" 
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20 per page</option>
        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
      </select>
    </div>
  </div>

  {{-- Loading Indicator --}}
  <div id="loading-indicator" class="hidden" role="status" aria-label="Loading tickets">
    <div class="flex flex-col items-center justify-center py-12">
      <div class="w-8 h-8 border-3 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
      <p class="mt-4 text-gray-600">Loading tickets...</p>
      <div class="sr-only">Please wait while we load the tickets</div>
    </div>
  </div>

  {{-- Main Content Area --}}
  <div id="main-content" class="min-h-screen">
    @include('tickets.scraping.partials.ticket-results', [
        'tickets' => $tickets, 
        'viewMode' => $viewMode ?? 'grid',
        'stats' => $stats ?? []
    ])
  </div>

  {{-- Quick View Modal --}}
  <div id="quick-view-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-96 overflow-y-auto">
        <div class="p-6">
          <div class="flex items-start justify-between">
            <h3 id="modal-title" class="text-lg font-medium text-gray-900">Ticket Details</h3>
            <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div id="modal-content" class="mt-4">
            <!-- Content will be loaded dynamically -->
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      // Enhanced ticket scraping interface functionality
      document.addEventListener('DOMContentLoaded', function() {
        initializeTicketInterface();
      });
      
      function initializeTicketInterface() {
        setupSearchAutocomplete();
        setupFilters();
        setupViewToggle();
        setupExportFunctionality();
        setupQuickView();
        setupKeyboardShortcuts();
        setupSearchPreferences();
      }
      
      function setupSearchAutocomplete() {
        const searchInput = document.getElementById('keywords');
        const suggestionsDiv = document.getElementById('search-suggestions');
        let debounceTimer;
        
        searchInput.addEventListener('input', function() {
          clearTimeout(debounceTimer);
          const query = this.value.trim();
          
          if (query.length >= 2) {
            debounceTimer = setTimeout(() => {
              fetchSuggestions(query);
            }, 300);
            document.getElementById('clear-search').classList.remove('hidden');
          } else {
            suggestionsDiv.classList.add('hidden');
            document.getElementById('clear-search').classList.add('hidden');
          }
        });
        
        document.getElementById('clear-search').addEventListener('click', function() {
          searchInput.value = '';
          searchInput.focus();
          suggestionsDiv.classList.add('hidden');
          this.classList.add('hidden');
        });
        
        document.querySelectorAll('.popular-search').forEach(button => {
          button.addEventListener('click', function() {
            searchInput.value = this.dataset.search;
            document.getElementById('filters-form').dispatchEvent(new Event('submit'));
          });
        });
      }
      
      async function fetchSuggestions(query) {
        try {
          const response = await fetch(`/tickets/scraping/search-suggestions?term=${encodeURIComponent(query)}`);
          const data = await response.json();
          
          if (data.success && data.suggestions.length > 0) {
            displaySuggestions(data.suggestions);
          }
        } catch (error) {
          console.error('Error fetching suggestions:', error);
        }
      }
      
      function displaySuggestions(suggestions) {
        const suggestionsList = document.getElementById('suggestions-list');
        const suggestionsDiv = document.getElementById('search-suggestions');
        
        suggestionsList.innerHTML = suggestions.map(suggestion => `
          <div class="px-3 py-2 hover:bg-gray-100 cursor-pointer suggestion-item flex items-center"
               data-value="${suggestion.value}">
            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <span class="flex-1">${suggestion.value}</span>
            <span class="text-xs text-gray-500 capitalize">${suggestion.type}</span>
          </div>
        `).join('');
        
        suggestionsList.querySelectorAll('.suggestion-item').forEach(item => {
          item.addEventListener('click', function() {
            document.getElementById('keywords').value = this.dataset.value;
            suggestionsDiv.classList.add('hidden');
            document.getElementById('filters-form').dispatchEvent(new Event('submit'));
          });
        });
        
        suggestionsDiv.classList.remove('hidden');
      }
      
      function setupFilters() {
        const form = document.getElementById('filters-form');
        
        form.addEventListener('change', debounce(function() {
          submitFilters();
        }, 500));
        
        document.querySelectorAll('[data-clear-filters]').forEach(button => {
          button.addEventListener('click', function() {
            form.reset();
            window.history.pushState({}, '', window.location.pathname);
            submitFilters();
          });
        });
        
        document.getElementById('advanced-filters-toggle').addEventListener('click', function() {
          const panel = document.getElementById('advanced-filters');
          const icon = document.getElementById('advanced-icon');
          const isExpanded = this.getAttribute('aria-expanded') === 'true';
          
          panel.classList.toggle('hidden');
          icon.style.transform = isExpanded ? 'rotate(0deg)' : 'rotate(180deg)';
          this.setAttribute('aria-expanded', !isExpanded);
        });
      }
      
      async function submitFilters() {
        const form = document.getElementById('filters-form');
        const formData = new FormData(form);
        const params = new URLSearchParams(formData);
        
        window.history.pushState({}, '', `${window.location.pathname}?${params.toString()}`);
        
        document.getElementById('loading-indicator').classList.remove('hidden');
        document.getElementById('main-content').style.opacity = '0.5';
        
        try {
          // This would make an AJAX request to update results
          // For now, just reload the page
          window.location.href = `${window.location.pathname}?${params.toString()}`;
        } catch (error) {
          console.error('Filter error:', error);
          showError('Failed to load tickets. Please try again.');
        } finally {
          document.getElementById('loading-indicator').classList.add('hidden');
          document.getElementById('main-content').style.opacity = '1';
        }
      }
      
      function setupViewToggle() {
        document.getElementById('grid-view-toggle').addEventListener('click', function() {
          switchView('grid');
        });
        
        document.getElementById('list-view-toggle').addEventListener('click', function() {
          switchView('list');
        });
      }
      
      function switchView(viewType) {
        document.querySelectorAll('[data-view]').forEach(btn => {
          btn.classList.toggle('bg-gray-100', btn.dataset.view === viewType);
          btn.classList.toggle('text-gray-900', btn.dataset.view === viewType);
        });
        
        localStorage.setItem('ticket_view_preference', viewType);
        updateViewDisplay(viewType);
      }
      
      function setupExportFunctionality() {
        const exportButton = document.getElementById('export-dropdown-button');
        const exportDropdown = document.getElementById('export-dropdown');
        
        exportButton.addEventListener('click', function() {
          exportDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
          if (!exportButton.contains(e.target) && !exportDropdown.contains(e.target)) {
            exportDropdown.classList.add('hidden');
          }
        });
        
        document.querySelectorAll('.export-option').forEach(option => {
          option.addEventListener('click', function(e) {
            e.preventDefault();
            const format = this.dataset.format;
            exportTickets(format);
            exportDropdown.classList.add('hidden');
          });
        });
      }
      
      async function exportTickets(format) {
        const form = document.getElementById('filters-form');
        const formData = new FormData(form);
        formData.append('format', format);
        
        try {
          const response = await fetch('/tickets/scraping/export', {
            method: 'POST',
            body: formData,
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });
          
          if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `sports-tickets-${new Date().toISOString().slice(0,10)}.${format}`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
          } else {
            showError('Export failed. Please try again.');
          }
        } catch (error) {
          console.error('Export error:', error);
          showError('Export failed. Please try again.');
        }
      }
      
      function setupQuickView() {
        const modal = document.getElementById('quick-view-modal');
        const closeModal = document.getElementById('close-modal');
        
        closeModal.addEventListener('click', function() {
          modal.classList.add('hidden');
        });
        
        modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            modal.classList.add('hidden');
          }
        });
        
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            modal.classList.add('hidden');
          }
        });
      }
      
      function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
          if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('keywords').focus();
          }
          
          if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            submitFilters();
          }
        });
      }
      
      function setupSearchPreferences() {
        const savedView = localStorage.getItem('ticket_view_preference');
        if (savedView) {
          switchView(savedView);
        }
      }
      
      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }
      
      function showError(message) {
        const existingError = document.getElementById('error-message');
        if (existingError) {
          existingError.querySelector('div:last-child').textContent = message;
        } else {
          const errorDiv = document.createElement('div');
          errorDiv.id = 'dynamic-error';
          errorDiv.className = 'mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800';
          errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
          document.querySelector('.mb-6').after(errorDiv);
          
          setTimeout(() => errorDiv.remove(), 5000);
        }
      }
      
      function removeFilter(filterKey) {
        const input = document.querySelector(`[name="${filterKey}"]`);
        if (input) {
          if (input.type === 'checkbox') {
            input.checked = false;
          } else {
            input.value = '';
          }
          submitFilters();
        }
      }
      
      window.removeFilter = removeFilter;
      window.showError = showError;
    </script>
  @endpush

  @push('styles')
    <style>
      .hd-button {
        @apply px-4 py-2 rounded-lg font-medium transition-all duration-200 focus:ring-2 focus:ring-offset-2;
      }
      
      .hd-button--primary {
        @apply bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500;
      }
      
      .hd-button--secondary {
        @apply bg-gray-600 text-white hover:bg-gray-700 focus:ring-gray-500;
      }
      
      .hd-button--outline {
        @apply border border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-gray-500;
      }
      
      .hd-button--ghost {
        @apply text-gray-700 hover:bg-gray-100 focus:ring-gray-500;
      }
      
      .suggestion-item:hover {
        @apply bg-blue-50;
      }
      
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      
      .animate-spin {
        animation: spin 1s linear infinite;
      }
      
      #main-content {
        transition: opacity 0.3s ease-in-out;
      }
      
      .focus-visible:focus {
        @apply ring-2 ring-blue-500 ring-offset-2 outline-none;
      }
    </style>
  @endpush
  
</x-unified-layout>
