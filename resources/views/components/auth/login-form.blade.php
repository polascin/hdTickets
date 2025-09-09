@props([
    'title' => 'Sign in to your account',
    'subtitle' => 'Access your HD Tickets sports events dashboard',
    'showRememberMe' => true,
    'showForgotPassword' => true,
    'showSecurityBadge' => true,
    'showRegistrationLinks' => true,
    'registration_style' => 'full' // 'full', 'compact', 'none'
])

<div class="bg-white py-8 px-4 shadow-xl sm:rounded-3xl sm:px-10" x-data="loginFormData()">
    <!-- Form Header -->
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $title }}</h2>
        <p class="text-sm text-gray-600">{{ $subtitle }}</p>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="text-sm font-medium text-green-800">{{ session('status') }}</div>
            </div>
        </div>
    @endif

    <!-- Error Messages -->
    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start">
                <svg class="h-5 w-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <div class="text-sm font-medium text-red-800 mb-2">There was an error with your login</div>
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Login Form -->
    <form method="POST" action="{{ route('login') }}" class="space-y-6" x-ref="loginForm" @submit="handleSubmit">
        @csrf
        
        <!-- Hidden Security Fields -->
        <input type="hidden" name="device_fingerprint" x-model="deviceFingerprint">
        <input type="hidden" name="client_timestamp" x-model="clientTimestamp">
        <input type="hidden" name="timezone" x-model="timezone">
        
        <!-- Email Field -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                    </svg>
                </div>
                <input id="email" 
                       name="email" 
                       type="email" 
                       autocomplete="email" 
                       required 
                       autofocus
                       x-model="form.email"
                       @input="clearErrors"
                       class="block w-full pl-10 pr-3 py-3 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-stadium-600 focus:border-stadium-600 transition-all duration-200 text-base min-h-[48px] touch-manipulation"
                       :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': errors.email}"
                       placeholder="Enter your email address"
                       value="{{ old('email') }}"
                       inputmode="email"
                       enterkeyhint="next"
                       style="font-size: 16px;"
                       aria-describedby="email-error">
            </div>
            <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email" id="email-error"></div>
        </div>

        <!-- Password Field -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                Password
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <input id="password" 
                       name="password" 
                       required 
                       autocomplete="current-password"
                       x-model="form.password"
                       @input="clearErrors"
                       class="block w-full pl-10 pr-12 py-3 border-2 border-gray-300 rounded-xl text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-stadium-600 focus:border-stadium-600 transition-all duration-200 text-base min-h-[48px] touch-manipulation"
                       :class="{'border-red-300 focus:border-red-500 focus:ring-red-500': errors.password}"
                       :type="showPassword ? 'text' : 'password'"
                       placeholder="Enter your password"
                       enterkeyhint="go"
                       style="font-size: 16px;"
                       aria-describedby="password-error">
                
                <!-- Password Toggle -->
                <button type="button" 
                        class="absolute inset-y-0 right-0 pr-3 flex items-center"
                        @click="showPassword = !showPassword"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                    <svg x-show="!showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <svg x-show="showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l3.122 3.122M12 12l4.242-4.242"/>
                    </svg>
                </button>
            </div>
            <div x-show="errors.password" class="mt-1 text-sm text-red-600" x-text="errors.password" id="password-error"></div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            @if($showRememberMe)
                <div class="flex items-center">
                    <input id="remember" 
                           name="remember" 
                           type="checkbox" 
                           x-model="form.remember"
                           class="h-5 w-5 text-stadium-600 focus:ring-stadium-500 border-gray-300 rounded touch-manipulation">
                    <label for="remember" class="ml-3 block text-sm text-gray-700 select-none cursor-pointer py-2 min-h-[44px] flex items-center">
                        Remember me
                    </label>
                </div>
            @endif

            @if($showForgotPassword && Route::has('password.request'))
                <a href="{{ route('password.request') }}" 
                   class="text-sm font-medium text-stadium-600 hover:text-stadium-500 transition-colors duration-200 py-2 min-h-[44px] flex items-center justify-end touch-manipulation">
                    Forgot your password?
                </a>
            @endif
        </div>

        <!-- reCAPTCHA Status -->
        <div x-show="recaptchaEnabled && !recaptchaReady" 
             x-transition 
             class="flex items-center justify-center p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
            <svg class="animate-spin h-4 w-4 mr-2 text-yellow-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm text-yellow-700">Initializing security verification...</span>
        </div>

        <!-- Submit Button -->
        <button type="submit" 
                :disabled="isSubmitting || (recaptchaEnabled && !recaptchaReady)"
                class="group relative w-full flex justify-center py-4 px-4 border border-transparent text-base font-semibold rounded-xl text-white bg-stadium-600 hover:bg-stadium-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02] min-h-[48px] touch-manipulation"
                :class="{'animate-pulse': isSubmitting}">
            
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg x-show="!isSubmitting" 
                     class="h-5 w-5 transition-all duration-200" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" 
                          stroke-linejoin="round" 
                          stroke-width="2" 
                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                
                <div x-show="isSubmitting" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></div>
            </span>
            
            <span x-text="isSubmitting ? 'Signing in...' : 'Sign In'"></span>
        </button>
    </form>

    @if($showRegistrationLinks && $registration_style !== 'none')
    <!-- Registration Links -->
    <div class="mt-8 space-y-4">
        @if($registration_style === 'full')
        <!-- Primary Registration CTA -->
        <div class="text-center">
            <div class="bg-gradient-to-r from-stadium-50 to-emerald-50 border border-stadium-200 rounded-xl p-6">
                <div class="flex items-center justify-center mb-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-stadium-500 to-emerald-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">New to HD Tickets?</h3>
                <p class="text-sm text-gray-600 mb-4">Join thousands of sports fans monitoring tickets across 50+ platforms</p>
                
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @if(Route::has('register.public'))
                        <a href="{{ route('register.public') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-stadium-600 to-emerald-600 hover:from-stadium-700 hover:to-emerald-700 text-white font-semibold rounded-lg shadow-lg transform transition-all duration-200 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Start 7-Day Free Trial
                        </a>
                    @endif
                    
                    @if(Route::has('subscription.plans'))
                        <a href="{{ route('subscription.plans') }}" 
                           class="inline-flex items-center justify-center px-6 py-3 bg-white hover:bg-gray-50 text-stadium-700 font-medium border-2 border-stadium-300 rounded-lg transition-all duration-200 hover:border-stadium-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stadium-500">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            View Pricing
                        </a>
                    @endif
                </div>
                
                <!-- Features Preview -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6 text-xs text-gray-600">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        7-day free trial
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        No credit card required
                    </div>
                    <div class="flex items-center col-span-2 md:col-span-1">
                        <svg class="w-4 h-4 text-emerald-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        GDPR compliant
                    </div>
                </div>
            </div>
        </div>

        <!-- Alternative Registration Options -->
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <div class="text-center">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Need Different Access?</h4>
                <div class="flex flex-col sm:flex-row gap-2 justify-center text-sm">
                    <!-- Professional Account -->
                    <a href="mailto:support@hdtickets.com?subject=Professional Account Request&body=I'm interested in a professional account with unlimited access to HD Tickets." 
                       class="inline-flex items-center text-purple-600 hover:text-purple-500 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 00-2 2H6a2 2 0 00-2-2V4m8 0H8m0 0v2H6m2 0v6.5c0 .83.67 1.5 1.5 1.5h1c.83 0 1.5-.67 1.5-1.5V6H8z"></path>
                        </svg>
                        Agent Account
                    </a>
                    
                    <span class="text-gray-400 hidden sm:inline">•</span>
                    
                    <!-- Enterprise/Admin -->
                    <a href="mailto:admin@hdtickets.com?subject=Enterprise Account Request&body=I'm interested in enterprise-level access to HD Tickets for my organization." 
                       class="inline-flex items-center text-red-600 hover:text-red-500 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Enterprise
                    </a>
                    
                    <span class="text-gray-400 hidden sm:inline">•</span>
                    
                    <!-- General Support -->
                    <a href="mailto:support@hdtickets.com?subject=Account Help&body=I need help with account access for HD Tickets." 
                       class="inline-flex items-center text-stadium-600 hover:text-stadium-500 font-medium transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Get Help
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Information for Different Roles -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-blue-800 mb-1">About HD Tickets Access Levels</h4>
                    <div class="text-xs text-blue-700 space-y-1">
                        <p><strong>Customer:</strong> $29.99/month • 100 tickets/month • 7-day free trial</p>
                        <p><strong>Agent:</strong> Unlimited access • Advanced monitoring • No subscription required</p>
                        <p><strong>Administrator:</strong> Full system access • User management • All features</p>
                    </div>
                </div>
            </div>
        </div>
        
        @elseif($registration_style === 'compact')
        <!-- Compact Registration Links -->
        <div class="text-center">
            <div class="bg-stadium-50 border border-stadium-200 rounded-xl p-4">
                <h3 class="text-sm font-medium text-gray-900 mb-2">New to HD Tickets?</h3>
                <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    @if(Route::has('register.public'))
                        <a href="{{ route('register.public') }}" 
                           class="inline-flex items-center justify-center px-4 py-2 bg-stadium-600 hover:bg-stadium-700 text-white font-medium rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Register Now
                        </a>
                    @endif
                    
                    <a href="mailto:support@hdtickets.com" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-white hover:bg-gray-50 text-stadium-700 font-medium border border-stadium-300 rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Get Help
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

