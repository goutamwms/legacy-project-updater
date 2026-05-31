<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AccessoryServiceTest extends TestCase
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
    "Name": "Wicker Basket",
    "Category": "Storage",
    "Description": "Holds stuff",
    "UnitPrice": 4.99,
    "StockCount": 8,
    "CompatibleWith": ["beach"]
  },
  {
    "AccessoryID": 3,
    "Name": "Bike Light",
    "Category": "Safety",
    "Description": "See at night",
    "UnitPrice": 3.49,
    "StockCount": 20,
    "CompatibleWith": ["mountain", "beach"]
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

    private function createService(): \AccessoryService
    {
        $repo = new \AccessoryRepository($this->tempDir);

        return new \AccessoryService($repo);
    }

    public function testGetAll(): void
    {
        $service = $this->createService();
        $items = $service->getAll();
        $this->assertCount(3, $items);
    }

    public function testGetCompatibleWithFilter(): void
    {
        $service = $this->createService();

        $beach = $service->getCompatibleWith('beach');
        $this->assertCount(3, $beach);

        $mountain = $service->getCompatibleWith('mountain');
        $this->assertCount(2, $mountain);

        $unknown = $service->getCompatibleWith('unknown');
        $this->assertCount(0, $unknown);
    }

    public function testProcessOrderSuccessWithoutBundle(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([
            ['AccessoryID' => 2, 'Quantity' => 2],
        ]);

        $this->assertTrue($result['Success']);
        $this->assertFalse($result['BundleDiscountApplied']);
        $this->assertSame(9.98, $result['TotalPrice']); // 4.99 * 2
        $this->assertSame(0.0, $result['DiscountAmount']);
    }

    public function testProcessOrderWithBundleDiscount(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 1], // $2.99
            ['AccessoryID' => 3, 'Quantity' => 2], // $3.49 * 2 = $6.98
        ]);

        $this->assertTrue($result['Success']);
        $this->assertTrue($result['BundleDiscountApplied']);

        $subtotal = 2.99 + 6.98; // 9.97
        $expectedDiscount = round($subtotal * 0.10, 2); // 1.00
        $expectedTotal = round($subtotal - $expectedDiscount, 2); // 8.97

        $this->assertSame($expectedDiscount, $result['DiscountAmount']);
        $this->assertSame($expectedTotal, $result['TotalPrice']);
    }

    public function testProcessOrderInsufficientStock(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 999],
        ]);

        $this->assertFalse($result['Success']);
    }

    public function testProcessOrderInvalidAccessoryId(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([
            ['AccessoryID' => 999, 'Quantity' => 1],
        ]);

        $this->assertFalse($result['Success']);
    }

    public function testProcessOrderDeductsStock(): void
    {
        $service = $this->createService();
        $service->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 3],
        ]);

        $items = $service->getAll();
        $this->assertSame(12, $items[0]['StockCount']); // 15 - 3
    }

    public function testGetCompatibleWithHandlesHtmlInput(): void
    {
        $service = $this->createService();
        $result = $service->getCompatibleWith('<script>beach</script>');
        $this->assertCount(3, $result);

        $result = $this->createService()->getCompatibleWith('BEACH');
        $this->assertCount(3, $result);
    }

    public function testGetCompatibleWithEmptyStringReturnsNone(): void
    {
        $service = $this->createService();
        $result = $service->getCompatibleWith('');
        $this->assertCount(0, $result);
    }

    public function testProcessOrderPartialStockDeductionPreservesOtherItems(): void
    {
        $service = $this->createService();
        $service->processOrder([['AccessoryID' => 1, 'Quantity' => 5]]);

        $items = $service->getAll();
        $this->assertSame(10, $items[0]['StockCount']); // 15 - 5
        $this->assertSame(8, $items[1]['StockCount']);  // unchanged
        $this->assertSame(20, $items[2]['StockCount']); // unchanged
    }

    public function testProcessOrderExactStockDepletesToZero(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([['AccessoryID' => 1, 'Quantity' => 15]]);

        $this->assertTrue($result['Success']);

        $items = $service->getAll();
        $this->assertSame(0, $items[0]['StockCount']);
    }

    public function testResetToDefaults(): void
    {
        $service = $this->createService();

        // Deplete stock
        $service->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 15],
            ['AccessoryID' => 3, 'Quantity' => 20],
        ]);

        $service->resetToDefaults();

        $items = $service->getAll();
        $this->assertSame(15, $items[0]['StockCount']); // Water Bottle reset to 15
        $this->assertSame(8, $items[1]['StockCount']);  // Basket reset to 8
        $this->assertSame(20, $items[2]['StockCount']); // Light reset to 20
    }

    public function testEmptyOrderReturnsError(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([]);
        $this->assertFalse($result['Success']);
        $this->assertSame('No items ordered.', $result['Message']);
    }

    public function testZeroQuantitiesAreSkipped(): void
    {
        $service = $this->createService();
        $result = $service->processOrder([
            ['AccessoryID' => 1, 'Quantity' => 0],
        ]);

        $this->assertFalse($result['Success']);
        $this->assertSame('No items ordered.', $result['Message']);
    }
}
