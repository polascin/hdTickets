<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Override;

/**
 * Sports Event Category Model
 *
 * @property int                            $id
 * @property string                         $uuid
 * @property int|null                       $parent_id
 * @property string                         $name
 * @property string|null                    $slug
 * @property string|null                    $description
 * @property string|null                    $color
 * @property string|null                    $icon
 * @property bool                           $is_active
 * @property int|null                       $sort_order
 * @property array|null                     $metadata
 * @property Carbon                         $created_at
 * @property Carbon                         $updated_at
 * @property Carbon|null                    $deleted_at
 * @property Category|null                  $parent
 * @property Collection<int, Category>      $children
 * @property Collection<int, ScrapedTicket> $scrapedTickets
 * @property Collection<int, Ticket>        $tickets
 * @property Collection<int, TicketSource>  $ticketSources
 * @property string                         $full_path
 * @property int                            $available_tickets_count
 * @property int                            $total_scraped_tickets_count
 * @property int                            $ticket_sources_count
 */
class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'parent_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'is_active',
        'sort_order',
        'metadata',
    ];

    protected $casts = ['deleted_at' => 'datetime'];

    /**
     * Get the route key for the model
     */
    /**
     * Get  route key name
     */
    #[Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Relationship: Parent category
     */
    /**
     * Parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relationship: Child categories
     */
    /**
     * Children
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Relationship: Scraped tickets in this category
     */
    /**
     * ScrapedTickets
     */
    public function scrapedTickets(): HasMany
    {
        return $this->hasMany(ScrapedTicket::class);
    }

    /**
     * Relationship: Tickets in this category
     */
    /**
     * Tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Relationship: Ticket sources in this category
     */
    /**
     * TicketSources
     */
    public function ticketSources(): HasMany
    {
        return $this->hasMany(TicketSource::class);
    }

    /**
     * Scope: Active categories
     *
     * @param mixed $query
     */
    /**
     * ScopeActive
     *
     * @param mixed $query
     */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Root categories (no parent)
     *
     * @param mixed $query
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Child categories (has parent)
     *
     * @param mixed $query
     */
    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope: Filter by parent
     *
     * @param mixed $query
     * @param mixed $parentId
     */
    public function scopeByParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope: Search categories
     *
     * @param mixed $query
     * @param mixed $search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search): void {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Ordered by sort order and name
     *
     * @param mixed $query
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Check if category is active
     */
    /**
     * Check if  active
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if category is root (no parent)
     */
    /**
     * Check if  root
     */
    public function isRoot(): bool
    {
        return null === $this->parent_id;
    }

    /**
     * Check if category has children
     */
    /**
     * Check if has  children
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get all ancestors (parents, grandparents, etc.)
     */
    /**
     * Get  ancestors
     */
    public function getAncestors(): array
    {
        $ancestors = [];
        $parent = $this->parent;

        while ($parent) {
            $ancestors[] = $parent;
            $parent = $parent->parent;
        }

        return array_reverse($ancestors);
    }

    /**
     * Get all descendants (children, grandchildren, etc.)
     */
    /**
     * Get  descendants
     */
    public function getDescendants(): array
    {
        $descendants = [];
        $children = $this->children;

        foreach ($children as $child) {
            $descendants[] = $child;
            $descendants = array_merge($descendants, $child->getDescendants());
        }

        return $descendants;
    }

    /**
     * Get  full path attribute
     */
    protected function fullPath(): Attribute
    {
        return Attribute::make(get: function () {
            $path = $this->name;
            $parent = $this->parent;
            while ($parent) {
                $path = $parent->name . ' > ' . $path;
                $parent = $parent->parent;
            }

            return $path;
        });
    }

    /**
     * Get  available tickets count attribute
     */
    protected function availableTicketsCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->scrapedTickets()->where('is_available', true)->count());
    }

    /**
     * Get  total scraped tickets count attribute
     */
    protected function totalScrapedTicketsCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->scrapedTickets()->count());
    }

    /**
     * Get  ticket sources count attribute
     */
    protected function ticketSourcesCount(): Attribute
    {
        return Attribute::make(get: fn () => $this->ticketSources()->count());
    }

    /**
     * Boot the model
     */
    /**
     * Boot
     */
    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($category): void {
            if (empty($category->uuid)) {
                $category->uuid = Str::uuid();
            }
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category): void {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
            'metadata'   => 'array',
        ];
    }
}
