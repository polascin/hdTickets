@props([
    'id' => 'enhanced-table-' . uniqid(),
    'headers' => [],
    'data' => [],
    'sortable' => true,
    'filterable' => true,
    'resizable' => true,
    'exportable' => true,
    'customizable' => true,
    'striped' => true,
    'hover' => true,
    'compact' => false,
    'loading' => false,
    'emptyMessage' => 'No data available',
    'searchPlaceholder' => 'Search...'
])

@php
    $tableClasses = collect([
        'enhanced-table w-full',
        'bg-white dark:bg-slate-800',
        'border-collapse',
        'rounded-lg overflow-hidden',
        'shadow-sm',
        $compact ? 'text-sm' : '',
    ])->filter()->implode(' ');
    
    $containerClasses = collect([
        'enhanced-table-container',
        'bg-white dark:bg-slate-800',
        'border border-gray-200 dark:border-slate-700',
        'rounded-lg overflow-hidden',
        'shadow-sm'
    ])->filter()->implode(' ');
@endphp

<div class="{{ $containerClasses }}" data-table-id="{{ $id }}">
    @if($customizable || $filterable || $exportable)
        <!-- Table Controls -->
        <div class="table-controls bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600 px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Table Options</h3>
                    
                    @if($customizable)
<button type="button" 
                                class="reset-table-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <x-icon name="undo" class="w-3.5 h-3.5 mr-1" />Reset
                        </button>
                    @endif
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($filterable)
                        <div class="relative">
                            <input type="text" 
                                   class="global-search form-input text-xs w-48 pl-8 pr-3 py-1.5" 
                                   placeholder="{{ $searchPlaceholder }}">
<x-icon name="search" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                        </div>
                    @endif
                    
                    @if($customizable)
<button type="button" 
                                class="column-settings-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <x-icon name="view-columns" class="w-3.5 h-3.5 mr-1" />Columns
                        </button>
                    @endif
                    
                    @if($exportable)
<button type="button" 
                                class="export-table-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <x-icon name="download" class="w-3.5 h-3.5 mr-1" />Export
                        </button>
                    @endif
                </div>
            </div>
            
            @if($customizable)
                <!-- Column Toggles (Initially Hidden) -->
                <div class="column-toggles mt-3 hidden">
                    <div class="border-t border-gray-200 dark:border-slate-600 pt-3">
                        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Visible Columns</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                            @foreach($headers as $index => $header)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                           class="column-toggle rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800" 
                                           data-column="{{ $header['key'] ?? 'col_' . $index }}" 
                                           checked>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $header['label'] ?? $header }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Table Container -->
    <div class="table-wrapper overflow-x-auto">
        @if($loading)
            <!-- Loading State -->
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center space-x-2">
                    <div class="spinner w-5 h-5 border-2 border-gray-300 dark:border-slate-600 border-t-blue-600 rounded-full animate-spin"></div>
                    <span class="text-gray-500 dark:text-gray-400">Loading...</span>
                </div>
            </div>
        @elseif(empty($data))
            <!-- Empty State -->
            <div class="p-8 text-center">
<div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 dark:bg-slate-700 rounded-full mb-4">
                    <x-icon name="table" class="text-gray-400 dark:text-gray-500 w-6 h-6" />
                </div>
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Data Available</h4>
                <p class="text-gray-500 dark:text-gray-400">{{ $emptyMessage }}</p>
            </div>
        @else
            <!-- Data Table -->
            <table id="{{ $id }}" class="{{ $tableClasses }}">
                <thead class="bg-gray-50 dark:bg-slate-700">
                    <tr>
                        @foreach($headers as $index => $header)
                            @php
                                $headerConfig = is_array($header) ? $header : ['label' => $header];
                                $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                $isSortable = ($headerConfig['sortable'] ?? $sortable) && $sortable;
                                $isFilterable = ($headerConfig['filterable'] ?? $filterable) && $filterable;
                                $isResizable = ($headerConfig['resizable'] ?? $resizable) && $resizable;
                                $minWidth = $headerConfig['min_width'] ?? 80;
                                $maxWidth = $headerConfig['max_width'] ?? 400;
                            @endphp
                            
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-slate-600 {{ $isSortable ? 'sortable cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-slate-600' : '' }} {{ $isResizable ? 'relative' : '' }}"
                                data-column="{{ $columnKey }}"
                                data-min-width="{{ $minWidth }}"
                                data-max-width="{{ $maxWidth }}"
                                @if($isSortable) title="Click to sort" @endif>
                                
                                @if($resizable && $isResizable)
<span class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 mr-2 inline-block">
                                        <x-icon name="grip-vertical" class="w-3.5 h-3.5" />
                                    </span>
                                @endif
                                
                                <span class="header-text">{{ $headerConfig['label'] ?? $header }}</span>
                                
                                @if($isSortable)
