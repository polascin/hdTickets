<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketAlert;
use App\Models\WatchlistItem;
use App\Models\UserPreference;
use App\Models\TicketPurchase;
use App\Services\SyncService;
use Carbon\Carbon;

class SyncController extends Controller
{
    protected $syncService;
    
    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Sync ticket alerts data
     */
    public function syncAlerts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $syncData = $request->json()->all();
            
            Log::info('Syncing alerts for user', ['user_id' => $user->id, 'data_count' => count($syncData)]);
            
            $result = $this->syncService->syncTicketAlerts($user, $syncData);
            
            return response()->json([
                'success' => true,
                'synced_count' => $result['synced_count'],
                'conflicts' => $result['conflicts'] ?? [],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync alerts', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync alerts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync ticket prices data
     */
    public function syncPrices(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $priceUpdates = $request->json()->all();
            
            Log::info('Syncing price updates', ['user_id' => $user->id, 'updates_count' => count($priceUpdates)]);
            
            $result = $this->syncService->syncPriceUpdates($user, $priceUpdates);
            
            return response()->json([
                'success' => true,
                'processed_count' => $result['processed_count'],
                'updated_tickets' => $result['updated_tickets'],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync prices', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync price updates'
            ], 500);
        }
    }

    /**
     * Sync user preferences
     */
    public function syncPreferences(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $preferences = $request->json()->all();
            
            Log::info('Syncing user preferences', ['user_id' => $user->id]);
            
            $result = $this->syncService->syncUserPreferences($user, $preferences);
            
            return response()->json([
                'success' => true,
                'synced_preferences' => $result['synced_count'],
                'conflicts' => $result['conflicts'] ?? [],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync preferences', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync preferences'
            ], 500);
        }
    }

    /**
     * Sync purchase queue
     */
    public function syncPurchases(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $purchases = $request->json()->all();
            
            Log::info('Syncing purchase queue', ['user_id' => $user->id, 'purchases_count' => count($purchases)]);
            
            $result = $this->syncService->syncPurchaseQueue($user, $purchases);
            
            return response()->json([
                'success' => true,
                'processed_purchases' => $result['processed_count'],
                'failed_purchases' => $result['failed_count'],
                'conflicts' => $result['conflicts'] ?? [],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync purchases', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync purchase queue'
            ], 500);
        }
    }

    /**
     * Sync watchlist data
     */
    public function syncWatchlist(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $watchlistData = $request->json()->all();
            
            Log::info('Syncing watchlist', ['user_id' => $user->id, 'items_count' => count($watchlistData)]);
            
            $result = $this->syncService->syncWatchlist($user, $watchlistData);
            
            return response()->json([
                'success' => true,
                'synced_items' => $result['synced_count'],
                'removed_items' => $result['removed_count'],
                'conflicts' => $result['conflicts'] ?? [],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync watchlist', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync watchlist'
            ], 500);
        }
    }

    /**
     * Sync analytics data
     */
    public function syncAnalytics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $analyticsData = $request->json()->all();
            
            Log::info('Syncing analytics', ['user_id' => $user->id]);
            
            $result = $this->syncService->syncAnalytics($user, $analyticsData);
            
            return response()->json([
                'success' => true,
                'events_processed' => $result['events_processed'],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to sync analytics', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to sync analytics'
            ], 500);
        }
    }

    /**
     * Bulk sync multiple data types
     */
    public function syncBulk(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $bulkData = $request->json()->all();
            
            Log::info('Performing bulk sync', ['user_id' => $user->id, 'types' => array_keys($bulkData)]);
            
            $results = [];
            $overallSuccess = true;
            
            foreach ($bulkData as $type => $data) {
                try {
                    switch ($type) {
                        case 'alerts':
                            $results[$type] = $this->syncService->syncTicketAlerts($user, $data);
                            break;
                        case 'preferences':
                            $results[$type] = $this->syncService->syncUserPreferences($user, $data);
                            break;
                        case 'watchlist':
                            $results[$type] = $this->syncService->syncWatchlist($user, $data);
                            break;
                        case 'purchases':
                            $results[$type] = $this->syncService->syncPurchaseQueue($user, $data);
                            break;
                        case 'analytics':
                            $results[$type] = $this->syncService->syncAnalytics($user, $data);
                            break;
                        default:
                            $results[$type] = ['error' => 'Unknown sync type'];
                            $overallSuccess = false;
                    }
                } catch (\Exception $e) {
                    $results[$type] = ['error' => $e->getMessage()];
                    $overallSuccess = false;
                }
            }
            
            return response()->json([
                'success' => $overallSuccess,
                'results' => $results,
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed bulk sync', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to perform bulk sync'
            ], 500);
        }
    }

    /**
     * Get sync status for user
     */
    public function getSyncStatus(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $status = [
                'user_id' => $user->id,
                'last_sync_times' => [
                    'alerts' => Cache::get("user_{$user->id}_last_sync_alerts"),
                    'preferences' => Cache::get("user_{$user->id}_last_sync_preferences"),
                    'watchlist' => Cache::get("user_{$user->id}_last_sync_watchlist"),
                    'purchases' => Cache::get("user_{$user->id}_last_sync_purchases"),
                    'analytics' => Cache::get("user_{$user->id}_last_sync_analytics"),
                ],
                'pending_conflicts' => $this->syncService->getPendingConflicts($user),
                'sync_queue_size' => Cache::get("user_{$user->id}_sync_queue_size", 0),
                'last_activity' => $user->updated_at->toISOString()
            ];
            
            return response()->json($status);
            
        } catch (\Exception $e) {
            Log::error('Failed to get sync status', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get sync status'
            ], 500);
        }
    }

    /**
     * Resolve sync conflicts
     */
    public function resolveConflicts(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $conflicts = $request->json()->all();
            
            Log::info('Resolving sync conflicts', ['user_id' => $user->id, 'conflicts_count' => count($conflicts)]);
            
            $result = $this->syncService->resolveConflicts($user, $conflicts);
            
            return response()->json([
                'success' => true,
                'resolved_count' => $result['resolved_count'],
                'remaining_conflicts' => $result['remaining_conflicts'],
                'last_sync' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to resolve conflicts', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to resolve conflicts'
            ], 500);
        }
    }

    /**
     * Get sync queue for user
     */
    public function getSyncQueue(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $queue = $this->syncService->getSyncQueue($user);
            
            return response()->json([
                'queue_size' => count($queue),
                'items' => $queue,
                'last_updated' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get sync queue', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get sync queue'
            ], 500);
        }
    }

    /**
     * Clear sync queue for user
     */
    public function clearSyncQueue(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $this->syncService->clearSyncQueue($user);
            
            return response()->json([
                'success' => true,
                'message' => 'Sync queue cleared',
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to clear sync queue', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear sync queue'
            ], 500);
        }
    }

    /**
     * Remove specific item from sync queue
     */
    public function removeSyncQueueItem(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->syncService->removeSyncQueueItem($user, $id);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from sync queue'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Item not found in queue'
                ], 404);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to remove sync queue item', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove queue item'
            ], 500);
        }
    }

    /**
     * Get sync statistics
     */
    public function getSyncStats(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stats = $this->syncService->getSyncStats($user);
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Failed to get sync stats', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get sync statistics'
            ], 500);
        }
    }

    /**
     * Get sync health status
     */
    public function getSyncHealth(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $health = $this->syncService->getSyncHealth($user);
            
            return response()->json($health);
            
        } catch (\Exception $e) {
            Log::error('Failed to get sync health', ['error' => $e->getMessage(), 'user_id' => $request->user()->id]);
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to get sync health status'
            ], 500);
        }
    }
}
