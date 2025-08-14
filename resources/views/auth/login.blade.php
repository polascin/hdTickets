<x-guest-layout>
    <!-- Live Regions for Screen Reader Announcements -->
    <div id="hd-status-region" class="hd-sr-live-region" aria-live="polite" aria-atomic="true"></div>
    <div id="hd-alert-region" class="hd-sr-live-region" aria-live="assertive" aria-atomic="true"></div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Registration Restriction Notice with Enhanced Accessibility -->
    <div class="hd-alert hd-alert-info" role="alert" aria-labelledby="registration-notice-title" aria-describedby="registration-notice-desc">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <h4 id="registration-notice-title" class="font-medium">Account Registration</h4>
            <p id="registration-notice-desc" class="text-sm mt-1">
                New user registration is restricted to administrators only. If you need an account, please contact your system administrator.
            </p>
        </div>
    </div>

<form method="POST" action="{{ route('login') }}" class="space-y-6 enhanced-form hd-form" 
      novalidate 
      id="login-form"
      role="form" 
      aria-labelledby="login-form-title"
      aria-describedby="login-form-description">
        
        <!-- Screen Reader Form Context -->
        <div class="hd-sr-only">
            <h1 id="login-form-title">HD Tickets Login Form</h1>
            <p id="login-form-description">Enter your email and password to access your HD Tickets sports events account. This form includes real-time validation and keyboard navigation support.</p>
        </div>
        
        @csrf
        
        <!-- Honeypot field for bot protection -->
        <input type="text" name="website" style="display: none;" tabindex="-1" autocomplete="off" aria-hidden="true" />
        
        <!-- Form submission tracking -->
        <input type="hidden" name="form_token" value="{{ Str::random(40) }}" />
        <input type="hidden" name="client_timestamp" value="" id="client_timestamp" />

        <!-- Email Address with Enhanced Accessibility -->
        <div class="hd-form-group space-y-1">
            <label for="email" class="hd-form-label form-label required" id="email-label">
                <span class="hd-sr-only">Required field: </span>{{ __('Email Address') }}
            </label>
            
            <!-- Field Description for Screen Readers -->
            <div id="email-description" class="hd-field-description hd-sr-only">
                Enter the email address associated with your HD Tickets account. This field is required and must be a valid email format.
            </div>
            
            <div class="relative">
                <input id="email" 
                       class="hd-form-input form-input" 
                       type="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus 
                       autocomplete="email username" 
                       spellcheck="false"
                       placeholder="example@email.com"
                       aria-label="Email address for login"
                       aria-labelledby="email-label"
                       aria-describedby="email-description{{ $errors->get('email') ? ' email-error' : '' }}"
                       aria-required="true"
                       aria-invalid="{{ $errors->get('email') ? 'true' : 'false' }}"
                       data-lpignore="true" />
                
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <title>Email icon</title>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
            </div>
            
            @if ($errors->get('email'))
                <div class="hd-error-message mt-2 text-sm text-red-600" id="email-error" role="alert" aria-live="polite">
                    <span class="hd-sr-only">Email error: </span>{{ $errors->first('email') }}
                </div>
            @endif
        </div>

        <!-- Password with Enhanced Accessibility -->
        <div class="hd-form-group space-y-1">
            <label for="password" class="hd-form-label form-label required" id="password-label">
                <span class="hd-sr-only">Required field: </span>{{ __('Password') }}
            </label>
            
            <!-- Field Description for Screen Readers -->
            <div id="password-description" class="hd-field-description hd-sr-only">
                Enter your account password. This field is required and will be masked for security.
            </div>
            
            <div class="relative">
                <input id="password" 
                       class="hd-form-input form-input"
                       type="password"
                       name="password"
                       required 
                       autocomplete="current-password" 
                       placeholder="Enter your password"
                       aria-label="Password for login"
                       aria-labelledby="password-label"
                       aria-describedby="password-description{{ $errors->get('password') ? ' password-error' : '' }}"
                       aria-required="true"
                       aria-invalid="{{ $errors->get('password') ? 'true' : 'false' }}" />
                
                <!-- Password Toggle Button (Accessible) -->
                <button type="button" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600" 
                        id="password-toggle"
                        aria-label="Show password"
                        aria-describedby="password-toggle-description"
                        onclick="togglePasswordVisibility('password')">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="password-icon" aria-hidden="true">
                        <title>Password visibility toggle</title>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </button>
                
                <!-- Screen reader description for password toggle -->
                <div id="password-toggle-description" class="hd-sr-only">
                    Click to toggle password visibility. Current state: hidden
                </div>
            </div>
            
            @if ($errors->get('password'))
                <div class="hd-error-message mt-2 text-sm text-red-600" id="password-error" role="alert" aria-live="polite">
                    <span class="hd-sr-only">Password error: </span>{{ $errors->first('password') }}
                </div>
            @endif
        </div>

        <!-- Remember Me with Enhanced Accessibility -->
        <div class="mt-6" role="group" aria-labelledby="remember-group-label">
            <div class="hd-enhanced-checkbox-wrapper">
                <input id="remember_me" 
                       type="checkbox" 
                       class="form-checkbox" 
                       name="remember"
                       aria-label="Keep me signed in for convenience"
                       aria-describedby="remember-description">
                <label for="remember_me" 
                       id="remember-group-label"
                       class="text-sm font-medium text-gray-700 select-none cursor-pointer">
                    {{ __('Remember me') }}
                </label>
                
                <!-- Screen reader description for checkbox -->
                <div id="remember-description" class="hd-sr-only">
                    Keep you signed in on this device for convenience. Your session will persist across browser visits.
                </div>
            </div>
        </div>

        <!-- Submit Button with Enhanced Accessibility -->
        <div class="mt-6">
            <button type="submit" 
                    class="hd-btn-primary relative overflow-hidden" 
                    id="login-submit-btn"
                    aria-label="Sign in to HD Tickets"
                    aria-describedby="login-button-description">
                
                <span class="absolute left-0 inset-y-0 flex items-center pl-3" aria-hidden="true">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <title>Login icon</title>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </span>
                <span>{{ __('Sign In') }}</span>
                
                <!-- Loading state announcement -->
                <span id="login-loading" class="hd-sr-only" aria-live="polite"></span>
            </button>
            
            <!-- Screen reader button description -->
            <div id="login-button-description" class="hd-sr-only">
                Submit the login form to access your HD Tickets account. Form will be validated before submission.
            </div>
        </div>
        
        <!-- Forgot Password Link with Enhanced Accessibility -->
        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="hd-link text-sm" 
                   href="{{ route('password.request') }}"
                   aria-label="Forgot your password? Reset it here"
                   aria-describedby="forgot-password-description">
                    {{ __('Forgot your password?') }}
                </a>
                
                <!-- Screen reader description for forgot password link -->
                <div id="forgot-password-description" class="hd-sr-only">
                    Navigate to password reset page where you can enter your email to receive reset instructions.
                </div>
            </div>
        @endif
    </form>
</x-guest-layout>
