@props([
    'title' => null,
    'showSearch' => true,
    'showNotifications' => true,
    'user' => null
])

@php
    use App\Support\Navigation;
    
    $navigation = new Navigation();
    $currentUser = $user ?? auth()->user();
    $userMenuItems = $navigation->getUserMenuItems($currentUser);
    $quickActions = $navigation->getQuickActions($currentUser);
    $isAdmin = $currentUser?->hasRole('admin') ?? false;
@endphp

<header 
    id="main-header"
    class="hdt-header"
    x-data="{
        userMenuOpen: false,
        notificationsOpen: false,
        searchOpen: false,
        searchQuery: '',
        notifications: [],
        unreadCount: 0,
        
        init() {
            this.setupKeyboardNavigation();
            this.loadNotifications();
            this.setupSearchShortcut();
        },
        
        toggleUserMenu() {
            this.userMenuOpen = !this.userMenuOpen;
            this.notificationsOpen = false;
            this.searchOpen = false;
            
            if (this.userMenuOpen) {
                this.$nextTick(() => {
                    this.$refs.userMenuFirstItem?.focus();
                });
            }
        },
        
        toggleNotifications() {
            this.notificationsOpen = !this.notificationsOpen;
            this.userMenuOpen = false;
            this.searchOpen = false;
            
            if (this.notificationsOpen) {
                this.markNotificationsAsRead();
                this.$nextTick(() => {
                    this.$refs.notificationsPanel?.focus();
                });
            }
        },
        
        toggleSearch() {
            this.searchOpen = !this.searchOpen;
            this.userMenuOpen = false;
            this.notificationsOpen = false;
            
            if (this.searchOpen) {
                this.$nextTick(() => {
                    this.$refs.searchInput?.focus();
                });
            }
        },
        
        closeAllMenus() {
            this.userMenuOpen = false;
            this.notificationsOpen = false;
            this.searchOpen = false;
        },
        
        setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                // Escape to close all menus
                if (e.key === 'Escape') {
                    this.closeAllMenus();
                }
                
                // Cmd/Ctrl + K to open search
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.toggleSearch();
                }
            });
        },
        
        setupSearchShortcut() {
            // Listen for forward slash to focus search
            document.addEventListener('keydown', (e) => {
                if (e.key === '/' && !this.isInputFocused()) {
                    e.preventDefault();
                    this.toggleSearch();
                }
            });
        },
        
        isInputFocused() {
            const activeElement = document.activeElement;
            return activeElement && (
                activeElement.tagName === 'INPUT' ||
                activeElement.tagName === 'TEXTAREA' ||
                activeElement.contentEditable === 'true'
            );
        },
        
        handleSearch() {
            if (this.searchQuery.trim()) {
                // Dispatch search event for handling by parent components
                this.$dispatch('search-submitted', {
                    query: this.searchQuery,
                    type: 'global'
                });
                
                // For now, redirect to search page
                window.location.href = `/search?q=${encodeURIComponent(this.searchQuery)}`;
            }
        },
        
        loadNotifications() {
            // In a real app, this would fetch from an API
            this.notifications = [
                {
                    id: 1,
                    title: 'New event available',
                    message: 'Champions League Final tickets are now available',
                    time: '2 minutes ago',
                    read: false,
                    type: 'event',
                    url: '/events/champions-league-final'
                },
                {
                    id: 2,
                    title: 'Price alert',
                    message: 'Premier League tickets price dropped by 15%',
                    time: '1 hour ago',
                    read: false,
                    type: 'price',
                    url: '/events/premier-league'
                },
                {
                    id: 3,
                    title: 'Watchlist update',
                    message: 'NBA Finals Game 7 tickets added to inventory',
                    time: '3 hours ago',
                    read: true,
                    type: 'watchlist',
                    url: '/watchlist'
                }
            ];
            
            this.unreadCount = this.notifications.filter(n => !n.read).length;
        },
        
        markNotificationsAsRead() {
            this.notifications = this.notifications.map(n => ({ ...n, read: true }));
            this.unreadCount = 0;
            
            // In a real app, this would make an API call
            // fetch('/api/notifications/mark-read', { method: 'POST' });
        },
        
        handleLogout() {
            if (confirm('Are you sure you want to logout?')) {
                // Create and submit logout form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('logout') }}';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                
                form.appendChild(csrfToken);
                document.body.appendChild(form);
                form.submit();
            }
        }
    }"
    @click.away="closeAllMenus()"
    role="banner"
    {{ $attributes->except(['class', 'id', 'role']) }}>

    <div class="hdt-header-content">
        
        {{-- Mobile Menu Toggle --}}
        <button type="button"
                class="hdt-mobile-menu-toggle lg:hidden"
                @click="$dispatch('sidebar-toggle')"
                data-sidebar-toggle
                aria-label="Toggle navigation menu"
                aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Page Title --}}
        @if($title)
            <div class="hdt-header-title">
                <h1 class="text-lg font-semibold text-text-primary">{{ $title }}</h1>
            </div>
        @endif

        {{-- Spacer --}}
        <div class="flex-1"></div>

        {{-- Header Actions --}}
        <div class="hdt-header-actions">
            
            {{-- Search --}}
            @if($showSearch)
                <div class="hdt-search-container" :class="{ 'hdt-search-container--open': searchOpen }">
                    {{-- Search Toggle (Mobile) --}}
                    <button type="button"
                            class="hdt-search-toggle md:hidden"
                            @click="toggleSearch()"
                            :aria-expanded="searchOpen"
                            aria-label="Toggle search">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>

                    {{-- Search Input (Desktop Always Visible, Mobile Toggleable) --}}
                    <div class="hdt-search-input-container" 
                         :class="{ 'hdt-search-input-container--open': searchOpen }"
                         x-show="searchOpen || window.innerWidth >= 768">
                        <form @submit.prevent="handleSearch()" class="relative">
                            <input type="text"
                                   x-ref="searchInput"
                                   x-model="searchQuery"
                                   placeholder="Search events, venues... (/ or ⌘K)"
                                   class="hdt-search-input"
                                   autocomplete="off"
                                   aria-label="Search events and venues">
                            
                            <div class="hdt-search-icon">
                                <svg class="w-4 h-4 text-text-quaternary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            {{-- Search Shortcut Hint --}}
                            <div class="hdt-search-shortcut hidden md:flex">
                                <kbd class="text-xs">⌘K</kbd>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Quick Actions (Admin) --}}
            @if($isAdmin && !empty($quickActions))
                <div class="hdt-quick-actions hidden lg:flex">
                    @foreach($quickActions as $action)
                        <a href="{{ $action['url'] }}"
                           class="hdt-quick-action"
                           title="{{ $action['description'] ?? $action['label'] }}"
                           @if($action['external'] ?? false) target="_blank" rel="noopener" @endif>
                            @if(isset($action['icon_svg']))
                                <span class="w-5 h-5" aria-hidden="true">
                                    {!! $action['icon_svg'] !!}
                                </span>
                            @endif
                            <span class="sr-only">{{ $action['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Notifications --}}
            @if($showNotifications && $currentUser)
                <div class="hdt-notifications" x-data="{ hover: false }">
                    <button type="button"
                            class="hdt-notifications-toggle"
                            @click="toggleNotifications()"
                            @mouseenter="hover = true"
                            @mouseleave="hover = false"
                            :aria-expanded="notificationsOpen"
                            aria-label="View notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        
                        {{-- Notification Badge --}}
                        <span x-show="unreadCount > 0" 
                              x-text="unreadCount"
                              class="hdt-notifications-badge"
                              :class="{ 'animate-pulse': hover && unreadCount > 0 }"
                              aria-label="unread notifications">
                        </span>
                    </button>

                    {{-- Notifications Dropdown --}}
                    <div x-show="notificationsOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="hdt-notifications-dropdown"
                         x-ref="notificationsPanel"
                         tabindex="-1">
                        
                        <div class="hdt-notifications-header">
                            <h3 class="text-sm font-semibold text-text-primary">Notifications</h3>
                            <button @click="markNotificationsAsRead()"
                                    class="text-xs text-primary-600 hover:text-primary-700"
                                    x-show="unreadCount > 0">
                                Mark all read
                            </button>
                        </div>

                        <div class="hdt-notifications-list">
                            <template x-for="notification in notifications" :key="notification.id">
                                <a :href="notification.url"
                                   class="hdt-notification-item"
                                   :class="{ 'hdt-notification-item--unread': !notification.read }"
                                   @click="closeAllMenus()">
                                    <div class="hdt-notification-content">
                                        <h4 class="hdt-notification-title" x-text="notification.title"></h4>
                                        <p class="hdt-notification-message" x-text="notification.message"></p>
                                        <time class="hdt-notification-time" x-text="notification.time"></time>
                                    </div>
                                    <div class="hdt-notification-indicator" x-show="!notification.read"></div>
                                </a>
                            </template>
                            
                            <div x-show="notifications.length === 0" 
                                 class="hdt-notifications-empty">
                                <svg class="w-8 h-8 text-text-quaternary mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-sm text-text-tertiary mt-2">No notifications</p>
                            </div>
                        </div>

                        <div class="hdt-notifications-footer">
                            <a href="{{ route('notifications.index') }}"
                               class="text-sm text-primary-600 hover:text-primary-700"
                               @click="closeAllMenus()">
                                View all notifications
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- User Menu --}}
            @if($currentUser)
                <div class="hdt-user-menu">
                    <button type="button"
                            class="hdt-user-menu-toggle"
                            @click="toggleUserMenu()"
                            :aria-expanded="userMenuOpen"
                            aria-label="User menu">
                        
                        {{-- User Avatar --}}
                        @if($currentUser->avatar)
                            <img src="{{ $currentUser->avatar }}" 
                                 alt="{{ $currentUser->name }}"
                                 class="hdt-user-avatar">
                        @else
                            <div class="hdt-user-avatar hdt-user-avatar--initials">
                                {{ strtoupper(substr($currentUser->name, 0, 2)) }}
                            </div>
                        @endif
                        
                        {{-- Chevron --}}
                        <svg class="hdt-user-menu-chevron w-4 h-4 transition-transform duration-200"
                             :class="{ 'rotate-180': userMenuOpen }"
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- User Menu Dropdown --}}
                    <div x-show="userMenuOpen"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="hdt-user-menu-dropdown"
                         role="menu"
                         aria-labelledby="user-menu">

                        {{-- User Info --}}
                        <div class="hdt-user-info">
                            <div class="font-medium text-text-primary">{{ $currentUser->name }}</div>
                            <div class="text-sm text-text-tertiary">{{ $currentUser->email }}</div>
                            @if($currentUser->hasRole('admin'))
                                <x-ui.badge variant="primary" size="xs" class="mt-1">Admin</x-ui.badge>
                            @endif
                        </div>

                        <hr class="hdt-user-menu-divider">

                        {{-- User Menu Items --}}
                        @foreach($userMenuItems as $item)
                            @if($item['type'] === 'divider')
                                <hr class="hdt-user-menu-divider">
                            @elseif($item['type'] === 'action')
                                <button type="button"
                                        x-ref="{{ $loop->first ? 'userMenuFirstItem' : '' }}"
                                        class="hdt-user-menu-item hdt-user-menu-item--action"
                                        @click="{{ $item['action'] }}(); closeAllMenus();"
                                        role="menuitem">
                                    @if(isset($item['icon_svg']))
                                        <span class="hdt-user-menu-icon" aria-hidden="true">
                                            {!! $item['icon_svg'] !!}
                                        </span>
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                </button>
                            @else
                                <a href="{{ $item['url'] ?? '#' }}"
                                   x-ref="{{ $loop->first ? 'userMenuFirstItem' : '' }}"
                                   class="hdt-user-menu-item"
                                   @click="closeAllMenus()"
                                   role="menuitem"
                                   @if($item['external'] ?? false) target="_blank" rel="noopener" @endif>
                                    @if(isset($item['icon_svg']))
                                        <span class="hdt-user-menu-icon" aria-hidden="true">
                                            {!! $item['icon_svg'] !!}
                                        </span>
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                    @if($item['external'] ?? false)
                                        <svg class="w-3 h-3 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    @endif
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Guest Actions --}}
                <div class="hdt-guest-actions">
                    <a href="{{ route('login') }}" 
                       class="hdt-header-button hdt-header-button--secondary">
                        Login
                    </a>
                    <a href="{{ route('register') }}" 
                       class="hdt-header-button hdt-header-button--primary">
                        Sign Up
                    </a>
                </div>
            @endif
        </div>
    </div>
