<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedalPal\Cache\NullCache;
use PHPUnit\Framework\TestCase;

class CacheTest extends TestCase
{
    private NullCache $nullCache;

    protected function setUp(): void
    {
        $this->nullCache = new NullCache();
    }

    public function testNullCacheGetReturnsNull(): void
    {
        $this->assertNull($this->nullCache->get('any_key'));
    }

    public function testNullCacheSetDoesNotThrow(): void
    {
        $this->nullCache->set('key', 'value', 3600);
        $this->assertNull($this->nullCache->get('key'));
    }

    public function testNullCacheSetMultipleDoesNotThrow(): void
    {
        $this->nullCache->setMultiple(['a' => '1', 'b' => '2'], 3600);
        $this->assertNull($this->nullCache->get('a'));
        $this->assertNull($this->nullCache->get('b'));
    }

    public function testNullCacheMultipleKeysAllReturnNull(): void
    {
        $this->nullCache->set('k1', 'v1', 100);
        $this->nullCache->set('k2', 'v2', 200);

        $this->assertNull($this->nullCache->get('k1'));
        $this->assertNull($this->nullCache->get('k2'));
        $this->assertNull($this->nullCache->get('nonexistent'));
    }
}
