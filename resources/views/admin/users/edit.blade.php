<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit User: ') . $user->full_name }}
            </h2>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Users
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus placeholder="Enter first name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Surname -->
                        <div class="mt-4">
                            <x-input-label for="surname" :value="__('Surname')" />
                            <x-text-input id="surname" class="block mt-1 w-full" type="text" name="surname" :value="old('surname', $user->surname)" autocomplete="family-name" placeholder="Enter last name" />
                            <x-input-error :messages="$errors->get('surname')" class="mt-2" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required placeholder="Enter email address" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Username -->
                        <div class="mt-4">
                            <x-input-label for="username" :value="__('Username')" />
                            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username', $user->username)" autocomplete="username" placeholder="Enter username" />
                            <x-input-error :messages="$errors->get('username')" class="mt-2" />
                        </div>

                        <!-- Role -->
                        <div class="mt-4">
                            <x-input-label for="role" :value="__('Role')" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                @foreach(App\Models\User::getRoles() as $role)
                                    <option value="{{ $role }}" @if(old('role', $user->role) === $role) selected @endif>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Active Status -->
                        <div class="mt-4">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} 
                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm text-gray-600">{{ __('Active') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mr-4">
                                Cancel
                            </a>
                            <x-primary-button>
                                {{ __('Update User') }}
                            </x-primary-button>
                        </div>
                    </form>

                    @if($user->id !== auth()->id())
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Reset Password</h3>
                            <p class="text-sm text-gray-600 mb-4">Send a password reset email to this user.</p>
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Send Password Reset Email
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
