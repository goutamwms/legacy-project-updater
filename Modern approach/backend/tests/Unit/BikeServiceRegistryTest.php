<?php

declare(strict_types=1);

namespace Tests\Unit;

use PedalPal\Service\BikeServiceInterface;
use PedalPal\Service\BikeServiceRegistry;
use PHPUnit\Framework\TestCase;

class BikeServiceRegistryTest extends TestCase
{
    public function testRegisterAndGet(): void
    {
        $registry = new BikeServiceRegistry();
        $service = $this->createStub(BikeServiceInterface::class);

        $registry->register('beach', $service);
        $this->assertSame($service, $registry->get('beach'));
    }

    public function testGetReturnsNullForUnregisteredType(): void
    {
        $registry = new BikeServiceRegistry();
        $this->assertNull($registry->get('nonexistent'));
    }

    public function testGetReturnsNullForEmptyRegistry(): void
    {
        $registry = new BikeServiceRegistry();
        $this->assertNull($registry->get('beach'));
    }

    public function testGetAllReturnsAllRegisteredServices(): void
    {
        $registry = new BikeServiceRegistry();
        $beach = $this->createStub(BikeServiceInterface::class);
        $mountain = $this->createStub(BikeServiceInterface::class);

        $registry->register('beach', $beach);
        $registry->register('mountain', $mountain);

        $all = $registry->getAll();
        $this->assertCount(2, $all);
        $this->assertArrayHasKey('beach', $all);
        $this->assertArrayHasKey('mountain', $all);
        $this->assertSame($beach, $all['beach']);
        $this->assertSame($mountain, $all['mountain']);
    }

    public function testRegisterOverwritesExisting(): void
    {
        $registry = new BikeServiceRegistry();
        $first = $this->createStub(BikeServiceInterface::class);
        $second = $this->createStub(BikeServiceInterface::class);

        $registry->register('beach', $first);
        $registry->register('beach', $second);

        $this->assertSame($second, $registry->get('beach'));
        $this->assertCount(1, $registry->getAll());
    }

    public function testGetAllReturnsEmptyArrayWhenEmpty(): void
    {
        $registry = new BikeServiceRegistry();
        $this->assertSame([], $registry->getAll());
    }
}
