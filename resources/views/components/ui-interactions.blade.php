{{-- Advanced UI Interactions Component --}}
{{-- Provides smooth transitions, micro-interactions, and enhanced user feedback --}}

<div x-data="uiInteractions()" x-init="init()" class="ui-interactions-manager">
  {{-- Page Transition Overlay --}}
  <div
    x-show="isTransitioning"
    x-transition:enter="transition-opacity duration-300 ease-in-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200 ease-in-out"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-gradient-to-br from-blue-600 to-purple-700 z-[9999] flex items-center justify-center">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-4 border-white border-t-transparent mx-auto mb-4"></div>
      <p class="text-white font-semibold" x-text="transitionMessage">Loading...</p>
    </div>
  </div>

  {{-- Loading Progress Bar --}}
  <div
    x-show="showProgressBar"
    x-transition:enter="transition-transform duration-200 ease-out"
    x-transition:enter-start="transform -translate-y-full"
    x-transition:enter-end="transform translate-y-0"
    x-transition:leave="transition-transform duration-200 ease-in"
    x-transition:leave-start="transform translate-y-0"
    x-transition:leave-end="transform -translate-y-full"
    class="fixed top-0 left-0 right-0 z-50 h-1 bg-gradient-to-r from-blue-500 to-purple-600 origin-left"
    :style="{ transform: `scaleX(${progressValue / 100})` }"></div>

  {{-- Tooltip Container --}}
  <div
    x-show="tooltip.show"
    x-transition:enter="transition-all duration-150 ease-out"
    x-transition:enter-start="opacity-0 scale-95 transform -translate-y-1"
    x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
    x-transition:leave="transition-all duration-100 ease-in"
    x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 transform -translate-y-1"
    class="fixed z-50 px-3 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none"
    :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }"
    x-text="tooltip.content"></div>

  {{-- Context Menu --}}
  <div
    x-show="contextMenu.show"
    x-transition:enter="transition-all duration-150 ease-out"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition-all duration-100 ease-in"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    @click.away="hideContextMenu()"
    class="fixed z-50 bg-white rounded-lg shadow-lg border border-gray-200 py-2 min-w-48"
    :style="{ top: contextMenu.y + 'px', left: contextMenu.x + 'px' }">
    <template x-for="item in contextMenu.items" :key="item.id">
      <button
        @click="executeContextAction(item); hideContextMenu()"
        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2 transition-colors duration-150"
        :class="{ 'text-red-600 hover:bg-red-50': item.danger, 'border-t border-gray-100 mt-1 pt-2': item.separator }">
        <span x-html="item.icon" class="w-4 h-4"></span>
        <span x-text="item.label"></span>
        <span x-show="item.shortcut" x-text="item.shortcut" class="ml-auto text-xs text-gray-400"></span>
      </button>
    </template>
  </div>

  {{-- Guided Tour Overlay --}}
  <div
    x-show="tour.active"
    x-transition:enter="transition-opacity duration-300 ease-in-out"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-200 ease-in-out"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black bg-opacity-50 z-[9998]">
    {{-- Tour Spotlight --}}
    <div
      class="absolute border-4 border-blue-500 rounded-lg shadow-2xl transition-all duration-500 ease-in-out pointer-events-none"
      :style="tour.spotlight"></div>

    {{-- Tour Popup --}}
    <div
      x-show="tour.currentStep"
      x-transition:enter="transition-all duration-300 ease-out"
      x-transition:enter-start="opacity-0 scale-95 transform translate-y-4"
      x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
      class="absolute bg-white rounded-xl shadow-2xl p-6 max-w-sm"
      :style="tour.popup">
      <div class="flex items-start justify-between mb-4">
        <h3 class="font-bold text-lg text-gray-900" x-text="tour.currentStep?.title"></h3>
        <button @click="endTour()" class="text-gray-400 hover:text-gray-600 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <p class="text-gray-600 mb-6" x-text="tour.currentStep?.content"></p>

      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-500">
          <span x-text="tour.stepIndex + 1"></span> of <span x-text="tour.steps.length"></span>
        </div>

        <div class="flex gap-2">
          <button
            @click="previousStep()"
            x-show="tour.stepIndex > 0"
            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
            Previous
          </button>

          <button
            @click="nextStep()"
            x-show="tour.stepIndex < tour.steps.length - 1"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            Next
          </button>

          <button
            @click="endTour()"
            x-show="tour.stepIndex === tour.steps.length - 1"
            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
            Finish
          </button>
        </div>
      </div>

      {{-- Tour Progress Bar --}}
      <div class="mt-4 w-full bg-gray-200 rounded-full h-1">
        <div
          class="bg-blue-600 h-1 rounded-full transition-all duration-500 ease-out"
          :style="{ width: ((tour.stepIndex + 1) / tour.steps.length) * 100 + '%' }"></div>
      </div>
    </div>
  </div>
