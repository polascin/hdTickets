# HD Tickets Login Page Customization Guide

## Overview

This guide provides comprehensive instructions for customizing the HD Tickets login page while maintaining security, accessibility, and performance standards. All customizations should preserve the existing security features and accessibility compliance.

## Visual Customization

### Theme Customization

#### Color Scheme
Modify the CSS custom properties in `resources/css/auth/login.css`:

```css
:root {
  /* Primary Colors */
  --hd-primary-50: #eff6ff;
  --hd-primary-100: #dbeafe;
  --hd-primary-500: #3b82f6;
  --hd-primary-600: #2563eb;
  --hd-primary-700: #1d4ed8;
  --hd-primary-900: #1e3a8a;
  
  /* Focus and Interaction */
  --hd-focus-color: #3b82f6;
  --hd-hover-color: #2563eb;
  
  /* Status Colors */
  --hd-error-color: #dc2626;
  --hd-success-color: #16a34a;
  --hd-warning-color: #d97706;
  --hd-info-color: #0891b2;
}
```

#### Dark Mode Support
Enable system-based dark mode detection:

```css
@media (prefers-color-scheme: dark) {
  :root {
    --hd-primary-50: #0c1427;
    --hd-primary-100: #1e293b;
    --hd-primary-500: #60a5fa;
    --hd-primary-600: #3b82f6;
    --hd-primary-700: #2563eb;
    --hd-primary-900: #dbeafe;
    
    --hd-bg-primary: #0f172a;
    --hd-text-primary: #f8fafc;
    --hd-border-color: #334155;
  }
}
```

### Layout Customization

#### Form Container Width
Adjust the form container dimensions:

```css
.hd-auth-container {
  max-width: 400px; /* Default: 384px */
  width: 100%;
  margin: 0 auto;
  padding: 2rem; /* Adjust spacing */
}
```

#### Field Spacing
Customize form field spacing:

```css
.hd-form-group {
  margin-bottom: 1.5rem; /* Adjust vertical spacing */
}

.hd-form-input {
  padding: 0.75rem 1rem; /* Adjust input padding */
  font-size: 1rem; /* Adjust font size */
  line-height: 1.5; /* Adjust line height */
}
```

### Logo and Branding

#### Adding Custom Logo
Add a logo above the form in `resources/views/auth/login.blade.php`:

```html
<!-- Add after the skip navigation links -->
<div class="hd-logo-container text-center mb-8">
    <img src="{{ asset('images/hd-tickets-logo.svg') }}" 
         alt="HD Tickets Logo" 
         class="hd-logo mx-auto h-12 w-auto">
    <h1 class="hd-sr-only">HD Tickets Login</h1>
</div>
```

#### Logo Styling
Style the logo container:

```css
.hd-logo-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-bottom: 2rem;
}

.hd-logo {
  max-height: 3rem;
  width: auto;
  filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.1));
}

@media (prefers-color-scheme: dark) {
  .hd-logo {
    filter: brightness(0) invert(1) drop-shadow(0 1px 3px rgba(255, 255, 255, 0.1));
  }
}
```

### Background Customization

#### Solid Background
```css
.hd-auth-background {
  background: linear-gradient(135deg, var(--hd-primary-600), var(--hd-primary-800));
  min-height: 100vh;
  display: flex;
  align-items: center;
}
```

#### Pattern Background
```css
.hd-auth-background {
  background-image: 
    radial-gradient(circle at 1px 1px, var(--hd-primary-100) 1px, transparent 0);
  background-size: 20px 20px;
  background-color: var(--hd-primary-50);
}
```

## Field Customization

### Input Field Styling

#### Rounded Corners
```css
.hd-form-input {
  border-radius: 0.75rem; /* More rounded */
}

.hd-form-input:focus {
  border-radius: 0.75rem;
}
```

#### Enhanced Icons
Add custom field icons:

```css
.hd-form-group.has-icon {
  position: relative;
}

.hd-form-group.has-icon .hd-field-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--hd-text-muted);
  z-index: 10;
}

.hd-form-group.has-icon .hd-form-input {
  padding-left: 3rem;
}
```

### Button Customization

#### Primary Button Styling
```css
.hd-btn-primary {
  background: linear-gradient(135deg, var(--hd-primary-600), var(--hd-primary-700));
  border: none;
  border-radius: 0.5rem;
  padding: 0.75rem 2rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px 0 rgba(59, 130, 246, 0.25);
}

.hd-btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 8px 25px rgba(59, 130, 246, 0.35);
}
```

#### Loading Animation
Customize the loading spinner:

```css
.hd-btn-loading .spinner {
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  width: 1rem;
  height: 1rem;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
```

## Content Customization

### Text Content

#### Customizing Labels and Messages
Modify language files in `resources/lang/en/auth.php`:

