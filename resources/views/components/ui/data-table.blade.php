@props([
    'columns' => [],
    'data' => [],
    'sortable' => true,
    'searchable' => false,
    'paginated' => false,
    'selectable' => false,
    'stickyHeader' => true,
    'responsive' => true,
    'density' => 'comfortable', // compact, comfortable, spacious
    'emptyMessage' => 'No data available',
    'loading' => false,
    'loadingRows' => 5,
    'id' => null,
    'caption' => null,
    'summary' => null
])

@php
    $tableId = $id ?? 'table-' . uniqid();
    $captionId = $tableId . '-caption';
    $summaryId = $tableId . '-summary';
    
    $densityClasses = [
        'compact' => 'hdt-table--compact',
        'comfortable' => 'hdt-table--comfortable',
        'spacious' => 'hdt-table--spacious'
    ];
    
    $tableClasses = [
        'hdt-table',
        'w-full border-collapse',
        $densityClasses[$density] ?? $densityClasses['comfortable'],
        $responsive ? 'hdt-table--responsive' : '',
        $stickyHeader ? 'hdt-table--sticky' : '',
        $selectable ? 'hdt-table--selectable' : '',
        $loading ? 'hdt-table--loading' : ''
    ];
@endphp

<div class="hdt-table-container" 
     x-data="{
        sortColumn: null,
        sortDirection: 'asc',
        selectedRows: [],
        searchQuery: '',
        loading: {{ $loading ? 'true' : 'false' }},
        data: {{ json_encode($data) }},
        columns: {{ json_encode($columns) }},
        
        get filteredData() {
            if (!this.searchQuery.trim()) return this.data;
            
            return this.data.filter(row => {
                return Object.values(row).some(value => 
                    String(value).toLowerCase().includes(this.searchQuery.toLowerCase())
                );
            });
        },
        
        get sortedData() {
            if (!this.sortColumn) return this.filteredData;
            
            return [...this.filteredData].sort((a, b) => {
                let aVal = a[this.sortColumn];
                let bVal = b[this.sortColumn];
                
                // Handle null/undefined values
                if (aVal === null || aVal === undefined) aVal = '';
                if (bVal === null || bVal === undefined) bVal = '';
                
                // Convert to strings for comparison
                aVal = String(aVal).toLowerCase();
                bVal = String(bVal).toLowerCase();
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        
        sort(column) {
            if (!column.sortable) return;
            
            if (this.sortColumn === column.key) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column.key;
                this.sortDirection = 'asc';
            }
        },
        
        toggleRow(index) {
            if (this.selectedRows.includes(index)) {
                this.selectedRows = this.selectedRows.filter(i => i !== index);
            } else {
                this.selectedRows.push(index);
            }
        },
        
        selectAll() {
            if (this.selectedRows.length === this.sortedData.length) {
                this.selectedRows = [];
            } else {
                this.selectedRows = [...Array(this.sortedData.length).keys()];
            }
        },
        
        isSelected(index) {
            return this.selectedRows.includes(index);
        },
        
        get hasSelection() {
            return this.selectedRows.length > 0;
        },
        
        get isAllSelected() {
            return this.selectedRows.length === this.sortedData.length && this.sortedData.length > 0;
        },
        
        get isPartiallySelected() {
            return this.selectedRows.length > 0 && this.selectedRows.length < this.sortedData.length;
        }
     }">

    {{-- Table Controls --}}
    @if($searchable || $selectable)
        <div class="hdt-table-controls flex items-center justify-between mb-4">
            {{-- Search --}}
            @if($searchable)
                <div class="hdt-table-search flex-1 max-w-sm">
                    <x-ui.form-input
                        type="search"
                        placeholder="Search table..."
                        x-model="searchQuery"
                        aria-label="Search table data" />
                </div>
            @endif
            
            {{-- Selection Actions --}}
            @if($selectable)
                <div class="hdt-table-actions flex items-center space-x-2">
                    <span x-show="hasSelection" 
                          class="text-sm text-text-secondary"
                          x-text="`${selectedRows.length} selected`"></span>
                    
                    <div x-show="hasSelection" class="flex space-x-1">
                        {{ $actions ?? '' }}
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Table Container with Scroll --}}
    <div class="hdt-table-scroll overflow-x-auto border border-border-primary rounded-lg">
        <table 
            id="{{ $tableId }}"
            class="{{ implode(' ', array_filter($tableClasses)) }}"
            role="table"
            @if($caption) aria-labelledby="{{ $captionId }}" @endif
            @if($summary) aria-describedby="{{ $summaryId }}" @endif>
            
            {{-- Caption --}}
            @if($caption)
                <caption id="{{ $captionId }}" 
                         class="hdt-table-caption text-left p-4 text-sm text-text-secondary font-medium">
                    {{ $caption }}
                </caption>
            @endif

            {{-- Table Header --}}
            <thead class="hdt-table-head">
                <tr role="row">
                    {{-- Select All Column --}}
                    @if($selectable)
                        <th scope="col" 
                            class="hdt-table-cell hdt-table-cell--header hdt-table-cell--select"
                            role="columnheader">
                            <x-ui.form-checkbox
                                :checked="false"
                                x-bind:checked="isAllSelected"
                                x-bind:indeterminate="isPartiallySelected"
                                @change="selectAll()"
                                aria-label="Select all rows" />
                        </th>
                    @endif

                    {{-- Data Columns --}}
                    <template x-for="column in columns" :key="column.key">
                        <th scope="col"
                            class="hdt-table-cell hdt-table-cell--header"
                            :class="column.sortable ? 'hdt-table-cell--sortable' : ''"
                            role="columnheader"
                            :aria-sort="sortColumn === column.key ? sortDirection : 'none'"
                            @if($sortable)
                                @click="sort(column)"
                                @keydown.enter="sort(column)"
                                @keydown.space.prevent="sort(column)"
                                :tabindex="column.sortable ? 0 : -1"
                            @endif>
                            
                            <div class="flex items-center justify-between">
                                <span x-text="column.label"></span>
                                
                                <template x-if="column.sortable">
                                    <div class="hdt-table-sort-indicator ml-2">
                                        <svg class="w-4 h-4 text-text-tertiary transition-transform"
                                             :class="{
                                                'rotate-180': sortColumn === column.key && sortDirection === 'desc',
                                                'text-text-primary': sortColumn === column.key
                                             }"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </div>
                                </template>
                            </div>
                        </th>
                    </template>
                </tr>
            </thead>

            {{-- Table Body --}}
            <tbody class="hdt-table-body">
                {{-- Loading State --}}
                <template x-if="loading">
                    <template x-for="i in {{ $loadingRows }}" :key="i">
                        <tr class="hdt-table-row hdt-table-row--loading">
                            @if($selectable)
                                <td class="hdt-table-cell">
                                    <div class="hdt-skeleton hdt-skeleton--checkbox"></div>
                                </td>
                            @endif
                            <template x-for="column in columns" :key="column.key">
                                <td class="hdt-table-cell">
                                    <div class="hdt-skeleton hdt-skeleton--text"></div>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>

                {{-- Empty State --}}
                <template x-if="!loading && sortedData.length === 0">
                    <tr class="hdt-table-row hdt-table-row--empty">
                        <td :colspan="columns.length + {{ $selectable ? 1 : 0 }}" 
                            class="hdt-table-cell hdt-table-cell--empty text-center py-12">
                            <div class="flex flex-col items-center space-y-2">
                                <svg class="w-8 h-8 text-text-quaternary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-text-secondary">{{ $emptyMessage }}</p>
                            </div>
                        </td>
                    </tr>
                </template>

                {{-- Data Rows --}}
                <template x-if="!loading && sortedData.length > 0">
                    <template x-for="(row, index) in sortedData" :key="index">
                        <tr class="hdt-table-row"
                            :class="{'hdt-table-row--selected': isSelected(index)}"
                            role="row">
                            
                            {{-- Select Column --}}
                            @if($selectable)
                                <td class="hdt-table-cell hdt-table-cell--select" role="gridcell">
                                    <x-ui.form-checkbox
                                        :checked="false"
                                        x-bind:checked="isSelected(index)"
                                        @change="toggleRow(index)"
                                        :aria-label="`Select row ${index + 1}`" />
                                </td>
                            @endif

                            {{-- Data Columns --}}
                            <template x-for="column in columns" :key="column.key">
                                <td class="hdt-table-cell"
                                    role="gridcell"
                                    :data-label="column.label">
                                    
                                    {{-- Custom Column Template --}}
                                    <template x-if="column.template">
                                        <div x-html="column.template(row[column.key], row, index)"></div>
                                    </template>
                                    
                                    {{-- Default Column Content --}}
                                    <template x-if="!column.template">
                                        <span x-text="row[column.key]"></span>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Table Summary --}}
    @if($summary)
        <div id="{{ $summaryId }}" 
             class="hdt-table-summary mt-2 text-sm text-text-tertiary">
            {{ $summary }}
        </div>
    @endif

    {{-- Table Info --}}
    <div class="hdt-table-info flex items-center justify-between mt-4 text-sm text-text-secondary">
        <div>
            <span x-show="!loading" 
                  x-text="`Showing ${sortedData.length} ${sortedData.length === 1 ? 'row' : 'rows'}`"></span>
            <span x-show="searchQuery.trim()" 
                  x-text="`of ${data.length} total`"></span>
        </div>
        
        @if($selectable)
            <div x-show="hasSelection">
                <span x-text="`${selectedRows.length} selected`"></span>
            </div>
        @endif
    </div>
