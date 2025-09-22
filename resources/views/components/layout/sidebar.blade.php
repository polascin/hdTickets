@props([
    'collapsed' => false,
    'overlay' => false,
    'role' => null
])

@php
    use App\Support\Navigation;
    
    $navigation = new Navigation();
    $userRole = $role ?? ($navigation->getCurrentUserRole() ?? 'customer');
    $menuItems = $navigation->getMenuForRole($userRole);
    $defaults = $navigation->getDefaults();
    $responsive = $navigation->getResponsiveConfig();
@endphp

<aside 
    id="main-sidebar"
    class="hdt-sidebar"
    :class="{
        'hdt-sidebar--collapsed': collapsed,
        'hdt-sidebar--overlay': overlay,
        'hdt-sidebar--open': overlay && sidebarOpen
    }"
    x-data="{
        collapsed: {{ $collapsed ? 'true' : 'false' }},
        overlay: {{ $overlay ? 'true' : 'false' }},
        sidebarOpen: false,
        expandedItems: [],
        focusedIndex: 0,
        menuItems: {{ $menuItems->toJson() }},
        
        init() {
            this.setupKeyboardNavigation();
            this.setupResponsiveBehavior();
            this.restoreExpandedState();
        },
        
        toggleSidebar() {
            if (this.overlay) {
                this.sidebarOpen = !this.sidebarOpen;
                this.handleFocusManagement();
            } else {
                this.collapsed = !this.collapsed;
                this.persistCollapsedState();
            }
            
            this.$dispatch('sidebar-toggled', {
                collapsed: this.collapsed,
                open: this.sidebarOpen,
                overlay: this.overlay
            });
        },
        
        closeSidebar() {
            if (this.overlay) {
                this.sidebarOpen = false;
                this.handleFocusManagement();
            }
        },
        
        toggleSubmenu(itemId) {
            const index = this.expandedItems.indexOf(itemId);
            if (index > -1) {
                this.expandedItems.splice(index, 1);
            } else {
                this.expandedItems.push(itemId);
            }
            
            this.persistExpandedState();
            
            // Announce to screen readers
            const item = this.menuItems.find(item => item.id === itemId);
            const isExpanded = this.expandedItems.includes(itemId);
            this.announceSubmenuState(item.label, isExpanded);
        },
        
        isSubmenuExpanded(itemId) {
            return this.expandedItems.includes(itemId);
        },
        
        setupKeyboardNavigation() {
            this.$el.addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e);
            });
        },
        
        handleKeyboardNavigation(e) {
            const menuLinks = this.$el.querySelectorAll('[role=\"menuitem\"]:not([aria-hidden=\"true\"])');
            
            switch (e.key) {
                case 'Escape':
                    if (this.overlay) {
                        this.closeSidebar();
                        // Return focus to menu button
                        document.querySelector('[data-sidebar-toggle]')?.focus();
                    }
                    break;
                    
                case 'ArrowDown':
                    e.preventDefault();
                    this.focusedIndex = Math.min(this.focusedIndex + 1, menuLinks.length - 1);
                    menuLinks[this.focusedIndex]?.focus();
                    break;
                    
                case 'ArrowUp':
                    e.preventDefault();
                    this.focusedIndex = Math.max(this.focusedIndex - 1, 0);
                    menuLinks[this.focusedIndex]?.focus();
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    this.focusedIndex = 0;
                    menuLinks[0]?.focus();
                    break;
                    
                case 'End':
                    e.preventDefault();
                    this.focusedIndex = menuLinks.length - 1;
                    menuLinks[menuLinks.length - 1]?.focus();
                    break;
                    
                case 'Enter':
                case ' ':
                    const focused = document.activeElement;
                    if (focused.hasAttribute('data-submenu-toggle')) {
                        e.preventDefault();
                        const itemId = focused.getAttribute('data-item-id');
                        this.toggleSubmenu(itemId);
                    }
                    break;
            }
        },
        
        setupResponsiveBehavior() {
            const updateSidebarBehavior = () => {
                const width = window.innerWidth;
                
                if (width < {{ $responsive['mobile']['breakpoint'] ?? 768 }}) {
                    this.overlay = true;
                    this.sidebarOpen = false;
                } else if (width < {{ $responsive['tablet']['breakpoint'] ?? 1024 }}) {
                    this.overlay = {{ $responsive['tablet']['behavior'] === 'overlay' ? 'true' : 'false' }};
                    this.sidebarOpen = false;
                } else {
                    this.overlay = false;
                    this.sidebarOpen = false;
                    this.collapsed = this.getStoredCollapsedState();
                }
            };
            
            updateSidebarBehavior();
            window.addEventListener('resize', updateSidebarBehavior);
        },
        
        handleFocusManagement() {
            if (this.overlay) {
                if (this.sidebarOpen) {
                    // Focus first menu item when opening
                    this.$nextTick(() => {
                        const firstMenuItem = this.$el.querySelector('[role=\"menuitem\"]');
                        firstMenuItem?.focus();
                    });
                } else {
                    // Return focus to toggle button when closing
                    document.querySelector('[data-sidebar-toggle]')?.focus();
                }
            }
        },
        
        persistCollapsedState() {
            try {
                localStorage.setItem('hdt-sidebar-collapsed', this.collapsed);
            } catch (e) {
                console.warn('Unable to persist sidebar state');
            }
        },
        
        getStoredCollapsedState() {
            try {
                const stored = localStorage.getItem('hdt-sidebar-collapsed');
                return stored !== null ? stored === 'true' : {{ $navigation->shouldCollapseByDefault() ? 'true' : 'false' }};
            } catch (e) {
                return {{ $navigation->shouldCollapseByDefault() ? 'true' : 'false' }};
            }
        },
        
        persistExpandedState() {
            try {
                localStorage.setItem('hdt-sidebar-expanded', JSON.stringify(this.expandedItems));
            } catch (e) {
                console.warn('Unable to persist expanded state');
            }
        },
        
        restoreExpandedState() {
            try {
                const stored = localStorage.getItem('hdt-sidebar-expanded');
                if (stored) {
                    this.expandedItems = JSON.parse(stored);
                }
            } catch (e) {
                this.expandedItems = [];
            }
        },
        
        announceSubmenuState(label, isExpanded) {
            const message = `${label} submenu ${isExpanded ? 'expanded' : 'collapsed'}`;
            this.announce(message);
        },
        
        announce(message) {
            // Create temporary element for screen reader announcement
            const announcer = document.createElement('div');
            announcer.setAttribute('aria-live', 'polite');
            announcer.setAttribute('aria-atomic', 'true');
            announcer.className = 'sr-only';
            announcer.textContent = message;
            
            document.body.appendChild(announcer);
            setTimeout(() => document.body.removeChild(announcer), 1000);
        }
    }"
    @keydown.escape.window="if (overlay && sidebarOpen) closeSidebar()"
    @click.away="if (overlay) closeSidebar()"
    role="navigation"
    aria-label="Main navigation"
    {{ $attributes->except(['class', 'id', 'role', 'aria-label']) }}>

    {{-- Overlay Backdrop --}}
    <div x-show="overlay && sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="hdt-sidebar-backdrop fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
         @click="closeSidebar()"
         aria-hidden="true"></div>

    {{-- Sidebar Content --}}
    <div class="hdt-sidebar-content">
        
        {{-- Sidebar Header --}}
        <div class="hdt-sidebar-header">
            <div class="hdt-sidebar-logo">
                @if(!$collapsed)
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center space-x-3 text-text-primary hover:text-primary-600 transition-colors"
                       aria-label="HD Tickets Dashboard">
                        <svg class="w-8 h-8 text-primary-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <span class="text-xl font-bold">HD Tickets</span>
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center justify-center text-primary-600"
                       aria-label="HD Tickets Dashboard">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </a>
                @endif
            </div>

            {{-- Collapse Toggle (Desktop) --}}
            <button @click="toggleSidebar()"
                    x-show="!overlay"
                    type="button"
                    class="hdt-sidebar-toggle"
                    :aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                    :aria-expanded="!collapsed">
                <svg class="w-5 h-5 transition-transform duration-200"
                     :class="{ 'rotate-180': collapsed }"
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- Navigation Menu --}}
        <nav class="hdt-sidebar-nav" role="menu">
            @foreach($menuItems as $item)
                <div class="hdt-nav-item" data-item-id="{{ $item['id'] }}">
                    
                    {{-- Main Menu Item --}}
                    @if(empty($item['children']))
                        {{-- Simple Link --}}
                        <a href="{{ $item['url'] ?? '#' }}"
                           class="hdt-nav-link {{ $item['is_active'] ? 'hdt-nav-link--active' : '' }}"
                           role="menuitem"
                           :aria-current="'{{ $item['is_active'] ? 'page' : 'false' }}'"
                           @if($item['description'] ?? false) title="{{ $item['description'] }}" @endif>
                            
                            @if($navigation->shouldShowIcons() && isset($item['icon_svg']))
                                <span class="hdt-nav-icon" aria-hidden="true">
                                    {!! $item['icon_svg'] !!}
                                </span>
                            @endif
                            
                            <span class="hdt-nav-label" x-show="!collapsed">{{ $item['label'] }}</span>
                            
                            @if($navigation->shouldShowBadges() && !empty($item['badge_data']))
                                <x-ui.badge 
                                    :variant="$item['badge_data']['variant'] ?? 'default'"
                                    size="sm"
                                    class="hdt-nav-badge"
                                    x-show="!collapsed">
                                    {{ $item['badge_data']['value'] ?? '' }}
                                </x-ui.badge>
                            @endif
                        </a>
                        
                    @else
                        {{-- Submenu Toggle --}}
                        <button type="button"
                                class="hdt-nav-link hdt-nav-link--submenu {{ $item['is_active'] || $item['has_active_child'] ? 'hdt-nav-link--active' : '' }}"
                                @click="toggleSubmenu('{{ $item['id'] }}')"
                                :aria-expanded="isSubmenuExpanded('{{ $item['id'] }}')"
                                role="menuitem"
                                data-submenu-toggle
                                data-item-id="{{ $item['id'] }}"
                                :aria-current="'{{ $item['is_active'] ? 'page' : 'false' }}'"
                                @if($item['description'] ?? false) title="{{ $item['description'] }}" @endif>
                            
                            @if($navigation->shouldShowIcons() && isset($item['icon_svg']))
                                <span class="hdt-nav-icon" aria-hidden="true">
                                    {!! $item['icon_svg'] !!}
                                </span>
                            @endif
                            
                            <span class="hdt-nav-label" x-show="!collapsed">{{ $item['label'] }}</span>
                            
                            <div class="hdt-nav-actions" x-show="!collapsed">
                                @if($navigation->shouldShowBadges() && !empty($item['badge_data']))
                                    <x-ui.badge 
                                        :variant="$item['badge_data']['variant'] ?? 'default'"
                                        size="sm"
                                        class="hdt-nav-badge">
                                        {{ $item['badge_data']['value'] ?? '' }}
                                    </x-ui.badge>
                                @endif
                                
                                <svg class="hdt-nav-chevron w-4 h-4 transition-transform duration-200"
                                     :class="{ 'rotate-90': isSubmenuExpanded('{{ $item['id'] }}') }"
                                     fill="none" 
                                     stroke="currentColor" 
                                     viewBox="0 0 24 24"
                                     aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </button>
                        
                        {{-- Submenu --}}
                        <div class="hdt-nav-submenu"
                             x-show="isSubmenuExpanded('{{ $item['id'] }}') && !collapsed"
                             x-collapse
                             role="menu"
                             aria-labelledby="submenu-{{ $item['id'] }}">
                            @foreach($item['children'] as $child)
                                <a href="{{ $child['url'] ?? '#' }}"
                                   class="hdt-nav-sublink {{ $child['is_active'] ? 'hdt-nav-sublink--active' : '' }}"
                                   role="menuitem"
                                   :aria-current="'{{ $child['is_active'] ? 'page' : 'false' }}'"
                                   @if($child['description'] ?? false) title="{{ $child['description'] }}" @endif>
                                    <span class="hdt-nav-sublabel">{{ $child['label'] }}</span>
                                    
                                    @if($navigation->shouldShowBadges() && !empty($child['badge_data']))
                                        <x-ui.badge 
                                            :variant="$child['badge_data']['variant'] ?? 'default'"
                                            size="xs"
                                            class="hdt-nav-badge">
                                            {{ $child['badge_data']['value'] ?? '' }}
                                        </x-ui.badge>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        {{-- Sidebar Footer --}}
        <div class="hdt-sidebar-footer">
            @if(!$collapsed)
                <div class="text-xs text-text-quaternary text-center">
                    <p>&copy; {{ date('Y') }} HD Tickets</p>
                    <p>Sports Events Platform</p>
                </div>
            @endif
        </div>
    </div>
