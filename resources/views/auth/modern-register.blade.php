@extends('layouts.guest')

@section('title', 'Create Account - HD Tickets')
@section('description',
  'Join HD Tickets - Professional sports event ticket monitoring platform. 7-day free trial,
  subscription-based access.')

  @push('styles')
    <style>
      .registration-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
      }

      .registration-card {
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

      .trial-badge {
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

      .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
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

      .form-input.success {
        border-color: #10b981;
      }

      .form-error {
        display: block;
        color: #dc2626;
        font-size: 14px;
        margin-top: 0.25rem;
      }

      .form-success {
        display: block;
        color: #10b981;
        font-size: 14px;
        margin-top: 0.25rem;
      }

      .password-strength {
        margin-top: 0.5rem;
        padding: 0.5rem;
        border-radius: 6px;
        font-size: 12px;
        text-align: center;
        font-weight: 600;
      }

      .strength-weak {
        background: #fef2f2;
        color: #dc2626;
      }

      .strength-fair {
        background: #fffbeb;
        color: #d97706;
      }

      .strength-good {
        background: #f0fdf4;
        color: #16a34a;
      }

      .strength-strong {
        background: #f0fdf4;
        color: #166534;
      }

      .checkbox-field {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
      }

      .checkbox-input {
        margin-top: 0.125rem;
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
      }

      .checkbox-label {
        font-size: 14px;
        color: #374151;
        cursor: pointer;
      }

      .checkbox-label a {
        color: #2563eb;
        text-decoration: none;
      }

      .checkbox-label a:hover {
        text-decoration: underline;
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

      .login-link {
        text-align: center;
        margin-top: 1.5rem;
        font-size: 14px;
        color: #6b7280;
      }

      .login-link a {
        color: #2563eb;
        text-decoration: none;
        font-weight: 600;
      }

      .login-link a:hover {
        text-decoration: underline;
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
        .registration-card {
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

        .form-row {
          grid-template-columns: 1fr;
        }
      }

      @media (max-width: 480px) {
        .registration-page {
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
  <div class="registration-page">
    <div class="registration-card">
      <!-- Branding Section -->
      <div class="branding-section">
        <div class="logo-container">
          <div class="logo">HD</div>
          <div class="logo-text">HD Tickets</div>
        </div>

        <h2 style="font-size: 32px; font-weight: bold; margin-bottom: 1rem; position: relative; z-index: 1;">
          Join thousands of sports fans
        </h2>

        <p style="font-size: 18px; color: #e0e7ff; position: relative; z-index: 1;">
          Get access to the most comprehensive ticket monitoring platform for sports events.
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
            Advanced filtering and search tools
          </li>
          <li>
            <span class="feature-icon">✓</span>
            Mobile app with push notifications
          </li>
        </ul>

        <div class="trial-badge">
          <span>🎉</span>
          7-day free trial included
        </div>
      </div>

      <!-- Form Section -->
      <div class="form-section">
        <div class="form-header">
          <h1 class="form-title">Create Account</h1>
          <p class="form-subtitle">Start monitoring tickets in minutes</p>
        </div>

        <form method="POST" action="{{ route('register.store') }}" id="registrationForm">
          @csrf

          <!-- Honeypot field -->
          <input type="text" name="website_url" style="display: none;" tabindex="-1" autocomplete="off">

          <!-- Name fields -->
          <div class="form-group">
            <div class="form-row">
              <div>
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" id="first_name" name="first_name" class="form-input" value="{{ old('first_name') }}"
                  placeholder="John" required data-validation="true">
                @error('first_name')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>
              <div>
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="form-input" value="{{ old('last_name') }}"
                  placeholder="Doe" required data-validation="true">
                @error('last_name')
                  <span class="form-error">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-input" value="{{ old('email') }}"
              placeholder="john@example.com" required data-validation="email">
            @error('email')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Phone (optional) -->
          <div class="form-group">
            <label for="phone" class="form-label">Phone Number <span style="color: #6b7280;">(optional)</span></label>
            <input type="tel" id="phone" name="phone" class="form-input" value="{{ old('phone') }}"
              placeholder="+1 (555) 123-4567" data-validation="true">
            @error('phone')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="Enter a strong password"
              required data-validation="password">
            @error('password')
              <span class="form-error">{{ $message }}</span>
            @enderror
            <div id="password-strength" class="password-strength" style="display: none;"></div>
          </div>

          <!-- Confirm Password -->
          <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input"
              placeholder="Confirm your password" required data-validation="true">
            @error('password_confirmation')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Terms acceptance -->
          <div class="checkbox-field">
            <input type="checkbox" id="accept_terms" name="accept_terms" class="checkbox-input" required
              {{ old('accept_terms') ? 'checked' : '' }}>
            <label for="accept_terms" class="checkbox-label">
              I agree to the <a href="{{ route('legal.terms-of-service') }}" target="_blank">Terms of Service</a> and
              <a href="{{ route('legal.privacy-policy') }}" target="_blank">Privacy Policy</a>
            </label>
            @error('accept_terms')
              <span class="form-error">{{ $message }}</span>
            @enderror
          </div>

          <!-- Marketing opt-in -->
          <div class="checkbox-field">
            <input type="checkbox" id="marketing_opt_in" name="marketing_opt_in" class="checkbox-input"
              value="1" {{ old('marketing_opt_in') ? 'checked' : '' }}>
            <label for="marketing_opt_in" class="checkbox-label">
              Send me updates about new features and ticket alerts
            </label>
          </div>

          <!-- Submit button -->
          <button type="submit" class="submit-button" id="submitButton">
            <span id="submitText">Create Account</span>
            <div id="submitSpinner" class="loading-spinner" style="display: none;"></div>
          </button>

          @error('registration')
            <div class="form-error" style="text-align: center; margin-top: 1rem;">
              {{ $message }}
            </div>
          @enderror
        </form>

        <div class="login-link">
          Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const form = document.getElementById('registrationForm');
      const submitButton = document.getElementById('submitButton');
      const submitText = document.getElementById('submitText');
      const submitSpinner = document.getElementById('submitSpinner');

      // Real-time validation
      const validationFields = document.querySelectorAll('[data-validation]');
      const validationTimeouts = new Map();

      validationFields.forEach(field => {
        field.addEventListener('input', function() {
          clearTimeout(validationTimeouts.get(field));

          validationTimeouts.set(field, setTimeout(() => {
            validateField(field);
          }, 500));
        });

        field.addEventListener('blur', function() {
          clearTimeout(validationTimeouts.get(field));
          validateField(field);
        });
      });

      // Password strength checking
      const passwordField = document.getElementById('password');
      const passwordStrength = document.getElementById('password-strength');

      passwordField.addEventListener('input', function() {
        if (this.value.length > 0) {
          checkPasswordStrength(this.value);
        } else {
          passwordStrength.style.display = 'none';
        }
      });

      // Form submission
      form.addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateForm()) {
          return;
        }

        submitButton.disabled = true;
        submitText.textContent = 'Creating Account...';
        submitSpinner.style.display = 'block';

        // Submit the form
        form.submit();
      });

      async function validateField(field) {
        const fieldName = field.name;
        const value = field.value.trim();

        if (!value && field.required) {
          setFieldError(field, 'This field is required');
          return;
        }

        if (!value) {
          clearFieldValidation(field);
          return;
        }

        try {
          const response = await fetch('{{ route('register.validate-field') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              field: fieldName,
              value: value
            })
          });

          const data = await response.json();

          if (data.valid) {
            setFieldSuccess(field, data.message);
          } else {
            setFieldError(field, data.message);
          }
        } catch (error) {
          console.error('Validation error:', error);
        }
      }

      async function checkPasswordStrength(password) {
        if (password.length < 3) {
          passwordStrength.style.display = 'none';
          return;
        }

        try {
          const response = await fetch('{{ route('register.check-password') }}', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              password: password,
              password_confirmation: document.getElementById('password_confirmation').value
            })
          });

          const data = await response.json();

          passwordStrength.style.display = 'block';
          passwordStrength.textContent = data.message;

          if (data.valid && data.strength) {
            if (data.strength >= 80) {
              passwordStrength.className = 'password-strength strength-strong';
            } else if (data.strength >= 60) {
              passwordStrength.className = 'password-strength strength-good';
            } else if (data.strength >= 40) {
              passwordStrength.className = 'password-strength strength-fair';
            } else {
              passwordStrength.className = 'password-strength strength-weak';
            }
          }
        } catch (error) {
          console.error('Password strength check error:', error);
        }
      }

      function setFieldError(field, message) {
        field.classList.remove('success');
        field.classList.add('error');

        let errorElement = field.parentNode.querySelector('.form-error');
        if (!errorElement) {
          errorElement = document.createElement('span');
          errorElement.className = 'form-error';
          field.parentNode.appendChild(errorElement);
        }
        errorElement.textContent = message;

        // Remove success message if exists
        const successElement = field.parentNode.querySelector('.form-success');
        if (successElement) {
          successElement.remove();
        }
      }

      function setFieldSuccess(field, message) {
        field.classList.remove('error');
        field.classList.add('success');

        let successElement = field.parentNode.querySelector('.form-success');
        if (!successElement) {
          successElement = document.createElement('span');
          successElement.className = 'form-success';
          field.parentNode.appendChild(successElement);
        }
        successElement.textContent = message;

        // Remove error message if exists
        const errorElement = field.parentNode.querySelector('.form-error');
        if (errorElement) {
          errorElement.remove();
        }
      }

      function clearFieldValidation(field) {
        field.classList.remove('error', 'success');

        const errorElement = field.parentNode.querySelector('.form-error');
        const successElement = field.parentNode.querySelector('.form-success');

        if (errorElement) errorElement.remove();
        if (successElement) successElement.remove();
      }

      function validateForm() {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
          if (!field.value.trim()) {
            setFieldError(field, 'This field is required');
            isValid = false;
          }
        });

        // Check password confirmation
        const password = document.getElementById('password').value;
        const passwordConfirmation = document.getElementById('password_confirmation').value;

        if (password !== passwordConfirmation) {
          setFieldError(document.getElementById('password_confirmation'), 'Passwords do not match');
          isValid = false;
        }

        return isValid;
      }
    });
  </script>
@endpush
