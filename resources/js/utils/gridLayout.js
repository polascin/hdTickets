/**
 * HD Tickets - Grid Layout Utilities
 * 
 * JavaScript utilities for enhanced grid responsiveness, automatic layout
 * adjustments, and dynamic column management based on content and container size.
 * 
 * Features:
 * - Automatic grid column calculation based on content width
 * - Dynamic layout adjustments on resize
 * - Responsive grid item sizing
 * - Content-aware grid arrangement
 * - Performance-optimized resize handling
 */

class HDGridLayout {
    constructor() {
        this.observers = new Set();
        this.gridContainers = new Set();
        this.resizeDebounceTimer = null;
        this.config = {
            debounceDelay: 100,
            minColumnWidth: 200,
            maxColumns: 12,
            gapSize: 16
        };

        this.init();
    }

    /**
     * Initialize the grid layout system
     */
    init() {
        this.setupResizeListener();
        this.initializeExistingGrids();
        this.observeNewGrids();
        
        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.initializeExistingGrids();
            });
        }
    }

    /**
     * Setup global resize listener with debouncing
     */
    setupResizeListener() {
        window.addEventListener('resize', (event) => {
            clearTimeout(this.resizeDebounceTimer);
            this.resizeDebounceTimer = setTimeout(() => {
                this.handleResize(event);
            }, this.config.debounceDelay);
        });

        // Also listen for orientation changes on mobile
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleResize();
            }, 100);
        });
    }

    /**
     * Handle window resize events
     */
    handleResize() {
        this.gridContainers.forEach(container => {
            this.updateGridLayout(container);
        });

        // Dispatch custom event for other components
        window.dispatchEvent(new CustomEvent('hd:gridLayoutUpdated', {
            detail: { gridContainers: Array.from(this.gridContainers) }
        }));
    }

    /**
     * Initialize existing grids in the DOM
     */
    initializeExistingGrids() {
        const grids = document.querySelectorAll('.hd-grid, .hd-card-grid, .hd-grid-auto-fit, .hd-grid-auto-fill');
        grids.forEach(grid => {
            this.initializeGrid(grid);
        });
    }

    /**
     * Observe for new grids added to the DOM
     */
    observeNewGrids() {
        if ('MutationObserver' in window) {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the node itself is a grid
                            if (this.isGridContainer(node)) {
                                this.initializeGrid(node);
                            }
                            
                            // Check for grid children
                            const grids = node.querySelectorAll?.('.hd-grid, .hd-card-grid, .hd-grid-auto-fit, .hd-grid-auto-fill');
                            grids?.forEach(grid => {
                                this.initializeGrid(grid);
                            });
                        }
                    });
                });
            });

            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            this.observers.add(observer);
        }
    }

    /**
     * Check if an element is a grid container
     */
    isGridContainer(element) {
        return element.classList?.contains('hd-grid') ||
               element.classList?.contains('hd-card-grid') ||
               element.classList?.contains('hd-grid-auto-fit') ||
               element.classList?.contains('hd-grid-auto-fill');
    }

    /**
     * Initialize a specific grid container
     */
    initializeGrid(container) {
        if (!container || this.gridContainers.has(container)) {
            return;
        }

        this.gridContainers.add(container);
        
        // Set initial layout
        this.updateGridLayout(container);
        
        // Add data attributes for tracking
        container.setAttribute('data-hd-grid-initialized', 'true');
        
        // Setup container-specific observer if needed
        this.setupContainerObserver(container);
    }

    /**
     * Setup observer for container-specific changes
     */
    setupContainerObserver(container) {
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver((entries) => {
                entries.forEach((entry) => {
                    this.updateGridLayout(entry.target);
                });
            });

            resizeObserver.observe(container);
            this.observers.add(resizeObserver);
        }
    }

    /**
     * Update grid layout based on container size and content
     */
    updateGridLayout(container) {
        if (!container) return;

        const containerWidth = container.clientWidth;
        const children = Array.from(container.children);
        
        if (children.length === 0) return;

        // Determine layout type
        const layoutType = this.getLayoutType(container);
        
        switch (layoutType) {
            case 'auto-fit':
                this.updateAutoFitGrid(container, containerWidth);
                break;
            case 'auto-fill':
                this.updateAutoFillGrid(container, containerWidth);
                break;
            case 'card-grid':
                this.updateCardGrid(container, containerWidth);
                break;
            case 'fixed-grid':
                this.updateFixedGrid(container, containerWidth);
                break;
            default:
                this.updateDefaultGrid(container, containerWidth);
        }

        // Update grid item classes
        this.updateGridItemClasses(container);
    }

    /**
     * Determine the layout type of a grid container
     */
    getLayoutType(container) {
        if (container.classList.contains('hd-grid-auto-fit')) return 'auto-fit';
        if (container.classList.contains('hd-grid-auto-fill')) return 'auto-fill';
        if (container.classList.contains('hd-card-grid')) return 'card-grid';
        if (container.classList.contains('hd-grid')) return 'fixed-grid';
        return 'default';
    }

    /**
     * Update auto-fit grid layout
     */
    updateAutoFitGrid(container, containerWidth) {
        const minItemWidth = this.getMinItemWidth(container);
        const gap = this.getGapSize(container);
        const availableWidth = containerWidth - gap;
        
        const columnsCount = Math.floor(availableWidth / (minItemWidth + gap));
        const actualColumns = Math.max(1, Math.min(columnsCount, this.config.maxColumns));
        
        container.style.gridTemplateColumns = `repeat(${actualColumns}, 1fr)`;
        
        // Update container classes
        this.updateGridResponsiveClasses(container, actualColumns);
    }

    /**
     * Update auto-fill grid layout
     */
    updateAutoFillGrid(container, containerWidth) {
        const minItemWidth = this.getMinItemWidth(container);
        const gap = this.getGapSize(container);
        
        container.style.gridTemplateColumns = `repeat(auto-fill, minmax(${minItemWidth}px, 1fr))`;
        
        // Calculate approximate columns for responsive classes
        const approximateColumns = Math.floor(containerWidth / (minItemWidth + gap));
        this.updateGridResponsiveClasses(container, approximateColumns);
    }

    /**
     * Update card grid layout
     */
    updateCardGrid(container, containerWidth) {
        const cardWidth = this.getCardWidth(container);
        const gap = this.getGapSize(container);
        const columnsCount = Math.floor((containerWidth + gap) / (cardWidth + gap));
        const actualColumns = Math.max(1, Math.min(columnsCount, this.config.maxColumns));
        
        container.style.gridTemplateColumns = `repeat(${actualColumns}, 1fr)`;
        this.updateGridResponsiveClasses(container, actualColumns);
    }

    /**
     * Update fixed grid layout
     */
    updateFixedGrid(container, containerWidth) {
        // Fixed grids use CSS classes, but we can optimize for mobile
        if (containerWidth < 576) {
            container.classList.add('hd-grid-mobile-stack');
        } else {
            container.classList.remove('hd-grid-mobile-stack');
        }
    }

    /**
     * Update default grid layout
     */
    updateDefaultGrid(container, containerWidth) {
        // Default responsive behavior
        const columns = this.calculateOptimalColumns(container, containerWidth);
        this.updateGridResponsiveClasses(container, columns);
    }

    /**
     * Calculate optimal number of columns based on content
     */
    calculateOptimalColumns(container, containerWidth) {
        const children = container.children;
        if (children.length === 0) return 1;

        const minItemWidth = this.config.minColumnWidth;
        const gap = this.getGapSize(container);
        const maxPossibleColumns = Math.floor((containerWidth + gap) / (minItemWidth + gap));
        
        return Math.max(1, Math.min(maxPossibleColumns, children.length, this.config.maxColumns));
    }

    /**
     * Update responsive classes based on column count
     */
    updateGridResponsiveClasses(container, columns) {
        // Remove existing responsive classes
        const responsiveClasses = [
            'hd-grid-cols-1', 'hd-grid-cols-2', 'hd-grid-cols-3', 'hd-grid-cols-4',
            'hd-grid-cols-5', 'hd-grid-cols-6', 'hd-grid-cols-7', 'hd-grid-cols-8',
            'hd-grid-cols-9', 'hd-grid-cols-10', 'hd-grid-cols-11', 'hd-grid-cols-12'
        ];
        
        container.classList.remove(...responsiveClasses);
        
        // Add current column class
        container.classList.add(`hd-grid-cols-${columns}`);
        
        // Update data attribute
        container.setAttribute('data-hd-grid-columns', columns);
    }

    /**
     * Update classes on grid items
     */
    updateGridItemClasses(container) {
        const children = Array.from(container.children);
        const columns = parseInt(container.getAttribute('data-hd-grid-columns') || '1');
        
        children.forEach((child, index) => {
            // Add item position classes
            const row = Math.floor(index / columns) + 1;
            const col = (index % columns) + 1;
            
            child.setAttribute('data-hd-grid-row', row);
            child.setAttribute('data-hd-grid-col', col);
            
            // Add first/last column classes
            child.classList.toggle('hd-grid-item-first-col', col === 1);
            child.classList.toggle('hd-grid-item-last-col', col === columns);
            
            // Add first/last row classes
            child.classList.toggle('hd-grid-item-first-row', row === 1);
            child.classList.toggle('hd-grid-item-last-row', index >= children.length - columns);
        });
    }

    /**
     * Get minimum item width from CSS or data attribute
     */
    getMinItemWidth(container) {
        // Check data attribute first
        const dataWidth = container.getAttribute('data-min-item-width');
        if (dataWidth) return parseInt(dataWidth);
        
        // Check CSS custom property
        const computedStyle = getComputedStyle(container);
        const cssWidth = computedStyle.getPropertyValue('--min-item-width');
        if (cssWidth) return parseInt(cssWidth);
        
        // Determine by grid type
        if (container.classList.contains('hd-card-grid-sm')) return 200;
        if (container.classList.contains('hd-card-grid-lg')) return 400;
        if (container.classList.contains('hd-card-grid')) return 300;
        
        return this.config.minColumnWidth;
    }

    /**
     * Get card width for card grids
     */
    getCardWidth(container) {
        if (container.classList.contains('hd-card-grid-sm')) return 200;
        if (container.classList.contains('hd-card-grid-lg')) return 400;
        return 300;
    }

    /**
     * Get gap size from CSS or container class
     */
    getGapSize(container) {
        const computedStyle = getComputedStyle(container);
        const gapValue = computedStyle.gap || computedStyle.gridGap;
        
        if (gapValue && gapValue !== 'normal') {
            return parseInt(gapValue);
        }
        
        // Check class-based gaps
        if (container.classList.contains('hd-grid-gap-sm')) return 8;
        if (container.classList.contains('hd-grid-gap-lg')) return 24;
        if (container.classList.contains('hd-grid-gap-xl')) return 32;
        if (container.classList.contains('hd-grid-no-gap')) return 0;
        
        return this.config.gapSize;
    }

    /**
     * Public API: Register a new grid container
     */
    registerGrid(container) {
        if (container && !this.gridContainers.has(container)) {
            this.initializeGrid(container);
        }
    }

    /**
     * Public API: Unregister a grid container
     */
    unregisterGrid(container) {
        if (this.gridContainers.has(container)) {
            this.gridContainers.delete(container);
            container.removeAttribute('data-hd-grid-initialized');
            container.removeAttribute('data-hd-grid-columns');
        }
    }

    /**
     * Public API: Force update all grids
     */
    updateAllGrids() {
        this.gridContainers.forEach(container => {
            this.updateGridLayout(container);
        });
    }

    /**
     * Public API: Get grid statistics
     */
    getGridStats() {
        return {
            totalGrids: this.gridContainers.size,
            activeObservers: this.observers.size,
            config: { ...this.config }
        };
    }

    /**
     * Public API: Update configuration
     */
    updateConfig(newConfig) {
        this.config = { ...this.config, ...newConfig };
        this.updateAllGrids();
    }

    /**
     * Cleanup method
     */
    destroy() {
        // Clear timers
        clearTimeout(this.resizeDebounceTimer);
        
        // Disconnect observers
        this.observers.forEach(observer => {
            observer.disconnect?.();
        });
        
        // Clear sets
        this.gridContainers.clear();
        this.observers.clear();
        
        // Remove event listeners
        window.removeEventListener('resize', this.handleResize);
        window.removeEventListener('orientationchange', this.handleResize);
    }
}

