{{-- HD Tickets • Sports Events Entry Tickets • New Single Login Page --}}
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In - HD Tickets</title>
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css'])
    
    <!-- Security Headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
</head>
<body class="h-full bg-gray-50">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Header -->
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900">HD Tickets</h1>
                <p class="mt-2 text-lg text-gray-600">Sports Events Entry Tickets</p>
                <p class="mt-4 text-sm text-gray-500">Sign in to your account</p>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-lg sm:rounded-lg sm:px-10">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-green-800" role="status" aria-live="polite">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm">{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-red-800" role="alert" aria-live="assertive">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium">There was a problem signing in:</p>
                                <ul class="mt-2 text-sm list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6" novalidate>
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <div class="mt-1">
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                inputmode="email"
                                autocomplete="email" 
                                required 
                                autofocus
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter your email address"
                                value="{{ old('email') }}"
                                aria-describedby="email-error"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600" id="email-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>
                        <div class="mt-1">
                            <input 
                                id="password" 
                                name="password" 
                                type="password" 
                                autocomplete="current-password" 
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="Enter your password"
                                aria-describedby="password-error"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600" id="password-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Honeypot Field (Hidden) -->
                    <div style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;" aria-hidden="true">
                        <label for="website_url" tabindex="-1">Leave this field empty</label>
                        <input type="text" name="website_url" id="website_url" tabindex="-1" autocomplete="off">
                    </div>

                    <!-- Security Metadata (Hidden) -->
                    <input type="hidden" name="device_fingerprint" id="device_fingerprint">
                    <input type="hidden" name="client_timestamp" id="client_timestamp">
                    <input type="hidden" name="timezone" id="timezone">

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input 
                                id="remember" 
                                name="remember" 
                                type="checkbox" 
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                            >
                            <label for="remember" class="ml-2 block text-sm text-gray-700">
                                Remember me
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <div class="text-sm">
                                <a 
                                    href="{{ route('password.request') }}" 
                                    class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 rounded"
                                >
                                    Forgot password?
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button 
                            type="submit" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            Sign In
                        </button>
                    </div>
                </form>

                <!-- Registration Link -->
                @if (Route::has('register'))
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-white text-gray-500">New to HD Tickets?</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <a 
                                href="{{ route('register') }}" 
                                class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200"
                            >
                                Create an account
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    © {{ date('Y') }} HD Tickets. Professional sports event ticket monitoring.
                </p>
            </div>
        </div>
    </div>

    <!-- No JavaScript Fallback -->
    <noscript>
        <div style="position: fixed; top: 20px; left: 20px; right: 20px; background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 6px; padding: 12px; z-index: 9999;">
            <p style="margin: 0; font-size: 14px; color: #374151;">
                <strong>JavaScript is disabled.</strong> The login form will still work, but some security features may be limited.
            </p>
        </div>
    </noscript>

    <!-- Security Metadata JavaScript -->
    <script>
        (function() {
            'use strict';
            
            try {
                // Generate timezone
                var timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                
                // Generate device fingerprint
                var fingerprint = [
                    navigator.userAgent || '',
                    navigator.language || '',
                    navigator.platform || '',
                    screen.width + 'x' + screen.height,
                    screen.colorDepth || '',
                    (new Date()).getTimezoneOffset()
                ].join('|');
                
                // Set client timestamp
                var timestamp = new Date().toISOString();
                
                // Populate hidden fields
                var timezoneField = document.getElementById('timezone');
                var fingerprintField = document.getElementById('device_fingerprint');
                var timestampField = document.getElementById('client_timestamp');
                
                if (timezoneField) timezoneField.value = timezone;
                if (fingerprintField) fingerprintField.value = fingerprint;
                if (timestampField) timestampField.value = timestamp;
                
            } catch (e) {
                // Fail silently - the server can handle missing security metadata
                if (console && console.warn) {
                    console.warn('HD Tickets: Could not generate security metadata', e);
                }
            }
        })();
    </script>
</body>
</html>