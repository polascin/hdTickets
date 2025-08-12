/**
 * HD Tickets - Skeleton Loaders
 * Loading state management for better UX
 */

class SkeletonLoader {
    constructor() {
        this.activeLoaders = new Set();
        this.init();
    }

    init() {
        // Auto-initialize skeleton loaders on page load
        this.initializeSkeletons();
        
        // Set up observers for dynamic content
        this.setupIntersectionObserver();
    }

    initializeSkeletons() {
        const skeletonElements = document.querySelectorAll('[data-skeleton]');
        skeletonElements.forEach(element => {
            this.prepareSkeleton(element);
        });
    }

    prepareSkeleton(element) {
        const skeletonType = element.getAttribute('data-skeleton');
        const targetSelector = element.getAttribute('data-skeleton-target');
        
        // Create skeleton content based on type
        switch (skeletonType) {
            case 'stat-card':
                this.createStatCardSkeleton(element);
                break;
            case 'ticket-item':
                this.createTicketItemSkeleton(element);
                break;
            case 'action-card':
                this.createActionCardSkeleton(element);
                break;
            case 'recent-tickets':
                this.createRecentTicketsSkeleton(element);
                break;
            default:
                this.createGenericSkeleton(element);
        }
    }

    createStatCardSkeleton(element) {
        element.innerHTML = `
            <div class="skeleton-card">
                <div class="flex items-center gap-4">
                    <div class="skeleton-icon w-12 h-12 rounded-lg"></div>
                    <div class="flex-1">
                        <div class="skeleton-title h-4 w-24 mb-2"></div>
                        <div class="skeleton-value h-6 w-16"></div>
                    </div>
                </div>
            </div>
        `;
    }

    createTicketItemSkeleton(element) {
        element.innerHTML = `
            <div class="skeleton-card">
                <div class="flex items-center gap-4">
                    <div class="skeleton w-3 h-3 rounded-full"></div>
                    <div class="flex-1">
                        <div class="skeleton-title h-4 w-48 mb-2"></div>
                        <div class="skeleton-text h-3 w-32 mb-1"></div>
                        <div class="skeleton-text h-3 w-24"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="skeleton h-6 w-16 rounded-full"></div>
                        <div class="skeleton h-6 w-20 rounded-full"></div>
                    </div>
                </div>
            </div>
        `;
    }

    createActionCardSkeleton(element) {
        element.innerHTML = `
            <div class="skeleton-card">
                <div class="flex items-center gap-4">
                    <div class="skeleton-icon w-12 h-12 rounded-lg"></div>
                    <div class="flex-1">
                        <div class="skeleton-title h-5 w-32 mb-2"></div>
                        <div class="skeleton-text h-3 w-40"></div>
                    </div>
                </div>
            </div>
        `;
    }

    createRecentTicketsSkeleton(element) {
        const skeletonItems = Array.from({ length: 3 }, () => `
            <div class="skeleton-card mb-4">
                <div class="flex items-center gap-4">
                    <div class="skeleton w-3 h-3 rounded-full"></div>
                    <div class="flex-1">
                        <div class="skeleton-title h-4 w-48 mb-2"></div>
                        <div class="skeleton-text h-3 w-32 mb-1"></div>
                        <div class="skeleton-text h-3 w-24"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="skeleton h-6 w-16 rounded-full"></div>
                        <div class="skeleton h-6 w-20 rounded-full"></div>
                    </div>
                </div>
            </div>
        `).join('');

        element.innerHTML = `
            <div class="skeleton-header mb-6">
                <div class="skeleton-title h-6 w-48"></div>
            </div>
            ${skeletonItems}
        `;
    }

    createGenericSkeleton(element) {
        element.innerHTML = `
            <div class="skeleton-card">
                <div class="skeleton-title h-6 w-3/4 mb-4"></div>
                <div class="skeleton-text h-4 w-full mb-2"></div>
                <div class="skeleton-text h-4 w-5/6 mb-2"></div>
                <div class="skeleton-text h-4 w-2/3"></div>
            </div>
        `;
    }

    show(selector) {
        const elements = typeof selector === 'string' ? 
            document.querySelectorAll(selector) : 
            [selector];

        elements.forEach(element => {
            if (element) {
                element.classList.remove('hidden');
                element.classList.add('skeleton-loading');
                this.activeLoaders.add(element);
            }
        });
    }

