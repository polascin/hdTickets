@extends('layouts.modern')

@section('title', 'Account Settings')
@section('description', 'Manage your Sports Events Tickets account settings, security, and preferences')

@push('styles')
<style>
/* Account Settings Styles */
.settings-nav {
    border-right: 2px solid #f1f5f9;
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.settings-nav-item {
    @apply w-full text-left px-4 py-3 text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-lg mb-2 transition-all duration-200 flex items-center space-x-3;
}

.settings-nav-item.active {
    @apply bg-blue-100 text-blue-700 font-medium;
}

.settings-section {
    @apply hidden;
}

.settings-section.active {
    @apply block;
}

.form-group {
    @apply mb-6;
}

.form-label {
    @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-input {
    @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent;
}

.form-select {
    @apply w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white;
}

.toggle-switch {
    @apply relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

.toggle-switch.enabled {
    @apply bg-blue-600;
}

.toggle-switch.disabled {
    @apply bg-gray-200;
}

.toggle-handle {
    @apply inline-block h-4 w-4 transform rounded-full bg-white transition;
}

.toggle-handle.enabled {
    @apply translate-x-6;
}

.toggle-handle.disabled {
    @apply translate-x-1;
}

.security-item {
    @apply flex items-center justify-between p-4 bg-gray-50 rounded-lg mb-4;
}

.danger-zone {
    @apply border-2 border-red-200 rounded-lg p-6 bg-red-50;
}

.subscription-card {
    @apply bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-6 border border-blue-200;
}

.notification-item {
    @apply flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-3;
}
</style>
@endpush

@section('content')
<div class="py-6">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Account Settings</h1>
                <p class="text-gray-600 mt-2">Manage your profile, security, and notification preferences</p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                    {{ ucfirst(Auth::user()->role) }} Account
                </span>
                @if(Auth::user()->role === 'customer')
                    @if(Auth::user()->hasActiveSubscription())
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                            Active Subscription
                        </span>
                    @else
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-medium rounded-full">
                            No Subscription
                        </span>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Settings Navigation -->
        <div class="lg:col-span-1">
            <div class="settings-nav">
                <nav class="space-y-1">
                    <button class="settings-nav-item active" data-section="profile">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Profile</span>
                    </button>
                    <button class="settings-nav-item" data-section="security">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Security</span>
                    </button>
                    <button class="settings-nav-item" data-section="notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.868 19.462A17.936 17.936 0 003 12c0-9.941 8.059-18 18-18s18 8.059 18 18-8.059 18-18 18c-2.508 0-4.885-.511-7.077-1.438L9 21l4.462-4.538z"></path>
                        </svg>
                        <span>Notifications</span>
                    </button>
                    @if(Auth::user()->role === 'customer')
                    <button class="settings-nav-item" data-section="subscription">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span>Subscription</span>
                    </button>
                    @endif
                    <button class="settings-nav-item" data-section="privacy">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Privacy</span>
                    </button>
                    <button class="settings-nav-item" data-section="account">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        </svg>
                        <span>Account</span>
                    </button>
                </nav>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="lg:col-span-3">
            <!-- Profile Section -->
            <div id="profile" class="settings-section active">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Profile Information</h2>
                        <span class="text-sm text-gray-500">Last updated: {{ Auth::user()->updated_at->format('M d, Y') }}</span>
                    </div>

                    <form action="{{ route('account.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Profile Photo -->
                        <div class="form-group">
                            <label class="form-label">Profile Photo</label>
                            <div class="flex items-center space-x-6">
                                <div class="relative">
                                    <img class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg" 
                                         src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=7c3aed&background=f3f4f6' }}" 
                                         alt="{{ Auth::user()->name }}">
                                    <div class="absolute bottom-0 right-0 bg-blue-600 rounded-full p-2 cursor-pointer" onclick="document.getElementById('profile_photo').click()">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <input type="file" id="profile_photo" name="profile_photo" class="hidden" accept="image/*">
                                    <button type="button" onclick="document.getElementById('profile_photo').click()" class="bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                                        Change Photo
                                    </button>
                                    <p class="text-xs text-gray-500 mt-1">JPG, PNG or GIF (max. 2MB)</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Full Name -->
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" value="{{ Auth::user()->name }}" class="form-input" required>
                            </div>

                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="relative">
                                    <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" class="form-input pr-10" required>
                                    @if(Auth::user()->email_verified_at)
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                @if(!Auth::user()->email_verified_at)
                                    <p class="text-sm text-red-600 mt-1">Email not verified - <a href="{{ route('verification.send') }}" class="underline">Send verification email</a></p>
                                @endif
                            </div>

                            <!-- Phone Number -->
                            <div class="form-group">
                                <label for="phone" class="form-label">Phone Number</label>
                                <div class="relative">
                                    <input type="tel" id="phone" name="phone" value="{{ Auth::user()->phone }}" class="form-input pr-10" placeholder="+1 (555) 123-4567">
                                    @if(Auth::user()->phone_verified_at)
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                @if(Auth::user()->phone && !Auth::user()->phone_verified_at)
                                    <p class="text-sm text-yellow-600 mt-1">Phone not verified - <a href="{{ route('phone.verify') }}" class="underline">Verify now</a></p>
                                @endif
                            </div>

                            <!-- Timezone -->
                            <div class="form-group">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select id="timezone" name="timezone" class="form-select">
                                    <option value="UTC" {{ (Auth::user()->timezone ?? 'UTC') === 'UTC' ? 'selected' : '' }}>UTC (Coordinated Universal Time)</option>
                                    <option value="America/New_York" {{ (Auth::user()->timezone ?? '') === 'America/New_York' ? 'selected' : '' }}>Eastern Time (ET)</option>
                                    <option value="America/Chicago" {{ (Auth::user()->timezone ?? '') === 'America/Chicago' ? 'selected' : '' }}>Central Time (CT)</option>
                                    <option value="America/Denver" {{ (Auth::user()->timezone ?? '') === 'America/Denver' ? 'selected' : '' }}>Mountain Time (MT)</option>
                                    <option value="America/Los_Angeles" {{ (Auth::user()->timezone ?? '') === 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PT)</option>
                                    <option value="Europe/London" {{ (Auth::user()->timezone ?? '') === 'Europe/London' ? 'selected' : '' }}>London (GMT)</option>
                                    <option value="Europe/Paris" {{ (Auth::user()->timezone ?? '') === 'Europe/Paris' ? 'selected' : '' }}>Paris (CET)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Sports Preferences -->
                        <div class="form-group">
                            <label class="form-label">Favorite Sports</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @php
                                    $sports = ['Football', 'Basketball', 'Baseball', 'Soccer', 'Hockey', 'Tennis', 'Golf', 'Boxing'];
                                    $userPreferences = json_decode(Auth::user()->sports_preferences ?? '[]', true);
                                @endphp
                                @foreach($sports as $sport)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="sports_preferences[]" value="{{ $sport }}" 
                                           {{ in_array($sport, $userPreferences) ? 'checked' : '' }}
                                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm text-gray-700">{{ $sport }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="form-group">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea id="bio" name="bio" rows="3" class="form-input" placeholder="Tell us about yourself...">{{ Auth::user()->bio }}</textarea>
                            <p class="text-sm text-gray-500 mt-1">Brief description for your profile (optional)</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Section -->
            <div id="security" class="settings-section">
                <div class="space-y-6">
                    <!-- Password Section -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Password & Authentication</h2>
                        
                        <form action="{{ route('account.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" class="form-input" required>
                                </div>
                                <div></div>
                                <div class="form-group">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" id="password" name="password" class="form-input" required>
                                    <p class="text-sm text-gray-500 mt-1">Must be at least 8 characters long</p>
                                </div>
                                <div class="form-group">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" required>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Two-Factor Authentication -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Two-Factor Authentication</h3>
                        
                        @if(Auth::user()->two_factor_confirmed_at)
                            <div class="security-item">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Two-Factor Authentication Enabled</h4>
                                        <p class="text-sm text-gray-600">Your account is protected with 2FA</p>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <button onclick="showRecoveryCodes()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                        View Recovery Codes
                                    </button>
                                    <form action="{{ route('two-factor.disable') }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium" 
                                                onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                            Disable
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="security-item">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-gray-900">Two-Factor Authentication Disabled</h4>
                                        <p class="text-sm text-gray-600">Enhance your security by enabling 2FA</p>
                                    </div>
                                </div>
                                <form action="{{ route('two-factor.enable') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Enable 2FA
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <!-- Login Sessions -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Active Sessions</h3>
                        
                        <div class="space-y-4">
                            @php
                                $currentSessionId = session()->getId();
                                $sessions = collect([
                                    [
                                        'id' => $currentSessionId,
                                        'device' => 'Current Session',
                                        'location' => 'New York, NY',
                                        'last_activity' => now(),
                                        'is_current' => true
                                    ],
                                    [
                                        'id' => 'session2',
                                        'device' => 'Chrome on Windows',
                                        'location' => 'Los Angeles, CA',
                                        'last_activity' => now()->subHours(2),
                                        'is_current' => false
                                    ]
                                ]);
                            @endphp

                            @foreach($sessions as $session)
                            <div class="security-item">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center mr-4">
                                        @if($session['is_current'])
                                            <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                        @else
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $session['device'] }}</h4>
                                        <div class="text-sm text-gray-600 space-x-4">
                                            <span>{{ $session['location'] }}</span>
                                            <span>{{ $session['last_activity']->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                @if(!$session['is_current'])
                                    <form action="{{ route('account.sessions.destroy', $session['id']) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                            Revoke
                                        </button>
                                    </form>
                                @else
                                    <span class="text-green-600 text-sm font-medium">Current</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form action="{{ route('account.sessions.destroy-all') }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium" 
                                        onclick="return confirm('This will log you out of all other sessions. Continue?')">
                                    Revoke All Other Sessions
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Section -->
            <div id="notifications" class="settings-section">
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Notification Preferences</h2>
                        <div class="flex items-center space-x-3">
                            <button onclick="toggleAllNotifications()" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                Enable All
                            </button>
                            <span class="text-gray-300">|</span>
                            <button onclick="toggleAllNotifications(false)" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                Disable All
                            </button>
                        </div>
                    </div>

                    <form action="{{ route('account.notifications.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Email Notifications -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Email Notifications</h3>
                            <div class="space-y-3">
                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Ticket Price Alerts</h4>
                                        <p class="text-sm text-gray-600">Get notified when ticket prices change for your followed events</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['email_price_alerts'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="email_price_alerts">
                                        <input type="checkbox" name="email_price_alerts" {{ Auth::user()->notification_preferences['email_price_alerts'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['email_price_alerts'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">New Event Notifications</h4>
                                        <p class="text-sm text-gray-600">Be the first to know about new sports events in your favorite categories</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['email_new_events'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="email_new_events">
                                        <input type="checkbox" name="email_new_events" {{ Auth::user()->notification_preferences['email_new_events'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['email_new_events'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Purchase Confirmations</h4>
                                        <p class="text-sm text-gray-600">Receive email confirmation for successful ticket purchases</p>
                                    </div>
                                    <label class="toggle-switch enabled" data-toggle="email_purchase_confirmations">
                                        <input type="checkbox" name="email_purchase_confirmations" checked disabled class="sr-only">
                                        <span class="toggle-handle enabled"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Marketing & Promotions</h4>
                                        <p class="text-sm text-gray-600">Updates about platform features, promotions, and sports events news</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['email_marketing'] ?? false ? 'enabled' : 'disabled' }}" data-toggle="email_marketing">
                                        <input type="checkbox" name="email_marketing" {{ Auth::user()->notification_preferences['email_marketing'] ?? false ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['email_marketing'] ?? false ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SMS Notifications -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">SMS Notifications</h3>
                            <div class="space-y-3">
                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Urgent Price Drops</h4>
                                        <p class="text-sm text-gray-600">Immediate SMS for significant price drops (requires verified phone)</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['sms_urgent_alerts'] ?? false ? 'enabled' : 'disabled' }}" data-toggle="sms_urgent_alerts">
                                        <input type="checkbox" name="sms_urgent_alerts" {{ Auth::user()->notification_preferences['sms_urgent_alerts'] ?? false ? 'checked' : '' }} class="sr-only" {{ !Auth::user()->phone_verified_at ? 'disabled' : '' }}>
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['sms_urgent_alerts'] ?? false ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Event Reminders</h4>
                                        <p class="text-sm text-gray-600">SMS reminders for upcoming events you have tickets for</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['sms_reminders'] ?? false ? 'enabled' : 'disabled' }}" data-toggle="sms_reminders">
                                        <input type="checkbox" name="sms_reminders" {{ Auth::user()->notification_preferences['sms_reminders'] ?? false ? 'checked' : '' }} class="sr-only" {{ !Auth::user()->phone_verified_at ? 'disabled' : '' }}>
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['sms_reminders'] ?? false ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>
                            </div>
                            
                            @if(!Auth::user()->phone_verified_at)
                                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        <p class="text-sm text-yellow-800">
                                            <a href="{{ route('phone.verify') }}" class="underline font-medium">Verify your phone number</a> to enable SMS notifications
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Browser Notifications -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Browser Notifications</h3>
                            <div class="space-y-3">
                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Real-Time Alerts</h4>
                                        <p class="text-sm text-gray-600">Desktop notifications for price changes and new tickets</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['browser_alerts'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="browser_alerts">
                                        <input type="checkbox" name="browser_alerts" {{ Auth::user()->notification_preferences['browser_alerts'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['browser_alerts'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Sound Alerts</h4>
                                        <p class="text-sm text-gray-600">Play notification sounds for important alerts</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->notification_preferences['sound_alerts'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="sound_alerts">
                                        <input type="checkbox" name="sound_alerts" {{ Auth::user()->notification_preferences['sound_alerts'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->notification_preferences['sound_alerts'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Quiet Hours -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quiet Hours</h3>
                            <div class="notification-item">
                                <div>
                                    <h4 class="font-medium text-gray-900">Enable Quiet Hours</h4>
                                    <p class="text-sm text-gray-600">Disable notifications during specified hours</p>
                                </div>
                                <label class="toggle-switch {{ Auth::user()->notification_preferences['quiet_hours_enabled'] ?? false ? 'enabled' : 'disabled' }}" data-toggle="quiet_hours_enabled">
                                    <input type="checkbox" name="quiet_hours_enabled" {{ Auth::user()->notification_preferences['quiet_hours_enabled'] ?? false ? 'checked' : '' }} class="sr-only">
                                    <span class="toggle-handle {{ Auth::user()->notification_preferences['quiet_hours_enabled'] ?? false ? 'enabled' : 'disabled' }}"></span>
                                </label>
                            </div>
                            
                            <div id="quietHoursConfig" class="{{ Auth::user()->notification_preferences['quiet_hours_enabled'] ?? false ? '' : 'hidden' }} mt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="quiet_hours_start" class="form-label">Start Time</label>
                                        <input type="time" id="quiet_hours_start" name="quiet_hours_start" 
                                               value="{{ Auth::user()->notification_preferences['quiet_hours_start'] ?? '22:00' }}" 
                                               class="form-input">
                                    </div>
                                    <div>
                                        <label for="quiet_hours_end" class="form-label">End Time</label>
                                        <input type="time" id="quiet_hours_end" name="quiet_hours_end" 
                                               value="{{ Auth::user()->notification_preferences['quiet_hours_end'] ?? '08:00' }}" 
                                               class="form-input">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                Update Notification Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if(Auth::user()->role === 'customer')
            <!-- Subscription Section -->
            <div id="subscription" class="settings-section">
                <div class="space-y-6">
                    <!-- Current Subscription -->
                    <div class="subscription-card">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Current Subscription</h2>
                                <p class="text-gray-600">Manage your sports events tickets access</p>
                            </div>
                            <div class="text-right">
                                @if(Auth::user()->hasActiveSubscription())
                                    <span class="px-4 py-2 bg-green-100 text-green-800 font-medium rounded-full">Active</span>
                                @else
                                    <span class="px-4 py-2 bg-yellow-100 text-yellow-800 font-medium rounded-full">No Subscription</span>
                                @endif
                            </div>
                        </div>

                        @if(Auth::user()->hasActiveSubscription())
                            <div class="bg-white rounded-xl p-6 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="text-center">
                                        <h3 class="text-3xl font-bold text-blue-600">{{ Auth::user()->subscription->tickets_per_month ?? 100 }}</h3>
                                        <p class="text-sm text-gray-600">Tickets per Month</p>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-3xl font-bold text-green-600">${{ number_format(Auth::user()->subscription->price ?? 29.99, 2) }}</h3>
                                        <p class="text-sm text-gray-600">Monthly Fee</p>
                                    </div>
                                    <div class="text-center">
                                        <h3 class="text-3xl font-bold text-purple-600">{{ Auth::user()->getMonthlyTicketUsage() }}</h3>
                                        <p class="text-sm text-gray-600">Used This Month</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Next billing date: <span class="font-medium">{{ Auth::user()->subscription->next_billing_date->format('F j, Y') ?? 'N/A' }}</span></p>
                                    <p class="text-sm text-gray-600">Subscription started: <span class="font-medium">{{ Auth::user()->subscription->created_at->format('F j, Y') ?? 'N/A' }}</span></p>
                                </div>
                                <div class="flex space-x-3">
                                    <button onclick="openUpgradeModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Upgrade Plan
                                    </button>
                                    <button onclick="cancelSubscription()" class="text-red-600 hover:text-red-700 font-medium">
                                        Cancel Subscription
                                    </button>
                                </div>
                            </div>
                        @else
                            @php
                                $freeTrialDays = config('subscription.free_access_days', 7);
                                $daysUsed = Auth::user()->created_at->diffInDays(now());
                                $daysRemaining = max(0, $freeTrialDays - $daysUsed);
                            @endphp
                            
                            @if($daysRemaining > 0)
                                <div class="bg-white rounded-xl p-6 mb-6">
                                    <div class="text-center">
                                        <h3 class="text-2xl font-bold text-green-600 mb-2">Free Trial Active</h3>
                                        <p class="text-gray-600 mb-4">You have {{ $daysRemaining }} day(s) remaining in your free trial</p>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full" style="width: {{ ($daysUsed / $freeTrialDays) * 100 }}%"></div>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-2">{{ $daysUsed }} of {{ $freeTrialDays }} days used</p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-white rounded-xl p-6 mb-6 text-center">
                                    <h3 class="text-2xl font-bold text-red-600 mb-2">Free Trial Expired</h3>
                                    <p class="text-gray-600 mb-4">Subscribe now to continue accessing sports event tickets</p>
                                </div>
                            @endif

                            <div class="text-center">
                                <button onclick="openSubscribeModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors">
                                    {{ $daysRemaining > 0 ? 'Subscribe Now' : 'Activate Subscription' }}
                                </button>
                                <p class="text-sm text-gray-500 mt-2">No money-back guarantee - All sales final</p>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Methods -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Payment Methods</h3>
                        
                        @if(Auth::user()->hasPaymentMethods())
                            <div class="space-y-4">
                                @foreach(Auth::user()->paymentMethods() as $method)
                                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                    <div class="flex items-center">
                                        <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                        </svg>
                                        <div>
                                            <h4 class="font-medium text-gray-900">•••• •••• •••• {{ $method->last4 ?? '1234' }}</h4>
                                            <p class="text-sm text-gray-600">Expires {{ $method->exp_month ?? '12' }}/{{ $method->exp_year ?? '2025' }}</p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-3">
                                        @if($method->is_default ?? true)
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Default</span>
                                        @else
                                            <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">Make Default</button>
                                        @endif
                                        <button class="text-red-600 hover:text-red-700 text-sm font-medium">Remove</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <h4 class="font-medium text-gray-900 mb-2">No Payment Methods</h4>
                                <p class="text-gray-600 mb-4">Add a payment method to manage your subscription</p>
                            </div>
                        @endif
                        
                        <div class="mt-6">
                            <button onclick="addPaymentMethod()" class="w-full bg-gray-100 hover:bg-gray-200 border-2 border-dashed border-gray-300 rounded-lg px-6 py-4 text-gray-600 font-medium transition-colors">
                                + Add New Payment Method
                            </button>
                        </div>
                    </div>

                    <!-- Billing History -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">Billing History</h3>
                            <button class="text-blue-600 hover:text-blue-700 text-sm font-medium">Download All</button>
                        </div>
                        
                        @php
                            $invoices = collect([
                                ['date' => now()->subMonth(), 'amount' => 29.99, 'status' => 'paid'],
                                ['date' => now()->subMonths(2), 'amount' => 29.99, 'status' => 'paid'],
                                ['date' => now()->subMonths(3), 'amount' => 29.99, 'status' => 'paid']
                            ]);
                        @endphp

                        <div class="space-y-4">
                            @forelse($invoices as $invoice)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">Monthly Subscription</h4>
                                    <p class="text-sm text-gray-600">{{ $invoice['date']->format('F j, Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-medium text-gray-900">${{ number_format($invoice['amount'], 2) }}</p>
                                    <div class="flex items-center space-x-3">
                                        <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded uppercase">{{ $invoice['status'] }}</span>
                                        <button class="text-blue-600 hover:text-blue-700 text-sm">Download</button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <p class="text-gray-500">No billing history yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Privacy Section -->
            <div id="privacy" class="settings-section">
                <div class="space-y-6">
                    <!-- Privacy Controls -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Privacy Settings</h2>
                        
                        <form action="{{ route('account.privacy.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="space-y-6">
                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Profile Visibility</h4>
                                        <p class="text-sm text-gray-600">Control who can see your profile information</p>
                                    </div>
                                    <select name="profile_visibility" class="form-select max-w-xs">
                                        <option value="public" {{ (Auth::user()->privacy_settings['profile_visibility'] ?? 'public') === 'public' ? 'selected' : '' }}>Public</option>
                                        <option value="agents_only" {{ (Auth::user()->privacy_settings['profile_visibility'] ?? '') === 'agents_only' ? 'selected' : '' }}>Agents Only</option>
                                        <option value="private" {{ (Auth::user()->privacy_settings['profile_visibility'] ?? '') === 'private' ? 'selected' : '' }}>Private</option>
                                    </select>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Activity Tracking</h4>
                                        <p class="text-sm text-gray-600">Allow the platform to track your activity for better recommendations</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->privacy_settings['activity_tracking'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="activity_tracking">
                                        <input type="checkbox" name="activity_tracking" {{ Auth::user()->privacy_settings['activity_tracking'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->privacy_settings['activity_tracking'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Data Sharing with Partners</h4>
                                        <p class="text-sm text-gray-600">Allow sharing anonymized data with sports venues and partners</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->privacy_settings['data_sharing'] ?? false ? 'enabled' : 'disabled' }}" data-toggle="data_sharing">
                                        <input type="checkbox" name="data_sharing" {{ Auth::user()->privacy_settings['data_sharing'] ?? false ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->privacy_settings['data_sharing'] ?? false ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>

                                <div class="notification-item">
                                    <div>
                                        <h4 class="font-medium text-gray-900">Cookies & Analytics</h4>
                                        <p class="text-sm text-gray-600">Allow analytics cookies for platform improvement</p>
                                    </div>
                                    <label class="toggle-switch {{ Auth::user()->privacy_settings['analytics_cookies'] ?? true ? 'enabled' : 'disabled' }}" data-toggle="analytics_cookies">
                                        <input type="checkbox" name="analytics_cookies" {{ Auth::user()->privacy_settings['analytics_cookies'] ?? true ? 'checked' : '' }} class="sr-only">
                                        <span class="toggle-handle {{ Auth::user()->privacy_settings['analytics_cookies'] ?? true ? 'enabled' : 'disabled' }}"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                    Update Privacy Settings
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Data Export -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Data Export</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-4 border border-gray-200 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Download Personal Data</h4>
                                <p class="text-sm text-gray-600 mb-4">Export all your personal data including profile, preferences, and activity history</p>
                                <form action="{{ route('account.export.personal') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Request Export
                                    </button>
                                </form>
                            </div>

                            <div class="p-4 border border-gray-200 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Download Purchase History</h4>
                                <p class="text-sm text-gray-600 mb-4">Export your complete ticket purchase history and transactions</p>
                                <form action="{{ route('account.export.purchases') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Export Purchases
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Section -->
            <div id="account" class="settings-section">
                <div class="space-y-6">
                    <!-- Account Information -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Account Information</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Account Type</h4>
                                <p class="text-sm text-gray-600">{{ ucfirst(Auth::user()->role) }} Account</p>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Member Since</h4>
                                <p class="text-sm text-gray-600">{{ Auth::user()->created_at->format('F j, Y') }}</p>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Email Status</h4>
                                <p class="text-sm text-gray-600">
                                    @if(Auth::user()->email_verified_at)
                                        <span class="text-green-600">✓ Verified</span>
                                    @else
                                        <span class="text-red-600">✗ Unverified</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <h4 class="font-medium text-gray-900 mb-2">Two-Factor Auth</h4>
                                <p class="text-sm text-gray-600">
                                    @if(Auth::user()->two_factor_confirmed_at)
                                        <span class="text-green-600">✓ Enabled</span>
                                    @else
                                        <span class="text-yellow-600">✗ Disabled</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Account Actions -->
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-6">Account Actions</h3>
                        
                        <div class="space-y-4">
                            <!-- Download Account Data -->
                            <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">Download All Data</h4>
                                    <p class="text-sm text-gray-600">Get a complete copy of your account data</p>
                                </div>
                                <form action="{{ route('account.export.complete') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                        Download
                                    </button>
                                </form>
                            </div>

                            <!-- Temporary Deactivation -->
                            <div class="flex items-center justify-between p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div>
                                    <h4 class="font-medium text-gray-900">Temporarily Deactivate Account</h4>
                                    <p class="text-sm text-gray-600">Hide your profile and pause notifications (can be reactivated)</p>
                                </div>
                                <button onclick="confirmDeactivation()" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                    Deactivate
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="danger-zone">
                        <div class="flex items-start">
                            <svg class="w-8 h-8 text-red-600 mr-4 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-red-800 mb-2">Danger Zone</h3>
                                <p class="text-red-700 mb-6">These actions are irreversible. Please proceed with caution.</p>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-red-200">
                                        <div>
                                            <h4 class="font-medium text-red-800">Delete Account Permanently</h4>
                                            <p class="text-sm text-red-600">All data will be permanently deleted. This action cannot be undone.</p>
                                        </div>
                                        <button onclick="confirmAccountDeletion()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                                            Delete Account
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Settings Navigation
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.settings-nav-item');
    const sections = document.querySelectorAll('.settings-section');
    
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const sectionId = this.dataset.section;
            
            // Update nav items
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            
            // Update sections
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
            
            // Update URL hash
            window.location.hash = sectionId;
        });
    });
    
    // Handle hash navigation
    if (window.location.hash) {
        const hash = window.location.hash.substring(1);
        const targetNavItem = document.querySelector(`[data-section="${hash}"]`);
        if (targetNavItem) {
            targetNavItem.click();
        }
    }
});

// Toggle Switch Functionality
document.addEventListener('DOMContentLoaded', function() {
    const toggles = document.querySelectorAll('.toggle-switch');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.querySelector('input[type="checkbox"]');
            const handle = this.querySelector('.toggle-handle');
            
            if (input && !input.disabled) {
                input.checked = !input.checked;
                
                if (input.checked) {
                    this.classList.remove('disabled');
                    this.classList.add('enabled');
                    handle.classList.remove('disabled');
                    handle.classList.add('enabled');
                } else {
                    this.classList.remove('enabled');
                    this.classList.add('disabled');
                    handle.classList.remove('enabled');
                    handle.classList.add('disabled');
                }
                
                // Handle special cases
                if (input.name === 'quiet_hours_enabled') {
                    const config = document.getElementById('quietHoursConfig');
                    if (input.checked) {
                        config.classList.remove('hidden');
                    } else {
                        config.classList.add('hidden');
                    }
                }
            }
        });
    });
});

// Profile Photo Preview
document.getElementById('profile_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.querySelector('.w-24.h-24.rounded-full').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Notification Functions
function toggleAllNotifications(enable = true) {
    const toggles = document.querySelectorAll('.toggle-switch input[type="checkbox"]:not([disabled])');
    
    toggles.forEach(input => {
        if (input.name !== 'email_purchase_confirmations') { // Skip required notifications
            input.checked = enable;
            
            const toggle = input.closest('.toggle-switch');
            const handle = toggle.querySelector('.toggle-handle');
            
            if (enable) {
                toggle.classList.remove('disabled');
                toggle.classList.add('enabled');
                handle.classList.remove('disabled');
                handle.classList.add('enabled');
            } else {
                toggle.classList.remove('enabled');
                toggle.classList.add('disabled');
                handle.classList.remove('enabled');
                handle.classList.add('disabled');
            }
        }
    });
}

// Two-Factor Authentication
function showRecoveryCodes() {
    alert('Recovery codes functionality would be implemented here');
}

// Subscription Functions
function openSubscribeModal() {
    alert('Subscribe modal would open here');
}

function openUpgradeModal() {
    alert('Upgrade plan modal would open here');
}

function cancelSubscription() {
    if (confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of your billing period.')) {
        // Handle subscription cancellation
        alert('Subscription cancellation would be processed here');
    }
}

function addPaymentMethod() {
    alert('Add payment method modal would open here');
}

// Account Actions
function confirmDeactivation() {
    if (confirm('Are you sure you want to temporarily deactivate your account? You can reactivate it anytime by logging in.')) {
        // Handle account deactivation
        alert('Account deactivation would be processed here');
    }
}

function confirmAccountDeletion() {
    const confirmation = prompt('Type "DELETE" to confirm permanent account deletion:');
    if (confirmation === 'DELETE') {
        if (confirm('This action is irreversible. Are you absolutely sure you want to permanently delete your account?')) {
            // Handle account deletion
            alert('Account deletion would be processed here');
        }
    }
}

// Success/Error Messages
function showMessage(message, type = 'success') {
    const alertClass = type === 'success' ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200';
    const alert = document.createElement('div');
    alert.className = `fixed top-4 right-4 p-4 rounded-lg border z-50 ${alertClass}`;
    alert.innerHTML = `
        <div class="flex items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg">×</button>
        </div>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 5000);
}

// Form Validation
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredInputs = form.querySelectorAll('input[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('border-red-500');
                isValid = false;
            } else {
                input.classList.remove('border-red-500');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showMessage('Please fill in all required fields.', 'error');
        }
    });
});
</script>
@endpush

@endsection
