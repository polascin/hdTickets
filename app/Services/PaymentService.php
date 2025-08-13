<?php declare(strict_types=1);

namespace App\Services;

class PaymentService
{
    public function processPayment(array $paymentData): array
    {
        return ['status' => 'success', 'transaction_id' => uniqid()];
    }

    public function refund(string $transactionId): bool
    {
        return TRUE;
    }
}
