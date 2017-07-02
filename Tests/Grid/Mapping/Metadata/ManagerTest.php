<?php

namespace APY\DataGridBundle\Grid\Tests\Mapping\Metadata;

use APY\DataGridBundle\Grid\Mapping\Driver\DriverInterface;
use APY\DataGridBundle\Grid\Mapping\Metadata\DriverHeap;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Mapping\Metadata\Metadata;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function setUp()
    {
        $this->manager = new Manager();
    }

    public function testAddDriver()
    {
        $driverInterfaceMock = $this->createMock(DriverInterface::class);
        $priority = 1;

        $driverHeap = new DriverHeap();
        $driverHeap->insert($driverInterfaceMock, $priority);

        $this->manager->addDriver($driverInterfaceMock, $priority);

        $this->assertAttributeEquals($driverHeap, 'drivers', $this->manager);
    }

    public function testGetDrivers()
    {
        $driverInterfaceMock = $this->createMock(DriverInterface::class);

        $priority = 1;
        $driverHeap = new DriverHeap();
        $driverHeap->insert($driverInterfaceMock, $priority);

        $this->manager->addDriver($driverInterfaceMock, $priority);
        $drivers = $this->manager->getDrivers();

        $this->assertEquals($driverHeap, $drivers);
    }

    public function testGetDriversReturnDifferentClone()
    {
        $driverFirstTime = $this->manager->getDrivers();
        $driverSecondTime = $this->manager->getDrivers();

        $this->assertNotSame($driverFirstTime, $driverSecondTime);
    }

    public function testGetMetadataWithoutDrivers()
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

    public function testGetMetadata()
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

        $this->manager->addDriver($driverInterfaceMock, 1);

        $metadata = $this->manager->getMetadata('foo');

        $this->assertAttributeEquals($fields, 'fields', $metadata);
        $this->assertAttributeEquals($groupBy, 'groupBy', $metadata);
        $this->assertAttributeEquals($mapping, 'fieldsMappings', $metadata);
    }
}
