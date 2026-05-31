<?php

declare(strict_types=1);

namespace PedalPal\Repository;

use PedalPal\Cache\CacheInterface;

/**
 * Abstract base for flat-file data repositories.
 *
 * Provides transparent caching (Redis or sidecar .json.cache file)
 * and an mtime-based freshness check. Subclasses only need to
 * implement loadFromSource() and writeToSource().
 */
abstract class FileRepository
{
    protected string $dataPath;
    protected string $cachePath;
    private ?CacheInterface $cache = null;
    private int $cacheTtl = 3600;

    public function __construct(string $dataFolder, string $filename, ?CacheInterface $cache = null)
    {
        $this->dataPath  = $dataFolder . DIRECTORY_SEPARATOR . $filename;
        $this->cachePath = $this->dataPath . '.cache';
        $this->cache     = $cache;
    }

    private function cacheDataKey(): string
    {
        return 'pedalpal:data:' . str_replace(['/', '\\', ':'], '_', $this->dataPath);
    }

    private function cacheMtimeKey(): string
    {
        return $this->cacheDataKey() . ':mtime';
    }

    private function isCacheFresh(): bool
    {
        if ($this->cache !== null) {
            $cachedMtime = $this->cache->get($this->cacheMtimeKey());
            if ($cachedMtime === null) {
                return false;
            }
            if (!file_exists($this->dataPath)) {
                return false;
            }

            return (int)$cachedMtime >= filemtime($this->dataPath);
        }

        if (!file_exists($this->cachePath)) {
            return false;
        }
        if (!file_exists($this->dataPath)) {
            return false;
        }

        return filemtime($this->cachePath) >= filemtime($this->dataPath);
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private function readCache(): ?array
    {
        if ($this->cache !== null) {
            $raw = $this->cache->get($this->cacheDataKey());
            if ($raw === null) {
                return null;
            }

            try {
                $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                /** @var list<array<string, mixed>>|null $data */
                return is_array($data) ? $data : null;
            } catch (\JsonException) {
                return null;
            }
        }

        $contents = @file_get_contents($this->cachePath);
        if ($contents === false) {
            return null;
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

            /** @var list<array<string, mixed>>|null $data */
            return is_array($data) ? $data : null;
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    private function writeCache(array $data): void
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($payload === false) {
            return;
        }
        $mtime = file_exists($this->dataPath) ? filemtime($this->dataPath) : time();

        if ($this->cache !== null) {
            $this->cache->setMultiple([
                $this->cacheDataKey()  => $payload,
                $this->cacheMtimeKey() => (string)$mtime,
            ], $this->cacheTtl);

            return;
        }

        @file_put_contents($this->cachePath, $payload);
    }

    /**
     * @return list<array<string, mixed>>
     */
    abstract protected function loadFromSource(): array;

    /**
     * @return list<array<string, mixed>>
     */
    public function getAll(): array
    {
        if ($this->isCacheFresh()) {
            $cached = $this->readCache();
            if ($cached !== null) {
                return $cached;
            }
        }

        $data = $this->loadFromSource();
        $this->writeCache($data);

        return $data;
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    public function save(array $data): void
    {
        $this->writeToSource($data);
        $this->writeCache($data);
    }

    /**
     * @param list<array<string, mixed>> $data
     */
    abstract protected function writeToSource(array $data): void;
}
