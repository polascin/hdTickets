@extends('layouts.modern-app')

@section('title', 'Discover Sports Tickets')

@section('meta_description', 'Find and compare sports event tickets from multiple platforms with advanced filtering and real-time pricing')

@push('head')
    <meta name="robots" content="index, follow">
    <link rel="preload" href="{{ asset('js/ticket-discovery.js') }}" as="script">
@endpush

@section('page-header')
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">
                Discover Sports Tickets 🎟️
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Find and compare tickets from multiple platforms with real-time pricing
            </p>
        </div>
        
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <!-- View Toggle -->
            <div class="flex rounded-lg border border-gray-200 dark:border-gray-700 p-1" 
                 x-data="{ view: 'grid' }">
                <button @click="view = 'grid'"
                        :class="view === 'grid' ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Grid
                </button>
                <button @click="view = 'list'"
                        :class="view === 'list' ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    List
                </button>
                <button @click="view = 'map'"
                        :class="view === 'map' ? 'bg-blue-500 text-white' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="flex items-center px-3 py-1.5 rounded-md text-sm font-medium transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Map
                </button>
            </div>

            <!-- Save Search -->
            <button class="hdt-button hdt-button--secondary hdt-button--md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                Save Search
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="ticketDiscovery()" x-init="init()" class="space-y-6">
        
        <!-- Advanced Filters -->
        <div class="hdt-card">
            <div class="hdt-card__header">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Search & Filters</h3>
                    <button @click="resetFilters()" 
                            class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        Clear All
                    </button>
                </div>
            </div>
            
            <div class="hdt-card__body">
                <!-- Main Search -->
                <div class="mb-6">
                    <div class="relative">
                        <input type="search" 
                               placeholder="Search events, teams, or venues..."
                               class="w-full hdt-input pl-12"
                               x-model="filters.search"
                               @input="debouncedSearch()">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Filter Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    
                    <!-- Sports Category -->
                    <div class="hdt-form-group">
                        <label class="hdt-label">Sport</label>
                        <select x-model="filters.sport" class="hdt-input">
                            <option value="">All Sports</option>
                            <option value="nfl">NFL</option>
                            <option value="nba">NBA</option>
                            <option value="mlb">MLB</option>
                            <option value="nhl">NHL</option>
                            <option value="mls">MLS</option>
                            <option value="ncaa">NCAA</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div class="hdt-form-group">
                        <label class="hdt-label">Date Range</label>
                        <select x-model="filters.dateRange" class="hdt-input">
                            <option value="">Any Time</option>
                            <option value="today">Today</option>
                            <option value="tomorrow">Tomorrow</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div class="hdt-form-group">
                        <label class="hdt-label">Max Price</label>
                        <select x-model="filters.maxPrice" class="hdt-input">
                            <option value="">No Limit</option>
                            <option value="50">Under $50</option>
                            <option value="100">Under $100</option>
                            <option value="200">Under $200</option>
                            <option value="500">Under $500</option>
                        </select>
                    </div>

                    <!-- Platform -->
                    <div class="hdt-form-group">
                        <label class="hdt-label">Platform</label>
                        <select x-model="filters.platform" class="hdt-input">
                            <option value="">All Platforms</option>
                            <option value="ticketmaster">Ticketmaster</option>
                            <option value="stubhub">StubHub</option>
                            <option value="seatgeek">SeatGeek</option>
                            <option value="vivid_seats">Vivid Seats</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters Expander -->
                <div x-data="{ expanded: false }" class="mt-4">
                    <button @click="expanded = !expanded"
                            class="flex items-center text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                        <span x-text="expanded ? 'Hide Advanced Filters' : 'Show Advanced Filters'"></span>
                        <svg class="w-4 h-4 ml-1 transform transition-transform" 
                             :class="{ 'rotate-180': expanded }" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Advanced Filters Content -->
                    <div x-show="expanded" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        
                        <!-- Venue -->
                        <div class="hdt-form-group">
                            <label class="hdt-label">Venue</label>
                            <input type="text" 
                                   placeholder="Enter venue name"
                                   class="hdt-input"
                                   x-model="filters.venue">
                        </div>

                        <!-- City -->
                        <div class="hdt-form-group">
                            <label class="hdt-label">City</label>
                            <input type="text" 
                                   placeholder="Enter city name"
                                   class="hdt-input"
                                   x-model="filters.city">
                        </div>

                        <!-- Availability -->
                        <div class="hdt-form-group">
                            <label class="hdt-label">Availability</label>
                            <select x-model="filters.availability" class="hdt-input">
                                <option value="">Any</option>
                                <option value="high">High (20+)</option>
                                <option value="medium">Medium (5-19)</option>
                                <option value="low">Low (1-4)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Active Filters -->
                <div x-show="hasActiveFilters()" class="mt-4 flex flex-wrap gap-2">
                    <template x-for="filter in getActiveFilters()" :key="filter.key">
                        <span class="hdt-badge hdt-badge--secondary hdt-badge--sm inline-flex items-center">
                            <span x-text="filter.label + ': ' + filter.value"></span>
                            <button @click="removeFilter(filter.key)"
                                    class="ml-1 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <!-- Results Header -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    <span x-text="filteredTickets.length"></span> Tickets Found
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Showing results from <span x-text="activePlatforms.length"></span> platforms
                </p>
            </div>

            <!-- Sort Options -->
            <div class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Sort by:</label>
                <select x-model="sortBy" @change="applySorting()" class="hdt-input hdt-input--sm">
                    <option value="relevance">Relevance</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                    <option value="date_asc">Date: Earliest First</option>
                    <option value="date_desc">Date: Latest First</option>
                    <option value="availability">Most Available</option>
                </select>
            </div>
        </div>

        <!-- Tickets Grid -->
        <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="ticket in filteredTickets" :key="ticket.id">
                <div class="hdt-ticket-card group cursor-pointer"
                     @click="viewTicketDetails(ticket)">
                    
                    <!-- Ticket Header -->
                    <div class="hdt-ticket-card__header relative">
                        <!-- Platform Badge -->
                        <div class="absolute top-2 right-2">
                            <span class="hdt-badge hdt-badge--info hdt-badge--xs" 
                                  x-text="ticket.platform"></span>
                        </div>
                        
                        <!-- Sport Category -->
                        <div class="hdt-ticket-card__sport">
                            <span class="hdt-badge hdt-badge--primary hdt-badge--xs" 
                                  x-text="ticket.sport"></span>
                        </div>
                        
                        <h4 class="hdt-ticket-card__title" x-text="ticket.title"></h4>
                        <p class="hdt-ticket-card__venue" x-text="ticket.venue + ' • ' + ticket.city"></p>
                        
                        <div class="hdt-ticket-card__meta">
                            <span x-text="formatDate(ticket.event_date)"></span>
                            <span x-text="ticket.event_time"></span>
                        </div>
                    </div>
                    
                    <!-- Ticket Body -->
                    <div class="hdt-ticket-card__body">
                        
                        <!-- Price Section -->
                        <div class="hdt-ticket-card__price">
                            <div class="hdt-ticket-card__price-current" x-text="'$' + ticket.price"></div>
                            <div x-show="ticket.price_change" 
                                 class="hdt-ticket-card__price-change"
                                 :class="ticket.price_trend === 'down' ? 'hdt-ticket-card__price-change--down' : 'hdt-ticket-card__price-change--up'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          :d="ticket.price_trend === 'down' ? 'M19 14l-7 7m0 0l-7-7m7 7V3' : 'M5 10l7-7m0 0l7 7m-7-7v18'"/>
                                </svg>
                                <span x-text="ticket.price_change"></span>
                            </div>
                        </div>
                        
                        <!-- Availability -->
                        <div class="hdt-ticket-card__availability">
                            <span x-text="ticket.available_tickets + ' available'"></span>
                            <span class="text-xs px-2 py-1 rounded-full"
                                  :class="getDemandClass(ticket.demand)"
                                  x-text="ticket.demand + ' demand'"></span>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="hdt-ticket-card__actions opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <button @click.stop="createAlert(ticket)" 
                                    class="hdt-button hdt-button--outline hdt-button--sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                                </svg>
                                Alert
                            </button>
                            <button @click.stop="addToComparison(ticket)" 
                                    class="hdt-button hdt-button--secondary hdt-button--sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Compare
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="i in 9" :key="i">
                <div class="hdt-card">
                    <div class="hdt-card__body">
                        <div class="animate-pulse">
                            <div class="hdt-skeleton hdt-skeleton--title mb-4"></div>
                            <div class="hdt-skeleton hdt-skeleton--text mb-2"></div>
                            <div class="hdt-skeleton hdt-skeleton--text mb-4"></div>
                            <div class="flex justify-between items-end">
                                <div class="hdt-skeleton h-6 w-16"></div>
                                <div class="hdt-skeleton h-8 w-20"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && filteredTickets.length === 0" class="text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No tickets found</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your search criteria or removing some filters</p>
            <button @click="resetFilters()" class="hdt-button hdt-button--primary hdt-button--md">
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('ticketDiscovery', () => ({
                loading: false,
                filters: {
                    search: '',
                    sport: '',
                    dateRange: '',
                    maxPrice: '',
                    platform: '',
                    venue: '',
                    city: '',
                    availability: ''
                },
                sortBy: 'relevance',
                tickets: [],
                filteredTickets: [],
                activePlatforms: ['Ticketmaster', 'StubHub', 'SeatGeek'],
                
                async init() {
                    await this.loadTickets();
                    this.applyFilters();
                },
                
                async loadTickets() {
                    this.loading = true;
                    
                    // Simulate API call - replace with actual API
                    this.tickets = [
                        {
                            id: 1,
                            title: 'Los Angeles Lakers vs Golden State Warriors',
                            sport: 'NBA',
                            venue: 'Crypto.com Arena',
                            city: 'Los Angeles',
                            event_date: '2024-12-25',
                            event_time: '8:00 PM',
                            platform: 'Ticketmaster',
                            price: 175,
                            price_change: '+$15',
                            price_trend: 'up',
                            available_tickets: 8,
                            demand: 'high'
                        },
                        {
                            id: 2,
                            title: 'Kansas City Chiefs vs Buffalo Bills',
                            sport: 'NFL',
                            venue: 'Arrowhead Stadium',
                            city: 'Kansas City',
                            event_date: '2025-01-15',
                            event_time: '3:00 PM',
                            platform: 'StubHub',
                            price: 185,
                            price_change: '-$20',
                            price_trend: 'down',
                            available_tickets: 12,
                            demand: 'high'
                        },
                        {
                            id: 3,
                            title: 'Boston Celtics vs Miami Heat',
                            sport: 'NBA',
                            venue: 'TD Garden',
                            city: 'Boston',
                            event_date: '2024-12-30',
                            event_time: '7:30 PM',
                            platform: 'SeatGeek',
                            price: 95,
                            price_change: null,
                            price_trend: 'stable',
                            available_tickets: 25,
                            demand: 'medium'
                        }
                    ];
                    
                    this.loading = false;
                },
                
                applyFilters() {
                    let filtered = [...this.tickets];
                    
                    // Apply search filter
                    if (this.filters.search) {
                        const search = this.filters.search.toLowerCase();
                        filtered = filtered.filter(ticket => 
                            ticket.title.toLowerCase().includes(search) ||
                            ticket.venue.toLowerCase().includes(search) ||
                            ticket.city.toLowerCase().includes(search)
                        );
                    }
                    
                    // Apply other filters
                    if (this.filters.sport) {
                        filtered = filtered.filter(ticket => 
                            ticket.sport.toLowerCase() === this.filters.sport.toLowerCase()
                        );
                    }
                    
                    if (this.filters.maxPrice) {
                        filtered = filtered.filter(ticket => 
                            ticket.price <= parseInt(this.filters.maxPrice)
                        );
                    }
                    
                    if (this.filters.platform) {
                        filtered = filtered.filter(ticket => 
                            ticket.platform.toLowerCase() === this.filters.platform.toLowerCase()
                        );
                    }
                    
                    if (this.filters.venue) {
                        filtered = filtered.filter(ticket => 
                            ticket.venue.toLowerCase().includes(this.filters.venue.toLowerCase())
                        );
                    }
                    
                    if (this.filters.city) {
                        filtered = filtered.filter(ticket => 
                            ticket.city.toLowerCase().includes(this.filters.city.toLowerCase())
                        );
                    }
                    
                    this.filteredTickets = filtered;
                    this.applySorting();
                },
                
                applySorting() {
                    switch (this.sortBy) {
                        case 'price_asc':
                            this.filteredTickets.sort((a, b) => a.price - b.price);
                            break;
                        case 'price_desc':
                            this.filteredTickets.sort((a, b) => b.price - a.price);
                            break;
                        case 'date_asc':
                            this.filteredTickets.sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
                            break;
                        case 'date_desc':
                            this.filteredTickets.sort((a, b) => new Date(b.event_date) - new Date(a.event_date));
                            break;
                        case 'availability':
                            this.filteredTickets.sort((a, b) => b.available_tickets - a.available_tickets);
                            break;
                    }
                },
                
                debouncedSearch() {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => {
                        this.applyFilters();
                    }, 300);
                },
                
                resetFilters() {
                    this.filters = {
                        search: '',
                        sport: '',
                        dateRange: '',
                        maxPrice: '',
                        platform: '',
                        venue: '',
                        city: '',
                        availability: ''
                    };
                    this.applyFilters();
                },
                
                hasActiveFilters() {
                    return Object.values(this.filters).some(value => value !== '');
                },
                
                getActiveFilters() {
                    const filters = [];
                    const filterLabels = {
                        search: 'Search',
                        sport: 'Sport',
                        dateRange: 'Date',
                        maxPrice: 'Max Price',
                        platform: 'Platform',
                        venue: 'Venue',
                        city: 'City',
                        availability: 'Availability'
                    };
                    
                    Object.entries(this.filters).forEach(([key, value]) => {
                        if (value) {
                            filters.push({
                                key,
                                label: filterLabels[key],
                                value
                            });
                        }
                    });
                    
                    return filters;
                },
                
                removeFilter(key) {
                    this.filters[key] = '';
                    this.applyFilters();
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                },
                
                getDemandClass(demand) {
                    switch (demand) {
                        case 'high':
                            return 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                        case 'medium':
                            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300';
                        case 'low':
                            return 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                        default:
                            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300';
                    }
                },
                
                viewTicketDetails(ticket) {
                    window.location.href = `/tickets/${ticket.id}`;
                },
                
                createAlert(ticket) {
                    window.location.href = `/alerts/create?ticket=${ticket.id}`;
                },
                
                addToComparison(ticket) {
                    // Add to comparison functionality
                    console.log('Adding to comparison:', ticket);
                }
            }));
        });
    </script>
@endsection