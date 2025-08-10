<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\TwoFactorAuthService;
use App\Services\SecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected $twoFactorService;
    protected $securityService;

    public function __construct(TwoFactorAuthService $twoFactorService, SecurityService $securityService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->securityService = $securityService;
    }

    /**
     * Display the user's profile view.
     */
    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account - redirect to new deletion protection system
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Redirect to the new account deletion protection system
        return redirect()->route('account.deletion.warning');
    }

    /**
     * Display comprehensive security settings
     */
    public function security(Request $request): View
    {
        $user = $request->user();
        $twoFactorEnabled = $this->twoFactorService->isEnabled($user);
        $remainingRecoveryCodes = $this->twoFactorService->getRemainingRecoveryCodesCount($user);
        
        // Get comprehensive security data
        $loginStatistics = $this->securityService->getLoginStatistics($user);
        $recentLoginHistory = $this->securityService->getRecentLoginHistory($user, 15);
        $activeSessions = $this->securityService->getActiveSessions($user);
        $securityCheckup = $this->securityService->performSecurityCheckup($user);
        
        // Generate new QR code if setting up 2FA
        $qrCodeSvg = null;
        $setupSecret = Session::get('2fa_setup_secret');
        if ($setupSecret) {
            $qrCodeSvg = $this->twoFactorService->getQRCodeSvg($user, $setupSecret);
        }

        return view('profile.security', [
            'user' => $user,
            'twoFactorEnabled' => $twoFactorEnabled,
            'remainingRecoveryCodes' => $remainingRecoveryCodes,
            'qrCodeSvg' => $qrCodeSvg,
            'setupSecret' => $setupSecret,
            'loginStatistics' => $loginStatistics,
            'recentLoginHistory' => $recentLoginHistory,
            'activeSessions' => $activeSessions,
            'securityCheckup' => $securityCheckup,
            'trustedDevices' => $user->trusted_devices ?? [],
        ]);
    }

    /**
     * Download backup codes as a text file
     */
    public function downloadBackupCodes(Request $request)
    {
        $user = $request->user();
        
        if (!$this->twoFactorService->isEnabled($user)) {
            return back()->withErrors(['error' => 'Two-factor authentication is not enabled.']);
        }

        $recoveryCodes = $this->twoFactorService->getRecoveryCodes($user);
        
        if (empty($recoveryCodes)) {
            return back()->withErrors(['error' => 'No backup codes available.']);
        }

        $content = "HD Tickets - Two-Factor Authentication Backup Codes\n";
        $content .= "Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "Account: {$user->email}\n\n";
        $content .= "IMPORTANT: Keep these codes safe and secure!\n";
        $content .= "Each code can only be used once.\n\n";
        $content .= "Backup Codes:\n";
        $content .= "=============\n";
        
        foreach ($recoveryCodes as $index => $code) {
            $content .= ($index + 1) . ". {$code}\n";
        }
        
        $content .= "\n" . str_repeat('=', 50) . "\n";
        $content .= "Store these codes in a safe place.\n";
        $content .= "If you lose access to your authenticator app,\n";
        $content .= "you can use these codes to regain access.\n";

        $filename = 'hd-tickets-backup-codes-' . now()->format('Y-m-d') . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Trust current device
     */
    public function trustDevice(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->securityService->trustDevice($user, $request);
        
        return back()->with('success', 'Device has been marked as trusted.');
    }

    /**
     * Remove trusted device
     */
    public function removeTrustedDevice(Request $request, int $deviceIndex): RedirectResponse
    {
        $user = $request->user();
        
        if ($this->securityService->untrustDevice($user, $deviceIndex)) {
            return back()->with('success', 'Trusted device has been removed.');
        }
        
        return back()->withErrors(['error' => 'Device not found.']);
    }

    /**
     * Revoke session
     */
    public function revokeSession(Request $request, string $sessionId): RedirectResponse
    {
        if ($this->securityService->revokeSession($sessionId)) {
            return back()->with('success', 'Session has been revoked.');
        }
        
        return back()->withErrors(['error' => 'Session not found.']);
    }

    /**
     * Revoke all other sessions
     */
    public function revokeAllOtherSessions(Request $request): RedirectResponse
    {
        $user = $request->user();
        $currentSessionId = Session::getId();
        
        $revokedCount = $this->securityService->revokeAllOtherSessions($user, $currentSessionId);
        
        if ($revokedCount > 0) {
            return back()->with('success', "Revoked {$revokedCount} other sessions.");
        }
        
        return back()->with('info', 'No other sessions to revoke.');
    }
}