</aside>

@pushOnce('styles')
<style>
/* Sidebar Base Styles */
.hdt-sidebar {
    --hdt-sidebar-width: 280px;
    --hdt-sidebar-collapsed-width: 80px;
    --hdt-sidebar-bg: var(--hdt-color-surface-primary);
    --hdt-sidebar-border: var(--hdt-color-border-primary);
    --hdt-sidebar-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: var(--hdt-sidebar-width);
    background: var(--hdt-sidebar-bg);
    border-right: 1px solid var(--hdt-sidebar-border);
    box-shadow: var(--hdt-sidebar-shadow);
    transition: width 300ms ease, transform 300ms ease;
    z-index: 50;
    display: flex;
    flex-direction: column;
}

/* Collapsed State */
.hdt-sidebar--collapsed {
    width: var(--hdt-sidebar-collapsed-width);
}

/* Overlay State (Mobile/Tablet) */
.hdt-sidebar--overlay {
    transform: translateX(-100%);
    z-index: 50;
}

.hdt-sidebar--overlay.hdt-sidebar--open {
    transform: translateX(0);
}

/* Sidebar Content */
.hdt-sidebar-content {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
}

/* Sidebar Header */
.hdt-sidebar-header {
    flex-shrink: 0;
    padding: 1.5rem 1rem;
    border-bottom: 1px solid var(--hdt-sidebar-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.hdt-sidebar-logo a {
    text-decoration: none;
    transition: color 150ms ease;
}

.hdt-sidebar-toggle {
    padding: 0.5rem;
    border: none;
    background: none;
    color: var(--hdt-color-text-tertiary);
    cursor: pointer;
    border-radius: var(--hdt-border-radius-sm);
    transition: all 150ms ease;
}

.hdt-sidebar-toggle:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-sidebar-toggle:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Sidebar Navigation */
.hdt-sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 0;
}

.hdt-nav-item {
    margin-bottom: 0.25rem;
}

/* Navigation Links */
.hdt-nav-link {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.75rem 1rem;
    color: var(--hdt-color-text-secondary);
    text-decoration: none;
    transition: all 150ms ease;
    border: none;
    background: none;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    text-align: left;
    border-radius: 0;
    position: relative;
    min-height: 44px; /* WCAG touch target */
}

.hdt-nav-link:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-nav-link:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: -2px;
    z-index: 1;
}

