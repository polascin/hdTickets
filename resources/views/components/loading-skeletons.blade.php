{{-- Loading Skeletons Component --}}
{{-- Provides various skeleton loading states for improved perceived performance --}}

<div class="loading-skeletons">
    {{-- Ticket Card Skeleton --}}
    <template x-if="false" id="ticket-card-skeleton">
        <div class="ticket-card-skeleton bg-white rounded-lg shadow-md p-6 animate-pulse">
            <div class="flex items-start gap-4">
                {{-- Event Image Skeleton --}}
                <div class="w-20 h-20 bg-gray-300 rounded-lg flex-shrink-0"></div>
                
                {{-- Content Skeleton --}}
                <div class="flex-1 space-y-3">
                    {{-- Title --}}
                    <div class="h-5 bg-gray-300 rounded w-3/4"></div>
                    
                    {{-- Venue and Date --}}
                    <div class="space-y-2">
                        <div class="h-4 bg-gray-200 rounded w-1/2"></div>
                        <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                    </div>
                    
                    {{-- Price and Actions --}}
                    <div class="flex items-center justify-between pt-2">
                        <div class="h-6 bg-gray-300 rounded w-20"></div>
                        <div class="flex gap-2">
                            <div class="h-8 w-16 bg-gray-200 rounded"></div>
                            <div class="h-8 w-20 bg-gray-300 rounded"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Dashboard Widget Skeleton --}}
    <template x-if="false" id="dashboard-widget-skeleton">
        <div class="dashboard-widget-skeleton bg-white rounded-lg shadow-md animate-pulse">
            {{-- Widget Header --}}
            <div class="p-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-gray-300 rounded"></div>
                        <div class="h-5 bg-gray-300 rounded w-32"></div>
                    </div>
                    <div class="w-6 h-6 bg-gray-200 rounded"></div>
                </div>
            </div>
            
            {{-- Widget Content --}}
            <div class="p-4 space-y-4">
                {{-- Chart Area --}}
                <div class="h-48 bg-gray-200 rounded"></div>
                
                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center space-y-2">
                        <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                        <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                    </div>
                    <div class="text-center space-y-2">
                        <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                        <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                    </div>
                    <div class="text-center space-y-2">
                        <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                        <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Table Skeleton --}}
    <template x-if="false" id="table-skeleton">
        <div class="table-skeleton bg-white rounded-lg shadow-md overflow-hidden animate-pulse">
            {{-- Table Header --}}
            <div class="bg-gray-50 px-6 py-3 border-b border-gray-200">
                <div class="flex gap-4">
                    <div class="h-4 bg-gray-300 rounded w-32"></div>
                    <div class="h-4 bg-gray-300 rounded w-24"></div>
                    <div class="h-4 bg-gray-300 rounded w-20"></div>
                    <div class="h-4 bg-gray-300 rounded w-28"></div>
                    <div class="h-4 bg-gray-300 rounded w-16"></div>
                </div>
            </div>
            
            {{-- Table Rows --}}
            <div class="divide-y divide-gray-200">
                <template x-for="i in 8">
                    <div class="px-6 py-4">
                        <div class="flex gap-4">
                            <div class="h-4 bg-gray-200 rounded w-32"></div>
                            <div class="h-4 bg-gray-200 rounded w-24"></div>
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                            <div class="h-4 bg-gray-200 rounded w-28"></div>
                            <div class="h-4 bg-gray-200 rounded w-16"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Form Skeleton --}}
    <template x-if="false" id="form-skeleton">
        <div class="form-skeleton bg-white rounded-lg shadow-md p-6 space-y-6 animate-pulse">
            {{-- Form Title --}}
            <div class="h-6 bg-gray-300 rounded w-48"></div>
            
            {{-- Form Fields --}}
            <div class="space-y-4">
                <div class="space-y-2">
                    <div class="h-4 bg-gray-300 rounded w-24"></div>
                    <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                
                <div class="space-y-2">
                    <div class="h-4 bg-gray-300 rounded w-32"></div>
                    <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                
                <div class="space-y-2">
                    <div class="h-4 bg-gray-300 rounded w-28"></div>
                    <div class="h-24 bg-gray-200 rounded"></div>
                </div>
                
                {{-- Checkbox Group --}}
                <div class="space-y-2">
                    <div class="h-4 bg-gray-300 rounded w-36"></div>
                    <div class="space-y-2">
                        <template x-for="i in 3">
                            <div class="flex items-center gap-3">
                                <div class="w-4 h-4 bg-gray-200 rounded"></div>
                                <div class="h-4 bg-gray-200 rounded w-24"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- Form Actions --}}
            <div class="flex gap-3 pt-4">
                <div class="h-10 bg-gray-300 rounded w-24"></div>
                <div class="h-10 bg-gray-200 rounded w-20"></div>
            </div>
        </div>
    </template>

    {{-- List Item Skeleton --}}
    <template x-if="false" id="list-item-skeleton">
        <div class="list-item-skeleton flex items-center p-4 bg-white border-b border-gray-200 animate-pulse">
            {{-- Avatar/Icon --}}
            <div class="w-12 h-12 bg-gray-300 rounded-full flex-shrink-0"></div>
            
            {{-- Content --}}
            <div class="ml-4 flex-1 space-y-2">
                <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
            </div>
            
            {{-- Action --}}
            <div class="w-8 h-8 bg-gray-200 rounded flex-shrink-0"></div>
        </div>
    </template>

    {{-- Profile Skeleton --}}
    <template x-if="false" id="profile-skeleton">
        <div class="profile-skeleton bg-white rounded-lg shadow-md p-6 animate-pulse">
            {{-- Profile Header --}}
            <div class="flex items-start gap-6 mb-6">
                {{-- Avatar --}}
                <div class="w-24 h-24 bg-gray-300 rounded-full flex-shrink-0"></div>
                
                {{-- Info --}}
                <div class="flex-1 space-y-3">
                    <div class="h-6 bg-gray-300 rounded w-48"></div>
                    <div class="h-4 bg-gray-200 rounded w-32"></div>
                    <div class="h-4 bg-gray-200 rounded w-40"></div>
                    
                    {{-- Action Buttons --}}
                    <div class="flex gap-3 pt-2">
                        <div class="h-8 bg-gray-300 rounded w-20"></div>
                        <div class="h-8 bg-gray-200 rounded w-16"></div>
                    </div>
                </div>
            </div>
            
            {{-- Profile Stats --}}
            <div class="grid grid-cols-3 gap-4 py-4 border-t border-gray-200">
                <div class="text-center space-y-2">
                    <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                    <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                </div>
                <div class="text-center space-y-2">
                    <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                    <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                </div>
                <div class="text-center space-y-2">
                    <div class="h-6 bg-gray-300 rounded w-12 mx-auto"></div>
                    <div class="h-3 bg-gray-200 rounded w-16 mx-auto"></div>
                </div>
            </div>
        </div>
    </template>

    {{-- Chat Message Skeleton --}}
    <template x-if="false" id="chat-message-skeleton">
        <div class="chat-message-skeleton space-y-4 animate-pulse">
            <template x-for="i in 5">
                <div class="flex items-start gap-3" :class="{ 'flex-row-reverse': i % 2 === 0 }">
                    {{-- Avatar --}}
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex-shrink-0"></div>
                    
                    {{-- Message --}}
                    <div class="flex-1 max-w-xs">
                        <div class="bg-gray-200 rounded-lg p-3 space-y-2">
                            <div class="h-3 bg-gray-300 rounded w-full"></div>
                            <div class="h-3 bg-gray-300 rounded" :class="i % 3 === 0 ? 'w-3/4' : 'w-1/2'"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </template>

    {{-- Navigation Skeleton --}}
    <template x-if="false" id="navigation-skeleton">
        <div class="navigation-skeleton bg-white shadow-md animate-pulse">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    {{-- Logo --}}
                    <div class="h-8 bg-gray-300 rounded w-32"></div>
                    
                    {{-- Nav Links --}}
                    <div class="hidden md:flex items-center gap-6">
                        <template x-for="i in 5">
                            <div class="h-4 bg-gray-200 rounded w-20"></div>
                        </template>
                    </div>
                    
                    {{-- User Menu --}}
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 bg-gray-200 rounded"></div>
                        <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Search Results Skeleton --}}
    <template x-if="false" id="search-results-skeleton">
        <div class="search-results-skeleton space-y-4 animate-pulse">
            {{-- Search Stats --}}
            <div class="flex items-center justify-between">
                <div class="h-4 bg-gray-300 rounded w-48"></div>
                <div class="h-4 bg-gray-200 rounded w-24"></div>
            </div>
            
            {{-- Results --}}
            <div class="space-y-4">
                <template x-for="i in 6">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <div class="flex gap-4">
                            <div class="w-16 h-16 bg-gray-300 rounded flex-shrink-0"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-5 bg-gray-300 rounded w-3/4"></div>
                                <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                <div class="h-3 bg-gray-200 rounded w-2/3"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    {{-- Mobile Card Skeleton --}}
    <template x-if="false" id="mobile-card-skeleton">
        <div class="mobile-card-skeleton bg-white rounded-lg shadow-md p-4 animate-pulse">
            <div class="space-y-3">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <div class="h-5 bg-gray-300 rounded w-32"></div>
                    <div class="w-6 h-6 bg-gray-200 rounded"></div>
                </div>
                
                {{-- Image --}}
                <div class="h-32 bg-gray-200 rounded"></div>
                
                {{-- Content --}}
                <div class="space-y-2">
                    <div class="h-4 bg-gray-300 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                </div>
                
                {{-- Footer --}}
                <div class="flex items-center justify-between pt-2">
                    <div class="h-4 bg-gray-300 rounded w-20"></div>
                    <div class="flex gap-2">
                        <div class="w-8 h-8 bg-gray-200 rounded"></div>
                        <div class="w-8 h-8 bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>

