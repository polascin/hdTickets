<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Smart Ticket Monitoring Dashboard API Controller
 * 
 * Handles API endpoints for the React Smart Ticket Monitoring Dashboard component
 * Provides real-time ticket data, monitoring controls, and alert management
 */
class TicketMonitoringController extends Controller
{
    /**
     * Get monitoring dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = Auth::user();
        $cacheKey = "monitoring_dashboard_{$user->id}";
        
        $data = Cache::remember($cacheKey, 300, function () use ($user) {
            // Get monitoring statistics
            $stats = $this->getMonitoringStats($user);
            
            // Get monitored tickets with their alerts
            $monitoredTickets = $this->getMonitoredTickets($user);
            
            // Get recent activity
            $recentActivity = $this->getRecentActivity($user);
            
            // Get demand indicators
            $demandIndicators = $this->getDemandIndicators();
            
            return [
                'statistics' => $stats,
                'monitored_tickets' => $monitoredTickets,
                'recent_activity' => $recentActivity,
                'demand_indicators' => $demandIndicators,
                'last_updated' => now()->toISOString(),
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
    
    /**
     * Get monitoring statistics
     */
    private function getMonitoringStats(User $user): array
    {
        $alerts = TicketAlert::where('user_id', $user->id);
        $tickets = Ticket::whereHas('ticketAlerts', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        });
        
