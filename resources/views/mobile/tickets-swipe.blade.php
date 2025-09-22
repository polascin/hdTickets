@extends('layouts.modern-app')

@section('title', 'Browse Tickets')

@section('meta_description', 'Mobile-optimized ticket browsing with swipe gestures and touch interactions')

@push('head')
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
@endpush

@section('page-header')
    <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-3">
            <button @click="$store.mobile.toggleFilters()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"/>
                </svg>
            </button>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">Browse Tickets</h1>
        </div>
        
        <div class="flex items-center space-x-2">
            <button @click="$store.mobile.toggleSearch()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>
            <button @click="$store.mobile.toggleView()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="$store.mobile.view === 'list'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z"/>
                </svg>
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="$store.mobile.view === 'cards'">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div x-data="mobileTickets()" x-init="init()" class="relative">
        
        <!-- Pull to Refresh Indicator -->
        <div x-show="pullToRefreshState.visible" 
             class="absolute top-0 left-0 right-0 z-10 flex items-center justify-center py-4 bg-blue-50 dark:bg-blue-900/20 border-b border-blue-200 dark:border-blue-800"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="transform -translate-y-full"
             x-transition:enter-end="transform translate-y-0">
            
            <div class="flex items-center space-x-2 text-blue-600 dark:text-blue-400">
                <div class="animate-spin" x-show="pullToRefreshState.loading">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div x-show="!pullToRefreshState.loading">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                    </svg>
                </div>
                <span class="text-sm font-medium" x-text="pullToRefreshState.message"></span>
            </div>
        </div>

        <!-- Mobile Search Overlay -->
        <div x-show="$store.mobile.showSearch" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-white dark:bg-gray-900">
            
            <div class="flex items-center p-4 border-b border-gray-200 dark:border-gray-700">
                <button @click="$store.mobile.toggleSearch()" class="mr-3 p-1">
                    <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </button>
                <input type="search" 
                       placeholder="Search events, teams, venues..."
                       class="flex-1 border-0 bg-transparent text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-0 focus:outline-none"
                       x-model="searchQuery"
                       @input="performSearch()"
                       x-ref="searchInput">
            </div>
            
            <div class="p-4">
                <div class="space-y-3">
                    <template x-for="result in searchResults" :key="result.id">
                        <div @click="selectSearchResult(result)" class="flex items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/50 rounded-lg flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white" x-text="result.title"></h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-text="result.venue + ' • ' + result.date"></p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-gray-900 dark:text-white">$<span x-text="result.price"></span></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Mobile Filter Bottom Sheet -->
        <div x-show="$store.mobile.showFilters" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black bg-opacity-50"
             @click="$store.mobile.toggleFilters()">
            
            <div @click.stop 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-y-full"
                 x-transition:enter-end="translate-y-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-y-0"
                 x-transition:leave-end="translate-y-full"
                 class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 rounded-t-lg shadow-xl max-h-[80vh] overflow-y-auto">
                
                <!-- Bottom Sheet Handle -->
                <div class="flex justify-center py-3">
                    <div class="w-12 h-1 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
                </div>
                
                <div class="px-4 pb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filters</h3>
                        <button @click="clearFilters()" class="text-blue-600 dark:text-blue-400 text-sm font-medium">
                            Clear All
                        </button>
                    </div>
                    
                    <!-- Sports Quick Filters -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Sports</h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="sport in popularSports" :key="sport.id">
                                <button @click="toggleSportFilter(sport.id)"
                                        :class="selectedSports.includes(sport.id) ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                        class="px-3 py-2 rounded-full text-sm font-medium transition-colors">
                                    <span x-text="sport.emoji"></span>
                                    <span x-text="sport.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Price Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">Price Range</h4>
                        <div class="space-y-3">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>$<span x-text="priceRange.min"></span></span>
                                <span>$<span x-text="priceRange.max"></span></span>
                            </div>
                            <input type="range" 
                                   x-model="priceRange.min" 
                                   min="0" max="1000" step="25"
                                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer">
                            <input type="range" 
                                   x-model="priceRange.max" 
                                   min="0" max="1000" step="25"
                                   class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer">
                        </div>
                    </div>
                    
                    <!-- Date Range -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-white mb-3">When</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <template x-for="period in datePeriods" :key="period.id">
                                <button @click="selectDatePeriod(period.id)"
                                        :class="selectedDatePeriod === period.id ? 'bg-blue-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                                        class="p-3 rounded-lg text-sm font-medium transition-colors">
                                    <span x-text="period.name"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Apply Filters Button -->
                    <button @click="applyFilters()" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Swipeable Ticket Cards Container -->
        <div class="pb-20" 
             x-ref="ticketContainer"
             @touchstart="handleTouchStart($event)"
             @touchmove="handleTouchMove($event)"
             @touchend="handleTouchEnd($event)">
            
            <template x-for="(ticket, index) in displayedTickets" :key="ticket.id">
                <div :id="'ticket-' + ticket.id"
                     class="relative bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 overflow-hidden"
                     :class="{ 'transform transition-transform duration-300': swipeState.activeTicket === ticket.id }"
                     :style="swipeState.activeTicket === ticket.id ? `transform: translateX(${swipeState.deltaX}px)` : ''">
                    
                    <!-- Swipe Action Backgrounds -->
                    <div class="absolute inset-y-0 left-0 w-20 bg-green-500 flex items-center justify-center"
                         :class="{ 'opacity-100': swipeState.deltaX > 50, 'opacity-0': swipeState.deltaX <= 50 }"
                         style="transition: opacity 0.2s;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    
                    <div class="absolute inset-y-0 right-0 w-20 bg-red-500 flex items-center justify-center"
                         :class="{ 'opacity-100': swipeState.deltaX < -50, 'opacity-0': swipeState.deltaX >= -50 }"
                         style="transition: opacity 0.2s;">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    
                    <!-- Ticket Content -->
                    <div class="relative bg-white dark:bg-gray-900 p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 pr-4">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/50 text-blue-800 dark:text-blue-300 text-xs font-medium rounded-full" 
                                          x-text="ticket.sport"></span>
                                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-xs font-medium rounded-full"
                                          x-text="ticket.platform"></span>
                                </div>
                                
                                <h3 class="font-semibold text-gray-900 dark:text-white text-lg mb-1" 
                                    x-text="ticket.title"></h3>
                                
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-2" 
                                   x-text="ticket.venue"></p>
                                
                                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span x-text="formatDate(ticket.date)"></span>
                                    <span x-text="ticket.time"></span>
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
                                    $<span x-text="ticket.price"></span>
                                </div>
                                <div class="flex items-center justify-end space-x-1 text-sm">
                                    <template x-if="ticket.priceChange">
                                        <span :class="ticket.priceChange.startsWith('+') ? 'text-red-500' : 'text-green-500'"
                                              x-text="ticket.priceChange"></span>
                                    </template>
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span x-text="ticket.availableTickets"></span> available
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Action Buttons -->
                        <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex space-x-2">
                                <button @click="toggleFavorite(ticket)" 
                                        :class="ticket.isFavorite ? 'text-red-500' : 'text-gray-400'"
                                        class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-5 h-5" :fill="ticket.isFavorite ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                    </svg>
                                </button>
                                <button @click="createAlert(ticket)" 
                                        class="p-2 rounded-full text-gray-400 hover:text-blue-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 14h16v-2H4v2zM4 9h16V7H4v2z"/>
                                    </svg>
                                </button>
                                <button @click="shareTicket(ticket)" 
                                        class="p-2 rounded-full text-gray-400 hover:text-green-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <button @click="viewTicket(ticket)" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            
            <!-- Loading Indicator -->
            <div x-show="loading" class="p-8 text-center">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-current border-t-transparent text-blue-600 rounded-full" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-gray-500 dark:text-gray-400">Loading more tickets...</p>
            </div>
            
            <!-- No Results -->
            <div x-show="!loading && displayedTickets.length === 0" class="p-8 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No tickets found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-4">Try adjusting your search or filters</p>
                <button @click="clearFilters()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Clear Filters
                </button>
            </div>
        </div>

        <!-- Floating Action Button -->
        <div class="fixed bottom-6 right-6 z-30">
            <button @click="scrollToTop()" 
                    x-show="showScrollTop"
                    x-transition:enter="transition ease-out duration-200 transform"
                    x-transition:enter-start="scale-0 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-150 transform"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-0 opacity-0"
                    class="w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Alpine.js Component -->
    <script>
        document.addEventListener('alpine:init', () => {
            // Mobile Store
            Alpine.store('mobile', {
                showSearch: false,
                showFilters: false,
                view: 'cards', // 'cards' or 'list'
                
                toggleSearch() {
                    this.showSearch = !this.showSearch;
                    if (this.showSearch) {
                        this.$nextTick(() => {
                            document.querySelector('input[x-ref="searchInput"]')?.focus();
                        });
                    }
                },
                
                toggleFilters() {
                    this.showFilters = !this.showFilters;
                },
                
                toggleView() {
                    this.view = this.view === 'cards' ? 'list' : 'cards';
                }
            });
            
            Alpine.data('mobileTickets', () => ({
                tickets: [],
                displayedTickets: [],
                loading: false,
                showScrollTop: false,
                searchQuery: '',
                searchResults: [],
                
                // Filters
                selectedSports: [],
                priceRange: { min: 0, max: 1000 },
                selectedDatePeriod: '',
                
                // Swipe State
                swipeState: {
                    startX: 0,
                    startY: 0,
                    deltaX: 0,
                    deltaY: 0,
                    activeTicket: null,
                    threshold: 50,
                    isVerticalScroll: false
                },
                
                // Pull to Refresh
                pullToRefreshState: {
                    visible: false,
                    loading: false,
                    message: 'Pull to refresh',
                    startY: 0,
                    deltaY: 0,
                    threshold: 80
                },
                
                popularSports: [
                    { id: 'nfl', name: 'NFL', emoji: '🏈' },
                    { id: 'nba', name: 'NBA', emoji: '🏀' },
                    { id: 'mlb', name: 'MLB', emoji: '⚾' },
                    { id: 'nhl', name: 'NHL', emoji: '🏒' },
                    { id: 'mls', name: 'MLS', emoji: '⚽' },
                    { id: 'ncaa', name: 'NCAA', emoji: '🎓' }
                ],
                
                datePeriods: [
                    { id: 'today', name: 'Today' },
                    { id: 'tomorrow', name: 'Tomorrow' },
                    { id: 'this_week', name: 'This Week' },
                    { id: 'this_month', name: 'This Month' },
                    { id: 'next_month', name: 'Next Month' },
                    { id: 'custom', name: 'Custom Range' }
                ],
                
                async init() {
                    await this.loadTickets();
                    this.setupScrollListener();
                    this.setupPullToRefresh();
                    
                    // Load more tickets when scrolling near bottom
                    this.setupInfiniteScroll();
                },
                
                async loadTickets(refresh = false) {
                    if (refresh) {
                        this.tickets = [];
                        this.displayedTickets = [];
                    }
                    
                    this.loading = true;
                    
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 1000));
                    
                    const newTickets = [
                        {
                            id: Date.now() + Math.random(),
                            title: 'Los Angeles Lakers vs Golden State Warriors',
                            venue: 'Crypto.com Arena, Los Angeles',
                            date: '2024-12-25',
                            time: '8:00 PM',
                            sport: 'NBA',
                            platform: 'Ticketmaster',
                            price: 175,
                            priceChange: '+$5',
                            availableTickets: 8,
                            isFavorite: false
                        },
                        {
                            id: Date.now() + Math.random() + 1,
                            title: 'Kansas City Chiefs vs Buffalo Bills',
                            venue: 'Arrowhead Stadium, Kansas City',
                            date: '2025-01-15',
                            time: '3:00 PM',
                            sport: 'NFL',
                            platform: 'StubHub',
                            price: 185,
                            priceChange: '-$20',
                            availableTickets: 12,
                            isFavorite: true
                        },
                        {
                            id: Date.now() + Math.random() + 2,
                            title: 'Boston Celtics vs Miami Heat',
                            venue: 'TD Garden, Boston',
                            date: '2024-12-30',
                            time: '7:30 PM',
                            sport: 'NBA',
                            platform: 'SeatGeek',
                            price: 95,
                            priceChange: null,
                            availableTickets: 25,
                            isFavorite: false
                        }
                    ];
                    
                    this.tickets.push(...newTickets);
                    this.displayedTickets = [...this.tickets];
                    this.loading = false;
                },
                
                setupScrollListener() {
                    let ticking = false;
                    
                    const handleScroll = () => {
                        if (!ticking) {
                            requestAnimationFrame(() => {
                                this.showScrollTop = window.scrollY > 400;
                                ticking = false;
                            });
                            ticking = true;
                        }
                    };
                    
                    window.addEventListener('scroll', handleScroll, { passive: true });
                },
                
                setupInfiniteScroll() {
                    const options = {
                        root: null,
                        rootMargin: '100px',
                        threshold: 0.1
                    };
                    
                    const observer = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting && !this.loading) {
                                this.loadTickets();
                            }
                        });
                    }, options);
                    
                    // Create sentinel element
                    const sentinel = document.createElement('div');
                    sentinel.style.height = '1px';
                    this.$refs.ticketContainer?.appendChild(sentinel);
                    observer.observe(sentinel);
                },
                
                setupPullToRefresh() {
                    let startY = 0;
                    let currentY = 0;
                    let pullDistance = 0;
                    
                    document.addEventListener('touchstart', (e) => {
                        if (window.scrollY === 0) {
                            startY = e.touches[0].pageY;
                        }
                    }, { passive: true });
                    
                    document.addEventListener('touchmove', (e) => {
                        if (window.scrollY === 0) {
                            currentY = e.touches[0].pageY;
                            pullDistance = currentY - startY;
                            
                            if (pullDistance > 0) {
                                this.pullToRefreshState.visible = pullDistance > 20;
                                this.pullToRefreshState.message = pullDistance > this.pullToRefreshState.threshold 
                                    ? 'Release to refresh' 
                                    : 'Pull to refresh';
                            }
                        }
                    }, { passive: true });
                    
                    document.addEventListener('touchend', async () => {
                        if (pullDistance > this.pullToRefreshState.threshold) {
                            this.pullToRefreshState.loading = true;
                            this.pullToRefreshState.message = 'Refreshing...';
                            
                            await this.loadTickets(true);
                            
                            setTimeout(() => {
                                this.pullToRefreshState.visible = false;
                                this.pullToRefreshState.loading = false;
                                this.pullToRefreshState.message = 'Pull to refresh';
                            }, 500);
                        } else {
                            this.pullToRefreshState.visible = false;
                        }
                        
                        pullDistance = 0;
                    }, { passive: true });
                },
                
                handleTouchStart(event) {
                    const touch = event.touches[0];
                    this.swipeState.startX = touch.pageX;
                    this.swipeState.startY = touch.pageY;
                    this.swipeState.deltaX = 0;
                    this.swipeState.deltaY = 0;
                    this.swipeState.isVerticalScroll = false;
                    
                    // Find the ticket being swiped
                    const ticketElement = event.target.closest('[id^="ticket-"]');
                    if (ticketElement) {
                        const ticketId = parseInt(ticketElement.id.replace('ticket-', ''));
                        this.swipeState.activeTicket = ticketId;
                    }
                },
                
                handleTouchMove(event) {
                    if (!this.swipeState.activeTicket) return;
                    
                    const touch = event.touches[0];
                    this.swipeState.deltaX = touch.pageX - this.swipeState.startX;
                    this.swipeState.deltaY = touch.pageY - this.swipeState.startY;
                    
                    // Determine if this is a vertical scroll
                    if (Math.abs(this.swipeState.deltaY) > Math.abs(this.swipeState.deltaX)) {
                        this.swipeState.isVerticalScroll = true;
                        return;
                    }
                    
                    // Prevent default scrolling if horizontal swipe
                    if (!this.swipeState.isVerticalScroll && Math.abs(this.swipeState.deltaX) > 10) {
                        event.preventDefault();
                    }
                },
                
                handleTouchEnd(event) {
                    if (!this.swipeState.activeTicket || this.swipeState.isVerticalScroll) {
                        this.resetSwipeState();
                        return;
                    }
                    
                    const ticket = this.tickets.find(t => t.id === this.swipeState.activeTicket);
                    
                    if (Math.abs(this.swipeState.deltaX) > this.swipeState.threshold) {
                        if (this.swipeState.deltaX > 0) {
                            // Swiped right - Add to favorites
                            this.toggleFavorite(ticket);
                            this.showToast('Added to favorites! ❤️');
                        } else {
                            // Swiped left - Remove/Hide
                            this.hideTicket(ticket);
                            this.showToast('Ticket hidden');
                        }
                    }
                    
                    this.resetSwipeState();
                },
                
                resetSwipeState() {
                    this.swipeState.activeTicket = null;
                    this.swipeState.deltaX = 0;
                    this.swipeState.deltaY = 0;
                    this.swipeState.isVerticalScroll = false;
                },
                
                performSearch() {
                    if (!this.searchQuery.trim()) {
                        this.searchResults = [];
                        return;
                    }
                    
                    // Simulate search results
                    this.searchResults = this.tickets.filter(ticket => 
                        ticket.title.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        ticket.venue.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                        ticket.sport.toLowerCase().includes(this.searchQuery.toLowerCase())
                    ).slice(0, 5);
                },
                
                selectSearchResult(result) {
                    this.$store.mobile.toggleSearch();
                    this.viewTicket(result);
                },
                
                toggleSportFilter(sportId) {
                    const index = this.selectedSports.indexOf(sportId);
                    if (index > -1) {
                        this.selectedSports.splice(index, 1);
                    } else {
                        this.selectedSports.push(sportId);
                    }
                },
                
                selectDatePeriod(periodId) {
                    this.selectedDatePeriod = this.selectedDatePeriod === periodId ? '' : periodId;
                },
                
                applyFilters() {
                    let filtered = [...this.tickets];
                    
                    // Apply sport filters
                    if (this.selectedSports.length > 0) {
                        filtered = filtered.filter(ticket => 
                            this.selectedSports.includes(ticket.sport.toLowerCase())
                        );
                    }
                    
                    // Apply price range
                    filtered = filtered.filter(ticket => 
                        ticket.price >= this.priceRange.min && ticket.price <= this.priceRange.max
                    );
                    
                    // Apply date period filter
                    if (this.selectedDatePeriod) {
                        // Implementation would depend on actual date logic
                    }
                    
                    this.displayedTickets = filtered;
                    this.$store.mobile.toggleFilters();
                },
                
                clearFilters() {
                    this.selectedSports = [];
                    this.priceRange = { min: 0, max: 1000 };
                    this.selectedDatePeriod = '';
                    this.displayedTickets = [...this.tickets];
                    this.$store.mobile.toggleFilters();
                },
                
                toggleFavorite(ticket) {
                    ticket.isFavorite = !ticket.isFavorite;
                },
                
                hideTicket(ticket) {
                    this.displayedTickets = this.displayedTickets.filter(t => t.id !== ticket.id);
                },
                
                createAlert(ticket) {
                    window.location.href = `/alerts/create?ticket=${ticket.id}`;
                },
                
                shareTicket(ticket) {
                    if (navigator.share) {
                        navigator.share({
                            title: ticket.title,
                            text: `Check out these tickets: ${ticket.title}`,
                            url: window.location.href
                        });
                    }
                },
                
                viewTicket(ticket) {
                    window.location.href = `/tickets/${ticket.id}`;
                },
                
                scrollToTop() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-US', { 
                        weekday: 'short', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                },
                
                showToast(message) {
                    if (window.showInfoToast) {
                        window.showInfoToast('', message, 2000);
                    }
                }
            }));
        });
    </script>
@endsection