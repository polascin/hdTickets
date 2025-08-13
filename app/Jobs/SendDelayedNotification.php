<?php declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDelayedNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private string $emailClass,
        private array $emailData,
        private string $recipientEmail,
    ) {
    }

    public function handle(): void
    {
        $mailableClass = 'App\\Mail\\' . $this->emailClass;

        if (class_exists($mailableClass)) {
            $mailable = new $mailableClass($this->emailData);
            Mail::to($this->recipientEmail)->send($mailable);
        }
    }
}
