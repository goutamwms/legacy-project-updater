<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class MountainBikeRepositoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $json = <<<'JSON'
[
  {
    "BikeID": 101,
    "ModelName": "TrailBlazer X9",
    "Brand": "ApexRide",
    "GearCount": 21,
    "SuspensionType": "Full",
    "FrameMaterial": "Aluminum",
    "DailyRate": 24.99,
    "IsAvailable": true,
    "Terrain": "All-Mountain",
    "WeightKg": 13.5
  }
]
JSON;
        file_put_contents($this->tempDir . '/mountain_bikes.json', $json);
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

    public function testGetAllReturnsBikesFromJson(): void
    {
        $repo = new \MountainBikeRepository($this->tempDir);
        $bikes = $repo->getAll();

        $this->assertCount(1, $bikes);
        $this->assertSame(101, $bikes[0]['BikeID']);
        $this->assertSame('TrailBlazer X9', $bikes[0]['ModelName']);
        $this->assertSame('Full', $bikes[0]['SuspensionType']);
        $this->assertSame('ApexRide', $bikes[0]['Brand']);
        $this->assertTrue($bikes[0]['IsAvailable']);
    }

    public function testSaveAndReload(): void
    {
        $repo = new \MountainBikeRepository($this->tempDir);
        $bikes = $repo->getAll();

        $bikes[0]['IsAvailable'] = false;
        $bikes[0]['DailyRate'] = 29.99;
        $repo->save($bikes);

        $reloaded = $repo->getAll();
        $this->assertFalse($reloaded[0]['IsAvailable']);
        $this->assertSame(29.99, $reloaded[0]['DailyRate']);
    }

    public function testCorruptedJsonReturnsEmpty(): void
    {
        file_put_contents($this->tempDir . '/mountain_bikes.json', 'invalid json');
        $repo = new \MountainBikeRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testMissingFileReturnsEmpty(): void
    {
        unlink($this->tempDir . '/mountain_bikes.json');
        $repo = new \MountainBikeRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testGetAllCreatesCacheFile(): void
    {
        $repo = new \MountainBikeRepository($this->tempDir);
        $repo->getAll();

        $cachePath = $this->tempDir . '/mountain_bikes.json.cache';
        $this->assertFileExists($cachePath);

        $cached = json_decode(file_get_contents($cachePath), true);
        $this->assertIsArray($cached);
        $this->assertCount(1, $cached);
    }

    public function testNonArrayJsonReturnsEmpty(): void
    {
        file_put_contents($this->tempDir . '/mountain_bikes.json', '{"not_an_array": true}');
        $repo = new \MountainBikeRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }
}
