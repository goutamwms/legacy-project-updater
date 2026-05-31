<?php

declare(strict_types=1);

namespace PedalPal\Cache;

/**
 * Strategy interface for cache back-ends.
 *
 * Allows the repository layer to be decoupled from a concrete
 * caching provider – Redis, Memcached, APCu, or a no-op null
 * implementation can be swapped without changing consumers.
 */
interface CacheInterface
{
    /** Retrieve a value by key. Returns null on miss. */
    public function get(string $key): ?string;

    /** Store a value with a TTL in seconds. */
    public function set(string $key, string $value, int $ttl): void;

    /**
     * Store multiple key-value pairs atomically.
     * @param array<string, string> $items
     */
    public function setMultiple(array $items, int $ttl): void;
}
