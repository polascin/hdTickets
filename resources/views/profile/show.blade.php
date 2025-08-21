<x-modern-app-layout title="My Profile" subtitle="Manage your account information, security settings, and preferences">
    <x-slot name="headerActions">
        <div class="flex flex-col sm:flex-row gap-3">
            <x-ui.button href="{{ route('profile.edit') }}" variant="primary" icon="edit"
                aria-label="Edit your profile information">
                Edit Profile
            </x-ui.button>
            <x-ui.button href="{{ route('profile.security') }}" variant="outline" icon="shield-check"
                aria-label="Manage security settings">
                Security
            </x-ui.button>
            <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="secondary" icon="chart-bar"
                aria-label="View your activity dashboard">
                Activity Dashboard
            </x-ui.button>
        </div>
    </x-slot>

    <!-- Enhanced Flash Messages -->
    @if (session('status') === 'profile-updated')
        <div class="mb-6">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">Profile updated successfully!</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="mb-6">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">Password updated successfully!</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('status') === 'photo-updated')
        <div class="mb-6">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-700 font-medium">Profile photo updated successfully!</p>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-8">
        <!-- Profile Header Section -->
        <x-ui.card class="overflow-hidden">
            <x-ui.card-content class="p-8">
                <div class="flex flex-col lg:flex-row items-start lg:items-center space-y-6 lg:space-y-0 lg:space-x-8">
                    <!-- Profile Photo -->
                    <div class="flex-shrink-0">
                        <div class="relative group">
                            @if($user->profile_picture)
                                <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                     alt="{{ $user->name }}"
                                     class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl transition-all duration-300 group-hover:shadow-2xl group-hover:scale-105">
                            @else
                                <div class="avatar-placeholder w-32 h-32 bg-gradient-to-br from-blue-500 via-purple-600 to-pink-600 rounded-full flex items-center justify-center border-4 border-white shadow-xl transition-all duration-300 group-hover:shadow-2xl group-hover:scale-105"
                                     title="Upload profile photo">
                                    <span class="text-white text-3xl font-bold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            
                            <!-- Photo Upload Overlay -->
                            <label for="profile-photo-upload" 
                                   class="upload-overlay absolute inset-0 bg-black bg-opacity-60 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer focus:opacity-100 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-offset-2"
                                   tabindex="0">
                                <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-white text-xs font-medium">Change Photo</span>
                                <input type="file" 
                                       id="profile-photo-upload" 
                                       class="sr-only" 
                                       accept="image/*"
                                       onchange="handleProfilePhotoChange(this)"
                                       aria-label="Upload profile picture">
                            </label>
                        </div>
                    </div>

                    <!-- Profile Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex-1">
                                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                                <p class="text-lg text-gray-600 mb-4">{{ $user->email }}</p>
                                
                                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Role: <span class="ml-1 capitalize font-medium">{{ ucfirst($user->role) }}</span>
                                    </div>
                                    
                                    @if($user->email_verified_at)
                                        <div class="flex items-center text-green-600">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Email Verified
                                        </div>
                                    @endif
                                    
                                    @if($user->created_at)
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 8a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Member since {{ $user->created_at->format('M Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>
    </div>
</x-modern-app-layout>
