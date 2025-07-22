<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-gradient-to-r from-cyan-500 to-teal-600 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        {{ __('User Details') }}
                    </h2>
                    <p class="text-sm text-gray-600">Review and manage user information</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <button onclick="window.location.href='{{ route('admin.users.edit', $user) }}'" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-semibold rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit User
                </button>
                <button onclick="window.location.href='{{ route('admin.users.index') }}'" class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-semibold rounded-lg shadow-lg transform transition hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Users
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- User Profile Header -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100 mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-8">
                    <div class="flex items-center space-x-6">
                        <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl">
                            {{ strtoupper(substr($user->name, 0, 1) . substr($user->surname ?? '', 0, 1)) }}
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                            <p class="text-gray-600 mt-1">{{ $user->email }}</p>
                            <div class="flex items-center mt-3 space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    @if($user->role === 'admin') bg-gradient-to-r from-red-100 to-red-200 text-red-800 
                                    @elseif($user->role === 'agent') bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 
                                    @else bg-gradient-to-r from-green-100 to-green-200 text-green-800 @endif">
                                    {{ ucfirst($user->role) }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    @if($user->is_active) bg-gradient-to-r from-green-100 to-green-200 text-green-800 @else bg-gradient-to-r from-red-100 to-red-200 text-red-800 @endif">
                                    @if($user->is_active)
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center text-green-600 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        Email Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-amber-600 text-xs">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        Email Unverified
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Details Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- User Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">User Information</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Name</label>
                                    <div class="text-sm text-gray-900">{{ $user->name }}</div>
                                </div>
                                
                                @if($user->surname)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Surname</label>
                                    <div class="text-sm text-gray-900">{{ $user->surname }}</div>
                                </div>
                                @endif
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email</label>
                                    <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                </div>
                                
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Role</label>
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($user->role === 'admin') bg-red-100 text-red-800 
                                            @elseif($user->role === 'agent') bg-blue-100 text-blue-800 
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Status</label>
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email Verified</label>
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($user->email_verified_at) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Details -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Account Details</h3>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Account Created</label>
                                    <div class="text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i:s') }}</div>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Last Updated</label>
                                    <div class="text-sm text-gray-900">{{ $user->updated_at->format('M d, Y H:i:s') }}</div>
                                </div>
                                
                                @if($user->email_verified_at)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email Verified At</label>
                                    <div class="text-sm text-gray-900">{{ $user->email_verified_at->format('M d, Y H:i:s') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Edit User
                            </a>
                            
                            <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                </button>
                            </form>
                            
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to reset this user\'s password to the default?')">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="bg-orange-500 hover:bg-orange-700 text-white font-bold py-2 px-4 rounded">
                                    Reset Password
                                </button>
                            </form>
                            
                            @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Delete User
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
