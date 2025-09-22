@php
  $variants = [
    'default' => 'hdt-card--default',
    'elevated' => 'hdt-card--elevated',
    'bordered' => 'hdt-card--bordered',
    'flush' => 'hdt-card--flush'
  ];

  $variant = $attributes->get('variant', 'default');
  $padding = $attributes->get('padding', 'default'); // none, sm, default, lg
  $interactive = $attributes->get('interactive', false);

  $classes = collect(['hdt-card'])
    ->push($variants[$variant] ?? $variants['default'])
    ->when($padding !== 'default', fn($c) => $c->push("hdt-card--padding-{$padding}"))
    ->when($interactive, fn($c) => $c->push('hdt-card--interactive'))
    ->push($attributes->get('class'))
    ->filter()
    ->join(' ');

  $cardAttributes = $attributes
    ->except(['variant', 'padding', 'interactive', 'class'])
    ->merge(['class' => $classes]);
@endphp

<div {{ $cardAttributes }}>
  @isset($header)
    <div class="hdt-card__header">
      {{ $header }}
    </div>
  @endisset

  @isset($title)
    <div class="hdt-card__header">
      <h3 class="hdt-card__title">{{ $title }}</h3>
      @isset($subtitle)
        <p class="hdt-card__subtitle">{{ $subtitle }}</p>
      @endisset
    </div>
  @endisset

  <div class="hdt-card__body">
    {{ $slot }}
  </div>

  @isset($footer)
    <div class="hdt-card__footer">
      {{ $footer }}
    </div>
  @endisset

  @isset($actions)
    <div class="hdt-card__actions">
      {{ $actions }}
    </div>
  @endisset
</div>

@once
@push('styles')
<style>
/* ============================================================================
   HD Tickets Card Component Styles
============================================================================ */

/* Base card styles */
.hdt-card {
  display: flex;
  flex-direction: column;
  background-color: white;
  border-radius: var(--hd-radius-lg);
  overflow: hidden;
  transition: all var(--hd-transition-base);
  position: relative;
}

.dark .hdt-card {
  background-color: var(--hd-gray-800);
}

/* Card variants */
.hdt-card--default {
  border: 1px solid var(--hd-gray-200);
  box-shadow: var(--hd-shadow-sm);
}

.dark .hdt-card--default {
  border-color: var(--hd-gray-700);
}

.hdt-card--elevated {
  border: 1px solid var(--hd-gray-100);
  box-shadow: var(--hd-shadow-lg);
}

.dark .hdt-card--elevated {
  border-color: var(--hd-gray-700);
  box-shadow: 
    0 10px 15px -3px rgba(0, 0, 0, 0.3), 
    0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

.hdt-card--bordered {
  border: 2px solid var(--hd-gray-200);
  box-shadow: none;
}

.dark .hdt-card--bordered {
  border-color: var(--hd-gray-600);
}

.hdt-card--flush {
  border: none;
  box-shadow: none;
  border-radius: 0;
}

/* Interactive cards */
.hdt-card--interactive {
  cursor: pointer;
}

.hdt-card--interactive:hover {
  transform: translateY(-2px);
  box-shadow: var(--hd-shadow-xl);
}

.hdt-card--interactive:active {
  transform: translateY(-1px);
}

.hdt-card--interactive:focus {
  outline: 2px solid var(--role-primary, var(--hd-primary-500));
  outline-offset: 2px;
}

/* Card header */
.hdt-card__header {
  padding: var(--hd-space-6);
  border-bottom: 1px solid var(--hd-gray-100);
  background-color: var(--hd-gray-50);
}

.dark .hdt-card__header {
  background-color: var(--hd-gray-750);
  border-bottom-color: var(--hd-gray-700);
}

.hdt-card__title {
  font-size: var(--hd-text-lg);
  font-weight: var(--hd-font-semibold);
  color: var(--hd-gray-900);
  margin: 0;
  line-height: var(--hd-leading-tight);
}

.dark .hdt-card__title {
  color: var(--hd-gray-100);
}

.hdt-card__subtitle {
  font-size: var(--hd-text-sm);
  color: var(--hd-gray-600);
  margin: var(--hd-space-1) 0 0 0;
  line-height: var(--hd-leading-normal);
}

.dark .hdt-card__subtitle {
  color: var(--hd-gray-400);
}

/* Card body */
.hdt-card__body {
  flex: 1;
  padding: var(--hd-space-6);
}

/* Card footer */
.hdt-card__footer {
  padding: var(--hd-space-4) var(--hd-space-6);
  border-top: 1px solid var(--hd-gray-100);
  background-color: var(--hd-gray-50);
}

.dark .hdt-card__footer {
  background-color: var(--hd-gray-750);
  border-top-color: var(--hd-gray-700);
}

/* Card actions */
.hdt-card__actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: var(--hd-space-3);
  padding: var(--hd-space-4) var(--hd-space-6);
  border-top: 1px solid var(--hd-gray-100);
  background-color: var(--hd-gray-25);
}