</header>

@pushOnce('styles')
<style>
/* Header Base Styles */
.hdt-header {
    --hdt-header-height: 4rem;
    --hdt-header-bg: var(--hdt-color-surface-primary);
    --hdt-header-border: var(--hdt-color-border-primary);
    --hdt-header-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
    
    position: fixed;
    top: 0;
    right: 0;
    left: 0;
    height: var(--hdt-header-height);
    background: var(--hdt-header-bg);
    border-bottom: 1px solid var(--hdt-header-border);
    box-shadow: var(--hdt-header-shadow);
    z-index: 40;
    transition: margin-left 300ms ease;
}

/* Header Content */
.hdt-header-content {
    display: flex;
    align-items: center;
    height: 100%;
    padding: 0 1rem;
    max-width: none;
}

@media (min-width: 1024px) {
    .hdt-header-content {
        padding: 0 1.5rem;
    }
}

/* Mobile Menu Toggle */
.hdt-mobile-menu-toggle {
    padding: 0.5rem;
    border: none;
    background: none;
    color: var(--hdt-color-text-secondary);
    cursor: pointer;
    border-radius: var(--hdt-border-radius-sm);
    transition: all 150ms ease;
    margin-right: 1rem;
}

.hdt-mobile-menu-toggle:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-mobile-menu-toggle:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Header Title */
.hdt-header-title {
    margin-right: 1rem;
}