</div>

<style>
  /* Micro-interaction styles */
  .btn-interactive {
    position: relative;
    overflow: hidden;
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .btn-interactive:hover {
    transform: scale(1.05);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  }

  .btn-interactive:active {
    transform: scale(0.95);
  }

  .btn-interactive::before {
    content: '';
    position: absolute;
    inset: 0;
    background-color: white;
    opacity: 0;
    transition-property: opacity;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .btn-interactive:hover::before {
    opacity: 0.1;
  }

  /* Card hover effects */
  .card-interactive {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 300ms;
  }

  .card-interactive:hover {
    transform: translateY(-0.25rem);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  }

  /* Form validation animations */
  .form-field {
    position: relative;
  }

  .form-field.error .form-input {
    border-color: rgb(239 68 68);
    animation: shake 0.5s ease-in-out;
  }

  .form-field.success .form-input {
    border-color: rgb(34 197 94);
  }

  .form-field .validation-icon {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  /* Loading states */
  .loading-skeleton {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    background-image: linear-gradient(to right, rgb(229 231 235), rgb(209 213 219), rgb(229 231 235));
    background-size: 200% 100%;
    animation: shimmer 2s infinite linear;
  }

  .loading-dots::after {
    content: '.';
    animation: loading-dots 2s infinite;
  }

  /* Success/Error state transitions */
  .status-success {
    animation: bounce-in 0.6s ease-out;
  }

  .status-error {
    animation: shake 0.5s ease-in-out;
  }

  /* Custom animations */
  @keyframes shimmer {
    0% {
      background-position: -200% 0;
    }

    100% {
      background-position: 200% 0;
    }
  }

  @keyframes loading-dots {

    0%,
    20% {
      content: '.';
    }

    40% {
      content: '..';
    }

    60%,
    100% {
      content: '...';
    }
  }

  @keyframes shake {

    0%,
    100% {
      transform: translateX(0);
    }

    10%,
    30%,
    50%,
    70%,
    90% {
      transform: translateX(-2px);
    }

    20%,
    40%,
    60%,
    80% {
      transform: translateX(2px);
    }
  }

  @keyframes bounce-in {
    0% {
      transform: scale(0.8);
      opacity: 0;
    }

    50% {
      transform: scale(1.1);
      opacity: 0.8;
    }

    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  @keyframes pulse {

    0%,
    100% {
      opacity: 1;
    }

    50% {
      opacity: .5;
    }
  }

  /* Utility classes */
  .animate-shimmer {
    animation: shimmer 2s infinite linear;
  }

  .animate-shake {
    animation: shake 0.5s ease-in-out;
  }

  .animate-bounce-in {
    animation: bounce-in 0.6s ease-out;
  }

  .bg-size-200 {
    background-size: 200% 100%;
  }

  /* Drag and drop styles */
  .drag-item {
    cursor: move;
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .drag-item.dragging {
    opacity: 0.5;
    transform: rotate(3deg) scale(0.95);
  }

  .drop-zone {
    border: 2px dashed rgb(209 213 219);
    transition-property: color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .drop-zone.drag-over {
    border-color: rgb(59 130 246);
    background-color: rgb(239 246 255);
  }

  /* Keyboard shortcuts hint */
  .keyboard-shortcut {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.5rem;
    background-color: rgb(243 244 246);
    border: 1px solid rgb(209 213 219);
    border-radius: 0.25rem;
    font-size: 0.75rem;
    line-height: 1rem;
    font-family: ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace;
    color: rgb(75 85 99);
  }
</style>

<script>
  function uiInteractions() {
    return {
      // State
      isTransitioning: false,
      transitionMessage: 'Loading...',
      showProgressBar: false,
      progressValue: 0,

      // Tooltip state
      tooltip: {
        show: false,
        content: '',
        x: 0,
        y: 0
      },

      // Context menu state
      contextMenu: {
        show: false,
        items: [],
        x: 0,
        y: 0
      },

      // Guided tour state
      tour: {
        active: false,
        steps: [],
        stepIndex: 0,
        currentStep: null,
        spotlight: '',
        popup: ''
      },

      // Keyboard shortcuts
      shortcuts: new Map(),

      init() {
        this.setupGlobalEvents();
        this.setupKeyboardShortcuts();
        this.setupTooltips();
        this.setupFormValidation();
        this.setupPageTransitions();

        console.log('[UI] Advanced interactions initialized');
      },

      // Page Transitions
      setupPageTransitions() {
        // Intercept link clicks for smooth transitions
        document.addEventListener('click', (e) => {
          const link = e.target.closest('a[href]:not([target="_blank"]):not([data-no-transition])');
          if (link && link.href.startsWith(window.location.origin)) {
            e.preventDefault();
            this.navigateWithTransition(link.href, link.dataset.transitionMessage || 'Loading...');
          }
        });

        // Handle browser back/forward
        window.addEventListener('popstate', () => {
          this.navigateWithTransition(window.location.href, 'Loading...');
        });
      },

      async navigateWithTransition(url, message = 'Loading...') {
        this.transitionMessage = message;
        this.isTransitioning = true;

        try {
          // Simulate loading delay for better UX
          await new Promise(resolve => setTimeout(resolve, 300));

          // Navigate to new page
          window.location.href = url;
        } catch (error) {
          console.error('[UI] Navigation failed:', error);
          this.isTransitioning = false;
        }
      },

      // Progress Bar
      showProgress(duration = 2000) {
        this.showProgressBar = true;
        this.progressValue = 0;

        const increment = 100 / (duration / 50);
        const interval = setInterval(() => {
          this.progressValue += increment;

          if (this.progressValue >= 100) {
            clearInterval(interval);
            setTimeout(() => {
              this.showProgressBar = false;
              this.progressValue = 0;
            }, 200);
          }
        }, 50);
      },

      // Tooltips
      setupTooltips() {
        document.addEventListener('mouseover', (e) => {
          const element = e.target.closest('[data-tooltip]');
          if (element) {
            this.showTooltip(element, element.dataset.tooltip);
          }
        });

        document.addEventListener('mouseout', (e) => {
          const element = e.target.closest('[data-tooltip]');
          if (element) {
            this.hideTooltip();
          }
        });
      },

      showTooltip(element, content) {
        const rect = element.getBoundingClientRect();
        this.tooltip = {
          show: true,
          content: content,
          x: rect.left + (rect.width / 2) - 50,
          y: rect.top - 35
        };
      },

      hideTooltip() {
        this.tooltip.show = false;
      },

      // Context Menu
      setupGlobalEvents() {
        document.addEventListener('contextmenu', (e) => {
          const element = e.target.closest('[data-context-menu]');
          if (element) {
            e.preventDefault();
            this.showContextMenu(e, element.dataset.contextMenu);
          }
        });
      },

      showContextMenu(event, menuType) {
        const menuItems = this.getContextMenuItems(menuType);

        this.contextMenu = {
          show: true,
          items: menuItems,
          x: event.clientX,
          y: event.clientY
        };
      },

      hideContextMenu() {
        this.contextMenu.show = false;
      },

      getContextMenuItems(menuType) {
        const menus = {
          ticket: [{
              id: 'view',
              label: 'View Details',
              icon: '👁️',
              action: 'viewTicket'
            },
            {
              id: 'favorite',
              label: 'Add to Favorites',
              icon: '❤️',
              action: 'favoriteTicket'
            },
            {
              id: 'alert',
              label: 'Create Price Alert',
              icon: '🔔',
              action: 'createAlert'
            },
            {
              id: 'share',
              label: 'Share',
              icon: '📤',
              action: 'shareTicket',
              separator: true
            },
            {
              id: 'hide',
              label: 'Hide',
              icon: '🙈',
              action: 'hideTicket',
              danger: true
            }
          ],
          dashboard: [{
              id: 'refresh',
              label: 'Refresh',
              icon: '🔄',
              action: 'refresh',
              shortcut: 'Ctrl+R'
            },
            {
              id: 'export',
              label: 'Export Data',
              icon: '📊',
              action: 'exportData'
            },
            {
              id: 'settings',
              label: 'Settings',
              icon: '⚙️',
              action: 'openSettings'
            }
          ]
        };

        return menus[menuType] || [];
      },

      executeContextAction(item) {
        // Dispatch custom event for context actions
        this.$dispatch('context-action', {
          action: item.action,
          item: item
        });

        console.log('[UI] Context action executed:', item.action);
      },

      // Guided Tour
      startTour(tourSteps) {
        this.tour = {
          active: true,
          steps: tourSteps,
          stepIndex: 0,
          currentStep: tourSteps[0],
          spotlight: '',
          popup: ''
        };

        this.updateTourPosition();
      },

      nextStep() {
        if (this.tour.stepIndex < this.tour.steps.length - 1) {
          this.tour.stepIndex++;
          this.tour.currentStep = this.tour.steps[this.tour.stepIndex];
          this.updateTourPosition();
        }
      },

      previousStep() {
        if (this.tour.stepIndex > 0) {
          this.tour.stepIndex--;
          this.tour.currentStep = this.tour.steps[this.tour.stepIndex];
          this.updateTourPosition();
        }
      },

      endTour() {
        this.tour.active = false;
        localStorage.setItem('hd_tickets_tour_completed', 'true');
      },

      updateTourPosition() {
        const step = this.tour.currentStep;
        if (!step.target) return;

        const element = document.querySelector(step.target);
        if (!element) return;

        const rect = element.getBoundingClientRect();

        // Position spotlight
        this.tour.spotlight = `
                top: ${rect.top - 10}px;
                left: ${rect.left - 10}px;
                width: ${rect.width + 20}px;
                height: ${rect.height + 20}px;
            `;

        // Position popup
        const popupX = rect.right + 20;
        const popupY = rect.top;

        this.tour.popup = `
                top: ${popupY}px;
                left: ${popupX}px;
            `;
      },

      // Keyboard Shortcuts
      setupKeyboardShortcuts() {
        // Default shortcuts
        this.registerShortcut('ctrl+/', () => this.showKeyboardShortcuts());
        this.registerShortcut('escape', () => {
          this.hideContextMenu();
          this.hideTooltip();
          if (this.tour.active) this.endTour();
        });

        document.addEventListener('keydown', (e) => {
          const key = this.getShortcutKey(e);
          const handler = this.shortcuts.get(key);

          if (handler) {
            e.preventDefault();
            handler();
          }
        });
      },

      registerShortcut(key, handler) {
        this.shortcuts.set(key, handler);
      },

      getShortcutKey(event) {
        const parts = [];

        if (event.ctrlKey) parts.push('ctrl');
        if (event.altKey) parts.push('alt');
        if (event.shiftKey) parts.push('shift');
        if (event.metaKey) parts.push('meta');

        parts.push(event.key.toLowerCase());

        return parts.join('+');
      },

      showKeyboardShortcuts() {
        // Dispatch event to show shortcuts modal
        this.$dispatch('show-shortcuts');
      },

      // Form Validation
      setupFormValidation() {
        document.addEventListener('input', (e) => {
          const input = e.target;
          if (input.hasAttribute('data-validate')) {
            this.validateField(input);
          }
        });
      },

      validateField(input) {
        const field = input.closest('.form-field');
        if (!field) return;

        const rules = input.dataset.validate.split('|');
        let isValid = true;
        let errorMessage = '';

        for (const rule of rules) {
          const [ruleName, ruleValue] = rule.split(':');
          const result = this.applyValidationRule(input.value, ruleName, ruleValue);

          if (!result.valid) {
            isValid = false;
            errorMessage = result.message;
            break;
          }
        }

        // Update field state
        field.classList.remove('error', 'success');
        field.classList.add(isValid ? 'success' : 'error');

        // Update error message
        const errorEl = field.querySelector('.error-message');
        if (errorEl) {
          errorEl.textContent = errorMessage;
        }

        // Update validation icon
        const iconEl = field.querySelector('.validation-icon');
        if (iconEl) {
          iconEl.innerHTML = isValid ? '✓' : '✗';
          iconEl.className = `validation-icon ${isValid ? 'text-green-500' : 'text-red-500'}`;
        }
      },

      applyValidationRule(value, rule, ruleValue) {
        const rules = {
          required: () => ({
            valid: value.trim() !== '',
            message: 'This field is required'
          }),
          email: () => ({
            valid: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value),
            message: 'Please enter a valid email address'
          }),
          min: (min) => ({
            valid: value.length >= parseInt(min),
            message: `Must be at least ${min} characters`
          }),
          max: (max) => ({
            valid: value.length <= parseInt(max),
            message: `Must not exceed ${max} characters`
          }),
          numeric: () => ({
            valid: /^\d+$/.test(value),
            message: 'Must be a valid number'
          })
        };

        const validator = rules[rule];
        return validator ? validator(ruleValue) : {
          valid: true,
          message: ''
        };
      },

      // Micro-interactions
      addButtonEffect(button) {
        button.classList.add('btn-interactive');

        button.addEventListener('click', function(e) {
          const ripple = document.createElement('span');
          const rect = this.getBoundingClientRect();
          const size = Math.max(rect.width, rect.height);
          const x = e.clientX - rect.left - size / 2;
          const y = e.clientY - rect.top - size / 2;

          ripple.style.width = ripple.style.height = size + 'px';
          ripple.style.left = x + 'px';
          ripple.style.top = y + 'px';
          ripple.className = 'absolute rounded-full bg-white opacity-30 pointer-events-none animate-ping';

          this.appendChild(ripple);

          setTimeout(() => {
            ripple.remove();
          }, 600);
        });
      },

      // Public API methods
      showNotification(message, type = 'info', duration = 3000) {
        this.$dispatch('showtoast', {
          message,
          type,
          duration
        });
      },

      triggerSuccess(element) {
        element.classList.add('status-success');
        setTimeout(() => {
          element.classList.remove('status-success');
        }, 1000);
      },

      triggerError(element) {
        element.classList.add('status-error');
        setTimeout(() => {
          element.classList.remove('status-error');
        }, 500);
      }
    };
  }

  // Initialize for all buttons with .btn class
  document.addEventListener('DOMContentLoaded', function() {
    const buttons = document.querySelectorAll('.btn:not(.btn-interactive)');
    buttons.forEach(button => {
      if (window.uiInteractions) {
        window.uiInteractions().addButtonEffect(button);
      }
    });
  });
</script>