<?php declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorSetupController extends Controller
{
    public function __construct(
        protected TwoFactorAuthService $twoFactorService,
    ) {
    }

    /**
     * Show the 2FA setup page for new registrations
     */
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // If 2FA is already enabled, redirect to verification notice
        if ($user->two_factor_enabled) {
            return redirect()->route('verification.notice')
                ->with('status', 'Two-factor authentication is already enabled.');
        }

        // Generate a temporary secret key for setup
        $secretKey = $this->twoFactorService->generateSecretKey();

        // Store temporarily in session for verification
        session(['2fa_temp_secret' => $secretKey]);

        $qrCodeSvg = $this->twoFactorService->getQRCodeSvg($user, $secretKey);

        return view('auth.twofactor-setup', compact('qrCodeSvg', 'secretKey'));
    }

    /**
     * Enable 2FA with verification code
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();
        $secretKey = session('2fa_temp_secret');

        if (! $secretKey) {
            throw ValidationException::withMessages([
                'code' => 'Session expired. Please refresh the page and try again.',
            ]);
        }

        $success = $this->twoFactorService->enableTwoFactor(
            $user,
            $secretKey,
            $request->input('code'),
        );

        if (! $success) {
            throw ValidationException::withMessages([
                'code' => 'Invalid verification code. Please try again.',
            ]);
        }

        // Clear the temporary secret
        session()->forget('2fa_temp_secret');

        // Update the two_factor_enabled_at timestamp
        $user->update(['two_factor_enabled_at' => now()]);

        return redirect()->route('verification.notice')
            ->with('status', 'Two-factor authentication has been enabled successfully!');
    }

    /**
     * Skip 2FA setup and continue to email verification
     */
    public function skip(Request $request): RedirectResponse
    {
        // Clear any temporary session data
        session()->forget('2fa_temp_secret');

        return redirect()->route('verification.notice')
            ->with('info', 'Two-factor authentication setup skipped. You can enable it later in your profile settings.');
    }
}
