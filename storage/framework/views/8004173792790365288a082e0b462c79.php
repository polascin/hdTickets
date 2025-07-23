<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'placeholder' => 'Search...',
    'action' => null,
    'method' => 'GET'
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
    'placeholder' => 'Search...',
    'action' => null,
    'method' => 'GET'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="relative" x-data="{ open: false }">
    <form action="<?php echo e($action); ?>" method="<?php echo e($method); ?>" class="relative">
        <?php if($method !== 'GET'): ?>
            <?php echo csrf_field(); ?>
        <?php endif; ?>
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
        <input 
            type="search" 
            name="search"
            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm" 
            placeholder="<?php echo e($placeholder); ?>"
            @focus="open = true"
            @blur="setTimeout(() => open = false, 200)"
        >
    </form>
    
    <!-- Search suggestions dropdown -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200">
        <!-- Search suggestions can be populated dynamically -->
        <div class="py-1">
            <div class="px-4 py-2 text-sm text-gray-500">
                Start typing to search...
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\components\nav-search.blade.php ENDPATH**/ ?>