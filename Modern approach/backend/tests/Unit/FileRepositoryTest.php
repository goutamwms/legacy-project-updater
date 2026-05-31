<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedalPal\Cache\NullCache;
use PedalPal\Repository\FileRepository;
use PHPUnit\Framework\TestCase;

class FileRepositoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        rmdir($this->tempDir);
    }

    public function testGetAllReadsFromSourceOnFirstCall(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Alpha"}]');
        $repo = $this->createRepo();

        $data = $repo->getAll();
        $this->assertCount(1, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('Alpha', $data[0]['name']);
    }

    public function testGetAllWritesCacheFile(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1}]');
        $repo = $this->createRepo();

        $repo->getAll();

        $this->assertFileExists($this->tempDir . '/test.json.cache');
    }

    public function testGetAllReadsFromCacheWhenFresh(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Original"}]');
        $repo = $this->createRepo();
        $repo->getAll();

        // Modify source but keep cache
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Modified"}]');

        // Should still read from cache (cache mtime >= source mtime)
        $data = $repo->getAll();
        $this->assertSame('Original', $data[0]['name']);
    }

    public function testSavePersistsToSourceAndUpdatesCache(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Original"}]');
        $repo = $this->createRepo();
        $repo->getAll();

        $repo->save([['id' => 1, 'name' => 'Updated']]);

        $raw = file_get_contents($this->tempDir . '/test.json');
        $this->assertStringContainsString('Updated', $raw);

        $data = $repo->getAll();
        $this->assertSame('Updated', $data[0]['name']);
    }

    public function testMissingSourceReturnsEmpty(): void
    {
        $repo = $this->createRepo();
        $this->assertSame([], $repo->getAll());
    }

    public function testCorruptedCacheFallsBackToSource(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Source"}]');
        file_put_contents($this->tempDir . '/test.json.cache', 'corrupted');

        $repo = $this->createRepo();
        $data = $repo->getAll();
        $this->assertSame('Source', $data[0]['name']);
    }

    public function testCorruptedCacheRewrittenOnAccess(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1,"name":"Fixed"}]');
        file_put_contents($this->tempDir . '/test.json.cache', 'corrupted');

        $repo = $this->createRepo();
        $repo->getAll();

        $cache = file_get_contents($this->tempDir . '/test.json.cache');
        $decoded = json_decode($cache, true);
        $this->assertIsArray($decoded);
        $this->assertSame('Fixed', $decoded[0]['name']);
    }

    public function testWithNullCacheInstanceSkipsFileCache(): void
    {
        file_put_contents($this->tempDir . '/test.json', '[{"id":1}]');

        $repo = new class ($this->tempDir, 'test.json', new NullCache()) extends FileRepository {
            protected function loadFromSource(): array
            {
                $raw = file_get_contents($this->dataPath);

                return $raw !== false ? (json_decode($raw, true) ?? []) : [];
            }

            protected function writeToSource(array $data): void
            {
                file_put_contents($this->dataPath, json_encode($data));
            }
        };

        $data = $repo->getAll();
        $this->assertCount(1, $data);

        // When a CacheInterface is provided (even NullCache), file cache is skipped
        $this->assertFileDoesNotExist($this->tempDir . '/test.json.cache');
    }

    private function createRepo(): FileRepository
    {
        return new class ($this->tempDir, 'test.json', null) extends FileRepository {
            protected function loadFromSource(): array
            {
                $raw = @file_get_contents($this->dataPath);
                if ($raw === false) {
                    return [];
                }
                $decoded = json_decode($raw, true);

                return is_array($decoded) ? $decoded : [];
            }

            protected function writeToSource(array $data): void
            {
                file_put_contents($this->dataPath, json_encode($data, JSON_PRETTY_PRINT));
            }
        };
    }
}
