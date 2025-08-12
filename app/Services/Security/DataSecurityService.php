<?php declare(strict_types=1);

namespace App\Services\Security;

use App\Services\SecurityService;
use DB;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

use function count;
use function dirname;
use function is_array;
use function strlen;

class DataSecurityService
{
    /** Field classification levels */
    public const CLASSIFICATION_LEVELS = [
        'public'       => 0,
        'internal'     => 1,
        'confidential' => 2,
        'restricted'   => 3,
        'secret'       => 4,
    ];

    /** Field encryption configurations */
    public const FIELD_CONFIGS = [
        // User sensitive data
        'users.email' => [
            'classification'    => 'confidential',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
        ],
        'users.phone' => [
            'classification'    => 'confidential',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
        ],
        'users.two_factor_secret' => [
            'classification'    => 'secret',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
        ],
        'users.two_factor_recovery_codes' => [
            'classification'    => 'secret',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
        ],
        'users.password' => [
            'classification'    => 'secret',
            'encryption_method' => 'hash_only',
            'key_rotation'      => FALSE,
            'audit_access'      => TRUE,
        ],
        'users.api_keys' => [
            'classification'    => 'secret',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
        ],

        // Payment data
        'payments.card_number' => [
            'classification'    => 'secret',
            'encryption_method' => 'aes256',
            'key_rotation'      => TRUE,
            'audit_access'      => TRUE,
            'tokenization'      => TRUE,
        ],
        'payments.cvv' => [
            'classification'    => 'secret',
            'encryption_method' => 'ephemeral',
            'key_rotation'      => FALSE,
            'audit_access'      => TRUE,
        ],

        // Ticket purchase data
        'tickets.purchase_data' => [
            'classification'    => 'confidential',
            'encryption_method' => 'aes256',
            'key_rotation'      => FALSE,
            'audit_access'      => TRUE,
        ],

        // System logs
        'logs.sensitive_data' => [
            'classification'    => 'confidential',
            'encryption_method' => 'aes256',
            'key_rotation'      => FALSE,
            'audit_access'      => TRUE,
            'auto_purge_days'   => 90,
        ],
    ];

    protected $securityService;

    protected $fieldEncrypter;

