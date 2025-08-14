@props([
    'tickets' => [],
    'showPagination' => true,
    'enableSwipe' => true,
    'cardLayout' => 'compact', // compact, detailed, minimal
    'loadMoreUrl' => null,
    'infiniteScroll' => false
])

@php
    $containerId = 'ticket-cards-' . uniqid();
    $cardsPerPage = 12;
@endphp

<div class="swipeable-ticket-cards" data-container-id="{{ $containerId }}">
    <!-- Loading State -->
    <div class="loading-skeleton hidden" data-loading-skeleton>
        @for($i = 0; $i < 6; $i++)
            <div class="ticket-card-skeleton animate-pulse bg-gray-200 rounded-xl p-4 mb-4">
                <div class="flex justify-between items-start mb-3">
                    <div class="h-4 bg-gray-300 rounded w-1/2"></div>
                    <div class="h-6 bg-gray-300 rounded-full w-16"></div>
                </div>
                <div class="h-3 bg-gray-300 rounded w-3/4 mb-2"></div>
                <div class="h-3 bg-gray-300 rounded w-1/2 mb-3"></div>
                <div class="flex justify-between">
                    <div class="h-4 bg-gray-300 rounded w-20"></div>
                    <div class="h-8 bg-gray-300 rounded w-24"></div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Tickets Grid -->
    <div 
        class="tickets-grid {{ $enableSwipe ? 'swipe-enabled' : '' }}" 
        data-tickets-grid="{{ $containerId }}"
        data-card-layout="{{ $cardLayout }}"
        @if($enableSwipe) 
            data-swipe-container="true" 
            data-swipe-threshold="50"
        @endif
    >
        @forelse($tickets as $index => $ticket)
            <div 
                class="ticket-card-wrapper relative {{ $cardLayout === 'minimal' ? 'mb-2' : 'mb-4' }}"
                data-ticket-id="{{ $ticket->id }}"
                data-card-index="{{ $index }}"
                @if($enableSwipe)
                    data-swipe-item="true"
                    data-swipe-actions='["favorite", "purchase", "share"]'
                @endif
            >
                <!-- Swipe Action Indicators -->
                @if($enableSwipe)
                    <div class="swipe-actions absolute inset-0 flex items-center justify-between px-4 opacity-0 pointer-events-none z-10">
                        <!-- Left Swipe Action (Favorite) -->
                        <div class="swipe-action-left flex items-center justify-center w-16 h-16 bg-yellow-500 text-white rounded-full transform scale-75">
                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        </div>
                        
                        <!-- Right Swipe Actions (Purchase/Share) -->
                        <div class="swipe-actions-right flex space-x-2">
                            <div class="swipe-action-purchase flex items-center justify-center w-16 h-16 bg-green-500 text-white rounded-full transform scale-75">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17M17 13a2 2 0 100 4 2 2 0 000-4zm-8 4a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <div class="swipe-action-share flex items-center justify-center w-16 h-16 bg-blue-500 text-white rounded-full transform scale-75">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Ticket Card -->
                <div class="ticket-card bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md transition-all duration-300 transform hover:-translate-y-1 {{ $cardLayout === 'minimal' ? 'p-3' : 'p-4' }}">
                    @if($cardLayout === 'detailed')
                        <!-- Detailed Card Layout -->
                        <div class="flex flex-col h-full">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <h3 class="font-bold text-lg text-gray-900 line-clamp-2 mb-1">
                                        {{ $ticket->event_name ?? $ticket->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $ticket->venue ?? $ticket->location }}
                                    </p>
                                </div>
                                <div class="ml-3">
                                    @php
                                        $availability = $ticket->availability ?? 'available';
                                        $availabilityColors = [
                                            'available' => 'bg-green-100 text-green-800',
                                            'limited' => 'bg-yellow-100 text-yellow-800', 
                                            'sold_out' => 'bg-red-100 text-red-800'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $availabilityColors[$availability] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst(str_replace('_', ' ', $availability)) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Event Details -->
                            <div class="space-y-2 mb-4 flex-1">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ \Carbon\Carbon::parse($ticket->event_date ?? $ticket->date)->format('M j, Y g:i A') }}
                                </div>
                                
                                @if(isset($ticket->category))
                                    <div class="flex items-center text-sm text-gray-600">
                                        <span class="text-lg mr-2">
                                            @php
                                                $categoryEmojis = [
                                                    'NFL' => '🏈', 'NBA' => '🏀', 'MLB' => '⚾', 'NHL' => '🏒',
                                                    'Premier League' => '⚽', 'Tennis' => '🎾', 'F1' => '🏎️'
                                                ];
                                            @endphp
                                            {{ $categoryEmojis[$ticket->category] ?? '🏆' }}
                                        </span>
                                        {{ $ticket->category }}
                                    </div>
                                @endif

                                @if(isset($ticket->section) || isset($ticket->row))
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                                        </svg>
                                        Section {{ $ticket->section ?? 'TBD' }}
                                        @if(isset($ticket->row)), Row {{ $ticket->row }}@endif
                                    </div>
                                @endif
                            </div>

                            <!-- Price and Actions -->
                            <div class="flex items-center justify-between">
                                <div class="price-info">
                                    <div class="flex items-baseline">
                                        <span class="text-2xl font-bold text-green-600">
                                            ${{ number_format($ticket->price ?? $ticket->current_price, 2) }}
                                        </span>
                                        @if(isset($ticket->original_price) && $ticket->original_price > $ticket->current_price)
                                            <span class="ml-2 text-sm text-gray-500 line-through">
                                                ${{ number_format($ticket->original_price, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500">per ticket</div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button class="favorite-btn p-2 rounded-full hover:bg-gray-100 transition-colors" data-ticket-id="{{ $ticket->id }}">
                                        <svg class="w-5 h-5 text-gray-400 hover:text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                        </svg>
                                    </button>
                                    @if($availability !== 'sold_out')
                                        <button class="buy-btn bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm">
                                            Buy Now
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @elseif($cardLayout === 'minimal')
                        <!-- Minimal Card Layout -->
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 truncate">{{ $ticket->event_name ?? $ticket->title }}</h4>
                                <p class="text-xs text-gray-500 truncate">{{ $ticket->venue ?? $ticket->location }}</p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($ticket->event_date ?? $ticket->date)->format('M j') }}</p>
                            </div>
                            <div class="ml-3 text-right">
                                <div class="text-lg font-bold text-green-600">${{ number_format($ticket->price ?? $ticket->current_price, 0) }}</div>
                                <button class="text-xs text-blue-600 hover:text-blue-800">View</button>
                            </div>
                        </div>

                    @else
                        <!-- Compact Card Layout (Default) -->
                        <div class="space-y-3">
                            <!-- Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-base text-gray-900 line-clamp-2">
                                        {{ $ticket->event_name ?? $ticket->title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 truncate">{{ $ticket->venue ?? $ticket->location }}</p>
                                </div>
                                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $availabilityColors[$availability] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $availability)) }}
                                </span>
                            </div>

                            <!-- Event Info -->
                            <div class="text-sm text-gray-600 flex items-center">
                                <svg class="w-4 h-4 mr-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ \Carbon\Carbon::parse($ticket->event_date ?? $ticket->date)->format('M j, Y g:i A') }}
                            </div>

                            <!-- Price and Action -->
                            <div class="flex items-center justify-between">
                                <div class="price-info">
                                    <span class="text-xl font-bold text-green-600">
                                        ${{ number_format($ticket->price ?? $ticket->current_price, 2) }}
                                    </span>
                                    @if(isset($ticket->original_price) && $ticket->original_price > $ticket->current_price)
                                        <span class="ml-1 text-xs text-gray-500 line-through">
                                            ${{ number_format($ticket->original_price, 2) }}
                                        </span>
                                    @endif
                                </div>
                                
                                @if($availability !== 'sold_out')
                                    <button class="buy-btn bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg font-medium transition-colors text-sm">
                                        Buy
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Quick Actions (Overlay) -->
                <div class="quick-actions absolute top-2 right-2 flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button class="share-btn p-1.5 bg-white rounded-full shadow-md hover:bg-gray-50" title="Share">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="col-span-full text-center py-12">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets found</h3>
                <p class="text-gray-500 max-w-sm mx-auto">There are no sports event tickets matching your criteria. Try adjusting your filters or check back later.</p>
            </div>
        @endforelse
    </div>

    <!-- Load More/Infinite Scroll -->
    @if($showPagination && count($tickets) > 0)
        <div class="load-more-container mt-6 text-center">
            @if($infiniteScroll && $loadMoreUrl)
                <div class="infinite-scroll-trigger" data-load-more-url="{{ $loadMoreUrl }}">
                    <div class="load-more-spinner hidden">
                        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 mt-2">Loading more tickets...</p>
                    </div>
                </div>
            @else
                <button class="load-more-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors">
                    Load More Tickets
                </button>
            @endif
        </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ now()->timestamp }}">
