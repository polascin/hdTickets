/**
 * HD Tickets - Shared Framework Utilities
 * 
 * Common utilities and services used across React, Vue, Angular, and Alpine.js
 */

export * from './types';
export * from './services/api';

// Re-export commonly used utilities
export { default as apiService } from './services/api';

// Design tokens utility
export const getDesignTokens = () => {
  const rootStyles = getComputedStyle(document.documentElement);
  const tokens: Record<string, string> = {};

  // Extract CSS custom properties (design tokens)
  const properties = [
    'hd-primary', 'hd-secondary', 'hd-success', 'hd-warning', 'hd-error', 'hd-info',
    'hd-primary-50', 'hd-primary-100', 'hd-primary-500', 'hd-primary-700', 'hd-primary-900',
    'hd-space-1', 'hd-space-2', 'hd-space-4', 'hd-space-6', 'hd-space-8', 'hd-space-12', 'hd-space-16',
    'hd-text-xs', 'hd-text-sm', 'hd-text-base', 'hd-text-lg', 'hd-text-xl', 'hd-text-2xl',
    'hd-radius', 'hd-radius-md', 'hd-radius-lg', 'hd-radius-xl',
    'hd-shadow', 'hd-shadow-md', 'hd-shadow-lg', 'hd-shadow-xl',
  ];

  properties.forEach(prop => {
    const value = rootStyles.getPropertyValue(`--${prop}`).trim();
    if (value) {
      tokens[prop.replace('hd-', '')] = value;
    }
  });

  return tokens;
};

// Format utilities
export const formatUtils = {
  price: (price: number, currency: string = 'USD'): string => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: currency,
    }).format(price);
  },

  date: (date: string | Date): string => {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(new Date(date));
  },

  relativeTime: (date: string | Date): string => {
    const now = new Date();
    const target = new Date(date);
    const diffInSeconds = Math.floor((now.getTime() - target.getTime()) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return `${Math.floor(diffInSeconds / 86400)} days ago`;
  },

  percentage: (value: number): string => {
    return `${Math.round(value)}%`;
  },

  truncate: (text: string, length: number): string => {
    if (text.length <= length) return text;
    return text.slice(0, length) + '...';
  }
};

// Validation utilities
export const validationUtils = {
  isEmail: (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  },

  isRequired: (value: any): boolean => {
    if (typeof value === 'string') return value.trim().length > 0;
    if (Array.isArray(value)) return value.length > 0;
    return value != null && value !== undefined;
  },

  isMinLength: (value: string, minLength: number): boolean => {
    return value && value.length >= minLength;
  },

  isMaxLength: (value: string, maxLength: number): boolean => {
    return value && value.length <= maxLength;
  },

  isNumber: (value: any): boolean => {
    return !isNaN(Number(value));
  },

  isPositiveNumber: (value: any): boolean => {
    return !isNaN(Number(value)) && Number(value) > 0;
  },

  isUrl: (url: string): boolean => {
    try {
      new URL(url);
      return true;
    } catch {
      return false;
    }
  }
};

// Event bus for inter-framework communication
export class EventBus {
  private events: Record<string, Function[]> = {};

  on(event: string, callback: Function): () => void {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    
    this.events[event].push(callback);
    
    // Return unsubscribe function
    return () => {
      const index = this.events[event]?.indexOf(callback);
      if (index !== undefined && index > -1) {
        this.events[event].splice(index, 1);
      }
    };
  }

  emit(event: string, data?: any): void {
    if (this.events[event]) {
      this.events[event].forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error(`Error in event callback for ${event}:`, error);
        }
      });
    }
  }

  off(event: string, callback?: Function): void {
    if (!callback) {
      delete this.events[event];
    } else if (this.events[event]) {
      const index = this.events[event].indexOf(callback);
      if (index > -1) {
        this.events[event].splice(index, 1);
      }
    }
  }

  once(event: string, callback: Function): void {
    const unsubscribe = this.on(event, (data: any) => {
      callback(data);
      unsubscribe();
    });
  }
}

// Global event bus instance
export const globalEventBus = new EventBus();

