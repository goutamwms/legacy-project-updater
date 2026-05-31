<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ElectricBikeRepositoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $json = <<<'JSON'
[
  {
    "bike_id": 201,
    "model_name": "Volt Rider",
    "brand": "EcoMotion",
    "battery_range_km": 80,
    "motor_power_w": 500,
    "daily_rate": 29.99,
    "is_available": true,
    "weight_kg": 22.5,
    "charge_time_h": 4.5
  },
  {
    "bike_id": 202,
    "model_name": "City Glide",
    "brand": "UrbanSpark",
    "battery_range_km": 60,
    "motor_power_w": 350,
    "daily_rate": 24.99,
    "is_available": false,
    "weight_kg": 19.8,
    "charge_time_h": 3.5
  }
]
JSON;
        file_put_contents($this->tempDir . '/electric_bikes.json', $json);
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
        $repo = new \ElectricBikeRepository($this->tempDir);
        $bikes = $repo->getAll();

        $this->assertCount(2, $bikes);
        $this->assertSame(201, $bikes[0]['bike_id']);
        $this->assertSame('Volt Rider', $bikes[0]['model_name']);
        $this->assertSame('EcoMotion', $bikes[0]['brand']);
        $this->assertSame(80, $bikes[0]['battery_range_km']);
        $this->assertSame(500, $bikes[0]['motor_power_w']);
        $this->assertSame(29.99, $bikes[0]['daily_rate']);
        $this->assertTrue($bikes[0]['is_available']);
        $this->assertSame(22.5, $bikes[0]['weight_kg']);
        $this->assertSame(4.5, $bikes[0]['charge_time_h']);
        $this->assertFalse($bikes[1]['is_available']);
    }

    public function testSaveAndReload(): void
    {
        $repo = new \ElectricBikeRepository($this->tempDir);
        $bikes = $repo->getAll();

        $bikes[0]['is_available'] = false;
        $bikes[0]['daily_rate'] = 19.99;
        $repo->save($bikes);

        $reloaded = $repo->getAll();
        $this->assertFalse($reloaded[0]['is_available']);
        $this->assertSame(19.99, $reloaded[0]['daily_rate']);
    }

    public function testCorruptedJsonReturnsEmpty(): void
    {
        file_put_contents($this->tempDir . '/electric_bikes.json', '{invalid json');
        $repo = new \ElectricBikeRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testMissingFileReturnsEmpty(): void
    {
        unlink($this->tempDir . '/electric_bikes.json');
        $repo = new \ElectricBikeRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testGetAllCreatesCacheFile(): void
    {
        $repo = new \ElectricBikeRepository($this->tempDir);
        $repo->getAll();

        $cachePath = $this->tempDir . '/electric_bikes.json.cache';
        $this->assertFileExists($cachePath);

        $cached = json_decode(file_get_contents($cachePath), true);
        $this->assertIsArray($cached);
        $this->assertCount(2, $cached);
    }

    public function testCacheInvalidatedOnDataChange(): void
    {
        $repo = new \ElectricBikeRepository($this->tempDir);
        $repo->getAll();

        $data = file_get_contents($this->tempDir . '/electric_bikes.json');
        $data = str_replace('Volt Rider', 'Updated Volt', $data);
        file_put_contents($this->tempDir . '/electric_bikes.json', $data);

        $tempFile = $this->tempDir . '/electric_bikes.json';
        touch($tempFile, time() + 2);

        $reloaded = $repo->getAll();
        $this->assertSame('Updated Volt', $reloaded[0]['model_name']);
    }
}
