/**
 * HD Tickets - Angular Framework Bridge
 * 
 * Bridge for seamless integration between Angular and other frameworks
 * Handles state synchronization, event communication, and lifecycle management
 */

import { getDesignTokens, globalEventBus, storageUtils } from '@shared';
import { FrameworkBridge } from '@shared/types';

export class AngularBridge implements FrameworkBridge {
  private sharedState: Map<string, any> = new Map();
  private componentInstances: Map<HTMLElement, any> = new Map();
  private eventListeners: Map<string, Function[]> = new Map();
  private initialized = false;

  constructor() {
    this.setupEventListeners();
  }

  /**
   * Initialize the Angular bridge
   */
  init(): void {
    if (this.initialized) return;

    console.log('🌉 Initializing Angular Bridge');
    
    // Initialize shared state from storage
    this.loadPersistedState();
    
    // Setup global event listeners
    this.setupGlobalEventListeners();
    
    // Setup framework communication
    this.setupFrameworkCommunication();
    
    this.initialized = true;
    console.log('✅ Angular Bridge initialized');
  }

  /**
   * Mount an Angular component
   */
  async mountComponent(element: HTMLElement, componentName: string, props?: any): Promise<void> {
    // Implementation is handled in the main Angular index file
    console.log(`AngularBridge.mountComponent called for ${componentName}`);
  }

  /**
   * Unmount an Angular component
   */
  unmountComponent(element: HTMLElement): void {
    const instance = this.componentInstances.get(element);
    if (instance && instance.destroy) {
      instance.destroy();
      this.componentInstances.delete(element);
    }
  }

  /**
   * Update component props
   */
  updateProps(element: HTMLElement, props: any): void {
    const instance = this.componentInstances.get(element);
    if (instance && instance.updateProps) {
      instance.updateProps(props);
    }
  }

  /**
   * Get shared state
   */
  getSharedState(key?: string): any {
    if (key) {
      return this.sharedState.get(key);
    }
    
    // Return all shared state as object
    const state: Record<string, any> = {};
    this.sharedState.forEach((value, key) => {
      state[key] = value;
    });
    return state;
  }

  /**
   * Update shared state
   */
  updateSharedState(keyOrData: string | Record<string, any>, value?: any): void {
    if (typeof keyOrData === 'string' && value !== undefined) {
      // Single key-value update
      this.sharedState.set(keyOrData, value);
      this.persistState(keyOrData, value);
      this.notifyStateChange(keyOrData, value);
    } else if (typeof keyOrData === 'object') {
      // Bulk update
      Object.entries(keyOrData).forEach(([key, val]) => {
        this.sharedState.set(key, val);
        this.persistState(key, val);
        this.notifyStateChange(key, val);
      });
    }
  }

  /**
   * Get design tokens for styling
   */
  getDesignTokens(): Record<string, string> {
    return getDesignTokens();
  }

  /**
   * Handle external state changes from other frameworks
   */
  handleExternalStateChange(data: { key: string; value: any; source: string }): void {
    if (data.source !== 'angular') {
      this.sharedState.set(data.key, data.value);
      this.notifyStateChange(data.key, data.value, false); // Don't broadcast back
    }
  }

  /**
   * Subscribe to state changes
   */
  onStateChange(key: string, callback: Function): () => void {
    if (!this.eventListeners.has(key)) {
      this.eventListeners.set(key, []);
    }
    
    this.eventListeners.get(key)!.push(callback);
    
    // Return unsubscribe function
    return () => {
      const listeners = this.eventListeners.get(key);
      if (listeners) {
        const index = listeners.indexOf(callback);
        if (index > -1) {
          listeners.splice(index, 1);
        }
      }
    };
  }

  /**
   * Setup event listeners for framework communication
   */
  private setupEventListeners(): void {
    // Listen for React events
    document.addEventListener('react:state-changed', (event: any) => {
      this.handleExternalStateChange({
        key: event.detail.key,
        value: event.detail.value,
        source: 'react'
      });
    });

    // Listen for Vue events
    document.addEventListener('vue:state-changed', (event: any) => {
      this.handleExternalStateChange({
        key: event.detail.key,
        value: event.detail.value,
        source: 'vue'
      });
    });

    // Listen for Alpine.js events
    document.addEventListener('alpine:state-changed', (event: any) => {
      this.handleExternalStateChange({
        key: event.detail.key,
        value: event.detail.value,
        source: 'alpine'
      });
    });
  }

  /**
   * Setup global event listeners
   */
  private setupGlobalEventListeners(): void {
    // Handle ticket selection events
    globalEventBus.on('ticket-selected', (ticket) => {
      this.updateSharedState('selectedTicket', ticket);
    });

    // Handle purchase completion events
    globalEventBus.on('purchase-completed', (purchase) => {
      this.updateSharedState('lastPurchase', purchase);
    });

    // Handle filter changes
    globalEventBus.on('filters-changed', (filters) => {
      this.updateSharedState('filters', filters);
    });

    // Handle user preference changes
    globalEventBus.on('preferences-changed', (preferences) => {
      this.updateSharedState('userPreferences', preferences);
    });
  }

