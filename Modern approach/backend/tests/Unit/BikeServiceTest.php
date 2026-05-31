<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class BikeServiceTest extends TestCase
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
  },
  {
    "BikeID": 102,
    "ModelName": "Summit Shredder",
    "Brand": "PeakForce",
    "GearCount": 27,
    "SuspensionType": "Full",
    "FrameMaterial": "Carbon Fiber",
    "DailyRate": 34.99,
    "IsAvailable": false,
    "Terrain": "Enduro",
    "WeightKg": 11.2
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

    private function createService(): \PedalPal\Service\BikeService
    {
        $repo = new \MountainBikeRepository($this->tempDir);

        return new \PedalPal\Service\BikeService($repo, 'BikeID', 'IsAvailable');
    }

    public function testGetAllReturnsBikes(): void
    {
        $service = $this->createService();
        $bikes = $service->getAll();
        $this->assertCount(2, $bikes);
    }

    public function testRentAvailableBikeReturnsTrue(): void
    {
        $service = $this->createService();
        $result = $service->rentBike(101);
        $this->assertTrue($result);

        $bikes = $service->getAll();
        $this->assertFalse($bikes[0]['IsAvailable']);
    }

    public function testRentUnavailableBikeReturnsFalse(): void
    {
        $service = $this->createService();
        $result = $service->rentBike(102);
        $this->assertFalse($result);
    }

    public function testRentNonexistentBikeReturnsFalse(): void
    {
        $service = $this->createService();
        $result = $service->rentBike(999);
        $this->assertFalse($result);
    }

    public function testRentBikePersistsToFile(): void
    {
        $service = $this->createService();
        $service->rentBike(101);

        // Create a fresh service to verify persistence
        $freshService = $this->createService();
        $bikes = $freshService->getAll();
        $this->assertFalse($bikes[0]['IsAvailable']);
    }

    public function testResetToDefaultsWithEmptyDefaultsIsNoOp(): void
    {
        $repo = new \MountainBikeRepository($this->tempDir);
        $service = new \PedalPal\Service\BikeService($repo, 'BikeID', 'IsAvailable');

        $bikes = $service->getAll();
        $this->assertCount(2, $bikes);

        $service->resetToDefaults();

        // No defaults provided, so data should be unchanged
        $bikesAfter = $service->getAll();
        $this->assertCount(2, $bikesAfter);
        $this->assertSame('TrailBlazer X9', $bikesAfter[0]['ModelName']);
    }

    public function testResetToDefaults(): void
    {
        $defaults = [
            ['BikeID' => 999, 'ModelName' => 'Default Bike', 'IsAvailable' => true, 'DailyRate' => 10.00],
        ];

        $repo = new \MountainBikeRepository($this->tempDir);
        $service = new \PedalPal\Service\BikeService($repo, 'BikeID', 'IsAvailable', $defaults);

        // Rent a bike first
        $service->rentBike(101);

        // Reset
        $service->resetToDefaults();

        $bikes = $service->getAll();
        $this->assertCount(1, $bikes);
        $this->assertSame(999, $bikes[0]['BikeID']);
        $this->assertSame('Default Bike', $bikes[0]['ModelName']);
    }
}
