@props([
    'breadcrumbs' => [],
    'currentSection' => null,
    'showBackButton' => false,
    'backUrl' => null,
    'user' => null,
    'showProfileCompletion' => true
])

@php
    $user = $user ?? Auth::user();
    
    // Auto-generate profile breadcrumbs if not provided
    if (empty($breadcrumbs) && $currentSection) {
        $profileSections = [
            'overview' => [
                'label' => 'Profile Overview',
                'url' => route('profile.show'),
                'icon' => 'user'
            ],
            'personal' => [
                'label' => 'Personal Information',
                'url' => route('profile.edit'),
                'icon' => 'id-card'
            ],
            'security' => [
                'label' => 'Security Settings',
                'url' => route('profile.security'),
                'icon' => 'shield-check'
            ],
            'preferences' => [
                'label' => 'Preferences',
                'url' => route('preferences.index'),
                'icon' => 'cog'
            ],
            'activity' => [
                'label' => 'Activity Dashboard',
                'url' => route('profile.activity.dashboard'),
                'icon' => 'chart-bar'
            ],
        ];
        
        $breadcrumbs = [
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Profile', 'url' => route('profile.show')],
        ];
        
        if ($currentSection && isset($profileSections[$currentSection]) && $currentSection !== 'overview') {
            $breadcrumbs[] = [
                'label' => $profileSections[$currentSection]['label'],
                'url' => $profileSections[$currentSection]['url']
            ];
        }
    }
    
    // Default back URL
    $backUrl = $backUrl ?? (count($breadcrumbs) > 1 ? $breadcrumbs[count($breadcrumbs) - 2]['url'] ?? route('dashboard') : route('dashboard'));
@endphp

<div class="profile-breadcrumbs bg-white border-b border-gray-200 {{ $showProfileCompletion ? 'pb-4' : 'pb-3' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-3">
            <!-- Mobile Back Button + Breadcrumbs -->
            <div class="flex items-center justify-between lg:hidden">
                @if($showBackButton)
                    <button onclick="window.history.back()" 
                            class="touch-target flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span class="hidden sm:inline">Back</span>
                    </button>
                @endif
                
                <!-- Mobile Profile Completion (if enabled) -->
                @if($showProfileCompletion)
                    <div class="flex-1 flex justify-end">
                        <x-profile-completion-indicator 
                            :user="$user" 
                            position="header" 
                            :showLabel="false" 
                            size="sm" />
                    </div>
                @endif
            </div>
            
            <!-- Desktop Breadcrumb Navigation -->
            <div class="hidden lg:flex lg:items-center lg:justify-between">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        @foreach($breadcrumbs as $index => $breadcrumb)
                            <li class="flex items-center">
                                @if($index > 0)
                                    <!-- Separator -->
                                    <svg class="flex-shrink-0 h-4 w-4 text-gray-300 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                                
                                @if(isset($breadcrumb['url']) && !$loop->last)
                                    <!-- Clickable breadcrumb -->
                                    <a href="{{ $breadcrumb['url'] }}" 
                                       class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors duration-200">
                                        @if(isset($breadcrumb['icon']) && $index === 0)
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                @if($breadcrumb['icon'] === 'home')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                                @endif
                                            </svg>
                                        @endif
                                        {{ $breadcrumb['label'] }}
                                    </a>
                                @else
                                    <!-- Current page (non-clickable) -->
                                    <span class="text-sm font-medium text-gray-900" aria-current="page">
                                        {{ $breadcrumb['label'] }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
                
                <!-- Desktop Profile Completion (if enabled) -->
                @if($showProfileCompletion)
                    <div class="flex items-center">
                        <x-profile-completion-indicator 
                            :user="$user" 
                            position="header" 
                            :showLabel="true" 
                            size="sm" />
                    </div>
                @endif
            </div>
            
            <!-- Current Page Title (Mobile) -->
            <div class="lg:hidden mt-2">
                <h1 class="text-lg font-semibold text-gray-900">
                    {{ end($breadcrumbs)['label'] ?? 'Profile' }}
                </h1>
                @if($currentSection && isset($profileSections[$currentSection]))
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $profileSections[$currentSection]['description'] ?? '' }}
                    </p>
                @endif
            </div>
        </div>
        
        <!-- Profile Section Navigation Tabs (for profile pages) -->
        @if($currentSection && in_array($currentSection, ['overview', 'personal', 'security', 'preferences', 'activity']))
            <div class="border-t border-gray-200 pt-3">
                <nav class="flex space-x-6 overflow-x-auto" aria-label="Profile sections">
                    @php
                        $sectionTabs = [
                            'overview' => [
                                'route' => 'profile.show',
                                'label' => 'Overview',
                                'icon' => 'user',
                                'description' => 'Profile summary and stats'
                            ],
                            'personal' => [
                                'route' => 'profile.edit',
                                'label' => 'Personal',
                                'icon' => 'id-card',
                                'description' => 'Name, bio, contact info'
                            ],
                            'security' => [
                                'route' => 'profile.security',
                                'label' => 'Security',
                                'icon' => 'shield-check',
                                'description' => '2FA and sessions'
                            ],
                            'preferences' => [
                                'route' => 'preferences.index',
                                'label' => 'Preferences',
                                'icon' => 'cog',
                                'description' => 'Customization options'
                            ],
                            'activity' => [
                                'route' => 'profile.activity.dashboard',
                                'label' => 'Activity',
                                'icon' => 'chart-bar',
                                'description' => 'Login history & stats'
                            ]
                        ];
                    @endphp
                    
                    @foreach($sectionTabs as $key => $tab)
                        <a href="{{ route($tab['route']) }}" 
                           class="flex items-center px-3 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition-all duration-200
                                  {{ $currentSection === $key 
                                     ? 'bg-blue-50 text-blue-700 border border-blue-200' 
                                     : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                            
                            <!-- Tab Icon -->
                            <svg class="w-4 h-4 mr-2 {{ $currentSection === $key ? 'text-blue-500' : 'text-gray-400' }}" 
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @switch($tab['icon'])
                                    @case('user')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        @break
                                    @case('id-card')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                        @break
                                    @case('shield-check')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        @break
                                    @case('cog')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        @break
                                    @case('chart-bar')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        @break
                                @endswitch
                            </svg>
                            
                            <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                            <span class="sm:hidden">{{ $tab['label'] }}</span>
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.touch-target {
    min-height: 44px;
    min-width: 44px;
}

.profile-breadcrumbs nav {
    scrollbar-width: none; /* Firefox */
}

.profile-breadcrumbs nav::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
</style>
@endpush
