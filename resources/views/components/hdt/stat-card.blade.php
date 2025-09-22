@php
  $variant = $attributes->get('variant', 'default'); // default, primary, success, warning, danger
  $size = $attributes->get('size', 'default'); // sm, default, lg
  $loading = $attributes->get('loading', false);
  $trend = $attributes->get('trend'); // up, down, neutral
  $trendValue = $attributes->get('trendValue');
  $trendLabel = $attributes->get('trendLabel', 'vs last period');

  $variants = [
    'default' => 'hdt-stat-card--default',
    'primary' => 'hdt-stat-card--primary',
    'success' => 'hdt-stat-card--success',
    'warning' => 'hdt-stat-card--warning',
    'danger' => 'hdt-stat-card--danger'
  ];

  $sizes = [
    'sm' => 'hdt-stat-card--sm',
    'default' => '',
    'lg' => 'hdt-stat-card--lg'
  ];

  $trendClasses = [
    'up' => 'hdt-stat-card__trend--up',
    'down' => 'hdt-stat-card__trend--down',
    'neutral' => 'hdt-stat-card__trend--neutral'
  ];

  $classes = collect(['hdt-stat-card'])
    ->push($variants[$variant] ?? $variants['default'])
    ->push($sizes[$size] ?? '')
    ->when($loading, fn($c) => $c->push('hdt-stat-card--loading'))
    ->push($attributes->get('class'))
    ->filter()
    ->join(' ');

  $cardAttributes = $attributes
    ->except(['variant', 'size', 'loading', 'trend', 'trendValue', 'trendLabel', 'class'])
    ->merge(['class' => $classes]);
@endphp

<div {{ $cardAttributes }}>
  @if($loading)
    <div class="hdt-stat-card__skeleton">
      <div class="hdt-skeleton hdt-skeleton--text hdt-skeleton--sm"></div>
      <div class="hdt-skeleton hdt-skeleton--text hdt-skeleton--lg"></div>
      <div class="hdt-skeleton hdt-skeleton--text hdt-skeleton--xs"></div>
    </div>
  @else
    <div class="hdt-stat-card__content">
      <!-- Icon -->
      @isset($icon)
        <div class="hdt-stat-card__icon">
          {{ $icon }}
        </div>
      @endisset

      <!-- Main content area -->
      <div class="hdt-stat-card__main">
        <!-- Label -->
        @isset($label)
          <div class="hdt-stat-card__label">{{ $label }}</div>
        @endisset

        <!-- Value -->
        @isset($value)
          <div class="hdt-stat-card__value">{{ $value }}</div>
        @endisset

        <!-- Description -->
        @isset($description)
          <div class="hdt-stat-card__description">{{ $description }}</div>
        @endisset

        <!-- Trend indicator -->
        @if($trend && $trendValue)
          <div class="hdt-stat-card__trend {{ $trendClasses[$trend] ?? '' }}">
            @if($trend === 'up')
              <svg class="hdt-stat-card__trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"></path>
              </svg>
            @elseif($trend === 'down')
              <svg class="hdt-stat-card__trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"></path>
              </svg>
            @else
              <svg class="hdt-stat-card__trend-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8"></path>
              </svg>
            @endif
            <span class="hdt-stat-card__trend-value">{{ $trendValue }}</span>
            <span class="hdt-stat-card__trend-label">{{ $trendLabel }}</span>
          </div>
        @endif
      </div>

      <!-- Additional content -->
      @if($slot->isNotEmpty())
        <div class="hdt-stat-card__extra">
          {{ $slot }}
        </div>
      @endif
    </div>
  @endif
</div>

@once
@push('styles')
<style>
/* ============================================================================
   HD Tickets Stat Card Component Styles
============================================================================ */

/* Base stat card styles */
.hdt-stat-card {
  display: flex;
  flex-direction: column;
  background-color: white;
  border-radius: var(--hd-radius-lg);
  border: 1px solid var(--hd-gray-200);
  box-shadow: var(--hd-shadow-sm);
  padding: var(--hd-space-6);
  transition: all var(--hd-transition-base);
  position: relative;
  overflow: hidden;
}

