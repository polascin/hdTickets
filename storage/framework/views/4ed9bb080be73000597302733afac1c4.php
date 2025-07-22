<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php else: ?>
            <style>
                /* Tailwind CSS fallback styles for guest pages */
                .min-h-screen { min-height: 100vh; }
                .flex { display: flex; }
                .flex-col { flex-direction: column; }
                .justify-center { justify-content: center; }
                .items-center { align-items: center; }
                .pt-6 { padding-top: 1.5rem; }
                .sm\:pt-0 { padding-top: 0; }
                .bg-gray-100 { background-color: #f3f4f6; }
                .w-20 { width: 5rem; }
                .h-20 { height: 5rem; }
                .fill-current { fill: currentColor; }
                .text-gray-500 { color: #6b7280; }
                .w-full { width: 100%; }
                .sm\:max-w-md { max-width: 28rem; }
                .mt-6 { margin-top: 1.5rem; }
                .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
                .py-4 { padding-top: 1rem; padding-bottom: 1rem; }
                .bg-white { background-color: #ffffff; }
                .shadow-md { box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
                .overflow-hidden { overflow: hidden; }
                .sm\:rounded-lg { border-radius: 0.5rem; }
                .font-sans { font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif; }
                .text-gray-900 { color: #111827; }
                .antialiased { -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
                .block { display: block; }
                .font-medium { font-weight: 500; }
                .text-sm { font-size: 0.875rem; }
                .text-gray-700 { color: #374151; }
                .border-gray-300 { border-color: #d1d5db; }
                .focus\:border-indigo-500:focus { border-color: #6366f1; }
                .focus\:ring-indigo-500:focus { --tw-ring-color: #6366f1; }
                .rounded-md { border-radius: 0.375rem; }
                .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
                .mt-1 { margin-top: 0.25rem; }
                .mt-4 { margin-top: 1rem; }
                .inline-flex { display: inline-flex; }
                .ms-2 { margin-left: 0.5rem; }
                .text-gray-600 { color: #4b5563; }
                .underline { text-decoration: underline; }
                .hover\:text-gray-900:hover { color: #111827; }
                .focus\:outline-none:focus { outline: none; }
                .focus\:ring-2:focus { --tw-ring-width: 2px; }
                .focus\:ring-offset-2:focus { --tw-ring-offset-width: 2px; }
                .items-center { align-items: center; }
                .justify-end { justify-content: flex-end; }
                .px-4 { padding-left: 1rem; padding-right: 1rem; }
                .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                .bg-gray-800 { background-color: #1f2937; }
                .border { border-width: 1px; }
                .border-transparent { border-color: transparent; }
                .font-semibold { font-weight: 600; }
                .text-xs { font-size: 0.75rem; }
                .text-white { color: #ffffff; }
                .uppercase { text-transform: uppercase; }
                .tracking-widest { letter-spacing: 0.1em; }
                .hover\:bg-gray-700:hover { background-color: #374151; }
                .focus\:bg-gray-700:focus { background-color: #374151; }
                .active\:bg-gray-900:active { background-color: #111827; }
                .transition { transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter; }
                .ease-in-out { transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); }
                .duration-150 { transition-duration: 150ms; }
                .ms-3 { margin-left: 0.75rem; }
                input { border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; width: 100%; }
                button { cursor: pointer; }
                @media (min-width: 640px) {
                    .sm\:pt-0 { padding-top: 0; }
                    .sm\:max-w-md { max-width: 28rem; }
                    .sm\:rounded-lg { border-radius: 0.5rem; }
                }
            </style>
        <?php endif; ?>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-20 h-20 fill-current text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-20 h-20 fill-current text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                <?php echo e($slot); ?>

            </div>
        </div>
    </body>
</html>
<?php /**PATH C:\Users\polas\OneDrive\www\hdtickets\resources\views/layouts/guest.blade.php ENDPATH**/ ?>