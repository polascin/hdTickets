<?php

namespace App\Traits;

use App\Services\EncryptionService;
use Illuminate\Database\Eloquent\Model;

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
     * Define which attributes should be encrypted
     * Override this in your model to specify encrypted fields
     *
     * @return array
     */
    abstract protected function getEncryptedAttributes(): array;

    /**
     * Boot the trait
     */
    protected static function bootHasEncryptedAttributes()
    {
        // Ensure encryption service is available after model is constructed
        static::retrieved(function ($model) {
            $model->initializeEncryptionService();
        });

        static::creating(function ($model) {
            $model->initializeEncryptionService();
        });

        static::updating(function ($model) {
            $model->initializeEncryptionService();
        });
    }

    /**
     * Initialize the encryption service
     */
    public function initializeEncryptionService()
    {
        if (!$this->encryptionService) {
            $this->encryptionService = app(EncryptionService::class);
        }
    }

    /**
     * Get an encrypted attribute
     *
     * @param string $key
     * @return mixed
     */
    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        // If this attribute should be encrypted and we have a value
        if ($this->shouldEncryptAttribute($key) && $value !== null) {
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
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // If this attribute should be encrypted
        if ($this->shouldEncryptAttribute($key) && $value !== null) {
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
     * Check if an attribute should be encrypted
     *
     * @param string $key
     * @return bool
     */
    protected function shouldEncryptAttribute(string $key): bool
    {
        return in_array($key, $this->getEncryptedAttributes());
    }

    /**
     * Check if an attribute should be encrypted as JSON
     *
     * @param string $key
     * @return bool
     */
    protected function isJsonEncryptedAttribute(string $key): bool
    {
        $casts = $this->getCasts();
        return isset($casts[$key]) && 
               (
                   $casts[$key] === 'encrypted:array' || 
                   $casts[$key] === 'encrypted:json' ||
                   $casts[$key] === 'encrypted:object'
               );
    }

    /**
     * Create a searchable hash for encrypted data (useful for searching)
     *
     * @param string $attribute
     * @param mixed $value
     * @return string
     */
    public function createSearchableHash(string $attribute, $value): string
    {
        $this->initializeEncryptionService();
        return $this->encryptionService->generateSearchableHash($value);
    }

    /**
     * Get decrypted attributes for a given set of fields
     *
     * @param array $fields
     * @return array
     */
    public function getDecryptedAttributes(array $fields = null): array
    {
        $fields = $fields ?? $this->getEncryptedAttributes();
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
     *
     * @param string|null $oldKey
     * @return bool
     */
    public function rotateEncryption(?string $oldKey = null): bool
    {
        $this->initializeEncryptionService();
        $encryptedFields = $this->getEncryptedAttributes();
        $rotated = false;

        foreach ($encryptedFields as $field) {
            if ($this->hasAttribute($field)) {
                $oldValue = $this->getOriginal($field);
                if ($oldValue) {
                    $newValue = $this->encryptionService->rotateEncryption($oldValue, $oldKey);
                    if ($newValue !== null) {
                        $this->attributes[$field] = $newValue;
                        $rotated = true;
                    }
                }
            }
        }

        if ($rotated) {
            return $this->save();
        }

        return true;
    }

    /**
     * Check if the model has an attribute
     *
     * @param string $attribute
     * @return bool
     */
    protected function hasAttribute(string $attribute): bool
    {
        return array_key_exists($attribute, $this->attributes);
    }
}
