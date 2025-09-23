@props([
    'messages' => null,
    'type' => 'error',
    'field' => null,
    'icon' => true,
    'realtime' => false,
])

@php
  $classes = 'hdt-form-message';

  if ($realtime) {
      $classes .= ' hdt-form-message--realtime';
  }

  $classes .= ' hdt-form-message--' . $type;

  $attributes = $attributes->merge(['class' => $classes]);

  if ($field) {
      $attributes = $attributes->merge([
          'data-field' => $field,
          'id' => $field . '-message',
      ]);
  }

  // Set ARIA attributes based on message type
  if ($type === 'error') {
      $attributes = $attributes->merge([
          'role' => 'alert',
          'aria-live' => 'assertive',
      ]);
  } else {
      $attributes = $attributes->merge([
          'role' => 'status',
          'aria-live' => 'polite',
      ]);
  }

  $iconMap = [
      'error' => '⚠',
      'success' => '✓',
      'warning' => '!',
      'info' => 'i',
  ];

  $iconSymbol = $iconMap[$type] ?? $iconMap['info'];
@endphp

@if ($messages)
  <div {{ $attributes }}>
    @if ($icon)
      <span class="hdt-form-message__icon">{{ $iconSymbol }}</span>
    @endif

    <div class="hdt-form-message__content">
      @if (is_array($messages) || is_iterable($messages))
        @foreach ($messages as $message)
          <span class="hdt-form-message__text">{{ $message }}</span>
        @endforeach
      @else
        <span class="hdt-form-message__text">{{ $messages }}</span>
      @endif
    </div>
  </div>
@endif
