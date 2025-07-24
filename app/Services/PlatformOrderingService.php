<?php

namespace App\Services;

class PlatformOrderingService
{
    /**
     * Get all platforms in the correct display order
     * 
     * @return array
     */
    public static function getAllPlatforms(): array
    {
        return collect(config('platforms.display_order'))
            ->sortBy('order')
            ->values()
            ->toArray();
    }

    /**
     * Get platform keys in the correct order
     * 
     * @return array
     */
    public static function getPlatformKeys(): array
    {
        return config('platforms.ordered_keys');
    }

    /**
     * Get platforms for dropdown/select elements
     * 
     * @param array $includeOnly Only include specific platforms
     * @param array $excludePlatforms Exclude specific platforms
     * @return array
     */
    public static function getPlatformsForSelect(array $includeOnly = [], array $excludePlatforms = []): array
    {
        $platforms = collect(config('platforms.display_order'))
            ->sortBy('order');
        
        // Filter platforms if includeOnly is specified
        if (!empty($includeOnly)) {
            $platforms = $platforms->filter(function($platform) use ($includeOnly) {
                return in_array($platform['key'], $includeOnly);
            });
        }
        
        // Exclude specific platforms if specified
        if (!empty($excludePlatforms)) {
            $platforms = $platforms->filter(function($platform) use ($excludePlatforms) {
                return !in_array($platform['key'], $excludePlatforms);
            });
        }
        
        return $platforms->values()->toArray();
    }

    /**
     * Get platform display name by key
     * 
     * @param string $key
     * @return string
     */
    public static function getPlatformDisplayName(string $key): string
    {
        $platforms = config('platforms.display_order');
        return $platforms[$key]['display_name'] ?? ucfirst($key);
    }

    /**
     * Check if a platform key is valid
     * 
     * @param string $key
     * @return bool
     */
    public static function isValidPlatform(string $key): bool
    {
        return array_key_exists($key, config('platforms.display_order'));
    }

    /**
     * Sort an array of platform keys according to the standard order
     * 
     * @param array $platformKeys
     * @return array
     */
    public static function sortPlatformKeys(array $platformKeys): array
    {
        $orderedKeys = config('platforms.ordered_keys');
        
        return collect($platformKeys)
            ->filter(function($key) use ($orderedKeys) {
                return in_array($key, $orderedKeys);
            })
            ->sortBy(function($key) use ($orderedKeys) {
                return array_search($key, $orderedKeys);
            })
            ->values()
            ->toArray();
    }

    /**
     * Get JavaScript array of platforms for frontend components
     * 
     * @return string JSON string
     */
    public static function getPlatformsForJavaScript(): string
    {
        $platforms = self::getAllPlatforms();
        
        $jsArray = collect($platforms)->map(function($platform) {
            return [
                'key' => $platform['key'],
                'name' => $platform['display_name'],
                'order' => $platform['order'],
            ];
        })->values();

        return json_encode($jsArray);
    }
}
