<?php

declare(strict_types=1);

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class BikeHandlerTest extends TestCase
{
    private string $dataFolder;

    protected function setUp(): void
    {
        $this->dataFolder = sys_get_temp_dir() . '/pedalpal_int_' . uniqid();
        mkdir($this->dataFolder, 0777, true);

        $this->createSampleData('beach_cruisers.xml', <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<BeachCruisers>
  <Bike><bike_id>1</bike_id><model_name>Test Beach</model_name><color>Red</color><frame_size>M</frame_size><daily_rate>10</daily_rate><is_available>true</is_available></Bike>
  <Bike><bike_id>2</bike_id><model_name>Rented Beach</model_name><color>Blue</color><frame_size>L</frame_size><daily_rate>12</daily_rate><is_available>false</is_available></Bike>
</BeachCruisers>
XML);

        $this->createSampleData('mountain_bikes.json', <<<'JSON'
[{"BikeID":101,"ModelName":"Test Mountain","Brand":"Test","GearCount":21,"SuspensionType":"Full","FrameMaterial":"Alum","DailyRate":24.99,"IsAvailable":true,"Terrain":"Trail","WeightKg":13.5}]
JSON);

        $this->createSampleData('electric_bikes.json', <<<'JSON'
[{"bike_id":201,"model_name":"Test Electric","brand":"TestCo","battery_range_km":80,"motor_power_w":500,"daily_rate":29.99,"is_available":true,"weight_kg":22.5,"charge_time_h":4.5}]
JSON);

        $this->createSampleData('accessories.json', <<<'JSON'
[{"AccessoryID":1,"Name":"Bottle","Category":"Hydration","Description":"Water","UnitPrice":2.99,"StockCount":15,"CompatibleWith":["all"]}]
JSON);

        putenv('REDIS_HOST=');
        putenv('REDIS_PORT=');

        \ApplicationServices::initialize($this->dataFolder, null);
    }

    protected function tearDown(): void
    {
        $files = glob($this->dataFolder . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        $cacheFiles = glob($this->dataFolder . '/../*.cache');
        if ($cacheFiles !== false) {
            foreach ($cacheFiles as $f) {
                is_file($f) && unlink($f);
            }
        }
        rmdir($this->dataFolder);
    }

    private function createSampleData(string $filename, string $content): void
    {
        file_put_contents($this->dataFolder . '/' . $filename, $content);
    }

    private function registry(): \PedalPal\Service\BikeServiceRegistry
    {
        $r = \ApplicationServices::getBikeRegistry();
        $this->assertNotNull($r);

        return $r;
    }

    private function accessoryService(): \AccessoryService
    {
        $s = \ApplicationServices::getAccessoryService();
        $this->assertNotNull($s);

        return $s;
    }

    public function testBeachActionReturnsBikes(): void
    {
        $data = $this->registry()->get('beach')?->getAll() ?? [];
        $this->assertCount(2, $data);
        $this->assertSame('Test Beach', $data[0]['model_name']);
    }

    public function testMountainActionReturnsBikes(): void
    {
        $data = $this->registry()->get('mountain')?->getAll() ?? [];
        $this->assertCount(1, $data);
        $this->assertSame('Test Mountain', $data[0]['ModelName']);
    }

    public function testElectricActionReturnsBikes(): void
    {
        $data = $this->registry()->get('electric')?->getAll() ?? [];
        $this->assertCount(1, $data);
        $this->assertSame('Test Electric', $data[0]['model_name']);
    }

    public function testRentAvailableBikeReturnsSuccess(): void
    {
        $service = $this->registry()->get('beach');
        $this->assertNotNull($service);

        $result = $service->rentBike(1);
        $this->assertTrue($result);

        $bikes = $service->getAll();
        $this->assertFalse($bikes[0]['is_available']);
    }

    public function testRentUnavailableBikeReturnsFailure(): void
    {
        $service = $this->registry()->get('beach');
        $this->assertNotNull($service);

        $result = $service->rentBike(2);
        $this->assertFalse($result);
    }

    public function testRentNonexistentBikeReturnsFailure(): void
    {
        $service = $this->registry()->get('beach');
        $this->assertNotNull($service);

        $result = $service->rentBike(999);
        $this->assertFalse($result);
    }

    public function testResetRestoresDefaults(): void
    {
        $beachService = $this->registry()->get('beach');
        $this->assertNotNull($beachService);

        $beachService->rentBike(1);

        $bikes = $beachService->getAll();
        $this->assertFalse($bikes[0]['is_available']);

        foreach ($this->registry()->getAll() as $service) {
            $service->resetToDefaults();
        }
        $this->accessoryService()->resetToDefaults();

        $beachService = $this->registry()->get('beach');
        $this->assertNotNull($beachService);
        $restored = $beachService->getAll();
        $this->assertTrue($restored[0]['is_available']);
    }

    public function testListActionWithTypeParameter(): void
    {
        $mountain = $this->registry()->get('mountain');
        $this->assertNotNull($mountain);
        $data = $mountain->getAll();
        $this->assertCount(1, $data);
    }

    public function testListActionWithUnknownTypeReturnsNull(): void
    {
        $this->assertNull($this->registry()->get('unknown'));
    }

    public function testRegistryReturnsAllThreeTypes(): void
    {
        $all = $this->registry()->getAll();
        $this->assertCount(3, $all);
        $this->assertArrayHasKey('beach', $all);
        $this->assertArrayHasKey('mountain', $all);
        $this->assertArrayHasKey('electric', $all);
    }

    public function testBikePersistenceAcrossInitializations(): void
    {
        $service = $this->registry()->get('beach');
        $this->assertNotNull($service);
        $service->rentBike(1);

        \ApplicationServices::initialize($this->dataFolder, null);

        $fresh = $this->registry()->get('beach');
        $this->assertNotNull($fresh);
        $bikes = $fresh->getAll();
        $this->assertFalse($bikes[0]['is_available']);
    }

    public function testAccessoryServiceReturnsCompatibleItems(): void
    {
        $accessories = $this->accessoryService()->getCompatibleWith('beach');
        $this->assertCount(1, $accessories);
        $this->assertSame('Bottle', $accessories[0]['Name']);
    }

    public function testAccessoryOrderFullFlow(): void
    {
        $result = $this->accessoryService()->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 3],
        ]);

        $this->assertTrue($result['Success']);
        $this->assertSame(2.99 * 3, $result['TotalPrice']);
        $this->assertFalse($result['BundleDiscountApplied']);

        $items = $this->accessoryService()->getAll();
        $this->assertSame(12, $items[0]['StockCount']);
    }
}
