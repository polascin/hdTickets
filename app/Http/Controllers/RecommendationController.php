<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\RecommendationEngineService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function array_slice;
use function count;

/**
 * HD Tickets Recommendation API Controller
 *
 * Handles HTTP requests for the AI-powered recommendation system,
 * providing personalized sports event suggestions, pricing strategies,
 * and alert optimizations.
 *
 * @version 1.0.0
 */
class RecommendationController extends Controller
{
    public function __construct(private RecommendationEngineService $recommendationEngine)
    {
        $this->middleware('auth');
        $this->middleware('verified');
        $this->middleware('throttle:30,1')->only(['generateRecommendations', 'refreshRecommendations']);
        $this->middleware('throttle:10,1')->only(['getPerformanceMetrics']);
    }

    /**
     * Display the AI recommendations dashboard
     */
    public function dashboard()
    {
        return view('recommendations.dashboard');
    }

    /**
     * Get personalized recommendations for the authenticated user
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Validate request parameters
            $validator = Validator::make($request->all(), [
                'limit'             => 'integer|min:1|max:50',
                'filters'           => 'array',
                'filters.sports'    => 'array|max:10',
                'filters.location'  => 'string|max:100',
                'filters.max_price' => 'numeric|min:0',
                'refresh'           => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Invalid request parameters',
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $options = [
                'limit'   => $request->input('limit', 20),
                'filters' => $request->input('filters', []),
            ];

            // Clear cache if refresh is requested
            if ($request->boolean('refresh')) {
                $this->recommendationEngine->clearUserCache($user);
            }

            $recommendations = $this->recommendationEngine->generateRecommendations($user, $options);

            // Log recommendation generation for analytics
            Log::info('Recommendations generated', [
                'user_id'          => $user->id,
                'options'          => $options,
                'event_count'      => count($recommendations['events']['recommendations'] ?? []),
                'confidence_score' => $recommendations['events']['confidence_score'] ?? NULL,
                'generation_time'  => $recommendations['meta']['generation_time_ms'] ?? NULL,
            ]);

            return response()->json($recommendations);
        } catch (Exception $e) {
            Log::error('Failed to generate recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to generate recommendations at this time',
                'error'   => app()->isProduction() ? 'Internal server error' : $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Refresh recommendations by clearing cache and generating new ones
     */
    public function refreshRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            // Clear user recommendation cache
            $this->recommendationEngine->clearUserCache($user);

            // Validate and extract options
            $options = [
                'limit'   => $request->input('limit', 20),
                'filters' => $request->input('filters', []),
            ];

            // Generate fresh recommendations
            $recommendations = $this->recommendationEngine->generateRecommendations($user, $options);

            Log::info('Recommendations refreshed', [
                'user_id'         => $user->id,
                'generation_time' => $recommendations['meta']['generation_time_ms'] ?? NULL,
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Recommendations refreshed successfully',
                'data'    => $recommendations,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to refresh recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to refresh recommendations',
                'error'   => app()->isProduction() ? 'Service temporarily unavailable' : $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get specific event recommendations with filters
     */
    public function getEventRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'sport'     => 'string|max:50',
                'location'  => 'string|max:100',
                'min_price' => 'numeric|min:0',
                'max_price' => 'numeric|min:0',
                'limit'     => 'integer|min:1|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $filters = array_filter([
                'sports'    => $request->input('sport') ? [$request->input('sport')] : NULL,
                'location'  => $request->input('location'),
                'min_price' => $request->input('min_price'),
                'max_price' => $request->input('max_price'),
            ]);

            $options = [
                'limit'   => $request->input('limit', 20),
                'filters' => $filters,
            ];

            $recommendations = $this->recommendationEngine->generateRecommendations($user, $options);

