@props([
    'categories' => [],
    'activeCategory' => null,
    'showCounts' => true,
    'position' => 'left', // left or right
    'defaultOpen' => false
])

@php
    $sidebarId = 'mobile-sidebar-' . uniqid();
    $leagueLogos = [
        'NFL' => '🏈',
        'NBA' => '🏀',
        'MLB' => '⚾',
        'NHL' => '🏒',
        'Premier League' => '⚽',
        'UEFA' => '⚽',
        'Champions League' => '⚽',
        'La Liga' => '⚽',
        'Serie A' => '⚽',
        'Bundesliga' => '⚽',
        'Tennis' => '🎾',
        'F1' => '🏎️',
        'Boxing' => '🥊',
        'MMA' => '🥊',
        'Cricket' => '🏏',
        'Golf' => '⛳',
        'Olympics' => '🏅',
        'Rugby' => '🏉'
    ];
@endphp

<div class="mobile-collapsible-sidebar" data-sidebar-id="{{ $sidebarId }}">
    <!-- Sidebar Toggle Button -->
    <button 
        type="button"
        class="sidebar-toggle fixed top-4 {{ $position === 'right' ? 'right-4' : 'left-4' }} z-40 bg-white shadow-lg rounded-full p-3 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 touch-target md:hidden"
        data-sidebar-toggle="{{ $sidebarId }}"
        aria-label="Toggle sports categories"
    >
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
        </svg>
        <div class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 rounded-full text-white text-xs flex items-center justify-center category-count-badge" style="display: none;">
            <span class="category-count">0</span>
        </div>
    </button>

    <!-- Sidebar Overlay -->
    <div 
        class="sidebar-overlay fixed inset-0 bg-black bg-opacity-50 z-30 hidden transition-opacity duration-300"
        data-sidebar-overlay="{{ $sidebarId }}"
    ></div>

    <!-- Sidebar Panel -->
    <div 
        class="sidebar-panel fixed top-0 {{ $position === 'right' ? 'right-0' : 'left-0' }} h-full w-80 max-w-xs bg-white shadow-xl z-40 transform transition-transform duration-300 {{ $position === 'right' ? 'translate-x-full' : '-translate-x-full' }} overflow-y-auto"
        data-sidebar-panel="{{ $sidebarId }}"
    >
        <!-- Sidebar Header -->
        <div class="sticky top-0 bg-white border-b border-gray-200 p-4 z-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    Sports Categories
                </h2>
                <button 
                    class="sidebar-close p-2 hover:bg-gray-100 rounded-lg touch-target" 
                    data-sidebar-close="{{ $sidebarId }}"
                    aria-label="Close sidebar"
                >
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Quick Filter Search -->
            <div class="relative">
                <input 
                    type="text" 
                    placeholder="Search categories..." 
                    class="w-full px-4 py-2 pl-10 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    data-category-search="{{ $sidebarId }}"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="p-4">
            <!-- All Categories Option -->
            <a 
                href="{{ request()->url() }}" 
                class="category-item flex items-center justify-between p-3 mb-2 rounded-lg transition-colors duration-200 {{ !$activeCategory ? 'bg-blue-50 text-blue-700 border-2 border-blue-200' : 'hover:bg-gray-50 text-gray-700' }} touch-target"
                data-category="all"
            >
                <div class="flex items-center">
                    <span class="text-2xl mr-3">🎫</span>
                    <span class="font-medium">All Sports</span>
                </div>
                @if($showCounts)
                    <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2 py-1 rounded-full">
                        {{ collect($categories)->sum('ticket_count') ?? 'All' }}
                    </span>
                @endif
            </a>

            @forelse($categories as $category)
                <a 
                    href="{{ request()->fullUrlWithQuery(['category' => $category->slug ?? $category->name]) }}" 
                    class="category-item flex items-center justify-between p-3 mb-2 rounded-lg transition-colors duration-200 {{ ($activeCategory === $category->slug || $activeCategory === $category->name) ? 'bg-blue-50 text-blue-700 border-2 border-blue-200' : 'hover:bg-gray-50 text-gray-700' }} touch-target"
                    data-category="{{ $category->slug ?? $category->name }}"
                    data-search-text="{{ strtolower($category->name) }}"
                >
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">
                            {{ $leagueLogos[$category->name] ?? '🏆' }}
                        </span>
                        <div>
                            <div class="font-medium">{{ $category->name }}</div>
                            @if(isset($category->description))
                                <div class="text-xs text-gray-500 truncate">{{ $category->description }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($showCounts && isset($category->ticket_count))
                            <span class="bg-gray-100 text-gray-600 text-xs font-medium px-2 py-1 rounded-full">
                                {{ $category->ticket_count }}
                            </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </a>
            @empty
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-2">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-500 text-sm">No categories available</p>
                </div>
            @endforelse
        </div>

        <!-- Sidebar Footer -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 p-4">
            <div class="text-xs text-gray-500 text-center">
                <p>Sports Events Monitoring</p>
                <p>{{ now()->format('M j, Y') }}</p>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* Mobile Collapsible Sidebar Styles */
.mobile-collapsible-sidebar {
    position: relative;
}

.sidebar-toggle {
    /* Enhanced touch target */
    min-width: 44px;
    min-height: 44px;
}

.sidebar-toggle:active {
    transform: scale(0.95);
}

.sidebar-panel {
    /* Smooth animations */
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    /* Safe area support */
    padding-top: max(1rem, env(safe-area-inset-top));
    padding-bottom: max(1rem, env(safe-area-inset-bottom));
}

.sidebar-panel.show {
    transform: translateX(0) !important;
}

.sidebar-overlay.show {
    opacity: 1;
}

/* Category items with improved touch targets */
.category-item {
    min-height: 48px; /* WCAG 2.1 AA compliant touch target */
    position: relative;
    overflow: hidden;
}

.category-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent 30%, rgba(59, 130, 246, 0.05) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform 0.6s ease;
}