<style>
    /* Enhanced skeleton animations */
    @keyframes skeleton-loading {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }
    
    .loading-skeletons .animate-pulse {
        animation: skeleton-loading 1.2s ease-in-out infinite;
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200px 100%;
    }
    
    /* Skeleton color variations */
    .loading-skeletons .bg-gray-300 {
        background: linear-gradient(90deg, #d1d5db 25%, #c7cbd1 50%, #d1d5db 75%);
        background-size: 200px 100%;
    }
    
    .loading-skeletons .bg-gray-200 {
        background: linear-gradient(90deg, #e5e7eb 25%, #dde0e4 50%, #e5e7eb 75%);
        background-size: 200px 100%;
    }
    
    /* Dark mode skeletons */
    .dark .loading-skeletons .bg-gray-300 {
        background: linear-gradient(90deg, #4b5563 25%, #3f454f 50%, #4b5563 75%);
        background-size: 200px 100%;
    }
    
    .dark .loading-skeletons .bg-gray-200 {
        background: linear-gradient(90deg, #6b7280 25%, #5a626c 50%, #6b7280 75%);
        background-size: 200px 100%;
    }
    
    /* Skeleton specific styles */
    .ticket-card-skeleton {
        min-height: 140px;
    }
    
    .dashboard-widget-skeleton {
        min-height: 320px;
    }
    
    .table-skeleton {
        min-height: 400px;
    }
    
    .form-skeleton {
        min-height: 500px;
    }
    
    .profile-skeleton {
        min-height: 280px;
    }
    
    .chat-message-skeleton {
        min-height: 300px;
    }
    
    .navigation-skeleton {
        min-height: 64px;
    }
    
    .search-results-skeleton {
        min-height: 600px;
    }
    
    .mobile-card-skeleton {
        min-height: 220px;
    }
    
    /* Responsive skeleton adjustments */
    @media (max-width: 640px) {
        .loading-skeletons .grid-cols-3 {
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        
        .ticket-card-skeleton .flex {
            flex-direction: column;
        }
        
        .ticket-card-skeleton .w-20 {
            width: 100%;
            height: 120px;
        }
    }
    
    /* Skeleton fade-in/out transitions */
    .skeleton-enter {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }
    
    .skeleton-enter-active {
        opacity: 1;
        transform: translateY(0);
    }
    
    .skeleton-exit {
        opacity: 1;
        transition: all 0.3s ease;
    }
    
    .skeleton-exit-active {
        opacity: 0;
        transform: translateY(-10px);
    }
    
    /* Performance optimizations */
    .loading-skeletons {
        contain: layout style paint;
    }
    
    .loading-skeletons * {
        will-change: auto;
    }
    
    .loading-skeletons .animate-pulse {
        will-change: background-position;
    }
</style>

<script>
// Skeleton Loading Utilities
window.SkeletonLoader = {
    // Show skeleton for a specific element
    show(element, type = 'default', count = 1) {
        const template = document.getElementById(`${type}-skeleton`);
        if (!template) {
            console.warn(`[Skeleton] Template ${type}-skeleton not found`);
            return;
        }
        
        element.innerHTML = '';
        element.classList.add('skeleton-container');
        
        for (let i = 0; i < count; i++) {
            const clone = template.content.cloneNode(true);
            clone.firstElementChild.classList.add('skeleton-enter');
            element.appendChild(clone);
            
            // Trigger animation
            setTimeout(() => {
                const skeletonEl = element.lastElementChild;
                if (skeletonEl) {
                    skeletonEl.classList.add('skeleton-enter-active');
                }
            }, i * 50); // Stagger animations
        }
    },
    
    // Hide skeleton and show content
    hide(element, content = '') {
        const skeletons = element.querySelectorAll('[class*="skeleton"]');
        
        skeletons.forEach((skeleton, index) => {
            setTimeout(() => {
                skeleton.classList.add('skeleton-exit');
                skeleton.classList.add('skeleton-exit-active');
                
                setTimeout(() => {
                    skeleton.remove();
                    
                    // Show content after last skeleton is removed
                    if (index === skeletons.length - 1) {
                        if (content) {
                            element.innerHTML = content;
                        }
                        element.classList.remove('skeleton-container');
                    }
                }, 300);
            }, index * 50);
        });
    },
    
    // Show loading skeleton for async operations
    async wrap(element, asyncOperation, skeletonType = 'default', count = 1) {
        this.show(element, skeletonType, count);
        
        try {
            const result = await asyncOperation();
            this.hide(element, result);
            return result;
        } catch (error) {
            this.hide(element, '<div class="text-red-500 text-center p-4">Failed to load content</div>');
            throw error;
        }
    },
    
    // Create skeleton for specific data types
    forTickets(element, count = 6) {
        this.show(element, 'ticket-card', count);
    },
    
    forDashboard(element) {
        this.show(element, 'dashboard-widget', 4);
    },
    
    forTable(element) {
        this.show(element, 'table', 1);
    },
    
    forForm(element) {
        this.show(element, 'form', 1);
    },
    
    forProfile(element) {
        this.show(element, 'profile', 1);
    },
    
    forChat(element) {
        this.show(element, 'chat-message', 1);
    },
    
    forNavigation(element) {
        this.show(element, 'navigation', 1);
    },
    
    forSearch(element) {
        this.show(element, 'search-results', 1);
    },
    
    forMobile(element, count = 3) {
        this.show(element, 'mobile-card', count);
    },
    
    // Smart skeleton detection based on element
    smart(element) {
        const classList = element.className;
        
        if (classList.includes('ticket') || classList.includes('event')) {
            this.forTickets(element);
        } else if (classList.includes('dashboard') || classList.includes('widget')) {
            this.forDashboard(element);
        } else if (classList.includes('table')) {
            this.forTable(element);
        } else if (classList.includes('form')) {
            this.forForm(element);
        } else if (classList.includes('profile')) {
            this.forProfile(element);
        } else if (classList.includes('chat') || classList.includes('message')) {
            this.forChat(element);
        } else if (classList.includes('nav')) {
            this.forNavigation(element);
        } else if (classList.includes('search')) {
            this.forSearch(element);
        } else {
            this.show(element, 'list-item', 5);
        }
    },
    
    // Batch operations
    showMultiple(elements, type = 'default', count = 1) {
        elements.forEach(element => {
            this.show(element, type, count);
        });
    },
    
    hideMultiple(elements) {
        elements.forEach(element => {
            this.hide(element);
        });
    }
};

// Auto-skeleton for elements with data-skeleton attribute
document.addEventListener('DOMContentLoaded', () => {
    const autoSkeletonElements = document.querySelectorAll('[data-skeleton]');
    
    autoSkeletonElements.forEach(element => {
        const skeletonType = element.dataset.skeleton;
        const skeletonCount = parseInt(element.dataset.skeletonCount) || 1;
        
        if (skeletonType === 'smart') {
            SkeletonLoader.smart(element);
        } else {
            SkeletonLoader.show(element, skeletonType, skeletonCount);
        }
    });
});

// Alpine.js integration
document.addEventListener('alpine:init', () => {
    Alpine.magic('skeleton', () => SkeletonLoader);
});
</script>