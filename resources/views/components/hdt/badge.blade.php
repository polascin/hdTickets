@php
  $variants = [
    'default' => 'hdt-badge--default',
    'primary' => 'hdt-badge--primary',
    'secondary' => 'hdt-badge--secondary',
    'success' => 'hdt-badge--success',
    'warning' => 'hdt-badge--warning',
    'danger' => 'hdt-badge--danger',
    'info' => 'hdt-badge--info',
    'neutral' => 'hdt-badge--neutral'
  ];

  $sizes = [
    'xs' => 'hdt-badge--xs',
    'sm' => 'hdt-badge--sm',
    'md' => 'hdt-badge--md',
    'lg' => 'hdt-badge--lg'
  ];

  $shapes = [
    'rounded' => 'hdt-badge--rounded',
    'pill' => 'hdt-badge--pill',
    'square' => 'hdt-badge--square'
  ];

  $variant = $attributes->get('variant', 'default');
  $size = $attributes->get('size', 'md');
  $shape = $attributes->get('shape', 'rounded');
  $dot = $attributes->get('dot', false);
  $pulse = $attributes->get('pulse', false);
  $dismissible = $attributes->get('dismissible', false);

  $classes = collect(['hdt-badge'])
    ->push($variants[$variant] ?? $variants['default'])
    ->push($sizes[$size] ?? $sizes['md'])
    ->push($shapes[$shape] ?? $shapes['rounded'])
    ->when($dot, fn($c) => $c->push('hdt-badge--dot'))
    ->when($pulse, fn($c) => $c->push('hdt-badge--pulse'))
    ->when($dismissible, fn($c) => $c->push('hdt-badge--dismissible'))
    ->push($attributes->get('class'))
    ->filter()
    ->join(' ');

  $badgeAttributes = $attributes
    ->except(['variant', 'size', 'shape', 'dot', 'pulse', 'dismissible', 'class'])
    ->merge(['class' => $classes]);
@endphp

<span {{ $badgeAttributes }}>
  @if($dot)
    <span class="hdt-badge__dot" aria-hidden="true"></span>
  @endif

  @isset($icon)
    <span class="hdt-badge__icon">
      {{ $icon }}
    </span>
  @endisset

  <span class="hdt-badge__content">
    {{ $slot }}
  </span>

  @if($dismissible)
    <button type="button" class="hdt-badge__dismiss" aria-label="Remove badge" @click="$el.closest('.hdt-badge').remove()">
      <svg class="hdt-badge__dismiss-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>
  @endif
</span>

@once
@push('styles')
<style>
/* ============================================================================
   HD Tickets Badge Component Styles
============================================================================ */

/* Base badge styles */
.hdt-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--hd-space-1);
  font-family: inherit;
  font-weight: var(--hd-font-medium);
  text-transform: uppercase;
  letter-spacing: 0.025em;
  white-space: nowrap;
  vertical-align: middle;
  border: 1px solid transparent;
  transition: all var(--hd-transition-base);
  position: relative;
}

/* Badge content */
.hdt-badge__content {
  display: inline-flex;
  align-items: center;
}

/* Badge icon */
.hdt-badge__icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

/* Badge dot indicator */
.hdt-badge__dot {
  width: 0.5em;
  height: 0.5em;
  border-radius: 50%;
  background-color: currentColor;
  flex-shrink: 0;
}