.dark .hdt-stat-card {
  background-color: var(--hd-gray-800);
  border-color: var(--hd-gray-700);
}

/* Card content layout */
.hdt-stat-card__content {
  display: flex;
  align-items: flex-start;
  gap: var(--hd-space-4);
  height: 100%;
}

.hdt-stat-card__main {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: var(--hd-space-2);
}

/* Icon */
.hdt-stat-card__icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 48px;
  height: 48px;
  border-radius: var(--hd-radius-lg);
  background: var(--role-accent, var(--hd-primary-600));
  color: white;
  flex-shrink: 0;
}

.hdt-stat-card__icon svg {
  width: 24px;
  height: 24px;
}

/* Label */
.hdt-stat-card__label {
  font-size: var(--hd-text-sm);
  font-weight: var(--hd-font-medium);
  color: var(--hd-gray-600);
  text-transform: uppercase;
  letter-spacing: 0.025em;
  line-height: var(--hd-leading-tight);
}

.dark .hdt-stat-card__label {
  color: var(--hd-gray-400);
}

/* Value */
.hdt-stat-card__value {
  font-size: var(--hd-text-3xl);
  font-weight: var(--hd-font-bold);
  color: var(--hd-gray-900);
  line-height: var(--hd-leading-tight);
}

.dark .hdt-stat-card__value {
  color: var(--hd-gray-100);
}

/* Description */
.hdt-stat-card__description {
  font-size: var(--hd-text-sm);
  color: var(--hd-gray-600);
  line-height: var(--hd-leading-normal);
}

.dark .hdt-stat-card__description {
  color: var(--hd-gray-400);
}

/* Trend indicator */
.hdt-stat-card__trend {
  display: flex;
  align-items: center;
  gap: var(--hd-space-1);
  font-size: var(--hd-text-sm);
  font-weight: var(--hd-font-medium);
  margin-top: var(--hd-space-1);
}

.hdt-stat-card__trend-icon {
  width: 16px;
  height: 16px;
  flex-shrink: 0;
}

.hdt-stat-card__trend-label {
  font-weight: var(--hd-font-normal);
  color: var(--hd-gray-500);
  margin-left: var(--hd-space-1);
}

.dark .hdt-stat-card__trend-label {
  color: var(--hd-gray-400);
}

/* Trend variants */
.hdt-stat-card__trend--up {
  color: var(--hd-success-600);
}

.dark .hdt-stat-card__trend--up {
  color: var(--hd-success-400);
}

.hdt-stat-card__trend--down {
  color: var(--hd-error-600);
}

.dark .hdt-stat-card__trend--down {
  color: var(--hd-error-400);
}

.hdt-stat-card__trend--neutral {
  color: var(--hd-gray-500);
}

.dark .hdt-stat-card__trend--neutral {
  color: var(--hd-gray-400);
}

/* Card variants */
.hdt-stat-card--primary {
  background: linear-gradient(135deg, var(--role-primary, var(--hd-primary-600)) 0%, var(--role-secondary, var(--hd-primary-700)) 100%);
  border-color: var(--role-primary, var(--hd-primary-500));
  color: white;
}

.hdt-stat-card--primary .hdt-stat-card__label,
.hdt-stat-card--primary .hdt-stat-card__value,
.hdt-stat-card--primary .hdt-stat-card__description {
  color: white;
}

.hdt-stat-card--primary .hdt-stat-card__trend-label {
  color: rgba(255, 255, 255, 0.8);
}

.hdt-stat-card--primary .hdt-stat-card__icon {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
}

.hdt-stat-card--success {
  border-color: var(--hd-success-200);
  background-color: var(--hd-success-50);
}

.dark .hdt-stat-card--success {
  background-color: var(--hd-success-900);
  border-color: var(--hd-success-800);
}

.hdt-stat-card--success .hdt-stat-card__icon {
  background-color: var(--hd-success-600);
}

.hdt-stat-card--warning {
  border-color: var(--hd-warning-200);
  background-color: var(--hd-warning-50);
}

.dark .hdt-stat-card--warning {
  background-color: var(--hd-warning-900);
  border-color: var(--hd-warning-800);
}

