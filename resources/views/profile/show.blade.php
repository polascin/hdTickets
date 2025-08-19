<x-modern-app-layout title="My Profile">
    <x-slot name="headerActions">
        <div class="flex space-x-3">
            <x-ui.button 
                href="{{ route('profile.edit') }}" 
                variant="primary"
                :icon="'<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z\"></path></svg>'">
                Edit Profile
            </x-ui.button>
            
            <x-ui.button 
                href="{{ route('profile.activity.dashboard') }}" 
                variant="secondary"
                :icon="'<svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\"></path></svg>'">
                Activity Dashboard
            </x-ui.button>
        </div>
    </x-slot>

    <!-- Flash Messages -->
    @if (session('status') === 'profile-updated')
        <x-ui.alert variant="success" title="Success!" dismissible="true" class="mb-6">
            Profile updated successfully!
        </x-ui.alert>
    @endif

    @php
        $user = auth()->user();
        $profileCompletion = $user->getProfileCompletion();
    @endphp

    <div class="space-y-6">
        <!-- Profile Overview Card -->
        <x-ui.card variant="elevated">
            <x-ui.card-content class="p-8">
                <div class="flex flex-col md:flex-row items-start md:items-center space-y-6 md:space-y-0 md:space-x-8">
                    <!-- Avatar Section -->
                    <div class="flex-shrink-0">
                        <div class="relative">
                            @if($user->profile_photo_path)
                                <img 
                                    src="{{ $user->profile_photo_url }}" 
                                    alt="{{ $user->name }}" 
                                    class="w-24 h-24 rounded-full border-4 border-white shadow-lg">
                            @else
                                <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                    <span class="text-2xl font-bold text-white">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif
                            <div class="absolute -bottom-1 -right-1">
                                <x-ui.badge variant="success" size="sm" dot="true">Online</x-ui.badge>
                            </div>
                        </div>
                    </div>

                    <!-- User Info Section -->
                    <div class="flex-1">
                        <div class="space-y-3">
                            <div>
                                <h1 class="hd-heading-1 !mb-1">{{ $user->name }}</h1>
                                <p class="hd-text-base text-gray-600">{{ $user->email }}</p>
                                <div class="flex items-center space-x-3 mt-2">
                                    <x-ui.badge variant="primary" pill="true">{{ ucfirst($user->role) }}</x-ui.badge>
                                    @if($user->email_verified_at)
                                        <x-ui.badge variant="success" size="sm">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            Verified
                                        </x-ui.badge>
                                    @endif
                                </div>
                            </div>

                            <!-- Profile Completion -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="hd-text-small font-medium">Profile Completion</span>
                                    <span class="hd-text-small font-bold text-blue-600">{{ $profileCompletion['percentage'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="h-2 rounded-full transition-all duration-500 {{ $profileCompletion['percentage'] >= 90 ? 'bg-green-500' : 'bg-blue-500' }}" 
                                        style="width: {{ $profileCompletion['percentage'] }}%">
                                    </div>
                                </div>
                                @if($profileCompletion['percentage'] < 100)
                                    <div class="mt-2">
                                        <p class="hd-text-small text-gray-600">
                                            Complete your profile to unlock all features
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="flex md:flex-col space-x-6 md:space-x-0 md:space-y-4">
                        <div class="text-center">
                            <div class="hd-heading-3 !mb-0 text-blue-600">{{ $user->created_at->diffInDays(now()) }}</div>
                            <div class="hd-text-small text-gray-600">Days Active</div>
                        </div>
                        <div class="text-center">
                            <div class="hd-heading-3 !mb-0 text-green-600">{{ $user->login_count ?? 0 }}</div>
                            <div class="hd-text-small text-gray-600">Logins</div>
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Profile Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <x-ui.card>
                <x-ui.card-header title="Personal Information">
                    <x-ui.button href="{{ route('profile.edit') }}" variant="ghost" size="sm">Edit</x-ui.button>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4">
                        <div>
                            <label class="hd-label">Full Name</label>
                            <p class="hd-text-base">{{ $user->name ?: 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="hd-label">Email Address</label>
                            <div class="flex items-center space-x-2">
                                <p class="hd-text-base">{{ $user->email }}</p>
                                @if($user->email_verified_at)
                                    <x-ui.badge variant="success" size="xs">Verified</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="xs">Unverified</x-ui.badge>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="hd-label">Member Since</label>
                            <p class="hd-text-base">{{ $user->created_at->format('M j, Y') }}</p>
                        </div>
                        <div>
                            <label class="hd-label">Last Active</label>
                            <p class="hd-text-base">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>

            <!-- Account Settings -->
            <x-ui.card>
                <x-ui.card-header title="Account Settings">
                    <x-ui.button href="{{ route('profile.edit') }}" variant="ghost" size="sm">Manage</x-ui.button>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <h4 class="hd-text-base font-medium">Two-Factor Authentication</h4>
                                <p class="hd-text-small text-gray-600">Add an extra layer of security</p>
                            </div>
                            @if($user->two_factor_secret)
                                <x-ui.badge variant="success">Enabled</x-ui.badge>
                            @else
                                <x-ui.badge variant="warning">Disabled</x-ui.badge>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div>
                                <h4 class="hd-text-base font-medium">Email Notifications</h4>
                                <p class="hd-text-small text-gray-600">Receive updates and alerts</p>
                            </div>
                            <x-ui.badge variant="success">Enabled</x-ui.badge>
                        </div>
                        
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <h4 class="hd-text-base font-medium">API Access</h4>
                                <p class="hd-text-small text-gray-600">Programmatic access to your data</p>
                            </div>
                            @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
                                <x-ui.badge variant="success">Available</x-ui.badge>
                            @else
                                <x-ui.badge variant="default">Upgrade Required</x-ui.badge>
                            @endif
                        </div>
                    </div>
                </x-ui.card-content>
            </x-ui.card>
        </div>

        <!-- Activity Overview -->
        @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
        <x-ui.card>
            <x-ui.card-header title="Recent Activity">
                <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="ghost" size="sm">View All</x-ui.button>
            </x-ui.card-header>
            <x-ui.card-content>
                <div class="space-y-4">
                    <!-- Sample activity items - replace with real data -->
                    <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="hd-text-base font-medium">Created new ticket alert</p>
                            <p class="hd-text-small text-gray-600">Lakers vs Warriors • 2 hours ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="hd-text-base font-medium">Profile updated successfully</p>
                            <p class="hd-text-small text-gray-600">Contact information • 1 day ago</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 py-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="hd-text-base font-medium">Logged in from new device</p>
                            <p class="hd-text-small text-gray-600">Chrome on Windows • 3 days ago</p>
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
        @endif

        <!-- Quick Actions -->
        <x-ui.card>
            <x-ui.card-header title="Quick Actions"></x-ui.card-header>
            <x-ui.card-content>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <x-ui.button 
                        href="{{ route('profile.edit') }}" 
                        variant="outline" 
                        fullWidth="true"
                        class="justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Profile
                    </x-ui.button>
                    
                    <x-ui.button 
                        href="{{ route('profile.security') }}" 
                        variant="outline" 
                        fullWidth="true"
                        class="justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Security
                    </x-ui.button>
                    
                    <x-ui.button 
                        href="{{ route('preferences.index') }}" 
                        variant="outline" 
                        fullWidth="true"
                        class="justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Preferences
                    </x-ui.button>
                    
                    @if(Auth::user()->isAdmin() || Auth::user()->isAgent())
                    <x-ui.button 
                        href="{{ route('profile.activity.dashboard') }}" 
                        variant="outline" 
                        fullWidth="true"
                        class="justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Activity
                    </x-ui.button>
                    @endif
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-modern-app-layout>
