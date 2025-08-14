<?php declare(strict_types=1);

namespace App\Services\NotificationChannels;

class ChannelFactory
{
    public function create(string $channel): object
    {
        return new class() {};
    }
}
