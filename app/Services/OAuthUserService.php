<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;

class OAuthUserService
{
    /**
     * Find or create a user from OAuth provider data
     */
    public function findOrCreateUser(SocialiteUser $socialiteUser, string $provider): User
    {
        // First, try to find existing user by provider ID
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialiteUser->getId())
            ->first();

        if ($user) {
            // Update user data with latest info from provider
            $this->updateUserFromProvider($user, $socialiteUser, $provider);

            return $user;
        }

        // If no user found by provider ID, check by email
        $existingUser = User::where('email', $socialiteUser->getEmail())->first();

        if ($existingUser) {
            // Link existing account with OAuth provider
            $this->linkAccountWithProvider($existingUser, $socialiteUser, $provider);

            return $existingUser;
        }

        // Create new user
        return $this->createUserFromProvider($socialiteUser, $provider);
    }

    /**
     * Generate a secure random password for OAuth users who want to set one
     */
    public function generateSecurePassword(): string
    {
        return Str::random(16);
    }

    /**
     * Set password for OAuth user
     */
    public function setPasswordForOAuthUser(User $user, string $password): bool
    {
        if (!$user->provider) {
            return FALSE; // Not an OAuth user
        }

        $user->update([
            'password'            => Hash::make($password),
            'password_changed_at' => now(),
        ]);

        return TRUE;
    }

    /**
     * Check if user can login with OAuth provider
     */
    public function canLoginWithProvider(User $user, string $provider): bool
    {
        if (!$user->is_active) {
            return FALSE;
        }

        // Check if user has this provider linked
        return $user->provider === $provider && $user->provider_id;
    }

    /**
     * Get OAuth login URL for provider
     */
    public function getLoginUrl(string $provider): string
    {
        return route('oauth.redirect', ['provider' => $provider]);
    }

    /**
     * Get supported OAuth providers
     */
    public function getSupportedProviders(): array
    {
        return [
            'google' => [
                'name'    => 'Google',
                'icon'    => 'fab fa-google',
                'color'   => 'danger',
                'enabled' => config('services.google.client_id') ? TRUE : FALSE,
            ],
            // Add more providers here as needed
            // 'facebook' => [
            //     'name' => 'Facebook',
            //     'icon' => 'fab fa-facebook-f',
            //     'color' => 'primary',
            //     'enabled' => config('services.facebook.client_id') ? true : false,
            // ],
        ];
    }

    /**
     * Handle OAuth user activity tracking
     */
    public function trackOAuthActivity(User $user, string $provider, string $action): void
    {
        // Update last login information
        if ($action === 'login') {
            $user->update([
                'last_login_at'         => now(),
                'last_login_ip'         => request()->ip(),
                'last_login_user_agent' => request()->userAgent(),
                'last_activity_at'      => now(),
                'login_count'           => ($user->login_count ?? 0) + 1,
                'failed_login_attempts' => 0, // Reset failed attempts on successful login
            ]);
        }
    }

    /**
     * Create a new user from OAuth provider data
     */
    protected function createUserFromProvider(SocialiteUser $socialiteUser, string $provider): User
    {
        $name = $this->extractName($socialiteUser);

        $user = User::create([
            'name'                 => $name['first'],
            'surname'              => $name['last'],
            'email'                => $socialiteUser->getEmail(),
            'avatar'               => $socialiteUser->getAvatar(),
            'provider'             => $provider,
            'provider_id'          => $socialiteUser->getId(),
            'provider_verified_at' => now(),
            'email_verified_at'    => now(), // OAuth users are considered verified
            'role'                 => User::ROLE_CUSTOMER, // Default role for OAuth users
            'is_active'            => TRUE,
            'registration_source'  => 'oauth_' . $provider,
            'password'             => NULL, // OAuth users don't have passwords initially
        ]);

        // Add Google ID for backwards compatibility
        if ($provider === 'google') {
            $user->update(['google_id' => $socialiteUser->getId()]);
        }

        return $user;
    }

    /**
     * Update existing user with latest provider data
     */
    protected function updateUserFromProvider(User $user, SocialiteUser $socialiteUser, string $provider): void
    {
        $updates = [];

        // Update avatar if changed
        if ($socialiteUser->getAvatar() && $user->avatar !== $socialiteUser->getAvatar()) {
            $updates['avatar'] = $socialiteUser->getAvatar();
        }

        // Update provider verified timestamp
        $updates['provider_verified_at'] = now();

        // Update last activity
        $updates['last_activity_at'] = now();

        if (!empty($updates)) {
            $user->update($updates);
        }
    }

    /**
     * Link existing user account with OAuth provider
     */
    protected function linkAccountWithProvider(User $user, SocialiteUser $socialiteUser, string $provider): void
    {
        $user->update([
            'provider'             => $provider,
            'provider_id'          => $socialiteUser->getId(),
            'provider_verified_at' => now(),
            'avatar'               => $user->avatar ?: $socialiteUser->getAvatar(),
            'last_activity_at'     => now(),
        ]);

        // Add Google ID for backwards compatibility
        if ($provider === 'google') {
            $user->update(['google_id' => $socialiteUser->getId()]);
        }
    }

    /**
     * Extract first and last name from OAuth provider data
     */
    protected function extractName(SocialiteUser $socialiteUser): array
    {
        $fullName = $socialiteUser->getName() ?? '';
        $nameParts = explode(' ', trim($fullName), 2);

        return [
            'first' => $nameParts[0] ?? '',
            'last'  => $nameParts[1] ?? '',
        ];
    }
}
