<?php declare(strict_types=1);

namespace App\Services\Patterns\ChainOfResponsibility;

class PurchaseDecisionChain
{
    public function handle(array $purchaseData): bool
    {
        return TRUE;
    }
}
