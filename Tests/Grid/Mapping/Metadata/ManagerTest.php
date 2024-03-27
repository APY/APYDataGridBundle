<?php

namespace APY\DataGridBundle\Tests\Grid\Mapping\Metadata;

use APY\DataGridBundle\Grid\Mapping\Driver\DriverInterface;
use APY\DataGridBundle\Grid\Mapping\Metadata\DriverHeap;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Mapping\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    private Manager $manager;

    protected function setUp(): void
    {
        $this->manager = new Manager();
    }

    public function testAddDriver(): void
    {
        $driverInterfaceMock = $this->createMock(DriverInterface::class);
        $priority = 1;

        $driverHeap = new DriverHeap();
        $driverHeap->insert($driverInterfaceMock, $priority);

        $this->manager->addDriver($driverInterfaceMock, $priority);

        $this->assertEquals($driverHeap, $this->manager->getDrivers());
    }

    public function testGetDrivers(): void
    {
        $driverInterfaceMock = $this->createMock(DriverInterface::class);

        $priority = 1;
        $driverHeap = new DriverHeap();
        $driverHeap->insert($driverInterfaceMock, $priority);

        $this->manager->addDriver($driverInterfaceMock, $priority);
        $drivers = $this->manager->getDrivers();

        $this->assertEquals($driverHeap, $drivers);
    }

    public function testGetDriversReturnDifferentClone(): void
    {
        $driverFirstTime = $this->manager->getDrivers();
        $driverSecondTime = $this->manager->getDrivers();

        $this->assertNotSame($driverFirstTime, $driverSecondTime);
    }

    public function testGetMetadataWithoutDrivers(): void
    {
        $cols = [];
        $mappings = [];
        $groupBy = [];

        $metadataExpected = new Metadata();
        $metadataExpected->setFields($cols);
        $metadataExpected->setFieldsMappings($mappings);
        $metadataExpected->setGroupBy($groupBy);

        $metadata = $this->manager->getMetadata('foo', 'bar');

        $this->assertEquals($metadataExpected, $metadata);
    }

    public function testGetMetadata(): void
    {
        $fields = ['0' => 'bar'];
        $groupBy = ['foo' => 'bar'];
        $mapping = ['bar' => ['foo' => 'foo2']];

        $driverInterfaceMock = $this->createMock(DriverInterface::class);
        $driverInterfaceMock->method('getClassColumns')
                            ->willReturn($fields);

        $driverInterfaceMock->method('getFieldsMetadata')
                            ->willReturn($mapping);

        $driverInterfaceMock->method('getGroupBy')
                            ->willReturn($groupBy);
        $driverInterfaceMock->expects(self::once())->method('supports')->willReturn(true);

        $this->manager->addDriver($driverInterfaceMock, 1);

        $metadata = $this->manager->getMetadata('foo');

        $this->assertEquals($fields, $metadata->getFields());
        $this->assertEquals($groupBy, $metadata->getGroupBy());
        $this->assertEquals($mapping['bar'], $metadata->getFieldMapping('bar'));
    }
}
