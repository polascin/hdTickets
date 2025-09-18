/**
 * HD Tickets Virtual Scrolling System
 * 
 * Efficient virtual scrolling implementation for handling large lists
 * with minimal DOM elements and optimal performance.
 * 
 * @version 1.0.0
 * @author HD Tickets Development Team
 */

class VirtualScroller {
    constructor(container, options = {}) {
        this.container = typeof container === 'string' 
            ? document.querySelector(container)
            : container;
            
        if (!this.container) {
            throw new Error('Container element not found');
        }
        
        this.options = {
            // Item configuration
            itemHeight: 100,
            estimateItemHeight: false,
            
            // Buffer configuration
            overscan: 5,
            threshold: 100,
            
            // Performance settings
            debounceMs: 10,
            useRAF: true,
            
            // Styling
            containerClass: 'virtual-scroller',
            itemClass: 'virtual-item',
            
            // Callbacks
            renderItem: null,
            onScroll: null,
            onUpdate: null,
            
            // Data
            items: [],
            
            // Debug
            debug: false,
            
            ...options
        };
        
        // Internal state
        this.scrollTop = 0;
        this.containerHeight = 0;
        this.totalHeight = 0;
        this.startIndex = 0;
        this.endIndex = 0;
        this.visibleItems = [];
        this.itemHeights = new Map();
        this.averageItemHeight = this.options.itemHeight;
        
        // DOM elements
        this.viewport = null;
        this.spacerBefore = null;
        this.spacerAfter = null;
        this.itemContainer = null;
        
        // Performance optimization
        this.isScrolling = false;
        this.scrollTimeout = null;
        this.rafId = null;
        
        this.init();
    }
    
    /**
     * Initialize virtual scroller
     */
    init() {
        this.createDOM();
        this.setupEventListeners();
        this.calculateDimensions();
        this.updateVisibleItems();
        
        if (this.options.debug) {
            console.log('[VirtualScroller] Initialized with options:', this.options);
        }
    }
    
    /**
     * Create DOM structure
     */
    createDOM() {
        this.container.classList.add(this.options.containerClass);
        
        // Create viewport
        this.viewport = document.createElement('div');
        this.viewport.style.cssText = `
            height: 100%;
            overflow-y: auto;
            position: relative;
        `;
        
        // Create spacers
        this.spacerBefore = document.createElement('div');
        this.spacerAfter = document.createElement('div');
        
        // Create item container
        this.itemContainer = document.createElement('div');
        this.itemContainer.style.cssText = `
            position: relative;
        `;
        
        // Append elements
        this.viewport.appendChild(this.spacerBefore);
        this.viewport.appendChild(this.itemContainer);
        this.viewport.appendChild(this.spacerAfter);
        this.container.appendChild(this.viewport);
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Debounced scroll handler
        const debouncedScroll = this.debounce(() => {
            this.handleScroll();
        }, this.options.debounceMs);
        
        this.viewport.addEventListener('scroll', (e) => {
            this.scrollTop = this.viewport.scrollTop;
            this.isScrolling = true;
            
            if (this.options.useRAF) {
                if (this.rafId) {
                    cancelAnimationFrame(this.rafId);
                }
                this.rafId = requestAnimationFrame(() => {
                    this.updateVisibleItems();
                    this.isScrolling = false;
                });
            } else {
                debouncedScroll();
            }
            
            if (this.options.onScroll) {
                this.options.onScroll({
                    scrollTop: this.scrollTop,
                    scrollDirection: this.getScrollDirection(this.scrollTop),
                    isScrolling: this.isScrolling
                });
            }
        });
        
        // Handle resize
        const resizeObserver = new ResizeObserver(() => {
            this.calculateDimensions();
            this.updateVisibleItems();
        });
        
        resizeObserver.observe(this.container);
        resizeObserver.observe(this.viewport);
        
        // Handle window resize
        window.addEventListener('resize', this.debounce(() => {
            this.calculateDimensions();
            this.updateVisibleItems();
        }, 100));
    }
    
    /**
     * Calculate container dimensions
     */
    calculateDimensions() {
        this.containerHeight = this.viewport.clientHeight;
        
        if (this.options.estimateItemHeight) {
            this.calculateAverageItemHeight();
        }
        
        this.totalHeight = this.getTotalHeight();
        
        if (this.options.debug) {
            console.log('[VirtualScroller] Dimensions:', {
                containerHeight: this.containerHeight,
                totalHeight: this.totalHeight,
                averageItemHeight: this.averageItemHeight
            });
        }
    }
    
    /**
     * Calculate average item height from measured items
     */
    calculateAverageItemHeight() {
        if (this.itemHeights.size === 0) return;
        
        const heights = Array.from(this.itemHeights.values());
        this.averageItemHeight = heights.reduce((sum, height) => sum + height, 0) / heights.length;
    }
    
    /**
     * Get total height of all items
     */
    getTotalHeight() {
        if (this.options.estimateItemHeight && this.itemHeights.size > 0) {
            // Use measured heights where available, estimate for the rest
            let totalHeight = 0;
            for (let i = 0; i < this.options.items.length; i++) {
                totalHeight += this.itemHeights.get(i) || this.averageItemHeight;
            }
            return totalHeight;
        }
        
        return this.options.items.length * this.averageItemHeight;
    }
    
