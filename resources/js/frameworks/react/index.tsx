/**
 * HD Tickets - React Framework Integration
 * 
 * React 18+ implementation for complex state management and real-time monitoring
 * Focus: Real-time ticket monitoring dashboard, complex data visualizations
 */

import React from 'react';
import { createRoot, Root } from 'react-dom/client';
import { Provider } from 'react-redux';
import { BrowserRouter } from 'react-router-dom';
import { store } from './store/store';
import { ReactBridge } from './bridge/ReactBridge';
import { initSharedUtils, globalEventBus } from '@shared';

// Component imports
import TicketMonitoringDashboard from './components/dashboard/TicketMonitoringDashboard';
import TicketList from './components/tickets/TicketList';
import TicketCard from './components/tickets/TicketCard';
import PriceChart from './components/charts/PriceChart';
import RealTimeStats from './components/dashboard/RealTimeStats';
import TicketFilters from './components/filters/TicketFilters';
import ErrorBoundary from './components/common/ErrorBoundary';
import LoadingSpinner from './components/common/LoadingSpinner';
import Modal from './components/common/Modal';
import NotificationSystem from './components/common/NotificationSystem';

// Initialize React bridge
const reactBridge = new ReactBridge();

// Component registry for dynamic loading
const COMPONENT_REGISTRY = {
  TicketMonitoringDashboard,
  TicketList,
  TicketCard,
  PriceChart,
  RealTimeStats,
  TicketFilters,
  ErrorBoundary,
  LoadingSpinner,
  Modal,
  NotificationSystem,
};

// React root instances for component tracking
const componentRoots = new Map<HTMLElement, Root>();

