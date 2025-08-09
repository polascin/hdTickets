@props([
    'events' => [],
    'isLoading' => false,
    'title' => 'High-Demand Events',
    'autoRotate' => true,
    'interval' => 5000 // milliseconds
])

<div class="event-spotlight-container bg-white shadow-lg rounded-xl overflow-hidden border border-gray-200">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-green-500 to-blue-600 text-white">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
        </div>
        
        @if(!$isLoading && count($events) > 1)
            <div class="flex items-center space-x-2">
                <button class="spotlight-prev-btn p-1 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button class="spotlight-next-btn p-1 rounded-full bg-white bg-opacity-20 hover:bg-opacity-30 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="spotlight-content-wrapper p-6" style="min-height: 300px;">
        @if($isLoading)
            <!-- Loading State -->
            <div class="flex flex-col items-center justify-center h-64 space-y-4">
                <div class="loading-shimmer w-24 h-24 rounded-full"></div>
                <div class="loading-shimmer w-48 h-6 rounded"></div>
                <div class="loading-shimmer w-32 h-4 rounded"></div>
                <div class="loading-shimmer w-40 h-4 rounded"></div>
            </div>
        @else
            @forelse($events as $index => $event)
                <div class="spotlight-event {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <div class="grid lg:grid-cols-2 gap-6 items-center">
                        <!-- Event Image/Visual -->
                        <div class="relative">
                            @if(isset($event['image']))
                                <img src="{{ $event['image'] }}" alt="{{ $event['name'] ?? 'Event' }}" 
                                     class="w-full h-48 object-cover rounded-lg shadow-md">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-gray-200 to-gray-300 rounded-lg flex items-center justify-center">
                                    <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Demand Badge -->
                            @if(isset($event['demand_level']))
                                <div class="absolute top-3 left-3">
                                    <span class="badge 
                                        @if($event['demand_level'] === 'very_high') badge-error text-white bg-red-500 @endif
                                        @if($event['demand_level'] === 'high') badge-warning text-white bg-orange-500 @endif
                                        @if($event['demand_level'] === 'medium') badge-info text-white bg-blue-500 @endif
                                        px-3 py-1 rounded-full text-xs font-semibold">
                                        {{ ucfirst(str_replace('_', ' ', $event['demand_level'])) }} Demand
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Event Details -->
                        <div class="space-y-4">
                            <div>
                                <h4 class="text-2xl font-bold text-gray-900 mb-2">
                                    {{ $event['name'] ?? 'Sports Event' }}
                                </h4>
                                <p class="text-gray-600 flex items-center mb-2">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $event['venue'] ?? 'Stadium' }}
                                </p>
                                <p class="text-gray-600 flex items-center mb-3">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ isset($event['date']) ? \Carbon\Carbon::parse($event['date'])->format('M j, Y g:i A') : 'Date TBD' }}
                                </p>
                            </div>

                            <!-- Price Range -->
                            @if(isset($event['price_range']))
                                <div class="flex items-center space-x-4">
                                    <div class="text-2xl font-bold text-green-600">
                                        ${{ number_format($event['price_range']['min'] ?? 0, 2) }} - ${{ number_format($event['price_range']['max'] ?? 0, 2) }}
                                    </div>
                                    @if(isset($event['price_change']))
                                        <span class="text-sm {{ $event['price_change'] > 0 ? 'text-red-500' : 'text-green-500' }} font-medium flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($event['price_change'] > 0)
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                                @endif
                                            </svg>
                                            {{ $event['price_change'] > 0 ? '+' : '' }}{{ number_format($event['price_change'], 1) }}%
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Availability -->
                            @if(isset($event['availability']))
                                <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                                    <span class="text-sm font-medium text-gray-700">Available Tickets</span>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full bg-gradient-to-r from-green-500 to-blue-600" 
                                                 style="width: {{ max(5, $event['availability']) }}%"></div>
                                        </div>
                                        <span class="text-sm font-semibold text-gray-900">{{ $event['availability'] }}%</span>
                                    </div>
                                </div>
                            @endif

                            <!-- Action Button -->
                            <div class="pt-2">
                                <button class="btn btn-primary w-full lg:w-auto px-8 py-3 bg-gradient-to-r from-green-500 to-blue-600 text-white rounded-lg hover:from-green-600 hover:to-blue-700 transition-all transform hover:scale-105">
                                    View Tickets
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- No Events State -->
                <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">No Events Available</h4>
                    <p class="text-gray-500 text-center">High-demand events will appear here when available.</p>
                </div>
            @endforelse
        @endif
    </div>

    <!-- Indicators -->
    @if(!$isLoading && count($events) > 1)
        <div class="flex justify-center space-x-2 p-4 bg-gray-50">
            @foreach($events as $index => $event)
                <button class="spotlight-indicator w-3 h-3 rounded-full {{ $index === 0 ? 'bg-green-500' : 'bg-gray-300' }} transition-all" 
                        data-index="{{ $index }}"></button>
            @endforeach
        </div>
    @endif
</div>

@if(!$isLoading && count($events) > 1)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.event-spotlight-container');
    const events = container.querySelectorAll('.spotlight-event');
    const indicators = container.querySelectorAll('.spotlight-indicator');
    const prevBtn = container.querySelector('.spotlight-prev-btn');
    const nextBtn = container.querySelector('.spotlight-next-btn');
    let currentIndex = 0;
    let autoRotateTimer;

    function showEvent(index) {
        events.forEach((event, i) => {
            event.classList.toggle('active', i === index);
            event.style.display = i === index ? 'block' : 'none';
        });
        
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('bg-green-500', i === index);
            indicator.classList.toggle('bg-gray-300', i !== index);
        });
        
        currentIndex = index;
    }

    function nextEvent() {
        const nextIndex = (currentIndex + 1) % events.length;
        showEvent(nextIndex);
    }

    function prevEvent() {
        const prevIndex = (currentIndex - 1 + events.length) % events.length;
        showEvent(prevIndex);
    }

    function startAutoRotate() {
        @if($autoRotate)
        autoRotateTimer = setInterval(nextEvent, {{ $interval }});
        @endif
    }

    function stopAutoRotate() {
        clearInterval(autoRotateTimer);
    }

    // Initialize
    showEvent(0);
    startAutoRotate();

    // Event listeners
    nextBtn?.addEventListener('click', () => {
        stopAutoRotate();
        nextEvent();
        startAutoRotate();
    });

    prevBtn?.addEventListener('click', () => {
        stopAutoRotate();
        prevEvent();
        startAutoRotate();
    });

    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => {
            stopAutoRotate();
            showEvent(index);
            startAutoRotate();
        });
    });

    // Pause on hover
    container.addEventListener('mouseenter', stopAutoRotate);
    container.addEventListener('mouseleave', startAutoRotate);
});
</script>
@endif

<style>
.spotlight-event {
    display: none;
    animation: fadeInScale 0.5s ease-out;
}

.spotlight-event.active {
    display: block;
}

@keyframes fadeInScale {
    0% {
        opacity: 0;
        transform: scale(0.95);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

.loading-shimmer {
    background: linear-gradient(90deg, var(--gray-200) 0px, var(--gray-100) 40px, var(--gray-200) 80px);
    background-size: 200px;
    animation: shimmer 1.5s infinite;
}
</style>