    hide(selector) {
        const elements = typeof selector === 'string' ? 
            document.querySelectorAll(selector) : 
            [selector];

        elements.forEach(element => {
            if (element) {
                element.classList.add('hidden');
                element.classList.remove('skeleton-loading');
                this.activeLoaders.delete(element);
            }
        });
    }

    showForElement(targetSelector, duration = null) {
        const targetElement = document.querySelector(targetSelector);
        const skeletonElement = document.querySelector(`[data-skeleton-target="${targetSelector}"]`);
        
        if (targetElement && skeletonElement) {
            // Hide original content
            targetElement.style.display = 'none';
            
            // Show skeleton
            this.show(skeletonElement);
            
            // Auto-hide after duration if specified
            if (duration) {
                setTimeout(() => {
                    this.hideForElement(targetSelector);
                }, duration);
            }
        }
    }

    hideForElement(targetSelector) {
        const targetElement = document.querySelector(targetSelector);
        const skeletonElement = document.querySelector(`[data-skeleton-target="${targetSelector}"]`);
        
        if (targetElement && skeletonElement) {
            // Hide skeleton
            this.hide(skeletonElement);
            
            // Show original content with fade-in
            targetElement.style.display = '';
            targetElement.classList.add('animate-fade-in');
            
            // Remove animation class after completion
            setTimeout(() => {
                targetElement.classList.remove('animate-fade-in');
            }, 300);
        }
    }

    setupIntersectionObserver() {
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const element = entry.target;
                        if (element.hasAttribute('data-lazy-skeleton')) {
                            this.prepareSkeleton(element);
                            observer.unobserve(element);
                        }
                    }
                });
            }, {
                rootMargin: '50px'
            });

            // Observe lazy skeleton elements
            document.querySelectorAll('[data-lazy-skeleton]').forEach(element => {
                observer.observe(element);
            });
        }
    }

    // Utility methods for common loading scenarios
    showStatsLoading() {
        this.show('[data-skeleton="stat-card"]');
    }

    hideStatsLoading() {
        this.hide('[data-skeleton="stat-card"]');
    }

    showTicketsLoading() {
        this.show('[data-skeleton="recent-tickets"]');
    }

    hideTicketsLoading() {
        this.hide('[data-skeleton="recent-tickets"]');
    }

    showActionsLoading() {
        this.show('[data-skeleton="actions-grid"]');
    }

    hideActionsLoading() {
        this.hide('[data-skeleton="actions-grid"]');
    }

    // Simulate loading for demo purposes
    simulateLoading(selector, duration = 2000) {
        this.show(selector);
        setTimeout(() => {
            this.hide(selector);
        }, duration);
    }

    // Clean up all active loaders
    hideAll() {
        this.activeLoaders.forEach(element => {
            element.classList.add('hidden');
            element.classList.remove('skeleton-loading');
        });
        this.activeLoaders.clear();
    }

    // Get loading state
    isLoading(selector) {
        const element = document.querySelector(selector);
        return element && element.classList.contains('skeleton-loading');
    }

    getActiveCount() {
        return this.activeLoaders.size;
    }
}

// CSS Animation styles for skeleton loader
const skeletonStyles = `
    @keyframes shimmer {
        0% { background-position: -468px 0; }
        100% { background-position: 468px 0; }
    }
    
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
        background-size: 400px 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 4px;
    }
    
    .skeleton-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e5e7eb;
    }
    
    .skeleton-icon {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
        background-size: 400px 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 8px;
    }
    
    .skeleton-title {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
        background-size: 400px 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 4px;
        height: 1rem;
    }
    
    .skeleton-text {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
        background-size: 400px 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 4px;
        height: 0.75rem;
    }
    
    .skeleton-value {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 37%, #f0f0f0 63%);
        background-size: 400px 100%;
        animation: shimmer 1.5s ease-in-out infinite;
        border-radius: 4px;
        height: 1.5rem;
    }
    
    .skeleton-loading {
        pointer-events: none;
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
`;

// Inject styles
if (typeof document !== 'undefined') {
    const styleSheet = document.createElement('style');
    styleSheet.textContent = skeletonStyles;
    document.head.appendChild(styleSheet);
}

// Global instance
if (typeof window !== 'undefined') {
    window.SkeletonLoader = SkeletonLoader;
    window.skeletonLoader = new SkeletonLoader();
}
