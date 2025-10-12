<?php declare(strict_types=1);

namespace App\Models;

use App\Services\EncryptionService;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Exception;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Log;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

use function count;

/**
 * User Model for Sports Ticket System
 *
 * @property int                                      $id
 * @property string|null                              $google_id
 * @property string                                   $name
 * @property string|null                              $surname
 * @property string|null                              $username
 * @property string                                   $email
 * @property string|null                              $phone
 * @property string|null                              $password
 * @property string|null                              $avatar
 * @property string|null                              $provider
 * @property string|null                              $provider_id
 * @property Carbon|null                              $provider_verified_at
 * @property bool|null                                $two_factor_enabled
 * @property string|null                              $two_factor_secret
 * @property Carbon|null                              $two_factor_confirmed_at
 * @property array|null                               $two_factor_recovery_codes
 * @property string                                   $role
 * @property bool                                     $is_active
 * @property Carbon|null                              $email_verified_at
 * @property Carbon|null                              $last_login_at
 * @property string|null                              $last_login_ip
 * @property string|null                              $last_login_user_agent
 * @property int                                      $login_count
 * @property int                                      $failed_login_attempts
 * @property Carbon|null                              $locked_until
 * @property bool|null                                $require_2fa
 * @property array|null                               $trusted_devices
 * @property Carbon|null                              $password_changed_at
 * @property string|null                              $registration_source
 * @property int|null                                 $activity_score
 * @property string|null                              $profile_picture
 * @property string|null                              $bio
 * @property string|null                              $timezone
 * @property string|null                              $language
 * @property string|null                              $created_by_type
 * @property int|null                                 $created_by_id
 * @property Carbon|null                              $last_activity_at
 * @property array|null                               $custom_permissions
 * @property bool|null                                $email_notifications
 * @property bool|null                                $push_notifications
 * @property int|null                                 $current_subscription_id
 * @property bool|null                                $has_trial_used
 * @property array|null                               $billing_address
 * @property string|null                              $stripe_customer_id
 * @property array|null                               $password_history
 * @property Carbon                                   $created_at
 * @property Carbon                                   $updated_at
 * @property Carbon|null                              $deleted_at
 * @property string|null                              $remember_token
 * @property Collection<int, Ticket>                  $tickets
 * @property Collection<int, Ticket>                  $assignedTickets
 * @property User|null                                $createdBy
 * @property Collection<int, User>                    $createdUsers
 * @property Collection<int, UserSubscription>        $subscriptions
 * @property Collection<int, TicketAlert>             $ticketAlerts
 * @property UserSubscription|null                    $currentSubscription
 * @property Collection<int, LoginHistory>            $loginHistory
 * @property Collection<int, UserSession>             $sessions
 * @property Collection<int, AccountDeletionRequest>  $deletionRequests
 * @property AccountDeletionRequest|null              $currentDeletionRequest
 * @property Collection<int, DataExportRequest>       $dataExportRequests
 * @property Collection<int, AccountDeletionAuditLog> $deletionAuditLogs
 * @property string                                   $full_name
 */