.hdt-nav-link--active {
    color: var(--hdt-color-primary-700);
    background: var(--hdt-color-primary-50);
    border-right: 3px solid var(--hdt-color-primary-600);
}

.hdt-nav-link--active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 3px;
    background: var(--hdt-color-primary-600);
}

/* Navigation Icon */
.hdt-nav-icon {
    flex-shrink: 0;
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.75rem;
    color: currentColor;
}

.hdt-sidebar--collapsed .hdt-nav-icon {
    margin-right: 0;
}

/* Navigation Label */
.hdt-nav-label {
    flex: 1;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Navigation Actions */
.hdt-nav-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
}

/* Navigation Badge */
.hdt-nav-badge {
    flex-shrink: 0;
}

/* Chevron Icon */
.hdt-nav-chevron {
    flex-shrink: 0;
    color: var(--hdt-color-text-quaternary);
    transition: transform 200ms ease, color 150ms ease;
}

.hdt-nav-link:hover .hdt-nav-chevron {
    color: var(--hdt-color-text-tertiary);
}

/* Submenu */
.hdt-nav-submenu {
    background: var(--hdt-color-surface-secondary);
    border-top: 1px solid var(--hdt-color-border-secondary);
    border-bottom: 1px solid var(--hdt-color-border-secondary);
    margin-bottom: 0.25rem;
}

