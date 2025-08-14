@props([
    'events' => [],
    'isLoading' => false,
    'title' => 'Price Tracker',
    'updateInterval' => 30000, // milliseconds
    'showChart' => true,
    'maxEntries' => 5
])

<div class="price-tracker-widget bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-500 to-green-600 text-white">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        </div>
        
        <!-- Real-time indicator -->
        <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs font-medium">LIVE</span>
        </div>
    </div>

    @if($isLoading)
        <!-- Loading State -->
        <div class="p-6">
            <div class="space-y-4">
                @for($i = 0; $i < 3; $i++)
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                        <div class="loading-shimmer w-12 h-12 rounded-lg"></div>
                        <div class="flex-1 space-y-2">
                            <div class="loading-shimmer w-3/4 h-4 rounded"></div>
                            <div class="loading-shimmer w-1/2 h-3 rounded"></div>
                        </div>
                        <div class="loading-shimmer w-20 h-6 rounded"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="p-6 space-y-4">
            @forelse(collect($events)->take($maxEntries) as $event)
                <div class="price-tracker-item bg-gray-50 rounded-lg p-4 transition-all hover:shadow-md border-l-4 
                    @if(isset($event['trend']) && $event['trend'] === 'up') border-red-500 @endif
                    @if(isset($event['trend']) && $event['trend'] === 'down') border-green-500 @endif
                    @if(isset($event['trend']) && $event['trend'] === 'stable') border-blue-500 @endif">
                    
                    <div class="flex items-center justify-between">
                        <!-- Event Info -->
                        <div class="flex items-center space-x-3">
                            <!-- Event Icon/Sport Type -->
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xs font-bold
                                @if(isset($event['sport'])) 
                                    @if($event['sport'] === 'football') bg-gradient-to-br from-yellow-600 to-yellow-800 @endif
                                    @if($event['sport'] === 'basketball') bg-gradient-to-br from-orange-500 to-red-600 @endif
                                    @if($event['sport'] === 'baseball') bg-gradient-to-br from-red-500 to-red-700 @endif
                                    @if($event['sport'] === 'soccer') bg-gradient-to-br from-green-500 to-green-700 @endif
                                    @if($event['sport'] === 'hockey') bg-gradient-to-br from-blue-600 to-blue-800 @endif
                                @else
                                    bg-gradient-to-br from-gray-500 to-gray-700
                                @endif">
                                {{ isset($event['sport']) ? strtoupper(substr($event['sport'], 0, 3)) : 'TIX' }}
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm">{{ $event['name'] ?? 'Event Name' }}</h4>
                                <p class="text-xs text-gray-500">{{ $event['venue'] ?? 'Venue' }}</p>
                                @if(isset($event['date']))
                                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($event['date'])->format('M j') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Price Info -->
                        <div class="text-right">
                            <div class="flex items-center space-x-2">
                                <!-- Current Price -->
                                <div class="text-xl font-bold text-gray-900">
                                    ${{ number_format($event['current_price'] ?? 0, 2) }}
                                </div>
                                
                                <!-- Price Change -->
                                @if(isset($event['price_change']))
                                    <div class="flex items-center text-sm font-medium
                                        @if($event['price_change'] > 0) text-red-600 @endif
                                        @if($event['price_change'] < 0) text-green-600 @endif
                                        @if($event['price_change'] == 0) text-gray-500 @endif">
                                        
                                        @if($event['price_change'] > 0)
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                        @elseif($event['price_change'] < 0)
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                            </svg>
                                        @endif
                                        
                                        {{ $event['price_change'] > 0 ? '+' : '' }}{{ number_format($event['price_change'], 1) }}%
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Previous Price -->
                            @if(isset($event['previous_price']))
                                <div class="text-xs text-gray-400 line-through">
                                    was ${{ number_format($event['previous_price'], 2) }}
                                </div>
                            @endif
                            
                            <!-- Last Updated -->
                            @if(isset($event['last_updated']))
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($event['last_updated'])->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Price History Mini Chart (if enabled) -->
                    @if($showChart && isset($event['price_history']) && count($event['price_history']) > 1)
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-gray-500">7-day trend</span>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs text-gray-400">
                                        Low: ${{ number_format(min($event['price_history']), 2) }}
                                    </span>
                                    <span class="text-xs text-gray-400">
                                        High: ${{ number_format(max($event['price_history']), 2) }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Simple SVG Chart -->
                            <div class="price-chart-container">
                                <svg class="w-full h-12" viewBox="0 0 200 40" xmlns="http://www.w3.org/2000/svg">
                                    @php
                                        $prices = $event['price_history'];
                                        $min = min($prices);
                                        $max = max($prices);
                                        $range = $max - $min ?: 1;
                                        $points = [];
                                        foreach($prices as $index => $price) {
                                            $x = ($index / (count($prices) - 1)) * 180 + 10;
                                            $y = 35 - (($price - $min) / $range) * 25;
                                            $points[] = "$x,$y";
                                        }
                                        $pathData = 'M ' . implode(' L ', $points);
                                    @endphp
                                    
                                    <!-- Grid lines -->
                                    <defs>
                                        <pattern id="grid" width="20" height="10" patternUnits="userSpaceOnUse">
                                            <path d="M 20 0 L 0 0 0 10" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
                                        </pattern>
                                    </defs>
                                    <rect width="100%" height="100%" fill="url(#grid)"/>
                                    
                                    <!-- Price line -->
                                    <path d="{{ $pathData }}" 
                                          fill="none" 
                                          stroke="url(#gradient-{{ $loop->index }})" 
                                          stroke-width="2" 
                                          stroke-linecap="round"/>
                                    
                                    <!-- Price points -->
                                    @foreach($prices as $index => $price)
                                        @php
                                            $x = ($index / (count($prices) - 1)) * 180 + 10;
                                            $y = 35 - (($price - $min) / $range) * 25;
                                        @endphp
                                        <circle cx="{{ $x }}" 
                                                cy="{{ $y }}" 
                                                r="2" 
                                                fill="white" 
                                                stroke="url(#gradient-{{ $loop->parent->index }})" 
                                                stroke-width="2"/>
                                    @endforeach
                                    
                                    <!-- Gradient definition -->
                                    <defs>
                                        <linearGradient id="gradient-{{ $loop->index }}" x1="0%" y1="0%" x2="100%" y2="0%">
                                            @if(isset($event['trend']) && $event['trend'] === 'up')
                                                <stop offset="0%" style="stop-color:#ef4444;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#f87171;stop-opacity:1" />
                                            @elseif(isset($event['trend']) && $event['trend'] === 'down')
                                                <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#34d399;stop-opacity:1" />
                                            @else
                                                <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                                <stop offset="100%" style="stop-color:#60a5fa;stop-opacity:1" />
                                            @endif
                                        </linearGradient>
                                    </defs>
                                </svg>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center space-x-2">
                            <!-- Price Alert Toggle -->
                            <button class="text-xs text-gray-500 hover:text-blue-600 flex items-center space-x-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 5L4 0v5h5zM7 12h.01M17 12h.01"/>
                                </svg>
                                <span>Set Alert</span>
                            </button>
                            
                            <!-- Bookmark -->
                            <button class="text-xs text-gray-500 hover:text-yellow-600 flex items-center space-x-1 transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                </svg>
                                <span>Track</span>
                            </button>
                        </div>
                        
                        <!-- View Details -->
                        <button class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full hover:bg-blue-200 transition-colors">
                            View Details
                        </button>
                    </div>
                </div>
            @empty
                <!-- No Data State -->
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Price Data Available</h4>
                    <p class="text-gray-500 text-sm">Price tracking will appear here when events are monitored.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Footer -->
    @if(!$isLoading && count($events) > 0)
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">
                    Last updated: <span class="font-medium">{{ now()->format('g:i A') }}</span>
                </span>
                <button class="text-blue-600 hover:text-blue-800 font-medium transition-colors refresh-prices-btn">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const refreshBtn = document.querySelector('.refresh-prices-btn');
    const updateInterval = {{ $updateInterval }};
    
    function refreshPrices() {
        // Add loading state
        if (refreshBtn) {
            const originalText = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<svg class="w-4 h-4 inline mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Refreshing...';
            
            // Simulate refresh (in real implementation, this would make an AJAX call)
            setTimeout(() => {
                refreshBtn.innerHTML = originalText;
                
                // Flash update indicator
                const indicators = document.querySelectorAll('.price-tracker-item');
                indicators.forEach(item => {
                    item.classList.add('animate-pulse');
                    setTimeout(() => item.classList.remove('animate-pulse'), 1000);
                });
            }, 2000);
        }
    }
    
    // Auto-refresh functionality
    if (updateInterval > 0) {
        setInterval(refreshPrices, updateInterval);
    }
    
    // Manual refresh button
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshPrices);
    }
});
</script>

<style>
.loading-shimmer {
    background: linear-gradient(90deg, var(--gray-200) 0px, var(--gray-100) 40px, var(--gray-200) 80px);
    background-size: 200px;
    animation: shimmer 1.5s infinite;
}

.price-chart-container {
    background: #f9fafb;
    border-radius: 6px;
    padding: 8px;
}

.price-tracker-item:hover {
    transform: translateY(-1px);
}

/* Real-time pulse effect */
.price-tracker-widget::before {
    content: '';
    position: absolute;
    top: 16px;
    right: 16px;
    width: 8px;
    height: 8px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .price-tracker-item {
        padding: 12px;
    }
    
    .price-tracker-item .text-xl {
        font-size: 1.125rem;
    }
    
    .price-chart-container svg {
        height: 32px;
    }
}
</style>
