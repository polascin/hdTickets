@props([
    'id', // base id like 'monitored-events'
    'icon' => 'fa-chart-bar',
    'iconColor' => 'text-primary',
    'label' => 'Stat',
    'value' => 0,
    'dispatch' => null, // e.g. alerts / searches etc
])

<article class="stats-card bg-light rounded p-3 h-100 focus-outline" tabindex="0" role="button"
  @if ($dispatch) @click="$dispatch('show-detail', { type: '{{ $dispatch }}' })" @keydown.enter="$dispatch('show-detail', { type: '{{ $dispatch }}' })" @endif
  aria-labelledby="{{ $id }}-title">
  <i class="fas {{ $icon }} {{ $iconColor }} mb-2 fs-4" aria-hidden="true"></i>
  <h4 id="{{ $id }}-title" class="fw-bold text-primary mb-1" x-text="statsLoading ? '…' : '{{ $value }}'">
    {{ $value }}</h4>
  <small class="text-muted">{{ $label }}</small>
</article>
