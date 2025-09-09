@props([
    'title' => 'Sign in to your account',
    'subtitle' => 'Access your HD Tickets sports events dashboard',
    'showRememberMe' => true,
    'showForgotPassword' => true,
    'showSecurityBadge' => true
])

<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-stadium-blue-50 via-white to-stadium-purple-50 py-12 px-4 sm:px-6 lg:px-8"
     x-data="loginForm()">
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-[url('/images/stadium-pattern.svg')] opacity-5"></div>
    
    <!-- Login Container -->
    <div class="relative max-w-md w-full space-y-8">
        
        <!-- Logo and Header -->
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-gradient-to-r from-stadium-blue-600 to-stadium-purple-600 rounded-full flex items-center justify-center mb-4 shadow-lg ring-4 ring-white/20">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h2>
            <p class="text-sm text-gray-600">{{ $subtitle }}</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Main Login Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-gray-200/50 p-8 transition-all duration-300 hover:shadow-2xl">
            
            <!-- Security Badge -->
            @if($showSecurityBadge && config('app.env') === 'production')
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-green-800">Secure Connection</p>
                        <p class="text-xs text-green-600">Your data is encrypted and protected</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" 
                  class="space-y-6" 
                  id="login-form"
                  novalidate
                  x-ref="form"
                  @submit="handleSubmit">
                
                @csrf
                
                <!-- Security Fields -->
                <input type="hidden" name="form_token" :value="formToken">
                <input type="hidden" name="client_timestamp" :value="clientTimestamp">
                <input type="hidden" name="timezone" :value="timezone">
                <input type="hidden" name="device_fingerprint" :value="deviceFingerprint">
                
                <!-- Honeypot -->
                <div style="position: absolute; left: -9999px;" aria-hidden="true">
                    <label for="website">Website</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>

                <!-- Email Field -->
                <x-auth.input-field 
                    name="email"
                    type="email"
                    label="Email Address"
                    icon="envelope"
                    placeholder="Enter your email address"
                    autocomplete="email username"
                    required
                    autofocus
                    x-model="form.email"
                    @blur="validateEmail"
                    ::class="{'border-red-300 focus:border-red-500 focus:ring-red-500': errors.email}"
                    aria-describedby="email-help email-error">
                    
                    <template x-if="!errors.email && form.email && emailStatus.exists === true">
                        <div class="text-xs text-green-600 mt-1 flex items-center">
                            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>Account found</span>
                        </div>
                    </template>
                </x-auth.input-field>

                <!-- Password Field -->
                <x-auth.password-field 
                    name="password"
                    label="Password"
                    placeholder="Enter your password"
                    required
                    x-model="form.password"
                    ::class="{'border-red-300 focus:border-red-500 focus:ring-red-500': errors.password}">
                </x-auth.password-field>

                <!-- Remember Me & Forgot Password Row -->
                <div class="flex items-center justify-between">
                    @if($showRememberMe)
                    <label class="flex items-center group cursor-pointer">
                        <input name="remember" 
                               type="checkbox" 
                               x-model="form.remember"
                               class="h-4 w-4 text-stadium-blue-600 focus:ring-stadium-blue-500 border-gray-300 rounded transition-colors duration-200">
                        <span class="ml-2 text-sm text-gray-700 select-none group-hover:text-gray-900">Keep me signed in</span>
                    </label>
                    @endif

                    @if($showForgotPassword && Route::has('password.request'))
                    <a class="text-sm font-medium text-stadium-blue-600 hover:text-stadium-blue-500 transition-colors duration-200 underline-offset-4 hover:underline" 
                       href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                    @endif
                </div>

                <!-- Rate Limit Warning -->
                <div x-show="rateLimitWarning" 
                     x-transition
                     class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Security Notice</p>
                            <p class="text-xs text-yellow-600">Please wait <span x-text="countdown"></span> seconds before trying again</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-xl text-white bg-gradient-to-r from-stadium-blue-600 to-stadium-purple-600 hover:from-stadium-blue-700 hover:to-stadium-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:transform-none"
                        :disabled="isSubmitting"
                        :class="{'animate-pulse': isSubmitting}">
                    
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <!-- Normal Icon -->
                        <svg x-show="!isSubmitting" 
                             class="h-5 w-5 transition-all duration-200" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24" 
                             aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        
                        <!-- Loading Spinner -->
                        <div x-show="isSubmitting" 
                             class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full">
                        </div>
                    </span>
                    
                    <span x-text="isSubmitting ? 'Signing in...' : 'Sign In'"></span>
                </button>

                <!-- Alternative Authentication -->
                <div x-show="showAlternatives" 
                     x-transition 
                     class="space-y-3"
                     style="display: none;">
                    
                    <!-- Biometric Login (if supported) -->
                    <template x-if="biometricSupported">
                        <button type="button" 
                                @click="tryBiometricLogin"
                                class="w-full flex justify-center items-center py-2 px-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                            </svg>
                            Use Biometric Login
                        </button>
                    </template>
                </div>
            </form>

            <!-- Security Information -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-xs text-gray-500">
                    Protected by advanced security measures
                </p>
                <div class="flex justify-center items-center space-x-4 text-xs text-gray-400">
                    <span class="flex items-center">
                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        SSL Encrypted
                    </span>
                    <span class="flex items-center">
                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        2FA Ready
                    </span>
                    <span class="flex items-center">
                        <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Fast Login
                    </span>
                </div>
            </div>
        </div>

        <!-- Registration Notice -->
        <div class="text-center">
            <div class="bg-stadium-blue-50 border border-stadium-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-center">
                    <svg class="h-5 w-5 text-stadium-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-left">
                        <p class="text-sm font-medium text-stadium-blue-800">Need an account?</p>
                        <p class="text-xs text-stadium-blue-600">Contact your system administrator for registration</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-gray-500">
            <p>&copy; {{ date('Y') }} HD Tickets. All rights reserved.</p>
            <p class="mt-1 space-x-1">
                <a href="#" class="hover:text-gray-700 transition-colors duration-200">Privacy Policy</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-gray-700 transition-colors duration-200">Terms of Service</a>
                <span>&bull;</span>
                <a href="#" class="hover:text-gray-700 transition-colors duration-200">Support</a>
            </p>
        </div>
    </div>
