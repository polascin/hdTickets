# Authentication & Registration Updates (October 2025)

This document summarises recent improvements to authentication and registration.

## Highlights
- Enhanced login view selection via configuration
- reCAPTCHA middleware alias for bot protection
- Public registration flow with realtime validation
- Optional 2FA prompt after registration

## Login Views
- When `AUTH_ENHANCED_LOGIN=true`, the app renders `resources/views/auth/login-enhanced.blade.php`.
- Fallback order: enhanced → comprehensive → basic.

## reCAPTCHA Middleware
- Alias: `recaptcha:{action}` (e.g., `recaptcha:login`, `recaptcha:register`).
- Apply to sensitive routes such as POST `/login` and POST `/register`.

Example:
```php
Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['throttle:login', 'recaptcha:login']);
```

## Public Registration Endpoints
- GET `/register/public` → PublicRegistrationController@create
- POST `/register/public` → PublicRegistrationController@store
  - Middleware: `throttle:12,1`, `recaptcha:register`
- Validation endpoints (throttle:60,1):
  - POST `/register/public/validation/check-email`
  - POST `/register/public/validation/check-phone`
  - POST `/register/public/validation/check-password`
  - POST `/register/public/validation/validate-field`

## 2FA Prompt After Registration
- Config: `AUTH_REGISTRATION_TWO_FACTOR_PROMPT` (default true in `.env.example`).
- Checked in controller via `config('auth.registration.two_factor_prompt')`.

## Environment Variables
Add to `.env` as needed:
```
AUTH_ENHANCED_LOGIN=true
AUTH_COMPREHENSIVE_LOGIN=true
AUTH_MAX_FAILED_ATTEMPTS=5
AUTH_LOCKOUT_DURATION=900
AUTH_DEVICE_FINGERPRINTING=true
AUTH_GEOLOCATION_TRACKING=true
AUTH_PROGRESSIVE_VALIDATION=true
AUTH_BIOMETRIC_AUTH=true
AUTH_PASSWORD_STRENGTH_METER=true
AUTH_SESSION_WARNINGS=true
AUTH_REGISTRATION_TWO_FACTOR_PROMPT=true

RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key
RECAPTCHA_ENABLED=true
RECAPTCHA_VERSION=v2
RECAPTCHA_THRESHOLD=0.5
```

## Post-Deploy
- Clear caches after deploying route/middleware changes:
```
php artisan optimize:clear && php artisan config:clear && php artisan route:clear && php artisan cache:clear
```
