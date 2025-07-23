<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'href' => null,
    'subtitle' => null
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
    'title',
    'value',
    'icon' => null,
    'color' => 'blue',
    'href' => null,
    'subtitle' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php if($href): ?>
    <a href="<?php echo e($href); ?>" class="block">
<?php endif; ?>
        <div class="bg-white overflow-hidden shadow rounded-lg <?php echo e($href ? 'hover:shadow-md transition-shadow duration-200' : ''); ?>">
            <div class="p-5">
                <div class="flex items-center">
                    <?php if($icon): ?>
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 text-<?php echo e($color); ?>-600">
                                <?php echo $icon; ?>

                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate"><?php echo e($title); ?></dt>
                            <dd class="text-lg font-medium text-gray-900"><?php echo e($value); ?></dd>
                            <?php if($subtitle): ?>
                                <dd class="text-xs text-gray-500 mt-1"><?php echo e($subtitle); ?></dd>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
<?php if($href): ?>
    </a>
<?php endif; ?>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\components\dashboard\stat-card.blade.php ENDPATH**/ ?>