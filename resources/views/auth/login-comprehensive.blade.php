@extends('layouts.guest-v3')

@section('title', 'Sign In - HD Tickets')
@section('description', 'Sign in to HD Tickets - Professional sports event ticket monitoring platform. Access your dashboard, alerts, and automated purchasing.')

@section('suppress-chrome')

@section('full-content')
  <style>
    .login-container {
      min-height: 100vh;
      background: white;
      margin: 0;
      padding: 0;
    }

    .login-layout {
      display: flex;
      min-height: 100vh;
    }

    .branding-panel {
      width: 50%;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #3730a3 100%);
      position: relative;
      overflow: hidden;
      display: flex;
    }

    .branding-content {
      position: relative;
      z-index: 10;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 48px;
      color: white;
    }

    .branding-inner {
      max-width: 400px;
    }

    .logo-section {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 32px;
    }

    .logo-image {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }

    .logo-text {
      font-size: 30px;
      font-weight: bold;
      margin: 0;
    }

    .main-headline {
      font-size: 36px;
      font-weight: bold;
      line-height: 1.2;
      margin-bottom: 24px;
      color: white;
    }

    .description-text {
      color: #dbeafe;
      font-size: 18px;
      margin-bottom: 32px;
      line-height: 1.6;
    }

    .feature-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .feature-icon {
      width: 24px;
      height: 24px;
      background: #4ade80;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .feature-icon span {
      color: #166534;
      font-size: 14px;
      font-weight: bold;
    }

    .feature-text {
      color: #dbeafe;
    }

    .form-panel {
      width: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
      background: white;
    }

    .form-container {
      width: 100%;
      max-width: 400px;
    }

    .mobile-logo {
      text-align: center;
      margin-bottom: 32px;
      display: none;
    }

    .mobile-logo-content {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 24px;
    }

    .mobile-logo-image {
      width: 40px;
      height: 40px;
      border-radius: 8px;
    }

    .mobile-logo-text {
      font-size: 24px;
      font-weight: bold;
      color: #1f2937;
      margin: 0;
    }

    .form-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .form-title {
      font-size: 30px;
      font-weight: bold;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .form-subtitle {
      color: #6b7280;
      margin: 0;
    }

    .alert-box {
      margin-bottom: 20px;
      padding: 12px 16px;
      border-radius: 8px;
      font-size: 14px;
    }

    .alert-success {
      background: #d1fae5;
      color: #065f46;
      border: 1px solid #a7f3d0;
    }

    .alert-error {
      background: #fee2e2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .oauth-section {
      margin-bottom: 24px;
    }

    .oauth-button {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px 24px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      color: #374151;
      background: white;
      text-decoration: none;
      margin-bottom: 12px;
      transition: all 0.2s;
      cursor: pointer;
    }

    .oauth-button:hover {
      background: #f9fafb;
    }

    .oauth-icon {
      width: 20px;
      height: 20px;
      margin-right: 12px;
    }

    .divider {
      position: relative;
      margin: 24px 0;
      text-align: center;
    }

    .divider::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 0;
      right: 0;
      height: 1px;
      background: #d1d5db;
    }

    .divider-text {
      padding: 0 12px;
      background: white;
      color: #6b7280;
      font-size: 14px;
      position: relative;
    }

    .form-field {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 8px;
    }

    .form-input {
      width: 100%;
      padding: 12px 16px;
      font-size: 16px;
      border: 1px solid #d1d5db;
      border-radius: 8px;
      background: white;
    }

    .form-input:focus {
      outline: none;
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .form-error {
      margin-top: 4px;
      font-size: 14px;
      color: #dc2626;
    }

    .checkbox-field {
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .checkbox-label {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .checkbox-input {
      width: 16px;
      height: 16px;
    }

    .checkbox-text {
      font-size: 14px;
      color: #374151;
    }

    .forgot-link {
      font-size: 14px;
      color: #2563eb;
      text-decoration: none;
    }

    .forgot-link:hover {
      text-decoration: underline;
    }

    .submit-button {
      width: 100%;
      padding: 16px;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      border: none;
      cursor: pointer;
      background: #2563eb;
      font-size: 16px;
      transition: background 0.2s;
    }

    .submit-button:hover:not(:disabled) {
      background: #1d4ed8;
    }

    .submit-button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    .signup-link {
      text-align: center;
      margin-top: 20px;
    }

    .signup-text {
      font-size: 14px;
      color: #6b7280;
    }

    .signup-anchor {
      color: #2563eb;
      text-decoration: none;
    }

    .signup-anchor:hover {
      text-decoration: underline;
    }

    /* Responsive breakpoints */
    @media (max-width: 1023px) {
      .branding-panel {
        display: none;
      }

      .form-panel {
        width: 100% !important;
      }
      
      .mobile-logo {
        display: block !important;
      }
    }

    @media (min-width: 1024px) {
      .mobile-logo {
        display: none !important;
      }
    }
  </style>

  <div class="login-container">
    <div class="login-layout">
      {{-- Left Side - Branding & Features --}}
      <div class="branding-panel">
        <div class="branding-content">
          <div class="branding-inner">
            {{-- Logo --}}
            <div class="logo-section">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="logo-image">
              <h1 class="logo-text">HD Tickets</h1>
            </div>

            {{-- Headline --}}
            <h2 class="main-headline">
              Welcome Back to Your Dashboard
            </h2>

            <p class="description-text">
              Access your professional ticket monitoring platform with real-time alerts, automated purchasing, and comprehensive analytics.
            </p>

            {{-- Feature List --}}
            <div class="feature-list">
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Instant access to your ticket alerts</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Manage automated purchases</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">View analytics and price trends</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Secure authentication with 2FA</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Right Side - Login Form --}}
      <div class="form-panel">
        <div class="form-container" x-data="loginForm()">
          {{-- Mobile Logo (visible only on mobile) --}}
          <div class="mobile-logo">
            <div class="mobile-logo-content">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="mobile-logo-image">
              <h1 class="mobile-logo-text">HD Tickets</h1>
            </div>
          </div>

          {{-- Form Header --}}
          <div class="form-header">
            <h2 class="form-title">Sign In</h2>
            <p class="form-subtitle">Access your ticket monitoring dashboard</p>
          </div>

          {{-- Status Messages --}}
          @if (session('status'))
            <div class="alert-box alert-success">
              {{ session('status') }}
            </div>
          @endif

          @if ($errors->any())
            <div class="alert-box alert-error">
              <strong>Error:</strong>
              @foreach ($errors->all() as $error)
                {{ $error }}
              @endforeach
            </div>
          @endif

          {{-- OAuth Options --}}
          @php
            try {
              $oauthService = app('App\\Services\\OAuthUserService');
              $providers = $oauthService->getSupportedProviders();
            } catch (Exception $e) {
              $providers = [];
            }
          @endphp

          @if (collect($providers)->where('enabled', true)->isNotEmpty())
            <div class="oauth-section">
              @foreach ($providers as $provider => $config)
                @if ($config['enabled'])
                  <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}" class="oauth-button">
                    @if ($provider === 'google')
                      <svg class="oauth-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4"
                          d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                        <path fill="#34A853"
                          d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                        <path fill="#FBBC05"
                          d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                        <path fill="#EA4335"
                          d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                      </svg>
                    @endif
                    Continue with {{ $config['name'] }}
                  </a>
                @endif
              @endforeach
            </div>

            <div class="divider">
              <span class="divider-text">Or continue with email</span>
            </div>
          @endif

          {{-- Login Form --}}
          <form method="POST" action="{{ route('login') }}" @submit="handleSubmit">
            @csrf
            <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off" />

            {{-- Hidden Security Fields --}}
            <input type="hidden" name="device_fingerprint" x-model="deviceFingerprint">
            <input type="hidden" name="client_timestamp" x-model="clientTimestamp">
            <input type="hidden" name="timezone" x-model="timezone">

            {{-- Email --}}
            <div class="form-field">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email" x-model="form.email" required autofocus
                placeholder="you@example.com" class="form-input" value="{{ old('email') }}">
              @error('email')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            {{-- Password --}}
            <div class="form-field">
              <label for="password" class="form-label">Password *</label>
              <input type="password" id="password" name="password" x-model="form.password" required
                placeholder="Enter your password" class="form-input">
              @error('password')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            {{-- Remember Me & Forgot Password --}}
            <div class="checkbox-field">
              <label class="checkbox-label">
                <input type="checkbox" name="remember" x-model="form.remember" class="checkbox-input">
                <span class="checkbox-text">Remember me</span>
              </label>

              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
              @endif
            </div>

            {{-- Submit --}}
            <button type="submit" :disabled="submitting" class="submit-button">
              <span x-text="submitting ? 'Signing in...' : 'Sign In'"></span>
            </button>

            <div class="signup-link">
              <p class="signup-text">
                Don't have an account? <a href="{{ route('register') }}" class="signup-anchor">Sign up</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
  <script>
    function loginForm() {
      return {
        form: {
          email: '{{ old('email') }}',
          password: '',
          remember: false
        },
        submitting: false,
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

        async handleSubmit(event) {
          if (this.submitting) {
            event.preventDefault();
            return;
          }

          if (!this.form.email || !this.form.password) {
            event.preventDefault();
            alert('Please fill in all required fields');
            return;
          }

          this.submitting = true;
          this.clientTimestamp = Date.now();
        }
      }
    }
  </script>
@endsection