@props([
    'elevation' => 1, // 0..5
    'padding' => 'md', // none, sm, md, lg
    'interactive' => false, // hover ring & cursor
    'border' => false, // force border even if elevated
    'divider' => false, // internal section dividers
    'as' => 'div', // element tag
    'maxWidth' => null, // optional width constraint class (e.g. 'max-w-md')
    'header' => null, // simple string header
    'subheading' => null, // optional subheading text
    'footer' => null, // simple string footer
    'loading' => false,
    // Legacy compatibility props (will map silently)
    'variant' => null,
    'size' => null,
    'href' => null,
    'clickable' => false,
])

@php
  // Backward compatibility mapping
  if ($variant) {
      switch ($variant) {
          case 'bordered':
              $border = true;
              $elevation = 0;
              break;
          case 'elevated':
              $elevation = max($elevation, 2);
              break;
          case 'flat':
              $elevation = 0;
              break;
          default: // default => keep
      }
  }

  $padMap = ['none' => 'p-0', 'sm' => 'p-3', 'md' => 'p-5', 'lg' => 'p-7'];
  $paddingClass = $padMap[$padding] ?? $padMap['md'];

  $elevation = (int) $elevation;
  $elevation = max(0, min(5, $elevation));
  $base = 'hdt-card hd-card bg-white dark:bg-gray-800 rounded-lg relative flex flex-col';
  $surface = $elevation > 0 ? 'shadow-sm' : 'border border-gray-200 dark:border-gray-700';
  $elevationAttr = 'data-elevation="' . $elevation . '"';
  $interactiveClass =
      $interactive || $clickable || $href
          ? 'transition ring-offset-1 hover:ring-2 hover:ring-blue-500 focus-within:ring-2 focus-within:ring-blue-500 cursor-pointer'
          : '';
  $forcedBorder = $border && $elevation > 0 ? 'border border-gray-200 dark:border-gray-700' : '';
  $widthClass = $maxWidth ?: '';
  $dividerClass = $divider ? 'hdt-card--divided' : '';
  $loadingClass = $loading ? 'hdt-card--loading' : '';

  $classes = collect([
      $base,
      $surface,
      $forcedBorder,
      $interactiveClass,
      $paddingClass,
      $widthClass,
      $dividerClass,
      $loadingClass,
      $attributes->get('class'),
  ])
      ->filter()
      ->join(' ');
  $tag = in_array($as, ['div', 'section', 'article', 'li']) ? $as : 'div';
@endphp

@if ($href)
  <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }} {!! $elevationAttr !!}>
    @includeWhen($loading, 'components.ui.partials.card-loading')
    @unless ($loading)
      @if ($header)
        <div class="hdt-card__header mb-4 flex items-start justify-between">
          <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{!! $header !!}</h3>
            @if ($subheading)
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{!! $subheading !!}</p>
            @endif
          </div>
          @isset($actions)
            <div class="hdt-card__actions ml-4 flex items-center gap-2">{{ $actions }}</div>
          @endisset
        </div>
      @endif
      <div class="hdt-card__body flex flex-col gap-4">{{ $slot }}</div>
      @if ($footer)
        <div
          class="hdt-card__footer mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
          {!! $footer !!}</div>
      @endif
    @endunless
  </a>
@else
  <{{ $tag }} {{ $attributes->merge(['class' => $classes]) }} {!! $elevationAttr !!}>
    @if ($loading)
      @includeWhen($loading, 'components.ui.partials.card-loading')
    @else
      @if ($header)
        <div class="hdt-card__header mb-4 flex items-start justify-between">
          <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{!! $header !!}</h3>
            @if ($subheading)
              <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{!! $subheading !!}</p>
            @endif
          </div>
          @isset($actions)
            <div class="hdt-card__actions ml-4 flex items-center gap-2">{{ $actions }}</div>
          @endisset
        </div>
      @endif
      <div class="hdt-card__body flex flex-col gap-4">{{ $slot }}</div>
      @if ($footer)
        <div
          class="hdt-card__footer mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
          {!! $footer !!}</div>
      @endif
    @endif
    </{{ $tag }}>
@endif

@once
  @push('styles')
    <style>
      .hdt-card[data-elevation="0"] {
        box-shadow: var(--hdt-elevation-0);
      }

      .hdt-card[data-elevation="1"] {
        box-shadow: var(--hdt-elevation-1);
      }

      .hdt-card[data-elevation="2"] {
        box-shadow: var(--hdt-elevation-2);
      }

      .hdt-card[data-elevation="3"] {
        box-shadow: var(--hdt-elevation-3);
      }

      .hdt-card[data-elevation="4"] {
        box-shadow: var(--hdt-elevation-4);
      }

      .hdt-card[data-elevation="5"] {
        box-shadow: var(--hdt-elevation-5);
      }

      .dark .hdt-card {
        background: var(--color-secondary-800);
      }

      .hdt-card--divided>.hdt-card__body>*+* {
        border-top: 1px solid var(--color-secondary-200);
        padding-top: 1rem;
      }

      .dark .hdt-card--divided>.hdt-card__body>*+* {
        border-color: var(--color-secondary-600);
      }

      .hdt-card--loading {
        position: relative;
      }

      .hdt-card--loading::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, var(--hdt-layer-hover), transparent);
        animation: hdt-card-loading 1s linear infinite;
      }

      @keyframes hdt-card-loading {
        0% {
          background-position: 0% 50%;
        }

        100% {
          background-position: 200% 50%;
        }
      }
    </style>
  @endpush
@endonce
