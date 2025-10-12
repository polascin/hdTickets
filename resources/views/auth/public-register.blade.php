@extends('layouts.guest-v3')

@section('title', 'Create Account - HD Tickets')
@section('description', 'Register for HD Tickets - Professional sports event ticket monitoring platform. 7-day free
  trial, subscription-based access.')

@section('content')
  <style>
    .registration-container {
      min-height: 100vh;
      background: white;
      margin: 0;
      padding: 0;
    }

    .registration-layout {
      display: flex;
      min-height: 100vh;
    }

    .branding-panel {
      display: none;
      width: 50%;
      background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #3730a3 100%);
      position: relative;
      overflow: hidden;
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
    }

    .feature-icon span {
      color: #166534;
      font-size: 14px;
      font-weight: bold;
    }

    .feature-text {
      color: #dbeafe;
    }

    .trial-badge {
      margin-top: 32px;
      display: inline-flex;
      align-items: center;
      padding: 8px 16px;
      background: #4ade80;
      color: #166534;
      border-radius: 50px;
      font-size: 14px;
      font-weight: 600;
    }

    .form-panel {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px;
    }

    .form-container {
      width: 100%;
      max-width: 400px;
    }

    .mobile-logo {
      text-align: center;
      margin-bottom: 32px;
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
    }

    .form-field {
      margin-bottom: 20px;
    }

    .form-field-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
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

    .form-help {
      margin-top: 4px;
      font-size: 12px;
      color: #6b7280;
    }

    .checkbox-field {
      margin-bottom: 24px;
    }

    .checkbox-label {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      cursor: pointer;
    }

    .checkbox-input {
      margin-top: 4px;
    }

    .checkbox-text {
      font-size: 14px;
      color: #374151;
    }

    .checkbox-link {
      color: #2563eb;
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

    .signin-link {
      text-align: center;
      margin-top: 20px;
    }

    .signin-text {
      font-size: 14px;
      color: #6b7280;
    }

    .signin-anchor {
      color: #2563eb;
      text-decoration: none;
    }

    .signin-anchor:hover {
      text-decoration: underline;
    }

    @media (min-width: 1024px) {
      .branding-panel {
        display: flex !important;
      }

      .form-panel {
        width: 50% !important;
      }
    }
  </style>

  <div class="registration-container">
    <div class="registration-layout">
      <!-- Left Side - Branding & Features -->
      <div class="branding-panel">
        <div class="branding-content">
          <div class="branding-inner">
            <!-- Logo -->
            <div class="logo-section">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="logo-image">
              <h1 class="logo-text">HD Tickets</h1>
            </div>

            <!-- Headline -->
            <h2 class="main-headline">
              Never Miss Your Favorite Sports Events
            </h2>

            <p class="description-text">
              Professional ticket monitoring platform with automated purchasing, real-time alerts, and analytics across
              50+ platforms.
            </p>

            <!-- Feature List -->
            <div class="feature-list">
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Real-time monitoring across multiple platforms</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Automated purchasing when tickets become available</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Advanced analytics and price tracking</span>
              </div>
              <div class="feature-item">
                <div class="feature-icon">
                  <span>✓</span>
                </div>
                <span class="feature-text">Enterprise-grade security with 2FA support</span>
              </div>
            </div>

            <!-- Trial Badge -->
            <div class="trial-badge">
              <span style="margin-right: 8px;">✓</span>
              7-day free trial • No commitment
            </div>
          </div>
        </div>
      </div>

      <!-- Right Side - Registration Form -->
      <div class="form-panel">
        <div class="form-container">
          <!-- Mobile Logo (visible only on mobile) -->
          <div class="mobile-logo">
            <div class="mobile-logo-content">
              <img src="{{ asset('assets/images/hdTicketsLogo.png') }}" alt="HD Tickets" class="mobile-logo-image">
              <h1 class="mobile-logo-text">HD Tickets</h1>
            </div>
          </div>

          <!-- Form Header -->
          <div class="form-header">
            <h2 class="form-title">Create Account</h2>
            <p class="form-subtitle">Get started with your 7-day free trial</p>
          </div>

          <!-- OAuth Options -->
          @php
            $oauthService = app('App\Services\OAuthUserService');
            $providers = $oauthService->getSupportedProviders();
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

          <!-- Registration Form -->
          <form method="POST" action="{{ route('register.public.store') }}" x-data="registrationForm()"
            @submit.prevent="submitForm()">
            @csrf
            <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off" />

            <!-- Email -->
            <div class="form-field">
              <label for="email" class="form-label">Email Address *</label>
              <input type="email" id="email" name="email" x-model="form.email" required
                placeholder="you@example.com" class="form-input">
              @error('email')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <!-- Names -->
            <div class="form-field-row">
              <div>
                <label for="first_name" class="form-label">First Name *</label>
                <input type="text" id="first_name" name="first_name" x-model="form.first_name" required
                  placeholder="John" class="form-input">
                @error('first_name')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
              <div>
                <label for="last_name" class="form-label">Last Name *</label>
                <input type="text" id="last_name" name="last_name" x-model="form.last_name" required placeholder="Doe"
                  class="form-input">
                @error('last_name')
                  <p class="form-error">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <!-- Phone -->
            <div class="form-field">
              <label for="phone" class="form-label">Phone Number <span
                  style="color: #6b7280;">(optional)</span></label>
              <input type="tel" id="phone" name="phone" x-model="form.phone" placeholder="+1 (555) 123-4567"
                class="form-input">
              <p class="form-help">For SMS notifications and account security</p>
              @error('phone')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <!-- Password -->
            <div class="form-field">
              <label for="password" class="form-label">Password *</label>
              <input type="password" id="password" name="password" x-model="form.password" required
                placeholder="Create a strong password" class="form-input">
              @error('password')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <!-- Password Confirmation -->
            <div class="form-field">
              <label for="password_confirmation" class="form-label">Confirm Password *</label>
              <input type="password" id="password_confirmation" name="password_confirmation"
                x-model="form.password_confirmation" required placeholder="Confirm your password" class="form-input">
              @error('password_confirmation')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <!-- Terms -->
            <div class="checkbox-field">
              <label class="checkbox-label">
                <input type="checkbox" name="accept_terms" x-model="form.accept_terms" required
                  class="checkbox-input">
                <span class="checkbox-text">
                  I agree to the <a href="{{ route('legal.terms-of-service') }}" target="_blank"
                    class="checkbox-link">Terms of Service</a>
                  and <a href="{{ route('legal.privacy-policy') }}" target="_blank" class="checkbox-link">Privacy
                    Policy</a>
                </span>
              </label>
              @error('accept_terms')
                <p class="form-error">{{ $message }}</p>
              @enderror
            </div>

            <!-- Submit -->
            <button type="submit" :disabled="!isFormValid() || submitting" class="submit-button">
              <span x-text="submitting ? 'Creating Account...' : 'Create My Account'"></span>
            </button>

            <div class="signin-link">
              <p class="signin-text">
                Already have an account? <a href="{{ route('login') }}" class="signin-anchor">Sign in</a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    function registrationForm() {
      return {
        form: {
          email: '',
          first_name: '',
          last_name: '',
          phone: '',
          password: '',
          password_confirmation: '',
          accept_terms: false
        },
        submitting: false,
        isFormValid() {
          return this.form.email &&
            this.form.first_name &&
            this.form.last_name &&
            this.form.password &&
            this.form.password_confirmation &&
            this.form.accept_terms &&
            this.form.password === this.form.password_confirmation;
        },
        async submitForm() {
          if (!this.isFormValid()) return;

          this.submitting = true;

          try {
            const form = this.$el;
            form.submit();
          } catch (error) {
            console.error('Form submission error:', error);
            this.submitting = false;
          }
        }
      }
    }
  </script>
@endsection