.category-item:hover::before {
    transform: translateX(100%);
}

/* Active state animations */
.category-item.active {
    animation: slideInLeft 0.3s ease-out;
}

@keyframes slideInLeft {
    from {
        transform: translateX(-10px);
        opacity: 0.5;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* League logo hover effects */
.category-item:hover span:first-child {
    transform: scale(1.1);
    transition: transform 0.2s ease;
}

/* Search functionality */
.category-item.hidden {
    display: none;
}

/* Badge animations */
.category-count-badge {
    animation: pulse 2s infinite;
}

/* Responsive adjustments */
@media (max-width: 375px) {
    .sidebar-panel {
        width: 90vw;
        max-width: none;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .sidebar-panel {
        background: #1f2937;
        border-color: #374151;
    }
    
    .category-item {
        color: #f9fafb;
    }
    
    .category-item:hover {
        background: #374151;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .category-item {
        border: 1px solid currentColor;
    }
    
    .sidebar-toggle {
        border: 2px solid currentColor;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .sidebar-panel,
    .sidebar-overlay,
    .category-item,
    .category-item::before {
        transition: none !important;
        animation: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarId = '{{ $sidebarId }}';
    const toggleButton = document.querySelector(`[data-sidebar-toggle="${sidebarId}"]`);
    const overlay = document.querySelector(`[data-sidebar-overlay="${sidebarId}"]`);
    const panel = document.querySelector(`[data-sidebar-panel="${sidebarId}"]`);
    const closeButton = document.querySelector(`[data-sidebar-close="${sidebarId}"]`);
    const searchInput = document.querySelector(`[data-category-search="${sidebarId}"]`);

    if (!toggleButton || !overlay || !panel) return;

    // Toggle sidebar
    function toggleSidebar() {
        const isOpen = panel.classList.contains('show');
        
        if (isOpen) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    // Open sidebar
    function openSidebar() {
        panel.classList.add('show');
        overlay.classList.remove('hidden');
        overlay.classList.add('show');
        document.body.classList.add('overflow-hidden');
        
        // Focus management for accessibility
        const firstFocusableElement = panel.querySelector('button, input, a');
        if (firstFocusableElement) {
            setTimeout(() => firstFocusableElement.focus(), 300);
        }
        
        // Dispatch event
        window.dispatchEvent(new CustomEvent('sidebar:open', { 
            detail: { sidebarId } 
        }));
    }

    // Close sidebar
    function closeSidebar() {
        panel.classList.remove('show');
        overlay.classList.remove('show');
        setTimeout(() => {
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 300);
        
        // Return focus to toggle button
        toggleButton.focus();
        
        // Dispatch event
        window.dispatchEvent(new CustomEvent('sidebar:close', { 
            detail: { sidebarId } 
        }));
    }

    // Event listeners
    toggleButton?.addEventListener('click', toggleSidebar);
    closeButton?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Keyboard navigation
    panel?.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Swipe to close (mobile)
    if (window.mobileUtils?.enableSwipeGestures) {
        window.mobileUtils.enableSwipeGestures(panel, {
            swipeRight: function() {
                if ('{{ $position }}' === 'left') {
                    closeSidebar();
                }
            },
            swipeLeft: function() {
                if ('{{ $position }}' === 'right') {
                    closeSidebar();
                }
            }
        });
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const categoryItems = panel.querySelectorAll('.category-item[data-search-text]');
            
            categoryItems.forEach(item => {
                const searchText = item.getAttribute('data-search-text');
                const isMatch = !searchTerm || searchText.includes(searchTerm);
                
                if (isMatch) {
                    item.classList.remove('hidden');
                    item.style.display = '';
                } else {
                    item.classList.add('hidden');
                    item.style.display = 'none';
                }
            });
            
            // Show "All Sports" if search is empty
            const allSportsItem = panel.querySelector('.category-item[data-category="all"]');
            if (allSportsItem) {
                if (searchTerm) {
                    allSportsItem.style.display = 'none';
                } else {
                    allSportsItem.style.display = '';
                }
            }
        });
    }

    // Update category count badge
    function updateCategoryCount() {
        const categoryCount = {{ count($categories) }};
        const badge = toggleButton.querySelector('.category-count-badge');
        const countSpan = badge?.querySelector('.category-count');
        
        if (categoryCount > 0 && badge && countSpan) {
            countSpan.textContent = categoryCount;
            badge.style.display = 'flex';
        }
    }

    updateCategoryCount();

    // Handle orientation changes
    window.addEventListener('orientationchange', function() {
        setTimeout(() => {
            if (panel.classList.contains('show')) {
                // Recalculate dimensions after orientation change
                panel.style.height = window.innerHeight + 'px';
            }
        }, 100);
    });

    // Auto-open on desktop if specified
    @if($defaultOpen)
        if (window.innerWidth >= 1024) {
            openSidebar();
        }
    @endif
});
</script>
@endpush
