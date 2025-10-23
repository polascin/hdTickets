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
  <!-- HD Tickets PNG Logo (requested as primary) -->
  <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets - Sports Event Tickets Platform"
    class="hd-logo {{ $logoClass }}" loading="{{ $loadingAttr }}" decoding="async" width="128" height="128" />

  <!-- Noscript fallback uses PNG as well -->
  <noscript>
    <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets - Sports Event Tickets Platform"
      class="hd-logo {{ $logoClass }}" width="128" height="128" />
  </noscript>
</div>