/* Header Actions */
.hdt-header-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: auto;
}

/* Search Styles */
.hdt-search-container {
    position: relative;
    display: flex;
    align-items: center;
}

.hdt-search-toggle {
    padding: 0.5rem;
    border: none;
    background: none;
    color: var(--hdt-color-text-secondary);
    cursor: pointer;
    border-radius: var(--hdt-border-radius-sm);
    transition: all 150ms ease;
}

.hdt-search-toggle:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-search-toggle:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-search-input-container {
    position: relative;
    width: 320px;
    max-width: 100%;
}

@media (max-width: 767px) {
    .hdt-search-input-container {
        position: absolute;
        top: 100%;
        right: 0;
        left: 0;
        background: var(--hdt-header-bg);
        border-bottom: 1px solid var(--hdt-header-border);
        padding: 1rem;
        box-shadow: var(--hdt-header-shadow);
        transform: translateY(-10px);
        opacity: 0;
        pointer-events: none;
        transition: all 200ms ease;
    }
    
    .hdt-search-input-container--open {
        transform: translateY(0);
        opacity: 1;
        pointer-events: all;
    }
}

.hdt-search-input {
    width: 100%;
    padding: 0.5rem 2.5rem 0.5rem 0.75rem;
    background: var(--hdt-color-surface-secondary);
    border: 1px solid var(--hdt-color-border-secondary);
    border-radius: var(--hdt-border-radius-md);
    color: var(--hdt-color-text-primary);
    font-size: 0.875rem;
    transition: all 150ms ease;
}

