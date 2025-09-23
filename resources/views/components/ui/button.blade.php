@props([
    'variant' => 'primary', // primary, secondary, success, warning, error, ghost, outline, subtle, danger (danger alias of error)
    'size' => 'md', // xs, sm, md, lg, xl (xl deprecated)
    'type' => 'button',
    'href' => null,
    'target' => null,
    'disabled' => false,
    'loading' => false,
    'loadingText' => 'Loading...',
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'fullWidth' => false,
    'ariaLabel' => null,
    'ariaDescribedby' => null,
    'confirm' => null,
])

@php
  // Map legacy variants to new unified set
  $normalizedVariant = match ($variant) {
      'error', 'danger' => 'danger',
      'ghost' => 'ghost',
      'outline' => 'outline',
      'warning' => 'warning',
      'success' => 'success',
      'secondary' => 'secondary',
      'subtle' => 'subtle',
      default => 'primary',
  };

  // Base classes (new) + legacy bridge classes (kept temporarily)
  $baseClasses = 'hdt-btn hd-button';

  // Size modifiers (legacy + new kept)
  $sizeMap = [
      'xs' => 'hd-button--xs hdt-btn--sm', // map xs -> sm in new scale
      'sm' => 'hd-button--sm hdt-btn--sm',
      'md' => 'hd-button--md',
      'lg' => 'hd-button--lg hdt-btn--lg',
      'xl' => 'hd-button--xl hdt-btn--lg', // xl collapses to lg
  ];
  $sizeClass = $sizeMap[$size] ?? 'hd-button--md';

  // Variant mapping to new modifiers; legacy class kept for styling fallback
  $variantMap = [
      'primary' => 'hd-button--primary',
      'secondary' => 'hd-button--secondary hdt-btn--secondary',
      'success' => 'hd-button--success',
      'warning' => 'hd-button--warning',
      'danger' => 'hd-button--error hdt-btn--danger',
      'ghost' => 'hd-button--ghost hdt-btn--ghost',
      'outline' => 'hd-button--outline hdt-btn--outline',
      'subtle' => 'hdt-btn--subtle',
  ];
  $variantClass = $variantMap[$normalizedVariant] ?? $variantMap['primary'];

  $classes = collect([
      $baseClasses,
      $variantClass,
      $sizeClass,
      $fullWidth ? 'hd-button--full w-full' : '',
      $disabled ? 'is-disabled' : '',
  ])
      ->filter()
      ->join(' ');

  $buttonId = $attributes->get('id') ?? 'button-' . uniqid();
@endphp

@if ($href)
  <a href="{{ $href }}" @if ($target) target="{{ $target }}" @endif
    @if ($target === '_blank') rel="noopener noreferrer" @endif id="{{ $buttonId }}"
    {{ $attributes->merge(['class' => $classes]) }} data-variant="{{ $normalizedVariant }}"
    data-size="{{ $size }}" @if ($loading) data-loading="true" @endif
    @if ($disabled) aria-disabled="true" @endif
    @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if ($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
    @if ($loading) aria-busy="true" @endif x-data="{
        loading: {{ $loading ? 'true' : 'false' }},
        confirmMessage: {{ $confirm ? "'" . addslashes($confirm) . "'" : 'null' }},
        handleClick(event) {
            if (this.loading) {
                event.preventDefault();
                return false;
            }
    
            if (this.confirmMessage && !confirm(this.confirmMessage)) {
                event.preventDefault();
                return false;
            }
    
            return true;
        }
    }" @click="handleClick($event)">

    {{-- Loading Spinner --}}
    <span x-show="loading" class="hd-button__spinner absolute inset-0 flex items-center justify-center" aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
        <path fill="currentColor" class="opacity-75"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
      </svg>
    </span>

    {{-- Icon (Left) --}}
    @if ($icon && $iconPosition === 'left')
      <span class="hd-button__icon hd-button__icon--left" x-show="!loading" aria-hidden="true">
        {!! $icon !!}
      </span>
    @endif

    {{-- Button Content --}}
    <span class="hd-button__text" x-show="!loading">
      {{ $slot }}
    </span>

    <span class="hd-button__text" x-show="loading">
      {{ $loadingText }}
    </span>

    {{-- Icon (Right) --}}
    @if ($icon && $iconPosition === 'right')
      <span class="hd-button__icon hd-button__icon--right" x-show="!loading" aria-hidden="true">
        {!! $icon !!}
      </span>
    @endif
  </a>