// Storage utilities with fallback
export const storageUtils = {
  set: (key: string, value: any): boolean => {
    try {
      localStorage.setItem(key, JSON.stringify(value));
      return true;
    } catch (error) {
      console.warn('Failed to save to localStorage:', error);
      return false;
    }
  },

  get: <T = any>(key: string, defaultValue?: T): T | null => {
    try {
      const item = localStorage.getItem(key);
      if (item === null) return defaultValue || null;
      return JSON.parse(item);
    } catch (error) {
      console.warn('Failed to read from localStorage:', error);
      return defaultValue || null;
    }
  },

  remove: (key: string): boolean => {
    try {
      localStorage.removeItem(key);
      return true;
    } catch (error) {
      console.warn('Failed to remove from localStorage:', error);
      return false;
    }
  },

  clear: (): boolean => {
    try {
      localStorage.clear();
      return true;
    } catch (error) {
      console.warn('Failed to clear localStorage:', error);
      return false;
    }
  }
};

// Framework detection utilities
export const frameworkUtils = {
  isReactAvailable: (): boolean => {
    return typeof window !== 'undefined' && window.React && window.ReactDOM;
  },

  isVueAvailable: (): boolean => {
    return typeof window !== 'undefined' && window.Vue;
  },

  isAngularAvailable: (): boolean => {
    return typeof window !== 'undefined' && window.ng;
  },

  isAlpineAvailable: (): boolean => {
    return typeof window !== 'undefined' && window.Alpine;
  },

  detectCurrentFramework: (): string | null => {
    if (document.querySelector('[data-react-component]')) return 'react';
    if (document.querySelector('[v-]') || document.querySelector('[x-data]')) return 'vue-alpine';
    if (document.querySelector('[ng-]') || document.querySelector('app-root')) return 'angular';
    return null;
  }
};

// Accessibility utilities
export const a11yUtils = {
  announce: (message: string, priority: 'polite' | 'assertive' = 'polite'): void => {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', priority);
    announcement.setAttribute('aria-atomic', 'true');
    announcement.style.position = 'absolute';
    announcement.style.left = '-10000px';
    announcement.textContent = message;
    
    document.body.appendChild(announcement);
    
    setTimeout(() => {
      document.body.removeChild(announcement);
    }, 1000);
  },

  focusElement: (selector: string | HTMLElement): boolean => {
    try {
      const element = typeof selector === 'string' 
        ? document.querySelector(selector) as HTMLElement
        : selector;
        
      if (element && typeof element.focus === 'function') {
        element.focus();
        return true;
      }
    } catch (error) {
      console.warn('Failed to focus element:', error);
    }
    return false;
  },

  trapFocus: (container: HTMLElement): () => void => {
    const focusableElements = container.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstElement = focusableElements[0] as HTMLElement;
    const lastElement = focusableElements[focusableElements.length - 1] as HTMLElement;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Tab') {
        if (e.shiftKey) {
          if (document.activeElement === firstElement) {
            lastElement.focus();
            e.preventDefault();
          }
        } else {
          if (document.activeElement === lastElement) {
            firstElement.focus();
            e.preventDefault();
          }
        }
      }
    };

    container.addEventListener('keydown', handleKeyDown);

    return () => {
      container.removeEventListener('keydown', handleKeyDown);
    };
  }
};

// Performance utilities
export const perfUtils = {
  debounce: <T extends (...args: any[]) => any>(
    func: T,
    wait: number
  ): ((...args: Parameters<T>) => void) => {
    let timeout: NodeJS.Timeout;
    return (...args: Parameters<T>) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => func(...args), wait);
    };
  },

  throttle: <T extends (...args: any[]) => any>(
    func: T,
    limit: number
  ): ((...args: Parameters<T>) => void) => {
    let inThrottle: boolean;
    return (...args: Parameters<T>) => {
      if (!inThrottle) {
        func(...args);
        inThrottle = true;
        setTimeout(() => (inThrottle = false), limit);
      }
    };
  },

  measurePerformance: (name: string, fn: () => any): any => {
    const start = performance.now();
    const result = fn();
    const end = performance.now();
    console.log(`${name} took ${end - start} milliseconds`);
    return result;
  }
};

// Initialize shared utilities
export const initSharedUtils = (): void => {
  // Set up global error handling
  window.addEventListener('error', (event) => {
    console.error('Global error:', event.error);
  });

  window.addEventListener('unhandledrejection', (event) => {
    console.error('Unhandled promise rejection:', event.reason);
  });

  // Set up global event listeners for framework communication
  document.addEventListener('hdtickets:state-change', (event: any) => {
    globalEventBus.emit('state-change', event.detail);
  });

  document.addEventListener('hdtickets:ticket-selected', (event: any) => {
    globalEventBus.emit('ticket-selected', event.detail);
  });

  document.addEventListener('hdtickets:purchase-completed', (event: any) => {
    globalEventBus.emit('purchase-completed', event.detail);
  });

  console.log('🔧 HD Tickets shared utilities initialized');
};