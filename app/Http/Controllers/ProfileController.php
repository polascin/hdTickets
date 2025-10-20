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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Response;

use function count;
use function extension_loaded;
use function is_array;
use function sprintf;

class ProfileController extends Controller
{
    public function __construct(protected TwoFactorAuthService $twoFactorService, protected SecurityService $securityService)
    {
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
            'last_login_formatted' => $user->last_login_at ? $user->last_login_at->format('M j, Y \\a\\t g:i A') : NULL,

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
            'active_sessions_count' => UserSession::where('user_id', $user->id)->active()->count(),
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
            'recommendations'         => $this->generateProfileRecommendations($profileCompletion, $securityStatus),
        ];

        return view('profile.show', ['user' => $user, 'profileCompletion' => $profileCompletion, 'userStats' => $userStats, 'securityStatus' => $securityStatus, 'recentActivity' => $recentActivity, 'profileInsights' => $profileInsights]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $profileCompletion = $user->getProfileCompletion();

        // Available timezones
        $timezones = collect(DateTimeZone::listIdentifiers())
            ->mapWithKeys(fn ($timezone): array => [$timezone => $timezone])
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

        return view('profile.edit', ['user' => $user, 'profileCompletion' => $profileCompletion, 'timezones' => $timezones, 'languages' => $languages]);
    }

    /**
     * Update user profile
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $user = $request->user();
            $validated = $request->validated();

            // Handle preferences separately
            $preferences = $user->preferences ?? [];
            if (isset($validated['preferences']) && is_array($validated['preferences'])) {
                $preferences = array_merge($preferences, $validated['preferences']);
                unset($validated['preferences']);
            }

            // Convert checkbox values to booleans
            $validated['email_notifications'] = $request->has('email_notifications');
            $validated['push_notifications'] = $request->has('push_notifications');

            // Fill user model with validated data
            $user->fill($validated);
            $user->preferences = $preferences;

            // Handle email change - require re-verification
            if ($user->isDirty('email')) {
                $user->email_verified_at = NULL;
                $user->sendEmailVerificationNotification();
            }

            $user->save();

            // Clear relevant caches
            Cache::forget("user_stats_{$user->id}");
            Cache::forget("profile_completion_{$user->id}");

            // Log the profile update for audit purposes
            activity()
                ->performedOn($user)
                ->causedBy($user)
                ->withProperties([
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'updated_fields' => array_keys($user->getDirty()),
                ])
                ->log('profile_updated');

            if ($request->ajax()) {
                return response()->json([
                    'success' => TRUE,
                    'message' => 'Profile updated successfully!',
                    'user'    => $user->only([
                        'name', 'surname', 'username', 'email', 'phone', 'bio',
                        'timezone', 'language', 'email_notifications', 'push_notifications',
                    ]),
                    'preferences' => $user->preferences,
                ]);
            }

            return Redirect::route('profile.edit')
                ->with('success', 'Profile updated successfully!');
        } catch (Exception $e) {
            Log::error('Profile update error: ' . $e->getMessage(), [
                'user_id'      => $request->user()?->id,
                'request_data' => $request->except(['password', '_token']),
                'trace'        => $e->getTraceAsString(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Failed to update profile. Please try again.',
                    'errors'  => config('app.debug') ? $e->getMessage() : NULL,
                ], 500);
            }

            return Redirect::route('profile.edit')
                ->with('error', 'Failed to update profile. Please try again.')
                ->withInput();
        }
    }

    /**
     * Upload profile picture
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'photo'     => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
                'crop_data' => 'nullable|json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $photo = $request->file('photo');
            $cropData = $request->input('crop_data') ? json_decode((string) $request->input('crop_data'), TRUE) : NULL;

            // Delete old profile picture
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Store image
            $path = $photo->storeAs(
                'profile_pictures',
                $user->id . '_' . time() . '.' . $photo->getClientOriginalExtension(),
                'public',
            );

            // Process image (crop and resize)
            $this->processProfileImage($path, $cropData);

            $user->profile_picture = $path;
            $user->save();

            return response()->json([
                'success'   => TRUE,
                'message'   => 'Profile picture updated successfully!',
                'image_url' => Storage::disk('public')->url($path),
            ]);
        } catch (Exception $e) {
            Log::error('Profile picture upload error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to upload profile picture.',
            ], 500);
        }
    }

    /**
     * Get user statistics for AJAX updates
     */
    public function stats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $cacheKey = "user_stats_{$user->id}";

