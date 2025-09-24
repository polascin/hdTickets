<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationSettings;
use App\Models\UserPreference;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationPreferencesController extends Controller
{
    /**
     * Get user's notification preferences
     */
    /**
     * Index
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $preferences = UserPreference::getAlertPreferences($user->id);
            $notificationSettings = UserPreference::getNotificationPreferences($user->id);

            $channels = UserNotificationSettings::where('user_id', $user->id)
                ->get()
                ->mapWithKeys(fn ($setting): array => [$setting->channel => $setting->getChannelSettings()]);

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'preferences'           => $preferences,
                    'notification_settings' => $notificationSettings,
                    'channels'              => $channels,
                    'supported_channels'    => UserNotificationSettings::getSupportedChannels(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve preferences',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user preferences
     */
    /**
     * Update
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = $this->validatePreferences($request->all());
            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $preferences = $request->only([
                'notification_channels',
                'favorite_teams',
                'preferred_venues',
                'event_types',
                'alert_timing',
                'price_thresholds',
                'ml_settings',
                'escalation_settings',
            ]);

            $result = UserPreference::updateMultiple($user->id, $preferences);

            if (!empty($result['errors'])) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Some preferences could not be updated',
                    'errors'  => $result['errors'],
                    'updated' => $result['updated'],
                ], 422);
            }

            return response()->json([
                'success' => TRUE,
                'message' => 'Preferences updated successfully',
                'updated' => $result['updated'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update preferences',
                'error'   => $e->getMessage(),
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
            $user = $request->user();
            $keys = $request->input('keys', NULL);

            UserPreference::resetToDefaults($user->id, $keys);

            return response()->json([
                'success'    => TRUE,
                'message'    => 'Preferences reset to defaults',
                'reset_keys' => $keys ?? 'all',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to reset preferences',
                'error'   => $e->getMessage(),
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
        try {
            $user = $request->user();
            $preferences = UserPreference::exportPreferences($user->id);

            return response()->json([
                'success'     => TRUE,
                'data'        => $preferences,
                'exported_at' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to export preferences',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import user preferences
     */
    /**
     * Import
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'preferences' => 'required|array',
                'overwrite'   => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $preferences = $request->input('preferences');
            $result = UserPreference::importPreferences($user->id, $preferences);

            if (!empty($result['errors'])) {
                return response()->json([
                    'success'  => FALSE,
                    'message'  => 'Some preferences could not be imported',
                    'errors'   => $result['errors'],
                    'imported' => $result['imported'],
                ], 422);
            }

            return response()->json([
                'success'  => TRUE,
                'message'  => 'Preferences imported successfully',
                'imported' => $result['imported'],
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
     * Get specific preference
     */
    /**
     * Show
     */
    public function show(Request $request, string $key): JsonResponse
    {
        try {
            $user = $request->user();
            $value = UserPreference::getValue($user->id, $key);

            if ($value === NULL) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Preference not found',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'key'   => $key,
                    'value' => $value,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve preference',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update specific preference
     */
    /**
     * UpdatePreference
     */
    public function updatePreference(Request $request, string $key): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'value' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $value = $request->input('value');

            if (!UserPreference::validatePreference($key, $value)) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid preference value for key: ' . $key,
                ], 422);
            }

            UserPreference::setValue($user->id, $key, $value);

            return response()->json([
                'success' => TRUE,
                'message' => 'Preference updated successfully',
                'data'    => [
                    'key'   => $key,
                    'value' => $value,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update preference',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate preferences data
     */
    /**
     * ValidatePreferences
     */
    protected function validatePreferences(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'notification_channels'          => 'sometimes|array',
            'notification_channels.critical' => 'sometimes|string|in:slack,discord,telegram,push,sms',
            'notification_channels.high'     => 'sometimes|string|in:slack,discord,telegram,push,sms',
            'notification_channels.medium'   => 'sometimes|string|in:slack,discord,telegram,push,sms',
            'notification_channels.normal'   => 'sometimes|string|in:slack,discord,telegram,push,sms',
            'notification_channels.disabled' => 'sometimes|array',

            'favorite_teams'   => 'sometimes|array',
            'favorite_teams.*' => 'string|max:100',

            'preferred_venues'   => 'sometimes|array',
            'preferred_venues.*' => 'string|max:100',

            'event_types'   => 'sometimes|array',
            'event_types.*' => 'integer|min:1|max:5',

            'alert_timing'                   => 'sometimes|array',
            'alert_timing.quiet_hours_start' => 'sometimes|date_format:H:i',
            'alert_timing.quiet_hours_end'   => 'sometimes|date_format:H:i',
            'alert_timing.timezone'          => 'sometimes|string|max:50',

            'price_thresholds'                             => 'sometimes|array',
            'price_thresholds.max_budget'                  => 'sometimes|numeric|min:0',
            'price_thresholds.significant_drop_percentage' => 'sometimes|numeric|min:0|max:100',
            'price_thresholds.price_alert_threshold'       => 'sometimes|numeric|min:0|max:100',

            'ml_settings'                                 => 'sometimes|array',
            'ml_settings.enable_predictions'              => 'sometimes|boolean',
            'ml_settings.prediction_confidence_threshold' => 'sometimes|numeric|min:0|max:1',
            'ml_settings.enable_recommendations'          => 'sometimes|boolean',

            'escalation_settings'                          => 'sometimes|array',
            'escalation_settings.enable_escalation'        => 'sometimes|boolean',
            'escalation_settings.emergency_contact_phone'  => 'sometimes|nullable|string|max:20',
            'escalation_settings.emergency_contact_email'  => 'sometimes|nullable|email|max:255',
            'escalation_settings.escalation_delay_minutes' => 'sometimes|integer|min:1|max:60',
        ]);
    }
}
