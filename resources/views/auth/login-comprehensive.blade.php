<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
      content="HD Tickets Login - Access your professional sports event ticket monitoring dashboard">
    <meta name="keywords" content="login, sports tickets, ticket monitoring, hd tickets, dashboard access">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="security-policy" content="comprehensive-login-v4">

    <title>Login - HD Tickets Professional Platform</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
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
