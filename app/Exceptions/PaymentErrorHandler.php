<?php declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class PaymentErrorHandler
{
    /**
     * Handle the error
     */
    public function handle(Exception $exception): void
    {
        Log::error('Error in Payment', [
            'exception' => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
