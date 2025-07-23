<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
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
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
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
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
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
?>

<div class="<?php echo e($containerClasses); ?>" data-table-id="<?php echo e($id); ?>">
    <?php if($customizable || $filterable || $exportable): ?>
        <!-- Table Controls -->
        <div class="table-controls bg-gray-50 dark:bg-slate-700 border-b border-gray-200 dark:border-slate-600 px-4 py-3">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Table Options</h3>
                    
                    <?php if($customizable): ?>
                        <button type="button" 
                                class="reset-table-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <i class="fas fa-undo mr-1"></i>Reset
                        </button>
                    <?php endif; ?>
                </div>
                
                <div class="flex items-center space-x-2">
                    <?php if($filterable): ?>
                        <div class="relative">
                            <input type="text" 
                                   class="global-search form-input text-xs w-48 pl-8 pr-3 py-1.5" 
                                   placeholder="<?php echo e($searchPlaceholder); ?>">
                            <i class="fas fa-search absolute left-2.5 top-1/2 transform -translate-y-1/2 text-gray-400 text-xs"></i>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($customizable): ?>
                        <button type="button" 
                                class="column-settings-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <i class="fas fa-columns mr-1"></i>Columns
                        </button>
                    <?php endif; ?>
                    
                    <?php if($exportable): ?>
                        <button type="button" 
                                class="export-table-btn btn-secondary text-xs px-3 py-1 rounded-md border border-gray-300 dark:border-slate-600 hover:bg-gray-100 dark:hover:bg-slate-600 transition-colors">
                            <i class="fas fa-download mr-1"></i>Export
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if($customizable): ?>
                <!-- Column Toggles (Initially Hidden) -->
                <div class="column-toggles mt-3 hidden">
                    <div class="border-t border-gray-200 dark:border-slate-600 pt-3">
                        <h4 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2">Visible Columns</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" 
                                           class="column-toggle rounded border-gray-300 dark:border-slate-600 text-blue-600 focus:border-blue-300 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800" 
                                           data-column="<?php echo e($header['key'] ?? 'col_' . $index); ?>" 
                                           checked>
                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"><?php echo e($header['label'] ?? $header); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Table Container -->
    <div class="table-wrapper overflow-x-auto">
        <?php if($loading): ?>
            <!-- Loading State -->
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center space-x-2">
                    <div class="spinner w-5 h-5 border-2 border-gray-300 dark:border-slate-600 border-t-blue-600 rounded-full animate-spin"></div>
                    <span class="text-gray-500 dark:text-gray-400">Loading...</span>
                </div>
            </div>
        <?php elseif(empty($data)): ?>
            <!-- Empty State -->
            <div class="p-8 text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 bg-gray-100 dark:bg-slate-700 rounded-full mb-4">
                    <i class="fas fa-table text-gray-400 dark:text-gray-500 text-xl"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Data Available</h4>
                <p class="text-gray-500 dark:text-gray-400"><?php echo e($emptyMessage); ?></p>
            </div>
        <?php else: ?>
            <!-- Data Table -->
            <table id="<?php echo e($id); ?>" class="<?php echo e($tableClasses); ?>">
                <thead class="bg-gray-50 dark:bg-slate-700">
                    <tr>
                        <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $headerConfig = is_array($header) ? $header : ['label' => $header];
                                $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                $isSortable = ($headerConfig['sortable'] ?? $sortable) && $sortable;
                                $isFilterable = ($headerConfig['filterable'] ?? $filterable) && $filterable;
                                $isResizable = ($headerConfig['resizable'] ?? $resizable) && $resizable;
                                $minWidth = $headerConfig['min_width'] ?? 80;
                                $maxWidth = $headerConfig['max_width'] ?? 400;
                            ?>
                            
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-b border-gray-200 dark:border-slate-600 <?php echo e($isSortable ? 'sortable cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-slate-600' : ''); ?> <?php echo e($isResizable ? 'relative' : ''); ?>"
                                data-column="<?php echo e($columnKey); ?>"
                                data-min-width="<?php echo e($minWidth); ?>"
                                data-max-width="<?php echo e($maxWidth); ?>"
                                <?php if($isSortable): ?> title="Click to sort" <?php endif; ?>>
                                
                                <?php if($resizable && $isResizable): ?>
                                    <span class="drag-handle cursor-move text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400 mr-2 inline-block">
                                        <i class="fas fa-grip-vertical text-xs"></i>
                                    </span>
                                <?php endif; ?>
                                
                                <span class="header-text"><?php echo e($headerConfig['label'] ?? $header); ?></span>
                                
                                <?php if($isSortable): ?>
                                    <i class="fas fa-sort sort-icon ml-2 text-gray-400 dark:text-gray-500"></i>
                                <?php endif; ?>
                                
                                <?php if($resizable && $isResizable): ?>
                                    <div class="column-resizer absolute right-0 top-0 w-1 h-full cursor-col-resize bg-transparent hover:bg-blue-500 hover:opacity-50"></div>
                                <?php endif; ?>
                            </th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tr>
                    
                    <?php if($filterable): ?>
                        <!-- Filter Row -->
                        <tr class="filter-row bg-gray-25 dark:bg-slate-750">
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $headerConfig = is_array($header) ? $header : ['label' => $header];
                                    $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                    $isFilterable = ($headerConfig['filterable'] ?? true) && $filterable;
                                ?>
                                
                                <th class="px-4 py-2 border-b border-gray-200 dark:border-slate-600">
                                    <?php if($isFilterable): ?>
                                        <input type="text" 
                                               class="column-filter form-input text-xs w-full py-1 px-2 border border-gray-300 dark:border-slate-600 rounded focus:border-blue-500 dark:focus:border-blue-400 focus:ring-1 focus:ring-blue-500 dark:focus:ring-blue-400 bg-white dark:bg-slate-700 text-gray-900 dark:text-white" 
                                               data-column="<?php echo e($columnKey); ?>"
                                               placeholder="Filter <?php echo e($headerConfig['label'] ?? $header); ?>">
                                    <?php endif; ?>
                                </th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endif; ?>
                </thead>
                
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-slate-700">
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rowIndex => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="table-row <?php echo e($hover ? 'hover:bg-gray-50 dark:hover:bg-slate-700' : ''); ?> <?php echo e($striped && $rowIndex % 2 === 1 ? 'bg-gray-25 dark:bg-slate-750' : ''); ?> transition-colors duration-150">
                            <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $headerConfig = is_array($header) ? $header : ['label' => $header];
                                    $columnKey = $headerConfig['key'] ?? 'col_' . $index;
                                    $cellValue = is_array($row) ? ($row[$columnKey] ?? '') : (is_object($row) ? ($row->$columnKey ?? '') : '');
                                ?>
                                
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white border-b border-gray-100 dark:border-slate-700">
                                    <?php if(isset($headerConfig['render'])): ?>
                                        <?php echo $headerConfig['render']($cellValue, $row, $rowIndex); ?>

                                    <?php else: ?>
                                        <?php echo e($cellValue); ?>

                                    <?php endif; ?>
                                </td>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <?php if(!empty($data)): ?>
        <!-- Table Footer -->
        <div class="table-footer bg-gray-50 dark:bg-slate-700 border-t border-gray-200 dark:border-slate-600 px-4 py-3">
            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                <div class="table-info">
                    Showing <span class="font-medium visible-rows"><?php echo e(count($data)); ?></span> of <span class="font-medium total-rows"><?php echo e(count($data)); ?></span> entries
                </div>
                
                <?php if(isset($pagination)): ?>
                    <div class="table-pagination">
                        <?php echo e($pagination); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php $__env->startPush('styles'); ?>
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize table customizer for this table
    const tableId = '<?php echo e($id); ?>';
    const tableElement = document.getElementById(tableId);
    
    if (tableElement && typeof TableCustomizer !== 'undefined') {
        new TableCustomizer('#' + tableId, {
            enableColumnReorder: <?php echo e($customizable ? 'true' : 'false'); ?>,
            enableColumnResize: <?php echo e($resizable ? 'true' : 'false'); ?>,
            enableColumnToggle: <?php echo e($customizable ? 'true' : 'false'); ?>,
            enableSort: <?php echo e($sortable ? 'true' : 'false'); ?>,
            enableFilters: <?php echo e($filterable ? 'true' : 'false'); ?>

        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\components\enhanced-table.blade.php ENDPATH**/ ?>