</div>

@pushOnce('styles')
<style>
/* Data Table Styles */
.hdt-table-container {
    --hdt-table-border: var(--hdt-color-border-primary);
    --hdt-table-header-bg: var(--hdt-color-surface-secondary);
    --hdt-table-row-hover: var(--hdt-color-surface-tertiary);
    --hdt-table-row-selected: var(--hdt-color-primary-50);
}

.hdt-theme-dark .hdt-table-container {
    --hdt-table-row-selected: var(--hdt-color-primary-900);
}

/* Table Structure */
.hdt-table {
    font-family: var(--hdt-font-family-sans);
    background: var(--hdt-color-surface-primary);
}

/* Table Density */
.hdt-table--compact .hdt-table-cell {
    padding: 0.5rem;
}

.hdt-table--comfortable .hdt-table-cell {
    padding: 0.75rem 1rem;
}

.hdt-table--spacious .hdt-table-cell {
    padding: 1rem 1.5rem;
}

/* Table Header */
.hdt-table-head {
    background: var(--hdt-table-header-bg);
    position: sticky;
    top: 0;
    z-index: 10;
}

.hdt-table--sticky .hdt-table-head th {
    position: sticky;
    top: 0;
}

.hdt-table-cell--header {
    font-weight: 600;
    text-align: left;
    border-bottom: 2px solid var(--hdt-table-border);
    color: var(--hdt-color-text-primary);
    white-space: nowrap;
}

