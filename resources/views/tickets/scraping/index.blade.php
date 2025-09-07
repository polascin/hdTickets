{{-- Enhanced Sports Tickets Scraping Interface --}}
<x-unified-layout title="Sports Event Tickets" subtitle="Browse and search for sports event tickets across multiple platforms">
  <x-slot name="headerActions">
    <div class="flex flex-col sm:flex-row gap-3">
      <x-ui.button id="refresh-tickets" icon="refresh" variant="outline">
        Refresh
      </x-ui.button>
      <x-ui.button variant="ghost" data-clear-filters icon="trash">
        Clear Filters
      </x-ui.button>
      <x-ui.button id="create-alert" icon="bell" variant="primary">
        Create Alert
      </x-ui.button>
    </div>
  </x-slot>

  <!-- Error Message -->
  @if (session('error') || isset($error))
    <x-ui.alert variant="error" class="mb-6">
      {{ session('error') ?? $error }}
    </x-ui.alert>
  @endif

  <!-- Success Message -->
  @if (session('success'))
    <x-ui.alert variant="success" class="mb-6">
      {{ session('success') }}
    </x-ui.alert>
  @endif

  <!-- Active Filters Summary -->
  @if (isset($activeFilters) && array_filter($activeFilters))
    <div class="mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg">
      <div class="flex items-start justify-between">
        <div>
          <h4 class="font-medium text-blue-900 mb-2">Active Filters:</h4>
          <div class="flex flex-wrap gap-2">
            @foreach ($activeFilters as $key => $value)
              @if ($value && $value !== '' && $value !== false)
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ ucfirst(str_replace(['_', 'only'], [' ', ''], $key)) }}:
                  @if (is_bool($value))
                    {{ $value ? 'Yes' : 'No' }}
                  @else
                    {{ $value }}
                  @endif
                  <button type="button" class="ml-1 text-blue-400 hover:text-blue-600"
                    onclick="removeFilter({{ json_encode($key) }})">
                    ×
                  </button>
                </span>
              @endif
            @endforeach
          </div>
        </div>
        <button data-clear-filters class="text-blue-600 hover:text-blue-800 text-sm font-medium">
          Clear All
        </button>
      </div>
    </div>
  @endif

  <!-- Advanced Search & Filters Panel -->
  <div class="hd-card hd-card--default hd-card--md mb-6">
    <div class="hd-card__header hd-card__header--bordered border-b">
      <h3 class="hd-card__title flex items-center">
        <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        Search & Filters
      </h3>
    </div>
    <div class="hd-card__content">
      <form id="filters-form" class="space-y-6">
        <!-- Search Input with Enhanced Features -->
        <div class="relative">
          <label for="keywords" class="hd-label">Search Tickets</label>
          <div class="relative">
            <input type="text" name="keywords" id="keywords" class="hd-input pl-10 pr-10"
              placeholder="Search by event, team, venue, or keyword..." value="{{ request('keywords') }}"
              autocomplete="off" aria-describedby="keywords-help" data-search-input>
            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <button type="button"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"
              id="clear-search" aria-label="Clear search">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <small id="keywords-help" class="text-xs text-gray-500 mt-1 block">Search across event names, teams, venues,
            and descriptions</small>

          <!-- Search Suggestions Dropdown -->
          <div id="search-suggestions"
            class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-md shadow-lg hidden max-h-48 overflow-y-auto">
            <div class="p-2 text-xs text-gray-500 border-b">Popular searches:</div>
            <div id="suggestions-list" class="py-1"></div>
          </div>
        </div>

        <!-- Filter Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Platform Filter -->
          <div>
            <label for="platform" class="hd-label">Platform</label>
            <select name="platform" id="platform" class="hd-input" aria-describedby="platform-help">
              <option value="">All Platforms</option>
              <option value="stubhub" {{ request('platform') == 'stubhub' ? 'selected' : '' }}>StubHub</option>
              <option value="ticketmaster" {{ request('platform') == 'ticketmaster' ? 'selected' : '' }}>Ticketmaster
              </option>
              <option value="viagogo" {{ request('platform') == 'viagogo' ? 'selected' : '' }}>Viagogo</option>
              <option value="funzone" {{ request('platform') == 'funzone' ? 'selected' : '' }}>FunZone</option>
            </select>
            <small id="platform-help" class="text-xs text-gray-500 mt-1 block">Filter tickets by platform</small>
            </select>
          </div>

          <!-- Min Price -->
          <x-ui.input name="min_price" type="number" label="Min Price ($)" placeholder="0" min="0"
            step="0.01" value="{{ request('min_price') }}" />

          <!-- Max Price -->
          <x-ui.input name="max_price" type="number" label="Max Price ($)" placeholder="1000" min="0"
            step="0.01" value="{{ request('max_price') }}" />

          <!-- Enhanced Sort Options -->
          <div>
            <label for="sort_by" class="hd-label">Sort By</label>
            <select name="sort_by" id="sort_by" class="hd-select" aria-describedby="sort-help">
              <option value="">Default Order</option>
              <option value="scraped_at" {{ request('sort_by', 'scraped_at') == 'scraped_at' ? 'selected' : '' }}>
                Recently Updated
              </option>
              <option value="event_date" {{ request('sort_by') == 'event_date' ? 'selected' : '' }}>Event Date:
                Earliest First</option>
              <option value="event_date_desc" {{ request('sort_by') == 'event_date_desc' ? 'selected' : '' }}>Event
                Date: Latest First</option>
              <option value="min_price" {{ request('sort_by') == 'min_price' ? 'selected' : '' }}>Price: Low to High
              </option>
              <option value="max_price" {{ request('sort_by') == 'max_price' ? 'selected' : '' }}>Price: High to Low
              </option>
              <option value="title" {{ request('sort_by') == 'title' ? 'selected' : '' }}>Event Name: A-Z</option>
              <option value="title_desc" {{ request('sort_by') == 'title_desc' ? 'selected' : '' }}>Event Name: Z-A
              </option>
              <option value="platform" {{ request('sort_by') == 'platform' ? 'selected' : '' }}>Platform: A-Z</option>
              <option value="availability" {{ request('sort_by') == 'availability' ? 'selected' : '' }}>Available
                First</option>
            </select>
            <small id="sort-help" class="text-xs text-gray-500 mt-1 block">Choose how to order your search
              results</small>
          </div>
        </div>

        <!-- Filter Options -->
        <div class="flex flex-wrap gap-6">
          <label class="flex items-center cursor-pointer group">
            <input type="checkbox" name="high_demand_only" value="1"
              {{ request('high_demand_only') ? 'checked' : '' }}
              class="rounded border-gray-300 text-red-600 focus:ring-red-500 transition-colors">
            <span class="ml-2 hd-text-small flex items-center group-hover:text-red-600 transition-colors">
              <svg class="w-4 h-4 mr-1 text-red-500" fill="currentColor" viewBox="0 0 20 20">
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
            <span class="ml-2 hd-text-small group-hover:text-green-600 transition-colors flex items-center">
              <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Available Only
            </span>
          </label>

          <button type="button" id="advanced-filters-toggle"
            class="hd-text-small text-blue-600 hover:text-blue-800 font-medium flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4">
              </path>
            </svg>
            Advanced Filters
            <svg class="w-3 h-3 ml-1 transition-transform" id="advanced-icon" fill="none" stroke="currentColor"
              viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
              </path>
            </svg>
          </button>
        </div>

        <!-- Advanced Filters (Hidden by default) -->
        <div id="advanced-filters" class="hidden border-t pt-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Date Range -->
            <div>
              <label class="hd-label">Event Date From</label>
              <input type="date" name="date_from" class="hd-input" value="{{ request('date_from') }}"
                min="{{ date('Y-m-d') }}">
            </div>
            <div>
              <label class="hd-label">Event Date To</label>
              <input type="date" name="date_to" class="hd-input" value="{{ request('date_to') }}"
                min="{{ date('Y-m-d') }}">
            </div>

            <!-- Venue/Location -->
            <x-ui.input name="venue" label="Venue/Location" placeholder="Enter venue name"
              value="{{ request('venue') }}" />
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Results Summary and Actions -->
  <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div class="flex items-center gap-4">
      <h2 class="hd-heading-2 font-semibold text-gray-900">Ticket Results</h2>
      <span class="hd-text-base text-gray-600">({{ $tickets->total() }} found)</span>
      @if (isset($stats['avg_price']) && $stats['avg_price'] > 0)
        <span class="hd-text-small text-gray-600">Avg:
          ${{ number_format((float) $stats['avg_price'], 2) }}</span>
      @endif
    </div>
    <div class="flex items-center gap-2">
      <button id="grid-view-toggle" class="hd-btn hd-btn--ghost" title="Grid View">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
      </button>
      <button id="list-view-toggle" class="hd-btn hd-btn--ghost" title="List View">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 6h16M4 10h16M4 14h16M4 18h16" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Enhanced Loading Indicator -->
  <div id="loading-indicator" class="flex flex-col items-center justify-center py-12 space-y-4"
    style="display: none;">
    <div class="relative">
      <div class="hd-loading-skeleton hd-loading-skeleton--spinner"></div>
      <div class="absolute inset-0 hd-loading-skeleton hd-loading-skeleton--spinner opacity-30 animate-ping"></div>
    </div>
    <div class="text-center">
      <p class="loading-text hd-text-base font-medium text-gray-700 mb-2">Loading tickets...</p>
      <p class="text-sm text-gray-500">Please wait while we fetch the latest data</p>
    </div>
    <div class="w-48 bg-gray-200 rounded-full h-1.5">
      <div class="bg-blue-600 h-1.5 rounded-full progress-bar" style="width: 0%; transition: width 0.5s ease-in-out;">
      </div>
    </div>
  </div>

  <!-- Tickets Container -->
  <!-- Tickets Display -->
  <div id="tickets-container">
    <!-- Grid View (default) -->
    <div id="grid-tickets" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
      @forelse ($tickets as $ticket)
        <x-ui.card
          class="hd-ticket-card hover:shadow-lg transition-all duration-200 border border-gray-200 hover:border-blue-300 group">
          <x-ui.card-content class="space-y-4 p-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
              <div class="flex-1 min-w-0">
                <h3 class="hd-text-lg font-semibold text-gray-900 truncate group-hover:text-blue-600 transition-colors"
                  title="{{ $ticket->title }}">
                  {{ $ticket->title }}
                </h3>
                <p class="hd-text-small text-gray-600 mt-1 flex items-center">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                  </svg>
                  @if ($ticket->event_date && \Carbon\Carbon::parse($ticket->event_date)->isValid())
                    {{ \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y') }}
                    <span class="text-gray-400 mx-1">•</span>
                    {{ \Carbon\Carbon::parse($ticket->event_date)->format('g:i A') }}
                  @else
                    <span class="text-gray-500">Date TBD</span>
                  @endif
                </p>
              </div>

              @if ($ticket->is_high_demand)
                <span
                  class="ml-2 flex-shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 animate-pulse">
                  <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                      clip-rule="evenodd"></path>
                  </svg>
                  Hot
                </span>
              @endif
            </div>

            <!-- Event Details -->
            @if ($ticket->venue)
              <div class="flex items-center text-gray-600">
                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                  </path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                <span class="hd-text-small truncate" title="{{ $ticket->venue }}">{{ $ticket->venue }}</span>
              </div>
            @endif

            <!-- Price Information -->
            <div class="bg-gradient-to-r from-emerald-50 to-blue-50 rounded-lg p-4 border border-emerald-100">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <p class="hd-text-xs text-gray-500 uppercase tracking-wide font-medium">From</p>
                  <p class="hd-text-xl font-bold text-green-600">
                    ${{ number_format((float) ($ticket->min_price ?? 0), 2) }}
                  </p>
                </div>
                @if ($ticket->max_price && $ticket->max_price > $ticket->min_price)
                  <div>
                    <p class="hd-text-xs text-gray-500 uppercase tracking-wide font-medium">Up to
                    </p>
                    <p class="hd-text-lg font-semibold text-gray-700">
                      ${{ number_format((float) $ticket->max_price, 2) }}
                    </p>
                  </div>
                @endif
              </div>

              @if ($ticket->quantity_available)
                <div class="mt-3 flex items-center justify-between">
                  <p class="hd-text-xs text-gray-600 flex items-center">
                    <svg class="w-3 h-3 mr-1 text-green-500" fill="currentColor" viewBox="0 0 20 20">
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
                  <span
                    class="text-xs font-bold text-blue-600">{{ strtoupper(substr($ticket->platform, 0, 1)) }}</span>
                </div>
                <span class="hd-text-small text-gray-600 capitalize font-medium">{{ $ticket->platform }}</span>
                @if ($ticket->scraped_at && \Carbon\Carbon::parse($ticket->scraped_at)->isValid())
                  <span class="ml-2 text-xs text-gray-400">•</span>
                  <span class="ml-2 text-xs text-gray-500">
                    {{ \Carbon\Carbon::parse($ticket->scraped_at)->diffForHumans() }}
                  </span>
                @endif
              </div>
              <div class="flex space-x-2">
                <button onclick="createAlert({{ $ticket->id }})"
                  class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50"
                  title="Create Alert" aria-label="Create alert for this ticket">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5"></path>
                  </svg>
                </button>
                <x-ui.button size="sm" variant="outline" data-ticket-id="{{ $ticket->id }}"
                  onclick="viewTicketDetails({{ $ticket->id }})" class="hover:border-blue-300">
                  Details
                </x-ui.button>
                <x-ui.button size="sm" href="{{ $ticket->url }}" target="_blank"
                  class="bg-gradient-to-r from-blue-600 to-green-600 hover:from-blue-700 hover:to-green-700 text-white border-none">
                  Buy Now
                </x-ui.button>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      @empty
        <!-- No tickets found -->
        <div class="col-span-full text-center py-16">
          <div class="w-32 h-32 mx-auto mb-6 text-gray-300">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
              </path>
            </svg>
          </div>
          <h3 class="hd-text-xl font-semibold text-gray-900 mb-3">No tickets found</h3>
          <p class="hd-text-base text-gray-600 max-w-md mx-auto mb-6">
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
    <div id="list-tickets" class="space-y-4 mb-8" style="display: none;">
      @forelse ($tickets as $ticket)
        <x-ui.card
          class="hd-ticket-card hover:shadow-md transition-all duration-200 border border-gray-200 hover:border-blue-300">
          <x-ui.card-content class="p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between space-y-4 lg:space-y-0 lg:space-x-6">
              <!-- Event Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between mb-2">
                  <h3 class="hd-text-lg font-semibold text-gray-900 truncate mr-4" title="{{ $ticket->title }}">
                    {{ $ticket->title ?: 'N/A' }}
                  </h3>
                  @if ($ticket->is_high_demand)
                    <span
                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      🔥 Hot
                    </span>
                  @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                      </path>
                    </svg>
                    {{ $ticket->event_date ? \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y g:i A') : 'N/A' }}
                  </span>
                  <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                      </path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ $ticket->venue ?: 'N/A' }}
                  </span>
                  <span class="flex items-center capitalize font-medium">
                    <div class="w-4 h-4 bg-blue-100 rounded-full flex items-center justify-center mr-1">
                      <span
                        class="text-xs font-bold text-blue-600">{{ $ticket->platform ? strtoupper(substr($ticket->platform, 0, 1)) : '?' }}</span>
                    </div>
                    {{ $ticket->platform ?: 'N/A' }}
                  </span>
                </div>
              </div>

              <!-- Price & Actions -->
              <div class="flex items-center space-x-6">
                <div class="text-right">
                  <p class="text-2xl font-bold text-green-600">
                    ${{ number_format((float) ($ticket->min_price ?? 0), 2) }}
                  </p>
                  @if ($ticket->max_price && $ticket->max_price > $ticket->min_price)
                    <p class="text-sm text-gray-500">
                      up to ${{ number_format((float) $ticket->max_price, 2) }}
                    </p>
                  @endif
                  @if ($ticket->quantity_available)
                    <p class="text-xs text-green-600 font-medium">
                      {{ $ticket->quantity_available }} available
                    </p>
                  @endif
                </div>

                <div class="flex space-x-2">
                  <button onclick="createAlert({{ $ticket->id }})"
                    class="p-2 text-gray-400 hover:text-blue-600 transition-colors rounded-full hover:bg-blue-50"
                    title="Create Alert" aria-label="Create alert for this ticket">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5">
                      </path>
                    </svg>
                  </button>
                  <x-ui.button size="sm" variant="outline" onclick="viewTicketDetails({{ $ticket->id }})">
                    Details
                  </x-ui.button>
                  <x-ui.button size="sm" href="{{ $ticket->url }}" target="_blank">
                    Buy Now
                  </x-ui.button>
                </div>
              </div>
            </div>
          </x-ui.card-content>
        </x-ui.card>
      @empty
        <div class="text-center py-16">
          <div class="w-32 h-32 mx-auto mb-6 text-gray-300">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z">
              </path>
            </svg>
          </div>
          <h3 class="hd-text-xl font-semibold text-gray-900 mb-3">No tickets found</h3>
          <p class="hd-text-base text-gray-600 max-w-md mx-auto mb-6">
            We couldn't find any tickets matching your search criteria. Try adjusting your filters or check
            back later.
          </p>
          <x-ui.button variant="outline" data-clear-filters>Clear All Filters</x-ui.button>
        </div>
      @endforelse
    </div>
  </div>

  <!-- Empty State -->
  <div id="empty-state" class="text-center py-12" style="display: {{ $tickets->count() > 0 ? 'none' : 'block' }};">
    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z">
      </path>
    </svg>
    <h3 class="hd-heading-3 mb-2">No tickets found</h3>
    <p class="hd-text-base text-gray-500 mb-6">Try adjusting your search criteria or check back later for new
      tickets.</p>
    <x-ui.button data-clear-filters>
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
        </path>
      </svg>
      Clear All Filters
    </x-ui.button>
  </div>

  <!-- Enhanced Pagination -->
  @if ($tickets->hasPages())
    <div
      class="flex flex-col sm:flex-row justify-between items-center bg-gradient-to-r from-white to-slate-50 border border-gray-200 rounded-lg px-6 py-4 shadow-sm mb-8">
      <div class="flex items-center space-x-4 text-gray-600 mb-4 sm:mb-0">
        <span class="hd-text-small">
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
        {{ $tickets->appends(request()->query())->links('pagination::tailwind', ['class' => 'pagination-enhanced']) }}
      </div>
    </div>
  @endif

  <!-- Enhanced Statistics Panel -->
  @if (isset($stats) && !empty($stats))
    <div class="mt-8">
      <x-ui.card>
        <x-ui.card-header title="Statistics" class="border-b border-gray-200">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
            </path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content class="pt-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div
              class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-blue-600 text-sm font-medium uppercase tracking-wide">Total Tickets
                  </p>
                  <p class="text-2xl font-bold text-blue-900 mt-1">
                    {{ $stats['total_tickets'] ?? 0 }}</p>
                </div>
                <div class="bg-blue-200 rounded-full p-3">
                  <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1z"
                      clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div
              class="bg-gradient-to-br from-emerald-50 to-green-100 rounded-lg p-4 border border-green-200 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-emerald-600 text-sm font-medium uppercase tracking-wide">Available</p>
                  <p class="text-2xl font-bold text-emerald-900 mt-1">
                    {{ $stats['available_tickets'] ?? $tickets->where('is_available', true)->count() }}
                  </p>
                </div>
                <div class="bg-emerald-200 rounded-full p-3">
                  <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                      clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
            </div>

            <div
              class="bg-gradient-to-br from-amber-50 to-orange-100 rounded-lg p-4 border border-amber-200 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-amber-600 text-sm font-medium uppercase tracking-wide">Avg Price
                  </p>
                  <p class="text-2xl font-bold text-amber-900 mt-1">
                    @if (isset($stats['avg_price']) && $stats['avg_price'] > 0)
                      ${{ number_format((float) $stats['avg_price'], 2) }}
                    @else
                      N/A
                    @endif
                  </p>
                </div>
                <div class="bg-amber-200 rounded-full p-3">
                  <svg class="w-6 h-6 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
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

            <div
              class="bg-gradient-to-br from-indigo-50 to-purple-100 rounded-lg p-4 border border-indigo-200 shadow-sm hover:shadow-md transition-shadow">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-indigo-600 text-sm font-medium uppercase tracking-wide">Platforms
                  </p>
                  <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $stats['platforms'] ?? 3 }}
                  </p>
                </div>
                <div class="bg-indigo-200 rounded-full p-3">
                  <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
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
              <div class="bg-gradient-to-r from-slate-50 to-gray-50 rounded-lg p-4 border border-slate-200">
                <div class="flex items-center justify-between text-sm">
                  <span class="text-gray-600">Lowest Price</span>
                  <span class="font-semibold text-green-600">
                    ${{ number_format((float) $stats['price_range']['min'], 2) }}
                  </span>
                </div>
                <div class="flex items-center justify-between text-sm mt-2">
                  <span class="text-gray-600">Highest Price</span>
                  <span class="font-semibold text-blue-600">
                    ${{ number_format((float) $stats['price_range']['max'], 2) }}
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
      initializeAutoRefresh();
      initializeAdvancedFilters();
      initializeTicketActions();

      // Auto-submit form on filter changes
      const form = document.getElementById('filters-form');
      const inputs = form.querySelectorAll('input, select');

      inputs.forEach(input => {
        const eventType = input.type === 'checkbox' ? 'change' : 'input';
        let timeout;

        input.addEventListener(eventType, function() {
          clearTimeout(timeout);
          timeout = setTimeout(() => {
            const actionType = input.name === 'keywords' ? 'search' : 'filter';
            showLoadingWithContext(actionType);
            submitFilters();
          }, input.type === 'checkbox' ? 0 : 500);
        });
      });

      // Initialize clear filters buttons
      document.querySelectorAll('[data-clear-filters]').forEach(button => {
        button.addEventListener('click', clearAllFilters);
      });

      // Initialize refresh buttons  
      document.querySelectorAll('#refresh-tickets, #refresh-search').forEach(button => {
        button.addEventListener('click', function() {
          showLoadingWithContext('refresh');
          window.location.reload();
        });
      });

      // Initialize create alert button
      document.getElementById('create-alert')?.addEventListener('click', function() {
        showCreateAlertModal();
      });
    });

    function initializeFilters() {
      // Enhanced search input with suggestions and clear button
      const keywordsInput = document.querySelector('input[name="keywords"]');
      const clearSearchBtn = document.getElementById('clear-search');
      const suggestionsDiv = document.getElementById('search-suggestions');

      if (keywordsInput) {
        // Show/hide clear button based on input value
        function toggleClearButton() {
          if (keywordsInput.value.length > 0) {
            clearSearchBtn?.classList.remove('hidden');
          } else {
            clearSearchBtn?.classList.add('hidden');
          }
        }

        // Initialize clear button visibility
        toggleClearButton();

        // Handle input events
        keywordsInput.addEventListener('input', function() {
          toggleClearButton();
          if (this.value.length > 2) {
            showSearchSuggestions();
          } else {
            hideSearchSuggestions();
          }
        });

        keywordsInput.addEventListener('focus', function() {
          if (this.value.length > 2) {
            showSearchSuggestions();
          }
        });

        keywordsInput.addEventListener('blur', function() {
          setTimeout(() => hideSearchSuggestions(), 150);
        });

        // Clear search functionality
        clearSearchBtn?.addEventListener('click', function() {
          keywordsInput.value = '';
          keywordsInput.focus();
          toggleClearButton();
          hideSearchSuggestions();
          showLoadingWithContext('clear');
          submitFilters();
        });
      }

      // Price validation
      const minPriceInput = document.querySelector('input[name="min_price"]');
      const maxPriceInput = document.querySelector('input[name="max_price"]');

      if (minPriceInput && maxPriceInput) {
        [minPriceInput, maxPriceInput].forEach(input => {
          input.addEventListener('input', function() {
            validatePriceRange();
            updatePriceDisplay();
          });
        });
      }
    }

    function initializeViewToggle() {
      const gridView = document.getElementById('grid-view');
      const listView = document.getElementById('list-view');
      const gridTickets = document.getElementById('grid-tickets');
      const listTickets = document.getElementById('list-tickets');

      let currentView = localStorage.getItem('tickets-view') || 'grid';

      function setView(view) {
        if (view === 'list') {
          gridTickets.style.display = 'none';
          listTickets.style.display = 'block';
          gridView.classList.remove('text-gray-600');
          gridView.classList.add('text-gray-400');
          listView.classList.remove('text-gray-400');
          listView.classList.add('text-gray-600');
        } else {
          gridTickets.style.display = 'grid';
          listTickets.style.display = 'none';
          gridView.classList.remove('text-gray-400');
          gridView.classList.add('text-gray-600');
          listView.classList.remove('text-gray-600');
          listView.classList.add('text-gray-400');
        }
        localStorage.setItem('tickets-view', view);
      }

      setView(currentView);

      gridView?.addEventListener('click', () => setView('grid'));
      listView?.addEventListener('click', () => setView('list'));
    }

    function initializeAutoRefresh() {
      // Auto-refresh every 5 minutes if user is active
      let refreshInterval;
      let userActive = true;

      function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(() => {
          if (userActive) {
            console.log('Auto-refreshing tickets...');
            submitFilters();
          }
        }, 300000); // 5 minutes
      }

      // Track user activity
      ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, () => {
          userActive = true;
        }, true);
      });

      // Set inactive after 10 minutes
      setInterval(() => {
        userActive = false;
      }, 600000);

      startAutoRefresh();
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
        } else {
          filters.classList.add('hidden');
          icon.style.transform = 'rotate(0deg)';
        }
      });
    }

    function initializeTicketActions() {
      // Initialize all ticket action buttons
      document.querySelectorAll('[onclick*="viewTicketDetails"]').forEach(button => {
        const ticketId = button.getAttribute('data-ticket-id');
        if (ticketId) {
          button.onclick = () => viewTicketDetails(ticketId);
        }
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

      // Enhanced URL state management
      const newUrl = window.location.pathname + '?' + params.toString();
      window.history.pushState({
        filters: Object.fromEntries(params)
      }, '', newUrl);

      // Update page title to reflect current search
      const keywords = params.get('keywords');
      const platform = params.get('platform');
      let title = 'HD Tickets - Sports Events';

      if (keywords || platform) {
        const parts = [];
        if (keywords) parts.push(`"${keywords}"`);
        if (platform) parts.push(platform.toUpperCase());
        title = `HD Tickets - ${parts.join(' on ')}`;
      }
      document.title = title;

      window.location.reload();
    }

    function clearAllFilters() {
      showLoadingWithContext('clear');
      // Clear URL parameters and reset title
      window.history.pushState({}, '', window.location.pathname);
      document.title = 'HD Tickets - Sports Events';
      window.location.href = window.location.pathname;
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
      if (event.state && event.state.filters) {
        // Restore form state from history
        const form = document.getElementById('filters-form');
        Object.keys(event.state.filters).forEach(key => {
          const input = form.querySelector(`[name="${key}"]`);
          if (input) {
            input.value = event.state.filters[key];
          }
        });
      }
      window.location.reload();
    });

    // Enhanced keyboard shortcuts
    document.addEventListener('keydown', function(e) {
      // Ctrl/Cmd + K to focus search
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="keywords"]');
        if (searchInput) {
          searchInput.focus();
          searchInput.select();
        }
      }

      // Ctrl/Cmd + Enter to submit search
      if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        e.preventDefault();
        showLoadingWithContext('search');
        submitFilters();
      }

      // Escape to clear search when focused
      if (e.key === 'Escape') {
        const searchInput = document.querySelector('input[name="keywords"]');
        if (document.activeElement === searchInput && searchInput.value) {
          e.preventDefault();
          searchInput.value = '';
          const clearBtn = document.getElementById('clear-search');
          if (clearBtn) clearBtn.click();
        } else {
          hideSearchSuggestions();
        }
      }

      // Ctrl/Cmd + R to refresh (with loading indicator)
      if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        showLoadingWithContext('refresh');
      }
    });

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

    // Enhanced loading states with better user feedback
    function showLoading(message = 'Loading tickets...') {
      const container = document.getElementById('tickets-container');
      const loading = document.getElementById('loading-indicator');

      if (container && loading) {
        container.style.display = 'none';
        loading.style.display = 'flex';

        // Update loading message if provided
        const loadingText = loading.querySelector('.loading-text');
        if (loadingText && message) {
          loadingText.textContent = message;
        }

        // Add loading state to relevant buttons
        const buttons = document.querySelectorAll('button[type="submit"], .hd-btn');
        buttons.forEach(btn => {
          btn.disabled = true;
          btn.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Show progress indicator on search form
        const form = document.getElementById('filters-form');
        if (form) {
          form.classList.add('loading-state');
        }
      }
    }

    function hideLoading() {
      const container = document.getElementById('tickets-container');
      const loading = document.getElementById('loading-indicator');

      if (container && loading) {
        container.style.display = 'block';
        loading.style.display = 'none';

        // Remove loading state from buttons
        const buttons = document.querySelectorAll('button[type="submit"], .hd-btn');
        buttons.forEach(btn => {
          btn.disabled = false;
          btn.classList.remove('opacity-50', 'cursor-not-allowed');
        });

        // Remove loading state from search form
        const form = document.getElementById('filters-form');
        if (form) {
          form.classList.remove('loading-state');
        }
      }
    }

    // Show specific loading messages for different actions
    function showLoadingWithContext(action) {
      const messages = {
        'search': 'Searching tickets...',
        'filter': 'Applying filters...',
        'sort': 'Sorting results...',
        'refresh': 'Refreshing data...',
        'clear': 'Clearing filters...'
      };

      showLoading(messages[action] || 'Loading...');
    }

    function validatePriceRange() {
      const minPrice = parseFloat(document.querySelector('input[name="min_price"]').value) || 0;
      const maxPrice = parseFloat(document.querySelector('input[name="max_price"]').value) || 0;
      const minInput = document.querySelector('input[name="min_price"]');
      const maxInput = document.querySelector('input[name="max_price"]');

      // Remove existing error states
      [minInput, maxInput].forEach(input => {
        input.classList.remove('border-red-500', 'text-red-600');
      });

      if (minPrice > 0 && maxPrice > 0 && minPrice > maxPrice) {
        maxInput.classList.add('border-red-500', 'text-red-600');
        return false;
      }

      return true;
    }

    function updatePriceDisplay() {
      // Could add real-time price range display here
      const minPrice = document.querySelector('input[name="min_price"]').value;
      const maxPrice = document.querySelector('input[name="max_price"]').value;

      if (minPrice || maxPrice) {
        console.log(`Price range: $${minPrice || 0} - $${maxPrice || '∞'}`);
      }
    }

    // Modal for ticket details
    function viewTicketDetails(ticketId) {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
      modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full">
                    <h2 class="text-xl font-bold mb-4">Ticket Details</h2>
                    <p>ID: ${ticketId}</p>
                    <p>More details would be loaded here...</p>
                    <button class="mt-6 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" id="close-details-modal">Close</button>
                </div>
            `;
      document.body.appendChild(modal);
      document.getElementById('close-details-modal').onclick = () => modal.remove();
    }

    // Enhanced Modal for creating alert with proper functionality
    function createAlert(ticketId = null) {
      const modal = document.createElement('div');
      modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50';
      modal.setAttribute('role', 'dialog');
      modal.setAttribute('aria-modal', 'true');
      modal.setAttribute('aria-labelledby', 'create-alert-title');

      modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl p-8 max-w-lg w-full mx-4 transform transition-all">
          <div class="flex items-center justify-between mb-6">
            <h2 id="create-alert-title" class="text-xl font-bold text-gray-900">Create Sports Ticket Alert</h2>
            <button type="button" id="close-alert-modal" class="text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded" aria-label="Close modal">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form id="create-alert-form" class="space-y-4">
            <input type="hidden" name="ticket_id" value="${ticketId || ''}">
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''}">
            
            <div>
              <label for="alert-name" class="block text-sm font-medium text-gray-700 mb-1">Alert Name <span class="text-red-500">*</span></label>
              <input type="text" 
                     id="alert-name" 
                     name="name" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                     placeholder="e.g., Manchester United vs Liverpool" 
                     required>
              <p class="text-xs text-gray-500 mt-1">Give your alert a memorable name</p>
            </div>
            
            <div>
              <label for="alert-keywords" class="block text-sm font-medium text-gray-700 mb-1">Keywords <span class="text-red-500">*</span></label>
              <input type="text" 
                     id="alert-keywords" 
                     name="keywords" 
                     class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                     placeholder="e.g., Manchester United, Premier League, Old Trafford" 
                     required>
              <p class="text-xs text-gray-500 mt-1">Enter keywords to match against event names, teams, or venues</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label for="alert-platform" class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                <select id="alert-platform" 
                        name="platform" 
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                  <option value="">Any Platform</option>
                  <option value="stubhub">StubHub</option>
                  <option value="ticketmaster">Ticketmaster</option>
                  <option value="viagogo">Viagogo</option>
                  <option value="funzone">FunZone</option>
                  <option value="test">Test Platform</option>
                </select>
              </div>
              
              <div>
                <label for="alert-max-price" class="block text-sm font-medium text-gray-700 mb-1">Max Price ($)</label>
                <input type="number" 
                       id="alert-max-price" 
                       name="max_price" 
                       class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       placeholder="e.g., 200" 
                       min="0" 
                       step="0.01">
              </div>
            </div>
            
            <div class="border-t pt-4">
              <h4 class="text-sm font-medium text-gray-700 mb-3">Notification Preferences</h4>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input type="checkbox" id="email-notifications" name="email_notifications" checked class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">Email notifications</span>
                </label>
                <label class="flex items-center">
                  <input type="checkbox" id="sms-notifications" name="sms_notifications" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                  <span class="ml-2 text-sm text-gray-700">SMS notifications</span>
                </label>
              </div>
            </div>
            
            <div id="alert-error" class="hidden bg-red-50 border border-red-200 rounded-md p-3">
              <div class="flex">
                <svg class="w-5 h-5 text-red-400 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-red-700" id="alert-error-message"></div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4">
              <button type="button" id="cancel-alert" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Cancel
              </button>
              <button type="submit" id="submit-alert" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <span class="flex items-center">
                  <svg class="w-4 h-4 mr-2 hidden" id="alert-spinner" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" class="animate-spin"></path>
                  </svg>
                  Create Alert
                </span>
              </button>
            </div>
          </form>
        </div>
      `;

      document.body.appendChild(modal);

      // Focus the first input
      setTimeout(() => {
        modal.querySelector('#alert-name').focus();
      }, 100);

      // Event handlers
      const closeModal = () => {
        modal.remove();
      };

      modal.querySelector('#close-alert-modal').addEventListener('click', closeModal);
      modal.querySelector('#cancel-alert').addEventListener('click', closeModal);

      // Close on backdrop click
      modal.addEventListener('click', (e) => {
        if (e.target === modal) {
          closeModal();
        }
      });

      // Close on escape key
      modal.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeModal();
        }
      });

      // Form submission with AJAX
      modal.querySelector('#create-alert-form').addEventListener('submit', async (e) => {
        e.preventDefault();

        const form = e.target;
        const submitBtn = form.querySelector('#submit-alert');
        const spinner = form.querySelector('#alert-spinner');
        const errorDiv = form.querySelector('#alert-error');
        const errorMsg = form.querySelector('#alert-error-message');

        // Show loading state
        submitBtn.disabled = true;
        spinner.classList.remove('hidden');
        errorDiv.classList.add('hidden');

        try {
          const formData = new FormData(form);
          const data = Object.fromEntries(formData);

          const response = await fetch('{{ route('tickets.alerts.create') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': data._token,
              'Accept': 'application/json'
            },
            body: JSON.stringify(data)
          });

          const result = await response.json();

          if (response.ok && result.success) {
            // Success - show notification and close modal
            showNotification('success', result.message ||
              'Ticket alert created successfully! You will be notified when matching tickets are found.');
            closeModal();

            // Optionally refresh the page or update the UI
            if (window.location.pathname.includes('/alerts')) {
              window.location.reload();
            }
          } else {
            // Handle validation errors
            if (result.errors) {
              const errorMessages = Object.values(result.errors).flat().join(', ');
              errorMsg.textContent = errorMessages;
            } else {
              errorMsg.textContent = result.message || 'Failed to create alert. Please try again.';
            }
            errorDiv.classList.remove('hidden');
          }
        } catch (error) {
          console.error('Alert creation error:', error);
          errorMsg.textContent = 'Network error. Please check your connection and try again.';
          errorDiv.classList.remove('hidden');
        } finally {
          // Reset loading state
          submitBtn.disabled = false;
          spinner.classList.add('hidden');
        }
      });
    }

    function showSearchSuggestions() {
      const suggestionsDiv = document.getElementById('search-suggestions');
      const suggestionsList = document.getElementById('suggestions-list');
      const keywordsInput = document.querySelector('input[name="keywords"]');

      if (!suggestionsDiv || !suggestionsList || !keywordsInput) return;

      // Popular search terms based on current data
      const popularSearches = [
        'Basketball', 'Football', 'Concert', 'Theater', 'Baseball',
        'Hockey', 'Music Festival', 'Comedy Show', 'Opera', 'Ballet'
      ];

      // Get current search value
      const currentValue = keywordsInput.value.toLowerCase();

      // Filter suggestions based on current input
      const filteredSuggestions = popularSearches.filter(term =>
        term.toLowerCase().includes(currentValue) &&
        term.toLowerCase() !== currentValue
      );

      if (filteredSuggestions.length > 0) {
        // Clear previous suggestions
        suggestionsList.innerHTML = '';

        // Add filtered suggestions
        filteredSuggestions.slice(0, 5).forEach(term => {
          const suggestionItem = document.createElement('div');
          suggestionItem.className = 'px-3 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700';
          suggestionItem.textContent = term;

          suggestionItem.addEventListener('click', function() {
            keywordsInput.value = term;
            hideSearchSuggestions();
            showLoadingWithContext('search');
            submitFilters();
          });

          suggestionsList.appendChild(suggestionItem);
        });

        suggestionsDiv.classList.remove('hidden');
      } else {
        hideSearchSuggestions();
      }
    }

    function hideSearchSuggestions() {
      const suggestionsDiv = document.getElementById('search-suggestions');
      if (suggestionsDiv) {
        suggestionsDiv.classList.add('hidden');
      }
    }

    function showCreateAlertModal() {
      createAlert();
    }

    // Enhanced notification system
    function showNotification(type = 'info', message = '', duration = 5000) {
      const notification = document.createElement('div');
      notification.className =
        `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg transform transition-all duration-300 max-w-sm ${getNotificationClasses(type)}`;
      notification.style.transform = 'translateX(100%)';

      notification.innerHTML = `
        <div class="flex items-start">
          <div class="flex-shrink-0">
            ${getNotificationIcon(type)}
          </div>
          <div class="ml-3 flex-1">
            <p class="text-sm font-medium">${message}</p>
          </div>
          <div class="ml-4 flex-shrink-0">
            <button type="button" class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
        </div>
      `;

      document.body.appendChild(notification);

      // Animate in
      setTimeout(() => {
        notification.style.transform = 'translateX(0)';
      }, 50);

      // Auto remove
      if (duration > 0) {
        setTimeout(() => {
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => {
            if (notification.parentNode) {
              notification.remove();
            }
          }, 300);
        }, duration);
      }
    }

    function getNotificationClasses(type) {
      switch (type) {
        case 'success':
          return 'bg-green-50 border border-green-200 text-green-800';
        case 'error':
          return 'bg-red-50 border border-red-200 text-red-800';
        case 'warning':
          return 'bg-yellow-50 border border-yellow-200 text-yellow-800';
        default:
          return 'bg-blue-50 border border-blue-200 text-blue-800';
      }
    }

    function getNotificationIcon(type) {
      const iconClass = 'w-5 h-5';
      switch (type) {
        case 'success':
          return `<svg class="${iconClass} text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>`;
        case 'error':
          return `<svg class="${iconClass} text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>`;
        case 'warning':
          return `<svg class="${iconClass} text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                  </svg>`;
        default:
          return `<svg class="${iconClass} text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>`;
      }
    }

    // Enhanced error handling
    window.addEventListener('error', function(e) {
      console.error('JavaScript error:', e.error);
      hideLoading();
    });

    // Page visibility API for smart refresh
    document.addEventListener('visibilitychange', function() {
      if (document.visibilityState === 'visible') {
        // Check if data is stale and refresh if needed
        const lastRefresh = sessionStorage.getItem('lastRefresh');
        const now = Date.now();
        if (!lastRefresh || now - parseInt(lastRefresh) > 300000) { // 5 minutes
          console.log('Page became visible, checking for updates...');
          sessionStorage.setItem('lastRefresh', now.toString());
        }
      }
    });
  </script>

  <!-- Enhanced Styles -->
  <style>
    .hd-ticket-card {
      transition: all 0.2s ease-in-out;
    }

    .hd-ticket-card:hover {
      transform: translateY(-2px);
    }

    .pagination-enhanced .pagination {
      display: flex;
      align-items: center;
      space-x: 1rem;
    }

    .pagination-enhanced .pagination a,
    .pagination-enhanced .pagination span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 0.5rem 0.75rem;
      text-sm;
      font-medium;
      border: 1px solid #d1d5db;
      background-color: white;
      color: #374151;
      text-decoration: none;
      transition: all 0.2s;
    }

    .pagination-enhanced .pagination a:hover {
      background-color: #f3f4f6;
      border-color: #9ca3af;
    }

    .pagination-enhanced .pagination .current {
      background-color: #3b82f6;
      border-color: #3b82f6;
      color: white;
    }

    .pagination-enhanced .pagination .disabled {
      opacity: 0.5;
      cursor: not-allowed;
      pointer-events: none;
    }

    @media (max-width: 640px) {
      .hd-ticket-card {
        margin-bottom: 1rem;
      }
    }

    /* Loading skeleton animations */
    @keyframes skeleton-loading {
      0% {
        background-color: hsl(200, 20%, 80%);
      }

      100% {
        background-color: hsl(200, 20%, 95%);
      }
    }

    .hd-loading-skeleton--spinner {
      width: 2rem;
      height: 2rem;
      border: 3px solid #f3f4f6;
      border-top: 3px solid #3b82f6;
      border-radius: 50%;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
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
  </style>
</x-unified-layout>
