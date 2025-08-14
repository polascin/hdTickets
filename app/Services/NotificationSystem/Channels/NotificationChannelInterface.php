<?php declare(strict_types=1);

namespace App\Services\NotificationSystem\Channels;

use App\Models\User;

interface NotificationChannelInterface
{
    /**
     * Send notification to the user
     */
    public function send(User $user, array $notification): bool;

    /**
     * Check if the channel is available and configured
     */
    public function isAvailable(): bool;
}
