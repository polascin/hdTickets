<?php declare(strict_types=1);

namespace App\Services\PayPal;

use Exception;

class PayPalService
{
    public function __construct()
    {
        // Temporarily disabled for deployment
    }

    public function __call($method, $args): void
    {
        throw new Exception('PayPal service is temporarily disabled');
    }
}