  /**
   * Setup framework communication channels
   */
  private setupFrameworkCommunication(): void {
    // Setup communication with Alpine.js
    if (window.Alpine) {
      this.setupAlpineCommunication();
    } else {
      document.addEventListener('alpine:init', () => {
        this.setupAlpineCommunication();
      });
    }

    // Setup communication with React
    this.setupReactCommunication();

    // Setup communication with Vue
    this.setupVueCommunication();
  }

  /**
   * Setup Alpine.js communication
   */
  private setupAlpineCommunication(): void {
    if (!window.Alpine?.store) return;

    // Create reactive Alpine stores for shared state
    this.sharedState.forEach((value, key) => {
      try {
        window.Alpine.store(key, value);
      } catch (error) {
        console.warn(`Failed to create Alpine store for ${key}:`, error);
      }
    });

    console.log('🏔️ Angular-Alpine communication established');
  }

  /**
   * Setup React communication
   */
  private setupReactCommunication(): void {
    document.addEventListener('react:ready', () => {
      console.log('⚛️ Angular-React communication established');
    });
  }

  /**
   * Setup Vue communication
   */
  private setupVueCommunication(): void {
    document.addEventListener('vue:ready', () => {
      console.log('🟢 Angular-Vue communication established');
    });
  }

  /**
   * Notify state change to listeners
   */
  private notifyStateChange(key: string, value: any, broadcast: boolean = true): void {
    // Notify local listeners
    const listeners = this.eventListeners.get(key);
    if (listeners) {
      listeners.forEach(callback => {
        try {
          callback(value);
        } catch (error) {
          console.error(`Error in state change callback for ${key}:`, error);
        }
      });
    }

    // Broadcast to other frameworks
    if (broadcast) {
      document.dispatchEvent(new CustomEvent('angular:state-changed', {
        detail: { key, value, source: 'angular' }
      }));

      // Update Alpine stores
      if (window.Alpine?.store && window.Alpine.store(key)) {
        try {
          const store = window.Alpine.store(key);
          Object.assign(store, typeof value === 'object' ? value : { value });
        } catch (error) {
          console.warn(`Failed to update Alpine store ${key}:`, error);
        }
      }
    }

    // Emit global event
    globalEventBus.emit(`state-changed:${key}`, value);
  }

  /**
   * Persist state to localStorage
   */
  private persistState(key: string, value: any): void {
    try {
      storageUtils.set(`angular-state:${key}`, value);
    } catch (error) {
      console.warn(`Failed to persist state ${key}:`, error);
    }
  }

  /**
   * Load persisted state from localStorage
   */
  private loadPersistedState(): void {
    try {
      // Load admin preferences
      const adminPreferences = storageUtils.get('angular-state:adminPreferences');
      if (adminPreferences) {
        this.sharedState.set('adminPreferences', adminPreferences);
      }

      // Load form data
      const formData = storageUtils.get('angular-state:formData');
      if (formData) {
        this.sharedState.set('formData', formData);
      }

      // Load selected user (for admin operations)
      const selectedUser = storageUtils.get('angular-state:selectedUser');
      if (selectedUser) {
        this.sharedState.set('selectedUser', selectedUser);
      }

      console.log('📂 Persisted Angular state loaded');
    } catch (error) {
      console.warn('Failed to load persisted state:', error);
    }
  }

  /**
   * Get component instance
   */
  getComponentInstance(element: HTMLElement): any {
    return this.componentInstances.get(element);
  }

  /**
   * Register component instance
   */
  registerComponentInstance(element: HTMLElement, instance: any): void {
    this.componentInstances.set(element, instance);
  }

  /**
   * Destroy the bridge and cleanup
   */
  destroy(): void {
    // Cleanup all component instances
    this.componentInstances.forEach((instance) => {
      if (instance.destroy) {
        instance.destroy();
      }
    });

    // Clear all data
    this.componentInstances.clear();
    this.sharedState.clear();
    this.eventListeners.clear();

    this.initialized = false;
    console.log('🗑️ Angular Bridge destroyed');
  }

  /**
   * Get bridge statistics for debugging
   */
  getStats(): Record<string, any> {
    return {
      initialized: this.initialized,
      componentCount: this.componentInstances.size,
      sharedStateKeys: Array.from(this.sharedState.keys()),
      eventListenerKeys: Array.from(this.eventListeners.keys()),
      memory: {
        sharedState: this.sharedState.size,
        components: this.componentInstances.size,
        listeners: this.eventListeners.size
      }
    };
  }
}

// Global instance
const angularBridge = new AngularBridge();

export default angularBridge;