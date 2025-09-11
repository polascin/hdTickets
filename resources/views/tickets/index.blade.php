@extends('layouts.modern')

@section('title', 'Sports Event Tickets - HD Tickets')

@section('meta')
    <meta name="description" content="Find and monitor sports event tickets from multiple platforms. Real-time price tracking, availability alerts, and seamless purchasing.">
    <meta name="keywords" content="sports tickets, event tickets, ticket monitoring, price alerts, {{ config('app.name') }}">
    <meta name="robots" content="index, follow">
@endsection

@push('styles')
    @vite(['resources/css/tickets.css'])
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .price-updated {
            animation: priceFlash 1s ease-in-out;
        }
        
        @keyframes priceFlash {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); background-color: #fef3cd; }
        }
        
        .filter-slide-enter {
            transform: translateX(-100%);
            opacity: 0;
        }
        
        .filter-slide-enter-active {
            transform: translateX(0);
            opacity: 1;
            transition: all 0.3s ease;
        }
        
        .search-suggestions {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        .sticky-filters {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Hero Section --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Find Your Perfect Sports Event Tickets
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    Monitor prices, track availability, and secure the best deals from multiple platforms
                </p>
                
                {{-- Quick Search Bar --}}
                <div class="max-w-2xl mx-auto">
                    <div class="relative">
                        <input type="text" 
                               id="hero-search" 
                               class="w-full px-6 py-4 text-gray-900 text-lg rounded-full shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-300 transition-all"
                               placeholder="Search for events, teams, or venues..."
                               autocomplete="off">
                        <button type="button" 
                                onclick="performHeroSearch()"
                                class="absolute right-2 top-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </button>
                        
                        {{-- Search Suggestions Dropdown --}}
                        <div id="hero-suggestions" 
                             class="absolute top-full left-0 right-0 mt-2 bg-white rounded-lg shadow-lg border border-gray-200 hidden z-50">
                            <div class="search-suggestions p-2">
                                {{-- Populated via JavaScript --}}
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Quick Stats --}}
                <div class="flex justify-center space-x-8 mt-8 text-blue-100">
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="total-tickets">{{ number_format($totalTickets ?? 0) }}</div>
                        <div class="text-sm">Active Tickets</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="platforms-count">{{ $platformsCount ?? 0 }}</div>
                        <div class="text-sm">Platforms</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" id="cities-count">{{ $citiesCount ?? 0 }}</div>
                        <div class="text-sm">Cities</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Main Content Area --}}
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            
            {{-- Sidebar Filters --}}
            <div class="lg:w-80 flex-shrink-0">
                <div class="sticky-filters lg:static bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-900">Filters</h2>
                        <button onclick="ticketFilters.clearAllFilters()" 
                                class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                                id="clear-filters-btn">
                            Clear All
                        </button>
                    </div>
                    
                    {{-- Search Input --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input type="text" 
                                   id="search-input" 
                                   class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                   placeholder="Event name, team, venue..."
                                   value="{{ request('search') }}">
                            <div id="search-suggestions" class="absolute top-full left-0 right-0 mt-1 bg-white rounded-md shadow-lg border border-gray-200 hidden z-50">
                                {{-- Populated via JavaScript --}}
                            </div>
                        </div>
                    </div>
                    
                    {{-- Sport Type Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sport Type</label>
                        <select id="sport-type-filter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Sports</option>
                            @foreach($sportTypes ?? [] as $sport)
                                <option value="{{ $sport->value }}" {{ request('sport_type') === $sport->value ? 'selected' : '' }}>
                                    {{ ucfirst($sport->value) }} ({{ $sport->count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- City Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                        <select id="city-filter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Cities</option>
                            @foreach($cities ?? [] as $city)
                                <option value="{{ $city->city }}" {{ request('city') === $city->city ? 'selected' : '' }}>
                                    {{ $city->city }} ({{ $city->count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Price Range Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="number" 
                                       id="price-min" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Min price"
                                       value="{{ request('price_min') }}"
                                       min="0"
                                       step="0.01">
                                <span class="text-gray-500">to</span>
                                <input type="number" 
                                       id="price-max" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Max price"
                                       value="{{ request('price_max') }}"
                                       min="0"
                                       step="0.01">
                            </div>
                            
                            {{-- Quick Price Filters --}}
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="ticketFilters.setPriceRange(0, 100)" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                    Under $100
                                </button>
                                <button onclick="ticketFilters.setPriceRange(100, 500)" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                    $100 - $500
                                </button>
                                <button onclick="ticketFilters.setPriceRange(500, 1000)" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                    $500 - $1K
                                </button>
                                <button onclick="ticketFilters.setPriceRange(1000, null)" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                                    Over $1K
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Date Range Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Event Date</label>
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <input type="date" 
                                       id="date-from" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="{{ request('date_from') }}">
                                <span class="text-gray-500">to</span>
                                <input type="date" 
                                       id="date-to" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       value="{{ request('date_to') }}">
                            </div>
                            
                            {{-- Quick Date Filters --}}
                            <div class="grid grid-cols-1 gap-2">
                                <button onclick="ticketFilters.setDateRange('today')" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors text-left">
                                    Today
                                </button>
                                <button onclick="ticketFilters.setDateRange('this_week')" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors text-left">
                                    This Week
                                </button>
                                <button onclick="ticketFilters.setDateRange('this_month')" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors text-left">
                                    This Month
                                </button>
                                <button onclick="ticketFilters.setDateRange('next_month')" 
                                        class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded-md transition-colors text-left">
                                    Next Month
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Platform Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Platform</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($platforms ?? [] as $platform)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           class="platform-filter"
                                           value="{{ $platform->name }}"
                                           {{ in_array($platform->name, request('platforms', [])) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">
                                        {{ $platform->name }} ({{ $platform->count }})
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Availability Filter --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Availability</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="availability" 
                                       value=""
                                       class="availability-filter"
                                       {{ request('availability') === '' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">All</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="availability" 
                                       value="available"
                                       class="availability-filter"
                                       {{ request('availability') === 'available' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Available</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="availability" 
                                       value="limited"
                                       class="availability-filter"
                                       {{ request('availability') === 'limited' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Limited</span>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Sort Options --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select id="sort-filter" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="relevance" {{ request('sort') === 'relevance' ? 'selected' : '' }}>Relevance</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Date: Earliest First</option>
                            <option value="date_desc" {{ request('sort') === 'date_desc' ? 'selected' : '' }}>Date: Latest First</option>
                            <option value="updated_desc" {{ request('sort') === 'updated_desc' ? 'selected' : '' }}>Recently Updated</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>
                    
                    {{-- Active Filters Summary --}}
                    <div id="active-filters" class="hidden">
                        <div class="border-t pt-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Active Filters</h3>
                            <div id="filter-tags" class="flex flex-wrap gap-2">
                                {{-- Populated via JavaScript --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Main Content --}}
            <div class="flex-1">
                {{-- Results Header --}}
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <div class="mb-4 sm:mb-0">
                        <h2 id="results-title" class="text-2xl font-semibold text-gray-900">
                            Sports Event Tickets
                        </h2>
                        <p id="results-count" class="text-gray-600">
                            @if(isset($tickets) && $tickets->count() > 0)
                                Showing {{ $tickets->count() }} of {{ $tickets->total() }} tickets
                            @else
                                Loading tickets...
                            @endif
                        </p>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        {{-- View Toggle --}}
                        <div class="flex items-center bg-gray-100 rounded-md p-1">
                            <button onclick="ticketFilters.setView('grid')" 
                                    id="grid-view-btn"
                                    class="p-2 rounded-md text-gray-600 hover:text-gray-900 transition-colors view-toggle active">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button onclick="ticketFilters.setView('list')" 
                                    id="list-view-btn"
                                    class="p-2 rounded-md text-gray-600 hover:text-gray-900 transition-colors view-toggle">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                        
                        {{-- Results Per Page --}}
                        <select id="per-page-filter" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="12" {{ request('per_page', 12) == 12 ? 'selected' : '' }}>12 per page</option>
                            <option value="24" {{ request('per_page', 12) == 24 ? 'selected' : '' }}>24 per page</option>
                            <option value="48" {{ request('per_page', 12) == 48 ? 'selected' : '' }}>48 per page</option>
                        </select>
                        
                        {{-- Export Options --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Export</span>
                            </button>
                            
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-200 z-10">
                                <div class="py-1">
                                    <button onclick="ticketFilters.exportResults('csv')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Export as CSV
                                    </button>
                                    <button onclick="ticketFilters.exportResults('json')" 
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Export as JSON
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                {{-- Loading Indicator --}}
                <div id="loading-indicator" class="hidden flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                    <span class="ml-3 text-gray-600">Loading tickets...</span>
                </div>
                
                {{-- Tickets Container --}}
                <div id="tickets-container">
                    @if(isset($tickets))
                        @include('tickets.partials.ticket-grid', ['tickets' => $tickets])
                    @else
                        {{-- Initial loading state --}}
                        <div class="text-center py-12">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                            <p class="mt-4 text-gray-600">Loading tickets...</p>
                        </div>
                    @endif
                </div>
                
                {{-- Pagination --}}
                <div id="pagination-container" class="mt-8">
                    @if(isset($tickets) && $tickets->hasPages())
                        <div class="flex justify-center">
                            {{ $tickets->appends(request()->query())->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Notification Toast Container --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

{{-- Share Modal --}}
<div id="share-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Share Ticket</h3>
            <button onclick="closeShareModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Share URL</label>
                <div class="flex items-center space-x-2">
                    <input type="text" 
                           id="share-url" 
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm"
                           readonly>
                    <button onclick="copyShareUrl()" 
                            class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                        Copy
                    </button>
                </div>
            </div>
            
            <div class="flex justify-center space-x-4">
                <button onclick="shareToFacebook()" 
                        class="p-3 bg-blue-600 hover:bg-blue-700 text-white rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M20 10C20 4.477 15.523 0 10 0S0 4.477 0 10c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V10h2.54V7.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V10h2.773l-.443 2.89h-2.33v6.988C16.343 19.128 20 14.991 20 10z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                
                <button onclick="shareToTwitter()" 
                        class="p-3 bg-blue-400 hover:bg-blue-500 text-white rounded-full transition-colors">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M6.29 18.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0020 3.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.073 4.073 0 01.8 7.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 010 16.407a11.616 11.616 0 006.29 1.84"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/tickets/TicketFilters.js', 'resources/js/tickets/PriceMonitor.js'])
    
    <script>
        // Configuration
        window.hdTicketsConfig = {
            apiEndpoint: '{{ url('/api/tickets') }}',
            enableAnalytics: {{ config('app.enable_analytics', false) ? 'true' : 'false' }},
            pusherKey: '{{ config('broadcasting.connections.pusher.key') }}',
            pusherCluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
            currentUserId: {{ auth()->id() ?? 'null' }}
        };
        
        // Initialize components
        let ticketFilters;
        let priceMonitor;
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize ticket filters
            ticketFilters = new TicketFilters({
                containerId: 'tickets-container',
                paginationId: 'pagination-container',
                loadingId: 'loading-indicator',
                apiEndpoint: window.hdTicketsConfig.apiEndpoint
            });
            
            // Initialize price monitor
            priceMonitor = new PriceMonitor({
                enableNotifications: true,
                enableSound: true
            });
            
            // Setup search functionality
            setupSearchFunctionality();
            
            // Setup bookmark functionality
            setupBookmarkFunctionality();
            
            // Setup share functionality
            setupShareFunctionality();
            
            console.log('HD Tickets initialized');
        });
        
        // Hero search functionality
        function performHeroSearch() {
            const query = document.getElementById('hero-search').value.trim();
            if (query) {
                document.getElementById('search-input').value = query;
                ticketFilters.applyFilters();
                
                // Scroll to results
                document.getElementById('tickets-container').scrollIntoView({ 
                    behavior: 'smooth' 
                });
            }
        }
        
        // Enhanced search with suggestions
        function setupSearchFunctionality() {
            const searchInput = document.getElementById('search-input');
            const heroSearch = document.getElementById('hero-search');
            const suggestionsContainer = document.getElementById('search-suggestions');
            const heroSuggestionsContainer = document.getElementById('hero-suggestions');
            
            let searchTimeout;
            
            // Setup search with debouncing
            [searchInput, heroSearch].forEach(input => {
                if (!input) return;
                
                input.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    if (query.length > 2) {
                        searchTimeout = setTimeout(() => {
                            fetchSearchSuggestions(query, this);
                        }, 300);
                    } else {
                        hideSuggestions();
                    }
                });
                
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (this.id === 'hero-search') {
                            performHeroSearch();
                        } else {
                            ticketFilters.applyFilters();
                        }
                        hideSuggestions();
                    }
                });
            });
            
            // Hide suggestions on outside click
            document.addEventListener('click', function(e) {
                if (!e.target.closest('#search-input') && !e.target.closest('#hero-search') && 
                    !e.target.closest('#search-suggestions') && !e.target.closest('#hero-suggestions')) {
                    hideSuggestions();
                }
            });
        }
        
        // Fetch search suggestions
        async function fetchSearchSuggestions(query, inputElement) {
            try {
                const response = await fetch(`{{ url('/api/tickets/suggestions') }}?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success) {
                    displaySuggestions(data.suggestions, inputElement);
                }
            } catch (error) {
                console.error('Failed to fetch suggestions:', error);
            }
        }
        
        // Display search suggestions
        function displaySuggestions(suggestions, inputElement) {
            const isHeroSearch = inputElement.id === 'hero-search';
            const container = isHeroSearch ? 
                document.getElementById('hero-suggestions') : 
                document.getElementById('search-suggestions');
            
            if (!container || suggestions.length === 0) {
                hideSuggestions();
                return;
            }
            
            const suggestionsHtml = suggestions.map(suggestion => 
                `<div class="px-3 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                      onclick="selectSuggestion('${suggestion.text.replace(/'/g, "\\'")}', '${inputElement.id}')">
                    <div class="font-medium text-gray-900">${suggestion.text}</div>
                    <div class="text-sm text-gray-500">${suggestion.category} • ${suggestion.count} tickets</div>
                </div>`
            ).join('');
            
            container.querySelector('.search-suggestions').innerHTML = suggestionsHtml;
            container.classList.remove('hidden');
        }
        
        // Select suggestion
        function selectSuggestion(text, inputId) {
            const input = document.getElementById(inputId);
            input.value = text;
            
            if (inputId === 'hero-search') {
                performHeroSearch();
            } else {
                ticketFilters.applyFilters();
            }
            
            hideSuggestions();
        }
        
        // Hide suggestions
        function hideSuggestions() {
            document.getElementById('search-suggestions')?.classList.add('hidden');
            document.getElementById('hero-suggestions')?.classList.add('hidden');
        }
        
        // Setup bookmark functionality
        function setupBookmarkFunctionality() {
            document.addEventListener('click', function(e) {
                if (e.target.closest('.bookmark-toggle')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const button = e.target.closest('.bookmark-toggle');
                    const ticketId = button.dataset.ticketId;
                    
                    toggleBookmark(ticketId, button);
                }
            });
        }
        
        // Toggle bookmark
        async function toggleBookmark(ticketId, button) {
            if (!window.hdTicketsConfig.currentUserId) {
                showToast('Please login to bookmark tickets', 'warning');
                return;
            }
            
            try {
                const response = await fetch(`{{ url('/api/tickets') }}/${ticketId}/bookmark`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateBookmarkButton(button, data.bookmarked);
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Failed to update bookmark', 'error');
                }
            } catch (error) {
                console.error('Bookmark error:', error);
                showToast('Failed to update bookmark', 'error');
            }
        }
        
        // Update bookmark button appearance
        function updateBookmarkButton(button, isBookmarked) {
            const svg = button.querySelector('svg');
            
            if (isBookmarked) {
                button.classList.remove('text-gray-400');
                button.classList.add('text-yellow-500');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('text-yellow-500');
                button.classList.add('text-gray-400');
                svg.setAttribute('fill', 'none');
            }
        }
        
        // Setup share functionality
        function setupShareFunctionality() {
            document.addEventListener('click', function(e) {
                if (e.target.closest('.share-button')) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const button = e.target.closest('.share-button');
                    const title = button.dataset.title;
                    const url = button.dataset.url;
                    
                    openShareModal(title, url);
                }
            });
        }
        
        // Open share modal
        function openShareModal(title, url) {
            document.getElementById('share-url').value = url;
            document.getElementById('share-modal').classList.remove('hidden');
            
            // Store for sharing functions
            window.shareData = { title, url };
        }
        
        // Close share modal
        function closeShareModal() {
            document.getElementById('share-modal').classList.add('hidden');
        }
        
        // Copy share URL
        async function copyShareUrl() {
            const urlInput = document.getElementById('share-url');
            
            try {
                await navigator.clipboard.writeText(urlInput.value);
                showToast('URL copied to clipboard', 'success');
            } catch (error) {
                // Fallback for older browsers
                urlInput.select();
                document.execCommand('copy');
                showToast('URL copied to clipboard', 'success');
            }
        }
        
        // Share to Facebook
        function shareToFacebook() {
            if (window.shareData) {
                const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.shareData.url)}`;
                window.open(url, '_blank', 'width=600,height=400');
            }
        }
        
        // Share to Twitter
        function shareToTwitter() {
            if (window.shareData) {
                const text = `Check out this sports ticket: ${window.shareData.title}`;
                const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(window.shareData.url)}`;
                window.open(url, '_blank', 'width=600,height=400');
            }
        }
        
        // Toast notification system
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            const id = Date.now();
            
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            toast.className = `px-4 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full ${colors[type]}`;
            toast.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="removeToast(${id})" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;
            
            toast.dataset.toastId = id;
            container.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 50);
            
            // Auto remove
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }
        
        // Remove toast
        function removeToast(id) {
            const toast = document.querySelector(`[data-toast-id="${id}"]`);
            if (toast) {
                toast.classList.add('translate-x-full');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }
        }
        
        // Handle page visibility changes for performance
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Pause any intensive operations
                if (window.priceMonitor) {
                    window.priceMonitor.pauseMonitoring();
                }
            } else {
                // Resume operations
                if (window.priceMonitor) {
                    window.priceMonitor.resumeMonitoring();
                }
            }
        });
    </script>
@endpush
