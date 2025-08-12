{{--
    Sidebar Partial - Unified Layout System
    Role-based sidebar navigation with responsive behavior
--}}
<div class="sidebar-content" x-data="sidebarManager()">
    {{-- Sidebar Header --}}
    <div class="sidebar-header mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-ticket-alt text-white text-lg"></i>
                </div>
                <div class="text-white">
                    <h3 class="font-semibold">{{ ucfirst(auth()->user()->getRoleNames()->first()) }} Panel</h3>
                    <p class="text-xs text-white/80">{{ auth()->user()->getProfileDisplay()['display_name'] }}</p>
                </div>
            </div>
            
            {{-- Desktop Collapse Toggle --}}
            <button @click="$dispatch('toggle-sidebar-collapse')" 
                    class="hidden lg:block p-2 rounded-lg text-white/80 hover:text-white hover:bg-white/10 transition-colors">
                <i class="fas fa-chevron-left w-4 h-4"></i>
            </button>
        </div>
    </div>

    {{-- Navigation Menu --}}
    <nav class="sidebar-nav space-y-2">
        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}" 
           class="sidebar-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>

        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            {{-- Tickets Section --}}
            <div class="sidebar-section">
                <div class="sidebar-section-header">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Tickets Management</span>
                </div>
                
                <div class="sidebar-section-items">
                    <a href="{{ route('tickets.scraping.index') }}" 
                       class="sidebar-nav-item {{ request()->routeIs('tickets.scraping.*') ? 'active' : '' }}">
                        <i class="fas fa-search"></i>
                        <span>Browse Tickets</span>
                        @if(request()->routeIs('tickets.scraping.*'))
                            <span class="ml-auto w-2 h-2 bg-blue-400 rounded-full"></span>
                        @endif
                    </a>

                    <a href="{{ route('tickets.alerts.index') }}" 
                       class="sidebar-nav-item {{ request()->routeIs('tickets.alerts.*') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i>
                        <span>My Alerts</span>
                        <span class="ml-auto badge badge-sm bg-red-500 text-white">3</span>
                    </a>

                    <a href="{{ route('purchase-decisions.index') }}" 
                       class="sidebar-nav-item {{ request()->routeIs('purchase-decisions.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Purchase Queue</span>
                        <span class="ml-auto badge badge-sm bg-green-500 text-white">5</span>
                    </a>

                    <a href="{{ route('ticket-sources.index') }}" 
                       class="sidebar-nav-item {{ request()->routeIs('ticket-sources.*') ? 'active' : '' }}">
                        <i class="fas fa-link"></i>
                        <span>Ticket Sources</span>
                    </a>
                </div>
            </div>

            {{-- Analytics Section --}}
            <div class="sidebar-section">
                <div class="sidebar-section-header">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </div>
                
                <div class="sidebar-section-items">
                    <a href="#" class="sidebar-nav-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Performance</span>
                    </a>

                    <a href="#" class="sidebar-nav-item">
                        <i class="fas fa-trending-up"></i>
                        <span>Price Trends</span>
                    </a>

                    <a href="#" class="sidebar-nav-item">
                        <i class="fas fa-clock"></i>
                        <span>Real-time Data</span>
                        <span class="ml-auto w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    </a>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                {{-- Admin Only Section --}}
                <div class="sidebar-section">
                    <div class="sidebar-section-header">
                        <i class="fas fa-cogs"></i>
                        <span>Administration</span>
                    </div>
                    
                    <div class="sidebar-section-items">
                        <a href="{{ route('admin.dashboard') }}" 
                           class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Admin Dashboard</span>
                        </a>

                        @if(auth()->user()->canManageUsers())
                            <a href="{{ route('admin.users.index') }}" 
                               class="sidebar-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="fas fa-users"></i>
                                <span>User Management</span>
                            </a>
                        @endif

                        <a href="{{ route('admin.reports.index') }}" 
                           class="sidebar-nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Reports</span>
                        </a>

                        <a href="{{ route('admin.categories.index') }}" 
                           class="sidebar-nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                            <i class="fas fa-tags"></i>
                            <span>Categories</span>
                        </a>

                        <a href="{{ route('admin.system.index') }}" 
                           class="sidebar-nav-item {{ request()->routeIs('admin.system.*') ? 'active' : '' }}">
                            <i class="fas fa-server"></i>
                            <span>System Settings</span>
                        </a>

                        <a href="{{ route('admin.scraping.index') }}" 
                           class="sidebar-nav-item {{ request()->routeIs('admin.scraping.*') ? 'active' : '' }}">
                            <i class="fas fa-robot"></i>
                            <span>Scraping Management</span>
                        </a>
                    </div>
                </div>
            @endif
        @endif

        {{-- Account Section --}}
        <div class="sidebar-section">
            <div class="sidebar-section-header">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </div>
            
            <div class="sidebar-section-items">
                <a href="{{ route('profile.show') }}" 
                   class="sidebar-nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-edit"></i>
                    <span>Profile Settings</span>
                    @php
                        $profileCompletion = auth()->user()->getProfileCompletion();
                    @endphp
                    @if($profileCompletion['percentage'] < 90)
                        <span class="ml-auto text-yellow-300">
                            <i class="fas fa-exclamation-triangle text-xs"></i>
                        </span>
                    @endif
                </a>

                <a href="#" class="sidebar-nav-item">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                </a>

                <a href="#" class="sidebar-nav-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Security</span>
                </a>
            </div>
        </div>
    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer mt-auto pt-6">
        {{-- Quick Stats Card --}}
        <div class="bg-white/10 rounded-lg p-4 mb-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-white">{{ now()->format('H:i') }}</div>
                <div class="text-xs text-white/80">{{ now()->format('M d, Y') }}</div>
            </div>
            
            <div class="grid grid-cols-2 gap-2 mt-3 text-center">
                <div>
                    <div class="text-lg font-semibold text-white">24</div>
                    <div class="text-xs text-white/80">Active Alerts</div>
                </div>
                <div>
                    <div class="text-lg font-semibold text-white">12</div>
                    <div class="text-xs text-white/80">Live Events</div>
                </div>
            </div>
        </div>

        {{-- Support Link --}}
        <a href="#" class="sidebar-nav-item text-center">
            <i class="fas fa-question-circle"></i>
            <span>Help & Support</span>
        </a>

        {{-- Version Info --}}
        <div class="text-center text-xs text-white/60 mt-2">
            HD Tickets v2.1.0
        </div>
    </div>
