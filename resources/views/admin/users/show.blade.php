<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('User Details') }}
            </h2>
            <div class="flex space-x-2">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Edit User
                </a>
                <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Users
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
                                
                                @if($user->username)
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Username</label>
                                    <div class="text-sm text-gray-900">{{ $user->username }}</div>
                                </div>
                                @endif
                                
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
