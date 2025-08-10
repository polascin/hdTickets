<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreferencePreset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'preference_data',
        'is_system_preset',
        'created_by',
        'is_active'
    ];

    protected $casts = [
        'preference_data' => 'json',
        'is_system_preset' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get the user who created this preset
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for system presets only
     */
    public function scopeSystemPresets($query)
    {
        return $query->where('is_system_preset', true);
    }

    /**
     * Scope for user-created presets only
     */
    public function scopeUserPresets($query)
    {
        return $query->where('is_system_preset', false);
    }

    /**
     * Scope for active presets only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for presets accessible to a specific user
     */
    public function scopeAccessibleTo($query, int $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('is_system_preset', true)
              ->orWhere('created_by', $userId);
        });
    }

    /**
     * Get default system presets
     */
    public static function getDefaultPresets(): array
    {
        return [
            'minimal_notifications' => [
                'name' => 'Minimal Notifications',
                'description' => 'Only essential notifications enabled',
                'preference_data' => [
                    'notifications' => [
                        'email_enabled' => true,
                        'email_ticket_assigned' => true,
                        'email_ticket_updated' => false,
                        'email_ticket_closed' => false,
                        'push_enabled' => false,
                        'sms_enabled' => false,
                    ],
                    'display' => [
                        'theme' => 'light',
                        'density' => 'comfortable',
                    ]
                ]
            ],
            'power_user' => [
                'name' => 'Power User',
                'description' => 'All features and notifications enabled',
                'preference_data' => [
                    'notifications' => [
                        'email_enabled' => true,
                        'email_ticket_assigned' => true,
                        'email_ticket_updated' => true,
                        'email_ticket_closed' => true,
                        'push_enabled' => true,
                        'push_ticket_assigned' => true,
                        'push_ticket_updated' => true,
                        'push_ticket_closed' => false,
                        'sms_enabled' => false,
                    ],
                    'display' => [
                        'theme' => 'dark',
                        'density' => 'compact',
                    ],
                    'dashboard' => [
                        'auto_refresh' => true,
                        'refresh_interval' => 15,
                        'compact_view' => true,
                    ]
                ]
            ],
            'mobile_optimized' => [
                'name' => 'Mobile Optimized',
                'description' => 'Settings optimized for mobile use',
                'preference_data' => [
                    'notifications' => [
                        'push_enabled' => true,
                        'push_ticket_assigned' => true,
                        'email_enabled' => false,
                        'sms_enabled' => false,
                    ],
                    'display' => [
                        'theme' => 'auto',
                        'density' => 'spacious',
                    ],
                    'dashboard' => [
                        'auto_refresh' => false,
                        'compact_view' => false,
                    ]
                ]
            ]
        ];
    }

    /**
     * Create default system presets
     */
    public static function createSystemPresets(): void
    {
        $defaults = self::getDefaultPresets();

        foreach ($defaults as $key => $presetData) {
            self::updateOrCreate(
                [
                    'name' => $presetData['name'],
                    'is_system_preset' => true
                ],
                [
                    'description' => $presetData['description'],
                    'preference_data' => $presetData['preference_data'],
                    'is_active' => true,
                    'created_by' => null
                ]
            );
        }
    }

    /**
     * Create a user preset from current preferences
     */
    public static function createFromUserPreferences(
        int $userId, 
        string $name, 
        string $description = null,
        array $categories = null
    ): self {
        // Get user's current preferences
        $query = UserPreference::where('user_id', $userId);
        
        if ($categories) {
            $query->whereIn('preference_category', $categories);
        }

        $preferences = $query->get()
            ->groupBy('preference_category')
            ->map(function ($categoryPrefs) {
                return $categoryPrefs->mapWithKeys(function ($pref) {
                    return [$pref->preference_key => [
                        'value' => UserPreference::castValue($pref->preference_value, $pref->data_type),
                        'data_type' => $pref->data_type
                    ]];
                });
            });

        return self::create([
            'name' => $name,
            'description' => $description ?? "Custom preset created from user preferences",
            'preference_data' => $preferences->toArray(),
            'is_system_preset' => false,
            'created_by' => $userId,
            'is_active' => true
        ]);
    }

    /**
     * Apply this preset to a user
     */
    public function applyToUser(int $userId): array
    {
        $updated = [];
        $errors = [];

        $presetData = is_string($this->preference_data) 
            ? json_decode($this->preference_data, true) 
            : $this->preference_data;

        foreach ($presetData as $category => $preferences) {
            foreach ($preferences as $key => $prefData) {
                try {
                    $value = $prefData['value'] ?? $prefData;
                    $dataType = $prefData['data_type'] ?? 'string';

                    UserPreference::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'preference_category' => $category,
                            'preference_key' => $key
                        ],
                        [
                            'preference_value' => UserPreference::processValue($value, $dataType),
                            'data_type' => $dataType
                        ]
                    );

                    $updated[] = "{$category}.{$key}";

                } catch (\Exception $e) {
                    $errors[] = "Error applying {$category}.{$key}: " . $e->getMessage();
                }
            }
        }

        return [
            'updated' => $updated,
            'errors' => $errors
        ];
    }

    /**
     * Get presets accessible to a user with usage statistics
     */
    public static function getAccessiblePresetsWithStats(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return self::accessibleTo($userId)
            ->active()
            ->withCount(['applications' => function($query) {
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
    public function duplicateForUser(int $userId, string $newName = null): self
    {
        return self::create([
            'name' => $newName ?? ($this->name . ' (Copy)'),
            'description' => $this->description,
            'preference_data' => $this->preference_data,
            'is_system_preset' => false,
            'created_by' => $userId,
            'is_active' => true
        ]);
    }

    /**
     * Validate preset data structure
     */
    public function validatePresetData(): array
    {
        $errors = [];
        $presetData = is_string($this->preference_data) 
            ? json_decode($this->preference_data, true) 
            : $this->preference_data;

        if (!is_array($presetData)) {
            $errors[] = 'Preference data must be a valid JSON object';
            return $errors;
        }

        // Validate structure
        foreach ($presetData as $category => $preferences) {
            if (!is_array($preferences)) {
                $errors[] = "Category '{$category}' must contain an array of preferences";
                continue;
            }

            foreach ($preferences as $key => $prefData) {
                // Allow both simple values and structured data
                if (is_array($prefData)) {
                    if (!isset($prefData['value'])) {
                        $errors[] = "Preference '{$category}.{$key}' must have a 'value' field";
                    }
                    
                    $dataType = $prefData['data_type'] ?? 'string';
                    if (!in_array($dataType, ['string', 'boolean', 'integer', 'array', 'json'])) {
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
    public function getSummary(): array
    {
        $presetData = is_string($this->preference_data) 
            ? json_decode($this->preference_data, true) 
            : $this->preference_data;

        $summary = [
            'categories' => array_keys($presetData),
            'total_preferences' => 0,
            'key_features' => []
        ];

        foreach ($presetData as $category => $preferences) {
            $summary['total_preferences'] += count($preferences);
            
            // Extract some key features for display
            if ($category === 'notifications') {
                $emailEnabled = $preferences['email_enabled']['value'] ?? $preferences['email_enabled'] ?? false;
                $pushEnabled = $preferences['push_enabled']['value'] ?? $preferences['push_enabled'] ?? false;
                $smsEnabled = $preferences['sms_enabled']['value'] ?? $preferences['sms_enabled'] ?? false;
                
                $notificationTypes = array_filter(['Email', 'Push', 'SMS'], function($type) use ($emailEnabled, $pushEnabled, $smsEnabled) {
                    return match($type) {
                        'Email' => $emailEnabled,
                        'Push' => $pushEnabled,
                        'SMS' => $smsEnabled,
                        default => false
                    };
                });
                
                if ($notificationTypes) {
                    $summary['key_features'][] = 'Notifications: ' . implode(', ', $notificationTypes);
                }
            }
            
            if ($category === 'display') {
                $theme = $preferences['theme']['value'] ?? $preferences['theme'] ?? null;
                $density = $preferences['density']['value'] ?? $preferences['density'] ?? null;
                
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