            $userStats = Cache::remember($cacheKey, now()->addMinutes(5), fn (): array => [
                'monitored_events' => $user->ticketAlerts()->where('status', 'active')->count(),
                'total_alerts'     => $user->ticketAlerts()->count(),
                'active_searches'  => $user->ticketAlerts()
                    ->where('status', 'active')
                    ->where('created_at', '>=', now()->subMonth())
                    ->count(),
                'recent_purchases'   => 0,
                'login_count'        => $user->login_count ?? 0,
                'last_login_display' => $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never',
                'profile_completion' => $user->getProfileCompletion()['percentage'],
                'security_score'     => $this->calculateSecurityScore($user),
                'account_age_days'   => $user->created_at->diffInDays(now()),
            ]);

            return response()->json([
                'success'    => TRUE,
                'stats'      => $userStats,
                'updated_at' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Profile stats error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load statistics.',
            ], 500);
        }
    }

    /**
     * Show security settings
     */
    public function security(Request $request): View
    {
        $user = $request->user();

        return view('profile.security', [
            'user'             => $user,
            'twoFactorEnabled' => (bool) $user->two_factor_secret,
            'securityData'     => $this->getSecurityOverview($user),
        ]);
    }

    /**
     * Show analytics dashboard
     */
    public function analytics(Request $request): View
    {
        $user = $request->user();

        return view('profile.analytics', [
            'user'      => $user,
            'analytics' => $this->getAnalyticsOverview($user),
        ]);
    }

    /**
     * Get activity data
     */
    public function getActivityData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $period = (int) $request->get('period', 30);
            $startDate = now()->subDays($period);

            $data = [
                'login_history' => LoginHistory::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->orderBy('created_at', 'desc')
                    ->limit(50)
                    ->get(),
                'active_sessions' => UserSession::where('user_id', $user->id)
                    ->active()
                    ->orderBy('last_activity', 'desc')
                    ->get(),
                'alerts_activity' => TicketAlert::where('user_id', $user->id)
                    ->where('created_at', '>=', $startDate)
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->get(),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $data,
            ]);
        } catch (Exception $e) {
            Log::error('Activity data error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load activity data.',
            ], 500);
        }
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password'         => 'required|string|min:8|confirmed|different:current_password',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Current password is incorrect.',
                ], 422);
            }

            $user->update([
                'password'            => Hash::make($request->password),
                'password_changed_at' => now(),
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Password updated successfully!',
            ]);
        } catch (Exception $e) {
            Log::error('Password update error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update password.',
            ], 500);
        }
    }

    /**
     * Revoke session
     */
    public function revokeSession(Request $request, string $sessionId): JsonResponse
    {
        try {
            $user = $request->user();

            $session = UserSession::where('user_id', $user->id)
                ->where('id', $sessionId)
                ->first();

            if (! $session) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Session not found.',
                ], 404);
            }

            $session->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Session revoked successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Session revoke error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to revoke session.',
            ], 500);
        }
    }

    /**
     * Delete account (redirect to deletion protection system)
     */
    public function destroy(Request $request): RedirectResponse
    {
        return redirect()->route('account.deletion.warning');
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'notifications' => 'nullable|array',
                'privacy'       => 'nullable|array',
                'interface'     => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = $request->user();
            $preferences = $user->preferences ?? [];

            foreach ($request->only(['notifications', 'privacy', 'interface']) as $key => $value) {
                if ($value !== NULL) {
                    $preferences[$key] = $value;
                }
            }

            $user->update(['preferences' => $preferences]);

            return response()->json([
                'success'     => TRUE,
                'message'     => 'Preferences updated successfully!',
                'preferences' => $preferences,
            ]);
        } catch (Exception $e) {
            Log::error('Preferences update error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update preferences.',
            ], 500);
        }
    }

    /**
     * Advanced security page
     */
    public function advancedSecurity(Request $request): View
    {
        $user = $request->user();

        return view('profile.security.advanced', [
            'user'           => $user,
            'securityData'   => $this->getSecurityOverview($user),
            'sessions'       => UserSession::where('user_id', $user->id)->active()->get(),
            'trustedDevices' => $user->trusted_devices ?? [],
        ]);
    }

    /**
     * Revoke all other sessions
     */
    public function revokeAllOtherSessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $currentSessionId = Session::getId();

            UserSession::where('user_id', $user->id)
                ->where('session_id', '!=', $currentSessionId)
                ->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'All other sessions revoked successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Session revoke all error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to revoke sessions.',
            ], 500);
        }
    }

    /**
     * Trust current device
     */
    public function trustDevice(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $deviceFingerprint = $request->input('device_fingerprint');
            $deviceName = $request->input('device_name', 'Unknown Device');

            $trustedDevices = $user->trusted_devices ?? [];
            $trustedDevices[] = [
                'fingerprint' => $deviceFingerprint,
                'name'        => $deviceName,
                'added_at'    => now()->toISOString(),
                'last_used'   => now()->toISOString(),
            ];

            $user->update(['trusted_devices' => $trustedDevices]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Device trusted successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Trust device error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to trust device.',
            ], 500);
        }
    }

    /**
     * Remove trusted device
     */
    public function removeTrustedDevice(Request $request, int $deviceIndex): JsonResponse
    {
        try {
            $user = $request->user();
            $trustedDevices = $user->trusted_devices ?? [];

            if (! isset($trustedDevices[$deviceIndex])) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Device not found.',
                ], 404);
            }

            unset($trustedDevices[$deviceIndex]);
            $trustedDevices = array_values($trustedDevices);

            $user->update(['trusted_devices' => $trustedDevices]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Trusted device removed successfully.',
            ]);
        } catch (Exception $e) {
            Log::error('Remove trusted device error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to remove trusted device.',
            ], 500);
        }
    }

    /**
     * Download backup codes
     */
    public function downloadBackupCodes(Request $request): Response
    {
        try {
            $user = $request->user();

            if (! $user->two_factor_secret) {
                abort(404, 'Two-factor authentication is not enabled.');
            }

            $backupCodes = $user->getRecoveryCodes() ??
                collect(range(1, 10))->map(fn () => strtoupper(str_replace('-', '', uuid())))->toArray();

            $content = "HD Tickets - Two-Factor Authentication Backup Codes\n";
            $content .= 'Generated: ' . now()->format('Y-m-d H:i:s') . "\n";
            $content .= "User: {$user->email}\n";
            $content .= str_repeat('=', 50) . "\n\n";
            $content .= "BACKUP CODES (keep these safe):\n\n";

            foreach ($backupCodes as $index => $code) {
                $content .= sprintf("%2d. %s\n", $index + 1, $code);
            }

            $content .= "\n" . str_repeat('=', 50) . "\n";
            $content .= "⚠️  IMPORTANT NOTES:\n";
            $content .= "• Each code can only be used once\n";
            $content .= "• Store these codes in a secure location\n";
            $content .= "• Use these codes if you lose access to your authenticator\n";
            $content .= "• Generate new codes if you suspect they are compromised\n";

            return Response::make($content, 200, [
                'Content-Type'        => 'text/plain',
                'Content-Disposition' => 'attachment; filename="hd-tickets-backup-codes-' . date('Y-m-d') . '.txt"',
            ]);
        } catch (Exception $e) {
            Log::error('Download backup codes error: ' . $e->getMessage());
            abort(500, 'Failed to generate backup codes.');
        }
    }

    /**
     * Get analytics data for AJAX requests
     */
    public function getAnalyticsData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $analytics = $this->getAnalyticsOverview($user);

            return response()->json([
                'success'    => TRUE,
                'analytics'  => $analytics,
                'updated_at' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Analytics data error: ' . $e->getMessage());

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load analytics data.',
            ], 500);
        }
    }

    /**
     * Calculate security score
     *
     * @param mixed $user
     */
    private function calculateSecurityScore($user, ?array $securityStatus = NULL): int
    {
        if (! $securityStatus) {
            $securityStatus = [
                'email_verified'     => (bool) $user->email_verified_at,
                'two_factor_enabled' => (bool) $user->two_factor_secret,
                'profile_complete'   => $user->getProfileCompletion()['percentage'] >= 100,
                'password_age_days'  => $user->password_changed_at
                    ? $user->password_changed_at->diffInDays(now())
                    : $user->created_at->diffInDays(now()),
            ];
        }

        $score = 0;

        if ($securityStatus['email_verified']) {
            $score += 20;
        }
        if ($securityStatus['two_factor_enabled']) {
            $score += 30;
        }
        if ($securityStatus['profile_complete']) {
            $score += 20;
        }

        // Password age scoring
        $passwordAgeDays = $securityStatus['password_age_days'];
        if ($passwordAgeDays <= 90) {
            $score += 15;
        } elseif ($passwordAgeDays <= 180) {
            $score += 10;
        } elseif ($passwordAgeDays <= 365) {
            $score += 5;
        }

        // Recent activity
        if ($user->last_login_at && $user->last_login_at->isAfter(now()->subDays(7))) {
            $score += 10;
        }

        // Phone number
        if (! empty($user->phone)) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Generate recommendations
     */
    private function generateProfileRecommendations(array $profileCompletion, array $securityStatus): array
    {
        $recommendations = [];

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

        return $recommendations;
    }

    /**
     * Process profile image
     */
    private function processProfileImage(string $path, ?array $cropData = NULL): void
    {
        try {
            $fullPath = Storage::disk('public')->path($path);

            if ($cropData && extension_loaded('gd')) {
                $image = imagecreatefromstring(file_get_contents($fullPath));

                if ($image) {
                    $croppedImage = imagecrop($image, [
                        'x'      => (int) $cropData['x'],
                        'y'      => (int) $cropData['y'],
                        'width'  => (int) $cropData['width'],
                        'height' => (int) $cropData['height'],
                    ]);

                    if ($croppedImage) {
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
     * Get security overview
     *
     * @param mixed $user
     */
    private function getSecurityOverview($user): array
    {
        return [
            'security_score'     => $this->calculateSecurityScore($user),
            'two_factor_enabled' => (bool) $user->two_factor_secret,
            'email_verified'     => (bool) $user->email_verified_at,
            'recent_logins'      => LoginHistory::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'active_sessions' => UserSession::where('user_id', $user->id)
                ->active()
                ->get(),
            'password_age_days' => $user->password_changed_at
                ? $user->password_changed_at->diffInDays(now())
                : $user->created_at->diffInDays(now()),
        ];
    }

    /**
     * Get analytics data (internal)
     *
     * @param mixed $user
     */
    private function getAnalyticsOverview($user): array
    {
        return [
            'profile_views'       => $user->profile_views ?? 0,
            'login_streak'        => $this->calculateLoginStreak($user),
            'activity_trend'      => $this->getActivityTrend($user),
            'ticket_alerts_stats' => [
                'total'                => $user->ticketAlerts()->count(),
                'active'               => $user->ticketAlerts()->where('status', 'active')->count(),
                'triggered_this_month' => $user->ticketAlerts()
                    ->where('last_triggered_at', '>=', now()->startOfMonth())
                    ->count(),
            ],
        ];
    }

    /**
     * Calculate login streak
     *
     * @param mixed $user
     */
    private function calculateLoginStreak($user): int
    {
        $loginHistory = LoginHistory::where('user_id', $user->id)
            ->where('status', 'success')
            ->orderBy('created_at', 'desc')
            ->pluck('created_at')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->unique()
            ->values();

        $streak = 0;
        now()->format('Y-m-d');

        foreach ($loginHistory as $index => $loginDate) {
            $expectedDate = now()->subDays($index)->format('Y-m-d');

            if ($loginDate === $expectedDate) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Get activity trend
     *
     * @param mixed $user
     */
    private function getActivityTrend($user): array
    {
        $days = collect(range(0, 29))->map(function ($day) use ($user): array {
            $date = now()->subDays($day)->format('Y-m-d');

            return [
                'date'   => $date,
                'logins' => LoginHistory::where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
                'alerts_created' => TicketAlert::where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->count(),
            ];
        })->reverse()->values();

        return $days->toArray();
    }
}
