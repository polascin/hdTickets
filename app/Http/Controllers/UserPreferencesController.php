<?php declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFavoriteTeam;
use App\Models\UserFavoriteVenue;
use App\Models\UserNotificationSettings;
use App\Models\UserPreference;
use App\Models\UserPreferencePreset;
use App\Models\UserPricePreference;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function count;
use function in_array;
use function is_bool;
use function is_string;
use function sprintf;

class UserPreferencesController extends Controller
{
    /**
     * Display the user preferences page
     */
    /**
     * Index
     */
    public function index(): Illuminate\Contracts\View\View
    {
        $user = auth()->user();
        $preferences = $this->getUserPreferences($user);
        $notificationChannels = UserNotificationSettings::getSupportedChannels();
        $timezones = $this->getTimezones();
        $languages = $this->getLanguages();
        $themes = $this->getThemes();

        return view('profile.preferences', compact(
            'user',
            'preferences',
            'notificationChannels',
            'timezones',
            'languages',
            'themes',
        ));
    }

    /**
     * Update user preferences via AJAX
     */
    /**
     * Update
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $preferences = $request->input('preferences', []);

            DB::beginTransaction();

            $updated = [];
            $errors = [];

            foreach ($preferences as $category => $categoryPrefs) {
                foreach ($categoryPrefs as $key => $value) {
                    $fullKey = $category . '.' . $key;

                    if ($this->validatePreference($fullKey, $value)) {
                        // Handle special cases
                        if ($this->isUserProfileField($key)) {
                            $this->updateUserProfile($user, $key, $value);
                        } elseif ($this->isNotificationChannel($key)) {
                            $this->updateNotificationChannel($user, $key, $value);
                        } else {
                            UserPreference::setValue($user->id, $fullKey, $value);
                        }

                        $updated[] = $fullKey;
                    } else {
                        $errors[] = "Invalid value for preference: {$fullKey}";
                    }
                }
            }

            DB::commit();

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success' => TRUE,
                'message' => 'Preferences updated successfully',
                'updated' => $updated,
                'errors'  => $errors,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('User preferences update failed', [
                'user_id'     => auth()->id(),
                'error'       => $e->getMessage(),
                'preferences' => $request->input('preferences', []),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update preferences',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update single preference via AJAX
     */
    /**
     * UpdateSingle
     */
    public function updateSingle(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'key'   => 'required|string',
            'value' => 'required',
            'type'  => 'sometimes|string|in:string,boolean,integer,float,json',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $key = $request->input('key');
            $value = $request->input('value');
            $type = $request->input('type', 'json');

            // Type conversion
            switch ($type) {
                case 'boolean':
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);

                    break;
                case 'integer':
                    $value = (int) $value;

                    break;
                case 'float':
                    $value = (float) $value;

                    break;
            }