.hdt-nav-sublink {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.625rem 1rem 0.625rem 3rem;
    color: var(--hdt-color-text-tertiary);
    text-decoration: none;
    transition: all 150ms ease;
    min-height: 40px;
}

.hdt-nav-sublink:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-nav-sublink:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: -2px;
}

.hdt-nav-sublink--active {
    color: var(--hdt-color-primary-700);
    background: var(--hdt-color-primary-25);
    font-weight: 500;
}

.hdt-nav-sublabel {
    flex: 1;
    font-size: 0.875rem;
}

/* Sidebar Footer */
.hdt-sidebar-footer {
    flex-shrink: 0;
    padding: 1rem;
    border-top: 1px solid var(--hdt-sidebar-border);
}

/* Dark Mode Adjustments */
.hdt-theme-dark .hdt-sidebar {
    --hdt-sidebar-bg: var(--hdt-color-surface-secondary);
    --hdt-sidebar-shadow: 0 1px 3px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
}

.hdt-theme-dark .hdt-nav-link--active {
    background: var(--hdt-color-primary-900);
    color: var(--hdt-color-primary-300);
}

.hdt-theme-dark .hdt-nav-sublink--active {
    background: var(--hdt-color-primary-900);
    color: var(--hdt-color-primary-300);
}

/* Responsive Behavior */
@media (max-width: 1023px) {
    .hdt-sidebar--overlay {
        width: var(--hdt-sidebar-width);
    }
}

