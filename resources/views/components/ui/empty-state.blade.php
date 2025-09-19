<?php declare(strict_types=1);

// UI Empty State component
// Props:
// - title (string)
// - description (string)
// - illustration (string) e.g. 'search-empty.svg'
// - class (string) optional extra container classes
// Slots:
// - default: actions or extra content (optional)
?>
@props([
  'title' => 'Nothing here yet',
  'description' => '',
  'illustration' => 'search-empty.svg',
  'class' => '',
  'testid' => null,
])

@php
  $src = Vite::asset("resources/illustrations/{$illustration}");
@endphp

<div {{ $attributes->merge(['class' => trim("text-center py-12 px-4 {$class}")]) }} @if($testid) data-testid="{{ $testid }}" @endif>
  <img src="{{ $src }}" alt="" class="mx-auto mb-6 w-40 h-auto" width="160" height="160" />
  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
  @if($description !== '')
    <p class="text-gray-600 dark:text-gray-400 max-w-md mx-auto">{{ $description }}</p>
  @endif
  @if(trim($slot))
    <div class="mt-6">
      {{ $slot }}
    </div>
  @endif
</div>
