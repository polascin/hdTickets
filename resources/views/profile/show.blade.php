<x-modern-app-layout title="My Profile">
    <x-slot name="headerActions">
        <div class="flex space-x-3">
            <x-ui.button href="{{ route('profile.edit') }}" variant="primary" icon="edit" :loading="false"
                aria-label="Edit your profile information">
                Edit Profile
            </x-ui.button>

            <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="secondary" icon="chart-bar"
                aria-label="View your activity dashboard">
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

    @if (session('status') === 'password-updated')
        <x-ui.alert variant="success" title="Security Updated!" dismissible="true" class="mb-6">
            Your password has been updated successfully.
        </x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="error" title="Error" dismissible="true" class="mb-6">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <div class="space-y-6" data-user-id="{{ $user->id }}"
        data-profile-percentage="{{ $profileCompletion['percentage'] }}">
        <!-- Profile Overview Card -->
        <x-ui.card variant="elevated" class="relative overflow-hidden profile-card">
            <!-- Background Pattern -->
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full opacity-50"
                aria-hidden="true"></div>
            <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-24 h-24 bg-gradient-to-tr from-green-100 to-blue-100 rounded-full opacity-30"
                aria-hidden="true"></div>

            <x-ui.card-content class="relative p-8">
                <div class="flex flex-col md:flex-row items-start md:items-center space-y-6 md:space-y-0 md:space-x-8">
                    <!-- Avatar Section with Upload Functionality -->
                    <div class="flex-shrink-0 group relative" role="img" aria-label="Profile photo section">
                        <div class="relative avatar-container">
                            @if ($user->profile_photo_path)
                                <img src="{{ $user->profile_photo_url }}" alt="Profile photo of {{ $user->name }}"
                                    class="avatar-image w-28 h-28 rounded-full border-4 border-white shadow-lg object-cover transition-all duration-200 group-hover:shadow-xl"
                                    loading="lazy">
                            @else
                                <div class="avatar-placeholder w-28 h-28 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg transition-all duration-200 group-hover:shadow-xl"
                                    aria-label="Profile initials">
                                    <span class="text-3xl font-bold text-white select-none">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </span>
                                </div>
                            @endif

                            <!-- Online Status Indicator -->
                            <div class="absolute -bottom-2 -right-2" aria-label="Online status" role="status">
                                <div class="relative">
                                    <div class="w-6 h-6 bg-green-500 rounded-full border-3 border-white shadow-sm"
                                        title="Currently online"></div>
                                    <div class="absolute inset-0 bg-green-500 rounded-full animate-ping opacity-75"
                                        aria-hidden="true"></div>
                                </div>
                            </div>

                            <!-- Upload Overlay -->
                            <button type="button"
                                class="upload-overlay absolute inset-0 bg-black bg-opacity-50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200 cursor-pointer focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                onclick="document.getElementById('profile-photo-upload').click()"
                                aria-label="Click to upload new profile photo" title="Upload new photo">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>

                            <!-- File Input -->
                            <input type="file" id="profile-photo-upload" class="sr-only"
                                accept="image/jpeg,image/jpg,image/png,image/webp"
                                onchange="ProfileManager.handlePhotoUpload(this)"
                                aria-label="Select profile photo file">
                        </div>
                    </div>

                    <!-- Enhanced User Info Section -->
                    <div class="flex-1 user-info-section">
                        <div class="space-y-4">
                            <header class="user-header">
                                <h1 class="hd-heading-1 !mb-2 flex items-center">
                                    {{ $user->name }}
                                    @if ($securityStatus['email_verified'])
                                        <span class="ml-3 inline-flex items-center" title="Email address verified"
                                            aria-label="Verified account">
                                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"
                                                aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </h1>
                                <p class="hd-text-base text-gray-600 mb-3">{{ $user->email }}</p>

                                <!-- Status Badges -->
                                <div class="flex flex-wrap items-center gap-2" role="group"
                                    aria-label="Account status badges">
                                    <x-ui.badge variant="primary" pill="true" class="font-medium">
                                        {{ ucfirst($user->role) }}
                                    </x-ui.badge>

                                    @if ($securityStatus['email_verified'])
                                        <x-ui.badge variant="success" size="sm" class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                                aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                            Verified
                                        </x-ui.badge>
                                    @else
                                        <x-ui.badge variant="warning" size="sm">
                                            Verification Pending
                                        </x-ui.badge>
                                    @endif

                                    @if ($securityStatus['two_factor_enabled'])
                                        <x-ui.badge variant="purple" size="sm" class="flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                                </path>
                                            </svg>
                                            2FA Enabled
                                        </x-ui.badge>
                                    @endif
                                </div>
                            </header>

                            <!-- Enhanced Profile Completion -->
                            <section
                                class="profile-completion bg-gradient-to-r from-blue-50 to-purple-50 rounded-xl p-5 border border-blue-100"
                                role="region" aria-label="Profile completion status">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="hd-text-small font-semibold text-gray-800 flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Profile Completion
                                    </span>
                                    <span
                                        class="hd-text-small font-bold text-blue-700 bg-blue-100 px-3 py-1 rounded-full"
                                        aria-label="Completion percentage">
                                        {{ $profileCompletion['percentage'] }}%
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="w-full bg-gray-200 rounded-full h-3 mb-2 overflow-hidden"
                                    role="progressbar" aria-valuenow="{{ $profileCompletion['percentage'] }}"
                                    aria-valuemin="0" aria-valuemax="100" aria-label="Profile completion progress">
                                    <div class="progress-bar h-3 rounded-full transition-all duration-700 ease-out {{ $profileCompletion['percentage'] >= 90 ? 'bg-gradient-to-r from-green-500 to-green-600' : ($profileCompletion['percentage'] >= 70 ? 'bg-gradient-to-r from-blue-500 to-purple-600' : 'bg-gradient-to-r from-yellow-500 to-orange-500') }}"
                                        data-progress="{{ $profileCompletion['percentage'] }}"
                                        style="width: {{ $profileCompletion['percentage'] }}%">
                                    </div>
                                </div>

                                <!-- Status Message -->
                                @if (!$securityStatus['profile_complete'])
                                    <div class="flex items-start space-x-2" role="alert">
                                        <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="hd-text-small text-amber-700 font-medium">
                                            Complete your profile to unlock all HD Tickets features
                                        </p>
                                    </div>
                                @else
                                    <div class="flex items-start space-x-2" role="status">
                                        <svg class="w-4 h-4 text-green-600 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                        <p class="hd-text-small text-green-700 font-medium">
                                            🎉 Your profile is complete! Enjoy full access to all features.
                                        </p>
                                    </div>
                                @endif
                            </section>
                        </div>
                    </div>

                    <!-- User Stats Section -->
                    <section class="grid grid-cols-2 gap-4 mt-6" role="region" aria-label="User statistics">
                        <div
                            class="stat-card bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl p-5 border border-green-200 transition-all duration-200 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="hd-text-xs font-medium text-gray-600 uppercase tracking-wider">Active
                                        Tickets</p>
                                    <h3 class="text-2xl font-bold text-green-700 mt-1"
                                        aria-label="Active tickets count">
                                        {{ $userStats['active_tickets'] }}
                                    </h3>
                                </div>
                                <div class="bg-green-600 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div
                            class="stat-card bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-5 border border-blue-200 transition-all duration-200 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="hd-text-xs font-medium text-gray-600 uppercase tracking-wider">Total
                                        Tickets</p>
                                    <h3 class="text-2xl font-bold text-blue-700 mt-1"
                                        aria-label="Total tickets count">
                                        {{ $userStats['total_tickets'] }}
                                    </h3>
                                </div>
                                <div class="bg-blue-600 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                        </path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div
                            class="stat-card bg-gradient-to-br from-purple-50 to-violet-100 rounded-xl p-5 border border-purple-200 transition-all duration-200 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="hd-text-xs font-medium text-gray-600 uppercase tracking-wider">Resolved
                                    </p>
                                    <h3 class="text-2xl font-bold text-purple-700 mt-1"
                                        aria-label="Resolved tickets count">
                                        {{ $userStats['resolved_tickets'] }}
                                    </h3>
                                </div>
                                <div class="bg-purple-600 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div
                            class="stat-card bg-gradient-to-br from-amber-50 to-orange-100 rounded-xl p-5 border border-amber-200 transition-all duration-200 hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="hd-text-xs font-medium text-gray-600 uppercase tracking-wider">Pending
                                    </p>
                                    <h3 class="text-2xl font-bold text-amber-700 mt-1"
                                        aria-label="Pending tickets count">
                                        {{ $userStats['pending_tickets'] }}
                                    </h3>
                                </div>
                                <div class="bg-amber-600 rounded-full p-3">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Enhanced Quick Stats -->
                    <section class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6" role="region"
                        aria-label="Account activity statistics">
                        <article
                            class="quick-stat text-center bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                            <div
                                class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="hd-heading-3 !mb-1 text-blue-600"
                                aria-label="{{ $joinedDaysAgo }} {{ $joinedDaysAgo === 1 ? 'day' : 'days' }} active">
                                {{ $joinedDaysAgo }}
                            </div>
                            <div class="hd-text-small text-gray-600 font-medium">
                                {{ $joinedDaysAgo === 1 ? 'Day Active' : 'Days Active' }}
                            </div>
                        </article>

                        <article
                            class="quick-stat text-center bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                            <div
                                class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                    </path>
                                </svg>
                            </div>
                            <div class="hd-heading-3 !mb-1 text-green-600"
                                aria-label="{{ $user->login_count ?? 0 }} {{ ($user->login_count ?? 0) === 1 ? 'login' : 'logins' }}">
                                {{ $user->login_count ?? 0 }}
                            </div>
                            <div class="hd-text-small text-gray-600 font-medium">
                                {{ ($user->login_count ?? 0) === 1 ? 'Login' : 'Logins' }}
                            </div>
                        </article>

                        <article
                            class="quick-stat text-center bg-white rounded-xl p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                            <div
                                class="flex items-center justify-center w-12 h-12 mx-auto mb-3 bg-purple-100 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="hd-heading-3 !mb-1 text-purple-600"
                                aria-label="Last login: {{ $user->last_login_at ? $user->last_login_at->diffForHumans(null, true) : 'Never' }}">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans(null, true) : 'Never' }}
                            </div>
                            <div class="hd-text-small text-gray-600 font-medium">Last Login</div>
                        </article>
                    </section>
                </div>
            </x-ui.card-content>
        </x-ui.card>

        <!-- Profile Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Personal Information -->
            <x-ui.card>
                <x-ui.card-header title="Personal Information">
                    <x-ui.button href="{{ route('profile.edit') }}" variant="ghost"
                        size="sm">Edit</x-ui.button>
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
                    <x-ui.button href="{{ route('profile.edit') }}" variant="ghost"
                        size="sm">Manage</x-ui.button>
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
                    <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="ghost"
                        size="sm">View
                        All</x-ui.button>
                </x-ui.card-header>
                <x-ui.card-content>
                    <div class="space-y-4">
                        <!-- Sample activity items - replace with real data -->
                        <div class="flex items-start space-x-4 py-3 border-b border-gray-100 last:border-b-0">
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
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
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
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
                            <div
                                class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
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
                        <x-ui.button href="{{ route('profile.activity.dashboard') }}" variant="outline"
                            fullWidth="true" class="justify-center">
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
