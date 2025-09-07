{{--
    Enhanced Responsive Table Component
    
    Features:
    - Mobile-first responsive design
    - Horizontal scrolling on mobile
    - Card layout option for small screens
    - Touch-friendly sorting and pagination
    - Accessible table markup
    - Loading states and empty states
    - Customizable column priorities
--}}
@props([
    'data' => [],
    'columns' => [],
    'mobileLayout' => 'cards', // 'cards', 'scroll', 'stack'
    'sortable' => true,
    'searchable' => true,
    'paginated' => true,
    'emptyMessage' => 'No data available',
    'loadingState' => false,
    'stickyHeader' => true,
    'striped' => true,
    'hover' => true,
    'compact' => false
])

@php
    $tableId = 'responsive-table-' . Str::random(8);
    $searchId = $tableId . '-search';
@endphp

<div class="responsive-table-wrapper" 
     x-data="responsiveTable({
        data: @js($data),
        columns: @js($columns),
        mobileLayout: '{{ $mobileLayout }}',
        sortable: {{ $sortable ? 'true' : 'false' }},
        searchable: {{ $searchable ? 'true' : 'false' }}
     })"
     x-init="init()">
     
    <!-- Table Header Controls -->
    @if($searchable || $sortable)
        <div class="table-controls">
            @if($searchable)
                <!-- Search Input -->
                <div class="table-search">
                    <label for="{{ $searchId }}" class="sr-only">Search table data</label>
                    <div class="search-input-group">
                        <input type="search" 
                               id="{{ $searchId }}"
                               class="search-input" 
                               placeholder="Search..."
                               x-model="searchTerm"
                               @input="performSearch()">
                        <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <button @click="clearSearch()" 
                                class="search-clear"
                                x-show="searchTerm.length > 0"
                                type="button">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif
            
            <!-- View Toggle (Mobile) -->
            <div class="view-toggle md:hidden">
                <button @click="toggleMobileLayout()" 
                        class="view-toggle-btn"
                        :class="{ 'active': currentMobileLayout === 'cards' }"
                        type="button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="sr-only">Toggle card view</span>
                </button>
                <button @click="toggleMobileLayout()" 
                        class="view-toggle-btn"
                        :class="{ 'active': currentMobileLayout === 'scroll' }"
                        type="button">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    <span class="sr-only">Toggle table view</span>
                </button>
            </div>
        </div>
    @endif
    
    <!-- Loading State -->
    <div x-show="loading || {{ $loadingState ? 'true' : 'false' }}" class="table-loading">
        <div class="loading-skeleton">
            @for($i = 1; $i <= 3; $i++)
                <div class="skeleton-row">
                    @for($j = 1; $j <= count($columns); $j++)
                        <div class="skeleton-cell"></div>
                    @endfor
                </div>
            @endfor
        </div>
    </div>
    
    <!-- Desktop/Tablet Table View -->
    <div class="table-container"
         x-show="!loading && !{{ $loadingState ? 'true' : 'false' }}"
         :class="{ 
            'table-striped': {{ $striped ? 'true' : 'false' }},
            'table-hover': {{ $hover ? 'true' : 'false' }},
            'table-compact': {{ $compact ? 'true' : 'false' }},
            'table-sticky-header': {{ $stickyHeader ? 'true' : 'false' }}
         }">
        
        <!-- Desktop Table -->
        <div class="hidden md:block">
            <table class="responsive-table" 
                   role="table" 
                   aria-label="Data table"
                   id="{{ $tableId }}">
                <thead class="table-header">
                    <tr>
                        @foreach($columns as $column)
                            <th scope="col" 
                                class="table-header-cell {{ $column['priority'] ?? 'normal' }}"
                                :class="{ 
                                    'sortable': {{ $sortable && ($column['sortable'] ?? true) ? 'true' : 'false' }},
                                    'sorted-asc': sortColumn === '{{ $column['key'] }}' && sortDirection === 'asc',
                                    'sorted-desc': sortColumn === '{{ $column['key'] }}' && sortDirection === 'desc'
                                }"
                                @if($sortable && ($column['sortable'] ?? true))
                                    @click="sort('{{ $column['key'] }}')"
                                    tabindex="0"
                                    @keydown.enter="sort('{{ $column['key'] }}')"
                                    @keydown.space.prevent="sort('{{ $column['key'] }}')"
                                    role="button"
                                    :aria-sort="getSortAriaAttribute('{{ $column['key'] }}')"
                                @endif>
                                <div class="header-content">
                                    <span class="header-text">{{ $column['label'] }}</span>
                                    @if($sortable && ($column['sortable'] ?? true))
                                        <span class="sort-indicator">
                                            <svg class="sort-icon sort-asc" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            </svg>
                                            <svg class="sort-icon sort-desc" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="table-body">
                    <template x-for="(row, index) in filteredData" :key="index">
                        <tr class="table-row">
                            @foreach($columns as $column)
                                <td class="table-cell {{ $column['priority'] ?? 'normal' }}"
                                    :class="{ 'cell-numeric': '{{ $column['type'] ?? 'text' }}' === 'number' }">
                                    @if(isset($column['component']))
                                        <x-dynamic-component 
                                            :component="$column['component']" 
                                            :value="row['{{ $column['key'] }}']"
                                            :row="row" />
                                    @else
                                        <span x-html="formatCellValue(row['{{ $column['key'] }}'], '{{ $column['type'] ?? 'text' }}')"></span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        
        <!-- Mobile Card View -->
        <div class="md:hidden mobile-cards" 
             x-show="currentMobileLayout === 'cards'">
            <template x-for="(row, index) in filteredData" :key="index">
                <div class="mobile-card">
                    @foreach($columns as $column)
                        @if(($column['mobile'] ?? true) && ($column['priority'] ?? 'normal') !== 'low')
                            <div class="card-field">
                                <div class="field-label">{{ $column['label'] }}</div>
                                <div class="field-value" 
                                     :class="{ 'field-numeric': '{{ $column['type'] ?? 'text' }}' === 'number' }">
                                    @if(isset($column['component']))
                                        <x-dynamic-component 
                                            :component="$column['component']" 
                                            :value="row['{{ $column['key'] }}']"
                                            :row="row" />
                                    @else
                                        <span x-html="formatCellValue(row['{{ $column['key'] }}'], '{{ $column['type'] ?? 'text' }}')"></span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </template>
        </div>
        
        <!-- Mobile Horizontal Scroll View -->
        <div class="md:hidden mobile-scroll-container" 
             x-show="currentMobileLayout === 'scroll'">
            <div class="mobile-table-scroll">
                <table class="mobile-table">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                @if($column['mobile'] ?? true)
                                    <th class="mobile-header-cell">{{ $column['label'] }}</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(row, index) in filteredData" :key="index">
                            <tr class="mobile-table-row">
                                @foreach($columns as $column)
                                    @if($column['mobile'] ?? true)
                                        <td class="mobile-table-cell"
                                            :class="{ 'cell-numeric': '{{ $column['type'] ?? 'text' }}' === 'number' }">
                                            @if(isset($column['component']))
                                                <x-dynamic-component 
                                                    :component="$column['component']" 
                                                    :value="row['{{ $column['key'] }}']"
                                                    :row="row" />
                                            @else
                                                <span x-html="formatCellValue(row['{{ $column['key'] }}'], '{{ $column['type'] ?? 'text' }}')"></span>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Empty State -->
    <div x-show="!loading && !{{ $loadingState ? 'true' : 'false' }} && filteredData.length === 0" 
         class="table-empty-state">
        <div class="empty-state-content">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="empty-message">{{ $emptyMessage }}</p>
            <button @click="clearSearch()" 
                    x-show="searchTerm.length > 0"
                    class="empty-action-btn">
                Clear search
            </button>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($paginated)
        <div x-show="totalPages > 1" class="table-pagination">
            <div class="pagination-info">
                <span x-text="`Showing ${((currentPage - 1) * itemsPerPage) + 1}-${Math.min(currentPage * itemsPerPage, totalItems)} of ${totalItems} results`"></span>
            </div>
            <div class="pagination-controls">
                <button @click="goToPage(currentPage - 1)" 
                        :disabled="currentPage === 1"
                        class="pagination-btn pagination-prev">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="sr-only">Previous page</span>
                </button>
                
                <template x-for="page in visiblePages" :key="page">
                    <button @click="goToPage(page)" 
                            :class="{ 'active': page === currentPage }"
                            class="pagination-btn pagination-number">
                        <span x-text="page"></span>
                    </button>
                </template>
                
                <button @click="goToPage(currentPage + 1)" 
                        :disabled="currentPage === totalPages"
                        class="pagination-btn pagination-next">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="sr-only">Next page</span>
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function responsiveTable(config) {
    return {
        // Data management
        data: config.data || [],
        columns: config.columns || [],
        filteredData: [],
        searchTerm: '',
        
        // Sorting
        sortable: config.sortable || false,
        sortColumn: '',
        sortDirection: 'asc',
        
        // Pagination
        currentPage: 1,
        itemsPerPage: 10,
        totalItems: 0,
        totalPages: 1,
        
        // Mobile layout
        mobileLayout: config.mobileLayout || 'cards',
        currentMobileLayout: config.mobileLayout || 'cards',
        
        // State
        loading: false,
        searchable: config.searchable || false,
        
        init() {
            this.filteredData = this.data;
            this.totalItems = this.data.length;
            this.updatePagination();
        },
        
        // Search functionality
        performSearch() {
            const term = this.searchTerm.toLowerCase();
            if (!term) {
                this.filteredData = this.data;
            } else {
                this.filteredData = this.data.filter(row => {
                    return this.columns.some(column => {
                        const value = row[column.key];
                        return value && value.toString().toLowerCase().includes(term);
                    });
                });
            }
            this.totalItems = this.filteredData.length;
            this.currentPage = 1;
            this.updatePagination();
        },
        
        clearSearch() {
            this.searchTerm = '';
            this.performSearch();
        },
        
        // Sorting functionality
        sort(column) {
            if (!this.sortable) return;
            
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
            
            this.filteredData.sort((a, b) => {
                const aVal = a[column];
                const bVal = b[column];
                
                let comparison = 0;
                if (aVal > bVal) comparison = 1;
                if (aVal < bVal) comparison = -1;
                
                return this.sortDirection === 'asc' ? comparison : -comparison;
            });
        },
        
        getSortAriaAttribute(column) {
            if (this.sortColumn !== column) return 'none';
            return this.sortDirection === 'asc' ? 'ascending' : 'descending';
        },
        
        // Mobile layout toggle
        toggleMobileLayout() {
            this.currentMobileLayout = this.currentMobileLayout === 'cards' ? 'scroll' : 'cards';
        },
        
        // Pagination
        updatePagination() {
            this.totalPages = Math.ceil(this.totalItems / this.itemsPerPage);
        },
        
        goToPage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        
        get visiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },
        
        get paginatedData() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredData.slice(start, end);
        },
        
        // Cell formatting
        formatCellValue(value, type) {
            if (value == null) return '-';
            
            switch (type) {
                case 'currency':
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'USD'
                    }).format(value);
                case 'number':
                    return new Intl.NumberFormat().format(value);
                case 'date':
                    return new Date(value).toLocaleDateString();
                case 'datetime':
                    return new Date(value).toLocaleString();
                default:
                    return value;
            }
        }
    }
}
</script>
@endpush

{{ $slot }}