.hdt-search-input:focus {
    outline: none;
    border-color: var(--hdt-color-primary-500);
    box-shadow: 0 0 0 3px var(--hdt-color-primary-100);
    background: var(--hdt-color-surface-primary);
}

.hdt-search-input::placeholder {
    color: var(--hdt-color-text-quaternary);
}

.hdt-search-icon {
    position: absolute;
    right: 2.5rem;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

.hdt-search-shortcut {
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
    align-items: center;
    gap: 0.25rem;
    pointer-events: none;
}

.hdt-search-shortcut kbd {
    padding: 0.125rem 0.375rem;
    background: var(--hdt-color-surface-tertiary);
    border: 1px solid var(--hdt-color-border-secondary);
    border-radius: var(--hdt-border-radius-sm);
    color: var(--hdt-color-text-tertiary);
    font-family: var(--hdt-font-mono);
}

/* Quick Actions */
.hdt-quick-actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    margin-right: 0.5rem;
}

.hdt-quick-action {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    color: var(--hdt-color-text-secondary);
    border-radius: var(--hdt-border-radius-sm);
    transition: all 150ms ease;
    text-decoration: none;
}

.hdt-quick-action:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-quick-action:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Notifications */
.hdt-notifications {
    position: relative;
}

.hdt-notifications-toggle {
    position: relative;
    padding: 0.5rem;
    border: none;
    background: none;
    color: var(--hdt-color-text-secondary);
    cursor: pointer;
    border-radius: var(--hdt-border-radius-sm);
    transition: all 150ms ease;
}

