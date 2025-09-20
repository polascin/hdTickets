<?php declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function is_array;
use function is_bool;
use function is_int;
use function is_string;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'key',
        'value',
        'type',
    ];

    /**
     * Get the user that owns this preference
     */
    /**
     * User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific preference key
     *
     * @param mixed $query
     */
    public function scopeForKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope for specific category
     *
     * @param mixed $query
     */
    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get user preference value with default
     *
     * @param mixed|null $default
     */
    public static function getValue(int $userId, string $category, string $key, $default = NULL)
    {
        $preference = static::where('user_id', $userId)
            ->where('category', $category)
            ->where('key', $key)
            ->first();

        if (!$preference) {
            return $default;
        }

        // Cast value based on data type
        return self::castValue($preference->value, $preference->type);
    }

    /**
     * Set user preference value
     *
     * @param mixed $value
     */
    /**
     * Set  value
     *
     * @param mixed $value
     */
    public static function setValue(int $userId, string $category, string $key, $value, string $dataType = 'string'): void
    {
        static::updateOrCreate(
            [
                'user_id'  => $userId,
                'category' => $category,
                'key'      => $key,
            ],
            [
                'value' => self::processValue($value, $dataType),
                'type'  => $dataType,
            ],
        );
    }

    /**
     * Get all preferences for a user by category
     */
    /**
     * Get  by category
     */
    public static function getByCategory(int $userId, string $category): array
    {
        return static::where('user_id', $userId)
            ->where('category', $category)
            ->get()
            ->mapWithKeys(fn ($pref): array => [$pref->key => self::castValue($pref->value, $pref->type)])
            ->toArray();
    }

    /**
     * Get default preference structure
     */
    /**
     * Get  default preferences
     */
    public static function getDefaultPreferences(): array
    {
        return [
            'notification_channels' => [
                'critical' => 'slack',
                'high'     => 'discord',
                'medium'   => 'telegram',
                'normal'   => 'push',
                'disabled' => [],
            ],
            'favorite_teams'   => [],
            'preferred_venues' => [],
            'event_types'      => [
                'concert' => 3,
                'sports'  => 4,
                'theater' => 2,
                'comedy'  => 2,
            ],
            'alert_timing' => [
                'quiet_hours_start' => '23:00',
                'quiet_hours_end'   => '07:00',
                'timezone'          => 'UTC',
            ],
            'price_thresholds' => [
                'max_budget'                  => 500,
                'significant_drop_percentage' => 20,
                'price_alert_threshold'       => 10,
            ],
            'ml_settings' => [
                'enable_predictions'              => TRUE,
                'prediction_confidence_threshold' => 0.7,
                'enable_recommendations'          => TRUE,
            ],
            'escalation_settings' => [
                'enable_escalation'        => TRUE,
                'emergency_contact_phone'  => NULL,
                'emergency_contact_email'  => NULL,
                'escalation_delay_minutes' => 5,
            ],
        ];
    }

    /**
     * Initialize default preferences for a user
     */
    /**
     * InitializeDefaults
     */
    public static function initializeDefaults(int $userId): void
    {
        $defaults = static::getDefaultPreferences();

        foreach ($defaults as $key => $value) {
            static::setValue($userId, static::getCategoryForKey($key), $key, $value, 'json');
        }
    }

    /**
     * Validate preference value based on key
     *
     * @param mixed $value
     */
    /**
     * ValidatePreference
     *
     * @param mixed $value
     */
    public static function validatePreference(string $key, $value): bool
    {
        return match ($key) {
            'notification_channels' => is_array($value)
                   && isset($value['critical'], $value['high'], $value['medium'], $value['normal']),
            'favorite_teams', 'preferred_venues' => is_array($value),
            'event_types' => is_array($value)
                   && collect($value)->every(fn ($priority): bool => is_int($priority) && $priority >= 1 && $priority <= 5),
            'alert_timing' => is_array($value)
                   && isset($value['quiet_hours_start'], $value['quiet_hours_end'], $value['timezone']),
            'price_thresholds' => is_array($value)
                   && isset($value['max_budget'])
                   && is_numeric($value['max_budget'])
                   && $value['max_budget'] > 0,
            'ml_settings' => is_array($value)
                   && isset($value['enable_predictions'])
                   && is_bool($value['enable_predictions']),
            'escalation_settings' => is_array($value)
                   && isset($value['enable_escalation'])
                   && is_bool($value['enable_escalation']),
            default => TRUE,
        };
    }

    /**
     * Get user's notification preferences
     */
    /**
     * Get  notification preferences
     */
    public static function getNotificationPreferences(int $userId): array
    {
        $channels = static::getValue($userId, 'notifications', 'notification_channels', []);
        $timing = static::getValue($userId, 'notifications', 'alert_timing', []);
        $escalation = static::getValue($userId, 'notifications', 'escalation_settings', []);

        return array_merge($channels, $timing, $escalation);
    }

    /**
     * Get user's alert preferences
     */
    /**
     * Get  alert preferences
     */
    public static function getAlertPreferences(int $userId): array
    {
        return [
            'favorite_teams'   => static::getValue($userId, 'preferences', 'favorite_teams', []),
            'preferred_venues' => static::getValue($userId, 'preferences', 'preferred_venues', []),
            'event_types'      => static::getValue($userId, 'preferences', 'event_types', []),
            'price_thresholds' => static::getValue($userId, 'alerts', 'price_thresholds', []),
            'ml_settings'      => static::getValue($userId, 'system', 'ml_settings', []),
        ];
    }

    /**
     * Update multiple preferences at once
     */
    /**
     * UpdateMultiple
     */
    public static function updateMultiple(int $userId, array $preferences): array
    {
        $updated = [];
        $errors = [];

        foreach ($preferences as $key => $value) {
            if (static::validatePreference($key, $value)) {
                static::setValue($userId, static::getCategoryForKey($key), $key, $value, 'json');
                $updated[] = $key;
            } else {
                $errors[] = "Invalid value for preference: {$key}";
            }
        }

        return [
            'updated' => $updated,
            'errors'  => $errors,
        ];
    }

    /**
     * Reset preferences to defaults
     */
    /**
     * ResetToDefaults
     */
    public static function resetToDefaults(int $userId, ?array $keys = NULL): void
    {
        $defaults = static::getDefaultPreferences();
        $keysToReset = $keys ?? array_keys($defaults);

        foreach ($keysToReset as $key) {
            if (isset($defaults[$key])) {
                static::setValue($userId, static::getCategoryForKey($key), $key, $defaults[$key], 'json');
            }
        }
    }

    /**
     * Export user preferences
     */
    /**
     * ExportPreferences
     */
    public static function exportPreferences(int $userId): array
    {
        return static::where('user_id', $userId)
            ->get()
            ->mapWithKeys(fn ($preference): array => [$preference->key => [
                'value'      => $preference->value,
                'type'       => $preference->type,
                'category'   => $preference->category,
                'updated_at' => $preference->updated_at,
            ]])
            ->toArray();
    }

    /**
     * Import user preferences
     */
    /**
     * ImportPreferences
     */
    public static function importPreferences(int $userId, array $preferences): array
    {
        $imported = [];
        $errors = [];

        foreach ($preferences as $key => $data) {
            try {
                if (is_array($data) && isset($data['value'])) {
                    $value = $data['value'];
                    $type = $data['type'] ?? 'json';
                    $category = $data['category'] ?? 'general';
                } else {
                    $value = $data;
                    $type = 'json';
                    $category = static::getCategoryForKey($key);
                }

                if (static::validatePreference($key, $value)) {
                    static::setValue($userId, $category, $key, $value, $type);
                    $imported[] = $key;
                } else {
                    $errors[] = "Invalid preference data for: {$key}";
                }
            } catch (Exception $e) {
                $errors[] = "Error importing preference {$key}: " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors'   => $errors,
        ];
    }

    /**
     * Get category for a preference key
     */
    /**
     * Get  category for key
     */
    protected static function getCategoryForKey(string $key): string
    {
        $categoryMap = [
            'notification_channels' => 'notifications',
            'favorite_teams'        => 'preferences',
            'preferred_venues'      => 'preferences',
            'event_types'           => 'preferences',
            'alert_timing'          => 'notifications',
            'price_thresholds'      => 'alerts',
            'ml_settings'           => 'system',
            'escalation_settings'   => 'notifications',
        ];

        return $categoryMap[$key] ?? 'general';
    }

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    /**
     * Process value for storage based on data type
     *
     * @param mixed $value
     */
    private static function processValue($value, string $dataType)
    {
        switch ($dataType) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL && (bool) $value;
            case 'integer':
                return is_numeric($value) ? (int) $value : 0;
            case 'array':
            case 'json':
                if (is_string($value)) {
                    $decoded = json_decode($value, TRUE);

                    return json_last_error() === JSON_ERROR_NONE ? json_encode($decoded) : $value;
                }

                return json_encode($value);
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * Cast value from storage based on data type
     *
     * @param mixed $value
     */
    private static function castValue($value, string $dataType)
    {
        return match ($dataType) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'array', 'json' => json_decode((string) $value, TRUE),
            default => (string) $value,
        };
    }
}
