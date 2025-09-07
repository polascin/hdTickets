/**
 * HD Tickets - Container Queries Support
 * 
 * Simplified container query utilities with polyfill support
 */

class ContainerQuerySupport {
    constructor(options = {}) {
        this.options = {
            enablePolyfill: true,
            debug: false,
            breakpoints: {
                xs: 320,
                sm: 384,
                md: 512,
                lg: 768,
                xl: 1024,
                '2xl': 1280
            },
            ...options
        };
        
        this.supportsContainerQueries = this.checkSupport();
        this.init();
    }
    
    init() {
        if (this.supportsContainerQueries) {
            console.log('✅ Container queries supported');
        } else if (this.options.enablePolyfill) {
            console.log('🔄 Enabling container query polyfill');
            this.setupPolyfill();
        }
    }
    
    checkSupport() {
        try {
            return CSS.supports('container-type', 'inline-size');
        } catch (e) {
            return false;
        }
    }
    
    setupPolyfill() {
        // Basic polyfill using ResizeObserver
        const containers = document.querySelectorAll('.card-container, .dashboard-container');
        
        containers.forEach(container => {
            const observer = new ResizeObserver(entries => {
                entries.forEach(entry => {
                    const width = entry.borderBoxSize[0].inlineSize;
                    this.updateContainerClasses(container, width);
                });
            });
            
            observer.observe(container);
        });
    }
    
    updateContainerClasses(element, width) {
        // Remove existing classes
        Object.keys(this.options.breakpoints).forEach(bp => {
            element.classList.remove(`cq-${bp}`);
        });
        
        // Add appropriate classes
        Object.entries(this.options.breakpoints).forEach(([name, breakpoint]) => {
            if (width >= breakpoint) {
                element.classList.add(`cq-${name}`);
            }
        });
    }
    
    makeContainer(element, name = 'component') {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (!element) return;
        
        if (this.supportsContainerQueries) {
            element.style.containerName = name;
            element.style.containerType = 'inline-size';
        }
    }
}

// Auto-initialize
if (typeof window !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        window.containerQuerySupport = new ContainerQuerySupport();
    });
}

export default ContainerQuerySupport;