            return response()->json([
                'success' => TRUE,
                'events'  => $recommendations['events'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get event recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load event recommendations',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get pricing strategy recommendations
     */
    public function getPricingStrategies(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $recommendations = $this->recommendationEngine->generateRecommendations($user);

            return response()->json([
                'success'                => TRUE,
                'pricing'                => $recommendations['pricing'],
                'user_price_sensitivity' => $recommendations['pricing']['current_price_sensitivity'] ?? NULL,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get pricing strategies', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load pricing strategies',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get alert setting recommendations
     */
    public function getAlertRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $recommendations = $this->recommendationEngine->generateRecommendations($user);

            return response()->json([
                'success' => TRUE,
                'alerts'  => $recommendations['alerts'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get alert recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load alert recommendations',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Apply AI-recommended alert settings to user preferences
     */
    public function applyAlertRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'apply_timing'    => 'boolean',
                'apply_frequency' => 'boolean',
                'apply_filters'   => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Get current recommendations
            $recommendations = $this->recommendationEngine->generateRecommendations($user);
            $alertRecommendations = $recommendations['alerts'] ?? NULL;

            if (!$alertRecommendations) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'No alert recommendations available',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Build updated preferences based on AI recommendations
            $updatedPreferences = [];

            if ($request->boolean('apply_timing', TRUE) && isset($alertRecommendations['optimal_timing'])) {
                $timing = $alertRecommendations['optimal_timing'];

                if (isset($timing['quiet_hours_suggestion'])) {
                    $updatedPreferences['quiet_hours'] = TRUE;
                    // Parse quiet hours suggestion (e.g., "22:00-08:00")
                    if (preg_match('/(\d{2}:\d{2})-(\d{2}:\d{2})/', $timing['quiet_hours_suggestion'], $matches)) {
                        $updatedPreferences['quiet_hours_start'] = $matches[1];
                        $updatedPreferences['quiet_hours_end'] = $matches[2];
                    }
                }
            }

            if ($request->boolean('apply_frequency', TRUE) && isset($alertRecommendations['frequency'])) {
                foreach ($alertRecommendations['frequency'] as $type => $frequency) {
                    $updatedPreferences["alert_frequency_{$type}"] = $frequency;
                }
            }

            if ($request->boolean('apply_filters', TRUE) && isset($alertRecommendations['smart_filters'])) {
                $filters = $alertRecommendations['smart_filters'];

                if (isset($filters['minimum_savings_threshold'])) {
                    $updatedPreferences['min_savings_threshold'] = $filters['minimum_savings_threshold'];
                }

                if (isset($filters['location_radius_km'])) {
                    $updatedPreferences['location_radius'] = $filters['location_radius_km'];
                }
            }

            // Update user preferences (this would typically go through a UserPreferencesService)
            // For now, we'll store in user's preferences JSON field or related table
            $user->preferences = array_merge($user->preferences ?? [], [
                'ai_alert_settings'             => $updatedPreferences,
                'ai_recommendations_applied_at' => now()->toISOString(),
            ]);
            $user->save();

            Log::info('AI alert recommendations applied', [
                'user_id'          => $user->id,
                'applied_settings' => $updatedPreferences,
            ]);

            return response()->json([
                'success'          => TRUE,
                'message'          => 'Alert recommendations applied successfully',
                'applied_settings' => $updatedPreferences,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to apply alert recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to apply alert recommendations',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get team and venue recommendations
     */
    public function getFollowRecommendations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $recommendations = $this->recommendationEngine->generateRecommendations($user);

            return response()->json([
                'success' => TRUE,
                'teams'   => $recommendations['teams'],
                'venues'  => $recommendations['venues'],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get follow recommendations', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load follow recommendations',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Provide feedback on recommendation quality
     */
    public function submitFeedback(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'recommendation_id'   => 'required|string|max:100',
                'recommendation_type' => 'required|in:event,pricing,alert,team,venue',
                'action'              => 'required|in:clicked,dismissed,purchased,followed',
                'rating'              => 'integer|min:1|max:5',
                'feedback_text'       => 'string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Store feedback for ML model improvement
            $feedbackData = [
                'user_id'             => $user->id,
                'recommendation_id'   => $request->input('recommendation_id'),
                'recommendation_type' => $request->input('recommendation_type'),
                'action'              => $request->input('action'),
                'rating'              => $request->input('rating'),
                'feedback_text'       => $request->input('feedback_text'),
                'created_at'          => now(),
            ];

            // Store in cache for batch processing
            $cacheKey = 'recommendation_feedback:' . date('Y-m-d-H');
            $existingFeedback = Cache::get($cacheKey, []);
            $existingFeedback[] = $feedbackData;
            Cache::put($cacheKey, $existingFeedback, 3600); // 1 hour

            Log::info('Recommendation feedback received', $feedbackData);

            return response()->json([
                'success' => TRUE,
                'message' => 'Thank you for your feedback!',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to submit recommendation feedback', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to submit feedback',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get recommendation system performance metrics (admin only)
     */
    public function getPerformanceMetrics(Request $request): JsonResponse
    {
        try {
            // Check admin access
            if (!Auth::user()->hasRole('admin')) {
                return response()->json([
                    'success' => FALSE,
                    'message' => 'Unauthorized access',
                ], Response::HTTP_FORBIDDEN);
            }

            $metrics = $this->recommendationEngine->getPerformanceMetrics();

            return response()->json([
                'success'      => TRUE,
                'metrics'      => $metrics,
                'generated_at' => now()->toISOString(),
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get recommendation metrics', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load performance metrics',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Clear user recommendation cache
     */
    public function clearUserCache(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $this->recommendationEngine->clearUserCache($user);

            Log::info('User recommendation cache cleared', ['user_id' => $user->id]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Recommendation cache cleared successfully',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to clear recommendation cache', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to clear cache',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's recommendation history
     */
    public function getRecommendationHistory(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'limit'  => 'integer|min:1|max:100',
                'offset' => 'integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            $limit = $request->input('limit', 20);
            $offset = $request->input('offset', 0);

            // Get recommendation history from cache/database
            // This would typically query a recommendation_history table
            $historyKey = "recommendation_history:user:{$user->id}";
            $history = Cache::get($historyKey, []);

            $paginatedHistory = array_slice($history, $offset, $limit);

            return response()->json([
                'success'    => TRUE,
                'history'    => $paginatedHistory,
                'pagination' => [
                    'limit'  => $limit,
                    'offset' => $offset,
                    'total'  => count($history),
                ],
            ]);
        } catch (Exception $e) {
            Log::error('Failed to get recommendation history', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to load recommendation history',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user preferences to improve recommendations
     */
    public function updateUserPreferences(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'favorite_sports'             => 'array|max:10',
                'favorite_sports.*'           => 'string|max:50',
                'preferred_price_range'       => 'array',
                'preferred_price_range.min'   => 'numeric|min:0',
                'preferred_price_range.max'   => 'numeric|min:0',
                'location_preferences'        => 'array',
                'location_preferences.cities' => 'array|max:5',
                'location_preferences.radius' => 'integer|min:1|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => FALSE,
                    'errors'  => $validator->errors(),
                ], Response::HTTP_BAD_REQUEST);
            }

            // Update user preferences
            $currentPreferences = $user->preferences ?? [];
            $newPreferences = array_merge($currentPreferences, [
                'recommendation_preferences' => $request->all(),
                'preferences_updated_at'     => now()->toISOString(),
            ]);

            $user->preferences = $newPreferences;
            $user->save();

            // Clear recommendation cache to force regeneration with new preferences
            $this->recommendationEngine->clearUserCache($user);

            Log::info('User recommendation preferences updated', [
                'user_id'         => $user->id,
                'new_preferences' => $request->all(),
            ]);

            return response()->json([
                'success' => TRUE,
                'message' => 'Preferences updated successfully. Your recommendations will be refreshed.',
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update user preferences', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => FALSE,
                'message' => 'Unable to update preferences',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
