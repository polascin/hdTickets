<?php declare(strict_types=1);

namespace App\Traits;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Model;

use function array_key_exists;
use function in_array;

/**
 * Trait for models that need AES-256 encryption
 *
 * This trait provides a standardized way to handle encrypted attributes
 * across multiple models with minimal code duplication.
 */
trait HasEncryptedAttributes
{
    protected $encryptionService;

    /**
     * Initialize the encryption service
     */
    public function initializeEncryptionService(): void
    {
        if (! $this->encryptionService) {
            $this->encryptionService = app(EncryptionService::class);
        }
    }

    /**
     * Get an encrypted attribute
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        // If this attribute should be encrypted and we have a value
        if ($this->shouldEncryptAttribute($key) && $value !== NULL) {
            $this->initializeEncryptionService();

            // Handle JSON encrypted data
            if ($this->isJsonEncryptedAttribute($key)) {
                return $this->encryptionService->decryptJsonData($value);
            }

            // Handle regular encrypted data
            return $this->encryptionService->decrypt($value);
        }

        return $value;
    }

    /**
     * Set an encrypted attribute
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // If this attribute should be encrypted
        if ($this->shouldEncryptAttribute($key) && $value !== NULL) {
            $this->initializeEncryptionService();

            // Handle JSON encrypted data
            if ($this->isJsonEncryptedAttribute($key)) {
                $value = $this->encryptionService->encryptJsonData($value);
            } else {
                // Handle regular encrypted data
                $value = $this->encryptionService->encrypt($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Create a searchable hash for encrypted data (useful for searching)
     *
     * @param mixed $value
     */
    public function createSearchableHash(string $attribute, $value): string
    {
        $this->initializeEncryptionService();

        return $this->encryptionService->generateSearchableHash($value);
    }

    /**
     * Get decrypted attributes for a given set of fields
     */
    public function getDecryptedAttributes(?array $fields = NULL): array
    {
        $fields ??= $this->getEncryptedAttributes();
        $decrypted = [];

        foreach ($fields as $field) {
            if ($this->hasAttribute($field)) {
                $decrypted[$field] = $this->getAttribute($field);
            }
        }

        return $decrypted;
    }

    /**
     * Rotate encryption for this model's encrypted fields
     * Useful when rotating encryption keys
     */
    public function rotateEncryption(?string $oldKey = NULL): bool
    {
        $this->initializeEncryptionService();
        $encryptedFields = $this->getEncryptedAttributes();
        $rotated = FALSE;

        foreach ($encryptedFields as $field) {
            if ($this->hasAttribute($field)) {
                $oldValue = $this->getOriginal($field);
                if ($oldValue) {
                    $newValue = $this->encryptionService->rotateEncryption($oldValue, $oldKey);
                    if ($newValue !== NULL) {
                        $this->attributes[$field] = $newValue;
                        $rotated = TRUE;
                    }
                }
            }
        }

        if ($rotated) {
            return $this->save();
        }

        return TRUE;
    }

    /**
     * Define which attributes should be encrypted
     * Override this in your model to specify encrypted fields
     */
    abstract protected function getEncryptedAttributes(): array;

    /**
     * Boot the trait
     */
    protected static function bootHasEncryptedAttributes(): void
    {
        // Ensure encryption service is available after model is constructed
        static::retrieved(function ($model): void {
            $model->initializeEncryptionService();
        });

        static::creating(function ($model): void {
            $model->initializeEncryptionService();
        });

        static::updating(function ($model): void {
            $model->initializeEncryptionService();
        });
    }

    /**
     * Check if an attribute should be encrypted
     */
    protected function shouldEncryptAttribute(string $key): bool
    {
        return in_array($key, $this->getEncryptedAttributes(), TRUE);
    }

    /**
     * Check if an attribute should be encrypted as JSON
     */
    protected function isJsonEncryptedAttribute(string $key): bool
    {
        $casts = $this->getCasts();

        return isset($casts[$key])
               && (
                   $casts[$key] === 'encrypted:array'
                   || $casts[$key] === 'encrypted:json'
                   || $casts[$key] === 'encrypted:object'
               );
    }

    /**
     * Check if the model has an attribute
     */
    protected function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes);
    }
}
