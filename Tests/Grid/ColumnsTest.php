<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ColumnsTest extends TestCase
{
    private \APY\DataGridBundle\Grid\Columns $columns;

    private \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authChecker;

    public function testGetIterator()
    {
        $iterator = $this->columns->getIterator();
        $this->assertInstanceOf(ColumnsIterator::class, $iterator);
    }

    public function testAddColumn()
    {
        $column = $this->buildColumnMocks(1);
        $this->columns->addColumn($column);

        $this->equalTo(1);
    }

    public function testAddColumnsOrder()
    {
        [$column1, $column2, $column3, $column4] = $this->buildColumnMocks(4);

        $this->columns
            ->addColumn($column1)
            ->addColumn($column2, 1)
            ->addColumn($column3, 2)
            ->addColumn($column4, -1)
            ;

        $this->assertSame([$column2, $column3, $column4, $column1], $this->columns->getColumns());
    }

    public function testRaiseExceptionIfGetColumnByIdDoesNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $column = $this->buildColumnMocks(1);
        $this->columns->addColumn($column);

        $this->columns->getColumnById('foo');
    }

    public function testGetColumnById()
    {
        $column = $this->buildColumnMocks(1);
        $column->method('getId')->willReturn('foo');
        $this->columns->addColumn($column);

        $this->assertSame($column, $this->columns->getColumnById('foo'));
    }

    public function testHasColumnById()
    {
        $column = $this->buildColumnMocks(1);
        $column->method('getId')->willReturn('foo');
        $this->columns->addColumn($column);

        $this->assertSame($column, $this->columns->hasColumnById('foo', true));
        $this->assertTrue($this->columns->hasColumnById('foo', false));
    }

    public function testRaiseExceptionIfGetPrimaryColumnDoesNotExists()
    {
        $this->expectException(\InvalidArgumentException::class);

        $column = $this->buildColumnMocks(1);
        $column->method('isPrimary')->willReturn(false);
        $this->columns->addColumn($column);

        $this->columns->getPrimaryColumn();
    }

    public function testGetPrimaryColumn()
    {
        [$column1, $column2, $column3] = $this->buildColumnMocks(3);

        $column1->method('isPrimary')->willReturn(false);
        $this->columns->addColumn($column1);

        $column2->method('isPrimary')->willReturn(true);
        $this->columns->addColumn($column2);

        $column3->method('isPrimary')->willReturn(true);
        $this->columns->addColumn($column3);

        $this->assertSame($column2, $this->columns->getPrimaryColumn());
    }

    public function testAddExtension()
    {
        $column1 = $this->createMock(Column::class);
        $column1->method('getType')->willReturn('foo');

        $column2 = $this->createMock(Column::class);
        $column2->method('getType')->willReturn('bar');

        $this->columns
            ->addExtension($column1)
            ->addExtension($column2);

        $this->assertEquals(['foo' => $column1, 'bar' => $column2], $this->columns->getExtensions());
    }

    public function testHasExtensionForColumnType()
    {
        $column1 = $this->createMock(Column::class);
        $column1->method('getType')->willReturn('foo');

        $this->columns->addExtension($column1);

        $this->assertTrue($this->columns->hasExtensionForColumnType('foo'));
        $this->assertFalse($this->columns->hasExtensionForColumnType('bar'));
    }

    public function testGetExtensionForColumnType()
    {
        $column1 = $this->createMock(Column::class);
        $column1->method('getType')->willReturn('foo');

        $this->columns->addExtension($column1);

        $this->assertEquals($column1, $this->columns->getExtensionForColumnType('foo'));
    }

    public function testGetHash()
    {
        $this->assertEquals('', $this->columns->getHash());

        [$column1, $column2, $column3, $column4] = $this->buildColumnMocks(4);

        $column1->method('getId')->willReturn('this');
        $column2->method('getId')->willReturn('Is');
        $column3->method('getId')->willReturn('The');
        $column4->method('getId')->willReturn('Hash');

        $this->columns
            ->addColumn($column1)
            ->addColumn($column2)
            ->addColumn($column3)
            ->addColumn($column4);

        $this->assertEquals('thisIsTheHash', $this->columns->getHash());
    }

    public function testSetColumnsOrder()
    {
        [$column1, $column2, $column3] = $this->buildColumnMocks(3);

        $column1->method('getId')->willReturn('col1');
        $column2->method('getId')->willReturn('col2');
        $column3->method('getId')->willReturn('col3');

        $this->columns
            ->addColumn($column1)
            ->addColumn($column2)
            ->addColumn($column3);
        $this->columns->setColumnsOrder(['col3', 'col1', 'col2']);

        $this->assertSame([$column3, $column1, $column2], $this->columns->getColumns());
    }

    public function testPartialSetColumnsOrderAndKeepOthers()
    {
        [$column1, $column2, $column3] = $this->buildColumnMocks(3);

        $column1->method('getId')->willReturn('col1');
        $column2->method('getId')->willReturn('col2');
        $column3->method('getId')->willReturn('col3');

        $this->columns
            ->addColumn($column1)
            ->addColumn($column2)
            ->addColumn($column3);
        $this->columns->setColumnsOrder(['col3', 'col2'], true);

        $this->assertSame([$column3, $column2, $column1], $this->columns->getColumns());
    }

    public function testPartialSetColumnsOrderWithoutKeepOthers()
    {
        [$column1, $column2, $column3] = $this->buildColumnMocks(3);

        $column1->method('getId')->willReturn('col1');
        $column2->method('getId')->willReturn('col2');
        $column3->method('getId')->willReturn('col3');

        $this->columns
            ->addColumn($column1)
            ->addColumn($column2)
            ->addColumn($column3);
        $this->columns->setColumnsOrder(['col3', 'col2'], false);

        $this->assertSame([$column3, $column2], $this->columns->getColumns());
    }

    /**
     * @param int $number
     *
     * @return array|\PHPUnit_Framework_MockObject_MockObject[]|\PHPUnit_Framework_MockObject_MockObject
     */
    private function buildColumnMocks($number) //: array|\PHPUnit_Framework_MockObject_MockObject|\APY\DataGridBundle\Grid\Column\Column
    {
        $mocks = [];
        for ($i = 0; $i < $number; ++$i) {
            $column = $this->createMock(Column::class);
            $column
                ->expects($this->once())
                ->method('setAuthorizationChecker')
                ->with($this->authChecker);

            $mocks[] = $column;
        }

        if ($number == 1) {
            return current($mocks);
        }

        return $mocks;
    }

    public function setUp(): void
    {
        $this->authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->columns = new Columns($this->authChecker);
    }
}
