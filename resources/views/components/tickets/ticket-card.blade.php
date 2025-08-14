{{--
/**
 * Ticket Card Component
 * 
 * Reusable component for displaying sports event ticket information
 * 
 * @prop ticket_id: string - Unique ticket identifier (format: TKT-XXXXXX)
 * @prop event_name: string - Name of the sports event
 * @prop venue: string - Event venue name
 * @prop date: string - Event date (ISO format)
 * @prop price: numeric - Ticket price
 * @prop availability_status: string - Ticket availability (available, limited, sold_out)
 * @prop platform_source: string - Ticket source platform
 * @prop sport_category: string - Sport category (football, rugby, cricket, tennis, other)
 * @prop section: string - Seat section (optional)
 * @prop row: string - Seat row (optional)
 * @prop seats: array - Seat numbers (optional)
 * @prop show_actions: boolean - Whether to show action buttons (default: true)
 * @prop user_role: string - Current user role for permission checks
 * 
 * @event ticket-selected: Emitted when ticket is selected
 * @event ticket-favorited: Emitted when ticket is favorited
 * @event price-alert-set: Emitted when price alert is set
 * 
 * @category sports-tickets
 * @lazy false
 */
--}}

@props([
    'ticketId' => null,
    'eventName' => 'Unknown Event',
    'venue' => 'Unknown Venue',
    'date' => null,
    'price' => 0,
    'availabilityStatus' => 'unknown',
    'platformSource' => 'unknown',
    'sportCategory' => 'other',
    'section' => null,
    'row' => null,
    'seats' => [],
    'showActions' => true,
    'userRole' => 'guest',
    'imageUrl' => null,
    'description' => null,
    'originalPrice' => null,
    'discountPercentage' => null,
    'isFavorited' => false,
    'hasAlert' => false,
    'class' => ''
])

@php
    // Format date for display
    $formattedDate = $date ? \Carbon\Carbon::parse($date)->format('M j, Y') : 'TBD';
    $formattedTime = $date ? \Carbon\Carbon::parse($date)->format('g:i A') : '';
    
    // Status styling
    $statusClasses = [
        'available' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'limited' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        'sold_out' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'on_hold' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    ];
    
    // Platform styling
    $platformClasses = [
        'ticketmaster' => 'text-blue-600',
        'stubhub' => 'text-orange-600',
        'seatgeek' => 'text-green-600',
        'official' => 'text-purple-600'
    ];
    
    // Sport category icons
    $sportIcons = [
        'football' => 'fas fa-football-ball',
        'rugby' => 'fas fa-football-ball',
        'cricket' => 'fas fa-baseball-ball',
        'tennis' => 'fas fa-table-tennis',
        'other' => 'fas fa-ticket-alt'
    ];
    
    $statusClass = $statusClasses[$availabilityStatus] ?? $statusClasses['available'];
    $platformClass = $platformClasses[$platformSource] ?? 'text-gray-600';
    $sportIcon = $sportIcons[$sportCategory] ?? $sportIcons['other'];
    
    // Calculate discount if applicable
    $showDiscount = $originalPrice && $originalPrice > $price;
    $calculatedDiscount = $showDiscount ? round((($originalPrice - $price) / $originalPrice) * 100) : 0;
@endphp

