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

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

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
Broadcast::channel('ticket.{ticketId}', function () {
    // Public channel for ticket-specific updates
    return true;
});

Broadcast::channel('platform.{platform}', function () {
    // Public channel for platform-wide updates
    return true;
});

Broadcast::channel('price-alerts', function () {
    // Public channel for price alert notifications
    return true;
});

Broadcast::channel('availability-alerts', function () {
    // Public channel for availability change notifications
    return true;
});

Broadcast::channel('status-alerts', function () {
    // Public channel for ticket status change notifications
    return true;
});

Broadcast::channel('system.announcements', function () {
    // Public channel for system-wide announcements
    return true;
});

Broadcast::channel('search.{searchId}', function () {
    // Public channel for search-related updates
    return true;
});

// Private channels - authentication required
Broadcast::channel('user.{userId}', function ($user, $userId) {
    // Private channel for user-specific notifications
    return $user && (int) $user->id === (int) $userId;
});

Broadcast::channel('user.{userId}.bookmarks', function ($user, $userId) {
    // Private channel for user bookmark updates
    return $user && (int) $user->id === (int) $userId;
});

Broadcast::channel('user.{userId}.alerts', function ($user, $userId) {
    // Private channel for user price alerts
    return $user && (int) $user->id === (int) $userId;
});
