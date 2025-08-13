<?php declare(strict_types=1);

namespace App\Services\Patterns\Strategy;

class PurchaseStrategyFactory
{
    public function create(string $strategy): object
    {
        return new class() {
            public function execute(): bool
            {
                return TRUE;
            }
        };
    }
}
