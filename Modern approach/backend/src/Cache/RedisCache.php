<?php

declare(strict_types=1);

namespace PedalPal\Cache;

/**
 * Redis-backed cache adapter.
 *
 * Wraps a \Redis instance behind CacheInterface.
 * Uses pipelined MULTI/EXEC for atomic multi-set operations.
 */
class RedisCache implements CacheInterface
{
    private \Redis $redis;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function get(string $key): ?string
    {
        $value = $this->redis->get($key);

        return is_string($value) ? $value : null;
    }

    public function set(string $key, string $value, int $ttl): void
    {
        $this->redis->setEx($key, $ttl, $value);
    }

    /** @param array<string, string> $items */
    public function setMultiple(array $items, int $ttl): void
    {
        $pipe = $this->redis->multi(\Redis::PIPELINE);
        foreach ($items as $key => $value) {
            $pipe->setEx($key, $ttl, $value);
        }
        $pipe->exec();
    }
}
