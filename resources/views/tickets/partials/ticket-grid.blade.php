{{-- Ticket Grid Partial View for AJAX responses --}}
@if($tickets->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="tickets-grid">
        @foreach($tickets as $ticket)
            <div class="ticket-card bg-white rounded-lg shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200" 
                 data-ticket-id="{{ $ticket->id }}">
                
                {{-- Ticket Image --}}
                <div class="relative">
                    <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-purple-600 rounded-t-lg flex items-center justify-center">
                        <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5zM11 19H9a2 2 0 01-2-2V7a2 2 0 012-2h2m0 14h2a2 2 0 002-2v-3a1 1 0 00-1-1h-1a1 1 0 00-1 1v3a2 2 0 01-2 2h-2z"></path>
                        </svg>
                    </div>
                    
                    {{-- Availability Status Badge --}}
                    <div class="absolute top-2 left-2">
                        <span class="availability-status px-2 py-1 text-xs font-semibold rounded-full
                            {{ $ticket->is_available ? 'bg-green-100 text-green-800' : 
                               ($ticket->is_high_demand ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            @if($ticket->is_available && $ticket->is_high_demand)
                                Limited
                            @elseif($ticket->is_available)
                                Available
                            @else
                                Sold Out
                            @endif
                        </span>
                    </div>
                    
                    {{-- Platform Badge --}}
                    <div class="absolute top-2 right-2">
                        <span class="px-2 py-1 text-xs font-medium bg-black bg-opacity-75 text-white rounded-full">
                            {{ ucfirst($ticket->platform) }}
                        </span>
                    </div>
                </div>
                
                {{-- Ticket Content --}}
                <div class="p-4">
                    {{-- Event Title --}}
                    <h3 class="ticket-title font-semibold text-gray-900 mb-2 line-clamp-2">
                        {{ $ticket->title }}
                    </h3>
                    
                    {{-- Event Details --}}
                    <div class="space-y-2 text-sm text-gray-600 mb-4">
                        @if($ticket->venue)
                            <div class="ticket-venue flex items-center">
<x-icon name="map-pin" class="w-4 h-4 mr-2 text-gray-400" />
                                {{ $ticket->venue }}
                            </div>
                        @endif
                        
                        @if($ticket->location)
                            <div class="ticket-city flex items-center">
<x-icon name="building" class="w-4 h-4 mr-2 text-gray-400" />
                                {{ $ticket->location }}
                            </div>
                        @endif
                        
                        @if($ticket->event_date)
                            <div class="ticket-date flex items-center">
<x-icon name="calendar" class="w-4 h-4 mr-2 text-gray-400" />
                                {{ \Carbon\Carbon::parse($ticket->event_date)->format('M j, Y g:i A') }}
                            </div>
                        @endif
                        
                        @if($ticket->sport)
                            <div class="ticket-category">
                                <span class="inline-block px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                    {{ ucfirst($ticket->sport) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Price Section --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-2">
                            @if($ticket->min_price && $ticket->max_price && $ticket->min_price != $ticket->max_price)
                                <span class="ticket-price text-lg font-bold text-gray-900">
                                    ${{ number_format($ticket->min_price, 2) }} - ${{ number_format($ticket->max_price, 2) }}
                                </span>
                            @else
                                <span class="ticket-price text-2xl font-bold text-gray-900">
                                    ${{ number_format($ticket->min_price ?? $ticket->max_price ?? 0, 2) }}
                                </span>
                            @endif
                            
                            {{-- High demand indicator --}}
                            @if($ticket->is_high_demand)
                                <span class="text-xs font-medium text-red-600">
                                    🔥 High Demand
                                </span>
                            @endif
                        </div>
                        
                        @if($ticket->popularity_score && $ticket->popularity_score > 50)
                            <div class="flex items-center text-xs text-gray-500">
<x-icon name="eye" class="w-4 h-4 mr-1" />
                                {{ number_format($ticket->popularity_score) }}% popular
                            </div>
                        @endif
                    </div>
                    
                    {{-- Action Buttons --}}
                    <div class="ticket-actions flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            {{-- Bookmark Button --}}
                            <button class="bookmark-toggle text-gray-400 hover:text-yellow-500 transition-colors" 
                                    data-ticket-id="{{ $ticket->id }}"
                                    title="Bookmark this ticket">
<x-icon name="bookmark" class="w-5 h-5" />
                            </button>
                            
                            {{-- Share Button --}}
                            <button class="share-button text-gray-400 hover:text-blue-500 transition-colors"
                                    data-ticket-id="{{ $ticket->id }}"
                                    data-title="{{ $ticket->title }}"
                                    data-url="{{ url('/tickets/' . $ticket->id) }}"
                                    title="Share this ticket">
<x-icon name="share" class="w-5 h-5" />
                            </button>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            {{-- View Details Link --}}
                            <a href="{{ url('/tickets/' . $ticket->id) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                               data-track="view_details">
                                View Details
                            </a>
                            
                            {{-- External Link --}}
                            @if($ticket->ticket_url)
                                <a href="{{ $ticket->ticket_url }}" 
                                   target="_blank" 
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors"
                                   data-track="buy_ticket">
                                    Buy Now
<x-icon name="external-link" class="w-4 h-4 ml-1" />
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Freshness Indicator --}}
                    @if($ticket->updated_at)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>Updated {{ \Carbon\Carbon::parse($ticket->updated_at)->diffForHumans() }}</span>
                                @php
                                    $hoursSinceUpdate = \Carbon\Carbon::parse($ticket->updated_at)->diffInHours();
                                    $freshnessClass = $hoursSinceUpdate < 1 ? 'text-green-600' : ($hoursSinceUpdate < 6 ? 'text-yellow-600' : 'text-gray-500');
                                @endphp
                                <span class="{{ $freshnessClass }}">
                                    @if($hoursSinceUpdate < 1)
                                        ✓ Fresh
                                    @elseif($hoursSinceUpdate < 6)  
                                        ◐ Recent
                                    @else
                                        ○ Older
                                    @endif
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@else
    {{-- No Results State --}}
    <x-ui.empty-state id="no-results"
                      title="No tickets found"
                      description="We couldn't find any sports event tickets matching your current filters. Try adjusting your search criteria."
                      illustration="search-empty.svg">
        <div class="space-y-2 text-sm text-gray-500">
            <ul class="list-disc list-inside space-y-1 max-w-md mx-auto">
                <li>Remove some filters to see more results</li>
                <li>Try different keywords or event names</li>
                <li>Expand your date range</li>
                <li>Check different cities or venues</li>
            </ul>
        </div>
        <button onclick="ticketFilters.clearAllFilters()" 
                class="mt-6 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
            Clear All Filters
        </button>
    </x-ui.empty-state>
@endif

{{-- Loading Skeleton Template (hidden by default) --}}
<div id="ticket-skeleton" class="hidden grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @for($i = 0; $i < 8; $i++)
        <div class="ticket-card bg-white rounded-lg shadow-sm border border-gray-200 animate-pulse">
            <div class="h-48 bg-gray-300 rounded-t-lg"></div>
            <div class="p-4">
                <div class="h-4 bg-gray-300 rounded mb-2"></div>
                <div class="h-3 bg-gray-200 rounded mb-4 w-3/4"></div>
                <div class="space-y-2 mb-4">
                    <div class="h-3 bg-gray-200 rounded w-full"></div>
                    <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="h-6 bg-gray-300 rounded w-16"></div>
                    <div class="h-8 bg-gray-300 rounded w-20"></div>
                </div>
            </div>
        </div>
    @endfor
</div>

<script>
// Ticket card interaction enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Track ticket interactions for analytics
    document.querySelectorAll('[data-track]').forEach(element => {
        element.addEventListener('click', function() {
            const trackingData = {
                action: this.dataset.track,
                ticket_id: this.closest('.ticket-card')?.dataset.ticketId,
                timestamp: new Date().toISOString()
            };
            
            // Send tracking data if analytics is enabled
            if (window.hdTicketsConfig && window.hdTicketsConfig.enableAnalytics) {
                // Implementation depends on your analytics setup
                console.log('Track:', trackingData);
            }
        });
    });
    
    // Enhance external links with confirmation for expensive tickets
    document.querySelectorAll('a[data-track="buy_ticket"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const ticketCard = this.closest('.ticket-card');
            const priceElement = ticketCard.querySelector('.ticket-price');
            
            if (priceElement) {
                const priceText = priceElement.textContent.replace(/[^0-9.]/g, '');
                const price = parseFloat(priceText);
                
                // Confirm for tickets over $500
                if (price > 500) {
                    if (!confirm(`This ticket costs $${price.toFixed(2)}. Are you sure you want to proceed to the external site?`)) {
                        e.preventDefault();
                    }
                }
            }
        });
    });
});
</script>
