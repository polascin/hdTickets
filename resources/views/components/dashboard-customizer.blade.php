{{-- Dashboard Customization Interface --}}
{{-- Drag-and-drop widgets, layout configuration, and personalization --}}

<div x-data="dashboardCustomizer()" x-init="init()" class="dashboard-customizer">
  {{-- Customization Toggle Button --}}
  <button
    @click="toggleCustomizationMode()"
    :class="{ 'bg-blue-600 text-white': isCustomizing, 'bg-gray-100 text-gray-700': !isCustomizing }"
    class="fixed bottom-6 right-6 z-40 p-4 rounded-full shadow-lg transition-all duration-300 hover:shadow-xl"
    data-tooltip="Customize Dashboard">
    <svg x-show="!isCustomizing" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
    </svg>
    <svg x-show="isCustomizing" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
    </svg>
  </button>

  {{-- Customization Panel --}}
  <div
    x-show="isCustomizing"
    x-transition:enter="transition-transform duration-300 ease-out"
    x-transition:enter-start="transform translate-x-full"
    x-transition:enter-end="transform translate-x-0"
    x-transition:leave="transition-transform duration-300 ease-in"
    x-transition:leave-start="transform translate-x-0"
    x-transition:leave-end="transform translate-x-full"
    class="fixed top-0 right-0 w-96 h-full bg-white shadow-2xl z-50 overflow-y-auto">
    <div class="p-6 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900">Customize Dashboard</h2>
        <button @click="toggleCustomizationMode()" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <p class="text-sm text-gray-600 mt-2">Drag and drop widgets to customize your dashboard layout</p>
    </div>

    {{-- Layout Options --}}
    <div class="p-6 border-b border-gray-200">
      <h3 class="font-semibold text-gray-900 mb-4">Layout Options</h3>

      <div class="grid grid-cols-2 gap-3 mb-4">
        <template x-for="layout in layoutOptions" :key="layout.id">
          <button
            @click="changeLayout(layout.id)"
            :class="{ 'ring-2 ring-blue-500 bg-blue-50': currentLayout === layout.id }"
            class="p-3 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors">
            <div class="text-xs font-medium text-gray-700" x-text="layout.name"></div>
            <div class="mt-2 grid gap-1" :class="layout.gridClass">
              <div
                x-for="i in layout.columns"
                class="bg-gray-300 rounded h-2"></div>
            </div>
          </button>
        </template>
      </div>
    </div>

    {{-- Available Widgets --}}
    <div class="p-6 border-b border-gray-200">
      <h3 class="font-semibold text-gray-900 mb-4">Available Widgets</h3>

      <div class="space-y-3">
        <template x-for="widget in availableWidgets" :key="widget.id">
          <div
            :draggable="isCustomizing"
            @dragstart="dragStart($event, widget)"
            :class="{ 'opacity-50 cursor-not-allowed': widget.inUse }"
            class="flex items-center p-3 bg-gray-50 rounded-lg cursor-move hover:bg-gray-100 transition-colors">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-lg mr-3"
              :style="{ backgroundColor: widget.color + '20', color: widget.color }">
              <span x-html="widget.icon"></span>
            </div>
            <div class="flex-1">
              <div class="font-medium text-gray-900" x-text="widget.title"></div>
              <div class="text-xs text-gray-500" x-text="widget.description"></div>
            </div>
            <div x-show="widget.inUse" class="text-green-500 text-xs">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </template>
      </div>
    </div>

    {{-- Theme Options --}}
    <div class="p-6 border-b border-gray-200">
      <h3 class="font-semibold text-gray-900 mb-4">Theme Options</h3>

      <div class="space-y-4">
        <div>
          <label class="text-sm font-medium text-gray-700">Color Scheme</label>
          <div class="mt-2 flex space-x-2">
            <template x-for="theme in colorThemes" :key="theme.id">
              <button
                @click="changeColorTheme(theme.id)"
                :class="{ 'ring-2 ring-offset-2 ring-blue-500': currentTheme === theme.id }"
                class="w-8 h-8 rounded-full shadow-sm"
                :style="{ backgroundColor: theme.primary }"></button>
            </template>
          </div>
        </div>

        <div>
          <label class="flex items-center">
            <input
              type="checkbox"
              x-model="darkMode"
              @change="toggleDarkMode()"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-gray-700">Dark Mode</span>
          </label>
        </div>

        <div>
          <label class="flex items-center">
            <input
              type="checkbox"
              x-model="compactMode"
              @change="toggleCompactMode()"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
            <span class="ml-2 text-sm text-gray-700">Compact Layout</span>
          </label>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="p-6">
      <div class="space-y-3">
        <button
          @click="saveCustomization()"
          class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors font-medium">
          Save Changes
        </button>

        <button
          @click="resetToDefault()"
          class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium">
          Reset to Default
        </button>
      </div>
    </div>
  </div>

  {{-- Customization Overlay --}}
  <div
    x-show="isCustomizing"
    x-transition:enter="transition-opacity duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black bg-opacity-20 z-30"
    @click="toggleCustomizationMode()"></div>

  {{-- Drop Zone Indicator --}}
  <div
    x-show="isDragging"
    x-transition
    class="fixed inset-0 pointer-events-none z-20">
    <div class="absolute inset-4 border-4 border-dashed border-blue-500 bg-blue-50 bg-opacity-50 rounded-xl flex items-center justify-center">
      <div class="text-blue-600 text-center">
        <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <p class="text-lg font-semibold">Drop widget here</p>
      </div>
    </div>
  </div>
