<?php

declare(strict_types=1);

namespace PedalPal;

use PedalPal\Cache\CacheInterface;
use PedalPal\Cache\RedisCache;

/**
 * Application configuration.
 *
 * Provides factory methods for shared dependencies
 * such as the Redis-backed cache adapter.
 */
class Config
{
    /**
     * Create a cache adapter from environment variables.
     *
     * Reads REDIS_HOST (default 127.0.0.1) and REDIS_PORT (default 6379).
     * Returns null when Redis is unavailable – callers should fall back
     * gracefully (e.g. file-based caching).
     */
    public static function cache(): ?CacheInterface
    {
        $host = getenv('REDIS_HOST') ?: '127.0.0.1';
        $port = (int)(getenv('REDIS_PORT') ?: 6379);

        try {
            $redis = new \Redis();
            $redis->connect($host, $port, 2.0);
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);

            return new RedisCache($redis);
        } catch (\RedisException) {
            return null;
        }
    }
}
