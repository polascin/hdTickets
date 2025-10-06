/**
 * HD Tickets - Angular Framework Integration (Demo)
 * 
 * Angular 17+ implementation for form-heavy admin interfaces and reactive forms
 * Focus: Admin interfaces, reactive forms, validation, data management
 * 
 * Note: This is a placeholder implementation demonstrating the architecture.
 * Full Angular integration would require proper Angular CLI setup with zone.js,
 * proper module bundling, and Angular Elements for micro-frontend integration.
 */

import { initSharedUtils, globalEventBus } from '@shared';
import { AngularBridge } from './bridge/AngularBridge';

// Initialize Angular bridge
const angularBridge = new AngularBridge();

// Simplified component registry (demo purposes)
const COMPONENT_REGISTRY = {
  AdminDashboardComponent: 'admin-dashboard',
  UserManagementComponent: 'user-management',
  ScrapingConfigComponent: 'scraping-config',
  ReactiveFormComponent: 'reactive-form',
  ValidationComponent: 'validation',
};

// Mount an Angular component (simplified demo implementation)
export const mountAngularComponent = async (
  element: HTMLElement,
  componentName: string,
  props: any = {}
): Promise<void> => {
  try {
    // Demo implementation - renders placeholder content
    element.innerHTML = `
      <div class="angular-component bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center mb-2">
          <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
          <h3 class="font-semibold text-red-800">Angular Component</h3>
        </div>
        <p class="text-red-700 text-sm mb-2">Component: <strong>${componentName}</strong></p>
        <p class="text-red-600 text-xs">
          This is a demo placeholder. Full Angular integration requires proper CLI setup.
        </p>
        ${props && Object.keys(props).length > 0 ? `
          <details class="mt-2">
            <summary class="text-xs cursor-pointer">Props</summary>
            <pre class="text-xs bg-red-100 p-2 rounded mt-1 overflow-x-auto">${JSON.stringify(props, null, 2)}</pre>
          </details>
        ` : ''}
      </div>
    `;
    
    console.log(`✅ Angular component ${componentName} mounted (demo mode)`);
  } catch (error) {
    console.error(`Failed to mount Angular component ${componentName}:`, error);
    renderErrorFallback(element, error, componentName);
  }
};

// Unmount an Angular component
export const unmountAngularComponent = (element: HTMLElement): void => {
  element.innerHTML = '';
  console.log('🗑️ Angular component unmounted');
};

// Update component props
export const updateAngularComponentProps = (element: HTMLElement, props: any): void => {
  const componentName = element.getAttribute('data-angular-component');
  if (componentName) {
    mountAngularComponent(element, componentName, props);
  }
};

// Error fallback renderer
const renderErrorFallback = (element: HTMLElement, error: any, componentName: string): void => {
  element.innerHTML = `
    <div class="angular-error-fallback p-4 border border-red-500 rounded-lg bg-red-50 text-red-700">
      <h3 class="font-bold mb-2">Angular Component Error</h3>
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
  const angularComponents = document.querySelectorAll('[data-angular-component]');
  
  angularComponents.forEach(async (element) => {
    const componentName = element.getAttribute('data-angular-component');
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
      
      await mountAngularComponent(element as HTMLElement, componentName, props);
    }
  });

  if (angularComponents.length > 0) {
    console.log(`🔴 Auto-mounted ${angularComponents.length} Angular components (demo mode)`);
  }
};

// Initialize Angular framework
export const initAngular = (): void => {
  try {
    // Initialize shared utilities
    initSharedUtils();
    
    // Initialize Angular bridge
    angularBridge.init();
    
    // Auto-mount existing components
    autoMountComponents();
    
    // Make Angular utilities available globally
    if (typeof window !== 'undefined') {
      window.HDTickets = window.HDTickets || {};
      window.HDTickets.Angular = {
        mount: mountAngularComponent,
        unmount: unmountAngularComponent,
        updateProps: updateAngularComponentProps,
        components: COMPONENT_REGISTRY
      };
      
      // Make Angular available globally for framework detection
      window.ng = true;
    }
    
    // Emit ready event
    document.dispatchEvent(new CustomEvent('angular:ready'));
    
    console.log('🔴 Angular framework initialized successfully (demo mode)');
  } catch (error) {
    console.error('Failed to initialize Angular framework:', error);
  }
};

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAngular);
} else {
  initAngular();
}

export {
  COMPONENT_REGISTRY,
  AngularBridge,
  angularBridge
};
