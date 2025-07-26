<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PreferencesController extends Controller
{
    /**
     * Get all user preferences
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $cacheKey = "user_preferences_{$userId}";
        
        $preferences = Cache::remember($cacheKey, 3600, function () use ($userId) {
            return [
                'dashboard' => [
                    'theme' => 'light',
                    'auto_refresh' => true,
                    'refresh_interval' => 30,
                    'show_notifications' => true,
                    'compact_mode' => false,
                ],
                'tickets' => [
                    'default_sort' => 'price_asc',
                    'items_per_page' => 25,
                    'show_unavailable' => false,
                    'auto_hide_expired' => true,
                    'price_format' => 'USD',
                ],
                'alerts' => [
                    'price_drop_threshold' => 10,
                    'availability_alerts' => true,
                    'email_notifications' => true,
                    'sms_notifications' => false,
                    'alert_frequency' => 'immediate',
                ],
                'monitoring' => [
                    'platforms' => ['ticketmaster', 'stubhub', 'viagogo'],
                    'max_price' => 1000,
                    'min_price' => 50,
                    'preferred_sections' => [],
                    'exclude_keywords' => [],
                ],
                'display' => [
                    'timezone' => 'UTC',
                    'date_format' => 'Y-m-d',
                    'time_format' => 'H:i',
                    'currency_symbol' => '$',
                ]
            ];
        });
        
        return response()->json([
            'data' => $preferences,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Store new preference
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|in:dashboard,tickets,alerts,monitoring,display',
            'key' => 'required|string',
            'value' => 'required'
        ]);

        $userId = auth()->id();
        $cacheKey = "user_preferences_{$userId}";
        
        // Get current preferences
        $preferences = Cache::get($cacheKey, []);
        
        // Update preference
        $preferences[$request->category][$request->key] = $request->value;
        
        // Cache updated preferences
        Cache::put($cacheKey, $preferences, 3600);
        
        // Here you would typically save to database
        // UserPreference::updateOrCreate([
        //     'user_id' => $userId,
        //     'category' => $request->category,
        //     'key' => $request->key
        // ], ['value' => $request->value]);

        return response()->json([
            'message' => 'Preference saved successfully',
            'data' => $preferences[$request->category]
        ]);
    }

    /**
     * Update specific preference
     */
    public function update(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|in:dashboard,tickets,alerts,monitoring,display',
            'value' => 'required'
        ]);

        $userId = auth()->id();
        $cacheKey = "user_preferences_{$userId}";
        
        $preferences = Cache::get($cacheKey, []);
        $preferences[$request->category][$key] = $request->value;
        
        Cache::put($cacheKey, $preferences, 3600);

        return response()->json([
            'message' => 'Preference updated successfully',
            'key' => $key,
            'value' => $request->value
        ]);
    }

    /**
     * Delete preference
     */
    public function destroy(Request $request, string $key): JsonResponse
    {
        $request->validate([
            'category' => 'required|string|in:dashboard,tickets,alerts,monitoring,display'
        ]);

        $userId = auth()->id();
        $cacheKey = "user_preferences_{$userId}";
        
        $preferences = Cache::get($cacheKey, []);
        
        if (isset($preferences[$request->category][$key])) {
            unset($preferences[$request->category][$key]);
            Cache::put($cacheKey, $preferences, 3600);
        }

        return response()->json([
            'message' => 'Preference deleted successfully'
        ]);
    }

    /**
     * Reset all preferences to default
     */
    public function reset(Request $request): JsonResponse
    {
        $userId = auth()->id();
        $cacheKey = "user_preferences_{$userId}";
        
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Preferences reset to default successfully'
        ]);
    }
}
