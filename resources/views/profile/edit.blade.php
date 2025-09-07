<x-unified-layout title="Edit Profile" subtitle="Update your personal information and preferences">
    <!-- Page Header -->
    <div class="bg-white shadow-sm border-b border-gray-200 mb-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center">
                        <x-profile-icon name="user-tag" class="w-8 h-8 text-blue-600 mr-3" />
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                            <nav class="flex mt-1" aria-label="Breadcrumb">
                                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                    <li class="inline-flex items-center">
                                        <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                            Dashboard
                                        </a>
                                    </li>
                                    <li>
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <a href="{{ route('profile.show') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                                Profile
                                            </a>
                                        </div>
                                    </li>
                                    <li aria-current="page">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 text-gray-400 mx-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-gray-900 text-sm font-medium">Edit</span>
                                        </div>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('profile.show') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <x-profile-icon name="eye" class="w-4 h-4 mr-2" />
                        View Profile
                    </a>
                    <a href="{{ route('profile.security') }}" 
                       class="inline-flex items-center px-4 py-2 border border-green-300 rounded-lg text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                        <x-profile-icon name="shield-check" class="w-4 h-4 mr-2" />
                        Security
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Edit Form with Modern Design -->
    <div class="profile-edit-container" x-data="profileEditForm()">
        
        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-400 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <x-profile-icon name="check-circle" class="w-5 h-5 text-green-600 mr-3" />
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-green-600 hover:text-green-800 transition-colors">
                        <x-profile-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-400 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <x-profile-icon name="exclamation-triangle" class="w-5 h-5 text-red-600 mr-3" />
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <button @click="show = false" class="text-red-600 hover:text-red-800 transition-colors">
                        <x-profile-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-amber-50 border-l-4 border-amber-400 rounded-lg" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-start justify-between">
                    <div class="flex items-start">
                        <x-profile-icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 mr-3 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-amber-800 mb-2">Please correct the following errors:</p>
                            <ul class="text-sm text-amber-700 list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button @click="show = false" class="text-amber-600 hover:text-amber-800 transition-colors">
                        <x-profile-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            </div>
        @endif

        <!-- Profile Section Navigation -->
        <div class="mb-8">
            <div class="border-b border-gray-200">
                <nav class="flex space-x-8 overflow-x-auto" aria-label="Profile sections">
                    <button @click="activeSection = 'basic'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'basic', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'basic' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="user" class="w-4 h-4 inline mr-2" />
                        Basic Information
                    </button>
                    <button @click="activeSection = 'photo'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'photo', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'photo' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="camera" class="w-4 h-4 inline mr-2" />
                        Profile Photo
                    </button>
                    <button @click="activeSection = 'contact'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'contact', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'contact' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="mail" class="w-4 h-4 inline mr-2" />
                        Contact Details
                    </button>
                    <button @click="activeSection = 'preferences'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'preferences', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'preferences' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="cog" class="w-4 h-4 inline mr-2" />
                        Preferences
                    </button>
                    @if($user->role === 'customer')
                    <button @click="activeSection = 'subscription'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'subscription', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'subscription' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="credit-card" class="w-4 h-4 inline mr-2" />
                        Subscription
                    </button>
                    @endif
                    <button @click="activeSection = 'notifications'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'notifications', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'notifications' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="bell" class="w-4 h-4 inline mr-2" />
                        Notifications
                    </button>
                    <button @click="activeSection = 'security'" 
                            :class="{ 'border-blue-500 text-blue-600': activeSection === 'security', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeSection !== 'security' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <x-profile-icon name="shield-check" class="w-4 h-4 inline mr-2" />
                        Security
                    </button>
                </nav>
            </div>
        </div>

        <!-- Form Container -->
        <form id="profile-edit-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" 
              @submit="handleFormSubmit" class="space-y-8">
            @csrf
            @method('PATCH')
            
            <!-- Form Sections -->
            <div class="profile-sections">
                <!-- Basic Information Section -->
                <div x-show="activeSection === 'basic'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Basic Information" icon="user" color="blue">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required 
                                       autocomplete="given-name"
                                       x-model="form.name"
                                       @input="validateField('name')"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('name') border-red-500 ring-red-500 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name
                                </label>
                                <input type="text" 
                                       id="surname" 
                                       name="surname" 
                                       value="{{ old('surname', $user->surname) }}" 
                                       autocomplete="family-name"
                                       x-model="form.surname"
                                       @input="validateField('surname')"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('surname') border-red-500 ring-red-500 @enderror">
                                @error('surname')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-lg">@</span>
                                </div>
                                <input type="text" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $user->username) }}" 
                                       autocomplete="username"
                                       placeholder="Choose a unique username"
                                       x-model="form.username"
                                       @input="validateField('username')"
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('username') border-red-500 ring-red-500 @enderror">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Used for public display and mentions</p>
                            @error('username')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                Biography
                            </label>
                            <textarea id="bio" 
                                      name="bio" 
                                      rows="4" 
                                      maxlength="500"
                                      placeholder="Tell us about yourself..."
                                      x-model="form.bio"
                                      @input="validateField('bio'); updateBioCount()"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none @error('bio') border-red-500 ring-red-500 @enderror">{{ old('bio', $user->bio) }}</textarea>
                            <div class="flex justify-between items-center mt-2">
                                <p class="text-sm text-gray-500">Brief description about yourself (max 500 characters)</p>
                                <p class="text-sm" :class="bioCount > 450 ? 'text-amber-600' : bioCount > 500 ? 'text-red-600' : 'text-gray-500'">
                                    <span x-text="bioCount">{{ strlen($user->bio ?? '') }}</span>/500
                                </p>
                            </div>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Read-only Account Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-gray-200">
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Account Role
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-profile-icon name="user-tag" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input type="text" 
                                           value="{{ ucfirst($user->role) }}" 
                                           readonly 
                                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 cursor-not-allowed">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    User ID
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-400 text-lg">#</span>
                                    </div>
                                    <input type="text" 
                                           value="{{ $user->id }}" 
                                           readonly 
                                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 font-mono cursor-not-allowed">
                                </div>
                            </div>
                        </div>
                    </x-profile-card>
                </div>

                <!-- Profile Photo Section -->
                <div x-show="activeSection === 'photo'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Profile Photo" icon="camera" color="purple">
                        <div class="flex flex-col lg:flex-row gap-8">
                            <!-- Current Photo -->
                            <div class="flex-shrink-0">
                                <div class="text-center">
                                    <div class="relative inline-block">
                                        @if($user->profile_picture)
                                            <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                                                 alt="{{ $user->name }}" 
                                                 class="w-32 h-32 lg:w-40 lg:h-40 rounded-full object-cover border-4 border-white shadow-lg"
                                                 id="current-avatar">
                                        @else
                                            <div class="w-32 h-32 lg:w-40 lg:h-40 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg" 
                                                 id="current-avatar">
                                                <span class="text-3xl lg:text-4xl font-bold text-white">
                                                    {{ substr($user->name, 0, 1) }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Edit Button -->
                                        <button type="button" 
                                                @click="showPhotoUpload = !showPhotoUpload"
                                                class="absolute -bottom-2 -right-2 w-10 h-10 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            <x-profile-icon name="camera" class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Upload Area & Guidelines -->
                            <div class="flex-1">
                                <div x-show="showPhotoUpload" x-transition class="mb-6">
                                    <!-- Drag & Drop Upload Area -->
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors"
                                         x-bind:class="{ 'border-blue-400 bg-blue-50': dragOver }"
                                         @dragover.prevent="dragOver = true"
                                         @dragleave.prevent="dragOver = false"
                                         @drop.prevent="handleFileDrop($event); dragOver = false">
                                        
                                        <x-profile-icon name="cloud-upload" class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                        <p class="text-lg font-medium text-gray-900 mb-2">Drop your photo here</p>
                                        <p class="text-sm text-gray-500 mb-4">or click to browse</p>
                                        
                                        <input type="file" 
                                               id="profile-photo" 
                                               name="profile_picture" 
                                               accept="image/*"
                                               class="hidden"
                                               @change="handleFileSelect($event)">
                                        
                                        <button type="button" 
                                                @click="document.getElementById('profile-photo').click()"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <x-profile-icon name="folder" class="w-4 h-4 mr-2" />
                                            Choose File
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Photo Guidelines -->
                                <div class="space-y-4">
                                    <h4 class="text-lg font-medium text-gray-900">Photo Guidelines</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="flex items-center text-sm text-gray-600">
                                            <x-profile-icon name="check-circle" class="w-4 h-4 text-green-500 mr-2" />
                                            JPG, PNG, WEBP formats
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <x-profile-icon name="check-circle" class="w-4 h-4 text-green-500 mr-2" />
                                            Maximum 5MB file size
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <x-profile-icon name="check-circle" class="w-4 h-4 text-green-500 mr-2" />
                                            Minimum 150×150 pixels
                                        </div>
                                        <div class="flex items-center text-sm text-gray-600">
                                            <x-profile-icon name="check-circle" class="w-4 h-4 text-green-500 mr-2" />
                                            Square images preferred
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="flex flex-wrap gap-3 pt-4">
                                        <button type="button" 
                                                @click="showPhotoUpload = !showPhotoUpload"
                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                            <x-profile-icon name="upload" class="w-4 h-4 mr-2" />
                                            Upload New Photo
                                        </button>
                                        
                                        @if($user->profile_picture)
                                        <button type="button" 
                                                @click="removeProfilePhoto()"
                                                class="inline-flex items-center px-4 py-2 border border-red-300 rounded-lg text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                                            <x-profile-icon name="trash" class="w-4 h-4 mr-2" />
                                            Remove Photo
                                        </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-profile-card>
                </div>
                
                <!-- Contact Details Section -->
                <div x-show="activeSection === 'contact'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Contact Details" icon="mail" color="green">
                        <div class="space-y-6">
                            <div class="form-group">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-profile-icon name="mail" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', $user->email) }}" 
                                           required 
                                           autocomplete="email"
                                           x-model="form.email"
                                           @input="validateField('email')"
                                           class="w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 ring-red-500 @enderror">
                                    @if($user->email_verified_at)
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <x-profile-icon name="check-circle" class="w-5 h-5 text-green-500" title="Verified" />
                                        </div>
                                    @else
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <x-profile-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500" title="Not Verified" />
                                        </div>
                                    @endif
                                </div>
                                @if(!$user->email_verified_at)
                                    <div class="mt-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                        <div class="flex items-center">
                                            <x-profile-icon name="exclamation-triangle" class="w-4 h-4 text-amber-600 mr-2" />
                                            <p class="text-sm text-amber-700">
                                                Email not verified. 
                                                <button type="button" class="font-medium text-amber-800 underline hover:no-underline">
                                                    Resend verification email
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                @endif
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <x-profile-icon name="phone" class="w-5 h-5 text-gray-400" />
                                    </div>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}" 
                                           autocomplete="tel"
                                           placeholder="+1 (555) 123-4567"
                                           x-model="form.phone"
                                           @input="validateField('phone')"
                                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('phone') border-red-500 ring-red-500 @enderror">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Used for account security and important notifications</p>
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </x-profile-card>
                </div>
                
                <!-- Preferences Section -->
                <div x-show="activeSection === 'preferences'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Preferences" icon="cog" color="purple">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="form-group">
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Timezone
                                </label>
                                <select id="timezone" 
                                        name="timezone" 
                                        x-model="form.timezone"
                                        @change="validateField('timezone')"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('timezone') border-red-500 ring-red-500 @enderror">
                                    <option value="">Select your timezone...</option>
                                    @foreach(timezone_identifiers_list() as $timezone)
                                        <option value="{{ $timezone }}" {{ old('timezone', $user->timezone) === $timezone ? 'selected' : '' }}>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                    Language
                                </label>
                                <select id="language" 
                                        name="language" 
                                        x-model="form.language"
                                        @change="validateField('language')"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('language') border-red-500 ring-red-500 @enderror">
                                    <option value="">Select language...</option>
                                    <option value="en" {{ old('language', $user->language ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="es" {{ old('language', $user->language) === 'es' ? 'selected' : '' }}>Español (Spanish)</option>
                                    <option value="fr" {{ old('language', $user->language) === 'fr' ? 'selected' : '' }}>Français (French)</option>
                                    <option value="de" {{ old('language', $user->language) === 'de' ? 'selected' : '' }}>Deutsch (German)</option>
                                    <option value="it" {{ old('language', $user->language) === 'it' ? 'selected' : '' }}>Italiano (Italian)</option>
                                    <option value="pt" {{ old('language', $user->language) === 'pt' ? 'selected' : '' }}>Português (Portuguese)</option>
                                </select>
                                @error('language')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </x-profile-card>
                </div>
                
                @if($user->role === 'customer')
                <!-- Subscription Section -->
                <div x-show="activeSection === 'subscription'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Subscription & Billing" icon="credit-card" color="amber">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                            <div class="flex items-start">
                                <x-profile-icon name="info" class="w-6 h-6 text-blue-600 mr-3 mt-0.5" />
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-blue-900 mb-2">Current Subscription Status</h4>
                                    @if($user->currentSubscription)
                                        <div class="space-y-2 text-sm text-blue-800">
                                            <p><span class="font-medium">Plan:</span> {{ ucfirst($user->currentSubscription->plan_name ?? 'Premium') }}</p>
                                            <p><span class="font-medium">Status:</span> <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span></p>
                                            <p><span class="font-medium">Next Billing:</span> {{ $user->currentSubscription->expires_at ? $user->currentSubscription->expires_at->format('M j, Y') : 'N/A' }}</p>
                                        </div>
                                    @else
                                        <div class="space-y-2 text-sm text-blue-800">
                                            <p><span class="font-medium">Status:</span> <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Free Trial</span></p>
                                            <p><span class="font-medium">Expires:</span> {{ $user->created_at->addDays(7)->format('M j, Y') }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-blue-200">
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                    <x-profile-icon name="credit-card" class="w-4 h-4 mr-2" />
                                    Manage Subscription
                                </button>
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                    <x-profile-icon name="document-text" class="w-4 h-4 mr-2" />
                                    View Invoices
                                </button>
                            </div>
                        </div>

                        @if($user->billing_address)
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-3">Billing Address</h4>
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    @php $billing = $user->billing_address; @endphp
                                    <div class="text-sm text-gray-700">
                                        {{ $billing['street'] ?? '' }}<br>
                                        {{ $billing['city'] ?? '' }}, {{ $billing['state'] ?? '' }} {{ $billing['postal_code'] ?? '' }}<br>
                                        {{ $billing['country'] ?? '' }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </x-profile-card>
                </div>
                @endif
                
                <!-- Notifications Section -->
                <div x-show="activeSection === 'notifications'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Notification Preferences" icon="bell" color="indigo">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Email Notifications</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <label for="email_notifications" class="text-sm font-medium text-gray-700">
                                                    Enable email notifications
                                                </label>
                                                <p class="text-sm text-gray-500">Receive notifications via email</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       id="email_notifications" 
                                                       name="email_notifications" 
                                                       value="1" 
                                                       {{ old('email_notifications', $user->email_notifications) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <label for="price_alerts" class="text-sm font-medium text-gray-700">
                                                    Ticket price alerts
                                                </label>
                                                <p class="text-sm text-gray-500">Get notified when ticket prices change</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       id="price_alerts" 
                                                       name="preferences[price_alerts]" 
                                                       value="1" 
                                                       {{ old('preferences.price_alerts', $user->preferences['price_alerts'] ?? false) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <label for="availability_alerts" class="text-sm font-medium text-gray-700">
                                                    Ticket availability alerts
                                                </label>
                                                <p class="text-sm text-gray-500">Get notified when tickets become available</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       id="availability_alerts" 
                                                       name="preferences[availability_alerts]" 
                                                       value="1" 
                                                       {{ old('preferences.availability_alerts', $user->preferences['availability_alerts'] ?? false) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Push Notifications</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <label for="push_notifications" class="text-sm font-medium text-gray-700">
                                                    Enable push notifications
                                                </label>
                                                <p class="text-sm text-gray-500">Receive browser push notifications</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       id="push_notifications" 
                                                       name="push_notifications" 
                                                       value="1" 
                                                       {{ old('push_notifications', $user->push_notifications) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <div class="flex-1">
                                                <label for="marketing_emails" class="text-sm font-medium text-gray-700">
                                                    Marketing emails
                                                </label>
                                                <p class="text-sm text-gray-500">Receive promotional and marketing emails</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" 
                                                       id="marketing_emails" 
                                                       name="preferences[marketing_emails]" 
                                                       value="1" 
                                                       {{ old('preferences.marketing_emails', $user->preferences['marketing_emails'] ?? false) ? 'checked' : '' }}
                                                       class="sr-only peer">
                                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-profile-card>
                </div>
                
                <!-- Security Section -->
                <div x-show="activeSection === 'security'" x-transition:enter.duration.300ms class="section-content">
                    <x-profile-card title="Security Settings" icon="shield-check" color="red">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-3">Two-Factor Authentication</h4>
                                    <p class="text-sm text-gray-600 mb-4">Add an extra layer of security to your account</p>
                                    
                                    @if($user->two_factor_secret)
                                        <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-lg">
                                            <x-profile-icon name="check-circle" class="w-5 h-5 text-green-600 mr-3" />
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-green-800">2FA is enabled</p>
                                                <p class="text-sm text-green-600">Your account is protected with two-factor authentication</p>
                                            </div>
                                            <a href="{{ route('profile.security') }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-green-300 rounded-md text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                Manage
                                            </a>
                                        </div>
                                    @else
                                        <div class="flex items-center p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                            <x-profile-icon name="exclamation-triangle" class="w-5 h-5 text-amber-600 mr-3" />
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-amber-800">2FA is disabled</p>
                                                <p class="text-sm text-amber-600">Enable 2FA to secure your account</p>
                                            </div>
                                            <a href="{{ route('profile.security') }}" 
                                               class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                Enable 2FA
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-3">Password</h4>
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <p class="text-sm text-gray-600 mb-3">
                                            Last changed: <span class="font-medium">{{ $user->password_changed_at ? $user->password_changed_at->diffForHumans() : $user->created_at->diffForHumans() }}</span>
                                        </p>
                                        <a href="{{ route('profile.security') }}" 
                                           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                            <x-profile-icon name="key" class="w-4 h-4 mr-2" />
                                            Change Password
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-lg font-medium text-gray-900 mb-3">Active Sessions</h4>
                                    <p class="text-sm text-gray-600 mb-4">These devices are currently signed in to your account</p>
                                    
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                            <div class="flex items-center">
                                                <x-profile-icon name="desktop-computer" class="w-5 h-5 text-blue-600 mr-3" />
                                                <div>
                                                    <p class="text-sm font-medium text-blue-900">Current Session</p>
                                                    <p class="text-xs text-blue-600">{{ request()->ip() }} • {{ Str::limit(request()->userAgent(), 50) }}</p>
                                                </div>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                        </div>
                                        
                                        <div class="text-center">
                                            <a href="{{ route('profile.security.advanced') }}" 
                                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                                                <x-profile-icon name="cog" class="w-4 h-4 mr-2" />
                                                Manage All Sessions
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-profile-card>
                </div>
            </div>
            
            <!-- Sticky Save Bar -->
            <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4 flex items-center justify-between shadow-lg">
                <div class="flex items-center space-x-4">
                    <div x-show="hasUnsavedChanges" class="flex items-center text-sm text-amber-600">
                        <x-profile-icon name="exclamation-triangle" class="w-4 h-4 mr-2" />
                        You have unsaved changes
                    </div>
                    <div x-show="!hasUnsavedChanges" class="flex items-center text-sm text-gray-500">
                        <x-profile-icon name="check-circle" class="w-4 h-4 mr-2 text-green-500" />
                        All changes saved
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('profile.show') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Cancel
                    </a>
                    
                    <button type="submit" 
                            :disabled="isSubmitting || !hasUnsavedChanges"
                            :class="{ 'opacity-50 cursor-not-allowed': isSubmitting || !hasUnsavedChanges }"
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg x-show="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <x-profile-icon x-show="!isSubmitting" name="save" class="w-4 h-4 mr-2" />
                        <span x-text="isSubmitting ? 'Saving...' : 'Save Changes'">Save Changes</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-unified-layout>


@endsection

@push('styles')
<style>
/* Profile Edit Page Styles */
.profile-edit-container {
    min-height: calc(100vh - 200px);
}

.section-content {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    position: relative;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Smooth hover effects for buttons */
.profile-edit-container button {
    transition: all 0.2s ease-in-out;
}

.profile-edit-container button:hover {
    transform: translateY(-1px);
}

/* Enhanced file upload area */
.drag-over {
    border-color: #3b82f6 !important;
    background-color: #eff6ff !important;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .profile-edit-container {
        padding-bottom: 100px; /* Account for sticky save bar */
    }
    
    .grid {
        grid-template-columns: 1fr;
    }
    
    .flex-col {
        flex-direction: column;
    }
    
    .lg\:flex-row {
        flex-direction: column;
    }
}

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

/* Accessibility improvements */
@media (prefers-reduced-motion: reduce) {
    .section-content,
    .form-group input,
    .form-group textarea,
    .form-group select,
    button {
        animation: none !important;
        transition: none !important;
        transform: none !important;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .form-group input,
    .form-group textarea,
    .form-group select {
        border-width: 2px;
    }
    
    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
        outline: 3px solid #000;
        outline-offset: 2px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Profile Edit Form Alpine.js Component
function profileEditForm() {
    return {
        // State
        activeSection: 'basic',
        hasUnsavedChanges: false,
        isSubmitting: false,
        dragOver: false,
        showPhotoUpload: false,
        bioCount: {{ strlen($user->bio ?? '') }},
        
        // Form Data
        form: {
            name: '{{ $user->name }}',
            surname: '{{ $user->surname ?? '' }}',
            username: '{{ $user->username ?? '' }}',
            bio: '{{ $user->bio ?? '' }}',
            email: '{{ $user->email }}',
            phone: '{{ $user->phone ?? '' }}'
        },
        
        originalForm: {},
        
        // Initialization
        init() {
            this.originalForm = { ...this.form };
            this.watchForChanges();
            this.handleHashNavigation();
            this.setupAutoSave();
        },
        
        // Watch for form changes
        watchForChanges() {
            this.$watch('form', () => {
                this.hasUnsavedChanges = JSON.stringify(this.form) !== JSON.stringify(this.originalForm);
            }, { deep: true });
        },
        
        // Handle hash-based navigation
        handleHashNavigation() {
            const hash = window.location.hash.replace('#', '');
            if (hash && ['basic', 'photo', 'contact', 'preferences', 'subscription', 'notifications', 'security'].includes(hash)) {
                this.activeSection = hash;
            }
            
            this.$watch('activeSection', (section) => {
                history.replaceState(null, null, '#' + section);
            });
        },
        
        // Auto-save functionality
        setupAutoSave() {
            let timeout;
            this.$watch('form', () => {
                if (this.hasUnsavedChanges) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        this.autoSave();
                    }, 3000); // Auto-save after 3 seconds of inactivity
                }
            }, { deep: true });
        },
        
        // Auto-save function
        async autoSave() {
            if (!this.hasUnsavedChanges || this.isSubmitting) return;
            
            try {
                const formData = new FormData();
                Object.keys(this.form).forEach(key => {
                    if (this.form[key] !== null && this.form[key] !== undefined) {
                        formData.append(key, this.form[key]);
                    }
                });
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                formData.append('_method', 'PATCH');
                formData.append('auto_save', '1');
                
                const response = await fetch('{{ route("profile.update") }}', {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    this.originalForm = { ...this.form };
                    this.hasUnsavedChanges = false;
                    this.showToast('Changes saved automatically', 'success');
                }
            } catch (error) {
                console.error('Auto-save failed:', error);
            }
        },
        
        // Validation
        validateField(fieldName) {
            const field = this.form[fieldName];
            const element = document.querySelector(`[name="${fieldName}"]`);
            
            if (!element) return;
            
            // Remove existing validation classes
            element.classList.remove('border-red-500', 'ring-red-500', 'border-green-500', 'ring-green-500');
            
            // Basic validation
            if (fieldName === 'name' && (!field || field.trim().length < 2)) {
                element.classList.add('border-red-500', 'ring-red-500');
                return false;
            }
            
            if (fieldName === 'email' && (!field || !this.isValidEmail(field))) {
                element.classList.add('border-red-500', 'ring-red-500');
                return false;
            }
            
            if (fieldName === 'username' && field && field.length > 0 && field.length < 3) {
                element.classList.add('border-red-500', 'ring-red-500');
                return false;
            }
            
            if (fieldName === 'bio' && field && field.length > 500) {
                element.classList.add('border-red-500', 'ring-red-500');
                return false;
            }
            
            // Valid field
            element.classList.add('border-green-500', 'ring-green-500');
            return true;
        },
        
        // Email validation
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },
        
        // Update bio counter
        updateBioCount() {
            this.bioCount = (this.form.bio || '').length;
        },
        
        // File handling
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.processFile(file);
            }
        },
        
        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) {
                this.processFile(file);
            }
        },
        
        processFile(file) {
            if (!file.type.startsWith('image/')) {
                this.showToast('Please select a valid image file', 'error');
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                this.showToast('File size must be less than 5MB', 'error');
                return;
            }
            
            // Preview the image
            const reader = new FileReader();
            reader.onload = (e) => {
                const currentAvatar = document.getElementById('current-avatar');
                if (currentAvatar) {
                    if (currentAvatar.tagName === 'IMG') {
                        currentAvatar.src = e.target.result;
                    } else {
                        // Replace div with img
                        const newImg = document.createElement('img');
                        newImg.src = e.target.result;
                        newImg.alt = '{{ $user->name }}';
                        newImg.className = 'w-32 h-32 lg:w-40 lg:h-40 rounded-full object-cover border-4 border-white shadow-lg';
                        newImg.id = 'current-avatar';
                        currentAvatar.parentNode.replaceChild(newImg, currentAvatar);
                    }
                }
                this.hasUnsavedChanges = true;
            };
            reader.readAsDataURL(file);
            
            // Upload the file
            this.uploadProfilePhoto(file);
        },
        
        async uploadProfilePhoto(file) {
            const formData = new FormData();
            formData.append('profile_picture', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            try {
                const response = await fetch('{{ route("profile.picture.upload") }}', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showToast('Profile photo updated successfully!', 'success');
                    this.showPhotoUpload = false;
                    // Update the avatar with the new image URL if provided
                    if (data.data && data.data.pictures && data.data.pictures.medium) {
                        const currentAvatar = document.getElementById('current-avatar');
                        if (currentAvatar) {
                            if (currentAvatar.tagName === 'IMG') {
                                currentAvatar.src = '/storage/' + data.data.pictures.medium;
                            } else {
                                // Replace div with img
                                const newImg = document.createElement('img');
                                newImg.src = '/storage/' + data.data.pictures.medium;
                                newImg.alt = '{{ $user->name }}';
                                newImg.className = 'w-32 h-32 lg:w-40 lg:h-40 rounded-full object-cover border-4 border-white shadow-lg';
                                newImg.id = 'current-avatar';
                                currentAvatar.parentNode.replaceChild(newImg, currentAvatar);
                            }
                        }
                    }
                } else {
                    this.showToast(data.message || 'Failed to upload photo', 'error');
                }
            } catch (error) {
                this.showToast('Upload failed. Please try again.', 'error');
                console.error('Upload error:', error);
            }
        },
        
        async removeProfilePhoto() {
            if (!confirm('Are you sure you want to remove your profile photo?')) {
                return;
            }
            
            try {
                const response = await fetch('{{ route("profile.picture.delete") }}', {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Replace image with initials
                    const currentAvatar = document.getElementById('current-avatar');
                    if (currentAvatar) {
                        const newDiv = document.createElement('div');
                        newDiv.className = 'w-32 h-32 lg:w-40 lg:h-40 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg';
                        newDiv.id = 'current-avatar';
                        newDiv.innerHTML = '<span class="text-3xl lg:text-4xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>';
                        currentAvatar.parentNode.replaceChild(newDiv, currentAvatar);
                    }
                    this.showToast('Profile photo removed successfully!', 'success');
                } else {
                    this.showToast(data.message || 'Failed to remove photo', 'error');
                }
            } catch (error) {
                this.showToast('Failed to remove photo. Please try again.', 'error');
                console.error('Remove photo error:', error);
            }
        },
        
        // Form submission
        async handleFormSubmit(event) {
            event.preventDefault();
            
            this.isSubmitting = true;
            
            // Validate all fields
            const isValid = Object.keys(this.form).every(field => this.validateField(field));
            
            if (!isValid) {
                this.isSubmitting = false;
                this.showToast('Please correct the errors in the form', 'error');
                return;
            }
            
            try {
                const formData = new FormData(event.target);
                const response = await fetch(event.target.action, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    this.originalForm = { ...this.form };
                    this.hasUnsavedChanges = false;
                    this.showToast('Profile updated successfully!', 'success');
                    
                    // Optionally redirect or refresh
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error('Failed to update profile');
                }
            } catch (error) {
                this.showToast('Failed to update profile. Please try again.', 'error');
                console.error('Form submission error:', error);
            } finally {
                this.isSubmitting = false;
            }
        },
        
        // Toast notification
        showToast(message, type = 'info') {
            // Remove existing toasts
            document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
            
            const toast = document.createElement('div');
            toast.className = `toast-notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
            
            const bgColor = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-amber-500',
                info: 'bg-blue-500'
            }[type] || 'bg-blue-500';
            
            toast.classList.add(bgColor);
            toast.innerHTML = `
                <div class="flex items-center text-white">
                    <span class="mr-2">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        ×
                    </button>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
    }
}

// Warn about unsaved changes before leaving
window.addEventListener('beforeunload', function(e) {
    const profileContainer = document.querySelector('.profile-edit-container');
    if (profileContainer && profileContainer.__x) {
        const component = profileContainer.__x.$data;
        if (component.hasUnsavedChanges && !component.isSubmitting) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
        }
    }
});

</script>
@endpush
