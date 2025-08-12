{{--
    Component Architecture Demonstration
    
    This example shows how to use the new component architecture with
    Blade, Alpine.js, and Vue.js components working together.
--}}

@extends('layouts.master')

@section('title', 'Component Architecture Demo')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
            Component Architecture Demonstration
        </h1>
        <p class="text-gray-600 dark:text-gray-300">
            Showcasing the integration of Blade, Alpine.js, and Vue.js components
        </p>
    </div>

    {{-- Component Statistics --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            Component Registry Statistics
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ $componentStats['blade_components'] ?? 0 }}
                </div>
                <div class="text-sm text-blue-600 dark:text-blue-400">Blade Components</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ $componentStats['alpine_components'] ?? 0 }}
                </div>
                <div class="text-sm text-green-600 dark:text-green-400">Alpine.js Components</div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ $componentStats['vue_components'] ?? 0 }}
                </div>
                <div class="text-sm text-purple-600 dark:text-purple-400">Vue.js Components</div>
            </div>
        </div>
    </div>

    {{-- Blade Component Example --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            1. Blade Component Example
        </h2>
        
        {{-- Using the ticket card Blade component --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <x-ticket-card 
                :ticket-id="'TKT-123456'"
                :event-name="'Liverpool vs Manchester United'"
                :venue="'Anfield Stadium'"
                :date="now()->addDays(7)->toISOString()"
                :price="85.00"
                :availability-status="'available'"
                :platform-source="'ticketmaster'"
                :sport-category="'football'"
                :section="'Kop Stand'"
                :row="'Row 15'"
                :seats="['Seat 12', 'Seat 13']"
                :image-url="asset('assets/images/anfield.jpg')"
                :description="'Premier League match - one of the biggest rivalries in English football'"
                :original-price="95.00"
                :is-favorited="false"
                :has-alert="false"
            />
        </div>
    </div>

    {{-- Alpine.js Component Example --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            2. Alpine.js Component Example
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            {{-- Event filter component using Alpine.js --}}
            <div x-data="eventFilter(
                {
                    category: 'all',
                    venue: 'all',
                    dateRange: 'this_week',
                    priceMin: null,
                    priceMax: null
                },
                [
                    {
                        id: 1,
                        name: 'Chelsea vs Arsenal',
                        venue: 'Stamford Bridge',
                        date: '{{ now()->addDays(3)->toISOString() }}',
                        price: 75,
                        sport_category: 'football',
                        availability_status: 'available',
                        platform_source: 'ticketmaster'
                    },
                    {
                        id: 2,
                        name: 'England vs New Zealand',
                        venue: 'Twickenham Stadium',
                        date: '{{ now()->addDays(5)->toISOString() }}',
                        price: 95,
                        sport_category: 'rugby',
                        availability_status: 'limited',
                        platform_source: 'official'
                    },
                    {
                        id: 3,
                        name: 'Wimbledon Finals',
                        venue: 'All England Club',
                        date: '{{ now()->addDays(2)->toISOString() }}',
                        price: 250,
                        sport_category: 'tennis',
                        availability_status: 'available',
                        platform_source: 'seatgeek'
                    }
                ]
            )" @filter-applied="console.log('Filter applied:', $event.detail)">
                
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Sports Event Filter
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">
                        This filter component is powered by Alpine.js with advanced reactivity
                    </p>
                </div>

                {{-- Filter Controls --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Sport Category
                        </label>
                        <select x-model="filters.category" 
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="all">All Sports</option>
                            <template x-for="category in categories" :key="category.value">
                                <option :value="category.value" x-text="category.label"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Date Range
                        </label>
                        <select x-model="filters.dateRange"
                                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <template x-for="range in dateRanges" :key="range.value">
                                <option :value="range.value" x-text="range.label"></option>
                            </template>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Min Price (£)
                        </label>
                        <input type="number" x-model="filters.priceMin" 
                               placeholder="0"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Max Price (£)
                        </label>
                        <input type="number" x-model="filters.priceMax" 
                               placeholder="999"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                    </div>
                </div>

                {{-- Filter Actions --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center space-x-4">
                        <button @click="clearFilters()" 
                                class="px-4 py-2 text-gray-600 hover:text-gray-900 border border-gray-300 rounded-md hover:bg-gray-50">
                            Clear Filters
                        </button>
                        
                        <button @click="toggleAdvancedFilters()" 
                                class="px-4 py-2 text-blue-600 hover:text-blue-900 border border-blue-300 rounded-md hover:bg-blue-50">
                            Advanced Filters
                        </button>
                    </div>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-300">
                        <span x-show="isFiltering" class="inline-flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Filtering...
                        </span>
                        <span x-show="!isFiltering">
                            <span x-text="filteredEvents.length"></span> of 
                            <span x-text="events.length"></span> events
                        </span>
                    </div>
                </div>

                {{-- Filter Summary --}}
                <div x-show="hasActiveFilters" 
                     class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                Active Filters (<span x-text="activeFilterCount"></span>)
                            </div>
                            <div class="text-sm text-blue-700 dark:text-blue-300 mt-1" x-text="filterSummary"></div>
                        </div>
                        <button @click="clearFilters()" 
                                class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                            Clear All
                        </button>
                    </div>
                </div>

                {{-- Filtered Results --}}
                <div class="space-y-4">
                    <template x-for="event in filteredEvents" :key="event.id">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white" x-text="event.name"></h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        <span x-text="event.venue"></span> • 
                                        <span x-text="new Date(event.date).toLocaleDateString()"></span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        £<span x-text="event.price"></span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400" x-text="event.platform_source"></div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <div x-show="filteredEvents.length === 0" class="text-center py-8">
                        <div class="text-gray-400 text-lg">No events match your criteria</div>
                        <button @click="clearFilters()" 
                                class="mt-2 text-blue-600 hover:text-blue-900 text-sm">
                            Clear filters to see all events
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vue.js Component Example --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            3. Vue.js Component Example (Lazy Loaded)
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                    Advanced Event Analytics
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    This component is lazy-loaded from Vue.js for complex interactive features
                </p>
            </div>
            
            {{-- Placeholder for lazy-loaded Vue component --}}
            <div id="advanced-analytics-demo" 
                 class="min-h-[400px] flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
                    <p class="text-gray-600 dark:text-gray-300">Loading Advanced Analytics Component...</p>
                    <button onclick="loadAdvancedAnalytics()" 
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        Click to Load Vue.js Component
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Component Communication Example --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            4. Component Communication
        </h2>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            {{-- Alpine to Vue Communication --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Alpine.js → Vue.js Communication
                </h3>
                
                <div x-data="{ 
                    message: 'Hello from Alpine!', 
                    count: 0,
                    sendToVue() {
                        this.count++;
                        document.dispatchEvent(new CustomEvent('alpine-to-vue', {
                            detail: { message: this.message, count: this.count }
                        }));
                    }
                }">
                    <div class="space-y-4">
                        <input x-model="message" 
                               type="text" 
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                               placeholder="Enter message">
                        
                        <button @click="sendToVue()" 
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            Send to Vue Component (<span x-text="count"></span>)
                        </button>
                    </div>
                </div>
            </div>

            {{-- Vue to Alpine Communication --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Vue.js → Alpine.js Communication
                </h3>
                
                <div x-data="{ 
                    receivedMessage: 'Waiting for Vue...',
                    receivedCount: 0,
                    init() {
                        document.addEventListener('vue-to-alpine', (e) => {
                            this.receivedMessage = e.detail.message;
                            this.receivedCount = e.detail.count;
                        });
                    }
                }">
                    <div class="space-y-4">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-sm text-gray-600 dark:text-gray-300">Received from Vue:</div>
                            <div class="font-medium text-gray-900 dark:text-white" x-text="receivedMessage"></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Count: <span x-text="receivedCount"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Component Performance Metrics --}}
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
            5. Component Performance Metrics
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                        {{ $lifecycleStats['active_components'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Active Components</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                        {{ number_format($lifecycleStats['average_creation_time'] ?? 0, 2) }}ms
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Avg Creation Time</div>
                </div>
                
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                        {{ $lifecycleStats['total_errors'] ?? 0 }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-300">Total Errors</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Function to lazy-load the Vue.js analytics component
function loadAdvancedAnalytics() {
    const container = document.getElementById('advanced-analytics-demo');
    
    // Show loading state
    container.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto mb-4"></div>
            <p class="text-gray-600 dark:text-gray-300">Loading Vue.js component...</p>
        </div>
    `;
    
    // Simulate component loading (in real implementation, this would use the ComponentRegistry)
    setTimeout(() => {
        import('./components/analytics/AdvancedEventAnalytics.vue')
            .then((module) => {
                // Component loaded successfully
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-green-600 text-xl mb-4">✅ Vue.js Component Loaded!</div>
                        <p class="text-gray-600 dark:text-gray-300">
                            Advanced Event Analytics component is now available.
                            <br>This demonstrates lazy loading of complex Vue.js components.
                        </p>
                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div class="text-sm text-blue-900 dark:text-blue-100">
                                Component Features:
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Real-time analytics charts</li>
                                    <li>Interactive data visualization</li>
                                    <li>Performance optimized rendering</li>
                                    <li>WebSocket integration</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                `;
            })
            .catch((error) => {
                console.error('Failed to load component:', error);
                container.innerHTML = `
                    <div class="text-center py-8">
                        <div class="text-red-600 text-xl mb-4">❌ Loading Failed</div>
                        <p class="text-gray-600 dark:text-gray-300">
                            Component could not be loaded. This is expected in the demo.
                        </p>
                        <button onclick="loadAdvancedAnalytics()" 
                                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Try Again
                        </button>
                    </div>
                `;
            });
    }, 2000);
}

// Demonstrate Vue to Alpine communication
function demonstrateVueToAlpine() {
    const vueData = {
        message: 'Hello from Vue.js!',
        count: Math.floor(Math.random() * 100),
        timestamp: new Date().toLocaleTimeString()
    };
    
    document.dispatchEvent(new CustomEvent('vue-to-alpine', {
        detail: vueData
    }));
}

// Auto-demonstrate communication every 10 seconds
setInterval(demonstrateVueToAlpine, 10000);
</script>
@endsection
