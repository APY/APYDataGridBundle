<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class BooleanColumnTest extends TestCase
{
    private \APY\DataGridBundle\Grid\Column\BooleanColumn $column;

    public function testGetType()
    {
        $this->assertEquals('boolean', $this->column->getType());
    }

    public function testGetDisplayedValue()
    {
        $this->assertEquals(1, $this->column->getDisplayedValue(true));
        $this->assertEquals(0, $this->column->getDisplayedValue(false));
        $this->assertEquals('foo', $this->column->getDisplayedValue('foo'));
    }

    public function testInitialize()
    {
        $params = [
            'filter' => 'foo',
            'bar'    => 'bar',
            'size'   => 52,
        ];

        $column = new BooleanColumn($params);

        $this->assertEquals([
            'filter'           => 'select',
            'selectFrom'       => 'values',
            'operators'        => [Column::OPERATOR_EQ],
            'defaultOperator'  => Column::OPERATOR_EQ,
            'operatorsVisible' => false,
            'selectMulti'      => false,
            'bar'              => 'bar',
            'size'             => 52,
        ], $column->getParams());
    }

    public function testInitializeAlignment()
    {
        $this->assertEquals(Column::ALIGN_CENTER, $this->column->getAlign());

        $column = new BooleanColumn(['align' => Column::ALIGN_LEFT]);
        $this->assertEquals(Column::ALIGN_LEFT, $column->getAlign());
    }

    public function testInitializeSize()
    {
        $this->assertEquals(30, $this->column->getSize());

        $column = new BooleanColumn(['size' => 40]);
        $this->assertEquals(40, $column->getSize());
    }

    public function testInitializeValues()
    {
        $this->assertEquals([1 => 'true', 0 => 'false'], $this->column->getValues());

        $values = [1 => 'foo', 0 => 'bar'];
        $params = ['values' => $values];
        $column = new BooleanColumn($params);
        $this->assertEquals($values, $column->getValues());
    }

    public function testIsQueryValid()
    {
        // It seems that's no way for this to return false

        $this->assertTrue($this->column->isQueryValid(true));
        $this->assertTrue($this->column->isQueryValid(false));
        $this->assertTrue($this->column->isQueryValid(1));
        $this->assertTrue($this->column->isQueryValid(0));
        $this->assertFalse($this->column->isQueryValid('foo'));
    }

    public function testRenderCell()
    {
        $this->assertEquals('true', $this->column->renderCell(
            true, $this->createMock(Row::class), $this->createMock(Router::class)
        ));

        $this->assertEquals('true', $this->column->renderCell(
            1, $this->createMock(Row::class), $this->createMock(Router::class)
        ));

        $this->assertEquals('false', $this->column->renderCell(
            0, $this->createMock(Row::class), $this->createMock(Router::class)
        ));
    }

    public function testRenderCellWithCallback()
    {
        $this->column->manipulateRenderCell(
            fn($value, $row, $router) => 'true'
        );
        $this->assertEquals('true', $this->column->renderCell(
            0, $this->createMock(Row::class), $this->createMock(Router::class)
        ));

        $this->column->manipulateRenderCell(
            fn($value, $row, $router) => 'false'
        );
        $this->assertEquals('false', $this->column->renderCell(
            1, $this->createMock(Row::class), $this->createMock(Router::class)
        ));

        $this->column->manipulateRenderCell(
            function ($value, $row, $router) {
                return;
            }
        );
        $this->assertEquals('false', $this->column->renderCell(
            1, $this->createMock(Row::class), $this->createMock(Router::class)
        ));
    }

    public function setUp(): void
    {
        $this->column = new BooleanColumn();
    }
}