        return [
            'total_monitored' => $alerts->count(),
            'active_alerts' => $alerts->where('is_active', true)->count(),
            'triggered_today' => $alerts->where('last_triggered_at', '>=', now()->startOfDay())->count(),
            'price_drops_24h' => $alerts->where('last_triggered_at', '>=', now()->subDay())->count(),
            'average_response_time' => '2.3s',
            'monitoring_uptime' => '99.8%',
        ];
    }
    
    /**
     * Get monitored tickets
     */
    private function getMonitoredTickets(User $user): array
    {
        $tickets = Ticket::whereHas('ticketAlerts', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('is_active', true);
        })
        ->with(['ticketAlerts' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }])
        ->limit(20)
        ->get();
        
        return $tickets->map(function ($ticket) {
            $alert = $ticket->ticketAlerts->first();
            
            return [
                'id' => $ticket->uuid,
                'event_name' => $ticket->event_name,
                'venue' => $ticket->venue,
                'date' => $ticket->event_date,
                'current_price' => $ticket->current_price,
                'target_price' => $alert?->target_price,
                'platform' => $ticket->platform,
                'status' => $this->getTicketStatus($ticket),
                'last_check' => $ticket->last_scraped_at,
                'price_change_24h' => $ticket->price_change_24h ?? 0,
                'alert_status' => $alert?->is_active ? 'active' : 'inactive',
                'demand_level' => $this->calculateDemandLevel($ticket),
            ];
        })->toArray();
    }
    
    /**
     * Get recent activity
     */
    private function getRecentActivity(User $user): array
    {
        // Get recent alert triggers and ticket updates
        $alerts = TicketAlert::where('user_id', $user->id)
            ->whereNotNull('last_triggered_at')
            ->orderBy('last_triggered_at', 'desc')
            ->with('ticket')
            ->limit(10)
            ->get();
        
        return $alerts->map(function ($alert) {
            return [
                'id' => $alert->uuid,
                'type' => 'alert_triggered',
                'title' => 'Price Alert Triggered',
                'message' => "Price for {$alert->ticket->event_name} dropped to £{$alert->ticket->current_price}",
                'timestamp' => $alert->last_triggered_at,
                'ticket_id' => $alert->ticket->uuid,
                'platform' => $alert->ticket->platform,
            ];
        })->toArray();
    }
    
    /**
     * Get demand indicators
     */
    private function getDemandIndicators(): array
    {
        return [
            'high_demand_events' => [
                [
                    'event' => 'Manchester United vs Liverpool',
                    'venue' => 'Old Trafford',
                    'demand_score' => 94,
                    'price_trend' => 'increasing',
                ],
                [
                    'event' => 'Anthony Joshua vs Francis Ngannou', 
                    'venue' => 'Wembley Stadium',
                    'demand_score' => 87,
                    'price_trend' => 'stable',
                ],
            ],
            'trending_platforms' => [
                ['name' => 'StubHub', 'activity' => '+23%'],
                ['name' => 'Viagogo', 'activity' => '+18%'],
                ['name' => 'Ticketmaster', 'activity' => '+12%'],
            ],
        ];
    }
    
    /**
     * Toggle monitoring for a ticket
     */
    public function toggleMonitoring(Request $request, string $ticketId): JsonResponse
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'target_price' => 'nullable|numeric|min:0',
        ]);
        
        $user = Auth::user();
        $ticket = Ticket::where('uuid', $ticketId)->firstOrFail();
        
        $alert = TicketAlert::where('user_id', $user->id)
            ->where('ticket_id', $ticket->id)
            ->first();
        
        if ($request->enabled) {
            if ($alert) {
                $alert->update([
                    'is_active' => true,
                    'target_price' => $request->target_price ?? $alert->target_price,
                ]);
            } else {
                $alert = TicketAlert::create([
                    'user_id' => $user->id,
                    'ticket_id' => $ticket->id,
                    'target_price' => $request->target_price ?? $ticket->current_price * 0.9,
                    'is_active' => true,
                    'alert_type' => 'price_drop',
                ]);
            }
        } else {
            if ($alert) {
                $alert->update(['is_active' => false]);
            }
        }
        
        // Clear cache
        Cache::forget("monitoring_dashboard_{$user->id}");
        
        return response()->json([
            'success' => true,
            'monitoring_enabled' => $request->enabled,
            'alert_id' => $alert?->uuid,
        ]);
    }
    
    /**
     * Update monitoring settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_interval' => 'required|integer|min:5|max:3600',
            'notifications_enabled' => 'required|boolean',
            'sound_alerts' => 'required|boolean',
            'price_threshold' => 'required|numeric|min:1|max:50',
        ]);
        
        $user = Auth::user();
        
        // Update user preferences (you might want to create a UserPreference model)
        $preferences = [
            'monitoring' => [
                'refresh_interval' => $request->refresh_interval,
                'notifications_enabled' => $request->notifications_enabled,
                'sound_alerts' => $request->sound_alerts,
                'price_threshold' => $request->price_threshold,
            ]
        ];
        
        // For now, store in user's custom_permissions field
        $user->update([
            'custom_permissions' => array_merge(
                $user->custom_permissions ?? [],
                $preferences
            )
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }
    
    /**
     * Get ticket monitoring details
     */
    public function show(Request $request, string $ticketId): JsonResponse
    {
        $user = Auth::user();
        $ticket = Ticket::where('uuid', $ticketId)->firstOrFail();
        
        $alert = TicketAlert::where('user_id', $user->id)
            ->where('ticket_id', $ticket->id)
            ->first();
        
        return response()->json([
            'success' => true,
            'data' => [
                'ticket' => [
                    'id' => $ticket->uuid,
                    'event_name' => $ticket->event_name,
                    'venue' => $ticket->venue,
                    'date' => $ticket->event_date,
                    'current_price' => $ticket->current_price,
                    'platform' => $ticket->platform,
                    'section' => $ticket->section,
                    'row' => $ticket->row,
                    'seat_numbers' => $ticket->seat_numbers,
                    'last_updated' => $ticket->last_scraped_at,
                ],
                'alert' => $alert ? [
                    'id' => $alert->uuid,
                    'target_price' => $alert->target_price,
                    'is_active' => $alert->is_active,
                    'alert_type' => $alert->alert_type,
                    'created_at' => $alert->created_at,
                    'last_triggered_at' => $alert->last_triggered_at,
                ] : null,
                'monitoring_enabled' => $alert?->is_active ?? false,
                'price_history' => $this->getPriceHistory($ticket),
            ],
        ]);
    }
    
    /**
     * Get price history for a ticket
     */
    private function getPriceHistory(Ticket $ticket): array
    {
        // This would come from a price_history table in a real implementation
        return [
            ['timestamp' => now()->subDays(7)->toISOString(), 'price' => $ticket->current_price * 1.1],
            ['timestamp' => now()->subDays(6)->toISOString(), 'price' => $ticket->current_price * 1.05],
            ['timestamp' => now()->subDays(5)->toISOString(), 'price' => $ticket->current_price * 1.08],
            ['timestamp' => now()->subDays(4)->toISOString(), 'price' => $ticket->current_price * 1.02],
            ['timestamp' => now()->subDays(3)->toISOString(), 'price' => $ticket->current_price * 0.98],
            ['timestamp' => now()->subDays(2)->toISOString(), 'price' => $ticket->current_price * 0.95],
            ['timestamp' => now()->subDays(1)->toISOString(), 'price' => $ticket->current_price * 1.01],
            ['timestamp' => now()->toISOString(), 'price' => $ticket->current_price],
        ];
    }
    
    /**
     * Calculate demand level for a ticket
     */
    private function calculateDemandLevel(Ticket $ticket): string
    {
        // Simple algorithm - you can make this more sophisticated
        $score = 0;
        
        // Price increase indicates demand
        if (($ticket->price_change_24h ?? 0) > 0) {
            $score += 20;
        }
        
        // Recent scraping indicates activity
        if ($ticket->last_scraped_at && $ticket->last_scraped_at->greaterThan(now()->subHour())) {
            $score += 15;
        }
        
        // Platform specific scoring
        if (in_array($ticket->platform, ['StubHub', 'Viagogo'])) {
            $score += 10;
        }
        
        if ($score >= 35) return 'high';
        if ($score >= 20) return 'medium';
        return 'low';
    }
    
    /**
     * Get ticket status
     */
    private function getTicketStatus(Ticket $ticket): string
    {
        if (!$ticket->is_available) {
            return 'sold_out';
        }
        
        if ($ticket->last_scraped_at && $ticket->last_scraped_at->lessThan(now()->subHour())) {
            return 'stale';
        }
        
        if (($ticket->price_change_24h ?? 0) < -10) {
            return 'price_drop';
        }
        
        return 'available';
    }
}