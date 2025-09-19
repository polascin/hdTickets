@extends('layouts.app-v2')

@section('title', 'User Profile - ' . $user->name)
@section('description', 'Detailed view of user profile for ' . $user->name)

@push('styles')
<style>
    /* User Profile Styles */
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        position: relative;
        overflow: hidden;
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="%23ffffff" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        opacity: 0.1;
    }

    .profile-avatar {
        @apply w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg;
    }

    .info-card {
        @apply bg-white rounded-xl shadow-lg p-6 border border-gray-200;
    }

    .stat-item {
        @apply text-center p-4;
    }

    .stat-value {
        @apply text-2xl font-bold text-gray-900;
    }

    .stat-label {
        @apply text-sm text-gray-600 mt-1;
    }

    .status-badge {
        @apply px-3 py-1 text-xs font-semibold rounded-full;
    }

    .role-customer {
        @apply bg-blue-100 text-blue-800;
    }

    .role-agent {
        @apply bg-purple-100 text-purple-800;
    }

    .role-admin {
        @apply bg-red-100 text-red-800;
    }

    .role-scraper {
        @apply bg-gray-100 text-gray-800;
    }

    .subscription-active {
        @apply bg-green-100 text-green-800;
    }

    .subscription-trial {
        @apply bg-yellow-100 text-yellow-800;
    }

    .subscription-expired {
        @apply bg-red-100 text-red-800;
    }

    .subscription-none {
        @apply bg-gray-100 text-gray-800;
    }

    .timeline-item {
        @apply relative flex items-start space-x-3 pb-6;
    }

    .timeline-item:last-child {
        @apply pb-0;
    }

    .timeline-item::before {
        content: '';
        @apply absolute left-4 top-10 w-0.5 h-full bg-gray-200;
    }

    .timeline-item:last-child::before {
        @apply hidden;
    }

    .timeline-icon {
        @apply flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center z-10;
    }
</style>
@endpush

@section('content')

