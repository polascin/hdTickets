@props([
    'disabled' => false,
    'type' => 'text',
    'floating' => false,
    'mask' => null,
    'validate' => null,
    'required' => false,
    'placeholder' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'size' => 'medium',
    'value' => null,
])

@php
  $classes = 'hdt-input';

  // Size variations
  if ($size === 'small') {
      $classes .= ' hdt-input--sm';
  } elseif ($size === 'large') {
      $classes .= ' hdt-input--lg';
  }

  // Icon variations
  if ($icon) {
      $classes .= ' hdt-input--with-icon';
      if ($iconPosition === 'right') {
          $classes .= ' hdt-input--icon-right';
      }
  }

  // Mask variations
  if ($mask) {
      $classes .= ' hdt-input--formatted';
      if ($mask === 'phone') {
          // phone mask adornment removed; handle via JS formatting only
      } elseif ($mask === 'email' || $type === 'email') {
          // email icon styling removed; rely on native type styles
      } elseif ($mask === 'currency') {
          // currency left symbol removed; consider wrapper span if needed
      }
  }

  $attributes = $attributes->merge([
      'class' => $classes,
      'type' => $type,
  ]);

  if ($mask) {
      $attributes = $attributes->merge(['data-mask' => $mask]);
  }

  if ($validate) {
      $attributes = $attributes->merge(['data-validate' => $validate]);
  }

  if ($required) {
      $attributes = $attributes->merge(['required' => true]);
  }

  if ($value !== null) {
      $attributes = $attributes->merge(['value' => $value]);
  }

  if ($placeholder) {
      $attributes = $attributes->merge(['placeholder' => $placeholder]);
  }
@endphp

<div class="hdt-input-wrapper {{ $floating ? 'hdt-field--floating' : '' }}">
  @if ($icon && $iconPosition === 'left')
    <div class="hdt-input__icon hdt-input__icon--left">
      @if (str_contains($icon, '<svg'))
        {!! $icon !!}
      @else
        <i class="{{ $icon }}"></i>
      @endif
    </div>
  @endif

  <input @disabled($disabled) {{ $attributes }}>

  @if ($icon && $iconPosition === 'right')
    <div class="hdt-input__icon hdt-input__icon--right">
      @if (str_contains($icon, '<svg'))
        {!! $icon !!}
      @else
        <i class="{{ $icon }}"></i>
      @endif
    </div>
  @endif

  {{-- Floating label placeholder for floating labels --}}
  @if ($floating && $placeholder)
    <div class="hdt-floating-label">{{ $placeholder }}</div>
  @endif
</div>