@else
  <button type="{{ $type }}" id="{{ $buttonId }}" {{ $attributes->merge(['class' => $classes]) }}
    data-variant="{{ $normalizedVariant }}" data-size="{{ $size }}"
    @if ($loading) data-loading="true" @endif @if ($disabled || $loading) disabled @endif
    @if ($ariaLabel) aria-label="{{ $ariaLabel }}" @endif
    @if ($ariaDescribedby) aria-describedby="{{ $ariaDescribedby }}" @endif
    @if ($loading) aria-busy="true" @endif x-data="{
        loading: {{ $loading ? 'true' : 'false' }},
        confirmMessage: {{ $confirm ? "'" . addslashes($confirm) . "'" : 'null' }},
        handleClick(event) {
            if (this.loading) {
                event.preventDefault();
                return false;
            }
    
            if (this.confirmMessage && !confirm(this.confirmMessage)) {
                event.preventDefault();
                return false;
            }
    
            return true;
        }
    }" @click="handleClick($event)">

    {{-- Loading Spinner --}}
    <span x-show="loading" class="hd-button__spinner absolute inset-0 flex items-center justify-center"
      aria-hidden="true">
      <svg viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
        <path fill="currentColor" class="opacity-75"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
      </svg>
    </span>

    {{-- Icon (Left) --}}
    @if ($icon && $iconPosition === 'left')
      <span class="hd-button__icon hd-button__icon--left" x-show="!loading" aria-hidden="true">
        {!! $icon !!}
      </span>
    @endif

    {{-- Button Content --}}
    <span class="hd-button__text" x-show="!loading">
      {{ $slot }}
    </span>

    <span class="hd-button__text" x-show="loading">
      {{ $loadingText }}
    </span>

    {{-- Icon (Right) --}}
    @if ($icon && $iconPosition === 'right')
      <span class="hd-button__icon hd-button__icon--right" x-show="!loading" aria-hidden="true">
        {!! $icon !!}
      </span>
    @endif
  </button>
@endif

