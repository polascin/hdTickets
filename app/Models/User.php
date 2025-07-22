<?php

namespace App\Models;

use App\Services\EncryptionService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

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
        'role',
        'is_active',
        'email_verified_at',
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
     * Relationship: Comments created by this user
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Relationship: Attachments uploaded by this user
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    /**
     * Relationship: Comments edited by this user
     */
    public function editedComments(): HasMany
    {
        return $this->hasMany(Comment::class, 'edited_by');
    }
}
