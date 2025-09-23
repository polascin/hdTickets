@props([
    'striped' => false,
    'hover' => false,
    'dense' => false,
    'comfortable' => false,
    'stickyHeader' => false,
    'caption' => null,
    'empty' => null, // string or slot for empty state
    'columns' => null, // optional array of column headers if not using thead slot
    'id' => null,
])

@php
  // Resolve density variant (dense wins if both accidentally provided)
  $densityClass = $dense ? 'hdt-table--dense' : ($comfortable ? 'hdt-table--comfortable' : '');
  $stripedClass = $striped ? 'hdt-table--striped' : '';
  $tableId = $id ?? uniqid('hdt_tbl_');
  $hoverAttr = $hover ? 'true' : 'false';
  $stickyAttr = $stickyHeader ? 'true' : 'false';
@endphp

<div class="hdt-table-container" data-component="hdt.table" {{ $attributes->except(['class', 'id']) }}>
  <table id="{{ $tableId }}" {{ $attributes->only('class') }}
    class="hdt-table {{ $densityClass }} {{ $stripedClass }} {{ $attributes->get('class') }}"
    data-hover="{{ $hoverAttr }}" data-sticky-header="{{ $stickyAttr }}">
    @if ($caption)
      <caption>{{ $caption }}</caption>
    @endif

    @if ($columns && !$slot->hasSubscribers('thead'))
      <thead>
        <tr>
          @foreach ($columns as $col)
            <th scope="col">{!! $col !!}</th>
          @endforeach
        </tr>
      </thead>
    @else
      @hasSection('thead')
        {{-- Legacy section support if used inside views with @section('thead') --}}
        <thead>@yield('thead')</thead>
      @elseif(trim($thead ?? '') !== '')
        {{-- If developer passed a named slot variable $thead --}}
        <thead>{!! $thead !!}</thead>
      @elseif(isset($header))
        <thead>{!! $header !!}</thead>
      @else
        {{-- Fallback: allow <x-slot:thead> usage --}}
        @if (isset($__laravel_slots['thead']))
          <thead>{{ $__laravel_slots['thead'] }}</thead>
        @endif
      @endif
    @endif

    <tbody>
      @if (trim($slot) === '' && $empty)
        <tr>
          <td colspan="{{ is_array($columns) ? count($columns) : 100 }}">
            <div class="hdt-table-empty">
              @if (is_string($empty))
                <span>{{ $empty }}</span>
              @else
                {{ $empty }}
              @endif
            </div>
          </td>
        </tr>
      @else
        {{ $slot }}
      @endif
    </tbody>
  </table>
</div>
