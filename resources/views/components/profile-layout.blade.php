@props([
    'title' => 'Profile',
    'user' => null,
    'activeSection' => 'overview',
    'showSidebar' => true,
    'sidebarCollapsed' => false,
    'breadcrumbs' => [],
    'headerActions' => null,
    'mobileFriendly' => true,
    'showBackButton' => false,
    'profileSections' => []
])

@php
    $user = $user ?? Auth::user();
    $profileDisplay = $user->getProfileDisplay();
    
    // Default profile sections if not provided
    $defaultSections = [
        'overview' => ['icon' => 'user', 'label' => 'Overview', 'route' => 'profile.show'],
        'personal' => ['icon' => 'id-card', 'label' => 'Personal Info', 'route' => 'profile.edit'],
        'security' => ['icon' => 'shield-check', 'label' => 'Security', 'route' => 'profile.security'],
        'preferences' => ['icon' => 'cog', 'label' => 'Preferences', 'route' => 'preferences.index'],
        'activity' => ['icon' => 'chart-bar', 'label' => 'Activity Dashboard', 'route' => 'profile.activity.dashboard'],
    ];
    
    $sections = !empty($profileSections) ? $profileSections : $defaultSections;
@endphp

<div class="profile-layout min-h-screen bg-gray-50" 
     x-data="{ 
         sidebarOpen: {{ $sidebarCollapsed ? 'false' : 'true' }}, 
         isMobile: window.innerWidth < 768,
         activeSection: '{{ $activeSection }}'
     }"
     x-init="
         $watch('sidebarOpen', value => localStorage.setItem('profile-sidebar-collapsed', !value));
         window.addEventListener('resize', () => {
             isMobile = window.innerWidth < 768;
             if (!isMobile && !sidebarOpen) sidebarOpen = true;
         });
     ">

    <!-- Mobile Header -->
    @if($mobileFriendly)
        <div class="profile-mobile-header lg:hidden bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
            <div class="flex items-center justify-between px-4 py-3 h-16">
                <div class="flex items-center space-x-3">
                    @if($showBackButton)
                        <button onclick="window.history.back()" 
                                class="touch-target p-2 -ml-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @endif
                    
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="touch-target p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                        </svg>
                    </button>
                    
                    <h1 class="text-lg font-semibold text-gray-900">{{ $title }}</h1>
                </div>
                
                <div class="flex items-center space-x-2">
                    @if($headerActions)
                        {{ $headerActions }}
                    @endif
                    
                    <div class="profile-avatar">
                        @if($profileDisplay['has_picture'])
                            <img class="w-8 h-8 rounded-full object-cover border-2 border-gray-200" 
                                 src="{{ $profileDisplay['picture_url'] }}" 
                                 alt="{{ $profileDisplay['display_name'] }}">
                        @else
                            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-white">
                                    {{ $profileDisplay['initials'] }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen && isMobile" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 lg:hidden">
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="sidebarOpen = false"></div>
    </div>

    <!-- Main Layout Grid -->
    <div class="profile-grid {{ $mobileFriendly ? 'lg:grid' : 'grid' }} grid-cols-1 lg:grid-cols-4 xl:grid-cols-5 min-h-screen">
        
        <!-- Profile Sidebar -->
        @if($showSidebar)
            <aside class="profile-sidebar bg-white shadow-lg lg:shadow-sm border-r border-gray-200 lg:col-span-1 xl:col-span-1
                         {{ $mobileFriendly ? 'fixed inset-y-0 left-0 z-50 w-64 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:w-auto lg:transform-none' : '' }}"
                   x-bind:class="{ 
                       '-translate-x-full': isMobile && !sidebarOpen,
                       'translate-x-0': isMobile && sidebarOpen 
                   }">
                
                <!-- Sidebar Header -->
                <div class="sidebar-header p-6 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="profile-avatar-large">
                                @if($profileDisplay['has_picture'])
                                    <img class="w-16 h-16 rounded-full object-cover border-4 border-white shadow-lg" 
                                         src="{{ $profileDisplay['picture_url'] }}" 
                                         alt="{{ $profileDisplay['display_name'] }}">
                                @else
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                        <span class="text-2xl font-bold text-white">
                                            {{ $profileDisplay['initials'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="hidden lg:block">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $profileDisplay['display_name'] }}</h3>
                                <p class="text-sm text-gray-600">{{ $user->email }}</p>
                                @if($user->role)
                                    <span class="inline-block mt-1 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        @if($mobileFriendly)
                            <button @click="sidebarOpen = false" 
                                    class="lg:hidden p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                    
                    <!-- Mobile User Info -->
                    <div class="lg:hidden mt-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $profileDisplay['display_name'] }}</h3>
                        <p class="text-sm text-gray-600">{{ $user->email }}</p>
                        @if($user->role)
                            <span class="inline-block mt-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                {{ ucfirst($user->role) }}
                            </span>
                        @endif
                    </div>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="sidebar-nav p-4 space-y-2 flex-1 overflow-y-auto">
                    @foreach($sections as $key => $section)
                        <a href="{{ route($section['route']) }}" 
                           class="nav-item flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-all duration-200 group
                                  {{ $activeSection === $key ? 'bg-blue-50 text-blue-700 border-r-4 border-blue-500' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
                           @click="activeSection = '{{ $key }}'; if(isMobile) sidebarOpen = false">
                            <x-profile-icon name="{{ $section['icon'] }}" 
                                           class="w-5 h-5 mr-3 {{ $activeSection === $key ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-600' }}" />
                            {{ $section['label'] }}
                        </a>
                    @endforeach
                </nav>
                
                <!-- Sidebar Footer -->
                <div class="sidebar-footer p-4 border-t border-gray-200">
                    <div class="text-xs text-gray-500 text-center">
                        <p>HD Tickets Profile</p>
                        <p>{{ now()->format('M j, Y') }}</p>
                    </div>
                </div>
            </aside>
        @endif
        
        <!-- Main Content Area -->
        <main class="profile-content lg:col-span-3 xl:col-span-4 {{ $mobileFriendly ? 'pt-16 lg:pt-0' : '' }}">
            <!-- Breadcrumbs -->
            @if(!empty($breadcrumbs))
                <div class="breadcrumb-container bg-white border-b border-gray-200 px-6 py-3">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2">
                            @foreach($breadcrumbs as $index => $breadcrumb)
                                <li class="flex items-center">
                                    @if($index > 0)
                                        <svg class="flex-shrink-0 h-4 w-4 text-gray-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                    @if(isset($breadcrumb['url']) && !$loop->last)
                                        <a href="{{ $breadcrumb['url'] }}" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                                            {{ $breadcrumb['label'] }}
                                        </a>
                                    @else
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $breadcrumb['label'] }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>
            @endif
            
            <!-- Page Header -->
            <div class="page-header bg-white border-b border-gray-200 px-6 py-6 {{ $showSidebar ? 'hidden lg:block' : '' }}">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
                        @if(isset($subtitle))
                            <p class="mt-1 text-sm text-gray-600">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($headerActions)
                        <div class="flex items-center space-x-3">
                            {{ $headerActions }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="content-wrapper p-6 space-y-6">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

<!-- Profile Layout Styles -->
@push('styles')
<link href="{{ asset('css/profile.css') }}?v={{ time() }}" rel="stylesheet">
@endpush

<!-- Profile Layout Scripts -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced profile layout functionality
    const profileLayout = document.querySelector('.profile-layout');
    if (!profileLayout) return;
    
    // Handle responsive behavior
    function handleResize() {
        const isMobile = window.innerWidth < 768;
        const sidebarState = Alpine.store('sidebar') || Alpine.$data(profileLayout);
        
        if (sidebarState && isMobile && sidebarState.sidebarOpen) {
            sidebarState.sidebarOpen = false;
        }
    }
    
    window.addEventListener('resize', handleResize);
    
    // Smooth scroll for mobile navigation
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                // Add a small delay for mobile to allow sidebar to close
                setTimeout(() => {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }, 300);
            }
        });
    });
    
    // Auto-save sidebar state
    const sidebar = document.querySelector('.profile-sidebar');
    if (sidebar) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    const isCollapsed = sidebar.classList.contains('-translate-x-full');
                    localStorage.setItem('profile-sidebar-collapsed', isCollapsed);
                }
            });
        });
        
        observer.observe(sidebar, { attributes: true });
    }
    
    // Keyboard navigation support
    document.addEventListener('keydown', function(e) {
        // ESC key to close mobile sidebar
        if (e.key === 'Escape' && window.innerWidth < 768) {
            const sidebarData = Alpine.$data(profileLayout);
            if (sidebarData && sidebarData.sidebarOpen) {
                sidebarData.sidebarOpen = false;
            }
        }
        
        // Keyboard shortcuts for navigation (Ctrl/Cmd + number)
        if ((e.ctrlKey || e.metaKey) && e.key >= '1' && e.key <= '6') {
            e.preventDefault();
            const sectionIndex = parseInt(e.key) - 1;
            const navItems = document.querySelectorAll('.nav-item');
            if (navItems[sectionIndex]) {
                navItems[sectionIndex].click();
            }
        }
    });
    
    // Enhanced touch support for mobile
    let touchStartY = 0;
    let touchEndY = 0;
    
    profileLayout.addEventListener('touchstart', function(e) {
        touchStartY = e.touches[0].clientY;
    });
    
    profileLayout.addEventListener('touchend', function(e) {
        touchEndY = e.changedTouches[0].clientY;
        const deltaY = touchStartY - touchEndY;
        
        // Pull down to refresh (mobile)
        if (deltaY < -100 && window.scrollY === 0) {
            window.location.reload();
        }
    });
    
    console.log('✅ Profile layout enhanced functionality initialized');
});
</script>
@endpush
