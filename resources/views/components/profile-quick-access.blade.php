@props([
    'user' => null,
    'position' => 'right', // 'left', 'right'
])

@php
    $user = $user ?? Auth::user();
    if ($user) {
        $profileDisplay = $user->getProfileDisplay();
        $completion = $user->getProfileCompletion();
    } else {
        $profileDisplay = ['has_picture' => false, 'initials' => 'G', 'display_name' => 'Guest', 'name' => 'Guest'];
        $completion = ['percentage' => 0, 'status' => 'incomplete', 'missing_fields' => [], 'is_complete' => false];
    }

    // Profile sections with quick access
    $quickAccessSections = [
        [
            'route' => 'profile.show',
            'label' => 'Profile Overview',
            'icon' => 'user',
            'description' => 'View your profile summary',
            'badge' => null,
        ],
        [
            'route' => 'profile.edit',
            'label' => 'Personal Info',
            'icon' => 'id-card',
            'description' => 'Update name, bio, and details',
            'badge' => $completion['percentage'] < 90 ? 'incomplete' : null,
        ],
        [
            'route' => 'profile.security',
            'label' => 'Security Settings',
            'icon' => 'shield-check',
            'description' => '2FA, sessions, and security',
            'badge' => $user && !$user->two_factor_enabled ? 'action-needed' : null,
        ],
        [
            'route' => 'preferences.index',
            'label' => 'Preferences',
            'icon' => 'cog',
            'description' => 'Customize your experience',
            'badge' => null,
        ],
        [
            'route' => 'profile.activity.dashboard',
            'label' => 'Activity Dashboard',
            'icon' => 'chart-bar',
            'description' => 'View login history and stats',
            'badge' => null,
        ],
    ];
@endphp

<div class="profile-quick-access-dropdown" x-data="profileQuickAccess()">
    <!-- Profile Section in User Dropdown -->
    <div class="px-4 py-3 border-b border-gray-200">
        <!-- Profile Header with Completion -->
        <div class="flex items-center space-x-3 mb-3">
            <div class="relative">
                @if ($profileDisplay['has_picture'])
                    <img class="w-12 h-12 rounded-full object-cover border-2 border-gray-200"
                        src="{{ $profileDisplay['picture_url'] }}" alt="{{ $profileDisplay['display_name'] }}">
                @else
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-2 border-white shadow-md">
                        <span class="text-lg font-bold text-white">
                            {{ $profileDisplay['initials'] }}
                        </span>
                    </div>
                @endif

                <!-- Profile completion mini indicator -->
                <div class="absolute -bottom-1 -right-1">
                    <x-profile-completion-indicator :user="$user" position="dropdown" :showLabel="false"
                        size="xs" />
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">
                    {{ $profileDisplay['display_name'] }}
                </p>
                <p class="text-xs text-gray-600 truncate">
                    {{ $user ? $user->email : 'guest@example.com' }}
                </p>
                @if ($user && $user->role)
                    <span
                        class="inline-block mt-1 px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ ucfirst($user->role) }}
                    </span>
                @endif
            </div>
        </div>

        <!-- Profile Completion Summary -->
        @if ($completion['percentage'] < 90)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-xs font-medium text-yellow-800">
                            Profile {{ $completion['percentage'] }}% complete
                        </p>
                        <p class="text-xs text-yellow-700">
                            {{ count($completion['missing_fields']) }}
                            field{{ count($completion['missing_fields']) > 1 ? 's' : '' }} remaining
                        </p>
                    </div>
                </div>
            </div>
        @elseif($completion['percentage'] >= 90)
            <div class="bg-green-50 border border-green-200 rounded-lg p-2">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-xs font-medium text-green-800">
                        Profile is complete! 🎉
                    </p>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Access Menu -->
    <div class="py-1">
        <div class="px-4 py-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quick Access</p>
        </div>

        @foreach ($quickAccessSections as $section)
            <a href="{{ route($section['route']) }}"
                class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out group
                      {{ request()->routeIs($section['route']) ? 'bg-blue-50 text-blue-700 border-r-2 border-blue-500' : '' }}">

                <!-- Icon -->
                <div class="flex-shrink-0 mr-3">
                    @switch($section['icon'])
                        @case('user')
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        @break

                        @case('id-card')
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                            </svg>
                        @break

                        @case('shield-check')
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        @break

                        @case('cog')
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        @break

                        @case('chart-bar')
                            <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        @break
                    @endswitch
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium truncate">{{ $section['label'] }}</p>

                        <!-- Badge -->
                        @if ($section['badge'])
                            @switch($section['badge'])
                                @case('incomplete')
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        !
                                    </span>
                                @break

                                @case('action-needed')
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        !
                                    </span>
                                @break
                            @endswitch
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $section['description'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Quick Actions -->
    <div class="border-t border-gray-200 py-1">
        <div class="px-4 py-2">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Quick Actions</p>
        </div>

        <!-- Picture Upload -->
        @if (!$profileDisplay['has_picture'])
            <button @click="openPictureUpload()"
                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="flex-1 text-left">
                    <p class="font-medium">Upload Profile Picture</p>
                    <p class="text-xs text-gray-500">Add a photo to personalize your profile</p>
                </div>
            </button>
        @endif

        <!-- Enable 2FA -->
        @if ($user && !$user->two_factor_enabled)
            <a href="{{ route('profile.security') }}"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                <div class="flex-1 text-left">
                    <p class="font-medium">Enable Two-Factor Auth</p>
                    <p class="text-xs text-gray-500">Secure your account with 2FA</p>
                </div>
                <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    Recommended
                </span>
            </a>
        @endif

        <!-- Complete Profile -->
        @if ($completion['percentage'] < 90)
            <a href="{{ route('profile.edit') }}"
                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <div class="flex-1 text-left">
                    <p class="font-medium">Complete Profile</p>
                    <p class="text-xs text-gray-500">{{ count($completion['missing_fields']) }} fields remaining</p>
                </div>
                <span
                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    {{ $completion['percentage'] }}%
                </span>
            </a>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        function profileQuickAccess() {
            return {
                openPictureUpload() {
                    // Redirect to profile edit page with picture upload section
                    window.location.href = '{{ route('profile.edit') }}#picture-section';
                }
            }
        }
    </script>
@endpush
