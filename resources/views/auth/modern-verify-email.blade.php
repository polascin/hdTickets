@extends('layouts.guest')

@section('title', __('auth.verify.title'))
@section('description', __('auth.verify.instructions_tips'))

@push('styles')
  <style>
    .verify-page {
      min-height: 100vh;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1.5rem;
    }

    .verify-card {
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
      background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E') repeat;
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

    .verification-benefits {
      list-style: none;
      padding: 0;
      margin: 2rem 0;
      position: relative;
      z-index: 1;
    }

    .verification-benefits li {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 1rem;
      color: #e0e7ff;
    }

    .benefit-icon {
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

    .alert {
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1.5rem;
      font-size: 14px;
    }

    .alert-success {
      background: #dcfce7;
      color: #166534;
      border: 1px solid #bbf7d0;
    }

    .alert-error {
      background: #fef2f2;
      color: #991b1b;
      border: 1px solid #fecaca;
    }

    .instructions {
      color: #4b5563;
      font-size: 16px;
      line-height: 1.6;
      margin-bottom: 1.5rem;
      text-align: center;
    }

    .masked-email {
      font-family: monospace;
      background: #f3f4f6;
      padding: 0.25rem 0.5rem;
      border-radius: 4px;
      font-weight: 600;
      color: #1f2937;
    }

    .tips {
      background: #fffbeb;
      border: 1px solid #fde68a;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1.5rem;
    }

    .tips-title {
      font-weight: 600;
      color: #d97706;
      font-size: 14px;
      margin-bottom: 0.5rem;
    }

    .tips-list {
      list-style: disc;
      margin-left: 1.25rem;
      color: #92400e;
      font-size: 13px;
    }

    .tips-list li {
      margin-bottom: 0.25rem;
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
      margin-bottom: 1.5rem;
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

    .cooldown-timer {
      font-family: monospace;
      font-weight: bold;
    }

    .links-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
      font-size: 14px;
    }

    .link {
      color: #2563eb;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .link:hover {
      color: #1d4ed8;
      text-decoration: underline;
    }

    .link-muted {
      color: #6b7280;
      text-decoration: none;
      transition: color 0.2s;
    }

    .link-muted:hover {
      color: #374151;
      text-decoration: underline;
    }

    .logout-form {
      display: inline;
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
      .verify-card {
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

      .links-section {
        flex-direction: column;
        text-align: center;
      }
    }

    @media (max-width: 480px) {
      .verify-page {
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

    .hidden {
      display: none;
    }

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
@endpush

@section('full-content')
  <div class="verify-page">
    <div class="verify-card">
      <!-- Branding Section -->
      <div class="branding-section">
        <div class="logo-container">
          <div class="logo">HD</div>
          <div class="logo-text">HD Tickets</div>
        </div>

        <h2 style="font-size: 32px; font-weight: bold; margin-bottom: 1rem; position: relative; z-index: 1;">
          Almost there!
        </h2>

        <p style="font-size: 18px; color: #e0e7ff; position: relative; z-index: 1;">
          We've sent you a verification email. Check your inbox to complete your HD Tickets account setup.
        </p>

        <ul class="verification-benefits">
          <li>
            <span class="benefit-icon">✓</span>
            Secure access to your account
          </li>
          <li>
            <span class="benefit-icon">✓</span>
            Real-time ticket price alerts
          </li>
          <li>
            <span class="benefit-icon">✓</span>
            Personalised event recommendations
          </li>
          <li>
            <span class="benefit-icon">✓</span>
            Priority customer support
          </li>
        </ul>

        <div class="security-badge">
          <span>🔒</span>
          Secure & verified email addresses only
        </div>
      </div>

      <!-- Form Section -->
      <div class="form-section">
        <div class="form-header">
          <h1 class="form-title">{{ __('auth.verify.heading') }}</h1>
          <p class="form-subtitle">Check your email to continue</p>
        </div>

        <!-- Live region for screen reader announcements -->
        <div id="status-region" class="sr-only" aria-live="polite" aria-atomic="true"></div>

        <!-- Success Message -->
        @if (session('status') == 'verification-link-sent')
          <div class="alert alert-success" role="status" aria-live="polite" id="success-alert" tabindex="-1">
            {{ __('auth.verify.link_sent') }}
          </div>
        @endif

        <!-- Error Message -->
        @if ($errors->any())
          <div class="alert alert-error" role="alert" aria-live="assertive" id="error-alert" tabindex="-1">
            @if ($errors->has('email'))
              {{ __('auth.verify.too_many_requests') }}
            @else
              {{ $errors->first() }}
            @endif
          </div>
        @endif

        <!-- Instructions -->
        <p class="instructions">
          {{ __('auth.verify.instructions', ['email' => Str::mask(auth()->user()->email, '*', 3)]) }}
        </p>

        <!-- Troubleshooting Tips -->
        <div class="tips">
          <div class="tips-title">{{ __('auth.verify.troubleshoot_title') }}</div>
          <ul class="tips-list">
            @foreach (__('auth.verify.troubleshoot_items') as $tip)
              <li>{{ $tip }}</li>
            @endforeach
          </ul>
        </div>

        <!-- Resend Form -->
        <form id="resend-form" method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit" class="submit-button" id="resend-btn">
            <span id="resend-label">{{ __('auth.verify.resend_button') }}</span>
            <span id="resend-wait" class="hidden">
              {{ __('auth.verify.resend_available_in') }} <span id="cooldown" class="cooldown-timer">60</span>s
            </span>
            <div id="submit-spinner" class="loading-spinner hidden"></div>
          </button>
        </form>

        <!-- Navigation Links -->
        <div class="links-section">
          <a href="{{ route('login') }}" class="link">
            {{ __('auth.verify.back_to_sign_in') }}
          </a>
          
          <div style="display: flex; gap: 1rem; align-items: center;">
            <a href="{{ route('profile.edit') }}" class="link-muted">
              {{ __('auth.verify.change_email') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
              @csrf
              <button type="submit" class="link-muted" style="background: none; border: none; cursor: pointer;">
                {{ __('auth.verify.log_out') }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('resend-form');
      const btn = document.getElementById('resend-btn');
      const label = document.getElementById('resend-label');
      const wait = document.getElementById('resend-wait');
      const cooldownEl = document.getElementById('cooldown');
      const spinner = document.getElementById('submit-spinner');
      const statusRegion = document.getElementById('status-region');

      let ticking = false;
      let cooldownTimer = null;

      // Focus management for accessibility
      const successAlert = document.getElementById('success-alert');
      const errorAlert = document.getElementById('error-alert');
      
      if (successAlert) {
        successAlert.focus();
        announceToScreenReader('{{ __("auth.verify.link_sent") }}');
      } else if (errorAlert) {
        errorAlert.focus();
        announceToScreenReader(errorAlert.textContent);
      }

      function announceToScreenReader(message) {
        statusRegion.textContent = message;
        setTimeout(() => {
          statusRegion.textContent = '';
        }, 1000);
      }

      function startCooldown(seconds) {
        let remaining = seconds;
        btn.disabled = true;
        label.classList.add('hidden');
        wait.classList.remove('hidden');
        spinner.classList.add('hidden');
        cooldownEl.textContent = remaining;
        ticking = true;
        
        cooldownTimer = setInterval(() => {
          remaining -= 1;
          cooldownEl.textContent = remaining;
          
          if (remaining <= 0) {
            clearInterval(cooldownTimer);
            btn.disabled = false;
            label.classList.remove('hidden');
            wait.classList.add('hidden');
            ticking = false;
            announceToScreenReader('{{ __("auth.verify.resend_button") }} is now available');
          }
        }, 1000);
      }

      form.addEventListener('submit', function(e) {
        if (ticking) {
          e.preventDefault();
          return;
        }

        btn.disabled = true;
        label.classList.add('hidden');
        spinner.classList.remove('hidden');
        
        // Start cooldown immediately to prevent double-clicks
        setTimeout(() => {
          if (!ticking) {
            startCooldown(60);
          }
        }, 1000);
      });

      // Cleanup on page unload
      window.addEventListener('beforeunload', function() {
        if (cooldownTimer) {
          clearInterval(cooldownTimer);
        }
      });

      // Keyboard accessibility for custom buttons
      document.querySelectorAll('button[type="submit"]:not([disabled])').forEach(button => {
        button.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.click();
          }
        });
      });
    });
  </script>
@endpush