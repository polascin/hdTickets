<?php declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\EncryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

use function gettype;
use function in_array;
use function is_array;
use function strlen;

/**
 * AES-256 Encryption Service for Sensitive Data
 *
 * This service provides secure encryption/decryption for sensitive user and financial data
 * using AES-256-CBC encryption with authenticated encryption.
 */
class EncryptionService
{
    /** Sensitive data types that require encryption */
    public const SENSITIVE_FIELDS = [
        // User sensitive data
        'email',
        'phone_number',
        'payment_details',
        'api_credentials',
        'session_tokens',

        // Financial data
        'transaction_id',
        'confirmation_number',
        'payment_info',
        'credit_card_info',

        // Authentication data
        'two_factor_secret',
        'backup_codes',
        'oauth_tokens',

        // Personal data
        'address',
        'personal_notes',
    ];

    /**
     * Encrypt sensitive data using AES-256
     *
     * @param mixed $value     The value to encrypt
     * @param bool  $serialize Whether to serialize the value before encryption
     *
     * @return string|null Encrypted value or null if encryption fails
     */
    /**
     * Encrypt
     *
     * @param mixed $value
     */
    public function encrypt($value, bool $serialize = FALSE): ?string
    {
        if ($value === NULL || $value === '') {
            return NULL;
        }

        try {
            // Serialize if requested (useful for arrays/objects)
            if ($serialize) {
                $value = serialize($value);
            }

            // Use Laravel's Crypt facade which implements AES-256-CBC with HMAC-SHA256
            return Crypt::encrypt($value);
        } catch (EncryptException $e) {
            Log::error('Encryption failed', [
                'error' => $e->getMessage(),
                'type'  => gettype($value),
            ]);

            return NULL;
        }
    }

    /**
     * Decrypt sensitive data
     *
     * @param string|null $encryptedValue The encrypted value
     * @param bool        $unserialize    Whether to unserialize after decryption
     *
     * @return mixed Decrypted value or null if decryption fails
     */
    public function decrypt(?string $encryptedValue, bool $unserialize = FALSE)
    {
        if ($encryptedValue === NULL || $encryptedValue === '') {
            return NULL;
        }

        try {
            $decrypted = Crypt::decrypt($encryptedValue);

            // Unserialize if requested
            if ($unserialize) {
                return unserialize($decrypted);
            }

            return $decrypted;
        } catch (DecryptException $e) {
            Log::error('Decryption failed', [
                'error'            => $e->getMessage(),
                'encrypted_length' => strlen($encryptedValue),
            ]);

            return NULL;
        }
    }

    /**
     * Encrypt an array of sensitive data
     *
     * @param array<string, mixed> $data            Array of data to encrypt
     * @param array<string, mixed> $fieldsToEncrypt Specific fields to encrypt (optional)
     *
     * @return array Array with encrypted sensitive fields
     */
    /**
     * EncryptArray
     */
    public function encryptArray(array $data, ?array $fieldsToEncrypt = NULL): array
    {
        $fieldsToEncrypt ??= self::SENSITIVE_FIELDS;
        $encrypted = $data;

        foreach ($fieldsToEncrypt as $field) {
            if (isset($data[$field]) && $data[$field] !== NULL) {
                $encrypted[$field] = $this->encrypt($data[$field]);
            }
        }

        return $encrypted;
    }

    /**
     * Decrypt an array of encrypted data
     *
     * @param array<string, mixed> $encryptedData   Array with encrypted fields
     * @param array<string, mixed> $fieldsToDecrypt Specific fields to decrypt (optional)
     *
     * @return array Array with decrypted fields
     */
    /**
     * DecryptArray
     */
    public function decryptArray(array $encryptedData, ?array $fieldsToDecrypt = NULL): array
    {
        $fieldsToDecrypt ??= self::SENSITIVE_FIELDS;
        $decrypted = $encryptedData;

        foreach ($fieldsToDecrypt as $field) {
            if (isset($encryptedData[$field]) && $encryptedData[$field] !== NULL) {
                $decrypted[$field] = $this->decrypt($encryptedData[$field]);
            }
        }

        return $decrypted;
    }

    /**
     * Check if a field is considered sensitive
     */
    /**
     * Check if  sensitive field
     */
    public function isSensitiveField(string $fieldName): bool
    {
        return in_array($fieldName, self::SENSITIVE_FIELDS, TRUE);
    }

    /**
     * Encrypt JSON data while preserving structure
     *
     * @return string Encrypted JSON string
     */
    /**
     * EncryptJsonData
     */
    public function encryptJsonData(array $jsonData): ?string
    {
        return $this->encrypt($jsonData, TRUE);
    }

    /**
     * Decrypt JSON data and restore structure
     */
    /**
     * DecryptJsonData
     */
    public function decryptJsonData(?string $encryptedJson): ?array
    {
        $decrypted = $this->decrypt($encryptedJson, TRUE);

        return is_array($decrypted) ? $decrypted : NULL;
    }

    /**
     * Generate a secure hash for sensitive data (for indexing/searching)
     * This creates a searchable hash without exposing the original data
     */
    /**
     * GenerateSearchableHash
     */
    public function generateSearchableHash(string $value): string
    {
        // Use HMAC with app key for consistent, secure hashing
        return hash_hmac('sha256', strtolower(trim($value)), config('app.key'));
    }

    /**
     * Rotate encryption for existing data (useful for key rotation)
     *
     * @param string $oldKey (if different from current)
     *
     * @return string|null Re-encrypted value with current key
     */
    /**
     * RotateEncryption
     */
    public function rotateEncryption(string $oldEncryptedValue, ?string $oldKey = NULL): ?string
    {
        try {
            // Temporarily switch key if provided
            if ($oldKey) {
                $originalKey = config('app.key');
                config(['app.key' => $oldKey]);
                $decrypted = $this->decrypt($oldEncryptedValue);
                config(['app.key' => $originalKey]);
            } else {
                $decrypted = $this->decrypt($oldEncryptedValue);
            }

            if ($decrypted === NULL) {
                return NULL;
            }

            return $this->encrypt($decrypted);
        } catch (Exception $e) {
            Log::error('Encryption rotation failed', [
                'error' => $e->getMessage(),
            ]);

            return NULL;
        }
    }
}
