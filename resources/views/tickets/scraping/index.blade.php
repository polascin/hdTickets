<x-modern-app-layout title="Sports Tickets" subtitle="Browse and search for sports tickets across multiple platforms">
    <x-slot name="headerActions">
        <div class="flex gap-3">
            <x-ui.button 
                id="refresh-tickets"
                :icon="'<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15\"></path></svg>'">
                Refresh
            </x-ui.button>
            <x-ui.button 
                variant="ghost" 
                data-clear-filters
                :icon="'<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16\"></path></svg>'">
                Clear Filters
            </x-ui.button>
        </div>
    </x-slot>

    <!-- Search & Filters Panel -->
    <x-ui.card class="mb-6">
        <x-ui.card-header title="Search & Filters">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </x-ui.card-header>
        <x-ui.card-content>
            <form id="filters-form" class="space-y-6">
                <!-- Search Input -->
                <x-ui.input 
                    name="keywords" 
                    label="Search Tickets"
                    placeholder="Search by event, team, venue..."
                    value="{{ request('keywords') }}"
                    variant="search"
                    :icon="'<svg class=\"w-5 h-5\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\"></path></svg>'"
                    iconPosition="right" />

                <!-- Filter Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Platform Filter -->
                    <div>
                        <label for="platform" class="hd-label">Platform</label>
                        <select name="platform" id="platform" class="hd-input">
                            <option value="">All Platforms</option>
                            <option value="stubhub" {{ request('platform') == 'stubhub' ? 'selected' : '' }}>StubHub</option>
                            <option value="ticketmaster" {{ request('platform') == 'ticketmaster' ? 'selected' : '' }}>Ticketmaster</option>
                            <option value="viagogo" {{ request('platform') == 'viagogo' ? 'selected' : '' }}>Viagogo</option>
                        </select>
                    </div>

                    <!-- Min Price -->
                    <x-ui.input 
                        name="min_price" 
                        type="number"
                        label="Min Price"
                        placeholder="0"
                        value="{{ request('min_price') }}" />

                    <!-- Max Price -->
                    <x-ui.input 
                        name="max_price" 
                        type="number"
                        label="Max Price"
                        placeholder="1000"
                        value="{{ request('max_price') }}" />
                </div>

                <!-- Options -->
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="high_demand" 
                            value="1" 
                            {{ request('high_demand') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 hd-text-small flex items-center">
                            <svg class="w-4 h-4 mr-1 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path>
                            </svg>
                            High Demand Only
                        </span>
                    </label>

                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="available_only" 
                            value="1" 
                            {{ request('available_only') ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 hd-text-small">Available Only</span>
                    </label>
                </div>
            </form>
        </x-ui.card-content>
    </x-ui.card>

    <!-- Loading Indicator -->
    <div id="loading-indicator" class="flex flex-col items-center justify-center py-12" style="display: none;">
        <div class="hd-loading-skeleton hd-loading-skeleton--spinner"></div>
        <p class="mt-4 hd-text-base">Loading tickets...</p>
    </div>

    <!-- Tickets Container -->
    <div id="tickets-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @forelse($tickets as $ticket)
            <x-ui.card hover="true" class="ticket-card">
                <x-ui.card-content>
                    <div class="flex justify-between items-start mb-3">
                        <h3 class="hd-text-lg font-semibold text-gray-900 truncate mr-2">{{ $ticket->title }}</h3>
                        @if($ticket->is_high_demand)
                            <x-ui.badge variant="warning" size="sm">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path>
                                </svg>
                                Hot
                            </x-ui.badge>
                        @endif
                    </div>
                    
                    <div class="space-y-2 mb-4">
                        @if($ticket->venue)
                            <p class="hd-text-small text-gray-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $ticket->venue }}
                            </p>
                        @endif
                        
                        @if($ticket->event_date)
                            <p class="hd-text-small text-gray-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $ticket->event_date->format('M j, Y g:i A') }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center mb-4">
                        <div class="hd-text-lg font-bold text-gray-900">
                            {{ $ticket->formatted_price }}
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <x-ui.badge variant="{{ $ticket->is_available ? 'success' : 'default' }}" size="sm">
                                {{ $ticket->is_available ? 'Available' : 'Sold Out' }}
                            </x-ui.badge>
                            
                            <span class="hd-text-small text-gray-500">{{ $ticket->platform_display_name }}</span>
                        </div>
                    </div>
                    
                    @if($ticket->is_available)
                        <x-ui.button 
                            href="{{ $ticket->ticket_url }}" 
                            target="_blank"
                            fullWidth="true"
                            :icon="'<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14\"></path></svg>'"
                            iconPosition="right">
                            View Tickets
                        </x-ui.button>
                    @endif
                </x-ui.card-content>
            </x-ui.card>
        @empty
            <!-- Empty state will be shown by JavaScript if no results -->
        @endforelse
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="text-center py-12" style="display: {{ $tickets->count() > 0 ? 'none' : 'block' }};">
        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
        </svg>
        <h3 class="hd-heading-3 mb-2">No tickets found</h3>
        <p class="hd-text-base text-gray-500 mb-6">Try adjusting your search criteria or check back later for new tickets.</p>
        <x-ui.button data-clear-filters>
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
            Clear All Filters
        </x-ui.button>
    </div>

    <!-- Load More Button -->
    @if($tickets->hasPages())
        <div class="text-center mt-8">
            <x-ui.button 
                id="load-more-btn" 
                variant="outline"
                size="lg">
                <span class="btn-text">Load More Tickets</span>
                <svg class="animate-spin -mr-1 ml-3 h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </x-ui.button>
        </div>
    @endif

    <!-- Statistics Panel -->
    <div class="mt-12">
        <h3 class="hd-heading-3 mb-6">Statistics</h3>
        <div class="hd-grid-4">
            <x-ui.card class="bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 hd-text-small font-medium">Total Tickets</p>
                            <p class="hd-heading-2 !text-white !mb-0" id="stat-total">{{ $stats['total_tickets'] ?? 0 }}</p>
                        </div>
                        <div class="bg-blue-400 bg-opacity-50 rounded-full p-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card class="bg-gradient-to-r from-green-500 to-green-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 hd-text-small font-medium">Available</p>
                            <p class="hd-heading-2 !text-white !mb-0" id="stat-available">{{ $tickets->where('is_available', true)->count() }}</p>
                        </div>
                        <div class="bg-green-400 bg-opacity-50 rounded-full p-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card class="bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 hd-text-small font-medium">High Demand</p>
                            <p class="hd-heading-2 !text-white !mb-0" id="stat-high-demand">{{ $stats['high_demand_tickets'] ?? 0 }}</p>
                        </div>
                        <div class="bg-orange-400 bg-opacity-50 rounded-full p-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
            
            <x-ui.card class="bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                <x-ui.card-content>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 hd-text-small font-medium">Active Alerts</p>
                            <p class="hd-heading-2 !text-white !mb-0" id="stat-platforms">{{ $stats['active_alerts'] ?? 0 }}</p>
                        </div>
                        <div class="bg-purple-400 bg-opacity-50 rounded-full p-3">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2L3 7v11c0 5.55 3.84 10 9 10s9-4.45 9-10V7l-7-5zM8 13l2-2 1-1 4-4-1.5-1.5L9 9 8 8l-1.5 1.5L8 13z"></path>
                            </svg>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form handling without external dependencies
        const filtersForm = document.getElementById('filters-form');
        const searchInput = document.querySelector('[name="keywords"]');
        const refreshBtn = document.getElementById('refresh-tickets');
        const clearFiltersBtn = document.querySelector('[data-clear-filters]');
        const loadingIndicator = document.getElementById('loading-indicator');
        const emptyState = document.getElementById('empty-state');
        const ticketsContainer = document.getElementById('tickets-container');
        
        // Handle form changes
        if (filtersForm) {
            filtersForm.addEventListener('change', function() {
                setTimeout(() => {
                    applyFilters();
                }, 300);
            });
        }
        
        // Handle search input
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 500);
            });
        }
        
        // Refresh button functionality
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }
        
        // Clear filters functionality
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', function() {
                const inputs = filtersForm.querySelectorAll('input, select');
                inputs.forEach(input => {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
                
                window.location.href = window.location.pathname;
            });
        }
        
        // Apply filters function
        function applyFilters() {
            if (!filtersForm) return;
            
            const formData = new FormData(filtersForm);
            const params = new URLSearchParams();
            
            for (let [key, value] of formData.entries()) {
                if (value && value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            const newUrl = params.toString() ? 
                `${window.location.pathname}?${params.toString()}` : 
                window.location.pathname;
                
            window.location.href = newUrl;
        }
        
        // Simple animation for stats
        function animateNumber(element, targetValue) {
            if (!element) return;
            
            const startValue = parseInt(element.textContent) || 0;
            const duration = 1000;
            const startTime = performance.now();
            
            function update(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
                element.textContent = currentValue.toLocaleString();
                
                if (progress < 1) {
                    requestAnimationFrame(update);
                }
            }
            
            requestAnimationFrame(update);
        }
        
        // Initialize animations
        const statElements = document.querySelectorAll('[id^="stat-"]');
        statElements.forEach(element => {
            const value = parseInt(element.textContent) || 0;
            if (value > 0) {
                element.textContent = '0';
                setTimeout(() => animateNumber(element, value), 100);
            }
        });
        
        console.log('Sports Tickets page loaded successfully');
    });
    </script>
    @endpush
</x-modern-app-layout>
