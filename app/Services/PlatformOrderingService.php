<?php declare(strict_types=1);

namespace App\Services;

use function array_key_exists;
use function in_array;

class PlatformOrderingService
{
    /**
     * Get all platforms in the correct display order
     */
    /**
     * Get  all platforms
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
     */
    /**
     * Get  platform keys
     */
    public static function getPlatformKeys(): array
    {
        return config('platforms.ordered_keys');
    }

    /**
     * Get platforms for dropdown/select elements
     *
     * @param array<string, mixed> $includeOnly      Only include specific platforms
     * @param array<string, mixed> $excludePlatforms Exclude specific platforms
     */
    /**
     * Get  platforms for select
     */
    public static function getPlatformsForSelect(array $includeOnly = [], array $excludePlatforms = []): array
    {
        $platforms = collect(config('platforms.display_order'))
            ->sortBy('order');

        // Filter platforms if includeOnly is specified
        if ($includeOnly !== []) {
            $platforms = $platforms->filter(fn ($platform): bool => in_array($platform['key'], $includeOnly, TRUE));
        }

        // Exclude specific platforms if specified
        if ($excludePlatforms !== []) {
            $platforms = $platforms->filter(fn ($platform): bool => ! in_array($platform['key'], $excludePlatforms, TRUE));
        }

        return $platforms->values()->toArray();
    }

    /**
     * Get platform display name by key
     */
    /**
     * Get  platform display name
     */
    public static function getPlatformDisplayName(string $key): string
    {
        $platforms = config('platforms.display_order');

        return $platforms[$key]['display_name'] ?? ucfirst($key);
    }

    /**
     * Check if a platform key is valid
     */
    /**
     * Check if  valid platform
     */
    public static function isValidPlatform(string $key): bool
    {
        return array_key_exists($key, config('platforms.display_order'));
    }

    /**
     * Sort an array of platform keys according to the standard order
     */
    /**
     * SortPlatformKeys
     */
    public static function sortPlatformKeys(array $platformKeys): array
    {
        $orderedKeys = config('platforms.ordered_keys');

        return collect($platformKeys)
            ->filter(fn ($key): bool => in_array($key, $orderedKeys, TRUE))
            ->sortBy(fn ($key): int|string|false => array_search($key, $orderedKeys, TRUE))
            ->values()
            ->toArray();
    }

    /**
     * Get JavaScript array of platforms for frontend components
     *
     * @return string JSON string
     */
    /**
     * Get  platforms for java script
     */
    public static function getPlatformsForJavaScript(): string
    {
        $platforms = self::getAllPlatforms();

        $jsArray = collect($platforms)->map(fn ($platform): array => [
            'key'   => $platform['key'],
            'name'  => $platform['display_name'],
            'order' => $platform['order'],
        ])->values();

        return json_encode($jsArray);
    }
}
