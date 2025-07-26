@extends('layouts.app')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $ticket->title }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">{{ $ticket->platform_display_name }} • {{ $ticket->venue ?? 'Venue TBD' }}</p>
    </div>
    <div class="flex items-center space-x-4">
        @if($ticket->is_high_demand)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                🔥 High Demand
            </span>
        @endif
        @if($ticket->ticket_url)
            <a href="{{ $ticket->ticket_url }}" target="_blank" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M8 11v6a2 2 0 002 2h4a2 2 0 002-2v-6m-6 0h6"></path>
                </svg>
                Buy on {{ $ticket->platform_display_name }}
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="py-6 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Ticket Info Card -->
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl mb-8">
            <div class="px-6 py-6 sm:px-8">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ $ticket->title }}</h3>
                        <div class="flex items-center text-gray-600 mb-4">
                            @if($ticket->sport)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                    {{ ucfirst($ticket->sport) }}
                                </span>
                            @endif
                            @if($ticket->event_type)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-2">
                                    {{ ucfirst($ticket->event_type) }}
                                </span>
                            @endif
                            @if($ticket->team)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $ticket->team }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @if($ticket->is_available)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                Available
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Sold Out
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Event Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">Event Date</div>
                        <div class="text-lg font-semibold text-gray-900">
                            @if($ticket->event_date)
                                {{ $ticket->event_date->format('l, M j, Y') }}
                                <div class="text-sm text-gray-600">{{ $ticket->event_date->format('g:i A') }}</div>
                            @else
                                <span class="text-gray-400">Date TBD</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">Venue</div>
                        <div class="text-lg font-semibold text-gray-900">
                            {{ $ticket->venue ?? 'Venue TBD' }}
                            @if($ticket->location)
                                <div class="text-sm text-gray-600">{{ $ticket->location }}</div>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500 mb-1">Platform</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $ticket->platform_display_name }}</div>
                        @if($ticket->external_id)
                            <div class="text-sm text-gray-600">ID: {{ $ticket->external_id }}</div>
                        @endif
                    </div>
                </div>

                <!-- Price Information -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Pricing Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @if($ticket->min_price)
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Starting from</div>
                                <div class="text-2xl font-bold text-green-600">{{ $ticket->currency }} {{ number_format($ticket->min_price, 2) }}</div>
                            </div>
                        @endif
                        
                        @if($ticket->max_price && $ticket->max_price != $ticket->min_price)
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Up to</div>
                                <div class="text-2xl font-bold text-red-600">{{ $ticket->currency }} {{ number_format($ticket->max_price, 2) }}</div>
                            </div>
                        @endif

                        @if($ticket->min_price && $ticket->max_price)
                            <div class="text-center">
                                <div class="text-sm font-medium text-gray-500">Average</div>
                                <div class="text-2xl font-bold text-blue-600">{{ $ticket->currency }} {{ number_format(($ticket->min_price + $ticket->max_price) / 2, 2) }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Details -->
                @if($ticket->availability || $ticket->search_keyword || $ticket->metadata)
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h4>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($ticket->availability)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Availability Details</dt>
                                    <dd class="text-sm text-gray-900">{{ $ticket->availability }}</dd>
                                </div>
                            @endif
                            
                            @if($ticket->search_keyword)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Search Keywords</dt>
                                    <dd class="text-sm text-gray-900">{{ $ticket->search_keyword }}</dd>
                                </div>
                            @endif
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $ticket->scraped_at ? $ticket->scraped_at->diffForHumans() : 'Unknown' }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Freshness</dt>
                                <dd class="text-sm">
                                    @if($ticket->is_recent)
                                        <span class="text-green-600 font-medium">Fresh (within 24h)</span>
                                    @else
                                        <span class="text-yellow-600 font-medium">Older data</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <a href="{{ route('tickets.scraping.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Tickets
            </a>
            
            <div class="flex items-center space-x-3">
                <button onclick="addToQueue()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add to Purchase Queue
                </button>
                
                @if($ticket->ticket_url)
                    <a href="{{ $ticket->ticket_url }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        Buy Now on {{ $ticket->platform_display_name }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function addToQueue() {
    fetch('{{ route("purchase-decisions.add-to-queue", $ticket) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ticket added to purchase queue!');
            window.location.href = '{{ route("purchase-decisions.index") }}';
        } else {
            alert('Error adding ticket to queue: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adding ticket to queue');
    });
}
</script>
@endsection
