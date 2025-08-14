@props([
    'filters' => [],
    'activeFilters' => [],
    'sportCategories' => [],
    'priceRanges' => [],
    'dateRanges' => [],
    'venues' => [],
    'showQuickFilters' => true,
    'showAdvancedFilters' => false,
    'position' => 'sticky' // sticky, fixed, relative
])

@php
    $filterId = 'touch-filters-' . uniqid();
    
    $leagueLogos = [
        'NFL' => ['emoji' => '🏈', 'color' => 'bg-orange-100 text-orange-800 border-orange-200'],
        'NBA' => ['emoji' => '🏀', 'color' => 'bg-orange-100 text-orange-800 border-orange-200'],
        'MLB' => ['emoji' => '⚾', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'NHL' => ['emoji' => '🏒', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'Premier League' => ['emoji' => '⚽', 'color' => 'bg-green-100 text-green-800 border-green-200'],
        'UEFA' => ['emoji' => '⚽', 'color' => 'bg-purple-100 text-purple-800 border-purple-200'],
        'Champions League' => ['emoji' => '⚽', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'La Liga' => ['emoji' => '⚽', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
        'Serie A' => ['emoji' => '⚽', 'color' => 'bg-green-100 text-green-800 border-green-200'],
        'Bundesliga' => ['emoji' => '⚽', 'color' => 'bg-red-100 text-red-800 border-red-200'],
        'Tennis' => ['emoji' => '🎾', 'color' => 'bg-green-100 text-green-800 border-green-200'],
        'F1' => ['emoji' => '🏎️', 'color' => 'bg-red-100 text-red-800 border-red-200'],
        'Boxing' => ['emoji' => '🥊', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'],
        'MMA' => ['emoji' => '🥊', 'color' => 'bg-red-100 text-red-800 border-red-200'],
        'Cricket' => ['emoji' => '🏏', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
        'Golf' => ['emoji' => '⛳', 'color' => 'bg-green-100 text-green-800 border-green-200'],
        'Olympics' => ['emoji' => '🏅', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200'],
        'Rugby' => ['emoji' => '🏉', 'color' => 'bg-green-100 text-green-800 border-green-200']
    ];

    $priceRangeOptions = [
        ['label' => 'Under $50', 'value' => '0-50', 'icon' => '💰'],
        ['label' => '$50 - $100', 'value' => '50-100', 'icon' => '💵'],
        ['label' => '$100 - $200', 'value' => '100-200', 'icon' => '💸'],
        ['label' => '$200 - $500', 'value' => '200-500', 'icon' => '💳'],
        ['label' => 'Over $500', 'value' => '500+', 'icon' => '🏆']
    ];

    $dateRangeOptions = [
        ['label' => 'Today', 'value' => 'today', 'icon' => '📅'],
        ['label' => 'This Week', 'value' => 'this_week', 'icon' => '📆'],
        ['label' => 'This Month', 'value' => 'this_month', 'icon' => '🗓️'],
        ['label' => 'Next 3 Months', 'value' => 'next_3_months', 'icon' => '📊']
    ];
@endphp

<div 
    class="touch-filter-controls {{ $position }} top-0 left-0 right-0 bg-white border-b border-gray-200 z-30"
    data-filter-id="{{ $filterId }}"
    data-position="{{ $position }}"
>
    <!-- Filter Header -->
    <div class="flex items-center justify-between px-4 py-3 md:px-6">
        <div class="flex items-center space-x-3">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                </svg>
                Filters
            </h3>
            
            <!-- Active Filter Count -->
            <div class="active-filter-count hidden bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded-full">
                <span class="count">0</span> active
            </div>
        </div>
        
        <div class="flex items-center space-x-2">
            <!-- Clear All Filters -->
            <button 
                class="clear-filters-btn hidden text-sm text-gray-600 hover:text-gray-900 px-3 py-1 rounded-lg hover:bg-gray-100 transition-colors touch-target"
                data-clear-filters="{{ $filterId }}"
            >
                Clear All
            </button>
            
            <!-- Advanced Filters Toggle -->
            <button 
                class="advanced-toggle-btn bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg font-medium text-sm transition-colors touch-target flex items-center"
                data-toggle-advanced="{{ $filterId }}"
                aria-label="Toggle advanced filters"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                <span class="toggle-text">Advanced</span>
            </button>
        </div>
    </div>

    <!-- Quick Filters (Always Visible) -->
    @if($showQuickFilters)
        <div class="quick-filters px-4 py-3 border-t border-gray-100 md:px-6">
            <!-- Sports Categories -->
            <div class="filter-group mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <span class="text-lg mr-2">🏆</span>
                    Sports
                </h4>
                <div class="flex flex-wrap gap-2">
                    @forelse($sportCategories as $category)
                        @php
                            $logoInfo = $leagueLogos[$category->name] ?? ['emoji' => '🏆', 'color' => 'bg-gray-100 text-gray-800 border-gray-200'];
                            $isActive = in_array($category->slug ?? $category->name, $activeFilters);
                        @endphp
                        <button 
                            class="sport-filter-btn touch-target flex items-center px-3 py-2 rounded-xl border-2 font-medium text-sm transition-all duration-200 {{ $isActive ? 'border-blue-500 bg-blue-50 text-blue-700' : $logoInfo['color'] }} hover:scale-105 active:scale-95"
                            data-filter-type="sport"
                            data-filter-value="{{ $category->slug ?? $category->name }}"
                            data-filter-label="{{ $category->name }}"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                        >
                            <span class="text-lg mr-2">{{ $logoInfo['emoji'] }}</span>
                            <span>{{ $category->name }}</span>
                            @if(isset($category->ticket_count))
                                <span class="ml-2 bg-white bg-opacity-70 text-xs px-2 py-0.5 rounded-full">
                                    {{ $category->ticket_count }}
                                </span>
                            @endif
                        </button>
                    @empty
                        <p class="text-sm text-gray-500 italic">No sports categories available</p>
                    @endforelse
                </div>
            </div>

            <!-- Price Range Quick Filters -->
            <div class="filter-group mb-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <span class="text-lg mr-2">💰</span>
                    Price Range
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($priceRangeOptions as $range)
                        @php
                            $isActive = in_array($range['value'], $activeFilters);
                        @endphp
                        <button 
                            class="price-filter-btn touch-target flex items-center px-3 py-2 rounded-xl border-2 font-medium text-sm transition-all duration-200 {{ $isActive ? 'border-green-500 bg-green-50 text-green-700' : 'bg-gray-100 text-gray-800 border-gray-200' }} hover:scale-105 active:scale-95"
                            data-filter-type="price"
                            data-filter-value="{{ $range['value'] }}"
                            data-filter-label="{{ $range['label'] }}"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                        >
                            <span class="text-lg mr-2">{{ $range['icon'] }}</span>
                            <span>{{ $range['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Date Range Quick Filters -->
            <div class="filter-group">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <span class="text-lg mr-2">📅</span>
                    When
                </h4>
                <div class="flex flex-wrap gap-2">
                    @foreach($dateRangeOptions as $range)
                        @php
                            $isActive = in_array($range['value'], $activeFilters);
                        @endphp
                        <button 
                            class="date-filter-btn touch-target flex items-center px-3 py-2 rounded-xl border-2 font-medium text-sm transition-all duration-200 {{ $isActive ? 'border-purple-500 bg-purple-50 text-purple-700' : 'bg-gray-100 text-gray-800 border-gray-200' }} hover:scale-105 active:scale-95"
                            data-filter-type="date"
                            data-filter-value="{{ $range['value'] }}"
                            data-filter-label="{{ $range['label'] }}"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                        >
                            <span class="text-lg mr-2">{{ $range['icon'] }}</span>
                            <span>{{ $range['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Advanced Filters (Collapsible) -->
    <div class="advanced-filters hidden border-t border-gray-100" data-advanced-filters="{{ $filterId }}">
        <div class="px-4 py-4 space-y-4 md:px-6">
            <!-- Venue Filter -->
            @if(count($venues) > 0)
                <div class="filter-group">
                    <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Venues
                    </h4>
                    <div class="relative">
                        <select 
                            class="venue-filter-select w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white touch-target"
                            data-filter-type="venue"
                            multiple
                        >
                            <option value="">All Venues</option>
                            @foreach($venues as $venue)
                                <option 
                                    value="{{ $venue->slug ?? $venue->name }}"
                                    {{ in_array($venue->slug ?? $venue->name, $activeFilters) ? 'selected' : '' }}
                                >
                                    {{ $venue->name }} @if(isset($venue->city))({{ $venue->city }})@endif
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Custom Price Range -->
            <div class="filter-group">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                    Custom Price Range
                </h4>
                <div class="flex items-center space-x-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Min Price</label>
                        <input 
                            type="number" 
                            class="price-min-input w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 touch-target"
                            placeholder="$0"
                            min="0"
                            data-filter-type="price_min"
                        >
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">Max Price</label>
                        <input 
                            type="number" 
                            class="price-max-input w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 touch-target"
                            placeholder="$1000"
                            min="0"
                            data-filter-type="price_max"
                        >
                    </div>
                </div>
            </div>

            <!-- Custom Date Range -->
            <div class="filter-group">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Custom Date Range
                </h4>
                <div class="flex items-center space-x-3">
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">From</label>
                        <input 
                            type="date" 
                            class="date-from-input w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 touch-target"
                            data-filter-type="date_from"
                        >
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-gray-600 mb-1">To</label>
                        <input 
                            type="date" 
                            class="date-to-input w-full px-3 py-2 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 touch-target"
                            data-filter-type="date_to"
                        >
                    </div>
                </div>
            </div>

            <!-- Availability Filter -->
            <div class="filter-group">
                <h4 class="text-sm font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Availability
                </h4>
                <div class="flex flex-wrap gap-2">
                    @php
                        $availabilityOptions = [
                            ['value' => 'available', 'label' => 'Available', 'color' => 'bg-green-100 text-green-800 border-green-200', 'icon' => '✅'],
                            ['value' => 'limited', 'label' => 'Limited', 'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200', 'icon' => '⚠️'],
                            ['value' => 'sold_out', 'label' => 'Sold Out', 'color' => 'bg-red-100 text-red-800 border-red-200', 'icon' => '❌']
                        ];
                    @endphp
                    @foreach($availabilityOptions as $option)
                        @php
                            $isActive = in_array($option['value'], $activeFilters);
                        @endphp
                        <button 
                            class="availability-filter-btn touch-target flex items-center px-3 py-2 rounded-xl border-2 font-medium text-sm transition-all duration-200 {{ $isActive ? 'border-blue-500 bg-blue-50 text-blue-700' : $option['color'] }} hover:scale-105 active:scale-95"
                            data-filter-type="availability"
                            data-filter-value="{{ $option['value'] }}"
                            data-filter-label="{{ $option['label'] }}"
                            aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                        >
                            <span class="mr-2">{{ $option['icon'] }}</span>
                            <span>{{ $option['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Apply/Reset Buttons -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <button 
                    class="reset-advanced-btn text-gray-600 hover:text-gray-900 px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors font-medium touch-target"
                    data-reset-advanced="{{ $filterId }}"
                >
                    Reset Advanced
                </button>
                <button 
                    class="apply-filters-btn bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors touch-target"
                    data-apply-filters="{{ $filterId }}"
                >
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ now()->timestamp }}">
<style>
/* Touch Filter Controls Styles */
.touch-filter-controls {
    /* Safe area support for devices with notches */
    padding-top: env(safe-area-inset-top, 0);
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

/* Enhanced touch targets */
.touch-target {
    min-height: 44px;
    min-width: 44px;
    touch-action: manipulation;
    user-select: none;
    -webkit-touch-callout: none;
}

/* Filter button animations */
.sport-filter-btn,
.price-filter-btn,
.date-filter-btn,
.availability-filter-btn {
    position: relative;
    overflow: hidden;
}

.sport-filter-btn::before,
.price-filter-btn::before,
.date-filter-btn::before,
.availability-filter-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.sport-filter-btn:hover::before,
.price-filter-btn:hover::before,
.date-filter-btn:hover::before,
.availability-filter-btn:hover::before {
    left: 100%;
}

/* Active state styles */
.sport-filter-btn[aria-pressed="true"],
.price-filter-btn[aria-pressed="true"],
.date-filter-btn[aria-pressed="true"],
.availability-filter-btn[aria-pressed="true"] {
    transform: scale(0.95);
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Input field enhancements for mobile */
input[type="number"],
input[type="date"],
select {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    font-size: 16px; /* Prevents zoom on iOS */
}

/* Multi-select styling */
select[multiple] {
    background-image: none;
    min-height: 120px;
    padding: 8px;
}

select[multiple] option {
    padding: 8px;
    margin: 2px 0;
    border-radius: 4px;
}

select[multiple] option:checked {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

/* Advanced filters animation */
.advanced-filters {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.advanced-filters.show {
    max-height: 1000px;
}

/* Filter group spacing */
.filter-group {
    opacity: 0;
    transform: translateY(10px);
    animation: fadeInUp 0.3s ease-out forwards;
}

.filter-group:nth-child(2) { animation-delay: 0.1s; }
.filter-group:nth-child(3) { animation-delay: 0.2s; }
.filter-group:nth-child(4) { animation-delay: 0.3s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Active filter count badge */
.active-filter-count {
    animation: bounceIn 0.3s ease-out;
}

@keyframes bounceIn {
    0% {
        transform: scale(0.3);
        opacity: 0;
    }
    50% {
        transform: scale(1.1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Loading state for apply button */
.apply-filters-btn.loading {
    position: relative;
    color: transparent;
}

.apply-filters-btn.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 16px;
    height: 16px;
    margin: -8px 0 0 -8px;
    border: 2px solid transparent;
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Sticky position adjustments */
.touch-filter-controls[data-position="sticky"] {
    position: sticky;
    top: 0;
    z-index: 30;
}

.touch-filter-controls[data-position="fixed"] {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 40;
}

/* Mobile-specific optimizations */
@media (max-width: 768px) {
    .touch-filter-controls {
        font-size: 14px;
    }
    
    .filter-group h4 {
        font-size: 13px;
    }
    
    .sport-filter-btn,
    .price-filter-btn,
    .date-filter-btn,
    .availability-filter-btn {
        font-size: 13px;
        padding: 8px 12px;
    }
    
    /* Horizontal scrolling for filter buttons on small screens */
    .filter-group .flex {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .filter-group .flex::-webkit-scrollbar {
        display: none;
    }
    
    .filter-group .flex {
        flex-wrap: nowrap;
        gap: 8px;
        padding-bottom: 8px;
    }
    
    .sport-filter-btn,
    .price-filter-btn,
    .date-filter-btn,
    .availability-filter-btn {
        flex-shrink: 0;
        white-space: nowrap;
    }
}

/* Tablet adjustments */
@media (min-width: 768px) and (max-width: 1024px) {
    .touch-filter-controls {
        padding: 16px 24px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .touch-filter-controls {
        background: rgba(17, 24, 39, 0.95);
        border-color: #374151;
    }
    
    .sport-filter-btn,
    .price-filter-btn,
    .date-filter-btn,
    .availability-filter-btn {
        border-color: #4b5563;
        background: #374151;
        color: #f9fafb;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .sport-filter-btn,
    .price-filter-btn,
    .date-filter-btn,
    .availability-filter-btn {
        border-width: 3px;
    }
    
    .touch-filter-controls {
        border-width: 2px;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .sport-filter-btn,
    .price-filter-btn,
    .date-filter-btn,
    .availability-filter-btn,
    .advanced-filters,
    .filter-group {
        transition: none;
        animation: none;
    }
    
    .sport-filter-btn::before,
    .price-filter-btn::before,
    .date-filter-btn::before,
    .availability-filter-btn::before {
        display: none;
    }
}

/* Focus indicators for keyboard navigation */
.sport-filter-btn:focus,
.price-filter-btn:focus,
.date-filter-btn:focus,
.availability-filter-btn:focus,
input:focus,
select:focus,
button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .touch-filter-controls {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterId = '{{ $filterId }}';
    const filterContainer = document.querySelector(`[data-filter-id="${filterId}"]`);
    
    if (!filterContainer) return;

    // State management
    let activeFilters = @json($activeFilters);
    let filterState = {};

    // UI Elements
    const advancedToggle = filterContainer.querySelector(`[data-toggle-advanced="${filterId}"]`);
    const advancedFilters = filterContainer.querySelector(`[data-advanced-filters="${filterId}"]`);
    const clearAllBtn = filterContainer.querySelector(`[data-clear-filters="${filterId}"]`);
    const applyBtn = filterContainer.querySelector(`[data-apply-filters="${filterId}"]`);
    const resetAdvancedBtn = filterContainer.querySelector(`[data-reset-advanced="${filterId}"]`);
    const activeCountBadge = filterContainer.querySelector('.active-filter-count');
    const countSpan = activeCountBadge?.querySelector('.count');

    // Initialize
    initializeFilters();
    updateActiveCount();
    updateClearButtonVisibility();

    function initializeFilters() {
        // Quick filter buttons
        const quickFilterBtns = filterContainer.querySelectorAll('[data-filter-type]');
        quickFilterBtns.forEach(btn => {
            btn.addEventListener('click', handleQuickFilterClick);
        });

        // Advanced toggle
        advancedToggle?.addEventListener('click', toggleAdvancedFilters);

        // Clear all filters
        clearAllBtn?.addEventListener('click', clearAllFilters);

        // Apply filters
        applyBtn?.addEventListener('click', applyFilters);

        // Reset advanced filters
        resetAdvancedBtn?.addEventListener('click', resetAdvancedFilters);

        // Custom input handlers
        const customInputs = filterContainer.querySelectorAll('input[type="number"], input[type="date"], select');
        customInputs.forEach(input => {
            input.addEventListener('change', handleCustomInputChange);
        });

        // Initialize filter state from active filters
        activeFilters.forEach(filter => {
            const btn = filterContainer.querySelector(`[data-filter-value="${filter}"]`);
            if (btn) {
                btn.setAttribute('aria-pressed', 'true');
                btn.classList.add('active');
                filterState[btn.dataset.filterType] = filterState[btn.dataset.filterType] || [];
                filterState[btn.dataset.filterType].push(filter);
            }
        });
    }

    function handleQuickFilterClick(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        const filterType = btn.dataset.filterType;
        const filterValue = btn.dataset.filterValue;
        const isActive = btn.getAttribute('aria-pressed') === 'true';

        // Toggle button state
        if (isActive) {
            btn.setAttribute('aria-pressed', 'false');
            btn.classList.remove('active');
            removeFilter(filterType, filterValue);
        } else {
            // For single-select filters (like some categories), remove others first
            if (['sport'].includes(filterType)) {
                // Allow multiple sport selections
            }
            
            btn.setAttribute('aria-pressed', 'true');
            btn.classList.add('active');
            addFilter(filterType, filterValue);
        }

        // Add visual feedback
        btn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            btn.style.transform = '';
        }, 100);

        updateActiveCount();
        updateClearButtonVisibility();

        // Auto-apply quick filters (optional - can be disabled)
        if (window.innerWidth <= 768) {
            debounce(applyFilters, 1000)();
        }
    }

    function handleCustomInputChange(e) {
        const input = e.currentTarget;
        const filterType = input.dataset.filterType;
        const value = input.value;

        if (value) {
            addFilter(filterType, value);
        } else {
            removeFilter(filterType);
        }

        updateActiveCount();
        updateClearButtonVisibility();
    }

    function toggleAdvancedFilters() {
        const isOpen = advancedFilters.classList.contains('show');
        const toggleText = advancedToggle.querySelector('.toggle-text');
        
        if (isOpen) {
            advancedFilters.classList.remove('show');
            advancedFilters.classList.add('hidden');
            toggleText.textContent = 'Advanced';
        } else {
            advancedFilters.classList.remove('hidden');
            advancedFilters.classList.add('show');
            toggleText.textContent = 'Basic';
        }

        // Animate button rotation
        const icon = advancedToggle.querySelector('svg');
        icon.style.transform = isOpen ? '' : 'rotate(180deg)';
    }

    function clearAllFilters() {
        // Reset all filter buttons
        const allFilterBtns = filterContainer.querySelectorAll('[data-filter-type]');
        allFilterBtns.forEach(btn => {
            btn.setAttribute('aria-pressed', 'false');
            btn.classList.remove('active');
        });

        // Reset custom inputs
        const customInputs = filterContainer.querySelectorAll('input[type="number"], input[type="date"]');
        customInputs.forEach(input => {
            input.value = '';
        });

        // Reset selects
        const selects = filterContainer.querySelectorAll('select');
        selects.forEach(select => {
            select.selectedIndex = 0;
            if (select.multiple) {
                Array.from(select.options).forEach(option => {
                    option.selected = false;
                });
            }
        });

        // Clear state
        filterState = {};
        activeFilters = [];

        updateActiveCount();
        updateClearButtonVisibility();

        // Apply cleared filters
        applyFilters();
    }

    function resetAdvancedFilters() {
        // Reset only advanced filter inputs
        const advancedInputs = advancedFilters.querySelectorAll('input, select');
        advancedInputs.forEach(input => {
            if (input.type === 'number' || input.type === 'date') {
                input.value = '';
            } else if (input.tagName === 'SELECT') {
                if (input.multiple) {
                    Array.from(input.options).forEach(option => {
                        option.selected = false;
                    });
                } else {
                    input.selectedIndex = 0;
                }
            }
        });

        // Remove advanced filters from state
        delete filterState.venue;
        delete filterState.price_min;
        delete filterState.price_max;
        delete filterState.date_from;
        delete filterState.date_to;

        updateActiveCount();
        updateClearButtonVisibility();
    }

    function addFilter(type, value) {
        if (!filterState[type]) {
            filterState[type] = [];
        }
        
        if (!filterState[type].includes(value)) {
            filterState[type].push(value);
        }
        
        if (!activeFilters.includes(value)) {
            activeFilters.push(value);
        }
    }

    function removeFilter(type, value) {
        if (filterState[type]) {
            if (value) {
                filterState[type] = filterState[type].filter(v => v !== value);
                if (filterState[type].length === 0) {
                    delete filterState[type];
                }
            } else {
                delete filterState[type];
            }
        }
        
        if (value) {
            activeFilters = activeFilters.filter(f => f !== value);
        }
    }

    function updateActiveCount() {
        const count = activeFilters.length;
        
        if (count > 0) {
            countSpan.textContent = count;
            activeCountBadge.classList.remove('hidden');
        } else {
            activeCountBadge.classList.add('hidden');
        }
    }

    function updateClearButtonVisibility() {
        const hasActiveFilters = activeFilters.length > 0;
        
        if (hasActiveFilters) {
            clearAllBtn.classList.remove('hidden');
        } else {
            clearAllBtn.classList.add('hidden');
        }
    }

    function applyFilters() {
        // Show loading state
        applyBtn.classList.add('loading');
        applyBtn.disabled = true;

        // Prepare filter data
        const filterData = {
            ...filterState,
            _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        };

        // Build URL with filters
        const url = new URL(window.location);
        
        // Clear existing filter params
        url.searchParams.delete('category');
        url.searchParams.delete('price_min');
        url.searchParams.delete('price_max');
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        url.searchParams.delete('venue');
        url.searchParams.delete('availability');

        // Add active filters
        Object.entries(filterState).forEach(([type, values]) => {
            if (Array.isArray(values)) {
                values.forEach(value => {
                    if (type === 'sport') {
                        url.searchParams.append('category', value);
                    } else {
                        url.searchParams.append(type, value);
                    }
                });
            } else {
                url.searchParams.set(type, values);
            }
        });

        // Navigate with new filters
        setTimeout(() => {
            window.location.href = url.toString();
        }, 300);
    }

    // Utility function for debouncing
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            // Recalculate sticky positioning if needed
            if (filterContainer.dataset.position === 'sticky') {
                filterContainer.style.top = '0';
            }
        }, 100);
    });

    // Handle scroll for sticky behavior enhancement
    let lastScrollY = window.scrollY;
    let ticking = false;

    function updateStickyBehavior() {
        const currentScrollY = window.scrollY;
        const isScrollingDown = currentScrollY > lastScrollY;
        
        if (filterContainer.dataset.position === 'sticky') {
            // Add shadow when scrolled
            if (currentScrollY > 10) {
                filterContainer.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
            } else {
                filterContainer.style.boxShadow = '';
            }
        }
        
        lastScrollY = currentScrollY;
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateStickyBehavior);
            ticking = true;
        }
    }, { passive: true });
});
</script>
@endpush
