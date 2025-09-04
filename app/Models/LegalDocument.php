<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Legal Document Model
 *
 * @property int         $id
 * @property string      $type
 * @property string      $title
 * @property string      $slug
 * @property string      $content
 * @property string      $version
 * @property bool        $is_active
 * @property bool        $requires_acceptance
 * @property Carbon      $effective_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class LegalDocument extends Model
{
    use HasFactory;

    // Legal document types
    public const TYPE_TERMS_OF_SERVICE = 'terms_of_service';

    public const TYPE_PRIVACY_POLICY = 'privacy_policy';

    public const TYPE_DISCLAIMER = 'disclaimer';

    public const TYPE_GDPR_COMPLIANCE = 'gdpr_compliance';

    public const TYPE_DATA_PROCESSING_AGREEMENT = 'data_processing_agreement';

    public const TYPE_COOKIE_POLICY = 'cookie_policy';

    public const TYPE_ACCEPTABLE_USE_POLICY = 'acceptable_use_policy';

    public const TYPE_LEGAL_NOTICES = 'legal_notices';

    protected $fillable = [
        'type',
        'title',
        'slug',
        'content',
        'version',
        'is_active',
        'requires_acceptance',
        'effective_date',
    ];

    protected $casts = [
        'is_active'           => 'boolean',
        'requires_acceptance' => 'boolean',
        'effective_date'      => 'datetime',
    ];

    /**
     * Get all available legal document types
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_TERMS_OF_SERVICE          => 'Terms of Service',
            self::TYPE_PRIVACY_POLICY            => 'Privacy Policy',
            self::TYPE_DISCLAIMER                => 'Disclaimer',
            self::TYPE_GDPR_COMPLIANCE           => 'GDPR Compliance',
            self::TYPE_DATA_PROCESSING_AGREEMENT => 'Data Processing Agreement',
            self::TYPE_COOKIE_POLICY             => 'Cookie Policy',
            self::TYPE_ACCEPTABLE_USE_POLICY     => 'Acceptable Use Policy',
            self::TYPE_LEGAL_NOTICES             => 'Legal Notices',
        ];
    }

    /**
     * Get documents that require acceptance during registration
     */
    public static function getRequiredForRegistration(): array
    {
        return [
            self::TYPE_TERMS_OF_SERVICE,
            self::TYPE_DISCLAIMER,
            self::TYPE_DATA_PROCESSING_AGREEMENT,
            self::TYPE_COOKIE_POLICY,
        ];
    }

    /**
     * Get active version of a document by type
     */
    public static function getActive(string $type): ?self
    {
        return static::where('type', $type)
            ->where('is_active', TRUE)
            ->where('effective_date', '<=', now())
            ->orderBy('effective_date', 'desc')
            ->first();
    }

    /**
     * Get all active documents required for registration
     */
    public static function getActiveRequiredDocuments(): array
    {
        $documents = [];
        foreach (self::getRequiredForRegistration() as $type) {
            if ($doc = self::getActive($type)) {
                $documents[$type] = $doc;
            }
        }

        return $documents;
    }

    /**
     * User acceptances for this document
     */
    public function userAcceptances(): HasMany
    {
        return $this->hasMany(UserLegalAcceptance::class);
    }

    /**
     * Get human-readable type name
     */
    public function getTypeNameAttribute(): string
    {
        return self::getTypes()[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Check if this document is currently active
     */
    public function isCurrentlyActive(): bool
    {
        return $this->is_active && $this->effective_date <= now();
    }

    /**
     * Generate unique slug based on title
     */
    public static function generateSlug(string $title, ?int $excludeId = NULL): string
    {
        $baseSlug = str_replace(' ', '-', strtolower(trim($title)));
        $baseSlug = preg_replace('/[^a-z0-9\-]/', '', $baseSlug);

        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
