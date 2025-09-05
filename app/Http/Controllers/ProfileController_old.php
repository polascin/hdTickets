<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\LoginHistory;
use App\Models\TicketAlert;
use App\Models\UserSession;
use App\Services\SecurityService;
use App\Services\TwoFactorAuthService;
use Cache;
use DateTimeZone;
use Exception;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InterventionImage;
use Response;

use function count;
use function in_array;

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
            'last_login_display'   => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
            'last_login_formatted' => $user->last_login_at ? $user->last_login_at->format('M j, Y \a\t g:i A') : NULL,

            // Sports Events Monitoring Statistics
            'monitored_events' => $user->ticketAlerts()->where('status', 'active')->count(),
            'total_alerts'     => $user->ticketAlerts()->count(),
            'recent_purchases' => 0, // Placeholder for purchase history when implemented
            'active_searches'  => $user->ticketAlerts()->where('status', 'active')->where('created_at', '>=', now()->subMonth())->count(),

            // Activity statistics
            'profile_views'      => $user->profile_views ?? 0,
            'account_age_months' => $user->created_at->diffInMonths(now()),
            'last_activity'      => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never active',
        ];

        // Enhanced security and verification status
        $securityStatus = [
            'email_verified'     => (bool) $user->email_verified_at,
            'two_factor_enabled' => (bool) $user->two_factor_secret,
            'profile_complete'   => $profileCompletion['percentage'] >= 100,
            'password_age_days'  => $user->password_changed_at ?
                                     $user->password_changed_at->diffInDays(now()) :
                                     $user->created_at->diffInDays(now()),
            'trusted_devices_count' => count($user->trusted_devices ?? []),
            'active_sessions_count' => 1, // Default to current session
        ];

        // Recent activity data
        $recentActivity = [
            'last_login'      => $user->last_login_at,
            'login_count'     => $user->login_count ?? 0,
            'recent_ips'      => [$user->last_login_ip],
            'account_changes' => $user->updated_at->diffForHumans(),
        ];

        // Profile insights and recommendations
        $profileInsights = [
            'completion_status'       => $profileCompletion['status'],
            'missing_critical_fields' => array_intersect($profileCompletion['missing_fields'], ['phone', 'two_factor_enabled']),
            'security_score'          => $this->calculateSecurityScore($user, $securityStatus),
            'recommendations'         => $this->generateProfileRecommendations($user, $profileCompletion, $securityStatus),
        ];

        return view('profile.show', compact(
            'user',
            'profileCompletion',
            'userStats',
            'securityStatus',
            'recentActivity',
            'profileInsights',
        ));
    }

    /**
     * Get current user statistics for AJAX updates with enhanced performance and caching
     */
    public function stats(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();

            // Cache key for user statistics
            $cacheKey = "user_stats_{$user->id}";

            $userStats = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
                return [
                    'monitored_events' => $user->ticketAlerts()->where('status', 'active')->count(),
                    'total_alerts'     => $user->ticketAlerts()->count(),
                    'active_searches'  => $user->ticketAlerts()
                        ->where('status', 'active')
                        ->where('created_at', '>=', now()->subMonth())
                        ->count(),
                    'recent_purchases'   => 0, // Placeholder for purchase history when implemented
                    'login_count'        => $user->login_count ?? 0,
                    'last_login_display' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
                    'profile_completion' => $user->getProfileCompletion()['percentage'],
                    'security_score'     => $this->calculateSecurityScore($user),
                    'account_age_days'   => $user->created_at->diffInDays(now()),
                ];
            });

            return response()->json([
                'success'    => TRUE,
                'stats'      => $userStats,
                'updated_at' => now()->toISOString(),
                'cached'     => TRUE,
            ]);
        } catch (Exception $e) {
            Log::error('Profile stats error: ' . $e->getMessage(), [
                'user_id' => $request->user()?->id,
                'error'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load statistics. Please try again.',
                'error'   => app()->isProduction() ? 'Server error' : $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        try {
            $user = $request->user();
            $profileCompletion = $user->getProfileCompletion();

            // Available timezones for selection
            $timezones = collect(DateTimeZone::listIdentifiers())
                ->mapWithKeys(function ($timezone) {
                    return [$timezone => $timezone];
                })
                ->toArray();

            // Available languages
            $languages = [
                'en' => 'English',
                'es' => 'Spanish',
                'fr' => 'French',
                'de' => 'German',
                'it' => 'Italian',
                'pt' => 'Portuguese',
                'nl' => 'Dutch',
                'pl' => 'Polish',
                'cs' => 'Czech',
                'sk' => 'Slovak',
            ];

            return view('profile.edit', compact(
                'user',
                'profileCompletion',
                'timezones',
                'languages',
            ));
        } catch (Exception $e) {
            Log::error('Profile edit page error: ' . $e->getMessage());

            return redirect()->route('profile.show')
                ->with('error', 'Unable to load profile edit page. Please try again.');
        }
    }

    /**
     * Show profile analytics dashboard
     */
    public function analytics(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            $user = $request->user();

            $analyticsService = new \App\Services\ProfileAnalyticsService();
            $analytics = $analyticsService->getAnalytics($user);

            return view('profile.analytics', compact('user', 'analytics'));
        } catch (Exception $e) {
            Log::error('Profile analytics error: ' . $e->getMessage());

            return redirect()->route('profile.show')
                ->with('error', 'Unable to load analytics. Please try again.');
        }
    }

    /**
     * Show advanced security dashboard
     */
    public function advancedSecurity(Request $request): \Illuminate\Contracts\View\View
    {
        try {
            $user = $request->user();

            $securityService = new \App\Services\AdvancedSecurityService();
            $securityData = $securityService->getSecurityDashboard($user);

            return view('profile.advanced-security', compact('user', 'securityData'));
        } catch (Exception $e) {
            Log::error('Advanced security dashboard error: ' . $e->getMessage());

            return redirect()->route('profile.security')
                ->with('error', 'Unable to load security dashboard. Please try again.');
        }
    }

    /**
     * Get real-time analytics data
     */
    public function getAnalyticsData(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();

            $analyticsService = new \App\Services\ProfileAnalyticsService();
            $analytics = $analyticsService->getAnalytics($user);

            return response()->json([
                'success'   => TRUE,
                'data'      => $analytics,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Analytics API error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to fetch analytics data.',
            ], 500);
        }
    }

    public function updatePreferences(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'theme'               => 'nullable|in:light,dark,auto',
                'notifications_email' => 'boolean',
                'notifications_push'  => 'boolean',
                'notifications_sms'   => 'boolean',
                'language'            => 'nullable|string|max:5',
                'timezone'            => 'nullable|string|max:100',
                'currency'            => 'nullable|string|max:3',
                'date_format'         => 'nullable|in:Y-m-d,m/d/Y,d/m/Y,d.m.Y',
                'time_format'         => 'nullable|in:24,12',
            ]);

            $user = $request->user();

            // Update preferences in user profile
            $preferences = array_merge($user->preferences ?? [], $validated);

            $user->update([
                'preferences' => $preferences,
                'language'    => $validated['language'] ?? $user->language,
                'timezone'    => $validated['timezone'] ?? $user->timezone,
            ]);

            // Clear user stats cache to reflect changes
            Cache::forget("user_stats_{$user->id}");

            return response()->json([
                'success'     => TRUE,
                'message'     => 'Preferences updated successfully!',
                'preferences' => $preferences,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Profile preferences update error: ' . $e->getMessage(), [
                'user_id'      => $request->user()?->id,
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to update preferences. Please try again.',
            ], 500);
        }
    }

    /**
     * Update the user's profile information.
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
                    Storage::disk('public')->delete($user->profile_photo_path);
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
        } catch (Exception $e) {
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

        if (! $this->twoFactorService->isEnabled($user)) {
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
     *
     * @param mixed $user
     * @param mixed $securityStatus
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
        if (! empty($user->phone)) {
            $score += 5;
        }

        return min($score, $maxScore);
    }

    /**
     * Generate profile recommendations based on user data
     *
     * @param mixed $user
     * @param mixed $profileCompletion
     * @param mixed $securityStatus
     */
    private function generateProfileRecommendations($user, $profileCompletion, $securityStatus): array
    {
        $recommendations = [];

        // Profile completion recommendations
        if ($profileCompletion['percentage'] < 90) {
            $recommendations[] = [
                'type'        => 'profile',
                'priority'    => 'high',
                'title'       => 'Complete Your Profile',
                'description' => 'Add missing information to unlock all features.',
                'action'      => 'Complete Profile',
                'route'       => 'profile.edit',
                'icon'        => 'user-circle',
            ];
        }

        // Security recommendations
        if (! $securityStatus['email_verified']) {
            $recommendations[] = [
                'type'        => 'security',
                'priority'    => 'high',
                'title'       => 'Verify Your Email',
                'description' => 'Verify your email address to secure your account.',
                'action'      => 'Verify Email',
                'route'       => NULL,
                'icon'        => 'mail',
            ];
        }

        if (! $securityStatus['two_factor_enabled']) {
            $recommendations[] = [
                'type'        => 'security',
                'priority'    => 'medium',
                'title'       => 'Enable Two-Factor Authentication',
                'description' => 'Add an extra layer of security to your account.',
                'action'      => 'Enable 2FA',
                'route'       => 'profile.security',
                'icon'        => 'shield-check',
            ];
        }

        // Password age recommendation
        if ($securityStatus['password_age_days'] > 180) {
            $recommendations[] = [
                'type'        => 'security',
                'priority'    => 'medium',
                'title'       => 'Update Your Password',
                'description' => 'Consider updating your password for better security.',
                'action'      => 'Change Password',
                'route'       => 'profile.security',
                'icon'        => 'key',
            ];
        }

        return $recommendations;
    }

    /**
     * Update user profile with enhanced validation
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Update basic profile information
            $user->fill($validated);

            // Handle timezone update
            if ($request->has('timezone')) {
                $user->timezone = $validated['timezone'];
            }

            // Handle language preference
            if ($request->has('language')) {
                $user->language = $validated['language'];
            }

            $user->save();

            // Clear profile cache
            Cache::forget("user_stats_{$user->id}");
            Cache::forget("profile_completion_{$user->id}");

            return Redirect::route('profile.show')
                ->with('success', 'Profile updated successfully!');
        } catch (Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'error' => $e->getTraceAsString(),
            ]);

            return Redirect::route('profile.edit')
                ->with('error', 'Failed to update profile. Please try again.');
        }
    }

    /**
     * Upload and crop profile picture
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240', // 10MB max
                'crop_data' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $photo = $request->file('photo');
            $cropData = $request->input('crop_data') ? json_decode($request->input('crop_data'), true) : null;

            // Delete old profile picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Generate unique filename
            $filename = 'profile_pictures/' . $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            
            // Store original image
            $path = $photo->storeAs('profile_pictures', $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension(), 'public');
            
            // Create thumbnails and apply cropping if needed
            $this->processProfileImage($path, $cropData);
            
            $user->profile_picture = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully!',
                'image_url' => Storage::disk('public')->url($path),
            ]);
        } catch (Exception $e) {
            Log::error('Profile picture upload error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture. Please try again.',
            ], 500);
        }
    }

    /**
     * Get user activity dashboard data
     */
    public function getActivityData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $period = $request->get('period', '30'); // days
            
            $startDate = now()->subDays((int)$period);
            
            // Login history
            $loginHistory = LoginHistory::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
            
            // Active sessions
            $activeSessions = UserSession::where('user_id', $user->id)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->orderBy('last_activity', 'desc')
                ->get();
            
            // Ticket alerts activity
            $alertsActivity = TicketAlert::where('user_id', $user->id)
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'login_history' => $loginHistory,
                    'active_sessions' => $activeSessions,
                    'alerts_activity' => $alertsActivity,
                    'stats' => [
                        'total_logins' => $loginHistory->count(),
                        'active_sessions_count' => $activeSessions->count(),
                        'alerts_created' => $alertsActivity->sum('count'),
                    ],
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Activity data error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load activity data.',
            ], 500);
        }
    }

    /**
     * Update password with enhanced security
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed|different:current_password',
                'password_confirmation' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            
            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect.',
                ], 422);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
            $user->save();

            // Log password change
            Log::info('Password changed for user', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Password update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password. Please try again.',
            ], 500);
        }
    }

    /**
     * Get security overview data
     */
    public function getSecurityData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Recent login attempts
            $recentLogins = LoginHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Active sessions
            $activeSessions = UserSession::where('user_id', $user->id)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->get();
            
            // Security score calculation
            $securityScore = $this->calculateSecurityScore($user);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'security_score' => $securityScore,
                    'two_factor_enabled' => (bool) $user->two_factor_secret,
                    'email_verified' => (bool) $user->email_verified_at,
                    'recent_logins' => $recentLogins,
                    'active_sessions' => $activeSessions,
                    'password_age_days' => $user->password_changed_at 
                        ? $user->password_changed_at->diffInDays(now()) 
                        : $user->created_at->diffInDays(now()),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Security data error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load security data.',
            ], 500);
        }
    }

    /**
     * Process profile image with cropping and thumbnails
     */
    private function processProfileImage(string $path, ?array $cropData = null): void
    {
        try {
            $fullPath = Storage::disk('public')->path($path);
            
            // If we have crop data, apply it
            if ($cropData && extension_loaded('gd')) {
                $image = imagecreatefromstring(file_get_contents($fullPath));
                
                if ($image) {
                    // Apply crop
                    $croppedImage = imagecrop($image, [
                        'x' => (int) $cropData['x'],
                        'y' => (int) $cropData['y'],
                        'width' => (int) $cropData['width'],
                        'height' => (int) $cropData['height'],
                    ]);
                    
                    if ($croppedImage) {
                        // Resize to standard profile picture size
                        $resized = imagescale($croppedImage, 300, 300);
                        
                        if ($resized) {
                            imagejpeg($resized, $fullPath, 90);
                            imagedestroy($resized);
                        }
                        
                        imagedestroy($croppedImage);
                    }
                    
                    imagedestroy($image);
                }
            }
        } catch (Exception $e) {
            Log::warning('Profile image processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Revoke user session
     */
    public function revokeSession(Request $request, string $sessionId): JsonResponse
    {
        try {
            $user = $request->user();
            
            $session = UserSession::where('user_id', $user->id)
                ->where('id', $sessionId)
                ->first();
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found.',
                ], 404);
            }
            
            $session->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Session revoked successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Session revoke error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke session.',
            ], 500);
        }
    }

    /**
     * Export user data
     */
    public function exportData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $format = $request->get('format', 'json');
            
            // Compile user data
            $userData = [
                'profile' => $user->toArray(),
                'ticket_alerts' => $user->ticketAlerts()->get()->toArray(),
                'login_history' => LoginHistory::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->limit(100)
                    ->get()
                    ->toArray(),
            ];
            
            // Remove sensitive data
            unset($userData['profile']['password']);
            unset($userData['profile']['two_factor_secret']);
            unset($userData['profile']['two_factor_recovery_codes']);
            
            $filename = 'user_data_export_' . $user->id . '_' . now()->format('Y-m-d_H-i-s');
            
            if ($format === 'json') {
                $content = json_encode($userData, JSON_PRETTY_PRINT);
                $filename .= '.json';
                $mimeType = 'application/json';
            } else {
                // CSV format (simplified)
                $content = $this->convertToCSV($userData);
                $filename .= '.csv';
                $mimeType = 'text/csv';
            }
            
            return response()->json([
                'success' => true,
                'download_url' => route('profile.download-export', [
                    'format' => $format,
                    'token' => encrypt([$user->id, time()]),
                ]),
                'filename' => $filename,
            ]);
        } catch (Exception $e) {
            Log::error('Data export error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to export data.',
            ], 500);
        }
    }

    /**
     * Convert array to CSV format
     */
    private function convertToCSV(array $data): string
    {
        $output = "Section,Key,Value\n";
        
        foreach ($data as $section => $items) {
            if (is_array($items)) {
                foreach ($items as $key => $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }
                    $output .= "{$section},{$key},\"" . str_replace('"', '\"\"', (string)$value) . "\"\n";
                }
            }
        }
        
        return $output;
    }
}
