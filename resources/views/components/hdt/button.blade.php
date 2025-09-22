@php
  $variants = [
    'primary' => 'hdt-button--primary',
    'secondary' => 'hdt-button--secondary', 
    'ghost' => 'hdt-button--ghost',
    'danger' => 'hdt-button--danger',
    'success' => 'hdt-button--success',
    'warning' => 'hdt-button--warning'
  ];

  $sizes = [
    'xs' => 'hdt-button--xs',
    'sm' => 'hdt-button--sm',
    'md' => 'hdt-button--md',
    'lg' => 'hdt-button--lg',
    'xl' => 'hdt-button--xl'
  ];

  $variant = $attributes->get('variant', 'primary');
  $size = $attributes->get('size', 'md');
  $disabled = $attributes->get('disabled', false);
  $loading = $attributes->get('loading', false);
  $iconOnly = $attributes->get('iconOnly', false);
  $href = $attributes->get('href');

  $classes = collect(['hdt-button'])
    ->push($variants[$variant] ?? $variants['primary'])
    ->push($sizes[$size] ?? $sizes['md'])
    ->when($iconOnly, fn($c) => $c->push('hdt-button--icon-only'))
    ->when($disabled || $loading, fn($c) => $c->push('hdt-button--disabled'))
    ->when($loading, fn($c) => $c->push('hdt-button--loading'))
    ->push($attributes->get('class'))
    ->filter()
    ->join(' ');

  $tag = $href ? 'a' : 'button';
  
  $buttonAttributes = $attributes
    ->except(['variant', 'size', 'iconOnly', 'loading', 'href', 'class'])
    ->when($href, fn($attr) => $attr->merge(['href' => $href]))
    ->when(!$href, fn($attr) => $attr->merge(['type' => $attributes->get('type', 'button')]))
    ->when($disabled || $loading, fn($attr) => $attr->merge(['disabled' => true]))
    ->when($loading, fn($attr) => $attr->merge(['aria-busy' => 'true']))
    ->merge(['class' => $classes]);
@endphp

<{{ $tag }} {{ $buttonAttributes }}>
  @if($loading)
    <span class="hdt-button__spinner" aria-hidden="true">
      <svg class="hdt-spinner" viewBox="0 0 24 24" fill="none">
        <circle class="hdt-spinner__track" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
        <path class="hdt-spinner__path" fill="currentColor" 
              d="m12,2a10,10 0 0,1 10,10h-2a8,8 0 0,0-8-8z"/>
      </svg>
    </span>
  @endif

  @if($iconOnly)
    {{ $slot }}
  @else
    <span class="hdt-button__content">
      {{ $slot }}
    </span>
  @endif
</{{ $tag }}>

@once
@push('styles')
<style>
/* ============================================================================
   HD Tickets Button Component Styles
============================================================================ */

/* Base button styles */
.hdt-button {
  --button-color: var(--hd-gray-900);
  --button-bg: var(--hd-gray-100);
  --button-border: var(--hd-gray-300);
  --button-hover-color: var(--hd-gray-900);
  --button-hover-bg: var(--hd-gray-200);
  --button-hover-border: var(--hd-gray-400);
  --button-focus-ring: var(--hd-primary-500);

  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--hd-space-2);
  font-family: inherit;
  font-weight: var(--hd-font-medium);
  text-decoration: none;
  border: 1px solid var(--button-border);
  border-radius: var(--hd-radius-md);
  background-color: var(--button-bg);
  color: var(--button-color);
  cursor: pointer;
  transition: all var(--hd-transition-base);
  user-select: none;
  white-space: nowrap;
  position: relative;
  overflow: hidden;
}

