<?php declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentErrorHandler
{
    /**
     * Handle the error
     */
    public function handle(Throwable $exception): void
    {
        Log::error('Error in Payment', [
            'exception' => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