<div class="hd-card hd-ticket-card hd-card--interactive {{ $class }}"
     x-data="ticketCard()" 
     data-ticket-id="{{ $ticketId }}"
     data-event-name="{{ $eventName }}"
     data-price="{{ $price }}"
     data-availability="{{ $availabilityStatus }}">
     
    {{-- Card Header with Image --}}
    @if($imageUrl)
        <div class="relative h-48 bg-gradient-to-br from-blue-500 to-purple-600 overflow-hidden">
            <img src="{{ $imageUrl }}" 
                 alt="{{ $eventName }}"
                 class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
                 loading="lazy">
            
            {{-- Overlay with sport category --}}
            <div class="absolute top-3 left-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-black/50 text-white backdrop-blur-sm">
                    <i class="{{ $sportIcon }} mr-1"></i>
                    {{ ucfirst($sportCategory) }}
                </span>
            </div>
            
            {{-- Favorite button --}}
            @if(auth()->check())
                <button type="button" 
                        class="absolute top-3 right-3 p-2 rounded-full bg-black/50 text-white hover:bg-black/70 transition-colors backdrop-blur-sm"
                        @click="toggleFavorite"
                        :class="{ 'text-red-400': isFavorited }">
                    <i class="fas fa-heart text-sm"></i>
                </button>
            @endif
        </div>
    @endif

    <div class="p-6">
        {{-- Event Title --}}
        <div class="mb-4">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 line-clamp-2">
                {{ $eventName }}
            </h3>
            
            {{-- Venue and Date --}}
            <div class="flex items-center text-gray-600 dark:text-gray-300 mb-2">
                <i class="fas fa-map-marker-alt mr-2 text-sm"></i>
                <span class="mr-4 truncate">{{ $venue }}</span>
                
                @if($formattedDate !== 'TBD')
                    <i class="fas fa-calendar mr-2 text-sm"></i>
                    <span>{{ $formattedDate }}</span>
                    @if($formattedTime)
                        <span class="ml-2">{{ $formattedTime }}</span>
                    @endif
                @endif
            </div>
            
            {{-- Seat Information --}}
            @if($section || $row || !empty($seats))
                <div class="flex items-center text-gray-600 dark:text-gray-300 text-sm">
                    <i class="fas fa-chair mr-2"></i>
                    @if($section)
                        <span class="mr-2">Section {{ $section }}</span>
                    @endif
                    @if($row)
                        <span class="mr-2">Row {{ $row }}</span>
                    @endif
                    @if(!empty($seats))
                        <span>Seats: {{ implode(', ', $seats) }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Price and Status Section --}}
        <div class="flex items-center justify-between mb-4">
            <div class="price-section">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">
                        £{{ number_format($price, 2) }}
                    </span>
                    
                    @if($showDiscount)
                        <span class="ml-2 text-sm text-gray-500 line-through">
                            £{{ number_format($originalPrice, 2) }}
                        </span>
                        <span class="ml-2 text-sm font-medium text-green-600 dark:text-green-400">
                            {{ $calculatedDiscount }}% off
                        </span>
                    @endif
                </div>
                
                {{-- Platform Source --}}
                <div class="flex items-center mt-1">
                    <span class="text-sm text-gray-500 mr-2">via</span>
                    <span class="text-sm font-medium {{ $platformClass }}">
                        {{ ucfirst($platformSource) }}
                    </span>
                </div>
            </div>
            
            {{-- Availability Status --}}
            <div class="flex flex-col items-end">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                    @if($availabilityStatus === 'available')
                        <i class="fas fa-check-circle mr-1"></i>
                        Available
                    @elseif($availabilityStatus === 'limited')
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Limited
                    @elseif($availabilityStatus === 'sold_out')
                        <i class="fas fa-times-circle mr-1"></i>
                        Sold Out
                    @else
                        <i class="fas fa-clock mr-1"></i>
                        On Hold
                    @endif
                </span>
                
                {{-- Alert indicators --}}
                <div class="flex items-center mt-2 space-x-1">
                    @if($hasAlert)
                        <span class="inline-flex items-center text-xs text-amber-600 dark:text-amber-400">
                            <i class="fas fa-bell mr-1"></i>
                            Alert Set
                        </span>
                    @endif
                    
                    @if($isFavorited)
                        <span class="inline-flex items-center text-xs text-red-600 dark:text-red-400">
                            <i class="fas fa-heart mr-1"></i>
                            Favorited
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($description)
            <div class="mb-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                    {{ $description }}
                </p>
            </div>
        @endif

        {{-- Action Buttons --}}
        @if($showActions && $availabilityStatus !== 'sold_out')
            <div class="flex items-center space-x-3">
                {{-- View Details Button --}}
                <button type="button" 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                        @click="viewDetails">
                    <i class="fas fa-eye mr-2"></i>
                    View Details
                </button>
                
                {{-- Quick Actions --}}
                <div class="flex space-x-2">
                    {{-- Price Alert Button --}}
                    @if(auth()->check())
                        <button type="button" 
                                class="p-2 border border-gray-300 dark:border-gray-600 hover:border-amber-500 text-gray-600 dark:text-gray-300 hover:text-amber-600 rounded-lg transition-colors duration-200"
                                @click="setPriceAlert"
                                title="Set Price Alert"
                                :class="{ 'border-amber-500 text-amber-600': hasAlert }">
                            <i class="fas fa-bell text-sm"></i>
                        </button>
                    @endif
                    
                    {{-- Share Button --}}
                    <button type="button" 
                            class="p-2 border border-gray-300 dark:border-gray-600 hover:border-blue-500 text-gray-600 dark:text-gray-300 hover:text-blue-600 rounded-lg transition-colors duration-200"
                            @click="shareTicket"
                            title="Share Ticket">
                        <i class="fas fa-share-alt text-sm"></i>
                    </button>
                </div>
            </div>
        @elseif($availabilityStatus === 'sold_out')
            <div class="text-center">
                <span class="text-gray-500 dark:text-gray-400 font-medium">
                    This ticket is currently sold out
                </span>
                
                @if(auth()->check())
                    <button type="button" 
                            class="block w-full mt-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                            @click="setAvailabilityAlert">
                        <i class="fas fa-bell mr-2"></i>
                        Notify When Available
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>