<div class="py-6">
    <!-- Header -->
    <div class="profile-header text-white py-8 px-6 rounded-2xl mb-8 relative z-10">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center space-x-6">
                <div class="relative">
                    <img class="profile-avatar" 
                         src="{{ $user->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7c3aed&background=f3f4f6' }}" 
                         alt="{{ $user->name }}">
                    <div class="absolute -bottom-2 -right-2 w-6 h-6 {{ $user->is_active ? 'bg-green-400' : 'bg-gray-400' }} rounded-full border-2 border-white"></div>
                </div>
                <div>
                    <h1 class="text-3xl font-bold mb-2">{{ $user->full_name }}</h1>
                    <p class="text-white/90 text-lg mb-3">{{ $user->email }}</p>
                    <div class="flex items-center space-x-4">
                        <span class="status-badge role-{{ $user->role }} text-white bg-white/20">
                            {{ ucfirst($user->role) }}
                        </span>
                        @if($user->role === 'customer')
                            @php
                                // Sample subscription logic - adjust based on your implementation
                                $hasSubscription = false; // Check actual subscription
                                $inFreeTrial = $user->created_at->diffInDays(now()) <= 7;
                                $subscriptionStatus = $hasSubscription ? 'active' : ($inFreeTrial ? 'trial' : 'none');
                            @endphp
                            <span class="status-badge subscription-{{ $subscriptionStatus }} text-white bg-white/20">
                                @if($hasSubscription)
                                    Active Subscription
                                @elseif($inFreeTrial)
                                    Free Trial
                                @else
                                    No Subscription
                                @endif
                            </span>
                        @endif
                        <span class="status-badge {{ $user->is_active ? 'bg-green-500/20 text-green-100' : 'bg-red-500/20 text-red-100' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center space-x-3 mt-6 lg:mt-0">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-4 py-2 text-white rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </a>
                @if($user->id !== auth()->id() && $user->role === 'customer')
                    <button onclick="impersonateUser({{ $user->id }})" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-4 py-2 text-white rounded-lg font-medium transition-colors">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                        </svg>
                        Impersonate
                    </button>
                @endif
                <a href="{{ route('admin.users.index') }}" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/30 px-4 py-2 text-white rounded-lg font-medium transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @php
            // Sample data - replace with actual queries
            $userStats = [
                'total_purchases' => 23,
                'monthly_usage' => 45,
                'tickets_limit' => $user->role === 'customer' ? 100 : 'Unlimited',
                'last_login' => $user->last_activity ?? $user->updated_at
            ];
        @endphp

        <div class="info-card">
            <div class="stat-item">
                <div class="stat-value text-blue-600">{{ number_format($userStats['total_purchases']) }}</div>
                <div class="stat-label">Total Purchases</div>
            </div>
        </div>

        @if($user->role === 'customer')
        <div class="info-card">
            <div class="stat-item">
                <div class="stat-value text-purple-600">{{ $userStats['monthly_usage'] }}</div>
                <div class="stat-label">This Month's Usage</div>
            </div>
        </div>

        <div class="info-card">
            <div class="stat-item">
                <div class="stat-value text-green-600">{{ $userStats['tickets_limit'] }}</div>
                <div class="stat-label">Monthly Limit</div>
            </div>
        </div>
        @endif

        <div class="info-card">
            <div class="stat-item">
                <div class="stat-value text-orange-600 text-lg">{{ $userStats['last_login']->format('M d') }}</div>
                <div class="stat-label">Last Seen</div>
                <div class="text-xs text-gray-500 mt-1">{{ $userStats['last_login']->diffForHumans() }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- User Information -->
        <div class="lg:col-span-2 space-y-8">

            <!-- User Details Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 card-grid-mobile">
                <!-- Personal Information Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Personal Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">First Name</span>
                            <span class="text-gray-900">{{ $user->name }}</span>
                        </div>
                        @if($user->surname)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">Last Name</span>
                            <span class="text-gray-900">{{ $user->surname }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">Email Address</span>
                            <span class="text-gray-900">{{ $user->email }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">User ID</span>
                            <span class="text-gray-900">#{{ $user->id }}</span>
                        </div>
                    </div>
                </div>

                <!-- Account Details Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012 0v4h4V3a1 1 0 012 0v4h2a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2h2z"></path>
                            </svg>
                            Account Details
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">Account Created</span>
                            <div class="text-right">
                                <div class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">Last Updated</span>
                            <div class="text-right">
                                <div class="text-gray-900">{{ $user->updated_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @if($user->email_verified_at)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium text-gray-700">Email Verified</span>
                            <div class="text-right">
                                <div class="text-gray-900">{{ $user->email_verified_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email_verified_at->diffForHumans() }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="mt-6 bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-3 actions-mobile">
                        <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit User
                        </a>
                        
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="{{ $user->is_active ? 'bg-yellow-500 hover:bg-yellow-700' : 'bg-green-500 hover:bg-green-700' }} text-white font-bold py-2 px-4 rounded transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($user->is_active)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        @endif
                                    </svg>
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to send password reset email to this user?')">
                                @csrf
                                <button type="submit" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Reset Password
                                </button>
                            </form>
                            
                            @if($user->role === 'customer')
                            <button onclick="impersonateUser({{ $user->id }})" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                </svg>
                                Impersonate
                            </button>
                            @endif
                            
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete User
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            @if($user->role === 'customer')
            <!-- Subscription Status -->
            <div class="info-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Subscription Status</h3>
                
                @php
                    // Sample subscription data - adjust based on your implementation
                    $hasActiveSubscription = false; // Check actual subscription
                    $freeTrialDays = 7;
                    $daysUsed = $user->created_at->diffInDays(now());
                    $daysRemaining = max(0, $freeTrialDays - $daysUsed);
                    $inFreeTrial = $daysRemaining > 0;
                @endphp

                @if($hasActiveSubscription)
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold text-green-800 mb-1">Active Subscription</h4>
                        <p class="text-sm text-green-600">$29.99/month</p>
                        <p class="text-xs text-green-600 mt-2">Next billing: {{ now()->addMonth()->format('M d, Y') }}</p>
                    </div>
                @elseif($inFreeTrial)
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold text-yellow-800 mb-1">Free Trial</h4>
                        <p class="text-sm text-yellow-600">{{ $daysRemaining }} days remaining</p>
                        <div class="w-full bg-yellow-200 rounded-full h-2 mt-3">
                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ ($daysUsed / $freeTrialDays) * 100 }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="text-center p-4 bg-gray-50 rounded-lg">
                        <div class="w-12 h-12 bg-gray-400 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <h4 class="font-semibold text-gray-800 mb-1">No Subscription</h4>
                        <p class="text-sm text-gray-600">Free trial expired</p>
                    </div>
                @endif

                <!-- Usage Stats -->
                <div class="mt-6 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Monthly Usage</span>
                        <span class="text-sm font-medium text-gray-900">{{ $userStats['monthly_usage'] }}/{{ $userStats['tickets_limit'] }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $userStats['tickets_limit'] !== 'Unlimited' ? ($userStats['monthly_usage'] / $userStats['tickets_limit']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Admin Actions -->
            <div class="info-card">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Admin Actions</h3>
                
                <div class="space-y-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="w-full bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-center block">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit User
                    </a>

                    @if($user->id !== auth()->id())
                        @if($user->role === 'customer')
                        <button onclick="impersonateUser({{ $user->id }})" class="w-full bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                            </svg>
                            Impersonate User
                        </button>
                        @endif

                        <button onclick="exportUserData({{ $user->id }})" class="w-full bg-green-100 hover:bg-green-200 text-green-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Export Data
                        </button>

                        <button onclick="deleteUser({{ $user->id }})" class="w-full bg-red-100 hover:bg-red-200 text-red-800 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete User
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function impersonateUser(userId) {
        if (confirm('Are you sure you want to impersonate this user? This action will be logged.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('admin.users.impersonate', ':userId') }}`.replace(':userId', userId);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }

    function exportUserData(userId) {
        if (confirm('Export all data for this user? This may take a moment to generate.')) {
            window.location.href = `{{ route('admin.users.export-single', ':userId') }}`.replace(':userId', userId);
        }
    }

    function deleteUser(userId) {
        if (confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')) {
            if (confirm('This will delete ALL user data including purchases and activity. Type DELETE in the prompt to confirm.')) {
                const confirmation = prompt('Type DELETE to confirm permanent deletion:');
                if (confirmation === 'DELETE') {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('admin.users.destroy', ':userId') }}`.replace(':userId', userId);
                    
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                    
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }
    }
</script>
@endpush

@endsection