.hdt-notifications-toggle:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
}

.hdt-notifications-toggle:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-notifications-badge {
    position: absolute;
    top: 0.25rem;
    right: 0.25rem;
    min-width: 1rem;
    height: 1rem;
    padding: 0 0.25rem;
    background: var(--hdt-color-danger-500);
    color: white;
    border-radius: 9999px;
    font-size: 0.625rem;
    font-weight: 600;
    line-height: 1rem;
    text-align: center;
    border: 2px solid var(--hdt-header-bg);
}

.hdt-notifications-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 24rem;
    background: var(--hdt-color-surface-primary);
    border: 1px solid var(--hdt-color-border-primary);
    border-radius: var(--hdt-border-radius-lg);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 50;
}

.hdt-notifications-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem;
    border-bottom: 1px solid var(--hdt-color-border-secondary);
}

.hdt-notifications-list {
    max-height: 24rem;
    overflow-y: auto;
}

.hdt-notification-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border-bottom: 1px solid var(--hdt-color-border-tertiary);
    text-decoration: none;
    transition: background-color 150ms ease;
}

.hdt-notification-item:hover {
    background: var(--hdt-color-surface-secondary);
}

.hdt-notification-item--unread {
    background: var(--hdt-color-primary-25);
}

.hdt-notification-content {
    flex: 1;
    min-width: 0;
}

.hdt-notification-title {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--hdt-color-text-primary);
    margin-bottom: 0.25rem;
}

.hdt-notification-message {
    font-size: 0.75rem;
    color: var(--hdt-color-text-secondary);
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.hdt-notification-time {
    font-size: 0.625rem;
    color: var(--hdt-color-text-quaternary);
}

.hdt-notification-indicator {
    width: 0.5rem;
    height: 0.5rem;
    background: var(--hdt-color-primary-500);
    border-radius: 50%;
    margin-left: 0.75rem;
    margin-top: 0.25rem;
    flex-shrink: 0;
}

.hdt-notifications-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 2rem;
}

.hdt-notifications-footer {
    padding: 0.75rem 1rem;
    border-top: 1px solid var(--hdt-color-border-secondary);
    text-align: center;
}

/* User Menu */
.hdt-user-menu {
    position: relative;
}

.hdt-user-menu-toggle {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: var(--hdt-border-radius-md);
    transition: all 150ms ease;
}

.hdt-user-menu-toggle:hover {
    background: var(--hdt-color-surface-tertiary);
}

.hdt-user-menu-toggle:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

.hdt-user-avatar {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    object-fit: cover;
}

.hdt-user-avatar--initials {
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--hdt-color-primary-500);
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
}

.hdt-user-menu-chevron {
    color: var(--hdt-color-text-tertiary);
}