class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;
    use LogsActivity;
    use SoftDeletes;

    /** User roles - Redesigned for scraping focus */
    public const ROLE_ADMIN = 'admin';      // System and platform configuration management

    public const ROLE_AGENT = 'agent';      // Ticket selection, purchasing, and monitoring

    public const ROLE_CUSTOMER = 'customer'; // Legacy role (deprecated for new system)

    public const ROLE_SCRAPER = 'scraper';  // Rotation users for scraping (no system access)

    protected ?EncryptionService $encryptionService;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'surname',
        'username',
        'email',
        'phone',
        'password',
        'google_id',
        'avatar',
        'provider',
        'provider_id',
        'provider_verified_at',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'two_factor_enabled_at',
        'terms_accepted_at',
        'privacy_accepted_at',
        'marketing_opt_in',
        'role',
        'is_active',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'login_count',
        'failed_login_attempts',
        'locked_until',
        'require_2fa',
        'trusted_devices',
        'password_changed_at',
        'registration_source',
        'activity_score',
        'profile_picture',
        'bio',
        'timezone',
        'language',
        'created_by_type',
        'created_by_id',
        'last_activity_at',
        'custom_permissions',
        'email_notifications',
        'push_notifications',
        'current_subscription_id',
        'has_trial_used',
        'billing_address',
        'stripe_customer_id',
        'password_history',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        try {
            $this->encryptionService = new EncryptionService();
        } catch (Exception $e) {
            // Log the error but continue without encryption
            logger('EncryptionService failed to initialize: ' . $e->getMessage());
            $this->encryptionService = NULL;
        }

        // Encrypt sensitive fields on save
        // Commented out custom encryption logic - using Laravel casts instead
        // static::saving(function ($model): void {
        //     if ($model->encryptionService) {
        //         foreach ($model->getEncryptedFields() as $field) {
        //             if (! empty($model->$field)) {
        //                 $model->$field = $model->encryptionService->encrypt($model->$field);
        //             }
        //         }
        //     }
        // });
        //
        // // Decrypt sensitive fields on retrieve
        // static::retrieved(function ($model): void {
        //     if ($model->encryptionService) {
        //         foreach ($model->getEncryptedFields() as $field) {
        //             if (! empty($model->$field)) {
        //         $model->$field = $model->encryptionService->decrypt($model->$field);
        //     }
        // });
        // });
    }

    /**
     * Get all available roles
     *
     * @return array<int, string>
     */
    /**
     * Get  roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_AGENT,
            self::ROLE_CUSTOMER,
            self::ROLE_SCRAPER,
        ];
    }

    /**
     * Check if user has specific role
     *
     * @param mixed $role
     */
    /**
     * Check if has  role
     *
     * @param mixed $role
     */
    public function hasRole($role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    /**
     * Check if  admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is agent
     */
    /**
     * Check if  agent
     */
    public function isAgent(): bool
    {
        return $this->hasRole(self::ROLE_AGENT);
    }

    /**
     * Check if user is customer
     */
    /**
     * Check if  customer
     */
    public function isCustomer(): bool
    {
        return $this->hasRole(self::ROLE_CUSTOMER);
    }

    /**
     * Check if user is scraper (fake user for rotation)
     */
    /**
     * Check if  scraper
     */
    public function isScraper(): bool
    {
        return $this->hasRole(self::ROLE_SCRAPER);
    }

    /**
     * Check if user is root admin (ticketmaster)
     */
    /**
     * Check if  root admin
     */
    public function isRootAdmin(): bool
    {
        return $this->isAdmin() && $this->name === 'ticketmaster';
    }

    /**
     * Check if user has permission for user management
     */
    /**
     * Check if can  manage users
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * AGENT PERMISSIONS: Ticket selection, purchasing, and monitoring
     */

    /**
     * Check if user can select and purchase tickets
     */
    /**
     * Check if can  select and purchase tickets
     */
    public function canSelectAndPurchaseTickets(): bool
    {
        if ($this->isAgent()) {
            return TRUE;
        }

        return $this->isAdmin();
    }

    /**
     * Check if user can access ticket purchasing decisions
     */
    /**
     * Check if can  make purchase decisions
     */
    public function canMakePurchaseDecisions(): bool
    {
        if ($this->isAgent()) {
            return TRUE;
        }

        return $this->isAdmin();
    }

    /**
     * Check if user can access monitoring management
     */
    /**
     * Check if can  manage monitoring
     */
    public function canManageMonitoring(): bool
    {
        if ($this->isAgent()) {
            return TRUE;
        }

        return $this->isAdmin();
    }

    /**
     * Check if user can view scraping performance metrics
     */
    /**
     * Check if can  view scraping metrics
     */
    public function canViewScrapingMetrics(): bool
    {
        if ($this->isAgent()) {
            return TRUE;
        }

        return $this->isAdmin();
    }

    /**
     * ADMIN PERMISSIONS: System and platform configuration management
     */

    /**
     * Check if user can manage system configuration
     */
    /**
     * Check if can  manage system
     */
    public function canManageSystem(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access platform administration
     */
    /**
     * Check if can  manage platforms
     */
    public function canManagePlatforms(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access financial reports
     */
    /**
     * Check if can  access financials
     */
    public function canAccessFinancials(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage API access
     */
    /**
     * Check if can  manage api access
     */
    public function canManageApiAccess(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can delete any data (root admin only)
     */
    /**
     * Check if can  delete any data
     */
    public function canDeleteAnyData(): bool
    {
        return $this->isRootAdmin();
    }

    /**
     * SCRAPER RESTRICTIONS: Scraper users have NO system access
     */

    /**
     * Check if user can access the system (scrapers cannot)
     */
    /**
     * Check if can  access system
     */
    public function canAccessSystem(): bool
    {
        return !$this->isScraper();
    }

    /**
     * Check if user can login to the web interface (scrapers cannot)
     */
    /**
     * Check if can  login to web
     */
    public function canLoginToWeb(): bool
    {
        return !$this->isScraper();
    }

    /**
     * Check if user is used for scraping rotation only
     */
    /**
     * Check if  scraping rotation user
     */
    public function isScrapingRotationUser(): bool
    {
        return $this->isScraper();
    }

    /**
     * Scope a query to only include users with unique usernames
     *
     * @param Builder  $query
     * @param string   $username
     * @param int|null $excludeId
     *
     * @return Builder
     */
    public function scopeUniqueUsername($query, $username, $excludeId = NULL)
    {
        $query = $query->where('username', $username);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query;
    }

    /**
     * Check if username is unique
     *
     * @param string   $username
     * @param int|null $excludeId
     */
    /**
     * Check if  username unique
     *
     * @param mixed $username
     * @param mixed $excludeId
     */
    public function isUsernameUnique($username, $excludeId = NULL): bool
    {
        return !static::uniqueUsername($username, $excludeId)->exists();
    }

    /**
     * Get user's comprehensive permissions array
     *
     * @return array<string, bool>
     */
    /**
     * Get  permissions
     */
    public function getPermissions(): array
    {
        return [
            // System Access
            'can_access_system' => $this->canAccessSystem(),
            'can_login_to_web'  => $this->canLoginToWeb(),

            // Admin Permissions (System & Platform Configuration)
            'manage_users'      => $this->canManageUsers(),
            'manage_system'     => $this->canManageSystem(),
            'manage_platforms'  => $this->canManagePlatforms(),
            'access_financials' => $this->canAccessFinancials(),
            'manage_api_access' => $this->canManageApiAccess(),
            'delete_any_data'   => $this->canDeleteAnyData(),
            'access_scraping'   => $this->canViewScrapingMetrics(),
            'access_reports'    => $this->isAdmin(),

            // Agent Permissions (Ticket Selection, Purchasing, Monitoring)
            'select_and_purchase_tickets' => $this->canSelectAndPurchaseTickets(),
            'make_purchase_decisions'     => $this->canMakePurchaseDecisions(),
            'manage_monitoring'           => $this->canManageMonitoring(),
            'view_scraping_metrics'       => $this->canViewScrapingMetrics(),

            // Role Checks
            'is_admin'                  => $this->isAdmin(),
            'is_agent'                  => $this->isAgent(),
            'is_customer'               => $this->isCustomer(),
            'is_scraper'                => $this->isScraper(),
            'is_root_admin'             => $this->isRootAdmin(),
            'is_scraping_rotation_user' => $this->isScrapingRotationUser(),
        ];
    }

    /**
     * Enhanced Data Display Methods
     */

    /**
     * Get formatted last login information
     */
    public function getLastLoginInfo(): array
    {
        if (!$this->last_login_at) {
            return [
                'formatted'  => 'Never logged in',
                'datetime'   => NULL,
                'ip'         => NULL,
                'user_agent' => NULL,
                'relative'   => 'Never',
            ];
        }

        return [
            'formatted'  => $this->last_login_at->format('M j, Y \a\t g:i A'),
            'datetime'   => $this->last_login_at,
            'ip'         => $this->last_login_ip,
            'user_agent' => $this->last_login_user_agent,
            'relative'   => $this->last_login_at->diffForHumans(),
        ];
    }

    /**
     * Get user activity statistics
     */
    /**
     * Get  activity stats
     *
     * @return array<string, mixed>
     */
    public function getActivityStats(): array
    {
        return [
            'login_count'      => $this->login_count ?? 0,
            'activity_score'   => $this->activity_score ?? 0,
            'account_age_days' => $this->created_at ? $this->created_at->diffInDays(now()) : 0,
            'last_activity'    => $this->last_activity_at ? $this->last_activity_at->diffForHumans() : 'No recent activity',
            'status'           => $this->is_active ? 'Active' : 'Inactive',
            'email_verified'   => (bool) $this->email_verified_at,
        ];
    }

    /**
     * Get account creation source information
     */
    public function getAccountCreationInfo(): array
    {
        $sourceLabels = [
            'web'    => 'Web Registration',
            'api'    => 'API Creation',
            'admin'  => 'Admin Created',
            'import' => 'Data Import',
            'system' => 'System Generated',
        ];

        return [
            'source'               => $this->registration_source ?? 'web',
            'source_label'         => $sourceLabels[$this->registration_source ?? 'web'] ?? 'Unknown',
            'created_by_type'      => $this->created_by_type ?? 'self',
            'created_by_id'        => $this->created_by_id,
            'created_at'           => $this->created_at,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('M j, Y \a\t g:i A') : 'Unknown',
            'created_at_relative'  => $this->created_at ? $this->created_at->diffForHumans() : 'Unknown',
        ];
    }

    /**
     * Get comprehensive user permissions for display
     */
    public function getUserPermissionsDisplay(): array
    {
        $permissions = $this->getPermissions();
        $roleDisplay = [
            'admin'    => ['label' => 'Administrator', 'color' => 'red', 'icon' => 'shield-check'],
            'agent'    => ['label' => 'Agent', 'color' => 'blue', 'icon' => 'user-check'],
            'customer' => ['label' => 'Customer', 'color' => 'green', 'icon' => 'user'],
            'scraper'  => ['label' => 'Scraper Bot', 'color' => 'gray', 'icon' => 'cpu'],
        ];

        $currentRole = $roleDisplay[$this->role] ?? ['label' => 'Unknown', 'color' => 'gray', 'icon' => 'question'];

        return [
            'role'                 => $this->role,
            'role_display'         => $currentRole,
            'permissions'          => $permissions,
            'is_system_accessible' => $this->canAccessSystem(),
            'custom_permissions'   => $this->custom_permissions ?? [],
        ];
    }

    /**
     * Calculate profile completion percentage
     */
    public function getProfileCompletion(): array
    {
        $fields = [
            'name'               => !empty($this->name),
            'surname'            => !empty($this->surname),
            'phone'              => !empty($this->phone),
            'bio'                => !empty($this->bio),
            'profile_picture'    => !empty($this->profile_picture),
            'timezone'           => !empty($this->timezone),
            'language'           => !empty($this->language),
            'two_factor_enabled' => $this->two_factor_enabled ?? FALSE,
        ];

        $completedFields = array_filter($fields);
        $completionPercentage = round((count($completedFields) / count($fields)) * 100);

        // Determine completion status
        $status = 'incomplete';
        if ($completionPercentage >= 90) {
            $status = 'excellent';
        } elseif ($completionPercentage >= 75) {
            $status = 'good';
        } elseif ($completionPercentage >= 50) {
            $status = 'fair';
        }

        return [
            'percentage'       => $completionPercentage,
            'status'           => $status,
            'completed_fields' => $completedFields,
            'missing_fields'   => array_keys(array_filter($fields, fn (bool $value): bool => !$value)),
            'total_fields'     => count($fields),
            'completed_count'  => count($completedFields),
            'is_complete'      => $completionPercentage >= 90,
        ];
    }

    /**
     * Get profile picture URL or initials
     */
    public function getProfileDisplay(): array
    {
        $initials = strtoupper(substr($this->name, 0, 1) . substr($this->surname ?? '', 0, 1));

        // Handle profile picture URL - check if it already contains full URL
        $pictureUrl = NULL;
        if ($this->profile_picture) {
            if (str_starts_with($this->profile_picture, 'http')) {
                // Already a full URL (from new upload system)
                $pictureUrl = $this->profile_picture;
            } else {
                // Legacy format - add asset path
                $pictureUrl = asset('storage/' . $this->profile_picture);
            }
        }

        return [
            'picture_url'  => $pictureUrl,
            'initials'     => $initials,
            'has_picture'  => !empty($this->profile_picture),
            'full_name'    => $this->full_name,
            'display_name' => ($this->full_name ?: $this->username) ?: $this->email,
            'bio'          => $this->bio,
            'timezone'     => $this->timezone ?? 'UTC',
            'language'     => $this->language ?? 'en',
        ];
    }

    /**
     * Get all available profile picture sizes
     */
    /**
     * Get  profile picture sizes
     */
    public function getProfilePictureSizes(): array
    {
        if (!$this->profile_picture || !$this->id) {
            return [];
        }

        $sizes = [];
        $profilePicturesPath = storage_path('app/public/profile-pictures');

        if (is_dir($profilePicturesPath)) {
            $files = glob($profilePicturesPath . "/profile_{$this->id}_*");

            foreach ($files as $file) {
                $filename = basename($file);
                if (preg_match("/^profile_{$this->id}_.*_(\w+)\.webp$/", $filename, $matches)) {
                    $sizeName = $matches[1];
                    $sizes[$sizeName] = asset('storage/profile-pictures/' . $filename);
                }
            }
        }

        return $sizes;
    }

    /**
     * Get profile picture URL by size
     */
    /**
     * Get  profile picture url
     */
    public function getProfilePictureUrl(string $size = 'medium'): ?string
    {
        $sizes = $this->getProfilePictureSizes();

        return $sizes[$size] ?? $this->getProfileDisplay()['picture_url'];
    }

    /**
     * Get notification preferences
     */
    public function getNotificationPreferences(): array
    {
        return [
            'email_notifications' => $this->email_notifications ?? TRUE,
            'push_notifications'  => $this->push_notifications ?? TRUE,
        ];
    }

    /**
     * Get comprehensive user information for enhanced display
     */
    public function getEnhancedUserInfo(): array
    {
        return [
            'basic_info' => [
                'id'        => $this->id,
                'uuid'      => $this->uuid,
                'username'  => $this->username,
                'email'     => $this->email,
                'phone'     => $this->phone,
                'full_name' => $this->full_name,
            ],
            'profile'          => $this->getProfileDisplay(),
            'last_login'       => $this->getLastLoginInfo(),
            'activity_stats'   => $this->getActivityStats(),
            'account_creation' => $this->getAccountCreationInfo(),
            'permissions'      => $this->getUserPermissionsDisplay(),
            'notifications'    => $this->getNotificationPreferences(),
        ];
    }

    /**
     * Relationship: Tickets created by this user
     */
    /**
     * Tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /**
     * Relationship: Tickets assigned to this user
     */
    /**
     * AssignedTickets
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }

    /**
     * Relationship: User who created this account
     */
    /**
     * CreatedBy
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by_id');
    }

    /**
     * Relationship: Users created by this user
     */
    /**
     * CreatedUsers
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by_id');
    }

    /**
     * Get all subscriptions for this user
     */
    /**
     * Subscriptions
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get all ticket alerts for this user
     */
    /**
     * TicketAlerts
     */
    public function ticketAlerts(): HasMany
    {
        return $this->hasMany(TicketAlert::class);
    }

    /**
     * Get the current active subscription
     */
    public function currentSubscription()
    {
        return $this->belongsTo(UserSubscription::class, 'current_subscription_id');
    }

    /**
     * Get the user's active subscription
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->with('paymentPlan')
            ->first();
    }

    /**
     * Get the user's subscription (alias for current subscription)
     * This is used by the dashboard to access subscription information
     */
    public function subscription()
    {
        return $this->currentSubscription();
    }

    /**
     * Relationship: Login history records for this user
     */
    /**
     * LoginHistory
     */
    public function loginHistory(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * Relationship: Active sessions for this user
     */
    /**
     * Sessions
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }

    /**
     * Get recent login history
     */
    public function recentLoginHistory(int $limit = 10)
    {
        return $this->loginHistory()
            ->orderBy('attempted_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get active sessions
     */
    public function activeSessions()
    {
        return $this->sessions()
            ->active()
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    /**
     * Check if user has an active subscription
     */
    /**
     * Check if has  active subscription
     */
    public function hasActiveSubscription(): bool
    {
        try {
            // Check if subscriptions table exists
            if (!$this->subscriptions()->getModel()->getConnection()->getSchemaBuilder()->hasTable('user_subscriptions')) {
                return FALSE; // No subscriptions table means no subscriptions
            }

            return $this->activeSubscription() !== NULL;
        } catch (Exception $e) {
            Log::warning('Error checking active subscription for user ' . $this->id . ': ' . $e->getMessage());

            return FALSE; // Default to no subscription on error
        }
    }

    /**
     * Check if user is on trial
     */
    /**
     * Check if  on trial
     */
    public function isOnTrial(): bool
    {
        $subscription = $this->activeSubscription();

        return $subscription && $subscription->isOnTrial();
    }

    /**
     * Get user's current payment plan
     */
    public function getCurrentPlan()
    {
        $subscription = $this->activeSubscription();

        return $subscription ? $subscription->paymentPlan : NULL;
    }

    /**
     * Check if user can access feature based on their plan
     */
    /**
     * Check if can  access feature
     */
    public function canAccessFeature(string $feature): bool
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            return FALSE; // No plan = no access
        }

        return match ($feature) {
            'advanced_analytics'   => $plan->advanced_analytics,
            'automated_purchasing' => $plan->automated_purchasing,
            'priority_support'     => $plan->priority_support,
            default                => TRUE,
        };
    }

    /**
     * Get remaining ticket allowance for current month
     */
    /**
     * Get  remaining ticket allowance
     */
    public function getRemainingTicketAllowance(): int
    {
        $plan = $this->getCurrentPlan();

        if (!$plan || $plan->hasUnlimitedTickets()) {
            return -1; // Unlimited
        }

        // You would need to track ticket usage per month
        // This is a simplified version
        return $plan->max_tickets_per_month;
    }

    /**
     * Check if user has reached their monthly ticket limit
     */
    /**
     * Check if has  reached ticket limit
     */
    public function hasReachedTicketLimit(): bool
    {
        $remaining = $this->getRemainingTicketAllowance();

        return $remaining !== -1 && $remaining <= 0;
    }

    /**
     * Subscribe user to a plan
     */
    /**
     * SubscribeToPlan
     */
    public function subscribeToPlan(PaymentPlan $plan, array $options = []): UserSubscription
    {
        // Cancel existing active subscription if any
        if ($existing = $this->activeSubscription()) {
            $existing->cancel();
        }

        $subscription = $this->subscriptions()->create([
            'payment_plan_id'        => $plan->id,
            'status'                 => $options['status'] ?? 'trial',
            'starts_at'              => $options['starts_at'] ?? now(),
            'ends_at'                => $options['ends_at'] ?? NULL,
            'trial_ends_at'          => $options['trial_ends_at'] ?? now()->addDays(14),
            'stripe_subscription_id' => $options['stripe_subscription_id'] ?? NULL,
            'stripe_customer_id'     => $options['stripe_customer_id'] ?? NULL,
            'amount_paid'            => $options['amount_paid'] ?? 0,
            'payment_method'         => $options['payment_method'] ?? NULL,
            'metadata'               => $options['metadata'] ?? NULL,
        ]);

        // Update current subscription reference
        $this->update(['current_subscription_id' => $subscription->id]);

        return $subscription;
    }

    /**
     * Relationship: Account deletion requests for this user
     */
    /**
     * DeletionRequests
     */
    public function deletionRequests(): HasMany
    {
        return $this->hasMany(AccountDeletionRequest::class);
    }

    /**
     * Relationship: Push notification subscriptions
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Relationship: Current active deletion request
     */
    /**
     * CurrentDeletionRequest
     */
    public function currentDeletionRequest(): HasOne
    {
        return $this->hasOne(AccountDeletionRequest::class)->active()->latest();
    }

    /**
     * Relationship: Data export requests for this user
     */
    /**
     * DataExportRequests
     */
    public function dataExportRequests(): HasMany
    {
        return $this->hasMany(DataExportRequest::class);
    }

    /**
     * Relationship: Deletion audit logs for this user
     */
    /**
     * DeletionAuditLogs
     */
    public function deletionAuditLogs(): HasMany
    {
        return $this->hasMany(AccountDeletionAuditLog::class);
    }

    /**
     * Check if user has an active deletion request
     */
    /**
     * Check if has  active deletion request
     */
    public function hasActiveDeletionRequest(): bool
    {
        return $this->currentDeletionRequest !== NULL;
    }

    /**
     * Check if user is in grace period
     */
    /**
     * Check if  in deletion grace period
     */
    public function isInDeletionGracePeriod(): bool
    {
        $request = $this->currentDeletionRequest;

        return $request && $request->isInGracePeriod();
    }

    /**
     * Get the current deletion request
     */
    /**
     * Get  current deletion request
     */
    public function getCurrentDeletionRequest(): ?AccountDeletionRequest
    {
        return $this->currentDeletionRequest;
    }

    /**
     * Configure activity logging
     */
    /**
     * Get  activitylog options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'surname', 'email', 'username', 'role',
                'is_active', 'phone', 'email_verified_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "User {$eventName}")
            ->useLogName('user_changes');
    }

    public function isVerified(): bool
    {
        return NULL !== $this->email_verified_at;
    }

    public function hasPermission(string $permission): bool
    {
        return TRUE;
    }

    public function scrapedTickets(): HasMany
    {
        return $this->hasMany(ScrapedTicket::class);
    }

    public function purchaseAttempts(): HasMany
    {
        return $this->hasMany(PurchaseAttempt::class);
    }

    public function purchaseQueues(): HasMany
    {
        return $this->hasMany(PurchaseQueue::class);
    }

    /**
     * User's ticket purchases
     */
    public function ticketPurchases(): HasMany
    {
        return $this->hasMany(TicketPurchase::class);
    }

    public function unreadNotifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->whereNull('read_at');
    }

    /**
     * RBAC Relationships
     */

    /**
     * User's assigned roles
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['assigned_at', 'expires_at', 'assigned_by'])
            ->withTimestamps();
    }

    /**
     * User's direct permissions
     */
    public function userPermissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    /**
     * User's resource access permissions
     */
    public function resourceAccess(): HasMany
    {
        return $this->hasMany(ResourceAccess::class);
    }

    /**
     * Two-factor authentication backup codes
     */
    public function twoFactorBackupCodes(): HasMany
    {
        return $this->hasMany(TwoFactorBackupCode::class);
    }

    /**
     * Two-factor recovery records
     */
    public function twoFactorRecoveries(): HasMany
    {
        return $this->hasMany(TwoFactorRecovery::class);
    }

    /**
     * User's login attempts
     */
    public function loginAttempts(): HasMany
    {
        return $this->hasMany(LoginAttempt::class);
    }

    /**
     * User's trusted devices
     */
    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * User's security events
     */
    public function securityEvents(): HasMany
    {
        return $this->hasMany(SecurityEvent::class);
    }

    /**
     * User's favorite teams for sports events monitoring
     */
    public function favoriteTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'user_favorite_teams', 'user_id', 'team_id')
            ->withTimestamps();
    }

    /**
     * User's favorite venues for sports events monitoring
     */
    public function favoriteVenues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'user_favorite_venues', 'user_id', 'venue_id')
            ->withTimestamps();
    }

    /**
     * User preferences for the sports events system
     */
    public function userPreferences(): HasMany
    {
        return $this->hasMany(UserPreference::class);
    }

    /**
     * User preferences (alias for userPreferences)
     * This is used by the dashboard to access user preferences
     */
    public function preferences(): HasMany
    {
        return $this->userPreferences();
    }

    /**
     * Get user's monthly ticket limit based on their subscription plan
     */
    public function getMonthlyTicketLimit(): int
    {
        $plan = $this->getCurrentPlan();

        if (!$plan) {
            // Free trial default limit
            return (int) config('subscription.default_ticket_limit', 100);
        }

        return $plan->max_tickets_per_month ?? 100;
    }

    /**
     * Get user's current monthly ticket usage
     */
    public function getMonthlyTicketUsage(): int
    {
        try {
            // Use purchase attempts table which exists
            return $this->purchaseAttempts()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->whereIn('status', ['success', 'in_progress', 'pending'])
                ->sum('attempted_quantity') ?? 0;
        } catch (Exception $e) {
            Log::warning('Error calculating monthly ticket usage for user ' . $this->id . ': ' . $e->getMessage());

            return 0; // Return 0 on error to prevent crashes
        }
    }

    /**
     * Get remaining days in free trial period
     */
    public function getFreeTrialDaysRemaining(): ?int
    {
        if ($this->hasActiveSubscription()) {
            return NULL; // Not on trial
        }

        $trialPeriod = (int) config('subscription.free_access_days', 7);
        $daysSinceRegistration = (int) $this->created_at->diffInDays(now());
        $daysRemaining = $trialPeriod - $daysSinceRegistration;

        return max(0, $daysRemaining);
    }

    /**
     * Get  full name attribute
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(get: fn (): string => trim($this->name . ' ' . $this->surname));
    }

    /**
     * Get the profile photo URL attribute
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::make(get: function () {
            if ($this->profile_photo_path) {
                return asset('storage/' . $this->profile_photo_path);
            }

            // Return default avatar based on initials
            return 'https://ui-avatars.com/api/?' . http_build_query([
                'name'       => $this->name,
                'color'      => '7C3AED',
                'background' => 'EDE9FE',
                'size'       => 200,
                'bold'       => 'true',
            ]);
        });
    }

    /**
     * Get the encryption service instance, with fallback for testing
     */
    protected function getEncryptionService()
    {
        try {
            return app(EncryptionService::class);
        } catch (Exception $e) {
            // Log the error and return null instead of anonymous class
            logger('EncryptionService not available: ' . $e->getMessage());

            return;
        }
    }

    /**
     * List of encrypted fields
     *
     * @return array<int, string>
     */
    /**
     * Get  encrypted fields
     */
    protected function getEncryptedFields(): array
    {
        return [
            'phone',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    /**
     * Casts
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'terms_accepted_at'         => 'datetime',
            'privacy_accepted_at'       => 'datetime',
            'two_factor_enabled_at'     => 'datetime',
            'last_login_at'             => 'datetime',
            'last_activity_at'          => 'datetime',
            'locked_until'              => 'datetime',
            'password_changed_at'       => 'datetime',
            'provider_verified_at'      => 'datetime',
            'custom_permissions'        => 'array',
            'trusted_devices'           => 'array',
            'two_factor_enabled'        => 'boolean',
            'two_factor_confirmed_at'   => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'marketing_opt_in'          => 'boolean',
            'require_2fa'               => 'boolean',
            'is_active'                 => 'boolean',
            'email_notifications'       => 'boolean',
            'push_notifications'        => 'boolean',
            'password_history'          => 'array',
            // 'email' => 'encrypted', // Temporarily disabled for seeding
            'phone'             => 'encrypted',
            'two_factor_secret' => 'encrypted',
            'password'          => 'hashed',
        ];
    }
}