.hdt-stat-card--warning .hdt-stat-card__icon {
  background-color: var(--hd-warning-600);
}

.hdt-stat-card--danger {
  border-color: var(--hd-error-200);
  background-color: var(--hd-error-50);
}

.dark .hdt-stat-card--danger {
  background-color: var(--hd-error-900);
  border-color: var(--hd-error-800);
}

.hdt-stat-card--danger .hdt-stat-card__icon {
  background-color: var(--hd-error-600);
}

/* Size variants */
.hdt-stat-card--sm {
  padding: var(--hd-space-4);
}

.hdt-stat-card--sm .hdt-stat-card__icon {
  width: 40px;
  height: 40px;
}

.hdt-stat-card--sm .hdt-stat-card__icon svg {
  width: 20px;
  height: 20px;
}

.hdt-stat-card--sm .hdt-stat-card__value {
  font-size: var(--hd-text-2xl);
}

.hdt-stat-card--lg {
  padding: var(--hd-space-8);
}

.hdt-stat-card--lg .hdt-stat-card__icon {
  width: 56px;
  height: 56px;
}

.hdt-stat-card--lg .hdt-stat-card__icon svg {
  width: 28px;
  height: 28px;
}

.hdt-stat-card--lg .hdt-stat-card__value {
  font-size: var(--hd-text-4xl);
}

/* Extra content area */
.hdt-stat-card__extra {
  margin-top: var(--hd-space-4);
  padding-top: var(--hd-space-4);
  border-top: 1px solid var(--hd-gray-100);
}

.dark .hdt-stat-card__extra {
  border-top-color: var(--hd-gray-700);
}

.hdt-stat-card--primary .hdt-stat-card__extra {
  border-top-color: rgba(255, 255, 255, 0.2);
}

/* Loading state */
.hdt-stat-card--loading {
  pointer-events: none;
}

.hdt-stat-card__skeleton {
  display: flex;
  flex-direction: column;
  gap: var(--hd-space-3);
}

/* Skeleton animation */
.hdt-skeleton {
  background: linear-gradient(
    90deg,
    var(--hd-gray-200) 25%,
    var(--hd-gray-300) 50%,
    var(--hd-gray-200) 75%
  );
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: var(--hd-radius-base);
}

.dark .hdt-skeleton {
  background: linear-gradient(
    90deg,
    var(--hd-gray-700) 25%,
    var(--hd-gray-600) 50%,
    var(--hd-gray-700) 75%
  );
  background-size: 200% 100%;
}

.hdt-skeleton--xs {
  height: 14px;
  width: 60%;
}

.hdt-skeleton--sm {
  height: 16px;
  width: 80%;
}

.hdt-skeleton--lg {
  height: 32px;
  width: 40%;
}

.hdt-skeleton--text {
  height: 20px;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .hdt-stat-card {
    padding: var(--hd-space-4);
  }
  
  .hdt-stat-card--lg {
    padding: var(--hd-space-6);
  }

  .hdt-stat-card__content {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: var(--hd-space-3);
  }
  
  .hdt-stat-card__value {
    font-size: var(--hd-text-2xl);
  }
  
  .hdt-stat-card--lg .hdt-stat-card__value {
    font-size: var(--hd-text-3xl);
  }
}

/* Hover effects for interactive cards */
.hdt-stat-card--interactive {
  cursor: pointer;
  transition: all var(--hd-transition-base);
}

.hdt-stat-card--interactive:hover {
  transform: translateY(-2px);
  box-shadow: var(--hd-shadow-lg);
}

.hdt-stat-card--interactive:focus {
  outline: 2px solid var(--role-primary, var(--hd-primary-500));
  outline-offset: 2px;
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .hdt-skeleton {
    animation: none;
  }
  
  .hdt-stat-card--interactive:hover {
    transform: none;
  }
}

/* High contrast mode */
@media (prefers-contrast: high) {
  .hdt-stat-card {
    border-width: 2px;
  }
  
  .hdt-stat-card__extra {
    border-top-width: 2px;
  }
}
</style>
@endpush
@endonce