```php
return [
    'email' => 'Email Address',
    'password' => 'Password',
    'remember' => 'Keep me signed in',
    'sign_in' => 'Sign In',
    'forgot_password' => 'Forgot your password?',
    'failed' => 'Invalid login credentials. Please check your email and password.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
];
```

#### Custom Welcome Message
Add a welcome message above the form:

```html
<!-- Add in login.blade.php after registration notice -->
<div class="hd-welcome-message text-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back</h2>
    <p class="text-gray-600">Sign in to access your HD Tickets account</p>
</div>
```

### Form Fields

#### Adding Custom Fields
To add new fields (while maintaining security):

```html
<!-- Custom field example -->
<div class="hd-form-group space-y-1">
    <label for="organization" class="hd-form-label form-label">
        Organization <span class="text-gray-400">(Optional)</span>
    </label>
    
    <input id="organization" 
           class="hd-form-input form-input" 
           type="text" 
           name="organization" 
           value="{{ old('organization') }}" 
           autocomplete="organization"
           aria-label="Organization name (optional)">
</div>
```

#### Field Validation Customization
Add custom validation rules in `LoginRequest.php`:

```php
public function rules(): array
{
    return [
        'email' => ['required', 'string', 'email', 'max:255'],
        'password' => ['required', 'string'],
        'organization' => ['nullable', 'string', 'max:100'],
        'website' => [new HoneypotRule], // Keep honeypot field
    ];
}
```

## JavaScript Customization

### Authentication Security

#### Custom Security Features
Extend the AuthSecurity class:

```javascript
class CustomAuthSecurity extends AuthSecurity {
    constructor() {
        super();
        this.setupCustomValidation();
        this.setupCustomRateLimit();
    }
    
    setupCustomValidation() {
        // Add custom client-side validation
        const form = document.getElementById('login-form');
        const emailField = document.getElementById('email');
        
        emailField.addEventListener('blur', (e) => {
            this.validateEmailDomain(e.target.value);
        });
    }
    
    validateEmailDomain(email) {
        const allowedDomains = ['company.com', 'organization.org'];
        const domain = email.split('@')[1];
        
        if (allowedDomains.length > 0 && !allowedDomains.includes(domain)) {
            this.showFieldError('email', 'Please use your organization email address');
        }
    }
}

// Initialize custom security
document.addEventListener('DOMContentLoaded', () => {
    new CustomAuthSecurity();
});
```

#### Custom Rate Limiting UI
Customize the rate limiting display:

```javascript
handleCustomRateLimit(seconds) {
    const countdownElement = document.createElement('div');
    countdownElement.className = 'hd-custom-countdown';
    countdownElement.innerHTML = `
        <div class="countdown-header">
            <h3>Security Timeout Active</h3>
            <p>Your account is temporarily locked for security.</p>
        </div>
        <div class="countdown-timer">
            <div class="timer-display">
                <span class="minutes">0</span>:<span class="seconds">00</span>
            </div>
            <div class="progress-ring">
                <svg class="progress-ring-svg">
                    <circle class="progress-ring-circle"></circle>
                </svg>
            </div>
        </div>
    `;
    
    // Add custom countdown logic
    this.startCustomCountdown(seconds, countdownElement);
}
```

## Backend Customization

### Authentication Logic

#### Custom Authentication Provider
Create a custom authentication service:

```php
<?php

namespace App\Services\Auth;

use App\Services\Security\AuthenticationService;

class CustomAuthenticationService extends AuthenticationService
{
    public function authenticate(array $credentials): bool
    {
        // Add custom authentication logic
        if (!$this->validateCustomCriteria($credentials)) {
            return false;
        }
        
        return parent::authenticate($credentials);
    }
    
    protected function validateCustomCriteria(array $credentials): bool
    {
        // Custom validation logic
        // e.g., time-based access, IP restrictions, etc.
        return true;
    }
}
```

#### Custom Validation Rules
Add custom validation in `app/Rules/`:

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class BusinessHoursRule implements Rule
{
    public function passes($attribute, $value)
    {
        $hour = now()->hour;
        return $hour >= 8 && $hour <= 18; // 8 AM to 6 PM
    }
    
    public function message()
    {
        return 'Login is only allowed during business hours (8 AM - 6 PM).';
    }
}
```

### Security Customization

#### Custom Rate Limiting
Modify rate limiting in `LoginRequest.php`:

```php
public function authenticate(): void
{
    $this->ensureIsNotRateLimited();
    
    // Custom rate limiting logic
    $this->customRateLimit();
    
    if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
        RateLimiter::hit($this->throttleKey());
        
        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }
    
    RateLimiter::clear($this->throttleKey());
}

