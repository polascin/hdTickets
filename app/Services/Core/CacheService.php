<?php declare(strict_types=1);

namespace App\Services\Core;

use Illuminate\Contracts\Cache\Repository;

class CacheService
{
    public function __construct(
        private Repository $cache,
    ) {
    }

    public function get(string $key, mixed $default = NULL): mixed
    {
        return $this->cache->get($key, $default);
    }

    public function put(string $key, mixed $value, int $ttl = 3600): bool
    {
        return $this->cache->put($key, $value, $ttl);
    }

    public function forget(string $key): bool
    {
        return $this->cache->forget($key);
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return $this->cache->remember($key, $ttl, $callback);
    }
}
