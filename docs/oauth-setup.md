# Google OAuth Implementation for HD Tickets

This document describes the Google OAuth authentication implementation for the HD Tickets sports events monitoring platform.

## Overview

Users can now register and login using their Google Gmail accounts in addition to the traditional email/password authentication. This provides a seamless onboarding experience and reduces friction for new users.

## Implementation Components

### 1. Database Schema

Added the following fields to the `users` table via migration:
- `google_id` (nullable string) - Google user ID for backwards compatibility
- `avatar` (nullable string) - User's profile picture URL from OAuth provider
- `provider` (nullable string) - OAuth provider name (e.g., 'google')
- `provider_id` (nullable string) - Unique user ID from OAuth provider
- `provider_verified_at` (nullable timestamp) - When OAuth verification was completed

### 2. User Model Updates

Updated `App\Models\User` to:
- Include new OAuth fields in fillable array
- Cast `provider_verified_at` as datetime
- Support users without passwords (OAuth-only users)

### 3. OAuth Service Layer

Created `App\Services\OAuthUserService` with the following capabilities:
- Find or create users from OAuth provider data
- Link existing accounts with OAuth providers
- Handle user activity tracking
- Generate secure passwords for OAuth users who want to set one
- Manage supported OAuth providers configuration

### 4. OAuth Controller

Implemented `App\Http\Controllers\Auth\OAuthController` with endpoints:
- `GET /auth/{provider}` - Redirect to OAuth provider
- `GET /auth/{provider}/callback` - Handle OAuth callback
- `GET /auth/link` - Show account linking page
- `GET /auth/{provider}/link` - Link existing account
- `GET /auth/{provider}/link/callback` - Handle account linking callback
- `DELETE /auth/{provider}/unlink` - Unlink OAuth provider

### 5. Routes Configuration

Added OAuth routes in `routes/auth.php`:
- Guest routes for initial OAuth login/register
- Authenticated routes for account linking/unlinking
- Support for multiple providers (currently Google, extensible for Facebook, Twitter, etc.)

### 6. Frontend Integration

Updated views to include Google OAuth buttons:
- Login form (`resources/views/components/auth/login-form.blade.php`)
- Public registration form (`resources/views/auth/public-register.blade.php`)
- Modern welcome page (`resources/views/welcome-modern.blade.php`)

### 7. Configuration

OAuth providers are configured in `config/services.php` using environment variables:
```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL'),
],
```

## Setup Instructions

### 1. Google OAuth App Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable Google+ API
4. Go to "Credentials" and create OAuth 2.0 Client ID
5. Set authorized redirect URIs:
   - `https://yourdomain.com/auth/google/callback`
6. Copy Client ID and Client Secret

### 2. Environment Configuration

Add to your `.env` file:
```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URL=${APP_URL}/auth/google/callback
```

### 3. Database Migration

Run the migration to add OAuth fields:
```bash
php artisan migrate
```

## Usage Flow

### New User Registration via Google

1. User clicks "Continue with Google" on registration or welcome page
2. Redirected to Google OAuth consent screen
3. After approval, redirected back to `/auth/google/callback`
4. New user account is created with:
   - Name from Google profile
   - Email from Google (verified)
   - Google avatar image
   - Customer role by default
   - No password (OAuth-only initially)

### Existing User Login via Google

1. User clicks "Sign in with Google" on login page
2. System finds existing user by email or OAuth provider ID
3. User is logged in and redirected to appropriate dashboard

### Account Linking

Existing users can link their Google account:
1. Go to profile security settings
2. Click "Link Google Account"
3. Complete OAuth flow
4. Account is now linked and can use either authentication method

## Security Features

- OAuth users are considered email-verified automatically
- Comprehensive error handling and logging
- CSRF protection on all routes
- Rate limiting on OAuth endpoints
- Secure session management
- Optional password setting for OAuth users

## Extensibility

The system is designed to support additional OAuth providers:
- Add provider configuration to `config/services.php`
- Update `OAuthUserService::getSupportedProviders()`
- Provider-specific logic can be added to service methods

## Testing

To test the OAuth flow:
1. Set up Google OAuth credentials
2. Visit the login or registration page
3. Click "Continue with Google"
4. Verify user creation and login flow

## Error Handling

The system handles various OAuth error scenarios:
- User cancellation
- Email permission denied
- Network errors
- Invalid OAuth state
- Account already linked to different user
- Rate limiting

All errors are logged and user-friendly messages are displayed.

## Compliance

- GDPR compliant user data handling
- Users can unlink OAuth accounts
- Clear privacy notices about data usage
- Secure token handling without storing sensitive OAuth tokens