.hdt-user-menu-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    width: 16rem;
    background: var(--hdt-color-surface-primary);
    border: 1px solid var(--hdt-color-border-primary);
    border-radius: var(--hdt-border-radius-lg);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    z-index: 50;
    padding: 0.5rem 0;
}

.hdt-user-info {
    padding: 0.75rem 1rem;
}

.hdt-user-menu-divider {
    margin: 0.5rem 0;
    border: none;
    border-top: 1px solid var(--hdt-color-border-secondary);
}

.hdt-user-menu-item {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 0.5rem 1rem;
    color: var(--hdt-color-text-secondary);
    text-decoration: none;
    transition: all 150ms ease;
    border: none;
    background: none;
    cursor: pointer;
    font-family: inherit;
    font-size: 0.875rem;
    text-align: left;
}

.hdt-user-menu-item:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-secondary);
}

.hdt-user-menu-item:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: -2px;
}

.hdt-user-menu-item--action {
    border: none;
    background: none;
}

.hdt-user-menu-icon {
    width: 1rem;
    height: 1rem;
    margin-right: 0.75rem;
    color: currentColor;
}

/* Guest Actions */
.hdt-guest-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.hdt-header-button {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--hdt-border-radius-md);
    text-decoration: none;
    transition: all 150ms ease;
    border: 1px solid transparent;
}

.hdt-header-button--secondary {
    color: var(--hdt-color-text-secondary);
    border-color: var(--hdt-color-border-secondary);
}

.hdt-header-button--secondary:hover {
    color: var(--hdt-color-text-primary);
    background: var(--hdt-color-surface-tertiary);
    border-color: var(--hdt-color-border-primary);
}

.hdt-header-button--primary {
    color: white;
    background: var(--hdt-color-primary-600);
    border-color: var(--hdt-color-primary-600);
}

.hdt-header-button--primary:hover {
    background: var(--hdt-color-primary-700);
    border-color: var(--hdt-color-primary-700);
}

.hdt-header-button:focus {
    outline: 2px solid var(--hdt-color-focus-ring);
    outline-offset: 2px;
}

/* Dark Mode */
.hdt-theme-dark .hdt-header {
    --hdt-header-bg: var(--hdt-color-surface-secondary);
    --hdt-header-shadow: 0 1px 3px rgba(0, 0, 0, 0.3), 0 1px 2px rgba(0, 0, 0, 0.2);
}

.hdt-theme-dark .hdt-search-input {
    background: var(--hdt-color-surface-tertiary);
}

.hdt-theme-dark .hdt-search-input:focus {
    background: var(--hdt-color-surface-primary);
}

/* Responsive */
@media (max-width: 640px) {
    .hdt-header-actions {
        gap: 0.25rem;
    }
    
    .hdt-notifications-dropdown,
    .hdt-user-menu-dropdown {
        width: 20rem;
        left: auto;
        right: 0;
    }
}

/* High Contrast */
@media (prefers-contrast: high) {
    .hdt-header {
        border-bottom-width: 2px;
    }
    
    .hdt-user-menu-toggle:focus,
    .hdt-notifications-toggle:focus,
    .hdt-search-toggle:focus {
        outline-width: 3px;
    }
}

/* Print */
@media print {
    .hdt-header {
        position: static;
        box-shadow: none;
        border-bottom: 2px solid #000;
    }
    
    .hdt-header-actions {
        display: none;
    }
}

/* Reduced Motion */
.hdt-reduced-motion .hdt-header,
.hdt-reduced-motion .hdt-user-menu-chevron,
.hdt-reduced-motion .hdt-notifications-toggle,
.hdt-reduced-motion .hdt-search-input-container {
    transition: none;
}

/* Sidebar Integration */
@media (min-width: 1024px) {
    .hdt-layout--sidebar .hdt-header {
        margin-left: 280px; /* Match sidebar width */
    }
    
    .hdt-layout--sidebar.hdt-layout--sidebar-collapsed .hdt-header {
        margin-left: 80px; /* Match collapsed sidebar width */
    }
}
</style>
@endPushOnce