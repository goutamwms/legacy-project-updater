<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AccessoryRepositoryTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/pedalpal_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $json = <<<'JSON'
[
  {
    "AccessoryID": 1,
    "Name": "Water Bottle",
    "Category": "Hydration",
    "Description": "Stays cold",
    "UnitPrice": 2.99,
    "StockCount": 15,
    "CompatibleWith": ["beach", "mountain"]
  },
  {
    "AccessoryID": 2,
    "Name": "Bike Light",
    "Category": "Safety",
    "Description": "See at night",
    "UnitPrice": 3.49,
    "StockCount": 20,
    "CompatibleWith": ["all"]
  }
]
JSON;
        file_put_contents($this->tempDir . '/accessories.json', $json);
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

    public function testGetAllReturnsAccessories(): void
    {
        $repo = new \AccessoryRepository($this->tempDir);
        $items = $repo->getAll();

        $this->assertCount(2, $items);
        $this->assertSame(1, $items[0]['AccessoryID']);
        $this->assertSame('Water Bottle', $items[0]['Name']);
        $this->assertSame(['beach', 'mountain'], $items[0]['CompatibleWith']);
    }

    public function testSaveUpdatesStock(): void
    {
        $repo = new \AccessoryRepository($this->tempDir);
        $items = $repo->getAll();

        $items[0]['StockCount'] = 5;
        $repo->save($items);

        $reloaded = $repo->getAll();
        $this->assertSame(5, $reloaded[0]['StockCount']);
    }

    public function testMissingFileReturnsEmpty(): void
    {
        unlink($this->tempDir . '/accessories.json');
        $repo = new \AccessoryRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testCorruptedJsonReturnsEmpty(): void
    {
        file_put_contents($this->tempDir . '/accessories.json', '{"invalid":');
        $repo = new \AccessoryRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testNonArrayJsonReturnsEmpty(): void
    {
        file_put_contents($this->tempDir . '/accessories.json', '"just a string"');
        $repo = new \AccessoryRepository($this->tempDir);
        $this->assertSame([], $repo->getAll());
    }

    public function testGetAllWithFreshCacheReturnsFromCache(): void
    {
        $repo = new \AccessoryRepository($this->tempDir);
        $items = $repo->getAll();
        $this->assertCount(2, $items);

        // Modify source to be different
        file_put_contents($this->tempDir . '/accessories.json', '[]');

        // Cache is still fresh so should return original 2 items
        $cached = $repo->getAll();
        $this->assertCount(2, $cached);
    }
}
