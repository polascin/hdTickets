# Auth.php Fix Summary

## Issues Fixed

### 1. **API Guard Configuration**
- **Problem**: `auth.php` was configured to use Laravel Passport for API authentication
- **Solution**: Updated API guard driver from `passport` to `sanctum`
- **File**: `/var/www/hdtickets/config/auth.php`

### 2. **Sanctum Configuration**
- **Problem**: Missing Sanctum configuration file
- **Solution**: Created comprehensive `sanctum.php` configuration with HD Tickets specific settings
- **File**: `/var/www/hdtickets/config/sanctum.php`

### 3. **User Model Authentication**
- **Problem**: User model was using Passport's `HasApiTokens` trait and `OAuthenticatable` interface
- **Solution**: Updated to use Sanctum's `HasApiTokens` trait and removed Passport interface
- **File**: `/var/www/hdtickets/app/Models/User.php`

### 4. **Service Provider Cleanup**
- **Problem**: AuthServiceProvider and AppServiceProvider had Passport-specific configurations
- **Solution**: Removed Passport imports and configurations from both providers
- **Files**: 
  - `/var/www/hdtickets/app/Providers/AuthServiceProvider.php`
  - `/var/www/hdtickets/app/Providers/AppServiceProvider.php`

### 5. **Database Migration**
- **Problem**: Missing Sanctum personal access tokens table migration
- **Solution**: Created Sanctum migration for personal access tokens
- **File**: `/var/www/hdtickets/database/migrations/2024_01_15_000000_create_personal_access_tokens_table.php`

### 6. **Environment Configuration**
- **Problem**: Missing Sanctum-specific environment variables
- **Solution**: Added Sanctum configuration to `.env.example`
- **File**: `/var/www/hdtickets/.env.example`

### 7. **User Model Relationships**
- **Problem**: Missing relationship for marketing campaigns
- **Solution**: Added `marketingCampaigns()` relationship method
- **File**: `/var/www/hdtickets/app/Models/User.php`

## Configuration Summary

### Auth Guard Configuration
```php
'guards' => [
    'web' => [
        'driver'   => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver'   => 'sanctum',  // Changed from 'passport'
        'provider' => 'users',
    ],
],
```

### Sanctum Settings
- **Stateful Domains**: `localhost,127.0.0.1,hdtickets.local`
- **Token Expiration**: 1 year (525600 minutes)
- **Rate Limiting**: Enabled (60 requests per minute)
- **Secure Cookies**: Environment-based (production only)

### User Model Updates
- **Trait**: `Laravel\Sanctum\HasApiTokens` (instead of Passport)
- **Interface**: Removed `OAuthenticatable` interface
- **Relationship**: Added `marketingCampaigns()` method

## Verification Steps

1. **Check API Authentication**:
   ```bash
   # Test API token generation
   php artisan tinker
   $user = User::first();
   $token = $user->createToken('test');
   echo $token->plainTextToken;
   ```

2. **Verify Database Migration**:
   ```bash
   php artisan migrate
   # Should create personal_access_tokens table
   ```

3. **Test API Routes**:
   ```bash
   # Should work with Bearer token authentication
   curl -H "Authorization: Bearer {token}" https://hdtickets.local/api/v1/user
   ```

## Files Modified

1. `/var/www/hdtickets/config/auth.php` - Updated API guard
2. `/var/www/hdtickets/config/sanctum.php` - Created Sanctum configuration
3. `/var/www/hdtickets/app/Models/User.php` - Updated authentication traits
4. `/var/www/hdtickets/app/Providers/AuthServiceProvider.php` - Removed Passport
5. `/var/www/hdtickets/app/Providers/AppServiceProvider.php` - Removed Passport
6. `/var/www/hdtickets/database/migrations/2024_01_15_000000_create_personal_access_tokens_table.php` - Created
7. `/var/www/hdtickets/.env.example` - Added Sanctum variables

## Next Steps

1. Run database migrations: `php artisan migrate`
2. Clear configuration cache: `php artisan config:clear`
3. Test API authentication with frontend
4. Update any remaining Passport references in custom code

The authentication system is now fully configured to use Laravel Sanctum instead of Passport, providing better integration with the HD Tickets platform's API and marketing dashboard features.