<script>
function loginFormData() {
    return {
        form: {
            email: '{{ old('email') }}',
            password: '',
            remember: false
        },
        
        showPassword: false,
        isSubmitting: false,
        errors: {},
        
        // Security
        deviceFingerprint: '',
        clientTimestamp: Date.now(),
        timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        
        // reCAPTCHA
        recaptchaEnabled: window.recaptchaConfig?.enabled || false,
        recaptchaReady: false,
        
        async init() {
            try {
                // Generate device fingerprint
                this.deviceFingerprint = await this.generateFingerprint();
                
                // Initialize reCAPTCHA
                if (this.recaptchaEnabled) {
                    await this.initializeRecaptcha();
                } else {
                    this.recaptchaReady = true;
                }
                
            } catch (error) {
                console.warn('Login form initialization error:', error);
                this.recaptchaReady = true; // Ensure form is still usable
            }
        },
        
        async generateFingerprint() {
            const data = {
                userAgent: navigator.userAgent,
                language: navigator.language,
                platform: navigator.platform,
                timezone: this.timezone,
                screen: {
                    width: screen.width,
                    height: screen.height,
                    colorDepth: screen.colorDepth
                }
            };
            
            return btoa(JSON.stringify(data));
        },
        
        async initializeRecaptcha() {
            if (!window.grecaptcha) {
                this.recaptchaReady = true;
                return;
            }
            
            try {
                await new Promise((resolve) => {
                    grecaptcha.ready(resolve);
                });
                this.recaptchaReady = true;
            } catch (error) {
                console.warn('reCAPTCHA initialization failed:', error);
                this.recaptchaEnabled = false;
                this.recaptchaReady = true;
            }
        },
        
        async generateRecaptchaToken() {
            if (!this.recaptchaEnabled || !this.recaptchaReady || !window.grecaptcha) {
                return null;
            }
            
            try {
                return await grecaptcha.execute(window.recaptchaConfig.siteKey, {
                    action: 'login'
                });
            } catch (error) {
                console.warn('Failed to generate reCAPTCHA token:', error);
                return null;
            }
        },
        
        clearErrors() {
            this.errors = {};
        },
        
        validateForm() {
            this.errors = {};
            let isValid = true;
            
            if (!this.form.email) {
                this.errors.email = 'Email address is required';
                isValid = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.errors.email = 'Please enter a valid email address';
                isValid = false;
            }
            
            if (!this.form.password) {
                this.errors.password = 'Password is required';
                isValid = false;
            } else if (this.form.password.length < 8) {
                this.errors.password = 'Password must be at least 8 characters';
                isValid = false;
            }
            
            return isValid;
        },
        
        async handleSubmit(event) {
            if (this.isSubmitting) {
                event.preventDefault();
                return;
            }
            
            if (!this.validateForm()) {
                event.preventDefault();
                return;
            }
            
            event.preventDefault();
            this.isSubmitting = true;
            
            try {
                // Update timestamp
                this.clientTimestamp = Date.now();
                
                // Generate reCAPTCHA token if enabled
                if (this.recaptchaEnabled && this.recaptchaReady) {
                    const token = await this.generateRecaptchaToken();
                    if (token) {
                        const tokenInput = document.createElement('input');
                        tokenInput.type = 'hidden';
                        tokenInput.name = 'recaptcha_token';
                        tokenInput.value = token;
                        this.$refs.loginForm.appendChild(tokenInput);
                    }
                }
                
                // Submit the form
                this.$refs.loginForm.submit();
                
            } catch (error) {
                console.error('Form submission error:', error);
                this.isSubmitting = false;
                this.errors.general = 'Security verification failed. Please try again.';
            }
        }
    }
}
</script>
