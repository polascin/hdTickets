<x-guest-layout>
    <!-- Enhanced Security Headers -->
    <meta name="security-policy" content="enhanced-login-v2">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Live Regions for Screen Reader Announcements -->
    <div id="hd-status-region" class="hd-sr-live-region" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="hd-sr-live-region" aria-live="assertive" aria-atomic="true"></div>

    <!-- Modern Login Container -->
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Welcome back</h2>
                <p class="text-sm text-gray-600">Sign in to your HD Tickets account</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Enhanced Login Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8">
                
                <!-- Security Notice -->
                @if(config('app.env') === 'production')
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-green-800">Secure Connection</p>
                            <p class="text-xs text-green-600">Your data is encrypted and protected</p>
                        </div>
                    </div>
                </div>
                @endif

                <form method="POST" action="{{ route('login') }}" 
                      class="space-y-6 enhanced-form" 
                      id="enhanced-login-form"
                      novalidate 
                      role="form" 
                      aria-labelledby="login-form-title">
                    
                    @csrf
                    
                    <!-- Enhanced Security Fields -->
                    <input type="hidden" name="form_token" value="{{ Str::random(40) }}" />
                    <input type="hidden" name="client_timestamp" value="" id="client_timestamp" />
                    <input type="hidden" name="timezone" value="" id="timezone" />
                    
                    <!-- Honeypot Protection -->
                    <div style="position: absolute; left: -9999px;" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off" />
                    </div>

                    <!-- Email Address Field -->
                    <div class="space-y-1">
                        <label for="email" class="block text-sm font-semibold text-gray-700">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   autocomplete="email username"
                                   spellcheck="false"
                                   required 
                                   value="{{ old('email') }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 hover:bg-white"
                                   placeholder="Enter your email address"
                                   aria-describedby="email-help"
                                   data-lpignore="true">
                        </div>
                        <p id="email-help" class="text-xs text-gray-500">We'll never share your email address</p>
                        @error('email')
                            <div class="text-red-600 text-sm mt-1 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1">
                        <label for="password" class="block text-sm font-semibold text-gray-700">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   autocomplete="current-password"
                                   required
                                   class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-gray-50/50 hover:bg-white"
                                   placeholder="Enter your password">
                            <button type="button" 
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                                    aria-label="Toggle password visibility">
                                <svg id="eye-open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg id="eye-closed" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l3.122 3.122M12 12l4.242-4.242"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-red-600 text-sm mt-1 flex items-center">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input id="remember_me" 
                                   name="remember" 
                                   type="checkbox" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-200">
                            <span class="ml-2 text-sm text-gray-700 select-none">Keep me signed in</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-blue-600 hover:text-blue-500 transition-colors duration-200 underline-offset-4 hover:underline" 
                               href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="space-y-4">
                        <button type="submit" 
                                id="login-submit"
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg id="login-icon" class="h-5 w-5 transition-all duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                <div id="loading-spinner" class="hidden animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div>
                            </span>
                            <span id="button-text">Sign In</span>
                        </button>

                        <!-- Alternative Login Methods -->
                        <div id="alternative-methods" class="hidden space-y-2">
                            <!-- Biometric login button will be inserted here by JavaScript -->
                        </div>
                    </div>

                    <!-- Security Information -->
                    <div class="text-center space-y-2">
                        <p class="text-xs text-gray-500">
                            Protected by advanced security measures
                        </p>
                        <div class="flex justify-center items-center space-x-4 text-xs text-gray-400">
                            <span class="flex items-center">
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                SSL Encrypted
                            </span>
                            <span class="flex items-center">
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                2FA Ready
                            </span>
                            <span class="flex items-center">
                                <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                Fast Login
                            </span>
                        </div>
                    </div>
                </form>

                <!-- Registration Notice -->
                <div class="mt-6 text-center">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-left">
                                <p class="text-sm font-medium text-blue-800">Need an account?</p>
                                <p class="text-xs text-blue-600">Contact your system administrator for registration</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Information -->
            <div class="text-center text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
                <p class="mt-1">
                    <a href="#" class="hover:text-gray-700 transition-colors duration-200">Privacy Policy</a>
                    &bull;
                    <a href="#" class="hover:text-gray-700 transition-colors duration-200">Terms of Service</a>
                    &bull;
                    <a href="#" class="hover:text-gray-700 transition-colors duration-200">Support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Enhanced JavaScript -->
    <script src="{{ asset('js/auth-security.js') }}"></script>
    <script src="{{ asset('js/login-enhancements.js') }}"></script>
    
    <script>
        // Initialize enhanced features
        document.addEventListener('DOMContentLoaded', function() {
            // Set timezone and timestamp
            document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
            document.getElementById('client_timestamp').value = Date.now();
            
            // Password visibility toggle
            const toggleBtn = document.getElementById('toggle-password');
            const passwordField = document.getElementById('password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');
            
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordField.type === 'password';
                passwordField.type = isPassword ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
                toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
            
            // Form submission with loading state
            const form = document.getElementById('enhanced-login-form');
            const submitBtn = document.getElementById('login-submit');
            const buttonText = document.getElementById('button-text');
            const loginIcon = document.getElementById('login-icon');
            const loadingSpinner = document.getElementById('loading-spinner');
            
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                buttonText.textContent = 'Signing in...';
                loginIcon.classList.add('hidden');
                loadingSpinner.classList.remove('hidden');
                
                // Re-enable after 10 seconds as fallback
                setTimeout(() => {
                    if (submitBtn.disabled) {
                        submitBtn.disabled = false;
                        buttonText.textContent = 'Sign In';
                        loginIcon.classList.remove('hidden');
                        loadingSpinner.classList.add('hidden');
                    }
                }, 10000);
            });
            
            // Auto-focus email field
            document.getElementById('email').focus();
        });
    </script>
</x-guest-layout>
