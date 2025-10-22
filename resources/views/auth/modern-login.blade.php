@extends('layouts.guest')

@section('title', 'Sign In - HD Tickets')
@section('description',
  'Sign in to HD Tickets - Professional sports event ticket monitoring platform. Secure access to your ticket monitoring dashboard.')

  @push('styles')
    <style>
      .login-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
      }

      .login-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        overflow: hidden;
        max-width: 900px;
        width: 100%;
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 600px;
      }

      .branding-section {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #3730a3 100%);
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: white;
        position: relative;
        overflow: hidden;
      }

      .branding-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
        opacity: 0.1;
      }

      .logo-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 2rem;
        position: relative;
        z-index: 1;
      }

      .logo {
        width: 48px;
        height: 48px;
        background: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 20px;
        color: #2563eb;
      }

      .logo-text {
        font-size: 24px;
        font-weight: bold;
      }

      .features-list {
        list-style: none;
        padding: 0;
        margin: 2rem 0;
        position: relative;
        z-index: 1;
      }

      .features-list li {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: #e0e7ff;
      }

      .feature-icon {
        width: 20px;
        height: 20px;
        background: #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: white;
        flex-shrink: 0;
      }

      .security-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #10b981;
        color: #065f46;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        position: relative;
        z-index: 1;
        margin-top: 1rem;
      }

      .form-section {
        padding: 3rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
      }

      .form-header {
        text-align: center;
        margin-bottom: 2rem;
      }

      .form-title {
        font-size: 28px;
        font-weight: bold;
        color: #1f2937;
        margin-bottom: 0.5rem;
      }

      .form-subtitle {
        color: #6b7280;
        font-size: 16px;
      }

      .form-group {
        margin-bottom: 1.5rem;
      }

      .form-label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
      }

      .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.2s;
        background: white;
      }

      .form-input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
      }

      .form-input.error {
        border-color: #dc2626;
      }

      .form-error {
        display: block;
        color: #dc2626;
        font-size: 14px;
        margin-top: 0.25rem;
      }

      .password-container {
        position: relative;
      }

      .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
      }

      .password-toggle:hover {
        color: #374151;
      }

      .checkbox-field {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
      }

      .checkbox-input {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
      }

      .checkbox-label {
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }

      .submit-button {
        width: 100%;
        background: #2563eb;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.875rem 1.5rem;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
      }

      .submit-button:hover:not(:disabled) {
        background: #1d4ed8;
        transform: translateY(-1px);
      }

      .submit-button:disabled {
        background: #9ca3af;
        cursor: not-allowed;
        transform: none;
      }

      .links-section {
        margin-top: 1.5rem;
        text-align: center;
      }

      .link {
        color: #2563eb;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
      }

      .link:hover {
        text-decoration: underline;
      }

      .divider {
        margin: 1.5rem 0;
        text-align: center;
        position: relative;
        color: #6b7280;
        font-size: 14px;
      }

      .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e5e7eb;
      }

      .divider span {
        background: white;
        padding: 0 1rem;
        position: relative;
      }

      .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        to {
          transform: rotate(360deg);
        }
      }

      @media (max-width: 768px) {
        .login-card {
          grid-template-columns: 1fr;
          max-width: 500px;
          margin: 1rem;
        }

        .branding-section {
          padding: 2rem;
          order: 2;
        }

        .form-section {
          padding: 2rem;
          order: 1;
        }
      }

      @media (max-width: 480px) {
        .login-page {
          padding: 0.5rem;
        }

        .branding-section,
        .form-section {
          padding: 1.5rem;
        }

        .form-title {
          font-size: 24px;
        }
      }
    </style>
  @endpush

