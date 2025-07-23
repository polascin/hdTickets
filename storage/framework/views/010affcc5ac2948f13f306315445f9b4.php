<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'message',
    'status' => null,
    'bannerColor' => 'blue'
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
    'message',
    'status' => null,
    'bannerColor' => 'blue'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div class="bg-<?php echo e($bannerColor); ?>-100 p-4 rounded-lg">
    <h3 class="text-<?php echo e($bannerColor); ?>-800 font-medium">
        <?php echo e($message); ?>

    </h3>
    <?php if($status): ?>
        <p class="text-sm text-<?php echo e($bannerColor); ?>-700 mt-1">
            <?php echo e($status); ?>

        </p>
    <?php endif; ?>
</div>

<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\components\dashboard\welcome-banner.blade.php ENDPATH**/ ?>