.dark .hdt-card__actions {
  background-color: var(--hd-gray-750);
  border-top-color: var(--hd-gray-700);
}

/* Padding variants */
.hdt-card--padding-none .hdt-card__body {
  padding: 0;
}

.hdt-card--padding-none .hdt-card__header {
  padding: var(--hd-space-4) var(--hd-space-6);
}

.hdt-card--padding-none .hdt-card__footer {
  padding: var(--hd-space-3) var(--hd-space-6);
}

.hdt-card--padding-sm .hdt-card__body {
  padding: var(--hd-space-4);
}

.hdt-card--padding-sm .hdt-card__header {
  padding: var(--hd-space-4);
}

.hdt-card--padding-sm .hdt-card__footer {
  padding: var(--hd-space-3) var(--hd-space-4);
}

.hdt-card--padding-lg .hdt-card__body {
  padding: var(--hd-space-8);
}

.hdt-card--padding-lg .hdt-card__header {
  padding: var(--hd-space-8) var(--hd-space-8) var(--hd-space-6) var(--hd-space-8);
}

.hdt-card--padding-lg .hdt-card__footer {
  padding: var(--hd-space-6) var(--hd-space-8);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .hdt-card__header,
  .hdt-card__body,
  .hdt-card__footer,
  .hdt-card__actions {
    padding-left: var(--hd-space-4);
    padding-right: var(--hd-space-4);
  }

  .hdt-card--padding-lg .hdt-card__header,
  .hdt-card--padding-lg .hdt-card__body,
  .hdt-card--padding-lg .hdt-card__footer {
    padding-left: var(--hd-space-6);
    padding-right: var(--hd-space-6);
  }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
  .hdt-card--interactive:hover {
    transform: none;
  }
  
  .hdt-card--interactive:active {
    transform: none;
  }
}

/* High contrast support */
@media (prefers-contrast: high) {
  .hdt-card--default,
  .hdt-card--elevated {
    border-width: 2px;
  }
  
  .hdt-card--bordered {
    border-width: 3px;
  }
  
  .hdt-card__header,
  .hdt-card__footer,
  .hdt-card__actions {
    border-width: 2px;
  }
}

/* Focus visible for better keyboard navigation */
.hdt-card--interactive:focus-visible {
  outline: 2px solid var(--role-primary, var(--hd-primary-500));
  outline-offset: 2px;
}

/* Loading state (when combined with skeleton) */
.hdt-card--loading {
  pointer-events: none;
  opacity: 0.7;
}

.hdt-card--loading .hdt-card__body {
  position: relative;
  overflow: hidden;
}

.hdt-card--loading .hdt-card__body::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.4),
    transparent
  );
  animation: shimmer 1.5s infinite;
}

.dark .hdt-card--loading .hdt-card__body::before {
  background: linear-gradient(
    90deg,
    transparent,
    rgba(255, 255, 255, 0.1),
    transparent
  );
}

@keyframes shimmer {
  0% {
    left: -100%;
  }
  100% {
    left: 100%;
  }
}
</style>
@endpush
@endonce