/* Hover and focus states */
.hdt-button:hover:not(.hdt-button--disabled) {
  background-color: var(--button-hover-bg);
  border-color: var(--button-hover-border);
  color: var(--button-hover-color);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.hdt-button:focus {
  outline: 2px solid var(--button-focus-ring);
  outline-offset: 2px;
}

.hdt-button:active:not(.hdt-button--disabled) {
  transform: translateY(0);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Primary variant */
.hdt-button--primary {
  --button-color: white;
  --button-bg: var(--role-primary, var(--hd-primary-600));
  --button-border: var(--role-primary, var(--hd-primary-600));
  --button-hover-color: white;
  --button-hover-bg: var(--role-primary, var(--hd-primary-700));
  --button-hover-border: var(--role-primary, var(--hd-primary-700));
  --button-focus-ring: var(--role-primary, var(--hd-primary-500));
}

/* Secondary variant */
.hdt-button--secondary {
  --button-color: var(--role-primary, var(--hd-primary-600));
  --button-bg: var(--hd-gray-50);
  --button-border: var(--role-primary, var(--hd-primary-300));
  --button-hover-color: var(--role-primary, var(--hd-primary-700));
  --button-hover-bg: color-mix(in srgb, var(--role-primary, var(--hd-primary-600)) 5%, white);
  --button-hover-border: var(--role-primary, var(--hd-primary-400));
}

/* Ghost variant */
.hdt-button--ghost {
  --button-color: var(--hd-gray-700);
  --button-bg: transparent;
  --button-border: transparent;
  --button-hover-color: var(--hd-gray-900);
  --button-hover-bg: var(--hd-gray-100);
  --button-hover-border: var(--hd-gray-200);
}

.dark .hdt-button--ghost {
  --button-color: var(--hd-gray-300);
  --button-hover-color: var(--hd-gray-100);
  --button-hover-bg: var(--hd-gray-800);
  --button-hover-border: var(--hd-gray-700);
}

/* Danger variant */
.hdt-button--danger {
  --button-color: white;
  --button-bg: var(--hd-error-600);
  --button-border: var(--hd-error-600);
  --button-hover-color: white;
  --button-hover-bg: var(--hd-error-700);
  --button-hover-border: var(--hd-error-700);
  --button-focus-ring: var(--hd-error-500);
}

/* Success variant */
.hdt-button--success {
  --button-color: white;
  --button-bg: var(--hd-success-600);
  --button-border: var(--hd-success-600);
  --button-hover-color: white;
  --button-hover-bg: var(--hd-success-700);
  --button-hover-border: var(--hd-success-700);
  --button-focus-ring: var(--hd-success-500);
}

/* Warning variant */
.hdt-button--warning {
  --button-color: var(--hd-gray-900);
  --button-bg: var(--hd-warning-400);
  --button-border: var(--hd-warning-400);
  --button-hover-color: var(--hd-gray-900);
  --button-hover-bg: var(--hd-warning-500);
  --button-hover-border: var(--hd-warning-500);
  --button-focus-ring: var(--hd-warning-500);
}

/* Size variants */
.hdt-button--xs {
  padding: var(--hd-space-1) var(--hd-space-3);
  font-size: var(--hd-text-xs);
  line-height: var(--hd-leading-tight);
  min-height: 28px;
}

.hdt-button--sm {
  padding: var(--hd-space-2) var(--hd-space-3);
  font-size: var(--hd-text-sm);
  line-height: var(--hd-leading-tight);
  min-height: 32px;
}

.hdt-button--md {
  padding: var(--hd-space-2) var(--hd-space-4);
  font-size: var(--hd-text-base);
  line-height: var(--hd-leading-normal);
  min-height: 40px;
}

.hdt-button--lg {
  padding: var(--hd-space-3) var(--hd-space-6);
  font-size: var(--hd-text-lg);
  line-height: var(--hd-leading-normal);
  min-height: 48px;
}

.hdt-button--xl {
  padding: var(--hd-space-4) var(--hd-space-8);
  font-size: var(--hd-text-xl);
  line-height: var(--hd-leading-normal);
  min-height: 56px;
}

/* Icon-only buttons */
.hdt-button--icon-only {
  width: auto;
  aspect-ratio: 1;
  padding: var(--hd-space-2);
}

.hdt-button--icon-only.hdt-button--xs { width: 28px; padding: var(--hd-space-1); }
.hdt-button--icon-only.hdt-button--sm { width: 32px; padding: var(--hd-space-2); }
.hdt-button--icon-only.hdt-button--md { width: 40px; padding: var(--hd-space-2); }
.hdt-button--icon-only.hdt-button--lg { width: 48px; padding: var(--hd-space-3); }
.hdt-button--icon-only.hdt-button--xl { width: 56px; padding: var(--hd-space-4); }

/* Disabled state */
.hdt-button--disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

/* Loading state */
.hdt-button--loading {
  cursor: wait;
}

.hdt-button--loading .hdt-button__content {
  opacity: 0.7;
}

/* Spinner */
.hdt-button__spinner {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.hdt-spinner {
  width: 1em;
  height: 1em;
  animation: spin 1s linear infinite;
}

.hdt-spinner__track {
  opacity: 0.2;
}

.hdt-spinner__path {
  opacity: 0.8;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Dark mode adjustments */
.dark .hdt-button {
  --button-color: var(--hd-gray-100);
  --button-bg: var(--hd-gray-800);
  --button-border: var(--hd-gray-600);
  --button-hover-color: var(--hd-gray-50);
  --button-hover-bg: var(--hd-gray-700);
  --button-hover-border: var(--hd-gray-500);
}

.dark .hdt-button--secondary {
  --button-color: var(--role-primary, var(--hd-primary-400));
  --button-bg: var(--hd-gray-800);
  --button-border: var(--role-primary, var(--hd-primary-400));
  --button-hover-color: var(--role-primary, var(--hd-primary-300));
  --button-hover-bg: color-mix(in srgb, var(--role-primary, var(--hd-primary-400)) 10%, var(--hd-gray-800));
  --button-hover-border: var(--role-primary, var(--hd-primary-300));
}

/* Touch targets (minimum 44px for accessibility) */
@media (pointer: coarse) {
  .hdt-button {
    min-height: var(--hd-touch-target-min);
  }
  
  .hdt-button--xs {
    min-height: var(--hd-touch-target-min);
    padding: var(--hd-space-2) var(--hd-space-4);
  }
  
  .hdt-button--sm {
    min-height: var(--hd-touch-target-min);
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .hdt-button {
    border-width: 2px;
  }
  
  .hdt-button:focus {
    outline-width: 3px;
  }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .hdt-button {
    transition: none;
  }
  
  .hdt-button:hover {
    transform: none;
  }
  
  .hdt-spinner {
    animation: none;
  }
}
</style>
@endpush
@endonce