@pushOnce('styles')
  <style>
    /* Enhanced accessible button styles */
    .hd-button {
      font-family: var(--hdt-font-family-sans);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 500;
      border-radius: var(--hdt-border-radius-md);
      border: 1px solid transparent;
      transition: all 150ms ease-in-out;
      cursor: pointer;
      position: relative;
      text-decoration: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
      user-select: none;
      min-height: 44px;
      /* WCAG minimum touch target */
      min-width: 44px;
    }

    /* Focus states */
    .hd-button:focus {
      outline: none;
      box-shadow: 0 0 0 2px var(--hdt-color-focus-ring);
    }

    .hd-button:focus-visible {
      outline: 2px solid var(--hdt-color-focus-ring);
      outline-offset: 2px;
    }

    /* Size variants with WCAG compliance */
    .hd-button--xs {
      padding: 0.375rem 0.75rem;
      font-size: var(--hdt-font-size-xs);
      min-height: 32px;
      min-width: 32px;
    }

    .hd-button--sm {
      padding: 0.5rem 1rem;
      font-size: var(--hdt-font-size-sm);
      min-height: 36px;
      min-width: 36px;
    }

    .hd-button--md {
      padding: 0.625rem 1.25rem;
      font-size: var(--hdt-font-size-base);
    }

    .hd-button--lg {
      padding: 0.75rem 1.5rem;
      font-size: var(--hdt-font-size-lg);
      min-height: 48px;
      min-width: 48px;
    }

    .hd-button--xl {
      padding: 1rem 2rem;
      font-size: var(--hdt-font-size-xl);
      min-height: 52px;
      min-width: 52px;
    }

    /* Variant colors with accessibility in mind */
    .hd-button--primary {
      background-color: var(--hdt-color-primary-600);
      color: white;
      border-color: var(--hdt-color-primary-600);
    }

    <style>

    /* V4 Button bridge notice (executed once) */
    document.addEventListener('DOMContentLoaded', ()=> {
        if ( !window.__hdtButtonWarned && document.querySelector('.hd-button')) {
          window.__hdtButtonWarned=true;

          try {
            console.warn('[HD Tickets] Legacy .hd-button classes are deprecated. Replace with .hdt-btn variants.');
          }

          catch(e) {}
        }
      });
  </style>
  margin-right: 0.5rem;
  animation: spin 1s linear infinite;
  flex-shrink: 0;
  }

  .hd-button__spinner svg {
  width: 100%;
  height: 100%;
  }

  @keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
  }

  /* Reduce motion for accessibility */
  .hdt-reduced-motion .hd-button {
  transition: none;
  }

  .hdt-reduced-motion .hd-button__spinner {
  animation: none;
  }

  /* High contrast mode support */
  @media (prefers-contrast: high) {
  .hd-button {
  border-width: 2px;
  }

  .hd-button:focus {
  border-width: 3px;
  }

  .hd-button:not(:disabled):hover {
  border-width: 3px;
  }
  }

  /* Role-specific theming */
  .hdt-theme-organizer .hd-button--primary {
  background-color: var(--hdt-color-organizer-600);
  border-color: var(--hdt-color-organizer-600);
  }

  .hdt-theme-organizer .hd-button--primary:hover:not(:disabled) {
  background-color: var(--hdt-color-organizer-700);
  border-color: var(--hdt-color-organizer-700);
  }

  .hdt-theme-organizer .hd-button--outline {
  color: var(--hdt-color-organizer-600);
  border-color: var(--hdt-color-organizer-600);
  }

  .hdt-theme-organizer .hd-button:focus {
  box-shadow: 0 0 0 2px var(--hdt-color-organizer-600);
  }

  .hdt-theme-attendee .hd-button--primary {
  background-color: var(--hdt-color-attendee-600);
  border-color: var(--hdt-color-attendee-600);
  }

  .hdt-theme-attendee .hd-button--primary:hover:not(:disabled) {
  background-color: var(--hdt-color-attendee-700);
  border-color: var(--hdt-color-attendee-700);
  }

  .hdt-theme-attendee .hd-button--outline {
  color: var(--hdt-color-attendee-600);
  border-color: var(--hdt-color-attendee-600);
  }

  .hdt-theme-attendee .hd-button:focus {
  box-shadow: 0 0 0 2px var(--hdt-color-attendee-600);
  }

  .hdt-theme-vendor .hd-button--primary {
  background-color: var(--hdt-color-vendor-600);
  border-color: var(--hdt-color-vendor-600);
  }

  .hdt-theme-vendor .hd-button--primary:hover:not(:disabled) {
  background-color: var(--hdt-color-vendor-700);
  border-color: var(--hdt-color-vendor-700);
  }

  .hdt-theme-vendor .hd-button--outline {
  color: var(--hdt-color-vendor-600);
  border-color: var(--hdt-color-vendor-600);
  }

  .hdt-theme-vendor .hd-button:focus {
  box-shadow: 0 0 0 2px var(--hdt-color-vendor-600);
  }

  /* Touch devices */
  @media (pointer: coarse) {
  .hd-button {
  min-height: 48px;
  min-width: 48px;
  }

  .hd-button--xs {
  min-height: 40px;
  min-width: 40px;
  }

  .hd-button--sm {
  min-height: 44px;
  min-width: 44px;
  }
  }

  /* Screen reader text */
  .hd-button .sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
  }

  /* Print styles */
  @media print {
  .hd-button {
  background: white !important;
  color: black !important;
  border: 1px solid black !important;
  box-shadow: none !important;
  }
  }
  </style>
@endPushOnce