    /**
     * Calculate which items should be visible
     */
    calculateVisibleRange() {
        const itemCount = this.options.items.length;
        
        if (itemCount === 0) {
            return { startIndex: 0, endIndex: 0 };
        }
        
        let startIndex = 0;
        let endIndex = 0;
        
        if (this.options.estimateItemHeight && this.itemHeights.size > 0) {
            // Calculate based on measured heights
            startIndex = this.findStartIndex();
            endIndex = this.findEndIndex(startIndex);
        } else {
            // Calculate based on fixed height
            startIndex = Math.floor(this.scrollTop / this.averageItemHeight);
            const visibleItemCount = Math.ceil(this.containerHeight / this.averageItemHeight);
            endIndex = startIndex + visibleItemCount;
        }
        
        // Apply overscan
        startIndex = Math.max(0, startIndex - this.options.overscan);
        endIndex = Math.min(itemCount, endIndex + this.options.overscan);
        
        return { startIndex, endIndex };
    }
    
    /**
     * Update visible items
     */
    updateVisibleItems() {
        const { startIndex, endIndex } = this.calculateVisibleRange();
        
        // Check if range has changed
        if (startIndex === this.startIndex && endIndex === this.endIndex) {
            return;
        }
        
        this.startIndex = startIndex;
        this.endIndex = endIndex;
        
        // Update visible items
        this.visibleItems = this.options.items.slice(startIndex, endIndex);
        
        // Render items
        this.renderItems();
        
        // Update spacers
        this.updateSpacers();
        
        if (this.options.onUpdate) {
            this.options.onUpdate({
                startIndex,
                endIndex,
                visibleItems: this.visibleItems,
                totalItems: this.options.items.length
            });
        }
        
        if (this.options.debug) {
            console.log('[VirtualScroller] Updated range:', { startIndex, endIndex });
        }
    }
    
    /**
     * Render visible items
     */
    renderItems() {
        // Clear existing items
        this.itemContainer.innerHTML = '';
        
        if (!this.options.renderItem) {
            console.warn('[VirtualScroller] No renderItem function provided');
            return;
        }
        
        // Render each visible item
        this.visibleItems.forEach((item, index) => {
            const itemIndex = this.startIndex + index;
            const itemElement = this.createItemElement(item, itemIndex);
            this.itemContainer.appendChild(itemElement);
        });
        
        // Measure items if needed
        if (this.options.estimateItemHeight) {
            this.measureItems();
        }
    }
    
    /**
     * Create item element
     */
    createItemElement(item, index) {
        const itemElement = document.createElement('div');
        itemElement.className = this.options.itemClass;
        itemElement.dataset.index = index;
        
        // Set initial height if not estimating
        if (!this.options.estimateItemHeight) {
            itemElement.style.height = `${this.averageItemHeight}px`;
        }
        
        // Render item content
        const content = this.options.renderItem(item, index);
        
        if (typeof content === 'string') {
            itemElement.innerHTML = content;
        } else if (content instanceof HTMLElement) {
            itemElement.appendChild(content);
        }
        
        return itemElement;
    }
    
    /**
     * Update items data
     */
    updateItems(newItems) {
        this.options.items = newItems;
        this.itemHeights.clear();
        this.calculateDimensions();
        this.updateVisibleItems();
    }
    
    /**
     * Debounce utility
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Update spacer heights
     */
    updateSpacers() {
        const beforeHeight = this.startIndex * this.averageItemHeight;
        const afterHeight = (this.options.items.length - this.endIndex) * this.averageItemHeight;
        
        this.spacerBefore.style.height = `${beforeHeight}px`;
        this.spacerAfter.style.height = `${Math.max(0, afterHeight)}px`;
    }
    
    /**
     * Handle scroll event
     */
    handleScroll() {
        this.updateVisibleItems();
        this.isScrolling = false;
    }
    
    /**
     * Get scroll direction
     */
    getScrollDirection(currentScrollTop) {
        const direction = currentScrollTop > this.scrollTop ? 'down' : 'up';
        this.lastScrollTop = this.scrollTop;
        return direction;
    }
    
    /**
     * Scroll to specific index
     */
    scrollToIndex(index, alignment = 'start') {
        const itemCount = this.options.items.length;
        const clampedIndex = Math.max(0, Math.min(itemCount - 1, index));
        
        let scrollTop = clampedIndex * this.averageItemHeight;
        
        // Apply alignment
        if (alignment === 'center') {
            scrollTop -= this.containerHeight / 2;
        } else if (alignment === 'end') {
            scrollTop -= this.containerHeight - this.averageItemHeight;
        }
        
        scrollTop = Math.max(0, Math.min(this.totalHeight - this.containerHeight, scrollTop));
        
        this.viewport.scrollTop = scrollTop;
    }
    
    /**
     * Get statistics
     */
    getStats() {
        return {
            totalItems: this.options.items.length,
            visibleItems: this.visibleItems.length,
            startIndex: this.startIndex,
            endIndex: this.endIndex,
            scrollTop: this.scrollTop,
            containerHeight: this.containerHeight,
            totalHeight: this.totalHeight,
            averageItemHeight: this.averageItemHeight
        };
    }
    
    /**
     * Refresh and recalculate everything
     */
    refresh() {
        this.calculateDimensions();
        this.updateVisibleItems();
    }
    
    /**
     * Destroy virtual scroller
     */
    destroy() {
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
        }
        
        this.container.innerHTML = '';
        this.container.classList.remove(this.options.containerClass);
        
        if (this.options.debug) {
            console.log('[VirtualScroller] Destroyed');
        }
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VirtualScroller;
}

// Global namespace
window.VirtualScroller = VirtualScroller;
