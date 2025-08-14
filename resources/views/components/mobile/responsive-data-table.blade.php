@props([
    'data' => [],
    'columns' => [],
    'showPagination' => true,
    'showFilters' => true,
    'showSearch' => true,
    'sortable' => true,
    'cardLayout' => 'default', // default, compact, detailed
    'actionButtons' => [],
    'emptyMessage' => 'No data available',
    'itemsPerPage' => 10,
    'currentPage' => 1,
    'totalItems' => 0
])

@php
    $tableId = 'responsive-table-' . uniqid();
    $hasData = count($data) > 0;
    $totalPages = $itemsPerPage > 0 ? ceil($totalItems / $itemsPerPage) : 1;
@endphp

<div 
    class="responsive-data-table" 
    data-table-id="{{ $tableId }}"
    data-card-layout="{{ $cardLayout }}"
    data-sortable="{{ $sortable ? 'true' : 'false' }}"
>
    <!-- Table Header with Controls -->
    @if($showFilters || $showSearch)
        <div class="table-controls bg-white border border-gray-200 rounded-t-lg p-4 space-y-4 md:space-y-0 md:flex md:items-center md:justify-between">
            <!-- Search -->
            @if($showSearch)
                <div class="search-container flex-1 md:max-w-sm">
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Search tickets..." 
                            class="table-search w-full px-4 py-2 pl-10 pr-4 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            data-search-target="{{ $tableId }}"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <button class="clear-search absolute inset-y-0 right-0 pr-3 hidden text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            <!-- View Toggle & Actions -->
            <div class="flex items-center space-x-3">
                <!-- Mobile View Toggle -->
                <div class="view-toggle md:hidden">
                    <button 
                        class="view-toggle-btn flex items-center px-3 py-2 text-sm bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        data-toggle-view="{{ $tableId }}"
                        aria-label="Toggle view"
                    >
                        <svg class="table-view-icon w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span class="view-text">Cards</span>
                    </button>
                </div>

                <!-- Items Per Page -->
                @if($showPagination)
                    <select 
                        class="items-per-page text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-blue-500"
                        data-items-per-page="{{ $tableId }}"
                    >
                        <option value="10" {{ $itemsPerPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $itemsPerPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $itemsPerPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $itemsPerPage == 100 ? 'selected' : '' }}>100</option>
                    </select>
                @endif

                <!-- Export/Actions -->
                @if(count($actionButtons) > 0)
                    <div class="table-actions flex items-center space-x-2">
                        @foreach($actionButtons as $action)
                            <button 
                                class="action-btn px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors {{ $action['class'] ?? '' }}"
                                data-action="{{ $action['action'] }}"
                                @if(isset($action['onclick'])) onclick="{{ $action['onclick'] }}" @endif
                            >
                                @if(isset($action['icon']))
                                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] }}"></path>
                                    </svg>
                                @endif
                                {{ $action['label'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Table/Cards Container -->
    <div class="table-container relative bg-white {{ $showFilters || $showSearch ? 'border-l border-r border-b border-gray-200 rounded-b-lg' : 'border border-gray-200 rounded-lg' }}">
        <!-- Loading Overlay -->
        <div class="loading-overlay absolute inset-0 bg-white bg-opacity-75 hidden items-center justify-center z-10">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600">Loading...</span>
            </div>
        </div>

        @if($hasData)
            <!-- Desktop Table View -->
            <div class="table-view hidden md:block" data-view="table">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <!-- Table Header -->
                        <thead class="bg-gray-50">
                            <tr>
                                @foreach($columns as $column)
                                    @php
                                        $sortable = $column['sortable'] ?? true;
                                        $currentSort = request()->get('sort') === ($column['key'] ?? '');
                                        $sortDirection = request()->get('direction', 'asc');
                                    @endphp
                                    <th 
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider {{ $sortable ? 'cursor-pointer hover:bg-gray-100' : '' }}"
                                        @if($sortable) 
                                            data-sortable-column="{{ $column['key'] ?? '' }}"
                                            data-current-direction="{{ $currentSort ? $sortDirection : '' }}"
                                        @endif
                                    >
                                        <div class="flex items-center space-x-1">
                                            <span>{{ $column['label'] }}</span>
                                            @if($sortable)
                                                <div class="sort-indicators">
                                                    <svg class="w-4 h-4 {{ $currentSort && $sortDirection === 'asc' ? 'text-blue-600' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                                @if(count($actionButtons) > 0)
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                @endif
                            </tr>
                        </thead>

                        <!-- Table Body -->
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($data as $row)
                                <tr class="table-row hover:bg-gray-50 transition-colors" data-row-id="{{ $row->id ?? '' }}">
                                    @foreach($columns as $column)
                                        @php
                                            $key = $column['key'] ?? '';
                                            $value = $key ? data_get($row, $key) : '';
                                            $type = $column['type'] ?? 'text';
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $column['class'] ?? 'text-gray-900' }}">
                                            @switch($type)
                                                @case('currency')
                                                    <span class="font-semibold text-green-600">${{ number_format($value, 2) }}</span>
                                                    @break
                                                @case('date')
                                                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($value)->format('M j, Y') }}</span>
                                                    @break
                                                @case('datetime')
                                                    <span class="text-gray-600">{{ \Carbon\Carbon::parse($value)->format('M j, Y g:i A') }}</span>
                                                    @break
                                                @case('badge')
                                                    @php
                                                        $badgeColors = [
                                                            'available' => 'bg-green-100 text-green-800',
                                                            'limited' => 'bg-yellow-100 text-yellow-800',
                                                            'sold_out' => 'bg-red-100 text-red-800',
                                                            'draft' => 'bg-gray-100 text-gray-800'
                                                        ];
                                                        $badgeClass = $badgeColors[strtolower($value)] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                        {{ ucfirst(str_replace('_', ' ', $value)) }}
                                                    </span>
                                                    @break
                                                @case('image')
                                                    @if($value)
                                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ $value }}" alt="Image">
                                                    @else
                                                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    @break
                                                @default
                                                    <span>{{ $value }}</span>
                                            @endswitch
                                        </td>
                                    @endforeach
                                    @if(count($actionButtons) > 0)
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                @foreach($actionButtons as $action)
                                                    <button 
                                                        class="text-{{ $action['color'] ?? 'blue' }}-600 hover:text-{{ $action['color'] ?? 'blue' }}-900 transition-colors"
                                                        onclick="handleRowAction('{{ $action['action'] }}', {{ json_encode($row) }})"
                                                        title="{{ $action['tooltip'] ?? $action['label'] }}"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] ?? 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' }}"></path>
                                                        </svg>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="card-view block md:hidden" data-view="cards">
                <div class="space-y-4 p-4">
                    @foreach($data as $row)
                        <div class="ticket-card bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow" data-row-id="{{ $row->id ?? '' }}">
                            @if($cardLayout === 'detailed')
                                <!-- Detailed Card Layout -->
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 text-base">
                                                {{ $row->event_name ?? $row->title ?? 'Event' }}
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                {{ $row->venue ?? $row->location ?? 'Venue' }}
                                            </p>
                                        </div>
                                        @if(isset($row->availability))
                                            @php
                                                $badgeColors = [
                                                    'available' => 'bg-green-100 text-green-800',
                                                    'limited' => 'bg-yellow-100 text-yellow-800',
                                                    'sold_out' => 'bg-red-100 text-red-800'
                                                ];
                                                $badgeClass = $badgeColors[strtolower($row->availability)] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                {{ ucfirst(str_replace('_', ' ', $row->availability)) }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                                        @if(isset($row->event_date))
                                            <div>
                                                <span class="text-gray-500">Date:</span>
                                                <div class="font-medium">{{ \Carbon\Carbon::parse($row->event_date)->format('M j, Y') }}</div>
                                            </div>
                                        @endif
                                        @if(isset($row->price))
                                            <div>
                                                <span class="text-gray-500">Price:</span>
                                                <div class="font-semibold text-green-600">${{ number_format($row->price, 2) }}</div>
                                            </div>
                                        @endif
                                        @if(isset($row->section))
                                            <div>
                                                <span class="text-gray-500">Section:</span>
                                                <div class="font-medium">{{ $row->section }}</div>
                                            </div>
                                        @endif
                                        @if(isset($row->category))
                                            <div>
                                                <span class="text-gray-500">Category:</span>
                                                <div class="font-medium">{{ $row->category }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    @if(count($actionButtons) > 0)
                                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-gray-100">
                                            @foreach($actionButtons as $action)
                                                <button 
                                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-{{ $action['color'] ?? 'blue' }}-600 text-white rounded-md hover:bg-{{ $action['color'] ?? 'blue' }}-700 transition-colors"
                                                    onclick="handleRowAction('{{ $action['action'] }}', {{ json_encode($row) }})"
                                                >
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $action['icon'] ?? 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z' }}"></path>
                                                    </svg>
                                                    {{ $action['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            @elseif($cardLayout === 'compact')
                                <!-- Compact Card Layout -->
                                <div class="p-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-medium text-gray-900 truncate">
                                                {{ $row->event_name ?? $row->title ?? 'Event' }}
                                            </h4>
                                            <div class="flex items-center space-x-2 text-xs text-gray-500 mt-1">
                                                @if(isset($row->event_date))
                                                    <span>{{ \Carbon\Carbon::parse($row->event_date)->format('M j') }}</span>
                                                @endif
                                                @if(isset($row->venue))
                                                    <span>•</span>
                                                    <span class="truncate">{{ $row->venue }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right ml-3">
                                            @if(isset($row->price))
                                                <div class="font-semibold text-green-600">${{ number_format($row->price, 2) }}</div>
                                            @endif
                                            @if(count($actionButtons) > 0)
                                                <button 
                                                    class="text-blue-600 hover:text-blue-800 text-xs mt-1"
                                                    onclick="handleRowAction('{{ $actionButtons[0]['action'] }}', {{ json_encode($row) }})"
                                                >
                                                    {{ $actionButtons[0]['label'] }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            @else
                                <!-- Default Card Layout -->
                                <div class="p-4">
                                    @foreach($columns as $column)
                                        @php
                                            $key = $column['key'] ?? '';
                                            $value = $key ? data_get($row, $key) : '';
                                            $type = $column['type'] ?? 'text';
                                            $mobile_hidden = $column['mobile_hidden'] ?? false;
                                        @endphp
                                        
                                        @if(!$mobile_hidden && $value)
                                            <div class="flex justify-between items-center py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                                <span class="text-sm font-medium text-gray-500">{{ $column['label'] }}</span>
                                                <span class="text-sm text-gray-900">
                                                    @switch($type)
                                                        @case('currency')
                                                            <span class="font-semibold text-green-600">${{ number_format($value, 2) }}</span>
                                                            @break
                                                        @case('date')
                                                            {{ \Carbon\Carbon::parse($value)->format('M j, Y') }}
                                                            @break
                                                        @case('datetime')
                                                            {{ \Carbon\Carbon::parse($value)->format('M j, Y g:i A') }}
                                                            @break
                                                        @case('badge')
                                                            @php
                                                                $badgeColors = [
                                                                    'available' => 'bg-green-100 text-green-800',
                                                                    'limited' => 'bg-yellow-100 text-yellow-800',
                                                                    'sold_out' => 'bg-red-100 text-red-800'
                                                                ];
                                                                $badgeClass = $badgeColors[strtolower($value)] ?? 'bg-gray-100 text-gray-800';
                                                            @endphp
                                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                                {{ ucfirst(str_replace('_', ' ', $value)) }}
                                                            </span>
                                                            @break
                                                        @default
                                                            {{ $value }}
                                                    @endswitch
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach

                                    @if(count($actionButtons) > 0)
                                        <div class="flex items-center justify-end space-x-2 pt-3 mt-3 border-t border-gray-100">
                                            @foreach($actionButtons as $action)
                                                <button 
                                                    class="inline-flex items-center px-3 py-1.5 text-sm bg-{{ $action['color'] ?? 'blue' }}-600 text-white rounded-md hover:bg-{{ $action['color'] ?? 'blue' }}-700 transition-colors"
                                                    onclick="handleRowAction('{{ $action['action'] }}', {{ json_encode($row) }})"
                                                >
                                                    {{ $action['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="empty-state text-center py-12 px-4">
                <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1V7a2 2 0 00-2-2H5zM5 14a2 2 0 00-2 2v3a1 1 0 001 1h1a1 1 0 001-1v-3a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets found</h3>
                <p class="text-gray-500 max-w-sm mx-auto">{{ $emptyMessage }}</p>
                @if($showSearch)
                    <button 
                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                        onclick="document.querySelector('.table-search').focus()"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Try searching
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($showPagination && $hasData && $totalPages > 1)
        <div class="pagination-container bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <!-- Mobile Pagination -->
                <div class="flex items-center space-x-2 md:hidden">
                    @if($currentPage > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" 
                           class="pagination-btn inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Previous
                        </a>
                    @endif
                    
                    <span class="text-sm text-gray-700">
                        Page {{ $currentPage }} of {{ $totalPages }}
                    </span>
                    
                    @if($currentPage < $totalPages)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" 
                           class="pagination-btn inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                            Next
                        </a>
                    @endif
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden md:flex md:items-center md:justify-between w-full">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing {{ ($currentPage - 1) * $itemsPerPage + 1 }} to {{ min($currentPage * $itemsPerPage, $totalItems) }} of {{ $totalItems }} results
                        </p>
                    </div>
                    
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        @if($currentPage > 1)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" 
                               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @endif

                        @for($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" 
                               class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i == $currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor

                        @if($currentPage < $totalPages)
                            <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" 
                               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </a>
                        @endif
                    </nav>
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/customer-dashboard.css') }}?v={{ now()->timestamp }}">
<style>
/* Responsive Data Table Styles */
.responsive-data-table {
    font-size: 14px;
    line-height: 1.5;
}

/* Table controls responsive */
.table-controls {
    transition: all 0.3s ease;
}

@media (max-width: 768px) {
    .table-controls {
        padding: 16px;
    }
    
    .table-controls .flex {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
}

/* Search input enhancements */
.table-search {
    transition: all 0.3s ease;
    font-size: 16px; /* Prevents zoom on iOS */
}

.table-search:focus + .clear-search {
    display: block;
}

/* Loading overlay */
.loading-overlay {
    backdrop-filter: blur(2px);
    transition: opacity 0.3s ease;
}

.loading-overlay.show {
    display: flex !important;
}

/* Table view optimizations */
.table-view table {
    font-size: 14px;
}

.table-row:hover {
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Sortable columns */
[data-sortable-column] {
    user-select: none;
}

[data-sortable-column]:hover {
    background-color: #f9fafb;
}

.sort-indicators {
    display: flex;
    flex-direction: column;
    margin-left: 4px;
}

/* Card view styles */
.card-view {
    max-height: 70vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.ticket-card {
    position: relative;
    overflow: hidden;
    transform: translateY(0);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.ticket-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Mobile card optimizations */
@media (max-width: 768px) {
    .card-view {
        padding: 12px;
    }
    
    .ticket-card {
        margin-bottom: 12px;
        border-radius: 12px;
    }
    
    .ticket-card:last-child {
        margin-bottom: 0;
    }
}

/* Touch-friendly interactions */
.pagination-btn,
.action-btn,
.view-toggle-btn {
    min-height: 44px;
    min-width: 44px;
    touch-action: manipulation;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
}

.pagination-btn:active,
.action-btn:active,
.view-toggle-btn:active {
    transform: scale(0.95);
}

/* Badge animations */
.ticket-card [class*="bg-green-"],
.ticket-card [class*="bg-yellow-"],
.ticket-card [class*="bg-red-"] {
    animation: fadeInScale 0.3s ease-out;
}

@keyframes fadeInScale {
    0% {
        opacity: 0;
        transform: scale(0.8);
    }
    100% {
        opacity: 1;
        transform: scale(1);
    }
}

/* Empty state styling */
.empty-state {
    opacity: 0;
    animation: fadeIn 0.6s ease-out forwards;
}

@keyframes fadeIn {
    0% { opacity: 0; transform: translateY(20px); }
    100% { opacity: 1; transform: translateY(0); }
}

/* View toggle animation */
.view-toggle-btn .table-view-icon {
    transition: transform 0.3s ease;
}

.view-toggle-btn.cards-active .table-view-icon {
    transform: rotate(180deg);
}

/* Responsive table improvements */
@media (max-width: 1024px) {
    .table-view {
        font-size: 13px;
    }
    
    .table-view th,
    .table-view td {
        padding: 12px 8px;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .ticket-card,
    .pagination-btn,
    .action-btn,
    .view-toggle-btn {
        transition: none;
        animation: none;
    }
    
    .ticket-card:hover {
        transform: none;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .ticket-card {
        border-width: 2px;
    }
    
    .pagination-btn,
    .action-btn {
        border-width: 2px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .responsive-data-table {
        color: #f9fafb;
    }
    
    .table-controls,
    .table-container,
    .ticket-card {
        background: #1f2937;
        border-color: #374151;
    }
    
    .table-search {
        background: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }
    
    .table-search::placeholder {
        color: #9ca3af;
    }
    
    .table-view thead {
        background: #374151;
    }
    
    .table-row:hover {
        background: #374151;
    }
}

/* Print styles */
@media print {
    .table-controls,
    .pagination-container,
    .action-btn {
        display: none;
    }
    
    .card-view {
        display: none;
    }
    
    .table-view {
        display: block !important;
    }
    
    .ticket-card {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #000;
    }
}

/* Performance optimizations */
.ticket-card {
    will-change: transform;
    contain: layout style paint;
}

/* Smooth scrolling */
.card-view {
    scroll-behavior: smooth;
}

/* Focus indicators */
.table-search:focus,
.pagination-btn:focus,
.action-btn:focus,
.view-toggle-btn:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Loading shimmer effect */
.ticket-card.loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableId = '{{ $tableId }}';
    const tableContainer = document.querySelector(`[data-table-id="${tableId}"]`);
    
    if (!tableContainer) return;

    // Elements
    const searchInput = tableContainer.querySelector('.table-search');
    const clearSearchBtn = tableContainer.querySelector('.clear-search');
    const viewToggleBtn = tableContainer.querySelector('[data-toggle-view]');
    const loadingOverlay = tableContainer.querySelector('.loading-overlay');
    const sortableColumns = tableContainer.querySelectorAll('[data-sortable-column]');
    const itemsPerPageSelect = tableContainer.querySelector('[data-items-per-page]');
    
    // State
    let currentView = window.innerWidth >= 768 ? 'table' : 'cards';
    let searchTimeout;

    // Initialize
    initializeTable();
    
    function initializeTable() {
        setupSearch();
        setupViewToggle();
        setupSorting();
        setupPagination();
        handleResponsiveView();
        
        // Window resize handler
        window.addEventListener('resize', debounce(handleResponsiveView, 250));
    }

    function setupSearch() {
        if (!searchInput) return;
        
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            
            // Show/hide clear button
            if (clearSearchBtn) {
                clearSearchBtn.style.display = query ? 'block' : 'none';
            }
            
            // Debounced search
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Clear search
        if (clearSearchBtn) {
            clearSearchBtn.addEventListener('click', function() {
                searchInput.value = '';
                searchInput.focus();
                this.style.display = 'none';
                performSearch('');
            });
        }
    }

    function performSearch(query) {
        if (query.length < 2 && query.length > 0) return;
        
        showLoading(true);
        
        // Build search URL
        const url = new URL(window.location);
        if (query) {
            url.searchParams.set('search', query);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.set('page', '1'); // Reset to first page
        
        // Navigate or perform AJAX search
        if (window.ajaxSearch) {
            performAjaxSearch(url.toString(), query);
        } else {
            window.location.href = url.toString();
        }
    }

    async function performAjaxSearch(url, query) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                updateTableContent(data.html);
                updatePagination(data.pagination);
            }
        } catch (error) {
            console.error('Search failed:', error);
        } finally {
            showLoading(false);
        }
    }

    function setupViewToggle() {
        if (!viewToggleBtn) return;
        
        viewToggleBtn.addEventListener('click', function() {
            const tableView = tableContainer.querySelector('.table-view');
            const cardView = tableContainer.querySelector('.card-view');
            const viewText = this.querySelector('.view-text');
            
            if (currentView === 'table') {
                // Switch to cards
                currentView = 'cards';
                if (tableView) tableView.classList.add('hidden');
                if (cardView) cardView.classList.remove('hidden');
                viewText.textContent = 'Table';
                this.classList.add('cards-active');
            } else {
                // Switch to table
                currentView = 'table';
                if (tableView) tableView.classList.remove('hidden');
                if (cardView) cardView.classList.add('hidden');
                viewText.textContent = 'Cards';
                this.classList.remove('cards-active');
            }
            
            // Store preference
            localStorage.setItem('tableView', currentView);
        });
        
        // Load saved preference
        const savedView = localStorage.getItem('tableView');
        if (savedView && savedView !== currentView) {
            viewToggleBtn.click();
        }
    }

    function setupSorting() {
        sortableColumns.forEach(column => {
            column.addEventListener('click', function() {
                const sortKey = this.getAttribute('data-sortable-column');
                const currentDirection = this.getAttribute('data-current-direction');
                const newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
                
                showLoading(true);
                
                // Build sort URL
                const url = new URL(window.location);
                url.searchParams.set('sort', sortKey);
                url.searchParams.set('direction', newDirection);
                url.searchParams.set('page', '1'); // Reset to first page
                
                window.location.href = url.toString();
            });
        });
    }

    function setupPagination() {
        if (!itemsPerPageSelect) return;
        
        itemsPerPageSelect.addEventListener('change', function() {
            const itemsPerPage = this.value;
            
            const url = new URL(window.location);
            url.searchParams.set('per_page', itemsPerPage);
            url.searchParams.set('page', '1'); // Reset to first page
            
            window.location.href = url.toString();
        });
    }

    function handleResponsiveView() {
        const tableView = tableContainer.querySelector('.table-view');
        const cardView = tableContainer.querySelector('.card-view');
        
        if (window.innerWidth >= 768) {
            // Desktop - show table view
            if (tableView) tableView.classList.remove('hidden', 'md:block');
            if (cardView) cardView.classList.add('hidden');
            if (viewToggleBtn) viewToggleBtn.style.display = 'none';
        } else {
            // Mobile - show card view
            if (tableView) tableView.classList.add('hidden');
            if (cardView) cardView.classList.remove('hidden', 'md:hidden');
            if (viewToggleBtn) viewToggleBtn.style.display = 'flex';
        }
    }

    function showLoading(show) {
        if (!loadingOverlay) return;
        
        if (show) {
            loadingOverlay.classList.add('show');
        } else {
            setTimeout(() => {
                loadingOverlay.classList.remove('show');
            }, 150);
        }
    }

    function updateTableContent(html) {
        // This would be used for AJAX updates
        // Implementation depends on your backend response format
    }

    function updatePagination(paginationData) {
        // Update pagination controls with new data
        // Implementation depends on your pagination structure
    }

    // Utility function
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

    // Handle visibility changes to pause animations
    document.addEventListener('visibilitychange', function() {
        const cards = tableContainer.querySelectorAll('.ticket-card');
        if (document.hidden) {
            cards.forEach(card => {
                card.style.animationPlayState = 'paused';
            });
        } else {
            cards.forEach(card => {
                card.style.animationPlayState = 'running';
            });
        }
    });

    // Expose global functions for row actions
    window.handleRowAction = function(action, rowData) {
        console.log('Row action:', action, rowData);
        
        // Handle different actions
        switch(action) {
            case 'edit':
                // Navigate to edit page or open modal
                break;
            case 'delete':
                // Show confirmation and delete
                break;
            case 'view':
                // Navigate to detail view
                break;
            case 'purchase':
                // Add to cart or purchase flow
                break;
            default:
                console.log('Unhandled action:', action);
        }
    };
});
</script>
@endpush