/**
 * Grid Layout Helper Functions
 */
const GridLayoutHelpers = {
    /**
     * Create a responsive grid with specified options
     */
    createResponsiveGrid(container, options = {}) {
        const defaultOptions = {
            minItemWidth: 200,
            maxColumns: 12,
            gap: '1rem',
            autoFit: true
        };

        const config = { ...defaultOptions, ...options };

        // Apply base classes
        container.classList.add('hd-grid');
        
        if (config.autoFit) {
            container.classList.add('hd-grid-auto-fit');
        }

        // Set CSS custom properties
        container.style.setProperty('--min-item-width', `${config.minItemWidth}px`);
        container.style.setProperty('--max-columns', config.maxColumns);
        container.style.gap = config.gap;

        // Register with grid system
        if (window.hdGridLayout) {
            window.hdGridLayout.registerGrid(container);
        }

        return container;
    },

    /**
     * Create a card grid layout
     */
    createCardGrid(container, cardSize = 'md') {
        const sizeClasses = {
            sm: 'hd-card-grid-sm',
            md: 'hd-card-grid',
            lg: 'hd-card-grid-lg'
        };

        container.classList.add(sizeClasses[cardSize] || sizeClasses.md);

        if (window.hdGridLayout) {
            window.hdGridLayout.registerGrid(container);
        }

        return container;
    },

    /**
     * Add responsive breakpoint classes to grid items
     */
    addResponsiveColumns(container, breakpoints = {}) {
        const defaultBreakpoints = {
            sm: 1,
            md: 2,
            lg: 3,
            xl: 4
        };

        const responsive = { ...defaultBreakpoints, ...breakpoints };

        // Apply responsive classes to container
        Object.entries(responsive).forEach(([breakpoint, columns]) => {
            if (breakpoint === 'sm') {
                container.classList.add(`hd-col-sm-${columns}`);
            } else {
                container.classList.add(`hd-col-${breakpoint}-${columns}`);
            }
        });

        return container;
    },

    /**
     * Calculate optimal layout for content
     */
    calculateOptimalLayout(items, containerWidth, minItemWidth = 200) {
        const itemCount = Array.isArray(items) ? items.length : items;
        const gap = 16; // Default gap
        const availableWidth = containerWidth - gap;
        
        const maxColumns = Math.floor(availableWidth / (minItemWidth + gap));
        const optimalColumns = Math.max(1, Math.min(maxColumns, itemCount, 12));
        
        return {
            columns: optimalColumns,
            itemWidth: Math.floor((availableWidth - (gap * (optimalColumns - 1))) / optimalColumns),
            rows: Math.ceil(itemCount / optimalColumns)
        };
    }
};

// Initialize the grid layout system
let hdGridLayout;

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        hdGridLayout = new HDGridLayout();
        window.hdGridLayout = hdGridLayout;
        window.GridLayoutHelpers = GridLayoutHelpers;
    });
} else {
    hdGridLayout = new HDGridLayout();
    window.hdGridLayout = hdGridLayout;
    window.GridLayoutHelpers = GridLayoutHelpers;
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { HDGridLayout, GridLayoutHelpers };
}

export { HDGridLayout, GridLayoutHelpers };
