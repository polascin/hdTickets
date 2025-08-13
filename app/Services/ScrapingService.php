<?php declare(strict_types=1);

namespace App\Services;

class ScrapingService
{
    public function scrape(string $url): array
    {
        return [];
    }

    public function getStatus(): string
    {
        return 'active';
    }
}
