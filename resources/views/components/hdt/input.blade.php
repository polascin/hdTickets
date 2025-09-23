@props([
    'id' => null,
    'name' => null,
    'type' => 'text',
    'label' => null,
    'value' => null,
    'placeholder' => null,
    'help' => null,
    'error' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'prefixIcon' => null, // heroicon name or raw svg slot
    'suffixIcon' => null,
    'size' => 'md', // sm|md|lg (maps to padding & font-size)
    'state' => null, // success|warning|error|info (affects border & ring color)
])

@php
  $inputId = $id ?: ($name ? 'hdt-input-' . preg_replace('/[^a-z0-9_\-]/i', '', $name) : 'hdt-input-' . uniqid());
  $helpId = $help ? $inputId . '-help' : null;
  $errorId = $error ? $inputId . '-error' : null;
  $describedBy = collect([$errorId, $helpId])
      ->filter()
      ->implode(' ');
  $sizeClass = match ($size) {
      'sm' => 'hdt-input--sm',
      'lg' => 'hdt-input--lg',
      default => 'hdt-input--md',
  };
  $stateClass = $state ? 'hdt-input-state--' . $state : '';
  $hasPrefix = $prefixIcon || isset($prefix);
  $hasSuffix = $suffixIcon || isset($suffix);
@endphp

<div class="hdt-field" data-component="hdt-input" {{ $attributes->except(['class']) }}>
  @if ($label)
    <label for="{{ $inputId }}" class="hdt-field-label">{!! $label !!} @if ($required)
        <span class="hdt-field-required" aria-hidden="true">*</span>
      @endif
    </label>
  @endif
  <div
    class="hdt-input-wrapper {{ $sizeClass }} {{ $stateClass }} @if ($hasPrefix) hdt-input-wrapper--prefix @endif @if ($hasSuffix) hdt-input-wrapper--suffix @endif @error($name) hdt-input-state--error @enderror">
    @if ($hasPrefix)
      <span class="hdt-input-affix hdt-input-affix--prefix">
        @if (isset($prefix))
          {{ $prefix }}
        @elseif($prefixIcon)
          <x-dynamic-component :component="'icons.' . $prefixIcon" class="hdt-input-icon" />
        @endif
      </span>
    @endif
    <input id="{{ $inputId }}" name="{{ $name }}" type="{{ $type }}"
      @if (!is_null($value)) value="{{ $value }}" @endif
      @if ($placeholder) placeholder="{{ $placeholder }}" @endif
      @if ($required) required @endif @if ($disabled) disabled @endif
      @if ($readonly) readonly @endif
      class="hdt-input {{ $sizeClass }} {{ $stateClass }} @if ($hasPrefix) hdt-input--with-prefix @endif @if ($hasSuffix) hdt-input--with-suffix @endif"
      @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif />
    @if ($hasSuffix)
      <span class="hdt-input-affix hdt-input-affix--suffix">
        @if (isset($suffix))
          {{ $suffix }}
        @elseif($suffixIcon)
          <x-dynamic-component :component="'icons.' . $suffixIcon" class="hdt-input-icon" />
        @endif
      </span>
    @endif
  </div>
  @if ($error)
    <p class="hdt-field-error" id="{{ $errorId }}">{!! $error !!}</p>
  @elseif($help)
    <p class="hdt-field-help" id="{{ $helpId }}">{!! $help !!}</p>
  @endif
</div>

