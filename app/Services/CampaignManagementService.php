<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CampaignAnalytics;
use App\Models\CampaignEmail;
use App\Models\CampaignTarget;
use App\Models\MarketingCampaign;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use function is_string;

/**
 * Campaign Management Service
 *
 * Comprehensive marketing automation service providing:
 * - Campaign creation and management
 * - Audience segmentation and targeting
 * - Email marketing automation
 * - Performance tracking and analytics
 * - A/B testing and optimization
 */
class CampaignManagementService
{
    /**
     * Create new marketing campaign
     */
    public function createCampaign(array $campaignData): MarketingCampaign
    {
        DB::beginTransaction();

        try {
            $campaign = MarketingCampaign::create([
                'name'            => $campaignData['name'],
                'description'     => $campaignData['description'] ?? NULL,
                'type'            => $campaignData['type'], // email, push, in_app, sms
                'status'          => 'draft',
                'target_audience' => $campaignData['target_audience'] ?? 'all',
                'schedule_type'   => $campaignData['schedule_type'] ?? 'immediate', // immediate, scheduled, recurring
                'scheduled_at'    => $campaignData['scheduled_at'] ?? NULL,
                'content'         => $campaignData['content'],
                'settings'        => $campaignData['settings'] ?? [],
                'created_by'      => auth()->id(),
            ]);

            // Create campaign targets based on audience criteria
            $this->createCampaignTargets($campaign, $campaignData['target_criteria'] ?? []);

            // Create A/B test variants if specified
            if (isset($campaignData['ab_test']) && $campaignData['ab_test']) {
                $this->createABTestVariants($campaign, $campaignData['variants'] ?? []);
            }

            DB::commit();

            Log::info('Marketing campaign created', [
                'campaign_id' => $campaign->id,
                'name'        => $campaign->name,
                'type'        => $campaign->type,
                'created_by'  => auth()->id(),
            ]);

            return $campaign;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to create campaign', [
                'error'         => $e->getMessage(),
                'campaign_data' => $campaignData,
            ]);

