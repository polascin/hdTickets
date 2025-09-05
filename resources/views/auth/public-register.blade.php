<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="index, follow">
    
    <title>{{ __('Register') }} - HD Tickets</title>
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- SEO Meta -->
    <meta name="description" content="Register for HD Tickets - Professional sports event ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta name="keywords" content="HD Tickets registration, sports ticket monitoring signup, professional sports platform, subscription registration, ticket monitoring account">
    
    <!-- Open Graph -->
    <meta property="og:title" content="Register - HD Tickets Professional Sports Monitoring">
    <meta property="og:description" content="Register for HD Tickets professional sports ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Register - HD Tickets Professional Sports Monitoring">
    <meta name="twitter:description" content="Register for HD Tickets professional sports ticket monitoring platform. 7-day free trial, subscription-based access, role-based permissions, and enterprise security.">
    <meta name="twitter:image" content="{{ asset('assets/images/hdTicketsLogo.png') }}">
    
    <!-- Custom Styles -->
    <style>
        .registration-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .form-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .legal-checkbox:checked {
            background-color: #10b981;
            border-color: #10b981;
        }
        
        .legal-document-link {
            color: #3b82f6;
            text-decoration: underline;
        }
        
        .legal-document-link:hover {
            color: #1d4ed8;
        }
        
        .floating-label {
            transition: all 0.2s ease-in-out;
        }
        
        .form-input:focus + .floating-label,
        .form-input:not(:placeholder-shown) + .floating-label {
            transform: translateY(-1.5rem) scale(0.875);
            color: #6b7280;
        }
        
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background-color: #ef4444; }
        .strength-medium { background-color: #f59e0b; }
        .strength-strong { background-color: #10b981; }
        
        .register-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }
        
        .register-btn:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }
        
        .feature-icon {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
    </style>
</head>
<body class="font-sans antialiased registration-bg">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Header -->
        <div class="w-full max-w-md mb-6">
            <div class="flex justify-center">
                <a href="{{ route('home') }}" class="flex items-center space-x-2">
                    <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" 
                         alt="HD Tickets Logo" 
                         class="w-12 h-12 rounded-lg">
                    <span class="text-2xl font-bold text-white">HD Tickets</span>
                </a>
            </div>
            <p class="text-center text-white/80 mt-2">Professional Sports Ticket Monitoring</p>
        </div>

        <div class="w-full sm:max-w-2xl mt-6 px-6 py-4 form-card shadow-2xl overflow-hidden sm:rounded-2xl">
            <!-- Registration Form Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Your Account</h1>
                <p class="text-gray-600">Join HD Tickets and start monitoring sports events</p>
                <div class="flex justify-center space-x-4 mt-4 text-sm">
                    <div class="flex items-center text-green-600">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        7-day free trial
                    </div>
                    <div class="flex items-center text-blue-600">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Secure & GDPR compliant
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if (isset($errors) && $errors->has('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-red-700 font-medium">{{ $errors->first('error') }}</p>
                    </div>
                </div>
            @endif

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register.public') }}" class="space-y-6" id="registration-form">
                @csrf
                
                <!-- Honeypot field for bot protection -->
                <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off" />
                
                <!-- Name Fields Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- First Name -->
                    <div class="relative">
                        <input id="name" 
                               type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               autofocus 
                               autocomplete="given-name"
                               placeholder=" "
                               class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                        <label for="name" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                            First Name *
                        </label>
                        @if(isset($errors) && $errors->has('name'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $errors->first('name') }}
                            </p>
                        @endif
                    </div>

                    <!-- Last Name -->
                    <div class="relative">
                        <input id="surname" 
                               type="text" 
                               name="surname" 
                               value="{{ old('surname') }}" 
                               autocomplete="family-name"
                               placeholder=" "
                               class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                        <label for="surname" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                            Last Name
                        </label>
                        @if(isset($errors) && $errors->has('surname'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $errors->first('surname') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Contact Fields Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Email Address -->
                    <div class="relative">
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autocomplete="email"
                               placeholder=" "
                               class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                        <label for="email" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                            Email Address *
                        </label>
                        @if(isset($errors) && $errors->has('email'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $errors->first('email') }}
                            </p>
                        @endif
                    </div>

                    <!-- Phone Number -->
                    <div class="relative">
                        <input id="phone" 
                               type="tel" 
                               name="phone" 
                               value="{{ old('phone') }}" 
                               autocomplete="tel"
                               placeholder=" "
                               class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                        <label for="phone" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                            Phone Number
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Optional - for SMS verification</p>
                        @if(isset($errors) && $errors->has('phone'))
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                {{ $errors->first('phone') }}
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Password Fields -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div class="relative">
                            <input id="password" 
                                   type="password" 
                                   name="password" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder=" "
                                   class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                            <label for="password" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                                Password *
                            </label>
                            @if(isset($errors) && $errors->has('password'))
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $errors->first('password') }}
                                </p>
                            @endif
                        </div>

                        <!-- Confirm Password -->
                        <div class="relative">
                            <input id="password_confirmation" 
                                   type="password" 
                                   name="password_confirmation" 
                                   required 
                                   autocomplete="new-password"
                                   placeholder=" "
                                   class="form-input block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white" />
                            <label for="password_confirmation" class="floating-label absolute left-3 top-3 text-gray-500 pointer-events-none">
                                Confirm Password *
                            </label>
                            @if(isset($errors) && $errors->has('password_confirmation'))
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $errors->first('password_confirmation') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Password Strength Indicator -->
                    <div class="space-y-2">
                        <div class="password-strength w-full strength-weak" id="password-strength"></div>
                        <div class="text-xs text-gray-600" id="password-requirements">
                            Password must contain at least 8 characters with uppercase, lowercase, numbers, and special characters.
                        </div>
                    </div>
                </div>

                <!-- Security Options -->
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <h3 class="font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Security Options
                    </h3>
                    
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="checkbox" 
                               name="enable_2fa" 
                               value="1" 
                               {{ old('enable_2fa') ? 'checked' : '' }}
                               class="mt-1 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-gray-900">Enable Two-Factor Authentication (2FA)</span>
                            <p class="text-xs text-gray-500 mt-1">
                                Add an extra layer of security to your account with Google Authenticator or similar apps.
                            </p>
                        </div>
                    </label>
                </div>

                <!-- Legal Documents Acceptance -->
                <div class="space-y-4">
                    <h3 class="font-medium text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Legal Agreement Required *
                    </h3>
                    <p class="text-sm text-gray-600">You must read and accept the following documents to register:</p>
                    
                    <div class="space-y-3 bg-gray-50 rounded-lg p-4">
                        @foreach($legalDocuments as $type => $document)
                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="checkbox" 
                                       name="accept_{{ $type }}" 
                                       value="1" 
                                       required
                                       {{ old("accept_{$type}") ? 'checked' : '' }}
                                       class="legal-checkbox mt-1 rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500">
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-gray-900">
                                        I have read and accept the 
                                        <a href="{{ route('legal.' . str_replace('_', '-', $type)) }}" 
                                           target="_blank" 
                                           class="legal-document-link">
                                            {{ $document->title }}
                                            <svg class="w-3 h-3 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </a>
                                    </span>
                                    @if($document->version)
                                        <p class="text-xs text-gray-500 mt-1">Version {{ $document->version }} - Last updated {{ $document->effective_date->format('M j, Y') }}</p>
                                    @endif
                                </div>
                            </label>
                            @if(isset($errors) && $errors->has("accept_{$type}"))
                                <p class="text-sm text-red-600 ml-6 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $errors->first("accept_{$type}") }}
                                </p>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Service Notice -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-medium text-yellow-800">Important Notice</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                This service is provided "as-is" with no warranty or money-back guarantee. 
                                You will receive a 7-day free trial, after which subscription fees apply.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="space-y-4">
                    <button type="submit" 
                            class="register-btn w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-white font-medium text-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            id="register-button">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        Create My Account
                    </button>
                    
                    <p class="text-center text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition-colors">
                            Sign in here
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Features Section -->
        <div class="w-full max-w-2xl mt-8 px-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="text-white">
                    <div class="feature-icon w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm">Real-Time Monitoring</h3>
                    <p class="text-xs text-white/80 mt-1">Track prices across 50+ platforms</p>
                </div>
                <div class="text-white">
                    <div class="feature-icon w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 15v-1.5A2.5 2.5 0 0111.5 11h1A2.5 2.5 0 0115 13.5V15"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm">Automated Alerts</h3>
                    <p class="text-xs text-white/80 mt-1">Get notified when prices drop</p>
                </div>
                <div class="text-white">
                    <div class="feature-icon w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-sm">Enterprise Security</h3>
                    <p class="text-xs text-white/80 mt-1">GDPR compliant with 2FA support</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="w-full max-w-2xl mt-8 text-center text-white/60 text-xs px-6">
            <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
            <p class="mt-1">Professional Sports Event Ticket Monitoring Platform</p>
        </div>
    </div>

    <!-- JavaScript for Enhanced UX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password strength checker
            const passwordInput = document.getElementById('password');
            const strengthIndicator = document.getElementById('password-strength');
            const requirementsText = document.getElementById('password-requirements');
            
            function checkPasswordStrength(password) {
                let strength = 0;
                let feedback = [];
                
                if (password.length >= 8) strength += 1;
                else feedback.push('8+ characters');
                
                if (/[a-z]/.test(password)) strength += 1;
                else feedback.push('lowercase');
                
                if (/[A-Z]/.test(password)) strength += 1;
                else feedback.push('uppercase');
                
                if (/[0-9]/.test(password)) strength += 1;
                else feedback.push('numbers');
                
                if (/[^A-Za-z0-9]/.test(password)) strength += 1;
                else feedback.push('special characters');
                
                // Update strength indicator
                strengthIndicator.className = 'password-strength w-full transition-all duration-300';
                if (strength < 3) {
                    strengthIndicator.classList.add('strength-weak');
                } else if (strength < 5) {
                    strengthIndicator.classList.add('strength-medium');
                } else {
                    strengthIndicator.classList.add('strength-strong');
                }
                
                // Update requirements text
                if (feedback.length > 0) {
                    requirementsText.textContent = `Missing: ${feedback.join(', ')}`;
                    requirementsText.className = 'text-xs text-red-600';
                } else {
                    requirementsText.textContent = 'Password strength: Strong ✓';
                    requirementsText.className = 'text-xs text-green-600';
                }
            }
            
            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
            
            // Form validation
            const form = document.getElementById('registration-form');
            const submitButton = document.getElementById('register-button');
            
            function validateForm() {
                const requiredFields = form.querySelectorAll('input[required]');
                const legalCheckboxes = form.querySelectorAll('.legal-checkbox');
                
                let allValid = true;
                
                // Check required fields
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        allValid = false;
                    }
                });
                
                // Check legal agreements
                let legalValid = true;
                legalCheckboxes.forEach(checkbox => {
                    if (!checkbox.checked) {
                        legalValid = false;
                    }
                });
                
                allValid = allValid && legalValid;
                
                // Check password confirmation
                const password = document.getElementById('password').value;
                const passwordConfirmation = document.getElementById('password_confirmation').value;
                if (password !== passwordConfirmation) {
                    allValid = false;
                }
                
                submitButton.disabled = !allValid;
            }
            
            // Add event listeners for form validation
            form.addEventListener('input', validateForm);
            form.addEventListener('change', validateForm);
            
            // Initial validation
            validateForm();
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                submitButton.innerHTML = `
                    <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Creating Account...
                `;
                submitButton.disabled = true;
            });
            
            // Floating label animation
            const inputs = document.querySelectorAll('.form-input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.querySelector('.floating-label').classList.add('active');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.querySelector('.floating-label').classList.remove('active');
                    }
                });
                
                // Check initial state
                if (input.value) {
                    input.parentElement.querySelector('.floating-label').classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