    protected $keyManager;

    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
        $this->initializeEncryption();
    }

    /**
     * Encrypt sensitive field data
     *
     * @param mixed $value
     */
    public function encryptField(string $fieldName, $value, array $options = []): string
    {
        if (empty($value)) {
            return $value;
        }

        $config = $this->getFieldConfig($fieldName);

        // Log field access for auditing
        if ($config['audit_access']) {
            $this->auditFieldAccess($fieldName, 'encrypt', $options);
        }

        switch ($config['encryption_method']) {
            case 'aes256':
                return $this->encryptWithAES256($fieldName, $value, $config);
            case 'ephemeral':
                return $this->encryptEphemeral($value);
            case 'tokenization':
                return $this->tokenizeData($fieldName, $value);
            default:
                throw new InvalidArgumentException("Unsupported encryption method: {$config['encryption_method']}");
        }
    }

    /**
     * Decrypt sensitive field data
     */
    public function decryptField(string $fieldName, string $encryptedValue, array $options = []): string
    {
        if (empty($encryptedValue)) {
            return $encryptedValue;
        }

        $config = $this->getFieldConfig($fieldName);

        // Log field access for auditing
        if ($config['audit_access']) {
            $this->auditFieldAccess($fieldName, 'decrypt', $options);
        }

        try {
            switch ($config['encryption_method']) {
                case 'aes256':
                    return $this->decryptWithAES256($fieldName, $encryptedValue, $config);
                case 'ephemeral':
                    return $this->decryptEphemeral($encryptedValue);
                case 'tokenization':
                    return $this->detokenizeData($fieldName, $encryptedValue);
                default:
                    throw new InvalidArgumentException("Unsupported decryption method: {$config['encryption_method']}");
            }
        } catch (DecryptException $e) {
            $this->handleDecryptionError($fieldName, $e, $options);

            return '[DECRYPTION_ERROR]';
        }
    }

    /**
     * Mask sensitive data for logging
     *
     * @param mixed $value
     */
    public function maskForLog(string $fieldName, $value): string
    {
        if (empty($value)) {
            return $value;
        }

        $config = $this->getFieldConfig($fieldName);
        $classification = $config['classification'];

        switch ($classification) {
            case 'secret':
                return '[REDACTED]';
            case 'restricted':
                return $this->partialMask($value, 0.8);
            case 'confidential':
                return $this->partialMask($value, 0.6);
            case 'internal':
                return $this->partialMask($value, 0.3);
            default:
                return $value;
        }
    }

    /**
     * Secure key rotation
     */
    public function rotateEncryptionKeys(): array
    {
        $results = [];

        foreach (self::FIELD_CONFIGS as $fieldName => $config) {
            if ($config['key_rotation']) {
                try {
                    $result = $this->rotateFieldKey($fieldName);
                    $results[$fieldName] = $result;
                } catch (Exception $e) {
                    $results[$fieldName] = ['success' => FALSE, 'error' => $e->getMessage()];
                    Log::error("Key rotation failed for field {$fieldName}", ['error' => $e->getMessage()]);
                }
            }
        }

        // Log key rotation activity
        $this->securityService->logSecurityActivity('Encryption keys rotated', [
            'fields_processed'     => count($results),
            'successful_rotations' => count(array_filter($results, fn ($r) => $r['success'] ?? FALSE)),
        ]);

        return $results;
    }

    /**
     * Database encryption at rest
     */
    public function configureTableEncryption(string $table, array $encryptedColumns): bool
    {
        // This would typically involve database-specific commands
        // For MySQL/MariaDB, this might involve:
        // ALTER TABLE {$table} ENCRYPTION='Y';

        $encryptionSql = "ALTER TABLE {$table} ENCRYPTION='Y'";

        try {
            DB::statement($encryptionSql);

            // Store encrypted column configuration
            Cache::put("table_encryption:{$table}", $encryptedColumns, now()->addYears(1));

            $this->securityService->logSecurityActivity('Table encryption enabled', [
                'table'             => $table,
                'encrypted_columns' => $encryptedColumns,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error("Failed to enable table encryption for {$table}", ['error' => $e->getMessage()]);

            return FALSE;
        }
    }

    /**
     * Create secure backup with encryption
     */
    public function createSecureBackup(array $tables = [], array $options = []): array
    {
        $backupId = Str::uuid();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupPath = storage_path("backups/secure_backup_{$timestamp}_{$backupId}");

        // Create backup directory
        if (! is_dir(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0o755, TRUE);
        }

        $results = [
            'backup_id' => $backupId,
            'timestamp' => $timestamp,
            'tables'    => [],
            'encrypted' => TRUE,
        ];

        foreach ($tables as $table) {
            try {
                $tableBackupPath = "{$backupPath}/{$table}.sql.enc";
                $this->createEncryptedTableBackup($table, $tableBackupPath);
                $results['tables'][$table] = ['success' => TRUE, 'path' => $tableBackupPath];
            } catch (Exception $e) {
                $results['tables'][$table] = ['success' => FALSE, 'error' => $e->getMessage()];
            }
        }

        // Create backup manifest
        $manifest = [
            'backup_id'              => $backupId,
            'created_at'             => now()->toISOString(),
            'tables'                 => $results['tables'],
            'encryption_key_version' => $this->getCurrentKeyVersion(),
            'checksum'               => $this->calculateBackupChecksum($backupPath),
        ];

        file_put_contents("{$backupPath}/manifest.json", json_encode($manifest, JSON_PRETTY_PRINT));

        // Log backup creation
        $this->securityService->logSecurityActivity('Secure backup created', [
            'backup_id'     => $backupId,
            'tables_count'  => count($tables),
            'success_count' => count(array_filter($results['tables'], fn ($r) => $r['success'])),
        ]);

        return $results;
    }

    /**
     * Validate data integrity
     */
    public function validateDataIntegrity(string $table, array $columns = []): array
    {
        $results = [
            'table'               => $table,
            'total_rows'          => 0,
            'corrupted_rows'      => 0,
            'decryption_errors'   => 0,
            'checksum_mismatches' => 0,
        ];

        $query = DB::table($table);
        if (! empty($columns)) {
            $query->select(array_merge(['id'], $columns));
        }

        $rows = $query->get();
        $results['total_rows'] = $rows->count();

        foreach ($rows as $row) {
            foreach ($columns as $column) {
                if (! empty($row->$column)) {
                    try {
                        $decrypted = $this->decryptField("{$table}.{$column}", $row->$column);
                        if ($decrypted === '[DECRYPTION_ERROR]') {
                            $results['decryption_errors']++;
                        }
                    } catch (Exception $e) {
                        $results['corrupted_rows']++;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Setup data retention policies
     */
    public function setupDataRetention(): void
    {
        foreach (self::FIELD_CONFIGS as $fieldName => $config) {
            if (isset($config['auto_purge_days'])) {
                $this->scheduleDataPurge($fieldName, $config['auto_purge_days']);
            }
        }
    }

    /**
     * Initialize encryption components
     */
    protected function initializeEncryption(): void
    {
        // Initialize field-level encrypter with dedicated key
        $fieldKey = $this->getOrCreateFieldEncryptionKey();
        $this->fieldEncrypter = new Encrypter($fieldKey, 'AES-256-CBC');

        // Initialize key manager
        $this->keyManager = new EncryptionKeyManager();
    }

    /**
     * Get field configuration
     */
    protected function getFieldConfig(string $fieldName): array
    {
        return self::FIELD_CONFIGS[$fieldName] ?? [
            'classification'    => 'internal',
            'encryption_method' => 'aes256',
            'key_rotation'      => FALSE,
            'audit_access'      => FALSE,
        ];
    }

    /**
     * Encrypt with AES-256
     *
     * @param mixed $value
     */
    protected function encryptWithAES256(string $fieldName, $value, array $config): string
    {
        $key = $this->getFieldEncryptionKey($fieldName);
        $encrypter = new Encrypter($key, 'AES-256-CBC');

        $encrypted = $encrypter->encrypt($value);

        // Add metadata for key rotation
        if ($config['key_rotation']) {
            $metadata = [
                'key_version'  => $this->getCurrentKeyVersion($fieldName),
                'encrypted_at' => time(),
            ];

            return base64_encode(json_encode(['data' => $encrypted, 'meta' => $metadata]));
        }

        return $encrypted;
    }

    /**
     * Decrypt with AES-256
     */
    protected function decryptWithAES256(string $fieldName, string $encryptedValue, array $config): string
    {
        if ($config['key_rotation']) {
            $payload = json_decode(base64_decode($encryptedValue, TRUE), TRUE);
            if (is_array($payload) && isset($payload['data'], $payload['meta'])) {
                $key = $this->getFieldEncryptionKey($fieldName, $payload['meta']['key_version'] ?? NULL);
                $encrypter = new Encrypter($key, 'AES-256-CBC');

                return $encrypter->decrypt($payload['data']);
            }
        }

        $key = $this->getFieldEncryptionKey($fieldName);
        $encrypter = new Encrypter($key, 'AES-256-CBC');

        return $encrypter->decrypt($encryptedValue);
    }

    /**
     * Encrypt ephemeral data (not persisted)
     *
     * @param mixed $value
     */
    protected function encryptEphemeral($value): string
    {
        return Crypt::encrypt($value);
    }

    /**
     * Decrypt ephemeral data
     */
    protected function decryptEphemeral(string $encryptedValue): string
    {
        return Crypt::decrypt($encryptedValue);
    }

    /**
     * Tokenize sensitive data
     *
     * @param mixed $value
     */
    protected function tokenizeData(string $fieldName, $value): string
    {
        $token = 'tok_' . Str::random(32);

        // Store token mapping securely
        Cache::put("token:{$token}", $this->encryptWithAES256($fieldName, $value, ['key_rotation' => FALSE]), now()->addYears(5));

        return $token;
    }

    /**
     * Detokenize data
     */
    protected function detokenizeData(string $fieldName, string $token): string
    {
        $encryptedValue = Cache::get("token:{$token}");
        if (! $encryptedValue) {
            throw new DecryptException('Token not found or expired');
        }

        return $this->decryptWithAES256($fieldName, $encryptedValue, ['key_rotation' => FALSE]);
    }

    /**
     * Partial data masking
     */
    protected function partialMask(string $value, float $maskRatio): string
    {
        $length = strlen($value);
        $maskLength = (int) ($length * $maskRatio);
        $visibleLength = $length - $maskLength;

        if ($visibleLength <= 2) {
            return str_repeat('*', $length);
        }

        $keepStart = (int) ($visibleLength / 2);
        $keepEnd = $visibleLength - $keepStart;

        return substr($value, 0, $keepStart) . str_repeat('*', $maskLength) . substr($value, -$keepEnd);
    }

    /**
     * Get or create field encryption key
     */
    protected function getOrCreateFieldEncryptionKey(): string
    {
        $key = Cache::get('field_encryption_key');

        if (! $key) {
            $key = base64_encode(random_bytes(32)); // 256-bit key
            Cache::put('field_encryption_key', $key, now()->addYears(1));
        }

        return base64_decode($key, TRUE);
    }

    /**
     * Get field-specific encryption key
     */
    protected function getFieldEncryptionKey(string $fieldName, ?int $version = NULL): string
    {
        $keyName = $version ? "field_key:{$fieldName}:v{$version}" : "field_key:{$fieldName}";

        $key = Cache::get($keyName);
        if (! $key) {
            $key = base64_encode(random_bytes(32));
            Cache::put($keyName, $key, now()->addYears(1));
        }

        return base64_decode($key, TRUE);
    }

    /**
     * Get current key version for field
     */
    protected function getCurrentKeyVersion(?string $fieldName = NULL): int
    {
        $versionKey = $fieldName ? "key_version:{$fieldName}" : 'global_key_version';

        return Cache::get($versionKey, 1);
    }

    /**
     * Rotate field encryption key
     */
    protected function rotateFieldKey(string $fieldName): array
    {
        $currentVersion = $this->getCurrentKeyVersion($fieldName);
        $newVersion = $currentVersion + 1;

        // Generate new key
        $newKey = base64_encode(random_bytes(32));
        Cache::put("field_key:{$fieldName}:v{$newVersion}", $newKey, now()->addYears(1));
        Cache::put("key_version:{$fieldName}", $newVersion, now()->addYears(1));

        return [
            'success'     => TRUE,
            'old_version' => $currentVersion,
            'new_version' => $newVersion,
            'rotated_at'  => now()->toISOString(),
        ];
    }

    /**
     * Audit field access
     */
    protected function auditFieldAccess(string $fieldName, string $operation, array $context): void
    {
        $this->securityService->logSecurityActivity("Field {$operation}", [
            'field_name'     => $fieldName,
            'operation'      => $operation,
            'context'        => $context,
            'classification' => $this->getFieldConfig($fieldName)['classification'],
        ]);
    }

    /**
     * Handle decryption errors
     */
    protected function handleDecryptionError(string $fieldName, Exception $error, array $options): void
    {
        Log::error("Decryption error for field {$fieldName}", [
            'error'   => $error->getMessage(),
            'field'   => $fieldName,
            'options' => $options,
        ]);

        $this->securityService->logSecurityActivity('Decryption error', [
            'field_name'    => $fieldName,
            'error_message' => $error->getMessage(),
            'risk_level'    => 'high',
        ]);
    }

    /**
     * Create encrypted table backup
     */
    protected function createEncryptedTableBackup(string $table, string $outputPath): void
    {
        $backupSql = DB::select("SELECT * FROM {$table}");
        $encryptedData = $this->fieldEncrypter->encrypt(json_encode($backupSql));
        file_put_contents($outputPath, $encryptedData);
    }

    /**
     * Calculate backup checksum
     */
    protected function calculateBackupChecksum(string $backupPath): string
    {
        $files = glob("{$backupPath}/*");
        $checksums = [];

        foreach ($files as $file) {
            $checksums[] = hash_file('sha256', $file);
        }

        return hash('sha256', implode('', $checksums));
    }

    /**
     * Schedule data purge based on retention policy
     */
    protected function scheduleDataPurge(string $fieldName, int $days): void
    {
        // This would typically integrate with a job scheduler
        // For now, we'll just log the scheduled purge
        Log::info("Data purge scheduled for field {$fieldName} after {$days} days");
    }
}

/**
 * Encryption Key Manager
 */
class EncryptionKeyManager
{
    public function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    public function rotateKey(string $keyName): array
    {
        $oldKey = Cache::get($keyName);
        $newKey = $this->generateKey();

        // Store new key
        Cache::put($keyName, $newKey, now()->addYears(1));

        // Archive old key for potential data recovery
        if ($oldKey) {
            Cache::put("{$keyName}_archived_" . time(), $oldKey, now()->addYears(2));
        }

        return [
            'key_name'         => $keyName,
            'rotated_at'       => now()->toISOString(),
            'old_key_archived' => $oldKey !== NULL,
        ];
    }
}