protected function customRateLimit(): void
{
    $key = 'custom-login:' . request()->ip();
    $maxAttempts = 3; // Custom limit
    $decayMinutes = 15; // Custom timeout
    
    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
        $seconds = RateLimiter::availableIn($key);
        throw ValidationException::withMessages([
            'email' => "Custom rate limit exceeded. Try again in {$seconds} seconds.",
        ]);
    }
}
```

## Mobile Customization

### Responsive Design

#### Mobile-First Styling
```css
/* Mobile styles (default) */
.hd-auth-container {
  padding: 1rem;
  max-width: 100%;
}

.hd-form-input {
  font-size: 16px; /* Prevents zoom on iOS */
  padding: 0.875rem 1rem;
}

/* Tablet styles */
@media (min-width: 640px) {
  .hd-auth-container {
    max-width: 400px;
    padding: 2rem;
  }
}

/* Desktop styles */
@media (min-width: 1024px) {
  .hd-auth-container {
    max-width: 480px;
  }
  
  .hd-form-input {
    font-size: 14px;
    padding: 0.75rem 1rem;
  }
}
```

#### Touch-Friendly Interactions
```css
.hd-btn-primary {
  min-height: 44px; /* iOS touch target minimum */
  touch-action: manipulation;
}

.hd-form-input {
  min-height: 44px;
}

@media (max-width: 640px) {
  .hd-checkbox-wrapper {
    padding: 0.5rem;
    margin: -0.5rem;
  }
}
```

## Accessibility Customization

### Enhanced Screen Reader Support

#### Custom ARIA Labels
```html
<form method="POST" action="{{ route('login') }}" 
      class="space-y-6 enhanced-form hd-form"
      role="form"
      aria-label="Sign in to HD Tickets"
      aria-describedby="form-instructions">
      
    <div id="form-instructions" class="hd-sr-only">
        This form allows you to sign in to your HD Tickets account. 
        All fields are required. Use Tab to navigate between fields.
    </div>
</form>
```

#### Custom Focus Management
```javascript
class AccessibilityEnhancer {
    constructor() {
        this.setupFocusTrapping();
        this.setupKeyboardNavigation();
    }
    
    setupFocusTrapping() {
        const form = document.getElementById('login-form');
        const focusableElements = form.querySelectorAll(
            'input:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        form.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    }
}
```

### Color Contrast Customization

#### High Contrast Mode
```css
@media (prefers-contrast: high) {
  :root {
    --hd-primary-600: #0056b3;
    --hd-text-primary: #000000;
    --hd-bg-primary: #ffffff;
    --hd-border-color: #000000;
    --hd-error-color: #cc0000;
    --hd-success-color: #008000;
  }
  
  .hd-form-input {
    border-width: 2px;
    border-color: var(--hd-border-color);
  }
  
  .hd-form-input:focus {
    outline-width: 3px;
    outline-offset: 2px;
  }
}
```

## Testing Customizations

### Accessibility Testing
```bash
# Install accessibility testing tools
npm install --save-dev @axe-core/cli

# Run accessibility tests
npx axe-cli http://localhost:8000/login

# Test with screen readers
# - NVDA (Windows)
# - JAWS (Windows)  
# - VoiceOver (macOS)
# - TalkBack (Android)
```

### Performance Testing
```bash
# Test performance impact
npm install --save-dev lighthouse-cli

# Run performance audit
npx lighthouse http://localhost:8000/login --output=html --output-path=./login-performance.html
```

### Cross-Browser Testing
```javascript
// Browser compatibility test
const testBrowserSupport = () => {
    const features = {
        cssFocusVisible: CSS.supports('selector(:focus-visible)'),
        customProperties: CSS.supports('color', 'var(--test)'),
        gridLayout: CSS.supports('display', 'grid'),
        flexbox: CSS.supports('display', 'flex')
    };
    
    console.log('Browser support:', features);
    return Object.values(features).every(Boolean);
};
```

## Maintenance Best Practices

### Version Control
- Always test customizations in a development environment
- Use feature branches for major customizations
- Document all changes in commit messages
- Maintain a changelog for customizations

### Security Considerations
- Never remove existing security features
- Always validate custom input fields
- Test rate limiting after modifications
- Ensure CSRF protection remains intact

### Performance Monitoring
- Monitor login performance after customizations
- Test on various devices and network conditions
- Use browser developer tools for optimization
- Implement performance budgets for assets

### Accessibility Maintenance
- Test with screen readers after changes
- Validate color contrast ratios
- Ensure keyboard navigation remains functional
- Run automated accessibility tests regularly

## Rollback Procedures

### Emergency Rollback
1. Restore original files from version control
2. Clear application cache: `php artisan cache:clear`
3. Clear route cache: `php artisan route:clear`
4. Clear view cache: `php artisan view:clear`
5. Test login functionality immediately

### Gradual Rollback
1. Identify problematic customizations
2. Remove changes incrementally
3. Test after each removal
4. Document issues for future reference

This customization guide ensures that any modifications maintain the security, accessibility, and performance standards of the HD Tickets login system while providing flexibility for organizational branding and user experience requirements.
