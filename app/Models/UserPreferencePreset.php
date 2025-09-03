<?php declare(strict_types=1);

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

use function count;
use function in_array;
use function is_array;
use function is_string;

class UserPreferencePreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'preference_data',
        'is_system_preset',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'preference_data'  => 'json',
        'is_system_preset' => 'boolean',
        'is_active'        => 'boolean',
    ];

    /**
     * Get the user who created this preset
     */
    /**
     * Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for system presets only
     *
     * @param mixed $query
     */
    public function scopeSystemPresets($query)
    {
        return $query->where('is_system_preset', TRUE);
    }

    /**
     * Scope for user-created presets only
     *
     * @param mixed $query
     */
    public function scopeUserPresets($query)
    {
        return $query->where('is_system_preset', FALSE);
    }

    /**
     * Scope for active presets only
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
        return $query->where('is_active', TRUE);
    }

    /**
     * Scope for presets accessible to a specific user
     *
     * @param mixed $query
     */
    public function scopeAccessibleTo($query, int $userId)
    {
        return $query->where(function ($q) use ($userId): void {
            $q->where('is_system_preset', TRUE)
                ->orWhere('created_by', $userId);
        });
    }

    /**
     * Get default system presets
     */
    /**
     * Get  default presets
     */
    public static function getDefaultPresets(): array
    {
        return [
            'minimal_notifications' => [
                'name'            => 'Minimal Notifications',
                'description'     => 'Only essential notifications enabled',
                'preference_data' => [
                    'notifications' => [
                        'email_enabled'         => TRUE,
                        'email_ticket_assigned' => TRUE,
                        'email_ticket_updated'  => FALSE,
                        'email_ticket_closed'   => FALSE,
                        'push_enabled'          => FALSE,
                        'sms_enabled'           => FALSE,
                    ],
                    'display' => [
                        'theme'   => 'light',
                        'density' => 'comfortable',
                    ],
                ],
            ],
            'power_user' => [
                'name'            => 'Power User',
                'description'     => 'All features and notifications enabled',
                'preference_data' => [
                    'notifications' => [
                        'email_enabled'         => TRUE,
                        'email_ticket_assigned' => TRUE,
                        'email_ticket_updated'  => TRUE,
                        'email_ticket_closed'   => TRUE,
                        'push_enabled'          => TRUE,
                        'push_ticket_assigned'  => TRUE,
                        'push_ticket_updated'   => TRUE,
                        'push_ticket_closed'    => FALSE,
                        'sms_enabled'           => FALSE,
                    ],
                    'display' => [
                        'theme'   => 'dark',
                        'density' => 'compact',
                    ],
                    'dashboard' => [
                        'auto_refresh'     => TRUE,
                        'refresh_interval' => 15,
                        'compact_view'     => TRUE,
                    ],
                ],
            ],
            'mobile_optimized' => [
                'name'            => 'Mobile Optimized',
                'description'     => 'Settings optimized for mobile use',
                'preference_data' => [
                    'notifications' => [
                        'push_enabled'         => TRUE,
                        'push_ticket_assigned' => TRUE,
                        'email_enabled'        => FALSE,
                        'sms_enabled'          => FALSE,
                    ],
                    'display' => [
                        'theme'   => 'auto',
                        'density' => 'spacious',
                    ],
                    'dashboard' => [
                        'auto_refresh' => FALSE,
                        'compact_view' => FALSE,
                    ],
                ],
            ],
        ];
    }

    /**
     * Create default system presets
     */
    /**
     * CreateSystemPresets
     */
    public static function createSystemPresets(): void
    {
        $defaults = self::getDefaultPresets();

        foreach ($defaults as $key => $presetData) {
            self::updateOrCreate(
                [
                    'name'             => $presetData['name'],
                    'is_system_preset' => TRUE,
                ],
                [
                    'description'     => $presetData['description'],
                    'preference_data' => $presetData['preference_data'],
                    'is_active'       => TRUE,
                    'created_by'      => NULL,
                ],
            );
        }
    }

    /**
     * Create a user preset from current preferences
     */
    /**
     * CreateFromUserPreferences
     */
    public static function createFromUserPreferences(
        int $userId,
        string $name,
        ?string $description = NULL,
        ?array $categories = NULL,
    ): self {
        // Get user's current preferences
        $query = UserPreference::where('user_id', $userId);

        if ($categories) {
            $query->whereIn('category', $categories);
        }

        $preferences = $query->get()
            ->groupBy('category')
            ->map(function ($categoryPrefs) {
                return $categoryPrefs->mapWithKeys(function ($pref) {
                    return [$pref->key => [
                        'value'     => UserPreference::castValue($pref->value, $pref->data_type),
                        'data_type' => $pref->data_type,
                    ]];
                });
            });

        return self::create([
            'name'             => $name,
            'description'      => $description ?? 'Custom preset created from user preferences',
            'preference_data'  => $preferences->toArray(),
            'is_system_preset' => FALSE,
            'created_by'       => $userId,
            'is_active'        => TRUE,
        ]);
    }

    /**
     * Apply this preset to a user
     */
    /**
     * ApplyToUser
     */
    public function applyToUser(int $userId): array
    {
        $updated = [];
        $errors = [];

        $presetData = is_string($this->preference_data)
            ? json_decode($this->preference_data, TRUE)
            : $this->preference_data;

        foreach ($presetData as $category => $preferences) {
            foreach ($preferences as $key => $prefData) {
                try {
                    $value = $prefData['value'] ?? $prefData;
                    $dataType = $prefData['data_type'] ?? 'string';

                    UserPreference::updateOrCreate(
                        [
                            'user_id'  => $userId,
                            'category' => $category,
                            'key'      => $key,
                        ],
                        [
                            'value'     => UserPreference::processValue($value, $dataType),
                            'data_type' => $dataType,
                        ],
                    );

                    $updated[] = "{$category}.{$key}";
                } catch (Exception $e) {
                    $errors[] = "Error applying {$category}.{$key}: " . $e->getMessage();
                }
            }
        }

        return [
            'updated' => $updated,
            'errors'  => $errors,
        ];
    }

    /**
     * Get presets accessible to a user with usage statistics
     */
    /**
     * Get  accessible presets with stats
     */
    public static function getAccessiblePresetsWithStats(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::accessibleTo($userId)
            ->active()
            ->withCount(['applications' => function ($query): void {
                // This would require a separate table to track preset applications
                // For now, we'll just return the presets
            }])
            ->orderBy('is_system_preset', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Duplicate this preset for a user
     */
    /**
     * DuplicateForUser
     */
    public function duplicateForUser(int $userId, ?string $newName = NULL): self
    {
        return self::create([
            'name'             => $newName ?? ($this->name . ' (Copy)'),
            'description'      => $this->description,
            'preference_data'  => $this->preference_data,
            'is_system_preset' => FALSE,
            'created_by'       => $userId,
            'is_active'        => TRUE,
        ]);
    }

    /**
     * Validate preset data structure
     */
    /**
     * ValidatePresetData
     */
    public function validatePresetData(): array
    {
        $errors = [];
        $presetData = is_string($this->preference_data)
            ? json_decode($this->preference_data, TRUE)
            : $this->preference_data;

        if (! is_array($presetData)) {
            $errors[] = 'Preference data must be a valid JSON object';

            return $errors;
        }

        // Validate structure
        foreach ($presetData as $category => $preferences) {
            if (! is_array($preferences)) {
                $errors[] = "Category '{$category}' must contain an array of preferences";

                continue;
            }

            foreach ($preferences as $key => $prefData) {
                // Allow both simple values and structured data
                if (is_array($prefData)) {
                    if (! isset($prefData['value'])) {
                        $errors[] = "Preference '{$category}.{$key}' must have a 'value' field";
                    }

                    $dataType = $prefData['data_type'] ?? 'string';
                    if (! in_array($dataType, ['string', 'boolean', 'integer', 'array', 'json'], TRUE)) {
                        $errors[] = "Invalid data type '{$dataType}' for preference '{$category}.{$key}'";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Get preset summary for display
     */
    /**
     * Get  summary
     */
    public function getSummary(): array
    {
        $presetData = is_string($this->preference_data)
            ? json_decode($this->preference_data, TRUE)
            : $this->preference_data;

        $summary = [
            'categories'        => array_keys($presetData),
            'total_preferences' => 0,
            'key_features'      => [],
        ];

        foreach ($presetData as $category => $preferences) {
            $summary['total_preferences'] += count($preferences);

            // Extract some key features for display
            if ($category === 'notifications') {
                $emailEnabled = $preferences['email_enabled']['value'] ?? $preferences['email_enabled'] ?? FALSE;
                $pushEnabled = $preferences['push_enabled']['value'] ?? $preferences['push_enabled'] ?? FALSE;
                $smsEnabled = $preferences['sms_enabled']['value'] ?? $preferences['sms_enabled'] ?? FALSE;

                $notificationTypes = array_filter(['Email', 'Push', 'SMS'], function ($type) use ($emailEnabled, $pushEnabled, $smsEnabled) {
                    return match ($type) {
                        'Email' => $emailEnabled,
                        'Push'  => $pushEnabled,
                        'SMS'   => $smsEnabled,
                        default => FALSE,
                    };
                });

                if ($notificationTypes) {
                    $summary['key_features'][] = 'Notifications: ' . implode(', ', $notificationTypes);
                }
            }

            if ($category === 'display') {
                $theme = $preferences['theme']['value'] ?? $preferences['theme'] ?? NULL;
                $density = $preferences['density']['value'] ?? $preferences['density'] ?? NULL;

                if ($theme) {
                    $summary['key_features'][] = ucfirst($theme) . ' theme';
                }
                if ($density) {
                    $summary['key_features'][] = ucfirst($density) . ' density';
                }
            }
        }

        return $summary;
    }
}
