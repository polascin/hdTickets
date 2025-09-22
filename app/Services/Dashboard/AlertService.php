<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Models\User;
use App\Models\TicketAlert;
use App\Models\ScrapedTicket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

/**
 * AlertService - Ticket Alert Management and Matching
 * 
 * Handles creation, management, and processing of ticket alerts.
 * Provides intelligent matching algorithms and notification systems.
 * 
 * Features:
 * - Alert creation and management
 * - Intelligent ticket matching
 * - Price monitoring and notifications
 * - Performance analytics and optimization
 * - Bulk alert processing
 * - Alert recommendation engine
 */
class AlertService
{
    protected const CACHE_TTL_MINUTES = 5;
    protected const CACHE_TTL_HOURLY = 60;
    protected const MAX_ALERTS_PER_USER = 50;
    protected const PRICE_TOLERANCE_PERCENTAGE = 5;

    /**
     * Get user alert statistics for dashboard
     */
    public function getUserAlertStats(User $user): array
    {
        $cacheKey = "user_alert_stats:{$user->id}";
        
        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL_MINUTES), function () use ($user) {
            try {
                $alerts = TicketAlert::where('user_id', $user->id)->get();
                
                return [
                    'total_alerts' => $alerts->count(),
                    'active_alerts' => $alerts->where('status', 'active')->count(),
                    'triggered_today' => $this->getAlertsTriggeredToday($user),
                    'triggered_this_week' => $this->getAlertsTriggeredThisWeek($user),
                    'success_rate' => $this->calculateAlertSuccessRate($alerts),
                    'avg_response_time' => $this->calculateAverageResponseTime($alerts),
                    'top_performing_alerts' => $this->getTopPerformingAlerts($alerts),
                    'alert_categories' => $this->getAlertCategories($alerts),
                    'recent_matches' => $this->getRecentMatches($user),
                    'optimization_suggestions' => $this->getOptimizationSuggestions($alerts),
                    'generated_at' => now()->toISOString()
                ];
            } catch (\Exception $e) {
                Log::error('Failed to get user alert stats', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                return $this->getEmptyAlertStats();
            }
        });
    }

    /**
     * Create a new ticket alert
     */
    public function createAlert(User $user, array $alertData): TicketAlert
    {
        try {
            // Validate user alert limit
            $alertCount = TicketAlert::where('user_id', $user->id)->count();
            if ($alertCount >= self::MAX_ALERTS_PER_USER) {
                throw new \Exception("Maximum number of alerts ({$alertCount}) reached");
            }

            // Validate alert data
            $this->validateAlertData($alertData);

            // Create the alert
            $alert = TicketAlert::create([
                'user_id' => $user->id,
                'name' => $alertData['name'] ?? $this->generateAlertName($alertData),
                'alert_type' => $alertData['type'] ?? 'general',
                'criteria' => $this->buildAlertCriteria($alertData),
                'notification_preferences' => $alertData['notifications'] ?? [],
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Clear related caches
            $this->clearUserAlertCache($user);

            Log::info('Alert created successfully', [
                'user_id' => $user->id,
                'alert_id' => $alert->id,
                'alert_name' => $alert->name
            ]);

            return $alert;
        } catch (\Exception $e) {
            Log::error('Failed to create alert', [
                'user_id' => $user->id,
                'alert_data' => $alertData,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing alert
     */
    public function updateAlert(User $user, int $alertId, array $alertData): TicketAlert
    {
        try {
            $alert = TicketAlert::where('user_id', $user->id)
                ->where('id', $alertId)
                ->firstOrFail();

            $this->validateAlertData($alertData);

            $alert->update([
                'name' => $alertData['name'] ?? $alert->name,
                'alert_type' => $alertData['type'] ?? $alert->alert_type,
                'criteria' => $this->buildAlertCriteria($alertData),
                'notification_preferences' => $alertData['notifications'] ?? $alert->notification_preferences,
                'status' => $alertData['status'] ?? $alert->status,
                'updated_at' => now()
            ]);

            $this->clearUserAlertCache($user);

            Log::info('Alert updated successfully', [
                'user_id' => $user->id,
                'alert_id' => $alert->id
            ]);

            return $alert;
        } catch (\Exception $e) {
            Log::error('Failed to update alert', [
                'user_id' => $user->id,
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete an alert
     */
    public function deleteAlert(User $user, int $alertId): bool
    {
        try {
            $alert = TicketAlert::where('user_id', $user->id)
                ->where('id', $alertId)
                ->firstOrFail();

            $alert->delete();
            $this->clearUserAlertCache($user);

            Log::info('Alert deleted successfully', [
                'user_id' => $user->id,
                'alert_id' => $alertId
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete alert', [
                'user_id' => $user->id,
                'alert_id' => $alertId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Process alerts against new tickets
     */
    public function processAlertsForTickets(Collection $tickets): array
    {
        try {
            $matchedAlerts = [];
            $processedCount = 0;

            foreach ($tickets as $ticket) {
                $alerts = $this->findMatchingAlerts($ticket);
                foreach ($alerts as $alert) {
                    if ($this->matchesAlertCriteria($ticket, $alert)) {
                        $matchedAlerts[] = [
                            'alert' => $alert,
                            'ticket' => $ticket,
                            'match_score' => $this->calculateMatchScore($ticket, $alert)
                        ];
                        
                        $this->recordAlertMatch($alert, $ticket);
                        $processedCount++;
                    }
                }
            }

            Log::info('Alert processing completed', [
                'tickets_processed' => $tickets->count(),
                'alerts_matched' => count($matchedAlerts),
                'total_matches' => $processedCount
            ]);

            return $matchedAlerts;
        } catch (\Exception $e) {
            Log::error('Failed to process alerts', [
                'ticket_count' => $tickets->count(),
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get suggested alerts for user
     */
    public function getSuggestedAlerts(User $user): array
    {
        try {
            $preferences = $user->preferences ?? [];
            $existingAlerts = TicketAlert::where('user_id', $user->id)->get();
            $suggestions = [];

            // Suggest sport-based alerts
            if (!empty($preferences['favorite_sports'])) {
                foreach ($preferences['favorite_sports'] as $sport) {
                    if (!$this->hasAlertForSport($existingAlerts, $sport)) {
                        $suggestions[] = [
                            'type' => 'sport',
                            'name' => "New {$sport} Events",
                            'description' => "Get notified about new {$sport} tickets",
                            'criteria' => ['sport' => $sport],
                            'priority' => 'high'
                        ];
                    }
                }
            }

            // Suggest team-based alerts
            if (!empty($preferences['favorite_teams'])) {
                foreach ($preferences['favorite_teams'] as $team) {
                    if (!$this->hasAlertForTeam($existingAlerts, $team)) {
                        $suggestions[] = [
                            'type' => 'team',
                            'name' => "{$team} Games",
                            'description' => "Get notified about {$team} tickets",
                            'criteria' => ['team' => $team],
                            'priority' => 'high'
                        ];
                    }
                }
            }

            // Suggest price drop alerts
            if (!empty($preferences['price_range']['max'])) {
                $maxPrice = $preferences['price_range']['max'];
                $suggestions[] = [
                    'type' => 'price_drop',
                    'name' => "Price Drops Under $" . $maxPrice,
                    'description' => "Get notified when tickets drop below your price range",
                    'criteria' => ['max_price' => $maxPrice],
                    'priority' => 'medium'
                ];
            }

            return array_slice($suggestions, 0, 5); // Limit to 5 suggestions
        } catch (\Exception $e) {
            Log::warning('Failed to get suggested alerts', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    // Helper methods for alert statistics
    protected function getAlertsTriggeredToday(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereDate('last_triggered_at', Carbon::today())
            ->count();
    }

    protected function getAlertsTriggeredThisWeek(User $user): int
    {
        return TicketAlert::where('user_id', $user->id)
            ->whereBetween('last_triggered_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])
            ->count();
    }

    protected function calculateAlertSuccessRate(Collection $alerts): float
    {
        if ($alerts->isEmpty()) {
            return 0.0;
        }

        $successfulAlerts = $alerts->filter(fn($alert) => ($alert->matches_count ?? 0) > 0);
        return round(($successfulAlerts->count() / $alerts->count()) * 100, 1);
    }

    protected function calculateAverageResponseTime(Collection $alerts): float
    {
        $responseTimes = [];
        
        foreach ($alerts as $alert) {
            if ($alert->last_triggered_at && $alert->created_at) {
                $responseTimes[] = $alert->created_at->diffInHours($alert->last_triggered_at);
            }
        }

        return count($responseTimes) > 0 ? round(array_sum($responseTimes) / count($responseTimes), 1) : 0;
    }

    protected function getTopPerformingAlerts(Collection $alerts): array
    {
        return $alerts->sortByDesc('matches_count')
            ->take(3)
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'name' => $alert->name ?? 'Unnamed Alert',
                    'matches' => $alert->matches_count ?? 0,
                    'type' => $alert->alert_type,
                    'created_at' => $alert->created_at?->format('M j, Y')
                ];
            })
            ->values()
            ->toArray();
    }

    protected function getAlertCategories(Collection $alerts): array
    {
        return [
            'price_drop' => $alerts->where('alert_type', 'price_drop')->count(),
            'availability' => $alerts->where('alert_type', 'availability')->count(),
            'event_specific' => $alerts->where('alert_type', 'event_specific')->count(),
            'team_specific' => $alerts->where('alert_type', 'team_specific')->count(),
            'sport_specific' => $alerts->where('alert_type', 'sport_specific')->count(),
            'general' => $alerts->whereNull('alert_type')->count()
        ];
    }

    protected function getRecentMatches(User $user, int $limit = 5): array
    {
        // This would require a matches/notifications table
        // For now, return mock data based on alerts
        $alerts = TicketAlert::where('user_id', $user->id)
            ->whereNotNull('last_triggered_at')
            ->orderBy('last_triggered_at', 'desc')
            ->take($limit)
            ->get();

        return $alerts->map(function ($alert) {
            return [
                'alert_name' => $alert->name ?? 'Unnamed Alert',
                'matched_at' => $alert->last_triggered_at?->diffForHumans(),
                'match_type' => 'New tickets found',
                'alert_id' => $alert->id
            ];
        })->toArray();
    }

    protected function getOptimizationSuggestions(Collection $alerts): array
    {
        $suggestions = [];

        // Check for inactive alerts
        $inactiveAlerts = $alerts->whereNull('last_triggered_at')
            ->where('created_at', '<', now()->subWeeks(2));

        if ($inactiveAlerts->count() > 0) {
            $suggestions[] = "Consider reviewing {$inactiveAlerts->count()} inactive alerts";
        }

        // Check for similar alerts
        $duplicateTypes = $alerts->groupBy('alert_type')
            ->filter(fn($group) => $group->count() > 3);

        if ($duplicateTypes->count() > 0) {
            $suggestions[] = "Consider consolidating similar alert types";
        }

        // Check for overly broad criteria
        $broadAlerts = $alerts->filter(function ($alert) {
            $criteria = $alert->criteria ?? [];
            return empty($criteria) || (count($criteria) === 1 && isset($criteria['sport']));
        });

        if ($broadAlerts->count() > 2) {
            $suggestions[] = "Add more specific criteria to improve alert precision";
        }

        return $suggestions;
    }

    protected function getEmptyAlertStats(): array
    {
        return [
            'total_alerts' => 0,
            'active_alerts' => 0,
            'triggered_today' => 0,
            'triggered_this_week' => 0,
            'success_rate' => 0,
            'avg_response_time' => 0,
            'top_performing_alerts' => [],
            'alert_categories' => [],
            'recent_matches' => [],
            'optimization_suggestions' => ['Create your first alert to start monitoring tickets'],
            'generated_at' => now()->toISOString()
        ];
    }

    // Helper methods for alert management
    protected function validateAlertData(array $data): void
    {
        if (empty($data['criteria'])) {
            throw new \InvalidArgumentException('Alert criteria cannot be empty');
        }

        if (isset($data['criteria']['max_price']) && $data['criteria']['max_price'] <= 0) {
            throw new \InvalidArgumentException('Maximum price must be greater than 0');
        }
    }

    protected function generateAlertName(array $data): string
    {
        $criteria = $data['criteria'] ?? [];
        $parts = [];

        if (isset($criteria['sport'])) {
            $parts[] = $criteria['sport'];
        }

        if (isset($criteria['team'])) {
            $parts[] = $criteria['team'];
        }

        if (isset($criteria['max_price'])) {
            $parts[] = "under $" . $criteria['max_price'];
        }

        return !empty($parts) ? implode(' ', $parts) : 'Custom Alert';
    }

    protected function buildAlertCriteria(array $data): array
    {
        $criteria = $data['criteria'] ?? [];
        
        // Normalize criteria format
        $normalized = [];
        
        if (isset($criteria['sport'])) {
            $normalized['sport'] = $criteria['sport'];
        }
        
        if (isset($criteria['team'])) {
            $normalized['team'] = $criteria['team'];
        }
        
        if (isset($criteria['venue'])) {
            $normalized['venue'] = $criteria['venue'];
        }
        
        if (isset($criteria['max_price'])) {
            $normalized['max_price'] = (float) $criteria['max_price'];
        }
        
        if (isset($criteria['min_price'])) {
            $normalized['min_price'] = (float) $criteria['min_price'];
        }

        return $normalized;
    }

    // Helper methods for alert processing
    protected function findMatchingAlerts(ScrapedTicket $ticket): Collection
    {
        return TicketAlert::where('status', 'active')
            ->whereRaw("JSON_EXTRACT(criteria, '$.sport') IS NULL OR JSON_EXTRACT(criteria, '$.sport') = ?", [$ticket->sport])
            ->get()
            ->filter(function ($alert) use ($ticket) {
                return $this->preFilterAlert($alert, $ticket);
            });
    }

    protected function preFilterAlert(TicketAlert $alert, ScrapedTicket $ticket): bool
    {
        $criteria = $alert->criteria ?? [];

        // Quick filters before detailed matching
        if (isset($criteria['sport']) && $criteria['sport'] !== $ticket->sport) {
            return false;
        }

        if (isset($criteria['max_price']) && $ticket->price > $criteria['max_price']) {
            return false;
        }

        return true;
    }

    protected function matchesAlertCriteria(ScrapedTicket $ticket, TicketAlert $alert): bool
    {
        $criteria = $alert->criteria ?? [];
        $matchScore = 0;
        $totalCriteria = count($criteria);

        if ($totalCriteria === 0) {
            return false; // No criteria means no match
        }

        // Sport matching
        if (isset($criteria['sport'])) {
            if (strtolower($ticket->sport) === strtolower($criteria['sport'])) {
                $matchScore++;
            } else {
                return false; // Sport is usually mandatory
            }
        }

        // Team matching
        if (isset($criteria['team'])) {
            $team = strtolower($criteria['team']);
            if (stripos($ticket->home_team, $team) !== false || 
                stripos($ticket->away_team, $team) !== false) {
                $matchScore++;
            } else {
                return false; // Team matching is strict
            }
        }

        // Price range matching
        if (isset($criteria['max_price']) && $ticket->price <= $criteria['max_price']) {
            $matchScore++;
        } elseif (isset($criteria['max_price'])) {
            return false; // Exceeds max price
        }

        if (isset($criteria['min_price']) && $ticket->price >= $criteria['min_price']) {
            $matchScore++;
        } elseif (isset($criteria['min_price'])) {
            return false; // Below min price
        }

        // Venue matching (flexible)
        if (isset($criteria['venue'])) {
            if (stripos($ticket->venue, $criteria['venue']) !== false) {
                $matchScore++;
            }
            // Don't fail for venue mismatch, just don't count it
        }

        // Require at least 80% criteria match
        return ($matchScore / $totalCriteria) >= 0.8;
    }

    protected function calculateMatchScore(ScrapedTicket $ticket, TicketAlert $alert): float
    {
        $criteria = $alert->criteria ?? [];
        $score = 0.0;
        $weights = [];

        // Sport match (high weight)
        if (isset($criteria['sport']) && strtolower($ticket->sport) === strtolower($criteria['sport'])) {
            $score += 0.4;
        }
        $weights[] = 0.4;

        // Team match (high weight)
        if (isset($criteria['team'])) {
            $team = strtolower($criteria['team']);
            if (stripos($ticket->home_team, $team) !== false || stripos($ticket->away_team, $team) !== false) {
                $score += 0.3;
            }
        }
        $weights[] = 0.3;

        // Price match (medium weight)
        if (isset($criteria['max_price'])) {
            $priceFit = 1 - (max(0, $ticket->price - $criteria['max_price']) / $criteria['max_price']);
            $score += $priceFit * 0.2;
        }
        $weights[] = 0.2;

        // Venue match (low weight)
        if (isset($criteria['venue'])) {
            if (stripos($ticket->venue, $criteria['venue']) !== false) {
                $score += 0.1;
            }
        }
        $weights[] = 0.1;

        return min(1.0, $score / array_sum($weights));
    }

    protected function recordAlertMatch(TicketAlert $alert, ScrapedTicket $ticket): void
    {
        try {
            $alert->increment('matches_count');
            $alert->update(['last_triggered_at' => now()]);

            Log::debug('Alert match recorded', [
                'alert_id' => $alert->id,
                'ticket_id' => $ticket->id,
                'user_id' => $alert->user_id
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to record alert match', [
                'alert_id' => $alert->id,
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    // Helper methods for alert suggestions
    protected function hasAlertForSport(Collection $alerts, string $sport): bool
    {
        return $alerts->contains(function ($alert) use ($sport) {
            $criteria = $alert->criteria ?? [];
            return isset($criteria['sport']) && strtolower($criteria['sport']) === strtolower($sport);
        });
    }

    protected function hasAlertForTeam(Collection $alerts, string $team): bool
    {
        return $alerts->contains(function ($alert) use ($team) {
            $criteria = $alert->criteria ?? [];
            return isset($criteria['team']) && stripos($criteria['team'], $team) !== false;
        });
    }

    /**
     * Clear user alert cache
     */
    protected function clearUserAlertCache(User $user): void
    {
        $cacheKeys = [
            "user_alert_stats:{$user->id}",
            "user_metrics_dashboard:{$user->id}", // This may use alert data
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}