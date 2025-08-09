@props([
    'events' => [],
    'isLoading' => false,
    'title' => 'Trending Sports Events',
    'maxEvents' => 6,
    'showViewAll' => true,
    'timeframe' => '7d' // 24h, 7d, 30d
])

<div class="trending-events-widget bg-white shadow-lg rounded-xl border border-gray-200 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-pink-500 to-purple-600 text-white">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        </div>
        
        <!-- Timeframe Filter -->
        <div class="flex items-center space-x-1 bg-white bg-opacity-20 rounded-lg p-1">
            <button class="trending-filter-btn px-3 py-1 rounded text-xs font-medium transition-all {{ $timeframe === '24h' ? 'bg-white text-purple-600' : 'text-white hover:bg-white hover:bg-opacity-20' }}" data-timeframe="24h">24H</button>
            <button class="trending-filter-btn px-3 py-1 rounded text-xs font-medium transition-all {{ $timeframe === '7d' ? 'bg-white text-purple-600' : 'text-white hover:bg-white hover:bg-opacity-20' }}" data-timeframe="7d">7D</button>
            <button class="trending-filter-btn px-3 py-1 rounded text-xs font-medium transition-all {{ $timeframe === '30d' ? 'bg-white text-purple-600' : 'text-white hover:bg-white hover:bg-opacity-20' }}" data-timeframe="30d">30D</button>
        </div>
    </div>

    @if($isLoading)
        <!-- Loading State -->
        <div class="p-6">
            <div class="space-y-4">
                @for($i = 0; $i < 4; $i++)
                    <div class="flex items-start space-x-4 p-4 border border-gray-100 rounded-lg">
                        <div class="loading-shimmer w-12 h-12 rounded-lg"></div>
                        <div class="flex-1 space-y-2">
                            <div class="loading-shimmer w-3/4 h-5 rounded"></div>
                            <div class="loading-shimmer w-1/2 h-4 rounded"></div>
                            <div class="loading-shimmer w-1/3 h-3 rounded"></div>
                        </div>
                        <div class="loading-shimmer w-16 h-8 rounded"></div>
                    </div>
                @endfor
            </div>
        </div>
    @else
        <div class="p-6">
            @forelse(collect($events)->take($maxEvents) as $index => $event)
                <div class="trending-event-item group relative bg-gradient-to-r from-gray-50 to-white border border-gray-100 rounded-lg p-4 mb-4 last:mb-0 hover:shadow-md hover:border-purple-200 transition-all cursor-pointer">
                    <!-- Trending Rank -->
                    <div class="absolute -top-2 -left-2 w-6 h-6 bg-gradient-to-br from-pink-500 to-purple-600 text-white text-xs font-bold rounded-full flex items-center justify-center shadow-lg">
                        {{ $index + 1 }}
                    </div>

                    <!-- Fire Icon for Hot Events -->
                    @if($index < 3)
                        <div class="absolute -top-1 -right-1 text-orange-500 animate-pulse">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    @endif

                    <div class="flex items-start space-x-4">
                        <!-- Event Visual/Sport Icon -->
                        <div class="relative">
                            @if(isset($event['image']))
                                <img src="{{ $event['image'] }}" alt="{{ $event['name'] ?? 'Event' }}" 
                                     class="w-16 h-16 object-cover rounded-lg shadow-sm">
                            @else
                                <div class="w-16 h-16 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm
                                    @if(isset($event['sport'])) 
                                        @if($event['sport'] === 'football') bg-gradient-to-br from-yellow-600 to-brown-800 @endif
                                        @if($event['sport'] === 'basketball') bg-gradient-to-br from-orange-500 to-red-600 @endif
                                        @if($event['sport'] === 'baseball') bg-gradient-to-br from-red-500 to-red-700 @endif
                                        @if($event['sport'] === 'soccer') bg-gradient-to-br from-green-500 to-green-700 @endif
                                        @if($event['sport'] === 'hockey') bg-gradient-to-br from-blue-600 to-blue-800 @endif
                                        @if($event['sport'] === 'tennis') bg-gradient-to-br from-yellow-400 to-green-500 @endif
                                    @else
                                        bg-gradient-to-br from-purple-500 to-pink-600
                                    @endif">
                                    {{ isset($event['sport']) ? strtoupper(substr($event['sport'], 0, 3)) : '🎟️' }}
                                </div>
                            @endif
                            
                            <!-- Trending Up Indicator -->
                            @if(isset($event['trend_direction']) && $event['trend_direction'] === 'up')
                                <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 17l10-10M17 7v10M17 7H7"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Event Details -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 text-sm group-hover:text-purple-600 transition-colors truncate">
                                        {{ $event['name'] ?? 'Sports Event' }}
                                    </h4>
                                    
                                    <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="truncate">{{ $event['venue'] ?? 'Venue TBD' }}</span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2 mt-1 text-xs text-gray-500">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('M j, Y') : 'Date TBD' }}</span>
                                    </div>
                                </div>

                                <!-- Trending Stats -->
                                <div class="text-right ml-4">
                                    @if(isset($event['views_increase']))
                                        <div class="text-sm font-bold text-purple-600">
                                            +{{ number_format($event['views_increase']) }}%
                                        </div>
                                        <div class="text-xs text-gray-500">views</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Event Metrics -->
                            <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                                <div class="flex items-center space-x-4 text-xs">
                                    <!-- Search Volume -->
                                    @if(isset($event['search_volume']))
                                        <div class="flex items-center space-x-1 text-blue-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <span class="font-medium">{{ number_format($event['search_volume']) }}</span>
                                        </div>
                                    @endif

                                    <!-- Ticket Interest -->
                                    @if(isset($event['ticket_interest']))
                                        <div class="flex items-center space-x-1 text-green-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                            </svg>
                                            <span class="font-medium">{{ $event['ticket_interest'] }}% interest</span>
                                        </div>
                                    @endif

                                    <!-- Social Mentions -->
                                    @if(isset($event['social_mentions']))
                                        <div class="flex items-center space-x-1 text-pink-600">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0v8a2 2 0 002 2h6a2 2 0 002-2V8"/>
                                            </svg>
                                            <span class="font-medium">{{ number_format($event['social_mentions']) }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Price Range -->
                                @if(isset($event['price_range']))
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900">
                                            ${{ number_format($event['price_range']['min'] ?? 0) }}-${{ number_format($event['price_range']['max'] ?? 0) }}
                                        </div>
                                        @if(isset($event['price_trend']) && $event['price_trend'] !== 'stable')
                                            <div class="text-xs {{ $event['price_trend'] === 'up' ? 'text-red-500' : 'text-green-500' }}">
                                                {{ $event['price_trend'] === 'up' ? '↗' : '↘' }} trending {{ $event['price_trend'] }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Trending Reasons -->
                            @if(isset($event['trending_reasons']) && count($event['trending_reasons']) > 0)
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach(collect($event['trending_reasons'])->take(2) as $reason)
                                        <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-purple-100 text-purple-700 rounded-full">
                                            {{ $reason }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons (Hidden by default, shown on hover) -->
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-white via-white to-transparent p-4 opacity-0 group-hover:opacity-100 transition-all transform translate-y-2 group-hover:translate-y-0">
                        <div class="flex items-center justify-center space-x-2">
                            <button class="btn btn-sm bg-purple-100 text-purple-700 hover:bg-purple-200 px-3 py-1 rounded-full text-xs font-medium transition-all">
                                View Event
                            </button>
                            <button class="btn btn-sm bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded-full text-xs font-medium transition-all">
                                Get Tickets
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <!-- No Events State -->
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Trending Events</h4>
                    <p class="text-gray-500 text-sm">Popular sports events will appear here as they start trending.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Footer -->
    @if(!$isLoading && count($events) > 0)
        <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <!-- Trend Summary -->
                <div class="text-sm text-gray-600">
                    <span class="font-medium text-purple-600">{{ count($events) }}</span> trending events
                    <span class="mx-1">•</span>
                    <span>Updated {{ now()->format('g:i A') }}</span>
                </div>

                @if($showViewAll)
                    <!-- View All Button -->
                    <button class="flex items-center space-x-1 text-sm font-medium text-purple-600 hover:text-purple-800 transition-colors">
                        <span>View All Trending</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.trending-filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            const timeframe = this.dataset.timeframe;
            
            // Update active state
            filterButtons.forEach(btn => {
                btn.classList.remove('bg-white', 'text-purple-600');
                btn.classList.add('text-white');
            });
            
            this.classList.remove('text-white');
            this.classList.add('bg-white', 'text-purple-600');
            
            // In real implementation, this would trigger an AJAX request
            console.log(`Switching to ${timeframe} timeframe`);
            
            // Add loading animation to event items
            const eventItems = document.querySelectorAll('.trending-event-item');
            eventItems.forEach(item => {
                item.style.opacity = '0.6';
                setTimeout(() => {
                    item.style.opacity = '1';
                }, 500);
            });
        });
    });
    
    // Auto-refresh trending data every 5 minutes
    setInterval(function() {
        const eventItems = document.querySelectorAll('.trending-event-item');
        eventItems.forEach((item, index) => {
            setTimeout(() => {
                item.classList.add('animate-pulse');
                setTimeout(() => item.classList.remove('animate-pulse'), 1000);
            }, index * 100);
        });
    }, 300000); // 5 minutes
});
</script>

