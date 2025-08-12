<?php declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationSettings;
use App\Services\NotificationChannels\DiscordNotificationChannel;
use App\Services\NotificationChannels\SlackNotificationChannel;
use App\Services\NotificationChannels\TelegramNotificationChannel;
use App\Services\NotificationChannels\WebhookNotificationChannel;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class NotificationChannelsController extends Controller
{
    protected $channelServices = [
        'slack'    => SlackNotificationChannel::class,
        'discord'  => DiscordNotificationChannel::class,
        'telegram' => TelegramNotificationChannel::class,
        'webhook'  => WebhookNotificationChannel::class,
    ];

    /**
     * Get user's notification channels
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $channels = UserNotificationSettings::where('user_id', $user->id)
                ->get()
                ->map(function ($setting) {
                    return [
                        'id'            => $setting->id,
                        'channel'       => $setting->channel,
                        'is_enabled'    => $setting->is_enabled,
                        'is_configured' => $setting->isConfigured(),
                        'settings'      => $setting->getChannelSettings(),
                        'created_at'    => $setting->created_at,
                        'updated_at'    => $setting->updated_at,
                    ];
                });

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'channels'           => $channels,
                    'supported_channels' => UserNotificationSettings::getSupportedChannels(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve channels',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create or update a notification channel
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $validator = $this->validateChannelData($request->all());
            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $channelData = $request->only([
                'channel',
                'is_enabled',
                'webhook_url',
                'channel_name',
                'slack_user_id',
                'ping_role_id',
                'discord_user_id',
                'chat_id',
                'auth_type',
                'auth_token',
                'api_key',
                'basic_username',
                'basic_password',
                'webhook_secret',
                'custom_headers',
                'max_retries',
                'retry_delay',
                'settings',
            ]);

            $setting = UserNotificationSettings::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'channel' => $channelData['channel'],
                ],
                array_merge($channelData, ['user_id' => $user->id]),
            );

            return response()->json([
                'success' => TRUE,
                'message' => 'Channel configured successfully',
                'data'    => [
                    'id'            => $setting->id,
                    'channel'       => $setting->channel,
                    'is_enabled'    => $setting->is_enabled,
                    'is_configured' => $setting->isConfigured(),
                    'settings'      => $setting->getChannelSettings(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to configure channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific channel settings
     */
    public function show(Request $request, string $channel): JsonResponse
    {
        try {
            $user = $request->user();

            $setting = UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', $channel)
                ->first();

            if (! $setting) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Channel not configured',
                ], 404);
            }

            return response()->json([
                'success' => TRUE,
                'data'    => [
                    'id'            => $setting->id,
                    'channel'       => $setting->channel,
                    'is_enabled'    => $setting->is_enabled,
                    'is_configured' => $setting->isConfigured(),
                    'settings'      => $setting->getChannelSettings(),
                    'created_at'    => $setting->created_at,
                    'updated_at'    => $setting->updated_at,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to retrieve channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update channel settings
     */
    public function update(Request $request, string $channel): JsonResponse
    {
        try {
            $user = $request->user();

            $setting = UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', $channel)
                ->first();

            if (! $setting) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Channel not configured',
                ], 404);
            }

            $validator = $this->validateChannelData($request->all(), $channel);
            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $updateData = $request->only([
                'is_enabled',
                'webhook_url',
                'channel_name',
                'slack_user_id',
                'ping_role_id',
                'discord_user_id',
                'chat_id',
                'auth_type',
                'auth_token',
                'api_key',
                'basic_username',
                'basic_password',
                'webhook_secret',
                'custom_headers',
                'max_retries',
                'retry_delay',
                'settings',
            ]);

            $setting->update($updateData);

            return response()->json([
                'success' => TRUE,
                'message' => 'Channel updated successfully',
                'data'    => [
                    'id'            => $setting->id,
                    'channel'       => $setting->channel,
                    'is_enabled'    => $setting->is_enabled,
                    'is_configured' => $setting->isConfigured(),
                    'settings'      => $setting->getChannelSettings(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to update channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a notification channel
     */
    public function destroy(Request $request, string $channel): JsonResponse
    {
        try {
            $user = $request->user();

            $setting = UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', $channel)
                ->first();

            if (! $setting) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Channel not configured',
                ], 404);
            }

            $setting->delete();

            return response()->json([
                'success' => TRUE,
                'message' => 'Channel deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to delete channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test a notification channel
     */
    public function test(Request $request, string $channel): JsonResponse
    {
        try {
            $user = $request->user();

            if (! isset($this->channelServices[$channel])) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unknown channel: ' . $channel,
                ], 400);
            }

            $serviceClass = $this->channelServices[$channel];
            $service = new $serviceClass();

            $result = $service->testConnection($user);

            return response()->json([
                'success'   => $result['success'],
                'message'   => $result['message'],
                'channel'   => $channel,
                'tested_at' => now()->toISOString(),
            ], $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to test channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle channel enabled status
     */
    public function toggle(Request $request, string $channel): JsonResponse
    {
        try {
            $user = $request->user();

            $setting = UserNotificationSettings::where('user_id', $user->id)
                ->where('channel', $channel)
                ->first();

            if (! $setting) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Channel not configured',
                ], 404);
            }

            $setting->update(['is_enabled' => ! $setting->is_enabled]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Channel ' . ($setting->is_enabled ? 'enabled' : 'disabled'),
                'data'    => [
                    'channel'    => $setting->channel,
                    'is_enabled' => $setting->is_enabled,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => FALSE,
                'message' => 'Failed to toggle channel',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get supported channels information
     */
    public function supported(): JsonResponse
    {
        return response()->json([
            'success' => TRUE,
            'data'    => UserNotificationSettings::getSupportedChannels(),
        ]);
    }

    /**
     * Validate channel data
     */
    protected function validateChannelData(array $data, ?string $channel = NULL): \Illuminate\Validation\Validator
    {
        $rules = [
            'channel' => [
                'required_without:' . $channel,
                'string',
                Rule::in(['slack', 'discord', 'telegram', 'webhook']),
            ],
            'is_enabled'      => 'sometimes|boolean',
            'webhook_url'     => 'sometimes|nullable|url|max:500',
            'channel_name'    => 'sometimes|nullable|string|max:100',
            'slack_user_id'   => 'sometimes|nullable|string|max:50',
            'ping_role_id'    => 'sometimes|nullable|string|max:50',
            'discord_user_id' => 'sometimes|nullable|string|max:50',
            'chat_id'         => 'sometimes|nullable|string|max:50',
            'auth_type'       => 'sometimes|nullable|in:none,bearer,api_key,basic',
            'auth_token'      => 'sometimes|nullable|string|max:500',
            'api_key'         => 'sometimes|nullable|string|max:255',
            'basic_username'  => 'sometimes|nullable|string|max:100',
            'basic_password'  => 'sometimes|nullable|string|max:255',
            'webhook_secret'  => 'sometimes|nullable|string|max:255',
            'custom_headers'  => 'sometimes|nullable|array',
            'max_retries'     => 'sometimes|integer|min:1|max:10',
            'retry_delay'     => 'sometimes|integer|min:1|max:60',
            'settings'        => 'sometimes|nullable|array',
        ];

        // If updating existing channel, channel field is not required
        if ($channel) {
            unset($rules['channel']);
        }

        return Validator::make($data, $rules);
    }
}
