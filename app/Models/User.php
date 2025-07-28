<?php

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, LogsActivity;

    protected $encryptionService;
    
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->encryptionService = $this->getEncryptionService();
    }
    
    /**
     * Get the encryption service instance, with fallback for testing
     */
    protected function getEncryptionService()
    {
        try {
            return app(EncryptionService::class);
        } catch (\Exception $e) {
            // During testing or when EncryptionService is not available,
            // return a mock that just returns the value as-is
            return new class {
                public function encrypt($value) { return $value; }
                public function decrypt($value) { return $value; }
            };
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'phone',
        'password',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
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
    ];

    /**
     * User roles - Redesigned for scraping focus
     */
    const ROLE_ADMIN = 'admin';      // System and platform configuration management
    const ROLE_AGENT = 'agent';      // Ticket selection, purchasing, and monitoring
    const ROLE_CUSTOMER = 'customer'; // Legacy role (deprecated for new system)
    const ROLE_SCRAPER = 'scraper';  // Rotation users for scraping (no system access)

    /**
     * Get all available roles
     */
    public static function getRoles()
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
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is agent
     */
    public function isAgent()
    {
        return $this->hasRole(self::ROLE_AGENT);
    }

    /**
     * Check if user is customer
     */
    public function isCustomer()
    {
        return $this->hasRole(self::ROLE_CUSTOMER);
    }

    /**
     * Check if user is scraper (fake user for rotation)
     */
    public function isScraper()
    {
        return $this->hasRole(self::ROLE_SCRAPER);
    }

    /**
     * Check if user is root admin (ticketmaster)
     */
    public function isRootAdmin()
    {
        return $this->isAdmin() && $this->name === 'ticketmaster';
    }

    /**
     * Check if user has permission for user management
     */
    public function canManageUsers()
    {
        return $this->isAdmin();
    }

    /**
     * AGENT PERMISSIONS: Ticket selection, purchasing, and monitoring
     */
    
    /**
     * Check if user can select and purchase tickets
     */
    public function canSelectAndPurchaseTickets()
    {
        return $this->isAgent() || $this->isAdmin();
    }
    
    /**
     * Check if user can access ticket purchasing decisions
     */
    public function canMakePurchaseDecisions()
    {
        return $this->isAgent() || $this->isAdmin();
    }
    
    /**
     * Check if user can access monitoring management
     */
    public function canManageMonitoring()
    {
        return $this->isAgent() || $this->isAdmin();
    }
    
    /**
     * Check if user can view scraping performance metrics
     */
    public function canViewScrapingMetrics()
    {
        return $this->isAgent() || $this->isAdmin();
    }

    /**
     * ADMIN PERMISSIONS: System and platform configuration management
     */
    
    /**
     * Check if user can manage system configuration
     */
    public function canManageSystem()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access platform administration
     */
    public function canManagePlatforms()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access financial reports
     */
    public function canAccessFinancials()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage API access
     */
    public function canManageApiAccess()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can delete any data (root admin only)
     */
    public function canDeleteAnyData()
    {
        return $this->isRootAdmin();
    }
    
    /**
     * SCRAPER RESTRICTIONS: Scraper users have NO system access
     */
    
    /**
     * Check if user can access the system (scrapers cannot)
     */
    public function canAccessSystem()
    {
        return !$this->isScraper();
    }
    
    /**
     * Check if user can login to the web interface (scrapers cannot)
     */
    public function canLoginToWeb()
    {
        return !$this->isScraper();
    }
    
    /**
     * Check if user is used for scraping rotation only
     */
    public function isScrapingRotationUser()
    {
        return $this->isScraper();
    }

    /**
     * Get the user's full name (concatenated name and surname)
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->surname);
    }

    /**
     * Scope a query to only include users with unique usernames
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $username
     * @param int|null $excludeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUniqueUsername($query, $username, $excludeId = null)
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
     * @param string $username
     * @param int|null $excludeId
     * @return bool
     */
    public static function isUsernameUnique($username, $excludeId = null)
    {
        return !static::uniqueUsername($username, $excludeId)->exists();
    }

    /**
     * Get user's comprehensive permissions array
     */
    public function getPermissions()
    {
        $permissions = [
            // System Access
            'can_access_system' => $this->canAccessSystem(),
            'can_login_to_web' => $this->canLoginToWeb(),
            
            // Admin Permissions (System & Platform Configuration)
            'manage_users' => $this->canManageUsers(),
            'manage_system' => $this->canManageSystem(),
            'manage_platforms' => $this->canManagePlatforms(),
            'access_financials' => $this->canAccessFinancials(),
            'manage_api_access' => $this->canManageApiAccess(),
            'delete_any_data' => $this->canDeleteAnyData(),
            'access_scraping' => $this->canViewScrapingMetrics(),
            'access_reports' => $this->isAdmin(),
            
            // Agent Permissions (Ticket Selection, Purchasing, Monitoring)
            'select_and_purchase_tickets' => $this->canSelectAndPurchaseTickets(),
            'make_purchase_decisions' => $this->canMakePurchaseDecisions(),
            'manage_monitoring' => $this->canManageMonitoring(),
            'view_scraping_metrics' => $this->canViewScrapingMetrics(),
            
            // Role Checks
            'is_admin' => $this->isAdmin(),
            'is_agent' => $this->isAgent(),
            'is_customer' => $this->isCustomer(),
            'is_scraper' => $this->isScraper(),
            'is_root_admin' => $this->isRootAdmin(),
            'is_scraping_rotation_user' => $this->isScrapingRotationUser(),
        ];

        return $permissions;
    }

    /**
     * Enhanced Data Display Methods
     */

    /**
     * Get formatted last login information
     */
    public function getLastLoginInfo()
    {
        if (!$this->last_login_at) {
            return [
                'formatted' => 'Never logged in',
                'datetime' => null,
                'ip' => null,
                'user_agent' => null,
                'relative' => 'Never'
            ];
        }

        return [
            'formatted' => $this->last_login_at->format('M j, Y \a\t g:i A'),
            'datetime' => $this->last_login_at,
            'ip' => $this->last_login_ip,
            'user_agent' => $this->last_login_user_agent,
            'relative' => $this->last_login_at->diffForHumans()
        ];
    }

    /**
     * Get user activity statistics
     */
    public function getActivityStats()
    {
        return [
            'login_count' => $this->login_count ?? 0,
            'activity_score' => $this->activity_score ?? 0,
            'account_age_days' => $this->created_at ? $this->created_at->diffInDays(now()) : 0,
            'last_activity' => $this->last_activity_at ? $this->last_activity_at->diffForHumans() : 'No recent activity',
            'status' => $this->is_active ? 'Active' : 'Inactive',
            'email_verified' => $this->email_verified_at ? true : false,
        ];
    }

    /**
     * Get account creation source information
     */
    public function getAccountCreationInfo()
    {
        $sourceLabels = [
            'web' => 'Web Registration',
            'api' => 'API Creation',
            'admin' => 'Admin Created',
            'import' => 'Data Import',
            'system' => 'System Generated'
        ];

        return [
            'source' => $this->registration_source ?? 'web',
            'source_label' => $sourceLabels[$this->registration_source ?? 'web'] ?? 'Unknown',
            'created_by_type' => $this->created_by_type ?? 'self',
            'created_by_id' => $this->created_by_id,
            'created_at' => $this->created_at,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('M j, Y \a\t g:i A') : 'Unknown',
            'created_at_relative' => $this->created_at ? $this->created_at->diffForHumans() : 'Unknown'
        ];
    }

    /**
     * Get comprehensive user permissions for display
     */
    public function getUserPermissionsDisplay()
    {
        $permissions = $this->getPermissions();
        $roleDisplay = [
            'admin' => ['label' => 'Administrator', 'color' => 'red', 'icon' => 'shield-check'],
            'agent' => ['label' => 'Agent', 'color' => 'blue', 'icon' => 'user-check'],
            'customer' => ['label' => 'Customer', 'color' => 'green', 'icon' => 'user'],
            'scraper' => ['label' => 'Scraper Bot', 'color' => 'gray', 'icon' => 'cpu']
        ];

        $currentRole = $roleDisplay[$this->role] ?? ['label' => 'Unknown', 'color' => 'gray', 'icon' => 'question'];

        return [
            'role' => $this->role,
            'role_display' => $currentRole,
            'permissions' => $permissions,
            'is_system_accessible' => $this->canAccessSystem(),
            'custom_permissions' => $this->custom_permissions ?? [],
        ];
    }

    /**
     * Get profile picture URL or initials
     */
    public function getProfileDisplay()
    {
        $initials = strtoupper(substr($this->name, 0, 1) . substr($this->surname ?? '', 0, 1));
        
        return [
            'picture_url' => $this->profile_picture ? asset('storage/' . $this->profile_picture) : null,
            'initials' => $initials,
            'has_picture' => !empty($this->profile_picture),
            'full_name' => $this->getFullNameAttribute(),
            'display_name' => $this->getFullNameAttribute() ?: $this->username ?: $this->email,
            'bio' => $this->bio,
            'timezone' => $this->timezone ?? 'UTC',
            'language' => $this->language ?? 'en'
        ];
    }

    /**
     * Get notification preferences
     */
    public function getNotificationPreferences()
    {
        return [
            'email_notifications' => $this->email_notifications ?? true,
            'push_notifications' => $this->push_notifications ?? true,
        ];
    }

    /**
     * Get comprehensive user information for enhanced display
     */
    public function getEnhancedUserInfo()
    {
        return [
            'basic_info' => [
                'id' => $this->id,
                'uuid' => $this->uuid,
                'username' => $this->username,
                'email' => $this->email,
                'phone' => $this->phone,
                'full_name' => $this->getFullNameAttribute(),
            ],
            'profile' => $this->getProfileDisplay(),
            'last_login' => $this->getLastLoginInfo(),
            'activity_stats' => $this->getActivityStats(),
            'account_creation' => $this->getAccountCreationInfo(),
            'permissions' => $this->getUserPermissionsDisplay(),
            'notifications' => $this->getNotificationPreferences(),
        ];
    }

    /**
     * Encrypt email before saving to database.
     */
    // public function setEmailAttribute($value)
    // {
    //     if (!$this->encryptionService) {
    //         $this->encryptionService = $this->getEncryptionService();
    //     }
    //     $this->attributes['email'] = $this->encryptionService->encrypt($value);
    // }

    /**
     * Decrypt email after retrieving from database.
     */
    // public function getEmailAttribute($value)
    // {
    //     if (!$this->encryptionService) {
    //         $this->encryptionService = $this->getEncryptionService();
    //     }
    //     return $this->encryptionService->decrypt($value);
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'locked_until' => 'datetime',
            'password_changed_at' => 'datetime',
            'custom_permissions' => 'array',
            'trusted_devices' => 'array',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'array',
            'require_2fa' => 'boolean',
            'is_active' => 'boolean',
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            // 'email' => 'encrypted', // Temporarily disabled for seeding
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: Tickets created by this user
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'requester_id');
    }

    /**
     * Relationship: Tickets assigned to this user
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assignee_id');
    }


    /**
     * Relationship: User who created this account
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Relationship: Users created by this user
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by_id');
    }

    /**
     * Get all subscriptions for this user
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
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
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                              ->orWhere('ends_at', '>', now());
                    })
                    ->with('paymentPlan')
                    ->first();
    }

    /**
     * Check if user has an active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Check if user is on trial
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
        return $subscription ? $subscription->paymentPlan : null;
    }

    /**
     * Check if user can access feature based on their plan
     */
    public function canAccessFeature(string $feature): bool
    {
        $plan = $this->getCurrentPlan();
        
        if (!$plan) {
            return false; // No plan = no access
        }

        return match($feature) {
            'advanced_analytics' => $plan->advanced_analytics,
            'automated_purchasing' => $plan->automated_purchasing,
            'priority_support' => $plan->priority_support,
            default => true
        };
    }

    /**
     * Get remaining ticket allowance for current month
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
    public function hasReachedTicketLimit(): bool
    {
        $remaining = $this->getRemainingTicketAllowance();
        return $remaining !== -1 && $remaining <= 0;
    }

    /**
     * Subscribe user to a plan
     */
    public function subscribeToPlan(PaymentPlan $plan, array $options = []): UserSubscription
    {
        // Cancel existing active subscription if any
        if ($existing = $this->activeSubscription()) {
            $existing->cancel();
        }

        $subscription = $this->subscriptions()->create([
            'payment_plan_id' => $plan->id,
            'status' => $options['status'] ?? 'trial',
            'starts_at' => $options['starts_at'] ?? now(),
            'ends_at' => $options['ends_at'] ?? null,
            'trial_ends_at' => $options['trial_ends_at'] ?? now()->addDays(14),
            'stripe_subscription_id' => $options['stripe_subscription_id'] ?? null,
            'stripe_customer_id' => $options['stripe_customer_id'] ?? null,
            'amount_paid' => $options['amount_paid'] ?? 0,
            'payment_method' => $options['payment_method'] ?? null,
            'metadata' => $options['metadata'] ?? null,
        ]);

        // Update current subscription reference
        $this->update(['current_subscription_id' => $subscription->id]);

        return $subscription;
    }

    /**
     * Configure activity logging
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name', 'surname', 'email', 'username', 'role', 
                'is_active', 'phone', 'email_verified_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "User {$eventName}")
            ->useLogName('user_changes');
    }
}
