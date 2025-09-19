@props([
  'name',
  'class' => 'w-5 h-5',
  'title' => null,
])

@php
  $icons = config('ui.icons', []);
  $def = $icons[$name] ?? null;
@endphp

<svg xmlns="http://www.w3.org/2000/svg"
     viewBox="0 0 24 24"
     fill="none"
     stroke="currentColor"
     aria-hidden="{{ $title ? 'false' : 'true' }}"
     role="{{ $title ? 'img' : 'presentation' }}"
     {{ $attributes->merge(['class' => $class]) }}>
  @if($title)
    <title>{{ $title }}</title>
  @endif

  @if(is_string($def))
    <path d="{{ $def }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
  @elseif(is_array($def))
    @foreach($def as $d)
      <path d="{{ $d }}" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
    @endforeach
  @else
    <!-- Fallback: simple square -->
    <path d="M4 4h16v16H4z" stroke-width="1.5" />
  @endif
</svg>