            if (! $this->validatePreference($key, $value)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid preference value',
                ], 422);
            }

            // Handle special cases
            if ($this->isUserProfileField($key)) {
                $this->updateUserProfile($user, $key, $value);
            } elseif ($this->isNotificationChannel($key)) {
                $this->updateNotificationChannel($user, $key, $value);
            } else {
                UserPreference::setValue($user->id, $key, $value);
            }

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success' => TRUE,
                'message' => 'Preference updated successfully',
                'key'     => $key,
                'value'   => $value,
            ]);
        } catch (Exception $e) {
            Log::error('Single preference update failed', [
                'user_id' => auth()->id(),
                'key'     => $request->input('key'),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update preference',
            ], 500);
        }
    }

    /**
     * Auto-detect user timezone
     */
    /**
     * DetectTimezone
     */
    public function detectTimezone(Request $request): JsonResponse
    {
        $timezone = $request->input('timezone');

        if (! $timezone || ! in_array($timezone, timezone_identifiers_list(), TRUE)) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Invalid timezone',
            ], 422);
        }

        try {
            $user = auth()->user();
            $user->update(['timezone' => $timezone]);

            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success'      => TRUE,
                'message'      => 'Timezone updated successfully',
                'timezone'     => $timezone,
                'display_name' => $this->getTimezoneDisplayName($timezone),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update timezone',
            ], 500);
        }
    }

    /**
     * Reset preferences to defaults
     */
    /**
     * Reset
     */
    public function reset(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $categories = $request->input('categories', []);

            DB::beginTransaction();

            if (empty($categories)) {
                // Reset all preferences
                UserPreference::where('user_id', $user->id)->delete();
                UserNotificationSettings::where('user_id', $user->id)->delete();
            } else {
                // Reset specific categories
                foreach ($categories as $category) {
                    UserPreference::where('user_id', $user->id)
                        ->where('key', 'LIKE', $category . '.%')
                        ->delete();
                }
            }

            DB::commit();

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success'     => TRUE,
                'message'     => 'Preferences reset successfully',
                'preferences' => $this->getUserPreferences($user),
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to reset preferences',
            ], 500);
        }
    }

    /**
     * Export user preferences
     */
    /**
     * Export
     */
    public function export(Request $request): JsonResponse
    {
        $user = auth()->user();
        $preferences = UserPreference::exportPreferences($user->id);

        return response()->json([
            'success'     => TRUE,
            'data'        => $preferences,
            'export_date' => now()->toISOString(),
            'user_id'     => $user->id,
        ]);
    }

    /**
     * Import user preferences
     */
    /**
     * Import
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'preferences' => 'required|array',
            'overwrite'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $preferences = $request->input('preferences');
            $overwrite = $request->input('overwrite', FALSE);

            if ($overwrite) {
                UserPreference::where('user_id', $user->id)->delete();
            }

            $result = UserPreference::importPreferences($user->id, $preferences);

            Cache::forget("user_preferences_{$user->id}");

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Preferences imported successfully',
                'imported' => $result['imported'],
                'errors'   => $result['errors'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to import preferences',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a single preference using new structure (AJAX)
     */
    /**
     * UpdatePreference
     */
    public function updatePreference(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category'  => 'required|string|max:50',
                'key'       => 'required|string|max:100',
                'value'     => 'nullable',
                'data_type' => 'sometimes|in:string,boolean,integer,array,json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $category = $request->category;
            $key = $request->key;
            $value = $request->value;
            $dataType = $request->data_type ?? 'string';

            // Process value based on data type
            $processedValue = $this->processPreferenceValue($value, $dataType);

            // Update or create preference
            $preference = UserPreference::updateOrCreate(
                [
                    'user_id'             => $user->id,
                    'preference_category' => $category,
                    'preference_key'      => $key,
                ],
                [
                    'preference_value' => $processedValue,
                    'data_type'        => $dataType,
                ],
            );

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            // Log the preference change
            Log::info('User preference updated', [
                'user_id'   => $user->id,
                'category'  => $category,
                'key'       => $key,
                'value'     => $processedValue,
                'data_type' => $dataType,
            ]);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Preference updated successfully',
                'preference' => $preference,
            ]);
        } catch (Exception $e) {
            Log::error('Error updating user preference', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while updating the preference',
            ], 500);
        }
    }

    /**
     * Update multiple preferences at once
     */
    /**
     * UpdatePreferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'preferences'             => 'required|array',
                'preferences.*.category'  => 'required|string|max:50',
                'preferences.*.key'       => 'required|string|max:100',
                'preferences.*.value'     => 'nullable',
                'preferences.*.data_type' => 'sometimes|in:string,boolean,integer,array,json',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $updated = [];

            DB::beginTransaction();

            try {
                foreach ($request->preferences as $pref) {
                    $category = $pref['category'];
                    $key = $pref['key'];
                    $value = $pref['value'] ?? NULL;
                    $dataType = $pref['data_type'] ?? 'string';

                    // Process value based on data type
                    $processedValue = $this->processPreferenceValue($value, $dataType);

                    // Update or create preference
                    $preference = UserPreference::updateOrCreate(
                        [
                            'user_id'             => $user->id,
                            'preference_category' => $category,
                            'preference_key'      => $key,
                        ],
                        [
                            'preference_value' => $processedValue,
                            'data_type'        => $dataType,
                        ],
                    );

                    $updated[] = $preference;
                }

                DB::commit();

                // Clear cache
                Cache::forget("user_preferences_{$user->id}");

                // Log the batch preference update
                Log::info('User preferences batch updated', [
                    'user_id' => $user->id,
                    'count'   => count($updated),
                ]);

                return response()->json([
                    'success'       => TRUE,
                    'message'       => 'Preferences updated successfully',
                    'updated_count' => count($updated),
                    'preferences'   => $updated,
                ]);
            } catch (Exception $e) {
                DB::rollBack();

                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Error updating user preferences batch', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while updating preferences',
            ], 500);
        }
    }

    /**
     * Export user preferences as JSON (enhanced version)
     */
    /**
     * ExportPreferences
     */
    public function exportPreferences(): JsonResponse
    {
        try {
            $user = Auth::user();

            $preferences = UserPreference::where('user_id', $user->id)
                ->select('preference_category', 'preference_key', 'preference_value', 'data_type')
                ->get()
                ->groupBy('preference_category');

            $exportData = [
                'user_id'     => $user->id,
                'exported_at' => now()->toISOString(),
                'preferences' => $preferences->map(function ($categoryPrefs, $category) {
                    return $categoryPrefs->mapWithKeys(function ($pref) {
                        return [$pref->preference_key => [
                            'value'     => $this->castPreferenceValue($pref->preference_value, $pref->data_type),
                            'data_type' => $pref->data_type,
                        ]];
                    });
                }),
            ];

            return response()->json([
                'success' => TRUE,
                'data'    => $exportData,
            ]);
        } catch (Exception $e) {
            Log::error('Error exporting user preferences', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while exporting preferences',
            ], 500);
        }
    }

    /**
     * Reset preferences to defaults (enhanced version)
     */
    /**
     * ResetPreferences
     */
    public function resetPreferences(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'categories'   => 'sometimes|array',
                'categories.*' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();
            $categories = $request->categories;

            $query = UserPreference::where('user_id', $user->id);

            if ($categories) {
                $query->whereIn('preference_category', $categories);
            }

            $deletedCount = $query->delete();

            // Clear cache
            Cache::forget("user_preferences_{$user->id}");

            // Log the reset action
            Log::info('User preferences reset', [
                'user_id'       => $user->id,
                'categories'    => $categories,
                'deleted_count' => $deletedCount,
            ]);

            return response()->json([
                'success'       => TRUE,
                'message'       => 'Preferences reset successfully',
                'deleted_count' => $deletedCount,
            ]);
        } catch (Exception $e) {
            Log::error('Error resetting user preferences', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while resetting preferences',
            ], 500);
        }
    }

    /**
     * Load a preference preset
     *
     * @param mixed $presetId
     */
    /**
     * LoadPreset
     *
     * @param mixed $presetId
     */
    public function loadPreset(Request $request, $presetId): JsonResponse
    {
        try {
            $user = Auth::user();

            $preset = UserPreferencePreset::where('id', $presetId)
                ->where('is_active', TRUE)
                ->where(function ($query) use ($user): void {
                    $query->where('is_system_preset', TRUE)
                        ->orWhere('created_by', $user->id);
                })
                ->first();

            if (! $preset) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Preset not found',
                ], 404);
            }

            $presetData = is_string($preset->preference_data)
                ? json_decode($preset->preference_data, TRUE)
                : $preset->preference_data;

            DB::beginTransaction();

            try {
                $updated = [];

                foreach ($presetData as $category => $preferences) {
                    foreach ($preferences as $key => $prefData) {
                        $value = $prefData['value'] ?? $prefData;
                        $dataType = $prefData['data_type'] ?? 'string';

                        // Process value based on data type
                        $processedValue = $this->processPreferenceValue($value, $dataType);

                        $preference = UserPreference::updateOrCreate(
                            [
                                'user_id'             => $user->id,
                                'preference_category' => $category,
                                'preference_key'      => $key,
                            ],
                            [
                                'preference_value' => $processedValue,
                                'data_type'        => $dataType,
                            ],
                        );

                        $updated[] = $preference;
                    }
                }

                DB::commit();

                // Clear cache
                Cache::forget("user_preferences_{$user->id}");

                Log::info('User preferences loaded from preset', [
                    'user_id'       => $user->id,
                    'preset_id'     => $presetId,
                    'preset_name'   => $preset->name,
                    'updated_count' => count($updated),
                ]);

                return response()->json([
                    'success'       => TRUE,
                    'message'       => "Preferences loaded from preset: {$preset->name}",
                    'updated_count' => count($updated),
                    'preset'        => $preset,
                ]);
            } catch (Exception $e) {
                DB::rollBack();

                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Error loading preference preset', [
                'user_id'   => Auth::id(),
                'preset_id' => $presetId,
                'error'     => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'An error occurred while loading the preset',
            ], 500);
        }
    }

    // ==================== SPORTS PREFERENCES METHODS ====================

    /**
     * Add favorite team
     */
    /**
     * AddFavoriteTeam
     */
    public function addFavoriteTeam(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'sport_type'   => 'required|string|max:50',
            'team_name'    => 'required|string|max:255',
            'team_city'    => 'nullable|string|max:255',
            'league'       => 'nullable|string|max:100',
            'priority'     => 'integer|min:1|max:5',
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            $team = UserFavoriteTeam::create([
                'user_id'      => $user->id,
                'sport_type'   => $request->sport_type,
                'team_name'    => $request->team_name,
                'team_city'    => $request->team_city,
                'league'       => $request->league,
                'priority'     => $request->priority ?? 1,
                'email_alerts' => $request->email_alerts ?? TRUE,
                'push_alerts'  => $request->push_alerts ?? TRUE,
                'sms_alerts'   => $request->sms_alerts ?? FALSE,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Favorite team added successfully',
                'team'    => $team,
            ]);
        } catch (Exception $e) {
            Log::error('Error adding favorite team', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to add favorite team',
            ], 500);
        }
    }

    /**
     * Remove favorite team
     */
    /**
     * RemoveFavoriteTeam
     */
    public function removeFavoriteTeam(Request $request, int $teamId): JsonResponse
    {
        try {
            $user = Auth::user();
            $team = UserFavoriteTeam::where('user_id', $user->id)
                ->where('id', $teamId)
                ->first();

            if (! $team) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Team not found',
                ], 404);
            }

            $team->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Favorite team removed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Error removing favorite team', [
                'user_id' => Auth::id(),
                'team_id' => $teamId,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to remove favorite team',
            ], 500);
        }
    }

    /**
     * Add favorite venue
     */
    /**
     * AddFavoriteVenue
     */
    public function addFavoriteVenue(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'venue_name'     => 'required|string|max:255',
            'city'           => 'required|string|max:255',
            'state_province' => 'nullable|string|max:255',
            'country'        => 'string|max:3',
            'venue_types'    => 'array',
            'capacity'       => 'integer|min:0',
            'priority'       => 'integer|min:1|max:5',
            'email_alerts'   => 'boolean',
            'push_alerts'    => 'boolean',
            'sms_alerts'     => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();

            $venue = UserFavoriteVenue::create([
                'user_id'        => $user->id,
                'venue_name'     => $request->venue_name,
                'city'           => $request->city,
                'state_province' => $request->state_province,
                'country'        => $request->country ?? 'USA',
                'venue_types'    => $request->venue_types ?? [],
                'capacity'       => $request->capacity,
                'priority'       => $request->priority ?? 1,
                'email_alerts'   => $request->email_alerts ?? TRUE,
                'push_alerts'    => $request->push_alerts ?? TRUE,
                'sms_alerts'     => $request->sms_alerts ?? FALSE,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Favorite venue added successfully',
                'venue'   => $venue,
            ]);
        } catch (Exception $e) {
            Log::error('Error adding favorite venue', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to add favorite venue',
            ], 500);
        }
    }

    /**
     * Remove favorite venue
     */
    /**
     * RemoveFavoriteVenue
     */
    public function removeFavoriteVenue(Request $request, int $venueId): JsonResponse
    {
        try {
            $user = Auth::user();
            $venue = UserFavoriteVenue::where('user_id', $user->id)
                ->where('id', $venueId)
                ->first();

            if (! $venue) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Venue not found',
                ], 404);
            }

            $venue->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Favorite venue removed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Error removing favorite venue', [
                'user_id'  => Auth::id(),
                'venue_id' => $venueId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to remove favorite venue',
            ], 500);
        }
    }

    /**
     * Add price preference
     */
    /**
     * AddPricePreference
     */
    public function addPricePreference(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'preference_name'         => 'required|string|max:255',
            'sport_type'              => 'nullable|string|max:50',
            'event_category'          => 'nullable|string|max:50',
            'min_price'               => 'nullable|numeric|min:0',
            'max_price'               => 'required|numeric|min:0',
            'preferred_quantity'      => 'integer|min:1|max:20',
            'seat_preferences'        => 'array',
            'price_drop_threshold'    => 'numeric|min:0|max:100',
            'auto_purchase_enabled'   => 'boolean',
            'auto_purchase_max_price' => 'nullable|numeric|min:0',
            'email_alerts'            => 'boolean',
            'push_alerts'             => 'boolean',
            'sms_alerts'              => 'boolean',
            'alert_frequency'         => 'in:immediate,hourly,daily',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Validate price range
        $errors = UserPricePreference::validatePreferenceData($request->all());
        if (! empty($errors)) {
            return response()->json([
                'success' => FALSE,
                'message' => implode(', ', $errors),
            ], 422);
        }

        try {
            $user = Auth::user();

            $preference = UserPricePreference::create([
                'user_id'                 => $user->id,
                'preference_name'         => $request->preference_name,
                'sport_type'              => $request->sport_type,
                'event_category'          => $request->event_category,
                'min_price'               => $request->min_price,
                'max_price'               => $request->max_price,
                'preferred_quantity'      => $request->preferred_quantity ?? 2,
                'seat_preferences'        => $request->seat_preferences ?? [],
                'price_drop_threshold'    => $request->price_drop_threshold ?? 15.00,
                'auto_purchase_enabled'   => $request->auto_purchase_enabled ?? FALSE,
                'auto_purchase_max_price' => $request->auto_purchase_max_price,
                'email_alerts'            => $request->email_alerts ?? TRUE,
                'push_alerts'             => $request->push_alerts ?? TRUE,
                'sms_alerts'              => $request->sms_alerts ?? FALSE,
                'alert_frequency'         => $request->alert_frequency ?? 'immediate',
            ]);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Price preference added successfully',
                'preference' => $preference,
            ]);
        } catch (Exception $e) {
            Log::error('Error adding price preference', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to add price preference',
            ], 500);
        }
    }

    /**
     * Remove price preference
     */
    /**
     * RemovePricePreference
     */
    public function removePricePreference(Request $request, int $preferenceId): JsonResponse
    {
        try {
            $user = Auth::user();
            $preference = UserPricePreference::where('user_id', $user->id)
                ->where('id', $preferenceId)
                ->first();

            if (! $preference) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Price preference not found',
                ], 404);
            }

            $preference->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Price preference removed successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Error removing price preference', [
                'user_id'       => Auth::id(),
                'preference_id' => $preferenceId,
                'error'         => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to remove price preference',
            ], 500);
        }
    }

    /**
     * Get user's sports preferences data
     */
    /**
     * Get  sports preferences
     */
    public function getSportsPreferences(): JsonResponse
    {
        try {
            $user = Auth::user();

            $favoriteTeams = UserFavoriteTeam::where('user_id', $user->id)
                ->orderBy('priority', 'desc')
                ->orderBy('team_name')
                ->get();

            $favoriteVenues = UserFavoriteVenue::where('user_id', $user->id)
                ->orderBy('priority', 'desc')
                ->orderBy('venue_name')
                ->get();

            $pricePreferences = UserPricePreference::where('user_id', $user->id)
                ->where('is_active', TRUE)
                ->orderBy('preference_name')
                ->get();

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'favorite_teams'    => $favoriteTeams,
                    'favorite_venues'   => $favoriteVenues,
                    'price_preferences' => $pricePreferences,
                    'available_sports'  => UserFavoriteTeam::getAvailableSports(),
                    'venue_types'       => UserFavoriteVenue::getAvailableVenueTypes(),
                    'event_categories'  => UserPricePreference::getEventCategories(),
                    'seat_preferences'  => UserPricePreference::getSeatPreferences(),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Error getting sports preferences', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to load sports preferences',
            ], 500);
        }
    }

    /**
     * Search teams for autocomplete
     */
    /**
     * SearchTeams
     */
    public function searchTeams(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'sport' => 'nullable|string',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $query = $request->query;
            $sport = $request->sport;
            $limit = $request->limit ?? 20;

            // Get popular teams first
            $popularTeams = UserFavoriteTeam::getPopularTeams($sport);

            // Filter by query
            $results = collect($popularTeams)
                ->filter(function ($team) use ($query) {
                    return str_contains(strtolower($team['full_name']), strtolower($query))
                           || str_contains(strtolower($team['name']), strtolower($query))
                           || str_contains(strtolower($team['city']), strtolower($query));
                })
                ->take($limit)
                ->values();

            return response()->json([
                'success' => TRUE,
                'results' => $results,
            ]);
        } catch (Exception $e) {
            Log::error('Error searching teams', [
                'query' => $request->query,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to search teams',
            ], 500);
        }
    }

    /**
     * Search venues for autocomplete
     */
    /**
     * SearchVenues
     */
    public function searchVenues(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'city'  => 'nullable|string',
            'limit' => 'integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $query = $request->query;
            $city = $request->city;
            $limit = $request->limit ?? 20;

            // Get popular venues first
            $popularVenues = UserFavoriteVenue::getPopularVenues($city);

            // Filter by query
            $results = collect($popularVenues)
                ->filter(function ($venue) use ($query) {
                    return str_contains(strtolower($venue['full_name']), strtolower($query))
                           || str_contains(strtolower($venue['name']), strtolower($query))
                           || str_contains(strtolower($venue['city']), strtolower($query));
                })
                ->take($limit)
                ->values();

            return response()->json([
                'success' => TRUE,
                'results' => $results,
            ]);
        } catch (Exception $e) {
            Log::error('Error searching venues', [
                'query' => $request->query,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to search venues',
            ], 500);
        }
    }

    /**
     * Update notification settings for a team/venue/price preference
     */
    /**
     * UpdateNotificationSettings
     */
    public function updateNotificationSettings(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'type'         => 'required|in:team,venue,price',
            'id'           => 'required|integer',
            'email_alerts' => 'boolean',
            'push_alerts'  => 'boolean',
            'sms_alerts'   => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => FALSE,
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $type = $request->type;
            $id = $request->id;

            $model = NULL;
            switch ($type) {
                case 'team':
                    $model = UserFavoriteTeam::where('user_id', $user->id)->find($id);

                    break;
                case 'venue':
                    $model = UserFavoriteVenue::where('user_id', $user->id)->find($id);

                    break;
                case 'price':
                    $model = UserPricePreference::where('user_id', $user->id)->find($id);

                    break;
            }

            if (! $model) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Item not found',
                ], 404);
            }

            $model->updateNotificationSettings([
                'email' => $request->email_alerts,
                'push'  => $request->push_alerts,
                'sms'   => $request->sms_alerts,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Notification settings updated successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Error updating notification settings', [
                'user_id' => Auth::id(),
                'type'    => $request->type,
                'id'      => $request->id,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update notification settings',
            ], 500);
        }
    }

    /**
     * Get user preferences with defaults
     */
    /**
     * Get  user preferences
     */
    private function getUserPreferences(User $user): array
    {
        $cacheKey = "user_preferences_{$user->id}";

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $preferences = UserPreference::where('user_id', $user->id)
                ->get()
                ->mapWithKeys(function ($pref) {
                    return [$pref->key => $pref->value];
                })
                ->toArray();

            // Get notification settings
            $notificationSettings = UserNotificationSettings::where('user_id', $user->id)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->channel => $setting->is_enabled];
                })
                ->toArray();

            return array_merge($this->getDefaultPreferences(), $preferences, [
                'notification_channels' => $notificationSettings,
                'user_timezone'         => $user->timezone ?? 'UTC',
                'user_language'         => $user->language ?? 'en',
            ]);
        });
    }

    /**
     * Get default preferences structure
     */
    /**
     * Get  default preferences
     */
    private function getDefaultPreferences(): array
    {
        return [
            // Notification Settings
            'email_notifications'    => TRUE,
            'push_notifications'     => TRUE,
            'sms_notifications'      => FALSE,
            'notification_frequency' => 'immediate', // immediate, hourly, daily
            'quiet_hours_enabled'    => FALSE,
            'quiet_hours_start'      => '23:00',
            'quiet_hours_end'        => '07:00',

            // Display Preferences
            'theme'             => 'light', // light, dark, auto
            'display_density'   => 'comfortable', // compact, comfortable, spacious
            'sidebar_collapsed' => FALSE,
            'show_tooltips'     => TRUE,
            'animation_enabled' => TRUE,
            'high_contrast'     => FALSE,

            // Dashboard Preferences
            'dashboard_auto_refresh'     => TRUE,
            'dashboard_refresh_interval' => 30,
            'dashboard_widgets_order'    => [
                'ticket_alerts', 'price_tracker', 'availability_map',
                'trending_events', 'user_analytics',
            ],
            'compact_ticket_cards' => FALSE,
            'show_price_history'   => TRUE,
            'currency_format'      => 'USD',

            // Alert Preferences
            'price_drop_threshold'     => 10,
            'availability_alerts'      => TRUE,
            'price_alerts'             => TRUE,
            'high_demand_alerts'       => TRUE,
            'escalation_enabled'       => FALSE,
            'escalation_delay_minutes' => 5,

            // Performance Preferences
            'lazy_loading_enabled'   => TRUE,
            'data_compression'       => TRUE,
            'offline_mode'           => FALSE,
            'bandwidth_optimization' => 'auto', // auto, low, high
        ];
    }

    /**
     * Validate preference value
     *
     * @param mixed $value
     */
    /**
     * ValidatePreference
     *
     * @param mixed $value
     */
    private function validatePreference(string $key, $value): bool
    {
        switch ($key) {
            case 'theme':
                return in_array($value, ['light', 'dark', 'auto'], TRUE);
            case 'display_density':
                return in_array($value, ['compact', 'comfortable', 'spacious'], TRUE);
            case 'notification_frequency':
                return in_array($value, ['immediate', 'hourly', 'daily'], TRUE);
            case 'currency_format':
                return in_array($value, ['USD', 'EUR', 'GBP', 'CAD'], TRUE);
            case 'bandwidth_optimization':
                return in_array($value, ['auto', 'low', 'high'], TRUE);
            case 'dashboard_refresh_interval':
                return is_numeric($value) && $value >= 10 && $value <= 300;
            case 'price_drop_threshold':
                return is_numeric($value) && $value >= 1 && $value <= 50;
            case 'escalation_delay_minutes':
                return is_numeric($value) && $value >= 1 && $value <= 60;
            case strpos($key, '.') !== FALSE:
                // Handle nested keys
                return $this->validateNestedPreference($key, $value);
            default:
                return TRUE;
        }
    }

    /**
     * Validate nested preference
     *
     * @param mixed $value
     */
    /**
     * ValidateNestedPreference
     *
     * @param mixed $value
     */
    private function validateNestedPreference(string $key, $value): bool
    {
        $parts = explode('.', $key);
        $lastPart = end($parts);

        if (str_ends_with($lastPart, '_enabled') || str_ends_with($lastPart, '_notifications')) {
            return is_bool($value);
        }

        if (str_ends_with($lastPart, '_threshold') || str_ends_with($lastPart, '_interval')) {
            return is_numeric($value);
        }

        return TRUE;
    }

    /**
     * Check if key is a user profile field
     */
    /**
     * Check if  user profile field
     */
    private function isUserProfileField(string $key): bool
    {
        return in_array($key, ['timezone', 'language'], TRUE);
    }

    /**
     * Check if key is a notification channel
     */
    /**
     * Check if  notification channel
     */
    private function isNotificationChannel(string $key): bool
    {
        return in_array($key, ['email', 'push', 'sms', 'slack', 'discord', 'telegram'], TRUE);
    }

    /**
     * Update user profile field
     *
     * @param mixed $value
     */
    /**
     * UpdateUserProfile
     *
     * @param mixed $value
     */
    private function updateUserProfile(User $user, string $key, $value): void
    {
        $user->update([$key => $value]);
    }

    /**
     * Update notification channel setting
     */
    /**
     * UpdateNotificationChannel
     */
    private function updateNotificationChannel(User $user, string $channel, bool $enabled): void
    {
        UserNotificationSettings::updateOrCreate(
            ['user_id' => $user->id, 'channel' => $channel],
            ['is_enabled' => $enabled],
        );
    }

    /**
     * Get available timezones
     */
    /**
     * Get  timezones
     */
    private function getTimezones(): array
    {
        $timezones = [];
        foreach (timezone_identifiers_list() as $timezone) {
            $timezones[$timezone] = $this->getTimezoneDisplayName($timezone);
        }

        return $timezones;
    }

    /**
     * Get timezone display name
     */
    /**
     * Get  timezone display name
     */
    private function getTimezoneDisplayName(string $timezone): string
    {
        try {
            $tz = new DateTimeZone($timezone);
            $offset = $tz->getOffset(new DateTime());
            $offsetHours = (int) ($offset / 3600);
            $offsetMinutes = (int) (($offset % 3600) / 60);
            $offsetString = sprintf('%+03d:%02d', $offsetHours, $offsetMinutes);

            return str_replace('_', ' ', $timezone) . " (UTC{$offsetString})";
        } catch (Exception $e) {
            return $timezone;
        }
    }

    /**
     * Get available languages
     */
    /**
     * Get  languages
     */
    private function getLanguages(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'nl' => 'Nederlands',
            'ru' => 'Русский',
            'ja' => '日本語',
            'ko' => '한국어',
            'zh' => '中文',
        ];
    }

    /**
     * Get available themes
     */
    /**
     * Get  themes
     */
    private function getThemes(): array
    {
        return [
            'light' => [
                'name'        => 'Light Mode',
                'description' => 'Clean and bright interface',
                'preview'     => '#ffffff',
            ],
            'dark' => [
                'name'        => 'Dark Mode',
                'description' => 'Easy on the eyes in low light',
                'preview'     => '#1f2937',
            ],
            'auto' => [
                'name'        => 'Auto',
                'description' => 'Matches your system preference',
                'preview'     => 'linear-gradient(45deg, #ffffff 50%, #1f2937 50%)',
            ],
        ];
    }

    /**
     * Process preference value based on data type
     *
     * @param mixed $value
     */
    private function processPreferenceValue($value, string $dataType)
    {
        switch ($dataType) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== NULL
                    ? (bool) $value : FALSE;

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
     * Cast preference value from storage based on data type
     *
     * @param mixed $value
     */
    private function castPreferenceValue($value, string $dataType)
    {
        switch ($dataType) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'array':
            case 'json':
                return json_decode($value, TRUE);
            case 'string':
            default:
                return (string) $value;
        }
    }
}
