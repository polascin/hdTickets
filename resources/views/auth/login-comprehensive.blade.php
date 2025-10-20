{{-- Comprehensive HD Tickets Login Page - Modern UI/UX --}}
<x-guest-layout>
  {{-- Additional Security Headers --}}
  <meta name="security-policy" content="comprehensive-login-v5">
  <meta name="description"
    content="HD Tickets Login - Access your professional sports event ticket monitoring dashboard">
  <meta name="keywords" content="login, sports tickets, ticket monitoring, hd tickets, dashboard access">

  @push('styles')
    <style>
      @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
      }
      
      .animate-float {
        animation: float 6s ease-in-out infinite;
      }
      
      @keyframes pulse-glow {
        0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); }
        50% { box-shadow: 0 0 40px rgba(139, 92, 246, 0.7); }
      }
      
      .glow-effect {
        animation: pulse-glow 3s ease-in-out infinite;
      }
    </style>
  @endpush

  {{-- Comprehensive Login Form with Enhanced UI/UX --}}
  <div class="w-full max-w-md mx-auto" x-data="comprehensiveLoginForm()">
    
    {{-- Main Login Card --}}
    <div class="bg-white rounded-2xl shadow-2xl overflow-hidden border border-gray-100 glow-effect">
      
      {{-- Gradient Header Bar --}}
      <div class="h-2 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600"></div>
      
      {{-- Card Content --}}
      <div class="p-8 sm:p-10">
        
        {{-- Header Section --}}
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4 shadow-lg animate-float">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
            </svg>
          </div>
          
          <div class="inline-flex items-center justify-center px-4 py-1.5 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-full mb-4">
            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="text-sm font-semibold text-green-800">Secure Login</span>
          </div>
          
          <h1 class="text-3xl font-bold text-gray-900 mb-2 tracking-tight">Welcome Back</h1>
          <p class="text-gray-600">Access your sports ticket monitoring dashboard</p>
        </div>

        {{-- Session Status Messages --}}
        @if (session('status'))
          <div class="mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-start">
              <svg class="w-5 h-5 text-green-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
              </div>
              <button @click="show = false" class="text-green-400 hover:text-green-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        @endif

        @if ($errors->any())
          <div class="mb-6 p-4 bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 rounded-xl" x-data="{ show: true }" x-show="show" x-transition>
            <div class="flex items-start">
              <svg class="w-5 h-5 text-red-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-semibold text-red-800 mb-2">Please check the following:</p>
                <ul class="text-sm text-red-700 space-y-1">
                  @foreach ($errors->all() as $error)
                    <li class="flex items-start">
                      <span class="mr-2">•</span>
                      <span>{{ $error }}</span>
                    </li>
                  @endforeach
                </ul>
              </div>
              <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>
          </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}" @submit="handleSubmit" x-ref="loginForm" class="space-y-6">
          @csrf
          
          {{-- Hidden Security Fields --}}
          <input type="hidden" name="device_fingerprint" x-model="deviceFingerprint">
          <input type="hidden" name="client_timestamp" x-model="clientTimestamp">
          <input type="hidden" name="timezone" x-model="timezone">
          
          {{-- Email Field --}}
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-900 mb-2">
              Email Address
            </label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 transition-colors duration-200" :class="emailFocused ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </div>
              <input 
                id="email" 
                name="email" 
                type="email" 
                autocomplete="email" 
                required 
                autofocus
                x-model="form.email"
                @focus="emailFocused = true"
                @blur="emailFocused = false; validateEmail()"
                @input="clearFieldError('email')"
                class="block w-full pl-12 pr-4 py-3.5 border-2 rounded-xl transition-all duration-200 bg-gray-50 focus:bg-white text-gray-900 placeholder-gray-500 text-base"
                :class="errors.email ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100'"
                placeholder="you@example.com"
                value="{{ old('email') }}"
                inputmode="email"
                style="font-size: 16px;">
            </div>
            <p x-show="errors.email" x-text="errors.email" x-transition class="mt-2 text-sm text-red-600 flex items-center">
            </p>
          </div>
          
          {{-- Password Field --}}
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
              Password
            </label>
            <div class="relative group">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-5 h-5 transition-colors duration-200" :class="passwordFocused ? 'text-blue-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </div>
              <input 
                id="password" 
                name="password" 
                required 
                autocomplete="current-password"
                x-model="form.password"
                @focus="passwordFocused = true"
                @blur="passwordFocused = false"
                @input="clearFieldError('password')"
                :type="showPassword ? 'text' : 'password'"
                class="block w-full pl-12 pr-12 py-3.5 border-2 rounded-xl transition-all duration-200 bg-gray-50 focus:bg-white text-gray-900 placeholder-gray-500 text-base"
                :class="errors.password ? 'border-red-300 focus:border-red-500 focus:ring-4 focus:ring-red-100' : 'border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-100'"
                placeholder="••••••••"
                style="font-size: 16px;">
              
              {{-- Password Toggle --}}
              <button 
                type="button" 
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 transition-colors duration-200"
                :aria-label="showPassword ? 'Hide password' : 'Show password'">
                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L12 12m0 0l3.122 3.122M12 12l4.242-4.242"/>
                </svg>
              </button>
            </div>
            <p x-show="errors.password" x-text="errors.password" x-transition class="mt-2 text-sm text-red-600 flex items-center">
            </p>
          </div>
          
          {{-- Remember Me & Forgot Password --}}
          <div class="flex items-center justify-between">
            <label class="flex items-center cursor-pointer group">
              <input 
                type="checkbox" 
                name="remember" 
                x-model="form.remember"
                class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all cursor-pointer">
              <span class="ml-2.5 text-sm text-gray-700 group-hover:text-gray-900 transition-colors select-none">Remember me for 30 days</span>
            </label>
            
            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                Forgot password?
              </a>
            @endif
          </div>
          
          {{-- Submit Button --}}
          <button 
            type="submit" 
            :disabled="isSubmitting"
            class="group relative w-full flex justify-center items-center py-4 px-6 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:opacity-60 disabled:cursor-not-allowed transition-all duration-200 transform hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-xl"
            :class="{'animate-pulse': isSubmitting}">
            
            <span class="absolute left-0 inset-y-0 flex items-center pl-4">
              <svg x-show="!isSubmitting" class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
              </svg>
              
              <div x-show="isSubmitting" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full" style="display: none;"></div>
            </span>
            
            <span x-text="isSubmitting ? 'Signing in...' : 'Sign In to Dashboard'">Sign In to Dashboard</span>
          </button>
        </form>
        
        {{-- Social Login Divider --}}
        <div class="relative my-8">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-200"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white text-gray-500 font-medium">Or continue with</span>
          </div>
        </div>
        
        {{-- Social Login Buttons --}}
        <div class="grid grid-cols-2 gap-4">
          <button 
            type="button" 
            @click="socialLogin('google')"
            class="group flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-xl bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Google</span>
          </button>
          
          <button 
            type="button" 
            @click="socialLogin('microsoft')"
            class="group flex items-center justify-center px-4 py-3 border-2 border-gray-200 rounded-xl bg-white hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 23 23">
              <path fill="#f35325" d="M1 1h10v10H1z"/>
              <path fill="#81bc06" d="M12 1h10v10H12z"/>
              <path fill="#05a6f0" d="M1 12h10v10H1z"/>
              <path fill="#ffba08" d="M12 12h10v10H12z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 group-hover:text-gray-900">Microsoft</span>
          </button>
        </div>
        
      </div>
      
      {{-- Registration Section --}}
      <div class="px-8 sm:px-10 py-6 bg-gradient-to-br from-gray-50 to-blue-50 border-t border-gray-100">
        <div class="text-center">
          <p class="text-sm text-gray-600 mb-4">
            Don't have an account yet?
          </p>
          <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-3 border-2 border-blue-600 rounded-xl text-sm font-semibold text-blue-600 bg-white hover:bg-blue-50 transition-all duration-200 transform hover:scale-105 active:scale-95 shadow-sm hover:shadow">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Create Free Account
          </a>
        </div>
        
        {{-- Trust Indicators --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 text-center">
          <div class="flex flex-col items-center">
            <svg class="w-6 h-6 text-green-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="text-xs font-medium text-gray-600">SSL Encrypted</span>
          </div>
          <div class="flex flex-col items-center">
            <svg class="w-6 h-6 text-blue-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span class="text-xs font-medium text-gray-600">GDPR Compliant</span>
          </div>
          <div class="flex flex-col items-center">
            <svg class="w-6 h-6 text-purple-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-xs font-medium text-gray-600">24/7 Monitoring</span>
          </div>
          <div class="flex flex-col items-center">
            <svg class="w-6 h-6 text-emerald-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <span class="text-xs font-medium text-gray-600">Secure Auth</span>
          </div>
        </div>
      </div>
      
    </div>
    
    {{-- Footer Links --}}
    <div class="mt-8 text-center">
      <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm">
        <a href="{{ route('welcome') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Home</a>
        <a href="{{ route('legal.terms-of-service') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Terms</a>
        <a href="{{ route('legal.privacy-policy') }}" class="text-gray-600 hover:text-gray-900 transition-colors">Privacy</a>
        <a href="mailto:support@hd-tickets.com" class="text-gray-600 hover:text-gray-900 transition-colors">Support</a>
      </div>
      <p class="mt-4 text-xs text-gray-500">
        © {{ date('Y') }} HD Tickets. Professional Sports Ticket Monitoring Platform.
      </p>
    </div>
    
  </div>

  @push('scripts')
    <script>
      function comprehensiveLoginForm() {
        return {
          form: {
            email: '{{ old('email') }}',
            password: '',
            remember: false
          },
          
          showPassword: false,
          emailFocused: false,
          passwordFocused: false,
          isSubmitting: false,
          errors: {},
          
          // Security fields
          deviceFingerprint: '',
          clientTimestamp: Date.now(),
          timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
          
          async init() {
            try {
              this.deviceFingerprint = await this.generateFingerprint();
            } catch (error) {
              console.warn('Fingerprint generation error:', error);
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
          
          validateEmail() {
            if (this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
              this.errors.email = 'Please enter a valid email address';
            }
          },
          
          clearFieldError(field) {
            delete this.errors[field];
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
            
            this.isSubmitting = true;
            this.clientTimestamp = Date.now();
          },
          
          socialLogin(provider) {
            console.log(`Social login with ${provider}`);
            // Placeholder - will be implemented with OAuth routes
            alert(`${provider} login will be available soon. Please use email/password for now.`);
          }
        }
      }
    </script>
  @endpush
</x-guest-layout>
      :root {
        --primary-blue: #1e40af;
        --primary-blue-light: #3b82f6;
        --primary-purple: #8b5cf6;
        --accent-green: #10b981;
        --accent-red: #ef4444;
        --accent-yellow: #f59e0b;
        --text-gray: #6b7280;
        --text-dark: #111827;
        --bg-light: #f8fafc;
        --bg-dark: #0f172a;
        --border-light: #e5e7eb;
        --shadow-light: rgba(0, 0, 0, 0.05);
        --shadow-medium: rgba(0, 0, 0, 0.1);
        --shadow-dark: rgba(0, 0, 0, 0.25);
      }

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: var(--text-dark);
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 50%, var(--primary-purple) 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
      }

      .login-container {
        width: 100%;
        max-width: 500px;
        position: relative;
      }

      .login-card {
        background: white;
        border-radius: 24px;
        padding: 48px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
      }

      .login-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-blue) 0%, var(--primary-purple) 100%);
      }

      .login-header {
        text-align: center;
        margin-bottom: 40px;
      }

      .logo {
        display: inline-flex;
        align-items: center;
        margin-bottom: 24px;
        color: var(--primary-blue);
        font-size: 32px;
        font-weight: 800;
        text-decoration: none;
      }

      .logo i {
        margin-right: 12px;
        color: var(--accent-green);
      }

      .login-title {
        font-size: 32px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 8px;
      }

      .login-subtitle {
        font-size: 16px;
        color: var(--text-gray);
        margin-bottom: 16px;
      }

      .security-badge {
        display: inline-flex;
        align-items: center;
        background: linear-gradient(135deg, var(--accent-green) 0%, #059669 100%);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 8px;
      }

      .security-badge i {
        margin-right: 6px;
        font-size: 10px;
      }

      /* Form Styles */
      .login-form {
        margin-bottom: 32px;
      }

      .form-group {
        margin-bottom: 24px;
      }

      .form-label {
        display: block;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
      }

      .form-input-wrapper {
        position: relative;
      }

      .form-input {
        width: 100%;
        padding: 16px 16px 16px 48px;
        border: 2px solid var(--border-light);
        border-radius: 12px;
        font-size: 16px;
        transition: all 0.3s ease;
        background: white;
        color: var(--text-dark);
      }

      .form-input:focus {
        outline: none;
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
      }

      .form-input.error {
        border-color: var(--accent-red);
      }

      .form-input.error:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
      }

      .form-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-gray);
        font-size: 16px;
      }

      .form-input:focus+.form-icon {
        color: var(--primary-blue);
      }

      .password-toggle {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-gray);
        cursor: pointer;
        font-size: 16px;
        padding: 4px;
        border-radius: 4px;
        transition: all 0.3s ease;
      }

      .password-toggle:hover {
        color: var(--primary-blue);
        background: rgba(30, 64, 175, 0.05);
      }

      .error-message {
        color: var(--accent-red);
        font-size: 14px;
        margin-top: 8px;
        display: flex;
        align-items: center;
      }

      .error-message i {
        margin-right: 6px;
        font-size: 12px;
      }

      .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        font-size: 14px;
      }

      .remember-me {
        display: flex;
        align-items: center;
        cursor: pointer;
      }

      .remember-me input {
        margin-right: 8px;
        width: 16px;
        height: 16px;
        accent-color: var(--primary-blue);
      }

      .forgot-password {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 500;
        transition: all 0.3s ease;
      }

      .forgot-password:hover {
        color: var(--primary-blue-light);
        text-decoration: underline;
      }

      /* Buttons */
      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 16px 32px;
        border-radius: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 16px;
        min-height: 52px;
        width: 100%;
        position: relative;
        overflow: hidden;
      }

      .btn-primary {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-light) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
      }

      .btn-primary:hover:not(:disabled) {
        background: linear-gradient(135deg, var(--primary-blue-light) 0%, var(--primary-purple) 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(30, 64, 175, 0.4);
      }

      .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
      }

      .btn-secondary {
        background: white;
        color: var(--primary-blue);
        border: 2px solid var(--border-light);
      }

      .btn-secondary:hover {
        background: var(--bg-light);
        border-color: var(--primary-blue);
        transform: translateY(-2px);
      }

      .btn-social {
        background: white;
        color: var(--text-dark);
        border: 2px solid var(--border-light);
        margin-bottom: 12px;
      }

      .btn-social:hover {
        background: var(--bg-light);
        transform: translateY(-2px);
      }

      .btn-google:hover {
        border-color: #db4437;
        color: #db4437;
      }

      .btn-microsoft:hover {
        border-color: #0078d4;
        color: #0078d4;
      }

      .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
        margin-right: 8px;
      }

      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }

      /* Alert Messages */
      .alert {
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
      }

      .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
      }

      .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
      }

      .alert-warning {
        background: #fffbeb;
        border: 1px solid #fed7aa;
        color: #92400e;
      }

      .alert-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1e40af;
      }

      .alert-icon {
        margin-right: 12px;
        margin-top: 2px;
        font-size: 18px;
      }

      /* Social Login Section */
      .social-login {
        margin-bottom: 32px;
      }

      .social-divider {
        text-align: center;
        margin: 32px 0;
        position: relative;
        color: var(--text-gray);
        font-size: 14px;
      }

      .social-divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: var(--border-light);
        z-index: 1;
      }

      .social-divider span {
        background: white;
        padding: 0 16px;
        position: relative;
        z-index: 2;
      }

      /* Registration Section */
      .registration-section {
        text-align: center;
        padding: 24px 0;
        border-top: 1px solid var(--border-light);
        margin-top: 32px;
      }

      .registration-text {
        color: var(--text-gray);
        margin-bottom: 16px;
      }

      .registration-link {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
      }

      .registration-link:hover {
        text-decoration: underline;
      }

      /* Trust Indicators */
      .trust-indicators {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 24px;
        margin-top: 24px;
        font-size: 12px;
        color: var(--text-gray);
        flex-wrap: wrap;
      }

      .trust-indicator {
        display: flex;
        align-items: center;
      }

      .trust-indicator i {
        margin-right: 6px;
        color: var(--accent-green);
      }

      /* Footer */
      .login-footer {
        text-align: center;
        margin-top: 40px;
        padding-top: 24px;
        border-top: 1px solid var(--border-light);
      }

      .footer-links {
        display: flex;
        justify-content: center;
        gap: 24px;
        margin-bottom: 16px;
        flex-wrap: wrap;
      }

      .footer-links a {
        color: var(--text-gray);
        text-decoration: none;
        font-size: 14px;
        transition: color 0.3s ease;
      }

      .footer-links a:hover {
        color: var(--primary-blue);
      }

      .footer-text {
        color: var(--text-gray);
        font-size: 12px;
      }

      /* Background Effects */
      .login-container::before {
        content: '';
        position: absolute;
        top: -100px;
        left: -100px;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: -1;
      }

      .login-container::after {
        content: '';
        position: absolute;
        bottom: -80px;
        right: -80px;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
        z-index: -1;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        .login-card {
          padding: 32px;
          border-radius: 16px;
        }

        .login-title {
          font-size: 28px;
        }

        .logo {
          font-size: 28px;
        }

        .form-options {
          flex-direction: column;
          gap: 16px;
          align-items: flex-start;
        }

        .footer-links {
          flex-direction: column;
          gap: 12px;
        }
      }

      @media (max-width: 480px) {
        body {
          padding: 16px;
        }

        .login-card {
          padding: 24px;
        }

        .trust-indicators {
          flex-direction: column;
          gap: 12px;
        }
      }

      /* Animation */
      .animate-fade-in {
        animation: fadeIn 0.8s ease-out;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(30px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Focus Management */
      .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0;
      }
    </style>
  </head>

  <body>
    <!-- Screen Reader Announcements -->
    <div id="status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>
    <div id="alert-region" class="sr-only" aria-live="assertive" aria-atomic="true"></div>

    <div class="login-container animate-fade-in">
      <div class="login-card">
        <!-- Header -->
        <div class="login-header">
          <a href="{{ route('welcome') }}" class="logo">
            <i class="fas fa-ticket-alt"></i>
            HD Tickets
          </a>

          <div class="security-badge">
            <i class="fas fa-shield-alt"></i>
            Secure Login
          </div>

          <h1 class="login-title">Welcome Back</h1>
          <p class="login-subtitle">Access your professional sports ticket monitoring dashboard</p>
        </div>

        <!-- Status Messages -->
        @if (session('status'))
          <div class="alert alert-success">
            <i class="fas fa-check-circle alert-icon"></i>
            <div>
              <div class="font-semibold">Success</div>
              <div>{{ session('status') }}</div>
            </div>
          </div>
        @endif

        @if (session('success'))
          <div class="alert alert-success">
            <i class="fas fa-check-circle alert-icon"></i>
            <div>
              <div class="font-semibold">Success</div>
              <div>{{ session('success') }}</div>
            </div>
          </div>
        @endif

        @if (session('error'))
          <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle alert-icon"></i>
            <div>
              <div class="font-semibold">Error</div>
              <div>{{ session('error') }}</div>
            </div>
          </div>
        @endif

        @if (session('warning'))
          <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle alert-icon"></i>
            <div>
              <div class="font-semibold">Warning</div>
              <div>{{ session('warning') }}</div>
            </div>
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-error">
            <i class="fas fa-exclamation-circle alert-icon"></i>
            <div>
              <div class="font-semibold">Please check the following errors:</div>
              <ul style="margin: 8px 0 0 0; padding-left: 16px;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="login-form" id="loginForm">
          @csrf

          <!-- Hidden Security Fields -->
          <input type="hidden" name="device_fingerprint" id="deviceFingerprint">
          <input type="hidden" name="client_timestamp" id="clientTimestamp">
          <input type="hidden" name="timezone" id="timezone">

          <!-- Email Field -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <div class="form-input-wrapper">
              <input type="email" name="email" id="email" class="form-input @error('email') error @enderror"
                value="{{ old('email') }}" required autofocus autocomplete="email"
                placeholder="Enter your email address" inputmode="email">
              <i class="fas fa-envelope form-icon"></i>
            </div>
            @error('email')
              <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
              </div>
            @enderror
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="form-input-wrapper">
              <input type="password" name="password" id="password"
                class="form-input @error('password') error @enderror" required autocomplete="current-password"
                placeholder="Enter your password">
              <i class="fas fa-lock form-icon"></i>
              <button type="button" class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye" id="passwordToggleIcon"></i>
              </button>
            </div>
            @error('password')
              <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                {{ $message }}
              </div>
            @enderror
          </div>

          <!-- Form Options -->
          <div class="form-options">
            <label class="remember-me">
              <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
              <span>Remember me for 30 days</span>
            </label>

            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="forgot-password">
                Forgot your password?
              </a>
            @endif
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary" id="loginButton">
            <span id="loginButtonText">
              <i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>
              Sign In to Dashboard
            </span>
          </button>
        </form>

        <!-- Social Login Section -->
        <div class="social-login">
          <div class="social-divider">
            <span>Or continue with</span>
          </div>

          <button type="button" class="btn btn-social btn-google" onclick="socialLogin('google')">
            <i class="fab fa-google" style="margin-right: 8px; color: #db4437;"></i>
            Continue with Google
          </button>

          <button type="button" class="btn btn-social btn-microsoft" onclick="socialLogin('microsoft')">
            <i class="fab fa-microsoft" style="margin-right: 8px; color: #0078d4;"></i>
            Continue with Microsoft
          </button>
        </div>

        <!-- Registration Section -->
        <div class="registration-section">
          <p class="registration-text">Don't have an account yet?</p>
          <a href="{{ route('register') }}" class="btn btn-secondary">
            <i class="fas fa-user-plus" style="margin-right: 8px;"></i>
            Create Free Account
          </a>
        </div>

        <!-- Trust Indicators -->
        <div class="trust-indicators">
          <div class="trust-indicator">
            <i class="fas fa-shield-alt"></i>
            <span>SSL Encrypted</span>
          </div>
          <div class="trust-indicator">
            <i class="fas fa-user-shield"></i>
            <span>GDPR Compliant</span>
          </div>
          <div class="trust-indicator">
            <i class="fas fa-lock"></i>
            <span>Secure Authentication</span>
          </div>
          <div class="trust-indicator">
            <i class="fas fa-clock"></i>
            <span>24/7 Monitoring</span>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="login-footer">
        <div class="footer-links">
          <a href="{{ route('welcome') }}">Home</a>
          <a href="/legal/terms">Terms of Service</a>
          <a href="/legal/privacy">Privacy Policy</a>
          <a href="/legal/gdpr">GDPR</a>
          <a href="/health">System Status</a>
          <a href="mailto:support@hd-tickets.com">Support</a>
        </div>
        <p class="footer-text">
          © {{ date('Y') }} HD Tickets. Professional Sports Ticket Monitoring Platform.
        </p>
      </div>
    </div>

    <!-- Scripts -->
    <script>
      // Form Enhancement and Security
      document.addEventListener('DOMContentLoaded', function() {
        initializeLoginForm();
        setupSecurityFields();
        setupFormValidation();
        setupAccessibility();
      });

      function initializeLoginForm() {
        const form = document.getElementById('loginForm');
        const submitButton = document.getElementById('loginButton');
        const buttonText = document.getElementById('loginButtonText');

        form.addEventListener('submit', function(e) {
          // Show loading state
          submitButton.disabled = true;
          buttonText.innerHTML = '<span class="loading-spinner"></span>Signing you in...';

          // Update screen reader
          announceToScreenReader('Signing in, please wait...');

          // Re-enable button after 10 seconds as fallback
          setTimeout(() => {
            if (submitButton.disabled) {
              submitButton.disabled = false;
              buttonText.innerHTML =
                '<i class="fas fa-sign-in-alt" style="margin-right: 8px;"></i>Sign In to Dashboard';
            }
          }, 10000);
        });
      }

      function setupSecurityFields() {
        // Device fingerprinting (basic)
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        ctx.textBaseline = 'top';
        ctx.font = '14px Arial';
        ctx.fillText('Device fingerprint', 2, 2);

        const fingerprint = canvas.toDataURL().slice(-50);
        document.getElementById('deviceFingerprint').value = fingerprint;

        // Client timestamp
        document.getElementById('clientTimestamp').value = Date.now();

        // Timezone
        document.getElementById('timezone').value = Intl.DateTimeFormat().resolvedOptions().timeZone;
      }

      function setupFormValidation() {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        // Real-time email validation
        emailInput.addEventListener('blur', function() {
          if (this.value && !isValidEmail(this.value)) {
            this.classList.add('error');
            showFieldError(this, 'Please enter a valid email address');
          } else {
            this.classList.remove('error');
            hideFieldError(this);
          }
        });

        // Password strength indicator
        passwordInput.addEventListener('input', function() {
          if (this.value.length > 0 && this.value.length < 6) {
            showFieldWarning(this, 'Password should be at least 6 characters');
          } else {
            hideFieldError(this);
          }
        });

        // Clear errors on input
        [emailInput, passwordInput].forEach(input => {
          input.addEventListener('input', function() {
            this.classList.remove('error');
            hideFieldError(this);
          });
        });
      }

      function setupAccessibility() {
        // Enhanced keyboard navigation
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.type !== 'submit') {
            const form = e.target.closest('form');
            if (form) {
              const inputs = Array.from(form.querySelectorAll('input, select, textarea'));
              const currentIndex = inputs.indexOf(e.target);
              if (currentIndex < inputs.length - 1) {
                inputs[currentIndex + 1].focus();
                e.preventDefault();
              }
            }
          }
        });
      }

      function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('passwordToggleIcon');

        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          toggleIcon.className = 'fas fa-eye-slash';
          announceToScreenReader('Password is now visible');
        } else {
          passwordInput.type = 'password';
          toggleIcon.className = 'fas fa-eye';
          announceToScreenReader('Password is now hidden');
        }
      }

      function socialLogin(provider) {
        announceToScreenReader(`Redirecting to ${provider} login...`);
        // TODO: Implement social login redirect
        console.log(`Social login with ${provider} - functionality to be implemented`);

        // Placeholder for actual implementation
        setTimeout(() => {
          alert(`${provider} login integration will be available soon. Please use email/password for now.`);
        }, 500);
      }

      // Utility Functions
      function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
      }

      function showFieldError(field, message) {
        hideFieldError(field);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i>${message}`;
        field.parentNode.appendChild(errorDiv);
      }

      function showFieldWarning(field, message) {
        hideFieldError(field);
        const warningDiv = document.createElement('div');
        warningDiv.className = 'error-message';
        warningDiv.style.color = 'var(--accent-yellow)';
        warningDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i>${message}`;
        field.parentNode.appendChild(warningDiv);
      }

      function hideFieldError(field) {
        const existing = field.parentNode.querySelector('.error-message');
        if (existing) {
          existing.remove();
        }
      }

      function announceToScreenReader(message) {
        const statusRegion = document.getElementById('status-region');
        statusRegion.textContent = message;

        // Clear after announcement
        setTimeout(() => {
          statusRegion.textContent = '';
        }, 1000);
      }

      // Auto-focus management
      window.addEventListener('load', function() {
        const emailInput = document.getElementById('email');
        if (emailInput && !emailInput.value) {
          setTimeout(() => emailInput.focus(), 100);
        }
      });

      // Security: Prevent back button cache
      window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
          window.location.reload();
        }
      });

      // Performance: Preload registration page
      document.addEventListener('DOMContentLoaded', function() {
        const registerLink = document.querySelector('a[href*="register"]');
        if (registerLink) {
          const link = document.createElement('link');
          link.rel = 'prefetch';
          link.href = registerLink.href;
          document.head.appendChild(link);
        }
      });
    </script>
  </body>

</html>
