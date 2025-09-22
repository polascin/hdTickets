<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * System Setting Model
 * 
 * Stores configuration settings for the HD Tickets platform.
 * Used for persisting admin panel system configurations.
 * 
 * @property string $key Configuration key
 * @property string $value Configuration value (can be JSON encoded)
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class SystemSetting extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'system_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get a setting value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        // Try to decode JSON, return raw value if not JSON
        $decoded = json_decode($setting->value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    /**
     * Set a setting value by key
     * 
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, $value): bool
    {
        $encodedValue = is_array($value) || is_object($value) 
            ? json_encode($value) 
            : $value;

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $encodedValue]
        ) !== null;
    }

    /**
     * Get multiple settings by prefix
     * 
     * @param string $prefix
     * @return \Illuminate\Support\Collection
     */
    public static function getByPrefix(string $prefix)
    {
        return static::where('key', 'like', $prefix . '%')
            ->get()
            ->pluck('value', 'key')
            ->map(function ($value) {
                $decoded = json_decode($value, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            });
    }

    /**
     * Delete settings by prefix
     * 
     * @param string $prefix
     * @return int
     */
    public static function deleteByPrefix(string $prefix): int
    {
        return static::where('key', 'like', $prefix . '%')->delete();
    }
}