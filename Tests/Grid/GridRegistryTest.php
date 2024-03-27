<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\ColumnAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\ColumnNotFoundException;
use APY\DataGridBundle\Grid\Exception\TypeAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\TypeNotFoundException;
use APY\DataGridBundle\Grid\GridRegistry;
use APY\DataGridBundle\Grid\GridTypeInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class GridRegistryTest.
 */
class GridRegistryTest extends TestCase
{
    /**
     * @var GridRegistry
     */
    private $registry;

    public function testAddTypeAlreadyExists(): void
    {
        $this->expectException(TypeAlreadyExistsException::class);

        $type = $this->createTypeMock();
        $this->registry->addType($type);
        $this->registry->addType($type);
    }

    public function testAddType(): void
    {
        $this->assertFalse($this->registry->hasType('foo'));
        $this->registry->addType($this->createTypeMock());
        $this->assertTrue($this->registry->hasType('foo'));
    }

    public function testAddIsFluent(): void
    {
        $registry = $this->registry->addType($this->createTypeMock());
        $this->assertSame($registry, $this->registry);
    }

    public function testGetTypeUnknown(): void
    {
        $this->expectException(TypeNotFoundException::class);
        $this->registry->getType('foo');
    }

    public function testGetType(): void
    {
        $expectedType = $this->createTypeMock();

        $this->registry->addType($expectedType);
        $this->assertSame($expectedType, $this->registry->getType('foo'));
    }

    public function testAddColumnAlreadyExists(): void
    {
        $this->expectException(ColumnAlreadyExistsException::class);

        $type = $this->createColumnTypeMock();

        $this->registry->addColumn($type);
        $this->registry->addColumn($type);
    }

    public function testAddColumnType(): void
    {
        $this->assertFalse($this->registry->hasColumn('type'));
        $this->registry->addColumn($this->createColumnTypeMock());
        $this->assertTrue($this->registry->hasColumn('type'));
    }

    public function testAddColumnTypeIsFluent(): void
    {
        $registry = $this->registry->addColumn($this->createColumnTypeMock());
        $this->assertSame($registry, $this->registry);
    }

    public function testGetColumnTypeUnknown(): void
    {
        $this->expectException(ColumnNotFoundException::class);
        $this->registry->getColumn('type');
    }

    public function testGetColumnType(): void
    {
        $expectedColumnType = $this->createColumnTypeMock();

        $this->registry->addColumn($expectedColumnType);
        $this->assertSame($expectedColumnType, $this->registry->getColumn('type'));
    }

    protected function setUp(): void
    {
        $this->registry = new GridRegistry();
    }

    protected function createTypeMock()
    {
        $mock = $this->createMock(GridTypeInterface::class);
        $mock->expects($this->any())
             ->method('getName')
             ->willReturn('foo');

        return $mock;
    }

    protected function createColumnTypeMock()
    {
        $mock = $this->createMock(Column::class);
        $mock->expects($this->any())
             ->method('getType')
             ->willReturn('type');

        return $mock;
    }
}
