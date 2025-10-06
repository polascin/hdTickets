/**
 * HD Tickets - Vue 3 Framework Integration
 * 
 * Vue 3 Composition API implementation for smooth purchase flows and reactive UIs
 * Focus: Interactive ticket purchase flow, dynamic pricing displays, smooth transitions
 */

import { createApp, App as VueApp } from 'vue';
import { createPinia } from 'pinia';
import { createRouter, createWebHistory } from 'vue-router';
import { VueBridge } from './bridge/VueBridge';
import { initSharedUtils, globalEventBus } from '@shared';

// Component imports
import TicketPurchaseFlow from './components/purchase/TicketPurchaseFlow.vue';
import PricingDisplay from './components/pricing/PricingDisplay.vue';
import TicketSelector from './components/tickets/TicketSelector.vue';
import PaymentForm from './components/forms/PaymentForm.vue';
import PurchaseConfirmation from './components/purchase/PurchaseConfirmation.vue';
import TransitionWrapper from './components/common/TransitionWrapper.vue';
import LoadingSpinner from './components/common/LoadingSpinner.vue';
import ErrorBoundary from './components/common/ErrorBoundary.vue';

// Initialize Vue bridge
const vueBridge = new VueBridge();

// Component registry for dynamic loading
const COMPONENT_REGISTRY = {
  TicketPurchaseFlow,
  PricingDisplay,
  TicketSelector,
  PaymentForm,
  PurchaseConfirmation,
  TransitionWrapper,
  LoadingSpinner,
  ErrorBoundary,
};

// Vue app instances for component tracking
const componentApps = new Map<HTMLElement, VueApp>();

// Pinia store
const pinia = createPinia();

// Vue Router (optional, for Vue-specific routing)
const router = createRouter({
  history: createWebHistory('/vue'),
  routes: [
    { path: '/purchase/:ticketId', component: TicketPurchaseFlow },
    { path: '/pricing/:eventId', component: PricingDisplay },
  ],
});

// Mount a Vue component
export const mountVueComponent = async (
  element: HTMLElement,
  componentName: string,
  props: any = {}
): Promise<void> => {
  try {
    const Component = COMPONENT_REGISTRY[componentName as keyof typeof COMPONENT_REGISTRY];
    
    if (!Component) {
      throw new Error(`Vue component '${componentName}' not found in registry`);
    }

    // Get design tokens and shared state
    const designTokens = vueBridge.getDesignTokens();
    const sharedState = vueBridge.getSharedState();

    // Create Vue app instance
    const app = createApp(Component, {
      ...props,
      designTokens,
      sharedState,
      onStateUpdate: (data: any) => {
        vueBridge.updateSharedState(data);
        globalEventBus.emit('state-change', data);
      },
      onTicketSelect: (ticket: any) => {
        globalEventBus.emit('ticket-selected', ticket);
        document.dispatchEvent(new CustomEvent('hdtickets:ticket-selected', { detail: ticket }));
      },
      onPurchaseComplete: (purchase: any) => {
        globalEventBus.emit('purchase-completed', purchase);
        document.dispatchEvent(new CustomEvent('hdtickets:purchase-completed', { detail: purchase }));
      }
    });

    // Install plugins
    app.use(pinia);
    
    // Only use router if we're in a routing context
    if (element.hasAttribute('data-vue-router')) {
      app.use(router);
    }

    // Global error handler
    app.config.errorHandler = (error, instance, info) => {
      console.error(`Vue component error in ${componentName}:`, error, info);
      
      if (window.Sentry) {
        window.Sentry.captureException(error, {
          tags: { component: componentName, framework: 'vue' },
          extra: { info, instance }
        });
      }
    };

    // Mount the app
    app.mount(element);
    
    // Store app reference
    componentApps.set(element, app);

    console.log(`✅ Vue component ${componentName} mounted successfully`);
  } catch (error) {
    console.error(`Failed to mount Vue component ${componentName}:`, error);
    renderErrorFallback(element, error, componentName);
  }
};

// Unmount a Vue component
export const unmountVueComponent = (element: HTMLElement): void => {
  const app = componentApps.get(element);
  if (app) {
    app.unmount();
    componentApps.delete(element);
    console.log('🗑️ Vue component unmounted');
  }
};

// Update component props
export const updateVueComponentProps = (element: HTMLElement, props: any): void => {
  // Re-mount with new props (Vue 3 approach)
  const componentName = element.getAttribute('data-vue-component');
  if (componentName) {
    unmountVueComponent(element);
    mountVueComponent(element, componentName, props);
  }
};

// Error fallback renderer
const renderErrorFallback = (element: HTMLElement, error: any, componentName: string): void => {
  element.innerHTML = `
    <div class="vue-error-fallback p-4 border border-red-500 rounded-lg bg-red-50 text-red-700">
      <h3 class="font-bold mb-2">Vue Component Error</h3>
      <p class="mb-2">The ${componentName} component failed to load.</p>
      <p class="text-sm text-red-600 mb-4">${error.message}</p>
      <button 
        onclick="window.location.reload()"
        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
      >
        Refresh Page
      </button>
    </div>
  `;
};