// Mount a React component
export const mountReactComponent = async (
  element: HTMLElement, 
  componentName: string, 
  props: any = {}
): Promise<void> => {
  try {
    const Component = COMPONENT_REGISTRY[componentName as keyof typeof COMPONENT_REGISTRY];
    
    if (!Component) {
      throw new Error(`React component '${componentName}' not found in registry`);
    }

    // Create root if it doesn't exist
    let root = componentRoots.get(element);
    if (!root) {
      root = createRoot(element);
      componentRoots.set(element, root);
    }

    // Get design tokens and shared state
    const designTokens = reactBridge.getDesignTokens();
    const sharedState = reactBridge.getSharedState();

    // Enhanced props with shared utilities
    const enhancedProps = {
      ...props,
      designTokens,
      sharedState,
      onStateUpdate: (data: any) => {
        reactBridge.updateSharedState(data);
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
    };

    // Render component with providers
    root.render(
      <React.StrictMode>
        <ErrorBoundary componentName={componentName}>
          <Provider store={store}>
            <BrowserRouter>
              <Component {...enhancedProps} />
            </BrowserRouter>
          </Provider>
        </ErrorBoundary>
      </React.StrictMode>
    );

    console.log(`✅ React component ${componentName} mounted successfully`);
  } catch (error) {
    console.error(`Failed to mount React component ${componentName}:`, error);
    renderErrorFallback(element, error, componentName);
  }
};

// Unmount a React component
export const unmountReactComponent = (element: HTMLElement): void => {
  const root = componentRoots.get(element);
  if (root) {
    root.unmount();
    componentRoots.delete(element);
    console.log('🗑️ React component unmounted');
  }
};

// Update component props
export const updateReactComponentProps = (element: HTMLElement, props: any): void => {
  // Re-mount with new props (React 18 approach)
  const componentName = element.getAttribute('data-react-component');
  if (componentName) {
    mountReactComponent(element, componentName, props);
  }
};

// Error fallback renderer
const renderErrorFallback = (element: HTMLElement, error: any, componentName: string): void => {
  const root = componentRoots.get(element) || createRoot(element);
  if (!componentRoots.has(element)) {
    componentRoots.set(element, root);
  }

  root.render(
    <div className="react-error-fallback p-4 border border-red-500 rounded-lg bg-red-50 text-red-700">
      <h3 className="font-bold mb-2">Component Error</h3>
      <p className="mb-2">The {componentName} component failed to load.</p>
      <p className="text-sm text-red-600 mb-4">{error.message}</p>
      <button 
        onClick={() => window.location.reload()}
        className="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
      >
        Refresh Page
      </button>
    </div>
  );
};

// Auto-mount components on DOM ready
const autoMountComponents = (): void => {
  const reactComponents = document.querySelectorAll('[data-react-component]');
  
  reactComponents.forEach(async (element) => {
    const componentName = element.getAttribute('data-react-component');
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
      
      await mountReactComponent(element as HTMLElement, componentName, props);
    }
  });

  if (reactComponents.length > 0) {
    console.log(`⚛️ Auto-mounted ${reactComponents.length} React components`);
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
            
            // Check if the node itself is a React component
            if (element.hasAttribute('data-react-component')) {
              const componentName = element.getAttribute('data-react-component')!;
              const propsData = element.getAttribute('data-props');
              let props = {};
              
              if (propsData) {
                try {
                  props = JSON.parse(propsData);
                } catch (e) {
                  console.warn('Invalid JSON in data-props:', propsData);
                }
              }
              
              mountReactComponent(element, componentName, props);
            }
            
            // Check for React components in the subtree
            const childComponents = element.querySelectorAll('[data-react-component]');
            childComponents.forEach((child) => {
              const componentName = child.getAttribute('data-react-component')!;
              const propsData = child.getAttribute('data-props');
              let props = {};
              
              if (propsData) {
                try {
                  props = JSON.parse(propsData);
                } catch (e) {
                  console.warn('Invalid JSON in data-props:', propsData);
                }
              }
              
              mountReactComponent(child as HTMLElement, componentName, props);
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
  // Listen for Alpine.js state changes
  document.addEventListener('alpine:state-changed', (event: any) => {
    reactBridge.handleExternalStateChange(event.detail);
  });

  // Listen for Vue state changes
  document.addEventListener('vue:state-changed', (event: any) => {
    reactBridge.handleExternalStateChange(event.detail);
  });

  // Listen for Angular state changes
  document.addEventListener('angular:state-changed', (event: any) => {
    reactBridge.handleExternalStateChange(event.detail);
  });

  // Global event bus integration
  globalEventBus.on('ticket-selected', (ticket) => {
    // Notify all React components about ticket selection
    store.dispatch({ type: 'tickets/selectTicket', payload: ticket });
  });

  globalEventBus.on('purchase-completed', (purchase) => {
    // Update React state with purchase completion
    store.dispatch({ type: 'purchases/addPurchase', payload: purchase });
  });
};

// Initialize React framework
export const initReact = (): void => {
  try {
    // Initialize shared utilities
    initSharedUtils();
    
    // Initialize React bridge
    reactBridge.init();
    
    // Auto-mount existing components
    autoMountComponents();
    
    // Setup mutation observer for dynamic components
    setupMutationObserver();
    
    // Setup inter-framework communication
    setupFrameworkCommunication();
    
    // Make React utilities available globally
    if (typeof window !== 'undefined') {
      window.HDTickets = window.HDTickets || {};
      window.HDTickets.React = {
        mount: mountReactComponent,
        unmount: unmountReactComponent,
        updateProps: updateReactComponentProps,
        store,
        components: COMPONENT_REGISTRY
      };
    }
    
    console.log('⚛️ React framework initialized successfully');
  } catch (error) {
    console.error('Failed to initialize React framework:', error);
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initReact);
} else {
  initReact();
}

// Hot module replacement for development
if (import.meta.hot) {
  import.meta.hot.accept('./store/store', () => {
    console.log('🔥 React store hot reloaded');
  });
  
  import.meta.hot.accept('./components/**/*', () => {
    console.log('🔥 React components hot reloaded');
    // Re-mount all components with updated code
    autoMountComponents();
  });
}

export {
  COMPONENT_REGISTRY,
  ReactBridge,
  reactBridge,
  store
};