.hdt-table-cell--sortable {
    cursor: pointer;
    user-select: none;
    transition: background-color 150ms ease;
}

.hdt-table-cell--sortable:hover {
    background: var(--hdt-table-row-hover);
}

.hdt-table-cell--sortable:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: -2px;
}

/* Table Body */
.hdt-table-body .hdt-table-row {
    border-bottom: 1px solid var(--hdt-table-border);
    transition: background-color 150ms ease;
}

.hdt-table-body .hdt-table-row:hover {
    background: var(--hdt-table-row-hover);
}

.hdt-table-row--selected {
    background: var(--hdt-table-row-selected) !important;
}

/* Table Cells */
.hdt-table-cell {
    border-right: 1px solid var(--hdt-table-border);
    vertical-align: middle;
}

.hdt-table-cell:last-child {
    border-right: none;
}

.hdt-table-cell--select {
    width: 48px;
    text-align: center;
}

.hdt-table-cell--empty {
    border: none;
    color: var(--hdt-color-text-secondary);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hdt-table--responsive {
        display: none;
    }
    
    .hdt-table--responsive + .hdt-table-mobile {
        display: block;
    }
    
    .hdt-table-mobile .hdt-table-row {
        display: block;
        border: 1px solid var(--hdt-table-border);
        border-radius: var(--hdt-border-radius-md);
        margin-bottom: 1rem;
        padding: 1rem;
        background: var(--hdt-color-surface-primary);
    }
    
    .hdt-table-mobile .hdt-table-cell {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border: none;
        border-bottom: 1px solid var(--hdt-color-border-secondary);
    }
    
    .hdt-table-mobile .hdt-table-cell:last-child {
        border-bottom: none;
    }
    
    .hdt-table-mobile .hdt-table-cell::before {
        content: attr(data-label) ': ';
        font-weight: 600;
        color: var(--hdt-color-text-secondary);
    }
    
    .hdt-table-mobile .hdt-table-cell--select::before {
        content: '';
    }
}

/* Loading Skeletons */
.hdt-skeleton {
    background: var(--hdt-color-surface-tertiary);
    border-radius: var(--hdt-border-radius-sm);
    animation: skeleton-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.hdt-skeleton--text {
    height: 1rem;
    width: 75%;
}

.hdt-skeleton--checkbox {
    width: 1rem;
    height: 1rem;
}

@keyframes skeleton-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
}

.hdt-reduced-motion .hdt-skeleton {
    animation: none;
}

/* Sort Indicator */
.hdt-table-sort-indicator svg {
    transition: transform 150ms ease, color 150ms ease;
}

/* Table Controls */
.hdt-table-controls {
    margin-bottom: 1rem;
}

.hdt-table-search {
    position: relative;
}

/* Print Styles */
@media print {
    .hdt-table-controls,
    .hdt-table-actions,
    .hdt-table-cell--select {
        display: none !important;
    }
    
    .hdt-table {
        background: white !important;
        color: black !important;
    }
    
    .hdt-table-row--selected {
        background: #f0f0f0 !important;
    }
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-table-cell--header {
        border-bottom-width: 3px;
    }
    
    .hdt-table-row {
        border-bottom-width: 2px;
    }
}

/* Focus Management */
.hdt-table-container:focus-within .hdt-table-cell--sortable:focus {
    z-index: 1;
    position: relative;
}
</style>
@endPushOnce