<style>
/* Swipeable Ticket Cards Styles */
.swipeable-ticket-cards {
    position: relative;
}

.tickets-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

/* Responsive grid */
@media (min-width: 640px) {
    .tickets-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .tickets-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1280px) {
    .tickets-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Ticket Card Wrapper */
.ticket-card-wrapper {
    position: relative;
    transform: translateX(0);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ticket-card-wrapper.group:hover .quick-actions {
    opacity: 1;
}

/* Swipe functionality */
.ticket-card-wrapper[data-swipe-item="true"] {
    cursor: grab;
    user-select: none;
}

.ticket-card-wrapper[data-swipe-item="true"]:active {
    cursor: grabbing;
}

.ticket-card-wrapper.swiping {
    transition: none;
    z-index: 10;
}

/* Swipe actions */
.swipe-actions {
    transition: opacity 0.3s ease;
}

.ticket-card-wrapper.swipe-left .swipe-actions,
.ticket-card-wrapper.swipe-right .swipe-actions {
    opacity: 1;
}

.ticket-card-wrapper.swipe-left .swipe-action-left,
.ticket-card-wrapper.swipe-right .swipe-actions-right > div {
    transform: scale(1);
}

/* Card hover effects */
.ticket-card {
    position: relative;
    overflow: hidden;
}

.ticket-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.ticket-card:hover::before {
    left: 100%;
}

/* Touch feedback */
.ticket-card:active {
    transform: scale(0.98);
}

/* Line clamping for text overflow */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Loading skeleton animation */
.ticket-card-skeleton {
    border-radius: 0.75rem;
    overflow: hidden;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

.loading-skeleton .ticket-card-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

/* Button animations */
.buy-btn, .favorite-btn, .share-btn {
    transform: scale(1);
    transition: all 0.2s ease;
}

.buy-btn:active, .favorite-btn:active, .share-btn:active {
    transform: scale(0.95);
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .ticket-card-wrapper,
    .ticket-card,
    .buy-btn,
    .favorite-btn,
    .share-btn {
        transition: none;
    }
    
    .loading-skeleton .ticket-card-skeleton {
        animation: none;
        background: #f0f0f0;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .ticket-card {
        border-width: 2px;
    }
    
    .buy-btn {
        border: 2px solid transparent;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .ticket-card {
        background: #1f2937;
        border-color: #374151;
    }
    
    .ticket-card-skeleton {
        background: #374151;
    }
    
    .loading-skeleton .ticket-card-skeleton {
        background: linear-gradient(90deg, #374151 25%, #4b5563 50%, #374151 75%);
    }
}

/* Mobile-specific optimizations */
@media (max-width: 639px) {
    .tickets-grid {
        gap: 0.75rem;
    }
    
    .ticket-card {
        padding: 0.75rem;
    }
    
    /* Larger touch targets on mobile */
    .buy-btn, .favorite-btn, .share-btn {
        min-height: 44px;
        min-width: 44px;
    }
}

/* Infinite scroll loading */
.infinite-scroll-trigger {
    height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const containerId = '{{ $containerId }}';
    const container = document.querySelector(`[data-container-id="${containerId}"]`);
    const ticketsGrid = container.querySelector(`[data-tickets-grid="${containerId}"]`);
    const enableSwipe = {{ $enableSwipe ? 'true' : 'false' }};
    const infiniteScroll = {{ $infiniteScroll ? 'true' : 'false' }};

    if (!container || !ticketsGrid) return;

    // Touch/Swipe handling for mobile
    if (enableSwipe && window.responsiveUtils?.isMobile()) {
        initializeSwipeGestures();
    }

    // Infinite scroll functionality
    if (infiniteScroll) {
        initializeInfiniteScroll();
    }

    // Card interactions
    initializeCardInteractions();

    function initializeSwipeGestures() {
        const swipeItems = ticketsGrid.querySelectorAll('[data-swipe-item="true"]');
        
        swipeItems.forEach(item => {
            let startX = 0;
            let startY = 0;
            let currentX = 0;
            let isDragging = false;
            let swipeDirection = null;
            
            const swipeActions = item.querySelector('.swipe-actions');
            const swipeThreshold = parseInt(item.getAttribute('data-swipe-threshold') || '50');
            
            // Touch start
            item.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isDragging = false;
                item.classList.add('swiping');
            }, { passive: true });
            
            // Touch move
            item.addEventListener('touchmove', function(e) {
                if (!startX) return;
                
                currentX = e.touches[0].clientX - startX;
                const currentY = e.touches[0].clientY - startY;
                
                // Determine if this is a horizontal swipe
                if (Math.abs(currentX) > Math.abs(currentY) && Math.abs(currentX) > 10) {
                    isDragging = true;
                    e.preventDefault();
                    
                    // Apply transform and show actions
                    const translateX = Math.max(-150, Math.min(150, currentX * 0.7));
                    item.style.transform = `translateX(${translateX}px)`;
                    
                    // Show appropriate swipe actions
                    if (Math.abs(translateX) > swipeThreshold) {
                        swipeActions.style.opacity = '1';
                        
                        if (translateX > 0) {
                            item.classList.add('swipe-left');
                            item.classList.remove('swipe-right');
                            swipeDirection = 'left';
                        } else {
                            item.classList.add('swipe-right');
                            item.classList.remove('swipe-left');
                            swipeDirection = 'right';
                        }
                    } else {
                        item.classList.remove('swipe-left', 'swipe-right');
                        swipeDirection = null;
                    }
                }
            }, { passive: false });
            
            // Touch end
            item.addEventListener('touchend', function(e) {
                if (!isDragging) {
                    resetSwipeState();
                    return;
                }
                
                const swipeDistance = Math.abs(currentX);
                
                if (swipeDistance > swipeThreshold && swipeDirection) {
                    // Execute swipe action
                    executeSwipeAction(item, swipeDirection);
                    
                    // Animate card away
                    const exitDirection = swipeDirection === 'left' ? '100%' : '-100%';
                    item.style.transform = `translateX(${exitDirection})`;
                    item.style.opacity = '0.5';
                    
                    setTimeout(() => {
                        resetSwipeState();
                    }, 300);
                } else {
                    // Snap back
                    resetSwipeState();
                }
                
                function resetSwipeState() {
                    item.classList.remove('swiping', 'swipe-left', 'swipe-right');
                    item.style.transform = '';
                    item.style.opacity = '';
                    swipeActions.style.opacity = '0';
                    startX = 0;
                    currentX = 0;
                    isDragging = false;
                    swipeDirection = null;
                }
            }, { passive: true });
        });
    }

    function executeSwipeAction(item, direction) {
        const ticketId = item.getAttribute('data-ticket-id');
        
        if (direction === 'left') {
            // Left swipe - Add to favorites
            toggleFavorite(ticketId);
            showSwipeActionFeedback('Added to favorites!', 'success');
        } else if (direction === 'right') {
            // Right swipe - Could be purchase or share based on distance
            const actionButtons = item.querySelectorAll('.swipe-actions-right > div');
            if (actionButtons.length > 0) {
                // For now, default to purchase action
                initiatePurchase(ticketId);
                showSwipeActionFeedback('Added to cart!', 'success');
            }
        }
    }

    function showSwipeActionFeedback(message, type) {
        const feedback = document.createElement('div');
        feedback.className = `fixed top-4 left-1/2 transform -translate-x-1/2 z-50 px-4 py-2 rounded-lg text-white font-medium ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} animate-fade-in-scale`;
        feedback.textContent = message;
        
        document.body.appendChild(feedback);
        
        setTimeout(() => {
            feedback.remove();
        }, 3000);
    }

    function initializeInfiniteScroll() {
        const loadMoreTrigger = container.querySelector('.infinite-scroll-trigger');
        const spinner = container.querySelector('.load-more-spinner');
        
        if (!loadMoreTrigger || !spinner) return;
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    loadMoreTickets();
                }
            });
        }, {
            rootMargin: '100px'
        });
        
        observer.observe(loadMoreTrigger);
        
        async function loadMoreTickets() {
            const loadMoreUrl = loadMoreTrigger.getAttribute('data-load-more-url');
            if (!loadMoreUrl) return;
            
            spinner.classList.remove('hidden');
            
            try {
                const response = await fetch(loadMoreUrl);
                const data = await response.json();
                
                if (data.html) {
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = data.html;
                    
                    // Append new tickets
                    while (tempDiv.firstChild) {
                        ticketsGrid.appendChild(tempDiv.firstChild);
                    }
                    
                    // Update load more URL for next batch
                    if (data.nextUrl) {
                        loadMoreTrigger.setAttribute('data-load-more-url', data.nextUrl);
                    } else {
                        observer.unobserve(loadMoreTrigger);
                        loadMoreTrigger.style.display = 'none';
                    }
                }
            } catch (error) {
                console.error('Error loading more tickets:', error);
                showSwipeActionFeedback('Error loading tickets', 'error');
            } finally {
                spinner.classList.add('hidden');
            }
        }
    }

    function initializeCardInteractions() {
        // Favorite buttons
        container.addEventListener('click', function(e) {
            if (e.target.closest('.favorite-btn')) {
                e.preventDefault();
                const btn = e.target.closest('.favorite-btn');
                const ticketId = btn.getAttribute('data-ticket-id');
                toggleFavorite(ticketId);
            }
            
            if (e.target.closest('.buy-btn')) {
                e.preventDefault();
                const wrapper = e.target.closest('.ticket-card-wrapper');
                const ticketId = wrapper.getAttribute('data-ticket-id');
                initiateQuickPurchase(ticketId, e.target);
            }
            
            if (e.target.closest('.share-btn')) {
                e.preventDefault();
                const wrapper = e.target.closest('.ticket-card-wrapper');
                const ticketId = wrapper.getAttribute('data-ticket-id');
                shareTicket(ticketId);
            }
        });
    }

    function toggleFavorite(ticketId) {
        // Implementation for favorite toggle
        console.log('Toggle favorite for ticket:', ticketId);
        // You would make an AJAX call here
    }

    function initiateQuickPurchase(ticketId, button) {
        const originalText = button.textContent;
        button.textContent = 'Adding...';
        button.disabled = true;
        
        // Simulate purchase process
        setTimeout(() => {
            button.textContent = 'Added!';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600');
            
            setTimeout(() => {
                button.textContent = originalText;
                button.disabled = false;
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.classList.remove('bg-green-600');
            }, 2000);
        }, 1000);
    }

    function shareTicket(ticketId) {
        if (navigator.share) {
            navigator.share({
                title: 'Sports Ticket',
                text: 'Check out this amazing sports event ticket!',
                url: window.location.href
            });
        } else {
            // Fallback to copy link
            navigator.clipboard.writeText(window.location.href).then(() => {
                showSwipeActionFeedback('Link copied!', 'success');
            });
        }
    }

    // Handle visibility changes to pause/resume animations
    document.addEventListener('visibilitychange', function() {
        const cards = ticketsGrid.querySelectorAll('.ticket-card');
        if (document.hidden) {
            cards.forEach(card => card.style.animationPlayState = 'paused');
        } else {
            cards.forEach(card => card.style.animationPlayState = 'running');
        }
    });
});
</script>
@endpush
