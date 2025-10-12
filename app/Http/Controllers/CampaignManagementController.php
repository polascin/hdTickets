<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CampaignManagementService;
use App\Models\MarketingCampaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Campaign Management Controller
 * 
 * Handles marketing campaign operations including:
 * - Campaign creation and management
 * - Campaign execution and scheduling
 * - Performance analytics and reporting
 * - A/B testing and optimization
 */
class CampaignManagementController extends Controller
{
    public function __construct(
        private CampaignManagementService $campaignService
    ) {
        $this->middleware('auth');
        $this->middleware('can:manage-campaigns')->except(['trackOpen', 'trackClick']);
    }

    /**
     * Get all campaigns with analytics
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $campaigns = $this->campaignService->getAllCampaignsWithAnalytics();

            return response()->json([
                'success' => true,
                'data' => $campaigns,
                'meta' => [
                    'total' => count($campaigns),
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch campaigns', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campaigns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new campaign
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:email,push,in_app,sms',
            'target_audience' => 'nullable|string|in:all,subscribers,active_users,inactive_users',
            'schedule_type' => 'nullable|in:immediate,scheduled,recurring',
            'scheduled_at' => 'nullable|date|after:now',
            'content' => 'required|array',
            'content.subject' => 'required_if:type,email|string|max:255',
            'content.body' => 'required|string',
            'content.title' => 'required_if:type,push,in_app|string|max:255',
            'content.message' => 'required_if:type,push,in_app,sms|string',
            'target_criteria' => 'nullable|array',
            'settings' => 'nullable|array',
            'ab_test' => 'nullable|boolean',
            'variants' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $campaign = $this->campaignService->createCampaign($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Campaign created successfully',
                'data' => [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'type' => $campaign->type,
                    'status' => $campaign->status,
                    'created_at' => $campaign->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create campaign', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific campaign details
     */
    public function show(MarketingCampaign $campaign): JsonResponse
    {
        try {
            $analytics = $this->campaignService->getCampaignAnalytics($campaign);

            return response()->json([
                'success' => true,
                'data' => [
                    'campaign' => $campaign->load(['creator', 'targets.user', 'analytics']),
                    'analytics' => $analytics
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch campaign details', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campaign details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update campaign
     */
    public function update(Request $request, MarketingCampaign $campaign): JsonResponse
    {
        if (!$campaign->isEditable()) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign cannot be edited in current status'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|in:email,push,in_app,sms',
            'schedule_type' => 'nullable|in:immediate,scheduled,recurring',
            'scheduled_at' => 'nullable|date|after:now',
            'content' => 'nullable|array',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $campaign->update(array_filter($request->all()));

            return response()->json([
                'success' => true,
                'message' => 'Campaign updated successfully',
                'data' => $campaign->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Launch campaign
     */
    public function launch(MarketingCampaign $campaign): JsonResponse
    {
        if (!$campaign->canBeLaunched()) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign cannot be launched in current status'
            ], 422);
        }

        try {
            $result = $this->campaignService->launchCampaign($campaign);

            return response()->json([
                'success' => true,
                'message' => 'Campaign launched successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to launch campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to launch campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause campaign
     */
    public function pause(MarketingCampaign $campaign): JsonResponse
    {
        if (!$campaign->canBePaused()) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign cannot be paused in current status'
            ], 422);
        }

        try {
            $campaign->update(['status' => MarketingCampaign::STATUS_PAUSED]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign paused successfully',
                'data' => $campaign->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to pause campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to pause campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resume paused campaign
     */
    public function resume(MarketingCampaign $campaign): JsonResponse
    {
        if ($campaign->status !== MarketingCampaign::STATUS_PAUSED) {
            return response()->json([
                'success' => false,
                'message' => 'Only paused campaigns can be resumed'
            ], 422);
        }

        try {
            $campaign->update(['status' => MarketingCampaign::STATUS_ACTIVE]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign resumed successfully',
                'data' => $campaign->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to resume campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resume campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel campaign
     */
    public function cancel(MarketingCampaign $campaign): JsonResponse
    {
        if ($campaign->status === MarketingCampaign::STATUS_COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Completed campaigns cannot be cancelled'
            ], 422);
        }

        try {
            $campaign->update(['status' => MarketingCampaign::STATUS_CANCELLED]);

            return response()->json([
                'success' => true,
                'message' => 'Campaign cancelled successfully',
                'data' => $campaign->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cancel campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete campaign
     */
    public function destroy(MarketingCampaign $campaign): JsonResponse
    {
        if (!in_array($campaign->status, [MarketingCampaign::STATUS_DRAFT, MarketingCampaign::STATUS_CANCELLED])) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft or cancelled campaigns can be deleted'
            ], 422);
        }

        try {
            $campaign->delete();

            return response()->json([
                'success' => true,
                'message' => 'Campaign deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete campaign',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get campaign analytics
     */
    public function analytics(MarketingCampaign $campaign): JsonResponse
    {
        try {
            $analytics = $this->campaignService->getCampaignAnalytics($campaign);

            return response()->json([
                'success' => true,
                'data' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch campaign analytics', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch campaign analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track email open (pixel tracking)
     */
    public function trackOpen(Request $request, string $campaignId, string $userId): JsonResponse
    {
        try {
            $campaign = MarketingCampaign::findOrFail($campaignId);
            $email = $campaign->emails()
                ->where('user_id', $userId)
                ->first();

            if ($email) {
                $email->markAsOpened();
                
                // Record interaction
                $campaign->interactions()->create([
                    'user_id' => $userId,
                    'action' => 'open',
                    'timestamp' => now(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip()
                    ]
                ]);
            }

            // Return 1x1 transparent pixel
            return response()->json(['success' => true], 200, [
                'Content-Type' => 'image/gif'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to track email open', [
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Track email click
     */
    public function trackClick(Request $request, string $campaignId, string $userId): JsonResponse
    {
        try {
            $campaign = MarketingCampaign::findOrFail($campaignId);
            $email = $campaign->emails()
                ->where('user_id', $userId)
                ->first();

            if ($email) {
                $email->markAsClicked();
                
                // Record interaction
                $campaign->interactions()->create([
                    'user_id' => $userId,
                    'action' => 'click',
                    'timestamp' => now(),
                    'metadata' => [
                        'url' => $request->get('url'),
                        'user_agent' => $request->userAgent(),
                        'ip_address' => $request->ip()
                    ]
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to track email click', [
                'campaign_id' => $campaignId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Process scheduled campaigns
     */
    public function processScheduled(): JsonResponse
    {
        try {
            $scheduledCampaigns = MarketingCampaign::where('status', MarketingCampaign::STATUS_SCHEDULED)
                ->where('scheduled_at', '<=', now())
                ->get();

            $processed = 0;
            foreach ($scheduledCampaigns as $campaign) {
                try {
                    $this->campaignService->processCampaign($campaign);
                    $campaign->update(['status' => MarketingCampaign::STATUS_ACTIVE]);
                    $processed++;
                } catch (\Exception $e) {
                    Log::error('Failed to process scheduled campaign', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Processed {$processed} scheduled campaigns"
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process scheduled campaigns', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process scheduled campaigns',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}