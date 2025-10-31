<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\PublicRegistrationRequest;
use App\Models\User;
use App\Services\PhoneVerificationService;
use App\Services\TwoFactorAuthService;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class PublicRegistrationController extends Controller
{
    public function __construct(
        private PhoneVerificationService $phoneService,
        private TwoFactorAuthService $twoFactorService,
    ) {
    }

    /**
     * Show the public registration form
     */
    public function create(): View
    {
        return view('auth.public-register');
    }

    /**
     * Handle public customer registration
     */
    public function store(PublicRegistrationRequest $request): RedirectResponse
    {
        // Check honeypot field - if filled, it's likely a bot
        if ($request->filled('website_url')) {
            // Silently fail - don't give bots feedback
            return back()->withInput()->withErrors([
                'email' => 'Registration failed. Please try again.',
            ]);
        }

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Create the user with the new registration fields
            $user = User::create([
                'name'                => $validated['first_name'] . ' ' . $validated['last_name'],
                'first_name'          => $validated['first_name'],
                'last_name'           => $validated['last_name'],
                'email'               => $validated['email'],
                'phone'               => $validated['phone'] ?? NULL,
                'password'            => Hash::make($validated['password']),
                'role'                => User::ROLE_CUSTOMER,
                'is_active'           => TRUE,
                'registration_source' => 'public_web',
                'password_changed_at' => now(),
                'terms_accepted_at'   => now(),
                'privacy_accepted_at' => now(),
                'marketing_opt_in'    => $request->boolean('marketing_opt_in', FALSE),
            ]);

            DB::commit();

            // Fire registered event (triggers email verification)
            event(new Registered($user));

            // Log the user in
            Auth::login($user);

            // Send email verification notification
            $user->sendEmailVerificationNotification();

            // Check if 2FA setup should be prompted
            if ($request->boolean('enable_2fa', FALSE) || config('auth.registration.two_factor_prompt', FALSE)) {
                return redirect()->route('register.twofactor.show');
            }

            // Redirect to email verification notice
            return redirect()->route('verification.notice')
                ->with('status', 'Registration successful! Please check your email to verify your account.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Registration failed. Please try again.'])
                ->withInput($request->except('password', 'password_confirmation'));
        }
    }

    /**
     * Show phone verification form
     */
    public function showPhoneVerification(): View
    {
        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        return view('auth.verify-phone', ['user' => $user]);
    }

    /**
     * Verify phone number
     */
    public function verifyPhone(Request $request): RedirectResponse
    {
        $request->validate([
            'verification_code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        if ($this->phoneService->verifyCode($user, $request->verification_code)) {
            $user->update(['phone_verified_at' => now()]);

            return redirect()->route('dashboard')
                ->with('success', 'Phone number verified successfully!');
        }

        return back()
            ->withErrors(['verification_code' => 'Invalid verification code.']);
    }

    /**
     * Resend phone verification code
     */
    public function resendPhoneVerification(): RedirectResponse
    {
        $user = Auth::user();

        if (!$user || !$user->phone) {
            return redirect()->route('dashboard');
        }

        try {
            $this->phoneService->sendVerificationCode($user);

            return back()
                ->with('success', 'Verification code sent!');
        } catch (Exception) {
            return back()
                ->withErrors(['error' => 'Failed to send verification code.']);
        }
    }

    /**
     * Show 2FA setup page
     */
    public function showTwoFactorSetup(): View
    {
        $user = Auth::user();

        if (!$user || !$user->two_factor_secret || $user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        $qrCodeUrl = $this->twoFactorService->generateQrCodeUrl($user);

        return view('auth.setup-2fa', ['user' => $user, 'qrCodeUrl' => $qrCodeUrl]);
    }

    /**
     * Confirm and enable 2FA
     */
    public function confirmTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = Auth::user();

        if (!$user || !$user->two_factor_secret || $user->two_factor_enabled) {
            return redirect()->route('dashboard');
        }

        if ($this->twoFactorService->verifyCode($user, $request->code)) {
            $user->update([
                'two_factor_enabled'      => TRUE,
                'two_factor_confirmed_at' => now(),
            ]);

            return redirect()->route('dashboard')
                ->with('success', 'Two-factor authentication enabled successfully!');
        }

        return back()
            ->withErrors(['code' => 'Invalid authentication code.']);
    }

    /**
     * Set up two-factor authentication for the user
     */
    private function setupTwoFactorAuth(User $user): void
    {
        $google2fa = new Google2FA();
        $secretKey = $google2fa->generateSecretKey();

        $user->update([
            'two_factor_secret'  => $secretKey,
            'two_factor_enabled' => FALSE, // Will be enabled after confirmation
        ]);
    }
}