// Auto-mount components on DOM ready
const autoMountComponents = (): void => {
  const vueComponents = document.querySelectorAll('[data-vue-component]');
  
  vueComponents.forEach(async (element) => {
    const componentName = element.getAttribute('data-vue-component');
    const propsData = element.getAttribute('data-props');
    
    if (componentName) {
      let props = {};
      if (propsData) {
        try {
          props = JSON.parse(propsData);
        } catch (e) {
          console.warn('Invalid JSON in data-props:', propsData);
        }
      }
      
      await mountVueComponent(element as HTMLElement, componentName, props);
    }
  });

  if (vueComponents.length > 0) {
    console.log(`🟢 Auto-mounted ${vueComponents.length} Vue components`);
  }
};

// Observer for dynamically added components
const setupMutationObserver = (): void => {
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === Node.ELEMENT_NODE) {
            const element = node as HTMLElement;
            
            // Check if the node itself is a Vue component
            if (element.hasAttribute('data-vue-component')) {
              const componentName = element.getAttribute('data-vue-component')!;
              const propsData = element.getAttribute('data-props');
              let props = {};
              
              if (propsData) {
                try {
                  props = JSON.parse(propsData);
                } catch (e) {
                  console.warn('Invalid JSON in data-props:', propsData);
                }
              }
              
              mountVueComponent(element, componentName, props);
            }
            
            // Check for Vue components in the subtree
            const childComponents = element.querySelectorAll('[data-vue-component]');
            childComponents.forEach((child) => {
              const componentName = child.getAttribute('data-vue-component')!;
              const propsData = child.getAttribute('data-props');
              let props = {};
              
              if (propsData) {
                try {
                  props = JSON.parse(propsData);
                } catch (e) {
                  console.warn('Invalid JSON in data-props:', propsData);
                }
              }
              
              mountVueComponent(child as HTMLElement, componentName, props);
            });
          }
        });
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });
};

// Setup inter-framework communication
const setupFrameworkCommunication = (): void => {
  // Listen for React state changes
  document.addEventListener('react:state-changed', (event: any) => {
    vueBridge.handleExternalStateChange(event.detail);
  });

  // Listen for Alpine.js state changes
  document.addEventListener('alpine:state-changed', (event: any) => {
    vueBridge.handleExternalStateChange(event.detail);
  });

  // Listen for Angular state changes
  document.addEventListener('angular:state-changed', (event: any) => {
    vueBridge.handleExternalStateChange(event.detail);
  });

  // Global event bus integration
  globalEventBus.on('ticket-selected', (ticket) => {
    // Update Pinia stores with ticket selection
    const ticketStore = pinia.state.value.tickets;
    if (ticketStore) {
      ticketStore.selectedTicket = ticket;
    }
  });

  globalEventBus.on('purchase-completed', (purchase) => {
    // Update Pinia stores with purchase completion
    const purchaseStore = pinia.state.value.purchases;
    if (purchaseStore) {
      purchaseStore.lastPurchase = purchase;
    }
  });
};

// Initialize Vue framework
export const initVue = (): void => {
  try {
    // Initialize shared utilities
    initSharedUtils();
    
    // Initialize Vue bridge
    vueBridge.init();
    
    // Auto-mount existing components
    autoMountComponents();
    
    // Setup mutation observer for dynamic components
    setupMutationObserver();
    
    // Setup inter-framework communication
    setupFrameworkCommunication();
    
    // Make Vue utilities available globally
    if (typeof window !== 'undefined') {
      window.HDTickets = window.HDTickets || {};
      window.HDTickets.Vue = {
        mount: mountVueComponent,
        unmount: unmountVueComponent,
        updateProps: updateVueComponentProps,
        pinia,
        router,
        components: COMPONENT_REGISTRY
      };
      
      // Make Vue available globally for framework detection
      window.Vue = true;
    }
    
    // Emit ready event
    document.dispatchEvent(new CustomEvent('vue:ready'));
    
    console.log('🟢 Vue framework initialized successfully');
  } catch (error) {
    console.error('Failed to initialize Vue framework:', error);
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initVue);
} else {
  initVue();
}

// Hot module replacement for development
if (import.meta.hot) {
  import.meta.hot.accept('./stores/index', () => {
    console.log('🔥 Vue stores hot reloaded');
  });
  
  import.meta.hot.accept('./components/**/*', () => {
    console.log('🔥 Vue components hot reloaded');
    // Re-mount all components with updated code
    autoMountComponents();
  });
}

export {
  COMPONENT_REGISTRY,
  VueBridge,
  vueBridge,
  pinia,
  router
};