@section('full-content')
  <div class="login-page">
    <div class="login-card">
      <!-- Branding Section -->
      <div class="branding-section">
        <div class="logo-container">
          <div class="logo">HD</div>
          <div class="logo-text">HD Tickets</div>
        </div>

        <h2 style="font-size: 32px; font-weight: bold; margin-bottom: 1rem; position: relative; z-index: 1;">
          Welcome back
        </h2>

        <p style="font-size: 18px; color: #e0e7ff; position: relative; z-index: 1;">
          Sign in to manage your sports event tickets securely and never miss your favourite matches.
        </p>

        <ul class="features-list">
          <li>
            <span class="feature-icon">✓</span>
            Real-time price monitoring across platforms
          </li>
          <li>
            <span class="feature-icon">✓</span>
            Smart alerts for price drops & availability
          </li>
          <li>
            <span class="feature-icon">✓</span>
            Secure ticket purchasing system
          </li>
          <li>
            <span class="feature-icon">✓</span>
            Professional dashboard with analytics
          </li>
        </ul>

        <div class="security-badge">
          <span>🔒</span>
          Bank-level security & encryption
        </div>
      </div>

      <!-- Form Section -->
      <div class="form-section">
        <div class="form-header">
          <h1 class="form-title">Sign In</h1>
          <p class="form-subtitle">Access your ticket monitoring dashboard</p>
        </div>

        <!-- Live region for form-level errors -->
        <div id="form-errors" class="sr-only" role="alert" aria-live="assertive" aria-atomic="true"></div>

        <!-- Session Status -->
        @if (session('status'))
          <div class="mb-6 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-green-800" role="status" 
               aria-live="polite">
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
          <div class="mb-6 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-red-800" role="alert" 
               aria-live="assertive">
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

        <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
          @csrf

          <!-- Honeypot field (must be named 'website' to match LoginRequest validation) -->
          <input type="text" name="website" style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;" 
                 tabindex="-1" autocomplete="off" aria-hidden="true">

          <!-- Hidden security metadata fields -->
          <input type="hidden" name="device_fingerprint" id="device_fingerprint">
          <input type="hidden" name="client_timestamp" id="client_timestamp">
          <input type="hidden" name="timezone" id="timezone">
          <input type="hidden" name="recaptcha_token" id="recaptcha_token">

          <!-- Email Address -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input 
              id="email" 
              name="email" 
              type="email" 
              inputmode="email"
              autocomplete="email" 
              required 
              autofocus
              class="form-input{{ $errors->has('email') ? ' error' : '' }}"
              placeholder="Enter your email address"
              value="{{ old('email') }}"
              aria-describedby="email-error"
              @if($errors->has('email')) aria-invalid="true" @endif
              data-test="email-input"
            >
            @error('email')
              <span class="form-error" id="email-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="password-container">
              <input 
                id="password" 
                name="password" 
                type="password" 
                autocomplete="current-password" 
                required
                class="form-input{{ $errors->has('password') ? ' error' : '' }}"
                placeholder="Enter your password"
                aria-describedby="password-error"
                @if($errors->has('password')) aria-invalid="true" @endif
                data-test="password-input"
              >
              <button type="button" class="password-toggle" id="passwordToggle" 
                      aria-label="Show password" data-test="password-toggle">
                <svg id="eyeIcon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg id="eyeOffIcon" class="h-5 w-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                </svg>
              </button>
            </div>
            @error('password')
              <span class="form-error" id="password-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Remember Me & Forgot Password -->
          <div class="flex items-center justify-between mb-6">
            <div class="checkbox-field mb-0">
              <input 
                id="remember" 
                name="remember" 
                type="checkbox" 
                class="checkbox-input"
                data-test="remember-checkbox"
              >
              <label for="remember" class="checkbox-label">
                Remember me
              </label>
            </div>

            @if (Route::has('password.request'))
              <a href="{{ route('password.request') }}" class="link" data-test="forgot-password-link">
                Forgotten your password?
              </a>
            @endif
          </div>

          <!-- Submit Button -->
          <button type="submit" class="submit-button" id="submitButton" data-test="login-submit">
            <span id="submitText">Sign In</span>
            <div id="submitSpinner" class="loading-spinner" style="display: none;"></div>
          </button>
        </form>

        <!-- Registration Link -->
        @if (Route::has('register'))
          <div class="divider">
            <span>New to HD Tickets?</span>
          </div>

          <div class="links-section">
            <a href="{{ route('register') }}" class="link" data-test="register-link">
              Create an account
            </a>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('loginForm');
      const submitButton = document.getElementById('submitButton');
      const submitText = document.getElementById('submitText');
      const submitSpinner = document.getElementById('submitSpinner');
      const passwordInput = document.getElementById('password');
      const passwordToggle = document.getElementById('passwordToggle');
      const eyeIcon = document.getElementById('eyeIcon');
      const eyeOffIcon = document.getElementById('eyeOffIcon');

      // Security metadata population (ported from login-enhanced.blade.php)
      try {
        // Generate timezone
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
        
        // Generate device fingerprint
        const fingerprint = [
          navigator.userAgent || '',
          navigator.language || '',
          navigator.platform || '',
          screen.width + 'x' + screen.height,
          screen.colorDepth || '',
          (new Date()).getTimezoneOffset()
        ].join('|');
        
        // Set client timestamp
        const timestamp = new Date().toISOString();
        
        // Populate hidden fields
        const timezoneField = document.getElementById('timezone');
        const fingerprintField = document.getElementById('device_fingerprint');
        const timestampField = document.getElementById('client_timestamp');
        
        if (timezoneField) timezoneField.value = timezone;
        if (fingerprintField) fingerprintField.value = fingerprint;
        if (timestampField) timestampField.value = timestamp;
        
      } catch (e) {
        // Fail silently - the server can handle missing security metadata
        if (console && console.warn) {
          console.warn('HD Tickets: Could not generate security metadata', e);
        }
      }

      // Password toggle functionality
      passwordToggle.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        
        if (isPassword) {
          eyeIcon.classList.add('hidden');
          eyeOffIcon.classList.remove('hidden');
          passwordToggle.setAttribute('aria-label', 'Hide password');
        } else {
          eyeIcon.classList.remove('hidden');
          eyeOffIcon.classList.add('hidden');
          passwordToggle.setAttribute('aria-label', 'Show password');
        }
      });

      // Form submission with reCAPTCHA
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        // Check if reCAPTCHA is enabled via global config
        if (window.recaptchaConfig && window.recaptchaConfig.enabled && window.grecaptcha) {
          submitButton.disabled = true;
          submitText.textContent = 'Verifying...';
          submitSpinner.style.display = 'block';

          // Get the login action from config, fallback to 'login'
          const loginAction = (window.recaptchaConfig.actions && window.recaptchaConfig.actions.login) || 'login';

          window.grecaptcha.ready(function() {
            window.grecaptcha.execute(window.recaptchaConfig.siteKey, { action: loginAction })
              .then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                proceedWithSubmission();
              })
              .catch(function(error) {
                console.warn('reCAPTCHA error:', error);
                // Fail open - proceed without token
                proceedWithSubmission();
              });
          });
        } else {
          // No reCAPTCHA or not enabled - proceed directly
          proceedWithSubmission();
        }

        function proceedWithSubmission() {
          submitButton.disabled = true;
          submitText.textContent = 'Signing in...';
          submitSpinner.style.display = 'block';
          
          // Submit the form
          form.submit();
        }
      });

      // Focus management for accessibility
      const firstError = document.querySelector('.form-input.error');
      if (firstError) {
        firstError.focus();
      }
    });
  </script>
@endpush