@media (max-width: 767px) {
    .hdt-sidebar {
        --hdt-sidebar-width: 100%;
    }
}

/* Reduced Motion */
.hdt-reduced-motion .hdt-sidebar {
    transition: none;
}

.hdt-reduced-motion .hdt-nav-chevron,
.hdt-reduced-motion .hdt-sidebar-toggle svg {
    transition: none;
}

/* High Contrast Mode */
@media (prefers-contrast: high) {
    .hdt-sidebar {
        border-right-width: 2px;
    }
    
    .hdt-nav-link--active {
        border-right-width: 4px;
    }
    
    .hdt-nav-link:focus {
        outline-width: 3px;
    }
}

/* Print Styles */
@media print {
    .hdt-sidebar {
        display: none;
    }
}

/* Screen Reader Only */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

/* Backdrop */
.hdt-sidebar-backdrop {
    backdrop-filter: blur(4px);
}

/* Tooltip for Collapsed State */
.hdt-sidebar--collapsed .hdt-nav-link[title]:hover::after {
    content: attr(title);
    position: absolute;
    left: calc(100% + 8px);
    top: 50%;
    transform: translateY(-50%);
    background: var(--hdt-color-surface-inverse);
    color: var(--hdt-color-text-inverse);
    padding: 0.5rem 0.75rem;
    border-radius: var(--hdt-border-radius-md);
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1000;
    pointer-events: none;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Role-based Theming */
.hdt-theme-organizer .hdt-nav-link--active {
    color: var(--hdt-color-organizer-700);
    background: var(--hdt-color-organizer-50);
    border-right-color: var(--hdt-color-organizer-600);
}

.hdt-theme-organizer .hdt-nav-link--active::before {
    background: var(--hdt-color-organizer-600);
}

.hdt-theme-attendee .hdt-nav-link--active {
    color: var(--hdt-color-attendee-700);
    background: var(--hdt-color-attendee-50);
    border-right-color: var(--hdt-color-attendee-600);
}

.hdt-theme-attendee .hdt-nav-link--active::before {
    background: var(--hdt-color-attendee-600);
}

.hdt-theme-vendor .hdt-nav-link--active {
    color: var(--hdt-color-vendor-700);
    background: var(--hdt-color-vendor-50);
    border-right-color: var(--hdt-color-vendor-600);
}

.hdt-theme-vendor .hdt-nav-link--active::before {
    background: var(--hdt-color-vendor-600);
}
</style>
@endPushOnce