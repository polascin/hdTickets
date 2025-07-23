<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'route',
    'routePattern' => null,
    'icon' => null,
    'label',
    'responsive' => false
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
    'route',
    'routePattern' => null,
    'icon' => null,
    'label',
    'responsive' => false
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $routePattern = $routePattern ?? $route . '*';
    $isActive = request()->routeIs($routePattern);
    $component = $responsive ? 'x-responsive-nav-link' : 'x-nav-link';
?>

<<?php echo e($component); ?> :href="route('<?php echo e($route); ?>')" :active="<?php echo e($isActive ? 'true' : 'false'); ?>">
    <?php if($icon): ?>
        <?php echo $icon; ?>

    <?php endif; ?>
    <?php echo e($label); ?>

</<?php echo e($component); ?>>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views\components\nav-menu-item.blade.php ENDPATH**/ ?>