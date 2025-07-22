<?php

namespace App\Models;

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
        'password',
        'role',
        'is_active',
        'email_verified_at',
    ];

    /**
     * User roles
     */
    const ROLE_ADMIN = 'admin';
    const ROLE_AGENT = 'agent';
    const ROLE_CUSTOMER = 'customer';

    /**
     * Get all available roles
     */
    public static function getRoles()
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_AGENT,
            self::ROLE_CUSTOMER,
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
     * Check if user can manage all tickets
     */
    public function canManageAllTickets()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access scraping operations
     */
    public function canAccessScraping()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can manage system configuration
     */
    public function canManageSystem()
    {
        return $this->isAdmin();
    }

    /**
     * Check if user can access performance monitoring
     */
    public function canAccessMonitoring()
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
            'manage_users' => $this->canManageUsers(),
            'manage_all_tickets' => $this->canManageAllTickets(),
            'access_scraping' => $this->canAccessScraping(),
            'manage_system' => $this->canManageSystem(),
            'access_monitoring' => $this->canAccessMonitoring(),
            'manage_platforms' => $this->canManagePlatforms(),
            'access_financials' => $this->canAccessFinancials(),
            'manage_api_access' => $this->canManageApiAccess(),
            'delete_any_data' => $this->canDeleteAnyData(),
            'is_root_admin' => $this->isRootAdmin(),
        ];

        return $permissions;
    }

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
