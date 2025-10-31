<?php declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', fn ($user, $id): bool => (int) $user->id === (int) $id);

/*
|--------------------------------------------------------------------------
| HD Tickets Broadcasting Channels
|--------------------------------------------------------------------------
|
| These channels are used for real-time ticket monitoring, price alerts,
| and availability updates in the HD Tickets sports event system.
|
*/

// Public channels - no authentication required
Broadcast::channel('ticket.{ticketId}', fn (): TRUE => // Public channel for ticket-specific updates
    TRUE);

Broadcast::channel('platform.{platform}', fn (): TRUE => // Public channel for platform-wide updates
    TRUE);

Broadcast::channel('price-alerts', fn (): TRUE => // Public channel for price alert notifications
    TRUE);

Broadcast::channel('availability-alerts', fn (): TRUE => // Public channel for availability change notifications
    TRUE);

Broadcast::channel('status-alerts', fn (): TRUE => // Public channel for ticket status change notifications
    TRUE);

Broadcast::channel('system.announcements', fn (): TRUE => // Public channel for system-wide announcements
    TRUE);

Broadcast::channel('search.{searchId}', fn (): TRUE => // Public channel for search-related updates
    TRUE);

// Private channels - authentication required
Broadcast::channel('user.{userId}', fn ($user, $userId): bool => // Private channel for user-specific notifications
    $user && (int) $user->id === (int) $userId);

Broadcast::channel('user.{userId}.bookmarks', fn ($user, $userId): bool => // Private channel for user bookmark updates
    $user && (int) $user->id === (int) $userId);

Broadcast::channel('user.{userId}.alerts', fn ($user, $userId): bool => // Private channel for user price alerts
    $user && (int) $user->id === (int) $userId);