</div>

{{-- Sidebar Styles --}}
<style>
.sidebar-nav-item {
    @apply flex items-center px-4 py-3 text-sm text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 group;
}

.sidebar-nav-item.active {
    @apply bg-white/20 text-white shadow-sm;
}

.sidebar-nav-item i {
    @apply w-5 h-5 mr-3 flex-shrink-0;
}

.sidebar-section {
    @apply mb-6;
}

.sidebar-section-header {
    @apply flex items-center px-4 py-2 text-xs font-semibold text-white/60 uppercase tracking-wider mb-2;
}

.sidebar-section-header i {
    @apply w-4 h-4 mr-2;
}

.sidebar-section-items {
    @apply space-y-1;
}

.badge-sm {
    @apply inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full min-w-[1.5rem] h-6;
}

/* Collapsed sidebar styles */
.sidebar-collapsed .sidebar-nav-item span:not(.badge) {
    @apply sr-only;
}

.sidebar-collapsed .sidebar-section-header span {
    @apply sr-only;
}

.sidebar-collapsed .sidebar-footer .bg-white\/10 {
    @apply hidden;
}

/* Mobile-specific styles */
@media (max-width: 767px) {
    .sidebar-content {
        @apply px-4;
    }
}
</style>

{{-- Alpine.js Sidebar Manager --}}
<script>
    function sidebarManager() {
        return {
            collapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            
            init() {
                // Apply initial collapsed state
                this.updateCollapsedState();
                
                // Listen for collapse toggle events
                this.$el.addEventListener('toggle-sidebar-collapse', () => {
                    this.toggleCollapse();
                });
                
                // Update active states on navigation
                this.updateActiveStates();
            },
            
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebarCollapsed', this.collapsed);
                this.updateCollapsedState();
            },
            
            updateCollapsedState() {
                document.body.classList.toggle('sidebar-collapsed', this.collapsed);
            },
            
            updateActiveStates() {
                // Update active navigation states based on current route
                const currentPath = window.location.pathname;
                const navItems = this.$el.querySelectorAll('.sidebar-nav-item');
                
                navItems.forEach(item => {
                    const href = item.getAttribute('href');
                    if (href && currentPath.startsWith(href) && href !== '/') {
                        item.classList.add('active');
                    }
                });
            }
        }
    }
</script>
