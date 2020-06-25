<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ActionsColumnTest extends TestCase
{
    /** @var ActionsColumn */
    private $column;

    public function testConstructor()
    {
        $columnId = 'columnId';
        $columnTitle = 'columnTitle';

        $rowAction1 = $this->createMock(RowAction::class);
        $rowAction2 = $this->createMock(RowAction::class);
        $column = new ActionsColumn($columnId, $columnTitle, [$rowAction1, $rowAction2]);

        $this->assertAttributeEquals([$rowAction1, $rowAction2], 'rowActions', $column);
        $this->assertAttributeEquals($columnId, 'id', $column);
        $this->assertAttributeEquals($columnTitle, 'title', $column);
        $this->assertAttributeEquals(false, 'sortable', $column);
        $this->assertAttributeEquals(false, 'visibleForSource', $column);
        $this->assertAttributeEquals(true, 'filterable', $column);
    }

    public function testGetType()
    {
        $this->assertEquals('actions', $this->column->getType());
    }

    public function testGetFilterType()
    {
        $this->assertEquals('actions', $this->column->getFilterType());
    }

    public function testGetActionsToRender()
    {
        $row = $this->createMock(Row::class);

        $rowAction1 = $this->createMock(RowAction::class);
        $rowAction1->method('render')->with($row)->willReturn(null);
        $rowAction2 = $this->createMock(RowAction::class);
        $rowAction2->method('render')->with($row)->willReturn($rowAction2);
        $rowAction2->method('getAttributes')->willReturn(['class' => '']);

        $column = new ActionsColumn('columnId', 'columnTitle', [
            $rowAction1,
            $rowAction2,
        ]);

        $this->assertEquals([1 => $rowAction2], $column->getActionsToRender($row));
    }

    public function testGetRowActions()
    {
        $rowAction1 = $this->createMock(RowAction::class);
        $rowAction2 = $this->createMock(RowAction::class);
        $column = new ActionsColumn('columnId', 'columnTitle', [
            $rowAction1,
            $rowAction2,
        ]);

        $this->assertEquals([$rowAction1, $rowAction2], $column->getRowActions());
    }

    public function testSetRowActions()
    {
        $rowAction1 = $this->createMock(RowAction::class);
        $rowAction2 = $this->createMock(RowAction::class);
        $column = new ActionsColumn('columnId', 'columnTitle', []);
        $column->setRowActions([$rowAction1, $rowAction2]);

        $this->assertAttributeEquals([$rowAction1, $rowAction2], 'rowActions', $column);
    }

    public function testIsNotVisibleIfExported()
    {
        $isExported = true;
        $this->assertFalse($this->column->isVisible($isExported));
    }

    public function testIsVisibleIfNotExportedAndNoAuthChecker()
    {
        $this->assertTrue($this->column->isVisible());
    }

    public function testIsVisibleIfNotExportedNoAuthCheckerAndNotRole()
    {
        $this->column->setAuthorizationChecker($this->createMock(AuthorizationCheckerInterface::class));
        $this->assertTrue($this->column->isVisible());
    }

    public function testIsVisibleIfAuthCheckerIsGranted()
    {
        $role = 'role';
        $this->column->setRole($role);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(true);
        $this->column->setAuthorizationChecker($authChecker);

        $this->assertTrue($this->column->isVisible());
    }

    public function testIsNotVisibleIfAuthCheckerIsNotGranted()
    {
        $role = 'role';
        $this->column->setRole($role);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(false);
        $this->column->setAuthorizationChecker($authChecker);

        $this->assertFalse($this->column->isVisible());
    }

    public function testGetPrimaryFieldAsRouteParametersIfRouteParametersNotSetted()
    {
        $row = $this->createMock(Row::class);
        $row->method('getPrimaryField')->willReturn('id');
        $row->method('getPrimaryFieldValue')->willReturn(1);

        $rowAction = $this->createMock(RowAction::class);
        $rowAction->method('getRouteParameters')->willReturn([]);

        $this->assertEquals(['id' => 1], $this->column->getRouteParameters($row, $rowAction));
    }

    public function testGetRouteParameters()
    {
        $row = $this->createMock(Row::class);
        $row
            ->method('getField')
            ->withConsecutive(['foo.bar'], ['barFoo'])
            ->willReturnOnConsecutiveCalls('testValue', 'aValue');

        $rowAction = $this->createMock(RowAction::class);
        $rowAction
            ->method('getRouteParametersMapping')
            ->withConsecutive(['foo.bar'], ['barFoo'])
            ->willReturnOnConsecutiveCalls(null, 'aName');

        $rowAction->method('getRouteParameters')->willReturn([
            'foo'            => 1,
            'foo.bar.foobar' => 2,
            1                => 'foo.bar',
            '2'              => 'barFoo',
        ]);

        $this->assertEquals([
            'foo'          => 1,
            'fooBarFoobar' => 2,
            'fooBar'       => 'testValue',
            'aName'        => 'aValue',
        ], $this->column->getRouteParameters($row, $rowAction));
    }

    public function setUp()
    {
        $rowAction1 = $this->createMock(RowAction::class);
        $rowAction2 = $this->createMock(RowAction::class);
        $this->column = new ActionsColumn('columnId', 'columnTitle', [
            $rowAction1,
            $rowAction2,
        ]);
    }
}
