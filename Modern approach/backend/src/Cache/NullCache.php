<?php

declare(strict_types=1);

namespace PedalPal\Cache;

/**
 * No-op cache implementation.
 *
 * All operations are silent no-ops. Useful as a default/adapter
 * when no caching provider is configured.
 */
class NullCache implements CacheInterface
{
    public function get(string $key): ?string
    {
        return null;
    }

    public function set(string $key, string $value, int $ttl): void
    {
    }

    /** @param array<string, string> $items */
    public function setMultiple(array $items, int $ttl): void
    {
    }
}