{{-- Alpine.js Component Logic --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('ticketCard', () => ({
            isFavorited: @json($isFavorited),
            hasAlert: @json($hasAlert),
            
            init() {
                // Initialize component
                this.$el.addEventListener('mouseenter', () => {
                    this.$el.classList.add('scale-105');
                });
                
                this.$el.addEventListener('mouseleave', () => {
                    this.$el.classList.remove('scale-105');
                });
            },
            
            viewDetails() {
                this.$dispatch('ticket-selected', {
                    ticketId: '{{ $ticketId }}',
                    eventName: '{{ $eventName }}',
                    price: {{ $price }},
                    availability: '{{ $availabilityStatus }}'
                });
                
                // Navigate to ticket details
                window.location.href = `/tickets/${encodeURIComponent('{{ $ticketId }}')}`;
            },
            
            toggleFavorite() {
                this.isFavorited = !this.isFavorited;
                
                this.$dispatch('ticket-favorited', {
                    ticketId: '{{ $ticketId }}',
                    favorited: this.isFavorited
                });
                
                // API call to update favorite status
                fetch(`/api/tickets/${encodeURIComponent('{{ $ticketId }}')}/favorite`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ favorited: this.isFavorited })
                }).catch(error => {
                    console.error('Failed to update favorite status:', error);
                    // Revert on error
                    this.isFavorited = !this.isFavorited;
                });
            },
            
            setPriceAlert() {
                this.hasAlert = !this.hasAlert;
                
                this.$dispatch('price-alert-set', {
                    ticketId: '{{ $ticketId }}',
                    alertActive: this.hasAlert,
                    targetPrice: {{ $price }}
                });
                
                // API call to set price alert
                fetch(`/api/tickets/${encodeURIComponent('{{ $ticketId }}')}/price-alert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ 
                        active: this.hasAlert,
                        targetPrice: {{ $price }}
                    })
                }).catch(error => {
                    console.error('Failed to set price alert:', error);
                    this.hasAlert = !this.hasAlert;
                });
            },
            
            setAvailabilityAlert() {
                this.$dispatch('availability-alert-set', {
                    ticketId: '{{ $ticketId }}',
                    eventName: '{{ $eventName }}'
                });
                
                // API call to set availability alert
                fetch(`/api/tickets/${encodeURIComponent('{{ $ticketId }}')}/availability-alert`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                }).then(response => {
                    if (response.ok) {
                        // Show success message
                        this.$dispatch('show-notification', {
                            type: 'success',
                            message: 'You\'ll be notified when this ticket becomes available'
                        });
                    }
                });
            },
            
            shareTicket() {
                const shareData = {
                    title: '{{ $eventName }}',
                    text: `Check out this ticket for {{ $eventName }} at {{ $venue }}`,
                    url: window.location.href + `/tickets/${encodeURIComponent('{{ $ticketId }}')}`
                };
                
                if (navigator.share) {
                    navigator.share(shareData);
                } else {
                    // Fallback to clipboard
                    navigator.clipboard.writeText(shareData.url).then(() => {
                        this.$dispatch('show-notification', {
                            type: 'success',
                            message: 'Link copied to clipboard'
                        });
                    });
                }
            }
        }));
    });
</script>

<style>
    .ticket-card:hover {
        transform: translateY(-2px);
    }
    
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    @media (prefers-reduced-motion: reduce) {
        .ticket-card {
            transition: none;
        }
    }
</style>
