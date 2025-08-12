<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Relationship: Parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Relationship: Child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Relationship: Scraped tickets in this category
     */
    public function scrapedTickets(): HasMany
    {
        return $this->hasMany(ScrapedTicket::class);
    }

    /**
     * Relationship: Tickets in this category
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Relationship: Ticket sources in this category
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
    public function scopeActive($query)
    {
        return $query->where('is_active', TRUE);
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
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if category is root (no parent)
     */
    public function isRoot(): bool
    {
        return NULL === $this->parent_id;
    }

    /**
     * Check if category has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * Get the full category path (parent > child)
     */
    public function getFullPathAttribute(): string
    {
        $path = $this->name;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->name . ' > ' . $path;
            $parent = $parent->parent;
        }

        return $path;
    }

    /**
     * Get all ancestors (parents, grandparents, etc.)
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
     * Get available scraped tickets count
     */
    public function getAvailableTicketsCountAttribute(): int
    {
        return $this->scrapedTickets()->where('is_available', TRUE)->count();
    }

    /**
     * Get total scraped tickets count
     */
    public function getTotalScrapedTicketsCountAttribute(): int
    {
        return $this->scrapedTickets()->count();
    }

    /**
     * Get ticket sources count
     */
    public function getTicketSourcesCountAttribute(): int
    {
        return $this->ticketSources()->count();
    }

    /**
     * Boot the model
     */
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
}
