<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OAuthUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

use function in_array;

class OAuthController extends Controller
{
    public function __construct(
        protected OAuthUserService $oauthUserService,
    ) {
    }

    /**
     * Redirect to OAuth provider
     */
    public function redirect(string $provider): RedirectResponse
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')
                    ->withErrors(['oauth' => 'Unsupported OAuth provider.']);
            }

            // Store the intended URL in session for post-login redirect
            if (request()->has('intended')) {
                session(['url.intended' => request('intended')]);
            }

            return Socialite::driver($provider)->redirect();
        } catch (Throwable $e) {
            logger()->error('OAuth redirect failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return redirect()->route('login')
                ->withErrors(['oauth' => 'Failed to initiate OAuth login. Please try again.']);
        }
    }

    /**
     * Handle OAuth provider callback
     */
    public function callback(string $provider, Request $request): RedirectResponse
    {
        try {
            // Validate provider
            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('login')
                    ->withErrors(['oauth' => 'Unsupported OAuth provider.']);
            }

            // Handle OAuth errors (user cancelled, etc.)
            if ($request->has('error')) {
                $error = $request->get('error');
                $errorDescription = $request->get('error_description', 'OAuth authentication failed');

                logger()->warning('OAuth callback error', [
                    'provider'    => $provider,
                    'error'       => $error,
                    'description' => $errorDescription,
                ]);

                return redirect()->route('login')
                    ->withErrors(['oauth' => 'Authentication cancelled or failed. Please try again.']);
            }

            // Get user data from OAuth provider
            $socialiteUser = Socialite::driver($provider)->user();

            if (!$socialiteUser->getEmail()) {
                return redirect()->route('login')
                    ->withErrors(['oauth' => 'Email is required for registration. Please ensure your ' . ucfirst($provider) . ' account has a verified email.']);
            }

            // Find or create user
            $user = $this->oauthUserService->findOrCreateUser($socialiteUser, $provider);

            // Check if user account is active
            if (!$user->is_active) {
                return redirect()->route('login')
                    ->withErrors(['oauth' => 'Your account is deactivated. Please contact support.']);
            }

            // Track OAuth activity
            $this->oauthUserService->trackOAuthActivity($user, $provider, 'login');

            // Log the user in
            Auth::login($user, TRUE); // Remember the user

            // Log successful OAuth login
            logger()->info('OAuth login successful', [
                'user_id'  => $user->id,
                'provider' => $provider,
                'email'    => $user->email,
            ]);

            // Redirect to intended URL or dashboard
            $redirectUrl = session()->pull('url.intended', $this->getPostLoginRedirectUrl($user));

            return redirect($redirectUrl)
                ->with('success', 'Successfully logged in with ' . ucfirst($provider) . '!');
        } catch (Throwable $e) {
            logger()->error('OAuth callback failed', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            $errorMessage = 'OAuth authentication failed. Please try again.';

            // Provide more specific error messages for common issues
            if (str_contains($e->getMessage(), 'email')) {
                $errorMessage = 'Unable to retrieve email from ' . ucfirst($provider) . '. Please ensure your account has a verified email address.';
            } elseif (str_contains($e->getMessage(), 'connection') || str_contains($e->getMessage(), 'network')) {
                $errorMessage = 'Network error occurred during authentication. Please check your connection and try again.';
            } elseif (str_contains($e->getMessage(), 'token')) {
                $errorMessage = 'Authentication token error. Please try logging in again.';
            }

            return redirect()->route('login')
                ->withErrors(['oauth' => $errorMessage]);
        }
    }

    /**
     * Show OAuth account linking page (for logged-in users who want to link OAuth)
     */
    public function linkAccount(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $providers = $this->oauthUserService->getSupportedProviders();
        $user = Auth::user();

        return view('auth.oauth.link-account', compact('providers', 'user'));
    }

    /**
     * Handle OAuth account linking for existing users
     */
    public function linkCallback(string $provider, Request $request): RedirectResponse
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login')
                    ->withErrors(['oauth' => 'You must be logged in to link accounts.']);
            }

            if (!$this->isProviderSupported($provider)) {
                return redirect()->route('profile.security')
                    ->withErrors(['oauth' => 'Unsupported OAuth provider.']);
            }

            $socialiteUser = Socialite::driver($provider)->user();
            $currentUser = Auth::user();

            // Check if this OAuth account is already linked to another user
            $existingUser = User::where('provider', $provider)
                ->where('provider_id', $socialiteUser->getId())
                ->where('id', '!=', $currentUser->id)
                ->first();

            if ($existingUser) {
                return redirect()->route('profile.security')
                    ->withErrors(['oauth' => 'This ' . ucfirst($provider) . ' account is already linked to another user.']);
            }

            // Link the account
            $currentUser->update([
                'provider'             => $provider,
                'provider_id'          => $socialiteUser->getId(),
                'provider_verified_at' => now(),
                'avatar'               => $currentUser->avatar ?: $socialiteUser->getAvatar(),
            ]);

            // Add Google ID for backwards compatibility
            if ($provider === 'google') {
                $currentUser->update(['google_id' => $socialiteUser->getId()]);
            }

            logger()->info('OAuth account linked', [
                'user_id'  => $currentUser->id,
                'provider' => $provider,
            ]);

            return redirect()->route('profile.security')
                ->with('success', ucfirst($provider) . ' account linked successfully!');
        } catch (Throwable $e) {
            logger()->error('OAuth account linking failed', [
                'provider' => $provider,
                'user_id'  => Auth::id(),
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('profile.security')
                ->withErrors(['oauth' => 'Failed to link ' . ucfirst($provider) . ' account. Please try again.']);
        }
    }

    /**
     * Unlink OAuth provider from user account
     */
    public function unlinkAccount(string $provider, Request $request): RedirectResponse
    {
        try {
            if (!Auth::check()) {
                return redirect()->route('login');
            }

            $user = Auth::user();

            // Ensure user has a password before unlinking OAuth (security measure)
            if (!$user->password && $user->provider === $provider) {
                return redirect()->route('profile.security')
                    ->withErrors(['oauth' => 'You must set a password before unlinking your ' . ucfirst($provider) . ' account.']);
            }

            // Remove OAuth provider information
            $updates = [
                'provider'             => NULL,
                'provider_id'          => NULL,
                'provider_verified_at' => NULL,
            ];

            // Remove Google ID for backwards compatibility
            if ($provider === 'google') {
                $updates['google_id'] = NULL;
            }

            $user->update($updates);

            logger()->info('OAuth account unlinked', [
                'user_id'  => $user->id,
                'provider' => $provider,
            ]);

            return redirect()->route('profile.security')
                ->with('success', ucfirst($provider) . ' account unlinked successfully!');
        } catch (Throwable $e) {
            logger()->error('OAuth account unlinking failed', [
                'provider' => $provider,
                'user_id'  => Auth::id(),
                'error'    => $e->getMessage(),
            ]);

            return redirect()->route('profile.security')
                ->withErrors(['oauth' => 'Failed to unlink ' . ucfirst($provider) . ' account. Please try again.']);
        }
    }

    /**
     * Check if OAuth provider is supported
     */
    protected function isProviderSupported(string $provider): bool
    {
        $supportedProviders = array_keys($this->oauthUserService->getSupportedProviders());

        return in_array($provider, $supportedProviders, TRUE);
    }

    /**
     * Get redirect URL after successful login based on user role
     *
     * @param mixed $user
     */
    protected function getPostLoginRedirectUrl($user): string
    {
        // Check if user is admin
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        // Check if user is agent
        if ($user->isAgent()) {
            return route('dashboard.agent');
        }

        // Check if user is customer
        if ($user->isCustomer()) {
            return route('dashboard.customer');
        }

        // Default fallback
        return route('dashboard');
    }
}