</div>

<style>
  /* Dashboard customization specific styles */
  .dashboard-customizer .widget-placeholder {
    border: 2px dashed rgb(209 213 219);
    background-color: rgb(249 250 251);
    border-radius: 0.5rem;
    padding: 1rem;
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .dashboard-customizer .widget-placeholder.drag-over {
    border-color: rgb(59 130 246);
    background-color: rgb(239 246 255);
  }

  .dashboard-customizer .widget-customizing {
    position: relative;
    border: 2px solid rgb(59 130 246);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  }

  .dashboard-customizer .widget-customizing::after {
    content: '⚙️';
    position: absolute;
    top: -0.25rem;
    right: -0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    background-color: rgb(37 99 235);
    border-radius: 9999px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    line-height: 1rem;
  }

  .dashboard-customizer .widget-dragging {
    opacity: 0.5;
    transform: rotate(2deg) scale(0.95);
    transition-property: transform;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
  }

  .dashboard-grid-1 {
    grid-template-columns: 1fr;
  }

  .dashboard-grid-2 {
    grid-template-columns: 1fr 1fr;
  }

  .dashboard-grid-3 {
    grid-template-columns: 1fr 1fr 1fr;
  }

  .dashboard-grid-4 {
    grid-template-columns: 1fr 1fr 1fr 1fr;
  }

  .dashboard-grid-mixed {
    grid-template-columns: 2fr 1fr;
  }

  /* Dark mode styles */
  .dark .dashboard-customizer .widget-placeholder {
    border-color: rgb(75 85 99);
    background-color: rgb(31 41 55);
  }

  .dark .dashboard-customizer .widget-placeholder.drag-over {
    border-color: rgb(96 165 250);
    background-color: rgba(30 58 138, 0.3);
  }

  /* Compact mode styles */
  .compact .dashboard-widget {
    padding: 0.75rem;
  }

  .compact .dashboard-widget .widget-header {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
</style>

<script>
  function dashboardCustomizer() {
    return {
      // State
      isCustomizing: false,
      isDragging: false,
      currentLayout: 'grid-3',
      currentTheme: 'blue',
      darkMode: false,
      compactMode: false,
      draggedWidget: null,

      // Layout options
      layoutOptions: [{
          id: 'grid-1',
          name: '1 Column',
          columns: 1,
          gridClass: 'dashboard-grid-1'
        },
        {
          id: 'grid-2',
          name: '2 Columns',
          columns: 2,
          gridClass: 'dashboard-grid-2'
        },
        {
          id: 'grid-3',
          name: '3 Columns',
          columns: 3,
          gridClass: 'dashboard-grid-3'
        },
        {
          id: 'grid-4',
          name: '4 Columns',
          columns: 4,
          gridClass: 'dashboard-grid-4'
        },
        {
          id: 'grid-mixed',
          name: 'Mixed',
          columns: 2,
          gridClass: 'dashboard-grid-mixed'
        }
      ],

      // Color themes
      colorThemes: [{
          id: 'blue',
          name: 'Blue',
          primary: '#2563eb'
        },
        {
          id: 'green',
          name: 'Green',
          primary: '#059669'
        },
        {
          id: 'purple',
          name: 'Purple',
          primary: '#7c3aed'
        },
        {
          id: 'red',
          name: 'Red',
          primary: '#dc2626'
        },
        {
          id: 'orange',
          name: 'Orange',
          primary: '#ea580c'
        },
        {
          id: 'teal',
          name: 'Teal',
          primary: '#0d9488'
        }
      ],

      // Available widgets
      availableWidgets: [{
          id: 'system-health',
          title: 'System Health',
          description: 'Server status and performance',
          icon: '⚡',
          color: '#059669',
          category: 'admin',
          size: 'medium',
          inUse: false
        },
        {
          id: 'revenue-chart',
          title: 'Revenue Chart',
          description: 'Sales and revenue analytics',
          icon: '📊',
          color: '#2563eb',
          category: 'admin',
          size: 'large',
          inUse: false
        },
        {
          id: 'recent-activity',
          title: 'Recent Activity',
          description: 'Latest user activities',
          icon: '🕐',
          color: '#7c3aed',
          category: 'all',
          size: 'medium',
          inUse: false
        },
        {
          id: 'ticket-alerts',
          title: 'Price Alerts',
          description: 'Active price monitoring alerts',
          icon: '🔔',
          color: '#dc2626',
          category: 'customer',
          size: 'small',
          inUse: false
        },
        {
          id: 'purchase-queue',
          title: 'Purchase Queue',
          description: 'Pending ticket purchases',
          icon: '🛒',
          color: '#ea580c',
          category: 'agent',
          size: 'medium',
          inUse: false
        },
        {
          id: 'favorite-teams',
          title: 'Favorite Teams',
          description: 'Your followed teams',
          icon: '❤️',
          color: '#ec4899',
          category: 'customer',
          size: 'small',
          inUse: false
        },
        {
          id: 'analytics-overview',
          title: 'Analytics Overview',
          description: 'Key performance metrics',
          icon: '📈',
          color: '#0d9488',
          category: 'admin',
          size: 'large',
          inUse: false
        },
        {
          id: 'support-tickets',
          title: 'Support Tickets',
          description: 'Customer support queue',
          icon: '🎫',
          color: '#7c2d12',
          category: 'agent',
          size: 'medium',
          inUse: false
        }
      ],

      init() {
        this.loadCustomization();
        this.setupDragAndDrop();
        this.filterWidgetsByRole();

        console.log('[Customizer] Dashboard customizer initialized');
      },

      toggleCustomizationMode() {
        this.isCustomizing = !this.isCustomizing;

        // Add customization class to body
        document.body.classList.toggle('dashboard-customizing', this.isCustomizing);

        if (this.isCustomizing) {
          this.highlightCustomizableElements();
        } else {
          this.removeCustomizationHighlights();
        }
      },

      setupDragAndDrop() {
        // Setup drag and drop for widgets
        document.addEventListener('dragover', (e) => {
          if (this.isCustomizing) {
            e.preventDefault();
            this.handleDragOver(e);
          }
        });

        document.addEventListener('drop', (e) => {
          if (this.isCustomizing) {
            e.preventDefault();
            this.handleDrop(e);
          }
        });

        document.addEventListener('dragend', () => {
          this.isDragging = false;
          this.removeDropZoneIndicators();
        });
      },

      dragStart(event, widget) {
        this.isDragging = true;
        this.draggedWidget = widget;

        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/html', event.target.outerHTML);

        // Add visual feedback
        setTimeout(() => {
          event.target.classList.add('widget-dragging');
        }, 0);

        this.showDropZoneIndicators();
      },

      handleDragOver(event) {
        const dropZone = event.target.closest('.widget-drop-zone, .dashboard-grid');

        if (dropZone) {
          dropZone.classList.add('drag-over');

          // Remove drag-over class from other drop zones
          document.querySelectorAll('.drag-over').forEach(zone => {
            if (zone !== dropZone) {
              zone.classList.remove('drag-over');
            }
          });
        }
      },

      handleDrop(event) {
        const dropZone = event.target.closest('.widget-drop-zone, .dashboard-grid');

        if (dropZone && this.draggedWidget) {
          this.addWidgetToDropZone(this.draggedWidget, dropZone);

          // Mark widget as in use
          this.draggedWidget.inUse = true;

          // Trigger success animation
          this.showSuccessMessage('Widget added successfully!');
        }

        this.removeDropZoneIndicators();
        this.isDragging = false;
        this.draggedWidget = null;
      },

      addWidgetToDropZone(widget, dropZone) {
        // Create widget element
        const widgetElement = this.createWidgetElement(widget);

        // Find appropriate position in drop zone
        const insertPosition = this.findInsertPosition(dropZone, widget.size);

        if (insertPosition) {
          insertPosition.appendChild(widgetElement);
        } else {
          dropZone.appendChild(widgetElement);
        }

        // Animate widget entrance
        setTimeout(() => {
          widgetElement.classList.add('animate-bounce-in');
        }, 100);
      },

      createWidgetElement(widget) {
        const widgetEl = document.createElement('div');
        widgetEl.className = `dashboard-widget widget-${widget.size} card-interactive`;
        widgetEl.dataset.widgetId = widget.id;

        widgetEl.innerHTML = `
                <div class="widget-header flex items-center justify-between p-4 border-b border-gray-200">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">${widget.icon}</span>
                        <h3 class="font-semibold text-gray-900">${widget.title}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="widget-settings-btn text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <button class="widget-remove-btn text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="widget-content p-4">
                    ${this.getWidgetContent(widget)}
                </div>
            `;

        // Add event listeners
        this.setupWidgetEventListeners(widgetEl, widget);

        return widgetEl;
      },

      getWidgetContent(widget) {
        const content = {
          'system-health': `
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">CPU Usage</span>
                            <span class="text-sm font-medium text-green-600">68%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 68%"></div>
                        </div>
                    </div>
                `,
          'revenue-chart': `
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900">$24,567</div>
                        <div class="text-sm text-gray-500">This month</div>
                        <div class="mt-2 text-green-600 text-sm">↗️ +12% from last month</div>
                    </div>
                `,
          'ticket-alerts': `
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Lakers vs Warriors</span>
                            <span class="text-red-600 font-medium">$89 ↓</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Yankees vs Red Sox</span>
                            <span class="text-green-600 font-medium">Available</span>
                        </div>
                    </div>
                `,
          'recent-activity': `
                    <div class="space-y-3">
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                            <div class="flex-1">
                                <div class="text-sm text-gray-900">New user registration</div>
                                <div class="text-xs text-gray-500">2 minutes ago</div>
                            </div>
                        </div>
                    </div>
                `
        };

        return content[widget.id] || `<div class="text-center text-gray-500">Widget content for ${widget.title}</div>`;
      },

      setupWidgetEventListeners(widgetEl, widget) {
        const removeBtn = widgetEl.querySelector('.widget-remove-btn');
        const settingsBtn = widgetEl.querySelector('.widget-settings-btn');

        if (removeBtn) {
          removeBtn.addEventListener('click', () => {
            this.removeWidget(widget, widgetEl);
          });
        }

        if (settingsBtn) {
          settingsBtn.addEventListener('click', () => {
            this.showWidgetSettings(widget);
          });
        }
      },

      removeWidget(widget, widgetEl) {
        widgetEl.classList.add('animate-fade-out');

        setTimeout(() => {
          widgetEl.remove();
          widget.inUse = false;
          this.showSuccessMessage('Widget removed successfully!');
        }, 300);
      },

      showWidgetSettings(widget) {
        // Dispatch event to show widget settings modal
        this.$dispatch('show-widget-settings', {
          widget
        });
      },

      findInsertPosition(dropZone, widgetSize) {
        // Simple positioning logic - can be enhanced
        const columns = dropZone.querySelectorAll('.dashboard-column');

        if (columns.length > 0) {
          // Find column with least widgets
          let targetColumn = columns[0];
          let minWidgets = targetColumn.children.length;

          columns.forEach(column => {
            if (column.children.length < minWidgets) {
              minWidgets = column.children.length;
              targetColumn = column;
            }
          });

          return targetColumn;
        }

        return null;
      },

      showDropZoneIndicators() {
        document.querySelectorAll('.widget-drop-zone, .dashboard-grid').forEach(zone => {
          zone.classList.add('drop-zone-active');
        });
      },

      removeDropZoneIndicators() {
        document.querySelectorAll('.drag-over, .drop-zone-active').forEach(zone => {
          zone.classList.remove('drag-over', 'drop-zone-active');
        });
      },

      highlightCustomizableElements() {
        document.querySelectorAll('.dashboard-widget').forEach(widget => {
          widget.classList.add('widget-customizing');
        });
      },

      removeCustomizationHighlights() {
        document.querySelectorAll('.widget-customizing').forEach(widget => {
          widget.classList.remove('widget-customizing');
        });
      },

      changeLayout(layoutId) {
        this.currentLayout = layoutId;

        // Apply new layout to dashboard
        const dashboard = document.querySelector('.dashboard-grid');
        if (dashboard) {
          const layout = this.layoutOptions.find(l => l.id === layoutId);
          dashboard.className = `dashboard-grid ${layout.gridClass}`;
        }

        this.showSuccessMessage(`Layout changed to ${this.layoutOptions.find(l => l.id === layoutId).name}`);
      },

      changeColorTheme(themeId) {
        this.currentTheme = themeId;
        const theme = this.colorThemes.find(t => t.id === themeId);

        // Apply theme colors
        document.documentElement.style.setProperty('--primary-color', theme.primary);

        this.showSuccessMessage(`Theme changed to ${theme.name}`);
      },

      toggleDarkMode() {
        document.documentElement.classList.toggle('dark', this.darkMode);
        localStorage.setItem('hd_tickets_dark_mode', this.darkMode);

        this.showSuccessMessage(this.darkMode ? 'Dark mode enabled' : 'Light mode enabled');
      },

      toggleCompactMode() {
        document.documentElement.classList.toggle('compact', this.compactMode);
        localStorage.setItem('hd_tickets_compact_mode', this.compactMode);

        this.showSuccessMessage(this.compactMode ? 'Compact mode enabled' : 'Normal mode enabled');
      },

      filterWidgetsByRole() {
        // Get user role from page data or API
        const userRole = window.userRole || 'customer';

        this.availableWidgets = this.availableWidgets.filter(widget => {
          return widget.category === 'all' || widget.category === userRole;
        });
      },

      saveCustomization() {
        const customization = {
          layout: this.currentLayout,
          theme: this.currentTheme,
          darkMode: this.darkMode,
          compactMode: this.compactMode,
          widgets: this.getActiveWidgets()
        };

        // Save to localStorage
        localStorage.setItem('hd_tickets_customization', JSON.stringify(customization));

        // Save to server
        this.saveToServer(customization);

        this.showSuccessMessage('Dashboard customization saved!');
        this.toggleCustomizationMode();
      },

      loadCustomization() {
        const saved = localStorage.getItem('hd_tickets_customization');

        if (saved) {
          const customization = JSON.parse(saved);

          this.currentLayout = customization.layout || 'grid-3';
          this.currentTheme = customization.theme || 'blue';
          this.darkMode = customization.darkMode || false;
          this.compactMode = customization.compactMode || false;

          // Apply saved settings
          this.changeLayout(this.currentLayout);
          this.changeColorTheme(this.currentTheme);
          if (this.darkMode) this.toggleDarkMode();
          if (this.compactMode) this.toggleCompactMode();
        }
      },

      resetToDefault() {
        this.currentLayout = 'grid-3';
        this.currentTheme = 'blue';
        this.darkMode = false;
        this.compactMode = false;

        // Reset widgets
        this.availableWidgets.forEach(widget => {
          widget.inUse = false;
        });

        // Clear saved customization
        localStorage.removeItem('hd_tickets_customization');

        // Apply default settings
        this.changeLayout(this.currentLayout);
        this.changeColorTheme(this.currentTheme);
        document.documentElement.classList.remove('dark', 'compact');

        // Remove all widgets
        document.querySelectorAll('.dashboard-widget[data-widget-id]').forEach(widget => {
          widget.remove();
        });

        this.showSuccessMessage('Dashboard reset to default settings!');
      },

      getActiveWidgets() {
        const widgets = [];
        document.querySelectorAll('.dashboard-widget[data-widget-id]').forEach(widget => {
          widgets.push({
            id: widget.dataset.widgetId,
            position: this.getWidgetPosition(widget)
          });
        });
        return widgets;
      },

      getWidgetPosition(widget) {
        const rect = widget.getBoundingClientRect();
        return {
          x: rect.left,
          y: rect.top,
          column: this.getWidgetColumn(widget)
        };
      },

      getWidgetColumn(widget) {
        const parent = widget.closest('.dashboard-column');
        if (parent) {
          return Array.from(parent.parentNode.children).indexOf(parent);
        }
        return 0;
      },

      async saveToServer(customization) {
        try {
          await fetch('/api/user/dashboard-customization', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(customization)
          });
        } catch (error) {
          console.error('[Customizer] Failed to save to server:', error);
        }
      },

      showSuccessMessage(message) {
        this.$dispatch('showtoast', {
          message,
          type: 'success',
          duration: 3000
        });
      }
    };
  }
</script>