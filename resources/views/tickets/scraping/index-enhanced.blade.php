<x-unified-layout title="Sports Tickets" subtitle="Browse and search for sports tickets across multiple platforms">
  <!-- Enhanced Sports Color Scheme CSS -->
  <link rel="stylesheet" href="{{ asset('css/sports-tickets-colors.css') }}?v={{ time() }}">
  <x-slot name="headerActions">
    <div class="flex flex-col sm:flex-row gap-3">
      <button id="refresh-tickets" type="button"
        class="hd-button hd-button--outline hd-button--md inline-flex items-center gap-2" aria-label="Refresh tickets">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
          </path>
        </svg>
        <span class="hd-button__text">Refresh</span>
      </button>
      <button type="button" data-clear-filters
        class="hd-button hd-button--ghost hd-button--md inline-flex items-center gap-2" aria-label="Clear all filters">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
          </path>
        </svg>
        <span class="hd-button__text">Clear Filters</span>
      </button>
      <button id="create-alert" type="button"
        class="hd-button hd-button--primary hd-button--md inline-flex items-center gap-2" aria-label="Create new alert">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5"></path>
        </svg>
        <span class="hd-button__text">Create Alert</span>
      </button>
    </div>
  </x-slot>

  <!-- Error Message -->
  @if (session('error') || isset($error))
    <x-ui.alert variant="error" class="mb-6" role="alert">
      <x-slot name="title">Error</x-slot>
      {{ session('error') ?? $error }}
    </x-ui.alert>
  @endif

  <!-- Success Message -->
  @if (session('success'))
    <x-ui.alert variant="success" class="mb-6" role="alert">
      <x-slot name="title">Success</x-slot>
      {{ session('success') }}
    </x-ui.alert>
  @endif

  <!-- Active Filters Summary -->
  @if (isset($activeFilters) && array_filter($activeFilters))
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg" role="region" aria-label="Active filters">
      <div class="flex items-start justify-between">
        <div>
          <h4 class="font-medium text-blue-900 mb-2">Active Filters:</h4>
          <div class="flex flex-wrap gap-2">
            @foreach ($activeFilters as $key => $value)
              @if ($value && $value !== '' && $value !== false)
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  role="button" tabindex="0" onkeydown="if(event.key==='Enter') removeFilter('{{ $key }}')">
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

  <!-- Advanced Search & Filters Panel -->
  <x-ui.card class="mb-6">
    <x-ui.card-header title="Search & Filters" class="border-b">
      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
      </svg>
    </x-ui.card-header>
    <x-ui.card-content>
      <form id="filters-form" class="space-y-6" role="search" aria-label="Ticket search and filtering">
        <!-- Search Input with Enhanced Features -->
        <div class="relative">
          <label for="keywords" class="sr-only">Search tickets</label>
          <input type="text" name="keywords" id="keywords" placeholder="Search by event, team, venue, or keyword..."
            value="{{ request('keywords') }}"
            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
            autocomplete="off" aria-describedby="keywords-help" />
          <div class="absolute left-3 top-1/2 transform -translate-y-1/2" aria-hidden="true">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <div id="keywords-help" class="sr-only">Enter keywords to search for sports event tickets</div>
        </div>

        <!-- Filter Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Platform Filter -->
          <div>
            <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
            <select name="platform" id="platform"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">All Platforms</option>
              <option value="stubhub" {{ request('platform') == 'stubhub' ? 'selected' : '' }}>StubHub</option>
              <option value="ticketmaster" {{ request('platform') == 'ticketmaster' ? 'selected' : '' }}>Ticketmaster
              </option>
              <option value="viagogo" {{ request('platform') == 'viagogo' ? 'selected' : '' }}>Viagogo</option>
            </select>
          </div>

          <!-- Min Price -->
          <div>
            <label for="min_price" class="block text-sm font-medium text-gray-700 mb-2">Min Price ($)</label>
            <input type="number" name="min_price" id="min_price" placeholder="0" min="0" step="0.01"
              value="{{ request('min_price') }}"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              aria-describedby="min-price-help" />
            <div id="min-price-help" class="sr-only">Enter minimum price for tickets</div>
          </div>

          <!-- Max Price -->
          <div>
            <label for="max_price" class="block text-sm font-medium text-gray-700 mb-2">Max Price ($)</label>
            <input type="number" name="max_price" id="max_price" placeholder="1000" min="0" step="0.01"
              value="{{ request('max_price') }}"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              aria-describedby="max-price-help" />
            <div id="max-price-help" class="sr-only">Enter maximum price for tickets</div>
          </div>

          <!-- Sort Options -->
          <div>
            <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select name="sort_by" id="sort_by"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="scraped_at" {{ request('sort_by', 'scraped_at') == 'scraped_at' ? 'selected' : '' }}>
                Latest</option>
              <option value="event_date" {{ request('sort_by') == 'event_date' ? 'selected' : '' }}>Event Date
              </option>
              <option value="min_price" {{ request('sort_by') == 'min_price' ? 'selected' : '' }}>Price (Low to High)
              </option>
              <option value="max_price" {{ request('sort_by') == 'max_price' ? 'selected' : '' }}>Price (High to Low)
              </option>
              <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Event Name</option>
            </select>
          </div>
        </div>

        <!-- Filter Options -->
        <fieldset class="space-y-4">
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
                <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Available Only
              </span>
            </label>

            <button type="button" id="advanced-filters-toggle"
              class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded px-2 py-1"
              aria-expanded="false" aria-controls="advanced-filters">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                aria-hidden="true">
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

        <!-- Advanced Filters (Hidden by default) -->
        <div id="advanced-filters" class="hidden border-t pt-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Date Range -->
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

            <!-- Venue/Location -->
            <div>
              <label for="venue" class="block text-sm font-medium text-gray-700 mb-2">Venue/Location</label>
              <input type="text" name="venue" id="venue" placeholder="Enter venue name"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                value="{{ request('venue') }}" />
            </div>
          </div>
        </div>
      </form>
    </x-ui.card-content>
  </x-ui.card>

  <!-- Results Summary and Actions -->
  <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div class="flex items-center gap-4">
      <h2 class="text-2xl font-semibold text-gray-900">Sports Event Tickets</h2>
      <span class="text-gray-600">({{ $tickets->total() ?? 0 }} found)</span>
      @if (isset($stats['avg_price']) && $stats['avg_price'] > 0)
        <span class="text-sm text-gray-600">Avg: ${{ number_format((float) $stats['avg_price'], 2) }}</span>
      @endif
    </div>
    <div class="flex items-center gap-2">
      <button id="grid-view-toggle"
        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:ring-2 focus:ring-gray-500"
        title="Grid View" aria-label="Switch to grid view">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
        </svg>
      </button>
      <button id="list-view-toggle"
        class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors focus:ring-2 focus:ring-gray-500"
        title="List View" aria-label="Switch to list view">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Loading Indicator -->
  <div id="loading-indicator" class="flex flex-col items-center justify-center py-12" role="status"
    aria-label="Loading tickets">
    <div class="w-8 h-8 border-3 border-gray-200 border-t-blue-600 rounded-full animate-spin"></div>
    <p class="mt-4 text-gray-600">Loading tickets...</p>
    <div class="sr-only">Please wait while we load the tickets</div>
  </div>

  <!-- Error State -->
  <div id="error-state" class="text-center py-16 hidden" role="alert">
    <div class="w-16 h-16 mx-auto mb-6 text-red-400" aria-hidden="true">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z">
        </path>
      </svg>
    </div>
    <h3 class="text-xl font-semibold text-gray-900 mb-3">Unable to load tickets</h3>
    <p class="text-gray-600 max-w-md mx-auto mb-6">
      We're experiencing technical difficulties. Please try again later or contact support if the problem persists.
    </p>
    <x-ui.button id="retry-loading" variant="primary">
      Try Again
    </x-ui.button>
  </div>

  <!-- Tickets Container -->
  <div id="tickets-container">
    <!-- Grid View (default) -->
    <div id="grid-tickets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      @forelse ($tickets ?? collect() as $ticket)
        <article
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
          <div class="p-6 space-y-4">
            <!-- Header -->
            <div class="flex justify-between items-start">
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors"
                  title="{{ $ticket->title ?? 'Sports Event Ticket' }}">
                  {{ $ticket->title ?? 'Sports Event Ticket' }}
                </h3>
                @if ($ticket->event_date ?? null)
                  <p class="text-sm text-gray-600 mt-1 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                      </path>
                    </svg>
                    <time datetime="{{ $ticket->event_date }}">
                      {{ \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y g:i A') }}
                    </time>
                  </p>
                @endif
              </div>

              @if ($ticket->is_high_demand ?? false)
                <span
                  class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 animate-pulse"
                  aria-label="High demand ticket">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd"
                      d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                      clip-rule="evenodd"></path>
                  </svg>
                  Hot
                </span>
              @endif
            </div>

            <!-- Venue -->
            @if ($ticket->venue ?? null)
              <div class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                  </path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="text-sm truncate" title="{{ $ticket->venue }}">{{ $ticket->venue }}</span>
              </div>
            @endif

            <!-- Price Information -->
            <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-4 border border-blue-100">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">From</p>
                  <p class="text-xl font-bold text-green-600">
                    ${{ number_format((float) ($ticket->min_price ?? 0), 2) }}
                  </p>
                </div>
                @if (($ticket->max_price ?? null) && $ticket->max_price > ($ticket->min_price ?? 0))
                  <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">Up to</p>
                    <p class="text-lg font-semibold text-gray-700">
                      ${{ number_format((float) $ticket->max_price, 2) }}
                    </p>
                  </div>
                @endif
              </div>

              @if ($ticket->quantity_available ?? null)
                <div class="mt-3 flex items-center justify-between">
                  <p class="text-xs text-gray-600 flex items-center">
                    <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20"
                      aria-hidden="true">
                      <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                    </svg>
                    {{ $ticket->quantity_available }} available
                  </p>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                    <span class="ml-1 text-xs text-green-600 font-medium">Live</span>
                  </div>
                </div>
              @endif
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
              <div class="flex items-center">
                <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                  <span class="text-xs font-bold text-blue-600">
                    {{ strtoupper(substr($ticket->platform ?? 'U', 0, 1)) }}
                  </span>
                </div>
                <span class="text-sm text-gray-600 capitalize font-medium">{{ $ticket->platform ?? 'Unknown' }}</span>
                @if ($ticket->scraped_at && \Carbon\Carbon::parse($ticket->scraped_at)->isValid())
                  <span class="ml-2 text-xs text-gray-400" aria-label="Last updated">•</span>
                  <span class="ml-2 text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($ticket->scraped_at)->diffForHumans() }}
                  </span>
                @endif
              </div>
              <div class="flex space-x-2">
                <button onclick="createAlert({{ $ticket->id ?? 0 }})"
                  class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                  title="Create Alert" aria-label="Create alert for this ticket">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5"></path>
                  </svg>
                </button>
                <x-ui.button size="sm" variant="outline" data-ticket-id="{{ $ticket->id ?? 0 }}"
                  onclick="viewTicketDetails({{ $ticket->id ?? 0 }})" class="hover:border-blue-300">
                  Details
                </x-ui.button>
                @if ($ticket->url ?? null)
                  <x-ui.button size="sm" href="{{ $ticket->url }}" target="_blank" rel="noopener"
                    class="bg-gradient-to-r from-blue-600 to-green-600 hover:from-blue-700 hover:to-green-700 text-white border-none">
                    Buy Now
                  </x-ui.button>
                @endif
              </div>
            </div>
          </div>
        </article>
      @empty
        <!-- No tickets found -->
        <div class="col-span-full text-center py-16" role="status">
          <div class="w-32 h-32 mx-auto mb-6 text-gray-300" aria-hidden="true">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
              </path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">No sports event tickets found</h3>
          <p class="text-gray-600 max-w-md mx-auto mb-6">
            We couldn't find any tickets matching your search criteria. Try adjusting your filters or check
            back later for new tickets.
          </p>
          <div class="flex justify-center space-x-3">
            <x-ui.button variant="outline" data-clear-filters>
              Clear All Filters
            </x-ui.button>
            <x-ui.button id="refresh-search" variant="primary">
              Refresh Search
            </x-ui.button>
          </div>
        </div>
      @endforelse
    </div>

    <!-- List View (hidden by default) -->
    <div id="list-tickets" class="space-y-4 mb-8 hidden">
      @forelse ($tickets ?? collect() as $ticket)
        <article
          class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 focus-within:ring-2 focus-within:ring-blue-500 focus-within:ring-offset-2">
          <div class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between space-y-4 lg:space-y-0 lg:space-x-6">
              <!-- Event Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="text-lg font-semibold text-gray-900 truncate mr-4"
                    title="{{ $ticket->title ?? 'Sports Event Ticket' }}">
                    {{ $ticket->title ?? 'Sports Event Ticket' }}
                  </h3>
                  @if ($ticket->is_high_demand ?? false)
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                      aria-label="High demand ticket">
                      🔥 Hot
                    </span>
                  @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                  @if ($ticket->event_date ?? null)
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                      </svg>
                      <time datetime="{{ $ticket->event_date }}">
                        {{ \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y g:i A') }}
                      </time>
                    </span>
                  @endif
                  @if ($ticket->venue ?? null)
                    <span class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      {{ $ticket->venue }}
                    </span>
                  @endif
                  <span class="flex items-center capitalize font-medium">
                    <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center mr-1">
                      <span class="text-xs font-bold text-blue-600">
                        {{ $ticket->platform ? strtoupper(substr($ticket->platform, 0, 1)) : '?' }}
                      </span>
                    </div>
                    {{ $ticket->platform ?? 'Unknown' }}
                  </span>
                </div>
              </div>

              <!-- Price & Actions -->
              <div class="flex items-center space-x-6">
                <div class="text-right">
                  <p class="text-2xl font-bold text-green-600">
                    ${{ number_format((float) ($ticket->min_price ?? 0), 2) }}
                  </p>
                  @if (($ticket->max_price ?? null) && $ticket->max_price > ($ticket->min_price ?? 0))
                    <p class="text-sm text-gray-500">
                      up to ${{ number_format((float) $ticket->max_price, 2) }}
                    </p>
                  @endif
                  @if ($ticket->quantity_available ?? null)
                    <p class="text-xs text-green-600 font-medium">
                      {{ $ticket->quantity_available }} available
                    </p>
                  @endif
                </div>

                <div class="flex space-x-2">
                  <button onclick="createAlert({{ $ticket->id ?? 0 }})"
                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50 focus:ring-2 focus:ring-blue-500 focus:ring-offset-1"
                    title="Create Alert" aria-label="Create alert for this ticket">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z">
                      </path>
                    </svg>
                  </button>
                  <x-ui.button size="sm" variant="outline" onclick="viewTicketDetails({{ $ticket->id ?? 0 }})">
                    Details
                  </x-ui.button>
                  @if ($ticket->url ?? null)
                    <x-ui.button size="sm" href="{{ $ticket->url }}" target="_blank" rel="noopener">
                      Buy Now
                    </x-ui.button>
                  @endif
                </div>
              </div>
            </div>
          </div>
        </article>
      @empty
        <div class="text-center py-16" role="status">
          <div class="w-32 h-32 mx-auto mb-6 text-gray-300" aria-hidden="true">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
              </path>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">No sports event tickets found</h3>
          <p class="text-gray-600 max-w-md mx-auto mb-6">
            We couldn't find any tickets matching your search criteria. Try adjusting your filters or check back later.
          </p>
          <x-ui.button variant="outline" data-clear-filters>Clear All Filters</x-ui.button>
        </div>
      @endforelse
    </div>
  </div>

  <!-- Enhanced Pagination -->
  @if (($tickets ?? collect())->hasPages())
    <nav
      class="flex flex-col sm:flex-row justify-between items-center bg-white border border-gray-200 rounded-lg px-6 py-4 shadow-sm mb-8"
      role="navigation" aria-label="Pagination">
      <div class="flex items-center space-x-4 text-gray-600 mb-4 sm:mb-0">
        <span class="text-sm">
          Showing <span class="font-semibold">{{ $tickets->firstItem() }}</span>
          to <span class="font-semibold">{{ $tickets->lastItem() }}</span>
          of <span class="font-semibold">{{ $tickets->total() }}</span> results
        </span>

        @if (request()->hasAny(['keywords', 'platform', 'min_price', 'max_price', 'high_demand_only', 'available_only']))
          <span class="text-blue-600 bg-blue-50 px-2 py-1 rounded-full text-xs font-medium">
            Filtered
          </span>
        @endif
      </div>

      <div class="flex items-center space-x-1">
        {{ $tickets->appends(request()->query())->links('pagination::tailwind') }}
      </div>
    </nav>
  @endif

  <!-- Enhanced Statistics Panel -->
  @if (isset($stats) && !empty($stats))
    <div class="mt-8">
      <x-ui.card>
        <x-ui.card-header title="Statistics" class="border-b border-gray-200">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            </path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content class="pt-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-blue-600 text-sm font-medium uppercase tracking-wide">Total Sports Tickets</p>
                  <p class="text-2xl font-bold text-blue-900 mt-1">{{ $stats['total_tickets'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-200 rounded-full p-3" aria-hidden="true">
                  <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z"
                      clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-green-600 text-sm font-medium uppercase tracking-wide">Available Now</p>
                  <p class="text-2xl font-bold text-green-900 mt-1">
                    {{ $stats['available_tickets'] ?? ($tickets ?? collect())->where('is_available', true)->count() }}
                  </p>
                </div>
                <div class="bg-green-200 rounded-full p-3" aria-hidden="true">
                  <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-4 border border-orange-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-orange-600 text-sm font-medium uppercase tracking-wide">Avg Price</p>
                  <p class="text-2xl font-bold text-orange-900 mt-1">
                    @if (isset($stats['avg_price']) && $stats['avg_price'] > 0)
                      ${{ number_format((float) $stats['avg_price'], 2) }}
                    @else
                      N/A
                    @endif
                  </p>
                </div>
                <div class="bg-orange-200 rounded-full p-3" aria-hidden="true">
                  <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z">
                    </path>
                    <path fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                      clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-purple-600 text-sm font-medium uppercase tracking-wide">Platforms</p>
                  <p class="text-2xl font-bold text-purple-900 mt-1">{{ $stats['platforms'] ?? 3 }}</p>
                </div>
                <div class="bg-purple-200 rounded-full p-3" aria-hidden="true">
                  <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z">
                    </path>
                  </svg>
                </div>
              </div>
            </div>
          </div>

          @if (isset($stats['price_range']) && !empty($stats['price_range']))
            <div class="mt-6 pt-6 border-t border-gray-200">
              <h4 class="font-medium text-gray-900 mb-3">Price Range</h4>
              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">Lowest Price</span>
                  <span class="font-semibold text-green-600">
                    ${{ number_format((float) ($stats['price_range']['min'] ?? 0), 2) }}
                  </span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                  <span class="text-gray-600">Highest Price</span>
                  <span class="font-semibold text-blue-600">
                    ${{ number_format((float) ($stats['price_range']['max'] ?? 0), 2) }}
                  </span>
                </div>
              </div>
            </div>
          @endif
        </x-ui.card-content>
      </x-ui.card>
    </div>
  @endif

  <!-- Enhanced JavaScript -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize components
      initializeFilters();
      initializeViewToggle();
      initializeAdvancedFilters();
      initializeTicketActions();
      initializeAccessibility();

      // Auto-submit form on filter changes
      const form = document.getElementById('filters-form');
      const inputs = form.querySelectorAll('input, select');

      inputs.forEach(input => {
        const eventType = input.type === 'checkbox' ? 'change' : 'input';
        let timeout;

        input.addEventListener(eventType, function() {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            showLoading();
            submitFilters();
          }, input.type === 'checkbox' ? 0 : 800);
        });
      });

      // Initialize clear filters buttons
      document.querySelectorAll('[data-clear-filters]').forEach(button => {
        button.addEventListener('click', clearAllFilters);
      });

      // Initialize refresh buttons  
      document.querySelectorAll('#refresh-tickets, #refresh-search, #retry-loading').forEach(button => {
        button.addEventListener('click', function() {
          showLoading();
          window.location.reload();
        });
      });

      // Initialize create alert button
      document.getElementById('create-alert')?.addEventListener('click', function() {
        showCreateAlertModal();
      });
    });

    function initializeFilters() {
      // Price validation
      const minPriceInput = document.querySelector('input[name="min_price"]');
      const maxPriceInput = document.querySelector('input[name="max_price"]');

      if (minPriceInput && maxPriceInput) {
        [minPriceInput, maxPriceInput].forEach(input => {
          input.addEventListener('input', function() {
            validatePriceRange();
          });
        });
      }

      // Keywords input enhancements
      const keywordsInput = document.querySelector('input[name="keywords"]');
      if (keywordsInput) {
        keywordsInput.addEventListener('keydown', function(e) {
          if (e.key === 'Enter') {
            e.preventDefault();
            showLoading();
            submitFilters();
          }
        });
      }
    }

    function initializeViewToggle() {
      const gridToggle = document.getElementById('grid-view-toggle');
      const listToggle = document.getElementById('list-view-toggle');
      const gridTickets = document.getElementById('grid-tickets');
      const listTickets = document.getElementById('list-tickets');

      if (!gridToggle || !listToggle || !gridTickets || !listTickets) return;

      let currentView = localStorage.getItem('hd-tickets-view') || 'grid';

      function setView(view) {
        if (view === 'list') {
          gridTickets.classList.add('hidden');
          listTickets.classList.remove('hidden');
          gridToggle.classList.remove('text-blue-600');
          gridToggle.classList.add('text-gray-600');
          listToggle.classList.remove('text-gray-600');
          listToggle.classList.add('text-blue-600');
        } else {
          gridTickets.classList.remove('hidden');
          listTickets.classList.add('hidden');
          gridToggle.classList.remove('text-gray-600');
          gridToggle.classList.add('text-blue-600');
          listToggle.classList.remove('text-blue-600');
          listToggle.classList.add('text-gray-600');
        }
        localStorage.setItem('hd-tickets-view', view);
      }

      setView(currentView);

      gridToggle.addEventListener('click', () => setView('grid'));
      listToggle.addEventListener('click', () => setView('list'));
    }

    function initializeAdvancedFilters() {
      const toggle = document.getElementById('advanced-filters-toggle');
      const filters = document.getElementById('advanced-filters');
      const icon = document.getElementById('advanced-icon');

      toggle?.addEventListener('click', function() {
        const isHidden = filters.classList.contains('hidden');

        if (isHidden) {
          filters.classList.remove('hidden');
          icon.style.transform = 'rotate(180deg)';
          toggle.setAttribute('aria-expanded', 'true');
        } else {
          filters.classList.add('hidden');
          icon.style.transform = 'rotate(0deg)';
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    }

    function initializeTicketActions() {
      // Initialize all ticket action buttons
      document.querySelectorAll('[onclick*="viewTicketDetails"]').forEach(button => {
        const ticketId = button.getAttribute('data-ticket-id');
        if (ticketId) {
          button.onclick = (e) => {
            e.preventDefault();
            viewTicketDetails(ticketId);
          };
        }
      });
    }

    function initializeAccessibility() {
      // Add keyboard navigation for filter chips
      document.querySelectorAll('[role="button"][tabindex="0"]').forEach(chip => {
        chip.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
          }
        });
      });
    }

    function submitFilters() {
      const form = document.getElementById('filters-form');
      const formData = new FormData(form);
      const params = new URLSearchParams();

      for (let [key, value] of formData.entries()) {
        if (value.trim() !== '') {
          params.append(key, value);
        }
      }

      // Add current sort direction if not already present
      const currentSort = new URLSearchParams(window.location.search).get('sort_dir');
      if (currentSort && !params.has('sort_dir')) {
        params.append('sort_dir', currentSort);
      }

      const newUrl = window.location.pathname + '?' + params.toString();

      // Announce to screen readers
      announceToScreenReader('Updating sports event tickets based on your search criteria');

      window.history.pushState({}, '', newUrl);
      window.location.reload();
    }

    function clearAllFilters() {
      showLoading();
      announceToScreenReader('Clearing all filters and showing all sports event tickets');
      window.location.href = window.location.pathname;
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

    function showLoading() {
      const container = document.getElementById('tickets-container');
      const loading = document.getElementById('loading-indicator');
      const errorState = document.getElementById('error-state');

      if (container) container.classList.add('hidden');
      if (loading) loading.classList.remove('hidden');
      if (errorState) errorState.classList.add('hidden');
    }

    function hideLoading() {
      const container = document.getElementById('tickets-container');
      const loading = document.getElementById('loading-indicator');

      if (container) container.classList.remove('hidden');
      if (loading) loading.classList.add('hidden');
    }

    function showError() {
      const container = document.getElementById('tickets-container');
      const loading = document.getElementById('loading-indicator');
      const errorState = document.getElementById('error-state');

      if (container) container.classList.add('hidden');
      if (loading) loading.classList.add('hidden');
      if (errorState) errorState.classList.remove('hidden');
    }

    function validatePriceRange() {
      const minPrice = parseFloat(document.querySelector('input[name="min_price"]').value) || 0;
      const maxPrice = parseFloat(document.querySelector('input[name="max_price"]').value) || 0;
      const minInput = document.querySelector('input[name="min_price"]');
      const maxInput = document.querySelector('input[name="max_price"]');

      // Remove existing error states
      [minInput, maxInput].forEach(input => {
        input.classList.remove('border-red-500', 'text-red-600');
        input.removeAttribute('aria-invalid');
      });

      if (minPrice > 0 && maxPrice > 0 && minPrice > maxPrice) {
        maxInput.classList.add('border-red-500', 'text-red-600');
        maxInput.setAttribute('aria-invalid', 'true');
        return false;
      }

      return true;
    }

    // Enhanced Modal for comprehensive ticket details
    function viewTicketDetails(ticketId) {
      // Create modal backdrop
      const modal = document.createElement('div');
      modal.className =
      'hd-modal-backdrop fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-labelledby', 'ticket-details-title');

      // Create modal content with loading state
      modal.innerHTML = `
                <div class="hd-modal-container bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-screen overflow-hidden transform transition-all duration-300 scale-95 opacity-0">
                    <!-- Modal Header -->
                    <div class="hd-modal-header flex items-center justify-between p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-green-50">
                        <div class="flex items-center space-x-3">
                            <div class="hd-sports-icon p-2 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 id="ticket-details-title" class="text-2xl font-bold text-gray-900">Ticket Details</h2>
                                <p class="text-sm text-gray-600">Comprehensive sports event information</p>
                            </div>
                        </div>
                        <button id="close-ticket-modal" class="hd-close-btn p-2 hover:bg-gray-100 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors" aria-label="Close modal">
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="hd-modal-body overflow-y-auto" style="max-height: calc(100vh - 200px);">
                        <!-- Loading State -->
                        <div id="ticket-loading" class="flex flex-col items-center justify-center py-16 px-8">
                            <div class="hd-loading-spinner w-12 h-12 border-4 border-gray-200 border-t-blue-600 rounded-full animate-spin mb-4"></div>
                            <p class="text-lg font-medium text-gray-700 mb-2">Loading ticket details...</p>
                            <p class="text-sm text-gray-500">Please wait while we fetch comprehensive information</p>
                        </div>
                        
                        <!-- Content Container -->
                        <div id="ticket-content" class="hidden"></div>
                        
                        <!-- Error State -->
                        <div id="ticket-error" class="hidden text-center py-16 px-8">
                            <div class="w-16 h-16 mx-auto mb-6 text-red-400">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-3">Unable to load ticket details</h3>
                            <p class="text-gray-600 max-w-md mx-auto mb-6">We're experiencing technical difficulties loading this ticket's information.</p>
                            <button id="retry-ticket-details" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                                Try Again
                            </button>
                        </div>
                    </div>
                </div>
            `;

      document.body.appendChild(modal);

      // Animate modal in
      setTimeout(() => {
        const container = modal.querySelector('.hd-modal-container');
        container.classList.remove('scale-95', 'opacity-0');
        container.classList.add('scale-100', 'opacity-100');
      }, 50);

      // Focus management
      const closeButton = modal.querySelector('#close-ticket-modal');

      // Load ticket details via AJAX
      loadTicketDetailsAjax(ticketId, modal);

      // Event handlers
      const closeModal = () => {
        const container = modal.querySelector('.hd-modal-container');
        container.classList.remove('scale-100', 'opacity-100');
        container.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
          if (document.body.contains(modal)) {
            document.body.removeChild(modal);
          }
          announceToScreenReader('Ticket details modal closed');
        }, 300);
      };

      closeButton.addEventListener('click', closeModal);

      // Close on backdrop click
      modal.addEventListener('click', function(e) {
        if (e.target === modal) {
          closeModal();
        }
      });

      // Close on escape key
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closeModal();
        }
      });

      // Retry button handler
      modal.querySelector('#retry-ticket-details')?.addEventListener('click', () => {
        loadTicketDetailsAjax(ticketId, modal);
      });
    }

    // AJAX function to load detailed ticket information
    function loadTicketDetailsAjax(ticketId, modal) {
      const loadingEl = modal.querySelector('#ticket-loading');
      const contentEl = modal.querySelector('#ticket-content');
      const errorEl = modal.querySelector('#ticket-error');

      // Show loading state
      loadingEl.classList.remove('hidden');
      contentEl.classList.add('hidden');
      errorEl.classList.add('hidden');

      // Make AJAX request to the web endpoint
      fetch(`/ajax/ticket-details/${ticketId}`, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
          },
          credentials: 'same-origin'
        })
        .then(response => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then(data => {
          if (data.success && data.data) {
            displayTicketDetails(data.data, contentEl, modal);
            loadingEl.classList.add('hidden');
            contentEl.classList.remove('hidden');
            announceToScreenReader('Ticket details loaded successfully');
          } else {
            throw new Error(data.message || 'Failed to load ticket details');
          }
        })
        .catch(error => {
          console.error('Error loading ticket details:', error);
          loadingEl.classList.add('hidden');
          errorEl.classList.remove('hidden');
          announceToScreenReader('Failed to load ticket details');
        });
    }

    // Function to display comprehensive ticket details
    function displayTicketDetails(ticket, container, modal) {
      const formatPrice = (price, currency = 'USD') => {
        if (!price || price === 0) return 'N/A';
        const symbol = currency === 'USD' ? '$' : currency === 'EUR' ? '€' : currency === 'GBP' ? '£' : currency + ' ';
        return symbol + parseFloat(price).toFixed(2);
      };

      const formatDate = (dateStr) => {
        if (!dateStr) return 'TBD';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
          weekday: 'long',
          year: 'numeric',
          month: 'long',
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      };

      const getStatusBadge = (status, isAvailable) => {
        if (!isAvailable)
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Sold Out</span>';
        if (status === 'active')
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Available</span>';
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">' + (status ||
          'Unknown').charAt(0).toUpperCase() + (status || 'unknown').slice(1) + '</span>';
      };

      const getPlatformBadge = (platform) => {
        const colors = {
          'stubhub': 'bg-blue-100 text-blue-800',
          'ticketmaster': 'bg-red-100 text-red-800',
          'viagogo': 'bg-green-100 text-green-800',
          'seatgeek': 'bg-purple-100 text-purple-800'
        };
        const colorClass = colors[platform?.toLowerCase()] || 'bg-gray-100 text-gray-800';
        return `<span class="px-3 py-1 rounded-full text-xs font-semibold ${colorClass}">${platform || 'Unknown'}</span>`;
      };

      const getRecommendationBadge = (score) => {
        if (score >= 80)
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">🌟 Highly Recommended</span>';
        if (score >= 60)
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">👍 Recommended</span>';
        return '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">⚠️ Consider Carefully</span>';
      };

      container.innerHTML = `
                <div class="p-6 space-y-8">
                    <!-- Event Header -->
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between space-y-4 lg:space-y-0">
                        <div class="flex-1">
                            <h3 class="text-3xl font-bold text-gray-900 mb-2">${ticket.title || 'Sports Event'}</h3>
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                ${getStatusBadge(ticket.status, ticket.is_available)}
                                ${getPlatformBadge(ticket.platform_display)}
                                ${ticket.is_high_demand ? '<span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 animate-pulse">🔥 High Demand</span>' : ''}
                                ${getRecommendationBadge(ticket.recommendation_score || 50)}
                            </div>
                            <div class="text-gray-600 space-y-1">
                                ${ticket.venue ? `<p class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg><strong>${ticket.venue}</strong></p>` : ''}
                                ${ticket.location ? `<p class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 4m0 13V4m-6 3l6-3"></path></svg>${ticket.location}</p>` : ''}
                                ${ticket.event_date_human ? `<p class="flex items-center"><svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg><strong>${formatDate(ticket.event_date)}</strong> ${ticket.event_date_relative ? `<span class="text-sm ml-2">(${ticket.event_date_relative})</span>` : ''}</p>` : ''}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="bg-gradient-to-br from-green-50 to-blue-50 rounded-lg p-4 border border-green-200">
                                <p class="text-sm text-gray-600 mb-1">Price Range</p>
                                <p class="text-2xl font-bold text-green-600">${ticket.formatted_price_range || 'N/A'}</p>
                                ${ticket.quantity_available ? `<p class="text-sm text-green-600 font-medium mt-1">${ticket.quantity_available} available</p>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8" aria-label="Ticket details tabs">
                            <button class="hd-tab-btn border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600" data-tab="overview" aria-selected="true">
                                Overview
                            </button>
                            <button class="hd-tab-btn border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="pricing">
                                Price History
                            </button>
                            <button class="hd-tab-btn border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="details">
                                Event Details
                            </button>
                            <button class="hd-tab-btn border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300" data-tab="platform">
                                Platform Info
                            </button>
                        </nav>
                    </div>
                    
                    <!-- Tab Content -->
                    <div class="hd-tab-content space-y-6">
                        <!-- Overview Tab -->
                        <div id="tab-overview" class="hd-tab-panel">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                                <!-- Key Information -->
                                <div class="space-y-6">
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Key Information</h4>
                                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Event Type:</span>
                                                <span class="font-medium">${ticket.event_type || 'Sports'}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Sport:</span>
                                                <span class="font-medium">${ticket.sport || 'N/A'}</span>
                                            </div>
                                            ${ticket.team ? `<div class="flex justify-between"><span class="text-gray-600">Teams:</span><span class="font-medium">${ticket.team}</span></div>` : ''}
                                            ${ticket.days_until_event !== null ? `<div class="flex justify-between"><span class="text-gray-600">Days Until Event:</span><span class="font-medium ${ticket.days_until_event < 7 ? 'text-red-600' : 'text-green-600'}">${ticket.days_until_event} days</span></div>` : ''}
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Data Freshness:</span>
                                                <span class="font-medium ${ticket.is_recent ? 'text-green-600' : 'text-yellow-600'}">
                                                    ${ticket.scraped_at_human || 'Unknown'} ${ticket.is_recent ? '✓' : '⚠️'}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Statistics -->
                                    ${ticket.statistics ? `
                                          <div>
                                              <h4 class="text-lg font-semibold text-gray-900 mb-3">Price Statistics</h4>
                                              <div class="bg-blue-50 rounded-lg p-4 space-y-3">
                                                  ${ticket.statistics.avg_price ? `<div class="flex justify-between"><span class="text-gray-600">Average Price:</span><span class="font-medium">${formatPrice(ticket.statistics.avg_price, ticket.currency)}</span></div>` : ''}
                                                  ${ticket.statistics.lowest_price ? `<div class="flex justify-between"><span class="text-gray-600">Lowest Price:</span><span class="font-medium text-green-600">${formatPrice(ticket.statistics.lowest_price, ticket.currency)}</span></div>` : ''}
                                                  ${ticket.statistics.highest_price ? `<div class="flex justify-between"><span class="text-gray-600">Highest Price:</span><span class="font-medium text-red-600">${formatPrice(ticket.statistics.highest_price, ticket.currency)}</span></div>` : ''}
                                                  <div class="flex justify-between">
                                                      <span class="text-gray-600">Price Trend:</span>
                                                      <span class="font-medium ${
                                                          ticket.statistics.price_trend === 'increasing' ? 'text-red-600' : 
                                                          ticket.statistics.price_trend === 'decreasing' ? 'text-green-600' : 
                                                          'text-gray-600'
                                                      }">
                                                          ${ticket.statistics.price_trend === 'increasing' ? '📈 Rising' : 
                                                            ticket.statistics.price_trend === 'decreasing' ? '📉 Falling' : 
                                                            '➡️ Stable'}
                                                      </span>
                                                  </div>
                                                  <div class="flex justify-between">
                                                      <span class="text-gray-600">Volatility:</span>
                                                      <span class="font-medium ${
                                                          ticket.statistics.price_volatility === 'high' ? 'text-red-600' : 
                                                          ticket.statistics.price_volatility === 'medium' ? 'text-yellow-600' : 
                                                          'text-green-600'
                                                      }">
                                                          ${ticket.statistics.price_volatility || 'Low'}
                                                      </span>
                                                  </div>
                                              </div>
                                          </div>
                                      ` : ''}
                                </div>
                                
                                <!-- Recommendations & Similar -->
                                <div class="space-y-6">
                                    <!-- Recommendation Score -->
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Recommendation</h4>
                                        <div class="bg-green-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <span class="text-gray-700">Overall Score</span>
                                                <span class="text-2xl font-bold text-green-600">${ticket.recommendation_score || 50}/100</span>
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-3 mb-3">
                                                <div class="bg-gradient-to-r from-green-400 to-blue-500 h-3 rounded-full" style="width: ${ticket.recommendation_score || 50}%"></div>
                                            </div>
                                            <p class="text-sm text-gray-600">
                                                ${ticket.recommendation_score >= 80 ? 'Excellent choice! This ticket offers great value and reliability.' :
                                                  ticket.recommendation_score >= 60 ? 'Good option with decent value and availability.' :
                                                  'Consider comparing with other options before purchasing.'}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <!-- Platform Reliability -->
                                    ${ticket.platform_reliability ? `
                                          <div>
                                              <h4 class="text-lg font-semibold text-gray-900 mb-3">Platform Trust</h4>
                                              <div class="bg-blue-50 rounded-lg p-4">
                                                  <div class="flex items-center justify-between mb-2">
                                                      <span class="text-gray-700">${ticket.platform_display} Reliability</span>
                                                      <span class="text-lg font-bold text-blue-600">${ticket.platform_reliability.score}/100</span>
                                                  </div>
                                                  <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                                      <div class="bg-blue-500 h-2 rounded-full" style="width: ${ticket.platform_reliability.score}%"></div>
                                                  </div>
                                                  <p class="text-sm text-gray-600 capitalize">Rating: ${ticket.platform_reliability.rating}</p>
                                              </div>
                                          </div>
                                      ` : ''}
                                    
                                    <!-- Similar Tickets -->
                                    ${ticket.similar_tickets_count > 0 ? `
                                          <div>
                                              <h4 class="text-lg font-semibold text-gray-900 mb-3">Similar Options</h4>
                                              <div class="bg-yellow-50 rounded-lg p-4">
                                                  <p class="text-gray-700 mb-2">
                                                      <span class="text-xl font-bold text-yellow-600">${ticket.similar_tickets_count}</span> similar tickets available
                                                  </p>
                                                  <p class="text-sm text-gray-600">Consider comparing prices and seating options with similar events.</p>
                                              </div>
                                          </div>
                                      ` : ''}
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing Tab -->
                        <div id="tab-pricing" class="hd-tab-panel hidden">
                            ${ticket.price_history && ticket.price_history.length > 0 ? `
                                  <h4 class="text-lg font-semibold text-gray-900 mb-4">Price History</h4>
                                  <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                                      <div class="overflow-x-auto">
                                          <table class="min-w-full divide-y divide-gray-200">
                                              <thead class="bg-gray-50">
                                                  <tr>
                                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                                  </tr>
                                              </thead>
                                              <tbody class="bg-white divide-y divide-gray-200">
                                                  ${ticket.price_history.map((entry, index) => {
                                                      const prevPrice = index > 0 ? ticket.price_history[index - 1].price : entry.price;
                                                      const change = entry.price - prevPrice;
                                                      const changePercent = prevPrice !== 0 ? (change / prevPrice * 100).toFixed(1) : 0;
                                                      
                                                      return `
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                                ${entry.recorded_human} ${entry.is_mock ? '<span class="text-xs text-gray-400">(demo)</span>' : ''}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                ${formatPrice(entry.price, ticket.currency)}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                                                ${entry.source}
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                                ${change === 0 ? '<span class="text-gray-500">—</span>' : 
                                                                  change > 0 ? `<span class="text-red-600">+${formatPrice(change, ticket.currency)} (+${changePercent}%)</span>` :
                                                                  `<span class="text-green-600">${formatPrice(change, ticket.currency)} (${changePercent}%)</span>`}
                                                            </td>
                                                        </tr>
                                                    `;
                                                  }).join('')}
                                              </tbody>
                                          </table>
                                      </div>
                                  </div>
                              ` : `
                                  <div class="text-center py-12">
                                      <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                      </svg>
                                      <h3 class="text-lg font-medium text-gray-900 mb-2">No Price History Available</h3>
                                      <p class="text-gray-600">Price history data is not available for this ticket yet.</p>
                                  </div>
                              `}
                        </div>
                        
                        <!-- Details Tab -->
                        <div id="tab-details" class="hd-tab-panel hidden">
                            <div class="space-y-6">
                                <h4 class="text-lg font-semibold text-gray-900">Complete Event Information</h4>
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                        <h5 class="font-medium text-gray-900">Event Details</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-600">Ticket ID:</span><span class="font-mono">#${ticket.id}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">External ID:</span><span class="font-mono text-xs">${ticket.external_id || 'N/A'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">UUID:</span><span class="font-mono text-xs">${ticket.uuid || 'N/A'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Category:</span><span>${ticket.category ? ticket.category.name : 'Uncategorized'}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Search Keywords:</span><span>${ticket.search_keyword || 'N/A'}</span></div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                                        <h5 class="font-medium text-gray-900">System Information</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between"><span class="text-gray-600">Created:</span><span>${new Date(ticket.created_at).toLocaleDateString()}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Last Updated:</span><span>${new Date(ticket.updated_at).toLocaleDateString()}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Scraped:</span><span>${ticket.scraped_at_human}</span></div>
                                            <div class="flex justify-between"><span class="text-gray-600">Popularity Score:</span><span>${ticket.popularity_score || 'N/A'}/100</span></div>
                                        </div>
                                    </div>
                                </div>
                                
                                ${ticket.metadata && Object.keys(ticket.metadata).length > 0 ? `
                                      <div>
                                          <h5 class="font-medium text-gray-900 mb-3">Additional Metadata</h5>
                                          <div class="bg-blue-50 rounded-lg p-4">
                                              <pre class="text-sm text-gray-700 whitespace-pre-wrap">${JSON.stringify(ticket.metadata, null, 2)}</pre>
                                          </div>
                                      </div>
                                  ` : ''}
                            </div>
                        </div>
                        
                        <!-- Platform Tab -->
                        <div id="tab-platform" class="hd-tab-panel hidden">
                            <div class="space-y-6">
                                <h4 class="text-lg font-semibold text-gray-900">Platform Information</h4>
                                <div class="bg-white border border-gray-200 rounded-lg p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                                <span class="text-lg font-bold text-blue-600">${ticket.platform_display ? ticket.platform_display.charAt(0) : 'U'}</span>
                                            </div>
                                            <div>
                                                <h5 class="font-semibold text-gray-900">${ticket.platform_display || 'Unknown Platform'}</h5>
                                                <p class="text-sm text-gray-600">Ticket marketplace</p>
                                            </div>
                                        </div>
                                        ${ticket.platform_reliability ? `
                                              <div class="text-right">
                                                  <p class="text-2xl font-bold text-blue-600">${ticket.platform_reliability.score}/100</p>
                                                  <p class="text-sm text-gray-600 capitalize">${ticket.platform_reliability.rating}</p>
                                              </div>
                                          ` : ''}
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                        <div><span class="text-gray-600">Platform Code:</span> <span class="font-mono">${ticket.platform || 'unknown'}</span></div>
                                        <div><span class="text-gray-600">External Reference:</span> <span class="font-mono text-xs">${ticket.external_id || 'N/A'}</span></div>
                                        <div><span class="text-gray-600">Last Sync:</span> <span>${ticket.scraped_at_human || 'Never'}</span></div>
                                        <div><span class="text-gray-600">Data Quality:</span> <span class="font-medium ${ticket.meta?.data_completeness >= 80 ? 'text-green-600' : ticket.meta?.data_completeness >= 60 ? 'text-yellow-600' : 'text-red-600'}">${ticket.meta?.data_completeness || 0}%</span></div>
                                    </div>
                                </div>
                                
                                ${ticket.ticket_url ? `
                                      <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                          <h5 class="font-medium text-gray-900 mb-2">External Link</h5>
                                          <p class="text-sm text-gray-600 mb-3">View this ticket on the original platform</p>
                                          <a href="${ticket.ticket_url}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                              </svg>
                                              View on ${ticket.platform_display}
                                          </a>
                                      </div>
                                  ` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="flex flex-col sm:flex-row items-center justify-between p-6 border-t border-gray-200 bg-gray-50 space-y-3 sm:space-y-0">
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span>Last updated: ${new Date().toLocaleString()}</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                                Live Data
                            </span>
                        </div>
                        <div class="flex space-x-3">
                            <button onclick="createAlert(${ticket.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 transition-colors">
                                🔔 Create Alert
                            </button>
                            ${ticket.ticket_url ? `
                                  <a href="${ticket.ticket_url}" target="_blank" rel="noopener noreferrer" class="px-6 py-2 bg-gradient-to-r from-green-600 to-blue-600 text-white rounded-lg hover:from-green-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-all transform hover:scale-105">
                                      🎫 Buy Now
                                  </a>
                              ` : ''}
                        </div>
                    </div>
                </div>
            `;

      // Initialize tab functionality
      initializeModalTabs(container);
    }

    // Initialize tab functionality for the modal
    function initializeModalTabs(container) {
      const tabButtons = container.querySelectorAll('.hd-tab-btn');
      const tabPanels = container.querySelectorAll('.hd-tab-panel');

      tabButtons.forEach(button => {
        button.addEventListener('click', () => {
          const targetTab = button.dataset.tab;

          // Update button states
          tabButtons.forEach(btn => {
            btn.classList.remove('border-blue-500', 'text-blue-600');
            btn.classList.add('border-transparent', 'text-gray-500');
            btn.setAttribute('aria-selected', 'false');
          });

          button.classList.remove('border-transparent', 'text-gray-500');
          button.classList.add('border-blue-500', 'text-blue-600');
          button.setAttribute('aria-selected', 'true');

          // Update panel visibility
          tabPanels.forEach(panel => {
            panel.classList.add('hidden');
          });

          const targetPanel = container.querySelector(`#tab-${targetTab}`);
          if (targetPanel) {
            targetPanel.classList.remove('hidden');
          }
        });
      });
    }

    // Modal for creating alert
    function createAlert(ticketId = null) {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-labelledby', 'alert-modal-title');

      modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full mx-4">
                    <h2 id="alert-modal-title" class="text-xl font-bold mb-4">Create Sports Ticket Alert</h2>
                    <form id="create-alert-form">
                        <input type="hidden" name="ticket_id" value="${ticketId ?? ''}">
                        <div class="space-y-4">
                            <div>
                                <label for="alert-keywords" class="block text-sm font-medium text-gray-700 mb-1">Keywords</label>
                                <input type="text" id="alert-keywords" name="keywords" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div>
                                <label for="alert-max-price" class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
                                <input type="number" id="alert-max-price" name="max_price" class="w-full border border-gray-300 rounded px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 focus:ring-2 focus:ring-gray-500" id="close-alert-modal">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 focus:ring-2 focus:ring-green-500">Create Alert</button>
                        </div>
                    </form>
                </div>
            `;

      document.body.appendChild(modal);

      // Focus management
      const keywordsInput = modal.querySelector('#alert-keywords');
      keywordsInput.focus();

      modal.querySelector('#close-alert-modal').onclick = () => {
        document.body.removeChild(modal);
        announceToScreenReader('Create alert modal closed');
      };

      modal.querySelector('#create-alert-form').onsubmit = function(e) {
        e.preventDefault();
        // Here you would send the alert to backend via AJAX
        announceToScreenReader(
          'Sports ticket alert created successfully! You will be notified when matching tickets are found.');
        document.body.removeChild(modal);
      };

      // Close on escape
      modal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          modal.querySelector('#close-alert-modal').click();
        }
      });
    }

    function showCreateAlertModal() {
      createAlert();
    }

    function announceToScreenReader(message) {
      const announcement = document.createElement('div');
      announcement.setAttribute('aria-live', 'polite');
      announcement.setAttribute('aria-atomic', 'true');
      announcement.className = 'sr-only';
      announcement.textContent = message;
      document.body.appendChild(announcement);
      setTimeout(() => document.body.removeChild(announcement), 1000);
    }

    // Enhanced error handling
    window.addEventListener('error', function(e) {
      console.error('JavaScript error:', e.error);
      hideLoading();
      showError();
    });

    // Page visibility API for smart refresh
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') {
        const lastRefresh = sessionStorage.getItem('hd-last-refresh');
        const now = Date.now();
        if (!lastRefresh || now - parseInt(lastRefresh) > 300000) { // 5 minutes
          console.log('Page became visible, checking for updates...');
          sessionStorage.setItem('hd-last-refresh', now.toString());
        }
      }
    });
  </script>

  <!-- Enhanced Styles -->
  <style>
    /* Enhanced ticket card animations */
    .hd-ticket-card {
      transition: all 0.2s ease-in-out;
    }

    .hd-ticket-card:hover {
      transform: translateY(-2px);
    }

    /* Loading spinner */
    .animate-spin {
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    /* Advanced filter animations */
    #advanced-filters {
      transition: all 0.3s ease-in-out;
      overflow: hidden;
    }

    #advanced-icon {
      transition: transform 0.3s ease-in-out;
    }

    /* Focus states for accessibility */
    .focus-visible {
      outline: 2px solid #3b82f6;
      outline-offset: 2px;
    }

    /* Screen reader only */
    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    /* Mobile enhancements */
    @media (max-width: 640px) {
      .grid-cols-1 {
        grid-template-columns: 1fr;
      }

      .hd-ticket-card {
        margin-bottom: 1rem;
      }
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
      .border-gray-200 {
        border-color: #000;
      }

      .text-gray-600 {
        color: #000;
      }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {

      .transition-all,
      .transition-colors,
      .transition-transform,
      .animate-pulse,
      .animate-spin {
        animation: none;
        transition: none;
      }
    }
  </style>
</x-unified-layout>
