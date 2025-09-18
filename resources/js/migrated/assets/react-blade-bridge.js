/**
 * HD Tickets React-Blade Bridge
 * 
 * Seamless integration system between Laravel Blade templates and React components
 * - Component wrapper strategy for embedding React in Blade
 * - State synchronization between Alpine.js and React
 * - Asset loading optimization for mixed environments
 * - Error boundary management
 */

(function(window, document) {
    'use strict';

    const ReactBladeBridge = {
        // Configuration
        config: {
            enableLogging: true,
            componentSelector: '[data-react-component]',
            stateSelector: '[data-shared-state]',
            errorBoundaryClass: 'react-error-boundary',
            fallbackComponent: 'DefaultErrorBoundary'
        },

        // State management
        components: new Map(),
        sharedState: new Map(),
        alpineStores: new Map(),
        mountedComponents: new Set(),
        componentRegistry: {},

        // Initialize the bridge system
        init: function() {
            if (this.config.enableLogging) {
                console.log('🌉 React-Blade Bridge initialized');
            }

            this.setupComponentRegistry();
            this.setupStateSync();
            this.setupErrorBoundaries();
            this.setupAssetOptimization();
            this.mountExistingComponents();
            this.setupMutationObserver();
            
            // Global error handlers
            this.setupGlobalErrorHandlers();
        },

        // Setup component registry for dynamic loading
        setupComponentRegistry: function() {
            // Define component mapping for dynamic imports
            this.componentRegistry = {
                // Dashboard Components
                'TicketChart': () => import('/js/components/react/TicketChart.js'),
                'StatCard': () => import('/js/components/react/StatCard.js'),
                'DataTable': () => import('/js/components/react/DataTable.js'),
                'FilterPanel': () => import('/js/components/react/FilterPanel.js'),
                
                // Form Components
                'DynamicForm': () => import('/js/components/react/DynamicForm.js'),
                'FormField': () => import('/js/components/react/FormField.js'),
                'DatePicker': () => import('/js/components/react/DatePicker.js'),
                'SearchInput': () => import('/js/components/react/SearchInput.js'),
                
                // Layout Components
                'Modal': () => import('/js/components/react/Modal.js'),
                'Sidebar': () => import('/js/components/react/Sidebar.js'),
                'Navbar': () => import('/js/components/react/Navbar.js'),
                
                // Utility Components
                'LoadingSpinner': () => import('/js/components/react/LoadingSpinner.js'),
                'ErrorBoundary': () => import('/js/components/react/ErrorBoundary.js'),
                'Toast': () => import('/js/components/react/Toast.js')
            };

            if (this.config.enableLogging) {
                console.log(`📋 Registered ${Object.keys(this.componentRegistry).length} React components`);
            }
        },

        // Mount all existing React components on page
        mountExistingComponents: function() {
            const components = document.querySelectorAll(this.config.componentSelector);
            
            components.forEach(element => {
                this.mountComponent(element);
            });

            if (this.config.enableLogging && components.length > 0) {
                console.log(`⚛️ Mounted ${components.length} React components`);
            }
        },

        // Mount a single React component
        mountComponent: async function(element) {
            if (this.mountedComponents.has(element)) {
                return; // Already mounted
            }

            const componentName = element.getAttribute('data-react-component');
            const propsData = element.getAttribute('data-props');
            const stateKey = element.getAttribute('data-state-key');
            const errorBoundary = element.getAttribute('data-error-boundary') !== 'false';

            if (!componentName) {
                console.warn('React component element missing data-react-component attribute');
                return;
            }

            try {
                // Parse props
                let props = {};
                if (propsData) {
                    try {
                        props = JSON.parse(propsData);
                    } catch (e) {
                        console.warn('Invalid JSON in data-props:', propsData);
                    }
                }

                // Add shared state to props if specified
                if (stateKey && this.sharedState.has(stateKey)) {
                    props.sharedState = this.sharedState.get(stateKey);
                    props.updateSharedState = (updates) => this.updateSharedState(stateKey, updates);
                }

                // Add design tokens to props
                props.designTokens = this.getDesignTokens();

                // Load and mount component
                await this.loadAndMountComponent(element, componentName, props, errorBoundary);
                
                this.mountedComponents.add(element);

                if (this.config.enableLogging) {
                    console.log(`✅ Mounted React component: ${componentName}`);
                }

            } catch (error) {
                console.error(`Failed to mount React component ${componentName}:`, error);
                this.renderErrorFallback(element, error, componentName);
            }
        },

        // Load and mount a React component
        loadAndMountComponent: async function(element, componentName, props, useErrorBoundary) {
            // Check if React is available
            if (!window.React || !window.ReactDOM) {
                throw new Error('React is not loaded');
            }

            // Load component dynamically
            const componentModule = await this.loadComponent(componentName);
            const Component = componentModule.default || componentModule;

            // Create component element
            let componentElement;
            
            if (useErrorBoundary) {
                // Wrap in error boundary
                const ErrorBoundary = await this.getErrorBoundaryComponent();
                componentElement = React.createElement(
                    ErrorBoundary,
                    {
                        fallback: (error) => this.createErrorFallback(error, componentName),
                        onError: (error, errorInfo) => this.handleComponentError(error, errorInfo, componentName)
                    },
                    React.createElement(Component, props)
                );
            } else {
                componentElement = React.createElement(Component, props);
            }

            // Mount component
            const root = ReactDOM.createRoot ? 
                ReactDOM.createRoot(element) : 
                element; // Fallback for React <18

            if (ReactDOM.createRoot) {
                root.render(componentElement);
            } else {
                ReactDOM.render(componentElement, element);
            }

            // Store component reference
            this.components.set(element, {
                name: componentName,
                component: Component,
                props: props,
                root: root
            });
        },

        // Load a component dynamically
        loadComponent: async function(componentName) {
            if (this.componentRegistry[componentName]) {
                return await this.componentRegistry[componentName]();
            }
            
            // Try loading from global scope
            if (window.HDTicketsReactComponents && window.HDTicketsReactComponents[componentName]) {
                return window.HDTicketsReactComponents[componentName];
            }

            throw new Error(`Component ${componentName} not found in registry`);
        },

        // Get error boundary component
        getErrorBoundaryComponent: async function() {
            try {
                const ErrorBoundary = await this.loadComponent('ErrorBoundary');
                return ErrorBoundary;
            } catch (error) {
                // Return default error boundary
                return this.createDefaultErrorBoundary();
            }
        },

        // Create default error boundary component
        createDefaultErrorBoundary: function() {
            return class DefaultErrorBoundary extends React.Component {
                constructor(props) {
                    super(props);
                    this.state = { hasError: false, error: null };
                }

                static getDerivedStateFromError(error) {
                    return { hasError: true, error };
                }

                componentDidCatch(error, errorInfo) {
                    console.error('React component error:', error, errorInfo);
                    if (this.props.onError) {
                        this.props.onError(error, errorInfo);
                    }
                }

                render() {
                    if (this.state.hasError) {
                        return this.props.fallback ? 
                            this.props.fallback(this.state.error) : 
                            React.createElement('div', {
                                className: 'react-error-fallback',
                                style: {
                                    padding: '1rem',
                                    border: '1px solid var(--hd-error)',
                                    borderRadius: 'var(--hd-radius-lg)',
                                    backgroundColor: 'var(--hd-error-50)',
                                    color: 'var(--hd-error-700)'
                                }
                            }, 'Component failed to load');
                    }

                    return this.props.children;
                }
            };
        },

        // Setup state synchronization between Alpine.js and React
        setupStateSync: function() {
            // Initialize shared state from data attributes
            const stateElements = document.querySelectorAll(this.config.stateSelector);
            
            stateElements.forEach(element => {
                const stateKey = element.getAttribute('data-state-key');
                const initialState = element.getAttribute('data-initial-state');
                
                if (stateKey && initialState) {
                    try {
                        const state = JSON.parse(initialState);
                        this.sharedState.set(stateKey, state);
                    } catch (e) {
                        console.warn('Invalid JSON in data-initial-state:', initialState);
                    }
                }
            });

            // Setup Alpine.js store synchronization
            if (window.Alpine) {
                this.setupAlpineSync();
            } else {
                // Wait for Alpine to load
                document.addEventListener('alpine:init', () => {
                    this.setupAlpineSync();
                });
            }
        },

        // Setup Alpine.js synchronization
        setupAlpineSync: function() {
            // Create reactive Alpine stores for shared state
            this.sharedState.forEach((state, key) => {
                if (window.Alpine.store) {
                    window.Alpine.store(key, state);
                    this.alpineStores.set(key, window.Alpine.store(key));
                }
            });

            if (this.config.enableLogging) {
                console.log(`🏔️ Synchronized ${this.sharedState.size} state stores with Alpine.js`);
            }
        },

        // Update shared state and sync with Alpine
        updateSharedState: function(key, updates) {
            const currentState = this.sharedState.get(key) || {};
            const newState = { ...currentState, ...updates };
            
            this.sharedState.set(key, newState);

            // Update Alpine store if it exists
            if (this.alpineStores.has(key)) {
                const store = this.alpineStores.get(key);
                Object.assign(store, updates);
            }

            // Update React components using this state
            this.notifyStateChange(key, newState);

            if (this.config.enableLogging) {
                console.log(`🔄 Updated shared state [${key}]:`, updates);
            }
        },

        // Notify React components of state changes
        notifyStateChange: function(stateKey, newState) {
            const elements = document.querySelectorAll(`[data-state-key="${stateKey}"]`);
            
            elements.forEach(element => {
                const componentData = this.components.get(element);
                if (componentData && componentData.component.onStateUpdate) {
                    componentData.component.onStateUpdate(newState);
                }
            });
        },

        // Get design tokens for React components
        getDesignTokens: function() {
            const rootStyles = getComputedStyle(document.documentElement);
            const tokens = {};

            // Extract CSS custom properties (design tokens)
            const properties = [
                'hd-primary', 'hd-secondary', 'hd-success', 'hd-warning', 'hd-error', 'hd-info',
                'hd-space-1', 'hd-space-2', 'hd-space-4', 'hd-space-6', 'hd-space-8',
                'hd-text-sm', 'hd-text-base', 'hd-text-lg', 'hd-text-xl',
                'hd-radius-lg', 'hd-shadow', 'hd-shadow-lg'
            ];

            properties.forEach(prop => {
                const value = rootStyles.getPropertyValue(`--${prop}`).trim();
                if (value) {
                    tokens[prop.replace('hd-', '')] = value;
                }
            });

            return tokens;
        },

        // Setup asset optimization for mixed Blade/React pages
        setupAssetOptimization: function() {
            // Preload React if components are present
            const reactComponents = document.querySelectorAll(this.config.componentSelector);
            
            if (reactComponents.length > 0 && !window.React) {
                this.preloadReact();
            }

            // Setup code splitting for large React components
            this.setupCodeSplitting();
        },

        // Preload React libraries
        preloadReact: function() {
            const reactUrls = [
                'https://unpkg.com/react@18/umd/react.production.min.js',
                'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js'
            ];

            reactUrls.forEach(url => {
                const link = document.createElement('link');
                link.rel = 'preload';
                link.href = url;
                link.as = 'script';
                link.crossOrigin = 'anonymous';
                document.head.appendChild(link);
            });

            if (this.config.enableLogging) {
                console.log('⚛️ Preloading React libraries');
            }
        },

        // Setup code splitting for React components
        setupCodeSplitting: function() {
            // Implement intersection observer for lazy loading components
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const element = entry.target;
                            const shouldLazyLoad = element.getAttribute('data-lazy-mount') === 'true';
                            
                            if (shouldLazyLoad && !this.mountedComponents.has(element)) {
                                this.mountComponent(element);
                                observer.unobserve(element);
                            }
                        }
                    });
                });

                // Observe all lazy-mount components
                const lazyComponents = document.querySelectorAll('[data-lazy-mount="true"]');
                lazyComponents.forEach(component => observer.observe(component));
            }
        },

        // Setup error boundaries
        setupErrorBoundaries: function() {
            // Global React error handling
            window.addEventListener('unhandledrejection', (event) => {
                if (event.reason && event.reason.message && event.reason.message.includes('React')) {
                    this.handleGlobalReactError(event.reason);
                    event.preventDefault();
                }
            });
        },

        // Setup global error handlers
        setupGlobalErrorHandlers: function() {
            // React component error handler
            this.onComponentError = this.onComponentError || ((error, errorInfo, componentName) => {
                console.error(`React component error in ${componentName}:`, error, errorInfo);
                
                // Report to error tracking service if available
                if (window.Sentry) {
                    window.Sentry.captureException(error, {
                        tags: { component: componentName },
                        extra: errorInfo
                    });
                }
            });
        },

        // Handle component-specific errors
        handleComponentError: function(error, errorInfo, componentName) {
            this.onComponentError(error, errorInfo, componentName);
        },

        // Handle global React errors
        handleGlobalReactError: function(error) {
            console.error('Global React error:', error);
            
            // Show user-friendly error message
            if (window.HDTickets && window.HDTickets.AccessibilityUtils) {
                window.HDTickets.AccessibilityUtils.announce(
                    'A component failed to load. Please refresh the page.',
                    'assertive'
                );
            }
        },

        // Render error fallback
        renderErrorFallback: function(element, error, componentName) {
            const fallback = document.createElement('div');
            fallback.className = 'react-error-fallback';
            fallback.innerHTML = `
                <div style="
                    padding: var(--hd-space-4);
                    border: 1px solid var(--hd-error);
                    border-radius: var(--hd-radius-lg);
                    background-color: var(--hd-error-50);
                    color: var(--hd-error-700);
                    text-align: center;
                ">
                    <p><strong>Component Error</strong></p>
                    <p>The ${componentName} component failed to load.</p>
                    <button onclick="window.location.reload()" style="
                        background: var(--hd-error);
                        color: white;
                        border: none;
                        padding: var(--hd-space-2) var(--hd-space-4);
                        border-radius: var(--hd-radius);
                        cursor: pointer;
                        margin-top: var(--hd-space-2);
                    ">Refresh Page</button>
                </div>
            `;
            
            element.innerHTML = '';
            element.appendChild(fallback);
        },

        // Create error fallback component
        createErrorFallback: function(error, componentName) {
            return React.createElement('div', {
                className: 'react-error-fallback',
                style: {
                    padding: 'var(--hd-space-4)',
                    border: '1px solid var(--hd-error)',
                    borderRadius: 'var(--hd-radius-lg)',
                    backgroundColor: 'var(--hd-error-50)',
                    color: 'var(--hd-error-700)',
                    textAlign: 'center'
                }
            }, [
                React.createElement('p', { key: 'title' }, React.createElement('strong', null, 'Component Error')),
                React.createElement('p', { key: 'message' }, `The ${componentName} component failed to load.`),
                React.createElement('button', {
                    key: 'refresh',
                    onClick: () => window.location.reload(),
                    style: {
                        background: 'var(--hd-error)',
                        color: 'white',
                        border: 'none',
                        padding: 'var(--hd-space-2) var(--hd-space-4)',
                        borderRadius: 'var(--hd-radius)',
                        cursor: 'pointer',
                        marginTop: 'var(--hd-space-2)'
                    }
                }, 'Refresh Page')
            ]);
        },

        // Setup mutation observer for dynamic content
        setupMutationObserver: function() {
            if (!window.MutationObserver) return;

            const observer = new MutationObserver((mutations) => {
                let hasNewComponents = false;

                mutations.forEach(mutation => {
                    if (mutation.type === 'childList') {
                        mutation.addedNodes.forEach(node => {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                // Check if the node itself is a React component
                                if (node.hasAttribute && node.hasAttribute('data-react-component')) {
                                    hasNewComponents = true;
                                }
                                
                                // Check for React components in added subtree
                                const components = node.querySelectorAll && 
                                    node.querySelectorAll(this.config.componentSelector);
                                if (components && components.length > 0) {
                                    hasNewComponents = true;
                                }
                            }
                        });
                    }
                });

                if (hasNewComponents) {
                    // Debounce mounting of new components
                    clearTimeout(this.mountTimeout);
                    this.mountTimeout = setTimeout(() => {
                        this.mountExistingComponents();
                    }, 100);
                }
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        },

        // Unmount a React component
        unmountComponent: function(element) {
            const componentData = this.components.get(element);
            
            if (componentData) {
                try {
                    if (componentData.root && componentData.root.unmount) {
                        componentData.root.unmount();
                    } else if (ReactDOM.unmountComponentAtNode) {
                        ReactDOM.unmountComponentAtNode(element);
                    }
                    
                    this.components.delete(element);
                    this.mountedComponents.delete(element);
                    
                    if (this.config.enableLogging) {
                        console.log(`🗑️ Unmounted React component: ${componentData.name}`);
                    }
                } catch (error) {
                    console.error('Failed to unmount React component:', error);
                }
            }
        },

        // Get component instance
        getComponent: function(element) {
            return this.components.get(element);
        },

        // Get shared state
        getSharedState: function(key) {
            return this.sharedState.get(key);
        },

        // Register a new component
        registerComponent: function(name, componentOrLoader) {
            this.componentRegistry[name] = typeof componentOrLoader === 'function' 
                ? componentOrLoader 
                : () => Promise.resolve(componentOrLoader);
                
            if (this.config.enableLogging) {
                console.log(`📝 Registered React component: ${name}`);
            }
        },

        // Destroy the bridge (cleanup)
        destroy: function() {
            // Unmount all components
            this.components.forEach((componentData, element) => {
                this.unmountComponent(element);
            });

            // Clear state
            this.components.clear();
            this.sharedState.clear();
            this.alpineStores.clear();
            this.mountedComponents.clear();
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            ReactBladeBridge.init();
        });
    } else {
        ReactBladeBridge.init();
    }

    // Export to global scope
    window.HDTickets = window.HDTickets || {};
    window.HDTickets.ReactBladeBridge = ReactBladeBridge;

    // Convenience functions
    window.mountReactComponent = (element, componentName, props) => {
        element.setAttribute('data-react-component', componentName);
        if (props) {
            element.setAttribute('data-props', JSON.stringify(props));
        }
        return ReactBladeBridge.mountComponent(element);
    };

    window.registerReactComponent = (name, component) => {
        ReactBladeBridge.registerComponent(name, component);
    };

})(window, document);
