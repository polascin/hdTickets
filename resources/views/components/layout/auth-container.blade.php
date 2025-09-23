@props([
    'maxWidth' => 'sm', // sm | md | lg
    'center' => true,
    'panel' => true,
])

{{--
  Semantic Auth Container Component
  Replaces legacy utility cluster: min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8
  Usage:
    <x-layout.auth-container>
       <!-- auth form -->
    </x-layout.auth-container>

  Props:
    - maxWidth: maps to sm:max-w-md / lg:max-w-lg variants (implemented with design-system utility classes or custom CSS)
    - center: vertically center content (default true)
    - panel: wraps slot in a standard auth panel style when true

  Migration Notes:
    This component reduces reliance on Tailwind snapshot utilities by encapsulating layout styling.
    After wider adoption, equivalent utilities can be pruned from tw-legacy.css.
--}}

@php
  $outerClasses = 'hdt-auth-container';
  if ($center) {
      $outerClasses .= ' hdt-auth-container--center';
  }
  $panelClasses = 'hdt-auth-panel';
  switch ($maxWidth) {
      case 'md':
          $panelClasses .= ' hdt-auth-panel--md';
          break;
      case 'lg':
          $panelClasses .= ' hdt-auth-panel--lg';
          break;
      default:
          $panelClasses .= ' hdt-auth-panel--sm';
  }
@endphp

<div class="{{ $outerClasses }}">
  <div class="hdt-auth-panel-wrapper">
    <div class="{{ $panel ? $panelClasses : '' }}">
      {{ $slot }}
    </div>
  </div>
</div>
