@props([
    'tickets' => [],
    'isLoading' => false,
    'speed' => 50, // pixels per second
    'height' => '60px'
])

<div class="live-ticker-container bg-gradient-to-r from-green-500 to-blue-600 text-white overflow-hidden shadow-lg rounded-lg" 
     style="height: {{ $height }};">
    
    @if($isLoading)
        <!-- Loading State -->
        <div class="flex items-center justify-center h-full">
            <div class="flex space-x-2 animate-pulse">
                <div class="w-3 h-3 bg-white bg-opacity-60 rounded-full animate-bounce"></div>
                <div class="w-3 h-3 bg-white bg-opacity-60 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                <div class="w-3 h-3 bg-white bg-opacity-60 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
            </div>
            <span class="ml-3 text-white text-opacity-90 font-medium">Loading live tickets...</span>
        </div>
    @else
        <!-- Ticker Content -->
        <div class="ticker-wrapper h-full flex items-center">
            <div class="ticker-content whitespace-nowrap animate-scroll-left" 
                 style="animation-duration: {{ count($tickets) * 8 }}s;">
                
                @forelse($tickets as $ticket)
                    <span class="inline-flex items-center mx-8 text-sm font-medium">
                        <!-- Ticket Icon -->
                        <svg class="w-4 h-4 mr-2 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        
                        <!-- Ticket Info -->
                        <span class="font-semibold">{{ $ticket['event'] ?? 'Sports Event' }}</span>
                        <span class="mx-2">•</span>
                        <span class="text-yellow-200">{{ $ticket['venue'] ?? 'Stadium' }}</span>
                        <span class="mx-2">•</span>
                        <span class="font-bold text-yellow-300">${{ number_format($ticket['price'] ?? 0, 2) }}</span>
                        <span class="mx-2">•</span>
                        <span class="text-green-200 text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">
                            {{ $ticket['availability'] ?? 'Available' }}
                        </span>
                    </span>
                @empty
                    <span class="inline-flex items-center text-sm font-medium text-white text-opacity-80">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        No live ticket updates available
                    </span>
                @endforelse
            </div>
        </div>
    @endif
</div>

<style>
@keyframes scroll-left {
    0% {
        transform: translateX(100%);
    }
    100% {
        transform: translateX(-100%);
    }
}

.animate-scroll-left {
    animation: scroll-left linear infinite;
}

.live-ticker-container {
    position: relative;
    background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-blue) 100%);
}

.ticker-wrapper {
    overflow: hidden;
    width: 100%;
}

.ticker-content {
    display: inline-block;
}

/* Pause animation on hover */
.live-ticker-container:hover .ticker-content {
    animation-play-state: paused;
}

/* Real-time indicator */
.live-ticker-container::before {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
    z-index: 10;
}

/* Mobile responsive */
@media (max-width: 640px) {
    .ticker-content {
        font-size: 0.75rem;
    }
}
</style>
