<?php declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function count;

/**
 * User Legal Acceptance Model
 *
 * @property int           $id
 * @property int           $user_id
 * @property int           $legal_document_id
 * @property string        $document_version
 * @property string        $acceptance_method
 * @property string|null   $ip_address
 * @property string|null   $user_agent
 * @property Carbon        $accepted_at
 * @property Carbon|null   $created_at
 * @property Carbon|null   $updated_at
 * @property User          $user
 * @property LegalDocument $legalDocument
 */
class UserLegalAcceptance extends Model
{
    use HasFactory;

    // Acceptance methods
    public const METHOD_REGISTRATION = 'registration';

    public const METHOD_EXPLICIT = 'explicit';

    public const METHOD_UPDATE = 'update';

    protected $fillable = [
        'user_id',
        'legal_document_id',
        'document_version',
        'acceptance_method',
        'ip_address',
        'user_agent',
        'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    /**
     * User who accepted the document
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Legal document that was accepted
     */
    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class);
    }

    /**
     * Record user acceptance of a document
     */
    public static function recordAcceptance(
        int $userId,
        int $documentId,
        string $version,
        string $method = self::METHOD_EXPLICIT,
        ?string $ipAddress = NULL,
        ?string $userAgent = NULL,
    ): self {
        // Remove any existing acceptance for the same document type by this user
        $document = LegalDocument::find($documentId);
        if ($document) {
            static::where('user_id', $userId)
                ->whereHas('legalDocument', fn ($q) => $q->where('type', $document->type))
                ->delete();
        }

        return static::create([
            'user_id'           => $userId,
            'legal_document_id' => $documentId,
            'document_version'  => $version,
            'acceptance_method' => $method,
            'ip_address'        => $ipAddress ?: request()->ip(),
            'user_agent'        => $userAgent ?: request()->userAgent(),
            'accepted_at'       => now(),
        ]);
    }

    /**
     * Check if user has accepted all required documents
     */
    public static function hasUserAcceptedRequired(int $userId): bool
    {
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        $acceptedTypes = static::where('user_id', $userId)
            ->whereHas('legalDocument', fn ($q) => $q->whereIn('type', $requiredTypes))
            ->with('legalDocument')
            ->get()
            ->pluck('legalDocument.type')
            ->toArray();

        return count($requiredTypes) === count($acceptedTypes);
    }

    /**
     * Get user's acceptances for required documents
     */
    public static function getUserRequiredAcceptances(int $userId): array
    {
        $requiredTypes = LegalDocument::getRequiredForRegistration();
        $acceptances = [];

        foreach ($requiredTypes as $type) {
            $acceptance = static::where('user_id', $userId)
                ->whereHas('legalDocument', fn ($q) => $q->where('type', $type))
                ->with('legalDocument')
                ->orderBy('accepted_at', 'desc')
                ->first();

            $acceptances[$type] = $acceptance;
        }

        return $acceptances;
    }
}
