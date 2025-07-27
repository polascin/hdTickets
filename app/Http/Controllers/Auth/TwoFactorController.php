<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    protected $twoFactorService;

    public function __construct(TwoFactorAuthService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show 2FA challenge form during login
     */
    public function challenge(): View
    {
        if (!Session::has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify 2FA code during login
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
            'recovery' => 'sometimes|boolean'
        ]);

        $userId = Session::get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['code' => 'Session expired. Please login again.']);
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['code' => 'Invalid session.']);
        }

        $isValid = false;

        if ($request->boolean('recovery')) {
            // Verify recovery code
            $isValid = $this->twoFactorService->verifyRecoveryCode($user, $request->code);
            if (!$isValid) {
                $isValid = $this->twoFactorService->verifyAdminBackupCode($user, $request->code);
            }
        } else {
            // Check if it's a regular TOTP code
            $secret = $this->twoFactorService->getSecret($user);
            if ($secret) {
                $isValid = $this->twoFactorService->verifyCode($secret, $request->code);
            }

            // If TOTP fails, try SMS code
            if (!$isValid) {
                $isValid = $this->twoFactorService->verifySmsCode($user, $request->code);
            }

            // If SMS fails, try email code
            if (!$isValid) {
                $isValid = $this->twoFactorService->verifyEmailCode($user, $request->code);
            }
        }

        if (!$isValid) {
            // Track failed attempts
            $user->increment('failed_login_attempts');
            
            // Lock account after 5 failed attempts
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
                Session::forget('2fa_user_id');
                
                activity('account_locked')
                    ->performedOn($user)
                    ->causedBy($user)
                    ->withProperties([
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'reason' => 'failed_2fa_attempts'
                    ])
                    ->log('Account locked due to failed 2FA attempts');
                    
                return redirect()->route('login')->withErrors(['code' => 'Account locked due to multiple failed attempts. Try again in 15 minutes.']);
            }

            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        // Reset failed attempts on successful verification
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent()
        ]);
        $user->increment('login_count');

        // Complete the login process
        Auth::login($user, Session::get('2fa_remember', false));
        Session::forget(['2fa_user_id', '2fa_remember']);
        
        // Log successful 2FA verification
        activity('two_factor_verified')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->boolean('recovery') ? 'recovery_code' : 'authenticator'
            ])
            ->log('Two-factor authentication verified');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show 2FA setup form
     */
    public function setup(): View
    {
        $user = Auth::user();
        
        // Check if 2FA is already enabled
        if ($this->twoFactorService->isEnabled($user)) {
            return redirect()->route('profile.security')->with('info', 'Two-factor authentication is already enabled.');
        }

        // Generate a new secret for setup
        $secret = $this->twoFactorService->generateSecretKey();
        $qrCodeSvg = $this->twoFactorService->getQRCodeSvg($user, $secret);
        
        Session::put('2fa_setup_secret', $secret);

        return view('auth.two-factor-setup', [
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
            'user' => $user
        ]);
    }

    /**
     * Enable 2FA after verification
     */
    public function enable(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        $secret = Session::get('2fa_setup_secret');

        if (!$secret) {
            return redirect()->route('2fa.setup')->withErrors(['code' => 'Setup session expired. Please start over.']);
        }

        if (!$this->twoFactorService->enableTwoFactor($user, $secret, $request->code)) {
            return back()->withErrors(['code' => 'Invalid verification code. Please try again.']);
        }

        Session::forget('2fa_setup_secret');

        // Show recovery codes
        $recoveryCodes = $this->twoFactorService->getRecoveryCodes($user);

        return redirect()->route('2fa.recovery-codes')->with([
            'recovery_codes' => $recoveryCodes,
            'success' => 'Two-factor authentication has been enabled successfully!'
        ]);
    }

    /**
     * Show recovery codes
     */
    public function recoveryCodes(): View
    {
        $user = Auth::user();
        
        if (!$this->twoFactorService->isEnabled($user)) {
            return redirect()->route('profile.security');
        }

        $recoveryCodes = Session::get('recovery_codes') ?: $this->twoFactorService->getRecoveryCodes($user);
        $remainingCount = $this->twoFactorService->getRemainingRecoveryCodesCount($user);

        return view('auth.two-factor-recovery-codes', [
            'recoveryCodes' => $recoveryCodes,
            'remainingCount' => $remainingCount,
            'user' => $user
        ]);
    }

    /**
     * Regenerate recovery codes
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$this->twoFactorService->isEnabled($user)) {
            return redirect()->route('profile.security');
        }

        $newCodes = $this->twoFactorService->regenerateRecoveryCodes($user);

        return redirect()->route('2fa.recovery-codes')->with([
            'recovery_codes' => $newCodes,
            'success' => 'Recovery codes have been regenerated successfully!'
        ]);
    }

    /**
     * Disable 2FA
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password'
        ]);

        $user = Auth::user();
        
        if (!$this->twoFactorService->isEnabled($user)) {
            return redirect()->route('profile.security')->with('info', 'Two-factor authentication is not enabled.');
        }

        $this->twoFactorService->disableTwoFactor($user);

        return redirect()->route('profile.security')->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Send SMS backup code
     */
    public function sendSmsCode(Request $request): RedirectResponse
    {
        $userId = Session::get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user || !$user->phone) {
            return back()->withErrors(['code' => 'SMS backup is not available for this account.']);
        }

        if ($this->twoFactorService->sendSmsCode($user)) {
            return back()->with('success', 'SMS verification code sent to your phone.');
        }

        return back()->withErrors(['code' => 'Failed to send SMS code. Please try again.']);
    }

    /**
     * Send email backup code
     */
    public function sendEmailCode(Request $request): RedirectResponse
    {
        $userId = Session::get('2fa_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return back()->withErrors(['code' => 'Invalid session.']);
        }

        if ($this->twoFactorService->sendEmailCode($user)) {
            return back()->with('success', 'Email verification code sent to your email address.');
        }

        return back()->withErrors(['code' => 'Failed to send email code. Please try again.']);
    }

    /**
     * Admin: Generate backup codes for a user
     */
    public function adminGenerateBackupCodes(Request $request, $userId): RedirectResponse
    {
        $admin = Auth::user();
        if (!$admin->isAdmin()) {
            abort(403, 'Access denied.');
        }

        $user = \App\Models\User::findOrFail($userId);
        
        try {
            $backupCodes = $this->twoFactorService->generateAdminBackupCodes($admin, $user);
            
            return back()->with([
                'admin_backup_codes' => $backupCodes,
                'target_user' => $user,
                'success' => "Emergency backup codes generated for {$user->name}. These codes are valid for 24 hours."
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