<x-icon name="arrows-up-down" class="sort-icon ml-2 w-3.5 h-3.5 text-gray-400 dark:text-gray-500" />
                                @endif
                                
                                @if($resizable && $isResizable)
                                    <div class="column-resizer absolute right-0 top-0 w-1 h-full cursor-col-resize bg-transparent hover:bg-blue-500 hover:opacity-50"></div>
                                @endif
                            </th>
                        @endforeach
                    </tr>
                    
                    @if($filterable)
                        <!-- Filter Row -->
                        <tr class="filter-row bg-gray-25 dark:bg-slate-750">
                            @foreach($headers as $index => $header)
                                @php
                                    $headerConfig = is_array($header) ? $header : ['label' => $header];
                                    $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                    $isFilterable = ($headerConfig['filterable'] ?? true) && $filterable;
                                @endphp
                                
                                <th class="px-4 py-2 border-b border-gray-200 dark:border-slate-600">
                                    @if($isFilterable)
                                        <input type="text" 
                                               class="column-filter form-input text-xs w-full py-1 px-2 border border-gray-300 dark:border-slate-600 rounded focus:border-blue-500 dark:focus:border-blue-400 focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-700 text-gray-900 dark:text-white" 
                                               data-column="{{ $columnKey }}"
                                               placeholder="Filter {{ $headerConfig['label'] ?? $header }}">
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
                
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                    @foreach($data as $rowIndex => $row)
                        <tr class="table-row {{ $hover ? 'hover:bg-gray-50 dark:hover:bg-slate-700' : '' }} {{ $striped && $rowIndex % 2 === 1 ? 'bg-gray-25 dark:bg-slate-750' : '' }} transition-colors duration-150">
                            @foreach($headers as $index => $header)
                                @php
                                    $headerConfig = is_array($header) ? $header : ['label' => $header];
                                    $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                    $cellValue = is_array($row) ? ($row[$columnKey] ?? '') : (is_object($row) ? ($row->$columnKey ?? '') : '');
                                @endphp
                                
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700">
                                    @if(isset($headerConfig['render']))
                                        {!! $headerConfig['render']($cellValue, $row, $rowIndex) !!}
                                    @else
                                        {{ $cellValue }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    
    @if(!empty($data))
        <!-- Table Footer -->
        <div class="table-footer bg-gray-50 dark:bg-slate-700 border-t border-gray-200 dark:border-slate-600 px-4 py-3">
            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <div class="table-info">
                    Showing <span class="font-medium visible-rows">{{ count($data) }}</span> of <span class="font-medium total-rows">{{ count($data) }}</span> entries
                </div>
                
                @isset($pagination)
                    <div class="table-pagination">
                        {{ $pagination }}
                    </div>
                @endisset
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    .enhanced-table-container {
        max-width: 100%;
    }
    
    .table-wrapper {
        -webkit-overflow-scrolling: touch;
    }
    
    .enhanced-table {
        min-width: 100%;
    }
    
    .enhanced-table th.sortable:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .dark-mode .enhanced-table th.sortable:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    .enhanced-table .sort-icon {
        transition: transform 0.2s ease;
    }
    
    .enhanced-table .sort-icon.fa-sort-up,
    .enhanced-table .sort-icon.fa-sort-down {
        color: #3b82f6;
    }
    
    .dark-mode .enhanced-table .sort-icon.fa-sort-up,
    .dark-mode .enhanced-table .sort-icon.fa-sort-down {
        color: #60a5fa;
    }
    
    .column-resizer {
        transition: background-color 0.2s ease;
    }
    
    .column-resizer:hover {
        background-color: #3b82f6 !important;
        opacity: 0.5 !important;
    }
    
    /* Responsive improvements */
    @media (max-width: 768px) {
        .enhanced-table-container .table-controls {
            padding: 1rem;
        }
        
        .enhanced-table-container .table-controls .flex {
            flex-direction: column;
            gap: 1rem;
        }
        
        .enhanced-table th,
        .enhanced-table td {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .global-search {
            width: 100% !important;
        }
    }
    
    /* Loading animation */
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
    
    .spinner {
        animation: spin 1s linear infinite;
    }
    
    /* High contrast mode */
    .high-contrast .enhanced-table {
        border: 2px solid #000;
    }
    
    .high-contrast .dark-mode .enhanced-table {
        border: 2px solid #fff;
    }
    
    .high-contrast .enhanced-table th,
    .high-contrast .enhanced-table td {
        border: 1px solid #000;
    }
    
    .high-contrast .dark-mode .enhanced-table th,
    .high-contrast .dark-mode .enhanced-table td {
        border: 1px solid #fff;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize table customizer for this table
    const tableId = '{{ $id }}';
    const tableElement = document.getElementById(tableId);
    
    if (tableElement && typeof TableCustomizer !== 'undefined') {
        new TableCustomizer('#' + tableId, {
            enableColumnReorder: {{ $customizable ? 'true' : 'false' }},
            enableColumnResize: {{ $resizable ? 'true' : 'false' }},
            enableColumnToggle: {{ $customizable ? 'true' : 'false' }},
            enableSort: {{ $sortable ? 'true' : 'false' }},
            enableFilters: {{ $filterable ? 'true' : 'false' }}
        });
    }
});
</script>
@endpush