/* Pulse animation for dot */
.hdt-badge--pulse .hdt-badge__dot {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Dismiss button */
.hdt-badge__dismiss {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  color: inherit;
  cursor: pointer;
  padding: 0;
  margin-left: var(--hd-space-1);
  opacity: 0.7;
  transition: opacity var(--hd-transition-base);
  flex-shrink: 0;
}

.hdt-badge__dismiss:hover {
  opacity: 1;
}

.hdt-badge__dismiss-icon {
  width: 0.875em;
  height: 0.875em;
}

/* Size variants */
.hdt-badge--xs {
  padding: var(--hd-space-1) var(--hd-space-2);
  font-size: 0.625rem; /* 10px */
  line-height: 1.2;
  min-height: 16px;
}

.hdt-badge--xs .hdt-badge__icon {
  width: 12px;
  height: 12px;
}

.hdt-badge--sm {
  padding: var(--hd-space-1) var(--hd-space-3);
  font-size: var(--hd-text-xs); /* 12px */
  line-height: 1.3;
  min-height: 20px;
}

.hdt-badge--sm .hdt-badge__icon {
  width: 14px;
  height: 14px;
}

.hdt-badge--md {
  padding: var(--hd-space-2) var(--hd-space-3);
  font-size: var(--hd-text-sm); /* 14px */
  line-height: 1.4;
  min-height: 24px;
}

.hdt-badge--md .hdt-badge__icon {
  width: 16px;
  height: 16px;
}

.hdt-badge--lg {
  padding: var(--hd-space-2) var(--hd-space-4);
  font-size: var(--hd-text-base); /* 16px */
  line-height: 1.5;
  min-height: 28px;
}

.hdt-badge--lg .hdt-badge__icon {
  width: 18px;
  height: 18px;
}

/* Shape variants */
.hdt-badge--rounded {
  border-radius: var(--hd-radius-base);
}

.hdt-badge--pill {
  border-radius: 9999px;
}

.hdt-badge--square {
  border-radius: 0;
}

/* Color variants */
.hdt-badge--default {
  background-color: var(--hd-gray-100);
  color: var(--hd-gray-800);
  border-color: var(--hd-gray-200);
}

.dark .hdt-badge--default {
  background-color: var(--hd-gray-800);
  color: var(--hd-gray-200);
  border-color: var(--hd-gray-700);
}

.hdt-badge--primary {
  background-color: var(--role-primary, var(--hd-primary-600));
  color: white;
  border-color: var(--role-primary, var(--hd-primary-600));
}

.hdt-badge--secondary {
  background-color: var(--hd-gray-600);
  color: white;
  border-color: var(--hd-gray-600);
}

.hdt-badge--success {
  background-color: var(--hd-success-600);
  color: white;
  border-color: var(--hd-success-600);
}

.hdt-badge--warning {
  background-color: var(--hd-warning-500);
  color: var(--hd-gray-900);
  border-color: var(--hd-warning-500);
}

.hdt-badge--danger {
  background-color: var(--hd-error-600);
  color: white;
  border-color: var(--hd-error-600);
}

.hdt-badge--info {
  background-color: var(--hd-primary-500);
  color: white;
  border-color: var(--hd-primary-500);
}

.hdt-badge--neutral {
  background-color: var(--hd-gray-500);
  color: white;
  border-color: var(--hd-gray-500);
}

/* Light variants for better contrast in certain contexts */
.hdt-badge--primary.hdt-badge--light {
  background-color: color-mix(in srgb, var(--role-primary, var(--hd-primary-600)) 15%, white);
  color: var(--role-primary, var(--hd-primary-700));
  border-color: color-mix(in srgb, var(--role-primary, var(--hd-primary-600)) 30%, white);
}

.hdt-badge--success.hdt-badge--light {
  background-color: var(--hd-success-100);
  color: var(--hd-success-700);
  border-color: var(--hd-success-200);
}

.hdt-badge--warning.hdt-badge--light {
  background-color: var(--hd-warning-100);
  color: var(--hd-warning-700);
  border-color: var(--hd-warning-200);
}

.hdt-badge--danger.hdt-badge--light {
  background-color: var(--hd-error-100);
  color: var(--hd-error-700);
  border-color: var(--hd-error-200);
}

.hdt-badge--info.hdt-badge--light {
  background-color: var(--hd-primary-100);
  color: var(--hd-primary-700);
  border-color: var(--hd-primary-200);
}

/* Dark mode adjustments for light variants */
.dark .hdt-badge--primary.hdt-badge--light {
  background-color: color-mix(in srgb, var(--role-primary, var(--hd-primary-400)) 20%, var(--hd-gray-800));
  color: var(--role-primary, var(--hd-primary-300));
  border-color: var(--role-primary, var(--hd-primary-500));
}

.dark .hdt-badge--success.hdt-badge--light {
  background-color: color-mix(in srgb, var(--hd-success-400) 20%, var(--hd-gray-800));
  color: var(--hd-success-300);
  border-color: var(--hd-success-500);
}

.dark .hdt-badge--warning.hdt-badge--light {
  background-color: color-mix(in srgb, var(--hd-warning-400) 20%, var(--hd-gray-800));
  color: var(--hd-warning-300);
  border-color: var(--hd-warning-500);
}

.dark .hdt-badge--danger.hdt-badge--light {
  background-color: color-mix(in srgb, var(--hd-error-400) 20%, var(--hd-gray-800));
  color: var(--hd-error-300);
  border-color: var(--hd-error-500);
}

.dark .hdt-badge--info.hdt-badge--light {
  background-color: color-mix(in srgb, var(--hd-primary-400) 20%, var(--hd-gray-800));
  color: var(--hd-primary-300);
  border-color: var(--hd-primary-500);
}

/* Dot-only badge (status indicator) */
.hdt-badge--dot {
  padding: 0;
  background: none;
  border: none;
  min-width: auto;
  min-height: auto;
  gap: 0;
}

.hdt-badge--dot .hdt-badge__content {
  display: none;
}

.hdt-badge--dot .hdt-badge__dot {
  width: 8px;
  height: 8px;
}

.hdt-badge--dot.hdt-badge--sm .hdt-badge__dot {
  width: 6px;
  height: 6px;
}

.hdt-badge--dot.hdt-badge--lg .hdt-badge__dot {
  width: 10px;
  height: 10px;
}

/* Status-specific colors for dots */
.hdt-badge--dot.hdt-badge--success .hdt-badge__dot {
  background-color: var(--hd-success-500);
}

.hdt-badge--dot.hdt-badge--warning .hdt-badge__dot {
  background-color: var(--hd-warning-500);
}

.hdt-badge--dot.hdt-badge--danger .hdt-badge__dot {
  background-color: var(--hd-error-500);
}

.hdt-badge--dot.hdt-badge--primary .hdt-badge__dot {
  background-color: var(--role-primary, var(--hd-primary-500));
}

/* Dismissible badge animation */
.hdt-badge--dismissible {
  transition: all var(--hd-transition-base), opacity var(--hd-transition-base);
}

.hdt-badge--dismissible:hover .hdt-badge__dismiss {
  opacity: 1;
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .hdt-badge {
    border-width: 2px;
  }
  
  .hdt-badge--dot .hdt-badge__dot {
    border: 1px solid currentColor;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .hdt-badge--pulse .hdt-badge__dot {
    animation: none;
  }
  
  .hdt-badge {
    transition: none;
  }
}

/* Focus management for dismissible badges */
.hdt-badge__dismiss:focus {
  outline: 2px solid var(--hd-primary-500);
  outline-offset: 1px;
  border-radius: var(--hd-radius-sm);
}

.hdt-badge__dismiss:focus-visible {
  outline: 2px solid var(--hd-primary-500);
  outline-offset: 1px;
}
</style>
@endpush
@endonce