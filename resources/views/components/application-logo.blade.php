@props(['class' => '', 'size' => 'default', 'lazy' => false])

@php
  $sizeClasses = [
      'small' => 'h-6 w-auto',
      'default' => 'h-8 w-auto',
      'large' => 'h-12 w-auto',
      'xl' => 'h-16 w-auto',
  ];

  $logoClass = $sizeClasses[$size] ?? $sizeClasses['default'];
  $loadingAttr = $lazy ? 'lazy' : 'eager';
@endphp

<div class="hd-logo-container {{ $class }}" {{ $attributes->except(['class', 'size', 'lazy']) }}>
  <!-- Enhanced SVG Logo with dark mode support -->
  <img src="{{ asset('images/logo-hdtickets-enhanced.svg') }}" alt="HD Tickets - Sports Event Tickets Platform"
    class="hd-logo {{ $logoClass }}" loading="{{ $loadingAttr }}" decoding="async" width="128" height="128" />

  <!-- Fallback for older browsers or when SVG fails -->
  <noscript>
    <img src="{{ asset('assets/images/hdTicketsLogo.webp') }}" alt="HD Tickets - Sports Event Tickets Platform"
      class="hd-logo {{ $logoClass }}" width="128" height="128" />
  </noscript>
</div>
