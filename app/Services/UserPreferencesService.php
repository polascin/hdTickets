<?php declare(strict_types=1);

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\Venue;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function array_slice;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;

/**
 * UserPreferencesService
 *
 * Comprehensive service for managing user preferences including sports, teams,
 * venues, pricing strategies, location settings, and advanced configurations
 * for the HD Tickets sports events monitoring platform.
 */
class UserPreferencesService
{
    /** Cache key prefix for user preferences. */
    private const CACHE_PREFIX = 'user_preferences:';

    /** Cache TTL in seconds (1 hour). */
    private const CACHE_TTL = 3600;

    /**
     * Get user preferences with defaults.
     */
    public function getUserPreferences(int $userId): array
    {
        $cacheKey = self::CACHE_PREFIX . $userId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId): array {
            $preferences = UserPreference::where('user_id', $userId)
                ->get()
                ->pluck('value', 'key')
                ->toArray();

            return array_merge($this->getDefaultPreferences(), $preferences);
        });
    }

    /**
     * Update user preferences.
     */
    public function updateUserPreferences(int $userId, array $preferences): array
    {
        DB::beginTransaction();

        try {
            // Process each preference category
            foreach ($preferences as $category => $categoryData) {
                if (is_array($categoryData)) {
                    $this->updatePreferenceCategory($userId, $category, $categoryData);
                } else {
                    // Handle single preference value
                    UserPreference::updateOrCreate(
                        [
                            'user_id' => $userId,
                            'key'     => $category,
                        ],
                        [
                            'value'     => $this->serializeValue($categoryData),
                            'data_type' => $this->getDataType($categoryData),
                        ],
                    );
                }
            }

            DB::commit();

            // Clear cache
            $this->clearUserPreferencesCache($userId);

            Log::info('User preferences updated successfully', [
                'user_id'    => $userId,
                'categories' => array_keys($preferences),
            ]);

            return $this->getUserPreferences($userId);
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    /**
     * Get default preferences structure.
     */
    public function getDefaultPreferences(): array
    {
        return [
            'sports'  => [],
            'teams'   => [],
            'venues'  => [],
            'pricing' => [
                'budgetMin'       => 50,
                'budgetMax'       => 300,
                'alertThresholds' => [
                    'small'  => 5,
                    'medium' => 15,
                    'large'  => 30,
                ],
                'strategy' => 'balanced',
            ],
            'location' => [
                'primary'     => '',
                'secondary'   => [],
                'maxDistance' => 100,
            ],
            'advanced' => [
                'alertFrequency'   => 'real-time',
                'monitoringWindow' => [
                    'days'  => 30,
                    'hours' => 24,
                ],
                'dataCollection' => [
                    'analytics'       => TRUE,
                    'personalization' => TRUE,
                    'marketing'       => FALSE,
                ],
                'automation' => [
                    'autoBookmark'     => FALSE,
                    'autoAlert'        => TRUE,
                    'smartSuggestions' => TRUE,
                ],
            ],
        ];
    }

    /**
     * Add team to user's favorites.
     */
    public function addFavoriteTeam(int $userId, int $teamId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $team = Team::findOrFail($teamId);

            // Check if already exists
            if ($user->favoriteTeams()->where('team_id', $teamId)->exists()) {
                return FALSE;
            }

            $user->favoriteTeams()->attach($teamId, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->clearUserPreferencesCache($userId);

            Log::info('Team added to favorites', [
                'user_id'   => $userId,
                'team_id'   => $teamId,
                'team_name' => $team->name,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to add favorite team', [
                'user_id' => $userId,
                'team_id' => $teamId,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Remove team from user's favorites.
     */
    public function removeFavoriteTeam(int $userId, int $teamId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $removed = $user->favoriteTeams()->detach($teamId);

            if ($removed) {
                $this->clearUserPreferencesCache($userId);

                Log::info('Team removed from favorites', [
                    'user_id' => $userId,
                    'team_id' => $teamId,
                ]);
            }

            return (bool) $removed;
        } catch (Exception $e) {
            Log::error('Failed to remove favorite team', [
                'user_id' => $userId,
                'team_id' => $teamId,
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Add venue to user's favorites.
     */
    public function addFavoriteVenue(int $userId, int $venueId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $venue = Venue::findOrFail($venueId);

            // Check if already exists
            if ($user->favoriteVenues()->where('venue_id', $venueId)->exists()) {
                return FALSE;
            }

            $user->favoriteVenues()->attach($venueId, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->clearUserPreferencesCache($userId);

            Log::info('Venue added to favorites', [
                'user_id'    => $userId,
                'venue_id'   => $venueId,
                'venue_name' => $venue->name,
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Failed to add favorite venue', [
                'user_id'  => $userId,
                'venue_id' => $venueId,
                'error'    => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Remove venue from user's favorites.
     */
    public function removeFavoriteVenue(int $userId, int $venueId): bool
    {
        try {
            $user = User::findOrFail($userId);
            $removed = $user->favoriteVenues()->detach($venueId);

            if ($removed) {
                $this->clearUserPreferencesCache($userId);

                Log::info('Venue removed from favorites', [
                    'user_id'  => $userId,
                    'venue_id' => $venueId,
                ]);
            }

            return (bool) $removed;
        } catch (Exception $e) {
            Log::error('Failed to remove favorite venue', [
                'user_id'  => $userId,
                'venue_id' => $venueId,
                'error'    => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Get user's favorite teams with details.
     *
     * @return Collection
     */
    public function getUserFavoriteTeams(int $userId)
    {
        $cacheKey = "user_favorite_teams:{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => User::findOrFail($userId)
            ->favoriteTeams()
            ->with(['sport'])
            ->orderBy('name')
            ->get());
    }

    /**
     * Get user's favorite venues with details.
     *
     * @return Collection
     */
    public function getUserFavoriteVenues(int $userId)
    {
        $cacheKey = "user_favorite_venues:{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, fn () => User::findOrFail($userId)
            ->favoriteVenues()
            ->with(['sports'])
            ->orderBy('name')
            ->get());
    }

    /**
     * Get personalized recommendations based on user preferences.
     */
    public function getPersonalizedRecommendations(int $userId): array
    {
        $preferences = $this->getUserPreferences($userId);
        $favoriteTeams = $this->getUserFavoriteTeams($userId);
        $this->getUserFavoriteVenues($userId);

        return [
            'sports'  => $this->getSportsRecommendations($preferences),
            'teams'   => $this->getTeamRecommendations($favoriteTeams),
            'venues'  => $this->getVenueRecommendations($preferences),
            'pricing' => $this->getPricingRecommendations($preferences),
            'events'  => $this->getEventRecommendations(),
        ];
    }

    /**
     * Export user preferences for backup or migration.
     */
    public function exportUserPreferences(int $userId): array
    {
        $user = User::findOrFail($userId);
        $preferences = $this->getUserPreferences($userId);
        $favoriteTeams = $this->getUserFavoriteTeams($userId);
        $favoriteVenues = $this->getUserFavoriteVenues($userId);

        return [
            'user_info' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'created_at' => $user->created_at,
            ],
            'preferences'    => $preferences,
            'favorite_teams' => $favoriteTeams->map(fn ($team): array => [
                'id'       => $team->id,
                'name'     => $team->name,
                'sport'    => $team->sport->name ?? 'Unknown',
                'added_at' => $team->pivot->created_at,
            ]),
            'favorite_venues' => $favoriteVenues->map(fn ($venue): array => [
                'id'       => $venue->id,
                'name'     => $venue->name,
                'city'     => $venue->city,
                'state'    => $venue->state,
                'added_at' => $venue->pivot->created_at,
            ]),
            'export_date' => now()->toISOString(),
            'version'     => config('app.version', '1.0.0'),
        ];
    }

    /**
     * Validate preference data.
     *
     * @param mixed $value
     */
    public function validatePreference(string $key, $value): bool
    {
        // Basic validation rules
        $validationRules = [
            'sports'                  => fn ($val): bool => is_array($val),
            'pricing.budgetMin'       => fn ($val): bool => is_numeric($val) && $val >= 0,
            'pricing.budgetMax'       => fn ($val): bool => is_numeric($val) && $val >= 0,
            'pricing.strategy'        => fn ($val): bool => in_array($val, ['budget', 'balanced', 'premium'], TRUE),
            'location.maxDistance'    => fn ($val): bool => is_numeric($val) && $val >= 0 && $val <= 1000,
            'advanced.alertFrequency' => fn ($val): bool => in_array($val, ['real-time', 'hourly', 'daily', 'weekly'], TRUE),
        ];

        if (isset($validationRules[$key])) {
            return $validationRules[$key]($value);
        }

        // Default validation passes
        return TRUE;
    }

    /**
     * Update a specific preference category.
     */
    private function updatePreferenceCategory(int $userId, string $category, array $categoryData): void
    {
        foreach ($categoryData as $key => $value) {
            $prefKey = $category . '.' . $key;

            UserPreference::updateOrCreate(
                [
                    'user_id' => $userId,
                    'key'     => $prefKey,
                ],
                [
                    'value'     => $this->serializeValue($value),
                    'data_type' => $this->getDataType($value),
                ],
            );
        }
    }

    /**
     * Clear user preferences cache.
     */
    private function clearUserPreferencesCache(int $userId): void
    {
        $cacheKeys = [
            self::CACHE_PREFIX . $userId,
            "user_favorite_teams:{$userId}",
            "user_favorite_venues:{$userId}",
            "user_recommendations:{$userId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Serialize value for storage.
     *
     * @param mixed $value
     */
    private function serializeValue($value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }

    /**
     * Determine data type for preference value.
     *
     * @param mixed $value
     */
    private function getDataType($value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_array($value) || is_object($value)) {
            return 'json';
        }

        return 'string';
    }

    /**
     * Get sports recommendations based on user preferences.
     */
    private function getSportsRecommendations(array $preferences): array
    {
        $currentSports = $preferences['sports'] ?? [];

        // Popular sports not yet selected
        $allSports = ['football', 'basketball', 'baseball', 'hockey', 'soccer', 'tennis'];
        $recommendations = array_diff($allSports, $currentSports);

        return array_slice($recommendations, 0, 3);
    }

    /**
     * Get team recommendations based on favorite teams.
     *
     * @param Collection $favoriteTeams
     */
    private function getTeamRecommendations($favoriteTeams): array
    {
        // Logic to recommend similar teams based on sports, location, etc.
        $favoriteTeams->pluck('sport.name')->unique()->toArray();

        // This would be replaced with actual database queries
        return [];
    }

    /**
     * Get venue recommendations based on favorite venues.
     */
    private function getVenueRecommendations(array $preferences): array
    {
        // This would be replaced with actual database queries
        return [];
    }

    /**
     * Get pricing recommendations based on user preferences.
     */
    private function getPricingRecommendations(array $preferences): array
    {
        $pricing = $preferences['pricing'] ?? [];
        $recommendations = [];

        // Suggest adjustments to budget ranges or alert thresholds
        if (($pricing['budgetMax'] ?? 0) < 100) {
            $recommendations[] = [
                'type'          => 'budget_increase',
                'message'       => 'Consider increasing your budget to access more premium events',
                'suggested_max' => ($pricing['budgetMax'] ?? 100) * 1.5,
            ];
        }

        return $recommendations;
    }

    /**
     * Get event recommendations based on all user data.
     */
    private function getEventRecommendations(): array
    {
        // Complex recommendation logic based on multiple factors
        return [];
        // This would involve actual event queries based on:
        // - Favorite teams' upcoming games
        // - Events at favorite venues
        // - Events in preferred sports categories
        // - Events within price range
        // - Events within location preferences
    }
}