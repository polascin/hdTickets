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
        <x-ui.alert variant="success" title="Success!" dismissible="true" class="mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Profile updated successfully! Your changes have been saved.
        </x-ui.alert>
    @endif

    @if (session('status') === 'password-updated')
        <x-ui.alert variant="success" title="Security Updated!" dismissible="true" class="mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m5.707-4.293a1 1 0 010 1.414L9.414 13.414a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 0z">
                </path>
            </svg>
            Your password has been updated successfully.
        </x-ui.alert>
    @endif

    @if (session('success'))
        <x-ui.alert variant="success" title="Success!" dismissible="true" class="mb-6">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Error" dismissible="true" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div class="space-y-8" data-user-id="{{ $user->id }}"
        data-profile-percentage="{{ $profileCompletion['percentage'] }}"
        data-security-score="{{ $profileInsights['security_score'] }}">

        <!-- Enhanced Profile Header Card -->
        <x-ui.card variant="elevated" class="relative overflow-hidden profile-header-card">
            <!-- Dynamic Background Pattern -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 opacity-60"></div>
            <div
                class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-200 to-purple-200 rounded-full opacity-30 transform translate-x-16 -translate-y-8">
            </div>
            <div
                class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-green-200 to-blue-200 rounded-full opacity-20 transform -translate-x-8 translate-y-8">
            </div>

            <x-ui.card-content class="relative p-8">
                <div class="flex flex-col xl:flex-row items-start xl:items-center space-y-8 xl:space-y-0 xl:space-x-10">

                    <!-- Enhanced Avatar Section -->
                    <div class="flex-shrink-0 group relative" role="img" aria-label="Profile photo section">
                        <div class="relative avatar-container">
                            @if ($user->profile_photo_path)
                                <img src="{{ $user->profile_photo_url }}" alt="Profile photo of {{ $user->name }}"
                                    class="avatar-image w-32 h-32 rounded-full border-4 border-white shadow-xl object-cover transition-all duration-300 group-hover:shadow-2xl group-hover:scale-105"
                                    loading="lazy">
                            @else
                                <div class="avatar-placeholder w-32 h-32 bg-gradient-to-br from-blue-500 via-purple-600 to-pink-600 rounded-full flex items-center justify-center border-4 border-white shadow-xl transition-all duration-300 group-hover:shadow-2xl group-hover:scale-105"
                                    aria-label="Profile initials">
                                    <span class="text-4xl font-bold text-white select-none">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif

                            <!-- Enhanced Status Indicators -->
                            <div class="absolute -bottom-2 -right-2 flex space-x-1">
                                <!-- Online Status -->
                                <div class="relative" aria-label="Online status" role="status">
                                    <div class="w-8 h-8 bg-green-500 rounded-full border-4 border-white shadow-lg flex items-center justify-center"
                                        title="Currently online">
                                        <div class="w-3 h-3 bg-white rounded-full"></div>
                                    </div>
                                    <div class="absolute inset-0 bg-green-400 rounded-full animate-ping opacity-75">
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Upload Overlay -->
                            <button type="button"
                                class="upload-overlay absolute inset-0 bg-black bg-opacity-60 rounded-full flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer focus:opacity-100 focus:outline-none focus:ring-4 focus:ring-blue-500 focus:ring-offset-2"
                                onclick="document.getElementById('profile-photo-upload').click()"
                                aria-label="Upload new profile photo" title="Upload new photo">
                                <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="text-white text-xs font-medium">Change Photo</span>
                            </button>

                            <!-- Enhanced File Input -->
                            <input type="file" id="profile-photo-upload" class="sr-only"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                onchange="ProfileManager.handlePhotoUpload(this)"
                                aria-label="Select profile photo file">
                        </div>
                    </div>

                    <!-- Enhanced User Information -->
                    <div class="flex-1 space-y-6">
                        <header class="user-header">
                            <h1 class="text-3xl font-bold text-gray-900 flex items-center mb-2">
                                {{ $user->name }}
                                @if ($securityStatus['email_verified'])
                                    <span class="ml-3 inline-flex items-center" title="Verified account"
                                        aria-label="Verified account">
                                        <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </span>
                                @endif
                            </h1>
                            <p class="text-lg text-gray-600 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                    </path>
                                </svg>
                                {{ $user->email }}
                            </p>

                            <!-- Enhanced Status Badges -->
                            <div class="flex flex-wrap items-center gap-3 mb-6">
                                <x-ui.badge variant="primary" size="lg" class="font-semibold">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    {{ ucfirst($user->role) }}
                                </x-ui.badge>

                                @if ($securityStatus['email_verified'])
                                    <x-ui.badge variant="success" size="md" class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        Email Verified
                                    </x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="md" class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                                            </path>
                                        </svg>
                                        Email Unverified
                                    </x-ui.badge>
                                @endif

                                @if ($securityStatus['two_factor_enabled'])
                                    <x-ui.badge variant="purple" size="md" class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                        2FA Active
                                    </x-ui.badge>
                                @endif

                                <!-- Security Score Badge -->
                                @php
                                    $securityScore = $profileInsights['security_score'];
                                    $securityVariant =
                                        $securityScore >= 80 ? 'success' : ($securityScore >= 60 ? 'warning' : 'error');
                                @endphp
                                <x-ui.badge variant="{{ $securityVariant }}" size="md"
                                    class="flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m5.707-4.293a1 1 0 010 1.414L9.414 13.414a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 0z">
                                        </path>
                                    </svg>
                                    Security: {{ $securityScore }}%
                                </x-ui.badge>
                            </div>
                        </header>

                        <!-- Enhanced Profile Completion -->
                        <section
                            class="profile-completion bg-gradient-to-r from-blue-50 via-purple-50 to-pink-50 rounded-xl p-6 border border-blue-100"
                            role="region" aria-label="Profile completion status">
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-base font-semibold text-gray-800 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Profile Completion
                                </span>
                                <span
                                    class="text-sm font-bold {{ $profileCompletion['percentage'] >= 90 ? 'text-green-700 bg-green-100' : ($profileCompletion['percentage'] >= 70 ? 'text-blue-700 bg-blue-100' : 'text-amber-700 bg-amber-100') }} px-3 py-1 rounded-full"
                                    aria-label="Completion percentage">
                                    {{ $profileCompletion['percentage'] }}%
                                </span>
                            </div>

                            <!-- Enhanced Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-4 mb-3 overflow-hidden shadow-inner"
                                role="progressbar" aria-valuenow="{{ $profileCompletion['percentage'] }}"
                                aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar h-4 rounded-full transition-all duration-1000 ease-out {{ $profileCompletion['percentage'] >= 90 ? 'bg-gradient-to-r from-green-500 to-emerald-600' : ($profileCompletion['percentage'] >= 70 ? 'bg-gradient-to-r from-blue-500 to-purple-600' : 'bg-gradient-to-r from-amber-500 to-orange-500') }}"
                                    style="width: {{ $profileCompletion['percentage'] }}%">
                                    <div class="h-full bg-white bg-opacity-20 animate-pulse"></div>
                                </div>
                            </div>

                            <!-- Status Message with Actions -->
                            @if (!$securityStatus['profile_complete'])
                                <div
                                    class="flex items-start justify-between p-3 bg-amber-50 rounded-lg border border-amber-200">
                                    <div class="flex items-start space-x-2">
                                        <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-amber-800 mb-1">
                                                Complete your profile to unlock all HD Tickets features
                                            </p>
                                            <p class="text-xs text-amber-700">
                                                Missing:
                                                {{ implode(', ', array_slice($profileCompletion['missing_fields'], 0, 3)) }}{{ count($profileCompletion['missing_fields']) > 3 ? '...' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <x-ui.button href="{{ route('profile.edit') }}" size="sm" variant="outline"
                                        class="ml-4 border-amber-300 text-amber-700 hover:bg-amber-100">
                                        Complete Now
                                    </x-ui.button>
                                </div>
                            @else
                                <div class="flex items-center p-3 bg-green-50 rounded-lg border border-green-200">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-green-800">
                                        🎉 Your profile is complete! Enjoy full access to all features.
                                    </p>
                                </div>
                            @endif
                        </section>
                    </div>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Enhanced Statistics Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Statistics Grid -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Ticket Statistics -->
                <x-ui.card>
                    <x-ui.card-header title="Ticket Activity" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-6">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Active Tickets -->
                            <div class="stat-card bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-4 border border-green-200 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                                onclick="animateStatCard(this)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">
                                            Active</p>
                                        <p class="text-2xl font-bold text-green-700 group-hover:text-green-800 transition-colors"
                                            data-count="{{ $userStats['active_tickets'] }}">
                                            {{ $userStats['active_tickets'] }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-green-600 rounded-full p-2 group-hover:bg-green-700 transition-colors">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-green-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                    In progress
                                </div>
                            </div>

                            <!-- Total Tickets -->
                            <div class="stat-card bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-4 border border-blue-200 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                                onclick="animateStatCard(this)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">
                                            Total</p>
                                        <p class="text-2xl font-bold text-blue-700 group-hover:text-blue-800 transition-colors"
                                            data-count="{{ $userStats['total_tickets'] }}">
                                            {{ $userStats['total_tickets'] }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-blue-600 rounded-full p-2 group-hover:bg-blue-700 transition-colors">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-blue-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2z">
                                        </path>
                                    </svg>
                                    All time
                                </div>
                            </div>

                            <!-- Resolved Tickets -->
                            <div class="stat-card bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl p-4 border border-purple-200 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                                onclick="animateStatCard(this)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">
                                            Resolved</p>
                                        <p class="text-2xl font-bold text-purple-700 group-hover:text-purple-800 transition-colors"
                                            data-count="{{ $userStats['resolved_tickets'] }}">
                                            {{ $userStats['resolved_tickets'] }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-purple-600 rounded-full p-2 group-hover:bg-purple-700 transition-colors">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-purple-600">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                    Completed
                                </div>
                            </div>

                            <!-- Pending Tickets -->
                            <div class="stat-card bg-gradient-to-br from-amber-50 to-orange-100 rounded-xl p-4 border border-amber-200 hover:shadow-lg transition-all duration-200 cursor-pointer group"
                                onclick="animateStatCard(this)">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-medium text-gray-600 uppercase tracking-wider mb-1">
                                            Pending</p>
                                        <p class="text-2xl font-bold text-amber-700 group-hover:text-amber-800 transition-colors"
                                            data-count="{{ $userStats['pending_tickets'] }}">
                                            {{ $userStats['pending_tickets'] }}
                                        </p>
                                    </div>
                                    <div
                                        class="bg-amber-600 rounded-full p-2 group-hover:bg-amber-700 transition-colors">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2 flex items-center text-xs text-amber-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Waiting
                                </div>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Account Activity Stats -->
                <x-ui.card>
                    <x-ui.card-header title="Account Activity" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-6">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                            <div
                                class="text-center bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-blue-100 rounded-full">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-3xl font-bold text-blue-600 mb-2"
                                    data-count="{{ $userStats['joined_days_ago'] }}">
                                    {{ $userStats['joined_days_ago'] }}
                                </div>
                                <div class="text-sm text-gray-600 font-medium">
                                    {{ $userStats['joined_days_ago'] === 1 ? 'Day Active' : 'Days Active' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Member since {{ $user->created_at->format('M Y') }}
                                </div>
                            </div>

                            <div
                                class="text-center bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full">
                                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                </div>
                                <div class="text-3xl font-bold text-green-600 mb-2"
                                    data-count="{{ $userStats['login_count'] }}">
                                    {{ $userStats['login_count'] }}
                                </div>
                                <div class="text-sm text-gray-600 font-medium">
                                    {{ $userStats['login_count'] === 1 ? 'Login' : 'Total Logins' }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Since account creation
                                </div>
                            </div>

                            <div
                                class="text-center bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                                <div
                                    class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-purple-100 rounded-full">
                                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="text-lg font-bold text-purple-600 mb-2">
                                    {{ $userStats['last_login_display'] }}
                                </div>
                                <div class="text-sm text-gray-600 font-medium">Last Login</div>
                                @if ($user->last_login_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $user->last_login_at->format('M j, Y g:i A') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">

                <!-- Profile Insights & Recommendations -->
                @if (!empty($profileInsights['recommendations']))
                    <x-ui.card>
                        <x-ui.card-header title="Recommendations" class="border-b border-gray-200">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3l1.09 3.26L16 8l-2.91 1.74L12 13l-1.09-3.26L8 8l2.91-1.74L12 3z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9"></path>
                            </svg>
                        </x-ui.card-header>
                        <x-ui.card-content class="pt-4">
                            <div class="space-y-4">
                                @foreach ($profileInsights['recommendations'] as $recommendation)
                                    <div
                                        class="flex items-start space-x-3 p-4 {{ $recommendation['priority'] === 'high' ? 'bg-red-50 border border-red-200' : ($recommendation['priority'] === 'medium' ? 'bg-yellow-50 border border-yellow-200' : 'bg-blue-50 border border-blue-200') }} rounded-lg">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-8 h-8 {{ $recommendation['priority'] === 'high' ? 'bg-red-100' : ($recommendation['priority'] === 'medium' ? 'bg-yellow-100' : 'bg-blue-100') }} rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 {{ $recommendation['priority'] === 'high' ? 'text-red-600' : ($recommendation['priority'] === 'medium' ? 'text-yellow-600' : 'text-blue-600') }}"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if ($recommendation['icon'] === 'user-circle')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                                        </path>
                                                    @elseif ($recommendation['icon'] === 'mail')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                                        </path>
                                                    @elseif ($recommendation['icon'] === 'shield-check')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M9 12l2 2 4-4m5.707-4.293a1 1 0 010 1.414L9.414 13.414a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 0z">
                                                        </path>
                                                    @elseif ($recommendation['icon'] === 'key')
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z">
                                                        </path>
                                                    @endif
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4
                                                class="text-sm font-semibold {{ $recommendation['priority'] === 'high' ? 'text-red-800' : ($recommendation['priority'] === 'medium' ? 'text-yellow-800' : 'text-blue-800') }} mb-1">
                                                {{ $recommendation['title'] }}
                                            </h4>
                                            <p
                                                class="text-xs {{ $recommendation['priority'] === 'high' ? 'text-red-700' : ($recommendation['priority'] === 'medium' ? 'text-yellow-700' : 'text-blue-700') }} mb-3">
                                                {{ $recommendation['description'] }}
                                            </p>
                                            @if ($recommendation['route'])
                                                <x-ui.button href="{{ route($recommendation['route']) }}"
                                                    size="xs"
                                                    variant="{{ $recommendation['priority'] === 'high' ? 'error' : ($recommendation['priority'] === 'medium' ? 'warning' : 'primary') }}">
                                                    {{ $recommendation['action'] }}
                                                </x-ui.button>
                                            @else
                                                <x-ui.button size="xs" variant="outline"
                                                    class="cursor-not-allowed" disabled>
                                                    {{ $recommendation['action'] }}
                                                </x-ui.button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endif

                <!-- Security Overview -->
                <x-ui.card>
                    <x-ui.card-header title="Security Overview" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.707-4.293a1 1 0 010 1.414L9.414 13.414a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L8 10.586l7.293-7.293a1 1 0 011.414 0z">
                            </path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-4">
                        <!-- Security Score -->
                        <div class="text-center mb-6">
                            <div class="relative inline-flex items-center justify-center w-24 h-24 mb-4">
                                <svg class="w-24 h-24 transform -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-gray-200" stroke="currentColor" stroke-width="3"
                                        fill="none"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831">
                                    </path>
                                    <path
                                        class="{{ $profileInsights['security_score'] >= 80 ? 'text-green-500' : ($profileInsights['security_score'] >= 60 ? 'text-yellow-500' : 'text-red-500') }}"
                                        stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round"
                                        stroke-dasharray="{{ $profileInsights['security_score'] }}, 100"
                                        d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831">
                                    </path>
                                </svg>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span
                                        class="text-2xl font-bold {{ $profileInsights['security_score'] >= 80 ? 'text-green-600' : ($profileInsights['security_score'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $profileInsights['security_score'] }}%
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">Security Score</h3>
                            <p class="text-sm text-gray-600">
                                @if ($profileInsights['security_score'] >= 80)
                                    Excellent security setup
                                @elseif ($profileInsights['security_score'] >= 60)
                                    Good security, room for improvement
                                @else
                                    Security needs attention
                                @endif
                            </p>
                        </div>

                        <!-- Security Checklist -->
                        <div class="space-y-3">
                            <div
                                class="flex items-center justify-between py-2 px-3 {{ $securityStatus['email_verified'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }} rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-5 h-5 {{ $securityStatus['email_verified'] ? 'text-green-600' : 'text-gray-400' }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-sm font-medium {{ $securityStatus['email_verified'] ? 'text-green-800' : 'text-gray-700' }}">Email
                                        Verified</span>
                                </div>
                                @if ($securityStatus['email_verified'])
                                    <x-ui.badge variant="success" size="xs">✓</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning" size="xs">Pending</x-ui.badge>
                                @endif
                            </div>

                            <div
                                class="flex items-center justify-between py-2 px-3 {{ $securityStatus['two_factor_enabled'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }} rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-5 h-5 {{ $securityStatus['two_factor_enabled'] ? 'text-green-600' : 'text-gray-400' }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-sm font-medium {{ $securityStatus['two_factor_enabled'] ? 'text-green-800' : 'text-gray-700' }}">Two-Factor
                                        Auth</span>
                                </div>
                                @if ($securityStatus['two_factor_enabled'])
                                    <x-ui.badge variant="success" size="xs">✓</x-ui.badge>
                                @else
                                    <x-ui.badge variant="error" size="xs">Disabled</x-ui.badge>
                                @endif
                            </div>

                            <div
                                class="flex items-center justify-between py-2 px-3 {{ $securityStatus['profile_complete'] ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }} rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-5 h-5 {{ $securityStatus['profile_complete'] ? 'text-green-600' : 'text-gray-400' }}">
                                        <svg fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span
                                        class="text-sm font-medium {{ $securityStatus['profile_complete'] ? 'text-green-800' : 'text-gray-700' }}">Profile
                                        Complete</span>
                                </div>
                                @if ($securityStatus['profile_complete'])
                                    <x-ui.badge variant="success" size="xs">✓</x-ui.badge>
                                @else
                                    <x-ui.badge variant="warning"
                                        size="xs">{{ $profileCompletion['percentage'] }}%</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        <!-- Security Actions -->
                        <div class="pt-4 border-t border-gray-200 mt-4">
                            <x-ui.button href="{{ route('profile.security') }}" variant="outline" size="sm"
                                class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Security Settings
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Recent Activity Summary -->
                <x-ui.card>
                    <x-ui.card-header title="Recent Activity" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-4">
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                <span class="text-gray-600">Last login:</span>
                                <span class="font-medium text-gray-900">{{ $recentActivity['last_activity'] }}</span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
                                <span class="text-gray-600">Total logins:</span>
                                <span class="font-medium text-gray-900">{{ $recentActivity['login_count'] }}</span>
                            </div>
                            <div class="flex items-center space-x-3 text-sm">
                                <div class="w-2 h-2 bg-purple-400 rounded-full"></div>
                                <span class="text-gray-600">Profile updated:</span>
                                <span
                                    class="font-medium text-gray-900">{{ $recentActivity['account_changes'] }}</span>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 mt-4">
                            <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="ghost"
                                size="sm" class="w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                View Full Activity
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>

        <!-- Enhanced Profile Information Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">

            <!-- Personal Information -->
            <div class="lg:col-span-2">
                <x-ui.card>
                    <x-ui.card-header title="Personal Information" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h4
                                    class="text-sm font-semibold text-gray-900 uppercase tracking-wide border-b border-gray-200 pb-2">
                                    Basic Details</h4>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">Full Name</span>
                                        <span class="text-sm text-gray-900 font-medium">{{ $user->name }}</span>
                                    </div>

                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">Email Address</span>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-900 font-medium">{{ $user->email }}</span>
                                            @if ($user->hasVerifiedEmail())
                                                <x-ui.badge variant="success" size="xs">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                    Verified
                                                </x-ui.badge>
                                            @else
                                                <x-ui.badge variant="warning" size="xs">Unverified</x-ui.badge>
                                            @endif
                                        </div>
                                    </div>

                                    @if ($user->phone)
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm font-medium text-gray-600">Phone</span>
                                            <span class="text-sm text-gray-900 font-medium">{{ $user->phone }}</span>
                                        </div>
                                    @endif

                                    @if ($user->department)
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm font-medium text-gray-600">Department</span>
                                            <x-ui.badge variant="primary"
                                                size="sm">{{ $user->department }}</x-ui.badge>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="space-y-4">
                                <h4
                                    class="text-sm font-semibold text-gray-900 uppercase tracking-wide border-b border-gray-200 pb-2">
                                    Account Details</h4>

                                <div class="space-y-3">
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">User Role</span>
                                        <x-ui.badge variant="{{ $user->is_admin ? 'error' : 'primary' }}"
                                            size="sm">
                                            {{ $user->is_admin ? 'Administrator' : 'User' }}
                                        </x-ui.badge>
                                    </div>

                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">Member Since</span>
                                        <span
                                            class="text-sm text-gray-900 font-medium">{{ $user->created_at->format('M j, Y') }}</span>
                                    </div>

                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">Account Status</span>
                                        <x-ui.badge variant="{{ $user->is_active ? 'success' : 'error' }}"
                                            size="sm">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </x-ui.badge>
                                    </div>

                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-600">Time Zone</span>
                                        <span
                                            class="text-sm text-gray-900 font-medium">{{ $user->timezone ?? 'UTC' }}</span>
                                    </div>

                                    @if ($user->last_login_at)
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm font-medium text-gray-600">Last Active</span>
                                            <span class="text-sm text-gray-900 font-medium"
                                                title="{{ $user->last_login_at->format('M j, Y g:i A') }}">
                                                {{ $user->last_login_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Profile Actions -->
                        <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 mt-6">
                            <x-ui.button href="{{ route('profile.edit') }}" variant="primary" size="sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                Edit Profile
                            </x-ui.button>

                            <x-ui.button href="{{ route('profile.security') }}" variant="outline" size="sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                    </path>
                                </svg>
                                Security Settings
                            </x-ui.button>

                            <x-ui.button href="{{ route('profile.preferences') }}" variant="ghost" size="sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Preferences
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Quick Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions Card -->
                <x-ui.card>
                    <x-ui.card-header title="Quick Actions" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-4">
                        <div class="space-y-3">
                            <x-ui.button href="{{ route('tickets.create') }}" variant="primary" size="sm"
                                class="w-full justify-start">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4"></path>
                                </svg>
                                New Ticket
                            </x-ui.button>

                            <x-ui.button href="{{ route('tickets.my') }}" variant="outline" size="sm"
                                class="w-full justify-start">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                    </path>
                                </svg>
                                My Tickets
                            </x-ui.button>

                            <x-ui.button href="{{ route('profile.activity') }}" variant="ghost" size="sm"
                                class="w-full justify-start">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                    </path>
                                </svg>
                                Activity Log
                            </x-ui.button>

                            <x-ui.button href="{{ route('knowledge-base.index') }}" variant="ghost" size="sm"
                                class="w-full justify-start">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                    </path>
                                </svg>
                                Knowledge Base
                            </x-ui.button>

                            <x-ui.button href="{{ route('support.contact') }}" variant="ghost" size="sm"
                                class="w-full justify-start">
                                <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                Get Support
                            </x-ui.button>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Profile Tips -->
                <x-ui.card>
                    <x-ui.card-header title="Profile Tips" class="border-b border-gray-200">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z">
                            </path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content class="pt-4">
                        <div class="space-y-4">
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-yellow-800">Complete Your Profile</h3>
                                        <p class="mt-1 text-sm text-yellow-700">A complete profile helps our support
                                            team provide better assistance.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Enable Two-Factor Auth</h3>
                                        <p class="mt-1 text-sm text-blue-700">Secure your account with an extra layer
                                            of protection.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <!-- Enhanced Footer Actions -->
            <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-100 rounded-2xl border border-blue-200 p-8">
                <div class="text-center">
                    <div class="flex justify-center mb-4">
                        <div class="bg-blue-600 rounded-full p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Need Help?</h2>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">
                        Our support team is here to help you with any questions or issues you might have.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <x-ui.button href="{{ route('support.contact') }}" variant="primary" size="md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                            Contact Support
                        </x-ui.button>
                        <x-ui.button href="{{ route('knowledge-base.index') }}" variant="outline" size="md">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            Browse Docs
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced JavaScript for Interactive Features -->
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Animate statistics cards
                    window.animateStatCard = function(element) {
                        element.classList.add('transform', 'scale-105');
                        setTimeout(() => {
                            element.classList.remove('transform', 'scale-105');
                        }, 200);

                        // Animate counter
                        const counter = element.querySelector('[data-count]');
                        if (counter) {
                            const target = parseInt(counter.getAttribute('data-count'));
                            const current = parseInt(counter.textContent);
                            if (current !== target) {
                                animateCounter(counter, current, target, 1000);
                            }
                        }
                    };

                    // Counter animation
                    function animateCounter(element, start, end, duration) {
                        const startTime = performance.now();

                        function updateCounter(currentTime) {
                            const elapsed = currentTime - startTime;
                            const progress = Math.min(elapsed / duration, 1);
                            const current = Math.floor(start + (end - start) * progress);

                            element.textContent = current;

                            if (progress < 1) {
                                requestAnimationFrame(updateCounter);
                            }
                        }

                        requestAnimationFrame(updateCounter);
                    }

                    // Profile picture upload preview
                    const fileInput = document.querySelector('#profile-picture-upload');
                    const profileImage = document.querySelector('#profile-image-preview');

                    if (fileInput && profileImage) {
                        fileInput.addEventListener('change', function(e) {
                            const file = e.target.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    profileImage.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            }
                        });
                    }

                    console.log('Enhanced profile page JavaScript loaded successfully');
                });
            </script>
        @endpush

</x-modern-app-layout>
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
                    @if ($user->email_verified_at)
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
                @if ($user->two_factor_secret)
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
                @if (Auth::user()->isAdmin() || Auth::user()->isAgent())
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
@if (Auth::user()->isAdmin() || Auth::user()->isAgent())
    <x-ui.card>
        <x-ui.card-header title="Recent Activity">
            <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="ghost" size="sm">View
                All</x-ui.button>
        </x-ui.card-header>
        <x-ui.card-content>
            <div class="space-y-4">
                <!-- Sample activity items - replace with real data -->
                <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-5 5v-5zM12 17H7a3 3 0 01-3-3V5a3 3 0 013-3h5"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="hd-text-base font-medium">Created new ticket alert</p>
                        <p class="hd-text-small text-gray-600">Lakers vs Warriors • 2 hours ago</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="hd-text-base font-medium">Profile updated successfully</p>
                        <p class="hd-text-small text-gray-600">Contact information • 1 day ago</p>
                    </div>
                </div>

                <div class="flex items-start space-x-4 py-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
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
            <x-ui.button href="{{ route('profile.edit') }}" variant="outline" fullWidth="true"
                class="justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Edit Profile
            </x-ui.button>

            <x-ui.button href="{{ route('profile.security') }}" variant="outline" fullWidth="true"
                class="justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                    </path>
                </svg>
                Security
            </x-ui.button>

            <x-ui.button href="{{ route('preferences.index') }}" variant="outline" fullWidth="true"
                class="justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                    </path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Preferences
            </x-ui.button>

            @if (Auth::user()->isAdmin() || Auth::user()->isAgent())
                <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="outline" fullWidth="true"
                    class="justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    Activity
                </x-ui.button>
            @endif
        </div>
    </x-ui.card-content>
</x-ui.card>
</div>

<!-- JavaScript for Enhanced Functionality -->
@push('scripts')
    <script>
        /**
         * Profile Page JavaScript Module
         * Handles photo upload, notifications, and UI interactions
         */
        class ProfileManager {
            constructor() {
                this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                this.uploadRoute = '{{ route('profile.photo.upload') }}';
                this.userName = '{{ $user->name }}';
                this.init();
            }

            init() {
                this.setupProgressAnimation();
                this.setupStatCardAnimations();
            }

            uploadPhoto(file) {
                // Validate file
                if (!this.validateFile(file)) return;

                const uploadOverlay = document.querySelector('.group .absolute.inset-0');

                // Show loading state
                if (uploadOverlay) {
                    uploadOverlay.innerHTML = `
                    <div class="flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-2 border-white border-t-transparent"></div>
                    </div>
                `;
                }

                // Create FormData
                const formData = new FormData();
                formData.append('photo', file);
                formData.append('_token', this.csrfToken);

                // Upload photo
                fetch(this.uploadRoute, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.updateAvatar(data.photo_url);
                            this.showNotification(data.message, 'success');
                        } else {
                            this.showNotification(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Upload error:', error);
                        this.showNotification('Failed to upload photo. Please try again.', 'error');
                    })
                    .finally(() => {
                        this.resetUploadOverlay(uploadOverlay);
                    });
            }

            validateFile(file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                const maxSize = 5 * 1024 * 1024; // 5MB

                if (!validTypes.includes(file.type)) {
                    this.showNotification('Please select a valid image file (JPEG, PNG, GIF, or WebP)', 'error');
                    return false;
                }

                if (file.size > maxSize) {
                    this.showNotification('Image size must be less than 5MB', 'error');
                    return false;
                }

                return true;
            }

            updateAvatar(photoUrl) {
                const avatarImg = document.querySelector(`img[alt="${this.userName}"]`);
                if (avatarImg) {
                    avatarImg.src = photoUrl;
                    avatarImg.classList.add('animate-pulse');
                    setTimeout(() => avatarImg.classList.remove('animate-pulse'), 500);
                }
            }

            resetUploadOverlay(overlay) {
                if (!overlay) return;

                overlay.innerHTML = `
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            `;
            }

            showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                const typeConfig = {
                    success: {
                        bg: 'bg-green-500',
                        border: 'border-green-600',
                        icon: this.getSuccessIcon()
                    },
                    error: {
                        bg: 'bg-red-500',
                        border: 'border-red-600',
                        icon: this.getErrorIcon()
                    },
                    info: {
                        bg: 'bg-blue-500',
                        border: 'border-blue-600',
                        icon: this.getInfoIcon()
                    }
                };

                const config = typeConfig[type] || typeConfig.info;

                notification.className = `
                fixed top-4 right-4 z-50 p-4 rounded-lg shadow-xl transition-all duration-300 transform translate-x-full 
                max-w-sm ${config.bg} text-white border-l-4 ${config.border}
            `;

                notification.innerHTML = `
                <div class="flex items-center space-x-3" role="alert" aria-live="polite">
                    ${config.icon}
                    <span class="font-medium">${message}</span>
                    <button onclick="this.closest('.fixed').remove()" 
                            class="ml-auto text-white hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 rounded"
                            aria-label="Close notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

                document.body.appendChild(notification);

                // Animate in
                requestAnimationFrame(() => {
                    notification.classList.remove('translate-x-full');
                });

                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => notification.remove(), 300);
                }, 5000);
            }

            getSuccessIcon() {
                return `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>`;
            }

            getErrorIcon() {
                return `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>`;
            }

            getInfoIcon() {
                return `<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>`;
            }

            setupProgressAnimation() {
                const progressBar = document.querySelector('[data-progress]');
                if (!progressBar) return;

                const percentage = progressBar.getAttribute('data-progress');
                progressBar.style.width = '0%';

                setTimeout(() => {
                    progressBar.style.width = percentage + '%';
                }, 500);
            }

            setupStatCardAnimations() {
                const statCards = document.querySelectorAll('.stat-card, .quick-stat');

                statCards.forEach(card => {
                    card.addEventListener('mouseenter', () => {
                        card.style.transform = 'translateY(-2px)';
                    });

                    card.addEventListener('mouseleave', () => {
                        card.style.transform = 'translateY(0)';
                    });
                });
            }
        }

        // Global function for backward compatibility
        window.handleProfilePhotoChange = function(input) {
            if (input.files && input.files[0]) {
                window.profileManager.uploadPhoto(input.files[0]);
            }
        };

        // Global notification function
        window.showNotification = function(message, type = 'info') {
            window.profileManager.showNotification(message, type);
        };

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.profileManager = new ProfileManager();
        });
    </script>
@endpush
</x-modern-app-layout>
