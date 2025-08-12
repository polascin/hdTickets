<?php declare(strict_types=1);

namespace App\Services\NotificationSystem\Channels;

use App\Mail\TicketNotificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailChannel implements NotificationChannelInterface
{
    public function send(User $user, array $notification): bool
    {
        try {
            if (! $user->email || ! $user->email_verified_at) {
                Log::info('Skipping email notification for unverified user', [
                    'user_id' => $user->id,
                    'type'    => $notification['type'],
                ]);

                return FALSE;
            }

            $mailable = new TicketNotificationMail($user, $notification);

            Mail::to($user->email)->send($mailable);

            Log::info('Email notification sent successfully', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'type'    => $notification['type'],
            ]);

            return TRUE;
        } catch (Throwable $e) {
            Log::error('Failed to send email notification', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'type'    => $notification['type'],
                'error'   => $e->getMessage(),
            ]);

            return FALSE;
        }
    }

    public function isAvailable(): bool
    {
        return ! empty(config('mail.default'));
    }
}
