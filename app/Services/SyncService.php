<?php declare(strict_types=1);

namespace App\Services;

use App\Models\TicketAlert;
use App\Models\TicketPurchase;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\WatchlistItem;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use function count;

/**
 * SyncService for HD Tickets Platform
 *
 * Handles synchronization of various data types for the sports events
 * ticket monitoring system including alerts, prices, preferences,
 * purchases, and watchlist items.
 */
class SyncService
{
    /**
     * Sync ticket alerts for a user
     */
    public function syncTicketAlerts(User $user, array $syncData): array
    {
        $syncedCount = 0;
        $conflicts = [];

        DB::beginTransaction();

        try {
            foreach ($syncData as $alertData) {
                $existingAlert = TicketAlert::where('user_id', $user->id)
                    ->where('ticket_id', $alertData['ticket_id'] ?? NULL)
                    ->first();

                if ($existingAlert) {
                    // Handle conflict - check if local version is newer
                    $localUpdated = $existingAlert->updated_at;
                    $remoteUpdated = isset($alertData['updated_at']) ?
                        new Carbon($alertData['updated_at']) :
                        now();

                    if ($localUpdated->greaterThan($remoteUpdated)) {
                        $conflicts[] = [
                            'type'   => 'alert',
                            'id'     => $existingAlert->id,
                            'reason' => 'Local version is newer',
                        ];

                        continue;
                    }

                    $existingAlert->update($alertData);
                } else {
                    TicketAlert::create(array_merge($alertData, ['user_id' => $user->id]));
                }

                $syncedCount++;
            }

            DB::commit();
            Log::info('Successfully synced ticket alerts', [
                'user_id'      => $user->id,
                'synced_count' => $syncedCount,
                'conflicts'    => count($conflicts),
            ]);

            return [
                'synced_count' => $syncedCount,
                'conflicts'    => $conflicts,
            ];
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to sync ticket alerts', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync price updates
     */
    public function syncPriceUpdates(User $user, array $priceUpdates): array
    {
        $processedCount = 0;
        $updatedTickets = [];

        try {
            foreach ($priceUpdates as $update) {
                if (! isset($update['ticket_id'], $update['new_price'])) {
                    continue;
                }

                // Here you would update the ticket price in your system
                // This is a placeholder implementation
                $processedCount++;
                $updatedTickets[] = $update['ticket_id'];
            }

            Log::info('Successfully processed price updates', [
                'user_id'         => $user->id,
                'processed_count' => $processedCount,
            ]);

            return [
                'processed_count' => $processedCount,
                'updated_tickets' => $updatedTickets,
            ];
        } catch (Exception $e) {
            Log::error('Failed to sync price updates', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync user preferences
     */
    public function syncUserPreferences(User $user, array $preferences): array
    {
        $syncedCount = 0;
        $conflicts = [];

        DB::beginTransaction();

        try {
            foreach ($preferences as $key => $value) {
                $existingPreference = UserPreference::where('user_id', $user->id)
                    ->where('key', $key)
                    ->first();

                if ($existingPreference) {
                    $existingPreference->update(['value' => $value]);
                } else {
                    UserPreference::create([
                        'user_id' => $user->id,
                        'key'     => $key,
                        'value'   => $value,
                    ]);
                }

                $syncedCount++;
            }

            DB::commit();

            Log::info('Successfully synced user preferences', [
                'user_id'      => $user->id,
                'synced_count' => $syncedCount,
            ]);

            return [
                'synced_count' => $syncedCount,
                'conflicts'    => $conflicts,
            ];
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to sync user preferences', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync purchase queue
     */
    public function syncPurchaseQueue(User $user, array $purchases): array
    {
        $processedCount = 0;
        $failedCount = 0;
        $conflicts = [];

        DB::beginTransaction();

        try {
            foreach ($purchases as $purchaseData) {
                try {
                    $existingPurchase = TicketPurchase::where('user_id', $user->id)
                        ->where('purchase_id', $purchaseData['purchase_id'] ?? NULL)
                        ->first();

                    if ($existingPurchase) {
                        $existingPurchase->update($purchaseData);
                    } else {
                        TicketPurchase::create(array_merge($purchaseData, ['user_id' => $user->id]));
                    }

                    $processedCount++;
                } catch (Exception $e) {
                    $failedCount++;
                    Log::warning('Failed to sync individual purchase', [
                        'user_id'       => $user->id,
                        'purchase_data' => $purchaseData,
                        'error'         => $e->getMessage(),
                    ]);
                }
            }

            DB::commit();

            Log::info('Successfully synced purchase queue', [
                'user_id'         => $user->id,
                'processed_count' => $processedCount,
                'failed_count'    => $failedCount,
            ]);

            return [
                'processed_count' => $processedCount,
                'failed_count'    => $failedCount,
                'conflicts'       => $conflicts,
            ];
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to sync purchase queue', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync watchlist
     */
    public function syncWatchlist(User $user, array $watchlistData): array
    {
        $syncedCount = 0;
        $removedCount = 0;
        $conflicts = [];

        DB::beginTransaction();

        try {
            // First, handle additions/updates
            foreach ($watchlistData as $itemData) {
                $existingItem = WatchlistItem::where('user_id', $user->id)
                    ->where('ticket_id', $itemData['ticket_id'] ?? NULL)
                    ->first();

                if ($existingItem) {
                    $existingItem->update($itemData);
                } else {
                    WatchlistItem::create(array_merge($itemData, ['user_id' => $user->id]));
                }

                $syncedCount++;
            }

            DB::commit();

            Log::info('Successfully synced watchlist', [
                'user_id'       => $user->id,
                'synced_count'  => $syncedCount,
                'removed_count' => $removedCount,
            ]);

            return [
                'synced_count'  => $syncedCount,
                'removed_count' => $removedCount,
                'conflicts'     => $conflicts,
            ];
        } catch (Exception $e) {
            DB::rollback();
            Log::error('Failed to sync watchlist', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Sync analytics data
     */
    public function syncAnalytics(User $user, array $analyticsData): array
    {
        $eventsProcessed = 0;

        try {
            // Process analytics events
            foreach ($analyticsData as $event) {
                // Here you would process individual analytics events
                // This is a placeholder implementation
                $eventsProcessed++;
            }

            Log::info('Successfully synced analytics data', [
                'user_id'          => $user->id,
                'events_processed' => $eventsProcessed,
            ]);

            return [
                'events_processed' => $eventsProcessed,
            ];
        } catch (Exception $e) {
            Log::error('Failed to sync analytics data', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
