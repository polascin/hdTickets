@props([
    'name' => null, // unique name for dispatch-based control (optional if using direct Alpine refs)
    'open' => false, // initial open state
    'title' => null, // string or html
    'subtitle' => null, // supporting text
    'size' => 'md', // sm|md|lg|xl|2xl|full (full = responsive max)
    'closeButton' => true, // show X button
    'escToClose' => true, // allow ESC key
    'staticBackdrop' => false, // clicking backdrop won't close
    'showBackdrop' => true, // allow disabling backdrop entirely
    'maxHeight' => 'screen', // screen|content (screen = scroll inside body)
    'id' => null, // optional explicit id
])

@php
  $modalId = $id ?: 'hdt-modal-' . uniqid();
  $sizeClass = match ($size) {
      'sm' => 'hdt-modal-panel--sm',
      'lg' => 'hdt-modal-panel--lg',
      'xl' => 'hdt-modal-panel--xl',
      '2xl' => 'hdt-modal-panel--2xl',
      'full' => 'hdt-modal-panel--full',
      default => 'hdt-modal-panel--md',
  };
  $scrollMode = $maxHeight === 'screen' ? 'hdt-modal-body--scroll' : '';
@endphp

<div x-data="hdtModal({
    open: @js($open),
    esc: @js($escToClose),
    staticBackdrop: @js($staticBackdrop),
    id: @js($modalId),
    name: @js($name),
    showBackdrop: @js($showBackdrop),
})" x-init="init()" x-on:keydown.escape.window="handleEscape" x-show="isOpen" x-cloak
  id="{{ $modalId }}" class="hdt-modal-root" role="dialog" :aria-modal="true" :aria-labelledby="titleId"
  :aria-describedby="descId" {{ $attributes->merge(['data-component' => 'hdt-modal']) }}>
  <!-- Backdrop -->
  @if ($showBackdrop)
    <div class="hdt-modal-backdrop" x-show="isOpen" x-transition.opacity @click="backdropClick" aria-hidden="true"></div>
  @endif

  <!-- Panel Wrapper for centering -->
  <div class="hdt-modal-wrapper" x-show="isOpen" x-transition.opacity.scale.origin.top @click="noop">
    <div class="hdt-modal-panel {{ $sizeClass }}" :class="panelDynamicClasses" @click.stop>
      @if ($closeButton)
        <button type="button" class="hdt-modal-close" @click="close" aria-label="Close dialog">
          <svg class="hdt-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      @endif

      @if ($title || $subtitle)
        <header class="hdt-modal-header">
          @if ($title)
            <h2 class="hdt-modal-title" x-ref="title" x-bind:id="titleId">{!! $title !!}</h2>
          @endif
          @if ($subtitle)
            <p class="hdt-modal-subtitle" x-ref="desc" x-bind:id="descId">{!! $subtitle !!}</p>
          @endif
        </header>
      @endif

      <div class="hdt-modal-body {{ $scrollMode }}">
        {{ $slot }}
      </div>

      @isset($footer)
        <footer class="hdt-modal-footer">
          {{ $footer }}
        </footer>
      @endisset
    </div>
  </div>
</div>

@once
  @push('scripts')
    <script>
      (function() {
        if (window.__HDT_MODAL_INIT__) return; // run once
        window.__HDT_MODAL_INIT__ = true;

        window.hdtModal = function(cfg) {
          return {
            isOpen: cfg.open || false,
            escEnabled: cfg.esc !== false,
            staticBackdrop: !!cfg.staticBackdrop,
            id: cfg.id,
            name: cfg.name,
            showBackdrop: cfg.showBackdrop !== false,
            titleId: null,
            descId: null,
            previouslyFocused: null,
            init() {
              // Generate stable ids if title/subtitle exist
              this.titleId = this.$refs.title ? this.id + '-title' : null;
              this.descId = this.$refs.desc ? this.id + '-desc' : null;
              if (this.isOpen) this.afterOpen();
              // Listen for global open/close events by name or id
              window.addEventListener('hdt:modal:open', e => {
                if (e.detail?.id === this.id || (this.name && e.detail?.name === this.name)) this.open();
              });
              window.addEventListener('hdt:modal:close', e => {
                if (e.detail?.id === this.id || (this.name && e.detail?.name === this.name)) this.close();
              });
            },
            open() {
              if (!this.isOpen) {
                this.previouslyFocused = document.activeElement;
                this.isOpen = true;
                this.$nextTick(() => this.afterOpen());
              }
            },
            close() {
              if (this.isOpen) {
                this.isOpen = false;
                this.afterClose();
              }
            },
            afterOpen() {
              document.documentElement.classList.add('overflow-hidden');
              this.focusFirstElement();
            },
            afterClose() {
              document.documentElement.classList.remove('overflow-hidden');
              if (this.previouslyFocused?.focus) this.previouslyFocused.focus();
            },
            handleEscape(e) {
              if (this.escEnabled && this.isOpen) {
                e.preventDefault();
                this.close();
              }
            },
            backdropClick() {
              if (!this.staticBackdrop) this.close();
            },
            focusables() {
              return Array.from(this.$root.querySelectorAll(
                'a[href],button:not([disabled]),input:not([type=hidden]):not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'
              )).filter(el => el.offsetParent !== null);
            },
            focusFirstElement() {
              const els = this.focusables();
              if (els.length) els[0].focus();
            },
            panelDynamicClasses() {
              return '';
            },
            noop() {},
          }
        }

        // Deprecation warnings for legacy modal classes (one-time)
        const legacySelectors = ['.hd-modal', '.hd-modal-overlay'];
        if (!window.__HDT_MODAL_DEPRECATION_EMITTED__) {
          window.__HDT_MODAL_DEPRECATION_EMITTED__ = true;
          queueMicrotask(() => {
            if (document.querySelector(legacySelectors.join(','))) {
              console.warn(
                '[hdt-modal] Legacy modal markup detected (class hd-modal / hd-modal-overlay). Please migrate to <x-hdt.modal>.'
              );
            }
          });
        }
      })
      ();
    </script>
  @endpush
@endonce

{{-- Usage Example (reference only - not rendered)
<x-hdt.modal name="invite-users" title="Invite Users" subtitle="Send email invites" size="lg" :open="false">
  <p>Content...</p>
  <x-slot:footer>
    <x-ui.button variant="subtle" onclick="window.dispatchEvent(new CustomEvent('hdt:modal:close',{detail:{name:'invite-users'}}))">Cancel</x-ui.button>
    <x-ui.button>Send Invites</x-ui.button>
  </x-slot:footer>
</x-hdt.modal>
<script>
  // Open programmatically
  window.dispatchEvent(new CustomEvent('hdt:modal:open',{detail:{name:'invite-users'}}));
</script>
--}}
