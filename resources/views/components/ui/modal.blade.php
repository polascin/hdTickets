@props([
    // Legacy API props
    'name' => 'modal',
    'show' => false,
    'maxWidth' => 'md', // sm, md, lg, xl, 2xl, full
    'closeable' => true,
    'backdrop' => true,
])

@php
  // Map legacy maxWidth to new size scale
  $sizeMap = [
      'sm' => 'sm',
      'md' => 'md',
      'lg' => 'lg',
      'xl' => 'xl',
      '2xl' => 'xl', // approximate, future: add real 2xl if needed
      'full' => 'full',
  ];
  $mappedSize = $sizeMap[$maxWidth] ?? 'md';
@endphp

{{-- Legacy modal wrapper delegating to new hdt modal. Retained for backward compatibility. --}}
@once
  @push('scripts')
    <script>
      if (!window.__HDT_LEGACY_UI_MODAL_WARNED__) {
        window.__HDT_LEGACY_UI_MODAL_WARNED__ = true;
        console.warn('[ui/modal] This component is deprecated. Migrate to <x-hdt.modal>.');
      }
    </script>
  @endpush
@endonce

<x-hdt.modal :name="$name" :open="$show" :size="$mappedSize" :close-button="$closeable" :static-backdrop="false" :esc-to-close="true"
  :id="'legacy-' . $name" :title="null" :subtitle="null" {{ $attributes->except(['class']) }}>
  {{ $slot }}
</x-hdt.modal>
