<?php
namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\GridRegistry;

/**
 * Class GridRegistryTest
 *
 * @package APY\DataGridBundle\Tests\Grid
 */
class GridRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GridRegistry
     */
    private $registry;

    public function testAddTypeAlreadyExists()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\TypeAlreadyExistsException');

        $type = $this->createTypeMock();

        $this->registry->addType($type);
        $this->registry->addType($type);
    }

    public function testAddType()
    {
        $this->assertFalse($this->registry->hasType('foo'));
        $this->registry->addType($this->createTypeMock());
        $this->assertTrue($this->registry->hasType('foo'));
    }

    public function testAddIsFluent()
    {
        $registry = $this->registry->addType($this->createTypeMock());
        $this->assertSame($registry, $this->registry);
    }

    public function testGetTypeUnknown()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\TypeNotFoundException');
        $this->registry->getType('foo');
    }

    public function testGetType()
    {
        $expectedType = $this->createTypeMock();

        $this->registry->addType($expectedType);
        $this->assertSame($expectedType, $this->registry->getType('foo'));
    }

    public function testAddColumnAlreadyExists()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\ColumnAlreadyExistsException');

        $type = $this->createColumnTypeMock();

        $this->registry->addColumn($type);
        $this->registry->addColumn($type);
    }

    public function testAddColumnType()
    {
        $this->assertFalse($this->registry->hasColumn('type'));
        $this->registry->addColumn($this->createColumnTypeMock());
        $this->assertTrue($this->registry->hasColumn('type'));
    }

    public function testAddColumnTypeIsFluent()
    {
        $registry = $this->registry->addColumn($this->createColumnTypeMock());
        $this->assertSame($registry, $this->registry);
    }

    public function testGetColumnTypeUnknown()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\ColumnNotFoundException');
        $this->registry->getColumn('type');
    }

    public function testGetColumnType()
    {
        $expectedColumnType = $this->createColumnTypeMock();

        $this->registry->addColumn($expectedColumnType);
        $this->assertSame($expectedColumnType, $this->registry->getColumn('type'));
    }

    protected function setUp()
    {
        $this->registry = new GridRegistry();
    }

    protected function createTypeMock()
    {
        $mock = $this->getMock('APY\DataGridBundle\Grid\GridTypeInterface');
        $mock->expects($this->any())
             ->method('getName')
             ->willReturn('foo');

        return $mock;
    }

    protected function createColumnTypeMock()
    {
        $mock = $this->getMock('APY\DataGridBundle\Grid\Column\Column');
        $mock->expects($this->any())
             ->method('getType')
             ->willReturn('type');

        return $mock;
    }
}
