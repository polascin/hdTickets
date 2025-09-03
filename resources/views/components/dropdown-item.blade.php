{{--
    Enhanced Dropdown Menu Item Component
    Provides consistent styling and behavior for dropdown menu items
--}}
@props([
    'value' => '',
    'href' => null,
    'icon' => null,
    'description' => null,
    'active' => false,
    'disabled' => false,
    'divider' => false,
    'dangerous' => false, // For destructive actions
])

@php
  $baseClasses = 'flex items-center w-full px-4 py-2 text-sm text-left transition-colors duration-150 ease-in-out';

  $stateClasses = match (true) {
      $dangerous => 'text-red-600 hover:text-red-700 hover:bg-red-50 focus:bg-red-50 focus:text-red-700',
      $disabled => 'text-gray-400 cursor-not-allowed opacity-50',
      $active => 'text-blue-600 bg-blue-50 font-medium',
      default => 'text-gray-700 hover:text-gray-900 hover:bg-gray-50 focus:bg-gray-100 focus:text-gray-900',
  };

  $classes = implode(
      ' ',
      array_filter([
          $baseClasses,
          $stateClasses,
          'focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-inset', // Accessibility
      ]),
  );
@endphp

@if ($divider)
  <div class="border-t border-gray-200 my-1" role="separator" aria-hidden="true"></div>
@else
  @if ($href && !$disabled)
    <a href="{{ $href }}" class="{{ $classes }}" role="menuitem" tabindex="-1"
      @if ($value) @click="select('{{ $value }}', $el.textContent.trim())" @endif
      {{ $attributes }}>
      @if ($icon)
        <div class="flex-shrink-0 w-5 h-5 mr-3" aria-hidden="true">
          {!! $icon !!}
        </div>
      @endif

      <div class="flex-1 min-w-0">
        <div class="font-medium">{{ $slot }}</div>
        @if ($description)
          <div class="text-xs text-gray-500 mt-1">{{ $description }}</div>
        @endif
      </div>

      @if ($active)
        <svg class="w-4 h-4 ml-2 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"
          aria-hidden="true">
          <path fill-rule="evenodd"
            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
            clip-rule="evenodd" />
        </svg>
      @endif
    </a>
  @else
    <button type="button" class="{{ $classes }}" role="menuitem" tabindex="-1"
      @if (!$disabled) @if ($value) 
                    @click="select('{{ $value }}', $el.textContent.trim())" @endif
    @else disabled @endif
      {{ $attributes }}
      >
      @if ($icon)
        <div class="flex-shrink-0 w-5 h-5 mr-3" aria-hidden="true">
          {!! $icon !!}
        </div>
      @endif

      <div class="flex-1 min-w-0">
        <div class="font-medium">{{ $slot }}</div>
        @if ($description)
          <div class="text-xs text-gray-500 mt-1">{{ $description }}</div>
        @endif
      </div>

      @if ($active)
        <svg class="w-4 h-4 ml-2 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"
          aria-hidden="true">
          <path fill-rule="evenodd"
            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
            clip-rule="evenodd" />
        </svg>
      @endif
    </button>
  @endif
@endif
