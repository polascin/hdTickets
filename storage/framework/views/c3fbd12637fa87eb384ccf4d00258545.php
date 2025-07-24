

<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'name' => 'platform',
    'id' => null,
    'value' => '',
    'showAll' => true,
    'allText' => 'All Platforms',
    'class' => 'form-select',
    'required' => false,
    'disabled' => false,
    'includeOnly' => [], // Only include specific platforms
    'excludePlatforms' => [], // Exclude specific platforms
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
    'name' => 'platform',
    'id' => null,
    'value' => '',
    'showAll' => true,
    'allText' => 'All Platforms',
    'class' => 'form-select',
    'required' => false,
    'disabled' => false,
    'includeOnly' => [], // Only include specific platforms
    'excludePlatforms' => [], // Exclude specific platforms
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<?php
    $platforms = collect(config('platforms.display_order'))
        ->sortBy('order');
    
    // Filter platforms if includeOnly is specified
    if (!empty($includeOnly)) {
        $platforms = $platforms->filter(function($platform) use ($includeOnly) {
            return in_array($platform['key'], $includeOnly);
        });
    }
    
    // Exclude specific platforms if specified
    if (!empty($excludePlatforms)) {
        $platforms = $platforms->filter(function($platform) use ($excludePlatforms) {
            return !in_array($platform['key'], $excludePlatforms);
        });
    }
    
    $selectId = $id ?? $name;
?>

<select 
    name="<?php echo e($name); ?>" 
    id="<?php echo e($selectId); ?>" 
    class="<?php echo e($class); ?>"
    <?php if($required): ?> required <?php endif; ?>
    <?php if($disabled): ?> disabled <?php endif; ?>
    <?php echo e($attributes); ?>

>
    <?php if($showAll): ?>
        <option value=""><?php echo e($allText); ?></option>
    <?php endif; ?>
    
    <?php $__currentLoopData = $platforms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $platform): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <option 
            value="<?php echo e($platform['key']); ?>" 
            <?php if($value === $platform['key']): ?> selected <?php endif; ?>
        >
            <?php echo e($platform['display_name']); ?>

        </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</select>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/components/platform-select.blade.php ENDPATH**/ ?>