<style>
.loading-shimmer {
    background: linear-gradient(90deg, var(--gray-200) 0px, var(--gray-100) 40px, var(--gray-200) 80px);
    background-size: 200px;
    animation: shimmer 1.5s infinite;
}

.trending-event-item {
    position: relative;
    overflow: hidden;
}

.trending-event-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(to bottom, #ec4899, #8b5cf6);
    transform: scaleY(0);
    transform-origin: top;
    transition: transform 0.3s ease-in-out;
}

.trending-event-item:hover::before {
    transform: scaleY(1);
}

/* Trending rank badge animation */
.trending-event-item:nth-child(1) .absolute:first-child {
    animation: bounce 2s infinite;
}

.trending-event-item:nth-child(2) .absolute:first-child {
    animation: bounce 2s infinite 0.2s;
}

.trending-event-item:nth-child(3) .absolute:first-child {
    animation: bounce 2s infinite 0.4s;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-3px);
    }
    60% {
        transform: translateY(-1px);
    }
}

/* Mobile responsiveness */
@media (max-width: 640px) {
    .trending-event-item {
        padding: 12px;
    }
    
    .trending-event-item .w-16 {
        width: 48px;
        height: 48px;
    }
    
    .trending-event-item .text-sm {
        font-size: 0.8rem;
    }
}
</style>