</div>

<script>
function loginForm() {
    return {
        // Form data
        form: {
            email: '{{ old('email') }}',
            password: '',
            remember: false
        },
        
        // UI state
        isSubmitting: false,
        showAlternatives: false,
        biometricSupported: false,
        rateLimitWarning: false,
        countdown: 0,
        
        // Validation
        errors: {},
        emailStatus: {},
        
        // Security
        formToken: '{{ Str::random(40) }}',
        clientTimestamp: Date.now(),
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        deviceFingerprint: '',
        
        async init() {
            // Generate device fingerprint
            this.deviceFingerprint = await this.generateFingerprint();
            
            // Check biometric support
            this.checkBiometricSupport();
            
            // Check for rate limit errors
            this.checkRateLimitErrors();
            
            // Focus email field
            this.$nextTick(() => {
                this.$refs.form.querySelector('[name="email"]')?.focus();
            });
        },
        
        async generateFingerprint() {
            const data = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
                screen: {
                    width: screen.width,
                    height: screen.height,
                    colorDepth: screen.colorDepth
                },
                canvas: this.getCanvasFingerprint()
            };
            
            return btoa(JSON.stringify(data));
        },
        
        getCanvasFingerprint() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            ctx.textBaseline = 'top';
            ctx.font = '14px Arial';
            ctx.fillText('HD Tickets Security Canvas', 2, 2);
            return canvas.toDataURL().slice(-50);
        },
        
        async checkBiometricSupport() {
            if (window.PublicKeyCredential) {
                this.biometricSupported = await PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable();
            }
        },
        
        checkRateLimitErrors() {
            const rateLimitSeconds = @json($errors->get('rate_limit_seconds.0') ?? null);
            if (rateLimitSeconds) {
                this.rateLimitWarning = true;
                this.countdown = parseInt(rateLimitSeconds);
                this.startCountdown();
            }
        },
        
        startCountdown() {
            const interval = setInterval(() => {
                this.countdown--;
                if (this.countdown <= 0) {
                    this.rateLimitWarning = false;
                    clearInterval(interval);
                }
            }, 1000);
        },
        
        async validateEmail() {
            if (!this.form.email || !this.isValidEmail(this.form.email)) {
                this.emailStatus = {};
                return;
            }
            
            try {
                const response = await fetch('{{ route("login.check-email") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email: this.form.email })
                });
                
                if (response.ok) {
                    this.emailStatus = await response.json();
                    if (this.emailStatus.exists && this.emailStatus.preferences?.two_factor_enabled) {
                        this.showAlternatives = true;
                    }
                }
            } catch (error) {
                console.log('Email validation failed:', error);
            }
        },
        
        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },
        
        async handleSubmit(event) {
            if (this.isSubmitting) {
                event.preventDefault();
                return;
            }
            
            this.isSubmitting = true;
            this.errors = {};
            
            // Update timestamp before submission
            this.clientTimestamp = Date.now();
            
            // Let the form submit naturally
            // The loading state will be cleared on page change or error
            setTimeout(() => {
                if (this.isSubmitting) {
                    this.isSubmitting = false;
                }
            }, 10000);
        },
        
        async tryBiometricLogin() {
            if (!this.biometricSupported || !this.form.email) return;
            
            try {
                // This would integrate with WebAuthn API
                console.log('Biometric login not implemented yet');
                
                this.$dispatch('toast', {
                    type: 'info',
                    message: 'Biometric login coming soon!'
                });
            } catch (error) {
                console.error('Biometric login failed:', error);
            }
        }
    }
}
</script>
