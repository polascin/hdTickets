<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\SecurityService;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ProfileController extends Controller
{
    protected TwoFactorAuthService $twoFactorService;

    protected SecurityService $securityService;

    public function __construct(TwoFactorAuthService $twoFactorService, SecurityService $securityService)
    {
        $this->twoFactorService = $twoFactorService;
        $this->securityService = $securityService;
    }

    /**
     * Display the user's profile view with enhanced data.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Get profile completion data
        $profileCompletion = $user->getProfileCompletion();

        // Calculate enhanced user statistics
        $userStats = [
            'joined_days_ago'      => $user->created_at->diffInDays(now()),
            'login_count'          => $user->login_count ?? 0,
            'last_login_display'   => $user->last_login_at ? $user->last_login_at->diffForHumans(null, true) : 'Never',
            'last_login_formatted' => $user->last_login_at ? $user->last_login_at->format('M j, Y \a\t g:i A') : null,
            
            // Sports Events Monitoring Statistics
            'monitored_events' => $user->ticketAlerts()->where('status', 'active')->count(),
            'total_alerts'     => $user->ticketAlerts()->count(),
            'recent_purchases' => 0, // Placeholder for purchase history when implemented
            'active_searches'  => $user->ticketAlerts()->where('status', 'active')->where('created_at', '>=', now()->subMonth())->count(),
            
            // Activity statistics
            'profile_views'       => $user->profile_views ?? 0,
            'account_age_months'  => $user->created_at->diffInMonths(now()),
            'last_activity'       => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never active',
        ];

        // Enhanced security and verification status
        $securityStatus = [
            'email_verified'       => (bool) $user->email_verified_at,
            'two_factor_enabled'   => (bool) $user->two_factor_secret,
            'profile_complete'     => $profileCompletion['percentage'] >= 100,
            'password_age_days'    => $user->password_changed_at ? 
                                     $user->password_changed_at->diffInDays(now()) : 
                                     $user->created_at->diffInDays(now()),
            'trusted_devices_count' => count($user->trusted_devices ?? []),
            'active_sessions_count' => 1, // Default to current session
        ];

        // Recent activity data
        $recentActivity = [
            'last_login' => $user->last_login_at,
            'login_count' => $user->login_count ?? 0,
            'recent_ips' => [$user->last_login_ip],
            'account_changes' => $user->updated_at->diffForHumans(),
        ];

        // Profile insights and recommendations
        $profileInsights = [
            'completion_status' => $profileCompletion['status'],
            'missing_critical_fields' => array_intersect($profileCompletion['missing_fields'], ['phone', 'two_factor_enabled']),
            'security_score' => $this->calculateSecurityScore($user, $securityStatus),
            'recommendations' => $this->generateProfileRecommendations($user, $profileCompletion, $securityStatus),
        ];

        return view('profile.show', compact(
            'user',
            'profileCompletion',
            'userStats',
            'securityStatus',
            'recentActivity',
            'profileInsights'
        ));
    }

    /**
     * Display the user's profile form.
     */
    /**
     * Edit
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
    /**
     * Update
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Fill the user with validated data
        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = NULL;
        }

        $user->save();

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => TRUE,
                'message' => 'Profile updated successfully!',
                'user'    => [
                    'name'            => $user->name,
                    'surname'         => $user->surname,
                    'username'        => $user->username,
                    'email'           => $user->email,
                    'phone'           => $user->phone,
                    'bio'             => $user->bio,
                    'timezone'        => $user->timezone,
                    'language'        => $user->language,
                    'full_name'       => $user->getFullNameAttribute(),
                    'profile_display' => $user->getProfileDisplay(),
                ],
            ]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        try {
            $user = $request->user();

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($user->profile_photo_path) {
                    \Storage::disk('public')->delete($user->profile_photo_path);
                }

                // Store new photo
                $path = $request->file('photo')->store('profile-photos', 'public');

                // Update user profile
                $user->update([
                    'profile_photo_path' => $path,
                ]);

                return response()->json([
                    'success'   => TRUE,
                    'message'   => 'Profile photo updated successfully!',
                    'photo_url' => $user->profile_photo_url,
                ]);
            }

            return response()->json([
                'success' => FALSE,
                'message' => 'No photo uploaded',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to upload photo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete the user's account - redirect to new deletion protection system
     */
    /**
     * Destroy
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Redirect to the new account deletion protection system
        return redirect()->route('account.deletion.warning');
    }

    /**
     * Display comprehensive security settings
     */
    /**
     * Security
     */
    public function security(Request $request): \Illuminate\Contracts\View\View
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
        $qrCodeSvg = NULL;
        $setupSecret = Session::get('2fa_setup_secret');
        if ($setupSecret) {
            $qrCodeSvg = $this->twoFactorService->getQRCodeSvg($user, $setupSecret);
        }

        return view('profile.security', [
            'user'                   => $user,
            'twoFactorEnabled'       => $twoFactorEnabled,
            'remainingRecoveryCodes' => $remainingRecoveryCodes,
            'qrCodeSvg'              => $qrCodeSvg,
            'setupSecret'            => $setupSecret,
            'loginStatistics'        => $loginStatistics,
            'recentLoginHistory'     => $recentLoginHistory,
            'activeSessions'         => $activeSessions,
            'securityCheckup'        => $securityCheckup,
            'trustedDevices'         => $user->trusted_devices ?? [],
        ]);
    }

    /**
     * Download backup codes as a text file
     */
    /**
     * DownloadBackupCodes
     */
    public function downloadBackupCodes(Request $request): RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
        $content .= 'Generated on: ' . now()->format('Y-m-d H:i:s') . "\n";
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
    /**
     * TrustDevice
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
    /**
     * RemoveTrustedDevice
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
    /**
     * RevokeSession
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
    /**
     * RevokeAllOtherSessions
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

    /**
     * Calculate user security score based on various factors
     */
    private function calculateSecurityScore($user, $securityStatus): int
    {
        $score = 0;
        $maxScore = 100;

        // Email verification (20 points)
        if ($securityStatus['email_verified']) {
            $score += 20;
        }

        // Two-factor authentication (30 points)
        if ($securityStatus['two_factor_enabled']) {
            $score += 30;
        }

        // Profile completion (20 points)
        if ($securityStatus['profile_complete']) {
            $score += 20;
        }

        // Password age (15 points - newer passwords get more points)
        $passwordAgeDays = $securityStatus['password_age_days'];
        if ($passwordAgeDays <= 90) {
            $score += 15;
        } elseif ($passwordAgeDays <= 180) {
            $score += 10;
        } elseif ($passwordAgeDays <= 365) {
            $score += 5;
        }

        // Recent activity (10 points)
        if ($user->last_login_at && $user->last_login_at->isAfter(now()->subDays(7))) {
            $score += 10;
        }

        // Phone number provided (5 points)
        if (!empty($user->phone)) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Generate profile recommendations based on user data
     */
    private function generateProfileRecommendations($user, $profileCompletion, $securityStatus): array
    {
        $recommendations = [];

        // Profile completion recommendations
        if ($profileCompletion['percentage'] < 90) {
            $recommendations[] = [
                'type' => 'profile',
                'priority' => 'high',
                'title' => 'Complete Your Profile',
                'description' => 'Add missing information to unlock all features.',
                'action' => 'Complete Profile',
                'route' => 'profile.edit',
                'icon' => 'user-circle',
            ];
        }

        // Security recommendations
        if (!$securityStatus['email_verified']) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'high',
                'title' => 'Verify Your Email',
                'description' => 'Verify your email address to secure your account.',
                'action' => 'Verify Email',
                'route' => null,
                'icon' => 'mail',
            ];
        }

        if (!$securityStatus['two_factor_enabled']) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'medium',
                'title' => 'Enable Two-Factor Authentication',
                'description' => 'Add an extra layer of security to your account.',
                'action' => 'Enable 2FA',
                'route' => 'profile.security',
                'icon' => 'shield-check',
            ];
        }

        // Password age recommendation
        if ($securityStatus['password_age_days'] > 180) {
            $recommendations[] = [
                'type' => 'security',
                'priority' => 'medium',
                'title' => 'Update Your Password',
                'description' => 'Consider updating your password for better security.',
                'action' => 'Change Password',
                'route' => 'profile.security',
                'icon' => 'key',
            ];
        }

        return $recommendations;
    }
}