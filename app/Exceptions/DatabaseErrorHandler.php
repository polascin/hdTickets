<?php declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

class DatabaseErrorHandler
{
    /**
     * Handle the error
     */
    public function handle(Exception $exception): void
    {
        Log::error('Error in Database', [
            'exception' => $exception->getMessage(),
            'trace'     => $exception->getTraceAsString(),
        ]);
    }
}
