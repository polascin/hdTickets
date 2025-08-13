<?php declare(strict_types=1);

namespace App\Services\Scraping\Adapters;

class PlatformAdapterFactory
{
    public function create(string $platform): object
    {
        return new class() {
            public function scrape(): array
            {
                return [];
            }
        };
    }
}
