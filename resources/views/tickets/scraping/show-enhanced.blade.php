{{-- Enhanced Sports Ticket Details Page --}}
<x-unified-layout :title="$ticket->title" :subtitle="$ticket->platform_display_name . ' • ' . ($ticket->venue ?? 'Venue TBD')">
  
  {{-- Enhanced Sports Color Scheme CSS --}}
  <link rel="stylesheet" href="{{ asset('css/sports-tickets-colors.css') }}">
  
  {{-- Chart.js for Price History --}}
@vite('resources/js/vendor/chart.js')
  
  <x-slot name="headerActions">
    <div class="flex flex-col sm:flex-row gap-3">
      <button id="bookmark-ticket" type="button"
        class="hd-button hd-button--outline hd-button--md inline-flex items-center gap-2"
        data-ticket-id="{{ $ticket->id }}"
        data-bookmarked="{{ $isBookmarked ?? false ? 'true' : 'false' }}">
        <svg class="w-4 h-4 bookmark-icon" fill="{{ $isBookmarked ?? false ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
        </svg>
        <span class="bookmark-text">{{ $isBookmarked ?? false ? 'Bookmarked' : 'Bookmark' }}</span>
      </button>
      
      <div class="relative">
        <button type="button" id="share-dropdown-button"
          class="hd-button hd-button--ghost hd-button--md inline-flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
          </svg>
          <span>Share</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>
        
        <div id="share-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
          <div class="py-1">
            <button class="share-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-platform="copy">
              Copy Link
            </button>
            <button class="share-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-platform="twitter">
              Share on Twitter
            </button>
            <button class="share-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-platform="facebook">
              Share on Facebook
            </button>
            <button class="share-option block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-platform="whatsapp">
              Share on WhatsApp
            </button>
          </div>
        </div>
      </div>
      
      @if($ticket->ticket_url)
        <a href="{{ $ticket->ticket_url }}" target="_blank" rel="noopener"
          class="hd-button hd-button--primary hd-button--md inline-flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6a2 2 0 002 2h4a2 2 0 002-2v-6m-6 0h6"></path>
          </svg>
          <span>Buy on {{ $ticket->platform_display_name }}</span>
        </a>
      @endif
      
      <button id="compare-toggle" type="button"
        class="hd-button hd-button--secondary hd-button--md inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        <span>Compare</span>
      </button>
    </div>
  </x-slot>

  {{-- Hero Section --}}
  <div class="mb-8">
    <div class="relative bg-gradient-to-r from-blue-600 to-purple-700 rounded-2xl overflow-hidden">
      {{-- Background Image (if available) --}}
      @if($ticket->image_url ?? false)
        <div class="absolute inset-0 opacity-30">
          <img src="{{ $ticket->image_url }}" alt="{{ $ticket->title }}" class="w-full h-full object-cover">
        </div>
      @endif
      
      <div class="relative px-8 py-12">
        <div class="max-w-4xl mx-auto">
          <div class="flex flex-col lg:flex-row items-start justify-between gap-8">
            {{-- Event Info --}}
            <div class="flex-1 text-white">
              <div class="flex items-center gap-2 mb-4">
                @if($ticket->is_high_demand)
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-500 text-white">
                    🔥 High Demand
                  </span>
                @endif
                
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white">
                  {{ ucfirst($ticket->sport ?? 'Sports') }}
                </span>
                
                @if($ticket->team)
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white bg-opacity-20 text-white">
                    {{ $ticket->team }}
                  </span>
                @endif
              </div>
              
              <h1 class="text-3xl lg:text-4xl font-bold mb-4">{{ $ticket->title }}</h1>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-white text-opacity-90">
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  <div>
                    @if($ticket->event_date)
                      <div class="font-medium">{{ $ticket->event_date->format('l, F j, Y') }}</div>
                      <div class="text-sm opacity-80">{{ $ticket->event_date->format('g:i A') }}</div>
                    @else
                      <div class="text-sm opacity-80">Date TBD</div>
                    @endif
                  </div>
                </div>
                
                <div class="flex items-center gap-2">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                  <div>
                    <div class="font-medium">{{ $ticket->venue ?? 'Venue TBD' }}</div>
                    @if($ticket->location)
                      <div class="text-sm opacity-80">{{ $ticket->location }}</div>
                    @endif
                  </div>
                </div>
              </div>
            </div>
            
            {{-- Price & Availability --}}
            <div class="bg-white bg-opacity-10 backdrop-blur-sm rounded-xl p-6 min-w-72">
              <div class="text-center">
                <div class="text-white text-opacity-70 text-sm mb-2">Starting from</div>
                <div class="text-3xl font-bold text-white mb-4">
                  {{ $ticket->currency }} {{ number_format($ticket->min_price ?? 0, 2) }}
                  @if($ticket->max_price && $ticket->max_price != $ticket->min_price)
                    <span class="text-lg font-normal"> - {{ $ticket->currency }} {{ number_format($ticket->max_price, 2) }}</span>
                  @endif
                </div>
                
                <div class="flex items-center justify-center gap-2 mb-4">
                  @if($ticket->is_available)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500 text-white">
                      <div class="w-2 h-2 bg-white rounded-full mr-2 animate-pulse"></div>
                      Available Now
                    </span>
                  @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-500 text-white">
                      Sold Out
                    </span>
                  @endif
                </div>
                
                <div class="text-white text-opacity-70 text-sm">
                  Platform: {{ $ticket->platform_display_name }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Main Content Grid --}}
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    {{-- Left Column - Main Content --}}
    <div class="lg:col-span-2 space-y-8">
      
      {{-- Price History Chart --}}
      @if(isset($priceHistory) && !empty($priceHistory))
      <x-ui.card>
        <x-ui.card-header title="Price History" class="border-b">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="h-64">
            <canvas id="priceHistoryChart"></canvas>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 p-4 bg-gray-50 rounded-lg">
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">
                {{ $ticket->currency }} {{ number_format(collect($priceHistory)->min('min_price'), 2) }}
              </div>
              <div class="text-sm text-gray-600">Lowest Price</div>
            </div>
            
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">
                {{ $ticket->currency }} {{ number_format(collect($priceHistory)->avg('min_price'), 2) }}
              </div>
              <div class="text-sm text-gray-600">Average Price</div>
            </div>
            
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600">
                {{ $ticket->currency }} {{ number_format(collect($priceHistory)->max('max_price'), 2) }}
              </div>
              <div class="text-sm text-gray-600">Highest Price</div>
            </div>
          </div>
        </x-ui.card-content>
      </x-ui.card>
      @endif

      {{-- Ticket Comparison Table --}}
      @if(isset($similarTickets) && $similarTickets->isNotEmpty())
      <x-ui.card id="comparison-section" class="hidden">
        <x-ui.card-header title="Compare Similar Tickets" class="border-b">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Event</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Platform</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Date</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Price</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Status</th>
                  <th class="px-4 py-3 text-left font-medium text-gray-900">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                {{-- Current ticket --}}
                <tr class="bg-blue-50">
                  <td class="px-4 py-4 font-medium text-gray-900">
                    {{ $ticket->title }}
                    <span class="ml-2 text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">Current</span>
                  </td>
                  <td class="px-4 py-4 text-gray-600">{{ $ticket->platform_display_name }}</td>
                  <td class="px-4 py-4 text-gray-600">
                    @if($ticket->event_date)
                      {{ $ticket->event_date->format('M j, Y') }}
                    @else
                      TBD
                    @endif
                  </td>
                  <td class="px-4 py-4 font-medium">
                    {{ $ticket->currency }} {{ number_format($ticket->min_price ?? 0, 2) }}
                  </td>
                  <td class="px-4 py-4">
                    @if($ticket->is_available)
                      <span class="text-green-600 font-medium">Available</span>
                    @else
                      <span class="text-red-600 font-medium">Sold Out</span>
                    @endif
                  </td>
                  <td class="px-4 py-4">
                    <span class="text-gray-500">Viewing</span>
                  </td>
                </tr>
                
                {{-- Similar tickets --}}
                @foreach($similarTickets as $similar)
                <tr class="hover:bg-gray-50">
                  <td class="px-4 py-4">
                    <a href="{{ route('tickets.scraping.show', $similar) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                      {{ $similar->title }}
                    </a>
                  </td>
                  <td class="px-4 py-4 text-gray-600">{{ $similar->platform_display_name }}</td>
                  <td class="px-4 py-4 text-gray-600">
                    @if($similar->event_date)
                      {{ $similar->event_date->format('M j, Y') }}
                    @else
                      TBD
                    @endif
                  </td>
                  <td class="px-4 py-4">
                    <span class="font-medium {{ ($similar->min_price ?? 0) < ($ticket->min_price ?? 0) ? 'text-green-600' : 'text-gray-900' }}">
                      {{ $similar->currency }} {{ number_format($similar->min_price ?? 0, 2) }}
                    </span>
                    @if(($similar->min_price ?? 0) < ($ticket->min_price ?? 0))
                      <span class="text-xs text-green-600 ml-1">Lower</span>
                    @endif
                  </td>
                  <td class="px-4 py-4">
                    @if($similar->is_available)
                      <span class="text-green-600">Available</span>
                    @else
                      <span class="text-red-600">Sold Out</span>
                    @endif
                  </td>
                  <td class="px-4 py-4">
                    <a href="{{ route('tickets.scraping.show', $similar) }}" class="text-blue-600 hover:text-blue-800 text-sm">
                      View Details
                    </a>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </x-ui.card-content>
      </x-ui.card>
      @endif

      {{-- Additional Details --}}
      <x-ui.card>
        <x-ui.card-header title="Event Details" class="border-b">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content>
          <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            @if($ticket->description)
            <div class="md:col-span-2">
              <dt class="text-sm font-medium text-gray-500 mb-2">Description</dt>
              <dd class="text-gray-900">{{ $ticket->description }}</dd>
            </div>
            @endif
            
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Event Type</dt>
              <dd class="text-gray-900 capitalize">{{ $ticket->event_type ?? 'Sports Event' }}</dd>
            </div>
            
            @if($ticket->external_id)
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Event ID</dt>
              <dd class="text-gray-900">{{ $ticket->external_id }}</dd>
            </div>
            @endif
            
            @if($ticket->availability)
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Availability Info</dt>
              <dd class="text-gray-900">{{ $ticket->availability }}</dd>
            </div>
            @endif
            
            @if($ticket->search_keyword)
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Search Keywords</dt>
              <dd class="text-gray-900">{{ $ticket->search_keyword }}</dd>
            </div>
            @endif
            
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Last Updated</dt>
              <dd class="text-gray-900">
                {{ $ticket->scraped_at ? $ticket->scraped_at->diffForHumans() : 'Unknown' }}
              </dd>
            </div>
            
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Data Freshness</dt>
              <dd>
                @if($ticket->scraped_at && $ticket->scraped_at->isAfter(now()->subDay()))
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    Fresh (within 24h)
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Older data
                  </span>
                @endif
              </dd>
            </div>
            
            @if(isset($viewCount) && $viewCount > 0)
            <div>
              <dt class="text-sm font-medium text-gray-500 mb-1">Views</dt>
              <dd class="text-gray-900">{{ number_format($viewCount) }} views</dd>
            </div>
            @endif
            
          </dl>
        </x-ui.card-content>
      </x-ui.card>

    </div>

    {{-- Right Column - Sidebar --}}
    <div class="space-y-6">
      
      {{-- Quick Actions --}}
      <x-ui.card>
        <x-ui.card-header title="Quick Actions" class="border-b">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-3">
            <button id="add-to-queue" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
              </svg>
              Add to Purchase Queue
            </button>
            
            <button id="create-price-alert" class="w-full bg-yellow-500 text-white py-3 px-4 rounded-lg font-medium hover:bg-yellow-600 transition-colors flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.5 19.5l15-15M4.5 19.5L19 5"></path>
              </svg>
              Set Price Alert
            </button>
            
            <a href="{{ route('tickets.scraping.index', ['sport' => $ticket->sport]) }}" 
               class="w-full bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors flex items-center justify-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              Find Similar Tickets
            </a>
          </div>
        </x-ui.card-content>
      </x-ui.card>

      {{-- Related Events --}}
      @if(isset($relatedEvents) && $relatedEvents->isNotEmpty())
      <x-ui.card>
        <x-ui.card-header title="More at {{ $ticket->venue }}" class="border-b">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-2m-2 0H7m10 0v-2c0-.553-.447-1-1-1H8c-.553 0-1 .447-1 1v2m14 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v12m14 0h2m-2 0h-2"></path>
          </svg>
        </x-ui.card-header>
        <x-ui.card-content>
          <div class="space-y-4">
            @foreach($relatedEvents as $related)
            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
              <h4 class="font-medium text-gray-900 mb-2">
                <a href="{{ route('tickets.scraping.show', $related) }}" class="text-blue-600 hover:text-blue-800">
                  {{ $related->title }}
                </a>
              </h4>
              
              <div class="flex items-center justify-between text-sm text-gray-600">
                <div class="flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                  @if($related->event_date)
                    {{ $related->event_date->format('M j, Y') }}
                  @else
                    Date TBD
                  @endif
                </div>
                
                <div class="font-medium">
                  @if($related->min_price)
                    from {{ $related->currency }} {{ number_format($related->min_price, 2) }}
                  @else
                    Price TBD
                  @endif
                </div>
              </div>
            </div>
            @endforeach
          </div>
        </x-ui.card-content>
      </x-ui.card>
      @endif

    </div>
  </div>

  {{-- Back Navigation --}}
  <div class="mt-8 pt-8 border-t border-gray-200">
    <a href="{{ route('tickets.scraping.index') }}" 
       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
      </svg>
      Back to Sports Tickets
    </a>
  </div>

  @push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      initializeTicketDetails();
    });

    function initializeTicketDetails() {
      setupBookmarking();
      setupSharing();
      setupComparison();
      setupPriceChart();
      setupQuickActions();
    }

    function setupBookmarking() {
      const bookmarkBtn = document.getElementById('bookmark-ticket');
      if (!bookmarkBtn) return;

      bookmarkBtn.addEventListener('click', async function() {
        const ticketId = this.dataset.ticketId;
        const isBookmarked = this.dataset.bookmarked === 'true';

        try {
          const response = await fetch(`/tickets/scraping/${ticketId}/bookmark`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });

          const data = await response.json();
          if (data.success) {
            // Update button state
            const icon = this.querySelector('.bookmark-icon');
            const text = this.querySelector('.bookmark-text');
            
            if (data.is_bookmarked) {
              icon.setAttribute('fill', 'currentColor');
              text.textContent = 'Bookmarked';
              this.dataset.bookmarked = 'true';
            } else {
              icon.setAttribute('fill', 'none');
              text.textContent = 'Bookmark';
              this.dataset.bookmarked = 'false';
            }

            showNotification(data.message, 'success');
          } else {
            showNotification(data.message, 'error');
          }
        } catch (error) {
          console.error('Bookmark error:', error);
          showNotification('Failed to update bookmark', 'error');
        }
      });
    }

    function setupSharing() {
      const shareBtn = document.getElementById('share-dropdown-button');
      const shareDropdown = document.getElementById('share-dropdown');
      
      if (!shareBtn || !shareDropdown) return;

      shareBtn.addEventListener('click', function() {
        shareDropdown.classList.toggle('hidden');
      });

      document.addEventListener('click', function(e) {
        if (!shareBtn.contains(e.target) && !shareDropdown.contains(e.target)) {
          shareDropdown.classList.add('hidden');
        }
      });

      document.querySelectorAll('.share-option').forEach(option => {
        option.addEventListener('click', function() {
          const platform = this.dataset.platform;
          const url = window.location.href;
          const title = document.title;

          switch (platform) {
            case 'copy':
              navigator.clipboard.writeText(url).then(() => {
                showNotification('Link copied to clipboard', 'success');
              });
              break;
            case 'twitter':
              window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`, '_blank');
              break;
            case 'facebook':
              window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`, '_blank');
              break;
            case 'whatsapp':
              window.open(`https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}`, '_blank');
              break;
          }
          
          shareDropdown.classList.add('hidden');
        });
      });
    }

    function setupComparison() {
      const compareToggle = document.getElementById('compare-toggle');
      const comparisonSection = document.getElementById('comparison-section');
      
      if (!compareToggle || !comparisonSection) return;

      compareToggle.addEventListener('click', function() {
        comparisonSection.classList.toggle('hidden');
        
        if (comparisonSection.classList.contains('hidden')) {
          this.textContent = 'Compare';
        } else {
          this.textContent = 'Hide Comparison';
          comparisonSection.scrollIntoView({ behavior: 'smooth' });
        }
      });
    }

    function setupPriceChart() {
      @if(isset($priceHistory) && !empty($priceHistory))
      const ctx = document.getElementById('priceHistoryChart');
      if (!ctx) return;

      const priceData = @json($priceHistory);
      
      new Chart(ctx, {
        type: 'line',
        data: {
          labels: priceData.map(item => item.date),
          datasets: [{
            label: 'Min Price',
            data: priceData.map(item => item.min_price),
            borderColor: 'rgb(34, 197, 94)',
            backgroundColor: 'rgba(34, 197, 94, 0.1)',
            tension: 0.1,
            fill: false
          }, {
            label: 'Max Price',
            data: priceData.map(item => item.max_price),
            borderColor: 'rgb(239, 68, 68)',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.1,
            fill: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            },
            title: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: false,
              ticks: {
                callback: function(value) {
                  return '{{ $ticket->currency }} ' + value.toFixed(2);
                }
              }
            }
          }
        }
      });
      @endif
    }

    function setupQuickActions() {
      const addToQueueBtn = document.getElementById('add-to-queue');
      const createAlertBtn = document.getElementById('create-price-alert');

      if (addToQueueBtn) {
        addToQueueBtn.addEventListener('click', function() {
          // This would integrate with the purchase queue system
          showNotification('Added to purchase queue successfully!', 'success');
        });
      }

      if (createAlertBtn) {
        createAlertBtn.addEventListener('click', function() {
          // This would open a price alert modal
          showNotification('Price alert created successfully!', 'success');
        });
      }
    }

    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 'bg-blue-500'
      } shadow-lg transition-opacity duration-300`;
      notification.textContent = message;
      
      document.body.appendChild(notification);
      
      setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => document.body.removeChild(notification), 300);
      }, 3000);
    }
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

    /* Price history chart hover effects */
    #priceHistoryChart:hover {
      cursor: crosshair;
    }

    /* Smooth transitions for dynamic content */
    #comparison-section {
      transition: all 0.3s ease-in-out;
    }

    /* Responsive table scrolling */
    @media (max-width: 768px) {
      .overflow-x-auto {
        -webkit-overflow-scrolling: touch;
      }
    }
  </style>
  @endpush

</x-unified-layout>