@once
  @push('styles')
    <style>
      /* === HDT Input (v4) === */
      .hdt-field {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
      }

      .hdt-field-label {
        font-size: var(--font-size-sm);
        font-weight: var(--font-weight-medium);
        color: var(--hdt-color-text, #1f2937);
      }

      .hdt-field-required {
        color: var(--color-error);
        margin-left: 0.125rem;
      }

      .hdt-input-wrapper {
        position: relative;
        display: flex;
        align-items: stretch;
        background: var(--hdt-color-bg-elevated, #fff);
        border: 1px solid var(--hdt-color-border);
        border-radius: var(--hdt-radius);
        transition: border-color var(--transition-fast), box-shadow var(--transition-fast), background-color var(--transition-fast);
      }

      .hdt-input-wrapper:focus-within {
        border-color: var(--color-primary-500);
        box-shadow: 0 0 0 1px var(--color-primary-500);
      }

      .hdt-input {
        flex: 1;
        width: 100%;
        border: 0;
        background: transparent;
        font: inherit;
        color: var(--hdt-color-text, #111827);
        line-height: 1.4;
        padding: 0.625rem 0.75rem;
        min-height: 2.5rem;
      }

      .hdt-input:focus {
        outline: none;
      }

      .hdt-input--sm {
        padding: 0.375rem 0.625rem;
        min-height: 2.25rem;
        font-size: var(--font-size-sm);
      }

      .hdt-input--lg {
        padding: 0.75rem 0.875rem;
        min-height: 2.75rem;
        font-size: var(--font-size-base);
      }

      .hdt-input-affix {
        display: inline-flex;
        align-items: center;
        padding: 0 0.625rem;
        color: var(--color-secondary-500);
      }

      .hdt-input-icon {
        width: 1rem;
        height: 1rem;
      }

      .hdt-input--with-prefix {
        padding-left: 0.25rem;
      }

      .hdt-input--with-suffix {
        padding-right: 0.25rem;
      }

      .hdt-input-wrapper--prefix .hdt-input {
        padding-left: 0.25rem;
      }

      .hdt-input-wrapper--suffix .hdt-input {
        padding-right: 0.25rem;
      }

      .hdt-field-help {
        font-size: var(--font-size-xs);
        color: var(--hdt-color-text-subtle, #64748b);
        margin: 0;
      }

      .hdt-field-error {
        font-size: var(--font-size-xs);
        color: var(--color-error);
        margin: 0;
      }

      /* State variants */
      .hdt-input-state--success {
        --_state-color: var(--color-success);
      }

      .hdt-input-state--warning {
        --_state-color: var(--color-warning);
      }

      .hdt-input-state--error {
        --_state-color: var(--color-error);
      }

      .hdt-input-state--info {
        --_state-color: var(--color-info);
      }

      .hdt-input-state--success:focus-within,
      .hdt-input-state--success.hdt-input-wrapper:focus-within {
        box-shadow: 0 0 0 1px var(--color-success);
        border-color: var(--color-success);
      }

      .hdt-input-state--warning:focus-within {
        box-shadow: 0 0 0 1px var(--color-warning);
        border-color: var(--color-warning);
      }

      .hdt-input-state--error:focus-within {
        box-shadow: 0 0 0 1px var(--color-error);
        border-color: var(--color-error);
      }

      .hdt-input-state--info:focus-within {
        box-shadow: 0 0 0 1px var(--color-info);
        border-color: var(--color-info);
      }

      .hdt-input[disabled],
      .hdt-input[readonly] {
        opacity: 0.6;
        cursor: not-allowed;
      }

      @media (prefers-color-scheme: dark) {
        .hdt-input-wrapper {
          background: #1e293b;
          border-color: var(--color-secondary-600);
        }

        .hdt-field-label {
          color: var(--color-secondary-200);
        }

        .hdt-field-help {
          color: var(--color-secondary-400);
        }

        .hdt-input {
          color: var(--color-secondary-100);
        }
      }

      @media (prefers-reduced-motion: reduce) {
        .hdt-input-wrapper {
          transition: none;
        }
      }
    </style>
  @endpush
@endonce

{{-- Example
<x-hdt.input name="email" label="Email" type="email" placeholder="user@example.com" help="We will never share your email." />
--}}@php
  $variants = [
      'default' => 'hdt-input--default',
      'filled' => 'hdt-input--filled',
      'underlined' => 'hdt-input--underlined',
  ];

  $sizes = [
      'sm' => 'hdt-input--sm',
      'md' => 'hdt-input--md',
      'lg' => 'hdt-input--lg',
  ];

  $variant = $attributes->get('variant', 'default');
  $size = $attributes->get('size', 'md');
  $error = $attributes->get('error', false);
  $disabled = $attributes->get('disabled', false);
  $readonly = $attributes->get('readonly', false);
  $required = $attributes->get('required', false);
  $loading = $attributes->get('loading', false);

  $label = $attributes->get('label');
  $hint = $attributes->get('hint');
  $errorMessage = $attributes->get('errorMessage');
  $id = $attributes->get('id') ?: 'input-' . uniqid();

  $hasPrefix = isset($prefix);
  $hasSuffix = isset($suffix);
  $hasIcon = isset($icon);

  $inputClasses = collect(['hdt-input__field'])
      ->when($hasPrefix, fn($c) => $c->push('hdt-input__field--has-prefix'))
      ->when($hasSuffix, fn($c) => $c->push('hdt-input__field--has-suffix'))
      ->when($hasIcon, fn($c) => $c->push('hdt-input__field--has-icon'))
      ->join(' ');

  $wrapperClasses = collect(['hdt-input'])
      ->push($variants[$variant] ?? $variants['default'])
      ->push($sizes[$size] ?? $sizes['md'])
      ->when($error, fn($c) => $c->push('hdt-input--error'))
      ->when($disabled, fn($c) => $c->push('hdt-input--disabled'))
      ->when($readonly, fn($c) => $c->push('hdt-input--readonly'))
      ->when($loading, fn($c) => $c->push('hdt-input--loading'))
      ->push($attributes->get('class'))
      ->filter()
      ->join(' ');

  $inputAttributes = $attributes
      ->except([
          'variant',
          'size',
          'error',
          'disabled',
          'readonly',
          'required',
          'loading',
          'label',
          'hint',
          'errorMessage',
          'class',
      ])
      ->merge([
          'id' => $id,
          'class' => $inputClasses,
          'aria-invalid' => $error ? 'true' : 'false',
      ])
      ->when($label, fn($attr) => $attr->merge(['aria-labelledby' => $id . '-label']))
      ->when($hint, fn($attr) => $attr->merge(['aria-describedby' => $id . '-hint']))
      ->when(
          $errorMessage && $error,
          fn($attr) => $attr->merge([
              'aria-describedby' =>
                  ($attr->get('aria-describedby') ? $attr->get('aria-describedby') . ' ' : '') . $id . '-error',
          ]),
      )
      ->when($required, fn($attr) => $attr->merge(['required' => true, 'aria-required' => 'true']))
      ->when($disabled, fn($attr) => $attr->merge(['disabled' => true]))
      ->when($readonly, fn($attr) => $attr->merge(['readonly' => true]));
@endphp

<div class="{{ $wrapperClasses }}">
  @if ($label)
    <label for="{{ $id }}" id="{{ $id }}-label" class="hdt-input__label">
      {{ $label }}
      @if ($required)
        <span class="hdt-input__required" aria-hidden="true">*</span>
      @endif
    </label>
  @endif

  @if ($hint && !$error)
    <div id="{{ $id }}-hint" class="hdt-input__hint">{{ $hint }}</div>
  @endif

  <div class="hdt-input__wrapper">
    @isset($prefix)
      <div class="hdt-input__prefix">
        {{ $prefix }}
      </div>
    @endisset

    @isset($icon)
      <div class="hdt-input__icon">
        {{ $icon }}
      </div>
    @endisset

    <input {{ $inputAttributes }}>

    @if ($loading)
      <div class="hdt-input__loading" aria-hidden="true">
        <svg class="hdt-spinner" viewBox="0 0 24 24" fill="none">
          <circle class="hdt-spinner__track" cx="12" cy="12" r="10" stroke="currentColor"
            stroke-width="2" />
          <path class="hdt-spinner__path" fill="currentColor" d="m12,2a10,10 0 0,1 10,10h-2a8,8 0 0,0-8-8z" />
        </svg>
      </div>
    @endif

    @isset($suffix)
      <div class="hdt-input__suffix">
        {{ $suffix }}
      </div>
    @endisset
  </div>

  @if ($error && $errorMessage)
    <div id="{{ $id }}-error" class="hdt-input__error" role="alert" aria-live="polite">
      <svg class="hdt-input__error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 15.5c-.77.833.192 2.5 1.732 2.5z" />
      </svg>
      {{ $errorMessage }}
    </div>
  @endif
</div>

@once
  @push('styles')
    <style>
      /* ============================================================================
       HD Tickets Input Component Styles
    ============================================================================ */

      /* Base input styles */
      .hdt-input {
        display: flex;
        flex-direction: column;
        gap: var(--hd-space-2);
        width: 100%;
      }

      /* Label */
      .hdt-input__label {
        display: flex;
        align-items: center;
        gap: var(--hd-space-1);
        font-size: var(--hd-text-sm);
        font-weight: var(--hd-font-medium);
        color: var(--hd-gray-700);
        line-height: var(--hd-leading-normal);
      }

      .dark .hdt-input__label {
        color: var(--hd-gray-300);
      }

      .hdt-input__required {
        color: var(--hd-error-500);
        font-weight: var(--hd-font-bold);
      }

      /* Hint text */
      .hdt-input__hint {
        font-size: var(--hd-text-xs);
        color: var(--hd-gray-500);
        line-height: var(--hd-leading-normal);
      }

      .dark .hdt-input__hint {
        color: var(--hd-gray-400);
      }

      /* Input wrapper */
      .hdt-input__wrapper {
        position: relative;
        display: flex;
        align-items: center;
      }

      /* Input field */
      .hdt-input__field {
        flex: 1;
        background-color: white;
        border: 1px solid var(--hd-gray-300);
        border-radius: var(--hd-radius-md);
        color: var(--hd-gray-900);
        font-size: var(--hd-text-base);
        font-family: inherit;
        line-height: var(--hd-leading-normal);
        transition: all var(--hd-transition-base);
        width: 100%;
      }

      .dark .hdt-input__field {
        background-color: var(--hd-gray-800);
        border-color: var(--hd-gray-600);
        color: var(--hd-gray-100);
      }

      .hdt-input__field:focus {
        outline: none;
        border-color: var(--role-primary, var(--hd-primary-500));
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--role-primary, var(--hd-primary-500)) 15%, transparent);
      }

      .hdt-input__field::placeholder {
        color: var(--hd-gray-400);
      }

      .dark .hdt-input__field::placeholder {
        color: var(--hd-gray-500);
      }

      /* Size variants */
      .hdt-input--sm .hdt-input__field {
        padding: var(--hd-space-2) var(--hd-space-3);
        font-size: var(--hd-text-sm);
        min-height: 36px;
      }

      .hdt-input--md .hdt-input__field {
        padding: var(--hd-space-3) var(--hd-space-4);
        font-size: var(--hd-text-base);
        min-height: 40px;
      }

      .hdt-input--lg .hdt-input__field {
        padding: var(--hd-space-4) var(--hd-space-5);
        font-size: var(--hd-text-lg);
        min-height: 48px;
      }

      /* Style variants */
      .hdt-input--filled .hdt-input__field {
        background-color: var(--hd-gray-50);
        border: 1px solid transparent;
      }

      .dark .hdt-input--filled .hdt-input__field {
        background-color: var(--hd-gray-900);
        border-color: transparent;
      }

      .hdt-input--filled .hdt-input__field:focus {
        background-color: white;
        border-color: var(--role-primary, var(--hd-primary-500));
      }

      .dark .hdt-input--filled .hdt-input__field:focus {
        background-color: var(--hd-gray-800);
      }

      .hdt-input--underlined .hdt-input__field {
        background-color: transparent;
        border: none;
        border-bottom: 2px solid var(--hd-gray-300);
        border-radius: 0;
        padding-left: 0;
        padding-right: 0;
      }

      .dark .hdt-input--underlined .hdt-input__field {
        border-bottom-color: var(--hd-gray-600);
      }

      .hdt-input--underlined .hdt-input__field:focus {
        border-bottom-color: var(--role-primary, var(--hd-primary-500));
        box-shadow: none;
      }

      /* Prefix and suffix */
      .hdt-input__prefix,
      .hdt-input__suffix {
        display: flex;
        align-items: center;
        font-size: var(--hd-text-sm);
        color: var(--hd-gray-500);
        white-space: nowrap;
        user-select: none;
      }

      .dark .hdt-input__prefix,
      .dark .hdt-input__suffix {
        color: var(--hd-gray-400);
      }

      .hdt-input__prefix {
        position: absolute;
        left: var(--hd-space-3);
        z-index: 1;
      }

      .hdt-input__suffix {
        position: absolute;
        right: var(--hd-space-3);
        z-index: 1;
      }

      .hdt-input--sm .hdt-input__prefix {
        left: var(--hd-space-2);
      }

      .hdt-input--sm .hdt-input__suffix {
        right: var(--hd-space-2);
      }

      .hdt-input--lg .hdt-input__prefix {
        left: var(--hd-space-4);
      }

      .hdt-input--lg .hdt-input__suffix {
        right: var(--hd-space-4);
      }

      /* Icon */
      .hdt-input__icon {
        position: absolute;
        left: var(--hd-space-3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--hd-gray-400);
        z-index: 1;
      }

      .dark .hdt-input__icon {
        color: var(--hd-gray-500);
      }

      .hdt-input--sm .hdt-input__icon {
        left: var(--hd-space-2);
      }

      .hdt-input--lg .hdt-input__icon {
        left: var(--hd-space-4);
      }

      .hdt-input__icon svg {
        width: 20px;
        height: 20px;
      }

      .hdt-input--sm .hdt-input__icon svg {
        width: 16px;
        height: 16px;
      }

      .hdt-input--lg .hdt-input__icon svg {
        width: 24px;
        height: 24px;
      }

      /* Field padding adjustments for prefix/suffix/icon */
      .hdt-input__field--has-prefix {
        padding-left: calc(var(--hd-space-8) + var(--hd-space-2));
      }

      .hdt-input__field--has-suffix {
        padding-right: calc(var(--hd-space-8) + var(--hd-space-2));
      }

      .hdt-input__field--has-icon {
        padding-left: calc(var(--hd-space-8) + var(--hd-space-2));
      }

      .hdt-input--sm .hdt-input__field--has-prefix,
      .hdt-input--sm .hdt-input__field--has-icon {
        padding-left: calc(var(--hd-space-6) + var(--hd-space-2));
      }

      .hdt-input--sm .hdt-input__field--has-suffix {
        padding-right: calc(var(--hd-space-6) + var(--hd-space-2));
      }

      .hdt-input--lg .hdt-input__field--has-prefix,
      .hdt-input--lg .hdt-input__field--has-icon {
        padding-left: calc(var(--hd-space-10) + var(--hd-space-2));
      }

      .hdt-input--lg .hdt-input__field--has-suffix {
        padding-right: calc(var(--hd-space-10) + var(--hd-space-2));
      }

      /* Loading state */
      .hdt-input__loading {
        position: absolute;
        right: var(--hd-space-3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--role-primary, var(--hd-primary-500));
        z-index: 1;
      }

      .hdt-input--sm .hdt-input__loading {
        right: var(--hd-space-2);
      }

      .hdt-input--lg .hdt-input__loading {
        right: var(--hd-space-4);
      }

      .hdt-spinner {
        width: 16px;
        height: 16px;
        animation: spin 1s linear infinite;
      }

      .hdt-input--lg .hdt-spinner {
        width: 20px;
        height: 20px;
      }

      .hdt-spinner__track {
        opacity: 0.2;
      }

      .hdt-spinner__path {
        opacity: 0.8;
      }

      @keyframes spin {
        from {
          transform: rotate(0deg);
        }

        to {
          transform: rotate(360deg);
        }
      }

      .hdt-input--loading .hdt-input__field {
        padding-right: calc(var(--hd-space-8) + var(--hd-space-2));
      }

      .hdt-input--loading.hdt-input--sm .hdt-input__field {
        padding-right: calc(var(--hd-space-6) + var(--hd-space-2));
      }

      .hdt-input--loading.hdt-input--lg .hdt-input__field {
        padding-right: calc(var(--hd-space-10) + var(--hd-space-2));
      }

      /* Error state */
      .hdt-input--error .hdt-input__field {
        border-color: var(--hd-error-500);
        box-shadow: none;
      }

      .hdt-input--error .hdt-input__field:focus {
        border-color: var(--hd-error-500);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--hd-error-500) 15%, transparent);
      }

      .hdt-input__error {
        display: flex;
        align-items: flex-start;
        gap: var(--hd-space-2);
        font-size: var(--hd-text-xs);
        color: var(--hd-error-600);
        line-height: var(--hd-leading-normal);
      }

      .dark .hdt-input__error {
        color: var(--hd-error-400);
      }

      .hdt-input__error-icon {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        margin-top: 1px;
      }

      /* Disabled state */
      .hdt-input--disabled .hdt-input__field {
        background-color: var(--hd-gray-50);
        color: var(--hd-gray-400);
        cursor: not-allowed;
        opacity: 0.6;
      }

      .dark .hdt-input--disabled .hdt-input__field {
        background-color: var(--hd-gray-900);
        color: var(--hd-gray-500);
      }

      .hdt-input--disabled .hdt-input__label {
        color: var(--hd-gray-400);
      }

      .dark .hdt-input--disabled .hdt-input__label {
        color: var(--hd-gray-500);
      }

      /* Readonly state */
      .hdt-input--readonly .hdt-input__field {
        background-color: var(--hd-gray-25);
        cursor: default;
      }

      .dark .hdt-input--readonly .hdt-input__field {
        background-color: var(--hd-gray-875);
      }

      /* High contrast mode */
      @media (prefers-contrast: high) {
        .hdt-input__field {
          border-width: 2px;
        }

        .hdt-input__field:focus {
          box-shadow: 0 0 0 4px color-mix(in srgb, var(--role-primary, var(--hd-primary-500)) 25%, transparent);
        }

        .hdt-input--error .hdt-input__field {
          border-width: 2px;
        }
      }

      /* Reduced motion */
      @media (prefers-reduced-motion: reduce) {
        .hdt-input__field {
          transition: none;
        }

        .hdt-spinner {
          animation: none;
        }
      }

      /* Focus visible for better keyboard navigation */
      .hdt-input__field:focus-visible {
        outline: 2px solid var(--role-primary, var(--hd-primary-500));
        outline-offset: 2px;
      }

      /* Touch device optimizations */
      @media (pointer: coarse) {
        .hdt-input--sm .hdt-input__field {
          min-height: var(--hd-touch-target-min);
          font-size: 16px;
          /* Prevent iOS zoom */
        }

        .hdt-input--md .hdt-input__field {
          min-height: var(--hd-touch-target-min);
          font-size: 16px;
          /* Prevent iOS zoom */
        }
      }
    </style>
  @endpush
@endonce