            throw $e;
        }
    }

    /**
     * Launch campaign
     */
    public function launchCampaign(MarketingCampaign $campaign): array
    {
        if ($campaign->status !== 'draft') {
            throw new Exception('Campaign must be in draft status to launch');
        }

        DB::beginTransaction();

        try {
            // Update campaign status
            $campaign->update([
                'status'      => $campaign->schedule_type === 'immediate' ? 'active' : 'scheduled',
                'launched_at' => now(),
            ]);

            // Process immediate campaigns
            if ($campaign->schedule_type === 'immediate') {
                $result = $this->processCampaign($campaign);
            } else {
                // Schedule campaign for later execution
                $result = $this->scheduleCampaign($campaign);
            }

            DB::commit();

            Log::info('Campaign launched', [
                'campaign_id'   => $campaign->id,
                'name'          => $campaign->name,
                'schedule_type' => $campaign->schedule_type,
            ]);

            return $result;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to launch campaign', [
                'campaign_id' => $campaign->id,
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Process campaign execution
     */
    public function processCampaign(MarketingCampaign $campaign): array
    {
        $targets = $campaign->targets()->with('user')->get();
        $results = [
            'total_targets' => $targets->count(),
            'sent'          => 0,
            'failed'        => 0,
            'errors'        => [],
        ];

        foreach ($targets as $target) {
            try {
                $sent = $this->sendCampaignMessage($campaign, $target->user);

                if ($sent) {
                    $results['sent']++;
                    $this->recordCampaignInteraction($campaign, $target->user, 'sent');
                } else {
                    $results['failed']++;
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'user_id' => $target->user_id,
                    'error'   => $e->getMessage(),
                ];

                Log::error('Campaign message failed', [
                    'campaign_id' => $campaign->id,
                    'user_id'     => $target->user_id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }

        // Update campaign analytics
        $this->updateCampaignAnalytics($campaign, $results);

        return $results;
    }

    /**
     * Get campaign performance analytics
     */
    public function getCampaignAnalytics(MarketingCampaign $campaign): array
    {
        $analytics = $campaign->analytics;
        $interactions = $campaign->interactions()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        return [
            'campaign_info' => [
                'id'          => $campaign->id,
                'name'        => $campaign->name,
                'type'        => $campaign->type,
                'status'      => $campaign->status,
                'created_at'  => $campaign->created_at,
                'launched_at' => $campaign->launched_at,
            ],
            'delivery_metrics' => [
                'total_targets'   => $analytics->total_targets ?? 0,
                'messages_sent'   => $analytics->messages_sent ?? 0,
                'messages_failed' => $analytics->messages_failed ?? 0,
                'delivery_rate'   => $analytics->delivery_rate ?? 0,
            ],
            'engagement_metrics' => [
                'opens'           => $interactions['open'] ?? 0,
                'clicks'          => $interactions['click'] ?? 0,
                'conversions'     => $interactions['conversion'] ?? 0,
                'unsubscribes'    => $interactions['unsubscribe'] ?? 0,
                'open_rate'       => $this->calculateRate($interactions['open'] ?? 0, $analytics->messages_sent ?? 0),
                'click_rate'      => $this->calculateRate($interactions['click'] ?? 0, $analytics->messages_sent ?? 0),
                'conversion_rate' => $this->calculateRate($interactions['conversion'] ?? 0, $analytics->messages_sent ?? 0),
            ],
            'revenue_impact'     => $this->calculateRevenueImpact($campaign),
            'performance_trends' => $this->getPerformanceTrends($campaign),
        ];
    }

    /**
     * Get all campaigns with analytics
     */
    public function getAllCampaignsWithAnalytics(): array
    {
        $campaigns = MarketingCampaign::with(['analytics', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        return $campaigns->map(function ($campaign) {
            $analytics = $campaign->analytics;

            return [
                'id'          => $campaign->id,
                'name'        => $campaign->name,
                'type'        => $campaign->type,
                'status'      => $campaign->status,
                'created_at'  => $campaign->created_at,
                'launched_at' => $campaign->launched_at,
                'created_by'  => $campaign->creator->name ?? 'Unknown',
                'metrics'     => [
                    'targets'          => $analytics->total_targets ?? 0,
                    'sent'             => $analytics->messages_sent ?? 0,
                    'delivery_rate'    => $analytics->delivery_rate ?? 0,
                    'engagement_score' => $this->calculateEngagementScore($campaign),
                ],
            ];
        })->toArray();
    }

    /**
     * Send campaign message to user
     */
    private function sendCampaignMessage(MarketingCampaign $campaign, User $user): bool
    {
        return match ($campaign->type) {
            'email'  => $this->sendEmailCampaign($campaign, $user),
            'push'   => $this->sendPushNotification($campaign, $user),
            'in_app' => $this->sendInAppNotification($campaign, $user),
            'sms'    => $this->sendSMSCampaign($campaign, $user),
            default  => FALSE,
        };
    }

    /**
     * Send email campaign
     */
    private function sendEmailCampaign(MarketingCampaign $campaign, User $user): bool
    {
        try {
            $content = $this->personalizeContent($campaign->content, $user);

            // Create email record
            $email = CampaignEmail::create([
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'subject'     => $content['subject'],
                'body'        => $content['body'],
                'status'      => 'pending',
            ]);

            // Send email using Laravel Mail
            Mail::to($user->email)->send(new \App\Mail\CampaignEmail($campaign, $user, $content));

            $email->update([
                'status'  => 'sent',
                'sent_at' => now(),
            ]);

            return TRUE;
        } catch (Exception $e) {
            Log::error('Email campaign failed', [
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'error'       => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    /**
     * Send push notification
     */
    private function sendPushNotification(MarketingCampaign $campaign, User $user): bool
    {
        try {
            // Implementation would integrate with push notification service
            // For now, just log the action
            Log::info('Push notification sent', [
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
            ]);

            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Send in-app notification
     */
    private function sendInAppNotification(MarketingCampaign $campaign, User $user): bool
    {
        try {
            // Create in-app notification
            $user->notifications()->create([
                'type' => 'campaign',
                'data' => [
                    'campaign_id' => $campaign->id,
                    'title'       => $campaign->content['title'] ?? 'Notification',
                    'message'     => $campaign->content['message'] ?? '',
                    'action_url'  => $campaign->content['action_url'] ?? NULL,
                ],
            ]);

            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Send SMS campaign
     */
    private function sendSMSCampaign(MarketingCampaign $campaign, User $user): bool
    {
        try {
            // Implementation would integrate with SMS service
            Log::info('SMS sent', [
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'phone'       => $user->phone,
            ]);

            return TRUE;
        } catch (Exception $e) {
            return FALSE;
        }
    }

    /**
     * Personalize campaign content for user
     */
    private function personalizeContent(array $content, User $user): array
    {
        $personalizedContent = $content;

        // Replace placeholders with user data
        $placeholders = [
            '{{user_name}}'         => $user->name,
            '{{first_name}}'        => $user->first_name ?? $user->name,
            '{{email}}'             => $user->email,
            '{{subscription_plan}}' => $user->subscription_plan ?? 'Free',
            '{{member_since}}'      => $user->created_at->format('M Y'),
        ];

        foreach ($personalizedContent as $key => $value) {
            if (is_string($value)) {
                $personalizedContent[$key] = str_replace(
                    array_keys($placeholders),
                    array_values($placeholders),
                    $value,
                );
            }
        }

        return $personalizedContent;
    }

    /**
     * Create campaign targets based on criteria
     */
    private function createCampaignTargets(MarketingCampaign $campaign, array $criteria): void
    {
        $query = User::query();

        // Apply targeting criteria
        if (isset($criteria['subscription_plan'])) {
            $query->whereIn('subscription_plan', (array) $criteria['subscription_plan']);
        }

        if (isset($criteria['user_role'])) {
            $query->whereIn('role', (array) $criteria['user_role']);
        }

        if (isset($criteria['registration_date'])) {
            $query->whereBetween('created_at', [
                $criteria['registration_date']['start'],
                $criteria['registration_date']['end'],
            ]);
        }

        if (isset($criteria['last_activity'])) {
            $query->where('last_activity_at', '>=', $criteria['last_activity']);
        }

        if (isset($criteria['location'])) {
            // Would filter by user location if available
        }

        if (isset($criteria['engagement_level'])) {
            // Would filter by calculated engagement score
        }

        // Create target records
        $users = $query->get();
        foreach ($users as $user) {
            CampaignTarget::create([
                'campaign_id' => $campaign->id,
                'user_id'     => $user->id,
                'target_type' => 'user',
                'status'      => 'pending',
            ]);
        }
    }

    /**
     * Record campaign interaction
     */
    private function recordCampaignInteraction(MarketingCampaign $campaign, User $user, string $action): void
    {
        CampaignAnalytics::create([
            'campaign_id' => $campaign->id,
            'user_id'     => $user->id,
            'action'      => $action,
            'timestamp'   => now(),
            'metadata'    => [],
        ]);
    }

    /**
     * Update campaign analytics
     */
    private function updateCampaignAnalytics(MarketingCampaign $campaign, array $results): void
    {
        $campaign->analytics()->updateOrCreate(
            ['campaign_id' => $campaign->id],
            [
                'total_targets'   => $results['total_targets'],
                'messages_sent'   => $results['sent'],
                'messages_failed' => $results['failed'],
                'delivery_rate'   => $results['total_targets'] > 0 ?
                    round(($results['sent'] / $results['total_targets']) * 100, 2) : 0,
                'last_updated' => now(),
            ],
        );
    }

    /**
     * Create A/B test variants
     */
    private function createABTestVariants(MarketingCampaign $campaign, array $variants): void
    {
        foreach ($variants as $variant) {
            // Implementation for A/B test variant creation
            // Would create separate campaign variants with different content
        }
    }

    /**
     * Schedule campaign for later execution
     */
    private function scheduleCampaign(MarketingCampaign $campaign): array
    {
        // Implementation would integrate with job scheduling system
        // For now, just return scheduled status
        return [
            'status'       => 'scheduled',
            'scheduled_at' => $campaign->scheduled_at,
            'message'      => 'Campaign scheduled successfully',
        ];
    }

    /**
     * Calculate engagement rate
     */
    private function calculateRate(int $numerator, int $denominator): float
    {
        return $denominator > 0 ? round(($numerator / $denominator) * 100, 2) : 0;
    }

    /**
     * Calculate revenue impact of campaign
     */
    private function calculateRevenueImpact(MarketingCampaign $campaign): array
    {
        // This would track conversions and revenue attributed to the campaign
        return [
            'attributed_revenue' => 0,
            'new_subscriptions'  => 0,
            'upgrades'           => 0,
            'roi'                => 0,
        ];
    }

    /**
     * Get performance trends for campaign
     */
    private function getPerformanceTrends(MarketingCampaign $campaign): array
    {
        $trends = [];
        $days = 7;

        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayInteractions = $campaign->interactions()
                ->whereDate('timestamp', $date)
                ->count();

            $trends[$date] = $dayInteractions;
        }

        return array_reverse($trends, TRUE);
    }

    /**
     * Calculate overall engagement score
     */
    private function calculateEngagementScore(MarketingCampaign $campaign): float
    {
        $analytics = $campaign->analytics;
        if (! $analytics || $analytics->messages_sent === 0) {
            return 0;
        }

        $interactions = $campaign->interactions()
            ->whereIn('action', ['open', 'click', 'conversion'])
            ->count();

        return round(($interactions / $analytics->messages_sent) * 100, 2);
    }
}
