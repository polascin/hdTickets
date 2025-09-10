<?php declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\HoneypotRule;
use App\Services\LoginAnalyticsService;
use App\Services\TwoFactorAuthService;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /**
     * Authorize
     */
    public function authorize(): bool
    {
        return TRUE;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array|\Illuminate\Contracts\Validation\Rule|string>
     */
    /**
     * Rules
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'website'  => [new HoneypotRule('website')], // Honeypot field
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    /**
     * Authenticate
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $user = User::where('email', $this->string('email'))->first();

        // Check if user exists and credentials are correct
        if (! $user || ! Hash::check($this->string('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            // Track analytics for failed login
            app(LoginAnalyticsService::class)->trackLoginAttempt(
                $this->string('email')->toString(),
                FALSE,
                $this->ip(),
                $this->userAgent(),
                ['error_type' => 'authentication_failed'],
            );

            // Track failed login attempts
            if ($user) {
                $user->increment('failed_login_attempts');

                // Lock account after 5 failed attempts
                if ($user->failed_login_attempts >= 5) {
                    $user->update(['locked_until' => now()->addMinutes(15)]);

                    // Track security event
                    app(LoginAnalyticsService::class)->trackSecurityEvent(
                        'account_locked',
                        $this->ip(),
                        $this->userAgent(),
                        ['email' => $this->string('email')->toString(), 'attempts' => $user->failed_login_attempts],
                    );

                    activity('account_locked')
                        ->performedOn($user)
                        ->withProperties([
                            'ip_address' => $this->ip(),
                            'user_agent' => $this->userAgent(),
                            'reason'     => 'failed_login_attempts',
                        ])
                        ->log('Account locked due to failed login attempts');
                }
            }

            // Generic error message to prevent user enumeration with helpful suggestions
            $suggestions = [
                'Double-check your email address for typos',
                'Make sure your password is entered correctly',
                'Try using the "Forgot Password?" link if you\'re having trouble',
                'Contact support if you continue having issues',
            ];

            throw ValidationException::withMessages([
                'email'             => 'Invalid login credentials. Please check your email and password.',
                'login_suggestions' => $suggestions,
                'error_type'        => 'authentication_failed',
            ]);
        }

        // Check if account is locked
        if ($user->locked_until && $user->locked_until->isFuture()) {
            // Track analytics for locked account attempt
            app(LoginAnalyticsService::class)->trackLoginAttempt(
                $this->string('email')->toString(),
                FALSE,
                $this->ip(),
                $this->userAgent(),
                ['error_type' => 'account_locked'],
            );

            $remainingTime = $user->locked_until->diffForHumans();
            $suggestions = [
                'Wait for the lockout period to expire',
                'Use the "Forgot Password?" link to reset your password',
                'Contact support if this was not you',
                'Review our security guidelines',
            ];

            throw ValidationException::withMessages([
                'email'               => "Your account is temporarily locked until {$remainingTime}. This is for security reasons after multiple failed login attempts.",
                'lockout_suggestions' => $suggestions,
                'error_type'          => 'account_locked',
                'locked_until'        => $user->locked_until->toISOString(),
            ]);
        }

        // Check if account is active
        if (! $user->is_active) {
            $suggestions = [
                'Contact your system administrator',
                'Email support at support@hdtickets.com',
                'Check if your subscription is current',
                'Review the terms of service',
            ];

            throw ValidationException::withMessages([
                'email'                   => 'Your account has been deactivated. Please contact support for assistance.',
                'deactivated_suggestions' => $suggestions,
                'error_type'              => 'account_deactivated',
            ]);
        }

        // Check if user can access the system (scrapers cannot)
        if (! $user->canAccessSystem()) {
            $suggestions = [
                'Use the API endpoints for scraper accounts',
                'Contact your administrator about account type',
                'Check the API documentation',
                'Verify you\'re using the correct login portal',
            ];

            throw ValidationException::withMessages([
                'email'              => 'This account type cannot access the web interface. Scraper accounts should use the API.',
                'access_suggestions' => $suggestions,
                'error_type'         => 'invalid_account_type',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        // Check if 2FA is enabled
        $twoFactorService = app(TwoFactorAuthService::class);
        if ($twoFactorService->isEnabled($user)) {
            // Store user ID and remember flag in session for 2FA verification
            Session::put('2fa_user_id', $user->id);
            Session::put('2fa_remember', $this->boolean('remember'));

            // Don't actually log in yet - redirect to 2FA challenge
            return;
        }

        // Standard login without 2FA
        Auth::login($user, $this->boolean('remember'));

        // Track successful login analytics
        app(LoginAnalyticsService::class)->trackLoginAttempt(
            $this->string('email')->toString(),
            TRUE,
            $this->ip(),
            $this->userAgent(),
            [
                'user_role'    => $user->role,
                'login_method' => '2fa_disabled',
                'remember_me'  => $this->boolean('remember'),
            ],
        );

        // Reset failed attempts and update login info
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => NULL,
            'last_login_at'         => now(),
            'last_login_ip'         => $this->ip(),
            'last_login_user_agent' => $this->userAgent(),
        ]);
        $user->increment('login_count');

        // Log successful login
        activity('user_login')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address'         => $this->ip(),
                'user_agent'         => $this->userAgent(),
                'two_factor_enabled' => FALSE,
            ])
            ->log('User logged in successfully');
    }

    /**
     * Check if 2FA is required for this login attempt
     */
    /**
     * Requires2FA
     */
    public function requires2FA(): bool
    {
        return Session::has('2fa_user_id');
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    /**
     * EnsureIsNotRateLimited
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());
        $minutes = ceil($seconds / 60);

        // Enhanced rate limit message with security context
        $message = 'Too many login attempts. For security reasons, this account is temporarily locked.';
        $message .= " Please try again in {$minutes} minute(s) ({$seconds} seconds).";
        $message .= ' If you continue to have issues, please contact support.';

        throw ValidationException::withMessages([
            'email'                 => $message,
            'rate_limit_seconds'    => $seconds, // For JS countdown timer
            'rate_limit_expires_at' => now()->addSeconds($seconds)->toISOString(),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    /**
     * ThrottleKey
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()) . '|' . $this->ip());
    }
}
