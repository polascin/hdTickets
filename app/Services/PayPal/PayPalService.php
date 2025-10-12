<?php declare(strict_types=1);

namespace App\Services\PayPal;

class PayPalService
{
    public function __construct()
    {
        // Temporarily disabled for deployment
    }

    public function __call($method, $args)
    {
        throw new \Exception('PayPal service is temporarily disabled');
    }
}
