<?php

namespace App\Services\NotificationSystem\Channels;

use App\Models\User;

interface NotificationChannelInterface
{
    /**
     * Send notification to the user
     *
     * @param User $user
     * @param array $notification
     * @return bool
     */
    public function send(User $user, array $notification): bool;

    /**
     * Check if the channel is available and configured
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
