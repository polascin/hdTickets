<x-unified-layout title="Trending Sports Event Tickets"
  subtitle="Discover the most popular and in-demand sports event tickets">
  <x-slot name="headerActions">
    <div class="flex flex-col sm:flex-row gap-3">
      <x-ui.button href="{{ route('tickets.scraping.index') }}" variant="outline" icon="arrow-left">
        Back to All Tickets
      </x-ui.button>
      <x-ui.button href="{{ route('tickets.scraping.best-deals') }}" variant="secondary" icon="tag">
        Best Deals
      </x-ui.button>
      <x-ui.button href="{{ route('tickets.alerts.index') }}" variant="primary" icon="bell">
        Create Alert
      </x-ui.button>
    </div>
  </x-slot>

  <!-- Error Message -->
  @if (session('error'))
    <div class="mb-6">
      <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
        <div class="flex items-center">
          <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd" />
          </svg>
          <p class="text-red-700 font-medium">{{ session('error') }}</p>
        </div>
      </div>
    </div>
  @endif

  <div class="space-y-8">
    <!-- Trending Statistics Overview -->
    <x-ui.card>
      <x-ui.card-header>
        <x-ui.card-title icon="trending-up">Trending Overview</x-ui.card-title>
      </x-ui.card-header>
      <x-ui.card-content>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Total Trending -->
          <div class="text-center">
            <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['total_trending']) }}</div>
            <div class="text-sm text-gray-500 mt-1">Trending Tickets</div>
          </div>

          <!-- Platforms -->
          <div class="text-center">
            <div class="text-3xl font-bold text-green-600">{{ count($stats['platforms']) }}</div>
            <div class="text-sm text-gray-500 mt-1">Active Platforms</div>
          </div>

          <!-- Average Price -->
          <div class="text-center">
            <div class="text-3xl font-bold text-purple-600">
              ${{ $stats['avg_price'] ? number_format($stats['avg_price'], 0) : '0' }}
            </div>
            <div class="text-sm text-gray-500 mt-1">Average Price</div>
          </div>

          <!-- Date Range -->
          <div class="text-center">
            <div class="text-lg font-semibold text-gray-700">
              @if ($stats['date_range']['from'] && $stats['date_range']['to'])
                {{ \Carbon\Carbon::parse($stats['date_range']['from'])->format('M j') }} -
                {{ \Carbon\Carbon::parse($stats['date_range']['to'])->format('M j') }}
              @else
                No Events
              @endif
            </div>
            <div class="text-sm text-gray-500 mt-1">Event Period</div>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Filters & Search -->
    <x-ui.card>
      <x-ui.card-content class="p-6">
        <div class="flex flex-col lg:flex-row gap-4 items-center">
          <div class="flex-1">
            <label for="sport-filter" class="block text-sm font-medium text-gray-700 mb-2">Filter by Sport</label>
            <select id="sport-filter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="all">All Sports</option>
              <option value="football">Football</option>
              <option value="basketball">Basketball</option>
              <option value="baseball">Baseball</option>
              <option value="soccer">Soccer</option>
              <option value="hockey">Hockey</option>
            </select>
          </div>
          <div class="flex-1">
            <label for="limit-filter" class="block text-sm font-medium text-gray-700 mb-2">Results Limit</label>
            <select id="limit-filter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="20">20 Results</option>
              <option value="50">50 Results</option>
              <option value="100">100 Results</option>
            </select>
          </div>
          <div class="flex-shrink-0">
            <button id="apply-filters"
              class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
              Apply Filters
            </button>
          </div>
        </div>
      </x-ui.card-content>
    </x-ui.card>

    <!-- Trending Tickets Grid -->
    @if ($tickets->count() > 0)
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($tickets as $ticket)
          <x-ui.card class="hover:shadow-lg transition-shadow duration-300">
            <x-ui.card-content class="p-6">
              <!-- Ticket Header -->
              <div class="flex justify-between items-start mb-4">
                <div class="flex-1">
                  <h3 class="font-semibold text-lg text-gray-900 line-clamp-2">
                    {{ $ticket->title ?? ($ticket->event_title ?? 'Sports Event') }}
                  </h3>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ $ticket->venue ?? 'Venue TBD' }}
                  </p>
                </div>
                <div class="flex-shrink-0 ml-4">
                  @if ($ticket->is_trending ?? false)
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                      🔥 Trending
                    </span>
                  @endif
                </div>
              </div>

              <!-- Event Date -->
              <div class="flex items-center text-sm text-gray-600 mb-3">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                @if ($ticket->event_date && \Carbon\Carbon::parse($ticket->event_date)->isValid())
                  {{ \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y \a\t g:i A') }}
                @else
                  <span class="text-gray-500">Date TBD</span>
                @endif
              </div>

              <!-- Price Information -->
              <div class="mb-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Price Range:</span>
                  <div class="text-right">
                    @if ($ticket->min_price && $ticket->max_price)
                      <span class="font-semibold text-green-600">
                        ${{ number_format($ticket->min_price, 0) }} - ${{ number_format($ticket->max_price, 0) }}
                      </span>
                    @elseif($ticket->min_price)
                      <span class="font-semibold text-green-600">
                        From ${{ number_format($ticket->min_price, 0) }}
                      </span>
                    @else
                      <span class="text-gray-500">Price TBD</span>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Platform & Availability -->
              <div class="flex justify-between items-center mb-4">
                <div class="flex items-center">
                  <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded uppercase font-medium">
                    {{ ucfirst($ticket->platform) }}
                  </span>
                </div>
                <div class="flex items-center">
                  @if ($ticket->is_available ?? true)
                    <span class="text-xs text-green-600 font-medium">✓ Available</span>
                  @else
                    <span class="text-xs text-red-600 font-medium">⚠ Limited</span>
                  @endif
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex gap-2">
                <a href="{{ route('tickets.scraping.show', $ticket->id) }}"
                  class="flex-1 text-center px-4 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors">
                  View Details
                </a>
                @if ($ticket->external_url)
                  <a href="{{ $ticket->external_url }}" target="_blank"
                    class="flex-1 text-center px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 transition-colors">
                    Buy Now
                  </a>
                @endif
              </div>
            </x-ui.card-content>
          </x-ui.card>
        @endforeach
      </div>
    @else
      <!-- No Tickets State -->
      <x-ui.card>
        <x-ui.card-content class="text-center py-12">
          <div class="text-gray-400 mb-4">
            <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.02-5.691-2.709M6.343 6.343l10.314 10.314" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Trending Tickets Found</h3>
          <p class="text-gray-500 mb-6">
            There are currently no trending sports event tickets available. Check back later or explore other
            categories.
          </p>
          <div class="flex justify-center gap-4">
            <x-ui.button href="{{ route('tickets.scraping.index') }}" variant="primary">
              Browse All Tickets
            </x-ui.button>
            <x-ui.button href="{{ route('tickets.scraping.high-demand-sports') }}" variant="outline">
              High-Demand Sports
            </x-ui.button>
          </div>
        </x-ui.card-content>
      </x-ui.card>
    @endif

    <!-- Quick Actions -->
    <x-ui.card>
      <x-ui.card-header>
        <x-ui.card-title icon="zap">Quick Actions</x-ui.card-title>
      </x-ui.card-header>
      <x-ui.card-content>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <a href="{{ route('tickets.alerts.index') }}"
            class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
            <div class="flex-shrink-0 w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-5 5v-5z M13 13h5l-5 5v-5z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">Set Price Alert</p>
              <p class="text-xs text-gray-500">Get notified when prices drop</p>
            </div>
          </a>

          <a href="{{ route('tickets.scraping.high-demand-sports') }}"
            class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
            <div class="flex-shrink-0 w-10 h-10 bg-red-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 10V3L4 14h7v7l9-11h-7z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">High Demand</p>
              <p class="text-xs text-gray-500">Popular sports events</p>
            </div>
          </a>

          <a href="{{ route('tickets.scraping.best-deals') }}"
            class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
            <div class="flex-shrink-0 w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">Best Deals</p>
              <p class="text-xs text-gray-500">Find discounted tickets</p>
            </div>
          </a>
        </div>
      </x-ui.card-content>
    </x-ui.card>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const applyFiltersBtn = document.getElementById('apply-filters');
      const sportFilter = document.getElementById('sport-filter');
      const limitFilter = document.getElementById('limit-filter');

      if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
          const sport = sportFilter.value;
          const limit = limitFilter.value;

          // Build new URL with filters
          const url = new URL(window.location.href);
          url.searchParams.set('sport', sport);
          url.searchParams.set('limit', limit);

          // Reload page with new filters
          window.location.href = url.toString();
        });
      }

      // Auto-refresh every 5 minutes for trending data
      setTimeout(function() {
        window.location.reload();
      }, 300000); // 5 minutes
    });
  </script>
</x-unified-layout>
